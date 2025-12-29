<?php
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$action = $_GET['action'] ?? $_REQUEST['action'] ?? '';
$inputRaw = file_get_contents('php://input');
$data = json_decode($inputRaw, true) ?? [];

$logFile = __DIR__ . '/api_log.txt';
function logAPI($msg) { 
    global $logFile; 
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND); 
}

$pdo = getDatabase();
if (!$pdo) {
    echo json_encode([
        'success' => false,
        'error' => 'Error conexión BD'
    ]);
    exit;
}


// CONFIGURACIÓN WHATSAPP
$WHATSAPP_TOKEN = 'EAA9SPy8AxVcBQUdrqpYlWZAVGlItEwWvePNshGPh1Gkj4vETo0YjaRuR7ruZBRHjcltKyfyUpykZAOYRLMCxuqF2aZCf9Ac9dWAH4uXb0qpVGxtcYvTyMe1KUOtRNSsEGZAa0njybcZA71ZBIuD9W4j05nraBMUrWiXz5ZCpOHUlulpAoMZAR8HDhwE08OLad41mKVyumb8Jp7kGuTNEcvOJifcOiPNgeMZBXSOJK4sfIKES9H7h3tv86X27UuU6klSLf9CGsizwZCejZAiFccaRTtDZAn3MTqxvPrlKsIwZDZD';
$WHATSAPP_PHONE = '925480580639940';

// Liberar recursos finalizados
function liberarRecursosFinalizados($pdo) {
    $pdo->query("UPDATE guias SET estado = 'activo', disponible = 1 WHERE estado IN ('asignado', 'en_tour') AND id NOT IN (SELECT DISTINCT guia_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE guia_id IS NOT NULL AND r.fecha_inicio >= CURDATE() AND r.estado = 'confirmada')");
    $pdo->query("UPDATE buses SET estado = 'activo', disponible = 1 WHERE estado IN ('asignado', 'en_tour') AND id NOT IN (SELECT DISTINCT bus_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE bus_id IS NOT NULL AND r.fecha_inicio >= CURDATE() AND r.estado = 'confirmada')");
}

liberarRecursosFinalizados($pdo);

// Obtener teléfono asesor
function obtenerTelefonoAsesor($pdo) {
    try {
        $stmt = $pdo->query("SELECT telefono FROM asesores WHERE disponible = 1 ORDER BY id LIMIT 1");
        $asesor = $stmt->fetch();
        return $asesor ? $asesor['telefono'] : null;
    } catch (Exception $e) { return null; }
}

// Enviar WhatsApp
function enviarWhatsApp($telefono, $mensaje, $botones = []) {
    global $WHATSAPP_TOKEN, $WHATSAPP_PHONE;
    if (!$telefono) { logAPI("❌ No hay teléfono destino"); return false; }
    
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    if (strlen($telefono) == 10) $telefono = '57' . $telefono;
    
    logAPI("📤 WhatsApp a: $telefono");
    
    if (!empty($botones)) {
        $btns = [];
        foreach (array_slice($botones, 0, 3) as $b) {
            $btns[] = ['type' => 'reply', 'reply' => ['id' => $b['id'], 'title' => mb_substr($b['title'], 0, 20)]];
        }
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $telefono,
            'type' => 'interactive',
            'interactive' => ['type' => 'button', 'body' => ['text' => $mensaje], 'action' => ['buttons' => $btns]]
        ];
    } else {
        $payload = ['messaging_product' => 'whatsapp', 'to' => $telefono, 'type' => 'text', 'text' => ['body' => $mensaje]];
    }
    
    $ch = curl_init("https://graph.facebook.com/v18.0/{$WHATSAPP_PHONE}/messages");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $WHATSAPP_TOKEN, 'Content-Type: application/json'],
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    logAPI($code == 200 ? "✅ Enviado OK" : "❌ Error $code: $resp");
    return $code == 200;
}

