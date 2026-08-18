# PLAN DE PRUEBAS MANUALES — MÓDULO RECURSOS HUMANOS (10 agosto 2026)

> **Alcance:** Módulo `rh/` completo (Empleados, Expediente, Contratos, Plantillas, Vacaciones, Incidencias, Horarios, Reloj Checador local, Nómina con incidencias, Comunicación, Departamentos).
>
> **Referencia:** `doc/cotizacion.md` (punto 2 — MÓDULO "Recursos Humanos") · `doc/VALIDACION_NOMINA_2026-08-04.md` (validación previa de nómina).
>
> **Fuera de alcance:** Reloj Checador Remoto en obras (API KONECT®/Bixpe — punto 3 de la cotización) **omitido por instrucción del cliente/equipo**.
>
> **Entorno:** `https://erp.chisarecubrimientos.com.mx` (producción) · PHP 8.2 · CodeIgniter 3.
>
> **Ejecutor:** Usuario (QA manual).

---

## 1. Mapeo cotización → implementación (Gap Analysis)

Estado verificado el 10-ago-2026 revisando código (`application/controllers/rh/*`, `application/models/RH/*`, vistas) y BD.

| # | Requisito de la cotización (punto 2) | Estado | Dónde está implementado | Cómo probarlo |
|:-:|:-------------------------------------|:------:|:------------------------|:--------------|
| 1 | Conexión directa con módulo "Administración de Usuarios" (usuarios = trabajadores) | ✅ | `rh/RecursosHumanos` — modal "Vincular usuario", "Usuarios sin empleado", "Crear usuario desde empleado", "Crear empleado desde usuario" (`EmpleadoUsuarioModel`) | Bloque D |
| 2 | Búsquedas por filtros: nombre, correo, Id usuario, puesto | ✅ | `search_empleados()` + DataTable en `main_empleados.php` | Bloque A |
| 3 | Listado de trabajadores con datos de contacto | ✅ | Tabla principal de `main_empleados.php` | Bloque A |
| 4 | Editar trabajador: NSS, RFC, horarios, turnos, etc. | ✅ | `editar.php` (48+ campos) + `horarios_empleados` | Bloque C |
| 5 | Contratos: agregar, editar, consultar | ✅ | `nuevo_contrato()`, `ver_contrato()`, `historial_contratos()` (`ContratoModel`) | Bloques F/G |
| 6 | Plantillas/machotes con versión, quién creó/autorizó, histórico en árbol | ✅ | `contrato_plantillas` + `PlantillaModel` (crear/duplicar/desactivar) + contratos con `version` auto-increment, `motivo_cambio`, `creado_por`, `vigente` | Bloques F/G |
| 7 | Documentación: contratos, renuncias, cartas de baja, finiquitos, liquidaciones (agregar/consultar/editar) | ✅ | `empleados_documentos` + `documento_subir()`/`documento_eliminar()` + checklist (`get_checklist_empleado`) | Bloque E |
| 8 | Sistema **no** realiza cálculos de finiquitos ni liquidaciones | ✅ | No existe módulo de cálculo (la "calculadora de baja" solo muestra antigüedad/datos informativos) | — |
| 9 | Cargar reportes del reloj checador biométrico (entrada/salida, horarios de comida) | ✅ local | `rh/RelojChecador` (dispositivos, sync, reporte diario/mensual, asistencias) | Bloque L |
| 10 | Registrar y procesar incidencias: faltas, HE, vacaciones, incapacidades | ✅ | `incidencias_empleados` + `IncidenciasModel` (Retardo, Falta, Falta Justificada, Permiso, Incapacidad, Suspensión, Amonestación, Renuncia, Otro + **Horas Extras**) | Bloque I |
| 11 | Incidencias se reflejan en el cálculo de nómina | ✅ | `NominaRhModel::calcular_conceptos_empleado()` lee `get_incidencias_nomina_periodo()`; HE→percepción, descuentos→deducción; `marcar_procesadas_periodo()` | Bloque K |
| 12 | Conexión con módulo "Contabilidad" | ⚠️ Parcial | Nómina integrada (cálculo automático salarios/deducciones/neto, exporta Excel para Aspel COI/NOI). La póliza contable automática depende del periodo contable (ver observación de validación previa) | Bloque K |

