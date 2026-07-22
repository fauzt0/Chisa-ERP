### Avance del Proyecto ERP Chisa Recubrimientos (al 2026-07-13)

Este documento resume el progreso en las mejoras del sistema ERP Chisa Recubrimientos, detallando las iteraciones completadas, el hito de presentación al cliente del 10 Jul 2026, y el plan para las próximas fases.

---

### Resumen Ejecutivo

| Hito | Fecha | Estado |
|------|-------|:------:|
| Alertas Toast | ~May 2026 | ✅ Completada |
| POS Redesign (recibos profesionales) | ~Jun 2026 | ✅ Completada |
| CRM Clientes y Proveedores | ~Jun 2026 | ✅ Completada |
| Pre-órdenes Automáticas (Producción→Compras) | 2026-07-06 | ✅ Completada (backend + UI) |
| Obras: PDF Profesional + Vínculo Obra↔Venta | 2026-07-08 | ✅ Completada (Gemini Flash) |
| Producción: Carga de Excels (Iteración 6 - parcial) | 2026-07-09 | ✅ Parcial (commits aplicados) |
| **🎯 Presentación al Cliente (10 Jul 2026)** | **2026-07-10** | **✅ TODOS los módulos PASS** |
| Servicios Recurrentes (mejora post-presentación) | 2026-07-13 | 🔧 Pendiente de commit |

---

### Iteraciones Completadas

**1. Alertas Toast (Modelo: Sonnet 4.6)**
- **Descripción:** Implementación de notificaciones "toast" no intrusivas para alertar al usuario sobre eventos importantes (stock bajo, órdenes retrasadas, etc.).
- **Estado:** **COMPLETADA**. Detecta nuevas notificaciones y las muestra con un diseño moderno, autodestruyéndose en 6 segundos. Incluye colores distintivos según el tipo de alerta.
- **Archivos modificados:**
    - `public_html/application/views/layouts/general_template.php`
    - `public_html/assets/dist/css/estilos.css`

**2. POS Redesign - Recibos Profesionales (Modelo: Kimi K2.7-code)**
- **Descripción:** Mejora de la funcionalidad de impresión de recibos en el módulo POS/Ventas, incluyendo la opción de seleccionar entre 3 templates de diseño profesional.
- **Estado:** **COMPLETADA**. Se añadió un método en `ventas/Pos.php` para renderizar recibos con diferentes templates. Se creó `ventas/pos/recibo_template.php` con 3 diseños (Factura Clásica, Nota de Remisión con Desglose, Formato Moderno). El diálogo de impresión utiliza SweetAlert para la selección del template.
- **Archivos modificados:**
    - `public_html/application/controllers/ventas/Pos.php`
    - `public_html/application/views/ventas/pos/recibo_template.php` (nuevo archivo)
    - `public_html/application/views/ventas/pos/main.php`
    - `public_html/application/controllers/usuarios/GestionUsuarios.php`
    - `public_html/application/views/usuarios/empresa/main.php`

**3. CRM Clientes y Proveedores Mejorado (Modelo: Kimi K2.7-code)**
- **Descripción:** Mejoras significativas en las vistas de gestión de clientes y proveedores, añadiendo funcionalidades CRM avanzadas.
- **Estado:** **COMPLETADA**.
    - **Clientes:** Offcanvas de detalle con pestañas (Información / Ventas / Cotizaciones). Historial paginado de ventas y cotizaciones. Conversión de cotizaciones en órdenes de venta. Badges de estatus de pago corregidos.
    - **Proveedores:** Offcanvas de detalle con pestañas (Información / Insumos / Órdenes). Historial paginado de órdenes de compra.
- **Archivos modificados:**
    - `public_html/application/controllers/ventas/Clientes.php`
    - `public_html/application/controllers/compras/Proveedores.php`
    - `public_html/application/models/Ventas/ClientesModel.php`
    - `public_html/application/models/Compras/OrdenesCompraModel.php`
    - `public_html/application/views/ventas/clientes/main.php`
    - `public_html/application/views/compras/proveedores/main.php`

**4. Pre-órdenes Automáticas (Producción → Compras) — COMPLETADA 100% backend + UI (2026-07-06)**
- **Descripción:** Al calcular los insumos requeridos para un proyecto/formulación, el sistema detecta insumos faltantes en stock (validando unidades de forma segura) y permite generar pre-órdenes de compra que requieren autorización de un administrador de Compras.

