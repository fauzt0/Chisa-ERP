CREATE TABLE IF NOT EXISTS nominas_notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomina_id INT NOT NULL,
    tipo ENUM('Ajuste','Corrección','Reclasificación') NOT NULL DEFAULT 'Ajuste',
    descripcion TEXT NOT NULL,
    monto DECIMAL(10,2) DEFAULT NULL,
    usuario_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nomina_id) REFERENCES nominas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
