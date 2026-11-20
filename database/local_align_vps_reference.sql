-- Alineación LOCAL del esquema respecto al volcado de referencia VPS (solo datos).
-- No modifica el servidor remoto. Ejecutar antes de importar /tmp/chisa-dump/vps_from_pma.sql.gz

-- CRM ventas: clientes
ALTER TABLE clientes
  ADD COLUMN IF NOT EXISTS tipo_contacto ENUM('Cliente','Prospecto') NOT NULL DEFAULT 'Cliente'
    AFTER tipo_cliente;

-- Descuentos: columna nullable al inicio (id autoincrement en VPS)
ALTER TABLE descuentos
  ADD COLUMN IF NOT EXISTS aplicacion_automatica TINYINT(1) NULL DEFAULT NULL
    COMMENT 'Reservado / alineación con entrenamiento' AFTER id;

-- Productos: alias (si faltaba en seed antiguo)
SET @has_alias := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'productos' AND column_name = 'alias'
);
SET @sql := IF(@has_alias = 0,
  'ALTER TABLE productos ADD COLUMN alias VARCHAR(200) NULL COMMENT ''Nombre alternativo'' AFTER nombre',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Productos: columnas extra en entrenamiento (valores 0/0 en volcado VPS)
ALTER TABLE productos
  ADD COLUMN IF NOT EXISTS requiere_lote TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Alineación volcado VPS' AFTER fecha_modificacion,
  ADD COLUMN IF NOT EXISTS permitir_venta_sin_stock TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Alineación volcado VPS' AFTER requiere_lote;

-- Movimientos inventario: la migración database/movimientos_inventario.sql deja 8 columnas;
-- el entrenamiento/VPS usa la estructura del seed demo (15 columnas).
DROP TABLE IF EXISTS movimientos_inventario;
CREATE TABLE movimientos_inventario (
  id INT(11) NOT NULL AUTO_INCREMENT,
  insumo_id INT(11) DEFAULT NULL,
  producto_id INT(11) DEFAULT NULL,
  tipo_movimiento ENUM('Entrada','Salida','Ajuste','Produccion','Merma','Devolución') NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  stock_anterior DECIMAL(10,2) DEFAULT NULL,
  stock_nuevo DECIMAL(10,2) DEFAULT NULL,
  costo_unitario DECIMAL(10,2) DEFAULT NULL,
  costo_total DECIMAL(12,2) DEFAULT NULL,
  motivo VARCHAR(255) DEFAULT NULL,
  referencia VARCHAR(100) DEFAULT NULL COMMENT 'Número de orden, producción, etc.',
  orden_compra_id INT(11) DEFAULT NULL,
  orden_produccion_id INT(11) DEFAULT NULL,
  fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_producto (producto_id),
  KEY idx_insumo (insumo_id),
  KEY idx_fecha (fecha_movimiento),
  KEY idx_tipo (tipo_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Movimientos de inventario (alineado entrenamiento)';

-- Pagos OC: columnas extra en volcado
ALTER TABLE pagos_ordenes_compra
  ADD COLUMN IF NOT EXISTS comprobante_ruta VARCHAR(255) NULL AFTER notas,
  ADD COLUMN IF NOT EXISTS comprobante_nombre VARCHAR(255) NULL AFTER comprobante_ruta;

-- Empleados cuentas: columnas VPS
ALTER TABLE empleados_cuentas_bancarias
  ADD COLUMN IF NOT EXISTS fecha_modificacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP AFTER fecha_creacion,
  ADD COLUMN IF NOT EXISTS usuario_modificacion INT(11) NULL AFTER usuario_creacion;

-- Obras / OV: columnas frecuentes en entrenamiento
ALTER TABLE obras
  ADD COLUMN IF NOT EXISTS fecha_completado_produccion DATETIME NULL;

ALTER TABLE ordenes_venta
  ADD COLUMN IF NOT EXISTS obra_id INT(11) NULL COMMENT 'Obra vinculada' AFTER cliente_id,
  ADD COLUMN IF NOT EXISTS fecha_completado_produccion DATETIME NULL;

-- Insumos: columnas adicionales (aprox. volcado VPS)
ALTER TABLE insumos
  ADD COLUMN IF NOT EXISTS proveedor_preferido_id INT(11) NULL,
  ADD COLUMN IF NOT EXISTS ubicacion_almacen VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS ultimo_costo_compra DECIMAL(12,4) NULL;