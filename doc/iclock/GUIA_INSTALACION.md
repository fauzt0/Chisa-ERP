# Proxy ZKTeco — Instalación en Windows (sin XAMPP)

Guía para instalar el **cartero ciego** que conecta el reloj ZKTeco (red local) con el ERP en la nube (`erp.chisarecubrimientos.com.mx`).

**No se usa XAMPP**, no se abre navegador para el usuario final y el proxy puede arrancar solo al encender Windows.

---

## Resumen rápido (checklist)

| # | Dónde | Acción |
|---|--------|--------|
| 1 | ERP | Alta del reloj en **Recursos Humanos → Reloj Checador → Dispositivos** (serial + token API) |
| 2 | Tu PC | Preparar USB con carpeta `iclock` + ZIP de PHP (ver sección 3) |
| 3 | PC cliente | Copiar a `C:\CHISA\iclock\` |
| 4 | PC cliente | `windows\extraer_php_usb.bat` → instala PHP portable |
| 5 | PC cliente | Editar `config.php` y `lib_api.php` (serial y token) |
| 6 | PC cliente | Probar con `iniciar_proxy.bat` + URLs de validación |
| 7 | PC cliente | `instalar_tarea_inicio.ps1` (admin) → arranque invisible |
| 8 | Reloj | Apuntar IP de la PC, puerto 80, ruta `/iclock/cdata` |

---

## 1. ¿Qué hace esta carpeta?

```
Reloj ZKTeco (LAN)
       │  HTTP ADMS
       ▼
PC Windows — carpeta iclock/   ← este proxy (PHP embebido, puerto 80)
       │  HTTPS + X-API-Key
       ▼
