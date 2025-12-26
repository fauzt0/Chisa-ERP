-- Verificar si la tabla existe y eliminarla para recrearla con la estructura correcta
DROP TABLE IF EXISTS movimientos_inventario;

-- Crear tabla de movimientos de inventario con la estructura correcta
CREATE TABLE movimientos_inventario (
  id INT(11) NOT NULL AUTO_INCREMENT,
  producto_id INT(11) NOT NULL,
  tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste') NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  motivo VARCHAR(255) NULL,
  referencia VARCHAR(100) NULL COMMENT 'Número de orden, producción, etc.',
  fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT(11) NULL,
  
  PRIMARY KEY (id),
  KEY idx_producto (producto_id),
  KEY idx_fecha (fecha_movimiento),
  KEY idx_tipo (tipo_movimiento),
  CONSTRAINT fk_mi_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';
