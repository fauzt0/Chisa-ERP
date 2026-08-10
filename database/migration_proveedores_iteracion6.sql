-- Iteración 6 — Cotizaciones de compras
-- Ejecutar: php database/migrar_cotizaciones.php

CREATE TABLE IF NOT EXISTS cotizaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(50) NOT NULL COMMENT 'Folio único de la cotización',
    grupo_folio VARCHAR(50) NULL COMMENT 'Agrupa cotizaciones para comparación side-by-side',
    proveedor_id INT NOT NULL,
    fecha_solicitud DATE NOT NULL,
    fecha_respuesta DATE NULL,
    estatus ENUM('Pendiente','Recibida','Rechazada','Aprobada') NOT NULL DEFAULT 'Pendiente',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Soft delete: 0 = inactivo',
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observaciones TEXT NULL,
    archivo_nombre VARCHAR(255) NULL COMMENT 'PDF/cotización del proveedor',
    archivo_ruta VARCHAR(500) NULL,
    orden_compra_id INT NULL COMMENT 'OC generada al aprobar',
    creado_por INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unq_cotizacion_folio (folio),
    KEY idx_cotizacion_proveedor (proveedor_id),
    KEY idx_cotizacion_grupo (grupo_folio),
    KEY idx_cotizacion_estatus (estatus),
    KEY idx_cotizacion_activo (activo),
    CONSTRAINT fk_cotizacion_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores (id),
    CONSTRAINT fk_cotizacion_oc FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cotizaciones de compra a proveedores';

CREATE TABLE IF NOT EXISTS cotizaciones_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT NOT NULL,
    insumo_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    KEY idx_cot_detalle_cotizacion (cotizacion_id),
    KEY idx_cot_detalle_insumo (insumo_id),
    CONSTRAINT fk_cot_detalle_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones (id) ON DELETE CASCADE,
    CONSTRAINT fk_cot_detalle_insumo FOREIGN KEY (insumo_id) REFERENCES insumos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de cotizaciones de compra';
