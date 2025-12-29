<?php
$pdo = new PDO('mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4', 'root', 'C121672@c', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "=== VERIFICANDO ASIGNACIONES DUPLICADAS ===\n\n";

// Encontrar reservas con múltiples asignaciones
$stmt = $pdo->query("SELECT reserva_id, COUNT(*) as cnt FROM asignaciones GROUP BY reserva_id HAVING cnt > 1");
$duplicados = $stmt->fetchAll();

if (!$duplicados) {
    echo "✅ No hay asignaciones duplicadas.\n";
} else {
    echo "Encontradas " . count($duplicados) . " reservas con múltiples asignaciones\n\n";
    
    foreach ($duplicados as $dup) {
        echo "📋 Reserva ID: {$dup['reserva_id']} ({$dup['cnt']} asignaciones)\n";
        
        $stmt2 = $pdo->prepare("SELECT a.id as asignacion_id, a.guia_id, g.nombre as guia, a.bus_id, b.nombre_busero as conductor FROM asignaciones a LEFT JOIN guias g ON a.guia_id = g.id LEFT JOIN buses b ON a.bus_id = b.id WHERE a.reserva_id = ?");
        $stmt2->execute([$dup['reserva_id']]);
        $asignaciones = $stmt2->fetchAll();
        
        foreach ($asignaciones as $asig) {
            echo "  - Asignación ID {$asig['asignacion_id']}: Guía: {$asig['guia']}, Bus: {$asig['conductor']}\n";
        }
        echo "\n";
    }
}

echo "\n=== VERIFICANDO CLIENTES EN RESERVAS CONFIRMADAS ===\n\n";

// Mostrar todas las reservas confirmadas con sus clientes
$stmt = $pdo->query("
    SELECT r.id, r.codigo_whatsapp, c.nombre as cliente, r.tour_id, r.fecha_inicio 
    FROM reservas r 
    LEFT JOIN clientes c ON r.cliente_id = c.id 
    WHERE r.estado = 'confirmada'
    ORDER BY r.fecha_inicio DESC, r.id DESC
");

$reservas = $stmt->fetchAll();
echo "Total de reservas confirmadas: " . count($reservas) . "\n\n";

$agrupadasPorFecha = [];
foreach ($reservas as $r) {
    $key = $r['tour_id'] . '_' . $r['fecha_inicio'];
    if (!isset($agrupadasPorFecha[$key])) {
        $agrupadasPorFecha[$key] = [];
    }
    $agrupadasPorFecha[$key][] = $r;
}

foreach ($agrupadasPorFecha as $key => $items) {
    if (count($items) > 1) {
        echo "🎯 Tour ID {$items[0]['tour_id']} - Fecha {$items[0]['fecha_inicio']}\n";
        foreach ($items as $r) {
            echo "  📋 Reserva #{$r['id']} ({$r['codigo_whatsapp']}): {$r['cliente']}\n";
        }
        echo "\n";
    }
}

echo "✅ Verificación completada\n";
?>
