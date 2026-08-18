# Checklist Manual — Después del E2E automático de `rh/Nomina`

> **Propósito:** Complementar `tests/e2e/tests/nomina-flujo.spec.js` (login → crear → calcular →
> detalle → procesar pago) con las validaciones que un script **no puede verificar de forma
> confiable**: contraste visual, responsive, contenido exacto de PDFs/Excel y casos límite
> de negocio.
>
> **Nota:** el Hallazgo H1 (enum `incidencias_empleados.tipo_incidencia` sin `'Horas Extras'`)
> fue corregido en BD de producción el 17-ago-2026 (`database/incidencias_empleados_tipo_horas_extras.sql`,
> commit `c258e7b`), por lo que el caso C9 ya puede probarse sin prerequisito.
>
> **Login sugerido:** `presentacion@chisa.mx` / `Demo2026!` (ya tiene los 5 permisos de nómina).

---

## A. Verificación visual del recorrido que hizo el script

Repite en el navegador, con ojo humano, lo que el E2E ya validó por código:

- [ ] A1. Modal "Nueva Nómina": los botones de "Selección rápida de periodo" no se enciman en móvil (375px) ni en tablet (768px).
- [ ] A2. Al cambiar "Tipo de Nómina" a Quincenal/Mensual, el hint "Auto-calculado según tipo de nómina" es legible (no se pierde en el fondo del input).
- [ ] A3. Badge de estatus **Borrador** → **Calculada** → **Pagada/Cancelada**: colores con buen contraste en modo claro y oscuro (si el toggle de tema ya funciona; ver `TODO.md`, sección "Reparar botón de Tema Oscuro").
- [ ] A4. Modal de Detalle (`#modalDetalleNomina`) en pantalla de escritorio (95vw) y en 375px (`modal-fullscreen-xl-down`): scroll horizontal de la tabla funcional, encabezados no se cortan.
- [ ] A5. Columna "Horas Extras" en el detalle: texto legible sobre el fondo de celda editable (`#fffef5`, amarillo claro).

## B. Edición inline del detalle (no cubierto por el E2E)

- [ ] B1. Doble clic en **Comidas** de un empleado → cambiar a `1700` → Tab/clic fuera → verificar que el Neto de ese empleado y los totales de cabecera se recalculan sin recargar la página.
- [ ] B2. Doble clic en **Horas Extras** de un empleado *con* `costo_hora_extra` configurado (ej. "Miguel", ~$136.66/hr) → capturar `5` → verificar que el monto calculado = `5 × costo_hora_extra` (bug histórico: quedaba en $0, corregido el 4-ago-2026 — confirmar que sigue corregido).
- [ ] B3. Cambiar **Forma de pago** de un empleado (select inline) a "Depósito" → toast de confirmación → recargar el modal y verificar que persiste.
- [ ] B4. Usar el buscador "Buscar trabajador..." dentro del modal de detalle con un nombre parcial → verifica que filtra en tiempo real sin perder los totales de cabecera (que no correspondan solo a la fila visible).

## C. Casos límite / negativos (edge cases)

