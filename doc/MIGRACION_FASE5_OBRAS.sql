-- ============================================================================
-- MIGRACIÓN FASE 5 · OBRAS Y PRESUPUESTOS (V1)
-- ERP CHISA/HAPA · CodeIgniter 3 · MySQL/MariaDB
-- Idempotente: CREATE TABLE IF NOT EXISTS / ALTER ADD COLUMN condicionado.
-- PROHIBIDO DROP. Convenciones: decimal(12,2)/(10,2), current_timestamp(),
-- ON UPDATE current_timestamp(), activo tinyint(1) DEFAULT 1, índices *_id,
-- utf8mb4_unicode_ci, COMMENT en español.
-- ============================================================================

-- A1 · conceptos_obra --------------------------------------------------------
CREATE TABLE IF NOT EXISTS conceptos_obra (
    id INT(11) NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(40) NOT NULL,
    descripcion TEXT NULL,
    unidad ENUM('M2','ML','M3','PZA','LOTE','SERVICIO') NOT NULL DEFAULT 'M2',
    tipo ENUM('Suministro','Aplicacion','Suministro y aplicacion','Otro') NOT NULL DEFAULT 'Suministro y aplicacion',
    seccion VARCHAR(60) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_por INT(11) NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_conceptos_codigo (codigo),
    KEY idx_conceptos_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Conceptos de obra (partidas) con código, unidad y tipo';

-- A2 · presupuestos_obra -----------------------------------------------------
CREATE TABLE IF NOT EXISTS presupuestos_obra (
    id INT(11) NOT NULL AUTO_INCREMENT,
    folio VARCHAR(50) NOT NULL,
    obra_id INT(11) NULL,
    cliente_id INT(11) NULL,
    sucursal_id INT(11) NULL,
    tipo ENUM('Presupuesto','Cotizacion') NOT NULL DEFAULT 'Presupuesto',
    version DECIMAL(3,1) NOT NULL DEFAULT 1.0,
    fecha DATE NULL,
    validez_dias INT(11) NOT NULL DEFAULT 15,
    pres_ref VARCHAR(60) NULL,
    atencion VARCHAR(150) NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    descuento_monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    iva_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 16.00,
    iva_monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estatus ENUM('Borrador','Enviado','Aprobado','Rechazado') NOT NULL DEFAULT 'Borrador',
    condiciones_pago TEXT NULL,
    notas_legales TEXT NULL,
    orden_venta_id INT(11) NULL,
    creado_por INT(11) NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uk_presupuestos_folio (folio),
    KEY idx_presupuestos_obra (obra_id),
    KEY idx_presupuestos_cliente (cliente_id),
    KEY idx_presupuestos_sucursal (sucursal_id),
    KEY idx_presupuestos_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Presupuestos y cotizaciones de venta (entidad nueva, no cotizaciones de compras)';

-- A3 · presupuesto_obra_conceptos -------------------------------------------
CREATE TABLE IF NOT EXISTS presupuesto_obra_conceptos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    presupuesto_id INT(11) NOT NULL,
    concepto_id INT(11) NULL,
    codigo VARCHAR(40) NULL,
    descripcion TEXT NULL,
    unidad VARCHAR(20) NULL,
    cantidad DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    importe DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    seccion VARCHAR(60) NULL,
    fase VARCHAR(60) NULL,
    generador_id INT(11) NULL,
    orden INT(11) NOT NULL DEFAULT 0,
    notas TEXT NULL,
    PRIMARY KEY (id),
    KEY idx_poc_presupuesto (presupuesto_id),
    KEY idx_poc_concepto (concepto_id),
    KEY idx_poc_generador (generador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Partidas (conceptos) de un presupuesto de obra';

-- A4 · concepto_apu_materiales ----------------------------------------------
CREATE TABLE IF NOT EXISTS concepto_apu_materiales (
    id INT(11) NOT NULL AUTO_INCREMENT,
    concepto_id INT(11) NOT NULL,
    insumo_id INT(11) NULL,
    producto_id INT(11) NULL,
    descripcion_libre VARCHAR(255) NULL,
    unidad VARCHAR(20) NULL,
    cantidad DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    importe DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    origen ENUM('formulacion','manual') NOT NULL DEFAULT 'manual',
    orden INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_apum_concepto (concepto_id),
    KEY idx_apum_insumo (insumo_id),
    KEY idx_apum_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Materiales del APU de un concepto de obra';

-- A5 · concepto_apu_cuadrillas ----------------------------------------------
CREATE TABLE IF NOT EXISTS concepto_apu_cuadrillas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    concepto_id INT(11) NOT NULL,
    categoria VARCHAR(120) NULL,
    unidad VARCHAR(20) NOT NULL DEFAULT 'JOR',
    salario_semanal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cant_cuadrilla DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    rendimiento_jor DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
    salario_jor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    KEY idx_apuc_concepto (concepto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cuadrillas (mano de obra) del APU de un concepto';

-- A6 · parametros_apu -------------------------------------------------------
CREATE TABLE IF NOT EXISTS parametros_apu (
    id INT(11) NOT NULL AUTO_INCREMENT,
    imss DECIMAL(6,3) NOT NULL DEFAULT 32.792,
    rcyv DECIMAL(6,3) NOT NULL DEFAULT 27.208,
    isn DECIMAL(6,3) NOT NULL DEFAULT 3.000,
    herramienta DECIMAL(6,3) NOT NULL DEFAULT 9.000,
    indirecto_utilidad DECIMAL(6,3) NOT NULL DEFAULT 24.000,
    vigente_desde DATE NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_parametros_apu_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Factores parametrizados del APU (NO hardcodear)';

-- A7 · obra_generadores -----------------------------------------------------
CREATE TABLE IF NOT EXISTS obra_generadores (
    id INT(11) NOT NULL AUTO_INCREMENT,
    obra_id INT(11) NOT NULL,
    concepto_id INT(11) NULL,
    hoja_no INT(11) NOT NULL DEFAULT 1,
    hoja_de INT(11) NOT NULL DEFAULT 1,
    periodo_inicio DATE NULL,
    periodo_fin DATE NULL,
    concepto_texto TEXT NULL,
    ubicacion VARCHAR(180) NULL,
    suma_hoja DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    acumulado_anterior DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estatus ENUM('Borrador','Aplicado') NOT NULL DEFAULT 'Borrador',
    fecha DATE NULL,
    PRIMARY KEY (id),
    KEY idx_generadores_obra (obra_id),
    KEY idx_generadores_concepto (concepto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Generadores de cuantificación por obra';

CREATE TABLE IF NOT EXISTS obra_generador_lineas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    generador_id INT(11) NOT NULL,
    bloque TINYINT(4) NOT NULL DEFAULT 1,
    pzas DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    n INT(11) NOT NULL DEFAULT 0,
    largo DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    alto DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    area DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    descuento DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    simbolo ENUM('','P','CV','HM','BOQH','BOQV','C','V','GE','VA','O') NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY idx_generador_lineas_generador (generador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Líneas de medición (bloques 1..3) de un generador';

-- A8 · obra_revisiones_cuantificacion ---------------------------------------
CREATE TABLE IF NOT EXISTS obra_revisiones_cuantificacion (
    id INT(11) NOT NULL AUTO_INCREMENT,
    obra_id INT(11) NOT NULL,
    concepto_id INT(11) NULL,
    descripcion TEXT NULL,
    total_cuantificado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_cotizado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    diferencia DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    diferencia_pct DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    comentario TEXT NULL,
    estatus ENUM('OK','Aclarar','Pendiente') NOT NULL DEFAULT 'Pendiente',
    revisado_por INT(11) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_revisiones_obra (obra_id),
    KEY idx_revisiones_concepto (concepto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Revisión de cuantificación: total cuantificado vs cotizado';

-- A10 · precios_insumo_historial --------------------------------------------
CREATE TABLE IF NOT EXISTS precios_insumo_historial (
    id INT(11) NOT NULL AUTO_INCREMENT,
    insumo_id INT(11) NULL,
    precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fecha DATE NULL,
    proveedor_id INT(11) NULL,
    origen ENUM('compra','captura','PRECIO_ACT') NOT NULL DEFAULT 'captura',
    notas VARCHAR(255) NULL,
    PRIMARY KEY (id),
    KEY idx_ph_insumo (insumo_id),
    KEY idx_ph_proveedor (proveedor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de precios de insumos (PRECIO_ACT)';

-- A9 · ALTERACIONES mínimas (idempotentes) ----------------------------------
-- obras.sucursal_id
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'obras' AND COLUMN_NAME = 'sucursal_id');
SET @sql := IF(@col = 0,
    'ALTER TABLE obras ADD COLUMN sucursal_id INT(11) NULL DEFAULT NULL AFTER cliente_id, ADD KEY idx_obras_sucursal (sucursal_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sucursales.logo_marca_agua
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sucursales' AND COLUMN_NAME = 'logo_marca_agua');
SET @sql := IF(@col = 0,
    'ALTER TABLE sucursales ADD COLUMN logo_marca_agua VARCHAR(255) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sucursales.texto_marca_agua
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sucursales' AND COLUMN_NAME = 'texto_marca_agua');
SET @sql := IF(@col = 0,
    'ALTER TABLE sucursales ADD COLUMN texto_marca_agua VARCHAR(120) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- A6 seed: valores observados en los archivos fuente (ZOCLOS → P.UNITARIO 306.66)
INSERT INTO parametros_apu (imss, rcyv, isn, herramienta, indirecto_utilidad, vigente_desde, activo)
SELECT 32.792, 27.208, 3.000, 9.000, 24.000, CURDATE(), 1
WHERE NOT EXISTS (SELECT 1 FROM parametros_apu WHERE activo = 1);

