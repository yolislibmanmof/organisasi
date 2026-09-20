<?php
// File: app/Controllers/ContentController.php (FINAL v7.0 — EXTENDED + BULK + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Gallery;
use Models\Officer;
use Models\Testimonial;

/**
 * ContentController — Ultimate Edition v7.0
 *
 * Menangani CRUD untuk 3 tipe konten: officers, testimonials, galleries.
 * Extension v7.0:
 * - Bulk actions (delete, reorder, update status)
 * - API dengan filter + search + pagination + stats per tipe
 * - Support rating & status workflow untuk testimonials
 * - Support aspect_ratio untuk galleries
 * - Support social media untuk officers
 * - Photo upload dengan validasi MIME & size
 * - Graceful fallback
 * - CSV export
 * - Audit logging
 */
class ContentController
{
    /** Tipe konten yang didukung */
    private const TYPES = ['officers', 'testimonials', 'galleries'];

    /** Upload directory mapping */
    private const UPLOAD_DIRS = [
        'officers'     => 'assets/uploads/officers/',
        'testimonials' => 'assets/uploads/testimonials/',
        'galleries'    => 'assets/uploads/galleries/',
    ];

    /** Max file size (20 MB untuk kualitas tinggi) */
    private const MAX_FILE_SIZE = 20 * 1024 * 1024;

    /** Allowed MIME types */
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    /* ============================================================
       MANAGE PAGE (VIEW)
       ============================================================ */

    /**
     * Halaman manajemen konten (render view, data via API).
     */
    public function manage(): void
    {
        Auth::handle();
        AdminOnly::handle();

        // Stats per tipe dengan graceful fallback
        $stats = $this->gatherStats();

        // Pending counts untuk badges
        $pending = $this->gatherPendingCounts();

        View::render('pages/content', [
            'title'  => 'Manajemen Konten',
            'user'   => Session::get('user'),
            'stats'  => $stats,
            'counts' => [  // backward compat
                'officers'     => $stats['officers']['total'],
                'testimonials' => $stats['testimonials']['total'],
                'galleries'    => $stats['galleries']['total'],
            ],
            'pending' => $pending,
            'types'   => self::TYPES,
        ], 'layouts/app');
    }

    /* ============================================================
       API ENDPOINT (list + filter + search + pagination + stats)
       ============================================================ */

