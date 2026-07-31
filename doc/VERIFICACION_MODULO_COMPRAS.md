# VERIFICACIÓN COMPLETA DEL MÓDULO DE COMPRAS/PROVEEDORES

**Fecha:** 31 de Julio 2026  
**Ruta:** `/compras/` (Proveedores, Órdenes de Compra, Pre-órdenes, Servicios Recurrentes)  
**Framework:** CodeIgniter 3 + PHP 7.4  
**Objetivo:** Verificar que el módulo cumpla con funciones profesionales de catálogo de proveedores, órdenes de compra, exportación Excel, recibos, carga de facturas (PDF/XML) y pre-órdenes con autorización desde producción y obras.

---

## ✅ VERIFICACIÓN EN VIVO (31 Julio 2026, 3:15 AM)

Se realizó verificación navegando el ERP como admin. Resultados:

| Página | URL | Estado | Datos |
|--------|-----|--------|-------|
| Login | `/admin` | ✅ OK | Login exitoso con 2FA |
| Dashboard | `/dashboard` | ✅ OK | Sidebar con módulo Compras visible |
| Proveedores | `/compras/Proveedores` | ✅ OK | 9 activos, 40 relaciones, 4 OC, filtros funcionales |
| Órdenes de Compra | `/compras/OrdenesCompra` | ✅ OK | 4 OC, 1 pendiente, Pre-órdenes visibles, tabs |
| PDF OC | `/compras/OrdenesCompra/generar_pdf/4` | ✅ OK | Logo, RFC, datos completos, imprimible |
| Insumos | `/compras/Insumos` | ✅ OK | 139 insumos, $528K inventario, 96 stock bajo |
| Servicios Recurrentes | `/compras/ServiciosRecurrentes` | ✅ OK | 3 pagos del mes, tabs funcionales |
| Categorías | `/compras/Categorias` | ✅ OK | Árbol jerárquico de categorías |

**Correcciones aplicadas y verificadas:**
- ✅ XML agregado a tipos de archivo permitidos
- ✅ SQL de `preordenes.origen_tipo` ejecutado → ahora incluye `'produccion'`
- ✅ BD contiene 1 pre-orden con `origen_tipo = 'produccion'` → confirmado funcional

---

## RESUMEN EJECUTIVO

El módulo de Compras es funcional y está bien estructurado. Se identificaron y **corrigieron** 2 brechas críticas, y quedan 2 mejoras recomendadas post-demo:

| # | Hallazgo | Severidad | Estado |
|---|----------|-----------|--------|
| 1 | No se permite subir archivos **XML** (solo PDF, imágenes, Office) | **ALTA** | ✅ CORREGIDO |
| 2 | No hay generación de **Recibos** como documento formal independiente | **MEDIA** | 🟡 Post-demo |
| 3 | Exportación de reporte es **CSV**, no Excel (.xlsx) | **MEDIA** | 🟡 Post-demo |
| 4 | El `origen_tipo` en BD no incluye `'produccion'` aunque el código lo usa | **ALTA** | ✅ CORREGIDO |

El resto de funcionalidades están completas y listas para demo.

---

## 1. CATÁLOGO DE PROVEEDORES

### 1.1 Estructura de datos

**Tabla `proveedores`** — Campos profesionales:

| Grupo | Campos |
|-------|--------|
| Identificación | `codigo` (PROV-XXX auto-generado), `razon_social`, `nombre_comercial`, `rfc` |
| Tipo | `tipo_proveedor` (Materia Prima, Materiales, Servicios, Mixto) |
| Contacto | `contacto_principal`, `telefono`, `telefono_alternativo`, `email`, `sitio_web` |
| Dirección | `direccion`, `ciudad`, `estado`, `codigo_postal`, `pais` |
| Financiero | `dias_credito`, `limite_credito`, `banco`, `cuenta_bancaria` |
| Control | `estatus` (Activo/Inactivo/Suspendido), `calificacion` (1-5), `observaciones` |
| Auditoría | `registrado_por`, `fecha_creacion` |

