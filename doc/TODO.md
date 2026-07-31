# TODO - Sistema ERP CHISA

## 📝 Notas Técnicas
- [ ] Validar límite de `max_input_vars` en PHP para formularios con muchos checkboxes. (IMPORTANTE: Esto se debe realizar en cada deployment antes de desplegar a producción)


## 🟡 Estatus del proyectos
- [X] Desarrollo
- [ ] Pruebas
- [ ] Despliegue

## Pruebas 
- [ ] verificar envio de correos a los clientes de ordenes de ventas
- [ ] verificar envío de correos a los proveedores de las ordenes de compra en módulo de proveedores
- [ ] verificar envío de correos de facturas a los clientes en módulo de facturación
- [ ] verificación de módulo de proveedores
- [ ] verificación de módulo de producción
- [ ] verificación de módulo de obras
- [ ] verificación de módulo de facturación
- [ ] verificación de módulo de usuarios
- [ ] verificación de módulo de permisos
- [ ] verificación de módulo de bitácora
- [ ] verificación de módulo de dashboard
- [ ] verificación de módulo de recursos humanos
- [ ] verificación de módulo de reloj checador
- [ ] verificación de módulo de citas
- [ ] verificación de módulo de calendario



## 🟡 Iteración módulo de "Proveedores"
- [x] Verificar en insumos la actualzación de stock, mejorar el proceso. Tomar en cuenta que en el módulo de producción también se actualiza el stock de insumos, ya que en la producción de los productos se utilizan insumos y estos se actualizan en ese momento.
- [x] Crear alertas de stock mínimo, determinar donde se muestran estas alertas y verificar que se tengan estas alertas en el sistema de notificaciones, para los usuarios con permisos al área de proveedores, producción, almacén y administración. Estas alertas también se pueden agregar en el dashboard de inicio para los usuarios que tengan permisos al área de proveedores, producción, almacén y administración.
- [x] Verificar y mejorar el proceso de ordenes de compra.
- [x] Verificar que las ordenes de compra se puedan consultar correctamente, que la información de la orden se vea en PDF y se pueda descargar y enviar por correo electrónico al email del proveedor registrado. Debido a que se esta en un entorno de producción, no se pueden hacer pruebas de envío de emails, por lo que es necesario agregar la comprobación de emails en las pruebas locales.


## 🟡 Iteración módulo de "Producción"
- [ ] Mejorar y cuadrar los procesos de producción a los procesos actuales. Los productos tienen una formulación y se fabrican en lotes de cubetas, por lo q ue se debe tener un control de inventario de materias primas y productos terminados (por kilo, litro, etc). El flujo de trabajo se especifica en el archivo produccion.md

## 🟡 Iteración módulo de "Obra"



## 🔴 Urgente / Crítico

## 🟡 Pendientes Facturación
- [x] Conexión API Facture App (Implementado)
- [ ] Implementar Automatización de Importación (Cron Job / Lazy Load)
- [ ] Vincular Facturas a Obras/Ordenes de Compra
- [/] Implementar envío de factura (PDF y XML) por correo electrónico directamente desde el ERP.
  
## 🐛 Bugs Conocidos

- [X] Verificar que `insert_batch()` funcione correctamente con tabla `privilege`
- [X] Validar que todos los permisos se inserten correctamente en la base de datos

