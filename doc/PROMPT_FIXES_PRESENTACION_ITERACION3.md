# PROMPT DE REEMPLAZO — Cierre de iteración 3: fixes + QA pendiente · ERP CHISA

> **Cómo usar este documento:** es **autosuficiente**. Pégalo completo en un chat nuevo de Cursor con acceso
> a shell en `/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html` (el agente anterior se detuvo por
> lentitud, no por bloqueo). Contiene: accesos, estado exacto verificado, hallazgos con archivo/línea,
> tareas a ejecutar en orden, casos QA pendientes, atajos, trampas y entregables con formato.
> **Fuente de los hallazgos:** 2ª pasada QA UI (`doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md`,
> `doc/entrenamiento_3/evidencias_ui/INFORME_QA_UI_2A_PASADA_CONTINUACION.md`) + consultas read-only del 2026-09-14.

---

## 0. TL;DR — objetivos, en este orden

| # | Objetivo | Tipo | Estado |
|---|----------|------|--------|
| T1 | Aplicar migración pendiente (con ENUM corregido) → arregla **BUG-UI-04** y **BUG-UI-07** y habilita **lote + entrada PT** | Bloqueante | ❌ |
| T2 | Arreglar **BUG-UI-05** (Almacén > Inventario 500) | Alta | ❌ |
| T3 | Arreglar **BUG-UI-08** (Descuentos crear/editar 500) | Media | ❌ |
| T4 | **Cargar precios**: 491/494 productos sin precio (el IVA ya funciona) | Alta / presentación | ❌ |
| T5 | **BUG-UI-09**: Ventas CRM lista obras borradas (`activo=0`) y KPIs inflados | Media | ❌ |
| T6 | Limpieza de datos TEST de la 2ª pasada (OB-00006, stock del pesaje, movimiento en 0) | Media | ⚠️ |
| T7 | Catálogo con datos incompletos (462 descripciones placeholder, 492 sin foto/rendimiento, formulaciones) | Presentación | ❌ |
| T8 | Re-verificación final + informe + commit | Obligatorio | — |
| T9 | **Opcional (no bloquea PR):** cerrar los casos QA que quedaron SKIP (B6, E3, E5, E8 preview, G2–G7) | QA | SKIP |

**Respuestas cortas a las dudas de negocio (ya verificadas, no hace falta re-investigar):**
- **¿Los productos tienen precio? NO.** `productos`: **494** filas → **463 con `precio_venta = 0`**, **28 con `NULL`**
  (491 sin precio) y **solo 3 con precio > 0**: `#3 PROD-0001` (CHISA GLASS MICRO) $500.00, `#4 PROD-0002` $520.00
  y `#475 VITROGLASS-ECOLOGICO` $5,023.57.
- **¿El sistema genera IVA? SÍ.** `iva = subtotal × 0.16` correcto en ventas y compras
  (OV-2026-0003: 5000→800→5800; OV-2026-0005: 500→80→580; OC-2026-DEMO1: 1000→160→1160). El problema es que
  **con precio 0 todo da $0.00** y POS/cotizaciones se ven vacíos.

---

## 1. Entorno, accesos y repositorio

| Dato | Valor |
|------|-------|
| Repo | `/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html` ⚠️ **es el mismo código que sirve el sitio en vivo** (cualquier guardado afecta producción al instante) |
| Stack | CodeIgniter 3 · PHP 8.3.33 (CLI en `/usr/local/bin/php`... verifica con `php -v`) · MySQL remoto |
| BD | host `67.217.58.138`, base/usuario `st32477_chisa` (credenciales en `application/config/database.php`) |
| Cliente MySQL | `mysql` y `mysqldump` disponibles en `/usr/local/bin/` |
| Sitio | https://erp.chisarecubrimientos.com.mx |
| Usuario QA | `soporte2@especialistasweb.com.mx` (EHWEB) — **pedir contraseña al humano**. Sin 2FA |
| Rama | `iteracion-3` |
| **Commit local (HEAD)** | **`175d862`** (*"test(produccion): soporte GET en la herramienta de pruebas y sondeo server-side de BUG-UI-02"*) |
| **origin/iteracion-3** | **`c3a4b6f`** → hay **5 commits locales SIN push** (`175d862`, `1bdec68`, `ee8f656`, `ced4e73`, `9f9cd57`) |
| Migraciones | **No existe `application/migrations`**: todo el SQL vive en `database/*.sql` y se aplica a mano |
| Entorno | `ENVIRONMENT=development` → los avisos `Severity: 8192` (*Creation of dynamic property…*) son **ruido preexistente de PHP 8.3, NO bugs**. Error real = `Fatal error`, `Uncaught`, HTTP 500, blanco o acción muerta |
| CSRF | Deshabilitado: el token se llama `ci_csrf_token` y viaja vacío → los POST de la app funcionan sin token |
| Untracked al recibir | `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md`, `doc/PROMPT_*.md` (4), `doc/entrenamiento_3/evidencias_ui/` (12 archivos, propiedad de `root` pero legibles) y `doc/entrenamiento_3/tools/verificar_presentacion.php` |

