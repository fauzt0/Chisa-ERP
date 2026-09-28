# Checklist manual — módulos iterados + entrenamiento_2

**Fecha:** 25 ago 2026 · **addendum I4:** 18 sep 2026 · **auditoría diagrama:** 24 sep 2026  
**URL:** `https://erp.chisarecubrimientos.com.mx/`  
**Login sugerido:** el de presentación vigente (el de QA `soporte2@…` puede estar desactualizado).  
**Leyenda:** ✅ / ⚠️ / ❌ / SKIP · **Aud. P0** = valida gap P0 de `doc/TODO.md` § Auditoría diagrama.

**Basado en:** `CHECKLIST_PRUEBAS_CRM_VENTAS.md`, `PRUEBAS_MANUALES_RH_2026-08-10.md`, `CHECKLIST_PRUEBA_GENERAL_NOMINA_2026-08-17.md`, `CHECKLIST_MANUAL_POST_E2E_NOMINA.md`, `AUDITORIA_OVERHAUL_PRODUCCION_2026-08-20.md`, `PLAN_ITERACION_PROVEEDORES.md`, `VERIFICACION_MODULO_COMPRAS.md`, `diagrama_general.png`, `cotizacion.md`.

### Orden sugerido (P0 auditoría)

| P0 | Ítems | Tema |
|----|-------|------|
| #1 | **B3, B4, B5, B6** | POS, IVA, entrega PT |
| #2 | **E5, E6** | Preorden→OC, recepción stock |
| #3 | **D3, D4, D5, D6** | Dashboard, pesaje, Completada→PT |
| #4 | **I1** + `cli_probe` | Facture conectado (sin timbrar real salvo decisión) |

Luego en paralelo: **C** (obras), **E1–E4**, **F–G**, **I2–I4**. P0 #5 autofactura y pasarela: sin filas (desarrollo nuevo).

---

## Reglas (servidor de producción / desarrollo en vivo)

1. Prefijo **`TEST-QA-`** en comentarios/folios de prueba. Cantidades mínimas (1 cubeta / 1 línea).
2. **No** pagar nómina real. **No** cobrar/entregar OV de cliente real. **No** DROP/TRUNCATE.
3. Cancelar lo TEST al terminar (motivo ≥ 10 caracteres). Anotar folios en la hoja de resultados.
4. Completada de producción **sin** pesaje debe fallar; segundo pesaje debe fallar.

---

## A. CRM — Clientes (`/ventas/Clientes`)

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| A1 | Listado + paginación + búsqueda | Tabla completa, filtra | |
| A2 | Filtros Tipo / Estatus / Saldo | Filtran | |
| A3 | Exportar Excel / Imprimir | Descarga / vista print | |
| A4 | Nuevo / editar / offcanvas (Info, Ventas, Cotizaciones) | CRUD OK | |
| A5 | Plantilla Excel + carga masiva (archivo TEST pequeño) | Inserta/omite duplicados RFC | |
| A6 | Convertir cotización → venta (solo TEST) | Cambia a confirmada | |

---

## B. CRM — Ventas / POS / Órdenes

| ID | Acción | Esperado | Aud. P0 | Resultado |
|----|--------|----------|---------|-----------|
| B1 | `/ventas/Ordenes` listado, filtros, Excel | OK | | |
| B2 | Detalle OV + formatos Factura / Remisión / Moderno | Abren | | |
| B3 | `/ventas/Pos` buscar producto, ticket, cliente | UI OK | **#1** | ✅ 24-sep |
| B4 | Guardar **cotización** TEST con fabricado | Aviso faltantes si aplica; **0 preorden** | **#1** | ✅ OV-2026-0010 |
| B5 | Confirmar cotización / compromiso TEST | Preorden Pendiente `origen=venta` solo si faltantes | **#1** | ✅ PRE-2026-0007 |
| B6 | Cobrar mostrador TEST | Si PT ≥ cantidad: baja PT; insumos **no**. Si PT &lt; cantidad: **rechaza** (no negativo) | **#1** | ✅ 28-sep-2026 CLI+BD (transacción revertida) |
| B7 | `/ventas/Descuentos` CRUD mínimo + visible en POS | OK | | |
| B8 | `/ventas/ObrasVentas` listado + detalle | Carga | | |
| B9 | POS: selector sucursal (Matriz CDMX) + alta caja TEST | OV lleva `sucursal_id`; stats por caja | | |
| B10 | POS: SKU precio $0 (p. ej. REF 308) no entra al ticket ni al POST | Mensaje; precio de catálogo | | ✅ 28-sep-2026 `validar_precios_pos()` (producto 10) |

