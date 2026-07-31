# PRUEBAS COMPLETAS — MÓDULO rh/Nomina (Pre-Presentación)

> **Fecha:** 31 julio 2026  
> **Estado B2:** ✅ Corregido — todos los empleados tienen salarios > 0, nóminas Calculadas con neto > 0  
> **URL:** `https://erp.chisarecubrimientos.com.mx/rh/Nomina`

---

## PARTE 1 — PROMPT PARA COMPOSER (Verificación Automatizada)

Copia y pega esto en Composer para que ejecute la verificación automatizada:

```
Ejecuta una verificación completa del módulo rh/Nomina. Trabaja en el directorio:
/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html

Archivos a verificar:
- application/controllers/rh/Nomina.php
- application/models/RH/NominaRhModel.php
- application/views/rh/nomina/main.php

=== BLOQUE A: Sintaxis y Estructura ===
A1. PHP Lint en los 3 archivos: `php -l` sobre cada uno.
A2. Contar métodos nuevos vs esperados:
    - Modelo (NominaRhModel.php): get_lugar_origen_empleado, get_planeador_mensual, _generar_periodos_mes (3 métodos)
    - Controlador (Nomina.php): planeador_mensual_ajax (1 endpoint)
    - Vista (main.php): abrirPlaneador, renderizarGrid, navegarPlaneador, crearNominaDesdePlaneador (4 funciones JS)
A3. Buscar `departamento_id` en el modelo — debe aparecer en al menos 4 contextos: agregar_empleados_nomina (SELECT), calcular_nomina (SELECT), get_lugar_origen_empleado (validación + where)
A4. Verificar fix de "Depósito" en guía de desembolso: buscar `['DEPÓSITO', $deposito]` en Nomina.php

=== BLOQUE B: Base de Datos ===
B1. Conteos:
    - SELECT COUNT(*) FROM nominas (esperado: ~27)
    - SELECT COUNT(*) FROM nominas_detalle (esperado: ~479)
    - SELECT COUNT(*) FROM empleados_cuentas_bancarias WHERE estatus=1 (esperado: ~10)
    - SELECT COUNT(*) FROM nominas_pagos_log (esperado: ~12)
    - SELECT COUNT(*) FROM empleados WHERE estatus IN (1,2) (esperado: ~15-18)
    - SELECT estatus, COUNT(*) FROM nominas GROUP BY estatus (debe haber: Pagada, Calculada, Parcial, Borrador o similares)
B2. Nóminas Calculadas — verificar neto > 0:
    - SELECT id, folio, estatus, total_neto FROM nominas WHERE estatus = 'Calculada'
    - TODAS deben tener total_neto > 0 (fix B2 aplicado)
    - Reportar cuáles tienen neto > 0 y cuáles no
B3. Configuración automatización:
    - SELECT * FROM nomina_configuracion (esperado: frecuencia='Semanal', auto_crear=1)
B4. Huérfanos (0 en las 3 tablas):
    - SELECT COUNT(*) FROM nominas_detalle nd LEFT JOIN nominas n ON n.id=nd.nomina_id WHERE n.id IS NULL
    - SELECT COUNT(*) FROM nominas_conceptos nc LEFT JOIN nominas_detalle nd ON nd.id=nc.nomina_detalle_id WHERE nd.id IS NULL
    - SELECT COUNT(*) FROM nominas_pagos_log pl LEFT JOIN nominas n ON n.id=pl.nomina_id WHERE n.id IS NULL
B5. Empleados con salario cero (DEBE SER 0):
    - SELECT COUNT(*) FROM empleados WHERE salario_base_diario = 0 AND salario_base_mensual = 0

=== BLOQUE C: Endpoint y Periodos ===
C1. Verificar ruta registrada:
    - curl -s -o /dev/null -w "%{http_code}" "https://erp.chisarecubrimientos.com.mx/rh/Nomina/planeador_mensual_ajax?mes=7&anio=2026&tipo=Semanal"
    - HTTP 302/307 = ruta existe (redirect por falta de sesión). HTTP 404 = error.
C2. Simular periodos julio 2026 (verificar lógica de _generar_periodos_mes en modelo L1558-1603):
    - Semanal: 4 periodos (jul 6-12, 13-19, 20-26, 27-ago 2)
    - Quincenal: 2 periodos (jul 1-15, 16-31)
    - Mensual: 1 periodo (jul 1-31)

=== BLOQUE D: UI en la Vista ===
D1. Botón Planeador: buscar `abrirPlaneador` (mínimo 2 matches)
D2. Modal Planeador: buscar `modalPlaneador` (mínimo 7 matches)
D3. Leyenda colores: buscar `-subtle` (debe haber 3 badges: Pagada, Calculada, Cancelada)
D4. CSS planeador: buscar `planeador-card` (mínimo 3 matches)
D5. Verificar que NO hay `innerHTML` sin `esc()` ni `XSS` vulnerable en JS de la vista
D6. Verificar que todas las funciones JS referenciadas existen (ej. `esc()`, `fmtNum()`, `formatoMoneda()`)

=== RESUMEN FINAL ===
Presenta una tabla con todos los checks numerados, su resultado (✅/❌), y el detalle de cada uno.
Al final, indica si el módulo está listo para presentación.
```

