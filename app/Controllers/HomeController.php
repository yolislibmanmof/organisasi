<?php
// File: app/Controllers/HomeController.php (FINAL v7.0 — EXTENDED + SEO + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Article;
use Models\Event;
use Models\Gallery;
use Models\Member;
use Models\Officer;
use Models\Setting;
use Models\Testimonial;

/**
 * HomeController — Ultimate Edition v7.0
 *
 * Menangani halaman landing publik dengan data lengkap:
 * - Hero stats (members, events, galleries, officers, years)
 * - Featured event + upcoming events
 * - Latest articles
 * - Officers grouped by division
 * - Featured testimonials (top rating)
 * - Gallery grid dengan aspect_ratio
 * - Announcement banner (dari Setting)
 * - SEO meta tags (dari Setting)
 * - Social links (dari Setting)
 * - Resilient: graceful fallback jika model fail
 */
class HomeController
{
    /**
     * Halaman landing / beranda.
     */
    public function index(): void
    {
        // Load settings (untuk announcement, SEO, social)
        Setting::loadAll();

        // Kumpulkan semua data dengan graceful fallback
        $data = $this->gatherLandingData();

        View::render('pages/landing', $data, 'layouts/public');
    }

    /**
     * Kumpulkan semua data yang dibutuhkan view landing v7.0.
     * Setiap block di-try/catch agar jika satu model fail, landing tetap jalan.
     *
     * @return array<string, mixed>
     */
    private function gatherLandingData(): array
    {
        // ========== BASIC DATA ==========
        $data = [
            'title'    => Setting::get('app_name', 'Beranda') . ' — Beranda',
            'loggedIn' => (bool) Session::get('user'),
        ];

        // ========== SEO META (dari Setting) ==========
        $data['seo'] = Setting::getSeoMeta();
        $data['seo']['title'] = $data['title'];
        $data['seo']['og_image'] = Setting::getLogoUrl();
        $data['seo']['og_url'] = function_exists('url') ? url('') : '/';

        // ========== ANNOUNCEMENT BANNER ==========
        $data['announcement'] = [
            'active' => Setting::isAnnouncementActive(),
            'text'   => Setting::get('announcement_text'),
            'link'   => Setting::get('announcement_link'),
        ];

        // ========== HERO STATS ==========
        $data['stats'] = $this->getHeroStats();

        // ========== FEATURED EVENT (Hero section) ==========
        $data['featuredEvent'] = $this->safeCall(
            fn() => Event::featured(),
            null
        );

        // ========== UPCOMING EVENTS (3) ==========
        $data['events'] = $this->safeCall(
            fn() => Event::upcoming(3),
            []
        );

        // ========== LATEST ARTICLES (3) ==========
        $data['articles'] = $this->safeCall(
            fn() => Article::latest(3),
            []
        );

        // ========== TOTAL ARTICLE COUNT (untuk "Lihat Semua") ==========
        $data['totalArticles'] = $this->safeCall(
            fn() => Article::countPublished(),
            0
        );

        // ========== OFFICERS (grouped by division) ==========
        $data['officers'] = $this->safeCall(
            fn() => Officer::groupByDivision(),
            []
        );
        $data['officersFlat'] = $this->safeCall(
            fn() => Officer::all(),
            []
        );
        $data['totalOfficers'] = $this->safeCall(
            fn() => Officer::countAll(),
            0
        );

        // ========== FEATURED TESTIMONIALS (top rating, published) ==========
        $data['testimonials'] = $this->safeCall(
            fn() => Testimonial::featured(6),
            []
        );
        $data['testimonialStats'] = $this->safeCall(
            fn() => [
                'count'  => Testimonial::countAll(),
                'rating' => Testimonial::averageRating(),
            ],
            ['count' => 0, 'rating' => 0]
        );

        // ========== GALLERIES (9 terbaru) ==========
        $data['galleries'] = $this->safeCall(
            fn() => Gallery::all(9),
            []
        );
        $data['totalGalleries'] = $this->safeCall(
            fn() => Gallery::countAll(),
            0
        );

        // ========== SOCIAL LINKS (dari Setting) ==========
        $data['socialLinks'] = Setting::getSocialLinks();

        // ========== ORGANIZATION INFO ==========
        $data['org'] = [
            'name'           => Setting::get('app_name', 'Organisasi'),
            'motto'          => Setting::get('motto'),
            'cabinet_period' => Setting::get('cabinet_period'),
            'visi'           => Setting::get('visi'),
            'misi'           => Setting::get('misi'),
            'logo_url'       => Setting::getLogoUrl(),
            'favicon_url'    => Setting::getFaviconUrl(),
        ];

        // ========== CONTACT INFO (dari Setting) ==========
        $data['contact'] = [
            'email'    => Setting::get('social_email'),
            'phone'    => Setting::get('social_phone'),
            'address'  => Setting::get('social_address'),
        ];

        // ========== META (untuk footer & misc) ==========
        $data['currentYear'] = (int) date('Y');
        $data['appVersion']  = '7.0';

        return $data;
    }

