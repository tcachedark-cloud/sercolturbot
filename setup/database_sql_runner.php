<?php
require_once __DIR__ . '/init-database.php';

$pdo = getDatabase();
if (!$pdo) {
    echo '<div style="color:red">❌ No se pudo conectar a MySQL</div>';
    return;
}

$sqlFile = __DIR__ . '/database.sql';

if (!file_exists($sqlFile)) {
    echo '<div style="color:red">❌ database.sql no encontrado</div>';
    return;
}

try {
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    echo '<div style="color:green">✅ Base de datos creada correctamente en Railway</div>';
} catch (PDOException $e) {
    echo '<div style="color:red">❌ Error SQL: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
