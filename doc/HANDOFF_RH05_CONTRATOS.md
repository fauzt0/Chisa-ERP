# HANDOFF — RH-05: Mejora del Sistema de Contratos PDF

> **Proyecto:** ERP Chisa Recubrimientos (CodeIgniter 3 + Bootstrap 4/AdminLTE)  
> **Fecha handoff:** 2026-06-25  
> **Fase:** PLAN_MEJORA.md — RH-05 (Contratos PDF)  
> **Estado global:** ~80% completado  

---

## RESUMEN DE LO QUE SE ESTABA HACIENDO

El objetivo de **RH-05** es mejorar drásticamente el sistema de contratos PDF para que genere documentos profesionales, con membrete corporativo, variables inteligentes reemplazables, 4 plantillas precargadas, previsualización en vivo, y generación de PDF con bordes y formato carta profesional.

---

## LO QUE YA SE HIZO (COMPLETADO)

### 1. `public_html/application/views/rh/empleados/form_plantilla.php` — TERMINADO
**Editor de plantillas completamente rediseñado:**

- **Header:** Gradient dark con iconos Lucide, título "Nueva/Editar Plantilla"
- **Previsualización en vivo:** Panel toggleable con zoom (50%/75%/100%), refresh, renderiza el contenido del editor con CSS aplicado en un div que simula hoja carta (800px ancho, box-shadow)
- **Campos nuevos:**
  - Color corporativo (input type="color", default #1a3a5c)
  - Domicilio fiscal
  - Logo upload
- **Sidebar izquierdo:** Panel de variables dinámicas agrupadas (Personales, Laborales, Contrato, Firmas) — 30+ variables con click-to-copy y feedback visual (bg-success temporal)
- **Botones de inserción rápida:** Firmas, Membrete, Testigos (con sello)
- **TinyMCE:** Height 650px, menubar activado, plugins completos, toolbar con blocks, underline, strikethrough, forecolor, table, image, pagebreak, fullscreen, code
- **Generación PDF profesional:** Crea wrapper con borde corporativo de 2px, estiliza headings con Georgia, escala 2x, formato letter, márgenes 20mm
- **4 modelos precargados en dropdown:**
  1. **Legal LFT Completo** — Contrato individual con 12 cláusulas, membrete corporativo, declaraciones I-II, firmas trabajador/empresa + testigos
  2. **Contrato Ejecutivo** — Header con gradiente corporativo, tabla de datos empleado/condiciones, 4 cláusulas para personal de confianza
  3. **Contrato Operativo** — Membrete simple, 7 cláusulas concisas, lenguaje directo para producción
  4. **Acuerdo de Confidencialidad** — Borde destacado, 5 cláusulas, espacio para sello

### 2. `public_html/application/views/rh/empleados/nuevo_contrato.php` — TERMINADO (460 líneas)
**Vista de generación de contrato para empleado específico, completamente rediseñada:**

- **Header:** Gradient dark con nombre del empleado, N° empleado, puesto
- **Previsualización en vivo:** Igual que form_plantilla, pero con indicador "Variables ya reemplazadas con datos de [nombre]"
- **Selector de plantilla:** Combina dropdown de plantillas guardadas + modelos predefinidos
- **Sidebar derecha:** Datos del empleado (nombre, RFC, CURP, NSS, puesto, departamento, tipo, salario, fecha ingreso)
- **Botón "Reemplazar Variables":** Sustituye todos los placeholders en el editor con datos reales del empleado
- **Función `replacePlaceholders()`:** Mapa completo de 30+ variables con datos reales del empleado desde PHP
- **Función `cargarModeloRapido()`:** Carga plantilla guardada vía AJAX o modelo predefinido con HTML inline
- **Historial de contratos:** Tabla mejorada con badges, botones Ver/PDF individuales
- **Modal ver contrato:** Modal-xl con cuerpo estilizado (Times New Roman, 12pt), botón descargar PDF
- **PDF profesional:** Misma calidad que form_plantilla (borde corporativo, headings Georgia, escala 2x)

---

## LO QUE FALTA POR HACER (PENDIENTE)

### 3. ACTUALIZAR `ContratoModel.php` — `obtener_reemplazos()`

**Archivo:** `public_html/application/models/RH/ContratoModel.php`  
**Método:** `obtener_reemplazos($empleado, $contrato_data = [])` (línea ~174-222)

El modelo actual NO tiene estos placeholders nuevos que usan las plantillas. Añadir al array de retorno:

```php
'{{color_corporativo}}' => $contrato_data['color_corporativo'] ?? '#1a3a5c',
'{{ciudad_contrato}}' => $contrato_data['ciudad_contrato'] ?? 'CD. JUÁREZ, CHIHUAHUA',
'{{numero_empleado}}' => $empleado->numero_empleado ?? 'N/A',
'{{logo_empresa}}' => '',
'{{email}}' => $empleado->email_personal ?? $empleado->email_corporativo ?? 'N/A',
'{{telefono}}' => $empleado->telefono ?? 'N/A',
'{{firma_testigo1}}' => '<br><br><div style="border-top:1px solid #999; width:200px; text-align:center; padding-top:5px; margin:0 auto;">TESTIGO<br><small>Nombre y Firma</small></div>',
'{{firma_testigo2}}' => '<br><br><div style="border-top:1px solid #999; width:200px; text-align:center; padding-top:5px; margin:0 auto;">TESTIGO<br><small>Nombre y Firma</small></div>',
```

### 4. ACTUALIZAR CONTROLADOR — `guardar_plantilla()`

**Archivo:** `public_html/application/controllers/rh/RecursosHumanos.php`  
**Método:** `guardar_plantilla()` (línea ~954)

En el array `$data`, añadir estas dos líneas:

```php
'color_corporativo' => $this->input->post('color_corporativo'),
'domicilio_empresa' => $this->input->post('domicilio_empresa'),
```

### 5. BASE DE DATOS — Añadir columna `color_corporativo`

```sql
ALTER TABLE contrato_plantillas 
ADD COLUMN color_corporativo VARCHAR(7) DEFAULT '#1a3a5c' AFTER logo;
```

### 6. OPCIONAL — Rediseñar `plantillas.php`

La vista de listado (`public_html/application/views/rh/empleados/plantillas.php`) sigue con el diseño antiguo. Se recomienda actualizar para consistencia visual.

---

## ARCHIVOS INVOLUCRADOS

| Archivo | Estado | Acción |
|---------|--------|--------|
| `views/rh/empleados/form_plantilla.php` | Terminado | — |
| `views/rh/empleados/nuevo_contrato.php` | Terminado (460 líneas) | — |
| `models/RH/ContratoModel.php` | Pendiente | Añadir placeholders a `obtener_reemplazos()` |
| `controllers/rh/RecursosHumanos.php` | Pendiente | Guardar `color_corporativo` y `domicilio_empresa` |
| BD `contrato_plantillas` | Pendiente | Añadir columna `color_corporativo` |
| `views/rh/empleados/plantillas.php` | Opcional | Rediseño visual |

---

## PLACEHOLDERS QUE FALTAN EN EL MODELO

| Placeholder | Origen | Estado |
|------------|--------|--------|
| `{{nombre_completo}}` | empleado | Existe |
| `{{rfc}}` | empleado | Existe |
| `{{curp}}` | empleado | Existe |
| `{{nss}}` | empleado | Existe |
| `{{nacionalidad}}` | empleado | Existe |
| `{{edad}}` | calculado | Existe |
| `{{genero}}` / `{{sexo}}` | empleado | Existe |
| `{{estado_civil}}` | empleado | Existe |
| `{{domicilio}}` | empleado | Existe |
| `{{beneficiarios}}` | empleado | Existe |
| `{{puesto}}` | empleado/contrato | Existe |
| `{{departamento}}` | contrato_data | Existe |
| `{{tipo_trabajador}}` | empleado/contrato | Existe |
| `{{tipo_contrato}}` | contrato_data | Existe |
| `{{tipo_nomina}}` | empleado/contrato | Existe |
| `{{jornada_laboral}}` | contrato_data | Existe |
| `{{salario_base_mensual}}` | contrato_data | Existe |
| `{{salario_base_diario}}` | contrato_data | Existe |
| `{{lugar_pago}}` | calculado | Existe |
| `{{fecha_inicio}}` | contrato_data | Existe |
| `{{fecha_generacion}}` | date() | Existe |
| `{{version}}` | contrato_data | Existe |
| `{{motivo_cambio}}` | contrato_data | Existe |
| `{{domicilio_empresa}}` | contrato_data | Existe |
| `{{firma_empleado_espacio}}` | generado | Existe |
| `{{firma_empresa_espacio}}` | generado | Existe |
| `{{numero_empleado}}` | empleado | **FALTA** |
| `{{telefono}}` | empleado | **FALTA** |
| `{{email}}` | empleado | **FALTA** |
| `{{ciudad_contrato}}` | config | **FALTA** |
| `{{color_corporativo}}` | plantilla | **FALTA** |
| `{{logo_empresa}}` | plantilla | **FALTA** |
| `{{firma_testigo1}}` | generado | **FALTA** |
| `{{firma_testigo2}}` | generado | **FALTA** |

---

## ESTILO VISUAL CONSISTENTE

Todas las nuevas vistas siguen:
- **Header:** `bg-gradient-dark text-white` con iconos Lucide
- **Botones:** `btn-outline-light` sobre dark, `btn-danger` para PDF, `btn-light` para preview
- **Dropdowns:** `dropdown-menu-end shadow` con headers
- **Preview:** Panel toggleable con zoom, 800px centrado, box-shadow, Times New Roman 12pt
- **PDF:** Wrapper con borde 2px color corporativo, padding 40px, html2canvas scale 2, letter

---

## ORDEN RECOMENDADO PARA FINALIZAR

1. Ejecutar SQL para columna `color_corporativo`
2. Actualizar `guardar_plantilla()` en el controlador
3. Actualizar `obtener_reemplazos()` en ContratoModel
4. Probar: crear plantilla, generar contrato, verificar reemplazo de variables
5. Probar generación de PDF desde ambas vistas
6. (Opcional) Rediseñar `plantillas.php`

---

## CONTEXTO TÉCNICO

- **Framework:** CodeIgniter 3, MySQL/MariaDB, Bootstrap 4 + AdminLTE 3
- **Iconos:** Lucide Icons — `lucide.createIcons()` después de cambios DOM
- **Notificaciones:** `notifyShow(message, type)`
- **Librerías:** TinyMCE 6.8.2, html2pdf.js 0.10.1 (ambas CDN)
- **Docs:** `REGLAS_TECNICAS.md`, `PLAN_MEJORA.md`, `DOCUMENTACION_TECNICA.md`
