# Checklist de módulos ERP CHISA — estado y cierre

**Fecha:** 2026-09-28 · **Rama:** `iteracion-4`  
**Leyenda:** ✅ Operativo / verificado · ⚠️ Parcial o falta smoke · ❌ No implementado · ⏸ Fuera de alcance operativo (contrato o I5+) · 🔒 Bloqueado por negocio/datos

**Fuentes:** menú `sidebar.php`, auditoría diagrama 2026-09-24, smoke P0, `doc/TODO.md`.  
**Smoke detallado:** `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md`.  
**Última validación técnica:** 2026-09-28 (agente con shell + BD) — smoke **CLI** (conversión de unidades y validaciones de recepción) y smoke **UI real** con sesión autenticada (E1–E5, E7, E8, T2, T5). Evidencia en §4 y en la tasklist al final.

---

## ¿Qué módulo cerrar al 100 % primero?

| Prioridad | Módulo | % operativo* | Por qué |
|-----------|--------|--------------|---------|
| **1 (recomendado)** | **Proveedores / Compras** | ~100 % | Cierre técnico **+ smoke UI real** 2026-09-28: conversión de unidades preorden→OC (2000 g → 2 Kg en UI), validaciones de recepción, idempotencia y T5 sin proveedor. Reventa N/A documentado. |
| 2 | **Almacén** | ~92 % | 28-sep-2026: guardas de entrega (stock, pendiente, partida, idempotencia) + smoke A1–A3 en transacción revertida. Faltan QR y Tres Guerras (⏸ diseño). |
| 3 | **Administración usuarios** | ~92 % | Casi completo; 2FA listo pero ⏸ hasta `ENVIRONMENT=production`. |
| 4 | **CRM Ventas** (sin contrato) | ~80 % | POS/cotizaciones fuertes; 28-sep: **B6 + B10 re-smoke ✅** (stock PT, insumos intactos, tx revertida) + guardas de idempotencia y consolidación de líneas. ⏸ pasarela, autofactura, calendario CRM. |
| 5 | **Contabilidad** (alcance I4) | ~80 % | Lectura + pólizas borrador OK; ⏸ DIOT, conciliación auto, Aspel pleno. |
| 6 | **Producción** | ~85 % | Core P1–P9 cerrado; **D5–D6 con evidencia real 17–18-sep** (`PESAJE-venta-28` → OV-2026-0009 `Completada` → lote + entrada PT, sin doble descuento); pendiente re-smoke UI + fix del atajo de `revision_manual` en `puede_completar_produccion()` (ver `TODO.md` §4.4); ⏸ viscosidad/calidad formal. |
| 7 | **Obras** (+ documental) | ~70 % | Técnico OK; ❌ carátula/resumen/generador → `entrenamiento_4/`. |
| 8 | **Facturación** | ~55 % | Sandbox OK; ❌ go-live, email, autofactura. |

\*“100 % operativo” = flujos diarios internos TEST-QA, **sin** promesas contractuales marcadas ⏸/❌.
Las filas **⚠️** que siguen en §4 Compras (**enlace OC ↔ factura de compra** y **plantilla PDF estilo Excel**) son
diferidos de alcance, no bloqueos del flujo diario: por eso la fila se cuenta como ~100 % y el criterio del cierre
es la tasklist (**T1–T5 ✅ + bloque E en ✅**), verificado el 2026-09-28.

**Decisión:** usar la **tasklist § Proveedores/Compras** al final de este archivo para el próximo agente.

---

## 1. Administración de usuarios

| Función | Estado | Notas |
|---------|--------|-------|
| Alta / edición / búsqueda administradores | ✅ | |
| Roles y permisos (`tiene_permiso`) | ✅ | |
| Bitácora | ✅ | |
| Import Excel usuarios | ✅ | |
| Alerta duplicados (email) | ✅ | |
| Datos de empresa / logo | ✅ | |
| Simulador alertas | ✅ | Permiso `admin_simular_alertas` |
| 2FA email nuevo dispositivo | ⚠️ | Código en `Auth`; desactivado en `development` |

