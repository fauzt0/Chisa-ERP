-- Vinculación empleado ↔ usuario ERP (administradores.empleado_id)
-- Idempotente: solo agrega la columna si no existe.

SET @db := DATABASE();

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE administradores ADD COLUMN empleado_id INT(11) NULL DEFAULT NULL COMMENT ''FK empleados.id'' AFTER departamento, ADD KEY idx_admin_empleado (empleado_id)',
        'SELECT ''empleado_id ya existe en administradores'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'administradores'
      AND COLUMN_NAME = 'empleado_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
