# Checklist de Pruebas Manuales — Módulo CRM Ventas

**Fecha de preparación:** Julio 2026  
**Módulo:** CRM Ventas (`ventas/Clientes`, `ventas/Ordenes`, `ventas/Pos`, `ventas/ObrasVentas`, `ventas/Descuentos`)  
**Objetivo:** Verificación completa antes de presentación al cliente

---

## 1. Clientes — CRUD y Gestión

| # | Prueba | Pasos | Resultado Esperado | ✅/❌ |
|---|--------|-------|-------------------|------|
| 1.1 | Listado de clientes | Navegar a `ventas/Clientes` | Tabla llena el ancho completo, columnas visibles | |
| 1.2 | DataTable — paginación | Cambiar entre páginas | Navegación fluida, datos correctos | |
| 1.3 | DataTable — búsqueda rápida | Escribir en campo búsqueda | Filtra resultados en tiempo real | |
| 1.4 | DataTable — filtros | Usar filtros de Tipo, Estatus, Saldo | Filtra correctamente | |
| 1.5 | DataTable — ordenamiento | Click en encabezados de columna | Ordena ascendente/descendente | |
| 1.6 | **DataTable — Exportar Excel** | Click botón "Excel" | Descarga archivo .xlsx con datos visibles | |
| 1.7 | **DataTable — Imprimir** | Click botón "Imprimir" | Abre ventana de impresión con formato | |
| 1.8 | Nuevo cliente | Click "Nuevo Cliente", llenar formulario | Cliente creado, aparece en tabla | |
| 1.9 | Editar cliente | Click ícono lápiz, modificar, guardar | Datos actualizados en tabla | |
| 1.10 | Ver detalle (offcanvas) | Click ícono ojo | Offcanvas abre con 3 pestañas: Info, Ventas, Cotizaciones | |
| 1.11 | Pestaña Ventas en offcanvas | Click en pestaña Ventas | Muestra historial de órdenes del cliente | |
| 1.12 | Pestaña Cotizaciones en offcanvas | Click en pestaña Cotizaciones | Muestra cotizaciones, botón "Convertir a venta" | |
| 1.13 | Convertir cotización a venta | Click en "Convertir a venta" | Cotización cambia a orden confirmada | |
| 1.14 | Ver detalle de orden (modal) | Click ojo en historial de ventas | Modal con productos, totales, botón imprimir | |
| 1.15 | Eliminar cliente | Click ícono basura, confirmar | Cliente eliminado si no tiene órdenes; mensaje de error si tiene | |
| 1.16 | Validación formulario | Dejar campos obligatorios vacíos | Mensajes de validación HTML5 | |

---

## 2. Carga Masiva de Clientes (NUEVO)

| # | Prueba | Pasos | Resultado Esperado | ✅/❌ |
|---|--------|-------|-------------------|------|
| 2.1 | Descargar plantilla | Click "Plantilla Excel" en toolbar | Descarga archivo `plantilla_clientes_erp.xlsx` | |
| 2.2 | Abrir modal carga masiva | Click "Carga masiva" | Modal con instrucciones y campo de archivo | |
| 2.3 | Validar formato de archivo | Subir un .pdf o archivo inválido | Mensaje "Solo se aceptan archivos .xlsx o .xls" | |
| 2.4 | Validar archivo vacío | Click "Importar clientes" sin seleccionar archivo | Mensaje "Seleccione un archivo Excel" | |
| 2.5 | Importar plantilla con datos | Llenar plantilla con 5+ clientes, subir | Muestra: X insertados, Y omitidos, Z errores | |
| 2.6 | RFC duplicado en archivo | Incluir 2 filas con mismo RFC | Segunda fila se omite | |
| 2.7 | RFC existente en sistema | Incluir RFC que ya existe en BD | Fila se omite, mensaje "ya existe en el sistema" | |
| 2.8 | Fila de ejemplo | No eliminar fila de ejemplo | Se omite automáticamente | |
| 2.9 | RFC inválido | RFC con menos de 12 caracteres | Error "no tiene formato válido" | |
| 2.10 | Sin razón social | Fila sin razón social | Error "razón social es obligatoria" | |
| 2.11 | Refrescar tabla post-import | Después de importar con éxito | Tabla se refresca mostrando nuevos clientes | |

---

## 3. Órdenes de Venta

