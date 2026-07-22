# Handoff — Iteración 5 Completada (Obras) + Preparación para Iteración 6 (Entrenamiento y Carga Excel de Producción)

> **Propósito:** Documento de transferencia de contexto para iniciar una **nueva conversación** en Cursor sin saturar tokens.  
> Contiene el estado del proyecto, resumen de la arquitectura, detalles del trabajo completado en la Iteración 5 y el plan detallado para la **Iteración 6: Producción (Carga de Excels restantes y Afinamiento de Cálculos)**.

**Fecha:** Miércoles 8 de Julio, 2026  
**Rama activa:** `feature/rh` (repositorio subido exitosamente)  
**Entorno técnico:** CodeIgniter 3 (PHP 7.4), MySQL/MariaDB, AdminLTE/Bootstrap 5, jQuery 3.7.1, DataTables 2.0.7, Lucide Icons, FontAwesome 5.

---

## ⚠️ Restricciones CRÍTICAS de UI (NO violar)

| Componente | ¿Usar? | Motivo |
|------------|:-------:|--------|
| **SweetAlert2 (`Swal`)** | ❌ NO (excepto POS) | **No está cargado globalmente en la app** (ni CDN ni en `app.js`). Solo fue cargado con CDN en `ventas/pos/main.php` para la selección de templates de recibo. Si se usa en otros lados, lanzará `ReferenceError: Swal is not defined`. |
| **Toastr (`toastr`)** | ❌ NO | No está cargado globalmente (algunas llamadas pre-existentes tienen guardia `typeof toastr !== 'undefined'`). |
| **`showErpToast()`** | **¡SÍ, USAR SIEMPRE!** | Es la función global no intrusiva de alertas toast cargada en `application/views/layouts/general_template.php`. Admite `{type: 'success'\|'danger'|'warning'\|'info', module, title, message}`. |
| **Modales de confirmación** | **¡SÍ, BOOTSTRAP 5!** | Usa modales Bootstrap 5 nativos (`<div class="modal fade">`) y lánzalos/ciérralos vía JS con `bootstrap.Modal.getOrCreateInstance(el).show() / hide()`. |

---

## 🛠️ Resumen de Trabajo Completado (Iteración 5 - Obras)

### 1. Base de Datos
- Se agregó la columna `orden_venta_id` (INT, NULL) en la tabla `obras` para vincular de forma relacional una obra con una Orden de Venta.
- Migración creada y ejecutada en `database/obras_orden_venta_link.sql`.

### 2. PDF Profesional Multipágina (5 hojas de diseño corporativo)
- **Endpoint:** `obras/Obras/exportar_pdf/{id}`
- **Plantilla:** `views/obras/pdf_resumen.php`
- **Generación:** `html2pdf.js` en frontend (diseño consistente con el módulo de RH).
- **Estructura del PDF:**
  - *Hoja 1: Resumen ejecutivo.* Logotipo global del ERP configurado dinámicamente, datos fiscales, tabla de productos/materiales con subtotales, IVA y totales.
  - *Hoja 2: Avance de obra.* Resumen de M² contratados y m² ejecutados, barra de progreso nativa (%) y gráfica visual de barras de avance.
  - *Hoja 3: Hoja de resumen técnico.* Desglose detallado de áreas acumuladas generadas y bloques estructurados para firmas de los autorizadores.
  - *Hoja 4: Estimación de obra.* Caja financiera completa de la obra (desglose de partidas por secciones, retenciones y totales financieros).
  - *Hoja 5: Generador de cuantificación.* Estructurado por `seccion_obra`, simbología técnica y totales métricos acumulados.
- **Acciones:** Añadidos botones dinámicos en la vista de detalle de la obra para "Previsualizar PDF" y "Descargar PDF".

### 3. Vinculación Obra ↔ Orden de Venta (CRM)
- Permite generar una Orden de Venta en el CRM (con estatus `'Cotización'`) a partir de la lista de productos y materiales calculados para la obra.
- Modal Bootstrap 5 para vincular manualmente una Orden de Venta ya existente del mismo cliente.
- Badge con enlace dinámico bidireccional (`OV-XXXX` en la vista de Obra y enlace a la obra correspondiente en Ventas → Órdenes).
- Funcionalidad de confirmación para enviar solicitudes a producción cuando la Orden de Venta vinculada sea aprobada.

