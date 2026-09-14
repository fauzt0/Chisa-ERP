# Plan de rediseño — Filtro "Buscar y filtrar productos" (`/produccion/productos`)

> **Rol de este documento:** especificación de diseño para que **otro agente (Cursor 2.5)** implemente el rediseño.
> Este archivo **no contiene** la implementación final; describe el problema, la propuesta visual y funcional, los tokens a reutilizar y los pasos concretos de implementación.
>
> **Autor:** agente diseñador (orquestación).
> **Ejecutor:** agente en otro chat (Cursor 2.5).
> **Alcance:** exclusivamente el panel de filtros de la pestaña **Catálogo** en `/produccion/productos`. No tocar el simulador, ni la tabla, ni el backend, ni el sistema de permisos.

---

## 1. Contexto y archivos afectados

| Elemento | Ruta | Qué contiene |
|---|---|---|
| Vista (markup del filtro) | `application/views/produccion/productos/main.php` | Panel de filtros en **líneas 140–191** (bloque `<!-- Filtros -->`). |
| Estilos + carga de JS | `application/views/produccion/productos/scripts.php` | CSS del filtro en **líneas 110–158** (`.panel-filtros`, `.panel-filtros-header`, labels, `.form-control/.form-select`, `.btn-chip-buscar`). |
| Controlador (no modificar) | `application/controllers/produccion/Productos.php` | `pageView = produccion/productos/main`, `pageScript = produccion/productos/scripts`. |
| Lógica de filtrado (JS, no romper contratos) | `assets/dist/js/produccion_productos.js` | Lee los IDs: `#buscarProductos`, `#filtroTipo`, `#filtroEstatus`, `#filtroStock`, `#btnLimpiarFiltrosProductos`, y los chips `.btn-chip-buscar[data-term]`. |

> ⚠️ **Contrato de IDs y clases (NO cambiar):** los siguientes selectores son consumidos por `produccion_productos.js`. Deben conservarse **exactamente**:
> `#buscarProductos`, `#filtroTipo`, `#filtroEstatus`, `#filtroStock`, `#btnLimpiarFiltrosProductos`, `.btn-chip-buscar` (con su atributo `data-term`).
> Se pueden reordenar, reestilizar y reagrupar en el DOM, pero **no renombrar ni eliminar**.

---

## 2. Estructura y estilos actuales (estado de partida)

### 2.1 Markup actual (resumen)

```
.row.mb-3.productos-filtros-panel
 └ .col-12
    └ .panel-filtros
       ├ .panel-filtros-header  → "🔍 Buscar y filtrar productos"
       └ .card-body
          └ .row.g-3.align-items-end
             ├ .col-lg-4.col-md-6  → label + #buscarProductos (input) + chips (.btn-chip-buscar)
             ├ .col-lg-2.col-md-6  → label + #filtroTipo    (select)
             ├ .col-lg-2.col-md-4  → label + #filtroEstatus (select)
             ├ .col-lg-2.col-md-4  → label + #filtroStock   (select)
             └ .col-lg-2.col-md-4  → #btnLimpiarFiltrosProductos (btn)
```

### 2.2 Tokens de color ya definidos (reutilizar, definidos en `scripts.php` sobre `.produccion-productos-page`)

```css
--prod-primary:      #1e40af;
--prod-primary-dark: #1e3a8a;
--prod-accent:       #059669;
--prod-accent-light: #d1fae5;
--prod-warn:         #d97706;
--prod-surface:      #ffffff;
--prod-bg:           #f1f5f9;
--prod-border:       #cbd5e1;
--prod-text:         #0f172a;
--prod-text-muted:   #475569;
--prod-radius:       10px;
--prod-shadow:       0 2px 8px rgba(15, 23, 42, 0.08);
```

---

## 3. Problemas detectados (por qué se ve "cerrado" y poco atractivo)

1. **Alturas de columna desiguales → huecos y desalineación.**
   La columna de búsqueda incluye los *chips* (`BASE ORGANICA BLANCA`, etc.) debajo del input, por lo que es mucho más alta que las columnas de selects. Con `align-items-end`, los selects se pegan al fondo y dejan un vacío arriba: sensación de "espacios muertos" y descuadre.

2. **Los `col-lg-2` son demasiado estrechos** para los selects con textos como "Descontinuado" o "Stock bajo"; el texto queda apretado contra el borde.