    /**
     * Hitung hero stats lengkap untuk landing page.
     *
     * @return array{
     *     members: int,
     *     officers: int,
     *     events: int,
     *     upcoming: int,
     *     ongoing: int,
     *     articles: int,
     *     galleries: int,
     *     yearsActive: int,
     *     foundedYear: int
     * }
     */
    private function getHeroStats(): array
    {
        // Members
        $members = $this->safeCall(fn() => Member::countAll(), 0);

        // Officers
        $officers = $this->safeCall(fn() => Officer::countAll(), 0);

        // Events breakdown
        $eventStatus = $this->safeCall(
            fn() => Event::countByStatus(),
            ['upcoming' => 0, 'ongoing' => 0, 'done' => 0, 'total' => 0]
        );
        $totalEvents = (int) ($eventStatus['total'] ?? 0);
        $upcoming    = (int) ($eventStatus['upcoming'] ?? 0);
        $ongoing     = (int) ($eventStatus['ongoing'] ?? 0);

        // Articles
        $articles = $this->safeCall(fn() => Article::countPublished(), 0);

        // Galleries
        $galleries = $this->safeCall(fn() => Gallery::countAll(), 0);

        // Years active (dari gallery/event tertua)
        $yearsActive = $this->calculateYearsActive();
        $foundedYear = $this->getFoundedYear();

        return [
            'members'     => $members,
            'officers'    => $officers,
            'events'      => $totalEvents,
            'upcoming'    => $upcoming,
            'ongoing'     => $ongoing,
            'articles'    => $articles,
            'galleries'   => $galleries,
            'yearsActive' => $yearsActive,
            'foundedYear' => $foundedYear,
        ];
    }

    /**
     * Hitung berapa tahun organisasi aktif.
     * Berdasarkan data event/gallery tertua di database.
     */
    private function calculateYearsActive(): int
    {
        $foundedYear = $this->getFoundedYear();
        $currentYear = (int) date('Y');
        return max(1, $currentYear - $foundedYear + 1);
    }

    /**
     * Dapatkan tahun pendirian (fallback ke tahun terawal di database).
     */
    private function getFoundedYear(): int
    {
        // Coba dari setting (jika ada)
        $cabinetPeriod = Setting::get('cabinet_period');
        if (preg_match('/\b(19|20)\d{2}\b/', $cabinetPeriod, $m)) {
            return (int) $m[0];
        }

        // Fallback: cari tahun tertua dari galleries/events
        try {
            $db = \Core\Database::getInstance();

            // Coba galleries
            $stmt = $db->query('SELECT MIN(YEAR(event_date)) FROM galleries WHERE event_date IS NOT NULL');
            $year = $stmt->fetchColumn();
            if ($year && (int) $year > 1900) return (int) $year;

            // Coba events
            $stmt = $db->query('SELECT MIN(YEAR(event_date)) FROM events WHERE event_date IS NOT NULL');
            $year = $stmt->fetchColumn();
            if ($year && (int) $year > 1900) return (int) $year;
        } catch (\Throwable $e) {
            // Ignore
        }

        // Default fallback
        return (int) date('Y') - 5; // Anggap 5 tahun lalu
    }

    /**
     * Safe call wrapper — graceful fallback jika callable gagal.
     *
     * @template T
     * @param callable(): T $callable
     * @param T $fallback
     * @return T
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            // Log ke error log untuk debugging (tidak ganggu user)
            error_log('[HomeController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}