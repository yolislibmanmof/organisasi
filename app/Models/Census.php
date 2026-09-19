<?php
// File: app/Models/Census.php
declare(strict_types=1);

namespace Models;

use Core\Database;

class Census {
    public static function store(array $d): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO census (full_name, email, phone, address, status, graduation_year, message)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['full_name'], $d['email'], $d['phone'], $d['address'],
            $d['status'], $d['graduation_year'], $d['message'],
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }
    public static function all(): array {
        return Database::getInstance()->query('SELECT * FROM census ORDER BY processed ASC, id DESC')->fetchAll();
    }
    public static function countNew(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM census WHERE processed = 0')->fetchColumn();
    }
    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM census WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
    public static function emailPending(string $email): bool {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM census WHERE email = :e AND processed = 0');
        $stmt->execute([':e' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }
    public static function markProcessed(int $id): void {
        Database::getInstance()->prepare('UPDATE census SET processed = 1 WHERE id = ?')->execute([$id]);
    }
    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM census WHERE id = ?')->execute([$id]);
    }
}