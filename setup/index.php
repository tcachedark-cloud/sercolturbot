<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicializador Base de Datos - SERCOLTURBOT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 800px;
            width: 100%;
            padding: 40px;
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
        .button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 200px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 2px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e8e8e8;
            border-color: #999;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #1565c0;
        }
        .success-box {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #2e7d32;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #FF9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #e65100;
        }
        .error-box {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #b71c1c;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #f0f0f0;
            text-align: center;
        }
        .feature-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .feature-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .feature-desc {
            font-size: 13px;
            color: #666;
        }
        .command-box {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            overflow-x: auto;
            color: #333;
        }
        .code {
            color: #e53935;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .emoji {
            font-size: 20px;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="emoji">🗄️</span>SERCOLTURBOT</h1>
        <p class="subtitle">Sistema de Gestión de Reservas y Bot de Atención</p>
        
        <div class="button-group">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=crear" class="btn btn-primary">
                <span class="emoji">✅</span>Crear Base de Datos
            </a>
            <a href="setup/database.sql" class="btn btn-secondary" download>
                <span class="emoji">📄</span>Descargar SQL
            </a>
        </div>

        <?php
        $action = $_GET['action'] ?? null;

        if ($action === 'crear') {
            include(__DIR__ . '/database_setup.php');
        } else {
            // Mostrar información
            ?>
            
            <div class="info-box">
                <strong>ℹ️ Bienvenido</strong><br>
                Este panel te ayudará a configurar la base de datos completa con todas las tablas necesarias para SERCOLTURBOT.
            </div>

            <h2 style="margin-top: 30px; margin-bottom: 15px;">📋 Características del Sistema</h2>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">👥</div>
                    <div class="feature-title">Clientes</div>
                    <div class="feature-desc">Gestión completa de clientes con datos personales y contacto</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">👨‍💼</div>
                    <div class="feature-title">Asesores</div>
                    <div class="feature-desc">Asesores especializados en diferentes tipos de tours</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">👨‍🏫</div>
                    <div class="feature-title">Guías</div>
                    <div class="feature-desc">Guías turísticos multilingües con calificaciones</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🚌</div>
                    <div class="feature-title">Buses</div>
                    <div class="feature-desc">Transporte con capacidades y disponibilidad</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🎫</div>
                    <div class="feature-title">Reservas</div>
                    <div class="feature-desc">Sistema completo de reservas de tours</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🤖</div>
                    <div class="feature-title">Bot Inteligente</div>
                    <div class="feature-desc">Responde en tiempo real a consultas de clientes</div>
                </div>
            </div>

            <h2 style="margin-top: 30px; margin-bottom: 15px;">📊 Tablas de la Base de Datos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tabla</th>
                        <th>Descripción</th>
                        <th>Funcionalidad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>clientes</strong></td>
                        <td>Datos de clientes</td>
                        <td>Información personal y contacto</td>
                    </tr>
                    <tr>
                        <td><strong>asesores</strong></td>
                        <td>Personal asesor</td>
                        <td>Asesores disponibles con especialidad</td>
                    </tr>
                    <tr>
                        <td><strong>guias</strong></td>
                        <td>Guías turísticos</td>
                        <td>Guías multilingües con experiencia</td>
                    </tr>
                    <tr>
                        <td><strong>buses</strong></td>
                        <td>Transporte</td>
                        <td>Buses con capacidades y estado</td>
                    </tr>
                    <tr>
                        <td><strong>tours</strong></td>
                        <td>Paquetes turísticos</td>
                        <td>Tours disponibles con precios</td>
                    </tr>
                    <tr>
                        <td><strong>reservas</strong></td>
                        <td>Reservas de tours</td>
                        <td>Gestión de reservas con estado</td>
                    </tr>
                    <tr>
                        <td><strong>asignaciones</strong></td>
                        <td>Asignación de recursos</td>
                        <td>Vincula guía, bus y asesor a cada reserva</td>
                    </tr>
                    <tr>
                        <td><strong>bot_conversaciones</strong></td>
                        <td>Historial del bot</td>
                        <td>Conversaciones en tiempo real con clientes</td>
                    </tr>
                    <tr>
                        <td><strong>disponibilidad</strong></td>
                        <td>Disponibilidad diaria</td>
                        <td>Guías y buses disponibles por fecha</td>
                    </tr>
                    <tr>
                        <td><strong>comentarios</strong></td>
                        <td>Calificaciones y reseñas</td>
                        <td>Feedback de clientes sobre tours</td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin-top: 30px; margin-bottom: 15px;">🔧 Métodos de Instalación</h2>
            
            <h3 style="margin: 20px 0 10px 0; color: #333;">Opción 1: Automática (Recomendado)</h3>
            <div class="success-box">
                Haz clic en el botón "✅ Crear Base de Datos" arriba para crear automáticamente todas las tablas.
            </div>

            <h3 style="margin: 20px 0 10px 0; color: #333;">Opción 2: PhpMyAdmin</h3>
            <ol style="margin-left: 20px; line-height: 1.8;">
                <li>Abre phpMyAdmin en <code style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px;">http://localhost/phpmyadmin</code></li>
                <li>Descarga el archivo SQL haciendo clic en "📄 Descargar SQL"</li>
                <li>Ve a "Importar" en phpMyAdmin</li>
                <li>Selecciona el archivo descargado y ejecuta</li>
            </ol>

            <h3 style="margin: 20px 0 10px 0; color: #333;">Opción 3: Línea de Comandos MySQL</h3>
            <div class="command-box">
                mysql -u root -p < setup/database.sql
            </div>

            <h2 style="margin-top: 30px; margin-bottom: 15px;">🤖 API del Bot</h2>
            <p style="margin: 10px 0; color: #666;">El bot responde a través de la API: <code style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px;">routes/bot_api.php</code></p>
            
            <h3 style="margin: 20px 0 10px 0; color: #333;">Endpoints Disponibles:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Acción</th>
                        <th>Método</th>
                        <th>Parámetros</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="code">mensaje</span></td>
                        <td>POST</td>
                        <td>cliente_id, mensaje</td>
                        <td>Procesa mensaje y responde</td>
                    </tr>
                    <tr>
                        <td><span class="code">conversaciones</span></td>
                        <td>GET</td>
                        <td>cliente_id</td>
                        <td>Obtiene historial de conversaciones</td>
                    </tr>
                    <tr>
                        <td><span class="code">resolver</span></td>
                        <td>POST</td>
                        <td>conversacion_id</td>
                        <td>Marca conversación como resuelta</td>
                    </tr>
                    <tr>
                        <td><span class="code">estadisticas</span></td>
                        <td>GET</td>
                        <td>—</td>
                        <td>Obtiene estadísticas del bot</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="margin: 20px 0 10px 0; color: #333;">Ejemplo de uso (JavaScript):</h3>
            <div class="command-box" style="font-size: 12px;">
fetch('routes/bot_api.php?action=mensaje', {<br>
&nbsp;&nbsp;method: 'POST',<br>
&nbsp;&nbsp;body: new URLSearchParams({<br>
&nbsp;&nbsp;&nbsp;&nbsp;cliente_id: 1,<br>
&nbsp;&nbsp;&nbsp;&nbsp;mensaje: 'Quiero reservar un tour a Cartagena'<br>
&nbsp;&nbsp;})<br>
})<br>
.then(r => r.json())<br>
.then(data => console.log(data.data.respuesta));
            </div>

            <div class="info-box" style="margin-top: 30px;">
                <strong>ℹ️ Datos de Prueba</strong><br>
                Se incluyen 3 clientes, 3 asesores, 3 guías, 3 buses y 3 tours de ejemplo para testing.
            </div>

            <?php
        }
        ?>
    </div>
</body>
</html>
