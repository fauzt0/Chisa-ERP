-- ========================================================================
-- Sincronización forzada RRHH → Reloj ZKTeco
-- Campos en empleados para PIN asignado por el ERP y nombre meta en el reloj
-- ========================================================================

-- Ejecutar una sola vez. Si la columna ya existe, omitir la línea correspondiente.
ALTER TABLE `empleados`
  ADD COLUMN `reloj_pin` INT(11) UNSIGNED DEFAULT NULL
    COMMENT 'PIN físico en reloj (secuencial desde 2; PIN 1=admin reloj)' AFTER `numero_empleado`,
  ADD COLUMN `reloj_nombre_meta` VARCHAR(24) DEFAULT NULL
    COMMENT 'Name en reloj: ID RH + iniciales (ej. 1FAS)' AFTER `reloj_pin`,
  ADD COLUMN `reloj_sync_at` DATETIME DEFAULT NULL
    COMMENT 'Última sync forzada hacia el reloj' AFTER `reloj_nombre_meta`;

ALTER TABLE `empleados`
  ADD KEY `idx_empleados_reloj_pin` (`reloj_pin`);
