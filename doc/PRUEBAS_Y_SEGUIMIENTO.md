# Bitácora de Pruebas, Cambios y Seguimiento: Módulo de Producción (Productos)

Este documento ha sido creado para registrar los cambios realizados en esta sesión, proveer un plan de pruebas exhaustivo y dejar las notas y pasos de seguimiento necesarios para que el siguiente agente/modelo (ej. Claude 3.5 Sonnet) continúe el desarrollo de forma fluida.

---

## 🛠️ Cambios Realizados en esta Sesión

Durante esta sesión nos enfocamos en **eliminar bugs visuales, mejorar el rendimiento de renderizado y reestructurar/modularizar el código** para reducir drásticamente la deuda técnica de la vista principal.

### 1. Resolución Definitiva del "Brinco" (Layout Shift)
* **El Problema:** La consola del navegador mostraba **61 errores 404 consecutivos** al intentar cargar una imagen en la ruta `assets/img/no-image.png`. Cada error disparaba la directiva de fallback `onerror` en el tag `<img>` de la tabla, la cual apuntaba recursivamente a la misma ruta inexistente. Esto creaba un **bucle infinito de redibujado (infinite redraw loop)** que saturaba el renderizado del navegador y causaba el constante "brinco" de la página.
* **La Solución:** 
  1. Generamos una imagen de placeholder profesional y minimalista (`assets/img/no-image.png`) que simula una cubeta de pintura gris.
  2. Modificamos la columna de imágenes en el controlador (`Productos.php`) agregando la instrucción `this.onerror=null;` antes de asignar la ruta fallback, eliminando de raíz cualquier posibilidad de bucle infinito.

### 2. Extracción de JS a Archivo Externo (Modularización)
* **El Problema:** El archivo de vista `main.php` superaba las 2,540 líneas mezclando HTML, CSS inline, modales Bootstrap y bloques masivos de JavaScript.
* **La Solución:**
  1. Creamos el archivo de script estático en `/assets/dist/js/produccion_productos.js` y transferimos las más de 1,480 líneas de JS allí.
  2. Limpiamos todas las etiquetas de PHP (`<?php ... ?>` o `<?= ... ?>`) que estaban incrustadas en el JavaScript para evitar errores de sintaxis en el cliente.
  3. Inyectamos las variables dinámicas de PHP al principio de la vista `main.php` en un bloque de configuración global:
     ```javascript
     const PUEDE_VER_COSTOS = <?= $puede_ver_costos ? 'true' : 'false' ?>;
     const BASE_URL = '<?= base_url() ?>';
     const CSRF_TOKEN_NAME = '<?= $this->security->get_csrf_token_name() ?>';
     const CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';
     ```
  4. Actualizamos el JS para utilizar estas constantes (`BASE_URL`, `CSRF_TOKEN_NAME`, `CSRF_HASH`) de forma nativa en todas sus llamadas AJAX de jQuery.
  5. **Resultado:** `main.php` se redujo a solo **1,059 líneas** (un 58% más limpio, legible y mantenible).

### 3. Ajuste de Layout en DataTables
* Se deshabilitó la clase `.fade` de la pestaña activa en la carga inicial (`pane-lista-productos`) para evitar micro-animaciones que confundan los cálculos de ancho de DataTables.
* Se añadieron propiedades recomendadas en la inicialización: `deferRender: true` y `autoWidth: false`, además de integrar un callback `drawCallback` que fuerza a DataTables a recalcular anchos exactos al terminar de pintar: `this.api().columns.adjust();`.

---

## 🧪 Plan de Pruebas Recomendado (Manual)

Por favor, realiza las siguientes comprobaciones para garantizar que todo funcione al 100% y diagnosticar por qué no se ven productos en el Simulador:

### Paso 1: Limpieza de Caché del Navegador (CRÍTICO)
> [!IMPORTANT]
> Dado que movimos todo el JavaScript a un archivo externo estático, tu navegador podría estar usando una versión en caché antigua de la página o no haber cargado el nuevo script.
> * Presiona **`Ctrl + F5`** (o `Cmd + Shift + R` en Mac) para forzar la recarga limpia de todos los recursos del sitio.

### Paso 2: Verificación de la Pestaña "Lista de Productos"
1. Entra a la ruta `/produccion/Productos`.
2. Verifica que el catálogo de productos cargue correctamente de forma rígida y fluida, sin parpadeos.
3. Abre la Consola del Desarrollador (`F12` -> `Console`) y confirma que no haya ningún error en rojo (debería estar en 0 errores 404).
4. Busca un producto sin imagen, confirma que muestre la cubeta gris y haz clic sobre ella para comprobar que el modal de zoom funcione.

### Paso 3: Diagnóstico de la Pestaña "Simulador de Producción" (Dropdown de Productos Vacío)
Si al ingresar al simulador el menú desplegable de productos aparece vacío o no responde, sigue este flujo de diagnóstico en caliente:
1. Abre la pestaña **Consola** (`F12`) y verifica si hay algún error de JS (ej. `BASE_URL is not defined` o `initCalculadoraExcel is not defined`).
2. Abre la pestaña **Network** (Red) de tu navegador y recarga la página.
3. Busca la petición AJAX llamada `get_productos_base_ajax`. 
   * Si devuelve un estado HTTP `200`, haz clic en ella y ve a la pestaña "Response" (Respuesta) para ver si la base de datos está devolviendo productos clasificados como **"Fabricado"** o que tengan formulaciones activas.
   * Si devuelve error `403 (Forbidden)` o `500 (Internal Server Error)`, significa que hay un problema con el Token CSRF en la petición o en el controlador.

---

## 📋 Seguimiento y Próximos Pasos (Para el Nuevo Chat)

Cuando abras el nuevo chat para vaciar la memoria de contexto, indícale al nuevo agente de **Claude Sonnet** que retome el proyecto a partir de este archivo (`PRUEBAS_Y_SEGUIMIENTO.md`). Las tareas pendientes sugeridas son:

1. **Resolver la carga del dropdown de productos en el simulador:** (Si las pruebas del Paso 3 de arriba arrojaron un error de JS o de respuesta vacía en `get_productos_base_ajax`).
2. **Modularización de Modales:** Extraer los modales de `main.php` (ej. `modalProducto`, `modalFormulacion`, `modalHistorialFormulaciones`) a sub-vistas dentro de la carpeta `application/views/produccion/productos/modals/` para limpiar aún más la vista principal.
3. **Validación Estricta en Controladores:** Implementar la librería `form_validation` en los endpoints de guardado de productos y formulaciones para evitar inyecciones directas de variables post del cliente a la base de datos.
4. **Carga Manual tipo Excel:** Continuar puliendo la experiencia fluida de edición inline en la tabla del Simulador para que la carga manual de componentes de formulaciones se sienta exactamente igual a una hoja de cálculo.
