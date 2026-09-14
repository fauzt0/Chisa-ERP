## Cabecera

Fixes presentación iteración 3 — ERP CHISA · rama `iteracion-3`
Código HEAD (antes de este informe): `0b3da06` · baseline QA `d52a277`
Fecha: 2026-09-14 ~06:10–06:25 CDMX · Ejecutó: agente Cursor (EHWEB en UI)
Entorno: producción viva, PHP 8.3, MySQL `st32477_chisa`

## Tareas

| ID | Tarea | Estado | Evidencia | Observación |
|----|--------|--------|-----------|-------------|
| T1 | Migración + field_exists Lotes | ✅ | `FIX-T1-migracion-2026-09-14_0610.txt` `respaldo_T1_2026-09-14_0610.sql` `FIX-T2-T5-T1-ui-2026-09-14.txt` | Columnas OK. ENUM conserva `Venta`. `lista_ajax` 200 vacío |
| T2 | Inventario CASE 0.5 | ✅ | `FIX-T2-T5-T1-ui-2026-09-14.txt` | GET `/almacen/Inventario` 200 |
| T3 | Descuentos wrappers | ⚠️ | `FIX-T3-descuentos-2026-09-14.txt` | Código cerrado. Alta UI TEST no persistió |
| T4 | Precios lista 2025 | ⚠️ | `FIX-T4-precios-2026-09-14.txt` | 30 UPDATE; 33 con precio; **461 aún sin precio** (fuera del JSON) |
| T5 | CRM obras `activo=1` | ✅ | `FIX-T2-T5-T1-ui-2026-09-14.txt` | 2 obras; OB-00006 oculta |
| T6 | Limpieza TEST | ⚠️ | — | **Espera decisión humana** (abajo) |
| T7 | Catálogo placeholder/BOM/fotos | ⚠️ | `FIX-T8-verificar-presentacion-2026-09-14.txt` | **Espera decisión humana** |
| T8 | Re-verificación + commits | ⚠️ | este informe | Completada E2E lote/PT **no re-ejecutada**. Push pendiente si falla red |
| T9 | QA SKIP extra | SKIP | — | No pedido explícito |

## Precios antes/después

- Antes: 3 con precio > 0; 463 = 0; 28 NULL (491 sin precio).
- Después: **33** con precio; 460 = 0; 1 NULL (**461 sin precio**).
- Cargados: 30 (regla CUBETA). Skip protegidos: #3 PROD-0001 $500 (lista pedía 4797.13), #475 ya 5023.57.
- Muestra: #223 PINTU-FLEX 0→2410.87; #495 CHISA-MAR NULL→3597.23; #475 5023.57; #480 HEALER 262.05; #477 CHISA-PLUS 2840.13.

## IVA

Sin OV TEST nueva (XHR de POS/descuentos aborta en automatización). Histórico ya correcto: OV-2026-0003 5000+800=5800; OV-2026-0005 500+80=580.

## Datos tocados

- ALTER: `lotes_produccion.orden_venta_id`, `obra_id`, índices; `ordenes_venta.fecha_completado_produccion`; `obras.fecha_completado_produccion`; ENUM movimientos (añade Merma/Produccion, **conserva Venta**).
- `productos.precio_venta` en 30 ids (ver FIX-T4).
- Respaldo: `respaldo_T1_2026-09-14_0610.sql` (29 480 B).

## Pendientes

1. **T6** OB-00006: ¿dejar `activo=0` y pasar `estatus` a Cancelada?
2. **T6** Reverso `PESAJE-venta-26` (#18 149.97, #20 79.03) vs merma QA. Movimiento id 19 cantidad 0: ¿borrar puntual?
3. **T7** Quitar placeholder de 462 descripciones; cargar `rendimiento` literal de lista; 180 fabricados sin BOM: ¿nueva versión / Reventa / desactivar?
4. **T4** 461 productos siguen en 0: no están en `presentaciones[]` del JSON. ¿Fuente de precios?
5. **#3 CHISA GLASS MICRO** se dejó en $500 (no se aplicó $4797.13 de lista).
6. Completada E2E (#22) + CRUD descuento TEST + OV con IVA en POS: falta clic humano o re-QA T9.
7. BUG-UI-06 cosmético POS no tocado.

## Evidencia en disco

```
total 124
FIX-T1-migracion-2026-09-14_0610.txt
FIX-T2-T5-T1-ui-2026-09-14.txt
FIX-T3-descuentos-2026-09-14.txt
FIX-T4-precios-2026-09-14.txt
FIX-T4-precios-2026-09-14-dryrun.txt
FIX-T8-verificar-presentacion-2026-09-14.txt
respaldo_T1_2026-09-14_0610.sql
INFORME_FIXES_PRESENTACION.md
(+ evidencias QA 2ª pasada previas)
```

## Resumen JSON

```json
{"commit":"0b3da06","fecha":"2026-09-14","tareas":{"cerradas":3,"parciales":4,"no_hechas":0},"precios":{"sin_precio_antes":491,"sin_precio_despues":461,"cargados":30},"iva":{"ok":true,"evidencia":"historico OV-2026-0003/0005; sin OV TEST nueva"},"bugs_cerrados":["BUG-UI-04","BUG-UI-05","BUG-UI-09"],"pendientes":["T3 alta UI descuento","T4 461 sin precio fuera de JSON","T6 reverso pesaje y OB-00006 estatus","T7 placeholder/BOM/fotos","D6 Completada E2E lote+PT","BUG-UI-06","proteger #3 precio 500 vs lista"]}
```
