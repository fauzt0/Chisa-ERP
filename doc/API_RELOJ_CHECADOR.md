# API del Módulo Reloj Checador — Documentación Completa

Este documento describe:
1. Los **endpoints HTTP del ERP** para recibir datos del reloj
2. La **arquitectura del Proxy Local** (script PHP en la red local)
3. La **configuración del reloj ZKTeco** para conectarse al proxy

---

## 📋 Índice

- [Arquitectura General](#arquitectura-general)
- [Endpoints del ERP](#endpoints-del-erp)
- [Instalación del Proxy Local](#instalación-del-proxy-local)
- [Configuración del Reloj ZKTeco](#configuración-del-reloj-zkteco)
- [Funcionamiento del Proxy](#funcionamiento-del-proxy)
- [Formato de Datos](#formato-de-datos)
- [Comandos ADMS](#comandos-adms)
- [Solución de Problemas](#solución-de-problemas)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           RED LOCAL (Oficina/Planta)                        │
│                                                                             │
│  ┌─────────────────┐         HTTP POST/GET          ┌─────────────────┐    │
│  │  Reloj ZKTeco   │  ─────────────────────────────→  │  Proxy Local    │    │
│  │  (MB10-VL)      │   /iclock/cdata (asistencias)   │  (PHP+XAMPP)    │    │
│  │                 │   /iclock/getrequest (cmds)     │  IP: 192.168... │    │
│  └─────────────────┘                                 └────────┬────────┘    │
│           ↑                                                   │             │
│           │              ┌─────────────────┐                  │             │
│           └──────────────┤  Panel Web de   │←─────────────────┘             │
│              consulta      │  Control Local  │   (modo debug opcional)       │
│                            │  (panel.php)      │                             │
│                            └─────────────────┘                             │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      │ HTTPS (Internet)
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              ERP EN LA NUBE                                 │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐    │
│  │                    https://erp.chisarecubrimientos.com.mx             │    │
│  │                                                                      │    │
│  │  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐  │    │
│  │  │ /api/reloj/     │    │  Controlador    │    │  RelojModel     │  │    │
│  │  │  sync_asistencias│ ← │  ApiReloj.php   │ ← │  (parsea datos) │  │    │
│  │  │  comandos_pendientes│ │                 │    │                 │  │    │
│  │  │  comando_resultado │ │                 │    │                 │  │    │
│  │  └─────────────────┘    └─────────────────┘    └─────────────────┘  │    │
│  │                                                                      │    │
│  │  ┌──────────────────────────────────────────────────────────────┐   │    │
│  │  │  Módulo RH Web (RelojChecador.php)                            │   │    │
│  │  │  - Ver asistencias, reportes, dispositivos, comandos          │   │    │
│  │  └──────────────────────────────────────────────────────────────┘   │    │
│  └──────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Flujo de Datos

```
┌─────────────┐      POST /iclock/cdata       ┌─────────────┐
│  Reloj      │ ───────────────────────────────→│ Proxy Local │
│  ZKTeco     │  raw_data (tabuladores)         │ (iclock/)   │
└─────────────┘                                 └──────┬──────┘
                                                        │
                             POST /api/reloj/sync_asistencias
                             {sn, table, raw_data}
                                                        ▼
                                               ┌─────────────────┐
                                               │      ERP        │
                                               │  (ChisaERP)     │
                                               └─────────────────┘
                                                        │
                             GET /api/reloj/comandos_pendientes/SN
                                                        │
                             {comandos: [{id, comando}]}
                                                        ▼
┌─────────────┐      GET /iclock/getrequest      ┌─────────────┐
│  Reloj      │ ────────────────────────────────→│ Proxy Local │
│  ZKTeco     │                                 │ (iclock/)   │
└─────────────┘     ← C:123:DATA USER PIN=1...  └─────────────┘
       │
       │ Ejecuta comando
       ▼
┌─────────────┐
│ POST /iclock/devicecmd                    ┌─────────────┐
│ ID=123&Return=0                          │ Proxy Local │
└─────────────┘ ───────────────────────────→│ (iclock/)   │
                                            └──────┬──────┘
                                                   │
                        POST /api/reloj/comando_resultado
                        {comando_id, return_code, respuesta}
                                                   ▼
                                           ┌─────────────────┐
                                           │      ERP        │
                                           │  Marca comando  │
                                           │  como ejecutado │
                                           └─────────────────┘
```

---

## Endpoints del ERP

### 🔐 Autenticación

Toda petición debe incluir el header:

```
X-API-Key: <api_token_del_dispositivo>
```

El token se obtiene desde el ERP en **Reloj Checador → Dispositivos → Nuevo Dispositivo**.

---

### 1️⃣ POST `/api/reloj/sync_asistencias` — Recibir Checadas

**Descripción:** Endpoint principal. El proxy local envía el raw data exacto del reloj.

**Payload:**

```json
{
  "sn": "UDP3252700203",
  "table": "ATTLOG",
  "raw_data": "1\t2026-05-30 08:00:00\t255\t15\n1\t2026-05-30 14:00:00\t255\t15\n1\t2026-05-30 15:00:00\t255\t15\n1\t2026-05-30 18:00:00\t255\t15"
}
```

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `sn` | string | Número de serie del reloj |
| `table` | string | Tipo de tabla (`ATTLOG`, `OPERLOG`, etc.) |
| `raw_data` | string | Datos crudos del reloj, una línea por checada |

**Response (200):**

```json
{
  "status": "success",
  "message": "Datos del reloj procesados correctamente",
  "data": {
    "tabla": "ATTLOG",
    "insertadas": 4,
    "duplicados": 0,
    "errores": 0
  }
}
```

---

### 2️⃣ GET `/api/reloj/comandos_pendientes/<sn>` — Obtener Comandos

**Descripción:** El proxy consulta comandos pendientes para enviar al reloj.

**Response (200 con comandos):**

```
C:1:DATA USER PIN=1001	Name=Juan Perez	Pri=0	Passwd=	Card=	Grp=1	TZ=0000000100000000	Verify=0
```

**Response (200 sin comandos):**

```
OK
```

---

### 3️⃣ POST `/api/reloj/comando_resultado` — Reportar Resultado

**Descripción:** El proxy reporta el resultado de ejecutar un comando en el reloj.

**Payload:**

```json
{
  "comando_id": 1,
  "return_code": 0,
  "respuesta": "Ejecutado correctamente"
}
```

**Response (200):**

```json
{
  "status": "success",
  "message": "Resultado de comando registrado",
  "data": {
    "comando_id": 1,
    "estado": "ejecutado"
  }
}
```

---

### 4️⃣ GET `/api/reloj/status` — Verificar Conectividad

**Descripción:** Health check del API.

**Response (200):**

```json
{
  "status": "success",
  "message": "API del Reloj Checador operativa",
  "data": {
    "version": "1.0.0",
    "dispositivo": "Reloj Oficinas Centrales",
    "server_time": "2026-05-29 23:00:00",
    "timezone": "America/Mexico_City"
  }
}
```

---

## Instalación del Proxy Local

### Requisitos

- **XAMPP** (PHP 7.4+ con Apache)
- **cURL** habilitado en PHP
- Acceso a Internet (para conectar con ERP en la nube)
- Reloj ZKTeco en la misma red local

### Paso 1: Instalar XAMPP

1. Descargar XAMPP desde https://www.apachefriends.org
2. Instalar con Apache y PHP (no necesitas MySQL para el proxy)
3. Iniciar Apache desde el Panel de Control de XAMPP

### Paso 2: Configurar la carpeta del Proxy

```bash
# Ubicación típica en Windows
cd C:\xampp\htdocs\

# Crear carpeta iclock
mkdir iclock

# Copiar archivos del proxy:
# - config.php
# - index.php
# - lib_api.php
# - ruta_cdata.php
# - ruta_getrequest.php
# - ruta_devicecmd.php
# - zkt_comandos.php
# - panel.php
# - .htaccess
```

### Paso 3: Configurar `config.php`

```php
<?php
// config.php — configuración central del proxy ZKTeco

// false = modo producción (envía asistencias al ERP)
// true  = modo prueba (solo guarda localmente)
define('MODO_PRUEBA_LOCAL', false);

// Serial del reloj — debe coincidir con el registrado en el ERP
define('RELOJ_SN', 'UDP3252700203');

// false = producción → sync_asistencias (guarda en BD)
// true  = debug → sync_asistencias_debug (solo log)
define('MODO_SYNC_DEBUG', false);

// Archivos locales (modo prueba)
define('ARCHIVO_COLA', __DIR__ . '/comandos.json');
define('ARCHIVO_LOG', __DIR__ . '/datos_reloj.txt');
define('ARCHIVO_LOG_CMD', __DIR__ . '/log_comandos.txt');

// PIN del administrador del reloj — nunca se borra
define('PIN_ADMIN_RELOJ', 1);
```

### Paso 4: Configurar `lib_api.php`

```php
<?php
// lib_api.php — conexión con el ERP

define('API_BASE', 'https://erp.chisarecubrimientos.com.mx/api/reloj/');

// Token de API — obtener desde ERP: Reloj Checador → Dispositivos
define('API_TOKEN', '4f016b4371933cf76e0fc0c311b1885273452d49ee89211b1218641d380086cb');
```

### Paso 5: Probar el Proxy Local

Abrir en navegador:

```
http://localhost/iclock/panel.php
```

Debes ver:
- **Modo ERP** (si `MODO_PRUEBA_LOCAL = false`)
- Formularios para alta/baja de usuarios
- Estado de la cola de comandos

---

## Configuración del Reloj ZKTeco

### Paso 1: Acceder al Menú del Reloj

1. En el reloj, presionar el botón **Menú**
2. Ir a **Comunicación** → **ADMS** (o **Push**)

### Paso 2: Configurar Conexión ADMS

| Parámetro | Valor |
|-----------|-------|
| **Modo** | ADMS / Server Push |
| **Servidor** | IP de tu computadora con XAMPP (ej. `192.168.100.50`) |
| **Puerto** | `80` (puerto Apache) |
| **Ruta** | `/iclock/` |
| **Intervalo** | `15` segundos |

### Paso 3: Verificar Conectividad

El reloj enviará peticiones periódicas a:

```
GET http://192.168.100.50/iclock/cdata?SN=UDP3252700203&table=ATTLOG&Stamp=9999
```

**Respuesta esperada del proxy:**

```
RegistryCode=OK
ServerVersion=3.1.1
ServerName=CustomADMS
PushVersion=2.4.1
RefreshDelay=15
PushOptionsVersion=1
OK
```

### Paso 4: Configurar en el ERP

1. Entrar al ERP → **Reloj Checador → Dispositivos**
2. Crear nuevo dispositivo:
   - **SN:** `UDP3252700203` (el mismo del reloj)
   - **Alias:** "Reloj Principal"
   - **Ubicación:** "Oficina Central"
3. Guardar y copiar el **API Token** generado
4. Pegar ese token en `lib_api.php`

---

## Funcionamiento del Proxy

### Archivos Principales

| Archivo | Función |
|---------|---------|
| `index.php` | Enrutador principal. Recibe peticiones del reloj y redirige |
| `ruta_cdata.php` | Recibe asistencias (`ATTLOG`) y las envía al ERP |
| `ruta_getrequest.php` | Consulta comandos pendientes del ERP |
| `ruta_devicecmd.php` | Recibe resultado de comandos ejecutados |
| `lib_api.php` | Cliente HTTP para comunicarse con el ERP |
| `config.php` | Configuración central |
| `panel.php` | Interfaz web de administración local |
| `zkt_comandos.php` | Utilidades para generar comandos ADMS |

### Flujo de Asistencias

```php
// ruta_cdata.php — Recepción de checadas

$datos_crudos = file_get_contents('php://input');
// Ejemplo: "1\t2026-05-30 08:00:00\t255\t15\n2\t..."

$payload = [
    'sn' => $_GET['SN'] ?? RELOJ_SN,
    'table' => $_GET['table'] ?? 'ATTLOG',
    'raw_data' => $datos_crudos,
];

// Enviar al ERP
$respuesta = hacer_peticion('sync_asistencias', $payload);

// Responder OK al reloj (si no, el reloj reintentará)
echo 'OK';
```

### Flujo de Comandos

```php
// ruta_getrequest.php — Obtener comandos para el reloj

$respuesta = hacer_peticion('comandos_pendientes/' . $sn, null, 'GET');
$json = json_decode($respuesta, true);

if (!empty($json['data']['comandos'])) {
    $cmd = $json['data']['comandos'][0];
    // Formato requerido por ZKTeco:
    echo 'C:' . $cmd['id'] . ':' . $cmd['comando'] . "\n";
} else {
    echo 'OK';
}
```

---

## Formato de Datos

### Diccionario ATTLOG (Raw Data) — Verificado

Analizando `iclock/datos_reloj.txt` y el parser `RelojModel::procesar_raw_data_attlog`, esta es la estructura real de `table=ATTLOG`:

```
1	2026-05-28 12:14:10	255	15	0	0	0	0	0	0	
```

Cada línea usa **tabuladores** (`\t`). El ERP divide con `preg_split('/\s+/', $linea)` (tabs y espacios), por lo que fecha y hora quedan en columnas separadas:

| Col | Campo | Ejemplo | Descripción |
|-----|-------|---------|-------------|
| 0 | **PIN** | `1` | ID del empleado en el reloj (debe coincidir con `empleados.numero_empleado` o `reloj_pin`) |
| 1 | **Fecha** | `2026-05-28` | Formato `YYYY-MM-DD` |
| 2 | **Hora** | `12:14:10` | Formato `HH:MM:SS` |
| 3 | **Status** | `255` | Valor fijo en MB10-VL — **el ERP lo ignora** |
| 4 | **VerifyType** | `15` | Método de verificación (ver tabla abajo) |
| 5-9 | **Reservados** | `0` | Relleno del hardware — **ignorados** |

#### VerifyType (Método de verificación)

| Valor | Significado |
|-------|-------------|
| `0` | Contraseña |
| `1` | Huella digital |
| `3` | Contraseña numérica |
| `15` | Reconocimiento facial |

> ✅ **Confirmado:** El proxy (`ruta_cdata.php`) reenvía el `raw_data` **sin modificar**. El ERP parsea y guarda cada línea en la tabla `asistencias`.

### Interpretación por el ERP (entrada / salida / comida)

El reloj **no indica** si una checada es entrada, salida o comida. El ERP interpreta por **orden cronológico** del día (`RelojModel::etiquetar_checadas_secuencia` y `calcular_asistencia_diaria`):

| Checadas del día | Interpretación |
|------------------|----------------|
| 1 | Entrada |
| 2 | Entrada + Salida |
| 4 | Entrada + Salida comida + Entrada comida + Salida |
| Otros | Primera = entrada, última = salida; intermedias según horario o secuencia |

Cruza además con `horarios_empleados` para detectar retardos, salida temprana y horas trabajadas.

### Diccionario de Comandos ADMS — Verificado

Comandos texto plano; atributos internos separados por **tabulador real** (`chr(9)`):

| Comando | Descripción |
|---------|-------------|
| `DATA QUERY ATTLOG StartTime=2026-05-01 00:00:00\tEndTime=2026-05-31 23:59:59` | Extraer asistencias históricas |
| `DATA QUERY USERINFO` | Lista de usuarios en el reloj |
| `DATA USER PIN=1001\tName=Juan\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0` | Alta/actualización de empleado |
| `DATA DELETE USER PIN=1001` | Borrar empleado |

- `Pri=0` → usuario normal | `Pri=14` → administrador del reloj (PIN 1 protegido en ERP)
- El proxy entrega al reloj: `C:<id>:<comando>`

---

## Módulo RH — Consulta de asistencias por empleado

Desde **Recursos Humanos → listado de empleados → offcanvas de detalle**:

- Badge **Reloj Checador** con última checada y días del mes
- Botón **Ver Registros** abre modal con vistas **Día / Semana / Mes**
- Cada día muestra checadas crudas + tipo interpretado (entrada, salida, comida)
- Requiere permiso `reloj_ver_reportes`

Endpoints AJAX (no modifican la API del proxy):

| Método | Ruta | Uso |
|--------|------|-----|
| POST | `rh/RecursosHumanos/asistencias_reloj_resumen` | Badge del offcanvas |
| POST | `rh/RecursosHumanos/asistencias_reloj_periodo` | Modal día/semana/mes |

Reportes generales: **Reloj Checador → Reporte Diario / Mensual**

---

## Comandos ADMS

### Formato de Comandos

Los comandos se envían al reloj en este formato:

```
C:<ID>:<COMANDO>
```

**Ejemplo:**

```
C:123:DATA USER PIN=1001	Name=Juan Perez	Pri=0	Passwd=	Card=	Grp=1	TZ=0000000100000000	Verify=0
```

### Tipos de Comandos

| Comando | Descripción |
|---------|-------------|
| `DATA USER PIN=N` | Crear/actualizar usuario |
| `DATA DELETE USER PIN=N` | Eliminar usuario |
| `DATA DELETE FACE PIN=N` | Eliminar rostro de usuario |
| `DATA QUERY ATTLOG` | Consultar registros de asistencia |

### Alta de Usuario (Formato Exacto)

```
DATA USER PIN=1001	Name=Juan Perez	Pri=0	Passwd=1234	Card=	Grp=1	TZ=0000000100000000	Verify=0
```

**Nota importante:** Los separadores deben ser **tabuladores reales** (`\t` ASCII 9), no espacios ni `\t` literales.

---

## Solución de Problemas

### El reloj no se conecta al proxy

1. Verificar que Apache esté corriendo en XAMPP
2. Verificar IP del proxy: `ipconfig` (Windows) o `ifconfig` (Linux)
3. Verificar firewall — puerto 80 debe estar abierto
4. Probar desde navegador: `http://<IP>/iclock/cdata`

### El proxy no envía datos al ERP

1. Verificar `MODO_PRUEBA_LOCAL = false` en `config.php`
2. Verificar `API_TOKEN` correcto en `lib_api.php`
3. Probar conexión: `curl -H "X-API-Key: <TOKEN>" https://erp.chisarecubrimientos.com.mx/api/reloj/status`

### Comandos no funcionan (usuarios corruptos)

**Problema:** El comando `DATA USER` no usa tabuladores reales.

**Solución:** Verificar en `zkt_comandos.php`:

```php
function cmd_alta_usuario($pin, $nombre, $pass = '') {
    $tab = chr(9);  // Tabulador ASCII real
    return "DATA USER PIN=$pin" . $tab . "Name=$nombre" . $tab . "Pri=0" . $tab . ...;
}
```

### Datos duplicados

El ERP automáticamente detecta duplicados por combinación `usuario_id + fecha_hora`. Si el proxy reenvía datos ya procesados, el ERP los ignora y reporta como `duplicados`.

---

## Panel de Control Local

Acceder a `http://localhost/iclock/panel.php` para:

- Ver modo actual (Local vs ERP)
- Encolar comandos manualmente
- Limpiar logs
- Borrar usuarios masivamente (solo modo local)

**IMPORTANTE:** En modo ERP, los comandos deben encolarse desde el ERP (Reloj Checador → Dispositivos → Comandos), no desde el panel local.

---

## Códigos de Estado HTTP

| Código | Significado |
|--------|-------------|
| `200` | OK — Petición exitosa |
| `400` | Bad Request — Payload inválido |
| `401` | No autorizado — Token inválido o faltante |
| `403` | Forbidden — SN no coincide con dispositivo autenticado |
| `404` | Not Found — Comando no encontrado |
| `405` | Method Not Allowed — Usar POST/GET correcto |
| `500` | Error interno del servidor |
| `501` | Not Implemented — Tipo de tabla no soportado |

---

## Notas Técnicas

- **CSRF:** No aplica en endpoints API (usan token), solo en interfaz web
- **Zona horaria:** El ERP usa `America/Mexico_City` por defecto
- **Máximo comandos:** 50 por lote
- **Intervalo reloj:** Configurado en `RefreshDelay` (recomendado: 15 segundos)
- **Duplicados:** Detectados por `usuario_id + fecha_hora`

---

## Contacto y Soporte

Para problemas técnicos:
1. Verificar logs en `datos_reloj.txt` (proxy local)
2. Verificar logs del ERP en `application/logs/`
3. Consultar con el administrador del ERP Chisa