**Extras implementados (no cotizados pero presentes):** vacaciones con balance por antigüedad LFT y solicitudes con aprobación (Bloque H), comunicación interna (mensajes/tareas, Bloque M), departamentos (Bloque N).

---

## 2. Hallazgos

| # | Severidad | Hallazgo | Detalle | Acción |
|:-:|:---------:|:---------|:--------|:-------|
| H1 | 🔴 **Crítico** | El enum `incidencias_empleados.tipo_incidencia` **no incluía "Horas Extras"** | La UI ofrece la opción "Horas Extras" (main_empleados línea 794) y el cálculo de nómina espera `tipo_incidencia === 'Horas Extras'` (NominaRhModel línea 842), pero el enum de BD era `('Retardo','Falta','Falta Justificada','Permiso','Incapacidad','Suspensión','Amonestación','Renuncia','Otro')`. Con MySQL en `STRICT_TRANS_TABLES`, el INSERT fallaba (error 1265). | ✅ **Corregido el 17 ago 2026:** `ALTER TABLE incidencias_empleados MODIFY tipo_incidencia ENUM(...,'Horas Extras',...)` aplicado y verificado en BD de producción (ver `database/incidencias_empleados_tipo_horas_extras.sql`, commit `c258e7b`). |
| H2 | 🟡 Menor | Incidencia tipo "Vacaciones" no existe en el enum | La cotización menciona "vacaciones" como incidencia, pero el sistema las gestiona en su módulo propio (balance + solicitudes con aprobación), lo cual cubre el requisito. | Ninguna (correcto). |
| O1 | Info | `aplicar_isr = 0` en `nomina_configuracion` | **Confirmado por el usuario: intencional** — el ISR se calcula en Aspel NOI. | Ninguna. |
| O2 | Info | Reloj checador en obras (API KONECT®/Bixpe) | Fuera de alcance por instrucción. | Ninguna. |
| O3 | Info | `empleados_documentos` está vacía (0 registros) | El expediente se puede llenar durante la prueba (Bloque E). | Prueba lo crea. |
| O4 | Info | Solo 2 incidencias demo (Falta Justificada) | Suficiente para probar, crear más en el Bloque I. | Prueba lo crea. |
| O5 | Info | Solo 2 plantillas de contrato | El cliente proveerá el contenido real (Nota1 de la cotización). | Prueba crea una más. |

---

## 3. Prerrequisitos

1. **Usuario de prueba** con permisos: `rh_empleados_consult`, `rh_empleados_add`, `rh_empleados_edit`, `rh_empleados_delete`, `rh_departamentos`, `rh_nomina`, `rh_nomina_configurar`, `rh_nomina_editar_detalle`, `rh_nomina_cuentas`, `rh_nomina_exportar`. *(El usuario `presentacion@chisa.mx` solo tiene los 5 de nómina; verificar si tiene los de empleados.)*
2. DevTools abiertas (F12 → pestaña Network) para monitorear AJAX.
3. **Datos actuales para referencia:**
   - 18 empleados: **activos** (estatus 1): 1 EHWEB, 2 Pedro Lopez, 3 Ana Karina Roman, 4 Maria Pilar, 5 Esahu Enrique, 6 Teodoro Jimenez, 9 Mauro Avila, 10 Miguel Ivan, 12 Francisco Martinez, 14 Rigo Nevarez, 15 Miguel (con costo HE $136.66/hr), 16 Oscar Galindo, 17 Iliana Quezada, 18 María del Carmen. **Inactivos** (estatus 2): 7 Jorge, 8 Marcelo, 11 Carolina, 13 Gerardo.
   - 28 contratos (varios empleados con historial), 2 plantillas, 28 horarios, 2 incidencias, 3 solicitudes de vacaciones, 2 periodos de vacaciones, 98 asistencias, 2 dispositivos reloj, 12 departamentos.
   - Nómina vigente: **NOM000027** (10–16 ago 2026, Calculada, neto $84,939.14).
