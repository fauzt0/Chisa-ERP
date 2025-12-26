-- =====================================================
-- TABLA: bitacora_usuarios
-- Registro de actividades de usuarios administradores
-- =====================================================
CREATE TABLE IF NOT EXISTS bitacora_usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  accion VARCHAR(100) NOT NULL COMMENT 'Tipo de acción realizada',
  modulo VARCHAR(50) NOT NULL COMMENT 'Módulo donde se realizó la acción',
  descripcion TEXT NULL COMMENT 'Descripción detallada de la acción',
  ip_address VARCHAR(45) NULL COMMENT 'Dirección IP del usuario',
  user_agent TEXT NULL COMMENT 'Navegador/dispositivo',
  fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  KEY idx_usuario (usuario_id),
  KEY idx_fecha (fecha_hora),
  KEY idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Bitácora de actividades de usuarios';
