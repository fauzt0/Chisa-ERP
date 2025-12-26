-- Verificar y agregar campos faltantes a la tabla productos

-- Agregar campo alias si no existe
SET @dbname = DATABASE();
SET @tablename = "productos";
SET @columnname = "alias";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(200) NULL COMMENT 'Nombre alternativo del producto' AFTER nombre")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Crear índice para alias si no existe
CREATE INDEX IF NOT EXISTS idx_productos_alias ON productos(alias);

-- Mostrar estructura de la tabla para verificar
DESCRIBE productos;
