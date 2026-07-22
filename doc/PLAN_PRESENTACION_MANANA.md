# Plan de Presentación — Viernes 10 de Julio, 2026

> **Propósito:** Guía de trabajo paralelo para afinar los módulos que se presentarán al cliente mañana, sin interferir con la **Iteración 6 (Producción)** que corre en otro chat.
>
> **Módulos en presentación:** Mi Perfil · Administradores · Recursos Humanos · Reloj Checador · **Proveedores/Compras** (prioridad máxima)
>
> **Rama activa:** `feature/rh`  
> **Stack:** CodeIgniter 3, PHP 7.4, MySQL, AdminLTE/Bootstrap 5, DataTables, Lucide Icons

---

## ⚠️ Restricciones CRÍTICAS de UI (NO violar)

| Componente | ¿Usar? | Motivo |
|------------|:-------:|--------|
| **SweetAlert2 (`Swal`)** | ❌ NO | No está cargado globalmente |
| **Toastr** | ❌ NO | No está cargado globalmente |
| **`showErpToast()`** | ✅ SÍ | Función global en `general_template.php` |
| **Modales** | ✅ Bootstrap 5 | `bootstrap.Modal.getOrCreateInstance(el).show()` |

---

## ✅ Chat A — COMPLETADO (9 Jul 2026, noche)

| ID | Tarea | Estado |
|----|-------|:------:|
| A.1.3 | Pre-órdenes unificadas (`Dashboard.php` → `PreordenesModel`) | ✅ |
| A.1.5 | Filtro `filtro_estatus` en Órdenes de Compra | ✅ |
| A.2.1 | Simulación de correo (`simular_correo_ajax` + modal Bootstrap 5) | ✅ |
| A.2.2 | Reporte de compras (tab + CSV + cards por periodo) | ✅ |
| A.1.1 | Migración preordenes verificada | ✅ |
| A.1.4 | Filtros Insumos | ✅ |
| A.2.3 | Tabs recibidas vs pendientes | ⏸ Pendiente (reporte cubre parcialmente) |
| A.2.4–A.2.6 | Proveedor principal, alias, comparador costos | ⏸ Backlog post-demo |
| A.3 | UI polish | ⏸ Backlog post-demo |

### Datos de demo cargados (actualizado 10 Jul ~06:30)
| Elemento | Detalle |
|----------|---------|
| Pre-orden | `PRE-2026-0001` — Pintura Vinílica Blanca, 25 Cubetas, COMEX, **Pendiente** (autorizable en vivo) |
| OC Recibida | `OC-2026-DEMO1` — $1,160.00, **Recibida**, stock Pintura Vinílica = **14** |
| OC Enviada | `OC-2026-DEMO2` — $1,740.00, **Pagado** (correo simulado ya probado) |
| Servicios recurrentes | Internet jul **Pagado**, Basura y Soporte jul **Pendiente** (demo pago en vivo) |
| Stock bajo | 1 insumo bajo mínimo (alerta campana) |
| Proveedores | 9 activos (materiales + Telmex + Soporte TI) |

> **Reset demo en vivo** (recepción/pago OC):  
> `mysql … < public_html/database/seed_demo_presentacion_compras.sql`  
> `mysql … < public_html/database/seed_servicios_recurrentes_demo.sql`

### Archivos modificados (Chat A)
```
application/controllers/produccion/Dashboard.php
application/controllers/compras/OrdenesCompra.php
application/models/Compras/OrdenesCompraModel.php
application/views/compras/ordenes_compra/main.php
application/views/produccion/dashboard/detalle.php
```
> `private_html` verificado — cambios sincronizados.

---

## ✅ Chat B — COMPLETADO (10 Jul 2026, madrugada)

| ID | Tarea | Estado |
|----|-------|:------:|
| B.1 | Migración `alertas_simuladas` + permiso `admin_simular_alertas` | ✅ |
| B.2 | `AlertasSimuladasModel` + endpoints CRUD en `GestionUsuarios` | ✅ |
| B.3 | Vista con cards por módulo (8 tipos) + preview + limpiar | ✅ |
| B.4 | Integración en `Notifications.php` (mezcla al inicio, permiso-gated) | ✅ |
| B.5 | Permiso en `permissions.php` + asignable en Roles | ✅ |
| B.6 | Link sidebar Administradores → Simulador de Alertas | ✅ |

