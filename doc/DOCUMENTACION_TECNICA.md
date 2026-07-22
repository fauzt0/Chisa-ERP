# DOCUMENTACIÓN TÉCNICA — MÓDULO DE PRODUCCIÓN Y FORMULACIONES (ERP CHISA)

Este documento sirve como guía arquitectónica, estándar de desarrollo y estado de avance para que cualquier desarrollador o agente de IA pueda retomar este proyecto de forma fluida y sin fricciones.

---

## Índice

- [1. Arquitectura General del Sistema](#1-arquitectura-general-del-sistema)
  - [1.1 Estructura de Directorios Clave](#11-estructura-de-directorios-clave)
- [2. Clases Base y Estándares del Proyecto](#2-clases-base-y-estándares-del-proyecto)
  - [2.1 Controladores (`MY_Controller.php`)](#21-controladores-my_controllerphp)
  - [2.2 Modelos (`MY_Model.php`)](#22-modelos-my_modelphp)
- [3. Pautas de Desarrollo Específicas](#3-pautas-de-desarrollo-específicas)
  - [3.1 Funcionamiento de DataTables (Patrón AJAX)](#31-funcionamiento-de-datatables-patrón-ajax)
  - [3.2 Gestión de Permisos y Costos](#32-gestión-de-permisos-y-costos)
  - [3.3 Separación Estricta de Modelo y Controlador](#33-separación-estricta-de-modelo-y-controlador)
- [4. Módulo de Producción y Overhaul de Formulaciones (El Reto Actual)](#4-módulo-de-producción-y-overhaul-de-formulaciones-el-reto-actual)
  - [4.1 Entidades y Relaciones Actuales](#41-entidades-y-relaciones-actuales)
  - [4.2 Hallazgo Crítico en Hojas de Referencia (Excel Real)](#42-hallazgo-crítico-en-hojas-de-referencia-excel-real)
  - [4.3 Propuesta de Cambio de Base de Datos (Esquema no Destructivo)](#43-propuesta-de-cambio-de-base-de-datos-esquema-no-destructivo)
- [5. Próximos Pasos Técnicos para Completar el Overhaul](#5-próximos-pasos-técnicos-para-completar-el-overhaul)
  - [Paso 1: Aplicar Migración de Base de Datos](#paso-1-aplicar-migración-de-base-de-datos)
  - [Paso 2: Actualizar el Backend (`ProductosModel.php` y `Productos.php`)](#paso-2-actualizar-el-backend-productosmodelphp-y-productosphp)
  - [Paso 3: Rediseñar la UI de Formulaciones (`views/produccion/productos/main.php`)](#paso-3-rediseñar-la-ui-de-formulaciones-viewsproduccionproductosmainphp)
  - [Paso 4: Importador de Archivos Excel](#paso-4-importador-de-archivos-excel)

---

## 1. Arquitectura General del Sistema

El ERP de Chisa Recubrimientos está construido bajo el framework **CodeIgniter 3** (PHP 7.4), utilizando una base de datos relacional (MySQL/MariaDB) y una interfaz de usuario interactiva basada en **Bootstrap**, **AdminLTE / Plantilla General**, **DataTables**, **jQuery** y **SweetAlert2**.

### 1.1 Estructura de Directorios Clave
El código fuente principal reside en `public_html/application/`:
- `core/`: Contiene las clases base de la aplicación (`MY_Controller.php`, `MY_Model.php`).
- `controllers/`: Lógica de control dividida por módulos (ej. `produccion/`, `compras/`, `obras/`, `ventas/`).
- `models/`: Modelos de base de datos divididos por módulos (ej. `Produccion/ProductosModel.php`).
- `views/`: Vistas divididas por módulos (ej. `produccion/productos/main.php`).
- `helpers/`: Helpers globales (ej. `permissions_helper.php`).
- `libraries/`: Librerías de inicialización y utilidades (ej. `Init_controller.php`).

---

## 2. Clases Base y Estándares del Proyecto

Para mantener la seguridad, consistencia y eficiencia, todo desarrollo debe heredar y respetar el funcionamiento de las clases del núcleo (`core/`).

### 2.1 Controladores (`MY_Controller.php`)
Todos los controladores del sistema heredan de `MY_Controller`. Este realiza automáticamente:
1. **Verificación de Sesión Activa**: A través de `Init_controller->check_session()`.
2. **Seguridad y Permisos por Módulo**: Si el controlador define la propiedad protegida `$modulo` (ej. `protected $modulo = 'Producción';`), el constructor valida automáticamente si el usuario tiene acceso. Si no lo tiene, redirige a la vista `deny` o retorna una respuesta JSON de error en llamadas AJAX.
3. **Estructura Estándar de Retorno (`$this->viewData`)**: Inicializa un array estandarizado para renderizado de vistas en el layout general (`layouts/general_template`).

### 2.2 Modelos (`MY_Model.php`)
Todos los modelos heredan de `MY_Model`, el cual proporciona métodos CRUD genéricos optimizados:
- **Soft Delete**: El sistema no elimina físicamente registros clave; realiza baja lógica modificando el campo `estatus` (ej. `0` o `Inactivo`).
- **Integración Nativa con DataTables**: Provee `get_datatables()` y `count_filtered()` que leen automáticamente los parámetros de paginación, ordenamiento y búsqueda enviados por DataTables (`$_POST['length']`, `$_POST['search']`, etc.) basándose en la propiedad configurada `$datatableConfig`.

---

## 3. Pautas de Desarrollo Específicas

### 3.1 Funcionamiento de DataTables (Patrón AJAX)
Para poblar tablas dinámicas, se utiliza el patrón DataTables Server-Side:
1. **Frontend**: Se inicializa la tabla apuntando a un endpoint AJAX del controlador (ej. `produccion/Productos/lista_ajax`).
2. **Controlador**: Llama a `$this->ProductosModel->get_datatables()`, itera sobre los resultados para formatear columnas (badges, botones de acción, imágenes con zoom), y retorna el JSON requerido por la especificación DataTables:
   ```json
   {
       "draw": 1,
       "recordsTotal": 120,
       "recordsFiltered": 15,
       "data": [ [...] ]
   }
   ```

### 3.2 Gestión de Permisos y Costos
- **Privilegios**: La tabla `privilege` asocia un `user_id` (campo `admin`) con un permiso (campo `permiso`) y un bit de activación (`valor = 1`).
- **Verificación**: Se utiliza el helper global `tiene_permiso('nombre_del_permiso')`.
- **Restricción de Costos/Precios en Producción**: Existe el permiso específico `produccion_ver_costos`. Para proteger datos confidenciales tanto en PHP como en la UI, se utilizan las siguientes funciones del `permissions_helper.php`:
  - `puede_ver_costos()`: Retorna un booleano.
  - `ocultar_costo($costo)` y `ocultar_precio($precio)`: Retornan el valor formateado si el usuario tiene el permiso, o la etiqueta `<span class="text-muted"><i class="fas fa-lock"></i> Restringido</span>` si no está autorizado.

### 3.3 Separación Estricta de Modelo y Controlador
- **Controlador**: Únicamente maneja la solicitud HTTP, valida parámetros de entrada (sanitización), invoca los métodos del modelo y retorna la respuesta (HTML o JSON). Nunca debe contener queries SQL directas o lógica de negocio compleja.
- **Modelo**: Contiene toda la lógica de negocio, queries Active Record (`$this->db->...`), cálculos matemáticos y procesamiento de datos.

---

## 4. Módulo de Producción y Overhaul de Formulaciones (El Reto Actual)

El objetivo actual es transformar el editor de formulaciones estáticas en un **dashboard interactivo tipo Excel** extremadamente potente y amigable para el usuario.

### 4.1 Entidades y Relaciones Actuales
- `productos`: Representa los productos terminados (tanto Fabricados como de Reventa). Los fabricados (`tipo_producto = 'Fabricado'`) tienen formulaciones asociadas.
- `formulaciones`: Cabecera de la fórmula (versión, costo total, rendimiento global, estatus activa/inactiva, etc.).
- `detalle_formulacion`: Detalle de los ingredientes (insumos o sub-productos), indicando cantidades y porcentajes.

### 4.2 Hallazgo Crítico en Hojas de Referencia (Excel Real)
Al analizar las imágenes reales del proceso de Chisa (`image.png` a `image-8.png`), se descubrió que la estructura real de producción requiere modelar relaciones complejas:
1. **Sub-grupos de Color**: Una formulación base (ej. "CHISA GLASS") contiene múltiples sub-grupos de color (ej. "COLOR CAFÉ", "COLOR AZUL", "COLOR CREMA", "VERDE") en la misma hoja. Cada uno tiene sus propios pigmentos e insumos específicos con sus respectivos porcentajes.
2. **Columna de Fase Acuosa**: Cada pigmento o color se calcula en función de un porcentaje de fase acuosa que aporta o requiere, calculando los kg de fase acuosa resultantes.
3. **Fórmula Específica por Cliente/Pedido**: Los encabezados del Excel real ligan formulaciones a lotes especiales (ej. `"1 CUBETA VENTA DAVID"`, `"3 CUBETAS PEDIATRIA"`).

### 4.3 Propuesta de Cambio de Base de Datos (Esquema no Destructivo)
Para soportar estas características, se deben agregar las siguientes columnas:
- En la tabla `formulaciones`:
  - `cliente_id` (INT, NULL): Ligar formulaciones personalizadas a un cliente del ERP.
  - `comentarios` (TEXT, NULL): Notas de especificaciones o modificaciones del cliente.
  - `rendimiento_m2_por_kg` (DECIMAL, NULL): Factor para la calculadora de rendimiento en obras.
- En la tabla `detalle_formulacion`:
  - `grupo_color` (VARCHAR, NULL): Permite agrupar los componentes por bloque visual (ej. 'COLOR CAFÉ', 'FASE ACUOSA BASE').
  - `porcentaje_fase_acuosa` (DECIMAL, NULL): Porcentaje asociado al cálculo del componente.
  - `kg_fase_acuosa` (DECIMAL, NULL): Kilogramos calculados de fase acuosa.

---

## 5. Próximos Pasos Técnicos para Completar el Overhaul

Si eres el siguiente desarrollador o agente de IA en tomar esta tarea, sigue este plan de ejecución sistemático:

### Paso 1: Aplicar Migración de Base de Datos
Ejecuta sentencias `ALTER TABLE` para incorporar las 6 columnas indicadas en la sección 4.3 sin afectar los datos históricos ya capturados.

### Paso 2: Actualizar el Backend (`ProductosModel.php` y `Productos.php`)
1. Modifica `get_formulacion_completa()` para que devuelva las nuevas columnas y ordene/agrupe los componentes por el campo `grupo_color`.
2. Actualiza `get_historial_formulaciones()` agregando filtros de búsqueda por `cliente_id`, `comentario` (LIKE) y rangos de fecha de creación.
3. Implementa el motor de escalamiento en el modelo: calcula dinámicamente los kilogramos requeridos de cada insumo basándose en la cantidad de cubetas solicitadas o los m² del proyecto (usando `rendimiento_m2_por_kg`).

### Paso 3: Rediseñar la UI de Formulaciones (`views/produccion/productos/main.php`)
1. Reemplaza la visualización estática actual por una tabla interactiva con diseño premium.
2. Agrupa los insumos visualmente por su `grupo_color` agregando filas de cabecera divisoras y totales parciales.
3. Agrega inputs de simulación en la cabecera: "Número de cubetas" o "Metros cuadrados del proyecto". Al cambiar estos valores, toda la columna de "Total Insumo" debe recalcularse en el frontend de inmediato sin recargar la página.
4. Implementa edición inline de celdas (`%`, `kg`, etc.) para usuarios autorizados, recalculando dependencias aritméticas al vuelo, y permitiendo "Guardar como nueva versión" (creando un nuevo registro de cabecera para mantener la auditoría del histórico) o "Actualizar versión actual".

### Paso 4: Importador de Archivos Excel
Crea el endpoint AJAX `importar_formulacion_excel_ajax` que utilice **PhpSpreadsheet** para leer la estructura de múltiples sub-bloques del Excel y generar de manera automatizada la formulación y componentes vinculando los insumos correspondientes.

---
*ERP Chisa Recubrimientos - Departamento de Ingeniería de Software*
