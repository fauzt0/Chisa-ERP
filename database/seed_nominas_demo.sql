-- ============================================================
-- SEED: Nóminas ficticias / de prueba (pagadas y pendientes)
-- Empleados con datos de entrenamiento1.jpg
-- Ejecutar DESPUÉS de empleados_sueldos_entrenamiento1.sql
--
-- CLI:  mysql ... < database/seed_nominas_demo.sql
-- PHP:  php database/run_seed_nominas_demo.php apply
-- ============================================================

-- Evitar duplicar si ya se corrió
DELETE nd FROM nominas_detalle nd
INNER JOIN nominas n ON n.id = nd.nomina_id
WHERE n.folio IN ('NOM000002','NOM000003','NOM000004','NOM000005');

DELETE FROM nominas WHERE folio IN ('NOM000002','NOM000003','NOM000004','NOM000005');

-- ------------------------------------------------------------
-- 1) NOM000002 — Semana 06/07/2026–12/07/2026 — PAGADA
--    Datos reales de entrenamiento1.jpg
-- ------------------------------------------------------------
INSERT INTO nominas (
  folio, periodo_inicio, periodo_fin, tipo_nomina, fecha_pago,
  total_percepciones, total_deducciones, total_neto,
  estatus, observaciones, usuario_creacion, fecha_creacion
) VALUES (
  'NOM000002', '2026-07-06', '2026-07-12', 'Semanal', '2026-07-12',
  62372.89, 3513.82, 58859.07,
  'Pagada', 'Seed demo — Relación de nómina entrenamiento1.jpg (06-12 jul 2026)', 1, '2026-07-12 18:00:00'
);

SET @n2 := LAST_INSERT_ID();

INSERT INTO nominas_detalle (
  nomina_id, empleado_id, lugar_origen, dias_trabajados,
  sueldo_base, sueldo_diario, horas_extras, costo_hora_extra, monto_horas_extras,
  comidas, viaticos_pasajes, prima, otros_bonos, otros_ingresos,
  percepciones, deducciones, prestamo_personal, otros_descuentos, infonavit_descuento,
  neto, monto_pagado, fecha_pago, estatus
) VALUES
-- Esahu Sanchez Martinez
(@n2, 5,  'OFICINA', 7, 10160.02, 1849.15, 0, 0, 0, 0, 0, 0, 0, 0, 10160.02, 235.53, 0, 235.53, 0, 9924.49, 9924.49, '2026-07-12 18:00:00', 'Pagado'),
-- Oscar Galindo Perez
(@n2, 16, 'OFICINA', 7, 5051.41, 866.47, 0, 0, 0, 0, 0, 0, 0, 0, 5051.41, 0, 0, 0, 0, 5051.41, 5051.41, '2026-07-12 18:00:00', 'Pagado'),
-- Mauro Avila Leal
(@n2, 9,  'OFICINA', 7, 6175.40, 1055.05, 0, 0, 0, 1700.00, 0, 0, 0, 0, 7875.40, 240.54, 0, 0, 240.54, 7634.86, 7634.86, '2026-07-12 18:00:00', 'Pagado'),
-- Carolina Galvan Franco
(@n2, 11, 'OFICINA', 7, 3551.65, 584.28, 0, 0, 0, 0, 0, 0, 0, 0, 3551.65, 307.87, 0, 0, 307.87, 3243.78, 3243.78, '2026-07-12 18:00:00', 'Pagado'),
-- Jorge Sanchez Chimal
(@n2, 7,  'OFICINA', 7, 5000.00, 1210.39, 0, 0, 0, 0, 0, 0, 0, 0, 5000.00, 0, 0, 0, 0, 5000.00, 5000.00, '2026-07-12 18:00:00', 'Pagado'),
-- Miguel Sanchez Colin (HE 956.64)
(@n2, 15, 'OFICINA', 7, 3341.59, 546.65, 7.00, 136.66, 956.64, 0, 0, 0, 0, 0, 4298.23, 0, 0, 0, 0, 4298.23, 4298.23, '2026-07-12 18:00:00', 'Pagado'),
-- Iliana Quezada Martinez
(@n2, 17, 'OFICINA', 7, 2211.34, 315.04, 0, 0, 0, 0, 0, 0, 0, 0, 2211.34, 0, 0, 0, 0, 2211.34, 2211.34, '2026-07-12 18:00:00', 'Pagado'),
-- Miguel Ivan Sanchez Martinez
(@n2, 10, 'OFICINA', 7, 6055.60, 1055.50, 0, 0, 0, 0, 0, 0, 0, 0, 6055.60, 0, 0, 0, 0, 6055.60, 6055.60, '2026-07-12 18:00:00', 'Pagado'),
-- Francisco Martinez Hernandez
(@n2, 12, 'LAGUNAS OAXACA', 7, 4526.92, 772.40, 0, 0, 0, 1700.00, 0, 0, 0, 0, 6226.92, 0, 0, 0, 0, 6226.92, 6226.92, '2026-07-12 18:00:00', 'Pagado'),
-- Marcelo Lugo Garcia
(@n2, 8,  'TUXTLA', 7, 4501.65, 763.00, 0, 0, 0, 1700.00, 0, 0, 0, 0, 6201.65, 1584.36, 0, 0, 1584.36, 4617.29, 4617.29, '2026-07-12 18:00:00', 'Pagado'),
-- Teodoro Jimenez Ramirez
(@n2, 6,  'PRODUCCION', 7, 3740.67, 634.56, 0, 0, 0, 0, 0, 0, 2000.00, 0, 5740.67, 1145.52, 0, 0, 1145.52, 4595.15, 4595.15, '2026-07-12 18:00:00', 'Pagado');

