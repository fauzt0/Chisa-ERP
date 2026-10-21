-- Datos de doc/entrenamiento_5 cargados en la base local.
-- No borra nada. Si el folio ya existe, no lo duplica. No incluye CCO-2026-0416 ni ordenes de venta.
-- En el VPS, despues de git pull:
-- mysql -u USUARIO -p NOMBRE_BD < database/entrenamiento_5_cotizaciones.sql
SET NAMES utf8mb4;
SET @db := DATABASE();
CREATE TABLE IF NOT EXISTS cotizaciones_comerciales (
  id INT NOT NULL AUTO_INCREMENT, folio VARCHAR(40) NOT NULL, sucursal_id INT NULL, cliente_id INT NULL,
  obra_id INT NULL, constructora VARCHAR(200) NULL, atencion VARCHAR(150) NULL, obra_nombre VARCHAR(200) NULL,
  ubicacion VARCHAR(255) NULL, pres_ref VARCHAR(180) NULL, fecha DATE NULL, introduccion VARCHAR(500) NULL,
  estatus ENUM('Borrador','Enviada','Aceptada','Rechazada','Cancelada') NOT NULL DEFAULT 'Borrador',
  observaciones TEXT NULL, creado_por INT NULL, fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  parent_id INT NULL, es_consulta TINYINT(1) NOT NULL DEFAULT 0, fecha_estatus DATETIME NULL,
  PRIMARY KEY (id), UNIQUE KEY uk_cco_folio (folio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET @sql := (SELECT IF(COUNT(*)=0, 'ALTER TABLE cotizaciones_comerciales ADD COLUMN parent_id INT NULL', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cotizaciones_comerciales' AND COLUMN_NAME='parent_id');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @sql := (SELECT IF(COUNT(*)=0, 'ALTER TABLE cotizaciones_comerciales ADD COLUMN es_consulta TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cotizaciones_comerciales' AND COLUMN_NAME='es_consulta');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @sql := (SELECT IF(COUNT(*)=0, 'ALTER TABLE cotizaciones_comerciales ADD COLUMN fecha_estatus DATETIME NULL', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cotizaciones_comerciales' AND COLUMN_NAME='fecha_estatus');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
CREATE TABLE IF NOT EXISTS cotizaciones_comerciales_detalle (
  id INT NOT NULL AUTO_INCREMENT, cotizacion_id INT NOT NULL, orden INT NOT NULL DEFAULT 0,
  grupo VARCHAR(160) NULL, concepto TEXT NULL, unidad VARCHAR(40) NULL,
  cantidad DECIMAL(14,2) NOT NULL DEFAULT 0, precio_unitario DECIMAL(14,2) NOT NULL DEFAULT 0,
  importe DECIMAL(14,2) NOT NULL DEFAULT 0,
  tipo ENUM('Producto','Aplicacion','Servicio') NOT NULL DEFAULT 'Servicio', producto_id INT NULL,
  PRIMARY KEY (id), KEY idx_cco_det (cotizacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET @sql := (SELECT IF(COUNT(*)=0, 'ALTER TABLE cotizaciones_comerciales_detalle ADD COLUMN producto_id INT NULL', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cotizaciones_comerciales_detalle' AND COLUMN_NAME='producto_id');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
CREATE TABLE IF NOT EXISTS conceptos_comerciales (
  id INT NOT NULL AUTO_INCREMENT, concepto TEXT NOT NULL, unidad VARCHAR(40) NULL,
  precio_referencia DECIMAL(14,2) NOT NULL DEFAULT 0, modelo VARCHAR(80) NULL, huella CHAR(40) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1, tipo ENUM('Producto','Aplicacion','Servicio') NOT NULL DEFAULT 'Servicio',
  producto_id INT NULL, PRIMARY KEY (id), UNIQUE KEY uk_cc_huella (huella)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS documento_versiones (
  id INT NOT NULL AUTO_INCREMENT, tipo ENUM('cotizacion','orden') NOT NULL, documento_id INT NOT NULL,
  raiz_id INT NOT NULL, padre_id INT NULL, numero INT NOT NULL DEFAULT 1, nota VARCHAR(300) NULL,
  folio VARCHAR(40) NULL, fecha DATETIME NOT NULL, PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-E50CB3DE','HOJA DE CALCULO SUMINISTRO DIF M2',NULL,'XE50CB3DEE6',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='HOJA DE CALCULO SUMINISTRO DIF M2')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='XE50CB3DEE6')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-E50CB3DE');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-14C1C11D','CINETECA NACIONAL',NULL,'X14C1C11D47',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='CINETECA NACIONAL')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X14C1C11D47')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-14C1C11D');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-F3C080F7','BE GRAND DEL VALLE',NULL,'XF3C080F78E',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='BE GRAND DEL VALLE')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='XF3C080F78E')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-F3C080F7');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-84AE2A46','CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.',NULL,'X84AE2A46FF',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X84AE2A46FF')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-84AE2A46');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-97233052','CIUDAD DE MEXICO A 14 DE NOVIEMBRE DEL 2023',NULL,'X9723305233',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='CIUDAD DE MEXICO A 14 DE NOVIEMBRE DEL 2023')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X9723305233')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-97233052');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-CA7D4BA6','CIUDAD DE MEXICO A 2 DE DICIEMBRE DEL 2022',NULL,'XCA7D4BA654',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='CIUDAD DE MEXICO A 2 DE DICIEMBRE DEL 2022')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='XCA7D4BA654')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-CA7D4BA6');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-06D2AEFF','ALICIA OJEDA',NULL,'X06D2AEFFAD',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='ALICIA OJEDA')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X06D2AEFFAD')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-06D2AEFF');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-6E6A1D54','ARQ. RODOLFO CASTILLO HERNANDEZ',NULL,'X6E6A1D5463',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='ARQ. RODOLFO CASTILLO HERNANDEZ')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X6E6A1D5463')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-6E6A1D54');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-6D38BD39','PRODEMEX TUXTLA',NULL,'X6D38BD396C',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='PRODEMEX TUXTLA')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X6D38BD396C')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-6D38BD39');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-4B8A5217','GUMESA IMSS HUIXTLA',NULL,'X4B8A5217E9',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='GUMESA IMSS HUIXTLA')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X4B8A5217E9')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-4B8A5217');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-1EBD83C7','ING. ABRAHAM GALLARDO',NULL,'X1EBD83C77A',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='ING. ABRAHAM GALLARDO')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='X1EBD83C77A')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-1EBD83C7');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-BB9BC2EB','KING OASIS',NULL,'XBB9BC2EBCC',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='KING OASIS')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='XBB9BC2EBCC')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-BB9BC2EB');