---

## PARTE 2 — LISTA DE PRUEBAS MANUALES (12 Bloques + Extras)

Ejecutar en orden, en el navegador. Usuario con permisos: `rh_nomina`, `rh_nomina_configurar`, `rh_nomina_editar_detalle`, `rh_nomina_cuentas`, `rh_nomina_exportar`.

---

### PRERREQUISITOS

- [ ] Seeder ejecutado → 27 nóminas en BD  
- [ ] Permisos asignados al usuario de prueba  
- [ ] DevTools > Network abierta para monitorear AJAX  

---

### BLOQUE 1: Vista Principal y DataTable

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 1.1 | Navegar a `/rh/Nomina` | 4 tarjetas stats: Total nóminas, Pendientes, Pagadas este mes, Neto pendiente | ☐ |
| 1.2 | Revisar tabla | ~27 nóminas, orden por periodo descendente | ☐ |
| 1.3 | Filtrar por Folio: `NOM000022` | 1 resultado. Limpiar | ☐ |
| 1.4 | Filtrar por Tipo: `Semanal` | Solo semanales | ☐ |
| 1.5 | Filtrar por Estatus: `Pagada` | ~22-24 registros | ☐ |
| 1.6 | Filtrar por periodo: `2026-07-01` → `2026-07-31` | Solo julio. Limpiar | ☐ |
| 1.7 | Verificar "Mostrar X entradas" | Ícono búsqueda no se encima con selector | ☐ |
| 1.8 | Ordenar por Periodo | Alterna asc/desc correctamente | ☐ |

---

### BLOQUE 2: Crear Nómina Manual

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 2.1 | Clic **Nueva Nómina** (`+`) | Modal: Tipo, Periodo inicio, Periodo fin, Fecha pago | ☐ |
| 2.2 | Tipo=Semanal, inicio=`2026-08-10` | `periodo_fin` se auto-calcula a `2026-08-16` | ☐ |
| 2.3 | Cambiar Tipo a Quincenal, inicio=`2026-08-01` | `periodo_fin` = `2026-08-15` | ☐ |
| 2.4 | Cambiar Tipo a Mensual, inicio=`2026-08-01` | `periodo_fin` = `2026-08-31` | ☐ |
| 2.5 | Editar manualmente `periodo_fin`, luego cambiar `periodo_inicio` | El valor manual se respeta | ☐ |
| 2.6 | Clic **Crear** | Toast verde. Tabla se refresca. Badge Borrador, totales $0.00 | ☐ |

---

### BLOQUE 3: Calcular Nómina

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 3.1 | En Borrador, clic **Calcular** (calculadora) | AJAX POST `calcular_ajax` → 200 | ☐ |
| 3.2 | Esperar | Toast "Nómina calculada correctamente" | ☐ |
| 3.3 | Verificar estatus | Cambia a Calculada. Totales > $0.00 | ☐ |
| 3.4 | Abrir detalle (ojo/lupa) | Modal con columnas: Empleado, Lugar, Días, Sueldo Base, H.E., Comidas, Bonos, Percepciones, Deducciones, Neto | ☐ |

---

### BLOQUE 4: Editar Detalle (permiso: `rh_nomina_editar_detalle`)

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 4.1 | Editar **Horas Extras** (ej. `5`) | Campo editable inline | ☐ |
| 4.2 | Tab o clic fuera | AJAX POST `actualizar_detalle_ajax`. Toast | ☐ |
| 4.3 | Verificar recálculo | Neto del empleado y totales se actualizan | ☐ |
| 4.4 | Editar **Comidas** (ej. `1700`) | Guarda y recalcula | ☐ |