ERP — /api/reloj/*             ← guarda checadas y entrega comandos
```

| Componente | Función |
|------------|---------|
| **Reloj ZKTeco** | Envía checadas por protocolo ADMS (HTTP) |
| **Carpeta `iclock/` (PC local)** | Recibe peticiones del reloj y las reenvía al ERP |
| **ERP `/api/reloj/*`** | Guarda asistencias y entrega comandos pendientes |

### Mapeo de rutas (verificado)

| El reloj llama a… | Archivo local | API ERP |
|-------------------|---------------|---------|
| `GET/POST …/iclock/cdata` | `ruta_cdata.php` | `POST /api/reloj/sync_asistencias` |
| `GET …/iclock/getrequest` | `ruta_getrequest.php` | `GET /api/reloj/comandos_pendientes/{SN}` |
| `POST …/iclock/devicecmd` | `ruta_devicecmd.php` | `POST /api/reloj/comando_resultado` |
| Prueba manual | `validar_entorno.php` / `test_erp.php` | `GET /api/reloj/status` (+ sync de prueba) |

**Prueba en servidor (jun 2026):** `status`, `sync_asistencias` y `comandos_pendientes` responden HTTP 200 con `status: success`.

---

## 2. Requisitos

### En el ERP (antes de ir al cliente)

1. Entrar a **Recursos Humanos → Reloj Checador → Dispositivos**.
2. Registrar el dispositivo con su **número de serie (SN)**.
3. Copiar el **token API** generado — se pegará en `lib_api.php`.

### En la PC del cliente

- Windows 10 u 11 (64 bits)
- Misma **red LAN** que el reloj checador
- Salida a internet **HTTPS** hacia `erp.chisarecubrimientos.com.mx`
- IP **fija** o reservada en el router para esta PC (recomendado)
- Permisos de **administrador** (puerto 80 y tarea programada)
- Ruta de instalación **sin espacios** (ej. `C:\CHISA\iclock\`)

---

## 3. Preparar la USB (en tu PC, con internet)

El repositorio **no incluye** el ZIP de PHP (pesa ~30 MB). Debes agregarlo antes de llevar la USB al cliente.

### Estructura recomendada en la USB

```
USB:\CHISA-Reloj\
└── iclock\
    ├── config.php.example    ← plantilla de configuración
    ├── config.php            ← crear en el cliente (copiar desde .example)
    ├── lib_api.php           ← pegar token API del ERP
    ├── index.php
    ├── ruta_*.php
    ├── validar_entorno.php
    ├── test_erp.php
    ├── windows\
    │   ├── descargar_php.ps1
    │   ├── extraer_php_usb.bat
    │   ├── php-portable-8.3.31-nts-x64.zip   ← O el ZIP oficial (ver abajo)
    │   ├── cacert.pem                         ← Certificados CA para HTTPS (ver Anexo B)
    │   ├── configurar_entorno.bat
    │   ├── iniciar_proxy.bat
    │   ├── iniciar_proxy_oculto.vbs
    │   ├── instalar_tarea_inicio.ps1
    │   ├── desinstalar_tarea_inicio.ps1
    │   └── php\              ← vacío hasta instalar (solo LEEME.txt)
    └── www\
        └── router.php
```

### Cómo obtener el ZIP de PHP para la USB

**Opción A — Descarga manual (recomendada para USB offline):**

1. Abrir [https://windows.php.net/download/](https://windows.php.net/download/)
2. Descargar **PHP 8.3 x64 NTS** (Non Thread Safe), archivo tipo:  
   `php-8.3.31-nts-Win32-vs16-x64.zip`
3. Copiar el ZIP dentro de `iclock\windows\`  
   Puede llamarse `php-portable-8.3.31-nts-x64.zip` **o** conservar el nombre oficial; ambos funcionan.

**Opción B — Script en tu PC (si el cliente tendrá internet):**

1. Clic derecho en `windows\descargar_php.ps1` → **Ejecutar con PowerShell**
2. Si PowerShell bloquea el script: abrir PowerShell como admin y ejecutar  
   `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`
3. El script crea `windows\php\php.exe` en tu PC; en el cliente puedes repetir el mismo paso sin necesidad del ZIP.

> ⚠️ **Certificado CA para HTTPS:** PHP portable no incluye el paquete de certificados raíz.  
> El script `configurar_entorno.bat` intenta descargarlo automáticamente desde  
> [https://curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem).  
> Si falla la descarga, deberás descargarlo manualmente y colocarlo en `windows\php\cacert.pem`.  
> Ver **Anexo B — Certificado SSL (cacert.pem)**.

---

## 4. Instalación en la PC del cliente (sin XAMPP)

### Paso A — Copiar archivos

1. Pegar toda la carpeta `iclock` en:  
   `C:\CHISA\iclock\`
2. No usar rutas como `C:\Users\...\Mis documentos\` ni `Program Files`.

### Paso B — Instalar PHP portable (solo la primera vez)

**Opción 1 — Desde USB, sin internet (recomendada en sitio):**

1. Doble clic en `C:\CHISA\iclock\windows\extraer_php_usb.bat`
2. Esperar mensaje **PHP listo** y `[OK] curl habilitado`
3. Verificar que existe `windows\php\php.exe`

**Opción 2 — Con internet en el cliente:**

1. Clic derecho en `windows\descargar_php.ps1` → **Ejecutar con PowerShell**
2. Ejecutar `windows\configurar_entorno.bat`

**Opción 3 — Manual (resumida):**

1. Extraer el ZIP de PHP dentro de `windows\php\` hasta que exista `windows\php\php.exe`
2. Ejecutar `windows\configurar_entorno.bat`

> Si los scripts fallan, seguir el **Anexo A — Instalación manual de PHP** (paso a paso completo).

### Paso C — Habilitar cURL, extensiones y certificado SSL

1. Ejecutar `windows\configurar_entorno.bat`
2. Debe mostrar `[OK] curl habilitado`
3. Si falla, abrir `windows\php\php.ini` y descomentar:  
   `extension=curl`, `extension=openssl`, `extension=mbstring`

Después de las extensiones, el script configura el **certificado CA** necesario para HTTPS:

- Descarga automáticamente `cacert.pem` desde `https://curl.se/ca/cacert.pem`
- Lo coloca en `windows\php\cacert.pem`
- Agrega en `php.ini` las líneas:
  - `curl.cainfo = "C:\CHISA\iclock\windows\php\cacert.pem"`
  - `openssl.cafile = "C:\CHISA\iclock\windows\php\cacert.pem"`

> ⚠️ **Si el script no pudo descargar el certificado** (PC sin internet en ese momento):
> 1. Descargar manualmente desde [https://curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem)
> 2. Copiar el archivo como `C:\CHISA\iclock\windows\php\cacert.pem`
> 3. Verificar con:
>    ```
>    C:\CHISA\iclock\windows\php\php.exe -r "echo 'curl.cainfo: ' . (ini_get('curl.cainfo') ?: '(vacio)') . PHP_EOL;"
>    ```
>    Debe mostrar la ruta completa al archivo `cacert.pem`.

### Paso D — Configurar serial y token

**1. `config.php`** — si no existe, copiar desde `config.php.example`:

```php
define('MODO_PRUEBA_LOCAL', false);   // false = producción (envía al ERP)
define('RELOJ_SN', 'UDP3252700203');  // = serial en Dispositivos del ERP
define('MODO_SYNC_DEBUG', false);     // false = guarda checadas reales en BD
```

**2. `lib_api.php`** — mismo token que en el ERP:

```php
define('API_BASE', 'https://erp.chisarecubrimientos.com.mx/api/reloj/');
define('API_TOKEN', '...');  // token del dispositivo en el ERP
```

| Variable | Valor en producción |
|----------|---------------------|
| `MODO_PRUEBA_LOCAL` | `false` |
| `MODO_SYNC_DEBUG` | `false` |
| `RELOJ_SN` | Serial exacto del reloj y del ERP |
| `API_TOKEN` | Token del dispositivo en el ERP |

### Paso E — Probar visible (una sola vez, técnico)

1. Abrir **Símbolo del sistema como administrador** (necesario para puerto 80)
2. Ejecutar:

```bat
C:\CHISA\iclock\windows\iniciar_proxy.bat
```

3. En el navegador **de esa PC** (solo para prueba del técnico):

| URL | Qué valida |
|-----|------------|
| `http://localhost/iclock/validar_entorno.php` | cURL, permisos, API status, sync |
| `http://localhost/iclock/test_erp.php` | Los 4 endpoints del ERP |
| `http://localhost/iclock/test_conexion.php` | Solo ping a `status` |

4. Deben aparecer marcas ✅ en cURL, API y sync.

**Si el puerto 80 está ocupado** (IIS, Skype, XAMPP antiguo):

1. Editar `windows\iniciar_proxy.bat` → cambiar `set PORT=80` por `set PORT=8080`
2. Configurar el reloj con **puerto 8080**
3. Desinstalar o detener XAMPP si ya no se usa

### Paso F — Firewall de Windows

Permitir que `php.exe` reciba conexiones de la red local:

1. **Panel de control → Firewall de Windows → Permitir una aplicación**
2. Agregar `C:\CHISA\iclock\windows\php\php.exe` → marcar **Red privada**

O en PowerShell **como administrador**:

```powershell
New-NetFirewallRule -DisplayName "CHISA Proxy Reloj PHP" `
  -Direction Inbound -Program "C:\CHISA\iclock\windows\php\php.exe" `
  -Action Allow -Profile Private
```

### Paso G — Dejar invisible para el usuario (producción)

El usuario **no debe** abrir XAMPP ni ningún navegador. El proxy corre en segundo plano.

**Opción recomendada — Tarea al iniciar Windows:**

1. Clic derecho en `windows\instalar_tarea_inicio.ps1` → **Ejecutar con PowerShell como administrador**
2. Se crea la tarea `CHISA_Proxy_Reloj_ZKTeco` (sin ventana, sin navegador)
3. Reiniciar la PC
4. Comprobar que el reloj sincroniza (última conexión en el ERP o checada de prueba)

**Verificar que el proxy está activo:**

```bat
netstat -an | findstr ":80 "
```

Debe aparecer `0.0.0.0:80` en LISTENING.

**Alternativa manual:** acceso directo a `iniciar_proxy_oculto.vbs` en la carpeta Inicio (`Win+R` → `shell:startup`).

---

## Anexo A — Instalación manual de PHP (si falla la autoinstalación)

Usa este apartado cuando `extraer_php_usb.bat`, `descargar_php.ps1` o `configurar_entorno.bat` no funcionen (PowerShell bloqueado, ZIP corrupto, antivirus, PC sin permisos, etc.).

### ¿Cuándo aplicar el plan B?

| Señal | Qué significa |
|-------|----------------|
| `extraer_php_usb.bat` se cierra sin mensaje | PowerShell bloqueado o ZIP no encontrado |
| `ERROR: no se encontró php.exe tras extraer` | ZIP mal extraído o carpeta anidada |
| `configurar_entorno.bat` dice que no existe `php.exe` | PHP no está en la ruta correcta |
| `php.exe` no es reconocido / no abre | Falta Visual C++ o arquitectura incorrecta (x86 vs x64) |
| `curl` no aparece en las pruebas | `php.ini` sin extensiones habilitadas |

### Paso 1 — Descargar PHP a mano

1. En la PC del cliente (o en tu PC y copiar a USB), abrir:  
   [https://windows.php.net/download/](https://windows.php.net/download/)
2. En la fila **PHP 8.3** (o la 8.2 más reciente si 8.3 no aparece), elegir:
   - **VS16 x64 Non Thread Safe** (abreviado **NTS**)
   - **Zip** (no el instalador MSI ni la versión Thread Safe **TS**)
3. El archivo se llama parecido a:  
   `php-8.3.31-nts-Win32-vs16-x64.zip`
4. Guardar el ZIP en `C:\CHISA\iclock\windows\` (o en la USB).

> **Importante:** usar **NTS** (Non Thread Safe) y **x64**. El proxy no usa Apache; la versión TS no aplica aquí.

### Paso 2 — Extraer en la carpeta correcta

La ruta final **debe** quedar así (note que `php.exe` está directamente dentro de `php\`):

```
C:\CHISA\iclock\windows\php\
├── php.exe          ← obligatorio
├── php.ini-development
├── php.ini-production
├── ext\             ← carpeta de extensiones (.dll)
│   ├── php_curl.dll
│   ├── php_openssl.dll
│   └── ...
└── ... (otros archivos del ZIP)
```

**Procedimiento con el Explorador de Windows:**

1. Abrir `C:\CHISA\iclock\windows\`
2. Si ya existe una carpeta `php` con archivos rotos, **renombrarla** a `php_viejo` o borrarla
3. Crear carpeta nueva: `C:\CHISA\iclock\windows\php`
4. Clic derecho en el ZIP → **Extraer todo…**
5. Si al extraer queda `windows\php\php-8.3.31-nts-Win32-vs16-x64\php.exe` (carpeta intermedia), **mover todo el contenido** de esa subcarpeta hacia `windows\php\`
6. Confirmar que existe: `C:\CHISA\iclock\windows\php\php.exe`

### Paso 3 — Crear y editar `php.ini`

1. Dentro de `C:\CHISA\iclock\windows\php\`, copiar el archivo:
   - De: `php.ini-development`
   - A: `php.ini` (mismo nombre, sin `-development`)
2. Abrir `php.ini` con **Bloc de notas**
3. Buscar (`Ctrl+B`) y modificar estas líneas (quitar el `;` al inicio si está comentado):

```ini
extension_dir = "ext"

extension=curl
extension=openssl
extension=mbstring
extension=fileinfo
```

4. Guardar y cerrar.

> Si `extension_dir` aparece como `;extension_dir = "./ext"`, descoméntala y déjala como `extension_dir = "ext"`.

### Paso 4 — Verificar desde la consola

Abrir **Símbolo del sistema** y ejecutar:

```bat
cd /d C:\CHISA\iclock\windows\php
php.exe -v
php.exe -m | findstr /i curl
```

**Resultado esperado:**

- `php.exe -v` muestra algo como `PHP 8.3.x (nts) ...`
- El segundo comando muestra la palabra `curl`

Si `curl` no aparece, revisar de nuevo `php.ini` y que existan `ext\php_curl.dll` y `ext\php_openssl.dll`.

### Paso 5 — Probar el proxy manualmente

```bat
cd /d C:\CHISA\iclock\windows
iniciar_proxy.bat
```

Abrir en el navegador: `http://localhost/iclock/validar_entorno.php`  
Debe mostrar ✅ en **php-curl** y en la conexión al ERP.

### Paso 6 — Continuar la guía normal

Una vez PHP funcione a mano:

1. Seguir desde la **sección 4, Paso D** (configurar `config.php` y `lib_api.php`)
2. Luego **Paso F** (firewall) y **Paso G** (tarea de inicio automático)

No hace falta volver a ejecutar los scripts de autoinstalación si ya verificaste `php.exe` y `curl` manualmente.

### Errores frecuentes al instalar a mano

| Error | Solución |
|-------|----------|
| *“VCRUNTIME140.dll no se encuentra”* | Instalar [Microsoft Visual C++ Redistributable x64](https://learn.microsoft.com/es-es/cpp/windows/latest-supported-vc-redist) |
| `php.exe` en subcarpeta, no en `windows\php\` | Mover archivos un nivel arriba (ver Paso 2) |
| Descargué **TS** en lugar de **NTS** | Borrar `php\` y bajar el ZIP **Non Thread Safe** |
| Descargué **x86** en PC de 64 bits | Borrar `php\` y bajar el ZIP **x64** |
| Antivirus borra `php.exe` | Agregar exclusión para `C:\CHISA\iclock\` |
| Puerto 80: *“Address already in use”* | Cambiar `PORT=8080` en `iniciar_proxy.bat` y en el reloj |
| `configurar_entorno.bat` falla pero PHP ya funciona | Puedes omitirlo si `php.ini` quedó bien (Pasos 3 y 4) |

### Estructura mínima para confirmar que todo está bien

Antes de irte del cliente, comprueba esta lista:

- [ ] Existe `C:\CHISA\iclock\windows\php\php.exe`
- [ ] Existe `C:\CHISA\iclock\windows\php\php.ini` con `extension=curl`
- [ ] Existe `C:\CHISA\iclock\windows\php\cacert.pem` (certificado CA)
- [ ] `php.exe -m` lista `curl`
- [ ] `validar_entorno.php` muestra ✅ en cURL y API
- [ ] `iniciar_proxy.bat` deja el puerto en escucha (`netstat`)
- [ ] Tarea `CHISA_Proxy_Reloj_ZKTeco` instalada (o acceso directo en Inicio)

---

## Anexo B — Certificado SSL (cacert.pem)

PHP portable en Windows **no incluye** el paquete de certificados raíz de autoridades certificadoras (CA).  
Sin este archivo, las conexiones HTTPS fallan con el error:

```
SSL certificate problem: unable to get local issuer certificate
```

### ¿Qué es `cacert.pem`?

Es un archivo de texto que contiene los certificados raíz de las principales autoridades certificadoras (DigiCert, Let's Encrypt, GlobalSign, etc.). cURL y OpenSSL lo usan para verificar que el certificado SSL del servidor (`erp.chisarecubrimientos.com.mx`) sea legítimo y de confianza.

**Descarga oficial:** [https://curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem)  
(Es el mismo bundle que usa Mozilla Firefox, mantenido por el proyecto cURL/ca-certificates.)

### ¿Dónde debe colocarse?

```
C:\CHISA\iclock\windows\php\cacert.pem
```

### ¿Cómo se configura en `php.ini`?

Dentro de `C:\CHISA\iclock\windows\php\php.ini`, deben existir estas líneas **sin** punto y coma al inicio:

```ini
curl.cainfo = "C:\CHISA\iclock\windows\php\cacert.pem"
openssl.cafile = "C:\CHISA\iclock\windows\php\cacert.pem"
```

### ¿Cómo verifico que está funcionando?

```bat
C:\CHISA\iclock\windows\php\php.exe -r "echo 'curl.cainfo: ' . (ini_get('curl.cainfo') ?: '(vacio)') . PHP_EOL;"
```

**Respuesta esperada:**
```
curl.cainfo: C:\CHISA\iclock\windows\php\cacert.pem
```

Si aparece `(vacio)`, el certificado no está configurado.

### Instalación automática (recomendada)

El script `configurar_entorno.bat` ya incluye:

1. Descarga de `cacert.pem` desde `https://curl.se/ca/cacert.pem`
2. Configuración de `curl.cainfo` y `openssl.cafile` en `php.ini`

Ejecutarlo como administrador es suficiente.

### Instalación manual (si falla el script)

| Paso | Acción |
|------|--------|
| 1 | Abrir [https://curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem) en el navegador |
| 2 | Guardar la página como `cacert.pem` (texto plano, no HTML) |
| 3 | Copiar el archivo a `C:\CHISA\iclock\windows\php\cacert.pem` |
| 4 | Editar `C:\CHISA\iclock\windows\php\php.ini` y agregar las 2 líneas de `curl.cainfo` y `openssl.cafile` |
| 5 | Verificar con el comando de prueba de arriba |

> ⚠️ **Importante:** Al descargar desde el navegador, asegúrate de que el archivo **no** tenga extensión `.txt` ni contenido HTML.  
> El archivo debe comenzar con `-----BEGIN CERTIFICATE-----` (son ~250 KB de texto plano).

### Incluir en la USB (para instalación sin internet)

Si preparas la USB antes de ir al cliente, puedes dejar el certificado ya descargado:

1. Descargar [cacert.pem](https://curl.se/ca/cacert.pem)
2. Copiarlo a la USB en: `iclock\windows\php\cacert.pem`

Así el cliente no necesitará internet para este paso.

---

## 5. Configurar el reloj ZKTeco

En el menú del dispositivo (o software ZKBio / ADMS):

| Campo | Valor ejemplo |
|-------|----------------|
| Servidor / IP | `192.168.1.50` (IP fija de la PC proxy) |
| Puerto | `80` (o `8080` si cambió el `.bat`) |
| Ruta / URL | `/iclock/cdata` (modo push ADMS estándar) |
| Serial (SN) | Debe coincidir con `RELOJ_SN` y el ERP |

Flujo automático del reloj:

- **POST** checadas → `/iclock/cdata` → ERP
- **GET** comandos → `/iclock/getrequest` → ERP
- **POST** resultado → `/iclock/devicecmd` → ERP

---

## 6. Comandos al reloj (usuarios, borrado, etc.)

No se encolan en el proxy. Se gestionan desde el ERP:

**Recursos Humanos → Reloj Checador → Dispositivos → Comandos**

El proxy solo **consulta** al ERP si hay comandos cuando el reloj hace `getrequest`.

---

## 7. Diagnóstico rápido

| Síntoma | Revisar |
|---------|---------|
| Reloj no conecta | IP de la PC, firewall, puerto 80/8080, proxy en ejecución (`netstat`) |
| HTTP 401/403 en pruebas | Token o SN incorrectos en `lib_api.php` / `config.php` |
| Checadas no aparecen en ERP | `MODO_PRUEBA_LOCAL` debe ser `false` |
| Comandos no bajan al reloj | Cola en ERP → Dispositivos; el reloj debe hacer polling `getrequest` |
| Puerto 80 en uso | IIS, Skype, XAMPP viejo — usar 8080 o desinstalar XAMPP |
| PowerShell no ejecuta scripts | `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned` |
| `php.exe` no encontrado | Ver **Anexo A** (instalación manual de PHP) |
| Autoinstalación falla | **Anexo A** — descarga, extracción y `php.ini` a mano |
| Error SSL: *“SSL certificate problem”* o *“unable to get local issuer certificate”* | Falta `cacert.pem` en `windows\php\`. Descargar desde [curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem) y verificar `curl.cainfo` en `php.ini` (ver **Anexo B**) |

### Archivos de prueba (solo técnico)

| URL local | Uso |
|-----------|-----|
| `/iclock/validar_entorno.php` | cURL, permisos, API |
| `/iclock/test_erp.php` | Prueba los endpoints del ERP |
| `/iclock/test_conexion.php` | Solo ping a `status` |

### Monitor en el ERP (solo debug)

- `https://erp.chisarecubrimientos.com.mx/api/reloj/monitor`  
  (útil si `MODO_SYNC_DEBUG = true` en pruebas)

---

## 8. Por qué no usamos XAMPP

| XAMPP (antes) | PHP portable (ahora) |
|---------------|----------------------|
| Instala Apache + MySQL + panel gráfico | Solo `php.exe` (~30 MB) |
| Icono y ventanas visibles | `iniciar_proxy_oculto.vbs` sin ventana |
| El usuario debe “abrir XAMPP” cada día | Tarea programada al encender Windows |
| Más software instalado | Un solo proceso HTTP (`php -S`) |
| Navegador / panel expuestos | El usuario final no interactúa con nada |

PHP incluye servidor web embebido (`php -S`) suficiente para el protocolo ADMS del reloj. El archivo `www\router.php` mantiene las mismas URLs `/iclock/...` que antes con Apache.

---

## 9. Desinstalar

1. Ejecutar `windows\desinstalar_tarea_inicio.ps1` (como administrador)
2. Eliminar `C:\CHISA\iclock`
3. Opcional: quitar regla de firewall de `php.exe`
4. Opcional: desinstalar XAMPP si quedó de una instalación anterior

---

## 10. Seguridad

- No compartir `API_TOKEN` por correo ni subirlo a repositorios públicos
- La PC proxy solo necesita **salida** HTTPS al ERP; no exponer puertos a internet
- Mantener `MODO_SYNC_DEBUG = false` en producción
- Usar IP fija en LAN; el reloj no necesita acceso desde fuera de la red local

---

## 11. Referencias técnicas

| Documento | Ubicación |
|-----------|-----------|
| API del reloj | `API_RELOJ_CHECADOR.md` (raíz del proyecto ERP) |
| Reglas del proxy | `REGLAS_TECNICAS.md` → sección *iclock/* |
| Scripts Windows | `iclock/windows/` |
| PHP manual (plan B) | **Anexo A** de esta guía |
| Certificado CA (cacert.pem) | **Anexo B** de esta guía |
| cURL CA bundle oficial | [https://curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem) |

---

## Contacto técnico

Ante fallos persistentes, reunir captura de `validar_entorno.php`, salida de `test_erp.php` y serial del reloj registrado en el ERP.
