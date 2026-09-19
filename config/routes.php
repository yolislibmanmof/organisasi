<?php
// File: config/routes.php (FINAL - TAHAP 5.7)
declare(strict_types=1);

if (!defined('APP_EXEC')) {
    die('Akses tidak diizinkan.');
}

/* ----------------------------------------------------------------
 | RUTE PUBLIK — beranda, artikel, galeri, tentang, event untuk tamu
 | ---------------------------------------------------------------- */
$router->get('/', 'HomeController@index');
$router->get('/artikel', 'ArticleController@index');
$router->get('/artikel/{id}', 'ArticleController@show');
$router->get('/galeri', 'GalleryController@index');
$router->get('/tentang', 'AboutController@index');
$router->get('/event', 'EventController@publicIndex');

/* ----------------------------------------------------------------
 | RUTE PUBLIK — SEO & SITEMAP
 | ---------------------------------------------------------------- */
$router->get('/sitemap.xml', 'SitemapController@index');

/* ----------------------------------------------------------------
 | RUTE AUTENTIKASI — login & logout
 | ---------------------------------------------------------------- */
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@processLogin');
$router->get('/logout', 'AuthController@logout');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — dashboard
 | ---------------------------------------------------------------- */
$router->get('/dashboard', 'DashboardController@index');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — MANAJEMEN ANGGOTA
 | ---------------------------------------------------------------- */
$router->get('/members', 'MemberController@index');
$router->get('/api/members', 'MemberController@api');
$router->get('/api/members/export', 'MemberController@export');
$router->get('/api/stats/registrations', 'DashboardController@statsRegistrations');
$router->post('/members/store', 'MemberController@store');
$router->post('/members/update/{id}', 'MemberController@update');
$router->post('/members/delete/{id}', 'MemberController@destroy');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — MODUL EVENT (ADMIN)
 | ---------------------------------------------------------------- */
$router->get('/events', 'EventController@index');
$router->get('/api/events', 'EventController@api');
$router->post('/events/store', 'EventController@store');
$router->post('/events/update/{id}', 'EventController@update');
$router->post('/events/delete/{id}', 'EventController@destroy');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — PROFIL & PENGATURAN
 | ---------------------------------------------------------------- */
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update', 'ProfileController@update');
$router->post('/profile/password', 'ProfileController@updatePassword');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG (ADMIN ONLY) — MANAJEMEN ARTIKEL
 | ---------------------------------------------------------------- */
$router->get('/articles', 'ArticleController@manage');
$router->get('/api/articles', 'ArticleController@api');
$router->post('/articles/store', 'ArticleController@store');
$router->post('/articles/update/{id}', 'ArticleController@update');
$router->post('/articles/delete/{id}', 'ArticleController@destroy');

/* ----------------------------------------------------------------
 | RUTE ADMIN — NOTIFIKASI
 | ---------------------------------------------------------------- */
$router->get('/api/notifications', 'NotificationController@feed');

/* ----------------------------------------------------------------
 | RUTE PUBLIK — SENSUS ANGGOTA
 | ---------------------------------------------------------------- */
$router->get('/sensus', 'CensusController@form');
$router->post('/sensus/store', 'CensusController@store');

/* ----------------------------------------------------------------
 | RUTE ADMIN — KONTEN SITUS
 | ---------------------------------------------------------------- */
$router->get('/content', 'ContentController@manage');
$router->get('/api/content/{type}', 'ContentController@api');
$router->post('/content/store', 'ContentController@store');
$router->post('/content/update/{id}', 'ContentController@update');
$router->post('/content/delete/{id}', 'ContentController@destroy');

/* ----------------------------------------------------------------
 | RUTE ADMIN — SENSUS
 | ---------------------------------------------------------------- */
$router->get('/census', 'CensusController@manage');
$router->get('/api/census', 'CensusController@api');
$router->post('/census/approve/{id}', 'CensusController@approve');
$router->post('/census/delete/{id}', 'CensusController@destroy');

/* ----------------------------------------------------------------
 | RUTE ADMIN — PENGATURAN SITUS
 | ---------------------------------------------------------------- */
$router->get('/settings', 'SettingController@index');
$router->post('/settings/save', 'SettingController@save');
$router->post('/settings/remove-asset', 'SettingController@removeAsset');

/* ----------------------------------------------------------------
 | RUTE FALLBACK — 404
 | ---------------------------------------------------------------- */
$router->fallback('ErrorController@notFound');