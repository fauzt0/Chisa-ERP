# PLAN DE ITERACIÓN — MÓDULO DE PROVEEDORES

> **Fecha:** 6 agosto 2026  
> **ERP:** Chisa Recubrimientos S.A. de C.V.  
> **URL:** https://erp.chisarecubrimientos.com.mx/compras/Proveedores

---

## Diagnóstico Inicial

| Componente | Estado |
|:-----------|:-------|
| Catálogo de proveedores CRUD | ✅ Completo (9 activos, 40 relaciones insumo-proveedor) |
| Insumos vinculados a proveedores | ✅ Completo (tabla `proveedor_insumo`, precios, tiempos entrega, principal) |
| Órdenes de compra (flujo) | ✅ Completo (Borrador→Enviada→Confirmada→En Tránsito→Recibida Parcial→Recibida) |
| Recepción de mercancía | ✅ Completo (actualiza inventario automáticamente) |
| Pre-órdenes desde producción | ✅ Completo (autorizar/rechazar → genera OC) |
| Documentos en OC (Factura, Remisión, Cotización) | ✅ Completo |
| Comentarios en OC | ✅ Completo |
| Pagos (registrar, marcar pagado, historial) | ✅ Completo |
| PDF de OC | ✅ Completo (vista imprimible) |
| Reportes de compras (CSV) | ✅ Completo |
| Carga masiva Excel de proveedores | ✅ Completo |
| Carga de comprobantes de proveedor | ✅ Existe (`subir_documento_ajax`) |
| **Comprobantes de pago (subir al pagar)** | 🆕 **NUEVO** (Iteración 5) |
| **Email real SMTP con previsualización/edición/CC** | 🆕 **NUEVO** (Iteración 5) |
| **WhatsApp — texto copiable** | 🆕 **NUEVO** (Iteración 5) |
| Cotizaciones comparativas | 🔜 Pendiente |
| Dashboard avanzado con gráficas | 🔜 Pendiente |
| Integración contable (pólizas de compras) | 🔜 Pendiente |

---

## Iteración 5 (6 agosto 2026) — Completada ✅

### Archivos modificados:

| Archivo | Cambio |
|:--------|:-------|
| `application/models/Compras/OrdenesCompraModel.php` | +`_columna_existe()`, +`generar_texto_whatsapp()`, comprobante en `registrar_pago()` y `marcar_pagado_completo()` |
| `application/controllers/compras/OrdenesCompra.php` | +`_subir_comprobante_pago()`, +`whatsapp_texto_ajax()`, +`enviar_correo_real_ajax()`, +`_generar_pdf_oc()` |
| `application/views/compras/ordenes_compra/main.php` | Modal pago: campo comprobante. Modal correo: editable + CC + WhatsApp + Enviar email |
| `database/migrar_proveedores.php` | Script de migración para columnas `comprobante_nombre`, `comprobante_ruta` |
| `database/migration_proveedores_iteracion5.sql` | SQL de migración con tabla `notificaciones_config` |

### Nuevas funcionalidades:

1. **Comprobantes de pago** — Al registrar un pago (parcial o total), ahora se puede adjuntar un archivo (PDF, imagen) como comprobante de la transferencia/depósito.

2. **Email real al proveedor** — El modal de correo ahora permite:
   - Editar el asunto y cuerpo del mensaje
   - Agregar direcciones CC (separadas por coma)
   - Adjuntar automáticamente el PDF de la OC
   - Enviar vía SMTP configurado en CodeIgniter

3. **WhatsApp** — Botón que genera texto formateado con los datos de la OC y lo copia al portapapeles para pegar en WhatsApp.

### Migración de BD requerida:

```bash
cd /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html
php database/migrar_proveedores.php
```

O ejecutar el SQL manualmente:
```sql
ALTER TABLE pagos_ordenes_compra ADD COLUMN comprobante_nombre VARCHAR(255) NULL AFTER notas;
ALTER TABLE pagos_ordenes_compra ADD COLUMN comprobante_ruta VARCHAR(500) NULL AFTER comprobante_nombre;
```

---

## Iteración 6 — Cotizaciones (Planificado)

**Objetivo:** Permitir solicitar cotizaciones a múltiples proveedores para un mismo insumo y compararlas antes de generar una OC.

### Funcionalidades planeadas:

1. **Nueva tabla `cotizaciones`:**
   - `id`, `folio`, `proveedor_id`, `fecha_solicitud`, `fecha_respuesta`, `estatus` (Pendiente/Recibida/Rechazada), `total`, `observaciones`

2. **Nueva tabla `cotizaciones_detalle`:**
   - `id`, `cotizacion_id`, `insumo_id`, `cantidad`, `precio_unitario`, `subtotal`

3. **Nuevo controlador `compras/Cotizaciones`:**
   - CRUD de cotizaciones
   - Comparación visual (tabla side-by-side de precios por proveedor)
   - Aprobar → genera OC automáticamente
   - Adjuntar PDF/cotización del proveedor

4. **Vista de comparación:**
   - Tabla: Insumo | Prov A ($) | Prov B ($) | Prov C ($) | Mejor precio
   - Resaltar automáticamente el mejor precio por insumo

---

## Iteración 7 — Dashboard Avanzado (Planificado)

1. Gráfica de gasto mensual (Chart.js)
2. Top 5 proveedores por monto
3. Órdenes vencidas (fecha entrega pasada sin recibir)
4. Alertas de pagos próximos a vencer
5. Widget de saldos pendientes por proveedor

---

## Iteración 8 — Integración Contable (Planificado)

1. Generar póliza contable al recibir mercancía
2. Generar póliza de pago al registrar pago
3. Vincular cuentas contables a proveedores/categorías
4. Exportar pólizas al módulo de Contabilidad

---

## Resumen

| Iteración | Estado | Entregables |
|:----------|:-------|:------------|
| 5 — Comprobantes, Email, WhatsApp | ✅ **Completada** | Subir comprobante al pagar, Email real SMTP editable con CC, WhatsApp copiable |
| 6 — Cotizaciones | 🔜 Planificado | Solicitar/comparar cotizaciones, aprobar → OC |
| 7 — Dashboard Avanzado | 🔜 Planificado | Gráficas, alertas, top proveedores |
| 8 — Integración Contable | 🔜 Planificado | Pólizas automáticas de compras y pagos |

---

*ERP Chisa Recubrimientos — Plan de Iteración Proveedores · 6 agosto 2026*
