-- Cuentas contables mínimas para pólizas de nómina (ejecutar si no existen)
-- Requiere periodo contable abierto en periodos_contables

INSERT IGNORE INTO `cuentas_contables` (`id`, `codigo`, `nombre`, `tipo_cuenta`, `subtipo`, `naturaleza`, `nivel`, `cuenta_padre_id`, `es_afectable`, `estatus`) VALUES
(101, '6.1.01', 'Sueldos y Salarios', 'Egresos', 'Operativos', 'Deudora', 3, NULL, 1, 'Activa'),
(102, '1.1.01.003', 'Bancos', 'Activo', 'Circulante', 'Deudora', 4, NULL, 1, 'Activa'),
(103, '2.1.05', 'ISR por Pagar', 'Pasivo', 'Circulante', 'Acreedora', 3, NULL, 1, 'Activa'),
(104, '2.1.06', 'IMSS por Pagar', 'Pasivo', 'Circulante', 'Acreedora', 3, NULL, 1, 'Activa'),
(105, '2.1.07', 'INFONAVIT por Pagar', 'Pasivo', 'Circulante', 'Acreedora', 3, NULL, 1, 'Activa'),
(106, '2.1.08', 'Pensión Alimenticia por Pagar', 'Pasivo', 'Circulante', 'Acreedora', 3, NULL, 1, 'Activa');
