-- ============================================================
-- Iteración 6 — Corrección de formulaciones con porcentaje incorrecto
-- Fecha: 2026-07-09
-- Problema raíz:
--   El importador de FICHAS_CHISA_GLASS_2014.xls leía la fila de
--   BASE (ej. "BLANCO | 1.0 | kg_base") como un componente de BOM,
--   generando 258 formulaciones con suma > 100%.
--
-- PATRÓN A (44 formulaciones con suma ~200%, 1 componente ≥99%):
--   La fila BLANCO/BASE con B=1.0 fue insertada como componente extra.
--   Criterio de seguridad: al eliminar ese componente, los restantes
--   suman entre 99 y 101% → corrección automática segura.
--
-- PATRÓN B (37 formulaciones con suma ~200%, sin componente ≥99%):
--   Son formulaciones multicolor legítimas: cada grupo de color
--   suma 100% independientemente. No requieren eliminación de
--   componentes; solo se añade una nota de trazabilidad.
--
-- CASOS INTERMEDIOS (2 formulaciones: id=37, id=43):
--   Grupos incompletos o mal definidos → marcados para revisión manual.
-- ============================================================

START TRANSACTION;

-- ─────────────────────────────────────────────────────────────────────────────
-- PASO 1: Eliminar componentes BLANCO/BASE espurios (Patrón A)
-- IDs obtenidos de auditoría: los únicos componentes con porcentaje ≥ 99%
-- en formulaciones cuyo resto suma entre 99 y 101%.
-- ─────────────────────────────────────────────────────────────────────────────

DELETE FROM detalle_formulacion
WHERE id IN (
    26, 52, 130, 286, 455, 516, 659, 1063, 1099, 1104, 1109, 1144, 1156,
    1393, 1397, 1401, 1405, 1411, 1415, 1419, 1424, 1428, 1432, 1436, 1440,
    1546, 2086, 2278, 2413, 2424, 2433, 2442, 2576, 2598, 2662, 2716, 2717,
    2732, 2749, 2759, 2773, 2787, 2796, 2974
);

-- Verificar que se eliminaron exactamente 44 filas
-- (si el número es distinto, algo cambió en la BD antes de ejecutar este script)
SELECT ROW_COUNT() AS filas_eliminadas_patron_a;

-- ─────────────────────────────────────────────────────────────────────────────
-- PASO 2: Anotar formulaciones multicolor (Patrón B — 37 formulaciones)
-- Son válidas: cada grupo de color (grupo_color) suma 100% de forma
-- independiente. Se actualiza el comentario para documentarlo.
-- ─────────────────────────────────────────────────────────────────────────────

UPDATE formulaciones
SET comentarios = CONCAT(
    IFNULL(comentarios, 'Importado desde Excel.'),
    ' | MULTICOLOR: contiene múltiples grupos de color; cada grupo suma 100% de forma independiente.'
)
WHERE id IN (
    SELECT formulacion_id FROM (
        SELECT formulacion_id,
            SUM(CASE WHEN ROUND(porcentaje,2) >= 99 THEN 1 ELSE 0 END) tiene_100
        FROM detalle_formulacion
        WHERE formulacion_id IN (
            SELECT formulacion_id FROM detalle_formulacion
            GROUP BY formulacion_id
            HAVING ROUND(SUM(porcentaje), 1) BETWEEN 199 AND 201
        )
        GROUP BY formulacion_id
        HAVING tiene_100 = 0
    ) t
);

SELECT ROW_COUNT() AS formulaciones_anotadas_patron_b;

-- ─────────────────────────────────────────────────────────────────────────────
-- PASO 3: Marcar casos intermedios para revisión manual (id=37, id=43)
-- ─────────────────────────────────────────────────────────────────────────────

UPDATE formulaciones
SET comentarios = CONCAT(
    IFNULL(comentarios, 'Importado desde Excel.'),
    ' | REQUIERE REVISION MANUAL: los grupos de componentes no suman 100% individualmente.'
)
WHERE id IN (37, 43);

-- ─────────────────────────────────────────────────────────────────────────────
-- PASO 4: Verificación post-corrección
-- ─────────────────────────────────────────────────────────────────────────────

SELECT
    SUM(CASE WHEN ROUND(suma, 1) BETWEEN 99 AND 101 THEN 1 ELSE 0 END) correctas_100pct,
    SUM(CASE WHEN ROUND(suma, 1) BETWEEN 199 AND 201 THEN 1 ELSE 0 END) multicolor_200pct,
    SUM(CASE WHEN ROUND(suma, 1) > 101 AND ROUND(suma, 1) < 199 THEN 1 ELSE 0 END) intermedias,
    SUM(CASE WHEN ROUND(suma, 1) < 99 THEN 1 ELSE 0 END) bajo_99pct,
    COUNT(*) total
FROM (
    SELECT formulacion_id, SUM(porcentaje) suma
    FROM detalle_formulacion
    GROUP BY formulacion_id
) t;

COMMIT;
