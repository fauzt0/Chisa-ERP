# TODO - Sistema ERP CHISA

**Última actualización:** 2026-09-29  
**Desarrollador:** Fausto Solano - CHISA Recubrimientos  
**Rama activa:** `iteracion-5` (base `iteracion-4` `c44a2cd`; commits locales sin push obligatorio)

**Handoff:** `ENVIRONMENT=development`. Rama `iteracion-3` **eliminada** (local + `origin`) el 2026-09-24 — seguir solo en `iteracion-4` / `main`. No timbrar/cobrar real, no autorizar `PRE-2026-0001`, no tocar `OV-2026-0009`. **Smoke:** `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` (columna **Aud. P0**). **Cumplimiento oferta:** § Auditoría diagrama abajo (reloj/Bixpe excluido).

---

## 📋 Auditoría diagrama general (2026-09-24)

**Fuentes:** `doc/diagrama_general.png`, `doc/cotizacion.md`, `sidebar.php`, código. **Excluido:** Reloj Checador / Bixpe / KONECT (I5).

### Resumen por módulo

| Módulo | ✅ | ⚠️ | ❌ | N/A |
|--------|----|----|----|-----|
| Admin usuarios | 7 | 1 | 0 | 0 |
| RH | 8 | 3 | 0 | 0 |
| Proveedores/Compras | 7 | 3 | 0 | 0 |
| CRM Clientes | 9 | 1 | 2 | 0 |
| Ventas / POS | 7 | 2 | 1 | 0 |
| Obras / cálculo materiales | 7 | 2 | 3 | 0 |
| Producción | 6 | 2 | 1 | 0 |
| Almacén | 6 | 0 | 2 | 0 |
| Facturación | 4 | 2 | 4 | 0 |
| Contabilidad | 8 | 2 | 2 | 2 |
| Reportes (dispersos) | 2 | 5 | 0 | 0 |
| **Total filas** | **~71 (60%)** | **~21 (18%)** | **~15 (13%)** | **2** |

*Ajustes vs matriz inicial:* RH calculadora baja → **⚠️** (cotización: no calcula finiquito oficial; solo datos en `get_datos_calculadora`). Reenvío cotización → **⚠️** (link + SMTP a validar).

### Top 10 — orden de atención

| # | Gap | P | Validar con checklist |
|---|-----|---|------------------------|
| 1 | Smoke POS: IVA trigger, entrega baja PT | P0 | **B3–B6** |
| 2 | Recepción OC → `insumos.stock` + movimientos | P0 | **E5, E6** |
| 3 | Dashboard producción OV/obras; Completada→PT; 2.º pesaje | P0 | **D3–D6** |
| 4 | Facturación go-live Facture (sandbox→prod + re-OAuth) | P0 | Decisión negocio; `cli_probe` |
| 5 | Liga autofactura cliente por OV | P0 | Nuevo desarrollo (no checklist) |
| 6 | Carátula + resumen general + generador conceptos | P1 | `entrenamiento_4/` (pendiente plantillas) |
| 7 | Control calidad / viscosidad por lote | P1 | Código nuevo |
| 8 | Notificaciones cruzadas OV / lote completado | P1 | B6, alertas |
| 9 | Email factura PDF+XML | P1 | §4.9 diferido |
| 10 | Reportes exportables RH/Ventas/Almacén | P1 | Post-smoke |

**Huecos contractuales (no confundir con bugs I4):** pasarela de pagos online, autofactura, micontador go-live, calendario CRM, DIOT/conciliación (N/A I4).

### Documental obras (diagrama vs hoy)

| Existe | `obras/pdf_resumen.php` (BOM técnico), entregas CRM, cotización/recibo obra |
| Falta (entrenamiento_4) | Carátula, resumen general contractual, generador/catálogo de conceptos |

### Smoke P0 manual (2026-09-24, usuario presentación)

Ejecutado vía `doc/SMOKE_P0_AGENTE_EJECUTOR.md`. Cierre: OV-2026-0010/0011 canceladas; PRE-2026-0008 rechazada; **PRE-2026-0001** y **OV-2026-0009** sin tocar.

| Bloque | Resultado | Notas |
|--------|-----------|--------|
| B3–B5 | ✅ | Cotización sin preorden; confirmación + preorden; IVA 500×0.16 |
| B6 | ⚠️→fix | Cobro mostrador bajó PT a negativo — **corregido** `validar_stock_pt_lineas` + `entregar_orden` transaccional |
| E5–E6 | ✅ | OC-2026-0001; recepción 1 Kg BLANCO |
| D3–D4 | ✅ | Dashboard OV; Completada sin pesaje bloqueada |
| D5–D6 | ✅ | **Evidencia real 17–18-sep-2026:** `PESAJE-venta-28` → OV-2026-0009 `Completada` → lote `PROD-20260918-22-2268` + entrada PT, sin doble descuento. Falta re-smoke UI (ninguna orden abierta pasa el filtro de insumos: 2 por stock, 2 por unidades ambiguas) → guion en §4.4; **corrida Opción A completada 28-sep-2026** (CLI+BD, tx revertida: 44 asserts, 0 fallos, 0 residuos) |
| I + cli_probe | ✅ | Orígenes OK; sandbox `ok: true` |

**P0 cerrados:** #1 (re-smoke B6 28-sep), #2 Compras, #4 Facture conexión. **#3** con evidencia real de flujo
(17–18-sep) y con el **fix del atajo de `revision_manual`** aplicado 28-sep-2026 (`60d9bb8`) + corrida D5–D6 en
transacción revertida (§4.4); queda pendiente **solo el re-smoke en UI**.

---

## 📝 Notas Técnicas

- [ ] Validar límite de `max_input_vars` en PHP para formularios con muchos checkboxes. (IMPORTANTE: en cada deployment). Verificado 2026-09-17: valor efectivo **1000** (default); subir si algún formulario con muchos checkboxes falla.
- Entorno de producción sigue en `ENVIRONMENT=development` (sin 2FA). No cambiar a `production` en I4 salvo decisión explícita.
- Overhaul Obras/Producción P1–P9 **cerrado** (2026-08-20). No reimplementar. Handoff histórico: git `doc/AUDITORIA_OVERHAUL_PRODUCCION_2026-08-20.md` si existía; estándares vigentes: `DOCUMENTACION_TECNICA.md`.

---

## 🟡 Estatus del proyecto

