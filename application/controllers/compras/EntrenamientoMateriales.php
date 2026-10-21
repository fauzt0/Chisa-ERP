<?php
/**
 * EntrenamientoMateriales — consulta y edición ligera de insumos.
 *
 * DECISIONES DE CLASIFICACIÓN (iteración 5):
 *   - La columna `categorias_insumos` tiene 16 categorías → no alcanza para
 *     distinguir materia prima de insumo genérico en el entrenamiento.
 *   - Se añade `insumos.clase_uso` ENUM('materia_prima','insumo') como
 *     clasificación propia de esta pantalla, independiente de categoria_id.
 *   - categoria_id = 1 (Materias Primas Químicas) se sugiere como materia_prima;
 *     Resinas es categoria_id = 4. El operador puede sobreescribir libremente.
 *   - La columna `insumos.tipo` (comprado/fabricado/semielaborado) NO se
 *     modifica desde esta pantalla; es responsabilidad del CRUD de Insumos.
 *
 * Permiso requerido: almacen_insumos
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class EntrenamientoMateriales extends MY_Controller {

    protected $modulo = 'Almacén';

    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Compras/InsumosModel');
        $this->load->model('Compras/ProveedoresModel');

        // ALTER idempotente: solo ADD, nunca DROP/MODIFY
        if (!$this->db->field_exists('clase_uso', 'insumos')) {
            $this->db->query(
                "ALTER TABLE insumos ADD COLUMN clase_uso ENUM('materia_prima','insumo') NULL DEFAULT NULL"
            );
        }
        if (!$this->db->field_exists('actualizado_por', 'insumos')) {
            $this->db->query(
                "ALTER TABLE insumos ADD COLUMN actualizado_por INT NULL DEFAULT NULL"
            );
        }
    }

    // -----------------------------------------------------------------------
    // GET: vista principal
    // -----------------------------------------------------------------------

    public function index() {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');

        $busqueda = trim((string) $this->input->get('busqueda'));
        $filtros  = $busqueda !== '' ? ['busqueda' => $busqueda] : [];

        $insumos  = $this->InsumosModel->get_all_insumos($filtros);
        $filas    = $this->_armar_filas($insumos);

        // Lista de proveedores activos para los selectores del modal
        $proveedores_activos = $this->ProveedoresModel->listar_activos();

        $this->viewData['pageTitle']  = 'Entrenamiento de materiales';
        $this->viewData['headTitle']  = 'Entrenamiento de materiales';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Entrenamiento de materiales';
        $this->viewData['response']   = [
            'busqueda'            => $busqueda,
            'filas'               => $filas,
            'total'               => count($filas),
            'proveedores_activos' => $proveedores_activos,
        ];
        $this->viewData['pageView'] = 'compras/entrenamiento_materiales/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    // -----------------------------------------------------------------------
    // POST: guardar datos del insumo (estatus, alias interno, clase_uso)
    // -----------------------------------------------------------------------

    public function guardar_insumo_ajax() {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');

        $id = (int) $this->input->post('id');
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']); return;
        }

        $insumo = $this->InsumosModel->get_insumo($id);
        if (!$insumo) {
            echo json_encode(['success' => false, 'message' => 'Insumo no encontrado']); return;
        }

        // Estatus: solo Activo o Inactivo; rechaza cambio si ya es Descontinuado
        $estatus_raw = $this->input->post('estatus');
        $estatus = null;
        if ($estatus_raw !== null && $estatus_raw !== '') {
            if (!in_array($estatus_raw, ['Activo', 'Inactivo'], true)) {
                echo json_encode(['success' => false, 'message' => 'Estatus no permitido']); return;
            }
            if ($insumo->estatus === 'Descontinuado') {
                echo json_encode(['success' => false, 'message' => 'El insumo está Descontinuado y no puede cambiarse desde esta pantalla']); return;
            }
            $estatus = $estatus_raw;
        }

        $clase_uso_raw = $this->input->post('clase_uso');
        $tiene_clase_uso = $clase_uso_raw !== null;
        $clase_uso = null;
        if ($tiene_clase_uso && $clase_uso_raw !== '') {
            if (!in_array($clase_uso_raw, ['materia_prima', 'insumo'], true)) {
                echo json_encode(['success' => false, 'message' => 'clase_uso inválido']); return;
            }
            $clase_uso = $clase_uso_raw;
        }

        $alias_interno = $this->input->post('alias_interno');

        $data = ['actualizado_por' => (int) $this->session->userdata('id')];
        if ($estatus    !== null) $data['estatus']    = $estatus;
        if ($tiene_clase_uso) $data['clase_uso'] = $clase_uso;
        if ($alias_interno !== null) $data['alias'] = trim($alias_interno);

        $ok = $this->InsumosModel->guardar_entrenamiento($id, $data);
        if ($ok) {
            $this->registrar_bitacora("Entrenamiento: guardado insumo #{$id}", 'Compras');
        }
        $payload = ['success' => (bool) $ok, 'message' => $ok ? 'Guardado correctamente' : 'Sin cambios o error al guardar'];
        if ($this->input->post('volver') === '1') {
            $this->session->set_flashdata('em_msg', $payload['message']);
            $this->session->set_flashdata('em_ok', $payload['success'] ? '1' : '0');
            redirect('compras/EntrenamientoMateriales/editar/' . $id);
            return;
        }
        echo json_encode($payload);
    }

    public function editar($id = null) {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');
        $id = (int) $id;
        $insumo = $id > 0 ? $this->InsumosModel->get_insumo($id) : null;
        if (!$insumo) {
            show_404();
            return;
        }
        $filas = $this->_armar_filas([$insumo]);
        $this->viewData['pageTitle'] = 'Entrenar ' . ($insumo->codigo ?? '');
        $this->viewData['headTitle'] = 'Entrenar ' . ($insumo->codigo ?? '');
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Entrenamiento de materiales > ' . ($insumo->codigo ?? '');
        $this->viewData['response'] = [
            'fila' => $filas[0],
            'proveedores_activos' => $this->ProveedoresModel->listar_activos(),
            'aviso' => $this->session->flashdata('em_msg'),
            'aviso_ok' => $this->session->flashdata('em_ok') === '1',
        ];
        $this->viewData['pageView'] = 'compras/entrenamiento_materiales/editar';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    // -----------------------------------------------------------------------
    // POST: agregar alias (Proveedor con proveedor_id, o Genérico)
    // -----------------------------------------------------------------------

    public function guardar_alias_ajax() {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');

        $insumo_id = (int) $this->input->post('insumo_id');
        $alias     = trim((string) $this->input->post('alias'));
        $tipo      = $this->input->post('tipo');

        if ($insumo_id <= 0 || $alias === '') {
            echo json_encode(['success' => false, 'message' => 'Insumo y alias requeridos']); return;
        }
        if (!in_array($tipo, ['Proveedor', 'Genérico', 'Comercial', 'Técnico'], true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de alias inválido']); return;
        }

        $proveedor_id = null;
        if ($tipo === 'Proveedor') {
            $pid = (int) $this->input->post('proveedor_id');
            if ($pid <= 0) {
                echo json_encode(['success' => false, 'message' => 'Se requiere proveedor para tipo Proveedor']); return;
            }
            $proveedor_id = $pid;
        }

        $data = [
            'insumo_id'   => $insumo_id,
            'alias'       => $alias,
            'tipo'        => $tipo,
            'estatus'     => 'Activo',
        ];
        if ($proveedor_id !== null) $data['proveedor_id'] = $proveedor_id;

        $res = $this->InsumosModel->agregar_alias($data);
        if (!empty($res['success'])) {
            $this->registrar_bitacora("Entrenamiento: alias '{$alias}' agregado a insumo #{$insumo_id}", 'Compras');
        }
        echo json_encode($res);
    }

    // -----------------------------------------------------------------------
    // POST: guardar precio de proveedor para un insumo (upsert)
    // -----------------------------------------------------------------------

    public function guardar_proveedor_precio_ajax() {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');

        $insumo_id    = (int) $this->input->post('insumo_id');
        $proveedor_id = (int) $this->input->post('proveedor_id');
        $precio_raw   = $this->input->post('precio_compra');

        if ($insumo_id <= 0 || $proveedor_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Insumo y proveedor requeridos']); return;
        }
        if (!is_numeric($precio_raw) || (float) $precio_raw < 0) {
            echo json_encode(['success' => false, 'message' => 'Precio inválido']); return;
        }

        $res = $this->ProveedoresModel->upsert_precio_insumo($proveedor_id, $insumo_id, (float) $precio_raw);
        if (!empty($res['success'])) {
            $this->registrar_bitacora("Entrenamiento: precio proveedor #{$proveedor_id} insumo #{$insumo_id} = \${$precio_raw}", 'Compras');
        }
        echo json_encode($res);
    }

    // -----------------------------------------------------------------------
    // POST: guardar datos de producto enlazado (solo si producto_id no es null)
    // -----------------------------------------------------------------------

    public function guardar_producto_ajax() {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');

        $insumo_id = (int) $this->input->post('insumo_id');
        if ($insumo_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']); return;
        }

        $insumo = $this->InsumosModel->get_insumo($insumo_id);
        if (!$insumo || empty($insumo->producto_id)) {
            echo json_encode(['success' => false, 'message' => 'El insumo no tiene producto enlazado']); return;
        }

        $precio_venta  = $this->input->post('precio_venta');
        $contenido_neto = $this->input->post('contenido_neto');

        $data = [];
        $uv = $this->input->post('unidad_venta');
        if ($uv !== null) $data['unidad_venta'] = $uv;
        $pp = $this->input->post('presentacion_principal');
        if ($pp !== null) $data['presentacion_principal'] = trim($pp);
        if ($contenido_neto !== null) $data['contenido_neto'] = is_numeric($contenido_neto) ? (float) $contenido_neto : null;
        $rend = $this->input->post('rendimiento');
        if ($rend !== null) $data['rendimiento'] = trim($rend);
        if ($precio_venta !== null && is_numeric($precio_venta)) {
            $data['precio_venta'] = (float) $precio_venta;
        }

        $ok = $this->InsumosModel->actualizar_producto_enlazado((int) $insumo->producto_id, $data);
        if ($ok) {
            $this->registrar_bitacora("Entrenamiento: producto #{$insumo->producto_id} actualizado (insumo #{$insumo_id})", 'Compras');
        }
        echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Producto actualizado' : 'Sin cambios o error']);
    }

    public function get_fila_ajax() {
        $this->requiere_permiso('almacen_insumos', 'Sin permiso');
        $id = (int) ($this->input->get('id') ?: $this->input->post('id'));
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $insumo = $this->InsumosModel->get_insumo($id);
        if (!$insumo) {
            echo json_encode(['success' => false, 'message' => 'Insumo no encontrado']);
            return;
        }
        $filas = $this->_armar_filas([$insumo]);
        $fila = $filas[0];
        $fila['proveedores_raw'] = array_map(function ($p) {
            return [
                'proveedor_id'     => (int) $p->proveedor_id,
                'precio_compra'    => (float) ($p->precio_compra ?? 0),
                'razon_social'     => $p->razon_social ?? '',
                'nombre_comercial' => $p->nombre_comercial ?? '',
                'prov_codigo'      => $p->prov_codigo ?? '',
            ];
        }, $fila['proveedores_raw']);
        $fila['aliases'] = array_map(function ($a) {
            return [
                'id'           => (int) $a->id,
                'alias'        => $a->alias,
                'tipo'         => $a->tipo,
                'proveedor_id' => $a->proveedor_id,
            ];
        }, $fila['aliases']);
        echo json_encode(['success' => true, 'fila' => $fila], JSON_UNESCAPED_UNICODE);
    }

    // -----------------------------------------------------------------------
    // Privados
    // -----------------------------------------------------------------------

    /**
     * Arma las filas para la vista sin hacer SQL directo: delega a modelos.
     *
     * @param  stdClass[] $insumos  resultado de InsumosModel::get_all_insumos
     * @return array[]
     */
    private function _armar_filas(array $insumos) {
        if (empty($insumos)) return [];

        $ids = $producto_ids = [];
        foreach ($insumos as $ins) {
            $ids[] = (int) $ins->id;
            if (!empty($ins->producto_id)) {
                $producto_ids[] = (int) $ins->producto_id;
            }
        }

        $aliases_map     = $this->InsumosModel->get_aliases_bulk($ids);
        $historial_map   = $this->InsumosModel->get_ultimo_historial_bulk($ids);
        $productos_map   = $this->InsumosModel->get_productos_enlazados($producto_ids);
        $proveedores_map = $this->ProveedoresModel->get_proveedores_insumos_bulk($ids);

        $filas = [];
        foreach ($insumos as $ins) {
            $id          = (int) $ins->id;
            $provs_raw   = $proveedores_map[$id] ?? [];

            // Texto de presentación para la columna de solo lectura
            $provs_txt = [];
            foreach ($provs_raw as $p) {
                $nombre      = $p->razon_social ?: ($p->nombre_comercial ?: $p->prov_codigo);
                $precio      = (float) ($p->precio_compra ?? 0);
                $provs_txt[] = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')
                               . ' — $' . number_format($precio, 2);
            }

            $producto_id = isset($ins->producto_id) ? (int) $ins->producto_id : null;
            $producto    = ($producto_id && isset($productos_map[$producto_id]))
                           ? $productos_map[$producto_id]
                           : null;

            $filas[] = [
                'id'             => $id,
                'producto_id'    => $producto_id ?: null,
                'codigo'         => $ins->codigo         ?? '',
                'nombre_tecnico' => $ins->nombre_tecnico ?? '',
                'alias_interno'  => $ins->alias          ?? '',
                'clase_uso'      => $ins->clase_uso      ?? '',
                'estatus'        => $ins->estatus        ?? '',
                'categoria'      => $ins->categoria_nombre ?? '',
                'tipo'           => $ins->tipo            ?? '',
                'aliases'        => $aliases_map[$id]    ?? [],   // stdClass[]
                'proveedores'    => $provs_txt,                   // string[] para display
                'proveedores_raw'=> $provs_raw,                   // stdClass[] para modal
                'historial_precio' => $historial_map[$id] ?? null,
                'producto'       => $producto,
            ];
        }
        return $filas;
    }
}
