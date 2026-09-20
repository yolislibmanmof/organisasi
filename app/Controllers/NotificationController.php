<?php
// File: app/Controllers/NotificationController.php (FINAL v7.0 — EXTENDED + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Article;
use Models\Census;
use Models\Event;
use Models\Gallery;
use Models\Member;
use Models\Testimonial;

/**
 * NotificationController — Ultimate Edition v7.0
 *
 * Menyediakan feed notifikasi real-time untuk admin dashboard:
 * - Pending counts (sensus + testimonials)
 * - Activity feed gabungan dari 6 modul (members, articles, events, galleries, testimonials, census)
 * - Decorated items (avatar, photo_url, relative_time, ISO timestamp)
 * - URL builder otomatis
 * - Graceful fallback per-modul
 * - Configurable limit
 * - Priority flags (urgent/normal)
 * - Audit logging
 */
class NotificationController
{
    /** Default jumlah item feed */
    private const DEFAULT_LIMIT = 8;

    /** Max limit yang diizinkan */
    private const MAX_LIMIT = 50;

    /**
     * API endpoint: feed notifikasi untuk admin.
     * GET /admin/notifications/feed?limit=8
     */
    public function feed(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $limit = max(1, min(self::MAX_LIMIT, (int) ($_GET['limit'] ?? self::DEFAULT_LIMIT)));

        // ===== PENDING COUNTS (untuk badge notifikasi) =====
        $pendingCensus = $this->safeCall(fn() => Census::countNew(), 0);

        $testiStats = $this->safeCall(fn() => Testimonial::adminStats(), []);
        $pendingTestimonials = (int) ($testiStats['pending'] ?? 0);

        $totalPending = $pendingCensus + $pendingTestimonials;

        // ===== BUILD FEED ITEMS =====
        $items = [];

        // 1. Sensus menunggu verifikasi (PRIORITAS TINGGI jika > 0)
        if ($pendingCensus > 0) {
            $items[] = $this->buildItem(
                icon: 'ph-clipboard-text',
                grad: 'grad-6',
                initial: 'S',
                title: $pendingCensus . ' entri sensus menunggu verifikasi',
                sub: 'Tinjau di menu Sensus Anggota',
                link: 'admin/census',
                time: time(),
                type: 'alert',
                priority: 'high',
                badge: $pendingCensus,
            );
        }

        // 2. Testimonials menunggu review (PRIORITAS TINGGI jika > 0)
        if ($pendingTestimonials > 0) {
            $items[] = $this->buildItem(
                icon: 'ph-chat-circle-text',
                grad: 'grad-5',
                initial: 'T',
                title: $pendingTestimonials . ' testimoni menunggu review',
                sub: 'Tinjau di menu Testimoni',
                link: 'admin/content?tab=testimonials&status=pending',
                time: time(),
                type: 'alert',
                priority: 'high',
                badge: $pendingTestimonials,
            );
        }

        // 3. Anggota baru (recent 3)
        $recentMembers = $this->safeCall(fn() => Member::recent(3), []);
        foreach ($recentMembers as $m) {
            $joinTs = !empty($m['join_date']) ? strtotime($m['join_date']) : time();
            $items[] = $this->buildItem(
                icon: 'ph-user-plus',
                grad: $m['avatar_color'] ?? 'grad-1',
                initial: $m['initial'] ?? 'M',
                photo_url: $m['photo_url'] ?? null,
                title: 'Anggota baru bergabung',
                sub: ($m['full_name'] ?? '—') . (!empty($m['email']) ? ' · ' . $m['email'] : ''),
                link: 'admin/members',
                time: $joinTs,
                type: 'member',
                priority: 'normal',
            );
        }

        // 4. Artikel diterbitkan (recent 3)
        $recentArticles = $this->safeCall(fn() => Article::recent(3), []);
        foreach ($recentArticles as $a) {
            $createdTs = !empty($a['created_at']) ? strtotime($a['created_at']) : time();
            $author = !empty($a['author_name']) ? ' oleh ' . $a['author_name'] : '';
            $items[] = $this->buildItem(
                icon: 'ph-newspaper',
                grad: 'grad-2',
                initial: 'A',
                photo_url: $a['cover_url'] ?? null,
                title: 'Artikel diterbitkan',
                sub: ($a['title'] ?? '—') . $author,
                link: 'admin/articles',
                time: $createdTs,
                type: 'article',
                priority: 'normal',
            );
        }

        // 5. Event dijadwalkan (recent 3)
        $recentEvents = $this->safeCall(fn() => Event::recent(3), []);
        foreach ($recentEvents as $e) {
            $eventTs = !empty($e['event_date']) ? strtotime($e['event_date']) : time();
            $loc = !empty($e['location']) ? ' · ' . $e['location'] : '';
            $items[] = $this->buildItem(
                icon: 'ph-calendar-plus',
                grad: 'grad-4',
                initial: 'E',
                photo_url: $e['cover_url'] ?? null,
                title: 'Event dijadwalkan',
                sub: ($e['title'] ?? '—') . $loc,
                link: 'admin/events',
                time: $eventTs,
                type: 'event',
                priority: 'normal',
            );
        }

        // 6. Galeri baru diunggah (recent 2)
        $recentGalleries = $this->safeCall(fn() => Gallery::all(2), []);
        foreach ($recentGalleries as $g) {
            $eventTs = !empty($g['event_date']) ? strtotime($g['event_date']) : time();
            $items[] = $this->buildItem(
                icon: 'ph-images',
                grad: 'grad-3',
                initial: $g['initial'] ?? 'G',
                photo_url: $g['image_url'] ?? null,
                title: 'Galeri baru diunggah',
                sub: ($g['title'] ?? '—') . (!empty($g['location']) ? ' · ' . $g['location'] : ''),
                link: 'admin/content?tab=galleries',
                time: $eventTs,
                type: 'gallery',
                priority: 'normal',
            );
        }

        // 7. Testimoni baru (recent 2, semua status)
        $recentTesti = $this->safeCall(fn() => Testimonial::latest(2, false), []);
        foreach ($recentTesti as $t) {
            $createdTs = !empty($t['created_at']) ? strtotime($t['created_at']) : time();
            $statusLabel = $t['status_label'] ?? 'Submitted';
            $items[] = $this->buildItem(
                icon: 'ph-chat-circle-text',
                grad: $t['avatar_color'] ?? 'grad-5',
                initial: $t['initial'] ?? 'T',
                photo_url: $t['photo_url'] ?? null,
                title: 'Testimoni baru (' . $statusLabel . ')',
                sub: ($t['name'] ?? '—') . ' · ' . ($t['role'] ?? ''),
                link: 'admin/content?tab=testimonials',
                time: $createdTs,
                type: 'testimonial',
                priority: ($t['status'] ?? '') === 'pending' ? 'high' : 'normal',
            );
        }

        // ===== SORT BY TIME DESC (terbaru di atas) =====
        usort($items, fn($a, $b) => $b['time_ts'] - $a['time_ts']);

        // Prioritas tinggi selalu di atas (stable sort dengan secondary key)
        usort($items, function ($a, $b) {
            $pA = $a['priority'] === 'high' ? 1 : 0;
            $pB = $b['priority'] === 'high' ? 1 : 0;
            if ($pA !== $pB) return $pB - $pA;
            return 0; // maintain time order
        });

        // Slice ke limit yang diminta
        $items = array_slice($items, 0, $limit);

        // Audit log (siapa yang akses feed)
        $adminUser = Session::get('user');
        error_log(sprintf(
            '[NotificationController] Feed accessed: items=%d pending=%d by=%s',
            count($items),
            $totalPending,
            $adminUser['username'] ?? 'unknown'
        ));

        json([
            'ok'      => true,
            'items'   => $items,
            'unread'  => $totalPending,
            'pending' => [
                'census'       => $pendingCensus,
                'testimonials' => $pendingTestimonials,
            ],
            'generated_at' => date('c'),
        ]);
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Build single feed item dengan struktur konsisten.
     */
    private function buildItem(
        string $icon,
        string $grad,
        string $initial,
        string $title,
        string $sub,
        string $link,
        int $time,
        string $type = 'info',
        string $priority = 'normal',
        ?string $photo_url = null,
        ?int $badge = null,
    ): array {
        // Build full URL
        $fullUrl = function_exists('url') ? url($link) : '/' . ltrim($link, '/');

        // Relative time (Bahasa Indonesia)
        $relativeTime = $this->formatRelativeTime($time);

        return [
            'icon'           => $icon,
            'grad'           => $grad,
            'initial'        => $initial,
            'photo_url'      => $photo_url,
            'title'          => $title,
            'sub'            => $sub,
            'link'           => $link,
            'url'            => $fullUrl,
            'time'           => date('H:i', $time),
            'time_formatted' => $relativeTime,
            'time_iso'       => date('c', $time),
            'time_ts'        => $time,
            'type'           => $type,
            'priority'       => $priority,
            'badge'          => $badge,
        ];
    }

    /**
     * Format relative time Bahasa Indonesia.
     */
    private function formatRelativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;

        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 172800) return 'Kemarin';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        if ($diff < 1209600) return 'Minggu lalu';
        if ($diff < 2592000) return (int) floor($diff / 604800) . ' minggu lalu';
        if ($diff < 5184000) return 'Bulan lalu';
        if ($diff < 31536000) return (int) floor($diff / 2592000) . ' bulan lalu';
        return (int) floor($diff / 31536000) . ' tahun lalu';
    }

    /**
     * Safe call wrapper — graceful fallback.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[NotificationController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}