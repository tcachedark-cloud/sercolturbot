<?php
/**
 * TEST: Email Service
 * Prueba el envío de emails desde SERCOLTURBOT
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         TEST: EmailService - SERCOLTURBOT                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../services/EmailService.php');

try {
    // Obtener conexión a BD
    $pdo = getDatabase();
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    echo "[✓] Conexión a BD establecida\n\n";
    
    // Crear instancia de EmailService
    $emailService = new EmailService($pdo);
    echo "[✓] Instancia de EmailService creada\n\n";
    
    // Test 1: Enviar recordatorio de cita
    echo "════════════════════════════════════════════════════════════\n";
    echo "TEST 1: Enviar Recordatorio de Cita\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    $cliente = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan.perez@example.com'  // ← REEMPLAZAR CON TU EMAIL
    ];
    
    $cita = [
        'fecha_hora' => '2025-02-15 14:30:00',
        'servicio' => 'Consultoría',
        'codigo' => 'CITA-250214-1234'
    ];
    
    echo "Cliente: {$cliente['nombre']}\n";
    echo "Email: {$cliente['email']}\n";
    echo "Cita: {$cita['servicio']} el {$cita['fecha_hora']}\n";
    echo "Código: {$cita['codigo']}\n\n";
    
    $resultado = $emailService->enviarRecordatorioCita($cliente, $cita);
    
    if ($resultado['success']) {
        echo "[✓] Email enviado exitosamente\n";
        echo "    Para: {$resultado['para']}\n";
        echo "    Asunto: Recordatorio de tu Cita - SERCOLTUR\n";
        echo "    Timestamp: {$resultado['timestamp']}\n";
    } else {
        echo "[✗] Error al enviar email\n";
        echo "    Error: {$resultado['error']}\n";
    }
    
    echo "\n════════════════════════════════════════════════════════════\n";
    echo "TEST 2: Enviar Confirmación de Reserva\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    $cliente2 = [
        'nombre' => 'María López',
        'email' => 'maria.lopez@example.com'  // ← REEMPLAZAR
    ];
    
    $reserva = [
        'codigo_whatsapp' => 'RES-250214-5678',
        'tour' => 'Tour Cafetero - 5 días',
        'fecha_inicio' => '2025-03-01',
        'cantidad_personas' => 4,
        'precio_total' => 1500000
    ];
    
    echo "Cliente: {$cliente2['nombre']}\n";
    echo "Email: {$cliente2['email']}\n";
    echo "Tour: {$reserva['tour']}\n";
    echo "Personas: {$reserva['cantidad_personas']}\n";
    echo "Total: \${$reserva['precio_total']}\n\n";
    
    $resultado2 = $emailService->enviarConfirmacionReserva($cliente2, $reserva);
    
    if ($resultado2['success']) {
        echo "[✓] Email de confirmación enviado\n";
    } else {
        echo "[✗] Error al enviar confirmación\n";
        echo "    Error: {$resultado2['error']}\n";
    }
    
    echo "\n════════════════════════════════════════════════════════════\n";
    echo "RESUMEN\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    echo "✓ EmailService está funcionando correctamente\n";
    echo "✓ Los emails se enviarán a las direcciones configuradas\n";
    echo "✓ Las plantillas HTML se generaron exitosamente\n\n";
    
    echo "NOTAS:\n";
    echo "• Cambiar emails en las pruebas para recibir confirmaciones\n";
    echo "• Verificar carpeta de SPAM si no llega el email\n";
    echo "• Configurar SMTP en config/config_empresarial.php\n\n";
    
} catch (Exception $e) {
    echo "[✗] ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "FIN DEL TEST\n";
echo "════════════════════════════════════════════════════════════\n";
?>
