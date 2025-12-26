-- Primero actualizar los valores existentes a los nuevos tipos
UPDATE clientes SET tipo_cliente = 'Regular' WHERE tipo_cliente = 'Empresa';
UPDATE clientes SET tipo_cliente = 'Regular' WHERE tipo_cliente = 'Persona Física';
UPDATE clientes SET tipo_cliente = 'Mostrador' WHERE codigo = 'CLI-00000';

-- Ahora sí modificar la columna con los nuevos valores ENUM
ALTER TABLE clientes 
MODIFY COLUMN tipo_cliente ENUM('Mostrador', 'Regular', 'Gobierno', 'Licitación', 'Distribuidor') DEFAULT 'Regular';
