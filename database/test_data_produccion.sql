-- ============================================================================
-- SCRIPT DE DATOS DE PRUEBA: MÓDULO DE PRODUCCIÓN / FABRICACIÓN
-- ============================================================================
-- Este script crea un flujo completo de prueba:
-- 1. Cliente -> 2. Insumos -> 3. Producto -> 4. Fórmula -> 5. OV y Obra
-- Use este script en phpMyAdmin para poblar el sistema y probar el Dashboard.
-- ============================================================================

-- 1. IDENTIFICAR TODOS LOS IDs PREVIOS PARA LIMPIEZA (Evita bloqueos de triggers)
SET @test_cliente_id = (SELECT id FROM `clientes` WHERE `codigo` = 'CL-TEST-001' LIMIT 1);
SET @test_prod_id    = (SELECT id FROM `productos` WHERE `codigo` = 'PROD-PINT-001' LIMIT 1);
SET @test_ov_id      = (SELECT id FROM `ordenes_venta` WHERE `folio` = 'OV-TEST-001' LIMIT 1);
SET @test_obra_id    = (SELECT id FROM `obras`         WHERE `folio` = 'OB-TEST-002' LIMIT 1);
SET @test_form_id    = (SELECT id FROM `formulaciones` WHERE `producto_id` = @test_prod_id LIMIT 1);

-- 2. LIMPIEZA TOTAL (Desactivando temporalmente las llaves foráneas)
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `detalle_orden_venta` WHERE `orden_venta_id` = @test_ov_id;
DELETE FROM `ordenes_venta`      WHERE `id` = @test_ov_id;
DELETE FROM `obras_productos`    WHERE `obra_id` = @test_obra_id;
DELETE FROM `obras`             WHERE `id` = @test_obra_id;
DELETE FROM `detalle_formulacion` WHERE `formulacion_id` = @test_form_id;
DELETE FROM `formulaciones`      WHERE `id` = @test_form_id;
DELETE FROM `productos`          WHERE `id` = @test_prod_id;
DELETE FROM `insumos`            WHERE `codigo` IN ('INS-RES-001', 'INS-PIG-002', 'INS-SOL-003');
DELETE FROM `clientes`           WHERE `id` = @test_cliente_id;

-- 3. INSERTAR DATOS NUEVOS UNO POR UNO (Garantiza integridad de IDs)
-- ---------------------------------------------------------
-- CATÁLOGO DE CLIENTES
-- ---------------------------------------------------------
INSERT INTO `clientes` (`codigo`, `razon_social`, `rfc`, `tipo_cliente`, `estatus`) 
VALUES ('CL-TEST-001', 'Empresa de Prueba S.A.', 'XAXX010101000', 'Empresa', 'Activo');
SET @new_cliente_id = LAST_INSERT_ID();

-- ---------------------------------------------------------
-- INVENTARIO DE INSUMOS (MATERIAS PRIMAS)
-- ---------------------------------------------------------
INSERT INTO `insumos` (`codigo`, `nombre_tecnico`, `unidad_medida`, `stock_actual`, `precio_promedio`, `estatus`) 
VALUES ('INS-RES-001', 'Resina Epóxica Base', 'Kg', 500.00, 120.00, 'Activo');
SET @new_ins1 = LAST_INSERT_ID();

INSERT INTO `insumos` (`codigo`, `nombre_tecnico`, `unidad_medida`, `stock_actual`, `precio_promedio`, `estatus`) 
VALUES ('INS-PIG-002', 'Pigmento Azul Intenso', 'Kg', 50.00, 450.00, 'Activo');
SET @new_ins2 = LAST_INSERT_ID();

INSERT INTO `insumos` (`codigo`, `nombre_tecnico`, `unidad_medida`, `stock_actual`, `precio_promedio`, `estatus`) 
VALUES ('INS-SOL-003', 'Solvente Diluyente X', 'L', 200.00, 85.00, 'Activo');
SET @new_ins3 = LAST_INSERT_ID();

-- ---------------------------------------------------------
-- CATÁLOGO DE PRODUCTOS (FABRICADOS)
-- ---------------------------------------------------------
INSERT INTO `productos` (`codigo`, `nombre`, `categoria_id`, `tipo_producto`, `unidad_venta`, `estatus`) 
VALUES ('PROD-PINT-001', 'Pintura Epóxica Azul 19L', 1, 'Fabricado', 'Cubeta', 'Activo');
SET @new_prod_id = LAST_INSERT_ID();

-- ---------------------------------------------------------
-- FORMULACIONES (VERSIONAMIENTO Y BOM)
-- ---------------------------------------------------------
INSERT INTO `formulaciones` (`producto_id`, `nombre_version`, `version`, `cantidad_producida`, `unidad_produccion`, `es_activa`) 
VALUES (@new_prod_id, 'Fórmula Estándar V1', 1, 19.00, 'L', 1);
SET @new_form_id = LAST_INSERT_ID();

-- Detalle de la Mezcla
INSERT INTO `detalle_formulacion` (`formulacion_id`, `tipo_componente`, `insumo_id`, `cantidad`, `unidad`, `porcentaje`) VALUES 
(@new_form_id, 'Insumo', @new_ins1, 10.00, 'Kg', 65.00),
(@new_form_id, 'Insumo', @new_ins2, 2.00,  'Kg', 15.00),
(@new_form_id, 'Insumo', @new_ins3, 5.00,  'L',  20.00);

-- ---------------------------------------------------------
-- ÓRDENES DE VENTA (DEBEN APARECER EN DASHBOARD)
-- ---------------------------------------------------------
INSERT INTO `ordenes_venta` (`folio`, `cliente_id`, `fecha_orden`, `fecha_creacion`, `estatus`, `total`, `tipo_venta`) 
VALUES ('OV-TEST-001', @new_cliente_id, CURDATE(), NOW(), 'Confirmada', 2500.00, 'Pedido');
SET @new_ov_id = LAST_INSERT_ID();

-- Detalle de Venta vinculado a la fórmula
INSERT INTO `detalle_orden_venta` (`orden_venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`, `formulacion_id`, `formulacion_version`) 
VALUES (@new_ov_id, @new_prod_id, 5.00, 500.00, 2500.00, @new_form_id, '1');

-- ---------------------------------------------------------
-- OBRAS (DEBEN APARECER EN DASHBOARD)
-- ---------------------------------------------------------
INSERT INTO `obras` (`folio`, `nombre`, `cliente_id`, `direccion`, `fecha_creacion`, `estatus`, `total`, `creado_por`) 
VALUES ('OB-TEST-002', 'Remodelación Nave Industrial', @new_cliente_id, 'Calle de Prueba 123, Ciudad de Prueba', NOW(), 'Aprobada', 15000.00, 1);
SET @new_obra_id = LAST_INSERT_ID();

-- Producto en Obra vinculado a la fórmula
INSERT INTO `obras_productos` (`obra_id`, `producto_id`, `cantidad_calculada`, `cantidad_ajustada`, `unidad`, `formulacion_id`, `formulacion_version`, `agregado_por`) 
VALUES (@new_obra_id, @new_prod_id, 10.00, 10.00, 'Cubeta', @new_form_id, '1.0', 1);

-- 4. REACTIVAR RESTRICCIONES
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
