-- Tabla: proveedores
CREATE TABLE IF NOT EXISTS proveedores (
  id INT(11) NOT NULL AUTO_INCREMENT,
  razon_social VARCHAR(255) NOT NULL,
  nombre_comercial VARCHAR(255) NULL,
  rfc VARCHAR(13) NOT NULL,
  tipo_proveedor ENUM('Materia Prima', 'Servicios', 'Materiales', 'Mixto') NOT NULL DEFAULT 'Mixto',
  contacto_principal VARCHAR(150) NULL,
  telefono VARCHAR(20) NULL,
  telefono_alternativo VARCHAR(20) NULL,
  email VARCHAR(150) NULL,
  sitio_web VARCHAR(255) NULL,
  direccion TEXT NULL,
  ciudad VARCHAR(100) NULL,
  estado VARCHAR(100) NULL,
  codigo_postal VARCHAR(10) NULL,
  pais VARCHAR(100) DEFAULT 'México',
  dias_credito INT(3) DEFAULT 0 COMMENT 'Días de crédito otorgados',
  limite_credito DECIMAL(12,2) DEFAULT 0.00,
  cuenta_bancaria VARCHAR(50) NULL,
  banco VARCHAR(100) NULL,
  observaciones TEXT NULL,
  calificacion TINYINT(1) DEFAULT 3 COMMENT '1-5 estrellas',
  estatus ENUM('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  registrado_por INT(11) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_rfc (rfc),
  KEY idx_razon_social (razon_social),
  KEY idx_tipo (tipo_proveedor),
  KEY idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de proveedores';
-- Tabla: categorias_insumos
CREATE TABLE IF NOT EXISTS categorias_insumos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  categoria_padre_id INT(11) NULL COMMENT 'Para categorías jerárquicas',
  tipo ENUM('Materia Prima', 'Material', 'Servicio') NOT NULL,
  descripcion TEXT NULL,
  icono VARCHAR(50) DEFAULT 'fa-box' COMMENT 'Clase FontAwesome',
  orden INT(3) DEFAULT 0,
  estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (id),
  KEY idx_padre (categoria_padre_id),
  KEY idx_tipo (tipo),
  CONSTRAINT fk_categoria_insumo_padre FOREIGN KEY (categoria_padre_id) REFERENCES categorias_insumos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de insumos jerárquicas';
-- Tabla: insumos
CREATE TABLE IF NOT EXISTS insumos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) NOT NULL COMMENT 'SKU o código interno',
  nombre_tecnico VARCHAR(255) NOT NULL COMMENT 'Nombre técnico/real del producto',
  alias VARCHAR(255) NULL COMMENT 'Nombre que usan los trabajadores',
  marca VARCHAR(100) NULL COMMENT 'Marca del insumo',
  descripcion TEXT NULL,
  categoria_id INT(11) NULL,
  unidad_medida ENUM('Kg', 'g', 'mg', 'L', 'mL', 'Pza', 'Cubeta', 'Tambo', 'Galón', 'm²', 'm³', 'Ton', 'Servicio', 'Otro') NOT NULL DEFAULT 'Pza',
  precio_promedio DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio promedio de todos los proveedores',
  stock_minimo DECIMAL(10,2) DEFAULT 0.00,
  stock_actual DECIMAL(10,2) DEFAULT 0.00,
  stock_maximo DECIMAL(10,2) DEFAULT 0.00,
  ubicacion_almacen VARCHAR(100) NULL,
  ficha_tecnica VARCHAR(255) NULL COMMENT 'Ruta al PDF',
  hoja_seguridad VARCHAR(255) NULL COMMENT 'Para químicos peligrosos',
  es_peligroso TINYINT(1) DEFAULT 0 COMMENT 'Químico peligroso',
  requiere_refrigeracion TINYINT(1) DEFAULT 0,
  vida_util_dias INT(4) NULL COMMENT 'Días de vida útil',
  observaciones TEXT NULL,
  imagen VARCHAR(255) NULL,
  estatus ENUM('Activo', 'Inactivo', 'Descontinuado') DEFAULT 'Activo',
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  registrado_por INT(11) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_codigo (codigo),
  KEY idx_nombre_tecnico (nombre_tecnico),
  KEY idx_alias (alias),
  KEY idx_marca (marca),
  KEY idx_categoria (categoria_id),
  KEY idx_estatus (estatus),
  KEY idx_stock_bajo (stock_actual, stock_minimo),
  CONSTRAINT fk_insumo_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_insumos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de insumos para compra';
-- Tabla: proveedor_insumo
CREATE TABLE IF NOT EXISTS proveedor_insumo (
  id INT(11) NOT NULL AUTO_INCREMENT,
  proveedor_id INT(11) NOT NULL,
  insumo_id INT(11) NOT NULL,
  codigo_proveedor VARCHAR(100) NULL COMMENT 'Código/SKU que usa el proveedor',
  nombre_proveedor VARCHAR(255) NULL COMMENT 'Nombre que le da el proveedor',
  precio_compra DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio de este proveedor',
  tiempo_entrega_dias INT(3) DEFAULT 0,
  cantidad_minima DECIMAL(10,2) DEFAULT 1.00,
  es_proveedor_principal TINYINT(1) DEFAULT 0 COMMENT 'Proveedor preferido',
  calidad TINYINT(1) DEFAULT 3 COMMENT '1-5 estrellas',
  ultima_compra DATE NULL,
  observaciones TEXT NULL,
  estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_proveedor_insumo (proveedor_id, insumo_id),
  KEY idx_proveedor (proveedor_id),
  KEY idx_insumo (insumo_id),
  KEY idx_principal (es_proveedor_principal),
  CONSTRAINT fk_pi_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE,
  CONSTRAINT fk_pi_insumo FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación proveedores-insumos con precios';
-- Tabla: contactos_proveedor
CREATE TABLE IF NOT EXISTS contactos_proveedor (
  id INT(11) NOT NULL AUTO_INCREMENT,
  proveedor_id INT(11) NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  puesto VARCHAR(100) NULL,
  telefono VARCHAR(20) NULL,
  email VARCHAR(150) NULL,
  es_principal TINYINT(1) DEFAULT 0,
  observaciones TEXT NULL,
  estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_proveedor (proveedor_id),
  KEY idx_principal (es_principal),
  CONSTRAINT fk_contacto_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contactos de proveedores';
-- ============================================================================
-- MÓDULO DE COMPRAS Y CONTROL DE INVENTARIO
-- ============================================================================
-- Tabla: ordenes_compra
CREATE TABLE IF NOT EXISTS ordenes_compra (
  id INT(11) NOT NULL AUTO_INCREMENT,
  folio VARCHAR(50) NOT NULL COMMENT 'Folio único de la orden',
  proveedor_id INT(11) NOT NULL,
  fecha_orden DATE NOT NULL,
  fecha_entrega_estimada DATE NULL,
  fecha_entrega_real DATE NULL,
  subtotal DECIMAL(12,2) DEFAULT 0.00,
  iva DECIMAL(12,2) DEFAULT 0.00,
  total DECIMAL(12,2) DEFAULT 0.00,
  forma_pago ENUM('Efectivo', 'Transferencia', 'Cheque', 'Crédito') DEFAULT 'Transferencia',
  condiciones_pago VARCHAR(255) NULL COMMENT 'Ej: 50% anticipo, 50% contra entrega',
  observaciones TEXT NULL,
  estatus ENUM('Borrador', 'Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial', 'Recibida', 'Cancelada') DEFAULT 'Borrador',
  creado_por INT(11) NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  aprobado_por INT(11) NULL,
  fecha_aprobacion DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio (folio),
  KEY idx_proveedor (proveedor_id),
  KEY idx_fecha (fecha_orden),
  KEY idx_estatus (estatus),
  CONSTRAINT fk_oc_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de compra a proveedores';
-- Tabla: detalle_orden_compra
CREATE TABLE IF NOT EXISTS detalle_orden_compra (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_compra_id INT(11) NOT NULL,
  insumo_id INT(11) NOT NULL,
  cantidad_solicitada DECIMAL(10,2) NOT NULL,
  cantidad_recibida DECIMAL(10,2) DEFAULT 0.00,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  observaciones TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_orden (orden_compra_id),
  KEY idx_insumo (insumo_id),
  CONSTRAINT fk_doc_orden FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_insumo FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de órdenes de compra';
-- Tabla: movimientos_inventario
CREATE TABLE IF NOT EXISTS movimientos_inventario (
  id INT(11) NOT NULL AUTO_INCREMENT,
  insumo_id INT(11) NOT NULL,
  tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste', 'Merma', 'Devolución') NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  stock_anterior DECIMAL(10,2) NOT NULL,
  stock_nuevo DECIMAL(10,2) NOT NULL,
  costo_unitario DECIMAL(10,2) DEFAULT 0.00,
  costo_total DECIMAL(12,2) DEFAULT 0.00,
  orden_compra_id INT(11) NULL COMMENT 'Si es entrada por compra',
  motivo VARCHAR(255) NULL COMMENT 'Razón del movimiento',
  observaciones TEXT NULL,
  usuario_id INT(11) NULL,
  fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_insumo (insumo_id),
  KEY idx_tipo (tipo_movimiento),
  KEY idx_fecha (fecha_movimiento),
  KEY idx_orden_compra (orden_compra_id),
  CONSTRAINT fk_mov_insumo FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_mov_orden FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de movimientos de inventario';
-- ============================================================================
-- DATOS INICIALES - CATEGORÍAS
-- ============================================================================
INSERT INTO categorias_insumos (nombre, categoria_padre_id, tipo, descripcion, icono, orden) VALUES
-- Categorías raíz
('Materias Primas Químicas', NULL, 'Materia Prima', 'Químicos y componentes para fabricación', 'fa-flask', 1),
('Materiales y Herramientas', NULL, 'Material', 'Materiales de trabajo y herramientas', 'fa-tools', 2),
('Servicios', NULL, 'Servicio', 'Servicios contratados', 'fa-handshake', 3);
-- Subcategorías de Materias Primas
INSERT INTO categorias_insumos (nombre, categoria_padre_id, tipo, descripcion, icono, orden) VALUES
('Resinas', 1, 'Materia Prima', 'Resinas acrílicas, epóxicas, etc.', 'fa-vial', 1),
('Solventes', 1, 'Materia Prima', 'Solventes y diluyentes', 'fa-flask-vial', 2),
('Pigmentos', 1, 'Materia Prima', 'Pigmentos y colorantes', 'fa-palette', 3),
('Aditivos', 1, 'Materia Prima', 'Aditivos y modificadores', 'fa-prescription-bottle', 4),
('Cargas', 1, 'Materia Prima', 'Cargas minerales', 'fa-cubes', 5);
-- Subcategorías de Materiales
INSERT INTO categorias_insumos (nombre, categoria_padre_id, tipo, descripcion, icono, orden) VALUES
('Envases', 2, 'Material', 'Cubetas, tambos, envases', 'fa-bucket', 1),
('Herramientas', 2, 'Material', 'Espátulas, brochas, rodillos', 'fa-screwdriver-wrench', 2),
('Empaques', 2, 'Material', 'Etiquetas, cajas, embalaje', 'fa-box', 3),
('Equipo de Seguridad', 2, 'Material', 'EPP y equipo de protección', 'fa-hard-hat', 4);
-- Subcategorías de Servicios
INSERT INTO categorias_insumos (nombre, categoria_padre_id, tipo, descripcion, icono, orden) VALUES
('Telecomunicaciones', 3, 'Servicio', 'Internet, telefonía', 'fa-wifi', 1),
('Mantenimiento', 3, 'Servicio', 'Mantenimiento de equipos', 'fa-wrench', 2),
('Transporte', 3, 'Servicio', 'Servicios de logística', 'fa-truck', 3),
('Consultoría', 3, 'Servicio', 'Servicios profesionales', 'fa-user-tie', 4);
-- ============================================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================================================
-- Eliminar índices si existen (para evitar errores de duplicados)
DROP INDEX IF EXISTS idx_proveedor_activo ON proveedores;
DROP INDEX IF EXISTS idx_insumo_activo ON insumos;
DROP INDEX IF EXISTS idx_insumo_stock_bajo ON insumos;
DROP INDEX IF EXISTS idx_insumo_busqueda ON insumos;
DROP INDEX IF EXISTS idx_oc_pendiente ON ordenes_compra;
-- Crear índices
CREATE INDEX idx_proveedor_activo ON proveedores(estatus, razon_social);
CREATE INDEX idx_insumo_activo ON insumos(estatus, nombre_tecnico);
CREATE INDEX idx_insumo_stock_bajo ON insumos(stock_actual, stock_minimo);
CREATE INDEX idx_insumo_busqueda ON insumos(nombre_tecnico, alias, marca);
CREATE INDEX idx_oc_pendiente ON ordenes_compra(estatus, fecha_orden);
-- ============================================================================
-- TRIGGERS PARA ACTUALIZACIÓN AUTOMÁTICA DE STOCK
-- ============================================================================
-- Eliminar triggers si existen
DROP TRIGGER IF EXISTS trg_actualizar_stock_movimiento;
DROP TRIGGER IF EXISTS trg_calcular_subtotal_detalle;
DROP TRIGGER IF EXISTS trg_calcular_subtotal_detalle_update;
DROP TRIGGER IF EXISTS trg_actualizar_totales_oc;
DELIMITER $$
-- Trigger: Actualizar stock al registrar movimiento de inventario
CREATE TRIGGER trg_actualizar_stock_movimiento
AFTER INSERT ON movimientos_inventario
FOR EACH ROW
BEGIN
  UPDATE insumos 
  SET stock_actual = NEW.stock_nuevo
  WHERE id = NEW.insumo_id;
END$$
-- Trigger: Calcular subtotal en detalle de orden de compra
CREATE TRIGGER trg_calcular_subtotal_detalle
BEFORE INSERT ON detalle_orden_compra
FOR EACH ROW
BEGIN
  SET NEW.subtotal = NEW.cantidad_solicitada * NEW.precio_unitario;
END$$
CREATE TRIGGER trg_calcular_subtotal_detalle_update
BEFORE UPDATE ON detalle_orden_compra
FOR EACH ROW
BEGIN
  SET NEW.subtotal = NEW.cantidad_solicitada * NEW.precio_unitario;
END$$
-- Trigger: Actualizar totales de orden de compra
CREATE TRIGGER trg_actualizar_totales_oc
AFTER INSERT ON detalle_orden_compra
FOR EACH ROW
BEGIN
  DECLARE v_subtotal DECIMAL(12,2);
  DECLARE v_iva DECIMAL(12,2);
  
  SELECT SUM(subtotal) INTO v_subtotal
  FROM detalle_orden_compra
  WHERE orden_compra_id = NEW.orden_compra_id;
  
  SET v_iva = v_subtotal * 0.16;
  
  UPDATE ordenes_compra
  SET subtotal = v_subtotal,
      iva = v_iva,
      total = v_subtotal + v_iva
  WHERE id = NEW.orden_compra_id;
END$$
DELIMITER ;