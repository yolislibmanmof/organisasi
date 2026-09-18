<?php
// File: app/Controllers/AuthController.php (FINAL - TERINTEGRASI TAHAP 4.2)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Member;
use Models\User;

class AuthController {
    public function showLogin(): void {
        // Pengguna yang sudah login tidak boleh melihat form login lagi
        if (Session::get('user')) {
            redirect('dashboard');
        }
        View::render('pages/login', ['title' => 'Masuk'], 'layouts/auth');
    }

    public function processLogin(): void {
        // 1. Verifikasi token CSRF
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            Session::flash('login_error', 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.');
            redirect('login');
        }

        $login    = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        // 2. Cari pengguna dan verifikasi kata sandi (bcrypt)
        $user = $login !== '' ? User::findByLogin($login) : null;

        if ($user === null || !password_verify($password, $user['password'])) {
            Session::flash('login_error', 'Username atau kata sandi yang Anda masukkan salah.');
            redirect('login');
        }

        if ($user['status'] !== 'active') {
            Session::flash('login_error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            redirect('login');
        }

        // 3. Cegah Session Fixation: regenerasi ID sesi
        session_regenerate_id(true);

        $profile = Member::findByUserId((int) $user['id']);

        // 4. Simpan data user lengkap (termasuk foto profil & waktu login)
        Session::set('user', [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'name'     => $profile['full_name'] ?? $user['username'],
            'role'     => $user['role'],
            'photo'    => $profile['photo'] ?? null,
        ]);
        Session::set('login_at', time());

        redirect('dashboard');
    }

    public function logout(): void {
        Session::destroy();
        redirect('login');
    }
}