-- Demo: proveedores de servicios + pagos recurrentes (internet, soporte)

INSERT INTO proveedores (codigo, razon_social, nombre_comercial, rfc, tipo_proveedor, contacto_principal, telefono, email, ciudad, estado, dias_credito, estatus)
SELECT 'PROV-SRV01', 'Telmex S.A. de C.V.', 'Telmex Internet Empresarial', 'TME840315KL9', 'Servicios', 'Atención Empresas', '800-123-0369', 'empresas@telmex.com', 'Ciudad de México', 'CDMX', 0, 'Activo'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM proveedores WHERE rfc = 'TME840315KL9');

INSERT INTO proveedores (codigo, razon_social, nombre_comercial, rfc, tipo_proveedor, contacto_principal, telefono, email, ciudad, estado, dias_credito, estatus)
SELECT 'PROV-SRV02', 'Soporte TI Chisa Partner S.A. de C.V.', 'Soporte TI Partner', 'STP901201AB1', 'Servicios', 'Mesa de ayuda', '55-5555-0100', 'soporte@tipartner.mx', 'Monterrey', 'Nuevo León', 15, 'Activo'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM proveedores WHERE rfc = 'STP901201AB1');

SET @prov_telmex = (SELECT id FROM proveedores WHERE rfc = 'TME840315KL9' LIMIT 1);
SET @prov_soporte = (SELECT id FROM proveedores WHERE rfc = 'STP901201AB1' LIMIT 1);

INSERT INTO servicios_recurrentes (proveedor_id, nombre_servicio, descripcion, tipo_servicio, frecuencia, dia_vencimiento, monto_estimado, activo, fecha_inicio, notas)
SELECT @prov_telmex, 'Internet empresarial 100 Mbps', 'Línea dedicada oficina principal', 'Telecomunicaciones', 'Mensual', 5, 1899.00, 1, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 'Pago día 5 de cada mes'
FROM DUAL WHERE @prov_telmex IS NOT NULL AND NOT EXISTS (
  SELECT 1 FROM servicios_recurrentes WHERE nombre_servicio = 'Internet empresarial 100 Mbps' AND proveedor_id = @prov_telmex
);

INSERT INTO servicios_recurrentes (proveedor_id, nombre_servicio, descripcion, tipo_servicio, frecuencia, dia_vencimiento, monto_estimado, activo, fecha_inicio, notas)
SELECT @prov_soporte, 'Soporte técnico mensual ERP', 'Mantenimiento servidores y estaciones', 'Soporte Técnico', 'Mensual', 15, 4500.00, 1, DATE_FORMAT(CURDATE(), '%Y-%m-01'), '8 horas soporte remoto incluidas'
FROM DUAL WHERE @prov_soporte IS NOT NULL AND NOT EXISTS (
  SELECT 1 FROM servicios_recurrentes WHERE nombre_servicio = 'Soporte técnico mensual ERP' AND proveedor_id = @prov_soporte
);

-- Pagos mes actual
INSERT INTO pagos_servicios_recurrentes (servicio_recurrente_id, periodo, fecha_vencimiento, monto, estatus)
SELECT sr.id,
       DATE_FORMAT(CURDATE(), '%Y-%m'),
       CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(LEAST(sr.dia_vencimiento, DAY(LAST_DAY(CURDATE()))), 2, '0')),
       sr.monto_estimado,
       IF(sr.dia_vencimiento < DAY(CURDATE()), 'Vencido', 'Pendiente')
FROM servicios_recurrentes sr
WHERE sr.activo = 1
  AND NOT EXISTS (
    SELECT 1 FROM pagos_servicios_recurrentes psr
    WHERE psr.servicio_recurrente_id = sr.id AND psr.periodo = DATE_FORMAT(CURDATE(), '%Y-%m')
  );

-- Pagos mes anterior (pagados como historial)
INSERT INTO pagos_servicios_recurrentes (servicio_recurrente_id, periodo, fecha_vencimiento, monto, estatus, fecha_pago, referencia)
SELECT sr.id,
       DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m'),
       CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-'), LPAD(LEAST(sr.dia_vencimiento, DAY(LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)))), 2, '0')),
       sr.monto_estimado,
       'Pagado',
       DATE_SUB(CURDATE(), INTERVAL 20 DAY),
       CONCAT('TRF-', DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y%m'))
FROM servicios_recurrentes sr
WHERE sr.activo = 1
  AND NOT EXISTS (
    SELECT 1 FROM pagos_servicios_recurrentes psr
    WHERE psr.servicio_recurrente_id = sr.id AND psr.periodo = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')
  );

SET @uid = (SELECT id FROM administradores WHERE username = 'presentacion@chisa.mx' LIMIT 1);
INSERT IGNORE INTO privilege (admin, permiso, valor) VALUES
(@uid, 'compras_pagos', 1),
(@uid, 'compras_servicios_recurrentes', 1);

-- Proveedor recolección de basura (demo)
INSERT INTO proveedores (codigo, razon_social, nombre_comercial, rfc, tipo_proveedor, telefono, email, ciudad, estado, estatus)
SELECT 'PROV-SRV03', 'Servicios Ambientales del Centro S.A. de C.V.', 'Ambientales Centro', 'SAC920615BC2', 'Servicios', '55-5555-0200', 'cobranza@ambientales.mx', 'Ciudad de México', 'CDMX', 'Activo'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM proveedores WHERE rfc = 'SAC920615BC2');

SET @prov_basura = (SELECT id FROM proveedores WHERE rfc = 'SAC920615BC2' LIMIT 1);

INSERT INTO servicios_recurrentes (proveedor_id, nombre_servicio, descripcion, tipo_servicio, frecuencia, dia_vencimiento, monto_estimado, activo, fecha_inicio, notas)
SELECT @prov_basura, 'Recolección de basura industrial', 'Contenedor 3m³ — recolección 2x semana', 'Recolección de Basura', 'Mensual', 10, 2800.00, 1, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 'Factura mensual fija'
FROM DUAL WHERE @prov_basura IS NOT NULL AND NOT EXISTS (
  SELECT 1 FROM servicios_recurrentes WHERE nombre_servicio = 'Recolección de basura industrial' AND proveedor_id = @prov_basura
);

INSERT INTO pagos_servicios_recurrentes (servicio_recurrente_id, periodo, fecha_vencimiento, monto, estatus)
SELECT sr.id, DATE_FORMAT(CURDATE(), '%Y-%m'),
  CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(LEAST(sr.dia_vencimiento, DAY(LAST_DAY(CURDATE()))), 2, '0')),
  sr.monto_estimado, IF(sr.dia_vencimiento < DAY(CURDATE()), 'Vencido', 'Pendiente')
FROM servicios_recurrentes sr
WHERE sr.nombre_servicio = 'Recolección de basura industrial'
  AND NOT EXISTS (SELECT 1 FROM pagos_servicios_recurrentes psr WHERE psr.servicio_recurrente_id = sr.id AND psr.periodo = DATE_FORMAT(CURDATE(), '%Y-%m'));
