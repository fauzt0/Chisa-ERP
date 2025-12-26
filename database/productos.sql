CREATE TABLE IF NOT EXISTS categorias_productos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT NULL,
  categoria_padre_id INT(11) NULL COMMENT 'Para jerarquía de categorías',
  estatus ENUM('Activa', 'Inactiva') DEFAULT 'Activa',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_padre (categoria_padre_id),
  CONSTRAINT fk_cat_prod_padre FOREIGN KEY (categoria_padre_id) REFERENCES categorias_productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de productos terminados';
-- Insertar categorías iniciales
INSERT INTO categorias_productos (nombre, descripcion, categoria_padre_id) VALUES
('Pinturas', 'Pinturas y recubrimientos', NULL),
('Recubrimientos', 'Recubrimientos especiales', NULL),
('Preparadores de Superficies', 'Primers, selladores, fondos', NULL),
('Pastas', 'Pastas y masillas', NULL),
('Selladores', 'Selladores y tapaporos', NULL),
('Reventa', 'Productos de reventa directa', NULL);
-- Subcategorías de Pinturas
INSERT INTO categorias_productos (nombre, descripcion, categoria_padre_id) VALUES
('Vinílicas', 'Pinturas vinílicas', 1),
('Esmaltes', 'Esmaltes y acabados', 1),
('Impermeabilizantes', 'Impermeabilizantes', 1);
-- Subcategorías de Reventa
INSERT INTO categorias_productos (nombre, descripcion, categoria_padre_id) VALUES
('Herramientas', 'Espátulas, rodillos, brochas', 6),
('Accesorios', 'Accesorios diversos', 6);
-- =====================================================
-- Tabla: productos
-- Catálogo de productos terminados (fabricados y reventa)
CREATE TABLE IF NOT EXISTS productos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código único del producto (PROD-0001)',
  nombre VARCHAR(200) NOT NULL,
  descripcion TEXT NULL,
  categoria_id INT(11) NOT NULL,
  tipo_producto ENUM('Fabricado', 'Reventa') NOT NULL DEFAULT 'Fabricado',
  
  -- Presentación y unidades
  unidad_venta ENUM('Cubeta', 'Litro', 'Galon', 'Kg', 'Pieza', 'Caja', 'Metro', 'M2') DEFAULT 'Cubeta',
  presentacion_principal VARCHAR(50) NULL COMMENT 'Ej: 19L, 4L, 1L',
  contenido_neto DECIMAL(10,2) NULL COMMENT 'Contenido en unidad base',
  unidad_contenido ENUM('L', 'ml', 'Kg', 'g', 'Pza') DEFAULT 'L',
  
  -- Códigos de identificación (NUEVO)
  codigo_barras VARCHAR(50) NULL COMMENT 'Código de barras EAN-13, UPC, etc.',
  codigo_qr TEXT NULL COMMENT 'Datos para generar QR (JSON o texto)',
  sku VARCHAR(50) NULL COMMENT 'SKU interno',
  
  -- Inventario
  stock_actual DECIMAL(10,2) DEFAULT 0.00,
  stock_minimo DECIMAL(10,2) DEFAULT 0.00,
  stock_maximo DECIMAL(10,2) DEFAULT 0.00,
  ubicacion_almacen VARCHAR(100) NULL,
  
  -- Costos y precios
  costo_produccion DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Calculado de formulación o costo de compra',
  precio_venta DECIMAL(10,2) DEFAULT 0.00,
  margen_utilidad DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de utilidad',
  
  -- Documentación
  foto_producto VARCHAR(255) NULL COMMENT 'Ruta de la foto',
  ficha_tecnica VARCHAR(255) NULL COMMENT 'Ruta del PDF de ficha técnica',
  hoja_seguridad VARCHAR(255) NULL COMMENT 'Ruta del PDF de hoja de seguridad',
  
  -- Datos adicionales
  peso_bruto DECIMAL(10,2) NULL COMMENT 'Peso con envase en Kg',
  rendimiento VARCHAR(100) NULL COMMENT 'Ej: 10-12 m2/L',
  tiempo_secado VARCHAR(100) NULL COMMENT 'Ej: 2-4 horas',
  colores_disponibles TEXT NULL COMMENT 'JSON o texto separado por comas',
  
  -- Proveedor (solo para reventa)
  proveedor_id INT(11) NULL COMMENT 'Solo si es producto de reventa',
  
  -- Control
  estatus ENUM('Activo', 'Inactivo', 'Descontinuado') DEFAULT 'Activo',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  usuario_creacion INT(11) NULL,
  fecha_modificacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_codigo (codigo),
  UNIQUE KEY uk_codigo_barras (codigo_barras),
  UNIQUE KEY uk_sku (sku),
  KEY idx_categoria (categoria_id),
  KEY idx_tipo (tipo_producto),
  KEY idx_estatus (estatus),
  KEY idx_proveedor (proveedor_id),
  CONSTRAINT fk_prod_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_productos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_prod_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de productos terminados';