**Pendientes agente:** ninguno obligatorio hasta go-live (activar 2FA con decisión de entorno).

---

## 2. Recursos Humanos

| Función | Estado | Notas |
|---------|--------|-------|
| Empleados CRUD, RFC/CURP/NSS | ✅ | |
| Departamentos | ✅ | |
| Incidencias, vacaciones | ✅ | |
| Contratos / plantillas / documentos | ✅ | |
| Nómina cálculo, recibos, planeador | ✅ | Smoke G en checklist |
| Comunicación interna | ✅ | |
| Export NOI / Aspel | ⚠️ | Excel; compatibilidad Aspel no confirmada |
| Calculadora finiquito “oficial” | ⏸ | Cotización: no calcula finiquito; solo datos apoyo |
| Viáticos obra ↔ RH | ⚠️ | Campo nómina; sin flujo obra dedicado |
| Reloj en menú RH | ✅ | ZKTeco; ≠ Bixpe contrato (I5) |

---

## 3. Reloj checador

| Función | Estado | Notas |
|---------|--------|-------|
| Dashboard, dispositivos, sync | ✅ | Base ZKTeco / iclock |
| API `ApiReloj` | ✅ | |
| Bixpe / KONECT / GPS obras | ⏸ | **Iteración 5** — no contar en % I4 |

---

## 4. Proveedores / Compras

| Función | Estado | Notas |
|---------|--------|-------|
| Categorías insumos | ✅ | |
| Insumos CRUD + stock | ✅ | |
| Proveedores CRUD, documentos | ✅ | |
| OC crear, PDF, estatus | ✅ | |
| Cotizaciones proveedor + comparar + tipo cambio | ✅ | |
| Preorden → autorizar → OC | ✅ | E5 ✅; sin proveedor → mensaje claro (T5); cantidad convertida a unidad del insumo |
| Recepción OC → stock insumo | ✅ | Smoke E6 OC-2026-0001 (entrada real 24-sep: BLANCO 0→1 Kg) + smoke CLI 2026-09-28 en **transacción revertida** con las guardas nuevas |
| Comprobantes pago email/WhatsApp | ✅ | Preview en TEST |
| Servicios recurrentes | ✅ | |
| Recepción **producto reventa** (PT) | ⏸ | **N/A**: 0 SKU `Reventa` (497/497 `Fabricado`) y `detalle_orden_compra.insumo_id` `NOT NULL` + FK. Si negocio lo pide → patrón `insumos.producto_id` |
| Unidades preorden → OC (`convertir_unidad_insumo`) | ✅ | Conversión en `PreordenesModel::aprobar()`; smoke CLI 2026-09-28 (1500 g → 1.5 Kg) |
| Idempotencia preorden (no duplicar OC) | ✅ | Código: `aprobar()` rechaza si `estatus !== 'Pendiente'`; confirmado en UI real 2026-09-28 (re-autorizar PRE-2026-0012 → bloqueado) |
| Validación de recepción (estatus, línea ajena, sobre-recibo) | ✅ | `recibir_mercancia()` en dos pasadas; smoke CLI 2026-09-28 |
| Enlace OC ↔ factura compra | ⚠️ | Contabilidad lee OC; UI enlace diferido |
| PDF OC estilo plantilla Excel (importe letra, etc.) | ⚠️ | Ver checklist §H |

---

## 5. Producción