-- ------------------------------------------------------------
-- 2) NOM000003 — Semana anterior 29/06–05/07/2026 — PAGADA (ficticia)
-- ------------------------------------------------------------
INSERT INTO nominas (
  folio, periodo_inicio, periodo_fin, tipo_nomina, fecha_pago,
  total_percepciones, total_deducciones, total_neto,
  estatus, observaciones, usuario_creacion, fecha_creacion
) VALUES (
  'NOM000003', '2026-06-29', '2026-07-05', 'Semanal', '2026-07-05',
  0, 0, 0,
  'Pagada', 'Seed demo — Nómina semanal ficticia ya pagada', 1, '2026-07-05 17:00:00'
);

SET @n3 := LAST_INSERT_ID();

INSERT INTO nominas_detalle (
  nomina_id, empleado_id, lugar_origen, dias_trabajados,
  sueldo_base, sueldo_diario, horas_extras, costo_hora_extra, monto_horas_extras,
  comidas, viaticos_pasajes, prima, otros_bonos, otros_ingresos,
  percepciones, deducciones, prestamo_personal, otros_descuentos, infonavit_descuento,
  neto, monto_pagado, fecha_pago, estatus
)
SELECT
  @n3,
  e.id,
  e.lugar_pago,
  7,
  ROUND(e.salario_base_diario * 7, 2),
  e.salario_base_diario,
  0, e.costo_hora_extra, 0,
  0, 0, 0, 0, 0,
  ROUND(e.salario_base_diario * 7, 2),
  IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0),
  0, 0,
  IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0),
  ROUND(e.salario_base_diario * 7 - IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0), 2),
  ROUND(e.salario_base_diario * 7 - IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0), 2),
  '2026-07-05 17:00:00',
  'Pagado'
FROM empleados e
WHERE e.id IN (5,6,7,8,9,10,11,12,15,16,17);

UPDATE nominas n
JOIN (
  SELECT nomina_id,
         ROUND(SUM(percepciones),2) p,
         ROUND(SUM(deducciones),2) d,
         ROUND(SUM(neto),2) neto
  FROM nominas_detalle WHERE nomina_id = @n3 GROUP BY nomina_id
) t ON t.nomina_id = n.id
SET n.total_percepciones = t.p, n.total_deducciones = t.d, n.total_neto = t.neto
WHERE n.id = @n3;

-- ------------------------------------------------------------
-- 3) NOM000004 — Semana 13/07–19/07/2026 — CALCULADA (pendiente de pago)
-- ------------------------------------------------------------
INSERT INTO nominas (
  folio, periodo_inicio, periodo_fin, tipo_nomina, fecha_pago,
  total_percepciones, total_deducciones, total_neto,
  estatus, observaciones, usuario_creacion, fecha_creacion
) VALUES (
  'NOM000004', '2026-07-13', '2026-07-19', 'Semanal', '2026-07-19',
  0, 0, 0,
  'Calculada', 'Seed demo — Calculada, empleados con estatus Pendiente de pago', 1, '2026-07-19 10:00:00'
);

