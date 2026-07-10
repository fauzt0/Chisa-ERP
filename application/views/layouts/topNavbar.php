<nav class="navbar navbar-expand navbar-bg">
  <a class="sidebar-toggle">
    <i class="hamburger align-self-center"></i>
  </a>

  <!--
  <form class="d-none d-sm-inline-block">
    <div class="input-group input-group-navbar">
      <input type="text" class="form-control" placeholder="Buscar" aria-label="Search">
      <button class="btn" type="button">
        <i class="align-middle" data-lucide="search"></i>
      </button>
    </div>
  </form>-->

  <!--
  <ul class="navbar-nav">
    <li class="nav-item px-2 dropdown d-none d-sm-inline-block">
      <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Mega menu
      </a>
      <div class="dropdown-menu dropdown-menu-start dropdown-mega" aria-labelledby="servicesDropdown">
        <div class="d-md-flex align-items-start justify-content-start">
          <div class="dropdown-mega-list">
            <div class="dropdown-header">UI Elements</div>
            <a class="dropdown-item" href="#">Alerts</a>
            <a class="dropdown-item" href="#">Buttons</a>
            <a class="dropdown-item" href="#">Cards</a>
            <a class="dropdown-item" href="#">Carousel</a>
            <a class="dropdown-item" href="#">General</a>
            <a class="dropdown-item" href="#">Grid</a>
            <a class="dropdown-item" href="#">Modals</a>
            <a class="dropdown-item" href="#">Tabs</a>
            <a class="dropdown-item" href="#">Typography</a>
          </div>
          <div class="dropdown-mega-list">
            <div class="dropdown-header">Forms</div>
            <a class="dropdown-item" href="#">Layouts</a>
            <a class="dropdown-item" href="#">Basic Inputs</a>
            <a class="dropdown-item" href="#">Input Groups</a>
            <a class="dropdown-item" href="#">Advanced Inputs</a>
            <a class="dropdown-item" href="#">Editors</a>
            <a class="dropdown-item" href="#">Validation</a>
            <a class="dropdown-item" href="#">Wizard</a>
          </div>
          <div class="dropdown-mega-list">
            <div class="dropdown-header">Tables</div>
            <a class="dropdown-item" href="#">Basic Tables</a>
            <a class="dropdown-item" href="#">Responsive Table</a>
            <a class="dropdown-item" href="#">Table with Buttons</a>
            <a class="dropdown-item" href="#">Column Search</a>
            <a class="dropdown-item" href="#">Muulti Selection</a>
            <a class="dropdown-item" href="#">Ajax Sourced Data</a>
          </div>
        </div>
      </div>
    </li>
  </ul>-->

  <div class="navbar-collapse collapse">
    <ul class="navbar-nav navbar-align">

      <li class="nav-item dropdown" id="comunicacion-nav-item" style="display: none;">
        <a class="nav-icon dropdown-toggle" href="#" id="comunicacionDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <div class="position-relative">
            <i class="align-middle fas fa-comments text-body"></i>
            <span class="indicator" id="comunicacion-badge" style="display: none;"></span>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="comunicacionDropdown">
          <div class="dropdown-menu-header" id="comunicacion-header">
            <span class="spinner-border spinner-border-sm me-2"></span> Cargando mensajes...
          </div>
          <div class="list-group" id="comunicacion-list"></div>
          <div class="dropdown-menu-footer">
            <a href="<?= base_url('rh/Comunicacion') ?>" class="text-muted">Abrir bandeja completa</a>
          </div>
        </div>
      </li>
      
      <li class="nav-item dropdown">
        <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
          <div class="position-relative">
            <i class="align-middle text-body" data-lucide="bell"></i>
            <span class="indicator" id="notifications-badge" style="display: none;"></span>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
          <div class="dropdown-menu-header" id="notifications-header">
            <span class="spinner-border spinner-border-sm me-2"></span> Cargando notificaciones...
          </div>
          <div class="list-group" id="notifications-list">
            <!-- Las notificaciones se cargarán dinámicamente aquí -->
          </div>
          <div class="dropdown-menu-footer">
            <a href="#" class="text-muted" onclick="refreshNotifications(); return false;">Actualizar notificaciones</a>
          </div>
        </div>
      </li>
      
      <li class="nav-item nav-theme-toggle dropdown">
        <a class="nav-icon js-theme-toggle" href="#">
          <div class="position-relative">
            <i class="align-middle text-body nav-theme-toggle-light" data-lucide="sun"></i>
            <i class="align-middle text-body nav-theme-toggle-dark" data-lucide="moon"></i>
          </div>
        </a>
      </li>
      <!--
      <li class="nav-item dropdown">
        <a class="nav-flag dropdown-toggle" href="#" id="languageDropdown" data-bs-toggle="dropdown">
          <img src="<?php echo base_url();?>assets/dist/img/flags/us.png" alt="English" />
        </a>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
          <a class="dropdown-item" href="#">
            <img src="<?php echo base_url();?>assets/dist/img/flags/us.png" alt="English" width="20" class="align-middle me-1" />
            <span class="align-middle">English</span>
          </a>
          <a class="dropdown-item" href="#">
            <img src="<?php echo base_url();?>assets/dist/img/flags/es.png" alt="Spanish" width="20" class="align-middle me-1" />
            <span class="align-middle">Spanish</span>
          </a>
          <a class="dropdown-item" href="#">
            <img src="<?php echo base_url();?>assets/dist/img/flags/de.png" alt="German" width="20" class="align-middle me-1" />
            <span class="align-middle">German</span>
          </a>
          <a class="dropdown-item" href="#">
            <img src="<?php echo base_url();?>assets/dist/img/flags/nl.png" alt="Dutch" width="20" class="align-middle me-1" />
            <span class="align-middle">Dutch</span>
          </a>
        </div>
      </li>-->
      <li class="nav-item dropdown">
        <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
          <i class="align-middle" data-lucide="settings"></i>
        </a>

        <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
          <i class="align-middle fas fa-user-circle me-1" style="font-size: 2rem;"></i> 
          <span><?= $this->session->userdata('name') ? $this->session->userdata('name') : 'Usuario' ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">                    
          <a class="dropdown-item" href="<?php echo base_url();?>Auth/logout">Cerrar Sesión</a>
        </div>
      </li>
    </ul>
  </div>
</nav>