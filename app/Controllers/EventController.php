<?php
// File: app/Controllers/EventController.php (FINAL v7.0 — EXTENDED + BULK + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Event;
use Models\Setting;

/**
 * EventController — Ultimate Edition v7.0
 *
 * Menangani sisi publik (agenda & arsip + featured) dan admin (manage + API + CRUD + bulk).
 * Extension v7.0:
 * - Featured event untuk hero section publik
 * - SEO meta + announcement banner
 * - Filter status (upcoming/ongoing/done) di admin API
 * - Support cover image upload
 * - Support end_date (multi-day event)
 * - Bulk delete
 * - CSV export
 * - Graceful fallback
 * - Audit logging
 */
class EventController
{
    /** Upload directory untuk cover image */
    private const UPLOAD_DIR = 'assets/uploads/events/';

    /** Max file size (10 MB) */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /** Allowed MIME types */
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    /* ============================================================
       SISI PUBLIK
       ============================================================ */

    /**
     * Halaman publik /event — agenda & arsip kegiatan.
     */
    public function publicIndex(): void
    {
        Setting::loadAll();

        $appName = Setting::get('app_name', 'Organisasi');

        // Data dengan graceful fallback
        $list = $this->safeCall(
            fn() => Event::publicList(),
            ['active' => [], 'done' => []]
        );

        $featured = $this->safeCall(
            fn() => Event::featured(),
            null
        );

        $stats = $this->safeCall(
            fn() => Event::adminStats(),
            ['total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'done' => 0, 'this_month' => 0, 'next_7_days' => 0]
        );

        // SEO meta
        $seo = Setting::getSeoMeta();
        $seo['title'] = 'Event & Kegiatan — ' . $appName;
        if (empty($seo['description'])) {
            $seo['description'] = 'Jadwal kegiatan, acara, dan arsip dokumentasi ' . $appName . '.';
        }
        $seo['og_image'] = $featured['cover_url'] ?? Setting::getLogoUrl();
        $seo['og_url'] = function_exists('url') ? url('event') : '/event';

        View::render('pages/event', [
            'title'       => 'Event & Kegiatan',
            'loggedIn'    => (bool) Session::get('user'),
            'active'      => $list['active'],
            'done'        => $list['done'],
            'featured'    => $featured,
            'stats'       => $stats,
            'seo'         => $seo,
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

    /* ============================================================
       SISI ADMIN — MANAGE PAGE
       ============================================================ */

    public function index(): void
    {
        Auth::handle();
        AdminOnly::handle();

        View::render('pages/events', [
            'title' => 'Manajemen Event',
            'user'  => Session::get('user'),
        ], 'layouts/app');
    }

    /* ============================================================
       ADMIN — API ENDPOINT
       ============================================================ */

    /**
     * API endpoint untuk admin: list + filter status + search + pagination + stats.
     * GET /admin/events/api?q=X&status=Y&sort=S&order=O&page=N
     */
    public function api(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $q       = trim((string) ($_GET['q'] ?? ''));
        $status  = trim((string) ($_GET['status'] ?? ''));
        $sort    = trim((string) ($_GET['sort'] ?? 'event_date'));
        $order   = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 10)));

        // Count & pagination
        $total = $this->safeCall(
            fn() => Event::countAdminList($status, $q),
            0
        );
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        // Data
        $data = $this->safeCall(
            fn() => Event::adminList($status, $q, $sort, $order, $page, $perPage),
            []
        );

        // Stats & status counts
        $stats = $this->safeCall(
            fn() => Event::adminStats(),
            ['total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'done' => 0, 'this_month' => 0, 'next_7_days' => 0]
        );
        $statusCounts = $this->safeCall(
            fn() => Event::countByStatus(),
            ['total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'done' => 0]
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
       ADMIN — CRUD
       ============================================================ */

    /**
     * Buat event baru dengan optional cover image + end_date.
     */
    public function store(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
            return;
        }

        // Handle cover image upload (optional)
        $coverResult = $this->handleCoverUpload();
        if ($coverResult === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload cover.'], 422);
            return;
        }
        if ($coverResult !== null) {
            $data['cover_image'] = $coverResult;
        }

        try {
            $newId = Event::create($data, (int) Session::get('user')['id']);

            error_log(sprintf(
                '[EventController] Created event: id=%d title="%s" by=%s',
                $newId, $data['title'], Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'      => true,
                'message' => 'Event baru berhasil dijadwalkan.',
                'id'      => $newId,
            ]);
        } catch (\Throwable $e) {
            error_log('[EventController::store] ' . $e->getMessage());
            // Cleanup cover jika DB gagal
            if ($coverResult !== null) {
                $this->deleteCover($coverResult);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan event.'], 500);
        }
    }

    /**
     * Update event (support cover image replacement + end_date).
     */
    public function update(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $idInt = (int) $id;
        if ($idInt <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $existing = $this->safeCall(fn() => Event::find($idInt), null);
        if ($existing === null) {
            json(['ok' => false, 'message' => 'Event tidak ditemukan.'], 404);
            return;
        }

        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
            return;
        }

        // Handle cover image (optional)
        $coverResult = $this->handleCoverUpload();
        if ($coverResult === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload cover.'], 422);
            return;
        }
        if ($coverResult !== null) {
            $data['cover_image'] = $coverResult;
            // Cleanup cover lama
            if (!empty($existing['cover_image'])) {
                $this->deleteCover($existing['cover_image']);
            }
        }

        try {
            Event::update($idInt, $data);

            error_log(sprintf(
                '[EventController] Updated event: id=%d by=%s',
                $idInt, Session::get('user')['username'] ?? 'unknown'
            ));

            json(['ok' => true, 'message' => 'Perubahan event telah disimpan.']);
        } catch (\Throwable $e) {
            error_log('[EventController::update] ' . $e->getMessage());
            // Cleanup new cover jika DB gagal
            if ($coverResult !== null) {
                $this->deleteCover($coverResult);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan perubahan.'], 500);
        }
    }

    /**
     * Hapus satu event (dengan cleanup cover).
     */
    public function destroy(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $idInt = (int) $id;
        if ($idInt <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $existing = $this->safeCall(fn() => Event::find($idInt), null);
        if ($existing === null) {
            json(['ok' => false, 'message' => 'Event tidak ditemukan.'], 404);
            return;
        }

        try {
            Event::delete($idInt); // Model handles cover cleanup

            error_log(sprintf(
                '[EventController] Deleted event: id=%d title="%s" by=%s',
                $idInt, $existing['title'] ?? '', Session::get('user')['username'] ?? 'unknown'
            ));

            json(['ok' => true, 'message' => 'Event telah dihapus.']);
        } catch (\Throwable $e) {
            error_log('[EventController::destroy] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus event.'], 500);
        }
    }

    /* ============================================================
       ADMIN — BULK ACTIONS
       ============================================================ */

    /**
     * Bulk delete events (JSON body: {ids: [1,2,3]}).
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
            json(['ok' => false, 'message' => 'Tidak ada event yang dipilih.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        $ids = array_filter($ids, fn($id) => $id > 0);
        if (empty($ids)) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        try {
            $affected = Event::bulkDelete(array_values($ids));

            error_log(sprintf(
                '[EventController] Bulk delete: affected=%d by=%s',
                $affected, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => "$affected event berhasil dihapus.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[EventController::bulkDelete] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus event.'], 500);
        }
    }

    /* ============================================================
       ADMIN — EXPORT
       ============================================================ */

    /**
     * Export events ke CSV.
     * GET /admin/events/export?status=upcoming
     */
    public function export(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $status = trim((string) ($_GET['status'] ?? ''));

        try {
            $events = Event::adminList($status, '', 'event_date', 'desc', 1, 10000);
        } catch (\Throwable $e) {
            error_log('[EventController::export] ' . $e->getMessage());
            http_response_code(500);
            echo 'Export gagal.';
            return;
        }

        $filename = 'events-' . ($status ?: 'semua') . '-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

        if (empty($events)) {
            fputcsv($out, ['Tidak ada data event']);
            fclose($out);
            exit;
        }

        fputcsv($out, ['ID', 'Judul', 'Tanggal', 'Tanggal Selesai', 'Waktu', 'Lokasi', 'Status', 'Dibuat']);

        foreach ($events as $e) {
            fputcsv($out, [
                $e['id'] ?? '',
                $e['title'] ?? '',
                $e['date_short'] ?? ($e['event_date'] ?? ''),
                $e['end_date_short'] ?? '',
                $e['time_formatted'] ?? ($e['event_time'] ?? ''),
                $e['location'] ?? '',
                $e['status_label'] ?? ($e['status'] ?? ''),
                $e['created_at'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    /* ============================================================
       PRIVATE: VALIDATION
       ============================================================ */

    private function validate(): array
    {
        $errors = [];
        $title  = trim((string) ($_POST['title'] ?? ''));
        $date   = (string) ($_POST['event_date'] ?? '');
        $endDate = trim((string) ($_POST['end_date'] ?? ''));
        $time   = (string) ($_POST['event_time'] ?? '');
        $loc    = trim((string) ($_POST['location'] ?? ''));
        $desc   = trim((string) ($_POST['description'] ?? ''));

        // Title: min 3, max 200
        if (mb_strlen($title) < 3) {
            $errors['title'] = 'Nama event minimal 3 karakter.';
        } elseif (mb_strlen($title) > 200) {
            $errors['title'] = 'Nama event maksimal 200 karakter.';
        }

        // Event date: required + valid
        if ($date === '') {
            $errors['event_date'] = 'Tanggal event wajib diisi.';
        } elseif (!strtotime($date)) {
            $errors['event_date'] = 'Format tanggal tidak valid.';
        }

        // End date: optional, tapi jika diisi harus valid & >= event_date
        if ($endDate !== '') {
            if (!strtotime($endDate)) {
                $errors['end_date'] = 'Format tanggal selesai tidak valid.';
            } elseif ($date !== '' && strtotime($endDate) < strtotime($date)) {
                $errors['end_date'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            }
        }

        // Time: optional, tapi jika diisi harus format HH:MM
        if ($time !== '' && !preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            $errors['event_time'] = 'Format waktu tidak valid (HH:MM).';
        }

        // Location: max 200
        if (mb_strlen($loc) > 200) {
            $errors['location'] = 'Lokasi maksimal 200 karakter.';
        }

        // Description: max 5000
        if (mb_strlen($desc) > 5000) {
            $errors['description'] = 'Deskripsi maksimal 5000 karakter.';
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return [
            'title'       => $title,
            'event_date'  => $date,
            'end_date'    => $endDate !== '' ? $endDate : null,
            'event_time'  => $time,
            'location'    => $loc,
            'description' => $desc,
        ];
    }

    /* ============================================================
       PRIVATE: COVER UPLOAD
       ============================================================ */

    /**
     * @return string|null|false Filename, null jika tidak ada, false jika error
     */
    private function handleCoverUpload(): ?string
    {
        $file = $_FILES['cover'] ?? null;

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return false;
        }

        // MIME validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return false;
        }

        if (@getimagesize($file['tmp_name']) === false) {
            return false;
        }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $name = 'event-' . date('Ymd_His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        $dir = $this->getUploadDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return false;
        }

        return $name;
    }

    private function deleteCover(string $filename): void
    {
        if (empty($filename)) return;
        $path = $this->getUploadDir() . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function getUploadDir(): string
    {
        if (function_exists('public_path')) {
            return rtrim(public_path(self::UPLOAD_DIR), '/') . '/';
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return rtrim($base . '/' . self::UPLOAD_DIR, '/') . '/';
    }

    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[EventController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}