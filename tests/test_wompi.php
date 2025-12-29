<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * TEST - SERVICIO DE PAGOS WOMPI
 * Archivo: tests/test_wompi.php
 * ═══════════════════════════════════════════════════════════════
 */

// Configuración
define('BASE_PATH', __DIR__ . '/..');

// Headers para API
header('Content-Type: application/json');

// Incluir servicios
require_once BASE_PATH . '/config/config_empresarial.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/services/PagoService.php';

// Conectar a BD
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=sercolturbot",
        "root",
        "C121672@c",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die(json_encode(['error' => 'DB Connection: ' . $e->getMessage()]));
}

// Inicializar servicio
$pagoService = new PagoService($pdo);

// Obtener acción
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'info';

// Respuestas
$respuestas = [];

try {
    switch ($accion) {
        
        case 'info':
            $respuestas['title'] = 'WOMPI Pago Service - Test Panel';
            $respuestas['endpoints'] = [
                [
                    'accion' => 'crear_pago',
                    'descripcion' => 'Crear un pago de prueba',
                    'metodo' => 'POST',
                    'parametros' => [
                        'monto' => 10000,
                        'email' => 'cliente@example.com',
                        'nombre_cliente' => 'Juan Pérez'
                    ]
                ],
                [
                    'accion' => 'verificar_pago',
                    'descripcion' => 'Verificar estado de un pago',
                    'metodo' => 'GET',
                    'parametros' => ['referencia' => 'PAGO-xxxxxxxx']
                ],
                [
                    'accion' => 'procesar_webhook',
                    'descripcion' => 'Procesar webhook de Wompi',
                    'metodo' => 'POST',
                    'parametros' => [
                        'event' => 'transaction.updated',
                        'data' => [
                            'transaction' => [
                                'reference' => 'PAGO-xxxxxxxx',
                                'status' => 'APPROVED'
                            ]
                        ]
                    ]
                ]
            ];
            $respuestas['estado_servicio'] = 'Operativo';
            $respuestas['ambiente'] = $GLOBALS['config']['wompi']['ambiente'];
            break;
            
        case 'crear_pago':
            $datos = [
                'monto' => $_POST['monto'] ?? 10000,
                'email' => $_POST['email'] ?? 'test@example.com',
                'referencia' => 'TEST-' . time()
            ];
            
            $resultado = $pagoService->crearPago($datos);
            $respuestas = $resultado;
            break;
            
        case 'verificar_pago':
            $referencia = $_GET['referencia'] ?? $_POST['referencia'] ?? null;
            
            if (!$referencia) {
                throw new Exception('Referencia requerida');
            }
            
            $resultado = $pagoService->verificarPago($referencia);
            $respuestas = $resultado;
            break;
            
        case 'procesar_webhook':
            $datos = json_decode(file_get_contents('php://input'), true);
            
            if (!$datos) {
                $datos = $_POST;
            }
            
            $resultado = $pagoService->procesarWebhook($datos);
            $respuestas = $resultado;
            break;
            
        case 'listar_pagos':
            $sql = "SELECT * FROM pagos ORDER BY fecha_creacion DESC LIMIT 20";
            $stmt = $pdo->query($sql);
            $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $respuestas = [
                'total' => count($pagos),
                'pagos' => $pagos
            ];
            break;
            
        case 'estadisticas':
            $sql = "
                SELECT 
                    estado,
                    COUNT(*) as cantidad,
                    SUM(monto) as total
                FROM pagos
                GROUP BY estado
            ";
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $respuestas = [
                'estadisticas' => $stats,
                'total_pagos' => array_sum(array_column($stats, 'cantidad')),
                'monto_total' => array_sum(array_column($stats, 'total'))
            ];
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $accion);
    }
    
} catch (Exception $e) {
    $respuestas = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    http_response_code(400);
}

// Retornar JSON
echo json_encode($respuestas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