## 💡 Mejoras Futuras (Esperar a que se termine la primera iteración)
- [ ] Agregar botón de "Exportar a Excel" en todas las tablas
- [ ] Agregar logs de cambios y acciones realizadas en bitácora, de todas las secciones del sistema
- [ ] Revisar los logs de la bitácora en cada una de las secciones del sistema
- [ ] Optimizar consultas de permisos usando caché de CodeIgniter
- [ ] Agregar validación de permisos en todas las secciones del sistema
- [ ] Agregar el módulo de conexión  con el reloj checador.
- [ ] En recursos humanos hacer una vinculación al módulo de reloj checador. 
- [ ] Agregar un módulo para recordatorios de cumpleaños. Cuando sea el cumpleaños del usuario o de algún trabajador, agregar la notificación en el sistema y al trabajador o usuario, mostrarle una pantalla de felicitación
- [ ] Agregar un nuevo permiso en la sección "administrador", que sea "super administrador", el cual servirá para validaciones especiales como por ejemplo, editar datos fiscales de trabajadores o permisos bloqueados en general, por ejemplo, si un administrador requiere editar estos datos, saldrá una alerta para que el "super administrador" pueda validar la acción o bien ingresar directamente la contraseña del "super administrador".
- [ ] Agregar un módulo para recordatorios de citas. 
- [ ] Agregar un calendario en el CRM
- [ ] Convertir el array ViewData en un objeto (DTO). Instrucciones en el archivo "DTO.md"
- [ ] Mejorar botónes en las tablas de resultados y mejorar color de textos en alertas con fondos de colores y modals (se pierden las letras con color negro y fondos de color) 
- [ ] Agregar módulo para cargar logo del sistema, el cual se usará en todos los pdf, tickets, recibos, etc.
- [ ] Mejorar contrato de usuario, homogeneizarlo con los pdf que genera el sistema.
- [ ] Sweet alerts y notify shows funcionan, pero algunas alertas como warning, presentan contrastes de colores extraños o de poco contraste que dificultan la visión. Es necesario verificar y corregir

---

## 📅 Plan: Planeador Mensual de Nóminas (30 jul 2026)

> **Estado:** Planificado — Documento completo en `doc/PLAN_PLANEADOR_NOMINA.md`
> **Resumen:** Modal con calendario mensual que muestra periodos cubiertos/descubiertos, estatus y folio de cada nómina. Incluye creación de nóminas desde el panel. Backend (endpoint + modelo) + Frontend (HTML + JS + CSS) completamente especificado.

---

## 🎨 Plan: Optimización Global de Visibilidad, Contraste y Responsive (post-Iteración Nómina)

> **Fecha del plan:** 29 jul 2026
> **Prioridad:** Alta (afecta todos los módulos)
> **Estado:** Planificado — NO ejecutar hasta terminar iteración `/rh/Nomina`
> **Referencia visual:** El offcanvas de `/rh/RecursosHumanos` tiene excelente contraste (header gradiente oscuro + texto blanco + cuerpo limpio). Ese es el estándar deseado para todo el ERP.

### 1. Reparar botón de Tema Oscuro 🌓

| # | Tarea | Archivo(s) | Notas |
|:--|:------|:-----------|:------|
| 1.1 | Agregar handler JS para `.js-theme-toggle` en navbar | `topNavbar.php` o nuevo `theme-toggle.js` | El botón sol/luna existe pero no funciona. Debe alternar `data-bs-theme` entre `"light"` y `"dark"` en `<html>` y persistir en `localStorage` (`appstack-config-theme`) |
| 1.2 | Verificar que `app.css` tiene variables CSS para `[data-bs-theme="dark"]` | `assets/dist/css/app.css` | Bootstrap 5.3 incluye dark mode nativo. Confirmar que las variables `--bs-body-bg`, `--bs-body-color`, etc. cambian correctamente |
| 1.3 | Hacer que el toggle respete el estado guardado al cargar la página | `settings.js` o nuevo script | Leer `localStorage` en `DOMContentLoaded` y aplicar `data-bs-theme` antes del primer render para evitar flash |

### 2. Corrección de Contraste en Modales y Tablas

