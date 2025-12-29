<?php
class BotService {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Procesar mensaje del cliente y generar respuesta del bot
     */
    public function procesarMensaje($cliente_id, $mensaje, $asesor_id = null) {
        $tipo_consulta = $this->identificarTipoConsulta($mensaje);
        $respuesta = $this->generarRespuesta($tipo_consulta, $mensaje);
        
        // Guardar conversación en BD
        $stmt = $this->pdo->prepare("
            INSERT INTO bot_conversaciones (cliente_id, asesor_id, mensaje_cliente, respuesta_bot, tipo_consulta)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$cliente_id, $asesor_id, $mensaje, $respuesta, $tipo_consulta]);
        
        // Registrar en log
        $this->registrarLog($cliente_id, $tipo_consulta, $mensaje);
        
        return [
            'respuesta' => $respuesta,
            'tipo' => $tipo_consulta,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Identificar el tipo de consulta del mensaje
     */
    private function identificarTipoConsulta($mensaje) {
        $mensaje_lower = strtolower($mensaje);
        
        if (preg_match('/reserv|booking|tour|paquete|precio/i', $mensaje)) {
            return 'reservas';
        } elseif (preg_match('/guia|acompañante|tour guide/i', $mensaje)) {
            return 'guias';
        } elseif (preg_match('/bus|transporte|viaje|salida/i', $mensaje)) {
            return 'buses';
        } elseif (preg_match('/asesor|ayuda|soporte|atencion/i', $mensaje)) {
            return 'asesoria';
        } elseif (preg_match('/disponibilidad|disponible|cuando|fecha/i', $mensaje)) {
            return 'disponibilidad';
        } else {
            return 'general';
        }
    }
    
    /**
     * Generar respuesta automática del bot
     */
    private function generarRespuesta($tipo, $mensaje) {
        $respuestas = [
            'reservas' => "¡Hola! 🎫 Me gustaría ayudarte con tu reserva. ¿En qué tour estás interesado? Tenemos:\n- Cartagena Clásica (3 días)\n- Santa Marta y Tayrona (4 días)\n- Bogotá Imperial (2 días)\n\n¿Cuántas personas son?",
            
            'guias' => "¡Perfecto! 👨‍🏫 Contamos con guías profesionales multilingües con amplia experiencia. Nuestros guías hablan:\n- Español\n- Inglés\n- Francés\n- Portugués\n\n¿Cuál es tu destino preferido?",
            
            'buses' => "🚌 Para tu viaje contamos con buses cómodos y seguros con capacidad de 35 a 50 pasajeros, aire acondicionado y servicios de WiFi. ¿Cuándo planeas viajar?",
            
            'asesoria' => "👨‍💼 Un asesor se comunicará contigo en breve. Mientras tanto, puedo ayudarte con información sobre nuestros tours y servicios. ¿En qué puedo ayudarte?",
            
            'disponibilidad' => "📅 Para verificar disponibilidad de fechas, por favor indícame:\n1. El tour que te interesa\n2. La fecha deseada\n3. Número de personas",
            
            'general' => "¡Hola! 👋 Bienvenido a SERCOLTURBOT. Soy tu asistente virtual. Puedo ayudarte con:\n- 🎫 Reservas de tours\n- 👨‍🏫 Información de guías\n- 🚌 Detalles de transporte\n- 📅 Disponibilidad\n- 👨‍💼 Conectarte con un asesor\n\n¿En qué puedo ayudarte?"
        ];
        
        return $respuestas[$tipo] ?? $respuestas['general'];
    }
    
    /**
     * Obtener conversaciones de un cliente
     */
    public function obtenerConversaciones($cliente_id, $limite = 20) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM bot_conversaciones 
            WHERE cliente_id = ? 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$cliente_id, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Marcar conversación como resuelta
     */
    public function marcarResuelta($conversacion_id) {
        $stmt = $this->pdo->prepare("
            UPDATE bot_conversaciones SET resuelta = TRUE WHERE id = ?
        ");
        return $stmt->execute([$conversacion_id]);
    }
    
    /**
     * Registrar en log
     */
    private function registrarLog($cliente_id, $tipo, $mensaje) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] Cliente:$cliente_id | Tipo:$tipo | Mensaje: " . substr($mensaje, 0, 100) . "\n";
        file_put_contents($log_dir . '/bot.log', $log_message, FILE_APPEND);
    }
    
    /**
     * Obtener estadísticas del bot
     */
    public function obtenerEstadisticas() {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_conversaciones,
                COUNT(DISTINCT cliente_id) as clientes_unicos,
                tipo_consulta,
                SUM(CASE WHEN resuelta = TRUE THEN 1 ELSE 0 END) as resueltas
            FROM bot_conversaciones
            GROUP BY tipo_consulta
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>