| Función | Estado | Notas |
|---------|--------|-------|
| Productos, formulaciones, BOM, simulador | ✅ | Overhaul cerrado |
| Dashboard pedidos OV/obras | ✅ | D3 ✅; **D5–D6 con evidencia real 17–18-sep** (`PESAJE-venta-28` → lote 1 + entrada PT sin doble descuento). Re-smoke UI pendiente: ninguna orden abierta pasa hoy el filtro de insumos (2 por stock, 2 por unidades ambiguas) |
| Pesaje, merma 20 % servidor | ✅ | |
| Completada sin pesaje bloqueada | ✅ | D4 smoke |
| Guarda al completar con unidades ambiguas | ⚠️ | **Hallazgo 28-sep**: con `revision_manual` no vacío e `insumos` vacío, `puede_completar_produccion()` devuelve `ok = true` (comprobado en `OV-2025-0013`): el atajo `empty($insumos)` se evalúa antes del chequeo de `revision_manual`. Fix P1 en `TODO.md` §4.4 |
| Lote, etiqueta, consultar lote | ✅ | |
| Preorden compra desde faltantes | ✅ | |
| Touchscreen / catálogo planta | ✅ | |
| Control calidad viscosidad | ❌ | Cotización §8 |
| Notificaciones áreas al completar | ⚠️ | Solo cambio estatus BD |

---

## 6. CRM Ventas

| Función | Estado | Notas |
|---------|--------|-------|
| Clientes / prospectos, contactos, Excel | ✅ | |
| Seguimientos (lista) | ✅ | |
| Descuentos por cliente | ✅ | |
| Historial ventas/cotizaciones | ✅ | |
| Convertir cotización → OV | ✅ | |
| Órdenes listado, pagos, PDF cotización | ✅ | |
| Reenvío cotización email | ⚠️ | Link + SMTP a validar |
| POS sucursales, guard $0, recibo | ✅ | |
| Cotización sin preorden / confirmar con preorden | ✅ | Smoke B4–B5 |
| Cobro mostrador sin PT negativo | ✅ | Fix 2026-09-24; **re-smoke B6 ✅ 2026-09-28** (CLI+BD, transacción revertida). Guardas nuevas: idempotencia de entrega + líneas consolidadas por producto |
| Pedido → En Preparación → almacén entrega | ✅ | Regla en §9.6 `REGLAS_TECNICAS`; guardas + smoke **J1–J5** 2026-09-28 en transacción revertida (los pendientes reales no tienen PT libre fuera de OVs intocables) |
| Obras listado CRM (`ObrasVentas`) | ✅ | |
| Calendario CRM | ❌ | Mejora futura |
| Pasarela pagos online | ⏸ | Contrato §6 |
| Autofactura cliente | ⏸ | Contrato §5/§10 |

---

## 7. Obras (ingeniería / cálculo materiales)

| Función | Estado | Notas |
|---------|--------|-------|
| CRUD obra, productos, BOM, archivos | ✅ | |
| Preorden solo al aprobar / compromiso | ✅ | |
| PDF resumen técnico materiales | ✅ | `pdf_resumen.php` |
| Entregas tab Obras + CRM | ✅ | |
| Pagos parciales, estatus ENUM | ✅ | |
| Smoke estatus UI (§C) | ⚠️ | |
| Carátula documental | ❌ | `entrenamiento_4/` |
| Resumen general contractual | ❌ | Distinto PDF técnico |
| Generador / catálogo conceptos | ❌ | `entrenamiento_4/` |

---

## 8. Facturación

| Función | Estado | Notas |
|---------|--------|-------|
| OAuth Facture, sandbox, emitir, sync, PDF/XML | ✅ | |
| `config/factureapp.php`, `cli_probe` | ✅ | |
| Dashboard “conectado” visible | ⚠️ | Token vía CLI |
| Email PDF+XML | ❌ | UI pendiente |
| Go-live micontador | ⏸ | Decisión negocio |
| Alertas facturas faltantes OV/OC | ❌ | |
| Autofactura | ⏸ | |
| POS snapshot vs API real | ⚠️ | Auditar filas `facturas` |

---

## 9. Contabilidad

| Función | Estado | Notas |
|---------|--------|-------|
| Catálogo cuentas, pólizas, bancos | ✅ | |
| Orígenes → pólizas borrador (idempotente) | ✅ | |
| Reportes BG, ER, balanza, diario, mayor, IVA | ✅ | Balanza con pólizas autorizadas |
| Nómina contable, servicios recurrentes | ✅ | |
| Autorizar pólizas TEST (smoke I2) | ⚠️ | Manual checklist |
| DIOT, conciliación auto, export COI pleno | ⏸ | Fuera I4 |

