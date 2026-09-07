# AUDITORÍA MÓDULO DE OBRAS — Iteración 3
**Fecha:** 2026-08-28  
**Rama:** `iteracion-3`  
**Auditor:** Agente IA (Claude Sonnet 4.6)

---

## 1. Mapa de archivos del módulo

| Archivo | Descripción |
|---------|-------------|
| `application/controllers/obras/Obras.php` | Controlador principal |
| `application/controllers/ventas/ObrasVentas.php` | Vista de obras desde CRM Ventas |
| `application/models/Obras/ObrasModel.php` | Modelo principal (~1060 líneas) |
| `application/views/obras/index.php` | Lista de obras (DataTables) |
| `application/views/obras/detalle.php` | Detalle obra (~1620 líneas) |
| `application/views/obras/partials/vinculo_venta.php` | Partial: vinculación OV + PDF |
| `application/views/obras/pdf_resumen.php` | PDF 5 hojas |
| `application/views/ventas/obras/main.php` | Vista CRM Ventas |
| `application/views/ventas/obras/detalle.php` | Detalle CRM Ventas |
| `application/controllers/almacen/Entregas.php` | Controlador entregas almacén |
| `application/models/Almacen/AlmacenModel.php` | Modelo almacén |
| `application/views/almacen/entregas/main.php` | Vista principal entregas |
| `database/almacen.sql` | Schema: entregas_almacen, detalle_entregas_almacen, trigger |

### Tablas BD involucradas

| Tabla | Estado |
|-------|--------|
| `obras` | ✅ Existe |
| `obras_productos` | ✅ Existe (incluye `cantidad_entregada`) |
| `obras_pagos` | ✅ Existe |
| `obras_archivos` | ✅ Existe |
| `obras_comentarios` | ✅ Existe |
| `entregas_almacen` | ✅ Existe (sin registros) |
| `detalle_entregas_almacen` | ✅ Existe |
| `sp_generar_folio_entrega` | ✅ Stored procedure existe |
| `tr_actualizar_entrega_almacen` | ✅ Trigger existe (con bug) |

---

## 2. Flujo de negocio vs implementación actual

| # | Paso | Estado | Archivos/Métodos | Notas |
|---|------|--------|------------------|-------|
| 1 | Nueva obra (alta y edición) | ✅ | `Obras::guardar_ajax`, `actualizar_ajax`, `detalle.php` | Funciona completo |
| 2 | Cálculo materiales m²→kg→insumos | ✅ | `ObrasModel::calcular_materiales_linea_obra`, `::calcular_materiales_obra` | Motor P8 implementado |
| 3 | Orden de obra — módulo Obras | ✅ | `Obras::generar_orden_venta_ajax`, `partials/vinculo_venta.php` | Genera y vincula OV |
| 3b | Orden de obra — CRM Ventas | ✅ | `ObrasVentas.php`, `ventas/obras/detalle.php` | Vista espejo |
| 4a | Insumos → Pre-órdenes compra | ✅ | `ObrasModel::verificar_insumos_y_preordenes_obra` | Se dispara al aprobar |
| 4b | Solicitudes de producción | ✅ | `ObrasModel::crear_solicitudes_produccion_desde_obra` | Se dispara al aprobar |
| 5 | PDF Resumen (5 hojas) | ⚠️ | `obras/pdf_resumen.php`, `Obras::exportar_pdf` | Estructura OK; ajustes menores |
| 6 | Seguimiento entregas en detalle obra | ❌ | Tab "Entregas" **no existe** en `detalle.php` | Brecha de UI |

---

## 3. Brechas encontradas

### B1 — Bug CRÍTICO: Trigger usa valores fuera del ENUM de `obras.estatus`
**Archivo:** BD `tr_actualizar_entrega_almacen`  
**Problema:** El trigger escribe `'Finalizada'` y `'En Proceso'` en `obras.estatus`, pero el ENUM válido es:
`('Planificación','En Cotización','Aprobada','En Ejecución','Pausada','Completada','Cancelada')`.  
Con `STRICT_TRANS_TABLES` activo el UPDATE falla silenciosamente (el campo queda sin cambio).  
**Impacto:** El estatus de la obra nunca se actualiza automáticamente al registrar entregas.  
**Corrección:** Recrear el trigger usando `'Completada'` y `'En Ejecución'`.

