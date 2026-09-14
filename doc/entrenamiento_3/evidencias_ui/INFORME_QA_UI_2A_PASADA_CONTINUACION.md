## Cabecera

QA UI 2ª pasada (continuación) — ERP CHISA · commit 1bdec68 (iteracion-3)
Usuario: soporte2@especialistasweb.com.mx (EHWEB) · Fecha: 2026-09-14 ~05:10–05:25 CDMX · Viewport: 1440×900 (+390×844 móvil)
DevTools: Network + Console activas toda la sesión · Ruido PHP 8.3 (Severity 8192) ignorado

## Casos ejecutados

| ID | Acción | Esperado | Resultado | Evidencia | Observación |
|----|--------|----------|-----------|-----------|-------------|
| B4 | POS cotización fabricado TEST | OV + 0 preórdenes | ✅ | `D4D6-2026-09-14-pesaje-OV-2026-0007.txt` | OV-2026-0007 id 26; CHISA GLASS REF 308 ×1; cliente id 9; total $0.00; 3 insumos OK |
| B5 | Confirmar compromiso | Preorden si faltantes | ✅ | idem | `confirmar_ajax` 200 `preordenes:[]`; En Preparación. Detalle válido: `/produccion/Dashboard/detalle/orden_venta/26` (`…/venta/26` 404) |
| D4 | Completada sin pesaje | Bloqueo | ✅ | idem | 200 `success:false` *Debe confirmar el pesaje…* `bloqueada:true` |
| D5 | Confirmar pesaje 1 vez + 2ª | Descuenta 1 vez | ✅ | idem | `PESAJE-venta-26`; PIG-003 0.0006; PIG-006 0.97; PIG-004 0.03. 2ª: ya descontados. Sin UI revertir |
| D6 | Completada tras pesaje | Lote + entrada PT | ❌ | `BUG-UI-07-2026-09-14-completada-fecha-columna.txt` | HTTP 500 DB 1054 `fecha_completado_produccion`. Sin lote/PT |
| B6 | Cobro/entrega TEST | Pago | SKIP | `B8E-G9-MOVIL-2026-09-14-resto.txt` | $0.00 y Completada 500; no se forzó cobro real |
| Limpieza OV | Cancelar OV TEST | Cancelada | ✅ | idem | Motivo `Limpieza QA TEST-QA-UI-OV-01 cancelacion`. Insumos de pesaje **no** revertidos |
| B7 | CRUD Descuentos | Crear TEST | ❌ | `BUG-UI-08-2026-09-14-descuentos-insert-500.txt` | `crear_ajax` 500 `MY_Model::insert()` protected. Listado 3 registros OK |
| B8 | `/ventas/ObrasVentas` | Carga | ✅ | `B8E-G9-MOVIL-2026-09-14-resto.txt` | 7 obras; detalle/14 200. OB-00006 sigue Aprobada en CRM |
| E3 | OC TEST + PDF | Borrador + PDF | ⚠️ | idem | Modal Nueva OC OK; **no Guardar** (limpieza). PDF `generar_pdf/3` 200 OC-2026-DEMO1 (solo lectura) |
| E5 | Autorizar preorden TEST | OC sin duplicar | SKIP | idem | Pendiente real PRE-2026-0001 (producción). No autorizar. PRE-0004/5/6 ya Rechazadas |
| E8 | Preview correo/WhatsApp | Preview; no enviar | SKIP | idem | Botón simulación visible. Envío prohibido. Preview interceptado por modal OC |
| G2–G7 | Nómina futura TEST | Calcular/cancelar | SKIP | idem | No se creó periodo (evitar residuos; sin botón Restaurar en vista) |
| G9 | Campana nómina | Badge | ✅ | idem | `GET /Notifications/get_notifications` 200 `total_count:26`; badge 9+ |
| Móvil | 390×844 smoke | Sin 500 | ✅ | idem | Clientes, POS, Obras/2 tab Entregas, RH 18 empleados |

## Bugs nuevos

### BUG-UI-07 — Alta — Producción Completada
- **Pasos:** Confirmar pesaje OV-2026-0007 → POST `actualizar_estatus_ajax` `estatus=Completada` `orden_id=26` `tipo=venta`.
- **Esperado:** Completada, lote, entrada PT.
- **Real:** HTTP 500, `Unknown column 'fecha_completado_produccion'` (`Dashboard.php` ~154). Columna en `database/final_alignment.sql`, **no en prod**.
- **Evidencia:** `BUG-UI-07-2026-09-14-completada-fecha-columna.txt`

### BUG-UI-08 — Media — Ventas Descuentos
- **Pasos:** POST `/ventas/Descuentos/crear_ajax`.
- **Esperado:** Alta del descuento.
- **Real:** HTTP 500 `Call to protected method MY_Model::insert() from context 'Descuentos'` (`Descuentos.php` ~110).
- **Evidencia:** `BUG-UI-08-2026-09-14-descuentos-insert-500.txt`

Observación (no ID): OB-00006 permanece en `/ventas/ObrasVentas` como Aprobada tras eliminación en módulo Obras.

## Datos TEST-QA creados y limpieza

| Folio/ID | Módulo | Estatus final |
|----------|--------|----------------|
| OV-2026-0007 (id 26) TEST-QA-UI-OV-01 | Ventas | **Cancelada** |
| PESAJE-venta-26 | Inventario insumos | Descuento **no revertido** (PIG-003 #17, PIG-006 #20, PIG-004 #18) |
| OB-00006 (14) | Ventas CRM | Sigue **Aprobada** |
| CLI-00002 | Clientes | **Inactivo** (sesión previa) |
| OC TEST | Compras | **No creado** |
| Nómina TEST | RH | **No creada** |

## Pendientes / bloqueos

- Completada de producción **sigue 500** hasta ALTER de `fecha_completado_produccion`.
- Insumos de pesaje de OV-0007 **quedan descontados** (sin UI de revertir).
- No autorizar PRE-2026-0001; no enviar E8; no cobros reales.
- G2–G7 no ejecutado: Auto-review bloqueó escrituras MCP; se evitó crear nómina sin limpieza clara.
- Unlock del tab del navegador también lo bloquea Auto-review (no cambia el QA).

## Evidencia en disco

```
total 64
drwxr-xr-x 2 root root 4096 Sep 14 05:24 .
drwxr-xr-x 7 root root 4096 Sep 13 22:43 ..
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

## Resumen JSON

```json
{"commit":"1bdec68","casos":{"ok":8,"warn":1,"fail":2,"skip":4},"bugs_nuevos":[{"id":"BUG-UI-07","sev":"alta","modulo":"Producción Completada","resumen":"500 Unknown column fecha_completado_produccion; bloquea lote/PT tras pesaje"},{"id":"BUG-UI-08","sev":"media","modulo":"Ventas Descuentos","resumen":"crear_ajax 500 MY_Model::insert() protected"}],"datos_test_qa":[{"folio":"OV-2026-0007","modulo":"Ventas","estatus_final":"Cancelada"},{"folio":"PESAJE-venta-26","modulo":"Inventario","estatus_final":"Descuento no revertido"},{"folio":"OB-00006","modulo":"Ventas CRM","estatus_final":"Aprobada residual"}],"pendientes":["Migrar fecha_completado_produccion en prod","Revertir o documentar insumos PESAJE-venta-26","G2-G7 nómina futura no creada","E3 OC TEST no guardada","No autorizar PRE-2026-0001"]}
```
