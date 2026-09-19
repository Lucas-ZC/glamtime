<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$envFile = __DIR__ . '/.env';
$config = file_exists($envFile) ? parse_ini_file($envFile) : [];
$host   = $config['DB_HOST'] ?? 'localhost';
$port   = $config['DB_PORT'] ?? '3306';
$dbname = $config['DB_NAME'] ?? 'glamtime';
$user   = $config['DB_USER'] ?? 'root';
$pass   = $config['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Falha ao conectar à base de dados. Contate o administrador.");
}