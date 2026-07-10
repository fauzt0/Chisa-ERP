-- Simulador de alertas para demostraciones (Chat B)
-- Ejecutar una vez en la BD del ERP

CREATE TABLE IF NOT EXISTS alertas_simuladas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(50) NOT NULL,
  modulo VARCHAR(50) NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  mensaje TEXT NOT NULL,
  url VARCHAR(255) NOT NULL,
  severidad ENUM('info','warning','danger') NOT NULL DEFAULT 'info',
  icono VARCHAR(50) NOT NULL DEFAULT 'info-circle',
  tiempo VARCHAR(20) NOT NULL DEFAULT 'Ahora',
  creado_por INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_creado_en (creado_en),
  INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Otorgar permiso al superadmin (id=1) si existe
INSERT IGNORE INTO privilege (admin, permiso, valor)
SELECT 1, 'admin_simular_alertas', 1
FROM DUAL
WHERE EXISTS (SELECT 1 FROM administradores WHERE id = 1);
