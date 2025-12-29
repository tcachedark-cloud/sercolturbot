<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SERVICIO DE EMAIL - SERCOLTURBOT
 * Envío de notificaciones, reportes y confirmaciones por email
 * ═══════════════════════════════════════════════════════════════
 */

class EmailService {
    private $config;
    private $pdo;
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $fromEmail;
    private $fromName;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        $this->config = require(__DIR__ . '/../config/config_empresarial.php');
        
        if ($this->config['email']['habilitado']) {
            $this->smtpHost = $this->config['email']['host'];
            $this->smtpPort = $this->config['email']['puerto'];
            $this->smtpUser = $this->config['email']['usuario'];
            $this->smtpPass = $this->config['email']['password'];
            $this->fromEmail = $this->config['email']['from_email'];
            $this->fromName = $this->config['email']['from_name'];
        }
    }
    
    /**
     * Enviar email usando PHP mail()
     */
    public function enviarEmail($para, $asunto, $cuerpo, $esHTML = true) {
        if (!$this->config['email']['habilitado']) {
            return ['success' => false, 'error' => 'Email no habilitado'];
        }
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: " . ($esHTML ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        
        try {
            $resultado = mail($para, $asunto, $cuerpo, $headers);
            
            $this->registrarEnvio($para, $asunto, $resultado);
            
            return [
                'success' => $resultado,
                'para' => $para,
                'asunto' => $asunto,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar confirmación de reserva
     */
    public function enviarConfirmacionReserva($cliente, $reserva) {
        $asunto = "✅ Reserva Confirmada - SERCOLTUR";
        
        $cuerpo = $this->plantillaConfirmacionReserva($cliente, $reserva);
        
        return $this->enviarEmail($cliente['email'], $asunto, $cuerpo, true);
    }
    
    /**
     * Enviar recordatorio de cita
     */
    public function enviarRecordatorioCita($cliente, $cita) {
        $asunto = "📋 Recordatorio de tu Cita - SERCOLTUR";
        
        $cuerpo = $this->plantillaRecordatorioCita($cliente, $cita);
        
        return $this->enviarEmail($cliente['email'], $asunto, $cuerpo, true);
    }
    
    /**
     * Enviar reporte semanal
     */
    public function enviarReporteSemanal($emailAdmin, $datos) {
        $asunto = "📊 Reporte Semanal - SERCOLTUR [" . date('Y-m-d') . "]";
        
        $cuerpo = $this->plantillaReporteSemanal($datos);
        
        return $this->enviarEmail($emailAdmin, $asunto, $cuerpo, true);
    }
    
    /**
     * Enviar notificación a asesor
     */
    public function enviarNotificacionAsesor($asesor, $reserva, $cliente) {
        $asunto = "🔔 NUEVA RESERVA - {$reserva['codigo_whatsapp']}";
        
        $cuerpo = $this->plantillaNotificacionAsesor($asesor, $reserva, $cliente);
        
        return $this->enviarEmail($asesor['email'], $asunto, $cuerpo, true);
    }
    
    /**
     * Plantilla: Confirmación de Reserva
     */
    private function plantillaConfirmacionReserva($cliente, $reserva) {
        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .content { color: #333; line-height: 1.6; }
                .detail { background: #f9f9f9; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
                .btn { display: inline-block; background: #667eea; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ ¡Reserva Confirmada!</h1>
                    <p>Tu reserva ha sido registrada exitosamente</p>
                </div>
                
                <div class='content'>
                    <p>Hola <strong>{$cliente['nombre']}</strong>,</p>
                    
                    <p>Nos complace confirmar que tu reserva ha sido procesada. A continuación encontrarás los detalles:</p>
                    
                    <div class='detail'>
                        <strong>📋 Código de Reserva:</strong> {$reserva['codigo_whatsapp']}<br>
                        <strong>🎭 Tour:</strong> {$reserva['tour']}<br>
                        <strong>📅 Fecha:</strong> {$reserva['fecha_inicio']}<br>
                        <strong>👥 Personas:</strong> {$reserva['cantidad_personas']}<br>
                        <strong>💰 Total:</strong> \${" . number_format($reserva['precio_total'], 0, ',', '.') . "}
                    </div>
                    
                    <p><strong>Próximos pasos:</strong></p>
                    <ul>
                        <li>Un asesor se comunicará contigo en breve para confirmar los detalles</li>
                        <li>Recibirás instrucciones de pago por email</li>
                        <li>Conserva tu código de reserva para consultas futuras</li>
                    </ul>
                    
                    <p><strong>¿Preguntas?</strong><br>
                    Contáctanos en:<br>
                    📱 +57 302 253 1580<br>
                    📧 info@sercoltur.com</p>
                </div>
                
                <div class='footer'>
                    <p>© 2025 SERCOLTUR - Todos los derechos reservados</p>
                    <p>Este es un correo automático, por favor no responder a este email</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Plantilla: Recordatorio de Cita
     */
    private function plantillaRecordatorioCita($cliente, $cita) {
        $horaMinutos = substr($cita['fecha_hora'], 11, 5);
        
        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
                .header { background: #ff9800; color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .content { color: #333; line-height: 1.6; }
                .detail { background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800; margin: 15px 0; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 Recordatorio de tu Cita</h1>
                </div>
                
                <div class='content'>
                    <p>Hola <strong>{$cliente['nombre']}</strong>,</p>
                    
                    <p>Te recordamos que tienes una cita próximamente:</p>
                    
                    <div class='detail'>
                        <strong>⏰ Hora:</strong> {$horaMinutos}<br>
                        <strong>📅 Fecha:</strong> " . date('d/m/Y', strtotime($cita['fecha_hora'])) . "<br>
                        <strong>🎯 Servicio:</strong> {$cita['servicio']}<br>
                        <strong>🎫 Código:</strong> {$cita['codigo']}
                    </div>
                    
                    <p><strong>¿Necesitas reprogramar o cancelar?</strong><br>
                    Contáctanos con tiempo:<br>
                    📱 +57 302 253 1580<br>
                    💬 WhatsApp: +57 302 253 1580</p>
                </div>
                
                <div class='footer'>
                    <p>© 2025 SERCOLTUR</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Plantilla: Reporte Semanal
     */
    private function plantillaReporteSemanal($datos) {
        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; }
                .header { background: #2c3e50; color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .section { margin: 20px 0; }
                .section h3 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background: #ecf0f1; font-weight: bold; }
                .stat { display: inline-block; background: #ecf0f1; padding: 15px; margin: 10px; border-radius: 5px; }
                .stat-value { font-size: 24px; font-weight: bold; color: #3498db; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📊 Reporte Semanal - SERCOLTUR</h1>
                    <p>Período: {$datos['periodo']}</p>
                </div>
                
                <div class='section'>
                    <h3>📈 Estadísticas Generales</h3>
                    <div class='stat'>
                        <div class='stat-value'>" . ($datos['reservas']['total'] ?? 0) . "</div>
                        <div>Nuevas Reservas</div>
                    </div>
                    <div class='stat'>
                        <div class='stat-value'>" . ($datos['ventas']['total'] ?? 0) . "</div>
                        <div>Ventas Confirmadas</div>
                    </div>
                    <div class='stat'>
                        <div class='stat-value'>\$" . number_format($datos['ventas']['ingresos'] ?? 0, 0, ',', '.') . "</div>
                        <div>Ingresos Totales</div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>💬 Conversaciones por Tipo</h3>
                    <table>
                        <tr><th>Tipo</th><th>Cantidad</th></tr>
                        " . (isset($datos['conversaciones']) ? implode('', array_map(fn($c) => "<tr><td>{$c['tipo_consulta']}</td><td>{$c['total']}</td></tr>", $datos['conversaciones'])) : '<tr><td colspan="2">Sin datos</td></tr>') . "
                    </table>
                </div>
                
                <div class='section'>
                    <h3>📅 Citas Agendadas</h3>
                    <table>
                        <tr><th>Estado</th><th>Cantidad</th></tr>
                        " . (isset($datos['citas']) ? implode('', array_map(fn($c) => "<tr><td>{$c['estado']}</td><td>{$c['total']}</td></tr>", $datos['citas'])) : '<tr><td colspan="2">Sin datos</td></tr>') . "
                    </table>
                </div>
                
                <div class='footer'>
                    <p>Reporte generado automáticamente el " . date('d/m/Y H:i:s') . "</p>
                    <p>© 2025 SERCOLTUR</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Plantilla: Notificación para Asesor
     */
    private function plantillaNotificacionAsesor($asesor, $reserva, $cliente) {
        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
                .header { background: #27ae60; color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .content { color: #333; line-height: 1.6; }
                .detail { background: #f0f8f4; padding: 15px; border-left: 4px solid #27ae60; margin: 15px 0; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔔 NUEVA RESERVA ASIGNADA</h1>
                </div>
                
                <div class='content'>
                    <p>Hola {$asesor['nombre']},</p>
                    
                    <p>Se ha asignado una nueva reserva que requiere tu confirmación:</p>
                    
                    <div class='detail'>
                        <strong>Código:</strong> {$reserva['codigo_whatsapp']}<br>
                        <strong>🎭 Tour:</strong> {$reserva['tour']}<br>
                        <strong>👤 Cliente:</strong> {$cliente['nombre']}<br>
                        <strong>📱 Teléfono:</strong> {$cliente['telefono']}<br>
                        <strong>👥 Personas:</strong> {$reserva['cantidad_personas']}<br>
                        <strong>📅 Fecha:</strong> {$reserva['fecha_inicio']}<br>
                        <strong>💰 Valor:</strong> \${" . number_format($reserva['precio_total'], 0, ',', '.') . "}
                    </div>
                    
                    <p><strong>Acción requerida:</strong><br>
                    Por favor confirma esta reserva en el dashboard o contacta al cliente para validar los detalles.</p>
                </div>
                
                <div class='footer'>
                    <p>© 2025 SERCOLTUR</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Registrar envío de email en base de datos
     */
    private function registrarEnvio($para, $asunto, $exitoso) {
        if (!$this->pdo) return;
        
        try {
            $this->pdo->prepare("
                INSERT INTO email_log (destinatario, asunto, estado, fecha_envio)
                VALUES (?, ?, ?, NOW())
            ")->execute([$para, $asunto, $exitoso ? 'enviado' : 'fallido']);
        } catch (Exception $e) {
            // Silenciar si la tabla no existe
        }
    }
}
?>