### 8 tipos simulables
| Tipo | Módulo |
|------|--------|
| `stock_bajo` | Almacén |
| `orden_retrasada` | Ventas |
| `obra_retrasada` | Obras |
| `datos_incompletos` | RH |
| `oc_pendientes` | Compras |
| `productos_sin_formulacion` | Producción |
| `preordenes_pendientes` | Compras |
| `solicitudes_produccion` | Producción |

### Archivos clave (Chat B)
```
database/alertas_simuladas.sql
application/models/Users/AlertasSimuladasModel.php
application/controllers/usuarios/GestionUsuarios.php
application/controllers/Notifications.php
application/views/usuarios/simulador_alertas.php
application/config/permissions.php
application/views/layouts/sidebar.php
```

### Demo rápida
1. `usuarios/GestionUsuarios/simulador_alertas`
2. Clic **Simular** en 2–3 cards → toast + campana
3. **Limpiar simulaciones** al terminar el bloque

> Usuario presentador necesita permiso **"Simular alertas del sistema (demo)"** en Roles.

---

## ✅ Chats C, D, E — COMPLETADOS (10 Jul 2026)

### Chat C — RH
| Tarea | Estado |
|-------|:------:|
| Card stats "Datos Faltantes" con links a editar | ✅ |
| Badge "Datos incompletos" + tooltips Bootstrap | ✅ |
| Banners dismissibles con sessionStorage | ✅ |
| Validación visual + client-side en `editar.php` | ✅ |

### Chat D — Reloj Checador
| Tarea | Estado |
|-------|:------:|
| Card Presentes / Ausentes / Retardos | ✅ |
| Toggle auto-refresh 30s (sessionStorage) | ✅ |
| Seed SQL `database/seed_demo_reloj_presentacion.sql` | ✅ *(ejecutar manualmente)* |

### Chat E — Admin + Perfil
| Tarea | Estado |
|-------|:------:|
| Mi Perfil: empty state con CTA | ✅ |
| Roles: preview badges permisos clave | ✅ |
| Bitácora: filtros fecha + tipo | ✅ |
| Usuario demo `presentacion@chisa.mx` / `Demo2026!` | ✅ *(ejecutar seed)* |

### Seeds pendientes de ejecutar (1 min)
```bash
cd public_html
mysql -u USUARIO -p NOMBRE_BD < database/seed_demo_reloj_presentacion.sql
mysql -u USUARIO -p NOMBRE_BD < database/seed_demo_usuario_presentacion.sql
```

---

## Estado General por Módulo

| Módulo | Estado demo | Urgencia mañana | Chat sugerido |
|--------|:-----------:|:---------------:|---------------|
| **Proveedores/Compras** | 🟢 Demo-ready — OC, pagos, recepción, **servicios recurrentes**, seguimiento mensual | ✅ Listo | ~~Chat A~~ |
| **Simulador de Alertas** | 🟢 Listo — 8 tipos simulables + toast | ✅ Listo | ~~Chat B~~ |
| **Recursos Humanos** | 🟢 Listo — card datos faltantes, badges, validación editar | ✅ Listo | ~~Chat C~~ |
| **Reloj Checador** | 🟢 Listo — presentes/ausentes, auto-refresh, seed SQL | ✅ Listo | ~~Chat D~~ |
| **Administradores** | 🟢 Listo — bitácora filtros, roles preview | ✅ Listo | ~~Chat E~~ |
| **Mi Perfil** | 🟢 Listo — empty state CTA + usuario demo | ✅ Listo | ~~Chat E~~ |

---

## Guion de Demostración Recomendado (30–45 min)

### Bloque 0 — Inicio · 3 min
0. `/dashboard` → vista general del ERP, accesos rápidos y campana de notificaciones

### Bloque 1 — Operación diaria (RH + Reloj) · 10 min
1. `rh/RecursosHumanos` → banners de alertas, lista de empleados, offcanvas de detalle
2. `rh/RelojChecador` → dashboard con gráfica, últimas checadas, panel de alertas
3. `usuarios/Perfil` → expediente vinculado + solicitud de vacaciones

