-- Migración: Agregar columnas de configuración de deducciones a nomina_configuracion
-- Permite al usuario activar/desactivar cada deducción desde el modal de Automatización.
--
-- Ejecutar: mysql -u usuario -p basededatos < database/nomina_configuracion_deducciones.sql

ALTER TABLE `nomina_configuracion`
  ADD COLUMN `aplicar_infonavit` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = INFONAVIT se descuenta, 0 = solo se muestra sin descontar',
  ADD COLUMN `aplicar_isr`       tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = ISR se descuenta, 0 = solo se muestra sin descontar (default OFF)',
  ADD COLUMN `aplicar_imss`      tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = IMSS se descuenta, 0 = solo se muestra sin descontar';
