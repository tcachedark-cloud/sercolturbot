<?php
header('Content-Type: text/plain');

$host = getenv('DB_HOST');
$db   = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: 3306;

var_dump($host, $db, $user, $port);

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "\n✅ CONECTADO A MYSQL\n";
    echo "DB: " . $pdo->query("SELECT DATABASE()")->fetchColumn();
} catch (Throwable $e) {
    echo "\n❌ ERROR PDO:\n";
    echo $e->getMessage();
}
