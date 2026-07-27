-- Archivo a crear: database/empleados_cuentas_bancarias.sql

CREATE TABLE IF NOT EXISTS `empleados_cuentas_bancarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `cuenta_bancaria_id` int(11) DEFAULT NULL COMMENT 'FK a cuentas_bancarias (catálogo de bancos)',
  `numero_cuenta` varchar(50) NOT NULL COMMENT 'Número de cuenta del empleado',
  `clabe` varchar(18) DEFAULT NULL COMMENT 'CLABE interbancaria del empleado',
  `es_default` tinyint(1) DEFAULT 0 COMMENT '1 = cuenta principal para depósito de nómina',
  `estatus` tinyint(1) DEFAULT 1 COMMENT '0=Inactiva, 1=Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_cuenta_bancaria` (`cuenta_bancaria_id`),
  CONSTRAINT `fk_empcta_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_empcta_cuenta` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuentas bancarias múltiples por empleado';

-- Insertar cuenta existente de cada empleado como cuenta default
INSERT INTO empleados_cuentas_bancarias (empleado_id, numero_cuenta, clabe, es_default, estatus)
SELECT 
    e.id,
    COALESCE(e.cuenta_bancaria, 'PENDIENTE'),
    COALESCE(e.cuenta_bancaria, NULL),
    1,
    1
FROM empleados e
WHERE e.cuenta_bancaria IS NOT NULL AND e.cuenta_bancaria != ''
  AND e.estatus IN (1, 2);

-- Asociar con cuentas_bancarias por nombre de banco (best effort)
UPDATE empleados_cuentas_bancarias ecb
JOIN empleados e ON ecb.empleado_id = e.id
JOIN cuentas_bancarias cb ON cb.banco = e.banco
SET ecb.cuenta_bancaria_id = cb.id
WHERE e.banco IS NOT NULL AND e.banco != '';
