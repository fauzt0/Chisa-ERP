<?php
/**
 * MY_Model - Modelo base para todos los modelos del ERP
 * 
 * Implementa funcionalidades comunes para todos los modelos:
 * - Operaciones CRUD básicas
 * - Soft delete (baja lógica)
 * - Búsquedas con DataTables
 * - Respuestas estandarizadas
 * - Validaciones comunes
 * 
 * @author ERP Chisa Recubrimientos
 * @version 1.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model {
    
    /**
     * Nombre de la tabla principal del modelo
     * @var string
     */
    protected $tableName;
    
    /**
     * Nombre de la clave primaria
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Campo que indica si el registro está activo (soft delete)
     * @var string
     */
    protected $statusField = 'estatus';
    
    /**
     * Campos de fecha automáticos
     * @var array
     */
    protected $dateFields = [
        'created' => 'fecha_alta',
        'updated' => 'fecha_edicion',
        'deleted' => 'fecha_baja'
    ];
    
    /**
     * Configuración para DataTables
     * @var array
     */
    protected $datatableConfig = [
        'column_order' => [],   // Columnas ordenables
        'column_search' => [],  // Columnas buscables
        'order' => ['id' => 'asc'] // Orden por defecto
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    // ========================================================================
    // MÉTODOS CRUD BÁSICOS
    // ========================================================================
    
    /**
     * Obtiene un registro por su ID
     * 
     * @param int $id ID del registro
     * @return object|null Objeto con los datos o null si no existe
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->tableName, [$this->primaryKey => $id])->row();
    }
    
    /**
     * Obtiene todos los registros
     * 
     * @param array $where Condiciones WHERE opcionales
     * @param string|array $order_by Orden opcional (ej: 'id DESC' o ['id' => 'DESC'])
     * @param int $limit Límite de registros
     * @param int $offset Offset para paginación
     * @return array Array de objetos con los resultados
     */
    public function get_all($where = [], $order_by = null, $limit = null, $offset = null) {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        if ($order_by) {
            if (is_array($order_by)) {
                foreach ($order_by as $field => $direction) {
                    $this->db->order_by($field, $direction);
                }
            } else {
                $this->db->order_by($order_by);
            }
        }
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Obtiene registros activos solamente
     * 
     * @param array $where Condiciones adicionales
     * @param string|array $order_by Orden opcional
     * @return array Array de objetos
     */
    public function get_active($where = [], $order_by = null) {
        $where[$this->statusField] = 1;
        return $this->get_all($where, $order_by);
    }
    
    /**
     * Inserta un nuevo registro
     * 
     * @param array $data Datos a insertar
     * @param bool $add_created_date Agregar fecha de creación automáticamente
     * @return int ID del registro insertado
     */
    protected function insert($data, $add_created_date = true) {
        // Agregar fecha de creación si está configurada
        if ($add_created_date && isset($this->dateFields['created'])) {
            $data[$this->dateFields['created']] = date('Y-m-d');
        }
        
        // Agregar estatus activo por defecto
        if (isset($this->statusField) && !isset($data[$this->statusField])) {
            $data[$this->statusField] = 1;
        }
        
        $this->db->insert($this->tableName, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Actualiza un registro existente
     * 
     * @param int $id ID del registro a actualizar
     * @param array $data Datos a actualizar
     * @param bool $add_updated_date Agregar fecha de edición automáticamente
     * @return bool True si se actualizó correctamente
     */
    protected function update($id, $data, $add_updated_date = true) {
        // Agregar fecha de edición si está configurada
        if ($add_updated_date && isset($this->dateFields['updated'])) {
            $data[$this->dateFields['updated']] = date('Y-m-d');
        }
        
        $this->db->where($this->primaryKey, $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina un registro (soft delete)
     * 
     * @param int $id ID del registro a eliminar
     * @return bool True si se eliminó correctamente
     */
    protected function soft_delete($id) {
        $data = [$this->statusField => 0];
        
        // Agregar fecha de baja si está configurada
        if (isset($this->dateFields['deleted'])) {
            $data[$this->dateFields['deleted']] = date('Y-m-d');
        }
        
        return $this->update($id, $data, false);
    }
    
    /**
     * Restaura un registro eliminado (soft delete)
     * 
     * @param int $id ID del registro a restaurar
     * @return bool True si se restauró correctamente
     */
    protected function restore($id) {
        $data = [$this->statusField => 1];
        
        // Limpiar fecha de baja si está configurada
        if (isset($this->dateFields['deleted'])) {
            $data[$this->dateFields['deleted']] = null;
        }
        
        return $this->update($id, $data, false);
    }
    
    /**
     * Elimina permanentemente un registro (hard delete)
     * USAR CON PRECAUCIÓN
     * 
     * @param int $id ID del registro a eliminar
     * @return bool True si se eliminó correctamente
     */
    protected function hard_delete($id) {
        $this->db->where($this->primaryKey, $id);
        return $this->db->delete($this->tableName);
    }
    
    // ========================================================================
    // MÉTODOS DE VALIDACIÓN Y UTILIDAD
    // ========================================================================
    
    /**
     * Verifica si existe un registro con el ID dado
     * 
     * @param int $id ID del registro
     * @return bool True si existe
     */
    public function exists($id) {
        return $this->db->where($this->primaryKey, $id)
                        ->count_all_results($this->tableName) > 0;
    }
    
    /**
     * Verifica si existe un registro activo con el ID dado
     * 
     * @param int $id ID del registro
     * @return bool True si existe y está activo
     */
    public function exists_active($id) {
        return $this->db->where($this->primaryKey, $id)
                        ->where($this->statusField, 1)
                        ->count_all_results($this->tableName) > 0;
    }
    
    /**
     * Cuenta todos los registros de la tabla
     * 
     * @param array $where Condiciones opcionales
     * @return int Número de registros
     */
    public function count_all($where = []) {
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($this->tableName);
    }
    
    /**
     * Verifica si un campo es único (útil para validaciones)
     * 
     * @param string $field Nombre del campo
     * @param mixed $value Valor a verificar
     * @param int $exclude_id ID a excluir (útil para ediciones)
     * @return bool True si es único
     */
    public function is_unique($field, $value, $exclude_id = null) {
        $this->db->where($field, $value);
        
        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', $exclude_id);
        }
        
        return $this->db->count_all_results($this->tableName) === 0;
    }
    
    // ========================================================================
    // MÉTODOS PARA DATATABLES
    // ========================================================================
    
    /**
     * Obtiene los datos para DataTables con búsqueda y paginación
     * 
     * @return array Array de resultados
     */
    public function get_datatables() {
        $this->_get_datatables_query();
        
        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Cuenta los registros filtrados para DataTables
     * 
     * @return int Número de registros filtrados
     */
    public function count_filtered() {
        $this->_get_datatables_query();
        return $this->db->count_all_results();
    }
    
    /**
     * Construye la query para DataTables con filtros y ordenamiento
     * 
     * @return void
     */
    protected function _get_datatables_query() {
        $this->db->from($this->tableName);
        
        // Búsqueda general
        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $search = $_POST['search'];
            $this->db->group_start();
            
            foreach ($this->datatableConfig['column_search'] as $i => $column) {
                if ($i === 0) {
                    $this->db->like($column, $search);
                } else {
                    $this->db->or_like($column, $search);
                }
            }
            
            $this->db->group_end();
        }
        
        // Ordenamiento
        if (isset($_POST['order'])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index];
            $direction = $_POST['order'][0]['dir'];
            $this->db->order_by($column_name, $direction);
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
    
    // ========================================================================
    // MÉTODOS DE RESPUESTA ESTANDARIZADA
    // ========================================================================
    
    /**
     * Crea una respuesta exitosa estandarizada
     * 
     * @param string $message Mensaje de éxito
     * @param mixed $data Datos adicionales opcionales
     * @return array Array con estructura de respuesta
     */
    protected function success_response($message, $data = null) {
        $response = [
            'success' => 1,
            'msg' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Crea una respuesta de error estandarizada
     * 
     * @param string $message Mensaje de error
     * @param mixed $data Datos adicionales opcionales
     * @return array Array con estructura de respuesta
     */
    protected function error_response($message, $data = null) {
        $response = [
            'success' => 0,
            'msg' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Crea una respuesta de no encontrado estandarizada
     * 
     * @param string $message Mensaje personalizado
     * @return array Array con estructura de respuesta
     */
    protected function not_found_response($message = 'Registro no encontrado') {
        return [
            'success' => -1,
            'msg' => $message
        ];
    }
    
    /**
     * Obtiene el último error de base de datos
     * 
     * @return array Array con código y mensaje de error
     */
    protected function get_db_error() {
        return $this->db->error();
    }
    
    /**
     * Verifica si hubo un error en la última operación de BD
     * 
     * @return bool True si hubo error
     */
    protected function has_db_error() {
        $error = $this->db->error();
        return $error['code'] !== 0;
    }
    
}
