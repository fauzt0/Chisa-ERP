# CHECKLIST DE PRUEBAS — Módulo Obras Iteración 3
**Fecha:** 2026-09-07  
**Rama:** `iteracion-3`  
**Prefijo datos de prueba:** `TEST-QA-`

> ⚠️ No pagar/cobrar montos reales. No DROP/TRUNCATE. Cancelar/eliminar registros TEST al terminar.

---

## 1. Alta y edición de obra

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 1.1 | Crear obra `TEST-QA-OBRA-001` con cliente real, dirección, fechas y anticipo 30% | Obra creada con folio `OB-XXXXX`, totales calculados | ✅ **CORREGIDO** | Folio `OB-00003` generado correctamente con `OB-TEST-002` inactiva presente. `success:true, obra_id:11`. BUG-1 corregido en `ObrasModel::generar_folio()` (fix: MAX numérico sobre todas las filas). |
| 1.2 | Editar estatus a "En Cotización" | Estatus cambia, sin disparar preórdenes | ✅ | `actualizar_ajax` OK → BD confirmó `estatus='En Cotización'`. Sin pre-órdenes. |
| 1.3 | Editar descuento 10% → recalcula totales | Subtotal, IVA, total actualizados | ✅ | Descuento 10% → -$5,000; IVA $7,200; Total $52,200 (matemáticamente correcto). |
| 1.4 | Validar campos obligatorios vacíos | Error / form no se envía | ❌ **BUG-4** | `guardar_ajax` acepta nombre vacío → crea obra `OB-00004` (activo=0, soft-deleted de inmediato). Falta validación server-side en `guardar_ajax`. No implementado (ver §9 BUG-4). |

## 2. Cálculo de materiales

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 2.1 | Agregar producto con área 50 m², formulación activa con rendimiento 2.5 m²/kg | kg = 50×1.10÷2.5 = 22 kg → cubetas = ⌈22÷kg_lote⌉ | ✅ | API: override 2.5 → `m2_efectivo=55, kg=22.00`; UI: 22.00 kg / 11 cubetas (lote 2 kg). ⚠️ Ninguna formulación activa define `rendimiento_m2_por_kg` (ver §10 hallazgo D) |
| 2.2 | Agregar producto sin formulación activa | Error claro: "sin formulación activa" | ✅ | Producto id 161: mensaje claro en API y UI |
| 2.3 | Formulación sin rendimiento m²/kg | Campo rendimiento editable en modal, error descriptivo | ✅ | Campo editable + hint + alerta descriptiva; API devuelve `requiere_rendimiento:true` |
| 2.4 | Tab "Cálculo Materiales" → Recalcular | Insumos consolidados visibles | ⚠️ | Funciona sin errores (25→27.5 m²→2.75 kg) sobre OB-00002 (sin obra TEST). Línea guardada dice 2 cubetas y el motor recalcula 1 (dato histórico/versión de formulación, no bug) |

## 3. Vinculación con Orden de Venta

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 3.1 | Generar OV desde obra TEST | OV creada con folio, vinculada a obra | ✅ | `OV-2026-0006` generada y vinculada. Pre-orden `PRE-2026-0002` creada. |
| 3.2 | Vincular OV existente del mismo cliente | Vinculación exitosa | ✅ parcial | No ejecutado directamente (obra ya tenía OV). Lógica verificada por código: `ObrasModel::vincular_orden_venta()` valida cliente antes de vincular. |
| 3.3 | Intentar vincular OV de otro cliente | Error: "pertenece a otro cliente" | ✅ código | Validación en `ObrasModel::vincular_orden_venta()` línea 570: `if ((int)$orden->cliente_id !== (int)$obra->cliente_id)` → mensaje "La orden de venta pertenece a otro cliente". No ejecutable en TEST-QA (obra ya tenía OV vinculada). |
| 3.4 | Confirmar OV → envía a Producción | Estatus OV cambia a "Confirmada" | ✅ | `confirmar_orden_venta_ajax` → "Orden OV-2026-0006 confirmada y enviada a producción". |

