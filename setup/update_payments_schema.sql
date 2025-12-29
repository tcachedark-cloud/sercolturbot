-- ═════════════════════════════════════════════════════════════
-- SCHEMA DE PAGOS - WOMPI INTEGRATION
-- ═════════════════════════════════════════════════════════════

-- Crear tabla de pagos si no existe
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referencia` VARCHAR(255) UNIQUE NOT NULL,
  `monto` DECIMAL(10, 2) NOT NULL,
  `moneda` VARCHAR(3) DEFAULT 'COP',
  `email` VARCHAR(255),
  `estado` VARCHAR(50) DEFAULT 'iniciado',
  `id_transaccion` VARCHAR(255),
  `respuesta_api` LONGTEXT,
  `reserva_id` INT,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_referencia` (`referencia`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_reserva` (`reserva_id`),
  CONSTRAINT `fk_pagos_reserva` FOREIGN KEY (`reserva_id`) REFERENCES `reservas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar columna a citas si no existe
ALTER TABLE `citas` 
  ADD COLUMN IF NOT EXISTS `google_event_id` VARCHAR(255) AFTER `fecha_cita`,
  ADD COLUMN IF NOT EXISTS `pagado` TINYINT(1) DEFAULT 0,
  ADD INDEX IF NOT EXISTS `idx_google_event` (`google_event_id`);

-- Crear tabla de auditoría de pagos
CREATE TABLE IF NOT EXISTS `pagos_auditorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pago_id` INT NOT NULL,
  `accion` VARCHAR(100),
  `estado_anterior` VARCHAR(50),
  `estado_nuevo` VARCHAR(50),
  `metadata` LONGTEXT,
  `ip_usuario` VARCHAR(45),
  `usuario_id` INT,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pago_id`) REFERENCES `pagos`(`id`) ON DELETE CASCADE,
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_accion` (`accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de logs de Wompi
CREATE TABLE IF NOT EXISTS `wompi_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `evento` VARCHAR(100),
  `referencia` VARCHAR(255),
  `request` LONGTEXT,
  `response` LONGTEXT,
  `http_code` INT,
  `error` TEXT,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_evento` (`evento`),
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_referencia` (`referencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
