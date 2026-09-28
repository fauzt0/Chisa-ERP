# TODO - Sistema ERP CHISA

**Última actualización:** 2026-09-28  
**Desarrollador:** Fausto Solano - CHISA Recubrimientos  
**Rama activa:** `iteracion-4` (base `main` `ff111ce`; commits I4 locales sin push obligatorio)

**Handoff:** `ENVIRONMENT=development`. Rama `iteracion-3` **eliminada** (local + `origin`) el 2026-09-24 — seguir solo en `iteracion-4` / `main`. No timbrar/cobrar real, no autorizar `PRE-2026-0001`, no tocar `OV-2026-0009`. **Smoke:** `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` (columna **Aud. P0**). **Cumplimiento oferta:** § Auditoría diagrama abajo (reloj/Bixpe excluido).

---

## 📋 Auditoría diagrama general (2026-09-24)

**Fuentes:** `doc/diagrama_general.png`, `doc/cotizacion.md`, `sidebar.php`, código. **Excluido:** Reloj Checador / Bixpe / KONECT (I5).

### Resumen por módulo

| Módulo | ✅ | ⚠️ | ❌ | N/A |
|--------|----|----|----|-----|
| Admin usuarios | 7 | 1 | 0 | 0 |
| RH | 8 | 3 | 0 | 0 |
| Proveedores/Compras | 7 | 3 | 0 | 0 |
| CRM Clientes | 9 | 1 | 2 | 0 |
| Ventas / POS | 7 | 2 | 1 | 0 |
| Obras / cálculo materiales | 7 | 2 | 3 | 0 |
| Producción | 6 | 2 | 1 | 0 |
| Almacén | 6 | 0 | 2 | 0 |
| Facturación | 4 | 2 | 4 | 0 |
| Contabilidad | 8 | 2 | 2 | 2 |
| Reportes (dispersos) | 2 | 5 | 0 | 0 |
| **Total filas** | **~71 (60%)** | **~21 (18%)** | **~15 (13%)** | **2** |

*Ajustes vs matriz inicial:* RH calculadora baja → **⚠️** (cotización: no calcula finiquito oficial; solo datos en `get_datos_calculadora`). Reenvío cotización → **⚠️** (link + SMTP a validar).

### Top 10 — orden de atención

| # | Gap | P | Validar con checklist |
|---|-----|---|------------------------|
| 1 | Smoke POS: IVA trigger, entrega baja PT | P0 | **B3–B6** |
| 2 | Recepción OC → `insumos.stock` + movimientos | P0 | **E5, E6** |
| 3 | Dashboard producción OV/obras; Completada→PT; 2.º pesaje | P0 | **D3–D6** |
| 4 | Facturación go-live Facture (sandbox→prod + re-OAuth) | P0 | Decisión negocio; `cli_probe` |
| 5 | Liga autofactura cliente por OV | P0 | Nuevo desarrollo (no checklist) |
| 6 | Carátula + resumen general + generador conceptos | P1 | `entrenamiento_4/` (pendiente plantillas) |
| 7 | Control calidad / viscosidad por lote | P1 | Código nuevo |
| 8 | Notificaciones cruzadas OV / lote completado | P1 | B6, alertas |
| 9 | Email factura PDF+XML | P1 | §4.9 diferido |
| 10 | Reportes exportables RH/Ventas/Almacén | P1 | Post-smoke |

**Huecos contractuales (no confundir con bugs I4):** pasarela de pagos online, autofactura, micontador go-live, calendario CRM, DIOT/conciliación (N/A I4).

### Documental obras (diagrama vs hoy)

| Existe | `obras/pdf_resumen.php` (BOM técnico), entregas CRM, cotización/recibo obra |
| Falta (entrenamiento_4) | Carátula, resumen general contractual, generador/catálogo de conceptos |

### Smoke P0 manual (2026-09-24, usuario presentación)

Ejecutado vía `doc/SMOKE_P0_AGENTE_EJECUTOR.md`. Cierre: OV-2026-0010/0011 canceladas; PRE-2026-0008 rechazada; **PRE-2026-0001** y **OV-2026-0009** sin tocar.

