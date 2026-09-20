<?php
// File: app/Models/Event.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Event — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature v5.7 + extension untuk fitur v7.0:
 * - Admin stats, list, search, filter, pagination, sort
 * - Bulk delete
 * - Featured event untuk hero section publik
 * - Decorator kaya (date formatted, relative time, days until, etc)
 * - Cover image support
 * - Multi-day event (end_date)
 * - Cache untuk stats & upcoming
 * - Timezone-aware (Asia/Jakarta)
 */
class Event
{
    /** Status constants */
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ONGOING  = 'ongoing';
    public const STATUS_DONE     = 'done';
    public const STATUSES = [self::STATUS_UPCOMING, self::STATUS_ONGOING, self::STATUS_DONE];

    /** Sort columns whitelist */
    private const SORT_WHITELIST = ['event_date', 'title', 'created_at', 'location'];

    /** Cache TTL (5 menit) */
    private const CACHE_TTL = 300;

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row dengan informasi tambahan untuk UI.
     *
     * Menambahkan:
     * - status (upcoming/ongoing/done) dengan support end_date
     * - date_formatted (format Indonesia: "20 September 2026")
     * - date_short (format pendek: "20 Sep 2026")
     * - date_iso (ISO 8601 untuk <time> tag)
     * - time_formatted ("14:30 WIB")
     * - relative_time ("Besok", "3 hari lagi", "2 minggu lalu")
     * - days_until (integer, negatif jika sudah lewat)
     * - is_today, is_tomorrow, is_past, is_multi_day
     * - initial (untuk avatar creator)
     * - creator_initial
     * - cover_url (jika ada cover_image)
     * - duration_days
     */
    private static function decorate(array $row): array
    {
        $today = date('Y-m-d');
        $eventDate = $row['event_date'] ?? '';
        $endDate   = $row['end_date']   ?? '';

        // Status calculation (support multi-day)
        if ($endDate && $endDate >= $today && $eventDate <= $today) {
            $row['status'] = self::STATUS_ONGOING;
        } elseif ($eventDate > $today) {
            $row['status'] = self::STATUS_UPCOMING;
        } elseif ($endDate && $endDate < $today) {
            $row['status'] = self::STATUS_DONE;
        } elseif ($eventDate === $today) {
            $row['status'] = self::STATUS_ONGOING;
        } else {
            $row['status'] = self::STATUS_DONE;
        }

        // Days until/ago
        if ($eventDate) {
            $diff = (strtotime($eventDate) - strtotime($today)) / 86400;
            $row['days_until'] = (int) $diff;
        } else {
            $row['days_until'] = 0;
        }

        // Boolean flags
        $row['is_today']      = ($eventDate === $today);
        $row['is_tomorrow']   = ($eventDate === date('Y-m-d', strtotime('+1 day')));
        $row['is_past']       = ($row['status'] === self::STATUS_DONE);
        $row['is_upcoming']   = ($row['status'] === self::STATUS_UPCOMING);
        $row['is_ongoing']    = ($row['status'] === self::STATUS_ONGOING);
        $row['is_multi_day']  = ($endDate && $endDate !== $eventDate);

        // Duration
        if ($endDate && $eventDate) {
            $row['duration_days'] = (int) ((strtotime($endDate) - strtotime($eventDate)) / 86400) + 1;
        } else {
            $row['duration_days'] = 1;
        }

        // Date formatting (Bahasa Indonesia)
        if ($eventDate) {
            $ts = strtotime($eventDate);
            $row['date_formatted'] = self::formatDateId($ts);
            $row['date_short']     = date('d M Y', $ts);
            $row['date_iso']       = date('Y-m-d', $ts);
            $row['date_day']       = self::formatDayId($ts);
        } else {
            $row['date_formatted'] = '—';
            $row['date_short']     = '—';
            $row['date_iso']       = '';
            $row['date_day']       = '—';
        }

        // End date formatting
        if ($endDate) {
            $ts = strtotime($endDate);
            $row['end_date_formatted'] = self::formatDateId($ts);
            $row['end_date_short']     = date('d M Y', $ts);
        } else {
            $row['end_date_formatted'] = null;
            $row['end_date_short']     = null;
        }

        // Time formatting
        if (!empty($row['event_time'])) {
            $row['time_formatted'] = date('H:i', strtotime($row['event_time'])) . ' WIB';
        } else {
            $row['time_formatted'] = null;
        }

        // Relative time (Bahasa Indonesia)
        $row['relative_time'] = self::relativeTime($row);

        // Creator info
        $creatorName = $row['creator_name'] ?? ($row['username'] ?? 'Admin');
        $row['creator_initial'] = strtoupper(mb_substr($creatorName, 0, 1));
        $row['creator_name'] = $creatorName;

        // Cover image URL (jika ada helper url())
        if (!empty($row['cover_image']) && function_exists('url')) {
            $row['cover_url'] = url('assets/uploads/events/' . $row['cover_image']);
        } else {
            $row['cover_url'] = null;
        }

        // Slug untuk URL (jika perlu)
        if (!empty($row['title'])) {
            $row['slug'] = self::slugify($row['title']);
        }

        return $row;
    }

