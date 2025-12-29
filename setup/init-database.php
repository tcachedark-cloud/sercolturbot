<?php
/**
 * Script de inicialización de base de datos para Railway
 * Se ejecuta automáticamente en el primer despliegue
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Detectar entorno
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT_NAME']) || isset($_ENV['MYSQL_HOST']);

if ($isRailway) {
    echo "🚀 Inicializando base de datos en Railway...\n\n";
    
    $host = $_ENV['MYSQL_HOST'] ?? 'localhost';
    $user = $_ENV['MYSQL_USER'] ?? 'root';
    $password = $_ENV['MYSQL_PASSWORD'] ?? '';
    $database = $_ENV['MYSQL_DATABASE'] ?? 'railway';
    $port = $_ENV['MYSQL_PORT'] ?? 3306;
    
    echo "[*] Host: $host:$port\n";
    echo "[*] Usuario: $user\n";
    echo "[*] Base de datos: $database\n\n";
    
} else {
    echo "⚙️  Inicializando base de datos en desarrollo local...\n\n";
    
    $host = 'localhost';
    $user = 'root';
    $password = 'C121672@c';
    $database = 'sercolturbot';
    $port = 3306;
}

// Conectar a MySQL sin especificar BD primero
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conectado a MySQL exitosamente\n";
} catch (PDOException $e) {
    die("❌ Error al conectar: " . $e->getMessage() . "\n");
}

// Crear base de datos si no existe
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de datos '$database' lista\n\n";
} catch (PDOException $e) {
    die("❌ Error al crear BD: " . $e->getMessage() . "\n");
}

// Seleccionar base de datos
try {
    $pdo->exec("USE `$database`");
    echo "✅ Usando base de datos '$database'\n\n";
} catch (PDOException $e) {
    die("❌ Error al seleccionar BD: " . $e->getMessage() . "\n");
}

// Importar esquema SQL
$sqlFile = __DIR__ . '/database.sql';

if (!file_exists($sqlFile)) {
    echo "⚠️  Archivo $sqlFile no encontrado\n";
    echo "💡 Asegúrate de que existe setup/database.sql\n";
    exit(1);
}

echo "[*] Leyendo archivo: $sqlFile\n\n";

$sql = file_get_contents($sqlFile);

// Dividir por puntos y coma y ejecutar cada statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

$totalStatements = count($statements);
$executedStatements = 0;
$errors = [];

foreach ($statements as $index => $statement) {
    if (empty($statement)) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $executedStatements++;
        
        // Mostrar progreso
        $progress = round(($executedStatements / $totalStatements) * 100);
        $preview = substr(preg_replace('/\s+/', ' ', $statement), 0, 60);
        echo "[✓] $executedStatements/$totalStatements ($progress%) - $preview...\n";
        
    } catch (PDOException $e) {
        $errors[] = [
            'statement' => $statement,
            'error' => $e->getMessage()
        ];
        echo "[✗] Error en statement $executedStatements: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";

if (empty($errors)) {
    echo "✅ ¡Base de datos inicializada correctamente!\n";
    echo "📊 Estadísticas:\n";
    echo "   - Total de statements: $totalStatements\n";
    echo "   - Ejecutados: $executedStatements\n";
    echo "   - Errores: 0\n";
} else {
    echo "⚠️  Inicialización completada con ${\count($errors)} errores\n";
    echo "Detalles de errores:\n";
    foreach ($errors as $error) {
        echo "  - " . $error['error'] . "\n";
    }
}

echo str_repeat("=", 70) . "\n\n";

// Verificar tablas creadas
try {
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tablas creadas:\n";
    foreach ($tables as $table) {
        echo "   ✓ $table\n";
    }
    echo "\n";
} catch (PDOException $e) {
    echo "⚠️  No se pudo listar tablas: " . $e->getMessage() . "\n";
}

echo "🎉 Listo para usar!\n";
?>
