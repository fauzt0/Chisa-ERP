-- Poner nómina base en $0 y tipo quincenal para todos los trabajadores.
-- Ejecutar una sola vez cuando se requiera reiniciar salarios antes de captura real.

UPDATE `empleados`
SET
  `salario_base_mensual` = 0.00,
  `salario_base_diario`  = 0.00,
  `tipo_nomina`          = 'Quincenal';

UPDATE `contratos_empleados`
SET
  `salario_base_mensual` = 0.00,
  `salario_base_diario`  = 0.00,
  `tipo_nomina`          = 'Quincenal'
WHERE `vigente` = 1;
