<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SERVICIO DE PAGOS WOMPI - SERCOLTURBOT
 * Procesamiento completo de pagos en línea con Wompi
 * ═══════════════════════════════════════════════════════════════
 */

class PagoService {
    private $config;
    private $pdo;
    private $ambiente;
    private $publicKey;
    private $privateKey;
    private $wompiUrl;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        $this->config = require(__DIR__ . '/../config/config_empresarial.php');
        
        if ($this->config['wompi']['habilitado']) {
            $this->ambiente = $this->config['wompi']['ambiente']; // 'sandbox' o 'production'
            $this->publicKey = $this->config['wompi']['public_key'];
            $this->privateKey = $this->config['wompi']['private_key'];
            
            $this->wompiUrl = $this->ambiente === 'production' 
                ? 'https://api.wompi.co/v1'
                : 'https://staging.wompi.co/v1';
        }
    }
    
    /**
     * Crear link de pago con Wompi
     */
    public function crearLinkPago(array $reserva, array $cliente): ?array {
        $wompi = $this->config['wompi'] ?? [];
        
        if (empty($wompi['habilitado']) || empty($wompi['private_key'])) {
            return null;
        }
        
        try {
            $referencia = 'SER-' . time() . '-' . ($reserva['id'] ?? rand(1000, 9999));
            $monto = (int)(($reserva['total'] ?? $reserva['precio_total'] ?? 0) * 100);
            
            $baseUrl = $wompi['ambiente'] === 'production' 
                ? 'https://production.wompi.co/v1'
                : 'https://sandbox.wompi.co/v1';
            
            $payload = [
                'name' => "Reserva Tour - " . ($reserva['codigo'] ?? $referencia),
                'description' => "Pago reserva SERCOLTUR - " . ($cliente['nombre'] ?? 'Cliente'),
                'single_use' => true,
                'collect_shipping' => false,
                'currency' => 'COP',
                'amount_in_cents' => $monto,
            ];
            
            $ch = curl_init("$baseUrl/payment_links");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $wompi['private_key'],
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 || $httpCode === 201) {
                $data = json_decode($response, true);
                $referencia = $data['data']['reference'] ?? $referencia;
                
                // Registrar pago
                $this->registrarPago($referencia, $monto / 100, $cliente, $reserva);
                
                return [
                    'success' => true,
                    'link' => $data['data']['url'] ?? null,
                    'referencia' => $referencia,
                ];
            }
            
            return ['success' => false, 'error' => 'Error creando link'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Crear pago directo (no solo link)
     */
    public function crearPago($datos) {
        if (!$this->config['wompi']['habilitado']) {
            return ['success' => false, 'error' => 'Wompi no habilitado'];
        }
        
        try {
            $monto = intval($datos['monto']);
            $referencia = $datos['referencia'] ?? 'PAGO-' . time();
            $email = $datos['email'] ?? '';
            
            if ($monto <= 0) {
                throw new Exception('El monto debe ser mayor a 0');
            }
            
            // Registrar pago en BD
            $this->registrarPago($referencia, $monto, ['email' => $email], []);
            
            return [
                'success' => true,
                'referencia' => $referencia,
                'monto' => $monto,
                'mensaje' => 'Pago registrado'
            ];
            
        } catch (Exception $e) {
            $this->registrarError($referencia ?? '', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Verificar estado de un pago
     */
    public function verificarPago($referencia) {
        if (!$this->config['wompi']['habilitado']) {
            return ['success' => false, 'error' => 'Wompi no habilitado'];
        }
        
        try {
            $baseUrl = $this->config['wompi']['ambiente'] === 'production' 
                ? 'https://api.wompi.co/v1'
                : 'https://staging.wompi.co/v1';
            
            $url = "$baseUrl/transactions?reference=" . urlencode($referencia);
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->config['wompi']['private_key'],
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                
                if (isset($data['data']) && !empty($data['data'])) {
                    $transaccion = $data['data'][0];
                    $estado = $transaccion['status'] ?? 'unknown';
                    
                    // Actualizar en BD
                    $this->actualizarEstadoPago($referencia, $estado);
                    
                    return [
                        'success' => true,
                        'referencia' => $referencia,
                        'estado' => $estado,
                        'id' => $transaccion['id'] ?? null,
                    ];
                }
            }
            
            return ['success' => false, 'error' => 'Pago no encontrado'];
            
        } catch (Exception $e) {
            error_log("Error verificando pago: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Procesar webhook de Wompi
     */
    public function procesarWebhook($datos) {
        try {
            $evento = $datos['event'] ?? '';
            $datosTransaccion = $datos['data']['transaction'] ?? [];
            
            if ($evento === 'transaction.updated') {
                $referencia = $datosTransaccion['reference'] ?? '';
                $estado = $datosTransaccion['status'] ?? '';
                
                // Actualizar estado
                $this->actualizarEstadoPago($referencia, $estado);
                
                // Si pago completado
                if ($estado === 'APPROVED') {
                    $this->procesarPagoAprobado($referencia);
                } elseif (in_array($estado, ['DECLINED', 'REJECTED'])) {
                    $this->procesarPagoRechazado($referencia);
                }
                
                return ['success' => true, 'mensaje' => 'Webhook procesado'];
            }
            
        } catch (Exception $e) {
            error_log("Error procesando webhook: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Procesar pago aprobado
     */
    private function procesarPagoAprobado($referencia) {
        try {
            if (!$this->pdo) return;
            
            // Obtener datos de pago
            $sql = "SELECT reserva_id FROM pagos WHERE referencia = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$referencia]);
            $pago = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pago && isset($pago['reserva_id'])) {
                // Actualizar reserva a confirmada
                $this->pdo->prepare("
                    UPDATE reservas 
                    SET estado = 'confirmada', fecha_confirmacion = NOW()
                    WHERE id = ?
                ")->execute([$pago['reserva_id']]);
            }
            
            // Log
            file_put_contents(
                __DIR__ . '/../logs/pagos.log',
                "[" . date('Y-m-d H:i:s') . "] Pago aprobado: $referencia\n",
                FILE_APPEND
            );
            
        } catch (Exception $e) {
            error_log("Error procesando pago aprobado: " . $e->getMessage());
        }
    }
    
    /**
     * Procesar pago rechazado
     */
    private function procesarPagoRechazado($referencia) {
        try {
            if (!$this->pdo) return;
            
            // Actualizar estado a fallido
            $this->pdo->prepare("
                UPDATE pagos 
                SET estado = 'fallido'
                WHERE referencia = ?
            ")->execute([$referencia]);
            
            file_put_text(
                __DIR__ . '/../logs/pagos.log',
                "[" . date('Y-m-d H:i:s') . "] Pago rechazado: $referencia\n",
                FILE_APPEND
            );
            
        } catch (Exception $e) {
            error_log("Error procesando pago rechazado: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar pago en BD
     */
    private function registrarPago($referencia, $monto, $cliente, $reserva) {
        if (!$this->pdo) return;
        
        try {
            $this->pdo->prepare("
                INSERT INTO pagos (referencia, monto, email, estado, fecha_creacion)
                VALUES (?, ?, ?, 'iniciado', NOW())
                ON DUPLICATE KEY UPDATE estado = 'reiniciado'
            ")->execute([$referencia, $monto, $cliente['email'] ?? '']);
        } catch (Exception $e) {
            // Silenciar si tabla no existe
        }
    }
    
    /**
     * Actualizar estado de pago
     */
    private function actualizarEstadoPago($referencia, $estado) {
        if (!$this->pdo) return;
        
        try {
            $this->pdo->prepare("
                UPDATE pagos 
                SET estado = ?, fecha_actualizacion = NOW()
                WHERE referencia = ?
            ")->execute([$estado, $referencia]);
        } catch (Exception $e) {
            // Silenciar
        }
    }
    
    /**
     * Registrar error
     */
    private function registrarError($referencia, $error) {
        $logFile = __DIR__ . '/../logs/pagos_errores.log';
        if (!is_dir(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0755, true);
        }
        file_put_contents(
            $logFile,
            "[" . date('Y-m-d H:i:s') . "] $referencia - ERROR: $error\n",
            FILE_APPEND
        );
    }
    
    /**
     * Generar mensaje de pago para WhatsApp
     */
    public function generarMensajePago(array $reserva, string $linkPago): string {
        $codigo = $reserva['codigo'] ?? $reserva['codigo_whatsapp'] ?? 'N/A';
        $total = $reserva['total'] ?? $reserva['precio_total'] ?? 0;
        
        $mensaje = "💳 *LINK DE PAGO*\n\n";
        $mensaje .= "📋 Reserva: *{$codigo}*\n";
        $mensaje .= "💰 Total: *$" . number_format($total, 0, ',', '.') . " COP*\n\n";
        $mensaje .= "🔗 Paga aquí:\n{$linkPago}\n\n";
        $mensaje .= "✅ El link es seguro.\n\n";
        $mensaje .= "🚌 *SERCOLTUR*";
        
        return $mensaje;
    }
}