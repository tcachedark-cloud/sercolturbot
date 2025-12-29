<?php
/**
 * WhatsAppBot - Bot completo para WhatsApp Business API
 */

class WhatsAppBot {
    
    private $pdo;
    private $phone_id;
    private $access_token;
    private $api_url;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Configuración de WhatsApp API
        $this->phone_id = '925480580639940';
        $this->access_token = 'EAA9SPy8AxVcBQaCMYd1lcfHapCX2Je6ZATObL8ElSqbAVdVJNl70tbf4B92MhEgUEXAfl0ZB2DyrnZCRSYq9GnCX5YmQZB4VVVOD3jBI7jiGHuDoXbg5E4DMMn4PsR9epSx3xvwTynRDUnWmLDuZCAD6DjxOXxhviyyU0XnKZAS6EhdyFdSm1YlH9pLgIZBhHgwae2dwMXIrArIWocNzR9QYvdXcPBjZCpFSQG8u4qYsHXRkN8DlzEAZA6a14zk7p1TDxSOBXGjtHeWvJ583E4Uvibl7JV38SOLWgiAZDZD';
        $this->api_url = 'https://graph.facebook.com/v18.0/';
        
        $this->log("🤖 WhatsAppBot inicializado");
    }
    
    /**
     * Procesar webhook de WhatsApp
     */
    public function procesarWebhook($data) {
        $this->log("📨 Webhook recibido: " . json_encode($data));
        
        if (!isset($data['entry'])) {
            return false;
        }
        
        foreach ($data['entry'] as $entry) {
            if (isset($entry['changes'])) {
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        $value = $change['value'];
                        
                        if (isset($value['messages']) && is_array($value['messages'])) {
                            foreach ($value['messages'] as $message) {
                                $this->procesarMensaje($message, $value['contacts'][0] ?? null);
                            }
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Procesar un mensaje individual
     */
    public function procesarMensaje($message, $contact = null) {
        $telefono = $message['from'];
        $user_name = $contact['profile']['name'] ?? 'Usuario';
        $mensaje = $this->extraerTextoMensaje($message);
        $mensaje_lower = strtolower(trim($mensaje));
        
        $this->log("📲 Mensaje de $telefono ($user_name): $mensaje");
        
        // Guardar mensaje en base de datos
        $this->guardarMensaje($telefono, $user_name, $mensaje, 'incoming');
        
        // Detectar si es saludo
        if ($this->esSaludo($mensaje_lower) || $mensaje_lower === '') {
            $this->enviarBienvenida($telefono, $user_name);
            return;
        }
        
        // Obtener sesión actual
        $sesion = $this->obtenerSesion($telefono);
        
        // Si no hay sesión, crear una
        if (!$sesion) {
            $this->crearSesion($telefono);
            $this->enviarMenuPrincipal($telefono);
            return;
        }
        
        // Procesar según el paso actual
        $this->procesarPorPaso($telefono, $mensaje_lower, $sesion['paso']);
    }
    
    /**
     * Extraer texto del mensaje según tipo
     */
    private function extraerTextoMensaje($message) {
        $tipo = $message['type'];
        
        if ($tipo === 'text') {
            return $message['text']['body'];
        } elseif ($tipo === 'interactive') {
            if (isset($message['interactive']['button_reply']['id'])) {
                return $message['interactive']['button_reply']['id'];
            }
        }
        
        return '';
    }
    
    /**
     * Detectar si es saludo
     */
    private function esSaludo($mensaje) {
        $saludos = [
            'hola', 'hol', 'hi', 'hello', 
            'buenas', 'buenos días', 'buenas tardes', 'buenas noches',
            'saludos', 'qué tal', 'que tal', 'qué onda',
            'buen día', 'buendia', 'hey', 'oye',
            'ho', 'hoa', 'hoka', 'ola'
        ];
        
        foreach ($saludos as $saludo) {
            if (strpos($mensaje, $saludo) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Enviar mensaje de bienvenida
     */
    private function enviarBienvenida($telefono, $user_name) {
        $saludo = $this->obtenerSaludoPorHora();
        
        $mensaje = $saludo . " *" . $user_name . "*! 👋\n\n";
        $mensaje .= "¡Bienvenido/a a *SERCOLTUR*! 🚌✨\n\n";
        $mensaje .= "Somos expertos en tours y experiencias en Medellín y sus alrededores.\n\n";
        $mensaje .= "¿En qué puedo ayudarte hoy?";
        
        $this->enviarMensaje($telefono, $mensaje);
        
        // Esperar 1 segundo y enviar menú
        sleep(1);
        $this->enviarMenuPrincipal($telefono);
        
        // Actualizar sesión
        $this->actualizarSesion($telefono, 'menu_principal');
    }
    
    /**
     * Enviar menú principal
     */
    private function enviarMenuPrincipal($telefono) {
        $mensaje = "📋 *MENÚ PRINCIPAL* 📋\n\n";
        $mensaje .= "Selecciona una categoría:\n\n";
        $mensaje .= "1️⃣ *GUATAPÉ* 🏞️\n";
        $mensaje .= "   Tours a Guatapé y Piedra del Peñol\n\n";
        $mensaje .= "2️⃣ *CITY TOURS* 🏙️\n";
        $mensaje .= "   Recorridos por Medellín\n\n";
        $mensaje .= "3️⃣ *AVENTURA* 🔥\n";
        $mensaje .= "   Tours extremos y naturaleza\n\n";
        $mensaje .= "4️⃣ *ESPECIALES* ✨\n";
        $mensaje .= "   Tours navideños y temáticos\n\n";
        $mensaje .= "Escribe el número (1, 2, 3, 4) o el nombre de la categoría que te interesa.";
        
        $this->enviarMensaje($telefono, $mensaje);
        $this->actualizarSesion($telefono, 'menu_principal');
    }
    
    /**
     * Procesar según el paso actual
     */
    private function procesarPorPaso($telefono, $mensaje, $paso_actual) {
        switch ($paso_actual) {
            case 'menu_principal':
                $this->procesarMenuPrincipal($telefono, $mensaje);
                break;
                
            case 'categoria_guatape':
                $this->procesarCategoriaGuatape($telefono, $mensaje);
                break;
                
            case 'categoria_citytours':
                $this->procesarCategoriaCityTours($telefono, $mensaje);
                break;
                
            case 'categoria_aventura':
                $this->procesarCategoriaAventura($telefono, $mensaje);
                break;
                
            case 'categoria_especiales':
                $this->procesarCategoriaEspeciales($telefono, $mensaje);
                break;
                
            case 'detalle_tour':
                $this->procesarDetalleTour($telefono, $mensaje);
                break;
                
            default:
                $this->enviarMenuPrincipal($telefono);
        }
    }
    
    /**
     * Procesar menú principal
     */
    private function procesarMenuPrincipal($telefono, $mensaje) {
        $categorias = [
            '1' => 'guatape',
            'guatape' => 'guatape',
            'guatapé' => 'guatape',
            '2' => 'citytours',
            'city' => 'citytours',
            'city tours' => 'citytours',
            'medellin' => 'citytours',
            '3' => 'aventura',
            'aventura' => 'aventura',
            '4' => 'especiales',
            'especiales' => 'especiales',
            'navideño' => 'especiales',
            'menu' => 'menu',
            'inicio' => 'menu'
        ];
        
        $opcion = $categorias[$mensaje] ?? null;
        
        if ($opcion === 'guatape') {
            $this->mostrarToursGuatape($telefono);
            $this->actualizarSesion($telefono, 'categoria_guatape');
            
        } elseif ($opcion === 'citytours') {
            $this->mostrarToursCityTours($telefono);
            $this->actualizarSesion($telefono, 'categoria_citytours');
            
        } elseif ($opcion === 'aventura') {
            $this->mostrarToursAventura($telefono);
            $this->actualizarSesion($telefono, 'categoria_aventura');
            
        } elseif ($opcion === 'especiales') {
            $this->mostrarToursEspeciales($telefono);
            $this->actualizarSesion($telefono, 'categoria_especiales');
            
        } elseif ($opcion === 'menu') {
            $this->enviarMenuPrincipal($telefono);
            
        } else {
            $this->enviarMensaje($telefono, 
                "❌ Opción no válida.\n\n" .
                "Por favor selecciona:\n" .
                "1. GUATAPÉ\n2. CITY TOURS\n3. AVENTURA\n4. ESPECIALES\n\n" .
                "O escribe MENU para ver el menú principal."
            );
        }
    }
    
    /**
     * Mostrar tours de Guatapé
     */
    private function mostrarToursGuatape($telefono) {
        $mensaje = "🏞️ *TOURS A GUATAPÉ* 🏞️\n\n";
        $mensaje .= "1️⃣ *Tour Guatapé Básico*\n";
        $mensaje .= "   💰 $109.000 por persona\n";
        $mensaje .= "   🕐 7:00 am - 7:20 am salida\n";
        $mensaje .= "   📍 Parque del Poblado / Estación Estadio\n\n";
        
        $mensaje .= "2️⃣ *Paquete Vibrante*\n";
        $mensaje .= "   💰 $195.000 por persona\n";
        $mensaje .= "   🕐 8:30 am - 9:00 am salida\n";
        $mensaje .= "   📍 Medellín + Guatapé completo\n\n";
        
        $mensaje .= "Escribe el número del tour (1 o 2) para más detalles.\n";
        $mensaje .= "O escribe MENU para volver al inicio.";
        
        $this->enviarMensaje($telefono, $mensaje);
    }
    
    /**
     * Procesar categoría Guatapé
     */
    private function procesarCategoriaGuatape($telefono, $mensaje) {
        if ($mensaje === '1') {
            $this->mostrarDetalleTour($telefono, 'guatape_basico');
            $this->actualizarSesion($telefono, 'detalle_tour');
        } elseif ($mensaje === '2') {
            $this->mostrarDetalleTour($telefono, 'paquete_vibrante');
            $this->actualizarSesion($telefono, 'detalle_tour');
        } elseif ($mensaje === 'menu') {
            $this->enviarMenuPrincipal($telefono);
        } else {
            $this->mostrarToursGuatape($telefono);
        }
    }
    
    /**
     * Mostrar City Tours
     */
    private function mostrarToursCityTours($telefono) {
        $mensaje = "🏙️ *CITY TOURS MEDELLÍN* 🏙️\n\n";
        $mensaje .= "1️⃣ *City Tour Medellín*\n";
        $mensaje .= "   💰 $65.000 por persona\n";
        $mensaje .= "   🕐 8:30 am - 9:00 am\n\n";
        
        $mensaje .= "2️⃣ *Solo Comuna 13*\n";
        $mensaje .= "   💰 $70.000 por persona\n";
        $mensaje .= "   🕐 8:00 am - 8:30 am\n\n";
        
        $mensaje .= "3️⃣ *City + Comuna 13*\n";
        $mensaje .= "   💰 $99.000 por persona\n";
        $mensaje .= "   🕐 8:00 am - 8:30 am\n\n";
        
        $mensaje .= "4️⃣ *Chiva Rumbera*\n";
        $mensaje .= "   💰 $65.000 por persona\n";
        $mensaje .= "   🕐 7:00 pm - 7:30 pm\n\n";
        
        $mensaje .= "Escribe el número del tour (1-4) para más detalles.\n";
        $mensaje .= "O escribe MENU para volver al inicio.";
        
        $this->enviarMensaje($telefono, $mensaje);
    }
    
    /**
     * Procesar categoría City Tours
     */
    private function procesarCategoriaCityTours($telefono, $mensaje) {
        if (in_array($mensaje, ['1', '2', '3', '4'])) {
            $tour_nombres = [
                '1' => 'city_tour',
                '2' => 'solo_comuna13',
                '3' => 'city_comuna13',
                '4' => 'chiva_rumbera'
            ];
            
            $this->mostrarDetalleTour($telefono, $tour_nombres[$mensaje]);
            $this->actualizarSesion($telefono, 'detalle_tour');
            
        } elseif ($mensaje === 'menu') {
            $this->enviarMenuPrincipal($telefono);
        } else {
            $this->mostrarToursCityTours($telefono);
        }
    }
    
    /**
     * Mostrar tours de Aventura
     */
    private function mostrarToursAventura($telefono) {
        $mensaje = "🔥 *TOURS DE AVENTURA* 🔥\n\n";
        $mensaje .= "1️⃣ *Tour del Parapente*\n";
        $mensaje .= "   💰 $350.000 por persona\n";
        $mensaje .= "   🕐 9:00 am - 4:30 pm\n\n";
        
        $mensaje .= "2️⃣ *Tour a Río Claro*\n";
        $mensaje .= "   💰 $220.000 por persona\n";
        $mensaje .= "   🕐 4:00 am - 4:30 am\n\n";
        
        $mensaje .= "3️⃣ *Tour a Jardín Antioquia*\n";
        $mensaje .= "   💰 $130.000 por persona\n";
        $mensaje .= "   🕐 5:00 am\n\n";
        
        $mensaje .= "Escribe el número del tour (1-3) para más detalles.\n";
        $mensaje .= "O escribe MENU para volver al inicio.";
        
        $this->enviarMensaje($telefono, $mensaje);
    }
    
    /**
     * Procesar categoría Aventura
     */
    private function procesarCategoriaAventura($telefono, $mensaje) {
        if (in_array($mensaje, ['1', '2', '3'])) {
            $tour_nombres = [
                '1' => 'parapente',
                '2' => 'rio_claro',
                '3' => 'jardin'
            ];
            
            $this->mostrarDetalleTour($telefono, $tour_nombres[$mensaje]);
            $this->actualizarSesion($telefono, 'detalle_tour');
            
        } elseif ($mensaje === 'menu') {
            $this->enviarMenuPrincipal($telefono);
        } else {
            $this->mostrarToursAventura($telefono);
        }
    }
    
    /**
     * Mostrar tours Especiales
     */
    private function mostrarToursEspeciales($telefono) {
        $mensaje = "✨ *TOURS ESPECIALES* ✨\n\n";
        $mensaje .= "1️⃣ *Tour Navideño*\n";
        $mensaje .= "   💰 $65.000 por persona\n";
        $mensaje .= "   🎄 Hasta enero 2026\n\n";
        
        $mensaje .= "2️⃣ *Hacienda Nápoles + Santorini*\n";
        $mensaje .= "   💰 $228.000 - $269.000\n";
        $mensaje .= "   🦁 Safari y parque temático\n\n";
        
        $mensaje .= "Escribe el número del tour (1 o 2) para más detalles.\n";
        $mensaje .= "O escribe MENU para volver al inicio.";
        
        $this->enviarMensaje($telefono, $mensaje);
    }
    
    /**
     * Procesar categoría Especiales
     */
    private function procesarCategoriaEspeciales($telefono, $mensaje) {
        if ($mensaje === '1') {
            $this->mostrarDetalleTour($telefono, 'navideno');
            $this->actualizarSesion($telefono, 'detalle_tour');
        } elseif ($mensaje === '2') {
            $this->mostrarDetalleTour($telefono, 'hacienda_napoles');
            $this->actualizarSesion($telefono, 'detalle_tour');
        } elseif ($mensaje === 'menu') {
            $this->enviarMenuPrincipal($telefono);
        } else {
            $this->mostrarToursEspeciales($telefono);
        }
    }
    
    /**
     * Mostrar detalle de un tour específico
     */
    private function mostrarDetalleTour($telefono, $tour_id) {
        $detalles = $this->obtenerDetalleTour($tour_id);
        
        if (!$detalles) {
            $this->enviarMensaje($telefono, "⚠️ Tour no encontrado. Por favor selecciona otra opción.");
            $this->enviarMenuPrincipal($telefono);
            return;
        }
        
        $mensaje = $detalles['mensaje'];
        $this->enviarMensaje($telefono, $mensaje);
        
        // Enviar opciones después del detalle
        sleep(1);
        $this->enviarOpcionesPostTour($telefono);
    }
    
    /**
     * Obtener detalle de un tour
     */
    private function obtenerDetalleTour($tour_id) {
        $tours = [
            'guatape_basico' => [
                'mensaje' => "🏞️ *TOUR A GUATAPÉ - BÁSICO*\n\n" .
                           "💰 *Precio:* $109.000 por persona\n\n" .
                           "📍 *Salida:*\n" .
                           "• Parque del Poblado (7:00 am)\n" .
                           "• Estación Estadio del Metro (7:20 am)\n\n" .
                           "✅ *Incluye:*\n" .
                           "• Transporte ida y regreso\n" .
                           "• Desayuno y almuerzo\n" .
                           "• Paseo en barco rumbero\n" .
                           "• Visita a Piedra del Peñol (exterior)\n" .
                           "• Municipio de Guatapé\n" .
                           "• Guía acompañante\n\n" .
                           "📞 *Para reservar:*\n" .
                           "Escribe RESERVAR o llama al +57 300 123 4567"
            ],
            
            'city_tour' => [
                'mensaje' => "🏙️ *CITY TOUR MEDELLÍN*\n\n" .
                           "💰 *Precio:* $65.000 por persona\n\n" .
                           "📍 *Salida:*\n" .
                           "• Estación Estadio del Metro\n" .
                           "• Parque del Poblado\n" .
                           "🕐 8:30 am - 9:00 am\n\n" .
                           "✅ *Incluye:*\n" .
                           "• Transporte\n" .
                           "• Visita a principales atracciones\n" .
                           "• Guía acompañante\n" .
                           "• Tarjeta de asistencia médica\n\n" .
                           "📞 *Para reservar:*\n" .
                           "Escribe RESERVAR o llama al +57 300 123 4567"
            ],
            
            'navideno' => [
                'mensaje' => "🎄 *TOUR NAVIDEÑO*\n\n" .
                           "💰 *Precio:* $65.000 por persona\n" .
                           "📅 *Disponible hasta:* Enero 2026\n\n" .
                           "📍 *Salida:* Estación Estadio\n" .
                           "⏰ *Horario:* Nocturno\n\n" .
                           "✅ *Incluye:*\n" .
                           "• Transporte ida y regreso\n" .
                           "• Degustación de licor (mayores)\n" .
                           "• Música y ambiente familiar\n" .
                           "• Recorrido por alumbrados\n" .
                           "• Asistencia médica\n\n" .
                           "📞 *Para reservar:*\n" .
                           "Escribe RESERVAR o llama al +57 300 123 4567"
            ]
        ];
        
        return $tours[$tour_id] ?? null;
    }
    
    /**
     * Enviar opciones después de ver un tour
     */
    private function enviarOpcionesPostTour($telefono) {
        $mensaje = "🎯 *¿QUÉ DESEAS HACER?*\n\n";
        $mensaje .= "1️⃣ *RESERVAR* - Reservar este tour\n";
        $mensaje .= "2️⃣ *OTRO TOUR* - Ver otro tour\n";
        $mensaje .= "3️⃣ *MENU* - Volver al menú principal\n\n";
        $mensaje .= "Escribe tu opción (1, 2 o 3).";
        
        $this->enviarMensaje($telefono, $mensaje);
    }
    
    /**
     * Procesar detalle de tour
     */
    private function procesarDetalleTour($telefono, $mensaje) {
        if (strpos($mensaje, 'reservar') !== false || $mensaje === '1') {
            $this->iniciarReserva($telefono);
        } elseif (strpos($mensaje, 'otro') !== false || $mensaje === '2') {
            $this->enviarMenuPrincipal($telefono);
        } elseif ($mensaje === 'menu' || $mensaje === '3') {
            $this->enviarMenuPrincipal($telefono);
        } else {
            $this->enviarOpcionesPostTour($telefono);
        }
    }
    
    /**
     * Iniciar proceso de reserva
     */
    private function iniciarReserva($telefono) {
        $mensaje = "📅 *INICIAR RESERVA*\n\n";
        $mensaje .= "¡Perfecto! 🎉 Para procesar tu reserva necesitamos:\n\n";
        $mensaje .= "1. *Nombre completo*\n";
        $mensaje .= "2. *Fecha deseada* (DD/MM/AAAA)\n";
        $mensaje .= "3. *Número de personas*\n\n";
        $mensaje .= "Por favor envía:\n";
        $mensaje .= "👉 *Nombre, fecha, personas*\n\n";
        $mensaje .= "Ejemplo: *Juan Pérez, 15/01/2025, 4 personas*";
        
        $this->enviarMensaje($telefono, $mensaje);
        $this->actualizarSesion($telefono, 'reserva_paso1');
    }
    
    /**
     * Obtener saludo según hora
     */
    private function obtenerSaludoPorHora() {
        $hora = (int)date('H');
        
        if ($hora >= 5 && $hora < 12) {
            return "¡Buenos días";
        } elseif ($hora >= 12 && $hora < 19) {
            return "¡Buenas tardes";
        } else {
            return "¡Buenas noches";
        }
    }
    
    /**
     * Gestión de sesiones
     */
    private function obtenerSesion($telefono) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM bot_sesiones WHERE telefono = ?");
            $stmt->execute([$telefono]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->log("❌ Error obteniendo sesión: " . $e->getMessage());
            return false;
        }
    }
    
    private function crearSesion($telefono) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bot_sesiones (telefono, paso) 
                VALUES (?, 'menu_principal')
            ");
            $stmt->execute([$telefono]);
            $this->log("✅ Sesión creada para $telefono");
        } catch (Exception $e) {
            $this->log("⚠️ Error creando sesión: " . $e->getMessage());
        }
    }
    
    private function actualizarSesion($telefono, $paso, $datos = null) {
        try {
            $datos_json = $datos ? json_encode($datos) : null;
            
            $stmt = $this->pdo->prepare("
                UPDATE bot_sesiones 
                SET paso = ?, datos = ?, fecha_actualizacion = NOW() 
                WHERE telefono = ?
            ");
            $stmt->execute([$paso, $datos_json, $telefono]);
            
            $this->log("📊 Sesión actualizada: $telefono -> $paso");
        } catch (Exception $e) {
            $this->log("⚠️ Error actualizando sesión: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar mensaje por WhatsApp API
     */
    public function enviarMensaje($telefono, $mensaje) {
        $url = $this->api_url . $this->phone_id . '/messages';
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $telefono,
            'type' => 'text',
            'text' => [
                'body' => $mensaje
            ]
        ];
        
        $this->log("📤 Enviando mensaje a $telefono: " . substr($mensaje, 0, 100) . "...");
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->access_token,
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $this->log("❌ Error enviando mensaje: " . curl_error($ch));
        } else {
            $this->log("📥 Respuesta API: HTTP $http_code");
            $this->guardarMensaje($telefono, 'Bot', $mensaje, 'outgoing');
        }
        
        curl_close($ch);
        
        return $http_code >= 200 && $http_code < 300;
    }
    
    /**
     * Guardar mensaje en base de datos
     */
    private function guardarMensaje($telefono, $user_name, $mensaje, $direccion) {
        try {
            // Buscar o crear conversación
            $stmt = $this->pdo->prepare("
                SELECT id FROM whatsapp_conversations 
                WHERE phone_number = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$telefono]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$conversation) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO whatsapp_conversations 
                    (phone_number, user_name, state, created_at, updated_at) 
                    VALUES (?, ?, 'active', NOW(), NOW())
                ");
                $stmt->execute([$telefono, $user_name]);
                $conversation_id = $this->pdo->lastInsertId();
            } else {
                $conversation_id = $conversation['id'];
            }
            
            // Guardar mensaje
            $stmt = $this->pdo->prepare("
                INSERT INTO whatsapp_messages 
                (conversation_id, phone_number, message_type, message_content, is_incoming, created_at) 
                VALUES (?, ?, 'text', ?, ?, NOW())
            ");
            
            $is_incoming = ($direccion === 'incoming') ? 1 : 0;
            $stmt->execute([$conversation_id, $telefono, $mensaje, $is_incoming]);
            
            $this->log("💾 Mensaje guardado en BD: $direccion");
            
        } catch (Exception $e) {
            $this->log("⚠️ Error guardando mensaje: " . $e->getMessage());
        }
    }
    
    /**
     * Logging
     */
    private function log($mensaje) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $mensaje\n";
        
        file_put_contents(__DIR__ . '/whatsapp_bot.log', $log_entry, FILE_APPEND);
        
        // Mostrar en consola si es CLI
        if (php_sapi_name() === 'cli') {
            echo $log_entry;
        }
    }
}