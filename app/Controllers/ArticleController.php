<?php
// File: app/Controllers/ArticleController.php
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Article;

class ArticleController {
    /* ================= SISI PUBLIK ================= */

    /** Daftar artikel untuk tamu: /artikel?kategori=X&halaman=N */
    public function index(): void {
        $category = trim((string) ($_GET['kategori'] ?? ''));
        $page     = max(1, (int) ($_GET['halaman'] ?? 1));
        $perPage  = 6;
        $total    = Article::countPublished($category);
        $pages    = max(1, (int) ceil($total / $perPage));

        View::render('pages/articles/list', [
            'title'      => 'Artikel',
            'loggedIn'   => (bool) Session::get('user'),
            'articles'   => Article::published($category, $page, $perPage),
            'categories' => Article::CATEGORIES,
            'active'     => $category,
            'page'       => $page,
            'pages'      => $pages,
            'total'      => $total,
        ], 'layouts/public');
    }

    /** Detail artikel untuk tamu: /artikel/{id} */
    public function show(string $id): void {
        $article = Article::find((int) $id);
        if ($article === null) {
            redirect('artikel');
        }
        View::render('pages/articles/detail', [
            'title'    => $article['title'],
            'loggedIn' => (bool) Session::get('user'),
            'article'  => $article,
            'related'  => Article::latest(3, (int) $article['id']),
        ], 'layouts/public');
    }

    /* ================= SISI ADMIN ================= */

    public function manage(): void {
        Auth::handle();
        AdminOnly::handle();
        View::render('pages/articles/manage', [
            'title'      => 'Manajemen Artikel',
            'user'       => Session::get('user'),
            'categories' => Article::CATEGORIES,
        ], 'layouts/app');
    }

    public function api(): void {
        Auth::handle();
        AdminOnly::handle();
        $q       = trim((string) ($_GET['q'] ?? ''));
        $perPage = 8;
        $total   = Article::countSearch($q);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        json([
            'data' => Article::search($q, $page, $perPage),
            'meta' => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    public function store(): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
        }
        Article::create($data, (int) Session::get('user')['id']);
        json(['ok' => true, 'message' => 'Artikel berhasil diterbitkan.']);
    }

    public function update(string $id): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        if (Article::find((int) $id) === null) {
            json(['ok' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
        }
        $data = $this->validate();
        if (isset($data['errors'])) {
            json(['ok' => false, 'errors' => $data['errors']], 422);
        }
        Article::update((int) $id, $data);
        json(['ok' => true, 'message' => 'Perubahan artikel telah disimpan.']);
    }

    public function destroy(string $id): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        if (Article::find((int) $id) === null) {
            json(['ok' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
        }
        Article::delete((int) $id);
        json(['ok' => true, 'message' => 'Artikel telah dihapus.']);
    }

    private function validate(): array {
        $errors   = [];
        $title    = trim((string) ($_POST['title'] ?? ''));
        $category = (string) ($_POST['category'] ?? 'Artikel');
        $excerpt  = trim((string) ($_POST['excerpt'] ?? ''));
        $content  = trim((string) ($_POST['content'] ?? ''));

        if (mb_strlen($title) < 5)   $errors['title']   = 'Judul minimal 5 karakter.';
        if (!in_array($category, Article::CATEGORIES, true)) $errors['category'] = 'Kategori tidak valid.';
        if (mb_strlen($content) < 20) $errors['content'] = 'Isi artikel minimal 20 karakter.';

        return $errors !== []
            ? ['errors' => $errors]
            : ['title' => $title, 'category' => $category, 'excerpt' => $excerpt, 'content' => $content];
    }
}