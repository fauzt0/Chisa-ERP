-- ============================================================
-- Iteración 6 — Activar la versión más reciente de formulación por producto
-- Fecha: 2026-07-09
--
-- Regla: por cada producto Fabricado con al menos 1 formulación válida
-- (con componentes y suma % entre 85 y 115%), marcar la de mayor
-- número de versión como activa y desactivar las demás.
--
-- Se excluyen formulaciones multicolor (suma ~200%) y las marcadas
-- REQUIERE REVISION MANUAL para no activar datos incorrectos.
-- ============================================================

START TRANSACTION;

-- 1. Desactivar TODAS las formulaciones actuales (limpiar estado previo)
UPDATE formulaciones SET es_activa = 0;

-- 2. Activar la versión más reciente con datos válidos (suma % entre 85-115)
--    por cada producto

UPDATE formulaciones f
JOIN (
    SELECT f2.producto_id, MAX(f2.id) AS max_id
    FROM formulaciones f2
    -- Solo formulaciones con componentes que sumen entre 85 y 115%
    WHERE f2.id IN (
        SELECT DISTINCT df.formulacion_id
        FROM detalle_formulacion df
        GROUP BY df.formulacion_id
        HAVING ROUND(SUM(df.porcentaje), 1) BETWEEN 85 AND 115
    )
    -- Excluir marcadas para revisión manual
    AND (f2.comentarios IS NULL OR f2.comentarios NOT LIKE '%REQUIERE REVISION MANUAL%')
    GROUP BY f2.producto_id
) latest ON latest.max_id = f.id
SET f.es_activa = 1,
    f.fecha_activacion = NOW();

SELECT 'Formulaciones activadas' AS accion, ROW_COUNT() AS total;

-- 3. Resumen del estado post-activación
SELECT
    SUM(CASE WHEN es_activa = 1 THEN 1 ELSE 0 END) activas,
    SUM(CASE WHEN es_activa = 0 THEN 1 ELSE 0 END) inactivas,
    COUNT(*) total
FROM formulaciones;

-- 4. Productos Fabricados sin ninguna formulación activa
SELECT p.id, p.codigo, LEFT(p.nombre,50) nombre
FROM productos p
LEFT JOIN formulaciones f ON f.producto_id = p.id AND f.es_activa = 1
WHERE p.tipo_producto = 'Fabricado'
AND f.id IS NULL
ORDER BY p.nombre LIMIT 30;

COMMIT;
