<?php
require_once __DIR__ . '/../setup/init-database.php';

$pdo = getDatabase();

header('Content-Type: text/plain');

if ($pdo) {
    echo "✅ CONECTADO A MYSQL\n";
    echo "Base de datos: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "\n";
} else {
    echo "❌ NO CONECTA\n";
    echo "DATABASE_URL = ";
    var_dump(getenv('DATABASE_URL'));
}