**Reglas de git:** commits atómicos en `iteracion-3` con el estilo del repo (`fix(modulo): …`, `docs(...)`, `test(...)`),
**nunca** push a `main`. Antes de cualquier `ALTER TABLE`, respaldar las tablas afectadas con `mysqldump`.
No commitear `application/config/database.php`. Probar en la UI antes de commitear.

---

## 2. Estado exacto verificado (evidencia dura — no re-investigar)

### 2.1 Git
```
HEAD local        = 175d862f986e312a118594451e8fd349715e5e09  (2026-09-13)
origin/iteracion-3 = c3a4b6f6520cbca15657c0b457d7c5d48dac1bbd
pendientes de push = 175d862, 1bdec68, ee8f656, ced4e73, 9f9cd57
working tree       = limpio salvo los untracked de doc/ listados arriba
```

### 2.2 BD (consultas read-only del 2026-09-14)
```
productos        : 494 · precio_venta: 463 = 0 · 28 NULL · 3 > 0 (#3 $500, #4 $520, #475 $5,023.57)
productos.activo : ¡NO EXISTE! la columna de estatus es `productos.estatus`  ← trampa al filtrar
descripcion      : 462 con "Producto importado desde Excel. Completa los datos en el catálogo."
sin foto / sin rendimiento : 492 / 492
formulaciones    : 956 (314 activas) · 180 fabricados activos SIN formulación activa
ordenes_venta    : 23 (22 con IVA ≠ 0) · `iva = subtotal×0.16` correcto · NO existe columna `descuento`
ordenes_compra   : 4 (todas con IVA correcto)
lotes_produccion : 0 lotes  ·  movimientos_productos tipo 'Produccion': 0
columnas FALTANTES en prod: lotes_produccion.orden_venta_id, lotes_produccion.obra_id,
                            ordenes_venta.fecha_completado_produccion, obras.fecha_completado_produccion
                            (lotes_produccion.orden_produccion_id SÍ existe)
ENUM actuales    : movimientos_productos.tipo_movimiento =
                   enum('Entrada','Salida','Ajuste','Produccion','Venta','Devolucion')
                   movimientos_inventario.tipo_movimiento = enum('Entrada','Salida','Ajuste')
obras            : 7 totales · 5 con activo=0 (borradas; siguen visibles en CRM Ventas) — OB-00006 id 14 sigue ahí
movimientos_inventario PESAJE-venta-26: id 21 insumo 18 (Negro de Humo) 150.00→149.97;
                   id 20 insumo 20 (Pasta Colorante Verde) 80.00→79.03; id 19 insumo 17 cantidad 0.00 (inútil)
OV-2026-0007 id 26: Cancelada, total 0.00 · OV-2026-0006 id 25: Cancelada 1160.00
VERIFICAR con: php doc/entrenamiento_3/tools/verificar_presentacion.php
```
> Nota: ese script es read-only y **hoy no verifica precios ni columnas** (lo dice el informe). En T8 amplíalo si
> te sirve (secciones J/K/L/M sugeridas en §5.8), o verifica esas cosas ad-hoc con el mismo patrón
> (`define('BASEPATH',true); $db=[]; require 'application/config/database.php';` + `mysqli`).

### 2.3 Bugs abiertos con causa raíz (detalle en §5)

