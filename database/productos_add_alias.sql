-- Agregar campo alias a la tabla productos
ALTER TABLE productos 
ADD COLUMN alias VARCHAR(200) NULL COMMENT 'Nombre alternativo del producto' 
AFTER nombre;

-- Crear índice para búsquedas por alias
CREATE INDEX idx_productos_alias ON productos(alias);