| # | Tarea | Archivo(s) | Notas |
|:--|:------|:-----------|:------|
| 2.1 | Reemplazar colores hardcodeados por variables CSS Bootstrap | `main.php` (Nomina), `main_empleados.php`, y otras vistas | Cambiar `#1e3a5f`, `#2d5a8e`, `#fffef5`, etc. por `var(--bs-primary)`, `var(--bs-dark)`, `var(--bs-warning-bg-subtle)` para que respondan al tema |
| 2.2 | Suavizar celdas `bg-success`/`bg-danger`/`bg-primary` con texto blanco | `main.php` (tabla detalle nómina) | Usar variantes `-subtle` con texto oscuro (ej. `bg-success-subtle text-success-emphasis`) en lugar de fondo sólido + blanco. Esto mejora legibilidad y reduce fatiga visual |
| 2.3 | Revisar `table-dark` — asegurar que en dark mode no se vuelva doble-oscuro | `main.php` | `table-dark` usa `#212529`. En dark mode puede confundirse con el fondo. Evaluar cambiar a `table-secondary` o usar variables |
| 2.4 | Mejorar contraste de `.bg-light` en tema oscuro | `main.php`, vistas varias | Bootstrap 5.3 mapea `bg-light` a un gris oscuro en dark mode. Verificar que el texto (`text-dark` o default) contraste adecuadamente |
| 2.5 | Aumentar `font-weight` en labels de formularios y encabezados de columna | Vistas de todos los módulos | `fw-semibold` o `fw-bold` donde aplique, para mejorar legibilidad en pantallas de baja resolución |

### 3. Responsive / Móvil en Todos los Módulos

| # | Tarea | Archivo(s) | Notas |
|:--|:------|:-----------|:------|
| 3.1 | Asegurar `table-responsive` en TODAS las tablas del sistema | Todas las vistas con DataTables | Envolver cada `<table>` en `<div class="table-responsive">` para scroll horizontal en móvil |
| 3.2 | Ajustar filtros DataTables para no encimarse en ≤767px | CSS global o `estilos.css` | Generalizar el fix que ya existe en `main.php` (líneas 1933-1947) a un archivo CSS compartido |
| 3.3 | Modales: usar `modal-fullscreen-{breakpoint}-down` consistentemente | Vistas con modales de detalle | Por ejemplo `modal-fullscreen-md-down` para que en tablet/móvil el modal ocupe toda la pantalla |
| 3.4 | Reducir `min-width` de tablas muy anchas en móvil | `main.php` (tabla detalle) | La tabla de detalle tiene `min-width: 2200px`. Evaluar columnas colapsables o scroll horizontal con primeras columnas fijas (`position: sticky`) |
| 3.5 | Botones de acción en tablas: asegurar que no se desborden | CSS global | Usar `btn-group` con `flex-wrap` o `text-nowrap` donde aplique. En móvil, considerar dropdown "Acciones" en lugar de botones individuales |
| 3.6 | Stats cards responsivas | `main.php` y dashboards | De `col-md-3` a `col-6 col-md-3` para que en móvil se muestren 2 por fila en lugar de 1 |

### 4. Estandarización de Estilos entre Módulos

| # | Tarea | Archivo(s) | Notas |
|:--|:------|:-----------|:------|
| 4.1 | Unificar headers de modales/offcanvas: mismo gradiente, misma tipografía | `modal_styles.php` y vistas | El gradiente `#1e3a5f → #2d5a8e` ya está en el partial `modal_styles.php`. Asegurar que todos los módulos lo usen consistentemente |
| 4.2 | Crear partial `tabla_estilos.php` con reglas compartidas para DataTables | `application/views/rh/partials/` o `application/views/partials/` | Mover estilos repetidos de filtros, selectores, y responsive de DataTables a un solo archivo |
| 4.3 | Revisar SweetAlert2 y toast: asegurar contraste en ambos temas | `estilos.css` y handlers JS | Los toast ya tienen colores definidos. Verificar que en dark mode los Swal no hereden fondos incorrectos |

### 5. Pruebas Cruzadas

| # | Tarea | Notas |
|:--|:------|:------|
| 5.1 | Probar cada módulo en tema claro y oscuro | Navegar todos los módulos alternando el tema y verificar legibilidad |
| 5.2 | Probar en viewport 375px (iPhone SE), 768px (iPad), 1440px (escritorio) | Usar DevTools responsive mode |
| 5.3 | Medir contraste con herramienta de accesibilidad | WCAG AA requiere ratio ≥ 4.5:1 para texto normal, ≥ 3:1 para texto grande |

---

### Módulos involucrados (afectados por el plan)

