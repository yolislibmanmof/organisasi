<?php
// File: config/routes.php (FINAL v7.0.3 — LEGACY-COMPAT)
declare(strict_types=1);

if (!defined('APP_EXEC')) {
    die('Akses tidak diizinkan.');
}

/**
 * Route Configuration — Ultimate Edition v7.0.3
 *
 * PATCH v7.0.3 (perbaikan 404 pada semua menu admin):
 * Sidebar admin (layouts/app.php) dan seluruh file JS masih memakai URL lama
 * (/members, /events, /articles, /content, /census, /settings, /profile,
 *  /api/members, /api/events, ...). Pada v7.0.1 route lama dihapus sehingga
 *  semua menu admin jatuh ke fallback 404.
 *
 * Kini SEMUA URL lama didaftarkan KEMBALI di dalam group middleware yang
 * benar (Auth / Auth+AdminOnly), menunjuk ke method controller yang sama
 * dengan route /admin/*. Sidebar & JS TIDAK perlu diubah.
 */

/* ================================================================
 | 1. SEO & META ROUTES
 | ================================================================ */

$router->get('/robots.txt', 'SitemapController@robots');
$router->get('/sitemap.xml', 'SitemapController@index');
$router->get('/sitemap-static.xml', 'SitemapController@staticSitemap');
$router->get('/sitemap-articles-{page}.xml', 'SitemapController@articlesSitemap');
$router->get('/sitemap-events.xml', 'SitemapController@eventsSitemap');
$router->get('/sitemap-images.xml', 'SitemapController@imagesSitemap');

/* ================================================================
 | 2. PUBLIC ROUTES (Guest Accessible)
 | ================================================================ */

$router->get('/', 'HomeController@index', 'home');

// Artikel publik (hanya /artikel — /articles adalah route admin)
$router->get('/artikel', 'ArticleController@index', 'articles.index');
$router->get('/artikel/{id}', 'ArticleController@show', 'articles.show');

$router->get('/galeri', 'GalleryController@index', 'gallery.index');
$router->get('/tentang', 'AboutController@index', 'about');
$router->get('/event', 'EventController@publicIndex', 'events.public');

// Sensus publik (dengan rate limiting)
$router->group(['middleware' => ['RateLimitCensus']], function ($r) {
    $r->get('/sensus', 'CensusController@form', 'census.form');
    $r->post('/sensus', 'CensusController@store', 'census.store');
});

/* ================================================================
 | 3. AUTHENTICATION ROUTES
 | ================================================================ */

$router->group(['middleware' => ['RateLimitLogin']], function ($r) {
    $r->get('/login', 'AuthController@showLogin', 'login');
    $r->post('/login', 'AuthController@processLogin', 'login.process');
});

$router->get('/logout', 'AuthController@logout', 'logout');

/* ================================================================
 | 4. AUTHENTICATED ROUTES (Member + Admin)
 | URL baru (/profil/*) DAN URL lama (/profile/*) keduanya aktif.
 | ================================================================ */

$router->group(['middleware' => ['Auth']], function ($r) {

    /* ---------- Dashboard ---------- */
    $r->get('/dashboard', 'DashboardController@index', 'dashboard');
    $r->get('/dashboard/stats/registrations', 'DashboardController@statsRegistrations');
    $r->get('/dashboard/stats/events', 'DashboardController@statsEvents');
    $r->get('/dashboard/stats/articles', 'DashboardController@statsArticles');
    $r->get('/dashboard/system-info', 'DashboardController@systemInfo');

    /* ---------- Profile (baru) ---------- */
    $r->get('/profil', 'ProfileController@index', 'profile');
    $r->post('/profil/update', 'ProfileController@update', 'profile.update');
    $r->post('/profil/photo', 'ProfileController@updatePhoto', 'profile.photo');
    $r->post('/profil/photo/remove', 'ProfileController@removePhoto', 'profile.photo.remove');
    $r->post('/profil/password', 'ProfileController@updatePassword', 'profile.password');
    $r->post('/profil/delete', 'ProfileController@deleteAccount', 'profile.delete');
    $r->post('/profil/check-password', 'ProfileController@checkPassword');

    /* ---------- Profile (LEGACY — dipakai sidebar lama) ---------- */
    $r->get('/profile', 'ProfileController@index');
    $r->post('/profile/update', 'ProfileController@update');
    $r->post('/profile/photo', 'ProfileController@updatePhoto');
    $r->post('/profile/photo/remove', 'ProfileController@removePhoto');
    $r->post('/profile/password', 'ProfileController@updatePassword');
    $r->post('/profile/delete', 'ProfileController@deleteAccount');
    $r->post('/profile/check-password', 'ProfileController@checkPassword');

}); // End authenticated group

