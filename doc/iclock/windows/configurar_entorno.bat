@echo off
setlocal
cd /d "%~dp0"

set PHP_DIR=%~dp0php
set PHP_INI=%PHP_DIR%\php.ini
set CACERT_PEM=%PHP_DIR%\cacert.pem
set CACERT_URL=https://curl.se/ca/cacert.pem

if not exist "%PHP_DIR%\php.exe" (
    echo [ERROR] No existe php.exe en windows\php
    echo Ejecuta primero: descargar_php.ps1
    pause
    exit /b 1
)

if not exist "%PHP_INI%" (
    copy /Y "%PHP_DIR%\php.ini-development" "%PHP_INI%" >nul
    echo Creado php.ini desde plantilla development
)

echo --- Configurando php.ini ---

powershell -NoProfile -Command ^
  "$ini = '%PHP_INI%';" ^
  "$c = Get-Content $ini -Raw;" ^
  "$c = $c -replace ';extension_dir = \"ext\"', 'extension_dir = \"ext\"';" ^
  "$c = $c -replace ';extension=curl', 'extension=curl';" ^
  "$c = $c -replace ';extension=openssl', 'extension=openssl';" ^
  "$c = $c -replace ';extension=mbstring', 'extension=mbstring';" ^
  "$c = $c -replace ';extension=fileinfo', 'extension=fileinfo';" ^
  "$c = $c -replace ';curl.cainfo =', 'curl.cainfo =';" ^
  "$c = $c -replace 'curl.cainfo =.*', 'curl.cainfo = \"%CACERT_PEM:\=\\%\"';" ^
  "$c = $c -replace ';openssl.cafile=', 'openssl.cafile=';" ^
  "$c = $c -replace 'openssl.cafile=.*', 'openssl.cafile = \"%CACERT_PEM:\=\\%\"';" ^
  "Set-Content -Path $ini -Value $c -Encoding ASCII"

echo.
echo Verificando extensiones...
"%PHP_DIR%\php.exe" -m | findstr /i curl
if errorlevel 1 (
    echo [AVISO] curl no aparece en php -m. Revisa php.ini manualmente.
) else (
    echo [OK] curl habilitado
)

echo.
echo --- Certificado CA para HTTPS ---

if exist "%CACERT_PEM%" (
    echo [OK] cacert.pem ya existe en windows\php\
) else (
    echo Descargando cacert.pem desde %CACERT_URL% ...
    powershell -NoProfile -Command ^
      "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12;" ^
      "try { Invoke-WebRequest -Uri '%CACERT_URL%' -OutFile '%CACERT_PEM%' -UseBasicParsing; exit 0 } catch { exit 1 }"
    if exist "%CACERT_PEM%" (
        echo [OK] cacert.pem descargado correctamente
    ) else (
        echo [AVISO] No se pudo descargar cacert.pem automaticamente.
        echo         Si ves errores SSL en la validacion, descargalo manualmente desde:
        echo         %CACERT_URL%
        echo         Y guardalo en: %CACERT_PEM%
    )
)

echo.
echo Verificando configuracion SSL...
"%PHP_DIR%\php.exe" -r "echo 'curl.cainfo: ' . (ini_get('curl.cainfo') ?: '(vacio)') . PHP_EOL;"
"%PHP_DIR%\php.exe" -r "echo 'openssl.cafile: ' . (ini_get('openssl.cafile') ?: '(vacio)') . PHP_EOL;"

echo.
echo Listo. Puedes iniciar el proxy con iniciar_proxy_oculto.vbs
pause
