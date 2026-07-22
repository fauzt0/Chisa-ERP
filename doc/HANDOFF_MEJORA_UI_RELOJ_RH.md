# Handoff — Mejora UI: Reloj Checador + RH

> **Propósito:** Documento para iniciar una **nueva conversación** en Cursor sin saturar tokens.  
> Contiene contexto, estado actual, restricciones y plan de trabajo para mejorar dashboard, reportes y la sección RH del módulo reloj ZKTeco.

**Fecha:** 2026-06-18  
**Rama activa:** `feature/produccion`  
**Repo git:** `public_html/.git` → https://github.com/fauzt0/Chisa-ERP.git

---

## ⚠️ Restricciones CRÍTICAS (NO violar)

| Componente | ¿Tocar? | Motivo |
|------------|---------|--------|
| `ApiReloj.php` → `sync_asistencias` | ❌ NO | API en producción que ya funcionaba con el reloj |
| `RelojModel::procesar_raw_data_attlog()` | ❌ NO | Parser de raw data del reloj |
| Carpeta `iclock/` (proxy local) | ❌ NO | Cartero ciego en planta; usuario no lo modifica |
| `RelojSyncRhModel` / sync empleados | ❌ NO | Sincronización empleados ya operativa |

**Sí se puede modificar:** vistas, controladores web RH, métodos de reporte/cálculo, estilos, modales, DataTables.

---

## Arquitectura (resumen)

```
Reloj ZKTeco → iclock/ (proxy PHP+XAMPP) → POST /api/reloj/sync_asistencias → tabla asistencias
                                              ↑
                                    ApiReloj (NO TOCAR recepción)

ERP Web:
  rh/RelojChecador/*     → dashboard, dispositivos, comandos, reportes
  rh/RecursosHumanos/*   → empleados + offcanvas con modal de asistencias (NUEVO)
```

**Documentación técnica:** `API_RELOJ_CHECADOR.md` (actualizada con diccionario ATTLOG y guía proxy).

---

## Formato ATTLOG (verificado)

Línea real del reloj (`iclock/datos_reloj.txt`):

```
1	2026-05-28 12:14:10	255	15	0	0	0	0	0	0
```

| Col | Campo | ERP |
|-----|-------|-----|
| 0 | PIN | `usuario_id` → vincula `empleados.numero_empleado` |
| 1 | Fecha | `YYYY-MM-DD` |
| 2 | Hora | `HH:MM:SS` |
| 3 | Status `255` | Ignorado |
| 4 | VerifyType | `1`=Huella, `3`=Contraseña, `15`=Rostro |
| 5-9 | Ceros | Ignorados |

**Interpretación entrada/salida/comida:** el ERP asigna por orden cronológico (reloj no envía tipo).

---

## Lo ya implementado (sesión anterior)

### Backend — `RelojModel.php`

- `metodo_checada_etiqueta()` — etiqueta VerifyType
- `etiquetar_checadas_secuencia()` — entrada/salida/comida por orden
- `get_resumen_asistencias_periodo()` — resumen diario en rango
- `calcular_asistencia_diaria()` — extendido con comida, horas reales vs horario
- `contar_checadas_empleado()`, `dia_semana_es()`

### Backend — `RecursosHumanos.php`

- `asistencias_reloj_resumen` — badge offcanvas (POST AJAX)
- `asistencias_reloj_periodo` — modal día/semana/mes (POST AJAX)
- Flag `puede_ver_reloj` si tiene permiso `reloj_ver_reportes`

### Frontend — `main_empleados.php`

- Badge **Reloj Checador** en offcanvas de empleado
- Modal `#modalAsistenciasReloj` con tabs Día / Semana / Mes
- Acordeón por día con checadas + tipo interpretado + resumen (entrada, salida, comida, retardo)

---

## Inventario de archivos del módulo

### Controladores

| Archivo | Rol |
|---------|-----|
| `controllers/api/ApiReloj.php` | API proxy — **solo lectura** |
| `controllers/rh/RelojChecador.php` | Web: dashboard, dispositivos, comandos, reportes, sync |
| `controllers/rh/RecursosHumanos.php` | RH empleados + asistencias reloj en offcanvas |

### Modelos

| Archivo | Rol |
|---------|-----|
| `models/Reloj/RelojModel.php` | Asistencias, comandos, DataTables SSR, cálculo diario |
| `models/Reloj/RelojSyncRhModel.php` | Sync empleados → reloj — **no tocar** |
| `models/RH/HorariosModel.php` | `horarios_empleados` — cruce con checadas |
| `models/RH/EmpleadoModel.php` | Empleados |

### Vistas Reloj Checador

