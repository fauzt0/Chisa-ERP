# PLAN DE EJECUCIÓN — PLANEADOR MENSUAL DE NÓMINAS

> **Objetivo:** Implementar un calendario/planeador visual que muestre los periodos de nómina del mes, indique cuáles ya están cubiertos (con estatus y folio), y permita crear nóminas directamente desde el panel.
>
> **Ejecutor:** Composer 2.5 / Grok
>
> **Entorno:** CodeIgniter 3 + Bootstrap 5 + jQuery + DataTables + SweetAlert2
>
> **Fecha del plan:** 30 de julio de 2026

---

## Índice

| Sección | Descripción |
|:--------|:------------|
| [Resumen visual](#resumen-visual) | Mockup conceptual de cómo se verá |
| [Fase 1: Backend](#fase-1-backend) | Endpoint AJAX + método en el modelo |
| [Fase 2: Frontend — HTML](#fase-2-frontend--html) | Modal, grid de periodos, botón disparador |
| [Fase 3: Frontend — JS](#fase-3-frontend--js) | Lógica de renderizado, navegación, interacciones |
| [Fase 4: CSS](#fase-4-css) | Estilos del grid, colores por estatus, responsive |
| [Fase 5: Auto-detección de Lugar de Origen](#fase-5-auto-detección-de-lugar-de-origen) | Detectar automáticamente Oficina/Obra/nombre de obra |
| [Instrucciones de implementación](#instrucciones-de-implementación) | Paso a paso para el agente |

---

## Resumen visual

```
┌─────────────────────────────────────────────────────────┐
│  📅 Planeador Mensual               ← Julio 2026 →     │
│                                                         │
│  ┌──────────┬──────────┬──────────┬──────────┬────────┐ │
│  │ Semana 1 │ Semana 2 │ Semana 3 │ Semana 4 │ Sem 5  │ │
│  │ 30jun-6j │ 7-13 jul │ 14-20jul │ 21-27jul │28j-3ag │ │
│  │          │          │          │          │        │ │
│  │ ✅Pagada │ ✅Pagada │ ✅Pagada │ ⬜ Calc. │ ➕ Crear│ │
│  │NOM000022 │NOM000023 │NOM000024 │NOM000025 │        │ │
│  │$45,230   │$44,100   │$46,500   │$43,890   │        │ │
│  └──────────┴──────────┴──────────┴──────────┴────────┘ │
│                                                         │
│  Vista: ○ Semanal  ○ Quincenal  ○ Mensual              │
└─────────────────────────────────────────────────────────┘
```

**Leyenda de colores:**

| Estatus | Fondo | Texto | Badge |
|:--------|:------|:------|:------|
| Sin nómina | Blanco / `bg-light` | `text-muted` | Botón `+ Crear` |
| Borrador | `bg-secondary-subtle` | `text-dark` | `Borrador` |
| Calculada | `bg-warning-subtle` | `text-dark` | `Calculada` |
| Parcial | `bg-info-subtle` | `text-dark` | `Parcial` |
| Pagada | `bg-success-subtle` | `text-dark` | `Pagada` |
| Cancelada | `bg-danger-subtle` | `text-dark` | `Cancelada` |

---

## Fase 1: Backend

### 1.1 Nuevo endpoint: `planeador_mensual_ajax`

**Archivo:** `application/controllers/rh/Nomina.php`

```php
/**
 * Retorna todas las nóminas de un mes para el planeador.
 * GET params: mes (1-12), anio (YYYY), tipo (Semanal|Quincenal|Mensual)
 */
public function planeador_mensual_ajax() {
    $this->requiere_permiso('rh_nomina');
    $mes  = (int)$this->input->get('mes') ?: (int)date('m');
    $anio = (int)$this->input->get('anio') ?: (int)date('Y');
    $tipo = $this->input->get('tipo') ?: 'Semanal';

    $result = $this->NominaRhModel->get_planeador_mensual($mes, $anio, $tipo);
    echo json_encode(['success' => true, 'periodos' => $result, 'mes' => $mes, 'anio' => $anio, 'tipo' => $tipo]);
}
```

### 1.2 Nuevo método en el modelo: `get_planeador_mensual`

**Archivo:** `application/models/RH/NominaRhModel.php`

```php
/**
 * Genera la lista de periodos del mes con su nómina asociada (si existe).
 *
 * @param int $mes  1-12
 * @param int $anio YYYY
 * @param string $tipo Semanal|Quincenal|Mensual
 * @return array  Cada elemento: ['inicio','fin','nomina'=>null|object,'periodo_label'=>string]
 */
public function get_planeador_mensual($mes, $anio, $tipo) {
    $periodos = $this->_generar_periodos_mes($mes, $anio, $tipo);
    if (empty($periodos)) {
        return [];
    }

    // Consultar TODAS las nóminas que intersectan este mes (incluyendo periodos que abarcan fin de mes)
    $primer_inicio = $periodos[0]['inicio'];
    $ultimo_fin    = $periodos[count($periodos) - 1]['fin'];

    $nominas = $this->db
        ->select('id, folio, tipo_nomina, periodo_inicio, periodo_fin, estatus, total_neto')
        ->from('nominas')
        ->where('periodo_inicio >=', $primer_inicio)
        ->where('periodo_fin <=', date('Y-m-t', strtotime($anio . '-' . $mes . '-01')))
        ->or_where('periodo_fin >=', $primer_inicio)
        ->where('periodo_inicio <=', $ultimo_fin)
        ->get()
        ->result();

    // Indexar nóminas por periodo_inicio
    $mapa = [];
    foreach ($nominas as $nom) {
        $mapa[$nom->periodo_inicio] = $nom;
    }

    // Asignar nómina a cada periodo
    foreach ($periodos as &$p) {
        $p['nomina'] = $mapa[$p['inicio']] ?? null;
    }

    return $periodos;
}

/**
 * Genera los periodos del mes según el tipo de nómina.
 */
private function _generar_periodos_mes($mes, $anio, $tipo) {
    $periodos = [];
    $primer_dia = "$anio-$mes-01";
    $ultimo_dia = date('Y-m-t', strtotime($primer_dia));

    switch ($tipo) {
        case 'Semanal':
            // Encontrar el lunes de la semana que contiene el día 1
            $cursor = date('Y-m-d', strtotime('monday this week', strtotime($primer_dia)));
            // Si el lunes está antes del día 1, avanzar una semana
            if ($cursor < $primer_dia) {
                $cursor = date('Y-m-d', strtotime($cursor . ' +7 days'));
            }
            while ($cursor <= $ultimo_dia) {
                $fin = date('Y-m-d', strtotime($cursor . ' +6 days'));
                $periodos[] = [
                    'inicio' => $cursor,
                    'fin'    => $fin,
                    'label'  => date('d M', strtotime($cursor)) . ' – ' . date('d M', strtotime($fin)),
                ];
                $cursor = date('Y-m-d', strtotime($cursor . ' +7 days'));
            }
            break;

        case 'Quincenal':
            $periodos[] = [
                'inicio' => date('Y-m-01', strtotime($primer_dia)),
                'fin'    => date('Y-m-15', strtotime($primer_dia)),
                'label'  => '1ra Quincena: ' . date('d M', strtotime($primer_dia)) . ' – ' . date('d M', strtotime(date('Y-m-15', strtotime($primer_dia)))),
            ];
            $periodos[] = [
                'inicio' => date('Y-m-16', strtotime($primer_dia)),
                'fin'    => $ultimo_dia,
                'label'  => '2da Quincena: ' . date('d M', strtotime(date('Y-m-16', strtotime($primer_dia)))) . ' – ' . date('d M', strtotime($ultimo_dia)),
            ];
            break;

        case 'Mensual':
            $periodos[] = [
                'inicio' => $primer_dia,
                'fin'    => $ultimo_dia,
                'label'  => date('F Y', strtotime($primer_dia)),
            ];
            break;
    }

    return $periodos;
}
```

**Nota técnica:** El seeder existente ya genera nóminas con `periodo_inicio` que caen en lunes (`_periodo_para_fecha_inicio` en el modelo). El mapeo `$mapa[$nom->periodo_inicio]` funciona porque el inicio identifica de forma única el periodo para un tipo dado.

---

## Fase 2: Frontend — HTML

### 2.1 Botón disparador

Agregar en la barra de acciones de `main.php` (junto a "Empleados", "Automatización", "Nueva Nómina"):

```html
<button type="button" class="btn btn-outline-info" onclick="abrirPlaneador()" title="Ver calendario de nóminas del mes">
  <i data-lucide="calendar" style="width:16px;height:16px;"></i> Planeador
</button>
```

### 2.2 Modal del Planeador

Agregar al final de `main.php` (antes del `<script>`):

```html
<!-- Modal: Planeador Mensual -->
<div class="modal fade rh-modal" id="modalPlaneador" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-scrollable" style="max-width: 95vw;">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title text-white mb-0">
          <i class="fas fa-calendar-alt me-2"></i>
          Planeador de Nóminas — <span id="planeador-titulo-mes">Julio 2026</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
          <!-- Selector de tipo de vista -->
          <div class="btn-group btn-group-sm" role="group">
            <input type="radio" class="btn-check" name="planeadorTipo" id="planeadorTipoSemanal" value="Semanal" checked>
            <label class="btn btn-outline-light" for="planeadorTipoSemanal">Semanal</label>
            <input type="radio" class="btn-check" name="planeadorTipo" id="planeadorTipoQuincenal" value="Quincenal">
            <label class="btn btn-outline-light" for="planeadorTipoQuincenal">Quincenal</label>
            <input type="radio" class="btn-check" name="planeadorTipo" id="planeadorTipoMensual" value="Mensual">
            <label class="btn btn-outline-light" for="planeadorTipoMensual">Mensual</label>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body">

        <!-- Navegación de mes -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="navegarPlaneador(-1)">
            <i class="fas fa-chevron-left"></i> Mes anterior
          </button>
          <h4 class="mb-0" id="planeadorLabelMes">Julio 2026</h4>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="navegarPlaneador(1)">
            Mes siguiente <i class="fas fa-chevron-right"></i>
          </button>
        </div>

        <!-- Grid de periodos -->
        <div id="planeadorGrid" class="row g-3">
          <!-- Se llena dinámicamente vía JS -->
        </div>

        <!-- Leyenda -->
        <div class="mt-4 pt-3 border-top">
          <div class="d-flex flex-wrap gap-3 small">
            <span><span class="badge bg-success-subtle text-dark me-1">■</span> Pagada</span>
            <span><span class="badge bg-warning-subtle text-dark me-1">■</span> Calculada</span>
            <span><span class="badge bg-info-subtle text-dark me-1">■</span> Parcial</span>
            <span><span class="badge bg-secondary-subtle text-dark me-1">■</span> Borrador</span>
            <span><span class="badge bg-danger-subtle text-dark me-1">■</span> Cancelada</span>
            <span><span class="badge bg-light border text-muted me-1">■</span> Sin nómina</span>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
```

---

## Fase 3: Frontend — JS

### 3.1 Variables de estado

```javascript
var planeadorMes = <?= (int)date('m') ?>;
var planeadorAnio = <?= (int)date('Y') ?>;
var planeadorTipo = 'Semanal';
```

### 3.2 Función principal: `abrirPlaneador()`

```javascript
function abrirPlaneador() {
  planeadorMes = <?= (int)date('m') ?>;
  planeadorAnio = <?= (int)date('Y') ?>;
  planeadorTipo = 'Semanal';
  $('#planeadorTipoSemanal').prop('checked', true);
  $('#modalPlaneador').modal('show');
  cargarPlaneador();
}
```

### 3.3 Cargar datos: `cargarPlaneador()`

```javascript
function cargarPlaneador() {
  var meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
               'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  $('#planeadorLabelMes').text(meses[planeadorMes - 1] + ' ' + planeadorAnio);
  $('#planeador-titulo-mes').text(meses[planeadorMes - 1] + ' ' + planeadorAnio);

  $.get('<?= base_url("rh/Nomina/planeador_mensual_ajax") ?>', {
    mes: planeadorMes,
    anio: planeadorAnio,
    tipo: planeadorTipo
  }, function(r) {
    if (!r.success) return;
    renderizarGrid(r.periodos);
  });
}
```

### 3.4 Renderizar grid: `renderizarGrid(periodos)`

```javascript
function renderizarGrid(periodos) {
  var $grid = $('#planeadorGrid');
  $grid.empty();

  if (!periodos || periodos.length === 0) {
    $grid.html('<div class="col-12 text-center text-muted py-5">No hay periodos para mostrar en este mes.</div>');
    return;
  }

  // Ancho de columna: 12 para 1 periodo (mensual), 6 para 2 (quincenal), o automático para semanal
  var colClass = 'col';
  if (periodos.length === 1) colClass = 'col-12';
  else if (periodos.length === 2) colClass = 'col-md-6';
  else if (periodos.length === 4) colClass = 'col-md-3';
  else if (periodos.length === 5) colClass = 'col-md'; // flex

  periodos.forEach(function(p) {
    var nom = p.nomina;
    var cardClass = 'border';
    var bgClass = '';
    var badgeHtml = '';
    var clickAction = '';

    if (nom) {
      // Tiene nómina
      var statusMap = {
        'Pagada':    { bg: 'bg-success-subtle', badge: 'success', icon: '✅' },
        'Calculada': { bg: 'bg-warning-subtle', badge: 'warning', icon: '⬜' },
        'Parcial':   { bg: 'bg-info-subtle',    badge: 'info',    icon: '◐' },
        'Borrador':  { bg: 'bg-secondary-subtle', badge: 'secondary', icon: '📝' },
        'Cancelada': { bg: 'bg-danger-subtle',  badge: 'danger',  icon: '❌' }
      };
      var s = statusMap[nom.estatus] || { bg: 'bg-light', badge: 'secondary', icon: '' };
      bgClass = s.bg;
      badgeHtml = '<span class="badge bg-' + s.badge + '">' + nom.estatus + '</span>';
      clickAction = 'onclick="verNominaDesdePlaneador(' + nom.id + ')"';
    } else {
      // Sin nómina
      bgClass = 'bg-light';
      badgeHtml = '<span class="badge bg-light text-muted border">Sin nómina</span>';
      clickAction = 'onclick="crearNominaDesdePlaneador(\'' + p.inicio + '\',\'' + p.fin + '\')"';
    }

    var cardHtml = '<div class="' + colClass + '">' +
      '<div class="card h-100 shadow-sm ' + bgClass + ' ' + cardClass + ' planeador-card" ' +
           'style="cursor:pointer;transition:transform 0.15s;" ' +
           clickAction + ' ' +
           'title="' + (nom ? nom.folio + ' — ' + (nom.estatus || '') + ' — Neto: $' + fmtNum(nom.total_neto || 0) : 'Clic para crear nómina en este periodo') + '">' +
        '<div class="card-body text-center py-3">' +
          '<div class="text-muted small mb-1">' + p.label + '</div>' +
          '<div class="fs-3 mb-2">' + (nom ? '📋' : '➕') + '</div>' +
          '<div class="fw-bold">' + (nom ? nom.folio : '') + '</div>' +
          '<div class="mb-1">' + badgeHtml + '</div>' +
          (nom ? '<div class="fw-semibold text-dark">$' + fmtNum(nom.total_neto || 0) + '</div>' : '') +
          '<div class="mt-2 small ' + (nom ? 'text-primary' : 'text-success') + '">' +
            (nom ? '<i class="fas fa-eye"></i> Ver detalle' : '<i class="fas fa-plus-circle"></i> Crear nómina') +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';

    $grid.append(cardHtml);
  });
}
```

### 3.5 Navegación: `navegarPlaneador(dir)`

```javascript
function navegarPlaneador(dir) {
  planeadorMes += dir;
  if (planeadorMes < 1) { planeadorMes = 12; planeadorAnio--; }
  if (planeadorMes > 12) { planeadorMes = 1; planeadorAnio++; }
  cargarPlaneador();
}
```

### 3.6 Cambio de tipo de vista

```javascript
// Listener para los radio buttons de tipo
$('input[name="planeadorTipo"]').on('change', function() {
  planeadorTipo = $(this).val();
  cargarPlaneador();
});
```

### 3.7 Acciones desde el planeador

```javascript
function verNominaDesdePlaneador(nominaId) {
  $('#modalPlaneador').modal('hide');
  // Pequeño delay para que termine la animación del modal
  setTimeout(function() {
    verNomina(nominaId); // Función existente en main.js
  }, 300);
}

function crearNominaDesdePlaneador(inicio, fin) {
  $('#modalPlaneador').modal('hide');
  setTimeout(function() {
    mostrarModalNuevo(); // Función existente
    // Pre-llenar fechas
    $('#periodoInicio').val(inicio);
    $('#periodoFin').val(fin);
    // Disparar cambio para recalcular periodo_fin si aplica
    $('#periodoInicio').trigger('change');
  }, 300);
}
```

---

## Fase 4: CSS

### 4.1 Estilos (agregar en el `<style>` de `main.php`)

```css
/* --- Planeador Mensual --- */

/* Card del planeador: efecto hover */
.planeador-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  min-height: 160px;
}
.planeador-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.12) !important;
}

/* Asegurar que el grid no se desborde */
#planeadorGrid .col,
#planeadorGrid .col-md,
#planeadorGrid .col-md-3,
#planeadorGrid .col-md-6,
#planeadorGrid .col-12 {
  min-width: 0;
}

/* Botones del header del planeador en móvil */
@media (max-width: 575px) {
  #modalPlaneador .btn-group-sm .btn {
    padding: 0.2rem 0.4rem;
    font-size: 0.7rem;
  }
  #modalPlaneador .modal-header {
    flex-direction: column;
    align-items: flex-start !important;
  }
  #modalPlaneador .modal-header .d-flex {
    width: 100%;
    justify-content: space-between;
  }
}
```

---

## Fase 5: Auto-detección de Lugar de Origen

> **Objetivo:** Al crear o calcular una nómina, el campo `lugar_origen` de cada empleado se auto-detecta según su departamento y asignación a obra. El valor se puede sobreescribir manualmente con doble clic (edición inline ya existente).

### Reglas de auto-detección

| Condición | `lugar_origen` resultante |
|:----------|:--------------------------|
| Departamento **NO es "Obras"** (cualquier otro) | `"Oficina"` |
| Departamento **es "Obras"** y `empleados.lugar_pago` tiene valor | El valor de `lugar_pago` (nombre de la obra) |
| Departamento **es "Obras"** y `empleados.lugar_pago` está vacío/null | `"Obra"` |

**Datos requeridos:**
- Tabla `departamentos`: columna `nombre`. El departamento "Obras" se identifica por tener `nombre = 'Obras'`.
- Tabla `empleados`: columnas `departamento_id` y `lugar_pago`.

### 5.1 Backend — Método helper en `NominaRhModel.php`

```php
/**
 * Determina el lugar de origen para un empleado según su departamento.
 *
 * Reglas:
 * - Si el departamento NO es "Obras" → "Oficina"
 * - Si es "Obras" y tiene lugar_pago definido → el nombre de la obra (lugar_pago)
 * - Si es "Obras" y lugar_pago está vacío → "Obra"
 *
 * @param object $empleado  Row de la tabla empleados (debe incluir departamento_id y lugar_pago)
 * @return string
 */
public function get_lugar_origen_empleado($empleado) {
    if (empty($empleado->departamento_id)) {
        return 'Oficina';
    }

    // Verificar si el departamento es "Obras"
    $dept = $this->db
        ->select('nombre')
        ->where('id', (int)$empleado->departamento_id)
        ->get('departamentos')
        ->row();

    $es_obras = $dept && strtolower(trim($dept->nombre)) === 'obras';

    if (!$es_obras) {
        return 'Oficina';
    }

    // Es Obras: usar lugar_pago si existe, si no "Obra"
    $lugar = trim((string)($empleado->lugar_pago ?? ''));
    return $lugar !== '' ? $lugar : 'Obra';
}
```

### 5.2 Backend — Integrar en `agregar_empleados_nomina()`

Modificar el método existente (línea 29-48) para que use el helper en vez de copiar `lugar_pago` directamente.

**Cambio necesario:**

```php
// Antes (línea 31):
->select('id, lugar_pago, forma_pago')

// Después:
->select('id, lugar_pago, forma_pago, departamento_id')

// Antes (línea 42):
'lugar_origen'=> $emp->lugar_pago ?? '',

// Después:
'lugar_origen'=> $this->get_lugar_origen_empleado($emp),
```

Esto aplica cuando se crea una nómina nueva (manual o desde el planeador). Todos los empleados agregados automáticamente reciben su `lugar_origen` auto-detectado.

### 5.3 Backend — Integrar en `calcular_nomina()`

En el método `calcular_nomina()`, el SELECT (líneas 63-71) ya incluye `e.lugar_pago`. Se debe agregar `e.departamento_id` al SELECT y usar el helper al actualizar `lugar_origen`.

**Cambio necesario en el SELECT (agregar `e.departamento_id`):**

```php
$this->db->select('
    nd.id, nd.empleado_id,
    e.salario_base_mensual, e.salario_base_diario,
    e.isr_porcentaje, e.imss_cuota,
    e.pension_alimenticia_porcentaje, e.pension_alimenticia_monto,
    e.descuento_infonavit, e.tiene_infonavit, e.infonavit_aportacion,
    e.costo_hora_extra,
    e.lugar_pago, e.departamento_id
');
```

**Cambio necesario en el UPDATE del detalle (línea 118):**

```php
// Antes:
'lugar_origen' => $det->lugar_pago ?? '',

// Después:
'lugar_origen' => $this->get_lugar_origen_empleado($det),
```

Esto recalcula el `lugar_origen` cada vez que se ejecuta "Calcular Nómina", manteniéndolo sincronizado con los datos actuales del empleado.

### 5.4 Frontend — Edición inline (ya existe, no requiere cambios)

La columna `lugar_origen` en la tabla de detalle (`renderTablaDetalle`) ya tiene la clase `editable` y el atributo `data-field="lugar_origen"`:

```javascript
html += '<td class="' + editableCls + ' text-center" data-field="lugar_origen"' + editableTitle + '>' + esc(d.lugar_origen) + '</td>';
```

El usuario puede hacer **doble clic** sobre el valor para editarlo manualmente. El valor auto-detectado es solo un punto de partida.

### 5.5 Integración con el Planeador Mensual

Cuando el usuario crea una nómina desde el planeador (`crearNominaDesdePlaneador`), el flujo es:

1. Se abre el modal "Nueva Nómina" con fechas pre-llenadas
2. El usuario confirma la creación
3. Se llama a `guardarNomina()` → que internamente inserta en `nominas` y luego llama a `agregar_empleados_nomina()`
4. `agregar_empleados_nomina()` ahora usa `get_lugar_origen_empleado()` automáticamente

**No se requiere código adicional en el frontend del planeador.** La auto-detección ocurre transparentemente en el backend.

---

## Instrucciones de implementación

El agente (Composer/Grok) debe seguir este orden:

### Paso 1: Modelo
1. Agregar `get_planeador_mensual($mes, $anio, $tipo)` en `NominaRhModel.php` (copiar del código arriba)
2. Agregar `_generar_periodos_mes($mes, $anio, $tipo)` en `NominaRhModel.php`

### Paso 2: Controlador
3. Agregar `planeador_mensual_ajax()` en `controllers/rh/Nomina.php`
4. Verificar que el permiso `rh_nomina` cubre este endpoint (el constructor ya lo maneja)

### Paso 3: Vista — HTML
5. Agregar el botón "Planeador" en la barra de acciones (junto a Empleados, Automatización, Nueva Nómina)
6. Agregar el modal `#modalPlaneador` antes del `<script>` de `main.php`

### Paso 4: Vista — JS
7. Agregar las variables `planeadorMes`, `planeadorAnio`, `planeadorTipo` al inicio del script
8. Agregar todas las funciones JS descritas en Fase 3
9. Agregar el listener de cambio de tipo de vista (`$('input[name="planeadorTipo"]')`)

### Paso 5: Vista — CSS
10. Agregar los estilos CSS de la Fase 4 en el bloque `<style>` existente

### Paso 6: Modelo — Lugar de Origen (Fase 5)
11. Agregar `get_lugar_origen_empleado($empleado)` en `NominaRhModel.php`
12. Modificar `agregar_empleados_nomina()`: agregar `departamento_id` al SELECT, usar helper en `lugar_origen`
13. Modificar `calcular_nomina()`: agregar `e.departamento_id` al SELECT, usar helper en `lugar_origen`

### Paso 7: Verificación del Planeador
14. Navegar a `/rh/Nomina`, hacer clic en "Planeador"
15. Verificar que el modal abre con el mes actual y muestra los periodos correctamente
16. Verificar que las nóminas existentes aparecen con su color de estatus
17. Hacer clic en una nómina existente → debe abrir el modal de detalle
18. Hacer clic en un periodo vacío → debe abrir el modal "Nueva Nómina" con fechas pre-llenadas
19. Cambiar entre vistas Semanal/Quincenal/Mensual
20. Navegar entre meses con las flechas
21. Probar en móvil (≤575px): el modal debe ser fullscreen, el grid debe ser legible

### Paso 8: Verificación del Lugar de Origen
22. Crear una nómina de prueba y verificar que el `lugar_origen` se auto-detecta correctamente:
    - Empleados con departamento ≠ "Obras" → deben mostrar "Oficina"
    - Empleados del departamento "Obras" con `lugar_pago` vacío → deben mostrar "Obra"
    - Empleados del departamento "Obras" con `lugar_pago` definido → deben mostrar el nombre de la obra
23. Hacer doble clic en el `lugar_origen` del detalle y editarlo → debe guardarse el valor manual
24. Recalcular la nómina → verificar que los lugares de origen editados manualmente NO se sobrescriban (o evaluar si se deben sobrescribir según el caso de uso)

---

## Notas técnicas

- **No tocar** el seeder ni la DB — el planeador solo lee datos existentes.
- **`btn-close-white`** en el header del modal requiere que el fondo sea oscuro (el gradiente lo es ✅).
- **`data-bs-toggle="tooltip"`** NO usar — usar `title` nativo para tooltips simples (el hover ya muestra datos en el `title` de la card).
- **Reutilizar funciones existentes:** `verNomina(id)`, `mostrarModalNuevo()` ya están implementadas y funcionales.
- **Columnas responsive:** Bootstrap `col-md-*` asegura que en móvil las cards se apilen verticalmente.
- **El endpoint usa GET** (no POST) porque es una consulta de solo lectura con parámetros de filtro. Esto permite cache del navegador y URLs compartibles si se desea en el futuro.
- **Lugar de Origen auto-detectado**: Al crear nómina (manual o desde planeador), el valor se asigna automáticamente según departamento. Al recalcular, se **sobrescribe** con el valor auto-detectado actual del empleado. Si el usuario editó manualmente el `lugar_origen` y luego recalcula, perderá su edición manual. Si se desea preservar ediciones manuales, se puede agregar una bandera `lugar_origen_manual` en `nominas_detalle` en una iteración futura.
- **El helper `get_lugar_origen_empleado()` es público** para que pueda ser llamado desde el controlador si se necesita un endpoint AJAX de sugerencia en el futuro.

---

*ERP Chisa Recubrimientos — Planeador Mensual de Nóminas — Plan creado 30 jul 2026*