- [X] Desarrollo
- [X] Iteración 3 — cerrada y mergeada a `main` (2026-09-18, `7778571`); rama `iteracion-3` retirada 2026-09-24
- [ ] Iteración 4 — **activa**: 4.0 catálogo, 4.2 POS, 4.3 obras (entregas CRM + estatus), 4.8 contabilidad, 4.9 Facture sandbox. Pendiente: re-smoke UI de POS (B6), 4.4 dashboard/merma UI, datos 4.6. **4.1 Compras: cerrado ✅ 2026-09-28** (técnico + smoke UI E1–E8, T2/T5).
- [ ] Iteración 5 — **no iniciar**: reloj checador (función nueva + auditoría de punches)
- [ ] Despliegue — en producción: `https://erp.chisarecubrimientos.com.mx`

---

## ✅ Iteraciones completadas

- [X] **Proveedores (I5–I6 históricas)** — comprobantes, email/WhatsApp, cotizaciones y comparación
- [X] **Nómina RH (I2 + Planeador)** — automatización, detalle, recibos, enum `'Horas Extras'`
- [X] **Producción / Obras (Overhaul)** — formulaciones, preórdenes, pesaje, etiquetas, m²→kg
- [X] **Import de formulaciones** — `referencia_cliente`
- [X] **CRM Ventas** — contactos extra, carga masiva, Excel
- [X] **Contraste / responsive** — tema oscuro, badges, tablas
- [X] **Facturación (base I anteriores)** — API Facture App en sandbox; detalle vivo y huecos en §**4.9** (no es go-live)
- [X] **Reloj checador (base)** — `api/ApiReloj`, `rh/RelojChecador`, proxy `doc/iclock/`
- [X] **PDF OC** — estilo Excel, importe con letra, UTF-8
- [X] **Obras I3** — tab Entregas (módulo Obras), trigger almacén, preórdenes/solicitudes, PDF, BUG-1 a BUG-8
- [X] **Entrenamiento 3 (Producción)** — OCR 25 capturas, fases 1–3, BOM-1 (#204 form#968)
- [X] **QA UI I3 + cierre E2E** — BUG-UI-04/05/06/07/08/09; Completada OV-2026-0009 + lote `PROD-20260918-22-2268`; descuentos CRUD; ENUM `'Completada'`; `direccion` en `guardar_ajax`; merge a `main` 2026-09-18

---

## 🟢 Iteración 4 — misma área, más detalle (ACTIVA)

No es un entrenamiento masivo nuevo: **no hay Excel adicional en el repo** y PASO 3 (contenido neto de envases) sigue bloqueado por negocio. I4 afina flujos ya existentes. Prefijo TEST-QA-. No cobrar/timbrar real, no autorizar `PRE-2026-0001`, no tocar OV-2026-0009 (Completada).

### 4.1 Compras / proveedores — entradas de insumos y productos
- [X] Recibir OC TEST: smoke E6 OC-2026-0001 — BLANCO 0→1 Kg (`recibir_mercancia`).
- [X] Recibir producto de reventa: **N/A documentado** (2026-09-28) — 0 productos `tipo_producto='Reventa'` en BD y `detalle_orden_compra.insumo_id` es `NOT NULL` + FK. Si negocio lo pide: patrón `insumos.producto_id`.
- [X] Preorden → autorizar → OC **sin duplicar**: garantizado en `PreordenesModel::aprobar()` (rechaza si `estatus !== 'Pendiente'`) y **verificado en UI real el 2026-09-28** (re-autorizar `PRE-2026-0012` → bloqueado, sin OC duplicada). T5 (sin proveedor) también verificado en UI: mensaje claro y 0 OC. `PRE-2026-0001` sin tocar.
- [X] Unidades: `convertir_unidad_insumo` aplicado en `aprobar()` (preorden → unidad del insumo). Reformulación: la línea de OC **no** guarda unidad, por lo que la conversión no puede hacerse en `recibir_mercancia`.
- [X] Recepción robusta (2026-09-28): `recibir_mercancia()` valida en dos pasadas estatus de la OC, pertenencia de la línea, cantidad > 0 y sobre-recibo. Evidencia en `MODULOS_ESTADO_CHECKLIST.md` §4.
- [X] PDF OC + preview correo/WhatsApp: preview verificado en UI 2026-09-28 (`simular_correo_ajax` + `whatsapp_texto_ajax`, sin SMTP). **Gap:** `generar_pdf` entrega HTML imprimible, no PDF binario (ver checklist §H).

### 4.2 Ventas — mostrador (POS) vs obras
- [X] Sucursales POS: tabla `sucursales`, OV con `sucursal_id`, selector de caja (sesión). Matriz CDMX sembrada. Stock PT **sigue global** (kardex por sucursal: posterior).
- [X] Guard POS: servidor y UI bloquean `precio_venta <= 0` (usa precio de catálogo, no el del ticket).
- [X] **Indirecta (obra):** agregar producto usa `consultar_insumos_obra` (sin preorden); preórdenes al pasar a **Aprobada** / compromiso (`verificar_insumos_y_preordenes_obra`).
- [X] **Directa (POS):** cotización → confirmar — smoke 24-sep B3–B5 ✅; **B6 + B10 re-smoke ✅ 28-sep-2026** (transacción revertida) tras la guarda de stock PT.
- [X] IVA smoke OV-2026-0010: $80 = (500−0)×0.16 (trigger).
- [X] Guard POS mostrador: no cobrar **Entregada** si `stock_actual` PT &lt; cantidad (`VentasModel::validar_stock_pt_lineas`).
- [X] Entregas almacén + ciclo completo B6 (PT suficiente) — **cerrado 28-sep-2026**: B6 re-smoke ✅ (tx revertida) y bloque **J. Almacén** (J1–J7) ✅ con guardas de `registrar_entrega()`. Nota: el camino feliz se validó con stock inyectado dentro de la transacción porque los pendientes reales tienen PT ≤ 0.

### 4.3 Obras — cálculos y estatus
- [X] Materiales obra: `calcular_materiales_linea_obra` / `calcular_insumos_para_proyecto` sin fallback rendimiento 1.0 del simulador general.
- [X] Validación servidor de estatus ENUM (`ObrasModel::ESTATUS_OBRA_VALIDOS` en `actualizar_obra_desde_post`).
- [X] Tab Entregas en CRM Ventas — `ventas/obras/detalle.php` + partial `obras/partials/seguimiento_entregas.php`.
- [X] SQL de actualización AJAX movido al modelo (`ObrasModel::actualizar_obra_desde_post`; controlador delgado).
- [ ] Smoke manual de estatus en UI (checklist §C; `stricton=false` en MySQL).

### 4.4 Producción / inventario (afinar, no rehacer)
- [~] Dashboard: pedidos de **OV y obras** visibles; Completada → lote + entrada PT; segundo pesaje bloqueado.
  Estado por sub-punto (auditoría 28-sep-2026, evidencia en BD): **OV/obras visibles** ✅;
  **Completada → lote + entrada PT** ✅ con evidencia real persistente (17–18-sep: `PESAJE-venta-28` →
  `OV-2026-0009` `Completada` → lote id 1 `PROD-20260918-22-2268` + `movimientos_productos` id 3
  (`Produccion`, 1 Kg, `venta_id = 28`) → `productos` id 22 stock 0→1 vía `tr_actualizar_stock_producto`,
  con **0** salidas de insumos nuevas); **2.º pesaje bloqueado** ✅ (observado **28-sep-2026** en corrida con
  transacción revertida: `success = false` y mensaje literal *"Los insumos de esta orden ya fueron descontados por
  pesaje anterior."*, con **0** filas nuevas en `movimientos_inventario`); **re-smoke UI** ⚠️ (ver *Guion smoke D5–D6*
  abajo y *Resultado de la corrida*).
- [X] Merma de pesaje en **servidor**: tope 20% en `ProduccionModel::confirmar_pesaje` (UI aún dice ~20%; caso B3 histórico — validar en smoke D).
- [ ] Escalado BOM y `explotar_bom_plano` en simulador vs obra (mismas cantidades).
- [ ] `grupo_color` en explosión (pendiente de `decisiones_pendientes.md` A1) — solo si toca un caso real de I4.
- [X] **Fix P1 (hallazgo 28-sep-2026):** `ProduccionModel::puede_completar_produccion()` devuelve **`ok = true`** cuando
  `revision_manual` no está vacío pero `insumos` quedó vacío: el atajo `if (empty($verificacion['insumos']))` (paso 2,
  línea ~1141) se evalúa **antes** del chequeo de `revision_manual` (paso 3, línea ~1146). Verificado en vivo con
  `OV-2025-0013` (id 13): `get_estado_pesaje_orden` → `bloqueada = true`, pero `puede_completar_produccion` → `ok = true`
  (*"Sin insumos calculables para esta orden."*), lo que en la UI permite marcar **Completada** sin pesaje ni consumo de
  insumos y generar lote + entrada PT. Fix sugerido: mover el chequeo de `revision_manual` antes del atajo (o marcar
  `bloqueada` y devolver `ok = false`).
  **Cerrado 28-sep-2026 (`60d9bb8`):** el chequeo de `revision_manual` se movió **antes** del atajo `empty($insumos)`,
  conservando **literalmente** los mensajes. Matriz antes/después sobre **34** órdenes (27 OV + 7 obras, ambas con el
  código actual y con el HEAD previo): delta exacto de **5** filas, todas con `count_revision_manual ≥ 1` **e**
  `count_insumos = 0` — `OV-2025-0013`, `OV-2025-0014`, `OV-2026-0001`, `OV-2026-0002` y `OB-00002`, todas con
  `ok = true → false` y mensaje → *"No se puede completar: hay insumos con unidades ambiguas que requieren revisión
  manual."*; las otras **29** filas **sin cambio** (incluidas las que ya devolvían `ok = false`, p. ej. `OV-TEST-001`
  y `OB-TEST-002`). Auditoría de consumidores de `revision_manual` (28-sep-2026): `descontar_stock_produccion()`,
  `get_estado_pesaje_orden()`, `get_estado_stock_multiple()`, `ProductosModel` (verificaciones de preorden) y
  `ObrasModel`/`Dashboard` **sí** evalúan la ambigüedad antes de cualquier atajo — el único patrón invertido era éste.

