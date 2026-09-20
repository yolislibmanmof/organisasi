<?php
// File: app/Models/Member.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Member — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature v5.5 + extension untuk fitur v7.0:
 * - Admin stats (total/active/inactive/newThisMonth)
 * - Admin list dengan filter status (all/active/inactive/new) + search + sort + pagination
 * - Bulk actions (delete & update status)
 * - Decorator kaya (initial, photo_url, status_label, relative_time, is_new)
 * - Cache untuk stats
 * - Email/username uniqueness check
 * - Photo file cleanup saat delete
 */
class Member
{
    /** User status constants */
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];

    /** Default password untuk anggota baru (harus diganti saat first login) */
    public const DEFAULT_PASSWORD = 'password123';

    /** Sort columns whitelist */
    private const SORT_WHITELIST = ['full_name', 'join_date', 'email', 'username', 'created_at'];

    /** Cache TTL (5 menit) */
    private const CACHE_TTL = 300;

    /** Upload directory untuk foto profil */
    private const PHOTO_DIR = 'assets/uploads/users/';

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row dengan informasi tambahan untuk UI.
     *
     * Menambahkan:
     * - initial (huruf pertama nama, untuk avatar placeholder)
     * - photo_url (URL lengkap photo profile)
     * - status (active/inactive dari users table)
     * - status_label ("Aktif" / "Non-aktif")
     * - join_date_formatted ("20 September 2026")
     * - join_date_short ("20 Sep 2026")
     * - join_date_iso (ISO 8601 untuk <time> tag)
     * - relative_time ("3 hari lalu")
     * - is_new (boolean, bergabung bulan ini)
     * - avatar_color (gradient class berdasarkan hash nama)
     * - phone_formatted (format Indonesia: 0812-3456-7890)
     * - email_masked (untuk privacy di list)
     */
    private static function decorate(array $row): array
    {
        // Initial untuk avatar placeholder
        $name = $row['full_name'] ?? ($row['username'] ?? '');
        $row['initial'] = strtoupper(mb_substr(trim($name), 0, 1));

        // Photo URL
        if (!empty($row['photo'])) {
            $row['photo_url'] = function_exists('url')
                ? url(self::PHOTO_DIR . $row['photo'])
                : '/' . self::PHOTO_DIR . $row['photo'];
        } else {
            $row['photo_url'] = null;
        }

        // Avatar color (deterministic berdasarkan nama)
        $row['avatar_color'] = self::avatarColorClass($name);

        // Status handling (dari users table atau fallback)
        $status = $row['status'] ?? self::STATUS_ACTIVE;
        $row['status'] = $status;
        $row['status_label'] = ($status === self::STATUS_ACTIVE) ? 'Aktif' : 'Non-aktif';
        $row['is_active'] = ($status === self::STATUS_ACTIVE);

        // Join date formatting
        if (!empty($row['join_date'])) {
            $ts = strtotime($row['join_date']);
            if ($ts !== false) {
                $row['join_date_formatted'] = self::formatDateId($ts);
                $row['join_date_short']     = date('d M Y', $ts);
                $row['join_date_iso']       = date('Y-m-d', $ts);
                $row['relative_time']       = self::relativeTime($ts);

                // Cek apakah bergabung bulan ini
                $joinMonth = date('Y-m', $ts);
                $currentMonth = date('Y-m');
                $row['is_new'] = ($joinMonth === $currentMonth);
            } else {
                $row['join_date_formatted'] = '—';
                $row['join_date_short']     = '—';
                $row['join_date_iso']       = '';
                $row['relative_time']       = '—';
                $row['is_new']              = false;
            }
        } else {
            $row['join_date_formatted'] = '—';
            $row['join_date_short']     = '—';
            $row['join_date_iso']       = '';
            $row['relative_time']       = '—';
            $row['is_new']              = false;
        }

        // Phone formatting (Indonesia: 0812-3456-7890)
        if (!empty($row['phone'])) {
            $digits = preg_replace('/\D/', '', $row['phone']);
            if (strlen($digits) > 8) {
                $row['phone_formatted'] = substr($digits, 0, 4) . '-' . substr($digits, 4, 4) . '-' . substr($digits, 8);
            } elseif (strlen($digits) > 4) {
                $row['phone_formatted'] = substr($digits, 0, 4) . '-' . substr($digits, 4);
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
                $namePart = $parts[0];
                $masked = strlen($namePart) > 2
                    ? substr($namePart, 0, 2) . str_repeat('*', max(1, strlen($namePart) - 2))
                    : $namePart . '*';
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
     * Deterministic avatar color class berdasarkan nama.
     * Return: grad-1, grad-2, ..., grad-6
     */
    private static function avatarColorClass(string $name): string
    {
        $hash = crc32(strtolower(trim($name)));
        $index = (abs($hash) % 6) + 1;
        return 'grad-' . $index;
    }

    /**
     * Format tanggal Bahasa Indonesia.
     */
    private static function formatDateId(int $timestamp): string
    {
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $day   = date('j', $timestamp);
        $month = $months[(int) date('n', $timestamp)];
        $year  = date('Y', $timestamp);
        return "$day $month $year";
    }

    /**
     * Relative time Bahasa Indonesia.
     */
    private static function relativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;
        $days = (int) floor($diff / 86400);

        if ($days === 0) return 'Hari ini';
        if ($days === 1) return 'Kemarin';
        if ($days < 7) return "$days hari lalu";
        if ($days < 14) return 'Minggu lalu';
        if ($days < 30) return (int) floor($days / 7) . ' minggu lalu';
        if ($days < 60) return 'Bulan lalu';
        if ($days < 365) return (int) floor($days / 30) . ' bulan lalu';
        return (int) floor($days / 365) . ' tahun lalu';
    }

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    private static function cacheKey(string $suffix): string
    {
        return 'members|' . $suffix;
    }

    private static function clearCache(): void
    {
        Cache::flush('members');
    }

    /* ============================================================
       FINDERS (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Find member by user_id (untuk halaman profile).
     * Backward compat signature, dengan decorator.
     */
    public static function findByUserId(int $userId): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT m.*, u.email, u.username, u.status, u.photo
             FROM members m
             JOIN users u ON u.id = m.user_id
             WHERE m.user_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Find member by ID (untuk admin edit).
     * Backward compat signature, dengan decorator + include status.
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT m.*, u.email, u.username, u.status, u.photo
             FROM members m
             JOIN users u ON u.id = m.user_id
             WHERE m.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Find by username (untuk uniqueness check).
     */
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT m.*, u.email, u.username, u.status, u.photo
             FROM members m
             JOIN users u ON u.id = m.user_id
             WHERE u.username = :u LIMIT 1'
        );
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Find by email.
     */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT m.*, u.email, u.username, u.status, u.photo
             FROM members m
             JOIN users u ON u.id = m.user_id
             WHERE u.email = :e LIMIT 1'
        );
        $stmt->execute([':e' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /* ============================================================
       UNIQUENESS CHECKS
       ============================================================ */

    /**
     * Cek username sudah dipakai (backward compat).
     * Extended: optional excludeUserId untuk edit.
     */
    public static function usernameExists(string $username, int $excludeUserId = 0): bool
    {
        if ($excludeUserId > 0) {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE username = :u AND id <> :ex'
            );
            $stmt->execute([':u' => $username, ':ex' => $excludeUserId]);
        } else {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE username = :u'
            );
            $stmt->execute([':u' => $username]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Cek email sudah dipakai.
     * Extended: optional excludeUserId untuk edit.
     */
    public static function emailExists(string $email, int $excludeUserId = 0): bool
    {
        $email = strtolower(trim($email));
        if ($excludeUserId > 0) {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE email = :e AND id <> :ex'
            );
            $stmt->execute([':e' => $email, ':ex' => $excludeUserId]);
        } else {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE email = :e'
            );
            $stmt->execute([':e' => $email]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Generate username unik dari nama lengkap (backward compat).
     */
    public static function generateUsername(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $base  = strtolower(($parts[0] ?? 'anggota') . '.' . ($parts[1] ?? ''));
        $base  = preg_replace('/[^a-z0-9.]/', '', rtrim($base, '.')) ?: 'anggota';

        $candidate = $base;
        $i = 1;
        while (self::usernameExists($candidate)) {
            $candidate = $base . $i++;
            if ($i > 100) break; // Safety
        }
        return $candidate;
    }

    /* ============================================================
       PUBLIC COUNTS (BACKWARD COMPAT)
       ============================================================ */

    /**
     * Total semua anggota (backward compat).
     */
    public static function countAll(): int
    {
        $cacheKey = self::cacheKey('count_all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM members')
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Count per status (untuk filter pills di view v7.0).
     *
     * @return array{active: int, inactive: int, total: int}
     */
    public static function countByStatus(): array
    {
        $cacheKey = self::cacheKey('count_by_status');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN u.status = 'inactive' THEN 1 ELSE 0 END) AS inactive
             FROM members m
             JOIN users u ON u.id = m.user_id"
        );
        $row = $stmt->fetch();

        $result = [
            'total'    => (int) ($row['total'] ?? 0),
            'active'   => (int) ($row['active'] ?? 0),
            'inactive' => (int) ($row['inactive'] ?? 0),
        ];

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Jumlah anggota yang bergabung bulan ini (backward compat).
     */
    public static function countThisMonth(): int
    {
        $cacheKey = self::cacheKey('count_this_month_' . date('Ym'));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM members
             WHERE DATE_FORMAT(join_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /* ============================================================
       SEARCH (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Search untuk list lama (backward compat, dengan decorator).
     * Untuk list admin gunakan `adminList()` yang lebih lengkap.
     */
    public static function search(string $keyword = '', int $page = 1, int $perPage = 8): array
    {
        $like   = '%' . $keyword . '%';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = 'SELECT m.id, m.user_id, m.full_name, m.phone, m.address, m.join_date, m.photo,
                       u.email, u.username, u.status
                FROM members m
                JOIN users u ON u.id = m.user_id
                WHERE (m.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3 OR u.username LIKE :q4)
                ORDER BY m.id DESC
                LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $keyword = ''): int
    {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM members m JOIN users u ON u.id = m.user_id
             WHERE (m.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3 OR u.username LIKE :q4)'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
        return (int) $stmt->fetchColumn();
    }

    /* ============================================================
       ADMIN METHODS (BARU v7.0)
       ============================================================ */

    /**
     * Statistik lengkap untuk admin dashboard members.
     *
     * @return array{
     *     total: int,
     *     active: int,
     *     inactive: int,
     *     newThisMonth: int,
     *     newToday: int
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $statusCounts = self::countByStatus();
        $newThisMonth = self::countThisMonth();

        // New today
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM members WHERE DATE(join_date) = CURDATE()"
        );
        $stmt->execute();
        $newToday = (int) $stmt->fetchColumn();

        $stats = [
            'total'        => $statusCounts['total'],
            'active'       => $statusCounts['active'],
            'inactive'     => $statusCounts['inactive'],
            'newThisMonth' => $newThisMonth,
            'newToday'     => $newToday,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * List anggota untuk admin dengan filter lengkap.
     *
     * @param string $status   Filter: all/active/inactive/new
     * @param string $search   Search di nama/email/phone/username
     * @param string $sort     Kolom sort (full_name/join_date/email/username)
     * @param string $order    Arah sort (asc/desc)
     * @param int    $page     Halaman (1-based)
     * @param int    $perPage  Item per halaman
     */
    public static function adminList(
        string $status = '',
        string $search = '',
        string $sort = 'join_date',
        string $order = 'desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT m.*, u.email, u.username, u.status, u.photo
                FROM members m
                JOIN users u ON u.id = m.user_id';

        // Filter status
        if ($status === self::STATUS_ACTIVE) {
            $where[] = 'u.status = :status';
            $params[':status'] = self::STATUS_ACTIVE;
        } elseif ($status === self::STATUS_INACTIVE) {
            $where[] = 'u.status = :status';
            $params[':status'] = self::STATUS_INACTIVE;
        } elseif ($status === 'new') {
            $where[] = "DATE_FORMAT(m.join_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
        }

        // Search
        if ($search !== '') {
            $where[] = '(m.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3 OR u.username LIKE :q4)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort
        $sortColumn = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'join_date';
        // Map column ke table prefix
        if (in_array($sortColumn, ['email', 'username'], true)) {
            $sortColumn = 'u.' . $sortColumn;
        } else {
            $sortColumn = 'm.' . $sortColumn;
        }
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sortColumn $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList (filter yang sama).
     */
    public static function countAdminList(string $status = '', string $search = ''): int
    {
        $params = [];
        $where  = [];

        $sql = 'SELECT COUNT(*) FROM members m JOIN users u ON u.id = m.user_id';

        if ($status === self::STATUS_ACTIVE) {
            $where[] = 'u.status = :status';
            $params[':status'] = self::STATUS_ACTIVE;
        } elseif ($status === self::STATUS_INACTIVE) {
            $where[] = 'u.status = :status';
            $params[':status'] = self::STATUS_INACTIVE;
        } elseif ($status === 'new') {
            $where[] = "DATE_FORMAT(m.join_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
        }

        if ($search !== '') {
            $where[] = '(m.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3 OR u.username LIKE :q4)';
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
       CRUD (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Buat member baru beserta user account (backward compat + extended).
     *
     * Extended fields:
     * - username (optional, auto-generate jika kosong)
     * - password (optional, pakai default)
     * - status (active/inactive)
     * - photo
     */
    public static function createWithUser(array $d): int
    {
        $pdo = Database::getInstance();

        // Generate username jika belum ada
        $username = !empty($d['username']) ? $d['username'] : self::generateUsername($d['full_name']);

        // Validasi uniqueness
        if (self::usernameExists($username)) {
            throw new \RuntimeException("Username '$username' sudah terpakai.");
        }
        if (self::emailExists($d['email'])) {
            throw new \RuntimeException("Email '{$d['email']}' sudah terdaftar.");
        }

        $password = !empty($d['password']) ? $d['password'] : self::DEFAULT_PASSWORD;
        $status   = in_array($d['status'] ?? '', self::STATUSES, true)
            ? $d['status']
            : self::STATUS_ACTIVE;

        $pdo->beginTransaction();
        try {
            // Insert user
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password, role, status, photo) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $username,
                $d['email'],
                password_hash($password, PASSWORD_DEFAULT),
                'member',
                $status,
                $d['photo'] ?? null,
            ]);
            $userId = (int) $pdo->lastInsertId();

            // Insert member
            $stmt = $pdo->prepare(
                'INSERT INTO members (user_id, full_name, phone, address, join_date, photo)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $d['full_name'],
                $d['phone'] ?? '',
                $d['address'] ?? '',
                $d['join_date'] ?? date('Y-m-d'),
                $d['photo'] ?? null,
            ]);
            $memberId = (int) $pdo->lastInsertId();

            $pdo->commit();
            self::clearCache();
            return $memberId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update member (backward compat signature: `updateMember`).
     * Extended: support update username, status, photo.
     */
    public static function updateMember(int $id, array $d): void
    {
        self::update($id, $d);
    }

    /**
     * Update member (nama method baru, lebih konsisten).
     */
    public static function update(int $id, array $d): void
    {
        $pdo = Database::getInstance();

        // Ambil data saat ini
        $current = self::find($id);
        if (!$current) {
            throw new \RuntimeException("Member dengan ID $id tidak ditemukan.");
        }

        // Cek uniqueness jika email/username berubah
        $newEmail = $d['email'] ?? $current['email'];
        $newUsername = $d['username'] ?? $current['username'];

        if ($newEmail !== $current['email'] && self::emailExists($newEmail, $current['user_id'])) {
            throw new \RuntimeException("Email '$newEmail' sudah terdaftar.");
        }
        if ($newUsername !== $current['username'] && self::usernameExists($newUsername, $current['user_id'])) {
            throw new \RuntimeException("Username '$newUsername' sudah terpakai.");
        }

        $pdo->beginTransaction();
        try {
            // Update member
            $pdo->prepare(
                'UPDATE members SET full_name = ?, phone = ?, address = ?, join_date = ?, photo = ? WHERE id = ?'
            )->execute([
                $d['full_name'],
                $d['phone'] ?? '',
                $d['address'] ?? '',
                $d['join_date'],
                $d['photo'] ?? null,
                $id,
            ]);

            // Update user
            $status = in_array($d['status'] ?? '', self::STATUSES, true)
                ? $d['status']
                : ($current['status'] ?? self::STATUS_ACTIVE);

            if (isset($d['photo'])) {
                $pdo->prepare(
                    'UPDATE users SET email = ?, username = ?, status = ?, photo = ? WHERE id = ?'
                )->execute([
                    $newEmail,
                    $newUsername,
                    $status,
                    $d['photo'],
                    $current['user_id'],
                ]);
            } else {
                $pdo->prepare(
                    'UPDATE users SET email = ?, username = ?, status = ? WHERE id = ?'
                )->execute([
                    $newEmail,
                    $newUsername,
                    $status,
                    $current['user_id'],
                ]);
            }

            $pdo->commit();
            self::clearCache();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update password member.
     */
    public static function updatePassword(int $id, string $newPassword): void
    {
        $member = self::find($id);
        if (!$member) return;

        Database::getInstance()->prepare(
            'UPDATE users SET password = ? WHERE id = ?'
        )->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $member['user_id'],
        ]);
    }

    /**
     * Update status member.
     */
    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) return;

        $member = self::find($id);
        if (!$member) return;

        Database::getInstance()->prepare(
            'UPDATE users SET status = ? WHERE id = ?'
        )->execute([$status, $member['user_id']]);

        self::clearCache();
    }

    /**
     * Delete member beserta user (backward compat + cleanup photo).
     */
    public static function deleteWithUser(int $id): void
    {
        $pdo = Database::getInstance();

        // Ambil data untuk cleanup
        $member = self::find($id);

        $pdo->beginTransaction();
        try {
            if ($member) {
                $pdo->prepare('DELETE FROM members WHERE id = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$member['user_id']]);
            }
            $pdo->commit();

            // Cleanup photo file (best-effort, di luar transaction)
            if ($member && !empty($member['photo'])) {
                $path = self::getPhotoPath($member['photo']);
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }

            self::clearCache();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /* ============================================================
       BULK ACTIONS (BARU v7.0)
       ============================================================ */

    /**
     * Bulk delete member beserta user.
     *
     * @return int Jumlah yang berhasil dihapus
     */
    public static function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        $pdo = Database::getInstance();

        // Ambil semua user_id untuk dihapus
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT id, user_id, photo FROM members WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $members = $stmt->fetchAll();

        if (empty($members)) return 0;

        $userIds = array_column($members, 'user_id');
        $photos = array_filter(array_column($members, 'photo'));

        $pdo->beginTransaction();
        try {
            // Delete members
            $stmt = $pdo->prepare("DELETE FROM members WHERE id IN ($placeholders)");
            $stmt->execute(array_values($ids));

            // Delete users
            $userPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
            $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($userPlaceholders)");
            $stmt->execute(array_values($userIds));

            $pdo->commit();

            // Cleanup photos
            foreach ($photos as $photo) {
                $path = self::getPhotoPath($photo);
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }

            self::clearCache();
            return count($members);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Bulk update status.
     *
     * @return int Jumlah yang berhasil diupdate
     */
    public static function bulkUpdateStatus(array $ids, string $status): int
    {
        if (empty($ids)) return 0;
        if (!in_array($status, self::STATUSES, true)) return 0;

        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        $pdo = Database::getInstance();

        // Ambil user_id dari member IDs
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT user_id FROM members WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $userIds = array_column($stmt->fetchAll(), 'user_id');

        if (empty($userIds)) return 0;

        $userPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $pdo->prepare(
            "UPDATE users SET status = ? WHERE id IN ($userPlaceholders)"
        );
        $params = array_merge([$status], array_values($userIds));
        $stmt->execute($params);

        self::clearCache();
        return $stmt->rowCount();
    }

    /* ============================================================
       PROFILE METHODS (USER-LEVEL)
       ============================================================ */

    /**
     * Update profile dari sisi user (backward compat).
     */
    public static function updateProfile(int $userId, array $d): void
    {
        Database::getInstance()->prepare(
            'UPDATE members SET full_name = ?, phone = ?, address = ? WHERE user_id = ?'
        )->execute([
            $d['full_name'],
            $d['phone'] ?? '',
            $d['address'] ?? '',
            $userId,
        ]);

        // Juga update email di users jika diberikan
        if (!empty($d['email'])) {
            Database::getInstance()->prepare(
                'UPDATE users SET email = ? WHERE id = ?'
            )->execute([$d['email'], $userId]);
        }

        self::clearCache();
    }

    /**
     * Update photo profile (backward compat).
     */
    public static function updatePhoto(int $userId, string $photo): void
    {
        // Ambil photo lama untuk cleanup
        $stmt = Database::getInstance()->prepare(
            'SELECT photo FROM members WHERE user_id = :uid LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);
        $oldPhoto = $stmt->fetchColumn();

        // Update di kedua tabel
        Database::getInstance()->prepare('UPDATE members SET photo = ? WHERE user_id = ?')
            ->execute([$photo, $userId]);
        Database::getInstance()->prepare('UPDATE users SET photo = ? WHERE id = ?')
            ->execute([$photo, $userId]);

        // Cleanup photo lama (jika berbeda)
        if ($oldPhoto && $oldPhoto !== $photo) {
            $path = self::getPhotoPath($oldPhoto);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /* ============================================================
       EXPORT & FEED
       ============================================================ */

    /**
     * Semua anggota untuk export CSV/JSON (backward compat + decorator).
     */
    public static function allForExport(): array
    {
        $rows = Database::getInstance()->query(
            'SELECT m.id, m.full_name, m.phone, m.address, m.join_date, m.photo,
                    u.username, u.email, u.status
             FROM members m JOIN users u ON u.id = m.user_id
             ORDER BY m.full_name ASC'
        )->fetchAll();
        return self::decorateAll($rows);
    }

    /**
     * Convert row ke format CSV-friendly.
     */
    public static function toCsvRow(array $row): array
    {
        return [
            'ID'             => $row['id'] ?? '',
            'Username'       => $row['username'] ?? '',
            'Nama Lengkap'   => $row['full_name'] ?? '',
            'Email'          => $row['email'] ?? '',
            'Telepon'        => $row['phone_formatted'] ?? '',
            'Alamat'         => $row['address'] ?? '',
            'Tanggal Gabung' => $row['join_date_short'] ?? '',
            'Status'         => $row['status_label'] ?? '',
        ];
    }

    /**
     * Anggota terbaru untuk feed aktivitas dashboard (backward compat + decorator).
     */
    public static function recent(int $limit = 3): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT m.full_name, m.join_date, m.photo, u.email, u.username, u.status
             FROM members m
             LEFT JOIN users u ON u.id = m.user_id
             ORDER BY m.id DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Registrasi per bulan untuk chart (backward compat).
     *
     * @return array{labels: array, totals: array}
     */
    public static function registrationsPerMonth(int $months = 6): array
    {
        $cacheKey = self::cacheKey('registrations_per_month_' . $months);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $start = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));
        $stmt  = Database::getInstance()->prepare(
            "SELECT DATE_FORMAT(join_date, '%Y-%m') AS ym, COUNT(*) AS total
             FROM members WHERE join_date >= :start GROUP BY ym ORDER BY ym"
        );
        $stmt->execute([':start' => $start]);

        $map = [];
        foreach ($stmt->fetchAll() as $r) {
            $map[$r['ym']] = (int) $r['total'];
        }

        $labels = [];
        $totals = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym       = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime($ym . '-01'));
            $totals[] = $map[$ym] ?? 0;
        }

        $result = ['labels' => $labels, 'totals' => $totals];
        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /* ============================================================
       HELPERS
       ============================================================ */

    /**
     * Get absolute path untuk file photo.
     */
    private static function getPhotoPath(string $filename): ?string
    {
        if (function_exists('public_path')) {
            return public_path(self::PHOTO_DIR . $filename);
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return $base . '/' . self::PHOTO_DIR . $filename;
    }

    /**
     * Generate random password (untuk reset atau invite).
     */
    public static function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}