4. **Corrección H1 aplicada** (17 ago 2026) — el enum de BD ya incluye `'Horas Extras'`; el flujo HE → nómina puede probarse directamente.

---

## 4. Bloques de prueba

> Leyenda: ✅ = pasar · ⚠️ = observar · ❌ = falla (reportar)

### Bloque A — Listado y búsqueda de empleados (`/rh/RecursosHumanos`)

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| A1 | Navegar a `/rh/RecursosHumanos` | ✅ La página carga; tabla con los 18 empleados (columnas: foto, nombre, puesto, depto, contacto, estatus) |
| A2 | Buscar `ana` en el buscador | ✅ Filtra y muestra a Ana Karina Roman |
| A3 | Buscar `contable` (puesto) | ✅ Muestra a Pedro Lopez (Auxiliar Contable) |
| A4 | Buscar `3` (Id) o `esahu@` (correo) | ✅ Encuentra por ID o email |
| A5 | Buscar un término sin resultados (`zzz`) | ✅ "No se encontraron registros" o vacío (sin error) |
| A6 | Clic en la fila de un empleado (ej. Ana Karina) | ✅ Se abre el detalle con 4 pestañas: **Personal, Fiscal, Laboral, Documentos** |
| A7 | Cambiar "Mostrar X entradas" | ✅ Funciona sin iconos encimados |

### Bloque B — Alta de empleado (`/rh/RecursosHumanos/alta`)

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| B1 | Navegar a `/rh/RecursosHumanos/alta` | ✅ Formulario completo (datos personales, fiscales, laborales, nómina) |
| B2 | Llenar campos mínimos: nombre, apellidos, fecha nacimiento, RFC, CURP, NSS, puesto, depto, salarios, fecha ingreso | ✅ Sin errores de validación de formato (RFC 13 chars, CURP 18, NSS 11) |
| B3 | Escribir un **RFC inválido** (ej. `ABC123`) y enviar | ✅ Validación rechaza: "El RFC no tiene un formato válido" |
| B4 | Escribir un **CURP inválido** y enviar | ✅ Validación rechaza |
| B5 | Escribir un **NSS inválido** y enviar | ✅ Validación rechaza |
| B6 | Corregir y guardar | ✅ Toast verde "Empleado registrado"; aparece en el listado; se generó `numero_empleado` y contrato inicial automático (verificar en Bloque G) |
| B7 | Intentar registrar el **mismo RFC/CURP/NSS** de nuevo | ✅ Alerta de duplicado (no permite duplicar) |
| B8 | (Opcional) Validar RFC existente en `https://www.sat.gob.mx` | ⚠️ La validación `validar_rfc_ajax` (si existe el endpoint) confirma formato |

### Bloque C — Edición de empleado (`/rh/RecursosHumanos/editar/{id}`)

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| C1 | Abrir `editar/3` (Ana Karina) | ✅ Formulario precargado con todos los datos |
| C2 | Cambiar teléfono y guardar | ✅ Toast verde; valor persiste al recargar |
| C3 | Cambiar `tipo_nomina` / `forma_pago` (ej. Depósito) | ✅ Se guarda; en el detalle aparece el nuevo badge |
| C4 | Cambiar `salario_base_mensual` (anotar valor original) y guardar | ✅ En el detalle el salario diario/mensual se recalcula; **restaurar el valor original** |
| C5 | Cambiar estatus a `2` (inactivo) y guardar, luego restaurar a `1` | ✅ El empleado desaparece/aparece según estatus en el listado |
| C6 | Cargar **foto de perfil** (si aplica) | ✅ Se muestra en el listado y detalle |

