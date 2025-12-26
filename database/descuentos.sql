-- Tabla de descuentos/precios especiales
CREATE TABLE IF NOT EXISTS descuentos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del descuento',
  descripcion TEXT NULL COMMENT 'Descripción detallada',
  tipo_descuento ENUM('Porcentaje', 'Monto Fijo') DEFAULT 'Porcentaje',
  valor DECIMAL(10,2) NOT NULL COMMENT 'Porcentaje o monto del descuento',
  estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  KEY idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Descuentos y precios especiales';

-- Agregar campos de descuento a ordenes_venta
ALTER TABLE ordenes_venta 
ADD COLUMN descuento_id INT(11) NULL AFTER forma_pago,
ADD COLUMN descuento_nombre VARCHAR(100) NULL AFTER descuento_id,
ADD COLUMN descuento_tipo VARCHAR(20) NULL AFTER descuento_nombre,
ADD COLUMN descuento_valor DECIMAL(10,2) DEFAULT 0 AFTER descuento_tipo,
ADD COLUMN descuento_aplicado DECIMAL(10,2) DEFAULT 0 AFTER descuento_valor,
ADD KEY idx_descuento (descuento_id);

-- Insertar algunos descuentos de ejemplo
INSERT INTO descuentos (nombre, descripcion, tipo_descuento, valor, estatus) VALUES
('Descuento Cliente Frecuente', 'Descuento del 10% para clientes frecuentes', 'Porcentaje', 10.00, 'Activo'),
('Descuento Mayoreo', 'Descuento del 15% para compras mayores a $10,000', 'Porcentaje', 15.00, 'Activo'),
('Descuento Promoción', 'Descuento fijo de $500 en compras especiales', 'Monto Fijo', 500.00, 'Activo');

-- Actualizar trigger para calcular totales con descuento
DROP TRIGGER IF EXISTS trg_ordenes_venta_calcular_totales;

DELIMITER $$
CREATE TRIGGER trg_ordenes_venta_calcular_totales
BEFORE UPDATE ON ordenes_venta
FOR EACH ROW
BEGIN
    -- Calcular descuento aplicado
    IF NEW.descuento_tipo = 'Porcentaje' THEN
        SET NEW.descuento_aplicado = NEW.subtotal * (NEW.descuento_valor / 100);
    ELSEIF NEW.descuento_tipo = 'Monto Fijo' THEN
        SET NEW.descuento_aplicado = NEW.descuento_valor;
    ELSE
        SET NEW.descuento_aplicado = 0;
    END IF;
    
    -- Recalcular IVA y total con descuento
    SET NEW.iva = (NEW.subtotal - NEW.descuento_aplicado) * 0.16;
    SET NEW.total = NEW.subtotal - NEW.descuento_aplicado + NEW.iva;
END$$
DELIMITER ;
