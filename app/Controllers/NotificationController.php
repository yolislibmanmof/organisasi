<?php
// File: app/Controllers/NotificationController.php (TAHAP 5.7)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Census;
use Models\Member;
use Models\Article;
use Models\Event;

class NotificationController {
    public function feed(): void {
        Auth::handle();
        AdminOnly::handle();

        $items = [];

        // Sensus menunggu verifikasi
        $newCensus = Census::countNew();
        if ($newCensus > 0) {
            $items[] = [
                'icon'  => 'ph-clipboard-text',
                'grad'  => 'grad-4',
                'title' => $newCensus . ' entri sensus menunggu verifikasi',
                'sub'   => 'Tinjau di menu Sensus Anggota',
                'link'  => 'census',
                'time'  => 'Baru saja',
            ];
        }

        // 5 aktivitas terbaru (gabungan)
        $members = Member::recent(2);
        foreach ($members as $m) {
            $items[] = [
                'icon'  => 'ph-user-plus',
                'grad'  => 'grad-1',
                'title' => 'Anggota baru bergabung',
                'sub'   => $m['full_name'],
                'link'  => 'members',
                'time'  => date('H:i', strtotime($m['join_date'] ?? 'now')),
            ];
        }

        $articles = Article::recent(2);
        foreach ($articles as $a) {
            $items[] = [
                'icon'  => 'ph-newspaper',
                'grad'  => 'grad-2',
                'title' => 'Artikel diterbitkan',
                'sub'   => $a['title'],
                'link'  => 'articles',
                'time'  => date('H:i', strtotime($a['created_at'])),
            ];
        }

        $events = Event::recent(2);
        foreach ($events as $e) {
            $items[] = [
                'icon'  => 'ph-calendar-plus',
                'grad'  => 'grad-4',
                'title' => 'Event dijadwalkan',
                'sub'   => $e['title'],
                'link'  => 'events',
                'time'  => date('d M', strtotime($e['event_date'])),
            ];
        }

        json(['items' => array_slice($items, 0, 6), 'unread' => $newCensus]);
    }
}