---

## 10. Almacén

| Función | Estado | Notas |
|---------|--------|-------|
| Dashboard mín/máx | ✅ | |
| Inventario insumos y PT, ajustes | ✅ | Movimiento `Entrada`/`Salida` mueve stock vía trigger `tr_actualizar_stock_producto`; valida tipo, cantidad, motivo y `usuario_id` de sesión (clave `id`) — smoke J6 28-sep-2026 |
| Entregas OV y obras | ✅ | 28-sep-2026: guardas en `AlmacenModel::registrar_entrega()` (estatus origen, partida propia, pendiente, **stock**) + smoke A1/A3 en tx revertida |
| Idempotencia de entrega | ✅ | Rechaza OV/obra ya `Entregada`/`Completada`; no hay doble descuento de PT |
| Trigger stock ventas/producción | ✅ | `tr_actualizar_entrega_almacen` cierra `cantidad_entregada` y estatus del origen |
| Salida por lector QR/barcode | ❌ | Hardware + UI |
| Tres Guerras rastreo | ⏸ | `PLAN_ENVIOS_TRES_GUERRAS.md` |

---

## 11. Reportes (transversal)

| Función | Estado | Notas |
|---------|--------|-------|
| Contabilidad (varios) | ✅ | |
| Bitácora usuarios | ✅ | |
| RH / Ventas / Compras / Almacén export dedicado | ⚠️ | Parcial por módulo |
| Hub “Reportes” único diagrama | ❌ | Disperso en módulos |

---

## Tasklist agente — cerrar **Proveedores/Compras** al 100 % operativo

Copiar este bloque a un chat Agent. Prefijo **TEST-QA-**. No autorizar `PRE-2026-0001`. No enviar correos reales.

### Objetivo
Dejar compras listo para operación diaria: preorden → OC → recepción (insumo y reventa si aplica) → stock correcto → checklist E en ✅.

### Tareas

- [x] **T1 — Smoke E completo (UI real 2026-09-28):** ejecutar E1–E8 del checklist manual; anotar folios en hoja de resultados.
- [x] **T2 — Idempotencia E5 (código verificado):** dos intentos de autorizar la misma preorden TEST; confirmar una sola OC. Documentar si falla.
- [x] **T3 — Unidades preorden→OC (reformulado 2026-09-28):** la premisa original era incorrecta — `detalle_orden_compra` **no** guarda unidad. Se convierte en `PreordenesModel::aprobar()` (unidad de la pre-orden → `insumos.unidad_medida`, aborta si no es convertible) y `recibir_mercancia()` valida que la cantidad no exceda el pendiente. Verificado por CLI.
- [x] **T4 — Reventa (N/A documentado):** recepción OC línea producto terminado → `movimientos_productos` Entrada, **sin** pesaje/BOM. Si no hay SKU reventa, documentar “N/A” y dejar stub en modelo si falta rama en `recibir_mercancia`. → **Hecho:** N/A documentado con evidencia (0 productos `Reventa`); **no** se agregó rama PT porque el esquema la impide (`insumo_id` `NOT NULL` + FK).
- [x] **T5 — Preorden sin proveedor (código verificado):** al autorizar, mensaje claro o proveedor sugerido desde insumo; no 500 silencioso.
- [x] **T6 — Código (aplicado 2026-09-28):** ajuste mínimo en `OrdenesCompraModel::recibir_mercancia` / controlador; reutilizar `convertir_unidad_insumo`.
- [x] **T7 — Docs:** marcar ✅/⚠️ en **§4 Proveedores/Compras** de este archivo y en `TODO.md` §4.1; no duplicar páginas nuevas.

### Resultado del cierre técnico (2026-09-28)

Validado por CLI (método temporal `compras/OrdenesCompra/cli_smoke_recepcion`, **ya retirado**) y consultas
**solo lectura** a BD. Verificación posterior: la BD quedó intacta — mismas 5 OC, mismas 8 preórdenes
(`PRE-2026-0001` sigue `Pendiente`), `insumos 1/61` en 14.00/1.00 y 0 movimientos en OC 4.

