# TODO - Sistema ERP CHISA

**Última actualización:** 2026-09-18  
**Desarrollador:** Fausto Solano - CHISA Recubrimientos  
**Rama activa:** `main` (iteración 3 fusionada; **iteración 4** en curso)

---

## 📝 Notas Técnicas

- [ ] Validar límite de `max_input_vars` en PHP para formularios con muchos checkboxes. (IMPORTANTE: en cada deployment). Verificado 2026-09-17: valor efectivo **1000** (default); subir si algún formulario con muchos checkboxes falla.
- Entorno de producción sigue en `ENVIRONMENT=development` (sin 2FA). No cambiar a `production` en I4 salvo decisión explícita.
- Overhaul Obras/Producción P1–P9 **cerrado** (2026-08-20). No reimplementar. Handoff histórico: git `doc/AUDITORIA_OVERHAUL_PRODUCCION_2026-08-20.md` si existía; estándares vigentes: `DOCUMENTACION_TECNICA.md`.

---

## 🟡 Estatus del proyecto

- [X] Desarrollo
- [X] Iteración 3 — cerrada y mergeada a `main` (2026-09-18, `7778571`)
- [ ] Iteración 4 — **activa**: afinar compras, ventas (mostrador/obras), producción, inventario y estatus
- [ ] Iteración 5 — **no iniciar**: reloj checador (función nueva + auditoría de punches)
- [] Despliegue — en producción: `https://erp.chisarecubrimientos.com.mx`

---

## ✅ Iteraciones completadas

