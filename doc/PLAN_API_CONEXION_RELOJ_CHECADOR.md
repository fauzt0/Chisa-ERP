# Plan de Desarrollo Asistido - Módulo ADMS ZKTeco (Backend ERP Chisa)

**Contexto del Proyecto para el Asistente AI:**
Actúas como un Desarrollador Senior de PHP y CodeIgniter 3. Estamos integrando un módulo de control de asistencia biométrica (ZKTeco MB10-VL) a un ERP existente (Chisa-ERP).

**Regla de Oro de la Arquitectura:**
El ERP es el **Cerebro Absoluto**. Un "Proxy Local" (script ejecutable instalado físicamente en la planta de la empresa) actuará únicamente como comunicador entre el hardware y este ERP.

**⚠️ REGLA CRÍTICA — Proxy Local es "CARTERO CIEGO":**
El Proxy Local es un archivo ejecutable instalado físicamente en la planta que **NO PODRÁ recibir mantenimiento o actualizaciones** en el futuro. Por lo tanto:
- El Proxy Local **NO** traduce, parsea o estructura los datos del reloj
- El Proxy Local **solo** recibe el texto plano (raw data) que escupe el ZKTeco y lo reenvía en un JSON minimalista
- **Toda la inteligencia de parseo está en el ERP**: `explode("\n")` para separar líneas, `preg_split('/\s+/')` para separar columnas
- El ERP tomará las decisiones, calculará los turnos, agrupará entradas/salidas y encolará los comandos
- El Proxy Local solo hará peticiones HTTP a la API de este ERP para subir raw_data o descargar órdenes

---

## FASE 0: Reconocimiento del Entorno y Reglas de Arquitectura (COMPLETADO ✅)

### 0.1. Estructura del Proyecto

- **Framework:** CodeIgniter 3 (confirmado en [`public_html/index.php`](public_html/index.php:315) línea 315: `require_once BASEPATH.'core/CodeIgniter.php'`)
- **Base de datos:** MySQL `st32477_chisa` (usuario: `st32477_chisa`), configurado en [`public_html/application/config/database.php`](public_html/application/config/database.php)
- **Rutas definidas en:** [`public_html/application/config/routes.php`](public_html/application/config/routes.php)
- **Controladores organizados en subdirectorios:** `almacen/`, `compras/`, `contabilidad/`, `dashboards/`, `facturacion/`, `obras/`, `produccion/`, `rh/`, `usuarios/`, `ventas/`

### 0.2. Sistema de Permisos por "Área" ⚠️ CRÍTICO

El sistema de permisos funciona de la siguiente manera:

1. **Base:** [`public_html/application/core/MY_Controller.php`](public_html/application/core/MY_Controller.php) extiende `CI_Controller`
2. **Validación automática:** En el constructor de `MY_Controller` se ejecuta `check_permissions()`, que a su vez llama a `Init_controller::has_module_access()`
3. **Regla:** Cada controlador web DEBE definir la propiedad `$this->modulo = 'Nombre del Área'` para que el sistema valide que el usuario tiene al menos un permiso en ese módulo
4. **Librería de permisos:** [`public_html/application/libraries/Init_controller.php`](public_html/application/libraries/Init_controller.php) contiene:
   - `has_module_access($user_id, $module_name)` — verifica si el usuario tiene ALGÚN permiso en el módulo
   - `has_permission($user_id, $permission)` — verifica un permiso específico contra la tabla `privilege`
   - `check_session()` — redirige si no hay sesión o el rol no es administrador
5. **Configuración de módulos:** [`public_html/application/config/permissions.php`](public_html/application/config/permissions.php) define todos los módulos y sus permisos
6. **Gestión de permisos:** El controlador [`public_html/application/controllers/usuarios/Roles.php`](public_html/application/controllers/usuarios/Roles.php) carga esta configuración y renderiza checkboxes; guarda los permisos como registros individuales en la tabla `privilege`

**Conclusión para nuestro módulo:**
- Los controladores WEB del reloj checador (FASE 3) DEBEN extender `MY_Controller` y definir `$this->modulo = 'Reloj Checador'`
- Los controladores API (FASE 1-2) NO extienden `MY_Controller` porque no tienen sesión web; se autentican por token

