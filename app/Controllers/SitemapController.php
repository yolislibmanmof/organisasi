<?php
// File: app/Controllers/SitemapController.php (FINAL v7.0 — EXTENDED + CACHED + SCALABLE)
declare(strict_types=1);

namespace Controllers;

use Core\Cache;
use Models\Article;
use Models\Event;
use Models\Setting;

/**
 * SitemapController — Ultimate Edition v7.0
 *
 * Menyediakan sitemap XML untuk SEO dengan fitur:
 * - Sitemap index (jika > 50k URLs, scalable)
 * - Static pages sitemap
 * - Articles sitemap (published only)
 * - Events sitemap (public events)
 * - Image sitemap extension (untuk Google Images)
 * - Cache output (5 menit, sitemap tidak perlu real-time)
 * - robots.txt handler
 * - Graceful fallback
 * - Proper XML escaping
 * - X-Robots-Tag header
 */
class SitemapController
{
    /** Cache TTL (5 menit) */
    private const CACHE_TTL = 300;

    /** Max URLs per sitemap (Google limit 50k, kita pakai 10k untuk safety) */
    private const MAX_URLS_PER_SITEMAP = 10000;

    /* ============================================================
       ROBOTS.TXT HANDLER
       ============================================================ */

    /**
     * Handle request ke /robots.txt
     */
    public function robots(): void
    {
        $cacheKey = 'sitemap|robots_txt';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
            echo $cached;
            exit;
        }

        Setting::loadAll();

        $baseUrl = $this->getBaseUrl();
        $sitemapUrl = $baseUrl . '/sitemap.xml';

        // Build robots.txt content
        $content = "# Robots.txt for " . Setting::get('app_name', 'Organisasi') . "\n";
        $content .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /profil\n";
        $content .= "Disallow: /api/\n";
        $content .= "\n";
        $content .= "# Sitemap location\n";
        $content .= "Sitemap: " . $sitemapUrl . "\n";