**Veredicto:** ✅ Completo y profesional.

### 1.2 Funcionalidades implementadas

| Funcionalidad | Estado | Detalle |
|--------------|--------|---------|
| CRUD completo | ✅ | Crear, leer, editar, eliminar con validaciones |
| Validación RFC único | ✅ | No permite duplicados, formato 12-13 caracteres |
| Código auto-generado | ✅ | PROV-001, PROV-002... |
| DataTables server-side | ✅ | Con búsqueda, filtros por estatus/tipo/órdenes |
| Vista detalle (Offcanvas) | ✅ | Con tabs: Info, Insumos, Órdenes, Servicios |
| Estadísticas dashboard | ✅ | Cards: activos, inactivos, relaciones, OC |
| Vinculación insumos | ✅ | Precio, tiempo entrega, cant. mínima, SKU proveedor |
| Importación masiva Excel | ✅ | Plantilla .xlsx descargable, validación, RFC único |
| Bitácora de cambios | ✅ | Registro de creación, edición, eliminación |
| Permisos granulares | ✅ | add, edit, consult, delete, insumos |

### 1.3 Hallazgos menores

- **⚠️ Teléfono y Email marcados como requeridos en la vista** (`<span class="text-danger">*</span>`) pero el controller solo valida `razon_social` y `rfc`. No hay validación real de formato de email.
- **⚠️ No se puede exportar el catálogo de proveedores a Excel** — solo se puede importar.

**Veredicto del catálogo:** ✅ **APROBADO** — Cumple funciones profesionales de CRM de proveedores.

---

## 2. ÓRDENES DE COMPRA

### 2.1 Workflow de estatus

```
Borrador → Enviada → Confirmada → En Tránsito → Recibida Parcial → Recibida
                                                                        ↓
                                                                    Cancelada
```

**Veredicto:** ✅ Flujo completo con transiciones controladas.

### 2.2 Funcionalidades implementadas

| Funcionalidad | Estado | Detalle |
|--------------|--------|---------|
| CRUD de OC | ✅ | Con folio auto-generado: OC-YYYY-XXXX |
| Detalle de partidas | ✅ | Insumos con precio, cantidad, subtotal |
| Cálculo IVA 16% | ✅ | Subtotal + IVA = Total automático |
| Workflow de estatus | ✅ | 7 estatus con validaciones de transición |
| Edición solo en Borrador | ✅ | Seguridad por estatus |
| Recepción de mercancía | ✅ | Actualiza inventario (movimientos_inventario) |
| Recepción parcial | ✅ | Permite recibir por partes |
| PDF imprimible | ✅ | Vista profesional con logo, datos, firmas |
| Simulación de correo | ✅ | Vista previa HTML (NO envía realmente) |
| Reporte de compras | ✅ | Por mes/trimestre/personalizado |
| Exportación CSV reporte | ⚠️ | CSV con BOM UTF-8 (NO Excel .xlsx) |
| Filtros DataTables | ✅ | Por estatus entrega y estatus pago |
| Historial por proveedor | ✅ | En offcanvas de proveedor, paginado |

### 2.3 Gestión de pagos

| Funcionalidad | Estado | Detalle |
|--------------|--------|---------|
| Registro de pagos | ✅ | Folio PAGC-YYYY-XXXX, monto, método, referencia |
| Pago parcial/total | ✅ | Valida no exceder saldo pendiente |
| Marcar como pagado | ✅ | Pago total automático |
| Estados de pago | ✅ | Pendiente, Parcial, Pagado, Sin adeudo |
| Saldo pendiente | ✅ | Cálculo automático |

### 2.4 Documentos adjuntos (Facturas)

| Funcionalidad | Estado | Detalle |
|--------------|--------|---------|
| Subida de archivos | ✅ | Por OC, con tipo y notas |
| Tipos de documento | ✅ | Factura, Nota de remisión, Cotización, Otro |
| Formatos permitidos | ⚠️ | pdf, jpg, jpeg, png, webp, doc, docx, xls, xlsx |
| **XML (CFDI)** | ❌ | **NO SOPORTADO** — Crítico para facturas electrónicas |
| Eliminación de adjuntos | ✅ | Elimina archivo físico y registro |
| Comentarios por OC | ✅ | Trazabilidad con autor y timestamp |

