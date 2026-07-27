-- Archivo a crear: database/nomina_detalle_iteracion2.sql

ALTER TABLE `nominas_detalle`
  ADD COLUMN `lugar_origen` varchar(100) DEFAULT NULL COMMENT 'Lugar u origen (Oficina, Lagunas Oaxaca, Tuxtla, etc.)' AFTER `empleado_id`,
  ADD COLUMN `sueldo_diario` decimal(10,2) DEFAULT 0.00 COMMENT 'Sueldo diario del empleado al momento del cálculo' AFTER `sueldo_base`,
  ADD COLUMN `horas_extras` decimal(6,2) DEFAULT 0.00 COMMENT 'Cantidad de horas extras' AFTER `sueldo_diario`,
  ADD COLUMN `costo_hora_extra` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo por hora extra (preestablecido por empleado)' AFTER `horas_extras`,
  ADD COLUMN `monto_horas_extras` decimal(10,2) DEFAULT 0.00 COMMENT 'Total horas extras = horas_extras * costo_hora_extra' AFTER `costo_hora_extra`,
  ADD COLUMN `comidas` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto por comidas' AFTER `monto_horas_extras`,
  ADD COLUMN `viaticos_pasajes` decimal(10,2) DEFAULT 0.00 COMMENT 'Viáticos y pasajes' AFTER `comidas`,
  ADD COLUMN `prima` decimal(10,2) DEFAULT 0.00 COMMENT 'Prima vacacional/dominical' AFTER `viaticos_pasajes`,
  ADD COLUMN `otros_bonos` decimal(10,2) DEFAULT 0.00 COMMENT 'Otros bonos' AFTER `prima`,
  ADD COLUMN `otros_ingresos` decimal(10,2) DEFAULT 0.00 COMMENT 'Otros ingresos/percepciones' AFTER `otros_bonos`,
  ADD COLUMN `prestamo_personal` decimal(10,2) DEFAULT 0.00 COMMENT 'Descuento por préstamo personal' AFTER `deducciones`,
  ADD COLUMN `otros_descuentos` decimal(10,2) DEFAULT 0.00 COMMENT 'Otros descuentos no contemplados' AFTER `prestamo_personal`,
  ADD COLUMN `infonavit_descuento` decimal(10,2) DEFAULT 0.00 COMMENT 'Descuento INFONAVIT del periodo (desglose visible)' AFTER `otros_descuentos`;