- **Solución al hallazgo crítico de unidades:** se creó `unidades_helper.php` con `convertir_unidad_insumo()`. Solo convierte dentro de la misma familia física segura:
  - Masa: `Kg, g, mg, Ton` (base gramos)
  - Volumen: `L, mL/ml, m³` (base mililitros)
  - Pieza: `Pza` (solo igualdad exacta)
  - `Cubeta`, `Tambo`, `Galón`, `m²`, `Servicio`, `Otro` → **siempre no convertibles**

- **Migración SQL aplicada:** `database/iteracion4_preordenes_unidades.sql`

- **Archivos nuevos:**
  - `public_html/application/helpers/unidades_helper.php`
  - `public_html/application/models/Compras/PreordenesModel.php`
  - `public_html/database/iteracion4_preordenes_unidades.sql`

- **Archivos modificados:**
  - `public_html/application/models/Produccion/ProductosModel.php`
  - `public_html/application/controllers/produccion/Productos.php`
  - `public_html/application/controllers/compras/OrdenesCompra.php`
  - `public_html/application/models/Compras/InsumosModel.php` (fix bug de JOIN)
  - `public_html/application/controllers/Notifications.php`
  - `public_html/application/config/permissions.php`
  - Múltiples vistas y JS de UI (productos, órdenes de compra)

- **Pendiente operativo (no código):**
  - Asignar permisos `compras_autorizar_preordenes` y `produccion_preordenes` a usuarios

**5. Obras: Documentación PDF Profesional + Vínculo Obra↔Venta — COMPLETADA (2026-07-08, Gemini Flash)**
- **Descripción:** Actualización drástica del módulo de Obras con PDF profesional multipágina (5 hojas), vinculación nativa Obra↔Ventas (CRM), y notificaciones con alerta sonora en Producción.
- **Componentes:**
  - **PDF 5 hojas:** Resumen ejecutivo, Avance de obra (gráfico de barras), Resumen técnico (firmas), Estimación financiera, Generador de cuantificación.
  - **Vinculación Obra↔Venta:** Badges de enlace mutuo, modal de vinculación manual, generación de cotizaciones desde obra.
  - **Notificaciones y Sonido:** Solicitudes de producción automáticas, polling 60s + Web Audio API.
- **Commit:** `af8f83a`

**6. Producción: Carga de Excels (Iteración 6 — Parcial) — Parcialmente completada (2026-07-09)**
- **Commits aplicados:**
  - `2e6cfaa feat(produccion): Iteración 6 — Corrección de formulaciones, parser multi-formato y nuevos insumos`
  - `b659e1b feat(produccion): importar Excel de entrenamiento vía CLI y deduplicación`
- **Descripción:** Se implementó el parser multi-formato para archivos Excel y se creó el comando CLI para importación con deduplicación.
- **Pendiente para completar la Iteración 6:**
  - Validar y afinar cálculos de formulaciones para los nuevos insumos cargados desde los Excels restantes (`FICHAS DE PINTURA Y PASTA.xlsx`, `PASTA SERGIO.xlsx`, `ficha masa roca.xlsx`, `T034.xls`)
  - Verificar conversiones contra stock vía `unidades_helper.php`
  - Integración completa en UI de producción

---

### 🎯 Hito: Presentación al Cliente — 10 Jul 2026

**Veredicto: ✅ LISTO PARA OPERACIÓN Y DEMO**

Login: `presentacion@chisa.mx` / `Demo2026!`

| Módulo | Ruta | Estado |
|--------|------|:------:|
| Dashboard / Inicio | `/dashboard` | ✅ |
| Mi Perfil | `/usuarios/Perfil` | ✅ |
| Administración — usuarios | `/usuarios/GestionUsuarios` | ✅ |
| Administración — roles | `/usuarios/Roles` | ✅ |
| Administración — bitácora | `/usuarios/GestionUsuarios/bitacora` | ✅ |
| Recursos Humanos | `/rh/RecursosHumanos` | ✅ |
| Reloj Checador | `/rh/RelojChecador` | ✅ |
| Proveedores ⭐ | `/compras/Proveedores` | ✅ |
| Órdenes de Compra ⭐ | `/compras/OrdenesCompra` | ✅ |
| Servicios Recurrentes ⭐ | `/compras/ServiciosRecurrentes` | ✅ |
| Insumos / stock | `/compras/Insumos` | ✅ |
| Producción Dashboard | `/produccion/Dashboard` | ✅ |