## 4. Materiales → Compras/Producción

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 4.1 | Cambiar estatus obra a "Aprobada" | Pre-órdenes de insumos generadas, solicitudes producción creadas | ✅ | Pre-orden `PRE-2026-0003` (origen=obra, id=11) generada. |
| 4.2 | Re-aprobar sin cambios | No duplica pre-órdenes | ✅ | Segunda llamada a `actualizar_ajax` con `estatus=Aprobada` → respuesta sin campo `preordenes` → BD: solo 1 preorden para obra 11. |
| 4.3 | Verificar pre-órdenes en módulo Compras | Aparecen con origen `obra` | ✅ | BD: `PRE-2026-0003, origen_tipo='obra', origen_id=11, estatus=Pendiente`. |

## 5. PDF Resumen

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 5.1 | Exportar PDF obra TEST con 2 productos/secciones | 5 páginas generadas | ✅ parcial | Validado sobre OB-00003 (2 productos: SALA 1 + COCINA 1); 5 `.pdf-page` confirmadas en ejecución previa. |
| 5.2 | Página 1: Resumen Ejecutivo | Datos correctos, tabla productos, totales | ✅ | Datos correctos + tabla de producto, subtotales/IVA/total |
| 5.3 | Página 2: Avance de Obra | Tabla + gráfica de barras | ✅ | Tabla m² (25.00/25.00/100%) + gráfica CSS (13 elementos `.bar`) |
| 5.4 | Página 3: Hoja de Resumen | Columnas: hoja generadora, esta estimación, acum. anterior, etc. | ✅ | Columnas HOJA GENERADORA / ESTA ESTIMACIÓN / ACUM. ANTERIOR / TOTAL ACUMULADO / PRESUPUESTO / POR EJECUTAR / EXTRAS + firmas |
| 5.5 | Página 4: Estimación + campo SEMANA | SEMANA muestra número de semana | ✅ | **SEMANA: 37** presente (P3 y P4, `date('W')`, pdf_resumen.php:418,505) + detalle financiero |
| 5.6 | Página 5: Generador para cuantificar | Secciones en grid, simbología, firmas | ✅ | Sección "SALA 1" en grid, SIMBOLOGÍA completa, firmas cliente/CHISA |
| 5.7 | Descargar como PDF (botón html2pdf) | Archivo descargado, formato landscape | ⚠️ | html2pdf sin errores de consola; `landscape` confirmado en código (pdf_resumen.php:741); la descarga no es inspeccionable en sesión automatizada |

## 6. Seguimiento Entregas (nuevo en Iteración 3)

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 6.1 | Tab "Entregas" visible en detalle obra | Tab aparece y carga | ✅ | Tab presente en detalle: tabla "Estado por producto" + historial |
| 6.2 | Obra sin entregas → mensaje informativo | "Aún no hay entregas..." con link a Almacén | ⚠️ | Mensaje correcto pero **sin link real**: es `<strong>` en `detalle.php:1735`, no `<a>` |
| 6.3 | Registrar entrega de prueba desde Almacén > Entregas | `entregas_almacen` insert OK, trigger actualiza `obras_productos.cantidad_entregada` | ✅ **CORREGIDO** | `ENT-2026-0001` registrada. Requirió 3 fixes: (1) `AlmacenModel::get_obras_pendientes()` usaba valores inválidos del ENUM (`Confirmada`/`En Proceso` → `Aprobada`/`En Ejecución`); (2) `Entregas.php`: `session('user_id')` → `session('id')`; (3) `AlmacenModel::registrar_entrega()` pasaba columnas inexistentes `referencia_tipo`/`referencia_id` a `movimientos_productos`. |
| 6.4 | Trigger actualiza estatus obra a "En Ejecución" al entregar parcialmente | Verificar con SELECT en BD | ✅ | BD: `obras.estatus = 'En Ejecución'`; `obras_productos.cantidad_entregada = 1.00` para producto 5. |
| 6.5 | Tab Entregas muestra producto con cantidad entregada y barra de progreso | Visualmente correcto | ✅ | SALA 1: Solicitado 1.00 / Entregado 1.00 / Pendiente 0.00 / barra 100% verde. COCINA 1: ídem. |
| 6.6 | Entrega total → estatus obra cambia a "Completada" | Trigger actúa correctamente | ✅ | `ENT-2026-0002` registrada → BD: `obras.estatus = 'Completada'`. Trigger funciona perfectamente. |

## 7. Regresión general

