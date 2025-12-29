<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SERVICIO DE RECORDATORIOS AUTOMÁTICOS
 * Ejecutar cada 5 minutos vía CRON/Task Scheduler
 * ═══════════════════════════════════════════════════════════════
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Cargar configuración y servicios
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../services/EmailService.php');

// Variables de WhatsApp
define('ACCESS_TOKEN', 'EAA9SPy8AxVcBQcOXdIZBoXM8YtvpDXWmuZBUkuQCRnLUURJd5wESkmO9KzLJRQZAZCG10p74bPMLZAJ7afBjfTEZC2voxXyxbQ5t6ibQNUZChO9PlFdx6IZBtZBpN64XfZCuwKIM0MMGFqkkaZBQt8INVa7ZCnjSe39DPtobSxI4drG8DGRT67NPOUJBoY4ubq005FlFEEYfaNtgRIVz3eDGZAmm2ex10cbECe214ev6LZAeKTyXGRJF8ZAWdZCl3tTSRlC5Y9yu5sKXNae7iZBVcZBXeuxT0bVYFI2ZCyv8blTOgZDZD');
define('PHONE_ID', '925480580639940');

class ReminderService {
    private $pdo;
    private $emailService;
    
    public function __construct() {
        $this->pdo = getDatabase();
        $this->emailService = new EmailService($this->pdo);
    }
    
    /**
     * Enviar recordatorios de citas próximas (60 minutos antes)
     */
    public function enviarRecordatorios() {
        if (!$this->pdo) {
            $this->log("❌ Error: No hay conexión a base de datos");
            return;
        }
        
        try {
            // Buscar citas en los próximos 60-65 minutos
            $sql = "
                SELECT c.*, cl.telefono as cliente_telefono, cl.email as cliente_email, cl.nombre as cliente_nombre
                FROM citas c
                LEFT JOIN clientes cl ON c.telefono = cl.telefono
                WHERE c.estado = 'confirmada' 
                AND c.recordatorio_enviado = 0
                AND DATE_ADD(c.fecha_hora, INTERVAL -60 MINUTE) <= NOW()
                AND DATE_ADD(c.fecha_hora, INTERVAL -65 MINUTE) >= NOW()
                LIMIT 50
            ";
            
            $resultado = $this->pdo->query($sql);
            $citas = $resultado->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($citas)) {
                $this->log("✓ Sin citas para recordar en este momento");
                return;
            }
            
            $this->log("📨 Procesando " . count($citas) . " recordatorios...");
            
            foreach ($citas as $cita) {
                $this->enviarRecordatorioCita($cita);
            }
            
            $this->log("✅ Recordatorios completados");
            
        } catch (Exception $e) {
            $this->log("❌ Error: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar recordatorio de una cita específica
     */
    private function enviarRecordatorioCita($cita) {
        try {
            $codigo = $cita['codigo'] ?? 'N/A';
            $nombre = $cita['cliente_nombre'] ?? $cita['nombre'] ?? 'Cliente';
            $telefono = $cita['cliente_telefono'] ?? $cita['telefono'] ?? '';
            $email = $cita['cliente_email'] ?? '';
            
            // Enviar WhatsApp
            if (!empty($telefono)) {
                $this->enviarWhatsAppRecordatorio($telefono, $cita);
            }
            
            // Enviar Email
            if (!empty($email)) {
                $cliente = ['nombre' => $nombre, 'email' => $email];
                $this->emailService->enviarRecordatorioCita($cliente, $cita);
            }
            
            // Marcar como enviado
            $this->pdo->prepare("
                UPDATE citas 
                SET recordatorio_enviado = 1, fecha_recordatorio = NOW()
                WHERE id = ?
            ")->execute([$cita['id']]);
            
            $this->log("✓ Recordatorio enviado para {$codigo}");
            
        } catch (Exception $e) {
            $this->log("⚠ Error en recordatorio: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar recordatorio vía WhatsApp
     */
    private function enviarWhatsAppRecordatorio($telefono, $cita) {
        try {
            $telefono = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($telefono) === 10) {
                $telefono = '57' . $telefono;
            }
            
            $fecha = date('d/m/Y', strtotime($cita['fecha_hora']));
            $hora = date('H:i', strtotime($cita['fecha_hora']));
            
            $mensaje = "📋 *Recordatorio de Cita*\n\n";
            $mensaje .= "Hola {$cita['cliente_nombre']},\n\n";
            $mensaje .= "⏰ Tu cita está próxima:\n\n";
            $mensaje .= "📅 Fecha: *{$fecha}*\n";
            $mensaje .= "🕐 Hora: *{$hora}*\n";
            $mensaje .= "🎯 Servicio: *{$cita['servicio']}*\n";
            $mensaje .= "🎫 Código: *{$cita['codigo']}*\n\n";
            $mensaje .= "¿Necesitas cambiar la cita? Responde con *'MODIFICAR'*\n\n";
            $mensaje .= "🚌 *SERCOLTUR*";
            
            $this->enviarTextoWhatsApp($telefono, $mensaje);
            
        } catch (Exception $e) {
            $this->log("⚠ Error WhatsApp: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar texto por WhatsApp API
     */
    private function enviarTextoWhatsApp($numero, $mensaje) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://graph.instagram.com/v18.0/" . PHONE_ID . "/messages",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([
                    "messaging_product" => "whatsapp",
                    "to" => $numero,
                    "type" => "text",
                    "text" => ["body" => $mensaje]
                ]),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer " . ACCESS_TOKEN
                ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                throw new Exception("Error CURL: $err");
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            $this->log("Error WhatsApp API: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar citas vencidas (más de 24 horas atrás)
     */
    public function limpiarCitasVencidas() {
        try {
            $resultado = $this->pdo->exec("
                UPDATE citas 
                SET estado = 'vencida'
                WHERE estado = 'confirmada'
                AND fecha_hora < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 100
            ");
            
            if ($resultado > 0) {
                $this->log("✓ {$resultado} citas marcadas como vencidas");
            }
        } catch (Exception $e) {
            $this->log("⚠ Error limpiando citas: " . $e->getMessage());
        }
    }
    
    /**
     * Log de operaciones
     */
    private function log($mensaje) {
        $log_file = __DIR__ . '/../logs/reminders.log';
        $timestamp = date('Y-m-d H:i:s');
        $linea = "[{$timestamp}] {$mensaje}\n";
        
        if (!is_dir(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0755, true);
        }
        
        file_put_contents($log_file, $linea, FILE_APPEND);
        echo $linea;
    }
}

// Ejecutar servicio
$reminders = new ReminderService();
$reminders->enviarRecordatorios();
$reminders->limpiarCitasVencidas();

echo "✅ Script completado\n";
?>