| Módulo | Ruta | Contraste | Dark Theme | Responsive |
|:-------|:-----|:---------|:-----------|:-----------|
| Nómina | `/rh/Nomina` | ⚠️ Celdas color sólido | ❌ Hardcodeado | ⚠️ Mínimo |
| RH Empleados | `/rh/RecursosHumanos` | ✅ Offcanvas OK | ❌ Tabla principal | ⚠️ |
| Dashboard | `/dashboard` | ⚠️ Cards | ❌ | ⚠️ |
| Proveedores | `/proveedores` | ⚠️ | ❌ | ⚠️ |
| Producción | `/produccion` | ⚠️ | ❌ | ⚠️ |
| Obras | `/obras` | ⚠️ | ❌ | ⚠️ |
| Facturación | `/facturacion` | ⚠️ | ❌ | ⚠️ |
| Usuarios | `/usuarios` | ⚠️ | ❌ | ⚠️ |
| CRM | `/crm` | ⚠️ | ❌ | ⚠️ |
| Reloj | `/reloj` | ⚠️ | ❌ | ⚠️ |

> **Leyenda:** ✅ Buen estado | ⚠️ Necesita mejora | ❌ No implementado / Roto



## ✅ Completado (Última sesión: 2025-12-27)
- [X] **Implementar POST-Redirect-GET en formulario de alta de usuarios**
  - Mostrar mensaje flash en vista `alta.php`
  - Prevenir reenvío de formulario al presionar F5
  - Archivo: `application/controllers/usuarios/GestionUsuarios.php` (línea ~100)
  - Archivo: `application/views/usuarios/alta.php` (después de línea 122)
- [X] **Estandarización de Nómina y Campos Fiscales MX**
  - Igualada la vista `editar.php` con `alta.php` (sección Pensión Alimenticia y Nómina).
  - Agregados campos en BD y Vistas: `isr_porcentaje`, `imss_cuota`, `infonavit_aportacion`, `afore_aportacion`.
  - Corregido layout de selector `tipo_nomina` en Alta.

## ✅ Completado (Última sesión: 2026-01-21)

- [x] Corregir indentación en `sidebar.php` (2 espacios)
- [x] Corregir indentación en `alta.php` (2 espacios)
- [x] Crear sistema de permisos dinámico basado en `config/permissions.php`
- [x] Implementar loop dinámico para generar checkboxes de permisos
- [x] Agregar botón "Seleccionar todos / Deseleccionar todos" para permisos
- [x] Cambiar estructura de inserción de permisos a `insert_batch()` (una fila por permiso)
- [x] Cargar archivo de configuración `permissions.php` en constructor del controlador
- [x] Agregar estilos CSS personalizados para sidebar (`estilos.css`)
- [x] Crear modal "Add Customer" en `main.php` (comentado para uso futuro)
- [x] Envolver checkboxes en `<label>` para mejorar UX (clickeable)
- [X] **Convertir campo "Departamento" en select con roles predefinidos**
  - Cambiar input text a `<select>` con opciones: Administrador, Vendedor, Almacén, Contador, etc.
  - Crear archivo de configuración `config/roles.php` con permisos predefinidos por rol
  - Agregar JavaScript para marcar/desmarcar permisos automáticamente al cambiar el select
  - Permitir personalización manual de permisos después de seleccionar rol  

