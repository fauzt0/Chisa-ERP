@echo off
:: Extrae PHP desde el ZIP incluido en la USB (sin internet)
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0descargar_php.ps1"
if errorlevel 1 exit /b 1
call "%~dp0configurar_entorno.bat"
