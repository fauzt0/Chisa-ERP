# Handoff — Módulo Compras/Proveedores (Presentación 10 Jul 2026)

> **Estado:** ✅ **LISTO PARA PRESENTACIÓN** — auditoría general 10 Jul 2026 ~06:05.

---

## Login demo
- **Usuario:** `presentacion@chisa.mx`
- **Contraseña:** `Demo2026!`
- **Permisos compras:** add, edit, consult, recepción, preórdenes, documentos, **pagos**, **servicios_recurrentes**, categorías
- **Permisos proveedores:** add, edit, consult, insumos
- **Otros:** reportes_compras, produccion_preordenes, user_bitacora

---

## Scorecard auditoría general (todo lo añadido)

| Área | Verificación HTTP / BD | Estado |
|------|------------------------|:------:|
| **5 pantallas** | Proveedores, OC, Insumos, Categorías, Servicios Recurrentes → 200 | ✅ |
| **Pre-órdenes** | listar, consultar, historial PRE-2026-0001 Pendiente | ✅ |
| **DataTables OC** | 4 filas, 7 columnas (incl. Pago) | ✅ |
| **DataTables proveedores** | 8 proveedores (6 materiales + 2 servicios) | ✅ |
| **Pagos OC** | `marcar_pagado_ajax` → OC-2026-DEMO2 Pagado + PAGC-2026-0001 | ✅ |
| **Adeudos OC** | Columna Pago + filtro estatus pago + modal $ | ✅ |
| **Servicios recurrentes** | 2 activos, calendario jul-2026 | ✅ |
| **Pago servicio** | Internet julio → Pagado; Soporte → Pendiente | ✅ |
| **Tab Servicios proveedor** | Telmex offcanvas + `pagos_proveedor_ajax` | ✅ |
| **Recepción OC** | OC-2026-DEMO1 Recibida, stock insumo 1 = 14 | ✅ |
| **Recepción desde Proveedores** | UI `recibirMercanciaProveedor` + modal | ✅ |
| **Inventario → Producción** | `movimientos_inventario` + trigger stock | ✅ |
| **PDF / CSV / reporte** | HTTP 200 | ✅ |
| **Comentarios / documentos OC** | AJAX OK | ✅ |
| **Permisos + bitácora** | controllers con `requiere_permiso` / `registrar_bitacora` | ✅ |
| **UI sin Swal/toastr** | views/compras limpio | ✅ |
| **Sidebar** | Servicios Recurrentes en menú Proveedores | ✅ |

---

## Datos demo actuales (post-auditoría)

| Elemento | Estado |
|----------|--------|
| PRE-2026-0001 | Pendiente (autorizable en vivo) |
| OC-2026-DEMO1 | **Recibida** — stock Pintura Vinílica = 14 |
| OC-2026-DEMO2 | **Enviada**, pago **Pagado** (audit test) |
| Telmex — Internet jul | **Pagado** |
| Soporte TI — jul | **Pendiente** (demo pago en vivo) |
| Proveedores Servicios | Telmex (id=7), Soporte TI (id=8) |

**Reset demo completo:**
```bash
mysql st32477_chisa < public_html/database/seed_demo_presentacion_compras.sql
mysql st32477_chisa < public_html/database/seed_servicios_recurrentes_demo.sql
```

---

## Guion demo ampliado (~30 min)

### 1. Proveedores materiales (7 min)
- Química Central → offcanvas → insumos → Nueva OC

### 2. Pre-órdenes + OC (8 min)
- PRE-2026-0001 → consultar → autorizar
- OC manual → aprobar → simular correo

### 3. Pagos y adeudos (5 min)
- Tab OC → filtro "Con adeudo" → registrar pago parcial/total
- OC-2026-DEMO2 ya Pagado (mostrar badge verde)

### 4. Servicios recurrentes (5 min)
- `Compras → Servicios Recurrentes`
- Calendario julio → pagar Soporte TI pendiente
- Proveedores → Telmex → tab Servicios

### 5. Recepción → Producción (3 min)
- OC-2026-DEMO1 ya Recibida → `compras/Insumos` stock 14
- Mencionar: recepción también desde offcanvas proveedor (botón camión)

### 6. Bitácora (2 min)
- Filtrar Compras / Proveedores

---

## Rutas clave

| Función | Ruta |
|---------|------|
| Proveedores | `/compras/Proveedores` |
| Órdenes de compra | `/compras/OrdenesCompra` |
| Servicios recurrentes | `/compras/ServiciosRecurrentes` |
| Insumos / stock | `/compras/Insumos` |

---

## Migraciones SQL aplicadas

| Archivo | Contenido |
|---------|-----------|
| `database/pagos_ordenes_compra.sql` | Tabla pagos OC + estatus_pago |
| `database/seed_servicios_recurrentes_demo.sql` | Proveedores servicio + pagos demo |

---

## Checklist navegador (última milla)

- [x] Login demo (HTTP)
- [x] Todas las pantallas cargan
- [x] Pago OC funciona (probado)
- [x] Pago servicio recurrente funciona (probado)
- [x] Recepción OC → stock (probado)
- [ ] **Tú:** autorizar PRE-2026-0001 en UI
- [ ] **Tú:** pagar Soporte TI julio en Servicios Recurrentes
- [ ] **Tú:** offcanvas Telmex → tab Servicios

---

*Última actualización: 10 Jul 2026 06:05 — auditoría general PASS.*