    /**
     * API endpoint untuk list konten per tipe.
     * GET /admin/content/api/{type}?q=X&status=Y&year=Z&sort=S&order=O&page=N
     */
    public function api(string $type): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!in_array($type, self::TYPES, true)) {
            json(['ok' => false, 'message' => 'Tipe tidak valid.'], 404);
            return;
        }

        $q       = trim((string) ($_GET['q'] ?? ''));
        $status  = trim((string) ($_GET['status'] ?? ''));
        $year    = trim((string) ($_GET['year'] ?? ''));
        $location = trim((string) ($_GET['location'] ?? ''));
        $sort    = trim((string) ($_GET['sort'] ?? ''));
        $order   = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 20)));

        try {
            $result = match ($type) {
                'officers'     => $this->apiOfficers($q, $sort, $order, $page, $perPage),
                'testimonials' => $this->apiTestimonials($q, $status, $sort, $order, $page, $perPage),
                'galleries'    => $this->apiGalleries($q, $year, $location, $page, $perPage),
            };

            json([
                'ok'   => true,
                'data' => $result['data'],
                'meta' => $result['meta'],
                'stats' => $result['stats'] ?? [],
                'filters' => $result['filters'] ?? [],
            ]);
        } catch (\Throwable $e) {
            error_log("[ContentController::api $type] " . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal memuat data.'], 500);
        }
    }

    /* ============================================================
       STORE (CREATE)
       ============================================================ */

    public function store(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $type = (string) ($_POST['type'] ?? '');
        $adminUser = Session::get('user');

        try {
            $result = match ($type) {
                'officers'     => $this->storeOfficer(),
                'testimonials' => $this->storeTestimonial(),
                'galleries'    => $this->storeGallery(),
                default        => ['ok' => false, 'message' => 'Tipe tidak valid.'],
            };

            if (!empty($result['ok'])) {
                error_log(sprintf(
                    '[ContentController] Created %s by %s',
                    $type, $adminUser['username'] ?? 'unknown'
                ));
            }

            json($result, empty($result['ok']) ? ($result['status'] ?? 422) : 200);
        } catch (\Throwable $e) {
            error_log("[ContentController::store $type] " . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menyimpan konten: ' . $e->getMessage()], 500);
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
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $type = (string) ($_POST['type'] ?? '');
        $adminUser = Session::get('user');

        try {
            $result = match ($type) {
                'officers'     => $this->updateOfficer($id),
                'testimonials' => $this->updateTestimonial($id),
                'galleries'    => $this->updateGallery($id),
                default        => ['ok' => false, 'message' => 'Tipe tidak valid.'],
            };

            if (!empty($result['ok'])) {
                error_log(sprintf(
                    '[ContentController] Updated %s id=%d by %s',
                    $type, $id, $adminUser['username'] ?? 'unknown'
                ));
            }

            json($result, empty($result['ok']) ? ($result['status'] ?? 422) : 200);
        } catch (\Throwable $e) {
            error_log("[ContentController::update $type id=$id] " . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menyimpan perubahan: ' . $e->getMessage()], 500);
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
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        $type = (string) ($_POST['type'] ?? '');
        $adminUser = Session::get('user');

        try {
            $result = match ($type) {
                'officers'     => $this->destroyOfficer($id),
                'testimonials' => $this->destroyTestimonial($id),
                'galleries'    => $this->destroyGallery($id),
                default        => ['ok' => false, 'message' => 'Tipe tidak valid.'],
            };

            if (!empty($result['ok'])) {
                error_log(sprintf(
                    '[ContentController] Deleted %s id=%d by %s',
                    $type, $id, $adminUser['username'] ?? 'unknown'
                ));
            }

            json($result);
        } catch (\Throwable $e) {
            error_log("[ContentController::destroy $type id=$id] " . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus konten: ' . $e->getMessage()], 500);
        }
    }

    /* ============================================================
       BULK ACTIONS (BARU v7.0)
       ============================================================ */

    /**
     * Bulk delete (JSON body: {type: 'officers', ids: [1,2,3]}).
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
        $type = (string) ($input['type'] ?? ($_POST['type'] ?? ''));
        $ids = $input['ids'] ?? ($_POST['ids'] ?? []);

        if (!in_array($type, self::TYPES, true)) {
            json(['ok' => false, 'message' => 'Tipe tidak valid.'], 422);
            return;
        }

        if (!is_array($ids) || empty($ids)) {
            json(['ok' => false, 'message' => 'Tidak ada item yang dipilih.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        $ids = array_filter($ids, fn($id) => $id > 0);
        if (empty($ids)) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        try {
            $affected = match ($type) {
                'officers'     => Officer::bulkDelete($ids),
                'testimonials' => Testimonial::bulkDelete($ids),
                'galleries'    => Gallery::bulkDelete($ids),
            };

            error_log(sprintf(
                '[ContentController] Bulk delete %s: affected=%d by=%s',
                $type, $affected, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => "$affected item berhasil dihapus.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log("[ContentController::bulkDelete $type] " . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus item.'], 500);
        }
    }

    /**
     * Bulk update status testimonials (JSON body: {ids: [1,2,3], status: 'published'}).
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
        $type = (string) ($input['type'] ?? ($_POST['type'] ?? ''));
        $ids = $input['ids'] ?? ($_POST['ids'] ?? []);
        $status = (string) ($input['status'] ?? ($_POST['status'] ?? ''));

        // Hanya testimonials yang support status
        if ($type !== 'testimonials') {
            json(['ok' => false, 'message' => 'Tipe tidak mendukung update status.'], 422);
            return;
        }

        if (!is_array($ids) || empty($ids)) {
            json(['ok' => false, 'message' => 'Tidak ada item yang dipilih.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        $ids = array_filter($ids, fn($id) => $id > 0);
        if (empty($ids)) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        if (!in_array($status, ['published', 'pending', 'draft'], true)) {
            json(['ok' => false, 'message' => 'Status tidak valid.'], 422);
            return;
        }

        try {
            $affected = Testimonial::bulkUpdateStatus($ids, $status);

            error_log(sprintf(
                '[ContentController] Bulk status %s=%s: affected=%d by=%s',
                $type, $status, $affected, Session::get('user')['username'] ?? 'unknown'
            ));

            $statusLabel = match ($status) {
                'published' => 'diterbitkan',
                'pending'   => 'ditandai menunggu review',
                'draft'     => 'disimpan sebagai draft',
                default     => 'diupdate',
            };

            json([
                'ok'       => true,
                'message'  => "$affected testimoni berhasil $statusLabel.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log("[ContentController::bulkUpdateStatus] " . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mengubah status.'], 500);
        }
    }

    /**
     * Reorder officers (JSON body: {ids: [5,2,8,1,3]}).
     */
    public function reorderOfficers(): void
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
            json(['ok' => false, 'message' => 'Tidak ada item untuk diurutkan.'], 422);
            return;
        }

        $ids = array_map('intval', array_filter($ids));
        $ids = array_filter($ids, fn($id) => $id > 0);
        if (empty($ids)) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        try {
            $affected = Officer::reorder($ids);

            error_log(sprintf(
                '[ContentController] Reordered officers: %d items by %s',
                $affected, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'       => true,
                'message'  => "Urutan $affected pengurus berhasil diperbarui.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[ContentController::reorderOfficers] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mengurutkan.'], 500);
        }
    }

    /**
     * Approve testimonial (shortcut untuk publish).
     */
    public function approveTestimonial(string $id): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
            return;
        }

        try {
            Testimonial::approve($id);
            error_log(sprintf(
                '[ContentController] Approved testimonial id=%d by %s',
                $id, Session::get('user')['username'] ?? 'unknown'
            ));
            json(['ok' => true, 'message' => 'Testimoni berhasil diterbitkan.']);
        } catch (\Throwable $e) {
            error_log('[ContentController::approveTestimonial] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menerbitkan testimoni.'], 500);
        }
    }

    /* ============================================================
       EXPORT
       ============================================================ */

    /**
     * Export CSV untuk tipe konten tertentu.
     * GET /admin/content/export/{type}
     */
    public function export(string $type): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!in_array($type, self::TYPES, true)) {
            http_response_code(404);
            echo 'Tipe tidak valid.';
            return;
        }

        try {
            $rows = match ($type) {
                'officers'     => Officer::allForExport(),
                'testimonials' => Testimonial::allForExport(),
                'galleries'    => Gallery::all(),
            };
        } catch (\Throwable $e) {
            error_log("[ContentController::export $type] " . $e->getMessage());
            http_response_code(500);
            echo 'Export gagal.';
            return;
        }

        $filename = $type . '-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel UTF-8
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (empty($rows)) {
            fputcsv($out, ['Tidak ada data']);
            fclose($out);
            exit;
        }

        // Header
        $csvRow = match ($type) {
            'officers'     => Officer::toCsvRow($rows[0]),
            'testimonials' => Testimonial::toCsvRow($rows[0]),
            'galleries'    => [
                'ID' => '', 'Judul' => '', 'Tanggal' => '', 'Lokasi' => '',
            ],
        };
        fputcsv($out, array_keys($csvRow));

        foreach ($rows as $row) {
            $csvRow = match ($type) {
                'officers'     => Officer::toCsvRow($row),
                'testimonials' => Testimonial::toCsvRow($row),
                'galleries'    => [
                    'ID'       => $row['id'] ?? '',
                    'Judul'    => $row['title'] ?? '',
                    'Tanggal'  => $row['date_short'] ?? '',
                    'Lokasi'   => $row['location'] ?? '',
                ],
            };
            fputcsv($out, $csvRow);
        }

        fclose($out);
        exit;
    }

    /* ============================================================
       PRIVATE: API PER TIPE
       ============================================================ */

    private function apiOfficers(string $q, string $sort, string $order, int $page, int $perPage): array
    {
        $total = Officer::countAdminList('', $q);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);

        $data = Officer::adminList('', $q, $sort ?: 'sort_order', $order ?: 'asc', $page, $perPage);
        $stats = Officer::adminStats();
        $divisions = Officer::divisionsWithCount();

        return [
            'data' => $data,
            'meta' => ['page' => $page, 'pages' => $pages, 'total' => $total, 'perPage' => $perPage],
            'stats' => $stats,
            'filters' => ['divisions' => $divisions],
        ];
    }

    private function apiTestimonials(string $q, string $status, string $sort, string $order, int $page, int $perPage): array
    {
        $rating = (int) ($_GET['rating'] ?? 0);
        $total = Testimonial::countAdminList($status, $q, $rating);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);

        $data = Testimonial::adminList($status, $q, $rating, $sort ?: 'created_at', $order, $page, $perPage);
        $stats = Testimonial::adminStats();

        return [
            'data' => $data,
            'meta' => ['page' => $page, 'pages' => $pages, 'total' => $total, 'perPage' => $perPage],
            'stats' => $stats,
            'filters' => ['statuses' => Testimonial::STATUSES],
        ];
    }

    private function apiGalleries(string $q, string $year, string $location, int $page, int $perPage): array
    {
        $total = Gallery::countAdminList($year, $location, $q);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);

        $data = Gallery::adminList($year, $location, $q, $page, $perPage);
        $stats = Gallery::adminStats();
        $years = Gallery::yearsWithCount();
        $locations = Gallery::locationsWithCount();

        return [
            'data' => $data,
            'meta' => ['page' => $page, 'pages' => $pages, 'total' => $total, 'perPage' => $perPage],
            'stats' => $stats,
            'filters' => ['years' => $years, 'locations' => $locations],
        ];
    }

    /* ============================================================
       PRIVATE: STORE PER TIPE
       ============================================================ */

    private function storeOfficer(): array
    {
        $d = $this->validateOfficer();
        if (isset($d['errors'])) {
            return ['ok' => false, 'errors' => $d['errors'], 'status' => 422];
        }

        // Upload photo (optional)
        $up = $this->upload($_FILES['photo'] ?? null, 'officers', false);
        if (isset($up['error'])) {
            return ['ok' => false, 'message' => $up['error'], 'status' => 422];
        }
        if (!empty($up['name'])) {
            $d['photo'] = $up['name'];
        }

        // Parse social media (JSON dari form)
        $d['social'] = $this->parseSocialInput();

        // Auto-generate sort_order jika 0
        if (($d['sort_order'] ?? 0) === 0) {
            $d['sort_order'] = Officer::getNextSortOrder();
        }

        $id = Officer::create($d);
        return ['ok' => true, 'message' => 'Pengurus berhasil ditambahkan.', 'id' => $id];
    }

    private function storeTestimonial(): array
    {
        $d = $this->validateTestimonial();
        if (isset($d['errors'])) {
            return ['ok' => false, 'errors' => $d['errors'], 'status' => 422];
        }

        // Upload photo (optional)
        $up = $this->upload($_FILES['photo'] ?? null, 'testimonials', false);
        if (isset($up['error'])) {
            return ['ok' => false, 'message' => $up['error'], 'status' => 422];
        }
        if (!empty($up['name'])) {
            $d['photo'] = $up['name'];
        }

        $id = Testimonial::create($d);
        return ['ok' => true, 'message' => 'Testimoni berhasil ditambahkan.', 'id' => $id];
    }

    private function storeGallery(): array
    {
        $d = $this->validateGallery(false);
        if (isset($d['errors'])) {
            return ['ok' => false, 'errors' => $d['errors'], 'status' => 422];
        }

        // Upload image (required)
        $up = $this->upload($_FILES['image'] ?? null, 'galleries', true);
        if (isset($up['error'])) {
            return ['ok' => false, 'message' => $up['error'], 'status' => 422];
        }
        $d['image'] = $up['name'];

        // Detect aspect ratio dari gambar
        if (!empty($up['tmp_path'])) {
            $d['aspect_ratio'] = $this->detectAspectRatio($up['tmp_path']);
        }

        $id = Gallery::create($d);
        return ['ok' => true, 'message' => 'Galeri berhasil diunggah.', 'id' => $id];
    }

    /* ============================================================
       PRIVATE: UPDATE PER TIPE
       ============================================================ */

    private function updateOfficer(int $id): array
    {
        $old = Officer::find($id);
        if (!$old) return ['ok' => false, 'message' => 'Data tidak ditemukan.', 'status' => 404];

        $d = $this->validateOfficer();
        if (isset($d['errors'])) {
            return ['ok' => false, 'errors' => $d['errors'], 'status' => 422];
        }

        // Upload photo (optional, replace jika ada)
        $up = $this->upload($_FILES['photo'] ?? null, 'officers', false);
        if (isset($up['error'])) {
            return ['ok' => false, 'message' => $up['error'], 'status' => 422];
        }
        if (!empty($up['name'])) {
            $d['photo'] = $up['name'];
            // Cleanup foto lama
            if (!empty($old['photo'])) {
                $this->unlinkUpload('officers', $old['photo']);
            }
        } else {
            $d['photo'] = $old['photo'];
        }

        // Parse social media
        $d['social'] = $this->parseSocialInput();

        Officer::update($id, $d);
        return ['ok' => true, 'message' => 'Data pengurus berhasil diperbarui.'];
    }

    private function updateTestimonial(int $id): array
    {
        $old = Testimonial::find($id);
        if (!$old) return ['ok' => false, 'message' => 'Data tidak ditemukan.', 'status' => 404];

        $d = $this->validateTestimonial();
        if (isset($d['errors'])) {
            return ['ok' => false, 'errors' => $d['errors'], 'status' => 422];
        }

        // Upload photo (optional)
        $up = $this->upload($_FILES['photo'] ?? null, 'testimonials', false);
        if (isset($up['error'])) {
            return ['ok' => false, 'message' => $up['error'], 'status' => 422];
        }
        if (!empty($up['name'])) {
            $d['photo'] = $up['name'];
            if (!empty($old['photo'])) {
                $this->unlinkUpload('testimonials', $old['photo']);
            }
        }

        Testimonial::update($id, $d);
        return ['ok' => true, 'message' => 'Testimoni berhasil diperbarui.'];
    }

    private function updateGallery(int $id): array
    {
        $old = Gallery::find($id);
        if (!$old) return ['ok' => false, 'message' => 'Data tidak ditemukan.', 'status' => 404];

        $d = $this->validateGallery(true);
        if (isset($d['errors'])) {
            return ['ok' => false, 'errors' => $d['errors'], 'status' => 422];
        }

        // Upload image (optional, replace jika ada)
        $up = $this->upload($_FILES['image'] ?? null, 'galleries', false);
        if (isset($up['error'])) {
            return ['ok' => false, 'message' => $up['error'], 'status' => 422];
        }
        if (!empty($up['name'])) {
            $d['image'] = $up['name'];
            if (!empty($up['tmp_path'])) {
                $d['aspect_ratio'] = $this->detectAspectRatio($up['tmp_path']);
            }
            if (!empty($old['image'])) {
                $this->unlinkUpload('galleries', $old['image']);
            }
        } else {
            $d['image'] = $old['image'];
            $d['aspect_ratio'] = $old['aspect_ratio'] ?? '1:1';
        }

        Gallery::update($id, $d);
        return ['ok' => true, 'message' => 'Data galeri berhasil diperbarui.'];
    }

    /* ============================================================
       PRIVATE: DESTROY PER TIPE
       ============================================================ */

    private function destroyOfficer(int $id): array
    {
        $old = Officer::find($id);
        if (!$old) return ['ok' => false, 'message' => 'Data tidak ditemukan.', 'status' => 404];

        if (!empty($old['photo'])) {
            $this->unlinkUpload('officers', $old['photo']);
        }
        Officer::delete($id);
        return ['ok' => true, 'message' => 'Pengurus berhasil dihapus.'];
    }

    private function destroyTestimonial(int $id): array
    {
        $old = Testimonial::find($id);
        if (!$old) return ['ok' => false, 'message' => 'Data tidak ditemukan.', 'status' => 404];

        if (!empty($old['photo'])) {
            $this->unlinkUpload('testimonials', $old['photo']);
        }
        Testimonial::delete($id);
        return ['ok' => true, 'message' => 'Testimoni berhasil dihapus.'];
    }

    private function destroyGallery(int $id): array
    {
        $old = Gallery::find($id);
        if (!$old) return ['ok' => false, 'message' => 'Data tidak ditemukan.', 'status' => 404];

        if (!empty($old['image'])) {
            $this->unlinkUpload('galleries', $old['image']);
        }
        Gallery::delete($id);
        return ['ok' => true, 'message' => 'Galeri berhasil dihapus.'];
    }

    /* ============================================================
       PRIVATE: VALIDASI
       ============================================================ */

    private function validateOfficer(): array
    {
        $errors = [];
        $name   = trim((string) ($_POST['full_name'] ?? ''));
        $pos    = trim((string) ($_POST['position'] ?? ''));
        $div    = trim((string) ($_POST['division'] ?? ''));
        $bio    = trim((string) ($_POST['bio'] ?? ''));

        if (mb_strlen($name) < 3) $errors['full_name'] = 'Nama minimal 3 karakter.';
        elseif (mb_strlen($name) > 100) $errors['full_name'] = 'Nama maksimal 100 karakter.';

        if ($pos === '') $errors['position'] = 'Jabatan wajib diisi.';
        elseif (mb_strlen($pos) > 100) $errors['position'] = 'Jabatan maksimal 100 karakter.';

        if (mb_strlen($div) > 50) $errors['division'] = 'Divisi maksimal 50 karakter.';

        if (mb_strlen($bio) > 500) $errors['bio'] = 'Bio maksimal 500 karakter.';

        if (!empty($errors)) return ['errors' => $errors];

        return [
            'full_name'  => $name,
            'position'   => $pos,
            'division'   => $div !== '' ? $div : null,
            'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
            'bio'        => $bio,
        ];
    }

    private function validateTestimonial(): array
    {
        $errors = [];
        $name   = trim((string) ($_POST['name'] ?? ''));
        $role   = trim((string) ($_POST['role'] ?? ''));
        $quote  = trim((string) ($_POST['quote'] ?? ''));
        $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
        $status = (string) ($_POST['status'] ?? Testimonial::STATUS_PENDING);

        if (mb_strlen($name) < 3) $errors['name'] = 'Nama minimal 3 karakter.';
        elseif (mb_strlen($name) > 100) $errors['name'] = 'Nama maksimal 100 karakter.';

        if (mb_strlen($role) > 100) $errors['role'] = 'Jabatan maksimal 100 karakter.';

        if (mb_strlen($quote) < 10) $errors['quote'] = 'Testimoni minimal 10 karakter.';
        elseif (mb_strlen($quote) > 1000) $errors['quote'] = 'Testimoni maksimal 1000 karakter.';

        if (!in_array($status, Testimonial::STATUSES, true)) {
            $status = Testimonial::STATUS_PENDING;
        }

        if (!empty($errors)) return ['errors' => $errors];

        return [
            'name'   => $name,
            'role'   => $role,
            'quote'  => $quote,
            'rating' => $rating,
            'status' => $status,
        ];
    }

    private function validateGallery(bool $allowEmpty): array
    {
        $errors = [];
        $title = trim((string) ($_POST['title'] ?? ''));
        $eventDate = trim((string) ($_POST['event_date'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));

        if ($title === '') {
            $errors['title'] = 'Judul foto wajib diisi.';
        } elseif (mb_strlen($title) > 150) {
            $errors['title'] = 'Judul maksimal 150 karakter.';
        }

        if (mb_strlen($location) > 100) {
            $errors['location'] = 'Lokasi maksimal 100 karakter.';
        }

        // Validate date format jika diisi
        if ($eventDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            $errors['event_date'] = 'Format tanggal tidak valid (YYYY-MM-DD).';
        }

        if (!empty($errors)) return ['errors' => $errors];

        return [
            'title'      => $title,
            'event_date' => $eventDate,
            'location'   => $location,
        ];
    }

    /* ============================================================
       PRIVATE: UPLOAD & FILE HANDLING
       ============================================================ */

    /**
     * Upload file dengan validasi lengkap.
     *
     * @return array{name: string, tmp_path: string}|array{error: string}|array<empty>
     */
    private function upload(?array $file, string $folder, bool $required): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required ? ['error' => 'Berkas wajib diunggah.'] : [];
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'Berkas melebihi batas unggah server. Periksa php.ini.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Gagal mengunggah berkas (code: ' . $file['error'] . ').'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['error' => 'Ukuran maksimal ' . (self::MAX_FILE_SIZE / 1048576) . ' MB.'];
        }

        // Validasi MIME dengan finfo (lebih aman dari getimagesize)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return ['error' => 'Format didukung: JPG, PNG, WEBP.'];
        }

        // Double-check dengan getimagesize (validasi struktur gambar)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['error' => 'Berkas harus berupa gambar valid.'];
        }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $name = $folder . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dir = $this->getUploadPath($folder);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $targetPath = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['error' => 'Gagal menyimpan berkas ke server.'];
        }

        return ['name' => $name, 'tmp_path' => $targetPath];
    }

    /**
     * Hapus file upload dari disk (best-effort).
     */
    private function unlinkUpload(string $folder, string $name): void
    {
        if (empty($name)) return;

        $path = $this->getUploadPath($folder) . $name;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Get absolute path untuk upload directory.
     */
    private function getUploadPath(string $folder): string
    {
        $relative = self::UPLOAD_DIRS[$folder] ?? 'assets/uploads/' . $folder . '/';

        if (function_exists('public_path')) {
            return rtrim(public_path($relative), '/') . '/';
        }

        // Fallback
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return rtrim($base . '/' . $relative, '/') . '/';
    }

    /**
     * Detect aspect ratio dari gambar (untuk masonry layout).
     */
    private function detectAspectRatio(string $path): string
    {
        $info = @getimagesize($path);
        if ($info === false) return '1:1';

        $w = $info[0];
        $h = $info[1];
        if ($w <= 0 || $h <= 0) return '1:1';

        $ratio = $w / $h;

        // Klasifikasi ke rasio umum
        if ($ratio > 1.7) return '16:9';
        if ($ratio > 1.4) return '3:2';
        if ($ratio > 1.2) return '4:3';
        if ($ratio > 0.9) return '1:1';
        if ($ratio > 0.7) return '3:4';
        return '9:16';
    }

    /**
     * Parse social media input dari form (array dari input name="social[instagram]" dll).
     */
    private function parseSocialInput(): ?string
    {
        $social = $_POST['social'] ?? [];

        // Jika sudah JSON string
        if (is_string($social) && !empty($social)) {
            $decoded = json_decode($social, true);
            if (is_array($decoded)) {
                $social = $decoded;
            }
        }

        if (!is_array($social) || empty($social)) {
            return null;
        }

        // Sanitize: hanya field yang diizinkan
        $allowed = ['instagram', 'twitter', 'facebook', 'linkedin', 'email', 'phone', 'website'];
        $clean = [];
        foreach ($allowed as $field) {
            if (!empty($social[$field])) {
                $value = trim((string) $social[$field]);
                if ($value !== '') {
                    $clean[$field] = $value;
                }
            }
        }

        return !empty($clean) ? json_encode($clean) : null;
    }

    /* ============================================================
       PRIVATE: STATS & COUNTS
       ============================================================ */

    private function gatherStats(): array
    {
        $officerStats = $this->safeCall(fn() => Officer::adminStats(), [
            'total' => 0, 'divisions' => 0, 'with_photo' => 0, 'without_photo' => 0,
        ]);

        $testiStats = $this->safeCall(fn() => Testimonial::adminStats(), [
            'total' => 0, 'published' => 0, 'pending' => 0, 'draft' => 0,
            'avg_rating' => 0, 'with_photo' => 0, 'five_star' => 0,
        ]);

        $galleryStats = $this->safeCall(fn() => Gallery::adminStats(), [
            'total' => 0, 'years' => 0, 'locations' => 0, 'this_year' => 0,
        ]);

        return [
            'officers'     => $officerStats,
            'testimonials' => $testiStats,
            'galleries'    => $galleryStats,
        ];
    }

    private function gatherPendingCounts(): array
    {
        $testiStats = $this->safeCall(fn() => Testimonial::adminStats(), []);
        $pending = (int) ($testiStats['pending'] ?? 0);

        return [
            'testimonials' => $pending,
            'total'        => $pending,
        ];
    }

    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[ContentController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}