// Notificar asesor
function notificarAsesor($pdo, $reservaId) {
    $telAsesor = obtenerTelefonoAsesor($pdo);
    if (!$telAsesor) { logAPI("❌ No hay asesor disponible"); return false; }
    
    $stmt = $pdo->prepare("SELECT r.*, t.nombre as tour, c.nombre as cliente, c.telefono as cliente_tel FROM reservas r LEFT JOIN tours t ON r.tour_id = t.id LEFT JOIN clientes c ON r.cliente_id = c.id WHERE r.id = ?");
    $stmt->execute([$reservaId]); $reserva = $stmt->fetch();
    if (!$reserva) return false;
    
    $msg = "🔔 *NUEVA RESERVA #$reservaId*\n\n━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🎭 *Tour:* {$reserva['tour']}\n👤 *Cliente:* {$reserva['cliente']}\n📱 *Tel:* {$reserva['cliente_tel']}\n";
    $msg .= "📅 *Fecha:* {$reserva['fecha_inicio']}\n👥 *Personas:* {$reserva['cantidad_personas']}\n";
    $msg .= "💰 *Total:* $" . number_format($reserva['precio_total'], 0, ',', '.') . "\n━━━━━━━━━━━━━━━━━━\n\n¿Confirmar venta?";
    
    return enviarWhatsApp($telAsesor, $msg, [
        ['id' => 'asesor_confirmar_' . $reservaId, 'title' => '✅ Confirmar'],
        ['id' => 'asesor_rechazar_' . $reservaId, 'title' => '❌ Rechazar']
    ]);
}

// Notificar asesor de confirmación
function notificarAsesorConfirmacion($pdo, $reservaId) {
    logAPI("=== NOTIFICANDO ASESOR - CONFIRMACIÓN RESERVA #$reservaId ===");
    
    // Obtener datos de la reserva
    $stmt = $pdo->prepare("SELECT r.*, t.nombre as tour, c.nombre as cliente, c.telefono as cliente_tel, a.id as asesor_id, a.nombre as asesor_nombre, a.telefono as asesor_tel FROM reservas r LEFT JOIN tours t ON r.tour_id = t.id LEFT JOIN clientes c ON r.cliente_id = c.id LEFT JOIN asesores a ON r.asesor_id = a.id WHERE r.id = ?");
    $stmt->execute([$reservaId]); 
    $reserva = $stmt->fetch();
    if (!$reserva) { logAPI("❌ Reserva no encontrada"); return false; }
    
    // Si no hay asesor asignado, obtener uno disponible
    $asesorTel = $reserva['asesor_tel'];
    $asesorNombre = $reserva['asesor_nombre'];
    
    if (!$asesorTel) {
        $stmt = $pdo->query("SELECT id, nombre, telefono FROM asesores WHERE disponible = 1 ORDER BY id LIMIT 1");
        $asesor = $stmt->fetch();
        if (!$asesor) { logAPI("❌ No hay asesor disponible"); return false; }
        $asesorTel = $asesor['telefono'];
        $asesorNombre = $asesor['nombre'];
    }
    
    // Construir mensaje
    $msg = "✅ *RESERVA CONFIRMADA*\n\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "📌 *Referencia:* #$reservaId\n";
    $msg .= "🎭 *Tour:* {$reserva['tour']}\n";
    $msg .= "👤 *Cliente:* {$reserva['cliente']}\n";
    $msg .= "📱 *Teléfono:* {$reserva['cliente_tel']}\n";
    $msg .= "📅 *Fecha:* {$reserva['fecha_inicio']}\n";
    $msg .= "👥 *Personas:* {$reserva['cantidad_personas']}\n";
    $msg .= "💰 *Total:* \${$reserva['precio_total']}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $msg .= "ℹ️ *Esta reserva ya está confirmada.*\n";
    $msg .= "⚠️ *NO necesita confirmación adicional.*\n";
    $msg .= "✓ *Los guías y buses ya fueron asignados.*\n";
    $msg .= "📍 *Próximos pasos: Esperar confirmación de guía y bus.*";
    
    $ok = enviarWhatsApp($asesorTel, $msg);
    
    if ($ok) {
        // Registrar notificación en BD
        try {
            $pdo->prepare("UPDATE reservas SET asesor_notificado_confirmacion = 1, fecha_notificacion_confirmacion = NOW() WHERE id = ?")
                ->execute([$reservaId]);
            logAPI("✅ Notificación registrada - Asesor: $asesorNombre");
        } catch (Exception $e) {
            logAPI("⚠️ No se pudo registrar notificación: " . $e->getMessage());
        }
    }
    
    return $ok;
}