#### Guion smoke D5–D6 (Producción) — números medidos el 28-sep-2026

Medición hecha con `ProduccionModel::get_insumos_requeridos_para_orden()` (solo lectura). El bloqueo real **no es
solo el stock**: de las 4 órdenes abiertas con producto fabricado, 2 se caen por stock y 2 por unidades ambiguas.

| Orden | Producto / línea | Insumos requeridos (medido) | Bloqueo exacto |
|-------|------------------|-----------------------------|----------------|
| `OV-2025-0013` (id 13, **En Preparación**) | PROD-0001 × 2 Cubetas | INS00001 (Pintura Vinílica Blanca) | `revision_manual`: *No hay una conversión segura definida entre "Kg" (fórmula) y "Cubeta" (insumo)* |
| `OV-TEST-001` (id 21, Confirmada) | PROD-PINT-001 × 5 Cubetas (form. 10) | Resina 61.75 Kg (stock 500) ✅ · Pigmento 14.25 Kg (50) ✅ · **Solvente 5 L** | `revision_manual`: conversión insegura **"Kg" ↔ "L"** (líquidos sin densidad) |
| `OV-2026-0004` (id 23, Confirmada) | PROD-0001 × 12 Cubetas | BLANCO 6 844.20 Kg | stock 1.00 Kg (faltan 6 843.20) |
| `OV-2026-0008` (id 27, **Cotización**) | PROD-0001 × 1 Cubeta | BLANCO 570.35 Kg | stock 1.00 Kg; además `Cotización` **no** aparece en el dashboard |

Punto de partida a respetar: `insumos` 61 (BLANCO) = **1.00 Kg**; `productos` 3 = **-57.00**; producto 10 =
**0.00**; `lotes_produccion` = **1**; `ordenes_produccion` = **0**; 27 filas en `movimientos_inventario`.

**Opción A — recomendada (transacción revertida, 0 residuos):**

1. `$this->db->trans_begin();`
2. Inyectar materia prima: `UPDATE insumos SET stock_actual = stock_actual + 570.35 WHERE id = 61;`
   (alcanza 1 cubeta; el tope de merma de 20 % permite hasta 684.42).
3. `ProduccionModel::confirmar_pesaje_y_descontar(27, 'venta', [['insumo_id' => 61, 'cantidad_real' => 570.35]], 1)`
   → assert `success = true`, 1 detalle y **una** salida con `referencia = PESAJE-venta-27`.
4. Repetir la llamada → assert `success = false` con *"Los insumos de esta orden ya fueron descontados por pesaje anterior."*
   (**2.º pesaje bloqueado**: observado el 28-sep-2026, ver *Resultado de la corrida* abajo — cierra el sub-punto ⚠️).
5. `puede_completar_produccion(27, 'venta')` → assert `ok = true` (*"Pesaje confirmado. Lista para completar."*).
6. Replicar el cierre del controlador (`produccion/Dashboard::actualizar_estatus_ajax` → `Completada`): actualizar
   estatus, insertar lote y llamar `procesar_inventario_por_produccion(27, 'venta', $lotes)`.
   Asserts: +1 `lotes_produccion`, +1 `movimientos_productos` (`Produccion`, `venta_id = 27`) y **0** filas nuevas
   en `movimientos_inventario`.
7. `$this->db->trans_rollback();` y confirmar que los conteos vuelven al punto de partida.
   *(Los métodos internos abren `trans_start()`; CI3 resuelve los anidados con SAVEPOINT — mismo patrón de los smokes A1/J1.)*

