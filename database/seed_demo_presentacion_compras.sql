-- ============================================================================
-- SEED DEMO — Presentación Compras (10 Jul 2026)
-- ERP Chisa Recubrimientos
--
-- Crea:
--   1. Pre-orden Pendiente (origen producción)
--   2. OC OC-2026-DEMO1 en estatus "En Tránsito" (2 insumos)
--   3. OC OC-2026-DEMO2 en estatus "Enviada" (simulación de correo)
--   4. Baja stock de 1 insumo vinculado (alerta campana)
--
-- REQUIERE: iteracion4_preordenes_unidades.sql aplicada (origen_tipo 'produccion')
-- REVERTIR: seed_demo_presentacion_compras_revert.sql
-- ============================================================================

-- Columnas opcionales en detalle (si no existen)
ALTER TABLE `detalle_orden_compra`
  ADD COLUMN IF NOT EXISTS `nombre_proveedor` VARCHAR(255) NULL COMMENT 'Nombre comercial del proveedor',
  ADD COLUMN IF NOT EXISTS `codigo_proveedor` VARCHAR(100) NULL COMMENT 'SKU del proveedor';

-- Evitar duplicar si ya se corrió
DELETE doc FROM `detalle_orden_compra` doc
INNER JOIN `ordenes_compra` oc ON oc.`id` = doc.`orden_compra_id`
WHERE oc.`folio` IN ('OC-2026-DEMO1', 'OC-2026-DEMO2');
DELETE FROM `ordenes_compra` WHERE `folio` IN ('OC-2026-DEMO1', 'OC-2026-DEMO2');
DELETE FROM `preordenes` WHERE `notas` LIKE '%DEMO: Pre-orden presentación%';

-- Referencias dinámicas
SET @demo_user_id := COALESCE(
  (SELECT `id` FROM `administradores` WHERE `estatus` = 1 ORDER BY `id` LIMIT 1),
  1
);

SET @demo_proveedor_id := (
  SELECT p.`id`
  FROM `proveedores` p
  WHERE p.`estatus` = 'Activo'
  ORDER BY p.`id`
  LIMIT 1
);

-- Asegurar email para simulación de correo
UPDATE `proveedores`
SET `email` = CONCAT('demo.proveedor.', `id`, '@chisa.mx')
WHERE `id` = @demo_proveedor_id
  AND TRIM(COALESCE(`email`, '')) = '';

SET @demo_formulacion_id := COALESCE(
  (SELECT `id` FROM `formulaciones` WHERE `es_activa` = 1 ORDER BY `id` LIMIT 1),
  1
);

-- Insumo principal para pre-orden y stock bajo
SET @demo_insumo_id := (
  SELECT pi.`insumo_id`
  FROM `proveedor_insumo` pi
  JOIN `insumos` i ON i.`id` = pi.`insumo_id`
  WHERE pi.`proveedor_id` = @demo_proveedor_id
    AND pi.`estatus` = 'Activo'
    AND i.`estatus` = 'Activo'
  ORDER BY pi.`es_proveedor_principal` DESC, pi.`precio_compra` ASC
  LIMIT 1
);

SET @demo_unidad := COALESCE(
  (SELECT `unidad_medida` FROM `insumos` WHERE `id` = @demo_insumo_id),
  'Kg'
);

-- 1) Alerta stock bajo en insumo demo
UPDATE `insumos`
SET `stock_actual` = GREATEST(0, LEAST(`stock_actual`, `stock_minimo` - 1))
WHERE `id` = @demo_insumo_id
  AND `stock_minimo` > 0
  AND `stock_actual` >= `stock_minimo`;

-- Si stock_minimo es 0, forzar umbral mínimo para la demo
UPDATE `insumos`
SET `stock_minimo` = 10.00,
    `stock_actual` = 3.00
WHERE `id` = @demo_insumo_id
  AND (`stock_minimo` IS NULL OR `stock_minimo` <= 0);

-- 2) Pre-orden pendiente
INSERT INTO `preordenes` (
  `folio`,
  `origen_tipo`,
  `origen_id`,
  `insumo_id`,
  `cantidad_solicitada`,
  `unidad`,
  `proveedor_sugerido_id`,
  `estatus`,
  `usuario_solicita_id`,
  `fecha_solicitud`,
  `notas`
) VALUES (
  'PRE-SEED-TEMP',
  'produccion',
  @demo_formulacion_id,
  @demo_insumo_id,
  25.000,
  @demo_unidad,
  @demo_proveedor_id,
  'Pendiente',
  @demo_user_id,
  NOW(),
  'DEMO: Pre-orden presentación — generada desde simulador de producción'
);

SET @demo_preorden_id := LAST_INSERT_ID();

-- 3) OC En Tránsito
INSERT INTO `ordenes_compra` (
  `folio`,
  `proveedor_id`,
  `fecha_orden`,
  `fecha_entrega_estimada`,
  `subtotal`,
  `iva`,
  `total`,
  `forma_pago`,
  `condiciones_pago`,
  `observaciones`,
  `estatus`,
  `creado_por`,
  `fecha_creacion`,
  `aprobado_por`,
  `fecha_aprobacion`,
  `origen`
) VALUES (
  'OC-2026-DEMO1',
  @demo_proveedor_id,
  CURDATE(),
  DATE_ADD(CURDATE(), INTERVAL 5 DAY),
  0.00,
  0.00,
  0.00,
  'Transferencia',
  '50% anticipo, 50% contra entrega',
  'DEMO: Orden en tránsito — seed presentación',
  'En Tránsito',
  @demo_user_id,
  NOW(),
  @demo_user_id,
  NOW(),
  'Compras'
);