// Asignar recursos
function asignarRecursos($pdo, $reservaId) {
    logAPI("=== ASIGNANDO RECURSOS RESERVA #$reservaId ===");
    
    $stmt = $pdo->prepare("SELECT r.*, t.nombre as tour, t.destino, c.nombre as cliente, c.telefono as cliente_tel FROM reservas r LEFT JOIN tours t ON r.tour_id = t.id LEFT JOIN clientes c ON r.cliente_id = c.id WHERE r.id = ?");
    $stmt->execute([$reservaId]); $reserva = $stmt->fetch();
    if (!$reserva) return ['success' => false, 'error' => 'Reserva no encontrada'];
    
    $tourId = $reserva['tour_id']; $fechaTour = $reserva['fecha_inicio'];
    
    // Verificar asignación existente
    $stmt = $pdo->prepare("SELECT a.guia_id, a.bus_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE r.tour_id = ? AND r.fecha_inicio = ? AND r.estado = 'confirmada' LIMIT 1");
    $stmt->execute([$tourId, $fechaTour]); $existente = $stmt->fetch();
    
    if ($existente) {
        logAPI("🔄 Vinculando a asignación existente");
        $pdo->prepare("INSERT INTO asignaciones (reserva_id, guia_id, bus_id, fecha_asignacion, guia_confirmado, bus_confirmado) VALUES (?,?,?,NOW(),1,1)")
            ->execute([$reservaId, $existente['guia_id'], $existente['bus_id']]);
        return ['success' => true, 'modo' => 'existente'];
    }
    
    // Buscar guía disponible
    $stmt = $pdo->prepare("SELECT * FROM guias WHERE disponible = 1 AND estado = 'activo' AND id NOT IN (SELECT DISTINCT a.guia_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE a.guia_id IS NOT NULL AND r.fecha_inicio = ? AND r.estado = 'confirmada') ORDER BY calificacion DESC LIMIT 1");
    $stmt->execute([$fechaTour]); $guia = $stmt->fetch();
    
    // Buscar bus disponible
    $stmt = $pdo->prepare("SELECT * FROM buses WHERE disponible = 1 AND estado = 'activo' AND id NOT IN (SELECT DISTINCT a.bus_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE a.bus_id IS NOT NULL AND r.fecha_inicio = ? AND r.estado = 'confirmada') ORDER BY id LIMIT 1");
    $stmt->execute([$fechaTour]); $bus = $stmt->fetch();
    
    $guiaId = $guia ? $guia['id'] : null; $busId = $bus ? $bus['id'] : null;
    
    // Crear asignación
    $pdo->prepare("INSERT INTO asignaciones (reserva_id, guia_id, bus_id, fecha_asignacion, guia_confirmado, bus_confirmado) VALUES (?,?,?,NOW(),0,0)")
        ->execute([$reservaId, $guiaId, $busId]);
    $asigId = $pdo->lastInsertId();
    
    if ($guiaId) $pdo->prepare("UPDATE guias SET estado = 'asignado', disponible = 0 WHERE id = ?")->execute([$guiaId]);
    if ($busId) $pdo->prepare("UPDATE buses SET estado = 'asignado', disponible = 0 WHERE id = ?")->execute([$busId]);
    
    $notificaciones = [];
    
    // Notificar guía
    if ($guia && $guia['telefono']) {
        $m = "🎯 *NUEVA ASIGNACIÓN*\n\nHola *{$guia['nombre']}*!\n\n🎭 {$reserva['tour']}\n📅 {$fechaTour}\n👥 {$reserva['cantidad_personas']} pax\n👤 {$reserva['cliente']}\n📱 {$reserva['cliente_tel']}\n\n¿Confirmas?";
        $ok = enviarWhatsApp($guia['telefono'], $m, [['id' => 'confirmar_guia_' . $asigId, 'title' => '✅ Confirmar'], ['id' => 'rechazar_guia_' . $asigId, 'title' => '❌ No puedo']]);
        $notificaciones[] = "Guía: " . ($ok ? "✅" : "❌");
    }
    
    // Notificar bus
    if ($bus && $bus['telefono']) {
        $m = "🚌 *NUEVA ASIGNACIÓN*\n\nHola *{$bus['nombre_busero']}*!\n\n🎭 {$reserva['tour']}\n📅 {$fechaTour}\n👥 {$reserva['cantidad_personas']} pax\n🚐 {$bus['placa']}\n\n¿Confirmas?";
        $ok = enviarWhatsApp($bus['telefono'], $m, [['id' => 'confirmar_bus_' . $asigId, 'title' => '✅ Confirmar'], ['id' => 'rechazar_bus_' . $asigId, 'title' => '❌ No puedo']]);
        $notificaciones[] = "Bus: " . ($ok ? "✅" : "❌");
    }
    
    return ['success' => true, 'notificaciones' => $notificaciones];
}

