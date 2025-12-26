-- =====================================================
-- MÓDULO DE ALMACÉN
-- Sistema de gestión de inventario y entregas
-- =====================================================

-- =====================================================
-- 1. TABLA: entregas_almacen
-- Registro de entregas (órdenes de venta y obras)
-- =====================================================
CREATE TABLE IF NOT EXISTS entregas_almacen (
  id INT PRIMARY KEY AUTO_INCREMENT,
  folio VARCHAR(50) UNIQUE NOT NULL COMMENT 'ENT-2025-0001',
  
  -- Origen de la entrega
  tipo_origen ENUM('Orden Venta', 'Obra') NOT NULL,
  orden_venta_id INT NULL,
  obra_id INT NULL,
  
  -- Fechas
  fecha_entrega DATETIME NOT NULL,
  
  -- Usuario responsable
  usuario_id INT NOT NULL,
  
  -- Observaciones
  observaciones TEXT NULL,
  
  -- Control
  estatus ENUM('Activa', 'Cancelada') DEFAULT 'Activa',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  -- Índices
  KEY idx_tipo_origen (tipo_origen),
  KEY idx_fecha (fecha_entrega),
  KEY idx_estatus (estatus),
  KEY idx_orden_venta (orden_venta_id),
  KEY idx_obra (obra_id),
  KEY idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Registro de entregas de almacén (órdenes y obras)';

-- =====================================================
-- 2. TABLA: detalle_entregas_almacen
-- Detalle de productos entregados
-- =====================================================
CREATE TABLE IF NOT EXISTS detalle_entregas_almacen (
  id INT PRIMARY KEY AUTO_INCREMENT,
  entrega_id INT NOT NULL,
  
  -- Tipo de detalle
  tipo_detalle ENUM('Orden Venta', 'Obra') NOT NULL,
  detalle_orden_id INT NULL COMMENT 'ID de detalle_orden_venta',
  obra_producto_id INT NULL COMMENT 'ID de obras_productos',
  
  -- Producto
  producto_id INT NOT NULL,
  cantidad_entregada DECIMAL(10,2) NOT NULL,
  
  -- Referencia al movimiento de stock generado
  movimiento_id INT NULL,
  
  -- Índices
  KEY idx_entrega (entrega_id),
  KEY idx_producto (producto_id),
  KEY idx_detalle_orden (detalle_orden_id),
  KEY idx_obra_producto (obra_producto_id),
  KEY idx_movimiento (movimiento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Detalle de productos entregados';

-- =====================================================
-- 3. MODIFICACIÓN: Agregar campo a obras_productos
-- =====================================================
ALTER TABLE obras_productos 
ADD COLUMN IF NOT EXISTS cantidad_entregada DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Cantidad ya entregada' 
AFTER cantidad_ajustada;

-- =====================================================
-- 4. STORED PROCEDURE: Generar folio de entrega
-- =====================================================
DROP PROCEDURE IF EXISTS sp_generar_folio_entrega;

DELIMITER $$
CREATE PROCEDURE sp_generar_folio_entrega(OUT nuevo_folio VARCHAR(50))
BEGIN
  DECLARE ultimo_numero INT;
  DECLARE anio_actual VARCHAR(4);
  
  SET anio_actual = YEAR(CURDATE());
  
  SELECT COALESCE(MAX(CAST(SUBSTRING(folio, 10) AS UNSIGNED)), 0) INTO ultimo_numero
  FROM entregas_almacen
  WHERE folio LIKE CONCAT('ENT-', anio_actual, '-%');
  
  SET nuevo_folio = CONCAT('ENT-', anio_actual, '-', LPAD(ultimo_numero + 1, 4, '0'));
END$$
DELIMITER ;

-- =====================================================
-- 5. TRIGGER: Actualizar cantidad_entregada y estatus
-- =====================================================
DROP TRIGGER IF EXISTS tr_actualizar_entrega_almacen;

DELIMITER $$
CREATE TRIGGER tr_actualizar_entrega_almacen
AFTER INSERT ON detalle_entregas_almacen
FOR EACH ROW
BEGIN
  DECLARE v_orden_id INT;
  DECLARE v_obra_id INT;
  
  -- Actualizar según tipo de detalle
  IF NEW.tipo_detalle = 'Orden Venta' THEN
    -- Actualizar cantidad entregada en detalle de orden
    UPDATE detalle_orden_venta 
    SET cantidad_entregada = cantidad_entregada + NEW.cantidad_entregada
    WHERE id = NEW.detalle_orden_id;
    
    -- Obtener orden_venta_id
    SELECT orden_venta_id INTO v_orden_id
    FROM detalle_orden_venta 
    WHERE id = NEW.detalle_orden_id;
    
    -- Actualizar estatus de orden de venta
    UPDATE ordenes_venta ov
    SET 
      estatus = CASE
        -- Si todo está entregado → Entregada
        WHEN (SELECT SUM(cantidad) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) = 
             (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id)
        THEN 'Entregada'
        -- Si hay algo entregado → En Preparación
        WHEN (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) > 0
        THEN 'En Preparación'
        ELSE estatus
      END,
      fecha_entrega_real = CASE
        WHEN (SELECT SUM(cantidad) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) = 
             (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id)
        THEN NOW()
        ELSE fecha_entrega_real
      END
    WHERE id = v_orden_id;
    
  ELSEIF NEW.tipo_detalle = 'Obra' THEN
    -- Actualizar cantidad entregada en obras_productos
    UPDATE obras_productos 
    SET cantidad_entregada = cantidad_entregada + NEW.cantidad_entregada
    WHERE id = NEW.obra_producto_id;
    
    -- Obtener obra_id
    SELECT obra_id INTO v_obra_id
    FROM obras_productos 
    WHERE id = NEW.obra_producto_id;
    
    -- Actualizar estatus de obra
    UPDATE obras o
    SET 
      estatus = CASE
        -- Si todo está entregado → Finalizada
        WHEN (SELECT SUM(cantidad) FROM obras_productos WHERE obra_id = v_obra_id) = 
             (SELECT SUM(cantidad_entregada) FROM obras_productos WHERE obra_id = v_obra_id)
        THEN 'Finalizada'
        -- Si hay algo entregado → En Proceso
        WHEN (SELECT SUM(cantidad_entregada) FROM obras_productos WHERE obra_id = v_obra_id) > 0
        THEN 'En Proceso'
        ELSE estatus
      END
    WHERE id = v_obra_id;
  END IF;
END$$
DELIMITER ;

-- =====================================================
-- 6. ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================
CREATE INDEX idx_entregas_fecha_tipo ON entregas_almacen(fecha_entrega, tipo_origen);
CREATE INDEX idx_detalle_tipo ON detalle_entregas_almacen(tipo_detalle);

-- =====================================================
-- FIN DEL SCHEMA DE ALMACÉN
-- =====================================================