### Bloque D — Conexión con módulo Usuarios (vinculación bidireccional)

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| D1 | En el detalle de un empleado, pestaña Laboral → botón "Vincular usuario" | ✅ Se abre modal de búsqueda de usuarios |
| D2 | Buscar un usuario existente (ej. `presentacion@chisa.mx`) y vincular | ✅ Toast verde; el usuario aparece vinculado en el detalle |
| D3 | Clic en "Usuarios sin empleado" | ✅ Lista usuarios del ERP sin trabajador asociado |
| D4 | Seleccionar uno y "Crear empleado desde usuario" | ✅ Se crea el empleado con datos del usuario |
| D5 | En un empleado sin usuario: "Crear usuario desde empleado" | ✅ Formulario (username, password, rol); al guardar el usuario se crea en `administradores` |
| D6 | Desvincular usuario del empleado | ✅ Confirma y desvincula sin borrar ninguno de los dos |

### Bloque E — Expediente y documentos

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| E1 | En el detalle de un empleado → pestaña **Documentos** | ✅ Checklist de documentos requeridos (Acta nacimiento, CURP, RFC, NSS, INE, Comprobante domicilio) con estado |
| E2 | Clic en "Subir documento" → tipo `Acta de nacimiento`, adjuntar PDF | ✅ Sube; aparece en la lista con nombre, tamaño, fecha; el checklist se actualiza |
| E3 | Subir un documento con tipo libre (ej. `Carta de baja`) | ✅ Aparece el label del tipo |
| E4 | Clic en el documento | ✅ Se abre/descarga el archivo |
| E5 | Eliminar el documento (icono papelera) | ✅ Confirma y desaparece de la lista |
| E6 | Verificar en BD (opcional): `SELECT * FROM empleados_documentos` | ✅ Registros con ruta y tipo correctos |

### Bloque F — Plantillas de contratos (`/rh/RecursosHumanos/plantillas`)

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| F1 | Navegar a `/rh/RecursosHumanos/plantillas` | ✅ Lista las 2 plantillas existentes |
| F2 | Clic en "Nueva plantilla" | ✅ Formulario: nombre, contenido (con placeholders `{nombre}`, `{puesto}`, `{salario}`, etc.) |
| F3 | Crear una plantilla "Prueba QA" con placeholders y guardar | ✅ Toast verde; aparece en el listado |
| F4 | Editar la plantilla creada | ✅ Guarda cambios |
| F5 | Duplicar la plantilla | ✅ Crea copia "Prueba QA (copia)" o similar |
| F6 | Desactivar la plantilla original | ✅ Desaparece del listado de activas |
| F7 | (Limpieza) Reactivar o eliminar las plantillas de prueba | ✅ Quedan solo las reales |

### Bloque G — Contratos (versión e historial)

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| G1 | En el detalle del empleado de Bloque B → pestaña Laboral → "Contrato" | ✅ Existe el **contrato inicial** (versión 1, tipo "Inicial", vigente) creado automáticamente al alta |
| G2 | Clic en "Ver contrato" | ✅ Muestra el contrato con datos del empleado sustituidos (nombre, puesto, salario, departamento) |
| G3 | Abrir un empleado con historial (ej. id 5 Esahu) | ✅ El historial muestra varias versiones ordenadas por fecha |
| G4 | Clic en "Nuevo contrato" (Renovación) con plantilla y guardar | ✅ Nueva versión (v+1), la anterior pasa a `vigente=0`, motivo registrado |
| G5 | Ver previsualización del contrato antes de guardar | ✅ Muestra el texto con placeholders ya reemplazados |
| G6 | Verificar en BD: `SELECT empleado_id, version, tipo_contrato, vigente, creado_por FROM contratos_empleados WHERE empleado_id=X ORDER BY version` | ✅ Versiones secuenciales, solo 1 vigente |