| Vista | Estado UI |
|-------|-----------|
| `rh/reloj_checador/dashboard.php` | Básico: 4 cards + tabla dispositivos + sync log |
| `rh/reloj_checador/reporte_diario.php` | DataTable checadas crudas del día |
| `rh/reloj_checador/reporte_mensual.php` | Resumen por empleado (días trabajados) |
| `rh/reloj_checador/dispositivos.php` | CRUD dispositivos + token |
| `rh/reloj_checador/comandos.php` | Cola comandos ADMS |
| `rh/reloj_checador/sync_log.php` | Bitácora sync |
| `rh/reloj_checador/sync_empleados_rh.php` | Sync forzado RH → reloj |

### Vistas RH

| Vista | Estado UI |
|-------|-----------|
| `rh/empleados/main_empleados.php` | Listado + offcanvas (vacaciones, incidencias, horario, **reloj nuevo**) |

### Rutas (`config/routes.php`)

- API: `api/reloj/*` (líneas 64-71)
- Web: `rh/RelojChecador/*` (líneas 73-99)

### Permisos (`config/permissions.php`)

- `reloj_ver_dashboard`
- `reloj_sync_asistencias`
- `reloj_ver_reportes`
- `reloj_gestionar`
- `reloj_sync_empleados_rh`

### Proxy local (referencia, no editar)

- `iclock/config.php`, `ruta_cdata.php`, `lib_api.php`, `panel.php`

---

## Objetivo de la nueva conversación

**Mejorar de una vez** la experiencia visual y funcional de:

1. Dashboard Reloj Checador
2. Reportes diario y mensual
3. Integración RH (offcanvas + coherencia con reportes)
4. Consistencia visual con el resto del ERP (cards, badges, modales como vacaciones/incidencias)

---

## Plan de mejora — Fase 1: Dashboard (`rh/RelojChecador`)

**Archivos:** `dashboard.php`, `RelojChecador::index()`, `RelojModel::get_estadisticas_dashboard()`

### Mejoras propuestas

- [ ] Cards con mismo estilo que `main_empleados.php` (progress bars, iconos Lucide)
- [ ] Card **Retardos hoy** / **Sin checada de salida** (usar `calcular_asistencia_diaria` en lote)
- [ ] Gráfica simple: checadas últimos 7 días (Chart.js o contadores)
- [ ] Tabla **Últimas checadas** (5-10 registros con PIN, nombre, hora, método)
- [ ] Indicador **dispositivo online/offline** (última conexión < 30 min = verde)
- [ ] Accesos rápidos: Reporte diario, Dispositivos, Sync empleados
- [ ] Widget **empleados sin PIN en reloj** (sin `reloj_pin` / sin sync)

### Endpoint nuevo sugerido

- `RelojChecador::dashboard_stats_ajax()` — stats en tiempo real sin recargar página

---

## Plan de mejora — Fase 2: Reporte Diario

**Archivos:** `reporte_diario.php`, `reporte_diario_scripts.php`, `search_asistencias_diario`, `RelojModel::get_asistencias_diario_datatables()`

### Problema actual

Solo muestra **checadas crudas** (una fila por registro). No muestra entrada/salida/comida interpretada.

### Mejoras propuestas

- [ ] Cambiar vista a **resumen por empleado por día** (una fila = un empleado):
  - Entrada | Salida comida | Entrada comida | Salida | Estado | Retardo | Horas
- [ ] Modal detalle al clic en fila → checadas del día + timeline (reutilizar lógica del modal RH)
- [ ] Filtros: fecha, departamento, empleado, estado (completo/retardo/falta)
- [ ] Badges de color por estado (`Asistencia completa`, `Con retardo`, etc.)
- [ ] Export CSV con columnas interpretadas (no solo crudas)
- [ ] Endpoint: `RelojChecador::reporte_diario_resumen` (agrupado por empleado)

### Método modelo sugerido

```php
RelojModel::get_resumen_diario_empleados($fecha, $departamento_id, $empleado_id)
// Por cada empleado con checada o con horario laboral: calcular_asistencia_diaria()
```

---

## Plan de mejora — Fase 3: Reporte Mensual

**Archivos:** `reporte_mensual.php`, `reporte_mensual_scripts.php`, `get_asistencias_mensual_datatables()`

### Problema actual

Solo cuenta `dias_trabajados` y primera/última checada del mes. Sin retardos, faltas ni horas.

### Mejoras propuestas

- [ ] Columnas: días laborales | días con checada | faltas | retardos | horas totales
- [ ] % asistencia = días_con_checada / días_laborales del horario
- [ ] Clic en empleado → modal mes completo (calendario o tabla por día)
- [ ] Export Excel mensual por departamento
- [ ] Comparativa mes anterior (opcional)

### Método modelo sugerido

```php
RelojModel::get_resumen_mensual_empleado($empleado_id, $anio, $mes)
```

---

## Plan de mejora — Fase 4: RH (`RecursosHumanos`)

**Archivo principal:** `rh/empleados/main_empleados.php`

### Ya hecho

