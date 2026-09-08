<script>
/* ---------------------------------------------------------------------------
 * Main dashboard — permission-gated charts (data only emitted for authorized
 * widgets) + per-user customizable layout (show/hide + reorder).
 * The global `Chart` (v3, provided by app.js) is reused; no CDN needed.
 * ------------------------------------------------------------------------- */
(function () {
  "use strict";

  // Chart payloads are ONLY present when the controller authorized that widget.
  var CHART_DATA = {
    ventas_mensuales: <?= isset($response['ventas_mensuales']) ? json_encode($response['ventas_mensuales']) : 'null' ?>,
    clientes:         <?= isset($response['datos_grafica'])   ? json_encode($response['datos_grafica'])   : 'null' ?>,
    compras_mes:      <?= isset($response['compras_mes'])      ? json_encode($response['compras_mes'])      : 'null' ?>,
    top_proveedores:  <?= isset($response['top_proveedores'])  ? json_encode($response['top_proveedores'])  : 'null' ?>,
    distribucion:     <?= isset($response['distribucion_tipo'])? json_encode($response['distribucion_tipo']): 'null' ?>
  };

  var MESES = ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];
  var charts = [];

  function cssVar(name, fallback) {
    try {
      var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
      return v || fallback;
    } catch (e) { return fallback; }
  }
  function palette() {
    var cv = window.cssVariables || {};
    return {
      primary: cv.primary || cssVar('--bs-primary', '#3f80ea'),
      success: cv.success || cssVar('--bs-success', '#4bbf73'),
      warning: cv.warning || cssVar('--bs-warning', '#e5a54b'),
      danger:  cv.danger  || cssVar('--bs-danger',  '#d9534f'),
      info:    cv.info    || cssVar('--bs-info',    '#1f9bcf'),
      purple:  '#6f42c1'
    };
  }
  function isDarkTheme() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark';
  }
  // Deterministic (attribute-based) colors so chart text/grid stay readable in
  // both themes without depending on computed CSS variables.
  function themeColors() {
    return isDarkTheme()
      ? { text: '#e9ecef', grid: 'rgba(255,255,255,0.16)' }
      : { text: '#495057', grid: 'rgba(0,0,0,0.08)' };
  }
  function baseScales() {
    var t = themeColors();
    return {
      y: { beginAtZero: true, ticks: { color: t.text }, grid: { color: t.grid } },
      x: { ticks: { color: t.text }, grid: { display: false } }
    };
  }
  function noLegend() { return { legend: { display: false } }; }

  function makeChart(canvasId, config) {
    var canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === "undefined") return;
    try {
      var c = new Chart(canvas, config);
      charts.push(c);
    } catch (e) { /* ignore a single chart failure */ }
  }

  function initCharts() {
    var p = palette();

    // Ventas mensuales (line)
    if (CHART_DATA.ventas_mensuales) {
      makeChart("chart-ventas-mensuales", {
        type: "line",
        data: { labels: MESES, datasets: [{ label: "Ventas", data: CHART_DATA.ventas_mensuales,
          borderColor: p.success, backgroundColor: p.success + "33", fill: true, tension: .35, pointRadius: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: noLegend(), scales: baseScales() }
      });
    }

    // Nuevos clientes (bar)
    if (CHART_DATA.clientes) {
      makeChart("chartjs-dashboard-bar", {
        type: "bar",
        data: { labels: MESES, datasets: [{ label: "Nuevos Clientes", data: CHART_DATA.clientes,
          backgroundColor: p.primary, borderRadius: 4, maxBarThickness: 28 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: noLegend(), scales: baseScales() }
      });
    }

    // Compras por mes (bar) — labels are YYYY-MM
    if (CHART_DATA.compras_mes) {
      var cLabels = CHART_DATA.compras_mes.map(function (r) {
        var parts = String(r.mes).split("-");
        return parts.length === 2 ? (MESES[parseInt(parts[1], 10) - 1] + " " + parts[0].slice(2)) : r.mes;
      });
      var cData = CHART_DATA.compras_mes.map(function (r) { return parseFloat(r.total_mes) || 0; });
      makeChart("chart-compras-mes", {
        type: "bar",
        data: { labels: cLabels, datasets: [{ label: "Compras", data: cData,
          backgroundColor: p.warning, borderRadius: 4, maxBarThickness: 28 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: noLegend(), scales: baseScales() }
      });
    }

    // Top proveedores (horizontal bar)
    if (CHART_DATA.top_proveedores) {
      var tLabels = CHART_DATA.top_proveedores.map(function (r) {
        var n = r.nombre_comercial || r.razon_social || "—";
        return n.length > 22 ? n.slice(0, 21) + "…" : n;
      });
      var tData = CHART_DATA.top_proveedores.map(function (r) { return parseFloat(r.total_comprado) || 0; });
      makeChart("chart-top-proveedores", {
        type: "bar",
        data: { labels: tLabels, datasets: [{ label: "Comprado", data: tData,
          backgroundColor: p.primary, borderRadius: 4 }] },
        options: {
          indexAxis: "y", responsive: true, maintainAspectRatio: false,
          plugins: noLegend(),
          scales: (function () { var s = baseScales(); s.x.grid.display = true; return s; })()
        }
      });
    }

    // Distribución por tipo (doughnut)
    if (CHART_DATA.distribucion) {
      var dLabels = CHART_DATA.distribucion.map(function (r) { return r.tipo_proveedor || "Sin tipo"; });
      var dData = CHART_DATA.distribucion.map(function (r) { return parseInt(r.total, 10) || 0; });
      makeChart("chart-distribucion-prov", {
        type: "doughnut",
        data: { labels: dLabels, datasets: [{ data: dData,
          backgroundColor: [p.primary, p.warning, p.success, p.info, p.danger, p.purple] }] },
        options: { responsive: true, maintainAspectRatio: false,
          plugins: { legend: { position: "bottom", labels: { color: themeColors().text } } } }
      });
    }
  }

  // Fully rebuild charts on theme change so axis/legend colors are re-applied
  // from scratch (more reliable than mutating options + update()).
  function destroyCharts() {
    charts.forEach(function (c) { try { c.destroy(); } catch (e) {} });
    charts = [];
  }
  var rebuildTimer = null;
  function rebuildCharts() {
    // Debounced: the custom event and the MutationObserver may both fire.
    if (rebuildTimer) clearTimeout(rebuildTimer);
    rebuildTimer = setTimeout(function () { destroyCharts(); initCharts(); }, 60);
  }

  document.addEventListener("DOMContentLoaded", initCharts);
  document.addEventListener("erp:themechange", rebuildCharts);
  try {
    var themeObserver = new MutationObserver(function (muts) {
      for (var i = 0; i < muts.length; i++) {
        if (muts[i].attributeName === "data-bs-theme") { rebuildCharts(); break; }
      }
    });
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ["data-bs-theme"] });
  } catch (e) {}

  // ---- Per-user customizable layout (show/hide + reorder) ------------------
  var USER_ID = <?= (int)($response['user_id'] ?? 0) ?>;
  var STORAGE_KEY = "erp_dashboard_layout_" + USER_ID;

  function readLayout() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      var parsed = JSON.parse(raw);
      return { order: Array.isArray(parsed.order) ? parsed.order : [],
               hidden: Array.isArray(parsed.hidden) ? parsed.hidden : [] };
    } catch (e) { return null; }
  }
  function writeLayout(layout) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(layout)); } catch (e) {}
  }
  function getContainer() { return document.getElementById("dashboard-widgets"); }
  function getWidgets() {
    var c = getContainer();
    return c ? Array.prototype.slice.call(c.querySelectorAll(".dashboard-widget")) : [];
  }
  function refitCharts() { try { window.dispatchEvent(new Event("resize")); } catch (e) {} }

  function applyLayout(layout) {
    var container = getContainer();
    if (!container || !layout) return;
    var byId = {};
    getWidgets().forEach(function (el) { byId[el.getAttribute("data-widget-id")] = el; });
    if (layout.order && layout.order.length) {
      layout.order.forEach(function (id) { if (byId[id]) container.appendChild(byId[id]); });
      getWidgets().forEach(function (el) {
        if (layout.order.indexOf(el.getAttribute("data-widget-id")) === -1) container.appendChild(el);
      });
    }
    var hidden = layout.hidden || [];
    getWidgets().forEach(function (el) {
      el.classList.toggle("widget-hidden", hidden.indexOf(el.getAttribute("data-widget-id")) !== -1);
    });
    refitCharts();
  }

  function buildConfigList() {
    var list = document.getElementById("dashboardConfigList");
    if (!list) return;
    list.innerHTML = "";
    getWidgets().forEach(function (el) {
      var id = el.getAttribute("data-widget-id");
      var title = el.getAttribute("data-widget-title") || id;
      var group = el.getAttribute("data-widget-group") || "";
      var visible = !el.classList.contains("widget-hidden");
      var item = document.createElement("div");
      item.className = "dash-config-item";
      item.setAttribute("data-widget-id", id);
      item.innerHTML =
        '<span class="dash-drag-handle" title="Arrastra para reordenar"><i class="fas fa-grip-vertical"></i></span>' +
        '<span class="dash-config-title">' + title +
          (group ? ' <span class="dash-config-group">· ' + group + '</span>' : '') + '</span>' +
        '<div class="form-check form-switch">' +
          '<input class="form-check-input dash-config-toggle" type="checkbox" role="switch"' +
          (visible ? " checked" : "") + '>' +
        '</div>';
      list.appendChild(item);
    });
    if (window.dragula && !list._drake) {
      list._drake = window.dragula([list], {
        moves: function (el, container, handle) {
          return handle && handle.closest && handle.closest(".dash-drag-handle");
        }
      });
    }
  }

  function collectLayoutFromConfig() {
    var list = document.getElementById("dashboardConfigList");
    var order = [], hidden = [];
    Array.prototype.slice.call(list.querySelectorAll(".dash-config-item")).forEach(function (item) {
      var id = item.getAttribute("data-widget-id");
      order.push(id);
      var toggle = item.querySelector(".dash-config-toggle");
      if (toggle && !toggle.checked) hidden.push(id);
    });
    return { order: order, hidden: hidden };
  }

  document.addEventListener("DOMContentLoaded", function () {
    applyLayout(readLayout());
    var modal = document.getElementById("dashboardConfigModal");
    if (modal) modal.addEventListener("show.bs.modal", buildConfigList);

    var saveBtn = document.getElementById("dashboardConfigSave");
    if (saveBtn) saveBtn.addEventListener("click", function () {
      var layout = collectLayoutFromConfig();
      writeLayout(layout);
      applyLayout(layout);
      var m = window.bootstrap && modal ? bootstrap.Modal.getInstance(modal) : null;
      if (m) m.hide();
    });

    var resetBtn = document.getElementById("dashboardConfigReset");
    if (resetBtn) resetBtn.addEventListener("click", function () {
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
      location.reload();
    });
  });
})();
</script>