- [X] **Proveedores (I5–I6 históricas)** — comprobantes, email/WhatsApp, cotizaciones y comparación
- [X] **Nómina RH (I2 + Planeador)** — automatización, detalle, recibos, enum `'Horas Extras'`
- [X] **Producción / Obras (Overhaul)** — formulaciones, preórdenes, pesaje, etiquetas, m²→kg
- [X] **Import de formulaciones** — `referencia_cliente`
- [X] **CRM Ventas** — contactos extra, carga masiva, Excel
- [X] **Contraste / responsive** — tema oscuro, badges, tablas
- [X] **Facturación** — API Facture App (emisión, sync, download)
- [X] **Reloj checador (base)** — `api/ApiReloj`, `rh/RelojChecador`, proxy `doc/iclock/`
- [X] **PDF OC** — estilo Excel, importe con letra, UTF-8
- [X] **Obras I3** — tab Entregas (módulo Obras), trigger almacén, preórdenes/solicitudes, PDF, BUG-1 a BUG-8
- [X] **Entrenamiento 3 (Producción)** — OCR 25 capturas, fases 1–3, BOM-1 (#204 form#968)
- [X] **QA UI I3 + cierre E2E** — BUG-UI-04/05/06/07/08/09; Completada OV-2026-0009 + lote `PROD-20260918-22-2268`; descuentos CRUD; ENUM `'Completada'`; `direccion` en `guardar_ajax`; merge a `main` 2026-09-18

---

## 🟢 Iteración 4 — misma área, más detalle (ACTIVA)

No es un entrenamiento masivo nuevo: **no hay Excel adicional en el repo** y PASO 3 (contenido neto de envases) sigue bloqueado por negocio. I4 afina flujos ya existentes. Prefijo TEST-QA-. No cobrar/timbrar real, no autorizar `PRE-2026-0001`, no tocar OV-2026-0009 (Completada).

### 4.1 Compras / proveedores — entradas de insumos y productos
- [ ] Recibir OC TEST: entrada de **insumos** actualiza `insumos.stock_actual` + `movimientos_inventario` (tipo Entrada, referencia de OC).
- [ ] Recibir producto de reventa (si aplica): entrada a `productos` / `movimientos_productos` sin disparar pesaje ni BOM.
- [ ] Preorden → autorizar → OC **sin duplicar** (no usar `PRE-2026-0001`).
- [ ] Unidades: `convertir_unidad_insumo`; no mezclar Kg/Cubeta/Pza en la recepción.
- [ ] PDF OC + preview correo/WhatsApp (**no enviar** a proveedores reales).

### 4.2 Ventas — mostrador (POS) vs obras
- [ ] **Directa (POS):** cliente + 1 línea con precio ≠ 0 → cotización **sin** preórdenes → confirmar → `En Preparación` si requiere producción, o surtir si hay stock PT.
- [ ] **Indirecta (obra):** agregar producto a obra **no** genera preorden en borrador; preórdenes solo en documento de compromiso (regla de negocio).
- [ ] Guard POS: avisar o bloquear `precio_venta <= 0` (BUG-DATA-01; #22 REF 308 sigue en $0).
- [ ] IVA = (subtotal − descuento) × 0.16 en ambos caminos (trigger `trg_ordenes_venta_calcular_totales`).
- [ ] Entradas/salidas de OV: al entregar, baja **PT** (`movimientos_productos` Salida); insumos **no** se mueven (ya se descontaron en pesaje).

### 4.3 Obras — cálculos y estatus
- [ ] Materiales: `calcular_insumos_para_proyecto` **sin** fallback rendimiento 1.0; Cubeta/Pza = cantidad×lote; Kg = kg (no 19×19=361).
- [ ] Estatus reales del ENUM: Planificación → En Cotización → Aprobada → En Ejecución → Pausada → Completada / Cancelada. Completada de producción exige pesaje (mismo parseo `in_array` de `forzar`).
- [ ] Tab Entregas también en CRM Ventas (`ventas/obras/detalle.php`) — pendiente de I3 diferido.
- [ ] Mover SQL de `Obras::actualizar_ajax` al modelo (auditoría B5).
- [ ] Smoke de estatus: no truncar ENUM (CI3 `stricton=false` corrompe valores inválidos a `''`).

### 4.4 Producción / inventario (afinar, no rehacer)
- [ ] Dashboard: pedidos de **OV y obras** visibles; Completada → lote + entrada PT; segundo pesaje bloqueado.
- [ ] Merma de pesaje: hoy la UI menciona ~20% pero B3 dejó pasar 66.67% en PIG-003 — decidir tope real y aplicarlo en servidor.
- [ ] Escalado BOM y `explotar_bom_plano` en simulador vs obra (mismas cantidades).
- [ ] `grupo_color` en explosión (pendiente de `decisiones_pendientes.md` A1) — solo si toca un caso real de I4.

### 4.5 Logística (si cabe en el sprint)
- [ ] API paquetería Tres Guerras — diseño en `doc/PLAN_ENVIOS_TRES_GUERRAS.md` (no improvisar).

### 4.6 Datos de negocio (no bloquean el arranque de I4; sí el PASO 3)
- [ ] `rendimiento_m2_por_kg`: 1/314 activas. Lista en `doc/entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md`.
- [ ] Contenido neto CUBETA/GALÓN; presentación de filas "(sin pres.)"; precio #476; `#83` ≡ `#214` EC-1; parafina CHISA PLUS.
- [ ] BUG-DATA-01: ~461 productos sin precio, placeholders, fotos. **No** cargar precios inventados.

### 4.7 Entrenamiento adicional
- **No hace falta un Entrenamiento 4 de OCR/Excel ahora:** no hay `.xlsx` nuevos en el repo; las fichas de `doc/entrenamiento_3/imagenes` + manifiestos ya se usaron en I3.
- Si planta entrega lista 2025 con **contenido neto** o fichas nuevas, retomar PASO 3 con `productos_match.json` / `propuesta_rendimientos_fase3.md` — no re-OCR de las 25 capturas.

---

## 🔵 Iteración 5 — Reloj checador (NO ejecutar aún)

- [ ] **Nueva función** del reloj (definir alcance con negocio al abrir I5).
- [ ] **Auditoría solamente** (sin cambiar código ni config): comprobar que el ERP **está recibiendo** checadas reales (`api/ApiReloj`, `rh/RelojChecador`, proxy `doc/iclock/`). Tablas/logs de punches, última sync, dispositivos activos. Documentar hallazgo; no “arreglar” en I4.
- [ ] Referencia: `doc/API_RELOJ_CHECADOR.md`, `doc/iclock/GUIA_INSTALACION.md`.

---

## 🟡 Pendientes diferidos (no I4 salvo que se desbloqueen)

- [ ] Correos reales: OV a cliente, OC a proveedor, factura PDF/XML (UI facturación: “Enviar por Correo (Pendiente)” — no implementado).
- [ ] Facturación: cron/lazy import; vincular facturas a obras/OC.
- [ ] Smoke módulos: usuarios, permisos, bitácora, citas, calendario, RH (nómina ya existía).
- [ ] Residuos demo: conservar `OV-TEST-001` / `OV-2026-0004` / “Empresa de Prueba S.A.” (guion demo).

---

## 💡 Mejoras futuras

- [ ] Exportar a Excel en tablas; bitácora de cambios; caché de permisos; super-administrador; cumpleaños; recordatorios de citas; calendario CRM; DTO de ViewData; logo en PDF; contraste de alerts/SweetAlert; contrato de usuario.

---

## 📚 Documentos vigentes (`doc/`)

| Documento | Uso |
|-----------|-----|
| `DOCUMENTACION_TECNICA.md` | Arquitectura y estándares |
| `REGLAS_TECNICAS.md` | Reglas para agentes |
| `cotizacion.md` | Requerimientos originales |
| `produccion.md` | Workflow venta → producción → entrega |
| `API_RELOJ_CHECADOR.md` | API reloj / ZKTeco (I5) |
| `SISTEMA_ALERTAS_NOTIFICACIONES.md` | Alertas |
| `GUIA_PRODUCCION_POST_IMPORTACION.md` | Operación post-import |
| `AUDITORIA_MODULO_OBRAS_2026-08-28.md` | Auditoría de Obras (referencia I4) |
| `PLAN_ENVIOS_TRES_GUERRAS.md` | Diseño paquetería |
| `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` | Plantilla de smoke por módulo |
| `entrenamiento_3/manifiestos/decisiones_pendientes.md` | Decisiones de catálogo/BOM pendientes |
| `entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md` | PASO 3 rendimientos (negocio) |
| `entrenamiento_3/GUION_DEMO_CLIENTE.md` | Guion de demo |

Los prompts/checklists operativos de I3 se archivaron en git (commit previo a esta limpieza). No re-crearlos.

> Overhaul P1–P9 cerrado. I4 **no** reescribe módulos; reutiliza `explotar_bom_plano`, `calcular_insumos_para_proyecto`, `crear_preordenes_desde_faltantes`, `convertir_unidad_insumo`.
