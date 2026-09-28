# Checklist de módulos ERP CHISA — estado y cierre

**Fecha:** 2026-09-28 · **Rama:** `iteracion-4`  
**Leyenda:** ✅ Operativo / verificado · ⚠️ Parcial o falta smoke · ❌ No implementado · ⏸ Fuera de alcance operativo (contrato o I5+) · 🔒 Bloqueado por negocio/datos

**Fuentes:** menú `sidebar.php`, auditoría diagrama 2026-09-24, smoke P0, `doc/TODO.md`.  
**Smoke detallado:** `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md`.  
**Última validación técnica:** 2026-09-28 (agente con shell + BD) — smoke CLI de Compras: conversión de unidades preorden→OC y validaciones de recepción. Evidencia en §4 y en la tasklist al final.

---

## ¿Qué módulo cerrar al 100 % primero?

| Prioridad | Módulo | % operativo* | Por qué |
|-----------|--------|--------------|---------|
| **1 (recomendado)** | **Proveedores / Compras** | ~99 % | Cierre técnico 2026-09-28: conversión de unidades preorden→OC, validaciones de recepción, reventa N/A documentado. Pendiente solo evidencia UI (E1–E4, E7–E8). |
| 2 | **Almacén** | ~85 % | Inventario, entregas, ajustes OK; faltan QR y Tres Guerras (⏸ diseño). Alinear entrega OV “En Preparación” vs POS. |
| 3 | **Administración usuarios** | ~92 % | Casi completo; 2FA listo pero ⏸ hasta `ENVIRONMENT=production`. |
| 4 | **CRM Ventas** (sin contrato) | ~78 % | POS/cotizaciones fuertes; ⏸ pasarela, autofactura, calendario CRM. |
| 5 | **Contabilidad** (alcance I4) | ~80 % | Lectura + pólizas borrador OK; ⏸ DIOT, conciliación auto, Aspel pleno. |
| 6 | **Producción** | ~75 % | Core P1–P9 cerrado; D5–D6 E2E pendiente; ⏸ viscosidad/calidad formal. |
| 7 | **Obras** (+ documental) | ~70 % | Técnico OK; ❌ carátula/resumen/generador → `entrenamiento_4/`. |
| 8 | **Facturación** | ~55 % | Sandbox OK; ❌ go-live, email, autofactura. |

\*“100 % operativo” = flujos diarios internos TEST-QA, **sin** promesas contractuales marcadas ⏸/❌.

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
| Recepción OC → stock insumo | ✅ | Smoke E6 OC-2026-0001 + smoke CLI 2026-09-28 |
| Comprobantes pago email/WhatsApp | ✅ | Preview en TEST |
| Servicios recurrentes | ✅ | |
| Recepción **producto reventa** (PT) | ⏸ | **N/A**: 0 SKU `Reventa` (497/497 `Fabricado`) y `detalle_orden_compra.insumo_id` `NOT NULL` + FK. Si negocio lo pide → patrón `insumos.producto_id` |
| Unidades preorden → OC (`convertir_unidad_insumo`) | ✅ | Conversión en `PreordenesModel::aprobar()`; smoke CLI 2026-09-28 (1500 g → 1.5 Kg) |
| Idempotencia preorden (no duplicar OC) | ✅ | Código: `aprobar()` rechaza si `estatus !== 'Pendiente'`; falta evidencia UI |
| Validación de recepción (estatus, línea ajena, sobre-recibo) | ✅ | `recibir_mercancia()` en dos pasadas; smoke CLI 2026-09-28 |
| Enlace OC ↔ factura compra | ⚠️ | Contabilidad lee OC; UI enlace diferido |
| PDF OC estilo plantilla Excel (importe letra, etc.) | ⚠️ | Ver checklist §H |

---

## 5. Producción

| Función | Estado | Notas |
|---------|--------|-------|
| Productos, formulaciones, BOM, simulador | ✅ | Overhaul cerrado |
| Dashboard pedidos OV/obras | ⚠️ | D3 ✅; D5–D6 E2E pendiente |
| Pesaje, merma 20 % servidor | ✅ | |
| Completada sin pesaje bloqueada | ✅ | D4 smoke |
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
| Cobro mostrador sin PT negativo | ✅ | Fix 2026-09-24; **re-smoke B6** |
| Pedido → En Preparación → almacén entrega | ⚠️ | Entrega OV con PT; flujo mixto POS vs almacén |
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
| Inventario insumos y PT, ajustes | ✅ | |
| Entregas OV y obras | ✅ | |
| Trigger stock ventas/producción | ✅ | |
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

- [ ] **T1 — Smoke E completo (pendiente UI):** ejecutar E1–E8 del checklist manual; anotar folios en hoja de resultados.
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

**Pendiente (requiere login UI de presentación):** E1–E4, E7–E8 y la re-autorización de una preorden TEST
en pantalla (T2/T5). Commit de código: `fix(compras)` en `iteracion-4`.

### Archivos clave
`compras/OrdenesCompra.php`, `OrdenesCompraModel.php`, `compras/Cotizaciones.php`, `compras/Proveedores.php`, `compras/Insumos.php`.

### Criterio de “100 %”
Todas T1–T5 ✅ o SKIP documentado; T6 solo si hubo bug; checklist bloque **E** en ✅.

---

## Tasklist agente — segundo módulo sugerido: **Almacén**

- [ ] **A1 — Entrega OV En Preparación** con PT ≥ cantidad (después de producción o ajuste stock TEST): `almacen/Entregas` baja PT una vez.
- [ ] **A2 — Coherencia con POS:** mostrador Entregada descuenta PT en POS; pedido confirmado descuenta en almacén al entregar — documentar regla en `REGLAS_TECNICAS` una línea si hace falta.
- [ ] **A3 — Smoke** bloque almacén (extender checklist si no hay filas; mínimo entrega TEST).
- [ ] **A4 — QR / Tres Guerras:** dejar ⏸ explícito; no implementar en este sprint salvo orden.

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
