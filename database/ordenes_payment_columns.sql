-- Agregar columnas de pago a ordenes_venta
-- Este script agrega las columnas necesarias para el sistema de pagos

-- Verificar si las columnas ya existen antes de agregarlas
SET @dbname = DATABASE();
SET @tablename = 'ordenes_venta';
SET @columnname1 = 'monto_pagado';
SET @columnname2 = 'saldo_pendiente';
SET @columnname3 = 'estatus_pago';

-- Agregar monto_pagado si no existe
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname1) > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0 AFTER total'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar saldo_pendiente si no existe
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname2) > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN saldo_pendiente DECIMAL(10,2) DEFAULT 0 AFTER monto_pagado'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar estatus_pago si no existe
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname3) > 0,
  'SELECT 1',
  "ALTER TABLE ordenes_venta ADD COLUMN estatus_pago ENUM('Pendiente', 'Parcial', 'Pagado') DEFAULT 'Pendiente' AFTER saldo_pendiente"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Actualizar saldos de órdenes existentes
-- Para órdenes que no tienen estos valores, establecer el saldo pendiente = total
UPDATE ordenes_venta 
SET saldo_pendiente = total,
    monto_pagado = 0,
    estatus_pago = 'Pendiente'
WHERE saldo_pendiente IS NULL 
  AND estatus != 'Cancelada' 
  AND estatus != 'Cotización';

-- Para cotizaciones, establecer saldo = 0
UPDATE ordenes_venta 
SET saldo_pendiente = 0,
    monto_pagado = 0,
    estatus_pago = 'Pendiente'
WHERE saldo_pendiente IS NULL 
  AND estatus = 'Cotización';
