<?php
// File: app/Models/Event.php (FINAL - TAHAP 5.7)
declare(strict_types=1);

namespace Models;

use Core\Database;

class Event {
    private static function decorate(array $row): array {
        $today = date('Y-m-d');
        $row['status'] = $row['event_date'] > $today ? 'upcoming'
            : ($row['event_date'] === $today ? 'ongoing' : 'done');
        return $row;
    }

    /** Daftar event untuk halaman publik: aktif dulu, lalu arsip */
    public static function publicList(): array {
        $rows = Database::getInstance()->query(
            'SELECT e.*, u.username AS creator_name
             FROM events e LEFT JOIN users u ON u.id = e.created_by
             ORDER BY e.event_date DESC'
        )->fetchAll();
        $rows = array_map([self::class, 'decorate'], $rows);

        $active = array_values(array_filter($rows, fn($r) => $r['status'] !== 'done'));
        $done   = array_values(array_filter($rows, fn($r) => $r['status'] === 'done'));
        usort($active, fn($a, $b) => strcmp($a['event_date'], $b['event_date']));

        return ['active' => $active, 'done' => array_slice($done, 0, 6)];
    }

    public static function search(string $keyword = '', int $page = 1, int $perPage = 6): array {
        $like   = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT e.*, u.username AS creator_name
                FROM events e LEFT JOIN users u ON u.id = e.created_by
                WHERE (e.title LIKE :q1 OR e.location LIKE :q2)
                ORDER BY e.event_date DESC
                LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return array_map([self::class, 'decorate'], $stmt->fetchAll());
    }

    public static function countSearch(string $keyword = ''): int {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM events e WHERE (e.title LIKE :q1 OR e.location LIKE :q2)'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return (int) $stmt->fetchColumn();
    }

    public static function countByStatus(): array {
        $today = date('Y-m-d');
        $pdo   = Database::getInstance();
        $up = $pdo->prepare('SELECT COUNT(*) FROM events WHERE event_date > :t');
        $up->execute([':t' => $today]);
        $on = $pdo->prepare('SELECT COUNT(*) FROM events WHERE event_date = :t');
        $on->execute([':t' => $today]);
        $dn = $pdo->prepare('SELECT COUNT(*) FROM events WHERE event_date < :t');
        $dn->execute([':t' => $today]);
        return [
            'upcoming' => (int) $up->fetchColumn(),
            'ongoing'  => (int) $on->fetchColumn(),
            'done'     => (int) $dn->fetchColumn(),
        ];
    }

    public static function countThisMonth(): int {
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM events WHERE DATE_FORMAT(event_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public static function upcoming(int $limit = 3): array {
        $stmt = Database::getInstance()->prepare(
            'SELECT e.*, u.username AS creator_name
             FROM events e LEFT JOIN users u ON u.id = e.created_by
             WHERE e.event_date >= :today
             ORDER BY e.event_date ASC, e.event_time ASC
             LIMIT ' . (int) $limit
        );
        $stmt->execute([':today' => date('Y-m-d')]);
        return array_map([self::class, 'decorate'], $stmt->fetchAll());
    }

    public static function recent(int $limit = 3): array {
        $stmt = Database::getInstance()->prepare(
            'SELECT title, event_date, location
             FROM events
             ORDER BY id DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare(
            'SELECT e.*, u.username AS creator_name FROM events e
             LEFT JOIN users u ON u.id = e.created_by WHERE e.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    public static function create(array $d, int $creatorId): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO events (title, description, location, event_date, event_time, created_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['title'], $d['description'], $d['location'],
            $d['event_date'], $d['event_time'] !== '' ? $d['event_time'] : null, $creatorId,
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function updateMember(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, event_time = ? WHERE id = ?'
        )->execute([
            $d['title'], $d['description'], $d['location'],
            $d['event_date'], $d['event_time'] !== '' ? $d['event_time'] : null, $id,
        ]);
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    }
}