<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MainDashboard extends MY_Controller {

  protected $modulo = 'dashboard_main';

  public function __construct()
  {
    parent::__construct();

    $this->viewData['success'] = true;
    $this->viewData['statusCode'] = get_status_code_by_result('emptyresult');
  }

  /**
   * Widget registry: the single source of truth for what the dashboard can show.
   * Each widget declares the permission required to see it (null = always
   * visible). Data for a widget is ONLY fetched/rendered when its permission is
   * granted, so restricted metrics never reach the HTML.
   */
  private function widget_registry()
  {
    return array(
      'welcome'            => array('perm' => null,                     'title' => 'Bienvenida',              'group' => 'General'),
      'ventas_mes'         => array('perm' => 'ventas_ordenes_consult', 'title' => 'Ventas del Mes',          'group' => 'Ventas'),
      'ventas_hoy'         => array('perm' => 'ventas_ordenes_consult', 'title' => 'Ventas de Hoy',           'group' => 'Ventas'),
      'produccion_ordenes' => array('perm' => 'produccion_ordenes',     'title' => 'Órdenes en Producción',   'group' => 'Producción'),
      'compras_resumen'    => array('perm' => 'compras_ordenes_consult','title' => 'Compras del Mes',         'group' => 'Compras'),
      'proveedores_resumen'=> array('perm' => 'proveedores_consult',    'title' => 'Proveedores Activos',     'group' => 'Proveedores'),
      'insumos_resumen'    => array('perm' => 'almacen_insumos',        'title' => 'Inventario de Insumos',   'group' => 'Almacén'),
      'empleados_resumen'  => array('perm' => 'rh_empleados_consult',   'title' => 'Empleados',               'group' => 'Recursos Humanos'),
      'stock_bajo'         => array('perm' => 'almacen_insumos',        'title' => 'Alerta de Stock Bajo',    'group' => 'Almacén'),
      // Chart widgets (permission-gated, same as the module pages they mirror).
      'ventas_chart'         => array('perm' => 'ventas_ordenes_consult', 'title' => 'Ventas Mensuales',            'group' => 'Ventas'),
      'clientes_chart'       => array('perm' => 'clientes_consult',       'title' => 'Nuevos Clientes',             'group' => 'Clientes'),
      'compras_chart'        => array('perm' => 'compras_ordenes_consult','title' => 'Compras por Mes',             'group' => 'Compras'),
      'proveedores_top_chart'=> array('perm' => 'proveedores_consult',    'title' => 'Top Proveedores',             'group' => 'Proveedores'),
      'proveedores_tipo_chart'=>array('perm' => 'proveedores_consult',    'title' => 'Proveedores por Tipo',        'group' => 'Proveedores'),
      'ultimas_ordenes'      => array('perm' => 'ventas_ordenes_consult', 'title' => 'Últimas Órdenes de Venta',    'group' => 'Ventas'),
    );
  }

  public function index()
  {
    $this->load->helper('permissions');
    $this->load->model('Dashboards/DashboardModel');

    $user_id = $this->session->userdata('id');
    // Single query -> full permission set for O(1) checks.
    $perms = $this->DashboardModel->get_user_permissions($user_id);
    $can = function ($perm) use ($perms) {
      return $perm === null || isset($perms[$perm]);
    };

    setViewSuccess('Dashboard cargado correctamente');
    $this->viewData['pageTitle']  = 'Dashboard';
    $this->viewData['headTitle']  = 'Dashboard';
    $this->viewData['breadcrumb'] = 'Inicio > Dashboard';

    $registry = $this->widget_registry();

    // Determine which widgets are authorized BEFORE touching any model.
    $authorized = array();
    foreach ($registry as $id => $meta) {
      if ($can($meta['perm'])) {
        $authorized[$id] = $meta;
      }
    }

    // --- Fetch data ONLY for authorized widgets (each model at most once) -----
    $data = array();

    if (isset($authorized['ventas_mes']) || isset($authorized['ventas_hoy'])) {
      $this->load->model('Ventas/VentasModel');
      $data['ventas_stats'] = $this->VentasModel->get_estadisticas();
    }
    if (isset($authorized['ultimas_ordenes'])) {
      if (!isset($this->VentasModel)) { $this->load->model('Ventas/VentasModel'); }
      $data['ultimas_ordenes'] = $this->VentasModel->get_ultimas_ordenes(5);
    }
    if (isset($authorized['produccion_ordenes'])) {
      $this->load->model('Produccion/ProduccionModel');
      $data['produccion_stats'] = $this->ProduccionModel->get_estadisticas();
    }
    if (isset($authorized['compras_resumen'])) {
      $this->load->model('Compras/OrdenesCompraModel');
      $data['compras_stats'] = $this->OrdenesCompraModel->get_estadisticas();
    }
    if (isset($authorized['proveedores_resumen'])) {
      $this->load->model('Compras/ProveedoresModel');
      $data['proveedores_stats'] = $this->ProveedoresModel->get_estadisticas();
    }
    if (isset($authorized['insumos_resumen']) || isset($authorized['stock_bajo'])) {
      $this->load->model('Compras/InsumosModel');
      if (isset($authorized['insumos_resumen'])) {
        $data['insumos_stats'] = $this->InsumosModel->get_estadisticas();
      }
      if (isset($authorized['stock_bajo'])) {
        $data['alertas_stock'] = $this->InsumosModel->get_insumos_stock_bajo();
      }
    }
    if (isset($authorized['empleados_resumen'])) {
      $this->load->model('RH/EmpleadoModel');
      $data['empleados_stats'] = $this->EmpleadoModel->get_estadisticas_rh();
    }
    if (isset($authorized['clientes_chart'])) {
      $this->load->model('Ventas/ClientesModel');
      $data['datos_grafica'] = $this->ClientesModel->get_nuevos_clientes_mensuales_anio();
    }
    if (isset($authorized['ventas_chart'])) {
      if (!isset($this->VentasModel)) { $this->load->model('Ventas/VentasModel'); }
      $data['ventas_mensuales'] = $this->VentasModel->get_ventas_mensuales_anio();
    }
    // Proveedores/compras chart widgets share one advanced-stats query.
    if (isset($authorized['compras_chart']) || isset($authorized['proveedores_top_chart']) || isset($authorized['proveedores_tipo_chart'])) {
      if (!isset($this->ProveedoresModel)) { $this->load->model('Compras/ProveedoresModel'); }
      $avanzadas = $this->ProveedoresModel->get_estadisticas_avanzadas();
      if (isset($authorized['compras_chart']))         { $data['compras_mes']       = $avanzadas['compras_mes']; }
      if (isset($authorized['proveedores_top_chart'])) { $data['top_proveedores']   = $avanzadas['top_proveedores']; }
      if (isset($authorized['proveedores_tipo_chart'])){ $data['distribucion_tipo'] = $avanzadas['distribucion_tipo']; }
    }

    // Attach the resolved data to each authorized widget so the view stays dumb
    // and can only ever render authorized widgets.
    $widgets = array();
    foreach ($authorized as $id => $meta) {
      $widgets[] = array(
        'id'    => $id,
        'title' => $meta['title'],
        'group' => $meta['group'],
        'perm'  => $meta['perm'],
      );
    }

    $data['widgets'] = $widgets;
    $data['user_id'] = (int) $user_id;

    $this->viewData['response']    = $data;
    $this->viewData['pageView']    = 'dashboards/mainDashboard';
    $this->viewData['pageScript']  = 'dashboards/mainDashboard_script';

    $this->load->view('layouts/general_template', $this->viewData);
  }

}