### 4. Notificaciones y Alerta Sonora
- Se añadió un bloque de "Solicitudes de producción pendientes" en la campana de notificaciones de `Notifications.php`.
- Al confirmar un avance o aprobar una obra sin una Orden de Venta enlazada, se generan solicitudes automáticas a producción.
- En el módulo de Producción se implementó un bucle de polling (cada 60 segundos) que lee las solicitudes de producción activas y, ante cualquier notificación nueva, reproduce un tono auditivo limpio utilizando la **Web Audio API** nativa (evitando depender de archivos `.mp3` estáticos o problemas de cross-origin).

---

## 🗺️ Mapa de Archivos Clave Tocados/Creados (Iteración 5)

- `public_html/application/controllers/obras/Obras.php` (Endpoints de previsualización, exportación de PDF e integración)
- `public_html/application/models/ObrasModel.php` (Queries de enlace de base de datos e inserción de solicitudes de producción)
- `public_html/application/controllers/obras/ObrasVentas.php` (Lógica AJAX de vinculación y generación de OVs)
- `public_html/application/views/obras/pdf_resumen.php` (Maquetación HTML/CSS del PDF corporativo de 5 hojas)
- `public_html/application/views/obras/partials/vinculo_venta.php` (Modal Bootstrap 5 de vinculación/desglose)
- `public_html/application/views/obras/detalle.php` (Botones de PDF y sección visual del vínculo)
- `public_html/application/views/ventas/obras/detalle.php` (Visualización inversa)
- `public_html/application/models/VentasModel.php` (Lógica de inserción de cotizaciones a partir de obras)
- `public_html/application/views/ventas/ordenes/main.php` (Enlace de retorno a la obra desde Ventas)
- `public_html/application/controllers/Notifications.php` (Contador y alerta de solicitudes de producción)
- `public_html/application/views/layouts/general_template.php` (Integración de scripts y reproducción sonora)

---

## 🚀 PRÓXIMA MISIÓN: Iteración 6 — Producción (Cargar Excels restantes + Afinamiento)

El siguiente agente debe implementar la **Iteración 6** del archivo `mejora_hoy.md` utilizando **Sonnet 4.6 / 5** (ideal para la lectura de estructuras complejas de Excel binario, procesamiento de matrices numéricas, cálculos matemáticos densos, y control estricto de fórmulas).

### Requerimientos Detallados:

#### 1. Carga y Entrenamiento con Excels en `entrenamiento/`
- Cargar y entrenar el sistema con los archivos Excel restantes que están en la carpeta `/entrenamiento` (o ubicaciones adjuntas):
  - `FICHAS DE PINTURA Y PASTA.xlsx` (Fichas técnicas y proporciones)
  - `PASTA SERGIO.xlsx` (Formulaciones completas de pasta de la línea Sergio)
  - `ficha masa roca.xlsx` (Ficha técnica de masa roca)
  - `T034.xls` (Formulaciones técnicas específicas)
- **Reglas del Importador:**
  - Extraer los componentes con sus respectivos porcentajes de BOM y ligar cada componente con su correspondiente `insumos.id` (basándose en nombre o código).
  - Si el componente es un sub-producto o producto fabricado intermedio, enlazarse de forma recursiva.
  - Agregar el **nombre de la pestaña del Excel** de donde provenga la fórmula como un campo de `nota_formulacion` o `comentarios` para dar trazabilidad completa.
  - Crear e integrar en el árbol de cambios de formulaciones en caso de detectar versiones existentes con ligeras variaciones de porcentaje.

#### 2. Afinamiento de Cálculos de Insumos y Alertas
- Asegurar que al simular o programar un lote, el cálculo de masa base, porcentaje de fase acuosa y kilogramos resultantes sea 100% exacto para todas las nuevas fórmulas cargadas.
- Validar las conversiones contra stock usando el `unidades_helper.php` implementado en la Iteración 4.

#### 3. Sugerencia de Mejora Operativa (Opcional - Obras)
- Si deseas dar un valor agregado técnico antes de cerrar el proyecto, puedes implementar la **captura de medidas detalladas en Obras (Largo × Altura por muro)** para poblar de forma totalmente automática y real la hoja 5 (Generador de cuantificación) del PDF resumen, igualando la fidelidad exacta de `resumen5.jpeg`.

---

## 💡 Consejos de Modelos para el Siguiente Agente

1. **Sonnet 4.6 / 5 (Recomendado para la Iteración 6):** El entrenamiento de algoritmos, análisis de datos de Excel (`PhpSpreadsheet`) y operaciones matemáticas en bases de datos requiere la máxima capacidad de razonamiento lógico y consistencia sintáctica que ofrece la familia Claude Sonnet.
