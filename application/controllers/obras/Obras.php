<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obras extends MY_Controller {
    
    protected $modulo = 'Obras';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Obras/ObrasModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal - Lista de obras
     */
    public function index() {
        setViewSuccess('Módulo de obras cargado correctamente');
        $this->viewData['pageTitle'] = 'Gestión de Obras';
        $this->viewData['headTitle'] = 'Obras';
        $this->viewData['breadcrumb'] = 'Inicio > Obras';
        
        // Obtener estadísticas
        $stats = $this->ObrasModel->get_estadisticas();
        
        $this->viewData['response'] = [
            'stats' => $stats
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'obras/index';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene obras para DataTables (AJAX)
     */
    public function get_obras_ajax() {
        $draw = $this->input->get('draw');
        $start = $this->input->get('start');
        $length = $this->input->get('length');
        $search = $this->input->get('search')['value'] ?? '';
        
        // Filtros adicionales
        $filtros = [];
        if($search) {
            $filtros['busqueda'] = $search;
        }
        
        $estatus_filter = $this->input->get('estatus_filter');
        if($estatus_filter && $estatus_filter != 'todos') {
            $filtros['estatus'] = [$estatus_filter];
        }
        
        // Obtener obras
        $obras = $this->ObrasModel->get_obras($filtros);
        $total_records = count($obras);
        
        // Paginar resultados
        $obras_paginated = array_slice($obras, $start, $length);
        
        // Formatear datos para DataTables
        $data = [];
        foreach($obras_paginated as $obra) {
            $data[] = [
                'id' => $obra->id,
                'folio' => $obra->folio,
                'nombre' => $obra->nombre,
                'cliente' => $obra->cliente ?: 'Sin cliente',
                'ciudad' => $obra->ciudad ?: '-',
                'estado' => $obra->estado ?: '-',
                'estatus' => $obra->estatus,
                'porcentaje_avance' => $obra->porcentaje_avance,
                'fecha_creacion' => $obra->fecha_creacion,
                'acciones' => ''
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
     * Vista detalle de una obra
     */
    public function detalle($obra_id) {
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);
        
        if(!$obra) {
            show_404();
            return;
        }
        
        setViewSuccess('Detalle de obra cargado correctamente');
        $this->viewData['pageTitle'] = 'Detalle de Obra - ' . $obra->folio;
        $this->viewData['headTitle'] = 'Detalle de Obra';
        $this->viewData['breadcrumb'] = 'Inicio > Obras > Detalle';
        
        $this->viewData['response'] = [
            'obra' => $obra
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'obras/detalle';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Guarda una nueva obra (AJAX)
     */
    public function guardar_ajax() {
        $data = [
            'nombre' => $this->input->post('nombre'),
            'cliente_id' => $this->input->post('cliente_id'),
            'direccion' => $this->input->post('direccion'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'coordenadas_gps' => $this->input->post('coordenadas_gps'),
            'area_total' => $this->input->post('area_total'),
            'tipo_superficie' => $this->input->post('tipo_superficie'),
            'condiciones_ambientales' => $this->input->post('condiciones_ambientales'),
            'especificaciones_tecnicas' => $this->input->post('especificaciones_tecnicas'),
            'estatus' => $this->input->post('estatus') ?: 'Planificación',
            'fecha_inicio_estimada' => $this->input->post('fecha_inicio_estimada'),
            'fecha_fin_estimada' => $this->input->post('fecha_fin_estimada'),
            'descripcion' => $this->input->post('descripcion'),
            'notas_internas' => $this->input->post('notas_internas'),
            'responsable_tecnico_id' => $this->input->post('responsable_tecnico_id'),
            'responsable_ventas_id' => $this->input->post('responsable_ventas_id'),
            // Campos financieros
            'costo_estimado' => $this->input->post('costo_estimado') ?: 0,
            'descuento_porcentaje' => $this->input->post('descuento_porcentaje') ?: 0,
            'iva_porcentaje' => $this->input->post('iva_porcentaje') ?: 16,
            'anticipo_porcentaje' => $this->input->post('anticipo_porcentaje') ?: 0,
            'condiciones_pago' => $this->input->post('condiciones_pago'),
            'tiempo_entrega' => $this->input->post('tiempo_entrega'),
            'creado_por' => $this->session->userdata('user_id') ?: 1
        ];
        
        $obra_id = $this->ObrasModel->crear_obra($data);
        
        if($obra_id) {
            // Calcular totales iniciales (aunque no haya productos aún)
            $this->ObrasModel->calcular_totales_obra($obra_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Obra creada correctamente',
                'obra_id' => $obra_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear la obra'
            ]);
        }
    }
    
    /**
     * Actualiza una obra existente (AJAX)
     */
    public function actualizar_ajax() {
        $obra_id = $this->input->post('obra_id');

        $this->db->select('estatus');
        $this->db->where('id', $obra_id);
        $obra_anterior = $this->db->get('obras')->row();
        
        $data = [
            'nombre' => $this->input->post('nombre'),
            'estatus' => $this->input->post('estatus'),
            'porcentaje_avance' => $this->input->post('porcentaje_avance'),
            'costo_real' => $this->input->post('costo_real'),
            'condiciones_ambientales' => $this->input->post('condiciones_ambientales'),
            'especificaciones_tecnicas' => $this->input->post('especificaciones_tecnicas'),
            'descuento_porcentaje' => $this->input->post('descuento_porcentaje'),
            'iva_porcentaje' => $this->input->post('iva_porcentaje'),
            'anticipo_porcentaje' => $this->input->post('anticipo_porcentaje'),
            'modificado_por' => $this->session->userdata('user_id') ?: 1
        ];
        
        $result = $this->ObrasModel->actualizar_obra($obra_id, $data);
        
        if($result) {
            // Recalcular totales
            $this->ObrasModel->calcular_totales_obra($obra_id);

            $nuevo_estatus = $this->input->post('estatus');
            if ($obra_anterior && $obra_anterior->estatus !== 'Aprobada' && $nuevo_estatus === 'Aprobada') {
                $obra_actual = $this->ObrasModel->get_obra_detalle($obra_id);
                if (empty($obra_actual->orden_venta_id)) {
                    $this->ObrasModel->crear_solicitudes_produccion_desde_obra($obra_id);
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Obra actualizada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar la obra'
            ]);
        }
    }
    
    /**
     * Elimina una obra (AJAX - soft delete)
     */
    public function eliminar_ajax() {
        $obra_id = $this->input->post('obra_id');
        $usuario_id = $this->session->userdata('user_id');
        
        $result = $this->ObrasModel->eliminar_obra($obra_id, $usuario_id);
        
        if($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Obra eliminada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la obra'
            ]);
        }
    }
    
    /**
     * Agrega un producto a la obra (AJAX)
     */
    public function agregar_producto_ajax() {
        $obra_id = $this->input->post('obra_id');
        
        $data = [
            'obra_id' => $obra_id,
            'producto_id' => $this->input->post('producto_id'),
            'cantidad_calculada' => $this->input->post('cantidad_calculada'),
            'cantidad_ajustada' => $this->input->post('cantidad_ajustada'),
            'unidad' => $this->input->post('unidad'),
            'area_aplicacion' => $this->input->post('area_aplicacion'),
            'rendimiento_teorico' => $this->input->post('rendimiento_teorico'),
            'factor_desperdicio' => $this->input->post('factor_desperdicio') ?: 1.10,
            'notas' => $this->input->post('notas'),
            'seccion_obra' => $this->input->post('seccion_obra'),
            'precio_unitario' => $this->input->post('precio_unitario'),
            'agregado_por' => $this->session->userdata('user_id') ?: 1
        ];
        
        $id = $this->ObrasModel->agregar_producto($data);
        
        if($id) {
            // Recalcular totales de la obra
            $this->ObrasModel->calcular_totales_obra($obra_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto agregado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al agregar el producto'
            ]);
        }
    }
    
    /**
     * Elimina un producto de la obra (AJAX)
     */
    public function eliminar_producto_ajax() {
        $producto_obra_id = $this->input->post('producto_obra_id');
        
        // Obtener obra_id antes de eliminar
        $this->db->select('obra_id');
        $this->db->where('id', $producto_obra_id);
        $producto = $this->db->get('obras_productos')->row();
        
        $result = $this->ObrasModel->eliminar_producto($producto_obra_id);
        
        if($result && $producto) {
            // Recalcular totales de la obra
            $this->ObrasModel->calcular_totales_obra($producto->obra_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el producto'
            ]);
        }
    }
    
    /**
     * Agrega un comentario a la obra (AJAX)
     */
    public function agregar_comentario_ajax() {
        $data = [
            'obra_id' => $this->input->post('obra_id'),
            'comentario' => $this->input->post('comentario'),
            'tipo' => $this->input->post('tipo') ?: 'General',
            'usuario_id' => $this->session->userdata('user_id') ?: 1
        ];
        
        $id = $this->ObrasModel->agregar_comentario($data);
        
        if($id) {
            echo json_encode([
                'success' => true,
                'message' => 'Comentario agregado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al agregar el comentario'
            ]);
        }
    }
    
    /**
     * Sube un archivo a la obra (AJAX)
     */
    public function subir_archivo_ajax() {
        $obra_id = $this->input->post('obra_id');
        
        if(!isset($_FILES['archivo'])) {
            echo json_encode([
                'success' => false,
                'message' => 'No se recibió ningún archivo'
            ]);
            return;
        }
        
        $config['upload_path'] = './uploads/obras/' . $obra_id . '/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|dwg|dxf';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = true;
        
        // Crear directorio si no existe
        if(!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }
        
        $this->load->library('upload', $config);
        
        if($this->upload->do_upload('archivo')) {
            $upload_data = $this->upload->data();
            
            $data = [
                'obra_id' => $obra_id,
                'nombre_original' => $upload_data['orig_name'],
                'nombre_archivo' => $upload_data['file_name'],
                'ruta_archivo' => $upload_data['full_path'],
                'tipo_archivo' => $upload_data['file_type'],
                'extension' => $upload_data['file_ext'],
                'tamano' => $upload_data['file_size'] * 1024, // Convertir a bytes
                'categoria' => $this->input->post('categoria') ?: 'Otro',
                'descripcion' => $this->input->post('descripcion'),
                'etiquetas' => $this->input->post('etiquetas'),
                'subido_por' => $this->session->userdata('user_id') ?: 1
            ];
            
            $id = $this->ObrasModel->guardar_archivo($data);
            
            if($id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Archivo subido correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al guardar la información del archivo'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ]);
        }
    }
    
    /**
     * Elimina un archivo de la obra (AJAX)
     */
    public function eliminar_archivo_ajax() {
        $archivo_id = $this->input->post('archivo_id');
        
        $result = $this->ObrasModel->eliminar_archivo($archivo_id);
        
        if($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el archivo'
            ]);
        }
    }
    
    /**
     * Obtiene lista de clientes para el selector (AJAX)
     */
    public function get_clientes_ajax() {
        $this->db->select('id, razon_social, nombre_comercial, rfc');
        $this->db->from('clientes');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('razon_social', 'ASC');
        
        $clientes = $this->db->get()->result();
        
        echo json_encode([
            'success' => true,
            'clientes' => $clientes
        ]);
    }
    
    /**
     * Obtiene lista de productos para el selector (AJAX)
     */
    public function get_productos_ajax() {
        $this->db->select('id, nombre, codigo, unidad_venta, precio_venta');
        $this->db->from('productos');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('nombre', 'ASC');
        
        $productos = $this->db->get()->result();
        
        echo json_encode([
            'success' => true,
            'productos' => $productos
        ]);
    }
    
    /**
     * Registra un nuevo pago (AJAX)
     */
    public function registrar_pago_ajax() {
        $data = [
            'obra_id' => $this->input->post('obra_id'),
            'fecha_pago' => $this->input->post('fecha_pago') . ' ' . date('H:i:s'),
            'monto' => $this->input->post('monto'),
            'metodo_pago' => $this->input->post('metodo_pago'),
            'referencia' => $this->input->post('referencia'),
            'concepto' => $this->input->post('concepto'),
            'notas' => $this->input->post('notas'),
            'recibido_por' => $this->session->userdata('user_id') ?: 1
        ];
        
        $pago_id = $this->ObrasModel->registrar_pago($data);
        
        if($pago_id) {
            echo json_encode([
                'success' => true,
                'message' => 'Pago registrado correctamente',
                'pago_id' => $pago_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el pago'
            ]);
        }
    }
    
    /**
     * Obtiene los pagos de una obra (AJAX)
     */
    public function get_pagos_ajax() {
        $obra_id = $this->input->get('obra_id');
        $pagos = $this->ObrasModel->get_pagos_obra($obra_id);
        
        echo json_encode([
            'success' => true,
            'pagos' => $pagos
        ]);
    }
    
    /**
     * Cancela un pago (AJAX)
     */
    public function cancelar_pago_ajax() {
        $pago_id = $this->input->post('pago_id');
        
        $result = $this->ObrasModel->cancelar_pago($pago_id);
        
        if($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Pago cancelado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cancelar el pago'
            ]);
        }
    }
    
    /**
     * Ver recibo de pago
     */
    public function ver_recibo($pago_id) {
        $pago = $this->ObrasModel->get_pago($pago_id);
        
        if(!$pago) {
            show_404();
            return;
        }
        
        $response['pago'] = $pago;
        $this->load->view('obras/recibo', $response);
    }
    
    /**
     * Obtiene la formulación activa de un producto (AJAX)
     */
    public function get_formulacion_ajax() {
        $producto_id = $this->input->post('producto_id');
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $this->load->model('Produccion/ProductosModel');
        $formulacion = $this->ProductosModel->get_formulacion_activa($producto_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Este producto no tiene una formulación activa asignada.']);
        }
    }
    
    /**
     * Obtiene historial de formulaciones de un producto (AJAX)
     */
    public function get_historial_formulaciones_ajax() {
        $producto_id = $this->input->post('producto_id');
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $this->load->model('Produccion/ProductosModel');
        $historial = $this->ProductosModel->get_historial_formulaciones($producto_id);
        
        if($historial) {
            echo json_encode(['success' => true, 'historial' => $historial]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró historial de formulaciones']);
        }
    }
    
    /**
     * Actualiza la formulación de un producto en la obra (AJAX)
     */
    public function actualizar_formulacion_producto_ajax() {
        $producto_obra_id = $this->input->post('producto_obra_id');
        $formulacion_id = $this->input->post('formulacion_id');
        $formulacion_version = $this->input->post('formulacion_version');
        
        if(!$producto_obra_id || !$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $this->db->where('id', $producto_obra_id);
        $result = $this->db->update('obras_productos', [
            'formulacion_id' => $formulacion_id,
            'formulacion_version' => $formulacion_version
        ]);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Formulación actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la formulación']);
        }
    }

    /**
     * Vista de documento PDF profesional de la obra
     */
    public function exportar_pdf($obra_id) {
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);
        if (!$obra) {
            show_404();
            return;
        }

        $this->load->model('Config/EmpresaModel');
        $data['obra'] = $obra;
        $data['empresa'] = $this->EmpresaModel->get_config();
        $this->load->view('obras/pdf_resumen', $data);
    }

    /**
     * Órdenes de venta del cliente disponibles para vincular (AJAX)
     */
    public function get_ordenes_venta_disponibles_ajax() {
        $obra_id = $this->input->get('obra_id');
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);

        if (!$obra) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }

        $ordenes = $this->ObrasModel->get_ordenes_venta_disponibles($obra->cliente_id, $obra_id);
        echo json_encode(['success' => true, 'ordenes' => $ordenes]);
    }

    /**
     * Vincula una orden de venta existente a la obra (AJAX)
     */
    public function vincular_orden_venta_ajax() {
        $obra_id = $this->input->post('obra_id');
        $orden_venta_id = $this->input->post('orden_venta_id');

        if (!$obra_id || !$orden_venta_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $result = $this->ObrasModel->vincular_orden_venta($obra_id, $orden_venta_id);
        echo json_encode($result);
    }

    /**
     * Genera una orden de venta desde la obra (AJAX)
     */
    public function generar_orden_venta_ajax() {
        $obra_id = $this->input->post('obra_id');
        if (!$obra_id) {
            echo json_encode(['success' => false, 'message' => 'ID de obra no proporcionado']);
            return;
        }

        $usuario_id = $this->session->userdata('user_id') ?: 1;
        $result = $this->ObrasModel->generar_orden_venta_desde_obra($obra_id, $usuario_id);
        echo json_encode($result);
    }

    /**
     * Confirma la orden de venta vinculada y envía a producción (AJAX)
     */
    public function confirmar_orden_venta_ajax() {
        $obra_id = $this->input->post('obra_id');
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);

        if (!$obra || empty($obra->orden_venta_id)) {
            echo json_encode(['success' => false, 'message' => 'La obra no tiene orden de venta vinculada']);
            return;
        }

        $this->load->model('Ventas/VentasModel');
        $this->VentasModel->confirmar_orden($obra->orden_venta_id);

        echo json_encode([
            'success' => true,
            'message' => 'Orden ' . $obra->orden_venta_folio . ' confirmada y enviada a producción'
        ]);
    }
}