- [ ] C1. Intentar **calcular dos veces seguidas** la misma nómina → el sistema no debe duplicar percepciones/deducciones (verificar que los totales no se disparan).
- [ ] C2. Crear una nómina para un tipo (ej. Quincenal) cuando **no hay empleados activos** de ese `tipo_nomina` → debe mostrar el toast "No hay empleados activos con ese tipo de nómina" sin dejar una nómina fantasma sin sentido en la tabla (o bien, verificar qué hace con la cabecera creada sin detalle).
- [ ] C3. En "Procesar Pago", capturar un **monto mayor al máximo permitido** para un empleado → debe rechazar con "Monto inválido... Máximo: $X" y no enviar la petición.
- [ ] C4. Pagar **parcialmente** solo 1 de N empleados → verificar badge **Parcial** → reabrir "Procesar Pago" → los ya pagados no deben reaparecer en la lista.
- [ ] C5. Intentar **cancelar una nómina en estatus Pagada** → debe bloquear con el mensaje "Use el sistema de notas de ajuste" (no debe permitir cancelar).
- [ ] C6. Capturar un motivo de cancelación de **menos de 10 caracteres** → debe rechazar antes de enviar la petición (validación de frontend).
- [ ] C7. Ejecutar el cron dos veces el mismo día (`php index.php rh/Nomina verificar_auto_nomina_ajax`) → la segunda ejecución **no debe duplicar** la nómina de la semana ya creada.
- [ ] C8. Verificar el caso de un empleado **sin cuenta bancaria activa** dentro del detalle de nómina → la columna "Banco/Cuenta" debe ofrecer "+ Agregar" en vez de romper la fila.
- [ ] C9. Registrar una incidencia "Horas Extras" desde `/rh/RecursosHumanos` para un empleado dentro del periodo de una nómina ya calculada → recalcular la nómina → confirmar que aparece como percepción "Horas Extras (dd/mm)" y coincide con `horas × costo_hora_extra`.
- [ ] C10. Reducir la ventana a 375px durante el flujo de "Procesar Pago": los botones 25/50/100% de monto rápido no deben desbordar la tarjeta.

## D. Revisión de PDFs y Excel (el script no valida contenido, solo que se descarguen)

- [ ] D1. **Excel de nómina pagada** (3 hojas): abrir el archivo y confirmar:
  - [ ] Hoja "Relacion Nomina": 18 columnas, agrupada por lugar de origen (Oficina/Obra), filas alternadas legibles.
  - [ ] Hoja "Transferencias": banco, CLABE, número de cuenta, beneficiario, monto — agrupada por banco con subtotales correctos (sumar a mano 2-3 grupos para validar).
  - [ ] Hoja "Resumen": logo de la empresa visible en A1, totales generales coinciden con los mostrados en la UI, guía de desembolso incluye las 4 formas de pago (Cheque, Transferencia, Efectivo, **Depósito**).
- [ ] D2. **Recibo de nómina individual (PDF)**: verificar que el nombre, RFC/NSS (si aplica), periodo, percepciones y deducciones desglosadas, y el neto coinciden exactamente con el detalle mostrado en pantalla para ese empleado.
- [ ] D3. **Recibo general (todos los empleados, PDF multipágina)**: confirmar que no se corta ningún recibo entre páginas y que el orden coincide con la tabla.
- [ ] D4. **Contrato PDF** (`/rh/RecursosHumanos` → detalle de empleado → Contrato): generar uno con la plantilla "Legal LFT Completo" y verificar que **todos** los placeholders se reemplazaron (buscar visualmente que no quede ningún `{{...}}` sin sustituir, en particular `{{numero_empleado}}`, `{{telefono}}`, `{{email}}`, `{{firma_testigo1}}` — agregados recientemente, ver `HANDOFF_RH05_CONTRATOS.md`).
- [ ] D5. **Reporte mensual del reloj checador (Excel/CSV, si aplica)**: exportar y confirmar columnas de días trabajados, faltas y retardos.

## E. Planeador mensual (no cubierto por el E2E)

- [ ] E1. Abrir "Planeador" → navegar 2-3 meses adelante y atrás con `<` `>` → el título del mes se actualiza y no se rompe en diciembre→enero (cambio de año).
- [ ] E2. Cambiar vista Semanal → Quincenal → Mensual → el grid se re-renderiza con la cantidad correcta de periodos (4-5 semanas, 2 quincenas, 1 mes).
- [ ] E3. Click en una card "Sin nómina" (`+`) de una semana futura → debe sugerir/abrir la creación de nómina con esas fechas precargadas.
- [ ] E4. Verificar sección "Próximas nóminas automáticas" (bug corregido el 4-ago-2026) → debe mostrar al menos la siguiente semana sin nómina, no vacío.

## F. Alertas — campana en vivo

