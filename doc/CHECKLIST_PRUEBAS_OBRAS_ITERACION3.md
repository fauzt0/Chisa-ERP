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
| 1.4 | Validar campos obligatorios vacíos | Error / form no se envía | ✅ **CORREGIDO Y RE-EJECUTADO 2026-09-10** | Al probar, `guardar_ajax` aceptó nombre vacío → creó la obra `OB-00004` (soft-deleted de inmediato). Validación agregada el 2026-09-07 (server-side en `guardar_ajax` + client-side en `guardarObra()`). **Re-ejecutado el 2026-09-10** con 4 escenarios (ver §11.3): nombre vacío, nombre solo espacios, cliente 0 → los tres rechazados con `success:false`; control positivo → obra creada OK (`OB-00005`, soft-deleted). Sin filas nuevas tras los casos negativos. |

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
| 7.5 | Archivos: subir, eliminar | Sin errores | ✅ | TEST-QA-archivo.png subido (id=1) y eliminado (fila + archivo físico). BUG-3 (preview roto al subir) corregido el 2026-09-07 (ver §9 BUG-3). |
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
| Alta/edición obra | ✅ **BUG-1 y BUG-4 CORREGIDOS** (OB-00003 generado correctamente; validación de nombre/cliente agregada en `guardar_ajax`). |
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

**BUG-2 — MENOR: ruta huérfana de recibo rota. CORREGIDO 2026-09-07.**
`GET /obras/Obras/ver_recibo/2` → "Unable to load the requested file: **obras/recibo.php**" (la vista no existía; controller `obras/Obras.php:571`). Sin enlace desde la UI (el detalle usa modal con `get_pagos_ajax`), pero el endpoint público rompía.
Fix: creada `application/views/obras/recibo.php` (fragmento autocontenido, balance `<div>` 0) y `ver_recibo()` ahora renderiza con el layout `layouts/general_template` (`viewData['pageTitle'|'pageView'|'pago']`). Commit `dad4cd3`. Verificado: `php -l` OK y `get_pago()` devuelve todos los campos usados por la vista.

**BUG-3 — MENOR: preview de archivos subidos roto. CORREGIDO 2026-09-07.**
`subir_archivo_ajax` guardaba `ruta_archivo = $upload_data['full_path']` (ruta absoluta del servidor, `Obras.php:417`) y `detalle.php:312` la imprimía como `src="<?= base_url() . $archivo->ruta_archivo ?>"` → URL inválida (`https://dominio//home/admin/...`) → la imagen no cargaba.
Fix: `subir_archivo_ajax()` ahora guarda la ruta relativa `uploads/obras/{obra_id}/{file_name}`; el preview de `detalle.php` normaliza rutas legacy absolutas (strip de `FCPATH`) y `ObrasModel::eliminar_archivo()` resuelve la ruta física antes de `unlink()`. Commit `dad4cd3`. No se migraron registros viejos (el manejo legacy los cubre).

