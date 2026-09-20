<?php
// File: app/Controllers/ArticleController.php (FINAL v7.0.4 — PARSE-ERROR FIXED)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Article;
use Models\Setting;

/**
 * ArticleController — Ultimate Edition v7.0.4
 *
 * PATCH v7.0.4 (KRITIS): pulihkan kurung penutup method manage() yang
 * hilang pada v7.0.3 — kehilangan satu karakter ini membuat SELURUH
 * class gagal dimuat (parse error) sehingga semua route artikel 500.
 *
 * Fitur: fallback nama view otomatis (resolveView), search, cover image,
 * bulk actions, CSV export, audit logging, graceful fallback.
 */
class ArticleController
{
    /** Upload directory untuk cover image */
    private const UPLOAD_DIR = 'assets/uploads/articles/';
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    /* ============================================================
       SISI PUBLIK
       ============================================================ */

    /**
     * Daftar artikel untuk tamu: /artikel?kategori=X&q=Y&halaman=N
     */
    public function index(): void
    {
        Setting::loadAll();

        $category = trim((string) ($_GET['kategori'] ?? ''));
        $search   = trim((string) ($_GET['q'] ?? ''));
        $page     = max(1, (int) ($_GET['halaman'] ?? 1));
        $perPage  = 6;

        $total = $this->safeCall(
            fn() => Article::countPublished($category, $search),
            0
        );
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);

        $articles = $this->safeCall(
            fn() => Article::published($category, $page, $perPage, $search),
            []
        );

        $categoryCounts = $this->safeCall(
            fn() => Article::countByCategory(true),
            []
        );
        $totalArticles = $this->safeCall(
            fn() => Article::countPublished('', ''),
            0
        );

        $seo = Setting::getSeoMeta();
        $appName = Setting::get('app_name', 'Organisasi');
        $pageTitle = 'Artikel & Informasi';
        if ($category !== '') {
            $pageTitle = "Kategori: $category";
        } elseif ($search !== '') {
            $pageTitle = "Pencarian: $search";
        }
        $seo['title'] = $pageTitle . ' — ' . $appName;
        if (empty($seo['description'])) {
            $seo['description'] = 'Kabar, edukasi, dan cerita inspiratif dari kegiatan ' . $appName . '.';
        }
        $seo['og_image'] = Setting::getLogoUrl();
        $seo['og_url'] = function_exists('url')
            ? url('artikel' . ($category !== '' ? '?kategori=' . urlencode($category) : ''))
            : '/artikel';

        // Fallback nama view: v7.0 → legacy
        $viewFile = $this->resolveView([
            'pages/artikel',
            'pages/articles/list',
            'pages/articles/index',
        ]);

