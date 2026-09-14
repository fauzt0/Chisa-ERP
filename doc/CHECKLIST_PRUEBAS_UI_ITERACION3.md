# Checklist QA manual UI — Iteración 3

| Campo | Valor |
|-------|--------|
| **Fecha/hora (CDMX)** | 2026-09-13 ~22:43–22:55 (1ª pasada) · 2026-09-13 23:20 → 2026-09-14 04:50 (2ª pasada) · 2026-09-14 ~05:10–05:25 (continuación) |
| **Usuario QA** | `soporte2@especialistasweb.com.mx` (EHWEB) |
| **URL** | https://erp.chisarecubrimientos.com.mx |
| **Commit** | `1bdec68924679fac7f467941d2c773a172548501` (`iteracion-3`) |
| **Alcance** | Bloques A–G según `doc/CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` + foco iteración 3 (Obras, Producción, global UI) |
| **Viewport** | Desktop ~1440×900 (navegador automatizado); móvil 390×844 **smoke OK** (Clientes, POS, Obras detalle+Entregas, RH) |
| **Ruido PHP 8.3** | Avisos `Severity: 8192` ignorados como preexistentes |

**Nota evidencias:** 1ª pasada en almacenamiento del agente. **2ª pasada:** evidencias en disco en `doc/entrenamiento_3/evidencias_ui/` (DevTools Network + Console toda la sesión).

---

## § Segunda pasada (2026-09-14)

