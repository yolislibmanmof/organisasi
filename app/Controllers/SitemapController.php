<?php
// File: app/Controllers/SitemapController.php (TAHAP 5.7)
declare(strict_types=1);

namespace Controllers;

use Models\Article;
use Models\Gallery;

class SitemapController {
    public function index(): void {
        header('Content-Type: application/xml; charset=utf-8');

        $base = rtrim(BASE_URL, '/');
        $articles = Article::published('', 1, 200);
        $galleries = Gallery::all(50);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Halaman statis
        foreach (['', '/artikel', '/galeri', '/tentang', '/sensus'] as $path) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($base . $path) . '</loc>';
            echo '<changefreq>' . ($path === '' ? 'daily' : 'weekly') . '</changefreq>';
            echo '<priority>' . ($path === '' ? '1.0' : '0.8') . '</priority>';
            echo '</url>';
        }

        // Artikel
        foreach ($articles as $a) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($base . '/artikel/' . $a['id']) . '</loc>';
            echo '<lastmod>' . date('Y-m-d', strtotime($a['created_at'])) . '</lastmod>';
            echo '<changefreq>monthly</changefreq>';
            echo '<priority>0.7</priority>';
            echo '</url>';
        }

        echo '</urlset>';
        exit;
    }
}