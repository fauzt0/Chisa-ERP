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
        
        // Búsqueda opcional por nombre de versión, descripción o comentarios
        if($busqueda && trim($busqueda) != '') {
            $this->db->group_start();
            $this->db->like('formulaciones.nombre_version', $busqueda);
            $this->db->or_like('formulaciones.descripcion', $busqueda);
            $this->db->or_like('formulaciones.comentarios', $busqueda);
            $this->db->or_like('clientes.razon_social', $busqueda);
            $this->db->or_like('clientes.nombre_comercial', $busqueda);
            $this->db->group_end();
        }
        
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

        if ($termino && strlen(trim($termino)) > 0) {
            $this->db->group_start();
            $this->db->like('p.nombre', $termino);
            $this->db->or_like('p.codigo', $termino);
            $this->db->or_like('f.nombre_version', $termino);
            $this->db->or_like('f.referencia_cliente', $termino);
            $this->db->group_end();
        }

        $this->db->order_by('f.es_activa DESC, f.fecha_creacion DESC');
        $this->db->limit($limite);
        return $this->db->get()->result();
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

            $componentes_escalados[] = $comp_escalado;
        }

        return [
            'formulacion' => $formulacion,
            'cubetas_calculadas' => $total_cubetas,
            'kg_necesarios' => $total_kg_necesarios,
            'multiplicador' => $multiplicador,
            'componentes' => $componentes_escalados
        ];
    }
}
