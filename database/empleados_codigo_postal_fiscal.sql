-- C.P. fiscal del trabajador (domicilio fiscal / CFDI)
ALTER TABLE `empleados`
  ADD COLUMN `codigo_postal_fiscal` varchar(5) DEFAULT NULL
  COMMENT 'Código postal fiscal (SAT)'
  AFTER `codigo_postal`;

-- Asignar CP del listado Chisa (por RFC)
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '08220' WHERE `rfc` = 'SAME870409RA6';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '08220' WHERE `rfc` = 'JIRT881122QU5';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '08220' WHERE `rfc` = 'SACJ7902091PA';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '50500' WHERE `rfc` = 'LUGM690116N80';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '03660' WHERE `rfc` = 'AILM8101153M7';
UPDATE `empleados` SET `codigo_postal` = '50100',  `codigo_postal_fiscal` = '50100' WHERE `rfc` = 'SAMM9211125F6';
UPDATE `empleados` SET `codigo_postal` = '55120',  `codigo_postal_fiscal` = '55120' WHERE `rfc` = 'GAFC7509239Q7';
UPDATE `empleados` SET `codigo_postal` = '50780',  `codigo_postal_fiscal` = '08220' WHERE `rfc` = 'MAHF781006GK1';
UPDATE `empleados` SET `codigo_postal` = '50783',  `codigo_postal_fiscal` = '08220' WHERE `rfc` = 'AAFG880507FF3';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '29045' WHERE `rfc` = 'NEMR7808202T3';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '54010' WHERE `rfc` = 'SACM780827AAA';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '09900' WHERE `rfc` = 'GAPO790101AT1';
UPDATE `empleados` SET `codigo_postal` = NULL,     `codigo_postal_fiscal` = '57130' WHERE `rfc` = 'QUMI830806BN5';
