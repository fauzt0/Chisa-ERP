<?php
/**
 * Vista Principal de Producción — Optimizada para Touchscreen
 * Secciones: Pedidos | Catálogo | Formulaciones | Histórico
 */
$base = base_url();
$ordenes      = $response['ordenes']          ?? [];
$filtros      = $response['filtros_activos']  ?? [];
?>
<!-- ═══════════════════════════════════════════════════════
     ESTILOS TOUCHSCREEN
══════════════════════════════════════════════════════════ -->
<style>
:root {
  --prod-blue:    #1565C0;
  --prod-green:   #2E7D32;
  --prod-orange:  #E65100;
  --prod-red:     #C62828;
  --prod-gray:    #37474F;
  --prod-light:   #ECEFF1;
  --touch-h:      58px;
  --touch-fs:     1.05rem;
  --card-radius:  14px;
}

/* ── Barra de navegación de secciones ── */
.prod-nav { display:flex; gap:0; border-bottom:3px solid var(--prod-blue); margin-bottom:1.25rem; }
.prod-nav-btn {
  flex:1; border:none; background:#fff; padding:0 .5rem;
  height:var(--touch-h); font-size:1rem; font-weight:700;
  color:#546E7A; cursor:pointer; transition:all .18s;
  border-bottom:4px solid transparent; border-radius:var(--card-radius) var(--card-radius) 0 0;
  display:flex; align-items:center; justify-content:center; gap:.45rem;
}
.prod-nav-btn.active {
  color:#fff; background:var(--prod-blue);
  border-bottom-color:var(--prod-blue);
}
.prod-nav-btn:not(.active):hover { background:var(--prod-light); color:var(--prod-blue); }

/* ── Panel genérico ── */
.prod-panel { display:none; }
.prod-panel.active { display:block; }