- [ ] F1. Con una nómina cuya `fecha_pago` sea hoy o ya venció (usa una de las nóminas demo o crea una con fecha pasada vía BD si tu entorno de prueba lo permite): la campana debe mostrar el badge numérico y, al abrir el dropdown, la notificación con folio/periodo/neto e ícono `$`.
- [ ] F2. Click en la notificación de nómina → redirige correctamente a `/rh/Nomina`.
- [ ] F3. Verificar que una nómina **Cancelada** no siga generando alertas de "por vencer".

## G. Regresión rápida de RH general (ya que nómina depende de datos de empleados)

- [ ] G1. `/rh/RecursosHumanos`: alta de un empleado con RFC/CURP/NSS inválidos → el sistema rechaza con mensaje claro (Bloque B de `PRUEBAS_MANUALES_RH_2026-08-10.md`).
- [ ] G2. Cambiar el `tipo_nomina` de un empleado activo (ej. de Semanal a Quincenal) → verificar que en la **siguiente** nómina Semanal generada este empleado **ya no aparece**, y sí aparece cuando corresponda su nómina Quincenal.
- [ ] G3. Vacaciones aprobadas dentro del periodo de una nómina → confirmar cómo se reflejan (o no) los días de vacaciones en el cálculo de días trabajados/pagados.

---

## Hoja de resultados

| Bloque | Ejecutado por | Fecha | Resultado (✅/⚠️/❌) | Notas |
|:-------|:--------------|:------|:---------------------:|:------|
| A — Visual | Cursor agente | 18 ago 2026 | ⚠️ | A2 hint auto-cálculo legible. A3 badges Borrador/Calculada/Pagada/Cancelada/Parcial **WCAG AA** en claro (8.08–11.92) y oscuro forzado (6.40–10.03). El **toggle de tema no funciona** (`data-bs-theme` se queda en default; TODO 1.1). A5 celda HE `#fffef5` / texto oscuro. A1/A4 375px no emulados. Overlay del layout intercepta clics en botones del header (Planeador se abrió por JS). |
| B — Edición inline | Cursor agente | 18 ago 2026 | ⚠️ | B1 Comidas 1700 recalcula neto y totales; restaurado. B2 HE 5× no se capturó; sí HE=2 × 136.66 = 273.32 (bug $0 del 4-ago no reaparece). Volver HE a 0 no recalcula percepciones hasta otro campo. B3 Depósito persiste; restaurado. B4 filtro en vivo; 2 Miguel; tfoot permanece. |
| C — Casos límite | Cursor agente | 18 ago 2026 | ⚠️ | C1 2.º `calcular_ajax` → «Nómina no válida para cálculo» (no duplica; **tampoco permite recálculo**). C2 Quincenal sin empleados: toast + borrador fantasma id 39 **eliminado**. C3 monto &gt; máx rechazado en front. C4 Parcial en NOM000038; pagados siguen listados como «Pagado» disabled. C5 mismo bloqueo que Pagada. C6 motivo corto rechazado. C7 cron ×2 sin duplicar. C8 Ana «+ Agregar». C9 no ejecutable: no hay recálculo de Calculada. C10 375px no emulado. |
| D — PDFs/Excel | — | 18 ago 2026 | ⚠️ | No se abrieron Excel/PDF/contrato/reloj en esta pasada. |
| E — Planeador | Cursor agente | 18 ago 2026 | ⚠️ | E1–E4 OK (dic→ene, 5/2/1 periodos, precarga fechas, próximas auto). Contraste «Sin nómina» 4.12:1. |
| F — Alertas | Cursor agente | 18 ago 2026 | ⚠️ | Campana con badge 9+; contenido = stock de almacén, **sin** notificaciones de nómina. |
| G — Regresión RH | Cursor agente | 18 ago 2026 | ⚠️ | Listado RH OK. Badges de estatus Activo/Reingreso aún sólidos (no AA). G1/G2/G3 (alta inválida, cambio tipo_nomina, vacaciones) no ejecutados. |

---

*ERP Chisa Recubrimientos — Checklist manual complementario al E2E automatizado de `rh/Nomina`. 13 agosto 2026.*