**Veredicto OC:** ✅ **APROBADO con observación** — Falta soporte para XML en adjuntos.

---

## 3. EXPORTACIÓN DE ARCHIVOS EXCEL

### 3.1 Funcionalidades actuales

| Funcionalidad | Formato | Estado |
|--------------|---------|--------|
| Plantilla proveedores (descarga) | .xlsx | ✅ PhpSpreadsheet |
| Importación masiva proveedores | .xlsx/.xls | ✅ PhpSpreadsheet |
| Exportación reporte compras | .csv | ⚠️ Solo CSV, no Excel |
| Exportación catálogo proveedores | — | ❌ No existe |

### 3.2 Brechas

- **⚠️ Reporte de compras se exporta como CSV** — para una presentación profesional, debe ser .xlsx con formato (negritas, anchos de columna, números como moneda).
- **⚠️ No se puede exportar el catálogo de proveedores** — el cliente podría necesitar esta funcionalidad.

**Veredicto exportación:** ⚠️ **FUNCIONAL pero limitado** — El CSV es suficiente para demo, pero se recomienda migrar a Excel.

---

## 4. RECIBOS

### 4.1 ¿Qué existe actualmente?

El módulo NO tiene una entidad "Recibo" independiente. Sin embargo, cubre el flujo de negocio:

| Documento | ¿Existe? | Sustituto actual |
|-----------|----------|------------------|
| Recibo de mercancía | ❌ | Recepción de mercancía (`recibir_mercancia_ajax`) actualiza inventario pero no genera documento |
| Recibo de pago | ❌ | Registro de pago con folio `PAGC-YYYY-XXXX` pero sin PDF de recibo |
| Orden de Compra PDF | ✅ | PDF profesional imprimible con logo y firmas |

### 4.2 Brechas

- **⚠️ No hay "Recibo de recepción"** como documento imprimible — al recibir mercancía no se genera un PDF/comprobante.
- **⚠️ No hay "Recibo de pago"** como documento independiente — los pagos tienen folio pero no generan un comprobante PDF.

**Veredicto recibos:** ⚠️ **CUBIERTO PARCIALMENTE** — Para demo, el PDF de OC + el registro de pagos pueden funcionar como "recibos". Se recomienda agregar PDF de recibo en iteración futura.

---

## 5. CARGA DE FACTURAS (PDF Y XML)

### 5.1 Análisis

| Requisito | Estado |
|-----------|--------|
| Subir PDF de factura | ✅ Soportado |
| Subir XML de factura (CFDI) | ❌ **NO SOPORTADO** |
| Asociar factura a OC | ✅ Mediante tipo "Factura" |
| Almacenamiento | ✅ `uploads/ordenes_compra/{id}/` |
| Límite de tamaño | ✅ 10 MB |

### 5.2 El problema del XML

En México, toda factura electrónica (CFDI) se compone de dos archivos:
1. **PDF** — Representación visual
2. **XML** — Comprobante fiscal digital con validez ante el SAT

El módulo actual **solo permite subir PDF pero NO XML**. Esto es una brecha crítica para un sistema profesional.

**Código donde está la restricción:**

```848:850:application/controllers/compras/OrdenesCompra.php
        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'pdf|jpg|jpeg|png|webp|doc|docx|xls|xlsx',
```

**Veredicto facturas:** ❌ **REQUIERE CORRECCIÓN** — Agregar `xml` a los tipos permitidos es trivial y necesario.

---

## 6. PRE-ÓRDENES CON ESTATUS DE AUTORIZACIÓN

### 6.1 Flujo implementado

```
Producción/Obra detecta faltantes
        ↓
Genera pre-orden (Pendiente)
        ↓
Admin de Compras revisa
        ↓
   ┌─────────┬─────────┐
   ↓         ↓         ↓
Autoriza   Rechaza   Edita
   ↓         ↓         ↓
Convierte   Guarda   Modifica cantidad/
a OC real   motivo   proveedor sugerido
(Borrador)
   ↓
Compras revisa OC
   ↓
Envía al proveedor
```