### Bloque 2 — Administración · 5 min
4. `usuarios/GestionUsuarios` → stats, alta/edición, bitácora
5. `usuarios/Roles` → matriz de permisos (mostrar `compras_autorizar_preordenes`)
6. `usuarios/GestionUsuarios/empresa` → logo y datos fiscales (visible en PDFs)

### Bloque 3 — Proveedores/Compras (estrella) · 20 min
7. `compras/Proveedores` → offcanvas CRM (Info / Insumos / Órdenes / **Servicios**)
8. **Recepción mercancía** desde offcanvas (camión) o desde OC
9. `compras/ServiciosRecurrentes` → **Seguimiento mensual** (internet, basura, soporte) → marcar pago pendiente
10. `compras/OrdenesCompra` → autorizar `PRE-2026-0001` → **Pagos/adeudos** (columna $) → PDF → correo simulado
11. `compras/Insumos` → verificar stock tras recepción (Pintura = 14)
12. Reporte de compras / montos gastados
13. Campana de alertas → pre-órdenes pendientes + stock bajo

### Bloque 4 — Simulador de alertas · 5 min
16. `usuarios/GestionUsuarios` o zona dedicada → disparar alertas de prueba
17. Mostrar toast + campana actualizándose en vivo

---

## CHAT A — Proveedores/Compras (PRIORIDAD MÁXIMA)

**Objetivo:** Que el usuario pueda cargar proveedores, enlazar productos, consultar costos, generar órdenes/pre-órdenes, ver recibidas/faltantes y simular solicitud por correo.

### A.1 — Verificación y fixes críticos (antes de UI)

| ID | Tarea | Archivos | Esfuerzo |
|----|-------|----------|----------|
| A.1.1 | Verificar migración `database/iteracion4_preordenes_unidades.sql` aplicada | BD | 15 min |
| A.1.2 | Verificar tabla `insumos_alias` existe (o crear migración) | `database/` | 30 min |
| A.1.3 | **Unificar pre-órdenes:** deshabilitar o redirigir `produccion/Dashboard/generar_preorden_compra_ajax` al flujo canónico `PreordenesModel` | `Dashboard.php`, `Productos.php` | 1 h |
| A.1.4 | Arreglar filtros de Insumos (`filtro_categoria`, `filtro_estatus`, `filtro_stock_bajo`) que la vista envía pero el modelo ignora | `InsumosModel.php` | 45 min |
| A.1.5 | Arreglar filtro `filtro_estatus` en Órdenes de Compra | `OrdenesCompraModel.php` | 30 min |

### A.2 — Funcionalidades faltantes para demo

| ID | Tarea | Descripción | Archivos sugeridos | Esfuerzo |
|----|-------|-------------|-------------------|----------|
| A.2.1 | **Simulación de correo a proveedor** | Botón "Enviar solicitud (simulación)" en OC con estatus Enviada/Confirmada. Modal que muestra: destinatario, asunto, cuerpo HTML con tabla de productos (`nombre_proveedor`, cantidad, precio). Botón "Copiar" + toast "Correo simulado — envío real pendiente de configuración SMTP". **NO enviar correo real.** | `ordenes_compra/main.php`, `OrdenesCompra.php` → `simular_correo_ajax()` | 2–3 h |
| A.2.2 | **Reporte de compras** | Nueva vista o panel en `OrdenesCompra`: totales por periodo (mes/trimestre), desglose por proveedor, OC recibidas vs pendientes, monto gastado (suma `total` de OC Recibida/Recibida Parcial). Cards + tabla + opción exportar CSV. | `OrdenesCompra.php`, `OrdenesCompraModel.php`, `views/compras/ordenes_compra/reporte.php` o tab en `main.php` | 3–4 h |
| A.2.3 | **Vista órdenes recibidas vs faltantes** | Panel con tabs: "Pendientes de recibir" (Enviada, En Tránsito, Confirmada, Recibida Parcial) vs "Recibidas" (Recibida). Badge con conteo en stats cards. | `ordenes_compra/main.php`, `OrdenesCompraModel.php` | 1–2 h |
| A.2.4 | **Proveedor principal** | Checkbox/toggle en modal de vincular insumo: "Proveedor principal". Al marcar, desmarcar otros del mismo insumo. | `proveedores/main.php`, `ProveedoresModel.php` | 1 h |
| A.2.5 | **Etiqueta de producción (alias)** | UI para gestionar `insumos_alias` (múltiples nombres que usan trabajadores). Tab o sección en detalle de insumo. | `insumos/main.php`, `Insumos.php` | 2 h |
| A.2.6 | **Comparador de costos por proveedor** | En offcanvas de insumo o en modal al vincular: tabla comparativa de precios de todos los proveedores que ofrecen ese insumo. | `Proveedores.php`, `proveedores/main.php` | 1–2 h |

