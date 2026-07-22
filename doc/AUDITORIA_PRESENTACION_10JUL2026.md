# Auditoría Presentación — 10 Jul 2026

> **Veredicto:** ✅ **LISTO PARA OPERACIÓN Y DEMO**

Login: `presentacion@chisa.mx` / `Demo2026!`

---

## Scorecard por módulo (HTTP 200 + AJAX)

| Módulo | Ruta | Estado |
|--------|------|:------:|
| **Dashboard / Inicio** | `/dashboard` | ✅ |
| **Mi Perfil** | `/usuarios/Perfil` | ✅ |
| **Administración — usuarios** | `/usuarios/GestionUsuarios` | ✅ |
| **Administración — roles** | `/usuarios/Roles` | ✅ |
| **Administración — bitácora** | `/usuarios/GestionUsuarios/bitacora` | ✅ |
| **Recursos Humanos** | `/rh/RecursosHumanos` | ✅ |
| **Reloj Checador** | `/rh/RelojChecador` | ✅ |
| **Proveedores** ⭐ | `/compras/Proveedores` | ✅ |
| **Órdenes de Compra** ⭐ | `/compras/OrdenesCompra` | ✅ |
| **Servicios Recurrentes** ⭐ | `/compras/ServiciosRecurrentes` | ✅ |
| **Insumos / stock** | `/compras/Insumos` | ✅ |
| **Producción Dashboard** | `/produccion/Dashboard` | ✅ |

---

## Proveedores / Compras — funcionalidades verificadas

| Funcionalidad | Estado |
|---------------|:------:|
| CRM proveedores (offcanvas, insumos, órdenes) | ✅ |
| Pre-órdenes consultar / editar / autorizar | ✅ |
| OC manual + PDF + correo simulado | ✅ |
| **Pagos y adeudos OC** (columna, filtro, modal $) | ✅ |
| **Recepción mercancía** → stock producción | ✅ |
| Recepción desde offcanvas proveedor (camión) | ✅ |
| **Servicios recurrentes** (internet, basura, soporte) | ✅ |
| **Seguimiento mensual 12 meses** (matriz por servicio) | ✅ |
| Calendario pagos del mes + marcar pagado | ✅ |
| Tab Servicios en proveedor tipo Servicios | ✅ |
| Bitácora Compras/Proveedores | ✅ |
| Permisos granulares | ✅ |

### Servicios demo (seguimiento mensual)

| Servicio | Tipo | Proveedor |
|----------|------|-----------|
| Internet empresarial 100 Mbps | Telecomunicaciones | Telmex |
| Recolección de basura industrial | Recolección de Basura | Ambientales Centro |
| Soporte técnico mensual ERP | Soporte Técnico | Soporte TI Partner |

**Contabilidad:** tabla `pagos_servicios_recurrentes.poliza_id` lista para vincular pólizas (integración posterior desde módulo Contabilidad).

---

## Guion demo Proveedores + Servicios (10 min)

1. **Proveedores** → Telmex → tab **Servicios** → ver pago julio
2. **Servicios Recurrentes** → tab **Seguimiento mensual** → matriz 12 meses (internet ✓, basura pendiente, soporte pendiente)
3. Marcar pago **basura** o **soporte** (clic en celda amarilla/roja)
4. **Órdenes de Compra** → filtro adeudo → registrar pago
5. **Insumos** → ver stock tras recepción OC

---

## Datos demo actuales

| Elemento | Estado |
|----------|--------|
| PRE-2026-0001 | Pendiente |
| OC-2026-DEMO1 | Recibida (stock +14) |
| OC-2026-DEMO2 | Enviada, Pagada |
| Internet jul-2026 | Pagado |
| Basura jul-2026 | Pendiente/Vencido |
| Soporte jul-2026 | Pendiente |

**Reset:**
```bash
mysql st32477_chisa < public_html/database/seed_demo_presentacion_compras.sql
mysql st32477_chisa < public_html/database/seed_servicios_recurrentes_demo.sql
```

---

## Respuesta: ¿existía seguimiento mensual?

**Sí, parcialmente** — las tablas `servicios_recurrentes` + `pagos_servicios_recurrentes` ya existían (módulo Contabilidad) pero **no estaban integradas en Compras**.

**Añadido en esta iteración:**
- Pantalla `/compras/ServiciosRecurrentes` con control desde Proveedores
- Tab **Seguimiento mensual** (matriz 12 meses × servicio)
- Tipos: Telecomunicaciones, **Recolección de Basura**, Soporte Técnico, etc.
- Generación automática de periodos mensuales al crear servicio
- Botón «Generar periodos» para extender calendario
- Demo: internet, basura, soporte técnico
- Puente documentado hacia Contabilidad (`poliza_id`)

---

*Auditoría: 10 Jul 2026 ~06:15 — todos los módulos PASS.*
