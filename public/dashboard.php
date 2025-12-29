<?php
/**
 * DASHBOARD SERCOLTURBOT - VERSIÓN EMPRESARIAL
 * Con FAQs y Estadísticas de Horarios
 * Auto-refresh cada 30 segundos
 */
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4", "root", "C121672@c",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$usuario = $_SESSION['nombre'] ?? 'Usuario';

// Estadísticas
$stats = [
    'pendientes' => $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado IN ('pendiente', 'pendiente_asesor')")->fetchColumn(),
    'confirmadas' => $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'confirmada'")->fetchColumn(),
    'ingresos' => $pdo->query("SELECT COALESCE(SUM(precio_total), 0) FROM reservas WHERE estado = 'confirmada'")->fetchColumn(),
    'esperando_asesor' => $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente_asesor'")->fetchColumn()
];

// Estadísticas de FAQs
$faqStats = ['total' => 0, 'consultas_hoy' => 0, 'mas_consultada' => '-'];
try {
    $check = $pdo->query("SHOW TABLES LIKE 'faqs'");
    if ($check->rowCount() > 0) {
        $faqStats['total'] = $pdo->query("SELECT COUNT(*) FROM faqs WHERE activo = 1")->fetchColumn();
        $faqStats['consultas_total'] = $pdo->query("SELECT COALESCE(SUM(veces_consultada), 0) FROM faqs")->fetchColumn();
        $masConsultada = $pdo->query("SELECT pregunta, veces_consultada FROM faqs ORDER BY veces_consultada DESC LIMIT 1")->fetch();
        if ($masConsultada) {
            $faqStats['mas_consultada'] = $masConsultada['pregunta'];
            $faqStats['mas_consultada_count'] = $masConsultada['veces_consultada'];
        }
    }
} catch (Exception $e) {}

// Estadísticas de conversaciones por tipo
$convStats = ['total' => 0, 'faq' => 0, 'reserva' => 0, 'fuera_horario' => 0];
try {
    $convStats['total'] = $pdo->query("SELECT COUNT(*) FROM bot_conversaciones")->fetchColumn();
    $convStats['faq'] = $pdo->query("SELECT COUNT(*) FROM bot_conversaciones WHERE tipo_consulta = 'faq'")->fetchColumn();
    $convStats['reserva'] = $pdo->query("SELECT COUNT(*) FROM bot_conversaciones WHERE tipo_consulta = 'reserva'")->fetchColumn();
    $convStats['fuera_horario'] = $pdo->query("SELECT COUNT(*) FROM bot_conversaciones WHERE tipo_consulta = 'fuera_horario'")->fetchColumn();
} catch (Exception $e) {}

