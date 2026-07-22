# Descarga PHP portable para Windows (NTS x64) dentro de iclock\windows\php
# Ejecutar en la PC del cliente con PowerShell (clic derecho → Ejecutar con PowerShell)
# Si ya trae el ZIP en la USB (php-portable-*.zip), no necesita internet.

$ErrorActionPreference = 'Stop'
$ScriptDir = $PSScriptRoot
$PhpDir = Join-Path $ScriptDir 'php'
$PhpExe = Join-Path $PhpDir 'php.exe'

Write-Host "=== CHISA — Instalar PHP portable ===" -ForegroundColor Cyan
Write-Host "Destino: $PhpDir"

if (Test-Path $PhpExe) {
    Write-Host "php.exe ya existe. Si quieres reinstalar, borra windows\php y vuelve a ejecutar." -ForegroundColor Yellow
    exit 0
}

New-Item -ItemType Directory -Force -Path $PhpDir | Out-Null

# 1) ZIP incluido en USB (sin internet) — acepta nombre portable o el de windows.php.net
$ZipLocal = Get-ChildItem -Path $ScriptDir -Filter 'php-portable-*.zip' | Sort-Object LastWriteTime -Descending | Select-Object -First 1
if (-not $ZipLocal) {
    $ZipLocal = Get-ChildItem -Path $ScriptDir -Filter 'php-*-nts-Win32-vs16-x64.zip' | Sort-Object LastWriteTime -Descending | Select-Object -First 1
}
$ZipPath = $null

if ($ZipLocal) {
    Write-Host "Usando ZIP local: $($ZipLocal.Name)" -ForegroundColor Green
    $ZipPath = $ZipLocal.FullName
} else {
    $PhpVersion = '8.3.31'
    $ZipName = "php-$PhpVersion-nts-Win32-vs16-x64.zip"
    $Url = "https://windows.php.net/downloads/releases/$ZipName"
    $ZipPath = Join-Path $env:TEMP 'php-portable-chisa.zip'
    Write-Host "Descargando $Url ..."
    Invoke-WebRequest -Uri $Url -OutFile $ZipPath -UseBasicParsing
}

Write-Host "Extrayendo..."
Expand-Archive -Path $ZipPath -DestinationPath $PhpDir -Force

if (-not (Test-Path $PhpExe)) {
    $nested = Get-ChildItem $PhpDir -Directory | Select-Object -First 1
    if ($nested) {
        Get-ChildItem $nested.FullName | Move-Item -Destination $PhpDir -Force
        Remove-Item $nested.FullName -Recurse -Force
    }
}

if ($ZipPath -like "$env:TEMP\*") {
    Remove-Item $ZipPath -Force -ErrorAction SilentlyContinue
}

if (-not (Test-Path $PhpExe)) {
    Write-Host "ERROR: no se encontró php.exe tras extraer." -ForegroundColor Red
    exit 1
}

Write-Host "PHP listo en $PhpDir" -ForegroundColor Green
Write-Host "Siguiente paso: ejecutar configurar_entorno.bat"
