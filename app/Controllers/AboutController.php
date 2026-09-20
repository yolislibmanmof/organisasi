<?php
// File: app/Controllers/AboutController.php (FINAL v7.0 — EXTENDED + SEO + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Officer;
use Models\Setting;

/**
 * AboutController — Ultimate Edition v7.0
 *
 * Menangani halaman "Tentang Kami" publik dengan data lengkap:
 * - Profil organisasi (visi, misi, motto, cabinet period)
 * - Struktur pengurus (grouped by division)
 * - Stats organisasi (years active, officers, divisions)
 * - Kontak & social media (dari Setting)
 * - Timeline/sejarah (dari cabinet period)
 * - SEO meta tags (dari Setting)
 * - Announcement banner (dari Setting)
 * - Resilient: graceful fallback jika model fail
 */
class AboutController
{
    /**
     * Halaman Tentang Kami.
     */
    public function index(): void
    {
        // Load settings sekali (in-memory cache per-request)
        Setting::loadAll();

        // Kumpulkan semua data dengan graceful fallback
        $data = $this->gatherAboutData();

        View::render('pages/about', $data, 'layouts/public');
    }

    /**
     * Kumpulkan semua data yang dibutuhkan view about v7.0.
     * Setiap block di-try/catch agar jika satu model fail, page tetap jalan.
     *
     * @return array<string, mixed>
     */
    private function gatherAboutData(): array
    {
        // ========== BASIC DATA ==========
        $appName = Setting::get('app_name', 'Organisasi');
        $data = [
            'title'    => $appName . ' — Tentang Kami',
            'loggedIn' => (bool) Session::get('user'),
        ];

        // ========== SEO META (dari Setting) ==========
        $data['seo'] = Setting::getSeoMeta();
        $data['seo']['title'] = $data['title'];
        $data['seo']['og_image'] = Setting::getLogoUrl();
        $data['seo']['og_url'] = function_exists('url') ? url('tentang') : '/tentang';
        // Fallback description jika kosong
        if (empty($data['seo']['description'])) {
            $visi = Setting::get('visi');
            $data['seo']['description'] = !empty($visi)
                ? mb_substr($visi, 0, 160)
                : 'Pelajari tentang ' . $appName . ', visi, misi, dan struktur organisasi kami.';
        }

        // ========== ANNOUNCEMENT BANNER ==========
        $data['announcement'] = [
            'active' => Setting::isAnnouncementActive(),
            'text'   => Setting::get('announcement_text'),
            'link'   => Setting::get('announcement_link'),
        ];

        // ========== ORGANIZATION PROFILE (dari Setting) ==========
        $data['org'] = [
            'name'           => $appName,
            'visi'           => Setting::get('visi'),
            'misi'           => Setting::get('misi'),
            'motto'          => Setting::get('motto'),
            'cabinet_period' => Setting::get('cabinet_period'),
            'logo_url'       => Setting::getLogoUrl(),
            'favicon_url'    => Setting::getFaviconUrl(),
        ];

        // Parse misi jadi array (satu misi per baris)
        $data['org']['misi_list'] = $this->parseMisi($data['org']['misi']);

        // Parse cabinet period jadi timeline info
        $data['org']['cabinet'] = $this->parseCabinetPeriod($data['org']['cabinet_period']);

        // ========== OFFICERS (grouped by division + flat) ==========
        $data['officers'] = $this->safeCall(
            fn() => Officer::groupByDivision(),
            []
        );
        $data['officersFlat'] = $this->safeCall(
            fn() => Officer::all(),
            []
        );

        // ========== DIVISION STATS ==========
        $data['divisionStats'] = $this->safeCall(
            fn() => Officer::countByDivision(),
            []
        );

        // ========== HERO STATS ==========
        $data['stats'] = $this->getAboutStats($data['officers'], $data['divisionStats']);

        // ========== CONTACT INFO (dari Setting) ==========
        $data['contact'] = [
            'email'    => Setting::get('social_email'),
            'phone'    => Setting::get('social_phone'),
            'address'  => Setting::get('social_address'),
        ];

        // ========== SOCIAL LINKS (dari Setting) ==========
        $data['socialLinks'] = Setting::getSocialLinks();

        // ========== META ==========
        $data['currentYear'] = (int) date('Y');
        $data['appVersion']  = '7.0';

        return $data;
    }