**BUG-4 — MENOR: `guardar_ajax` acepta nombre de obra vacío. CORREGIDO 2026-09-07 — RE-VERIFICADO 2026-09-10.**
Repro: POST a `obras/Obras/guardar_ajax` con `nombre=''` → `success:true`, obra creada. Obra `OB-00004` (nombre vacío) creada durante prueba → soft-deleted de inmediato.
Fix: validación server-side en `guardar_ajax()` (`trim()` del nombre y `cliente_id > 0`, responde `success:false` con mensaje) + validación client-side en `guardarObra()` (`index.php`). Commit `dad4cd3`. **Re-ejecutado el 2026-09-10** con 4 escenarios (incluido control positivo), ver §11.3: nombre vacío y solo espacios → `success:false` "El nombre de la obra es obligatorio"; `cliente_id=0` → `success:false` "Debe seleccionar un cliente"; alta válida → `success:true` con folio `OB-00005`.

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
- **E.** Generadores de folio con el mismo patrón defectuoso (ORDER BY id DESC + intval/cast) — **CORREGIDOS 2026-09-07** con `MAX(CAST(SUBSTRING(...) AS UNSIGNED))` (mismo enfoque del BUG-1), commit `dad4cd3`: `Compras/OrdenesCompraModel.php::generar_folio()`, `Compras/CotizacionesModel.php::generar_folio()` y `::generar_grupo_folio()`, `Obras/ObrasModel.php::generar_folio_recibo()`.
- **F.** *(CORREGIDO 2026-09-10)* Modal de entrega de obra (`almacen/Entregas`): las columnas **Pedido / Pendiente / A Entregar** se calculaban con `op.cantidad_ajustada`, que es **NULL** cuando la línea de obra nunca se ajustó manualmente (caso normal: solo existe `cantidad_calculada`). Sin `COALESCE`, la API devolvía `cantidad = null` y `pendiente_entregar = null`, y el JS del modal imprimía `null` y calculaba `Math.min(null, stock)` → **input "A Entregar" en 0/negativo, impidiendo registrar la entrega**. Causa raíz confirmada sobre la obra 11 (2 filas con `cantidad_ajustada = NULL`, `cantidad_entregada = 1.00`). Fix: `AlmacenModel::get_obra_detalle()` y `::get_obras_pendientes()` ahora usan `COALESCE(op.cantidad_ajustada, op.cantidad_calculada, 0)` (mismo criterio que ya usaba el tab Entregas del detalle de obra); `get_orden_detalle()` con `COALESCE(dov.cantidad_entregada, 0)`; y en `views/almacen/entregas/main.php` se añadió el helper `fmtCantidad()` + clamp a ≥0 del máximo/valor del input en ambos modales (obra y OV).
- **G.** *(2026-09-10)* Correcciones de BUG-2/3/4 y del hallazgo E integradas en `iteracion-3` (commit `dad4cd3`, pusheado a `origin`). El fix del dashboard del prompt de pendientes (T5) fue superado por la reescritura del dashboard del agente cloud, integrada en el merge `f008279`. Pendiente: validación manual de UI post-merge (dashboard por permisos, toggle de tema, login) y merge de `iteracion-3` → `main`.
- **H.** *(2026-09-10)* `Obras::guardar_ajax()` valida `nombre` y `cliente_id`, pero **no** valida `direccion`: si se envía el POST sin ese campo (el formulario lo marca `required`, por lo que no ocurre desde la UI), la inserción falla con `Error Number: 1048 Column 'direccion' cannot be null`. Mejora sugerida para iteración 4: validar también `direccion` server-side y responder `success:false` en lugar de propagar el error de BD.

---

## 11. Pase previo a la validación manual *(2026-09-10)*

Objetivo: dejar la rama lista para **una sola** pasada de validación manual. Se cerraron los dos cosméticos pendientes, se re-ejecutó el caso 1.4 y se hizo un smoke test de rutas post-merge. **No se agregaron funcionalidades nuevas.**

### 11.1 Cambios aplicados

| # | Cambio | Archivo | Cómo se verificó |
|---|--------|---------|------------------|
| 1 | El mensaje "Aún no hay entregas registradas…" del tab Entregas ahora es un **link real** a `almacen/Entregas` (antes era un `<strong>`) | `application/views/obras/detalle.php` (~línea 1739) | Render del detalle: contiene `href="https://erp.chisarecubrimientos.com.mx/almacen/Entregas"`; `php -l` OK |
| 2 | **Hallazgo F** — `COALESCE(cantidad_ajustada, cantidad_calculada, 0)` en `get_obra_detalle()` y `get_obras_pendientes()`; `COALESCE(cantidad_entregada, 0)` en `get_orden_detalle()` | `application/models/Almacen/AlmacenModel.php` | Consulta replicada antes/después: `cantidad` pasó de `NULL` a `1.00` y `pendiente_entregar` de `NULL` a `0.00` |
| 3 | **Hallazgo F** (front) — helper `fmtCantidad()` + clamp a ≥ 0 del `max`/`value` del input "A Entregar" en los modales de obra y de OV | `application/views/almacen/entregas/main.php` | `node --check` sobre el JS extraído: OK; el HTML servido incluye `fmtCantidad` |

> Contexto del hallazgo F: el modal usaba `cantidad_ajustada`, que es NULL cuando la línea de obra nunca se ajustó a mano (solo tiene `cantidad_calculada`, que es el caso normal). Eso producía celdas `null` y un input en 0, bloqueando la entrega. El tab Entregas del detalle de obra ya usaba el criterio `COALESCE(ajustada, calculada)`; ahora ambos coinciden.

### 11.2 Smoke test post-merge (rec. 4)