        Cache::set($cacheKey, $content, 3600); // Cache 1 jam

        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo $content;
        exit;
    }

    /* ============================================================
       SITEMAP INDEX (untuk skala besar)
       ============================================================ */

    /**
     * Sitemap index — entry point untuk crawler.
     * GET /sitemap.xml
     */
    public function index(): void
    {
        $cacheKey = 'sitemap|index';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->sendXmlHeaders();
            echo $cached;
            exit;
        }

        Setting::loadAll();
        $baseUrl = $this->getBaseUrl();

        // Cek apakah butuh sitemap index (jika total URLs > MAX_URLS_PER_SITEMAP)
        $totalArticles = $this->safeCall(fn() => Article::countPublished(), 0);
        $totalEvents = $this->safeCall(
            fn() => Event::countAdminList('', ''),
            0
        );

        // Untuk skala kecil (< 10k URLs), gunakan single sitemap
        if ($totalArticles + $totalEvents + 10 < self::MAX_URLS_PER_SITEMAP) {
            // Redirect ke main sitemap (hindari redirect loop dengan render langsung)
            $this->renderMainSitemap();
            return;
        }

        // Sitemap index untuk skala besar
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages sitemap
        $xml .= $this->buildSitemapEntry($baseUrl . '/sitemap-static.xml', time());

        // Articles sitemap (paginated jika perlu)
        $articlePages = max(1, (int) ceil($totalArticles / self::MAX_URLS_PER_SITEMAP));
        for ($i = 1; $i <= $articlePages; $i++) {
            $xml .= $this->buildSitemapEntry(
                $baseUrl . '/sitemap-articles-' . $i . '.xml',
                time()
            );
        }

        // Events sitemap
        $xml .= $this->buildSitemapEntry($baseUrl . '/sitemap-events.xml', time());

        // Images sitemap (untuk Google Images)
        $xml .= $this->buildSitemapEntry($baseUrl . '/sitemap-images.xml', time());

        $xml .= '</sitemapindex>';

        Cache::set($cacheKey, $xml, self::CACHE_TTL);
        $this->sendXmlHeaders();
        echo $xml;
        exit;
    }

    /* ============================================================
       MAIN SITEMAP (single sitemap untuk skala kecil)
       ============================================================ */

    /**
     * Render main sitemap (gabungan semua URL).
     */
    private function renderMainSitemap(): void
    {
        $cacheKey = 'sitemap|main';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->sendXmlHeaders();
            echo $cached;
            exit;
        }

        Setting::loadAll();
        $baseUrl = $this->getBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $xml .= ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

        // ===== STATIC PAGES =====
        $staticPages = [
            ['path' => '',            'freq' => 'daily',   'priority' => '1.0'],
            ['path' => '/artikel',    'freq' => 'daily',   'priority' => '0.9'],
            ['path' => '/event',      'freq' => 'weekly',  'priority' => '0.8'],
            ['path' => '/galeri',     'freq' => 'weekly',  'priority' => '0.7'],
            ['path' => '/tentang',    'freq' => 'monthly', 'priority' => '0.6'],
            ['path' => '/sensus',     'freq' => 'monthly', 'priority' => '0.5'],
        ];

        foreach ($staticPages as $page) {
            $xml .= $this->buildUrlEntry(
                loc: $baseUrl . $page['path'],
                lastmod: date('Y-m-d'),
                changefreq: $page['freq'],
                priority: $page['priority'],
            );
        }

        // ===== ARTICLES =====
        $articles = $this->safeCall(
            fn() => Article::published('', 1, self::MAX_URLS_PER_SITEMAP),
            []
        );

        foreach ($articles as $a) {
            $articleUrl = $baseUrl . '/artikel/' . $a['id'];
            $lastmod = !empty($a['updated_at'])
                ? date('Y-m-d', strtotime($a['updated_at']))
                : (!empty($a['created_at']) ? date('Y-m-d', strtotime($a['created_at'])) : date('Y-m-d'));

            // Images untuk article (jika ada cover)
            $images = [];
            if (!empty($a['cover_url'])) {
                $images[] = [
                    'loc'     => $a['cover_url'],
                    'title'   => $a['title'] ?? '',
                    'caption' => $a['excerpt'] ?? '',
                ];
            }

            $xml .= $this->buildUrlEntry(
                loc: $articleUrl,
                lastmod: $lastmod,
                changefreq: 'monthly',
                priority: '0.7',
                images: $images,
                news: [
                    'publication' => Setting::get('app_name', 'Organisasi'),
                    'title'       => $a['title'] ?? '',
                    'date'        => !empty($a['created_at']) ? date('Y-m-d', strtotime($a['created_at'])) : date('Y-m-d'),
                ],
            );
        }

        // ===== EVENTS (public) =====
        $events = $this->safeCall(
            fn() => Event::adminList('', '', 'event_date', 'desc', 1, 1000),
            []
        );

        foreach ($events as $e) {
            // Event tidak punya individual page di v7.0 (hanya list), skip
            // Uncomment jika nanti ada halaman detail event:
            // $eventUrl = $baseUrl . '/event/' . $e['id'];
            // ...
        }

        $xml .= '</urlset>';

        Cache::set($cacheKey, $xml, self::CACHE_TTL);
        $this->sendXmlHeaders();
        echo $xml;
        exit;
    }

    /* ============================================================
       SPECIALIZED SITEMAPS (untuk skala besar)
       ============================================================ */

    /**
     * Static pages sitemap.
     * GET /sitemap-static.xml
     */
    public function staticSitemap(): void
    {
        $cacheKey = 'sitemap|static';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->sendXmlHeaders();
            echo $cached;
            exit;
        }

        Setting::loadAll();
        $baseUrl = $this->getBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $pages = [
            ['', 'daily', '1.0'],
            ['/artikel', 'daily', '0.9'],
            ['/event', 'weekly', '0.8'],
            ['/galeri', 'weekly', '0.7'],
            ['/tentang', 'monthly', '0.6'],
            ['/sensus', 'monthly', '0.5'],
        ];

        foreach ($pages as [$path, $freq, $priority]) {
            $xml .= $this->buildUrlEntry(
                loc: $baseUrl . $path,
                lastmod: date('Y-m-d'),
                changefreq: $freq,
                priority: $priority,
            );
        }

        $xml .= '</urlset>';

        Cache::set($cacheKey, $xml, self::CACHE_TTL);
        $this->sendXmlHeaders();
        echo $xml;
        exit;
    }

    /**
     * Articles sitemap (paginated).
     * GET /sitemap-articles-{page}.xml
     */
    public function articlesSitemap(string $page = '1'): void
    {
        $pageNum = max(1, (int) $page);
        $cacheKey = 'sitemap|articles_' . $pageNum;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->sendXmlHeaders();
            echo $cached;
            exit;
        }

        Setting::loadAll();
        $baseUrl = $this->getBaseUrl();

        $articles = $this->safeCall(
            fn() => Article::published('', $pageNum, self::MAX_URLS_PER_SITEMAP),
            []
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $xml .= ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

        foreach ($articles as $a) {
            $lastmod = !empty($a['updated_at'])
                ? date('Y-m-d', strtotime($a['updated_at']))
                : (!empty($a['created_at']) ? date('Y-m-d', strtotime($a['created_at'])) : date('Y-m-d'));

            $images = [];
            if (!empty($a['cover_url'])) {
                $images[] = [
                    'loc'     => $a['cover_url'],
                    'title'   => $a['title'] ?? '',
                    'caption' => $a['excerpt'] ?? '',
                ];
            }

            $xml .= $this->buildUrlEntry(
                loc: $baseUrl . '/artikel/' . $a['id'],
                lastmod: $lastmod,
                changefreq: 'monthly',
                priority: '0.7',
                images: $images,
                news: [
                    'publication' => Setting::get('app_name', 'Organisasi'),
                    'title'       => $a['title'] ?? '',
                    'date'        => !empty($a['created_at']) ? date('Y-m-d', strtotime($a['created_at'])) : date('Y-m-d'),
                ],
            );
        }

        $xml .= '</urlset>';

        Cache::set($cacheKey, $xml, self::CACHE_TTL);
        $this->sendXmlHeaders();
        echo $xml;
        exit;
    }

    /**
     * Events sitemap.
     * GET /sitemap-events.xml
     */
    public function eventsSitemap(): void
    {
        $cacheKey = 'sitemap|events';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->sendXmlHeaders();
            echo $cached;
            exit;
        }

        Setting::loadAll();
        $baseUrl = $this->getBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Events page
        $xml .= $this->buildUrlEntry(
            loc: $baseUrl . '/event',
            lastmod: date('Y-m-d'),
            changefreq: 'daily',
            priority: '0.8',
        );

        // Individual events (jika ada halaman detail)
        $events = $this->safeCall(
            fn() => Event::adminList('', '', 'event_date', 'desc', 1, 1000),
            []
        );

        // Note: v7.0 tidak punya halaman detail event publik,
        // tapi tetap entry event page untuk SEO
        foreach ($events as $e) {
            $eventDate = !empty($e['event_date']) ? date('Y-m-d', strtotime($e['event_date'])) : date('Y-m-d');
            // Optional: tambahkan sebagai entry jika ada detail page
        }

        $xml .= '</urlset>';

        Cache::set($cacheKey, $xml, self::CACHE_TTL);
        $this->sendXmlHeaders();
        echo $xml;
        exit;
    }

    /**
     * Images sitemap (untuk Google Images).
     * GET /sitemap-images.xml
     */
    public function imagesSitemap(): void
    {
        $cacheKey = 'sitemap|images';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->sendXmlHeaders();
            echo $cached;
            exit;
        }

        Setting::loadAll();
        $baseUrl = $this->getBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // Collect images dari articles
        $articles = $this->safeCall(fn() => Article::published('', 1, 1000), []);
        foreach ($articles as $a) {
            if (empty($a['cover_url'])) continue;

            $xml .= $this->buildUrlEntry(
                loc: $baseUrl . '/artikel/' . $a['id'],
                lastmod: !empty($a['created_at']) ? date('Y-m-d', strtotime($a['created_at'])) : date('Y-m-d'),
                changefreq: 'monthly',
                priority: '0.6',
                images: [[
                    'loc'     => $a['cover_url'],
                    'title'   => $a['title'] ?? '',
                    'caption' => $a['excerpt'] ?? '',
                ]],
            );
        }

        $xml .= '</urlset>';

        Cache::set($cacheKey, $xml, self::CACHE_TTL);
        $this->sendXmlHeaders();
        echo $xml;
        exit;
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Send XML headers dengan best practices.
     */
    private function sendXmlHeaders(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow', true);
        header('Cache-Control: public, max-age=' . self::CACHE_TTL);
    }

    /**
     * Get base URL (dengan trailing slash removed).
     */
    private function getBaseUrl(): string
    {
        if (defined('BASE_URL')) {
            return rtrim((string) BASE_URL, '/');
        }
        // Fallback
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    /**
     * Build single URL entry untuk sitemap.
     */
    private function buildUrlEntry(
        string $loc,
        string $lastmod,
        string $changefreq,
        string $priority,
        array $images = [],
        array $news = [],
    ): string {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . $this->xmlEscape($loc) . "</loc>\n";
        $xml .= "    <lastmod>" . $this->xmlEscape($lastmod) . "</lastmod>\n";
        $xml .= "    <changefreq>" . $this->xmlEscape($changefreq) . "</changefreq>\n";
        $xml .= "    <priority>" . $this->xmlEscape($priority) . "</priority>\n";

        // Image extension
        foreach ($images as $img) {
            $xml .= "    <image:image>\n";
            $xml .= "      <image:loc>" . $this->xmlEscape($img['loc']) . "</image:loc>\n";
            if (!empty($img['title'])) {
                $xml .= "      <image:title>" . $this->xmlEscape($img['title']) . "</image:title>\n";
            }
            if (!empty($img['caption'])) {
                $xml .= "      <image:caption>" . $this->xmlEscape($img['caption']) . "</image:caption>\n";
            }
            $xml .= "    </image:image>\n";
        }

        // News extension (untuk Google News)
        if (!empty($news)) {
            $xml .= "    <news:news>\n";
            $xml .= "      <news:publication>\n";
            $xml .= "        <news:name>" . $this->xmlEscape($news['publication']) . "</news:name>\n";
            $xml .= "        <news:language>id</news:language>\n";
            $xml .= "      </news:publication>\n";
            $xml .= "      <news:publication_date>" . $this->xmlEscape($news['date']) . "</news:publication_date>\n";
            $xml .= "      <news:title>" . $this->xmlEscape($news['title']) . "</news:title>\n";
            $xml .= "    </news:news>\n";
        }

        $xml .= "  </url>\n";
        return $xml;
    }

    /**
     * Build sitemap index entry.
     */
    private function buildSitemapEntry(string $loc, int $lastmodTs): string
    {
        $xml = "  <sitemap>\n";
        $xml .= "    <loc>" . $this->xmlEscape($loc) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d\TH:i:sP', $lastmodTs) . "</lastmod>\n";
        $xml .= "  </sitemap>\n";
        return $xml;
    }

    /**
     * XML-safe escaping (lebih robust dari htmlspecialchars biasa).
     */
    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Safe call wrapper — graceful fallback.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[SitemapController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}