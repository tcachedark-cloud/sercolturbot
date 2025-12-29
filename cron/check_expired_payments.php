<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * VERIFICAR PAGOS VENCIDOS - CRON JOB
 * Ejecutar cada 10 minutos
 * Archivo: cron/check_expired_payments.php
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
require_once BASE_PATH . '/services/PagoService.php';

$pagoService = new PagoService($pdo);
$logFile = BASE_PATH . '/logs/cron/check_expired_payments_' . date('Y-m-d') . '.log';

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
log_message("VERIFICAR PAGOS VENCIDOS - Iniciado");

try {
    
    // Obtener pagos iniciados hace más de 30 minutos
    $sql = "
        SELECT id, referencia, estado, fecha_creacion
        FROM pagos 
        WHERE estado = 'iniciado' 
        AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        LIMIT 50
    ";
    
    $stmt = $pdo->query($sql);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_message("Pagos vencidos encontrados: " . count($pagos));
    
    foreach ($pagos as $pago) {
        $resultado = $pagoService->verificarPago($pago['referencia']);
        
        if ($resultado['success']) {
            $estado = $resultado['estado'] ?? 'desconocido';
            log_message("Pago {$pago['referencia']} verificado - Estado: $estado");
        } else {
            log_message("Error verificando {$pago['referencia']}: " . $resultado['error']);
        }
    }
    
    // Marcar pagos pendientes como expirados (después de 1 hora)
    $sqlExpired = "
        UPDATE pagos 
        SET estado = 'expirado'
        WHERE estado = 'iniciado' 
        AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ";
    
    $pdo->exec($sqlExpired);
    $expiredCount = $pdo->exec($sqlExpired);
    
    log_message("Pagos marcados como expirados: $expiredCount");
    
    log_message("✓ Ejecución completada exitosamente");
    log_message("═════════════════════════════════════════════════════════\n");
    
} catch (Exception $e) {
    log_message("✗ ERROR: " . $e->getMessage());
    log_message("═════════════════════════════════════════════════════════\n");
    error_log("Check Expired Payments Error: " . $e->getMessage());
}

$pdo = null;
?>