| ID | Sev. | Módulo | Causa raíz verificada |
|----|------|--------|------------------------|
| BUG-UI-04 | Alta | Producción > Control de Lotes | `ProduccionModel::get_lotes_global_datatables()` (líneas **652-659**) hace `JOIN ordenes_venta ov ON ov.id = lp.orden_venta_id` y `JOIN obras o ON o.id = lp.obra_id` **sin comprobar que las columnas existan** (no existen) → HTTP 500 en `POST /produccion/Lotes/lista_ajax` |
| BUG-UI-05 | Alta | Almacén > Inventario | `AlmacenModel::get_insumos()` línea **~100-104** y `get_productos()` línea **~139-143** meten `… WHEN i.stock_actual <= i.stock_minimo * 0.5 THEN "critico" …` dentro de `$this->db->select()`; el query builder escapa `0.5` como `` `0`.`5` `` → `Database Error 1064`. **Preexistente** (commit `22f8b21`, ya en `main`) |
| BUG-UI-06 | Baja (cosmético) | Ventas > POS | `views/ventas/pos/main.php` define **dos veces** `buscarProductos()`: líneas **864** y **1113**; la 2ª gana y ya no oculta "Más Vendidos" al buscar |
| BUG-UI-07 | Alta | Producción al completar | `produccion/Dashboard::actualizar_estatus_ajax()` líneas **148-154** hace `UPDATE ordenes_venta SET … fecha_completado_produccion` (columna inexistente) → 500. Y el INSERT del lote (líneas **226-244**) usa `orden_venta_id`/`obra_id`, también ausentes |
| BUG-UI-08 | Media | Ventas > Descuentos | `controllers/ventas/Descuentos.php:110` y `:134` llaman `$this->DescuentosModel->insert()` / `->update()`, que en `core/MY_Model.php` son `protected` (líneas **128** y **151**) → `Call to protected method` |
| BUG-UI-09 | Media | Ventas > Obras (CRM) | `ObrasModel::eliminar_obra()` hace **soft delete** (`obras.activo = 0`) pero `ventas/ObrasVentas::lista_ajax()` (líneas ~37-114) y `get_estadisticas_obras()` (`count_all*('obras')`) **no filtran `activo=1`** → OB-00006 sigue listada y contada |
| BUG-DATA-01 | Alta / presentación | Catálogo | 491/494 sin `precio_venta`; 462 con descripción placeholder; 492 sin foto; 492 sin rendimiento; 180 fabricados activos sin formulación activa |

**Cerrados / no repetir:** BUG-UI-01 y BUG-UI-03 **no son bugs** (la búsqueda de Productos es substring literal por
código/nombre/alias/categoría; `hospital ROCA` no implica AND y `475` es ID interno, no buscable). BUG-UI-02
(tab Entregas) **no reproducible**: `GET get_entregas_obra_ajax?obra_id=2` → 200 en ~90 ms.

---

## 3. Reglas duras y precauciones

- Nunca `DROP`/`TRUNCATE`/`DELETE` masivo. Cambios de esquema solo con `ALTER` **idempotente** + **respaldo previo**
  (`mysqldump` de las tablas afectadas, guardar salida en el informe).
- El repo sirve el sitio en vivo: si un cambio rompe la UI, revertir el archivo de inmediato.
- No enviar correos/WhatsApp, no emitir CFDI real, no cobros/pagos reales, no importaciones masivas,
  no modificar precios de catálogo **existentes** sin OK del humano.
- No tocar datos reales: OB-00001, OB-00002, OVs reales distintas de las TEST, nómina real, clientes/proveedores
  reales más allá de referenciarlos. **No autorizar** `PRE-2026-0001` (preorden real).
- Datos de prueba con prefijo `TEST-QA-`, cantidades mínimas (1 línea / 1 unidad) y limpieza por UI.
- Respetar `doc/REGLAS_TECNICAS.md` (CI3, MVC, Active Record, soft delete, PRG, sin SQL crudo en controllers/vistas).
- Un bloque a la vez. Si un 500/blanco bloquea: detén ese bloque, documenta con evidencia y sigue.
- Ruido tolerado: avisos `Severity: 8192` de PHP 8.3.

---

## 4. Contexto: qué ya se probó (2ª pasada QA UI) — para no repetir

Resumen del acta (detalle completo en `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md` y en
`doc/entrenamiento_3/evidencias_ui/INFORME_QA_UI_2A_PASADA_CONTINUACION.md`):

