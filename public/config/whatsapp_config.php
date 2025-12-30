<?php
/**
 * CONFIGURACIÓN META WHATSAPP API
 * Credenciales y configuración para integración
 */

// Meta WhatsApp Business API Credentials
define('META_PHONE_NUMBER_ID', 'TU_PHONE_NUMBER_ID'); // Obtener de Meta
define('META_BUSINESS_ACCOUNT_ID', 'TU_BUSINESS_ACCOUNT_ID');
define('META_ACCESS_TOKEN', 'TU_ACCESS_TOKEN'); // Token de acceso
define('META_WEBHOOK_TOKEN', 'TU_WEBHOOK_VERIFY_TOKEN'); // Token de verificación

// URLs de API
define('META_API_VERSION', 'v18.0');
define('META_API_URL', 'https://graph.instagram.com/' . META_API_VERSION . '/');
define('META_SEND_MESSAGE_URL', META_API_URL . META_PHONE_NUMBER_ID . '/messages');

// Configuración del Bot
define('BOT_NAME', 'SERCOLTURBOT WhatsApp');
define('BOT_WELCOME_MESSAGE', '👋 ¡Hola! Bienvenido a SERCOLTURBOT. Soy tu asistente de viajes. ¿En qué puedo ayudarte?');

// Estados de Conversación
define('STATE_INITIAL', 'initial');
define('STATE_SELECTING_TOUR', 'selecting_tour');
define('STATE_SELECTING_DATE', 'selecting_date');
define('STATE_ENTERING_PEOPLE', 'entering_people');
define('STATE_ENTERING_NAME', 'entering_name');
define('STATE_ENTERING_EMAIL', 'entering_email');
define('STATE_ENTERING_PHONE', 'entering_phone');
define('STATE_PAYMENT_INFO', 'payment_info');
define('STATE_CONFIRMING_RESERVATION', 'confirming_reservation');
define('STATE_COMPLETED', 'completed');

// Logs
define('WHATSAPP_LOG_PATH', __DIR__ . '/../logs/whatsapp.log');

/**
 * Loguear actividad de WhatsApp
 */
function logWhatsApp($mensaje, $tipo = 'INFO', $data = []) {
    $timestamp = date('Y-m-d H:i:s');
    $log = "[$timestamp] [$tipo] $mensaje";
    if (!empty($data)) {
        $log .= ' | ' . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    $log .= "\n";
    @file_put_contents(WHATSAPP_LOG_PATH, $log, FILE_APPEND);
}

/**
 * Enviar mensaje a WhatsApp
 */
function sendWhatsAppMessage($phone_number, $message) {
    $url = META_SEND_MESSAGE_URL;
    
    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $phone_number,
        'type' => 'text',
        'text' => [
            'body' => $message
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . META_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    logWhatsApp('Mensaje enviado', 'SEND', ['phone' => $phone_number, 'code' => $httpCode]);
    
    return $httpCode === 200;
}

/**
 * Enviar mensaje con botones
 */
function sendWhatsAppMessageWithButtons($phone_number, $header, $body, $buttons) {
    $url = META_SEND_MESSAGE_URL;
    
    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $phone_number,
        'type' => 'interactive',
        'interactive' => [
            'type' => 'button',
            'header' => ['type' => 'text', 'text' => $header],
            'body' => ['text' => $body],
            'action' => ['buttons' => $buttons]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . META_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    logWhatsApp('Mensaje con botones enviado', 'SEND', ['phone' => $phone_number]);
    
    return $httpCode === 200;
}

/**
 * Enviar lista de opciones
 */
function sendWhatsAppList($phone_number, $header, $body, $sections) {
    $url = META_SEND_MESSAGE_URL;
    
    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $phone_number,
        'type' => 'interactive',
        'interactive' => [
            'type' => 'list',
            'header' => ['type' => 'text', 'text' => $header],
            'body' => ['text' => $body],
            'action' => ['sections' => $sections]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . META_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    logWhatsApp('Lista enviada', 'SEND', ['phone' => $phone_number]);
    
    return $httpCode === 200;
}

?>
