<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * TAREAS DE VALIDACIÓN GENERAL - CRON JOB
 * Ejecutar cada 6 horas
 * Archivo: cron/validation_tasks.php
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

$logFile = BASE_PATH . '/logs/cron/validation_tasks_' . date('Y-m-d') . '.log';

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
log_message("TAREAS DE VALIDACIÓN - Iniciado");

try {
    
    // 1. Validar integridad de datos
    log_message("\n[1] Validando integridad de datos...");
    
    // Verificar registros huérfanos
    $sql = "
        SELECT COUNT(*) as huerfanos FROM citas 
        WHERE reserva_id NOT IN (SELECT id FROM reservas WHERE id IS NOT NULL)
    ";
    $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    log_message("  Citas huérfanas encontradas: " . $result['huerfanos']);
    
    // 2. Validar pagos pendientes
    log_message("\n[2] Validando pagos pendientes...");
    
    $sql = "
        SELECT COUNT(*) as pendientes FROM pagos
        WHERE estado = 'iniciado'
        AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ";
    $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    $pendientes = $result['pendientes'];
    log_message("  Pagos pendientes antiguos: $pendientes");
    
    if ($pendientes > 0) {
        $pdo->exec("
            UPDATE pagos 
            SET estado = 'expirado'
            WHERE estado = 'iniciado'
            AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ");
        log_message("  ✓ Marcados como expirados");
    }
    
    // 3. Validar citas vencidas
    log_message("\n[3] Validando citas vencidas...");
    
    $sql = "
        SELECT COUNT(*) as vencidas FROM citas
        WHERE fecha_cita < NOW()
        AND estado != 'completada'
        AND estado != 'cancelada'
    ";
    $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    log_message("  Citas vencidas sin completar: " . $result['vencidas']);
    
    // 4. Validar reservas sin confirmación
    log_message("\n[4] Validando reservas sin confirmación...");
    
    $sql = "
        SELECT COUNT(*) as sin_conf FROM reservas
        WHERE estado = 'iniciada'
        AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ";
    $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    log_message("  Reservas iniciadas sin confirmar hace 24h: " . $result['sin_conf']);
    
    // 5. Validar logs de BD
    log_message("\n[5] Validando tamaño de logs...");
    
    $tables = ['email_logs', 'reminder_audits', 'wompi_logs'];
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) as qty FROM $table WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        try {
            $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            if ($result['qty'] > 0) {
                $pdo->prepare("DELETE FROM $table WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)")->execute();
                log_message("  ✓ Limpiados " . $result['qty'] . " registros de $table");
            }
        } catch (Exception $e) {
            // Tabla no existe
        }
    }
    
    // 6. Validar espacio en disco
    log_message("\n[6] Validando espacio disponible...");
    
    $drive = 'C:';
    $freeSpace = disk_free_space($drive);
    $totalSpace = disk_total_space($drive);
    $percentUsed = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
    
    log_message("  Espacio usado: $percentUsed%");
    log_message("  Espacio libre: " . round($freeSpace / (1024 * 1024 * 1024), 2) . " GB");
    
    if ($percentUsed > 90) {
        log_message("  ⚠ ADVERTENCIA: Espacio en disco crítico!");
    }
    
    // 7. Estadísticas generales
    log_message("\n[7] Estadísticas generales...");
    
    $stats = [
        'clientes' => "SELECT COUNT(*) FROM clientes",
        'reservas' => "SELECT COUNT(*) FROM reservas",
        'citas' => "SELECT COUNT(*) FROM citas",
        'pagos' => "SELECT COUNT(*) FROM pagos",
    ];
    
    foreach ($stats as $nombre => $sql) {
        try {
            $count = $pdo->query($sql)->fetchColumn();
            log_message("  $nombre: $count registros");
        } catch (Exception $e) {
            // Tabla no existe
        }
    }
    
    log_message("\n═════════════════════════════════════════════════════════");
    log_message("✓ Validación completada exitosamente");
    log_message("═════════════════════════════════════════════════════════\n");
    
} catch (Exception $e) {
    log_message("✗ ERROR: " . $e->getMessage());
    log_message("═════════════════════════════════════════════════════════\n");
    error_log("Validation Tasks Error: " . $e->getMessage());
}

$pdo = null;
?>
