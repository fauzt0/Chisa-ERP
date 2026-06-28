-- Extensión opcional para contratos RH (plantilla_id)
-- Ejecutar una sola vez si la columna no existe.

SET @db := DATABASE();

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE contratos_empleados ADD COLUMN plantilla_id INT UNSIGNED NULL DEFAULT NULL AFTER empleado_id',
        'SELECT ''plantilla_id ya existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'contratos_empleados'
      AND COLUMN_NAME = 'plantilla_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
