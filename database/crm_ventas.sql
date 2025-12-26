-- =====================================================
-- MÓDULO CRM - VENTAS (POINT OF SALE)
-- Sistema de gestión de ventas con interfaz POS
-- Incluye: Clientes, Órdenes de Venta, Solicitudes de Producción
-- =====================================================

-- =====================================================
-- TABLA: clientes
-- =====================================================
CREATE TABLE IF NOT EXISTS clientes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'CLI-00001',
  
  -- Datos fiscales
  razon_social VARCHAR(200) NOT NULL,
  nombre_comercial VARCHAR(200) NULL,
  rfc VARCHAR(13) NOT NULL,
  regimen_fiscal VARCHAR(100) NULL,
  
  -- Contacto
  contacto_nombre VARCHAR(150) NULL,
  telefono VARCHAR(20) NULL,
  email VARCHAR(100) NULL,
  
  -- Dirección
  calle VARCHAR(200) NULL,
  numero_exterior VARCHAR(20) NULL,
  numero_interior VARCHAR(20) NULL,
  colonia VARCHAR(100) NULL,
  ciudad VARCHAR(100) NULL,
  estado VARCHAR(50) NULL,
  codigo_postal VARCHAR(10) NULL,
  
  -- Financiero
  limite_credito DECIMAL(10,2) DEFAULT 0.00,
  dias_credito INT(11) DEFAULT 0,
  saldo_pendiente DECIMAL(10,2) DEFAULT 0.00,
  
  -- Control
  tipo_cliente ENUM('Empresa', 'Persona Física', 'Mostrador') DEFAULT 'Empresa',
  estatus ENUM('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_codigo (codigo),
  KEY idx_estatus (estatus),
  KEY idx_razon_social (razon_social),
  KEY idx_tipo_cliente (tipo_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clientes del sistema';

-- Insertar cliente por defecto para ventas en mostrador
INSERT INTO clientes (codigo, razon_social, rfc, tipo_cliente, estatus) VALUES
('CLI-00000', 'CLIENTE MOSTRADOR', 'XAXX010101000', 'Mostrador', 'Activo');

-- =====================================================
-- TABLA: ordenes_venta
-- =====================================================
CREATE TABLE IF NOT EXISTS ordenes_venta (
  id INT(11) NOT NULL AUTO_INCREMENT,
  folio VARCHAR(50) NOT NULL UNIQUE COMMENT 'OV-2025-0001',
  
  -- Cliente
  cliente_id INT(11) NOT NULL,
  
  -- Fechas
  fecha_orden DATE NOT NULL,
  fecha_entrega_estimada DATE NULL,
  fecha_entrega_real DATE NULL,
  
  -- Montos
  subtotal DECIMAL(10,2) DEFAULT 0.00,
  iva DECIMAL(10,2) DEFAULT 0.00,
  total DECIMAL(10,2) DEFAULT 0.00,
  
  -- Pago
  forma_pago ENUM('Efectivo', 'Transferencia', 'Cheque', 'Tarjeta', 'Crédito') DEFAULT 'Efectivo',
  condiciones_pago VARCHAR(200) NULL COMMENT 'Ej: 50% anticipo, 50% contra entrega',
  
  -- Estado
  estatus ENUM('Cotización', 'Confirmada', 'En Preparación', 'Entregada', 'Cancelada') DEFAULT 'Cotización',
  tipo_venta ENUM('Mostrador', 'Pedido') DEFAULT 'Mostrador',
  
  -- Observaciones
  observaciones TEXT NULL,
  motivo_cancelacion TEXT NULL,
  
  -- Producción
  requiere_produccion BOOLEAN DEFAULT FALSE COMMENT 'Si algún producto no tiene stock',
  
  -- Auditoría
  creado_por INT(11) NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio (folio),
  KEY idx_cliente (cliente_id),
  KEY idx_estatus (estatus),
  KEY idx_fecha (fecha_orden),
  KEY idx_tipo_venta (tipo_venta),
  CONSTRAINT fk_ov_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de venta y cotizaciones';

-- =====================================================
-- TABLA: detalle_orden_venta
-- =====================================================
CREATE TABLE IF NOT EXISTS detalle_orden_venta (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_venta_id INT(11) NOT NULL,
  producto_id INT(11) NOT NULL,
  
  -- Cantidades
  cantidad DECIMAL(10,2) NOT NULL,
  cantidad_entregada DECIMAL(10,2) DEFAULT 0.00,
  
  -- Precios
  precio_unitario DECIMAL(10,2) NOT NULL,
  descuento DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de descuento',
  subtotal DECIMAL(10,2) NOT NULL COMMENT 'cantidad * precio_unitario * (1 - descuento/100)',
  
  -- Stock
  stock_disponible_al_crear DECIMAL(10,2) NULL COMMENT 'Stock disponible al momento de crear la orden',
  requiere_produccion BOOLEAN DEFAULT FALSE,
  
  -- Observaciones
  observaciones TEXT NULL,
  
  PRIMARY KEY (id),
  KEY idx_orden (orden_venta_id),
  KEY idx_producto (producto_id),
  CONSTRAINT fk_dov_orden FOREIGN KEY (orden_venta_id) REFERENCES ordenes_venta(id) ON DELETE CASCADE,
  CONSTRAINT fk_dov_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en órdenes de venta';

-- =====================================================
-- TABLA: solicitudes_produccion
-- =====================================================
CREATE TABLE IF NOT EXISTS solicitudes_produccion (
  id INT(11) NOT NULL AUTO_INCREMENT,
  folio VARCHAR(50) NOT NULL UNIQUE COMMENT 'SP-2025-0001',
  
  -- Origen
  orden_venta_id INT(11) NULL COMMENT 'Orden de venta que generó la solicitud',
  producto_id INT(11) NOT NULL,
  
  -- Cantidades
  cantidad_solicitada DECIMAL(10,2) NOT NULL,
  cantidad_producida DECIMAL(10,2) DEFAULT 0.00,
  
  -- Fechas
  fecha_solicitud DATE NOT NULL,
  fecha_requerida DATE NULL COMMENT 'Fecha en que se necesita el producto',
  fecha_inicio_produccion DATE NULL,
  fecha_fin_produccion DATE NULL,
  
  -- Estado
  estatus ENUM('Pendiente', 'En Proceso', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
  prioridad ENUM('Baja', 'Media', 'Alta', 'Urgente') DEFAULT 'Media',
  
  -- Observaciones
  observaciones TEXT NULL,
  
  -- Auditoría
  creado_por INT(11) NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio (folio),
  KEY idx_orden_venta (orden_venta_id),
  KEY idx_producto (producto_id),
  KEY idx_estatus (estatus),
  KEY idx_prioridad (prioridad),
  CONSTRAINT fk_sp_orden_venta FOREIGN KEY (orden_venta_id) REFERENCES ordenes_venta(id) ON DELETE SET NULL,
  CONSTRAINT fk_sp_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de producción generadas por ventas';

-- =====================================================
-- TRIGGERS PARA CÁLCULOS AUTOMÁTICOS
-- =====================================================

-- Trigger: Calcular subtotal de detalle antes de insertar
DELIMITER $$
CREATE TRIGGER tr_detalle_ov_subtotal_insert
BEFORE INSERT ON detalle_orden_venta
FOR EACH ROW
BEGIN
  SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario * (1 - NEW.descuento / 100);
END$$
DELIMITER ;

-- Trigger: Calcular subtotal de detalle antes de actualizar
DELIMITER $$
CREATE TRIGGER tr_detalle_ov_subtotal_update
BEFORE UPDATE ON detalle_orden_venta
FOR EACH ROW
BEGIN
  SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario * (1 - NEW.descuento / 100);
END$$
DELIMITER ;

-- Trigger: Actualizar totales de orden después de insertar detalle
DELIMITER $$
CREATE TRIGGER tr_actualizar_totales_ov_insert
AFTER INSERT ON detalle_orden_venta
FOR EACH ROW
BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = NEW.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = NEW.orden_venta_id;
END$$
DELIMITER ;

-- Trigger: Actualizar totales de orden después de actualizar detalle
DELIMITER $$
CREATE TRIGGER tr_actualizar_totales_ov_update
AFTER UPDATE ON detalle_orden_venta
FOR EACH ROW
BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = NEW.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = NEW.orden_venta_id;
END$$
DELIMITER ;

-- Trigger: Actualizar totales de orden después de eliminar detalle
DELIMITER $$
CREATE TRIGGER tr_actualizar_totales_ov_delete
AFTER DELETE ON detalle_orden_venta
FOR EACH ROW
BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = OLD.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = OLD.orden_venta_id;
END$$
DELIMITER ;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure: Generar folio de orden de venta
DELIMITER $$
CREATE PROCEDURE sp_generar_folio_orden_venta(OUT nuevo_folio VARCHAR(50))
BEGIN
  DECLARE ultimo_numero INT;
  DECLARE anio_actual VARCHAR(4);
  
  SET anio_actual = YEAR(CURDATE());
  
  SELECT COALESCE(MAX(CAST(SUBSTRING(folio, 9) AS UNSIGNED)), 0) INTO ultimo_numero
  FROM ordenes_venta
  WHERE folio LIKE CONCAT('OV-', anio_actual, '-%');
  
  SET nuevo_folio = CONCAT('OV-', anio_actual, '-', LPAD(ultimo_numero + 1, 4, '0'));
END$$
DELIMITER ;

-- Procedure: Generar folio de solicitud de producción
DELIMITER $$
CREATE PROCEDURE sp_generar_folio_solicitud_produccion(OUT nuevo_folio VARCHAR(50))
BEGIN
  DECLARE ultimo_numero INT;
  DECLARE anio_actual VARCHAR(4);
  
  SET anio_actual = YEAR(CURDATE());
  
  SELECT COALESCE(MAX(CAST(SUBSTRING(folio, 9) AS UNSIGNED)), 0) INTO ultimo_numero
  FROM solicitudes_produccion
  WHERE folio LIKE CONCAT('SP-', anio_actual, '-%');
  
  SET nuevo_folio = CONCAT('SP-', anio_actual, '-', LPAD(ultimo_numero + 1, 4, '0'));
END$$
DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

CREATE INDEX idx_clientes_rfc ON clientes(rfc);
CREATE INDEX idx_ordenes_fecha_estatus ON ordenes_venta(fecha_orden, estatus);
CREATE INDEX idx_solicitudes_fecha_estatus ON solicitudes_produccion(fecha_solicitud, estatus);

-- =====================================================
-- FIN DEL SCHEMA DE CRM VENTAS
-- =====================================================
