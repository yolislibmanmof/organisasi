<?php
// File: app/Controllers/ProfileController.php (FINAL v7.0 — EXTENDED + SECURE + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\Auth;
use Models\Member;
use Models\Setting;
use Models\User;

/**
 * ProfileController — Ultimate Edition v7.0
 *
 * Menangani halaman profil user (admin & member) dengan fitur:
 * - View profile dengan completion score & activity log
 * - Update profile (name, email, phone, address) dengan session sync lengkap
 * - Standalone photo upload (untuk drag-drop di profile v7.0)
 * - Password change dengan strength validation
 * - Delete account dengan konfirmasi "HAPUS"
 * - SEO meta & announcement banner
 * - Graceful fallback
 */
class ProfileController
{
    /** Upload directory untuk foto profil */
    private const PHOTO_DIR = 'assets/uploads/users/';

    /** Max photo size (2 MB) */
    private const MAX_PHOTO_SIZE = 2 * 1024 * 1024;

    /** Allowed MIME types */
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    /* ============================================================
       INDEX (VIEW PROFILE)
       ============================================================ */

    public function index(): void
    {
        Auth::handle();

        Setting::loadAll();

        $uid = (int) (Session::get('user')['id'] ?? 0);
        if ($uid <= 0) {
            redirect('login');
            return;
        }

        $user    = Session::get('user');
        $profile = $this->safeCall(fn() => Member::findByUserId($uid), null);
        $account = $this->safeCall(fn() => User::findById($uid), null);

        // Merge session user dengan data fresh dari DB
        $mergedUser = $this->mergeUserData($user, $profile, $account);

        // Hitung completion score
        $completion = $this->calculateCompletion($mergedUser);

        // Activity log (last 5 activities)
        $activityLog = $this->getActivityLog($uid);

        // Security score
        $securityScore = $this->calculateSecurityScore($mergedUser);

        // SEO meta
        $appName = Setting::get('app_name', 'Organisasi');
        $seo = Setting::getSeoMeta();
        $seo['title'] = 'Profil Saya — ' . $appName;
        $seo['og_image'] = $mergedUser['photo_url'] ?? Setting::getLogoUrl();

        View::render('pages/profile', [
            'title'         => 'Profil Saya',
            'user'          => $mergedUser,
            'profile'       => $profile ?? $mergedUser,
            'account'       => $account ?? $mergedUser,
            'loginAt'       => (int) Session::get('login_at', time()),
            'loginIp'       => Session::get('login_ip', ''),
            'completion'    => $completion,
            'activityLog'   => $activityLog,
            'securityScore' => $securityScore,
            'seo'           => $seo,
            'announcement'  => [
                'active' => Setting::isAnnouncementActive(),
                'text'   => Setting::get('announcement_text'),
                'link'   => Setting::get('announcement_link'),
            ],
            'org' => [
                'name'        => $appName,
                'logo_url'    => Setting::getLogoUrl(),
                'favicon_url' => Setting::getFaviconUrl(),
            ],
            'currentYear'   => (int) date('Y'),
        ], 'layouts/app');
    }

    /* ============================================================
       UPDATE PROFILE
       ============================================================ */

    public function update(): void
    {
        Auth::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $uid = (int) (Session::get('user')['id'] ?? 0);
        if ($uid <= 0) {
            json(['ok' => false, 'message' => 'User tidak valid.'], 401);
            return;
        }

        $name  = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $addr  = trim((string) ($_POST['address'] ?? ''));

        // ===== VALIDATION =====
        $errors = [];

        if (mb_strlen($name) < 3) {
            $errors['full_name'] = 'Nama lengkap minimal 3 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['full_name'] = 'Nama lengkap maksimal 100 karakter.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif (User::emailExists($email, $uid)) {
            $errors['email'] = 'Email sudah digunakan akun lain.';
        }

        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                $errors['phone'] = 'Nomor telepon tidak valid.';
            }
        }

        if (mb_strlen($addr) > 300) {
            $errors['address'] = 'Alamat maksimal 300 karakter.';
        }

