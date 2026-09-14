# PROMPT — Continuación QA UI (2ª pasada, Iteración 3) → para modelo rápido con navegador

> Pégalo completo en un chat nuevo de un agente con navegador **y acceso a shell al repo**
> (`/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html`).
> Al terminar, escribe el informe en la ruta indicada en **ENTREGABLES** para que el agente anterior lo lea.

---

## 1. Contexto y misión

Eres un QA manual con navegador sobre el ERP de CHISA Recubrimientos (CodeIgniter 3 + MySQL,
**PRODUCCIÓN EN VIVO**). Misión: **terminar la 2ª pasada de validación de UI** de la iteración 3
antes del PR `iteracion-3 → main`, y dejar un informe legible por otro agente.

- URL: `https://erp.chisarecubrimientos.com.mx`
- Commit en prueba: **`1bdec68`** (rama `iteracion-3`; es la copia que sirve el sitio). No hagas commits.
- Usuario QA: `soporte2@especialistasweb.com.mx` (EHWEB). Pide las credenciales al humano (no la cuenta demo). Sin 2FA.
- `ENVIRONMENT=development`: los avisos `A PHP Error was encountered / Severity: 8192 / Creation of dynamic property…`
  son **ruido preexistente de PHP 8.3, NO bugs**. Error real = `Fatal error`, `Uncaught`, HTTP 500, pantalla en blanco
  o acción que no responde.
- DevTools (Network + Console) abiertas toda la sesión.

## 2. Estado ya verificado (NO repetir; úsalo como base)

Ya ejecutado y documentado en `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md` § Segunda pasada:

| Caso | Resultado |
|---|---|
| GL0 login/dashboard/tema | ✅ |
| BUG-UI-02 (tab Entregas `get_entregas_obra_ajax`) | ✅ **no reproducible** (200, ~90 ms) |
| BUG-UI-01 / BUG-UI-03 (búsqueda Productos) | ✅ **no son bugs** (búsqueda substring literal por código/nombre/alias/categoría) |
| A3 Excel/Imprimir Clientes · A4 `TEST-QA-CLIENTE-01` | ✅ (cliente CLI-00002 quedó **Inactivo**) |
| C1b–C5c ciclo obra `TEST-QA-UI-OBRA-01` (OB-00006) | ✅ bloqueo sin rendimiento, cálculo kg/cubetas, preórdenes PRE-2026-0004/5/6, PDF 5 hojas, limpieza completa |
| C5b entrega desde Almacén | ⚠️ bloqueada por **stock 0** del producto TEST (el E2E de entrega ya se validó en 1ª pasada) |
| D1 / D1b búsquedas catálogo | ✅ |
| D2 formulación #204 | ✅ V5 activa (form#968) con AEROSIL 200 (#118) |
| D7 `/produccion/Lotes` | ❌ **BUG-UI-04** (500) |
| `/almacen/Inventario` | ❌ **BUG-UI-05** (500, preexistente en `main`) |

Evidencias en disco: `doc/entrenamiento_3/evidencias_ui/*.txt` (ya hay 7 archivos; **no los borres**).

## 3. Autorización vigente

- **Autorizado:** todo el ciclo TEST-QA — obra completa, cotización → confirmación → cobro/entrega TEST,
  pesaje/completada E2E, OC/preorden/recepción, nómina de periodo **futuro** TEST, altas y ediciones TEST.
- **Prohibido:** enviar correos/WhatsApp (E8 solo *preview*; todo botón "Enviar" → SKIP "requiere autorización humana"),
  CFDI real, pagos/cobros de montos reales, importaciones masivas, modificar catálogos/precios existentes,
  activar/desactivar formulaciones, y **tocar datos reales** (OB-00001, OB-00002, OVs reales, nómina real,
  clientes/proveedores reales más allá de referenciarlos en un documento TEST).
- **Reglas duras:** prefijo `TEST-QA-` en todo lo que crees; cantidades mínimas (1 línea / 1 unidad / 1 registro);
  **prohibido DROP/TRUNCATE/DELETE** — la limpieza es por UI (cancelar / rechazar / inactivar / eliminar obra).
  Si algo **no se puede limpiar por UI, NO lo crees**: márcalo SKIP con la razón.
  No cotices los productos #475–#503 (precio NULL): solo valida que sean buscables.
- Un bloque a la vez. Si un 500/pantalla en blanco bloquea, detén ese bloque, documenta y sigue.

## 4. Atajos técnicos ya descubiertos (ahórrate tiempo)

