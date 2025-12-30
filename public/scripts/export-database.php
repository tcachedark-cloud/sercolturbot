<?php
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');

require_once __DIR__ . '/../config/database.php';

try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "-- Exportación de base de datos\n";
    echo "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Base de datos: " . getenv('DB_NAME') . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Obtener estructura
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        echo $createTable['Create Table'] . ";\n\n";
        
        // Obtener datos
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            
            foreach ($rows as $row) {
                $values = array_map(function($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    if (is_numeric($v) && !preg_match('/^0/', $v)) return $v;
                    return $pdo->quote($v);
                }, array_values($row));
                
                echo "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n";
            }
            echo "\n";
        }
    }
    
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    
} catch (Exception $e) {
    http_response_code(500);
    die("❌ Error al exportar: " . $e->getMessage());
}
?>