### A.3 — Mejoras gráficas (UI polish)

| ID | Tarea | Detalle |
|----|-------|---------|
| A.3.1 | Stats cards unificadas | Mismo estilo que RH/Nómina: iconos Lucide, colores corporativos |
| A.3.2 | Offcanvas proveedor | Mejorar tab Insumos: badges de precio, tiempo entrega, proveedor principal |
| A.3.3 | Panel pre-órdenes | Destacar visualmente pre-órdenes Pendiente (borde danger, contador animado) |
| A.3.4 | PDF OC | Verificar logo empresa y datos fiscales desde `EmpresaModel` |
| A.3.5 | Categorías | Contador de insumos por categoría en árbol; iconos por tipo |
| A.3.6 | Empty states | Mensajes amigables cuando no hay proveedores/OC/pre-órdenes |

### A.4 — Datos de prueba para demo

Crear script o checklist de seed manual:

```
□ 3 proveedores activos con email válido
□ 10+ insumos vinculados con nombre_proveedor y precio_compra distintos
□ 1 insumo con stock bajo (dispara alerta campana)
□ 1 pre-orden Pendiente (desde simulador producción)
□ 2 OC: 1 Recibida, 1 En Tránsito
□ Usuario demo con permiso compras_autorizar_preordenes
```

### Prompt sugerido para Chat A

```
Lee PLAN_PRESENTACION_MANANA.md sección CHAT A.
Prioridad: A.2.1 (simulación correo) + A.2.2 (reporte compras) + A.1.3 (unificar pre-órdenes).
Restricciones UI: showErpToast(), Bootstrap 5 modals, NO Swal/toastr.
Verifica funcionamiento end-to-end: proveedor → insumo → pre-orden → OC → PDF → simular correo.
```

---

## CHAT B — Simulador de Alertas (Administradores)

**Objetivo:** Poder demostrar alertas de distintos módulos sin depender de datos reales en BD.

### B.1 — Diseño propuesto

Nueva sección en Administradores: **"Simulador de Alertas"** (`usuarios/GestionUsuarios/simulador_alertas` o controlador dedicado `AlertasSimulador.php`).

| Elemento | Descripción |
|----------|-------------|
| Panel por módulo | Cards: Almacén, Ventas, Obras, RH, Compras, Producción |
| Botón "Simular" | Crea registro temporal en tabla `alertas_simuladas` O inserta dato de prueba reversible |
| Botón "Limpiar simulaciones" | Elimina todas las alertas de prueba |
| Preview | Muestra cómo se verá en la campana (icono, color, mensaje, link) |
| Toast demo | Al simular, dispara `showErpToast()` inmediato además de aparecer en campana |

### B.2 — Implementación técnica (opción recomendada)

