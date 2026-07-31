-- ============================================================
-- Fix: Asigna salarios a empleados demo con salario en cero
-- (IDs 2, 3, 4, 13, 14) — Requisito B2 del reporte de verificación
-- ============================================================

-- Pedro Lopez Morales (id 2) — OFICINA — Semanal — activar
UPDATE empleados SET
  salario_base_diario = 850.00,
  salario_base_mensual = ROUND(850.00 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  estatus = 1,
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  fecha_edicion = CURDATE()
WHERE id = 2;

-- Ana Karina Roman Martinez (id 3) — OFICINA — Semanal — activar
UPDATE empleados SET
  salario_base_diario = 720.00,
  salario_base_mensual = ROUND(720.00 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  estatus = 1,
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  fecha_edicion = CURDATE()
WHERE id = 3;

-- Maria Pilar Gomez (id 4) — OFICINA — Semanal — activar
UPDATE empleados SET
  salario_base_diario = 650.00,
  salario_base_mensual = ROUND(650.00 * 30, 2),
  lugar_pago = 'OFICINA',
  tipo_nomina = 'Semanal',
  estatus = 1,
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  fecha_edicion = CURDATE()
WHERE id = 4;

-- Gerardo Almazan Felipe (id 13) — PRODUCCION — Semanal
UPDATE empleados SET
  salario_base_diario = 900.00,
  salario_base_mensual = ROUND(900.00 * 30, 2),
  lugar_pago = 'PRODUCCION',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  fecha_edicion = CURDATE()
WHERE id = 13;

-- Rigo Antonio Nevarez Martinez (id 14) — PRODUCCION — Semanal
UPDATE empleados SET
  salario_base_diario = 780.00,
  salario_base_mensual = ROUND(780.00 * 30, 2),
  lugar_pago = 'PRODUCCION',
  tipo_nomina = 'Semanal',
  tiene_infonavit = 0,
  descuento_infonavit = 0.00,
  fecha_edicion = CURDATE()
WHERE id = 14;
