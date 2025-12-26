-- Actualización para soportar selección de formulaciones específicas en ventas
-- ========================================================================

-- 1. Agregar formulacion_id a detalle_orden_venta
ALTER TABLE detalle_orden_venta 
ADD COLUMN formulacion_id INT(11) NULL COMMENT 'ID de la formulación específica usada para esta venta',
ADD COLUMN formulacion_version VARCHAR(50) NULL COMMENT 'Versión de la formulación usada';

-- 2. Agregar formulacion_id a solicitudes_produccion (para propagar la elección)
ALTER TABLE solicitudes_produccion
ADD COLUMN formulacion_id INT(11) NULL COMMENT 'Formulación específica solicitada desde ventas',
ADD CONSTRAINT fk_solprod_formulacion FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE RESTRICT;

-- Notas:
-- Al confirmar la venta, si el detalle tiene formulacion_id, se debe copiar a solicitudes_produccion.
-- Al crear la Orden de Producción desde solicitudes, se debe usar este formulacion_id.
