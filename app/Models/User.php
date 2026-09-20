<?php
// File: app/Models/User.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model User — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature lama + extension untuk fitur v7.0:
 * - Decorator kaya (initial, photo_url, avatar_color, status_label, role_label)
 * - Admin stats (total/active/inactive/admin/member)
 * - Admin list dengan filter role/status/search/sort/pagination
 * - Bulk actions (delete & update status)
 * - Cache untuk stats & counts
 * - Photo support dengan cleanup
 * - Export capabilities
 * - Helper methods (verify, findByEmail/Username, etc)
 */
class User
{
    /** Role constants */
    public const ROLE_ADMIN  = 'admin';
    public const ROLE_MEMBER = 'member';
    public const ROLES = [self::ROLE_ADMIN, self::ROLE_MEMBER];

    /** Status constants */
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];

    /** Cache TTL (5 menit) */
    private const CACHE_TTL = 300;

    /** Upload directory untuk photo profile */
    private const PHOTO_DIR = 'assets/uploads/users/';

    /** Sort columns whitelist */
    private const SORT_WHITELIST = ['username', 'email', 'role', 'status', 'created_at', 'id'];

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row dengan informasi tambahan untuk UI.
     *
     * Menambahkan:
     * - initial (huruf pertama nama/username)
     * - photo_url (URL lengkap photo)
     * - avatar_color (gradient class berdasarkan hash)
     * - display_name (nama yang akan ditampilkan)
     * - status_label ("Aktif" / "Non-aktif")
     * - status_color (CSS class: ok/warn)
     * - role_label ("Admin" / "Anggota")
     * - role_color (CSS class: accent/primary)
     * - is_admin, is_active (boolean flags)
     * - created_date_formatted ("20 September 2026")
     * - created_date_short ("20 Sep 2026")
     * - created_date_iso (ISO 8601)
     * - relative_time ("3 hari lalu")
     * - email_masked (untuk privacy di list)
     */
    private static function decorate(array $row): array
    {
        // Display name (nama lengkap dari members, fallback ke username)
        $displayName = $row['full_name'] ?? ($row['name'] ?? ($row['username'] ?? 'User'));
        $row['display_name'] = $displayName;

        // Initial untuk avatar placeholder
        $row['initial'] = strtoupper(mb_substr(trim($displayName), 0, 1));

        // Avatar color (deterministic berdasarkan username)
        $row['avatar_color'] = self::avatarColorClass($row['username'] ?? $displayName);

        // Photo URL
        if (!empty($row['photo'])) {
            $row['photo_url'] = function_exists('url')
                ? url(self::PHOTO_DIR . $row['photo'])
                : '/' . self::PHOTO_DIR . $row['photo'];
        } else {
            $row['photo_url'] = null;
        }

        // Status handling
        $status = $row['status'] ?? self::STATUS_ACTIVE;
        $row['status'] = $status;
        $row['status_label'] = ($status === self::STATUS_ACTIVE) ? 'Aktif' : 'Non-aktif';
        $row['status_color'] = ($status === self::STATUS_ACTIVE) ? 'ok' : 'warn';
        $row['is_active'] = ($status === self::STATUS_ACTIVE);

        // Role handling
        $role = $row['role'] ?? self::ROLE_MEMBER;
        $row['role'] = $role;
        $row['role_label'] = ($role === self::ROLE_ADMIN) ? 'Admin' : 'Anggota';
        $row['role_color'] = ($role === self::ROLE_ADMIN) ? 'accent' : 'primary';
        $row['is_admin'] = ($role === self::ROLE_ADMIN);

        // Created date formatting
        if (!empty($row['created_at'])) {
            $ts = strtotime($row['created_at']);
            if ($ts !== false) {
                $row['created_date_formatted'] = self::formatDateId($ts);
                $row['created_date_short']     = date('d M Y', $ts);
                $row['created_date_iso']       = date('Y-m-d', $ts);
                $row['relative_time']          = self::relativeTime($ts);
            } else {
                $row['created_date_formatted'] = '—';
                $row['created_date_short']     = '—';
                $row['created_date_iso']       = '';
                $row['relative_time']          = '—';
            }
        } else {
            $row['created_date_formatted'] = '—';
            $row['created_date_short']     = '—';
            $row['created_date_iso']       = '';
            $row['relative_time']          = '—';
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

        // Remove password dari decorator output (security)
        unset($row['password']);

        return $row;
    }

    private static function decorateAll(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /**
     * Deterministic avatar color class.
     * Return: grad-1 sampai grad-6
     */
    private static function avatarColorClass(string $seed): string
    {
        $hash = crc32(strtolower(trim($seed)));
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
        return 'users|' . $suffix;
    }

    private static function clearCache(): void
    {
        Cache::flush('users');
    }

    /* ============================================================
       FINDERS (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Find by username atau email (untuk login).
     * Backward compat signature v5.6, dengan decorator.
     *
     * Catatan: password TIDAK di-strip di decorator untuk verify().
     * Jika perlu raw row dengan password, gunakan findByIdRaw().
     */
    public static function findByLogin(string $login): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM users WHERE username = :u OR email = :em LIMIT 1'
        );
        $stmt->execute([':u' => $login, ':em' => $login]);
        $row = $stmt->fetch();
        return $row === false ? null : $row; // Raw untuk login (butuh password)
    }

    /**
     * Find by ID (backward compat + decorator).
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Find by ID tanpa decorator (raw row dengan password).
     * Untuk operasi internal yang butuh password (verify, etc).
     */
    public static function findByIdRaw(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Find by email.
     */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM users WHERE email = :e LIMIT 1'
        );
        $stmt->execute([':e' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Find by username.
     */
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM users WHERE username = :u LIMIT 1'
        );
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Ambil semua user (dengan decorator).
     * Gunakan hati-hati untuk dataset besar.
     */
    public static function all(): array
    {
        $rows = Database::getInstance()->query(
            'SELECT * FROM users ORDER BY id ASC'
        )->fetchAll();
        return self::decorateAll($rows);
    }

    /* ============================================================
       COUNTS (CACHED)
       ============================================================ */

    /**
     * Total semua user (backward compat + cached).
     */
    public static function countAll(): int
    {
        $cacheKey = self::cacheKey('count_all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM users')
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Jumlah user aktif (backward compat + cached).
     */
    public static function countActive(): int
    {
        $cacheKey = self::cacheKey('count_active');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query("SELECT COUNT(*) FROM users WHERE status = 'active'")
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Jumlah user inactive (cached).
     */
    public static function countInactive(): int
    {
        $cacheKey = self::cacheKey('count_inactive');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Count by role (untuk breakdown admin/member).
     *
     * @return array{admin: int, member: int, total: int}
     */
    public static function countByRole(): array
    {
        $cacheKey = self::cacheKey('count_by_role');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin,
                SUM(CASE WHEN role = 'member' THEN 1 ELSE 0 END) AS member
             FROM users"
        );
        $row = $stmt->fetch();

        $result = [
            'total'  => (int) ($row['total'] ?? 0),
            'admin'  => (int) ($row['admin'] ?? 0),
            'member' => (int) ($row['member'] ?? 0),
        ];

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Count by status.
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
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) AS inactive
             FROM users"
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

    /* ============================================================
       UNIQUENESS CHECKS
       ============================================================ */

    /**
     * Cek email sudah terdaftar.
     * Backward compat: `emailExistsExcept` dipertahankan, signature baru lebih flexible.
     *
     * @param string $email
     * @param int $excludeId Exclude user ID (untuk edit)
     */
    public static function emailExists(string $email, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE email = :e AND id <> :id'
            );
            $stmt->execute([':e' => strtolower(trim($email)), ':id' => $excludeId]);
        } else {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE email = :e'
            );
            $stmt->execute([':e' => strtolower(trim($email))]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Backward compat signature lama.
     */
    public static function emailExistsExcept(string $email, int $exceptId): bool
    {
        return self::emailExists($email, $exceptId);
    }

    /**
     * Cek username sudah terdaftar.
     */
    public static function usernameExists(string $username, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE username = :u AND id <> :id'
            );
            $stmt->execute([':u' => $username, ':id' => $excludeId]);
        } else {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users WHERE username = :u'
            );
            $stmt->execute([':u' => $username]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    /* ============================================================
       ADMIN STATS
       ============================================================ */

    /**
     * Statistik lengkap untuk admin dashboard.
     *
     * @return array{
     *     total: int,
     *     active: int,
     *     inactive: int,
     *     admin: int,
     *     member: int,
     *     newThisMonth: int,
     *     newToday: int
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::getInstance();

        // Single query untuk total + status + role breakdown
        $stmt = $db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) AS inactive,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin,
                SUM(CASE WHEN role = 'member' THEN 1 ELSE 0 END) AS member
             FROM users"
        );
        $row = $stmt->fetch();

        // New this month
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM users
             WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );
        $stmt->execute();
        $newThisMonth = (int) $stmt->fetchColumn();

        // New today
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()"
        );
        $stmt->execute();
        $newToday = (int) $stmt->fetchColumn();

        $stats = [
            'total'        => (int) ($row['total'] ?? 0),
            'active'       => (int) ($row['active'] ?? 0),
            'inactive'     => (int) ($row['inactive'] ?? 0),
            'admin'        => (int) ($row['admin'] ?? 0),
            'member'       => (int) ($row['member'] ?? 0),
            'newThisMonth' => $newThisMonth,
            'newToday'     => $newToday,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /* ============================================================
       ADMIN LIST
       ============================================================ */

    /**
     * List users untuk admin dengan filter lengkap.
     *
     * @param string $role    Filter role (admin/member/all)
     * @param string $status  Filter status (active/inactive/all)
     * @param string $search  Search di username/email
     * @param string $sort    Kolom sort
     * @param string $order   Arah sort (asc/desc)
     * @param int    $page    Halaman (1-based)
     * @param int    $perPage Item per halaman
     */
    public static function adminList(
        string $role = '',
        string $status = '',
        string $search = '',
        string $sort = 'created_at',
        string $order = 'desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT * FROM users';

        if ($role !== '' && in_array($role, self::ROLES, true)) {
            $where[] = 'role = :role';
            $params[':role'] = $role;
        }

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(username LIKE :q1 OR email LIKE :q2)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort
        if (!in_array($sort, self::SORT_WHITELIST, true)) $sort = 'created_at';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sort $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList.
     */
    public static function countAdminList(
        string $role = '',
        string $status = '',
        string $search = ''
    ): int {
        $params = [];
        $where  = [];

        $sql = 'SELECT COUNT(*) FROM users';

        if ($role !== '' && in_array($role, self::ROLES, true)) {
            $where[] = 'role = :role';
            $params[':role'] = $role;
        }

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(username LIKE :q1 OR email LIKE :q2)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ============================================================
       CRUD
       ============================================================ */

    /**
     * Create user baru.
     *
     * @param array $d Data user (username, email, password, role, status, photo)
     * @return int ID user baru
     */
    public static function create(array $d): int
    {
        // Validasi uniqueness
        if (self::usernameExists($d['username'])) {
            throw new \RuntimeException("Username '{$d['username']}' sudah terpakai.");
        }
        if (self::emailExists($d['email'])) {
            throw new \RuntimeException("Email '{$d['email']}' sudah terdaftar.");
        }

        $role   = in_array($d['role'] ?? '', self::ROLES, true) ? $d['role'] : self::ROLE_MEMBER;
        $status = in_array($d['status'] ?? '', self::STATUSES, true) ? $d['status'] : self::STATUS_ACTIVE;

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO users (username, email, password, role, status, photo, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $d['username'],
            $d['email'],
            password_hash($d['password'], PASSWORD_DEFAULT),
            $role,
            $status,
            $d['photo'] ?? null,
        ]);

        self::clearCache();
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update user.
     * Support update: username, email, role, status, photo.
     * Password diupdate via `updatePassword()` terpisah.
     */
    public static function update(int $id, array $d): void
    {
        $current = self::findByIdRaw($id);
        if (!$current) {
            throw new \RuntimeException("User dengan ID $id tidak ditemukan.");
        }

        // Cek uniqueness jika berubah
        $newUsername = $d['username'] ?? $current['username'];
        $newEmail = $d['email'] ?? $current['email'];

        if ($newUsername !== $current['username'] && self::usernameExists($newUsername, $id)) {
            throw new \RuntimeException("Username '$newUsername' sudah terpakai.");
        }
        if ($newEmail !== $current['email'] && self::emailExists($newEmail, $id)) {
            throw new \RuntimeException("Email '$newEmail' sudah terdaftar.");
        }

        $role   = in_array($d['role'] ?? '', self::ROLES, true) ? $d['role'] : $current['role'];
        $status = in_array($d['status'] ?? '', self::STATUSES, true) ? $d['status'] : $current['status'];

        if (isset($d['photo'])) {
            Database::getInstance()->prepare(
                'UPDATE users SET username = ?, email = ?, role = ?, status = ?, photo = ? WHERE id = ?'
            )->execute([
                $newUsername,
                $newEmail,
                $role,
                $status,
                $d['photo'],
                $id,
            ]);

            // Cleanup photo lama jika berbeda
            if (!empty($current['photo']) && $current['photo'] !== $d['photo']) {
                $path = self::getPhotoPath($current['photo']);
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }
        } else {
            Database::getInstance()->prepare(
                'UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?'
            )->execute([
                $newUsername,
                $newEmail,
                $role,
                $status,
                $id,
            ]);
        }

        self::clearCache();
    }

    /**
     * Update email (backward compat).
     */
    public static function updateEmail(int $id, string $email): void
    {
        if (self::emailExists($email, $id)) {
            throw new \RuntimeException("Email '$email' sudah terdaftar.");
        }
        Database::getInstance()->prepare('UPDATE users SET email = ? WHERE id = ?')
            ->execute([$email, $id]);
        self::clearCache();
    }

    /**
     * Update password (backward compat + improved).
     * Terima plain password, akan di-hash otomatis.
     */
    public static function updatePassword(int $id, string $passwordOrHash): void
    {
        // Cek apakah sudah di-hash (bcrypt $2y$... atau $argon2id$)
        $isHashed = str_starts_with($passwordOrHash, '$2y$')
                 || str_starts_with($passwordOrHash, '$2b$')
                 || str_starts_with($passwordOrHash, '$argon2id$')
                 || str_starts_with($passwordOrHash, '$argon2i$');

        $hash = $isHashed
            ? $passwordOrHash
            : password_hash($passwordOrHash, PASSWORD_DEFAULT);

        Database::getInstance()->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([$hash, $id]);
    }

    /**
     * Update status user.
     */
    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) return;
        Database::getInstance()->prepare('UPDATE users SET status = ? WHERE id = ?')
            ->execute([$status, $id]);
        self::clearCache();
    }

    /**
     * Update role user.
     */
    public static function updateRole(int $id, string $role): void
    {
        if (!in_array($role, self::ROLES, true)) return;
        Database::getInstance()->prepare('UPDATE users SET role = ? WHERE id = ?')
            ->execute([$role, $id]);
        self::clearCache();
    }

    /**
     * Update photo profile.
     */
    public static function updatePhoto(int $id, string $photo): void
    {
        $current = self::findByIdRaw($id);
        if (!$current) return;

        Database::getInstance()->prepare('UPDATE users SET photo = ? WHERE id = ?')
            ->execute([$photo, $id]);

        // Cleanup photo lama jika berbeda
        if (!empty($current['photo']) && $current['photo'] !== $photo) {
            $path = self::getPhotoPath($current['photo']);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /**
     * Delete user dengan cleanup photo.
     */
    public static function delete(int $id): void
    {
        $current = self::findByIdRaw($id);

        Database::getInstance()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

        // Cleanup photo
        if ($current && !empty($current['photo'])) {
            $path = self::getPhotoPath($current['photo']);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /* ============================================================
       AUTH
       ============================================================ */

    /**
     * Verifikasi password user (backward compat).
     * Gunakan raw row untuk dapat password.
     */
    public static function verify(int $id, string $password): bool
    {
        $u = self::findByIdRaw($id);
        if ($u === null) return false;
        if (empty($u['password'])) return false;
        return password_verify($password, $u['password']);
    }

    /**
     * Verifikasi login (username/email + password).
     *
     * @return array|null User row jika valid, null jika gagal
     */
    public static function verifyLogin(string $login, string $password): ?array
    {
        $user = self::findByLogin($login);
        if ($user === null) return null;
        if (empty($user['password'])) return null;
        if (!password_verify($password, $user['password'])) return null;

        // Rehash jika diperlukan (algorithm upgrade)
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            self::updatePassword($user['id'], $password);
        }

        return self::decorate($user);
    }

    /* ============================================================
       BULK ACTIONS
       ============================================================ */

    /**
     * Bulk delete users dengan cleanup photo.
     * Proteksi: tidak bisa delete diri sendiri (optional).
     *
     * @param array $ids
     * @param int $currentUserId ID user yang sedang login (untuk proteksi)
     * @return int Jumlah yang berhasil dihapus
     */
    public static function bulkDelete(array $ids, int $currentUserId = 0): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));

        // Jangan delete diri sendiri
        if ($currentUserId > 0) {
            $ids = array_filter($ids, fn($id) => $id !== $currentUserId);
        }
        if (empty($ids)) return 0;

        // Ambil data untuk cleanup
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT id, photo FROM users WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $rows = $stmt->fetchAll();

        if (empty($rows)) return 0;

        // Delete
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM users WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $affected = $stmt->rowCount();

        // Cleanup photos
        foreach ($rows as $row) {
            if (!empty($row['photo'])) {
                $path = self::getPhotoPath($row['photo']);
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }
        }

        self::clearCache();
        return $affected;
    }

    /**
     * Bulk update status.
     *
     * @return int Jumlah yang berhasil diupdate
     */
    public static function bulkUpdateStatus(array $ids, string $status, int $currentUserId = 0): int
    {
        if (empty($ids)) return 0;
        if (!in_array($status, self::STATUSES, true)) return 0;

        $ids = array_map('intval', array_filter($ids));
        if ($currentUserId > 0) {
            $ids = array_filter($ids, fn($id) => $id !== $currentUserId);
        }
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE users SET status = ? WHERE id IN ($placeholders)"
        );
        $params = array_merge([$status], array_values($ids));
        $stmt->execute($params);

        self::clearCache();
        return $stmt->rowCount();
    }

    /**
     * Bulk update role.
     *
     * @return int Jumlah yang berhasil diupdate
     */
    public static function bulkUpdateRole(array $ids, string $role, int $currentUserId = 0): int
    {
        if (empty($ids)) return 0;
        if (!in_array($role, self::ROLES, true)) return 0;

        $ids = array_map('intval', array_filter($ids));
        if ($currentUserId > 0) {
            $ids = array_filter($ids, fn($id) => $id !== $currentUserId);
        }
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE users SET role = ? WHERE id IN ($placeholders)"
        );
        $params = array_merge([$role], array_values($ids));
        $stmt->execute($params);

        self::clearCache();
        return $stmt->rowCount();
    }

    /* ============================================================
       EXPORT
       ============================================================ */

    /**
     * Semua user untuk export CSV (dengan decorator).
     */
    public static function allForExport(): array
    {
        $rows = Database::getInstance()->query(
            'SELECT * FROM users ORDER BY role DESC, username ASC'
        )->fetchAll();
        return self::decorateAll($rows);
    }

    /**
     * Convert row ke format CSV-friendly.
     */
    public static function toCsvRow(array $row): array
    {
        return [
            'ID'          => $row['id'] ?? '',
            'Username'    => $row['username'] ?? '',
            'Email'       => $row['email'] ?? '',
            'Nama'        => $row['display_name'] ?? '',
            'Role'        => $row['role_label'] ?? '',
            'Status'      => $row['status_label'] ?? '',
            'Terdaftar'   => $row['created_date_short'] ?? '',
        ];
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
     * Generate random password (untuk reset/invite).
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

    /**
     * Generate username unik dari nama (mirip Member::generateUsername).
     */
    public static function generateUsername(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $base  = strtolower(($parts[0] ?? 'user') . '.' . ($parts[1] ?? ''));
        $base  = preg_replace('/[^a-z0-9.]/', '', rtrim($base, '.')) ?: 'user';

        $candidate = $base;
        $i = 1;
        while (self::usernameExists($candidate)) {
            $candidate = $base . $i++;
            if ($i > 100) break; // Safety
        }
        return $candidate;
    }

    /**
     * Hitung jumlah admin minimum (untuk proteksi delete).
     * Jangan izinkan delete admin terakhir.
     */
    public static function countAdmins(): int
    {
        $roleCounts = self::countByRole();
        return $roleCounts['admin'];
    }

    /**
     * Proteksi: cek apakah bisa delete user (jangan delete admin terakhir).
     */
    public static function canDelete(int $userId): bool
    {
        $user = self::findByIdRaw($userId);
        if (!$user) return false;

        // Jika bukan admin, boleh dihapus
        if ($user['role'] !== self::ROLE_ADMIN) return true;

        // Jika admin, cek jumlah admin lain
        return self::countAdmins() > 1;
    }

    /* ============================================================
       MAINTENANCE
       ============================================================ */

    /**
     * Cleanup orphan files (file photo tanpa user di DB).
     *
     * @param bool $dryRun Jika true, hanya return list file
     * @return array List file orphan
     */
    public static function cleanupOrphanFiles(bool $dryRun = true): array
    {
        $uploadPath = self::getPhotoPath('');
        if (!$uploadPath || !is_dir($uploadPath)) return [];

        $dbFiles = array_column(
            Database::getInstance()->query('SELECT photo FROM users')->fetchAll(),
            'photo'
        );
        $dbFiles = array_filter($dbFiles);

        $diskFiles = array_diff(scandir($uploadPath), ['.', '..']);
        $orphans = array_diff($diskFiles, $dbFiles);

        if (!$dryRun) {
            foreach ($orphans as $file) {
                $path = $uploadPath . '/' . $file;
                if (is_file($path)) @unlink($path);
            }
        }

        return array_values($orphans);
    }
}