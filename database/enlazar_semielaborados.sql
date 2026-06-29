-- Enlaces recomendados insumo (comprado) → producto fabricado (semielaborado)
-- Ejecutar DESPUÉS de importar BASES ORGANICAS Y TINTAS.xls
-- y de activar la formulación activa de cada producto.

-- Coincidencias exactas de nombre
CALL sp_enlazar_insumo_fabricado(105, 205);  -- TINTA NEGRA
CALL sp_enlazar_insumo_fabricado(91, 204);   -- SOLUCION DE AEROSIL 200
CALL sp_enlazar_insumo_fabricado(96, 206);   -- TINTA AMARILLO OXIDO

-- Nombre abreviado en fórmulas CHISA GLASS → base orgánica
CALL sp_enlazar_insumo_fabricado(61, 202);   -- BLANCO → BASE ORGANICA BLANCA

-- Verificar resultado
SELECT * FROM v_insumos_fabricados;
