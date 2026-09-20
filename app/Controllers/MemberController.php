<?php
// File: app/Controllers/MemberController.php (FINAL v7.0 — EXTENDED + BULK + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Member;
use Models\User;

/**
 * MemberController — Ultimate Edition v7.0
 *
 * Menangani manajemen anggota dengan fitur lengkap:
 * - API dengan filter status/search/sort/pagination + stats
 * - CRUD dengan photo upload support
 * - Bulk delete & bulk update status
 * - CSV export (real download)
 * - Random password generation
 * - Username/status/role update support
 * - Max length validation
 * - Audit logging
 * - Graceful fallback
 */
class MemberController
{
    /* ============================================================
       MANAGE PAGE (VIEW)
       ============================================================ */

    public function index(): void
    {
        Auth::handle();
        AdminOnly::handle();

        View::render('pages/members', [
            'title' => 'Manajemen Anggota',
            'user'  => Session::get('user'),
        ], 'layouts/app');
    }

    /* ============================================================
       API ENDPOINT (list + filter + search + pagination + stats)
       ============================================================ */

    /**
     * API endpoint untuk list anggota.
     * GET /admin/members/api?q=X&status=Y&sort=S&order=O&page=N
     */
    public function api(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $q       = trim((string) ($_GET['q'] ?? ''));
        $status  = trim((string) ($_GET['status'] ?? ''));
        $sort    = trim((string) ($_GET['sort'] ?? 'join_date'));
        $order   = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 10)));

        // Count & pagination
        $total = $this->safeCall(
            fn() => Member::countAdminList($status, $q),
            0
        );
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        // Data
        $data = $this->safeCall(
            fn() => Member::adminList($status, $q, $sort, $order, $page, $perPage),
            []
        );

        // Stats & counts untuk UI
        $stats = $this->safeCall(
            fn() => Member::adminStats(),
            ['total' => 0, 'active' => 0, 'inactive' => 0, 'newThisMonth' => 0, 'newToday' => 0]
        );
        $statusCounts = $this->safeCall(
            fn() => Member::countByStatus(),
            ['total' => 0, 'active' => 0, 'inactive' => 0]
        );

        json([
            'data' => $data,
            'meta' => [
                'page'    => $page,
                'pages'   => $pages,
                'total'   => $total,
                'perPage' => $perPage,
            ],
            'stats'        => $stats,
            'statusCounts' => $statusCounts,
        ]);
    }

    /* ============================================================
       EXPORT CSV
       ============================================================ */

    /**
     * Export anggota ke CSV (real download, bukan JSON).
     * GET /admin/members/export?status=active
     */
    public function export(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $status = trim((string) ($_GET['status'] ?? ''));

        try {
            $members = Member::allForExport();

            // Filter by status jika diberikan
            if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
                $members = array_filter($members, fn($m) => ($m['status'] ?? '') === $status);
            }
        } catch (\Throwable $e) {
            error_log('[MemberController::export] ' . $e->getMessage());
            http_response_code(500);
            echo 'Export gagal.';
            return;
        }

        $filename = 'anggota-' . ($status ?: 'semua') . '-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel UTF-8
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (empty($members)) {
            fputcsv($out, ['Tidak ada data anggota']);
            fclose($out);
            exit;
        }

        // Header
        fputcsv($out, ['ID', 'Username', 'Nama Lengkap', 'Email', 'Telepon', 'Alamat', 'Tanggal Gabung', 'Status']);

        foreach ($members as $m) {
            fputcsv($out, [
                $m['id'] ?? '',
                $m['username'] ?? '',
                $m['full_name'] ?? '',
                $m['email'] ?? '',
                $m['phone'] ?? '',
                $m['address'] ?? '',
                $m['join_date'] ?? '',
                $m['status_label'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    /* ============================================================
       STORE (CREATE)
       ============================================================ */

    public function store(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid. Muat ulang halaman.'], 419);
            return;
        }

        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
            return;
        }

        // Generate random password (12 karakter)
        $password = Member::generatePassword(12);
        $data['password'] = $password;

        // Handle photo upload (optional)
        $photoResult = $this->handlePhotoUpload();
        if ($photoResult === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload foto.'], 422);
            return;
        }
        if ($photoResult !== null) {
            $data['photo'] = $photoResult;
        }

        // Status default
        $data['status'] = $_POST['status'] ?? 'active';

        try {
            $memberId = Member::createWithUser($data);

            $adminUser = Session::get('user');
            error_log(sprintf(
                '[MemberController] Created member: id=%d email=%s by=%s',
                $memberId, $data['email'], $adminUser['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => 'Anggota berhasil ditambahkan. Username otomatis dibuat. Password awal: ' . $password,
                'id'       => $memberId,
                'password' => $password,
            ]);
        } catch (\Throwable $e) {
            error_log('[MemberController::store] ' . $e->getMessage());
            // Cleanup photo jika DB gagal
            if ($photoResult !== null) {
                $this->deletePhoto($photoResult);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan data. ' . $e->getMessage()], 500);
        }
    }

    /* ============================================================
       UPDATE
       ============================================================ */

    public function update(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid. Muat ulang halaman.'], 419);
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $existing = $this->safeCall(fn() => Member::find($id), null);
        if ($existing === null) {
            json(['ok' => false, 'message' => 'Data anggota tidak ditemukan.'], 404);
            return;
        }

        $data = $this->validate($id);
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
            return;
        }

        // Handle photo upload (optional)
        $photoResult = $this->handlePhotoUpload();
        if ($photoResult === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload foto.'], 422);
            return;
        }
        if ($photoResult !== null) {
            $data['photo'] = $photoResult;
            // Cleanup foto lama
            if (!empty($existing['photo'])) {
                $this->deletePhoto($existing['photo']);
            }
        }

        // Support update username & status
        if (!empty($_POST['username'])) {
            $data['username'] = trim((string) $_POST['username']);
        }
        if (!empty($_POST['status'])) {
            $data['status'] = (string) $_POST['status'];
        }

        try {
            Member::update($id, $data);

            $adminUser = Session::get('user');
            error_log(sprintf(
                '[MemberController] Updated member: id=%d by=%s',
                $id, $adminUser['username'] ?? 'unknown'
            ));

            json(['ok' => true, 'message' => 'Perubahan data anggota telah disimpan.']);
        } catch (\Throwable $e) {
            error_log('[MemberController::update] ' . $e->getMessage());
            // Cleanup new photo jika DB gagal
            if ($photoResult !== null) {
                $this->deletePhoto($photoResult);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan perubahan. ' . $e->getMessage()], 500);
        }
    }

    /* ============================================================
       DESTROY (SINGLE DELETE)
       ============================================================ */

    public function destroy(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid. Muat ulang halaman.'], 419);
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $member = $this->safeCall(fn() => Member::find($id), null);
        if ($member === null) {
            json(['ok' => false, 'message' => 'Data anggota tidak ditemukan.'], 404);
            return;
        }

        // Proteksi: tidak bisa hapus diri sendiri
        $currentUser = Session::get('user');
        if ((int) ($member['user_id'] ?? 0) === (int) ($currentUser['id'] ?? 0)) {
            json(['ok' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 403);
            return;
        }

        try {
            Member::deleteWithUser($id);

            error_log(sprintf(
                '[MemberController] Deleted member: id=%d email=%s by=%s',
                $id, $member['email'] ?? '', $currentUser['username'] ?? 'unknown'
            ));

            json(['ok' => true, 'message' => 'Data anggota telah dihapus permanen.']);
        } catch (\Throwable $e) {
            error_log('[MemberController::destroy] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus data.'], 500);
        }
    }

    /* ============================================================
       BULK ACTIONS (BARU v7.0)
       ============================================================ */

    /**
     * Bulk delete anggota.
     * JSON body: {ids: [1,2,3]}
     */
    public function bulkDelete(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids = $input['ids'] ?? ($_POST['ids'] ?? []);

        if (!is_array($ids) || empty($ids)) {
            json(['ok' => false, 'message' => 'Tidak ada anggota yang dipilih.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        $ids = array_filter($ids, fn($id) => $id > 0);

        // Proteksi: exclude diri sendiri
        $currentUserId = (int) (Session::get('user')['id'] ?? 0);
        $ids = array_filter($ids, function ($id) use ($currentUserId) {
            $member = Member::find($id);
            return $member && (int) ($member['user_id'] ?? 0) !== $currentUserId;
        });

        if (empty($ids)) {
            json(['ok' => false, 'message' => 'Tidak ada anggota valid untuk dihapus.'], 422);
            return;
        }

        try {
            $affected = Member::bulkDelete(array_values($ids));

            error_log(sprintf(
                '[MemberController] Bulk delete: affected=%d by=%s',
                $affected, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => "$affected anggota berhasil dihapus.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[MemberController::bulkDelete] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus anggota.'], 500);
        }
    }

    /**
     * Bulk update status anggota.
     * JSON body: {ids: [1,2,3], status: 'active'|'inactive'}
     */
    public function bulkUpdateStatus(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids = $input['ids'] ?? ($_POST['ids'] ?? []);
        $status = (string) ($input['status'] ?? ($_POST['status'] ?? ''));

        if (!is_array($ids) || empty($ids)) {
            json(['ok' => false, 'message' => 'Tidak ada anggota yang dipilih.'], 422);
            return;
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            json(['ok' => false, 'message' => 'Status tidak valid.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        $ids = array_filter($ids, fn($id) => $id > 0);
        if (empty($ids)) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        try {
            $affected = Member::bulkUpdateStatus(array_values($ids), $status);

            $statusLabel = $status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            error_log(sprintf(
                '[MemberController] Bulk status %s: affected=%d by=%s',
                $status, $affected, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => "$affected anggota berhasil $statusLabel.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[MemberController::bulkUpdateStatus] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mengubah status.'], 500);
        }
    }

    /* ============================================================
       PRIVATE: VALIDATION
       ============================================================ */

    /**
     * Validasi input form anggota.
     */
    private function validate(int $ignoreId = 0): array
    {
        $errors  = [];
        $full    = trim((string) ($_POST['full_name'] ?? ''));
        $email   = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone   = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $join    = (string) ($_POST['join_date'] ?? date('Y-m-d'));
        $username = trim((string) ($_POST['username'] ?? ''));

        // Nama: min 3, max 100
        if (mb_strlen($full) < 3) {
            $errors['full_name'] = 'Nama lengkap minimal 3 karakter.';
        } elseif (mb_strlen($full) > 100) {
            $errors['full_name'] = 'Nama lengkap maksimal 100 karakter.';
        }

        // Email: valid format + uniqueness
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } else {
            // Gunakan model method untuk cek duplicate
            $excludeUserId = 0;
            if ($ignoreId > 0) {
                $member = Member::find($ignoreId);
                $excludeUserId = (int) ($member['user_id'] ?? 0);
            }
            if (User::emailExists($email, $excludeUserId)) {
                $errors['email'] = 'Email sudah digunakan oleh anggota lain.';
            }
        }

        // Phone: optional, tapi jika diisi validasi format
        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                $errors['phone'] = 'Nomor telepon tidak valid (8-15 digit).';
            }
        }

        // Address: max 300
        if (mb_strlen($address) > 300) {
            $errors['address'] = 'Alamat maksimal 300 karakter.';
        }

        // Join date: wajib + valid format
        if ($join === '') {
            $errors['join_date'] = 'Tanggal bergabung wajib diisi.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $join)) {
            $errors['join_date'] = 'Format tanggal tidak valid (YYYY-MM-DD).';
        }

        // Username: optional (auto-generate jika kosong)
        if ($username !== '') {
            if (mb_strlen($username) < 3 || mb_strlen($username) > 30) {
                $errors['username'] = 'Username harus 3-30 karakter.';
            } elseif (!preg_match('/^[a-z0-9._]+$/', $username)) {
                $errors['username'] = 'Username hanya boleh huruf kecil, angka, titik, dan underscore.';
            } else {
                $excludeUserId = 0;
                if ($ignoreId > 0) {
                    $member = Member::find($ignoreId);
                    $excludeUserId = (int) ($member['user_id'] ?? 0);
                }
                if (User::usernameExists($username, $excludeUserId)) {
                    $errors['username'] = 'Username sudah digunakan.';
                }
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return [
            'full_name' => $full,
            'email'     => $email,
            'phone'     => $phone,
            'address'   => $address,
            'join_date' => $join,
            'username'  => $username,
        ];
    }

    /* ============================================================
       PRIVATE: PHOTO UPLOAD
       ============================================================ */

    /**
     * Handle upload foto profil anggota.
     *
     * @return string|null|false Filename jika sukses, null jika tidak ada file, false jika error
     */
    private function handlePhotoUpload(): ?string
    {
        $file = $_FILES['photo'] ?? null;

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Max 2 MB
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }

        // MIME validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            return false;
        }

        // Double-check dengan getimagesize
        if (@getimagesize($file['tmp_name']) === false) {
            return false;
        }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $name = 'member-' . bin2hex(random_bytes(8)) . '.' . $ext;

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
     * Get upload directory path.
     */
    private function getUploadDir(): string
    {
        if (function_exists('public_path')) {
            return rtrim(public_path('assets/uploads/users/'), '/') . '/';
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return rtrim($base . '/assets/uploads/users/', '/') . '/';
    }

    /**
     * Safe call wrapper.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[MemberController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}