#### Funcionalidades verificadas en Compras/Proveedores:
- CRM proveedores (offcanvas, insumos, órdenes) ✅
- Pre-órdenes consultar / editar / autorizar ✅
- OC manual + PDF + correo simulado ✅
- Pagos y adeudos OC (columna, filtro, modal $) ✅
- Recepción mercancía → stock producción ✅
- Recepción desde offcanvas proveedor (camión) ✅
- Servicios recurrentes (internet, basura, soporte) ✅
- Seguimiento mensual 12 meses (matriz por servicio) ✅
- Calendario pagos del mes + marcar pagado ✅
- Tab Servicios en proveedor tipo Servicios ✅
- Bitácora Compras/Proveedores ✅
- Permisos granulares ✅

#### Datos demo para presentación:
- PRE-2026-0001 (Pendiente)
- OC-2026-DEMO1 (Recibida, stock +14)
- OC-2026-DEMO2 (Enviada, Pagada)
- Internet jul-2026 (Pagado)
- Basura jul-2026 (Pendiente/Vencido)
- Soporte jul-2026 (Pendiente)

**Commits de presentación:** `9996b2c`, `b6366d7`
- `b6366d7 feat(presentacion): carga masiva de proveedores y ajustes finales RH/demo`

---

### Mejoras Post-Presentación (en progreso, sin commit)

**Servicios Recurrentes — Comprobantes de pago (2026-07-13)**
- Se agregó funcionalidad para subir comprobantes PDF a pagos de servicios recurrentes
- Migración SQL: `database/pagos_servicios_comprobantes.sql`
- Archivos modificados (sin commit):
  - `application/controllers/compras/ServiciosRecurrentes.php` (+305/-89 líneas)
  - `application/models/Compras/ProveedoresModel.php` (+10 líneas)
  - `application/models/Compras/ServiciosRecurrentesModel.php` (+106/- líneas)
  - `application/views/compras/servicios_recurrentes/main.php` (+453/- líneas)
- Archivos nuevos (sin trackear):
  - `database/pagos_servicios_comprobantes.sql`
  - `uploads/servicios_recurrentes/7/eca85460df4d551fe9b81cb4f3aa2885.pdf`

---

### Estado de Git

- **Repositorio:** `private_html/` → `github.com/fauzt0/Chisa-ERP.git`
- **Rama activa:** `main`
- **Último commit:** `b6366d7` (10 Jul 2026)
- **Cambios pendientes:** 4 archivos modificados (Servicios Recurrentes) + 3 untracked
- **Documentos de seguimiento:** `HANDOFF_OBRAS_Y_PRODUCCION.md`, `AUDITORIA_PRESENTACION_10JUL2026.md`

---

### Pendientes Operativos

- [ ] **Hacer commit de cambios en Servicios Recurrentes** (comprobantes de pago)
- [ ] Asignar permisos `compras_autorizar_preordenes` a usuarios de Compras
- [ ] Asignar permiso `produccion_preordenes` a usuarios de Producción
- [ ] **Completar Iteración 6:** afinamiento de cálculos de formulaciones post-importación de Excels
- [ ] Mejora opcional: captura de medidas detalladas (Largo × Altura por muro) en Obras para hoja de cuantificación

---

### Próximas Iteraciones

**7. Afinamiento de Cálculos de Producción (restante de Iteración 6)**
- Validar conversiones contra stock usando `unidades_helper.php` para todas las nuevas fórmulas cargadas
- Asegurar cálculo exacto de masa base, porcentaje de fase acuosa y kg resultantes
- **Modelo recomendado:** **Sonnet 4.6/5** (razonamiento lógico y manejo de Excel binario)

**8. Mejora POS (post-presentación)**
- El usuario menciona querer mejorar el POS después de completar los cambios de presentación

---

### Recordatorio de Modelos por Tarea

- **Sonnet 4.6/5** → Lógica de negocio/datos delicados (unidades, cálculos, fórmulas, Excel binario)
- **Kimi K2.7-code** → Refactors grandes de CRUD/CRM
- **Gemini Flash** → Templates/HTML/PDF y UI pura