<?php
// File: app/Controllers/AboutController.php (TAHAP 5.7)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Officer;

class AboutController {
    public function index(): void {
        View::render('pages/about', [
            'title'    => 'Tentang Kami',
            'loggedIn' => (bool) Session::get('user'),
            'officers' => Officer::all(),
        ], 'layouts/public');
    }
}