### 6.2 Funcionalidades implementadas

| Funcionalidad | Estado | Detalle |
|--------------|--------|---------|
| Creación automática desde faltantes | ✅ | `PreordenesModel::crear_preordenes_desde_faltantes()` |
| Panel de pendientes | ✅ | Tabla en vista principal con badge de conteo |
| Historial de procesadas | ✅ | Con folio OC generada y estatus |
| Autorizar → OC en Borrador | ✅ | `aprobar()` con transacción |
| Rechazar con motivo | ✅ | `rechazar()` obliga motivo |
| Editar antes de autorizar | ✅ | Cantidad, proveedor, notas |
| Proveedor sugerido automático | ✅ | Menor precio del insumo vinculado |
| Prevención de duplicados | ✅ | Mismo insumo + mismo origen pendiente |
| Permisos granulares | ✅ | `compras_autorizar_preordenes` |

### 6.3 Brecha crítica: `origen_tipo` ENUM

**El ENUM en base de datos:**

```86:87:database/mejoras_fase2_formulaciones_preordenes.sql
  `origen_tipo`          ENUM('venta','obra','interno') NOT NULL DEFAULT 'interno'
    COMMENT 'Qué generó la necesidad',
```

**Pero el código usa `'produccion'`:**

```282:283:application/models/Compras/PreordenesModel.php
        $orden_data = [
            // ...
            'origen'         => 'Produccion',
            'origen_tipo'    => $preorden->origen_tipo,
```

Si el módulo de Producción intenta crear pre-órdenes con `origen_tipo = 'produccion'`, el INSERT a MySQL **fallará** porque el valor no está en el ENUM.

**Acción requerida:** Ejecutar:
```sql
ALTER TABLE `preordenes` 
  MODIFY COLUMN `origen_tipo` ENUM('venta','obra','interno','produccion') 
  NOT NULL DEFAULT 'interno';
```

**Veredicto pre-órdenes:** ⚠️ **FUNCIONAL pero con bug** — El ENUM de BD debe actualizarse antes de la demo.

---

## 7. PLAN DE IMPLEMENTACIÓN (ITERACIONES)

### Iteración 0 — Correcciones CRÍTICAS para la demo de mañana (1-2 horas)

| # | Tarea | Prioridad | Esfuerzo |
|---|-------|-----------|----------|
| 0.1 | Agregar `xml` a `allowed_types` en `subir_documento_ajax()` | **ALTA** | 5 min |
| 0.2 | Corregir ENUM de `preordenes.origen_tipo` agregando `'produccion'` | **ALTA** | 5 min |
| 0.3 | Agregar validación de formato email en controller Proveedores | BAJA | 10 min |
| 0.4 | Sincronizar campos requeridos entre vista y controller (teléfono, email) | BAJA | 10 min |

### Iteración 1 — Mejoras post-demo (recomendadas, 1-2 días)

| # | Tarea | Prioridad |
|---|-------|-----------|
| 1.1 | Migrar exportación de reporte de CSV a Excel (.xlsx) con PhpSpreadsheet | MEDIA |
| 1.2 | Agregar exportación Excel del catálogo de proveedores | BAJA |
| 1.3 | Generar PDF de "Recibo de Recepción" al recibir mercancía | MEDIA |
| 1.4 | Generar PDF de "Recibo de Pago" con folio | MEDIA |
| 1.5 | Implementar envío real de correo (SMTP) en lugar de solo simulación | BAJA |
| 1.6 | Agregar upload simultáneo de PDF + XML para facturas (campo pareado) | MEDIA |

---

## 8. LISTADO DE TESTS MANUALES PARA LA DEMO

### 8.1 Catálogo de Proveedores

