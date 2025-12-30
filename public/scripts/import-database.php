<?php
header('Content-Type: text/plain; charset=utf-8');

// Protección básica
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['sql_file'])) {
    die("Usa POST con archivo 'sql_file'");
}

require_once __DIR__ . '/../config/database.php';

$file = $_FILES['sql_file']['tmp_name'];

try {
    $sql = file_get_contents($file);
    
    // Separar sentencias SQL
    $statements = array_filter(
        array_map('trim', preg_split('/;(?=\s|$)/', $sql)),
        function($s) { return !empty($s) && !str_starts_with($s, '--'); }
    );
    
    $count = 0;
    foreach ($statements as $statement) {
        $pdo->exec($statement . ";");
        $count++;
    }
    
    echo "✅ Base de datos importada correctamente\n";
    echo "Sentencias ejecutadas: $count\n";
    
} catch (Exception $e) {
    http_response_code(500);
    die("❌ Error al importar: " . $e->getMessage());
}
?>