INSERT INTO clientes (codigo, razon_social, nombre_comercial, rfc, contacto_nombre, telefono, email, ciudad, estado, tipo_cliente, estatus)
SELECT 'CCO-A624FBB1','RAMSICOM',NULL,'XA624FBB142',NULL,NULL,NULL,NULL,NULL,'Empresa','Activo' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE razon_social='RAMSICOM')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE rfc='XA624FBB142')
  AND NOT EXISTS (SELECT 1 FROM clientes WHERE codigo='CCO-A624FBB1');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0001',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='HOJA DE CALCULO SUMINISTRO DIF M2' LIMIT 1),(SELECT id FROM clientes WHERE rfc='XE50CB3DEE6' LIMIT 1)),NULL,'HOJA DE CALCULO SUMINISTRO DIF M2',NULL,'HOJA DE CALCULO SUMINISTRO DIF M2',NULL,'HOJA DE CALCULO SUMINISTRO DIF M2.xlsx','2026-10-21','Presupuesto de suministro y aplicación.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 01:58:29',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0001');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0002',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='CINETECA NACIONAL' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X14C1C11D47' LIMIT 1)),NULL,'CINETECA NACIONAL',NULL,'CINETECA NACIONAL',NULL,'16553 CINETECA NACIONAL.xlsx','2026-10-21','Presupuesto de suministro y aplicación.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 01:58:29',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0002');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0003',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X84AE2A46FF' LIMIT 1)),NULL,'CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.','ING. HECTOR AGUIRRE CISNEROS','HOSPITAL C.H. 57 IGUALA DE LA INDEPENDENCIA, EDO. DE GUERRERO',NULL,'16489-S BE GRAND DEL VALLE.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 01:58:29',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0003');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0004',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='CIUDAD DE MEXICO A 14 DE NOVIEMBRE DEL 2023' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X9723305233' LIMIT 1)),NULL,'SERAD GLOBAL SOLUTIONS','ING. CESAR BRAVO','SERAD GLOBAL SOLUTIONS',NULL,'16544-S SERAD GLOBAL SOLUTIONS.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 01:58:30',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0004');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0005',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='CIUDAD DE MEXICO A 2 DE DICIEMBRE DEL 2022' LIMIT 1),(SELECT id FROM clientes WHERE rfc='XCA7D4BA654' LIMIT 1)),NULL,'HOSPITAL MEDICA MIA',NULL,'HOSPITAL MEDICA MIA',NULL,'16362-S HOSPITAL MEDICA MIA.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 01:58:30',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0005');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0006',IF((SELECT COUNT(*) FROM sucursales WHERE id=1)>0,1,NULL),COALESCE((SELECT id FROM clientes WHERE razon_social='BE GRAND DEL VALLE' LIMIT 1),(SELECT id FROM clientes WHERE rfc='XF3C080F78E' LIMIT 1)),NULL,'BE GRAND DEL VALLE','Ing. Prueba','Obra de verificacion de interfaz','CDMX','VERIF-UI','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Cancelada',NULL,NULL,'2026-10-21 01:59:22',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0006');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0007',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='ALICIA OJEDA' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X06D2AEFFAD' LIMIT 1)),NULL,'ALICIA OJEDA','ING. CESAR BRAVO','ALICIA OJEDA',NULL,'16370-S ALICIA OJEDA.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0007');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0008',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='ARQ. RODOLFO CASTILLO HERNANDEZ' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X6E6A1D5463' LIMIT 1)),NULL,'ARQ. RODOLFO CASTILLO HERNANDEZ','ING. CESAR BRAVO','ARQ. RODOLFO CASTILLO HERNANDEZ',NULL,'16441-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0008');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0009',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='PRODEMEX TUXTLA' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X6D38BD396C' LIMIT 1)),NULL,'PRODEMEX TUXTLA',NULL,'PRODEMEX TUXTLA',NULL,'16386-S PRODEMEX TUXTLA.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0009');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0010',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='GUMESA IMSS HUIXTLA' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X4B8A5217E9' LIMIT 1)),NULL,'GUMESA IMSS HUIXTLA','ING. CESAR BRAVO','GUMESA IMSS HUIXTLA',NULL,'16466-S GUMESA IMSS HUIXTLA.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0010');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0011',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='ING. ABRAHAM GALLARDO' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X1EBD83C77A' LIMIT 1)),NULL,'ING. ABRAHAM GALLARDO',NULL,'ING. ABRAHAM GALLARDO',NULL,'16501-S ING. ABRAHAM GALLARDO.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0011');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0012',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='ING. ABRAHAM GALLARDO' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X1EBD83C77A' LIMIT 1)),NULL,'ING. ABRAHAM GALLARDO',NULL,'ING. ABRAHAM GALLARDO',NULL,'16432-S ING. ABRAHAM GALLARDO.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0012');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0013',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='ING. ABRAHAM GALLARDO' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X1EBD83C77A' LIMIT 1)),NULL,'ING. ABRAHAM GALLARDO',NULL,'ING. ABRAHAM GALLARDO',NULL,'16482-S ING. ABRAHAM GALLARDO.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0013');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0014',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='KING OASIS' LIMIT 1),(SELECT id FROM clientes WHERE rfc='XBB9BC2EBCC' LIMIT 1)),NULL,'KING OASIS',NULL,'KING OASIS',NULL,'16406-S KING OASIS.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0014');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0015',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='RAMSICOM' LIMIT 1),(SELECT id FROM clientes WHERE rfc='XA624FBB142' LIMIT 1)),NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16373-S RAMSICOM.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0015');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0016',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='RAMSICOM' LIMIT 1),(SELECT id FROM clientes WHERE rfc='XA624FBB142' LIMIT 1)),NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16395-S RAMSICOM.xlsx','2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Cargada desde entrenamiento 5 (archivo ligero)',NULL,'2026-10-21 02:55:26',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0016');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0017',NULL,NULL,NULL,'HOSP IMSS No1 OAXCA',NULL,'HOSP IMSS No1 OAXCA',NULL,'13589 HOSP IMSS No1 OAXCA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0017');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0018',NULL,NULL,NULL,'HOSPITAL GENERAL TEPIC, NAY 1',NULL,'HOSPITAL GENERAL TEPIC, NAY 1',NULL,'16351 HOSPITAL GENERAL TEPIC, NAY 1.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0018');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0019',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16356-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0019');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0020',NULL,NULL,NULL,'Sumato Supervisión y Mantenimiento',NULL,'Sumato Supervisión y Mantenimiento',NULL,'16357-S Sumato Supervisión y Mantenimiento.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0020');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0021',NULL,NULL,NULL,'Sumato Supervisión y Mantenimiento',NULL,'Sumato Supervisión y Mantenimiento',NULL,'16358 Sumato Supervisión y Mantenimiento.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0021');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0022',NULL,NULL,NULL,'PEMCO',NULL,'PEMCO',NULL,'16359-S PEMCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0022');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0023',NULL,NULL,NULL,'ENSENADA',NULL,'ENSENADA',NULL,'16360-S ENSENADA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0023');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0024',NULL,NULL,NULL,'HOSPITAL EL QUEMADO, ACA',NULL,'HOSPITAL EL QUEMADO, ACA',NULL,'16361-S HOSPITAL EL QUEMADO, ACA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0024');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0025',NULL,NULL,NULL,'ISSSTE ZONA BOCA DEL RIO',NULL,'ISSSTE ZONA BOCA DEL RIO',NULL,'16363-S ISSSTE ZONA BOCA DEL RIO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0025');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0026',NULL,NULL,NULL,'C H 30 EN HUEJUTLA DE REYES, HIDALGO',NULL,'C H 30 EN HUEJUTLA DE REYES, HIDALGO',NULL,'16364-S C H 30 EN HUEJUTLA DE REYES, HIDALGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0026');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0027',NULL,NULL,NULL,'Sumato Supervisión y Mantenimiento',NULL,'Sumato Supervisión y Mantenimiento',NULL,'16365 Sumato Supervisión y Mantenimiento.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0027');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0028',NULL,NULL,NULL,'HGR 23 ENSENADA',NULL,'HGR 23 ENSENADA',NULL,'16366-S HGR 23 ENSENADA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0028');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0029',NULL,NULL,NULL,'HOSPITAL ISSSTE EMILIANO ZAPATA',NULL,'HOSPITAL ISSSTE EMILIANO ZAPATA',NULL,'16367 HOSPITAL ISSSTE EMILIANO ZAPATA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0029');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0030',NULL,NULL,NULL,'OOAD CHIAPAS',NULL,'OOAD CHIAPAS',NULL,'16368 OOAD CHIAPAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0030');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0031',NULL,NULL,NULL,'OOAD CHIAPAS',NULL,'OOAD CHIAPAS',NULL,'16369-S OOAD CHIAPAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0031');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0032',NULL,NULL,NULL,'LUZ ESQUIVEL',NULL,'LUZ ESQUIVEL',NULL,'16371-S LUZ ESQUIVEL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0032');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0033',NULL,NULL,NULL,'ARQ. IVAN OJEDA LOPEZ',NULL,'ARQ. IVAN OJEDA LOPEZ',NULL,'16372-S ARQ. IVAN OJEDA LOPEZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0033');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0034',NULL,NULL,NULL,'HOSPITAL HEMODIALISIS AHEOS, TUXTLA',NULL,'HOSPITAL HEMODIALISIS AHEOS, TUXTLA',NULL,'16374 HOSPITAL HEMODIALISIS AHEOS, TUXTLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0034');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0035',NULL,NULL,NULL,'SECRETARIA DE MARINA',NULL,'SECRETARIA DE MARINA',NULL,'16375 SECRETARIA DE MARINA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0035');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0036',NULL,NULL,NULL,'SECRETARIA DE MARINA',NULL,'SECRETARIA DE MARINA',NULL,'16376 SECRETARIA DE MARINA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0036');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0037',NULL,NULL,NULL,'HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA',NULL,'HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA',NULL,'16377-S HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0037');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0038',NULL,NULL,NULL,'SECRETARIA DE MARINA',NULL,'SECRETARIA DE MARINA',NULL,'16378 SECRETARIA DE MARINA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0038');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0039',NULL,NULL,NULL,'SECRETARIA DE MARINA',NULL,'SECRETARIA DE MARINA',NULL,'16379 SECRETARIA DE MARINA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0039');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0040',NULL,NULL,NULL,'OOAD CHIAPAS',NULL,'OOAD CHIAPAS',NULL,'16380-S OOAD CHIAPAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0040');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0041',NULL,NULL,NULL,'HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA',NULL,'HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA',NULL,'16381 HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0041');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0042',NULL,NULL,NULL,'MED DISEÑO CONSTRUCCION Y SUPERVISION',NULL,'MED DISEÑO CONSTRUCCION Y SUPERVISION',NULL,'16382-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0042');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0043',NULL,NULL,NULL,'ASCH',NULL,'ASCH',NULL,'16383 ASCH.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0043');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0044',NULL,NULL,NULL,'ARQ. IVONE TOBILLA AZCANIO',NULL,'ARQ. IVONE TOBILLA AZCANIO',NULL,'16384 ARQ. IVONE TOBILLA AZCANIO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0044');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0045',NULL,NULL,NULL,'SHOP ELLAZ CDMX BOSQUES',NULL,'SHOP ELLAZ CDMX BOSQUES',NULL,'16385 SHOP ELLAZ CDMX BOSQUES.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0045');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0046',NULL,NULL,NULL,'HOSPITAL GENERAL TEPIC, NAY',NULL,'HOSPITAL GENERAL TEPIC, NAY',NULL,'16387 HOSPITAL GENERAL TEPIC, NAY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0046');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0047',NULL,NULL,NULL,'DESARROLLO DEL SURESTE 25',NULL,'DESARROLLO DEL SURESTE 25',NULL,'16388-S DESARROLLO DEL SURESTE 25.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0047');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0048',NULL,NULL,NULL,'ARTURO MONTER ALGARIN',NULL,'ARTURO MONTER ALGARIN',NULL,'16389 ARTURO MONTER ALGARIN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0048');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0049',NULL,NULL,NULL,'HOSP IMSS No1 OAXACA',NULL,'HOSP IMSS No1 OAXACA',NULL,'16389 HOSP IMSS No1 OAXACA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0049');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0050',NULL,NULL,NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16390-S RAMSICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0050');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0051',NULL,NULL,NULL,'YALA SA DE CV',NULL,'YALA SA DE CV',NULL,'16391 YALA SA DE CV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0051');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0052',NULL,NULL,NULL,'YALA SA DE CV',NULL,'YALA SA DE CV',NULL,'16392 YALA SA DE CV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0052');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0053',NULL,NULL,NULL,'SR. MAURICIO GUEVARA',NULL,'SR. MAURICIO GUEVARA',NULL,'16393 SR. MAURICIO GUEVARA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0053');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0054',NULL,NULL,NULL,'GRUPO TRITON',NULL,'GRUPO TRITON',NULL,'16394 GRUPO TRITON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0054');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0055',NULL,NULL,NULL,'SERVICIOS DE SALUD DE DURANGO',NULL,'SERVICIOS DE SALUD DE DURANGO',NULL,'16396 SERVICIOS DE SALUD DE DURANGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0055');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0056',NULL,NULL,NULL,'SERVICIOS DE SALUD DE DURANGO',NULL,'SERVICIOS DE SALUD DE DURANGO',NULL,'16397-S SERVICIOS DE SALUD DE DURANGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0056');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0057',NULL,NULL,NULL,'IMSS 5 DE MAYO TUXTLA GUTIERREZ',NULL,'IMSS 5 DE MAYO TUXTLA GUTIERREZ',NULL,'16398 IMSS 5 DE MAYO TUXTLA GUTIERREZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0057');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0058',NULL,NULL,NULL,'IMSS 5 DE MAYO TUXTLA',NULL,'IMSS 5 DE MAYO TUXTLA',NULL,'16398 IMSS 5 DE MAYO TUXTLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0058');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0059',NULL,NULL,NULL,'SERVICIOS INTEGRALES LACANJA',NULL,'SERVICIOS INTEGRALES LACANJA',NULL,'16399-S SERVICIOS INTEGRALES LACANJA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0059');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0060',NULL,NULL,NULL,'IMSS ZUMPANGO',NULL,'IMSS ZUMPANGO',NULL,'16400-S IMSS ZUMPANGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0060');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0061',NULL,NULL,NULL,'IMSS ZUNPANGO',NULL,'IMSS ZUNPANGO',NULL,'16401 IMSS ZUNPANGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0061');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0062',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16402-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0062');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0063',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO, GRO',NULL,'HOSPITAL NAVAL DE ACAPULCO, GRO',NULL,'16403 HOSPITAL NAVAL DE ACAPULCO, GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0063');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0064',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16404-S HOSPITAL NAVAL DE ACAPULCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0064');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0065',NULL,NULL,NULL,'HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA',NULL,'HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA',NULL,'16405 HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0065');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0066',NULL,NULL,NULL,'KING OASIS',NULL,'KING OASIS',NULL,'16407 KING OASIS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0066');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0067',NULL,NULL,NULL,'HOSPITAL DR. RAYMUNDO ABARCA ALARCON',NULL,'HOSPITAL DR. RAYMUNDO ABARCA ALARCON',NULL,'16408-S HOSPITAL DR. RAYMUNDO ABARCA ALARCON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0067');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0068',NULL,NULL,NULL,'REMODELACION IMSS PALMAS',NULL,'REMODELACION IMSS PALMAS',NULL,'16409-S REMODELACION IMSS PALMAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0068');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0069',NULL,NULL,NULL,'ARTURO MONTER ALGARIN',NULL,'ARTURO MONTER ALGARIN',NULL,'16410 ARTURO MONTER ALGARIN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0069');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0070',NULL,NULL,NULL,'HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS',NULL,'HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS',NULL,'16411 HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0070');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0071',NULL,NULL,NULL,'HOSPITAL DEL IMSS TEPIC',NULL,'HOSPITAL DEL IMSS TEPIC',NULL,'16412-S HOSPITAL DEL IMSS TEPIC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0071');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0072',NULL,NULL,NULL,'HOSPITAL GENERAL DE ACAPULCO, GRO',NULL,'HOSPITAL GENERAL DE ACAPULCO, GRO',NULL,'16413 HOSPITAL GENERAL DE ACAPULCO, GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0072');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0073',NULL,NULL,NULL,'HOSPITAL REGIONAL DE TOLUCA, ISSEMYM',NULL,'HOSPITAL REGIONAL DE TOLUCA, ISSEMYM',NULL,'16414-S HOSPITAL REGIONAL DE TOLUCA, ISSEMYM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0073');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0074',NULL,NULL,NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16415-S RAMSICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0074');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0075',NULL,NULL,NULL,'IMSS RIO COLORADO, SON',NULL,'IMSS RIO COLORADO, SON',NULL,'16417-S IMSS RIO COLORADO, SON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0075');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0076',NULL,NULL,NULL,'SECRETARIA DE MARINA',NULL,'SECRETARIA DE MARINA',NULL,'16418 SECRETARIA DE MARINA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0076');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0077',NULL,NULL,NULL,'HOSPITAL GENERAL XOCO, CDMX',NULL,'HOSPITAL GENERAL XOCO, CDMX',NULL,'16419 HOSPITAL GENERAL XOCO, CDMX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0077');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0078',NULL,NULL,NULL,'GUMESA IMSS HUIXTLA',NULL,'GUMESA IMSS HUIXTLA',NULL,'16420-S GUMESA IMSS HUIXTLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0078');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0079',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16421-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0079');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0080',NULL,NULL,NULL,'HOSPITAL EN MONTERREY',NULL,'HOSPITAL EN MONTERREY',NULL,'16422 HOSPITAL EN MONTERREY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0080');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0081',NULL,NULL,NULL,'HOSPITAL DE TLAJOMULCO',NULL,'HOSPITAL DE TLAJOMULCO',NULL,'16423-S HOSPITAL DE TLAJOMULCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0081');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0082',NULL,NULL,NULL,'HOSPITAL SANTA ROSA LIMA',NULL,'HOSPITAL SANTA ROSA LIMA',NULL,'16424-S HOSPITAL SANTA ROSA LIMA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0082');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0083',NULL,NULL,NULL,'OSCAR ALVAREZ',NULL,'OSCAR ALVAREZ',NULL,'16425-S OSCAR ALVAREZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0083');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0084',NULL,NULL,NULL,'HOSPITAL SANTA ROSA LIMA',NULL,'HOSPITAL SANTA ROSA LIMA',NULL,'16426-S HOSPITAL SANTA ROSA LIMA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0084');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0085',NULL,NULL,NULL,'CLINICA DEL IMSS MONTERREY',NULL,'CLINICA DEL IMSS MONTERREY',NULL,'16427 CLINICA DEL IMSS MONTERREY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0085');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0086',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16428 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0086');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0087',NULL,NULL,NULL,'Sumato Supervisión y Mantenimiento',NULL,'Sumato Supervisión y Mantenimiento',NULL,'16429-S Sumato Supervisión y Mantenimiento.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0087');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0088',NULL,NULL,NULL,'HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA',NULL,'HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA',NULL,'16430 HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0088');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0089',NULL,NULL,NULL,'HOSPITAL NAVAL DE DOS BOCAS, VER',NULL,'HOSPITAL NAVAL DE DOS BOCAS, VER',NULL,'16433 HOSPITAL NAVAL DE DOS BOCAS, VER.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0089');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0090',NULL,NULL,NULL,'HOSPITAL GENERAL DE TEMIXCO 1',NULL,'HOSPITAL GENERAL DE TEMIXCO 1',NULL,'16434 HOSPITAL GENERAL DE TEMIXCO-1.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0090');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0091',NULL,NULL,NULL,'HOSPITAL GENERAL DE TEMIXCO',NULL,'HOSPITAL GENERAL DE TEMIXCO',NULL,'16434 HOSPITAL GENERAL DE TEMIXCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0091');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0092',NULL,NULL,NULL,'16434 1 HOSPITAL GENERAL DE TEMIXCO',NULL,'16434 1 HOSPITAL GENERAL DE TEMIXCO',NULL,'16434-1 HOSPITAL GENERAL DE TEMIXCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0092');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0093',NULL,NULL,NULL,'16434 2 HOSPITAL GENERAL DE TEMIXCO',NULL,'16434 2 HOSPITAL GENERAL DE TEMIXCO',NULL,'16434-2 HOSPITAL GENERAL DE TEMIXCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0093');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0094',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16435 HOSPITAL NAVAL DE ACAPULCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0094');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0095',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO 1',NULL,'HOSPITAL NAVAL DE ACAPULCO 1',NULL,'16436 HOSPITAL NAVAL DE ACAPULCO 1.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0095');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0096',NULL,NULL,NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16437-S RAMSICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0096');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0097',NULL,NULL,NULL,'SHOP ELLAZ TOWN SQUARE METEPEC',NULL,'SHOP ELLAZ TOWN SQUARE METEPEC',NULL,'16438 SHOP ELLAZ TOWN SQUARE METEPEC.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0097');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0098',NULL,NULL,NULL,'MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS',NULL,'MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS',NULL,'16439-S MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0098');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0099',NULL,NULL,NULL,'MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS',NULL,'MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS',NULL,'16440  MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0099');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0100',NULL,NULL,NULL,'ARQ. RODOLFO CASTILLO HERNANDEZ',NULL,'ARQ. RODOLFO CASTILLO HERNANDEZ',NULL,'16442-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0100');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0101',NULL,NULL,NULL,'P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR)',NULL,'P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR)',NULL,'16443-S P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR).XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0101');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0102',NULL,NULL,NULL,'UMF DE HUAUTLA DE JIMENEZ, OAX',NULL,'UMF DE HUAUTLA DE JIMENEZ, OAX',NULL,'16444-S UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0102');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0103',NULL,NULL,NULL,'GRUPO TRITON',NULL,'GRUPO TRITON',NULL,'16445 GRUPO TRITON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0103');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0104',NULL,NULL,NULL,'GRUPO MPV',NULL,'GRUPO MPV',NULL,'16446-S GRUPO MPV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0104');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0105',NULL,NULL,NULL,'GRUPO MPV',NULL,'GRUPO MPV',NULL,'16447  GRUPO MPV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0105');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0106',NULL,NULL,NULL,'SERVICIOS DE MANTENIMIENTO ORGALIM',NULL,'SERVICIOS DE MANTENIMIENTO ORGALIM',NULL,'16448 SERVICIOS DE MANTENIMIENTO ORGALIM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0106');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0107',NULL,NULL,NULL,'COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2',NULL,'COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2',NULL,'16450-S COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0107');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0108',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16451-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0108');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0109',NULL,NULL,NULL,'HOSPITAL COMUNITARIO MANUEL DOBLADO',NULL,'HOSPITAL COMUNITARIO MANUEL DOBLADO',NULL,'16452-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0109');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0110',NULL,NULL,NULL,'HOSPITAL  20 DE NOVIEMBRE',NULL,'HOSPITAL  20 DE NOVIEMBRE',NULL,'16453-S HOSPITAL  20 DE NOVIEMBRE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0110');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0111',NULL,NULL,NULL,'20 DE NOVIEMBRE, ASCH',NULL,'20 DE NOVIEMBRE, ASCH',NULL,'16454 20 DE NOVIEMBRE, ASCH.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0111');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0112',NULL,NULL,NULL,'HOSPITAL COMUNITARIO MANUEL DOBLADO',NULL,'HOSPITAL COMUNITARIO MANUEL DOBLADO',NULL,'16455-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0112');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0113',NULL,NULL,NULL,'UMF2 ISSSTE HECELCHAKAN, CAMP',NULL,'UMF2 ISSSTE HECELCHAKAN, CAMP',NULL,'16456-S UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0113');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0114',NULL,NULL,NULL,'UMF2 ISSSTE HECELCHAKAN, CAMP',NULL,'UMF2 ISSSTE HECELCHAKAN, CAMP',NULL,'16457 UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0114');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0115',NULL,NULL,NULL,'HOSPITAL EN AGUASCALIENTES',NULL,'HOSPITAL EN AGUASCALIENTES',NULL,'16458-S HOSPITAL EN AGUASCALIENTES.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0115');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0116',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16459-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0116');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0117',NULL,NULL,NULL,'SHOP ELLAZ',NULL,'SHOP ELLAZ',NULL,'16460-S SHOP ELLAZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0117');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0118',NULL,NULL,NULL,'SHOP ELLAZ',NULL,'SHOP ELLAZ',NULL,'16461-S SHOP ELLAZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0118');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0119',NULL,NULL,NULL,'UMF 15 DE MONTERREY',NULL,'UMF 15 DE MONTERREY',NULL,'16462 UMF 15 DE MONTERREY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0119');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0120',NULL,NULL,NULL,'16463 COTIZACION',NULL,'16463 COTIZACION',NULL,'16463-COTIZACION.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0120');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0121',NULL,NULL,NULL,'ADRIANA AGUIRRE R.',NULL,'ADRIANA AGUIRRE R.',NULL,'16464 ADRIANA AGUIRRE R..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0121');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0122',NULL,NULL,NULL,'SICOM',NULL,'SICOM',NULL,'16464 SICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0122');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0123',NULL,NULL,NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16465-S RAMSICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0123');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0124',NULL,NULL,NULL,'CONSTRUCCIONES HERSO',NULL,'CONSTRUCCIONES HERSO',NULL,'16467 CONSTRUCCIONES HERSO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0124');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0125',NULL,NULL,NULL,'MED DISEÑO CONSTRUCCION Y SUPERVISION',NULL,'MED DISEÑO CONSTRUCCION Y SUPERVISION',NULL,'16468-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0125');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0126',NULL,NULL,NULL,'SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES',NULL,'SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES',NULL,'16469 SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0126');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0127',NULL,NULL,NULL,'ING. RAUL ZEFERINO',NULL,'ING. RAUL ZEFERINO',NULL,'16470 ING. RAUL ZEFERINO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0127');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0128',NULL,NULL,NULL,'SHOP ELLAZ GALERIAS ATIZAPAN',NULL,'SHOP ELLAZ GALERIAS ATIZAPAN',NULL,'16471 SHOP ELLAZ GALERIAS ATIZAPAN.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0128');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0129',NULL,NULL,NULL,'IMSS 5 DE MAYO',NULL,'IMSS 5 DE MAYO',NULL,'16472 IMSS 5 DE MAYO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0129');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0130',NULL,NULL,NULL,'IMSS CHETUMAL',NULL,'IMSS CHETUMAL',NULL,'16473  IMSS CHETUMAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0130');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0131',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16474-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0131');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0132',NULL,NULL,NULL,'IMSS 5 DE MAYO',NULL,'IMSS 5 DE MAYO',NULL,'16475 IMSS 5 DE MAYO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0132');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0133',NULL,NULL,NULL,'PEMCO',NULL,'PEMCO',NULL,'16476-S PEMCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0133');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0134',NULL,NULL,NULL,'IMSS 5 DE MAYO',NULL,'IMSS 5 DE MAYO',NULL,'16477 IMSS 5 DE MAYO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0134');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0135',NULL,NULL,NULL,'GUMESA IMSS HUIXTLA',NULL,'GUMESA IMSS HUIXTLA',NULL,'16478-S GUMESA IMSS HUIXTLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0135');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0136',NULL,NULL,NULL,'PRODEI',NULL,'PRODEI',NULL,'16479 PRODEI.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0136');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0137',NULL,NULL,NULL,'HGR 216 CAMAS ENSENADA',NULL,'HGR 216 CAMAS ENSENADA',NULL,'16480-S HGR 216 CAMAS ENSENADA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0137');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0138',NULL,NULL,NULL,'Sumato Supervisión y Mantenimiento',NULL,'Sumato Supervisión y Mantenimiento',NULL,'16481-S Sumato Supervisión y Mantenimiento.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0138');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0139',NULL,NULL,NULL,'IMSS BIENESTAR CHIAPAS',NULL,'IMSS BIENESTAR CHIAPAS',NULL,'16483 IMSS BIENESTAR CHIAPAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0139');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0140',NULL,NULL,NULL,'ULSA CONSORCIO URBANISTA',NULL,'ULSA CONSORCIO URBANISTA',NULL,'16484-S ULSA CONSORCIO URBANISTA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0140');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0141',NULL,NULL,NULL,'CLINICA DEL IMSS EN MONTERREY',NULL,'CLINICA DEL IMSS EN MONTERREY',NULL,'16486 CLINICA DEL IMSS EN MONTERREY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0141');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0142',NULL,NULL,NULL,'INCOP',NULL,'INCOP',NULL,'16487 INCOP.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0142');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0143',NULL,NULL,NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16488-S RAMSICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0143');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0144',NULL,NULL,NULL,'ARQ. VICTORIA  PEREZ',NULL,'ARQ. VICTORIA  PEREZ',NULL,'16490-S ARQ. VICTORIA  PEREZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0144');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0145',NULL,NULL,NULL,'SHOP ELLAZ SAN AGUSTIN, MTY',NULL,'SHOP ELLAZ SAN AGUSTIN, MTY',NULL,'16491-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0145');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0146',NULL,NULL,NULL,'JAVIER CEJUDO',NULL,'JAVIER CEJUDO',NULL,'16492 JAVIER CEJUDO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0146');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0147',NULL,NULL,NULL,'DICOREMA ARQUITECTO',NULL,'DICOREMA ARQUITECTO',NULL,'16493-S DICOREMA ARQUITECTO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0147');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0148',NULL,NULL,NULL,'CONSTRUCTORA RECO',NULL,'CONSTRUCTORA RECO',NULL,'16494-S CONSTRUCTORA RECO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0148');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0149',NULL,NULL,NULL,'OYM, S.A DE C.V',NULL,'OYM, S.A DE C.V',NULL,'16494-S OYM, S.A DE C.V.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0149');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0150',NULL,NULL,NULL,'HGZ 10 MANZANILLO',NULL,'HGZ 10 MANZANILLO',NULL,'16495-S HGZ 10 MANZANILLO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0150');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0151',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16496-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0151');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0152',NULL,NULL,NULL,'ARQ. VICTORIA  PEREZ',NULL,'ARQ. VICTORIA  PEREZ',NULL,'16497-S ARQ. VICTORIA  PEREZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0152');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0153',NULL,NULL,NULL,'SHOP ELLAZ ZONA ROSA',NULL,'SHOP ELLAZ ZONA ROSA',NULL,'16498 SHOP ELLAZ ZONA ROSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0153');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0154',NULL,NULL,NULL,'GRUPO TRITON',NULL,'GRUPO TRITON',NULL,'16499 GRUPO TRITON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0154');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0155',NULL,NULL,NULL,'EDGAR CONSTANTINO',NULL,'EDGAR CONSTANTINO',NULL,'16502 EDGAR CONSTANTINO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0155');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0156',NULL,NULL,NULL,'PRODEMEX',NULL,'PRODEMEX',NULL,'16503 PRODEMEX.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0156');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0157',NULL,NULL,NULL,'PRODEMEX',NULL,'PRODEMEX',NULL,'16504-S PRODEMEX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0157');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0158',NULL,NULL,NULL,'CONCRETO Y ESPACIO URBANO,SON',NULL,'CONCRETO Y ESPACIO URBANO,SON',NULL,'16505 CONCRETO Y ESPACIO URBANO,SON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0158');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0159',NULL,NULL,NULL,'HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON',NULL,'HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON',NULL,'16506-S HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0159');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0160',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16507 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0160');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0161',NULL,NULL,NULL,'IMSS COLIMA',NULL,'IMSS COLIMA',NULL,'16508-S IMSS COLIMA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0161');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0162',NULL,NULL,NULL,'HOSPITAL 20 DE NOVIEMBRE GERFLOR',NULL,'HOSPITAL 20 DE NOVIEMBRE GERFLOR',NULL,'16509  HOSPITAL 20 DE NOVIEMBRE GERFLOR.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0162');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0163',NULL,NULL,NULL,'HOSPITAL 20 DE NOVIEMBRE POLYFLOR',NULL,'HOSPITAL 20 DE NOVIEMBRE POLYFLOR',NULL,'16510  HOSPITAL 20 DE NOVIEMBRE POLYFLOR.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0163');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0164',NULL,NULL,NULL,'RAMSICOM',NULL,'RAMSICOM',NULL,'16511-S RAMSICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0164');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0165',NULL,NULL,NULL,'DESARROLLO DEL SURESTE 25',NULL,'DESARROLLO DEL SURESTE 25',NULL,'16512-S DESARROLLO DEL SURESTE 25.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0165');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0166',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16513 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0166');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0167',NULL,NULL,NULL,'PRODEMEX GUANAJUATO',NULL,'PRODEMEX GUANAJUATO',NULL,'16514-S PRODEMEX GUANAJUATO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0167');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0168',NULL,NULL,NULL,'CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V.',NULL,'CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V.',NULL,'16515-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0168');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0169',NULL,NULL,NULL,'ARQ LAURA IVETTE',NULL,'ARQ LAURA IVETTE',NULL,'16516 ARQ LAURA IVETTE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0169');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0170',NULL,NULL,NULL,'CECAN XALAPA',NULL,'CECAN XALAPA',NULL,'16517 CECAN XALAPA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0170');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0171',NULL,NULL,NULL,'JALAPA, VER',NULL,'JALAPA, VER',NULL,'16518 JALAPA, VER.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0171');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0172',NULL,NULL,NULL,'HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL)',NULL,'HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL)',NULL,'16519 HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL).xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0172');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0173',NULL,NULL,NULL,'ING. ABRAHAM GALLARDO',NULL,'ING. ABRAHAM GALLARDO',NULL,'16520-S ING. ABRAHAM GALLARDO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0173');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0174',NULL,NULL,NULL,'20 DE NOVIEMBRE, ASCH',NULL,'20 DE NOVIEMBRE, ASCH',NULL,'16521 20 DE NOVIEMBRE, ASCH.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0174');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0175',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16522-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0175');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0176',NULL,NULL,NULL,'16523  ARQ. JESUS IGLESIAS CORNELIO',NULL,'16523  ARQ. JESUS IGLESIAS CORNELIO',NULL,'16523- ARQ. JESUS IGLESIAS CORNELIO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0176');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0177',NULL,NULL,NULL,'UAM AZCAPOTZALCO',NULL,'UAM AZCAPOTZALCO',NULL,'16524 UAM AZCAPOTZALCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0177');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0178',NULL,NULL,NULL,'UAM AZCAPOTZALCO',NULL,'UAM AZCAPOTZALCO',NULL,'16525 UAM AZCAPOTZALCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0178');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0179',NULL,NULL,NULL,'INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES',NULL,'INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES',NULL,'16526  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0179');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0180',NULL,NULL,NULL,'ARQ. IVONE TOBILLA AZCANIO',NULL,'ARQ. IVONE TOBILLA AZCANIO',NULL,'16527 ARQ. IVONE TOBILLA AZCANIO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0180');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0181',NULL,NULL,NULL,'MED DISEÑO CONSTRUCCION Y SUPERVISION',NULL,'MED DISEÑO CONSTRUCCION Y SUPERVISION',NULL,'16528-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0181');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0182',NULL,NULL,NULL,'CLINICA 23 DEL IMSS',NULL,'CLINICA 23 DEL IMSS',NULL,'16529 CLINICA 23 DEL IMSS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0182');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0183',NULL,NULL,NULL,'CLINICA 23 DEL IMSS',NULL,'CLINICA 23 DEL IMSS',NULL,'16530-S CLINICA 23 DEL IMSS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0183');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0184',NULL,NULL,NULL,'IMSS EN OCOZOCUAUTLA, CHIA',NULL,'IMSS EN OCOZOCUAUTLA, CHIA',NULL,'16531 IMSS EN OCOZOCUAUTLA, CHIA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0184');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0185',NULL,NULL,NULL,'CINETECA NACIONAL CHAPULTEPEC',NULL,'CINETECA NACIONAL CHAPULTEPEC',NULL,'16532 CINETECA NACIONAL CHAPULTEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0185');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0186',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0186');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0187',NULL,NULL,NULL,'16533 1 HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533 1 HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533-1 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0187');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0188',NULL,NULL,NULL,'16533 2 HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533 2 HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533-2 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0188');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0189',NULL,NULL,NULL,'16533 3 HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533 3 HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16533-3 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0189');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0190',NULL,NULL,NULL,'DESARROLLO DEL SURESTE',NULL,'DESARROLLO DEL SURESTE',NULL,'16534 DESARROLLO DEL SURESTE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0190');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0191',NULL,NULL,NULL,'CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V.',NULL,'CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V.',NULL,'16535-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0191');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0192',NULL,NULL,NULL,'DESARROLLO DEL SURESTE 25',NULL,'DESARROLLO DEL SURESTE 25',NULL,'16536-S DESARROLLO DEL SURESTE 25.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0192');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0193',NULL,NULL,NULL,'SICOM',NULL,'SICOM',NULL,'16537 SICOM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0193');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0194',NULL,NULL,NULL,'CONSTRUCTORA EMACO',NULL,'CONSTRUCTORA EMACO',NULL,'16538 CONSTRUCTORA EMACO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0194');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0195',NULL,NULL,NULL,'INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES',NULL,'INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES',NULL,'16539-S  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0195');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0196',NULL,NULL,NULL,'1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES',NULL,'1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES',NULL,'16539-S -1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0196');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0197',NULL,NULL,NULL,'MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN',NULL,'MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN',NULL,'16540-S MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0197');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0198',NULL,NULL,NULL,'SHOP ELLAZ',NULL,'SHOP ELLAZ',NULL,'16541-S SHOP ELLAZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0198');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0199',NULL,NULL,NULL,'IMSS CHETUMAL DESC',NULL,'IMSS CHETUMAL DESC',NULL,'16542-S IMSS CHETUMAL DESC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0199');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0200',NULL,NULL,NULL,'IMSS CHETUMAL',NULL,'IMSS CHETUMAL',NULL,'16542-S IMSS CHETUMAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0200');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0201',NULL,NULL,NULL,'ANMED SA DE CV',NULL,'ANMED SA DE CV',NULL,'16543-S ANMED SA DE CV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0201');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0202',NULL,NULL,NULL,'SHOP ELLAZ SAN AGUSTIN, MTY',NULL,'SHOP ELLAZ SAN AGUSTIN, MTY',NULL,'16545-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0202');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0203',NULL,NULL,NULL,'SHOP ELLAZ SAN AGUSTIN, MTY',NULL,'SHOP ELLAZ SAN AGUSTIN, MTY',NULL,'16546 SHOP ELLAZ SAN AGUSTIN, MTY.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0203');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0204',NULL,NULL,NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC',NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC',NULL,'16547-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0204');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0205',NULL,NULL,NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA',NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA',NULL,'16548-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0205');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0206',NULL,NULL,NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA',NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA',NULL,'16549 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0206');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0207',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16550 HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0207');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0208',NULL,NULL,NULL,'HOSPITAL ISSSTE CHILPANCINGO, GRO',NULL,'HOSPITAL ISSSTE CHILPANCINGO, GRO',NULL,'16551 HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0208');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0209',NULL,NULL,NULL,'HOSPITAL ISSSTE CHILPANCINGO, GRO',NULL,'HOSPITAL ISSSTE CHILPANCINGO, GRO',NULL,'16552-S HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0209');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0210',NULL,NULL,NULL,'CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V.',NULL,'CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V.',NULL,'16554-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0210');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0211',NULL,NULL,NULL,'ING EDUARDO FLORES, PUEBLA',NULL,'ING EDUARDO FLORES, PUEBLA',NULL,'16555-S ING EDUARDO FLORES, PUEBLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0211');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0212',NULL,NULL,NULL,'ING EDUARDO FLORES, PUEBLA DESC',NULL,'ING EDUARDO FLORES, PUEBLA DESC',NULL,'16556-S ING EDUARDO FLORES, PUEBLA DESC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0212');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0213',NULL,NULL,NULL,'ING EDUARDO FLORES, PUEBLA',NULL,'ING EDUARDO FLORES, PUEBLA',NULL,'16556-S ING EDUARDO FLORES, PUEBLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0213');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0214',NULL,NULL,NULL,'SHOP ELLAZ',NULL,'SHOP ELLAZ',NULL,'16557-S SHOP ELLAZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0214');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0215',NULL,NULL,NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA',NULL,'HOSPITAL GENERAL DE PABELLON DE ARTEAGA',NULL,'16558 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0215');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0216',NULL,NULL,NULL,'CLINICA DEL IMSS MONTERREY',NULL,'CLINICA DEL IMSS MONTERREY',NULL,'16559 CLINICA DEL IMSS MONTERREY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0216');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0217',NULL,NULL,NULL,'HOSPITAL DEL IMSS MONTERREY 1',NULL,'HOSPITAL DEL IMSS MONTERREY 1',NULL,'16559 HOSPITAL DEL IMSS MONTERREY 1.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0217');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0218',NULL,NULL,NULL,'HOSPITAL DEL IMSS MTY',NULL,'HOSPITAL DEL IMSS MTY',NULL,'16560 HOSPITAL DEL IMSS MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0218');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0219',NULL,NULL,NULL,'CLINICA DEL IMSS MTY',NULL,'CLINICA DEL IMSS MTY',NULL,'16561-S CLINICA DEL IMSS MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0219');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0220',NULL,NULL,NULL,'CLINICA EN GUASAVE',NULL,'CLINICA EN GUASAVE',NULL,'16563-S CLINICA EN GUASAVE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0220');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0221',NULL,NULL,NULL,'UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO',NULL,'UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO',NULL,'16564 UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0221');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0222',NULL,NULL,NULL,'20 DE NOVIEMBRE, ASCH',NULL,'20 DE NOVIEMBRE, ASCH',NULL,'16565 20 DE NOVIEMBRE, ASCH.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0222');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0223',NULL,NULL,NULL,'HOSPITAL IGNACIO ZARAGOZA, ASCH',NULL,'HOSPITAL IGNACIO ZARAGOZA, ASCH',NULL,'16565 HOSPITAL IGNACIO ZARAGOZA, ASCH.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0223');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0224',NULL,NULL,NULL,'2G INGENIERIA Y ARQUITECTURA',NULL,'2G INGENIERIA Y ARQUITECTURA',NULL,'16566 2G INGENIERIA Y ARQUITECTURA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0224');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0225',NULL,NULL,NULL,'DIGITAL GG MEXICO',NULL,'DIGITAL GG MEXICO',NULL,'16567-S DIGITAL GG MEXICO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0225');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0226',NULL,NULL,NULL,'ISSSTE VASCO DE QUIROGA',NULL,'ISSSTE VASCO DE QUIROGA',NULL,'16568-S ISSSTE VASCO DE QUIROGA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0226');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0227',NULL,NULL,NULL,'ISSSTE VASCO DE QUIROGA',NULL,'ISSSTE VASCO DE QUIROGA',NULL,'16569 ISSSTE VASCO DE QUIROGA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0227');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0228',NULL,NULL,NULL,'16569 1 ISSSTE VASCO DE QUIROGA',NULL,'16569 1 ISSSTE VASCO DE QUIROGA',NULL,'16569-1 ISSSTE VASCO DE QUIROGA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0228');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0229',NULL,NULL,NULL,'PRODEMEX TUXTLA',NULL,'PRODEMEX TUXTLA',NULL,'16570-S PRODEMEX TUXTLA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0229');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0230',NULL,NULL,NULL,'S HOSP CRUZ AZUL, LAGUNAS OAX',NULL,'S HOSP CRUZ AZUL, LAGUNAS OAX',NULL,'16571 - S HOSP CRUZ AZUL, LAGUNAS OAX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0230');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0231',NULL,NULL,NULL,'HOSP CRUZ AZUL, LAGUNAS OAX',NULL,'HOSP CRUZ AZUL, LAGUNAS OAX',NULL,'16571 HOSP CRUZ AZUL, LAGUNAS OAX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0231');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0232',NULL,NULL,NULL,'SHOP ELLAZ',NULL,'SHOP ELLAZ',NULL,'16572-S SHOP ELLAZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0232');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0233',NULL,NULL,NULL,'BODEGA , QRO',NULL,'BODEGA , QRO',NULL,'16573 BODEGA , QRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0233');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0234',NULL,NULL,NULL,'JAIME BAUTISTA',NULL,'JAIME BAUTISTA',NULL,'16574-S JAIME BAUTISTA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0234');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0235',NULL,NULL,NULL,'HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'16575 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0235');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0236',NULL,NULL,NULL,'CLINICA DEL IMSS MTY',NULL,'CLINICA DEL IMSS MTY',NULL,'16576 CLINICA DEL IMSS MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0236');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0237',NULL,NULL,NULL,'CLINICA DEL IMSS MTY',NULL,'CLINICA DEL IMSS MTY',NULL,'16577-S CLINICA DEL IMSS MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0237');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0238',NULL,NULL,NULL,'REYNA MENDOZA',NULL,'REYNA MENDOZA',NULL,'16578 REYNA MENDOZA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0238');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0239',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO, DGO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO, DGO',NULL,'16579 HOSPITAL DE SANTIAGO PAPASQUIARO, DGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0239');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0240',NULL,NULL,NULL,'CLINICA DEL IMSS 15 MTY',NULL,'CLINICA DEL IMSS 15 MTY',NULL,'16580 CLINICA DEL IMSS 15 MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0240');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0241',NULL,NULL,NULL,'CLINICA DEL IMSS 15 MTY',NULL,'CLINICA DEL IMSS 15 MTY',NULL,'16581-S CLINICA DEL IMSS 15 MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0241');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0242',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO,DGO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO,DGO',NULL,'16582 HOSPITAL DE SANTIAGO PAPASQUIARO,DGO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0242');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0243',NULL,NULL,NULL,'CLINICA DEL IMSS 15 DE MTY',NULL,'CLINICA DEL IMSS 15 DE MTY',NULL,'16583 CLINICA DEL IMSS 15 DE MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0243');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0244',NULL,NULL,NULL,'CLINICA DEL IMSS 15 DE MTY',NULL,'CLINICA DEL IMSS 15 DE MTY',NULL,'16584-S CLINICA DEL IMSS 15 DE MTY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0244');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0245',NULL,NULL,NULL,'RONCES ZAGAL ARQUITECTOS & ASOCIADOS',NULL,'RONCES ZAGAL ARQUITECTOS & ASOCIADOS',NULL,'16585 RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0245');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0246',NULL,NULL,NULL,'RONCES ZAGAL ARQUITECTOS & ASOCIADOS',NULL,'RONCES ZAGAL ARQUITECTOS & ASOCIADOS',NULL,'16586-S RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0246');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0247',NULL,NULL,NULL,'PARK RESERVE',NULL,'PARK RESERVE',NULL,'16587 PARK RESERVE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0247');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0248',NULL,NULL,NULL,'CASA HABITACION TEXCOCO',NULL,'CASA HABITACION TEXCOCO',NULL,'16588-S CASA HABITACION TEXCOCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0248');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0249',NULL,NULL,NULL,'UAM UNIDAD XOCHIMILCO',NULL,'UAM UNIDAD XOCHIMILCO',NULL,'16589 UAM UNIDAD XOCHIMILCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0249');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0250',NULL,NULL,NULL,'UAM XOCHIMILCO',NULL,'UAM XOCHIMILCO',NULL,'16589 UAM XOCHIMILCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0250');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0251',NULL,NULL,NULL,'HOSPITAL IMSS DE MORELIA',NULL,'HOSPITAL IMSS DE MORELIA',NULL,'16590 HOSPITAL IMSS DE MORELIA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0251');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0252',NULL,NULL,NULL,'HOSPITAL EN HERMOSILLO, SON',NULL,'HOSPITAL EN HERMOSILLO, SON',NULL,'16591 HOSPITAL EN HERMOSILLO, SON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0252');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0253',NULL,NULL,NULL,'HOSPITAL EN HERMOSILLO, SON',NULL,'HOSPITAL EN HERMOSILLO, SON',NULL,'16592-S HOSPITAL EN HERMOSILLO, SON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0253');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0254',NULL,NULL,NULL,'PROMOCAT',NULL,'PROMOCAT',NULL,'16593 PROMOCAT.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0254');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0255',NULL,NULL,NULL,'HOSPITAL DE CELAYA',NULL,'HOSPITAL DE CELAYA',NULL,'16595 HOSPITAL DE CELAYA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0255');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0256',NULL,NULL,NULL,'HOSPITAL SAN LUIS RIO COLORADO,SON',NULL,'HOSPITAL SAN LUIS RIO COLORADO,SON',NULL,'16597-S HOSPITAL SAN LUIS RIO COLORADO,SON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0256');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0257',NULL,NULL,NULL,'INSTITUTO NACIONAL DE NEUROLOGIA',NULL,'INSTITUTO NACIONAL DE NEUROLOGIA',NULL,'16598 INSTITUTO NACIONAL DE NEUROLOGIA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0257');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0258',NULL,NULL,NULL,'S 1 FARMACIA  CRUZ AZUL, TULA',NULL,'S 1 FARMACIA  CRUZ AZUL, TULA',NULL,'16599 - S 1 FARMACIA  CRUZ AZUL, TULA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0258');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0259',NULL,NULL,NULL,'S FARMACIA  CRUZ AZUL, TULA',NULL,'S FARMACIA  CRUZ AZUL, TULA',NULL,'16599 - S FARMACIA  CRUZ AZUL, TULA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0259');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0260',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16600 HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0260');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0261',NULL,NULL,NULL,'HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO',NULL,'HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO',NULL,'16601 HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0261');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0262',NULL,NULL,NULL,'HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO',NULL,'HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO',NULL,'16602-S HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0262');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0263',NULL,NULL,NULL,'HOSPITAL COMUNITARIO ROMITA, GTO',NULL,'HOSPITAL COMUNITARIO ROMITA, GTO',NULL,'16603 HOSPITAL COMUNITARIO ROMITA, GTO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0263');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0264',NULL,NULL,NULL,'POLY GOON ESTUDIO',NULL,'POLY GOON ESTUDIO',NULL,'16604-S  POLY GOON ESTUDIO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0264');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0265',NULL,NULL,NULL,'HOSPITAL EN CHILPANCINGO, GRO',NULL,'HOSPITAL EN CHILPANCINGO, GRO',NULL,'16605 HOSPITAL EN CHILPANCINGO, GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0265');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0266',NULL,NULL,NULL,'HOSPITAL EN CHILPANCINGO, GRO',NULL,'HOSPITAL EN CHILPANCINGO, GRO',NULL,'16606-S HOSPITAL EN CHILPANCINGO, GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0266');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0267',NULL,NULL,NULL,'HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO',NULL,'HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO',NULL,'16607 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0267');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0268',NULL,NULL,NULL,'HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'16608 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0268');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0269',NULL,NULL,NULL,'16608 1 HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'16608 1 HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'16608-1 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0269');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0270',NULL,NULL,NULL,'HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'HOSPITAL DEL ISSSTE TEHUANTEPEC',NULL,'16609-S HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0270');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0271',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16610-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0271');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0272',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16611 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0272');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0273',NULL,NULL,NULL,'PREFIMAX SA DE CV',NULL,'PREFIMAX SA DE CV',NULL,'16612 PREFIMAX SA DE CV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0273');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0274',NULL,NULL,NULL,'PREFIMAX SA DE CV',NULL,'PREFIMAX SA DE CV',NULL,'16613-S PREFIMAX SA DE CV.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0274');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0275',NULL,NULL,NULL,'UMF 15 DE MONTERREY',NULL,'UMF 15 DE MONTERREY',NULL,'16614 UMF 15 DE MONTERREY.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0275');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0276',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'16615 HOSPITAL INFANTIL NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0276');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0277',NULL,NULL,NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'HOSPITAL DE SANTIAGO PAPASQUIARO',NULL,'16616 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0277');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0278',NULL,NULL,NULL,'HELIPUERTO',NULL,'HELIPUERTO',NULL,'16617 HELIPUERTO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0278');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0279',NULL,NULL,NULL,'MAUVAN',NULL,'MAUVAN',NULL,'16618-S  MAUVAN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0279');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0280',NULL,NULL,NULL,'16619 1 CUAUTITLAN IZCALLI',NULL,'16619 1 CUAUTITLAN IZCALLI',NULL,'16619-1 CUAUTITLAN IZCALLI.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0280');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0281',NULL,NULL,NULL,'CLINICA ESTETICA',NULL,'CLINICA ESTETICA',NULL,'16620-S CLINICA ESTETICA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0281');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0282',NULL,NULL,NULL,'GAE SOLUCIONES CONSTRUCTIVAS',NULL,'GAE SOLUCIONES CONSTRUCTIVAS',NULL,'16621-S GAE SOLUCIONES CONSTRUCTIVAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0282');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0283',NULL,NULL,NULL,'GAE SOLUCIONES CONSTRUCTIVAS',NULL,'GAE SOLUCIONES CONSTRUCTIVAS',NULL,'16622  GAE SOLUCIONES CONSTRUCTIVAS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0283');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0284',NULL,NULL,NULL,'CLINICA ESTETICA',NULL,'CLINICA ESTETICA',NULL,'16623 CLINICA ESTETICA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0284');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0285',NULL,NULL,NULL,'SINDICATO NACIONAL DE TRABAJADORES DEL IMSS',NULL,'SINDICATO NACIONAL DE TRABAJADORES DEL IMSS',NULL,'16624-S SINDICATO NACIONAL DE TRABAJADORES DEL IMSS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0285');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0286',NULL,NULL,NULL,'HOSPITAL 20 DE NOVIEMBRE',NULL,'HOSPITAL 20 DE NOVIEMBRE',NULL,'16625 HOSPITAL 20 DE NOVIEMBRE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0286');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0287',NULL,NULL,NULL,'16625 1 HOSPITAL 20 DE NOVIEMBRE',NULL,'16625 1 HOSPITAL 20 DE NOVIEMBRE',NULL,'16625-1 HOSPITAL 20 DE NOVIEMBRE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0287');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0288',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'16628 HOSPITAL INFANTIL NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0288');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0289',NULL,NULL,NULL,'16628 1 HOSPITAL INFANTIL NVO LEON',NULL,'16628 1 HOSPITAL INFANTIL NVO LEON',NULL,'16628-1 HOSPITAL INFANTIL NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0289');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0290',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16629 HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0290');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0291',NULL,NULL,NULL,'16629 1 HOSPITAL INFANTIL DE NVO LEON',NULL,'16629 1 HOSPITAL INFANTIL DE NVO LEON',NULL,'16629-1 HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0291');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0292',NULL,NULL,NULL,'16629 2 HOSPITAL INFANTIL DE NVO LEON',NULL,'16629 2 HOSPITAL INFANTIL DE NVO LEON',NULL,'16629-2 HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0292');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0293',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16629-C HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0293');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0294',NULL,NULL,NULL,'COGUTSA MATERIALES',NULL,'COGUTSA MATERIALES',NULL,'16630-S COGUTSA MATERIALES.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0294');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0295',NULL,NULL,NULL,'CLINICA ESTETICA',NULL,'CLINICA ESTETICA',NULL,'16631-S CLINICA ESTETICA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0295');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0296',NULL,NULL,NULL,'HOSPITAL PABELLON',NULL,'HOSPITAL PABELLON',NULL,'16631-S HOSPITAL PABELLON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0296');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0297',NULL,NULL,NULL,'HOSPITAL PABELLON',NULL,'HOSPITAL PABELLON',NULL,'16632 HOSPITAL PABELLON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0297');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0298',NULL,NULL,NULL,'UMF DE HUAUTLA DE JIMENEZ, OAX',NULL,'UMF DE HUAUTLA DE JIMENEZ, OAX',NULL,'16633 UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0298');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0299',NULL,NULL,NULL,'ACABADOS LHOGAR',NULL,'ACABADOS LHOGAR',NULL,'16634-S ACABADOS LHOGAR.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0299');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0300',NULL,NULL,NULL,'HOSPITAL DE SAN LUIS RIO COLORDO',NULL,'HOSPITAL DE SAN LUIS RIO COLORDO',NULL,'16634-S HOSPITAL DE SAN LUIS RIO COLORDO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0300');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0301',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16635 HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0301');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0302',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16635-C HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0302');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0303',NULL,NULL,NULL,'MARCELINO ONOFRE',NULL,'MARCELINO ONOFRE',NULL,'16636-S  MARCELINO ONOFRE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0303');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0304',NULL,NULL,NULL,'HOSPITAL DE CELAYA',NULL,'HOSPITAL DE CELAYA',NULL,'16637 HOSPITAL DE CELAYA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0304');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0305',NULL,NULL,NULL,'HOSPITAL DE CELAYA',NULL,'HOSPITAL DE CELAYA',NULL,'16638 HOSPITAL DE CELAYA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0305');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0306',NULL,NULL,NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'HOSPITAL INFANTIL DE NVO LEON',NULL,'16639-S HOSPITAL INFANTIL DE NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0306');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0307',NULL,NULL,NULL,'CLINICA DEL IMSS STA BARBARA, SLW',NULL,'CLINICA DEL IMSS STA BARBARA, SLW',NULL,'16640-S CLINICA DEL IMSS STA BARBARA, SLW.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0307');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0308',NULL,NULL,NULL,'CONSTRUCCIONES Y SERVICIOS GIMON',NULL,'CONSTRUCCIONES Y SERVICIOS GIMON',NULL,'16641-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0308');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0309',NULL,NULL,NULL,'ING PEDRO LUIS OLAN',NULL,'ING PEDRO LUIS OLAN',NULL,'16642 ING PEDRO LUIS OLAN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0309');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0310',NULL,NULL,NULL,'ING PEDRO LUIS OLAN',NULL,'ING PEDRO LUIS OLAN',NULL,'16643-S ING PEDRO LUIS OLAN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0310');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0311',NULL,NULL,NULL,'HOSPITAL KUMATE CHUMATO',NULL,'HOSPITAL KUMATE CHUMATO',NULL,'16644  HOSPITAL KUMATE CHUMATO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0311');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0312',NULL,NULL,NULL,'HOSPITAL EN METEPEC',NULL,'HOSPITAL EN METEPEC',NULL,'16644-S HOSPITAL EN METEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0312');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0313',NULL,NULL,NULL,'HOSPITAL EN ACAPULCO',NULL,'HOSPITAL EN ACAPULCO',NULL,'16645-S HOSPITAL EN ACAPULCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0313');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0314',NULL,NULL,NULL,'HOSPITAL EN METEPEC',NULL,'HOSPITAL EN METEPEC',NULL,'16646 HOSPITAL EN METEPEC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0314');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0315',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16647-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0315');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0316',NULL,NULL,NULL,'INTERCENTURION',NULL,'INTERCENTURION',NULL,'16648-S INTERCENTURION.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0316');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0317',NULL,NULL,NULL,'ISSSTE TAMPICO',NULL,'ISSSTE TAMPICO',NULL,'16649 ISSSTE TAMPICO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0317');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0318',NULL,NULL,NULL,'HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO',NULL,'HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO',NULL,'16650 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0318');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0319',NULL,NULL,NULL,'ALICIA OJEDA',NULL,'ALICIA OJEDA',NULL,'16651-S ALICIA OJEDA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0319');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0320',NULL,NULL,NULL,'ACABADOS LHOGAR',NULL,'ACABADOS LHOGAR',NULL,'16652 ACABADOS LHOGAR.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0320');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0321',NULL,NULL,NULL,'SHOP ELLAZ CDMX ARCOS I',NULL,'SHOP ELLAZ CDMX ARCOS I',NULL,'16653 SHOP ELLAZ CDMX ARCOS I.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0321');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0322',NULL,NULL,NULL,'HOSPITAL DE 20 CAMAS ZITACUARO, MIC',NULL,'HOSPITAL DE 20 CAMAS ZITACUARO, MIC',NULL,'16654 HOSPITAL DE 20 CAMAS ZITACUARO, MIC.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0322');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0323',NULL,NULL,NULL,'HOSPITAL DEL NIÑO EN NL',NULL,'HOSPITAL DEL NIÑO EN NL',NULL,'16655 HOSPITAL DEL NIÑO EN NL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0323');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0324',NULL,NULL,NULL,'HOSPITAL DEL NIÑO EN NL',NULL,'HOSPITAL DEL NIÑO EN NL',NULL,'16656-S HOSPITAL DEL NIÑO EN NL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0324');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0325',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS',NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS',NULL,'16657 HOSPITAL INFANTIL NVO LEON ZOCLOS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0325');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0326',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'16657 HOSPITAL INFANTIL NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0326');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0327',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS',NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS',NULL,'16657 HOSPITAL INFANTIL NVO_LEON_ZOCLOS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0327');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0328',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS 1',NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS 1',NULL,'16658 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 1.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0328');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0329',NULL,NULL,NULL,'JONATHAN GONZALEZ',NULL,'JONATHAN GONZALEZ',NULL,'16658-S JONATHAN GONZALEZ.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0329');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0330',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS 2',NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS 2',NULL,'16659 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 2.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0330');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0331',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS 3',NULL,'HOSPITAL INFANTIL NVO LEON ZOCLOS 3',NULL,'16660 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 3.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0331');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0332',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON GERFLOR',NULL,'HOSPITAL INFANTIL NVO LEON GERFLOR',NULL,'16661 HOSPITAL INFANTIL NVO LEON GERFLOR.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0332');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0333',NULL,NULL,NULL,'CONSTRUCTORA RECO',NULL,'CONSTRUCTORA RECO',NULL,'16662-S CONSTRUCTORA RECO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0333');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0334',NULL,NULL,NULL,'HOSPITAL DE CELAYA',NULL,'HOSPITAL DE CELAYA',NULL,'16663 HOSPITAL DE CELAYA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0334');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0335',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON MUROS',NULL,'HOSPITAL INFANTIL NVO LEON MUROS',NULL,'16664 HOSPITAL INFANTIL NVO LEON MUROS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0335');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0336',NULL,NULL,NULL,'CONSTRUCCIONES Y SERVICIOS GIMON',NULL,'CONSTRUCCIONES Y SERVICIOS GIMON',NULL,'16665-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0336');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0337',NULL,NULL,NULL,'HOSPITAL EN CDMX',NULL,'HOSPITAL EN CDMX',NULL,'16666 HOSPITAL EN CDMX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0337');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0338',NULL,NULL,NULL,'REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA',NULL,'REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA',NULL,'16667-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0338');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0339',NULL,NULL,NULL,'REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA',NULL,'REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA',NULL,'16668-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0339');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0340',NULL,NULL,NULL,'REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA',NULL,'REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA',NULL,'16669 REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0340');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0341',NULL,NULL,NULL,'IMSS RIO COLORADO, SONORA',NULL,'IMSS RIO COLORADO, SONORA',NULL,'16671-S IMSS RIO COLORADO, SONORA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:55',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0341');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0342',NULL,NULL,NULL,'IMSS RIO COLORADO, SONORA',NULL,'IMSS RIO COLORADO, SONORA',NULL,'16672-S IMSS RIO COLORADO, SONORA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0342');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0343',NULL,NULL,NULL,'MORA ASESORES PARA LA CONSTRUCCION BASILICA',NULL,'MORA ASESORES PARA LA CONSTRUCCION BASILICA',NULL,'16673  MORA ASESORES PARA LA CONSTRUCCION BASILICA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0343');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0344',NULL,NULL,NULL,'IMSS RIO COLORADO, SONORA',NULL,'IMSS RIO COLORADO, SONORA',NULL,'16674 IMSS RIO COLORADO, SONORA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0344');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0345',NULL,NULL,NULL,'IMSS RIO COLORADO, SONORA CHISA PLAST',NULL,'IMSS RIO COLORADO, SONORA CHISA PLAST',NULL,'16675 IMSS RIO COLORADO, SONORA CHISA PLAST.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0345');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0346',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16676 HOSPITAL NAVAL DE ACAPULCO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0346');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0347',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16677-S HOSPITAL NAVAL DE ACAPULCO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0347');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0348',NULL,NULL,NULL,'EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL',NULL,'EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL',NULL,'16678 EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0348');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0349',NULL,NULL,NULL,'HOSPITAL DE CELAYA',NULL,'HOSPITAL DE CELAYA',NULL,'16678 HOSPITAL DE CELAYA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0349');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0350',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL',NULL,'HOSPITAL GENERAL DE CHETUMAL',NULL,'16679 HOSPITAL GENERAL DE CHETUMAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0350');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0351',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL',NULL,'HOSPITAL GENERAL DE CHETUMAL',NULL,'16680-S HOSPITAL GENERAL DE CHETUMAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0351');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0352',NULL,NULL,NULL,'CESSA AGS',NULL,'CESSA AGS',NULL,'16681 CESSA-AGS.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0352');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0353',NULL,NULL,NULL,'CESSA AGS',NULL,'CESSA AGS',NULL,'16682-S CESSA-AGS.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0353');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0354',NULL,NULL,NULL,'ILINSA',NULL,'ILINSA',NULL,'16683-S ILINSA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0354');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0355',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL GP',NULL,'HOSPITAL GENERAL DE CHETUMAL GP',NULL,'16684 HOSPITAL GENERAL DE CHETUMAL GP.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0355');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0356',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL ASIRE',NULL,'HOSPITAL GENERAL DE CHETUMAL ASIRE',NULL,'16685 HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0356');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0357',NULL,NULL,NULL,'HOSPITAL NAVAL DE CHETUMAL',NULL,'HOSPITAL NAVAL DE CHETUMAL',NULL,'16686-S HOSPITAL NAVAL DE CHETUMAL.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0357');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0358',NULL,NULL,NULL,'HOSPITAL NAVAL DE CHETUMAL',NULL,'HOSPITAL NAVAL DE CHETUMAL',NULL,'16687 HOSPITAL NAVAL DE CHETUMAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0358');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0359',NULL,NULL,NULL,'DECORACLEAN',NULL,'DECORACLEAN',NULL,'16688-S DECORACLEAN.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0359');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0360',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL ASIRE',NULL,'HOSPITAL GENERAL DE CHETUMAL ASIRE',NULL,'16689-S HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0360');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0361',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL GP (2)',NULL,'HOSPITAL GENERAL DE CHETUMAL GP (2)',NULL,'16690 HOSPITAL GENERAL DE CHETUMAL GP (2).xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0361');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0362',NULL,NULL,NULL,'HOSPITAL GENERAL DE CHETUMAL GP',NULL,'HOSPITAL GENERAL DE CHETUMAL GP',NULL,'16690 HOSPITAL GENERAL DE CHETUMAL GP.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0362');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0363',NULL,NULL,NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'HOSPITAL INFANTIL NVO LEON',NULL,'16691 HOSPITAL INFANTIL NVO LEON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0363');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0364',NULL,NULL,NULL,'16691 2026 VGG CESSA CUERARAMO, GTO',NULL,'16691 2026 VGG CESSA CUERARAMO, GTO',NULL,'16691-2026-VGG CESSA CUERARAMO, GTO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0364');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0365',NULL,NULL,NULL,'CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO.',NULL,'CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO.',NULL,'16692 CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0365');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0366',NULL,NULL,NULL,'HOSPITAL DONATO ALARCON',NULL,'HOSPITAL DONATO ALARCON',NULL,'16692 HOSPITAL DONATO ALARCON.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0366');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0367',NULL,NULL,NULL,'SHOP ELLAZ ATIZAPAN',NULL,'SHOP ELLAZ ATIZAPAN',NULL,'16693 SHOP ELLAZ ATIZAPAN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0367');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0368',NULL,NULL,NULL,'16693 2026 VG HOSPITAL COMUNITARIO ABASOLO, GTO.',NULL,'16693 2026 VG HOSPITAL COMUNITARIO ABASOLO, GTO.',NULL,'16693-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0368');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0369',NULL,NULL,NULL,'DECORACLEAN',NULL,'DECORACLEAN',NULL,'16694-S DECORACLEAN.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0369');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0370',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16694-S HOSPITAL NAVAL DE ACAPULCO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0370');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0371',NULL,NULL,NULL,'16694 S VG CLINICA DEL IMSS STA BARBARA, S.L.W',NULL,'16694 S VG CLINICA DEL IMSS STA BARBARA, S.L.W',NULL,'16694-S-VG CLINICA DEL IMSS STA BARBARA, S.L.W.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0371');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0372',NULL,NULL,NULL,'16694 S VG HOSPITAL NAVAL DE ACAPULCO',NULL,'16694 S VG HOSPITAL NAVAL DE ACAPULCO',NULL,'16694-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0372');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0373',NULL,NULL,NULL,'INSTITUTO NACIONAL DE NEUROLOGIA PSIQUIATRIA (2)',NULL,'INSTITUTO NACIONAL DE NEUROLOGIA PSIQUIATRIA (2)',NULL,'16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA (2).xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0373');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0374',NULL,NULL,NULL,'INSTITUTO NACIONAL DE NEUROLOGIA PSIQUIATRIA',NULL,'INSTITUTO NACIONAL DE NEUROLOGIA PSIQUIATRIA',NULL,'16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0374');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0375',NULL,NULL,NULL,'16695 S VG HOSPITAL NAVAL DE ACAPULCO',NULL,'16695 S VG HOSPITAL NAVAL DE ACAPULCO',NULL,'16695-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0375');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0376',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16696-S HOSPITAL NAVAL DE ACAPULCO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0376');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0377',NULL,NULL,NULL,'ING. ABRAHAM GALLARDO',NULL,'ING. ABRAHAM GALLARDO',NULL,'16696-S ING. ABRAHAM GALLARDO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0377');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0378',NULL,NULL,NULL,'BE GRAND CONTADERO',NULL,'BE GRAND CONTADERO',NULL,'16697-S BE GRAND CONTADERO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0378');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0379',NULL,NULL,NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'HOSPITAL NAVAL DE ACAPULCO',NULL,'16697-S HOSPITAL NAVAL DE ACAPULCO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0379');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0380',NULL,NULL,NULL,'16698 S VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO',NULL,'16698 S VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO',NULL,'16698-S-VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0380');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0381',NULL,NULL,NULL,'VGG HOSPITAL MAC IRAPUATO, GTO.',NULL,'VGG HOSPITAL MAC IRAPUATO, GTO.',NULL,'16699 -VGG HOSPITAL MAC IRAPUATO, GTO..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0381');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0382',NULL,NULL,NULL,'16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL',NULL,'16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL',NULL,'16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0382');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0383',NULL,NULL,NULL,'CONDOMINIO PITAGORAS 308 CDMX',NULL,'CONDOMINIO PITAGORAS 308 CDMX',NULL,'16700 CONDOMINIO PITAGORAS 308 CDMX.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0383');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0384',NULL,NULL,NULL,'ISSSTE PARACHO, MICHOACAN',NULL,'ISSSTE PARACHO, MICHOACAN',NULL,'16700 ISSSTE PARACHO, MICHOACAN.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0384');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0385',NULL,NULL,NULL,'16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL',NULL,'16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL',NULL,'16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0385');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0386',NULL,NULL,NULL,'IMSS, HOSPITAL DE GINECO OBSTETRICIA 4, SAN ANGEL',NULL,'IMSS, HOSPITAL DE GINECO OBSTETRICIA 4, SAN ANGEL',NULL,'16702 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0386');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0387',NULL,NULL,NULL,'16703 S VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO',NULL,'16703 S VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO',NULL,'16703-S-VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0387');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0388',NULL,NULL,NULL,'HOSPITAL MAC IRAPUATO, GTO.',NULL,'HOSPITAL MAC IRAPUATO, GTO.',NULL,'16704 HOSPITAL MAC IRAPUATO, GTO..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0388');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0389',NULL,NULL,NULL,'16705 ISSSTE PARACHO, MICHOACAN VG',NULL,'16705 ISSSTE PARACHO, MICHOACAN VG',NULL,'16705-ISSSTE PARACHO, MICHOACAN-VG.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0389');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0390',NULL,NULL,NULL,'HOSPITAL GENERAL DE ZONA No. 10, GTO.',NULL,'HOSPITAL GENERAL DE ZONA No. 10, GTO.',NULL,'16706-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0390');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0391',NULL,NULL,NULL,'ISSSTE PARACHO MICH.',NULL,'ISSSTE PARACHO MICH.',NULL,'16706-S ISSSTE PARACHO MICH..XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0391');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0392',NULL,NULL,NULL,'HOSPITAL GENERAL DE ZONA No. 10, GTO.',NULL,'HOSPITAL GENERAL DE ZONA No. 10, GTO.',NULL,'16707-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0392');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0393',NULL,NULL,NULL,'HOSPITAL GENERAL DE ZONA No. 10, GTO.',NULL,'HOSPITAL GENERAL DE ZONA No. 10, GTO.',NULL,'16708-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0393');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0394',NULL,NULL,NULL,'IMSS, HOSPITAL DE GINECO OBSTETRICIA 4, SAN ANGEL VG',NULL,'IMSS, HOSPITAL DE GINECO OBSTETRICIA 4, SAN ANGEL VG',NULL,'16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL VG.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0394');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0395',NULL,NULL,NULL,'IMSS, HOSPITAL DE GINECO OBSTETRICIA 4, SAN ANGEL',NULL,'IMSS, HOSPITAL DE GINECO OBSTETRICIA 4, SAN ANGEL',NULL,'16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0395');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0396',NULL,NULL,NULL,'16710 2026 VG HOSPITAL COMUNITARIO ABASOLO, GTO.',NULL,'16710 2026 VG HOSPITAL COMUNITARIO ABASOLO, GTO.',NULL,'16710-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0396');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0397',NULL,NULL,NULL,'HOSPITAL DE CANCUN. Q.R. VG',NULL,'HOSPITAL DE CANCUN. Q.R. VG',NULL,'16711 HOSPITAL DE CANCUN. Q.R. VG.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0397');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0398',NULL,NULL,NULL,'HOSPITAL  REGIONAL NAVAL DE MANZANILLO',NULL,'HOSPITAL  REGIONAL NAVAL DE MANZANILLO',NULL,'16712 HOSPITAL  REGIONAL NAVAL DE MANZANILLO.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0398');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0399',NULL,NULL,NULL,'16713  VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN',NULL,'16713  VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN',NULL,'16713- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0399');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0400',NULL,NULL,NULL,'16714 S VG PRADO NORTE  440',NULL,'16714 S VG PRADO NORTE  440',NULL,'16714-S-VG PRADO NORTE  440.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0400');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0401',NULL,NULL,NULL,'CLINICA CONDESA CDMX',NULL,'CLINICA CONDESA CDMX',NULL,'16715 CLINICA CONDESA CDMX.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0401');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0402',NULL,NULL,NULL,'HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG',NULL,'HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG',NULL,'16716 HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0402');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0403',NULL,NULL,NULL,'PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM',NULL,'PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM',NULL,'16717 PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0403');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0404',NULL,NULL,NULL,'16718 S VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX.',NULL,'16718 S VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX.',NULL,'16718-S-VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX..XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0404');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0405',NULL,NULL,NULL,'MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO.',NULL,'MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO.',NULL,'16719 MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO..xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0405');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0406',NULL,NULL,NULL,'16720 S',NULL,'16720 S',NULL,'16720-S.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0406');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0407',NULL,NULL,NULL,'SHOP ELLAZ GRAN PLAZA GUADALAJARA',NULL,'SHOP ELLAZ GRAN PLAZA GUADALAJARA',NULL,'16721-S SHOP ELLAZ GRAN PLAZA GUADALAJARA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0407');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0408',NULL,NULL,NULL,'SHOP ELLAZ MERIDA',NULL,'SHOP ELLAZ MERIDA',NULL,'16722-S SHOP ELLAZ MERIDA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0408');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0409',NULL,NULL,NULL,'SHOP ELLAZ ANTARA',NULL,'SHOP ELLAZ ANTARA',NULL,'16723-S SHOP ELLAZ ANTARA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0409');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0410',NULL,NULL,NULL,'SHOP ELLAZ GALERIAS MONTERREY',NULL,'SHOP ELLAZ GALERIAS MONTERREY',NULL,'16724-S SHOP ELLAZ GALERIAS MONTERREY.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0410');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0411',NULL,NULL,NULL,'16725  VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN',NULL,'16725  VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN',NULL,'16725- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0411');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0412',NULL,NULL,NULL,'FORMATO COTIZACIONES 2026 HAPA   copia',NULL,'FORMATO COTIZACIONES 2026 HAPA   copia',NULL,'FORMATO_COTIZACIONES_2026_HAPA - copia.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0412');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0413',NULL,NULL,NULL,'FORMATO COTIZACIONES 2026 HAPA',NULL,'FORMATO COTIZACIONES 2026 HAPA',NULL,'FORMATO_COTIZACIONES_2026_HAPA.XLSX','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0413');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0414',NULL,NULL,NULL,'PRUEBA',NULL,'PRUEBA',NULL,'PRUEBA.xlsx','2026-10-21','Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.','Borrador','Consulta de archivo',NULL,'2026-10-21 03:26:56',1,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0414');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0415',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X84AE2A46FF' LIMIT 1)),NULL,'CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.','ING. HECTOR AGUIRRE CISNEROS','HOSPITAL C.H. 57 IGUALA DE LA INDEPENDENCIA, EDO. DE GUERRERO',NULL,NULL,'2026-10-21','Adición por requerimientos nuevos de CCO-2026-0003.','Borrador','Subcotización de CCO-2026-0003',NULL,'2026-10-21 03:27:19',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0415');
