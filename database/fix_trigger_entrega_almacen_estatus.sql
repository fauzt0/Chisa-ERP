-- ============================================================
-- FIX: trigger tr_actualizar_entrega_almacen — estatus obras
-- ============================================================
-- Bug:  La versión original del trigger (database/almacen.sql)
--       asignaba los valores 'Finalizada' y 'En Proceso' a la
--       columna obras.estatus, que NO existen en su definición
--       ENUM. Con STRICT_TRANS_TABLES activo el UPDATE se
--       ejecuta sin error pero el campo no cambia (valor fuera
--       del set → cadena vacía silenciosa); en la práctica el
--       estatus de la obra NUNCA se actualizaba al registrar
--       una entrega.
--
-- Fix:  Se reemplazaron los valores inválidos por los valores
--       correctos del ENUM de obras.estatus:
--         'Finalizada'  →  'Completada'
--         'En Proceso'  →  'En Ejecución'
--       Adicionalmente se corrigió la expresión de la cantidad
--       total a entregar: ahora usa
--         COALESCE(cantidad_ajustada, cantidad_calculada)
--       para respetar ajustes manuales (antes usaba cantidad
--       a secas, que puede ser NULL en obras sin ajuste).
--
-- ENUMs verificados en producción (2026-09-07):
--   entregas_almacen.estatus : enum('Activa','Cancelada')
--   obras.estatus            : enum('Planificación','En Cotización',
--                                   'Aprobada','En Ejecución','Pausada',
--                                   'Completada','Cancelada')
--   → El filtro ea.estatus = 'Activa' en ObrasModel::get_entregas_obra()
--     es correcto; 'Activa' es un valor válido del ENUM.
--
-- Aplicado en producción: 2026-09-07 11:23:38 (UTC-6)
-- Entorno staging (private_html): N/A — no existe copia staging con BD propia.
-- Rama git: iteracion-3  commit: 89bba19 (referenciado aquí)
-- ============================================================

DROP TRIGGER IF EXISTS tr_actualizar_entrega_almacen;

DELIMITER $$

CREATE TRIGGER tr_actualizar_entrega_almacen
AFTER INSERT ON detalle_entregas_almacen
FOR EACH ROW
BEGIN
  DECLARE v_orden_id INT;
  DECLARE v_obra_id  INT;

  -- ── Rama: entrega de Orden de Venta ──────────────────────
  IF NEW.tipo_detalle = 'Orden Venta' THEN

    -- Acumular cantidad entregada en el detalle de la orden
    UPDATE detalle_orden_venta
    SET cantidad_entregada = cantidad_entregada + NEW.cantidad_entregada
    WHERE id = NEW.detalle_orden_id;

    -- Obtener el ID de la orden de venta
    SELECT orden_venta_id INTO v_orden_id
    FROM detalle_orden_venta
    WHERE id = NEW.detalle_orden_id;

    -- Actualizar estatus e-fecha_entrega_real de la OV
    UPDATE ordenes_venta ov
    SET
      estatus = CASE
        -- Todo entregado → Entregada
        WHEN (SELECT SUM(cantidad)           FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) =
             (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id)
             THEN 'Entregada'
        -- Algo entregado → En Preparación
        WHEN (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) > 0
             THEN 'En Preparación'
        ELSE estatus
      END,
      fecha_entrega_real = CASE
        WHEN (SELECT SUM(cantidad)           FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) =
             (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id)
             THEN NOW()
        ELSE fecha_entrega_real
      END
    WHERE id = v_orden_id;

  -- ── Rama: entrega de Obra ─────────────────────────────────
  ELSEIF NEW.tipo_detalle = 'Obra' THEN

    -- Acumular cantidad entregada en la línea de producto de obra
    UPDATE obras_productos
    SET cantidad_entregada = cantidad_entregada + NEW.cantidad_entregada
    WHERE id = NEW.obra_producto_id;

    -- Obtener el ID de la obra
    SELECT obra_id INTO v_obra_id
    FROM obras_productos
    WHERE id = NEW.obra_producto_id;

    -- Actualizar estatus de la obra usando valores válidos del ENUM
    -- FIX: 'Finalizada' → 'Completada'  |  'En Proceso' → 'En Ejecución'
    -- FIX: cantidad total = COALESCE(cantidad_ajustada, cantidad_calculada)
    UPDATE obras o
    SET estatus = CASE
      -- Todo entregado → Completada  (era 'Finalizada' — inválido)
      WHEN (SELECT SUM(COALESCE(cantidad_ajustada, cantidad_calculada))
            FROM obras_productos WHERE obra_id = v_obra_id) =
           (SELECT SUM(COALESCE(cantidad_entregada, 0))
            FROM obras_productos WHERE obra_id = v_obra_id)
           THEN 'Completada'
      -- Algo entregado → En Ejecución  (era 'En Proceso' — inválido)
      WHEN (SELECT SUM(COALESCE(cantidad_entregada, 0))
            FROM obras_productos WHERE obra_id = v_obra_id) > 0
           THEN 'En Ejecución'
      ELSE estatus
    END
    WHERE id = v_obra_id;

  END IF;
END$$

DELIMITER ;