/* ── Card orden (pedidos) ── */
.orden-card {
  border-radius:var(--card-radius); border:none;
  box-shadow:0 2px 10px rgba(0,0,0,.1);
  cursor:pointer; transition:transform .15s,box-shadow .15s;
  overflow:hidden;
}
.orden-card:active { transform:scale(.98); }
.orden-card:hover { box-shadow:0 4px 18px rgba(0,0,0,.18); }
.orden-card .card-header { padding:.85rem 1.1rem .6rem; }
.orden-card .card-body   { padding:.6rem 1.1rem .9rem; font-size:.95rem; }
.orden-card .card-folio  { font-size:1.15rem; font-weight:800; color:#fff; }
.orden-card .card-body p { margin-bottom:.35rem; }

/* ── Badges de estado ── */
.badge-estado { font-size:.78rem; padding:.35rem .65rem; border-radius:20px; }

/* ── Búsqueda grande ── */
.search-bar-touch {
  height:var(--touch-h); font-size:1.15rem; border-radius:10px;
  border:2px solid #B0BEC5; padding:0 1.1rem;
  width:100%; box-sizing:border-box;
}
.search-bar-touch:focus { border-color:var(--prod-blue); outline:none; }

/* ── Botones de filtro de categoría ── */
.cat-btn {
  height:50px; border-radius:8px; font-size:.88rem; font-weight:600;
  border:2px solid #CFD8DC; background:#fff; color:#37474F;
  padding:0 1rem; cursor:pointer; transition:all .15s;
  white-space:nowrap;
}
.cat-btn.active, .cat-btn:hover { background:var(--prod-blue); color:#fff; border-color:var(--prod-blue); }

/* ── Card producto catálogo ── */
.prod-cat-card {
  border-radius:10px; border:1px solid #E0E0E0;
  transition:box-shadow .15s; cursor:pointer; overflow:hidden;
}
.prod-cat-card:hover { box-shadow:0 3px 14px rgba(0,0,0,.14); }
.prod-cat-card .pcc-head {
  background:linear-gradient(135deg,var(--prod-blue),#1976D2);
  color:#fff; padding:.55rem .9rem; font-weight:700; font-size:.9rem;
}
.prod-cat-card .pcc-body { padding:.55rem .9rem; font-size:.85rem; }
.pcc-formula-btn {
  width:100%; height:42px; border-radius:7px; font-size:.82rem;
  font-weight:700; border:none; cursor:pointer; transition:background .15s;
  background:#E3F2FD; color:var(--prod-blue);
}
.pcc-formula-btn:hover { background:var(--prod-blue); color:#fff; }

/* ── Card formulación ── */
.formula-card {
  border-radius:10px; border:1px solid #E0E0E0;
  padding:.75rem 1rem; cursor:pointer; transition:box-shadow .15s;
  background:#fff;
}
.formula-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.12); }
.formula-card .fc-ref  { font-weight:800; font-size:1rem; color:var(--prod-blue); }
.formula-card .fc-meta { font-size:.82rem; color:#607D8B; }
.formula-card.activa   { border-left:4px solid var(--prod-green); }

/* ── Árbol BOM ── */
.bom-node { padding:.4rem .6rem; border-radius:7px; margin-bottom:.35rem; font-size:.9rem; }
.bom-node.nivel-0 { background:#E3F2FD; border-left:4px solid var(--prod-blue); }
.bom-node.nivel-1 { background:#F3E5F5; border-left:4px solid #7B1FA2; margin-left:1.5rem; }
.bom-node.nivel-2 { background:#E8F5E9; border-left:4px solid #388E3C; margin-left:3rem; }
.bom-node.nivel-3 { background:#FFF8E1; border-left:4px solid #F57F17; margin-left:4.5rem; }
.bom-node.fabricado { font-weight:700; }
.bom-badge-fab  { font-size:.7rem; background:#7B1FA2; color:#fff; border-radius:4px; padding:1px 5px; }
.bom-badge-comp { font-size:.7rem; background:#1565C0; color:#fff; border-radius:4px; padding:1px 5px; }
.bom-badge-stock-ok  { font-size:.7rem; background:#2E7D32; color:#fff; border-radius:4px; padding:1px 5px; }
.bom-badge-stock-no  { font-size:.7rem; background:#C62828; color:#fff; border-radius:4px; padding:1px 5px; }

/* ── Tabla formulación Excel ── */
.tabla-formula { font-size:.88rem; }
.tabla-formula th { background:#1565C0; color:#fff; padding:.5rem .7rem; white-space:nowrap; }
.tabla-formula td { padding:.4rem .7rem; vertical-align:middle; }
.tabla-formula tr.grupo-header td { background:#E3F2FD; font-weight:700; color:var(--prod-blue); font-size:.85rem; }
.tabla-formula tr:hover { background:#F5F5F5; }
.tabla-formula .pct-col  { font-weight:700; color:#1565C0; }
.tabla-formula .kg-col   { font-weight:700; color:#2E7D32; }
.tabla-formula .fa-col   { color:#7B1FA2; font-size:.8rem; }

/* ── Historial ── */
.hist-row {
  padding:.7rem 1rem; border-radius:8px; border:1px solid #E0E0E0;
  margin-bottom:.5rem; display:flex; align-items:center; gap:.8rem;
  background:#fff; cursor:pointer; transition:box-shadow .15s;
}
.hist-row:hover { box-shadow:0 2px 10px rgba(0,0,0,.1); }
.hist-folio { font-weight:800; font-size:1rem; min-width:120px; }
.hist-cliente { font-size:.9rem; color:#37474F; flex:1; }
.hist-fecha   { font-size:.8rem; color:#90A4AE; min-width:90px; text-align:right; }

/* ── Header de sección ── */
.sec-header {
  display:flex; align-items:center; gap:.75rem;
  margin-bottom:1rem; padding-bottom:.5rem;
  border-bottom:2px solid var(--prod-light);
}
.sec-header h4 { margin:0; font-size:1.2rem; font-weight:800; color:var(--prod-gray); }

/* ── Botón acción grande ── */
.btn-touch {
  height:var(--touch-h); font-size:1rem; font-weight:700;
  border-radius:10px; display:inline-flex; align-items:center;
  gap:.55rem; padding:0 1.5rem; cursor:pointer; border:none;
  transition:opacity .15s;
}
.btn-touch:active { opacity:.85; }
.btn-touch-blue   { background:var(--prod-blue);   color:#fff; }
.btn-touch-green  { background:var(--prod-green);  color:#fff; }
.btn-touch-orange { background:var(--prod-orange); color:#fff; }
.btn-touch-gray   { background:var(--prod-gray);   color:#fff; }
.btn-touch-outline{ background:#fff; color:var(--prod-blue); border:2px solid var(--prod-blue)!important; }

/* ── Hora en header ── */
#prod-reloj { font-size:1.2rem; font-weight:700; color:var(--prod-blue); letter-spacing:.05em; }

/* ── Spinner / vacío ── */
.prod-empty { padding:2.5rem 1rem; text-align:center; color:#90A4AE; font-size:1rem; }
.prod-empty i { font-size:2.5rem; margin-bottom:.5rem; display:block; }

/* ── Responsive ── */
@media(max-width:768px){
  .prod-nav-btn { font-size:.78rem; padding:0 .25rem; }
  .prod-nav-btn span.d-none-sm { display:none; }
}
</style>

<!-- ═══════════════════════════════════════════════════════
     HEADER DE PRODUCCIÓN
══════════════════════════════════════════════════════════ -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-3">
    <div style="background:var(--prod-blue);border-radius:12px;padding:.55rem .85rem;color:#fff;">
      <i class="fas fa-industry fa-lg"></i>
    </div>
    <div>
      <h3 class="mb-0" style="font-size:1.5rem;font-weight:900;color:var(--prod-gray);">PRODUCCIÓN</h3>
      <small class="text-muted" style="font-size:.8rem;">Panel de Operadores</small>
    </div>
  </div>
  <div class="d-flex align-items-center gap-3">
    <span id="prod-reloj">--:--:--</span>
    <span class="badge bg-secondary" id="sync-badge" title="Sincronización en tiempo real">
      <i class="fas fa-circle text-success" style="font-size:.55rem;"></i> En vivo
    </span>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     NAVEGACIÓN DE SECCIONES
══════════════════════════════════════════════════════════ -->
<div class="prod-nav" id="prodNav">
  <button class="prod-nav-btn active" data-panel="pedidos" onclick="activarPanel('pedidos',this)">
    <i class="fas fa-clipboard-list"></i>
    <span>PEDIDOS</span>
    <span class="badge bg-warning text-dark ms-1" id="badge-pedidos"><?=count($ordenes)?></span>
  </button>
  <button class="prod-nav-btn" data-panel="catalogo" onclick="activarPanel('catalogo',this)">
    <i class="fas fa-boxes"></i>
    <span>CATÁLOGO</span>
  </button>
  <button class="prod-nav-btn" data-panel="formulaciones" onclick="activarPanel('formulaciones',this)">
    <i class="fas fa-flask"></i>
    <span>FORMULACIONES</span>
  </button>
  <button class="prod-nav-btn" data-panel="historico" onclick="activarPanel('historico',this)">
    <i class="fas fa-history"></i>
    <span>HISTÓRICO</span>
  </button>
</div>

<!-- ═══════════════════════════════════════════════════════
     PANEL 1: PEDIDOS ACTIVOS
══════════════════════════════════════════════════════════ -->
<div class="prod-panel active" id="panel-pedidos">

  <!-- Filtro rápido de búsqueda -->
  <div class="row mb-3 g-2 align-items-center">
    <div class="col-md-5 col-12">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        <input id="busq-pedidos" type="text" class="search-bar-touch border-start-0"
               placeholder="Buscar folio o cliente..."
               value="<?=htmlspecialchars($filtros['busqueda'] ?? '')?>"
               oninput="filtrarTarjetas(this.value)">
      </div>
    </div>
    <div class="col-auto">
      <div class="d-flex flex-wrap gap-2">
        <?php
        // Estatus reales: ordenes_venta → Confirmada, En Preparación, Entregada
        //                 obras         → Aprobada, En Ejecución, Completada
        $estatus_disponibles = [
            'Confirmada'    => 'warning',
            'En Preparación'=> 'info',
            'Aprobada'      => 'success',
            'En Ejecución'  => 'primary',
            'Entregada'     => 'secondary',
        ];
        $estatus_default = ['Confirmada','En Preparación','Aprobada','En Ejecución'];
        foreach($estatus_disponibles as $e=>$c): ?>
        <label class="cat-btn d-flex align-items-center gap-1 mb-0" style="height:42px;font-size:.82rem;">
          <input type="checkbox" class="estatus-chk" value="<?=$e?>"
            <?=in_array($e, $filtros['estatus'] ?? $estatus_default) ? 'checked' : ''?>
            onchange="recargarPedidos()" style="margin:0 3px 0 0;">
          <span class="badge bg-<?=$c?>" style="font-size:.75rem;"><?=$e?></span>
        </label>
        <?php endforeach; ?>
        <button class="btn-touch btn-touch-blue" style="height:42px;font-size:.82rem;" onclick="recargarPedidos()">
          <i class="fas fa-sync-alt"></i> Actualizar
        </button>
      </div>
    </div>
  </div>

  <!-- Grid de órdenes -->
  <div id="grid-pedidos" class="row g-3">
    <?php if(empty($ordenes)): ?>
    <div class="col-12">
      <div class="prod-empty">
        <i class="fas fa-check-circle text-success"></i>
        Sin órdenes pendientes en este momento.
      </div>
    </div>
    <?php else: foreach($ordenes as $o):
      $es_obra  = ($o->tipo_registro === 'obra');
      $hc = $es_obra ? '#E65100' : '#1565C0';
      $ic = $es_obra ? 'hard-hat' : 'shopping-cart';
      if(in_array($o->estatus, ['Confirmada','Aprobada'])) $bc = 'warning';
      elseif(in_array($o->estatus, ['En Proceso','En Ejecución'])) $bc = 'primary';
      elseif($o->estatus === 'Completada') $bc = 'success';
      else $bc = 'secondary';
    ?>
    <div class="col-12 col-md-6 col-xl-4 orden-card-wrap"
         data-folio="<?=strtolower($o->folio)?>"
         data-cliente="<?=strtolower($o->cliente??'')?>">
      <div class="card orden-card"
           onclick="abrirDetalleOrden('<?=$o->tipo_registro?>',<?=$o->id?>)">
        <div class="card-header" style="background:<?=$hc?>;">
          <div class="d-flex justify-content-between align-items-center">
            <span class="card-folio"><i class="fas fa-<?=$ic?> me-1"></i><?=$o->folio?></span>
            <span class="badge bg-light text-dark" style="font-size:.7rem;"><?=$es_obra?'OBRA':'VENTA'?></span>
          </div>
          <span class="badge badge-estado bg-<?=$bc?> mt-1"><?=$o->estatus?></span>
        </div>
        <div class="card-body">
          <p><i class="fas fa-building text-muted me-1"></i><strong><?=$o->cliente?:'Sin cliente'?></strong></p>
          <p class="text-muted"><i class="fas fa-calendar me-1"></i><?=date('d/m/Y',strtotime($o->fecha_creacion))?></p>
          <p class="text-muted"><i class="fas fa-box me-1"></i><?=$o->total_productos?> producto(s)</p>
          <div class="d-flex gap-2 mt-2">
            <span class="badge bg-secondary stock-badge"
                  id="stock_badge_<?=($o->tipo_registro=='obra'?'obra':'venta')?>_<?=$o->id?>"
                  title="Verificando...">
              <i class="fas fa-spinner fa-spin fa-xs"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>

</div><!-- /panel-pedidos -->

<!-- ═══════════════════════════════════════════════════════
     PANEL 2: CATÁLOGO DE PRODUCTOS
══════════════════════════════════════════════════════════ -->
<div class="prod-panel" id="panel-catalogo">

  <div class="sec-header">
    <i class="fas fa-boxes fa-lg text-primary"></i>
    <h4>Catálogo de Productos</h4>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-md-5 col-12">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        <input id="busq-catalogo" type="text" class="search-bar-touch border-start-0"
               placeholder="Buscar producto, código..."
               oninput="buscarCatalogo()">
      </div>
    </div>
    <div class="col-12">
      <div id="cat-filtros" class="d-flex flex-wrap gap-2 mt-1">
        <button class="cat-btn active" data-cat="" onclick="filtrarCat(this,'')">
          <i class="fas fa-th"></i> Todos
        </button>
        <!-- Categorías se cargan dinámicamente -->
      </div>
    </div>
  </div>

  <div id="grid-catalogo" class="row g-3">
    <div class="col-12 prod-empty">
      <i class="fas fa-spinner fa-spin"></i><br>Cargando catálogo...
    </div>
  </div>

</div><!-- /panel-catalogo -->

<!-- ═══════════════════════════════════════════════════════
     PANEL 3: FORMULACIONES
══════════════════════════════════════════════════════════ -->
<div class="prod-panel" id="panel-formulaciones">

  <div class="sec-header">
    <i class="fas fa-flask fa-lg text-primary"></i>
    <h4>Formulaciones</h4>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-md-6 col-12">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        <input id="busq-formulas" type="text" class="search-bar-touch border-start-0"
               placeholder="Buscar producto, referencia o versión..."
               oninput="buscarFormulaciones()">
      </div>
    </div>
    <div class="col-auto">
      <button class="btn-touch btn-touch-blue" onclick="buscarFormulaciones()">
        <i class="fas fa-search"></i> Buscar
      </button>
    </div>
  </div>

  <!-- Resultados de búsqueda -->
  <div id="lista-formulas" class="row g-2 mb-4">
    <div class="col-12 prod-empty">
      <i class="fas fa-flask"></i><br>
      Escribe para buscar una formulación.
    </div>
  </div>

  <!-- Vista de formulación activa seleccionada -->
  <div id="formula-detalle-area" style="display:none;">
    <div class="sec-header mt-2">
      <i class="fas fa-table fa-lg text-success"></i>
      <h4 id="formula-detalle-titulo">Formulación</h4>
      <div class="ms-auto d-flex gap-2">
        <input id="formula-kg-input" type="number" min="0.1" step="0.1" value="1"
               class="form-control" style="width:100px;height:42px;"
               onchange="recalcularTablaFormula()">
        <label class="d-flex align-items-center me-1 text-muted" style="font-size:.85rem;">kg</label>
        <button class="btn-touch btn-touch-outline" onclick="abrirArbolBOM()" style="height:42px;font-size:.82rem;">
          <i class="fas fa-project-diagram"></i> Ver árbol BOM
        </button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm tabla-formula" id="tabla-formula-vista">
        <thead>
          <tr>
            <th>Componente</th>
            <th class="text-center">%</th>
            <th class="text-center">Kg</th>
            <th>Grupo / Color</th>
            <th>F. Acuosa</th>
          </tr>
        </thead>
        <tbody id="tabla-formula-body"></tbody>
      </table>
    </div>
  </div>

</div><!-- /panel-formulaciones -->

<!-- ═══════════════════════════════════════════════════════
     PANEL 4: HISTÓRICO
══════════════════════════════════════════════════════════ -->
<div class="prod-panel" id="panel-historico">

  <div class="sec-header">
    <i class="fas fa-history fa-lg text-primary"></i>
    <h4>Historial de Producción</h4>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-md-5 col-12">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        <input id="busq-hist" type="text" class="search-bar-touch border-start-0"
               placeholder="Folio o cliente..."
               oninput="buscarHistorial(this.value)">
      </div>
    </div>
    <div class="col-auto">
      <button class="btn-touch btn-touch-gray" onclick="cargarHistorial('')" style="height:42px;font-size:.82rem;">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>
  </div>

  <div id="lista-historial">
    <div class="prod-empty"><i class="fas fa-spinner fa-spin"></i><br>Cargando...</div>
  </div>

</div><!-- /panel-historico -->


<!-- ═══════════════════════════════════════════════════════
     MODAL: ÁRBOL BOM
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalArbolBOM" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--prod-blue);color:#fff;">
        <h5 class="modal-title">
          <i class="fas fa-project-diagram me-2"></i>
          Árbol de Formulación (BOM)
          <small id="bom-titulo-producto" class="ms-2 opacity-75"></small>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Controles -->
        <div class="d-flex gap-3 align-items-center mb-3 flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <label class="fw-bold" style="font-size:.9rem;">Cantidad (kg):</label>
            <input id="bom-kg" type="number" min="0.1" step="1" value="1"
                   class="form-control" style="width:100px;"
                   onchange="recargarBOM()">
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="badge" style="background:#1565C0;font-size:.8rem;padding:.35rem .6rem;">
              <i class="fas fa-circle me-1"></i> Insumo comprado
            </span>
            <span class="badge" style="background:#7B1FA2;font-size:.8rem;padding:.35rem .6rem;">
              <i class="fas fa-industry me-1"></i> Sub-producto fabricado
            </span>
            <span class="badge" style="background:#2E7D32;font-size:.8rem;padding:.35rem .6rem;">
              <i class="fas fa-check me-1"></i> Stock suficiente
            </span>
            <span class="badge" style="background:#C62828;font-size:.8rem;padding:.35rem .6rem;">
              <i class="fas fa-times me-1"></i> Stock insuficiente
            </span>
          </div>
        </div>

        <!-- Tabs árbol / lista plana -->
        <ul class="nav nav-tabs mb-3" id="bomTabs">
          <li class="nav-item">
            <button class="nav-link active" onclick="mostrarBomTab('arbol',this)">
              <i class="fas fa-sitemap me-1"></i> Vista Árbol
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" onclick="mostrarBomTab('plano',this)">
              <i class="fas fa-list me-1"></i> Lista de Materias Primas
            </button>
          </li>
        </ul>

        <!-- Árbol -->
        <div id="bom-arbol-panel">
          <div id="bom-arbol-container">
            <div class="prod-empty"><i class="fas fa-spinner fa-spin"></i></div>
          </div>
        </div>

        <!-- Lista plana -->
        <div id="bom-plano-panel" style="display:none;">
          <table class="table table-sm tabla-formula" id="bom-tabla-plana">
            <thead>
              <tr>
                <th>Insumo (Materia Prima Real)</th>
                <th class="text-center">Kg requerido</th>
                <th class="text-center">Stock actual</th>
                <th class="text-center">Estado</th>
              </tr>
            </thead>
            <tbody id="bom-plano-body"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <div id="bom-resumen-texto" class="text-muted me-auto" style="font-size:.85rem;"></div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<script>
const BASE_URL = '<?=$base?>';
let formulaActual = null; // formulación actualmente mostrada
let bomFormulacionId = null;

// ── Reloj ──────────────────────────────────────────────
function actualizarReloj() {
  const ahora = new Date();
  document.getElementById('prod-reloj').textContent =
    ahora.toLocaleTimeString('es-MX', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
setInterval(actualizarReloj, 1000);
actualizarReloj();

// ── Navegación de paneles ──────────────────────────────
function activarPanel(nombre, btn) {
  document.querySelectorAll('.prod-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.prod-nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('panel-' + nombre).classList.add('active');
  btn.classList.add('active');

  // Carga diferida según panel
  if (nombre === 'catalogo'   && !catalogoCargado)    cargarCatalogo();
  if (nombre === 'formulaciones' && !formulasBuscadas) buscarFormulaciones();
  if (nombre === 'historico'  && !historialCargado)   cargarHistorial('');
}

// ── PANEL PEDIDOS ──────────────────────────────────────
function filtrarTarjetas(termino) {
  const t = termino.toLowerCase();
  document.querySelectorAll('.orden-card-wrap').forEach(el => {
    const folio   = el.dataset.folio   || '';
    const cliente = el.dataset.cliente || '';
    el.style.display = (folio.includes(t) || cliente.includes(t)) ? '' : 'none';
  });
}

function recargarPedidos() {
  const estatus = [...document.querySelectorAll('.estatus-chk:checked')].map(c => c.value);
  const busqueda = document.getElementById('busq-pedidos').value;
  const params = new URLSearchParams();
  estatus.forEach(e => params.append('estatus[]', e));
  if (busqueda) params.append('busqueda', busqueda);
  window.location.href = BASE_URL + 'produccion/Dashboard?' + params.toString();
}

function abrirDetalleOrden(tipo, id) {
  window.location.href = BASE_URL + 'produccion/Dashboard/detalle/' + tipo + '/' + id;
}

// Stock badges
document.addEventListener('DOMContentLoaded', () => {
  actualizarBadgesStock();
  // Auto-refresh cada 45 segundos
  setInterval(() => { actualizarBadgesStock(); }, 45000);
});

function actualizarBadgesStock() {
  const badges = document.querySelectorAll('.stock-badge');
  if (!badges.length) return;
  const ordenes = [];
  badges.forEach(b => {
    const parts = b.id.replace('stock_badge_', '').split('_');
    if (parts.length >= 2) ordenes.push({ id: parseInt(parts.slice(1).join('_')), tipo: parts[0] });
  });
  if (!ordenes.length) return;
  $.post(BASE_URL + 'produccion/Dashboard/get_stock_estado_ordenes_ajax',
    { ordenes: JSON.stringify(ordenes) }, res => {
    if (!res.success) return;
    Object.entries(res.estados).forEach(([key, estado]) => {
      const b = document.getElementById('stock_badge_' + key);
      if (!b) return;
      b.className = 'badge badge-estado stock-badge';
      if (estado === 'ok') {
        b.classList.add('bg-success');
        b.innerHTML = '<i class="fas fa-check-circle fa-xs"></i> Stock OK';
        b.title = 'Stock completo';
      } else if (estado === 'faltante') {
        b.classList.add('bg-danger');
        b.innerHTML = '<i class="fas fa-exclamation-triangle fa-xs"></i> Sin Insumos';
        b.title = 'Insumos faltantes';
      } else if (estado === 'sin_formulacion') {
        b.classList.add('bg-warning');
        b.innerHTML = '<i class="fas fa-flask fa-xs"></i> Sin Fórmula';
      } else {
        b.classList.add('bg-secondary');
        b.innerHTML = '—';
      }
    });
  }, 'json');
}

// ── PANEL CATÁLOGO ─────────────────────────────────────
let catalogoCargado = false;
let catActiva = '';
let busqCatTimeout = null;

function cargarCatalogo() {
  catalogoCargado = true;
  const termino = document.getElementById('busq-catalogo').value;
  const grid = document.getElementById('grid-catalogo');
  grid.innerHTML = '<div class="col-12 prod-empty"><i class="fas fa-spinner fa-spin"></i><br>Cargando...</div>';

  $.post(BASE_URL + 'produccion/Dashboard/get_catalogo_ajax',
    { categoria_id: catActiva, termino }, res => {
    if (!res.success) return;

    // Renderizar categorías (solo la primera vez)
    const filtrosDiv = document.getElementById('cat-filtros');
    if (filtrosDiv.children.length === 1) { // sólo "Todos"
      res.categorias.forEach(c => {
        const btn = document.createElement('button');
        btn.className = 'cat-btn';
        btn.dataset.cat = c.id;
        btn.onclick = () => filtrarCat(btn, c.id);
        btn.innerHTML = c.nombre;
        filtrosDiv.appendChild(btn);
      });
    }

    // Renderizar productos
    if (!res.productos.length) {
      grid.innerHTML = '<div class="col-12 prod-empty"><i class="fas fa-box-open"></i><br>Sin productos.</div>';
      return;
    }
    grid.innerHTML = '';
    res.productos.forEach(p => {
      const div = document.createElement('div');
      div.className = 'col-6 col-md-4 col-lg-3';
      const hasFormula = p.formulacion_id > 0;
      div.innerHTML = `
        <div class="prod-cat-card h-100">
          <div class="pcc-head">
            <div style="font-size:.75rem;opacity:.8;">${p.producto_codigo || ''}</div>
            <div>${p.nombre}</div>
          </div>
          <div class="pcc-body">
            <div class="text-muted mb-1" style="font-size:.78rem;">${p.categoria_nombre || 'Sin categoría'}</div>
            <div style="font-size:.82rem;">
              <i class="fas fa-flask text-primary me-1"></i>
              ${p.num_formulaciones} formulación(es)
            </div>
            ${hasFormula ? `
            <button class="pcc-formula-btn mt-2"
                    onclick="verFormulaCatalogo(${p.id}, ${p.formulacion_id}, '${escHtml(p.nombre)}')">
              <i class="fas fa-eye me-1"></i> Ver Formulación
            </button>` : `
            <div class="text-muted mt-2" style="font-size:.78rem;">
              <i class="fas fa-exclamation-triangle text-warning me-1"></i>Sin formulación activa
            </div>`}
          </div>
        </div>`;
      grid.appendChild(div);
    });
  }, 'json');
}

function filtrarCat(btn, catId) {
  document.querySelectorAll('#cat-filtros .cat-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  catActiva = catId;
  cargarCatalogo();
}

function buscarCatalogo() {
  clearTimeout(busqCatTimeout);
  busqCatTimeout = setTimeout(cargarCatalogo, 400);
}

function verFormulaCatalogo(productoId, formulacionId, nombre) {
  activarPanel('formulaciones', document.querySelector('[data-panel="formulaciones"]'));
  cargarFormulacionDetalle(formulacionId, nombre);
}

// ── PANEL FORMULACIONES ────────────────────────────────
let formulasBuscadas = false;
let busqFormulaTimeout = null;
let formulaData = null; // componentes de la formulación actualmente cargada

function buscarFormulaciones() {
  formulasBuscadas = true;
  const termino = document.getElementById('busq-formulas').value;
  const lista = document.getElementById('lista-formulas');
  lista.innerHTML = '<div class="col-12 prod-empty"><i class="fas fa-spinner fa-spin"></i></div>';

  $.post(BASE_URL + 'produccion/Dashboard/buscar_formulaciones_ajax',
    { termino }, res => {
    if (!res.success || !res.formulaciones.length) {
      lista.innerHTML = '<div class="col-12 prod-empty"><i class="fas fa-flask"></i><br>Sin resultados.</div>';
      return;
    }
    lista.innerHTML = '';
    const agrupado = {};
    res.formulaciones.forEach(f => {
      if (!agrupado[f.producto_id]) agrupado[f.producto_id] = { nombre: f.producto_nombre, items: [] };
      agrupado[f.producto_id].items.push(f);
    });
    Object.values(agrupado).forEach(grupo => {
      const wrap = document.createElement('div');
      wrap.className = 'col-12 col-md-6 col-lg-4';
      const activa = grupo.items.find(f => f.es_activa == 1);
      const versBadges = grupo.items.map(f =>
        `<span class="badge ${f.es_activa==1?'bg-success':'bg-secondary'} me-1" style="cursor:pointer;"
               onclick="cargarFormulacionDetalle(${f.id},'${escHtml(grupo.nombre)}')"
               title="${escHtml(f.nombre_version||'v'+f.version)}">
           v${f.version}${f.es_activa==1?' ✓':''}
         </span>`
      ).join('');
      wrap.innerHTML = `
        <div class="formula-card ${activa ? 'activa' : ''}"
             onclick="cargarFormulacionDetalle(${activa ? activa.id : grupo.items[0].id}, '${escHtml(grupo.nombre)}')">
          <div class="fc-ref">${escHtml(grupo.nombre)}</div>
          <div class="fc-meta">${activa ? activa.referencia_cliente || '' : ''}</div>
          <div class="mt-1">${versBadges}</div>
        </div>`;
      lista.appendChild(wrap);
    });
  }, 'json');
}

function cargarFormulacionDetalle(formulacionId, nombre) {
  bomFormulacionId = formulacionId;
  document.getElementById('formula-detalle-titulo').textContent = nombre || 'Formulación';
  document.getElementById('formula-detalle-area').style.display = '';
  document.getElementById('bom-titulo-producto').textContent = nombre || '';
  renderizarTablaFormula(formulacionId, parseFloat(document.getElementById('formula-kg-input').value) || 1);
}

function recalcularTablaFormula() {
  if (!bomFormulacionId) return;
  const kg = parseFloat(document.getElementById('formula-kg-input').value) || 1;
  renderizarTablaFormula(bomFormulacionId, kg);
}

function renderizarTablaFormula(formulacionId, kg) {
  const tbody = document.getElementById('tabla-formula-body');
  tbody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i></td></tr>';

  $.post(BASE_URL + 'produccion/Dashboard/get_formulacion_detalle_ajax',
    { formulacion_id: formulacionId }, res => {
    if (!res.success) { tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Error al cargar</td></tr>'; return; }
    formulaData = res.formulacion;
    const comps = res.formulacion.componentes || [];
    if (!comps.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin componentes</td></tr>'; return; }

    const cantidadBase = parseFloat(res.formulacion.cantidad_producida) || 1;
    const factor = kg / cantidadBase;

    // Agrupar
    let grupoActual = null;
    tbody.innerHTML = '';
    comps.forEach(c => {
      const grupo = c.grupo_color || '';
      if (grupo && grupo !== grupoActual) {
        grupoActual = grupo;
        const tr = document.createElement('tr');
        tr.className = 'grupo-header';
        tr.innerHTML = `<td colspan="5"><i class="fas fa-layer-group me-1"></i>${escHtml(grupo)}</td>`;
        tbody.appendChild(tr);
      }

      const nombre = c.insumo_nombre || c.producto_nombre || '—';
      const pct    = parseFloat(c.porcentaje) || 0;
      const kgComp = parseFloat(c.cantidad) * factor;
      const kgFa   = c.porcentaje_fase_acuosa ? (pct / 100 * kg * parseFloat(c.porcentaje_fase_acuosa) / 100) : null;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escHtml(nombre)}</td>
        <td class="text-center pct-col">${pct.toFixed(2)}%</td>
        <td class="text-center kg-col">${kgComp.toFixed(4)}</td>
        <td class="text-muted" style="font-size:.8rem;">${escHtml(grupo)}</td>
        <td class="fa-col text-center">${kgFa ? kgFa.toFixed(4) : '—'}</td>`;
      tbody.appendChild(tr);
    });
  }, 'json');
}

// ── ÁRBOL BOM ──────────────────────────────────────────
function abrirArbolBOM() {
  if (!bomFormulacionId) return;
  document.getElementById('bom-kg').value = document.getElementById('formula-kg-input').value || 1;
  const modal = new bootstrap.Modal(document.getElementById('modalArbolBOM'));
  modal.show();
  cargarBOM();
}

function recargarBOM() { cargarBOM(); }

function cargarBOM() {
  const kg = parseFloat(document.getElementById('bom-kg').value) || 1;
  document.getElementById('bom-arbol-container').innerHTML =
    '<div class="prod-empty"><i class="fas fa-spinner fa-spin"></i></div>';
  document.getElementById('bom-plano-body').innerHTML = '';

  $.post(BASE_URL + 'produccion/Dashboard/explotar_bom_ajax',
    { formulacion_id: bomFormulacionId, cantidad_kg: kg }, res => {
    if (!res.success) {
      document.getElementById('bom-arbol-container').innerHTML =
        '<div class="prod-empty text-danger"><i class="fas fa-times-circle"></i><br>Error al cargar BOM.</div>';
      return;
    }
    renderizarArbolBOM(res.arbol, 'bom-arbol-container');
    renderizarPlanosBOM(res.plano, 'bom-plano-body');

    const totMatPrima = res.plano.reduce((s,n) => s + n.kg, 0);
    document.getElementById('bom-resumen-texto').textContent =
      `Total ${res.plano.length} materias primas | ${totMatPrima.toFixed(2)} kg`;
  }, 'json');
}

function renderizarArbolBOM(nodos, containerId) {
  const container = document.getElementById(containerId);
  container.innerHTML = '';
  if (!nodos.length) {
    container.innerHTML = '<div class="prod-empty">Sin componentes.</div>';
    return;
  }
  nodos.forEach(n => container.appendChild(crearNodoBOM(n)));
}

function crearNodoBOM(nodo) {
  const div = document.createElement('div');
  div.className = `bom-node nivel-${Math.min(nodo.nivel, 3)} ${nodo.es_fabricado ? 'fabricado' : ''}`;

  const stockOk = nodo.stock >= nodo.kg;
  const stockBadge = `<span class="${stockOk ? 'bom-badge-stock-ok' : 'bom-badge-stock-no'}">
    ${stockOk ? '✓ Stock OK' : '✗ Insuf.'} (${parseFloat(nodo.stock).toFixed(2)})
  </span>`;

  const tipoBadge = nodo.es_fabricado
    ? '<span class="bom-badge-fab"><i class="fas fa-industry me-1"></i>Fabricado</span>'
    : '<span class="bom-badge-comp"><i class="fas fa-shopping-cart me-1"></i>Comprado</span>';

  div.innerHTML = `
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
      <div>
        ${nodo.nivel > 0 ? '<i class="fas fa-level-up-alt fa-rotate-90 text-muted me-1" style="font-size:.7rem;"></i>' : ''}
        <strong>${escHtml(nodo.nombre)}</strong>
        <span class="text-muted ms-1" style="font-size:.78rem;">(${escHtml(nodo.codigo||'')})</span>
      </div>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        ${tipoBadge}
        ${!nodo.es_fabricado ? stockBadge : ''}
        <span style="font-size:.85rem;font-weight:700;color:var(--prod-green);">${parseFloat(nodo.kg).toFixed(4)} kg</span>
        <span style="font-size:.78rem;color:#607D8B;">${parseFloat(nodo.porcentaje).toFixed(2)}%</span>
      </div>
    </div>`;

  // Hijos
  if (nodo.sub_componentes && nodo.sub_componentes.length) {
    const hijos = document.createElement('div');
    hijos.style.marginTop = '.35rem';
    nodo.sub_componentes.forEach(h => hijos.appendChild(crearNodoBOM(h)));
    div.appendChild(hijos);
  }
  return div;
}

function renderizarPlanosBOM(plano, tbodyId) {
  const tbody = document.getElementById(tbodyId);
  tbody.innerHTML = '';
  if (!plano.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>';
    return;
  }
  plano.forEach(n => {
    const stockOk = n.stock >= n.kg;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${escHtml(n.nombre)}</td>
      <td class="text-center kg-col">${parseFloat(n.kg).toFixed(4)}</td>
      <td class="text-center">${parseFloat(n.stock).toFixed(2)}</td>
      <td class="text-center">
        <span class="badge ${stockOk ? 'bg-success' : 'bg-danger'}">${stockOk ? 'OK' : 'Faltante'}</span>
      </td>`;
    tbody.appendChild(tr);
  });
}

function mostrarBomTab(tab, btn) {
  document.querySelectorAll('#bomTabs .nav-link').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('bom-arbol-panel').style.display = (tab === 'arbol') ? '' : 'none';
  document.getElementById('bom-plano-panel').style.display = (tab === 'plano') ? '' : 'none';
}

// ── PANEL HISTÓRICO ────────────────────────────────────
let historialCargado = false;
let histTimeout = null;

function cargarHistorial(busqueda) {
  historialCargado = true;
  const lista = document.getElementById('lista-historial');
  lista.innerHTML = '<div class="prod-empty"><i class="fas fa-spinner fa-spin"></i><br>Cargando...</div>';

  $.post(BASE_URL + 'produccion/Dashboard/get_historial_ajax',
    { busqueda }, res => {
    if (!res.success || !res.historial.length) {
      lista.innerHTML = '<div class="prod-empty"><i class="fas fa-inbox"></i><br>Sin registros.</div>';
      return;
    }
    lista.innerHTML = '';
    res.historial.forEach(h => {
      const div = document.createElement('div');
      const esObra = h.tipo_registro === 'obra';
      const color = esObra ? 'var(--prod-orange)' : 'var(--prod-blue)';
      const fecha = h.fecha_completado_produccion
        ? new Date(h.fecha_completado_produccion).toLocaleDateString('es-MX')
        : new Date(h.fecha_creacion).toLocaleDateString('es-MX');
      div.innerHTML = `
        <div class="hist-row" onclick="abrirDetalleOrden('${h.tipo_registro}', ${h.id})">
          <div style="width:8px;height:40px;border-radius:4px;background:${color};flex-shrink:0;"></div>
          <div class="hist-folio">${escHtml(h.folio)}</div>
          <div class="hist-cliente">${escHtml(h.cliente || '—')}</div>
          <div class="text-muted" style="font-size:.8rem;">${h.total_productos} prod.</div>
          <div class="hist-fecha">${fecha}</div>
          <span class="badge bg-success" style="font-size:.72rem;">${h.estatus}</span>
        </div>`;
      lista.appendChild(div);
    });
  }, 'json');
}

function buscarHistorial(val) {
  clearTimeout(histTimeout);
  histTimeout = setTimeout(() => cargarHistorial(val), 400);
}

// ── Utilidades ─────────────────────────────────────────
function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<!-- Toast de nuevas órdenes -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
  <div id="toastNuevasOrdenes" class="toast" role="alert" aria-atomic="true">
    <div class="toast-header bg-success text-white">
      <i class="fas fa-bell me-2"></i>
      <strong class="me-auto">¡Nuevas Órdenes!</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">
      <p id="toastMensaje" class="mb-2"></p>
      <button class="btn btn-sm btn-primary w-100" onclick="location.reload()">
        <i class="fas fa-sync"></i> Actualizar
      </button>
    </div>
  </div>
</div>