**Opción B — E2E con UI real (deja residuo, requiere limpieza):**

1. Recepcionar BLANCO vía OC por ≥ 571 Kg (E5/E6) o ajustar `insumos` 61 con el módulo de almacén.
2. POS: crear **OV TEST-*** de PROD-0001 × 1 Cubeta y pasarla a `Confirmada`/`En Preparación` (así aparece en `/produccion/Dashboard`).
3. UI: confirmar pesaje (BLANCO 570.35) y verificar que los insumos bajan **una vez**.
4. UI: **Completada** → verificar lote + etiqueta + entrada PT (producto 3 pasa de -57.00 a -56.00).
5. Limpieza obligatoria (regla de items TEST): cancelar la OV `TEST-*`, **reverso** de BLANCO a 1.00 Kg y del
   producto 3 a -57.00, y borrar el lote `QA-*`.

#### Resultado de la corrida — 28-sep-2026 (Opción A, transacción revertida)

Ejecutada con un controlador CLI **temporal** `produccion/Cli_fase1_smoke` (borrado al terminar; patrón de los smokes
A1/J1/B6: `MY_Controller` omite sesión y permisos cuando `is_cli()` es `true`; `php -l` en limpio). Salida cruda:
**44 asserts, 0 fallos**, y re-assert del baseline de 28-sep-2026 íntegro.

| Paso | Resultado observado |
|------|---------------------|
| 0 · Datos vigentes | `OV-2026-0008` (id 27) en `Cotización`, 1 línea `PROD-0001` × 1.00 Cubeta, `revision_manual = 0`, insumo 61 teórico **570.35 Kg** (stock 1.00) |
| 1 · Inyección (dentro de la TX) | `insumos` 61 → **571.35** |
| 2 · `confirmar_pesaje_y_descontar(27, 'venta', [{insumo_id: 61, cantidad_real: 570.35}], 1)` | `success = true`, 1 detalle; **una** fila con `referencia = PESAJE-venta-27` (id 41, `Salida`, 570.35, `571.35 → 1.00`, `usuario_id = 1`); `movimientos_inventario` 27 → 28; trigger `trg_stock_insumos_movimiento` deja `insumos` 61 = **1.00** |
| 3 · 2.º pesaje (llamada idéntica) | `success = false`, mensaje literal **"Los insumos de esta orden ya fueron descontados por pesaje anterior."**; **0** filas nuevas (28 → 28) |
| 4 · `puede_completar_produccion(27, 'venta')` | `ok = true`, *"Pesaje confirmado. Lista para completar."* |
| 5 · Cierre estilo `Dashboard::actualizar_estatus_ajax` | OV-27 → `Completada` + `fecha_completado_produccion`; lote id 2 `PROD-20260928-3-8944` (1 Cubeta, `Producido`); `procesar_inventario_por_produccion` → `true`; **+1** `lotes_produccion` (1 → 2); **+1** `movimientos_productos` (`Produccion`, `venta_id = 27`, cantidad 1.00, motivo con el código del lote); producto 3: **−57.00 → −56.00** (`tr_actualizar_stock_producto`); **0** filas nuevas en `movimientos_inventario` (28 = 28) |
| 6 · Fix P1 end-to-end | `puede_completar_produccion(13, 'venta')` → `ok = false`, `bloqueada = true`, *"No se puede completar: hay insumos con unidades ambiguas que requieren revisión manual."* |
| 7 · `trans_rollback()` | Ejecutado |
| 8 · Re-assert baseline | **13/13 OK:** `insumos` 61 = 1.00; producto 3 = −57.00; producto 22 = 1.00; producto 10 = 0.00; `lotes_produccion` = 1; `ordenes_produccion` = 0; `movimientos_inventario` = 27; `movimientos_productos` = 3; `ordenes_venta` = 27; `ordenes_compra` = 7; `preordenes` = 10; `facturas` = 9; `polizas` = 35. Además OV-27 = `Cotización` con `fecha_completado_produccion = NULL`, OV-13 = `En Preparación` y **0** filas con `referencia = PESAJE-venta-27` (confirmado también por consulta SQL fuera del script) |

Extras de solo lectura (mismo controlador, sin transacción): `get_estado_pesaje_orden(13, 'venta')` →
`bloqueada = true`, `stock_suficiente = false`, `consumido = false`, `revision_manual = 1`. Las 4 órdenes abiertas con
producto fabricado (`sin_productos = 0`, `stock_suficiente = 0` en todas):

| id | folio | `count(insumos)` | `count(revision_manual)` | `bloqueada` |
|----|-------|------------------|--------------------------|-------------|
| 13 | `OV-2025-0013` | 0 | 1 | 1 |
| 21 | `OV-TEST-001` | 2 | 1 | 1 |
| 23 | `OV-2026-0004` | 1 | 0 | 1 |
| 27 | `OV-2026-0008` | 1 | 0 | 1 |

**Sin probar en esta corrida:** el re-smoke en **UI real** (ninguna orden abierta pasa aún el filtro de insumos) y el
sub-paso D7 (etiqueta / consulta de lote); sólo se validó el cierre por código con transacción revertida.

### 4.8 Contabilidad MX (solo lectura de módulos existentes)

No se cambian Ventas, Compras, Facturación, Nómina ni Almacén. Contabilidad **lee** documentos y arma pólizas/reportes SAT-básicos.
- [X] Catálogo mínimo (Clientes, IVA, Ventas, Inventario, Proveedores, Capital) + ejercicio/periodos del año en curso si faltan.
- [X] Orígenes: CFDI `facturas` Emitida → Ingresos; OC Recibida → Diario (inventario/IVA acreditable/proveedores); nómina Pagada → Egresos. Idempotente por `origen`+`origen_id`.
- [X] Reportes: balanza, balance general, estado de resultados (ya existían); **libro diario, mayor, auxiliar IVA** (trasladado vs acreditable).
- [X] Pólizas en **borrador** hasta autorizar; entonces alimentan balanza/balance. OV sin CFDI **no** se póliza. Sync CLI 2026-09-18: 35 pólizas (7 CFDI, 3 OC, 25 nóminas), 0 desbalanceadas; segunda corrida omitió 35.
- [ ] Fuera de I4: DIOT, XML SAT, conciliación bancaria automática, export Aspel COI/NOI, cobros reales.

### 4.9 Facturación — Facture App (auditoría conexión, 2026-09-18)

Handoff para retomar integración OAuth / timbrado sin re-leer todo el código.

**Integración vigente**

