<?php
header('Content-Type: text/plain; charset=utf-8');

// Cargar configuración de BD
require_once __DIR__ . '/../public/config/database.php';

// Nombre del archivo de exportación
$timestamp = date('Y-m-d_H-i-s');
$filename = "backup_{$timestamp}.sql";

try {
    // Obtener todas las tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $sql = "-- Exportación de base de datos\n";
    $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        // Estructura de la tabla
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        $sql .= "\n" . $createTable['Create Table'] . ";\n\n";
        
        // Datos de la tabla
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $columnList = implode('`, `', $columns);
            
            foreach ($rows as $row) {
                $values = array_map(function($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote($v);
                }, $row);
                
                $sql .= "INSERT INTO `$table` (`$columnList`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
    }
    
    // Descargar archivo
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $sql;
    
} catch (Exception $e) {
    http_response_code(500);
    die("❌ Error al exportar: " . $e->getMessage());
}