    private static function decorateAll(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /**
     * Format tanggal Bahasa Indonesia.
     * Contoh: "20 September 2026"
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
     * Format nama hari Bahasa Indonesia.
     */
    private static function formatDayId(int $timestamp): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[(int) date('w', $timestamp)];
    }

    /**
     * Relative time Bahasa Indonesia untuk event.
     * Contoh: "Besok", "3 hari lagi", "2 minggu lalu", "Hari ini"
     */
    private static function relativeTime(array $row): string
    {
        if ($row['is_today']) return 'Hari ini';
        if ($row['is_tomorrow']) return 'Besok';

        $days = $row['days_until'] ?? 0;

        if ($days > 0) {
            if ($days === 1) return 'Besok';
            if ($days < 7) return "$days hari lagi";
            if ($days < 14) return 'Minggu depan';
            if ($days < 30) return (int) floor($days / 7) . ' minggu lagi';
            if ($days < 60) return 'Bulan depan';
            if ($days < 365) return (int) floor($days / 30) . ' bulan lagi';
            return (int) floor($days / 365) . ' tahun lagi';
        }

        $days = abs($days);
        if ($days === 1) return 'Kemarin';
        if ($days < 7) return "$days hari lalu";
        if ($days < 14) return 'Minggu lalu';
        if ($days < 30) return (int) floor($days / 7) . ' minggu lalu';
        if ($days < 60) return 'Bulan lalu';
        if ($days < 365) return (int) floor($days / 30) . ' bulan lalu';
        return (int) floor($days / 365) . ' tahun lalu';
    }

    /**
     * Slugify judul untuk URL.
     */
    private static function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    private static function cacheKey(string $suffix): string
    {
        return 'events|' . $suffix;
    }

    private static function clearCache(): void
    {
        Cache::flush('events');
    }

    /* ============================================================
       PUBLIC METHODS
       ============================================================ */

