<?php
/**
 * EJEMPLO DE USO DEL BOT SERCOLTURBOT
 * Este archivo demuestra cómo usar el BotService
 */

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../services/BotService.php');

$botService = new BotService($pdo);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo Bot SERCOLTURBOT</title>
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
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .chat-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            height: 600px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            display: flex;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message.user {
            justify-content: flex-end;
        }
        .message-content {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 10px;
            word-wrap: break-word;
        }
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 0;
        }
        .message.bot .message-content {
            background: #f0f0f0;
            color: #333;
            border-bottom-left-radius: 0;
        }
        .input-area {
            padding: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
        }
        input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .emoji {
            margin-right: 5px;
        }
        .info-panel {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 20px;
            margin-top: 20px;
        }
        .info-panel h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .example-button {
            display: inline-block;
            padding: 8px 12px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px 5px 5px 0;
            transition: all 0.3s;
            font-size: 13px;
        }
        .example-button:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-container">
            <div class="header">
                <span class="emoji">🤖</span> SERCOLTURBOT - Asistente Virtual
            </div>
            <div class="messages" id="messages">
                <div class="message bot">
                    <div class="message-content">
                        👋 ¡Hola! Soy tu asistente virtual SERCOLTURBOT. ¿En qué puedo ayudarte hoy?
                    </div>
                </div>
            </div>
            <div class="input-area">
                <input type="text" id="messageInput" placeholder="Escribe tu mensaje..." autocomplete="off">
                <button onclick="sendMessage()">📤 Enviar</button>
            </div>
        </div>

        <div class="info-panel">
            <h2>💡 Prueba estos ejemplos:</h2>
            <div>
                <button class="example-button" onclick="setMessage('Quiero hacer una reserva')">
                    🎫 Reservas
                </button>
                <button class="example-button" onclick="setMessage('¿Hablan inglés los guías?')">
                    👨‍🏫 Guías
                </button>
                <button class="example-button" onclick="setMessage('¿Cómo es el bus?')">
                    🚌 Buses
                </button>
                <button class="example-button" onclick="setMessage('¿Cuáles son los tours?')">
                    🎯 Tours
                </button>
                <button class="example-button" onclick="setMessage('Necesito un asesor')">
                    👨‍💼 Asesor
                </button>
                <button class="example-button" onclick="setMessage('¿Hay disponibilidad en febrero?')">
                    📅 Disponibilidad
                </button>
            </div>

            <h2 style="margin-top: 25px;">📊 Estadísticas del Bot:</h2>
            <div class="stats">
                <?php
                try {
                    $estadisticas = $botService->obtenerEstadisticas();
                    
                    $total_conversaciones = array_sum(array_column($estadisticas, 'total_conversaciones'));
                    $clientes_unicos = count(array_unique(array_column($estadisticas, 'clientes_unicos')));
                    $resueltas = array_sum(array_column($estadisticas, 'resueltas'));
                    
                    echo "
                    <div class='stat-box'>
                        <div class='stat-number'>$total_conversaciones</div>
                        <div class='stat-label'>Conversaciones</div>
                    </div>
                    <div class='stat-box'>
                        <div class='stat-number'>" . $clientes_unicos . "</div>
                        <div class='stat-label'>Clientes Únicos</div>
                    </div>
                    <div class='stat-box'>
                        <div class='stat-number'>$resueltas</div>
                        <div class='stat-label'>Resueltas</div>
                    </div>
                    ";
                } catch (Exception $e) {
                    echo "<p style='color: #666;'>Estadísticas no disponibles aún</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        const messagesDiv = document.getElementById('messages');
        const messageInput = document.getElementById('messageInput');
        const clienteId = 1; // Cliente de prueba

        function setMessage(msg) {
            messageInput.value = msg;
            messageInput.focus();
        }

        function addMessage(content, isUser = true) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
            messageDiv.innerHTML = `<div class="message-content">${escapeHtml(content)}</div>`;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        async function sendMessage() {
            const mensaje = messageInput.value.trim();
            
            if (!mensaje) return;

            // Mostrar mensaje del usuario
            addMessage(mensaje, true);
            messageInput.value = '';

            try {
                // Enviar al bot
                const response = await fetch('../routes/bot_api.php?action=mensaje', {
                    method: 'POST',
                    body: new URLSearchParams({
                        cliente_id: clienteId,
                        mensaje: mensaje
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Mostrar respuesta del bot
                    setTimeout(() => {
                        addMessage(data.data.respuesta, false);
                    }, 500);
                } else {
                    addMessage('❌ Error: ' + (data.error || 'Error desconocido'), false);
                }
            } catch (error) {
                addMessage('❌ Error de conexión: ' + error.message, false);
            }
        }

        // Enviar con Enter
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Enfoque inicial
        messageInput.focus();
    </script>
</body>
</html>
