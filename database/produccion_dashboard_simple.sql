-- Script para módulo de producción con dashboard táctil
-- Versión sin foreign keys para evitar errores de dependencias

-- 1. Tabla para lotes de producción (productos terminados)
CREATE TABLE IF NOT EXISTS lotes_produccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_barras VARCHAR(50) UNIQUE NOT NULL,
    orden_produccion_id INT NOT NULL,
    producto_id INT NOT NULL,
    formulacion_id INT NULL,
    formulacion_version VARCHAR(50) NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(20) DEFAULT 'kg',
    fecha_produccion DATETIME NOT NULL,
    fecha_vencimiento DATE NULL,
    usuario_id INT NULL COMMENT 'Usuario que produjo el lote',
    estatus ENUM('Producido', 'En Almacén', 'Despachado', 'Merma') DEFAULT 'Producido',
    ubicacion_almacen VARCHAR(100) NULL COMMENT 'Ubicación física en almacén',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo_barras (codigo_barras),
    INDEX idx_orden (orden_produccion_id),
    INDEX idx_producto (producto_id),
    INDEX idx_estatus (estatus),
    INDEX idx_fecha (fecha_produccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lotes de productos terminados con códigos de barras para trazabilidad';

-- 2. Tabla para historial de cambios de estatus en producción
CREATE TABLE IF NOT EXISTS produccion_historial_estatus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_produccion_id INT NOT NULL,
    estatus_anterior VARCHAR(50) NULL,
    estatus_nuevo VARCHAR(50) NOT NULL,
    usuario_id INT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT NULL,
    INDEX idx_orden (orden_produccion_id),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios de estatus en órdenes de producción';

-- 3. Tabla para movimientos de lotes (trazabilidad)
CREATE TABLE IF NOT EXISTS lotes_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_id INT NOT NULL,
    tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste', 'Merma') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    cantidad_anterior DECIMAL(10,2) NOT NULL,
    cantidad_nueva DECIMAL(10,2) NOT NULL,
    motivo VARCHAR(255) NULL,
    usuario_id INT NULL,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    documento_referencia VARCHAR(100) NULL COMMENT 'Folio de orden de venta, ajuste, etc',
    INDEX idx_lote (lote_id),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_fecha (fecha_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Movimientos de lotes para trazabilidad completa';

-- 4. Stored Procedure para generar código de barras único
DELIMITER //

DROP PROCEDURE IF EXISTS sp_generar_codigo_barras//

CREATE PROCEDURE sp_generar_codigo_barras(
    IN p_producto_id INT,
    OUT p_codigo VARCHAR(50)
)
BEGIN
    DECLARE v_contador INT;
    DECLARE v_fecha VARCHAR(8);
    DECLARE v_random INT;
    DECLARE v_codigo_temp VARCHAR(50);
    DECLARE v_existe INT;
    
    -- Formato de fecha: YYYYMMDD
    SET v_fecha = DATE_FORMAT(NOW(), '%Y%m%d');
    
    -- Intentar generar código único (máximo 10 intentos)
    SET v_contador = 0;
    SET v_existe = 1;
    
    WHILE v_existe = 1 AND v_contador < 10 DO
        -- Generar número aleatorio de 4 dígitos
        SET v_random = FLOOR(1000 + RAND() * 9000);
        
        -- Construir código: PROD-YYYYMMDD-PRODUCTOID-RANDOM
        SET v_codigo_temp = CONCAT('PROD-', v_fecha, '-', p_producto_id, '-', v_random);
        
        -- Verificar si existe
        SELECT COUNT(*) INTO v_existe
        FROM lotes_produccion
        WHERE codigo_barras = v_codigo_temp;
        
        SET v_contador = v_contador + 1;
    END WHILE;
    
    -- Si después de 10 intentos sigue existiendo, agregar timestamp
    IF v_existe = 1 THEN
        SET v_codigo_temp = CONCAT('PROD-', v_fecha, '-', p_producto_id, '-', UNIX_TIMESTAMP());
    END IF;
    
    SET p_codigo = v_codigo_temp;
END//

DELIMITER ;

-- Verificación final
SELECT 'Tablas creadas correctamente' AS Resultado;

-- Mostrar estructura de tablas creadas
DESCRIBE lotes_produccion;
DESCRIBE produccion_historial_estatus;
DESCRIBE lotes_movimientos;
