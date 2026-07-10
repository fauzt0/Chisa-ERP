-- ============================================================================
-- Órdenes de Compra — Documentos externos y comentarios
-- ============================================================================

CREATE TABLE IF NOT EXISTS `ordenes_compra_comentarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `orden_compra_id` INT(11) NOT NULL,
  `comentario` TEXT NOT NULL,
  `creado_por` INT(11) NULL,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_oc_comentarios_orden` (`orden_compra_id`),
  CONSTRAINT `fk_oc_comentarios_orden` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ordenes_compra_documentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `orden_compra_id` INT(11) NOT NULL,
  `tipo` ENUM('Factura','Nota de remisión','Cotización','Otro') NOT NULL DEFAULT 'Otro',
  `nombre_archivo` VARCHAR(255) NOT NULL,
  `ruta` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(120) NULL,
  `tamano_bytes` INT(11) NULL,
  `notas` VARCHAR(500) NULL,
  `subido_por` INT(11) NULL,
  `fecha_subida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_oc_documentos_orden` (`orden_compra_id`),
  CONSTRAINT `fk_oc_documentos_orden` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
