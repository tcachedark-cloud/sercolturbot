<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SERCOLTURBOT - Centro de Control</title>
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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        header h1 {
            font-size: 42px;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 4px solid #667eea;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .card.setup {
            border-top-color: #28a745;
        }
        
        .card.tools {
            border-top-color: #ffc107;
        }
        
        .card.api {
            border-top-color: #17a2b8;
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        .card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .btn.success {
            background: #28a745;
        }
        
        .btn.success:hover {
            background: #218838;
        }
        
        .btn.warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn.warning:hover {
            background: #e0a800;
        }
        
        .btn.info {
            background: #17a2b8;
        }
        
        .btn.info:hover {
            background: #138496;
        }
        
        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
            color: #333;
        }
        
        .info-box strong {
            color: #667eea;
        }
        
        .status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .status.online {
            color: #28a745;
        }
        
        .status.offline {
            color: #dc3545;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .dot.online {
            background: #28a745;
            animation: pulse 2s infinite;
        }
        
        .dot.offline {
            background: #dc3545;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .section-title {
            font-size: 28px;
            color: #333;
            margin-top: 40px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .section-title:first-child {
            margin-top: 0;
        }
        
        footer {
            background: #f9f9f9;
            padding: 20px 40px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
        }
        
        .feature-list {
            list-style: none;
            margin: 15px 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .feature-list li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            header h1 {
                font-size: 28px;
            }
            
            .content {
                padding: 20px;
            }
            
            .grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 SERCOLTURBOT</h1>
            <p>Centro de Control - Sistema Integral de Turismo</p>
        </header>
        
        <div class="content">
            <!-- INICIO RÁPIDO -->
            <div class="section-title">🚀 Inicio Rápido</div>
            <div class="grid">
                <div class="card setup">
                    <h3>⚙️ Setup Base de Datos</h3>
                    <p>Instala y configura la base de datos MySQL con todas las tablas necesarias.</p>
                    <a href="setup/test.php" class="btn success">Ver Estado del Sistema</a>
                </div>
                
                <div class="card setup">
                    <h3>📝 Insertar Datos Ejemplo</h3>
                    <p>Carga datos de prueba (tours, guías, buses, clientes) para empezar a usar el sistema.</p>
                    <a href="../setup/insert_sample_data.php" class="btn success">Insertar Datos</a>
                </div>
                
                <div class="card setup">
                    <h3>📚 Documentación</h3>
                    <p>Guías completas de instalación, configuración e integración con Meta WhatsApp.</p>
                    <a href="../setup/WHATSAPP_SETUP.md" class="btn info">Ver Guía WhatsApp</a>
                </div>
            </div>
            
            <!-- HERRAMIENTAS -->
            <div class="section-title">🛠️ Herramientas</div>
            <div class="grid">
                <div class="card tools">
                    <h3>💬 Chat Web</h3>
                    <p>Interfaz de chat en tiempo real con bot automático para pruebas y soporte.</p>
                    <a href="chat_demo.php" class="btn warning">Abrir Chat</a>
                    <div class="status online">
                        <span class="dot online"></span>
                        Sistema activo
                    </div>
                </div>
                
                <div class="card tools">
                    <h3>📊 Dashboard Administrativo</h3>
                    <p>Panel de control completo con estadísticas, reservas, guías, buses y conversaciones WhatsApp.</p>
                    <a href="dashboard.php" class="btn warning">Abrir Dashboard</a>
                    <div class="status online">
                        <span class="dot online"></span>
                        Actualización en tiempo real
                    </div>
                </div>
                
                <div class="card tools">
                    <h3>🔍 Verificar Sistema</h3>
                    <p>Ejecuta un test completo de configuración, bases de datos y archivos necesarios.</p>
                    <a href="../setup/test.php" class="btn warning">Ejecutar Test</a>
                </div>
            </div>
            
            <!-- APIs -->
            <div class="section-title">🔌 APIs</div>
            <div class="grid">
                <div class="card api">
                    <h3>🤖 Bot API (Web)</h3>
                    <p>API REST para el chatbot web. Procesa mensajes y retorna respuestas automáticas.</p>
                    <p><strong>Endpoint:</strong> POST /routes/bot_api.php</p>
                    <ul class="feature-list">
                        <li>Procesamiento de mensajes</li>
                        <li>Identificación de consultas</li>
                        <li>Historial de conversaciones</li>
                        <li>Estadísticas de uso</li>
                    </ul>
                </div>
                
                <div class="card api">
                    <h3>💬 WhatsApp API</h3>
                    <p>Integración completa con Meta WhatsApp Business API para venta automática.</p>
                    <p><strong>Endpoint:</strong> POST /routes/whatsapp_webhook.php</p>
                    <ul class="feature-list">
                        <li>Recepción de mensajes</li>
                        <li>Conversación con estados</li>
                        <li>Generación de reservas</li>
                        <li>Asignación de recursos</li>
                    </ul>
                </div>
                
                <div class="card api">
                    <h3>📈 Dashboard API</h3>
                    <p>API para obtener datos en tiempo real del dashboard y actualizar reservas.</p>
                    <p><strong>Endpoint:</strong> GET/POST /public/dashboard-api.php</p>
                    <ul class="feature-list">
                        <li>Estadísticas en vivo</li>
                        <li>Listado de recursos</li>
                        <li>Actualización de datos</li>
                        <li>Reportes</li>
                    </ul>
                </div>
            </div>
            
            <!-- INFORMACIÓN DEL SISTEMA -->
            <div class="info-box">
                <strong>📌 Información del Sistema:</strong><br>
                ✓ PHP: 7.4+<br>
                ✓ MySQL: InnoDB, UTF8MB4<br>
                ✓ Base de datos: <strong>sercolturbot</strong><br>
                ✓ Usuario: <strong>root</strong><br>
                ✓ Contraseña: <strong>C121672@c</strong><br>
                <br>
                <strong>⚠️ Para Producción:</strong> Cambiar credenciales de base de datos y configurar variables de entorno.
            </div>
            
            <!-- CARACTERÍSTICAS -->
            <div class="section-title">✨ Características Principales</div>
            <div class="grid">
                <div class="card">
                    <h3>🗄️ Base de Datos Completa</h3>
                    <ul class="feature-list">
                        <li>12 tablas bien estructuradas</li>
                        <li>Relaciones con claves foráneas</li>
                        <li>Índices optimizados</li>
                        <li>Integridad referencial</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3>🤖 Bots Automáticos</h3>
                    <ul class="feature-list">
                        <li>Bot web con chat interactivo</li>
                        <li>Bot WhatsApp completo</li>
                        <li>Máquina de estados avanzada</li>
                        <li>Respuestas inteligentes</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3>🚀 Automatización Completa</h3>
                    <ul class="feature-list">
                        <li>Creación automática de reservas</li>
                        <li>Asignación de guías y buses</li>
                        <li>Confirmaciones automáticas</li>
                        <li>Logs y seguimiento</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3>📊 Dashboard Avanzado</h3>
                    <ul class="feature-list">
                        <li>Estadísticas en tiempo real</li>
                        <li>5 módulos de información</li>
                        <li>API REST integrada</li>
                        <li>Interfaz responsive</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3>🔐 Seguridad</h3>
                    <ul class="feature-list">
                        <li>Consultas preparadas (PDO)</li>
                        <li>Validación de webhooks</li>
                        <li>Manejo de excepciones</li>
                        <li>Logs de actividad</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3>📱 Multi-Canal</h3>
                    <ul class="feature-list">
                        <li>Chat web integrado</li>
                        <li>WhatsApp Business</li>
                        <li>API REST pública</li>
                        <li>Panel administrativo</li>
                    </ul>
                </div>
            </div>
            
            <!-- PRÓXIMOS PASOS -->
            <div class="section-title">📋 Próximos Pasos</div>
            <div class="info-box">
                <strong>1. Verificar Sistema:</strong> Haz clic en "Ver Estado del Sistema" para comprobar que todo está configurado correctamente.<br><br>
                
                <strong>2. Insertar Datos de Prueba:</strong> Carga datos de ejemplo (tours, guías, buses) haciendo clic en "Insertar Datos".<br><br>
                
                <strong>3. Probar Chat Web:</strong> Accede al chat web para ver cómo funciona el bot automático.<br><br>
                
                <strong>4. Explorar Dashboard:</strong> Visita el dashboard para ver todas las reservas, asignaciones y recursos.<br><br>
                
                <strong>5. Configurar WhatsApp (Opcional):</strong> Si deseas usar el bot en WhatsApp, sigue la guía en setup/WHATSAPP_SETUP.md.<br><br>
                
                <strong>6. Pasar a Producción:</strong> Cambia las credenciales de base de datos y despliega en un servidor público con HTTPS.
            </div>
        </div>
        
        <footer>
            <p>SERCOLTURBOT © 2024 | Sistema Integral de Gestión de Turismo</p>
            <p style="font-size: 12px; margin-top: 10px;">Para documentación completa, consulta README.md e IMPLEMENTACION_COMPLETA.md</p>
        </footer>
    </div>
</body>
</html>