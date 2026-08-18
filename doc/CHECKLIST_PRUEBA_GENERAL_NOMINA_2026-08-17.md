# Checklist — Prueba general manual de `rh/Nomina` (pre-commit)

> **Fecha:** 17 ago 2026
> **Propósito:** Prueba general del módulo de Nómina antes de hacer commit de la alineación de H1
> (enum `incidencias_empleados.tipo_incidencia` con `'Horas Extras'`) y pasar a la siguiente iteración.
> **URL:** `https://erp.chisarecubrimientos.com.mx/rh/Nomina`
> **Login sugerido:** `presentacion@chisa.mx` / `Demo2026!` (debe tener los 5 permisos de nómina:
> `rh_nomina`, `rh_nomina_configurar`, `rh_nomina_editar_detalle`, `rh_nomina_cuentas`, `rh_nomina_exportar`)

## ⚠️ Reglas para probar en producción

1. **Nunca pagar una nómina real** salvo autorización explícita. Para probar el pago, crear una
   nómina de prueba en un periodo futuro lejano (ej. dentro de 1-2 meses) y **cancelarla al terminar**.
2. Las incidencias de prueba se registran con **montos pequeños de prueba** (ej. $50) y **se eliminan al terminar** (ver A4).
   Importante: con monto $0 la incidencia se guarda pero **no genera percepción/deducción** en nómina (el modelo omite montos ≤ 0). Para validar el flujo completo, el monto debe ser > 0.
3. Tener abierta la pestaña **Network** del navegador (F12) para monitorear respuestas AJAX.
4. Marcar cada caso ✅ / ⚠️ / ❌ en la hoja de resultados al final.

## Datos de referencia (verificar que sigan vigentes en pantalla)

| Dato | Valor esperado |
|:-----|:---------------|
| Nómina más reciente | **NOM000027** (10–16 ago 2026, Calculada, neto $84,939.14) — puede haber nuevas auto-creadas por el cron (lunes 17-ago) |
| Empleado con costo de HE | **15 Miguel** (costo_hora_extra ≈ $136.66/hr) |
| Empleados activos | 13 (todos `tipo_nomina = Semanal`) |
| Incidencias existentes | 2 demo (Falta Justificada) |
| Automatización | Semanal, auto_crear activado, 1 día de anticipación |

---

## A. Incidencias → Nómina (lo que se corrigió con H1) — prioridad máxima

> **Objetivo:** confirmar que el flujo que antes fallaba con error 1265 ya funciona de punta a punta.

- [ ] A1. `/rh/RecursosHumanos` → detalle de **empleado 15 (Miguel)** → pestaña **Laboral → Incidencias** → **Registrar incidencia** de tipo **Horas Extras**, fecha de hoy, **Monto de Descuento = 683.30** (5 × $136.66), guardar.
  - ✅ Esperado: toast verde, aparece en la lista con estatus Activa (**sin** error 1265).
  - ℹ️ El formulario no tiene campo de horas: el monto de la percepción HE se captura directo en "Monto de Descuento". Debe ser > 0 para impactar nómina (con $0 se guarda pero no genera concepto).
- [ ] A2. Crear nómina de prueba: tipo **Semanal**, periodo que **incluya la fecha de la incidencia** (si la incidencia es de esta semana, usar la semana actual; si el cron ya creó la nómina de esta semana, usar esa misma) → **Calcular**.
  - ✅ Esperado: en el detalle, el empleado 15 tiene percepción **"Horas Extras (dd/mm)"** con monto = **683.30** (el capturado en A1).
- [ ] A3. Verificar en el detalle que las incidencias del periodo quedaron `estatus = Procesada` (opcional: consulta `SELECT * FROM incidencias_empleados WHERE empleado_id=15`).
- [ ] A4. **Limpieza:** eliminar la incidencia de prueba (o cancelarla) y **cancelar la nómina de prueba** con motivo ≥ 10 caracteres.
- [ ] A5. **Regresión del resto del enum:** registrar una incidencia **Falta** con descuento (monto $50 para que genere deducción) y una **Incapacidad** (monto $0 o $50) → ambas deben guardarse y mostrarse con su badge (estatus Activa). Limpiar al terminar.

## B. Vista principal y listado

- [ ] B1. La página carga con las tarjetas de estadísticas: **Total nóminas**, **Pendientes de pago**, **Pagadas este mes**, **Neto pendiente**.
- [ ] B2. La tabla muestra las nóminas ordenadas por periodo (más reciente arriba); folios, fechas, totales y badges de estatus legibles.
- [ ] B3. Filtros: **Folio** (parcial), **Tipo**, **Estatus**, **Periodo desde/hasta** → filtra y **Limpiar** restaura todo.
- [ ] B4. "Mostrar X entradas" funciona y el ícono de búsqueda no se encima con el selector.
- [ ] B5. Ordenar por columna **Periodo** alterna asc/desc correctamente.

