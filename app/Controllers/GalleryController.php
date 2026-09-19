<?php
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Gallery;

class GalleryController {
    public function index(): void {
        $page    = max(1, (int) ($_GET['halaman'] ?? 1));
        $perPage = 12;
        $total   = Gallery::countPublic();
        $pages   = max(1, (int) ceil($total / $perPage));

        View::render('pages/gallery', [
            'title'     => 'Galeri Kegiatan',
            'loggedIn'  => (bool) Session::get('user'),
            'galleries' => Gallery::publicAll($page, $perPage),
            'page'      => $page,
            'pages'     => $pages,
            'total'     => $total,
        ], 'layouts/public');
    }
}