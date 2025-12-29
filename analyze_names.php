<?php
$pdo = new PDO('mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4', 'root', 'C121672@c', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "=== ANALIZANDO RESERVAS Y CLIENTES ===\n\n";

// Ver todas las reservas con sus clientes
$stmt = $pdo->query("
    SELECT r.id, r.codigo_whatsapp, c.id as cliente_id, c.nombre, c.telefono, r.estado 
    FROM reservas r 
    LEFT JOIN clientes c ON r.cliente_id = c.id 
    ORDER BY c.nombre, r.id DESC
");

$reservas = $stmt->fetchAll();

if ($reservas) {
    echo "Total de reservas: " . count($reservas) . "\n\n";
    
    $clienteActual = null;
    foreach ($reservas as $r) {
        if ($clienteActual !== $r['nombre']) {
            echo "\n👤 CLIENTE: {$r['nombre']} (ID: {$r['cliente_id']}, Tel: {$r['telefono']})\n";
            echo "   " . str_repeat("=", 60) . "\n";
            $clienteActual = $r['nombre'];
        }
        echo "   📋 Reserva #{$r['id']} | Código: {$r['codigo_whatsapp']} | Estado: {$r['estado']}\n";
    }
} else {
    echo "No hay reservas.\n";
}

echo "\n\n=== BUSCANDO NOMBRES DUPLICADOS DENTRO DE CAMPOS ===\n\n";

// Buscar clientes con nombres que contengan repeticiones
$stmt = $pdo->query("SELECT id, nombre FROM clientes WHERE nombre LIKE '%  %' OR nombre LIKE '%Monica%Monica%' OR nombre LIKE '%Vanesa%Vanesa%'");
$conRepeticiones = $stmt->fetchAll();

if ($conRepeticiones) {
    echo "⚠️  Encontrados clientes con nombres duplicados:\n";
    foreach ($conRepeticiones as $c) {
        echo "  ID {$c['id']}: '{$c['nombre']}'\n";
    }
} else {
    echo "✅ No hay nombres duplicados dentro de los campos.\n";
}

echo "\n";
?>
