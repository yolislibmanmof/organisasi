<?php
// File: app/Controllers/CensusController.php (FINAL v7.0 — EXTENDED + SECURE + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Census;
use Models\Member;
use Models\Setting;
use Models\User;

/**
 * CensusController — Ultimate Edition v7.0
 *
 * Menangani sisi publik (form submission + tracking) dan admin (list + approve + bulk + export).
 * Extension v7.0:
 * - SEO meta tags dari Setting
 * - Announcement banner
 * - Purpose-specific validation & messaging
 * - Reference number generator
 * - Random password generation (tidak lagi hardcode)
 * - IP & User-Agent tracking
 * - Admin stats + filter + search + pagination
 * - Bulk approve/delete
 * - CSV export
 * - Graceful fallback
 */
class CensusController
{
    /* ============================================================
       SISI PUBLIK
       ============================================================ */

    /**
     * Tampilkan form sensus publik.
     */
    public function form(): void
    {
        Setting::loadAll();

        $appName = Setting::get('app_name', 'Organisasi');

        // SEO meta
        $seo = Setting::getSeoMeta();
        $seo['title'] = 'Sensus & Pendaftaran Anggota — ' . $appName;
        if (empty($seo['description'])) {
            $seo['description'] = 'Daftar sebagai anggota baru atau lengkapi data sensus alumni ' . $appName . '.';
        }
        $seo['og_image'] = Setting::getLogoUrl();
        $seo['og_url'] = function_exists('url') ? url('sensus') : '/sensus';

        View::render('pages/sensus', [
            'title'        => 'Sensus Anggota',
            'loggedIn'     => (bool) Session::get('user'),
            'success'      => Session::getFlash('sensus_ok'),
            'error'        => Session::getFlash('sensus_err'),
            'seo'          => $seo,
            'announcement' => [
                'active' => Setting::isAnnouncementActive(),
                'text'   => Setting::get('announcement_text'),
                'link'   => Setting::get('announcement_link'),
            ],
            'org' => [
                'name'        => $appName,
                'logo_url'    => Setting::getLogoUrl(),
                'favicon_url' => Setting::getFaviconUrl(),
            ],
            'socialLinks' => Setting::getSocialLinks(),
            'currentYear' => (int) date('Y'),
        ], 'layouts/public');
    }

    /**
     * Proses submission form sensus.
     * Support 2 purpose: 'pendaftaran' (daftar anggota baru) dan 'sensus' (rekap alumni).
     */
    public function store(): void
    {
        // CSRF check
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            Session::flash('sensus_err', ['message' => 'Sesi tidak valid. Silakan coba lagi.']);
            redirect('sensus');
            return;
        }

        // Ambil & sanitize input
        $name    = trim((string) ($_POST['full_name'] ?? ''));
        $email   = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone   = trim((string) ($_POST['phone'] ?? ''));
        $addr    = trim((string) ($_POST['address'] ?? ''));
        $status  = (string) ($_POST['status'] ?? '');
        $year    = trim((string) ($_POST['graduation_year'] ?? ''));
        $msg     = trim((string) ($_POST['message'] ?? ''));
        $purpose = in_array($_POST['purpose'] ?? '', Census::PURPOSES, true)
                   ? $_POST['purpose']
                   : Census::PURPOSE_CENSUS;

        // ===== VALIDASI =====
        $errors = [];

        // Nama
        if (mb_strlen($name) < 3) {
            $errors['full_name'] = 'Nama lengkap minimal 3 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['full_name'] = 'Nama lengkap maksimal 100 karakter.';
        }

        // Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        // Phone (opsional, tapi jika diisi harus valid)
        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                $errors['phone'] = 'Nomor telepon tidak valid (8-15 digit).';
            }
        }

        // Status
        if (!in_array($status, Census::STATUSES, true)) {
            $errors['status'] = 'Status keanggotaan tidak valid.';
        }

        // Graduation year (opsional, tapi jika diisi harus numeric 1950-2099)
        if ($year !== '') {
            if (!preg_match('/^\d{4}$/', $year)) {
                $errors['graduation_year'] = 'Tahun harus 4 digit angka.';
            } else {
                $yearInt = (int) $year;
                if ($yearInt < 1950 || $yearInt > (int) date('Y')) {
                    $errors['graduation_year'] = 'Tahun harus antara 1950 dan ' . date('Y') . '.';
                }
            }
        }

        // Address max length
        if (mb_strlen($addr) > 300) {
            $errors['address'] = 'Alamat maksimal 300 karakter.';
        }

        // Message max length
        if (mb_strlen($msg) > 500) {
            $errors['message'] = 'Pesan maksimal 500 karakter.';
        }

        if (!empty($errors)) {
            Session::flash('sensus_err', ['message' => 'Mohon perbaiki field yang bermasalah.', 'errors' => $errors]);
            redirect('sensus');
            return;
        }

        // ===== DUPLICATE CHECK =====
        // Cek email pending dengan filter purpose
        $emailPending = $this->safeCall(
            fn() => Census::emailPending($email, $purpose),
            false
        );
        if ($emailPending) {
            $msgErr = $purpose === Census::PURPOSE_REGISTRATION
                ? 'Email Anda sudah mendaftar dan sedang menunggu verifikasi pengurus.'
                : 'Email Anda sudah tercatat dalam sensus dan sedang menunggu verifikasi.';
            Session::flash('sensus_err', ['message' => $msgErr]);
            redirect('sensus');
            return;
        }

        // Cek email sudah jadi member (untuk pendaftaran)
        if ($purpose === Census::PURPOSE_REGISTRATION) {
            $existsAsMember = $this->safeCall(
                fn() => User::emailExists($email),
                false
            );
            if ($existsAsMember) {
                Session::flash('sensus_err', [
                    'message' => 'Email ini sudah terdaftar sebagai anggota aktif. Silakan login.'
                ]);
                redirect('sensus');
                return;
            }
        }

        // ===== STORE =====
        try {
            $clientIp = $this->getClientIp();
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

            $newId = Census::store([
                'full_name'       => $name,
                'email'           => $email,
                'phone'           => $phone,
                'address'         => $addr,
                'status'          => $status,
                'graduation_year' => $year,
                'message'         => $msg,
                'purpose'         => $purpose,
                'ip_address'      => $clientIp,
                'user_agent'      => $userAgent,
            ]);

            // Ambil reference number dari row yang baru dibuat
            $row = $this->safeCall(fn() => Census::find($newId), null);
            $reference = $row['reference'] ?? '—';

            $msgOk = $purpose === Census::PURPOSE_REGISTRATION
                ? 'Terima kasih! Pendaftaran Anda telah tercatat dengan nomor referensi ' . $reference . '. Akun akan dibuat setelah verifikasi pengurus.'
                : 'Terima kasih! Data sensus Anda telah tercatat dengan nomor referensi ' . $reference . '.';

            Session::flash('sensus_ok', [
                'message'   => $msgOk,
                'reference' => $reference,
                'purpose'   => $purpose,
            ]);

            // Audit log
            error_log(sprintf(
                '[CensusController] Submission: id=%d purpose=%s email=%s ip=%s',
                $newId, $purpose, $email, $clientIp
            ));

        } catch (\Throwable $e) {
            error_log('[CensusController::store] ' . $e->getMessage());
            Session::flash('sensus_err', ['message' => 'Terjadi kesalahan sistem. Silakan coba lagi.']);
        }

        redirect('sensus');
    }

    /* ============================================================
       SISI ADMIN
       ============================================================ */

    /**
     * Halaman manajemen sensus (render view, data via API).
     */
    public function manage(): void
    {
        Auth::handle();
        AdminOnly::handle();

        View::render('pages/census', [
            'title' => 'Manajemen Sensus',
            'user'  => Session::get('user'),
        ], 'layouts/app');
    }

    /**
     * API endpoint untuk admin: list + filter + search + pagination + stats.
     * GET /admin/sensus/api?status=X&purpose=Y&processed=Z&q=SEARCH&sort=S&order=O&page=N
     */
    public function api(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $status    = trim((string) ($_GET['status'] ?? ''));
        $purpose   = trim((string) ($_GET['purpose'] ?? ''));
        $processed = trim((string) ($_GET['processed'] ?? ''));
        $search    = trim((string) ($_GET['q'] ?? ''));
        $sort      = trim((string) ($_GET['sort'] ?? 'created_at'));
        $order     = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        $perPage   = 15;

        // Count & pagination
        $total = $this->safeCall(
            fn() => Census::countAdminList($status, $purpose, $processed, $search),
            0
        );
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        // Data
        $data = $this->safeCall(
            fn() => Census::adminList($status, $purpose, $processed, $search, $sort, $order, $page, $perPage),
            []
        );

        // Stats & counts
        $stats = $this->safeCall(
            fn() => Census::adminStats(),
            ['total' => 0, 'pending' => 0, 'processed' => 0, 'pendaftaran' => 0, 'sensus' => 0, 'new_today' => 0, 'new_this_week' => 0]
        );
        $statusCounts = $this->safeCall(
            fn() => Census::countByStatus(),
            []
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

    /**
     * Approve satu entri sensus.
     * Jika purpose=pendaftaran → buat akun member dengan random password.
     * Jika purpose=sensus → hanya mark processed.
     */
    public function approve(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $row = $this->safeCall(fn() => Census::find((int) $id), null);

        if ($row === null) {
            json(['ok' => false, 'message' => 'Data sensus tidak ditemukan.'], 404);
            return;
        }

        if ($row['is_processed']) {
            json(['ok' => false, 'message' => 'Entri ini sudah diproses sebelumnya.'], 422);
            return;
        }

        $purpose = $row['purpose'] ?? Census::PURPOSE_CENSUS;
        $adminUser = Session::get('user');

        // ===== SENSUS REKAP: hanya mark processed =====
        if ($purpose !== Census::PURPOSE_REGISTRATION) {
            try {
                Census::markProcessed((int) $id);
                error_log(sprintf(
                    '[CensusController] Approved sensus: id=%d by=%s',
                    $id, $adminUser['username'] ?? 'unknown'
                ));
                json([
                    'ok'       => true,
                    'message'  => 'Entri sensus rekap ditandai sebagai telah diproses.',
                    'type'     => 'sensus',
                    'reference' => $row['reference'] ?? null,
                ]);
            } catch (\Throwable $e) {
                error_log('[CensusController::approve sensus] ' . $e->getMessage());
                json(['ok' => false, 'message' => 'Gagal memproses entri.'], 500);
            }
            return;
        }

        // ===== PENDAFTARAN: buat akun member =====
        // Cek ulang email belum jadi member (race condition)
        if ($this->safeCall(fn() => User::emailExists($row['email']), false)) {
            Census::markProcessed((int) $id);
            json([
                'ok'      => false,
                'message' => 'Email sudah terdaftar sebagai anggota aktif. Entri ditandai sebagai diproses.',
                'type'    => 'already_member',
            ], 409);
            return;
        }

        try {
            // Generate random password (12 karakter)
            $password = Member::generatePassword(12);

            $memberId = Member::createWithUser([
                'full_name' => $row['full_name'],
                'email'     => $row['email'],
                'phone'     => $row['phone'] ?? '',
                'address'   => $row['address'] ?? '',
                'join_date' => date('Y-m-d'),
                'password'  => $password,
                'status'    => 'active',
            ]);

            Census::markProcessed((int) $id);

            error_log(sprintf(
                '[CensusController] Approved pendaftaran: census_id=%d member_id=%d email=%s by=%s',
                $id, $memberId, $row['email'], $adminUser['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => 'Anggota baru berhasil dibuat. Kredensial: ' . $row['email'] . ' / ' . $password,
                'type'     => 'pendaftaran',
                'memberId' => $memberId,
                'email'    => $row['email'],
                'password' => $password,
                'reference' => $row['reference'] ?? null,
            ]);

        } catch (\Throwable $e) {
            error_log('[CensusController::approve pendaftaran] ' . $e->getMessage());
            json([
                'ok'      => false,
                'message' => 'Gagal membuat akun. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus satu entri sensus.
     */
    public function destroy(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $row = $this->safeCall(fn() => Census::find((int) $id), null);
        if ($row === null) {
            json(['ok' => false, 'message' => 'Data sensus tidak ditemukan.'], 404);
            return;
        }

        try {
            Census::delete((int) $id);
            error_log(sprintf(
                '[CensusController] Deleted: id=%d by=%s',
                $id, Session::get('user')['username'] ?? 'unknown'
            ));
            json(['ok' => true, 'message' => 'Entri sensus telah dihapus.']);
        } catch (\Throwable $e) {
            error_log('[CensusController::destroy] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus entri.'], 500);
        }
    }

    /**
     * Bulk approve (JSON body: {ids: [1,2,3]}).
     * Hanya approve entri yang belum diproses.
     */
    public function bulkApprove(): void
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
            json(['ok' => false, 'message' => 'Tidak ada entri yang dipilih.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $approved = 0;
        $failed = 0;
        $results = [];

        foreach ($ids as $id) {
            try {
                $row = Census::find($id);
                if ($row === null || $row['is_processed']) {
                    $failed++;
                    continue;
                }

                $purpose = $row['purpose'] ?? Census::PURPOSE_CENSUS;

                if ($purpose === Census::PURPOSE_REGISTRATION) {
                    // Cek email belum jadi member
                    if (User::emailExists($row['email'])) {
                        Census::markProcessed($id);
                        $results[] = ['id' => $id, 'status' => 'already_member'];
                        $approved++;
                        continue;
                    }

                    $password = Member::generatePassword(12);
                    $memberId = Member::createWithUser([
                        'full_name' => $row['full_name'],
                        'email'     => $row['email'],
                        'phone'     => $row['phone'] ?? '',
                        'address'   => $row['address'] ?? '',
                        'join_date' => date('Y-m-d'),
                        'password'  => $password,
                        'status'    => 'active',
                    ]);
                    Census::markProcessed($id);
                    $results[] = [
                        'id'       => $id,
                        'status'   => 'created',
                        'memberId' => $memberId,
                        'email'    => $row['email'],
                        'password' => $password,
                    ];
                } else {
                    Census::markProcessed($id);
                    $results[] = ['id' => $id, 'status' => 'marked'];
                }

                $approved++;

            } catch (\Throwable $e) {
                error_log("[CensusController::bulkApprove] id=$id error: " . $e->getMessage());
                $failed++;
                $results[] = ['id' => $id, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        error_log(sprintf(
            '[CensusController] Bulk approve: approved=%d failed=%d by=%s',
            $approved, $failed, Session::get('user')['username'] ?? 'unknown'
        ));

        json([
            'ok'       => true,
            'message'  => "$approved entri diproses, $failed gagal.",
            'approved' => $approved,
            'failed'   => $failed,
            'results'  => $results,
        ]);
    }

    /**
     * Bulk delete (JSON body: {ids: [1,2,3]}).
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
            json(['ok' => false, 'message' => 'Tidak ada entri yang dipilih.'], 422);
            return;
        }

        try {
            $affected = Census::bulkDelete($ids);
            error_log(sprintf(
                '[CensusController] Bulk delete: affected=%d by=%s',
                $affected, Session::get('user')['username'] ?? 'unknown'
            ));
            json([
                'ok'       => true,
                'message'  => "$affected entri berhasil dihapus.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[CensusController::bulkDelete] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus entri.'], 500);
        }
    }

    /**
     * Export CSV (GET /admin/sensus/export?purpose=X&processed=Y).
     */
    public function export(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $purpose   = trim((string) ($_GET['purpose'] ?? ''));
        $processed = trim((string) ($_GET['processed'] ?? ''));

        try {
            $rows = Census::exportAll($purpose, $processed);
        } catch (\Throwable $e) {
            error_log('[CensusController::export] ' . $e->getMessage());
            http_response_code(500);
            echo 'Export gagal.';
            return;
        }

        $filename = 'sensus-' . ($purpose ?: 'semua') . '-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel UTF-8
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header (ambil dari row pertama)
        if (!empty($rows)) {
            $csvHeader = array_keys(Census::toCsvRow($rows[0]));
            fputcsv($out, $csvHeader);

            foreach ($rows as $r) {
                fputcsv($out, Census::toCsvRow($r));
            }
        } else {
            fputcsv($out, ['Tidak ada data']);
        }

        fclose($out);
        exit;
    }

    /* ============================================================
       HELPERS
       ============================================================ */

    /**
     * Get client IP (handle proxy).
     */
    private function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', (string) $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Safe call wrapper — graceful fallback.
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[CensusController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}