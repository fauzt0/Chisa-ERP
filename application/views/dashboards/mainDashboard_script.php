<?php // Only emit the chart script when the "Nuevos Clientes" widget is authorized. ?>
<?php if (isset($response['datos_grafica'])): ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var canvas = document.getElementById("chartjs-dashboard-bar");
    if (!canvas || typeof Chart === "undefined") return;
    var primary = (window.cssVariables && window.cssVariables.primary) || "#3f80ea";
    new Chart(canvas, {
      type: "bar",
      data: {
        labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        datasets: [{
          label: "Nuevos Clientes",
          backgroundColor: primary,
          borderColor: primary,
          hoverBackgroundColor: primary,
          hoverBorderColor: primary,
          data: <?= json_encode($response['datos_grafica']) ?>,
          barPercentage: .75,
          categoryPercentage: .5
        }]
      },
      options: {
        maintainAspectRatio: false,
        legend: { display: false },
        scales: {
          yAxes: [{ gridLines: { display: false }, ticks: { stepSize: 20 }, stacked: true }],
          xAxes: [{ gridLines: { color: "transparent" }, stacked: true }]
        }
      }
    });
  });
</script>
<?php endif; ?>

<script>
/* ---------------------------------------------------------------------------
 * Main dashboard — per-user customizable widget layout (show/hide + reorder)
 * ------------------------------------------------------------------------- */
(function () {
  "use strict";

  var USER_ID = <?= (int)($response['user_id'] ?? 0) ?>;
  var STORAGE_KEY = "erp_dashboard_layout_" + USER_ID;

  function readLayout() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      var parsed = JSON.parse(raw);
      return {
        order: Array.isArray(parsed.order) ? parsed.order : [],
        hidden: Array.isArray(parsed.hidden) ? parsed.hidden : []
      };
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

  // Apply a saved layout to the DOM. Only reorders/hides widgets that actually
  // exist (i.e. authorized ones) — a tampered layout cannot reveal a widget the
  // server never rendered.
  function applyLayout(layout) {
    var container = getContainer();
    if (!container || !layout) return;

    var byId = {};
    getWidgets().forEach(function (el) { byId[el.getAttribute("data-widget-id")] = el; });

    if (layout.order && layout.order.length) {
      layout.order.forEach(function (id) {
        if (byId[id]) container.appendChild(byId[id]);
      });
      getWidgets().forEach(function (el) {
        if (layout.order.indexOf(el.getAttribute("data-widget-id")) === -1) {
          container.appendChild(el);
        }
      });
    }

    var hidden = layout.hidden || [];
    getWidgets().forEach(function (el) {
      var id = el.getAttribute("data-widget-id");
      el.classList.toggle("widget-hidden", hidden.indexOf(id) !== -1);
    });
  }

  // Build the config modal list from the CURRENT dashboard DOM (authorized only).
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
    if (modal) {
      modal.addEventListener("show.bs.modal", buildConfigList);
    }

    var saveBtn = document.getElementById("dashboardConfigSave");
    if (saveBtn) {
      saveBtn.addEventListener("click", function () {
        var layout = collectLayoutFromConfig();
        writeLayout(layout);
        applyLayout(layout);
        var m = window.bootstrap && modal ? bootstrap.Modal.getInstance(modal) : null;
        if (m) m.hide();
      });
    }

    var resetBtn = document.getElementById("dashboardConfigReset");
    if (resetBtn) {
      resetBtn.addEventListener("click", function () {
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
        location.reload();
      });
    }
  });
})();
</script>
