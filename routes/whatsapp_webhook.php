<?php
/**
 * WEBHOOK DE WHATSAPP
 * Recibe y procesa mensajes de Meta WhatsApp
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../config/whatsapp_config.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../services/WhatsAppService.php');

try {
    // Verificación de webhook
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $mode = $_GET['hub_mode'] ?? null;
        $token = $_GET['hub_verify_token'] ?? null;
        $challenge = $_GET['hub_challenge'] ?? null;

        if ($mode === 'subscribe' && $token === META_WEBHOOK_TOKEN) {
            logWhatsApp('Webhook verificado correctamente', 'VERIFY');
            http_response_code(200);
            echo $challenge;
            exit;
        } else {
            logWhatsApp('Verificación de webhook fallida', 'ERROR', ['mode' => $mode, 'token' => $token]);
            http_response_code(403);
            exit;
        }
    }

    // Procesar mensajes entrantes
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        logWhatsApp('Mensaje recibido', 'RECEIVE', $data);

        // Validar estructura
        if (!isset($data['entry'])) {
            http_response_code(200);
            exit;
        }

        $service = new WhatsAppService($pdo);

        // Procesar cada entrada
        foreach ($data['entry'] as $entry) {
            if (!isset($entry['changes'])) continue;

            foreach ($entry['changes'] as $change) {
                if ($change['field'] === 'messages') {
                    $message_data = $change['value'];

                    if (!isset($message_data['messages'])) continue;

                    foreach ($message_data['messages'] as $message) {
                        $phone = $message_data['contacts'][0]['wa_id'] ?? null;
                        $sender_name = $message_data['contacts'][0]['profile']['name'] ?? 'Usuario';

                        // Procesar por tipo de mensaje
                        if ($message['type'] === 'text') {
                            $text = $message['text']['body'];
                            $service->processTextMessage($phone, $sender_name, $text);
                        } elseif ($message['type'] === 'button') {
                            $button_text = $message['button']['text'];
                            $button_payload = $message['button']['payload'];
                            $service->processButtonResponse($phone, $sender_name, $button_text, $button_payload);
                        } elseif ($message['type'] === 'interactive') {
                            $interactive = $message['interactive'];
                            if ($interactive['type'] === 'button_reply') {
                                $reply = $interactive['button_reply'];
                                $service->processButtonResponse($phone, $sender_name, $reply['title'], $reply['id']);
                            } elseif ($interactive['type'] === 'list_reply') {
                                $reply = $interactive['list_reply'];
                                $service->processListResponse($phone, $sender_name, $reply['title'], $reply['id']);
                            }
                        }
                    }
                }
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    }

} catch (Exception $e) {
    logWhatsApp('Error en webhook: ' . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