SET @n4 := LAST_INSERT_ID();

INSERT INTO nominas_detalle (
  nomina_id, empleado_id, lugar_origen, dias_trabajados,
  sueldo_base, sueldo_diario, horas_extras, costo_hora_extra, monto_horas_extras,
  comidas, viaticos_pasajes, prima, otros_bonos, otros_ingresos,
  percepciones, deducciones, prestamo_personal, otros_descuentos, infonavit_descuento,
  neto, monto_pagado, fecha_pago, estatus
)
SELECT
  @n4,
  e.id,
  e.lugar_pago,
  7,
  ROUND(e.salario_base_diario * 7, 2),
  e.salario_base_diario,
  0, e.costo_hora_extra, 0,
  CASE e.id WHEN 9 THEN 1700 WHEN 12 THEN 1700 WHEN 8 THEN 1700 ELSE 0 END,
  0, 0,
  CASE e.id WHEN 6 THEN 2000 ELSE 0 END,
  0,
  ROUND(
    e.salario_base_diario * 7
    + CASE e.id WHEN 9 THEN 1700 WHEN 12 THEN 1700 WHEN 8 THEN 1700 ELSE 0 END
    + CASE e.id WHEN 6 THEN 2000 ELSE 0 END
  , 2),
  IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0),
  0, 0,
  IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0),
  ROUND(
    e.salario_base_diario * 7
    + CASE e.id WHEN 9 THEN 1700 WHEN 12 THEN 1700 WHEN 8 THEN 1700 ELSE 0 END
    + CASE e.id WHEN 6 THEN 2000 ELSE 0 END
    - IF(e.tiene_infonavit = 1, e.descuento_infonavit, 0)
  , 2),
  0,
  NULL,
  'Pendiente'
FROM empleados e
WHERE e.id IN (5,6,7,8,9,10,11,12,15,16,17);

UPDATE nominas n
JOIN (
  SELECT nomina_id,
         ROUND(SUM(percepciones),2) p,
         ROUND(SUM(deducciones),2) d,
         ROUND(SUM(neto),2) neto
  FROM nominas_detalle WHERE nomina_id = @n4 GROUP BY nomina_id
) t ON t.nomina_id = n.id
SET n.total_percepciones = t.p, n.total_deducciones = t.d, n.total_neto = t.neto
WHERE n.id = @n4;

-- ------------------------------------------------------------
-- 4) NOM000005 — Semana 20/07–26/07/2026 — BORRADOR (lista para calcular)
-- ------------------------------------------------------------
INSERT INTO nominas (
  folio, periodo_inicio, periodo_fin, tipo_nomina, fecha_pago,
  total_percepciones, total_deducciones, total_neto,
  estatus, observaciones, usuario_creacion, fecha_creacion
) VALUES (
  'NOM000005', '2026-07-20', '2026-07-26', 'Semanal', '2026-07-26',
  0, 0, 0,
  'Borrador', 'Seed demo — Borrador para probar cálculo automático', 1, '2026-07-24 09:00:00'
);

SET @n5 := LAST_INSERT_ID();

INSERT INTO nominas_detalle (nomina_id, empleado_id, lugar_origen)
SELECT @n5, e.id, e.lugar_pago
FROM empleados e
WHERE e.id IN (5,6,7,8,9,10,11,12,15,16,17);

-- Verificación
SELECT folio, periodo_inicio, periodo_fin, tipo_nomina, estatus, total_neto,
       (SELECT COUNT(*) FROM nominas_detalle nd WHERE nd.nomina_id = n.id) AS empleados,
       (SELECT SUM(CASE WHEN estatus='Pagado' THEN 1 ELSE 0 END) FROM nominas_detalle nd WHERE nd.nomina_id = n.id) AS pagados,
       (SELECT SUM(CASE WHEN estatus='Pendiente' THEN 1 ELSE 0 END) FROM nominas_detalle nd WHERE nd.nomina_id = n.id) AS pendientes
FROM nominas n
WHERE folio IN ('NOM000002','NOM000003','NOM000004','NOM000005')
ORDER BY folio;
