<?php
require_once __DIR__ . '/config/database.php';



if (isset($_GET['dbtest'])) {
    require_once __DIR__ . '/setup/init-database.php';

    header('Content-Type: text/plain');

    $pdo = getDatabase();
    if ($pdo) {
        echo "✅ CONECTADO A MYSQL\n";
        echo "Base de datos: " . $pdo->query("SELECT DATABASE()")->fetchColumn();
    } else {
        echo "❌ NO CONECTA\n";
        echo "DATABASE_URL = ";
        var_dump(getenv('DATABASE_URL'));
    }
    exit;
}

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/setup/init-database.php';

$pdo = getDatabase();
if (!$pdo) {
    die('❌ Error: No se pudo conectar a MySQL (Railway)');
}

$usuario = $_SESSION['nombre'] ?? 'Usuario';

/* =========================
   ESTADÍSTICAS GENERALES
========================= */
$stats = [
    'pendientes' => $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado IN ('pendiente','pendiente_asesor')")->fetchColumn(),
    'confirmadas' => $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado='confirmada'")->fetchColumn(),
    'ingresos' => $pdo->query("SELECT COALESCE(SUM(precio_total),0) FROM reservas WHERE estado='confirmada'")->fetchColumn(),
    'esperando_asesor' => $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado='pendiente_asesor'")->fetchColumn()
];

/* =========================
   FAQs
========================= */
$faqStats = ['total'=>0,'consultas_total'=>0,'mas_consultada'=>'-'];
if ($pdo->query("SHOW TABLES LIKE 'faqs'")->rowCount()) {
    $faqStats['total'] = $pdo->query("SELECT COUNT(*) FROM faqs WHERE activo=1")->fetchColumn();
    $faqStats['consultas_total'] = $pdo->query("SELECT COALESCE(SUM(veces_consultada),0) FROM faqs")->fetchColumn();
    $row = $pdo->query("SELECT pregunta FROM faqs ORDER BY veces_consultada DESC LIMIT 1")->fetch();
    if ($row) $faqStats['mas_consultada'] = $row['pregunta'];
}

/* =========================
   CONVERSACIONES BOT
========================= */
$convStats = [
    'total'=>$pdo->query("SELECT COUNT(*) FROM bot_conversaciones")->fetchColumn(),
    'faq'=>$pdo->query("SELECT COUNT(*) FROM bot_conversaciones WHERE tipo_consulta='faq'")->fetchColumn(),
    'reserva'=>$pdo->query("SELECT COUNT(*) FROM bot_conversaciones WHERE tipo_consulta='reserva'")->fetchColumn(),
    'fuera_horario'=>$pdo->query("SELECT COUNT(*) FROM bot_conversaciones WHERE tipo_consulta='fuera_horario'")->fetchColumn()
];

/* =========================
   DATA PRINCIPAL
========================= */
$reservas = $pdo->query("
    SELECT r.*, t.nombre AS tour, c.nombre AS cliente, c.telefono
    FROM reservas r
    LEFT JOIN tours t ON t.id=r.tour_id
    LEFT JOIN clientes c ON c.id=r.cliente_id
    ORDER BY r.id DESC
")->fetchAll();

$asignaciones = $pdo->query("
    SELECT r.tour_id,r.fecha_inicio,t.nombre AS tour,
           SUM(r.cantidad_personas) total_pax,
           GROUP_CONCAT(DISTINCT CONCAT(c.nombre,' (',c.telefono,')') SEPARATOR ' | ') clientes,
           COUNT(DISTINCT r.id) num_reservas,
           g.nombre guia,g.telefono guia_tel,b.nombre_busero conductor,b.placa,b.telefono bus_tel,b.capacidad
    FROM reservas r
    JOIN asignaciones a ON a.reserva_id=r.id
    LEFT JOIN tours t ON t.id=r.tour_id
    LEFT JOIN guias g ON g.id=a.guia_id
    LEFT JOIN buses b ON b.id=a.bus_id
    LEFT JOIN clientes c ON c.id=r.cliente_id
    WHERE r.estado='confirmada'
    GROUP BY r.tour_id,r.fecha_inicio
")->fetchAll();

$tours = $pdo->query("SELECT * FROM tours")->fetchAll();
$guias = $pdo->query("SELECT * FROM guias")->fetchAll();
$buses = $pdo->query("SELECT * FROM buses")->fetchAll();
$asesores = $pdo->query("SELECT * FROM asesores")->fetchAll();
$faqs = $pdo->query("SHOW TABLES LIKE 'faqs'")->rowCount() ? $pdo->query("SELECT * FROM faqs")->fetchAll() : [];
$clientesConversacion = $pdo->query("
    SELECT c.nombre,c.telefono,
           COUNT(bc.id) total_msgs,
           MAX(bc.timestamp) ultima
    FROM clientes c
    JOIN bot_conversaciones bc ON bc.cliente_id=c.id
    GROUP BY c.id
    ORDER BY ultima DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta http-equiv="refresh" content="30">
<title>Dashboard SERCOLTURBOT</title>
<link rel="stylesheet" href="assets/dashboard.css">
</head>
<body>

<div class="header">
  <h1>📊 SERCOLTURBOT <span class="badge">EMPRESARIAL</span></h1>
  <div>
    👤 <?= htmlspecialchars($usuario) ?>
    <a href="logout.php">Salir</a>
  </div>
</div>

<div class="container">

<div class="stats">
  <div>⏳ Pendientes: <?= $stats['pendientes'] ?></div>
  <div>👨‍💼 Asesor: <?= $stats['esperando_asesor'] ?></div>
  <div>✅ Confirmadas: <?= $stats['confirmadas'] ?></div>
  <div>💰 Ingresos: $<?= number_format($stats['ingresos'],0,',','.') ?></div>
  <div>❓ FAQs: <?= $faqStats['consultas_total'] ?></div>
</div>

<h2>🎫 Reservas</h2>
<table>
<tr><th>ID</th><th>Cliente</th><th>Tour</th><th>Fecha</th><th>Pax</th><th>Total</th><th>Estado</th></tr>
<?php foreach ($reservas as $r): ?>
<tr>
<td>#<?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['cliente']) ?></td>
<td><?= htmlspecialchars($r['tour']) ?></td>
<td><?= $r['fecha_inicio'] ?></td>
<td><?= $r['cantidad_personas'] ?></td>
<td>$<?= number_format($r['precio_total'],0,',','.') ?></td>
<td><?= $r['estado'] ?></td>
</tr>
<?php endforeach ?>
</table>

<h2>💬 Conversaciones WhatsApp</h2>
<?php foreach ($clientesConversacion as $c): ?>
<div>
👤 <?= htmlspecialchars($c['nombre']) ?> | 📱 <?= $c['telefono'] ?>
<br>Mensajes: <?= $c['total_msgs'] ?> | Último: <?= $c['ultima'] ?>
</div>
<?php endforeach ?>

</div>
</body>
</html>
