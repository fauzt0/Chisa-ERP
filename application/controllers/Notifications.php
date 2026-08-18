<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Users/UserModel');
    $this->load->model('Almacen/AlmacenModel');
  }

  /**
   * Obtiene todas las notificaciones del sistema
   * Retorna JSON con las notificaciones agrupadas por tipo
   */
  public function get_notifications() {
    $notifications = [];
    $total_count = 0;

    // 0. ALERTAS SIMULADAS (solo usuarios con permiso de demo)
    $this->load->helper('permissions');
    if (tiene_permiso('admin_simular_alertas')) {
      $this->load->model('Users/AlertasSimuladasModel');
      $simuladas = $this->AlertasSimuladasModel->get_para_notificaciones();
      if (!empty($simuladas)) {
        foreach ($simuladas as $sim) {
          $notifications[] = $sim;
          $total_count++;
        }
      }
    }

    // 1. ALMACÉN - Stock bajo
    $stock_bajo = $this->_get_stock_bajo();
    if(!empty($stock_bajo)) {
      foreach($stock_bajo as $item) {
        $notifications[] = [
          'type' => 'warning',
          'icon' => 'exclamation-triangle',
          'module' => 'Almacén',
          'title' => 'Stock bajo',
          'message' => $item->nombre . ' tiene solo ' . $item->stock_actual . ' ' . $item->unidad,
          'link' => base_url('almacen/Inventario'),
          'time' => 'Ahora'
        ];
        $total_count++;
      }
    }

    // 2. VENTAS - Órdenes pendientes de entrega
    $ordenes_pendientes = $this->_get_ordenes_pendientes();
    if(!empty($ordenes_pendientes)) {
      foreach($ordenes_pendientes as $orden) {
        $dias_retraso = $this->_calcular_dias_retraso($orden->fecha_entrega_estimada);
        if($dias_retraso > 0) {
          $notifications[] = [
            'type' => 'danger',
            'icon' => 'exclamation-circle',
            'module' => 'Ventas',
            'title' => 'Orden retrasada',
            'message' => 'Orden ' . $orden->folio . ' con ' . $dias_retraso . ' días de retraso',
            'link' => base_url('ventas/Ordenes'),
            'time' => $dias_retraso . 'd'
          ];
          $total_count++;
        }
      }
    }

    // 3. OBRAS - Obras retrasadas
    $obras_retrasadas = $this->_get_obras_retrasadas();
    if(!empty($obras_retrasadas)) {
      foreach($obras_retrasadas as $obra) {
        $notifications[] = [
          'type' => 'warning',
          'icon' => 'clock',
          'module' => 'Obras',
          'title' => 'Obra retrasada',
          'message' => $obra->nombre . ' está retrasada',
          'link' => base_url('obras/Obras/detalle/' . $obra->id),
          'time' => 'Hoy'
        ];
        $total_count++;
      }
    }

    // 4. RECURSOS HUMANOS - Datos faltantes
    $empleados_datos_faltantes = $this->_get_empleados_datos_faltantes();
    if(!empty($empleados_datos_faltantes)) {
      foreach($empleados_datos_faltantes as $empleado) {
        $campos_faltantes = [];
        if(empty($empleado->nss)) $campos_faltantes[] = 'NSS';
        if(empty($empleado->rfc)) $campos_faltantes[] = 'RFC';
        if(empty($empleado->curp)) $campos_faltantes[] = 'CURP';
        
        if(!empty($campos_faltantes)) {
          $nombre_completo = trim($empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . ($empleado->apellido_materno ?? ''));
          $notifications[] = [
            'type' => 'info',
            'icon' => 'user-circle',
            'module' => 'RH',
            'title' => 'Datos incompletos',
            'message' => $nombre_completo . ' - Falta: ' . implode(', ', $campos_faltantes),
            'link' => base_url('rh/RecursosHumanos'),
            'time' => 'Hoy'
          ];
          $total_count++;
        }
      }
    }

    // 5. COMPRAS - Órdenes de compra pendientes
    $ordenes_compra_pendientes = $this->_get_ordenes_compra_pendientes();
    if(!empty($ordenes_compra_pendientes)) {
      $notifications[] = [
        'type' => 'info',
        'icon' => 'shopping-cart',
        'module' => 'Compras',
        'title' => 'Órdenes de compra pendientes',
        'message' => count($ordenes_compra_pendientes) . ' órdenes pendientes de recibir',
        'link' => base_url('compras/OrdenesCompra'),
        'time' => 'Hoy'
      ];
      $total_count++;
    }

    // 6. PRODUCCIÓN - Productos con formulación pendiente
    $productos_sin_formulacion = $this->_get_productos_sin_formulacion();
    if(!empty($productos_sin_formulacion)) {
      $notifications[] = [
        'type' => 'warning',
        'icon' => 'box',
        'module' => 'Producción',
        'title' => 'Productos sin formulación',
        'message' => count($productos_sin_formulacion) . ' productos necesitan formulación',
        'link' => base_url('produccion/Productos'),
        'time' => 'Hoy'
      ];
      $total_count++;
    }

    // 7. COMPRAS - Pre-órdenes automáticas pendientes de autorización
    $preordenes_pendientes = $this->_get_preordenes_pendientes();
    if($preordenes_pendientes > 0) {
      $notifications[] = [
        'type' => 'danger',
        'icon' => 'clipboard-check',
        'module' => 'Compras',
        'title' => 'Pre-órdenes pendientes de autorización',
        'message' => $preordenes_pendientes . ' pre-orden(es) generada(s) desde Producción esperan autorización',
        'link' => base_url('compras/OrdenesCompra'),
        'time' => 'Ahora'
      ];
      $total_count++;
    }

    // 8. PRODUCCIÓN - Solicitudes pendientes (incluye órdenes desde obras)
    $solicitudes_pendientes = $this->_get_solicitudes_produccion_pendientes();
    if(!empty($solicitudes_pendientes)) {
      $count = count($solicitudes_pendientes);
      $ultima = $solicitudes_pendientes[0];
      $notifications[] = [
        'type' => 'danger',
        'icon' => 'industry',
        'module' => 'Producción',
        'title' => 'Solicitudes de producción pendientes',
        'message' => $count . ' solicitud(es) requieren atención' . ($ultima->folio ? ' (última: ' . $ultima->folio . ')' : ''),
        'link' => base_url('produccion/Dashboard'),
        'time' => 'Ahora'
      ];
      $total_count++;
    }

    // 8. RECURSOS HUMANOS - Nóminas pendientes de revisión/pago
    if (tiene_permiso('rh_nomina') && $this->db->table_exists('nominas')) {
      $this->db->select('id, folio, tipo_nomina, periodo_inicio, periodo_fin, fecha_pago, estatus, total_neto');
      $this->db->from('nominas');
      $this->db->where_in('estatus', ['Borrador', 'Calculada', 'Parcial']);
      $this->db->order_by('fecha_pago', 'ASC');
      $this->db->limit(5);
      $nominas_pendientes = $this->db->get()->result();

      foreach ($nominas_pendientes as $nom) {
        $dias = (int)floor((strtotime($nom->fecha_pago) - strtotime(date('Y-m-d'))) / 86400);
        if ($dias < -3) {
          $type = 'danger';
          $time = abs($dias) . 'd vencida';
        } elseif ($dias <= 0) {
          $type = 'warning';
          $time = 'Hoy';
        } elseif ($dias <= 3) {
          $type = 'info';
          $time = 'En ' . $dias . 'd';
        } else {
          continue; // aún lejos de la fecha de pago
        }
        $notifications[] = [
          'type' => $type,
          'icon' => 'money-bill-wave',
          'module' => 'RH',
          'title' => 'Nómina ' . $nom->estatus,
          'message' => $nom->folio . ' (' . $nom->tipo_nomina . ') · ' .
            date('d/m', strtotime($nom->periodo_inicio)) . '–' . date('d/m', strtotime($nom->periodo_fin)) .
            ' · Neto $' . number_format((float)$nom->total_neto, 2),
          'link' => base_url('rh/Nomina'),
          'time' => $time
        ];
        $total_count++;
      }
    }

    // 8b. RH — Cancelaciones recientes (alerta distinta a pendientes de pago)
    if (tiene_permiso('rh_nomina') && $this->db->table_exists('nominas_cancelaciones')) {
      $this->db->select('nc.motivo, nc.created_at, n.id, n.folio, n.tipo_nomina, n.periodo_inicio, n.periodo_fin');
      $this->db->from('nominas_cancelaciones nc');
      $this->db->join('nominas n', 'n.id = nc.nomina_id');
      $this->db->where('nc.created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')));
      $this->db->order_by('nc.created_at', 'DESC');
      $this->db->limit(5);
      $canceladas_recientes = $this->db->get()->result();
      foreach ($canceladas_recientes as $canc) {
        $hace = max(0, (int)floor((time() - strtotime($canc->created_at)) / 3600));
        $time = $hace < 1 ? 'Ahora' : ($hace < 24 ? $hace . 'h' : ((int)floor($hace / 24) . 'd'));
        $notifications[] = [
          'type' => 'warning',
          'icon' => 'ban',
          'module' => 'RH',
          'title' => 'Nómina cancelada',
          'message' => $canc->folio . ' · ' . substr((string)$canc->motivo, 0, 80),
          'link' => base_url('rh/Nomina'),
          'time' => $time
        ];
        $total_count++;
      }
    }

    // Priorizar vencidas/críticas y alertas de RH para que no las tape el stock de almacén.
    usort($notifications, function ($a, $b) {
        $prioType = ['danger' => 0, 'warning' => 1, 'info' => 2];
        $ta = $prioType[$a['type'] ?? ''] ?? 3;
        $tb = $prioType[$b['type'] ?? ''] ?? 3;
        if ($ta !== $tb) {
            return $ta - $tb;
        }
        $ma = (($a['module'] ?? '') === 'RH') ? 0 : 1;
        $mb = (($b['module'] ?? '') === 'RH') ? 0 : 1;
        return $ma - $mb;
    });
    $notifications = array_slice($notifications, 0, 12);

    echo json_encode([
      'success' => true,
      'total_count' => $total_count,
      'notifications' => $notifications
    ]);
  }

  // =====================================================
  // MÉTODOS PRIVADOS PARA OBTENER DATOS
  // =====================================================

  /**
   * Obtiene productos e insumos con stock bajo
   */
  private function _get_stock_bajo() {
    // Productos con stock bajo o en cero
    $this->db->select('id, codigo, nombre, stock_actual, stock_minimo, unidad_venta as unidad');
    $this->db->from('productos');
    $this->db->where('stock_actual <=', 'stock_minimo', FALSE);
    $this->db->where('estatus', 'Activo');
    $this->db->limit(5);
    $productos = $this->db->get()->result();

    // Insumos con stock bajo o en cero (incluye stock = 0)
    $this->db->select('id, codigo, nombre_tecnico as nombre, stock_actual, stock_minimo, unidad_medida as unidad');
    $this->db->from('insumos');
    $this->db->where('stock_actual <=', 'stock_minimo', FALSE);
    $this->db->where('estatus', 'Activo');
    $this->db->order_by('stock_actual', 'ASC');
    $this->db->limit(10);
    $insumos = $this->db->get()->result();

    return array_merge($productos, $insumos);
  }

  /**
   * Obtiene órdenes de venta pendientes de entrega
   */
  private function _get_ordenes_pendientes() {
    $this->db->select('id, folio, fecha_entrega_estimada');
    $this->db->from('ordenes_venta');
    $this->db->where_in('estatus', ['Confirmada', 'En Proceso']);
    $this->db->where('fecha_entrega_real IS NULL');
    $this->db->limit(5);
    return $this->db->get()->result();
  }

  /**
   * Obtiene obras con retraso en entrega
   */
  private function _get_obras_retrasadas() {
    $this->db->select('id, folio, nombre, fecha_inicio_estimada, fecha_fin_estimada');
    $this->db->from('obras');
    $this->db->where_in('estatus', ['En Ejecución', 'Aprobada']);
    $this->db->where('fecha_fin_estimada <', date('Y-m-d'));
    $this->db->where('activo', 1);
    $this->db->limit(5);
    return $this->db->get()->result();
  }

  /**
   * Obtiene empleados con datos faltantes
   */
  private function _get_empleados_datos_faltantes() {
    $this->db->select('id, nombre, apellido_paterno, apellido_materno, nss, rfc, curp');
    $this->db->from('empleados');
    $this->db->where('estatus', 'Activo'); // String 'Activo', no número
    $this->db->group_start();
    $this->db->where('nss IS NULL')->or_where('nss', '');
    $this->db->or_where('rfc IS NULL')->or_where('rfc', '');
    $this->db->or_where('curp IS NULL')->or_where('curp', '');
    $this->db->group_end();
    $this->db->limit(5);
    return $this->db->get()->result();
  }

  /**
   * Obtiene órdenes de compra pendientes
   */
  private function _get_ordenes_compra_pendientes() {
    $this->db->select('id, folio');
    $this->db->from('ordenes_compra');
    $this->db->where_in('estatus', ['Pendiente', 'En Tránsito']);
    $this->db->limit(5);
    return $this->db->get()->result();
  }

  /**
   * Obtiene productos sin formulación activa
   */
  private function _get_productos_sin_formulacion() {
    $this->db->select('p.id, p.codigo, p.nombre');
    $this->db->from('productos p');
    $this->db->join('formulaciones f', 'f.producto_id = p.id', 'left');
    $this->db->where('f.id IS NULL');
    $this->db->where('p.estatus', 'Activo');
    $this->db->limit(5);
    return $this->db->get()->result();
  }

  /**
   * Cuenta pre-órdenes de compra (tabla `preordenes`) pendientes de autorización
   */
  private function _get_preordenes_pendientes() {
    $this->db->where('estatus', 'Pendiente');
    return $this->db->count_all_results('preordenes');
  }

  /**
   * Solicitudes de producción pendientes
   */
  private function _get_solicitudes_produccion_pendientes() {
    $this->db->select('id, folio, fecha_solicitud, fecha_creacion');
    $this->db->from('solicitudes_produccion');
    $this->db->where('estatus', 'Pendiente');
    $this->db->order_by('fecha_creacion', 'DESC');
    $this->db->limit(10);
    return $this->db->get()->result();
  }

  /**
   * Calcula días de retraso desde una fecha
   */
  private function _calcular_dias_retraso($fecha_estimada) {
    if(empty($fecha_estimada)) return 0;
    
    $fecha_est = new DateTime($fecha_estimada);
    $hoy = new DateTime();
    
    if($hoy > $fecha_est) {
      $diff = $hoy->diff($fecha_est);
      return $diff->days;
    }
    
    return 0;
  }

}
