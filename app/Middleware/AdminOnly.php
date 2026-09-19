<?php
// File: app/Middleware/AdminOnly.php
declare(strict_types=1);

namespace Middleware;

use Core\Session;

class AdminOnly {
    /** Blokir akses non-administrator; lempar kembali ke dashboard */
    public static function handle(): void {
        $user = Session::get('user');
        if ($user === null || ($user['role'] ?? '') !== 'admin') {
            redirect('dashboard');
        }
    }
}