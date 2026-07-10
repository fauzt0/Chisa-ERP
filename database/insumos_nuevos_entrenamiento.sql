-- ============================================================
-- Iteración 6 — Insumos faltantes para los nuevos Excels de entrenamiento
-- Fecha: 2026-07-09
--
-- Estos insumos aparecen en FICHAS DE PINTURA Y PASTA.xlsx,
-- PASTA SERGIO.xlsx y ficha masa roca.xlsx pero no existían en BD.
-- Se crean con unidad_medida=Kg como default; ajustar en catálogo
-- si alguno se compra por litros u otra unidad.
-- ============================================================

-- Usar INSERT IGNORE para que sea idempotente (re-ejecutar = sin error)

INSERT IGNORE INTO insumos
    (nombre_tecnico, codigo, unidad_medida, estatus, precio_promedio, tipo)
VALUES
    ('TRIPOLIFOSFATO DE POTASIO',  'INS-TRIPOT-001',  'Kg', 'Activo', 0, 'comprado'),
    ('MONOETILENGLICOL',           'INS-MEG-001',     'Kg', 'Activo', 0, 'comprado'),
    ('AGUA',                       'INS-AGUA-001',    'L',  'Activo', 0, 'comprado'),
    ('ACRONAL 295-D',              'INS-ACR295D-001', 'Kg', 'Activo', 0, 'comprado'),
    ('RESINA W4535',               'INS-RES-W4535',   'Kg', 'Activo', 0, 'comprado'),
    ('RESINA D-06',                'INS-RES-D06',     'Kg', 'Activo', 0, 'comprado'),
    ('RESINA D-25',                'INS-RES-D25',     'Kg', 'Activo', 0, 'comprado'),
    ('RESINA D-50',                'INS-RES-D50',     'Kg', 'Activo', 0, 'comprado'),
    ('BUTILCELLOSOLVE',            'INS-BCS-001',     'Kg', 'Activo', 0, 'comprado'),
    ('CANASOL NF 1000',            'INS-CNF1000-001', 'Kg', 'Activo', 0, 'comprado'),
    ('CERO FINO',                  'INS-CERFIN-001',  'Kg', 'Activo', 0, 'comprado'),
    ('CERO GRUESO',                'INS-CERGRUES-001','Kg', 'Activo', 0, 'comprado'),
    -- Sinónimos de insumos existentes con nombre exacto del Excel
    ('CAOLIN M-325',               'INS-CAO325-001',  'Kg', 'Activo', 0, 'comprado'),
    ('CARBONATO M-325',            'INS-CARB325-001', 'Kg', 'Activo', 0, 'comprado'),
    ('TILOSE',                     'INS-TILOSE-001',  'Kg', 'Activo', 0, 'comprado'),
    ('TEXANOL',                    'INS-TEXANOL-001', 'Kg', 'Activo', 0, 'comprado'),
    ('DISPERSANTE',                'INS-DISP-001',    'Kg', 'Activo', 0, 'comprado'),
    ('ANTIESPUMANTE',              'INS-ANTIESP-001', 'Kg', 'Activo', 0, 'comprado'),
    ('ARENA SILICA',               'INS-ARENASIL-001','Kg', 'Activo', 0, 'comprado'),
    ('CARBONATO',                  'INS-CARB-001',    'Kg', 'Activo', 0, 'comprado');

SELECT 'Insumos insertados (o ya existentes)' AS resultado, ROW_COUNT() AS nuevos_insertados;

-- Verificar el catálogo final de insumos nuevos
SELECT id, codigo, nombre_tecnico, unidad_medida, tipo
FROM insumos
WHERE codigo LIKE 'INS-%'
ORDER BY nombre_tecnico;
