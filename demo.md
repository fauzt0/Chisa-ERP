# Guía de Demostración — 10 Jul 2026

> **Verificada en vivo:** 10 Jul 2026 ~08:00 — todas las rutas y AJAX de esta guía responden OK.
>
> **Login:** `presentacion@chisa.mx` / `Demo2026!`
>
> **URL base:** https://erp.chisarecubrimientos.com.mx

---

## Estado demo actual (BD)

| Elemento | Estado | Uso en demo |
|----------|--------|-------------|
| `PRE-2026-0001` | Pendiente | Autorizar en vivo → genera OC |
| `OC-2026-DEMO1` | Recibida, pago **Parcial** ($660 adeudo) | Botón **$** → registrar pago |
| `OC-2026-DEMO2` | Enviada, **Pagado** | Botón correo simulado |
| Stock insumo #1 (Pintura) | **14** | Tras recepción → producción |
| Internet Telmex jul-2026 | Pagado | Seguimiento mensual ✓ |
| Soporte TI jul-2026 | Pagado | Seguimiento mensual ✓ |
| Basura Ambientales jul-2026 | **Pendiente** $2,800 | Marcar pago en vivo |
| Proveedor materiales | COMEX (id 1) | Offcanvas insumos/OC |
| Proveedor servicios | Telmex (id 7) | Tab Servicios |
| Proveedor basura | Ambientales Centro (id 9) | Pago pendiente jul |

> **Reset completo** (opcional, antes de presentar):  
> `mysql … < public_html/database/seed_demo_presentacion_compras.sql`  
> `mysql … < public_html/database/seed_servicios_recurrentes_demo.sql`

---

## Ruta de demostración (~35 min)

### 1. Dashboard (3 min)
- **Ruta:** `/dashboard`
- **Qué mostrar:** pantalla de inicio, accesos rápidos, campana de notificaciones
- **Verificado:** HTTP 200 ✅

---

### 2. Mi Perfil (3 min)
- **Ruta:** `/usuarios/Perfil`
- **Qué mostrar:** datos del usuario, formulario de cuenta, sección vacaciones (si vinculado)
- **Verificado:** HTTP 200 ✅

---

### 3. Administradores (8 min)

#### 3a. Usuarios
- **Ruta:** `/usuarios/GestionUsuarios`
- **Qué mostrar:** lista de usuarios, stats, alta/edición
- **Verificado:** HTTP 200 ✅

#### 3b. Roles y permisos
- **Ruta:** `/usuarios/Roles`
- **Qué mostrar:** matriz de permisos — destacar `compras_autorizar_preordenes`, `compras_pagos`, `compras_servicios_recurrentes`
- **Verificado:** HTTP 200 ✅

#### 3c. Bitácora
- **Ruta:** `/usuarios/GestionUsuarios/bitacora`
- **Qué mostrar:** filtros por fecha y tipo; acciones de Compras/Proveedores
- **Verificado:** HTTP 200 + `get_user_logs_ajax` ✅

#### 3d. Simulador de alertas (opcional, 2 min)
- **Ruta:** `/usuarios/GestionUsuarios/simulador_alertas`
- **Qué mostrar:** clic **Simular** en 2 cards → toast + campana; **Limpiar** al terminar
- **Verificado:** HTTP 200, 8 tipos ✅

---

### 4. Recursos Humanos (8 min)

#### 4a. Empleados
- **Ruta:** `/rh/RecursosHumanos`
- **Qué mostrar:** cards de stats, banners de alertas, tabla 18 empleados, clic fila → offcanvas detalle
- **Verificado:** HTTP 200 + `search_empleados` ✅

#### 4b. Alta empleado (solo mostrar formulario)
- **Ruta:** `/rh/RecursosHumanos/alta`
- **Verificado:** HTTP 200 ✅

#### 4c. Reloj Checador
- **Ruta:** `/rh/RelojChecador`
- **Qué mostrar:** presentes/ausentes/retardos, últimas checadas, auto-refresh
- **Verificado:** HTTP 200 + `dashboard_stats_ajax` ✅

---

### 5. Proveedores — estrella Compras (5 min)
- **Ruta:** `/compras/Proveedores`
- **Pasos:**
  1. Buscar **COMEX** → clic ojo → `verDetalleProveedor()` → offcanvas tabs **Info / Insumos / Órdenes**
  2. Buscar **Telmex Internet Empresarial** → offcanvas → tab **Servicios** (pagos julio)
  3. En tab Órdenes de COMEX: ver historial `OC-2026-DEMO1`