SET @oc_transito_id := LAST_INSERT_ID();

INSERT INTO `detalle_orden_compra` (
  `orden_compra_id`, `insumo_id`, `cantidad_solicitada`, `cantidad_recibida`,
  `precio_unitario`, `subtotal`, `nombre_proveedor`, `codigo_proveedor`
)
SELECT
  @oc_transito_id,
  pi.`insumo_id`,
  10.00,
  0.00,
  pi.`precio_compra`,
  ROUND(10.00 * pi.`precio_compra`, 2),
  COALESCE(pi.`nombre_proveedor`, i.`nombre_tecnico`),
  pi.`codigo_proveedor`
FROM `proveedor_insumo` pi
JOIN `insumos` i ON i.`id` = pi.`insumo_id`
WHERE pi.`proveedor_id` = @demo_proveedor_id
  AND pi.`estatus` = 'Activo'
  AND i.`estatus` = 'Activo'
ORDER BY pi.`es_proveedor_principal` DESC, pi.`precio_compra` ASC
LIMIT 2;

UPDATE `ordenes_compra` oc
JOIN (
  SELECT `orden_compra_id`, SUM(`subtotal`) AS st
  FROM `detalle_orden_compra`
  WHERE `orden_compra_id` = @oc_transito_id
  GROUP BY `orden_compra_id`
) d ON d.`orden_compra_id` = oc.`id`
SET oc.`subtotal` = d.st,
    oc.`iva` = ROUND(d.st * 0.16, 2),
    oc.`total` = ROUND(d.st * 1.16, 2)
WHERE oc.`id` = @oc_transito_id;

-- 4) OC Enviada (para simulación de correo en demo)
INSERT INTO `ordenes_compra` (
  `folio`,
  `proveedor_id`,
  `fecha_orden`,
  `fecha_entrega_estimada`,
  `subtotal`,
  `iva`,
  `total`,
  `forma_pago`,
  `observaciones`,
  `estatus`,
  `creado_por`,
  `fecha_creacion`,
  `aprobado_por`,
  `fecha_aprobacion`,
  `origen`
) VALUES (
  'OC-2026-DEMO2',
  @demo_proveedor_id,
  CURDATE(),
  DATE_ADD(CURDATE(), INTERVAL 3 DAY),
  0.00,
  0.00,
  0.00,
  'Transferencia',
  'DEMO: Orden enviada al proveedor — seed presentación (usar botón correo)',
  'Enviada',
  @demo_user_id,
  NOW(),
  @demo_user_id,
  NOW(),
  'Compras'
);

SET @oc_enviada_id := LAST_INSERT_ID();

INSERT INTO `detalle_orden_compra` (
  `orden_compra_id`, `insumo_id`, `cantidad_solicitada`, `cantidad_recibida`,
  `precio_unitario`, `subtotal`, `nombre_proveedor`, `codigo_proveedor`
)
SELECT
  @oc_enviada_id,
  pi.`insumo_id`,
  15.00,
  0.00,
  pi.`precio_compra`,
  ROUND(15.00 * pi.`precio_compra`, 2),
  COALESCE(pi.`nombre_proveedor`, CONCAT(i.`nombre_tecnico`, ' (ref. proveedor)')),
  pi.`codigo_proveedor`
FROM `proveedor_insumo` pi
JOIN `insumos` i ON i.`id` = pi.`insumo_id`
WHERE pi.`proveedor_id` = @demo_proveedor_id
  AND pi.`estatus` = 'Activo'
  AND i.`estatus` = 'Activo'
ORDER BY pi.`es_proveedor_principal` DESC, pi.`precio_compra` ASC
LIMIT 1;

UPDATE `ordenes_compra` oc
JOIN (
  SELECT `orden_compra_id`, SUM(`subtotal`) AS st
  FROM `detalle_orden_compra`
  WHERE `orden_compra_id` = @oc_enviada_id
  GROUP BY `orden_compra_id`
) d ON d.`orden_compra_id` = oc.`id`
SET oc.`subtotal` = d.st,
    oc.`iva` = ROUND(d.st * 0.16, 2),
    oc.`total` = ROUND(d.st * 1.16, 2)
WHERE oc.`id` = @oc_enviada_id;

-- ============================================================================
-- Verificación
-- ============================================================================
SELECT 'preordenes_pendientes' AS metrica, COUNT(*) AS valor
FROM `preordenes` WHERE `estatus` = 'Pendiente'
UNION ALL
SELECT 'oc_en_transito', COUNT(*) FROM `ordenes_compra` WHERE `estatus` = 'En Tránsito'
UNION ALL
SELECT 'oc_enviada_demo', COUNT(*) FROM `ordenes_compra` WHERE `folio` = 'OC-2026-DEMO2'
UNION ALL
SELECT 'insumos_stock_bajo', COUNT(*) FROM `insumos`
  WHERE `stock_minimo` > 0 AND `stock_actual` < `stock_minimo`;

SELECT p.`folio` AS preorden_folio, i.`codigo`, i.`nombre_tecnico`, p.`cantidad_solicitada`, p.`unidad`, pr.`razon_social`
FROM `preordenes` p
JOIN `insumos` i ON i.`id` = p.`insumo_id`
LEFT JOIN `proveedores` pr ON pr.`id` = p.`proveedor_sugerido_id`
WHERE p.`id` = @demo_preorden_id;

SELECT `folio`, `estatus`, `total`, `proveedor_id` FROM `ordenes_compra`
WHERE `folio` IN ('OC-2026-DEMO1', 'OC-2026-DEMO2');