- [ ] **T-PROV-01**: Crear un proveedor nuevo con todos los campos (RFC único, código auto-generado)
- [ ] **T-PROV-02**: Intentar crear proveedor con RFC duplicado → Debe mostrar error
- [ ] **T-PROV-03**: Editar proveedor (todos los campos, incluyendo financieros)
- [ ] **T-PROV-04**: Cambiar estatus: Activo → Inactivo → Suspendido
- [ ] **T-PROV-05**: Eliminar proveedor sin órdenes ni insumos → Éxito
- [ ] **T-PROV-06**: Intentar eliminar proveedor con órdenes de compra → Error
- [ ] **T-PROV-07**: Intentar eliminar proveedor con insumos vinculados → Error
- [ ] **T-PROV-08**: Vincular un insumo al proveedor (precio, tiempo entrega, SKU)
- [ ] **T-PROV-09**: Marcar proveedor como principal para un insumo
- [ ] **T-PROV-10**: Ver detalle del proveedor en offcanvas (tabs: Info, Insumos, Órdenes)
- [ ] **T-PROV-11**: Filtrar por estatus, tipo y "con/sin órdenes"
- [ ] **T-PROV-12**: Búsqueda rápida por nombre, RFC, teléfono, email
- [ ] **T-PROV-13**: Descargar plantilla Excel de proveedores
- [ ] **T-PROV-14**: Importar proveedores desde Excel (válidos + RFC duplicados + errores)
- [ ] **T-PROV-15**: Verificar bitácora registra creación/edición/eliminación

### 8.2 Órdenes de Compra

- [ ] **T-OC-01**: Crear nueva OC con proveedor y fecha de entrega estimada
- [ ] **T-OC-02**: Agregar insumos a la OC (debe cargar precios del proveedor)
- [ ] **T-OC-03**: Verificar cálculo automático: subtotal, IVA 16%, total
- [ ] **T-OC-04**: Editar partidas (cantidad, precio) en OC Borrador
- [ ] **T-OC-05**: Eliminar partida y verificar recálculo de totales
- [ ] **T-OC-06**: Cambiar estatus: Borrador → Enviada (registra aprobador)
- [ ] **T-OC-07**: Continuar workflow: Enviada → Confirmada → En Tránsito
- [ ] **T-OC-08**: Intentar editar OC que NO está en Borrador → No debe permitir
- [ ] **T-OC-09**: Intentar eliminar OC que NO está en Borrador → Error
- [ ] **T-OC-10**: Ver PDF de OC (logo, datos empresa, proveedor, partidas, totales, firmas)
- [ ] **T-OC-11**: Simular correo al proveedor (vista previa HTML)
- [ ] **T-OC-12**: Recibir mercancía completa → Estatus cambia a "Recibida"
- [ ] **T-OC-13**: Recibir mercancía parcial → Estatus "Recibida Parcial"
- [ ] **T-OC-14**: Verificar que el stock de insumos aumenta al recibir
- [ ] **T-OC-15**: Registrar un pago parcial → Estatus pago "Parcial", saldo pendiente
- [ ] **T-OC-16**: Intentar pagar más del saldo pendiente → Error
- [ ] **T-OC-17**: Marcar como pagado → Estatus pago "Pagado", saldo $0
- [ ] **T-OC-18**: Ver historial de órdenes desde la vista de proveedor
- [ ] **T-OC-19**: Filtrar OC por estatus de entrega y estatus de pago

### 8.3 Documentos y Facturas

- [ ] **T-DOC-01**: Subir un PDF de factura a una OC
- [ ] **T-DOC-02**: Subir un archivo JPG/PNG como documento
- [ ] **T-DOC-03**: ⚠️ **Subir un archivo XML** (debe funcionar después de la corrección 0.1)
- [ ] **T-DOC-04**: Ver lista de documentos adjuntos a la OC
- [ ] **T-DOC-05**: Eliminar un documento adjunto
- [ ] **T-DOC-06**: Agregar comentario a una OC
- [ ] **T-DOC-07**: Ver historial de comentarios con autor y timestamp

### 8.4 Reportes y Exportación

