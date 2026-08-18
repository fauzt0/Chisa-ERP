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

        if(isset($_POST['filtro_tipo_contacto']) && $_POST['filtro_tipo_contacto'] != '') {
            $this->db->where('clientes.tipo_contacto', $_POST['filtro_tipo_contacto']);
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
     * Historial paginado de órdenes de venta de un cliente (excluye cotizaciones)
     */
    public function get_historial_ventas($cliente_id, $limit = 10, $offset = 0) {
        $this->db->select('id, folio, fecha_orden, total, estatus, estatus_pago, tipo_venta');
        $this->db->from('ordenes_venta');
        $this->db->where('cliente_id', $cliente_id);
        $this->db->where('estatus !=', 'Cotización');
        $this->db->order_by('fecha_orden', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    /**
     * Cuenta el historial de ventas de un cliente (excluye cotizaciones)
     */
    public function count_historial_ventas($cliente_id) {
        $this->db->where('cliente_id', $cliente_id);
        $this->db->where('estatus !=', 'Cotización');
        return $this->db->count_all_results('ordenes_venta');
    }

    /**
     * Historial paginado de cotizaciones de un cliente
     */
    public function get_historial_cotizaciones($cliente_id, $limit = 10, $offset = 0) {
        $this->db->select('id, folio, fecha_orden, total, estatus, estatus_pago, tipo_venta');
        $this->db->from('ordenes_venta');
        $this->db->where('cliente_id', $cliente_id);
        $this->db->where('estatus', 'Cotización');
        $this->db->order_by('fecha_orden', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    /**
     * Cuenta las cotizaciones de un cliente
     */
    public function count_historial_cotizaciones($cliente_id) {
        $this->db->where('cliente_id', $cliente_id);
        $this->db->where('estatus', 'Cotización');
        return $this->db->count_all_results('ordenes_venta');
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

    /**
     * Seguimientos CRM de un cliente
     */
    public function get_seguimientos($cliente_id, $limit = 20) {
        $this->db->select('s.*, CONCAT(a.nombre, " ", a.apellidos) as usuario_nombre');
        $this->db->from('seguimientos_cliente s');
        $this->db->join('administradores a', 'a.id = s.usuario_id', 'left');
        $this->db->where('s.cliente_id', (int) $cliente_id);
        $this->db->order_by('s.fecha', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Registra un seguimiento CRM
     */
    public function crear_seguimiento($data) {
        $data['fecha_registro'] = date('Y-m-d H:i:s');
        if (empty($data['fecha'])) {
            $data['fecha'] = date('Y-m-d H:i:s');
        }
        return $this->db->insert('seguimientos_cliente', $data);
    }

    /**
     * Elimina un seguimiento CRM
     */
    public function eliminar_seguimiento($id, $cliente_id) {
        $this->db->where('id', (int) $id);
        $this->db->where('cliente_id', (int) $cliente_id);
        return $this->db->delete('seguimientos_cliente');
    }

    /**
     * Contactos adicionales de un cliente
     */
    public function get_contactos($cliente_id) {
        $this->db->from('contactos_cliente');
        $this->db->where('cliente_id', (int) $cliente_id);
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('es_principal', 'DESC');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Obtiene un contacto adicional por ID y cliente
     */
    public function get_contacto($id, $cliente_id) {
        $this->db->from('contactos_cliente');
        $this->db->where('id', (int) $id);
        $this->db->where('cliente_id', (int) $cliente_id);
        $this->db->where('estatus', 'Activo');
        return $this->db->get()->row();
    }

    /**
     * Crea o actualiza un contacto adicional
     */
    public function guardar_contacto($data, $id = null) {
        $cliente_id = (int) ($data['cliente_id'] ?? 0);
        if (!$cliente_id) {
            return false;
        }

        $payload = [
            'nombre' => trim($data['nombre'] ?? ''),
            'puesto' => trim($data['puesto'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'es_principal' => !empty($data['es_principal']) ? 1 : 0,
            'observaciones' => trim($data['observaciones'] ?? '') ?: null,
            'estatus' => 'Activo',
        ];

        if ($payload['nombre'] === '') {
            return false;
        }

        if (!empty($payload['es_principal'])) {
            $this->db->where('cliente_id', $cliente_id);
            $this->db->update('contactos_cliente', ['es_principal' => 0]);
        }

        if ($id) {
            $this->db->where('id', (int) $id);
            $this->db->where('cliente_id', $cliente_id);
            return $this->db->update('contactos_cliente', $payload);
        }

        $payload['cliente_id'] = $cliente_id;
        return $this->db->insert('contactos_cliente', $payload);
    }

    /**
     * Baja lógica de un contacto adicional
     */
    public function eliminar_contacto($id, $cliente_id) {
        $this->db->where('id', (int) $id);
        $this->db->where('cliente_id', (int) $cliente_id);
        return $this->db->update('contactos_cliente', ['estatus' => 'Inactivo']);
    }

    /**
     * Importa clientes desde filas parseadas del Excel
     */
    public function importar_masivo(array $rows, $usuario_id = null) {
        $inserted = 0;
        $errors = 0;
        $skipped = 0;
        $messages = [];
        $tipos_validos = ['Empresa', 'Persona Física', 'Mostrador'];
        $estatus_validos = ['Activo', 'Inactivo', 'Suspendido'];
        $tipos_contacto_validos = ['Cliente', 'Prospecto'];
        $rfcs_vistos = [];

        foreach ($rows as $idx => $row) {
            $linea = (int) ($row['_linea'] ?? ($idx + 2));
            $razon = trim($row['razon_social'] ?? '');
            $rfc = strtoupper(preg_replace('/\s+/', '', $row['rfc'] ?? ''));

            if (preg_match('/^\(EJEMPLO\)/i', $razon)) {
                $skipped++;
                $messages[] = "Fila {$linea}: fila de ejemplo omitida.";
                continue;
            }

            if ($razon === '') {
                $errors++;
                $messages[] = "Fila {$linea}: la razón social es obligatoria.";
                continue;
            }

            if ($rfc === '') {
                $errors++;
                $messages[] = "Fila {$linea}: el RFC es obligatorio.";
                continue;
            }

            if (strlen($rfc) < 12 || strlen($rfc) > 13) {
                $errors++;
                $messages[] = "Fila {$linea}: RFC «{$rfc}» no tiene formato válido (12–13 caracteres).";
                continue;
            }

            if (isset($rfcs_vistos[$rfc])) {
                $skipped++;
                $messages[] = "Fila {$linea}: RFC {$rfc} duplicado en el archivo (omitido).";
                continue;
            }
            $rfcs_vistos[$rfc] = true;

            $this->db->where('rfc', $rfc);
            if ($this->db->count_all_results($this->tableName) > 0) {
                $skipped++;
                $messages[] = "Fila {$linea}: RFC {$rfc} ya existe en el sistema (omitido).";
                continue;
            }

            $tipo = trim($row['tipo_cliente'] ?? '');
            if ($tipo === '' || !in_array($tipo, $tipos_validos, true)) {
                $tipo = 'Empresa';
            }

            $tipo_contacto = trim($row['tipo_contacto'] ?? '');
            if ($tipo_contacto === '' || !in_array($tipo_contacto, $tipos_contacto_validos, true)) {
                $tipo_contacto = 'Cliente';
            }

            $estatus = trim($row['estatus'] ?? '');
            if ($estatus === '' || !in_array($estatus, $estatus_validos, true)) {
                $estatus = 'Activo';
            }

            $data = [
                'razon_social' => $razon,
                'nombre_comercial' => trim($row['nombre_comercial'] ?? ''),
                'rfc' => $rfc,
                'regimen_fiscal' => trim($row['regimen_fiscal'] ?? ''),
                'contacto_nombre' => trim($row['contacto_nombre'] ?? ''),
                'telefono' => trim($row['telefono'] ?? ''),
                'email' => trim($row['email'] ?? ''),
                'calle' => trim($row['calle'] ?? ''),
                'numero_exterior' => trim($row['numero_exterior'] ?? ''),
                'numero_interior' => trim($row['numero_interior'] ?? ''),
                'colonia' => trim($row['colonia'] ?? ''),
                'ciudad' => trim($row['ciudad'] ?? ''),
                'estado' => trim($row['estado'] ?? ''),
                'codigo_postal' => trim($row['codigo_postal'] ?? ''),
                'limite_credito' => is_numeric($row['limite_credito'] ?? '') ? (float) $row['limite_credito'] : 0,
                'dias_credito' => is_numeric($row['dias_credito'] ?? '') ? (int) $row['dias_credito'] : 0,
                'tipo_cliente' => $tipo,
                'tipo_contacto' => $tipo_contacto,
                'estatus' => $estatus,
                'saldo_pendiente' => 0,
            ];

            if ($this->crear_cliente($data)) {
                $inserted++;
            } else {
                $errors++;
                $messages[] = "Fila {$linea}: no se pudo insertar «{$razon}».";
            }
        }

        return [
            'inserted' => $inserted,
            'errors' => $errors,
            'skipped' => $skipped,
            'messages' => $messages,
        ];
    }
}