-- =====================================================
-- Tabla: movimientos_productos (NUEVA)
-- Registro de entradas y salidas de productos terminados
CREATE TABLE IF NOT EXISTS movimientos_productos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  producto_id INT(11) NOT NULL,
  tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste', 'Produccion', 'Venta', 'Devolucion') NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  stock_anterior DECIMAL(10,2) NOT NULL,
  stock_nuevo DECIMAL(10,2) NOT NULL,
  
  -- Referencia
  orden_produccion_id INT(11) NULL COMMENT 'Si viene de producción',
  venta_id INT(11) NULL COMMENT 'Si es una venta',
  motivo TEXT NULL,
  
  -- Escaneo (NUEVO)
  escaneado_barras BOOLEAN DEFAULT FALSE COMMENT 'Si se escaneó código de barras/QR',
  codigo_escaneado VARCHAR(100) NULL,
  
  -- Auditoría
  usuario_id INT(11) NULL,
  fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (id),
  KEY idx_producto (producto_id),
  KEY idx_tipo (tipo_movimiento),
  KEY idx_fecha (fecha_movimiento),
  CONSTRAINT fk_movprod_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';
-- Trigger: Actualizar stock de producto al crear movimiento
DELIMITER $$
CREATE TRIGGER tr_actualizar_stock_producto
AFTER INSERT ON movimientos_productos
FOR EACH ROW
BEGIN
  UPDATE productos 
  SET stock_actual = NEW.stock_nuevo
  WHERE id = NEW.producto_id;