> **Nota de alcance (28-sep-2026, auditoría posterior):** ese “5 OC / 8 preórdenes” es el **estado previo al smoke
> UI** del mismo día. Tras el smoke UI el conteo quedó en **7 OC / 10 preórdenes** (se crearon y cancelaron
> `OC-2026-0002/0003`, `PRE-2026-0009` quedó `Rechazada` y `PRE-2026-0012` `Convertida` con su OC cancelada).
> El dato vigente es el del bloque *Evidencia smoke UI*; `insumos 1/61` sigue en 14.00 / 1.00.

| Caso | Esperado | Observado |
|------|----------|-----------|
| Recepción sobre OC `Recibida` | Bloquea | ✅ "No se puede recibir mercancía de una orden en estatus Recibida" |
| Orden inexistente | Bloquea | ✅ "Orden no encontrada" |
| Línea de otra OC (detalle 15 en OC 4) | Bloquea | ✅ "La línea #15 no pertenece a la orden OC-2026-DEMO2" |
| Sobre-recibo (999 de 15) | Bloquea | ✅ "La cantidad a recibir (999) excede el pendiente (15) de la línea #10" |
| Recepción sin cantidades | Bloquea | ✅ "No hay cantidades válidas por recibir" |
| Línea inexistente | Bloquea | ✅ "La línea #999999 no existe en la orden de compra" |
| Conversión 1000 g → Kg | 1 Kg | ✅ `cantidad_convertida: 1` (`familia: masa`) |
| Conversión 2 Cubeta → Kg | Aborta | ✅ "No hay una conversión segura definida entre Cubeta y Kg" |
| Recepción positiva (OC-2026-DEMO2, 1 Cubeta) | Stock 14→15, OC `Recibida Parcial`, movimiento con unidad | ✅ dentro de transacción revertida; post-rollback 0.00 / 14.00 / `Enviada` / 0 movimientos |
| `aprobar()` con unidad incompatible (Kg → insumo Cubeta) | Aborta sin OC | ✅ pre-orden sigue `Pendiente`, 0 OC generadas |
| `aprobar()` con unidad convertible (1500 g → insumo Kg) | OC con 1.5 Kg | ✅ `detalle.cantidad_solicitada = 1.50` |

### Evidencia smoke UI (2026-09-28, sesión real autenticada)

Ejecutado contra `https://erp.chisarecubrimientos.com.mx` con el usuario de presentación, sesión por cookie
y llamadas a los **endpoints reales** (`curl`). Sin correos reales (`simular_correo_ajax` es preview;
`enviar_correo_real_ajax` **no** se invocó) y **sin mover stock**.

| Ítem | Acción | Resultado observado |
|------|--------|---------------------|
| E1 | `/compras/Proveedores` + `lista_ajax` + detalle | ✅ 200 · 9 proveedores · detalle del proveedor 1 (COMEX) |
| E2 | Insumos vinculados + OC del proveedor + historial | ✅ insumo 1 a $100 · OC-2026-DEMO1 · historial paginado |
| E3 | Crear OC TEST (1 línea) | ✅ `OC-2026-0003` en Borrador (insumo 61, 1 Kg, $10) |
| E4 | PDF/print de la OC | ✅ 200, HTML imprimible con **importe con letra** y firmas (gap: no es PDF binario) |
| E5 | Autorizar pre-orden TEST | ✅ `PRE-2026-0012` (2000 g) → `OC-2026-0002` en Borrador |
| E5b | **Conversión de unidades en UI** | ✅ `detalle.cantidad_solicitada = 2.00` Kg (venía en 2000 g) |
| E6 | Recepción → stock | ✅ 24-sep OC-2026-0001 (BLANCO 1 Kg) + lógica validada por CLI. **No se repitió** para no alterar inventario real |
| E7 | Pagos + comprobante | ✅ `get_pagos_orden_ajax` (PAGC-2026-0001 $1,740) + modal de pago/comprobante presente en la vista |
| E8 | Preview correo + WhatsApp | ✅ destinatario, asunto y cuerpo generados; **no** se envió SMTP |
| T5 | Autorizar pre-orden **sin** proveedor | ✅ "No hay proveedor sugerido ni seleccionado…" (0 OC creadas) |
| T2 | Re-autorizar la misma pre-orden | ✅ "Solo se pueden autorizar pre-órdenes en estatus Pendiente" (sin OC duplicada) |
| Fix nuevo | Recibir mercancía en OC `Borrador` | ✅ "No se puede recibir mercancía de una orden en estatus Borrador" (0 movimientos de inventario) |

