-- ============================================================================
-- SEED DEMO — Usuario presentación (10 Jul 2026)
-- Usuario: presentacion@chisa.mx / Contraseña: Demo2026!
-- REVERTIR: DELETE FROM privilege WHERE admin IN (SELECT id FROM administradores WHERE username='presentacion@chisa.mx');
--           DELETE FROM administradores WHERE username='presentacion@chisa.mx';
-- ============================================================================

SET @emp_id := (
  SELECT `id` FROM `empleados`
  WHERE `estatus` IN (1, 2)
  ORDER BY `id`
  LIMIT 1
);

DELETE p FROM `privilege` p
INNER JOIN `administradores` a ON a.`id` = p.`admin`
WHERE a.`username` = 'presentacion@chisa.mx';

DELETE FROM `administradores` WHERE `username` = 'presentacion@chisa.mx';

INSERT INTO `administradores` (
  `nombre`, `apellidos`, `username`, `password`, `privilegios`, `departamento`,
  `avatar`, `estatus`, `meta_nombre`, `fecha_alta`, `empleado_id`
) VALUES (
  'Demo',
  'Presentación',
  'presentacion@chisa.mx',
  '$2y$10$CCbieDO/EzzS9ZLL17TOMuL5M6w4w2tsJcjkwv5jOLybwuDTulkMy',
  'demo_presentacion',
  'RH',
  '',
  1,
  'demo_presentacion',
  CURDATE(),
  @emp_id
);

SET @uid := LAST_INSERT_ID();

INSERT INTO `privilege` (`admin`, `permiso`, `valor`) VALUES
(@uid, 'user_consult', 1),
(@uid, 'user_bitacora', 1),
(@uid, 'admin_simular_alertas', 1),
(@uid, 'rh_empleados_consult', 1),
(@uid, 'rh_empleados_edit', 1),
(@uid, 'reloj_ver_dashboard', 1),
(@uid, 'reloj_ver_reportes', 1),
(@uid, 'proveedores_consult', 1),
(@uid, 'proveedores_edit', 1),
(@uid, 'compras_ordenes_consult', 1),
(@uid, 'compras_ordenes_edit', 1),
(@uid, 'compras_autorizar_preordenes', 1),
(@uid, 'compras_recepcion', 1),
(@uid, 'compras_ordenes_add', 1),
(@uid, 'proveedores_add', 1),
(@uid, 'proveedores_insumos', 1),
(@uid, 'produccion_preordenes', 1),
(@uid, 'compras_preordenes_edit', 1),
(@uid, 'compras_documentos', 1),
(@uid, 'compras_pagos', 1),
(@uid, 'compras_servicios_recurrentes', 1),
(@uid, 'reportes_compras', 1);
