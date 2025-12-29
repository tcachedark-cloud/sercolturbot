<?php
/**
 * SERVICIO DE WHATSAPP
 * Lógica de conversaciones y venta
 */

require_once(__DIR__ . '/../config/whatsapp_config.php');

class WhatsAppService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Procesar mensaje de texto
     */
    public function processTextMessage($phone, $name, $text) {
        // Obtener o crear conversación
        $conversation = $this->getOrCreateConversation($phone, $name);
        $conv_id = $conversation['id'];
        $state = $conversation['state'];

        // Enrutar según estado
        switch ($state) {
            case STATE_INITIAL:
                $this->handleInitialState($phone, $conv_id, $text);
                break;
            case STATE_ENTERING_NAME:
                $this->handleNameInput($phone, $conv_id, $text);
                break;
            case STATE_ENTERING_EMAIL:
                $this->handleEmailInput($phone, $conv_id, $text);
                break;
            case STATE_ENTERING_PEOPLE:
                $this->handlePeopleInput($phone, $conv_id, $text);
                break;
            case STATE_SELECTING_DATE:
                $this->handleDateInput($phone, $conv_id, $text);
                break;
            default:
                $this->showMainMenu($phone, $conv_id);
        }
    }

    /**
     * Procesar respuesta de botón
     */
    public function processButtonResponse($phone, $name, $title, $payload) {
        $conversation = $this->getOrCreateConversation($phone, $name);
        $conv_id = $conversation['id'];

        // Analizar payload
        if (strpos($payload, 'tour_') === 0) {
            $tour_id = str_replace('tour_', '', $payload);
            $this->selectTour($phone, $conv_id, $tour_id);
        } elseif (strpos($payload, 'action_') === 0) {
            $action = str_replace('action_', '', $payload);
            $this->handleAction($phone, $conv_id, $action);
        }
    }

    /**
     * Procesar respuesta de lista
     */
    public function processListResponse($phone, $name, $title, $id) {
        $conversation = $this->getOrCreateConversation($phone, $name);
        $conv_id = $conversation['id'];

        // Analizar ID
        if (strpos($id, 'tour_') === 0) {
            $tour_id = str_replace('tour_', '', $id);
            $this->selectTour($phone, $conv_id, $tour_id);
        }
    }

    /**
     * Obtener o crear conversación
     */
    private function getOrCreateConversation($phone, $name) {
        // Buscar conversación existente
        $stmt = $this->pdo->prepare("
            SELECT * FROM whatsapp_conversations 
            WHERE phone_number = ? 
            ORDER BY updated_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$phone]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conversation) {
            return $conversation;
        }

        // Crear nueva conversación
        $stmt = $this->pdo->prepare("
            INSERT INTO whatsapp_conversations 
            (phone_number, user_name, state, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$phone, $name, STATE_INITIAL]);

        return [
            'id' => $this->pdo->lastInsertId(),
            'phone_number' => $phone,
            'user_name' => $name,
            'state' => STATE_INITIAL
        ];
    }

    /**
     * Mostrar menú principal
     */
    private function showMainMenu($phone, $conv_id) {
        $message = "🎯 *¿Qué te gustaría hacer?*\n\n";
        $message .= "1️⃣ Ver tours disponibles\n";
        $message .= "2️⃣ Mis reservas\n";
        $message .= "3️⃣ Información general";

        sendWhatsAppMessage($phone, $message);
    }

    /**
     * Manejar estado inicial
     */
    private function handleInitialState($phone, $conv_id, $text) {
        $text_lower = strtolower($text);

        if (strpos($text_lower, 'hola') !== false || strpos($text_lower, 'hi') !== false) {
            sendWhatsAppMessage($phone, "👋 ¡Hola! Bienvenido a SERCOLTURBOT");
            $this->showToursList($phone, $conv_id);
        } elseif (strpos($text_lower, 'tour') !== false || strpos($text_lower, 'reserva') !== false) {
            $this->showToursList($phone, $conv_id);
        } else {
            $this->showMainMenu($phone, $conv_id);
        }

        $this->updateConversationState($conv_id, STATE_SELECTING_TOUR);
    }

    /**
     * Mostrar lista de tours
     */
    private function showToursList($phone, $conv_id) {
        // Obtener tours
        $stmt = $this->pdo->query("SELECT id, nombre, descripcion, precio, duracion_dias FROM tours WHERE activo = TRUE");
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $message = "🎫 *Tours Disponibles:*\n\n";

        $sections = [
            [
                'title' => 'Nuestros Tours',
                'rows' => []
            ]
        ];

        foreach ($tours as $tour) {
            $message .= "🏖️ *{$tour['nombre']}*\n";
            $message .= "💵 ${$tour['precio']} - {$tour['duracion_dias']} días\n";
            $message .= "{$tour['descripcion']}\n\n";

            $sections[0]['rows'][] = [
                'id' => 'tour_' . $tour['id'],
                'title' => $tour['nombre'],
                'description' => "$ {$tour['precio']} - {$tour['duracion_dias']} días"
            ];
        }

        // Enviar lista
        sendWhatsAppList($phone, "Elige tu tour", "Selecciona uno de nuestros tours", $sections);
    }

    /**
     * Seleccionar tour
     */
    private function selectTour($phone, $conv_id, $tour_id) {
        // Obtener detalles del tour
        $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tour) {
            sendWhatsAppMessage($phone, "❌ Tour no encontrado");
            return;
        }

        // Guardar en conversación
        $stmt = $this->pdo->prepare("
            UPDATE whatsapp_conversations 
            SET selected_tour_id = ?, state = ? 
            WHERE id = ?
        ");
        $stmt->execute([$tour_id, STATE_SELECTING_DATE, $conv_id]);

        $message = "✅ Excelente! Has seleccionado *{$tour['nombre']}*\n\n";
        $message .= "📅 *¿Para qué fecha?* (ej: 2025-01-15)\n";
        $message .= "💵 Precio: ${$tour['precio']}\n";
        $message .= "⏱️ Duración: {$tour['duracion_dias']} días";

        sendWhatsAppMessage($phone, $message);
    }

    /**
     * Manejar entrada de fecha
     */
    private function handleDateInput($phone, $conv_id, $text) {
        // Validar fecha
        $date = DateTime::createFromFormat('Y-m-d', $text);
        
        if (!$date) {
            sendWhatsAppMessage($phone, "❌ Por favor ingresa una fecha válida (formato: YYYY-MM-DD)");
            return;
        }

        // Guardar fecha
        $stmt = $this->pdo->prepare("
            UPDATE whatsapp_conversations 
            SET selected_date = ?, state = ? 
            WHERE id = ?
        ");
        $stmt->execute([$text, STATE_ENTERING_PEOPLE, $conv_id]);

        sendWhatsAppMessage($phone, "👥 *¿Cuántas personas viajarán?*");
    }

    /**
     * Manejar entrada de cantidad de personas
     */
    private function handlePeopleInput($phone, $conv_id, $text) {
        if (!is_numeric($text) || $text < 1) {
            sendWhatsAppMessage($phone, "❌ Por favor ingresa un número válido de personas");
            return;
        }

        // Guardar cantidad
        $stmt = $this->pdo->prepare("
            UPDATE whatsapp_conversations 
            SET num_people = ?, state = ? 
            WHERE id = ?
        ");
        $stmt->execute([$text, STATE_ENTERING_NAME, $conv_id]);

        sendWhatsAppMessage($phone, "👤 *¿Cuál es tu nombre completo?*");
    }

    /**
     * Manejar entrada de nombre
     */
    private function handleNameInput($phone, $conv_id, $text) {
        // Guardar nombre
        $stmt = $this->pdo->prepare("
            UPDATE whatsapp_conversations 
            SET full_name = ?, state = ? 
            WHERE id = ?
        ");
        $stmt->execute([$text, STATE_ENTERING_EMAIL, $conv_id]);

        sendWhatsAppMessage($phone, "📧 *¿Cuál es tu correo electrónico?*");
    }

    /**
     * Manejar entrada de email
     */
    private function handleEmailInput($phone, $conv_id, $text) {
        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
            sendWhatsAppMessage($phone, "❌ Por favor ingresa un correo válido");
            return;
        }

        // Guardar email y crear reserva
        $this->createReservation($phone, $conv_id, $text);
    }

    /**
     * Crear reserva automáticamente
     */
    private function createReservation($phone, $conv_id, $email) {
        try {
            // Obtener datos de conversación
            $stmt = $this->pdo->prepare("
                SELECT * FROM whatsapp_conversations WHERE id = ?
            ");
            $stmt->execute([$conv_id]);
            $conv = $stmt->fetch(PDO::FETCH_ASSOC);

            // Crear o actualizar cliente
            $stmt = $this->pdo->prepare("
                SELECT id FROM clientes WHERE email = ?
            ");
            $stmt->execute([$email]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$client) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO clientes (nombre, email, telefono, documento, fecha_registro)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$conv['full_name'], $email, $phone, $phone]);
                $client_id = $this->pdo->lastInsertId();
            } else {
                $client_id = $client['id'];
            }

            // Crear reserva
            $stmt = $this->pdo->prepare("
                INSERT INTO reservas (cliente_id, tour_id, fecha_reserva, fecha_inicio, cantidad_personas, precio_total, estado)
                VALUES (?, ?, NOW(), ?, ?, ?, 'pendiente')
            ");

            // Obtener precio del tour
            $stmt2 = $this->pdo->prepare("SELECT precio FROM tours WHERE id = ?");
            $stmt2->execute([$conv['selected_tour_id']]);
            $tour = $stmt2->fetch(PDO::FETCH_ASSOC);
            $total_price = $tour['precio'] * $conv['num_people'];

            $stmt->execute([
                $client_id,
                $conv['selected_tour_id'],
                $conv['selected_date'],
                $conv['num_people'],
                $total_price
            ]);

            $reservation_id = $this->pdo->lastInsertId();

            // Asignar guía y bus
            $this->assignGuideAndBus($reservation_id, $conv['selected_date'], $conv['num_people']);

            // Actualizar conversación
            $stmt = $this->pdo->prepare("
                UPDATE whatsapp_conversations 
                SET reservation_id = ?, state = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reservation_id, STATE_COMPLETED, $conv_id]);

            // Enviar confirmación
            $message = "✅ *¡Reserva Confirmada!*\n\n";
            $message .= "🎫 *Número de reserva:* #$reservation_id\n";
            $message .= "👤 Nombre: {$conv['full_name']}\n";
            $message .= "📅 Fecha: {$conv['selected_date']}\n";
            $message .= "👥 Personas: {$conv['num_people']}\n";
            $message .= "💵 Total: \$" . number_format($total_price, 2) . "\n\n";
            $message .= "Nos pondremos en contacto contigo pronto para confirmar los detalles.";

            sendWhatsAppMessage($phone, $message);

            logWhatsApp('Reserva creada', 'SUCCESS', ['reservation_id' => $reservation_id, 'phone' => $phone]);

        } catch (Exception $e) {
            logWhatsApp('Error creando reserva: ' . $e->getMessage(), 'ERROR');
            sendWhatsAppMessage($phone, "❌ Hubo un error. Por favor intenta de nuevo.");
        }
    }

    /**
     * Asignar guía y bus automáticamente
     */
    private function assignGuideAndBus($reservation_id, $date, $num_people) {
        try {
            // Buscar guía disponible
            $stmt = $this->pdo->prepare("
                SELECT id FROM guias 
                WHERE disponible = TRUE 
                ORDER BY calificacion DESC 
                LIMIT 1
            ");
            $stmt->execute();
            $guide = $stmt->fetch(PDO::FETCH_ASSOC);

            // Buscar bus con capacidad suficiente
            $stmt = $this->pdo->prepare("
                SELECT id FROM buses 
                WHERE disponible = TRUE AND capacidad >= ? 
                ORDER BY capacidad ASC 
                LIMIT 1
            ");
            $stmt->execute([$num_people]);
            $bus = $stmt->fetch(PDO::FETCH_ASSOC);

            // Crear asignación
            if ($guide && $bus) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO asignaciones (reserva_id, guia_id, bus_id, fecha_asignacion)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$reservation_id, $guide['id'], $bus['id']]);

                logWhatsApp('Guía y bus asignados', 'SUCCESS', ['reservation_id' => $reservation_id]);
            }

        } catch (Exception $e) {
            logWhatsApp('Error asignando recursos: ' . $e->getMessage(), 'ERROR');
        }
    }

    /**
     * Actualizar estado de conversación
     */
    private function updateConversationState($conv_id, $state) {
        $stmt = $this->pdo->prepare("
            UPDATE whatsapp_conversations 
            SET state = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$state, $conv_id]);
    }

    /**
     * Manejar acción genérica
     */
    private function handleAction($phone, $conv_id, $action) {
        switch ($action) {
            case 'menu':
                $this->showMainMenu($phone, $conv_id);
                break;
            case 'tours':
                $this->showToursList($phone, $conv_id);
                break;
            default:
                sendWhatsAppMessage($phone, "❌ Acción no reconocida");
        }
    }
}
?>
