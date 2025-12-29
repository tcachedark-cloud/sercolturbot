-- Actualizar tabla de reservas para registrar notificaciones al asesor
-- Ejecutar este script en la base de datos

ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS asesor_notificado_confirmacion TINYINT DEFAULT 0 COMMENT 'Flag: asesor fue notificado de la confirmación';

ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS fecha_notificacion_confirmacion DATETIME NULL COMMENT 'Fecha/hora cuando se notificó al asesor';

-- Si la tabla reservas no tiene el campo asesor_id, agregarlo:
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS asesor_id INT NULL COMMENT 'ID del asesor que confirmó la reserva';

-- Crear índice para consultas rápidas
ALTER TABLE reservas ADD INDEX IF NOT EXISTS idx_asesor_notificacion (asesor_notificado_confirmacion, estado);

-- Para ver el estado de las notificaciones:
-- SELECT id, numero_referencia, cliente_id, estado, asesor_notificado_confirmacion, fecha_notificacion_confirmacion FROM reservas;
