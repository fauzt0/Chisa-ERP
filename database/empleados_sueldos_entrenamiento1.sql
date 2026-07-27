-- ============================================================
-- Actualiza sueldos / lugar de pago / INFONAVIT / cuentas
-- según doc/imagenes_nomima_iteracion1/entrenamiento1.jpg
-- y entrenamiento2.jpg (cuentas Banorte)
-- Periodo de referencia: 06/07/2026 – 12/07/2026 (semanal)
-- ============================================================

-- Catálogo banco Banorte (si no existe)
INSERT INTO cuentas_bancarias (banco, numero_cuenta, clabe, tipo_cuenta, moneda, estatus)
SELECT 'Banorte', 'CATALOGO-BANORTE', NULL, 'Nómina', 'MXN', 'Activa'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM cuentas_bancarias WHERE banco = 'Banorte' LIMIT 1
);

SET @banorte_id := (SELECT id FROM cuentas_bancarias WHERE banco = 'Banorte' ORDER BY id ASC LIMIT 1);

-- ESAHU ENRIQUE SANCHEZ MARTINEZ (id 5) — OFICINA
UPDATE empleados SET
  salario_base_diario = 1849.15,
  salario_base_mensual = ROUND(1849.15 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  banco = 'Banorte',
  fecha_edicion = CURDATE()
WHERE id = 5;

-- OSCAR GALINDO PEREZ (id 16) — OFICINA
UPDATE empleados SET
  salario_base_diario = 866.47,
  salario_base_mensual = ROUND(866.47 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  banco = 'Banorte',
  cuenta_bancaria = '1276719591',
  fecha_edicion = CURDATE()
WHERE id = 16;

-- MAURO AVILA LEAL (id 9) — OFICINA
UPDATE empleados SET
  salario_base_diario = 1055.05,
  salario_base_mensual = ROUND(1055.05 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 1,
  descuento_infonavit = 240.54,
  banco = 'Banorte',
  cuenta_bancaria = '1281547613',
  fecha_edicion = CURDATE()
WHERE id = 9;

-- CAROLINA GALVAN FRANCO (id 11) — OFICINA
UPDATE empleados SET
  salario_base_diario = 584.28,
  salario_base_mensual = ROUND(584.28 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 1,
  descuento_infonavit = 307.87,
  banco = 'Banorte',
  cuenta_bancaria = '1276719546',
  fecha_edicion = CURDATE()
WHERE id = 11;

-- JORGE SANCHEZ CHIMAL (id 7) — OFICINA
UPDATE empleados SET
  salario_base_diario = 1210.39,
  salario_base_mensual = ROUND(1210.39 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  fecha_edicion = CURDATE()
WHERE id = 7;

-- MIGUEL SANCHEZ COLIN (id 15) — OFICINA
-- Horas extras en papel: $956.64 → costo HE ≈ (diario/8)*2 = 136.66
UPDATE empleados SET
  salario_base_diario = 546.65,
  salario_base_mensual = ROUND(546.65 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  costo_hora_extra = 136.66,
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  banco = 'Banorte',
  cuenta_bancaria = '1281146548',
  fecha_edicion = CURDATE()
WHERE id = 15;

-- ILIANA QUEZADA MARTINEZ (id 17) — OFICINA
UPDATE empleados SET
  salario_base_diario = 315.04,
  salario_base_mensual = ROUND(315.04 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  banco = 'Banorte',
  cuenta_bancaria = '1281940438',
  fecha_edicion = CURDATE()
WHERE id = 17;

-- MIGUEL IVAN SANCHEZ MARTINEZ (id 10) — OFICINA
UPDATE empleados SET
  salario_base_diario = 1055.50,
  salario_base_mensual = ROUND(1055.50 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  banco = 'Banorte',
  cuenta_bancaria = '1281633589',
  fecha_edicion = CURDATE()
WHERE id = 10;

-- FRANCISCO MARTINEZ HERNANDEZ (id 12) — LAGUNAS OAXACA
UPDATE empleados SET
  salario_base_diario = 772.40,
  salario_base_mensual = ROUND(772.40 * 30, 2),
  lugar_pago = 'LAGUNAS OAXACA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  banco = 'Banorte',
  cuenta_bancaria = '1292066558',
  fecha_edicion = CURDATE()
WHERE id = 12;

-- MARCELO LUGO GARCIA (id 8) — TUXTLA
UPDATE empleados SET
  salario_base_diario = 763.00,
  salario_base_mensual = ROUND(763.00 * 30, 2),
  lugar_pago = 'TUXTLA',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 1,
  descuento_infonavit = 1584.36,
  banco = 'Banorte',
  cuenta_bancaria = '1305447477',
  fecha_edicion = CURDATE()
WHERE id = 8;

-- TEODORO JIMENEZ RAMIREZ (id 6) — PRODUCCION
UPDATE empleados SET
  salario_base_diario = 634.56,
  salario_base_mensual = ROUND(634.56 * 30, 2),
  lugar_pago = 'PRODUCCION',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 1,
  descuento_infonavit = 1145.52,
  banco = 'Banorte',
  cuenta_bancaria = '1276724719',
  fecha_edicion = CURDATE()
WHERE id = 6;

-- Cuentas bancarias por empleado (default) desde entrenamiento2.jpg
INSERT INTO empleados_cuentas_bancarias (empleado_id, cuenta_bancaria_id, numero_cuenta, clabe, es_default, estatus)
SELECT e.id, @banorte_id, e.cuenta_bancaria, NULL, 1, 1
FROM empleados e
WHERE e.id IN (6, 8, 9, 10, 11, 12, 15, 16, 17)
  AND e.cuenta_bancaria IS NOT NULL AND e.cuenta_bancaria != ''
  AND NOT EXISTS (
    SELECT 1 FROM empleados_cuentas_bancarias ecb
    WHERE ecb.empleado_id = e.id AND ecb.numero_cuenta = e.cuenta_bancaria AND ecb.estatus = 1
  );

-- Alinear frecuencia de automatización con nómina real (semanal)
UPDATE nomina_configuracion
SET frecuencia = 'Semanal', crear_dias_antes = 1
WHERE id = (SELECT id FROM (SELECT id FROM nomina_configuracion ORDER BY id DESC LIMIT 1) t);

-- Verificación
SELECT id, numero_empleado,
       CONCAT(nombre,' ',apellido_paterno,' ',IFNULL(apellido_materno,'')) AS nombre,
       salario_base_diario, salario_base_mensual, lugar_pago, tipo_nomina,
       tiene_infonavit, descuento_infonavit, costo_hora_extra, cuenta_bancaria
FROM empleados
WHERE id IN (5,6,7,8,9,10,11,12,15,16,17)
ORDER BY lugar_pago, nombre;
