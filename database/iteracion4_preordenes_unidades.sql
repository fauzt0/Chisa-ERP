-- ============================================================================
-- MIGRACIÓN: Iteración 4 — Pre-órdenes Automáticas (Producción → Compras)
-- ERP Chisa Recubrimientos
-- FECHA: 2026-07-03
-- EJECUTAR EN: Base de datos de producción CHISA ERP
--
-- CONTEXTO: La tabla `preordenes` ya fue creada en una migración anterior
-- (mejoras_fase2_formulaciones_preordenes.sql) pero su ENUM `unidad` solo
-- soporta 5 valores ('Kg','L','g','ml','Pza'), insuficiente porque una
-- pre-orden debe expresarse en la unidad de COMPRA del insumo
-- (insumos.unidad_medida), que admite 14 valores distintos (Cubeta, Tambo,
-- Galón, Ton, m², m³, Servicio, Otro, etc.). Esta migración amplía el ENUM
-- para igualarlo al catálogo real de insumos.unidad_medida, y agrega
-- 'produccion' como origen_tipo válido (hoy solo admite venta/obra/interno).
-- ============================================================================

-- 1. Ampliar ENUM de unidad en preordenes para igualar insumos.unidad_medida
ALTER TABLE `preordenes`
  MODIFY COLUMN `unidad` ENUM(
    'Kg','g','mg','L','mL','Pza','Cubeta','Tambo','Galón','m²','m³','Ton','Servicio','Otro'
  ) NOT NULL DEFAULT 'Pza'
  COMMENT 'Unidad de compra del insumo (debe reflejar insumos.unidad_medida)';

-- 2. Agregar 'produccion' como origen_tipo válido (generación automática desde
--    el cálculo de insumos de una formulación/proyecto)
ALTER TABLE `preordenes`
  MODIFY COLUMN `origen_tipo` ENUM('venta','obra','interno','produccion')
  NOT NULL DEFAULT 'interno'
  COMMENT 'Qué generó la necesidad. origen_id = formulacion_id cuando es "produccion"';

-- 3. Índice para listar pendientes rápido en notificaciones/dashboard de Compras
CREATE INDEX IF NOT EXISTS `idx_preordenes_estatus_fecha`
  ON `preordenes` (`estatus`, `fecha_solicitud`);

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'preordenes'
  AND COLUMN_NAME IN ('unidad', 'origen_tipo');
