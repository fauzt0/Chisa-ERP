-- ========================================================================
-- Módulo: Reloj Checador Biométrico (ZKTeco ADMS)
-- Descripción: Tablas para el control de asistencia biométrica
-- Integración con ERP Chisa Recubrimientos
-- Versión: 1.0 (FASE 1)
-- ========================================================================

-- ========================================================================
-- 1. Dispositivos Registrados
-- Almacena los relojes checador dados de alta en el sistema
-- Cada dispositivo tiene su propio API token para autenticar al Proxy Local
-- ========================================================================
CREATE TABLE IF NOT EXISTS `reloj_dispositivos` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sn` varchar(50) NOT NULL COMMENT 'Número de serie del dispositivo ZKTeco',
  `alias` varchar(100) DEFAULT NULL COMMENT 'Nombre descriptivo (Ej: Reloj Entrada Planta)',
  `ubicacion` varchar(200) DEFAULT NULL COMMENT 'Ubicación física del dispositivo',
  `api_token` varchar(64) NOT NULL COMMENT 'Token único para autenticar al Proxy Local',
  `ultima_conexion` datetime DEFAULT NULL COMMENT 'Última comunicación con el Proxy Local',
  `ultimo_comando_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Último comando enviado',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_alta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sn` (`sn`),
  UNIQUE KEY `uk_token` (`api_token`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================================
-- 2. Asistencias (Checadas)
-- Registro de cada checada realizada por los empleados en los relojes
-- ========================================================================
CREATE TABLE IF NOT EXISTS `asistencias` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` varchar(20) NOT NULL COMMENT 'PIN o UserID del empleado en el reloj ZKTeco',
  `empleado_id` int(11) DEFAULT NULL COMMENT 'Relación con empleados.id del ERP (se vinculará después)',
  `fecha_hora` datetime NOT NULL COMMENT 'Fecha y hora exacta de la checada',
  `metodo` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Método de verificación: 1=Huella, 15=Rostro, 0=Otro',
  `dispositivo_sn` varchar(50) NOT NULL COMMENT 'Número de serie del reloj que registró la checada',
  `creado_el` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_fecha` (`usuario_id`, `fecha_hora`) COMMENT 'Evita duplicados de la misma checada',
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_fecha` (`fecha_hora`),
  KEY `idx_dispositivo` (`dispositivo_sn`),
  KEY `idx_fecha_empleado` (`empleado_id`, `fecha_hora`),
  CONSTRAINT `fk_asistencia_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================================
-- 3. Cola de Comandos para Reloj
-- Almacena comandos pendientes de enviar a los dispositivos ZKTeco
-- El Proxy Local consulta esta tabla periódicamente
-- ========================================================================
CREATE TABLE IF NOT EXISTS `reloj_comandos` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dispositivo_sn` varchar(50) NOT NULL COMMENT 'SN del reloj destino del comando',
  `comando` text NOT NULL COMMENT 'Comando en texto plano (ej: DATA USER PIN=2\tName=Juan)',
  `estado` enum('pendiente','enviado','ejecutado','fallido') NOT NULL DEFAULT 'pendiente',
  `respuesta` text DEFAULT NULL COMMENT 'Respuesta devuelta por el reloj al ejecutar',
  `intentos` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Número de intentos de envío',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio` datetime DEFAULT NULL COMMENT 'Cuándo se marcó como enviado',
  `fecha_ejecucion` datetime DEFAULT NULL COMMENT 'Cuándo se ejecutó en el reloj',
  `creado_por` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID del usuario que encoló el comando (desde el ERP)',
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_dispositivo_estado` (`dispositivo_sn`, `estado`),
  KEY `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================================
-- 4. Historial de Sincronización
-- Bitácora de cada comunicación entre el Proxy Local y el ERP
-- ========================================================================
CREATE TABLE IF NOT EXISTS `reloj_sync_log` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dispositivo_sn` varchar(50) DEFAULT NULL,
  `tipo` enum('asistencias','comandos','resultado','conexion','error') NOT NULL,
  `payload_resumen` varchar(255) DEFAULT NULL COMMENT 'Resumen del payload enviado/recibido',
  `registros_afectados` int(11) DEFAULT NULL COMMENT 'Cantidad de registros procesados',
  `ip_origen` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_dispositivo` (`dispositivo_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================================
-- Insertar registro por defecto para pruebas
-- Token: chisa-zkteco-proxy-token-2026
-- ========================================================================
INSERT INTO `reloj_dispositivos` (`sn`, `alias`, `ubicacion`, `api_token`, `activo`)
VALUES ('MB10VL-XXXX-TEST', 'Reloj de Prueba ZKTeco', 'Oficina Central - Pruebas', 'chisa-zkteco-proxy-token-2026', 1)
ON DUPLICATE KEY UPDATE `alias` = VALUES(`alias`);
