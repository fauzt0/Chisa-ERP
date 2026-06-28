-- Registro de pagos parciales y consolidación de adeudos
-- Ejecutar una sola vez

CREATE TABLE IF NOT EXISTS `nominas_pagos_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomina_id` int(11) NOT NULL COMMENT 'Nómina desde donde se procesó el pago',
  `nomina_detalle_id` int(11) DEFAULT NULL COMMENT 'Detalle del periodo actual, si aplica',
  `empleado_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `monto_periodo` decimal(10,2) DEFAULT 0.00 COMMENT 'Parte aplicada al periodo actual',
  `monto_adeudos` decimal(10,2) DEFAULT 0.00 COMMENT 'Parte aplicada a adeudos anteriores',
  `detalle_adeudos_json` text DEFAULT NULL COMMENT 'JSON de detalle_ids de adeudos liquidados',
  `poliza_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nomina_id` (`nomina_id`),
  KEY `idx_empleado_id` (`empleado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de pagos de nómina por empleado';
