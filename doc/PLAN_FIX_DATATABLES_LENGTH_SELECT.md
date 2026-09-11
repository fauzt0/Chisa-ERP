# Pendiente — Fix "Mostrar X entradas": número encimado con la flecha del select

> **Estado:** investigación completada, **corrección aún NO aplicada** (se detuvo por límite de créditos).
> **Rutas afectadas:** `/compras/proveedores` y `/compras/OrdenesCompra` (y, por consistencia, cualquier tabla DataTables del sistema).
> **Modo:** económico. Este documento deja todo listo para que otro agente aplique el fix en una sola pasada.

---

## 1. Síntoma
En las tablas de proveedores y órdenes de compra, el control de DataTables "Mostrar X registros / entries per page" muestra el **número encimado con la flecha** del `<select>` (el valor y el ícono ▾ se solapan).

---

## 2. Causa raíz (confirmada por reproducción con los assets reales)
Se renderizó una tabla usando el `app.js` y `app.css` reales en Chrome headless y se volcó el DOM. DataTables 2.x genera el control así (incluso con `dom:'lrtip'`):

```html
<div class="dt-length">
  <select name="t_length" class="form-select form-select-sm" id="dt-length-0">…</select>
  <label for="dt-length-0"> entries per page</label>
</div>
```

Hallazgos clave:

1. **El contenedor real es `.dt-length`** (DataTables 2.x), **no** el legacy `.dataTables_length`.
2. **El `<select>` es `.form-select .form-select-sm`**, que trae flecha SVG de fondo (`background-position:right .7rem center`, `background-size:16px 12px`) y `padding-right:2.1rem` heredado de `.form-select`.
3. Además, `app.css` fuerza `div.dt-length select { width:auto }`, por lo que el select se **encoge al contenido**; si algún estilo reduce el `padding-right` (por ejemplo un `padding` shorthand sobre `.form-select-sm`) el número queda debajo de la flecha.

### Por qué el "fix" actual NO funciona
En `application/views/compras/proveedores/main.php` (aprox. **líneas 1014–1021**) existe:

```css
/* Fix: evitar que la flecha del select "mostrar X entradas" se encime con el número */
.dataTables_length select {
  padding-right: 2rem !important;
  background-position: right 0.5rem center !important;
}
```

Ese selector apunta a `.dataTables_length` (clase **legacy** que DataTables 2.x ya **no** emite). Como el contenedor real es `.dt-length`, **la regla nunca se aplica** → código muerto. En `ordenes_compra/main.php` **no hay ninguna regla**.

---

## 3. Corrección propuesta (aplicar tal cual)

### Opción A (recomendada, DRY y global)
Agregar **una sola** regla en `assets/dist/css/theme.css` (archivo global ya cargado en todo el sistema) para corregir el control en **todas** las tablas de una vez. Colocarla al final del archivo:

```css
/* -----------------------------------------------------------------------------
   DataTables — control "Mostrar X registros": evitar que el número se encime
   con la flecha del <select>. DataTables 2.x usa .dt-length y clase
   .form-select.form-select-sm (con flecha SVG). Se garantiza espacio a la
   derecha, posición de la flecha y un ancho mínimo cómodo.
   Se incluye el selector legacy .dataTables_length por compatibilidad.
   --------------------------------------------------------------------------- */
div.dt-length select,
div.dataTables_length select,
div.dt-container .dt-length select.form-select,
div.dt-container .dataTables_length select.form-select {
  padding-right: 2.25rem !important;
  background-position: right 0.7rem center !important;
  min-width: 5.25rem;      /* deja lugar cómodo para "100" + flecha */
  width: auto;
}
```

> Nota: `theme.css` fue creado en tareas previas para centralizar ajustes de contraste/tema; añadir aquí este fix es coherente y afecta a todas las tablas sin duplicar CSS.

### Opción B (si se prefiere alcance acotado a las 2 rutas)
Reemplazar en `application/views/compras/proveedores/main.php` el bloque muerto (líneas ~1014–1021) por:

```css
/* Fix: "Mostrar X registros" — el número se encimaba con la flecha del select.
   DataTables 2.x usa .dt-length + .form-select-sm. */
div.dt-length select {
  padding-right: 2.25rem !important;
  background-position: right 0.7rem center !important;
  min-width: 5.25rem;
  width: auto;
}
```

