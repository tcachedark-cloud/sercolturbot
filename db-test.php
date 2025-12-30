<?php
require_once __DIR__ . '/setup/init-database.php';

$pdo = getDatabase();

echo "<pre>";
if ($pdo) {
    echo "✅ CONECTADO A MYSQL\n";
    echo "Base de datos: " . $pdo->query("SELECT DATABASE()")->fetchColumn();
} else {
    echo "❌ NO CONECTA\n";
    echo "DATABASE_URL = ";
    var_dump(getenv('DATABASE_URL'));
}
echo "</pre>";
