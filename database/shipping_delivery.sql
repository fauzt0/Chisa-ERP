-- Script para agregar funcionalidad de envíos y entregas
-- Agrega campos necesarios para gestionar órdenes con envío a domicilio

-- Verificar si las columnas ya existen antes de agregarlas
SET @dbname = DATABASE();
SET @tablename = 'ordenes_venta';

-- 1. Agregar fecha_entrega_estimada
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = 'fecha_entrega_estimada') > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN fecha_entrega_estimada DATE NULL AFTER fecha_orden'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Agregar direccion_envio
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = 'direccion_envio') > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN direccion_envio TEXT NULL AFTER observaciones'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Agregar requiere_envio
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = 'requiere_envio') > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN requiere_envio BOOLEAN DEFAULT FALSE AFTER tipo_venta'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. Agregar condiciones_pago
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = 'condiciones_pago') > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN condiciones_pago VARCHAR(255) NULL AFTER forma_pago COMMENT "Ej: 50% anticipo, 50% contra entrega"'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Actualizar ENUM de tipo_venta
-- Nota: ALTER MODIFY siempre se ejecuta, no se puede hacer condicional fácilmente
-- Si ya tiene los valores, no causará error
ALTER TABLE ordenes_venta 
MODIFY COLUMN tipo_venta ENUM('Mostrador', 'Email', 'Teléfono', 'Otro') DEFAULT 'Mostrador';

-- 6. Actualizar ENUM de estatus
-- Agregar nuevos estatus para el flujo de envíos
ALTER TABLE ordenes_venta 
MODIFY COLUMN estatus ENUM('Cotización', 'Confirmada', 'En Preparación', 'Enviada', 'Entregada', 'Cancelada') DEFAULT 'Cotización';

-- 7. Actualizar órdenes existentes
-- Establecer requiere_envio = FALSE para órdenes de Mostrador
UPDATE ordenes_venta 
SET requiere_envio = FALSE 
WHERE tipo_venta = 'Mostrador' AND requiere_envio IS NULL;

-- Establecer requiere_envio = TRUE para otros tipos de venta
UPDATE ordenes_venta 
SET requiere_envio = TRUE 
WHERE tipo_venta != 'Mostrador' AND requiere_envio IS NULL;

-- Mensaje de confirmación
SELECT 'Script ejecutado correctamente. Columnas agregadas:' as Mensaje;
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'ordenes_venta'
  AND COLUMN_NAME IN ('fecha_entrega_estimada', 'direccion_envio', 'requiere_envio', 'condiciones_pago')
ORDER BY ORDINAL_POSITION;
