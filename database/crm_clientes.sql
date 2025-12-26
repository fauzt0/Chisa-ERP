-- ============================================================================
-- MÓDULO CRM - GESTIÓN DE CLIENTES
-- Sistema ERP Chisare Cubrimientos
-- Creado: 2025-12-24
-- ============================================================================

-- Tabla de categorías de clientes
CREATE TABLE IF NOT EXISTS `categorias_cliente` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `color` VARCHAR(20) DEFAULT '#007bff' COMMENT 'Color para identificación visual',
  `estatus` TINYINT(1) DEFAULT 1,
  `fecha_alta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_edicion` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_estatus` (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de clientes (Prospecto, Cliente Activo, VIP, etc.)';

-- Insertar categorías predeterminadas
INSERT INTO `categorias_cliente` (`nombre`, `descripcion`, `color`) VALUES
('Prospecto', 'Cliente potencial en proceso de contacto', '#6c757d'),
('Cliente Nuevo', 'Cliente recién adquirido (menos de 6 meses)', '#28a745'),
('Cliente Activo', 'Cliente con compras regulares', '#007bff'),
('Cliente VIP', 'Cliente de alto valor', '#ffc107'),
('Cliente Inactivo', 'Sin compras en los últimos 6 meses', '#dc3545');


-- Tabla principal de clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `numero_cliente` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Número único generado automáticamente',
  
  -- Datos de la empresa/persona
  `tipo_cliente` ENUM('Persona Física', 'Persona Moral') NOT NULL DEFAULT 'Persona Moral',
  `razon_social` VARCHAR(255) NOT NULL COMMENT 'Nombre de la empresa o persona',
  `nombre_comercial` VARCHAR(255) NULL COMMENT 'Nombre comercial si es diferente',
  `rfc` VARCHAR(13) NOT NULL UNIQUE,
  `categoria_id` INT(11) NULL COMMENT 'Categoría del cliente',
  
  -- Datos de contacto principal
  `telefono_principal` VARCHAR(20) NULL,
  `telefono_secundario` VARCHAR(20) NULL,
  `email_principal` VARCHAR(255) NULL,
  `email_secundario` VARCHAR(255) NULL,
  `sitio_web` VARCHAR(255) NULL,
  
  -- Dirección fiscal
  `calle` VARCHAR(255) NULL,
  `numero_exterior` VARCHAR(20) NULL,
  `numero_interior` VARCHAR(20) NULL,
  `colonia` VARCHAR(100) NULL,
  `codigo_postal` VARCHAR(10) NULL,
  `ciudad` VARCHAR(100) NULL,
  `estado` VARCHAR(100) NULL,
  `pais` VARCHAR(100) DEFAULT 'México',
  
  -- Información comercial
  `giro_comercial` VARCHAR(255) NULL COMMENT 'Industria o sector',
  `credito_autorizado` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Límite de crédito',
  `dias_credito` INT(11) DEFAULT 0 COMMENT 'Días de crédito otorgados',
  `descuento_general` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Descuento general en %',
  
  -- Datos del representante legal (Persona Moral)
  `representante_legal` VARCHAR(255) NULL,
  `telefono_representante` VARCHAR(20) NULL,
  `email_representante` VARCHAR(255) NULL,
  
  -- Información adicional
  `observaciones` TEXT NULL,
  `origen_cliente` VARCHAR(100) NULL COMMENT 'Cómo llegó el cliente (Referido, Web, Publicidad, etc.)',
  `vendedor_asignado_id` INT(11) NULL COMMENT 'ID del empleado vendedor asignado',
  
  -- Control de estatus
  `estatus` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `motivo_baja` TEXT NULL,
  `fecha_baja` DATE NULL,
  
  -- Metadata
  `usuario_alta_id` TINYINT(4) NULL,
  `usuario_edicion_id` TINYINT(4) NULL,
  `fecha_alta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_edicion` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  -- Índices
  INDEX `idx_numero_cliente` (`numero_cliente`),
  INDEX `idx_rfc` (`rfc`),
  INDEX `idx_razon_social` (`razon_social`),
  INDEX `idx_categoria` (`categoria_id`),
  INDEX `idx_estatus` (`estatus`),
  INDEX `idx_vendedor` (`vendedor_asignado_id`),
  
  -- Foreign Keys
  CONSTRAINT `fk_cliente_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_cliente`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cliente_vendedor` FOREIGN KEY (`vendedor_asignado_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cliente_usuario_alta` FOREIGN KEY (`usuario_alta_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cliente_usuario_edicion` FOREIGN KEY (`usuario_edicion_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla principal de clientes del CRM';


-- Tabla de contactos adicionales por cliente
CREATE TABLE IF NOT EXISTS `contactos_cliente` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cliente_id` INT(11) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `puesto` VARCHAR(100) NULL,
  `telefono` VARCHAR(20) NULL,
  `telefono_movil` VARCHAR(20) NULL,
  `email` VARCHAR(255) NULL,
  `es_principal` TINYINT(1) DEFAULT 0 COMMENT 'Contacto principal',
  `departamento` VARCHAR(100) NULL COMMENT 'Departamento (Compras, Finanzas, etc.)',
  `observaciones` TEXT NULL,
  `estatus` TINYINT(1) DEFAULT 1,
  `fecha_alta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_edicion` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_cliente` (`cliente_id`),
  INDEX `idx_principal` (`es_principal`),
  CONSTRAINT `fk_contacto_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contactos adicionales por cliente';


-- Tabla de seguimientos/interacciones con clientes
CREATE TABLE IF NOT EXISTS `seguimientos_cliente` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cliente_id` INT(11) NOT NULL,
  `tipo_seguimiento` ENUM('Llamada', 'Email', 'Reunión', 'Visita', 'WhatsApp', 'Cotización', 'Otro') NOT NULL,
  `asunto` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_seguimiento` DATETIME NOT NULL,
  `fecha_proximo_seguimiento` DATE NULL COMMENT 'Fecha para el siguiente contacto',
  
  -- Resultado del seguimiento
  `resultado` ENUM('Exitoso', 'Pendiente', 'Sin Respuesta', 'Rechazado', 'Reagendar') DEFAULT 'Pendiente',
  `observaciones` TEXT NULL,
  
  -- Archivos adjuntos
  `archivo_adjunto` VARCHAR(255) NULL COMMENT 'Ruta del archivo adjunto',
  
  -- Metadata
  `usuario_id` TINYINT(4) NULL COMMENT 'Usuario que realizó el seguimiento',
  `fecha_alta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_edicion` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_cliente` (`cliente_id`),
  INDEX `idx_tipo` (`tipo_seguimiento`),
  INDEX `idx_fecha` (`fecha_seguimiento`),
  INDEX `idx_proximo` (`fecha_proximo_seguimiento`),
  INDEX `idx_resultado` (`resultado`),
  INDEX `idx_usuario` (`usuario_id`),
  
  CONSTRAINT `fk_seguimiento_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seguimiento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Seguimientos e interacciones con clientes';


-- Tabla de documentos del cliente
CREATE TABLE IF NOT EXISTS `documentos_cliente` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cliente_id` INT(11) NOT NULL,
  `tipo_documento` ENUM('Contrato', 'Cotización', 'Factura', 'Identificación', 'Comprobante Domicilio', 'Otro') NOT NULL,
  `nombre_archivo` VARCHAR(255) NOT NULL,
  `ruta_archivo` VARCHAR(500) NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_documento` DATE NULL,
  `fecha_subida` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `subido_por` TINYINT(4) NULL,
  
  INDEX `idx_cliente` (`cliente_id`),
  INDEX `idx_tipo` (`tipo_documento`),
  CONSTRAINT `fk_documento_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documento_usuario` FOREIGN KEY (`subido_por`) REFERENCES `administradores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos asociados a clientes';


-- ============================================================================
-- VISTAS ÚTILES
-- ============================================================================

-- Vista de clientes con información completa
CREATE OR REPLACE VIEW `vista_clientes_completa` AS
SELECT 
  c.*,
  cat.nombre as categoria_nombre,
  cat.color as categoria_color,
  CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as vendedor_nombre,
  (SELECT COUNT(*) FROM seguimientos_cliente WHERE cliente_id = c.id) as total_seguimientos,
  (SELECT MAX(fecha_seguimiento) FROM seguimientos_cliente WHERE cliente_id = c.id) as ultimo_seguimiento,
  (SELECT COUNT(*) FROM contactos_cliente WHERE cliente_id = c.id AND estatus = 1) as total_contactos
FROM clientes c
LEFT JOIN categorias_cliente cat ON c.categoria_id = cat.id
LEFT JOIN empleados e ON c.vendedor_asignado_id = e.id;