- **CSRF deshabilitado**: el token se llama `ci_csrf_token` y va vacío. Los `POST` de la app funcionan sin token.
- **Endpoints DataTables server-side** (útiles para leer catálogos sin abrir pantalla, desde la consola del navegador
  con `fetch(..., {credentials:'include'})`, params `peticion=ajax&draw=1&start=0&length=300` + filtros):
  - Insumos + **stock**: `POST /compras/Insumos/lista_ajax` (columna 5 = `stock_actual / stock_minimo`).
  - Productos: `POST /produccion/Productos/lista_ajax` (col 0 código, 2 nombre, 5 tipo, 6 stock, 7 precio, 8 estatus).
  - Clientes: `POST /ventas/Clientes/lista_ajax`.
- **Stock por producto** también se ve en `Almacén > Entregas` al pulsar "Entregar" (modal muestra `Stock`),
  porque **Inventario está roto (BUG-UI-05)**.
- **POS**: el buscador solo dispara con **eventos de teclado reales** (`keyup`). Si tu navegador inserta texto sin
  eventos (p. ej. `insertText`), el grid no se actualiza: después de escribir, pulsa una tecla física
  (p. ej. `Backspace` + la última letra) y verifica que `#grid_productos` cambió.
  Ojo: `views/ventas/pos/main.php` define **dos veces** `buscarProductos()`; la 2ª gana y **no** oculta "Más Vendidos"
  al buscar (BUG-UI-06, cosmético, ya registrado).
- **Lotes no es verificable por pantalla** (BUG-UI-04). Para ver un lote generado usa
  `Producción > Fabricación Dashboard > detalle` (sección "Lotes generados") y/o `produccion/Lotes/consultar`
  con el folio/código de barras; o `produccion/Dashboard/etiqueta_lote/{lote_id}`.
- **Nómina**: no hay envío de correo en el ciclo G2–G7; el periodo debe ser **futuro**.

## 5. Hallazgo bloqueante para D4–D6 (léelo antes de producir)

Con los endpoints reales se verificó:

- 144 insumos: **101 en/bajo mínimo, solo 43 con stock > 0**.
- Los 3 insumos de `#204` (EXXOL D-40, PLIOWAY E-CH, AEROSIL 200) están en **0** → #204 **no se puede producir ni pesar**.
- De ~260 formulaciones evaluadas, **solo 5 tienen stock suficiente** para pesar 1 lote:

| Producto | ID | Formulación | Notas |
|---|---|---|---|
| **CHISA GLASS REF 308** | **22** | **f684 (V4 activa)** | Óxido de Hierro Amarillo (200 kg) · Pasta Colorante Verde (80 kg) · Negro de Humo (150 kg) ← **usa este** |
| CHISA GLASS REF.8750 W | 84 | f559 | — |
| CHISA GLASS REF.CA-1012 | 36 | f36 | — |
| CHISA GLASS REF.CA-1018 | 35 | f35 | — |
| Pintura Epóxica Azul 19L | 10 | f10 | — |

