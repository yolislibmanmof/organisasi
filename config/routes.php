<?php
/**
 * ================================================================
 *  DAFTAR RUTE APLIKASI (URL → Controller@Method)
 * ================================================================
 *  Format: $router->{method}('/url', 'NamaController@namaMethod');
 *  Parameter dinamis: $router->get('/member/{id}', 'MemberController@show');
 */
declare(strict_types=1);

if (!defined('APP_EXEC')) {
    die('Akses tidak diizinkan.');
}

/* ----------------------------------------------------------------
 | RUTE PUBLIK — dapat diakses tanpa login
 | ---------------------------------------------------------------- */
$router->get('/', 'HomeController@index');

/* ----------------------------------------------------------------
 | RUTE AUTENTIKASI — login & logout
 | ---------------------------------------------------------------- */
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@processLogin');
$router->get('/logout', 'AuthController@logout');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — wajib login (middleware dijalankan
 | di dalam masing-masing controller melalui Auth::handle())
 | ---------------------------------------------------------------- */
$router->get('/dashboard', 'DashboardController@index');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — MANAJEMEN ANGGOTA (Tahap 3 + 4.3)
 | ---------------------------------------------------------------- */
$router->get('/members', 'MemberController@index');
$router->get('/api/members', 'MemberController@api');
$router->get('/api/members/export', 'MemberController@export');  // TAHAP 4.3
$router->get('/api/stats/registrations', 'DashboardController@statsRegistrations');
$router->post('/members/store', 'MemberController@store');
$router->post('/members/update/{id}', 'MemberController@update');
$router->post('/members/delete/{id}', 'MemberController@destroy');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — MODUL EVENT (Tahap 4.1)
 | ---------------------------------------------------------------- */
$router->get('/events', 'EventController@index');
$router->get('/api/events', 'EventController@api');
$router->post('/events/store', 'EventController@store');
$router->post('/events/update/{id}', 'EventController@update');
$router->post('/events/delete/{id}', 'EventController@destroy');

/* ----------------------------------------------------------------
 | RUTE TERLINDUNG — PROFIL & PENGATURAN (Tahap 4.2)
 | ---------------------------------------------------------------- */
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update', 'ProfileController@update');
$router->post('/profile/password', 'ProfileController@updatePassword');