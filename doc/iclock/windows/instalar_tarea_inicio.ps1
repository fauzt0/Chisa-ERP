# Registra el proxy para que arranque al encender Windows (sin ventana visible)
# Ejecutar como Administrador: clic derecho → Ejecutar con PowerShell como administrador

$ErrorActionPreference = 'Stop'
$TaskName = 'CHISA_Proxy_Reloj_ZKTeco'
$Vbs = Join-Path $PSScriptRoot 'iniciar_proxy_oculto.vbs'
$Wscript = Join-Path $env:Windir 'System32\wscript.exe'

if (-not (Test-Path $Vbs)) {
    Write-Host "No se encuentra iniciar_proxy_oculto.vbs" -ForegroundColor Red
    exit 1
}

$Action = New-ScheduledTaskAction -Execute $Wscript -Argument "`"$Vbs`""
$Trigger = New-ScheduledTaskTrigger -AtStartup
$Principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
$Settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger -Principal $Principal -Settings $Settings -Force

Write-Host "Tarea '$TaskName' instalada. El proxy iniciará al arrancar Windows." -ForegroundColor Green
Write-Host "Para probar ahora: doble clic en iniciar_proxy_oculto.vbs"
Write-Host "Para quitar: ejecutar desinstalar_tarea_inicio.ps1"