3. **Sin separación visual entre grupos.** Búsqueda (texto libre) y filtros (selects) se mezclan en una sola fila sin jerarquía. No se comunica "esto es buscar / esto es filtrar".

4. **Header tipo etiqueta plana.** `.panel-filtros-header` es una franja gris con texto en mayúsculas; no aporta jerarquía ni acción (por ejemplo, no muestra cuántos filtros están activos ni permite colapsar).

5. **Botón "Limpiar" siempre visible y con el mismo peso** que los filtros, aunque no haya nada que limpiar. Ocupa una columna completa y compite visualmente.

6. **Padding interno ajustado.** `.card-body` usa el padding por defecto de Bootstrap; combinado con `g-3` y labels en mayúsculas de 0.85rem, todo respira poco.

7. **Modo oscuro:** el panel usa colores claros fijos (`--prod-surface`, `--prod-bg`) sin variante para `data-bs-theme="dark"`, por lo que en tema oscuro puede verse un bloque blanco duro dentro de una página oscura (revisar contra `assets/dist/css/theme.css`).

8. **Falta de feedback en móvil.** En `col-md-4`/`col-md-6` los controles quedan en filas de 2–3 elementos muy juntos, sin respiro vertical.

---

## 4. Objetivo de diseño

Un panel de filtros **limpio, aireado, jerárquico y funcional**, que:

- Separe visualmente **Búsqueda** (input grande + chips) de **Filtros** (selects).
- Tenga **altura consistente** por control, sin huecos por desalineación.
- Sea **cómodo al tacto** (mín. 44px de alto en controles — ya existe).
- Muestre **estado de filtros activos** (contador / badges) y permita **limpiar** solo cuando aplique.
- Respete **tema claro y oscuro** con contraste WCAG-AA.
- Sea **totalmente responsive** (desktop, tablet, móvil) con reflujo ordenado.
- Reutilice los **tokens** y el estilo del resto de la página (bordes 2px, radios 10px, sombra suave).

---

## 5. Propuesta visual (layout objetivo)

### 5.1 Estructura propuesta (desktop ≥ lg)

```
┌────────────────────────────────────────────────────────────────────────────┐
│  🔍  Buscar y filtrar productos              [ 2 filtros activos ]  [Limpiar]│  ← header con contador + acción
├────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  BUSCAR PRODUCTO                                                             │
│  ┌────────────────────────────────────────────────────────────────────┐    │
│  │ 🔍  Ej: BASE ORGANICA BLANCA, TINTA NEGRA, CHISA GLASS...            │    │  ← input-group grande, icono a la izq.
│  └────────────────────────────────────────────────────────────────────┘    │
│  [BASE ORGANICA BLANCA] [TINTA NEGRA] [SOLUCION FASE ACUOSA] [CHISA GLASS]   │  ← chips
│                                                                              │
│  ── FILTROS ──────────────────────────────────────────────────────────────  │  ← separador sutil / label de grupo
│                                                                              │
│  TIPO                  ESTATUS                STOCK                          │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────┐              │
│  │ Todos      ▾ │     │ Todos      ▾ │     │ Todos      ▾ │              │
│  └───────────────┘     └───────────────┘     └───────────────┘              │
│                                                                              │
└────────────────────────────────────────────────────────────────────────────┘
```

Claves del layout:
- **Fila 1 (Búsqueda):** input a todo el ancho dentro de un `input-group` con icono de lupa (`.input-group-text` con `fa-search`). Debajo, los chips.
- **Separador de grupo:** una línea/etiqueta discreta "FILTROS" que separa la búsqueda de los selects.
- **Fila 2 (Filtros):** los tres selects en columnas **iguales y más anchas** (`col-lg-4` cada uno, o `col-md-6`/`col-sm-12`), ya **sin** mezclarse con la búsqueda → desaparecen los huecos por desalineación.
- **"Limpiar" se mueve al header**, alineado a la derecha, como acción secundaria (`btn-sm btn-outline-*`), y **solo se muestra/activa cuando hay algún filtro aplicado** (ver §7).

### 5.2 Grid responsive recomendado

| Breakpoint | Búsqueda | Selects (Tipo/Estatus/Stock) |
|---|---|---|
| `≥ lg` (desktop) | `col-12` (input full width) | `col-lg-4` cada uno (3 en una fila) |
| `md` (tablet) | `col-12` | `col-md-4` cada uno (siguen en fila, más anchos que hoy) |
| `< md` (móvil) | `col-12` | `col-12` apilados, con `gap` vertical cómodo |