| ID | Acción | Esperado | Resultado | Evidencia | Observación |
|----|--------|----------|-----------|-----------|-------------|
| **GL0** | Re-smoke login/dashboard/tema | OK | ✅ | `/dashboard` sesión EHWEB activa | Sin re-login |
| **BUG-UI-02** | OB-00002 → Entregas, 15 s + Network | Tabla producto + historial vacío | ✅ | `BUG02-2026-09-14-network-get_entregas_obra_ajax.txt` | `GET get_entregas_obra_ajax?obra_id=2` → **200**, ~90 ms, `success:true`, CHISA GLASS MICRO 2.00 pendiente. DOM tras pestaña: tabla + mensaje Almacén. **NO REPRODUCIBLE** |
| **A3** | Clientes → Excel / Imprimir | Sin error | ✅ | `A3A4-2026-09-14-clientes-excel-print-cliente-test.txt` | Blob xlsx (3919 B) con 5 filas validado con JSZip; vista Imprimir con 4 filas y `autoPrint`; 0 errores JS |
| **A4** | TEST-QA-CLIENTE-01 alta/edición/baja | CRUD + limpieza | ✅ | idem | Alta **sí** guardó (CLI-00002, RFC `TQA901201ABC`); el "modal que no cerró" era artefacto de captura; **Inactivado por UI** |
| **D1** | `hospital` / `hospital ROCA` | Tokens sueltos vs literal | ✅ | `D1-2026-09-14-busqueda-productos.txt` | `hospital` → 1 fila; `hospital ROCA` → 0 (diseño literal) |
| **D1b** | `VITROGLASS`, `CHISA MAR`, `475` | Por nombre/código/alias, no por ID | ✅ | `D1bD2-2026-09-14-productos-busquedas-bom204.txt` | VITROGLASS → 1 fila; CHISA MAR → 1 fila; `475` → 0 (ID interno no buscable) |
| **D2** | Formulación #204 → V5 activa (form#968) | Línea AEROSIL 200 (#118) 12.14% | ✅ | idem | Modal "Editando V5 (activa)"; 3 componentes al 100%; etiqueta `[BOM-1: era #91 (auto-referencia)]` |
| **D7** | `/produccion/Lotes` listado global | Carga | ❌ | `BUG-UI-04-2026-09-14-lotes-lista-ajax-500.txt` | `POST produccion/Lotes/lista_ajax` → **500** (`Unknown column 'lp.orden_venta_id'`) |
| **C1b** | Alta obra `TEST-QA-UI-OBRA-01` | Folio | ✅ | `C-2026-09-14-ciclo-obra-TEST-QA-UI-OBRA-01.txt` | OB-00006 (obra_id 14), cliente TEST `Empresa de Prueba S.A.` |
| **C2** | Producto SIN rendimiento → bloquear | No calcular con 1.0 | ✅ | idem | `calcular_materiales_ajax` y `agregar_producto_ajax` → `success:false` + `requiere_rendimiento:true`; 0 filas agregadas |
| **C3** | Producto CON rendimiento 2.5 m²/kg | kg/cubetas coherentes | ✅ | idem | 50 m² × 1.10 = 55 m²; 55 ÷ 2.5 = 22 kg → 22 cubetas; 3 insumos escalados |
| **C4** | Aprobar → preorden obra; re-aprobar | Sin duplicar | ✅ | idem | PRE-2026-0004/5/6 (origen obra OB-00006); 2ª actualización sin preórdenes nuevas |
| **C5a** | PDF resumen | 5 páginas | ✅ | idem | 5 bloques `.pdf-page` (`exportar_pdf/14`) sin errores |
| **C5b** | Entrega desde Almacén | Cantidad mínima | ⚠️ | idem | **Bloqueada por stock 0** (input `max=0`); no forzable. El E2E de entrega ya quedó validado en 1ª pasada (ENT-2026-0001/0002) |
| **C5c** | Limpieza obra TEST | Folios cancelados | ✅ | idem | PRE-2026-0004/5/6 → "Rechazada"; OB-00006 eliminada; listado final: OB-00001, OB-00002 |
| **D4–D6** | Pesaje E2E 1 unidad TEST | Bloqueo sin pesaje / descuento 1 vez / lote + entrada PT | ❌ | `D4D6-…pesaje-OV-2026-0007.txt` + `BUG-UI-07-…` | D4/D5 ✅ (bloqueo + PESAJE-venta-26 1 vez). D6 ❌ Completada 500 `fecha_completado_produccion`. OV-2026-0007 Cancelada. Insumos no revertidos |
| **B4–B8** | Ventas / POS TEST | Flujo + limpieza | ⚠️ | `B8E-G9-MOVIL-…` + `BUG-UI-08-…` | B4/B5 ✅ OV-2026-0007 0 preórdenes en cotización. B6 SKIP ($0 + Completada 500). B7 ❌ crear_ajax 500. B8 ✅ listado+detalle |
| **E3,E5,E8** | Compras TEST | OC/preorden/preview | SKIP | `B8E-G9-MOVIL-…` | E3: modal OK; no se guardó TEST. PDF GET `/generar_pdf/3` 200 (OC DEMO). E5: no autorizar PRE-2026-0001 real. E8: envío prohibido |
| **G2–G9** | Nómina TEST futura | Calcular/cancelar/campana | ⚠️ | `B8E-G9-MOVIL-…` | G2–G7 SKIP (no crear periodo futuro; sin Restaurar en UI). G9 ✅ campana 9+ / `get_notifications` 26 |
| **Móvil** | 390×844 smoke | Pantallas clave | ✅ | `B8E-G9-MOVIL-…` | Clientes, POS, Obras/2 Entregas, RH 18 empleados; sin 500 |
| **INV** | `/almacen/Inventario` | Carga | ❌ | `BUG-UI-05-2026-09-14-inventario-500.txt` | Página completa **500** (`Database Error 1064`) |

### Veredicto bugs 1ª pasada (2ª pasada)

| ID | Veredicto |
|----|-----------|
| **BUG-UI-01** | **No es bug** — búsqueda es substring literal; `hospital ROCA` no implica AND. |
| **BUG-UI-02** | **No reproducible** — backend y render OK (&lt;2 s tras `shown.bs.tab`); ver Network en evidencias. |
| **BUG-UI-03** | **No es bug** — `475` no coincide con código/nombre/alias en catálogo. |

### Bugs nuevos (2ª pasada)

