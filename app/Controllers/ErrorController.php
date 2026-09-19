<?php
// File: app/Controllers/ErrorController.php (TAHAP 5.7)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;

class ErrorController {
    public function notFound(): void {
        http_response_code(404);
        View::render('pages/404', [
            'title'    => 'Halaman Tidak Ditemukan',
            'loggedIn' => (bool) Session::get('user'),
        ], 'layouts/public');
    }
}