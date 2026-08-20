# BRIEF DE AUDITORÍA — Overhaul módulo Obras y Producción

ERP Chisa Recubrimientos · CodeIgniter 3 · PHP 8.x · MySQL/MariaDB

- **Workspace:** `/home/admin/domains/erp.chisarecubrimientos.com.mx`
- **Código:** `public_html/` (`private_html` es SYMLINK a `public_html`; no hay copia duplicada)
- **Estado:** SISTEMA EN PRODUCCIÓN. No `DROP`/`TRUNCATE`. No git commit/push salvo que el usuario lo pida.

**Fecha de cierre del overhaul:** 2026-08-20  
**Implementación:** Composer 2.5 (fases P5–P9) + Grok (P1–P4 y UI formulaciones P3)  
**Auditoría iterativa:** Grok como orquestador; P5 y P6 se rechazaron una vez y se rehicieron (P5.1, P6.1).

Este documento es un **handoff para otro auditor**. No es la fuente de verdad: contrastar contra el código y contra:

- `public_html/doc/DOCUMENTACION_TECNICA.md`
- `public_html/doc/cotizacion.md`

---

## 1. Objetivo de negocio (pedido original del cliente)

Elevar “obras y producción” a nivel profesional:

- Entrenamiento completo con Excel: `public_html/doc/BASES ORGANICAS Y TINTAS.xls`, `CHISA GLASS 2021.xls`, `FICHAS_CHISA_GLASS_2014.xls`
- Lineamientos: `DOCUMENTACION_TECNICA.md` y `cotizacion.md` (módulos 4 Compras, 6 Ventas, 7 Cálculo de materiales, 8 Producción, 9 Almacén)
- Dashboard de planta: órdenes de venta, alertas visuales y sonoras
- Ver si hay insumos suficientes; si no, preorden a Compras (autorización, no OC automática)
- Consultar formulaciones (histórico, cliente, palabras clave, nombres secundarios/alias)
- Actualizar existencias de insumos al fabricar (pesaje en báscula)
- Etiqueta de lote: código de barras + logo + tamaño ajustable; al escanear: producto, cubeta, lote, venta
- Cálculo de materiales en obras: m² → kg → insumos

---

## 2. Estándares que el auditor debe exigir

`DOCUMENTACION_TECNICA.md`:

- Controladores heredan `MY_Controller`; `$modulo` para permisos
- Modelos heredan `MY_Model`; soft delete
- **Separación:** SQL y negocio en modelo; controlador = HTTP / validación / JSON
- DataTables server-side (`draw` / `recordsTotal` / `recordsFiltered` / `data`)
- Costos: `produccion_ver_costos` + `puede_ver_costos()` / `ocultar_costo()`
- Permisos: `tiene_permiso('...')`; no inventar permisos
- CSRF en POST AJAX
- Columnas **ya existían** (NO re-migrar): `formulaciones.cliente_id`, `comentarios`, `referencia_cliente`, `rendimiento_m2_por_kg`; `detalle_formulacion.grupo_color`, `porcentaje_fase_acuosa`, `kg_fase_acuosa`

Infra a **reutilizar** (no duplicar):

- `ProductosModel::explotar_bom_arbol` / `explotar_bom_plano`
- `ProductosModel::calcular_insumos_para_proyecto` (**OJO:** si falta rendimiento usa fallback `1.0` — prohibido a ciegas en Obras)
- Helper `convertir_unidad_insumo` / `unidad_familia` (`application/helpers/unidades_helper.php`)
- `Compras/PreordenesModel::crear_preordenes_desde_faltantes` (dedupe Pendiente por insumo + `origen_tipo` + `origen_id`)
- ENUM `preordenes.origen_tipo`: `venta` | `obra` | `interno` | `produccion`
- Trigger `trg_stock_insumos_movimiento` ON `movimientos_inventario` INSERT

---

## 3. Qué se hizo, fase por fase

### P1 — Entrenamiento Excel (completado, Grok)

Import CLI `Productos::importar_archivo_cli` lee `IMPORT_FILE` (CI3 parte paths con espacios).

Parser: fallback `total_kg`, kg derivado de %, filtro de fórmulas malformadas (over-capture de hojas con muchos colores).

CHISA GLASS 2021 importado con dedup; `referencia_cliente` poblada; formulaciones de una sola versión auto-activadas.

Excel histórico no siempre tiene % coherente al 100% (datos reales de planta).

### P2 — Búsqueda avanzada (completado, Grok)

`ProductosModel::_aplicar_busqueda_tokens` (AND por palabra).

