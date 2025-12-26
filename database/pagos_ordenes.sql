-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pagos_ordenes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_venta_id INT(11) NOT NULL,
  folio VARCHAR(50) NOT NULL UNIQUE COMMENT 'PAG-2025-0001',
  fecha_pago DATE NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  metodo_pago ENUM('Efectivo', 'Transferencia', 'Tarjeta', 'Cheque') NOT NULL,
  referencia VARCHAR(100) NULL COMMENT 'Número de cheque, transferencia, etc.',
  notas TEXT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio (folio),
  KEY idx_orden (orden_venta_id),
  KEY idx_fecha (fecha_pago),
  CONSTRAINT fk_pago_orden FOREIGN KEY (orden_venta_id) REFERENCES ordenes_venta(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos aplicados a órdenes de venta';

-- Agregar campos de pago a ordenes_venta
ALTER TABLE ordenes_venta 
ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0 AFTER total,
ADD COLUMN saldo_pendiente DECIMAL(10,2) DEFAULT 0 AFTER monto_pagado,
ADD COLUMN estatus_pago ENUM('Pendiente', 'Parcial', 'Pagado') DEFAULT 'Pendiente' AFTER saldo_pendiente;

-- Stored Procedure para generar folio de pago
DELIMITER $$
CREATE PROCEDURE sp_generar_folio_pago(OUT nuevo_folio VARCHAR(50))
BEGIN
    DECLARE ultimo_numero INT DEFAULT 0;
    DECLARE anio_actual VARCHAR(4);
    
    SET anio_actual = YEAR(CURDATE());
    
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)), 0) 
    INTO ultimo_numero
    FROM pagos_ordenes
    WHERE folio LIKE CONCAT('PAG-', anio_actual, '-%');
    
    SET nuevo_folio = CONCAT('PAG-', anio_actual, '-', LPAD(ultimo_numero + 1, 4, '0'));
END$$
DELIMITER ;

-- Trigger para actualizar saldo de orden al insertar pago
DELIMITER $$
CREATE TRIGGER trg_pago_actualizar_orden
AFTER INSERT ON pagos_ordenes
FOR EACH ROW
BEGIN
    DECLARE total_orden DECIMAL(10,2);
    DECLARE nuevo_monto_pagado DECIMAL(10,2);
    DECLARE nuevo_saldo DECIMAL(10,2);
    DECLARE nuevo_estatus VARCHAR(20);
    
    -- Obtener total de la orden
    SELECT total INTO total_orden
    FROM ordenes_venta
    WHERE id = NEW.orden_venta_id;
    
    -- Calcular nuevo monto pagado
    SELECT COALESCE(SUM(monto), 0) INTO nuevo_monto_pagado
    FROM pagos_ordenes
    WHERE orden_venta_id = NEW.orden_venta_id;
    
    -- Calcular saldo pendiente
    SET nuevo_saldo = total_orden - nuevo_monto_pagado;
    
    -- Determinar estatus de pago
    IF nuevo_saldo <= 0 THEN
        SET nuevo_estatus = 'Pagado';
    ELSEIF nuevo_monto_pagado > 0 THEN
        SET nuevo_estatus = 'Parcial';
    ELSE
        SET nuevo_estatus = 'Pendiente';
    END IF;
    
    -- Actualizar orden
    UPDATE ordenes_venta
    SET monto_pagado = nuevo_monto_pagado,
        saldo_pendiente = nuevo_saldo,
        estatus_pago = nuevo_estatus
    WHERE id = NEW.orden_venta_id;
    
    -- Actualizar saldo del cliente
    UPDATE clientes c
    SET c.saldo_pendiente = (
        SELECT COALESCE(SUM(ov.saldo_pendiente), 0)
        FROM ordenes_venta ov
        WHERE ov.cliente_id = c.id
        AND ov.estatus != 'Cancelada'
    )
    WHERE c.id = (SELECT cliente_id FROM ordenes_venta WHERE id = NEW.orden_venta_id);
END$$
DELIMITER ;

-- Actualizar saldos iniciales de órdenes existentes
UPDATE ordenes_venta 
SET saldo_pendiente = total,
    estatus_pago = 'Pendiente'
WHERE estatus != 'Cancelada' AND estatus != 'Cotización';

-- Actualizar saldos de clientes
UPDATE clientes c
SET c.saldo_pendiente = (
    SELECT COALESCE(SUM(ov.saldo_pendiente), 0)
    FROM ordenes_venta ov
    WHERE ov.cliente_id = c.id
    AND ov.estatus != 'Cancelada'
);
