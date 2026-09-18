<?php
// File: app/Controllers/HomeController.php (FINAL - TAHAP 5.1)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Event;
use Models\Member;

class HomeController {
    /** Beranda publik: landing page dengan data dinamis */
    public function index(): void {
        $status = Event::countByStatus();

        View::render('pages/landing', [
            'title'    => 'Beranda',
            'loggedIn' => (bool) Session::get('user'),
            'stats'    => [
                'members'  => Member::countAll(),
                'events'   => array_sum($status),
                'upcoming' => $status['upcoming'],
                'ongoing'  => $status['ongoing'],
            ],
            'events'   => Event::upcoming(3),
        ], 'layouts/public');
    }
}