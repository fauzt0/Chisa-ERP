-- Agregar campo fecha_completado a ordenes_produccion

ALTER TABLE ordenes_produccion 
ADD COLUMN IF NOT EXISTS fecha_completado DATETIME NULL 
COMMENT 'Fecha cuando se completa la producción';

-- Verificar que se agregó correctamente
SELECT 'Campo fecha_completado agregado correctamente' AS Resultado;

DESCRIBE ordenes_produccion;