Los 5 tienen **precio $0.00 y stock 0** → la OV TEST quedará en **$0.00** (aceptable, es dato TEST).
`CHISA GLASS MICRO` (#3) sí tiene precio ($500) pero su formulación tiene 1 faltante (BLANCO) → producción bloqueada.

Receta para (re)encontrar productos producibles, desde la consola del navegador:
`get_productos_base_ajax` (lista) → por producto `get_historial_formulaciones_ajax` (elige la `es_activa=1`) →
`calcular_insumos_ajax {formulacion_id, cubetas:1}` → sirve si `datos.hay_insumos_faltantes === false`.

## 6. Alcance pendiente — ejecutar EN ESTE ORDEN

### A) D4–D6 · Pesaje / completada E2E (1 unidad TEST del producto #22)

1. **POS** (`/ventas/Pos`): cliente `Empresa de Prueba S.A.` (residuo TEST existente, id 9), producto
   `CHISA GLASS REF 308` ×1, `Observaciones` = `TEST-QA-UI-OV-01`. **Guardar Cotización** → anota folio y
   confirma que **no** se generó preorden (B4).
2. **Ventas > Órdenes de Venta** (`/ventas/Ordenes`): abre la OV, **Confirmar** → anota folio y si generó
   preórdenes (con insumos suficientes **no** debería; documenta el resultado real) (B5).
3. **Producción > Fabricación Dashboard** (`/produccion/Dashboard`): localiza la OV y abre
   `/produccion/Dashboard/detalle/venta/{id}`. Verifica EN ORDEN:
   - intentar **Completada** sin pesaje → debe **BLOQUEAR** con mensaje de insumos/pesaje (captura response
     de `produccion/Dashboard/actualizar_estatus_ajax`);
   - **Confirmar pesaje y descontar** (`confirmar_pesaje_ajax`) con las cantidades teóricas → debe descontar
     insumos **una sola vez** (anota el folio/registro `PESAJE-*` si aparece);
   - **segundo intento de pesaje** → debe **fallar** (ya consumido);
   - **Completada** → debe generar **lote** + **entrada a PT** sin volver a descontar insumos.
   - Antes de confirmar el pesaje, verifica si existe forma de **cancelar/revertir por UI**; si no existe,
     márcalo en observaciones (la operación está autorizada).
4. **Ventas** (`/ventas/Ordenes`): **cobrar/entregar** solo la OV TEST → el stock del producto debe **bajar**
   y los insumos **no** deben moverse otra vez (B6). Si el total $0.00 bloquea el cobro, documéntalo con
   evidencia (Network) y marca esa parte SKIP.
5. **Limpieza por UI**: cancela entrega, OV y cotización TEST (lista los folios). Si la OV ya no es cancelable
   tras el cobro, documéntalo explícitamente.

### B) B7–B8 · Ventas restantes
- `/ventas/Descuentos`: CRUD mínimo TEST (crear `TEST-QA-DESC-01` → editar → desactivar/eliminar por UI).
- `/ventas/ObrasVentas`: listado + detalle (solo lectura).

### C) E3, E5, E8 · Compras
- E3: `TEST-QA OC` de **1 línea** → borrador + PDF; valida subtotal/IVA/total contra el PDF.
- E5: preorden TEST → **autorizar** → OC generada (verifica que **no** se duplique); recepción **parcial**
  del TEST si aplica (si no hay forma de limpiarla por UI, no la hagas y marca SKIP con razón).
- E8: **solo preview** de correo/WhatsApp (NO enviar → SKIP "requiere autorización humana").
- Limpieza: cancela/rechaza OC y preorden TEST; lista folios.

### D) G2–G9 · Nómina (periodo **futuro** TEST, sin pagar)
- Nueva nómina semanal futura → **Borrador ($0)** → **Calcular** (valida `sueldo = diario × días` en 1 empleado)
  → **recalcular** (no duplica) → comidas/horas extra inline → **restaurar** → **Cancelar** (motivo ≥10 caracteres).
- G9: verifica la **campana de nómina por vencer** en el header.

### E) Móvil 390×844 (pasada rápida)
Clientes, Órdenes de Venta, POS, Obras detalle (incl. tab Entregas), catálogo Producción, RH listado.

## 7. ENTREGABLES (obligatorios)

1. **Evidencias en disco** en `doc/entrenamiento_3/evidencias_ui/` con nombre `<ID>-<fecha>-<descriptor>.txt|png`
   (URL, status, Content-Type, tiempo, primeras líneas del body + captura). Al final corre
   `ls -la doc/entrenamiento_3/evidencias_ui/` y **pega la salida** en el informe.
2. **Actualizar** `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md`: cambia a ✅/⚠️/❌/SKIP las filas pendientes
   (`D4–D6`, `B4–B8`, `E3/E5–E8`, `G2–G9`, móvil) y añade bugs nuevos con ID, módulo, severidad, pasos,
   esperado vs real y evidencia.
3. **Escribir el informe** en `doc/entrenamiento_3/evidencias_ui/INFORME_QA_UI_2A_PASADA_CONTINUACION.md`
   con esta estructura exacta (para que otro agente lo lea rápido):
   - `## Cabecera`: usuario, fecha/hora CDMX, commit, viewport, estado de DevTools.
   - `## Casos ejecutados`: tabla `ID | Acción | Esperado | Resultado ✅/⚠️/❌/SKIP | Evidencia | Observación`.
   - `## Bugs nuevos`: ID, módulo, severidad, pasos, esperado vs real, evidencia. **No cuentes** deprecations
     PHP 8.3, #475–#503 sin precio, formulaciones inactivas, ni los residuos OV-TEST-001 / OV-2026-0004 /
     `Empresa de Prueba S.A.`
   - `## Datos TEST-QA creados y limpieza`: folio/ID + estatus final (y qué quedó sin poder limpiar).
   - `## Pendientes / bloqueos` con la razón exacta.
   - `## Evidencia en disco`: salida del `ls -la`.
   - `## Resumen JSON` (**obligatorio**, último bloque del informe, en un fence ```json): 
     `{"commit":"1bdec68","casos":{"ok":N,"warn":N,"fail":N,"skip":N},"bugs_nuevos":[{"id":"","sev":"","modulo":"","resumen":""}],"datos_test_qa":[{"folio":"","modulo":"","estatus_final":""}],"pendientes":[""]}`
4. En el chat: resumen de **máximo 10 líneas** + los ❌/⚠️ con su evidencia.

## 8. Plantilla de cabecera del informe

```text
QA UI 2ª pasada (continuación) — ERP CHISA · commit 1bdec68 (iteracion-3)
Usuario: soporte2@especialistasweb.com.mx (EHWEB) · Fecha: YYYY-MM-DD HH:MM CDMX · Viewport: 1440×900 (+390×844 móvil)
DevTools: Network + Console activas toda la sesión · Ruido PHP 8.3 (Severity 8192) ignorado
```