### 0.3. Tablas Existentes de RH Reutilizables

Se verificaron las siguientes tablas ya existentes en la base de datos:

| Tabla | Descripción | Estado |
|-------|-------------|--------|
| [`empleados`](public_html/application/models/RH/EmpleadoModel.php) | Catálogo de empleados con `numero_empleado`, `nombre`, `apellido_paterno`, `estatus` | ✅ Reutilizable |
| [`horarios_empleados`](public_html/application/models/RH/HorariosModel.php) | Horarios por empleado (7 días, hora entrada/salida/comida) | ✅ Reutilizable |
| [`contratos_empleados`](public_html/application/models/RH/ContratoModel.php) | Contratos de empleados | 🔜 Para FASE 3 |
| [`incidencias_empleados`](public_html/application/models/RH/IncidenciasModel.php) | Incidencias/permisos | 🔜 Para FASE 3 |
| [`vacaciones_empleados`](public_html/application/models/RH/VacacionesModel.php) | Solicitudes de vacaciones | 🔜 Para FASE 3 |

**Estructura de [`horarios_empleados`](public_html/application/models/RH/HorariosModel.php):**
- `empleado_id`, `dia_semana` (ENUM: Lunes–Domingo), `hora_entrada`, `hora_salida`
- `hora_entrada_comida`, `hora_salida_comida`, `es_dia_laboral`
- `turno`, `fecha_inicio`, `fecha_fin`, `estatus` (Activo/Inactivo)

**Métodos disponibles en [`HorariosModel`](public_html/application/models/RH/HorariosModel.php):**
- `get_horario_empleado($empleado_id)` — obtiene horario semanal completo
- `guardar_horario(...)` — guarda/actualiza horarios
- `get_resumen_horario($empleado_id)` — resumen legible
- `tiene_horario($empleado_id)` — verifica si tiene horario asignado
- `crear_horario_estandar(...)` — crea horario por defecto (Lun–Vie 9:00–18:00)

**Métodos disponibles en [`EmpleadoModel`](public_html/application/models/RH/EmpleadoModel.php):**
- `get_empleado_completo($id)` — obtiene datos del empleado
- `get_lista_empleados_activos()` — listado para selects
- `get_by_departamento($departamento_id)` — empleados por departamento
- `get_estadisticas_rh()` — dashboard RH
- DataTables SSR completo (hereda de `MY_Model`)

### 0.4. Patrón MY_Model (DataTables SSR)

[`public_html/application/core/MY_Model.php`](public_html/application/core/MY_Model.php) proporciona:

- CRUD completo: `insert()`, `update()`, `soft_delete()`, `restore()`, `hard_delete()`
- DataTables SSR: `get_datatables()`, `count_filtered()`, `_get_datatables_query()`
- Métodos de validación: `exists()`, `exists_active()`, `is_unique()`
- Respuestas estandarizadas: `success_response()`, `error_response()`, `not_found_response()`
- Manejo automático de fechas `created_at`, `updated_at`

### 0.5. Controlador Preexistente (Testzk)

Se encontró un controlador prototipo anterior en [`public_html/application/controllers/Testzk.php`](public_html/application/controllers/Testzk.php):
- Extiende `CI_Controller` directamente
- Endpoint `push()` para recibir datos de ZKTeco (JSON + form-data)
- Logging a archivo plano
- Sin autenticación por token
- **Este controlador queda REEMPLAZADO por el nuevo `ApiReloj`**

---

## FASE 1 + FASE 2: Estructura Base del Controlador API (COMPLETADO Y PROBADO ✅)

### 1.1. Archivos Creados/Modificados