> Ya **no** se mezcla el input de búsqueda en la misma `.row` que los selects; van en dos bloques separados. Esto elimina el problema raíz de alturas desiguales.

---

## 6. Especificaciones de estilo (tokens, espaciado, tipografía)

Aplicar todo dentro del scope existente `.produccion-productos-page .panel-filtros` para no filtrar estilos a otras vistas.

### 6.1 Contenedor del panel
- Fondo: `var(--prod-surface)`; borde `2px solid var(--prod-border)`; radio `var(--prod-radius)`; sombra `var(--prod-shadow)`. (Ya existe — conservar.)
- **Padding interno del cuerpo:** aumentar a `1.25rem 1.5rem` (desktop) y `1rem` (móvil). Hoy usa el default de `.card-body`.

### 6.2 Header (`.panel-filtros-header`)
- Convertir en `d-flex align-items-center justify-content-between`.
- Izquierda: icono `fa-filter` + título. Mantener mayúsculas/`letter-spacing` actuales.
- Derecha: `span` contador de filtros activos (`#contadorFiltrosProductos`, badge estilo *soft*: fondo `--prod-accent-light`, texto `--prod-primary-dark`) + botón **Limpiar** (`btn btn-sm btn-outline-secondary`).
- Mantener fondo `var(--prod-bg)` y borde inferior `2px solid var(--prod-border)`.

### 6.3 Búsqueda
- Envolver `#buscarProductos` en `.input-group` con `.input-group-text` (icono `fa-search`).
- Altura mín. 46–48px; fuente `1.05rem` (ya existe la regla para `#buscarProductos`).
- **Chips:** conservar `.btn-chip-buscar` (radio 20px, `btn-sm`, `btn-outline-primary`). Añadir `mt-2`, `gap-2`, y `flex-wrap`. Considerar un micro-label "Sugerencias:" en `text-muted` `small` antes de los chips.

### 6.4 Separador de grupo "FILTROS"
- Un `div` con línea (`border-top: 1px dashed var(--prod-border)`) y una etiqueta pequeña superpuesta o un simple `<span>` en `--prod-text-muted`, mayúsculas, `0.75rem`, `letter-spacing: 0.08em`, con `margin: 1rem 0 0.75rem`.

### 6.5 Selects
- `min-height: 44px`, borde `2px solid var(--prod-border)`, radio `8px`, `font-size: 1rem` (ya existe — conservar).
- **Labels** de cada select: mantener estilo (`600`, `0.85rem`, uppercase, `--prod-text-muted`), con `margin-bottom: 0.4rem`.
- Ancho: pasar de `col-lg-2` a `col-lg-4` para que respiren.
- Estado *focus*: conservar `box-shadow: 0 0 0 3px rgba(30,64,175,.2)` y `border-color: var(--prod-primary)`.
- **Estado "activo"** (opcional pero recomendado): cuando un select tiene valor ≠ "", resaltar su borde con `var(--prod-primary)` para que se note qué filtros están aplicados (clase `.is-filtro-activo`).

### 6.6 Espaciado general
- Gap entre bloques (búsqueda ↔ separador ↔ selects): usar `row g-3` internamente y márgenes verticales de `0.75rem–1rem`.
- En móvil, `g-3` es suficiente; verificar que no haya controles pegados.

---

## 7. Comportamiento funcional a añadir (JS ligero)

> El JS puede añadirse en el bloque `<script>` de `scripts.php` o en `produccion_productos.js`. **No** cambiar los IDs existentes ni la lógica de filtrado de DataTables; solo **añadir** los comportamientos de UI descritos.

1. **Contador de filtros activos**
   - Recalcular al cambiar `#filtroTipo`, `#filtroEstatus`, `#filtroStock` o al escribir en `#buscarProductos`.
   - Contar cuántos tienen valor no vacío → escribir en `#contadorFiltrosProductos`.
   - Texto: `"N filtro(s) activo(s)"`; ocultar el badge cuando `N === 0`.

2. **Botón "Limpiar" contextual**
   - Deshabilitado (o `d-none`) cuando `N === 0`.
   - Al hacer clic (comportamiento actual): limpiar búsqueda + resetear los tres selects a `""` + re-disparar el filtrado + recalcular contador. **Conservar el handler existente** de `#btnLimpiarFiltrosProductos`; solo añadir el reset del contador y de las clases `.is-filtro-activo`.