    /**
     * Hitung stats untuk halaman about.
     *
     * @param array $officersGrouped Hasil groupByDivision
     * @param array $divisionStats Hasil countByDivision
     * @return array{
     *     totalOfficers: int,
     *     totalDivisions: int,
     *     yearsActive: int,
     *     foundedYear: int,
     *     currentPeriod: string
     * }
     */
    private function getAboutStats(array $officersGrouped, array $divisionStats): array
    {
        // Total officers (flat)
        $totalOfficers = 0;
        foreach ($officersGrouped as $division => $members) {
            $totalOfficers += count($members);
        }
        if ($totalOfficers === 0) {
            $totalOfficers = $this->safeCall(fn() => Officer::countAll(), 0);
        }

        // Total divisions
        $totalDivisions = count($officersGrouped);
        if ($totalDivisions === 0) {
            $totalDivisions = count($divisionStats);
        }

        // Years active & founded year
        $foundedYear = $this->getFoundedYear();
        $currentYear = (int) date('Y');
        $yearsActive = max(1, $currentYear - $foundedYear + 1);

        // Current cabinet period
        $cabinet = Setting::get('cabinet_period');
        $currentPeriod = !empty($cabinet) ? $cabinet : 'Periode ' . $currentYear;

        return [
            'totalOfficers'  => $totalOfficers,
            'totalDivisions' => $totalDivisions,
            'yearsActive'    => $yearsActive,
            'foundedYear'    => $foundedYear,
            'currentPeriod'  => $currentPeriod,
        ];
    }

    /**
     * Parse misi jadi array (satu misi per baris).
     *
     * @return array<int, string>
     */
    private function parseMisi(string $misi): array
    {
        if (empty($misi)) return [];

        $lines = preg_split('/\r\n|\r|\n/', $misi);
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            // Hapus bullet prefix jika ada (-, *, 1., 2.)
            $line = preg_replace('/^[\-\*]\s*/', '', $line);
            $line = preg_replace('/^\d+\.\s*/', '', $line);
            if ($line !== '') $result[] = $line;
        }
        return $result;
    }

    /**
     * Parse cabinet period jadi structured info.
     * Contoh: "Kabinet Askara Periode 2024-2025"
     *
     * @return array{name: string, start_year: int, end_year: int, is_active: bool, full: string}
     */
    private function parseCabinetPeriod(string $period): array
    {
        $result = [
            'name'       => '',
            'start_year' => (int) date('Y'),
            'end_year'   => (int) date('Y'),
            'is_active'  => true,
            'full'       => $period,
        ];

        if (empty($period)) return $result;

        // Ekstrak nama kabinet (sebelum kata "Periode" atau tahun)
        if (preg_match('/^(.+?)(?:\s+Periode|\s+\d{4})/i', $period, $m)) {
            $result['name'] = trim($m[1]);
        } else {
            $result['name'] = $period;
        }

        // Ekstrak range tahun (YYYY-YYYY atau YYYY)
        if (preg_match('/(\d{4})\s*[-–]\s*(\d{4})/', $period, $m)) {
            $result['start_year'] = (int) $m[1];
            $result['end_year']   = (int) $m[2];
        } elseif (preg_match('/(\d{4})/', $period, $m)) {
            $result['start_year'] = (int) $m[1];
            $result['end_year']   = (int) $m[1];
        }

        // Cek apakah masih aktif
        $currentYear = (int) date('Y');
        $result['is_active'] = ($currentYear >= $result['start_year'] && $currentYear <= $result['end_year']);

        return $result;
    }

    /**
     * Dapatkan tahun pendirian organisasi.
     */
    private function getFoundedYear(): int
    {
        // Coba dari cabinet period
        $cabinet = Setting::get('cabinet_period');
        if (preg_match('/\b(19|20)\d{2}\b/', $cabinet, $m)) {
            return (int) $m[0];
        }

        // Fallback: cari dari database (events/galleries tertua)
        try {
            $db = \Core\Database::getInstance();

            $stmt = $db->query('SELECT MIN(YEAR(event_date)) FROM galleries WHERE event_date IS NOT NULL');
            $year = $stmt->fetchColumn();
            if ($year && (int) $year > 1900) return (int) $year;

            $stmt = $db->query('SELECT MIN(YEAR(event_date)) FROM events WHERE event_date IS NOT NULL');
            $year = $stmt->fetchColumn();
            if ($year && (int) $year > 1900) return (int) $year;
        } catch (\Throwable $e) {
            // Ignore
        }

        // Default fallback
        return (int) date('Y') - 5;
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
            error_log('[AboutController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}