END$$
DELIMITER ;
-- =====================================================
-- Tabla: formulaciones
-- Formulaciones (BOM - Bill of Materials) de productos fabricados
CREATE TABLE IF NOT EXISTS formulaciones (
  id INT(11) NOT NULL AUTO_INCREMENT,
  producto_id INT(11) NOT NULL,
  version INT(11) DEFAULT 1 COMMENT 'Versión de la formulación',
  nombre_version VARCHAR(100) NULL COMMENT 'Ej: V1.0, V2.0 Mejorada',
  descripcion TEXT NULL,
  
  -- Rendimiento
  cantidad_producida DECIMAL(10,2) NOT NULL COMMENT 'Cantidad que produce esta formulación',
  unidad_produccion ENUM('L', 'ml', 'Kg', 'g', 'Pza') DEFAULT 'L',
  
  -- Costos
  costo_total_insumos DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Calculado automáticamente',
  costo_mano_obra DECIMAL(10,2) DEFAULT 0.00,
  costo_indirecto DECIMAL(10,2) DEFAULT 0.00,
  costo_total DECIMAL(10,2) DEFAULT 0.00,
  
  -- Control de versión
  es_activa BOOLEAN DEFAULT TRUE COMMENT 'Solo una versión activa por producto',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  usuario_creacion INT(11) NULL,
  fecha_activacion DATETIME NULL,
  
  PRIMARY KEY (id),
  KEY idx_producto (producto_id),
  KEY idx_activa (es_activa),
  CONSTRAINT fk_form_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formulaciones de productos (BOM)';
-- =====================================================
-- Tabla: detalle_formulacion
-- Componentes de la formulación (insumos y productos)
CREATE TABLE IF NOT EXISTS detalle_formulacion (
  id INT(11) NOT NULL AUTO_INCREMENT,
  formulacion_id INT(11) NOT NULL,
  
  -- Tipo de componente
  tipo_componente ENUM('Insumo', 'Producto') NOT NULL COMMENT 'Insumo o Producto base',
  insumo_id INT(11) NULL COMMENT 'Si es insumo',
  producto_id INT(11) NULL COMMENT 'Si es producto base',
  
  -- Cantidad
  cantidad DECIMAL(10,3) NOT NULL,
  unidad ENUM('L', 'ml', 'Kg', 'g', 'Pza') NOT NULL,
  porcentaje DECIMAL(5,2) NULL COMMENT 'Porcentaje en la formulación',
  
  -- Costo
  costo_unitario DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Costo del componente al momento',
  costo_total DECIMAL(10,2) DEFAULT 0.00 COMMENT 'cantidad * costo_unitario',
  
  -- Notas
  observaciones TEXT NULL,
  orden INT(11) DEFAULT 0 COMMENT 'Orden de mezclado',
  
  PRIMARY KEY (id),
  KEY idx_formulacion (formulacion_id),
  KEY idx_insumo (insumo_id),
  KEY idx_producto (producto_id),
  CONSTRAINT fk_detform_formulacion FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE CASCADE,
  CONSTRAINT fk_detform_insumo FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_detform_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  CONSTRAINT chk_componente CHECK (
    (tipo_componente = 'Insumo' AND insumo_id IS NOT NULL AND producto_id IS NULL) OR
    (tipo_componente = 'Producto' AND producto_id IS NOT NULL AND insumo_id IS NULL)
  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de formulaciones (BOM)';
-- =====================================================
-- Tabla: alertas_stock (NUEVA)
-- Sistema de alertas para productos e insumos
CREATE TABLE IF NOT EXISTS alertas_stock (
  id INT(11) NOT NULL AUTO_INCREMENT,
  tipo_alerta ENUM('Producto', 'Insumo', 'Formulacion') NOT NULL,
  
  -- Referencia
  producto_id INT(11) NULL,
  insumo_id INT(11) NULL,
  formulacion_id INT(11) NULL COMMENT 'Si la alerta es por formulación',
  
  -- Alerta
  nivel_alerta ENUM('Critico', 'Bajo', 'Proximo') NOT NULL,
  mensaje TEXT NOT NULL,
  stock_actual DECIMAL(10,2) NULL,
  stock_minimo DECIMAL(10,2) NULL,
  
  -- Estado
  leida BOOLEAN DEFAULT FALSE,
  resuelta BOOLEAN DEFAULT FALSE,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_lectura DATETIME NULL,
  fecha_resolucion DATETIME NULL,
  
  PRIMARY KEY (id),
  KEY idx_tipo (tipo_alerta),
  KEY idx_nivel (nivel_alerta),
  KEY idx_leida (leida),
  KEY idx_producto (producto_id),
  KEY idx_insumo (insumo_id),
  CONSTRAINT fk_alert_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_alert_insumo FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE,
  CONSTRAINT fk_alert_formulacion FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alertas de stock bajo';
-- =====================================================
-- Tabla: presentaciones_producto
-- Diferentes presentaciones de un mismo producto
CREATE TABLE IF NOT EXISTS presentaciones_producto (
  id INT(11) NOT NULL AUTO_INCREMENT,
  producto_id INT(11) NOT NULL,
  nombre VARCHAR(50) NOT NULL COMMENT 'Ej: Cubeta 19L, Cubeta 4L, Galon',
  contenido DECIMAL(10,2) NOT NULL,
  unidad ENUM('L', 'ml', 'Kg', 'g', 'Pza') NOT NULL,
  codigo_barras VARCHAR(50) NULL COMMENT 'Código de barras específico de esta presentación',
  precio_venta DECIMAL(10,2) DEFAULT 0.00,
  es_principal BOOLEAN DEFAULT FALSE,
  estatus ENUM('Activa', 'Inactiva') DEFAULT 'Activa',
  PRIMARY KEY (id),
  UNIQUE KEY uk_pres_codigo_barras (codigo_barras),
  KEY idx_producto (producto_id),
  CONSTRAINT fk_pres_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Presentaciones de productos';
-- =====================================================
-- TRIGGERS para cálculos automáticos
-- =====================================================
-- Trigger: Calcular costo total de detalle de formulación
DELIMITER $$
CREATE TRIGGER tr_detalle_formulacion_costo
BEFORE INSERT ON detalle_formulacion
FOR EACH ROW
BEGIN
  SET NEW.costo_total = NEW.cantidad * NEW.costo_unitario;
END$$
DELIMITER ;
-- Trigger: Actualizar costo total de formulación al insertar detalle
DELIMITER $$
CREATE TRIGGER tr_actualizar_costo_formulacion_insert
AFTER INSERT ON detalle_formulacion
FOR EACH ROW
BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = NEW.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = NEW.formulacion_id;
END$$
DELIMITER ;
-- Trigger: Actualizar costo total de formulación al actualizar detalle
DELIMITER $$
CREATE TRIGGER tr_actualizar_costo_formulacion_update
AFTER UPDATE ON detalle_formulacion
FOR EACH ROW
BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = NEW.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = NEW.formulacion_id;
END$$
DELIMITER ;
-- Trigger: Actualizar costo total de formulación al eliminar detalle
DELIMITER $$
CREATE TRIGGER tr_actualizar_costo_formulacion_delete
AFTER DELETE ON detalle_formulacion
FOR EACH ROW
BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = OLD.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = OLD.formulacion_id;
END$$
DELIMITER ;
-- Trigger: Actualizar costo de producción del producto al cambiar formulación activa
DELIMITER $$
CREATE TRIGGER tr_actualizar_costo_producto
AFTER UPDATE ON formulaciones
FOR EACH ROW
BEGIN
  IF NEW.es_activa = TRUE THEN
    UPDATE productos 
    SET costo_produccion = NEW.costo_total
    WHERE id = NEW.producto_id;
  END IF;
END$$
DELIMITER ;
-- =====================================================
-- STORED PROCEDURES para alertas (NUEVO)
-- =====================================================
-- Procedure: Verificar stock bajo de productos
DELIMITER $$
CREATE PROCEDURE sp_verificar_stock_productos()
BEGIN
  -- Limpiar alertas antiguas resueltas (más de 30 días)
  DELETE FROM alertas_stock 
  WHERE resuelta = TRUE 
  AND fecha_resolucion < DATE_SUB(NOW(), INTERVAL 30 DAY);
  
  -- Crear alertas para productos con stock bajo
  INSERT INTO alertas_stock (tipo_alerta, producto_id, nivel_alerta, mensaje, stock_actual, stock_minimo)
  SELECT 
    'Producto',
    p.id,
    CASE 
      WHEN p.stock_actual = 0 THEN 'Critico'
      WHEN p.stock_actual <= p.stock_minimo * 0.5 THEN 'Critico'
      WHEN p.stock_actual <= p.stock_minimo THEN 'Bajo'
      ELSE 'Proximo'
    END,
    CONCAT('Producto "', p.nombre, '" con stock bajo: ', p.stock_actual, ' ', p.unidad_venta),
    p.stock_actual,
    p.stock_minimo
  FROM productos p
  WHERE p.estatus = 'Activo'
  AND p.stock_actual <= p.stock_minimo
  AND NOT EXISTS (
    SELECT 1 FROM alertas_stock a 
    WHERE a.producto_id = p.id 
    AND a.resuelta = FALSE
    AND a.tipo_alerta = 'Producto'
  );
END$$
DELIMITER ;
-- Procedure: Verificar insumos de formulaciones
DELIMITER $$
CREATE PROCEDURE sp_verificar_insumos_formulaciones()
BEGIN
  -- Alertas para insumos usados en formulaciones activas
  INSERT INTO alertas_stock (tipo_alerta, insumo_id, formulacion_id, nivel_alerta, mensaje, stock_actual, stock_minimo)
  SELECT DISTINCT
    'Formulacion',
    i.id,
    f.id,
    CASE 
      WHEN i.stock_actual = 0 THEN 'Critico'
      WHEN i.stock_actual <= i.stock_minimo * 0.5 THEN 'Critico'
      WHEN i.stock_actual <= i.stock_minimo THEN 'Bajo'
      ELSE 'Proximo'
    END,
    CONCAT('Insumo "', i.nombre_tecnico, '" usado en "', p.nombre, '" está agotado o próximo a agotarse'),
    i.stock_actual,
    i.stock_minimo
  FROM detalle_formulacion df
  INNER JOIN formulaciones f ON f.id = df.formulacion_id
  INNER JOIN productos p ON p.id = f.producto_id
  INNER JOIN insumos i ON i.id = df.insumo_id
  WHERE f.es_activa = TRUE
  AND p.estatus = 'Activo'
  AND df.tipo_componente = 'Insumo'
  AND i.stock_actual <= i.stock_minimo
  AND NOT EXISTS (
    SELECT 1 FROM alertas_stock a 
    WHERE a.insumo_id = i.id 
    AND a.formulacion_id = f.id
    AND a.resuelta = FALSE
  );
END$$
DELIMITER ;
-- =====================================================
-- ÍNDICES ADICIONALES para optimización
-- =====================================================
CREATE INDEX idx_productos_stock_bajo ON productos(stock_actual, stock_minimo);
CREATE INDEX idx_productos_tipo_estatus ON productos(tipo_producto, estatus);
CREATE INDEX idx_formulaciones_producto_activa ON formulaciones(producto_id, es_activa);
CREATE INDEX idx_movimientos_fecha_tipo ON movimientos_productos(fecha_movimiento, tipo_movimiento);
CREATE INDEX idx_alertas_no_resueltas ON alertas_stock(resuelta, nivel_alerta);