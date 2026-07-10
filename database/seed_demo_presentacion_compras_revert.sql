-- ============================================================================
-- REVERT — Seed demo presentación Compras
-- Elimina solo los registros creados por seed_demo_presentacion_compras.sql
-- ============================================================================

DELETE doc FROM `detalle_orden_compra` doc
INNER JOIN `ordenes_compra` oc ON oc.`id` = doc.`orden_compra_id`
WHERE oc.`folio` IN ('OC-2026-DEMO1', 'OC-2026-DEMO2');

DELETE FROM `ordenes_compra` WHERE `folio` IN ('OC-2026-DEMO1', 'OC-2026-DEMO2');

DELETE FROM `preordenes` WHERE `notas` LIKE '%DEMO: Pre-orden presentación%';

SELECT 'revert_ok' AS resultado,
  (SELECT COUNT(*) FROM `preordenes` WHERE `notas` LIKE '%DEMO:%') AS preordenes_demo_restantes,
  (SELECT COUNT(*) FROM `ordenes_compra` WHERE `folio` LIKE 'OC-2026-DEMO%') AS oc_demo_restantes;
