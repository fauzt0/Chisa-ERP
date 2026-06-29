<?php
/**
 * ClientesModel - Modelo de gestión de clientes
 * 
 * Gestiona clientes del sistema CRM con CRUD completo
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class ClientesModel extends MY_Model {
    
    protected $tableName = 'clientes';
    
    // Configuración para DataTables
    protected $datatableConfig = [
        'table' => 'clientes',
        'column_order' => ['clientes.codigo', 'clientes.razon_social', 'clientes.rfc', 'clientes.telefono', 'clientes.ciudad', 'clientes.saldo_pendiente', 'clientes.tipo_cliente', 'clientes.estatus', null],
        'column_search' => ['clientes.codigo', 'clientes.razon_social', 'clientes.nombre_comercial', 'clientes.rfc', 'clientes.telefono', 'clientes.email', 'clientes.contacto_nombre', 'clientes.ciudad'],
        'order' => ['fecha_creacion' => 'DESC']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Override de _get_datatables_query
     */
    protected function _get_datatables_query() {
        $this->db->select('clientes.*,
            (SELECT COUNT(*) FROM ordenes_venta ov WHERE ov.cliente_id = clientes.id) AS total_ordenes,
            (SELECT MAX(ov2.fecha_orden) FROM ordenes_venta ov2 WHERE ov2.cliente_id = clientes.id) AS ultima_orden', false);
        $this->db->from($this->tableName);
        
        // Excluir cliente MOSTRADOR de la lista
        $this->db->where('codigo !=', 'CLI-00000');
        
        // Filtros adicionales
        if(isset($_POST['filtro_tipo_cliente']) && $_POST['filtro_tipo_cliente'] != '') {
            $this->db->where('clientes.tipo_cliente', $_POST['filtro_tipo_cliente']);
        }
        
        if(isset($_POST['filtro_estatus']) && $_POST['filtro_estatus'] != '') {
            $this->db->where('clientes.estatus', $_POST['filtro_estatus']);
        }
        
        if(isset($_POST['filtro_saldo']) && $_POST['filtro_saldo'] != '') {
            if($_POST['filtro_saldo'] == 'con_saldo') {
                $this->db->where('clientes.saldo_pendiente >', 0);
            } else if($_POST['filtro_saldo'] == 'sin_saldo') {
                $this->db->where('clientes.saldo_pendiente', 0);
            }
        }

        if(isset($_POST['filtro_busqueda_rapida']) && $_POST['filtro_busqueda_rapida'] !== '') {
            $q = $_POST['filtro_busqueda_rapida'];
            $this->db->group_start();
            $this->db->like('clientes.razon_social', $q);
            $this->db->or_like('clientes.nombre_comercial', $q);
            $this->db->or_like('clientes.rfc', $q);
            $this->db->or_like('clientes.codigo', $q);
            $this->db->or_like('clientes.telefono', $q);
            $this->db->or_like('clientes.email', $q);
            $this->db->group_end();
        }
        
        // Búsqueda
        $i = 0;
        if(isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            foreach ($this->datatableConfig['column_search'] as $column) {
                if($i === 0) {
                    $this->db->group_start();
                    $this->db->like($column, $_POST['search']['value']);
                } else {
                    $this->db->or_like($column, $_POST['search']['value']);
                }
                
                if(count($this->datatableConfig['column_search']) - 1 == $i) {
                    $this->db->group_end();
                }
                $i++;
            }
        }
        
        // Ordenamiento
        if(isset($_POST['order']) && isset($_POST['order'][0])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index] ?? null;
            if($column_name) {
                $this->db->order_by($column_name, $_POST['order'][0]['dir']);
            }
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by('clientes.' . key($order), $order[key($order)]);
        }
    }

    /**
     * Órdenes de venta de un cliente
     */
    public function get_ordenes_cliente($cliente_id, $limit = 10) {
        $this->db->select('id, folio, fecha_orden, total, estatus, estatus_pago');
        $this->db->from('ordenes_venta');
        $this->db->where('cliente_id', $cliente_id);
        $this->db->order_by('fecha_orden', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
    
    /**
     * Override de get_datatables
     */
    public function get_datatables() {
        $this->_get_datatables_query();
        if(isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Override de count_filtered
     */
    public function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
    
    /**
     * Override de count_all
     */
    public function count_all($where = []) {
        $this->db->from($this->tableName);
        $this->db->where('codigo !=', 'CLI-00000');
        return $this->db->count_all_results();
    }
    
    /**
     * Obtiene un cliente por ID
     */
    public function get_cliente($id) {
        $this->db->where('id', $id);
        return $this->db->get($this->tableName)->row();
    }
    
    /**
     * Crea un nuevo cliente
     */
    public function crear_cliente($data) {
        // Generar código si no existe
        if(empty($data['codigo'])) {
            $data['codigo'] = $this->generar_codigo();
        }
        
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza un cliente
     */
    public function actualizar_cliente($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina un cliente
     */
    public function eliminar_cliente($id) {
        // Verificar si tiene órdenes de venta
        $this->db->where('cliente_id', $id);
        $tiene_ordenes = $this->db->count_all_results('ordenes_venta') > 0;
        
        if($tiene_ordenes) {
            return ['success' => false, 'message' => 'No se puede eliminar: el cliente tiene órdenes de venta registradas'];
        }
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Cliente eliminado' : 'Error al eliminar'];
    }
    
    /**
     * Genera código único para cliente
     */
    private function generar_codigo() {
        $prefijo = 'CLI-';
        
        $this->db->select('codigo');
        $this->db->from($this->tableName);
        $this->db->like('codigo', $prefijo, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultimo = $this->db->get()->row();
        
        if($ultimo) {
            $numero = intval(substr($ultimo->codigo, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtiene clientes para select (activos)
     */
    public function get_clientes_select() {
        $this->db->select('id, codigo, razon_social, nombre_comercial, rfc, email, email_facturacion, limite_credito, dias_credito');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('razon_social', 'ASC');
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Obtiene cliente MOSTRADOR
     */
    public function get_cliente_mostrador() {
        $this->db->where('codigo', 'CLI-00000');
        return $this->db->get($this->tableName)->row();
    }
    
    /**
     * Obtiene estadísticas de clientes
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Total de clientes (sin mostrador)
        $this->db->where('codigo !=', 'CLI-00000');
        $stats['total_clientes'] = $this->db->count_all_results($this->tableName);
        
        $total = $stats['total_clientes'] > 0 ? $stats['total_clientes'] : 1;
        
        // Clientes activos
        $this->db->where('estatus', 'Activo');
        $this->db->where('codigo !=', 'CLI-00000');
        $stats['clientes_activos'] = $this->db->count_all_results($this->tableName);
        
        $stats['porcentaje_activos'] = round(($stats['clientes_activos'] / $total) * 100);
        
        // Clientes regulares (Tipo)
        $this->db->where('tipo_cliente', 'Regular');
        $this->db->where('codigo !=', 'CLI-00000');
        $stats['clientes_regulares'] = $this->db->count_all_results($this->tableName);
        
        $stats['porcentaje_regulares'] = round(($stats['clientes_regulares'] / $total) * 100);
        
        // Clientes con saldo pendiente
        $this->db->where('saldo_pendiente >', 0);
        $this->db->where('codigo !=', 'CLI-00000');
        $stats['clientes_con_saldo'] = $this->db->count_all_results($this->tableName);
        
        $stats['porcentaje_con_saldo'] = round(($stats['clientes_con_saldo'] / $total) * 100);
        
        // Nuevos Clientes (últimos 30 días)
        $fecha_limite = date('Y-m-d', strtotime('-30 days'));
        $this->db->where('fecha_creacion >=', $fecha_limite);
        $this->db->where('codigo !=', 'CLI-00000');
        $stats['nuevos_30_dias'] = $this->db->count_all_results($this->tableName);
        
        // Crecimiento (vs periodo anterior 30 días - estimación simple)
        $this->db->where('fecha_creacion <', $fecha_limite);
        $this->db->where('fecha_creacion >=', date('Y-m-d', strtotime('-60 days')));
        $this->db->where('codigo !=', 'CLI-00000');
        $nuevos_anteriores = $this->db->count_all_results($this->tableName);
        
        $stats['porcentaje_crecimiento_nuevos'] = ($nuevos_anteriores > 0) 
            ? round((($stats['nuevos_30_dias'] - $nuevos_anteriores) / $nuevos_anteriores) * 100) 
            : 100; // Si no había nuevos, 100% crecimiento
            
        return $stats;
    }

    /**
     * Obtiene nuevos clientes mensuales del año actual para graficar
     */
    public function get_nuevos_clientes_mensuales_anio() {
        $anio = date('Y');
        $this->db->select('MONTH(fecha_creacion) as mes, COUNT(*) as cantidad');
        $this->db->from($this->tableName);
        $this->db->where('YEAR(fecha_creacion)', $anio);
        $this->db->where('codigo !=', 'CLI-00000'); // Excluir mostrador
        $this->db->group_by('MONTH(fecha_creacion)');
        $this->db->order_by('mes', 'ASC');
        
        $resultados = $this->db->get()->result();
        
        // Formatear array con 12 meses inicializados en 0
        $datos_mensuales = array_fill(1, 12, 0);
        
        foreach($resultados as $fila) {
            $datos_mensuales[$fila->mes] = (int)$fila->cantidad;
        }
        
        return array_values($datos_mensuales); // Retornar indexado desde 0 para JS
    }
}
