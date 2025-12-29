<?php
/**
 * TEST DE CONEXIÓN A BASE DE DATOS
 * Sube este archivo y ábrelo en el navegador
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test de Conexión BD</h1>";

// Probar diferentes configuraciones
$configs = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => 'sercolturbot'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'db' => 'sercolturbot'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'db' => 'sercolturbot'],
];

foreach ($configs as $i => $cfg) {
    echo "<h3>Intento " . ($i+1) . ": {$cfg['host']} / {$cfg['user']}</h3>";
    
    try {
        $pdo = new PDO(
            "mysql:host={$cfg['host']};dbname={$cfg['db']};charset=utf8mb4",
            $cfg['user'],
            $cfg['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p style='color:green'>✅ CONEXIÓN EXITOSA!</p>";
        
        // Contar tours
        $tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE activo=1")->fetchColumn();
        echo "<p>Tours activos: <strong>$tours</strong></p>";
        
        // Mostrar tours
        $stmt = $pdo->query("SELECT id, nombre, precio FROM tours WHERE activo=1 ORDER BY id LIMIT 5");
        echo "<ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>ID {$row['id']}: {$row['nombre']} - \${$row['precio']}</li>";
        }
        echo "</ul>";
        
        // Verificar Nápoles
        $napoles = $pdo->query("SELECT id, nombre FROM tours WHERE activo=1 AND (nombre LIKE '%nápoles%' OR nombre LIKE '%napoles%')")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Tours de Nápoles encontrados: <strong>" . count($napoles) . "</strong></p>";
        foreach ($napoles as $n) {
            echo "<p>- ID {$n['id']}: {$n['nombre']}</p>";
        }
        
        echo "<hr><p style='color:green;font-size:18px'>USA ESTA CONFIGURACIÓN:</p>";
        echo "<pre>";
        echo "Host: {$cfg['host']}\n";
        echo "User: {$cfg['user']}\n";
        echo "Pass: {$cfg['pass']}\n";
        echo "DB: {$cfg['db']}\n";
        echo "</pre>";
        
        break; // Salir si funciona
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

// También verificar si existe el archivo de config
echo "<hr><h2>📁 Verificar archivo de configuración</h2>";
$configPath = __DIR__ . '/../config/database.php';
if (file_exists($configPath)) {
    echo "<p style='color:green'>✅ Existe: $configPath</p>";
    echo "<pre>" . htmlspecialchars(file_get_contents($configPath)) . "</pre>";
} else {
    echo "<p style='color:orange'>⚠️ No existe: $configPath</p>";
}
?>