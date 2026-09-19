<?php
// File: app/Controllers/HomeController.php (FINAL - TAHAP 5.3)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Article;
use Models\Event;
use Models\Gallery;
use Models\Member;
use Models\Officer;
use Models\Testimonial;

class HomeController {
    public function index(): void {
        $status = Event::countByStatus();

        View::render('pages/landing', [
            'title'        => 'Beranda',
            'loggedIn'     => (bool) Session::get('user'),
            'stats'        => [
                'members'  => Member::countAll(),
                'events'   => array_sum($status),
                'upcoming' => $status['upcoming'],
                'ongoing'  => $status['ongoing'],
            ],
            'events'       => Event::upcoming(3),
            'articles'     => Article::latest(3),
            'officers'     => Officer::all(),
            'testimonials' => Testimonial::all(),
            'galleries'    => Gallery::all(9),
        ], 'layouts/public');
    }
}