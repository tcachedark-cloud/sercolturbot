<?php
require_once __DIR__ . '/database.php';

header('Content-Type: text/plain');

echo "DB_HOST: "; var_dump(getenv('DB_HOST'));
echo "DB_PORT: "; var_dump(getenv('DB_PORT'));
echo "DB_DATABASE: "; var_dump(getenv('DB_DATABASE'));
echo "DB_USERNAME: "; var_dump(getenv('DB_USERNAME'));
echo "DB_PASSWORD: "; echo getenv('DB_PASSWORD') ? "SET\n" : "MISSING\n";

$pdo = getDatabase();

if ($pdo) {
    echo "\n✅ CONECTADO A MYSQL\n";
    echo "Base de datos: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "\n";
} else {
    echo "\n❌ NO CONECTA A MYSQL\n";
}
