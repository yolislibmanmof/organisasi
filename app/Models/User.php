<?php
// File: app/Models/User.php
declare(strict_types=1);

namespace Models;

use Core\Database;

class User {
    public static function findByLogin(string $login): ?array {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM users WHERE username = :u OR email = :em LIMIT 1'
        );
        $stmt->execute([':u' => $login, ':em' => $login]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findById(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countAll(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function countActive(): int {
        return (int) Database::getInstance()->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    }

    public static function emailExistsExcept(string $email, int $exceptId): bool {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM users WHERE email = :e AND id <> :id');
        $stmt->execute([':e' => $email, ':id' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function updateEmail(int $id, string $email): void {
        Database::getInstance()->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$email, $id]);
    }

    public static function updatePassword(int $id, string $hash): void {
        Database::getInstance()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $id]);
    }

    public static function verify(int $id, string $password): bool {
        $u = self::findById($id);
        return $u !== null && password_verify($password, $u['password']);
    }
}