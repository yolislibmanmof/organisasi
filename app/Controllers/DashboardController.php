<?php
// File: app/Controllers/DashboardController.php (FINAL v7.0 — EXTENDED + COMPREHENSIVE + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Article;
use Models\Census;
use Models\Event;
use Models\Gallery;
use Models\Member;
use Models\Officer;
use Models\Setting;
use Models\Testimonial;
use Models\User;

/**
 * DashboardController — Ultimate Edition v7.0
 *
 * Dashboard admin dengan data komprehensif:
 * - 8+ stat cards (members, users, events, articles, officers, galleries, testimonials, census)
 * - Pending counts (untuk notification badges)
 * - Activity feed gabungan (5 item terbaru dari semua modul)
 * - Quick actions (shortcut ke form create)
 * - Chart data endpoints (registrations, events, articles)
 * - System info (PHP, cache, disk)
 * - Graceful fallback untuk setiap modul
 */
class DashboardController
{
    /**
     * Halaman utama dashboard.
     */
    public function index(): void
    {
        Auth::handle();
        AdminOnly::handle();

        Setting::loadAll();

        $user = Session::get('user');
        $appName = Setting::get('app_name', 'Organisasi');

        // ========== STAT CARDS (comprehensive) ==========
        $stats = $this->gatherStats();

        // ========== PENDING COUNTS (notification badges) ==========
        $pending = $this->gatherPendingCounts();

        // ========== ACTIVITY FEED ==========
        $feed = $this->gatherActivityFeed();

        // ========== QUICK ACTIONS ==========
        $quickActions = $this->getQuickActions();

        // ========== WELCOME MESSAGE (time-based) ==========
        $greeting = $this->getGreeting();

        View::render('pages/dashboard', [
            'title'        => 'Dashboard',
            'user'         => $user,
            'greeting'     => $greeting,

            // Stats
            'totalMembers'     => $stats['members'],
            'totalUsers'       => $stats['users'],
            'activeUsers'      => $stats['active_users'],
            'totalEvents'      => $stats['events'],
            'eventThisMonth'   => $stats['events_this_month'],
            'upcomingEvents'   => $stats['upcoming_events'],
            'totalArticles'    => $stats['articles'],
            'totalGalleries'   => $stats['galleries'],
            'totalOfficers'    => $stats['officers'],
            'totalTestimonials' => $stats['testimonials'],
            'totalCensus'      => $stats['census'],
            'newMembersMonth'  => $stats['new_members_month'],

            // Pending counts (badges)
            'pendingCensus'       => $pending['census'],
            'pendingTestimonials' => $pending['testimonials'],
            'totalPending'        => $pending['total'],

            // Feed & actions
            'feed'         => $feed,
            'quickActions' => $quickActions,

            // Meta
            'appName'     => $appName,
            'currentYear' => (int) date('Y'),
        ], 'layouts/app');
    }

    /**
     * API endpoint: stats registrations chart (6 bulan terakhir).
     * GET /admin/dashboard/stats-registrations
     */
    public function statsRegistrations(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $months = max(3, min(12, (int) ($_GET['months'] ?? 6)));

        $memberData = $this->safeCall(
            fn() => Member::registrationsPerMonth($months),
            ['labels' => [], 'totals' => []]
        );

        json([
            'type'   => 'registrations',
            'months' => $months,
            'data'   => $memberData,
        ]);
    }

    /**
     * API endpoint: stats events chart (6 bulan terakhir).
     */
    public function statsEvents(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $months = max(3, min(12, (int) ($_GET['months'] ?? 6)));

        $data = $this->getEventsPerMonth($months);

        json([
            'type'   => 'events',
            'months' => $months,
            'data'   => $data,
        ]);
    }

    /**
     * API endpoint: stats articles chart (6 bulan terakhir).
     */
    public function statsArticles(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $months = max(3, min(12, (int) ($_GET['months'] ?? 6)));

        $data = $this->getArticlesPerMonth($months);

        json([
            'type'   => 'articles',
            'months' => $months,
            'data'   => $data,
        ]);
    }