### B2 — Tab "Entregas/Seguimiento" inexistente en detalle de obra
**Archivo:** `application/views/obras/detalle.php`  
**Problema:** No hay tab que muestre el avance de entrega por producto (`cantidad_entregada` existe en BD pero no se expone).  
**Impacto:** El equipo no puede ver desde el módulo de Obras qué productos se han entregado y cuáles faltan.  
**Corrección:** Agregar tab "Entregas" + endpoint AJAX `get_entregas_obra_ajax` en `Obras.php` + método en `ObrasModel`.

### B3 — Rutas `almacen/Entregas` no registradas en `routes.php`
**Archivo:** `application/config/routes.php`  
**Problema:** El controlador `almacen/Entregas` existe pero no tiene rutas explícitas; CI3 puede resolverlo automáticamente pero no está documentado.  
**Corrección:** Agregar rutas para `almacen/Entregas`.

### B4 — `alert()` en `detalle.php` (regresión UX)
**Archivo:** `application/views/obras/detalle.php` (múltiples funciones JS)  
**Problema:** Las respuestas AJAX usan `alert()` nativo del navegador en lugar de `showErpToast()`.  
**Corrección:** Migrar todas las llamadas `alert()` a `showErpToast()`.

### B5 — SQL directo en controlador `Obras::actualizar_ajax` (líneas 172–175)
**Archivo:** `application/controllers/obras/Obras.php`  
**Problema:** `$this->db->select('estatus')... $this->db->get('obras')` en el controlador; viola la separación MC.  
**Corrección:** Mover a método del modelo (menor, no bloquea funcionalidad).

### B6 — PDF: label "CONSTRUCTORA" inconsistente y campo SEMANA faltante
**Archivo:** `application/views/obras/pdf_resumen.php`  
**Problema:** En página 4 falta campo `SEMANA:`, la referencia muestra datos de periodo semanal.  
La página 2 muestra "CONTRATISTA:" en la cabecera pero la referencia dice "CONSTRUCTORA:" para la empresa cliente.  
**Corrección:** Ajuste menor de labels en el PDF.

---

## 4. Riesgos

| # | Riesgo | Severidad | Mitigación |
|---|--------|-----------|------------|
| R1 | Trigger falla silenciosamente → estatus obra no actualiza | ALTO | Fix inmediato: recrear trigger |
| R2 | Sin visibilidad de entregas en obra → usuario no sabe qué falta entregar | MEDIO | Agregar tab Entregas |
| R3 | Rutas sin registrar pueden fallar en config restrictiva | BAJO | Agregar a routes.php |

---

## 5. Plan de corrección priorizado

| Prioridad | ID | Descripción | Estado |
|-----------|-----|-------------|--------|
| P1 | B1 | Fix trigger obras.estatus en BD | ✅ Implementado |
| P2 | B2 | Tab Entregas en detalle obra + AJAX | ✅ Implementado |
| P3 | B3 | Rutas almacen/Entregas en routes.php | ✅ Implementado |
| P4 | B4 | alert() → showErpToast() en detalle.php | ✅ Implementado |
| P5 | B5 | SQL en controlador → modelo | Diferido (no bloquea) |
| P6 | B6 | Ajuste labels PDF | ✅ Implementado |

---

## 6. SQL ejecutado

```sql
-- B1: Fix trigger obras.estatus — valores válidos del ENUM
DROP TRIGGER IF EXISTS tr_actualizar_entrega_almacen;
CREATE TRIGGER tr_actualizar_entrega_almacen
AFTER INSERT ON detalle_entregas_almacen
FOR EACH ROW
BEGIN
  -- (ver script completo en corrección B1)
  -- 'Finalizada' → 'Completada', 'En Proceso' → 'En Ejecución'
END;
```

---

## 7. Pendientes para Iteración 4

- Integración API paquetería Tres Guerras (ver `doc/PLAN_ENVIOS_TRES_GUERRAS.md`)
- Mejoras módulo Producción (formulaciones/variantes)
- Tab "Entregas" en vista CRM Ventas (`ventas/obras/detalle.php`)
- Módulo checador remoto Bixpe/KONECT
- SQL en controlador `actualizar_ajax` → mover a modelo