| Pieza | Ubicación |
|-------|-----------|
| Cliente API + OAuth | `application/libraries/FactureApp.php` |
| Ambiente sandbox/prod | `application/config/factureapp.php` (`factureapp_ambiente`) |
| HTTP / timbrado / sync | `application/controllers/facturacion/Facturas.php` |
| Token persistido | `application/models/Facturacion/FacturaApiTokenModel.php` → tabla **`api_tokens`** (`provider = facture_app`) |
| Catálogos SAT (helper) | `application/helpers/facturacion_helper.php` |
| UI dashboard | `application/views/facturacion/dashboard.php` (`$conectado` = hay `access_token` en BD) |
| Callback OAuth | `{base_url}facturacion/Facturas/callback` |
| Conectar / desconectar | `Facturas/conectar`, `Facturas/desconectar` |
| Probe CLI (sin timbrar) | `php index.php facturacion/Facturas/cli_probe` |

**Ambiente activo:** `config/factureapp.php` → `factureapp_ambiente = sandbox` (`app.facture.com.mx`). Producción: cambiar a `produccion` (`app.micontador.mx`) y **re-OAuth** (`conectar`). Secretos solo en ese config (**no** en docs).

**Estado de conexión**

- [X] Registro OAuth en BD: **sí** (`api_tokens`, `user_id` NULL = token global del sistema).
- [X] Última autorización guardada: **2026-02-04** (`updated_at` del registro).
- [X] `expires_in` almacenado: **365**; `refresh_token` en BD **sin** flujo automático (reautorizar con `conectar`).
- [X] Probe CLI **2026-09-24**: `ok: true`, ambiente `sandbox`, mensaje *Petición satisfactoria* (`cli_probe`).

**Datos locales (`facturas`) — recontado 2026-09-28**

- **9** filas con `folio_fiscal` (UUID). **8 Emitida** + **1 Cancelada** (`id=6`, `F-OV-2025-0015`). Contabilidad I4 → **7** pólizas `origen=facturas` (las pólizas ya generadas no se recalculan solas).
- **API real (indicio):** `id=7` folio `1770318424`, sin `orden_venta_id`, con `pdf_path`/`xml_path` — es la **única** fila con PDF/XML local.
- **Snapshot POS:** los folios `F-OV-*` (`id=6` cancelada y `id=8–14` emitidas) **no** tienen PDF/XML — no timbrar como CFDI real sin auditar. Nota: hoy **todas** las filas traen `folio_fiscal` (UUID), por lo que el indicador “sin folio” ya no sirve para detectar el snapshot: usar el prefijo `F-OV-`.
- **8 de 9** filas tienen `orden_venta_id`; la única sin OV es `id=7`.

**Pendientes sugeridos**

- [X] Centralizar ambiente en `application/config/factureapp.php`.
- [X] Documentar renovación de token (comentarios en config + `cli_probe`).
- [ ] Checklist corte a producción: micontador + re-OAuth + smoke timbrado **solo** RFC de prueba hasta go-live.
- [ ] Diferidos: cron/lazy import, vínculo facturas ↔ obras/OC, envío correo PDF/XML.

### 4.5 Logística (si cabe en el sprint)
- [ ] API paquetería Tres Guerras — diseño en `doc/PLAN_ENVIOS_TRES_GUERRAS.md` (no improvisar).