---

### BLOQUE 5: Procesar Pago

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 5.1 | En Calculada, clic **Procesar Pago** (`$`) | Modal lista empleados pendientes | ☐ |
| 5.2 | Verificar modal | Por empleado: nombre, neto pendiente, adeudos, monto a pagar, checkbox Incluir adeudos | ☐ |
| 5.3 | Pago parcial: reducir monto de un empleado | El sistema permite montos parciales | ☐ |
| 5.4 | Seleccionar algunos empleados, clic **Procesar Pago** | Toast. Estatus → Parcial (badge naranja) | ☐ |
| 5.5 | Reabrir Procesar Pago | Solo aparecen pendientes (no los ya pagados) | ☐ |
| 5.6 | Pagar restantes | Estatus → Pagada (badge verde) | ☐ |
| 5.7 | Verificar log | `nominas_pagos_log` tiene registros con montos, usuario, fecha | ☐ |

---

### BLOQUE 6: Cuentas Bancarias (permiso: `rh_nomina_cuentas`)

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 6.1 | En detalle, clic ícono banco/tarjeta | Modal "Cuentas Bancarias" | ☐ |
| 6.2 | Clic **Agregar cuenta** | Form: banco (catálogo), número, CLABE | ☐ |
| 6.3 | Llenar y guardar | Nueva cuenta en lista. AJAX `guardar_cuenta_empleado_ajax` | ☐ |
| 6.4 | Marcar default (estrella) | AJAX `set_cuenta_default_ajax` | ☐ |
| 6.5 | Eliminar cuenta (soft delete) | Desaparece de lista. DB: `estatus=0` | ☐ |
| 6.6 | Columna Banco/Cuenta en detalle | Muestra `<select>` con cuentas o botón "+ Agregar" | ☐ |

---

### BLOQUE 7: Automatización (permiso: `rh_nomina_configurar`)

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 7.1 | Clic **Automatización** (engrane) | Modal: Frecuencia, Auto-crear, Días anticipación | ☐ |
| 7.2 | Verificar valores | Frecuencia=Semanal, Auto-crear=Activado, Días=1 | ☐ |
| 7.3 | Leer texto aclaratorio | Indica que revisa 3 tipos (Semanal/Quincenal/Mensual) | ☐ |
| 7.4 | Cambiar Días a `2`, guardar | Toast. AJAX `guardar_configuracion_ajax` | ☐ |
| 7.5 | Reabrir | Valor persiste. Restaurar a `1` | ☐ |

---

### BLOQUE 8: Exportar Excel (permiso: `rh_nomina_exportar`)

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 8.1 | En Pagada, clic descarga Excel | Archivo `.xlsx` descargado | ☐ |
| 8.2 | Abrir archivo | 3 hojas: Relacion Nomina, Transferencias, Resumen | ☐ |
| 8.3 | Hoja 1 — Relación | 18 columnas, agrupada por lugar/origen, filas alternadas | ☐ |
| 8.4 | Hoja 2 — Transferencias | 5 columnas, agrupada por banco, subtotales | ☐ |
| 8.5 | Hoja 3 — Resumen | Totales generales, logo empresa en A1, guía de desembolso (Cheque, Transferencia, Efectivo, Depósito, Otros) | ☐ |
| 8.6 | Logo visible | `chisa_recubrimientos_logo.jpg` se renderiza | ☐ |

---

### BLOQUE 9: Recibos de Pago

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 9.1 | En Pagada, clic impresora/recibos | Modal vista previa de todos los recibos | ☐ |
| 9.2 | Verificar recibo individual | Logo, datos empleado, periodo, conceptos desglosados, totales, área firma | ☐ |
| 9.3 | Clic Imprimir | Nueva pestaña con formato de impresión | ☐ |
| 9.4 | Clic Descargar PDF | PDF multi-página con todos los recibos | ☐ |
| 9.5 | Clic PDF por empleado (individual) | Descarga recibo individual vía html2pdf | ☐ |

---