Campos: `p.nombre`, `p.codigo`, `p.alias`, `f.nombre_version`, `f.referencia_cliente`, `f.comentarios`, `f.descripcion`, `c.razon_social`, `c.nombre_comercial`.

`get_insumos_select_ajax`: alias + tabla `insumos_alias` + campo `buscar`.

### P3 — UI formulaciones (completado, Grok) — bug crítico

**Antes:** `guardarFormulacion()` siempre llamaba `crear_formulacion_ajax` (siempre versión nueva, N POSTs, race).

**Ahora:**

- `ProductosModel::guardar_formulacion_completa($cabecera, $componentes, $modo, $id)` transaccional
- `Productos::guardar_formulacion_completa_ajax`
- Modos: `actualizar` (sobrescribe versión abierta) | `nueva` (histórico)

UI: `cliente_id`, `referencia_cliente`, `comentarios`, `rendimiento_m2_por_kg`, `grupo_color`, semáforo de % ~100%, picker de insumos con filtro por alias.

- JS: `public_html/assets/dist/js/produccion_productos.js` (`guardarFormulacion`, `cargarInsumosSelect`, `renderizarComponentes`)
- Vista modal: `views/produccion/productos/modals/modales.php`
- Botones: “Actualizar versión actual” vs “Guardar como nueva versión”

### P4 — Dashboard planta alertas (completado, Grok)

`views/produccion/dashboard/main.php` (aprox. L984–1100)

- Poll 20 s → `produccion/Dashboard/check_nuevas_ordenes_ajax`
- Web Audio (sin mp3), toast Bootstrap, badge `#badge-nuevas-pedidos`, mute en `localStorage` `prod_sonido`
- `AudioContext` requiere gesto del usuario (click/touch)

### P5 — Verificación de insumos + preórdenes (completado tras P5.1)

Motor:

- `ProductosModel::verificar_disponibilidad_insumos_para_orden($origen_id, $tipo)`
- `::consultar_verificacion_insumos()` → UI, **sin** preorden
- `::procesar_verificacion_insumos_post_creacion()` → crea preórdenes
- `::_resolver_total_bom_linea()` — Cubeta/Pza → `cantidad × lote`; Kg/g → kg directo; L/ml volumen; Galón/m² → `revision_manual`
- `VentasModel::consultar_insumos_venta` / `verificar_insumos_y_preordenes_venta`
- `ObrasModel::consultar_insumos_obra` / `verificar_insumos_y_preordenes_obra`

**Regla de disparo** (la primera entrega de P5 se rechazó):

| Evento | ¿Preorden? |
|---|---|
| POS Cotización | NO (solo consultar) |
| POS Confirmada / En Preparación / Entregada | SÍ |
| `Ordenes/confirmar_ajax` | SÍ, una sola vez (`confirmar_orden` devuelve `{success, insumos}`) |
| Obra: agregar producto | NO (consultar) |
| Obra → Aprobada | SÍ `origen=obra` |
| Obra → generar OV | SÍ `origen=venta` **solo si** la obra **no** tiene ya preórdenes Pendiente `origen=obra` |

Bugs que P5.1 corrigió (verificar que no hayan vuelto):

1. Cotización/borrador **no** debe crear preórdenes
2. Escalado **no** es siempre `cantidad × cantidad_producida` (461 fabricados se venden en Kg; 3 en Cubeta)
3. `confirmar_orden` + `confirmar_ajax` no deben explotar BOM dos veces

P5 **no** descuenta stock de insumos.

### P6 — Pesaje y descuento de insumos (completado tras P6.1)

`ProduccionModel::confirmar_pesaje_y_descontar`

- Insert `movimientos_inventario` tipo `Salida`, referencia `PESAJE-{venta|obra}-{id}`
- Trigger actualiza `insumos.stock_actual`
- Merma máx +20% salvo permiso `produccion_ordenes`
- `insumos_ya_consumidos()` bloquea segundo pesaje
- Completar lote (`procesar_inventario_por_produccion`) **solo** entra producto terminado (`movimientos_productos`), **no** insumos
- POS Entregada: `VentasModel::entregar_orden` descuenta `productos.stock_actual`; movimiento en `movimientos_inventario` con `producto_id` (tabla históricamente mixta)

`puede_completar_produccion()` — orden obligatorio:

1. Forzar **solo** si flag true real + `produccion_ordenes`
2. Si `insumos_ya_consumidos` → OK (no revalidar stock ya descontado)
3. Sin productos / sin insumos → OK
4. Faltantes o `revision_manual` → bloquear
5. Stock OK sin pesaje → “confirme el pesaje”

