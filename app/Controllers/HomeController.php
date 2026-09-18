<?php
// File: app/Controllers/HomeController.php  (PERBARUI file lama)
declare(strict_types=1);

namespace Controllers;

use Core\Session;

class HomeController {
    public function index(): void {
        redirect(Session::get('user') ? 'dashboard' : 'login');
    }
}