---

## C. Obras (`/obras/Obras`)

| ID | Acción | Esperado | Aud. P0 | Resultado |
|----|--------|----------|---------|-----------|
| C1 | Listado obras | Carga | | |
| C2 | Obra borrador: producto **sin** rendimiento ni override | Bloquea; **no** calcula con 1.0; **no** preorden | | |
| C3 | Producto **con** rendimiento + m² (ej. MASA ROCA 319) | kg/cubetas/insumos coherentes | | |
| C4 | Aprobar solo obra TEST con faltantes | Preorden `origen=obra`; generar OV no duplica | | |
| C5 | Cancelar/limpiar obra TEST | Folio anotado | | |

---

## D. Producción

| ID | Acción | Esperado | Aud. P0 | Resultado |
|----|--------|----------|---------|-----------|
| D1 | `/produccion/Productos` buscar `hospital` / `ROCA` / alias | Filtra AND | | |
| D2 | Ver formulación (no Guardar salvo TEST consciente) | Cliente, comentarios, rendimiento, grupos | | |
| D3 | `/produccion/Dashboard` listado + mute | Carga; ruta detalle: `…/detalle/orden_venta/{id}` (no `/venta/`) | **#3** | ✅ OV-2026-0010 |
| D4 | E2E emulado: 1 cubeta TEST → Completada **bloqueada** sin pesaje | Error/bloqueo | **#3** | ✅ |
| D5 | Confirmar pesaje → `PESAJE-*`; insumos bajan una vez | Segundo pesaje falla | **#3** | ⚠️ SKIP (1 Kg vs 570 Kg BOM) |
| D6 | Completada → lote + entrada PT; insumos **no** bajan otra vez | OK | **#3** | ⚠️ SKIP (sin pesaje) |
| D7 | Etiqueta + `/produccion/Lotes/consultar` | Producto/cubeta/lote/venta | | |

---

## E. Proveedores / Compras / OC

| ID | Acción | Esperado | Aud. P0 | Resultado |
|----|--------|----------|---------|-----------|
| E1 | `/compras/Proveedores` listado, filtros, detalle | Carga | | ✅ 28-sep (9 proveedores + detalle) |
| E2 | Insumos vinculados / historial OC del proveedor | Datos visibles | | ✅ 28-sep |
| E3 | `/compras/OrdenesCompra` crear OC TEST (1 línea) | Borrador → PDF | | ✅ 28-sep OC-2026-0003 (cancelada) |
| E4 | Comparar PDF ERP vs plantilla Excel histórica (ver §H) | Anotar gaps | | ✅ 28-sep: importe con letra y firmas OK; endpoint devuelve HTML imprimible, no PDF binario |
| E5 | Preórdenes: listar; autorizar **solo** TEST | Genera OC | **#2** | ✅ PRE-0007→OC-2026-0001; UI 28-sep: PRE-2026-0012→OC-2026-0002 (2000 g→2 Kg) y re-autorizar bloqueado |
| E6 | Recepción parcial TEST (si aplica) | Stock insumo sube | **#2** | ✅ BLANCO 0→1 Kg; 28-sep: recepción en `Borrador` **bloqueada** sin movimientos (no se repitió la entrada real) |
| E7 | Pago TEST + comprobante (si iteración 5 activa) | Guarda | | ✅ 28-sep (lectura de pagos + modal/comprobante; no se registró pago nuevo) |
| E8 | Correo/WhatsApp texto (preview; no spam a proveedor real) | Preview OK | | ✅ 28-sep (`simular_correo_ajax` + `whatsapp_texto_ajax`; sin SMTP) |

