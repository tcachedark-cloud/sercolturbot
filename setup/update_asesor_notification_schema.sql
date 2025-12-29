-- ============================================================================
-- ACTUALIZACIÓN DE SCHEMA: Sistema de Notificaciones de Asesor
-- ============================================================================
-- Descripción: Agregar campos para registrar notificaciones al asesor
-- Fecha: 2025-12-29
-- ============================================================================

-- 1️⃣ COLUMNA: Relación con el asesor
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS asesor_id INT NULL COMMENT 'ID del asesor que confirmó la reserva';

-- 2️⃣ COLUMNA: Flag de notificación enviada
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS asesor_notificado_confirmacion TINYINT DEFAULT 0 COMMENT 'Flag: asesor fue notificado de la confirmación (0=no, 1=sí)';

-- 3️⃣ COLUMNA: Timestamp de notificación
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS fecha_notificacion_confirmacion DATETIME NULL COMMENT 'Fecha/hora cuando se notificó al asesor';

-- 4️⃣ ÍNDICE: Optimización para consultas de notificación
ALTER TABLE reservas 
ADD INDEX IF NOT EXISTS idx_asesor_notificacion (asesor_notificado_confirmacion, estado);

-- 5️⃣ ÍNDICE: Búsqueda por asesor
ALTER TABLE reservas 
ADD INDEX IF NOT EXISTS idx_asesor_id (asesor_id);

-- ============================================================================
-- QUERIES DE VERIFICACIÓN
-- ============================================================================

-- Ver estado de notificaciones:
-- SELECT id, numero_referencia, cliente_id, asesor_id, estado, 
--        asesor_notificado_confirmacion, fecha_notificacion_confirmacion 
-- FROM reservas 
-- WHERE asesor_notificado_confirmacion = 0;

-- Ver todas las notificaciones enviadas:
-- SELECT id, numero_referencia, asesor_id, fecha_notificacion_confirmacion 
-- FROM reservas 
-- WHERE asesor_notificado_confirmacion = 1 
-- ORDER BY fecha_notificacion_confirmacion DESC;
