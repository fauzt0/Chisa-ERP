-- ============================================================================
-- MIGRACIÓN FASE 2: Formulaciones, Pre-órdenes e Importador Excel
-- ERP Chisa Recubrimientos
-- FECHA: 2026-06-28
-- EJECUTAR EN: Base de datos de producción CHISA ERP
-- ORDEN DE EJECUCIÓN: Este archivo es autocontenido, ejecutar completo.
-- ============================================================================

-- ============================================================================
-- BLOQUE 1: AMPLIAR TABLA formulaciones
-- Agrega soporte para: cliente específico, variante de color, rendimiento m²,
-- referencia de pedido y comentarios (requeridos por importador Excel y módulo obras)
-- ============================================================================

ALTER TABLE `formulaciones`
  ADD COLUMN IF NOT EXISTS `cliente_id` INT(11) NULL DEFAULT NULL
    COMMENT 'Cliente al que pertenece esta formulación específica (NULL = genérica)'
    AFTER `es_activa`,

  ADD COLUMN IF NOT EXISTS `variante_descripcion` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Descripción de variante, ej: REF. 128 MICRO, 125 MICRO'
    AFTER `cliente_id`,

  ADD COLUMN IF NOT EXISTS `rendimiento_m2_por_kg` DECIMAL(10,4) NULL DEFAULT NULL
    COMMENT 'Cuántos m² cubre 1 kg de este producto. Permite calcular material para obra.'
    AFTER `variante_descripcion`,

  ADD COLUMN IF NOT EXISTS `cantidad_cubetas_ref` DECIMAL(10,3) NULL DEFAULT NULL
    COMMENT 'Cantidad de cubetas de referencia del pedido original (de la etiqueta del Excel)'
    AFTER `rendimiento_m2_por_kg`,

  ADD COLUMN IF NOT EXISTS `referencia_cliente` VARCHAR(200) NULL DEFAULT NULL
    COMMENT 'Etiqueta original del Excel, ej: 1 CUBETA VENTA DAVID, 3 CUBETAS PEDIATRIA'
    AFTER `cantidad_cubetas_ref`,

  ADD COLUMN IF NOT EXISTS `comentarios` TEXT NULL DEFAULT NULL
    COMMENT 'Notas o ajustes especiales de esta formulación'
    AFTER `referencia_cliente`;

-- Índice para búsqueda por cliente
CREATE INDEX IF NOT EXISTS `idx_formulaciones_cliente`
  ON `formulaciones` (`cliente_id`);

-- ============================================================================
-- BLOQUE 2: AMPLIAR TABLA detalle_formulacion
-- Agrega soporte para: grupos de color (COLOR CAFÉ, COLOR AZUL…),
-- datos de Fase Acuosa y orden explícito de mezclado
-- ============================================================================

ALTER TABLE `detalle_formulacion`
  ADD COLUMN IF NOT EXISTS `grupo_color` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Nombre del grupo de color al que pertenece este insumo, ej: COLOR CAFÉ, COLOR CREMA'
    AFTER `orden`,

  ADD COLUMN IF NOT EXISTS `porcentaje_fase_acuosa` DECIMAL(8,4) NULL DEFAULT NULL
    COMMENT '% que representa la Fase Acuosa de este insumo dentro del total de agua'
    AFTER `grupo_color`,

  ADD COLUMN IF NOT EXISTS `kg_fase_acuosa` DECIMAL(10,4) NULL DEFAULT NULL
    COMMENT 'kg correspondientes a la Fase Acuosa de este insumo'
    AFTER `porcentaje_fase_acuosa`,

  ADD COLUMN IF NOT EXISTS `porcentaje_agua` DECIMAL(8,4) NULL DEFAULT NULL
    COMMENT '% de agua dentro de la Fase Acuosa (normalmente 45%)'
    AFTER `kg_fase_acuosa`,

  ADD COLUMN IF NOT EXISTS `kg_agua` DECIMAL(10,4) NULL DEFAULT NULL
    COMMENT 'kg de agua resultantes (kg_fase_acuosa * porcentaje_agua / 100)'
    AFTER `porcentaje_agua`;

-- Índice para agrupar y filtrar por color
CREATE INDEX IF NOT EXISTS `idx_detalle_grupo_color`
  ON `detalle_formulacion` (`grupo_color`);

-- ============================================================================
-- BLOQUE 3: TABLA preordenes
-- Flujo: Producción detecta falta de insumos → genera pre-orden →
-- Proveedores la valida → convierte a Orden de Compra
-- ============================================================================