- Badge + modal asistencias reloj en offcanvas

### Mejoras adicionales propuestas

- [ ] Unificar estilo de badges (vacaciones, incidencias, horario, reloj) — misma altura, iconos Lucide
- [ ] En offcanvas: mostrar **PIN reloj** y estado sync (`reloj_pin`, `reloj_sync_at` si existen columnas)
- [ ] Link directo «Abrir en reporte mensual» desde modal asistencias
- [ ] Indicador visual si empleado **no tiene horario** configurado (afecta cálculo)
- [ ] Pulir offcanvas header (avatar/iniciales, número empleado destacado)
- [ ] Tabla empleados: columna opcional «Última checada» (si permiso reloj)

---

## Plan de mejora — Fase 5: Dispositivos y Comandos (UI)

**Archivos:** `dispositivos.php`, `comandos.php`, scripts asociados

### Mejoras propuestas

- [ ] Dispositivos: card layout en móvil, copiar token con un clic, estado conexión visual
- [ ] Comandos: filtro por estado, badge ADMS válido/inválido, preview comando formateado
- [ ] Sync log: auto-refresh opcional, filtro por tipo (asistencias/comandos/resultado)

---

## Plan de mejora — Fase 6: CSS / componentes compartidos

- [ ] Crear partial `rh/reloj_checador/_estilos.php` o CSS común del módulo
- [ ] Componentes reutilizables:
  - `badge_estado_asistencia($estado)`
  - `badge_metodo_checada($metodo)`
  - `timeline_checadas($checadas)` — PHP o JS
- [ ] Paleta sugerida: verde `#15803d` / `#22c55e` (coherente con modal RH ya creado)

---

## Tablas BD relevantes

| Tabla | Uso |
|-------|-----|
| `asistencias` | Checadas crudas (`usuario_id`, `empleado_id`, `fecha_hora`, `metodo`, `dispositivo_sn`) |
| `reloj_dispositivos` | SN, token, última conexión |
| `reloj_comandos` | Cola ADMS |
| `reloj_sync_log` | Bitácora |
| `horarios_empleados` | Entrada, salida, comida, tolerancia, por día semana |
| `empleados` | `numero_empleado`, `reloj_pin` (si migración aplicada) |

---

## Orden de implementación sugerido

1. **Reporte diario resumido** (mayor valor inmediato para RH)
2. **Dashboard** con últimas checadas y alertas
3. **Reporte mensual** enriquecido
4. **RH offcanvas** pulido + columna última checada
5. **Dispositivos/comandos** UI
6. **CSS compartido** y refactor de componentes

---

## Criterios de aceptación

- [ ] Usuario RH ve por empleado: entrada, salida, comida (si 4 checadas), estado y retardo
- [ ] Reporte diario agrupa por empleado, no solo lista cruda
- [ ] Dashboard muestra actividad reciente y salud del dispositivo
- [ ] Modal RH y reportes usan la **misma lógica** (`calcular_asistencia_diaria` / `etiquetar_checadas_secuencia`)
- [ ] **Cero cambios** en `sync_asistencias` y `procesar_raw_data_attlog`
- [ ] Permisos respetados (`reloj_ver_reportes`, `reloj_ver_dashboard`, etc.)
- [ ] UI consistente con cards de `main_empleados.php` y modales existentes

---

## Prompt sugerido para nueva conversación

Copia y pega esto al iniciar el chat:

```
Lee el archivo HANDOFF_MEJORA_UI_RELOJ_RH.md en la raíz del proyecto.

Implementa las mejoras de UI del módulo Reloj Checador según el plan:
- Empezar por Fase 2 (Reporte Diario resumido por empleado) y Fase 1 (Dashboard).
- NO modificar ApiReloj::sync_asistencias ni RelojModel::procesar_raw_data_attlog ni iclock/.
- Reutilizar calcular_asistencia_diaria() y etiquetar_checadas_secuencia() ya existentes.
- Mantener estilo visual coherente con rh/empleados/main_empleados.php.

Rama: feature/produccion
```

---

## Referencias rápidas

| Recurso | Ruta |
|---------|------|
| Doc API + proxy | `API_RELOJ_CHECADOR.md` |
| Plan original módulo | `PLAN_API_CONEXION_RELOJ_CHECADOR.md` |
| Ejemplo raw data | `iclock/datos_reloj.txt` |
| SQL tablas reloj | `public_html/database/reloj_checador.sql` |
| Este handoff | `HANDOFF_MEJORA_UI_RELOJ_RH.md` |

---

## Notas para el agente

- CodeIgniter 3, Bootstrap 5, DataTables SSR, jQuery
- Controladores web extienden `MY_Controller` con `$this->modulo`
- CSRF en POST AJAX: `csrf_token_name` / `csrf_hash`
- Zona horaria ERP: `America/Mexico_City`
- Commit solo si el usuario lo pide explícitamente
