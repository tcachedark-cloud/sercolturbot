<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config/database.php';

try {
    echo "🔄 Limpiando base de datos...\n\n";
    
    // Obtener todas las tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "✅ No hay tablas para limpiar\n";
        exit;
    }
    
    // Deshabilitar restricciones
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Eliminar cada tabla
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "✓ Eliminada tabla: $table\n";
    }
    
    // Reabilitar restricciones
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "\n✅ Base de datos limpiada correctamente\n";
    echo "Ahora puedes importar el backup sin errores\n";
    
} catch (Exception $e) {
    http_response_code(500);
    die("❌ Error al limpiar: " . $e->getMessage());
}
?>
