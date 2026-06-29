-- Estatus laboral de empleados: 0=Inactivo, 1=Activo, 2=Reingreso (activo laboral)
ALTER TABLE `empleados`
  MODIFY COLUMN `estatus` tinyint(1) NOT NULL DEFAULT 1
  COMMENT '0=Inactivo, 1=Activo, 2=Reingreso';