**Nota 2026-09-28 (agente con shell + BD):** además de E5–E6, se validaron por CLI las guardas nuevas de
`recibir_mercancia()` (estatus de OC, línea ajena, sobre-recibo, línea inexistente) y la conversión de
unidades en `PreordenesModel::aprobar()` (1500 g → 1.5 Kg; Cubeta→Kg **aborta** con mensaje). Además, E1–E5, E7 y E8 quedaron validados en **UI real** (28-sep, sesión autenticada por cookie) y se comprobó que la recepción en OC `Borrador` queda bloqueada. Evidencia completa en `MODULOS_ESTADO_CHECKLIST.md` §4.

---

## F. RH — Empleados / expediente

Detalle largo: `PRUEBAS_MANUALES_RH_2026-08-10.md`.

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| F1 | `/rh/RecursosHumanos` buscar `ana` | Filtra | |
| F2 | Detalle: Personal / Fiscal / Laboral / Documentos | Sin 500 | |
| F3 | Alta con RFC/CURP inválidos | Rechaza | |
| F4 | Incidencias (limpiar si creas HE) | Badge OK | |
| F5 | Vacaciones listado | Carga | |
| F6 | Contrato/plantilla PDF | Sin `{{...}}` suelto | |
| F7 | `/rh/RelojChecador` | Carga (Bixpe obras = fuera de alcance) | |

---

## G. RH — Nómina (`/rh/Nomina`)

**Solo nómina TEST periodo futuro.** No pagar real. Ver también `CHECKLIST_PRUEBA_GENERAL_NOMINA_2026-08-17.md`.

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| G1 | Listado, tarjetas, filtros | Carga | |
| G2 | Nueva Semanal futura → Borrador | Totales $0 | |
| G3 | Calcular | Calculada; `sueldo = diario × días` en 1 empleado | |
| G4 | Inline Comidas/HE → restaurar | Recalcula | |
| G5 | Calcular 2ª vez | No duplica conceptos | |
| G6 | Cancelar TEST (motivo ≥ 10) | Cancelada | |
| G7 | Intent cancelar Pagada demo | Bloquea (“notas de ajuste”) | |
| G8 | Planeador &lt; &gt; meses | OK | |
| G9 | Campana nómina por vencer (si hay) | Badge → `/rh/Nomina` | |

---

## H. Hallazgos `doc/entrenamiento_2/` (verificación 25-ago-2026)

### Archivos presentes

| Archivo | Tipo | Notas |
|---------|------|-------|
| `CHISA GLASS REF AZ-03-1.xlsx` (+ copia) | Formulación | 1 hoja, ~28 filas. Cliente **HOSPITAL JUAREZ**, ref **AZ-03-1**, lote **2 LTS / 2 kg**. Perfil con `KILOS` → **importable** por `Productos::importar_archivo_cli`. |
| `Copia de BASES ORGANICAS Y TINTAS.xls` | Semielaborados | ~33 encabezados `KILOS` (vs ~35 en `doc/BASES ORGANICAS Y TINTAS.xls`). Misma estructura; precios pueden diferir. **Ya entrenado** en P1 — reimportar solo con `dedup` y revisar omitidos. |
| `FICHAS DE CHISA GLASS 2014.xls` | Fichas | ~316 `KILOS` en 3 hojas. El de `doc/FICHAS_CHISA_GLASS_2014.xls` tiene **más** (~423, 4 hojas). **No reimportar a ciegas**; el histórico grande ya se usó. |
| `ORDENES DE COMPRA.xls` | Plantillas OC históricas | **58 hojas** = 58 OC de ejemplo (no catálogo de productos). Referencia de **formato** PDF/impreso. |

### Órdenes de compra: ¿el ERP puede sacar un formato similar?