// Reservas
$reservas = $pdo->query("
    SELECT r.*, t.nombre as tour, c.nombre as cliente, c.telefono 
    FROM reservas r 
    LEFT JOIN tours t ON r.tour_id = t.id 
    LEFT JOIN clientes c ON r.cliente_id = c.id 
    ORDER BY r.id DESC
")->fetchAll();

// Asignaciones
$asignaciones = $pdo->query("
    SELECT 
        r.tour_id, r.fecha_inicio, t.nombre as tour,
        SUM(r.cantidad_personas) as total_pax,
        GROUP_CONCAT(DISTINCT CONCAT(c.nombre, ' (', c.telefono, ')') SEPARATOR ' | ') as clientes,
        COUNT(DISTINCT r.id) as num_reservas,
        MIN(a.guia_confirmado) as guia_confirmado,
        MIN(a.bus_confirmado) as bus_confirmado,
        g.id as guia_id, g.nombre as guia, g.telefono as guia_tel, g.estado as guia_estado,
        b.id as bus_id, b.nombre_busero as conductor, b.placa, b.telefono as bus_tel, b.capacidad, b.estado as bus_estado
    FROM reservas r
    INNER JOIN asignaciones a ON a.reserva_id = r.id
    LEFT JOIN tours t ON r.tour_id = t.id
    LEFT JOIN guias g ON a.guia_id = g.id
    LEFT JOIN buses b ON a.bus_id = b.id
    LEFT JOIN clientes c ON r.cliente_id = c.id
    WHERE r.estado = 'confirmada'
    GROUP BY r.tour_id, r.fecha_inicio, t.nombre, g.id, g.nombre, g.telefono, g.estado, b.id, b.nombre_busero, b.placa, b.telefono, b.capacidad, b.estado
    ORDER BY r.fecha_inicio ASC
")->fetchAll();

// Tours, Guías, Buses, Asesores
$tours = $pdo->query("SELECT * FROM tours ORDER BY id")->fetchAll();
$guias = $pdo->query("SELECT * FROM guias ORDER BY calificacion DESC")->fetchAll();
$buses = $pdo->query("SELECT * FROM buses ORDER BY id")->fetchAll();
$asesores = $pdo->query("SELECT * FROM asesores ORDER BY id")->fetchAll();

// FAQs
$faqs = [];
try {
    $check = $pdo->query("SHOW TABLES LIKE 'faqs'");
    if ($check->rowCount() > 0) {
        $faqs = $pdo->query("SELECT * FROM faqs ORDER BY veces_consultada DESC")->fetchAll();
    }
} catch (Exception $e) {}

// Clientes con conversaciones
$clientesConversacion = $pdo->query("
    SELECT DISTINCT c.id, c.nombre, c.telefono, 
           (SELECT COUNT(*) FROM bot_conversaciones WHERE cliente_id = c.id) as total_msgs,
           (SELECT MAX(timestamp) FROM bot_conversaciones WHERE cliente_id = c.id) as ultima
    FROM clientes c
    INNER JOIN bot_conversaciones bc ON bc.cliente_id = c.id
    ORDER BY ultima DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>Dashboard - SERCOLTURBOT Empresarial</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0d0d14; color: #e0e0e0; min-height: 100vh; }
        
        .header { background: linear-gradient(135deg, #1a1a2e, #16213e); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #252545; }
        .header h1 { font-size: 20px; display: flex; align-items: center; gap: 10px; }
        .header-badge { background: #10b981; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 10px; }
        .btn-logout { background: rgba(239,68,68,0.2); color: #ef4444; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-logout:hover { background: #ef4444; color: #fff; }
        
        .container { max-width: 1500px; margin: 0 auto; padding: 20px; }
        
        .stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #1a1a2e; border-radius: 12px; padding: 20px; border: 1px solid #252545; }
        .stat-icon { font-size: 24px; margin-bottom: 8px; }
        .stat-value { font-size: 28px; font-weight: 700; }
        .stat-label { font-size: 12px; color: #888; margin-top: 4px; }
        .stat-card.orange .stat-value { color: #f59e0b; }
        .stat-card.green .stat-value { color: #10b981; }
        .stat-card.blue .stat-value { color: #6366f1; }
        .stat-card.purple .stat-value { color: #a855f7; }
        .stat-card.cyan .stat-value { color: #06b6d4; }
        
        .tabs { display: flex; gap: 8px; margin-bottom: 20px; background: #1a1a2e; padding: 8px; border-radius: 12px; flex-wrap: wrap; }
        .tab { padding: 10px 18px; background: transparent; border: none; border-radius: 8px; color: #888; cursor: pointer; font-size: 13px; transition: all 0.2s; }
        .tab:hover { background: #252545; color: #fff; }
        .tab.active { background: #6366f1; color: #fff; }
        
        .panel { display: none; }
        .panel.active { display: block; }
        
        .section { background: #1a1a2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #252545; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .section-title { font-size: 16px; font-weight: 600; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #252545; padding: 12px; text-align: left; font-size: 11px; color: #888; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #252545; font-size: 13px; }
        tr:hover { background: #1e1e38; }
        
        .badge { display: inline-block; padding: 3px 10px; background: #6366f1; border-radius: 6px; font-size: 11px; }
        
        .status { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status.pendiente { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .status.pendiente_asesor { background: rgba(168,85,247,0.15); color: #a855f7; }
        .status.confirmada { background: rgba(16,185,129,0.15); color: #10b981; }
        .status.cancelada { background: rgba(239,68,68,0.15); color: #ef4444; }
        .status.activo { background: rgba(16,185,129,0.15); color: #10b981; }
        .status.asignado { background: rgba(99,102,241,0.15); color: #6366f1; }
        .status.en_tour { background: rgba(6,182,212,0.15); color: #06b6d4; }
        .status.mantenimiento { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .status.inactivo { background: rgba(107,114,128,0.15); color: #9ca3af; }
        
        .btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; transition: all 0.2s; margin: 2px; }
        .btn-primary { background: #6366f1; color: #fff; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-success { background: rgba(16,185,129,0.2); color: #10b981; }
        .btn-success:hover { background: #10b981; color: #fff; }
        .btn-danger { background: rgba(239,68,68,0.2); color: #ef4444; }
        .btn-danger:hover { background: #ef4444; color: #fff; }
        .btn-purple { background: rgba(168,85,247,0.2); color: #a855f7; }
        .btn-purple:hover { background: #a855f7; color: #fff; }
        .btn-warning { background: rgba(245,158,11,0.2); color: #f59e0b; }
        .btn-warning:hover { background: #f59e0b; color: #fff; }
        .btn-cyan { background: rgba(6,182,212,0.2); color: #06b6d4; }
        .btn-cyan:hover { background: #06b6d4; color: #fff; }
        
        .toggle { width: 40px; height: 22px; background: #ef4444; border-radius: 11px; border: none; cursor: pointer; position: relative; transition: 0.2s; }
        .toggle.on { background: #10b981; }
        .toggle::after { content: ''; position: absolute; width: 18px; height: 18px; background: #fff; border-radius: 50%; top: 2px; left: 2px; transition: 0.2s; }
        .toggle.on::after { left: 20px; }
        
        .assign-card { background: #12121c; border-radius: 10px; padding: 15px; margin-bottom: 12px; border-left: 3px solid #6366f1; }
        .assign-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
        .assign-title { font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .assign-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; font-size: 12px; }
        .assign-grid > div { padding: 8px; background: #1a1a2e; border-radius: 6px; }
        .assign-grid label { color: #666; font-size: 10px; display: block; margin-bottom: 2px; }
        
        .ocupacion-bar { height: 8px; background: #252545; border-radius: 4px; overflow: hidden; margin-top: 10px; }
        .ocupacion-fill { height: 100%; border-radius: 4px; transition: width 0.3s; }
        .ocupacion-fill.low { background: #10b981; }
        .ocupacion-fill.medium { background: #f59e0b; }
        .ocupacion-fill.high { background: #ef4444; }
        
        .faq-card { background: #12121c; border-radius: 10px; padding: 15px; margin-bottom: 12px; border: 1px solid #252545; }
        .faq-card:hover { border-color: #6366f1; }
        .faq-question { font-weight: 600; color: #6366f1; margin-bottom: 8px; }
        .faq-answer { font-size: 13px; color: #888; margin-bottom: 10px; white-space: pre-line; }
        .faq-meta { display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #666; }
        .faq-keywords { display: flex; gap: 5px; flex-wrap: wrap; }
        .faq-keyword { background: #252545; padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .faq-count { background: rgba(16,185,129,0.2); color: #10b981; padding: 3px 10px; border-radius: 10px; }
        
        .client-card { background: #12121c; border-radius: 8px; padding: 12px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #252545; }
        .client-name { font-weight: 600; color: #6366f1; }
        .client-phone { font-size: 12px; color: #888; }
        .client-stats { font-size: 11px; color: #666; text-align: right; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .mini-stat { background: #12121c; border-radius: 8px; padding: 15px; text-align: center; border: 1px solid #252545; }
        .mini-stat-value { font-size: 24px; font-weight: 700; color: #6366f1; }
        .mini-stat-label { font-size: 11px; color: #888; margin-top: 5px; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #1a1a2e; border-radius: 16px; width: 90%; max-width: 500px; max-height: 85vh; overflow: hidden; border: 1px solid #252545; }
        .modal-header { padding: 15px 20px; border-bottom: 1px solid #252545; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 16px; }
        .modal-close { width: 28px; height: 28px; background: #252545; border: none; border-radius: 6px; color: #888; cursor: pointer; font-size: 18px; }
        .modal-close:hover { background: #ef4444; color: #fff; }
        .modal-body { padding: 20px; max-height: 55vh; overflow-y: auto; }
        .modal-footer { padding: 15px 20px; border-top: 1px solid #252545; display: flex; justify-content: flex-end; gap: 10px; }
        
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 12px; color: #888; }
        .form-control { width: 100%; padding: 10px; background: #12121c; border: 1px solid #252545; border-radius: 6px; color: #fff; font-size: 13px; }
        .form-control:focus { outline: none; border-color: #6366f1; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        textarea.form-control { min-height: 80px; resize: vertical; }
        
        .empty { text-align: center; padding: 40px; color: #555; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #1a1a2e; border-left: 4px solid #10b981; border-radius: 8px; z-index: 9999; max-width: 350px; font-size: 13px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        .toast.error { border-color: #ef4444; }
        
        .refresh-indicator { position: fixed; top: 80px; right: 20px; background: #1a1a2e; padding: 8px 12px; border-radius: 8px; font-size: 11px; color: #888; border: 1px solid #252545; }
        
        @media (max-width: 768px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .assign-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .tabs { overflow-x: auto; flex-wrap: nowrap; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 SERCOLTURBOT <span class="header-badge">EMPRESARIAL</span></h1>
        <div style="display:flex;align-items:center;gap:15px">
            <span style="color:#888">👤 <?= htmlspecialchars($usuario) ?></span>
            <a href="logout.php" class="btn-logout">🚪 Salir</a>
        </div>
    </div>
    
    <div class="refresh-indicator">🔄 Auto-refresh: 30s</div>

    <div class="container">
        <!-- ESTADÍSTICAS -->
        <div class="stats">
            <div class="stat-card orange">
                <div class="stat-icon">⏳</div>
                <div class="stat-value"><?= $stats['pendientes'] ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">👨‍💼</div>
                <div class="stat-value"><?= $stats['esperando_asesor'] ?></div>
                <div class="stat-label">Esperando Asesor</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= $stats['confirmadas'] ?></div>
                <div class="stat-label">Confirmadas</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon">💰</div>
                <div class="stat-value">$<?= number_format($stats['ingresos'], 0, ',', '.') ?></div>
                <div class="stat-label">Ingresos</div>
            </div>
            <div class="stat-card cyan">
                <div class="stat-icon">❓</div>
                <div class="stat-value"><?= $faqStats['consultas_total'] ?? 0 ?></div>
                <div class="stat-label">Consultas FAQ</div>
            </div>
        </div>

        <!-- PESTAÑAS -->
        <div class="tabs">
            <button class="tab" data-tab="reservas" onclick="showTab('reservas', this)">🎫 Reservas</button>
            <button class="tab" data-tab="asignaciones" onclick="showTab('asignaciones', this)">📋 Asignaciones</button>
            <button class="tab" data-tab="tours" onclick="showTab('tours', this)">🎭 Tours</button>
            <button class="tab" data-tab="guias" onclick="showTab('guias', this)">👨‍🏫 Guías</button>
            <button class="tab" data-tab="buses" onclick="showTab('buses', this)">🚌 Buses</button>
            <button class="tab" data-tab="asesores" onclick="showTab('asesores', this)">👨‍💼 Asesores</button>
            <button class="tab" data-tab="faqs" onclick="showTab('faqs', this)">❓ FAQs</button>
            <button class="tab" data-tab="whatsapp" onclick="showTab('whatsapp', this)">💬 WhatsApp</button>
        </div>

        <!-- PANEL RESERVAS -->
        <div id="reservas" class="panel">
            <div class="section">
                <div class="section-header">
                    <div class="section-title">🎫 Reservas</div>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tour</th>
                        <th>Fecha</th>
                        <th>Pax</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($reservas as $r): ?>
                    <tr>
                        <td><span class="badge">#<?= $r['id'] ?></span></td>
                        <td>
                            <strong><?= htmlspecialchars($r['cliente'] ?? 'N/A') ?></strong><br>
                            <small style="color:#666"><?= $r['telefono'] ?? '' ?></small>
                        </td>
                        <td><?= htmlspecialchars($r['tour'] ?? 'N/A') ?></td>
                        <td><?= $r['fecha_inicio'] ?></td>
                        <td><?= $r['cantidad_personas'] ?></td>
                        <td>$<?= number_format($r['precio_total'], 0, ',', '.') ?></td>
                        <td><span class="status <?= $r['estado'] ?>"><?= ucfirst(str_replace('_', ' ', $r['estado'])) ?></span></td>
                        <td>
                            <?php if ($r['estado'] === 'pendiente'): ?>
                                <button class="btn btn-purple" onclick="enviarAsesor(<?= $r['id'] ?>)" title="Enviar al asesor">👨‍💼</button>
                                <button class="btn btn-success" onclick="confirmarVenta(<?= $r['id'] ?>)" title="Confirmar directo">✓</button>
                                <button class="btn btn-danger" onclick="cancelarReserva(<?= $r['id'] ?>)">✕</button>
                            <?php elseif ($r['estado'] === 'pendiente_asesor'): ?>
                                <span style="color:#a855f7;font-size:11px">⏳ Esperando asesor</span>
                                <button class="btn btn-success" onclick="confirmarVenta(<?= $r['id'] ?>)" title="Confirmar desde dashboard">✓</button>
                            <?php endif; ?>
                            <button class="btn btn-danger" onclick="eliminarReserva(<?= $r['id'] ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservas)): ?>
                    <tr><td colspan="8" class="empty">No hay reservas</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- PANEL ASIGNACIONES -->
        <div id="asignaciones" class="panel">
            <div class="section">
                <div class="section-title">📋 Asignaciones (Agrupadas por Tour/Fecha)</div>
                <?php if ($asignaciones): ?>
                    <?php foreach ($asignaciones as $a): 
                        $cap = $a['capacidad'] ?? 40;
                        $pax = $a['total_pax'] ?? 0;
                        $ocup = $cap > 0 ? round(($pax / $cap) * 100) : 0;
                        $ocupClass = $ocup < 50 ? 'low' : ($ocup < 80 ? 'medium' : 'high');
                    ?>
                    <div class="assign-card">
                        <div class="assign-header">
                            <div class="assign-title">
                                🎭 <?= htmlspecialchars($a['tour']) ?>
                                <span class="badge"><?= $a['num_reservas'] ?> reserva(s)</span>
                            </div>
                            <span style="color:#888;font-size:12px">📅 <?= $a['fecha_inicio'] ?></span>
                        </div>
                        <div class="assign-grid">
                            <div><label>👥 Pasajeros</label><strong style="color:#10b981"><?= $pax ?> pax</strong></div>
                            <div><label>👨‍🏫 Guía</label><?= htmlspecialchars($a['guia'] ?? 'Sin asignar') ?></div>
                            <div><label>📱 Tel Guía</label><?= $a['guia_tel'] ?? '-' ?></div>
                            <div><label>🚌 Conductor</label><?= htmlspecialchars($a['conductor'] ?? 'Sin asignar') ?></div>
                            <div><label>🚐 Placa</label><?= $a['placa'] ?? '-' ?></div>
                            <div><label>📱 Tel Bus</label><?= $a['bus_tel'] ?? '-' ?></div>
                        </div>
                        <div style="margin-top:10px;font-size:11px;color:#888">
                            <strong>Clientes:</strong> <?= htmlspecialchars($a['clientes']) ?>
                        </div>
                        <div class="ocupacion-bar">
                            <div class="ocupacion-fill <?= $ocupClass ?>" style="width:<?= min($ocup, 100) ?>%"></div>
                        </div>
                        <div style="font-size:11px;color:#888;margin-top:4px">Ocupación: <?= $pax ?>/<?= $cap ?> (<?= $ocup ?>%)</div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty">No hay asignaciones. Confirma reservas para crear asignaciones.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANEL TOURS -->
        <div id="tours" class="panel">
            <div class="section">
                <div class="section-header">
                    <div class="section-title">🎭 Tours</div>
                    <button class="btn btn-primary" onclick="abrirModalTour()">➕ Nuevo Tour</button>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Destino</th>
                        <th>Precio</th>
                        <th>Capacidad</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($tours as $t): ?>
                    <tr>
                        <td><span class="badge">#<?= $t['id'] ?></span></td>
                        <td><?= htmlspecialchars($t['nombre']) ?></td>
                        <td><?= htmlspecialchars($t['destino'] ?? '-') ?></td>
                        <td>$<?= number_format($t['precio'], 0, ',', '.') ?></td>
                        <td><?= $t['capacidad_maxima'] ?? '-' ?></td>
                        <td>
                            <button class="toggle <?= $t['activo'] ? 'on' : '' ?>" onclick="toggleTour(<?= $t['id'] ?>, <?= $t['activo'] ? 0 : 1 ?>)"></button>
                        </td>
                        <td>
                            <button class="btn btn-primary" onclick='editarTour(<?= json_encode($t) ?>)'>✏️</button>
                            <button class="btn btn-danger" onclick="eliminarTour(<?= $t['id'] ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- PANEL GUÍAS -->
        <div id="guias" class="panel">
            <div class="section">
                <div class="section-header">
                    <div class="section-title">👨‍🏫 Guías</div>
                    <button class="btn btn-primary" onclick="abrirModalGuia()">➕ Nuevo Guía</button>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Idiomas</th>
                        <th>Calificación</th>
                        <th>Estado</th>
                        <th>Disponible</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($guias as $g): ?>
                    <tr>
                        <td><span class="badge">#<?= $g['id'] ?></span></td>
                        <td><?= htmlspecialchars($g['nombre']) ?></td>
                        <td><?= $g['telefono'] ?? '-' ?></td>
                        <td><?= $g['idiomas'] ?? '-' ?></td>
                        <td>⭐ <?= number_format($g['calificacion'] ?? 0, 1) ?></td>
                        <td><span class="status <?= $g['estado'] ?? 'activo' ?>"><?= ucfirst(str_replace('_', ' ', $g['estado'] ?? 'activo')) ?></span></td>
                        <td>
                            <button class="toggle <?= $g['disponible'] ? 'on' : '' ?>" onclick="toggleGuiaDisp(<?= $g['id'] ?>, <?= $g['disponible'] ? 0 : 1 ?>)"></button>
                        </td>
                        <td>
                            <button class="btn btn-primary" onclick='editarGuia(<?= json_encode($g) ?>)'>✏️</button>
                            <?php if ($g['estado'] !== 'activo'): ?>
                            <button class="btn btn-success" onclick="liberarGuia(<?= $g['id'] ?>)" title="Liberar">🔓</button>
                            <?php endif; ?>
                            <button class="btn btn-danger" onclick="eliminarGuia(<?= $g['id'] ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($guias)): ?>
                    <tr><td colspan="8" class="empty">No hay guías registrados</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- PANEL BUSES -->
        <div id="buses" class="panel">
            <div class="section">
                <div class="section-header">
                    <div class="section-title">🚌 Buses</div>
                    <button class="btn btn-primary" onclick="abrirModalBus()">➕ Nuevo Bus</button>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Conductor</th>
                        <th>Placa</th>
                        <th>Teléfono</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                        <th>Disponible</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($buses as $b): ?>
                    <tr>
                        <td><span class="badge">#<?= $b['id'] ?></span></td>
                        <td><?= htmlspecialchars($b['nombre_busero']) ?></td>
                        <td><?= $b['placa'] ?></td>
                        <td><?= $b['telefono'] ?? '-' ?></td>
                        <td><?= $b['capacidad'] ?></td>
                        <td><span class="status <?= $b['estado'] ?? 'activo' ?>"><?= ucfirst(str_replace('_', ' ', $b['estado'] ?? 'activo')) ?></span></td>
                        <td>
                            <button class="toggle <?= $b['disponible'] ? 'on' : '' ?>" onclick="toggleBusDisp(<?= $b['id'] ?>, <?= $b['disponible'] ? 0 : 1 ?>)"></button>
                        </td>
                        <td>
                            <button class="btn btn-primary" onclick='editarBus(<?= json_encode($b) ?>)'>✏️</button>
                            <button class="btn btn-warning" onclick="toggleMantenimiento(<?= $b['id'] ?>, '<?= $b['estado'] ?>')">🔧</button>
                            <?php if ($b['estado'] !== 'activo'): ?>
                            <button class="btn btn-success" onclick="liberarBus(<?= $b['id'] ?>)" title="Liberar">🔓</button>
                            <?php endif; ?>
                            <button class="btn btn-danger" onclick="eliminarBus(<?= $b['id'] ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($buses)): ?>
                    <tr><td colspan="8" class="empty">No hay buses registrados</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- PANEL ASESORES -->
        <div id="asesores" class="panel">
            <div class="section">
                <div class="section-header">
                    <div class="section-title">👨‍💼 Asesores</div>
                    <button class="btn btn-primary" onclick="abrirModalAsesor()">➕ Nuevo Asesor</button>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Especialidad</th>
                        <th>Disponible</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($asesores as $a): ?>
                    <tr>
                        <td><span class="badge">#<?= $a['id'] ?></span></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td><?= $a['telefono'] ?></td>
                        <td><?= $a['email'] ?? '-' ?></td>
                        <td><?= $a['especialidad'] ?? '-' ?></td>
                        <td>
                            <button class="toggle <?= $a['disponible'] ? 'on' : '' ?>" onclick="toggleAsesorDisp(<?= $a['id'] ?>, <?= $a['disponible'] ? 0 : 1 ?>)"></button>
                        </td>
                        <td>
                            <button class="btn btn-primary" onclick='editarAsesor(<?= json_encode($a) ?>)'>✏️</button>
                            <button class="btn btn-danger" onclick="eliminarAsesor(<?= $a['id'] ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($asesores)): ?>
                    <tr><td colspan="7" class="empty">No hay asesores registrados</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- PANEL FAQs -->
        <div id="faqs" class="panel">
            <!-- Estadísticas FAQs -->
            <div class="stats-grid">
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $faqStats['total'] ?></div>
                    <div class="mini-stat-label">FAQs Activas</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $faqStats['consultas_total'] ?? 0 ?></div>
                    <div class="mini-stat-label">Total Consultas</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $convStats['fuera_horario'] ?></div>
                    <div class="mini-stat-label">Fuera de Horario</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $convStats['reserva'] ?></div>
                    <div class="mini-stat-label">Reservas Bot</div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-header">
                    <div class="section-title">❓ Preguntas Frecuentes (FAQs)</div>
                    <button class="btn btn-primary" onclick="abrirModalFAQ()">➕ Nueva FAQ</button>
                </div>
                
                <?php if ($faqs): ?>
                    <?php foreach ($faqs as $faq): ?>
                    <div class="faq-card">
                        <div class="faq-question"><?= htmlspecialchars($faq['pregunta']) ?></div>
                        <div class="faq-answer"><?= htmlspecialchars($faq['respuesta_corta'] ?: substr($faq['respuesta'], 0, 150) . '...') ?></div>
                        <div class="faq-meta">
                            <div class="faq-keywords">
                                <?php 
                                $keywords = json_decode($faq['palabras_clave'] ?? '[]', true) ?: [];
                                foreach (array_slice($keywords, 0, 5) as $kw): 
                                ?>
                                <span class="faq-keyword"><?= htmlspecialchars($kw) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px">
                                <span class="faq-count">📊 <?= $faq['veces_consultada'] ?> consultas</span>
                                <button class="btn btn-primary" onclick='editarFAQ(<?= json_encode($faq) ?>)'>✏️</button>
                                <button class="toggle <?= $faq['activo'] ? 'on' : '' ?>" onclick="toggleFAQ(<?= $faq['id'] ?>, <?= $faq['activo'] ? 0 : 1 ?>)"></button>
                                <button class="btn btn-danger" onclick="eliminarFAQ(<?= $faq['id'] ?>)">🗑️</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty">
                        No hay FAQs configuradas.<br><br>
                        <small>Ejecuta el SQL de FAQs en phpMyAdmin para agregar preguntas frecuentes.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANEL WHATSAPP -->
        <div id="whatsapp" class="panel">
            <!-- Estadísticas Conversaciones -->
            <div class="stats-grid">
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $convStats['total'] ?></div>
                    <div class="mini-stat-label">Total Mensajes</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $convStats['faq'] ?></div>
                    <div class="mini-stat-label">Respondidas por FAQ</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $convStats['reserva'] ?></div>
                    <div class="mini-stat-label">Reservas</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value"><?= $convStats['fuera_horario'] ?></div>
                    <div class="mini-stat-label">Fuera de Horario</div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">💬 Clientes con Conversaciones</div>
                <?php if ($clientesConversacion): ?>
                    <?php foreach ($clientesConversacion as $c): ?>
                    <div class="client-card">
                        <div class="client-info">
                            <div class="client-name">👤 <?= htmlspecialchars($c['nombre']) ?></div>
                            <div class="client-phone">📱 <?= $c['telefono'] ?></div>
                        </div>
                        <div class="client-stats">
                            <?= $c['total_msgs'] ?> mensajes<br>
                            <small><?= $c['ultima'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty">No hay conversaciones registradas</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODALES -->
    <!-- Modal Tour -->
    <div class="modal" id="modalTour">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTourTitle">Tour</h3>
                <button class="modal-close" onclick="closeModal('modalTour')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tour_id">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" class="form-control" id="tour_nombre">
                </div>
                <div class="form-group">
                    <label>Destino</label>
                    <input type="text" class="form-control" id="tour_destino">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Precio *</label>
                        <input type="number" class="form-control" id="tour_precio">
                    </div>
                    <div class="form-group">
                        <label>Capacidad</label>
                        <input type="number" class="form-control" id="tour_capacidad" value="40">
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea class="form-control" id="tour_descripcion" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal('modalTour')">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarTour()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Guía -->
    <div class="modal" id="modalGuia">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalGuiaTitle">Guía</h3>
                <button class="modal-close" onclick="closeModal('modalGuia')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="guia_id">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" class="form-control" id="guia_nombre">
                </div>
                <div class="form-group">
                    <label>Teléfono * (ej: 573011773292)</label>
                    <input type="text" class="form-control" id="guia_telefono" placeholder="573011773292">
                </div>
                <div class="form-group">
                    <label>Idiomas</label>
                    <input type="text" class="form-control" id="guia_idiomas" value="Español">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Experiencia (años)</label>
                        <input type="number" class="form-control" id="guia_experiencia" value="1">
                    </div>
                    <div class="form-group">
                        <label>Calificación</label>
                        <input type="number" class="form-control" id="guia_calificacion" value="5" step="0.1" min="0" max="5">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal('modalGuia')">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarGuia()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Bus -->
    <div class="modal" id="modalBus">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalBusTitle">Bus</h3>
                <button class="modal-close" onclick="closeModal('modalBus')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bus_id">
                <div class="form-group">
                    <label>Nombre del Conductor *</label>
                    <input type="text" class="form-control" id="bus_conductor">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Placa *</label>
                        <input type="text" class="form-control" id="bus_placa">
                    </div>
                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="text" class="form-control" id="bus_telefono" placeholder="573136761256">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" class="form-control" id="bus_marca">
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" class="form-control" id="bus_modelo">
                    </div>
                </div>
                <div class="form-group">
                    <label>Capacidad</label>
                    <input type="number" class="form-control" id="bus_capacidad" value="40">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal('modalBus')">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarBus()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Asesor -->
    <div class="modal" id="modalAsesor">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalAsesorTitle">Asesor</h3>
                <button class="modal-close" onclick="closeModal('modalAsesor')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="asesor_id">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" class="form-control" id="asesor_nombre">
                </div>
                <div class="form-group">
                    <label>Teléfono * (ej: 573052100297)</label>
                    <input type="text" class="form-control" id="asesor_telefono" placeholder="573052100297">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" class="form-control" id="asesor_email">
                </div>
                <div class="form-group">
                    <label>Especialidad</label>
                    <input type="text" class="form-control" id="asesor_especialidad" value="Ventas">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal('modalAsesor')">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarAsesor()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal FAQ -->
    <div class="modal" id="modalFAQ">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalFAQTitle">FAQ</h3>
                <button class="modal-close" onclick="closeModal('modalFAQ')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="faq_id">
                <div class="form-group">
                    <label>Pregunta *</label>
                    <input type="text" class="form-control" id="faq_pregunta" placeholder="¿Cuáles son los horarios?">
                </div>
                <div class="form-group">
                    <label>Palabras clave (separadas por coma) *</label>
                    <input type="text" class="form-control" id="faq_keywords" placeholder="horario, hora, atencion">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select class="form-control" id="faq_categoria">
                        <option value="general">General</option>
                        <option value="tours">Tours</option>
                        <option value="reservas">Reservas</option>
                        <option value="pagos">Pagos</option>
                        <option value="cancelaciones">Cancelaciones</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Respuesta corta (para WhatsApp) *</label>
                    <textarea class="form-control" id="faq_respuesta_corta" rows="2" placeholder="Lun-Vie: 7am-8pm"></textarea>
                </div>
                <div class="form-group">
                    <label>Respuesta completa</label>
                    <textarea class="form-control" id="faq_respuesta" rows="4" placeholder="Respuesta detallada..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal('modalFAQ')">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarFAQ()">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        let currentTab = localStorage.getItem('activeTab') || 'reservas';

        function showTab(id, btn) {
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            if (btn) btn.classList.add('active');
            else document.querySelector(`[data-tab="${id}"]`)?.classList.add('active');
            localStorage.setItem('activeTab', id);
        }

        document.addEventListener('DOMContentLoaded', () => showTab(currentTab));

        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        document.querySelectorAll('.modal').forEach(m => {
            m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
        });

        function toast(msg, isError = false) {
            const t = document.createElement('div');
            t.className = 'toast' + (isError ? ' error' : '');
            t.innerHTML = msg.replace(/\n/g, '<br>');
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 4000);
        }

        function recargar() { setTimeout(() => location.reload(), 1200); }

        async function api(action, data = {}) {
            try {
                const r = await fetch('dashboard-api.php?action=' + encodeURIComponent(action), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                return await r.json();
            } catch (e) {
                return { success: false, error: e.message };
            }
        }

        // RESERVAS
        async function enviarAsesor(id) {
            toast('📤 Enviando al asesor...');
            const r = await api('enviar-a-asesor', { id });
            if (r.success) { toast('👨‍💼 Enviado al asesor'); recargar(); }
            else toast('❌ ' + r.error, true);
        }

        async function confirmarVenta(id) {
            if (!confirm('¿Confirmar venta y asignar guía/bus?')) return;
            toast('⏳ Confirmando...');
            const r = await api('confirmar-venta', { id });
            if (r.success) { toast('✅ Venta confirmada'); recargar(); }
            else toast('❌ ' + r.error, true);
        }

        async function cancelarReserva(id) {
            if (!confirm('¿Cancelar esta reserva?')) return;
            await api('cancelar-reserva', { id });
            toast('❌ Reserva cancelada'); recargar();
        }

        async function eliminarReserva(id) {
            if (!confirm('¿Eliminar reserva #' + id + '?')) return;
            await api('delete-reservation', { id });
            toast('🗑️ Reserva eliminada'); recargar();
        }

        // TOURS
        function abrirModalTour() {
            document.getElementById('modalTourTitle').textContent = '➕ Nuevo Tour';
            document.getElementById('tour_id').value = '';
            document.getElementById('tour_nombre').value = '';
            document.getElementById('tour_destino').value = '';
            document.getElementById('tour_precio').value = '';
            document.getElementById('tour_capacidad').value = '40';
            document.getElementById('tour_descripcion').value = '';
            openModal('modalTour');
        }

        function editarTour(t) {
            document.getElementById('modalTourTitle').textContent = '✏️ Editar Tour';
            document.getElementById('tour_id').value = t.id;
            document.getElementById('tour_nombre').value = t.nombre;
            document.getElementById('tour_destino').value = t.destino || '';
            document.getElementById('tour_precio').value = t.precio;
            document.getElementById('tour_capacidad').value = t.capacidad_maxima || 40;
            document.getElementById('tour_descripcion').value = t.descripcion || '';
            openModal('modalTour');
        }

        async function guardarTour() {
            const d = {
                id: document.getElementById('tour_id').value,
                nombre: document.getElementById('tour_nombre').value,
                destino: document.getElementById('tour_destino').value,
                precio: document.getElementById('tour_precio').value,
                capacidad_maxima: document.getElementById('tour_capacidad').value,
                descripcion: document.getElementById('tour_descripcion').value
            };
            if (!d.nombre || !d.precio) { toast('Completa los campos obligatorios', true); return; }
            await api(d.id ? 'edit-tour' : 'create-tour', d);
            toast('✅ Tour guardado'); closeModal('modalTour'); recargar();
        }

        async function toggleTour(id, activo) { await api('toggle-tour', { id, activo }); recargar(); }
        async function eliminarTour(id) { if (!confirm('¿Eliminar este tour?')) return; const r = await api('delete-tour', { id }); if (!r.success) toast(r.error, true); else recargar(); }

        // GUÍAS
        function abrirModalGuia() {
            document.getElementById('modalGuiaTitle').textContent = '➕ Nuevo Guía';
            document.getElementById('guia_id').value = '';
            document.getElementById('guia_nombre').value = '';
            document.getElementById('guia_telefono').value = '';
            document.getElementById('guia_idiomas').value = 'Español';
            document.getElementById('guia_experiencia').value = '1';
            document.getElementById('guia_calificacion').value = '5';
            openModal('modalGuia');
        }

        function editarGuia(g) {
            document.getElementById('modalGuiaTitle').textContent = '✏️ Editar Guía';
            document.getElementById('guia_id').value = g.id;
            document.getElementById('guia_nombre').value = g.nombre;
            document.getElementById('guia_telefono').value = g.telefono || '';
            document.getElementById('guia_idiomas').value = g.idiomas || 'Español';
            document.getElementById('guia_experiencia').value = g.experiencia || 1;
            document.getElementById('guia_calificacion').value = g.calificacion || 5;
            openModal('modalGuia');
        }

        async function guardarGuia() {
            const d = {
                id: document.getElementById('guia_id').value,
                nombre: document.getElementById('guia_nombre').value,
                telefono: document.getElementById('guia_telefono').value,
                idiomas: document.getElementById('guia_idiomas').value,
                experiencia: document.getElementById('guia_experiencia').value,
                calificacion: document.getElementById('guia_calificacion').value
            };
            if (!d.nombre || !d.telefono) { toast('Completa nombre y teléfono', true); return; }
            await api(d.id ? 'edit-guide' : 'create-guide', d);
            toast('✅ Guía guardado'); closeModal('modalGuia'); recargar();
        }

        async function toggleGuiaDisp(id, disponible) { await api('edit-guide', { id, disponible }); recargar(); }
        async function liberarGuia(id) { if (!confirm('¿Liberar este guía?')) return; await api('liberar-guia', { id }); toast('🔓 Guía liberado'); recargar(); }
        async function eliminarGuia(id) { if (!confirm('¿Eliminar este guía?')) return; const r = await api('delete-guide', { id }); if (!r.success) toast(r.error, true); else recargar(); }

        // BUSES
        function abrirModalBus() {
            document.getElementById('modalBusTitle').textContent = '➕ Nuevo Bus';
            document.getElementById('bus_id').value = '';
            document.getElementById('bus_conductor').value = '';
            document.getElementById('bus_placa').value = '';
            document.getElementById('bus_telefono').value = '';
            document.getElementById('bus_marca').value = '';
            document.getElementById('bus_modelo').value = '';
            document.getElementById('bus_capacidad').value = '40';
            openModal('modalBus');
        }

        function editarBus(b) {
            document.getElementById('modalBusTitle').textContent = '✏️ Editar Bus';
            document.getElementById('bus_id').value = b.id;
            document.getElementById('bus_conductor').value = b.nombre_busero;
            document.getElementById('bus_placa').value = b.placa;
            document.getElementById('bus_telefono').value = b.telefono || '';
            document.getElementById('bus_marca').value = b.marca || '';
            document.getElementById('bus_modelo').value = b.modelo || '';
            document.getElementById('bus_capacidad').value = b.capacidad || 40;
            openModal('modalBus');
        }

        async function guardarBus() {
            const d = {
                id: document.getElementById('bus_id').value,
                nombre_busero: document.getElementById('bus_conductor').value,
                placa: document.getElementById('bus_placa').value,
                telefono: document.getElementById('bus_telefono').value,
                marca: document.getElementById('bus_marca').value,
                modelo: document.getElementById('bus_modelo').value,
                capacidad: document.getElementById('bus_capacidad').value
            };
            if (!d.nombre_busero || !d.placa || !d.telefono) { toast('Completa los campos obligatorios', true); return; }
            await api(d.id ? 'edit-bus' : 'create-bus', d);
            toast('✅ Bus guardado'); closeModal('modalBus'); recargar();
        }

        async function toggleBusDisp(id, disponible) { await api('edit-bus', { id, disponible }); recargar(); }
        async function toggleMantenimiento(id, estado) {
            const nuevo = estado === 'mantenimiento' ? 'activo' : 'mantenimiento';
            await api('edit-bus', { id, estado: nuevo });
            toast(nuevo === 'mantenimiento' ? '🔧 En mantenimiento' : '✅ Activo'); recargar();
        }
        async function liberarBus(id) { if (!confirm('¿Liberar este bus?')) return; await api('liberar-bus', { id }); toast('🔓 Bus liberado'); recargar(); }
        async function eliminarBus(id) { if (!confirm('¿Eliminar este bus?')) return; const r = await api('delete-bus', { id }); if (!r.success) toast(r.error, true); else recargar(); }

        // ASESORES
        function abrirModalAsesor() {
            document.getElementById('modalAsesorTitle').textContent = '➕ Nuevo Asesor';
            document.getElementById('asesor_id').value = '';
            document.getElementById('asesor_nombre').value = '';
            document.getElementById('asesor_telefono').value = '';
            document.getElementById('asesor_email').value = '';
            document.getElementById('asesor_especialidad').value = 'Ventas';
            openModal('modalAsesor');
        }

        function editarAsesor(a) {
            document.getElementById('modalAsesorTitle').textContent = '✏️ Editar Asesor';
            document.getElementById('asesor_id').value = a.id;
            document.getElementById('asesor_nombre').value = a.nombre;
            document.getElementById('asesor_telefono').value = a.telefono || '';
            document.getElementById('asesor_email').value = a.email || '';
            document.getElementById('asesor_especialidad').value = a.especialidad || 'Ventas';
            openModal('modalAsesor');
        }

        async function guardarAsesor() {
            const d = {
                id: document.getElementById('asesor_id').value,
                nombre: document.getElementById('asesor_nombre').value,
                telefono: document.getElementById('asesor_telefono').value,
                email: document.getElementById('asesor_email').value,
                especialidad: document.getElementById('asesor_especialidad').value
            };
            if (!d.nombre || !d.telefono || !d.email) { toast('Completa los campos obligatorios', true); return; }
            await api(d.id ? 'edit-asesor' : 'create-asesor', d);
            toast('✅ Asesor guardado'); closeModal('modalAsesor'); recargar();
        }

        async function toggleAsesorDisp(id, disponible) { await api('edit-asesor', { id, disponible }); recargar(); }
        async function eliminarAsesor(id) { if (!confirm('¿Eliminar este asesor?')) return; const r = await api('delete-asesor', { id }); if (!r.success) toast(r.error, true); else recargar(); }

        // FAQs
        function abrirModalFAQ() {
            document.getElementById('modalFAQTitle').textContent = '➕ Nueva FAQ';
            document.getElementById('faq_id').value = '';
            document.getElementById('faq_pregunta').value = '';
            document.getElementById('faq_keywords').value = '';
            document.getElementById('faq_categoria').value = 'general';
            document.getElementById('faq_respuesta_corta').value = '';
            document.getElementById('faq_respuesta').value = '';
            openModal('modalFAQ');
        }

        function editarFAQ(f) {
            document.getElementById('modalFAQTitle').textContent = '✏️ Editar FAQ';
            document.getElementById('faq_id').value = f.id;
            document.getElementById('faq_pregunta').value = f.pregunta;
            const kws = JSON.parse(f.palabras_clave || '[]');
            document.getElementById('faq_keywords').value = kws.join(', ');
            document.getElementById('faq_categoria').value = f.categoria || 'general';
            document.getElementById('faq_respuesta_corta').value = f.respuesta_corta || '';
            document.getElementById('faq_respuesta').value = f.respuesta || '';
            openModal('modalFAQ');
        }

        async function guardarFAQ() {
            const keywords = document.getElementById('faq_keywords').value.split(',').map(k => k.trim()).filter(k => k);
            const d = {
                id: document.getElementById('faq_id').value,
                pregunta: document.getElementById('faq_pregunta').value,
                palabras_clave: JSON.stringify(keywords),
                categoria: document.getElementById('faq_categoria').value,
                respuesta_corta: document.getElementById('faq_respuesta_corta').value,
                respuesta: document.getElementById('faq_respuesta').value || document.getElementById('faq_respuesta_corta').value
            };
            if (!d.pregunta || keywords.length === 0 || !d.respuesta_corta) { toast('Completa pregunta, palabras clave y respuesta', true); return; }
            await api(d.id ? 'edit-faq' : 'create-faq', d);
            toast('✅ FAQ guardada'); closeModal('modalFAQ'); recargar();
        }

        async function toggleFAQ(id, activo) { await api('toggle-faq', { id, activo }); recargar(); }
        async function eliminarFAQ(id) { if (!confirm('¿Eliminar esta FAQ?')) return; await api('delete-faq', { id }); toast('🗑️ FAQ eliminada'); recargar(); }
    </script>
</body>
</html>
