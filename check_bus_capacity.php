<?php
$pdo = new PDO('mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4', 'root', 'C121672@c', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "=== VERIFICANDO CAPACIDAD DE BUSES ===\n\n";

$stmt = $pdo->query("SELECT id, placa, nombre_busero, capacidad FROM buses ORDER BY id");
$buses = $stmt->fetchAll();

if (!$buses) {
    echo "❌ No hay buses registrados.\n";
    exit;
}

echo "Total de buses: " . count($buses) . "\n\n";

$sinCapacidad = 0;
foreach ($buses as $b) {
    $cap = $b['capacidad'] ?? 0;
    if (!$cap) {
        echo "⚠️  Bus #{$b['id']} {$b['placa']} - {$b['nombre_busero']}: SIN CAPACIDAD\n";
        $sinCapacidad++;
    } else {
        echo "✅ Bus #{$b['id']} {$b['placa']} - {$b['nombre_busero']}: Capacidad {$cap} pax\n";
    }
}

if ($sinCapacidad > 0) {
    echo "\n⚠️  $sinCapacidad buses sin capacidad definida.\n";
    echo "Actualizando capacidad a 40 (por defecto)...\n";
    
    $stmt = $pdo->prepare("UPDATE buses SET capacidad = 40 WHERE capacidad IS NULL OR capacidad = 0");
    $stmt->execute();
    echo "✅ Actualizado.\n";
}
?>
