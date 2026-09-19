<?php
// File: app/Models/Gallery.php (FINAL - TAHAP 5.6)
declare(strict_types=1);

namespace Models;

use Core\Database;

class Gallery {
    public static function all(int $limit = 0): array {
        $sql = 'SELECT * FROM galleries ORDER BY id DESC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;
        return Database::getInstance()->query($sql)->fetchAll();
    }

    /** Semua foto untuk halaman publik /galeri dengan paginasi */
    public static function publicAll(int $page = 1, int $perPage = 12): array {
        $offset = ($page - 1) * $perPage;
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries ORDER BY event_date DESC, id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countPublic(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM galleries')->fetchColumn();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM galleries WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $d): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO galleries (title, image, event_date, location) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$d['title'], $d['image'], $d['event_date'] ?: null, $d['location'] ?: null]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE galleries SET title = ?, image = ?, event_date = ?, location = ? WHERE id = ?'
        )->execute([$d['title'], $d['image'], $d['event_date'] ?: null, $d['location'] ?: null, $id]);
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM galleries WHERE id = ?')->execute([$id]);
    }
}