3. **Resalte de select activo**
   - Añadir/quitar la clase `.is-filtro-activo` según el `value` del select en cada `change`.

4. **Sin cambios de contrato:** los eventos que hoy disparan el filtrado de la tabla deben seguir disparándose igual (no cambiar nombres de listeners ni IDs).

---

## 8. Modo oscuro (obligatorio revisar)

El panel debe verse integrado en `html[data-bs-theme="dark"]`. Dos opciones:

- **Opción A (preferida):** añadir overrides en `scripts.php` dentro del scope, p. ej.:
  ```css
  html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros { background: var(--bs-secondary-bg); border-color: var(--bs-border-color); }
  html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros-header { background: var(--bs-tertiary-bg); color: var(--bs-body-color); border-bottom-color: var(--bs-border-color); }
  html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros label { color: var(--bs-secondary-color); }
  ```
  Usar las variables de Bootstrap 5.3 (`--bs-secondary-bg`, `--bs-tertiary-bg`, `--bs-body-color`, `--bs-border-color`, `--bs-secondary-color`) para heredar el tema.
- **Opción B:** redefinir los tokens `--prod-*` bajo `html[data-bs-theme="dark"] .produccion-productos-page { ... }`.

> Verificar contraste del badge contador y de los chips en oscuro; ajustar `--prod-accent-light` si queda demasiado claro sobre fondo oscuro.

---

## 9. Accesibilidad

- Cada control conserva su `<label for="...">` asociado (ya existe). Mantener.
- El `input-group-text` con la lupa es decorativo → `aria-hidden="true"` en el icono.
- El badge contador: añadir `aria-live="polite"` para anunciar cambios.
- Contraste texto/fondo ≥ 4.5:1 en ambos temas.
- Foco visible en input, selects, chips y botón (ya hay `box-shadow` de focus; no eliminarlo).

---

## 10. Criterios de aceptación (checklist para el ejecutor)

- [ ] La búsqueda ocupa su propia fila (input-group con icono) y los chips van debajo, sin desalinear los selects.
- [ ] Los tres selects están en columnas iguales y más anchas (`col-lg-4`), sin huecos verticales.
- [ ] Header muestra título + contador de filtros activos + botón "Limpiar" a la derecha.
- [ ] "Limpiar" solo se activa cuando hay ≥ 1 filtro aplicado y resetea todo correctamente.
- [ ] El filtrado de la tabla sigue funcionando exactamente igual (IDs/clases intactos).
- [ ] Los selects con valor muestran resalte `.is-filtro-activo`.
- [ ] Se ve correcto y con buen contraste en **tema claro y oscuro**.
- [ ] Responsive correcto en desktop, tablet y móvil (sin controles pegados).
- [ ] Padding y espaciado más aireados; nada "cerrado".
- [ ] No se modificó el controlador, ni el simulador, ni la tabla, ni el backend, ni permisos.

---

## 11. Restricciones (NO hacer)

- ❌ No renombrar ni eliminar `#buscarProductos`, `#filtroTipo`, `#filtroEstatus`, `#filtroStock`, `#btnLimpiarFiltrosProductos`, `.btn-chip-buscar` / `data-term`.
- ❌ No tocar `application/config/database.php` ni scripts de entorno (`.cursor/*.sh`).
- ❌ No modificar la pestaña "Simulador de Producción" ni la tabla `#tablaProductos`.
- ❌ No cambiar la lógica de negocio ni las consultas del controlador/modelo.
- ❌ No romper definiciones de permisos existentes.
- ✅ Solo CSS/markup del panel de filtros + JS ligero de UI (contador, limpiar contextual, resalte).

---

## 12. Resumen para el ejecutor (Cursor 2.5)

1. En `main.php` (líneas ~140–191): reestructurar el bloque de filtros en **header (título + contador + limpiar)** y **cuerpo con dos secciones**: (a) búsqueda `input-group` + chips, (b) separador "FILTROS" + 3 selects en `col-lg-4`. Conservar IDs/clases del contrato.
2. En `scripts.php` (CSS, líneas ~110–158): ampliar padding, estilizar header flex, input-group, separador, badge contador, estado `.is-filtro-activo`, y **añadir overrides de modo oscuro**.
3. Añadir JS ligero (en `scripts.php` o `produccion_productos.js`): contador de filtros activos, "Limpiar" contextual, resalte de selects activos. Sin alterar la lógica de filtrado existente.
4. Probar en claro/oscuro y responsive; validar checklist §10.