## C. Crear nómina

- [ ] C1. **Nueva Nómina** → modal con Tipo, Periodo inicio/fin, Fecha pago + presets de periodo rápido.
- [ ] C2. Auto-cálculo de `periodo_fin`: Semanal inicio `2026-09-07` → fin `2026-09-13`; Quincenal inicio `2026-09-01` → fin `2026-09-15`; Mensual inicio `2026-09-01` → fin `2026-09-30`. El hint "Auto-calculado según tipo de nómina" es legible.
- [ ] C3. Cambiar `periodo_fin` manualmente y luego `periodo_inicio` → el valor manual se respeta.
- [ ] C4. Crear nómina de prueba **Semanal** (periodo futuro lejano, ej. `2026-10-05` al `2026-10-11`) → toast verde, fila con estatus **Borrador** y totales $0.00.
- [ ] C5. Crear nómina con **tipo sin empleados activos** (ej. Quincenal si nadie es Quincenal) → debe avisar "No hay empleados activos con ese tipo de nómina" sin dejar nómina fantasma. Eliminar el borrador si se creó.

## D. Calcular y detalle

- [ ] D1. En la nómina Borrador de prueba (C4): botón **Calcular** → toast verde, estatus **Calculada**, totales > $0.
- [ ] D2. Abrir **Ver/Editar detalle**: modal amplio (95vw escritorio / fullscreen móvil) con columnas: Empleado, Lugar, Días, Sueldo Base, H.E., Comidas, Bonos, Percepciones, Deducciones, Neto, Banco/Cuenta, Forma de pago.
- [ ] D3. **Verificar un cálculo manual:** elegir un empleado, comprobar `sueldo_base = salario_diario × días` y `neto = percepciones − deducciones` (calculadora de mano).
- [ ] D4. Buscador "Buscar trabajador..." filtra en tiempo real sin romper los totales de cabecera.
- [ ] D5. En 375px: la tabla tiene scroll horizontal y los encabezados no se cortan.

## E. Edición inline del detalle

- [ ] E1. Doble clic en **Horas Extras** del empleado 15 (si aparece) o de cualquier empleado → capturar `2` → Tab/clic fuera → AJAX `actualizar_detalle_ajax` OK, **Neto y totales se recalculan** sin recargar.
- [ ] E2. Doble clic en **Comidas** → `1700` → recálculo automático. (Restaurar el valor original después.)
- [ ] E3. Cambiar **Forma de pago** inline a "Depósito" → toast → persiste al reabrir el modal. (Restaurar.)
- [ ] E4. **Cuentas bancarias:** ícono de banco junto a un empleado → modal con cuentas; probar **Agregar cuenta** (banco del catálogo + número + CLABE) y **Agregar tarjeta** (número de tarjeta) → ambas deben aparecer en la lista sin error 500 (regresión del Bug B del 4-ago-2026), marcar **default**, y **eliminar** (soft delete). Los botones no deben romper la fila si el empleado no tiene cuentas (debe ofrecer "+ Agregar").
- [ ] E5. **Edición rápida de empleado** desde el detalle (icono editar junto al nombre) → abre modal con datos laborales; cambiar un valor (ej. salario) → guarda y recalcula; **restaurar el valor original** al terminar (regresión del 4-ago-2026).
- [ ] E6. En una nómina **Pagada** existente: el detalle es de **solo lectura** (sin celdas editables) y el footer lo indica.

## F. Procesar pago (solo sobre la nómina de prueba)

- [ ] F1. En la nómina Calculada de prueba: **Procesar Pago** → modal con lista de empleados: nombre, neto pendiente, adeudos (si existen), campo "Monto a pagar", checkbox "Incluir adeudos", botones rápidos 25/50/100%.
- [ ] F2. **Pago parcial:** marcar 1 empleado con monto menor al total → Procesar → estatus **Parcial**.
- [ ] F3. Reabrir Procesar Pago → los ya pagados **no reaparecen**; pagar los restantes → estatus **Pagada**.
- [ ] F4. **Validaciones:** monto mayor al máximo → rechaza con "Monto inválido... Máximo: $X"; monto $0 o vacío → rechaza.
- [ ] F5. Verificar en BD (opcional): `SELECT * FROM nominas_pagos_log WHERE nomina_id = <id de prueba>` → registros con monto, usuario y fecha.

## G. Cancelar y notas de ajuste

- [ ] G1. **Cancelar** una nómina Calculada de prueba con motivo ≥ 10 caracteres → estatus **Cancelada** con badge, registro en `nominas_cancelaciones`.
- [ ] G2. Motivo de **menos de 10 caracteres** → rechazado antes de enviar.
- [ ] G3. Intentar cancelar una nómina **Pagada** (usar una nómina Pagada demo existente) → debe bloquear con "Use el sistema de notas de ajuste".
- [ ] G4. En una nómina Pagada/Parcial: **Notas de ajuste** → agregar una nota → aparece en la sección colapsable y persiste.
- [ ] G5. Filtro por estatus **Cancelada** muestra solo canceladas.

