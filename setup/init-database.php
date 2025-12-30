<?php
/**
 * Inicialización de base de datos
 * Compatible con Render + Railway
 */

function getDatabase() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $host = getenv('DB_HOST') ?: getenv('MYSQLHOST');
            $port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306;
            $db   = getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE');
            $user = getenv('DB_USERNAME') ?: getenv('MYSQLUSER');
            $pass = getenv('DB_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD');

            if (!$host || !$db || !$user || !$pass) {
                throw new Exception('❌ Variables de entorno MySQL incompletas');
            }

            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false
                ]
            );

        } catch (Throwable $e) {
            error_log("❌ ERROR DB: " . $e->getMessage());
            return null;
        }
    }

    return $pdo;
}
