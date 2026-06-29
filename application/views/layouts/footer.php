<footer class="footer" id="erp-main-footer">
  <div class="container-fluid">
    <div class="row text-muted">
      <div class="col-6 text-start">
        <ul class="list-inline">
          <li class="list-inline-item">
            <a class="text-muted" href="#">Support</a>
          </li>
          <li class="list-inline-item">
            <a class="text-muted" href="#">Help Center</a>
          </li>
          <li class="list-inline-item">
            <a class="text-muted" href="#">Privacy</a>
          </li>
          <li class="list-inline-item">
            <a class="text-muted" href="#">Terms of Service</a>
          </li>
        </ul>
      </div>
      <div class="col-6 text-end">
        <div class="d-inline-flex align-items-center justify-content-end gap-2 mb-2 erp-font-controls">
          <span class="small text-muted d-none d-sm-inline">Tamaño de letra</span>
          <div class="btn-group btn-group-sm" role="group" aria-label="Tamaño de letra del contenido">
            <button type="button" class="btn btn-outline-secondary px-2" id="erp-font-decrease" title="Disminuir letra" aria-label="Disminuir letra">A−</button>
            <button type="button" class="btn btn-outline-secondary px-2" id="erp-font-increase" title="Aumentar letra" aria-label="Aumentar letra">A+</button>
          </div>
        </div>
        <p class="mb-0">
          &copy; <?php echo date('Y');?> - <a href="https://chisarecubrimientos.com.mx" class="text-muted">Chisa Recubrimientos S.A. de C.V.</a>
        </p>
      </div>
    </div>
  </div>
</footer>
<script>
(function () {
  var STORAGE_KEY = 'erp_font_scale_level';
  var STEPS = { '-2': 0.85, '-1': 0.925, '0': 1, '1': 1.1, '2': 1.2 };

  function clamp(level) {
    var n = parseInt(level, 10);
    return isNaN(n) ? 0 : Math.max(-2, Math.min(2, n));
  }

  function currentLevel() {
    return clamp(document.documentElement.getAttribute('data-erp-font-level') || '0');
  }

  function getTargets() {
    return [
      document.getElementById('erp-main-content'),
      document.getElementById('erp-main-footer')
    ].filter(Boolean);
  }

  function apply(level) {
    level = clamp(level);
    var zoom = STEPS[String(level)] || 1;
    document.documentElement.style.setProperty('--erp-font-zoom', String(zoom));
    document.documentElement.setAttribute('data-erp-font-level', String(level));

    getTargets().forEach(function (el) {
      el.style.zoom = zoom;
    });

    try {
      localStorage.setItem(STORAGE_KEY, String(level));
    } catch (e) {}

    var dec = document.getElementById('erp-font-decrease');
    var inc = document.getElementById('erp-font-increase');
    if (dec) dec.disabled = level <= -2;
    if (inc) inc.disabled = level >= 2;
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('#erp-font-decrease')) {
      e.preventDefault();
      apply(currentLevel() - 1);
    }
    if (e.target.closest('#erp-font-increase')) {
      e.preventDefault();
      apply(currentLevel() + 1);
    }
  });

  var saved = 0;
  try {
    saved = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
  } catch (e) {}
  apply(saved);

  window.ErpFontScale = { apply: apply };
})();
</script>