| # | Prueba | Pasos | Resultado Esperado | ✅/❌ |
|---|--------|-------|-------------------|------|
| 3.1 | Listado de órdenes | Navegar a `ventas/Ordenes` | Tabla con todas las órdenes | |
| 3.2 | Filtros de órdenes | Usar filtros Estatus, Tipo, Fechas | Filtra correctamente | |
| 3.3 | **Exportar Excel** | Click botón "Excel" | Descarga .xlsx con órdenes | |
| 3.4 | Ver detalle de orden | Click ícono ojo | Modal con datos completos, productos y totales | |
| 3.5 | Confirmar orden | Click ✓ en cotización | Cambia a "En Preparación" o "Confirmada" | |
| 3.6 | Cancelar orden | Click ✕, ingresar motivo | Orden cancelada con motivo registrado | |
| 3.7 | Registrar pago | Click $, llenar formulario de pago | Pago registrado, saldo pendiente se actualiza | |
| 3.8 | Historial de pagos | Ver historial en modal de pago | Muestra pagos anteriores con folio, fecha, monto | |
| 3.9 | Imprimir recibo | Click dropdown de impresión > Formato Factura | Nueva pestaña con recibo en formato factura | |
| 3.10 | Imprimir nota de remisión | Click dropdown > Nota de Remisión | Nueva pestaña con formato remisión | |
| 3.11 | Imprimir formato moderno | Click dropdown > Formato Moderno | Nueva pestaña con diseño moderno | |
| 3.12 | Botón "Ir al POS" | Click en "Ir al POS" | Navega a la interfaz de punto de venta | |

---

## 4. Punto de Venta (POS)

| # | Prueba | Pasos | Resultado Esperado | ✅/❌ |
|---|--------|-------|-------------------|------|
| 4.1 | Interfaz POS | Navegar a `ventas/Pos` | Catálogo productos (izq) + Ticket (der) | |
| 4.2 | Buscar producto | Escribir nombre/código | Grid de productos se actualiza | |
| 4.3 | Top productos | Ver sección "Más Vendidos" | Muestra ~6 productos más vendidos | |
| 4.4 | Agregar al ticket | Click en producto del catálogo | Aparece en tabla del ticket | |
| 4.5 | Cambiar cantidad | Modificar cantidad en ticket | Subtotal se recalcula | |
| 4.6 | Eliminar del ticket | Click basura en línea del ticket | Producto se remueve | |
| 4.7 | Seleccionar cliente | Buscar en Select2 | Datos del cliente se muestran | |
| 4.8 | Nuevo cliente desde POS | Click +, llenar formulario | Cliente creado, seleccionado automáticamente | |
| 4.9 | Datos fiscales | Click ícono factura en cliente seleccionado | Modal para editar RFC, régimen, uso CFDI | |
| 4.10 | Aplicar descuento | Seleccionar descuento en dropdown | Se muestra en ticket, total se recalcula | |
| 4.11 | Tipo Mostrador | Seleccionar Mostrador, Cobrar | Orden creada como Entregada, pago automático | |
| 4.12 | Tipo Pedido | Seleccionar Pedido, llenar dirección | Campos de envío visibles, fecha entrega | |
| 4.13 | Costo de envío | Ingresar costo en Pedido | Se suma al total | |
| 4.14 | Guardar cotización | Click "Guardar Cotización" | Orden creada con estatus Cotización | |
| 4.15 | Cobrar (efectivo) | Click "Cobrar" con pago Efectivo | Orden creada Entregada, pago registrado | |
| 4.16 | Cobrar (crédito) | Click "Cobrar" con pago Crédito | Orden creada, saldo pendiente registrado | |
| 4.17 | SweetAlert post-venta | Después de cobrar | Modal con opciones de recibo: Factura, Remisión, Moderno | |
| 4.18 | Cancelar ticket | Click "Cancelar Ticket" | Ticket se limpia completamente | |
| 4.19 | Ver formulación | Click ícono matraz en producto fabricado | Modal con componentes de la fórmula | |
| 4.20 | Historial formulaciones | Click "Historial" en modal formulación | Lista de versiones anteriores | |
| 4.21 | Factura simulada | Verificar tabla facturas después de venta | Factura generada con UUID simulado | |

---

## 5. Descuentos

| # | Prueba | Pasos | Resultado Esperado | ✅/❌ |
|---|--------|-------|-------------------|------|
| 5.1 | Listado de descuentos | Navegar a `ventas/Descuentos` | Tabla con descuentos existentes | |
| 5.2 | Crear descuento (%) | Nuevo, tipo Porcentaje, valor 10 | Creado, aparece en tabla y en POS | |
| 5.3 | Crear descuento ($) | Nuevo, tipo Monto Fijo, valor 100 | Creado, aparece en tabla y en POS | |
| 5.4 | Editar descuento | Editar nombre, guardar | Datos actualizados | |
| 5.5 | Eliminar descuento | Eliminar, confirmar | Desaparece de lista | |
| 5.6 | Descuentos en POS | Abrir POS, revisar dropdown | Descuentos activos disponibles | |

---

