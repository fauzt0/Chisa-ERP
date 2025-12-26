-- Actualizar tabla clientes con campos de facturación adicionales
ALTER TABLE clientes
ADD COLUMN uso_cfdi VARCHAR(5) NULL COMMENT 'Clave SAT: G03, G01, etc.' AFTER regimen_fiscal,
ADD COLUMN email_facturacion VARCHAR(100) NULL AFTER email;

-- Tabla para simulación de facturas (vinculada a orden de venta)
CREATE TABLE IF NOT EXISTS facturas (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_venta_id INT(11) NOT NULL,
  cliente_id INT(11) NOT NULL,
  
  -- Datos Fiscales (Snapshot del momento)
  rfc VARCHAR(13) NOT NULL,
  razon_social VARCHAR(200) NOT NULL,
  regimen_fiscal VARCHAR(100) NOT NULL,
  uso_cfdi VARCHAR(5) NOT NULL,
  codigo_postal VARCHAR(10) NOT NULL,
  
  -- Timbrado (Simulado)
  folio_fiscal VARCHAR(36) NOT NULL COMMENT 'UUID simulado',
  serie VARCHAR(10) DEFAULT 'A',
  folio VARCHAR(50) NOT NULL,
  fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  -- Totales
  subtotal DECIMAL(10,2) NOT NULL,
  iva DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  
  estatus ENUM('Emitida', 'Cancelada') DEFAULT 'Emitida',
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio_fiscal (folio_fiscal),
  KEY idx_orden (orden_venta_id),
  KEY idx_cliente (cliente_id),
  CONSTRAINT fk_factura_orden FOREIGN KEY (orden_venta_id) REFERENCES ordenes_venta(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de facturas emitidas';
