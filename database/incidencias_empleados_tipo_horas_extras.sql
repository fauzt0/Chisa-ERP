ALTER TABLE `incidencias_empleados`
  MODIFY `tipo_incidencia` ENUM('Retardo','Falta','Falta Justificada','Permiso',
  'Incapacidad','Suspensión','Amonestación','Renuncia','Otro','Horas Extras')
  NOT NULL;