### BLOQUE 10: Alertas — Campana Live

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 10.1 | Revisar campana en header ERP | Badge numérico rojo si hay nóminas vencidas/próximas | ☐ |
| 10.2 | Clic campana | Notificaciones con ícono `$`, etiquetas danger/warning/info | ☐ |
| 10.3 | Contenido notificación | Folio, tipo, periodo, neto | ☐ |
| 10.4 | Clic notificación | Redirige a `/rh/Nomina` | ☐ |

---

### BLOQUE 11: Cancelación y Notas

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 11.1 | Cancelar nómina Calculada (motivo ≥10 chars) | Estatus → Cancelada, badge rojo, registro en `nominas_cancelaciones` | ☐ |
| 11.2 | Intentar cancelar Pagada | Error: "Use el sistema de notas de ajuste" | ☐ |
| 11.3 | Agregar nota de ajuste en Pagada | Se guarda, aparece en sección colapsable | ☐ |
| 11.4 | Filtrar por Cancelada | Solo muestra canceladas | ☐ |
| 11.5 | Modal detalle en Pagada/Cancelada | Sin celdas editables, footer "Nómina finalizada" | ☐ |

---

### BLOQUE 12: Planeador Mensual

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 12.1 | Clic **Planeador** (calendario) | Modal con grid de periodos del mes actual | ☐ |
| 12.2 | Verificar cards | Cada periodo muestra: rango de fechas, estatus (badge color), folio, monto neto | ☐ |
| 12.3 | Navegar mes: `<` `>` | Cambia a mes anterior/siguiente. Título se actualiza | ☐ |
| 12.4 | Cambiar vista: Semanal → Quincenal → Mensual | Grid se re-renderiza con periodos correctos | ☐ |
| 12.5 | Colores por estatus | Pagada=verde, Calculada=amarillo, Parcial=azul, Cancelada=rojo, Borrador=gris, Sin nómina=blanco con `+` | ☐ |
| 12.6 | Clic en card de nómina existente | Abre detalle de esa nómina | ☐ |
| 12.7 | Clic en card "Sin nómina" (`+`) | Sugiere crear nómina para ese periodo | ☐ |
| 12.8 | Leyenda de colores | 3-5 badges `-subtle` con etiquetas visibles | ☐ |

---

### BLOQUE 13: Responsive y UX

| # | Acción | Verificación | ✓ |
|:--|:-------|:-------------|:--|
| 13.1 | Reducir ventana a 375px | Tablas con scroll horizontal, filtros no se enciman | ☐ |
| 13.2 | Modal detalle en 375px | Ocupa fullscreen, scroll funcional | ☐ |
| 13.3 | Buscador "Buscar trabajador..." en detalle | Filtra filas en tiempo real | ☐ |
| 13.4 | Buscador en Procesar Pago | Filtra empleados | ☐ |

---

### BLOQUE 14: Flujo Completo del Contador

| # | Acción | Resultado | ✓ |
|:--|:-------|:----------|:--|
| 14.1 | Lunes: abrir ERP, revisar campana | Notificaciones de nóminas por pagar | ☐ |
| 14.2 | Ir a Nómina, revisar Calculada | Totales visibles | ☐ |
| 14.3 | Abrir detalle, verificar montos | Columnas completas | ☐ |
| 14.4 | Editar HE/comidas si aplica | Guardado inline, recálculo | ☐ |
| 14.5 | Verificar cuentas bancarias | CLABE y banco visibles | ☐ |
| 14.6 | Procesar pago completo | Estatus → Pagada | ☐ |
| 14.7 | Exportar Excel (3 hojas) | Archivo listo para banco | ☐ |
| 14.8 | Generar recibos PDF | PDF para empleados | ☐ |

---

**Total: 14 bloques, ~72 verificaciones manuales.**

---

## PARTE 3 — COMANDOS RÁPIDOS DE REFERENCIA

```bash
# Re-seed (reset + datos demo)
cd /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html
/usr/local/php82/bin/php database/run_seed_nominas_reset_demo.php apply

# Solo reset (borrar todas las nóminas)
/usr/local/php82/bin/php database/run_seed_nominas_reset_demo.php revert

# Re-aplicar fix de salarios (si se pierden los datos)
/usr/local/php83/bin/php database/fix_y_reseed.php

# Verificar cron de auto-creación
php index.php rh/Nomina verificar_auto_nomina_ajax

# Probar endpoint del planeador
curl -s "https://erp.chisarecubrimientos.com.mx/rh/Nomina/planeador_mensual_ajax?mes=7&anio=2026&tipo=Semanal"
```
