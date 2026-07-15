-- Comprobantes de pago para servicios recurrentes
ALTER TABLE `pagos_servicios_recurrentes`
  ADD COLUMN IF NOT EXISTS `comprobante_ruta` VARCHAR(500) NULL DEFAULT NULL AFTER `notas`,
  ADD COLUMN IF NOT EXISTS `comprobante_nombre` VARCHAR(255) NULL DEFAULT NULL AFTER `comprobante_ruta`,
  ADD COLUMN IF NOT EXISTS `comprobante_mime` VARCHAR(120) NULL DEFAULT NULL AFTER `comprobante_nombre`;
