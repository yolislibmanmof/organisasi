<?php
// File: app/Controllers/DashboardController.php (FINAL - TAHAP 5.5)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\Auth;
use Models\Article;
use Models\Event;
use Models\Member;
use Models\User;

class DashboardController {
    public function index(): void {
        Auth::handle();

        /* ========== Data statistik ========== */
        $totalMembers   = Member::countAll();
        $totalUsers     = User::countAll();
        $activeUsers    = User::countActive();
        $eventThisMonth = Event::countThisMonth();

        /* ========== Feed aktivitas gabungan ========== */
        $feed = [];

        // Anggota terbaru (berdasarkan join_date)
        foreach (Member::recent(3) as $m) {
            $feed[] = [
                'type'  => 'member',
                'icon'  => 'ph-user-plus',
                'grad'  => 'grad-1',
                'title' => 'Anggota baru bergabung',
                'sub'   => $m['full_name'] . ($m['email'] ? ' · ' . $m['email'] : ''),
                'time'  => ($m['join_date'] ?? date('Y-m-d')) . ' 00:00:00',
            ];
        }

        // Event terbaru (berdasarkan event_date)
        foreach (Event::recent(3) as $e) {
            $loc = !empty($e['location']) ? ' · ' . $e['location'] : '';
            $feed[] = [
                'type'  => 'event',
                'icon'  => 'ph-calendar-plus',
                'grad'  => 'grad-4',
                'title' => 'Event dijadwalkan',
                'sub'   => $e['title'] . $loc,
                'time'  => ($e['event_date'] ?? date('Y-m-d')) . ' 00:00:00',
            ];
        }

        // Artikel terbaru (berdasarkan created_at)
        foreach (Article::recent(3) as $a) {
            $author = !empty($a['author_name']) ? ' oleh @' . $a['author_name'] : '';
            $feed[] = [
                'type'  => 'article',
                'icon'  => 'ph-newspaper',
                'grad'  => 'grad-2',
                'title' => 'Artikel diterbitkan',
                'sub'   => $a['title'] . $author,
                'time'  => $a['created_at'],
            ];
        }

        // Urutkan berdasarkan waktu terbaru
        usort($feed, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
        $feed = array_slice($feed, 0, 5);

        /* ========== Render view ========== */
        View::render('pages/dashboard', [
            'title'          => 'Dashboard',
            'user'           => Session::get('user'),
            'totalMembers'   => $totalMembers,
            'totalUsers'     => $totalUsers,
            'activeUsers'    => $activeUsers,
            'eventThisMonth' => $eventThisMonth,
            'feed'           => $feed,
            'newThisMonth'   => Member::countThisMonth(),
        ], 'layouts/app');
    }

    public function statsRegistrations(): void {
        Auth::handle();
        json(Member::registrationsPerMonth(6));
    }
}