### Bloque H — Vacaciones

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| H1 | En el detalle de un empleado → pestaña Laboral → "Vacaciones" | ✅ Balance visible (días por antigüedad LFT) y periodo actual |
| H2 | Clic en "Solicitar vacaciones" → rango de fechas válido | ✅ Calcula días hábiles; guarda solicitud con estatus Pendiente |
| H3 | Ver "Todas las solicitudes" | ✅ Aparece la solicitud con empleado, rango, días |
| H4 | Aprobar la solicitud (mismo modal o listado global) | ✅ Estatus → Aprobada; el balance se ajusta |
| H5 | Rechazar una solicitud (con motivo) | ✅ Estatus → Rechazada con motivo |
| H6 | En el listado de empleados, verificar "días de vacaciones" del empleado | ✅ El campo se actualizó |

### Bloque I — Incidencias

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| I1 | En el detalle de un empleado → pestaña Laboral → "Incidencias" | ✅ Lista las 2 incidencias demo (Falta Justificada) con badge amarillo |
| I2 | Registrar **Falta** con monto de descuento y archivo de evidencia | ✅ Toast verde; aparece en la lista con evidencia descargable |
| I3 | Registrar **Incapacidad** | ✅ Aparece con estatus Activa |
| I4 | Registrar **Horas Extras** (ej. empleado 15 Miguel, 5 hrs) | ✅ Se guarda y en nómina aparece como percepción (H1 corregido el 17 ago 2026) |
| I5 | Filtrar incidencias por tipo y rango de fechas | ✅ Filtra correctamente |
| I6 | Cancelar una incidencia | ✅ Estatus → Cancelada |
| I7 | Verificar en BD: `SELECT * FROM incidencias_empleados WHERE empleado_id=X` | ✅ Registros con estatus correcto |

### Bloque J — Horarios

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| J1 | En el detalle de un empleado → "Horario" | ✅ Muestra los 7 días con entrada/salida y comida (28 registros ya existen en BD) |
| J2 | Editar la hora de entrada de un día | ✅ Guarda con toast; persiste |
| J3 | "Crear horario estándar" en un empleado sin horario (ej. el de Bloque B) | ✅ Genera lunes–viernes 9:00–18:00 con comida |
| J4 | Ver "Historial de horarios" | ✅ Muestra cambios por fecha de vigencia |
| J5 | Verificar en BD: `SELECT * FROM horarios_empleados WHERE empleado_id=X` | ✅ Filas con `dia_semana`, horas, `es_dia_laboral`, `turno` |

### Bloque K — Nómina con incidencias (`/rh/Nomina`)

> Base: nómina **NOM000027** (10–16 ago, Calculada). Si la prueba I4 registró HE o una falta para un empleado del periodo, el recálculo debe reflejarlas.

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| K1 | Abrir detalle de NOM000027 | ✅ 18 empleados con montos; columnas completas |
| K2 | Si se registró una incidencia de descuento en el periodo (I2): clic "Calcular" | ✅ La deducción (ej. "Falta (10/08)") aparece en percepciones/deducciones del empleado; los totales se actualizan |
| K3 | Si se registró HE (I4): verificar percepción "Horas Extras (dd/mm)" | ✅ Monto = hrs × costo_hora_extra del empleado |
| K4 | Revisar incidencias en BD tras calcular | ✅ Las incidencias del periodo quedaron `estatus = Procesada` |
| K5 | Procesar pago de la nómina (completo o parcial) | ✅ Estatus → Pagada/Parcial; log en `nominas_pagos_log` |
| K6 | Exportar Excel (3 hojas) | ✅ `Relacion Nomina`, `Transferencias`, `Resumen Pago` con logo |
| K7 | Generar recibos (general e individual PDF) | ✅ PDF por empleado con datos |
| K8 | Cron CLI: `php index.php rh/Nomina verificar_auto_nomina_ajax` | ✅ `{"success":true,...}` (o crea si corresponde) |
| K9 | Alertas campana | ✅ Nóminas por pagar/vencidas visibles; clic redirige a `/rh/Nomina` |