| Bloque | Resultado | Notas |
|--------|-----------|--------|
| B3–B5 | ✅ | Cotización sin preorden; confirmación + preorden; IVA 500×0.16 |
| B6 | ⚠️→fix | Cobro mostrador bajó PT a negativo — **corregido** `validar_stock_pt_lineas` + `entregar_orden` transaccional |
| E5–E6 | ✅ | OC-2026-0001; recepción 1 Kg BLANCO |
| D3–D4 | ✅ | Dashboard OV; Completada sin pesaje bloqueada |
| D5–D6 | ⚠️ SKIP | BOM 570 Kg vs 1 Kg stock — repetir con BOM chico o stock completo |
| I + cli_probe | ✅ | Orígenes OK; sandbox `ok: true` |

**P0 cerrados:** #2 Compras, #4 Facture conexión. **#1** tras re-smoke B6 con fix. **#3** pendiente D5–D6 E2E.

---

## 📝 Notas Técnicas

- [ ] Validar límite de `max_input_vars` en PHP para formularios con muchos checkboxes. (IMPORTANTE: en cada deployment). Verificado 2026-09-17: valor efectivo **1000** (default); subir si algún formulario con muchos checkboxes falla.
- Entorno de producción sigue en `ENVIRONMENT=development` (sin 2FA). No cambiar a `production` en I4 salvo decisión explícita.
- Overhaul Obras/Producción P1–P9 **cerrado** (2026-08-20). No reimplementar. Handoff histórico: git `doc/AUDITORIA_OVERHAUL_PRODUCCION_2026-08-20.md` si existía; estándares vigentes: `DOCUMENTACION_TECNICA.md`.

---

## 🟡 Estatus del proyecto

- [X] Desarrollo
- [X] Iteración 3 — cerrada y mergeada a `main` (2026-09-18, `7778571`); rama `iteracion-3` retirada 2026-09-24
- [ ] Iteración 4 — **activa**: 4.0 catálogo, 4.2 POS, 4.3 obras (entregas CRM + estatus), 4.8 contabilidad, 4.9 Facture sandbox. Pendiente: re-smoke UI de POS (B6), 4.4 dashboard/merma UI, datos 4.6. **4.1 Compras: cerrado ✅ 2026-09-28** (técnico + smoke UI E1–E8, T2/T5).
- [ ] Iteración 5 — **no iniciar**: reloj checador (función nueva + auditoría de punches)
- [ ] Despliegue — en producción: `https://erp.chisarecubrimientos.com.mx`

---

## ✅ Iteraciones completadas

