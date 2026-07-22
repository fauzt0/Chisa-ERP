#!/bin/bash

# Lista de tablas de la base de datos
tables=(
"administradores"
"alertas_stock"
"bitacora"
"categorias_insumos"
"categorias_productos"
"clientes"
"contactos_proveedor"
"contratos_empleados"
"cuentas_bancarias"
"cuentas_contables"
"departamentos"
"descuentos"
"detalle_entregas_almacen"
"detalle_formulacion"
"detalle_orden_compra"
"detalle_orden_produccion"
"detalle_orden_venta"
"ejercicios_fiscales"
"empleados"
"entregas_almacen"
"facturas"
"facturas_obras"
"formulaciones"
"horarios_empleados"
"incidencias_empleados"
"insumos"
"lotes_movimientos"
"lotes_produccion"
"movimientos_bancarios"
"movimientos_inventario"
"movimientos_productos"
"nomina_detalles"
"nominas"
"nominas_conceptos"
"nominas_detalle"
"obras"
"obras_archivos"
"obras_comentarios"
"obras_pagos"
"obras_productos"
"ordenes_compra"
"ordenes_produccion"
"ordenes_venta"
"pagos_ordenes"
"pagos_servicios_recurrentes"
"periodos_contables"
"polizas"
"polizas_detalle"
"presentaciones_producto"
"privilege"
"produccion_historial_estatus"
"productos"
"proveedor_insumo"
"proveedores"
"servicios_recurrentes"
"solicitudes_produccion"
"solicitudes_vacaciones"
"vacaciones"
"vacaciones_empleados"
)

echo "Analizando uso de tablas en el código..."
echo "=========================================="
echo ""

unused_tables=()
used_tables=()

for table in "${tables[@]}"; do
    # Buscar referencias en PHP (modelos, controladores, vistas)
    count=$(grep -r -i "$table" /home/st32477/domains/erp.chisarecubrimientos.com.mx/public_html/application --include="*.php" 2>/dev/null | wc -l)
    
    if [ $count -eq 0 ]; then
        unused_tables+=("$table")
        echo "❌ $table - NO USADA (0 referencias)"
    else
        used_tables+=("$table")
        echo "✅ $table - USADA ($count referencias)"
    fi
done

echo ""
echo "=========================================="
echo "RESUMEN"
echo "=========================================="
echo "Total de tablas: ${#tables[@]}"
echo "Tablas usadas: ${#used_tables[@]}"
echo "Tablas NO usadas: ${#unused_tables[@]}"
echo ""

if [ ${#unused_tables[@]} -gt 0 ]; then
    echo "TABLAS NO UTILIZADAS:"
    for table in "${unused_tables[@]}"; do
        echo "  - $table"
    done
fi
