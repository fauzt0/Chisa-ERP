# CHECKLIST DE PRUEBAS — Módulo Obras Iteración 3
**Fecha:** 2026-09-07  
**Rama:** `iteracion-3`  
**Prefijo datos de prueba:** `TEST-QA-`

> ⚠️ No pagar/cobrar montos reales. No DROP/TRUNCATE. Cancelar/eliminar registros TEST al terminar.

---

## 1. Alta y edición de obra

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 1.1 | Crear obra `TEST-QA-OBRA-001` con cliente real, dirección, fechas y anticipo 30% | Obra creada con folio `OB-XXXXX`, totales calculados | | |
| 1.2 | Editar estatus a "En Cotización" | Estatus cambia, sin disparar preórdenes | | |
| 1.3 | Editar descuento 10% → recalcula totales | Subtotal, IVA, total actualizados | | |
| 1.4 | Validar campos obligatorios vacíos | Error / form no se envía | | |

## 2. Cálculo de materiales

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 2.1 | Agregar producto con área 50 m², formulación activa con rendimiento 2.5 m²/kg | kg = 50×1.10÷2.5 = 22 kg → cubetas = ⌈22÷kg_lote⌉ | | |
| 2.2 | Agregar producto sin formulación activa | Error claro: "sin formulación activa" | | |
| 2.3 | Formulación sin rendimiento m²/kg | Campo rendimiento editable en modal, error descriptivo | | |
| 2.4 | Tab "Cálculo Materiales" → Recalcular | Insumos consolidados visibles | | |

## 3. Vinculación con Orden de Venta

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 3.1 | Generar OV desde obra TEST | OV creada con folio, vinculada a obra | | |
| 3.2 | Vincular OV existente del mismo cliente | Vinculación exitosa | | |
| 3.3 | Intentar vincular OV de otro cliente | Error: "pertenece a otro cliente" | | |
| 3.4 | Confirmar OV → envía a Producción | Estatus OV cambia a "Confirmada" | | |

## 4. Materiales → Compras/Producción

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 4.1 | Cambiar estatus obra a "Aprobada" | Pre-órdenes de insumos generadas, solicitudes producción creadas | | |
| 4.2 | Re-aprobar sin cambios | No duplica pre-órdenes | | |
| 4.3 | Verificar pre-órdenes en módulo Compras | Aparecen con origen `obra` | | |

## 5. PDF Resumen

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 5.1 | Exportar PDF obra TEST con 2 productos/secciones | 5 páginas generadas | | |
| 5.2 | Página 1: Resumen Ejecutivo | Datos correctos, tabla productos, totales | | |
| 5.3 | Página 2: Avance de Obra | Tabla + gráfica de barras | | |
| 5.4 | Página 3: Hoja de Resumen | Columnas: hoja generadora, esta estimación, acum. anterior, etc. | | |
| 5.5 | Página 4: Estimación + campo SEMANA | SEMANA muestra número de semana | | |
| 5.6 | Página 5: Generador para cuantificar | Secciones en grid, simbología, firmas | | |
| 5.7 | Descargar como PDF (botón html2pdf) | Archivo descargado, formato landscape | | |

## 6. Seguimiento Entregas (nuevo en Iteración 3)

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 6.1 | Tab "Entregas" visible en detalle obra | Tab aparece y carga | | |
| 6.2 | Obra sin entregas → mensaje informativo | "Aún no hay entregas..." con link a Almacén | | |
| 6.3 | Registrar entrega de prueba desde Almacén > Entregas | `entregas_almacen` insert OK, trigger actualiza `obras_productos.cantidad_entregada` | | |
| 6.4 | Trigger actualiza estatus obra a "En Ejecución" al entregar parcialmente | Verificar con SELECT en BD | | |
| 6.5 | Tab Entregas muestra producto con cantidad entregada y barra de progreso | Visualmente correcto | | |
| 6.6 | Entrega total → estatus obra cambia a "Completada" | Trigger actúa correctamente | | |

## 7. Regresión general

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 7.1 | Módulo CRM Ventas > Obras lista correctamente | Sin errores | | |
| 7.2 | Rutas `almacen/Entregas` funcionan | Sin 404 | | |
| 7.3 | Toasts `showErpToast` reemplazan alert() en detalle | Sin popups nativos | | |
| 7.4 | Pagos: registrar, ver recibo, cancelar | Sin errores | | |
| 7.5 | Archivos: subir, eliminar | Sin errores | | |
| 7.6 | Comentarios: agregar | Sin errores | | |

---

## 8. Limpieza post-prueba

- [ ] Eliminar (soft-delete) obra `TEST-QA-OBRA-001`
- [ ] Cancelar pre-órdenes `TEST-QA-*` generadas
- [ ] Cancelar entrega de prueba en Almacén
- [ ] Verificar que no quedaron registros activos con prefijo TEST-QA

---

## Resultados generales

| Área | Estado |
|------|--------|
| Alta/edición obra | ⬜ Pendiente de probar |
| Cálculo materiales | ⬜ Pendiente de probar |
| Vinculación OV | ⬜ Pendiente de probar |
| Materiales → Producción | ⬜ Pendiente de probar |
| PDF Resumen | ⬜ Pendiente de probar |
| Tab Entregas (nuevo) | ⬜ Pendiente de probar |
| Regresión | ⬜ Pendiente de probar |