Ejecutado con `curl` sobre `https://erp.chisarecubrimientos.com.mx` (usuario `presentacion@chisa.mx`) + render por CLI de las vistas del módulo.

| Ruta / Recurso | Método | HTTP | Resultado |
|---|---|---|---|
| `/` (login) y `POST /authenticate` | GET/POST | 200 / 303 | Login OK, sesión creada (sin 2FA: el entorno resuelve a `development`) |
| `/dashboard` | GET | 200 (118 KB) | Sin errores PHP |
| `/almacen/Entregas` | GET | 200 (72 KB) | Sin errores PHP |
| `/obras/Obras` | GET | 307 → `/deny` | **No es un bug**: el usuario demo no tiene permisos del módulo Obras (ver §11.4) |
| `/obras/Obras/detalle/2` | GET | 307 → `/deny` | Ídem |
| `/ventas/ObrasVentas` | GET | 307 → `/deny` | Ídem |
| `obras/Obras`, `ventas/ObrasVentas`, `obras/Obras/detalle/1`, `almacen/Entregas` | CLI | — | Render completo (97 KB / 83 KB / 171 KB / 96 KB) sin `Fatal error`, `Parse error` ni `Uncaught` |
| `/assets/dist/js/theme-toggle.js`, `/assets/dist/css/theme.css` | GET | 200 | Assets del merge disponibles |
| `general_template` → `theme-toggle.js` | — | — | Incluido en el layout |
| `almacen/Entregas/get_obra_detalle_ajax` (obra 11 y 2) | POST | 200 | `success:true`; obra 11 ya devuelve `cantidad:1.00 / pendiente:0.00` (antes `null`) |

Errores PHP detectados en las rutas alcanzables: **0**.

### 11.3 Caso 1.4 re-ejecutado (BUG-4)

Ejecutado por CLI (`php index.php obras/Obras/guardar_ajax`; `MY_Controller` omite sesión/permisos en CLI, por lo que se prueba exactamente el mismo código de validación del endpoint). Snapshot de BD: `obras_total=5, max_id=12, activas=2` antes y después de los casos negativos.

| Escenario | Payload | Respuesta del endpoint | ¿Creó obra? |
|---|---|---|---|
| 1.4a nombre vacío | `nombre="", cliente_id=1` | `{"success":false,"message":"El nombre de la obra es obligatorio"}` | No |
| 1.4b nombre solo espacios | `nombre="   ", cliente_id=1` | `{"success":false,"message":"El nombre de la obra es obligatorio"}` | No |
| 1.4c sin cliente | `nombre="TEST-QA-VALIDACION-BUG4", cliente_id=0` | `{"success":false,"message":"Debe seleccionar un cliente"}` | No |
| 1.4d control positivo | `nombre="TEST-QA-VALIDACION-BUG4", cliente_id=1, direccion=...` | `{"success":true,"message":"Obra creada correctamente","obra_id":13}` → folio `OB-00005` | Sí (limpiada) |

El control positivo confirma que la validación no bloquea el alta legítima y que el folio sigue generándose correctamente (BUG-1).

**Limpieza:** obra `OB-00005` (id 13, `TEST-QA-VALIDACION-BUG4`) → `activo=0`; sin filas dependientes (`obras_productos/pagos/archivos/comentarios` = 0, preórdenes = 0). Estado final: 2 obras activas (`OB-00001`, `OB-00002`).

### 11.4 Notas para la validación manual

1. **El usuario demo `presentacion@chisa.mx` NO tiene permisos del módulo Obras** (revisado en `privilege`: sus permisos son de compras/proveedores/RH). Para validar Obras hay que entrar con una cuenta con permisos de Obras: ids **1** (`soporte2@especialistasweb.com.mx`), **6** (`ggeneral@chisarecubrimientos.com.mx`) o **7** (`facturacion@chisarecubrimientos.com.mx`).
2. El servidor resuelve `ENVIRONMENT = development` (no hay `CI_ENV` en nginx ni en `.htaccess`), así que el login **no pide 2FA** y los errores de PHP se muestran en pantalla. Conviene decidirlo explícitamente antes de la validación: si se espera probar 2FA, hay que ejecutar con `CI_ENV=production`.
3. Pendiente de la validación manual: dashboard por permisos, toggle de tema, login y el resto de bloques A–G de `doc/CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md`; después, PR de `iteracion-3` → `main`.
