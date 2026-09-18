<?php
// File: app/Middleware/Auth.php
declare(strict_types=1);

namespace Middleware;

use Core\Session;

class Auth {
    /** Blokir halaman wajib-login; lempar kembali ke form login */
    public static function handle(): void {
        if (!Session::get('user')) {
            Session::flash('login_error', 'Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.');
            redirect('login');
        }
    }
}