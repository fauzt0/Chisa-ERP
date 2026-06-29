-- Datos fiscales y de contacto de la empresa (registro único id=1)
CREATE TABLE IF NOT EXISTS configuracion_empresa (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  razon_social VARCHAR(255) NOT NULL DEFAULT 'Chisa Recubrimientos',
  nombre_comercial VARCHAR(255) NULL,
  rfc VARCHAR(13) NULL,
  regimen_fiscal VARCHAR(150) NULL,
  calle VARCHAR(150) NULL,
  numero_exterior VARCHAR(20) NULL,
  numero_interior VARCHAR(20) NULL,
  colonia VARCHAR(100) NULL,
  ciudad VARCHAR(100) NULL,
  estado VARCHAR(100) NULL,
  codigo_postal VARCHAR(10) NULL,
  telefono VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  sitio_web VARCHAR(255) NULL,
  logo VARCHAR(255) NULL DEFAULT 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg',
  fecha_actualizacion DATETIME NULL,
  actualizado_por INT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracion_empresa (id, razon_social, nombre_comercial, ciudad, estado)
VALUES (1, 'Chisa Recubrimientos', 'CHISA', 'México', '');
