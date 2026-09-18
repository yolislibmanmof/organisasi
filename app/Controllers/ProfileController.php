<?php
// File: app/Controllers/ProfileController.php (FINAL - TERKOREKSI)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\Auth;
use Models\Member;
use Models\User;

class ProfileController {
    /**
     * Direktori unggahan foto profil.
     * CATATAN: class constant TIDAK boleh memuat pemanggilan fungsi
     * (batasan ekspresi konstan waktu-kompilasi PHP), oleh karena itu
     * jalur ini disediakan melalui method statis.
     */
    private static function uploadDir(): string {
        return dirname(__DIR__, 2) . '/public/assets/uploads/';
    }

    public function index(): void {
        Auth::handle();
        $uid = (int) Session::get('user')['id'];
        View::render('pages/profile', [
            'title'   => 'Profil Saya',
            'user'    => Session::get('user'),
            'profile' => Member::findByUserId($uid),
            'account' => User::findById($uid),
            'loginAt' => (int) Session::get('login_at', time()),
        ], 'layouts/app');
    }

    public function update(): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        $uid   = (int) Session::get('user')['id'];
        $name  = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $addr  = trim((string) ($_POST['address'] ?? ''));

        $errors = [];
        if (mb_strlen($name) < 3) $errors['full_name'] = 'Nama lengkap minimal 3 karakter.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Format email tidak valid.';
        elseif (User::emailExistsExcept($email, $uid)) $errors['email'] = 'Email sudah digunakan akun lain.';
        if ($errors !== []) json(['ok' => false, 'errors' => $errors], 422);

        // --- Unggah foto profil (opsional) ---
        $dir  = self::uploadDir();
        $file = $_FILES['photo'] ?? null;
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $res = $this->handleUpload($file);
            if (isset($res['error'])) json(['ok' => false, 'message' => $res['error']], 422);

            // Hapus foto lama bila ada
            $old = Member::findByUserId($uid);
            if ($old && !empty($old['photo']) && is_file($dir . $old['photo'])) {
                @unlink($dir . $old['photo']);
            }
            Member::updatePhoto($uid, $res['name']);

            $u = Session::get('user');
            $u['photo'] = $res['name'];
            Session::set('user', $u);
        }

        Member::updateProfile($uid, ['full_name' => $name, 'phone' => $phone, 'address' => $addr]);
        User::updateEmail($uid, $email);

        $u = Session::get('user');
        $u['name'] = $name;
        Session::set('user', $u);

        json(['ok' => true, 'message' => 'Profil berhasil diperbarui.']);
    }

    public function updatePassword(): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        $uid     = (int) Session::get('user')['id'];
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $errors = [];
        if (!User::verify($uid, $current)) $errors['current_password'] = 'Kata sandi saat ini salah.';
        if (strlen($new) < 8)              $errors['new_password'] = 'Kata sandi baru minimal 8 karakter.';
        elseif ($new === $current)         $errors['new_password'] = 'Kata sandi baru harus berbeda dari sebelumnya.';
        if ($new !== $confirm)             $errors['confirm_password'] = 'Konfirmasi kata sandi tidak cocok.';
        if ($errors !== []) json(['ok' => false, 'errors' => $errors], 422);

        User::updatePassword($uid, password_hash($new, PASSWORD_DEFAULT));
        session_regenerate_id(true);
        json(['ok' => true, 'message' => 'Kata sandi berhasil diubah.']);
    }

    private function handleUpload(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK)         return ['error' => 'Gagal mengunggah berkas.'];
        if ($file['size'] > 2 * 1024 * 1024)          return ['error' => 'Ukuran foto maksimal 2 MB.'];
        $info = @getimagesize($file['tmp_name']);
        if ($info === false)                          return ['error' => 'Berkas harus berupa gambar.'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($info['mime'], $allowed, true)) return ['error' => 'Format didukung: JPG, PNG, WEBP.'];

        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime']];
        $name = 'avatar-' . bin2hex(random_bytes(8)) . '.' . $ext;

        $dir = self::uploadDir();
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return ['error' => 'Gagal menyimpan berkas.'];
        }
        return ['name' => $name];
    }
}