// ========== PROCESAR ACCIONES ==========
try {
    logAPI(">>> Action: $action");
    
    switch ($action) {
        // RESERVAS
        case 'enviar-a-asesor':
            $id = $data['id'] ?? 0;
            if (!$id) { echo json_encode(['success' => false, 'error' => 'ID requerido']); break; }
            $pdo->prepare("UPDATE reservas SET estado = 'pendiente_asesor' WHERE id = ?")->execute([$id]);
            $ok = notificarAsesor($pdo, $id);
            echo json_encode(['success' => true, 'notificado' => $ok]);
            break;
        
        case 'confirmar-venta':
            $id = $data['id'] ?? 0;
            if (!$id) { echo json_encode(['success' => false, 'error' => 'ID requerido']); break; }
            $pdo->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?")->execute([$id]);
            $result = asignarRecursos($pdo, $id);
            $notificacionAsesor = notificarAsesorConfirmacion($pdo, $id);
            echo json_encode(['success' => true, 'asignacion' => $result, 'notificacion_asesor' => $notificacionAsesor]);
            break;
        
        case 'cancelar-reserva':
            $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ?")->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        case 'delete-reservation':
            $id = $data['id'] ?? 0;
            $stmt = $pdo->prepare("SELECT guia_id, bus_id FROM asignaciones WHERE reserva_id = ?"); $stmt->execute([$id]); $asig = $stmt->fetch();
            if ($asig) {
                if ($asig['guia_id']) $pdo->prepare("UPDATE guias SET estado = 'activo', disponible = 1 WHERE id = ?")->execute([$asig['guia_id']]);
                if ($asig['bus_id']) $pdo->prepare("UPDATE buses SET estado = 'activo', disponible = 1 WHERE id = ?")->execute([$asig['bus_id']]);
            }
            $pdo->prepare("DELETE FROM asignaciones WHERE reserva_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM reservas WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        // TOURS
        case 'toggle-tour':
            $pdo->prepare("UPDATE tours SET activo = ? WHERE id = ?")->execute([$data['activo'], $data['id']]);
            echo json_encode(['success' => true]);
            break;

        case 'create-tour':
            $pdo->prepare("INSERT INTO tours (nombre, destino, precio, duracion_dias, capacidad_maxima, descripcion, activo) VALUES (?,?,?,?,?,?,1)")
                ->execute([$data['nombre'], $data['destino'] ?? '', $data['precio'], $data['duracion_dias'] ?? 1, $data['capacidad_maxima'] ?? 40, $data['descripcion'] ?? '']);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'edit-tour':
            $sets = []; $vals = [];
            foreach (['nombre', 'destino', 'precio', 'duracion_dias', 'capacidad_maxima', 'descripcion', 'activo'] as $c) {
                if (isset($data[$c])) { $sets[] = "$c=?"; $vals[] = $data[$c]; }
            }
            if ($sets) { $vals[] = $data['id']; $pdo->prepare("UPDATE tours SET " . implode(',', $sets) . " WHERE id=?")->execute($vals); }
            echo json_encode(['success' => true]);
            break;

        case 'delete-tour':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE tour_id = ? AND estado NOT IN ('cancelada')"); $stmt->execute([$data['id']]);
            if ($stmt->fetchColumn() > 0) { echo json_encode(['success' => false, 'error' => 'Tour tiene reservas activas']); }
            else { $pdo->prepare("DELETE FROM tours WHERE id = ?")->execute([$data['id']]); echo json_encode(['success' => true]); }
            break;

        // GUÍAS
        case 'create-guide':
            $pdo->prepare("INSERT INTO guias (nombre, telefono, idiomas, experiencia, calificacion, estado, disponible) VALUES (?,?,?,?,?,'activo',1)")
                ->execute([$data['nombre'], $data['telefono'], $data['idiomas'] ?? 'Español', $data['experiencia'] ?? 1, $data['calificacion'] ?? 5]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'edit-guide':
            $sets = []; $vals = [];
            foreach (['nombre', 'telefono', 'idiomas', 'experiencia', 'calificacion', 'estado', 'disponible'] as $c) {
                if (isset($data[$c])) { $sets[] = "$c=?"; $vals[] = $data[$c]; }
            }
            if (isset($data['disponible']) && $data['disponible'] == 1) { $sets[] = "estado=?"; $vals[] = 'activo'; }
            if ($sets) { $vals[] = $data['id']; $pdo->prepare("UPDATE guias SET " . implode(',', $sets) . " WHERE id=?")->execute($vals); }
            echo json_encode(['success' => true]);
            break;

        case 'delete-guide':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM asignaciones a JOIN reservas r ON a.reserva_id=r.id WHERE a.guia_id=? AND r.estado='confirmada' AND r.fecha_inicio >= CURDATE()");
            $stmt->execute([$data['id']]);
            if ($stmt->fetchColumn() > 0) { echo json_encode(['success' => false, 'error' => 'Guía tiene asignaciones activas']); }
            else { $pdo->prepare("DELETE FROM guias WHERE id=?")->execute([$data['id']]); echo json_encode(['success' => true]); }
            break;
        
        case 'liberar-guia':
            $pdo->prepare("UPDATE guias SET estado = 'activo', disponible = 1 WHERE id = ?")->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        // BUSES
        case 'create-bus':
            $pdo->prepare("INSERT INTO buses (nombre_busero, placa, telefono, marca, modelo, capacidad, estado, disponible) VALUES (?,?,?,?,?,?,'activo',1)")
                ->execute([$data['nombre_busero'], $data['placa'], $data['telefono'], $data['marca'] ?? '', $data['modelo'] ?? '', $data['capacidad'] ?? 40]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'edit-bus':
            $sets = []; $vals = [];
            foreach (['nombre_busero', 'placa', 'telefono', 'marca', 'modelo', 'capacidad', 'estado', 'disponible'] as $c) {
                if (isset($data[$c])) { $sets[] = "$c=?"; $vals[] = $data[$c]; }
            }
            if (isset($data['disponible']) && $data['disponible'] == 1) { $sets[] = "estado=?"; $vals[] = 'activo'; }
            if ($sets) { $vals[] = $data['id']; $pdo->prepare("UPDATE buses SET " . implode(',', $sets) . " WHERE id=?")->execute($vals); }
            echo json_encode(['success' => true]);
            break;

        case 'delete-bus':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM asignaciones a JOIN reservas r ON a.reserva_id=r.id WHERE a.bus_id=? AND r.estado='confirmada' AND r.fecha_inicio >= CURDATE()");
            $stmt->execute([$data['id']]);
            if ($stmt->fetchColumn() > 0) { echo json_encode(['success' => false, 'error' => 'Bus tiene asignaciones activas']); }
            else { $pdo->prepare("DELETE FROM buses WHERE id=?")->execute([$data['id']]); echo json_encode(['success' => true]); }
            break;
        
        case 'liberar-bus':
            $pdo->prepare("UPDATE buses SET estado = 'activo', disponible = 1 WHERE id = ?")->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        // ASESORES
        case 'create-asesor':
            $pdo->prepare("INSERT INTO asesores (nombre, telefono, email, especialidad, disponible) VALUES (?,?,?,?,1)")
                ->execute([$data['nombre'], $data['telefono'], $data['email'], $data['especialidad'] ?? 'Ventas']);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'edit-asesor':
            $sets = []; $vals = [];
            foreach (['nombre', 'telefono', 'email', 'especialidad', 'disponible'] as $c) {
                if (isset($data[$c])) { $sets[] = "$c=?"; $vals[] = $data[$c]; }
            }
            if ($sets) { $vals[] = $data['id']; $pdo->prepare("UPDATE asesores SET " . implode(',', $sets) . " WHERE id=?")->execute($vals); }
            echo json_encode(['success' => true]);
            break;

        case 'delete-asesor':
            $pdo->prepare("DELETE FROM asesores WHERE id=?")->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        // ==================== FAQs ====================
        case 'create-faq':
            $pdo->prepare("INSERT INTO faqs (categoria, pregunta, palabras_clave, respuesta, respuesta_corta, activo) VALUES (?,?,?,?,?,1)")
                ->execute([$data['categoria'] ?? 'general', $data['pregunta'], $data['palabras_clave'], $data['respuesta'], $data['respuesta_corta']]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'edit-faq':
            $sets = []; $vals = [];
            foreach (['categoria', 'pregunta', 'palabras_clave', 'respuesta', 'respuesta_corta', 'activo'] as $c) {
                if (isset($data[$c])) { $sets[] = "$c=?"; $vals[] = $data[$c]; }
            }
            if ($sets) { $vals[] = $data['id']; $pdo->prepare("UPDATE faqs SET " . implode(',', $sets) . " WHERE id=?")->execute($vals); }
            echo json_encode(['success' => true]);
            break;

        case 'toggle-faq':
            $pdo->prepare("UPDATE faqs SET activo = ? WHERE id = ?")->execute([$data['activo'], $data['id']]);
            echo json_encode(['success' => true]);
            break;

        case 'delete-faq':
            $pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        case '':
            echo json_encode(['success' => false, 'error' => 'Action vacío']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => "Acción no reconocida: $action"]);
    }
    
} catch (Exception $e) {
    logAPI("❌ ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
