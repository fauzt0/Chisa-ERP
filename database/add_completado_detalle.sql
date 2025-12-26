-- No es necesario este script
-- La tabla ordenes_produccion ya tiene la estructura necesaria
-- Cada orden de producción es para un solo producto

SELECT 'La tabla ordenes_produccion ya existe con la estructura correcta' AS Mensaje;

-- Agregar campo completado directamente a ordenes_produccion si no existe
ALTER TABLE ordenes_produccion 
ADD COLUMN IF NOT EXISTS orden_venta_id INT NULL 
COMMENT 'Orden de venta que generó esta producción';

-- Verificar estructura
DESCRIBE ordenes_produccion;
