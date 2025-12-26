-- Script para módulo de producción con dashboard táctil
-- Crea/actualiza tablas necesarias para gestión de producción y códigos de barras

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
    FOREIGN KEY (orden_produccion_id) REFERENCES ordenes_produccion(id) ON DELETE RESTRICT,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE SET NULL,
    INDEX idx_codigo_barras (codigo_barras),
    INDEX idx_orden (orden_produccion_id),
    INDEX idx_producto (producto_id),
    INDEX idx_estatus (estatus),
    INDEX idx_fecha (fecha_produccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lotes de productos terminados con códigos de barras para trazabilidad';

-- 2. Verificar y agregar campos a ordenes_produccion si no existen
-- Agregar fecha_inicio si no existe
SET @dbname = DATABASE();
SET @tablename = 'ordenes_produccion';
SET @columnname = 'fecha_inicio';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_produccion ADD COLUMN fecha_inicio DATETIME NULL COMMENT "Fecha cuando se inicia la producción"'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar fecha_completado si no existe
SET @columnname = 'fecha_completado';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_produccion ADD COLUMN fecha_completado DATETIME NULL COMMENT "Fecha cuando se completa la producción"'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar usuario_asignado si no existe
SET @columnname = 'usuario_asignado';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_produccion ADD COLUMN usuario_asignado INT NULL COMMENT "Usuario responsable de la producción"'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Tabla para historial de cambios de estatus en producción
CREATE TABLE IF NOT EXISTS produccion_historial_estatus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_produccion_id INT NOT NULL,
    estatus_anterior VARCHAR(50) NULL,
    estatus_nuevo VARCHAR(50) NOT NULL,
    usuario_id INT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT NULL,
    FOREIGN KEY (orden_produccion_id) REFERENCES ordenes_produccion(id) ON DELETE CASCADE,
    INDEX idx_orden (orden_produccion_id),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios de estatus en órdenes de producción';

-- 4. Tabla para movimientos de lotes (trazabilidad)
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
    FOREIGN KEY (lote_id) REFERENCES lotes_produccion(id) ON DELETE CASCADE,
    INDEX idx_lote (lote_id),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_fecha (fecha_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Movimientos de lotes para trazabilidad completa';

-- 5. Vista para dashboard de producción (órdenes con detalles)
CREATE OR REPLACE VIEW v_dashboard_produccion AS
SELECT 
    op.id,
    op.folio,
    op.orden_venta_id,
    ov.folio as orden_venta_folio,
    op.fecha_orden,
    op.fecha_requerida,
    op.fecha_inicio,
    op.fecha_completado,
    op.estatus,
    op.prioridad,
    op.usuario_asignado,
    c.razon_social as cliente,
    c.nombre_comercial,
    COUNT(DISTINCT dop.producto_id) as total_productos,
    SUM(dop.cantidad) as cantidad_total,
    GROUP_CONCAT(DISTINCT p.nombre SEPARATOR ', ') as productos,
    CASE 
        WHEN op.estatus = 'Pendiente' THEN 1
        WHEN op.estatus = 'En Proceso' THEN 2
        WHEN op.estatus = 'Completada' THEN 3
        ELSE 4
    END as orden_prioridad
FROM ordenes_produccion op
LEFT JOIN ordenes_venta ov ON ov.id = op.orden_venta_id
LEFT JOIN clientes c ON c.id = ov.cliente_id
LEFT JOIN detalle_orden_produccion dop ON dop.orden_produccion_id = op.id
LEFT JOIN productos p ON p.id = dop.producto_id
GROUP BY op.id, op.folio, op.orden_venta_id, ov.folio, op.fecha_orden, 
         op.fecha_requerida, op.fecha_inicio, op.fecha_completado, 
         op.estatus, op.prioridad, op.usuario_asignado, c.razon_social, c.nombre_comercial
ORDER BY orden_prioridad ASC, op.fecha_requerida ASC;

-- 6. Stored Procedure para generar código de barras único
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

-- 7. Trigger para registrar cambios de estatus automáticamente
DELIMITER //

DROP TRIGGER IF EXISTS tr_produccion_cambio_estatus//

CREATE TRIGGER tr_produccion_cambio_estatus
AFTER UPDATE ON ordenes_produccion
FOR EACH ROW
BEGIN
    -- Solo registrar si cambió el estatus
    IF OLD.estatus != NEW.estatus THEN
        INSERT INTO produccion_historial_estatus (
            orden_produccion_id,
            estatus_anterior,
            estatus_nuevo,
            observaciones
        ) VALUES (
            NEW.id,
            OLD.estatus,
            NEW.estatus,
            CONCAT('Cambio automático de estatus de ', OLD.estatus, ' a ', NEW.estatus)
        );
    END IF;
    
    -- Actualizar fecha_inicio si pasa a "En Proceso"
    IF NEW.estatus = 'En Proceso' AND OLD.estatus != 'En Proceso' AND NEW.fecha_inicio IS NULL THEN
        UPDATE ordenes_produccion 
        SET fecha_inicio = NOW() 
        WHERE id = NEW.id;
    END IF;
    
    -- Actualizar fecha_completado si pasa a "Completada"
    IF NEW.estatus = 'Completada' AND OLD.estatus != 'Completada' AND NEW.fecha_completado IS NULL THEN
        UPDATE ordenes_produccion 
        SET fecha_completado = NOW() 
        WHERE id = NEW.id;
    END IF;
END//

DELIMITER ;

-- Verificación final
SELECT 'Tablas creadas correctamente' AS Resultado;

-- Mostrar estructura de tablas creadas
DESCRIBE lotes_produccion;
DESCRIBE produccion_historial_estatus;
DESCRIBE lotes_movimientos;

-- Mostrar vista creada
SELECT COUNT(*) as total_ordenes FROM v_dashboard_produccion;
