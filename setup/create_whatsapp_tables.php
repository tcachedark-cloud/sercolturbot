<?php
/**
 * CREAR TABLAS DE WHATSAPP
 * Ejecuta este script una sola vez para crear las tablas necesarias
 * Acceso: http://localhost/SERCOLTURBOT/setup/create_whatsapp_tables.php
 */

require_once(__DIR__ . '/../config/database.php');

try {
    echo "<h2>📱 Creando tablas de WhatsApp...</h2>";

    // Crear tabla de conversaciones
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS whatsapp_conversations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(20) NOT NULL,
            user_name VARCHAR(100),
            state VARCHAR(50) DEFAULT 'initial',
            selected_tour_id INT,
            selected_date DATE,
            num_people INT,
            full_name VARCHAR(100),
            email VARCHAR(100),
            reservation_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_phone (phone_number),
            INDEX idx_state (state),
            FOREIGN KEY (selected_tour_id) REFERENCES tours(id),
            FOREIGN KEY (reservation_id) REFERENCES reservas(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabla whatsapp_conversations creada<br>";

    // Crear tabla de mensajes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS whatsapp_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conversation_id INT NOT NULL,
            phone_number VARCHAR(20),
            message_type VARCHAR(50),
            message_content TEXT,
            is_incoming BOOLEAN,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id),
            INDEX idx_conversation (conversation_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabla whatsapp_messages creada<br>";

    // Crear tabla de comentarios si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reserva_id INT NOT NULL,
            usuario VARCHAR(100),
            comentario TEXT,
            fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reserva_id) REFERENCES reservas(id),
            INDEX idx_reserva (reserva_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabla comentarios creada<br>";

    // Crear tabla de disponibilidad si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS disponibilidad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            guia_id INT,
            bus_id INT,
            fecha_disponibilidad DATE,
            disponible BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (guia_id) REFERENCES guias(id),
            FOREIGN KEY (bus_id) REFERENCES buses(id),
            INDEX idx_fecha (fecha_disponibilidad)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabla disponibilidad creada<br>";

    // Crear tabla de conversaciones bot si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bot_conversaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            asesor_id INT,
            mensaje TEXT,
            respuesta TEXT,
            tipo_consulta VARCHAR(50),
            fecha_mensaje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id),
            FOREIGN KEY (asesor_id) REFERENCES asesores(id),
            INDEX idx_cliente (cliente_id),
            INDEX idx_fecha (fecha_mensaje)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabla bot_conversaciones creada<br>";

    echo "<hr>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ Todas las tablas de WhatsApp se crearon exitosamente</h3>";
    echo "<p>Ahora puedes acceder al dashboard en: <a href='../public/dashboard.php'>http://localhost/SERCOLTURBOT/public/dashboard.php</a></p>";
    echo "</div>";

} catch(PDOException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error al crear tablas:</h3>";
    echo "<pre>{$e->getMessage()}</pre>";
    echo "</div>";
}
?>