    /**
     * API endpoint: system info (PHP, memory, cache, disk).
     * Untuk admin panel "System Status".
     */
    public function systemInfo(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $uploadDir = public_path('assets/uploads/');
        $diskFree = is_dir($uploadDir) ? disk_free_space($uploadDir) : 0;
        $diskTotal = is_dir($uploadDir) ? disk_total_space($uploadDir) : 0;

        json([
            'php_version'    => PHP_VERSION,
            'php_sapi'       => PHP_SAPI,
            'memory_limit'   => ini_get('memory_limit'),
            'memory_usage'   => round(memory_get_usage(true) / 1048576, 2) . ' MB',
            'memory_peak'    => round(memory_get_peak_usage(true) / 1048576, 2) . ' MB',
            'max_upload'     => ini_get('upload_max_filesize'),
            'post_max'       => ini_get('post_max_size'),
            'disk_free'      => round($diskFree / 1073741824, 2) . ' GB',
            'disk_total'     => round($diskTotal / 1073741824, 2) . ' GB',
            'disk_percent'   => $diskTotal > 0 ? round(($diskFree / $diskTotal) * 100, 1) : 0,
            'server_time'    => date('Y-m-d H:i:s'),
            'timezone'       => date_default_timezone_get(),
        ]);
    }

    /* ============================================================
       PRIVATE: DATA GATHERING
       ============================================================ */

    /**
     * Kumpulkan semua stats dengan graceful fallback.
     */
    private function gatherStats(): array
    {
        // Members
        $memberStats = $this->safeCall(fn() => Member::adminStats(), []);
        $members = (int) ($memberStats['total'] ?? $this->safeCall(fn() => Member::countAll(), 0));
        $newMembersMonth = (int) ($memberStats['newThisMonth'] ?? $this->safeCall(fn() => Member::countThisMonth(), 0));

        // Users
        $userStats = $this->safeCall(fn() => User::adminStats(), []);
        $users = (int) ($userStats['total'] ?? $this->safeCall(fn() => User::countAll(), 0));
        $activeUsers = (int) ($userStats['active'] ?? $this->safeCall(fn() => User::countActive(), 0));

        // Events
        $eventStats = $this->safeCall(fn() => Event::adminStats(), []);
        $events = (int) ($eventStats['total'] ?? 0);
        $eventsThisMonth = (int) ($eventStats['this_month'] ?? $this->safeCall(fn() => Event::countThisMonth(), 0));
        $upcomingEvents = (int) ($eventStats['upcoming'] ?? 0);

        // Articles
        $articleStats = $this->safeCall(fn() => Article::adminStats(), []);
        $articles = (int) ($articleStats['total'] ?? $this->safeCall(fn() => Article::countAll(), 0));

        // Galleries
        $galleryStats = $this->safeCall(fn() => Gallery::adminStats(), []);
        $galleries = (int) ($galleryStats['total'] ?? $this->safeCall(fn() => Gallery::countAll(), 0));

        // Officers
        $officers = $this->safeCall(fn() => Officer::countAll(), 0);

        // Testimonials
        $testimonialStats = $this->safeCall(fn() => Testimonial::adminStats(), []);
        $testimonials = (int) ($testimonialStats['total'] ?? $this->safeCall(fn() => Testimonial::countAll(), 0));

        // Census
        $censusStats = $this->safeCall(fn() => Census::adminStats(), []);
        $census = (int) ($censusStats['total'] ?? $this->safeCall(fn() => count(Census::all()), 0));

        return [
            'members'            => $members,
            'users'              => $users,
            'active_users'       => $activeUsers,
            'events'             => $events,
            'events_this_month'  => $eventsThisMonth,
            'upcoming_events'    => $upcomingEvents,
            'articles'           => $articles,
            'galleries'          => $galleries,
            'officers'           => $officers,
            'testimonials'       => $testimonials,
            'census'             => $census,
            'new_members_month'  => $newMembersMonth,
        ];
    }

    /**
     * Kumpulkan pending counts untuk notification badges.
     */
    private function gatherPendingCounts(): array
    {
        $censusPending = $this->safeCall(
            fn() => Census::countNew(),
            0
        );

        $testiStats = $this->safeCall(fn() => Testimonial::adminStats(), []);
        $testiPending = (int) ($testiStats['pending'] ?? 0);

        return [
            'census'       => $censusPending,
            'testimonials' => $testiPending,
            'total'        => $censusPending + $testiPending,
        ];
    }

