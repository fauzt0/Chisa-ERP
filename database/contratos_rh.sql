CREATE TABLE `contratos_empleados` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empleado_id` INT(11) NOT NULL,
  `version` INT(11) NOT NULL DEFAULT 1,
  `tipo_contrato` ENUM('Inicial', 'Renovación', 'Modificación Salarial', 'Cambio de Puesto', 'Cambio de Departamento') NOT NULL,
  `vigente` TINYINT(1) DEFAULT 1,
  
  -- Datos del contrato (snapshot del empleado en ese momento)
  `puesto` VARCHAR(100) NOT NULL,
  `departamento` VARCHAR(100) NOT NULL,
  `tipo_trabajador` VARCHAR(50) NOT NULL,
  `salario_base_mensual` DECIMAL(15,2) NOT NULL,
  `salario_base_diario` DECIMAL(15,2) NOT NULL,
  `tipo_nomina` VARCHAR(50) NOT NULL,
  `jornada_laboral` VARCHAR(100) DEFAULT 'Tiempo Completo',
  
  -- Fechas
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NULL,
  `fecha_creacion` DATETIME NOT NULL,
  
  -- Metadata
  `motivo_cambio` TEXT NULL,
  `creado_por` TINYINT(4) NULL,
  `contrato_texto` TEXT NULL COMMENT 'Texto completo del contrato generado',
  
  INDEX `idx_empleado` (`empleado_id`),
  INDEX `idx_vigente` (`vigente`),
  CONSTRAINT `fk_contrato_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contrato_admin` FOREIGN KEY (`creado_por`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `solicitudes_vacaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empleado_id` INT(11) NOT NULL,
  `periodo_vacaciones_id` INT(11) NOT NULL COMMENT 'De qué período se toman',
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `dias_solicitados` INT(11) NOT NULL,
  `estatus` ENUM('Pendiente', 'Aprobada', 'Rechazada', 'Cancelada') DEFAULT 'Pendiente',
  `motivo_rechazo` TEXT NULL,
  `aprobado_por` TINYINT(4) NULL COMMENT 'ID del admin que aprobó',
  `fecha_solicitud` DATETIME NOT NULL,
  `fecha_aprobacion` DATETIME NULL,
  `observaciones` TEXT NULL,
  
  INDEX `idx_empleado` (`empleado_id`),
  INDEX `idx_estatus` (`estatus`),
  INDEX `idx_fechas` (`fecha_inicio`, `fecha_fin`),
  CONSTRAINT `fk_solicitud_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitud_periodo` FOREIGN KEY (`periodo_vacaciones_id`) REFERENCES `vacaciones_empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitud_aprobador` FOREIGN KEY (`aprobado_por`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `vacaciones_empleados` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empleado_id` INT(11) NOT NULL,
  `periodo_inicio` DATE NOT NULL COMMENT 'Inicio del período de vacaciones (aniversario)',
  `periodo_fin` DATE NOT NULL COMMENT 'Fin del período',
  `anios_antiguedad` INT(11) NOT NULL COMMENT 'Años cumplidos en este período',
  `dias_correspondientes` INT(11) NOT NULL COMMENT 'Días que le tocan por ley',
  `dias_adicionales` INT(11) DEFAULT 0 COMMENT 'Días extra otorgados por la empresa',
  `dias_totales` INT(11) NOT NULL COMMENT 'Total disponibles',
  `dias_tomados` INT(11) DEFAULT 0 COMMENT 'Días ya utilizados',
  `dias_disponibles` INT(11) NOT NULL COMMENT 'Días restantes',
  `fecha_creacion` DATETIME NOT NULL,
  
  INDEX `idx_empleado` (`empleado_id`),
  INDEX `idx_periodo` (`periodo_inicio`, `periodo_fin`),
  CONSTRAINT `fk_vacaciones_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla de incidencias de empleados
CREATE TABLE IF NOT EXISTS incidencias_empleados (
  id INT(11) NOT NULL AUTO_INCREMENT,
  empleado_id INT(11) NOT NULL,
  tipo_incidencia ENUM('Retardo', 'Falta', 'Falta Justificada', 'Permiso', 'Incapacidad', 'Suspensión', 'Amonestación', 'Renuncia', 'Otro') NOT NULL,
  fecha_incidencia DATE NOT NULL,
  hora_incidencia TIME NULL COMMENT 'Para retardos',
  descripcion TEXT NULL,
  observaciones TEXT NULL,
  tiene_descuento TINYINT(1) DEFAULT 0 COMMENT 'Si aplica descuento en nómina',
  monto_descuento DECIMAL(10,2) NULL,
  archivo_adjunto VARCHAR(255) NULL COMMENT 'Ruta del archivo (incapacidad, justificante)',
  registrado_por INT(11) NULL COMMENT 'ID del usuario que registró',
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  estatus ENUM('Activa', 'Cancelada', 'Procesada') DEFAULT 'Activa',
  PRIMARY KEY (id),
  KEY idx_empleado (empleado_id),
  KEY idx_fecha (fecha_incidencia),
  KEY idx_tipo (tipo_incidencia),
  CONSTRAINT fk_incidencia_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de incidencias de empleados';


CREATE TABLE IF NOT EXISTS horarios_empleados (
  id INT(11) NOT NULL AUTO_INCREMENT,
  empleado_id INT(11) NOT NULL,
  dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
  hora_entrada TIME NOT NULL,
  hora_salida TIME NOT NULL,
  hora_entrada_comida TIME NULL COMMENT 'Inicio de comida/descanso',
  hora_salida_comida TIME NULL COMMENT 'Fin de comida/descanso',
  es_dia_laboral TINYINT(1) DEFAULT 1 COMMENT '1=Día laboral, 0=Día de descanso',
  turno VARCHAR(50) NULL COMMENT 'Matutino, Vespertino, Nocturno, etc.',
  fecha_inicio DATE NOT NULL COMMENT 'Fecha desde la que aplica este horario',
  fecha_fin DATE NULL COMMENT 'Fecha hasta la que aplica (NULL = indefinido)',
  observaciones TEXT NULL,
  creado_por INT(11) NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (id),
  KEY idx_empleado (empleado_id),
  KEY idx_dia (dia_semana),
  KEY idx_vigencia (fecha_inicio, fecha_fin),
  CONSTRAINT fk_horario_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Horarios laborales de empleados por día de la semana';
-- Índice compuesto para búsquedas eficientes
CREATE INDEX idx_empleado_vigente ON horarios_empleados(empleado_id, estatus, fecha_inicio, fecha_fin);