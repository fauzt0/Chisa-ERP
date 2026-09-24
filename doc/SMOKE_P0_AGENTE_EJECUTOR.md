# Smoke manual ERP CHISA — P0 auditoría diagrama (agente ejecutor)

**Uso:** abrir este archivo en Cursor Agent (modelo recomendado: **Grok 4.7 High**) y pedir: *“Ejecuta al pie de la letra `doc/SMOKE_P0_AGENTE_EJECUTOR.md`”*.

**Auditoría posterior:** otro agente/humano contrasta el entregable con código y checklist; no re-ejecutar UI completa salvo dudas puntuales.

---

## Objetivo

Ejecutar las filas con **Aud. P0** del checklist y dejar **evidencia** verificable. No implementar código salvo anotar un bug bloqueante obvio y seguir.

---

## Entorno

| Dato | Valor |
|------|--------|
| URL | `https://erp.chisarecubrimientos.com.mx/` |
| Repo | `/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html` |
| Rama | `iteracion-4` |
| PHP CLI | `/usr/local/php82/bin/php index.php …` desde `public_html` |
| Entorno | `ENVIRONMENT=development` (2FA no obligatorio en dev) |

---

## Reglas duras

1. Prefijo **`TEST-QA-`** en comentarios y folios de prueba.
2. **No** timbrar CFDI real, **no** cobrar cliente real, **no** pagar nómina real.
3. **No** enviar email/WhatsApp a proveedores o clientes reales (preview OK si existe).
4. **No** autorizar `PRE-2026-0001`. **No** modificar `OV-2026-0009`.
5. **No** `DROP` / `TRUNCATE`. Cantidades mínimas (1 línea / 1 cubeta).
6. Al cerrar un TEST: cancelar o anotar con motivo ≥ 10 caracteres si aplica.
7. **No** editar `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` ni `TODO.md` salvo petición explícita del usuario; solo reportar en el chat.

---

## Documentos de referencia

- Checklist: `doc/CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` (columna **Aud. P0**, tabla **Orden sugerido** al inicio).
- Gaps / contexto: `doc/TODO.md` § **Auditoría diagrama general**.
- Reglas agente: `doc/REGLAS_TECNICAS.md`, `.cursorrules`.

---

## Orden obligatorio de ejecución

### P0 #1 — Ventas / POS (`Aud. P0` **#1**)

| ID | Ruta / acción | Esperado clave |
|----|----------------|----------------|
| B3 | `/ventas/Pos` — buscar producto, ticket, cliente | UI OK |
| B4 | Guardar **cotización** TEST con fabricado | Aviso faltantes; **0 preorden** en cotización |
| B5 | Confirmar cotización / compromiso TEST | Preorden solo si faltantes; `origen=venta` |
| B6 | Cobrar mostrador **solo** TEST | Con PT suficiente: baja stock; insumos **no**. Sin PT: mensaje de rechazo (no stock negativo) |

Validar en detalle OV: IVA coherente con `(subtotal − descuento) × 0.16` si aplica.

### P0 #2 — Compras / OC (`Aud. P0` **#2**)

| ID | Acción | Esperado clave |
|----|--------|----------------|
| E5 | Preórdenes: autorizar **solo** TEST | Genera OC sin duplicar |
| E6 | Recepción parcial/total TEST | `insumos.stock` sube (recepción vía `recibir_mercancia`) |

Anotar folio OC y, si la UI lo muestra, stock antes/después.

### P0 #3 — Producción (`Aud. P0` **#3**)

**Ruta detalle OV:** `/produccion/Dashboard/detalle/orden_venta/{id}` (no usar `/detalle/venta/`).

**Pesaje E2E:** si el BOM pide cientos de Kg, recepcionar stock suficiente en E6 **o** usar producto/ línea con BOM pequeño; con 1 Kg vs 570 Kg teóricos D5–D6 quedarán SKIP (UI bloquea pesaje sin stock).

| ID | Acción | Esperado clave |
|----|--------|----------------|
| D3 | `/produccion/Dashboard` | Carga; pedidos OV/obra visibles si hay TEST |
| D4 | Completada **sin** pesaje | Debe **fallar** / bloquear |
| D5 | Confirmar pesaje | `PESAJE-*`; insumos bajan; **segundo** pesaje falla |
| D6 | Completada tras pesaje | Lote + entrada PT; insumos no bajan otra vez |

### P0 #4 — Facturación conexión (`Aud. P0` **#4**)

| ID | Acción | Esperado clave |
|----|--------|----------------|
| I1 | `/contabilidad/Origenes` | Lista CFDI, OC recibidas, nóminas (smoke carga) |
| CLI | `php index.php facturacion/Facturas/cli_probe` | JSON `ok: true` (sin timbrar) |
| UI | `/facturacion/Facturas` dashboard | Muestra conectado si hay token (opcional con login) |

**Fuera de alcance:** autofactura, pasarela de pagos, timbrado go-live producción.

---

## Login

Usar credenciales de **presentación** que proporcione Fausto en el chat. Si faltan, pedirlas **una vez** y usar browser MCP. No inventar usuarios ni contraseñas en commits/docs.

---

## Evidencia por ítem

Para cada fila B3–B6, E5–E6, D3–D6, I1 (+ cli_probe):

- Resultado: **✅ / ⚠️ / ❌ / SKIP**
- Folio(s) TEST
- Evidencia: captura, URL, mensaje UI, o consulta **solo lectura** a BD si el usuario lo autoriza
- Nota de una línea

---

## Entregable final (pegar en el chat)

### 1. Tabla de resultados

```text
| ID | Resultado | Folio(s) TEST | Evidencia | Nota |
```

### 2. Hoja de resultados (resumen)

Bloques **B**, **E**, **D**, **I**: fecha, quién = “Agente smoke P0”, ✅/⚠️/❌, folios TEST, notas.

### 3. Hallazgos P0

Lista de ítems ❌ o ⚠️: esperado vs observado.

### 4. Bloqueos

Login, 500, permisos: URL + mensaje + SKIP motivado.

---

## Si el browser o CLI fallan

- Reintentar **una** vez con evidencia nueva.
- No fuerces timbrado, cobro real ni autorización de preórdenes de producción reales.
- Sigue con el siguiente ítem P0 y documenta.
