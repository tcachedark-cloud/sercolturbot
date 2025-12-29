<?php
/**
 * TEST DE VERIFICACIÓN DEL SISTEMA SERCOLTURBOT
 * Este script verifica que todo esté instalado correctamente
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación SERCOLTURBOT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .test-section {
            margin: 25px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }
        .test-section h2 {
            color: #2196F3;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .test-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .test-item.pass {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            color: #2e7d32;
        }
        .test-item.fail {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
        .test-item.warning {
            background: #fff3e0;
            border-left: 4px solid #FF9800;
            color: #e65100;
        }
        .icon {
            font-size: 20px;
            min-width: 20px;
        }
        .test-name {
            flex: 1;
        }
        .test-value {
            color: #999;
            font-size: 12px;
            font-family: monospace;
        }
        .summary {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .summary.success {
            background: #e8f5e9;
            border: 2px solid #4CAF50;
            color: #2e7d32;
        }
        .summary.partial {
            background: #fff3e0;
            border: 2px solid #FF9800;
            color: #e65100;
        }
        .summary.error {
            background: #ffebee;
            border: 2px solid #f44336;
            color: #c62828;
        }
        .summary h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .count {
            font-size: 12px;
            opacity: 0.8;
        }
        .action-links {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            padding: 10px 20px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #1976D2;
        }
        .btn.primary {
            background: #4CAF50;
        }
        .btn.primary:hover {
            background: #388E3C;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación del Sistema SERCOLTURBOT</h1>
        <p class="subtitle">Comprobando que todo esté instalado correctamente</p>

        <?php
        $tests = [
            'pass' => [],
            'fail' => [],
            'warning' => []
        ];

        // TEST 1: Archivos existentes
        echo '<div class="test-section">';
        echo '<h2>📁 Verificación de Archivos</h2>';

        $archivos_requeridos = [
            'config/database.php' => 'Configuración de BD',
            'services/BotService.php' => 'Servicio del Bot',
            'routes/bot_api.php' => 'API del Bot',
            'public/chat_demo.php' => 'Demo Interactiva',
            'setup/database.sql' => 'Script SQL',
            'README.md' => 'Documentación'
        ];

        foreach ($archivos_requeridos as $archivo => $desc) {
            $path = __DIR__ . '/' . $archivo;
            if (file_exists($path)) {
                $size = filesize($path);
                echo '<div class="test-item pass">';
                echo '<span class="icon">✓</span>';
                echo '<span class="test-name">' . $desc . '</span>';
                echo '<span class="test-value">' . $archivo . ' (' . number_format($size / 1024, 1) . ' KB)</span>';
                echo '</div>';
                $tests['pass'][] = $desc;
            } else {
                echo '<div class="test-item fail">';
                echo '<span class="icon">✗</span>';
                echo '<span class="test-name">' . $desc . ' - NO ENCONTRADO</span>';
                echo '<span class="test-value">' . $archivo . '</span>';
                echo '</div>';
                $tests['fail'][] = $desc;
            }
        }
        echo '</div>';

        // TEST 2: Conexión a BD
        echo '<div class="test-section">';
        echo '<h2>🗄️ Verificación de Base de Datos</h2>';

        try {
            require_once(__DIR__ . '/config/database.php');
            
            // Verificar conexión
            $result = $pdo->query("SELECT DATABASE() as db");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            echo '<div class="test-item pass">';
            echo '<span class="icon">✓</span>';
            echo '<span class="test-name">Conexión a BD</span>';
            echo '<span class="test-value">BD: ' . $row['db'] . '</span>';
            echo '</div>';
            $tests['pass'][] = 'Conexión a BD';

            // Contar tablas
            $result = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $tabla_count = $row['count'];
            
            if ($tabla_count >= 10) {
                echo '<div class="test-item pass">';
                echo '<span class="icon">✓</span>';
                echo '<span class="test-name">Tablas de BD</span>';
                echo '<span class="test-value">' . $tabla_count . ' tablas encontradas</span>';
                echo '</div>';
                $tests['pass'][] = 'Tablas creadas';
            } else {
                echo '<div class="test-item warning">';
                echo '<span class="icon">⚠</span>';
                echo '<span class="test-name">Tablas de BD</span>';
                echo '<span class="test-value">' . $tabla_count . ' tablas (se esperaban 10)</span>';
                echo '</div>';
                $tests['warning'][] = 'Número de tablas incompleto';
            }

            // Verificar tablas específicas
            $tablas_criticas = ['clientes', 'asesores', 'guias', 'buses', 'tours', 'reservas', 'bot_conversaciones'];
            foreach ($tablas_criticas as $tabla) {
                try {
                    $result = $pdo->query("SELECT COUNT(*) as count FROM " . $tabla);
                    $row = $result->fetch(PDO::FETCH_ASSOC);
                    echo '<div class="test-item pass">';
                    echo '<span class="icon">✓</span>';
                    echo '<span class="test-name">Tabla: ' . $tabla . '</span>';
                    echo '<span class="test-value">' . $row['count'] . ' registros</span>';
                    echo '</div>';
                    $tests['pass'][] = 'Tabla ' . $tabla;
                } catch (Exception $e) {
                    echo '<div class="test-item fail">';
                    echo '<span class="icon">✗</span>';
                    echo '<span class="test-name">Tabla: ' . $tabla . ' - ERROR</span>';
                    echo '<span class="test-value">' . $e->getMessage() . '</span>';
                    echo '</div>';
                    $tests['fail'][] = 'Tabla ' . $tabla;
                }
            }

        } catch (Exception $e) {
            echo '<div class="test-item fail">';
            echo '<span class="icon">✗</span>';
            echo '<span class="test-name">Conexión a BD - FALLIDA</span>';
            echo '<span class="test-value">' . $e->getMessage() . '</span>';
            echo '</div>';
            $tests['fail'][] = 'Conexión a BD';
        }
        echo '</div>';

        // TEST 3: Servicios
        echo '<div class="test-section">';
        echo '<h2>🔧 Verificación de Servicios</h2>';

        try {
            require_once(__DIR__ . '/services/BotService.php');
            echo '<div class="test-item pass">';
            echo '<span class="icon">✓</span>';
            echo '<span class="test-name">Clase BotService</span>';
            echo '<span class="test-value">Cargada correctamente</span>';
            echo '</div>';
            $tests['pass'][] = 'BotService';
        } catch (Exception $e) {
            echo '<div class="test-item fail">';
            echo '<span class="icon">✗</span>';
            echo '<span class="test-name">Clase BotService - ERROR</span>';
            echo '<span class="test-value">' . $e->getMessage() . '</span>';
            echo '</div>';
            $tests['fail'][] = 'BotService';
        }

        echo '</div>';

        // TEST 4: Permisos y Directorios
        echo '<div class="test-section">';
        echo '<h2>🔐 Verificación de Permisos</h2>';

        $dirs_check = [
            'logs' => 'Directorio de Logs',
            'setup' => 'Directorio Setup',
            'routes' => 'Directorio Routes'
        ];

        foreach ($dirs_check as $dir => $desc) {
            $path = __DIR__ . '/' . $dir;
            if (is_dir($path)) {
                if (is_writable($path)) {
                    echo '<div class="test-item pass">';
                    echo '<span class="icon">✓</span>';
                    echo '<span class="test-name">' . $desc . ' - Escribible</span>';
                    echo '<span class="test-value">' . $dir . '/</span>';
                    echo '</div>';
                    $tests['pass'][] = $desc . ' (writable)';
                } else {
                    echo '<div class="test-item warning">';
                    echo '<span class="icon">⚠</span>';
                    echo '<span class="test-name">' . $desc . ' - Sin permisos de escritura</span>';
                    echo '<span class="test-value">' . $dir . '/</span>';
                    echo '</div>';
                    $tests['warning'][] = $desc . ' (no writable)';
                }
            } else {
                echo '<div class="test-item fail">';
                echo '<span class="icon">✗</span>';
                echo '<span class="test-name">' . $desc . ' - NO EXISTE</span>';
                echo '<span class="test-value">' . $dir . '/</span>';
                echo '</div>';
                $tests['fail'][] = $desc;
            }
        }

        echo '</div>';

        // Resumen
        $total_pass = count($tests['pass']);
        $total_fail = count($tests['fail']);
        $total_warning = count($tests['warning']);
        $total = $total_pass + $total_fail + $total_warning;

        echo '<div class="summary ';
        if ($total_fail > 0) {
            echo 'error';
        } elseif ($total_warning > 0) {
            echo 'partial';
        } else {
            echo 'success';
        }
        echo '">';

        if ($total_fail > 0) {
            echo '<h2>❌ VERIFICACIÓN FALLIDA</h2>';
            echo '<p>Se encontraron ' . $total_fail . ' errores críticos que necesitan atención.</p>';
        } elseif ($total_warning > 0) {
            echo '<h2>⚠️ VERIFICACIÓN CON ADVERTENCIAS</h2>';
            echo '<p>El sistema está funcionando pero con ' . $total_warning . ' advertencias.</p>';
        } else {
            echo '<h2>✅ ¡VERIFICACIÓN EXITOSA!</h2>';
            echo '<p>Todos los componentes están instalados correctamente.</p>';
        }

        echo '<p class="count">Pruebas: ' . $total_pass . ' ✓ | ' . $total_warning . ' ⚠ | ' . $total_fail . ' ✗</p>';

        echo '<div class="action-links">';
        if ($total_fail === 0) {
            echo '<a href="public/chat_demo.php" class="btn primary">💬 Probar Bot</a>';
            echo '<a href="setup/" class="btn primary">🗄️ Panel BD</a>';
        } else {
            echo '<a href="setup/" class="btn">🗄️ Crear BD</a>';
        }
        echo '<a href="javascript:location.reload()" class="btn">🔄 Recargar</a>';
        echo '</div>';

        echo '</div>';
        ?>

    </div>
</body>
</html>