**Opción A — Tabla `alertas_simuladas` (más limpia):**
```sql
CREATE TABLE alertas_simuladas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(50),      -- stock_bajo, preorden, datos_faltantes, etc.
  modulo VARCHAR(50),
  titulo VARCHAR(200),
  mensaje TEXT,
  url VARCHAR(255),
  severidad ENUM('info','warning','danger'),
  creado_por INT,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
Modificar `Notifications.php` para mezclar alertas simuladas (solo usuarios con permiso `admin_simular_alertas`) con las reales.

**Opción B — Manipular datos reales (más rápida, menos limpia):**
Botones que temporalmente bajan `stock_actual` de un insumo o crean pre-orden de prueba, con botón "Revertir".

### B.3 — Tareas

| ID | Tarea | Esfuerzo |
|----|-------|----------|
| B.1 | Crear migración + modelo `AlertasSimuladasModel` | 1 h |
| B.2 | Endpoint CRUD en controlador | 1 h |
| B.3 | Vista con cards por tipo de alerta (8 tipos actuales en `Notifications.php`) | 2 h |
| B.4 | Integrar en `Notifications.php` | 1 h |
| B.5 | Permiso `admin_simular_alertas` en `permissions.php` | 15 min |
| B.6 | Link en sidebar Administradores | 15 min |

### Prompt sugerido para Chat B

```
Lee PLAN_PRESENTACION_MANANA.md sección CHAT B y SISTEMA_ALERTAS_NOTIFICACIONES.md.
Implementa simulador de alertas en Administradores. Opción A (tabla alertas_simuladas).
Los 8 tipos actuales de Notifications.php deben ser simulables.
Usar showErpToast() al crear simulación. Solo admins con permiso admin_simular_alertas.
```

---

## CHAT C — Recursos Humanos (Pulido UI)

**Estado:** Funcional en producción. Solo afinar para impresionar visualmente.

### C.1 — Tareas de alto impacto visual

| ID | Tarea | Archivo | Esfuerzo |
|----|-------|---------|----------|
| C.1.1 | Card "Datos faltantes" con lista clickeable → editar empleado | `main_empleados.php` | 1–2 h |
| C.1.2 | Badges más visibles en tabla (⚠️ Datos incompletos con tooltip) | `main_empleados.php` | 45 min |
| C.1.3 | Offcanvas empleado: tabs más claros (iconos Lucide consistentes) | `main_empleados.php` | 1 h |
| C.1.4 | Verificar banners dismissibles no reaparecen en misma sesión | JS en `main_empleados.php` | 30 min |
| C.1.5 | Alta empleado: validación visual campos faltantes en rojo | `alta.php`, `editar.php` | 1 h |

### C.2 — Evitar en demo (no tocar hoy)

- Contratos PDF (RH-05) — mejora grande, no bloquea demo
- Calculadora finiquito completa — ya dice "simulador de referencia"
- Sincronización reloj masiva — mostrar solo si ya hay PINs configurados

### Prompt sugerido para Chat C

```
Lee PLAN_PRESENTACION_MANANA.md sección CHAT C y PLAN_MEJORA.md RH-01/RH-04.
Solo mejoras visuales de UI en RH, sin cambiar lógica de negocio.
Prioridad: C.1.1 (card datos faltantes) y C.1.2 (badges).
```

---

## CHAT D — Reloj Checador (Pulido + Datos)

**Estado:** Dashboard profesional. Riesgo principal: pantalla vacía sin checadas.

### D.1 — Tareas

| ID | Tarea | Esfuerzo |
|----|-------|----------|
| D.1.1 | **Seed de checadas** para demo (script SQL o usar `Testzk/simulate` antes de presentar) | 30 min |
| D.1.2 | Mejorar card "Hoy": presentes / ausentes / retardos con colores | `reloj_checador/index.php` | 1 h |
| D.1.3 | Auto-refresh opcional en tabla últimas checadas (toggle) | `reloj_checador/index.php` | 45 min |
| D.1.4 | Verificar al menos 1 dispositivo activo en BD para demo | BD | 15 min |

### D.2 — Pre-demo checklist

```
□ Ejecutar Testzk/simulate o insertar 20+ checadas del día
□ Verificar empleados con PIN asignado
□ Abrir dashboard 5 min antes — confirmar gráfica poblada
```

### Prompt sugerido para Chat D

```
Lee PLAN_PRESENTACION_MANANA.md sección CHAT D.
Prioridad: datos de demo (D.1.1) y card "Hoy" (D.1.2).
NO cambiar API del reloj ni lógica de sync.
```

---

## CHAT E — Administradores + Mi Perfil (Pulido menor)

### E.1 — Tareas

| ID | Tarea | Esfuerzo |
|----|-------|----------|
| E.1.1 | Mi Perfil: mensaje más amigable si no hay empleado vinculado + CTA | `perfil/main_perfil.php` | 30 min |
| E.1.2 | Administradores: mostrar preview de permisos clave en lista de roles | `roles/main.php` | 45 min |
| E.1.3 | Bitácora: filtro por fecha y tipo de acción | `bitacora.php` | 1 h |
| E.1.4 | Crear usuario demo `presentacion@chisa.mx` vinculado a empleado | Manual/seed | 15 min |

### Prompt sugerido para Chat E

```
Lee PLAN_PRESENTACION_MANANA.md sección CHAT E.
Mejoras menores de UI en Mi Perfil y Administradores.
Preparar usuario demo vinculado a empleado para mostrar expediente completo.
```

---

## Mapa de Archivos Clave — Proveedores

```
Controllers:
  application/controllers/compras/Proveedores.php
  application/controllers/compras/OrdenesCompra.php
  application/controllers/compras/Insumos.php
  application/controllers/compras/Categorias.php
  application/controllers/produccion/Productos.php      ← pre-órden canónica
  application/controllers/produccion/Dashboard.php    ← pre-órden legacy (fixear)
  application/controllers/Notifications.php

