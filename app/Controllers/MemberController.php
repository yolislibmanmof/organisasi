<?php
// File: app/Controllers/MemberController.php (FINAL - TAHAP 4.3)
declare(strict_types=1);

namespace Controllers;

use Core\Database;
use Core\Session;
use Core\View;
use Middleware\Auth;
use Models\Member;

class MemberController {
    public function index(): void {
        Auth::handle();
        View::render('pages/members', [
            'title' => 'Manajemen Anggota',
            'user'  => Session::get('user'),
        ], 'layouts/app');
    }

    /** API JSON: live search + paginasi */
    public function api(): void {
        Auth::handle();
        $q       = trim((string) ($_GET['q'] ?? ''));
        $perPage = 8;
        $total   = Member::countSearch($q);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        json([
            'data' => Member::search($q, $page, $perPage),
            'meta' => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    /** API JSON khusus ekspor laporan PDF (TAHAP 4.3) */
    public function export(): void {
        Auth::handle();
        json([
            'data' => Member::allForExport(),
            'meta' => ['generated' => date('c')],
        ]);
    }

    public function store(): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid. Muat ulang halaman.'], 419);
        }

        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
        }

        try {
            $data['password'] = 'member123'; // Kredensial awal anggota
            Member::createWithUser($data);
            json(['ok' => true, 'message' => 'Anggota berhasil ditambahkan. Kredensial awal: username otomatis / member123.']);
        } catch (\Throwable $e) {
            json(['ok' => false, 'message' => 'Gagal menyimpan data. Pastikan email belum terdaftar.'], 500);
        }
    }

    public function update(string $id): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid. Muat ulang halaman.'], 419);
        }
        if (Member::find((int) $id) === null) {
            json(['ok' => false, 'message' => 'Data anggota tidak ditemukan.'], 404);
        }

        $data = $this->validate((int) $id);
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
        }

        Member::updateMember((int) $id, $data);
        json(['ok' => true, 'message' => 'Perubahan data anggota telah disimpan.']);
    }

    public function destroy(string $id): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid. Muat ulang halaman.'], 419);
        }
        $member = Member::find((int) $id);
        if ($member === null) {
            json(['ok' => false, 'message' => 'Data anggota tidak ditemukan.'], 404);
        }
        if ((int) $member['user_id'] === (int) Session::get('user')['id']) {
            json(['ok' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 403);
        }

        Member::deleteWithUser((int) $id);
        json(['ok' => true, 'message' => 'Data anggota telah dihapus permanen.']);
    }

    /** Validasi server-side; kembalikan ['errors'=>...] atau data bersih */
    private function validate(int $ignoreId = 0): array {
        $errors  = [];
        $full    = trim((string) ($_POST['full_name'] ?? ''));
        $email   = trim((string) ($_POST['email'] ?? ''));
        $phone   = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $join    = (string) ($_POST['join_date'] ?? date('Y-m-d'));

        if (mb_strlen($full) < 3) {
            $errors['full_name'] = 'Nama lengkap minimal 3 karakter.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } else {
            $stmt = Database::getInstance()->prepare(
                'SELECT COUNT(*) FROM users u JOIN members m ON m.user_id = u.id
                 WHERE u.email = :e AND m.id <> :id'
            );
            $stmt->execute([':e' => $email, ':id' => $ignoreId]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['email'] = 'Email sudah digunakan oleh anggota lain.';
            }
        }
        if ($join === '') {
            $errors['join_date'] = 'Tanggal bergabung wajib diisi.';
        }

        return $errors !== []
            ? ['errors' => $errors]
            : ['full_name' => $full, 'email' => $email, 'phone' => $phone, 'address' => $address, 'join_date' => $join];
    }
}