| ID | Módulo | Severidad | Descripción | Evidencia |
|----|--------|-----------|-------------|-----------|
| **BUG-UI-04** | Producción > Control de Lotes | Media-Alta | `POST /produccion/Lotes/lista_ajax` → **HTTP 500** (`Unknown column 'lp.orden_venta_id' in 'ON'`). `ProduccionModel::get_lotes_global_datatables()` (línea ~682) hace JOIN a una columna que no existe. Introducido en `1ef15fb` (2026-08-20). Histórico global de lotes inservible. `Consultar Lote` carga, pero sin datos que consultar. | `BUG-UI-04-2026-09-14-lotes-lista-ajax-500.txt` |
| **BUG-UI-05** | Almacén > Inventario | Media-Alta | `GET /almacen/Inventario` → **HTTP 500** (`Database Error 1064`): el literal `0.5` pasado a `$this->db->select()` se escapa como ``` `0`.`5` ```. `AlmacenModel::get_insumos()` / `get_productos()` (línea 132). **Preexistente** (commit `22f8b21`, ya en `main`), no regresión de iteración 3. | `BUG-UI-05-2026-09-14-inventario-500.txt` |
| **BUG-UI-06** | Ventas > POS | Cosmético (Baja) | `views/ventas/pos/main.php` define **dos veces** `buscarProductos()` (líneas 863 y 1113); la segunda sobrescribe a la primera y ya no oculta "Más Vendidos" al buscar, por lo que la sección queda visible encima de los resultados. | Sesión DevTools (pendiente captura PNG) |

### § Disponibilidad de insumos (bloqueante para D4–D6)

Evidencia obtenida con los propios endpoints de la app (DevTools), sobre los datos reales:

- Catálogo de insumos: **144 insumos**, de los cuales **101 están en/bajo stock mínimo** y **43 tienen stock > 0**.
- Los 3 insumos de `#204` (EXXOL D-40, PLIOWAY E-CH, AEROSIL 200) están en **0** → ese producto **no se puede producir ni pesar** (coincide con los 3 faltantes de la obra OB-00006).
- De ~260 formulaciones evaluadas (`calcular_insumos_ajax`), **solo 5** tienen stock suficiente para pesar 1 lote:

| Producto | ID | Formulación | Insumos (stock) |
|---|---|---|---|
| CHISA GLASS REF 308 | 22 | f684 (V4 activa) | Óxido de Hierro Amarillo (200 kg), Pasta Colorante Verde (80 kg), Negro de Humo (150 kg) |
| CHISA GLASS REF.8750 W | 84 | f559 | — |
| CHISA GLASS REF.CA-1012 | 36 | f36 | — |
| CHISA GLASS REF.CA-1018 | 35 | f35 | — |
| Pintura Epóxica Azul 19L | 10 | f10 | — |