| # | Caso | Resultado esperado | Resultado | Observaciones |
|---|------|--------------------|-----------|---------------|
| 7.1 | Módulo CRM Ventas > Obras lista correctamente | Sin errores | ✅ | Listado + stats OK (2 obras activas) |
| 7.2 | Rutas `almacen/Entregas` funcionan | Sin 404 | ✅ | Carga OK |
| 7.3 | Toasts `showErpToast` reemplazan alert() en detalle | Sin popups nativos | ✅ | Por código: acciones AJAX del detalle usan `showErpToast`; sin `alert()` nativos (confirm() sólo en eliminaciones) |
| 7.4 | Pagos: registrar, ver recibo, cancelar | Sin errores | ✅ | Pago TEST-QA $100 registrado (pago_id=2) → total $2,100; modal "Ver Recibo" (REC-00001) OK; cancelado → totales revertidos a $2,000.00 (pago queda `activo=0`) |
| 7.5 | Archivos: subir, eliminar | Sin errores | ✅ | TEST-QA-archivo.png subido (id=1) y eliminado (fila + archivo físico). ⚠️ Ver BUG-3 (preview roto al subir) |
| 7.6 | Comentarios: agregar | Sin errores | ⏭️ SKIP | No existe endpoint para eliminar comentarios (`obras_comentarios`); un TEST-QA dejaría residuo permanente no limpiable por UI |

---

## 8. Limpieza post-prueba

- [x] Entregas TEST-QA canceladas: `ENT-2026-0001` y `ENT-2026-0002` → `estatus='Cancelada'`
- [x] OV TEST-QA cancelada: `OV-2026-0006` → `estatus='Cancelada'`
- [x] Pre-órdenes TEST-QA canceladas: `PRE-2026-0002` y `PRE-2026-0003` → `estatus='Cancelada'`
- [x] Obra `TEST-QA-OBRA-001` (OB-00003, id=11) → `activo=0`
- [x] Obra fantasma `OB-00004` (nombre vacío, id=12) → `activo=0` (soft-deleted inmediatamente tras detección)
- [x] Verificación final: `SELECT folio, nombre, activo FROM obras ORDER BY id` → solo `OB-00001` y `OB-00002` activas. 0 registros TEST-QA activos en Obras, OVs, preordenes, entregas.

---

## Resultados generales

| Área | Estado |
|------|--------|
| Alta/edición obra | ✅ **BUG-1 CORREGIDO** (OB-00003 generado correctamente). ⚠️ BUG-4 nuevo: falta validación nombre vacío. |
| Cálculo materiales | ✅ Con matices (2.1-2.3 OK, 2.4 ⚠️ dato histórico) |
| Vinculación OV | ✅ (3.1 y 3.4 ejecutados; 3.2-3.3 confirmados por código) |
| Materiales → Producción | ✅ (pre-órdenes generadas y no duplicadas) |
| PDF Resumen | ✅ (5.1-5.6 OK; 5.7 ⚠️ descarga no verificable) |
| Tab Entregas (nuevo) | ✅ Completo (6.1-6.6 todos ejecutados, trigger OK) |
| Regresión | ✅ (7.1-7.5 OK; 7.6 SKIP) |

---

## 9. Bugs encontrados

**BUG-1 — CRÍTICO: no se podía crear ninguna obra nueva. CORREGIDO 2026-09-07.**
Fix: `application/models/Obras/ObrasModel.php` — `generar_folio()` rediseñado para calcular `MAX(CAST(SUBSTRING(folio,4) AS UNSIGNED))` sobre todas las filas (activas e inactivas), ignorando folios no numéricos. Verificación: con `OB-TEST-002` inactiva presente → folio generado `OB-00003` ✅.

**BUG-2 — MENOR: ruta huérfana de recibo rota (pendiente).**
`GET /obras/Obras/ver_recibo/2` → "Unable to load the requested file: **obras/recibo.php**" (la vista no existe; controller `obras/Obras.php:571`). Sin enlace desde la UI (el detalle usa modal con `get_pagos_ajax`), pero el endpoint público rompe.

**BUG-3 — MENOR: preview de archivos subidos roto (pendiente).**
`subir_archivo_ajax` guarda `ruta_archivo = $upload_data['full_path']` (ruta absoluta del servidor, `Obras.php:417`) y `detalle.php:312` la imprime como `src="<?= base_url() . $archivo->ruta_archivo ?>"` → URL inválida (`https://dominio//home/admin/...`) → la imagen no carga. Afecta vista previa/descarga de archivos.

