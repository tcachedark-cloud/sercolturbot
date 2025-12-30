<?php
header('Content-Type: text/plain; charset=utf-8');

// Protección básica
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['sql_file'])) {
    die("Usa POST con archivo 'sql_file'");
}

require_once __DIR__ . '/../config/database.php';

$file = $_FILES['sql_file']['tmp_name'];
$skipErrors = isset($_GET['skip_errors']);

try {
    $sql = file_get_contents($file);
    
    // Remover comentarios /* */ y -- 
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sql = preg_replace('/^--.*$/m', '', $sql);
    
    // Deshabilitar restricciones de clave foránea
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("SET UNIQUE_CHECKS=0");
    
    // Separar sentencias SQL
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) { return !empty($s) && strlen($s) > 3; }
    );
    
    $count = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        // Saltar líneas que no parecen SQL válido
        if (preg_match('/^(Win|Apache|Server|Port)/', $statement)) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            if (!$skipErrors) {
                // Reabilitar restricciones antes de fallar
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
                $pdo->exec("SET UNIQUE_CHECKS=1");
                throw $e;
            }
            echo "⚠️ Error ignorado: " . substr($e->getMessage(), 0, 100) . "\n";
        }
    }
    
    // Reabilitar restricciones
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    $pdo->exec("SET UNIQUE_CHECKS=1");
    
    echo "✅ Base de datos importada correctamente\n";
    echo "✓ Sentencias ejecutadas: $count\n";
    if ($errors > 0) {
        echo "⚠️ Errores ignorados: $errors\n";
    }
    
} catch (Exception $e) {
    http_response_code(500);
    die("❌ Error al importar: " . $e->getMessage());
}
?>