- [X] **Proveedores (I5–I6 históricas)** — comprobantes, email/WhatsApp, cotizaciones y comparación
- [X] **Nómina RH (I2 + Planeador)** — automatización, detalle, recibos, enum `'Horas Extras'`
- [X] **Producción / Obras (Overhaul)** — formulaciones, preórdenes, pesaje, etiquetas, m²→kg
- [X] **Import de formulaciones** — `referencia_cliente`
- [X] **CRM Ventas** — contactos extra, carga masiva, Excel
- [X] **Contraste / responsive** — tema oscuro, badges, tablas
- [X] **Facturación (base I anteriores)** — API Facture App en sandbox; detalle vivo y huecos en §**4.9** (no es go-live)
- [X] **Reloj checador (base)** — `api/ApiReloj`, `rh/RelojChecador`, proxy `doc/iclock/`
- [X] **PDF OC** — estilo Excel, importe con letra, UTF-8
- [X] **Obras I3** — tab Entregas (módulo Obras), trigger almacén, preórdenes/solicitudes, PDF, BUG-1 a BUG-8
- [X] **Entrenamiento 3 (Producción)** — OCR 25 capturas, fases 1–3, BOM-1 (#204 form#968)
- [X] **QA UI I3 + cierre E2E** — BUG-UI-04/05/06/07/08/09; Completada OV-2026-0009 + lote `PROD-20260918-22-2268`; descuentos CRUD; ENUM `'Completada'`; `direccion` en `guardar_ajax`; merge a `main` 2026-09-18

---

## 🟢 Iteración 4 — misma área, más detalle (ACTIVA)

No es un entrenamiento masivo nuevo: **no hay Excel adicional en el repo** y PASO 3 (contenido neto de envases) sigue bloqueado por negocio. I4 afina flujos ya existentes. Prefijo TEST-QA-. No cobrar/timbrar real, no autorizar `PRE-2026-0001`, no tocar OV-2026-0009 (Completada).

### 4.1 Compras / proveedores — entradas de insumos y productos
- [X] Recibir OC TEST: smoke E6 OC-2026-0001 — BLANCO 0→1 Kg (`recibir_mercancia`).
- [X] Recibir producto de reventa: **N/A documentado** (2026-09-28) — 0 productos `tipo_producto='Reventa'` en BD y `detalle_orden_compra.insumo_id` es `NOT NULL` + FK. Si negocio lo pide: patrón `insumos.producto_id`.
- [X] Preorden → autorizar → OC **sin duplicar**: garantizado en `PreordenesModel::aprobar()` (rechaza si `estatus !== 'Pendiente'`) y verificado 2026-09-28; falta re-smoke en UI. `PRE-2026-0001` sin tocar.
- [X] Unidades: `convertir_unidad_insumo` aplicado en `aprobar()` (preorden → unidad del insumo). Reformulación: la línea de OC **no** guarda unidad, por lo que la conversión no puede hacerse en `recibir_mercancia`.
- [X] Recepción robusta (2026-09-28): `recibir_mercancia()` valida en dos pasadas estatus de la OC, pertenencia de la línea, cantidad > 0 y sobre-recibo. Evidencia en `MODULOS_ESTADO_CHECKLIST.md` §4.
- [X] PDF OC + preview correo/WhatsApp: preview verificado en UI 2026-09-28 (`simular_correo_ajax` + `whatsapp_texto_ajax`, sin SMTP). **Gap:** `generar_pdf` entrega HTML imprimible, no PDF binario (ver checklist §H).

### 4.2 Ventas — mostrador (POS) vs obras
- [X] Sucursales POS: tabla `sucursales`, OV con `sucursal_id`, selector de caja (sesión). Matriz CDMX sembrada. Stock PT **sigue global** (kardex por sucursal: posterior).
- [X] Guard POS: servidor y UI bloquean `precio_venta <= 0` (usa precio de catálogo, no el del ticket).
- [X] **Indirecta (obra):** agregar producto usa `consultar_insumos_obra` (sin preorden); preórdenes al pasar a **Aprobada** / compromiso (`verificar_insumos_y_preordenes_obra`).
- [X] **Directa (POS):** cotización → confirmar — smoke 24-sep B3–B5 ✅; B6 revalidar tras guard stock PT.
- [X] IVA smoke OV-2026-0010: $80 = (500−0)×0.16 (trigger).
- [X] Guard POS mostrador: no cobrar **Entregada** si `stock_actual` PT &lt; cantidad (`VentasModel::validar_stock_pt_lineas`).
- [ ] Entregas almacén + ciclo completo B6 (PT suficiente) — re-smoke post-fix.

### 4.3 Obras — cálculos y estatus
- [X] Materiales obra: `calcular_materiales_linea_obra` / `calcular_insumos_para_proyecto` sin fallback rendimiento 1.0 del simulador general.
- [X] Validación servidor de estatus ENUM (`ObrasModel::ESTATUS_OBRA_VALIDOS` en `actualizar_obra_desde_post`).
- [X] Tab Entregas en CRM Ventas — `ventas/obras/detalle.php` + partial `obras/partials/seguimiento_entregas.php`.
- [X] SQL de actualización AJAX movido al modelo (`ObrasModel::actualizar_obra_desde_post`; controlador delgado).
- [ ] Smoke manual de estatus en UI (checklist §C; `stricton=false` en MySQL).

### 4.4 Producción / inventario (afinar, no rehacer)
- [ ] Dashboard: pedidos de **OV y obras** visibles; Completada → lote + entrada PT; segundo pesaje bloqueado.
- [X] Merma de pesaje en **servidor**: tope 20% en `ProduccionModel::confirmar_pesaje` (UI aún dice ~20%; caso B3 histórico — validar en smoke D).
- [ ] Escalado BOM y `explotar_bom_plano` en simulador vs obra (mismas cantidades).
- [ ] `grupo_color` en explosión (pendiente de `decisiones_pendientes.md` A1) — solo si toca un caso real de I4.

### 4.8 Contabilidad MX (solo lectura de módulos existentes)
No se cambian Ventas, Compras, Facturación, Nómina ni Almacén. Contabilidad **lee** documentos y arma pólizas/reportes SAT-básicos.
- [X] Catálogo mínimo (Clientes, IVA, Ventas, Inventario, Proveedores, Capital) + ejercicio/periodos del año en curso si faltan.
- [X] Orígenes: CFDI `facturas` Emitida → Ingresos; OC Recibida → Diario (inventario/IVA acreditable/proveedores); nómina Pagada → Egresos. Idempotente por `origen`+`origen_id`.
- [X] Reportes: balanza, balance general, estado de resultados (ya existían); **libro diario, mayor, auxiliar IVA** (trasladado vs acreditable).
- [X] Pólizas en **borrador** hasta autorizar; entonces alimentan balanza/balance. OV sin CFDI **no** se póliza. Sync CLI 2026-09-18: 35 pólizas (7 CFDI, 3 OC, 25 nóminas), 0 desbalanceadas; segunda corrida omitió 35.
- [ ] Fuera de I4: DIOT, XML SAT, conciliación bancaria automática, export Aspel COI/NOI, cobros reales.

### 4.9 Facturación — Facture App (auditoría conexión, 2026-09-18)

Handoff para retomar integración OAuth / timbrado sin re-leer todo el código.

**Integración vigente**

| Pieza | Ubicación |
|-------|-----------|
| Cliente API + OAuth | `application/libraries/FactureApp.php` |
| Ambiente sandbox/prod | `application/config/factureapp.php` (`factureapp_ambiente`) |
| HTTP / timbrado / sync | `application/controllers/facturacion/Facturas.php` |
| Token persistido | `application/models/Facturacion/FacturaApiTokenModel.php` → tabla **`api_tokens`** (`provider = facture_app`) |
| Catálogos SAT (helper) | `application/helpers/facturacion_helper.php` |
| UI dashboard | `application/views/facturacion/dashboard.php` (`$conectado` = hay `access_token` en BD) |
| Callback OAuth | `{base_url}facturacion/Facturas/callback` |
| Conectar / desconectar | `Facturas/conectar`, `Facturas/desconectar` |
| Probe CLI (sin timbrar) | `php index.php facturacion/Facturas/cli_probe` |

**Ambiente activo:** `config/factureapp.php` → `factureapp_ambiente = sandbox` (`app.facture.com.mx`). Producción: cambiar a `produccion` (`app.micontador.mx`) y **re-OAuth** (`conectar`). Secretos solo en ese config (**no** en docs).

**Estado de conexión**

- [X] Registro OAuth en BD: **sí** (`api_tokens`, `user_id` NULL = token global del sistema).
- [X] Última autorización guardada: **2026-02-04** (`updated_at` del registro).
- [X] `expires_in` almacenado: **365**; `refresh_token` en BD **sin** flujo automático (reautorizar con `conectar`).
- [X] Probe CLI **2026-09-24**: `ok: true`, ambiente `sandbox`, mensaje *Petición satisfactoria* (`cli_probe`).

**Datos locales (`facturas`) — recontado 2026-09-18**

- **8** filas con `folio_fiscal`. **7 Emitida** + **1 Cancelada** (`id=6`). Contabilidad I4 → **7** pólizas `origen=facturas`.
- **API real (indicio):** `id=7` folio `1770318424`, sin `orden_venta_id`, con `pdf_path`/`xml_path`.
- **Snapshot POS:** folios `F-OV-2026-*` sin PDF/XML — no timbrar como CFDI real sin auditar.

**Pendientes sugeridos**

- [X] Centralizar ambiente en `application/config/factureapp.php`.
- [X] Documentar renovación de token (comentarios en config + `cli_probe`).
- [ ] Checklist corte a producción: micontador + re-OAuth + smoke timbrado **solo** RFC de prueba hasta go-live.
- [ ] Diferidos: cron/lazy import, vínculo facturas ↔ obras/OC, envío correo PDF/XML.

### 4.5 Logística (si cabe en el sprint)
- [ ] API paquetería Tres Guerras — diseño en `doc/PLAN_ENVIOS_TRES_GUERRAS.md` (no improvisar).

### 4.0 Catálogo web oficial (I4 arranque)
Fuente: [categorías Chisa](https://www.chisarecubrimientos.com.mx/categorias) (22 familias: Recubrimientos, Pinturas, Preparadores, Pastas, Selladores).
- [X] Cruzar familias/REF de la tienda vs `productos` del ERP (2026-09-18, rama `iteracion-4`).
- [X] SKUs existentes: 84 fotos nuevas en `uploads/productos/web_*` (no se pisaron las 31+ que ya tenían imagen; **no** se tocó precio). Quedan ~379 sin foto (colores internos sin ficha en la tienda).
- [X] Familias ausentes dadas de alta (precio 0): COLOR GLASS, MARMO GOT, MARMO ROC.

### 4.6 Datos de negocio (no bloquean el arranque de I4; sí el PASO 3)
- [ ] `rendimiento_m2_por_kg`: 1/314 activas. Lista en `doc/entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md`.
- [ ] Contenido neto CUBETA/GALÓN; presentación de filas "(sin pres.)"; precio #476; `#83` ≡ `#214` EC-1; parafina CHISA PLUS.
- [ ] BUG-DATA-01: ~461 productos sin precio, placeholders, fotos. **No** cargar precios inventados. Fotos de 4.0 cubren las familias de la tienda, no todo el catálogo interno.

### 4.7 Entrenamiento 4 — Ventas/CRM documental (próximo)
- Carpeta prevista: `doc/entrenamiento_4/` (plantillas que cargará negocio).
- Alcance diagrama: **carátula**, **resumen general** (distinto de `pdf_resumen.php` técnico), **generador** y **catálogo de conceptos** por obra.
- **No implementar** hasta tener formatos firmados; reutilizar PDF técnico actual como posible “Anexo”.
- PASO 3 catálogo (rendimientos/neto): sigue en `entrenamiento_3/manifiestos/` si negocio desbloquea datos.

---

## 🔵 Iteración 5 — Reloj checador (NO ejecutar aún)

- [ ] **Nueva función** del reloj (definir alcance con negocio al abrir I5).
- [ ] **Auditoría solamente** (sin cambiar código ni config): comprobar que el ERP **está recibiendo** checadas reales (`api/ApiReloj`, `rh/RelojChecador`, proxy `doc/iclock/`). Tablas/logs de punches, última sync, dispositivos activos. Documentar hallazgo; no “arreglar” en I4.
- [ ] Referencia: `doc/API_RELOJ_CHECADOR.md`, `doc/iclock/GUIA_INSTALACION.md`.

---

## 🟡 Pendientes diferidos (no I4 salvo que se desbloqueen)

- [ ] Correos reales: OV a cliente, OC a proveedor, factura PDF/XML (UI facturación: “Enviar por Correo (Pendiente)” — no implementado).
- [ ] Facturación: cron/lazy import; vincular facturas a obras/OC. **Conexión Facture App:** ver § **4.9**.
- [ ] Smoke módulos: usuarios, permisos, bitácora, citas, calendario, RH (nómina ya existía) — usar checklist manual.
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
| `diagrama_general.png` | Mapa funcional vs oferta |
| `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` | Smoke manual + columna **Aud. P0** |
| `SMOKE_P0_AGENTE_EJECUTOR.md` | Instrucciones agente smoke P0 |
| `MODULOS_ESTADO_CHECKLIST.md` | Estado por módulo + tasklist cierre (prioridad: Compras) |
| `entrenamiento_3/manifiestos/decisiones_pendientes.md` | Decisiones de catálogo/BOM pendientes |
| `entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md` | PASO 3 rendimientos (negocio) |
| `entrenamiento_3/GUION_DEMO_CLIENTE.md` | Guion de demo |

Los prompts/checklists operativos de I3 se archivaron en git (commit previo a esta limpieza). No re-crearlos.

> Overhaul P1–P9 cerrado. I4 **no** reescribe módulos; reutiliza `explotar_bom_plano`, `calcular_insumos_para_proyecto`, `crear_preordenes_desde_faltantes`, `convertir_unidad_insumo`.
