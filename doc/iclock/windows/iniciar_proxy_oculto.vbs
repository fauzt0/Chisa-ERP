' Inicia el proxy sin ventana de consola ni navegador (sustituto invisible de XAMPP)
Set WshShell = CreateObject("WScript.Shell")
scriptDir = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)
bat = scriptDir & "\iniciar_proxy.bat"
WshShell.Run """" & bat & """", 0, False
