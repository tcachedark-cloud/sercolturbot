<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SINCRONIZAR GOOGLE CALENDAR - CRON JOB
 * Ejecutar cada 15 minutos
 * Archivo: cron/sync_google_calendar.php
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
require_once BASE_PATH . '/services/GoogleCalendarService.php';

$config = require_once BASE_PATH . '/config/config_empresarial.php';
$logFile = BASE_PATH . '/logs/cron/sync_google_calendar_' . date('Y-m-d') . '.log';

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
log_message("SINCRONIZAR GOOGLE CALENDAR - Iniciado");

try {
    
    // Verificar que Google Calendar está habilitado
    if (!$config['google_calendar']['habilitado']) {
        log_message("⚠ Google Calendar no está habilitado");
        exit(0);
    }
    
    // Inicializar servicio
    $gcalService = new GoogleCalendarService($pdo);
    
    // Verificar que está configurado
    if (!$gcalService->estaConfigurado()) {
        log_message("⚠ Google Calendar no está configurado correctamente");
        exit(0);
    }
    
    log_message("Iniciando sincronización desde Google Calendar...");
    
    // Sincronizar eventos
    $resultado = $gcalService->sincronizarDesdeGoogle();
    
    if ($resultado['success']) {
        $cantidad = $resultado['eventos_sincronizados'] ?? 0;
        log_message("✓ Sincronización completada: $cantidad eventos procesados");
    } else {
        log_message("✗ Error en sincronización: " . $resultado['error']);
    }
    
    log_message("═════════════════════════════════════════════════════════\n");
    
} catch (Exception $e) {
    log_message("✗ ERROR: " . $e->getMessage());
    log_message("═════════════════════════════════════════════════════════\n");
    error_log("Google Calendar Sync Error: " . $e->getMessage());
}

$pdo = null;
?>
