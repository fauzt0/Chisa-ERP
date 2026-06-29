-- ═══════════════════════════════════════════════════════════
-- FASE 3: BOM Multinivel — Enlace insumos fabricados a productos
-- Ejecutar en: st32477_chisa
-- ═══════════════════════════════════════════════════════════

-- 1. Aseguramos que la columna 'tipo' y 'producto_id' existan en insumos
--    (ya fueron creadas por fase2, esto es solo verificación segura)
ALTER TABLE insumos
  MODIFY COLUMN tipo ENUM('comprado','fabricado','semielaborado') DEFAULT 'comprado',
  MODIFY COLUMN producto_id INT(11) NULL COMMENT 'Si tipo=fabricado, ID del producto que lo produce';

-- 2. Índice para acelerar búsquedas BOM
ALTER TABLE insumos 
  ADD INDEX IF NOT EXISTS idx_tipo_producto (tipo, producto_id);

-- 3. Vista para ver fácilmente qué insumos están ligados a productos fabricados
CREATE OR REPLACE VIEW v_insumos_fabricados AS
  SELECT
    i.id          AS insumo_id,
    i.codigo      AS insumo_codigo,
    i.nombre_tecnico AS insumo_nombre,
    i.tipo,
    p.id          AS producto_id,
    p.codigo      AS producto_codigo,
    p.nombre      AS producto_nombre,
    f.id          AS formulacion_id,
    f.version     AS formulacion_version,
    f.es_activa
  FROM insumos i
  JOIN productos p ON p.id = i.producto_id
  LEFT JOIN formulaciones f ON f.producto_id = p.id AND f.es_activa = 1
  WHERE i.tipo IN ('fabricado','semielaborado');

-- 4. Vista de explosión BOM de un nivel (para diagnóstico rápido)
CREATE OR REPLACE VIEW v_bom_nivel1 AS
  SELECT
    f.id            AS formulacion_id,
    p.id            AS producto_id,
    p.nombre        AS producto_nombre,
    p.codigo        AS producto_codigo,
    df.id           AS detalle_id,
    df.tipo_componente,
    COALESCE(i.nombre_tecnico, psub.nombre) AS componente_nombre,
    COALESCE(i.codigo, psub.codigo)         AS componente_codigo,
    i.tipo          AS insumo_tipo,
    i.producto_id   AS insumo_producto_id,
    df.porcentaje,
    df.cantidad,
    df.grupo_color,
    CASE WHEN i.tipo IN ('fabricado','semielaborado') AND i.producto_id IS NOT NULL
         THEN 1 ELSE 0
    END AS es_sub_fabricado
  FROM formulaciones f
  JOIN productos p        ON p.id = f.producto_id
  JOIN detalle_formulacion df ON df.formulacion_id = f.id
  LEFT JOIN insumos i     ON i.id = df.insumo_id
  LEFT JOIN productos psub ON psub.id = df.producto_id
  WHERE f.es_activa = 1;

-- 5. Procedimiento helper: marcar un insumo como fabricado y enlazarlo a un producto
DELIMITER //
DROP PROCEDURE IF EXISTS sp_enlazar_insumo_fabricado //
CREATE PROCEDURE sp_enlazar_insumo_fabricado(
    IN p_insumo_id   INT,
    IN p_producto_id INT
)
BEGIN
    UPDATE insumos
    SET tipo       = 'fabricado',
        producto_id = p_producto_id
    WHERE id = p_insumo_id;

    SELECT i.id, i.nombre_tecnico, i.tipo, p.nombre AS producto_nombre
    FROM insumos i
    JOIN productos p ON p.id = i.producto_id
    WHERE i.id = p_insumo_id;
END //
DELIMITER ;

-- 6. Procedimiento para auto-detectar coincidencias nombre insumo ↔ nombre producto
--    Ayuda a encontrar candidatos a enlazar (NO hace cambios, solo lista candidatos)
DELIMITER //
DROP PROCEDURE IF EXISTS sp_detectar_candidatos_fabricados //
CREATE PROCEDURE sp_detectar_candidatos_fabricados()
BEGIN
    SELECT
        i.id          AS insumo_id,
        i.nombre_tecnico AS insumo_nombre,
        i.tipo        AS insumo_tipo_actual,
        p.id          AS producto_id_candidato,
        p.nombre      AS producto_nombre_candidato,
        ROUND(
            (LENGTH(i.nombre_tecnico) + LENGTH(p.nombre) -
             2 * LENGTH(REPLACE(LOWER(i.nombre_tecnico), LOWER(SUBSTRING(p.nombre, 1, 5)), '')))
            / GREATEST(LENGTH(i.nombre_tecnico), LENGTH(p.nombre)) * 100
        , 0) AS similitud_approx
    FROM insumos i
    JOIN productos p ON (
        LOWER(i.nombre_tecnico) LIKE CONCAT('%', LOWER(p.nombre), '%')
        OR LOWER(p.nombre) LIKE CONCAT('%', LOWER(i.nombre_tecnico), '%')
    )
    WHERE i.tipo = 'comprado'
      AND p.tipo_producto = 'Fabricado'
    ORDER BY similitud_approx DESC
    LIMIT 50;
END //
DELIMITER ;

-- 7. Vista resumen para el dashboard: stock de insumos vs necesidad de formulaciones activas
CREATE OR REPLACE VIEW v_stock_insumos_vs_formulaciones AS
  SELECT
    i.id            AS insumo_id,
    i.nombre_tecnico,
    i.codigo,
    i.stock_actual,
    i.unidad_medida,
    i.tipo,
    COUNT(DISTINCT df.formulacion_id) AS num_formulaciones_uso,
    SUM(df.cantidad) AS cantidad_total_en_formulaciones
  FROM insumos i
  LEFT JOIN detalle_formulacion df ON df.insumo_id = i.id
  LEFT JOIN formulaciones f ON f.id = df.formulacion_id AND f.es_activa = 1
  WHERE i.estatus = 'Activo'
  GROUP BY i.id
  ORDER BY i.nombre_tecnico;
