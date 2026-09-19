<?php
// File: app/Controllers/CensusController.php (FINAL - TAHAP 5.7)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Census;
use Models\Member;

class CensusController {
    /* ---------- SISI PUBLIK ---------- */
    public function form(): void {
        View::render('pages/sensus', [
            'title'    => 'Sensus Anggota',
            'loggedIn' => (bool) Session::get('user'),
            'success'  => Session::getFlash('sensus_ok'),
            'error'    => Session::getFlash('sensus_err'),
        ], 'layouts/public');
    }

    public function store(): void {
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            Session::flash('sensus_err', 'Sesi tidak valid. Silakan coba lagi.');
            redirect('sensus');
        }

        $name    = trim((string) ($_POST['full_name'] ?? ''));
        $email   = trim((string) ($_POST['email'] ?? ''));
        $phone   = trim((string) ($_POST['phone'] ?? ''));
        $addr    = trim((string) ($_POST['address'] ?? ''));
        $status  = (string) ($_POST['status'] ?? 'pelajar');
        $year    = trim((string) ($_POST['graduation_year'] ?? ''));
        $msg     = trim((string) ($_POST['message'] ?? ''));
        $purpose = in_array($_POST['purpose'] ?? '', ['pendaftaran', 'sensus'], true)
                   ? $_POST['purpose'] : 'sensus';

        if (mb_strlen($name) < 3) {
            Session::flash('sensus_err', 'Nama lengkap minimal 3 karakter.');
            redirect('sensus');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('sensus_err', 'Format email tidak valid.');
            redirect('sensus');
        }
        if (!in_array($status, ['pelajar', 'mahasiswa', 'alumni'], true)) {
            Session::flash('sensus_err', 'Status keanggotaan tidak valid.');
            redirect('sensus');
        }
        if (Census::emailPending($email)) {
            Session::flash('sensus_err', 'Email Anda sudah terdaftar dan sedang menunggu verifikasi.');
            redirect('sensus');
        }

        Census::store([
            'full_name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $addr,
            'status' => $status, 'graduation_year' => $year, 'message' => $msg,
            'purpose' => $purpose,
        ]);

        $msgOk = $purpose === 'pendaftaran'
            ? 'Terima kasih! Pendaftaran Anda telah tercatat. Akun akan dibuat setelah verifikasi pengurus.'
            : 'Terima kasih! Data sensus Anda telah tercatat untuk rekap alumni.';
        Session::flash('sensus_ok', $msgOk);
        redirect('sensus');
    }

    /* ---------- SISI ADMIN ---------- */
    public function manage(): void {
        Auth::handle();
        AdminOnly::handle();
        View::render('pages/census', [
            'title' => 'Sensus Anggota',
            'user'  => Session::get('user'),
        ], 'layouts/app');
    }

    public function api(): void {
        Auth::handle();
        AdminOnly::handle();
        json(['data' => Census::all(), 'meta' => ['new' => Census::countNew()]]);
    }

    public function approve(string $id): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);

        $row = Census::find((int) $id);
        if ($row === null) json(['ok' => false, 'message' => 'Data sensus tidak ditemukan.'], 404);
        if ((int) $row['processed'] === 1) json(['ok' => false, 'message' => 'Entri ini sudah diproses sebelumnya.'], 422);

        // Hanya entri 'pendaftaran' yang dibuatkan akun
        if (($row['purpose'] ?? 'sensus') !== 'pendaftaran') {
            Census::markProcessed((int) $id);
            json(['ok' => true, 'message' => 'Entri sensus rekap ditandai sebagai telah diproses.']);
            return;
        }

        try {
            Member::createWithUser([
                'full_name' => $row['full_name'],
                'email'     => $row['email'],
                'phone'     => $row['phone'] ?? '',
                'address'   => $row['address'] ?? '',
                'join_date' => date('Y-m-d'),
                'password'  => 'member123',
            ]);
            Census::markProcessed((int) $id);
            json(['ok' => true, 'message' => 'Anggota dibuat. Kredensial awal: ' . $row['email'] . ' / member123']);
        } catch (\Throwable $e) {
            json(['ok' => false, 'message' => 'Gagal membuat akun. Email kemungkinan sudah terdaftar sebagai anggota.'], 500);
        }
    }

    public function destroy(string $id): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        Census::delete((int) $id);
        json(['ok' => true, 'message' => 'Entri sensus telah dihapus.']);
    }
}