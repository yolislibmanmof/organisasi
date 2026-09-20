<?php
// File: app/Controllers/GalleryController.php (FINAL v7.0.2 — VIEW & MODEL FALLBACK)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Gallery;
use Models\Setting;

/**
 * GalleryController — Ultimate Edition v7.0.2
 *
 * PATCH v7.0.2 (perbaikan 500 di /galeri):
 * 1. Fallback nama view: 'pages/galeri' → 'pages/gallery' (nama lama).
 * 2. Fallback method model via method_exists():
 *    - adminList()        → publicAll()
 *    - countAdminList()   → countPublic()
 *    - yearsWithCount()   → uniqueYears() + countByYear()
 *    - locationsWithCount() → agregasi dari all()
 *    Sehingga controller ini aman baik untuk Models\Gallery v5.9 maupun v7.0.
 */
class GalleryController
{
    /**
     * Halaman publik galeri kegiatan.
     * GET /galeri?year=2026&location=Jakarta&q=cari&halaman=2
     */
    public function index(): void
    {
        Setting::loadAll();

        $appName = Setting::get('app_name', 'Organisasi');

        // ===== PARAMETER =====
        $year     = trim((string) ($_GET['year'] ?? ''));
        $location = trim((string) ($_GET['location'] ?? ''));
        $search   = trim((string) ($_GET['q'] ?? ''));
        $page     = max(1, (int) ($_GET['halaman'] ?? 1));
        $perPage  = 12;

        // Validasi year (harus 4 digit angka)
        if ($year !== '' && !preg_match('/^\d{4}$/', $year)) {
            $year = '';
        }

        // ===== COUNT & PAGINATION (dengan fallback model) =====
        $total = $this->safeCall(function () use ($year, $location, $search) {
            if (method_exists(Gallery::class, 'countAdminList')) {
                return Gallery::countAdminList($year, $location, $search);
            }
            // Fallback model v5.9
            if ($year === '' && $location === '' && $search === '') {
                return Gallery::countPublic();
            }
            return count($this->legacyFilter($year, $location, $search));
        }, 0);

        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);

        // ===== DATA GALERI (dengan fallback model) =====
        $galleries = $this->safeCall(function () use ($year, $location, $search, $page, $perPage) {
            if (method_exists(Gallery::class, 'adminList')) {
                return Gallery::adminList($year, $location, $search, $page, $perPage);
            }
            // Fallback model v5.9: filter manual di PHP
            if ($year === '' && $location === '' && $search === '') {
                return Gallery::publicAll($page, $perPage);
            }
            $filtered = $this->legacyFilter($year, $location, $search);
            return array_slice($filtered, ($page - 1) * $perPage, $perPage);
        }, []);

        // ===== YEAR COUNTS (dengan fallback) =====
        $yearCounts = $this->safeCall(function () {
            if (method_exists(Gallery::class, 'yearsWithCount')) {
                return Gallery::yearsWithCount();
            }
            $out = [];
            foreach (Gallery::uniqueYears() as $y) {
                $out[(int) $y] = Gallery::countByYear((int) $y);
            }
            return $out;
        }, []);

        // ===== LOCATION COUNTS (dengan fallback) =====
        $locationCounts = $this->safeCall(function () {
            if (method_exists(Gallery::class, 'locationsWithCount')) {
                return Gallery::locationsWithCount();
            }
            $out = [];
            foreach (Gallery::all() as $g) {
                $loc = trim((string) ($g['location'] ?? ''));
                if ($loc === '') continue;
                $out[$loc] = ($out[$loc] ?? 0) + 1;
            }
            arsort($out);
            return $out;
        }, []);

        // ===== FEATURED =====
        $featured = $this->safeCall(
            fn() => $this->getFeaturedGallery($galleries),
            null
        );

        // ===== UNIQUE YEARS & LOCATIONS =====
        $uniqueYears     = $this->safeCall(fn() => Gallery::uniqueYears(), []);
        $uniqueLocations = $this->safeCall(fn() => Gallery::uniqueLocations(), []);

