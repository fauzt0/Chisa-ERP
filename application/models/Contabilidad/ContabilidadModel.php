<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ContabilidadModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    // =====================================================
    // CUENTAS CONTABLES
    // =====================================================
    
    /**
     * Obtiene todas las cuentas contables
     */
    public function get_cuentas($filtros = []) {
        $this->db->select('c.*, cp.nombre as cuenta_padre_nombre');
        $this->db->from('cuentas_contables c');
        $this->db->join('cuentas_contables cp', 'c.cuenta_padre_id = cp.id', 'left');
        
        // Filtros
        if(!empty($filtros['tipo_cuenta'])) {
            $this->db->where('c.tipo_cuenta', $filtros['tipo_cuenta']);
        }
        
        if(!empty($filtros['estatus'])) {
            $this->db->where('c.estatus', $filtros['estatus']);
        }
        
        if(!empty($filtros['es_afectable'])) {
            $this->db->where('c.es_afectable', $filtros['es_afectable']);
        }
        
        $this->db->order_by('c.codigo', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene una cuenta por ID
     */
    public function get_cuenta($id) {
        $this->db->select('c.*, cp.nombre as cuenta_padre_nombre');
        $this->db->from('cuentas_contables c');
        $this->db->join('cuentas_contables cp', 'c.cuenta_padre_id = cp.id', 'left');
        $this->db->where('c.id', $id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Obtiene cuentas afectables para selects
     */
    public function get_cuentas_afectables() {
        $this->db->select('id, codigo, nombre');
        $this->db->from('cuentas_contables');
        $this->db->where('es_afectable', 1);
        $this->db->where('estatus', 'Activa');
        $this->db->order_by('codigo', 'ASC');
        
        return $this->db->get()->result();
    }
    
    // =====================================================
    // PÓLIZAS
    // =====================================================
    
    /**
     * Obtiene pólizas con filtros
     */
    public function get_polizas($filtros = []) {
        $this->db->select('p.*, per.nombre as periodo_nombre');
        $this->db->from('polizas p');
        $this->db->join('periodos_contables per', 'p.periodo_id = per.id', 'left');
        
        // Filtros
        if(!empty($filtros['tipo_poliza'])) {
            $this->db->where('p.tipo_poliza', $filtros['tipo_poliza']);
        }
        
        if(!empty($filtros['estatus'])) {
            $this->db->where('p.estatus', $filtros['estatus']);
        }
        
        if(!empty($filtros['fecha_inicio'])) {
            $this->db->where('p.fecha >=', $filtros['fecha_inicio']);
        }
        
        if(!empty($filtros['fecha_fin'])) {
            $this->db->where('p.fecha <=', $filtros['fecha_fin']);
        }
        
        if(!empty($filtros['periodo_id'])) {
            $this->db->where('p.periodo_id', $filtros['periodo_id']);
        }
        
        $this->db->order_by('p.fecha', 'DESC');
        $this->db->order_by('p.folio', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene una póliza con su detalle
     */
    public function get_poliza_completa($id) {
        // Póliza
        $this->db->select('p.*, per.nombre as periodo_nombre');
        $this->db->from('polizas p');
        $this->db->join('periodos_contables per', 'p.periodo_id = per.id', 'left');
        $this->db->where('p.id', $id);
        $poliza = $this->db->get()->row();
        
        if($poliza) {
            // Detalle
            $this->db->select('pd.*, c.codigo as cuenta_codigo, c.nombre as cuenta_nombre');
            $this->db->from('polizas_detalle pd');
            $this->db->join('cuentas_contables c', 'pd.cuenta_id = c.id');
            $this->db->where('pd.poliza_id', $id);
            $this->db->order_by('pd.orden', 'ASC');
            $poliza->detalle = $this->db->get()->result();
        }
        
        return $poliza;
    }
    
    /**
     * Crea una póliza con su detalle
     */
    public function crear_poliza($data, $detalle) {
        $this->db->trans_start();
        
        // Insertar póliza
        $this->db->insert('polizas', $data);
        $poliza_id = $this->db->insert_id();
        
        // Insertar detalle
        if($poliza_id && !empty($detalle)) {
            foreach($detalle as $item) {
                $item['poliza_id'] = $poliza_id;
                $this->db->insert('polizas_detalle', $item);
            }
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() ? $poliza_id : false;
    }
    
    // =====================================================
    // PERIODOS Y EJERCICIOS
    // =====================================================
    
    /**
     * Obtiene el periodo actual
     */
    public function get_periodo_actual() {
        $this->db->select('p.*, e.año');
        $this->db->from('periodos_contables p');
        $this->db->join('ejercicios_fiscales e', 'p.ejercicio_id = e.id');
        $this->db->where('p.estatus', 'Abierto');
        $this->db->where('e.estatus', 'Abierto');
        $this->db->where('p.fecha_inicio <=', date('Y-m-d'));
        $this->db->where('p.fecha_fin >=', date('Y-m-d'));
        
        return $this->db->get()->row();
    }
    
    /**
     * Obtiene todos los periodos de un ejercicio
     */
    public function get_periodos_ejercicio($ejercicio_id) {
        $this->db->select('*');
        $this->db->from('periodos_contables');
        $this->db->where('ejercicio_id', $ejercicio_id);
        $this->db->order_by('numero_periodo', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene ejercicio actual
     */
    public function get_ejercicio_actual() {
        $this->db->where('estatus', 'Abierto');
        $this->db->where('YEAR(fecha_inicio) <=', date('Y'));
        $this->db->where('YEAR(fecha_fin) >=', date('Y'));
        
        return $this->db->get('ejercicios_fiscales')->row();
    }
    
    // =====================================================
    // DASHBOARD - ESTADÍSTICAS
    // =====================================================
    
    /**
     * Obtiene resumen financiero del periodo
     */
    public function get_resumen_financiero($periodo_id = null) {
        if(!$periodo_id) {
            $periodo = $this->get_periodo_actual();
            $periodo_id = $periodo ? $periodo->id : null;
        }
        
        if(!$periodo_id) {
            return null;
        }
        
        // Obtener totales de ingresos
        $this->db->select('SUM(pd.haber) as total_ingresos');
        $this->db->from('polizas_detalle pd');
        $this->db->join('polizas p', 'pd.poliza_id = p.id');
        $this->db->join('cuentas_contables c', 'pd.cuenta_id = c.id');
        $this->db->where('p.periodo_id', $periodo_id);
        $this->db->where('p.estatus', 'Autorizada');
        $this->db->where('c.tipo_cuenta', 'Ingresos');
        $ingresos = $this->db->get()->row();
        
        // Obtener totales de egresos
        $this->db->select('SUM(pd.debe) as total_egresos');
        $this->db->from('polizas_detalle pd');
        $this->db->join('polizas p', 'pd.poliza_id = p.id');
        $this->db->join('cuentas_contables c', 'pd.cuenta_id = c.id');
        $this->db->where('p.periodo_id', $periodo_id);
        $this->db->where('p.estatus', 'Autorizada');
        $this->db->where_in('c.tipo_cuenta', ['Egresos', 'Costos']);
        $egresos = $this->db->get()->row();
        
        return [
            'ingresos' => $ingresos->total_ingresos ?: 0,
            'egresos' => $egresos->total_egresos ?: 0,
            'utilidad' => ($ingresos->total_ingresos ?: 0) - ($egresos->total_egresos ?: 0)
        ];
    }
    
    /**
     * Obtiene pólizas pendientes de autorizar
     */
    public function get_polizas_pendientes() {
        $this->db->where('estatus', 'Borrador');
        $this->db->order_by('fecha', 'DESC');
        $this->db->limit(10);
        
        return $this->db->get('polizas')->result();
    }
}
