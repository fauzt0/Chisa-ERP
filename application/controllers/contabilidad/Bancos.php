<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bancos extends CI_Controller {
    
    public $viewData = [];
    public $outputData = [];
    
    public function __construct() {
        parent::__construct();
        $this->load->library("Init_controller");
        $this->load->model('Contabilidad/ContabilidadModel');
        
        // General viewdata for view files
        $this->viewData = [
            'success'     => true,
            'statusCode'  => get_status_code_by_result('emptyresult'),
            'message'     => 'Respuesta sin contenido',  
            'error'       => '',
            'pageTitle'   => '',      
            'headTitle'   => '',   
            'pageView'    => '',
            'pageScript'  => '',
            'breadcrumb'  => '',
            'validate'    => '',
            'response'    => [],
        ];     

        $this->outputData = [
            'success'     => true,  
            'statusCode'  => get_status_code_by_result('emptyresult'),
            'message'     => 'Respuesta sin contenido',
            'error'       => '',      
            'response'    => [],
        ];
    }
    
    /**
     * Vista principal de bancos
     */
    public function index() {
        setViewSuccess('Bancos cargados correctamente');
        $this->viewData['pageTitle'] = 'Bancos';
        $this->viewData['headTitle'] = 'Gestión de Bancos';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Bancos';
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/bancos/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene cuentas bancarias para DataTable (AJAX)
     */
    public function lista_cuentas_ajax() {
        $this->db->select('cb.*, cc.codigo as cuenta_contable_codigo, cc.nombre as cuenta_contable_nombre');
        $this->db->from('cuentas_bancarias cb');
        $this->db->join('cuentas_contables cc', 'cb.cuenta_contable_id = cc.id', 'left');
        $this->db->order_by('cb.banco', 'ASC');
        $cuentas = $this->db->get()->result();
        
        $data = [];
        foreach($cuentas as $cuenta) {
            $row = [];
            
            // Banco
            $row[] = '<strong>' . $cuenta->banco . '</strong>';
            
            // Número de Cuenta
            $row[] = $cuenta->numero_cuenta;
            
            // CLABE
            $row[] = $cuenta->clabe ?: 'N/A';
            
            // Tipo
            $badge_tipo = '';
            switch($cuenta->tipo_cuenta) {
                case 'Cheques': $badge_tipo = 'primary'; break;
                case 'Inversión': $badge_tipo = 'success'; break;
                case 'Nómina': $badge_tipo = 'warning'; break;
                case 'Ahorro': $badge_tipo = 'info'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_tipo.'">'.$cuenta->tipo_cuenta.'</span>';
            
            // Saldo Actual
            $row[] = '$' . number_format($cuenta->saldo_actual, 2);
            
            // Cuenta Contable
            $row[] = $cuenta->cuenta_contable_codigo ? 
                $cuenta->cuenta_contable_codigo . ' - ' . $cuenta->cuenta_contable_nombre : 
                'N/A';
            
            // Estatus
            $badge_estatus = $cuenta->estatus == 'Activa' ? 'success' : 'secondary';
            $row[] = '<span class="badge bg-'.$badge_estatus.'">'.$cuenta->estatus.'</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verCuentaBancaria('.$cuenta->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="editarCuentaBancaria('.$cuenta->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="verMovimientos('.$cuenta->id.')" title="Ver Movimientos">
                    <i class="fas fa-list"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="conciliar('.$cuenta->id.')" title="Conciliar">
                    <i class="fas fa-check-double"></i>
                </button>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($cuentas),
            "recordsFiltered" => count($cuentas),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene movimientos bancarios para DataTable (AJAX)
     */
    public function lista_movimientos_ajax() {
        $cuenta_id = $this->input->post('cuenta_id');
        
        $this->db->select('mb.*, cb.banco, cb.numero_cuenta');
        $this->db->from('movimientos_bancarios mb');
        $this->db->join('cuentas_bancarias cb', 'mb.cuenta_bancaria_id = cb.id');
        
        if($cuenta_id) {
            $this->db->where('mb.cuenta_bancaria_id', $cuenta_id);
        }
        
        $this->db->order_by('mb.fecha', 'DESC');
        $this->db->order_by('mb.id', 'DESC');
        $movimientos = $this->db->get()->result();
        
        $data = [];
        foreach($movimientos as $mov) {
            $row = [];
            
            // Fecha
            $row[] = date('d/m/Y', strtotime($mov->fecha));
            
            // Banco/Cuenta
            $row[] = $mov->banco . '<br><small>' . $mov->numero_cuenta . '</small>';
            
            // Tipo
            $badge_tipo = '';
            switch($mov->tipo_movimiento) {
                case 'Depósito': $badge_tipo = 'success'; break;
                case 'Retiro': $badge_tipo = 'danger'; break;
                case 'Transferencia': $badge_tipo = 'primary'; break;
                case 'Comisión': $badge_tipo = 'warning'; break;
                case 'Interés': $badge_tipo = 'info'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_tipo.'">'.$mov->tipo_movimiento.'</span>';
            
            // Concepto
            $row[] = $mov->concepto;
            
            // Referencia
            $row[] = $mov->referencia ?: 'N/A';
            
            // Monto
            $color = in_array($mov->tipo_movimiento, ['Depósito', 'Interés']) ? 'success' : 'danger';
            $signo = in_array($mov->tipo_movimiento, ['Depósito', 'Interés']) ? '+' : '-';
            $row[] = '<span class="text-'.$color.'">'.$signo.'$' . number_format($mov->monto, 2).'</span>';
            
            // Saldo
            $row[] = '$' . number_format($mov->saldo, 2);
            
            // Conciliado
            $row[] = $mov->conciliado ? 
                '<span class="badge bg-success"><i class="fas fa-check"></i> Sí</span>' : 
                '<span class="badge bg-warning"><i class="fas fa-clock"></i> No</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verMovimiento('.$mov->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            
            if(!$mov->conciliado) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-success" onclick="marcarConciliado('.$mov->id.')" title="Marcar Conciliado">
                    <i class="fas fa-check"></i>
                </button>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($movimientos),
            "recordsFiltered" => count($movimientos),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Crea cuenta bancaria (AJAX)
     */
    public function crear_cuenta_ajax() {
        $data = [
            'banco' => $this->input->post('banco'),
            'numero_cuenta' => $this->input->post('numero_cuenta'),
            'clabe' => $this->input->post('clabe'),
            'tipo_cuenta' => $this->input->post('tipo_cuenta'),
            'moneda' => $this->input->post('moneda') ?: 'MXN',
            'saldo_inicial' => $this->input->post('saldo_inicial') ?: 0,
            'saldo_actual' => $this->input->post('saldo_inicial') ?: 0,
            'cuenta_contable_id' => $this->input->post('cuenta_contable_id') ?: NULL,
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        $result = $this->db->insert('cuentas_bancarias', $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cuenta bancaria creada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cuenta bancaria']);
        }
    }
    
    /**
     * Crea movimiento bancario (AJAX)
     */
    public function crear_movimiento_ajax() {
        $cuenta_id = $this->input->post('cuenta_bancaria_id');
        $tipo = $this->input->post('tipo_movimiento');
        $monto = floatval($this->input->post('monto'));
        
        // Obtener saldo actual
        $cuenta = $this->db->get_where('cuentas_bancarias', ['id' => $cuenta_id])->row();
        
        if(!$cuenta) {
            echo json_encode(['success' => false, 'message' => 'Cuenta no encontrada']);
            return;
        }
        
        // Calcular nuevo saldo
        $saldo_anterior = $cuenta->saldo_actual;
        if(in_array($tipo, ['Depósito', 'Interés'])) {
            $nuevo_saldo = $saldo_anterior + $monto;
        } else {
            $nuevo_saldo = $saldo_anterior - $monto;
        }
        
        // Crear movimiento
        $data = [
            'cuenta_bancaria_id' => $cuenta_id,
            'fecha' => $this->input->post('fecha'),
            'tipo_movimiento' => $tipo,
            'concepto' => $this->input->post('concepto'),
            'referencia' => $this->input->post('referencia'),
            'monto' => $monto,
            'saldo' => $nuevo_saldo,
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        $this->db->trans_start();
        
        $this->db->insert('movimientos_bancarios', $data);
        $movimiento_id = $this->db->insert_id();
        
        // Actualizar saldo de cuenta
        $this->db->where('id', $cuenta_id);
        $this->db->update('cuentas_bancarias', ['saldo_actual' => $nuevo_saldo]);
        
        // Generar póliza si está configurado
        if($this->input->post('generar_poliza')) {
            $poliza_id = $this->generar_poliza_movimiento($movimiento_id);
            if($poliza_id) {
                $this->db->where('id', $movimiento_id);
                $this->db->update('movimientos_bancarios', ['poliza_id' => $poliza_id]);
            }
        }
        
        $this->db->trans_complete();
        
        if($this->db->trans_status()) {
            echo json_encode(['success' => true, 'message' => 'Movimiento registrado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar movimiento']);
        }
    }
    
    /**
     * Marca movimiento como conciliado (AJAX)
     */
    public function conciliar_movimiento_ajax() {
        $id = $this->input->post('id');
        
        $data = [
            'conciliado' => 1,
            'fecha_conciliacion' => date('Y-m-d')
        ];
        
        $this->db->where('id', $id);
        $result = $this->db->update('movimientos_bancarios', $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Movimiento conciliado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al conciliar']);
        }
    }
    
    /**
     * Genera póliza para movimiento bancario
     */
    private function generar_poliza_movimiento($movimiento_id) {
        $mov = $this->db->get_where('movimientos_bancarios', ['id' => $movimiento_id])->row();
        
        if(!$mov) return false;
        
        $cuenta_bancaria = $this->db->get_where('cuentas_bancarias', ['id' => $mov->cuenta_bancaria_id])->row();
        
        if(!$cuenta_bancaria || !$cuenta_bancaria->cuenta_contable_id) return false;
        
        $periodo = $this->ContabilidadModel->get_periodo_actual();
        
        // Determinar tipo de póliza y cuentas
        $tipo_poliza = in_array($mov->tipo_movimiento, ['Depósito', 'Interés']) ? 'Ingresos' : 'Egresos';
        
        $data_poliza = [
            'folio' => 'BCO-' . str_pad($movimiento_id, 6, '0', STR_PAD_LEFT),
            'tipo_poliza' => $tipo_poliza,
            'fecha' => $mov->fecha,
            'periodo_id' => $periodo->id,
            'concepto' => $mov->tipo_movimiento . ' - ' . $mov->concepto,
            'origen' => 'banco',
            'origen_id' => $movimiento_id,
            'total_debe' => $mov->monto,
            'total_haber' => $mov->monto,
            'usuario_creacion' => $this->session->userdata('user_id'),
            'estatus' => 'Autorizada',
            'usuario_autorizacion' => $this->session->userdata('user_id'),
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ];
        
        // Detalle según tipo de movimiento
        $detalle = [];
        
        if(in_array($mov->tipo_movimiento, ['Depósito', 'Interés'])) {
            $detalle = [
                [
                    'cuenta_id' => $cuenta_bancaria->cuenta_contable_id,
                    'concepto' => $mov->concepto,
                    'debe' => $mov->monto,
                    'haber' => 0,
                    'orden' => 1
                ],
                [
                    'cuenta_id' => $this->get_cuenta_id('4.2.01'), // Otros Ingresos
                    'concepto' => $mov->concepto,
                    'debe' => 0,
                    'haber' => $mov->monto,
                    'orden' => 2
                ]
            ];
        } else {
            $detalle = [
                [
                    'cuenta_id' => $this->get_cuenta_id('6.2.02'), // Comisiones Bancarias
                    'concepto' => $mov->concepto,
                    'debe' => $mov->monto,
                    'haber' => 0,
                    'orden' => 1
                ],
                [
                    'cuenta_id' => $cuenta_bancaria->cuenta_contable_id,
                    'concepto' => $mov->concepto,
                    'debe' => 0,
                    'haber' => $mov->monto,
                    'orden' => 2
                ]
            ];
        }
        
        return $this->ContabilidadModel->crear_poliza($data_poliza, $detalle);
    }
    
    /**
     * Obtiene cuentas bancarias para selectores (AJAX)
     */
    public function get_cuentas_ajax() {
        $this->db->select('id, banco, numero_cuenta, saldo_actual');
        $this->db->from('cuentas_bancarias');
        $this->db->where('estatus', 'Activa');
        $this->db->order_by('banco', 'ASC');
        $cuentas = $this->db->get()->result();
        
        echo json_encode(['success' => true, 'cuentas' => $cuentas]);
    }
    
    private function get_cuenta_id($codigo) {
        $cuenta = $this->db->get_where('cuentas_contables', ['codigo' => $codigo])->row();
        return $cuenta ? $cuenta->id : null;
    }
}
