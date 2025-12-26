-- Agregar campo direccion_envio a ordenes_venta
-- Solo agrega la columna si no existe

ALTER TABLE ordenes_venta 
ADD COLUMN IF NOT EXISTS direccion_envio TEXT NULL 
COMMENT 'Dirección de entrega para pedidos (no mostrador)';

-- Verificar que se agregó correctamente
SELECT 'Columna direccion_envio agregada correctamente' AS Resultado;

DESCRIBE ordenes_venta;
