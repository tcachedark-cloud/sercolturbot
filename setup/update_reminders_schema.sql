-- ═══════════════════════════════════════════════════════════════
-- ACTUALIZACIÓN SCHEMA PARA SISTEMA DE RECORDATORIOS
-- Ejecutar en MySQL para agregar campos a tabla citas
-- ═══════════════════════════════════════════════════════════════

-- Agregar columna recordatorio_enviado si no existe
ALTER TABLE citas 
ADD COLUMN IF NOT EXISTS recordatorio_enviado TINYINT DEFAULT 0 COMMENT 'Si se envió recordatorio';

-- Agregar columna fecha_recordatorio si no existe
ALTER TABLE citas 
ADD COLUMN IF NOT EXISTS fecha_recordatorio TIMESTAMP NULL COMMENT 'Cuando se envió el recordatorio';

-- Crear tabla de log de emails si no existe
CREATE TABLE IF NOT EXISTS email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(255) NOT NULL,
    asunto VARCHAR(255),
    estado ENUM('enviado', 'fallido') DEFAULT 'fallido',
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_destinatario (destinatario),
    INDEX idx_fecha (fecha_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de auditoría de recordatorios
CREATE TABLE IF NOT EXISTS reminder_audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    tipo_envio VARCHAR(50) COMMENT 'whatsapp, email, sms',
    estado VARCHAR(50) COMMENT 'enviado, fallido, pendiente',
    respuesta_api LONGTEXT,
    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
    INDEX idx_cita (cita_id),
    INDEX idx_fecha (fecha_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice para búsqueda eficiente de recordatorios pendientes
CREATE INDEX IF NOT EXISTS idx_recordatorios_pendientes 
ON citas (estado, recordatorio_enviado, fecha_hora) 
WHERE estado = 'confirmada' AND recordatorio_enviado = 0;
