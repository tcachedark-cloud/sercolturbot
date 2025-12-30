<?php
/**
 * Conexión MySQL - Render FREE + Railway
 */

function getDatabase() {
    static $pdo = null;
    if ($pdo) return $pdo;

    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: 3306;
    $db   = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');

    if (!$host || !$db || !$user || !$pass) {
        error_log('❌ MySQL ENV missing');
        return null;
    }

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        return $pdo;
    } catch (Throwable $e) {
        error_log('DB ERROR: '.$e->getMessage());
        return null;
    }
}