## 6. Obras desde CRM

| # | Prueba | Pasos | Resultado Esperado | ✅/❌ |
|---|--------|-------|-------------------|------|
| 6.1 | Listado de obras | Navegar a `ventas/ObrasVentas` | Tabla con obras del sistema | |
| 6.2 | Ver detalle de obra | Click en ojo | Página de detalle con datos de la obra | |
| 6.3 | Generar factura de obra | Click en factura (si existe botón) | Factura generada para la obra | |
| 6.4 | Imprimir factura de obra | Ver vista de factura | Formato de factura legible | |

---

## 7. Facturación (Estado Actual)

| # | Verificación | Resultado | Notas |
|---|-------------|----------|-------|
| 7.1 | Tabla `facturas` existe | ✅ Sí | Creada por `facturacion_pos.sql` |
| 7.2 | Facturas simuladas se generan | ✅ Sí | Al crear orden en POS con cliente con RFC |
| 7.3 | UUID es simulado | ⚠️ Sí | Usa `mt_rand()`, no es fiscal |
| 7.4 | Vista de factura existe | ✅ Sí | `ventas/pos/factura.php` |
| 7.5 | Conexión con PAC real | ❌ No | Requiere integración con proveedor de timbrado |

---

## 8. Pruebas de Integración Cruzada

| # | Prueba | Módulos involucrados | Resultado Esperado | ✅/❌ |
|---|--------|---------------------|-------------------|------|
| 8.1 | Crear cliente → crear orden | Clientes + POS | Cliente aparece en selector del POS | |
| 8.2 | Cotización → confirmar → producción | POS + Órdenes + Producción | Confirmar genera solicitud de producción si no hay stock | |
| 8.3 | Orden entregada → descuenta stock | POS + Inventario | Stock del producto disminuye | |
| 8.4 | Pago registrado → saldo actualizado | Órdenes + Finanzas | Saldo pendiente decrementa, estatus pago cambia | |
| 8.5 | Obra vinculada → visible en detalle orden | Obras + Órdenes | Alerta amarilla en detalle de orden con link a obra | |
| 8.6 | Descuento creado → disponible en POS | Descuentos + POS | Aparece en dropdown de descuentos | |

---

## 9. Verificaciones de UI

| # | Elemento | Estado actual | Notas |
|---|---------|--------------|-------|
| 9.1 | Tabla clientes — ancho completo | 🆕 Corregido | `autoWidth: false`, layout con botones |
| 9.2 | Tabla órdenes — ancho completo | 🆕 Corregido | `autoWidth: false`, layout con botones |
| 9.3 | Botón "Mostrar X entradas" | 🆕 Corregido | Layout organizado con filas separadas |
| 9.4 | Botones Exportar Excel | 🆕 Agregado | En ambas tablas |
| 9.5 | Botones Imprimir | 🆕 Agregado | En ambas tablas |
| 9.6 | Botón "Plantilla Excel" clientes | 🆕 Agregado | Descarga plantilla para carga masiva |
| 9.7 | Botón "Carga masiva" clientes | 🆕 Agregado | Modal con upload y procesamiento |

---

## Resumen de Resultados

| Sección | Total pruebas | Aprobadas | Fallidas | Observaciones |
|---------|--------------|-----------|----------|---------------|
| 1. Clientes CRUD | 16 | | | |
| 2. Carga Masiva | 11 | | | 🆕 Nueva funcionalidad |
| 3. Órdenes | 12 | | | |
| 4. POS | 21 | | | |
| 5. Descuentos | 6 | | | |
| 6. Obras | 4 | | | |
| 7. Facturación | 5 verificaciones | | | |
| 8. Integración | 6 | | | |
| 9. UI | 7 verificaciones | | | |
| **TOTAL** | **88** | | | |

---

## Notas para el Cliente

1. **Facturación fiscal real (CFDI 4.0):** Actualmente el sistema emite facturas simuladas. Para facturación fiscal real se requiere contratar un PAC (Proveedor Autorizado de Certificación) como Facturama, Finkok, o SW Sapiense, e integrar su API.

2. **Carga masiva de clientes:** Use la plantilla Excel descargable desde el botón "Plantilla Excel". Los campos obligatorios son Razón Social y RFC. Los RFC duplicados se omiten automáticamente.

3. **Exportación a Excel:** Todas las tablas ahora incluyen botón de exportación a Excel con los datos visibles.

4. **Recibos:** Hay 3 formatos de recibo disponibles (Factura, Nota de Remisión, Moderno) accesibles desde el dropdown de cada orden.

5. **Control de inventario:** Las órdenes de mostrador descuentan stock automáticamente. Las órdenes de pedido generan solicitudes de producción si no hay stock suficiente.
