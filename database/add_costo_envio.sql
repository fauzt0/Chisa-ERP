-- Agregar campo costo_envio a ordenes_venta
-- El costo de envío se suma al total de la orden

ALTER TABLE ordenes_venta 
ADD COLUMN IF NOT EXISTS costo_envio DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Costo adicional por envío a domicilio';

-- Verificar que se agregó correctamente
SELECT 'Columna costo_envio agregada correctamente' AS Resultado;

DESCRIBE ordenes_venta;