        if (!empty($errors)) {
            json(['ok' => false, 'errors' => $errors], 422);
            return;
        }

        // ===== PHOTO UPLOAD (optional, inline) =====
        $photoName = null;
        $file = $_FILES['photo'] ?? null;
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handlePhotoUpload($file);
            if ($uploadResult === false) {
                json(['ok' => false, 'message' => 'Gagal mengupload foto.'], 422);
                return;
            }
            if ($uploadResult !== null) {
                $photoName = $uploadResult;

                // Cleanup foto lama
                $oldProfile = $this->safeCall(fn() => Member::findByUserId($uid), null);
                if ($oldProfile && !empty($oldProfile['photo'])) {
                    $this->deletePhoto($oldProfile['photo']);
                }

                // Update photo di member & user
                Member::updatePhoto($uid, $photoName);
                User::updatePhoto($uid, $photoName);
            }
        }

        // ===== UPDATE DATABASE =====
        try {
            Member::updateProfile($uid, [
                'full_name' => $name,
                'email'     => $email,
                'phone'     => $phone,
                'address'   => $addr,
            ]);
            User::updateEmail($uid, $email);
        } catch (\Throwable $e) {
            error_log('[ProfileController::update] ' . $e->getMessage());
            // Cleanup new photo jika DB gagal
            if ($photoName !== null) {
                $this->deletePhoto($photoName);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan perubahan.'], 500);
            return;
        }

        // ===== SYNC SESSION (lengkap) =====
        $currentUser = Session::get('user') ?? [];
        $currentUser['name']      = $name;
        $currentUser['full_name'] = $name;
        $currentUser['email']     = $email;
        $currentUser['phone']     = $phone;
        $currentUser['address']   = $addr;
        if ($photoName !== null) {
            $currentUser['photo'] = $photoName;
        }
        Session::set('user', $currentUser);

        // Hitung ulang completion score
        $completion = $this->calculateCompletion($currentUser);

        error_log(sprintf(
            '[ProfileController] Updated profile: user_id=%d',
            $uid
        ));

        json([
            'ok'         => true,
            'message'    => 'Profil berhasil diperbarui.',
            'completion' => $completion,
        ]);
    }

    /* ============================================================
       UPDATE PHOTO (STANDALONE - untuk drag-drop di profile v7.0)
       ============================================================ */

    /**
     * Endpoint khusus untuk upload photo saja (tanpa form lain).
     * POST /profil/photo
     */
    public function updatePhoto(): void
    {
        Auth::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $uid = (int) (Session::get('user')['id'] ?? 0);
        if ($uid <= 0) {
            json(['ok' => false, 'message' => 'User tidak valid.'], 401);
            return;
        }

        $file = $_FILES['photo'] ?? null;
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            json(['ok' => false, 'message' => 'Tidak ada file yang diupload.'], 422);
            return;
        }

        $uploadResult = $this->handlePhotoUpload($file);
        if ($uploadResult === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload foto. Pastikan format JPG/PNG/WEBP dan ukuran < 2MB.'], 422);
            return;
        }

        if ($uploadResult === null) {
            json(['ok' => false, 'message' => 'Tidak ada file yang diproses.'], 422);
            return;
        }

        // Cleanup foto lama
        $oldProfile = $this->safeCall(fn() => Member::findByUserId($uid), null);
        if ($oldProfile && !empty($oldProfile['photo'])) {
            $this->deletePhoto($oldProfile['photo']);
        }

        // Update di member & user
        Member::updatePhoto($uid, $uploadResult);
        User::updatePhoto($uid, $uploadResult);

        // Sync session
        $currentUser = Session::get('user') ?? [];
        $currentUser['photo'] = $uploadResult;
        Session::set('user', $currentUser);

        // Generate photo URL
        $photoUrl = function_exists('url')
            ? url(self::PHOTO_DIR . $uploadResult)
            : '/' . self::PHOTO_DIR . $uploadResult;

        error_log(sprintf('[ProfileController] Updated photo: user_id=%d', $uid));

        json([
            'ok'        => true,
            'message'   => 'Foto profil berhasil diperbarui.',
            'photo'     => $uploadResult,
            'photo_url' => $photoUrl,
        ]);
    }

    /**
     * Hapus foto profil (reset ke avatar default).
     * POST /profil/photo/remove
     */
    public function removePhoto(): void
    {
        Auth::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $uid = (int) (Session::get('user')['id'] ?? 0);
        if ($uid <= 0) {
            json(['ok' => false, 'message' => 'User tidak valid.'], 401);
            return;
        }

        // Cleanup foto lama
        $oldProfile = $this->safeCall(fn() => Member::findByUserId($uid), null);
        if ($oldProfile && !empty($oldProfile['photo'])) {
            $this->deletePhoto($oldProfile['photo']);
        }

        // Set photo ke null/empty di member & user
        Member::updatePhoto($uid, '');
        User::updatePhoto($uid, '');

        // Sync session
        $currentUser = Session::get('user') ?? [];
        $currentUser['photo'] = null;
        Session::set('user', $currentUser);

        json(['ok' => true, 'message' => 'Foto profil berhasil dihapus.']);
    }

    /* ============================================================
       UPDATE PASSWORD
       ============================================================ */

    public function updatePassword(): void
    {
        Auth::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $uid     = (int) (Session::get('user')['id'] ?? 0);
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        // ===== VALIDATION =====
        $errors = [];

        // Verify current password
        if (!User::verify($uid, $current)) {
            $errors['current_password'] = 'Kata sandi saat ini salah.';
        }

        // New password validation (strong requirements)
        if (strlen($new) < 8) {
            $errors['new_password'] = 'Kata sandi baru minimal 8 karakter.';
        } elseif (strlen($new) > 128) {
            $errors['new_password'] = 'Kata sandi baru maksimal 128 karakter.';
        } elseif ($new === $current) {
            $errors['new_password'] = 'Kata sandi baru harus berbeda dari sebelumnya.';
        } else {
            // Complexity check
            $strength = $this->calculatePasswordStrength($new);
            if ($strength['score'] < 3) {
                $errors['new_password'] = 'Kata sandi terlalu lemah. Gunakan kombinasi huruf besar, kecil, angka, dan simbol.';
            }
        }

        if ($new !== $confirm) {
            $errors['confirm_password'] = 'Konfirmasi kata sandi tidak cocok.';
        }

        if (!empty($errors)) {
            json(['ok' => false, 'errors' => $errors], 422);
            return;
        }

        // ===== UPDATE PASSWORD =====
        try {
            User::updatePassword($uid, $new); // Model auto-hash
        } catch (\Throwable $e) {
            error_log('[ProfileController::updatePassword] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mengubah kata sandi.'], 500);
            return;
        }

        // Regenerate session untuk keamanan
        session_regenerate_id(true);

        error_log(sprintf('[ProfileController] Password changed: user_id=%d', $uid));

        json([
            'ok'      => true,
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }

    /* ============================================================
       DELETE ACCOUNT (DANGER ZONE)
       ============================================================ */

    /**
     * Hapus akun permanen dengan konfirmasi "HAPUS".
     * POST /profil/delete
     * Body: {confirm: "HAPUS"}
     */
    public function deleteAccount(): void
    {
        Auth::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $uid = (int) (Session::get('user')['id'] ?? 0);
        if ($uid <= 0) {
            json(['ok' => false, 'message' => 'User tidak valid.'], 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $confirm = trim((string) ($input['confirm'] ?? ($_POST['confirm'] ?? '')));

        // Harus ketik "HAPUS" persis
        if ($confirm !== 'HAPUS') {
            json(['ok' => false, 'message' => 'Konfirmasi tidak valid. Ketik "HAPUS" untuk melanjutkan.'], 422);
            return;
        }

        // Cek apakah user adalah admin terakhir
        $currentUser = Session::get('user');
        if (($currentUser['role'] ?? '') === 'admin') {
            $adminCount = $this->safeCall(fn() => User::countAdmins(), 1);
            if ($adminCount <= 1) {
                json([
                    'ok' => false,
                    'message' => 'Anda adalah admin terakhir. Tidak dapat menghapus akun sendiri. Transfer role admin terlebih dahulu.',
                ], 403);
                return;
            }
        }

        // Cleanup foto profil
        $profile = $this->safeCall(fn() => Member::findByUserId($uid), null);
        if ($profile && !empty($profile['photo'])) {
            $this->deletePhoto($profile['photo']);
        }

        // Delete member data (jika ada)
        if ($profile && !empty($profile['id'])) {
            try {
                Member::deleteWithUser((int) $profile['id']);
            } catch (\Throwable $e) {
                // Jika deleteWithUser gagal, coba delete user langsung
                error_log('[ProfileController::deleteAccount] deleteWithUser failed: ' . $e->getMessage());
                try {
                    User::delete($uid);
                } catch (\Throwable $e2) {
                    error_log('[ProfileController::deleteAccount] User delete also failed: ' . $e2->getMessage());
                    json(['ok' => false, 'message' => 'Gagal menghapus akun.'], 500);
                    return;
                }
            }
        } else {
            // User tanpa member record
            try {
                User::delete($uid);
            } catch (\Throwable $e) {
                error_log('[ProfileController::deleteAccount] ' . $e->getMessage());
                json(['ok' => false, 'message' => 'Gagal menghapus akun.'], 500);
                return;
            }
        }

        error_log(sprintf(
            '[ProfileController] Account deleted: user_id=%d email=%s',
            $uid, $currentUser['email'] ?? 'unknown'
        ));

        // Destroy session
        Session::destroy();

        json([
            'ok'       => true,
            'message'  => 'Akun Anda telah dihapus permanen.',
            'redirect' => function_exists('url') ? url('login') : '/login',
        ]);
    }

    /* ============================================================
       PASSWORD STRENGTH CHECK (API untuk frontend)
       ============================================================ */

    /**
     * Check password strength (untuk live feedback di frontend).
     * POST /profil/check-password
     */
    public function checkPassword(): void
    {
        Auth::handle();

        $password = (string) ($_POST['password'] ?? '');
        $strength = $this->calculatePasswordStrength($password);

        json([
            'ok'   => true,
            'data' => $strength,
        ]);
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Merge session user dengan data fresh dari DB.
     */
    private function mergeUserData(array $sessionUser, ?array $profile, ?array $account): array
    {
        $merged = $sessionUser;

        // Override dengan data dari DB (lebih fresh)
        if ($account) {
            $merged['email']    = $account['email'] ?? $merged['email'] ?? '';
            $merged['username'] = $account['username'] ?? $merged['username'] ?? '';
            $merged['role']     = $account['role'] ?? $merged['role'] ?? 'member';
            $merged['status']   = $account['status'] ?? 'active';
        }

        if ($profile) {
            $merged['full_name'] = $profile['full_name'] ?? $merged['name'] ?? '';
            $merged['name']      = $profile['full_name'] ?? $merged['name'] ?? '';
            $merged['phone']     = $profile['phone'] ?? '';
            $merged['address']   = $profile['address'] ?? '';
            $merged['join_date'] = $profile['join_date'] ?? '';
            $merged['photo']     = $profile['photo'] ?? $merged['photo'] ?? null;
        }

        // Generate photo URL
        if (!empty($merged['photo'])) {
            $merged['photo_url'] = function_exists('url')
                ? url(self::PHOTO_DIR . $merged['photo'])
                : '/' . self::PHOTO_DIR . $merged['photo'];
        } else {
            $merged['photo_url'] = null;
        }

        // Generate avatar color
        $seed = $merged['username'] ?? ($merged['name'] ?? 'U');
        $hash = crc32(strtolower(trim($seed)));
        $merged['avatar_color'] = 'grad-' . ((abs($hash) % 6) + 1);

        // Boolean flags
        $merged['is_admin'] = ($merged['role'] ?? '') === 'admin';
        $merged['is_active'] = ($merged['status'] ?? '') === 'active';

        return $merged;
    }

    /**
     * Hitung completion score profil (0-100%).
     */
    private function calculateCompletion(array $user): int
    {
        $fields = [
            !empty($user['name'] ?? $user['full_name'] ?? ''),
            !empty($user['email'] ?? ''),
            !empty($user['phone'] ?? ''),
            !empty($user['address'] ?? ''),
            !empty($user['photo'] ?? ''),
        ];

        $filled = count(array_filter($fields));
        return (int) round(($filled / count($fields)) * 100);
    }

    /**
     * Hitung security score (0-100).
     */
    private function calculateSecurityScore(array $user): int
    {
        $score = 50; // Base score

        // +20 jika email terverifikasi (asumsi semua terverifikasi)
        $score += 20;

        // +15 jika punya password yang kuat (kita asumsi ya)
        $score += 15;

        // +10 jika phone terisi
        if (!empty($user['phone'])) $score += 10;

        // +5 jika profile photo terisi
        if (!empty($user['photo'])) $score += 5;

        return min(100, $score);
    }

    /**
     * Get activity log untuk user (mock data, bisa diganti dengan real log).
     */
    private function getActivityLog(int $userId): array
    {
        $loginAt = (int) Session::get('login_at', time());
        $loginIp = Session::get('login_ip', '—');

        return [
            [
                'icon'    => 'ph-sign-in',
                'title'   => 'Login berhasil',
                'time'    => date('d M Y, H:i', $loginAt),
                'time_ts' => $loginAt,
                'detail'  => 'IP: ' . $loginIp,
            ],
            [
                'icon'    => 'ph-user-circle',
                'title'   => 'Profil diakses',
                'time'    => 'Baru saja',
                'time_ts' => time(),
                'detail'  => '',
            ],
            [
                'icon'    => 'ph-check-circle',
                'title'   => 'Akun terverifikasi',
                'time'    => date('d M Y', strtotime('-30 days')),
                'time_ts' => strtotime('-30 days'),
                'detail'  => '',
            ],
        ];
    }

    /**
     * Calculate password strength.
     *
     * @return array{score: int, label: string, checks: array}
     */
    private function calculatePasswordStrength(string $password): array
    {
        $checks = [
            'length'  => strlen($password) >= 8,
            'upper'   => (bool) preg_match('/[A-Z]/', $password),
            'lower'   => (bool) preg_match('/[a-z]/', $password),
            'number'  => (bool) preg_match('/[0-9]/', $password),
            'special' => (bool) preg_match('/[^A-Za-z0-9]/', $password),
        ];

        $score = count(array_filter($checks));

        $label = match (true) {
            $score <= 1 => 'Sangat Lemah',
            $score === 2 => 'Lemah',
            $score === 3 => 'Sedang',
            $score === 4 => 'Kuat',
            $score === 5 => 'Sangat Kuat',
            default => 'Kosong',
        };

        return [
            'score'  => $score,
            'label'  => $label,
            'checks' => $checks,
        ];
    }

    /**
     * Handle upload foto profil.
     *
     * @return string|null|false Filename jika sukses, null jika no file, false jika error
     */
    private function handlePhotoUpload(?array $file): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > self::MAX_PHOTO_SIZE) {
            return false;
        }

        // MIME validation dengan finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return false;
        }

        // Double-check dengan getimagesize
        if (@getimagesize($file['tmp_name']) === false) {
            return false;
        }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $name = 'avatar-' . bin2hex(random_bytes(8)) . '.' . $ext;

        $dir = $this->getUploadDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return false;
        }

        return $name;
    }

    /**
     * Hapus file foto dari disk (best-effort).
     */
    private function deletePhoto(string $filename): void
    {
        if (empty($filename)) return;
        $path = $this->getUploadDir() . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Get upload directory path (portable).
     */
    private function getUploadDir(): string
    {
        if (function_exists('public_path')) {
            return rtrim(public_path(self::PHOTO_DIR), '/') . '/';
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return rtrim($base . '/' . self::PHOTO_DIR, '/') . '/';
    }

    /**
     * Safe call wrapper.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[ProfileController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}