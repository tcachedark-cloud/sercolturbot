
<?php

// Prioriza variables estándar; si no existen, usa las antiguas
$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306;

// Nombre de base de datos: DB_DATABASE (convención) o DB_NAME (antiguo)
$db   = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: 'railway';

// Usuario y contraseña: DB_USERNAME/DB_PASSWORD (convención) o DB_USER/DB_PASS (antiguo)
$user = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';

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
    // Mensaje claro para diagnóstico (no exponer credenciales)
    http_response_code(500);
    die("❌ Error DB: " . $e->getMessage());
}
