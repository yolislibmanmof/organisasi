<?php
// File: app/Controllers/EventController.php
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\Auth;
use Models\Event;

class EventController {
    public function index(): void {
        Auth::handle();
        View::render('pages/events', [
            'title' => 'Event & Kegiatan',
            'user'  => Session::get('user'),
        ], 'layouts/app');
    }

    public function api(): void {
        Auth::handle();
        $q       = trim((string) ($_GET['q'] ?? ''));
        $perPage = 6;
        $total   = Event::countSearch($q);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        json([
            'data' => Event::search($q, $page, $perPage),
            'meta' => array_merge(
                ['page' => $page, 'pages' => $pages, 'total' => $total],
                Event::countByStatus()
            ),
        ]);
    }

    public function store(): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        $data = $this->validate();
        if (isset($data['errors'])) { json(['ok' => false, 'errors' => $data['errors']], 422); }

        Event::create($data, (int) Session::get('user')['id']);
        json(['ok' => true, 'message' => 'Event baru berhasil dijadwalkan.']);
    }

    public function update(string $id): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        if (Event::find((int) $id) === null) {
            json(['ok' => false, 'message' => 'Event tidak ditemukan.'], 404);
        }
        $data = $this->validate();
        if (isset($data['errors'])) { json(['ok' => false, 'errors' => $data['errors']], 422); }

        Event::updateMember((int) $id, $data);
        json(['ok' => true, 'message' => 'Perubahan event telah disimpan.']);
    }

    public function destroy(string $id): void {
        Auth::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        if (Event::find((int) $id) === null) {
            json(['ok' => false, 'message' => 'Event tidak ditemukan.'], 404);
        }
        Event::delete((int) $id);
        json(['ok' => true, 'message' => 'Event telah dihapus.']);
    }

    private function validate(): array {
        $errors = [];
        $title  = trim((string) ($_POST['title'] ?? ''));
        $date   = (string) ($_POST['event_date'] ?? '');
        $time   = (string) ($_POST['event_time'] ?? '');
        $loc    = trim((string) ($_POST['location'] ?? ''));
        $desc   = trim((string) ($_POST['description'] ?? ''));

        if (mb_strlen($title) < 3)       $errors['title'] = 'Nama event minimal 3 karakter.';
        if ($date === '')                $errors['event_date'] = 'Tanggal event wajib diisi.';
        elseif (!strtotime($date))       $errors['event_date'] = 'Format tanggal tidak valid.';

        return $errors !== []
            ? ['errors' => $errors]
            : ['title' => $title, 'event_date' => $date, 'event_time' => $time, 'location' => $loc, 'description' => $desc];
    }
}