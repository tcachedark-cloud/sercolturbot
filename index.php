<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>SERCOLTURBOT</title>";
echo "<style>body{font-family:Arial;background:#1a1a2e;color:#fff;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}";
echo ".container{text-align:center;padding:40px}.logo{font-size:80px}.status{background:#12121c;padding:20px;border-radius:10px;margin:20px 0}";
echo ".ok{color:#10b981}.error{color:#ef4444}.btn{display:inline-block;padding:12px 30px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;margin:10px}</style></head><body>";
echo "<div class='container'>";
echo "<div class='logo'>🚌</div>";
echo "<h1>SERCOLTURBOT</h1>";
echo "<p>Sistema de Reservas de Tours - Medellín</p>";

echo "<div class='status'>";
echo "<p class='ok'>✅ PHP funcionando correctamente</p>";

// Verificar variables MySQL de Railway
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'no configurado';
$db = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'no configurado';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'no configurado';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';

echo "<p>📊 MySQL Host: " . htmlspecialchars($host) . "</p>";
echo "<p>📊 MySQL Database: " . htmlspecialchars($db) . "</p>";
echo "<p>📊 MySQL Port: " . htmlspecialchars($port) . "</p>";

// Intentar conexión
if ($host !== 'no configurado' && $db !== 'no configurado') {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<p class='ok'>✅ Base de datos conectada exitosamente</p>";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        echo "<p>📋 Tablas encontradas: " . count($tables) . "</p>";
        
        if (count($tables) > 0) {
            echo "<p style='font-size:12px;color:#888'>Tablas: ";
            foreach ($tables as $t) {
                echo array_values($t)[0] . ", ";
            }
            echo "</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='error'>⚠️ Variables MySQL no configuradas</p>";
    echo "<p style='font-size:12px'>Verifica que MySQL esté conectado en Railway</p>";
}

echo "</div>";

echo "<div style='margin-top:20px'>";
echo "<a href='public/dashboard.php' class='btn'>📊 Dashboard</a>";
echo "<a href='public/whatsapp-api.php' class='btn'>🤖 Bot Status</a>";
echo "</div>";

echo "<p style='margin-top:30px;color:#555;font-size:12px'>SERCOLTURBOT v1.0 - Railway Production</p>";
echo "</div></body></html>";
?>
