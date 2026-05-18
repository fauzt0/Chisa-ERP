# Plan de Implementación — Módulo de Producción: Formulaciones

## Resumen

El módulo de producción existe y tiene la infraestructura base (tablas `formulaciones`, `detalle_formulacion`, controladores y endpoints AJAX). Sin embargo, la **UI de formulaciones es confusa** y carece de funcionalidades clave descritas en `produccion.md`.

> [!IMPORTANT]
> **Hallazgo crítico de las imágenes de referencia (raíz del proyecto):** Las hojas Excel muestran que una formulación de "CHISA GLASS" contiene múltiples **sub-grupos de color** (COLOR CAFÉ, COLOR AZUL, COLOR CREMA, VERDE, etc.), cada uno con sus propios pigmentos y porcentajes. Además, cada color tiene una columna adicional de **"FASE ACUOSA"** (% de agua + kg resultantes). Los encabezados de hoja también registran datos de cliente/pedido (ej. "1 CUBETA VENTA DAVID", "3 CUBETAS PEDIATRIA", REF. cliente). Esto redefine cómo deben modelarse los componentes en `detalle_formulacion`.

1. **Vista tipo "consulta Excel"** de formulaciones (filas = insumos, columnas = %, kg por cubeta, total requerido para N cubetas).
2. **Edición inline simple** de componentes con permiso de edición.
3. **Cálculo automático**: fórmula para X kilos → cubre Y m² → proyecto Z requiere X m² → sistema calcula kilos e insumos necesarios.
4. **Historial de formulaciones** filtrable por cliente, fecha y comentario.
5. **Importación masiva desde Excel** (preparación estructural, no UI completa todavía).
6. **Mejora del árbol de variantes** (color/textura) en `produccion/Productos`.

---

## Revisión del estado actual

### Lo que ya existe
| Elemento | Estado |
|---|---|
| Tablas `formulaciones` y `detalle_formulacion` | ✅ Existe. Tiene: versión, cantidad_producida, unidad_produccion, porcentaje, cantidad por componente |
| CRUD de formulaciones (AJAX) | ✅ En `Productos.php` |
| Historial de formulaciones por producto | ✅ Existe en `get_historial_formulaciones_ajax()` |
| Vista `produccion/productos/main.php` (89KB) | ✅ Muy grande, confusa |
| Variantes (padre → hijo) | ✅ Existe pero confuso |
| Campo `rendimiento` en tabla `productos` | ✅ Existe (rendimiento m²/kg del producto) |

### Lo que falta en la BD
| Campo | Tabla | Propósito |
|---|---|---|
| `cliente_id` | `formulaciones` | Ligar formulación específica a un cliente |
| `comentarios` | `formulaciones` | Notas/especificaciones del cliente (REF. cliente, pedido especial) |
| `rendimiento_m2_por_kg` | `formulaciones` | Cuántos m² cubre 1 kg de esta formulación específica |
| `grupo_color` | `detalle_formulacion` | Agrupa pigmentos por variante de color (CAFÉ, AZUL, CREMA, VERDE, etc.) |
| `porcentaje_fase_acuosa` | `detalle_formulacion` | % de fase acuosa asignado a este componente |
| `kg_fase_acuosa` | `detalle_formulacion` | kg de fase acuosa calculados para este componente |

> [!IMPORTANT]
> Se requieren **6 columnas nuevas** (3 en `formulaciones`, 3 en `detalle_formulacion`). Son migraciones no destructivas. El campo `grupo_color` es clave: permite agrupar los pigmentos en bloques de color dentro de una misma formulación, replicando exactamente la estructura de las hojas Excel de referencia.

---

## Open Questions

> [!IMPORTANT]
> **Pregunta 1 — Carga de Excel**: ¿El formato de Excel para carga masiva de formulaciones será el mismo que las hojas de referencia (columnas: nombre insumo, %, kg por cubeta)? ¿O necesitan un template nuevo? Esto afecta el diseño del importer.

> [!IMPORTANT]
> **Pregunta 2 — Rendimiento en m²**: Las imágenes de referencia muestran solo `kilos por cubeta`. ¿El campo de m²/kg es por formulación o por producto? La propuesta es: el campo `rendimiento` ya existe en `productos` (global), y el nuevo `rendimiento_m2_por_kg` en `formulaciones` lo sobreescribe si se especifica.

> [!NOTE]
> **Pregunta 3 — Variantes con color**: Las imágenes `PXL_20260211_202309113` muestran variantes de color (CAFÉ, AZUL, CREMA, VERDE) con proporciones de pigmento. ¿Estas variantes de color se manejan como productos hijos (variantes en la BD) o como componentes especiales dentro de la misma formulación base?

---

## Cambios Propuestos

### 1. Base de Datos — Migración

#### [MODIFY] Tabla `formulaciones` — Nuevos campos
```sql
ALTER TABLE formulaciones
  ADD COLUMN cliente_id INT NULL DEFAULT NULL AFTER producto_id,
  ADD COLUMN comentarios TEXT NULL DEFAULT NULL AFTER descripcion,
  ADD COLUMN rendimiento_m2_por_kg DECIMAL(10,4) NULL DEFAULT NULL AFTER unidad_produccion;
```

---

### 2. Backend — Model y Controller

