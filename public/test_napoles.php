<?php
/**
 * SERCOLTUR WhatsApp Bot - CORREGIDO
 * Nápoles aparece correctamente en la lista
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$VERIFY_TOKEN = 'SERCOLTUR2025';
$ACCESS_TOKEN = 'TU_ACCESS_TOKEN_AQUI'; // ⚠️ ACTUALIZAR
$PHONE_ID = '925480580639940';

$SESSIONS_DIR = __DIR__ . '/sessions';
if (!is_dir($SESSIONS_DIR)) mkdir($SESSIONS_DIR, 0755, true);

// ============ CONEXIÓN BD ============
function getDatabase() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $configFile = __DIR__ . '/../config/database.php';
            if (file_exists($configFile)) {
                require_once($configFile);
                if (isset($pdo)) return $pdo;
            }
            
            $pdo = new PDO(
                "mysql:host=localhost;dbname=sercolturbot;charset=utf8mb4",
                "root", "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            logBot("❌ BD Error: " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

function logBot($msg) {
    file_put_contents(__DIR__ . '/whatsapp_log.txt', "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

// ============ WEBHOOK GET ============
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    if (empty($token) && empty($challenge)) { mostrarConfig(); exit; }
    if ($token === $VERIFY_TOKEN) { echo $challenge; exit; }
    http_response_code(403);
    exit;
}

// ============ WEBHOOK POST ============
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(200);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data && isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
        $msg = $data['entry'][0]['changes'][0]['value']['messages'][0];
        $phone = $msg['from'];
        $nombre = $data['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? '';
        
        if (isset($msg['text']['body'])) {
            logBot("📱 $phone: " . $msg['text']['body']);
            procesarMensaje($phone, trim($msg['text']['body']), $nombre);
        }
        
        if (isset($msg['interactive']['button_reply']['id'])) {
            $btnId = $msg['interactive']['button_reply']['id'];
            logBot("🔘 $phone: $btnId");
            procesarBoton($phone, $btnId);
        }
        
        if (isset($msg['interactive']['list_reply']['id'])) {
            $listId = $msg['interactive']['list_reply']['id'];
            logBot("📋 $phone: $listId");
            procesarBoton($phone, $listId);
        }
    }
    
    exit;
}

// ============ FUNCIÓN PARA DETECTAR SI ES TOUR DE NÁPOLES ============
function esNapoles($nombre) {
    $n = strtolower($nombre);
    return (strpos($n, 'nápoles') !== false || strpos($n, 'napoles') !== false);
}

function esNapolesBasico($nombre) {
    $n = strtolower($nombre);
    return esNapoles($nombre) && (strpos($n, 'básico') !== false || strpos($n, 'basico') !== false);
}

function esNapolesSafari($nombre) {
    $n = strtolower($nombre);
    return esNapoles($nombre) && strpos($n, 'safari') !== false;
}

// ============ TOURS DESDE BASE DE DATOS ============
function obtenerToursDB() {
    $pdo = getDatabase();
    if (!$pdo) {
        logBot("⚠️ Sin conexión BD");
        return obtenerToursEstaticos();
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM tours WHERE activo = 1 ORDER BY id ASC");
        $toursDB = $stmt->fetchAll();
        
        if (empty($toursDB)) {
            return obtenerToursEstaticos();
        }
        
        $tours = [];
        $num = 1;
        $napolesBasico = null;
        $napolesSafari = null;
        
        // PRIMERO: Buscar los tours de Nápoles
        foreach ($toursDB as $tour) {
            if (esNapolesBasico($tour['nombre'])) {
                $napolesBasico = $tour;
                logBot("🔍 Nápoles Básico encontrado: ID=" . $tour['id']);
            }
            if (esNapolesSafari($tour['nombre'])) {
                $napolesSafari = $tour;
                logBot("🔍 Nápoles Safari encontrado: ID=" . $tour['id']);
            }
        }
        
        // SEGUNDO: Construir lista de tours
        $napolesYaAgregado = false;
        
        foreach ($toursDB as $tour) {
            // Si es un tour de Nápoles
            if (esNapoles($tour['nombre'])) {
                // Solo agregar UNA VEZ como tour combinado
                if (!$napolesYaAgregado && $napolesBasico && $napolesSafari) {
                    $tours[(string)$num] = [
                        'id' => (int)$napolesBasico['id'],
                        'nombre' => 'Hacienda Nápoles + Santorini',
                        'emoji' => '🦁',
                        'tiene_pasaportes' => true,
                        'destino' => 'Hacienda Nápoles',
                        'salida' => 'Parque del poblado o Estación estadio',
                        'hora' => '4:00am o 4:30am',
                        'basico' => [
                            'id' => (int)$napolesBasico['id'],
                            'precio' => (float)$napolesBasico['precio'],
                            'precio_texto' => '$' . number_format($napolesBasico['precio'], 0, ',', '.'),
                            'descripcion' => $napolesBasico['descripcion'] ?? ''
                        ],
                        'safari' => [
                            'id' => (int)$napolesSafari['id'],
                            'precio' => (float)$napolesSafari['precio'],
                            'precio_texto' => '$' . number_format($napolesSafari['precio'], 0, ',', '.'),
                            'descripcion' => $napolesSafari['descripcion'] ?? ''
                        ]
                    ];
                    logBot("✅ Nápoles agregado como tour #$num");
                    $napolesYaAgregado = true;
                    $num++;
                }
                // Saltar para no agregar individualmente
                continue;
            }
            
            // Tours normales (no Nápoles)
            $tours[(string)$num] = [
                'id' => (int)$tour['id'],
                'nombre' => $tour['nombre'],
                'emoji' => obtenerEmoji($tour['nombre']),
                'precio' => (float)$tour['precio'],
                'precio_texto' => '$' . number_format($tour['precio'], 0, ',', '.'),
                'destino' => $tour['destino'] ?? 'Medellín',
                'descripcion' => $tour['descripcion'] ?? '',
                'duracion' => ($tour['duracion_dias'] ?? 1) . ' día(s)',
                'capacidad' => $tour['capacidad_maxima'] ?? 30,
                'salida' => extraerSalida($tour['descripcion'] ?? ''),
                'hora' => extraerHora($tour['descripcion'] ?? ''),
                'incluye' => extraerIncluye($tour['descripcion'] ?? ''),
                'no_incluye' => extraerNoIncluye($tour['descripcion'] ?? '')
            ];
            $num++;
        }
        
        logBot("✅ Total tours: " . count($tours));
        return $tours;
        
    } catch (Exception $e) {
        logBot("❌ Error: " . $e->getMessage());
        return obtenerToursEstaticos();
    }
}

function extraerSalida($desc) {
    if (preg_match('/SALIDA[:\s]*([^\n]+)/i', $desc, $m)) return trim($m[1]);
    return 'Estación Estadio - Parque del Poblado';
}

function extraerHora($desc) {
    if (preg_match('/HORA[:\s]*([^\n]+)/i', $desc, $m)) return trim($m[1]);
    return 'Consultar horarios';
}

function extraerIncluye($desc) {
    if (preg_match('/INCLUYE[:\s]*\n?(.*?)(?=NO INCLUYE|❌|$)/is', $desc, $m)) return trim($m[1]);
    return $desc;
}

function extraerNoIncluye($desc) {
    if (preg_match('/NO INCLUYE[:\s]*([^\n]+)/i', $desc, $m)) return trim($m[1]);
    return 'Consultar';
}

function obtenerEmoji($nombre) {
    $n = strtolower($nombre);
    $emojis = [
        'guatap' => '🏞️', 'navide' => '🎄', 'comuna' => '🏙️',
        'napoles' => '🦁', 'nápoles' => '🦁', 'chiva' => '🎉',
        'jardin' => '🌿', 'jardín' => '🌿', 'rio claro' => '💧', 'río claro' => '💧',
        'santa fe' => '🏛️', 'cafe' => '☕', 'café' => '☕',
        'parapente' => '🪂', 'city' => '🌆', 'medell' => '🌆',
        'vibrante' => '✨', 'paquete' => '✨'
    ];
    
    foreach ($emojis as $key => $emoji) {
        if (strpos($n, $key) !== false) return $emoji;
    }
    return '🎯';
}

function obtenerToursEstaticos() {
    return [
        '1' => ['id' => 1, 'nombre' => 'Tour a Guatapé', 'emoji' => '🏞️', 'precio' => 109000, 'precio_texto' => '$109.000', 'salida' => 'Parque del Poblado', 'hora' => '7:00am', 'incluye' => 'Transporte, desayuno, almuerzo', 'no_incluye' => 'Ingreso piedra'],
    ];
}

// ============ PROCESAR MENSAJE ============
function procesarMensaje($phone, $text, $nombre = '') {
    $session = getSesion($phone);
    $estado = $session['estado'] ?? 'inicio';
    $txt = strtolower(trim($text));
    
    // Comandos globales
    if (in_array($txt, ['menu', 'menú', 'inicio', '0', 'salir', 'cancelar', 'volver'])) {
        limpiarSesion($phone);
        enviarMenu($phone);
        return;
    }
    
    // Saludo
    if (esSaludo($txt) && $estado === 'inicio') {
        enviarBienvenida($phone, $nombre);
        return;
    }
    
    // Flujo de reserva
    switch ($estado) {
        case 'esperando_nombre':
            $session['reserva']['nombre'] = $text;
            $session['estado'] = 'esperando_personas';
            setSesion($phone, $session);
            enviarTexto($phone, "✅ Perfecto *{$text}*!\n\n👥 *¿Cuántas personas van?*\n\nEscribe el número (ej: 2)");
            break;
            
        case 'esperando_personas':
            $p = intval(preg_replace('/[^0-9]/', '', $text));
            if ($p < 1 || $p > 50) {
                enviarTexto($phone, "❌ Número inválido (1-50)");
                return;
            }
            $session['reserva']['personas'] = $p;
            $session['estado'] = 'esperando_fecha';
            setSesion($phone, $session);
            enviarTexto($phone, "✅ *{$p} persona(s)*\n\n📅 *¿Para qué fecha?*\n\n(ej: 15 enero 2026)");
            break;
            
        case 'esperando_fecha':
            $session['reserva']['fecha'] = $text;
            $session['estado'] = 'esperando_telefono';
            setSesion($phone, $session);
            enviarTexto($phone, "✅ Fecha: *{$text}*\n\n📱 *¿Tu teléfono de contacto?*\n\n(ej: 3001234567)");
            break;
            
        case 'esperando_telefono':
            $session['reserva']['telefono'] = $text;
            $session['estado'] = 'confirmar';
            setSesion($phone, $session);
            mostrarResumen($phone, $session);
            break;
            
        default:
            if (is_numeric($txt)) {
                $tours = obtenerToursDB();
                if (isset($tours[$txt])) {
                    mostrarTour($phone, $txt);
                    return;
                }
            }
            enviarMenu($phone);
    }
}

// ============ PROCESAR BOTONES ============
function procesarBoton($phone, $btn) {
    logBot("🎯 Botón: $btn");
    
    switch ($btn) {
        case 'btn_tours': mostrarListaTours($phone); break;
        case 'btn_populares': mostrarPopulares($phone); break;
        case 'btn_contacto': enviarContacto($phone); break;
        case 'btn_menu': limpiarSesion($phone); enviarMenu($phone); break;
        case 'btn_reservar': iniciarReserva($phone); break;
        case 'btn_confirmar': confirmarReserva($phone); break;
        case 'btn_cancelar': limpiarSesion($phone); enviarTexto($phone, "❌ Reserva cancelada.\n\nEscribe *MENU*"); break;
        case 'btn_otros': mostrarListaTours($phone); break;
        case 'btn_napoles_basico': mostrarNapolesDetalle($phone, 'basico'); break;
        case 'btn_napoles_safari': mostrarNapolesDetalle($phone, 'safari'); break;
        
        default:
            if (strpos($btn, 'tour_') === 0) {
                mostrarTour($phone, str_replace('tour_', '', $btn));
            }
    }
}

// ============ MENSAJES ============
function enviarBienvenida($phone, $nombre = '') {
    $h = (int)date('H');
    $s = $h < 12 ? "Buenos días" : ($h < 19 ? "Buenas tardes" : "Buenas noches");
    $n = $nombre ? " *{$nombre}*" : "";
    
    $msg = "¡{$s}{$n}! 👋\n\n";
    $msg .= "Bienvenido/a a *SERCOLTUR* 🚌✨\n\n";
    $msg .= "Tours por Medellín y Antioquia.\n\n";
    $msg .= "¿Qué deseas hacer?";
    
    enviarBotones($phone, $msg, [
        ['id' => 'btn_tours', 'title' => '🏞️ Ver Tours'],
        ['id' => 'btn_populares', 'title' => '⭐ Populares'],
        ['id' => 'btn_contacto', 'title' => '📞 Contacto']
    ]);
    setSesion($phone, ['estado' => 'menu', 'nombre_contacto' => $nombre]);
}

function enviarMenu($phone) {
    enviarBotones($phone, "📋 *MENÚ PRINCIPAL*\n\n¿Qué deseas hacer?", [
        ['id' => 'btn_tours', 'title' => '🏞️ Ver Tours'],
        ['id' => 'btn_populares', 'title' => '⭐ Populares'],
        ['id' => 'btn_contacto', 'title' => '📞 Contacto']
    ]);
    setSesion($phone, ['estado' => 'menu']);
}

function mostrarListaTours($phone) {
    $tours = obtenerToursDB();
    $msg = "🏞️ *TOURS DISPONIBLES*\n\n";
    
    foreach ($tours as $n => $t) {
        if (isset($t['tiene_pasaportes']) && $t['tiene_pasaportes']) {
            $precio = $t['basico']['precio_texto'] . ' - ' . $t['safari']['precio_texto'];
        } else {
            $precio = $t['precio_texto'];
        }
        $msg .= "*{$n}.* {$t['emoji']} {$t['nombre']}\n    💰 {$precio}\n\n";
    }
    
    $msg .= "📝 Escribe el *NÚMERO* del tour (1-" . count($tours) . ")";
    enviarTexto($phone, $msg);
    setSesion($phone, ['estado' => 'ver_tours']);
}

function mostrarPopulares($phone) {
    $tours = obtenerToursDB();
    $top3 = array_slice($tours, 0, 3, true);
    
    $msg = "⭐ *TOURS MÁS POPULARES*\n\n";
    $botones = [];
    $i = 1;
    
    foreach ($top3 as $num => $t) {
        if (isset($t['tiene_pasaportes']) && $t['tiene_pasaportes']) {
            $precio = $t['basico']['precio_texto'] . ' - ' . $t['safari']['precio_texto'];
        } else {
            $precio = $t['precio_texto'];
        }
        $msg .= "{$i}️⃣ *{$t['emoji']} {$t['nombre']}*\n    💰 {$precio}\n\n";
        $botones[] = ['id' => 'tour_' . $num, 'title' => mb_substr("{$i}. {$t['nombre']}", 0, 20)];
        $i++;
    }
    
    enviarBotones($phone, $msg, $botones);
}

function mostrarTour($phone, $num) {
    $tours = obtenerToursDB();
    
    if (!isset($tours[$num])) {
        enviarTexto($phone, "❌ Tour no encontrado. Escribe *MENU*");
        return;
    }
    
    $t = $tours[$num];
    
    // Si tiene pasaportes (Nápoles)
    if (isset($t['tiene_pasaportes']) && $t['tiene_pasaportes']) {
        logBot("🦁 Tour Nápoles seleccionado, mostrando opciones");
        mostrarOpcionesNapoles($phone, $num, $t);
        return;
    }
    
    // Tour normal
    $msg = "{$t['emoji']} *{$t['nombre']}*\n\n";
    $msg .= "💰 *Precio:* {$t['precio_texto']} /persona\n";
    $msg .= "📍 *Salida:* {$t['salida']}\n";
    $msg .= "⏰ *Hora:* {$t['hora']}\n";
    
    if (!empty($t['incluye'])) {
        $msg .= "\n✅ *INCLUYE:*\n{$t['incluye']}\n";
    }
    if (!empty($t['no_incluye']) && $t['no_incluye'] !== 'Consultar') {
        $msg .= "\n❌ *NO INCLUYE:* {$t['no_incluye']}";
    }
    
    setSesion($phone, [
        'estado' => 'detalle',
        'tour_num' => $num,
        'tour_id' => $t['id'],
        'tour_nombre' => $t['nombre'],
        'tour_precio' => $t['precio'],
        'tour_precio_texto' => $t['precio_texto']
    ]);
    
    enviarBotones($phone, $msg, [
        ['id' => 'btn_reservar', 'title' => '📅 Reservar'],
        ['id' => 'btn_otros', 'title' => '🔄 Otros Tours'],
        ['id' => 'btn_menu', 'title' => '🏠 Menú']
    ]);
}

function mostrarOpcionesNapoles($phone, $num, $t) {
    $msg = "🦁 *{$t['nombre']}*\n\n";
    $msg .= "Este tour tiene *2 tipos de pasaporte*:\n\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "📦 *PASAPORTE BÁSICO*\n";
    $msg .= "💰 {$t['basico']['precio_texto']} /persona\n";
    $msg .= "Atracciones principales del parque\n\n";
    $msg .= "🦁 *PASAPORTE SAFARI*\n";
    $msg .= "💰 {$t['safari']['precio_texto']} /persona\n";
    $msg .= "Todo incluido + atracciones exclusivas\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $msg .= "📍 *Salida:* {$t['salida']}\n";
    $msg .= "⏰ *Hora:* {$t['hora']}\n\n";
    $msg .= "*¿Cuál pasaporte prefieres?*";
    
    setSesion($phone, [
        'estado' => 'napoles_opciones', 
        'tour_num' => $num,
        'napoles_data' => [
            'basico_id' => $t['basico']['id'],
            'basico_precio' => $t['basico']['precio'],
            'basico_precio_texto' => $t['basico']['precio_texto'],
            'safari_id' => $t['safari']['id'],
            'safari_precio' => $t['safari']['precio'],
            'safari_precio_texto' => $t['safari']['precio_texto'],
            'salida' => $t['salida'],
            'hora' => $t['hora']
        ]
    ]);
    
    enviarBotones($phone, $msg, [
        ['id' => 'btn_napoles_basico', 'title' => '📦 Básico $228k'],
        ['id' => 'btn_napoles_safari', 'title' => '🦁 Safari $269k'],
        ['id' => 'btn_otros', 'title' => '🔄 Otros Tours']
    ]);
}

function mostrarNapolesDetalle($phone, $tipo) {
    $session = getSesion($phone);
    $napoles = $session['napoles_data'] ?? null;
    
    if (!$napoles) {
        // Buscar en BD
        $tours = obtenerToursDB();
        foreach ($tours as $n => $tour) {
            if (isset($tour['tiene_pasaportes']) && $tour['tiene_pasaportes']) {
                $napoles = [
                    'basico_id' => $tour['basico']['id'],
                    'basico_precio' => $tour['basico']['precio'],
                    'basico_precio_texto' => $tour['basico']['precio_texto'],
                    'safari_id' => $tour['safari']['id'],
                    'safari_precio' => $tour['safari']['precio'],
                    'safari_precio_texto' => $tour['safari']['precio_texto'],
                    'salida' => $tour['salida'],
                    'hora' => $tour['hora']
                ];
                $session['tour_num'] = $n;
                break;
            }
        }
    }
    
    if (!$napoles) {
        enviarTexto($phone, "❌ Error. Escribe *MENU*");
        return;
    }
    
    $titulo = $tipo === 'basico' ? 'BÁSICO' : 'SAFARI';
    $precio = $tipo === 'basico' ? $napoles['basico_precio'] : $napoles['safari_precio'];
    $precioTxt = $tipo === 'basico' ? $napoles['basico_precio_texto'] : $napoles['safari_precio_texto'];
    $tourId = $tipo === 'basico' ? $napoles['basico_id'] : $napoles['safari_id'];
    
    $msg = "🦁 *HACIENDA NÁPOLES - PASAPORTE {$titulo}*\n\n";
    $msg .= "💰 *Precio:* {$precioTxt} /persona\n";
    $msg .= "📍 *Salida:* {$napoles['salida']}\n";
    $msg .= "⏰ *Hora:* {$napoles['hora']}\n\n";
    
    if ($tipo === 'basico') {
        $msg .= "✅ *INCLUYE:*\n";
        $msg .= "• Transporte IDA y REGRESO\n";
        $msg .= "• Desayuno\n";
        $msg .= "• Plaza Santorini\n";
        $msg .= "• Lago de los hipopótamos\n";
        $msg .= "• Amazon safari\n";
        $msg .= "• Gran sabana africana\n";
        $msg .= "• Museos\n";
        $msg .= "• Mariposario, aves, reptiles\n";
        $msg .= "• Guía y asistencia médica\n\n";
        $msg .= "❌ *NO INCLUYE:* Almuerzo, Piscinas";
    } else {
        $msg .= "✅ *INCLUYE TODO LO BÁSICO MÁS:*\n";
        $msg .= "• Río salvaje\n";
        $msg .= "• Cataratas Victoria\n";
        $msg .= "• Cobras\n";
        $msg .= "• Acceso completo\n\n";
        $msg .= "❌ *NO INCLUYE:* Almuerzo";
    }
    
    setSesion($phone, [
        'estado' => 'detalle',
        'tour_num' => $session['tour_num'] ?? '4',
        'tour_id' => $tourId,
        'tour_nombre' => "Hacienda Nápoles - " . ucfirst($tipo),
        'tour_precio' => $precio,
        'tour_precio_texto' => $precioTxt,
        'tipo_napoles' => $tipo,
        'napoles_data' => $napoles
    ]);
    
    $otroBtn = $tipo === 'basico' 
        ? ['id' => 'btn_napoles_safari', 'title' => '🦁 Ver Safari']
        : ['id' => 'btn_napoles_basico', 'title' => '📦 Ver Básico'];
    
    enviarBotones($phone, $msg, [
        ['id' => 'btn_reservar', 'title' => '📅 Reservar'],
        $otroBtn,
        ['id' => 'btn_menu', 'title' => '🏠 Menú']
    ]);
}

// ============ RESERVA ============
function iniciarReserva($phone) {
    $s = getSesion($phone);
    
    if (!isset($s['tour_id'])) {
        enviarTexto($phone, "❌ Primero selecciona un tour.\n\nEscribe *MENU*");
        return;
    }
    
    $msg = "📅 *RESERVAR: {$s['tour_nombre']}*\n\n";
    $msg .= "💰 Precio: {$s['tour_precio_texto']} /persona\n\n";
    $msg .= "👤 *¿Cuál es tu nombre completo?*";
    
    $s['estado'] = 'esperando_nombre';
    $s['reserva'] = [
        'tour_id' => $s['tour_id'],
        'tour_nombre' => $s['tour_nombre'],
        'tour_precio' => $s['tour_precio'],
        'tour_precio_texto' => $s['tour_precio_texto']
    ];
    if (isset($s['tipo_napoles'])) {
        $s['reserva']['tipo_napoles'] = $s['tipo_napoles'];
    }
    setSesion($phone, $s);
    
    enviarTexto($phone, $msg);
}

function mostrarResumen($phone, $s) {
    $r = $s['reserva'];
    $total = $r['tour_precio'] * $r['personas'];
    $totalTxt = '$' . number_format($total, 0, ',', '.');
    
    $msg = "📋 *RESUMEN DE RESERVA*\n\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🎫 *Tour:* {$r['tour_nombre']}\n";
    $msg .= "👤 *Nombre:* {$r['nombre']}\n";
    $msg .= "👥 *Personas:* {$r['personas']}\n";
    $msg .= "📅 *Fecha:* {$r['fecha']}\n";
    $msg .= "📱 *Teléfono:* {$r['telefono']}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "💰 *Precio unitario:* {$r['tour_precio_texto']}\n";
    $msg .= "💵 *TOTAL:* {$totalTxt}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $msg .= "¿Confirmas la reserva?";
    
    enviarBotones($phone, $msg, [
        ['id' => 'btn_confirmar', 'title' => '✅ Confirmar'],
        ['id' => 'btn_cancelar', 'title' => '❌ Cancelar']
    ]);
}

function confirmarReserva($phone) {
    $s = getSesion($phone);
    
    if (!isset($s['reserva'])) {
        enviarTexto($phone, "❌ No hay reserva pendiente.\n\nEscribe *MENU*");
        return;
    }
    
    $r = $s['reserva'];
    $total = $r['tour_precio'] * $r['personas'];
    $totalTxt = '$' . number_format($total, 0, ',', '.');
    
    $result = guardarReservaBD($phone, $r, $total);
    
    if ($result['ok']) {
        $msg = "🎉 *¡RESERVA CONFIRMADA!* 🎉\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🎫 *Código:* {$result['codigo']}\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "🎯 *Tour:* {$r['tour_nombre']}\n";
        $msg .= "👤 *Nombre:* {$r['nombre']}\n";
        $msg .= "👥 *Personas:* {$r['personas']}\n";
        $msg .= "📅 *Fecha:* {$r['fecha']}\n";
        $msg .= "📱 *Teléfono:* {$r['telefono']}\n\n";
        $msg .= "💵 *TOTAL:* {$totalTxt}\n\n";
        $msg .= "📞 Te contactaremos para el pago.\n\n";
        $msg .= "¡Gracias por elegir *SERCOLTUR*! 🚌✨";
        
        logBot("✅ Reserva: {$result['codigo']}");
    } else {
        $msg = "❌ Error al procesar.\n\nContacta: +57 300 123 4567";
        logBot("❌ Error: {$result['error']}");
    }
    
    enviarTexto($phone, $msg);
    limpiarSesion($phone);
    
    if ($result['ok']) {
        sleep(2);
        enviarBotones($phone, "¿Algo más?", [
            ['id' => 'btn_tours', 'title' => '🏞️ Ver Tours'],
            ['id' => 'btn_contacto', 'title' => '📞 Contacto']
        ]);
    }
}

function guardarReservaBD($phone, $r, $total) {
    $pdo = getDatabase();
    if (!$pdo) return ['ok' => false, 'error' => 'Sin BD'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE telefono = ?");
        $stmt->execute([$phone]);
        $c = $stmt->fetch();
        
        if ($c) {
            $cid = $c['id'];
            $pdo->prepare("UPDATE clientes SET nombre = ? WHERE id = ?")->execute([$r['nombre'], $cid]);
        } else {
            $pdo->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)")->execute([$r['nombre'], $phone]);
            $cid = $pdo->lastInsertId();
        }
        
        $codigo = 'SER-' . date('ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $fecha = parsearFecha($r['fecha']);
        
        $notas = "WhatsApp Bot - " . date('d/m/Y H:i');
        if (isset($r['tipo_napoles'])) {
            $notas .= " | Pasaporte: " . ucfirst($r['tipo_napoles']);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO reservas (cliente_id, tour_id, fecha_reserva, fecha_inicio, cantidad_personas, precio_total, estado, codigo_whatsapp, telefono_contacto, canal_origen, notas)
            VALUES (?, ?, NOW(), ?, ?, ?, 'pendiente', ?, ?, 'whatsapp', ?)
        ");
        $stmt->execute([$cid, $r['tour_id'], $fecha, $r['personas'], $total, $codigo, $r['telefono'], $notas]);
        $rid = $pdo->lastInsertId();
        
        $pdo->prepare("
            INSERT INTO whatsapp_conversations (phone_number, user_name, state, selected_tour_id, reservation_id, num_people, full_name)
            VALUES (?, ?, 'completed', ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE state='completed', reservation_id=?, updated_at=NOW()
        ")->execute([$phone, $r['nombre'], $r['tour_id'], $rid, $r['personas'], $r['nombre'], $rid]);
        
        $pdo->commit();
        return ['ok' => true, 'codigo' => $codigo, 'id' => $rid];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

function parsearFecha($txt) {
    $meses = ['enero'=>'01','febrero'=>'02','marzo'=>'03','abril'=>'04','mayo'=>'05','junio'=>'06',
              'julio'=>'07','agosto'=>'08','septiembre'=>'09','octubre'=>'10','noviembre'=>'11','diciembre'=>'12'];
    
    $txt = strtolower(trim($txt));
    foreach ($meses as $m => $n) {
        if (strpos($txt, $m) !== false) {
            preg_match('/(\d{1,2})/', $txt, $d);
            preg_match('/(\d{4})/', $txt, $a);
            if (!empty($d[1]) && !empty($a[1])) {
                return $a[1] . '-' . $n . '-' . str_pad($d[1], 2, '0', STR_PAD_LEFT);
            }
        }
    }
    return date('Y-m-d', strtotime('+7 days'));
}

function enviarContacto($phone) {
    $msg = "📞 *CONTACTO SERCOLTUR*\n\n";
    $msg .= "📍 Medellín, Colombia\n";
    $msg .= "📱 +57 300 123 4567\n";
    $msg .= "📧 info@sercoltur.com\n\n";
    $msg .= "🕒 Lun-Vie: 8am-6pm | Sáb: 9am-2pm";
    
    enviarBotones($phone, $msg, [
        ['id' => 'btn_tours', 'title' => '🏞️ Ver Tours'],
        ['id' => 'btn_menu', 'title' => '🏠 Menú']
    ]);
}

// ============ ENVÍO ============
function enviarTexto($phone, $msg) {
    enviarAPI(['messaging_product' => 'whatsapp', 'to' => $phone, 'type' => 'text', 'text' => ['body' => $msg]]);
}

function enviarBotones($phone, $msg, $btns) {
    $buttons = [];
    foreach ($btns as $b) {
        $buttons[] = ['type' => 'reply', 'reply' => ['id' => $b['id'], 'title' => mb_substr($b['title'], 0, 20)]];
    }
    
    enviarAPI([
        'messaging_product' => 'whatsapp',
        'to' => $phone,
        'type' => 'interactive',
        'interactive' => ['type' => 'button', 'body' => ['text' => $msg], 'action' => ['buttons' => $buttons]]
    ]);
}

function enviarAPI($data) {
    global $ACCESS_TOKEN, $PHONE_ID;
    
    $ch = curl_init("https://graph.facebook.com/v18.0/{$PHONE_ID}/messages");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $ACCESS_TOKEN, 'Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    logBot($code === 200 ? "✅ Enviado" : "❌ Error $code: $res");
}

// ============ SESIONES ============
function esSaludo($t) {
    foreach (['hola','hi','hello','buenas','hey','buenos dias','buenas tardes','buenas noches'] as $s) {
        if (strpos($t, $s) !== false) return true;
    }
    return false;
}

function getSesion($phone) {
    global $SESSIONS_DIR;
    $f = $SESSIONS_DIR . '/' . preg_replace('/\D/', '', $phone) . '.json';
    return file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
}

function setSesion($phone, $data) {
    global $SESSIONS_DIR;
    $f = $SESSIONS_DIR . '/' . preg_replace('/\D/', '', $phone) . '.json';
    file_put_contents($f, json_encode(array_merge(getSesion($phone), $data), JSON_UNESCAPED_UNICODE));
}

function limpiarSesion($phone) {
    global $SESSIONS_DIR;
    $f = $SESSIONS_DIR . '/' . preg_replace('/\D/', '', $phone) . '.json';
    if (file_exists($f)) unlink($f);
}

// ============ CONFIG ============
function mostrarConfig() {
    global $VERIFY_TOKEN;
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    $pdo = getDatabase();
    $bdOk = $pdo ? true : false;
    $stats = ['tours' => 0, 'reservas' => 0, 'napoles' => 'No'];
    
    if ($pdo) {
        try {
            $stats['tours'] = $pdo->query("SELECT COUNT(*) FROM tours WHERE activo=1")->fetchColumn();
            $stats['reservas'] = $pdo->query("SELECT COUNT(*) FROM reservas")->fetchColumn();
            $napoles = $pdo->query("SELECT COUNT(*) FROM tours WHERE activo=1 AND (nombre LIKE '%nápoles%' OR nombre LIKE '%napoles%')")->fetchColumn();
            $stats['napoles'] = $napoles >= 2 ? '✅ Sí (Básico + Safari)' : '❌ Faltan tours';
        } catch (Exception $e) {}
    }
    
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SERCOLTUR Bot</title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:system-ui;background:#1a1a2e;color:#fff;min-height:100vh;padding:20px}
    .c{max-width:500px;margin:auto}.card{background:rgba(255,255,255,.1);border-radius:16px;padding:20px;margin-bottom:16px}
    h1{text-align:center;margin-bottom:16px}.stats{display:flex;gap:12px;margin-bottom:16px}
    .stat{flex:1;background:rgba(0,217,165,.1);padding:12px;border-radius:8px;text-align:center}
    .stat b{font-size:24px;color:#00d9a5}code{display:block;background:#000;padding:10px;border-radius:6px;font-size:11px;margin:8px 0}
    .btn{display:inline-block;background:#e94560;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;margin:4px}
    .info{background:rgba(77,171,247,.1);padding:12px;border-radius:8px;margin:12px 0;font-size:13px}
    </style></head><body><div class="c">
    <div class="card"><h1>🤖 SERCOLTUR Bot</h1>
    <div class="stats"><div class="stat"><b>'.$stats['tours'].'</b><br>Tours</div><div class="stat"><b>'.$stats['reservas'].'</b><br>Reservas</div></div>
    <p>BD: '.($bdOk?'✅ Conectada':'❌ Error').'</p>
    <div class="info">🦁 Nápoles: '.$stats['napoles'].'</div></div>
    <div class="card"><p>URL:</p><code>'.$url.'</code><p>Token:</p><code>'.$VERIFY_TOKEN.'</code>
    <a href="?hub_verify_token='.$VERIFY_TOKEN.'&hub_challenge=TEST" class="btn">🧪 Probar</a>
    <a href="dashboard.php" class="btn">📊 Dashboard</a></div>
    </div></body></html>';
}
?>