<?php
/**
 * Conexión MySQL REAL para Render + Railway
 * Usa DATABASE_URL (método correcto)
 */

function getDatabase() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $databaseUrl = getenv('DATABASE_URL');

        if (!$databaseUrl) {
            throw new Exception('DATABASE_URL no existe en Render');
        }

        // mysql://user:pass@host:port/dbname
        $parts = parse_url($databaseUrl);

        if ($parts === false) {
            throw new Exception('DATABASE_URL inválida');
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? 3306;
        $user = $parts['user'];
        $pass = $parts['pass'] ?? '';
        $db   = ltrim($parts['path'], '/');

        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        return $pdo;

    } catch (Throwable $e) {
        error_log('❌ DB ERROR: ' . $e->getMessage());
        return null;
    }
}