## H. Exportar Excel (nómina Pagada de prueba o demo)

- [ ] H1. Ícono Excel → descarga `.xlsx` con **3 hojas**: `Relacion Nomina`, `Transferencias`, `Resumen`.
- [ ] H2. **Relacion Nomina:** columnas completas, agrupada por lugar (Oficina/Obra), filas alternadas legibles.
- [ ] H3. **Transferencias:** banco, CLABE, cuenta, beneficiario, monto; agrupada por banco con subtotales (sumar 2-3 grupos a mano).
- [ ] H4. **Resumen:** logo de la empresa en A1, totales coinciden con la UI, guía de desembolso incluye Cheque, Transferencia, Efectivo y **Depósito**.
- [ ] H5. *No bloqueante (observación del 4-ago-2026):* al procesar un pago, la respuesta puede indicar que **no se generó póliza contable** ("verifique periodo y cuentas") — es config del periodo contable, no un error del módulo; solo anotarlo si aparece.

## I. Recibos de pago (PDF)

- [ ] I1. En nómina Pagada: ícono **Recibos** → modal con vista previa de todos los recibos.
- [ ] I2. Recibo individual: nombre, RFC/NSS, periodo, percepciones/deducciones desglosadas y neto coinciden con el detalle en pantalla.
- [ ] I3. **Descargar PDF individual** (botón por empleado) → se genera el PDF.
- [ ] I4. **PDF general multipágina**: ningún recibo cortado entre páginas; el orden coincide con la tabla.

## J. Planeador mensual

- [ ] J1. Botón **Planeador** → modal con grid de periodos del mes actual.
- [ ] J2. Navegar `<`/`>` 2-3 meses, incluyendo **diciembre → enero** (cambio de año) → el título del mes se actualiza y no se rompe.
- [ ] J3. Cambiar vista **Semanal → Quincenal → Mensual** → grid correcto (4-5 semanas, 2 quincenas, 1 mes).
- [ ] J4. Clic en una card "Sin nómina" (`+`) → abre/sugiere creación con esas fechas precargadas.
- [ ] J5. La sección "Próximas nóminas automáticas" muestra al menos la siguiente semana sin nómina (no vacía).

## K. Automatización y cron

- [ ] K1. Botón **Automatización** → modal con frecuencia, auto-crear, días de anticipación; el texto aclara que el cron evalúa Semanal/Quincenal/Mensual por calendario.
- [ ] K2. Cambiar días de anticipación a `2`, guardar, reabrir → persiste. **Restaurar a `1`** al terminar.
- [ ] K3. Desde CLI: `php index.php rh/Nomina verificar_auto_nomina_ajax` → responde JSON con `"success":true` y **no duplica** la nómina del periodo actual si ya existe.

## L. Alertas (campana)

- [ ] L1. La campana del header muestra badge numérico si hay nóminas por vencer/vencidas; al abrirla, las de nómina tienen ícono `$` y folio/periodo/neto.
- [ ] L2. Clic en una notificación de nómina → redirige a `/rh/Nomina`.
- [ ] L3. Las nóminas **Canceladas** no generan alertas de pago.

## M. Regresión rápida de RH (dependencias del módulo)

- [ ] M1. `/rh/RecursosHumanos`: listado y detalle de empleados cargan sin errores.
- [ ] M2. Incidencias (pestaña Laboral): listar, registrar **Falta** y **Cancelar** una incidencia → estatus correctos (ya cubierto en A5, aquí solo confirmar cancelación).
- [ ] M3. Cambiar `tipo_nomina` de un empleado de prueba (ej. de Semanal a Quincenal y regresar) → la siguiente nómina Semanal ya no lo incluye y la Quincenal sí (si se crea). Dejar el empleado como estaba.

---

## Hoja de resultados

