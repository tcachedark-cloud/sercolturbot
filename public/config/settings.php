<?php
/**
 * CONFIGURACIÓN SERCOLTURBOT
 * Edita este archivo para personalizar el sistema
 */

// ================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sercolturbot');
define('DB_USER', 'root');
define('DB_PASS', 'C121672@c');
define('DB_CHARSET', 'utf8mb4');

// ================================================
// CONFIGURACIÓN DEL BOT
// ================================================

// Mensajes de bienvenida
define('BOT_WELCOME', '👋 ¡Hola! Soy tu asistente virtual SERCOLTURBOT. ¿En qué puedo ayudarte?');

// Timeout de respuesta en segundos
define('BOT_TIMEOUT', 5);

// Habilitar logs
define('BOT_LOG_ENABLED', true);
define('BOT_LOG_PATH', __DIR__ . '/logs/bot.log');

// ================================================
// RESPUESTAS DEL BOT (PERSONALIZABLE)
// ================================================
$BOT_RESPONSES = [
    'reservas' => "¡Hola! 🎫 Me gustaría ayudarte con tu reserva. ¿En qué tour estás interesado? Tenemos:\n- Cartagena Clásica (3 días)\n- Santa Marta y Tayrona (4 días)\n- Bogotá Imperial (2 días)\n\n¿Cuántas personas son?",
    
    'guias' => "¡Perfecto! 👨‍🏫 Contamos con guías profesionales multilingües con amplia experiencia. Nuestros guías hablan:\n- Español\n- Inglés\n- Francés\n- Portugués\n\n¿Cuál es tu destino preferido?",
    
    'buses' => "🚌 Para tu viaje contamos con buses cómodos y seguros con capacidad de 35 a 50 pasajeros, aire acondicionado y servicios de WiFi. ¿Cuándo planeas viajar?",
    
    'asesoria' => "👨‍💼 Un asesor se comunicará contigo en breve. Mientras tanto, puedo ayudarte con información sobre nuestros tours y servicios. ¿En qué puedo ayudarte?",
    
    'disponibilidad' => "📅 Para verificar disponibilidad de fechas, por favor indícame:\n1. El tour que te interesa\n2. La fecha deseada\n3. Número de personas",
    
    'general' => "¡Hola! 👋 Bienvenido a SERCOLTURBOT. Soy tu asistente virtual. Puedo ayudarte con:\n- 🎫 Reservas de tours\n- 👨‍🏫 Información de guías\n- 🚌 Detalles de transporte\n- 📅 Disponibilidad\n- 👨‍💼 Conectarte con un asesor\n\n¿En qué puedo ayudarte?"
];

// ================================================
// PALABRAS CLAVE POR TIPO DE CONSULTA
// ================================================
$BOT_KEYWORDS = [
    'reservas' => ['reserv', 'booking', 'tour', 'paquete', 'precio', 'costo', 'tarifa'],
    'guias' => ['guia', 'acompañante', 'tour guide', 'idioma', 'experiencia'],
    'buses' => ['bus', 'transporte', 'viaje', 'salida', 'vehículo', 'capacidad'],
    'asesoria' => ['asesor', 'ayuda', 'soporte', 'atencion', 'hablar', 'persona'],
    'disponibilidad' => ['disponibilidad', 'disponible', 'cuando', 'fecha', 'horario']
];

// ================================================
// CONFIGURACIÓN DE SEGURIDAD
// ================================================

// Habilitar CORS
define('CORS_ENABLED', true);
define('CORS_ORIGINS', ['*']); // Cambiar a dominios específicos en producción

// Validación de parámetros
define('VALIDATE_INPUTS', true);

// ================================================
// CONFIGURACIÓN DE EMPRESA
// ================================================
define('COMPANY_NAME', 'SERCOLTURBOT');
define('COMPANY_EMAIL', 'info@sercolturbot.com');
define('COMPANY_PHONE', '+57 300 000 0000');
define('COMPANY_ADDRESS', 'Bogotá, Colombia');

// ================================================
// CONFIGURACIÓN DE TOURS (EDITA SEGÚN TU OFERTA)
// ================================================
$TOURS_DEFAULT = [
    [
        'nombre' => 'Cartagena Clásica',
        'precio' => 450,
        'duracion' => 3,
        'destino' => 'Cartagena',
        'descripcion' => 'Tour de 3 días por Cartagena con playas y turismo cultural'
    ],
    [
        'nombre' => 'Santa Marta y Tayrona',
        'precio' => 650,
        'duracion' => 4,
        'destino' => 'Santa Marta',
        'descripcion' => 'Aventura en la Sierra Nevada y Parque Tayrona'
    ],
    [
        'nombre' => 'Bogotá Imperial',
        'precio' => 350,
        'duracion' => 2,
        'destino' => 'Bogotá',
        'descripcion' => 'Recorrido histórico por la capital colombiana'
    ]
];

// ================================================
// ZONA HORARIA
// ================================================
define('TIMEZONE', 'America/Bogota');
date_default_timezone_set(TIMEZONE);

// ================================================
// FUNCIONES ÚTILES
// ================================================

/**
 * Obtener conexión a BD
 */
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Error de conexión a BD: " . $e->getMessage());
    }
}

/**
 * Registrar en log
 */
function logBot($mensaje, $tipo = 'INFO') {
    if (BOT_LOG_ENABLED) {
        $timestamp = date('Y-m-d H:i:s');
        $log = "[$timestamp] [$tipo] $mensaje\n";
        @file_put_contents(BOT_LOG_PATH, $log, FILE_APPEND);
    }
}

/**
 * Responder JSON
 */
function respondJSON($success, $data = null, $error = null) {
    header('Content-Type: application/json; charset=utf-8');
    $response = [
        'success' => $success,
        'data' => $data,
        'error' => $error,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validar parámetro requerido
 */
function requireParam($param, $name = null) {
    $value = $_POST[$param] ?? $_GET[$param] ?? null;
    if (!$value) {
        respondJSON(false, null, "Parámetro requerido: " . ($name ?? $param));
    }
    return $value;
}

?>