Y **añadir ese mismo bloque `<style>`** en `application/views/compras/ordenes_compra/main.php` (que hoy no tiene ninguna regla). Buscar un `<style>` existente en la vista o crear uno cerca del final del archivo, antes del `<script>` de inicialización.

---

## 4. Archivos involucrados
| Archivo | Acción |
|---|---|
| `assets/dist/css/theme.css` | **Opción A:** agregar la regla global al final. |
| `application/views/compras/proveedores/main.php` | **Opción B:** reemplazar bloque muerto `.dataTables_length select` (líneas ~1014–1021). Si se usa la Opción A, **eliminar** ese bloque muerto para no dejar código sin efecto. |
| `application/views/compras/ordenes_compra/main.php` | **Opción B:** añadir el bloque `<style>` (init de DataTables en línea ~1080, `pageLength:25`). |

> Recomendación: **Opción A** + eliminar el bloque muerto de proveedores.

---

## 5. Contexto técnico verificado (para no re-investigar)
- DataTables presente: **2.x** (bundled dentro de `assets/dist/js/app.js`; jQuery también expuesto: `typeof jQuery === 'function'`, `jQuery.fn.DataTable` disponible).
- `app.css` (AppStack Bootstrap 5.3) define:
  - `.form-select { padding:.25rem 2.1rem .25rem .7rem; background-position:right .7rem center; background-size:16px 12px; }`
  - `.form-select-sm { padding-top:.15rem; padding-bottom:.15rem; padding-left:.5rem; }` (no resetea `padding-right`).
  - `div.dt-length select { display:inline-block; margin-right:.5em; width:auto; }`
- **No** existe ninguna regla `select.dt-input` ni overrides de `.form-select` en `estilos.css`, `demo-presentacion.css` ni `theme.css` (verificado por grep). Es decir, el fix no colisiona con reglas existentes.
- Config DataTables:
  - `proveedores`: `dom:'lrtip'`, tabla `#tablaProveedores`.
  - `ordenes_compra`: layout por defecto (sin `dom`), `pageLength:25`, idioma es-MX vía CDN, tabla `#tablaOrdenes`.

---

## 6. Cómo verificar (económico, sin levantar la app/BD)
Reproducir con Chrome headless y los assets reales:

1. Crear un HTML que enlace `app.css`, `estilos.css`, `demo-presentacion.css`, `theme.css` y `app.js`, con una `<table id="t">` mínima.
2. Inicializar: `jQuery(function(){ jQuery('#t').DataTable({ dom:'lrtip', pageLength:100, lengthMenu:[10,25,50,100] }); });`
3. Tomar screenshot:
   `google-chrome --headless=new --no-sandbox --user-data-dir=<tmp> --allow-file-access-from-files --virtual-time-budget=3500 --window-size=520,150 --screenshot=out.png "file://.../harness.html"`
4. Confirmar que "100" y la flecha ▾ quedan **separados** (probar tema claro y `data-bs-theme="dark"`).
5. Volcar el DOM con `--dump-dom` para confirmar que el select mantiene `.form-select.form-select-sm` dentro de `.dt-length`.

> En el harness con los assets **actuales** el control se ve correcto; el fix añade margen de seguridad (`padding-right`/`min-width`) para el entorno real donde el número se solapa. Tras aplicar el fix, repetir el screenshot para dejar evidencia antes/después.

---

## 7. Criterios de aceptación
- [ ] En `/compras/proveedores` y `/compras/OrdenesCompra`, el número del selector "Mostrar X registros" **no** se encima con la flecha (tema claro y oscuro).
- [ ] Se eliminó el bloque muerto `.dataTables_length select` de `proveedores/main.php` (o se reemplazó por `.dt-length select`).
- [ ] `ordenes_compra` quedó cubierto (por la regla global de `theme.css` o por su propio `<style>`).
- [ ] No se alteró la lógica de DataTables, ni backend, ni permisos; solo CSS.
- [ ] Verificación con screenshot antes/después (headless, sin levantar la app).

---

## 8. Restricciones
- Solo **CSS**. No tocar `app.js`/`app.css` (assets vendor), ni `application/config/database.php`, ni scripts `.cursor/*.sh`, ni permisos.
- No cambiar la configuración de inicialización de las tablas (`dom`, `columns`, `ajax`, etc.).
- Trabajar en la rama `cursor/cloud-agent-env`. No abrir PR (el admin fusionará luego).