**Limpieza (regla TEST-QA-):** `OC-2026-0002` y `OC-2026-0003` → **Cancelada**; `PRE-2026-0009` → **Rechazada**
(motivo "TEST-QA cierre smoke UI…"); `PRE-2026-0012` → **Convertida** con su OC cancelada.
`PRE-2026-0001` y `OV-2026-0009` **sin tocar**. `insumos.stock_actual` de BLANCO sigue en `1.00` y
`movimientos_inventario` no recibió filas de las OC TEST.

**Nota:** el smoke de escritura usó folios automáticos del ERP (`OC-2026-0002/0003`, `PRE-2026-0009/0012`);
la marca `TEST-QA-` quedó en `observaciones`, `notas` y `motivo_rechazo`. Commit de código: `fix(compras)`.

### Archivos clave
`compras/OrdenesCompra.php`, `OrdenesCompraModel.php`, `compras/Cotizaciones.php`, `compras/Proveedores.php`, `compras/Insumos.php`.

### Criterio de “100 %”
Todas T1–T5 ✅ o SKIP documentado; T6 solo si hubo bug; checklist bloque **E** en ✅.

---

## Tasklist agente — segundo módulo sugerido: **Almacén**

- [x] **A1 — Entrega OV En Preparación** con PT ≥ cantidad: `almacen/Entregas` baja PT **una vez**. → ✅ 28-sep-2026 smoke CLI+BD en **transacción revertida** (parcial 1 de 2 → cierre `Entregada` + `fecha_entrega_real`; kardex en `movimientos_productos`). Datos reales: solo PT con stock ≤ 0, así que el camino feliz se validó con stock inyectado dentro de la misma tx (sin residuos).
- [x] **A2 — Coherencia con POS:** mostrador Entregada descuenta PT en POS; pedido confirmado descuenta en almacén al entregar — documentado en `REGLAS_TECNICAS` **§9.6 Ventas / POS**. ✅ 28-sep-2026
- [x] **A3 — Smoke** bloque almacén: ✅ 28-sep-2026 bloque **J. Almacén** agregado al checklist manual (J1–J7). Casos: entrega parcial/cierre, stock insuficiente (OV y obra), sobre-entrega, partida ajena, producto que no coincide, doble entrega, consolidación de líneas. 0 residuos.
- [x] **A4 — QR / Tres Guerras:** ⏸ explícito (J7 + §10); no se implementa en este sprint.
- [x] **Extra J6 (28-sep-2026) — auditoría de ajustes:** `Inventario::ajustar_stock_ajax()` y `produccion/Productos::ajustar_stock_ajax()` validan tipo (`Entrada`/`Salida`), cantidad numérica > 0 y motivo obligatorio, y leen el usuario de la clave de sesión **`id`** (antes `userdata('user_id')` → `movimientos_productos.usuario_id` NULL). Smoke CLI `almacen/Inventario/cli_smoke_j6` en tx revertida. Decisión: `Salida` > stock permitida a propósito (corrige sobre-conteos) informando el stock resultante.

---

## Auditoría de usuario en sesión (transversal, 28-sep-2026)