### 4.0 Catálogo web oficial (I4 arranque)
Fuente: [categorías Chisa](https://www.chisarecubrimientos.com.mx/categorias) (22 familias: Recubrimientos, Pinturas, Preparadores, Pastas, Selladores).
- [X] Cruzar familias/REF de la tienda vs `productos` del ERP (2026-09-18, rama `iteracion-4`).
- [X] SKUs existentes: 84 fotos nuevas en `uploads/productos/web_*` (no se pisaron las 31+ que ya tenían imagen; **no** se tocó precio). Quedan ~379 sin foto (colores internos sin ficha en la tienda).
- [X] Familias ausentes dadas de alta (precio 0): COLOR GLASS, MARMO GOT, MARMO ROC.

### 4.6 Datos de negocio (no bloquean el arranque de I4; sí el PASO 3)
- [ ] `rendimiento_m2_por_kg`: 1/314 activas. Lista en `doc/entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md`.
- [ ] Contenido neto CUBETA/GALÓN; presentación de filas "(sin pres.)"; precio #476; `#83` ≡ `#214` EC-1; parafina CHISA PLUS.
- [ ] BUG-DATA-01: ~461 productos sin precio, placeholders, fotos. **No** cargar precios inventados. Fotos de 4.0 cubren las familias de la tienda, no todo el catálogo interno.

### 4.7 Entrenamiento 4 — Ventas/CRM documental (próximo)
- Carpeta prevista: `doc/entrenamiento_4/` (plantillas que cargará negocio).
- Alcance diagrama: **carátula**, **resumen general** (distinto de `pdf_resumen.php` técnico), **generador** y **catálogo de conceptos** por obra.
- **No implementar** hasta tener formatos firmados; reutilizar PDF técnico actual como posible “Anexo”.
- PASO 3 catálogo (rendimientos/neto): sigue en `entrenamiento_3/manifiestos/` si negocio desbloquea datos.

---

## 🔵 Iteración 5 — Reloj checador (NO ejecutar aún)

- [ ] **Nueva función** del reloj (definir alcance con negocio al abrir I5).
- [ ] **Auditoría solamente** (sin cambiar código ni config): comprobar que el ERP **está recibiendo** checadas reales (`api/ApiReloj`, `rh/RelojChecador`, proxy `doc/iclock/`). Tablas/logs de punches, última sync, dispositivos activos. Documentar hallazgo; no “arreglar” en I4.
- [ ] Referencia: `doc/API_RELOJ_CHECADOR.md`, `doc/iclock/GUIA_INSTALACION.md`.

---

## 🟡 Pendientes diferidos (no I4 salvo que se desbloqueen)

- [ ] Correos reales: OV a cliente, OC a proveedor, factura PDF/XML (UI facturación: “Enviar por Correo (Pendiente)” — no implementado).
- [ ] Facturación: cron/lazy import; vincular facturas a obras/OC. **Conexión Facture App:** ver § **4.9**.
- [ ] Smoke módulos: usuarios, permisos, bitácora, citas, calendario, RH (nómina ya existía) — usar checklist manual.
- [ ] Residuos demo: conservar `OV-TEST-001` / `OV-2026-0004` / “Empresa de Prueba S.A.” (guion demo).

---

## 💡 Mejoras futuras

- [ ] Exportar a Excel en tablas; bitácora de cambios; caché de permisos; super-administrador; cumpleaños; recordatorios de citas; calendario CRM; DTO de ViewData; logo en PDF; contraste de alerts/SweetAlert; contrato de usuario.

---

## 📚 Documentos vigentes (`doc/`)

| Documento | Uso |
|-----------|-----|
| `DOCUMENTACION_TECNICA.md` | Arquitectura y estándares |
| `REGLAS_TECNICAS.md` | Reglas para agentes |
| `cotizacion.md` | Requerimientos originales |
| `produccion.md` | Workflow venta → producción → entrega |
| `API_RELOJ_CHECADOR.md` | API reloj / ZKTeco (I5) |
| `SISTEMA_ALERTAS_NOTIFICACIONES.md` | Alertas |
| `GUIA_PRODUCCION_POST_IMPORTACION.md` | Operación post-import |
| `PLAN_ENVIOS_TRES_GUERRAS.md` | Diseño paquetería |
| `diagrama_general.png` | Mapa funcional vs oferta |
| `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` | Smoke manual + columna **Aud. P0** |
| `SMOKE_P0_AGENTE_EJECUTOR.md` | Instrucciones agente smoke P0 |
| `MODULOS_ESTADO_CHECKLIST.md` | Estado por módulo + tasklist cierre (prioridad: Compras) |
| `entrenamiento_3/manifiestos/decisiones_pendientes.md` | Decisiones de catálogo/BOM pendientes |
| `entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md` | PASO 3 rendimientos (negocio) |
| `entrenamiento_3/GUION_DEMO_CLIENTE.md` | Guion de demo |

Los prompts/checklists operativos de I3 se archivaron en git (commit previo a esta limpieza). No re-crearlos.

> Overhaul P1–P9 cerrado. I4 **no** reescribe módulos; reutiliza `explotar_bom_plano`, `calcular_insumos_para_proyecto`, `crear_preordenes_desde_faltantes`, `convertir_unidad_insumo`.

---

## Fase 5 · Obras y Presupuestos (V1) — 2026-09-29

**Rama `iteracion-5`. CERO push. CERO cambios en `main`.**

### Implementado (hecho)
- Migración idempotente `doc/MIGRACION_FASE5_OBRAS.sql` (A1–A10): `conceptos_obra`,
  `presupuestos_obra`, `presupuesto_obra_conceptos`, `concepto_apu_materiales`,
  `concepto_apu_cuadrillas`, `parametros_apu`, `obra_generadores`, `obra_generador_lineas`,
  `obra_revisiones_cuantificacion`, `precios_insumo_historial` + ALTER `obras.sucursal_id`,
  `sucursales.logo_marca_agua/texto_marca_agua`. Verificada 2 ejecuciones = 0 errores.
- 6 modelos nuevos en `application/models/Obras/`: `ConceptosObraModel`, `PresupuestosObraModel`,
  `ApuModel`, `GeneradoresModel`, `RevisionCuantificacionModel`, `ExportacionObraModel`.
- Ampliación `SucursalesModel::get_marca_agua()` + columnas de marca de agua.
- UI Ventas: `ObrasVentas::crear()`, `guardar_obra_ajax()` + endpoints AJAX de partidas, APU,
  generador y revisión. Vista `ventas/obras/crear.php` + pestañas reales en `detalle.php`.
- 8 vistas de impresión C1–C8 (`application/views/obras/`) + exportación Excel/PDF con marca de agua.

### Smoke verificado (Fase F)
- APU Z-01C = **306.66018163809525** (diff 0.000000000000 exacto, tolerancia ±0.01). Hallazgo
  de revisión: `concepto_apu_materiales.costo_unitario/importe` era `DECIMAL(12,2)` y **truncaba**
  el adhesivo real 7.9802 $/kg → 7.98 (0.55 kg × 7.9802 = 4.38911 exacto de fuente); con escala 2
  daba PU 306.66004523809522 (diff 0.0001364). Se amplió a **DECIMAL(12,4)** (CREATE +
  upgrade idempotente en `ApuModel::asegurar_infraestructura()` + `round(...,4)` en ambos
  `agregar_material()`) y se re-verificó el control exacto vía `index.php smaketmp apu`.
  Material exacto: 0.13×381.60 (49.608) + 0.55×7.9802 (4.38911) + 0.01×39.80 (0.398) = 54.39511.
- Presupuesto control 16550: subtotal 2,015,160.00 / IVA 322,425.60 / TOTAL 2,337,585.60 (exacto).
- Generador SUMA+ACUMULADO=TOTAL y "aplicar a partida" actualiza cantidad. ✔

### DECISIONES TOMADAS (documentar)
1. **Fórmula APU real (fuente de verdad = archivos)**: costo_directo = material + MO + IMSS + RCYV
   + HERRAMIENTA; **ISN se calcula y se muestra pero NO suma** (igual que los archivos IMSS y ZOCLOS);
   indirecto se aplica sobre costo_directo. Los archivos usan **HERRAMIENTA 9% e INDIRECTO 24%** para el
   proyecto ZOCLOS (→ 306.66). El §3.3 del prompt decía "5% / 34%" (corresponde al proyecto IMSS, control
   304.90); por eso `parametros_apu` queda **parametrizado** (NO hardcodeado) y se sembró 9%/24% para
   reproducir el control Z-01C. Ver `doc/REGLAS_TECNICAS.md` §Fase5.
2. Material Z-01C CORREGIDO en revision: subtotal material exacto = 54.39511
   (LOSETA 0.13x381.60 = 49.608 + ADHESIVO 0.55x7.9802 = 4.38911 + JUNTA 0.01x39.80 = 0.398).
   El LOSETA 54.40 del prompt es el SUBTOTAL MATERIAL, no una 4.a linea (el prompt sumaba 108.79 por
   doble conteo).
3. PDF server-side con mPDF RESUELTO: composer require mpdf/mpdf:^8.2 instalado SIN advisories
   (No security vulnerability advisories found). ExportacionObraModel::exportar_pdf() usa mPDF 8.x
   como motor preferido (carta, UTF-8, marca de agua de sucursal nativa en TODAS las paginas) y conserva
   el fallback HTML + html2pdf.js si mPDF no estuviera disponible. Smoke CLI smaketmp pdf = OK
   (engine=mpdf, %PDF-1.4, 73437 bytes, 2 paginas).
4. **Marca de agua Excel**: encabezado de página `setOddHeader` (PhpSpreadsheet no soporta marca de agua
   diagonal nativa). En PDF/HTML: div diagonal semitransparente + logo.

### PENDIENTES detectados (NO inventar precios)
- **P1 (ya auditado) — brecha Kg↔Cubeta**: 462 productos en `unidad_venta='Kg'` vs 35 en `Cubeta`;
  128 insumos en `Kg`. Productos CHISA-GLASS-REF-* (≈50 filas) en Kg con `precio_venta=0.00`.
  Decisión de negocio pendiente del usuario.
- **Precios faltantes**: 464/497 productos (93%) sin `precio_venta` y 220 sin `costo_produccion`.
  Insumos de loseta/adhesivo/mortero/tablaroca/masking/poliuretano NO existían; se dieron de alta 6 con
  precio 0.00 (`LOSETA-CERAMICA-60X120`, `ADHESIVO-INTERCERAMIC-SELECT`, `JUNTA-SIN-ARENA`,
  `POLIURETANO-COMEX`, `MASKING-TAPE`, `TABLAROCA-DURROCK`). → **PENDIENTE CAPTURA DE PRECIO**.
- `SELLADOR-INICIAL` (476) tiene `precio_venta = NULL`.
- **16619 HOSPITAL SANTIAGO PAPASQUIARO.pdf** = escaneado SIN capa de texto → **PENDIENTE OCR/captura**.
- Carga inicial de conceptos ejecutada (26 códigos de §3.2). Los APU de cada concepto se capturan vía UI.
- **5.ª pestaña Documentos en Ventas (HECHO)**: `presupuestos_tabs.php` + endpoints
  `ObrasVentas::documentos_ajax/subir_documento_ajax/eliminar_documento_ajax` (misma regla de
  subida que `obras/Obras::subir_archivo_ajax`, expuesta en Ventas para no exigir permisos del
  módulo Obras). Smoke HTTP 200 + JSON `{"success":true}`.
- **Listado real de 38 proveedores químicos (HECHO)**: `LISTA DE PRODUCTOS QUIMICOS PROVEEDORES ERP.xlsx`
  (filas 6–43: PROVEEDOR col C, RFC col G; producto/presentación cols A/B). `importar_excel_ajax()`
  ahora detecta ese layout (sin romper la plantilla A–O) y carga producto/presentación en
  `observaciones`; `tipo_proveedor='Materia Prima'`. **Idempotente por RFC** (segunda corrida = 38
  omitidos). Smoke CLI `smaketmp proveedores` = OK. Domicilios del XLSX = sólo email/teléfono de
  contacto → `direccion/ciudad/estado/cp=''` y `pais='México'`.

### CIERRE FASE G — validación pre-demo (2026-09-29)
Corrida de validación de los 4 módulos + exportaciones. Hallazgos y correcciones:

- **Hallazgo (faltante real de UI):** los endpoints `exportar_pdf_presupuesto`, `exportar_excel` e
  `imprimir_presupuesto` respondían 200 pero **ninguna vista los enlazaba** (0 coincidencias en
  `application/views/`): era imposible exportar sin escribir la URL a mano. Corregido:
  - `views/obras/partials/presupuestos_tabs.php` → barra **"Exportar con marca de agua"**
    (`#barraExportar`, visible solo con presupuesto seleccionado) con menú **PDF C1–C8**, menú
    **Excel C1–C8**, **"Libro con las 8 hojas"** (`?hojas=`) y **"Vista para imprimir"**.
    JS: `exportarObraPdf()`, `exportarObraExcel()`, `exportarObraExcelTodas()`, `VISTAS_EXPORT`.
  - `views/obras/detalle.php` + `controllers/obras/Obras.php::detalle()` → botón **"Exportar
    presupuesto"** (dropdown por presupuesto: PDF C1, PDF C8, Excel 8 hojas, vista imprimible);
    `detalle()` ahora entrega `response['presupuestos']` (`PresupuestosObraModel::listar`).
- **Evidencia exportaciones (sesión HTTP real, presupuesto 8 / PRES-00001):**
  - 8/8 **PDF** `%PDF-` (~58–87 KB, 2 páginas) con `ExtGState /CA 0.08 /ca 0.08` → marca de agua
    nativa de mPDF presente en **todas** las páginas.
  - 8/8 **XLSX** `PK` con `oddHeader = "&C&8Sucursal: Matriz CDMX"`; `?hojas=` de las 8 vistas →
    libro de **8 hojas** (18,854 B) nombres `Presupuesto…Datos_obra`.
  - `get_marca_agua()` confirmado con doble fallback: texto → `Sucursal: <nombre sucursal>`;
    logo → `configuraciones_empresa.logo`. Por eso la marca sale aunque
    `sucursales.texto_marca_agua` esté `NULL` (única sucursal activa = Matriz CDMX).
- **Pestaña Documentos** verificada end-to-end: `documentos_ajax` / `subir_documento_ajax` /
  `eliminar_documento_ajax` (tabla real: **`obras_archivos`**, no existe `documentos_obra`).
- **Higiene:** `uploads/tmp/` (tempDir de mPDF) añadido a `.gitignore` + `uploads/tmp/.htaccess`
  (`Require all denied`; el host ya devolvía 403). Folio de prueba `SMOKE-PDF-1` renombrado a
  **`PRES-00001`** para que el documento cliente no salga con folio de smoke test.
- **Sin hallazgos:** RH/Compras/Ventas/Obras HTTP 200 sin `Fatal error`/`Parse error`; `composer
  audit` = no advisories; `php -l` limpio en archivos tocados; PHP 8.3.33; `application/logs/`
  sin errores nuevos.
- **Pendiente de decisión de negocio (no bloquea demo):** no existe un "archivo/reporte de estatus
  de obra" aparte — lo que existe es el badge de estatus + avance en `obras/Obras/detalle`, el PDF
  **C8 Datos de Obra Contratada** y la pestaña Documentos. Si el cliente espera un reporte de
  estatus/avance imprimible (tipo acta), habría que especificarlo como vista C9.

### Reglas de repo cumplidas
R1 branch iteracion-5 ✔ · R2 español ✔ · R3 commits por fase ✔ · R4 sin Cli_* ✔ · R5 docs ✔ ·
R6 ProduccionModel intacto ✔ · R7 git limpio ✔ · R8 .gitignore entrenamiento_4 ✔ · R9 precios 0.00 + TODO ✔.


---

### SEGUIMIENTO 2026-09-29 (auditoría pre-demo) — marca de agua por sucursal a medias y permisos del login demo

Revisión de **solo lectura** (sin `INSERT`/`UPDATE`: no se tocó datos). Se documenta y se comitea el
WIP que estaba suelto en el árbol de trabajo.

> **Corrección a la sección “Reglas de repo cumplidas” de arriba:** `R7 git limpio ✔` era inexacto —
> había **3 archivos modificados sin commitear** (los del WIP de abajo). Con este commit el árbol
> vuelve a estar limpio.

#### 1. Marca de agua por sucursal: el backend ya resuelve por sucursal, falta **la UI**

Verificado en código, no es suposición: `ExportacionObraModel::get_marca_agua($presupuesto_id)`
(líneas 46-50) resuelve la sucursal **del presupuesto** con
`PresupuestosObraModel::resolver_sucursal_id()` y la pasa a `SucursalesModel::get_marca_agua($sucursal_id)`,
que aplica el doble fallback ya conocido (`texto_marca_agua` → `Sucursal: <nombre>`;
`logo_marca_agua` → `configuraciones_empresa.logo`). Es decir: **la marca ya es por sucursal**; lo que no
existe es la forma de **capturarla** sin recurrir a SQL.

WIP heredado (ahora commiteado; inocuo en producción, nada de esto se ejecuta desde la UI):

| Archivo | Qué hace hoy | Riesgo |
|---------|--------------|--------|
| `models/Ventas/SucursalesModel.php` | `listar_todas()` (activas e inactivas) y `actualizar()` con lista blanca `nombre/direccion/telefono/estatus/texto_marca_agua/logo_marca_agua`, cadena vacía → `NULL` para que aplique el fallback | **Nulo**: ningún controlador/vista los llama todavía (código muerto) |
| `controllers/usuarios/GestionUsuarios.php::empresa()` (línea ~720) | Carga `Ventas/SucursalesModel` y entrega `'sucursales' => listar_todas()` a la vista | **Nulo**: `views/usuarios/empresa/main.php` **no referencia** `sucursales` ni `marca_agua` (0 coincidencias) → no hay formulario |
| `views/obras/partials/print_head.php` | `$folio = $presupuesto ? … : ($folio_custom ?? 'SIN-FOLIO')` | **Nulo**: indefinida protegida por `??`, sin warning |

Para cerrarla:

- [ ] Panel en `views/usuarios/empresa/main.php`: una fila por sucursal con
      `marca[<id>][texto_marca_agua]` + carga de `logo_marca_agua` + guardar (usar `showErpToast()`,
      Bootstrap 5 y el layout de tarjetas ya usado en esa pantalla).
- [ ] Endpoint AJAX/POST en `GestionUsuarios` que valide y delegue en `SucursalesModel::actualizar()`;
      el logo debe ir al mismo destino y con las mismas validaciones de subida que usan las demás
      cargas del sistema (`uploads/`, no `uploads/tmp/`).
- [ ] Definir qué permiso protege el guardado (la pantalla hoy cae bajo `protected $modulo = 'Administradores'`,
      sin `requiere_permiso()` específico).
- [ ] Smoke: fijar texto en Matriz CDMX → exportar C1 en PDF y Excel y ver el texto nuevo; vaciar →
      debe regresar el fallback `Sucursal: Matriz CDMX`. Idealmente con **dos** sucursales activas y
      dos presupuestos de sucursal distinta para probar que la marca no se mezcla.


#### 2. El login de la guía (`presentacion@chisa.mx`) no tiene permisos de Obras

`admin.id = 9` tiene 31 privilegios activos pero **0** filas con `permiso LIKE 'obras_%'`:

| admin | usuario | `obras_*` activas | `compras_autorizar_preordenes` · `compras_pagos` · `compras_servicios_recurrentes` | `admin_simular_alertas` | activos |
|-------|---------|------------------:|-----------------------------------------------------------------------------------|------------------------|---------|
| 1 | `soporte2@especialistasweb.com.mx` | 5 | ✅ | ✅ | 79 |
| 6 | `ggeneral@chisarecubrimientos.com.mx` | 5 | ❌ (sólo `compras_ordenes_*` + `compras_recepcion`) | ❌ | 67 |
| 7 | `facturacion@chisarecubrimientos.com.mx` | 5 | ❌ (idem) | ❌ | 67 |
| 9 | `presentacion@chisa.mx` | **0** | ✅ | ✅ | 31 |

Consecuencia medida (HTTP): `/ventas/ObrasVentas`, `/obras/Obras`, `/obras/Obras/detalle/1`,
`ventas/ObrasVentas/exportar_pdf_presupuesto/8/presupuesto` y `…/exportar_excel/8/presupuesto`
responden **`307 → /deny`** con el login 9, mientras dashboard, perfil, usuarios, roles, bitácora,
simulador, RH, reloj, proveedores, servicios recurrentes, órdenes de compra e insumos responden 200.
El menú **CRM Ventas → Obras** se muestra igualmente porque `views/layouts/sidebar.php:140` **no** filtra
por permiso → en vivo se ve un “Acceso Denegado” al hacer clic.

Ni `ventas/ObrasVentas` ni `obras/Obras` usan `requiere_permiso()`: sólo `protected $modulo = 'Obras'`,
así que **basta un** `obras_*` (`obras_consult`) para habilitar sección + exportaciones. **Decisión de
la demo del 29-Sep: no se tocó la BD**; se presentó con el admin id 1 (el único con cobertura completa).
Pendiente si se quiere seguir usando el login de demo: alta de `obras_*` en `privilege` para el id 9
ojo con `UserModel`, que al guardar **reemplaza** el set completo de privilegios del usuario.

#### 3. Re-verificación de exportaciones sobre el WIP (solo lectura)

- `php -l` limpio en los 3 archivos del WIP; `application/logs/` sin errores nuevos; mPDF **8.3.1**.
- CLI `php index.php ventas/ObrasVentas/exportar_pdf_presupuesto 8 presupuesto` → PDF reconstruido con
  `qpdf`: **2 páginas** y `/ExtGState` con `/GS2 → /CA 0.08 /ca 0.08` (watermark nativo de mPDF activo).
  `…/exportar_excel 8 presupuesto` → `.xlsx` válido con `sheet1.xml` lleno. Coincide con la evidencia
  HTTP de la Fase G (8/8 PDF, 8/8 XLSX).
- **Trampa al validar por CLI:** con `ENVIRONMENT = development` los *deprecation notices* de PHP 8.3 del
  core de CI3 (`Creation of dynamic property CI_URI::$config`, `CI_Router::$uri`, …) se imprimen **antes**
  del binario, así que el archivo no arranca con `%PDF` (en la prueba, en el offset 24 702) y `unzip`
  reporta “extra bytes at beginning” en el `.xlsx`. **Es ruido de CLI**: por HTTP el cuerpo sale limpio.
  Al validar por CLI, recortar desde `%PDF`/`PK` o apagar `display_errors`.
- `ENVIRONMENT` se define en `index.php:56` como `development` → el **2FA no pide código** al entrar desde
  otra IP, así que el login de demo desde laptop no se traba.

### Reglas de repo (esta entrada)
R1 branch `iteracion-5` ✔ · R2 español ✔ · R3 commit por cambio ✔ · R4 sin controladores `Cli_*` (la
validación por CLI fue efímera, nada quedó en `application/controllers/`) ✔ · R5 docs (`demo.md` + esta
entrada) ✔ · R6 `ProduccionModel` intacto ✔ · R7 **git limpio de verdad** tras este commit ✔.