| Caso | Resultado |
|---|---|
| GL0 login/dashboard/tema · GL1-3 | ✅ |
| A3 Excel/Imprimir Clientes · A4 `TEST-QA-CLIENTE-01` | ✅ (CLI-00002 quedó **Inactivo**) |
| C1b–C5c ciclo obra `TEST-QA-UI-OBRA-01` (OB-00006, id 14) | ✅ bloqueo sin rendimiento, kg/cubetas, PRE-2026-0004/5/6, PDF 5 hojas, limpieza UI. **Residuo:** OB-00006 `activo=0` + estatus Aprobada sigue visible en CRM Ventas (BUG-UI-09) |
| D1/D1b/D2 búsquedas y formulación #204 (V5 activa, form#968 con AEROSIL #118) | ✅ |
| D4/D5 pesaje de OV-2026-0007 | ✅ bloqueo sin pesaje + `PESAJE-venta-26` descontó 1 sola vez |
| D6 Completada | ❌ BUG-UI-07 (500 por `fecha_completado_produccion`) → **OV-2026-0007 Cancelada**; insumos del pesaje **no revertidos** (T6) |
| B4/B5 POS + confirmar (OV-2026-0007, CHISA GLASS REF 308 #22 ×1) | ✅ 0 preórdenes en cotización; `preordenes:[]` al confirmar |
| B6 cobrar/entregar TEST | SKIP: total $0 + Completada bloqueada |
| B7 Descuentos | ❌ BUG-UI-08 |
| B8 ObrasVentas | ✅ listado+detalle (y de paso se vio BUG-UI-09) |
| D7 Lotes | ❌ BUG-UI-04 |
| E3 OC TEST / E5 / E8 | SKIP (no se creó OC TEST; no autorizar PRE real; envío prohibido) |
| G2–G7 nómina futura | SKIP (no se creó periodo; evitar residuos) · G9 campana ✅ |
| Móvil 390×844 | ✅ smoke (Clientes, POS, Obras/2 Entregas, RH) |
| `/almacen/Inventario` | ❌ BUG-UI-05 (500, preexistente en `main`) |
| Evidencias | 12 archivos en `doc/entrenamiento_3/evidencias_ui/` — **no borrar** |

**Datos TEST-QA creados y su estatus final** (para T6): CLI-00002 **Inactivo**; OB-00006 **activo=0** (pero visible
en CRM); PRE-2026-0004/5/6 **Rechazada**; OV-2026-0007 **Cancelada** (pesaje aplicado sin revertir: ver 2.2);
`TEST-QA-UI-OBRA-01`, `TEST-QA-UI-OV-01`, `TEST-QA-CLIENTE-01`, `TEST-QA-DESC-01` (si lo creas en T3).

---

## 5. Tareas (ejecutar en orden)

### T1 — Migración pendiente `final_alignment.sql` (bloqueante) → cierra BUG-UI-04 y BUG-UI-07

**Causa raíz:** `database/final_alignment.sql` **nunca se aplicó en producción**. Verificado con `INFORMATION_SCHEMA`:
de las columnas que agrega, en prod solo existe `lotes_produccion.orden_produccion_id` (§2.2).

**⚠️ NO aplicar el archivo tal cual:** su bloque de ENUMs (líneas 59-60 del archivo) reescribe
`ENUM('Entrada','Salida','Ajuste','Merma','Devolución','Produccion')` y **eliminaría `Venta`** de
`movimientos_productos` (hoy presente y usada por ventas/entregas) → rompería los movimientos de stock de ventas.
Además usa `'Devolución'` con acento, mientras el código escribe `'Devolucion'`.

**Fix:**
1. Crear `database/fix_presentacion_iter3.sql` **idempotente** (mismo patrón `INFORMATION_SCHEMA` + `PREPARE` del
   archivo original) con:
   - `ALTER TABLE lotes_produccion MODIFY COLUMN orden_produccion_id INT NULL;`
   - `ADD COLUMN orden_venta_id INT NULL` (si no existe) + `ADD COLUMN obra_id INT NULL` (si no existe)
     + índices `idx_ov`, `idx_obra` (solo si no existen);
   - `ADD COLUMN fecha_completado_produccion DATETIME NULL` en `ordenes_venta` y en `obras` (si no existen);
   - ENUM **corregidos conservando lo existente**:
     - `movimientos_productos`: `enum('Entrada','Salida','Ajuste','Produccion','Venta','Devolucion','Merma')`
     - `movimientos_inventario`: `enum('Entrada','Salida','Ajuste','Produccion','Merma','Devolucion')`
2. Respaldar antes: `mysqldump` de `lotes_produccion`, `ordenes_venta`, `obras`, `movimientos_productos`,
   `movimientos_inventario` → guardar en `doc/entrenamiento_3/evidencias_ui/`.
3. Aplicar y guardar la salida literal en `doc/entrenamiento_3/evidencias_ui/FIX-T1-migracion-<fecha>.txt`.
4. **Alternativa/complemento recomendado (defensa en código):** hacer que
   `ProduccionModel::get_lotes_global_datatables()` (líneas 652-659) use el patrón `field_exists` que ya existe
   en el mismo archivo, en `_fetch_lote_etiqueta_row()` (líneas **1228-1250**): añadir el select/join de
   `ordenes_venta` y `obras` **solo si** `$this->db->field_exists('orden_venta_id','lotes_produccion')` /
   `field_exists('obra_id','lotes_produccion')`. Así el listado no vuelve a romperse si el esquema viaja sin migración.

**Verificación / aceptación:**
- `INFORMATION_SCHEMA`: las 4 columnas existen (o corre el script ampliado, §5.8).
- UI: `/produccion/Lotes` pinta la tabla (sin 500, sin filas o con lotes).
- UI: marcar una OV de prueba como **Completada** → 200 y se crea lote + movimiento
  `movimientos_productos.tipo_movimiento='Produccion'` (entrada PT). Hoy lotes = 0 y movs `Produccion` = 0,
  así que **cualquier fila nueva es la prueba**. Usa un producto producible (§7).

### T2 — BUG-UI-05 · Almacén > Inventario 500

**Fix:** no escapar la expresión del CASE, en `application/models/Almacen/AlmacenModel.php`
(`get_insumos()` ~línea 100 y `get_productos()` ~línea 139):
- usar el 2º parámetro de escape: `$this->db->select('… CASE … * 0.5 … END AS nivel_stock', FALSE);`
- o definir el CASE con `$this->db->query()`, o pasar el literal ya escapado.
No cambiar la lógica de negocio (mismos umbrales: `<= 0.5·min` crítico, `<= min` bajo, etc.).

**Aceptación:** `/almacen/Inventario` → 200, KPIs y listados pintan; `stock_actual` coincide con la columna Stock de
`/compras/Insumos`. Guardar evidencia (status + primeras líneas del body).

### T3 — BUG-UI-08 · Descuentos crear/editar 500

**Fix (mínimo y seguro):** en `application/models/Ventas/DescuentosModel.php` añadir wrappers públicos:
```php
public function crear($data)          { return $this->insert($data); }
public function actualizar($id, $data){ return $this->update($id, $data); }
```
y usarlos en `controllers/ventas/Descuentos.php:110` y `:134`. **No** cambiar la visibilidad de
`MY_Model::insert/update` (afectaría a todos los modelos).

**Aceptación:** crear `TEST-QA-DESC-01`, editar, desactivar/eliminar **por UI**; `lista_ajax` sigue OK (hay 3 descuentos).

### T4 — Precios de productos (presentación, alta)

**Política ya decidida** en `doc/entrenamiento_3/manifiestos/decisiones_pendientes.md` (§A8/A9):
`productos.precio_venta` = precio de la lista 2025 **tal cual (sin IVA)**; el IVA se calcula en los documentos.

**Fuente:** `doc/entrenamiento_3/manifiestos/productos_match.json` → 64 items; **32 con `presentaciones[]`**
(cada una con `presentacion`, `precio_sin_iva`, `rendimiento_teorico`, `categoria`): **4 mapeados a productos
existentes** (`producto_id`) y **28 nuevos** (ya creados con `id >= 475`).

**Fix:** crear `doc/entrenamiento_3/tools/cargar_precios.php` con `--dry-run` (default) y `--apply`:
1. Leer el JSON y resolver el producto destino: por `producto_id` si existe; si no, por `codigo`/`nombre`
   (los 28 nuevos ya existen en BD).
2. Presentación principal: preferir `CUBETA`; si no, la primera de la lista (documentar la regla).
3. `UPDATE productos SET precio_venta = <precio_sin_iva> WHERE id = …` — **no** tocar los 3 con precio > 0
   (#3, #4, #475) sin validarlos antes con el humano.
4. Imprimir tabla antes→después + total; guardar en `doc/entrenamiento_3/evidencias_ui/FIX-T4-precios-<fecha>.txt`.

**Aceptación:** consulta read-only → activos sin precio ≈ 0 (excepciones documentadas). UI: POS muestra precio ≠ 0
al buscar `VITROGLASS`, `CHISA MAR`, `HEALERGLASS`; cotización TEST de 1 unidad con subtotal + **IVA 16 %** + total
coherentes; la OV confirmada guarda `iva = subtotal×0.16`.

**Relacionado (no bloquea):** `doc/entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md` — de las 45 filas de
la lista 2025 solo 2 tienen `rendimiento_m2_por_kg` calculable (VITROGLASS 9.74 m²/kg y POLY-COLOR 4.0); 43 quedan
`FALTANTE` porque falta el **contenido neto del envase** (dato de negocio, pendiente). **No inventar valores**:
cargar lo calculable y listar los FALTANTE en el informe. `productos.rendimiento` (texto) = rango literal de la lista.

### T5 — BUG-UI-09 · Ventas CRM: obras borradas visibles + KPIs inflados

**Fix:** en `application/controllers/ventas/ObrasVentas.php`:
- `lista_ajax()` (líneas ~37-114): añadir `$this->db->where('o.activo', 1);` antes del `count_all_results`.
- `get_estadisticas_obras()`: añadir el mismo filtro a **todos** los `count_all*('obras')`.
- Revisar `detalle()` y `get_obra_ajax`/facturación: si la obra está `activo=0`, avisar "obra eliminada"
  en vez de mostrarla (decisión de UX; documenta lo que hagas).

**Aceptación:** `/ventas/ObrasVentas` no muestra OB-00006; los KPIs cuadran con las obras `activo=1`.

### T6 — Limpieza de los datos TEST de la 2ª pasada

| Objeto | Situación | Acción |
|---|---|---|
| `obras.id=14` **OB-00006** (`activo=0`, estatus Aprobada, total 1160.00) | Sigue en BD y **visible en CRM Ventas** | Con T5 desaparece de la UI. **Decidir con el humano**: recomendado dejarla `activo=0` + `estatus='Cancelada'` |
| `movimientos_inventario` **PESAJE-venta-26**: insumo #18 (Negro de Humo) 150.00→149.97; #20 (Pasta Colorante Verde) 80.00→79.03 | Insumos descontados por el pesaje de una OV TEST luego cancelada; **no hay UI para revertir** | **Decisión del humano**: dejar documentado como merma de QA **o** registrar movimientos de reverso (`tipo_movimiento='Entrada'`, `referencia='REVERSO-QA-PESAJE-venta-26'`) |
| Movimiento id 19: insumo #17 (PIG-003) `cantidad = 0.00` | Registro inútil | Candidato a limpieza puntual **con OK del humano** (o filtrar los 0 en el reporte de movimientos) |
| `OV-2026-0007` (id 26, total 0.00) | Ya **Cancelada** | No borrar (histórico) |
| `PRE-2026-0001` | Preorden **real** | **NO autorizar / no tocar** |
| Residuos conocidos: OV-TEST-001, OV-2026-0004, CL-TEST-001 ("Empresa de Prueba S.A.") | No son de esta iteración | **No tocar** |

### T7 — Catálogo: datos incompletos para la presentación

Verificado (§2.2): 462 con descripción placeholder `"Producto importado desde Excel. Completa los datos en el catálogo."`,
492 sin `foto_producto`, 492 sin `rendimiento`, **180 fabricados activos sin formulación activa**.
**Fix incremental por prioridad (con OK del humano antes de escribir):**
1. Quitar el placeholder (descripción vacía o texto neutro) — visible en POS y listado de Productos.
2. Cargar `rendimiento` donde el manifiesto lo tenga (`presentaciones[].rendimiento_teorico` y/o
   `propuesta_rendimientos_fase3.md`); lo FALTANTE se lista, no se inventa.
3. Emitir la lista de los **180 fabricados sin formulación activa** y decidir con el humano:
   ¿reactivar BOM (creando versión nueva, nunca modificando la activa), re-clasificar a `Reventa` o desactivar?
4. Fotos: fuera de alcance salvo que el humano aporte archivos.

### T8 — Re-verificación final, informe y commit

1. Ejecutar `php doc/entrenamiento_3/tools/verificar_presentacion.php` (puedes ampliarlo con: J) columnas
   agregadas, K) ENUMs, L) conteo de precios con lista de los que sí tienen, M) placeholder/foto/rendimiento/BOM).