La sesión de CHISA expone el id del usuario en la clave **`id`** (`Auth::_create_user_session()` escribe `id`, `role`,
`name`, `email`, `departamento`); **`user_id` nunca se escribe**. Por eso los **55 usos** de
`$this->session->userdata('user_id')` repartidos en **19 archivos** devolvían `NULL` o caían al fallback `?: 1`
(atribución falsa al usuario 1) en Almacén, Ventas, Producción, Obras, Contabilidad y RH.

- **Cambio:** todos los sitios usan `session->userdata('id')`; los patrones redundantes
  (`userdata('user_id') ?: userdata('id')`) se simplificaron a `userdata('id')`. Se conservan los fallbacks
  `?: 1` / `?: 0` solo para contextos CLI/cron sin sesión.
- **Sin cambio de flujo:** las 55 lecturas eran asignaciones a campos de auditoría (ninguna en `WHERE`/comparación,
  verificado con `grep`); `php -l` OK en los 18 archivos PHP modificados.
- **Daño histórico (solo lectura, no se reescribe):** `empleados.usuario_alta_id` 18/18 NULL,
  `cuentas_contables.usuario_creacion` 16/16 NULL, `movimientos_inventario.usuario_id` 17/27 NULL,
  `obras_productos.agregado_por` 6/6 = usuario 1, `facturas_obras.creado_por` 1/1 = usuario 1.
- **Limpieza:** eliminado el snippet huérfano `application/controllers/produccion/ajustar_stock_method.php`
  (copia del endpoint antiguo de ajuste, sin `<?php` ni clase, no enrutable).
- **Regla permanente:** `REGLAS_TECNICAS` **§2.1 Sesión y Autenticación**.

---

## Mantenimiento de este documento

Al cerrar un módulo, cambiar su fila en la tabla “¿Qué módulo cerrar…?” y marcar funciones ✅. Referenciar desde `doc/TODO.md` § Documentos vigentes.

---

## Nota para agentes (DeepSeek / entorno sin terminal)

Si **no hay salida de terminal** ni escritura al mismo workspace que Cursor:

| Tarea | Sin terminal | Con Cursor Agent + shell/browser |
|-------|----------------|----------------------------------|
| T1 smoke UI E1–E8 | ❌ | ✅ Browser ERP o manual Fausto |
| T2 idempotencia | ✅ **Código:** `PreordenesModel::aprobar` rechaza si `estatus !== 'Pendiente'` | ✅ Re-autorizar misma PRE → debe fallar |
| T3 unidades recepción | ✅ **Cerrado 2026-09-28:** la línea de OC no guarda unidad; la conversión va en `PreordenesModel::aprobar()` (preorden→insumo) y `recibir_mercancia()` valida antes de escribir | Falta evidencia UI |
| T4 reventa PT | ✅ **N/A:** 0 productos `Reventa` y `insumo_id` es `NOT NULL` + FK → si negocio lo pide, vía `insumos.producto_id` | — |
| T5 sin proveedor | ✅ **Código:** mensaje en `aprobar` línea ~259 | Smoke UI |
| T6 código | ✅ Editar archivos vía IDE | ✅ |
| T7 docs | ✅ Editar este `.md` y `TODO.md` | ✅ |

**Hallazgos estáticos (2026-09-28, Cursor):** T2 y T5 ya cubiertos en backend. T3/T4 requieren desarrollo antes de marcar §4 Compras al 100 %.

**Actualización 2026-09-28 (agente con shell + BD):** T3/T6 **aplicados y verificados** — `aprobar()` convierte la cantidad a la unidad del insumo (aborta si no es convertible) y `recibir_mercancia()` valida en dos pasadas (estatus, pertenencia de línea, sobre-recibo). T4 **N/A documentado** (0 productos `Reventa`).

**CLI útil (solo entorno con acceso):** desde `public_html`:  
`/usr/local/php82/bin/php index.php …` — no hay comando CLI permanente de recepción OC. Patrón válido para smoke sin UI: método temporal con guard `is_cli()` + envolver la llamada en `$this->db->trans_begin()` / `trans_rollback()` para probar recepciones sin dejar rastro en la BD (retirar el método al terminar).
