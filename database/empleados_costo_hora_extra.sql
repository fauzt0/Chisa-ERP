ALTER TABLE `empleados`
  ADD COLUMN `costo_hora_extra` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo por hora extra (preestablecido para cálculo de nómina)' AFTER `descuento_infonavit`;