    /**
     * Kumpulkan activity feed dari semua modul (5 item terbaru).
     * Pakai decorator dari model untuk consistency.
     */
    private function gatherActivityFeed(): array
    {
        $feed = [];

        // ===== Members (joined recently) =====
        $recentMembers = $this->safeCall(fn() => Member::recent(3), []);
        foreach ($recentMembers as $m) {
            $feed[] = [
                'type'           => 'member',
                'icon'           => 'ph-user-plus',
                'grad'           => $m['avatar_color'] ?? 'grad-1',
                'initial'        => $m['initial'] ?? 'M',
                'photo_url'      => $m['photo_url'] ?? null,
                'title'          => 'Anggota baru bergabung',
                'sub'            => ($m['full_name'] ?? '—') . (!empty($m['email']) ? ' · ' . $m['email'] : ''),
                'time'           => $m['join_date'] ?? date('Y-m-d'),
                'time_formatted' => $m['relative_time'] ?? 'Baru saja',
                'time_iso'       => !empty($m['join_date']) ? date('c', strtotime($m['join_date'])) : '',
                'url'            => function_exists('url') ? url('admin/members') : '/admin/members',
            ];
        }

        // ===== Events (recently created/scheduled) =====
        $recentEvents = $this->safeCall(fn() => Event::recent(3), []);
        foreach ($recentEvents as $e) {
            $loc = !empty($e['location']) ? ' · ' . $e['location'] : '';
            $feed[] = [
                'type'           => 'event',
                'icon'           => 'ph-calendar-plus',
                'grad'           => 'grad-4',
                'initial'        => 'E',
                'photo_url'      => $e['cover_url'] ?? null,
                'title'          => 'Event dijadwalkan',
                'sub'            => ($e['title'] ?? '—') . $loc,
                'time'           => $e['event_date'] ?? date('Y-m-d'),
                'time_formatted' => $e['relative_time'] ?? 'Hari ini',
                'time_iso'       => !empty($e['event_date']) ? date('c', strtotime($e['event_date'])) : '',
                'url'            => function_exists('url') ? url('admin/events') : '/admin/events',
            ];
        }

        // ===== Articles (recently published) =====
        $recentArticles = $this->safeCall(fn() => Article::recent(3), []);
        foreach ($recentArticles as $a) {
            $author = !empty($a['author_name']) ? ' oleh ' . $a['author_name'] : '';
            $feed[] = [
                'type'           => 'article',
                'icon'           => 'ph-newspaper',
                'grad'           => 'grad-2',
                'initial'        => 'A',
                'photo_url'      => $a['cover_url'] ?? null,
                'title'          => 'Artikel diterbitkan',
                'sub'            => ($a['title'] ?? '—') . $author,
                'time'           => $a['created_at'] ?? date('Y-m-d H:i:s'),
                'time_formatted' => $a['relative_time'] ?? 'Baru saja',
                'time_iso'       => !empty($a['created_at']) ? date('c', strtotime($a['created_at'])) : '',
                'url'            => function_exists('url') ? url('admin/articles') : '/admin/articles',
            ];
        }

        // ===== Galleries (recently uploaded) =====
        $recentGalleries = $this->safeCall(fn() => Gallery::all(2), []);
        foreach ($recentGalleries as $g) {
            $feed[] = [
                'type'           => 'gallery',
                'icon'           => 'ph-images',
                'grad'           => 'grad-3',
                'initial'        => $g['initial'] ?? 'G',
                'photo_url'      => $g['image_url'] ?? null,
                'title'          => 'Galeri baru diunggah',
                'sub'            => ($g['title'] ?? '—') . (!empty($g['location']) ? ' · ' . $g['location'] : ''),
                'time'           => $g['event_date'] ?? date('Y-m-d'),
                'time_formatted' => $g['relative_time'] ?? 'Baru saja',
                'time_iso'       => !empty($g['event_date']) ? date('c', strtotime($g['event_date'])) : '',
                'url'            => function_exists('url') ? url('admin/galleries') : '/admin/galleries',
            ];
        }

        // ===== Testimonials (recently submitted) =====
        $recentTesti = $this->safeCall(fn() => Testimonial::latest(2, false), []);
        foreach ($recentTesti as $t) {
            $statusLabel = $t['status_label'] ?? 'Submitted';
            $feed[] = [
                'type'           => 'testimonial',
                'icon'           => 'ph-chat-circle-text',
                'grad'           => $t['avatar_color'] ?? 'grad-5',
                'initial'        => $t['initial'] ?? 'T',
                'photo_url'      => $t['photo_url'] ?? null,
                'title'          => 'Testimoni baru (' . $statusLabel . ')',
                'sub'            => ($t['name'] ?? '—') . ' · ' . ($t['role'] ?? ''),
                'time'           => $t['created_at'] ?? date('Y-m-d H:i:s'),
                'time_formatted' => $t['relative_time'] ?? 'Baru saja',
                'time_iso'       => !empty($t['created_at']) ? date('c', strtotime($t['created_at'])) : '',
                'url'            => function_exists('url') ? url('admin/testimonials') : '/admin/testimonials',
            ];
        }

        // ===== Census (recently submitted) =====
        $recentCensus = $this->safeCall(fn() => array_slice(Census::all(), 0, 2), []);
        foreach ($recentCensus as $c) {
            $purposeLabel = $c['purpose_label'] ?? 'Submission';
            $feed[] = [
                'type'           => 'census',
                'icon'           => 'ph-identification-card',
                'grad'           => 'grad-6',
                'initial'        => $c['initial'] ?? 'C',
                'photo_url'      => null,
                'title'          => 'Sensus baru (' . $purposeLabel . ')',
                'sub'            => ($c['full_name'] ?? '—') . ' · ' . ($c['reference'] ?? ''),
                'time'           => $c['created_at'] ?? date('Y-m-d H:i:s'),
                'time_formatted' => $c['relative_time'] ?? 'Baru saja',
                'time_iso'       => !empty($c['created_at']) ? date('c', strtotime($c['created_at'])) : '',
                'url'            => function_exists('url') ? url('admin/census') : '/admin/census',
            ];
        }

        // Sort by time desc
        usort($feed, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));

