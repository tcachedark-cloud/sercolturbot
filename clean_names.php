<?php
$pdo = new PDO('mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4', 'root', 'C121672@c', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "=== LIMPIANDO NOMBRES DUPLICADOS ===\n\n";

// Actualizar nombres que tengan patrón duplicado (ej: "Monica monica" o "Monica Monica")
$stmt = $pdo->query("SELECT id, nombre FROM clientes");
$clientes = $stmt->fetchAll();

$actualizados = 0;

foreach ($clientes as $c) {
    $nombre = $c['nombre'];
    
    // Patrón 1: Nombre repetido exactamente (Monica Monica)
    if (preg_match('/^(.+?)\s+\1$/i', $nombre, $m)) {
        $nombreNuevo = $m[1];
        echo "🔄 ID {$c['id']}: '{$nombre}' → '{$nombreNuevo}'\n";
        $pdo->prepare("UPDATE clientes SET nombre = ? WHERE id = ?")->execute([$nombreNuevo, $c['id']]);
        $actualizados++;
    }
    // Patrón 2: Nombre con espacios múltiples
    elseif (preg_match('/\s{2,}/', $nombre)) {
        $nombreNuevo = preg_replace('/\s+/', ' ', $nombre);
        echo "🔄 ID {$c['id']}: '{$nombre}' → '{$nombreNuevo}' (espacios)\n";
        $pdo->prepare("UPDATE clientes SET nombre = ? WHERE id = ?")->execute([$nombreNuevo, $c['id']]);
        $actualizados++;
    }
}

if ($actualizados === 0) {
    echo "✅ No se encontraron nombres duplicados para limpiar.\n";
} else {
    echo "\n✅ Se limpiaron $actualizados registros.\n";
}
?>