Models:
  application/models/Compras/ProveedoresModel.php
  application/models/Compras/OrdenesCompraModel.php
  application/models/Compras/PreordenesModel.php
  application/models/Compras/InsumosModel.php

Views:
  application/views/compras/proveedores/main.php
  application/views/compras/ordenes_compra/main.php
  application/views/compras/ordenes_compra/pdf_oc.php
  application/views/compras/insumos/main.php
  application/views/compras/categorias/main.php

Support:
  application/helpers/unidades_helper.php
  application/config/permissions.php
  database/iteracion4_preordenes_unidades.sql
  database/insumos_proveedores.sql
```

---

## Orden de Ejecución Recomendado (esta noche)

```
Hora 0–2h   → Chat A: fixes críticos (A.1) + simulación correo (A.2.1)
Hora 2–4h   → Chat A: reporte compras (A.2.2) + recibidas/faltantes (A.2.3)
Hora 4–5h   → Chat B: simulador alertas (paralelo con A si hay 2 chats)
Hora 5–6h   → Chat A: UI polish (A.3) + seed datos demo
Hora 6–7h   → Chats C/D/E en paralelo (pulido RH, Reloj, Admin)
Hora 7h     → Ensayo completo con guion de arriba
```

---

## Riesgos y Mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Pre-orden no se genera | Verificar permiso `produccion_preordenes` y migración iteración 4 |
| Dashboard producción crea OC mal | ✅ Resuelto en A.1.3 — ahora usa `PreordenesModel` |
| Campana sin alertas en demo | ✅ Simulador B + stock bajo + pre-orden reales |
| Toast no aparece | Crear alerta **mientras** la página está abierta (no al cargar) |
| Mi Perfil vacío | Usuario demo con `empleado_id` vinculado |
| Reloj vacío | Ejecutar Testzk/simulate 30 min antes |
| Filtros no funcionan | ✅ A.1.4 y A.1.5 resueltos |
| Reloj vacío | Ejecutar `seed_demo_reloj_presentacion.sql` |

---

## Métricas de Éxito para Mañana

- [x] Alta de proveedor + vinculación de ≥3 insumos con precio *(6 proveedores, 139 insumos)*
- [x] Pre-orden pendiente lista (`PRE-2026-0001`) — demo: autorizar → OC Borrador
- [ ] PDF de OC con logo y datos empresa *(verificar en ensayo)*
- [x] Simulación de correo visible (`OC-2026-DEMO2` Enviada)
- [x] Reporte de compras con monto gastado del periodo
- [x] Al menos 3 tipos de alerta visibles en campana *(stock bajo + pre-orden reales; simulador para el resto)*
- [x] RH: lista empleados + offcanvas + card/banner datos faltantes
- [x] Reloj: dashboard presentes/ausentes + seed SQL listo
- [x] Mi Perfil: usuario demo `presentacion@chisa.mx` (ejecutar seed)
- [x] Administradores: roles con badges permisos clave + bitácora filtros

---

## Referencias

| Documento | Uso |
|-----------|-----|
| `mejora_hoy.md` | Estado general módulos §2 Proveedores |
| `PLAN_MEJORA.md` | Detalle RH (P0) y Producción (P1) |
| `HANDOFF_OBRAS_Y_PRODUCCION.md` | Iteración 6 en otro chat |
| `SISTEMA_ALERTAS_NOTIFICACIONES.md` | Arquitectura campana (actualizar tras Chat B) |
| `DOCUMENTACION_TECNICA.md` | Stack y estructura |

---

*Última actualización: Viernes 10 de Julio, 2026, 03:35 — Chats A–E completados. Ejecutar seeds SQL antes de la demo.*