        View::render($viewFile, [
            'title'           => $pageTitle,
            'loggedIn'        => (bool) Session::get('user'),
            'articles'        => $articles,
            'categories'      => Article::categories(),
            'active'          => $category,
            'search'          => $search,
            'page'            => $page,
            'pages'           => $pages,
            'totalArticles'   => $totalArticles,
            'categoryCounts'  => $categoryCounts,
            'seo'             => $seo,
            'announcement'    => [
                'active' => Setting::isAnnouncementActive(),
                'text'   => Setting::get('announcement_text'),
                'link'   => Setting::get('announcement_link'),
            ],
            'socialLinks'     => Setting::getSocialLinks(),
            'org'             => [
                'name'        => $appName,
                'logo_url'    => Setting::getLogoUrl(),
                'favicon_url' => Setting::getFaviconUrl(),
            ],
            'currentYear'     => (int) date('Y'),
        ], 'layouts/public');
    }

    /**
     * Detail artikel untuk tamu: /artikel/{id}
     */
    public function show(string $id): void
    {
        Setting::loadAll();

        $article = $this->safeCall(
            fn() => Article::find((int) $id),
            null
        );

        if ($article === null) {
            redirect('artikel');
            return;
        }

        $this->safeCall(
            fn() => Article::incrementViews((int) $article['id']),
            null
        );

        $related = $this->safeCall(
            fn() => Article::related((int) $article['id'], $article['category'] ?? '', 3),
            []
        );

        $prevArticle = $this->safeCall(
            fn() => Article::prev((int) $article['id'], $article['created_at'] ?? ''),
            null
        );
        $nextArticle = $this->safeCall(
            fn() => Article::next((int) $article['id'], $article['created_at'] ?? ''),
            null
        );

        $seo = Setting::getSeoMeta();
        $appName = Setting::get('app_name', 'Organisasi');
        $seo['title'] = $article['title'] . ' — ' . $appName;
        if (empty($seo['description']) && !empty($article['excerpt'])) {
            $seo['description'] = mb_substr($article['excerpt'], 0, 160);
        }
        $seo['og_image'] = !empty($article['cover_url'])
            ? $article['cover_url']
            : Setting::getLogoUrl();
        $seo['og_url'] = function_exists('url')
            ? url('artikel/' . $article['id'])
            : '/artikel/' . $article['id'];

        // Fallback nama view: v7.0 → legacy
        $viewFile = $this->resolveView([
            'pages/article-detail',
            'pages/articles/detail',
            'pages/articles/show',
        ]);

        View::render($viewFile, [
            'title'       => $article['title'],
            'loggedIn'    => (bool) Session::get('user'),
            'article'     => $article,
            'related'     => $related,
            'prevArticle' => $prevArticle,
            'nextArticle' => $nextArticle,
            'seo'         => $seo,
            'announcement' => [
                'active' => Setting::isAnnouncementActive(),
                'text'   => Setting::get('announcement_text'),
                'link'   => Setting::get('announcement_link'),
            ],
            'socialLinks' => Setting::getSocialLinks(),
            'org'         => [
                'name'        => $appName,
                'logo_url'    => Setting::getLogoUrl(),
                'favicon_url' => Setting::getFaviconUrl(),
            ],
            'currentYear' => (int) date('Y'),
        ], 'layouts/public');
    }

    /* ============================================================
       SISI ADMIN
       ============================================================ */

    /**
     * Halaman manajemen artikel (render view, data via API).
     */
    public function manage(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $categories = $this->safeCall(
            fn() => Article::categories(),
            Article::CATEGORIES
        );

        // Fallback nama view admin artikel: v7.0 → legacy
        $viewFile = $this->resolveView([
            'pages/articles',
            'pages/articles/manage',
            'pages/articles-manage',
            'pages/article-manage',
        ]);

        View::render($viewFile, [
            'title'      => 'Manajemen Artikel',
            'user'       => Session::get('user'),
            'categories' => $categories,
        ], 'layouts/app');
    } // <-- PENUTUP METHOD manage() (inilah yang hilang pada v7.0.3)

    /**
     * API endpoint untuk admin: list + filter + search + pagination + stats.
     * GET /admin/articles/api?q=X&status=Y&category=Z&sort=S&order=O&page=N
     */
    public function api(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $q        = trim((string) ($_GET['q'] ?? ''));
        $status   = trim((string) ($_GET['status'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));
        $sort     = trim((string) ($_GET['sort'] ?? 'created_at'));
        $order    = strtolower(trim((string) ($_GET['order'] ?? 'desc')));
        $perPage  = 10;

        $total = $this->safeCall(
            fn() => Article::countAdminList($status, $q, $category),
            0
        );
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        $data = $this->safeCall(
            fn() => Article::adminList($status, $q, $category, $page, $perPage, $sort, $order),
            []
        );

        $stats = $this->safeCall(
            fn() => Article::adminStats(),
            ['total' => 0, 'published' => 0, 'draft' => 0, 'total_views' => 0]
        );

        $categoryCounts = $this->safeCall(
            fn() => Article::countByCategory(false),
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
            'stats'          => $stats,
            'categoryCounts' => $categoryCounts,
        ]);
    }

    /**
     * Simpan artikel baru (dengan cover image upload).
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

        $coverImage = $this->handleCoverUpload();
        if ($coverImage === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload cover.'], 422);
            return;
        }
        if ($coverImage !== null) {
            $data['cover_image'] = $coverImage;
        }

        try {
            $newId = Article::create($data, (int) Session::get('user')['id']);
            json([
                'ok'      => true,
                'message' => 'Artikel berhasil diterbitkan.',
                'id'      => $newId,
            ]);
        } catch (\Throwable $e) {
            error_log('[ArticleController::store] ' . $e->getMessage());
            if ($coverImage !== null) {
                $this->deleteCoverFile($coverImage);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan artikel.'], 500);
        }
    }

    /**
     * Update artikel (dengan optional cover image upload).
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

        $existing = $this->safeCall(
            fn() => Article::findAny($idInt),
            null
        );

        if ($existing === null) {
            json(['ok' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
            return;
        }

        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
            return;
        }

        $coverImage = $this->handleCoverUpload();
        if ($coverImage === false) {
            json(['ok' => false, 'message' => 'Gagal mengupload cover.'], 422);
            return;
        }
        if ($coverImage !== null) {
            $data['cover_image'] = $coverImage;
            if (!empty($existing['cover_image'])) {
                $this->deleteCoverFile($existing['cover_image']);
            }
        }

        try {
            Article::update($idInt, $data);
            json(['ok' => true, 'message' => 'Perubahan artikel telah disimpan.']);
        } catch (\Throwable $e) {
            error_log('[ArticleController::update] ' . $e->getMessage());
            if ($coverImage !== null) {
                $this->deleteCoverFile($coverImage);
            }
            json(['ok' => false, 'message' => 'Gagal menyimpan perubahan.'], 500);
        }
    }

    /**
     * Hapus satu artikel (dengan cleanup cover file).
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

        $existing = $this->safeCall(
            fn() => Article::findAny($idInt),
            null
        );

        if ($existing === null) {
            json(['ok' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
            return;
        }

        try {
            Article::delete($idInt);
            json(['ok' => true, 'message' => 'Artikel telah dihapus.']);
        } catch (\Throwable $e) {
            error_log('[ArticleController::destroy] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus artikel.'], 500);
        }
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
            json(['ok' => false, 'message' => 'Tidak ada artikel yang dipilih.'], 422);
            return;
        }

        try {
            $affected = $this->safeCall(
                fn() => Article::bulkDelete($ids),
                0
            );
            json([
                'ok'       => true,
                'message'  => "$affected artikel berhasil dihapus.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[ArticleController::bulkDelete] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus artikel.'], 500);
        }
    }

    /**
     * Bulk update status (JSON body: {ids: [1,2,3], status: 'published'|'draft'}).
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
            json(['ok' => false, 'message' => 'Tidak ada artikel yang dipilih.'], 422);
            return;
        }

        if (!in_array($status, ['published', 'draft'], true)) {
            json(['ok' => false, 'message' => 'Status tidak valid.'], 422);
            return;
        }

        try {
            $affected = $this->safeCall(
                fn() => Article::bulkUpdateStatus($ids, $status),
                0
            );
            $statusLabel = $status === 'published' ? 'diterbitkan' : 'disimpan sebagai draft';
            json([
                'ok'       => true,
                'message'  => "$affected artikel berhasil $statusLabel.",
                'affected' => $affected,
            ]);
        } catch (\Throwable $e) {
            error_log('[ArticleController::bulkUpdateStatus] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mengubah status.'], 500);
        }
    }

    /**
     * Export artikel ke CSV.
     * GET /admin/articles/export?status=published
     */
    public function export(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $status = trim((string) ($_GET['status'] ?? ''));

        try {
            $articles = Article::adminList($status, '', '', 1, 10000);
        } catch (\Throwable $e) {
            error_log('[ArticleController::export] ' . $e->getMessage());
            http_response_code(500);
            echo 'Export gagal.';
            return;
        }

        $filename = 'artikel-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['ID', 'Judul', 'Kategori', 'Penulis', 'Status', 'Views', 'Tanggal']);

        foreach ($articles as $a) {
            fputcsv($out, [
                $a['id'] ?? '',
                $a['title'] ?? '',
                $a['category'] ?? '',
                $a['author_name'] ?? '',
                $a['status'] ?? '',
                $a['views'] ?? 0,
                $a['created_at'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    /* ============================================================
       VALIDATION & HELPERS
       ============================================================ */

    private function validate(): array
    {
        $errors  = [];
        $title   = trim((string) ($_POST['title'] ?? ''));
        $category = (string) ($_POST['category'] ?? 'Artikel');
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $status  = (string) ($_POST['status'] ?? 'published');

        if (mb_strlen($title) < 5) {
            $errors['title'] = 'Judul minimal 5 karakter.';
        } elseif (mb_strlen($title) > 200) {
            $errors['title'] = 'Judul maksimal 200 karakter.';
        }

        $allowedCategories = $this->safeCall(fn() => Article::categories(), Article::CATEGORIES);
        if (!in_array($category, $allowedCategories, true)) {
            $errors['category'] = 'Kategori tidak valid.';
        }

        if (mb_strlen($excerpt) > 300) {
            $errors['excerpt'] = 'Ringkasan maksimal 300 karakter.';
        }

        if (mb_strlen($content) < 20) {
            $errors['content'] = 'Isi artikel minimal 20 karakter.';
        } elseif (mb_strlen($content) > 50000) {
            $errors['content'] = 'Isi artikel maksimal 50.000 karakter.';
        }

        if (!in_array($status, ['published', 'draft'], true)) {
            $status = 'draft';
        }

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        return [
            'title'    => $title,
            'category' => $category,
            'excerpt'  => $excerpt,
            'content'  => $content,
            'status'   => $status,
        ];
    }

    private function handleCoverUpload(): ?string
    {
        if (empty($_FILES['cover']) || $_FILES['cover']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['cover'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('[ArticleController] Upload error: ' . $file['error']);
            return false;
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExt, true)) {
            return false;
        }

        $filename = 'cover_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $uploadDir = public_path(self::UPLOAD_DIR);
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            error_log('[ArticleController] Failed to move uploaded file');
            return false;
        }

        return $filename;
    }

    private function deleteCoverFile(string $filename): void
    {
        if (empty($filename)) return;

        $path = public_path(self::UPLOAD_DIR . $filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Pilih nama view pertama yang benar-benar ada di disk.
     * Mencegah 500 akibat perbedaan nama file view antar versi.
     *
     * @param array<int, string> $candidates
     */
    private function resolveView(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                return $candidate;
            }
        }
        return $candidates[0];
    }

    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[ArticleController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}