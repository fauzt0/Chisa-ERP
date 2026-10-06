# Smoke manual ERP CHISA — P0 auditoría diagrama (agente ejecutor)

**Uso:** abrir este archivo en Cursor Agent (modelo recomendado: **Grok 4.7 High**) y pedir: *“Ejecuta al pie de la letra `doc/SMOKE_P0_AGENTE_EJECUTOR.md`”*.

**Auditoría posterior:** otro agente/humano contrasta el entregable con código y checklist; no re-ejecutar UI completa salvo dudas puntuales.

> **Estado (2026-10-06):** el ciclo P0 de la auditoría diagrama está **cerrado** — #1 POS (B6), #2 Compras y
> #4 Facture conexión con evidencia al 2026-09-28. Queda **solo** el re-smoke **UI** de Producción (#3, D5–D6),
> ver `TODO.md` §4.4. Iteración 4 cerrada; rama activa `iteracion-5`.

---

## Objetivo

Ejecutar las filas con **Aud. P0** del checklist y dejar **evidencia** verificable. No implementar código salvo anotar un bug bloqueante obvio y seguir.

---

## Entorno

| Dato | Valor |
|------|--------|
| URL | `https://erp.chisarecubrimientos.com.mx/` |
| Repo | `/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html` |
| Rama | `iteracion-5` |
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

**Pesaje E2E (medido 28-sep-2026):** hay **dos** bloqueos, no solo stock — stock (BLANCO 1 Kg vs 570.35 Kg por cubeta
y 6 844.2 Kg por 12 cubetas) y **unidades ambiguas** (`revision_manual`: `"Kg" ↔ "L"` en solventes y
`"Kg" ↔ "Cubeta"` en el insumo INS00001), que hoy frenan `OV-2025-0013` y `OV-TEST-001`. Es decir: hoy **ninguna**
orden abierta llega al camino feliz. Guion con números medidos, tabla de bloqueos y opciones A (transacción
revertida) / B (UI con limpieza): **`TODO.md` §4.4**.

> D5–D6 **ya tienen evidencia real persistente** (17–18-sep-2026: `PESAJE-venta-28` → `OV-2026-0009` `Completada`
> → lote `PROD-20260918-22-2268` + entrada PT, sin doble descuento). Lo que falta es la corrida en UI con el código actual.

| ID | Acción | Esperado clave |
|----|--------|----------------|
| D3 | `/produccion/Dashboard` | Carga; pedidos OV/obra visibles si hay TEST |
| D4 | Completada **sin** pesaje | Debe **fallar** / bloquear |
| D5 | Confirmar pesaje | `PESAJE-*`; insumos bajan; **segundo** pesaje falla — ✅ evidencia real 17-sep (`PESAJE-venta-28`, 3 salidas); **rechazo del 2.º pesaje observado 28-sep-2026** (OV-2026-0008, transacción revertida): `success = false` con *"Los insumos de esta orden ya fueron descontados por pesaje anterior."* y 0 filas nuevas en `movimientos_inventario` |
| D6 | Completada tras pesaje | Lote + entrada PT; insumos no bajan otra vez — ✅ evidencia real 18-sep (lote `PROD-20260918-22-2268`, 0 salidas nuevas) |

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
