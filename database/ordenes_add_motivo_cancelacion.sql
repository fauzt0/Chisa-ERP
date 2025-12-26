-- Agregar campo motivo_cancelacion a ordenes_venta
ALTER TABLE ordenes_venta 
ADD COLUMN motivo_cancelacion TEXT NULL AFTER observaciones;