| Archivo | Acción | Descripción |
|---------|--------|-------------|
| [`public_html/database/reloj_checador.sql`](public_html/database/reloj_checador.sql) | ✅ Creado | Esquema SQL completo (4 tablas + datos de prueba) |
| [`public_html/application/controllers/api/ApiReloj.php`](public_html/application/controllers/api/ApiReloj.php) | ✅ Creado | Controlador API con 4 endpoints, autenticación por token, y parseo de raw_data ZKTeco |
| [`public_html/application/models/Reloj/RelojModel.php`](public_html/application/models/Reloj/RelojModel.php) | ✅ Creado | Modelo completo con lógica de negocio y parseo de raw_data ATTLOG |
| [`public_html/application/config/permissions.php`](public_html/application/config/permissions.php) | ✅ Modificado | Nuevo módulo "Reloj Checador" con 4 permisos |
| [`public_html/application/config/routes.php`](public_html/application/config/routes.php) | ✅ Modificado | 4 rutas API registradas |

### 1.2. Esquema SQL — 4 Tablas

#### `reloj_dispositivos`
Almacena los relojes checador registrados. Cada uno tiene un `api_token` único de 64 caracteres para autenticar al Proxy Local.

```sql
CREATE TABLE reloj_dispositivos (
  id            INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sn            VARCHAR(50) NOT NULL UNIQUE,
  alias         VARCHAR(100),
  ubicacion     VARCHAR(200),
  api_token     VARCHAR(64) NOT NULL UNIQUE,
  ultima_conexion DATETIME,
  ultimo_comando_id INT(11) UNSIGNED,
  activo        TINYINT(1) DEFAULT 1,
  fecha_alta    DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### `asistencias`
Registro de cada checada. La combinación `usuario_id + fecha_hora` es única (evita duplicados). `empleado_id` es FK hacia `empleados.id` con `ON DELETE SET NULL`.

```sql
CREATE TABLE asistencias (
  id          INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  VARCHAR(20) NOT NULL,
  empleado_id INT(11) DEFAULT NULL,
  fecha_hora  DATETIME NOT NULL,
  metodo      TINYINT(4) DEFAULT 1,
  dispositivo_sn VARCHAR(50) NOT NULL,
  creado_el   DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_usuario_fecha (usuario_id, fecha_hora),
  FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE SET NULL
);
```

> ⚠️ **Nota técnica:** `empleados.id` es `INT(11)` sin UNSIGNED. Por eso `empleado_id` también es `INT(11)` sin UNSIGNED.

#### `reloj_comandos`
Cola de comandos. El Proxy Local consulta los `pendiente`, el ERP los marca como `enviado`, y el Proxy reporta el resultado (`ejecutado`/`fallido`).

```sql
CREATE TABLE reloj_comandos (
  id              INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  dispositivo_sn  VARCHAR(50) NOT NULL,
  comando         TEXT NOT NULL,
  estado          ENUM('pendiente','enviado','ejecutado','fallido') DEFAULT 'pendiente',
  respuesta       TEXT,
  intentos        TINYINT(4) DEFAULT 0,
  fecha_creacion  DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_envio     DATETIME,
  fecha_ejecucion DATETIME,
  creado_por      INT(11) UNSIGNED
);
```

#### `reloj_sync_log`
Bitácora de cada comunicación entre Proxy Local y ERP.

```sql
CREATE TABLE reloj_sync_log (
  id                 INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  dispositivo_sn     VARCHAR(50),
  tipo               ENUM('asistencias','comandos','resultado','conexion','error'),
  payload_resumen    VARCHAR(255),
  registros_afectados INT(11),
  ip_origen          VARCHAR(45),
  fecha              DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### Dato de Prueba Insertado

```sql
-- Token para pruebas: chisa-zkteco-proxy-token-2026
INSERT INTO reloj_dispositivos (sn, alias, ubicacion, api_token, activo)
VALUES ('MB10VL-XXXX-TEST', 'Reloj de Prueba ZKTeco', 'Oficina Central - Pruebas', 'chisa-zkteco-proxy-token-2026', 1);
```

### 1.3. Endpoints API

| Método | Ruta | Descripción | Fase |
|--------|------|-------------|------|
| `GET` | [`api/reloj/status`](public_html/application/controllers/api/ApiReloj.php:245) | Health-check, verifica conectividad y devuelve datos del dispositivo autenticado | F1 |
| `POST` | [`api/reloj/sync_asistencias`](public_html/application/controllers/api/ApiReloj.php:70) | **Recibe raw_data ZKTeco** (texto plano), lo parsea con `explode()` + `preg_split()`, evita duplicados, vincula con empleados ERP | F1/F2 |
| `GET` | [`api/reloj/comandos_pendientes/(:sn)`](public_html/application/controllers/api/ApiReloj.php:145) | Obtiene comandos pendientes para un dispositivo y los marca como "enviado" | F1 |
| `POST` | [`api/reloj/comando_resultado`](public_html/application/controllers/api/ApiReloj.php:193) | Recibe resultado de ejecución de comando (return_code + respuesta) | F1 |

### 1.3.1. Formato del Payload sync_asistencias (FASE 2)

El Proxy Local envía el texto plano EXACTO que escupe el reloj ZKTeco:

```json
{
  "sn":       "MB10VL-XXXX-TEST",
  "table":    "ATTLOG",
  "raw_data": "1\t2026-05-29 08:15:30\t255\t15\n2\t2026-05-29 09:00:00\t255\t1"
}
```

**Columnas del ATTLOG (separadas por tab/espacio):**

| Columna | Campo     | Descripción                     |
|---------|-----------|----------------------------------|
| 0       | usuario_id | PIN del empleado en el reloj     |
| 1       | fecha      | YYYY-MM-DD                      |
| 2       | hora       | HH:MM:SS                        |
| 3       | estado     | Se ignora (byte de estado)       |
| 4       | metodo     | 15=Rostro, 1=Huella, 0=Password |

**Algoritmo de parseo en el ERP:**
1. `trim($raw_data)` — eliminar espacios en blanco al inicio/fin
2. `explode("\n", $raw_data)` — separar líneas individuales
3. Por cada línea: `preg_split('/\s+/', $linea)` — separar columnas por cualquier whitespace
4. Validar formato de fecha (`/^\d{4}-\d{2}-\d{2}$/`) y hora (`/^\d{2}:\d{2}:\d{2}$/`)
5. Combinar fecha + hora → `fecha_hora`
6. Insertar con detección de duplicados (`usuario_id` + `fecha_hora`)
7. Vincular `usuario_id` con `empleados.numero_empleado` del ERP si existe

**Seguridad adicional:** El `sn` del payload DEBE coincidir con el SN del dispositivo autenticado vía X-API-Key. Si no coinciden, responde HTTP 403.

### 1.4. Modelo de Seguridad API

```
Proxy Local  ─── HTTP ───>  ERP (ApiReloj)
  Header: X-API-Key          │
                              ├─ validar_token() en reloj_dispositivos
                              ├─ actualizar_conexion()
                              └─ responder JSON
```

- **Autenticación:** Header `X-API-Key` (fallback a `?token=` solo para GET)
- **Validación:** Contra tabla `reloj_dispositivos.api_token` + `activo = 1`
- **Tolerancia a formatos:** El método [`_obtener_payload_json()`](public_html/application/controllers/api/ApiReloj.php:298) soporta:
  - Raw JSON body (recomendado)
  - Form-data con campo `json_data`
  - POST tradicional con campos sueltos

### 1.5. Modelo RelojModel — Métodos Disponibles

**Autenticación:**
- [`validar_token($token)`](public_html/application/models/Reloj/RelojModel.php:36) — valida token del dispositivo
- [`actualizar_conexion($dispositivo_id)`](public_html/application/models/Reloj/RelojModel.php:51) — actualiza timestamp de última conexión

**Parseo de Raw Data ZKTeco (NUEVO EN FASE 2):**
- [`procesar_raw_data_attlog($raw_data, $dispositivo_sn)`](public_html/application/models/Reloj/RelojModel.php:130) — **parsea el texto plano del reloj ZKTeco usando `explode("\n")` y `preg_split('/\s+/')`**, extrae las 5 columnas (usuario_id, fecha, hora, estado, método), valida formatos, vincula con empleados ERP e inserta evitando duplicados

**Asistencias:**
- [`buscar_empleado_por_pin($pin)`](public_html/application/models/Reloj/RelojModel.php:70) — vincula PIN del reloj con empleados ERP
- [`insertar_asistencia($data)`](public_html/application/models/Reloj/RelojModel.php:101) — inserta con detección de duplicados
- [`get_asistencias_rango($inicio, $fin, $empleado_id)`](public_html/application/models/Reloj/RelojModel.php:194) — consulta por fechas

**Comandos:**
- [`obtener_y_marcar_comandos($sn)`](public_html/application/models/Reloj/RelojModel.php:220) — obtiene pendientes y los marca como "enviado" (transacción)
- [`actualizar_comando_resultado($id, $estado, $respuesta)`](public_html/application/models/Reloj/RelojModel.php:279) — actualiza resultado
- [`encolar_comando($sn, $comando, $creado_por)`](public_html/application/models/Reloj/RelojModel.php:316) — inserta nuevo comando
- [`get_estadisticas_comandos($sn)`](public_html/application/models/Reloj/RelojModel.php:336) — conteo por estado

**Sync Log:**
- [`registrar_sync_log($sn, $tipo, $resumen, $registros)`](public_html/application/models/Reloj/RelojModel.php:375)
- [`get_sync_log($limite)`](public_html/application/models/Reloj/RelojModel.php:393)

**Dispositivos:**
- [`get_dispositivos($solo_activos)`](public_html/application/models/Reloj/RelojModel.php:412)
- [`get_dispositivo_by_sn($sn)`](public_html/application/models/Reloj/RelojModel.php:430)
- [`get_estadisticas_dashboard()`](public_html/application/models/Reloj/RelojModel.php:443)

### 1.6. Permisos Agregados

En [`public_html/application/config/permissions.php`](public_html/application/config/permissions.php:108) se agregó el módulo:

```php
'Reloj Checador' => array(
  'reloj_ver_dashboard'    => 'Ver dashboard del reloj checador',
  'reloj_sync_asistencias' => 'Sincronizar asistencias',
  'reloj_ver_reportes'     => 'Ver reportes de asistencia',
  'reloj_gestionar'        => 'Gestionar dispositivos y comandos',
),
```

### 1.7. Rutas Registradas

En [`public_html/application/config/routes.php`](public_html/application/config/routes.php:64):

```php
$route['api/reloj/sync_asistencias']       = 'api/ApiReloj/sync_asistencias';
$route['api/reloj/comandos_pendientes/(:any)'] = 'api/ApiReloj/comandos_pendientes/$1';
$route['api/reloj/comando_resultado']      = 'api/ApiReloj/comando_resultado';
$route['api/reloj/status']                 = 'api/ApiReloj/status';
```

### 1.8. Pruebas Realizadas ✅ (FASE 1 + FASE 2)

| Escenario | Método | Token | Resultado |
|-----------|--------|-------|-----------|
| Status OK | `GET` | Válido | ✅ HTTP 200, JSON con versión y dispositivo |
| Sin token | `GET` | Ninguno | ✅ HTTP 401, "No autorizado" |
| Token inválido | `GET` | Inválido | ✅ HTTP 401, "Token inválido" |
| **Raw data ATTLOG (6 registros)** | `POST` | Válido | ✅ **6 insertadas** |
| **Duplicados (mismos 2 registros)** | `POST` | Válido | ✅ **0 insertadas, 2 duplicados** |
| **SN mismatch** | `POST` | Válido | ✅ **HTTP 403, "SN no coincide"** |
| **Missing raw_data** | `POST` | Válido | ✅ **HTTP 400, "Payload inválido"** |
| **Tabla no soportada (BIODATA)** | `POST` | Válido | ✅ **HTTP 501, "no soportado"** |
| Sync con GET | `GET` | Válido | ✅ HTTP 405, "Método no permitido" |

### 1.9. Errores Corregidos Durante FASE 1 y FASE 2

1. **FK constraint error (errno 150):** `empleados.id` es `INT(11)` sin UNSIGNED. Se corrigió `asistencias.empleado_id` de `INT(11) UNSIGNED` a `INT(11)`.
2. **Empty JSON en errores 401/405:** El método `_responder()` original no llamaba a `->_display()` + `exit`, por lo que el framework no enviaba el cuerpo JSON en respuestas con error. Se corrigió agregando `->_display()` y `exit`.
3. **FASE 2 — Validación SN vs Token:** Se agregó verificación de que el `sn` del payload coincida con el SN del dispositivo autenticado (evita que un token válido se use para enviar datos de otro reloj).

---

## FASE 2: Parseo de Raw Data ZKTeco (COMPLETADO Y PROBADO ✅)

### 2.1. Cambio Arquitectónico Crítico

**El Proxy Local es un "Cartero Ciego".** Dado que el Proxy Local es un archivo ejecutable instalado físicamente en la planta que **NO PODRÁ recibir mantenimiento o actualizaciones** en el futuro:

- ❌ El Proxy Local **NO** parsea, traduce o estructura los datos del reloj
- ✅ El Proxy Local **solo** reenvía el texto plano exacto que escupe el ZKTeco
- ✅ **Toda la inteligencia de parseo está en el ERP**

### 2.2. Lo Implementado

**En el controlador [`ApiReloj::sync_asistencias()`](public_html/application/controllers/api/ApiReloj.php:70):**
- Acepta el nuevo formato: `{"sn":"...","table":"ATTLOG","raw_data":"..."}`
- Valida que `sn` del payload coincida con el SN del dispositivo autenticado (HTTP 403 si no)
- Enruta según el valor de `table`: actualmente soporta `ATTLOG`, otros tipos devuelven HTTP 501
- Registra en bitácora de sincronización con detalle de insertadas/duplicados/errores

**En el modelo [`RelojModel::procesar_raw_data_attlog()`](public_html/application/models/Reloj/RelojModel.php:130):**
- `trim($raw_data)` — sanitización inicial
- `explode("\n", $raw_data)` — separa líneas individuales
- `preg_split('/\s+/', $linea)` — separa columnas por cualquier whitespace (tabs o espacios)
- Validación de formato: fecha (`/^\d{4}-\d{2}-\d{2}$/`) y hora (`/^\d{2}:\d{2}:\d{2}$/`)
- Ignora la columna 3 (estado/byte de estado del ZKTeco)
- Convierte método a entero (15=Rostro, 1=Huella, 0=Password)
- Vincula `usuario_id` con `empleados.numero_empleado` del ERP
- Inserta con detección de duplicados (reusa `insertar_asistencia()`)
- Retorna estadísticas: `insertadas`, `duplicados`, `errores`, `detalles`

### 2.3. Resultados de Pruebas de FASE 2

| Escenario | Resultado |
|-----------|-----------|
| 6 registros ATTLOG válidos | ✅ 6 insertados, 0 errores |
| Reprocesar mismos 2 registros | ✅ 0 insertados, 2 duplicados |
| SN del payload no coincide con dispositivo autenticado | ✅ HTTP 403 |
| Payload sin raw_data | ✅ HTTP 400 con mensaje claro |
| Tabla no soportada (BIODATA) | ✅ HTTP 501 |

---

## FASE 3: Implementación Final — El Cerebro (PENDIENTE 🔜)

### 3.1. Gestión de Comandos Web (UI)

Crear controlador web que extienda `MY_Controller` con `$this->modulo = 'Reloj Checador'`:

- **Vista de Dashboard:** Tarjetas con estadísticas (asistencias hoy, comandos pendientes, dispositivos activos, última sincronización)
- **Gestión de Dispositivos:** CRUD de `reloj_dispositivos` con DataTables SSR
- **Cola de Comandos:** Formulario para encolar comandos manualmente + tabla con estado de cada comando (DataTables SSR)
- **Historial de Sincronización:** Tabla con `reloj_sync_log` filtrable por tipo y fecha

### 3.2. Cálculo de Asistencias (Cruce con Horarios)

Lógica en modelo para procesar las checadas brutas y convertirlas en reportes de asistencia:

1. **Agrupar por empleado + día:** `DATE(fecha_hora)`
2. **Determinar entradas/salidas:** `MIN(fecha_hora)` → Entrada, `MAX(fecha_hora)` → Salida
3. **Evaluar registros intermedios:** Si hay más de 2 checadas en un día, las intermedias se evalúan como salidas a comida según el horario (`hora_entrada_comida` / `hora_salida_comida`)
4. **Cruzar con [`HorariosModel::get_horario_empleado()`](public_html/application/models/RH/HorariosModel.php:22):** Comparar hora real vs hora programada para detectar retardos, faltas, horas extra

### 3.3. Reportes con DataTables SSR

- **Reporte Diario:** Filtros por fecha, empleado, departamento. Columnas: empleado, entrada, salida, horas trabajadas, retardos
- **Reporte Mensual:** Resumen por empleado: días trabajados, faltas, retardos, horas extra
- **Exportación a PDF/Excel:** Desde DataTables (botón de exportación)

### 3.4. Cron para Tareas Programadas

- **Sincronización automática:** Script CLI que consulta `reloj_comandos` y fuerza extracción de asistencias históricas
- **Protección:** Todos los cron routes DEBEN usar `is_cli()` o `$this->input->is_cli_request()` como primera línea del método

---

## Resumen de Arquitectura de Seguridad

```
┌─────────────────────────────────────────────────────────────────┐
│                        ERP Chisa                                │
│                                                                 │
│  ┌─────────────────────┐    ┌──────────────────────────────┐   │
│  │ Controladores WEB   │    │  Controladores API           │   │
│  │ (MY_Controller)     │    │  (CI_Controller directo)     │   │
│  │                     │    │                              │   │
│  │ $this->modulo =     │    │  Autenticación vía           │   │
│  │   'Reloj Checador'  │    │  X-API-Key header            │   │
│  │                     │    │                              │   │
│  │ check_session()     │    │  No requiere sesión web      │   │
│  │ has_module_access() │    │                              │   │
│  └─────────┬───────────┘    └─────────────┬────────────────┘   │
│            │                              │                     │
│            ▼                              ▼                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                  Modelos (RelojModel)                     │  │
│  │  - reloj_dispositivos  - asistencias                     │  │
│  │  - reloj_comandos      - reloj_sync_log                  │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                  Cron Jobs (is_cli())                     │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                           │
                    HTTP API (X-API-Key)
                           │
┌─────────────────────────────────────────────────────────────────┐
│                     Proxy Local (Planta)                        │
│  Script que se comunica con el hardware ZKTeco MB10-VL         │
│  Envía checadas → Consulta comandos → Reporta resultados       │
└─────────────────────────────────────────────────────────────────┘
```

---

## Siguientes Pasos — FASE 3: Interfaz Web y Lógica RH

FASE 1 ✅ y FASE 2 ✅ están completas y probadas. El siguiente paso es construir la interfaz gráfica para Recursos Humanos.

### Lo que sigue (FASE 3):

1. **Dashboard del Reloj Checador** — Controlador web que extienda `MY_Controller` con `$this->modulo = 'Reloj Checador'`. Vista con tarjetas de estadísticas (asistencias hoy, comandos pendientes, dispositivos activos, última sincronización).
2. **Gestión de Dispositivos** — CRUD de `reloj_dispositivos` con DataTables SSR.
3. **Cola de Comandos Web** — Formulario para encolar comandos manualmente + tabla con estado (DataTables SSR).
4. **Historial de Sincronización** — Tabla con `reloj_sync_log` filtrable por tipo y fecha.
5. **Cálculo de Asistencias** — Modelo que cruce `asistencias` con `horarios_empleados` para calcular entradas/salidas, retardos, horas extra.
6. **Reportes DataTables SSR** — Reportes diarios y mensuales con filtros por empleado/departamento/fecha.

### ¿Cómo continuar?

- **Opción A (recomendada):** Continuar aquí mismo en este chat
- **Opción B:** Abrir un nuevo chat con instrucción: *"Eres un Senior Full-Stack PHP/CI3 Developer. Continuamos FASE 3 del módulo ZKTeco. Contexto completo en PLAN_API_CONEXION_RELOJ_CHECADOR.md"*
- **Opción C:** Usar otro modelo/herramienta con este plan como contexto

Confirma cuál opción prefieres y comenzamos con FASE 3.
