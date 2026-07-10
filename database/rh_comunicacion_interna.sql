-- Comunicación interna RH: mensajes y tareas entre empleados vinculados
-- Idempotente

CREATE TABLE IF NOT EXISTS rh_mensajes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  de_empleado_id INT(11) NOT NULL,
  para_empleado_id INT(11) NOT NULL,
  de_usuario_id INT(11) NULL DEFAULT NULL COMMENT 'administradores.id quien envió',
  mensaje TEXT NOT NULL,
  leido TINYINT(1) NOT NULL DEFAULT 0,
  fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_rh_msg_para (para_empleado_id, leido),
  KEY idx_rh_msg_de (de_empleado_id),
  KEY idx_rh_msg_fecha (fecha_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rh_tareas (
  id INT(11) NOT NULL AUTO_INCREMENT,
  de_empleado_id INT(11) NOT NULL,
  para_empleado_id INT(11) NOT NULL,
  de_usuario_id INT(11) NULL DEFAULT NULL,
  titulo VARCHAR(200) NOT NULL,
  descripcion TEXT NULL,
  estatus ENUM('Pendiente','En proceso','Hecha','Cancelada') NOT NULL DEFAULT 'Pendiente',
  fecha_limite DATE NULL DEFAULT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_rh_tarea_para (para_empleado_id, estatus),
  KEY idx_rh_tarea_de (de_empleado_id),
  KEY idx_rh_tarea_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
