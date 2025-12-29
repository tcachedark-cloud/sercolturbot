<?php
// Limpiar cualquier salida previa
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de errores strict
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $errstr . ' en ' . basename($errfile) . ':' . $errline], JSON_UNESCAPED_UNICODE);
    exit;
});

try {
    // Incluir con validación
    $db_file = __DIR__ . '/../config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception('Archivo de configuración no encontrado: ' . $db_file);
    }
    require_once($db_file);
    
    $bot_file = __DIR__ . '/../services/BotService.php';
    if (!file_exists($bot_file)) {
        throw new Exception('Archivo de servicio no encontrado: ' . $bot_file);
    }
    require_once($bot_file);

    if (!isset($pdo)) {
        throw new Exception('Conexión a BD no inicializada');
    }

    $botService = new BotService($pdo);

    // Obtener acción
    $action = $_GET['action'] ?? $_POST['action'] ?? 'mensaje';

    switch ($action) {
        case 'mensaje':
            // Procesar mensaje del cliente
            $cliente_id = $_POST['cliente_id'] ?? null;
            $mensaje = $_POST['mensaje'] ?? null;
            $asesor_id = $_POST['asesor_id'] ?? null;
            
            if (!$cliente_id || !$mensaje) {
                throw new Exception('Parámetros incompletos: cliente_id y mensaje requeridos');
            }
            
            $resultado = $botService->procesarMensaje($cliente_id, $mensaje, $asesor_id);
            echo json_encode(['success' => true, 'data' => $resultado], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'conversaciones':
            // Obtener historial de conversaciones
            $cliente_id = $_GET['cliente_id'] ?? null;
            
            if (!$cliente_id) {
                throw new Exception('cliente_id requerido');
            }
            
            $conversaciones = $botService->obtenerConversaciones($cliente_id);
            echo json_encode(['success' => true, 'data' => $conversaciones], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'resolver':
            // Marcar conversación como resuelta
            $conversacion_id = $_POST['conversacion_id'] ?? null;
            
            if (!$conversacion_id) {
                throw new Exception('conversacion_id requerido');
            }
            
            $resultado = $botService->marcarResuelta($conversacion_id);
            echo json_encode(['success' => $resultado, 'message' => 'Conversación marcada como resuelta'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'estadisticas':
            // Obtener estadísticas del bot
            $estadisticas = $botService->obtenerEstadisticas();
            echo json_encode(['success' => true, 'data' => $estadisticas], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $action);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de BD: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