Bugs que P6.1 corrigió (**crítico** verificar):

- Nunca `$forzar = (bool)$this->input->post(...)` porque `(bool)"0" === true`
- Debe ser: `in_array(..., ['1', 1, 'true', true], true)` → `Dashboard.php` `actualizar_estatus_ajax`
- UI `detalle.php`: forzar solo si `bloqueada && !consumido`

Endpoints: `produccion/Dashboard/confirmar_pesaje_ajax`, `verificar_stock_ajax`  
Vista: `views/produccion/dashboard/detalle.php`  
Permisos existentes: `produccion_ordenes`, `produccion_preordenes` (no inventados)

### P7 — Etiquetas de lote (completado)

- Modelo: `get_lote_etiqueta_datos`, `consultar_lote_por_codigo_barras`, `_normalizar_datos_etiqueta_lote`
- Vista impresión: `views/produccion/dashboard/etiqueta_lote.php`
- Consulta scan: `views/produccion/lotes/consultar.php` — URL `/produccion/Lotes/consultar`
- Endpoints:
  - `GET /produccion/Dashboard/etiqueta_lote/{id}?size=100x50&zoom=100`
  - `POST /produccion/Lotes/consultar_lote_ajax` (CSRF) y `Dashboard/consultar_lote_ajax`
- Logo real: `assets/dist/img/brands/chisa_recubrimientos_logo.jpg` (el del sidebar, ~27 KB)
- Presets: 50×25 y 100×50 mm; zoom 50–200 solo preview; `localStorage` `chisa_etiqueta_prefs_size` / `chisa_etiqueta_prefs_zoom`
- Datos: producto, código, lote, cubeta/cantidad, formulación, folio OV/obra, fecha
- Sidebar + almacén/inventario enlazan a consultar
- `historial_ventas`: revisado, campos coinciden con `ordenes_venta`; sin cambio

Residuos P7: JsBarcode por CDN jsdelivr (falla sin internet en planta). `@page size` con CSS variables no es universal en térmicas.

### P8 — Cálculo de materiales en obras m² → kg (completado)

- `ObrasModel::calcular_materiales_linea_obra` / `calcular_materiales_obra` / `preparar_linea_producto_obra`
- `Obras.php`: `calcular_materiales_ajax`, `materiales_obra_ajax`; `agregar_producto_ajax` recalcula en servidor
- Vista: `views/obras/detalle.php` (modal preview + tab Cálculo Materiales)

Fórmula:

```
m2_efectivos = m2 × factor_desperdicio   (mín 1.0; default UI 1.10)
kg           = m2_efectivos / rendimiento_m2_por_kg
               (formulación; si falta, override de línea;
                SI AMBOS VACÍOS → requiere_rendimiento, NO fallback 1.0)
cubetas      = ceil(kg / cantidad_producida)
insumos      = calcular_insumos_para_proyecto(formulacion_id, cubetas, null)
               // tercer arg null = no pasar m²
```

Validación numérica BD formulación **319 MASA ROCA**: rendimiento 0.336 m²/kg, lote 20 kg  
100 m² → 297.62 kg → 15 cubetas; MARMOLINA 11.3×15 = 169.5 kg (coincide)

Si la formulación **ya tiene** rendimiento, el campo de línea **no lo pisa** (es fallback, no override).  
`agregar_producto` exige m². No crea preórdenes (P5 al aprobar).

### P9 — Pruebas (completado como code + SELECT, no E2E en POS)

Prohibido INSERT en prod. Checklist cubierto por lectura de código + queries.

Estado BD al 2026-08-20:

- `lotes_produccion` = 0
- preórdenes Pendiente venta/obra = 0
- 1 preorden `origen=produccion` (legacy)
- 0 cotizaciones
- 17 OV Entregada
- 0 movimientos `PESAJE-*`
- 0 `TEST-P9`

Ítems 2–3 y 7–10: lógica OK, no ejecutados en UI (no hay lotes ni cotizaciones). El primer lote real es la prueba viva.

---

## 4. Archivos principales tocados

```
application/models/Produccion/ProductosModel.php
application/models/Produccion/ProduccionModel.php
application/models/Ventas/VentasModel.php
application/models/Obras/ObrasModel.php
application/models/Compras/PreordenesModel.php   (reutilizado; poco o nada nuevo)
application/controllers/produccion/Productos.php
application/controllers/produccion/Dashboard.php
application/controllers/produccion/Lotes.php
application/controllers/ventas/Pos.php
application/controllers/ventas/Ordenes.php
application/controllers/obras/Obras.php
application/views/produccion/productos/modals/modales.php
application/views/produccion/dashboard/main.php
application/views/produccion/dashboard/detalle.php
application/views/produccion/dashboard/etiqueta_lote.php
application/views/produccion/lotes/main.php
application/views/produccion/lotes/consultar.php          (NUEVA)
application/views/ventas/pos/main.php
application/views/ventas/ordenes/main.php
application/views/obras/detalle.php
application/views/layouts/sidebar.php
application/views/almacen/inventario/main.php
assets/dist/js/produccion_productos.js
```