    /**
     * Daftar event untuk halaman publik: aktif dulu, lalu arsip.
     * Backward compatible dengan signature v5.7.
     *
     * @return array{active: array, done: array}
     */
    public static function publicList(): array
    {
        $cacheKey = self::cacheKey('public_list');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $rows = Database::getInstance()->query(
            'SELECT e.*, u.username AS creator_name
             FROM events e LEFT JOIN users u ON u.id = e.created_by
             ORDER BY e.event_date DESC'
        )->fetchAll();
        $rows = self::decorateAll($rows);

        $active = array_values(array_filter($rows, fn($r) => $r['status'] !== self::STATUS_DONE));
        $done   = array_values(array_filter($rows, fn($r) => $r['status'] === self::STATUS_DONE));

        // Sort active: ongoing first, then upcoming by date asc
        usort($active, function ($a, $b) {
            // Ongoing first
            if ($a['is_ongoing'] && !$b['is_ongoing']) return -1;
            if (!$a['is_ongoing'] && $b['is_ongoing']) return 1;
            // Then by date asc (closest first)
            return strcmp($a['event_date'], $b['event_date']);
        });

        $result = [
            'active' => $active,
            'done'   => array_slice($done, 0, 6),
        ];

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Featured event (event ongoing atau upcoming terdekat).
     * Untuk hero section public page.
     */
    public static function featured(): ?array
    {
        $cacheKey = self::cacheKey('featured');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $today = date('Y-m-d');
        $db = Database::getInstance();

        // Prioritas: ongoing > upcoming terdekat
        $stmt = $db->prepare(
            'SELECT e.*, u.username AS creator_name
             FROM events e LEFT JOIN users u ON u.id = e.created_by
             WHERE e.event_date <= :t1 AND (e.end_date IS NULL OR e.end_date >= :t2)
             ORDER BY e.event_date DESC
             LIMIT 1'
        );
        $stmt->execute([':t1' => $today, ':t2' => $today]);
        $row = $stmt->fetch();

        if (!$row) {
            // Fallback: upcoming terdekat
            $stmt = $db->prepare(
                'SELECT e.*, u.username AS creator_name
                 FROM events e LEFT JOIN users u ON u.id = e.created_by
                 WHERE e.event_date > :t
                 ORDER BY e.event_date ASC
                 LIMIT 1'
            );
            $stmt->execute([':t' => $today]);
            $row = $stmt->fetch();
        }

        $result = $row === false ? null : self::decorate($row);
        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Search event (backward compat signature).
     */
    public static function search(string $keyword = '', int $page = 1, int $perPage = 6): array
    {
        $like   = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT e.*, u.username AS creator_name
                FROM events e LEFT JOIN users u ON u.id = e.created_by
                WHERE (e.title LIKE :q1 OR e.location LIKE :q2 OR e.description LIKE :q3)
                ORDER BY e.event_date DESC
                LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $keyword = ''): int
    {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM events e WHERE (e.title LIKE :q1 OR e.location LIKE :q2 OR e.description LIKE :q3)'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count per status (digabung jadi 1 query untuk efisiensi).
     * Backward compatible dengan signature v5.7.
     *
     * @return array{upcoming: int, ongoing: int, done: int, total: int}
     */
    public static function countByStatus(): array
    {
        $cacheKey = self::cacheKey('count_by_status');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $today = date('Y-m-d');
        $db = Database::getInstance();

        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN event_date > :t1 THEN 1 ELSE 0 END) AS upcoming,
                    SUM(CASE WHEN (event_date <= :t2 AND (end_date IS NULL OR end_date >= :t3)) OR event_date = :t4 THEN 1 ELSE 0 END) AS ongoing,
                    SUM(CASE WHEN event_date < :t5 AND (end_date IS NULL OR end_date < :t6) THEN 1 ELSE 0 END) AS done
                FROM events";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t1' => $today, ':t2' => $today, ':t3' => $today,
            ':t4' => $today, ':t5' => $today, ':t6' => $today,
        ]);
        $row = $stmt->fetch();

        $result = [
            'upcoming' => (int) ($row['upcoming'] ?? 0),
            'ongoing'  => (int) ($row['ongoing'] ?? 0),
            'done'     => (int) ($row['done'] ?? 0),
            'total'    => (int) ($row['total'] ?? 0),
        ];

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Count event bulan ini (backward compat).
     */
    public static function countThisMonth(): int
    {
        $cacheKey = self::cacheKey('count_this_month_' . date('Ym'));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM events WHERE DATE_FORMAT(event_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Event upcoming (untuk homepage sidebar, cached).
     * Backward compat signature.
     */
    public static function upcoming(int $limit = 3): array
    {
        $cacheKey = self::cacheKey('upcoming_' . $limit);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT e.*, u.username AS creator_name
             FROM events e LEFT JOIN users u ON u.id = e.created_by
             WHERE e.event_date >= :today
             ORDER BY e.event_date ASC, e.event_time ASC
             LIMIT ' . (int) $limit
        );
        $stmt->execute([':today' => date('Y-m-d')]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Event terbaru (untuk dashboard activity feed).
     * Backward compat signature, dengan decorator.
     */
    public static function recent(int $limit = 3): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT e.*, u.username AS creator_name
             FROM events e LEFT JOIN users u ON u.id = e.created_by
             ORDER BY e.id DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Find by ID (publik, backward compat).
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT e.*, u.username AS creator_name FROM events e
             LEFT JOIN users u ON u.id = e.created_by WHERE e.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Create event (backward compat signature + extended).
     * Extended: support cover_image dan end_date.
     */
    public static function create(array $d, int $creatorId): int
    {
        $coverImage = $d['cover_image'] ?? null;
        $endDate    = !empty($d['end_date']) ? $d['end_date'] : null;
        $eventTime  = !empty($d['event_time']) ? $d['event_time'] : null;

        if ($coverImage !== null) {
            $stmt = Database::getInstance()->prepare(
                'INSERT INTO events (title, description, location, event_date, end_date, event_time, cover_image, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $d['title'], $d['description'], $d['location'],
                $d['event_date'], $endDate, $eventTime,
                $coverImage, $creatorId,
            ]);
        } else {
            $stmt = Database::getInstance()->prepare(
                'INSERT INTO events (title, description, location, event_date, end_date, event_time, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $d['title'], $d['description'], $d['location'],
                $d['event_date'], $endDate, $eventTime, $creatorId,
            ]);
        }

        self::clearCache();
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update event (backward compat signature + extended).
     * Catatan: method lama bernama `updateMember`, dipertahankan untuk backward compat.
     */
    public static function updateMember(int $id, array $d): void
    {
        self::update($id, $d);
    }

    /**
     * Update event (nama method baru, lebih konsisten).
     */
    public static function update(int $id, array $d): void
    {
        $coverImage = $d['cover_image'] ?? null;
        $endDate    = !empty($d['end_date']) ? $d['end_date'] : null;
        $eventTime  = !empty($d['event_time']) ? $d['event_time'] : null;

        if ($coverImage !== null) {
            Database::getInstance()->prepare(
                'UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, end_date = ?, event_time = ?, cover_image = ? WHERE id = ?'
            )->execute([
                $d['title'], $d['description'], $d['location'],
                $d['event_date'], $endDate, $eventTime, $coverImage, $id,
            ]);
        } else {
            Database::getInstance()->prepare(
                'UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, end_date = ?, event_time = ? WHERE id = ?'
            )->execute([
                $d['title'], $d['description'], $d['location'],
                $d['event_date'], $endDate, $eventTime, $id,
            ]);
        }

        self::clearCache();
    }

    /**
     * Delete event (backward compat + cleanup cover image).
     */
    public static function delete(int $id): void
    {
        // Ambil cover image dulu untuk cleanup
        $row = self::find($id);
        Database::getInstance()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);

        // Cleanup file cover (best-effort)
        if ($row && !empty($row['cover_image'])) {
            $path = public_path('assets/uploads/events/' . $row['cover_image']);
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /* ============================================================
       ADMIN METHODS (BARU v7.0)
       ============================================================ */

    /**
     * Statistik lengkap untuk admin dashboard events.
     *
     * @return array{
     *     total: int,
     *     upcoming: int,
     *     ongoing: int,
     *     done: int,
     *     this_month: int,
     *     next_7_days: int
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $statusCounts = self::countByStatus();
        $thisMonth    = self::countThisMonth();

        // Next 7 days (cached inline)
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM events
             WHERE event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        $stmt->execute();
        $next7Days = (int) $stmt->fetchColumn();

        $stats = [
            'total'       => $statusCounts['total'],
            'upcoming'    => $statusCounts['upcoming'],
            'ongoing'     => $statusCounts['ongoing'],
            'done'        => $statusCounts['done'],
            'this_month'  => $thisMonth,
            'next_7_days' => $next7Days,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * List event untuk admin (filter + search + pagination + sort).
     *
     * @param string $status   Filter: upcoming/ongoing/done/all
     * @param string $search   Search di title/location/description
     * @param string $sort     Kolom sort (event_date/title/created_at/location)
     * @param string $order    Arah sort (asc/desc)
     * @param int    $page     Halaman (1-based)
     * @param int    $perPage  Item per halaman
     */
    public static function adminList(
        string $status = '',
        string $search = '',
        string $sort = 'event_date',
        string $order = 'desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT e.*, u.username AS creator_name
                FROM events e LEFT JOIN users u ON u.id = e.created_by';

        $today = date('Y-m-d');

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            if ($status === self::STATUS_UPCOMING) {
                $where[] = 'e.event_date > :today_up';
                $params[':today_up'] = $today;
            } elseif ($status === self::STATUS_ONGOING) {
                $where[] = '((e.event_date <= :today_on1 AND (e.end_date IS NULL OR e.end_date >= :today_on2)) OR e.event_date = :today_on3)';
                $params[':today_on1'] = $today;
                $params[':today_on2'] = $today;
                $params[':today_on3'] = $today;
            } elseif ($status === self::STATUS_DONE) {
                $where[] = '(e.event_date < :today_dn AND (e.end_date IS NULL OR e.end_date < :today_dn2))';
                $params[':today_dn']  = $today;
                $params[':today_dn2'] = $today;
            }
        }

        if ($search !== '') {
            $where[] = '(e.title LIKE :q1 OR e.location LIKE :q2 OR e.description LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort
        if (!in_array($sort, self::SORT_WHITELIST, true)) $sort = 'event_date';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY e.$sort $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

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

        $sql = 'SELECT COUNT(*) FROM events e';

        $today = date('Y-m-d');

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            if ($status === self::STATUS_UPCOMING) {
                $where[] = 'e.event_date > :today_up';
                $params[':today_up'] = $today;
            } elseif ($status === self::STATUS_ONGOING) {
                $where[] = '((e.event_date <= :today_on1 AND (e.end_date IS NULL OR e.end_date >= :today_on2)) OR e.event_date = :today_on3)';
                $params[':today_on1'] = $today;
                $params[':today_on2'] = $today;
                $params[':today_on3'] = $today;
            } elseif ($status === self::STATUS_DONE) {
                $where[] = '(e.event_date < :today_dn AND (e.end_date IS NULL OR e.end_date < :today_dn2))';
                $params[':today_dn']  = $today;
                $params[':today_dn2'] = $today;
            }
        }

        if ($search !== '') {
            $where[] = '(e.title LIKE :q1 OR e.location LIKE :q2 OR e.description LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Bulk delete event.
     */
    public static function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        // Cleanup cover images
        foreach ($ids as $id) {
            $row = self::find($id);
            if ($row && !empty($row['cover_image'])) {
                $path = public_path('assets/uploads/events/' . $row['cover_image']);
                if (is_string($path) && is_file($path)) {
                    @unlink($path);
                }
            }
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM events WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));

        self::clearCache();
        return $stmt->rowCount();
    }

    /**
     * Find by ID tanpa filter (untuk admin edit).
     * Berbeda dengan find() yang mungkin ada filter tambahan di masa depan.
     */
    public static function findAny(int $id): ?array
    {
        return self::find($id);
    }

    /* ============================================================
       CLEANUP / MAINTENANCE
       ============================================================ */

    /**
     * Hapus event done yang lebih lama dari N hari.
     * Untuk cron job / maintenance.
     */
    public static function cleanupOldDone(int $olderThanDays = 365): int
    {
        $cutoff = date('Y-m-d', strtotime("-$olderThanDays days"));
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM events WHERE event_date < :cutoff AND (end_date IS NULL OR end_date < :cutoff2)"
        );
        $stmt->execute([':cutoff' => $cutoff, ':cutoff2' => $cutoff]);

        self::clearCache();
        return $stmt->rowCount();
    }
}