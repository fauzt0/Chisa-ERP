-- 1. Configuraciones de entorno
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES 'utf8mb4';

-- -----------------------------------------------------
-- 2. Tabla: administradores
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL, -- Cambiado de TEXT a VARCHAR para mejor rendimiento
  `apellidos` varchar(150) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(255) NOT NULL,
  `privilegios` text NOT NULL,
  `departamento` varchar(40) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL,
  `estatus` tinyint(4) NOT NULL,
  `meta_nombre` varchar(50) NOT NULL,
  `fecha_alta` date NOT NULL,
  `fecha_baja` date DEFAULT NULL,
  `fecha_edicion` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. Tabla: privilege
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `privilege` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` tinyint(4) NOT NULL,
  `permiso` varchar(100) NOT NULL,
  `valor` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_admin_priv` (`admin`),
  CONSTRAINT `fk_privilege_admin` FOREIGN KEY (`admin`) REFERENCES `administradores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. Tabla: bitacora (Auditoría del Sistema)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bitacora` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mensaje` text NOT NULL,
  `usuario` varchar(250) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_fecha_hora` (`fecha`, `hora`), -- Índice útil para reportes cronológicos
  KEY `idx_mensaje` (`mensaje`(768)) -- Ajustado a 768 para compatibilidad máxima con InnoDB
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 5. Tabla: departamentos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` varchar(100) NOT NULL UNIQUE,
  `descripcion` text,
  `responsable_id` int(11) DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_alta` date NOT NULL,
  INDEX `idx_estatus` (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 6. Tabla: empleados
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `numero_empleado` varchar(20) UNIQUE NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100),
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M', 'F', 'Otro') NOT NULL,
  `estado_civil` enum('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union Libre'),
  `rfc` varchar(13) UNIQUE NOT NULL,
  `curp` varchar(18) UNIQUE NOT NULL,
  `departamento_id` int(11),
  `puesto` varchar(100) NOT NULL,
  `jefe_directo_id` int(11),
  `fecha_ingreso` date NOT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_alta` date NOT NULL,
  `usuario_alta_id` tinyint(4),
  `usuario_edicion_id` tinyint(4),
  
  INDEX `idx_departamento` (`departamento_id`),
  
  CONSTRAINT `fk_empleado_depto` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_empleado_jefe` FOREIGN KEY (`jefe_directo_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_empleado_admin_alta` FOREIGN KEY (`usuario_alta_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_empleado_admin_edit` FOREIGN KEY (`usuario_edicion_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 7. Relación circular: Responsable de Departamento
-- -----------------------------------------------------
ALTER TABLE `departamentos` 
ADD CONSTRAINT `fk_depto_responsable` 
FOREIGN KEY (`responsable_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;