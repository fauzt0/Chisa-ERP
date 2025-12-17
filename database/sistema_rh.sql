-- 1. Configuraciones de entorno
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES 'utf8mb4';

-- -----------------------------------------------------
-- 2. Tabla: administradores (Necesaria para auditoría en empleados)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `usuario` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `estatus` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. Tabla: departamentos (Catálogo base)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `descripcion` TEXT,
  `responsable_id` INT(11) DEFAULT NULL COMMENT 'FK circular a empleados se define después',
  `estatus` TINYINT(1) DEFAULT 1,
  `fecha_alta` DATE NOT NULL,
  INDEX `idx_estatus` (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. Tabla: empleados (Tabla principal)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `numero_empleado` VARCHAR(20) UNIQUE NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido_paterno` VARCHAR(100) NOT NULL,
  `apellido_materno` VARCHAR(100),
  `fecha_nacimiento` DATE NOT NULL,
  `genero` ENUM('M', 'F', 'Otro') NOT NULL,
  `estado_civil` ENUM('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union Libre'),
  `telefono` VARCHAR(15),
  `telefono_emergencia` VARCHAR(15),
  `email_personal` VARCHAR(150),
  `email_corporativo` VARCHAR(150),
  `calle` VARCHAR(200),
  `numero_exterior` VARCHAR(20),
  `numero_interior` VARCHAR(20),
  `colonia` VARCHAR(100),
  `codigo_postal` VARCHAR(5),
  `ciudad` VARCHAR(100),
  `estado` VARCHAR(100),
  `pais` VARCHAR(50) DEFAULT 'México',
  `rfc` VARCHAR(13) UNIQUE NOT NULL,
  `curp` VARCHAR(18) UNIQUE NOT NULL,
  `nss` VARCHAR(11) UNIQUE,
  `tipo_trabajador` ENUM('Planta', 'Temporal', 'Por Proyecto', 'Honorarios', 'Practicante') NOT NULL,
  `departamento_id` INT(11),
  `puesto` VARCHAR(100) NOT NULL,
  `jefe_directo_id` INT(11),
  `fecha_ingreso` DATE NOT NULL,
  `fecha_baja` DATE NULL,
  `motivo_baja` TEXT NULL,
  `salario_base_mensual` DECIMAL(10,2) NOT NULL,
  `salario_base_diario` DECIMAL(10,2) NOT NULL,
  `tipo_nomina` ENUM('Semanal', 'Quincenal', 'Mensual') DEFAULT 'Quincenal',
  `forma_pago` ENUM('Transferencia', 'Efectivo', 'Cheque') DEFAULT 'Transferencia',
  `banco` VARCHAR(100),
  `cuenta_bancaria` VARCHAR(18),
  `tiene_fonacot` BOOLEAN DEFAULT FALSE,
  `tiene_infonavit` BOOLEAN DEFAULT FALSE,
  `descuento_infonavit` DECIMAL(10,2) DEFAULT 0,
  `estatus` TINYINT(1) DEFAULT 1,
  `fecha_alta` DATE NOT NULL,
  `fecha_edicion` DATE NULL,
  `usuario_alta_id` INT(11),
  `usuario_edicion_id` INT(11),
  
  INDEX `idx_departamento` (`departamento_id`),
  INDEX `idx_estatus` (`estatus`),
  
  CONSTRAINT `fk_empleado_depto` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_empleado_jefe` FOREIGN KEY (`jefe_directo_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_empleado_admin_alta` FOREIGN KEY (`usuario_alta_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_empleado_admin_edit` FOREIGN KEY (`usuario_edicion_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 5. Tabla: nomina_detalles (Dependiente de empleados)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `nomina_detalles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empleado_id` INT(11) NOT NULL,
  `periodo_inicio` DATE NOT NULL,
  `periodo_fin` DATE NOT NULL,
  `salario_base` DECIMAL(10,2),
  `comisiones` DECIMAL(10,2) DEFAULT 0,
  `bonos` DECIMAL(10,2) DEFAULT 0,
  `horas_extra` DECIMAL(10,2) DEFAULT 0,
  `deducciones_imss` DECIMAL(10,2) DEFAULT 0,
  `deducciones_isr` DECIMAL(10,2) DEFAULT 0,
  `otras_deducciones` DECIMAL(10,2) DEFAULT 0,
  `total_percepciones` DECIMAL(10,2),
  `total_deducciones` DECIMAL(10,2),
  `neto_pagar` DECIMAL(10,2),
  `estatus` ENUM('Pendiente', 'Pagada', 'Cancelada') DEFAULT 'Pendiente',
  `fecha_pago` DATE NULL,
  CONSTRAINT `fk_nomina_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 6. Ajustes Finales (Relaciones circulares)
-- -----------------------------------------------------
-- Ahora que existe empleados, vinculamos el responsable del departamento
ALTER TABLE `departamentos` 
ADD CONSTRAINT `fk_depto_responsable` 
FOREIGN KEY (`responsable_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;