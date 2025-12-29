<?php
/**
 * SERCOLTUR WhatsApp Bot - VERSIÓN EMPRESARIAL
 * Con Horarios de Atención y FAQs integrados
 * Sincronizado con Dashboard
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);


$VERIFY_TOKEN = 'SERCOLTUR2025';
$ACCESS_TOKEN = 'EAA9SPy8AxVcBQTrAzAKrjSCv3vfmlycXhXkbmwjvHunpHiCTi2dkHm2uwYQiIUZBmdhX0kaNnUD4NURHOdesvXdaVqpZACKoXDvzPbQZC4aI9rsRlrRmRIa98Yru3FuZChjxDSNSB5f3r2MP3qorVvSH4HaRYRbVsWMnB5l4BWCaZCUZBp878cKKFpa5QSGMngRpoqqcmQ0MOtzxgXEKZAgyJeIpRSUDSU3AWOrwDjwx3clmWJ2La5PwHd0aAZALBsqem4W1nsP87PZCuzGzZADaGt3sQlBGxggZAfj4CMz';
$PHONE_ID = '925480580639940';
$SESSIONS_DIR = __DIR__ . '/sessions';
if (!is_dir($SESSIONS_DIR)) mkdir($SESSIONS_DIR, 0755, true);




// ═══════════════════════════════════════════════════════════════
// DETALLES DE TOURS
// ═══════════════════════════════════════════════════════════════
$TOUR_DETALLES = [
    'Tour a Guatapé' => "🪨 *Tour a Guatapé*\n💰 *\$109.000*\n\n📍 *Salida:* Parque del Poblado – Estación Estadio\n🕐 *Hora:* 7:00am Poblado | 7:20am Estadio\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Desayuno y Almuerzo\n• Pasaporte BARCO RUMBERO\n• Guarne y Marinilla (Panorámico)\n• Visita Piedra del Peñol\n• Paseo en barco por la represa\n• Municipio de Guatapé\n• Alto del chocho\n• La casa al revés\n• Guía y asistencia médica\n\n❌ *NO INCLUYE:*\n• Ingreso a la Piedra (Opcional)",
    'Tour Navideño' => "🎄 *Tour Navideño*\n💰 *\$65.000*\n\n📍 *Salida:* Estación Estadio (Salidas diarias)\n🕐 *Disponible hasta:* 12 enero 2026\n🎁 *10% DESCUENTO* grupos desde 10 personas\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Degustación de licor (mayores de edad)\n• Música y ambiente familiar\n• Guía acompañante\n• Panorámico por la ciudad\n• Caminata Parques del Río\n• Recorrido alumbrados\n• Asistencia médica",
    'City Tour Comuna 13' => "🎨 *City Tour Comuna 13*\n💰 *\$99.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 8:00am – 8:30am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Almuerzo\n• Parque del Poblado, Pueblito Paisa\n• Pies Descalzos, Plaza Botero\n• Parques del Río\n• Ingreso Metro y Metro Cable\n• Graffitis Comuna 13\n• Escaleras eléctricas\n• Guía bilingüe\n• Seguro de viaje",
    'Hacienda Nápoles + Santorini' => "🦛 *Hacienda Nápoles + Santorini*\n\n📍 *Salida:* Parque del Poblado o Estación Estadio\n🕐 *Hora:* 4:00am o 4:30am\n\n🎫 *PASAPORTE BÁSICO - \$228.000*\n• Transporte y Desayuno\n• Plaza Santorini\n• Lago de los hipopótamos\n• Amazon Safari\n• Gran Sabana Africana\n• Museo Memorial y Africano\n• Kamaria, Aventura Jurásica\n• Mariposario, Aves, Reptiles\n\n🦁 *PASAPORTE SAFARI - \$269.000*\n• Todo lo del Básico MÁS:\n• Río Salvaje\n• Cataratas Victoria\n• Cobras\n\n❌ *NO INCLUYE:* Almuerzo, Piscinas",
    'Paquete Vibrante' => "✨ *Paquete Vibrante - Medellín y Guatapé*\n💰 *\$195.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 8:30am – 9:00am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Desayuno y Almuerzo\n• Guía acompañante\n• Tarjeta asistencia médica",
    'Chiva Rumbera' => "🎉 *Chiva Rumbera*\n💰 *\$65.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 7:00pm – 7:30pm\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Cerveza, agua, gaseosa\n• Recorrido: Av 70, Puente 4 Sur\n• Parque El Poblado, Parque Lleras\n• Provenza, Milla de Oro, Av 33\n• Asistencia médica",
    'City Tour Medellín' => "🏙️ *City Tour Medellín*\n💰 *\$65.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 8:30am – 9:00am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Parque del Poblado\n• Plaza Botero\n• Parque de los Deseos\n• Parque Pies Descalzos\n• Pueblito Paisa (Cerro Nutibara)\n• Guía y asistencia médica\n\n❌ *NO INCLUYE:*\n• Ingreso Metro y Metro Cable",
    'Solo Comuna 13' => "🎨 *Solo Comuna 13*\n💰 *\$70.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 8:00am – 8:30am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Ingreso Metro y Metro Cable\n• Graffitis Comuna 13\n• Escaleras eléctricas\n• Guía bilingüe\n• Asistencia médica",
    'Tour a Jardín Antioquia' => "🌸 *Tour a Jardín Antioquia*\n💰 *\$130.000*\n\n📍 *Salida:* Parque del Poblado – Estación Estadio\n🕐 *Hora:* 5:00am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Desayuno, Almuerzo y Refrigerio\n• Municipio de Hispania\n• Andes (Panorámico)\n• Municipio de Jardín\n• Basílica Inmaculada Concepción\n• Casa de los dulces\n• Café las Macanas\n• Recorrido en Chiva 15-20 min\n• Guía y asistencia médica",
    'Tour a Río Claro' => "💧 *Tour a Río Claro*\n💰 *\$220.000*\n\n📍 *Salida:* Parque del Poblado – Estación Estadio\n🕐 *Hora:* 4:00am o 4:30am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Desayuno y Almuerzo\n• Rafting\n• Body Rafting\n• Hidro Senderismo\n• Espeleología Caverna del Cóndor\n• Guía local\n• Asistencia médica",
    'Tour a Santa Fe de Antioquia' => "🏛️ *Tour a Santa Fe de Antioquia*\n💰 *\$120.000*\n\n📍 *Salida:* Parque del Poblado – Estación Estadio\n🕐 *Hora:* 8:00am – 8:30am (7 horas)\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Almuerzo\n• Túnel y Puente de Occidente\n• Plazuela de Santa Bárbara\n• Parque Principal y Catedral\n• Museo Juan del Corral\n• Artesanías y dulces\n• Guía y asistencia médica",
    'Tour de Café' => "☕ *Tour de Café*\n💰 *\$220.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 8:00am – 8:30am\n\n✅ *INCLUYE:*\n• Transporte IDA y REGRESO\n• Almuerzo tipo fiambre paisa\n• Kit café blanqueado y panela\n• Bebida de mucílago\n• Plátano calado con miel y quesito\n• Degustación café especial\n• Catación cafés tipo miel\n• Guía y asistencia médica",
    'Tour del Parapente' => "🪂 *Tour del Parapente*\n💰 *\$350.000*\n\n📍 *Salida:* Estación Estadio – Parque del Poblado\n🕐 *Hora:* 9:00am a 4:30pm\n\n✅ *INCLUYE:*\n• Transporte\n• Hidratación de bienvenida\n• Fotos y videos HD (GoPro)\n• Equipo de seguridad homologado\n• Piloto tándem con licencia\n• Derecho de pista\n• Vuelo 15-20 minutos\n• Asistencia médica"
];

// ═══════════════════════════════════════════════════════════════
// FUNCIONES AUXILIARES
// ═══════════════════════════════════════════════════════════════

function getDatabase() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4",
                "root",
                "C121672@c",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) { 
            return null; 
        }
    }
    return $pdo;
}


function logBot($msg) { file_put_contents(__DIR__ . '/whatsapp_log.txt', "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND); }

// ═══════════════════════════════════════════════════════════════
// FUNCIÓN: VERIFICAR HORARIO DE ATENCIÓN
// ═══════════════════════════════════════════════════════════════
function dentroDeHorario() {
    global $HORARIOS;
    
    if (!$HORARIOS['habilitado']) return true;
    
    date_default_timezone_set($HORARIOS['zona_horaria']);
    $diaSemana = (int)date('w');
    $horaActual = date('H:i');
    
    $diaConfig = $HORARIOS['dias'][$diaSemana] ?? null;
    if (!$diaConfig || !$diaConfig['activo']) return false;
    
    return $horaActual >= $diaConfig['inicio'] && $horaActual <= $diaConfig['fin'];
}

function getMensajeFueraHorario() {
    global $HORARIOS;
    return $HORARIOS['mensaje_fuera_horario'];
}

function generarMensajeHorarios() {
    global $HORARIOS;
    $dias = $HORARIOS['dias'];
    $diasNombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    
    $msg = "📅 *HORARIOS DE ATENCIÓN*\n\n";
    foreach ($dias as $num => $dia) {
        if ($dia['activo']) {
            $msg .= "• {$diasNombres[$num]}: {$dia['inicio']} - {$dia['fin']}\n";
        } else {
            $msg .= "• {$diasNombres[$num]}: Cerrado\n";
        }
    }
    $msg .= "\n🚌 *SERCOLTUR*";
    return $msg;
}

// ═══════════════════════════════════════════════════════════════
// FUNCIÓN: BUSCAR EN FAQs
// ═══════════════════════════════════════════════════════════════
function buscarFAQ($mensaje) {
    $pdo = getDatabase();
    if (!$pdo) return null;
    
    try {
        // Verificar si existe la tabla faqs
        $check = $pdo->query("SHOW TABLES LIKE 'faqs'");
        if ($check->rowCount() == 0) return null;
        
        $stmt = $pdo->query("SELECT * FROM faqs WHERE activo = 1");
        while ($row = $stmt->fetch()) {
            $keywords = json_decode($row['palabras_clave'] ?? '[]', true) ?: [];
            foreach ($keywords as $kw) {
                if (stripos($mensaje, $kw) !== false) {
                    // Incrementar contador
                    $pdo->prepare("UPDATE faqs SET veces_consultada = veces_consultada + 1 WHERE id = ?")->execute([$row['id']]);
                    return $row;
                }
            }
        }
    } catch (Exception $e) {
        logBot("Error FAQs: " . $e->getMessage());
    }
    
    return null;
}

function obtenerTelefonoAsesor() {
    $pdo = getDatabase(); if (!$pdo) return null;
    $stmt = $pdo->query("SELECT telefono FROM asesores WHERE disponible = 1 ORDER BY id LIMIT 1");
    $a = $stmt->fetch();
    return $a ? $a['telefono'] : null;
}

function guardarConversacion($tel, $msg, $resp, $tipo = 'general') {
    $pdo = getDatabase(); if (!$pdo) return;
    $tel = preg_replace('/[^0-9]/', '', $tel);
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE telefono = ?"); $stmt->execute([$tel]); $c = $stmt->fetch();
    if (!$c) { $pdo->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)")->execute(['Cliente ' . substr($tel, -4), $tel]); $cid = $pdo->lastInsertId(); } 
    else { $cid = $c['id']; }
    $pdo->prepare("INSERT INTO bot_conversaciones (cliente_id, mensaje_cliente, respuesta_bot, tipo_consulta) VALUES (?,?,?,?)")->execute([$cid, $msg, $resp, $tipo]);
}

function liberarRecursosFinalizados() {
    $pdo = getDatabase(); if (!$pdo) return;
    $pdo->query("UPDATE guias SET estado = 'activo', disponible = 1 WHERE estado IN ('asignado', 'en_tour') AND id NOT IN (SELECT DISTINCT guia_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE guia_id IS NOT NULL AND r.fecha_inicio >= CURDATE() AND r.estado = 'confirmada')");
    $pdo->query("UPDATE buses SET estado = 'activo', disponible = 1 WHERE estado IN ('asignado', 'en_tour') AND id NOT IN (SELECT DISTINCT bus_id FROM asignaciones a INNER JOIN reservas r ON a.reserva_id = r.id WHERE bus_id IS NOT NULL AND r.fecha_inicio >= CURDATE() AND r.estado = 'confirmada')");
}

// ═══════════════════════════════════════════════════════════════
// WEBHOOK - GET (Verificación)
// ═══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['hub_verify_token'] ?? ''; $challenge = $_GET['hub_challenge'] ?? '';
    if (empty($token) && empty($challenge)) { echo "<h1>🚌 SERCOLTUR Bot Empresarial OK</h1><p>Horarios y FAQs activos</p>"; exit; }
    if ($token === $VERIFY_TOKEN) { echo $challenge; exit; }
    http_response_code(403); exit;
}

// ═══════════════════════════════════════════════════════════════
// WEBHOOK - POST (Mensajes)
// ═══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(200);
    liberarRecursosFinalizados();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data && isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
        $msg = $data['entry'][0]['changes'][0]['value']['messages'][0];
        $phone = $msg['from'];
        $name = $data['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? '';
        
        // ═══ VERIFICAR HORARIO ═══
        if (!dentroDeHorario()) {
            logBot("⏰ Mensaje fuera de horario de: $phone");
            enviarTexto($phone, getMensajeFueraHorario());
            guardarConversacion($phone, $msg['text']['body'] ?? 'mensaje', 'fuera_horario', 'fuera_horario');
            exit;
        }
        
        // Procesar texto
        if (isset($msg['text']['body'])) {
            $texto = trim($msg['text']['body']);
            $lower = strtolower($texto);
            
            // ═══ COMANDO HORARIOS ═══
            if (in_array($lower, ['horario', 'horarios', 'hora', 'horas'])) {
                enviarTexto($phone, generarMensajeHorarios());
                guardarConversacion($phone, $texto, 'horarios', 'horarios');
                exit;
            }
            
            // ═══ BUSCAR EN FAQs ═══
            $faq = buscarFAQ($lower);
            if ($faq) {
                $respuesta = $faq['respuesta_corta'] ?: $faq['respuesta'];
                enviarTexto($phone, $respuesta);
                guardarConversacion($phone, $texto, $respuesta, 'faq');
                logBot("FAQ encontrada: {$faq['pregunta']}");
                exit;
            }
            
            // Procesar mensaje normal
            procesarMensaje($phone, $texto, $name);
        }
        
        // Procesar botones
        if (isset($msg['interactive']['button_reply']['id'])) {
            procesarBoton($phone, $msg['interactive']['button_reply']['id']);
        }
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════
// FUNCIONES DE ASIGNACIÓN
// ═══════════════════════════════════════════════════════════════

function asignarRecursosDesdeBot($pdo, $reservaId) {
    logBot("=== ASIGNANDO RECURSOS RESERVA #$reservaId ===");
    $stmt = $pdo->prepare("SELECT r.*, t.nombre as tour, t.destino, c.nombre as cliente, c.telefono as cliente_tel FROM reservas r LEFT JOIN tours t ON r.tour_id = t.id LEFT JOIN clientes c ON r.cliente_id = c.id WHERE r.id = ?");
    $stmt->execute([$reservaId]); $res = $stmt->fetch(); 
    if (!$res) { logBot("ERROR: Reserva no encontrada"); return; }
    
    $fecha = $res['fecha_inicio']; $tourId = $res['tour_id'];
    
    $stmt = $pdo->prepare("SELECT guia_id, bus_id FROM asignaciones a JOIN reservas r ON a.reserva_id = r.id WHERE r.tour_id = ? AND r.fecha_inicio = ? AND r.estado = 'confirmada' LIMIT 1");
    $stmt->execute([$tourId, $fecha]); $ex = $stmt->fetch();
    if ($ex) { 
        logBot("Vinculando a asignación existente");
        $pdo->prepare("INSERT INTO asignaciones (reserva_id, guia_id, bus_id, fecha_asignacion, guia_confirmado, bus_confirmado) VALUES (?,?,?,NOW(),1,1)")->execute([$reservaId, $ex['guia_id'], $ex['bus_id']]); 
        return; 
    }
    
    $stmt = $pdo->prepare("SELECT * FROM guias WHERE disponible = 1 AND estado = 'activo' AND id NOT IN (SELECT DISTINCT guia_id FROM asignaciones a JOIN reservas r ON a.reserva_id = r.id WHERE guia_id IS NOT NULL AND r.fecha_inicio = ? AND r.estado = 'confirmada') ORDER BY calificacion DESC LIMIT 1");
    $stmt->execute([$fecha]); $guia = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT * FROM buses WHERE disponible = 1 AND estado = 'activo' AND id NOT IN (SELECT DISTINCT bus_id FROM asignaciones a JOIN reservas r ON a.reserva_id = r.id WHERE bus_id IS NOT NULL AND r.fecha_inicio = ? AND r.estado = 'confirmada') ORDER BY id LIMIT 1");
    $stmt->execute([$fecha]); $bus = $stmt->fetch();
    
    $gid = $guia ? $guia['id'] : null; $bid = $bus ? $bus['id'] : null;
    logBot("Guía: " . ($guia ? $guia['nombre'] : 'NINGUNO') . " | Bus: " . ($bus ? $bus['placa'] : 'NINGUNO'));
    
    $pdo->prepare("INSERT INTO asignaciones (reserva_id, guia_id, bus_id, fecha_asignacion, guia_confirmado, bus_confirmado) VALUES (?,?,?,NOW(),0,0)")->execute([$reservaId, $gid, $bid]);
    $asigId = $pdo->lastInsertId();
    
    if ($gid) $pdo->prepare("UPDATE guias SET estado = 'asignado', disponible = 0 WHERE id = ?")->execute([$gid]);
    if ($bid) $pdo->prepare("UPDATE buses SET estado = 'asignado', disponible = 0 WHERE id = ?")->execute([$bid]);
    
    if ($guia && $guia['telefono']) {
        $m = "🎯 *NUEVA ASIGNACIÓN DE TOUR*\n\nHola *{$guia['nombre']}*! 👋\n\n";
        $m .= "━━━━━━━━━━━━━━━━━━\n📋 *DETALLES*\n━━━━━━━━━━━━━━━━━━\n\n";
        $m .= "🎭 *Tour:* {$res['tour']}\n📍 *Destino:* {$res['destino']}\n📅 *Fecha:* {$fecha}\n";
        $m .= "👥 *Pasajeros:* {$res['cantidad_personas']} pax\n💰 *Valor:* $" . number_format($res['precio_total'], 0, ',', '.') . "\n\n";
        $m .= "━━━━━━━━━━━━━━━━━━\n👤 *CLIENTE*\n━━━━━━━━━━━━━━━━━━\n\n";
        $m .= "👤 {$res['cliente']}\n📱 {$res['cliente_tel']}\n\n";
        if ($bus) { $m .= "━━━━━━━━━━━━━━━━━━\n🚌 *TRANSPORTE*\n━━━━━━━━━━━━━━━━━━\n\n🚐 {$bus['placa']} - {$bus['nombre_busero']}\n📱 {$bus['telefono']}\n\n"; }
        $m .= "¿Confirmas tu asistencia?";
        enviarBotones($guia['telefono'], $m, [['id' => 'confirmar_guia_' . $asigId, 'title' => '✅ Confirmar'], ['id' => 'rechazar_guia_' . $asigId, 'title' => '❌ No puedo']]);
        logBot("📤 Notificación a guía: {$guia['nombre']}");
    }
    
    if ($bus && $bus['telefono']) {
        $m = "🚌 *NUEVA ASIGNACIÓN*\n\nHola *{$bus['nombre_busero']}*! 👋\n\n";
        $m .= "🎭 *Tour:* {$res['tour']}\n📅 *Fecha:* {$fecha}\n👥 *Pasajeros:* {$res['cantidad_personas']}\n🚐 *Vehículo:* {$bus['placa']}\n";
        if ($guia) $m .= "👨‍🏫 *Guía:* {$guia['nombre']}\n";
        $m .= "\n¿Confirmas?";
        enviarBotones($bus['telefono'], $m, [['id' => 'confirmar_bus_' . $asigId, 'title' => '✅ Confirmar'], ['id' => 'rechazar_bus_' . $asigId, 'title' => '❌ No puedo']]);
        logBot("📤 Notificación a bus: {$bus['nombre_busero']}");
    }
}

function procesarConfirmacion($phone, $buttonId) {
    $pdo = getDatabase(); if (!$pdo) return false;
    
    if (preg_match('/^asesor_confirmar_(\d+)$/', $buttonId, $m)) {
        $id = $m[1]; 
        logBot("✅ Asesor confirma reserva #$id");
        $pdo->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?")->execute([$id]);
        asignarRecursosDesdeBot($pdo, $id);
        $stmt = $pdo->prepare("SELECT r.*, t.nombre as tour, c.nombre as cliente FROM reservas r LEFT JOIN tours t ON r.tour_id = t.id LEFT JOIN clientes c ON r.cliente_id = c.id WHERE r.id = ?"); 
        $stmt->execute([$id]); $res = $stmt->fetch();
        enviarTexto($phone, "✅ *VENTA CONFIRMADA*\n\n📋 Reserva #{$id}\n🎭 {$res['tour']}\n👤 {$res['cliente']}\n💰 $" . number_format($res['precio_total'], 0, ',', '.') . "\n\n✅ Guía y conductor notificados.\n📊 Dashboard actualizado.");
        return true;
    }
    
    if (preg_match('/^asesor_rechazar_(\d+)$/', $buttonId, $m)) { 
        logBot("❌ Asesor rechaza reserva #{$m[1]}");
        $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ?")->execute([$m[1]]); 
        enviarTexto($phone, "❌ Reserva #{$m[1]} rechazada."); 
        return true; 
    }
    
    if (preg_match('/^confirmar_guia_(\d+)$/', $buttonId, $m)) {
        logBot("✅ Guía confirma asignación #{$m[1]}");
        $pdo->prepare("UPDATE asignaciones SET guia_confirmado = 1 WHERE id = ?")->execute([$m[1]]);
        $stmt = $pdo->prepare("SELECT g.nombre, g.id FROM asignaciones a JOIN guias g ON a.guia_id = g.id WHERE a.id = ?"); 
        $stmt->execute([$m[1]]); $info = $stmt->fetch();
        if ($info) { 
            $pdo->prepare("UPDATE guias SET estado = 'en_tour' WHERE id = ?")->execute([$info['id']]); 
            enviarTexto($phone, "✅ *¡CONFIRMADO!*\n\nGracias *{$info['nombre']}*!\n\nTu asignación ha sido registrada.\n\n🚌 *SERCOLTUR*"); 
        }
        return true;
    }
    
    if (preg_match('/^rechazar_guia_(\d+)$/', $buttonId, $m)) { 
        logBot("❌ Guía rechaza #{$m[1]}");
        $stmt = $pdo->prepare("SELECT guia_id FROM asignaciones WHERE id = ?"); $stmt->execute([$m[1]]); $asig = $stmt->fetch();
        if ($asig && $asig['guia_id']) $pdo->prepare("UPDATE guias SET estado = 'activo', disponible = 1 WHERE id = ?")->execute([$asig['guia_id']]);
        $pdo->prepare("UPDATE asignaciones SET guia_id = NULL, guia_confirmado = 0 WHERE id = ?")->execute([$m[1]]); 
        enviarTexto($phone, "❌ Entendido. Se buscará otro guía.\n\n🚌 *SERCOLTUR*"); 
        return true; 
    }
    
    if (preg_match('/^confirmar_bus_(\d+)$/', $buttonId, $m)) {
        logBot("✅ Bus confirma #{$m[1]}");
        $pdo->prepare("UPDATE asignaciones SET bus_confirmado = 1 WHERE id = ?")->execute([$m[1]]);
        $stmt = $pdo->prepare("SELECT b.nombre_busero, b.id FROM asignaciones a JOIN buses b ON a.bus_id = b.id WHERE a.id = ?"); 
        $stmt->execute([$m[1]]); $info = $stmt->fetch();
        if ($info) { 
            $pdo->prepare("UPDATE buses SET estado = 'en_tour' WHERE id = ?")->execute([$info['id']]); 
            enviarTexto($phone, "✅ *¡CONFIRMADO!*\n\nGracias *{$info['nombre_busero']}*!\n\nTu servicio ha sido registrado.\n\n🚌 *SERCOLTUR*"); 
        }
        return true;
    }
    
    if (preg_match('/^rechazar_bus_(\d+)$/', $buttonId, $m)) { 
        logBot("❌ Bus rechaza #{$m[1]}");
        $stmt = $pdo->prepare("SELECT bus_id FROM asignaciones WHERE id = ?"); $stmt->execute([$m[1]]); $asig = $stmt->fetch();
        if ($asig && $asig['bus_id']) $pdo->prepare("UPDATE buses SET estado = 'activo', disponible = 1 WHERE id = ?")->execute([$asig['bus_id']]);
        $pdo->prepare("UPDATE asignaciones SET bus_id = NULL, bus_confirmado = 0 WHERE id = ?")->execute([$m[1]]); 
        enviarTexto($phone, "❌ Entendido. Se buscará otro vehículo.\n\n🚌 *SERCOLTUR*"); 
        return true; 
    }
    
    return false;
}

// ═══════════════════════════════════════════════════════════════
// FUNCIONES DE TOURS
// ═══════════════════════════════════════════════════════════════

function esNapolesSantorini($nombre) {
    $n = strtolower($nombre);
    return (stripos($n, 'napoles') !== false || stripos($n, 'nápoles') !== false || stripos($n, 'santorini') !== false || stripos($n, 'hacienda') !== false);
}

function getEmoji($n) { $n = strtolower($n); if (stripos($n, 'napoles') !== false) return '🦛'; if (stripos($n, 'santorini') !== false) return '🏖️'; if (stripos($n, 'guatape') !== false) return '🪨'; if (stripos($n, 'comuna') !== false) return '🎨'; if (stripos($n, 'cafe') !== false) return '☕'; if (stripos($n, 'jardin') !== false) return '🌸'; if (stripos($n, 'navide') !== false) return '🎄'; if (stripos($n, 'chiva') !== false) return '🎉'; if (stripos($n, 'rio claro') !== false) return '💧'; if (stripos($n, 'santa fe') !== false) return '🏛️'; if (stripos($n, 'parapente') !== false) return '🪂'; return '🎯'; }

function obtenerDetalleTour($nombre) {
    global $TOUR_DETALLES;
    foreach ($TOUR_DETALLES as $key => $detalle) { if (stripos($nombre, $key) !== false || stripos($key, $nombre) !== false) return $detalle; }
    $n = strtolower($nombre);
    if (stripos($n, 'guatap') !== false) return $TOUR_DETALLES['Tour a Guatapé'];
    if (stripos($n, 'navide') !== false) return $TOUR_DETALLES['Tour Navideño'];
    if (stripos($n, 'comuna 13') !== false && stripos($n, 'city') !== false) return $TOUR_DETALLES['City Tour Comuna 13'];
    if (stripos($n, 'comuna 13') !== false || stripos($n, 'solo comuna') !== false) return $TOUR_DETALLES['Solo Comuna 13'];
    if (stripos($n, 'napoles') !== false || stripos($n, 'santorini') !== false || stripos($n, 'hacienda') !== false) return $TOUR_DETALLES['Hacienda Nápoles + Santorini'];
    if (stripos($n, 'vibrante') !== false) return $TOUR_DETALLES['Paquete Vibrante'];
    if (stripos($n, 'chiva') !== false) return $TOUR_DETALLES['Chiva Rumbera'];
    if (stripos($n, 'city tour medell') !== false) return $TOUR_DETALLES['City Tour Medellín'];
    if (stripos($n, 'jardin') !== false || stripos($n, 'jardín') !== false) return $TOUR_DETALLES['Tour a Jardín Antioquia'];
    if (stripos($n, 'rio claro') !== false || stripos($n, 'río claro') !== false) return $TOUR_DETALLES['Tour a Río Claro'];
    if (stripos($n, 'santa fe') !== false) return $TOUR_DETALLES['Tour a Santa Fe de Antioquia'];
    if (stripos($n, 'cafe') !== false || stripos($n, 'café') !== false) return $TOUR_DETALLES['Tour de Café'];
    if (stripos($n, 'parapente') !== false) return $TOUR_DETALLES['Tour del Parapente'];
    return null;
}

function obtenerTours() { $pdo = getDatabase(); if (!$pdo) return []; $tours = []; $n = 1; foreach ($pdo->query("SELECT * FROM tours WHERE activo = 1")->fetchAll() as $t) { $tours[$n] = ['id' => $t['id'], 'nombre' => $t['nombre'], 'destino' => $t['destino'] ?? '', 'precio' => $t['precio'], 'precio_texto' => '$' . number_format($t['precio'], 0, ',', '.'), 'duracion' => $t['duracion_dias'] ?? 1, 'emoji' => getEmoji($t['nombre']), 'es_napoles' => esNapolesSantorini($t['nombre'])]; $n++; } return $tours; }

// ═══════════════════════════════════════════════════════════════
// PROCESAMIENTO DE MENSAJES
// ═══════════════════════════════════════════════════════════════

function procesarMensaje($phone, $texto, $nombre = '') {
    $session = getSesion($phone); $estado = $session['estado'] ?? 'inicio'; $lower = strtolower($texto);
    if (in_array($lower, ['menu', 'hola', 'hi', 'buenas', 'inicio', '0'])) { limpiarSesion($phone); enviarBienvenida($phone, $nombre); guardarConversacion($phone, $texto, 'Menu', 'menu'); return; }
    switch ($estado) {
        case 'esperando_nombre': $session['reserva']['nombre'] = $texto; $session['estado'] = 'esperando_personas'; setSesion($phone, $session); enviarTexto($phone, "✅ *{$texto}*\n\n👥 ¿Cuántas personas?"); break;
        case 'esperando_personas': $p = intval(preg_replace('/\D/', '', $texto)); if ($p < 1) { enviarTexto($phone, "❌ Número inválido"); return; } $session['reserva']['personas'] = $p; $session['estado'] = 'esperando_fecha'; setSesion($phone, $session); enviarTexto($phone, "✅ *{$p} personas*\n\n📅 ¿Fecha? (ej: 15 enero 2026)"); break;
        case 'esperando_fecha': $session['reserva']['fecha'] = $texto; $session['estado'] = 'esperando_telefono'; setSesion($phone, $session); enviarTexto($phone, "✅ *{$texto}*\n\n📱 ¿Tu teléfono?"); break;
        case 'esperando_telefono': $session['reserva']['telefono'] = $texto; $session['estado'] = 'confirmar'; setSesion($phone, $session); mostrarResumen($phone, $session); break;
        default:
            if (is_numeric($lower)) { $tours = obtenerTours(); $num = (int)$lower; if (isset($tours[$num])) { $t = $tours[$num]; if ($t['es_napoles']) { mostrarNapolesSantorini($phone, $num, $t); } else { mostrarTourConDetalles($phone, $num, $t); } return; } }
            enviarMenu($phone);
    }
}

function procesarBoton($phone, $buttonId) {
    if (procesarConfirmacion($phone, $buttonId)) return;
    
    if (preg_match('/^napoles_basico_(\d+)$/', $buttonId, $m)) { 
        $s = getSesion($phone); $t = $s['tour_base'] ?? null; 
        if ($t) { 
            setSesion($phone, ['estado' => 'detalle_tour', 'tour_id' => $t['id'], 'tour_nombre' => 'Hacienda Nápoles (Básico)', 'tour_precio' => 228000, 'tour_precio_texto' => '$228.000', 'pasaporte' => 'Básico']); 
            enviarBotones($phone, "🎫 *PASAPORTE BÁSICO*\n💰 *\$228.000*\n\n✅ Transporte y Desayuno\n✅ Plaza Santorini\n✅ Lago hipopótamos\n✅ Amazon Safari\n✅ Sabana Africana\n✅ Museos\n✅ Aventura Jurásica\n✅ Mariposario y más\n\n¿Reservar?", [['id' => 'btn_reservar', 'title' => '📅 Reservar'], ['id' => 'btn_menu', 'title' => '🏠 Menú']]); 
        } 
        return; 
    }
    
    if (preg_match('/^napoles_safari_(\d+)$/', $buttonId, $m)) { 
        $s = getSesion($phone); $t = $s['tour_base'] ?? null; 
        if ($t) { 
            setSesion($phone, ['estado' => 'detalle_tour', 'tour_id' => $t['id'], 'tour_nombre' => 'Hacienda Nápoles (Safari)', 'tour_precio' => 269000, 'tour_precio_texto' => '$269.000', 'pasaporte' => 'Safari']); 
            enviarBotones($phone, "🦁 *PASAPORTE SAFARI*\n💰 *\$269.000*\n\n✅ Todo lo del Básico MÁS:\n✅ Río Salvaje\n✅ Cataratas Victoria\n✅ Cobras\n\n¿Reservar?", [['id' => 'btn_reservar', 'title' => '📅 Reservar'], ['id' => 'btn_menu', 'title' => '🏠 Menú']]); 
        } 
        return; 
    }
    
    switch ($buttonId) {
        case 'btn_tours': mostrarListaTours($phone); break;
        case 'btn_populares': mostrarPopulares($phone); break;
        case 'btn_contacto': enviarTexto($phone, "📞 *SERCOLTUR*\n\n📱 +57 302 253 1580\n📧 info@sercoltur.com\n\n📍 Medellín, Colombia\n\n📅 Escribe *horario* para ver horarios"); break;
        case 'btn_menu': limpiarSesion($phone); enviarMenu($phone); break;
        case 'btn_reservar': iniciarReserva($phone); break;
        case 'btn_confirmar': confirmarReserva($phone); break;
        case 'btn_cancelar': limpiarSesion($phone); enviarTexto($phone, "❌ Cancelada"); break;
        default: 
            if (strpos($buttonId, 'tour_') === 0) { $n = (int)str_replace('tour_', '', $buttonId); $tours = obtenerTours(); if (isset($tours[$n])) { if ($tours[$n]['es_napoles']) { mostrarNapolesSantorini($phone, $n, $tours[$n]); } else { mostrarTourConDetalles($phone, $n, $tours[$n]); } } }
    }
}

function enviarBienvenida($phone, $nombre = '') { $h = (int)date('H'); $s = $h < 12 ? "Buenos días" : ($h < 19 ? "Buenas tardes" : "Buenas noches"); $n = $nombre ? " *{$nombre}*" : ""; enviarBotones($phone, "¡{$s}{$n}! 👋\n\nBienvenido a *SERCOLTUR* 🚌\n\n¿Qué deseas?", [['id' => 'btn_tours', 'title' => '🏞️ Ver Tours'], ['id' => 'btn_populares', 'title' => '⭐ Populares'], ['id' => 'btn_contacto', 'title' => '📞 Contacto']]); setSesion($phone, ['estado' => 'menu']); }
function enviarMenu($phone) { enviarBotones($phone, "📋 *MENÚ*\n\n¿Qué deseas?", [['id' => 'btn_tours', 'title' => '🏞️ Ver Tours'], ['id' => 'btn_populares', 'title' => '⭐ Populares'], ['id' => 'btn_contacto', 'title' => '📞 Contacto']]); setSesion($phone, ['estado' => 'menu']); }
function mostrarListaTours($phone) { $tours = obtenerTours(); $m = "🏞️ *TOURS*\n\n"; foreach ($tours as $n => $t) { $p = $t['es_napoles'] ? " 🎫🦁" : ""; $m .= "{$t['emoji']} *{$n}.* {$t['nombre']}{$p}\n    💰 {$t['precio_texto']}\n\n"; } $m .= "📝 Escribe el *NÚMERO*"; enviarTexto($phone, $m); setSesion($phone, ['estado' => 'ver_tours']); }
function mostrarPopulares($phone) { $tours = obtenerTours(); $pop = array_slice($tours, 0, 3, true); $m = "⭐ *POPULARES*\n\n"; $btns = []; foreach ($pop as $n => $t) { $m .= "{$t['emoji']} *{$t['nombre']}*\n💰 {$t['precio_texto']}\n\n"; $btns[] = ['id' => 'tour_' . $n, 'title' => mb_substr($t['nombre'], 0, 18)]; } enviarBotones($phone, $m, array_slice($btns, 0, 3)); }

function mostrarNapolesSantorini($phone, $num, $t) {
    setSesion($phone, ['estado' => 'seleccionar_pasaporte', 'tour_num' => $num, 'tour_base' => $t]);
    $detalle = obtenerDetalleTour($t['nombre']); if ($detalle) enviarTexto($phone, $detalle);
    enviarBotones($phone, "🎟️ *Elige tu pasaporte:*", [['id' => 'napoles_basico_' . $num, 'title' => '🎫 Básico $228.000'], ['id' => 'napoles_safari_' . $num, 'title' => '🦁 Safari $269.000'], ['id' => 'btn_menu', 'title' => '🏠 Menú']]);
}

function mostrarTourConDetalles($phone, $num, $t) {
    setSesion($phone, ['estado' => 'detalle_tour', 'tour_id' => $t['id'], 'tour_nombre' => $t['nombre'], 'tour_precio' => $t['precio'], 'tour_precio_texto' => $t['precio_texto']]);
    $detalle = obtenerDetalleTour($t['nombre']);
    if ($detalle) { enviarTexto($phone, $detalle); enviarBotones($phone, "¿Deseas reservar este tour?", [['id' => 'btn_reservar', 'title' => '📅 Reservar'], ['id' => 'btn_tours', 'title' => '🔄 Otros Tours'], ['id' => 'btn_menu', 'title' => '🏠 Menú']]); }
    else { enviarBotones($phone, "{$t['emoji']} *{$t['nombre']}*\n\n💰 {$t['precio_texto']}\n📍 {$t['destino']}\n⏱️ {$t['duracion']} día(s)\n\n¿Reservar?", [['id' => 'btn_reservar', 'title' => '📅 Reservar'], ['id' => 'btn_tours', 'title' => '🔄 Otros'], ['id' => 'btn_menu', 'title' => '🏠 Menú']]); }
}

function iniciarReserva($phone) { $s = getSesion($phone); if (!isset($s['tour_id'])) { enviarTexto($phone, "❌ Selecciona un tour primero"); return; } $s['estado'] = 'esperando_nombre'; $s['reserva'] = ['tour_id' => $s['tour_id'], 'tour_nombre' => $s['tour_nombre'], 'tour_precio' => $s['tour_precio'], 'pasaporte' => $s['pasaporte'] ?? null]; setSesion($phone, $s); enviarTexto($phone, "📅 *RESERVAR*\n\n🎯 {$s['tour_nombre']}\n💰 {$s['tour_precio_texto']}\n\n👤 ¿Tu nombre?"); }
function mostrarResumen($phone, $s) { $r = $s['reserva']; $total = $r['tour_precio'] * $r['personas']; $pas = !empty($r['pasaporte']) ? "\n🎫 {$r['pasaporte']}" : ""; enviarBotones($phone, "📋 *RESUMEN*\n\n🎯 {$r['tour_nombre']}{$pas}\n👤 {$r['nombre']}\n👥 {$r['personas']} pax\n📅 {$r['fecha']}\n📱 {$r['telefono']}\n\n💵 *TOTAL: $" . number_format($total, 0, ',', '.') . "*\n\n¿Confirmar?", [['id' => 'btn_confirmar', 'title' => '✅ Confirmar'], ['id' => 'btn_cancelar', 'title' => '❌ Cancelar']]); }

function confirmarReserva($phone) {
    $s = getSesion($phone); if (!isset($s['reserva'])) { enviarTexto($phone, "❌ Sin reserva"); return; }
    $r = $s['reserva']; $total = $r['tour_precio'] * $r['personas'];
    $pdo = getDatabase(); if (!$pdo) { enviarTexto($phone, "❌ Error. Contacta: +57 302 253 1580"); return; }
    $telAsesor = obtenerTelefonoAsesor();
    try {
        $pdo->beginTransaction();
        $tel = preg_replace('/[^0-9]/', '', $phone);
        $nombreLimpio = trim($r['nombre']);
        $stmt = $pdo->prepare("SELECT id, nombre FROM clientes WHERE telefono = ?"); $stmt->execute([$tel]); $c = $stmt->fetch();
        if ($c) { $cid = $c['id']; if ($nombreLimpio && $nombreLimpio !== $c['nombre']) $pdo->prepare("UPDATE clientes SET nombre = ? WHERE id = ?")->execute([$nombreLimpio, $cid]); }
        else { $pdo->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)")->execute([$nombreLimpio, $tel]); $cid = $pdo->lastInsertId(); }
        $codigo = 'SER-' . date('ymd') . '-' . rand(1000, 9999);
        $fecha = parsearFecha($r['fecha']);
        $nota = 'WhatsApp' . (!empty($r['pasaporte']) ? " | {$r['pasaporte']}" : "");
        $pdo->prepare("INSERT INTO reservas (cliente_id, tour_id, fecha_inicio, cantidad_personas, precio_total, estado, codigo_whatsapp, telefono_contacto, canal_origen, notas) VALUES (?,?,?,?,?,'pendiente_asesor',?,?,'whatsapp',?)")->execute([$cid, $r['tour_id'], $fecha, $r['personas'], $total, $codigo, $r['telefono'], $nota]);
        $reservaId = $pdo->lastInsertId();
        $pdo->commit();
        
        logBot("Nueva reserva #$reservaId - Notificando asesor: $telAsesor");
        
        if ($telAsesor) {
            $msg = "🔔 *NUEVA RESERVA #$reservaId*\n\n━━━━━━━━━━━━━━━━━━\n🎭 *Tour:* {$r['tour_nombre']}\n👤 *Cliente:* {$r['nombre']}\n📱 *Tel:* {$r['telefono']}\n👥 *Personas:* {$r['personas']}\n📅 *Fecha:* {$r['fecha']}\n💰 *Total:* $" . number_format($total, 0, ',', '.') . "\n━━━━━━━━━━━━━━━━━━\n\n¿Confirmar esta venta?";
            enviarBotones($telAsesor, $msg, [['id' => 'asesor_confirmar_' . $reservaId, 'title' => '✅ Confirmar'], ['id' => 'asesor_rechazar_' . $reservaId, 'title' => '❌ Rechazar']]);
        }
        
        enviarTexto($phone, "🎉 *RESERVA RECIBIDA*\n\n🎫 Código: *{$codigo}*\n\n📋 {$r['tour_nombre']}\n👥 {$r['personas']} pax\n💵 $" . number_format($total, 0, ',', '.') . "\n\n⏳ Un asesor confirmará tu reserva pronto.\n\n🚌 *SERCOLTUR*");
        limpiarSesion($phone);
        guardarConversacion($phone, 'Reserva', "Código: $codigo", 'reserva');
    } catch (Exception $e) { $pdo->rollBack(); enviarTexto($phone, "❌ Error. Contacta: +57 302 253 1580"); logBot("ERROR: " . $e->getMessage()); }
}

function parsearFecha($t) { $meses = ['enero'=>'01','febrero'=>'02','marzo'=>'03','abril'=>'04','mayo'=>'05','junio'=>'06','julio'=>'07','agosto'=>'08','septiembre'=>'09','octubre'=>'10','noviembre'=>'11','diciembre'=>'12']; $t = strtolower($t); foreach ($meses as $m => $n) { if (strpos($t, $m) !== false) { preg_match('/(\d{1,2})/', $t, $d); preg_match('/(\d{4})/', $t, $a); if (!empty($d[1]) && !empty($a[1])) return $a[1] . '-' . $n . '-' . str_pad($d[1], 2, '0', STR_PAD_LEFT); } } return date('Y-m-d', strtotime('+7 days')); }

// ═══════════════════════════════════════════════════════════════
// FUNCIONES DE ENVÍO WHATSAPP
// ═══════════════════════════════════════════════════════════════

function enviarTexto($phone, $msg) { return enviarAPI(['messaging_product' => 'whatsapp', 'to' => $phone, 'type' => 'text', 'text' => ['body' => $msg]]); }
function enviarBotones($phone, $msg, $btns) { $b = []; foreach (array_slice($btns, 0, 3) as $x) $b[] = ['type' => 'reply', 'reply' => ['id' => $x['id'], 'title' => mb_substr($x['title'], 0, 20)]]; return enviarAPI(['messaging_product' => 'whatsapp', 'to' => $phone, 'type' => 'interactive', 'interactive' => ['type' => 'button', 'body' => ['text' => $msg], 'action' => ['buttons' => $b]]]); }
function enviarAPI($data) { global $ACCESS_TOKEN, $PHONE_ID; $ch = curl_init("https://graph.facebook.com/v18.0/{$PHONE_ID}/messages"); curl_setopt_array($ch, [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $ACCESS_TOKEN, 'Content-Type: application/json'], CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($data), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]); $r = curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch); logBot($c == 200 ? "OK" : "Error $c: $r"); return $c == 200; }

// ═══════════════════════════════════════════════════════════════
// FUNCIONES DE SESIÓN
// ═══════════════════════════════════════════════════════════════

function getSesion($p) { global $SESSIONS_DIR; $f = $SESSIONS_DIR . '/' . preg_replace('/\D/', '', $p) . '.json'; return file_exists($f) ? json_decode(file_get_contents($f), true) ?: [] : []; }
function setSesion($p, $d) { global $SESSIONS_DIR; $f = $SESSIONS_DIR . '/' . preg_replace('/\D/', '', $p) . '.json'; file_put_contents($f, json_encode(array_merge(getSesion($p), $d))); }
function limpiarSesion($p) { global $SESSIONS_DIR; $f = $SESSIONS_DIR . '/' . preg_replace('/\D/', '', $p) . '.json'; if (file_exists($f)) unlink($f); }