CREATE TABLE IF NOT EXISTS `preordenes` (
  `id`                   INT(11)      NOT NULL AUTO_INCREMENT,
  `folio`                VARCHAR(30)  NOT NULL COMMENT 'Ej: PRE-2026-0001',

  -- Origen de la solicitud
  `origen_tipo`          ENUM('venta','obra','interno') NOT NULL DEFAULT 'interno'
    COMMENT 'Qué generó la necesidad',
  `origen_id`            INT(11)      NULL DEFAULT NULL
    COMMENT 'ID de la orden_venta u obra que originó la pre-orden',

  -- Insumo solicitado
  `insumo_id`            INT(11)      NOT NULL,
  `cantidad_solicitada`  DECIMAL(10,3) NOT NULL,
  `unidad`               ENUM('Kg','L','g','ml','Pza') NOT NULL DEFAULT 'Kg',

  -- Proveedor sugerido (opcional, se puede dejar a criterio de compras)
  `proveedor_sugerido_id` INT(11)     NULL DEFAULT NULL,

  -- Validación por compras
  `cantidad_aprobada`    DECIMAL(10,3) NULL DEFAULT NULL,
  `estatus`              ENUM('Pendiente','Aprobada','Rechazada','Convertida','Cancelada')
                           NOT NULL DEFAULT 'Pendiente',
  `motivo_rechazo`       TEXT NULL DEFAULT NULL,
  `orden_compra_id`      INT(11)      NULL DEFAULT NULL
    COMMENT 'OC generada al aprobar la pre-orden',

  -- Usuarios
  `usuario_solicita_id`  INT(11)      NOT NULL COMMENT 'Usuario de producción que solicitó',
  `usuario_aprueba_id`   INT(11)      NULL DEFAULT NULL COMMENT 'Usuario de compras que aprobó/rechazó',

  -- Fechas
  `fecha_solicitud`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_respuesta`      DATETIME     NULL DEFAULT NULL,
  `notas`                TEXT         NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `idx_preordenes_estatus`   (`estatus`),
  KEY `idx_preordenes_insumo`    (`insumo_id`),
  KEY `idx_preordenes_origen`    (`origen_tipo`, `origen_id`),
  KEY `idx_preordenes_proveedor` (`proveedor_sugerido_id`),

  CONSTRAINT `fk_preorden_insumo`
    FOREIGN KEY (`insumo_id`) REFERENCES `insumos`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_preorden_proveedor`
    FOREIGN KEY (`proveedor_sugerido_id`) REFERENCES `proveedores`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_preorden_oc`
    FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra`(`id`) ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pre-órdenes de compra generadas desde Producción, pendientes de aprobación en Compras';

-- Auto-increment del folio
DROP TRIGGER IF EXISTS `tr_preorden_folio`;
DELIMITER //
CREATE TRIGGER `tr_preorden_folio`
BEFORE INSERT ON `preordenes`
FOR EACH ROW
BEGIN
  DECLARE v_num INT;
  SELECT COALESCE(MAX(id), 0) + 1 INTO v_num FROM preordenes;
  SET NEW.folio = CONCAT('PRE-', YEAR(NOW()), '-', LPAD(v_num, 4, '0'));
END//
DELIMITER ;

-- ============================================================================
-- BLOQUE 4: TABLA log_importaciones
-- Registro de cada importación masiva de Excel al sistema
-- ============================================================================

CREATE TABLE IF NOT EXISTS `log_importaciones` (
  `id`                   INT(11)      NOT NULL AUTO_INCREMENT,
  `archivo`              VARCHAR(255) NOT NULL COMMENT 'Nombre del archivo .xlsx importado',
  `fecha`                DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id`           INT(11)      NOT NULL,

  -- Resultados
  `productos_importados` INT(11)      NOT NULL DEFAULT 0,
  `formulaciones_creadas` INT(11)     NOT NULL DEFAULT 0,
  `insumos_creados`      INT(11)      NOT NULL DEFAULT 0,
  `insumos_no_encontrados` INT(11)    NOT NULL DEFAULT 0,
  `errores`              TEXT         NULL DEFAULT NULL COMMENT 'Lista de errores o advertencias del parseo',

  -- Estatus
  `estatus`              ENUM('Exitoso','Parcial','Error') NOT NULL DEFAULT 'Exitoso',

  PRIMARY KEY (`id`),
  KEY `idx_log_usuario` (`usuario_id`),
  KEY `idx_log_fecha`   (`fecha`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historial de importaciones masivas de formulaciones desde Excel';

-- ============================================================================
-- BLOQUE 5: AJUSTE EN ordenes_compra
-- Vincular OC con la pre-orden que la originó (si no existe ya)
-- ============================================================================

ALTER TABLE `ordenes_compra`
  ADD COLUMN IF NOT EXISTS `preorden_id` INT(11) NULL DEFAULT NULL
    COMMENT 'Pre-orden que originó esta OC (NULL = OC directa de compras)'
    AFTER `origen_tipo`;

-- ============================================================================
-- BLOQUE 6: TABLAS CRM CLIENTES (si no fueron aplicadas antes)
-- Estas tablas están definidas en crm_clientes.sql — ejecutar SOLO si no existen
-- ============================================================================

-- Verificar con: SHOW TABLES LIKE 'contactos_cliente';
-- Si ya existen, este bloque es seguro por el IF NOT EXISTS

CREATE TABLE IF NOT EXISTS `contactos_cliente` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `cliente_id`      INT(11)      NOT NULL,
  `nombre`          VARCHAR(150) NOT NULL,
  `puesto`          VARCHAR(100) NULL,
  `telefono`        VARCHAR(20)  NULL,
  `email`           VARCHAR(150) NULL,
  `es_principal`    TINYINT(1)   DEFAULT 0,
  `observaciones`   TEXT         NULL,
  `estatus`         ENUM('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contacto_cliente` (`cliente_id`),
  CONSTRAINT `fk_contacto_cli` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Contactos múltiples por cliente (CRM)';

CREATE TABLE IF NOT EXISTS `seguimientos_cliente` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `cliente_id`      INT(11)      NOT NULL,
  `tipo`            ENUM('Llamada','Visita','Correo','WhatsApp','Otro') NOT NULL DEFAULT 'Llamada',
  `fecha`           DATETIME     NOT NULL,
  `asunto`          VARCHAR(200) NULL,
  `notas`           TEXT         NULL,
  `usuario_id`      INT(11)      NOT NULL COMMENT 'Usuario que registró el seguimiento',
  `fecha_registro`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_seg_cliente` (`cliente_id`),
  KEY `idx_seg_fecha`   (`fecha`),
  CONSTRAINT `fk_seguimiento_cli` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historial de seguimientos comerciales por cliente (CRM)';

CREATE TABLE IF NOT EXISTS `documentos_cliente` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `cliente_id`      INT(11)      NOT NULL,
  `nombre`          VARCHAR(200) NOT NULL,
  `tipo`            ENUM('Contrato','Cotización','Especificación','Identificación','Otro') DEFAULT 'Otro',
  `archivo`         VARCHAR(255) NOT NULL COMMENT 'Ruta del archivo subido',
  `usuario_id`      INT(11)      NOT NULL,
  `fecha_subida`    DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doc_cliente` (`cliente_id`),
  CONSTRAINT `fk_doc_cli` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Documentos adjuntos por cliente (CRM)';

-- ============================================================================
-- BLOQUE 7: TABLA calificaciones_proveedor
-- Para el módulo de rating/evaluación de proveedores
-- ============================================================================

CREATE TABLE IF NOT EXISTS `calificaciones_proveedor` (
  `id`               INT(11)       NOT NULL AUTO_INCREMENT,
  `proveedor_id`     INT(11)       NOT NULL,
  `orden_compra_id`  INT(11)       NULL DEFAULT NULL COMMENT 'OC que se evalúa',
  `entrega_tiempo`   TINYINT(1)    NOT NULL DEFAULT 1 COMMENT '1=Sí llegó a tiempo, 0=No',
  `calidad`          TINYINT(1)    NOT NULL DEFAULT 1 COMMENT '1=Buena calidad, 0=Mala calidad',
  `precio_ok`        TINYINT(1)    NOT NULL DEFAULT 1 COMMENT '1=Precio acordado, 0=Diferencia de precio',
  `calificacion`     DECIMAL(3,1)  NOT NULL COMMENT 'Promedio calculado (1-5)',
  `comentarios`      TEXT          NULL,
  `usuario_id`       INT(11)       NOT NULL,
  `fecha`            DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_calif_proveedor` (`proveedor_id`),
  CONSTRAINT `fk_calif_proveedor`
    FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_calif_oc`
    FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Evaluaciones de desempeño por proveedor';

-- ============================================================================
-- VERIFICACIÓN FINAL
-- Ejecuta esto al final para confirmar que todo se aplicó correctamente
-- ============================================================================

SELECT 'formulaciones' AS tabla,
       GROUP_CONCAT(COLUMN_NAME ORDER BY ORDINAL_POSITION) AS columnas_nuevas
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'formulaciones'
  AND COLUMN_NAME IN ('cliente_id','variante_descripcion','rendimiento_m2_por_kg',
                      'cantidad_cubetas_ref','referencia_cliente','comentarios')

UNION ALL

SELECT 'detalle_formulacion',
       GROUP_CONCAT(COLUMN_NAME ORDER BY ORDINAL_POSITION)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'detalle_formulacion'
  AND COLUMN_NAME IN ('grupo_color','porcentaje_fase_acuosa','kg_fase_acuosa',
                      'porcentaje_agua','kg_agua')

UNION ALL

SELECT 'preordenes',
       CONCAT(COUNT(*), ' columnas')
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'preordenes'

UNION ALL

SELECT 'log_importaciones',
       CONCAT(COUNT(*), ' columnas')
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'log_importaciones';
