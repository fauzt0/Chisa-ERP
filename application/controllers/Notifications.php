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

    // Limitar a las 10 notificaciones más importantes
    $notifications = array_slice($notifications, 0, 10);

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