- [ ] **T-REP-01**: Ver reporte de compras del mes actual
- [ ] **T-REP-02**: Cambiar a trimestre y ver datos actualizados
- [ ] **T-REP-03**: Usar rango personalizado de fechas
- [ ] **T-REP-04**: Exportar reporte a CSV y abrir en Excel (verificar BOM UTF-8)
- [ ] **T-REP-05**: Verificar que el CSV contiene: resumen, desglose por proveedor, detalle de OC

### 8.5 Pre-órdenes

- [ ] **T-PRE-01**: Ver panel de pre-órdenes pendientes (debe aparecer si hay datos)
- [ ] **T-PRE-02**: Ver badge de conteo de pendientes
- [ ] **T-PRE-03**: Ver detalle de una pre-orden (folio, insumo, cantidad, proveedor sugerido)
- [ ] **T-PRE-04**: Editar una pre-orden pendiente (cambiar cantidad y/o proveedor)
- [ ] **T-PRE-05**: Autorizar una pre-orden → Verificar que se crea OC en Borrador
- [ ] **T-PRE-06**: Verificar que la OC generada tiene el detalle correcto
- [ ] **T-PRE-07**: Rechazar una pre-orden con motivo → Estatus cambia a "Rechazada"
- [ ] **T-PRE-08**: Ver historial de pre-órdenes procesadas (Convertidas/Rechazadas)
- [ ] **T-PRE-09**: ⚠️ Verificar que `origen_tipo = 'produccion'` funciona (después de corrección 0.2)
- [ ] **T-PRE-10**: Verificar que no se duplica pre-orden para mismo insumo + mismo origen

---

## 9. DIAGRAMA DE ARQUITECTURA DEL MÓDULO

```
┌─────────────────────────────────────────────────────────────────┐
│                    MÓDULO COMPRAS (/compras/)                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────────────┐ │
│  │ Proveedores  │  │  Insumos     │  │ Órdenes de Compra     │ │
│  │ CRUD + CRM   │  │  Catálogo    │  │ Workflow 7 estatus    │ │
│  │ + Excel I/O  │  │  + Aliases   │  │ + Pagos + Docs + PDF │ │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬────────────┘ │
│         │                 │                      │               │
│         └─────────┬───────┘                      │               │
│                   │                              │               │
│         ┌─────────▼─────────┐     ┌──────────────▼────────────┐ │
│         │ proveedor_insumo  │     │      Pre-ordenes          │ │
│         │ (precios x prov)  │     │ Prod/Obra → Compras → OC  │ │
│         └───────────────────┘     └───────────────────────────┘ │
│                                                                  │
│  ┌──────────────────────┐    ┌──────────────────────────────┐   │
│  │ ServiciosRecurrentes │    │ Categorías (árbol jerárquico)│   │
│  │ Pagos mensuales      │    │ Materia Prima → Resinas, etc │   │
│  └──────────────────────┘    └──────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Tablas de soporte: movimientos_inventario,                │   │
│  │ ordenes_compra_documentos, ordenes_compra_comentarios,    │   │
│  │ pagos_ordenes_compra                                       │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 10. CONCLUSIÓN FINAL

El módulo de Compras/Proveedores está **bien construido y es funcional** para la demo de mañana, con las siguientes condiciones:

### Acciones INMEDIATAS (antes de la demo):
1. **Ejecutar SQL** para corregir el ENUM de `preordenes.origen_tipo`
2. **Agregar `xml`** a los `allowed_types` de subida de documentos
3. Verificar que existan **datos de prueba** para todos los flujos a demostrar

### Para la demo, enfocarse en estos flujos:
1. **Crear proveedor → Vincular insumo → Crear OC → Recibir → Pagar → Exportar reporte**
2. **Mostrar pre-orden pendiente → Autorizar → Revisar OC generada**
3. **Subir factura PDF + XML → Adjuntar a OC**

### El módulo es presentable ✅
Con las 2 correcciones críticas aplicadas, el módulo cumple con los requisitos del cliente. Las mejoras de la Iteración 1 (PDF de recibos, Excel en lugar de CSV) pueden planearse para la siguiente entrega.
