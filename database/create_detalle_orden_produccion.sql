-- Crear tabla detalle_orden_produccion para múltiples productos por orden

CREATE TABLE IF NOT EXISTS detalle_orden_produccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_produccion_id INT NOT NULL,
    producto_id INT NOT NULL,
    formulacion_id INT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    unidad_medida VARCHAR(20) DEFAULT 'kg',
    completado BOOLEAN DEFAULT FALSE,
    fecha_completado DATETIME NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orden (orden_produccion_id),
    INDEX idx_producto (producto_id),
    INDEX idx_completado (completado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalle de productos en órdenes de producción';

-- Verificar que se creó correctamente
SELECT 'Tabla detalle_orden_produccion creada correctamente' AS Resultado;

DESCRIBE detalle_orden_produccion;
