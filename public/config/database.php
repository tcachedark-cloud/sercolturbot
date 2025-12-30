<?php

// Soporta múltiples formatos de variables de entorno
// Railway usa DATABASE_URL, otras plataformas usan variables individuales
$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: getenv('RAILWAY_DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: getenv('RAILWAY_DB_PORT') ?: 3306;
$db   = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: getenv('RAILWAY_DB_NAME') ?: 'railway';
$user = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: getenv('RAILWAY_DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: getenv('RAILWAY_DB_PASSWORD') ?: '';

// Si DATABASE_URL está disponible (formato Railway), parsear URL
$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
    $parsed = parse_url($databaseUrl);
    $host = $parsed['host'] ?? $host;
    $port = $parsed['port'] ?? $port;
    $user = $parsed['user'] ?? $user;
    $pass = $parsed['pass'] ?? $pass;
    $db = ltrim($parsed['path'] ?? '', '/') ?: $db;
}

try {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("❌ Error DB: " . $e->getMessage());
}
