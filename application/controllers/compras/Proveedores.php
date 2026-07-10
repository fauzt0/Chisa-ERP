<?php
/**
 * Proveedores - Controlador de gestión de proveedores
 */
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Proveedores extends MY_Controller {
    
    protected $modulo = 'Proveedores';
    
    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Compras/ProveedoresModel');
        $this->load->model('Compras/InsumosModel');
        $this->load->model('Compras/OrdenesCompraModel');
    }

    private function puede_gestionar_insumos_proveedor() {
        return tiene_permiso('proveedores_insumos') || tiene_permiso('proveedores_edit');
    }

    private function requiere_gestion_insumos_proveedor() {
        if (!$this->puede_gestionar_insumos_proveedor()) {
            $msg = 'No tienes permiso para gestionar insumos del proveedor';
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            redirect('deny');
        }
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Catálogo de Proveedores';
        $this->viewData['headTitle'] = 'Gestión de Proveedores';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Proveedores';
        
        // Obtener estadísticas
        $stats = $this->ProveedoresModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/proveedores/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de proveedores para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->ProveedoresModel->get_datatables();
        $data = array();
        $no = (int) ($this->input->post('start') ?? 0);

        foreach ($list as $proveedor) {
            $no++;
            $row = array();
            
            // Código
            $row[] = $proveedor->codigo;
            
            // Razón Social + Nombre Comercial
            $nombre = '<strong>' . $proveedor->razon_social . '</strong>';
            if($proveedor->nombre_comercial) {
                $nombre .= '<br><small class="text-muted">' . $proveedor->nombre_comercial . '</small>';
            }
            $row[] = $nombre;
            
            // RFC
            $row[] = $proveedor->rfc;
            
            // Contacto (teléfono + email)
            $contacto = '';
            if($proveedor->telefono) {
                $contacto .= '<i class="fas fa-phone"></i> ' . $proveedor->telefono;
            }
            if($proveedor->email) {
                $contacto .= '<br><i class="fas fa-envelope"></i> ' . $proveedor->email;
            }
            $row[] = $contacto ?: '-';
            
            // Ciudad, Estado
            $ubicacion = '';
            if($proveedor->ciudad) {
                $ubicacion = $proveedor->ciudad;
                if($proveedor->estado) {
                    $ubicacion .= ', ' . $proveedor->estado;
                }
            }
            $row[] = $ubicacion ?: '-';

            // Tipo de proveedor
            $tipo_badges = [
                'Materia Prima' => 'primary',
                'Materiales' => 'info',
                'Servicios' => 'warning',
                'Mixto' => 'secondary'
            ];
            $tb = $tipo_badges[$proveedor->tipo_proveedor] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $tb . '">' . ($proveedor->tipo_proveedor ?: 'Mixto') . '</span>';

            // Resumen CRM
            $crm = '<div class="small">';
            $crm .= '<span class="badge bg-light text-dark me-1" title="Insumos vinculados"><i class="fas fa-boxes"></i> ' . (int)($proveedor->total_insumos ?? 0) . '</span>';
            $crm .= '<span class="badge bg-light text-dark" title="Órdenes de compra"><i class="fas fa-file-invoice"></i> ' . (int)($proveedor->total_ordenes ?? 0) . '</span>';
            if(!empty($proveedor->ultima_orden)) {
                $crm .= '<br><span class="text-muted" style="font-size:0.75rem;">Últ. OC: ' . date('d/m/Y', strtotime($proveedor->ultima_orden)) . '</span>';
            }
            $crm .= '</div>';
            $row[] = $crm;
            
            // Estatus
            $estatus_badges = [
                'Activo' => 'success',
                'Inactivo' => 'secondary',
                'Suspendido' => 'warning'
            ];
            $eb = $estatus_badges[$proveedor->estatus] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $eb . '">' . $proveedor->estatus . '</span>';
            
            // Acciones según permisos
            $acciones = '<div class="btn-acciones-crm">';
            if (tiene_permiso('proveedores_consult')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-secondary" onclick="verDetalleProveedor('.$proveedor->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            }
            if (tiene_permiso('proveedores_insumos') || tiene_permiso('proveedores_edit')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-info" onclick="mostrarModalInsumos('.$proveedor->id.')" title="Insumos">
                    <i class="fas fa-boxes"></i>
                </button>';
            }
            if (tiene_permiso('proveedores_edit')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-primary" onclick="mostrarModalEditar('.$proveedor->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>';
            }
            if (tiene_permiso('proveedores_delete')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProveedor('.$proveedor->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            }
            $acciones .= '</div>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => (int) ($this->input->post('draw') ?? 0),
            "recordsTotal" => $this->ProveedoresModel->count_all(),
            "recordsFiltered" => $this->ProveedoresModel->count_filtered(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    
    /**
     * Obtiene un proveedor específico (AJAX)
     */
    public function get_proveedor_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $proveedor = $this->ProveedoresModel->get_proveedor($id);
        if($proveedor) {
            echo json_encode(['success' => true, 'proveedor' => $proveedor]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
        }
    }
    
    /**
     * Crea un nuevo proveedor (AJAX)
     */
    public function crear_ajax() {
        $this->requiere_permiso('proveedores_add', 'No tienes permiso para agregar proveedores');

        $data = [
            'codigo' => $this->input->post('codigo'),
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => $this->input->post('rfc'),
            'tipo_proveedor' => $this->input->post('tipo_proveedor') ?: 'Mixto',
            'contacto_principal' => $this->input->post('contacto_principal'),
            'telefono' => $this->input->post('telefono'),
            'telefono_alternativo' => $this->input->post('telefono_alternativo'),
            'email' => $this->input->post('email'),
            'sitio_web' => $this->input->post('sitio_web'),
            'direccion' => $this->input->post('direccion'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'pais' => $this->input->post('pais') ?: 'México',
            'dias_credito' => $this->input->post('dias_credito') ?: 0,
            'limite_credito' => $this->input->post('limite_credito') ?: 0,
            'banco' => $this->input->post('banco'),
            'cuenta_bancaria' => $this->input->post('cuenta_bancaria'),
            'observaciones' => $this->input->post('observaciones'),
            'calificacion' => 3,
            'estatus' => 'Activo'
        ];
        
        // Validaciones
        if(empty($data['razon_social'])) {
            echo json_encode(['success' => false, 'message' => 'La razón social es requerida']);
            return;
        }
        
        if(empty($data['rfc'])) {
            echo json_encode(['success' => false, 'message' => 'El RFC es requerido']);
            return;
        }

        $this->db->where('rfc', $data['rfc']);
        if ($this->db->count_all_results('proveedores') > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un proveedor registrado con ese RFC']);
            return;
        }
        
        $result = $this->ProveedoresModel->crear_proveedor($data);
        
        if($result) {
            $this->registrar_bitacora(
                'Proveedor creado: ' . $data['razon_social'] . ' (RFC ' . $data['rfc'] . ')',
                'Proveedores'
            );
            echo json_encode(['success' => true, 'message' => 'Proveedor creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear proveedor']);
        }
    }
    
    /**
     * Actualiza un proveedor (AJAX)
     */
    public function editar_ajax() {
        $this->requiere_permiso('proveedores_edit', 'No tienes permiso para editar proveedores');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'codigo' => $this->input->post('codigo'),
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => $this->input->post('rfc'),
            'tipo_proveedor' => $this->input->post('tipo_proveedor'),
            'contacto_principal' => $this->input->post('contacto_principal'),
            'telefono' => $this->input->post('telefono'),
            'telefono_alternativo' => $this->input->post('telefono_alternativo'),
            'email' => $this->input->post('email'),
            'sitio_web' => $this->input->post('sitio_web'),
            'direccion' => $this->input->post('direccion'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'pais' => $this->input->post('pais'),
            'dias_credito' => $this->input->post('dias_credito'),
            'limite_credito' => $this->input->post('limite_credito'),
            'banco' => $this->input->post('banco'),
            'cuenta_bancaria' => $this->input->post('cuenta_bancaria'),
            'observaciones' => $this->input->post('observaciones'),
            'estatus' => $this->input->post('estatus')
        ];

        if (!empty($data['rfc'])) {
            $this->db->where('rfc', $data['rfc']);
            $this->db->where('id !=', $id);
            if ($this->db->count_all_results('proveedores') > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe otro proveedor con ese RFC']);
                return;
            }
        }
        
        $result = $this->ProveedoresModel->actualizar_proveedor($id, $data);
        
        if($result) {
            $prov = $this->ProveedoresModel->get_proveedor($id);
            $this->registrar_bitacora(
                'Proveedor actualizado: ' . ($prov->razon_social ?? ('ID ' . $id)),
                'Proveedores'
            );
            echo json_encode(['success' => true, 'message' => 'Proveedor actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar proveedor']);
        }
    }
    
    /**
     * Elimina un proveedor (AJAX)
     */
    public function eliminar_ajax() {
        $this->requiere_permiso('proveedores_delete', 'No tienes permiso para eliminar proveedores');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $prov = $this->ProveedoresModel->get_proveedor($id);
        $result = $this->ProveedoresModel->eliminar_proveedor($id);
        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Proveedor eliminado: ' . ($prov->razon_social ?? ('ID ' . $id)),
                'Proveedores'
            );
        }
        echo json_encode($result);
    }
    
    /**
     * Obtiene insumos de un proveedor (AJAX)
     */
    public function get_insumos_proveedor_ajax() {
        $id = $this->input->post('proveedor_id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $insumos = $this->ProveedoresModel->get_insumos_proveedor($id);
        echo json_encode(['success' => true, 'insumos' => $insumos]);
    }
    
    /**
     * Agrega un insumo a un proveedor (AJAX)
     */
    public function agregar_insumo_ajax() {
        $this->requiere_gestion_insumos_proveedor();

        $proveedor_id = $this->input->post('proveedor_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$proveedor_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor e insumo requeridos']);
            return;
        }
        
        $data = [
            'precio_compra'           => $this->input->post('precio_compra'),
            'tiempo_entrega_dias'     => $this->input->post('tiempo_entrega_dias') ?: 0,
            'cantidad_minima'         => $this->input->post('cantidad_minima') ?: 1,
            'codigo_proveedor'        => $this->input->post('codigo_proveedor'),
            'nombre_proveedor'        => $this->input->post('nombre_proveedor'),
            'observaciones'           => $this->input->post('observaciones'),
            'es_proveedor_principal'  => $this->input->post('es_proveedor_principal') ? 1 : 0,
        ];
        
        $result = $this->ProveedoresModel->agregar_insumo($proveedor_id, $insumo_id, $data);
        if (!empty($result['success'])) {
            $prov = $this->ProveedoresModel->get_proveedor($proveedor_id);
            $this->registrar_bitacora(
                'Insumo vinculado a proveedor ' . ($prov->razon_social ?? $proveedor_id) . ' (insumo #' . $insumo_id . ', $' . number_format((float)$data['precio_compra'], 2) . ')',
                'Proveedores'
            );
        }
        echo json_encode($result);
    }
    
    /**
     * Actualiza precio de un insumo del proveedor (AJAX)
     */
    public function actualizar_precio_insumo_ajax() {
        $this->requiere_gestion_insumos_proveedor();

        $proveedor_id = $this->input->post('proveedor_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$proveedor_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor e insumo requeridos']);
            return;
        }
        
        $data = [
            'precio_compra'           => $this->input->post('precio_compra'),
            'tiempo_entrega_dias'     => $this->input->post('tiempo_entrega_dias'),
            'cantidad_minima'         => $this->input->post('cantidad_minima'),
            'codigo_proveedor'        => $this->input->post('codigo_proveedor'),
            'nombre_proveedor'        => $this->input->post('nombre_proveedor'),
            'observaciones'           => $this->input->post('observaciones'),
            'es_proveedor_principal'  => $this->input->post('es_proveedor_principal') ? 1 : 0,
        ];
        
        $result = $this->ProveedoresModel->actualizar_precio_insumo($proveedor_id, $insumo_id, $data);
        if (!empty($result['success'])) {
            $prov = $this->ProveedoresModel->get_proveedor($proveedor_id);
            $this->registrar_bitacora(
                'Insumo actualizado en proveedor ' . ($prov->razon_social ?? $proveedor_id) . ' (insumo #' . $insumo_id . ')',
                'Proveedores'
            );
        }
        echo json_encode($result);
    }
    
    /**
     * Elimina un insumo de un proveedor (AJAX)
     */
    public function eliminar_insumo_ajax() {
        $this->requiere_gestion_insumos_proveedor();

        $proveedor_id = $this->input->post('proveedor_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$proveedor_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor e insumo requeridos']);
            return;
        }
        
        $result = $this->ProveedoresModel->eliminar_insumo($proveedor_id, $insumo_id);
        if (!empty($result['success'])) {
            $prov = $this->ProveedoresModel->get_proveedor($proveedor_id);
            $this->registrar_bitacora(
                'Insumo desvinculado de proveedor ' . ($prov->razon_social ?? $proveedor_id) . ' (insumo #' . $insumo_id . ')',
                'Proveedores'
            );
        }
        echo json_encode($result);
    }
    
    /**
     * Obtiene lista de insumos para select (AJAX)
     */
    public function get_insumos_select_ajax() {
        $insumos = $this->InsumosModel->get_all_insumos(['estatus' => 'Activo']);
        
        $opciones = [];
        foreach($insumos as $ins) {
            $opciones[] = [
                'id' => $ins->id,
                'text' => $ins->codigo . ' - ' . $ins->nombre_tecnico . ' (' . $ins->unidad_medida . ')'
            ];
        }
        
        echo json_encode(['success' => true, 'insumos' => $opciones]);
    }

    /**
     * Órdenes de compra de un proveedor (AJAX)
     */
    public function get_ordenes_proveedor_ajax() {
        $proveedor_id = $this->input->post('proveedor_id');
        if(!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor requerido']);
            return;
        }

        $ordenes = $this->OrdenesCompraModel->get_ordenes_proveedor($proveedor_id);
        echo json_encode(['success' => true, 'ordenes' => $ordenes]);
    }

    /**
     * Historial paginado de órdenes de compra de un proveedor (AJAX)
     */
    public function get_historial_ordenes_compra_ajax($proveedor_id = 0, $limit = 10, $offset = 0) {
        $proveedor_id = (int) ($proveedor_id ?: $this->input->post('proveedor_id'));
        $limit = (int) ($limit ?: $this->input->post('limit') ?: 10);
        $offset = (int) ($offset ?: $this->input->post('offset') ?: 0);

        if(!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor requerido']);
            return;
        }

        $ordenes = $this->OrdenesCompraModel->get_historial_ordenes($proveedor_id, $limit, $offset);
        $total = $this->OrdenesCompraModel->count_historial_ordenes($proveedor_id);

        echo json_encode([
            'success' => true,
            'ordenes' => $ordenes,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Descarga plantilla Excel para carga masiva de proveedores.
     */
    public function descargar_plantilla_excel() {
        $this->requiere_permiso('proveedores_add', 'No tienes permiso para importar proveedores');

        $headers = [
            'Razón social *',
            'Nombre comercial',
            'RFC *',
            'Tipo proveedor',
            'Contacto principal',
            'Teléfono',
            'Teléfono alternativo',
            'Email',
            'Dirección',
            'Ciudad',
            'Estado',
            'Código postal',
            'País',
            'Días crédito',
            'Observaciones',
        ];

        $ejemplo = [
            '(EJEMPLO) Pinturas del Norte SA de CV',
            'Pinturas Norte',
            'PDN850101ABC',
            'Materiales',
            'Lic. García',
            '8181234567',
            '',
            'ventas@pinturasnorte.mx',
            'Av. Industrial 100',
            'Monterrey',
            'Nuevo León',
            '64000',
            'México',
            '30',
            'Reemplace o elimine esta fila antes de importar',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Proveedores');

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $col = 'A';
        foreach ($ejemplo as $valor) {
            $sheet->setCellValue($col . '2', $valor);
            $col++;
        }

        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $instrucciones = $spreadsheet->createSheet();
        $instrucciones->setTitle('Instrucciones');
        $instrucciones->setCellValue('A1', 'Cómo usar esta plantilla');
        $instrucciones->setCellValue('A3', '1. Capture sus proveedores en la hoja «Proveedores» desde la fila 2.');
        $instrucciones->setCellValue('A4', '2. La fila 2 es solo un ejemplo — reemplácela o elimínela antes de importar.');
        $instrucciones->setCellValue('A5', '3. Campos obligatorios: Razón social y RFC.');
        $instrucciones->setCellValue('A6', '4. Tipo proveedor: Materia Prima | Materiales | Servicios | Mixto (opcional, default Mixto).');
        $instrucciones->setCellValue('A7', '5. No modifique el orden de las columnas en la fila 1.');
        $instrucciones->getStyle('A1')->getFont()->setBold(true);
        $instrucciones->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="plantilla_proveedores_erp.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Procesa carga masiva de proveedores desde Excel (AJAX).
     */
    public function importar_excel_ajax() {
        $this->requiere_permiso('proveedores_add', 'No tienes permiso para importar proveedores');

        if (empty($_FILES['archivo_excel']['name'])) {
            echo json_encode(['success' => false, 'message' => 'Seleccione un archivo Excel (.xlsx o .xls)']);
            return;
        }

        $ext = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            echo json_encode(['success' => false, 'message' => 'Solo se aceptan archivos .xlsx o .xls']);
            return;
        }

        if (!empty($_FILES['archivo_excel']['size']) && $_FILES['archivo_excel']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo permitido (5 MB)']);
            return;
        }

        $tmp = $_FILES['archivo_excel']['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo subido']);
            return;
        }

        try {
            $readerType = ($ext === 'xlsx') ? 'Xlsx' : 'Xls';
            $reader = IOFactory::createReader($readerType);
            $spreadsheet = $reader->load($tmp);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $rows = [];
            $totalFilas = count($sheetData);
            for ($i = 2; $i <= $totalFilas; $i++) {
                if (empty($sheetData[$i])) {
                    continue;
                }
                $fila = $sheetData[$i];
                $razon = $this->_normalizar_celda_excel($fila['A'] ?? '');
                $rfc = strtoupper(preg_replace('/\s+/', '', $this->_normalizar_celda_excel($fila['C'] ?? '')));

                if ($razon === '' && $rfc === '') {
                    continue;
                }

                if ($rfc === '') {
                    continue;
                }

                if (preg_match('/^\(EJEMPLO\)/i', $razon)) {
                    continue;
                }

                $rows[] = [
                    '_linea' => $i,
                    'razon_social' => $razon,
                    'nombre_comercial' => $this->_normalizar_celda_excel($fila['B'] ?? ''),
                    'rfc' => $rfc,
                    'tipo_proveedor' => $this->_normalizar_celda_excel($fila['D'] ?? ''),
                    'contacto_principal' => $this->_normalizar_celda_excel($fila['E'] ?? ''),
                    'telefono' => $this->_normalizar_celda_excel($fila['F'] ?? ''),
                    'telefono_alternativo' => $this->_normalizar_celda_excel($fila['G'] ?? ''),
                    'email' => $this->_normalizar_celda_excel($fila['H'] ?? ''),
                    'direccion' => $this->_normalizar_celda_excel($fila['I'] ?? ''),
                    'ciudad' => $this->_normalizar_celda_excel($fila['J'] ?? ''),
                    'estado' => $this->_normalizar_celda_excel($fila['K'] ?? ''),
                    'codigo_postal' => $this->_normalizar_celda_excel($fila['L'] ?? ''),
                    'pais' => $this->_normalizar_celda_excel($fila['M'] ?? ''),
                    'dias_credito' => $this->_normalizar_celda_excel($fila['N'] ?? ''),
                    'observaciones' => $this->_normalizar_celda_excel($fila['O'] ?? ''),
                ];
            }

            if (empty($rows)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay proveedores para importar. Reemplace la fila de ejemplo en la plantilla o agregue filas con RFC en la columna C.',
                ]);
                return;
            }

            $usuario_id = $this->session->userdata('id');
            $result = $this->ProveedoresModel->importar_masivo($rows, $usuario_id);

            if ($result['inserted'] > 0) {
                $this->registrar_bitacora(
                    'Carga masiva proveedores: ' . $result['inserted'] . ' insertados, ' . $result['skipped'] . ' omitidos, ' . $result['errors'] . ' errores',
                    'Proveedores'
                );
            }

            echo json_encode([
                'success' => $result['errors'] === 0 && ($result['inserted'] > 0 || $result['skipped'] > 0),
                'partial' => $result['inserted'] > 0 && ($result['errors'] > 0 || $result['skipped'] > 0),
                'message' => 'Carga finalizada: ' . $result['inserted'] . ' insertados, ' . $result['skipped'] . ' omitidos, ' . $result['errors'] . ' errores',
                'resultado' => $result,
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Normaliza valores leídos de Excel (números, notación científica, espacios).
     */
    private function _normalizar_celda_excel($valor) {
        if ($valor === null || $valor === '') {
            return '';
        }
        if (is_float($valor) || is_int($valor)) {
            if (is_float($valor) && floor($valor) == $valor) {
                return (string) (int) $valor;
            }
            return rtrim(rtrim(sprintf('%.10F', (float) $valor), '0'), '.');
        }
        return trim((string) $valor);
    }
}
