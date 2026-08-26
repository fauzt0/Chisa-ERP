# Checklist manual — módulos iterados + entrenamiento_2

**Fecha:** 25 ago 2026  
**URL:** `https://erp.chisarecubrimientos.com.mx/`  
**Login sugerido:** `presentacion@chisa.mx` / `Demo2026!`  
**Leyenda:** ✅ / ⚠️ / ❌ / SKIP  

**Basado en:** `CHECKLIST_PRUEBAS_CRM_VENTAS.md`, `PRUEBAS_MANUALES_RH_2026-08-10.md`, `CHECKLIST_PRUEBA_GENERAL_NOMINA_2026-08-17.md`, `CHECKLIST_MANUAL_POST_E2E_NOMINA.md`, `AUDITORIA_OVERHAUL_PRODUCCION_2026-08-20.md`, `PLAN_ITERACION_PROVEEDORES.md`, `VERIFICACION_MODULO_COMPRAS.md`.

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

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| B1 | `/ventas/Ordenes` listado, filtros, Excel | OK | |
| B2 | Detalle OV + formatos Factura / Remisión / Moderno | Abren | |
| B3 | `/ventas/Pos` buscar producto, ticket, cliente | UI OK | |
| B4 | Guardar **cotización** TEST con fabricado | Aviso faltantes si aplica; **0 preorden** | |
| B5 | Confirmar cotización / compromiso TEST | Preorden Pendiente `origen=venta` solo si faltantes | |
| B6 | Cobrar / entregar **solo** TEST | Stock producto baja; insumos **no** | |
| B7 | `/ventas/Descuentos` CRUD mínimo + visible en POS | OK | |
| B8 | `/ventas/ObrasVentas` listado + detalle | Carga | |

---

## C. Obras (`/obras/Obras`)

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| C1 | Listado obras | Carga | |
| C2 | Obra borrador: producto **sin** rendimiento ni override | Bloquea; **no** calcula con 1.0; **no** preorden | |
| C3 | Producto **con** rendimiento + m² (ej. MASA ROCA 319) | kg/cubetas/insumos coherentes | |
| C4 | Aprobar solo obra TEST con faltantes | Preorden `origen=obra`; generar OV no duplica | |
| C5 | Cancelar/limpiar obra TEST | Folio anotado | |

---

## D. Producción

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| D1 | `/produccion/Productos` buscar `hospital` / `ROCA` / alias | Filtra AND | |
| D2 | Ver formulación (no Guardar salvo TEST consciente) | Cliente, comentarios, rendimiento, grupos | |
| D3 | `/produccion/Dashboard` listado + mute | Carga | |
| D4 | E2E emulado: 1 cubeta TEST → Completada **bloqueada** sin pesaje | Error/bloqueo | |
| D5 | Confirmar pesaje → `PESAJE-*`; insumos bajan una vez | Segundo pesaje falla | |
| D6 | Completada → lote + entrada PT; insumos **no** bajan otra vez | OK | |
| D7 | Etiqueta + `/produccion/Lotes/consultar` | Producto/cubeta/lote/venta | |

---

## E. Proveedores / Compras / OC

| ID | Acción | Esperado | Resultado |
|----|--------|----------|-----------|
| E1 | `/compras/Proveedores` listado, filtros, detalle | Carga | |
| E2 | Insumos vinculados / historial OC del proveedor | Datos visibles | |
| E3 | `/compras/OrdenesCompra` crear OC TEST (1 línea) | Borrador → PDF | |
| E4 | Comparar PDF ERP vs plantilla Excel histórica (ver §H) | Anotar gaps | |
| E5 | Preórdenes: listar; autorizar **solo** TEST | Genera OC | |
| E6 | Recepción parcial TEST (si aplica) | Stock insumo sube | |
| E7 | Pago TEST + comprobante (si iteración 5 activa) | Guarda | |
| E8 | Correo/WhatsApp texto (preview; no spam a proveedor real) | Preview OK | |

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

## Hoja de resultados

| Bloque | Fecha | Quién | ✅/⚠️/❌ | Folios TEST | Notas |
|--------|-------|-------|---------|-------------|-------|
| A Clientes | | | | | |
| B Ventas/POS | | | | | |
| C Obras | | | | | |
| D Producción | | | | | |
| E Proveedores/OC | | | | | |
| F RH empleados | | | | | |
| G Nómina | | | | | |
| H Entrenamiento / PDF OC | | | | | |

**Listo para iterar código cuando:** B4 (cotización sin preorden), C2 (sin fallback 1.0), D4–D6 (pesaje) en ✅ o SKIP justificado, y decisión explícita sobre PDF OC (gaps §H) + import AZ-03.
