/**
 * ERP — DataTables Responsive (flecha para columnas que no caben).
 * Default global: responsive + autoWidth false, igual que rh/RecursosHumanos.
 */
(function (window) {
  'use strict';

  var LANG = { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json' };
  var SKIP = '.cal-month-table, table.conceptos, .rh-skip-responsive';

  function applyDtDefaults() {
    var $ = window.jQuery;
    if (!$ || !$.fn || !$.fn.dataTable || !$.fn.dataTable.defaults) return false;
    $.fn.dataTable.defaults.responsive = true;
    $.fn.dataTable.defaults.autoWidth = false;
    return true;
  }
  applyDtDefaults();

  function $jq() {
    return window.jQuery;
  }

  function isEmptyPlaceholder($t) {
    var $rows = $t.find('tbody tr');
    return $rows.length === 1 && $rows.find('td[colspan]').length > 0;
  }

  function shouldSkip($t, extra) {
    extra = extra || {};
    if (!$t || !$t.length) return true;
    if ($t.is(SKIP) || $t.closest(SKIP).length) return true;
    if (!extra.allowComplexHeader && $t.find('thead tr').length > 1) return true;
    return false;
  }

  /**
   * (Re)inicializa una tabla con Responsive + scrollX, como empleados.
   * @param {string|Element|jQuery} selector
   * @param {object} extra Opciones DataTables extra
   */
  window.rhDestroyResponsiveTable = function (selector) {
    var $ = $jq();
    if (!$ || !$.fn.DataTable) return;
    var $t = $(selector);
    if ($t.length && $.fn.DataTable.isDataTable($t)) {
      try { $t.DataTable().destroy(); } catch (e) { /* ignore */ }
    }
  };

  window.rhRefreshResponsiveTable = function (selector, extra) {
    var $ = $jq();
    extra = extra || {};
    if (!$ || !$.fn || !$.fn.DataTable) return null;

    var $t = $(selector);
    if (shouldSkip($t, extra)) return null;

    if ($.fn.DataTable.isDataTable($t)) {
      try { $t.DataTable().destroy(); } catch (e) { /* ignore */ }
    }

    if (isEmptyPlaceholder($t) && !extra.forceEmpty) return null;

    var compact = extra.paging === false;
    var opts = $.extend(true, {
      responsive: true,
      scrollX: extra.scrollX !== false,
      autoWidth: false,
      paging: extra.paging !== undefined ? extra.paging : false,
      lengthChange: extra.lengthChange !== undefined ? extra.lengthChange : false,
      searching: extra.searching !== undefined ? extra.searching : false,
      info: extra.info !== undefined ? extra.info : false,
      ordering: extra.ordering !== undefined ? extra.ordering : true,
      language: extra.language || LANG,
      pageLength: extra.pageLength || 10,
      dom: extra.dom || (compact ? 't' : undefined)
    }, extra);

    delete opts.forceEmpty;
    delete opts.allowComplexHeader;
    if (opts.dom === undefined) delete opts.dom;

    try {
      return $t.DataTable(opts);
    } catch (e) {
      if (window.console && console.warn) {
        console.warn('rhRefreshResponsiveTable', selector, e);
      }
      return null;
    }
  };

  window.rhRecalcResponsiveTables = function (scope) {
    var $ = $jq();
    if (!$ || !$.fn.DataTable) return;
    $(scope || document).find('table.dataTable').each(function () {
      if (!$.fn.DataTable.isDataTable(this)) return;
      try {
        var api = $(this).DataTable();
        api.columns.adjust();
        if (api.responsive && typeof api.responsive.recalc === 'function') {
          api.responsive.recalc();
        }
      } catch (e) { /* ignore */ }
    });
  };

  function boot() {
    var $ = $jq();
    if (!$) return;

    $(document).on('shown.bs.modal shown.bs.tab shown.bs.collapse', function (ev) {
      window.rhRecalcResponsiveTables(ev.target);
    });

    var resizeTimer;
    $(window).on('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        window.rhRecalcResponsiveTables();
      }, 150);
    });

    setTimeout(function () {
      applyDtDefaults();
      $('table.rh-dt-responsive').each(function () {
        if ($.fn.DataTable.isDataTable(this)) return;
        window.rhRefreshResponsiveTable(this, {
          paging: false,
          searching: false,
          info: false
        });
      });
      window.rhRecalcResponsiveTables();
    }, 80);
  }

  if (window.jQuery) {
    window.jQuery(boot);
  } else {
    document.addEventListener('DOMContentLoaded', boot);
  }
})(window);
