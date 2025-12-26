<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Departamentos extends CI_Controller {

  public $viewData = [];
  public $outputData = [];
  
  public function __construct() 
  {
    parent::__construct();
    // Cargar librerías y modelos
    $this->load->library("Init_controller");
    $this->load->model("RH/DepartamentoModel");
    $this->load->model("RH/EmpleadoModel");

    // ViewData general
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
    $this->session->email = "soporte2@chisarecubrimientos.com"; 
  }

  // ========================================================================
  // VISTA PRINCIPAL
  // ========================================================================

  public function index(){
    setViewSuccess('Departamentos cargado correctamente');
    $this->viewData['pageTitle'] = 'Departamentos';
    $this->viewData['headTitle'] = 'Gestión de Departamentos';
    $this->viewData['breadcrumb'] = 'Inicio > Recursos Humanos > Departamentos';
    
    // Obtener estadísticas
    $stats = $this->DepartamentoModel->get_estadisticas();
    
    $this->viewData['response'] = [
      'stats' => $stats
    ];
    $this->viewData['validate'] = '';
    $this->viewData['pageView'] = 'rh/departamentos/main_departamentos';

    // Render views
	  $this->load->view('layouts/general_template', $this->viewData);
  }

  // ========================================================================
  // BÚSQUEDA DATATABLES
  // ========================================================================

  public function search_departamentos(){
    $list = $this->DepartamentoModel->get_datatables();
    $data = array();
    $no = $_POST['start'];

    foreach ($list as $dept) {
      $no++;
      $row = array();
      
      // ID
      $row[] = $dept->id;
      
      // Nombre
      $row[] = $dept->nombre;
      
      // Descripción
      $row[] = $dept->descripcion ?? 'N/A';
      
      // Empleados
      $empleados_count = $this->DepartamentoModel->count_empleados($dept->id);
      $row[] = '<span class="badge bg-info">'.$empleados_count.' empleado(s)</span>';
      
      // Estatus
      if($dept->estatus == 1){
        $row[] = '<span class="badge bg-success">Activo</span>';
      } else {
        $row[] = '<span class="badge bg-danger">Inactivo</span>';
      }
      
      // Acciones
      $acciones = '
        <button type="button" class="btn btn-sm btn-primary" onclick="departamento_detail('.$dept->id.')">
          <i class="fas fa-eye"></i>
        </button>
        <button type="button" class="btn btn-sm btn-warning" onclick="editar_departamento('.$dept->id.')">
          <i class="fas fa-edit"></i>
        </button>';
      
      if($dept->estatus == 1 && $empleados_count == 0){
        $acciones .= '
          <button type="button" class="btn btn-sm btn-danger" onclick="delete_departamento('.$dept->id.')">
            <i class="fas fa-trash"></i>
          </button>';
      }
      
      $row[] = $acciones;
      
      $data[] = $row;
    }

    $output = array(
      "draw" => $_POST['draw'],
      "recordsTotal" => $this->DepartamentoModel->count_all(),
      "recordsFiltered" => $this->DepartamentoModel->count_filtered(),
      "data" => $data,
    );

    echo json_encode($output);
  }

  // ========================================================================
  // AGREGAR DEPARTAMENTO (AJAX)
  // ========================================================================

  public function agregar(){
    $data = [
      'nombre' => $this->input->post('nombre'),
      'descripcion' => $this->input->post('descripcion'),
    ];

    $result = $this->DepartamentoModel->mod_add($data);

    echo json_encode([
      'success' => $result['success'] == 1,
      'message' => $result['msg']
    ]);
  }

  // ========================================================================
  // EDITAR DEPARTAMENTO (AJAX)
  // ========================================================================

  public function editar(){
    $id = $this->input->post('id');
    $data = [
      'nombre' => $this->input->post('nombre'),
      'descripcion' => $this->input->post('descripcion'),
      'estatus' => $this->input->post('estatus'),
    ];

    $result = $this->DepartamentoModel->mod_update($id, $data);

    echo json_encode([
      'success' => $result['success'] == 1,
      'message' => $result['msg']
    ]);
  }

  // ========================================================================
  // ELIMINAR DEPARTAMENTO (AJAX)
  // ========================================================================

  public function eliminar(){
    $id = $this->input->post('id');

    $result = $this->DepartamentoModel->mod_delete($id);

    echo json_encode([
      'success' => $result['success'] == 1,
      'message' => $result['msg']
    ]);
  }

  // ========================================================================
  // DETALLE DEPARTAMENTO (AJAX)
  // ========================================================================

  public function detail(){
    $id = $this->input->post('id');
    $dept = $this->DepartamentoModel->get_departamento_completo($id);

    if(!$dept){
      echo json_encode(['response' => null]);
      return;
    }

    // Generar HTML de detalles
    $detail = '
      <tr><td><strong>ID:</strong></td><td>'.$dept->id.'</td></tr>
      <tr><td><strong>Nombre:</strong></td><td>'.$dept->nombre.'</td></tr>
      <tr><td><strong>Descripción:</strong></td><td>'.($dept->descripcion ?? 'N/A').'</td></tr>
      <tr><td><strong>Empleados:</strong></td><td>'.$dept->empleados_count.'</td></tr>
      <tr><td><strong>Fecha Alta:</strong></td><td>'.$dept->fecha_alta.'</td></tr>
      <tr><td><strong>Estatus:</strong></td><td>'.($dept->estatus == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>').'</td></tr>
    ';

    // Acciones
    $actions = '
      <button class="btn btn-warning btn-sm w-100 mb-2" onclick="editar_departamento('.$id.')">
        <i data-lucide="edit"></i> Editar
      </button>';
    
    if($dept->estatus == 1 && $dept->empleados_count == 0){
      $actions .= '
        <button class="btn btn-danger btn-sm w-100" onclick="delete_departamento('.$id.')">
          <i data-lucide="trash-2"></i> Eliminar
        </button>';
    }

    echo json_encode([
      'response' => $dept,
      'detail' => $detail,
      'actions' => $actions
    ]);
  }
}