        return array_slice($feed, 0, 8);
    }

    /**
     * Quick actions untuk dashboard.
     */
    private function getQuickActions(): array
    {
        $url = function_exists('url')
            ? fn($p) => url($p)
            : fn($p) => '/' . ltrim($p, '/');

        return [
            [
                'label' => 'Tulis Artikel',
                'icon'  => 'ph-newspaper',
                'url'   => $url('admin/articles'),
                'color' => 'grad-2',
            ],
            [
                'label' => 'Buat Event',
                'icon'  => 'ph-calendar-plus',
                'url'   => $url('admin/events'),
                'color' => 'grad-4',
            ],
            [
                'label' => 'Tambah Anggota',
                'icon'  => 'ph-user-plus',
                'url'   => $url('admin/members'),
                'color' => 'grad-1',
            ],
            [
                'label' => 'Upload Galeri',
                'icon'  => 'ph-images',
                'url'   => $url('admin/galleries'),
                'color' => 'grad-3',
            ],
            [
                'label' => 'Proses Sensus',
                'icon'  => 'ph-identification-card',
                'url'   => $url('admin/census'),
                'color' => 'grad-6',
                'badge' => null, // akan diisi dari pendingCensus di view
            ],
            [
                'label' => 'Review Testimoni',
                'icon'  => 'ph-chat-circle-text',
                'url'   => $url('admin/testimonials'),
                'color' => 'grad-5',
                'badge' => null,
            ],
        ];
    }

    /**
     * Get greeting berdasarkan waktu.
     */
    private function getGreeting(): string
    {
        $hour = (int) date('H');
        if ($hour < 11) return 'Selamat Pagi';
        if ($hour < 15) return 'Selamat Siang';
        if ($hour < 18) return 'Selamat Sore';
        return 'Selamat Malam';
    }

    /**
     * Get events per month (untuk chart).
     */
    private function getEventsPerMonth(int $months): array
    {
        try {
            $start = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));
            $stmt = \Core\Database::getInstance()->prepare(
                "SELECT DATE_FORMAT(event_date, '%Y-%m') AS ym, COUNT(*) AS total
                 FROM events WHERE event_date >= :start GROUP BY ym ORDER BY ym"
            );
            $stmt->execute([':start' => $start]);

            $map = [];
            foreach ($stmt->fetchAll() as $r) {
                $map[$r['ym']] = (int) $r['total'];
            }

            $labels = [];
            $totals = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $ym = date('Y-m', strtotime("-$i months"));
                $labels[] = date('M Y', strtotime($ym . '-01'));
                $totals[] = $map[$ym] ?? 0;
            }

            return ['labels' => $labels, 'totals' => $totals];
        } catch (\Throwable $e) {
            error_log('[DashboardController::getEventsPerMonth] ' . $e->getMessage());
            return ['labels' => [], 'totals' => []];
        }
    }

    /**
     * Get articles per month (untuk chart).
     */
    private function getArticlesPerMonth(int $months): array
    {
        try {
            $start = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));
            $stmt = \Core\Database::getInstance()->prepare(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
                 FROM articles WHERE created_at >= :start GROUP BY ym ORDER BY ym"
            );
            $stmt->execute([':start' => $start]);

            $map = [];
            foreach ($stmt->fetchAll() as $r) {
                $map[$r['ym']] = (int) $r['total'];
            }

            $labels = [];
            $totals = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $ym = date('Y-m', strtotime("-$i months"));
                $labels[] = date('M Y', strtotime($ym . '-01'));
                $totals[] = $map[$ym] ?? 0;
            }

            return ['labels' => $labels, 'totals' => $totals];
        } catch (\Throwable $e) {
            error_log('[DashboardController::getArticlesPerMonth] ' . $e->getMessage());
            return ['labels' => [], 'totals' => []];
        }
    }

    /**
     * Safe call wrapper.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[DashboardController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}