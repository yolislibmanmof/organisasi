<?php
// File: app/Models/Census.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Census — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature v5.9 + extension untuk fitur v7.0:
 * - Purpose field (pendaftaran / sensus)
 * - Reference number generator
 * - Admin stats, search, filter, pagination
 * - Bulk actions
 * - Decorator (relative time, formatted date, initial avatar)
 * - IP & User-Agent tracking
 * - Cache untuk stats
 * - Export helper
 */
class Census
{
    /** Purpose options */
    public const PURPOSE_REGISTRATION = 'pendaftaran';
    public const PURPOSE_CENSUS       = 'sensus';
    public const PURPOSES = [self::PURPOSE_REGISTRATION, self::PURPOSE_CENSUS];

    /** Status options */
    public const STATUSES = ['pelajar', 'mahasiswa', 'alumni', 'umum'];

    /** Processed flags */
    public const PENDING    = 0;
    public const PROCESSED  = 1;

    /** Cache TTL (5 menit untuk stats) */
    private const CACHE_TTL = 300;

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row untuk display di view:
     * - Initial avatar
     * - Formatted date
     * - Relative time
     * - Purpose label
     * - Status label
     */
    private static function decorate(array $row): array
    {
        // Initial avatar (untuk UI)
        $name = $row['full_name'] ?? '';
        $row['initial'] = strtoupper(mb_substr($name, 0, 1));

        // Purpose label
        $row['purpose_label'] = match ($row['purpose'] ?? '') {
            self::PURPOSE_REGISTRATION => 'Pendaftaran Anggota',
            self::PURPOSE_CENSUS       => 'Sensus Alumni',
            default                    => 'Lainnya',
        };

        // Status label
        $row['status_label'] = ucfirst($row['status'] ?? 'umum');

        // Formatted date
        if (!empty($row['created_at'])) {
            $ts = strtotime($row['created_at']);
            $row['date_formatted'] = date('d M Y, H:i', $ts ?: time());
            $row['date_short']     = date('d M Y', $ts ?: time());
            $row['date_iso']       = date('c', $ts ?: time());
            $row['relative_time']  = self::relativeTime($ts ?: time());
        } else {
            $row['date_formatted'] = '—';
            $row['date_short']     = '—';
            $row['date_iso']       = '';
            $row['relative_time']  = '—';
        }

        // Processed date
        if (!empty($row['processed_at'])) {
            $ts = strtotime($row['processed_at']);
            $row['processed_formatted'] = date('d M Y, H:i', $ts ?: time());
        } else {
            $row['processed_formatted'] = null;
        }

        // Booleans untuk UI
        $row['is_processed'] = ((int) ($row['processed'] ?? 0)) === self::PROCESSED;
        $row['is_pending']   = !$row['is_processed'];

        // Phone formatting (Indonesia style)
        if (!empty($row['phone'])) {
            $digits = preg_replace('/\D/', '', $row['phone']);
            if (strlen($digits) > 8) {
                $row['phone_formatted'] = substr($digits, 0, 4) . '-' . substr($digits, 4, 4) . '-' . substr($digits, 8);
            } else {
                $row['phone_formatted'] = $row['phone'];
            }
        } else {
            $row['phone_formatted'] = '—';
        }

        // Email masking (untuk privacy di list)
        if (!empty($row['email'])) {
            $parts = explode('@', $row['email']);
            if (count($parts) === 2) {
                $name = $parts[0];
                $masked = strlen($name) > 2 
                    ? substr($name, 0, 2) . str_repeat('*', max(1, strlen($name) - 2))
                    : $name . '*';
                $row['email_masked'] = $masked . '@' . $parts[1];
            } else {
                $row['email_masked'] = $row['email'];
            }
        } else {
            $row['email_masked'] = '—';
        }

        return $row;
    }

