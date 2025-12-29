-- ================================================
-- TABLAS PARA WHATSAPP BOT
-- ================================================

USE sercolturbot;

-- Tabla de conversaciones de WhatsApp
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mensajes de WhatsApp
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
