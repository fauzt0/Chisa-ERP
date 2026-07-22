# PLAN DE MEJORA — ERP Chisa Recubrimientos

> **Propósito:** Plan maestro de mejora del sistema ERP, priorizado por módulos con tareas accionables.
>
> **Versión:** 1.1 | **Última actualización:** 2026-06-19

---

## Índice

- [Resumen Ejecutivo](#resumen-ejecutivo)
- [Priorización](#priorización)
- [🔴 P0 — Módulo RH y Reloj Checador](#-p0--módulo-rh-y-reloj-checador)
- [🟡 P1 — Módulo de Producción y Proveedores](#-p1--módulo-de-producción-y-proveedores)
- [🟡 P1 — Módulo de Ventas y Obras](#-p1--módulo-de-ventas-y-obras)
- [🟢 P2 — Módulo de Facturación y Mejoras Generales](#-p2--módulo-de-facturación-y-mejoras-generales)
- [⚪ P3 — Mejoras Futuras (Backlog)](#-p3--mejoras-futuras-backlog)
- [Cronograma Estimado](#cronograma-estimado)
- [Métricas de Éxito](#métricas-de-éxito)

---

## Resumen Ejecutivo

El ERP Chisa Recubrimientos es un sistema maduro basado en CodeIgniter 3 con 13 módulos funcionales. Actualmente en fase de **transición de desarrollo a pruebas**, requiere mejoras focalizadas en cuatro áreas críticas:

1. **Recursos Humanos + Reloj Checador** — Integración completa empleados↔asistencias, UI moderna
2. **Producción + Proveedores** — Overhaul de formulaciones tipo Excel, flujo de producción automatizado, entrenamiento con tablas reales
3. **Ventas + Obras** — Estimaciones de obra profesional con desglose de materiales y costos
4. **Facturación + General** — Automatización, exportaciones, bitácora, experiencia de usuario

---

## Priorización

| Prioridad | Significado | Módulos |
|-----------|-------------|---------|
| 🔴 **P0** | Crítico — Bloquea operación diaria | RH, Reloj Checador |
| 🟡 **P1** | Alto — Impacto directo en productividad | Producción, Proveedores/Compras, Ventas, Obras |
| 🟢 **P2** | Medio — Mejora de calidad y UX | Facturación, General |
| ⚪ **P3** | Backlog — Deseable a futuro | Varios |

---

## 🔴 P0 — Módulo RH y Reloj Checador

### RH-01: Mejora UI del Offcanvas de Detalle de Empleado

**Problema:** El offcanvas actual muestra datos en tablas básicas sin agrupación visual clara. La vinculación con el reloj checador es mínima.

**Tareas:**
- [ ] **RH-01.1** Mejorar diseño del offcanvas con pestañas (Tabs): Información Personal, Fiscal, Laboral, Reloj Checador, Documentos.
- [ ] **RH-01.2** Agregar badge de "Reloj Checador" con última checada y días trabajados del mes (ya implementado parcialmente en `asistencias_reloj_resumen`).
- [ ] **RH-01.3** Botón "Ver Registros" que abra modal con vista Día/Semana/Mes de checadas (ya implementado parcialmente en `asistencias_reloj_periodo`).
- [ ] **RH-01.4** Mejorar colores y tipografía: badges de estatus más visibles, alertas de datos faltantes con iconos.

**Archivos involucrados:**
- `public_html/application/controllers/rh/RecursosHumanos.php` → métodos `detail()`, `asistencias_reloj_resumen()`, `asistencias_reloj_periodo()`
- `public_html/application/views/rh/empleados/main_empleados.php` → offcanvas
- `public_html/application/models/Reloj/RelojSyncRhModel.php`

---

### RH-02: Integración Completa Empleado ↔ Reloj

**Problema:** La vinculación entre `numero_empleado` (RH) y `PIN` (reloj ZKTeco) no está completamente automatizada ni visible.

**Restricción técnica:** El API local del reloj checador no permite eliminar o editar usuarios individualmente, únicamente dar de alta nuevos usuarios. Por lo tanto:
- Nos limitaremos a asignar un PIN para cada empleado.
- Para agregar un nuevo usuario al reloj, se deberá sincronizar **todos los usuarios nuevamente** (conservando el PIN que tenían anteriormente).
- El PIN del reloj debe ser automático en orden incremental.
- El PIN 1 siempre se asigna al administrador, por lo que el conteo de empleados debe empezar desde el PIN "2".

**Tareas:**
- [ ] **RH-02.1** En el formulario de alta/edición de empleado, mostrar campo `reloj_pin` vinculado a `numero_empleado`.
- [ ] **RH-02.2** Botón "Sincronizar con Reloj" que encole comando `DATA USER` al dispositivo correspondiente.
- [ ] **RH-02.3** Indicador de estado de sincronización: Pendiente / Enviado / Ejecutado / Error.
- [ ] **RH-02.4** Vista previa del comando ADMS antes de encolar (mostrando tabuladores reales como `⇥`).
- [ ] **RH-02.5** Al dar de baja un empleado, mostrar advertencia de que el PIN no se puede eliminar del reloj individualmente.

**Archivos involucrados:**
- `public_html/application/views/rh/empleados/alta.php`
- `public_html/application/views/rh/empleados/editar.php`
- `public_html/application/controllers/rh/RelojChecador.php`
- `public_html/application/models/Reloj/RelojSyncRhModel.php`

---

### RH-03: Calculadora de Finiquito Interactiva

**Problema:** La calculadora actual (`get_datos_calculadora()`) solo retorna datos; no tiene UI interactiva para simular escenarios. Es necesario mejorarla toda.

**Tareas:**
- [ ] **RH-03.1** Crear modal con sliders/inputs para: días de aguinaldo (default 15), prima vacacional % (default 25%), días trabajados, salario diario.
- [ ] **RH-03.2** Calcular en tiempo real (JS): aguinaldo proporcional, vacaciones no gozadas, prima vacacional, indemnización (según tipo de baja).
- [ ] **RH-03.3** Incluir desglose detallado de cada concepto.
- [ ] **RH-03.4** Leyenda obligatoria: "Este es un valor de cálculo aproximado y deberá verificarse."
- [ ] **RH-03.5** Botón "Imprimir" que genere PDF con el desglose.

**Archivos involucrados:**
- `public_html/application/controllers/rh/RecursosHumanos.php` → `get_datos_calculadora()`
- `public_html/application/views/rh/empleados/main_empleados.php` → modal calculadora

---

### RH-04: Validación de Datos Faltantes

**Problema:** La alerta de datos fiscales faltantes (`get_empleados_datos_faltantes()`) es solo numérica; no guía al usuario a la acción.

**Tareas:**
- [ ] **RH-04.1** En el dashboard RH, mostrar card con lista de empleados con datos faltantes (RFC, CURP, NSS, etc.).
- [ ] **RH-04.2** Cada empleado en la lista debe tener botón directo a editar.
- [ ] **RH-04.3** Colorear campos faltantes en rojo en el formulario de edición.
- [ ] **RH-04.4** Badge en la tabla principal de empleados: "⚠️ Datos incompletos" con tooltip de qué falta.

---

### RH-05: Mejora del sistema de contratos PDF

**Problema:** Actualmente el sistema permite crear contratos con base en plantillas. Ya existen plantillas base pero visualmente se ven mal. Los PDFs generados tienen poca calidad estética y no reflejan una imagen profesional.

**Tareas:**
- [ ] **RH-05.1** Rediseñar las plantillas base de contratos con formato profesional: encabezado con logo de la empresa, datos del empleado en tabla estructurada, cláusulas numeradas y bien espaciadas, áreas de firma (empleado, empresa, testigos) con líneas punteadas, pie de página con número de página y fecha.
- [ ] **RH-05.2** Mejorar el editor de plantillas (TinyMCE actual) con estilos predefinidos: estilos para títulos (h1, h2), estilos para tablas de datos del empleado, estilos para cláusulas con sangría, placeholders visibles `[{nombre_empleado}]`, `[{puesto}]`, `[{salario}]`, `[{fecha_ingreso}]`, etc.
- [ ] **RH-05.3** Crear previsualización en vivo del contrato dentro del modal/offcanvas antes de generarlo como PDF.
- [ ] **RH-05.4** Mejorar la generación de PDF con MPDF: márgenes adecuados (2.5cm), fuentes profesionales (DejaVu Sans), colores corporativos, encabezado y pie de página automáticos, tabla de datos del empleado con diseño limpio.
- [ ] **RH-05.5** Agregar al menos 3 plantillas precargadas: "Contrato por tiempo indeterminado" (planta), "Contrato por obra o tiempo determinado", "Contrato de honorarios".
- [ ] **RH-05.6** Opción de "Vista previa en HTML" antes de generar PDF definitivo.
- [ ] **RH-05.7** Al guardar un contrato, conservar el contenido HTML formateado para poder reeditarlo posteriormente (no solo el PDF).
- [ ] **RH-05.8** Historial de contratos con timeline visual mejorado.

**Archivos involucrados:**
- `public_html/application/models/RH/ContratoModel.php`
- `public_html/application/models/RH/PlantillaModel.php`
- `public_html/application/views/rh/empleados/nuevo_contrato.php`
- `public_html/application/views/rh/empleados/form_plantilla.php`
- `public_html/application/views/rh/empleados/plantillas.php`

---

### RL-01: Dashboard Mejorado del Reloj Checador

**Problema:** El dashboard actual (`RelojChecador.php`) tiene estadísticas básicas pero carece de visualizaciones impactantes.

**Nota importante:** No tocar funcionalidades del sistema en estos puntos, solo mejorar la interfaz.

**Tareas:**
- [ ] **RL-01.1** Agregar gráficos con Chart.js: checadas por hora (barras), asistencia diaria (línea), distribución de métodos de verificación (pastel).
- [ ] **RL-01.2** Card de "Hoy" con: total empleados, presentes, ausentes, retardo.
- [ ] **RL-01.3** Tabla en tiempo real de últimas 10 checadas (con auto-refresh cada 30s opcional).
- [ ] **RL-01.4** Filtro por dispositivo en el dashboard.

**Archivos involucrados:**
- `public_html/application/controllers/rh/RelojChecador.php` → `dashboard_stats_ajax()`
- `public_html/application/views/rh/reloj_checador/main.php`

---

### RL-02: Reportes Visuales de Asistencias

**Problema:** Los reportes diario/mensual son tablas planas; falta visualización gráfica y exportación avanzada.

**Tareas:**
- [ ] **RL-02.1** Agregar vista de calendario mensual con colores: verde (asistió), rojo (falta), amarillo (retardo), gris (descanso).
- [ ] **RL-02.2** Exportar a PDF el reporte mensual con formato profesional (logo, periodo, firmas).
- [ ] **RL-02.3** Comparativa mes actual vs mes anterior.
- [ ] **RL-02.4** Detalle por empleado: horas trabajadas, horas extra, retardos acumulados.

---

## 🟡 P1 — Módulo de Producción y Proveedores

### PR-01: Overhaul de Formulaciones (Editor Tipo Excel)

**Problema:** El editor actual de formulaciones es confuso y no refleja la estructura real de las hojas de trabajo de Chisa. Se necesitan tablas similares a las que usan actualmente los usuarios en Excel (ver imágenes de referencia `image.png` a `image-8.png`), donde se muestren:
- Columna 1: Nombre del producto final y nombre de los insumos/materia prima
- Columna 2: Porcentajes de cada insumo
- Columna 3: Unidades (kilos, litros, etc.) con total al final
- Grupos de color (sub-bloques como "COLOR CAFÉ", "COLOR AZUL", etc.)
- Fase acuosa con sus cálculos de porcentaje y kg resultantes

**Tareas:**
- [ ] **PR-01.1** Rediseñar tabla de detalle de formulación replicando el formato Excel real de las imágenes de referencia:
  - **Encabezado**: Nombre del producto, código, versión, fecha
  - **Columnas**: Insumo/Material | % (como en columna 2 de imágenes) | kg/lts (como en columna 3) | % Fase Acuosa | kg Fase Acuosa | Costo Unitario | Costo Total
  - **Totales**: Fila de total al final con 100% y suma de kg/lts
- [ ] **PR-01.2** Agregar agrupación visual por `grupo_color` con filas divisoras tipo "COLOR CAFÉ", "FASE ACUOSA BASE", etc. (como se ve en las imágenes de referencia), con subtotales por grupo.
- [ ] **PR-01.3** Inputs de simulación en cabecera: "Número de cubetas" y "Metros cuadrados del proyecto". Al cambiar estos valores, toda la columna de "Total Insumo" debe recalcularse en frontend (similar al cálculo automático de un Excel).
- [ ] **PR-01.4** Edición inline de celdas (`%`, `kg`, `porcentaje_fase_acuosa`) para usuarios con permiso `produccion_editar`, con recálculo automático de dependencias.
- [ ] **PR-01.5** Botón "Guardar como nueva versión" (crea nueva cabecera, preserva histórico).
- [ ] **PR-01.6** Botón "Actualizar versión actual" (sobrescribe, solo admin).

**Archivos involucrados:**
- `public_html/application/views/produccion/productos/main.php`
- `public_html/application/controllers/produccion/Productos.php`
- `public_html/application/models/Produccion/ProductosModel.php`

---

### PR-02: Migración de Base de Datos para Formulaciones

**Problema:** La BD actual no soporta grupos de color, fase acuosa, ni vinculación a clientes/pedidos.

**Tareas:**
- [ ] **PR-02.1** `ALTER TABLE formulaciones ADD COLUMN cliente_id INT NULL`
- [ ] **PR-02.2** `ALTER TABLE formulaciones ADD COLUMN comentarios TEXT NULL`
- [ ] **PR-02.3** `ALTER TABLE formulaciones ADD COLUMN rendimiento_m2_por_kg DECIMAL(10,4) NULL`
- [ ] **PR-02.4** `ALTER TABLE detalle_formulacion ADD COLUMN grupo_color VARCHAR(100) NULL`
- [ ] **PR-02.5** `ALTER TABLE detalle_formulacion ADD COLUMN porcentaje_fase_acuosa DECIMAL(10,4) NULL`
- [ ] **PR-02.6** `ALTER TABLE detalle_formulacion ADD COLUMN kg_fase_acuosa DECIMAL(10,4) NULL`
- [ ] **PR-02.7** Índice en `formulaciones.cliente_id` y `detalle_formulacion.grupo_color`

---

### PR-03: Entrenamiento del Sistema con Formulaciones Reales

**Problema:** El sistema necesita ser "entrenado" con las formulaciones que los usuarios manejan actualmente en Excel (ver imágenes `image-1.png` a `image-8.png`). Estas formulaciones representan la operación real de la empresa y deben ser la base del nuevo sistema.

**Tareas:**
- [ ] **PR-03.1** Identificar la estructura común en todas las imágenes de referencia:
  - `image.png`: Formulación "CHISA PLUS" con 20 insumos, porcentajes y kilos por cubeta
  - `image-1.png`: Variante con colores CAFÉ, AZUL, CREMA, VERDE — cada uno con sus pigmentos específicos y porcentajes
  - `image-2.png`: Formulación con fases acuosas y múltiples sub-grupos de color
  - `image-3.png`: Formulación para cliente/pedido especial con comentarios
  - `image-4.png`: Formulación con altos porcentajes de resina y pigmentos
  - `image-5.png`: Hoja de cálculo completa con rendimiento en m² por kg
  - `image-6.png`: Formulación con notas de ajuste por lote
  - `image-7.png`: Formulación para línea de productos específica
  - `image-8.png`: Resumen de formulaciones por cliente con históricos
- [ ] **PR-03.2** Crear un **visor de formulaciones** que muestre las tablas exactamente como en los Excel de referencia (formato visual idéntico).
- [ ] **PR-03.3** Agregar una sección "Mis Formulaciones Recientes" en el dashboard de producción que muestre las últimas formulaciones consultadas/editadas, con thumbnail de la tabla.
- [ ] **PR-03.4** Permitir búsqueda y filtro de formulaciones por: nombre de producto, cliente, fecha, grupo de color, comentarios, rango de rendimiento.
- [ ] **PR-03.5** Vincular cada formulación a **imágenes de referencia** (subir captura del Excel original) para que el usuario pueda comparar visualmente la versión digital con el original.
- [ ] **PR-03.6** Calcular y mostrar el **rendimiento en m² por kg** para cada formulación, con calculadora de "¿cuántos m² alcanzo con X cubetas?".

**Archivos involucrados:**
- `public_html/application/views/produccion/productos/main.php`
- `public_html/application/views/produccion/dashboard.php`
- `public_html/application/models/Produccion/ProductosModel.php`
- `imagenes_referencias/` → todas las imágenes de referencia

---

### PR-04: Importador Masivo de Formulaciones desde Excel

**Problema:** Actualmente no hay forma de cargar las formulaciones existentes (como las de `image-1.png` a `image-8.png`) de forma masiva. Se necesita un importador inteligente que reconozca la estructura de las hojas de cálculo que los usuarios ya tienen.

**Tareas:**
- [ ] **PR-04.1** Crear endpoint AJAX `importar_formulacion_excel_ajax`.
- [ ] **PR-04.2** Usar PhpSpreadsheet para leer archivos `.xlsx` con múltiples bloques y detectar automáticamente:
  - **Bloque 1 - Encabezado**: nombre del producto, código, versión, fecha, cliente, notas.
  - **Bloque 2 - Cabecera de columnas**: Insumo, %, kg/lts, (más columnas si existen).
  - **Bloque 3 - Grupo de color**: Detectar filas como "COLOR CAFÉ", "COLOR AZUL", etc. y tratar como separadores de grupo.
  - **Bloque 4 - Insumos**: Nombre del insumo + porcentaje + cantidad + fase acuosa.
  - **Bloque 5 - Total**: Fila con 100% y suma de kg/lts.
- [ ] **PR-04.3** Vincular insumos por nombre exacto con la tabla `insumos`. Si un insumo no existe, mostrarlo en rojo y permitir crearlo sobre la marcha.
- [ ] **PR-04.4** Mostrar preview completa antes de guardar: tabla renderizada igual al Excel original, con opción de:
  - Corregir matches de insumos (si el nombre difiere ligeramente).
  - Reordenar grupos de color.
  - Ajustar porcentajes si es necesario.
- [ ] **PR-04.5** Generar formulación completa + detalle automáticamente en una transacción.
- [ ] **PR-04.6** Permitir **carga por lotes**: seleccionar múltiples archivos Excel y procesarlos secuencialmente.
- [ ] **PR-04.7** Log de importación: registrar qué archivos se importaron, cuántos insumos, qué errores hubo.

**Archivos involucrados:**
- `public_html/application/controllers/produccion/Productos.php` → nuevo método `importar_formulacion_excel_ajax`
- `public_html/application/models/Produccion/ProductosModel.php` → nuevo método `importar_desde_excel()`
- `public_html/application/views/produccion/productos/importar.php` → nueva vista

---

### PR-05: Flujo de Producción — Pedidos Pendientes

**Problema:** El dashboard de producción no muestra claramente los pedidos que requieren fabricación.

**Tareas:**
- [ ] **PR-05.1** Card de "Pedidos Pendientes" en dashboard de producción con alertas sonoras opcionales.
- [ ] **PR-05.2** Al recibir un nuevo pedido (venta/obra) sin stock suficiente, mostrarlo automáticamente.
- [ ] **PR-05.3** Calcular y mostrar insumos necesarios para fabricar los productos del pedido (usando la formulación activa) con tabla similar a las imágenes de referencia.
- [ ] **PR-05.4** Permitir a producción ajustar las cantidades de insumos calculadas.
- [ ] **PR-05.5** Botón "Solicitar Insumos" que genere pre-orden de compra para el módulo de proveedores.
- [ ] **PR-05.6** Escalamiento dinámico: si el pedido requiere 3 cubetas de 27kg cada una, el sistema debe calcular automáticamente los insumos para las 3 cubetas.
- [ ] **PR-05.7** Vista de "instrucciones de producción" que muestre: producto, cantidad a fabricar, lista de insumos con cantidades, pasos de producción.

---

### PR-06: Código de Barras para Productos Terminados

**Problema:** Los productos fabricados no tienen código de barras para trazabilidad.

**Tareas:**
- [ ] **PR-06.1** Integrar librería de código de barras (Code 128 o QR).
- [ ] **PR-06.2** Al finalizar un lote de producción, generar códigos de barras únicos por cubeta.
- [ ] **PR-06.3** Permitir impresión de etiquetas con: código de barras, nombre producto, fecha fabricación, lote, peso.
- [ ] **PR-06.4** Registrar salida de almacén escaneando código de barras (módulo de almacén/ventas).

---

### PV-01: Pre-Órdenes de Compra desde Producción

**Problema:** Cuando faltan insumos para producción, no hay un flujo automatizado hacia compras.

**Tareas:**
- [ ] **PV-01.1** Crear tabla `preordenes` con: producción_id, insumo_id, cantidad_solicitada, cantidad_aprobada, estatus.
- [ ] **PV-01.2** Vista en compras/proveedores: "Pre-Órdenes Pendientes" con opción de aprobar/rechazar/ajustar.
- [ ] **PV-01.3** Al aprobar, crear automáticamente orden de compra.
- [ ] **PV-01.4** Notificaciones a usuarios de compras cuando hay nuevas pre-órdenes.
- [ ] **PV-01.5** Alertas en dashboard de compras/producción/almacén/administración.

---

### PV-02: Alertas de Stock Mínimo

**Problema:** Las alertas de stock mínimo existen pero no están completamente integradas en el dashboard.

**Tareas:**
- [ ] **PV-02.1** Card en dashboard principal: "Insumos bajo stock mínimo" (solo usuarios con permisos a proveedores/producción/almacén/administración).
- [ ] **PV-02.2** Notificación en campana del sistema.
- [ ] **PV-02.3** Configuración de umbrales de stock mínimo por insumo.
- [ ] **PV-02.4** Sugerencia automática de cantidad a reordenar basada en consumo histórico.

---

## 🟡 P1 — Módulo de Ventas y Obras

### VO-01: Generación de "Estimación de Obra" desde Ventas/Obras

**Problema:** En cada venta o cotización no se genera automáticamente un archivo de "estimación de obra" que incluya el desglose de materiales, cantidades, costos y rendimientos. Actualmente los usuarios generan estos documentos manualmente por fuera del sistema (ver imágenes `resumen1.jpeg` a `resumen5.jpeg`).

**Tareas:**
- [ ] **VO-01.1** Crear módulo/nuevo endpoint que, al generar una orden de venta o cotización, ofrezca la opción "Generar Estimación de Obra".
- [ ] **VO-01.2** La Estimación de Obra debe incluir (basado en `resumen1.jpeg` a `resumen5.jpeg`):
  - **Encabezado**: Datos del cliente (nombre, RFC, dirección), datos de la obra (nombre, ubicación), número de estimación, fecha.
  - **Tabla de conceptos**: Partida | Descripción | Unidad (m², lts, kg) | Cantidad | Precio Unitario | Importe.
  - **Desglose de materiales**: Para cada producto en la venta/obra, mostrar su formulación con los insumos necesarios y cantidades (basado en las formulaciones cargadas en producción). Esto debe reflejar tablas similares a las de las imágenes `image-1.png` a `image-8.png`.
  - **Rendimiento**: m² por producto, total de m² de la obra, cálculo de cuántas cubetas/litros/kg se necesitan.
  - **Subtotales por partida** y **Total general** con IVA desglosado.
  - **Notas**: Condiciones de pago, tiempo de entrega, validez de la cotización, observaciones.
  - **Firmas**: Espacio para firma del cliente y del vendedor.
- [ ] **VO-01.3** Generar la estimación en formato PDF profesional (usando MPDF) con:
  - Logo de la empresa
  - Encabezado y pie de página
  - Tablas con bordes y colores corporativos
  - Numeración de página
- [ ] **VO-01.4** Opción de "Vista previa en HTML" antes de generar PDF definitivo.
- [ ] **VO-01.5** Guardar historial de estimaciones generadas para cada orden de venta/obra, con posibilidad de reimprimir.
- [ ] **VO-01.6** Vincular la estimación con la orden de venta/obra, de modo que al cambiar cantidades en la orden, se pueda regenerar la estimación.
- [ ] **VO-01.7** Botón "Enviar Estimación por Correo" que adjunte el PDF al email del cliente.

**Archivos involucrados:**
- `public_html/application/controllers/ventas/Ordenes.php` → nuevo método `generar_estimacion_obra()`
- `public_html/application/controllers/ventas/Clientes.php`
- `public_html/application/controllers/obras/Obras.php`
- `public_html/application/models/Produccion/ProductosModel.php` → para obtener formulaciones
- `public_html/application/views/ventas/estimacion_obra/` → nuevas vistas

---

### VO-02: Plantillas de Estimación de Obra

**Problema:** Cada tipo de obra puede requerir un formato de estimación diferente (obra residencial, industrial, mantenimiento, etc.).

**Tareas:**
- [ ] **VO-02.1** Crear administrador de plantillas de estimación (similar a las plantillas de contratos de RH).
- [ ] **VO-02.2** Plantillas precargadas: "Estimación estándar", "Estimación obra residencial", "Estimación obra industrial", "Cotización simple".
- [ ] **VO-02.3** Placeholders para datos dinámicos: `[{cliente_nombre}]`, `[{obra_nombre}]`, `[{fecha}]`, `[{total}]`, `[{conceptos_tabla}]`, etc.
- [ ] **VO-02.4** Editor de contenido con TinyMCE para personalizar el formato de la estimación.

---

### VO-03: Dashboard de Obras con Seguimiento de Estimaciones

**Problema:** No hay visibilidad del estado de las estimaciones generadas (enviadas, aprobadas, rechazadas).

**Tareas:**
- [ ] **VO-03.1** En el módulo de obras, agregar sección "Estimaciones" con listado de todas las estimaciones generadas.
- [ ] **VO-03.2** Estados: Borrador, Enviada, Aprobada, Rechazada, Convertida en Venta.
- [ ] **VO-03.3** Al aprobar una estimación, opción de convertir automáticamente a orden de venta.
- [ ] **VO-03.4** Métricas: total estimado vs total facturado por obra.

---

## 🟢 P2 — Módulo de Facturación y Mejoras Generales

### FA-01: Automatización de Importación de Facturas

**Problema:** La sincronización con Facture App requiere intervención manual (lazy load).

**Tareas:**
- [ ] **FA-01.1** Crear cron job (`cron/importar_facturas.php`) que ejecute sincronización cada hora.
- [ ] **FA-01.2** Validar que el cron solo corra en CLI (`is_cli()`).
- [ ] **FA-01.3** Registrar resultado de cada ejecución en log.
- [ ] **FA-01.4** Notificar si hay facturas nuevas importadas.

---

### FA-02: Envío de Factura por Correo desde el ERP

**Problema:** El envío de facturas (PDF + XML) por correo no está completamente implementado.

**Tareas:**
- [ ] **FA-02.1** Botón "Enviar por Correo" en el detalle de factura.
- [ ] **FA-02.2** Usar el email del cliente registrado.
- [ ] **FA-02.3** Adjuntar PDF y XML de la factura.
- [ ] **FA-02.4** Plantilla de correo profesional con datos de la factura.
- [ ] **FA-02.5** Registrar envío en bitácora.

---

### GE-01: Exportar a Excel en Todas las Tablas

**Problema:** No hay botón de exportación estandarizado en las tablas del sistema.

**Tareas:**
- [ ] **GE-01.1** Crear helper `export_helper.php` con función `exportar_datatable_a_excel($datos, $columnas, $nombre_archivo)`.
- [ ] **GE-01.2** Agregar botón "Exportar a Excel" en todas las tablas DataTables del sistema.
- [ ] **GE-01.3** Respetar filtros activos al exportar.
- [ ] **GE-01.4** Usar PhpSpreadsheet para generar `.xlsx`.

---

### GE-02: Bitácora Completa del Sistema

**Problema:** La bitácora actual no cubre todos los módulos.

**Tareas:**
- [ ] **GE-02.1** Revisar y completar logs en: producción, obras, facturación, proveedores, almacén, citas, calendario.
- [ ] **GE-02.2** Estandarizar formato de bitácora: `[fecha] [usuario] [módulo] [acción] [detalle]`.
- [ ] **GE-02.3** Vista de bitácora con filtros avanzados: por usuario, módulo, acción, rango de fechas.
- [ ] **GE-02.4** Exportar bitácora a Excel/PDF.

---

### GE-03: Super Administrador

**Problema:** No existe un rol de "super administrador" para validar acciones críticas.

**Tareas:**
- [ ] **GE-03.1** Agregar permiso `super_admin` en `config/permissions.php`.
- [ ] **GE-03.2** Para acciones críticas (editar datos fiscales, eliminar registros), mostrar prompt de contraseña del super admin.
- [ ] **GE-03.3** Registrar validaciones de super admin en bitácora.

---

### GE-04: Logo Personalizable en PDFs

**Problema:** Los PDFs usan un logo hardcodeado o no tienen logo.

**Tareas:**
- [ ] **GE-04.1** Crear configuración de sistema: "Logo del ERP" (upload de imagen).
- [ ] **GE-04.2** Guardar en `assets/uploads/logos/logo_erp.png`.
- [ ] **GE-04.3** Usar en todos los PDFs: contratos, facturas, órdenes de compra, reportes, recibos, estimaciones de obra.

---

### GE-05: Recordatorios de Cumpleaños

**Problema:** No hay felicitación automática de cumpleaños para empleados/usuarios.

**Tareas:**
- [ ] **GE-05.1** Verificar fechas de nacimiento en `empleados` y `users`.
- [ ] **GE-05.2** Notificación en el sistema el día del cumpleaños.
- [ ] **GE-05.3** Pantalla de felicitación al iniciar sesión el día de su cumpleaños.
- [ ] **GE-05.4** Card en dashboard: "Cumpleaños del mes".

---

## ⚪ P3 — Mejoras Futuras (Backlog)

- [ ] **P3-01** Módulo de recordatorios de citas (CRM).
- [ ] **P3-02** Calendario en el CRM.
- [ ] **P3-03** Mejorar botones y colores de texto en alertas con fondos de colores y modales.
- [ ] **P3-04** Conectar API de envíos de TRES GUERRAS para seguimiento de entregas. **(NO REALIZAR AÚN — NECESITAMOS AUTORIZACIÓN DE 3 GUERRAS)**
- [ ] **P3-05** Módulo de "Mi Perfil" para que cada empleado vea sus datos, solicite vacaciones, etc.
- [ ] **P3-06** Validación de permisos en todas las secciones del sistema.
- [ ] **P3-07** Convertir `ViewData` de array a objeto DTO (ver `DTO.md`).
- [ ] **P3-08** Optimizar consultas de permisos usando caché de CodeIgniter.

---

## Cronograma Estimado

| Fase | Prioridad | Módulos | Tareas | Esfuerzo Estimado |
|------|-----------|---------|--------|-------------------|
| **Fase 1** | 🔴 P0 | RH + Reloj Checador | RH-01 a RH-05, RL-01, RL-02 | 4-5 semanas |
| **Fase 2** | 🟡 P1 | Producción + Proveedores | PR-01 a PR-06, PV-01, PV-02 | 6-8 semanas |
| **Fase 3** | 🟡 P1 | Ventas + Obras | VO-01 a VO-03 | 3-4 semanas |
| **Fase 4** | 🟢 P2 | Facturación + General | FA-01, FA-02, GE-01 a GE-05 | 2-3 semanas |
| **Fase 5** | ⚪ P3 | Backlog | P3-01 a P3-08 | Según disponibilidad |

---

## Métricas de Éxito

| Indicador | Meta |
|-----------|------|
| Empleados con datos fiscales completos | > 95% |
| Sincronización empleado↔reloj exitosa | > 98% |
| Contratos PDF con formato profesional generados | 100% |
| Formulaciones cargadas desde Excel (training) | > 90% de las existentes |
| Tiempo para crear/editar una formulación | Reducción del 50% |
| Pedidos de producción procesados en < 24h | > 90% |
| Estimaciones de obra generadas automáticamente | 100% de las ventas/obras |
| Facturas sincronizadas automáticamente | 100% |
| Cobertura de bitácora del sistema | Todos los módulos |
| Exportación a Excel disponible | Todas las tablas |

---

> **ERP Chisa Recubrimientos** — Departamento de Ingeniería de Software
>
> *Este plan es una guía viva. Se actualizará conforme avancen las fases y surjan nuevas necesidades.*