    private static function decorateAll(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /**
     * Relative time helper (Bahasa Indonesia).
     */
    private static function relativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 60)     return 'Baru saja';
        if ($diff < 3600)   return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400)  return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', $timestamp);
    }

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    private static function cacheKey(string $suffix): string
    {
        return 'census|' . $suffix;
    }

    private static function clearCache(): void
    {
        Cache::flush('census');
    }

    /* ============================================================
       REFERENCE NUMBER GENERATOR
       ============================================================ */

    /**
     * Generate unique reference number.
     * Format: REG-YYYYMMDD-XXXX (untuk pendaftaran)
     *         SEN-YYYYMMDD-XXXX (untuk sensus)
     */
    public static function generateReference(string $purpose = self::PURPOSE_REGISTRATION): string
    {
        $prefix = ($purpose === self::PURPOSE_CENSUS) ? 'SEN' : 'REG';
        $date   = date('Ymd');

        // Generate suffix unik dengan retry
        for ($i = 0; $i < 10; $i++) {
            $suffix = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $ref    = $prefix . '-' . $date . '-' . $suffix;

            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM census WHERE reference = :ref'
            );
            $stmt->execute([':ref' => $ref]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $ref;
            }
        }

        // Fallback: tambah timestamp
        return $prefix . '-' . $date . '-' . substr((string) time(), -4);
    }

    /* ============================================================
       PUBLIC SUBMISSION (backward compat + extended)
       ============================================================ */

    /**
     * Simpan submission census.
     *
     * Backward compat: signature lama `store(array $d): int` dipertahankan.
     * Extended: support purpose, reference, IP, user agent.
     *
     * @return int ID baris yang baru dibuat
     */
    public static function store(array $d): int
    {
        // Defaults & sanitasi
        $purpose        = $d['purpose']        ?? self::PURPOSE_REGISTRATION;
        $fullName       = trim($d['full_name']       ?? '');
        $email          = trim(strtolower($d['email'] ?? ''));
        $phone          = trim($d['phone']            ?? '');
        $address        = trim($d['address']          ?? '');
        $status         = $d['status']                ?? '';
        $graduationYear = $d['graduation_year']       ?? '';
        $message        = trim($d['message']          ?? '');
        $ipAddress      = $d['ip_address']            ?? self::getClientIp();
        $userAgent      = $d['user_agent']            ?? self::getClientUserAgent();

        // Generate reference number
        $reference = self::generateReference($purpose);

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO census
                (full_name, email, phone, address, status, graduation_year, message,
                 purpose, reference, ip_address, user_agent, processed, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())'
        );
        $stmt->execute([
            $fullName, $email, $phone, $address, $status, $graduationYear, $message,
            $purpose, $reference, $ipAddress, $userAgent,
        ]);

        self::clearCache();

        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Ambil semua data (backward compat).
     * Catatan: untuk list admin gunakan `adminList()` yang support pagination.
     */
    public static function all(): array
    {
        $rows = Database::getInstance()
            ->query('SELECT * FROM census ORDER BY processed ASC, id DESC')
            ->fetchAll();
        return self::decorateAll($rows);
    }

    /**
     * Hitung yang belum diproses (backward compat).
     */
    public static function countNew(): int
    {
        return (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM census WHERE processed = 0')
            ->fetchColumn();
    }

    /**
     * Find by ID (backward compat, dengan decorator).
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM census WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Find by reference number (untuk tracking publik).
     */
    public static function findByReference(string $reference): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM census WHERE reference = :ref LIMIT 1'
        );
        $stmt->execute([':ref' => $reference]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Cek apakah email sudah punya submission pending.
     *
     * Extended: bisa filter by purpose agar tidak tabrakan.
     * Backward compat: signature lama `emailPending(string $email): bool` tetap jalan.
     */
    public static function emailPending(string $email, string $purpose = ''): bool
    {
        $params = [':e' => strtolower(trim($email))];
        $sql = 'SELECT COUNT(*) FROM census WHERE email = :e AND processed = 0';

        if ($purpose !== '' && in_array($purpose, self::PURPOSES, true)) {
            $sql .= ' AND purpose = :p';
            $params[':p'] = $purpose;
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Tandai sebagai sudah diproses.
     * Extended: set processed_at timestamp.
     */
    public static function markProcessed(int $id): void
    {
        Database::getInstance()->prepare(
            'UPDATE census SET processed = 1, processed_at = NOW() WHERE id = ?'
        )->execute([$id]);
        self::clearCache();
    }

    /**
     * Hapus satu data (backward compat).
     */
    public static function delete(int $id): void
    {
        Database::getInstance()->prepare('DELETE FROM census WHERE id = ?')->execute([$id]);
        self::clearCache();
    }

    /* ============================================================
       ADMIN — STATS
       ============================================================ */

    /**
     * Statistik lengkap untuk admin dashboard.
     * Cached 5 menit.
     *
     * @return array{
     *     total: int,
     *     pending: int,
     *     processed: int,
     *     pendaftaran: int,
     *     sensus: int,
     *     new_today: int,
     *     new_this_week: int
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::getInstance();

        $total     = (int) $db->query('SELECT COUNT(*) FROM census')->fetchColumn();
        $pending   = (int) $db->query('SELECT COUNT(*) FROM census WHERE processed = 0')->fetchColumn();
        $processed = $total - $pending;

        $stmt = $db->query(
            "SELECT purpose, COUNT(*) AS c FROM census GROUP BY purpose"
        );
        $byPurpose = ['pendaftaran' => 0, 'sensus' => 0];
        foreach ($stmt->fetchAll() as $row) {
            if (isset($byPurpose[$row['purpose']])) {
                $byPurpose[$row['purpose']] = (int) $row['c'];
            }
        }

        $newToday = (int) $db->query(
            "SELECT COUNT(*) FROM census WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();

        $newThisWeek = (int) $db->query(
            "SELECT COUNT(*) FROM census WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        $stats = [
            'total'         => $total,
            'pending'       => $pending,
            'processed'     => $processed,
            'pendaftaran'   => $byPurpose['pendaftaran'],
            'sensus'        => $byPurpose['sensus'],
            'new_today'     => $newToday,
            'new_this_week' => $newThisWeek,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * Count by status (pelajar/mahasiswa/alumni/umum).
     */
    public static function countByStatus(): array
    {
        $cacheKey = self::cacheKey('count_by_status');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $result = [];
        $rows = Database::getInstance()->query(
            'SELECT status, COUNT(*) AS c FROM census GROUP BY status ORDER BY c DESC'
        )->fetchAll();
        foreach ($rows as $row) {
            $result[$row['status'] ?: 'tidak_diketahui'] = (int) $row['c'];
        }

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /* ============================================================
       ADMIN — LIST + SEARCH + FILTER + PAGINATION
       ============================================================ */

    /**
     * List untuk admin dengan filter lengkap.
     *
     * @param string $status    Filter status (pelajar/mahasiswa/alumni/umum)
     * @param string $purpose   Filter purpose (pendaftaran/sensus)
     * @param string $processed Filter processed (pending/processed/all)
     * @param string $search    Search di nama/email/phone/reference
     * @param string $sort      Kolom sort (created_at, full_name, id)
     * @param string $order     Arah sort (asc/desc)
     * @param int    $page      Halaman (1-based)
     * @param int    $perPage   Item per halaman
     */
    public static function adminList(
        string $status = '',
        string $purpose = '',
        string $processed = '',
        string $search = '',
        string $sort = 'created_at',
        string $order = 'desc',
        int $page = 1,
        int $perPage = 15
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT * FROM census';

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        if ($purpose !== '' && in_array($purpose, self::PURPOSES, true)) {
            $where[] = 'purpose = :purpose';
            $params[':purpose'] = $purpose;
        }

        if ($processed === 'pending') {
            $where[] = 'processed = 0';
        } elseif ($processed === 'processed') {
            $where[] = 'processed = 1';
        }

        if ($search !== '') {
            $where[] = '(full_name LIKE :q1 OR email LIKE :q2 OR phone LIKE :q3 OR reference LIKE :q4)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort column whitelist
        $sortWhitelist = ['created_at', 'full_name', 'id', 'processed', 'purpose'];
        if (!in_array($sort, $sortWhitelist, true)) $sort = 'created_at';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sort $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList (dengan filter yang sama).
     */
    public static function countAdminList(
        string $status = '',
        string $purpose = '',
        string $processed = '',
        string $search = ''
    ): int {
        $params = [];
        $where  = [];

        $sql = 'SELECT COUNT(*) FROM census';

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        if ($purpose !== '' && in_array($purpose, self::PURPOSES, true)) {
            $where[] = 'purpose = :purpose';
            $params[':purpose'] = $purpose;
        }

        if ($processed === 'pending') {
            $where[] = 'processed = 0';
        } elseif ($processed === 'processed') {
            $where[] = 'processed = 1';
        }

        if ($search !== '') {
            $where[] = '(full_name LIKE :q1 OR email LIKE :q2 OR phone LIKE :q3 OR reference LIKE :q4)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ============================================================
       ADMIN — BULK ACTIONS
       ============================================================ */

    /**
     * Bulk mark as processed.
     */
    public static function bulkMarkProcessed(array $ids): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE census SET processed = 1, processed_at = NOW() WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));

        self::clearCache();
        return $stmt->rowCount();
    }

    /**
     * Bulk delete.
     */
    public static function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM census WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));

        self::clearCache();
        return $stmt->rowCount();
    }

    /* ============================================================
       ADMIN — EXPORT
       ============================================================ */

    /**
     * Ambil semua data untuk export (tanpa pagination, dengan decorator).
     * Digunakan untuk CSV/JSON export.
     */
    public static function exportAll(
        string $purpose = '',
        string $processed = ''
    ): array {
        $params = [];
        $where  = [];

        $sql = 'SELECT * FROM census';

        if ($purpose !== '' && in_array($purpose, self::PURPOSES, true)) {
            $where[] = 'purpose = :purpose';
            $params[':purpose'] = $purpose;
        }

        if ($processed === 'pending') {
            $where[] = 'processed = 0';
        } elseif ($processed === 'processed') {
            $where[] = 'processed = 1';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Convert row ke format CSV (flat array dengan header friendly).
     */
    public static function toCsvRow(array $row): array
    {
        return [
            'Reference'        => $row['reference'] ?? '',
            'Purpose'          => $row['purpose_label'] ?? '',
            'Nama Lengkap'     => $row['full_name'] ?? '',
            'Email'            => $row['email'] ?? '',
            'Telepon'          => $row['phone_formatted'] ?? '',
            'Status'           => $row['status_label'] ?? '',
            'Angkatan'         => $row['graduation_year'] ?? '',
            'Alamat'           => $row['address'] ?? '',
            'Pesan'            => $row['message'] ?? '',
            'Status Proses'    => $row['is_processed'] ? 'Diproses' : 'Pending',
            'Tanggal Submit'   => $row['date_formatted'] ?? '',
            'Tanggal Proses'   => $row['processed_formatted'] ?? '—',
            'IP Address'       => $row['ip_address'] ?? '',
        ];
    }

    /* ============================================================
       CLIENT INFO HELPERS
       ============================================================ */

    /**
     * Ambil IP client (handle proxy).
     */
    private static function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', (string) $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Ambil User-Agent client (truncate ke 500 char).
     */
    private static function getClientUserAgent(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return substr($ua, 0, 500);
    }

    /* ============================================================
       CLEANUP / MAINTENANCE
       ============================================================ */

    /**
     * Hapus data pending yang lebih lama dari N hari.
     * Digunakan untuk cron job / maintenance.
     */
    public static function cleanupOldPending(int $olderThanDays = 30): int
    {
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM census WHERE processed = 0 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$olderThanDays]);

        self::clearCache();
        return $stmt->rowCount();
    }
}