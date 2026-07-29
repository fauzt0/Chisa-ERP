-- Iteración 3 Fase 2: agregar "Depósito" como forma de pago en empleados
ALTER TABLE empleados MODIFY COLUMN forma_pago ENUM('Transferencia','Efectivo','Cheque','Depósito') DEFAULT 'Transferencia';
