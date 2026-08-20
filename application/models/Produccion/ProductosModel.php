<?php
/**
 * ProductosModel - Modelo de gestión de productos terminados
 * 
 * Gestiona productos fabricados y de reventa con formulaciones (BOM),
 * control de inventario, códigos de barras/QR y alertas de stock
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductosModel extends MY_Model {
    
    protected $tableName = 'productos';
    
    // Configuración para DataTables (columnas calificadas por tabla — evita error SQL al buscar/ordenar)
    protected $datatableConfig = [
        'table' => 'productos',
        'column_order' => [
            'productos.codigo',
            'productos.nombre',
            'productos.alias',
            'categorias_productos.nombre',
            'productos.tipo_producto',
            'productos.stock_actual',
            'productos.precio_venta',
            'productos.estatus',
            null
        ],
        'column_search' => [
            'productos.codigo',
            'productos.nombre',
            'productos.alias',
            'categorias_productos.nombre'
        ],
        'order' => ['productos.fecha_creacion' => 'DESC']
    ];
    
    public function __construct() {
        parent::__construct();
        $this->load->helper('unidades');
    }
    
    /**
     * Override de _get_datatables_query para agregar join con categorías
     */
    protected function _get_datatables_query() {
        $this->db->select('productos.*, categorias_productos.nombre as categoria_nombre');
        $this->db->from($this->tableName);
        $this->db->join('categorias_productos', 'categorias_productos.id = productos.categoria_id', 'left');

        // Filtros del panel superior
        if (!empty($_POST['filtro_tipo'])) {
            $this->db->where('productos.tipo_producto', $_POST['filtro_tipo']);
        }
        if (!empty($_POST['filtro_estatus'])) {
            $this->db->where('productos.estatus', $_POST['filtro_estatus']);
        }
        if (!empty($_POST['filtro_stock'])) {
            if ($_POST['filtro_stock'] === 'bajo') {
                $this->db->where('productos.stock_actual <= productos.stock_minimo', null, false);
            } elseif ($_POST['filtro_stock'] === 'ok') {
                $this->db->where('productos.stock_actual > productos.stock_minimo', null, false);
            }
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
            $column_name = $this->datatableConfig['column_order'][$column_index];
            $this->db->order_by($column_name, $_POST['order'][0]['dir']);
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }
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
        return $this->db->count_all_results();
    }
    
    /**
     * Override de count_all
     */
    public function count_all($where = []) {
        $this->db->from($this->tableName);
        return $this->db->count_all_results();
    }
    
    /**
     * Obtiene un producto completo con formulación activa
     */
    public function get_producto($id) {
        $this->db->select('productos.*, categorias_productos.nombre as categoria_nombre');
        $this->db->from($this->tableName);
        $this->db->join('categorias_productos', 'categorias_productos.id = productos.categoria_id', 'left');
        $this->db->where('productos.id', $id);
        $producto = $this->db->get()->row();
        
        if($producto && $producto->tipo_producto == 'Fabricado') {
            // Obtener formulación activa
            $producto->formulacion = $this->get_formulacion_activa($id);
        }
        
        return $producto;
    }
    
    /**
     * Crea un nuevo producto
     */
    public function crear_producto($data) {
        // Generar código si no existe
        if(empty($data['codigo'])) {
            $data['codigo'] = $this->generar_codigo();
        }
        
        // Generar código de barras EAN-13 si no existe
        if(empty($data['codigo_barras'])) {
            $data['codigo_barras'] = $this->generar_codigo_barras_ean13();
        }
        
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza un producto
     */
    public function actualizar_producto($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina un producto
     */
    public function eliminar_producto($id) {
        // Verificar si tiene movimientos
        $this->db->where('producto_id', $id);
        $tiene_movimientos = $this->db->count_all_results('movimientos_productos') > 0;
        
        if($tiene_movimientos) {
            return ['success' => false, 'message' => 'No se puede eliminar: el producto tiene movimientos registrados'];
        }
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Producto eliminado' : 'Error al eliminar'];
    }
    
    /**
     * Genera código único para producto
     */
    private function generar_codigo() {
        $prefijo = 'PROD-';
        
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
        
        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Genera código de barras EAN-13 único
     * Formato: 750 (México) + 4 dígitos empresa + 5 dígitos producto + 1 dígito verificador
     */
    private function generar_codigo_barras_ean13() {
        $prefijo_pais = '750'; // México
        $codigo_empresa = '0001'; // Código de empresa (puedes personalizarlo)
        
        // Obtener siguiente número de producto
        $this->db->select('codigo_barras');
        $this->db->from($this->tableName);
        $this->db->where('codigo_barras IS NOT NULL');
        $this->db->like('codigo_barras', $prefijo_pais . $codigo_empresa, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultimo = $this->db->get()->row();
        
        if($ultimo && strlen($ultimo->codigo_barras) == 13) {
            // Extraer el número de producto (5 dígitos)
            $numero_producto = intval(substr($ultimo->codigo_barras, 7, 5)) + 1;
        } else {
            $numero_producto = 1;
        }
        
        // Formar los primeros 12 dígitos
        $codigo_sin_verificador = $prefijo_pais . $codigo_empresa . str_pad($numero_producto, 5, '0', STR_PAD_LEFT);
        
        // Calcular dígito verificador
        $digito_verificador = $this->calcular_digito_verificador_ean13($codigo_sin_verificador);
        
        return $codigo_sin_verificador . $digito_verificador;
    }
    
    /**
     * Calcula el dígito verificador para código EAN-13
     */
    private function calcular_digito_verificador_ean13($codigo) {
        $suma = 0;
        for($i = 0; $i < 12; $i++) {
            $digito = intval($codigo[$i]);
            // Posiciones impares (0, 2, 4...) se multiplican por 1
            // Posiciones pares (1, 3, 5...) se multiplican por 3
            $suma += ($i % 2 == 0) ? $digito : $digito * 3;
        }
        
        $modulo = $suma % 10;
        return ($modulo == 0) ? 0 : 10 - $modulo;
    }
    
    // =====================================================
    // GESTIÓN DE FORMULACIONES (BOM)
    // =====================================================
    
    /**
     * Obtiene la formulación activa de un producto
     */
    public function get_formulacion_activa($producto_id) {
        $this->db->where('producto_id', $producto_id);
        $this->db->where('es_activa', TRUE);
        $formulacion = $this->db->get('formulaciones')->row();
        
        if($formulacion) {
            $formulacion->componentes = $this->get_componentes_formulacion($formulacion->id);
        }
        
        return $formulacion;
    }
    
    /**
     * Obtiene todas las formulaciones de un producto (historial)
     */
    public function get_formulaciones_producto($producto_id) {
        $this->db->where('producto_id', $producto_id);
        $this->db->order_by('version', 'DESC');
        return $this->db->get('formulaciones')->result();
    }
    
    /**
     * Obtiene los componentes de una formulación
     */
    public function get_componentes_formulacion($formulacion_id) {
        $this->db->select('
            detalle_formulacion.*,
            insumos.codigo as insumo_codigo,
            insumos.nombre_tecnico as insumo_nombre,
            insumos.stock_actual as insumo_stock,
            insumos.stock_minimo as insumo_stock_minimo,
            insumos.unidad_medida as insumo_unidad_medida,
            productos.codigo as producto_codigo,
            productos.nombre as producto_nombre
        ');
        $this->db->from('detalle_formulacion');
        $this->db->join('insumos', 'insumos.id = detalle_formulacion.insumo_id', 'left');
        $this->db->join('productos', 'productos.id = detalle_formulacion.producto_id', 'left');
        $this->db->where('detalle_formulacion.formulacion_id', $formulacion_id);
        $this->db->order_by('detalle_formulacion.orden', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Crea una nueva formulación
     */
    public function crear_formulacion($data) {
        // Si es la primera formulación, es activa por defecto
        $this->db->where('producto_id', $data['producto_id']);
        $tiene_formulaciones = $this->db->count_all_results('formulaciones') > 0;
        
        if(!$tiene_formulaciones) {
            $data['es_activa'] = TRUE;
            $data['version'] = 1;
        } else {
            // Obtener siguiente versión
            $this->db->select_max('version');
            $this->db->where('producto_id', $data['producto_id']);
            $result = $this->db->get('formulaciones')->row();
            $data['version'] = ($result->version ?? 0) + 1;
        }
        
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('formulaciones', $data);
    }
    
    /**
     * Agrega un componente a la formulación
     */
    public function agregar_componente($formulacion_id, $data) {
        $data['formulacion_id'] = $formulacion_id;
        
        // Obtener costo unitario del componente
        if($data['tipo_componente'] == 'Insumo' && !empty($data['insumo_id'])) {
            $insumo = $this->db->where('id', $data['insumo_id'])->get('insumos')->row();
            $data['costo_unitario'] = $insumo->precio_promedio ?? 0;
        } elseif($data['tipo_componente'] == 'Producto' && !empty($data['producto_id'])) {
            $producto = $this->db->where('id', $data['producto_id'])->get('productos')->row();
            $data['costo_unitario'] = $producto->costo_produccion ?? 0;
        }
        
        // Guardar porcentaje si viene del formulario
        if(isset($data['porcentaje']) && $data['porcentaje'] !== '' && $data['porcentaje'] !== null) {
            $data['porcentaje'] = (float)$data['porcentaje'];
        } else {
            $data['porcentaje'] = null;
        }
        
        // El trigger calculará el costo_total automáticamente
        $result = $this->db->insert('detalle_formulacion', $data);
        
        return $result;
    }
    
    /**
     * Elimina un componente de la formulación
     */
    public function eliminar_componente($id) {
        $this->db->where('id', $id);
        return $this->db->delete('detalle_formulacion');
    }

    /**
     * Guarda una formulación completa (cabecera + componentes) de forma transaccional.
     * Soporta dos modos:
     *   - 'nueva'      : crea una nueva versión (para mantener el histórico/auditoría).
     *   - 'actualizar' : sobreescribe la versión existente ($formulacion_id).
     *
     * $componentes es un array de arrays con: tipo, item_id, cantidad, unidad,
     * porcentaje, grupo_color, porcentaje_fase_acuosa, observaciones.
     *
     * @return array ['success'=>bool, 'formulacion_id'=>int, 'message'=>string]
     */
    public function guardar_formulacion_completa($cabecera, $componentes, $modo = 'nueva', $formulacion_id = null) {
        $this->db->trans_start();

        if ($modo === 'actualizar' && $formulacion_id) {
            // No permitimos cambiar producto_id ni version al actualizar.
            $update = [
                'nombre_version'        => $cabecera['nombre_version'],
                'descripcion'           => $cabecera['descripcion'],
                'comentarios'           => $cabecera['comentarios'],
                'cliente_id'            => $cabecera['cliente_id'],
                'referencia_cliente'    => $cabecera['referencia_cliente'],
                'cantidad_producida'    => $cabecera['cantidad_producida'],
                'unidad_produccion'     => $cabecera['unidad_produccion'],
                'rendimiento_m2_por_kg' => $cabecera['rendimiento_m2_por_kg'],
                'costo_mano_obra'       => $cabecera['costo_mano_obra'],
                'costo_indirecto'       => $cabecera['costo_indirecto'],
            ];
            $this->db->where('id', $formulacion_id)->update('formulaciones', $update);
            $this->db->where('formulacion_id', $formulacion_id)->delete('detalle_formulacion');
            $fid = $formulacion_id;
        } else {
            // Nueva versión: siguiente número; la primera del producto queda activa.
            $this->db->where('producto_id', $cabecera['producto_id']);
            $tiene = $this->db->count_all_results('formulaciones') > 0;

            $this->db->select_max('version');
            $this->db->where('producto_id', $cabecera['producto_id']);
            $row = $this->db->get('formulaciones')->row();

            $cabecera['version']        = ($row->version ?? 0) + 1;
            $cabecera['es_activa']      = $tiene ? FALSE : TRUE;
            $cabecera['fecha_creacion'] = date('Y-m-d H:i:s');
            $this->db->insert('formulaciones', $cabecera);
            $fid = $this->db->insert_id();
        }

        // (Re)insertar componentes.
        $orden = 0;
        foreach ($componentes as $comp) {
            $tipo = ($comp['tipo'] === 'Producto') ? 'Producto' : 'Insumo';
            $row = [
                'formulacion_id'         => $fid,
                'tipo_componente'        => $tipo,
                'insumo_id'              => $tipo === 'Insumo'   ? ($comp['item_id'] ?: null) : null,
                'producto_id'            => $tipo === 'Producto' ? ($comp['item_id'] ?: null) : null,
                'cantidad'               => (float)($comp['cantidad'] ?? 0),
                'unidad'                 => $comp['unidad'] ?? 'Kg',
                'porcentaje'             => (isset($comp['porcentaje']) && $comp['porcentaje'] !== '' && $comp['porcentaje'] !== null) ? (float)$comp['porcentaje'] : null,
                'grupo_color'            => (!empty($comp['grupo_color'])) ? $comp['grupo_color'] : null,
                'porcentaje_fase_acuosa' => (isset($comp['porcentaje_fase_acuosa']) && $comp['porcentaje_fase_acuosa'] !== '' && $comp['porcentaje_fase_acuosa'] !== null) ? (float)$comp['porcentaje_fase_acuosa'] : null,
                'observaciones'          => $comp['observaciones'] ?? null,
                'orden'                  => $orden++,
            ];
            // Costo unitario desde el catálogo (el trigger calcula costo_total).
            if ($tipo === 'Insumo' && $row['insumo_id']) {
                $ins = $this->db->select('precio_promedio')->where('id', $row['insumo_id'])->get('insumos')->row();
                $row['costo_unitario'] = $ins->precio_promedio ?? 0;
            } elseif ($tipo === 'Producto' && $row['producto_id']) {
                $pr = $this->db->select('costo_produccion')->where('id', $row['producto_id'])->get('productos')->row();
                $row['costo_unitario'] = $pr->costo_produccion ?? 0;
            } else {
                $row['costo_unitario'] = 0;
            }
            $this->db->insert('detalle_formulacion', $row);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'formulacion_id' => null, 'message' => 'Error al guardar la formulación (transacción revertida).'];
        }
        return ['success' => true, 'formulacion_id' => $fid, 'message' => ($modo === 'actualizar' ? 'Formulación actualizada.' : 'Nueva versión guardada.')];
    }
    
    /**
     * Activa una formulación (desactiva las demás del mismo producto)
     */
    public function activar_formulacion($formulacion_id) {
        $formulacion = $this->db->where('id', $formulacion_id)->get('formulaciones')->row();
        
        if(!$formulacion) {
            return ['success' => false, 'message' => 'Formulación no encontrada'];
        }
        
        $this->db->trans_start();
        
        // Desactivar todas las formulaciones del producto
        $this->db->where('producto_id', $formulacion->producto_id);
        $this->db->update('formulaciones', ['es_activa' => FALSE]);
        
        // Activar la seleccionada
        $this->db->where('id', $formulacion_id);
        $this->db->update('formulaciones', [
            'es_activa' => TRUE,
            'fecha_activacion' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error al activar formulación'];
        }
        
        return ['success' => true, 'message' => 'Formulación activada correctamente'];
    }
    
    // =====================================================
    // MOVIMIENTOS DE PRODUCTOS
    // =====================================================
    
    /**
     * Registra un movimiento de producto (entrada/salida)
     */
    public function registrar_movimiento($data) {
        // Obtener stock actual
        $producto = $this->db->where('id', $data['producto_id'])->get('productos')->row();
        
        if(!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }
        
        $data['stock_anterior'] = $producto->stock_actual;
        
        // Calcular nuevo stock según tipo de movimiento
        if(in_array($data['tipo_movimiento'], ['Entrada', 'Produccion', 'Devolucion'])) {
            $data['stock_nuevo'] = $data['stock_anterior'] + $data['cantidad'];
        } else { // Salida, Venta, Ajuste negativo
            $data['stock_nuevo'] = $data['stock_anterior'] - $data['cantidad'];
        }
        
        $data['fecha_movimiento'] = date('Y-m-d H:i:s');
        
        $result = $this->db->insert('movimientos_productos', $data);
        
        // El trigger actualiza el stock automáticamente
        
        return ['success' => $result, 'message' => $result ? 'Movimiento registrado' : 'Error al registrar'];
    }
    
    /**
     * Registra salida escaneando código de barras/QR
     */
    public function registrar_salida_escaneo($codigo, $cantidad, $user_id) {
        // Buscar producto por código de barras, QR o SKU
        $this->db->group_start();
        $this->db->where('codigo_barras', $codigo);
        $this->db->or_where('codigo_qr', $codigo);
        $this->db->or_where('sku', $codigo);
        $this->db->group_end();
        $producto = $this->db->get('productos')->row();
        
        if(!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado con ese código'];
        }
        
        return $this->registrar_movimiento([
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'Salida',
            'cantidad' => $cantidad,
            'motivo' => 'Salida por escaneo de código',
            'escaneado_barras' => TRUE,
            'codigo_escaneado' => $codigo,
            'usuario_id' => $user_id
        ]);
    }
    
    // =====================================================
    // ALERTAS Y ESTADÍSTICAS
    // =====================================================
    
    /**
     * Obtiene estadísticas de productos
     */
    /**
     * Obtiene estadísticas de productos para los cards
     */
    public function get_estadisticas() {
        $stats = [];
        
        // 1. Total de Productos y Porcentaje Activos
        $total_query = $this->db->get($this->tableName);
        $stats['total_products'] = $total_query->num_rows();
        
        $this->db->where('estatus', 'Activo');
        $stats['active_products'] = $this->db->count_all_results($this->tableName);
        
        $stats['inactive_products'] = $stats['total_products'] - $stats['active_products'];
        
        $stats['active_percentage'] = ($stats['total_products'] > 0) 
            ? round(($stats['active_products'] / $stats['total_products']) * 100) 
            : 0;
            
        // 2. Nuevos Productos (últimos 30 días)
        $fecha_limite = date('Y-m-d', strtotime('-30 days'));
        $this->db->where('fecha_creacion >=', $fecha_limite);
        $stats['new_products_30days'] = $this->db->count_all_results($this->tableName);
        
        // Crecimiento (comparado com previo a 30 días)
        $this->db->where('fecha_creacion <', $fecha_limite);
        $previous_total = $this->db->count_all_results($this->tableName);
        
        $stats['growth_percentage'] = ($previous_total > 0)
            ? round(($stats['new_products_30days'] / $previous_total) * 100)
            : 100;
            
        // 3. Productos Fabricados vs Reventa
        $this->db->where('tipo_producto', 'Fabricado');
        $stats['manufactured_products'] = $this->db->count_all_results($this->tableName);
        
        // 4. Stock Bajo (Alerta)
        $this->db->where('stock_actual <=', 'stock_minimo', FALSE);
        $this->db->where('estatus', 'Activo');
        $stats['low_stock_products'] = $this->db->count_all_results($this->tableName);
        
        return $stats;
    }
    
    /**
     * Obtiene alertas activas de productos
     */
    public function get_alertas_productos() {
        $this->db->select('alertas_stock.*, productos.nombre as producto_nombre');
        $this->db->from('alertas_stock');
        $this->db->join('productos', 'productos.id = alertas_stock.producto_id', 'left');
        $this->db->where('alertas_stock.tipo_alerta', 'Producto');
        $this->db->where('alertas_stock.resuelta', FALSE);
        $this->db->order_by('alertas_stock.nivel_alerta', 'DESC');
        $this->db->order_by('alertas_stock.fecha_creacion', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Verifica y crea alertas de stock bajo
     */
    public function verificar_alertas_stock() {
        // Llamar al stored procedure
        $this->db->query('CALL sp_verificar_stock_productos()');
        $this->db->query('CALL sp_verificar_insumos_formulaciones()');
    }
    
    /**
     * Obtiene categorías para select
     */
    public function get_categorias_select() {
        $this->db->select('id, nombre');
        $this->db->where('estatus', 'Activa');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get('categorias_productos')->result();
    }
    
    /**
     * Obtiene historial de formulaciones de un producto con búsqueda y filtros opcionales
     */
    public function get_historial_formulaciones($producto_id, $busqueda = null, $cliente_id = null, $fecha_inicio = null, $fecha_fin = null) {
        $this->db->select('formulaciones.*, administradores.nombre as creador_nombre, administradores.apellidos as creador_apellidos, clientes.razon_social as cliente_nombre, clientes.nombre_comercial');
        $this->db->from('formulaciones');
        $this->db->join('administradores', 'administradores.id = formulaciones.usuario_creacion', 'left');
        $this->db->join('clientes', 'clientes.id = formulaciones.cliente_id', 'left');
        $this->db->where('formulaciones.producto_id', $producto_id);
        
        // Filtro por cliente
        if($cliente_id !== null && $cliente_id !== '') {
            $this->db->where('formulaciones.cliente_id', $cliente_id);
        }
        
        // Rango de fechas
        if($fecha_inicio) {
            $this->db->where('DATE(formulaciones.fecha_creacion) >=', $fecha_inicio);
        }
        if($fecha_fin) {
            $this->db->where('DATE(formulaciones.fecha_creacion) <=', $fecha_fin);
        }
        
        // Búsqueda avanzada por tokens sobre versión, descripción, comentarios,
        // referencia de cliente y datos del cliente.
        $this->_aplicar_busqueda_tokens($busqueda, [
            'formulaciones.nombre_version',
            'formulaciones.descripcion',
            'formulaciones.comentarios',
            'formulaciones.referencia_cliente',
            'clientes.razon_social',
            'clientes.nombre_comercial',
        ]);
        
        $this->db->order_by('formulaciones.version', 'DESC');
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene una formulación completa con todos sus componentes y el nombre del cliente
     */
    public function get_formulacion_completa($formulacion_id) {
        // Obtener datos de la formulación
        $this->db->select('formulaciones.*, clientes.razon_social as cliente_nombre');
        $this->db->from('formulaciones');
        $this->db->join('clientes', 'clientes.id = formulaciones.cliente_id', 'left');
        $this->db->where('formulaciones.id', $formulacion_id);
        $formulacion = $this->db->get()->row();
        
        if($formulacion) {
            // Obtener componentes con nombres completos
            $formulacion->componentes = $this->get_componentes_formulacion($formulacion_id);
        }
        
        return $formulacion;
    }

    // =====================================================
    // BOM EXPLOSION - CÁLCULO MULTINIVEL DE INSUMOS
    // =====================================================

    /**
     * Explosión de BOM (Bill of Materials) multinivel.
     * Devuelve un árbol con los componentes de la formulación.
     * Los insumos de tipo 'fabricado' se expanden mostrando sus sub-componentes.
     *
     * @param int   $formulacion_id  ID de la formulación a explotar
     * @param float $cantidad_total  Cantidad en kg del lote a producir
     * @param int   $nivel           Nivel de anidamiento (0=raíz)
     * @param array $visitados       IDs de formulaciones ya visitadas (anti-ciclo)
     * @return array  Árbol de componentes
     */
    public function explotar_bom_arbol($formulacion_id, $cantidad_total, $nivel = 0, &$visitados = []) {
        if ($nivel > 6 || in_array($formulacion_id, $visitados)) {
            return [];
        }
        $visitados[] = $formulacion_id;

        $this->db->select('
            df.*,
            i.codigo       AS insumo_codigo,
            i.nombre_tecnico AS insumo_nombre,
            i.alias        AS insumo_alias,
            i.stock_actual AS insumo_stock,
            i.unidad_medida AS insumo_unidad,
            i.tipo         AS insumo_tipo,
            i.producto_id  AS insumo_producto_id,
            p.codigo       AS prod_codigo,
            p.nombre       AS prod_nombre
        ');
        $this->db->from('detalle_formulacion df');
        $this->db->join('insumos i',   'i.id = df.insumo_id',    'left');
        $this->db->join('productos p', 'p.id = df.producto_id',  'left');
        $this->db->where('df.formulacion_id', $formulacion_id);
        $this->db->order_by('df.grupo_color ASC, df.orden ASC');
        $componentes = $this->db->get()->result();

        $arbol = [];
        foreach ($componentes as $comp) {
            // porcentaje puede ser NULL (datos legacy sin %); en ese caso escalar por cantidad base
            $pct = (isset($comp->porcentaje) && $comp->porcentaje !== null && $comp->porcentaje > 0)
                ? (float)$comp->porcentaje : 0;
            if ($pct > 0) {
                $kg_comp = round(($pct / 100) * $cantidad_total, 6);
            } else {
                // Sin porcentaje: la cantidad es absoluta (kg para el lote de referencia)
                // Escalamos proporcionalmente respecto al lote base de la formulación
                $kg_comp = (float)$comp->cantidad;
            }

            $nodo = [
                'id'          => $comp->id,
                'nivel'       => $nivel,
                'tipo_comp'   => $comp->tipo_componente,
                'nombre'      => $comp->tipo_componente === 'Insumo'
                                    ? ($comp->insumo_alias ?: $comp->insumo_nombre)
                                    : $comp->prod_nombre,
                'nombre_tecnico' => $comp->insumo_nombre,
                'codigo'      => $comp->insumo_codigo ?: $comp->prod_codigo,
                'porcentaje'  => (float)$comp->porcentaje,
                'kg'          => $kg_comp,
                'grupo'       => $comp->grupo_color,
                'unidad'      => $comp->insumo_unidad ?: 'Kg',
                'stock'       => (float)$comp->insumo_stock,
                'tipo_insumo' => $comp->insumo_tipo ?: 'comprado',
                'pct_fase_acuosa' => (float)$comp->porcentaje_fase_acuosa,
                'insumo_id'   => $comp->insumo_id,
                'producto_id_comp' => $comp->producto_id,
                'sub_componentes' => [],
                'es_fabricado' => false,
            ];

            // Si el insumo es fabricado y tiene un producto ligado → explotar
            if ($comp->tipo_componente === 'Insumo'
                && $comp->insumo_tipo === 'fabricado'
                && !empty($comp->insumo_producto_id))
            {
                $formulacion_sub = $this->get_formulacion_activa($comp->insumo_producto_id);
                if ($formulacion_sub) {
                    $nodo['es_fabricado']    = true;
                    $nodo['formulacion_sub_id'] = $formulacion_sub->id;
                    $visitados_copia = $visitados; // no contaminar ramas hermanas
                    $nodo['sub_componentes'] = $this->explotar_bom_arbol(
                        $formulacion_sub->id, $kg_comp, $nivel + 1, $visitados_copia
                    );
                }
            }

            $arbol[] = $nodo;
        }

        // Restaurar para que el padre pueda explotar otras ramas
        $visitados = array_values(array_diff($visitados, [$formulacion_id]));
        return $arbol;
    }

    /**
     * Versión plana del BOM explosion: retorna sólo las materias primas reales
     * (sin sub-productos fabricados intermedios), acumulando cantidades.
     */
    public function explotar_bom_plano($formulacion_id, $cantidad_total, $nivel = 0, &$visitados = []) {
        $arbol = $this->explotar_bom_arbol($formulacion_id, $cantidad_total, $nivel, $visitados);
        $lista = [];
        $this->_aplanar_bom($arbol, $lista);

        // Acumular por insumo_id
        $acumulado = [];
        foreach ($lista as $item) {
            $key = 'i_' . ($item['insumo_id'] ?: 'p_' . $item['producto_id_comp']);
            if (!isset($acumulado[$key])) {
                $acumulado[$key] = $item;
            } else {
                $acumulado[$key]['kg'] += $item['kg'];
            }
        }

        return array_values($acumulado);
    }

    /** Recursivo interno para aplanar el árbol BOM */
    private function _aplanar_bom($arbol, &$lista) {
        foreach ($arbol as $nodo) {
            if (!$nodo['es_fabricado']) {
                $lista[] = $nodo; // hoja = materia prima real
            } else {
                // Sub-producto fabricado: incluir la rama de sub-componentes
                $this->_aplanar_bom($nodo['sub_componentes'], $lista);
            }
        }
    }

    /**
     * Busca formulaciones activas por nombre de producto o referencia
     */
    public function buscar_formulaciones($termino, $limite = 30) {
        $this->db->select('
            f.id, f.version, f.nombre_version, f.cantidad_producida, f.unidad_produccion,
            f.referencia_cliente, f.es_activa, f.fecha_creacion,
            p.id AS producto_id, p.nombre AS producto_nombre, p.codigo AS producto_codigo,
            p.imagen AS producto_imagen,
            c.razon_social AS cliente_nombre
        ');
        $this->db->from('formulaciones f');
        $this->db->join('productos p', 'p.id = f.producto_id', 'left');
        $this->db->join('clientes c', 'c.id = f.cliente_id', 'left');

        // Búsqueda avanzada por tokens: cada palabra debe aparecer en ALGÚN campo.
        // Campos: nombre/código/alias del producto, versión, referencia de cliente,
        // comentarios, descripción y datos del cliente. Permite buscar formulaciones
        // de clientes antiguos por nombre parcial o nombres secundarios.
        $campos = [
            'p.nombre', 'p.codigo', 'p.alias',
            'f.nombre_version', 'f.referencia_cliente', 'f.comentarios', 'f.descripcion',
            'c.razon_social', 'c.nombre_comercial',
        ];
        $this->_aplicar_busqueda_tokens($termino, $campos);

        $this->db->order_by('f.es_activa DESC, f.fecha_creacion DESC');
        $this->db->limit($limite);
        return $this->db->get()->result();
    }

    /**
     * Aplica una búsqueda por tokens sobre el query builder actual: divide el término
     * en palabras y exige que CADA palabra aparezca (LIKE) en al menos uno de los campos.
     */
    private function _aplicar_busqueda_tokens($termino, array $campos) {
        $termino = trim((string)$termino);
        if ($termino === '') {
            return;
        }
        $tokens = preg_split('/\s+/', $termino);
        foreach ($tokens as $tok) {
            $tok = trim($tok);
            if ($tok === '') {
                continue;
            }
            $this->db->group_start();
            $primero = true;
            foreach ($campos as $campo) {
                if ($primero) {
                    $this->db->like($campo, $tok);
                    $primero = false;
                } else {
                    $this->db->or_like($campo, $tok);
                }
            }
            $this->db->group_end();
        }
    }

    /**
     * Obtiene catálogo de productos para el panel touchscreen con filtros
     */
    public function get_catalogo_touchscreen($categoria_id = null, $termino = null, $limite = 50) {
        $this->db->select('
            p.*,
            cp.nombre AS categoria_nombre,
            f.id AS formulacion_id,
            f.nombre_version,
            f.cantidad_producida,
            (SELECT COUNT(*) FROM formulaciones WHERE producto_id = p.id) AS num_formulaciones
        ');
        $this->db->from('productos p');
        $this->db->join('categorias_productos cp', 'cp.id = p.categoria_id', 'left');
        $this->db->join('formulaciones f', 'f.producto_id = p.id AND f.es_activa = 1', 'left');
        $this->db->where('p.estatus', 'Activo');

        if ($categoria_id) {
            $this->db->where('p.categoria_id', $categoria_id);
        }
        if ($termino && strlen(trim($termino)) > 1) {
            $this->db->group_start();
            $this->db->like('p.nombre', $termino);
            $this->db->or_like('p.codigo', $termino);
            $this->db->or_like('p.alias', $termino);
            $this->db->group_end();
        }

        $this->db->order_by('p.nombre', 'ASC');
        $this->db->limit($limite);
        return $this->db->get()->result();
    }

    /**
     * Obtiene historial de órdenes completadas para producción.
     * Columnas reales: ordenes_venta usa 'fecha_entrega_real', obras usa 'fecha_fin_real'.
     * Estatus reales: ordenes_venta → 'Entregada'; obras → 'Completada'.
     */
    public function get_historial_ordenes_produccion($filtros = []) {
        $limite   = $filtros['limite']   ?? 50;
        $busqueda = $filtros['busqueda'] ?? '';

        // Órdenes de venta entregadas (el estatus final en ordenes_venta es 'Entregada')
        $this->db->select('
            ov.id,
            ov.folio,
            ov.estatus,
            ov.fecha_creacion,
            ov.fecha_entrega_real AS fecha_completado_produccion,
            c.razon_social AS cliente,
            "orden_venta" AS tipo_registro,
            COUNT(dov.id) AS total_productos
        ');
        $this->db->from('ordenes_venta ov');
        $this->db->join('clientes c', 'c.id = ov.cliente_id', 'left');
        $this->db->join('detalle_orden_venta dov', 'dov.orden_venta_id = ov.id', 'left');
        $this->db->where_in('ov.estatus', ['Entregada', 'Cancelada']);
        if ($busqueda) {
            $this->db->group_start();
            $this->db->like('ov.folio', $busqueda);
            $this->db->or_like('c.razon_social', $busqueda);
            $this->db->group_end();
        }
        $this->db->group_by('ov.id');
        $this->db->order_by('ov.fecha_creacion', 'DESC');
        $this->db->limit($limite);
        $ventas = $this->db->get()->result();

        // Obras completadas (estatus real: 'Completada' o 'Cancelada')
        $this->db->select('
            o.id,
            o.folio,
            o.estatus,
            o.fecha_creacion,
            o.fecha_fin_real AS fecha_completado_produccion,
            c.razon_social AS cliente,
            "obra" AS tipo_registro,
            COUNT(op.id) AS total_productos
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->join('obras_productos op', 'op.obra_id = o.id', 'left');
        $this->db->where_in('o.estatus', ['Completada', 'Cancelada']);
        if ($busqueda) {
            $this->db->group_start();
            $this->db->like('o.folio', $busqueda);
            $this->db->or_like('c.razon_social', $busqueda);
            $this->db->group_end();
        }
        $this->db->group_by('o.id');
        $this->db->order_by('o.fecha_creacion', 'DESC');
        $this->db->limit($limite);
        $obras = $this->db->get()->result();

        $todos = array_merge($ventas, $obras);
        usort($todos, function($a, $b) {
            return strtotime($b->fecha_creacion) - strtotime($a->fecha_creacion);
        });
        return array_slice($todos, 0, $limite);
    }

    /**
     * Calcula y escala los insumos requeridos para un proyecto basado en cubetas o m²
     */
    public function calcular_insumos_para_proyecto($formulacion_id, $cubetas = null, $m2 = null) {
        $formulacion = $this->get_formulacion_completa($formulacion_id);
        if(!$formulacion) {
            return null;
        }

        $rendimiento = !empty($formulacion->rendimiento_m2_por_kg) ? (float)$formulacion->rendimiento_m2_por_kg : null;
        if($rendimiento === null) {
            // Buscar rendimiento global del producto
            $producto = $this->db->select('rendimiento')->where('id', $formulacion->producto_id)->get('productos')->row();
            $rendimiento = $producto && !empty($producto->rendimiento) ? (float)$producto->rendimiento : 1.0; // Evitar división por cero
        }

        // Determinar multiplicador de escala
        $total_kg_necesarios = 0.0;
        $total_cubetas = 0.0;
        
        if($m2 !== null && $m2 !== '' && $m2 > 0) {
            // kg = m² / rendimiento (m²/kg)
            $total_kg_necesarios = (float)$m2 / $rendimiento;
            // cubetas = kg / cantidad_producida
            $cantidad_base = !empty($formulacion->cantidad_producida) ? (float)$formulacion->cantidad_producida : 27.0;
            $total_cubetas = ceil($total_kg_necesarios / $cantidad_base);
            $multiplicador = $total_cubetas; // Escalar por cubetas enteras requeridas
        } elseif($cubetas !== null && $cubetas !== '' && $cubetas > 0) {
            $total_cubetas = (float)$cubetas;
            $cantidad_base = !empty($formulacion->cantidad_producida) ? (float)$formulacion->cantidad_producida : 27.0;
            $total_kg_necesarios = $total_cubetas * $cantidad_base;
            $multiplicador = $total_cubetas;
        } else {
            $total_cubetas = 1.0;
            $cantidad_base = !empty($formulacion->cantidad_producida) ? (float)$formulacion->cantidad_producida : 27.0;
            $total_kg_necesarios = $cantidad_base;
            $multiplicador = 1.0;
        }

        // Escalar componentes
        $componentes_escalados = [];
        $insumos_faltantes = [];
        $hay_insumos_faltantes = false;
        $hay_insumos_revision_manual = false;

        foreach($formulacion->componentes as $comp) {
            $comp_escalado = clone $comp;
            $comp_escalado->cantidad_original = $comp->cantidad;
            $comp_escalado->cantidad_escalada = (float)$comp->cantidad * $multiplicador;
            $comp_escalado->costo_total_escalado = (float)$comp->costo_unitario * $comp_escalado->cantidad_escalada;
            
            // Cálculos de fase acuosa
            if(!empty($comp->porcentaje_fase_acuosa)) {
                $comp_escalado->kg_fase_acuosa_escalado = $comp_escalado->cantidad_escalada * ((float)$comp->porcentaje_fase_acuosa / 100);
            } else {
                $comp_escalado->kg_fase_acuosa_escalado = null;
            }

            // ── Validación de stock vs. faltante (SOLO para componentes tipo Insumo) ──
            // IMPORTANTE (hallazgo crítico): detalle_formulacion.unidad (unidad de la
            // fórmula) puede NO coincidir con insumos.unidad_medida (unidad de compra/
            // stock). Nunca se asume una conversión; solo se compara cuando es segura.
            $comp_escalado->unidad_coincide         = null;
            $comp_escalado->conversion_aplicada     = false;
            $comp_escalado->factor_conversion       = null;
            $comp_escalado->cantidad_en_unidad_insumo = null;
            $comp_escalado->insumo_faltante         = null;
            $comp_escalado->cantidad_faltante       = null;
            $comp_escalado->requiere_revision_manual = false;
            $comp_escalado->motivo_revision         = null;

            if ($comp->tipo_componente === 'Insumo' && !empty($comp->insumo_id)) {
                $unidad_formula = $comp->unidad;
                $unidad_insumo  = $comp->insumo_unidad_medida;

                $conv = convertir_unidad_insumo($comp_escalado->cantidad_escalada, $unidad_formula, $unidad_insumo);

                $comp_escalado->unidad_coincide     = $conv['unidad_coincide'];
                $comp_escalado->conversion_aplicada = $conv['success'] && !$conv['unidad_coincide'];
                $comp_escalado->factor_conversion   = $conv['factor'];

                if ($conv['success']) {
                    $stock_disponible = (float) ($comp->insumo_stock ?? 0);
                    $cantidad_necesaria = $conv['cantidad_convertida'];

                    $comp_escalado->cantidad_en_unidad_insumo = $cantidad_necesaria;
                    $comp_escalado->insumo_faltante = $cantidad_necesaria > $stock_disponible;
                    $comp_escalado->cantidad_faltante = $comp_escalado->insumo_faltante
                        ? round($cantidad_necesaria - $stock_disponible, 6)
                        : 0;

                    if ($comp_escalado->insumo_faltante) {
                        $hay_insumos_faltantes = true;
                        $insumos_faltantes[] = [
                            'detalle_formulacion_id' => $comp->id,
                            'insumo_id'              => $comp->insumo_id,
                            'insumo_codigo'          => $comp->insumo_codigo,
                            'insumo_nombre'          => $comp->insumo_nombre,
                            'unidad_insumo'          => $unidad_insumo,
                            'cantidad_requerida'     => $cantidad_necesaria,
                            'stock_disponible'       => $stock_disponible,
                            'cantidad_faltante'      => $comp_escalado->cantidad_faltante,
                        ];
                    }
                } else {
                    // No se pudo comparar de forma segura: NO se marca como faltante,
                    // se señala para revisión manual del usuario/administrador.
                    $comp_escalado->requiere_revision_manual = true;
                    $comp_escalado->motivo_revision = $conv['motivo'];
                    $hay_insumos_revision_manual = true;
                }
            }

            $componentes_escalados[] = $comp_escalado;
        }

        return [
            'formulacion' => $formulacion,
            'cubetas_calculadas' => $total_cubetas,
            'kg_necesarios' => $total_kg_necesarios,
            'multiplicador' => $multiplicador,
            'componentes' => $componentes_escalados,
            'hay_insumos_faltantes' => $hay_insumos_faltantes,
            'hay_insumos_revision_manual' => $hay_insumos_revision_manual,
            'insumos_faltantes' => $insumos_faltantes,
        ];
    }

    // =====================================================
    // VERIFICACIÓN DE INSUMOS PARA VENTAS / OBRAS (P5)
    // =====================================================

    /**
     * Obtiene las líneas fabricadas con formulación de una orden de venta u obra.
     *
     * @param int    $origen_id
     * @param string $tipo 'venta' | 'obra'
     * @return array{lineas: array, sin_formulacion: bool}
     */
    private function _obtener_lineas_fabricadas_para_verificacion($origen_id, $tipo = 'venta') {
        $sin_formulacion = false;

        if ($tipo === 'obra') {
            $this->db->select('
                op.producto_id,
                COALESCE(op.cantidad_ajustada, op.cantidad_calculada, 0) AS cantidad,
                op.formulacion_id,
                op.unidad AS unidad_linea,
                p.unidad_venta,
                p.nombre AS producto_nombre,
                p.codigo AS producto_codigo,
                p.tipo_producto
            ');
            $this->db->from('obras_productos op');
            $this->db->join('productos p', 'p.id = op.producto_id');
            $this->db->where('op.obra_id', (int) $origen_id);
            $this->db->where('p.tipo_producto', 'Fabricado');
            $lineas = $this->db->get()->result();
        } else {
            $this->db->select('
                dov.producto_id,
                dov.cantidad,
                dov.formulacion_id,
                p.unidad_venta,
                p.nombre AS producto_nombre,
                p.codigo AS producto_codigo,
                p.tipo_producto
            ');
            $this->db->from('detalle_orden_venta dov');
            $this->db->join('productos p', 'p.id = dov.producto_id');
            $this->db->where('dov.orden_venta_id', (int) $origen_id);
            $this->db->where('p.tipo_producto', 'Fabricado');
            $lineas = $this->db->get()->result();
        }

        $lineas_validas = [];
        foreach ($lineas as $linea) {
            $cantidad = (float) $linea->cantidad;
            if ($cantidad <= 0) {
                continue;
            }

            $formulacion_id = !empty($linea->formulacion_id) ? (int) $linea->formulacion_id : null;
            if (!$formulacion_id) {
                $form_activa = $this->get_formulacion_activa($linea->producto_id);
                if (!$form_activa) {
                    $sin_formulacion = true;
                    continue;
                }
                $formulacion_id = (int) $form_activa->id;
            }

            $lineas_validas[] = (object) [
                'producto_id'      => (int) $linea->producto_id,
                'producto_nombre'  => $linea->producto_nombre,
                'producto_codigo'  => $linea->producto_codigo,
                'cantidad'         => $cantidad,
                'formulacion_id'   => $formulacion_id,
                'unidad_linea'     => $linea->unidad_linea ?? null,
                'unidad_producto'  => $linea->unidad_venta ?? null,
            ];
        }

        return [
            'lineas'          => $lineas_validas,
            'sin_formulacion' => $sin_formulacion,
        ];
    }

    /**
     * Normaliza unidades del catálogo (productos.unidad_venta, obras_productos.unidad).
     */
    private function _canonical_unidad_linea($unidad) {
        $u = mb_strtolower(trim((string) $unidad), 'UTF-8');
        $map = [
            'litro'  => 'L',
            'l'      => 'L',
            'kg'     => 'Kg',
            'kilogramo' => 'Kg',
            'g'      => 'g',
            'mg'     => 'mg',
            'ml'     => 'ml',
            'pieza'  => 'Pza',
            'pza'    => 'Pza',
            'cubeta' => 'Cubeta',
            'caja'   => 'Pza',
        ];

        return $map[$u] ?? trim((string) $unidad);
    }

    /**
     * Unidades de contenedor/embalaje: cantidad × cantidad_producida del lote.
     */
    private function _es_unidad_conteo_envase($unidad) {
        $u = mb_strtolower(trim((string) $unidad), 'UTF-8');
        return in_array($u, ['cubeta', 'pza', 'pieza', 'caja'], true);
    }

    /**
     * Unidades que no permiten escalar el BOM de forma segura.
     */
    private function _es_unidad_ambigua_escalado($unidad) {
        $u = mb_strtolower(trim((string) $unidad), 'UTF-8');
        return in_array($u, ['galon', 'galón', 'metro', 'm2', 'm²', 'servicio', 'otro', 'ton', 'tambo'], true);
    }

    /**
     * Resuelve el total del lote para explotar_bom_plano según unidad de línea vs formulación.
     *
     * Reglas:
     *  - Cubeta/Pza/Caja → total = cantidad × cantidad_producida (lote)
     *  - Kg/L (y g/ml)   → total = cantidad en unidad del lote (sin multiplicar por cantidad_producida)
     *  - Ambiguo         → success=false (revision_manual, sin preorden)
     *
     * @return array{success:bool, kg:float, motivo:?string}
     */
    private function _resolver_total_bom_linea($cantidad, $unidad_linea, $unidad_producto, $formulacion_id) {
        $this->load->helper('unidades');

        $formulacion = $this->db->select('id, cantidad_producida, unidad_produccion')
            ->where('id', (int) $formulacion_id)
            ->get('formulaciones')
            ->row();

        if (!$formulacion) {
            return ['success' => false, 'kg' => 0.0, 'motivo' => 'Formulación no encontrada'];
        }

        $cantidad = (float) $cantidad;
        if ($cantidad <= 0) {
            return ['success' => false, 'kg' => 0.0, 'motivo' => 'Cantidad de línea inválida'];
        }

        $unidad_efectiva = $this->_canonical_unidad_linea($unidad_linea ?: $unidad_producto ?: $formulacion->unidad_produccion ?: 'Kg');
        $unidad_prod     = $this->_canonical_unidad_linea($formulacion->unidad_produccion ?: 'Kg');
        $cantidad_prod   = (float) ($formulacion->cantidad_producida ?: 0);

        if ($this->_es_unidad_ambigua_escalado($unidad_efectiva)) {
            return [
                'success' => false,
                'kg'      => 0.0,
                'motivo'  => "Unidad de línea \"{$unidad_efectiva}\" requiere revisión manual para escalar el BOM.",
            ];
        }

        // Venta por contenedor (cubeta, pieza, caja)
        if ($this->_es_unidad_conteo_envase($unidad_efectiva)) {
            if ($cantidad_prod <= 0) {
                return [
                    'success' => false,
                    'kg'      => 0.0,
                    'motivo'  => "La formulación no define cantidad_producida para escalar por {$unidad_efectiva}.",
                ];
            }

            $familia_prod = unidad_familia($unidad_prod);
            if ($familia_prod === 'masa' || $familia_prod === 'volumen') {
                return ['success' => true, 'kg' => $cantidad * $cantidad_prod];
            }

            return [
                'success' => false,
                'kg'      => 0.0,
                'motivo'  => "Unidad de producción \"{$unidad_prod}\" no compatible con venta por {$unidad_efectiva}.",
            ];
        }

        // Venta directa por masa (Kg, g, …)
        $familia_linea = unidad_familia($unidad_efectiva);
        if ($familia_linea === 'masa') {
            if ($cantidad_prod > 0 && unidades_son_compatibles($unidad_efectiva, $unidad_prod)) {
                $conv_lote = convertir_unidad_insumo($cantidad_prod, $unidad_prod, $unidad_efectiva);
                if ($conv_lote['success'] && abs($cantidad - $conv_lote['cantidad_convertida']) < 0.0001) {
                    // Ej.: 19 Kg vendidos de un lote nominal de 19 Kg → 19 kg BOM (no 361)
                    $conv_kg = convertir_unidad_insumo($cantidad, $unidad_efectiva, 'Kg');
                    if ($conv_kg['success']) {
                        return ['success' => true, 'kg' => $conv_kg['cantidad_convertida']];
                    }
                }
            }

            $conv = convertir_unidad_insumo($cantidad, $unidad_efectiva, 'Kg');
            if ($conv['success']) {
                return ['success' => true, 'kg' => $conv['cantidad_convertida']];
            }
            return ['success' => false, 'kg' => 0.0, 'motivo' => $conv['motivo']];
        }

        // Venta directa por volumen (L, ml, …)
        if ($familia_linea === 'volumen') {
            if ($cantidad_prod > 0 && unidades_son_compatibles($unidad_efectiva, $unidad_prod)) {
                $conv_lote = convertir_unidad_insumo($cantidad_prod, $unidad_prod, $unidad_efectiva);
                if ($conv_lote['success'] && abs($cantidad - $conv_lote['cantidad_convertida']) < 0.0001) {
                    return ['success' => true, 'kg' => $cantidad];
                }
            }

            if (unidades_son_compatibles($unidad_efectiva, $unidad_prod)) {
                $conv = convertir_unidad_insumo($cantidad, $unidad_efectiva, $unidad_prod);
                if ($conv['success']) {
                    return ['success' => true, 'kg' => $conv['cantidad_convertida']];
                }
            }

            return ['success' => true, 'kg' => $cantidad];
        }

        return [
            'success' => false,
            'kg'      => 0.0,
            'motivo'  => "Unidad \"{$unidad_efectiva}\" no permite escalar el BOM automáticamente.",
        ];
    }

    /**
     * Verifica insumos teóricos para una línea única de producción (orden_produccion legacy).
     * Reutiliza explotar_bom_plano + _resolver_total_bom_linea (mismas reglas P5.1).
     */
    public function verificar_insumos_linea_produccion($formulacion_id, $cantidad, $unidad_linea, $producto_id) {
        $this->load->helper('unidades');

        $producto = $this->db->select('nombre, codigo, unidad_venta')
            ->where('id', (int) $producto_id)
            ->get('productos')
            ->row();

        $linea = (object) [
            'cantidad'        => (float) $cantidad,
            'unidad_linea'    => $unidad_linea,
            'unidad_producto' => $producto->unidad_venta ?? null,
            'formulacion_id'  => (int) $formulacion_id,
            'producto_id'     => (int) $producto_id,
            'producto_nombre' => $producto->nombre ?? '',
            'producto_codigo' => $producto->codigo ?? '',
        ];

        $revision_manual = [];
        $mapa_insumos = [];

        $escalado = $this->_resolver_total_bom_linea(
            $linea->cantidad,
            $linea->unidad_linea ?? null,
            $linea->unidad_producto ?? null,
            $linea->formulacion_id
        );

        if (!$escalado['success']) {
            return [
                'ok'              => false,
                'faltantes'       => [],
                'suficientes'     => [],
                'revision_manual' => [[
                    'tipo'            => 'linea',
                    'producto_id'     => $linea->producto_id,
                    'producto_nombre' => $linea->producto_nombre,
                    'producto_codigo' => $linea->producto_codigo,
                    'motivo_revision' => $escalado['motivo'],
                ]],
                'sin_formulacion' => false,
            ];
        }

        $total_kg = (float) $escalado['kg'];
        if ($total_kg > 0) {
            $visitados = [];
            $bom_plano = $this->explotar_bom_plano($linea->formulacion_id, $total_kg, 0, $visitados);

            foreach ($bom_plano as $item) {
                if (empty($item['insumo_id'])) {
                    continue;
                }

                $insumo_id = (int) $item['insumo_id'];
                $kg_requeridos = (float) ($item['kg'] ?? 0);
                if ($kg_requeridos <= 0) {
                    continue;
                }

                if (!isset($mapa_insumos[$insumo_id])) {
                    $insumo_row = $this->db->select('id, codigo, nombre_tecnico, stock_actual, unidad_medida, precio_promedio')
                        ->where('id', $insumo_id)
                        ->get('insumos')
                        ->row();

                    if (!$insumo_row) {
                        continue;
                    }

                    $mapa_insumos[$insumo_id] = [
                        'insumo_id'          => $insumo_id,
                        'insumo_codigo'      => $insumo_row->codigo,
                        'insumo_nombre'      => $insumo_row->nombre_tecnico,
                        'unidad_insumo'      => $insumo_row->unidad_medida,
                        'unidad_formula'     => 'Kg',
                        'cantidad_requerida' => 0.0,
                        'stock_disponible'   => (float) $insumo_row->stock_actual,
                        'precio_promedio'    => (float) $insumo_row->precio_promedio,
                    ];
                }

                $mapa_insumos[$insumo_id]['cantidad_requerida'] += $kg_requeridos;
            }
        }

        $faltantes = [];
        $suficientes = [];

        foreach ($mapa_insumos as $datos) {
            $conv = convertir_unidad_insumo($datos['cantidad_requerida'], 'Kg', $datos['unidad_insumo']);

            if (!$conv['success']) {
                $revision_manual[] = array_merge($datos, ['motivo_revision' => $conv['motivo']]);
                continue;
            }

            $cantidad_en_unidad_insumo = (float) $conv['cantidad_convertida'];
            $cantidad_faltante = max(0, round($cantidad_en_unidad_insumo - $datos['stock_disponible'], 6));

            $item = array_merge($datos, [
                'cantidad_en_unidad_insumo' => $cantidad_en_unidad_insumo,
                'cantidad_faltante'         => $cantidad_faltante,
                'disponible'                => $cantidad_faltante <= 0,
            ]);

            if ($cantidad_faltante > 0) {
                $faltantes[] = $item;
            } else {
                $suficientes[] = $item;
            }
        }

        return [
            'ok'              => empty($faltantes) && empty($revision_manual),
            'faltantes'       => $faltantes,
            'suficientes'     => $suficientes,
            'revision_manual' => $revision_manual,
            'sin_formulacion' => false,
        ];
    }

    /**
     * Verifica disponibilidad de insumos para una orden de venta u obra.
     * Usa explotar_bom_plano + conversión segura de unidades (como calcular_insumos_para_proyecto).
     *
     * @param int    $origen_id ID orden_venta u obra
     * @param string $tipo      'venta' | 'obra'
     * @return array{
     *   ok: bool,
     *   faltantes: array,
     *   suficientes: array,
     *   revision_manual: array,
     *   sin_formulacion: bool,
     *   sin_productos_fabricados: bool,
     *   productos_analizados: int
     * }
     */
    public function verificar_disponibilidad_insumos_para_orden($origen_id, $tipo = 'venta') {
        $this->load->helper('unidades');

        $origen_id = (int) $origen_id;
        $tipo = ($tipo === 'obra') ? 'obra' : 'venta';

        $datos_lineas = $this->_obtener_lineas_fabricadas_para_verificacion($origen_id, $tipo);
        $lineas = $datos_lineas['lineas'];

        if (empty($lineas)) {
            return [
                'ok'                       => true,
                'faltantes'                => [],
                'suficientes'              => [],
                'revision_manual'          => [],
                'sin_formulacion'          => $datos_lineas['sin_formulacion'],
                'sin_productos_fabricados' => true,
                'productos_analizados'     => 0,
            ];
        }

        $mapa_insumos = [];
        $revision_manual = [];

        foreach ($lineas as $linea) {
            $escalado = $this->_resolver_total_bom_linea(
                $linea->cantidad,
                $linea->unidad_linea ?? null,
                $linea->unidad_producto ?? null,
                $linea->formulacion_id
            );

            if (!$escalado['success']) {
                $revision_manual[] = [
                    'tipo'            => 'linea',
                    'producto_id'     => $linea->producto_id,
                    'producto_nombre' => $linea->producto_nombre,
                    'producto_codigo' => $linea->producto_codigo,
                    'motivo_revision' => $escalado['motivo'],
                ];
                continue;
            }

            $total_kg = (float) $escalado['kg'];
            if ($total_kg <= 0) {
                continue;
            }

            $visitados = [];
            $bom_plano = $this->explotar_bom_plano($linea->formulacion_id, $total_kg, 0, $visitados);

            foreach ($bom_plano as $item) {
                if (empty($item['insumo_id'])) {
                    continue;
                }

                $insumo_id = (int) $item['insumo_id'];
                $kg_requeridos = (float) ($item['kg'] ?? 0);
                if ($kg_requeridos <= 0) {
                    continue;
                }

                if (!isset($mapa_insumos[$insumo_id])) {
                    $insumo_row = $this->db->select('id, codigo, nombre_tecnico, stock_actual, unidad_medida, precio_promedio')
                        ->where('id', $insumo_id)
                        ->get('insumos')
                        ->row();

                    if (!$insumo_row) {
                        continue;
                    }

                    $mapa_insumos[$insumo_id] = [
                        'insumo_id'          => $insumo_id,
                        'insumo_codigo'      => $insumo_row->codigo,
                        'insumo_nombre'      => $insumo_row->nombre_tecnico,
                        'unidad_insumo'      => $insumo_row->unidad_medida,
                        'unidad_formula'     => 'Kg',
                        'cantidad_requerida' => 0.0,
                        'stock_disponible'   => (float) $insumo_row->stock_actual,
                        'precio_promedio'    => (float) $insumo_row->precio_promedio,
                        'productos_origen'   => [],
                    ];
                }

                $mapa_insumos[$insumo_id]['cantidad_requerida'] += $kg_requeridos;
                $mapa_insumos[$insumo_id]['productos_origen'][$linea->producto_id] = $linea->producto_nombre;
            }
        }

        $faltantes = [];
        $suficientes = [];

        foreach ($mapa_insumos as $insumo_id => $datos) {
            $unidad_formula = $datos['unidad_formula'] ?: 'Kg';
            $unidad_insumo  = $datos['unidad_insumo'];

            $conv = convertir_unidad_insumo($datos['cantidad_requerida'], $unidad_formula, $unidad_insumo);

            $item_base = [
                'insumo_id'          => $datos['insumo_id'],
                'insumo_codigo'      => $datos['insumo_codigo'],
                'insumo_nombre'      => $datos['insumo_nombre'],
                'unidad_insumo'      => $unidad_insumo,
                'unidad_formula'     => $unidad_formula,
                'cantidad_requerida' => $datos['cantidad_requerida'],
                'stock_disponible'   => $datos['stock_disponible'],
                'precio_promedio'    => $datos['precio_promedio'],
                'productos_origen'   => array_values($datos['productos_origen']),
            ];

            if (!$conv['success']) {
                $revision_manual[] = array_merge($item_base, [
                    'motivo_revision' => $conv['motivo'],
                ]);
                continue;
            }

            $cantidad_en_unidad_insumo = (float) $conv['cantidad_convertida'];
            $cantidad_faltante = max(0, round($cantidad_en_unidad_insumo - $datos['stock_disponible'], 6));

            $item = array_merge($item_base, [
                'cantidad_en_unidad_insumo' => $cantidad_en_unidad_insumo,
                'cantidad_faltante'         => $cantidad_faltante,
                'conversion_aplicada'       => !empty($conv['unidad_coincide']) ? false : true,
            ]);

            if ($cantidad_faltante > 0) {
                $faltantes[] = $item;
            } else {
                $suficientes[] = $item;
            }
        }

        usort($faltantes, fn($a, $b) => $b['cantidad_faltante'] <=> $a['cantidad_faltante']);

        return [
            'ok'                       => empty($faltantes) && empty($revision_manual),
            'faltantes'                => $faltantes,
            'suficientes'              => $suficientes,
            'revision_manual'          => $revision_manual,
            'sin_formulacion'          => $datos_lineas['sin_formulacion'],
            'sin_productos_fabricados' => false,
            'productos_analizados'     => count($lineas),
        ];
    }

    /**
     * Solo consulta disponibilidad (sin crear pre-órdenes). Para cotizaciones y borradores.
     */
    public function consultar_verificacion_insumos($origen_id, $tipo = 'venta') {
        $tipo = ($tipo === 'obra') ? 'obra' : 'venta';
        $verificacion = $this->verificar_disponibilidad_insumos_para_orden($origen_id, $tipo);

        return $this->_empaquetar_respuesta_verificacion($verificacion, null, true);
    }

    /**
     * Verifica insumos y, si hay faltantes, genera pre-órdenes Pendiente hacia Compras.
     * Usar solo en documentos de compromiso (venta confirmada, obra aprobada, etc.).
     *
     * @param int         $origen_id
     * @param string      $tipo       'venta' | 'obra'
     * @param int         $usuario_id
     * @param string|null $notas
     * @return array
     */
    public function procesar_verificacion_insumos_post_creacion($origen_id, $tipo, $usuario_id, $notas = null) {
        $tipo = ($tipo === 'obra') ? 'obra' : 'venta';
        $verificacion = $this->verificar_disponibilidad_insumos_para_orden($origen_id, $tipo);

        $preordenes = null;
        if (!empty($verificacion['faltantes'])) {
            $faltantes_canonicos = [];
            foreach ($verificacion['faltantes'] as $f) {
                if (empty($f['insumo_id']) || ($f['cantidad_faltante'] ?? 0) <= 0 || empty($f['unidad_insumo'])) {
                    continue;
                }
                $faltantes_canonicos[] = [
                    'insumo_id'         => $f['insumo_id'],
                    'cantidad_faltante' => (float) $f['cantidad_faltante'],
                    'unidad_insumo'     => $f['unidad_insumo'],
                ];
            }

            if (!empty($faltantes_canonicos)) {
                $this->load->model('Compras/PreordenesModel');
                $preordenes = $this->PreordenesModel->crear_preordenes_desde_faltantes(
                    $faltantes_canonicos,
                    $tipo,
                    (int) $origen_id,
                    (int) $usuario_id,
                    $notas
                );
            }
        }

        return $this->_empaquetar_respuesta_verificacion($verificacion, $preordenes, false);
    }

    /**
     * Arma la respuesta estándar para controladores/UI.
     */
    private function _empaquetar_respuesta_verificacion(array $verificacion, $preordenes, $solo_consulta) {
        return [
            'verificacion'    => $verificacion,
            'preordenes'      => $preordenes,
            'mensaje_resumen' => $this->_construir_resumen_verificacion_insumos($verificacion, $preordenes, $solo_consulta),
            'bloqueada'       => !$verificacion['ok'] || !empty($verificacion['revision_manual']),
            'solo_consulta'   => $solo_consulta,
        ];
    }

    /**
     * Texto resumido para mostrar al usuario tras crear venta/obra.
     */
    private function _construir_resumen_verificacion_insumos(array $verificacion, $preordenes = null, $solo_consulta = false) {
        if (!empty($verificacion['sin_productos_fabricados'])) {
            if ($verificacion['sin_formulacion']) {
                return 'La orden no incluye productos fabricados con formulación activa.';
            }
            return 'La orden no incluye productos fabricados; no se requiere verificación de insumos.';
        }

        $partes = [];

        if ($verificacion['ok'] && empty($verificacion['revision_manual'])) {
            $partes[] = 'Insumos OK para producción (' . count($verificacion['suficientes']) . ' insumo(s) verificados).';
        } elseif ($verificacion['ok'] && !empty($verificacion['revision_manual'])) {
            $partes[] = 'Stock aparentemente suficiente, pero ' . count($verificacion['revision_manual']) . ' insumo(s) requieren revisión manual de unidades.';
        } else {
            $partes[] = 'Faltan ' . count($verificacion['faltantes']) . ' insumo(s) para producir esta orden.';
            if ($solo_consulta) {
                $partes[] = 'Las pre-órdenes se generarán al confirmar el documento.';
            }
        }

        if ($preordenes && !empty($preordenes['creadas'])) {
            $folios = array_map(function ($p) {
                return $p->folio ?? ('#' . $p->id);
            }, $preordenes['creadas']);
            $partes[] = 'Se generaron ' . count($folios) . ' pre-orden(es) Pendiente: ' . implode(', ', $folios) . '.';
        } elseif (!$verificacion['ok'] && (empty($preordenes) || empty($preordenes['creadas']))) {
            if (!empty($preordenes['errores'])) {
                $partes[] = 'No se pudieron crear nuevas pre-órdenes (posible duplicado pendiente).';
            }
        }

        if (!empty($verificacion['revision_manual'])) {
            $partes[] = count($verificacion['revision_manual']) . ' insumo(s) con unidades no comparables — revisar en Producción.';
        }

        return implode(' ', $partes);
    }
}