## ✅ Completado (Última sesión: 2026-01-23)
- [x] Este módulo permitirá gestionar el área de recursos humanos de la empresa, por lo que debe permitir realizar búsquedas de trabajadores mediante filtros como nombre, correo, Id de trabajador, puesto de trabajo, etc. 
- [x] Una vez encontrado el resultado de la búsqueda, se debe verificar que el sistema muestre el listado de trabajadores con sus datos de contacto o bien un botón para mostrar sus datos(puede ser en el offcanvas), y la opción para editar los datos del trabajador como número de seguridad social, RFC, horarios, 
turnos, etc.  
- [x] Además, permitirá agregar, editar y consultar contratos o documentación relacionada con su 
registro de la empresa.  
- [x] Verificar que exista una alerta o notificación para los usuarios con permisos de "Consultar empleados", que indique si un trabajador tiene datos fiscales faltantes como RFC, NSS, etc, y que además en el dashboard inicial salga un card o alguna alerta visible con esta información, pero recordando que unicamente se mostrará para el administrador en sesión que tenga el permiso Consultar empleados. Las alertas y el sistema ya existen, solo verifica si esto ya esta implementado o si falta agregarse.
- [x] Se debe verificar el funcionamiento de todas las prestaciones, vacaciones, horarios, turnos, datos fiscales y prestaciones conforme a la ley en México y en el "tipo de trabajador" en la sección "datos laborales" verificar que se incluyan todos los tipos de contratación en México como "planta", "honorarios", etc y todos los que apliquen a México.
- [x] En la sección de editar empleado en "nómina", verificar que esten todos los campos requeridos como descuentos de seguridad social, descuentos por conceptos, impuestos y todo lo relacionado a ese módulo en México. Agregar lo faltante en caso de ser necesario.
- [x] En la sección de "prestaciones" dentro de Editar empleado, verificar que no falten datos o completarlo, recuerda que ya estamos en otra etapa y estamos mejorando el producto minimo viable.
- [x] Este módulo actualmente genera un contrato por default, pero es necesario que permita el alta y administración de plantillas o machotes de contratos. Puede ser mediante un formulario o editor de texto enriquecido de tu preferencia, y posteriormente generarlo en un PDF como el que ya genera actualmente, esto respetando los datos del trabajador.
- [x] El sistema ya conserva  la versión de cada contrato y hace un histórico en forma de arbol de cada cambio realizado en el trabajador organizados en una estructura de árbol por fecha de creación o edición para facilitar su seguimiento. ¿Puedes verificar el funcionamiento de esto? y en caso de ser necesario agregar un pequeño buscador de contratos por fecha de creación o edición.
- [x] Verificar que el sistema almacene los datos de los trabajadores junto con la documentación relacionada con contratos, renuncias, cartas de baja, finiquitos y liquidaciones los cuales se podrán agregar, consultar y editar en este módulo, facilitando su consulta y gestión. Además, verificar o agregar una calculadora de finiquitos y liquidaciones conforme a la ley en México pero con una leyenda que indique este es un valor de calculo aproximado y deberá verificarse. La calculadora de finiquito y liquidación debe permitir cambiar los parámetros como porcentajes, etc, pero conforme a la ley en México.
-[x]Verificar el funcionamiento de las solicitudes de vacaciones, seguimientos y alertas.
-[x]Crear una zona de "mi perfil", donde cada usuario pueda visualizar sus datos, para esto, es necesario que desde el panel de alta de usuarios, se pueda vincular un usuario(administrador) con un trabajador y que este usuario pueda ver sus datos y realizar acciones como solicitar vacaciones, etc. ¿Como se podría resolver esta vinculación? y que acciones podría realizar el usuario en su perfil? 
- [x] La tabla de resultados "Columna principal de datos" en la sección /rh/RecursosHumanos queda muy encimada, por lo que se deberá extenderse  (col-12) y la sección "datos del empleado" se moverá a un "offcanvas". He puesto un ejemplo en la vista `application/views/rh/empleados/main_empleados.php` con el botón de ejemplo que es el disparador del offcanvas así como el ejemplo del offc

---

**Última actualización:** 2026-01-21
**Desarrollador:** Sistema ERP - CHISA Recubrimientos

## ✅ Completado (Última sesión: 2026-02-05)
- [x] **Módulo de Facturación (Fase 1: Emisión y Sincronización)**
  - Dashboard de Facturas con listado histórico, filtros y descarga.
  - Emisión de Facturas (Cliente, Conceptos, Cálculos automáticos de IVA).
  - **Smart Download:** Recuperación automática de PDF/XML desde API si no existen localmente.
  - **Sincronización Bidireccional:**
    - Validar estatus (Local -> API) para cancelar facturas obsoletas.
    - Importar facturas faltantes (API -> Local) automáticamente.
  - Gestión de Tokens OAuth para Facture App.

**Última actualización:** 2026-02-06
**Desarrollador:** Fausto Solano - CHISA Recubrimientos 


