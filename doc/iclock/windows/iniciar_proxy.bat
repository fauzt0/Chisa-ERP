@echo off
setlocal
cd /d "%~dp0"

set PHP=%~dp0php\php.exe
set WWW=%~dp0..\www
set ROUTER=%WWW%\router.php
set PORT=80

if not exist "%PHP%" (
    echo PHP no instalado. Ejecute descargar_php.ps1 y configurar_entorno.bat
    exit /b 1
)

if not exist "%ROUTER%" (
    echo Falta www\router.php
    exit /b 1
)

:: Puerto 80 requiere permisos de administrador. Si falla, cambie PORT a 8080
:: y configure el reloj con puerto 8080 en opciones ADMS.
"%PHP%" -S 0.0.0.0:%PORT% -t "%WWW%" "%ROUTER%"
