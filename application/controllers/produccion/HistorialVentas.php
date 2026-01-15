<?php
/**
 * HistorialVentas Controller - Búsqueda de historial de ventas de productos
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class HistorialVentas extends MY_Controller {
    
    protected $modulo = 'Producción';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Produccion/ProductosModel');
    }
    
    /**
     * Vista principal del buscador
     */
    public function index() {
        $data['pageTitle'] = 'Historial de Ventas';
        $data['headTitle'] = 'Historial de Ventas de Productos';
        $data['breadcrumb'] = 'Inicio > Producción > Historial de Ventas';
        
        // Obtener lista de productos para el filtro
        $this->db->select('id, codigo, nombre');
        $this->db->where('estatus', 1);
        $this->db->order_by('nombre', 'ASC');
        $productos = $this->db->get('productos')->result();
        
        // Obtener lista de clientes para el filtro
        $this->db->select('id, razon_social');
        $this->db->where('estatus', 1);
        $this->db->order_by('razon_social', 'ASC');
        $clientes = $this->db->get('clientes')->result();
        
        $data['response'] = [
            'productos' => $productos,
            'clientes' => $clientes
        ];
        
        $data['validate'] = '';
        $data['pageView'] = 'produccion/historial_ventas/main';
        
        $this->load->view('layouts/general_template', $data);
    }
    
    /**
     * Búsqueda AJAX para DataTables
     */
    public function buscar_ajax() {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search')['value'];
        
        // Filtros personalizados
        $producto_id = $this->input->post('producto_id');
        $cliente_id = $this->input->post('cliente_id');
        $formulacion_id = $this->input->post('formulacion_id');
        $tipo = $this->input->post('tipo'); // 'venta', 'obra', o null para todos
        $fecha_inicio = $this->input->post('fecha_inicio');
        $fecha_fin = $this->input->post('fecha_fin');
        
        // Query unificado de órdenes de venta y obras
        $subquery_ventas = "
            SELECT 
                'venta' as tipo,
                ov.id,
                ov.folio,
                ov.fecha_creacion,
                ov.estatus,
                p.id as producto_id,
                p.codigo as producto_codigo,
                p.nombre as producto_nombre,
                p.foto_producto as producto_imagen,
                c.id as cliente_id,
                c.razon_social as cliente_nombre,
                c.rfc as cliente_rfc,
                c.telefono as cliente_telefono,
                c.email as cliente_email,
                f.id as formulacion_id,
                f.version as formulacion_version,
                f.nombre_version as formulacion_nombre,
                dov.cantidad,
                p.unidad_venta as unidad
            FROM ordenes_venta ov
            JOIN detalle_orden_venta dov ON dov.orden_venta_id = ov.id
            JOIN productos p ON p.id = dov.producto_id
            JOIN clientes c ON c.id = ov.cliente_id
            LEFT JOIN formulaciones f ON f.id = dov.formulacion_id
        ";
        
        $subquery_obras = "
            SELECT 
                'obra' as tipo,
                o.id,
                o.folio,
                o.fecha_creacion,
                o.estatus,
                p.id as producto_id,
                p.codigo as producto_codigo,
                p.nombre as producto_nombre,
                p.foto_producto as producto_imagen,
                c.id as cliente_id,
                c.razon_social as cliente_nombre,
                c.rfc as cliente_rfc,
                c.telefono as cliente_telefono,
                c.email as cliente_email,
                f.id as formulacion_id,
                f.version as formulacion_version,
                f.nombre_version as formulacion_nombre,
                op.cantidad_ajustada as cantidad,
                'unidad' as unidad
            FROM obras o
            JOIN obras_productos op ON op.obra_id = o.id
            JOIN productos p ON p.id = op.producto_id
            JOIN clientes c ON c.id = o.cliente_id
            LEFT JOIN formulaciones f ON f.id = op.formulacion_id
        ";
        
        // Construir query final
        if($tipo == 'venta') {
            $query = "SELECT * FROM ($subquery_ventas) AS ventas WHERE 1=1";
        } elseif($tipo == 'obra') {
            $query = "SELECT * FROM ($subquery_obras) AS obras WHERE 1=1";
        } else {
            $query = "SELECT * FROM ($subquery_ventas UNION ALL $subquery_obras) AS ventas_unificadas WHERE 1=1";
        }
        
        // Aplicar filtros
        $params = [];
        
        if($producto_id) {
            $query .= " AND producto_id = ?";
            $params[] = $producto_id;
        }
        
        if($cliente_id) {
            $query .= " AND cliente_id = ?";
            $params[] = $cliente_id;
        }
        
        if($formulacion_id) {
            $query .= " AND formulacion_id = ?";
            $params[] = $formulacion_id;
        }
        
        if($fecha_inicio && $fecha_fin) {
            $query .= " AND DATE(fecha_creacion) BETWEEN ? AND ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        // Búsqueda global
        if(!empty($search)) {
            $query .= " AND (
                folio LIKE ? OR
                producto_codigo LIKE ? OR
                producto_nombre LIKE ? OR
                cliente_nombre LIKE ? OR
                cliente_rfc LIKE ?
            )";
            $search_param = "%$search%";
            $params = array_merge($params, array_fill(0, 5, $search_param));
        }
        
        // Contar total
        $count_query = "SELECT COUNT(*) as total FROM ($query) AS count_table";
        $total_records = $this->db->query($count_query, $params)->row()->total;
        
        // Ordenar y paginar
        $query .= " ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
        $params[] = (int)$length;
        $params[] = (int)$start;
        
        $resultados = $this->db->query($query, $params)->result();
        
        // Formatear datos para DataTables
        $data = [];
        foreach($resultados as $row) {
            // Icono de tipo
            $tipo_icon = $row->tipo == 'venta' ? '🛒' : '🏗️';
            $tipo_text = $row->tipo == 'venta' ? 'Venta' : 'Obra';
            
            // Badge de estatus
            $badge_class = $this->get_badge_class($row->estatus);
            $estatus_badge = '<span class="badge bg-' . $badge_class . '">' . $row->estatus . '</span>';
            
            // Formulación
            $formulacion = $row->formulacion_version 
                ? '<span class="badge bg-info">V' . $row->formulacion_version . '</span><br><small>' . ($row->formulacion_nombre ?? '') . '</small>'
                : '<span class="text-muted">Sin formulación</span>';
            
            // Acciones
            $url_detalle = $row->tipo == 'venta' 
                ? base_url('produccion/Dashboard/detalle/venta/' . $row->id)
                : base_url('produccion/Dashboard/detalle/obra/' . $row->id);
                
            $acciones = '
                <a href="' . $url_detalle . '" class="btn btn-sm btn-primary" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </a>
            ';
            
            if($row->formulacion_id) {
                $acciones .= '
                    <button onclick="verFormulacion(' . $row->formulacion_id . ', \'' . addslashes($row->producto_nombre) . '\')" 
                            class="btn btn-sm btn-info" title="Ver Formulación">
                        <i class="fas fa-flask"></i>
                    </button>
                ';
            }
            
            $data[] = [
                date('d/m/Y', strtotime($row->fecha_creacion)),
                $tipo_icon . ' ' . $tipo_text,
                $row->folio,
                '<strong>' . $row->producto_nombre . '</strong><br><small class="text-muted">' . $row->producto_codigo . '</small>',
                $row->cliente_nombre,
                $formulacion,
                '<strong>' . number_format($row->cantidad, 2) . '</strong> ' . $row->unidad,
                $estatus_badge,
                $acciones
            ];
        }
        
        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_records,
            'data' => $data
        ]);
    }
    
    /**
     * Obtiene formulaciones de un producto (AJAX)
     */
    public function get_formulaciones_ajax() {
        $producto_id = $this->input->post('producto_id');
        
        if(!$producto_id) {
            echo json_encode([]);
            return;
        }
        
        $this->db->select('id, version, nombre_version');
        $this->db->where('producto_id', $producto_id);
        $this->db->order_by('version', 'DESC');
        $formulaciones = $this->db->get('formulaciones')->result();
        
        echo json_encode($formulaciones);
    }
    
    /**
     * Obtiene clase de badge según estatus
     */
    private function get_badge_class($estatus) {
        $classes = [
            'Pendiente' => 'warning',
            'Confirmada' => 'info',
            'En Producción' => 'primary',
            'Completada' => 'success',
            'Entregada' => 'success',
            'Cancelada' => 'danger',
            'Planificación' => 'secondary',
            'En Cotización' => 'warning',
            'Aprobada' => 'success',
            'En Ejecución' => 'primary',
            'Pausada' => 'danger'
        ];
        
        return $classes[$estatus] ?? 'secondary';
    }
}
