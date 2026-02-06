<footer class="footer">
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
        <p class="mb-0">
          <?php if(ENVIRONMENT === 'development'): ?>
            <span class="badge bg-warning text-dark me-2">
              <i class="align-middle" data-feather="tool"></i> DESARROLLO (<?php echo ENVIRONMENT; ?>)
            </span>
          <?php elseif(ENVIRONMENT === 'testing'): ?>
            <span class="badge bg-info text-dark me-2">
              <i class="align-middle" data-feather="cpu"></i> PRUEBAS
            </span>
          <?php endif; ?>
          &copy; <?php echo date('Y');?> - <a href="https://chisarecubrimientos.com.mx" class="text-muted">Chisa Recubrimientos S.A. de C.V.</a>
        </p>
      </div>
    </div>
  </div>
</footer>
