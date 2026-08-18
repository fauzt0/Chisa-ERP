# SEGUIMIENTO — Contraste de badges, tablas y tema (actualizado 18 ago 2026)

> **Para:** Agente o revisor que retome contraste / apariencia del ERP.
> **Estado (18-ago-2026):** Frente RH de badges **validado**. Estilos **globales** de contraste + DataTables Responsive aplicados en working tree (**sin commit**).
> **Entorno:** PRODUCCIÓN — `https://erp.chisarecubrimientos.com.mx` · PHP 8.2 · CodeIgniter 3 + Bootstrap 5.3.
> **Login demo:** `presentacion@chisa.mx` / `Demo2026!`
> **Repo:** `public_html/` · rama `feature/rh-nominas-iteracion2`.

---

## 1. Validación del cierre RH (los 4 archivos del doc original)

Patrón RH: `bg-*-subtle text-*-emphasis border border-*-subtle` (WCAG AA, se adapta a dark mode).

### 1.1 Rechequeo 18-ago-2026

| Archivo | Resultado |
|:--------|:----------|
| `application/controllers/rh/Nomina.php` | Sin badges sólidos. OK. |
| `application/controllers/rh/RecursosHumanos.php` | 7 badges PHP en patrón subtle. OK. |
| `application/views/rh/nomina/main.php` | Stepper, estatus, detalle, planeador, notas: subtle. OK. |
| `application/views/rh/empleados/main_empleados.php` | Faltantes, RFC/CURP/NSS, incidencias, calendario, “Ocupado”: subtle. OK. (línea “Ocupado” ahora ~3067, no 3038) |

### 1.2 Huecos que el doc original no cubría (ahora cubiertos por CSS global)

Siguen existiendo clases sólidas en markup de Reloj, Departamentos, comunicación y contratos (`badge bg-success`, etc.). **No se cambió la lógica PHP/JS**: el CSS de `#erp-main-content` las pinta como subtle en claro y oscuro.

### 1.3 Validación visual (navegador, 18-ago-2026)

| Prueba | Claro | Oscuro |
|:-------|:------|:-------|
| Badges de nómina (Borrador → Calculada → Pagada/Cancelada) | Legibles | Legibles (subtle) |
| Modal **Registrar Nueva Incidencia** (referencia) | Labels ~8:1 | Labels ~19:1, inputs ~7:1 |
| Modal Vacaciones (`bg-light` interno) | OK | Cabeceras ~9:1 |
| Tabla empleados / historial de nóminas (flecha ▶ Responsive) | OK | OK (collapsed + child row) |
| Toggle luna/sol | Funciona (`theme-toggle.js`) | Funciona |

Planeador: no se reabrió en esta pasada; los badges del planeador ya usan el patrón subtle en el HTML.

---

## 2. Estilos globales (todos los módulos) — solo apariencia

Sin cambiar endpoints, filtros, pagos ni DataTables ajax. Solo CSS + defaults de DataTables.

| Qué | Dónde | Efecto |
|:----|:------|:-------|
| Badges de estatus (success/danger/warning/info/primary/secondary/dark) | `assets/dist/css/estilos.css` → `#erp-main-content .badge.bg-*` | Mismo contraste AA que RH |
| `bg-light`, `text-dark` en cards/tablas, `table-light` | mismo archivo, solo `html[data-bs-theme="dark"]` | Evita negro-sobre-negro |
| Modales (cuerpo, labels, inputs, `bg-light`) | `views/rh/partials/modal_styles.php` (layout global) | Como incidencia, claro/oscuro |
| DataTables: `responsive: true` + `autoWidth: false` por defecto | `assets/dist/js/rh-tables-responsive.js` | Flecha ▶ si la tabla no cabe |
| Wrapper `.table-responsive` + DataTable | `estilos.css` (`overflow: visible`) | El plugin puede colapsar columnas |

**No se toca:** campana, sidebar, toasts (fuera de `#erp-main-content`); recibos/contratos en “papel blanco”; calendarios `.cal-month-table`; desglose de nómina / modal de pago (spreadsheet / filas anidadas).

Módulos que heredan esto sin editar su JS de negocio: Compras, Ventas, Almacén, Contabilidad, Producción, Usuarios, Obras, Dashboards, Reloj, etc.

Recargar con **Ctrl+F5** tras desplegar CSS/JS (llevan `?v=time()`).

---

## 3. Pendientes (no bloquean el contraste)

### 3.1 Checklists de nómina (funcional, no de color)
- Excel/PDF, 375px, incidencias A2–A5 según `doc/CHECKLIST_PRUEBA_GENERAL_NOMINA_2026-08-17.md`.
- **NOM000038** (Parcial de prueba): ya **se puede cancelar** (cancelación de cabecera/empleado implementada). Cancelarla si se quiere limpiar el dato residual.

### 3.2 Commit (solo si el usuario lo pide)
No hacer commit ni push sin confirmación. Agrupar por tema, por ejemplo:
1. RH: badges + responsive tablas + cancelación (si aplica).
2. Global: `estilos.css` + `rh-tables-responsive.js` + `modal_styles.php`.

---

## 4. Referencias

| Documento | Contenido |
|:--|:--|
| `doc/CHECKLIST_PRUEBA_GENERAL_NOMINA_2026-08-17.md` | Pruebas manuales nómina (13 bloques A–M) |
| `doc/CHECKLIST_MANUAL_POST_E2E_NOMINA.md` | Post-E2E (A3 = contraste badges) |
| `doc/HANDOFF_NOMINA_CIERRE_H1_CONTRASTE_2026-08-17.md` | Handoff H1 / E2E |
| `assets/dist/css/estilos.css` | Badges + tablas Responsive + dark surfaces |
| `assets/dist/js/rh-tables-responsive.js` | Defaults DataTables + recalc en modal/tab |
| `application/views/rh/partials/modal_styles.php` | Contraste de modales |

## 5. Restricciones vigentes

- **PRODUCCIÓN:** no seeders destructivos; no pagar nóminas reales.
- No exponer credenciales en reportes ni commits.
- No commit/push sin confirmación del usuario.
- Cambios de este frente: **solo apariencia** (no alterar cálculos, permisos ni flujos).

---

*ERP Chisa Recubrimientos — Contraste WCAG AA en RH validado; estilos globales de badges/tablas/tema aplicados 18 ago 2026. Working tree, sin commit.*
