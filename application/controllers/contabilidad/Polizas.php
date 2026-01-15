<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Polizas extends MY_Controller {
    
    protected $modulo = 'Contabilidad';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Contabilidad/ContabilidadModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal de pólizas
     */
    public function index() {
        setViewSuccess('Pólizas Contables cargadas correctamente');
        $this->viewData['pageTitle'] = 'Pólizas Contables';
        $this->viewData['headTitle'] = 'Pólizas Contables';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Pólizas';
        
        // Obtener periodo actual
        $this->viewData['periodo_actual'] = $this->ContabilidadModel->get_periodo_actual();
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/polizas/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene pólizas para DataTable (AJAX)
     */
    public function lista_ajax() {
        $filtros = [
            'tipo_poliza' => $this->input->post('filtro_tipo'),
            'estatus' => $this->input->post('filtro_estatus'),
            'fecha_inicio' => $this->input->post('filtro_fecha_inicio'),
            'fecha_fin' => $this->input->post('filtro_fecha_fin'),
            'periodo_id' => $this->input->post('filtro_periodo')
        ];
        
        $polizas = $this->ContabilidadModel->get_polizas($filtros);
        
        $data = [];
        foreach($polizas as $poliza) {
            $row = [];
            
            // Folio
            $row[] = '<strong>' . $poliza->folio . '</strong>';
            
            // Tipo
            $badge_tipo = '';
            switch($poliza->tipo_poliza) {
                case 'Ingresos': $badge_tipo = 'success'; break;
                case 'Egresos': $badge_tipo = 'danger'; break;
                case 'Diario': $badge_tipo = 'primary'; break;
                case 'Cheque': $badge_tipo = 'warning'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_tipo.'">'.$poliza->tipo_poliza.'</span>';
            
            // Fecha
            $row[] = date('d/m/Y', strtotime($poliza->fecha));
            
            // Periodo
            $row[] = $poliza->periodo_nombre;
            
            // Concepto
            $row[] = substr($poliza->concepto, 0, 50) . (strlen($poliza->concepto) > 50 ? '...' : '');
            
            // Debe
            $row[] = '$' . number_format($poliza->total_debe, 2);
            
            // Haber
            $row[] = '$' . number_format($poliza->total_haber, 2);
            
            // Estatus
            $badge_estatus = '';
            switch($poliza->estatus) {
                case 'Borrador': $badge_estatus = 'secondary'; break;
                case 'Autorizada': $badge_estatus = 'success'; break;
                case 'Cancelada': $badge_estatus = 'danger'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_estatus.'">'.$poliza->estatus.'</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verPoliza('.$poliza->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            
            if($poliza->estatus == 'Borrador') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-primary" onclick="editarPoliza('.$poliza->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="autorizarPoliza('.$poliza->id.')" title="Autorizar">
                    <i class="fas fa-check"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarPoliza('.$poliza->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            }
            
            if($poliza->estatus == 'Autorizada') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-warning" onclick="cancelarPoliza('.$poliza->id.')" title="Cancelar">
                    <i class="fas fa-ban"></i>
                </button>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($polizas),
            "recordsFiltered" => count($polizas),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene una póliza completa (AJAX)
     */
    public function get_poliza_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $poliza = $this->ContabilidadModel->get_poliza_completa($id);
        
        if($poliza) {
            echo json_encode(['success' => true, 'poliza' => $poliza]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Póliza no encontrada']);
        }
    }
    
    /**
     * Crea una nueva póliza (AJAX)
     */
    public function crear_ajax() {
        // Datos de la póliza
        $data = [
            'folio' => $this->generar_folio($this->input->post('tipo_poliza')),
            'tipo_poliza' => $this->input->post('tipo_poliza'),
            'fecha' => $this->input->post('fecha'),
            'periodo_id' => $this->input->post('periodo_id'),
            'concepto' => $this->input->post('concepto'),
            'referencia' => $this->input->post('referencia'),
            'origen' => 'manual',
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        // Detalle de la póliza
        $detalle = json_decode($this->input->post('detalle'), true);
        
        // Validar que debe = haber
        $total_debe = 0;
        $total_haber = 0;
        foreach($detalle as $item) {
            $total_debe += floatval($item['debe']);
            $total_haber += floatval($item['haber']);
        }
        
        if(abs($total_debe - $total_haber) > 0.01) {
            echo json_encode([
                'success' => false, 
                'message' => 'El total de cargos debe ser igual al total de abonos'
            ]);
            return;
        }
        
        $data['total_debe'] = $total_debe;
        $data['total_haber'] = $total_haber;
        
        // Crear póliza
        $poliza_id = $this->ContabilidadModel->crear_poliza($data, $detalle);
        
        if($poliza_id) {
            echo json_encode(['success' => true, 'message' => 'Póliza creada correctamente', 'poliza_id' => $poliza_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear póliza']);
        }
    }
    
    /**
     * Autoriza una póliza (AJAX)
     */
    public function autorizar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'estatus' => 'Autorizada',
            'usuario_autorizacion' => $this->session->userdata('user_id'),
            'fecha_autorizacion' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $id);
        $this->db->where('estatus', 'Borrador');
        $result = $this->db->update('polizas', $data);
        
        if($result && $this->db->affected_rows() > 0) {
            echo json_encode(['success' => true, 'message' => 'Póliza autorizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al autorizar póliza']);
        }
    }
    
    /**
     * Cancela una póliza (AJAX)
     */
    public function cancelar_ajax() {
        $id = $this->input->post('id');
        $motivo = $this->input->post('motivo');
        
        if(!$id || !$motivo) {
            echo json_encode(['success' => false, 'message' => 'ID y motivo son requeridos']);
            return;
        }
        
        $data = [
            'estatus' => 'Cancelada',
            'motivo_cancelacion' => $motivo
        ];
        
        $this->db->where('id', $id);
        $this->db->where('estatus', 'Autorizada');
        $result = $this->db->update('polizas', $data);
        
        if($result && $this->db->affected_rows() > 0) {
            echo json_encode(['success' => true, 'message' => 'Póliza cancelada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar póliza']);
        }
    }
    
    /**
     * Elimina una póliza en borrador (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $this->db->where('id', $id);
        $this->db->where('estatus', 'Borrador');
        $result = $this->db->delete('polizas');
        
        if($result && $this->db->affected_rows() > 0) {
            echo json_encode(['success' => true, 'message' => 'Póliza eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar póliza']);
        }
    }
    
    /**
     * Genera folio automático para póliza
     */
    private function generar_folio($tipo) {
        $prefijo = '';
        switch($tipo) {
            case 'Ingresos': $prefijo = 'ING'; break;
            case 'Egresos': $prefijo = 'EGR'; break;
            case 'Diario': $prefijo = 'DIA'; break;
            case 'Cheque': $prefijo = 'CHE'; break;
        }
        
        $this->db->select('folio');
        $this->db->from('polizas');
        $this->db->like('folio', $prefijo, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultima = $this->db->get()->row();
        
        if($ultima) {
            $numero = intval(substr($ultima->folio, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}
