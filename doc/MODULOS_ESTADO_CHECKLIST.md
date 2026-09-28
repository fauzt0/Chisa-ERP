# Checklist de módulos ERP CHISA — estado y cierre

**Fecha:** 2026-09-28 · **Rama:** `iteracion-4`  
**Leyenda:** ✅ Operativo / verificado · ⚠️ Parcial o falta smoke · ❌ No implementado · ⏸ Fuera de alcance operativo (contrato o I5+) · 🔒 Bloqueado por negocio/datos

**Fuentes:** menú `sidebar.php`, auditoría diagrama 2026-09-24, smoke P0, `doc/TODO.md`.  
**Smoke detallado:** `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md`.

---

## ¿Qué módulo cerrar al 100 % primero?

| Prioridad | Módulo | % operativo* | Por qué |
|-----------|--------|--------------|---------|
| **1 (recomendado)** | **Proveedores / Compras** | ~88 % | E5–E6 smoke ✅; pocos ítems pendientes (reventa, unidades, idempotencia preorden, checklist E). Sin pasarela ni timbrado. |
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
| Preorden → autorizar → OC | ⚠️ | Smoke E5 ✅; insumo sin proveedor pide selección manual |
| Recepción OC → stock insumo | ✅ | Smoke E6 OC-2026-0001 |
| Comprobantes pago email/WhatsApp | ✅ | Preview en TEST |
| Servicios recurrentes | ✅ | |
| Recepción **producto reventa** (PT) | ❌ | TODO §4.1 |
| Unidades recepción (`convertir_unidad_insumo`) | ⚠️ | Smoke pendiente |
| Idempotencia preorden (no duplicar OC) | ⚠️ | Smoke formal E5 |
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

- [ ] **T1 — Smoke E completo:** ejecutar E1–E8 del checklist manual; anotar folios en hoja de resultados.
- [ ] **T2 — Idempotencia E5:** dos intentos de autorizar la misma preorden TEST; confirmar una sola OC. Documentar si falla.
- [ ] **T3 — Unidades recepción:** OC con línea en unidad distinta a stock insumo; usar `convertir_unidad_insumo` en recepción; verificar `movimientos_inventario` + `stock_actual` (solo lectura SQL o UI inventario).
- [ ] **T4 — Reventa (si hay SKU reventa en BD):** recepción OC línea producto terminado → `movimientos_productos` Entrada, **sin** pesaje/BOM. Si no hay SKU reventa, documentar “N/A” y dejar stub en modelo si falta rama en `recibir_mercancia`.
- [ ] **T5 — Preorden sin proveedor:** al autorizar, mensaje claro o proveedor sugerido desde insumo; no 500 silencioso.
- [ ] **T6 — Código (solo si T3/T4 fallan):** ajuste mínimo en `OrdenesCompraModel::recibir_mercancia` / controlador; reutilizar `convertir_unidad_insumo`.
- [ ] **T7 — Docs:** marcar ✅/⚠️ en **§4 Proveedores/Compras** de este archivo y en `TODO.md` §4.1; no duplicar páginas nuevas.

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
| T3 unidades recepción | ⚠️ **Gap:** `recibir_mercancia` **no** llama `convertir_unidad_insumo`; suma cantidad cruda al stock | Implementar T6 |
| T4 reventa PT | ⚠️ **Gap:** solo `insumo_id`; `producto_id` siempre null | Implementar rama PT o N/A documentado |
| T5 sin proveedor | ✅ **Código:** mensaje en `aprobar` línea ~259 | Smoke UI |
| T6 código | ✅ Editar archivos vía IDE | ✅ |
| T7 docs | ✅ Editar este `.md` y `TODO.md` | ✅ |

**Hallazgos estáticos (2026-09-28, Cursor):** T2 y T5 ya cubiertos en backend. T3/T4 requieren desarrollo antes de marcar §4 Compras al 100 %.

**CLI útil (solo entorno con acceso):** desde `public_html`:  
`/usr/local/php82/bin/php index.php …` — no hay comando CLI de recepción OC; smoke es UI o script puntual.