### Bloque L — Reloj Checador local (`/rh/RelojChecador`)

> Solo la parte local (dispositivos ZKTeco, sync, reportes). No aplica API Bixpe/obras.

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| L1 | `/rh/RelojChecador` (dashboard) | ✅ Tarjetas de stats + gráfica de checadas últimos 7 días (2 dispositivos y 98 asistencias en BD) |
| L2 | `/rh/RelojChecador/dispositivos` | ✅ Lista los 2 dispositivos con estado y token |
| L3 | Abrir detalle de un dispositivo | ✅ Muestra configuración y permite editar/guardar |
| L4 | `/rh/RelojChecador/comandos` | ✅ Lista/encola comandos; "vaciar comandos" funciona |
| L5 | `/rh/RelojChecador/sync_log` | ✅ Muestra el historial de sincronización (1,101 registros) con filtros |
| L6 | `/rh/RelojChecador/reporte_diario` con filtros (fecha, empleado, depto) | ✅ Tabla de asistencias del día con entrada/salida/comida; modal de detalle |
| L7 | Botón "Exportar CSV" del reporte diario | ✅ Descarga CSV con las checadas |
| L8 | `/rh/RelojChecador/reporte_mensual` (mes, año, empleado) | ✅ Resumen mensual por empleado (días trabajados, retardos, faltas) |
| L9 | `/rh/RelojChecador/sync_empleados_rh` | ✅ Preview de sync empleados↔RH; "aplicar migración" actualiza PIN/nombre |
| L10 | Verificar en BD: `SELECT * FROM asistencias ORDER BY id DESC LIMIT 10` | ✅ Checadas con empleado, fecha, hora |

### Bloque M — Comunicación interna (`/rh/Comunicacion`) — extra

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| M1 | `/rh/Comunicacion` | ✅ Panel con resumen de mensajes/tareas |
| M2 | Enviar un mensaje a un empleado | ✅ Aparece en la bandeja; "marcar leído" funciona |
| M3 | Crear una tarea | ✅ Se lista; actualizar estatus funciona |

### Bloque N — Departamentos (`/rh/Departamentos`) — extra

| Paso | Acción | Verificación esperada |
|:----:|:-------|:----------------------|
| N1 | `/rh/Departamentos` | ✅ Lista los 12 departamentos con conteos |
| N2 | Agregar un departamento de prueba | ✅ Toast verde; aparece en el listado |
| N3 | Editar y eliminar el de prueba | ✅ Eliminación con confirmación; no se elimina si tiene empleados (verificar mensaje) |
| N4 | Verificar detalle de departamento | ✅ Muestra empleados del departamento |

---

## 5. Hoja de resultados

| Bloque | Fecha | Resultado (✅/⚠️/❌) | Notas / fallas encontradas |
|:-------|:-----:|:--------------------:|:---------------------------|
| A — Listado/búsqueda | | | |
| B — Alta | | | |
| C — Edición | | | |
| D — Usuarios↔Empleados | | | |
| E — Expediente | | | |
| F — Plantillas | | | |
| G — Contratos | | | |
| H — Vacaciones | | | |
| I — Incidencias | | | |
| J — Horarios | | | |
| K — Nómina + incidencias | | | |
| L — Reloj checador | | | |
| M — Comunicación | | | |
| N — Departamentos | | | |

---

*ERP Chisa Recubrimientos — Plan de pruebas manuales RH · 10 agosto 2026. Actualizado 17 ago 2026: Hallazgo crítico H1 corregido (enum incidencias con `'Horas Extras'`).*