Vista actual: `application/views/compras/ordenes_compra/pdf_oc.php` (`/compras/OrdenesCompra/generar_pdf/{id}`).

| Elemento en Excel histórico | PDF ERP actual | Gap |
|----------------------------|----------------|-----|
| Título + Nº OC + fecha | Folio + fecha + estatus | Parcial (OK funcional) |
| Bloque PROVEEDOR (dir, tel, attn, email, cuenta) | Razón social, RFC, tel, dir | ⚠️ Falta attn/email/cuenta bancaria en PDF |
| Bloque CLIENTE (datos Chisa) | Logo + razón social en header | ⚠️ No hay bloque “CLIENTE” explícito al estilo Excel |
| Tabla Cantidad \| Unidad \| Descripción \| P.U. \| Importe | # \| Código \| Descripción \| Unidad \| Cant \| P.U. \| Subtotal | ⚠️ Orden de columnas distinto; Excel no usa “código” |
| Importe con letra | — | ❌ No existe |
| Subtotal / IVA 16% / Total | Sí (+ descuento) | OK |
| Firmas (Firmo cheque / Elaboró / Autorizó / Elabora cheque) | Elaboró·Compras / Autorizó·Gerencia / Aceptó·Proveedor | ⚠️ Roles distintos |
| Importar las 58 OC a BD | — | ❌ Fuera de alcance del importador de formulaciones |

**Conclusión OC:** el flujo crear OC → PDF **ya existe** y cubre lo operativo. Para “formato similar al Excel” hace falta una **iteración de plantilla PDF** (importe con letra, bloque cliente, firmas, opcionalmente columnas al estilo plantilla). No confundir el `.xls` con carga de productos.

### Productos / formulaciones — qué entrenar

**Estado BD (consulta 25-ago-2026):** ~464 productos, ~941 formulaciones, 9 proveedores, 4 OC. Existe producto `CHISA GLASS REF. AZ - 03- 2` (id 464). **No** hay formulación/producto claro para **AZ-03-1** ni comentarios `HOSPITAL JUAREZ` → el xlsx nuevo sí aporta.

| Acción | Recomendación |
|--------|----------------|
| `AZ-03-1.xlsx` | **Importar** (falta en catálogo; sí hay AZ-03-2). CLI `IMPORT_FILE` + `dedup`. Activar versión; revisar `referencia_cliente` / HOSPITAL JUAREZ. |
| Bases orgánicas (copia) | Ya hay ~7 productos “BASE ORGANICA*”. **No masivo**; si importas, solo `dedup` y revisar omitidos/precios. |
| FICHAS 2014 (entrenamiento_2) | Subconjunto del ya importado. **No** reimportar completo. |
| FICHAS en `doc/FICHAS_CHISA_GLASS_2014.xls` | Ya usado — no rehacer. |

**Comando CLI (solo cuando autorices el import):**

```bash
cd /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html
IMPORT_FILE="doc/entrenamiento_2/CHISA GLASS REF AZ-03-1.xlsx" IMPORT_MODE=dedup \
  /usr/local/php82/bin/php index.php produccion/Productos importar_archivo_cli
```

Post-import: Producción → Productos → buscar `AZ-03` / `HOSPITAL` → activar formulación → Simulador 1–2 kg.

---

## I. Contabilidad I4 (solo lectura)

No alterar Ventas/Compras/RH. Prefijo TEST. **No** autorizar pólizas de documentos reales salvo decisión explícita.

| ID | Acción | Esperado | Aud. P0 | Resultado |
|----|--------|----------|---------|-----------|
| I1 | `/contabilidad/Origenes` | Lista CFDI, OC recibidas, nóminas pagadas | **#4** | ✅ 24-sep |
| I2 | Generar pólizas borrador (idempotente) | No duplica `origen`+`origen_id`; debe=haber | | |
| I3 | Reportes: diario / mayor / IVA | Carga; balanza vacía hasta autorizar | | |
| I4 | Dashboard periodo 2026 | Periodo septiembre 2026 abierto | | |

