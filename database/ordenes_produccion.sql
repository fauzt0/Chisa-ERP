-- =====================================================
-- MÓDULO DE PRODUCCIÓN
-- =====================================================

-- Tabla: ordenes_produccion
CREATE TABLE IF NOT EXISTS ordenes_produccion (
  id INT(11) NOT NULL AUTO_INCREMENT,
  folio VARCHAR(50) NOT NULL UNIQUE COMMENT 'OP-2025-0001',
  
  -- Producto a fabricar
  producto_id INT(11) NOT NULL,
  formulacion_id INT(11) NOT NULL COMMENT 'Versión de formulación usada',
  
  -- Cantidades
  cantidad_programada DECIMAL(10,2) NOT NULL,
  cantidad_producida DECIMAL(10,2) DEFAULT 0.00,
  unidad_medida ENUM('L', 'ml', 'Kg', 'g', 'Pza') DEFAULT 'L',
  
  -- Fechas
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_inicio DATETIME NULL,
  fecha_fin DATETIME NULL,
  
  -- Costos (histórico al momento de producir)
  costo_unitario_estimado DECIMAL(10,2) DEFAULT 0.00,
  costo_total_estimado DECIMAL(10,2) DEFAULT 0.00,
  costo_real DECIMAL(10,2) DEFAULT 0.00,
  
  -- Estatus
  estatus ENUM('Pendiente', 'En Proceso', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
  
  -- Auditoría
  creado_por INT(11) NULL,
  iniciado_por INT(11) NULL,
  finalizado_por INT(11) NULL,
  observaciones TEXT NULL,
  
  PRIMARY KEY (id),
  UNIQUE KEY uk_folio (folio),
  KEY idx_producto (producto_id),
  KEY idx_formulacion (formulacion_id),
  KEY idx_estatus (estatus),
  KEY idx_fechas (fecha_inicio, fecha_fin),
  CONSTRAINT fk_op_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_op_formulacion FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de producción';

-- Modificar tabla solicitudes_produccion para vincular con OP
ALTER TABLE solicitudes_produccion
ADD COLUMN orden_produccion_id INT(11) NULL AFTER estatus,
ADD KEY idx_orden_prod (orden_produccion_id),
ADD CONSTRAINT fk_sp_orden_prod FOREIGN KEY (orden_produccion_id) REFERENCES ordenes_produccion(id) ON DELETE SET NULL;

-- Stored Procedure: Generar folio OP
DELIMITER $$
CREATE PROCEDURE sp_generar_folio_op(OUT nuevo_folio VARCHAR(50))
BEGIN
    DECLARE ultimo_numero INT DEFAULT 0;
    DECLARE anio_actual VARCHAR(4);
    
    SET anio_actual = YEAR(CURDATE());
    
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)), 0) 
    INTO ultimo_numero
    FROM ordenes_produccion
    WHERE folio LIKE CONCAT('OP-', anio_actual, '-%');
    
    SET nuevo_folio = CONCAT('OP-', anio_actual, '-', LPAD(ultimo_numero + 1, 4, '0'));
END$$
DELIMITER ;

-- Trigger: Actualizar estatus de solicitudes cuando se asignan a una OP
DELIMITER $$
CREATE TRIGGER tr_op_actualizar_solicitudes
AFTER UPDATE ON ordenes_produccion
FOR EACH ROW
BEGIN
    IF NEW.estatus = 'En Proceso' THEN
        UPDATE solicitudes_produccion 
        SET estatus = 'En Proceso', fecha_inicio_produccion = NOW()
        WHERE orden_produccion_id = NEW.id;
    ELSEIF NEW.estatus = 'Completada' THEN
        UPDATE solicitudes_produccion 
        SET estatus = 'Completada', fecha_fin_produccion = NOW(), cantidad_producida = cantidad_solicitada
        WHERE orden_produccion_id = NEW.id;
    ELSEIF NEW.estatus = 'Cancelada' THEN
        UPDATE solicitudes_produccion 
        SET estatus = 'Pendiente', orden_produccion_id = NULL
        WHERE orden_produccion_id = NEW.id;
    END IF;
END$$
DELIMITER ;