        // ===== SEO META =====
        $seo = Setting::getSeoMeta();
        $pageTitle = 'Galeri Kegiatan';
        if ($year !== '')     $pageTitle = "Galeri Tahun $year";
        if ($location !== '') $pageTitle = "Galeri di $location";
        if ($search !== '')   $pageTitle = "Pencarian Galeri: $search";

        $seo['title'] = $pageTitle . ' — ' . $appName;
        if (empty($seo['description'])) {
            $seo['description'] = "Dokumentasi kegiatan dan acara $appName. Total $total foto galeri tersedia.";
        }
        $seo['og_image'] = $featured['image_url'] ?? Setting::getLogoUrl();
        $seo['og_url'] = function_exists('url')
            ? url('galeri' . $this->buildQueryString(['year' => $year, 'location' => $location, 'q' => $search]))
            : '/galeri';

        // ===== FILTER INDICATOR =====
        $activeFilters = [];
        if ($year !== '')     $activeFilters[] = "Tahun: $year";
        if ($location !== '') $activeFilters[] = "Lokasi: $location";
        if ($search !== '')   $activeFilters[] = "Pencarian: \"$search\"";

        // ===== ANNOUNCEMENT =====
        $announcement = [
            'active' => Setting::isAnnouncementActive(),
            'text'   => Setting::get('announcement_text'),
            'link'   => Setting::get('announcement_link'),
        ];

        // ===== PATCH v7.0.2: FALLBACK NAMA VIEW =====
        $viewFile = View::exists('pages/galeri')
            ? 'pages/galeri'
            : 'pages/gallery';   // nama view lama (v5.x)

        View::render($viewFile, [
            'title'          => $pageTitle,
            'loggedIn'       => (bool) Session::get('user'),

            // Data
            'galleries'      => $galleries,
            'featured'       => $featured,

            // Pagination
            'page'           => $page,
            'pages'          => $pages,
            'total'          => $total,
            'perPage'        => $perPage,

            // Filter
            'year'           => $year,
            'location'       => $location,
            'search'         => $search,
            'activeFilters'  => $activeFilters,

            // Filter options
            'yearCounts'     => $yearCounts,
            'locationCounts' => $locationCounts,
            'uniqueYears'    => $uniqueYears,
            'uniqueLocations' => $uniqueLocations,

            // SEO & meta
            'seo'            => $seo,
            'announcement'   => $announcement,

            // Org info
            'org' => [
                'name'        => $appName,
                'logo_url'    => Setting::getLogoUrl(),
                'favicon_url' => Setting::getFaviconUrl(),
            ],
            'socialLinks'    => Setting::getSocialLinks(),
            'currentYear'    => (int) date('Y'),
        ], 'layouts/public');
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Fallback filter manual untuk Models\Gallery v5.9
     * (yang belum punya adminList/countAdminList).
     *
     * @return array<int, array>
     */
    private function legacyFilter(string $year, string $location, string $search): array
    {
        $rows = $this->safeCall(fn() => Gallery::all(), []);
        $q = mb_strtolower($search);

        return array_values(array_filter($rows, function ($g) use ($year, $location, $q) {
            if ($year !== '') {
                $gy = !empty($g['event_date']) ? date('Y', strtotime($g['event_date'])) : '';
                if ($gy !== $year) return false;
            }
            if ($location !== '') {
                if (strcasecmp(trim((string) ($g['location'] ?? '')), $location) !== 0) return false;
            }
            if ($q !== '') {
                $hay = mb_strtolower(($g['title'] ?? '') . ' ' . ($g['location'] ?? ''));
                if (!str_contains($hay, $q)) return false;
            }
            return true;
        }));
    }

    /**
     * Get featured gallery (yang punya gambar + terbaru).
     */
    private function getFeaturedGallery(array $galleries): ?array
    {
        foreach ($galleries as $g) {
            if (!empty($g['image_url']) || !empty($g['image'])) {
                return $g;
            }
        }
        return $galleries[0] ?? null;
    }

    /**
     * Build query string untuk URL (exclude parameter kosong).
     */
    private function buildQueryString(array $params): string
    {
        $filtered = array_filter($params, fn($v) => $v !== '' && $v !== null);
        if (empty($filtered)) return '';
        return '?' . http_build_query($filtered);
    }

    /**
     * Safe call wrapper — graceful fallback.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[GalleryController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}