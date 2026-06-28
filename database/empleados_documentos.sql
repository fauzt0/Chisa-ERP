-- Documentos adjuntos de empleados (RH)
-- Ejecutar una sola vez en la base de datos del ERP

CREATE TABLE IF NOT EXISTS `empleados_documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `tipo_documento` varchar(50) NOT NULL COMMENT 'Tipo: acta_nacimiento, curp, rfc, nss, ine, etc.',
  `nombre_archivo` varchar(255) NOT NULL COMMENT 'Nombre original del archivo',
  `ruta_archivo` varchar(500) NOT NULL,
  `tamano_bytes` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `usuario_subida_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empleado_id` (`empleado_id`),
  KEY `idx_tipo_documento` (`tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos PDF/imagen del expediente del empleado';
