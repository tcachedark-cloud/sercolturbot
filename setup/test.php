<?php
/**
 * SCRIPT DE PRUEBA DEL SISTEMA
 * Verifica que todas las configuraciones sean correctas
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test SERCOLTURBOT</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        .test { margin: 20px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .test.pass { border-left-color: #28a745; background: #d4edda; }
        .test.fail { border-left-color: #dc3545; background: #f8d7da; }
        .test.warn { border-left-color: #ffc107; background: #fff3cd; }
        strong { display: block; margin-bottom: 5px; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Test de Configuración SERCOLTURBOT</h1>
    <p>Ejecutando verificaciones...</p>";

$passed = 0;
$failed = 0;
$warned = 0;

// Test 1: PHP Version
echo "<div class='test ";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "pass";
    $passed++;
} else {
    echo "fail";
    $failed++;
}
echo "'>";
echo "<strong>✓ Versión PHP</strong>";
echo "PHP " . PHP_VERSION . " (Requerido: 7.4+)";
echo "</div>";

// Test 2: Database Connection
echo "<div class='test ";
try {
    require_once(__DIR__ . '/config/database.php');
    $stmt = $pdo->query("SELECT 1");
    echo "pass";
    $passed++;
    echo "'>";
    echo "<strong>✓ Conexión a Base de Datos</strong>";
    echo "Conectado exitosamente a: sercolturbot";
} catch (Exception $e) {
    echo "fail";
    $failed++;
    echo "'>";
    echo "<strong>✗ Conexión a Base de Datos</strong>";
    echo "Error: " . $e->getMessage();
}
echo "</div>";

// Test 3: Database Tables
if (isset($pdo)) {
    $requiredTables = ['clientes', 'asesores', 'guias', 'buses', 'tours', 'reservas', 'asignaciones', 'bot_conversaciones'];
    
    foreach ($requiredTables as $table) {
        echo "<div class='test ";
        try {
            $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "pass";
            $passed++;
        } catch (Exception $e) {
            echo "fail";
            $failed++;
        }
        echo "'>";
        echo "<strong>📊 Tabla: " . ucfirst($table) . "</strong>";
        echo "</div>";
    }
}

// Test 4: Directories
$directories = [
    'logs' => 'Logs',
    'config' => 'Configuración',
    'routes' => 'Rutas',
    'public' => 'Público'
];

foreach ($directories as $dir => $name) {
    echo "<div class='test ";
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "pass";
        $passed++;
    } else {
        echo "fail";
        $failed++;
    }
    echo "'>";
    echo "<strong>📁 Directorio: " . $name . "</strong>";
    echo "Ruta: " . $dir;
    echo "</div>";
}

// Test 5: Required Files
$files = [
    'config/database.php' => 'Configuración BD',
    'config/whatsapp_config.php' => 'Configuración WhatsApp',
    'services/BotService.php' => 'Servicio Bot',
    'services/WhatsAppService.php' => 'Servicio WhatsApp',
    'routes/bot_api.php' => 'API Bot',
    'routes/whatsapp_webhook.php' => 'Webhook WhatsApp',
    'public/dashboard.php' => 'Dashboard'
];

foreach ($files as $file => $name) {
    echo "<div class='test ";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "pass";
        $passed++;
    } else {
        echo "fail";
        $failed++;
    }
    echo "'>";
    echo "<strong>📄 Archivo: " . $name . "</strong>";
    echo "Ruta: " . $file;
    echo "</div>";
}

// Test 6: cURL Extension
echo "<div class='test ";
if (extension_loaded('curl')) {
    echo "pass";
    $passed++;
} else {
    echo "fail";
    $failed++;
}
echo "'>";
echo "<strong>🔧 Extensión cURL</strong>";
echo extension_loaded('curl') ? "✓ Instalada" : "✗ No instalada (necesaria para WhatsApp)";
echo "</div>";

// Test 7: File Permissions
echo "<div class='test ";
if (is_writable(__DIR__ . '/logs')) {
    echo "pass";
    $passed++;
} else {
    echo "warn";
    $warned++;
}
echo "'>";
echo "<strong>📝 Permisos Logs</strong>";
echo is_writable(__DIR__ . '/logs') ? "✓ Escribible" : "⚠ No se puede escribir en logs";
echo "</div>";

// Test 8: WhatsApp Configuration
echo "<div class='test ";
$config = include(__DIR__ . '/config/whatsapp_config.php');
if (defined('META_PHONE_NUMBER_ID') && META_PHONE_NUMBER_ID !== 'TU_PHONE_NUMBER_ID') {
    echo "pass";
    $passed++;
    echo "'>";
    echo "<strong>✓ Credenciales WhatsApp</strong>";
    echo "Configuradas y listas para usar";
} else {
    echo "warn";
    $warned++;
    echo "'>";
    echo "<strong>⚠ Credenciales WhatsApp</strong>";
    echo "No configuradas. Ver <code>WHATSAPP_SETUP.md</code>";
}
echo "</div>";

// Summary
echo "<hr>";
echo "<h2>📊 Resumen</h2>";
echo "<p>";
echo "✅ Pasadas: <strong style='color: #28a745;'>" . $passed . "</strong><br>";
echo "❌ Fallidas: <strong style='color: #dc3545;'>" . $failed . "</strong><br>";
echo "⚠️ Advertencias: <strong style='color: #ffc107;'>" . $warned . "</strong>";
echo "</p>";

if ($failed === 0) {
    echo "<div class='test pass'>";
    echo "<strong>✅ Sistema Listo</strong>";
    echo "Todas las verificaciones críticas pasaron. El sistema está listo para usarse.";
    echo "</div>";
} else {
    echo "<div class='test fail'>";
    echo "<strong>❌ Errores Detectados</strong>";
    echo "Por favor resuelve los errores antes de continuar.";
    echo "</div>";
}

echo "</div>
</body>
</html>";
