-- Bloque RESET auditable (usado por run_seed_nominas_reset_demo.php)
-- NO toca empleados, usuarios, cuentas bancarias ni nomina_configuracion.

DELETE FROM nominas_pagos_log;
DELETE FROM nominas_conceptos;
DELETE FROM nominas_detalle;
DELETE FROM nominas;

ALTER TABLE nominas_pagos_log AUTO_INCREMENT = 1;
ALTER TABLE nominas_conceptos AUTO_INCREMENT = 1;
ALTER TABLE nominas_detalle AUTO_INCREMENT = 1;
ALTER TABLE nominas AUTO_INCREMENT = 1;
