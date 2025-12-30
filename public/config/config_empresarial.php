<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * CONFIGURACIÓN EMPRESARIAL - SERCOLTURBOT
 * Agregar este archivo a tu carpeta config/
 * ═══════════════════════════════════════════════════════════════
 */

return [
    // ========== DATOS DEL NEGOCIO ==========
    'negocio' => [
        'nombre' => 'SERCOLTUR',
        'telefono' => '+57 302 253 1580',
        'email' => 'info@sercoltur.com',
        'website' => 'https://sercoltur.com',
    ],
    
    // ========== HORARIOS DE ATENCIÓN ==========
    'horarios' => [
        'habilitado' => true,
        'zona_horaria' => 'America/Bogota',
        'dias' => [
            0 => ['nombre' => 'Domingo',   'inicio' => '08:00', 'fin' => '16:00', 'activo' => true],
            1 => ['nombre' => 'Lunes',     'inicio' => '07:00', 'fin' => '20:00', 'activo' => true],
            2 => ['nombre' => 'Martes',    'inicio' => '07:00', 'fin' => '20:00', 'activo' => true],
            3 => ['nombre' => 'Miércoles', 'inicio' => '07:00', 'fin' => '20:00', 'activo' => true],
            4 => ['nombre' => 'Jueves',    'inicio' => '07:00', 'fin' => '20:00', 'activo' => true],
            5 => ['nombre' => 'Viernes',   'inicio' => '07:00', 'fin' => '20:00', 'activo' => true],
            6 => ['nombre' => 'Sábado',    'inicio' => '08:00', 'fin' => '18:00', 'activo' => true],
        ],
        'mensaje_fuera_horario' => "⏰ *Estamos fuera de horario*\n\n📅 Nuestro horario:\n• Lun-Vie: 7am - 8pm\n• Sábado: 8am - 6pm\n• Domingo: 8am - 4pm\n\n📝 Deja tu mensaje y te contactaremos.\n\n🚌 *SERCOLTUR*",
    ],
    
    // ========== OPENAI (IA) ==========
    'openai' => [
        'habilitado' => false, // Cambiar a true y agregar API key para activar
        'api_key' => '', // Tu API key de OpenAI
        'modelo' => 'gpt-4o-mini',
        'max_tokens' => 500,
        'temperatura' => 0.7,
        'contexto_sistema' => "Eres el asistente virtual de SERCOLTUR, una empresa de tours en Medellín, Colombia. Responde en español de manera amable y concisa.",
    ],
    
    // ========== GOOGLE CALENDAR ==========
    'google_calendar' => [
        'habilitado' => false, // Cambiar a true cuando tengas las credenciales
        'credentials_path' => __DIR__ . '/google_credentials.json',
        'calendar_id' => '',
    ],
    
    // ========== PAGOS WOMPI ==========
    'wompi' => [
        'habilitado' => false,
        'ambiente' => 'sandbox', // 'sandbox' o 'production'
        'public_key' => '',
        'private_key' => '',
    ],
    
    // ========== EMAIL ==========
    'email' => [
        'habilitado' => false,
        'host' => 'smtp.gmail.com',
        'puerto' => 587,
        'usuario' => '',
        'password' => '',
        'from_email' => 'noreply@sercoltur.com',
        'from_name' => 'SERCOLTUR',
    ],
    
    // ========== WHATSAPP, FACEBOOK, INSTAGRAM ==========
    // Nota: Las credenciales se leen de variables de entorno para seguridad
    'whatsapp' => [
        'habilitado' => true,
        'phone_number_id' => $_ENV['925480580639940'] ?? '', // ID del número de teléfono WhatsApp Business
        'access_token' => $_ENV['EAA9SPy8AxVcBQegZC5kuPw8QbofJqns78aZAZA7ilv1BeaZCPNf5JXPdrCTCWCFesegSu2OLYynsZAP8UZC5eNWCP9uZAMY8to2FtZBqwZCmEOw2PvdvUEIBjWYtlabPQ92KZBQNJF0PafhtyjyTCVpwWGMcClb4Xmdv0mVsrbJK76A4mRQtwjjbPWCh0RJfioI0pKtUtK2vDJB9gS4yJhei7mFEz7CG3Klgfm96INcZBdgnH1eTbOcZBZCzuXP5G8ZCCnm4zKwdv032iZBKKHRHSTCM9FL5SxxpvBIiznd7wZDZD'] ?? '', // Token de acceso de Meta
    ],
    'facebook' => [
        'habilitado' => false,
        'page_access_token' => $_ENV['FACEBOOK_PAGE_ACCESS_TOKEN'] ?? '',
    ],
    'instagram' => [
        'habilitado' => false,
        'business_account_id' => $_ENV['INSTAGRAM_BUSINESS_ACCOUNT_ID'] ?? '',
        'access_token' => $_ENV['INSTAGRAM_ACCESS_TOKEN'] ?? '',
    ],
    
    // ========== CONFIGURACIÓN BOT ==========
    'bot' => [
        'usar_ia_para_desconocidos' => false, // true para usar IA cuando no entiende
        'transferir_a_humano_despues_de' => 3, // Intentos antes de transferir
    ],
];