- **Verificado:** HTTP 200 + `lista_ajax`, `get_proveedor_ajax`, `get_historial_ordenes_compra_ajax`, `pagos_proveedor_ajax` ✅

---

### 6. Servicios Recurrentes (5 min)
- **Ruta:** `/compras/ServiciosRecurrentes`
- **Pasos:**
  1. Tab **Seguimiento mensual** → matriz 12 meses (internet ✓, soporte ✓, basura pendiente)
  2. Clic celda **Basura / jul-2026** → modal pago → registrar $2,800
  3. Tab **Calendario del mes** → resumen por pagar
- **Verificado:** HTTP 200 + `seguimiento_mensual_ajax`, `registrar_pago_ajax`, `resumen_ajax` ✅

---

### 7. Órdenes de Compra (7 min)
- **Ruta:** `/compras/OrdenesCompra`
- **Pasos:**
  1. Sección **Pre-órdenes pendientes** → clic `PRE-2026-0001` → modal consultar → **Autorizar** (genera OC borrador)
  2. Tabla OC → `OC-2026-DEMO1` → botón **$** → ver adeudo $660 → registrar pago parcial o marcar pagado
  3. `OC-2026-DEMO2` → botón **correo** → `simularCorreoProveedor()` → modal con vista previa
  4. Clic PDF en cualquier OC → descarga
  5. Tab **Reporte de Compras** → `cargarReporteCompras()` → exportar CSV
- **Verificado:** HTTP 200 + `lista_ajax`, `lista_preordenes_ajax`, `get_preorden_ajax`, `get_orden_ajax`, `get_pagos_orden_ajax`, `simular_correo_ajax`, `reporte_ajax`, `generar_pdf`, `exportar_reporte_csv` ✅

---

### 8. Insumos — cierre (2 min)
- **Ruta:** `/compras/Insumos`
- **Qué mostrar:** Pintura Vinílica stock **14** (entrada por recepción OC)
- **Verificado:** HTTP 200 + `lista_ajax` ✅

---

### 9. Obras desde Ventas — exportación con marca de agua (5 min)
- **Ruta:** `/ventas/ObrasVentas` → detalle de obra (p. ej. OB-00001) → bloque de pestañas
- **Qué mostrar:**
  1. Pestañas **Partidas · Unitarios (APU) · Generadores · Revisión · Documentos** (5.ª pestaña nueva).
  2. Barra **"Exportar con marca de agua"** (aparece al haber presupuesto): menú **PDF** C1–C8,
     menú **Excel** C1–C8 + **"Libro con las 8 hojas"**, y **"Vista para imprimir"**.
  3. El PDF sale **carta, server-side con mPDF**, con la sucursal en diagonal en TODAS las páginas
     (si `sucursales.texto_marca_agua` está vacío se usa `Sucursal: <nombre>`).
  4. En Excel la marca va en el **encabezado de página** (`oddHeader`) y el libro de 8 hojas trae
     una hoja por reporte (C1..C8).
- **Alternativa módulo Obras:** `/obras/Obras/detalle/{id}` → botón rojo **Exportar presupuesto**.
- **Verificado:** 8/8 PDF (`%PDF`, 2 págs, ExtGState `/CA 0.08` = watermark) y 8/8 XLSX (`PK`,
  `oddHeader = Sucursal: Matriz CDMX`) ✅ · libro de 8 hojas = 18.8 KB ✅

---

## Recordatorios técnicos

| Regla | Detalle |
|-------|---------|
| Toasts | Usar `showErpToast()` — **no** SweetAlert2 ni toastr global |
| Modales | Bootstrap 5: `bootstrap.Modal.getOrCreateInstance(el).show()` |
| Contabilidad | `poliza_id` en pagos servicios — integración posterior |

---

## Checklist pre-presentación (2 min)

- [ ] Login con `presentacion@chisa.mx`
- [ ] Campana sin alertas simuladas viejas (limpiar en simulador)
- [ ] `PRE-2026-0001` visible como Pendiente
- [ ] Basura jul-2026 Pendiente (para pago en vivo)
- [ ] Opcional: ejecutar seeds si quieres OC DEMO1 con adeudo $1,160 completo
- [ ] Obra con presupuesto activo (hoy `PRES-00001`, obra OB-00001) para la demo de exportación PDF/Excel

---

## Documentación de respaldo

- `AUDITORIA_PRESENTACION_10JUL2026.md` — scorecard técnico
- `HANDOFF_COMPRAS_PRESENTACION.md` — detalle módulo Compras
- `PLAN_PRESENTACION_MANANA.md` — plan general actualizado
