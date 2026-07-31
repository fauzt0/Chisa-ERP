<?php
/**
 * Clientes Controller
 * 
 * Gestión de clientes del sistema CRM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Clientes extends MY_Controller {
    
    protected $modulo = 'Clientes (CRM)';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/ClientesModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Clientes';
        $this->viewData['headTitle'] = 'Gestión de Clientes';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Clientes';
        
        // Obtener estadísticas
        $stats = $this->ClientesModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'ventas/clientes/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de clientes para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->ClientesModel->get_datatables();
        $data = [];
        
        foreach ($list as $cliente) {
            $row = [];
            
            // Código
            $row[] = $cliente->codigo;
            
            // Razón Social / Nombre Comercial
            $nombre = '<strong>' . $cliente->razon_social . '</strong>';
            if($cliente->nombre_comercial) {
                $nombre .= '<br><small class="text-muted">' . $cliente->nombre_comercial . '</small>';
            }
            if((int)($cliente->total_ordenes ?? 0) > 0) {
                $nombre .= '<br><span class="badge bg-light text-dark mt-1" style="font-size:0.7rem;"><i class="fas fa-shopping-cart"></i> ' . (int)$cliente->total_ordenes . ' órdenes</span>';
            }
            $row[] = $nombre;
            
            // RFC
            $row[] = $cliente->rfc;
            
            // Contacto
            $contacto = '';
            if($cliente->telefono) {
                $contacto .= '<i class="fas fa-phone"></i> ' . $cliente->telefono . '<br>';
            }
            if($cliente->email) {
                $contacto .= '<i class="fas fa-envelope"></i> ' . $cliente->email;
            }
            $row[] = $contacto ?: '<span class="text-muted">Sin datos</span>';

            // Ciudad
            $ubicacion = '';
            if($cliente->ciudad) {
                $ubicacion = $cliente->ciudad;
                if($cliente->estado) {
                    $ubicacion .= ', ' . $cliente->estado;
                }
            }
            $row[] = $ubicacion ?: '<span class="text-muted">—</span>';

            // Saldo pendiente
            $saldo = (float) ($cliente->saldo_pendiente ?? 0);
            if($saldo > 0) {
                $row[] = '<span class="text-danger fw-semibold">$' . number_format($saldo, 2) . '</span>';
            } else {
                $row[] = '<span class="text-muted">$0.00</span>';
            }
            
            // Tipo de cliente
            $tipo_badges = [
                'Regular' => 'primary',
                'Mostrador' => 'secondary',
                'Gobierno' => 'success',
                'Licitación' => 'warning',
                'Distribuidor' => 'info'
            ];
            $badge_color = $tipo_badges[$cliente->tipo_cliente] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge_color . '">' . $cliente->tipo_cliente . '</span>';
            
            // Estatus
            $estatus_badges = [
                'Activo' => 'success',
                'Inactivo' => 'secondary',
                'Suspendido' => 'danger'
            ];
            $badge_color = $estatus_badges[$cliente->estatus] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge_color . '">' . $cliente->estatus . '</span>';
            
            // Acciones
            $acciones = '<div class="btn-acciones-crm">
            <button type="button" class="btn btn-sm btn-info" onclick="verCliente('.$cliente->id.')" title="Ver detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-primary" onclick="editarCliente('.$cliente->id.')" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarCliente('.$cliente->id.')" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
            </div>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            'recordsTotal' => $this->ClientesModel->count_all(),
            'recordsFiltered' => $this->ClientesModel->count_filtered(),
            'data' => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene un cliente (AJAX)
     */
    public function get_cliente_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $cliente = $this->ClientesModel->get_cliente($id);
        
        if($cliente) {
            echo json_encode(['success' => true, 'cliente' => $cliente]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        }
    }
    
    /**
     * Crea un nuevo cliente (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => strtoupper($this->input->post('rfc')),
            'regimen_fiscal' => $this->input->post('regimen_fiscal'),
            'contacto_nombre' => $this->input->post('contacto_nombre'),
            'telefono' => $this->input->post('telefono'),
            'email' => $this->input->post('email'),
            'calle' => $this->input->post('calle'),
            'numero_exterior' => $this->input->post('numero_exterior'),
            'numero_interior' => $this->input->post('numero_interior'),
            'colonia' => $this->input->post('colonia'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'limite_credito' => $this->input->post('limite_credito') ?: 0,
            'dias_credito' => $this->input->post('dias_credito') ?: 0,
            'tipo_cliente' => $this->input->post('tipo_cliente'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->ClientesModel->crear_cliente($data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cliente creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cliente']);
        }
    }
    
    /**
     * Actualiza un cliente (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $data = [
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => strtoupper($this->input->post('rfc')),
            'regimen_fiscal' => $this->input->post('regimen_fiscal'),
            'contacto_nombre' => $this->input->post('contacto_nombre'),
            'telefono' => $this->input->post('telefono'),
            'email' => $this->input->post('email'),
            'calle' => $this->input->post('calle'),
            'numero_exterior' => $this->input->post('numero_exterior'),
            'numero_interior' => $this->input->post('numero_interior'),
            'colonia' => $this->input->post('colonia'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'limite_credito' => $this->input->post('limite_credito') ?: 0,
            'dias_credito' => $this->input->post('dias_credito') ?: 0,
            'tipo_cliente' => $this->input->post('tipo_cliente'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->ClientesModel->actualizar_cliente($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cliente']);
        }
    }
    
    /**
     * Elimina un cliente (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $result = $this->ClientesModel->eliminar_cliente($id);
        echo json_encode($result);
    }
    
    /**
     * Obtiene clientes para select (AJAX)
     */
    public function get_clientes_select_ajax() {
        $clientes = $this->ClientesModel->get_clientes_select();
        echo json_encode(['success' => true, 'clientes' => $clientes]);
    }

    /**
     * Órdenes de venta de un cliente (AJAX)
     */
    public function get_ordenes_cliente_ajax() {
        $cliente_id = $this->input->post('cliente_id');
        if(!$cliente_id) {
            echo json_encode(['success' => false, 'message' => 'Cliente requerido']);
            return;
        }

        $ordenes = $this->ClientesModel->get_ordenes_cliente($cliente_id);
        echo json_encode(['success' => true, 'ordenes' => $ordenes]);
    }

    /**
     * Historial paginado de órdenes de venta de un cliente (AJAX)
     */
    public function get_historial_ventas_ajax($cliente_id = 0, $limit = 10, $offset = 0) {
        $cliente_id = (int) ($cliente_id ?: $this->input->post('cliente_id'));
        $limit = (int) ($limit ?: $this->input->post('limit') ?: 10);
        $offset = (int) ($offset ?: $this->input->post('offset') ?: 0);

        if(!$cliente_id) {
            echo json_encode(['success' => false, 'message' => 'Cliente requerido']);
            return;
        }

        $ordenes = $this->ClientesModel->get_historial_ventas($cliente_id, $limit, $offset);
        $total = $this->ClientesModel->count_historial_ventas($cliente_id);

        echo json_encode([
            'success' => true,
            'ordenes' => $ordenes,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Historial paginado de cotizaciones de un cliente (AJAX)
     */
    public function get_historial_cotizaciones_ajax($cliente_id = 0, $limit = 10, $offset = 0) {
        $cliente_id = (int) ($cliente_id ?: $this->input->post('cliente_id'));
        $limit = (int) ($limit ?: $this->input->post('limit') ?: 10);
        $offset = (int) ($offset ?: $this->input->post('offset') ?: 0);

        if(!$cliente_id) {
            echo json_encode(['success' => false, 'message' => 'Cliente requerido']);
            return;
        }

        $cotizaciones = $this->ClientesModel->get_historial_cotizaciones($cliente_id, $limit, $offset);
        $total = $this->ClientesModel->count_historial_cotizaciones($cliente_id);

        echo json_encode([
            'success' => true,
            'cotizaciones' => $cotizaciones,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Convierte una cotización en orden de venta (AJAX)
     */
    public function convertir_cotizacion_ajax() {
        $orden_id = $this->input->post('orden_id');
        if(!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Orden requerida']);
            return;
        }

        $this->load->model('Ventas/VentasModel');
        $orden = $this->VentasModel->get_orden_completa($orden_id);

        if(!$orden) {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
            return;
        }

        if($orden->estatus != 'Cotización') {
            echo json_encode(['success' => false, 'message' => 'La orden no está en estatus Cotización']);
            return;
        }

        $this->VentasModel->confirmar_orden($orden_id);

        echo json_encode([
            'success' => true,
            'message' => 'Cotización convertida a orden de venta correctamente'
        ]);
    }

    /**
     * Descarga plantilla Excel para carga masiva de clientes
     */
    public function descargar_plantilla_excel() {
        $headers = [
            'Razón social *',
            'Nombre comercial',
            'RFC *',
            'Régimen fiscal',
            'Contacto',
            'Teléfono',
            'Email',
            'Calle',
            'Núm. Ext.',
            'Núm. Int.',
            'Colonia',
            'Ciudad',
            'Estado',
            'Código postal',
            'Límite crédito',
            'Días crédito',
            'Tipo cliente',
            'Estatus',
        ];

        $ejemplo = [
            '(EJEMPLO) Construcciones del Norte SA de CV',
            'ConstruNorte',
            'CDN850101ABC',
            '601',
            'Lic. García López',
            '8181234567',
            'contacto@constructora.mx',
            'Av. Industrial 100',
            '100',
            'A',
            'Centro',
            'Monterrey',
            'Nuevo León',
            '64000',
            '50000',
            '30',
            'Regular',
            'Activo',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clientes');

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
        $instrucciones->setCellValue('A3', '1. Capture sus clientes en la hoja «Clientes» desde la fila 2.');
        $instrucciones->setCellValue('A4', '2. La fila 2 es solo un ejemplo — reemplácela o elimínela antes de importar.');
        $instrucciones->setCellValue('A5', '3. Campos obligatorios: Razón social y RFC.');
        $instrucciones->setCellValue('A6', '4. Tipo cliente: Regular | Mostrador | Gobierno | Licitación | Distribuidor (opcional, default Regular).');
        $instrucciones->setCellValue('A7', '5. Estatus: Activo | Inactivo | Suspendido (opcional, default Activo).');
        $instrucciones->setCellValue('A8', '6. Los RFC duplicados (en el archivo o en el sistema) se omiten automáticamente.');
        $instrucciones->setCellValue('A9', '7. No modifique el orden de las columnas en la fila 1.');
        $instrucciones->getStyle('A1')->getFont()->setBold(true);
        $instrucciones->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="plantilla_clientes_erp.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Procesa carga masiva de clientes desde Excel (AJAX)
     */
    public function importar_excel_ajax() {
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

                if (preg_match('/^\(EJEMPLO\)/i', $razon)) {
                    continue;
                }

                $rows[] = [
                    '_linea' => $i,
                    'razon_social' => $razon,
                    'nombre_comercial' => $this->_normalizar_celda_excel($fila['B'] ?? ''),
                    'rfc' => $rfc,
                    'regimen_fiscal' => $this->_normalizar_celda_excel($fila['D'] ?? ''),
                    'contacto_nombre' => $this->_normalizar_celda_excel($fila['E'] ?? ''),
                    'telefono' => $this->_normalizar_celda_excel($fila['F'] ?? ''),
                    'email' => $this->_normalizar_celda_excel($fila['G'] ?? ''),
                    'calle' => $this->_normalizar_celda_excel($fila['H'] ?? ''),
                    'numero_exterior' => $this->_normalizar_celda_excel($fila['I'] ?? ''),
                    'numero_interior' => $this->_normalizar_celda_excel($fila['J'] ?? ''),
                    'colonia' => $this->_normalizar_celda_excel($fila['K'] ?? ''),
                    'ciudad' => $this->_normalizar_celda_excel($fila['L'] ?? ''),
                    'estado' => $this->_normalizar_celda_excel($fila['M'] ?? ''),
                    'codigo_postal' => $this->_normalizar_celda_excel($fila['N'] ?? ''),
                    'limite_credito' => $this->_normalizar_celda_excel($fila['O'] ?? ''),
                    'dias_credito' => $this->_normalizar_celda_excel($fila['P'] ?? ''),
                    'tipo_cliente' => $this->_normalizar_celda_excel($fila['Q'] ?? ''),
                    'estatus' => $this->_normalizar_celda_excel($fila['R'] ?? ''),
                ];
            }

            if (empty($rows)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay clientes para importar. Reemplace la fila de ejemplo en la plantilla o agregue filas con RFC en la columna C.',
                ]);
                return;
            }

            $usuario_id = $this->session->userdata('id');
            $result = $this->ClientesModel->importar_masivo($rows, $usuario_id);

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
     * Normaliza valores leídos de Excel (números, notación científica, espacios)
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