INSERT INTO cotizaciones_comerciales (folio,sucursal_id,cliente_id,obra_id,constructora,atencion,obra_nombre,ubicacion,pres_ref,fecha,introduccion,estatus,observaciones,creado_por,fecha_creacion,es_consulta,fecha_estatus)
SELECT 'CCO-2026-0417',NULL,COALESCE((SELECT id FROM clientes WHERE razon_social='CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.' LIMIT 1),(SELECT id FROM clientes WHERE rfc='X84AE2A46FF' LIMIT 1)),NULL,'CONSTRUCCIONES, MANTENIMIENTO Y PROYECTOS ROMACO, S.A. DE C.V.','ING. HECTOR AGUIRRE CISNEROS','HOSPITAL C.H. 57 IGUALA DE LA INDEPENDENCIA, EDO. DE GUERRERO',NULL,NULL,'2026-10-21','PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.','Borrador','Versión de CCO-2026-0003. Ajuste de altura',NULL,'2026-10-21 03:57:30',0,NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales WHERE folio='CCO-2026-0417');
UPDATE cotizaciones_comerciales h JOIN cotizaciones_comerciales p ON p.folio='CCO-2026-0003' SET h.parent_id=p.id WHERE h.folio='CCO-2026-0415' AND (h.parent_id IS NULL OR h.parent_id=0);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,'PLANTA BAJA','Suministro y aplicación de recubrimiento de prueba','M2',10.00,125.50,1255.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0006' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,'PLANTA BAJA','Zoclo de prueba','ML',4.00,80.00,320.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0006' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.- MORTERO PLASTICO MALLA 350-400 (REND. 16-18M2)',NULL,1.00,1205.56,1205.56,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'2.- ARENA SILICA (REND. 22-25M2)',NULL,1.00,1188.64,1188.64,'Producto',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,3,NULL,'3.- SELLADOR INICIAL (REND. 90M2)',NULL,1.00,907.37,907.37,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,4,NULL,'4.- PRAIMER GLASS REF. S.M.A (REND. 85M2)',NULL,1.00,1028.18,1028.18,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,5,NULL,'5.- CHISA GLASS REF. S.M.A (REND. 55-60M2)',NULL,2.00,4797.13,9594.26,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,6,NULL,'6.- VITROGLASS REF. S.M.A (REND. 180-190M2)',NULL,1.00,5023.57,5023.57,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,7,NULL,'7.- RODILLO DE FELPA (REND 250M2)',NULL,1.00,76.40,76.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,8,NULL,'8.- BROCHA PARA APLICACIÓN (REND. 350M2)',NULL,1.00,64.20,64.20,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,9,NULL,'9.- LIJA MEDIANA No. 80 (REND. 20M2)',NULL,10.00,19.30,193.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,10,NULL,'10.- PAPEL PARA PROTECCIÓN (REND. 250M2)',NULL,1.00,740.33,740.33,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,11,NULL,'11.- MASKING TAPE PARA PROTECCIÓN (REND. 12.5M2)',NULL,4.00,18.20,72.80,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,12,NULL,'1.- MORTERO PLASTICO MALLA 350-400 (REND. 16-18M2)',NULL,1.00,1205.56,1205.56,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,13,NULL,'2.- ARENA SILICA (REND. 22-25M2)',NULL,1.00,1188.64,1188.64,'Producto',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,14,NULL,'3.- SELLADOR INICIAL (REND. 90M2)',NULL,3.00,907.37,2722.11,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,15,NULL,'4.- PRAIMER GLASS REF. S.M.A (REND. 85M2)',NULL,3.00,1028.18,3084.54,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,16,NULL,'5.- CHISA GLASS REF. S.M.A (REND. 55-60M2)',NULL,5.00,4797.13,23985.65,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,17,NULL,'6.- VITROGLASS REF. S.M.A (REND. 180-190M2)',NULL,2.00,5023.57,10047.14,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,18,NULL,'7.- RODILLO DE FELPA (REND 250M2)',NULL,1.00,76.40,76.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,19,NULL,'8.- BROCHA PARA APLICACIÓN (REND. 350M2)',NULL,2.00,64.20,128.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,20,NULL,'9.- LIJA MEDIANA No. 80 (REND. 20M2)',NULL,23.00,19.30,443.90,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,21,NULL,'10.- PAPEL PARA PROTECCIÓN (REND. 250M2)',NULL,1.00,740.33,740.33,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,22,NULL,'11.- MASKING TAPE PARA PROTECCIÓN (REND. 12.5M2)',NULL,20.00,18.20,364.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,23,NULL,'1.- MORTERO PLASTICO MALLA 350-400 (REND. 16-18M2)',NULL,1.00,1205.56,1205.56,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,24,NULL,'2.- ARENA SILICA (REND. 22-25M2)',NULL,1.00,1188.64,1188.64,'Producto',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,25,NULL,'3.- SELLADOR INICIAL (REND. 90M2)',NULL,6.00,907.37,5444.22,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,26,NULL,'4.- PRAIMER GLASS REF. S.M.A (REND. 85M2)',NULL,6.00,1028.18,6169.08,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,27,NULL,'5.- CHISA GLASS REF. S.M.A (REND. 55-60M2)',NULL,9.00,4797.13,43174.17,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,28,NULL,'6.- VITROGLASS REF. S.M.A (REND. 180-190M2)',NULL,3.00,5023.57,15070.71,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,29,NULL,'7.- RODILLO DE FELPA (REND 250M2)',NULL,2.00,76.40,152.80,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,30,NULL,'8.- BROCHA PARA APLICACIÓN (REND. 350M2)',NULL,2.00,64.20,128.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,31,NULL,'9.- LIJA MEDIANA No. 80 (REND. 20M2)',NULL,25.00,19.30,482.50,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,32,NULL,'10.- PAPEL PARA PROTECCIÓN (REND. 250M2)',NULL,2.00,740.33,1480.66,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,33,NULL,'11.- MASKING TAPE PARA PROTECCIÓN (REND. 12.5M2)',NULL,40.00,18.20,728.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,34,NULL,'1.- MORTERO PLASTICO MALLA 350-400 (REND. 16-18M2)',NULL,1.00,1205.56,1205.56,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,35,NULL,'2.- ARENA SILICA (REND. 22-25M2)',NULL,1.00,1188.64,1188.64,'Producto',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,36,NULL,'3.- SELLADOR INICIAL (REND. 90M2)',NULL,8.00,907.37,7258.96,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,37,NULL,'4.- PRAIMER GLASS REF. S.M.A (REND. 85M2)',NULL,9.00,1028.18,9253.62,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,38,NULL,'5.- CHISA GLASS REF. S.M.A (REND. 55-60M2)',NULL,14.00,4797.13,67159.82,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,39,NULL,'6.- VITROGLASS REF. S.M.A (REND. 180-190M2)',NULL,4.00,5023.57,20094.28,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,40,NULL,'7.- RODILLO DE FELPA (REND 250M2)',NULL,3.00,76.40,229.20,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0001' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'Flexómetro de 5 mts uso Rudo','Pza',14.00,107.33,1502.62,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'Porta herramienta de carnaza de 13 compartimientos','Pza',14.00,2.70,37.80,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,3,NULL,'Cutter profesional para navaja de 18 mm','Pza',14.00,4.05,56.70,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,4,NULL,'Pinza para Mecánico 10"','pza',14.00,5.40,75.60,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,5,NULL,'Cortador de vidrio profesional 6" con depósito para aceite','pza',6.00,6.75,40.50,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,6,NULL,'Destapacaños 15m tipo barreno con manivela','pza',3.00,8.10,24.30,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,7,NULL,'Cepillo para carpintero estriado 14"','pza',7.00,1280.75,8965.25,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,8,NULL,'Prensa con ajuste rápido 36"','pza',15.00,10.80,162.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,9,NULL,'Serrucho 24"','pza',6.00,12.15,72.90,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,10,NULL,'Flota de esponja 12" espesor 12" acabado áspero','pza',6.00,13.50,81.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,11,NULL,'Amarrador de varillas','pza',9.00,14.85,133.65,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,12,NULL,'Cortador de tubo de cobre 3/16" a 2" cuchilla 18 mm','pza',3.00,16.20,48.60,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,13,NULL,'Juego de avellanador','Lote',3.00,17.55,52.65,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,14,NULL,'Arco 12"','pza',6.00,18.90,113.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,15,NULL,'Caja de herramientas con llantas','pza',14.00,20.25,283.50,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,16,NULL,'Odómetro','pza',3.00,21.60,64.80,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,17,NULL,'Protector Facial','pza',3.00,22.95,68.85,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,18,NULL,'Kit Contra Cáidas','Lote',8.00,24.30,194.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,19,NULL,'Rodilleras para piso con Gel','Pza',6.00,25.65,153.90,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,20,NULL,'Taladro Rotomartillo','Pza',3.00,27.00,81.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,21,NULL,'Taladro Inalámbrico','Pza',3.00,28.35,85.05,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,22,NULL,'Sierra Circular 1800W','pza',3.00,29.70,89.10,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,23,NULL,'Sierra Caladora','pza',2.00,31.05,62.10,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,24,NULL,'Lijadora Orbital','pza',3.00,32.40,97.20,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,25,NULL,'Esmeril Angular','pza',6.00,33.75,202.50,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,26,NULL,'Esmeril de Banco','pza',1.00,35.10,35.10,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,27,NULL,'Juego de desatornilladores combinados','Lote',14.00,36.45,510.30,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,28,NULL,'Llave Inglesa Ajustable 12" (Perico)','pza',8.00,37.80,302.40,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,29,NULL,'Llave Inglesa Ajustable 15" (Perico)','pza',8.00,39.15,313.20,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,30,NULL,'Llave Steelson 14"','pza',6.00,40.50,243.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,31,NULL,'Llave Steelson 18"','pza',6.00,41.85,251.10,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,32,NULL,'Pinzas de presión de nariz 9"','pza',6.00,43.20,259.20,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,33,NULL,'Pinza de presión recta 10"','pza',6.00,44.55,267.30,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,34,NULL,'Juego de Dados y accesorios 3/4"','pza',3.00,45.90,137.70,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,35,NULL,'-','pza',6.00,47.25,283.50,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,36,NULL,'Juego de limas con mango bimaterial de 9 piezas','pza',6.00,48.60,291.60,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,37,NULL,'Escalera de Fibra de vidrio extensión a 6','Pza',3.00,49.95,149.85,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,38,NULL,'Escalera de Fibra de vidrio extensión a 24','Pza',3.00,51.30,153.90,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,39,NULL,'Escalera de Fibra de vidrio extensión a 28','Pza',3.00,52.65,157.95,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,40,NULL,'Escalera Plegable de 3 escalones','Pza',6.00,54.00,324.00,'Servicio',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0002' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',2603.60,113.95,296680.22,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'SUMINISTRO Y APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF-S.M.A EN MUROS CON APLANADO FINO DE MEZCLA Y/O SUPERFICIE LISA, INCLUYE: LIMPIEZA, LIJADO Y/O REBABEO DE MURO, SEGÚN SEA EL CASO; APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF- S.M.A., A MANO CON RODILLO DE FELPA Y/O LLANA METALICA, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, O POR ASPERSIÓN DE AIRE, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE','M2',148.23,155.90,23109.06,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON RECUBRIMIENTO EXISTENTE, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACION DE ARENA SILICA MALLA 250-300 A MANO CON LLANA METALICA, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','LOTE DE DETALLES HAS',1.00,27794.64,27794.64,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0004' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0005' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0005' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON RECUBRIMIENTO EXISTENTE, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACION DE ARENA SILICA MALLA 250-300 A MANO CON LLANA METALICA, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','LOTE DE DETALLES HAS',1.00,27794.64,27794.64,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0007' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON RECUBRIMIENTO EXISTENTE, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACION DE ARENA SILICA MALLA 250-300 A MANO CON LLANA METALICA, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','LOTE DE DETALLES HAS',1.00,27794.64,27794.64,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0008' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0009' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0009' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON RECUBRIMIENTO EXISTENTE, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACION DE ARENA SILICA MALLA 250-300 A MANO CON LLANA METALICA, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','LOTE DE DETALLES HAS',1.00,27794.64,27794.64,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0010' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0011' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0011' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0012' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0012' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0013' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0013' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0014' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0014' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0015' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0015' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',320.00,189.90,60768.00,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0016' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',320.00,95.69,30620.80,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0016' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,1,NULL,'SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',2603.60,113.95,296680.22,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0417' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT INTO cotizaciones_comerciales_detalle (cotizacion_id,orden,grupo,concepto,unidad,cantidad,precio_unitario,importe,tipo,producto_id)
SELECT c.id,2,NULL,'SUMINISTRO Y APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF-S.M.A EN MUROS CON APLANADO FINO DE MEZCLA Y/O SUPERFICIE LISA, INCLUYE: LIMPIEZA, LIJADO Y/O REBABEO DE MURO, SEGÚN SEA EL CASO; APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF- S.M.A., A MANO CON RODILLO DE FELPA Y/O LLANA METALICA, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, O POR ASPERSIÓN DE AIRE, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE','M2',148.23,155.90,23109.06,'Aplicacion',NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0417' AND NOT EXISTS (SELECT 1 FROM cotizaciones_comerciales_detalle x WHERE x.cotizacion_id=c.id);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('1.- MORTERO PLASTICO MALLA 350-400 (REND. 16-18M2)',NULL,1205.56,NULL,'b0d9c2b726926625be993fee79b88e0bb10c0c29',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('2.- ARENA SILICA (REND. 22-25M2)',NULL,1188.64,NULL,'4b69888565efa3fb88a9b40233a3a953a15218ba',1,'Producto',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('3.- SELLADOR INICIAL (REND. 90M2)',NULL,907.37,NULL,'9f857b9aac378a3237207810345b9a09c4647253',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('4.- PRAIMER GLASS REF. S.M.A (REND. 85M2)',NULL,1028.18,NULL,'7fa7f57500deb38bf05f06b5bd741303d52f114b',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('5.- CHISA GLASS REF. S.M.A (REND. 55-60M2)',NULL,4797.13,NULL,'d6575bc18f807d4d52012673c5a3e931aecf5f6a',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('6.- VITROGLASS REF. S.M.A (REND. 180-190M2)',NULL,5023.57,NULL,'a8b385cec00a91eacda9418385ff6bc2c62473ec',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('7.- RODILLO DE FELPA (REND 250M2)',NULL,76.40,NULL,'86163a244c921e55657f9c598b94e457264c1b6d',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('8.- BROCHA PARA APLICACIÓN (REND. 350M2)',NULL,64.20,NULL,'25212c78bce29d754be4593618e3cb5d2703d9c8',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('9.- LIJA MEDIANA No. 80 (REND. 20M2)',NULL,19.30,NULL,'83b1eb893c62fcbab2faac698d921ab7eb287b22',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('10.- PAPEL PARA PROTECCIÓN (REND. 250M2)',NULL,740.33,NULL,'79416b66f77b35b1b65abc8eaf17f4075d2cace8',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('11.- MASKING TAPE PARA PROTECCIÓN (REND. 12.5M2)',NULL,18.20,NULL,'ef47c453f65b75f9cd666358118c51405820519a',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUMINISTRO PARA 250 M2',NULL,0.00,NULL,'7d0ba9d0c31240b17da468dc7d138a024d8865f0',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUMINISTRO PARA 500 M2',NULL,0.00,NULL,'2b876c93eef9f37f86d8bb3e9851e50b5d674e74',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUMINISTRO PARA 750 M2',NULL,0.00,NULL,'56516c8e9f0333390908001196af7317a6d58278',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('VIATICOS SEMANALES (ISLAS MUJERES)',NULL,0.00,NULL,'41b46eac7f5bc2e88623b3bea7c82da05053fbef',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('PASAJE',NULL,3926.00,NULL,'a784e5ce934474ccd96f5a4588e826c2413eac4d',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUELDO',NULL,5500.00,NULL,'a69cfc374498bb4981dfbdf72e1750535d9565e9',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ENVIO',NULL,23598.33,NULL,'62b7a9ad2378d26c44cc096501ab3abc5eaa636d',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('HOSPEDAJE',NULL,5100.00,NULL,'5d610e83c4c537851c8f09b6134b9fd31446906b',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('COMIDAS',NULL,2500.00,NULL,'9730dd241054eb7108ae074b7a89f8141074ee0a',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('VIATICOS SEMANALES (ENSENADA)',NULL,0.00,NULL,'0b19a40846d8e2de68ce4171556e4215d26e689f',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Flexómetro de 5 mts uso Rudo','Pza',107.33,'FH-5M','64bddf56b0f756972abe3dfb8624dcd70ba42229',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Porta herramienta de carnaza de 13 compartimientos','Pza',2.70,'POHE-8CX','0b3c533c49c04d8493818cd7322803e649922c26',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cutter profesional para navaja de 18 mm','Pza',4.05,'CUT-6X','9e0294dfc1049804b7dca4767de261b2350cb7a0',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Pinza para Mecánico 10"','pza',5.40,'PCH-10X','e0428df4d3c31933e76817192f450fd3ac3a0fdc',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cortador de vidrio profesional 6" con depósito para aceite','pza',6.75,'CV-5DX','6d99b9dbe7105f59f55200300645133cae509e7a',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Destapacaños 15m tipo barreno con manivela','pza',8.10,'DECAB-150','eff3dacc54a270ecc8f61460347363e045e411fd',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cepillo para carpintero estriado 14"','pza',1280.75,'5C','429746af194dfa019e5a85ca03de3b0c03828075',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Prensa con ajuste rápido 36"','pza',10.80,'PAR-236','698b8a8c9b432e6fa186b8234e4ac3a8d78c18fc',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Serrucho 24"','pza',12.15,'STX-24','1c3b4abb0e1494de0ba0da71a13ca3649c1b0dbf',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Flota de esponja 12" espesor 12" acabado áspero','pza',13.50,'FL-12','9209e81fe2c91c9b9b26300de836ccd0b670d200',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Amarrador de varillas','pza',14.85,'AM-VA','7a3a519507a7e0adfcc8e753df175c98abca8f75',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cortador de tubo de cobre 3/16" a 2" cuchilla 18 mm','pza',16.20,'COT-50','145fee189c2895a96f691345b31b1fe088f31a5c',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Juego de avellanador','Lote',17.55,'JAV-7','abd896b1476c56fb061e90d91c5993ef16ec2ac7',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Arco 12"','pza',18.90,'ATT-12','f66bd42d51b4c33d65ae4c067c21fa965f740e22',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Caja de herramientas con llantas','pza',20.25,'CHP-1925R','510fc23f6b86ac6357543872a3ec2ac266d024b3',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Odómetro','pza',21.60,'ODM-12','5eb8a984faa8869bad73fa58c973eacd221d29f0',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Protector Facial','pza',22.95,'PF-500','3606a3604504f573553f7980f024e3a729ccf371',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Kit Contra Cáidas','Lote',24.30,'ARN-KIT-1','ef6e4700412baccc2949c680df385eb2adcce74e',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Rodilleras para piso con Gel','Pza',25.65,'ROD-500','a86e51a6dfd29171b3ed803980ccf04d3bb78517',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Taladro Rotomartillo','Pza',27.00,'D25260KB3','86284e3df00a04721f011fa95c69186b5353bab3',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Taladro Inalámbrico','Pza',28.35,'Ded7781d1-b3','3aa9103d964c6f1c3a3cf41c62f907d264ab1ef2',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Sierra Circular 1800W','pza',29.70,'DW E575','b9175bbaf6dcc4bdf67cef5b7df4a2643cab0fcf',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Sierra Caladora','pza',31.05,'DW 300','9170e2c05f0b1a0008a21c9bfaf4de1a34388f33',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Lijadora Orbital','pza',32.40,'DW E6411-B3','452c28b400ee5f78edb2d8208d40216f86a30824',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Esmeril Angular','pza',33.75,'DW E4120x-B3','b0789d2252f3117896dfb86b28c9a6ac08e5128b',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Esmeril de Banco','pza',35.10,'Dw752B3','aa6c0ca744fd58ee06534389d60cc9798a345fbe',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Juego de desatornilladores combinados','Lote',36.45,'JBUD02','30307d97315b47662e938ef6d061d147e6fd64ec',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Llave Inglesa Ajustable 12" (Perico)','pza',37.80,'712S1','bbbc864b28ed459974a263fd05c3f48fe24dea34',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Llave Inglesa Ajustable 15" (Perico)','pza',39.15,'712S1','c2c956fea8533ab95a208b88c23f814b93fa6522',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Llave Steelson 14"','pza',40.50,'814HD','4185edd09d2090d1038c7401b9a72af5d751d655',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Llave Steelson 18"','pza',41.85,'8518A','2aa7af4cb51fd7a9849e084805aa3acf4af90213',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Pinzas de presión de nariz 9"','pza',43.20,'PNLB9','3c473f4f0ced8cf7758c32a2cee7b2add666e29b',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Pinza de presión recta 10"','pza',44.55,'293R','d523c0d88eaf8cfc4e8160f35ab0930291393cf1',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Juego de Dados y accesorios 3/4"','pza',45.90,'55104','a9e2ef2cb36a9ca4774dd73bbdc0622de53f72e8',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('-','pza',47.25,'86A','3bc15c8aae3e4124dd409035f32ea2fd6835efc9',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Juego de limas con mango bimaterial de 9 piezas','pza',48.60,'195164','9c5c762987d547603c1588229ebf76a8e0b49fd4',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Escalera de Fibra de vidrio extensión a 6','Pza',49.95,'H-1194','1fdc9e7401a81569309f4708da678f8185f78dbc',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Escalera de Fibra de vidrio extensión a 24','Pza',51.30,'H-4138','23bd4a64e0edd3d8cef979b54adf999de7bc951e',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Escalera de Fibra de vidrio extensión a 28','Pza',52.65,'H-5621','eb459b8a669cf4d631dca11192ad13811f569eb1',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Escalera Plegable de 3 escalones','Pza',54.00,'H-7892','9a4c770935e04d526eba7f3e37441170841c2a44',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Compresor de Aire Electrónico 8 Litros','Pza',3184.66,'-','00a9030e3534426d7f99ee57c3dbc6918aef9d5a',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Lijadora para extensión 9 x 23.4 x 8.5 cm','Pza',318.00,'-','21e6abc7cc5ec4ca844ce69da7ee1376d75d0a55',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Pinzas para electricista 7"','pza',869.20,'D-201-7','441fad401ec5a9d63e43f95d25c85d59bc5d2984',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Pinzas de punta cónica 8"','pza',848.00,'D-203-8','70836f79a033abf70238676b82fbf73bfac90aba',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Pinzas de corte diagonal 7"','pza',530.00,'D-220-7','7e42fb0ecec9b31f09c4e2083bb062aede935c31',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Guía Jalacable de nylon 30 mts','pza',1272.00,'14-N30','5a10b741059f0daa47cc78c8f529c3685e4c5821',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable THW - C10 Caja 100m Negro','Rllo',2756.19,'-','b703f4e85f6169c974856aef4df51141bfd1c0a8',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable THW - C10 Caja 100m Blanco','Rllo',2756.19,'-','e846c6785aad6befcc1cda5cf1c8ef9d044ad153',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable THW - C10 Caja 100m Verde','Rllo',2756.19,'-','b73540b95cafd072ac785b7f03b3471f4293bda5',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable THW - C12 Caja 100m Negro','Rllo',1743.35,'-','e13944acf6cde378945953d45d66f8fc853a9f82',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable THW - C12 Caja 100m Blanco','Rllo',1743.35,'-','7e8eaccc8437d2f45f9a74a73cae512cf1151d82',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable THW - C12 Caja 100m Verde','Rllo',1743.35,'-','c99c33ba174fb69e6039b61b10f2b08839efd71b',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Cable Desnudo C12 100m','Rllo',1171.59,'-','eb7e11a2ee27c8f7804c6ccedaff2b5041392ae5',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Interruptor termomagnético 80 amp 3 polos 120/240v 100 a Atornillable','Pza',1972.87,'-','19f676622eb58179dab5de2addfa8a50f3fdf510',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Sensor de movimiento Infrarrojo 306° con triple sensor SM-360-TS','Pza',516.43,'-','8773a624aedd27da021134dc0a8bdd0a9d73b4d4',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('E32, Luminario con anuncio de salida de acrílico con leds de color verde de 3w a 120- 220 vy bateria recargable de 6 a 8 hrs mod H-9','Pza',1273.62,'-','ddd4d407791b00eb7b08aa910bfb6a89677d110a',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Tubo rígido metálico Conduit de lipo Ligero EV 21MM (3/4") 300 CMS','Pza',134.81,'-','b19716b95fe1c96ae8face51bb158fb5acc7d498',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Conector recto de acero para tubo de pared delgada (ETM) con tornillo de fijación 21 mm','Pza',9.30,'-','bbe978cec550a5bca154b53c61c0e1d115af62c8',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Finder','Pza',1650.39,'-','a06ac277bc1de6a52f46b370d7729ab2726e84c0',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Probador de Tensión sin contacto tipo Lápiz','pza',1060.00,'NCVT-41R','44178c92916cabc345ace529725b78c26878d0c4',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Lámpara de mano recargable','pza',265.00,'LAM-290','d3c75e5a124c61a45cb280be015907815d59600c',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Linterna LED tipo Minero en con batería recargable','pza',265.00,'LAM-240','9fefe641a69780bd6d2ab7c22d90a6e4afd95089',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Manifould R410/22/404/134','Pza',2120.00,'QS8N3SM','3486b69fd10b6260d865e4f0d5af40ca271180d5',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Multímetro Industrial','Pza',3710.00,'323','0705b904d9e4b161f4612c0e8b9856d89bb847ca',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Temporizador Electrónico 30A, 120-277 Voltaje, Modo Operación: 7 Días Número de canales','Pza',9540.00,'-','61f45289eb41fdca5f9f72c824d4f7c0e656c67b',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ELLO-30-15-led-E53, color negro (Salas 1a 8), Luminario de empotrar quince luces de 45x','Pza',2520.19,'-','518b50c8d0af6e4cc71693a0921cf540bb1219ca',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ELLO-10-5-LED-E3, Color negro (Dulcerías 1,2,5 6, y galerías 3 y4), Luminario de empotrar cinco luces de 15x4.5x5 cms,, incluye dimmer electrónico de 10w 120/227v, de 850 Lúmenes','Pza',68.00,'-','b9865569455106ab278534dc169819fbcaea823c',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('RMC-20-LED-24V-HCNW , Luminario para riel magnético con LED de 20w a 24 VDC, en color negro de 200 lumenes','Pza',1795.81,'-','3ebe248b0119a2a1bc1826eb3a449ce565c6499e',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Interruptor de temporizador mecánico de alta resistencia, metal, resistente a manipulaciones, voltaje 120, 240, 277 v, clasificación NEMA 3R, interior, temporizador para luminarias del camino a Ac, Vasco de Quiroga . Mpd GE 46536','Pza',2120.00,NULL,'4b1dc6d6192c84eb6bb818eec876dd91858075a2',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Luminaria LED de 100w 127 - 277 v 60hz 0.757-0.359 a 4000k, Uso exterior','Pza',1814.72,NULL,'f4d7648e61ac8d8ab425f898398a147800a2b12e',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Bomba de Vacío 7CFM, 2 Etapas','Pza',12720.00,NULL,'0774e4db44d87e59cc00ecae45af435d3dd8a611',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Soldadora Inversora de 200A','Pza',9222.00,NULL,'299b3fe01b81ef30028777b35a2a60549d5d3dbb',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Taladro de Columna de Piso 15"','Pza',17384.00,NULL,'e90f825a4ef9a1e2698ee369305df5bee6ee0fd0',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Careta eléctrónica para soldar variable de 9 a 13 sombras','Pza',1081.20,NULL,'61b6d3be8c50d8a66ae26824ce7f231c70cbc720',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Máscara de Cara completa Reutilizable','Pza',5394.34,NULL,'dfe982d0e6a570e40ed21a9bfc60de9dbe900124',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Par de Cartuchos con Filtro para pascara','Pza',466.40,NULL,'b8dee7cc71a5070485c4d2e432b3dde5e27d9977',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',113.95,NULL,'41c53bfc90746e6c0356f0edb6491eeb931c38c6',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUMINISTRO Y APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF-S.M.A EN MUROS CON APLANADO FINO DE MEZCLA Y/O SUPERFICIE LISA, INCLUYE: LIMPIEZA, LIJADO Y/O REBABEO DE MURO, SEGÚN SEA EL CASO; APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF- S.M.A., A MANO CON RODILLO DE FELPA Y/O LLANA METALICA, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, O POR ASPERSIÓN DE AIRE, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE','M2',155.90,NULL,'150b6c46baeb1811d6df790025df08b88c217346',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('FORMA DE PAGO Y CONSIDERACIONES GENERALES CORRESPONDIENTES AL PRESUPUESTO REF. 13331/2020.',NULL,0.00,NULL,'c9c3665c093d95f9ba91bca45556401b06367ec5',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('OBRA: HOSPITAL C.H. 57 IGUALA DE LA INDEPENDENCIA, EDO. DE GUERRERO',NULL,0.00,NULL,'e74a0ec49f21191d288d2a2f55364bd2ff3d9243',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('FORMA DE PAGO',NULL,0.00,NULL,'4443c02963d59a0de88a996fc7882b7fc1e60087',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('EN CASO DE SER ACEPTADO EL PRESENTE PRESUPUESTO REQUERIMOS DEL 50% COMO ANTICIPO, EL CUAL SERA APLICADO A LA FABRICACIÓN, Y GASTOS DE ENVIÓ DE MATERIALES AL LUGAR DE LOS TRABAJOS: EL PORCENTAJE RESTANTE AL IMPORTE DEL PRESUPUESTO SE PAGARÁ EN ESTIMACIONES SEMANALES SEGÚN EL AVANCE DE LOS TRABAJOS OBJETO DEL PRESENTE, AL OTORGAR EL PAGO DE ANTICIPO PARA LA EJECUCIÓN DE LOS TRABAJOS, SE CONSIDERA COMO ACEPTADO EL PRESUPUESTO EN TODOS SUS PUNTOS Y EL MISMO FUNGIRÁ COMO CONTRATO.',NULL,0.00,NULL,'e199e45e612f533c8a0057881dd670ae9f35de20',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('N O T A S:',NULL,0.00,NULL,'bc3bb7b19dcd40fc6888d09537629efd8dadcec1',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('TODO AUMENTO DE OBRA O CAMBIO DE ESPECIFICACIÓN, DEBERÁ SER NOTIFICADO POR EL CLIENTE MEDIANTE ORDEN DE TRABAJO, CON LO CUAL SE REALIZARA LA MODIFICACIÓN BAJO EL CARGO CORRESPONDIENTE EN SU CASO.',NULL,0.00,NULL,'c54d6cb0f101d00c9f9bce022a4d02ccfde2d280',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('EL PERSONAL APLICADOR DE CHISA RECUBRIMIENTOS, S.A. DE C.V. NO SE ENCUENTRA AUTORIZADO PARA REALIZAR TRABAJOS DE TABLAROCA, RESANES DE ALBAÑILERÍA O YESO, DESPRENDIMIENTO DE PAPEL TAPIZ, COLOCACIÓN O RETIRO DE SEÑALIZACIÓN, RETIRO DE MOBILIARIO, APARATOS, O EQUIPO, EVITANDO ASÍ CUALQUIER DAÑO A LOS MISMOS.',NULL,0.00,NULL,'f42cdf798650dfe160fa1fee667be1bced862fc9',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('EN CASO DE QUE EL CLIENTE NO LIBERE ÁREAS PARA SER APLICADO EL RECUBRIMIENTO, SE REALIZARAN LOS CARGOS CORRESPONDIENTES POR TIEMPOS MUERTOS, VIÁTICOS, Y LOS GASTOS QUE SE GENEREN DERIVADOS DEL MISMO, EN EL SUPUESTO DE QUE LOS VOLÚMENES CONTRATADOS VARÍEN A LOS EXISTENTES EN OBRA, EL CLIENTE LIQUIDARA EL MATERIAL EXCEDENTE FABRICADO PARA LA MISMA.',NULL,0.00,NULL,'ad485c951f8524cd50d2ac65a6cc8e12a7ee2200',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('EL PRESENTE PRESUPUESTO TIENE UNA VIGENCIA DE 15 DÍAS HÁBILES, AL TERMINO DE ESTA FECHA SE DEBERÁ ACTUALIZAR LA COTIZACIÓN, PARA VERIFICAR LA VIGENCIA DE LOS PRECIOS.',NULL,0.00,NULL,'c51ef24b1141526d5db8d9fe96ec1d532a59f7eb',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ESTE TIPO DE MATERIAL POR SUS COMPONENTES REQUIERE DE UNA SUPERFICIE SECA, LIBRE DE HUMEDAD Y/O SALITRE, DE LO CONTRARIO NO RECOMENDAMOS LA APLICACIÓN DE NUESTROS PRODUCTOS HASTA CORREGIR EL PROBLEMA, YA QUE NO SEREMOS RESPONSABLES POR DEFECTOS, MANCHAS O DESPRENDIMIENTOS QUE PUDIERAN PRESENTARSE, SI EN LAS SUPERFICIES DONDE SERÁN APLICADOS LOS PRODUCTOS COTIZADOS EXISTIERA PINTURA EN MAL ESTADO O CON FALSA ADHERENCIA, EL CLIENTE SE COMPROMETE A RETIRAR LA MISMA, PREVIO A LA APLICACIÓN DE NUESTR',NULL,0.00,NULL,'138e0dd631deca9a13ce4c4e84db9e5bf943e625',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('TODO LO RELACIONADO A LICENCIAS, PERMISOS DE CONSTRUCCIÓN Y SINDICATOS, CORRERÁ POR CUENTA DEL CLIENTE.',NULL,0.00,NULL,'d8dfb4c67f74eea8e573bf23a80a621cb338cd12',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('INICIAMOS LOS TRABAJOS PREVIA ENTREGA DE PAGO TOTAL ANTICIPADO A LOS 15 DIAS HABILES PARA OBRAS FORANEAS Y EN 5 DIAS HABILES EN OBRAS EN LA CDMX, SIEMPRE Y CUANDO EL ACABADO DE LOS MUROS SEA DE MEZCLA FINO Y LIBRE DE DETALLES DE ALBAÑILERIA Y/O TABLAROCA, EN DICHOS TIEMPOS SE CONSIDERA LA FABRICACION DEL MATERIAL Y EL TRASLADO DE MATERIALES A EL LUGAR DE LOS TRABAJOS.',NULL,0.00,NULL,'1d88a52570b36e2f1f1d92be744eb9c722f44635',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('TODA DEVOLUCIÓN DE CHEQUE, CAUSARA EL 20% DE CARGO DE ACUERDO CON EL ART. 193 DE LA LEY GENERAL DE TÍTULOS Y OPERACIONES DE CRÉDITO.',NULL,0.00,NULL,'cb0cd1a4208b234a027daecffdf6149a4018546a',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('CHISA RECUBRIMIENTOS, S.A. DE C.V. CON DOMICILIO EN ANDRES MOLINA ENRIQUEZ No. 156 COL. SAN PEDRO IZTACALCO, ALCALDÍA IZTACALCO, EN CIUDAD DE MÉXICO, UTILIZARA SUS DATOS PERSONALES RECABADOS UNICA Y EXCLUSIVAMENTE PARA EFECTOS DE PROPORCIONARLE INFORMACION DE NUESTROS SERVICIOS, PRODUCTOS Y COSTOS. PARA MAYOR INFORMACION ACERCA DEL TRATAMIENTO Y DE LOS DERECHOS QUE PUEDE HACER VALER, USTED PUEDE ACCEDER AL AVISO DE PRIVACIDAD INTEGRAL A TRAVES DE NUESTRA PAGINA DE INTERNET www.chisarecubrimiento',NULL,0.00,NULL,'3b4d06776841fda0c9914ffe3d5a9aaa244d8f29',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SIN MAS POR EL MOMENTO, LE REITERO A USTED, LAS SEGURIDADES DE MI MAS DISTINGUIDA CONSIDERACIÓN.',NULL,0.00,NULL,'e3a55ced6fce0980c03a15124d3c5327d99ecc7c',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ACEPTO',NULL,0.00,NULL,'9c431645c481bae6ea6fd8ace8c1f80876168508',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('____________________________________ _______________________________________',NULL,0.00,NULL,'41bd5aaa95c6640357d1d72fb72535b25021f5ff',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ING. HECTOR AGUIRRE CISNEROS CHISA RECUBRIMIENTOS, S.A. DE C.V',NULL,0.00,NULL,'2a32c12861edda669ced8038a6b4dcb089c3dc5a',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON RECUBRIMIENTO EXISTENTE, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACION DE ARENA SILICA MALLA 250-300 A MANO CON LLANA METALICA, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','LOTE DE DETALLES HAS',27794.64,NULL,'c0684381fe92c1a09e63dfa355d62a5739ae088c',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('FORMA DE PAGO Y CONSIDERACIONES GENERALES CORRESPONDIENTES AL PRESUPUESTO REF. 15262/2023.',NULL,0.00,NULL,'d873367ba86d564dfde2163eb32041858da5acba',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('EN CASO DE SER ACEPTADO EL PRESENTE PRESUPUESTO REQUERIMOS DEL PAGO TOTAL, EL CUAL SERA APLICADO A LA FABRICACIÓN, Y GASTOS DE ENVIÓ DE MATERIALES AL LUGAR DE LOS TRABAJOS: AL OTORGAR EL PAGO DE ANTICIPO PARA LA EJECUCIÓN DE LOS TRABAJOS, SE CONSIDERA COMO ACEPTADO EL PRESUPUESTO EN TODOS SUS PUNTOS Y EL MISMO FUNGIRÁ COMO CONTRATO.',NULL,0.00,NULL,'41d9be4b2d602f7323fe32a2fb627d1860fc9a81',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('________________________________ _______________________________________',NULL,0.00,NULL,'fb44d53fc0f4cb0717aac7d6a0f531edb16ee714',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ING. CESAR BRAVO CHISA RECUBRIMIENTOS, S.A. DE C.V',NULL,0.00,NULL,'dacd5fcf75faaf820e2416395f7b10607fbd7881',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2',189.90,NULL,'c579db9c35eeb8b38596649619ab5628fcb7f58c',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2',95.69,NULL,'b04dbb53ba502fd68c4de8d8f55d1d0dc3655e67',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('FORMA DE PAGO Y CONSIDERACIONES GENERALES CORRESPONDIENTES AL PRESUPUESTO REF. 14667/2022.',NULL,0.00,NULL,'e16d54652b1e44315c3b0ec3d9abbeeba00697a0',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('OBRA:',NULL,0.00,NULL,'ed4566a4835b30358ed136af29597bced0e9b23f',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('EN CASO DE SER ACEPTADO EL PRESENTE PRESUPUESTO REQUERIMOS DEL PAGO TOTAL, EL CUAL SERA APLICADO A LA FABRICACIÓN, Y GASTOS DE ENVIÓ DE MATERIALES AL LUGAR DE LOS TRABAJOS: EL PORCENTAJE RESTANTE AL IMPORTE DEL PRESUPUESTO SE PAGARÁ EN ESTIMACIONES SEMANALES SEGÚN EL AVANCE DE LOS TRABAJOS OBJETO DEL PRESENTE, AL OTORGAR EL PAGO DE ANTICIPO PARA LA EJECUCIÓN DE LOS TRABAJOS, SE CONSIDERA COMO ACEPTADO EL PRESUPUESTO EN TODOS SUS PUNTOS Y EL MISMO FUNGIRÁ COMO CONTRATO.',NULL,0.00,NULL,'b2fd21f3523582a87fbce836e3c53bee61d9987e',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('ARQ. ALBERTO BARRETO G. CHISA RECUBRIMIENTOS, S.A. DE C.V',NULL,0.00,NULL,'ac8075c55034cffaaa816d4521337d5bb807d11e',0,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Suministro y aplicación de recubrimiento de prueba','M2',125.50,NULL,'7ad2ef868bade85a55fb1c9815d49f50adee34b6',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('Zoclo de prueba','ML',80.00,NULL,'952b67a250e168e2c36bb1ff22e4bfbccca9baa9',1,'Servicio',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('CHISA GLASS MICRO','PZA',500.00,NULL,'991b953151d51b137cacfbeed15112d0d807cbae',1,'Producto',NULL);
INSERT IGNORE INTO conceptos_comerciales (concepto,unidad,precio_referencia,modelo,huella,activo,tipo,producto_id) VALUES ('SUMINISTRO Y APLICACIÓN DE CHISA GLASS MICRO COLOR 2, INCLUYE MANO DE OBRA Y MATERIAL.','M2',520.00,NULL,'300bf72f9dd517d00ba7c3d7dd3adc646bed9242',1,'Aplicacion',NULL);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB0D9C2B7','1.- MORTERO PLASTICO MALLA 350-400 (REND. 16-18M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9F857B9A','3.- SELLADOR INICIAL (REND. 90M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO7FA7F575','4.- PRAIMER GLASS REF. S.M.A (REND. 85M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOD6575BC1','5.- CHISA GLASS REF. S.M.A (REND. 55-60M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOA8B385CE','6.- VITROGLASS REF. S.M.A (REND. 180-190M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO86163A24','7.- RODILLO DE FELPA (REND 250M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO25212C78','8.- BROCHA PARA APLICACIÓN (REND. 350M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO83B1EB89','9.- LIJA MEDIANA No. 80 (REND. 20M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO79416B66','10.- PAPEL PARA PROTECCIÓN (REND. 250M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOEF47C453','11.- MASKING TAPE PARA PROTECCIÓN (REND. 12.5M2)','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO7D0BA9D0','SUMINISTRO PARA 250 M2','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO2B876C93','SUMINISTRO PARA 500 M2','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO56516C8E','SUMINISTRO PARA 750 M2','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOA784E5CE','PASAJE','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOA69CFC37','SUELDO','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO62B7A9AD','ENVIO','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO5D610E83','HOSPEDAJE','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9730DD24','COMIDAS','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO64BDDF56','Flexómetro de 5 mts uso Rudo','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO0B3C533C','Porta herramienta de carnaza de 13 compartimientos','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9E0294DF','Cutter profesional para navaja de 18 mm','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOE0428DF4','Pinza para Mecánico 10"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO6D99B9DB','Cortador de vidrio profesional 6" con depósito para aceite','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOEFF3DACC','Destapacaños 15m tipo barreno con manivela','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO429746AF','Cepillo para carpintero estriado 14"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO698B8A8C','Prensa con ajuste rápido 36"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO1C3B4ABB','Serrucho 24"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9209E81F','Flota de esponja 12" espesor 12" acabado áspero','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO7A3A5195','Amarrador de varillas','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO145FEE18','Cortador de tubo de cobre 3/16" a 2" cuchilla 18 mm','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOABD896B1','Juego de avellanador','LOTE','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOF66BD42D','Arco 12"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO510FC23F','Caja de herramientas con llantas','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO5EB8A984','Odómetro','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO3606A360','Protector Facial','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOEF6E4700','Kit Contra Cáidas','LOTE','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOA86E51A6','Rodilleras para piso con Gel','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO86284E3D','Taladro Rotomartillo','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO3AA9103D','Taladro Inalámbrico','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB9175BBA','Sierra Circular 1800W','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9170E2C0','Sierra Caladora','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO452C28B4','Lijadora Orbital','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB0789D22','Esmeril Angular','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOAA6C0CA7','Esmeril de Banco','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO30307D97','Juego de desatornilladores combinados','LOTE','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOBBBC864B','Llave Inglesa Ajustable 12" (Perico)','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOC2C956FE','Llave Inglesa Ajustable 15" (Perico)','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO4185EDD0','Llave Steelson 14"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO2AA7AF4C','Llave Steelson 18"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO3C473F4F','Pinzas de presión de nariz 9"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOD523C0D8','Pinza de presión recta 10"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOA9E2EF2C','Juego de Dados y accesorios 3/4"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO3BC15C8A','-','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9C5C7629','Juego de limas con mango bimaterial de 9 piezas','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO1FDC9E74','Escalera de Fibra de vidrio extensión a 6','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO23BD4A64','Escalera de Fibra de vidrio extensión a 24','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOEB459B8A','Escalera de Fibra de vidrio extensión a 28','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9A4C7709','Escalera Plegable de 3 escalones','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO00A9030E','Compresor de Aire Electrónico 8 Litros','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO21E6ABC7','Lijadora para extensión 9 x 23.4 x 8.5 cm','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO441FAD40','Pinzas para electricista 7"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO70836F79','Pinzas de punta cónica 8"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO7E42FB0E','Pinzas de corte diagonal 7"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO5A10B741','Guía Jalacable de nylon 30 mts','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB703F4E8','Cable THW - C10 Caja 100m Negro','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOE846C678','Cable THW - C10 Caja 100m Blanco','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB73540B9','Cable THW - C10 Caja 100m Verde','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOE13944AC','Cable THW - C12 Caja 100m Negro','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO7E8EACCC','Cable THW - C12 Caja 100m Blanco','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOC99C33BA','Cable THW - C12 Caja 100m Verde','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOEB7E11A2','Cable Desnudo C12 100m','SERVICIO','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO19F67662','Interruptor termomagnético 80 amp 3 polos 120/240v 100 a Atornillable','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO8773A624','Sensor de movimiento Infrarrojo 306° con triple sensor SM-360-TS','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCODDD4D407','E32, Luminario con anuncio de salida de acrílico con leds de color verde de 3w a 120- 220 vy bateria recargable de 6 a 8 hrs mod H-9','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB19716B9','Tubo rígido metálico Conduit de lipo Ligero EV 21MM (3/4") 300 CMS','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOBBE978CE','Conector recto de acero para tubo de pared delgada (ETM) con tornillo de fijación 21 mm','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOA06AC277','Finder','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO44178C92','Probador de Tensión sin contacto tipo Lápiz','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOD3C75E5A','Lámpara de mano recargable','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO9FEFE641','Linterna LED tipo Minero en con batería recargable','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO3486B69F','Manifould R410/22/404/134','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO0705B904','Multímetro Industrial','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO61F45289','Temporizador Electrónico 30A, 120-277 Voltaje, Modo Operación: 7 Días Número de canales','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO518B50C8','ELLO-30-15-led-E53, color negro (Salas 1a 8), Luminario de empotrar quince luces de 45x','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB9865569','ELLO-10-5-LED-E3, Color negro (Dulcerías 1,2,5 6, y galerías 3 y4), Luminario de empotrar cinco luces de 15x4.5x5 cms,, incluye dimmer electrónico de 10w 120/227v, de 850 Lúmenes','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO3EBE248B','RMC-20-LED-24V-HCNW , Luminario para riel magnético con LED de 20w a 24 VDC, en color negro de 200 lumenes','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO4B1DC6D6','Interruptor de temporizador mecánico de alta resistencia, metal, resistente a manipulaciones, voltaje 120, 240, 277 v, clasificación NEMA 3R, interior, temporizador para luminarias del camino a Ac, Vasco de Quiroga . Mpd GE 46536','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOF4D7648E','Luminaria LED de 100w 127 - 277 v 60hz 0.757-0.359 a 4000k, Uso exterior','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO0774E4DB','Bomba de Vacío 7CFM, 2 Etapas','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO299B3FE0','Soldadora Inversora de 200A','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOE90F825A','Taladro de Columna de Piso 15"','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO61B6D3BE','Careta eléctrónica para soldar variable de 9 a 13 sombras','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCODFE982D0','Máscara de Cara completa Reutilizable','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB8DEE7CC','Par de Cartuchos con Filtro para pascara','PZA','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO41C53BFC','SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2','Aplicacion','Aplicación',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO150B6C46','SUMINISTRO Y APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF-S.M.A EN MUROS CON APLANADO FINO DE MEZCLA Y/O SUPERFICIE LISA, INCLUYE: LIMPIEZA, LIJADO Y/O REBABEO DE MURO, SEGÚN SEA EL CASO; APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PINTURA ANTIBACTERIAL PROTECT PLUS REF- S.M.A., A MANO CON RODILLO DE FELPA Y/O LLANA METALICA, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, O POR ASPERSIÓN DE AIRE, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE','M2','Aplicacion','Aplicación',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOC579DB9C','1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON SUPERFICIE LISA, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','M2','Aplicacion','Aplicación',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO7AD2EF86','Suministro y aplicación de recubrimiento de prueba','M2','Aplicacion','Aplicación',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO952B67A2','Zoclo de prueba','ML','Otro','Servicio externo',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOC0684381','1.-SUMINISTRO Y APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. EN MUROS CON RECUBRIMIENTO EXISTENTE, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACION DE ARENA SILICA MALLA 250-300 A MANO CON LLANA METALICA, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE PRAIMER GLASS APLICADO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE RECUBRIMIENTO CHISA GLASS REF-S.M.A. CON EQUIPO NEUMÁTICO POR ASPERSIÓN DE AIRE, APLICACIÓN DE VITROGLASS ECOLÓGICO CON RODILLO DE FELPA Y BROCHA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA. NO INCLUYE ANDAMIAJE.','LOTE','Aplicacion','Aplicación',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCOB04DBB53','3.-SUMINISTRO Y APLICACIÓN DE ARENA SILICA MALLA 250-300 EN MUROS PREVIAMENTE PREPARADOS CON MORTERO PLASTICO, INCLUYE : LIMPIEZA Y LIJADO DE MURO, APLICACIÓN DE SELLADOR ACRÍLICO 4648 A MANO CON RODILLO DE FELPA Y BROCHA, APLICACIÓN DE ARENA SILICA MALLA 250-300 APLICADA A MANO CON LLANA METÁLICA, PROTECCIÓN DE SUPERFICIES ADYACENTES CON PAPEL Y MASKING TAPE, LIMPIEZA Y RETIRO DE SOBRANTES PROPIOS HASTA EL LUGAR INDICADO DENTRO DE OBRA, FLETE, Y MANO DE OBRA ESPECIALIZADA, TODO LO ANTERIOR CONSIDERADO HASTA UNA ALTURA DE 2.70 MTS. Y DEMÁS DERIVADOS DEL USO DE HERRAMIENTA, NO INCLUYE ANDAMIAJE.','M2','Aplicacion','Aplicación',1);
INSERT IGNORE INTO conceptos_obra (codigo,descripcion,unidad,tipo,seccion,activo) VALUES ('CCO300BF72F','SUMINISTRO Y APLICACIÓN DE CHISA GLASS MICRO COLOR 2, INCLUYE MANO DE OBRA Y MATERIAL.','M2','Aplicacion','Aplicación',1);
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '13589 HOSP IMSS No1 OAXCA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/13589 HOSP IMSS No1 OAXCA.xlsx', 'xlsx', 9615732, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0017' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/13589 HOSP IMSS No1 OAXCA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16351 HOSPITAL GENERAL TEPIC, NAY 1.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16351 HOSPITAL GENERAL TEPIC, NAY 1.xlsx', 'xlsx', 9620136, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0018' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16351 HOSPITAL GENERAL TEPIC, NAY 1.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16356-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16356-S ILINSA.XLSX', 'xlsx', 50921, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0019' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16356-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16357-S Sumato Supervisión y Mantenimiento.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16357-S Sumato Supervisión y Mantenimiento.xlsx', 'xlsx', 39637, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0020' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16357-S Sumato Supervisión y Mantenimiento.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16358 Sumato Supervisión y Mantenimiento.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16358 Sumato Supervisión y Mantenimiento.xlsx', 'xlsx', 9654254, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0021' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16358 Sumato Supervisión y Mantenimiento.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16359-S PEMCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16359-S PEMCO.xlsx', 'xlsx', 39978, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0022' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16359-S PEMCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16360-S ENSENADA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16360-S ENSENADA.xlsx', 'xlsx', 39669, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0023' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16360-S ENSENADA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16361-S HOSPITAL EL QUEMADO, ACA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16361-S HOSPITAL EL QUEMADO, ACA.xlsx', 'xlsx', 39980, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0024' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16361-S HOSPITAL EL QUEMADO, ACA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16363-S ISSSTE ZONA BOCA DEL RIO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16363-S ISSSTE ZONA BOCA DEL RIO.xlsx', 'xlsx', 39618, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0025' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16363-S ISSSTE ZONA BOCA DEL RIO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16364-S C H 30 EN HUEJUTLA DE REYES, HIDALGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16364-S C H 30 EN HUEJUTLA DE REYES, HIDALGO.xlsx', 'xlsx', 39571, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0026' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16364-S C H 30 EN HUEJUTLA DE REYES, HIDALGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16365 Sumato Supervisión y Mantenimiento.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16365 Sumato Supervisión y Mantenimiento.xlsx', 'xlsx', 9654383, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0027' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16365 Sumato Supervisión y Mantenimiento.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16366-S HGR 23 ENSENADA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16366-S HGR 23 ENSENADA.xlsx', 'xlsx', 40093, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0028' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16366-S HGR 23 ENSENADA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16367 HOSPITAL ISSSTE EMILIANO ZAPATA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16367 HOSPITAL ISSSTE EMILIANO ZAPATA.xlsx', 'xlsx', 9656645, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0029' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16367 HOSPITAL ISSSTE EMILIANO ZAPATA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16368 OOAD CHIAPAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16368 OOAD CHIAPAS.xlsx', 'xlsx', 9656520, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0030' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16368 OOAD CHIAPAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16369-S OOAD CHIAPAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16369-S OOAD CHIAPAS.xlsx', 'xlsx', 40087, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0031' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16369-S OOAD CHIAPAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16371-S LUZ ESQUIVEL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16371-S LUZ ESQUIVEL.xlsx', 'xlsx', 39643, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0032' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16371-S LUZ ESQUIVEL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16372-S ARQ. IVAN OJEDA LOPEZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16372-S ARQ. IVAN OJEDA LOPEZ.xlsx', 'xlsx', 39986, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0033' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16372-S ARQ. IVAN OJEDA LOPEZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16374 HOSPITAL HEMODIALISIS AHEOS, TUXTLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16374 HOSPITAL HEMODIALISIS AHEOS, TUXTLA.xlsx', 'xlsx', 9657304, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0034' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16374 HOSPITAL HEMODIALISIS AHEOS, TUXTLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16375 SECRETARIA DE MARINA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16375 SECRETARIA DE MARINA.xlsx', 'xlsx', 9655576, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0035' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16375 SECRETARIA DE MARINA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16376 SECRETARIA DE MARINA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16376 SECRETARIA DE MARINA.xlsx', 'xlsx', 9655838, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0036' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16376 SECRETARIA DE MARINA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16377-S HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16377-S HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx', 'xlsx', 40431, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0037' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16377-S HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16378 SECRETARIA DE MARINA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16378 SECRETARIA DE MARINA.xlsx', 'xlsx', 9657473, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0038' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16378 SECRETARIA DE MARINA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16379 SECRETARIA DE MARINA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16379 SECRETARIA DE MARINA.xlsx', 'xlsx', 9657108, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0039' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16379 SECRETARIA DE MARINA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16380-S OOAD CHIAPAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16380-S OOAD CHIAPAS.xlsx', 'xlsx', 40082, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0040' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16380-S OOAD CHIAPAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16381 HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16381 HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx', 'xlsx', 9657348, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0041' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16381 HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16382-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16382-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx', 'xlsx', 40123, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0042' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16382-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16383 ASCH.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16383 ASCH.xlsx', 'xlsx', 39737, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0043' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16383 ASCH.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16384 ARQ. IVONE TOBILLA AZCANIO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16384 ARQ. IVONE TOBILLA AZCANIO.xlsx', 'xlsx', 9657407, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0044' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16384 ARQ. IVONE TOBILLA AZCANIO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16385 SHOP ELLAZ CDMX BOSQUES.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16385 SHOP ELLAZ CDMX BOSQUES.XLSX', 'xlsx', 52468, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0045' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16385 SHOP ELLAZ CDMX BOSQUES.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16387 HOSPITAL GENERAL TEPIC, NAY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16387 HOSPITAL GENERAL TEPIC, NAY.xlsx', 'xlsx', 9654351, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0046' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16387 HOSPITAL GENERAL TEPIC, NAY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16388-S DESARROLLO DEL SURESTE 25.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16388-S DESARROLLO DEL SURESTE 25.xlsx', 'xlsx', 40089, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0047' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16388-S DESARROLLO DEL SURESTE 25.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16389 ARTURO MONTER ALGARIN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16389 ARTURO MONTER ALGARIN.xlsx', 'xlsx', 9615023, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0048' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16389 ARTURO MONTER ALGARIN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16389 HOSP IMSS No1 OAXACA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16389 HOSP IMSS No1 OAXACA.xlsx', 'xlsx', 9615320, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0049' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16389 HOSP IMSS No1 OAXACA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16390-S RAMSICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16390-S RAMSICOM.xlsx', 'xlsx', 39269, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0050' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16390-S RAMSICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16391 YALA SA DE CV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16391 YALA SA DE CV.xlsx', 'xlsx', 9653941, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0051' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16391 YALA SA DE CV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16392 YALA SA DE CV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16392 YALA SA DE CV.xlsx', 'xlsx', 9653822, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0052' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16392 YALA SA DE CV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16393 SR. MAURICIO GUEVARA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16393 SR. MAURICIO GUEVARA.xlsx', 'xlsx', 9654112, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0053' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16393 SR. MAURICIO GUEVARA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16394 GRUPO TRITON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16394 GRUPO TRITON.xlsx', 'xlsx', 9654528, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0054' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16394 GRUPO TRITON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16396 SERVICIOS DE SALUD DE DURANGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16396 SERVICIOS DE SALUD DE DURANGO.xlsx', 'xlsx', 9654889, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0055' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16396 SERVICIOS DE SALUD DE DURANGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16397-S SERVICIOS DE SALUD DE DURANGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16397-S SERVICIOS DE SALUD DE DURANGO.xlsx', 'xlsx', 40726, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0056' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16397-S SERVICIOS DE SALUD DE DURANGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16398 IMSS 5 DE MAYO TUXTLA GUTIERREZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16398 IMSS 5 DE MAYO TUXTLA GUTIERREZ.xlsx', 'xlsx', 9654491, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0057' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16398 IMSS 5 DE MAYO TUXTLA GUTIERREZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16398 IMSS 5 DE MAYO TUXTLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16398 IMSS 5 DE MAYO TUXTLA.xlsx', 'xlsx', 40042, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0058' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16398 IMSS 5 DE MAYO TUXTLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16399-S SERVICIOS INTEGRALES LACANJA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16399-S SERVICIOS INTEGRALES LACANJA.xlsx', 'xlsx', 40091, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0059' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16399-S SERVICIOS INTEGRALES LACANJA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16400-S IMSS ZUMPANGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16400-S IMSS ZUMPANGO.xlsx', 'xlsx', 40090, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0060' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16400-S IMSS ZUMPANGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16401 IMSS ZUNPANGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16401 IMSS ZUNPANGO.xlsx', 'xlsx', 9654769, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0061' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16401 IMSS ZUNPANGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16402-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16402-S ILINSA.XLSX', 'xlsx', 51012, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0062' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16402-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16403 HOSPITAL NAVAL DE ACAPULCO, GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16403 HOSPITAL NAVAL DE ACAPULCO, GRO.xlsx', 'xlsx', 9654730, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0063' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16403 HOSPITAL NAVAL DE ACAPULCO, GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16404-S HOSPITAL NAVAL DE ACAPULCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16404-S HOSPITAL NAVAL DE ACAPULCO.xlsx', 'xlsx', 40179, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0064' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16404-S HOSPITAL NAVAL DE ACAPULCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16405 HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16405 HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA.xlsx', 'xlsx', 9654623, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0065' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16405 HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16407 KING OASIS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16407 KING OASIS.xlsx', 'xlsx', 9654551, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0066' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16407 KING OASIS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16408-S HOSPITAL DR. RAYMUNDO ABARCA ALARCON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16408-S HOSPITAL DR. RAYMUNDO ABARCA ALARCON.xlsx', 'xlsx', 39376, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0067' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16408-S HOSPITAL DR. RAYMUNDO ABARCA ALARCON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16409-S REMODELACION IMSS PALMAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16409-S REMODELACION IMSS PALMAS.xlsx', 'xlsx', 39626, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0068' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16409-S REMODELACION IMSS PALMAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16410 ARTURO MONTER ALGARIN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16410 ARTURO MONTER ALGARIN.xlsx', 'xlsx', 9615219, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0069' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16410 ARTURO MONTER ALGARIN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16411 HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16411 HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS.xlsx', 'xlsx', 9617073, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0070' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16411 HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16412-S HOSPITAL DEL IMSS TEPIC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16412-S HOSPITAL DEL IMSS TEPIC.xlsx', 'xlsx', 39483, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0071' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16412-S HOSPITAL DEL IMSS TEPIC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16413 HOSPITAL GENERAL DE ACAPULCO, GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16413 HOSPITAL GENERAL DE ACAPULCO, GRO.xlsx', 'xlsx', 9616011, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0072' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16413 HOSPITAL GENERAL DE ACAPULCO, GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16414-S HOSPITAL REGIONAL DE TOLUCA, ISSEMYM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16414-S HOSPITAL REGIONAL DE TOLUCA, ISSEMYM.xlsx', 'xlsx', 39706, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0073' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16414-S HOSPITAL REGIONAL DE TOLUCA, ISSEMYM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16415-S RAMSICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16415-S RAMSICOM.xlsx', 'xlsx', 39263, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0074' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16415-S RAMSICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16417-S IMSS RIO COLORADO, SON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16417-S IMSS RIO COLORADO, SON.xlsx', 'xlsx', 41519, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0075' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16417-S IMSS RIO COLORADO, SON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16418 SECRETARIA DE MARINA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16418 SECRETARIA DE MARINA.xlsx', 'xlsx', 9618326, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0076' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16418 SECRETARIA DE MARINA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16419 HOSPITAL GENERAL XOCO, CDMX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16419 HOSPITAL GENERAL XOCO, CDMX.xlsx', 'xlsx', 9615923, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0077' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16419 HOSPITAL GENERAL XOCO, CDMX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16420-S GUMESA IMSS HUIXTLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16420-S GUMESA IMSS HUIXTLA.xlsx', 'xlsx', 39248, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0078' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16420-S GUMESA IMSS HUIXTLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16421-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16421-S ILINSA.XLSX', 'xlsx', 50975, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0079' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16421-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16422 HOSPITAL EN MONTERREY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16422 HOSPITAL EN MONTERREY.xlsx', 'xlsx', 9618347, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0080' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16422 HOSPITAL EN MONTERREY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16423-S HOSPITAL DE TLAJOMULCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16423-S HOSPITAL DE TLAJOMULCO.xlsx', 'xlsx', 40561, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0081' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16423-S HOSPITAL DE TLAJOMULCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16424-S HOSPITAL SANTA ROSA LIMA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16424-S HOSPITAL SANTA ROSA LIMA.xlsx', 'xlsx', 40833, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0082' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16424-S HOSPITAL SANTA ROSA LIMA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16425-S OSCAR ALVAREZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16425-S OSCAR ALVAREZ.xlsx', 'xlsx', 39541, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0083' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16425-S OSCAR ALVAREZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16426-S HOSPITAL SANTA ROSA LIMA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16426-S HOSPITAL SANTA ROSA LIMA.xlsx', 'xlsx', 40865, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0084' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16426-S HOSPITAL SANTA ROSA LIMA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16427 CLINICA DEL IMSS MONTERREY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16427 CLINICA DEL IMSS MONTERREY.xlsx', 'xlsx', 9624769, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0085' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16427 CLINICA DEL IMSS MONTERREY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16428 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16428 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9619805, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0086' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16428 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16429-S Sumato Supervisión y Mantenimiento.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16429-S Sumato Supervisión y Mantenimiento.xlsx', 'xlsx', 39522, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0087' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16429-S Sumato Supervisión y Mantenimiento.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16430 HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16430 HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA.xlsx', 'xlsx', 9617036, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0088' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16430 HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16433 HOSPITAL NAVAL DE DOS BOCAS, VER.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16433 HOSPITAL NAVAL DE DOS BOCAS, VER.xlsx', 'xlsx', 9614601, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0089' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16433 HOSPITAL NAVAL DE DOS BOCAS, VER.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16434 HOSPITAL GENERAL DE TEMIXCO-1.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16434 HOSPITAL GENERAL DE TEMIXCO-1.xlsx', 'xlsx', 44721, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0090' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16434 HOSPITAL GENERAL DE TEMIXCO-1.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16434 HOSPITAL GENERAL DE TEMIXCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16434 HOSPITAL GENERAL DE TEMIXCO.xlsx', 'xlsx', 44645, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0091' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16434 HOSPITAL GENERAL DE TEMIXCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16434-1 HOSPITAL GENERAL DE TEMIXCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16434-1 HOSPITAL GENERAL DE TEMIXCO.xlsx', 'xlsx', 44777, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0092' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16434-1 HOSPITAL GENERAL DE TEMIXCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16434-2 HOSPITAL GENERAL DE TEMIXCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16434-2 HOSPITAL GENERAL DE TEMIXCO.xlsx', 'xlsx', 44630, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0093' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16434-2 HOSPITAL GENERAL DE TEMIXCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16435 HOSPITAL NAVAL DE ACAPULCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16435 HOSPITAL NAVAL DE ACAPULCO.xlsx', 'xlsx', 9618029, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0094' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16435 HOSPITAL NAVAL DE ACAPULCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16436 HOSPITAL NAVAL DE ACAPULCO 1.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16436 HOSPITAL NAVAL DE ACAPULCO 1.xlsx', 'xlsx', 9620090, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0095' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16436 HOSPITAL NAVAL DE ACAPULCO 1.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16437-S RAMSICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16437-S RAMSICOM.xlsx', 'xlsx', 39271, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0096' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16437-S RAMSICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16438 SHOP ELLAZ TOWN SQUARE METEPEC.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16438 SHOP ELLAZ TOWN SQUARE METEPEC.XLSX', 'xlsx', 52640, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0097' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16438 SHOP ELLAZ TOWN SQUARE METEPEC.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16439-S MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16439-S MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx', 'xlsx', 40939, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0098' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16439-S MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16440  MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16440  MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx', 'xlsx', 9618689, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0099' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16440  MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16442-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16442-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx', 'xlsx', 39299, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0100' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16442-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16443-S P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR).XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16443-S P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR).XLSX', 'xlsx', 52412, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0101' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16443-S P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR).XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16444-S UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16444-S UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx', 'xlsx', 39559, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0102' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16444-S UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16445 GRUPO TRITON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16445 GRUPO TRITON.xlsx', 'xlsx', 9614994, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0103' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16445 GRUPO TRITON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16446-S GRUPO MPV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16446-S GRUPO MPV.xlsx', 'xlsx', 39532, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0104' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16446-S GRUPO MPV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16447  GRUPO MPV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16447  GRUPO MPV.xlsx', 'xlsx', 9615300, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0105' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16447  GRUPO MPV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16448 SERVICIOS DE MANTENIMIENTO ORGALIM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16448 SERVICIOS DE MANTENIMIENTO ORGALIM.xlsx', 'xlsx', 9615565, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0106' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16448 SERVICIOS DE MANTENIMIENTO ORGALIM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16450-S COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16450-S COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2.xlsx', 'xlsx', 40851, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0107' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16450-S COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16451-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16451-S ILINSA.XLSX', 'xlsx', 51012, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0108' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16451-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16452-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16452-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx', 'xlsx', 40948, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0109' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16452-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16453-S HOSPITAL  20 DE NOVIEMBRE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16453-S HOSPITAL  20 DE NOVIEMBRE.xlsx', 'xlsx', 40631, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0110' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16453-S HOSPITAL  20 DE NOVIEMBRE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16454 20 DE NOVIEMBRE, ASCH.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16454 20 DE NOVIEMBRE, ASCH.xlsx', 'xlsx', 40955, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0111' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16454 20 DE NOVIEMBRE, ASCH.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16455-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16455-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx', 'xlsx', 56441, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0112' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16455-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16456-S UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16456-S UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx', 'xlsx', 40904, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0113' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16456-S UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16457 UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16457 UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx', 'xlsx', 9614409, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0114' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16457 UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16458-S HOSPITAL EN AGUASCALIENTES.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16458-S HOSPITAL EN AGUASCALIENTES.xlsx', 'xlsx', 42005, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0115' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16458-S HOSPITAL EN AGUASCALIENTES.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16459-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16459-S ILINSA.XLSX', 'xlsx', 50985, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0116' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16459-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16460-S SHOP ELLAZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16460-S SHOP ELLAZ.xlsx', 'xlsx', 39410, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0117' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16460-S SHOP ELLAZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16461-S SHOP ELLAZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16461-S SHOP ELLAZ.xlsx', 'xlsx', 39958, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0118' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16461-S SHOP ELLAZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16462 UMF 15 DE MONTERREY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16462 UMF 15 DE MONTERREY.xlsx', 'xlsx', 9619659, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0119' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16462 UMF 15 DE MONTERREY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16463-COTIZACION.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16463-COTIZACION.xlsx', 'xlsx', 40196, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0120' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16463-COTIZACION.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16464 ADRIANA AGUIRRE R..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16464 ADRIANA AGUIRRE R..xlsx', 'xlsx', 39745, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0121' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16464 ADRIANA AGUIRRE R..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16464 SICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16464 SICOM.xlsx', 'xlsx', 9615008, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0122' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16464 SICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16465-S RAMSICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16465-S RAMSICOM.xlsx', 'xlsx', 39242, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0123' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16465-S RAMSICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16467 CONSTRUCCIONES HERSO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16467 CONSTRUCCIONES HERSO.xlsx', 'xlsx', 9617627, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0124' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16467 CONSTRUCCIONES HERSO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16468-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16468-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx', 'xlsx', 40203, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0125' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16468-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16469 SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16469 SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES.xlsx', 'xlsx', 9617542, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0126' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16469 SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16470 ING. RAUL ZEFERINO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16470 ING. RAUL ZEFERINO.xlsx', 'xlsx', 39982, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0127' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16470 ING. RAUL ZEFERINO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16471 SHOP ELLAZ GALERIAS ATIZAPAN.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16471 SHOP ELLAZ GALERIAS ATIZAPAN.XLSX', 'xlsx', 52761, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0128' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16471 SHOP ELLAZ GALERIAS ATIZAPAN.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16472 IMSS 5 DE MAYO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16472 IMSS 5 DE MAYO.xlsx', 'xlsx', 9617488, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0129' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16472 IMSS 5 DE MAYO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16473  IMSS CHETUMAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16473  IMSS CHETUMAL.xlsx', 'xlsx', 9615788, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0130' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16473  IMSS CHETUMAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16474-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16474-S ILINSA.XLSX', 'xlsx', 51011, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0131' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16474-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16475 IMSS 5 DE MAYO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16475 IMSS 5 DE MAYO.xlsx', 'xlsx', 9617159, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0132' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16475 IMSS 5 DE MAYO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16476-S PEMCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16476-S PEMCO.xlsx', 'xlsx', 39721, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0133' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16476-S PEMCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16477 IMSS 5 DE MAYO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16477 IMSS 5 DE MAYO.xlsx', 'xlsx', 9617409, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0134' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16477 IMSS 5 DE MAYO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16478-S GUMESA IMSS HUIXTLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16478-S GUMESA IMSS HUIXTLA.xlsx', 'xlsx', 40651, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0135' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16478-S GUMESA IMSS HUIXTLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16479 PRODEI.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16479 PRODEI.xlsx', 'xlsx', 9615362, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0136' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16479 PRODEI.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16480-S HGR 216 CAMAS ENSENADA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16480-S HGR 216 CAMAS ENSENADA.xlsx', 'xlsx', 40073, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0137' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16480-S HGR 216 CAMAS ENSENADA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16481-S Sumato Supervisión y Mantenimiento.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16481-S Sumato Supervisión y Mantenimiento.xlsx', 'xlsx', 40963, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0138' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16481-S Sumato Supervisión y Mantenimiento.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16483 IMSS BIENESTAR CHIAPAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16483 IMSS BIENESTAR CHIAPAS.xlsx', 'xlsx', 9618597, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0139' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16483 IMSS BIENESTAR CHIAPAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16484-S ULSA CONSORCIO URBANISTA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16484-S ULSA CONSORCIO URBANISTA.xlsx', 'xlsx', 41412, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0140' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16484-S ULSA CONSORCIO URBANISTA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16486 CLINICA DEL IMSS EN MONTERREY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16486 CLINICA DEL IMSS EN MONTERREY.xlsx', 'xlsx', 39542, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0141' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16486 CLINICA DEL IMSS EN MONTERREY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16487 INCOP.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16487 INCOP.XLSX', 'xlsx', 54487, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0142' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16487 INCOP.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16488-S RAMSICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16488-S RAMSICOM.xlsx', 'xlsx', 39243, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0143' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16488-S RAMSICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16490-S ARQ. VICTORIA  PEREZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16490-S ARQ. VICTORIA  PEREZ.xlsx', 'xlsx', 39843, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0144' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16490-S ARQ. VICTORIA  PEREZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16491-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16491-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx', 'xlsx', 39978, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0145' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16491-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16492 JAVIER CEJUDO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16492 JAVIER CEJUDO.xlsx', 'xlsx', 9620745, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0146' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16492 JAVIER CEJUDO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16493-S DICOREMA ARQUITECTO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16493-S DICOREMA ARQUITECTO.xlsx', 'xlsx', 39815, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0147' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16493-S DICOREMA ARQUITECTO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16494-S CONSTRUCTORA RECO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16494-S CONSTRUCTORA RECO.xlsx', 'xlsx', 86225, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0148' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16494-S CONSTRUCTORA RECO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16494-S OYM, S.A DE C.V.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16494-S OYM, S.A DE C.V.xlsx', 'xlsx', 84152, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0149' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16494-S OYM, S.A DE C.V.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16495-S HGZ 10 MANZANILLO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16495-S HGZ 10 MANZANILLO.xlsx', 'xlsx', 41693, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0150' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16495-S HGZ 10 MANZANILLO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16496-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16496-S ILINSA.XLSX', 'xlsx', 53378, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0151' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16496-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16497-S ARQ. VICTORIA  PEREZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16497-S ARQ. VICTORIA  PEREZ.xlsx', 'xlsx', 39841, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0152' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16497-S ARQ. VICTORIA  PEREZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16498 SHOP ELLAZ ZONA ROSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16498 SHOP ELLAZ ZONA ROSA.XLSX', 'xlsx', 52742, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0153' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16498 SHOP ELLAZ ZONA ROSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16499 GRUPO TRITON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16499 GRUPO TRITON.xlsx', 'xlsx', 9615549, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0154' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16499 GRUPO TRITON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16502 EDGAR CONSTANTINO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16502 EDGAR CONSTANTINO.xlsx', 'xlsx', 9619879, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0155' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16502 EDGAR CONSTANTINO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16503 PRODEMEX.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16503 PRODEMEX.XLSX', 'xlsx', 53991, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0156' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16503 PRODEMEX.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16504-S PRODEMEX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16504-S PRODEMEX.xlsx', 'xlsx', 44959, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0157' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16504-S PRODEMEX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16505 CONCRETO Y ESPACIO URBANO,SON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16505 CONCRETO Y ESPACIO URBANO,SON.xlsx', 'xlsx', 9619427, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0158' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16505 CONCRETO Y ESPACIO URBANO,SON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16506-S HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16506-S HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON.xlsx', 'xlsx', 39829, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0159' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16506-S HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16507 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16507 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9619725, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0160' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16507 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16508-S IMSS COLIMA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16508-S IMSS COLIMA.xlsx', 'xlsx', 45303, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0161' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16508-S IMSS COLIMA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16509  HOSPITAL 20 DE NOVIEMBRE GERFLOR.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16509  HOSPITAL 20 DE NOVIEMBRE GERFLOR.xlsx', 'xlsx', 9619009, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0162' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16509  HOSPITAL 20 DE NOVIEMBRE GERFLOR.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16510  HOSPITAL 20 DE NOVIEMBRE POLYFLOR.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16510  HOSPITAL 20 DE NOVIEMBRE POLYFLOR.xlsx', 'xlsx', 9614536, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0163' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16510  HOSPITAL 20 DE NOVIEMBRE POLYFLOR.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16511-S RAMSICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16511-S RAMSICOM.xlsx', 'xlsx', 41298, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0164' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16511-S RAMSICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16512-S DESARROLLO DEL SURESTE 25.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16512-S DESARROLLO DEL SURESTE 25.xlsx', 'xlsx', 42345, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0165' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16512-S DESARROLLO DEL SURESTE 25.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16513 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16513 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9619007, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0166' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16513 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16514-S PRODEMEX GUANAJUATO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16514-S PRODEMEX GUANAJUATO.xlsx', 'xlsx', 41283, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0167' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16514-S PRODEMEX GUANAJUATO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16515-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16515-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx', 'xlsx', 41512, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0168' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16515-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16516 ARQ LAURA IVETTE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16516 ARQ LAURA IVETTE.xlsx', 'xlsx', 9619443, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0169' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16516 ARQ LAURA IVETTE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16517 CECAN XALAPA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16517 CECAN XALAPA.xlsx', 'xlsx', 9618754, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0170' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16517 CECAN XALAPA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16518 JALAPA, VER.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16518 JALAPA, VER.xlsx', 'xlsx', 9618632, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0171' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16518 JALAPA, VER.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16519 HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL).xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16519 HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL).xlsx', 'xlsx', 9617595, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0172' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16519 HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL).xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16520-S ING. ABRAHAM GALLARDO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16520-S ING. ABRAHAM GALLARDO.xlsx', 'xlsx', 41236, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0173' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16520-S ING. ABRAHAM GALLARDO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16521 20 DE NOVIEMBRE, ASCH.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16521 20 DE NOVIEMBRE, ASCH.xlsx', 'xlsx', 42117, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0174' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16521 20 DE NOVIEMBRE, ASCH.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16522-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16522-S ILINSA.XLSX', 'xlsx', 55305, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0175' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16522-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16523- ARQ. JESUS IGLESIAS CORNELIO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16523- ARQ. JESUS IGLESIAS CORNELIO.XLSX', 'xlsx', 55046, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0176' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16523- ARQ. JESUS IGLESIAS CORNELIO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16524 UAM AZCAPOTZALCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16524 UAM AZCAPOTZALCO.xlsx', 'xlsx', 42373, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0177' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16524 UAM AZCAPOTZALCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16525 UAM AZCAPOTZALCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16525 UAM AZCAPOTZALCO.xlsx', 'xlsx', 9618203, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0178' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16525 UAM AZCAPOTZALCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16526  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16526  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx', 'xlsx', 9618498, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0179' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16526  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16527 ARQ. IVONE TOBILLA AZCANIO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16527 ARQ. IVONE TOBILLA AZCANIO.xlsx', 'xlsx', 9620118, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0180' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16527 ARQ. IVONE TOBILLA AZCANIO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16528-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16528-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx', 'xlsx', 42119, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0181' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16528-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16529 CLINICA 23 DEL IMSS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16529 CLINICA 23 DEL IMSS.xlsx', 'xlsx', 9620015, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0182' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16529 CLINICA 23 DEL IMSS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16530-S CLINICA 23 DEL IMSS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16530-S CLINICA 23 DEL IMSS.xlsx', 'xlsx', 41850, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0183' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16530-S CLINICA 23 DEL IMSS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16531 IMSS EN OCOZOCUAUTLA, CHIA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16531 IMSS EN OCOZOCUAUTLA, CHIA.xlsx', 'xlsx', 9627156, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0184' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16531 IMSS EN OCOZOCUAUTLA, CHIA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16532 CINETECA NACIONAL CHAPULTEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16532 CINETECA NACIONAL CHAPULTEPEC.xlsx', 'xlsx', 9622341, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0185' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16532 CINETECA NACIONAL CHAPULTEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16533 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16533 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9619002, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0186' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16533 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16533-1 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16533-1 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9618833, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0187' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16533-1 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16533-2 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16533-2 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9618966, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0188' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16533-2 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16533-3 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16533-3 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9617821, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0189' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16533-3 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16534 DESARROLLO DEL SURESTE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16534 DESARROLLO DEL SURESTE.xlsx', 'xlsx', 9619776, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0190' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16534 DESARROLLO DEL SURESTE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16535-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16535-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx', 'xlsx', 41855, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0191' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16535-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16536-S DESARROLLO DEL SURESTE 25.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16536-S DESARROLLO DEL SURESTE 25.xlsx', 'xlsx', 77060, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0192' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16536-S DESARROLLO DEL SURESTE 25.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16537 SICOM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16537 SICOM.xlsx', 'xlsx', 9619386, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0193' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16537 SICOM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16538 CONSTRUCTORA EMACO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16538 CONSTRUCTORA EMACO.xlsx', 'xlsx', 9618613, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0194' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16538 CONSTRUCTORA EMACO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16539-S  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16539-S  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx', 'xlsx', 42176, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0195' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16539-S  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16539-S -1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16539-S -1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx', 'xlsx', 66561, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0196' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16539-S -1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16540-S MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16540-S MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN.xlsx', 'xlsx', 42397, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0197' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16540-S MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16541-S SHOP ELLAZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16541-S SHOP ELLAZ.xlsx', 'xlsx', 41788, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0198' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16541-S SHOP ELLAZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16542-S IMSS CHETUMAL DESC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16542-S IMSS CHETUMAL DESC.xlsx', 'xlsx', 42198, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0199' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16542-S IMSS CHETUMAL DESC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16542-S IMSS CHETUMAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16542-S IMSS CHETUMAL.xlsx', 'xlsx', 42070, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0200' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16542-S IMSS CHETUMAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16543-S ANMED SA DE CV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16543-S ANMED SA DE CV.xlsx', 'xlsx', 42182, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0201' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16543-S ANMED SA DE CV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16545-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16545-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx', 'xlsx', 41943, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0202' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16545-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16546 SHOP ELLAZ SAN AGUSTIN, MTY.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16546 SHOP ELLAZ SAN AGUSTIN, MTY.XLSX', 'xlsx', 54820, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0203' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16546 SHOP ELLAZ SAN AGUSTIN, MTY.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16547-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16547-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC.xlsx', 'xlsx', 42343, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0204' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16547-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16548-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16548-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx', 'xlsx', 42877, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0205' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16548-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16549 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16549 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx', 'xlsx', 9620084, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0206' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16549 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16550 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16550 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 9621810, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0207' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16550 HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16551 HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16551 HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx', 'xlsx', 9618959, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0208' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16551 HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16552-S HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16552-S HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx', 'xlsx', 42699, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0209' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16552-S HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16554-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16554-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx', 'xlsx', 41520, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0210' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16554-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16555-S ING EDUARDO FLORES, PUEBLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16555-S ING EDUARDO FLORES, PUEBLA.xlsx', 'xlsx', 41580, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0211' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16555-S ING EDUARDO FLORES, PUEBLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16556-S ING EDUARDO FLORES, PUEBLA DESC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16556-S ING EDUARDO FLORES, PUEBLA DESC.xlsx', 'xlsx', 42251, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0212' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16556-S ING EDUARDO FLORES, PUEBLA DESC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16556-S ING EDUARDO FLORES, PUEBLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16556-S ING EDUARDO FLORES, PUEBLA.xlsx', 'xlsx', 42093, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0213' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16556-S ING EDUARDO FLORES, PUEBLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16557-S SHOP ELLAZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16557-S SHOP ELLAZ.xlsx', 'xlsx', 41961, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0214' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16557-S SHOP ELLAZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16558 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16558 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx', 'xlsx', 9621415, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0215' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16558 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16559 CLINICA DEL IMSS MONTERREY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16559 CLINICA DEL IMSS MONTERREY.xlsx', 'xlsx', 9618614, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0216' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16559 CLINICA DEL IMSS MONTERREY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16559 HOSPITAL DEL IMSS MONTERREY 1.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16559 HOSPITAL DEL IMSS MONTERREY 1.xlsx', 'xlsx', 9622993, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0217' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16559 HOSPITAL DEL IMSS MONTERREY 1.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16560 HOSPITAL DEL IMSS MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16560 HOSPITAL DEL IMSS MTY.xlsx', 'xlsx', 9620551, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0218' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16560 HOSPITAL DEL IMSS MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16561-S CLINICA DEL IMSS MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16561-S CLINICA DEL IMSS MTY.xlsx', 'xlsx', 42772, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0219' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16561-S CLINICA DEL IMSS MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16563-S CLINICA EN GUASAVE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16563-S CLINICA EN GUASAVE.xlsx', 'xlsx', 42695, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0220' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16563-S CLINICA EN GUASAVE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16564 UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16564 UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO.xlsx', 'xlsx', 42629, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0221' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16564 UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16565 20 DE NOVIEMBRE, ASCH.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16565 20 DE NOVIEMBRE, ASCH.xlsx', 'xlsx', 42122, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0222' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16565 20 DE NOVIEMBRE, ASCH.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16565 HOSPITAL IGNACIO ZARAGOZA, ASCH.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16565 HOSPITAL IGNACIO ZARAGOZA, ASCH.xlsx', 'xlsx', 42473, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0223' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16565 HOSPITAL IGNACIO ZARAGOZA, ASCH.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16566 2G INGENIERIA Y ARQUITECTURA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16566 2G INGENIERIA Y ARQUITECTURA.xlsx', 'xlsx', 9619459, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0224' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16566 2G INGENIERIA Y ARQUITECTURA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16567-S DIGITAL GG MEXICO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16567-S DIGITAL GG MEXICO.XLSX', 'xlsx', 54988, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0225' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16567-S DIGITAL GG MEXICO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16568-S ISSSTE VASCO DE QUIROGA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16568-S ISSSTE VASCO DE QUIROGA.xlsx', 'xlsx', 81974, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0226' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16568-S ISSSTE VASCO DE QUIROGA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16569 ISSSTE VASCO DE QUIROGA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16569 ISSSTE VASCO DE QUIROGA.xlsx', 'xlsx', 9622259, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0227' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16569 ISSSTE VASCO DE QUIROGA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16569-1 ISSSTE VASCO DE QUIROGA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16569-1 ISSSTE VASCO DE QUIROGA.xlsx', 'xlsx', 9619801, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0228' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16569-1 ISSSTE VASCO DE QUIROGA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16570-S PRODEMEX TUXTLA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16570-S PRODEMEX TUXTLA.xlsx', 'xlsx', 78629, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0229' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16570-S PRODEMEX TUXTLA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16571 - S HOSP CRUZ AZUL, LAGUNAS OAX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16571 - S HOSP CRUZ AZUL, LAGUNAS OAX.xlsx', 'xlsx', 39540, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0230' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16571 - S HOSP CRUZ AZUL, LAGUNAS OAX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16571 HOSP CRUZ AZUL, LAGUNAS OAX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16571 HOSP CRUZ AZUL, LAGUNAS OAX.xlsx', 'xlsx', 9619379, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0231' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16571 HOSP CRUZ AZUL, LAGUNAS OAX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16572-S SHOP ELLAZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16572-S SHOP ELLAZ.xlsx', 'xlsx', 41710, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0232' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16572-S SHOP ELLAZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16573 BODEGA , QRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16573 BODEGA , QRO.xlsx', 'xlsx', 9616401, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0233' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16573 BODEGA , QRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16574-S JAIME BAUTISTA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16574-S JAIME BAUTISTA.XLSX', 'xlsx', 55006, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0234' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16574-S JAIME BAUTISTA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16575 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16575 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'xlsx', 9618538, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0235' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16575 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16576 CLINICA DEL IMSS MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16576 CLINICA DEL IMSS MTY.xlsx', 'xlsx', 9619937, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0236' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16576 CLINICA DEL IMSS MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16577-S CLINICA DEL IMSS MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16577-S CLINICA DEL IMSS MTY.xlsx', 'xlsx', 42752, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0237' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16577-S CLINICA DEL IMSS MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16578 REYNA MENDOZA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16578 REYNA MENDOZA.xlsx', 'xlsx', 9619536, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0238' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16578 REYNA MENDOZA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16579 HOSPITAL DE SANTIAGO PAPASQUIARO, DGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16579 HOSPITAL DE SANTIAGO PAPASQUIARO, DGO.xlsx', 'xlsx', 9617555, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0239' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16579 HOSPITAL DE SANTIAGO PAPASQUIARO, DGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16580 CLINICA DEL IMSS 15 MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16580 CLINICA DEL IMSS 15 MTY.xlsx', 'xlsx', 9620131, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0240' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16580 CLINICA DEL IMSS 15 MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16581-S CLINICA DEL IMSS 15 MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16581-S CLINICA DEL IMSS 15 MTY.xlsx', 'xlsx', 42871, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0241' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16581-S CLINICA DEL IMSS 15 MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16582 HOSPITAL DE SANTIAGO PAPASQUIARO,DGO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16582 HOSPITAL DE SANTIAGO PAPASQUIARO,DGO.xlsx', 'xlsx', 9618656, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0242' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16582 HOSPITAL DE SANTIAGO PAPASQUIARO,DGO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16583 CLINICA DEL IMSS 15 DE MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16583 CLINICA DEL IMSS 15 DE MTY.xlsx', 'xlsx', 9620037, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0243' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16583 CLINICA DEL IMSS 15 DE MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16584-S CLINICA DEL IMSS 15 DE MTY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16584-S CLINICA DEL IMSS 15 DE MTY.xlsx', 'xlsx', 42814, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0244' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16584-S CLINICA DEL IMSS 15 DE MTY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16585 RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16585 RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx', 'xlsx', 9619542, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0245' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16585 RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16586-S RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16586-S RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx', 'xlsx', 81035, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0246' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16586-S RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16587 PARK RESERVE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16587 PARK RESERVE.xlsx', 'xlsx', 9620454, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0247' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16587 PARK RESERVE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16588-S CASA HABITACION TEXCOCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16588-S CASA HABITACION TEXCOCO.xlsx', 'xlsx', 42039, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0248' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16588-S CASA HABITACION TEXCOCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16589 UAM UNIDAD XOCHIMILCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16589 UAM UNIDAD XOCHIMILCO.xlsx', 'xlsx', 42136, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0249' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16589 UAM UNIDAD XOCHIMILCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16589 UAM XOCHIMILCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16589 UAM XOCHIMILCO.xlsx', 'xlsx', 9620173, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0250' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16589 UAM XOCHIMILCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16590 HOSPITAL IMSS DE MORELIA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16590 HOSPITAL IMSS DE MORELIA.xlsx', 'xlsx', 9617277, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0251' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16590 HOSPITAL IMSS DE MORELIA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16591 HOSPITAL EN HERMOSILLO, SON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16591 HOSPITAL EN HERMOSILLO, SON.xlsx', 'xlsx', 9618815, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0252' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16591 HOSPITAL EN HERMOSILLO, SON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16592-S HOSPITAL EN HERMOSILLO, SON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16592-S HOSPITAL EN HERMOSILLO, SON.xlsx', 'xlsx', 81085, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0253' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16592-S HOSPITAL EN HERMOSILLO, SON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16593 PROMOCAT.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16593 PROMOCAT.xlsx', 'xlsx', 9618491, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0254' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16593 PROMOCAT.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16595 HOSPITAL DE CELAYA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16595 HOSPITAL DE CELAYA.xlsx', 'xlsx', 9737887, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0255' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16595 HOSPITAL DE CELAYA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16597-S HOSPITAL SAN LUIS RIO COLORADO,SON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16597-S HOSPITAL SAN LUIS RIO COLORADO,SON.xlsx', 'xlsx', 81188, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0256' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16597-S HOSPITAL SAN LUIS RIO COLORADO,SON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16598 INSTITUTO NACIONAL DE NEUROLOGIA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16598 INSTITUTO NACIONAL DE NEUROLOGIA.xlsx', 'xlsx', 9618116, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0257' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16598 INSTITUTO NACIONAL DE NEUROLOGIA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16599 - S 1 FARMACIA  CRUZ AZUL, TULA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16599 - S 1 FARMACIA  CRUZ AZUL, TULA.xlsx', 'xlsx', 74925, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0258' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16599 - S 1 FARMACIA  CRUZ AZUL, TULA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16599 - S FARMACIA  CRUZ AZUL, TULA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16599 - S FARMACIA  CRUZ AZUL, TULA.xlsx', 'xlsx', 75402, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0259' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16599 - S FARMACIA  CRUZ AZUL, TULA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16600 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16600 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 9620683, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0260' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16600 HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16601 HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16601 HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx', 'xlsx', 9620079, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0261' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16601 HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16602-S HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16602-S HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx', 'xlsx', 75322, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0262' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16602-S HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16603 HOSPITAL COMUNITARIO ROMITA, GTO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16603 HOSPITAL COMUNITARIO ROMITA, GTO.xlsx', 'xlsx', 9623405, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0263' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16603 HOSPITAL COMUNITARIO ROMITA, GTO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16604-S  POLY GOON ESTUDIO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16604-S  POLY GOON ESTUDIO.xlsx', 'xlsx', 72732, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0264' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16604-S  POLY GOON ESTUDIO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16605 HOSPITAL EN CHILPANCINGO, GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16605 HOSPITAL EN CHILPANCINGO, GRO.xlsx', 'xlsx', 9620677, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0265' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16605 HOSPITAL EN CHILPANCINGO, GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16606-S HOSPITAL EN CHILPANCINGO, GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16606-S HOSPITAL EN CHILPANCINGO, GRO.xlsx', 'xlsx', 75112, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0266' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16606-S HOSPITAL EN CHILPANCINGO, GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16607 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16607 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx', 'xlsx', 9621124, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0267' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16607 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16608 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16608 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'xlsx', 9617387, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0268' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16608 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16608-1 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16608-1 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'xlsx', 9618471, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0269' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16608-1 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16609-S HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16609-S HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx', 'xlsx', 75001, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0270' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16609-S HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16610-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16610-S ILINSA.XLSX', 'xlsx', 55177, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0271' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16610-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16611 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16611 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9618716, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0272' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16611 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16612 PREFIMAX SA DE CV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16612 PREFIMAX SA DE CV.xlsx', 'xlsx', 9621768, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0273' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16612 PREFIMAX SA DE CV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16613-S PREFIMAX SA DE CV.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16613-S PREFIMAX SA DE CV.xlsx', 'xlsx', 75219, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0274' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16613-S PREFIMAX SA DE CV.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16614 UMF 15 DE MONTERREY.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16614 UMF 15 DE MONTERREY.xlsx', 'xlsx', 9620835, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0275' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16614 UMF 15 DE MONTERREY.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16615 HOSPITAL INFANTIL NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16615 HOSPITAL INFANTIL NVO LEON.xlsx', 'xlsx', 9625924, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0276' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16615 HOSPITAL INFANTIL NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16616 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16616 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx', 'xlsx', 9619160, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0277' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16616 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16617 HELIPUERTO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16617 HELIPUERTO.xlsx', 'xlsx', 9623955, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0278' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16617 HELIPUERTO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16618-S  MAUVAN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16618-S  MAUVAN.xlsx', 'xlsx', 72665, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0279' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16618-S  MAUVAN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16619-1 CUAUTITLAN IZCALLI.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16619-1 CUAUTITLAN IZCALLI.xlsx', 'xlsx', 9618804, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0280' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16619-1 CUAUTITLAN IZCALLI.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16620-S CLINICA ESTETICA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16620-S CLINICA ESTETICA.xlsx', 'xlsx', 76272, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0281' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16620-S CLINICA ESTETICA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16621-S GAE SOLUCIONES CONSTRUCTIVAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16621-S GAE SOLUCIONES CONSTRUCTIVAS.xlsx', 'xlsx', 74720, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0282' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16621-S GAE SOLUCIONES CONSTRUCTIVAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16622  GAE SOLUCIONES CONSTRUCTIVAS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16622  GAE SOLUCIONES CONSTRUCTIVAS.xlsx', 'xlsx', 9620519, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0283' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16622  GAE SOLUCIONES CONSTRUCTIVAS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16623 CLINICA ESTETICA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16623 CLINICA ESTETICA.xlsx', 'xlsx', 9619699, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0284' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16623 CLINICA ESTETICA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16624-S SINDICATO NACIONAL DE TRABAJADORES DEL IMSS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16624-S SINDICATO NACIONAL DE TRABAJADORES DEL IMSS.xlsx', 'xlsx', 75889, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0285' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16624-S SINDICATO NACIONAL DE TRABAJADORES DEL IMSS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16625 HOSPITAL 20 DE NOVIEMBRE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16625 HOSPITAL 20 DE NOVIEMBRE.xlsx', 'xlsx', 9619238, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0286' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16625 HOSPITAL 20 DE NOVIEMBRE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16625-1 HOSPITAL 20 DE NOVIEMBRE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16625-1 HOSPITAL 20 DE NOVIEMBRE.xlsx', 'xlsx', 9621752, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0287' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16625-1 HOSPITAL 20 DE NOVIEMBRE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16628 HOSPITAL INFANTIL NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16628 HOSPITAL INFANTIL NVO LEON.xlsx', 'xlsx', 9620307, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0288' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16628 HOSPITAL INFANTIL NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16628-1 HOSPITAL INFANTIL NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16628-1 HOSPITAL INFANTIL NVO LEON.xlsx', 'xlsx', 9620156, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0289' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16628-1 HOSPITAL INFANTIL NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16629 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16629 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 9640588, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0290' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16629 HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16629-1 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16629-1 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 9624631, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0291' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16629-1 HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16629-2 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16629-2 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 9625702, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0292' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16629-2 HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16629-C HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16629-C HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 113809, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0293' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16629-C HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16630-S COGUTSA MATERIALES.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16630-S COGUTSA MATERIALES.xlsx', 'xlsx', 76305, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0294' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16630-S COGUTSA MATERIALES.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16631-S CLINICA ESTETICA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16631-S CLINICA ESTETICA.xlsx', 'xlsx', 76261, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0295' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16631-S CLINICA ESTETICA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16631-S HOSPITAL PABELLON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16631-S HOSPITAL PABELLON.xlsx', 'xlsx', 76378, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0296' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16631-S HOSPITAL PABELLON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16632 HOSPITAL PABELLON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16632 HOSPITAL PABELLON.xlsx', 'xlsx', 9620180, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0297' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16632 HOSPITAL PABELLON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16633 UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16633 UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx', 'xlsx', 9619851, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0298' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16633 UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16634-S ACABADOS LHOGAR.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16634-S ACABADOS LHOGAR.xlsx', 'xlsx', 77808, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0299' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16634-S ACABADOS LHOGAR.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16634-S HOSPITAL DE SAN LUIS RIO COLORDO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16634-S HOSPITAL DE SAN LUIS RIO COLORDO.xlsx', 'xlsx', 42644, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0300' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16634-S HOSPITAL DE SAN LUIS RIO COLORDO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16635 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16635 HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 9621323, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0301' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16635 HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16635-C HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16635-C HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 78264, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0302' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16635-C HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16636-S  MARCELINO ONOFRE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16636-S  MARCELINO ONOFRE.xlsx', 'xlsx', 76328, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0303' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16636-S  MARCELINO ONOFRE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16637 HOSPITAL DE CELAYA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16637 HOSPITAL DE CELAYA.xlsx', 'xlsx', 9742043, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0304' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16637 HOSPITAL DE CELAYA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16638 HOSPITAL DE CELAYA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16638 HOSPITAL DE CELAYA.xlsx', 'xlsx', 9741504, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0305' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16638 HOSPITAL DE CELAYA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16639-S HOSPITAL INFANTIL DE NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16639-S HOSPITAL INFANTIL DE NVO LEON.xlsx', 'xlsx', 80640, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0306' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16639-S HOSPITAL INFANTIL DE NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16640-S CLINICA DEL IMSS STA BARBARA, SLW.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16640-S CLINICA DEL IMSS STA BARBARA, SLW.xlsx', 'xlsx', 76865, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0307' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16640-S CLINICA DEL IMSS STA BARBARA, SLW.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16641-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16641-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx', 'xlsx', 76182, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0308' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16641-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16642 ING PEDRO LUIS OLAN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16642 ING PEDRO LUIS OLAN.xlsx', 'xlsx', 9738928, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0309' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16642 ING PEDRO LUIS OLAN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16643-S ING PEDRO LUIS OLAN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16643-S ING PEDRO LUIS OLAN.xlsx', 'xlsx', 76206, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0310' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16643-S ING PEDRO LUIS OLAN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16644  HOSPITAL KUMATE CHUMATO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16644  HOSPITAL KUMATE CHUMATO.xlsx', 'xlsx', 9618491, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0311' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16644  HOSPITAL KUMATE CHUMATO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16644-S HOSPITAL EN METEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16644-S HOSPITAL EN METEPEC.xlsx', 'xlsx', 76161, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0312' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16644-S HOSPITAL EN METEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16645-S HOSPITAL EN ACAPULCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16645-S HOSPITAL EN ACAPULCO.xlsx', 'xlsx', 76270, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0313' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16645-S HOSPITAL EN ACAPULCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16646 HOSPITAL EN METEPEC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16646 HOSPITAL EN METEPEC.xlsx', 'xlsx', 9620883, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0314' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16646 HOSPITAL EN METEPEC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16647-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16647-S ILINSA.XLSX', 'xlsx', 75128, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0315' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16647-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16648-S INTERCENTURION.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16648-S INTERCENTURION.xlsx', 'xlsx', 72689, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0316' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16648-S INTERCENTURION.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16649 ISSSTE TAMPICO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16649 ISSSTE TAMPICO.xlsx', 'xlsx', 9617476, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0317' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16649 ISSSTE TAMPICO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16650 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16650 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx', 'xlsx', 9619799, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0318' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16650 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16651-S ALICIA OJEDA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16651-S ALICIA OJEDA.xlsx', 'xlsx', 198839, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0319' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16651-S ALICIA OJEDA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16652 ACABADOS LHOGAR.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16652 ACABADOS LHOGAR.xlsx', 'xlsx', 9617885, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0320' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16652 ACABADOS LHOGAR.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16653 SHOP ELLAZ CDMX ARCOS I.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16653 SHOP ELLAZ CDMX ARCOS I.xlsx', 'xlsx', 12233023, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0321' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16653 SHOP ELLAZ CDMX ARCOS I.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16654 HOSPITAL DE 20 CAMAS ZITACUARO, MIC.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16654 HOSPITAL DE 20 CAMAS ZITACUARO, MIC.xlsx', 'xlsx', 9621491, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0322' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16654 HOSPITAL DE 20 CAMAS ZITACUARO, MIC.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16655 HOSPITAL DEL NIÑO EN NL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16655 HOSPITAL DEL NIÑO EN NL.xlsx', 'xlsx', 9623829, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0323' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16655 HOSPITAL DEL NIÑO EN NL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16656-S HOSPITAL DEL NIÑO EN NL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16656-S HOSPITAL DEL NIÑO EN NL.xlsx', 'xlsx', 78866, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0324' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16656-S HOSPITAL DEL NIÑO EN NL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16657 HOSPITAL INFANTIL NVO LEON ZOCLOS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16657 HOSPITAL INFANTIL NVO LEON ZOCLOS.xlsx', 'xlsx', 38212223, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0325' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16657 HOSPITAL INFANTIL NVO LEON ZOCLOS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16657 HOSPITAL INFANTIL NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16657 HOSPITAL INFANTIL NVO LEON.xlsx', 'xlsx', 9626780, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0326' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16657 HOSPITAL INFANTIL NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16657 HOSPITAL INFANTIL NVO_LEON_ZOCLOS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16657 HOSPITAL INFANTIL NVO_LEON_ZOCLOS.xlsx', 'xlsx', 40811314, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0327' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16657 HOSPITAL INFANTIL NVO_LEON_ZOCLOS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16658 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 1.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16658 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 1.xlsx', 'xlsx', 43948394, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0328' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16658 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 1.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16658-S JONATHAN GONZALEZ.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16658-S JONATHAN GONZALEZ.xlsx', 'xlsx', 200721, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0329' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16658-S JONATHAN GONZALEZ.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16659 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 2.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16659 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 2.xlsx', 'xlsx', 46726652, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0330' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16659 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 2.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16660 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 3.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16660 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 3.xlsx', 'xlsx', 48502080, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0331' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16660 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 3.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16661 HOSPITAL INFANTIL NVO LEON GERFLOR.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16661 HOSPITAL INFANTIL NVO LEON GERFLOR.xlsx', 'xlsx', 9744595, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0332' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16661 HOSPITAL INFANTIL NVO LEON GERFLOR.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16662-S CONSTRUCTORA RECO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16662-S CONSTRUCTORA RECO.xlsx', 'xlsx', 81902, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0333' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16662-S CONSTRUCTORA RECO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16663 HOSPITAL DE CELAYA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16663 HOSPITAL DE CELAYA.xlsx', 'xlsx', 9740638, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0334' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16663 HOSPITAL DE CELAYA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16664 HOSPITAL INFANTIL NVO LEON MUROS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16664 HOSPITAL INFANTIL NVO LEON MUROS.xlsx', 'xlsx', 16658342, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0335' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16664 HOSPITAL INFANTIL NVO LEON MUROS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16665-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16665-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx', 'xlsx', 78125, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0336' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16665-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16666 HOSPITAL EN CDMX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16666 HOSPITAL EN CDMX.xlsx', 'xlsx', 9746811, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0337' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16666 HOSPITAL EN CDMX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16667-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16667-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX', 'xlsx', 77033, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0338' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16667-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16668-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16668-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX', 'xlsx', 77041, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0339' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16668-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16669 REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16669 REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.xlsx', 'xlsx', 9620687, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0340' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16669 REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16671-S IMSS RIO COLORADO, SONORA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16671-S IMSS RIO COLORADO, SONORA.XLSX', 'xlsx', 77625, 1, NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0341' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16671-S IMSS RIO COLORADO, SONORA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16672-S IMSS RIO COLORADO, SONORA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16672-S IMSS RIO COLORADO, SONORA.XLSX', 'xlsx', 77614, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0342' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16672-S IMSS RIO COLORADO, SONORA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16673  MORA ASESORES PARA LA CONSTRUCCION BASILICA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16673  MORA ASESORES PARA LA CONSTRUCCION BASILICA.xlsx', 'xlsx', 9743576, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0343' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16673  MORA ASESORES PARA LA CONSTRUCCION BASILICA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16674 IMSS RIO COLORADO, SONORA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16674 IMSS RIO COLORADO, SONORA.xlsx', 'xlsx', 9743893, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0344' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16674 IMSS RIO COLORADO, SONORA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16675 IMSS RIO COLORADO, SONORA CHISA PLAST.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16675 IMSS RIO COLORADO, SONORA CHISA PLAST.xlsx', 'xlsx', 9743995, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0345' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16675 IMSS RIO COLORADO, SONORA CHISA PLAST.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16676 HOSPITAL NAVAL DE ACAPULCO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16676 HOSPITAL NAVAL DE ACAPULCO.xlsx', 'xlsx', 9779762, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0346' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16676 HOSPITAL NAVAL DE ACAPULCO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16677-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16677-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'xlsx', 77641, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0347' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16677-S HOSPITAL NAVAL DE ACAPULCO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16678 EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16678 EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx', 'xlsx', 9738366, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0348' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16678 EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16678 HOSPITAL DE CELAYA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16678 HOSPITAL DE CELAYA.xlsx', 'xlsx', 9742781, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0349' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16678 HOSPITAL DE CELAYA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16679 HOSPITAL GENERAL DE CHETUMAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16679 HOSPITAL GENERAL DE CHETUMAL.xlsx', 'xlsx', 9621026, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0350' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16679 HOSPITAL GENERAL DE CHETUMAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16680-S HOSPITAL GENERAL DE CHETUMAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16680-S HOSPITAL GENERAL DE CHETUMAL.xlsx', 'xlsx', 77210, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0351' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16680-S HOSPITAL GENERAL DE CHETUMAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16681 CESSA-AGS.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16681 CESSA-AGS.xlsx', 'xlsx', 9620485, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0352' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16681 CESSA-AGS.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16682-S CESSA-AGS.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16682-S CESSA-AGS.XLSX', 'xlsx', 77108, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0353' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16682-S CESSA-AGS.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16683-S ILINSA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16683-S ILINSA.XLSX', 'xlsx', 76854, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0354' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16683-S ILINSA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16684 HOSPITAL GENERAL DE CHETUMAL GP.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16684 HOSPITAL GENERAL DE CHETUMAL GP.xlsx', 'xlsx', 9621952, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0355' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16684 HOSPITAL GENERAL DE CHETUMAL GP.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16685 HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16685 HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx', 'xlsx', 9621572, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0356' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16685 HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16686-S HOSPITAL NAVAL DE CHETUMAL.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16686-S HOSPITAL NAVAL DE CHETUMAL.XLSX', 'xlsx', 77026, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0357' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16686-S HOSPITAL NAVAL DE CHETUMAL.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16687 HOSPITAL NAVAL DE CHETUMAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16687 HOSPITAL NAVAL DE CHETUMAL.xlsx', 'xlsx', 9621063, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0358' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16687 HOSPITAL NAVAL DE CHETUMAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16688-S DECORACLEAN.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16688-S DECORACLEAN.XLSX', 'xlsx', 76779, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0359' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16688-S DECORACLEAN.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16689-S HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16689-S HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx', 'xlsx', 77453, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0360' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16689-S HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16690 HOSPITAL GENERAL DE CHETUMAL GP (2).xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16690 HOSPITAL GENERAL DE CHETUMAL GP (2).xlsx', 'xlsx', 9621993, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0361' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16690 HOSPITAL GENERAL DE CHETUMAL GP (2).xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16690 HOSPITAL GENERAL DE CHETUMAL GP.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16690 HOSPITAL GENERAL DE CHETUMAL GP.xlsx', 'xlsx', 9621100, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0362' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16690 HOSPITAL GENERAL DE CHETUMAL GP.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16691 HOSPITAL INFANTIL NVO LEON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16691 HOSPITAL INFANTIL NVO LEON.xlsx', 'xlsx', 9621110, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0363' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16691 HOSPITAL INFANTIL NVO LEON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16691-2026-VGG CESSA CUERARAMO, GTO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16691-2026-VGG CESSA CUERARAMO, GTO.xlsx', 'xlsx', 9621477, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0364' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16691-2026-VGG CESSA CUERARAMO, GTO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16692 CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16692 CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO..xlsx', 'xlsx', 9739183, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0365' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16692 CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16692 HOSPITAL DONATO ALARCON.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16692 HOSPITAL DONATO ALARCON.xlsx', 'xlsx', 76972, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0366' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16692 HOSPITAL DONATO ALARCON.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16693 SHOP ELLAZ ATIZAPAN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16693 SHOP ELLAZ ATIZAPAN.xlsx', 'xlsx', 9619606, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0367' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16693 SHOP ELLAZ ATIZAPAN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16693-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16693-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx', 'xlsx', 9618724, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0368' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16693-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16694-S DECORACLEAN.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16694-S DECORACLEAN.XLSX', 'xlsx', 76776, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0369' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16694-S DECORACLEAN.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16694-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16694-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'xlsx', 77000, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0370' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16694-S HOSPITAL NAVAL DE ACAPULCO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16694-S-VG CLINICA DEL IMSS STA BARBARA, S.L.W.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16694-S-VG CLINICA DEL IMSS STA BARBARA, S.L.W.xlsx', 'xlsx', 78499, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0371' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16694-S-VG CLINICA DEL IMSS STA BARBARA, S.L.W.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16694-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16694-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX', 'xlsx', 77008, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0372' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16694-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA (2).xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA (2).xlsx', 'xlsx', 76762, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0373' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA (2).xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA.xlsx', 'xlsx', 9619072, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0374' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16695-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16695-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX', 'xlsx', 76859, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0375' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16695-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16696-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16696-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'xlsx', 76714, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0376' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16696-S HOSPITAL NAVAL DE ACAPULCO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16696-S ING. ABRAHAM GALLARDO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16696-S ING. ABRAHAM GALLARDO.XLSX', 'xlsx', 76846, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0377' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16696-S ING. ABRAHAM GALLARDO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16697-S BE GRAND CONTADERO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16697-S BE GRAND CONTADERO.XLSX', 'xlsx', 76818, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0378' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16697-S BE GRAND CONTADERO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16697-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16697-S HOSPITAL NAVAL DE ACAPULCO.XLSX', 'xlsx', 77609, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0379' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16697-S HOSPITAL NAVAL DE ACAPULCO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16698-S-VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16698-S-VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO.XLSX', 'xlsx', 126328, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0380' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16698-S-VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16699 -VGG HOSPITAL MAC IRAPUATO, GTO..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16699 -VGG HOSPITAL MAC IRAPUATO, GTO..xlsx', 'xlsx', 9695279, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0381' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16699 -VGG HOSPITAL MAC IRAPUATO, GTO..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx', 'xlsx', 9743779, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0382' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16700 CONDOMINIO PITAGORAS 308 CDMX.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16700 CONDOMINIO PITAGORAS 308 CDMX.XLSX', 'xlsx', 126842, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0383' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16700 CONDOMINIO PITAGORAS 308 CDMX.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16700 ISSSTE PARACHO, MICHOACAN.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16700 ISSSTE PARACHO, MICHOACAN.xlsx', 'xlsx', 9621961, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0384' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16700 ISSSTE PARACHO, MICHOACAN.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx', 'xlsx', 9738424, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0385' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16702 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16702 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx', 'xlsx', 9621137, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0386' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16702 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16703-S-VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16703-S-VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO.XLSX', 'xlsx', 126278, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0387' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16703-S-VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16704 HOSPITAL MAC IRAPUATO, GTO..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16704 HOSPITAL MAC IRAPUATO, GTO..xlsx', 'xlsx', 9694067, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0388' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16704 HOSPITAL MAC IRAPUATO, GTO..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16705-ISSSTE PARACHO, MICHOACAN-VG.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16705-ISSSTE PARACHO, MICHOACAN-VG.xlsx', 'xlsx', 9623759, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0389' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16705-ISSSTE PARACHO, MICHOACAN-VG.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16706-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16706-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX', 'xlsx', 127604, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0390' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16706-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16706-S ISSSTE PARACHO MICH..XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16706-S ISSSTE PARACHO MICH..XLSX', 'xlsx', 126317, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0391' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16706-S ISSSTE PARACHO MICH..XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16707-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16707-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX', 'xlsx', 126325, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0392' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16707-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16708-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16708-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX', 'xlsx', 126102, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0393' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16708-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL VG.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL VG.xlsx', 'xlsx', 9622414, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0394' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL VG.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx', 'xlsx', 9622417, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0395' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16710-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16710-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx', 'xlsx', 9618538, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0396' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16710-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16711 HOSPITAL DE CANCUN. Q.R. VG.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16711 HOSPITAL DE CANCUN. Q.R. VG.xlsx', 'xlsx', 9620802, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0397' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16711 HOSPITAL DE CANCUN. Q.R. VG.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16712 HOSPITAL  REGIONAL NAVAL DE MANZANILLO.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16712 HOSPITAL  REGIONAL NAVAL DE MANZANILLO.xlsx', 'xlsx', 9624658, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0398' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16712 HOSPITAL  REGIONAL NAVAL DE MANZANILLO.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16713- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16713- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX', 'xlsx', 91116, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0399' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16713- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16714-S-VG PRADO NORTE  440.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16714-S-VG PRADO NORTE  440.XLSX', 'xlsx', 126155, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0400' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16714-S-VG PRADO NORTE  440.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16715 CLINICA CONDESA CDMX.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16715 CLINICA CONDESA CDMX.xlsx', 'xlsx', 9624861, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0401' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16715 CLINICA CONDESA CDMX.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16716 HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16716 HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG.xlsx', 'xlsx', 9618711, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0402' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16716 HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16717 PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16717 PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM.xlsx', 'xlsx', 9620807, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0403' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16717 PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM.xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16718-S-VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX..XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16718-S-VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX..XLSX', 'xlsx', 126238, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0404' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16718-S-VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX..XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16719 MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO..xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/16719 MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO..xlsx', 'xlsx', 9617501, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0405' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16719 MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO..xlsx');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16720-S.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16720-S.XLSX', 'xlsx', 126380, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0406' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16720-S.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16721-S SHOP ELLAZ GRAN PLAZA GUADALAJARA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16721-S SHOP ELLAZ GRAN PLAZA GUADALAJARA.XLSX', 'xlsx', 126365, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0407' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16721-S SHOP ELLAZ GRAN PLAZA GUADALAJARA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16722-S SHOP ELLAZ MERIDA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16722-S SHOP ELLAZ MERIDA.XLSX', 'xlsx', 126387, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0408' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16722-S SHOP ELLAZ MERIDA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16723-S SHOP ELLAZ ANTARA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16723-S SHOP ELLAZ ANTARA.XLSX', 'xlsx', 126407, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0409' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16723-S SHOP ELLAZ ANTARA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16724-S SHOP ELLAZ GALERIAS MONTERREY.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16724-S SHOP ELLAZ GALERIAS MONTERREY.XLSX', 'xlsx', 126370, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0410' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16724-S SHOP ELLAZ GALERIAS MONTERREY.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, '16725- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/16725- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX', 'xlsx', 91106, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0411' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/16725- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, 'FORMATO_COTIZACIONES_2026_HAPA - copia.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/FORMATO_COTIZACIONES_2026_HAPA - copia.XLSX', 'xlsx', 126159, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0412' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/FORMATO_COTIZACIONES_2026_HAPA - copia.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, 'FORMATO_COTIZACIONES_2026_HAPA.XLSX', 'doc/entrenamiento_5/COTIZACIONES 2026/FORMATO_COTIZACIONES_2026_HAPA.XLSX', 'xlsx', 126159, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0413' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/FORMATO_COTIZACIONES_2026_HAPA.XLSX');
INSERT INTO expediente_archivos (origen_tipo,origen_id,carpeta_id,nombre,ruta,extension,tamano,es_enlace,subido_por,fecha)
SELECT 'cotizacion', c.id, NULL, 'PRUEBA.xlsx', 'doc/entrenamiento_5/COTIZACIONES 2026/PRUEBA.xlsx', 'xlsx', 9619619, 1, NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0414' AND NOT EXISTS (SELECT 1 FROM expediente_archivos x WHERE x.origen_tipo='cotizacion' AND x.ruta='doc/entrenamiento_5/COTIZACIONES 2026/PRUEBA.xlsx');
INSERT INTO seguimiento_fases (origen_tipo,origen_id,orden,nombre,porcentaje,estatus,fecha_compromiso,notas)
SELECT 'cotizacion', c.id, 0, 'Planta baja', 40.00, 'En proceso', '2026-10-31', NULL FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM seguimiento_fases s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.nombre='Planta baja');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «13589 HOSP IMSS No1 OAXCA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0017' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «13589 HOSP IMSS No1 OAXCA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16351 HOSPITAL GENERAL TEPIC, NAY 1.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0018' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16351 HOSPITAL GENERAL TEPIC, NAY 1.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16356-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0019' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16356-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16357-S Sumato Supervisión y Mantenimiento.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0020' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16357-S Sumato Supervisión y Mantenimiento.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16358 Sumato Supervisión y Mantenimiento.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0021' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16358 Sumato Supervisión y Mantenimiento.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16359-S PEMCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0022' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16359-S PEMCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16360-S ENSENADA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0023' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16360-S ENSENADA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16361-S HOSPITAL EL QUEMADO, ACA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0024' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16361-S HOSPITAL EL QUEMADO, ACA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16363-S ISSSTE ZONA BOCA DEL RIO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0025' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16363-S ISSSTE ZONA BOCA DEL RIO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16364-S C H 30 EN HUEJUTLA DE REYES, HIDALGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0026' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16364-S C H 30 EN HUEJUTLA DE REYES, HIDALGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16365 Sumato Supervisión y Mantenimiento.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0027' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16365 Sumato Supervisión y Mantenimiento.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16366-S HGR 23 ENSENADA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0028' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16366-S HGR 23 ENSENADA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16367 HOSPITAL ISSSTE EMILIANO ZAPATA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0029' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16367 HOSPITAL ISSSTE EMILIANO ZAPATA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16368 OOAD CHIAPAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0030' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16368 OOAD CHIAPAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16369-S OOAD CHIAPAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0031' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16369-S OOAD CHIAPAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16371-S LUZ ESQUIVEL.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0032' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16371-S LUZ ESQUIVEL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16372-S ARQ. IVAN OJEDA LOPEZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0033' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16372-S ARQ. IVAN OJEDA LOPEZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16374 HOSPITAL HEMODIALISIS AHEOS, TUXTLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0034' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16374 HOSPITAL HEMODIALISIS AHEOS, TUXTLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16375 SECRETARIA DE MARINA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0035' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16375 SECRETARIA DE MARINA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16376 SECRETARIA DE MARINA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0036' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16376 SECRETARIA DE MARINA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16377-S HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0037' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16377-S HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16378 SECRETARIA DE MARINA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0038' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16378 SECRETARIA DE MARINA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16379 SECRETARIA DE MARINA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0039' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16379 SECRETARIA DE MARINA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16380-S OOAD CHIAPAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0040' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16380-S OOAD CHIAPAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16381 HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0041' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16381 HOSPITAL IMSS BIENESTAR DONATO G ALARCON, ACA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16382-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0042' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16382-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16383 ASCH.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0043' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16383 ASCH.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16384 ARQ. IVONE TOBILLA AZCANIO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0044' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16384 ARQ. IVONE TOBILLA AZCANIO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16385 SHOP ELLAZ CDMX BOSQUES.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0045' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16385 SHOP ELLAZ CDMX BOSQUES.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16387 HOSPITAL GENERAL TEPIC, NAY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0046' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16387 HOSPITAL GENERAL TEPIC, NAY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16388-S DESARROLLO DEL SURESTE 25.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0047' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16388-S DESARROLLO DEL SURESTE 25.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16389 ARTURO MONTER ALGARIN.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0048' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16389 ARTURO MONTER ALGARIN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16389 HOSP IMSS No1 OAXACA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0049' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16389 HOSP IMSS No1 OAXACA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16390-S RAMSICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0050' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16390-S RAMSICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16391 YALA SA DE CV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0051' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16391 YALA SA DE CV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16392 YALA SA DE CV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0052' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16392 YALA SA DE CV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16393 SR. MAURICIO GUEVARA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0053' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16393 SR. MAURICIO GUEVARA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16394 GRUPO TRITON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0054' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16394 GRUPO TRITON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16396 SERVICIOS DE SALUD DE DURANGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0055' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16396 SERVICIOS DE SALUD DE DURANGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16397-S SERVICIOS DE SALUD DE DURANGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0056' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16397-S SERVICIOS DE SALUD DE DURANGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16398 IMSS 5 DE MAYO TUXTLA GUTIERREZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0057' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16398 IMSS 5 DE MAYO TUXTLA GUTIERREZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16398 IMSS 5 DE MAYO TUXTLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0058' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16398 IMSS 5 DE MAYO TUXTLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16399-S SERVICIOS INTEGRALES LACANJA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0059' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16399-S SERVICIOS INTEGRALES LACANJA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16400-S IMSS ZUMPANGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0060' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16400-S IMSS ZUMPANGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16401 IMSS ZUNPANGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0061' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16401 IMSS ZUNPANGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16402-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0062' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16402-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16403 HOSPITAL NAVAL DE ACAPULCO, GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0063' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16403 HOSPITAL NAVAL DE ACAPULCO, GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16404-S HOSPITAL NAVAL DE ACAPULCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0064' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16404-S HOSPITAL NAVAL DE ACAPULCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16405 HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0065' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16405 HOSPITAL DEL BIENESTAR DONATO GUERRA, ACA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16407 KING OASIS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0066' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16407 KING OASIS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16408-S HOSPITAL DR. RAYMUNDO ABARCA ALARCON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0067' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16408-S HOSPITAL DR. RAYMUNDO ABARCA ALARCON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16409-S REMODELACION IMSS PALMAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0068' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16409-S REMODELACION IMSS PALMAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16410 ARTURO MONTER ALGARIN.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0069' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16410 ARTURO MONTER ALGARIN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16411 HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0070' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16411 HOSPITAL DEL IMSS 5 DE MAYO TUXTLA GUTIÉRREZ, CHIAPAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16412-S HOSPITAL DEL IMSS TEPIC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0071' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16412-S HOSPITAL DEL IMSS TEPIC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16413 HOSPITAL GENERAL DE ACAPULCO, GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0072' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16413 HOSPITAL GENERAL DE ACAPULCO, GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16414-S HOSPITAL REGIONAL DE TOLUCA, ISSEMYM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0073' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16414-S HOSPITAL REGIONAL DE TOLUCA, ISSEMYM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16415-S RAMSICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0074' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16415-S RAMSICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16417-S IMSS RIO COLORADO, SON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0075' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16417-S IMSS RIO COLORADO, SON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16418 SECRETARIA DE MARINA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0076' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16418 SECRETARIA DE MARINA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16419 HOSPITAL GENERAL XOCO, CDMX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0077' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16419 HOSPITAL GENERAL XOCO, CDMX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16420-S GUMESA IMSS HUIXTLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0078' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16420-S GUMESA IMSS HUIXTLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16421-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0079' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16421-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16422 HOSPITAL EN MONTERREY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0080' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16422 HOSPITAL EN MONTERREY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16423-S HOSPITAL DE TLAJOMULCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0081' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16423-S HOSPITAL DE TLAJOMULCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16424-S HOSPITAL SANTA ROSA LIMA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0082' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16424-S HOSPITAL SANTA ROSA LIMA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16425-S OSCAR ALVAREZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0083' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16425-S OSCAR ALVAREZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16426-S HOSPITAL SANTA ROSA LIMA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0084' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16426-S HOSPITAL SANTA ROSA LIMA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16427 CLINICA DEL IMSS MONTERREY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0085' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16427 CLINICA DEL IMSS MONTERREY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16428 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0086' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16428 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16429-S Sumato Supervisión y Mantenimiento.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0087' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16429-S Sumato Supervisión y Mantenimiento.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16430 HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0088' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16430 HOSP. DE ALTA ESPECIALIDAD GRAL. IGNACIO ZARAGOZA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16433 HOSPITAL NAVAL DE DOS BOCAS, VER.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0089' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16433 HOSPITAL NAVAL DE DOS BOCAS, VER.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16434 HOSPITAL GENERAL DE TEMIXCO-1.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0090' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16434 HOSPITAL GENERAL DE TEMIXCO-1.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16434 HOSPITAL GENERAL DE TEMIXCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0091' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16434 HOSPITAL GENERAL DE TEMIXCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16434-1 HOSPITAL GENERAL DE TEMIXCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0092' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16434-1 HOSPITAL GENERAL DE TEMIXCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16434-2 HOSPITAL GENERAL DE TEMIXCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0093' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16434-2 HOSPITAL GENERAL DE TEMIXCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16435 HOSPITAL NAVAL DE ACAPULCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0094' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16435 HOSPITAL NAVAL DE ACAPULCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16436 HOSPITAL NAVAL DE ACAPULCO 1.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0095' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16436 HOSPITAL NAVAL DE ACAPULCO 1.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16437-S RAMSICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0096' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16437-S RAMSICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16438 SHOP ELLAZ TOWN SQUARE METEPEC.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0097' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16438 SHOP ELLAZ TOWN SQUARE METEPEC.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16439-S MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0098' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16439-S MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16440  MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0099' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16440  MANTENIMIENTO MENOR AL INMUEBLE DEL HOSPITAL COMUNITARIO SANTA CRUZ DE JUVENTINO ROSAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16442-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0100' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16442-S ARQ. RODOLFO CASTILLO HERNANDEZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16443-S P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR).XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0101' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16443-S P CONSTRUCCIÓN SUSTENTABLE DEL SUR (PROSUR).XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16444-S UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0102' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16444-S UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16445 GRUPO TRITON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0103' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16445 GRUPO TRITON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16446-S GRUPO MPV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0104' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16446-S GRUPO MPV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16447  GRUPO MPV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0105' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16447  GRUPO MPV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16448 SERVICIOS DE MANTENIMIENTO ORGALIM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0106' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16448 SERVICIOS DE MANTENIMIENTO ORGALIM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16450-S COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0107' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16450-S COMPANY MANTENIMIENTO Y SERVICIOS GENERALES MC2.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16451-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0108' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16451-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16452-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0109' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16452-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16453-S HOSPITAL  20 DE NOVIEMBRE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0110' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16453-S HOSPITAL  20 DE NOVIEMBRE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16454 20 DE NOVIEMBRE, ASCH.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0111' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16454 20 DE NOVIEMBRE, ASCH.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16455-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0112' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16455-S HOSPITAL COMUNITARIO MANUEL DOBLADO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16456-S UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0113' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16456-S UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16457 UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0114' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16457 UMF2 ISSSTE HECELCHAKAN, CAMP.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16458-S HOSPITAL EN AGUASCALIENTES.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0115' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16458-S HOSPITAL EN AGUASCALIENTES.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16459-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0116' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16459-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16460-S SHOP ELLAZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0117' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16460-S SHOP ELLAZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16461-S SHOP ELLAZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0118' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16461-S SHOP ELLAZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16462 UMF 15 DE MONTERREY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0119' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16462 UMF 15 DE MONTERREY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16463-COTIZACION.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0120' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16463-COTIZACION.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16464 ADRIANA AGUIRRE R..xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0121' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16464 ADRIANA AGUIRRE R..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16464 SICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0122' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16464 SICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16465-S RAMSICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0123' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16465-S RAMSICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16467 CONSTRUCCIONES HERSO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0124' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16467 CONSTRUCCIONES HERSO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16468-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0125' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16468-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16469 SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0126' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16469 SECRETARIA DE OBRAS PUBLICAS DE AGUASCALIENTES.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16470 ING. RAUL ZEFERINO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0127' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16470 ING. RAUL ZEFERINO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16471 SHOP ELLAZ GALERIAS ATIZAPAN.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0128' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16471 SHOP ELLAZ GALERIAS ATIZAPAN.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16472 IMSS 5 DE MAYO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0129' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16472 IMSS 5 DE MAYO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16473  IMSS CHETUMAL.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0130' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16473  IMSS CHETUMAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16474-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0131' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16474-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16475 IMSS 5 DE MAYO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0132' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16475 IMSS 5 DE MAYO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16476-S PEMCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0133' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16476-S PEMCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16477 IMSS 5 DE MAYO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0134' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16477 IMSS 5 DE MAYO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16478-S GUMESA IMSS HUIXTLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0135' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16478-S GUMESA IMSS HUIXTLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16479 PRODEI.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0136' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16479 PRODEI.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16480-S HGR 216 CAMAS ENSENADA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0137' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16480-S HGR 216 CAMAS ENSENADA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16481-S Sumato Supervisión y Mantenimiento.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0138' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16481-S Sumato Supervisión y Mantenimiento.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16483 IMSS BIENESTAR CHIAPAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0139' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16483 IMSS BIENESTAR CHIAPAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16484-S ULSA CONSORCIO URBANISTA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0140' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16484-S ULSA CONSORCIO URBANISTA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16486 CLINICA DEL IMSS EN MONTERREY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0141' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16486 CLINICA DEL IMSS EN MONTERREY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16487 INCOP.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0142' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16487 INCOP.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16488-S RAMSICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0143' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16488-S RAMSICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16490-S ARQ. VICTORIA  PEREZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0144' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16490-S ARQ. VICTORIA  PEREZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16491-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0145' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16491-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16492 JAVIER CEJUDO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0146' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16492 JAVIER CEJUDO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16493-S DICOREMA ARQUITECTO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0147' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16493-S DICOREMA ARQUITECTO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16494-S CONSTRUCTORA RECO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0148' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16494-S CONSTRUCTORA RECO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16494-S OYM, S.A DE C.V.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0149' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16494-S OYM, S.A DE C.V.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16495-S HGZ 10 MANZANILLO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0150' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16495-S HGZ 10 MANZANILLO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16496-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0151' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16496-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16497-S ARQ. VICTORIA  PEREZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0152' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16497-S ARQ. VICTORIA  PEREZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16498 SHOP ELLAZ ZONA ROSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0153' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16498 SHOP ELLAZ ZONA ROSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16499 GRUPO TRITON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0154' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16499 GRUPO TRITON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16502 EDGAR CONSTANTINO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0155' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16502 EDGAR CONSTANTINO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16503 PRODEMEX.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0156' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16503 PRODEMEX.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16504-S PRODEMEX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0157' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16504-S PRODEMEX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16505 CONCRETO Y ESPACIO URBANO,SON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0158' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16505 CONCRETO Y ESPACIO URBANO,SON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16506-S HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0159' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16506-S HOSPITAL GENERAL DR. RAYMUNDO ABRACA ALARCON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16507 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0160' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16507 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16508-S IMSS COLIMA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0161' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16508-S IMSS COLIMA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16509  HOSPITAL 20 DE NOVIEMBRE GERFLOR.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0162' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16509  HOSPITAL 20 DE NOVIEMBRE GERFLOR.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16510  HOSPITAL 20 DE NOVIEMBRE POLYFLOR.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0163' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16510  HOSPITAL 20 DE NOVIEMBRE POLYFLOR.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16511-S RAMSICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0164' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16511-S RAMSICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16512-S DESARROLLO DEL SURESTE 25.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0165' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16512-S DESARROLLO DEL SURESTE 25.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16513 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0166' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16513 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16514-S PRODEMEX GUANAJUATO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0167' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16514-S PRODEMEX GUANAJUATO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16515-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0168' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16515-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16516 ARQ LAURA IVETTE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0169' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16516 ARQ LAURA IVETTE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16517 CECAN XALAPA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0170' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16517 CECAN XALAPA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16518 JALAPA, VER.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0171' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16518 JALAPA, VER.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16519 HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL).xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0172' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16519 HOSPITAL NAVAL DE MANZANILLO (ALTA INGENIERIA CIVIL).xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16520-S ING. ABRAHAM GALLARDO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0173' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16520-S ING. ABRAHAM GALLARDO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16521 20 DE NOVIEMBRE, ASCH.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0174' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16521 20 DE NOVIEMBRE, ASCH.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16522-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0175' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16522-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16523- ARQ. JESUS IGLESIAS CORNELIO.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0176' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16523- ARQ. JESUS IGLESIAS CORNELIO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16524 UAM AZCAPOTZALCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0177' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16524 UAM AZCAPOTZALCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16525 UAM AZCAPOTZALCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0178' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16525 UAM AZCAPOTZALCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16526  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0179' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16526  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16527 ARQ. IVONE TOBILLA AZCANIO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0180' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16527 ARQ. IVONE TOBILLA AZCANIO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16528-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0181' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16528-S MED DISEÑO CONSTRUCCION Y SUPERVISION.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16529 CLINICA 23 DEL IMSS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0182' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16529 CLINICA 23 DEL IMSS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16530-S CLINICA 23 DEL IMSS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0183' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16530-S CLINICA 23 DEL IMSS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16531 IMSS EN OCOZOCUAUTLA, CHIA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0184' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16531 IMSS EN OCOZOCUAUTLA, CHIA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16532 CINETECA NACIONAL CHAPULTEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0185' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16532 CINETECA NACIONAL CHAPULTEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16533 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0186' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16533 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16533-1 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0187' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16533-1 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16533-2 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0188' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16533-2 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16533-3 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0189' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16533-3 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16534 DESARROLLO DEL SURESTE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0190' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16534 DESARROLLO DEL SURESTE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16535-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0191' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16535-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16536-S DESARROLLO DEL SURESTE 25.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0192' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16536-S DESARROLLO DEL SURESTE 25.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16537 SICOM.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0193' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16537 SICOM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16538 CONSTRUCTORA EMACO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0194' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16538 CONSTRUCTORA EMACO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16539-S  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0195' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16539-S  INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16539-S -1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0196' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16539-S -1 INSTITUTO NACIONAL DE INVESTIGACIONES NUCLEARES.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16540-S MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0197' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16540-S MANTENIMIENTOS OVANDO RESIDENCIA PUERTO CANCUN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16541-S SHOP ELLAZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0198' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16541-S SHOP ELLAZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16542-S IMSS CHETUMAL DESC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0199' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16542-S IMSS CHETUMAL DESC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16542-S IMSS CHETUMAL.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0200' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16542-S IMSS CHETUMAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16543-S ANMED SA DE CV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0201' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16543-S ANMED SA DE CV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16545-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0202' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16545-S SHOP ELLAZ SAN AGUSTIN, MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16546 SHOP ELLAZ SAN AGUSTIN, MTY.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0203' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16546 SHOP ELLAZ SAN AGUSTIN, MTY.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16547-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0204' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16547-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA DESC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16548-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0205' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16548-S HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16549 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0206' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16549 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16550 HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0207' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16550 HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16551 HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0208' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16551 HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16552-S HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0209' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16552-S HOSPITAL ISSSTE CHILPANCINGO, GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16554-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0210' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16554-S  CONSTRUCTORA LA IMAGEN DE HOY S.A. DE C.V..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16555-S ING EDUARDO FLORES, PUEBLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0211' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16555-S ING EDUARDO FLORES, PUEBLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16556-S ING EDUARDO FLORES, PUEBLA DESC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0212' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16556-S ING EDUARDO FLORES, PUEBLA DESC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16556-S ING EDUARDO FLORES, PUEBLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0213' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16556-S ING EDUARDO FLORES, PUEBLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16557-S SHOP ELLAZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0214' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16557-S SHOP ELLAZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16558 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0215' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16558 HOSPITAL GENERAL DE PABELLON DE ARTEAGA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16559 CLINICA DEL IMSS MONTERREY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0216' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16559 CLINICA DEL IMSS MONTERREY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16559 HOSPITAL DEL IMSS MONTERREY 1.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0217' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16559 HOSPITAL DEL IMSS MONTERREY 1.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16560 HOSPITAL DEL IMSS MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0218' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16560 HOSPITAL DEL IMSS MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16561-S CLINICA DEL IMSS MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0219' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16561-S CLINICA DEL IMSS MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16563-S CLINICA EN GUASAVE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0220' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16563-S CLINICA EN GUASAVE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16564 UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0221' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16564 UNIVERSIDAD AUTONOMA DE LA CIUDAD DE MEXICO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16565 20 DE NOVIEMBRE, ASCH.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0222' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16565 20 DE NOVIEMBRE, ASCH.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16565 HOSPITAL IGNACIO ZARAGOZA, ASCH.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0223' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16565 HOSPITAL IGNACIO ZARAGOZA, ASCH.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16566 2G INGENIERIA Y ARQUITECTURA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0224' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16566 2G INGENIERIA Y ARQUITECTURA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16567-S DIGITAL GG MEXICO.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0225' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16567-S DIGITAL GG MEXICO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16568-S ISSSTE VASCO DE QUIROGA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0226' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16568-S ISSSTE VASCO DE QUIROGA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16569 ISSSTE VASCO DE QUIROGA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0227' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16569 ISSSTE VASCO DE QUIROGA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16569-1 ISSSTE VASCO DE QUIROGA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0228' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16569-1 ISSSTE VASCO DE QUIROGA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16570-S PRODEMEX TUXTLA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0229' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16570-S PRODEMEX TUXTLA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16571 - S HOSP CRUZ AZUL, LAGUNAS OAX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0230' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16571 - S HOSP CRUZ AZUL, LAGUNAS OAX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16571 HOSP CRUZ AZUL, LAGUNAS OAX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0231' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16571 HOSP CRUZ AZUL, LAGUNAS OAX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16572-S SHOP ELLAZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0232' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16572-S SHOP ELLAZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16573 BODEGA , QRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0233' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16573 BODEGA , QRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16574-S JAIME BAUTISTA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0234' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16574-S JAIME BAUTISTA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16575 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0235' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16575 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16576 CLINICA DEL IMSS MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0236' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16576 CLINICA DEL IMSS MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16577-S CLINICA DEL IMSS MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0237' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16577-S CLINICA DEL IMSS MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16578 REYNA MENDOZA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0238' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16578 REYNA MENDOZA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16579 HOSPITAL DE SANTIAGO PAPASQUIARO, DGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0239' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16579 HOSPITAL DE SANTIAGO PAPASQUIARO, DGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16580 CLINICA DEL IMSS 15 MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0240' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16580 CLINICA DEL IMSS 15 MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16581-S CLINICA DEL IMSS 15 MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0241' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16581-S CLINICA DEL IMSS 15 MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16582 HOSPITAL DE SANTIAGO PAPASQUIARO,DGO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0242' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16582 HOSPITAL DE SANTIAGO PAPASQUIARO,DGO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16583 CLINICA DEL IMSS 15 DE MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0243' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16583 CLINICA DEL IMSS 15 DE MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16584-S CLINICA DEL IMSS 15 DE MTY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0244' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16584-S CLINICA DEL IMSS 15 DE MTY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16585 RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0245' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16585 RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16586-S RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0246' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16586-S RONCES ZAGAL ARQUITECTOS & ASOCIADOS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16587 PARK RESERVE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0247' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16587 PARK RESERVE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16588-S CASA HABITACION TEXCOCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0248' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16588-S CASA HABITACION TEXCOCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16589 UAM UNIDAD XOCHIMILCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0249' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16589 UAM UNIDAD XOCHIMILCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16589 UAM XOCHIMILCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0250' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16589 UAM XOCHIMILCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16590 HOSPITAL IMSS DE MORELIA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0251' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16590 HOSPITAL IMSS DE MORELIA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16591 HOSPITAL EN HERMOSILLO, SON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0252' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16591 HOSPITAL EN HERMOSILLO, SON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16592-S HOSPITAL EN HERMOSILLO, SON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0253' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16592-S HOSPITAL EN HERMOSILLO, SON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16593 PROMOCAT.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0254' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16593 PROMOCAT.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16595 HOSPITAL DE CELAYA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0255' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16595 HOSPITAL DE CELAYA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16597-S HOSPITAL SAN LUIS RIO COLORADO,SON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0256' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16597-S HOSPITAL SAN LUIS RIO COLORADO,SON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16598 INSTITUTO NACIONAL DE NEUROLOGIA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0257' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16598 INSTITUTO NACIONAL DE NEUROLOGIA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16599 - S 1 FARMACIA  CRUZ AZUL, TULA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0258' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16599 - S 1 FARMACIA  CRUZ AZUL, TULA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16599 - S FARMACIA  CRUZ AZUL, TULA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0259' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16599 - S FARMACIA  CRUZ AZUL, TULA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16600 HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0260' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16600 HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16601 HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0261' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16601 HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16602-S HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0262' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16602-S HOSPITAL 90 CAMAS DR GONZALO CASTAÑEDA EN TLALTELOCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16603 HOSPITAL COMUNITARIO ROMITA, GTO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0263' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16603 HOSPITAL COMUNITARIO ROMITA, GTO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16604-S  POLY GOON ESTUDIO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0264' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16604-S  POLY GOON ESTUDIO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16605 HOSPITAL EN CHILPANCINGO, GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0265' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16605 HOSPITAL EN CHILPANCINGO, GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16606-S HOSPITAL EN CHILPANCINGO, GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0266' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16606-S HOSPITAL EN CHILPANCINGO, GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16607 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0267' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16607 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16608 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0268' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16608 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16608-1 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0269' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16608-1 HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16609-S HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0270' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16609-S HOSPITAL DEL ISSSTE TEHUANTEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16610-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0271' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16610-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16611 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0272' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16611 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16612 PREFIMAX SA DE CV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0273' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16612 PREFIMAX SA DE CV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16613-S PREFIMAX SA DE CV.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0274' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16613-S PREFIMAX SA DE CV.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16614 UMF 15 DE MONTERREY.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0275' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16614 UMF 15 DE MONTERREY.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16615 HOSPITAL INFANTIL NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0276' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16615 HOSPITAL INFANTIL NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16616 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0277' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16616 HOSPITAL DE SANTIAGO PAPASQUIARO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16617 HELIPUERTO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0278' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16617 HELIPUERTO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16618-S  MAUVAN.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0279' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16618-S  MAUVAN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16619-1 CUAUTITLAN IZCALLI.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0280' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16619-1 CUAUTITLAN IZCALLI.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16620-S CLINICA ESTETICA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0281' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16620-S CLINICA ESTETICA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16621-S GAE SOLUCIONES CONSTRUCTIVAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0282' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16621-S GAE SOLUCIONES CONSTRUCTIVAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16622  GAE SOLUCIONES CONSTRUCTIVAS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0283' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16622  GAE SOLUCIONES CONSTRUCTIVAS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16623 CLINICA ESTETICA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0284' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16623 CLINICA ESTETICA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16624-S SINDICATO NACIONAL DE TRABAJADORES DEL IMSS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0285' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16624-S SINDICATO NACIONAL DE TRABAJADORES DEL IMSS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16625 HOSPITAL 20 DE NOVIEMBRE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0286' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16625 HOSPITAL 20 DE NOVIEMBRE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16625-1 HOSPITAL 20 DE NOVIEMBRE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0287' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16625-1 HOSPITAL 20 DE NOVIEMBRE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16628 HOSPITAL INFANTIL NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0288' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16628 HOSPITAL INFANTIL NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16628-1 HOSPITAL INFANTIL NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0289' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16628-1 HOSPITAL INFANTIL NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16629 HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0290' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16629 HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16629-1 HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0291' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16629-1 HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16629-2 HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0292' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16629-2 HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16629-C HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0293' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16629-C HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16630-S COGUTSA MATERIALES.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0294' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16630-S COGUTSA MATERIALES.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16631-S CLINICA ESTETICA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0295' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16631-S CLINICA ESTETICA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16631-S HOSPITAL PABELLON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0296' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16631-S HOSPITAL PABELLON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16632 HOSPITAL PABELLON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0297' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16632 HOSPITAL PABELLON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16633 UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0298' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16633 UMF DE HUAUTLA DE JIMENEZ, OAX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16634-S ACABADOS LHOGAR.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0299' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16634-S ACABADOS LHOGAR.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16634-S HOSPITAL DE SAN LUIS RIO COLORDO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0300' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16634-S HOSPITAL DE SAN LUIS RIO COLORDO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16635 HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0301' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16635 HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16635-C HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0302' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16635-C HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16636-S  MARCELINO ONOFRE.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0303' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16636-S  MARCELINO ONOFRE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16637 HOSPITAL DE CELAYA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0304' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16637 HOSPITAL DE CELAYA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16638 HOSPITAL DE CELAYA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0305' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16638 HOSPITAL DE CELAYA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16639-S HOSPITAL INFANTIL DE NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0306' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16639-S HOSPITAL INFANTIL DE NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16640-S CLINICA DEL IMSS STA BARBARA, SLW.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0307' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16640-S CLINICA DEL IMSS STA BARBARA, SLW.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16641-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0308' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16641-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16642 ING PEDRO LUIS OLAN.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0309' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16642 ING PEDRO LUIS OLAN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16643-S ING PEDRO LUIS OLAN.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0310' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16643-S ING PEDRO LUIS OLAN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16644  HOSPITAL KUMATE CHUMATO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0311' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16644  HOSPITAL KUMATE CHUMATO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16644-S HOSPITAL EN METEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0312' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16644-S HOSPITAL EN METEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16645-S HOSPITAL EN ACAPULCO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0313' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16645-S HOSPITAL EN ACAPULCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16646 HOSPITAL EN METEPEC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0314' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16646 HOSPITAL EN METEPEC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16647-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0315' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16647-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16648-S INTERCENTURION.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0316' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16648-S INTERCENTURION.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16649 ISSSTE TAMPICO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0317' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16649 ISSSTE TAMPICO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16650 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0318' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16650 HOSPITAL RAYMUNDO ABARCA, CHILPANCINGO,GRO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16651-S ALICIA OJEDA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0319' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16651-S ALICIA OJEDA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16652 ACABADOS LHOGAR.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0320' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16652 ACABADOS LHOGAR.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16653 SHOP ELLAZ CDMX ARCOS I.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0321' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16653 SHOP ELLAZ CDMX ARCOS I.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16654 HOSPITAL DE 20 CAMAS ZITACUARO, MIC.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0322' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16654 HOSPITAL DE 20 CAMAS ZITACUARO, MIC.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16655 HOSPITAL DEL NIÑO EN NL.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0323' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16655 HOSPITAL DEL NIÑO EN NL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16656-S HOSPITAL DEL NIÑO EN NL.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0324' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16656-S HOSPITAL DEL NIÑO EN NL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16657 HOSPITAL INFANTIL NVO LEON ZOCLOS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0325' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16657 HOSPITAL INFANTIL NVO LEON ZOCLOS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16657 HOSPITAL INFANTIL NVO LEON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0326' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16657 HOSPITAL INFANTIL NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16657 HOSPITAL INFANTIL NVO_LEON_ZOCLOS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0327' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16657 HOSPITAL INFANTIL NVO_LEON_ZOCLOS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16658 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 1.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0328' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16658 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 1.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16658-S JONATHAN GONZALEZ.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0329' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16658-S JONATHAN GONZALEZ.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16659 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 2.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0330' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16659 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 2.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16660 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 3.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0331' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16660 HOSPITAL INFANTIL NVO_LEON_ZOCLOS 3.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16661 HOSPITAL INFANTIL NVO LEON GERFLOR.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0332' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16661 HOSPITAL INFANTIL NVO LEON GERFLOR.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16662-S CONSTRUCTORA RECO.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0333' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16662-S CONSTRUCTORA RECO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16663 HOSPITAL DE CELAYA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0334' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16663 HOSPITAL DE CELAYA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16664 HOSPITAL INFANTIL NVO LEON MUROS.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0335' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16664 HOSPITAL INFANTIL NVO LEON MUROS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16665-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0336' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16665-S CONSTRUCCIONES Y SERVICIOS GIMON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16666 HOSPITAL EN CDMX.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0337' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16666 HOSPITAL EN CDMX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16667-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0338' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16667-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16668-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0339' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16668-S REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16669 REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.xlsx»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0340' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16669 REINGENIERIA Y CONTROL APLICADOS A PROCESOS DE LA INFRAESTRUCTURA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16671-S IMSS RIO COLORADO, SONORA.XLSX»', NULL, '2026-10-21 03:26:55' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0341' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:55' AND s.mensaje='Documento «16671-S IMSS RIO COLORADO, SONORA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16672-S IMSS RIO COLORADO, SONORA.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0342' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16672-S IMSS RIO COLORADO, SONORA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16673  MORA ASESORES PARA LA CONSTRUCCION BASILICA.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0343' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16673  MORA ASESORES PARA LA CONSTRUCCION BASILICA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16674 IMSS RIO COLORADO, SONORA.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0344' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16674 IMSS RIO COLORADO, SONORA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16675 IMSS RIO COLORADO, SONORA CHISA PLAST.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0345' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16675 IMSS RIO COLORADO, SONORA CHISA PLAST.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16676 HOSPITAL NAVAL DE ACAPULCO.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0346' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16676 HOSPITAL NAVAL DE ACAPULCO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16677-S HOSPITAL NAVAL DE ACAPULCO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0347' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16677-S HOSPITAL NAVAL DE ACAPULCO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16678 EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0348' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16678 EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16678 HOSPITAL DE CELAYA.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0349' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16678 HOSPITAL DE CELAYA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16679 HOSPITAL GENERAL DE CHETUMAL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0350' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16679 HOSPITAL GENERAL DE CHETUMAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16680-S HOSPITAL GENERAL DE CHETUMAL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0351' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16680-S HOSPITAL GENERAL DE CHETUMAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16681 CESSA-AGS.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0352' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16681 CESSA-AGS.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16682-S CESSA-AGS.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0353' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16682-S CESSA-AGS.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16683-S ILINSA.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0354' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16683-S ILINSA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16684 HOSPITAL GENERAL DE CHETUMAL GP.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0355' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16684 HOSPITAL GENERAL DE CHETUMAL GP.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16685 HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0356' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16685 HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16686-S HOSPITAL NAVAL DE CHETUMAL.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0357' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16686-S HOSPITAL NAVAL DE CHETUMAL.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16687 HOSPITAL NAVAL DE CHETUMAL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0358' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16687 HOSPITAL NAVAL DE CHETUMAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16688-S DECORACLEAN.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0359' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16688-S DECORACLEAN.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16689-S HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0360' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16689-S HOSPITAL GENERAL DE CHETUMAL ASIRE.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16690 HOSPITAL GENERAL DE CHETUMAL GP (2).xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0361' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16690 HOSPITAL GENERAL DE CHETUMAL GP (2).xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16690 HOSPITAL GENERAL DE CHETUMAL GP.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0362' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16690 HOSPITAL GENERAL DE CHETUMAL GP.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16691 HOSPITAL INFANTIL NVO LEON.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0363' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16691 HOSPITAL INFANTIL NVO LEON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16691-2026-VGG CESSA CUERARAMO, GTO.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0364' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16691-2026-VGG CESSA CUERARAMO, GTO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16692 CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO..xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0365' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16692 CLINICA IMSS DE RENACIMIENTO, ACAPULCO, GRO..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16692 HOSPITAL DONATO ALARCON.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0366' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16692 HOSPITAL DONATO ALARCON.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16693 SHOP ELLAZ ATIZAPAN.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0367' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16693 SHOP ELLAZ ATIZAPAN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16693-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0368' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16693-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16694-S DECORACLEAN.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0369' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16694-S DECORACLEAN.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16694-S HOSPITAL NAVAL DE ACAPULCO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0370' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16694-S HOSPITAL NAVAL DE ACAPULCO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16694-S-VG CLINICA DEL IMSS STA BARBARA, S.L.W.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0371' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16694-S-VG CLINICA DEL IMSS STA BARBARA, S.L.W.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16694-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0372' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16694-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA (2).xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0373' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA (2).xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0374' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16695 INSTITUTO NACIONAL DE NEUROLOGIA-PSIQUIATRIA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16695-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0375' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16695-S-VG HOSPITAL NAVAL DE ACAPULCO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16696-S HOSPITAL NAVAL DE ACAPULCO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0376' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16696-S HOSPITAL NAVAL DE ACAPULCO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16696-S ING. ABRAHAM GALLARDO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0377' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16696-S ING. ABRAHAM GALLARDO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16697-S BE GRAND CONTADERO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0378' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16697-S BE GRAND CONTADERO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16697-S HOSPITAL NAVAL DE ACAPULCO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0379' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16697-S HOSPITAL NAVAL DE ACAPULCO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16698-S-VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0380' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16698-S-VG HOSPITAL GENERAL COLUMBA RIVERA OSORIO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16699 -VGG HOSPITAL MAC IRAPUATO, GTO..xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0381' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16699 -VGG HOSPITAL MAC IRAPUATO, GTO..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0382' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16699VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16700 CONDOMINIO PITAGORAS 308 CDMX.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0383' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16700 CONDOMINIO PITAGORAS 308 CDMX.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16700 ISSSTE PARACHO, MICHOACAN.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0384' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16700 ISSSTE PARACHO, MICHOACAN.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0385' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16701VG EXTENSION DE LABORATORIO, PLANTA 1, CIUDAD INDUSTRIAL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16702 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0386' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16702 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16703-S-VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0387' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16703-S-VG HOSPITAL EL QUEMADO EN ACAPULCO,GRO.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16704 HOSPITAL MAC IRAPUATO, GTO..xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0388' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16704 HOSPITAL MAC IRAPUATO, GTO..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16705-ISSSTE PARACHO, MICHOACAN-VG.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0389' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16705-ISSSTE PARACHO, MICHOACAN-VG.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16706-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0390' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16706-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16706-S ISSSTE PARACHO MICH..XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0391' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16706-S ISSSTE PARACHO MICH..XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16707-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0392' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16707-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16708-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0393' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16708-S HOSPITAL GENERAL DE ZONA No. 10, GTO..XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL VG.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0394' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL VG.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0395' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16709 IMSS, HOSPITAL DE GINECO-OBSTETRICIA 4, SAN ANGEL.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16710-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0396' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16710-2026-VG HOSPITAL COMUNITARIO ABASOLO, GTO..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16711 HOSPITAL DE CANCUN. Q.R. VG.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0397' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16711 HOSPITAL DE CANCUN. Q.R. VG.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16712 HOSPITAL  REGIONAL NAVAL DE MANZANILLO.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0398' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16712 HOSPITAL  REGIONAL NAVAL DE MANZANILLO.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16713- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0399' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16713- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16714-S-VG PRADO NORTE  440.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0400' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16714-S-VG PRADO NORTE  440.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16715 CLINICA CONDESA CDMX.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0401' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16715 CLINICA CONDESA CDMX.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16716 HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0402' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16716 HOSPITAL DEL IMSS, U.M.F. No. 15 MONTERREY, N.L. VG.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16717 PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0403' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16717 PRIMERA ETAPA DEL PLAN MAESTRO DE LA EXTENSION DE SAN  MIGUEL ALLENDE DE LA UNAM.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16718-S-VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX..XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0404' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16718-S-VG CENTRO DE SALUD, SERVICIOS AMPLIADOS EN SAN FRANCISCO IXHUACAN, OAX..XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16719 MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO..xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0405' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16719 MANTENIMINETO MENOR AL INMUEBLE QUE OCUPA EL CENTRO ESTATAL DE CIUDADOS CRITICS SALAMANCA, GTO..xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16720-S.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0406' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16720-S.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16721-S SHOP ELLAZ GRAN PLAZA GUADALAJARA.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0407' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16721-S SHOP ELLAZ GRAN PLAZA GUADALAJARA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16722-S SHOP ELLAZ MERIDA.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0408' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16722-S SHOP ELLAZ MERIDA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16723-S SHOP ELLAZ ANTARA.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0409' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16723-S SHOP ELLAZ ANTARA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16724-S SHOP ELLAZ GALERIAS MONTERREY.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0410' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16724-S SHOP ELLAZ GALERIAS MONTERREY.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «16725- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0411' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «16725- VG CENTRO DE SALUD SERVICIOS AMPLIADOS SAN FRANCISCO IXHUACAN.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «FORMATO_COTIZACIONES_2026_HAPA - copia.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0412' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «FORMATO_COTIZACIONES_2026_HAPA - copia.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «FORMATO_COTIZACIONES_2026_HAPA.XLSX»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0413' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «FORMATO_COTIZACIONES_2026_HAPA.XLSX»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «PRUEBA.xlsx»', NULL, '2026-10-21 03:26:56' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0414' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:26:56' AND s.mensaje='Documento «PRUEBA.xlsx»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'fase', 'Fase «Planta baja» al 40%', NULL, '2026-10-21 03:27:19' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:27:19' AND s.mensaje='Fase «Planta baja» al 40%');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'nota', 'Cliente pidió confirmar altura de andamio', NULL, '2026-10-21 03:27:19' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:27:19' AND s.mensaje='Cliente pidió confirmar altura de andamio');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'documento', 'Documento «factura-demo.pdf»', NULL, '2026-10-21 03:27:19' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:27:19' AND s.mensaje='Documento «factura-demo.pdf»');
INSERT INTO seguimiento_eventos (origen_tipo,origen_id,tipo,mensaje,usuario_id,fecha)
SELECT 'cotizacion', c.id, 'adicion', 'Se abrió la adición CCO-2026-0415', NULL, '2026-10-21 03:27:19' FROM cotizaciones_comerciales c
WHERE c.folio='CCO-2026-0003' AND NOT EXISTS (SELECT 1 FROM seguimiento_eventos s WHERE s.origen_tipo='cotizacion' AND s.origen_id=c.id AND s.fecha='2026-10-21 03:27:19' AND s.mensaje='Se abrió la adición CCO-2026-0415');
INSERT INTO documento_versiones (tipo,documento_id,raiz_id,padre_id,numero,nota,folio,fecha)
SELECT 'cotizacion', c.id, c.id, NULL, 1, 'Versión inicial', c.folio, NOW()
FROM cotizaciones_comerciales c WHERE c.folio='CCO-2026-0003'
AND NOT EXISTS (SELECT 1 FROM documento_versiones v WHERE v.tipo='cotizacion' AND v.folio='CCO-2026-0003');
INSERT INTO documento_versiones (tipo,documento_id,raiz_id,padre_id,numero,nota,folio,fecha)
SELECT 'cotizacion', h.id, p.id, p.id, 2, 'Ajuste de altura', h.folio, NOW()
FROM cotizaciones_comerciales h JOIN cotizaciones_comerciales p ON p.folio='CCO-2026-0003'
WHERE h.folio='CCO-2026-0417'
AND NOT EXISTS (SELECT 1 FROM documento_versiones v WHERE v.tipo='cotizacion' AND v.folio='CCO-2026-0417');