| Bloque | Ejecutado por | Fecha | Resultado (✅/⚠️/❌) | Notas / incidencias encontradas |
|:-------|:--------------|:------|:--------------------:|:--------------------------------|
| A — Incidencias → Nómina (H1) | Cursor agente + cliente (A1) | 18 ago 2026 | ⚠️ | A1 ya ✅ (cliente). A2–A5 no se reejecutaron: `calcular_ajax` **rechaza** una nómina ya Calculada (`Nómina no válida para cálculo`), así que no se puede recargar HE sobre NOM000027/028. No se registraron incidencias nuevas para no ensuciar producción. |
| B — Vista principal | Cursor agente | 18 ago 2026 | ✅ | Stats 36 / 7 pendientes / 0 pagadas mes / neto $539,539.23. Filtros folio/tipo/estatus/periodo + Limpiar OK. Length 25→10 OK. Orden Periodo asc (feb-2026) / desc (ago-2027) OK. DataTables `searching:false` (no hay ícono de búsqueda que se encime). |
| C — Crear nómina | Cursor agente | 18 ago 2026 | ✅ | C1 presets OK. C2 auto-fin Semanal 07→13 sep, Quincenal 01→15, Mensual 01→30 + hint legible. C3 fin manual se respeta. C4 **NOM000038** Semanal 05–11 oct, Borrador $0.00, 18 empleados. C5 Quincenal avisó «No hay empleados…»; se creó borrador id 39 (0 emp.) y se **eliminó**. |
| D — Calcular y detalle | Cursor agente | 18 ago 2026 | ✅ | D1 Calculada, neto $84,939.14. D2 modal con columnas pedidas (no hay columna explícita «Días»; `dias_trabajados=7` en datos). D3 Miguel: 546.65×7=3,826.55; neto=perc−ded. D4 buscador «Miguel» deja 2 filas (hay dos Miguel) y el tfoot/totales siguen. D5 375px no emulado. |
| E — Edición inline | Cursor agente | 18 ago 2026 | ⚠️ | E1 HE=2 → monto 273.32 (2×136.66) y neto 4,099.87. **Al volver HE a 0 el neto no se recalcula hasta otro campo.** E2 Comidas 1700 → neto 5,526.55; restaurado. E3 Forma de pago Depósito persiste; restaurado a Transferencia. E4 «+ Agregar» en Ana (sin cuentas); no se dieron de alta cuentas reales. E5 no tocado (salario). E6 NOM000023 Pagada: 0 celdas `.editable`, footer «solo lectura». |
| F — Procesar pago | Cursor agente | 18 ago 2026 | ⚠️ | F1 modal OK (25/50/100%, adeudos). F4 monto > máx rechaza (`Máximo: $2450.00`); $0 no envía. **Efecto colateral:** al validar F4 con el resto de checkboxes en 100%, se pagaron 17/18 empleados de NOM000038 → quedó **Parcial** (Ana pendiente $2,450). Sin póliza (`poliza_id` null). F3: los ya pagados **siguen en la lista** como deshabilitados «Pagado» (no desaparecen). |
| G — Cancelar y notas | Cursor agente | 18 ago 2026 | ⚠️ | G2 motivo &lt;10 caracteres bloqueado en front. G1 no pudo cancelar NOM000038 (ya Parcial). El backend responde «Use el sistema de notas de ajuste» (mismo mensaje que G3 para Pagada). G5 filtro Cancelada = 7 filas, todas Cancelada. G4 nota en Pagada demo **no** se escribió. |
| H — Excel | — | 18 ago 2026 | ⚠️ | No se abrió el .xlsx (contenido 3 hojas pendiente). El botón Exportar está visible en Pagadas. |
| I — Recibos PDF | — | 18 ago 2026 | ⚠️ | No se validó contenido PDF. Botón Recibos presente en Pagadas. |
| J — Planeador | Cursor agente | 18 ago 2026 | ⚠️ | J1–J5 OK: Agosto 2026, 5 cards semanales / 2 quincenas / 1 mes; dic-2026→ene-2027; clic «Sin nómina» precarga 24–30 ago; próximas auto 24–30 ago «En 5 días». Badge **Sin nómina** (`bg-light text-muted`) = **4.12:1** (falla AA 4.5). Resto de badges del planeador ≥8:1. |
| K — Automatización | Cursor agente | 18 ago 2026 | ✅ | K1 modal Semanal, auto_crear=1, 1 día, texto de calendario OK. K2 no se cambió (evitar tocar config prod). K3 CLI ×2: `{"success":true,"creada":false,"message":"No corresponde crear nómina hoy"}` — no duplica. |
| L — Alertas | Cursor agente | 18 ago 2026 | ⚠️ | Campana 9+; el dropdown solo muestra alertas de **Almacén / stock bajo**. Cero ítems de nómina pese a varias Calculada pendientes. L2/L3 no verificables sin alerta de nómina. |
| M — Regresión RH | Cursor agente | 18 ago 2026 | ⚠️ | M1 `/rh/RecursosHumanos` carga (18 empleados). Badges «Datos incompletos» / 📁 con patrón subtle y AA. **Activo/Reingreso/Inactivo** siguen sólidos en `EmpleadoModel::badge_estatus_html` (2.33 / 3.16 / no medido) — fuera de los 4 archivos del frente de contraste. M2/M3 no ejecutados. |

---

*ERP Chisa Recubrimientos — Checklist de prueba general manual de `rh/Nomina`. 17 ago 2026. Previo al commit de alineación de H1 (`database/contratos_rh.sql` + docs) y a la siguiente iteración.*