/* ================================================================
 | 5. ADMIN-ONLY ROUTES
 | URL baru (/admin/*) DAN URL lama (sidebar + JS lama) keduanya aktif.
 | ================================================================ */

$router->group(['middleware' => ['Auth', 'AdminOnly']], function ($r) {

    /* ========== MANAJEMEN ANGGOTA ========== */
    // URL baru
    $r->get('/admin/members', 'MemberController@index', 'admin.members');
    $r->get('/admin/members/api', 'MemberController@api');
    $r->get('/admin/members/export', 'MemberController@export');
    $r->post('/admin/members', 'MemberController@store', 'admin.members.store');
    $r->put('/admin/members/{id}', 'MemberController@update', 'admin.members.update');
    $r->delete('/admin/members/{id}', 'MemberController@destroy', 'admin.members.destroy');
    $r->post('/admin/members/bulk-delete', 'MemberController@bulkDelete');
    $r->post('/admin/members/bulk-status', 'MemberController@bulkUpdateStatus');
    // URL LEGACY (sidebar + members.js)
    $r->get('/members', 'MemberController@index');
    $r->get('/api/members', 'MemberController@api');
    $r->get('/api/members/export', 'MemberController@export');
    $r->get('/api/stats/registrations', 'DashboardController@statsRegistrations');
    $r->post('/members/store', 'MemberController@store');
    $r->post('/members/update/{id}', 'MemberController@update');
    $r->post('/members/delete/{id}', 'MemberController@destroy');

    /* ========== MANAJEMEN ARTIKEL ========== */
    // URL baru
    $r->get('/admin/articles', 'ArticleController@manage', 'admin.articles');
    $r->get('/admin/articles/api', 'ArticleController@api');
    $r->get('/admin/articles/export', 'ArticleController@export');
    $r->post('/admin/articles', 'ArticleController@store', 'admin.articles.store');
    $r->put('/admin/articles/{id}', 'ArticleController@update', 'admin.articles.update');
    $r->delete('/admin/articles/{id}', 'ArticleController@destroy', 'admin.articles.destroy');
    $r->post('/admin/articles/bulk-delete', 'ArticleController@bulkDelete');
    $r->post('/admin/articles/bulk-status', 'ArticleController@bulkUpdateStatus');
    // URL LEGACY (sidebar + articles.js)
    $r->get('/articles', 'ArticleController@manage');
    $r->get('/api/articles', 'ArticleController@api');
    $r->post('/articles/store', 'ArticleController@store');
    $r->post('/articles/update/{id}', 'ArticleController@update');
    $r->post('/articles/delete/{id}', 'ArticleController@destroy');

    /* ========== MANAJEMEN EVENT ========== */
    // URL baru
    $r->get('/admin/events', 'EventController@index', 'admin.events');
    $r->get('/admin/events/api', 'EventController@api');
    $r->get('/admin/events/export', 'EventController@export');
    $r->post('/admin/events', 'EventController@store', 'admin.events.store');
    $r->put('/admin/events/{id}', 'EventController@update', 'admin.events.update');
    $r->delete('/admin/events/{id}', 'EventController@destroy', 'admin.events.destroy');
    $r->post('/admin/events/bulk-delete', 'EventController@bulkDelete');
    // URL LEGACY (sidebar + events.js)
    $r->get('/events', 'EventController@index');
    $r->get('/api/events', 'EventController@api');
    $r->post('/events/store', 'EventController@store');
    $r->post('/events/update/{id}', 'EventController@update');
    $r->post('/events/delete/{id}', 'EventController@destroy');

    /* ========== MANAJEMEN KONTEN ========== */
    // URL baru
    $r->get('/admin/content', 'ContentController@manage', 'admin.content');
    $r->get('/admin/content/api/{type}', 'ContentController@api');
    $r->get('/admin/content/export/{type}', 'ContentController@export');
    $r->post('/admin/content', 'ContentController@store', 'admin.content.store');
    $r->put('/admin/content/{id}', 'ContentController@update', 'admin.content.update');
    $r->delete('/admin/content/{id}', 'ContentController@destroy', 'admin.content.destroy');
    $r->post('/admin/content/bulk-delete', 'ContentController@bulkDelete');
    $r->post('/admin/content/bulk-status', 'ContentController@bulkUpdateStatus');
    $r->post('/admin/content/reorder-officers', 'ContentController@reorderOfficers');
    $r->post('/admin/content/approve-testimonial/{id}', 'ContentController@approveTestimonial');
    // URL LEGACY (sidebar + content.js)
    $r->get('/content', 'ContentController@manage');
    $r->get('/api/content/{type}', 'ContentController@api');
    $r->post('/content/store', 'ContentController@store');
    $r->post('/content/update/{id}', 'ContentController@update');
    $r->post('/content/delete/{id}', 'ContentController@destroy');

    /* ========== MANAJEMEN SENSUS ========== */
    // URL baru
    $r->get('/admin/census', 'CensusController@manage', 'admin.census');
    $r->get('/admin/census/api', 'CensusController@api');
    $r->get('/admin/census/export', 'CensusController@export');
    $r->post('/admin/census/approve/{id}', 'CensusController@approve');
    $r->delete('/admin/census/{id}', 'CensusController@destroy', 'admin.census.destroy');
    $r->post('/admin/census/bulk-approve', 'CensusController@bulkApprove');
    $r->post('/admin/census/bulk-delete', 'CensusController@bulkDelete');
    // URL LEGACY (sidebar + census.js)
    $r->get('/census', 'CensusController@manage');
    $r->get('/api/census', 'CensusController@api');
    $r->post('/census/approve/{id}', 'CensusController@approve');
    $r->post('/census/delete/{id}', 'CensusController@destroy');

    /* ========== PENGATURAN SITUS ========== */
    // URL baru
    $r->get('/admin/settings', 'SettingController@index', 'admin.settings');
    $r->post('/admin/settings/save', 'SettingController@save');
    $r->post('/admin/settings/remove-asset', 'SettingController@removeAsset');
    $r->post('/admin/settings/reset', 'SettingController@reset');
    $r->get('/admin/settings/export', 'SettingController@export');
    $r->post('/admin/settings/import', 'SettingController@import');
    $r->post('/admin/settings/preview-seo', 'SettingController@previewSeo');
    // URL LEGACY (sidebar + settings.js)
    $r->get('/settings', 'SettingController@index');
    $r->post('/settings/save', 'SettingController@save');
    $r->post('/settings/remove-asset', 'SettingController@removeAsset');

    /* ========== NOTIFIKASI ========== */
    $r->get('/admin/notifications/feed', 'NotificationController@feed');
    $r->get('/api/notifications', 'NotificationController@feed');   // LEGACY

}); // End admin group

/* ================================================================
 | 6. ERROR HANDLERS
 | ================================================================ */

$router->error(403, 'ErrorController@forbidden');
$router->error(500, 'ErrorController@serverError');
$router->error(419, 'ErrorController@csrfExpired');

/* ================================================================
 | 7. FALLBACK (404 — Route tidak ditemukan)
 | ================================================================ */

$router->fallback('ErrorController@notFound');