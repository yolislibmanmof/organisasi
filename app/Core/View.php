<?php
// File: app/Core/View.php
declare(strict_types=1);

namespace Core;

class View {
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): void {
        $basePath = dirname(__DIR__, 2); // Akar proyek

        extract($data, EXTR_SKIP);

        // 1. Render isi halaman terlebih dahulu
        ob_start();
        require $basePath . '/views/' . $view . '.php';
        $content = ob_get_clean();

        // 2. Bungkus isi halaman dengan layout induk
        require $basePath . '/views/' . $layout . '.php';
    }
}