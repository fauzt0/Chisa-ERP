-- ============================================================================
-- SEED DEMO — Reloj Checador (Presentación 10 Jul 2026)
-- Crea checadas de hoy + últimos 7 días para dashboard poblado
-- REVERTIR: DELETE FROM asistencias WHERE dispositivo_sn = 'MB10VL-XXXX-TEST' AND creado_el >= CURDATE();
-- ============================================================================

UPDATE `reloj_dispositivos`
SET `ultima_conexion` = NOW(),
    `alias` = 'Reloj Entrada Demo'
WHERE `sn` = 'MB10VL-XXXX-TEST';

-- Asignar PIN a empleados activos sin PIN (2..9)
SET @pin := 1;
UPDATE `empleados` e
JOIN (
  SELECT `id`, (@pin := @pin + 1) AS nuevo_pin
  FROM `empleados`
  WHERE `estatus` IN (1, 2)
  ORDER BY `id`
  LIMIT 8
) x ON x.id = e.id
SET e.`reloj_pin` = x.nuevo_pin
WHERE e.`reloj_pin` IS NULL OR CAST(e.`reloj_pin` AS CHAR) IN ('', '0');

-- Limpiar checadas demo previas del día (solo dispositivo test)
DELETE FROM `asistencias`
WHERE `dispositivo_sn` = 'MB10VL-XXXX-TEST'
  AND DATE(`fecha_hora`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);

-- Checadas de HOY (entrada + salida) para hasta 8 empleados
INSERT INTO `asistencias` (`usuario_id`, `empleado_id`, `fecha_hora`, `metodo`, `dispositivo_sn`)
SELECT
  CAST(COALESCE(NULLIF(CAST(e.`reloj_pin` AS CHAR), ''), CAST(e.`numero_empleado` AS CHAR)) AS CHAR),
  e.`id`,
  CONCAT(CURDATE(), ' ', LPAD(7 + (e.`id` % 2), 2, '0'), ':', LPAD((e.`id` * 3) % 60, 2, '0'), ':00'),
  1,
  'MB10VL-XXXX-TEST'
FROM `empleados` e
WHERE e.`estatus` IN (1, 2)
  AND COALESCE(CAST(e.`reloj_pin` AS CHAR), '') NOT IN ('', '0')
ORDER BY e.`id`
LIMIT 8;

INSERT INTO `asistencias` (`usuario_id`, `empleado_id`, `fecha_hora`, `metodo`, `dispositivo_sn`)
SELECT
  CAST(COALESCE(NULLIF(CAST(e.`reloj_pin` AS CHAR), ''), CAST(e.`numero_empleado` AS CHAR)) AS CHAR),
  e.`id`,
  CONCAT(CURDATE(), ' 17:', LPAD((e.`id` * 5) % 60, 2, '0'), ':00'),
  1,
  'MB10VL-XXXX-TEST'
FROM `empleados` e
WHERE e.`estatus` IN (1, 2)
  AND COALESCE(CAST(e.`reloj_pin` AS CHAR), '') NOT IN ('', '0')
ORDER BY e.`id`
LIMIT 8;

-- Checadas últimos 7 días (solo entrada, para gráfica)
INSERT INTO `asistencias` (`usuario_id`, `empleado_id`, `fecha_hora`, `metodo`, `dispositivo_sn`)
SELECT
  CAST(COALESCE(NULLIF(CAST(e.`reloj_pin` AS CHAR), ''), CAST(e.`numero_empleado` AS CHAR)) AS CHAR),
  e.`id`,
  CONCAT(DATE_SUB(CURDATE(), INTERVAL d.`dia` DAY), ' 08:15:00'),
  1,
  'MB10VL-XXXX-TEST'
FROM `empleados` e
CROSS JOIN (
  SELECT 1 AS dia UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
) d
WHERE e.`estatus` IN (1, 2)
  AND COALESCE(CAST(e.`reloj_pin` AS CHAR), '') NOT IN ('', '0')
ORDER BY e.`id`
LIMIT 40;

INSERT INTO `reloj_sync_log` (`dispositivo_sn`, `tipo`, `payload_resumen`, `registros_afectados`, `fecha`)
SELECT 'MB10VL-XXXX-TEST', 'asistencias', 'Seed demo presentación reloj', COUNT(*), NOW()
FROM `asistencias`
WHERE `dispositivo_sn` = 'MB10VL-XXXX-TEST'
  AND DATE(`fecha_hora`) = CURDATE();
