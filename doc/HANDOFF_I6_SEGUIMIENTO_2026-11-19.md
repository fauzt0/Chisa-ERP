# Handoff I6 — formulaciones, ciclo y Grokbot (2026-11-19)

**Rama:** `iteracion-6` · **Commit I6:** `git log -1 --oneline` (mensaje: `feat(i6): historial operativo, consulta MP, dashboard y prefactura sandbox.`).  
**BD producción/entrenamiento:** no se migró esquema ni se importó `FICHAS_CHISA_GLASS_2014.xls`. **Espejo de BD y pruebas Grokbot aún NO iniciados** por Fausto.

---

## Resumen ejecutivo

Iteración 6 conecta **historial de formulaciones usable en operación**, **consulta de MP en cotización** (sin preorden), **dashboard con % entrega**, **prefactura sandbox desde OV tras pago** y **métricas de huecos en entrenamiento de productos**. Sin reabrir P1–P9. Sin tocar `OV-2026-0009` ni `PRE-2026-0001`.

---

## Cambios por módulo (18 archivos, ~+288/−37 líneas)

| Módulo | Archivos | Qué hace |
|--------|----------|----------|
| **Producción — Historial** | `HistorialFormulaciones.php`, `historial_formulaciones/main.php`, `ProductosModel` | Por defecto **activa + historial**; «Solo activas» solo si `filtrar=1` y casilla marcada. Búsqueda: comentarios, versión, REF, producto, cliente. Con producto+cliente incluye versiones **generales** (`cliente_id` NULL). Orden: `es_activa DESC`. |
| **Producción — Entrenamiento** | `EntrenamientoProductos.php`, `entrenamiento_productos/main.php`, `ProductosModel` | Filtros **sin fórmula / sin activa**; columnas conteo; banner (snapshot ~497 fabricados, 31 sin fórmula, 152 sin activa, **0/956** con `cliente_id`). Candado de historial **solo** en ficha de entrenamiento. |
| **Producción — Dashboard** | `ProduccionModel.php`, `dashboard/main.php`, `dashboard/detalle.php` | **% entrega** OV/obra en tarjetas; enlace/modal a `HistorialFormulaciones/ver/{id}`. |
| **Ventas — POS** | `pos/main.php` | Historial con cliente + Ver; copy insumos **consulta** vs **preorden** (lógica en `Pos`/`VentasModel` previa). |
| **Ventas — CCO** | `CotizacionesObra.php`, `cotizaciones_obra/editar.php` | `consultar_mp_ajax`: solo lectura, `solo_consulta: true`, líneas con `producto_id` + fórmula activa. Botones «Consultar materia prima» y «Fórmulas». |
| **Ventas — Órdenes** | `Ordenes.php`, `ordenes/main.php`, `VentasModel.php` | «Facturar (sandbox)» si pago Parcial/Pagado; post-pago abre `Facturas/crear?orden_id=`. Detalle OV: enlace fórmula. |
| **Facturación** | `Facturas.php`, `facturacion/crear.php` | Prefill desde OV; `orden_venta_id` en POST emitir. **`Facturas::emitir` sigue con `die()` debug — no timbrar en QA.** |
| **Docs** | `TODO.md`, `PRUEBAS_GROKBOT_I6.md`, este handoff | §4.1 I6; casos Grokbot P4, V2, V3, D1, F1. |

---

## Reglas de negocio (no romper)

- Preórden solo en **compromiso** (confirmar OV, etc.). Cotización/CCO consulta **no** crea `PRE-`.
- Completar lote exige **pesaje** (sin cambios I6).
- `asociar_cliente` en historial: escritura CEO; no probar en Grokbot sin autorización.
- Excel fichas: **solo referencia**; no import masivo.

---

## Riesgos / límites para el siguiente agente

1. **CCO MP:** Solo partidas **Producto** con SKU (ej. CCO-2026-0003 solo aplicaciones → mensaje «sin SKU ligado» es correcto).
2. **Factura:** Abrir formulario prefilled OK; **no** pulsar «Emitir Factura».
3. **Historial sin filtro:** ~956 filas; usar filtros en UI.
4. **Ruino:** `console.log` en detalle OV (`ordenes/main.php`) — opcional limpiar después.
5. **Dato pendiente (no código):** asociar `cliente_id` a versiones (métrica 0/956).

---

## Smoke web ya observado (2026-11-19)

- `/produccion/HistorialFormulaciones` — listado completo + checkbox Solo activas.
- `/produccion/EntrenamientoProductos?hueco=sin_formula` — banner huecos.
- `/produccion/Dashboard` — Entrega 0% en tarjetas; detalle con texto Fórmula/Historial.
- `/ventas/CotizacionesObra/editar/3` — botones Consultar MP / Fórmulas.
- `/facturacion/Facturas/crear?orden_id=23` — prefiltro OV-2026-0004 sandbox (sin emitir).

---

## Próximos pasos (Fausto / siguiente agente)

1. Commit I6 en `iteracion-6` (push a `origin` pendiente si aplica).
2. **Espejo de BD** para Grokbot (sin DROP/TRUNCATE en prod).
3. Ejecutar checklist `doc/PRUEBAS_GROKBOT_I6.md` — **sin** cobro real, timbrado, SMTP, ni alterar `OV-2026-0009` / `PRE-2026-0001`.
4. Al salir Grokbot: POS $0, RES-002 como al inicio, fórmula 957 inactiva sin cliente.

---

## Archivos del diff (referencia)

```
application/controllers/facturacion/Facturas.php
application/controllers/produccion/EntrenamientoProductos.php
application/controllers/produccion/HistorialFormulaciones.php
application/controllers/ventas/CotizacionesObra.php
application/controllers/ventas/Ordenes.php
application/models/Produccion/ProduccionModel.php
application/models/Produccion/ProductosModel.php
application/models/Ventas/VentasModel.php
application/views/facturacion/crear.php
application/views/produccion/dashboard/detalle.php
application/views/produccion/dashboard/main.php
application/views/produccion/entrenamiento_productos/main.php
application/views/produccion/historial_formulaciones/main.php
application/views/ventas/cotizaciones_obra/editar.php
application/views/ventas/ordenes/main.php
application/views/ventas/pos/main.php
doc/PRUEBAS_GROKBOT_I6.md
doc/TODO.md
doc/HANDOFF_I6_SEGUIMIENTO_2026-11-19.md
```