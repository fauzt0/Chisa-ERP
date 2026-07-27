-- Archivo a crear: database/nomina_configuracion.sql

CREATE TABLE IF NOT EXISTS `nomina_configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frecuencia` enum('Semanal','Quincenal','Mensual') NOT NULL DEFAULT 'Quincenal',
  `auto_crear` tinyint(1) DEFAULT 0 COMMENT '1 = Crear nómina automáticamente según frecuencia',
  `crear_dias_antes` int(11) DEFAULT 0 COMMENT 'Días de anticipación para crear la nómina automática',
  `ultima_ejecucion` datetime DEFAULT NULL COMMENT 'Fecha/hora de la última ejecución automática',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración de automatización de nóminas';

-- Insertar configuración por defecto
INSERT INTO `nomina_configuracion` (frecuencia, auto_crear, crear_dias_antes, activo) 
VALUES ('Quincenal', 0, 1, 1);
