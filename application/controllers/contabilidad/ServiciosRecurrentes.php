<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiciosRecurrentes extends MY_Controller {
    
    protected $modulo = 'Contabilidad';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Contabilidad/ContabilidadModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal de servicios recurrentes
     */
    public function index() {
        setViewSuccess('Servicios Recurrentes cargados correctamente');
        $this->viewData['pageTitle'] = 'Servicios Recurrentes';
        $this->viewData['headTitle'] = 'Servicios Recurrentes';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Servicios Recurrentes';
        
        // Obtener resumen del mes actual
        $this->viewData['mes_actual'] = date('Y-m');
        $this->viewData['resumen'] = $this->get_resumen_mes(date('Y-m'));
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/servicios_recurrentes/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene servicios para DataTable (AJAX)
     */
    public function lista_servicios_ajax() {
        $this->db->select('sr.*, p.nombre as proveedor_nombre, 
                          cc.codigo as cuenta_codigo, cc.nombre as cuenta_nombre,
                          cb.banco, cb.numero_cuenta');
        $this->db->from('servicios_recurrentes sr');
        $this->db->join('proveedores p', 'sr.proveedor_id = p.id', 'left');
        $this->db->join('cuentas_contables cc', 'sr.cuenta_contable_id = cc.id', 'left');
        $this->db->join('cuentas_bancarias cb', 'sr.cuenta_bancaria_id = cb.id', 'left');
        $this->db->order_by('sr.nombre_servicio', 'ASC');
        $servicios = $this->db->get()->result();
        
        $data = [];
        foreach($servicios as $servicio) {
            $row = [];
            
            // Nombre
            $row[] = '<strong>' . $servicio->nombre_servicio . '</strong>';
            
            // Tipo
            $badge_tipo = '';
            switch($servicio->tipo_servicio) {
                case 'Servicios Públicos': $badge_tipo = 'primary'; break;
                case 'Renta': $badge_tipo = 'success'; break;
                case 'Seguros': $badge_tipo = 'warning'; break;
                case 'Suscripciones': $badge_tipo = 'info'; break;
                case 'Mantenimiento': $badge_tipo = 'secondary'; break;
                default: $badge_tipo = 'dark'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_tipo.'">'.$servicio->tipo_servicio.'</span>';
            
            // Proveedor
            $row[] = $servicio->proveedor_nombre ?: 'N/A';
            
            // Frecuencia
            $row[] = $servicio->frecuencia;
            
            // Día de Vencimiento
            $row[] = 'Día ' . $servicio->dia_vencimiento;
            
            // Monto Estimado
            $row[] = '$' . number_format($servicio->monto_estimado, 2);
            
            // Cuenta Contable
            $row[] = $servicio->cuenta_codigo ? 
                $servicio->cuenta_codigo . ' - ' . $servicio->cuenta_nombre : 
                'N/A';
            
            // Estatus
            $badge_estatus = $servicio->activo ? 'success' : 'secondary';
            $row[] = '<span class="badge bg-'.$badge_estatus.'">'.($servicio->activo ? 'Activo' : 'Inactivo').'</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verServicio('.$servicio->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="editarServicio('.$servicio->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="registrarPago('.$servicio->id.')" title="Registrar Pago">
                    <i class="fas fa-dollar-sign"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="verHistorial('.$servicio->id.')" title="Historial">
                    <i class="fas fa-history"></i>
                </button>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($servicios),
            "recordsFiltered" => count($servicios),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene pagos del mes para DataTable (AJAX)
     */
    public function lista_pagos_ajax() {
        $periodo = $this->input->post('periodo') ?: date('Y-m');
        
        $this->db->select('psr.*, sr.nombre_servicio, sr.tipo_servicio');
        $this->db->from('pagos_servicios_recurrentes psr');
        $this->db->join('servicios_recurrentes sr', 'psr.servicio_recurrente_id = sr.id');
        $this->db->where('psr.periodo', $periodo);
        $this->db->order_by('psr.fecha_vencimiento', 'ASC');
        $pagos = $this->db->get()->result();
        
        $data = [];
        foreach($pagos as $pago) {
            $row = [];
            
            // Servicio
            $row[] = $pago->nombre_servicio;
            
            // Tipo
            $row[] = $pago->tipo_servicio;
            
            // Fecha Vencimiento
            $row[] = date('d/m/Y', strtotime($pago->fecha_vencimiento));
            
            // Monto
            $row[] = '$' . number_format($pago->monto, 2);
            
            // Fecha Pago
            $row[] = $pago->fecha_pago ? date('d/m/Y', strtotime($pago->fecha_pago)) : 'N/A';
            
            // Estatus
            $badge_estatus = '';
            switch($pago->estatus) {
                case 'Pendiente': $badge_estatus = 'warning'; break;
                case 'Pagado': $badge_estatus = 'success'; break;
                case 'Vencido': $badge_estatus = 'danger'; break;
                case 'Cancelado': $badge_estatus = 'secondary'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_estatus.'">'.$pago->estatus.'</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verPago('.$pago->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            
            if($pago->estatus == 'Pendiente') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-success" onclick="pagarServicio('.$pago->id.')" title="Registrar Pago">
                    <i class="fas fa-check"></i>
                </button>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($pagos),
            "recordsFiltered" => count($pagos),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Crea servicio recurrente (AJAX)
     */
    public function crear_servicio_ajax() {
        $data = [
            'proveedor_id' => $this->input->post('proveedor_id') ?: NULL,
            'nombre_servicio' => $this->input->post('nombre_servicio'),
            'descripcion' => $this->input->post('descripcion'),
            'tipo_servicio' => $this->input->post('tipo_servicio'),
            'frecuencia' => $this->input->post('frecuencia'),
            'dia_vencimiento' => $this->input->post('dia_vencimiento'),
            'monto_estimado' => $this->input->post('monto_estimado'),
            'cuenta_contable_id' => $this->input->post('cuenta_contable_id') ?: NULL,
            'cuenta_bancaria_id' => $this->input->post('cuenta_bancaria_id') ?: NULL,
            'fecha_inicio' => $this->input->post('fecha_inicio'),
            'notas' => $this->input->post('notas'),
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        $result = $this->db->insert('servicios_recurrentes', $data);
        
        if($result) {
            $servicio_id = $this->db->insert_id();
            
            // Generar pagos para los próximos 12 meses
            $this->generar_pagos_futuros($servicio_id);
            
            echo json_encode(['success' => true, 'message' => 'Servicio creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear servicio']);
        }
    }
    
    /**
     * Registra pago de servicio (AJAX)
     */
    public function registrar_pago_ajax() {
        $pago_id = $this->input->post('pago_id');
        $fecha_pago = $this->input->post('fecha_pago');
        $monto = $this->input->post('monto');
        $referencia = $this->input->post('referencia');
        $notas = $this->input->post('notas');
        
        // Obtener pago
        $pago = $this->db->get_where('pagos_servicios_recurrentes', ['id' => $pago_id])->row();
        
        if(!$pago) {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
            return;
        }
        
        // Obtener servicio
        $servicio = $this->db->get_where('servicios_recurrentes', ['id' => $pago->servicio_recurrente_id])->row();
        
        $this->db->trans_start();
        
        // Actualizar pago
        $this->db->where('id', $pago_id);
        $this->db->update('pagos_servicios_recurrentes', [
            'fecha_pago' => $fecha_pago,
            'monto' => $monto,
            'referencia' => $referencia,
            'notas' => $notas,
            'estatus' => 'Pagado',
            'usuario_registro' => $this->session->userdata('user_id')
        ]);
        
        // Generar póliza y movimiento bancario
        $poliza_id = $this->generar_poliza_pago($pago_id, $servicio, $monto, $fecha_pago);
        $movimiento_id = $this->generar_movimiento_bancario($pago_id, $servicio, $monto, $fecha_pago);
        
        // Actualizar referencias
        $this->db->where('id', $pago_id);
        $this->db->update('pagos_servicios_recurrentes', [
            'poliza_id' => $poliza_id,
            'movimiento_bancario_id' => $movimiento_id
        ]);
        
        $this->db->trans_complete();
        
        if($this->db->trans_status()) {
            echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar pago']);
        }
    }
    
    /**
     * Métodos auxiliares
     */
    
    private function get_resumen_mes($periodo) {
        $this->db->select('COUNT(*) as total, 
                          SUM(CASE WHEN estatus = "Pendiente" THEN 1 ELSE 0 END) as pendientes,
                          SUM(CASE WHEN estatus = "Pagado" THEN 1 ELSE 0 END) as pagados,
                          SUM(CASE WHEN estatus = "Vencido" THEN 1 ELSE 0 END) as vencidos,
                          SUM(monto) as total_monto,
                          SUM(CASE WHEN estatus = "Pagado" THEN monto ELSE 0 END) as monto_pagado');
        $this->db->from('pagos_servicios_recurrentes');
        $this->db->where('periodo', $periodo);
        return $this->db->get()->row();
    }
    
    private function generar_pagos_futuros($servicio_id) {
        $servicio = $this->db->get_where('servicios_recurrentes', ['id' => $servicio_id])->row();
        
        if(!$servicio) return;
        
        $fecha_inicio = $servicio->fecha_inicio ?: date('Y-m-d');
        $meses = 12;
        
        for($i = 0; $i < $meses; $i++) {
            $periodo = date('Y-m', strtotime($fecha_inicio . " +$i months"));
            $año = substr($periodo, 0, 4);
            $mes = substr($periodo, 5, 2);
            $dia = min($servicio->dia_vencimiento, cal_days_in_month(CAL_GREGORIAN, $mes, $año));
            $fecha_vencimiento = "$año-$mes-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe
            $existe = $this->db->get_where('pagos_servicios_recurrentes', [
                'servicio_recurrente_id' => $servicio_id,
                'periodo' => $periodo
            ])->row();
            
            if(!$existe) {
                $this->db->insert('pagos_servicios_recurrentes', [
                    'servicio_recurrente_id' => $servicio_id,
                    'periodo' => $periodo,
                    'fecha_vencimiento' => $fecha_vencimiento,
                    'monto' => $servicio->monto_estimado,
                    'estatus' => 'Pendiente'
                ]);
            }
        }
    }
    
    private function generar_poliza_pago($pago_id, $servicio, $monto, $fecha) {
        $periodo = $this->ContabilidadModel->get_periodo_actual();
        
        $data_poliza = [
            'folio' => 'SRV-' . str_pad($pago_id, 6, '0', STR_PAD_LEFT),
            'tipo_poliza' => 'Egresos',
            'fecha' => $fecha,
            'periodo_id' => $periodo->id,
            'concepto' => 'Pago de ' . $servicio->nombre_servicio,
            'origen' => 'servicio_recurrente',
            'origen_id' => $pago_id,
            'total_debe' => $monto,
            'total_haber' => $monto,
            'usuario_creacion' => $this->session->userdata('user_id'),
            'estatus' => 'Autorizada',
            'usuario_autorizacion' => $this->session->userdata('user_id'),
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ];
        
        $detalle = [
            [
                'cuenta_id' => $servicio->cuenta_contable_id,
                'concepto' => 'Pago de ' . $servicio->nombre_servicio,
                'debe' => $monto,
                'haber' => 0,
                'orden' => 1
            ],
            [
                'cuenta_id' => $this->get_cuenta_bancos(),
                'concepto' => 'Pago de ' . $servicio->nombre_servicio,
                'debe' => 0,
                'haber' => $monto,
                'orden' => 2
            ]
        ];
        
        return $this->ContabilidadModel->crear_poliza($data_poliza, $detalle);
    }
    
    private function generar_movimiento_bancario($pago_id, $servicio, $monto, $fecha) {
        if(!$servicio->cuenta_bancaria_id) return null;
        
        // Obtener saldo actual
        $cuenta = $this->db->get_where('cuentas_bancarias', ['id' => $servicio->cuenta_bancaria_id])->row();
        $nuevo_saldo = $cuenta->saldo_actual - $monto;
        
        // Crear movimiento
        $this->db->insert('movimientos_bancarios', [
            'cuenta_bancaria_id' => $servicio->cuenta_bancaria_id,
            'fecha' => $fecha,
            'tipo_movimiento' => 'Retiro',
            'concepto' => 'Pago de ' . $servicio->nombre_servicio,
            'monto' => $monto,
            'saldo' => $nuevo_saldo,
            'usuario_creacion' => $this->session->userdata('user_id')
        ]);
        
        // Actualizar saldo
        $this->db->where('id', $servicio->cuenta_bancaria_id);
        $this->db->update('cuentas_bancarias', ['saldo_actual' => $nuevo_saldo]);
        
        return $this->db->insert_id();
    }
    
    private function get_cuenta_bancos() {
        $cuenta = $this->db->get_where('cuentas_contables', ['codigo' => '1.1.01.003'])->row();
        return $cuenta ? $cuenta->id : null;
    }
}