---

## 5. Flujos que el auditor debe recorrer (código y UI si puede)

A. Cotización POS fabricado → Swal con faltantes, 0 INSERT en `preordenes`  
B. Confirmar cotización → preorden Pendiente `origen=venta`  
C. Venta cobrada con faltantes → preorden al guardar  
D. Venta Entregada → `productos.stock_actual` baja; insumos **no**  
E. Dashboard producción: poll + sonido  
F. Búsqueda formulación “hospital” / “ROCA” / alias  
G. Guardar formulación: actualizar vs nueva versión (no duplicar siempre)  
H. Detalle orden: pesaje → `movimientos_inventario` `PESAJE-*` → Completada → lotes + `movimientos_productos`; segundo pesaje error; Completada sin pesaje bloqueada; `forzar=0` no bypasea  
I. Etiqueta print + `/produccion/Lotes/consultar`  
J. Obra m² con rendimiento 319; sin rendimiento bloquea; aprobar obra preorden; generar OV no duplica  

---

## 6. Hallazgos residuales

No son fallos de una fase abierta; el auditor puede puntuarlos:

1. Dashboard `get_estado_stock_multiple` explota BOM por cada orden del listado (N+1, poll).
2. `explotar_bom_arbol` no escala componentes con `porcentaje` NULL (datos legacy).
3. P8 insumos = 1er nivel × cubetas; P5 producción usa BOM multinivel. Pueden divergir si hay sub-productos fabricados.
4. `detalle_orden_venta` no trae `unidad_linea`; escalado P5 usa `productos.unidad_venta`. Si POS vende cubetas de un SKU en Kg, el BOM se equivoca.
5. `entregar_orden` escribe en `movimientos_inventario` (`producto_id`); producción usa `movimientos_productos`. Inconsistencia histórica.
6. JsBarcode CDN; `@page` CSS var en térmicas.
7. 50×25 mm recorta campos (`overflow: hidden`).
8. SQL legado en controladores (Pos, Obras, Dashboard lote) — no se limpió en masa; las fases nuevas metieron lógica en modelos.
9. `DOCUMENTACION_TECNICA.md` §5 “próximos pasos” está **desactualizada** (habla de migrar columnas que ya existen y de UI Excel-like incompleta). No usarla como checklist de “falta hacer”.
10. No hay prueba E2E de pesaje/etiqueta porque `lotes_produccion` = 0.

---

## 7. Qué no se hizo (fuera de alcance de este overhaul)

- No se reescribió el editor a “Excel inline” completo (P3 mejoró modal + guardar transaccional + grupos; la documentación pedía un dashboard tipo Excel más agresivo).
- No se copió JsBarcode a `assets/` locales.
- No se unificó `movimientos_inventario` vs `movimientos_productos`.
- No hay suite automatizada PHPUnit.
- Facturación, Bixpe, Tres Guerras, MICONTADOR: fuera de este trabajo.

---

## 8. Cómo auditar (método sugerido)

1. Leer `DOCUMENTACION_TECNICA.md` §2–3 y `cotizacion.md` §4, 6, 7, 8, 9.
2. Grep los métodos de la sección 3; no confiar solo en este brief.
3. Verificar P5.1 disparo y `_resolver_total_bom_linea` con casos: 1 Cubeta lote 19 kg → 19; 19 Kg lote 19 kg → 19 (no 361).
4. Verificar P6.1 parseo `forzar` y orden de `puede_completar_produccion`.
5. Verificar P8 no llama `calcular_insumos_para_proyecto(..., m2)` cuando no hay rendimiento.
6. `php -l` en PHP tocado. CSRF en POST nuevos. Permisos en `application/config/permissions.php` (`produccion_ordenes`, `produccion_preordenes`, `compras_autorizar_preordenes`).
7. No “arreglar” en la misma pasada salvo bugs bloqueantes; reportar severidad.

**Criterio de éxito:** el módulo es usable en planta para venta → producción → pesaje → lote → etiqueta y obra m² → materiales, sin duplicar versiones de fórmula ni preórdenes de cotizaciones, y sin descontar insumos dos veces.
