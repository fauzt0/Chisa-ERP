-- Pago parcial / por empleado en nómina
-- Ejecutar una sola vez en phpMyAdmin o consola MySQL

-- Columna monto_pagado (omitir si ya existe)
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nominas_detalle' AND COLUMN_NAME = 'monto_pagado'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `nominas_detalle` ADD COLUMN `monto_pagado` decimal(10,2) DEFAULT 0.00 COMMENT ''Monto neto ya pagado'' AFTER `neto`',
  'SELECT ''monto_pagado ya existe'' AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Columna fecha_pago (omitir si ya existe)
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nominas_detalle' AND COLUMN_NAME = 'fecha_pago'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `nominas_detalle` ADD COLUMN `fecha_pago` datetime DEFAULT NULL COMMENT ''Fecha del último pago'' AFTER `monto_pagado`',
  'SELECT ''fecha_pago ya existe'' AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE `nominas_detalle`
  MODIFY COLUMN `estatus` enum('Pendiente','Parcial','Pagado','Cancelado') DEFAULT 'Pendiente';

ALTER TABLE `nominas`
  MODIFY COLUMN `estatus` enum('Borrador','Calculada','Parcial','Pagada','Cancelada') DEFAULT 'Borrador';