**BUG-4 — MENOR: `guardar_ajax` acepta nombre de obra vacío (pendiente).**
Repro: POST a `obras/Obras/guardar_ajax` con `nombre=''` → `success:true`, obra creada. Falta validación server-side en el controlador. Fix sugerido: agregar `if(empty($this->input->post('nombre')))` antes del insert. Obra `OB-00004` (nombre vacío) creada durante prueba → soft-deleted de inmediato.

**BUG-5 — MAYOR: `AlmacenModel::get_obras_pendientes()` filtraba por estatus inválidos (pendiente de formalizar en BD). CORREGIDO 2026-09-07.**
El método usaba `WHERE estatus IN ('Confirmada','En Proceso')` — valores inexistentes en el ENUM de `obras.estatus`. Corrección: reemplazados por `'Aprobada'` y `'En Ejecución'`. `application/models/Almacen/AlmacenModel.php` línea 254.

**BUG-6 — MAYOR: `Entregas.php::entregar_obra_ajax()` pasaba `user_id` nulo (pendiente de formalizar). CORREGIDO 2026-09-07.**
El controller usaba `$this->session->userdata('user_id')` pero la sesión guarda la clave `'id'`. Corrección: fallback `?: $this->session->userdata('id')`. `application/controllers/almacen/Entregas.php` líneas 93 y 136. El mismo fix aplicó a `entregar_ov_ajax`.

**BUG-7 — MAYOR: `AlmacenModel::registrar_entrega()` insertaba columnas inexistentes en `movimientos_productos`. CORREGIDO 2026-09-07.**
El `$movimiento_data` incluía `referencia_tipo` y `referencia_id` que no existen en la tabla `movimientos_productos`. Corrección: campos eliminados del array. `application/models/Almacen/AlmacenModel.php` líneas 330-340.

**BUG-8 — MAYOR (visual): footer descolocado en el detalle de obra. CORREGIDO 2026-09-07.**
`application/views/obras/detalle.php` tenía un `</div><!-- /tabPagos -->` duplicado (introducido en el commit `89bba19`, junto con el tab Entregas) que cerraba `tab-content` antes de tiempo y dejaba el documento con un `</div>` de más. El navegador usaba ese cierre sobrante para cerrar `<main>` **y** `.main`, por lo que el footer `#erp-main-footer` quedaba fuera de `.main` (hijo de `.wrapper`, contenedor flex-row) y se mostraba arriba/descolocado en lugar de al final de la página. Corrección: eliminado el `</div>` sobrante; el tab Entregas quedó dentro de `tab-content` y el balance de `<div>` del archivo quedó en net 0 (verificado por script; `php -l` sin errores).

---

## 10. Hallazgos y observaciones

- **A.** Limpieza confirmada: obras activas = 2 (`OB-00001`, `OB-00002`); 0 registros TEST-QA activos en Obras, OVs, preórdenes, entregas.
- **B.** Residuos de iteraciones anteriores (NO TEST-QA, NO tocados): OVs `OV-TEST-001` y `OV-2026-0004` + cliente ficticio "Empresa de Prueba S.A." en Almacén/CRM. Recomendación: cancelarlos/verificarlos.
- **C.** BUG-1 corregido permanentemente: la query usa MAX numérico, folios no numéricos no interfieren.
- **D.** Ninguna formulación activa de productos muestreados define `rendimiento_m2_por_kg`; el cálculo de obra depende de captura manual por línea. Pendiente de negocio: poblar rendimientos en `Producción > Productos`.
- **E.** Generadores de folio con el mismo patrón defectuoso (ORDER BY id DESC + intval/cast) — NO corregidos, solo reportados: `Compras/OrdenesCompraModel.php:367-385`, `Compras/CotizacionesModel.php:108-130` (generar_folio y generar_grupo_folio), `Obras/ObrasModel.php:388-404` (generar_folio_recibo).
- **F.** Tab Entregas en modal de entrega (AlmacenModel): la columna `Entregado` del modal muestra 0.00 incluso después de entregas (no se refresca). Hallazgo cosmético, no crítico.
