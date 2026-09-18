<?php
// File: database/seed.php
// Jalankan SEKALI melalui terminal:  php database/seed.php
declare(strict_types=1);

use Core\Database;

define('APP_EXEC', true);
require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/app/Core/Database.php';

$pdo = Database::getInstance();

if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
    echo "› Seeder dilewati: tabel users sudah memiliki data.\n";
    exit(0);
}

$stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->execute(['admin', 'admin@organisasi.id', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
$stmt->execute(['budi.santoso', 'budi@organisasi.id', password_hash('member123', PASSWORD_DEFAULT), 'member']);

$budiId = (int) $pdo->query("SELECT id FROM users WHERE username = 'budi.santoso'")->fetchColumn();
$pdo->prepare('INSERT INTO members (user_id, full_name, phone, join_date) VALUES (?, ?, ?, ?)')
    ->execute([$budiId, 'Budi Santoso', '081234567890', date('Y-m-d')]);

echo "› Seeder berhasil!\n";
echo "  Akun Admin  : admin / admin123\n";
echo "  Akun Member : budi.santoso / member123\n";