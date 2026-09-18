<?php
// File: app/Controllers/DashboardController.php (FINAL - TERINTEGRASI TAHAP 4)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\Auth;
use Models\Member;
use Models\User;
use Models\Event;

class DashboardController {
    public function index(): void {
        Auth::handle();
        View::render('pages/dashboard', [
            'title'          => 'Dashboard',
            'user'           => Session::get('user'),
            'totalMembers'   => Member::countAll(),
            'totalUsers'     => User::countAll(),
            'activeUsers'    => User::countActive(),
            'eventThisMonth' => Event::countThisMonth(),
        ], 'layouts/app');
    }

    /** API JSON untuk grafik ApexCharts */
    public function statsRegistrations(): void {
        Auth::handle();
        json(Member::registrationsPerMonth(6));
    }
}