2. UI con DevTools (Network + Console): `/produccion/Lotes` (200) · `/almacen/Inventario` (200) ·
   `/ventas/Descuentos` (CRUD completo) · POS con precios ≠ 0 · OV TEST con IVA correcto ·
   **Completada → lote + entrada PT** (producto producible, §7).
3. Actualizar `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md`: mover BUG-UI-04/05/07 (y 06/08/09) a **cerrado** con la
   evidencia del fix, y anotar en la tabla de veredictos.
4. Commits atómicos en `iteracion-3` (código + SQL + docs). **Push a `origin/iteracion-3`** (hay 5 commits locales
   sin subir, §2.1) — nunca a `main`.
5. (Opcional, pero recomendado) `git add` de los untracked de `doc/` (checklist, prompts, evidencias, tools) para no
   perder el rastro del QA.

### T9 — Casos QA que quedaron pendientes (no bloquean los fixes; hacerlos si el humano lo pide)

- **B6** cobrar/entregar una OV TEST de **precio ≠ 0** (tras T4 ya es posible): verificar que baja el stock de PT y
  que los insumos **no** se mueven otra vez; si $0 bloquea, documentar con Network.
- **E3** OC TEST de **1 línea** → borrador + PDF (valida subtotal/IVA/total contra el PDF) → cancelar por UI.
- **E5** preorden TEST → autorizar → OC generada **sin duplicar**; recepción parcial **solo si es limpiable por UI**
  (si no, SKIP con razón). **No** tocar `PRE-2026-0001`.