---

## J. Almacén (`/almacen/Entregas` · `/almacen/Inventario`)

| ID | Acción | Esperado | Aud. P0 | Resultado |
|----|--------|----------|---------|-----------|
| J1 | Entregar OV `En Preparación` con PT ≥ cantidad | Baja PT **una vez**; `detalle_orden_venta.cantidad_entregada` + cantidad; OV `Entregada` + `fecha_entrega_real`; kardex en `movimientos_productos` | | ✅ 28-sep-2026 CLI+BD (tx revertida): parcial (1 de 2) y cierre |
| J2 | Entregar con PT **insuficiente** | **Rechaza** con disponible/requiere; no crea entrega ni mueve stock | | ✅ 28-sep-2026 (PT 3 = −57 y PT 10 = 0) |
| J3 | Entregar más que el pendiente / partida ajena / producto que no coincide | Rechaza; nada se escribe | | ✅ 28-sep-2026 (pendiente 5 y se piden 6; partida 19 en OV-0015) |
| J4 | Reintentar entrega de la **misma** OV | Rechaza (ya `Entregada`); stock intacto | | ✅ 28-sep-2026 |
| J5 | Entrega de obra (`Aprobada` / `En Ejecución`) | Baja PT; `obras_productos.cantidad_entregada`; obra → `Completada` | | ✅ 28-sep-2026 (OB-00002) |
| J6 | Ajuste de inventario (`/almacen/Inventario`) + motivo | Movimiento `Ajuste` con usuario y stock coherente | | |
| J7 | QR / Tres Guerras | ⏸ fuera de alcance (diseño pendiente) | | |

**Nota 28-sep-2026:** el camino feliz de J1/J5 se validó **en transacción revertida** (los pendientes reales tienen PT con stock ≤ 0). No quedaron folios, kardex ni entregas.

---

## Hoja de resultados

| Bloque | Fecha | Quién | ✅/⚠️/❌ | Folios TEST | Notas |
|--------|-------|-------|---------|-------------|-------|
| A Clientes | | | | | |
| B Ventas/POS | 24-sep-2026 · 28-sep-2026 | Agente smoke P0 · agente CLI+BD | ✅ | OV-2026-0010/0011 · OV TEST (rollback) | B3–B5 ✅; 28-sep: **B6 + B10 ✅** con transacción revertida (PT 1→0, insumos 17 174 sin cambio, 0 residuos). Guardas nuevas: idempotencia en `entregar_orden()` + consolidación por producto en `validar_stock_pt_lineas()` |
| C Obras | | | | | |
| D Producción | 24-sep-2026 | Agente smoke P0 | ⚠️ | OV-0010 | D3–D4 ✅; D5–D6 SKIP BOM |
| E Proveedores/OC | 24-sep-2026 · 28-sep-2026 | Agente smoke P0 · agente CLI+BD+UI | ✅ | PRE-0007, OC-2026-0001, OC-2026-0002/0003 (canceladas), PRE-2026-0009 (rechazada), PRE-2026-0012 | E5–E6 ✅; 28-sep: guardas de recepción + conversión de unidades (CLI y **UI real**), E1–E4/E7/E8 ✅. Stock intacto |
| F RH empleados | | | | | |
| G Nómina | | | | | |
| H Entrenamiento / PDF OC | | | | | |
| I Contabilidad I4 | 24-sep-2026 | Agente smoke P0 | ✅ | — | I1 + cli_probe sandbox |
| J Almacén | 28-sep-2026 | Agente CLI+BD | ✅ | ENT-2026-0003..0005 (rollback) | A1–A3 + guardas en `registrar_entrega()` (estatus origen, partida, pendiente, stock consolidado); OB-00002 → `Completada`; 0 residuos |

**Listo para iterar código cuando:** filas **Aud. P0 #1–#3** (B3–B6, E5–E6, D3–D6) en ✅ o SKIP justificado; además C2, y decisión PDF OC (§H) si aplica.
