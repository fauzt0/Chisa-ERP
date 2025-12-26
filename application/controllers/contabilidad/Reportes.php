<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reportes extends CI_Controller {
    
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
     * Vista principal de reportes
     */
    public function index() {
        setViewSuccess('Reportes Financieros cargados correctamente');
        $this->viewData['pageTitle'] = 'Reportes Financieros';
        $this->viewData['headTitle'] = 'Reportes Financieros';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Reportes';
        
        // Obtener ejercicio y periodo actual
        $this->viewData['ejercicio_actual'] = $this->ContabilidadModel->get_ejercicio_actual();
        $this->viewData['periodo_actual'] = $this->ContabilidadModel->get_periodo_actual();
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/reportes/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Genera Balance General (AJAX)
     */
    public function balance_general_ajax() {
        $fecha_corte = $this->input->post('fecha_corte') ?: date('Y-m-d');
        
        // Obtener todas las cuentas de Activo, Pasivo y Capital
        $cuentas_activo = $this->get_cuentas_con_saldo('Activo', $fecha_corte);
        $cuentas_pasivo = $this->get_cuentas_con_saldo('Pasivo', $fecha_corte);
        $cuentas_capital = $this->get_cuentas_con_saldo('Capital', $fecha_corte);
        
        // Calcular totales
        $total_activo = array_sum(array_column($cuentas_activo, 'saldo'));
        $total_pasivo = array_sum(array_column($cuentas_pasivo, 'saldo'));
        $total_capital = array_sum(array_column($cuentas_capital, 'saldo'));
        
        // Calcular utilidad del ejercicio
        $utilidad = $this->calcular_utilidad_ejercicio($fecha_corte);
        
        $data = [
            'fecha_corte' => $fecha_corte,
            'activo' => $cuentas_activo,
            'pasivo' => $cuentas_pasivo,
            'capital' => $cuentas_capital,
            'total_activo' => $total_activo,
            'total_pasivo' => $total_pasivo,
            'total_capital' => $total_capital,
            'utilidad_ejercicio' => $utilidad,
            'total_pasivo_capital' => $total_pasivo + $total_capital + $utilidad
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
    }
    
    /**
     * Genera Estado de Resultados (AJAX)
     */
    public function estado_resultados_ajax() {
        $fecha_inicio = $this->input->post('fecha_inicio');
        $fecha_fin = $this->input->post('fecha_fin') ?: date('Y-m-d');
        
        // Obtener cuentas de Ingresos, Costos y Egresos
        $cuentas_ingresos = $this->get_cuentas_con_movimientos('Ingresos', $fecha_inicio, $fecha_fin);
        $cuentas_costos = $this->get_cuentas_con_movimientos('Costos', $fecha_inicio, $fecha_fin);
        $cuentas_egresos = $this->get_cuentas_con_movimientos('Egresos', $fecha_inicio, $fecha_fin);
        
        // Calcular totales
        $total_ingresos = array_sum(array_column($cuentas_ingresos, 'haber')) - array_sum(array_column($cuentas_ingresos, 'debe'));
        $total_costos = array_sum(array_column($cuentas_costos, 'debe')) - array_sum(array_column($cuentas_costos, 'haber'));
        $total_egresos = array_sum(array_column($cuentas_egresos, 'debe')) - array_sum(array_column($cuentas_egresos, 'haber'));
        
        $utilidad_bruta = $total_ingresos - $total_costos;
        $utilidad_neta = $utilidad_bruta - $total_egresos;
        
        $data = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'ingresos' => $cuentas_ingresos,
            'costos' => $cuentas_costos,
            'egresos' => $cuentas_egresos,
            'total_ingresos' => $total_ingresos,
            'total_costos' => $total_costos,
            'total_egresos' => $total_egresos,
            'utilidad_bruta' => $utilidad_bruta,
            'utilidad_neta' => $utilidad_neta
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
    }
    
    /**
     * Genera Balanza de Comprobación (AJAX)
     */
    public function balanza_comprobacion_ajax() {
        $fecha_inicio = $this->input->post('fecha_inicio');
        $fecha_fin = $this->input->post('fecha_fin') ?: date('Y-m-d');
        
        // Obtener todas las cuentas con movimientos
        $this->db->select('c.id, c.codigo, c.nombre, c.naturaleza, 
                          COALESCE(SUM(pd.debe), 0) as total_debe,
                          COALESCE(SUM(pd.haber), 0) as total_haber');
        $this->db->from('cuentas_contables c');
        $this->db->join('polizas_detalle pd', 'c.id = pd.cuenta_id', 'left');
        $this->db->join('polizas p', 'pd.poliza_id = p.id', 'left');
        
        if($fecha_inicio) {
            $this->db->where('p.fecha >=', $fecha_inicio);
        }
        if($fecha_fin) {
            $this->db->where('p.fecha <=', $fecha_fin);
        }
        
        $this->db->where('p.estatus', 'Autorizada');
        $this->db->where('c.es_afectable', 1);
        $this->db->group_by('c.id');
        $this->db->having('total_debe > 0 OR total_haber > 0');
        $this->db->order_by('c.codigo', 'ASC');
        
        $cuentas = $this->db->get()->result();
        
        // Calcular saldos
        $total_debe = 0;
        $total_haber = 0;
        $saldo_deudor_total = 0;
        $saldo_acreedor_total = 0;
        
        foreach($cuentas as &$cuenta) {
            $total_debe += $cuenta->total_debe;
            $total_haber += $cuenta->total_haber;
            
            $diferencia = $cuenta->total_debe - $cuenta->total_haber;
            
            if($diferencia > 0) {
                $cuenta->saldo_deudor = $diferencia;
                $cuenta->saldo_acreedor = 0;
                $saldo_deudor_total += $diferencia;
            } else {
                $cuenta->saldo_deudor = 0;
                $cuenta->saldo_acreedor = abs($diferencia);
                $saldo_acreedor_total += abs($diferencia);
            }
        }
        
        $data = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'cuentas' => $cuentas,
            'total_debe' => $total_debe,
            'total_haber' => $total_haber,
            'saldo_deudor_total' => $saldo_deudor_total,
            'saldo_acreedor_total' => $saldo_acreedor_total
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
    }
    
    /**
     * Métodos auxiliares
     */
    
    private function get_cuentas_con_saldo($tipo_cuenta, $fecha_corte) {
        $this->db->select('c.codigo, c.nombre, c.naturaleza,
                          COALESCE(SUM(pd.debe), 0) as total_debe,
                          COALESCE(SUM(pd.haber), 0) as total_haber');
        $this->db->from('cuentas_contables c');
        $this->db->join('polizas_detalle pd', 'c.id = pd.cuenta_id', 'left');
        $this->db->join('polizas p', 'pd.poliza_id = p.id', 'left');
        $this->db->where('c.tipo_cuenta', $tipo_cuenta);
        $this->db->where('c.es_afectable', 1);
        $this->db->where('p.fecha <=', $fecha_corte);
        $this->db->where('p.estatus', 'Autorizada');
        $this->db->group_by('c.id');
        $this->db->order_by('c.codigo', 'ASC');
        
        $cuentas = $this->db->get()->result_array();
        
        // Calcular saldo según naturaleza
        foreach($cuentas as &$cuenta) {
            if($cuenta['naturaleza'] == 'Deudora') {
                $cuenta['saldo'] = $cuenta['total_debe'] - $cuenta['total_haber'];
            } else {
                $cuenta['saldo'] = $cuenta['total_haber'] - $cuenta['total_debe'];
            }
        }
        
        return array_filter($cuentas, function($c) {
            return abs($c['saldo']) > 0.01;
        });
    }
    
    private function get_cuentas_con_movimientos($tipo_cuenta, $fecha_inicio, $fecha_fin) {
        $this->db->select('c.codigo, c.nombre,
                          COALESCE(SUM(pd.debe), 0) as debe,
                          COALESCE(SUM(pd.haber), 0) as haber');
        $this->db->from('cuentas_contables c');
        $this->db->join('polizas_detalle pd', 'c.id = pd.cuenta_id', 'left');
        $this->db->join('polizas p', 'pd.poliza_id = p.id', 'left');
        $this->db->where('c.tipo_cuenta', $tipo_cuenta);
        $this->db->where('c.es_afectable', 1);
        
        if($fecha_inicio) {
            $this->db->where('p.fecha >=', $fecha_inicio);
        }
        if($fecha_fin) {
            $this->db->where('p.fecha <=', $fecha_fin);
        }
        
        $this->db->where('p.estatus', 'Autorizada');
        $this->db->group_by('c.id');
        $this->db->having('debe > 0 OR haber > 0');
        $this->db->order_by('c.codigo', 'ASC');
        
        return $this->db->get()->result_array();
    }
    
    private function calcular_utilidad_ejercicio($fecha_corte) {
        // Obtener ingresos
        $this->db->select('COALESCE(SUM(pd.haber - pd.debe), 0) as total');
        $this->db->from('polizas_detalle pd');
        $this->db->join('polizas p', 'pd.poliza_id = p.id');
        $this->db->join('cuentas_contables c', 'pd.cuenta_id = c.id');
        $this->db->where('c.tipo_cuenta', 'Ingresos');
        $this->db->where('p.fecha <=', $fecha_corte);
        $this->db->where('p.estatus', 'Autorizada');
        $ingresos = $this->db->get()->row()->total;
        
        // Obtener egresos (costos + gastos)
        $this->db->select('COALESCE(SUM(pd.debe - pd.haber), 0) as total');
        $this->db->from('polizas_detalle pd');
        $this->db->join('polizas p', 'pd.poliza_id = p.id');
        $this->db->join('cuentas_contables c', 'pd.cuenta_id = c.id');
        $this->db->where_in('c.tipo_cuenta', ['Costos', 'Egresos']);
        $this->db->where('p.fecha <=', $fecha_corte);
        $this->db->where('p.estatus', 'Autorizada');
        $egresos = $this->db->get()->row()->total;
        
        return $ingresos - $egresos;
    }
}
