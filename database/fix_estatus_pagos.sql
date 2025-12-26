-- Corregir estatus de pago para órdenes que NO son a crédito
-- Si la forma de pago NO es Crédito, asumimos que ya se pagó al momento (POS)

UPDATE ordenes_venta 
SET saldo_pendiente = 0,
    estatus_pago = 'Pagado'
WHERE forma_pago != 'Crédito' 
AND estatus != 'Cancelada' 
AND estatus != 'Cotización';

-- Opcional: Si quisiéramos ser más estrictos, deberíamos insertar registros en pagos_ordenes,
-- pero para corregir la visualización rápida, esto es suficiente.