- **E8** solo **preview** de correo/WhatsApp (envío = SKIP "requiere autorización humana").
- **G2–G7** nómina de periodo **futuro** TEST: Borrador ($0) → Calcular → recalcular (no duplica) →
  comidas/horas extra inline → Cancelar (motivo ≥10 caracteres).
- Re-ejecutar **D6** (Completada E2E) tras T1, usando el producto producible (§7): pesaje (1 sola vez) → lote +
  entrada PT; OV/cotización TEST y limpieza por UI al final (listar folios).

---

## 6. Entregables

1. Código + SQL en `iteracion-3` (commits atómicos, pusheados a `origin/iteracion-3`).
2. **Informe** en `doc/entrenamiento_3/evidencias_ui/INFORME_FIXES_PRESENTACION.md` con:
   - `## Cabecera` (commit, fecha/hora CDMX, quién ejecutó, entorno).
   - `## Tareas` → tabla `ID | Tarea | Estado ✅/⚠️/❌ | Evidencia | Observación`.
   - `## Precios antes/después` (sin precio antes → después; muestra de 5 precios cargados).
   - `## IVA` (evidencia de una OV TEST con subtotal/IVA/total).
   - `## Datos tocados` (SQL aplicado, tablas y conteos antes/después, respaldos generados).
   - `## Pendientes` con la razón exacta.
   - `## Resumen JSON` (**último bloque, en fence ```json**):
     `{"commit":"","fecha":"","tareas":{"cerradas":N,"parciales":N,"no_hechas":N},"precios":{"sin_precio_antes":491,"sin_precio_despues":0,"cargados":N},"iva":{"ok":true,"evidencia":""},"bugs_cerrados":["BUG-UI-04","BUG-UI-05"],"pendientes":[""]}`
3. Evidencias en disco `doc/entrenamiento_3/evidencias_ui/<ID>-<fecha>-<descriptor>.txt` (URL, status,
   Content-Type, tiempo, primeras líneas del body) + `ls -la` final pegado en el informe.
4. Resumen de **≤10 líneas** en el chat con los ❌/⚠️ y su evidencia.

---

## 7. Atajos técnicos, endpoints y trampas (ahorra horas)

- **Endpoints DataTables server-side** (POST, `peticion=ajax&draw=1&start=0&length=300` + filtros, desde la consola
  del navegador con `fetch(..., {credentials:'include'})`; CSRF vacío):
  - `/compras/Insumos/lista_ajax` (col 5 = `stock_actual / stock_minimo`).
  - `/produccion/Productos/lista_ajax` (col 0 código, 2 nombre, 5 tipo, 6 stock, 7 precio, 8 estatus).
  - `/ventas/Clientes/lista_ajax`.
- **Rutas clave:** `/produccion/Dashboard` · detalle: **`/produccion/Dashboard/detalle/orden_venta/{id}`**
  (⚠️ `…/detalle/venta/{id}` da **404**) · `/produccion/Lotes` · `/produccion/Lotes/consultar` ·
  `/produccion/Dashboard/etiqueta_lote/{lote_id}` · `/almacen/Inventario` · `/almacen/Entregas` ·
  `/ventas/Descuentos` · `/ventas/ObrasVentas` · `/compras/OrdenesCompra` · `/obras/Obras`.
- **Productos producibles hoy** (insumos con stock suficiente; receta: `get_productos_base_ajax` → por producto
  `get_historial_formulaciones_ajax` (usar `es_activa=1`) → `calcular_insumos_ajax {formulacion_id, cubetas:1}` →
  sirve si `datos.hay_insumos_faltantes === false`): **#22 CHISA GLASS REF 308 (f684)** ← el más cómodo,
  #84 (f559), #36 (f36), #35 (f35), #10 (f10). Los 3 insumos de `#204` (EXXOL D-40, PLIOWAY E-CH, AEROSIL 200)
  están en **0** → #204 no se puede producir. `CHISA GLASS MICRO` #3 tiene precio pero 1 faltante (BLANCO).
- **POS:** el buscador solo dispara con **eventos de teclado reales** (`keyup`); si el texto se inserta sin eventos,
  pulsa una tecla física después de escribir y verifica que `#grid_productos` cambió.
- **Lotes:** `lotes_produccion.estatus` ENUM real, el valor que usa el código es `'Producido'`;
  `codigo_barras` lo genera `sp_generar_codigo_barras` (con fallback `PROD-YYYYMMDD-…`).
  Mientras T1 no esté aplicado, un lote se puede ver en `Produccion > Fabricación Dashboard > detalle`
  (sección "Lotes generados") o `produccion/Lotes/consultar`.
- **`movimientos_productos`** tiene `venta_id` y `motivo`; `tipo_movimiento` incluye `'Venta'` (no lo pierdas con la migración).
- **`productos` NO tiene `activo`**: la columna de estatus es `productos.estatus` (¡error común!).
  `obras` **sí** tiene `activo` (soft delete).
- **Nómina:** no hay envío de correo en el ciclo G2–G7; el periodo debe ser **futuro**.
- **Usuarios con permisos de Obras** (por si el QA lo pide): ids 1 (`soporte2@especialistasweb.com.mx`),
  6 (`ggeneral@chisarecubrimientos.com.mx`), 7 (`facturacion@chisarecubrimientos.com.mx`). El usuario demo
  `presentacion@chisa.mx` **no** tiene permisos de Obras.
- **Herramientas read-only ya existentes:** `php doc/entrenamiento_3/tools/verificar_presentacion.php`,
  `php doc/entrenamiento_3/tools/consulta_db.php find|insumos|form|activas|resumen "texto"`,
  `php doc/entrenamiento_3/tools/verificar_fase3.php`.

---

## 8. Comandos útiles

```bash
# Estado del repo
git log --oneline -6 && git status --short && git log origin/iteracion-3..HEAD --oneline

# Respaldo antes de ALTER (ejemplo)
mysqldump -h 67.217.58.138 -u st32477_chisa -p st32477_chisa lotes_produccion ordenes_venta obras \
  movimientos_productos movimientos_inventario > doc/entrenamiento_3/evidencias_ui/respaldo_T1_$(date +%F_%H%M).sql

# Verificaciones read-only
php doc/entrenamiento_3/tools/verificar_presentacion.php
php doc/entrenamiento_3/tools/consulta_db.php activas 22     # formulación activa de un producto
php doc/entrenamiento_3/tools/consulta_db.php find "VITROGLASS"
```

---

## 9. Archivos de contexto (leer si hace falta detalle)

| Archivo | Para qué |
|---|---|
| `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md` | Acta completa del QA (2 pasadas), bugs, veredictos pre-PR |
| `doc/entrenamiento_3/evidencias_ui/INFORME_QA_UI_2A_PASADA_CONTINUACION.md` | Informe de la 2ª pasada + resumen JSON |
| `doc/CHECKLIST_PRUEBAS_OBRAS_ITERACION3.md` | QA de Obras (BUG-1…BUG-8, ciclo completo) |
| `doc/entrenamiento_3/manifiestos/decisiones_pendientes.md` | Decisiones A1–A11 del entrenamiento 3 (fuente de la política de precios) |
| `doc/entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md` | PASO 3 (rendimientos/precios) y datos FALTANTE de negocio |
| `doc/entrenamiento_3/manifiestos/productos_match.json` | Fuente para T4 (32 items con `presentaciones[]`) |
| `doc/REGLAS_TECNICAS.md` · `DOCUMENTACION_TECNICA.md` | Reglas y arquitectura |
| `doc/TODO.md` | Pendientes globales del proyecto |
| `database/final_alignment.sql` | Migración a NO aplicar tal cual (ver T1) |

---

*ERP Chisa Recubrimientos · prompt de reemplazo generado el 2026-09-14 para cerrar la iteración 3
(fixes de presentación + QA pendiente) en un chat nuevo.*
