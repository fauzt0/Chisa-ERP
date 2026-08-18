-- Opcional: guarda qué empleado se canceló en nominas_cancelaciones.
-- El código funciona sin esta columna (el motivo incluye el detalle_id).

ALTER TABLE nominas_cancelaciones
  ADD COLUMN detalle_id INT NULL AFTER nomina_id;
