<?php
$pdo = new PDO('mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4', 'root', 'C121672@c', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "=== LIMPIANDO CLIENTES DUPLICADOS ===\n\n";

// Encontrar clientes duplicados por teléfono
$stmt = $pdo->query("SELECT telefono, COUNT(*) as cnt FROM clientes GROUP BY telefono HAVING cnt > 1");
$duplicados = $stmt->fetchAll();

if (!$duplicados) {
    echo "✅ No hay clientes duplicados.\n";
    exit;
}

echo "Encontrados " . count($duplicados) . " números de teléfono duplicados\n\n";

foreach ($duplicados as $dup) {
    echo "📱 Teléfono: {$dup['telefono']} ({$dup['cnt']} registros)\n";
    
    // Obtener todos los registros duplicados
    $stmt2 = $pdo->prepare("SELECT id, nombre FROM clientes WHERE telefono = ? ORDER BY id ASC");
    $stmt2->execute([$dup['telefono']]);
    $registros = $stmt2->fetchAll();
    
    $nombreFinal = null;
    $idConservar = null;
    
    // Buscar el nombre más actualizado (no genérico)
    foreach ($registros as $reg) {
        echo "  - ID {$reg['id']}: {$reg['nombre']}\n";
        
        // Si no es un nombre genérico "Cliente XXXX", es el nombre real
        if (!preg_match('/^Cliente \d+$/', $reg['nombre'])) {
            $nombreFinal = $reg['nombre'];
            $idConservar = $idConservar ?: $reg['id'];
        }
    }
    
    // Si todos son genéricos, usar el primero
    if (!$idConservar) {
        $idConservar = $registros[0]['id'];
        $nombreFinal = $registros[0]['nombre'];
    }
    
    echo "  ✓ Se mantendrá: ID $idConservar - $nombreFinal\n";
    
    // Redirigir todas las reservas al cliente a conservar
    foreach ($registros as $reg) {
        if ($reg['id'] != $idConservar) {
            $pdo->prepare("UPDATE reservas SET cliente_id = ? WHERE cliente_id = ?")->execute([$idConservar, $reg['id']]);
            $pdo->prepare("UPDATE bot_conversaciones SET cliente_id = ? WHERE cliente_id = ?")->execute([$idConservar, $reg['id']]);
            
            // Eliminar cliente duplicado
            $pdo->prepare("DELETE FROM clientes WHERE id = ?")->execute([$reg['id']]);
            echo "  - Eliminado ID {$reg['id']}\n";
        }
    }
    
    // Actualizar nombre del cliente conservado
    $pdo->prepare("UPDATE clientes SET nombre = ? WHERE id = ?")->execute([$nombreFinal, $idConservar]);
    echo "  - Nombre actualizado a: $nombreFinal\n\n";
}

echo "\n✅ LIMPIEZA COMPLETADA\n";
