<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SINCRONIZAR RECORDATORIOS - CRON JOB
 * Ejecutar cada 30 minutos
 * Archivo: cron/sync_reminders.php
 * ═══════════════════════════════════════════════════════════════
 */

define('BASE_PATH', __DIR__ . '/..');

// Conectar a BD
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=sercolturbot",
        "root",
        "C121672@c",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    error_log("Error DB: " . $e->getMessage());
    exit(1);
}

// Incluir servicios
require_once BASE_PATH . '/config/config_empresarial.php';
require_once BASE_PATH . '/services/ReminderService.php';

$logFile = BASE_PATH . '/logs/cron/sync_reminders_' . date('Y-m-d') . '.log';

function log_message($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(
        $logFile,
        "[$timestamp] $msg\n",
        FILE_APPEND
    );
}

log_message("═════════════════════════════════════════════════════════");
log_message("SINCRONIZAR RECORDATORIOS - Iniciado");

try {
    
    $reminderService = new ReminderService($pdo);
    
    // Obtener citas que necesitan recordatorio
    $sql = "
        SELECT 
            c.id, c.fecha_cita, c.telefono, c.nombre,
            r.id as reserva_id, r.codigo
        FROM citas c
        LEFT JOIN reservas r ON c.reserva_id = r.id
        WHERE c.recordatorio_enviado = 0
        AND c.fecha_cita > NOW()
        AND c.fecha_cita <= DATE_ADD(NOW(), INTERVAL 60 MINUTE)
        LIMIT 100
    ";
    
    $stmt = $pdo->query($sql);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_message("Citas encontradas: " . count($citas));
    
    $enviados = 0;
    $errores = 0;
    
    foreach ($citas as $cita) {
        try {
            // Enviar recordatorio
            $resultado = $reminderService->enviarRecordatorioCita($cita);
            
            if ($resultado['success']) {
                // Marcar como enviado
                $pdo->prepare("
                    UPDATE citas 
                    SET recordatorio_enviado = 1, fecha_recordatorio = NOW()
                    WHERE id = ?
                ")->execute([$cita['id']]);
                
                $enviados++;
                log_message("✓ Recordatorio enviado para cita ID {$cita['id']}");
            } else {
                $errores++;
                log_message("✗ Error enviando recordatorio para cita ID {$cita['id']}");
            }
            
        } catch (Exception $e) {
            $errores++;
            log_message("✗ Excepción en cita ID {$cita['id']}: " . $e->getMessage());
        }
    }
    
    log_message("═════════════════════════════════════════════════════════");
    log_message("Resumen: $enviados enviados, $errores errores");
    log_message("═════════════════════════════════════════════════════════\n");
    
} catch (Exception $e) {
    log_message("✗ ERROR: " . $e->getMessage());
    log_message("═════════════════════════════════════════════════════════\n");
    error_log("Sync Reminders Error: " . $e->getMessage());
}

$pdo = null;
?>
