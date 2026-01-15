<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nomina extends MY_Controller {
    
    protected $modulo = 'Contabilidad';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Contabilidad/ContabilidadModel');
        $this->load->model('Contabilidad/NominaModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal de nóminas
     */
    public function index() {
        setViewSuccess('Nóminas cargadas correctamente');
        $this->viewData['pageTitle'] = 'Nómina';
        $this->viewData['headTitle'] = 'Gestión de Nómina';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Nómina';
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/nomina/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene nóminas para DataTable (AJAX)
     */
    public function lista_ajax() {
        $this->db->select('*');
        $this->db->from('nominas');
        $this->db->order_by('fecha_pago', 'DESC');
        $nominas = $this->db->get()->result();
        
        $data = [];
        foreach($nominas as $nomina) {
            $row = [];
            
            // Folio
            $row[] = '<strong>' . $nomina->folio . '</strong>';
            
            // Tipo
            $badge_tipo = '';
            switch($nomina->tipo_nomina) {
                case 'Semanal': $badge_tipo = 'primary'; break;
                case 'Quincenal': $badge_tipo = 'success'; break;
                case 'Mensual': $badge_tipo = 'info'; break;
                case 'Extraordinaria': $badge_tipo = 'warning'; break;
                case 'Aguinaldo': $badge_tipo = 'danger'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_tipo.'">'.$nomina->tipo_nomina.'</span>';
            
            // Periodo
            $row[] = date('d/m/Y', strtotime($nomina->periodo_inicio)) . ' - ' . date('d/m/Y', strtotime($nomina->periodo_fin));
            
            // Fecha de Pago
            $row[] = date('d/m/Y', strtotime($nomina->fecha_pago));
            
            // Totales
            $row[] = '$' . number_format($nomina->total_percepciones, 2);
            $row[] = '$' . number_format($nomina->total_deducciones, 2);
            $row[] = '$' . number_format($nomina->total_neto, 2);
            
            // Estatus
            $badge_estatus = '';
            switch($nomina->estatus) {
                case 'Borrador': $badge_estatus = 'secondary'; break;
                case 'Calculada': $badge_estatus = 'warning'; break;
                case 'Pagada': $badge_estatus = 'success'; break;
                case 'Cancelada': $badge_estatus = 'danger'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_estatus.'">'.$nomina->estatus.'</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verNomina('.$nomina->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            
            if($nomina->estatus == 'Borrador') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-success" onclick="calcularNomina('.$nomina->id.')" title="Calcular">
                    <i class="fas fa-calculator"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarNomina('.$nomina->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            }
            
            if($nomina->estatus == 'Calculada') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-primary" onclick="pagarNomina('.$nomina->id.')" title="Marcar como Pagada">
                    <i class="fas fa-check"></i>
                </button>';
            }
            
            if($nomina->estatus == 'Pagada') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-secondary" onclick="imprimirRecibos('.$nomina->id.')" title="Imprimir Recibos">
                    <i class="fas fa-print"></i>
                </button>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($nominas),
            "recordsFiltered" => count($nominas),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Crea una nueva nómina (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'folio' => $this->generar_folio(),
            'periodo_inicio' => $this->input->post('periodo_inicio'),
            'periodo_fin' => $this->input->post('periodo_fin'),
            'tipo_nomina' => $this->input->post('tipo_nomina'),
            'fecha_pago' => $this->input->post('fecha_pago'),
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        $result = $this->db->insert('nominas', $data);
        
        if($result) {
            $nomina_id = $this->db->insert_id();
            
            // Agregar empleados activos
            $this->agregar_empleados_nomina($nomina_id, $data['tipo_nomina']);
            
            echo json_encode(['success' => true, 'message' => 'Nómina creada correctamente', 'nomina_id' => $nomina_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear nómina']);
        }
    }
    
    /**
     * Calcula una nómina (AJAX)
     */
    public function calcular_ajax() {
        $nomina_id = $this->input->post('id');
        
        if(!$nomina_id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        // Obtener nómina
        $nomina = $this->db->get_where('nominas', ['id' => $nomina_id])->row();
        
        if(!$nomina || $nomina->estatus != 'Borrador') {
            echo json_encode(['success' => false, 'message' => 'Nómina no válida para cálculo']);
            return;
        }
        
        // Obtener detalle de empleados
        $this->db->select('nd.*, e.salario_diario, e.salario_mensual');
        $this->db->from('nominas_detalle nd');
        $this->db->join('empleados e', 'nd.empleado_id = e.id');
        $this->db->where('nd.nomina_id', $nomina_id);
        $empleados = $this->db->get()->result();
        
        $total_percepciones = 0;
        $total_deducciones = 0;
        $total_neto = 0;
        
        foreach($empleados as $emp) {
            // Calcular días trabajados
            $dias = $this->calcular_dias_periodo($nomina->periodo_inicio, $nomina->periodo_fin, $nomina->tipo_nomina);
            
            // Calcular sueldo base
            $sueldo = 0;
            switch($nomina->tipo_nomina) {
                case 'Semanal':
                    $sueldo = $emp->salario_diario * $dias;
                    break;
                case 'Quincenal':
                    $sueldo = $emp->salario_mensual / 2;
                    break;
                case 'Mensual':
                    $sueldo = $emp->salario_mensual;
                    break;
            }
            
            // Calcular percepciones y deducciones
            $percepciones = $sueldo;
            $deducciones = $this->calcular_deducciones($sueldo);
            $neto = $percepciones - $deducciones;
            
            // Actualizar detalle
            $this->db->where('id', $emp->id);
            $this->db->update('nominas_detalle', [
                'dias_trabajados' => $dias,
                'sueldo_base' => $sueldo,
                'percepciones' => $percepciones,
                'deducciones' => $deducciones,
                'neto' => $neto
            ]);
            
            // Guardar conceptos
            $this->guardar_conceptos_nomina($emp->id, $sueldo, $deducciones);
            
            $total_percepciones += $percepciones;
            $total_deducciones += $deducciones;
            $total_neto += $neto;
        }
        
        // Actualizar totales de nómina
        $this->db->where('id', $nomina_id);
        $this->db->update('nominas', [
            'total_percepciones' => $total_percepciones,
            'total_deducciones' => $total_deducciones,
            'total_neto' => $total_neto,
            'estatus' => 'Calculada'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Nómina calculada correctamente']);
    }
    
    /**
     * Marca nómina como pagada y genera póliza (AJAX)
     */
    public function pagar_ajax() {
        $nomina_id = $this->input->post('id');
        
        if(!$nomina_id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        // Generar póliza contable
        $poliza_id = $this->generar_poliza_nomina($nomina_id);
        
        if($poliza_id) {
            $this->db->where('id', $nomina_id);
            $this->db->update('nominas', [
                'estatus' => 'Pagada',
                'poliza_id' => $poliza_id
            ]);
            
            // Actualizar detalle
            $this->db->where('nomina_id', $nomina_id);
            $this->db->update('nominas_detalle', ['estatus' => 'Pagado']);
            
            echo json_encode(['success' => true, 'message' => 'Nómina pagada y póliza generada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al generar póliza']);
        }
    }
    
    /**
     * Métodos auxiliares
     */
    
    private function generar_folio() {
        $this->db->select('folio');
        $this->db->from('nominas');
        $this->db->like('folio', 'NOM', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultima = $this->db->get()->row();
        
        if($ultima) {
            $numero = intval(substr($ultima->folio, 3)) + 1;
        } else {
            $numero = 1;
        }
        
        return 'NOM' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
    
    private function agregar_empleados_nomina($nomina_id, $tipo_nomina) {
        $this->db->select('id');
        $this->db->from('empleados');
        $this->db->where('estatus', 'Activo');
        $this->db->where('tipo_nomina', $tipo_nomina);
        $empleados = $this->db->get()->result();
        
        foreach($empleados as $emp) {
            $this->db->insert('nominas_detalle', [
                'nomina_id' => $nomina_id,
                'empleado_id' => $emp->id
            ]);
        }
    }
    
    private function calcular_dias_periodo($inicio, $fin, $tipo) {
        $dias = (strtotime($fin) - strtotime($inicio)) / 86400 + 1;
        return $dias;
    }
    
    private function calcular_deducciones($sueldo) {
        // Cálculo simplificado de ISR e IMSS
        $isr = $sueldo * 0.10; // 10% ISR (simplificado)
        $imss = $sueldo * 0.0235; // 2.35% IMSS empleado
        
        return $isr + $imss;
    }
    
    private function guardar_conceptos_nomina($detalle_id, $sueldo, $deducciones) {
        // Percepciones
        $this->db->insert('nominas_conceptos', [
            'nomina_detalle_id' => $detalle_id,
            'tipo' => 'Percepción',
            'concepto' => 'Sueldo Base',
            'monto' => $sueldo
        ]);
        
        // Deducciones
        $isr = $sueldo * 0.10;
        $imss = $sueldo * 0.0235;
        
        $this->db->insert('nominas_conceptos', [
            'nomina_detalle_id' => $detalle_id,
            'tipo' => 'Deducción',
            'concepto' => 'ISR',
            'monto' => $isr
        ]);
        
        $this->db->insert('nominas_conceptos', [
            'nomina_detalle_id' => $detalle_id,
            'tipo' => 'Deducción',
            'concepto' => 'IMSS',
            'monto' => $imss
        ]);
    }
    
    private function generar_poliza_nomina($nomina_id) {
        // Obtener nómina
        $nomina = $this->db->get_where('nominas', ['id' => $nomina_id])->row();
        
        if(!$nomina) return false;
        
        // Obtener periodo actual
        $periodo = $this->ContabilidadModel->get_periodo_actual();
        
        // Crear póliza
        $data_poliza = [
            'folio' => 'NOM-' . $nomina->folio,
            'tipo_poliza' => 'Egresos',
            'fecha' => $nomina->fecha_pago,
            'periodo_id' => $periodo->id,
            'concepto' => 'Pago de nómina ' . $nomina->tipo_nomina . ' - ' . $nomina->folio,
            'origen' => 'nomina',
            'origen_id' => $nomina_id,
            'total_debe' => $nomina->total_percepciones,
            'total_haber' => $nomina->total_percepciones,
            'usuario_creacion' => $this->session->userdata('user_id'),
            'estatus' => 'Autorizada',
            'usuario_autorizacion' => $this->session->userdata('user_id'),
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ];
        
        // Detalle de póliza
        $detalle = [
            [
                'cuenta_id' => $this->get_cuenta_id('6.1.01'), // Sueldos y Salarios
                'concepto' => 'Sueldos y salarios',
                'debe' => $nomina->total_percepciones,
                'haber' => 0,
                'orden' => 1
            ],
            [
                'cuenta_id' => $this->get_cuenta_id('1.1.01.003'), // Bancos
                'concepto' => 'Pago de nómina',
                'debe' => 0,
                'haber' => $nomina->total_neto,
                'orden' => 2
            ],
            [
                'cuenta_id' => $this->get_cuenta_id('2.1.05'), // ISR por Pagar
                'concepto' => 'ISR retenido',
                'debe' => 0,
                'haber' => $nomina->total_deducciones * 0.8, // Aproximado
                'orden' => 3
            ],
            [
                'cuenta_id' => $this->get_cuenta_id('2.1.06'), // IMSS por Pagar
                'concepto' => 'IMSS retenido',
                'debe' => 0,
                'haber' => $nomina->total_deducciones * 0.2, // Aproximado
                'orden' => 4
            ]
        ];
        
        return $this->ContabilidadModel->crear_poliza($data_poliza, $detalle);
    }
    
    private function get_cuenta_id($codigo) {
        $cuenta = $this->db->get_where('cuentas_contables', ['codigo' => $codigo])->row();
        return $cuenta ? $cuenta->id : null;
    }
}