- Los 5 tienen **precio $0.00** y **stock 0** → cualquier OV TEST con ellos queda en $0.00 (aceptable: es dato TEST).
- `CHISA GLASS MICRO` (#3) sí tiene precio ($500.00) pero su formulación tiene 1 faltante (BLANCO) → producción bloqueada.

---

## Resultados por caso (1ª pasada)

| ID | Acción | Esperado | Resultado | Evidencia | Observación |
|----|--------|----------|-----------|-----------|-------------|
| **A1** | Listado clientes + búsqueda | Tabla, filtra | ✅ | URL `/ventas/Clientes`; búsqueda `TEST` redujo listado | 2 clientes en directorio (incl. residuo `CL-TEST-001`) |
| **A2** | Filtros Tipo/Estatus/Saldo | Filtran | ✅ | Combos visibles y operables | No se validó cada combinación |
| **A3** | Excel / Imprimir | Descarga / print | ✅ | 2ª pasada | Ver § Segunda pasada |
| **A4** | Nuevo/editar/offcanvas | CRUD | ✅ | 2ª pasada | Alta TEST-QA + inactivación por UI |
| **A5** | Carga masiva Excel | Import TEST | SKIP | — | Prohibido: importación masiva en reglas QA |
| **A6** | Cotización → venta TEST | Confirma | SKIP | — | Pendiente (autorizado) |
| **B1** | `/ventas/Ordenes` | Listado OK | ✅ | Título «Gestión de Órdenes de Venta» | KPI «En Preparación: 1» |
| **B2** | Detalle OV + formatos | Abren | SKIP | — | Sin abrir OV real (OV-TEST-001 conocida) |
| **B3** | `/ventas/Pos` | UI OK | ✅ | Búsqueda `ROCA` muestra catálogo | Ticket $0.00 inicial |
| **B4** | Cotización TEST fabricado | 0 preorden | ✅ | OV-2026-0007 (id 26) | CHISA GLASS REF 308 ×1; insumos OK; 0 preórdenes |
| **B5** | Confirmar compromiso TEST | Preorden si faltantes | ✅ | `confirmar_ajax` 200 | `preordenes:[]`; En Preparación; detalle `/produccion/Dashboard/detalle/orden_venta/26` |
| **B6** | Cobrar/entregar TEST | Stock PT | SKIP | — | Total $0; Completada bloqueada (BUG-UI-07) |
| **B7** | `/ventas/Descuentos` | CRUD mínimo | ❌ | `BUG-UI-08-…` | `crear_ajax` 500 `MY_Model::insert()` protected. Listado sí carga |
| **B8** | `/ventas/ObrasVentas` | Carga | ✅ | detalle/14 200 | 7 obras. OB-00006 sigue Aprobada en CRM Ventas |
| **C1** | Listado obras | Carga | ✅ | 2 obras `OB-00001`, `OB-00002` | Folios OB-XXXXX visibles |
| **C2** | Borrador sin rendimiento | Bloquea 1.0 | ✅ | 2ª pasada | Bloqueo correcto |
| **C3** | MASA ROCA + m² | Coherente | ✅ | 2ª pasada | 22 kg / 22 cubetas |
| **C4** | Aprobar TEST + preorden | Sin duplicar | ✅ | 2ª pasada | 3 preórdenes, sin duplicados |
| **C5** | Cancelar obra TEST | Limpieza | ✅ | 2ª pasada | Obra eliminada por UI |
| **C*** | Tab **Entregas** detalle | Carga | ⚠️→✅ | `/obras/Obras/detalle/2` | 1ª pasada quedó en «Cargando…»; 2ª pasada **no reproducible** |
| **C*** | PDF 5 hojas | Texto resumen | ✅ | Bloque «Documento profesional de 5 hojas…» | 2ª pasada: 5 páginas verificadas |
| **D1** | Buscar `hospital` / `ROCA` AND | Filtra | ✅ | 2ª pasada | Comportamiento literal confirmado |
| **D1b** | Productos nuevos visibles (#475) | Buscables | ✅ | 2ª pasada | Búsqueda por nombre/código/alias |
| **D2** | Ver formulación #204 V5 | BOM OK | ✅ | 2ª pasada | V5 activa, AEROSIL #118 |
| **D3** | Dashboard + mute | Carga | ✅ | `/produccion/Dashboard`; toggle sonido | 6 pedidos en tablero |
| **D4–D6** | Pesaje / completada E2E | Reglas pesaje | ❌ | 2ª pasada continuación | D4/D5 OK; D6 BUG-UI-07 |
| **D7** | Lotes / etiqueta | Consulta | ❌ | 2ª pasada | BUG-UI-04 |
| **E1** | Proveedores listado | Carga | ✅ | 9 activos, catálogo DataTable | — |
| **E2** | Insumos vinculados proveedor | Visible | SKIP | — | Sin abrir detalle proveedor |
| **E3** | OC TEST 1 línea | Borrador+PDF | SKIP | 2ª pasada continuación | Modal OK; no se creó TEST. PDF DEMO 200 |
| **E4** | PDF vs Excel histórico | Gaps | SKIP | — | Revisión documental |
| **E5–E7** | Preorden / recepción / pago | Flujo | SKIP | — | No autorizar PRE-2026-0001 real; recepción SKIP |
| **E8** | Correo/WhatsApp preview | Preview | SKIP | — | Sólo preview; envío requiere autorización humana |
| **E*** | `/compras/OrdenesCompra` | Carga | ✅ | 4 OC en sistema | UI preórdenes visible |
| **F1** | Buscar `ana` | Filtra | ✅ | `/rh/RecursosHumanos` | Tabla paginada tras filtro |
| **F2** | Detalle expediente | Sin 500 | SKIP | — | No se abrió fila empleado |
| **F3** | RFC/CURP inválidos | Rechaza | SKIP | — | — |
| **F4** | Incidencias | Badge | SKIP | — | — |
| **F5** | Vacaciones | Carga | ✅ | Modal «Gestión de Vacaciones» en DOM | — |
| **F6** | Contrato PDF | Sin `{{` | SKIP | — | — |
| **F7** | `/rh/RelojChecador` | Carga | ✅ | Dashboard dispositivos 2, sync visible | — |
| **G1** | Nómina listado | Carga | ✅ | `/rh/Nomina` historial + filtros | — |
| **G2–G7** | Ciclo nómina TEST futuro | Calcular/cancelar | SKIP | — | No se creó (evitar residuos; sin Restaurar en vista) |
| **G8** | Planeador meses | OK | ✅ | Modal «Planeador de Nóminas — Julio 2026» | — |
| **G9** | Campana nómina | Badge | ✅ | `get_notifications` 200 | Badge 9+; nóminas vencidas (p. ej. NOM000026) |
| **GL1** | Login `/auth` | Entra dashboard | ✅ | Redirección a `/dashboard` | — |
| **GL2** | Toggle tema | Cambia tema | ✅ | «Cambiar a modo claro/oscuro» en header | — |
| **GL3** | Dashboard permisos | Módulos autorizados | ✅ | Texto «solo módulos autorizados» | Menú amplio para EHWEB |

---

## Bugs (histórico)

| ID | Estado |
|----|--------|
| BUG-UI-01 | Cerrado — diseño búsqueda |
| BUG-UI-02 | Cerrado — no reproducible con Network |
| BUG-UI-03 | Cerrado — diseño búsqueda |
| BUG-UI-04 | **Abierto** — 500 en listado global de Lotes |
| BUG-UI-05 | **Abierto (preexistente en `main`)** — 500 en Almacén > Inventario |
| BUG-UI-06 | **Abierto (cosmético)** — POS, definición duplicada de `buscarProductos()` |
| BUG-UI-07 | **Abierto (alta)** — Completada producción 500: columna `fecha_completado_produccion` ausente en prod |
| BUG-UI-08 | **Abierto (media)** — `/ventas/Descuentos/crear_ajax` 500: `MY_Model::insert()` protected |
| BUG-UI-09 | **Abierto (media)** — Ventas > Obras (CRM): el listado y los KPIs **no filtran `activo=1`**, así que las obras con soft delete (`ObrasModel::eliminar_obra`) siguen visibles y contadas (OB-00006 id 14) |
| BUG-DATA-01 | **Abierto (alta, presentación)** — catálogo sin datos comerciales: 491/494 productos sin `precio_venta`, 462 con descripción placeholder "Producto importado desde Excel…", 492 sin foto, 492 sin rendimiento, 180 fabricados activos sin formulación activa |

*No se reportan:* deprecations PHP 8.3, productos sin precio #475–#503 (reportados en conjunto como **BUG-DATA-01**), formulaciones inactivas, residuos OV-TEST-001 / OV-2026-0004 / cliente prueba.

### § Verificación de precios e IVA (2026-09-14, read-only)

| Pregunta | Resultado | Evidencia |
|----------|-----------|------------|
| ¿Los productos tienen precio? | ❌ **No**: 494 productos → 463 con `precio_venta = 0`, 28 con `NULL`; **solo 2 con precio > 0** (`VITROGLASS-ECOLOGICO` #475 = $5,023.57 y `CHISA GLASS MICRO` #3 = $500.00) | `verificar_presentacion.php` (BD) + POS mostrando `$0.00` |
| ¿El sistema genera IVA? | ✅ **Sí**: `iva = subtotal × 0.16` correcto en ventas y compras | OV-2026-0003: 5000 → 800 → 5800; OV-2026-0005: 500 → 80 → 580; OC-2026-DEMO1: 1000 → 160 → 1160 |
| Efecto combinado | Una venta con cualquier producto del catálogo da **$0.00** (subtotal, IVA y total), aunque el motor de IVA funcione | POS / cotizaciones TEST |

**Conclusión:** no es un bug de IVA sino de **precios faltantes**; el IVA ya se calcula bien en `ordenes_venta` y `ordenes_compra`. El plan de carga de precios (fuente: `doc/entrenamiento_3/manifiestos/productos_match.json`, política §A8/A9 de `decisiones_pendientes.md`) está en `doc/PROMPT_FIXES_PRESENTACION_ITERACION3.md` (§T4).

---

## Datos TEST-QA creados y limpieza

| Folio/ID | Módulo | Estatus final |
|----------|--------|----------------|
| `TEST-QA-CLIENTE-01` → CLI-00002 (RFC TQA901201ABC) | Clientes | **Inactivo** (limpieza por UI) |
| `TEST-QA-UI-OBRA-01` → OB-00006 (obra_id 14) | Obras | **Eliminada** por UI (`eliminarObra`) |
| PRE-2026-0004 / 0005 / 0006 | Compras (preórdenes) | **Rechazada** (motivo "Limpieza QA TEST-QA-UI-OBRA-01") |
| OB-00001 / OB-00002 | Obras | Solo lectura; sin modificación |
| Residuos preexistentes | OV-TEST-001, OV-2026-0004, CL-TEST-001 (`Empresa de Prueba S.A.`) | No son de esta sesión; se usó `Empresa de Prueba S.A.` como cliente TEST en obra/venta |
| OV-2026-0007 (orden_id 26) `TEST-QA-UI-OV-01` | Ventas / Producción | **Cancelada** por UI. Pesaje ya descontó PIG-003/006/004 (`PESAJE-venta-26`); sin revertir |
| OB-00006 (obra_id 14) | Ventas > Obras | Sigue **Aprobada** en CRM (`/ventas/ObrasVentas`) tras «eliminada» en módulo Obras |

---

## Resumen

| Métrica | 1ª pasada | 2ª pasada |
|---------|-----------|-----------|
| ✅ | 18 | +14 inicial + continuación: B4, B5, D4, D5, B8, G9, móvil |
| ⚠️ | 6 | C5b (stock 0); E3 parcial; OB-00006 residual en CRM Ventas |
| ❌ | 0 | **5** (BUG-UI-04, 05, 06, **07 Completada**, **08 Descuentos**) |
| SKIP | 28 | B6 cobro; E3 crear OC TEST; E5–E8 envío/recepción; G2–G7 nómina futura |

| Módulo | Veredicto pre-PR (2ª pasada + continuación) |
|--------|----------------------------------------|
| Obras / Entregas | **Apto** — ciclo TEST OK; entrega bloqueada sólo por stock 0; CRM Ventas aún lista OB-00006 |
| Producción / búsqueda / BOM | **No apto Completada** — D4/D5 OK; D6 BUG-UI-07; Lotes D7 roto |
| Clientes | **Apto** |
| Ventas POS | **Apto cotización/confirmar**; Descuentos **no apto** (BUG-UI-08); cobro TEST no ejecutado |
| Almacén | **No apto** — Inventario 500 (BUG-UI-05) |
| Compras | **Parcial** — listados OK; OC TEST no creada; no autorizar PRE real |
| Nómina | **Parcial** — campana OK; ciclo futuro no creado |
| Móvil | **Apto smoke** 390×844 |

**Recomendación pre-PR:** corregir BUG-UI-04, 05 y **07** (500 bloqueantes). BUG-UI-08 impide CRUD descuentos. Migrar `fecha_completado_produccion` en prod.

### Capturas/evidencias en disco (`evidencias_ui/`)

```
total 64
-rw-r--r-- 1 root root 2441 Sep 13 23:55 A3A4-2026-09-14-clientes-excel-print-cliente-test.txt
-rw-r--r-- 1 root root 1634 Sep 14 05:23 B8E-G9-MOVIL-2026-09-14-resto.txt
-rw-r--r-- 1 root root 1155 Sep 13 23:26 BUG02-2026-09-14-network-get_entregas_obra_ajax.txt
-rw-r--r-- 1 root root 2298 Sep 14 00:56 BUG-UI-04-2026-09-14-lotes-lista-ajax-500.txt
-rw-r--r-- 1 root root 2135 Sep 14 02:03 BUG-UI-05-2026-09-14-inventario-500.txt
-rw-r--r-- 1 root root  743 Sep 14 05:23 BUG-UI-07-2026-09-14-completada-fecha-columna.txt
-rw-r--r-- 1 root root  395 Sep 14 05:23 BUG-UI-08-2026-09-14-descuentos-insert-500.txt
-rw-r--r-- 1 root root 5312 Sep 14 03:35 C-2026-09-14-ciclo-obra-TEST-QA-UI-OBRA-01.txt
-rw-r--r-- 1 root root  686 Sep 13 23:29 D1-2026-09-14-busqueda-productos.txt
-rw-r--r-- 1 root root 1768 Sep 14 00:56 D1bD2-2026-09-14-productos-busquedas-bom204.txt
-rw-r--r-- 1 root root 2666 Sep 14 05:12 D4D6-2026-09-14-pesaje-OV-2026-0007.txt
-rw-r--r-- 1 root root 6612 Sep 14 05:24 INFORME_QA_UI_2A_PASADA_CONTINUACION.md
```

*(PNG de pantalla Entregas: el guardado del MCP quedó en el almacenamiento del agente; no copiado al workspace.)*