#### [MODIFY] [ProductosModel.php](file:///home/st32477/domains/erp.chisarecubrimientos.com.mx/public_html/application/models/Produccion/ProductosModel.php)
- `get_historial_formulaciones()`: agregar filtros por `cliente_id`, `comentarios` (LIKE) y rango de `fecha_creacion`.
- `get_formulacion_completa()`: incluir `cliente_id` and `comentarios` en el SELECT.
- `crear_formulacion()`: aceptar `cliente_id`, `comentarios`, `rendimiento_m2_por_kg`.
- Nuevo método `calcular_insumos_para_proyecto($formulacion_id, $m2_requeridos)`: dado un proyecto de X m², retorna la tabla de insumos escalada.

#### [MODIFY] [Productos.php](file:///home/st32477/domains/erp.chisarecubrimientos.com.mx/public_html/application/controllers/produccion/Productos.php)
- `crear_formulacion_ajax()`: aceptar nuevos campos del formulario.
- `get_historial_formulaciones_ajax()`: aceptar parámetros `cliente_id`, `fecha_inicio`, `fecha_fin`, `comentario`.
- Nuevo endpoint `calcular_para_proyecto_ajax()`: recibe `formulacion_id` + `m2` (o `cubetas`), retorna tabla de insumos calculada escalada.
- Nuevo endpoint `importar_formulacion_excel_ajax()`: procesa un archivo `.xlsx` con la estructura de las hojas de referencia.

---

### 3. Frontend — Vista `produccion/productos/main.php`

Este es el cambio más grande. La vista actual (89KB) se mejora con:

#### Tab 1: Lista de Productos (existente, mejorada)
- La tabla DataTables actual se mantiene.
- Mejora visual de botones de acción.

#### Tab 2: Consulta de Formulaciones (NUEVA)
Vista tipo "Excel/tabla de producción" basada en las imágenes de referencia:

```
┌─────────────────────────────────────────────────────────────────────┐
│  [Selector Producto ▼]  [Selector Formulación ▼]  [🔍 Buscar]       │
│  Filtros: Cliente | Fecha | Comentario                               │
├─────────────────────────────────────────────────────────────────────┤
│  PRODUCTO: CHISA PLUS    C/QE2383  C/QE220-S    📅 2024-01-15       │
│  Comentario: Formulación estándar para cliente ABC                   │
├──────────────────────┬──────────┬──────────────┬───────────────────┤
│ INSUMO               │    %     │ kg/cubeta    │ TOTAL (3 cubetas) │
├──────────────────────┼──────────┼──────────────┼───────────────────┤
│ Agua                 │ 24.44%   │ 6.599        │ 19.797 kg         │
│ Edenol DOA           │ 0.40%    │ 0.108        │ 0.324 kg          │
│ Dipersante           │ 0.20%    │ 0.054        │ 0.162 kg          │
│ ...                  │ ...      │ ...          │ ...               │
├──────────────────────┼──────────┼──────────────┼───────────────────┤
│ TOTAL                │ 100.00%  │ 27.000 kg    │ 81.000 kg         │
└──────────────────────┴──────────┴──────────────┴───────────────────┘

  [Calculadora] Cubetas: [___3___]  o  Área: [___] m²   [Calcular ▶]
  [✏️ Editar Formulación]  [📋 Nueva Versión]  [📤 Importar Excel]
```

#### Edición inline (solo con permiso de edición)
- Al hacer clic en "Editar Formulación", las celdas de `%` y `kg` se vuelven inputs editables.
- Botón "Guardar como nueva versión" → crea nueva fila en `formulaciones` (conserva el historial).
- Botón "Actualizar versión actual" → modifica la versión existente.

#### Tab 3: Historial / Árbol de Formulaciones (MEJORADO)
- Timeline visual por producto.
- Filtros: cliente, fecha, comentario.
- Cada item del historial muestra: versión, fecha, cliente, comentario y botón "Replicar esta versión".
- Botón "Activar" para setear como formulación activa.

---

### 4. Importación desde Excel

#### Formato de la hoja Excel esperada (basada en imágenes de referencia):
| Columna A | Columna B | Columna C |
|---|---|---|
| Nombre Producto | KILOS | cantidad_total |
| Nombre Insumo 1 | % | kg |
| Nombre Insumo 2 | % | kg |
| ... | ... | ... |
| TOTAL | 100.00% | suma_kg |

- El endpoint `importar_formulacion_excel_ajax()` usa **PhpSpreadsheet** (ya instalado en el proyecto).
- Lee la hoja, identifica producto por nombre, crea formulación, agrega componentes por nombre de insumo (buscando en tabla `insumos`).

---

## Plan de Verificación

### Pruebas automáticas (endpoints AJAX)
- `POST produccion/Productos/calcular_para_proyecto_ajax` con `formulacion_id` + `m2=50` → respuesta con tabla de insumos escalada.
- `POST produccion/Productos/get_historial_formulaciones_ajax` con `cliente_id` → solo retorna formulaciones de ese cliente.
- `POST produccion/Productos/importar_formulacion_excel_ajax` con archivo de prueba → crea formulación con componentes.

### Verificación manual en navegador
1. Navegar a `produccion/Productos` → ver el Tab de "Consulta de Formulaciones".
2. Seleccionar un producto fabricado → ver su formulación en tabla tipo Excel.
3. Cambiar el número de cubetas → ver cálculo automático de insumos.
4. Ingresar m² → ver cálculo automático usando `rendimiento_m2_por_kg`.
5. Editar una fila de insumo inline → guardar como nueva versión → verificar en historial.
6. Filtrar historial por cliente → solo aparecen formulaciones de ese cliente.
