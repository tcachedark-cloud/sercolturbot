-- ================================================
-- SCRIPT COMPLETO BASE DE DATOS SERCOLTURBOT
-- ================================================

-- Crear Base de Datos
CREATE DATABASE IF NOT EXISTS sercolturbot;
USE sercolturbot;

-- ================================================
-- TABLA: CLIENTES
-- ================================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    documento VARCHAR(20) UNIQUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: ASESORES
-- ================================================
CREATE TABLE IF NOT EXISTS asesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    especialidad VARCHAR(100),
    disponible BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: GUÍAS
-- ================================================
CREATE TABLE IF NOT EXISTS guias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    idiomas VARCHAR(200),
    experiencia INT,
    disponible BOOLEAN DEFAULT TRUE,
    calificacion DECIMAL(3,2),
    fecha_contratacion DATE,
    telefono VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: BUSES
-- ================================================
CREATE TABLE IF NOT EXISTS buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_busero VARCHAR(100) NOT NULL,
    placa VARCHAR(20) UNIQUE NOT NULL,
    capacidad INT NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    estado VARCHAR(30),
    telefono VARCHAR(20),
    marca VARCHAR(50),
    modelo VARCHAR(50),
    año INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: TOURS/PAQUETES
-- ================================================
CREATE TABLE IF NOT EXISTS tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion_dias INT,
    destino VARCHAR(100),
    capacidad_maxima INT,
    activo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: RESERVAS
-- ================================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tour_id INT,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    cantidad_personas INT NOT NULL,
    precio_total DECIMAL(10,2),
    estado VARCHAR(30) DEFAULT 'pendiente',
    notas TEXT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: ASIGNACIONES (Reserva-Guía-Bus-Asesor)
-- ================================================
CREATE TABLE IF NOT EXISTS asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    guia_id INT,
    bus_id INT,
    asesor_id INT,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (guia_id) REFERENCES guias(id),
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    FOREIGN KEY (asesor_id) REFERENCES asesores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: BOT_CONVERSACIONES (Respuestas en Tiempo Real)
-- ================================================
CREATE TABLE IF NOT EXISTS bot_conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    asesor_id INT,
    mensaje_cliente TEXT NOT NULL,
    respuesta_bot TEXT,
    tipo_consulta VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resuelta BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: DISPONIBILIDAD
-- ================================================
CREATE TABLE IF NOT EXISTS disponibilidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(30) NOT NULL,
    referencia_id INT NOT NULL,
    fecha DATE NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_disponibilidad (tipo, referencia_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: COMENTARIOS/CALIFICACIONES
-- ================================================
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    reserva_id INT,
    calificacion INT,
    comentario TEXT,
    fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (reserva_id) REFERENCES reservas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: CONVERSACIONES_WHATSAPP
-- ================================================
CREATE TABLE IF NOT EXISTS whatsapp_conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    user_name VARCHAR(100),
    email VARCHAR(100),
    state VARCHAR(50) DEFAULT 'greeting',
    selected_tour_id INT,
    quantity_people INT,
    reservation_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (selected_tour_id) REFERENCES tours(id),
    FOREIGN KEY (reservation_id) REFERENCES reservas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLA: MENSAJES_WHATSAPP
-- ================================================
CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    direction ENUM('incoming', 'outgoing') NOT NULL,
    message_type VARCHAR(50),
    content LONGTEXT,
    whatsapp_message_id VARCHAR(100) UNIQUE,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- ================================================
CREATE INDEX idx_cliente_email ON clientes(email);
CREATE INDEX idx_cliente_documento ON clientes(documento);
CREATE INDEX idx_asesor_email ON asesores(email);
CREATE INDEX idx_bus_placa ON buses(placa);
CREATE INDEX idx_reserva_cliente ON reservas(cliente_id);
CREATE INDEX idx_reserva_estado ON reservas(estado);
CREATE INDEX idx_bot_cliente ON bot_conversaciones(cliente_id);
CREATE INDEX idx_bot_timestamp ON bot_conversaciones(timestamp);
CREATE INDEX idx_phone_number ON whatsapp_conversations(phone_number);
CREATE INDEX idx_conversation_id ON whatsapp_messages(conversation_id);
CREATE INDEX idx_status ON whatsapp_messages(status);

-- ================================================
-- DATOS DE PRUEBA
-- ================================================

-- Insertar Clientes
INSERT INTO clientes (nombre, email, telefono, documento) VALUES
('Juan Pérez', 'juan@email.com', '3001234567', '1234567890'),
('María García', 'maria@email.com', '3009876543', '9876543210'),
('Carlos López', 'carlos@email.com', '3005555555', '5555555555');

-- Insertar Asesores
INSERT INTO asesores (nombre, email, telefono, especialidad) VALUES
('Roberto Silva', 'roberto@sercolturbot.com', '3001111111', 'Tours Nacionales'),
('Ana Martínez', 'ana@sercolturbot.com', '3002222222', 'Tours Internacionales'),
('Pedro Gómez', 'pedro@sercolturbot.com', '3003333333', 'Grupos y Eventos');

-- Insertar Guías
INSERT INTO guias (nombre, idiomas, experiencia, disponible, calificacion) VALUES
('Santiago Ruiz', 'Español, Inglés, Francés', 8, TRUE, 4.8),
('Laura Díaz', 'Español, Inglés', 5, TRUE, 4.9),
('Miguel Ángel', 'Español, Portugués', 10, TRUE, 4.7);

-- Insertar Buses
INSERT INTO buses (nombre_busero, placa, capacidad, disponible, estado, marca, modelo, año) VALUES
('Transportes Colombia', 'ABC123', 45, TRUE, 'Operativo', 'Mercedes Benz', 'O500', 2020),
('Viajes Seguros', 'XYZ789', 50, TRUE, 'Operativo', 'Volvo', 'B8R', 2021),
('Rutas del País', 'DEF456', 35, TRUE, 'Mantenimiento', 'Scania', 'K400', 2019);

-- Insertar Tours
INSERT INTO tours (nombre, descripcion, precio, duracion_dias, destino, capacidad_maxima) VALUES
('Cartagena Clásica', 'Tour de 3 días por Cartagena con playas y turismo cultural', 450.00, 3, 'Cartagena', 50),
('Santa Marta y Tayrona', 'Aventura en la Sierra Nevada y Parque Tayrona', 650.00, 4, 'Santa Marta', 40),
('Bogotá Imperial', 'Recorrido histórico por la capital colombiana', 350.00, 2, 'Bogotá', 45);
