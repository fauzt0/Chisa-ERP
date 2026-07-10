-- Pagos de órdenes de compra (cuentas por pagar)
-- Ejecutar: mysql st32477_chisa < database/pagos_ordenes_compra.sql

CREATE TABLE IF NOT EXISTS pagos_ordenes_compra (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_compra_id INT(11) NOT NULL,
  folio VARCHAR(50) NOT NULL,
  fecha_pago DATE NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  metodo_pago ENUM('Efectivo','Transferencia','Cheque','Crédito') NOT NULL DEFAULT 'Transferencia',
  referencia VARCHAR(100) NULL,
  notas TEXT NULL,
  registrado_por INT(11) NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio (folio),
  KEY idx_orden (orden_compra_id),
  KEY idx_fecha (fecha_pago),
  CONSTRAINT fk_pago_oc FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos aplicados a órdenes de compra';

-- Campos de adeudo en órdenes de compra
ALTER TABLE ordenes_compra
  ADD COLUMN IF NOT EXISTS monto_pagado DECIMAL(12,2) DEFAULT 0 AFTER total,
  ADD COLUMN IF NOT EXISTS saldo_pendiente DECIMAL(12,2) DEFAULT 0 AFTER monto_pagado,
  ADD COLUMN IF NOT EXISTS estatus_pago ENUM('Pendiente','Parcial','Pagado','Sin adeudo') DEFAULT 'Sin adeudo' AFTER saldo_pendiente;

-- Ampliar tipos de servicio recurrente
ALTER TABLE servicios_recurrentes
  MODIFY COLUMN tipo_servicio ENUM(
    'Servicios Públicos','Renta','Seguros','Suscripciones',
    'Telecomunicaciones','Mantenimiento','Soporte Técnico','Recolección de Basura','Otros'
  ) DEFAULT 'Otros';

-- Inicializar saldos en OC existentes (no borrador/cancelada)
UPDATE ordenes_compra
SET monto_pagado = 0,
    saldo_pendiente = total,
    estatus_pago = 'Pendiente'
WHERE estatus NOT IN ('Borrador', 'Cancelada') AND total > 0;

UPDATE ordenes_compra
SET monto_pagado = 0,
    saldo_pendiente = 0,
    estatus_pago = 'Sin adeudo'
WHERE estatus IN ('Borrador', 'Cancelada') OR total = 0 OR total IS NULL;
