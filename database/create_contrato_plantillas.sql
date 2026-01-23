CREATE TABLE IF NOT EXISTS contrato_plantillas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT NOT NULL,
    estatus TINYINT DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
    fecha_creacion DATETIME NOT NULL,
    fecha_edicion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuario_id INT DEFAULT NULL,
    INDEX (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO contrato_plantillas (nombre, descripcion, contenido, fecha_creacion, estatus)
SELECT 'Contrato Individual Estándar', 'Plantilla por defecto generada automáticamente.', 
'CONTRATO INDIVIDUAL DE TRABAJO

CONTRATO No. {{version}}
Tipo: {{tipo_contrato}}

Entre CHISA RECUBRIMIENTOS (en adelante "LA EMPRESA") y:

NOMBRE: {{nombre_completo}}
RFC: {{rfc}}
CURP: {{curp}}
NSS: {{nss}}

CLÁUSULAS:

PRIMERA.- PUESTO: {{puesto}}
SEGUNDA.- DEPARTAMENTO: {{departamento}}
TERCERA.- TIPO DE TRABAJADOR: {{tipo_trabajador}}
CUARTA.- SALARIO BASE MENSUAL: {{salario_base_mensual}}
QUINTA.- SALARIO BASE DIARIO: {{salario_base_diario}}
SEXTA.- TIPO DE NÓMINA: {{tipo_nomina}}
SÉPTIMA.- JORNADA LABORAL: {{jornada_laboral}}

FECHA DE INICIO: {{fecha_inicio}}
MOTIVO: {{motivo_cambio}}

Fecha de generación: {{fecha_generacion}}', NOW(), 1
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM contrato_plantillas WHERE nombre = 'Contrato Individual Estándar');
