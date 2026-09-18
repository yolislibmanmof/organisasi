<?php
// File: app/Models/Member.php (FINAL - TAHAP 4.3)
declare(strict_types=1);

namespace Models;

use Core\Database;

class Member {
    public static function findByUserId(int $userId): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM members WHERE user_id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countAll(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM members')->fetchColumn();
    }

    /** Pencarian + paginasi untuk API live-search */
    public static function search(string $keyword = '', int $page = 1, int $perPage = 8): array {
        $like   = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;

        $sql = 'SELECT m.id, m.user_id, m.full_name, m.phone, m.address, m.join_date,
                       u.email, u.username, u.status
                FROM members m
                JOIN users u ON u.id = m.user_id
                WHERE (m.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3)
                ORDER BY m.id DESC
                LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return $stmt->fetchAll();
    }

    public static function countSearch(string $keyword = ''): int {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM members m JOIN users u ON u.id = m.user_id
             WHERE (m.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3)'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return (int) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare(
            'SELECT m.*, u.email, u.username FROM members m
             JOIN users u ON u.id = m.user_id WHERE m.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function usernameExists(string $username): bool {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM users WHERE username = :u');
        $stmt->execute([':u' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Membuat username otomatis dari nama, mis. "Budi Santoso" → budi.santoso */
    public static function generateUsername(string $fullName): string {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $base  = strtolower(($parts[0] ?? 'anggota') . '.' . ($parts[1] ?? ''));
        $base  = preg_replace('/[^a-z0-9.]/', '', rtrim($base, '.')) ?: 'anggota';

        $candidate = $base;
        $i = 1;
        while (self::usernameExists($candidate)) {
            $candidate = $base . $i++;
        }
        return $candidate;
    }

    /** Transaksi atomik: buat akun pengguna + profil anggota sekaligus */
    public static function createWithUser(array $d): int {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $username = self::generateUsername($d['full_name']);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $d['email'], password_hash($d['password'], PASSWORD_DEFAULT), 'member']);
            $userId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO members (user_id, full_name, phone, address, join_date) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $d['full_name'], $d['phone'], $d['address'], $d['join_date']]);
            $memberId = (int) $pdo->lastInsertId();

            $pdo->commit();
            return $memberId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateMember(int $id, array $d): void {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE members SET full_name = ?, phone = ?, address = ?, join_date = ? WHERE id = ?')
                ->execute([$d['full_name'], $d['phone'], $d['address'], $d['join_date'], $id]);
            $pdo->prepare('UPDATE users SET email = ? WHERE id = (SELECT user_id FROM members WHERE id = ?)')
                ->execute([$d['email'], $id]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function deleteWithUser(int $id): void {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT user_id FROM members WHERE id = ?');
            $stmt->execute([$id]);
            $userId = (int) $stmt->fetchColumn();

            $pdo->prepare('DELETE FROM members WHERE id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Statistik pendaftaran per bulan untuk grafik ApexCharts */
    public static function registrationsPerMonth(int $months = 6): array {
        $start = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));
        $stmt  = Database::getInstance()->prepare(
            "SELECT DATE_FORMAT(join_date, '%Y-%m') AS ym, COUNT(*) AS total
             FROM members WHERE join_date >= :start GROUP BY ym ORDER BY ym"
        );
        $stmt->execute([':start' => $start]);

        $map = [];
        foreach ($stmt->fetchAll() as $r) {
            $map[$r['ym']] = (int) $r['total'];
        }

        $labels = [];
        $totals = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym       = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime($ym . '-01'));
            $totals[] = $map[$ym] ?? 0;
        }
        return ['labels' => $labels, 'totals' => $totals];
    }

    /* ============================================================
       METHOD UNTUK MODUL PROFIL (TAHAP 4.2)
       ============================================================ */
    public static function updateProfile(int $userId, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE members SET full_name = ?, phone = ?, address = ? WHERE user_id = ?'
        )->execute([$d['full_name'], $d['phone'], $d['address'], $userId]);
    }

    public static function updatePhoto(int $userId, string $photo): void {
        Database::getInstance()->prepare('UPDATE members SET photo = ? WHERE user_id = ?')
            ->execute([$photo, $userId]);
    }

    /* ============================================================
       METHOD UNTUK EKSPOR LAPORAN PDF (TAHAP 4.3)
       ============================================================ */
    public static function allForExport(): array {
        return Database::getInstance()->query(
            'SELECT m.id, m.full_name, m.phone, m.join_date, u.username, u.email, u.status
             FROM members m JOIN users u ON u.id = m.user_id
             ORDER BY m.full_name ASC'
        )->fetchAll();
    }
}