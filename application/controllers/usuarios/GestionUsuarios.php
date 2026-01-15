<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GestionUsuarios extends MY_Controller {

  protected $modulo = 'Administradores';
  
  public function __construct() 
  {
    parent::__construct();
    
    // Cargar modelos específicos
    $this->load->model("Users/UserModel"); 
    $this->load->model("Users/RolesModel");
    $this->config->load('permissions'); 
    
    // El controlador base ya maneja la sesión y los permisos del módulo
  }

  public function index(){
    //preparamos los datos de la vista
    setViewSuccess('Gestion de usuarios cargado correctamente');
    $this->viewData['pageTitle'] = 'Gestion de usuarios';
    $this->viewData['headTitle'] = 'Gestion de usuarios';
    $this->viewData['breadcrumb'] = 'Inicio > Gestion de usuarios';
    
    // Obtener estadísticas críticas del sistema
    $stats = $this->UserModel->get_critical_stats();
    $last_user = $this->UserModel->get_last_registered_user();
    $users_by_dept = $this->UserModel->get_users_by_department();
    
    $this->viewData['response'] = [
      'stats' => $stats,
      'last_user' => $last_user,
      'users_by_dept' => $users_by_dept
    ];
    $this->viewData['validate'] = '';
    $this->viewData['pageView'] = 'usuarios/main_usuarios';
    

    //render views
	  $this->load->view('layouts/general_template', $this->viewData);
  }

  /**
   * Busca los usuarios en la base de datos y devuelve un json con los resultados para el datatables 
   */
  public function search_users(){
    
    $list = $this->UserModel->_mod_search_users(); ///buscamos el resultado desde el modelo

    $data = array();
    $no = $_POST['start'];
    foreach ($list as $admins) {
      ////procesamos etiquetas, botones, resultados, y avatares
      if($admins->estatus==1){
        $tmp_estatus = '<span class="badge badge-subtle-success">Activo</span>';
      }else{
        $tmp_estatus = '<span class="badge badge-subtle-danger">Suspendido</span>';
      }

      ///botones de acciones
      $tmp_acciones = '
        <button class="btn btn-outline-success shadow-sm align-middle" onclick="user_detail('.$admins->id.')" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Tooltip on top">
          <i class="align-middle fas fa-fw fa-eye"></i>
        </button>
        <a href="'.base_url().'usuarios/GestionUsuarios/editar/'.$admins->id.'" button alt="editar" class="btn btn-primary me-1 mb-1 mt-1" ><i class="align-middle fas fa-fw fa-edit"></i> Editar</a>
      ';

      // agregamos el td con el id y la foto del avatar, en caso de que no se tenga foto, se muestra un circulo azul con la inicial del nombre
      $tmp_avatar = '<div class="avatar avatar-md avatar-rounded avatar-indigo">'.$admins->nombre.'</div>';


      //$no++;
      $row = array();
      //$row[] = $no;
      $row['DT_RowId'] = "row_".$admins->id;
      $row[] = $admins->id;
      $row[] = $admins->nombre;
      $row[] = $admins->apellidos;
      $row[] = $admins->username;
      $row[] = $tmp_estatus;
      $row[] = $tmp_acciones;
      $data[] = $row;
    }
    $output = array(
      "draw" => $_POST['draw'],
      "recordsTotal" => $this->UserModel->count_all("administradores"),
      "recordsFiltered" => $this->UserModel->count_filtered_users(),
      "data" => $data,
    );
    //output to json format
    echo json_encode($output);   
  }

  /**
   * Obtiene los detalles de un usuario para mostrarlos en la barra lateral derecha del listado de usuarios.
   */
  public function detail(){
    $data['response'] = NULL;
    $uid = $this->input->post('id');
    $tmp_estatus = '<span class="badge bg-danger">Suspendido</span>';

    if($uid!=NULL){
      $data['response'] = 1;
      ///obtenemos los datos del usuario
      $row = $this->UserModel->mod_get_user_from_id($uid);
      if($row->estatus==1){
        $tmp_estatus = '<span class="badge bg-success">Activo</span>';
      }
      $data['detail'] = '<tr>
                          <th>Nombre</th> <td>'.$row->nombre.'</td>
                        </tr>
                        <tr>
                          <th>Apellidos</th> <td>'.$row->apellidos.'</td>
                        </tr>
                        <tr>
                          <th>Email</th> <td style="word-break: break-all; max-width: 200px;">'.$row->username.'</td>
                        </tr>
                        <tr>
                          <th>Dapartamento</th> <td>'.$row->departamento.'</td>
                        </tr>
                        <tr>
                          <th>Estatus</th> <td>'.$tmp_estatus.'</td>
                        </tr>';

      $data['actions'] = '<a href="'.base_url().'usuarios/GestionUsuarios/editar/'.$row->id.'" button alt="editar" class="btn btn-primary me-1 mb-1" ><i class="align-middle fas fa-fw fa-edit"></i> Editar</a>';
      if($row->estatus==1){
        $data['actions'] .= '<button alt="eliminar" class="btn btn-danger me-1 mb-1" onclick="delete_user('.$row->id.');"><i class="fas fa-times"></i> Eliminar</button>';
      }else{
        $data['actions'] .= '<button alt="restaurar" class="btn btn-warning me-1 mb-1" onclick="restaurar('.$row->id.');"><i class="align-middle me-2 fas fa-fw fa-sync-alt"></i> Restaurar</button>';
      }
      //obtenemos los ultimos movimientos del administrador
      $result = $this->UserModel->last_logs($row->username,5);
      $data['last_logs'] = "";
      foreach ($result->result() as $arow) {
        $data['last_logs'] .='<li class="timeline-item">
                                <strong>'.$arow->tipo.'</strong>
                                <span class="float-end text-muted text-sm">'.$arow->fecha.'</span>
                                <p>'.$arow->mensaje.'</p>
                              </li>';
      }
    }

    echo json_encode($data);
  }

  /**
   *  Almacena un nuevo usuario en la base de datos y asigna los permisos correspondientes
   */
  public function alta(){
    $permissions = $this->config->item('permissions');
    setViewSuccess('Alta de nuevo Usuario');

    //verificamos 
    if($this->input->post('save')!=NULL){ //almacenamos los datos
      $validation = array(
        array('field' => 'nombre', 'label' => 'Nombre', 'rules' => 'trim|required',
          'errors' => array('required' => 'Es necesario ingresar Nombre'),
        ),
        array('field' => 'apellidos', 'label' => 'Apellidos', 'rules' => 'trim|required',
          'errors' => array('required' => 'Es necesario ingresar Apellido'),
        ),
  			array('field' => 'username', 'label' => 'Email', 'rules' => 'trim|required|valid_email|is_unique[administradores.username]',
          'errors' => array(
            'required'    => 'Es necesario ingresar un email',
            'valid_email' => 'Es necesario ingresar un email válido',
            'is_unique'   => 'El administrador ya se encuentra registrado en el sistema'
          ),
        ),
  			array('field' => 'password', 'label' => 'Contraseña', 'rules' => 'trim|required|min_length[6]',
          'errors' => array(
            'required' => 'Es necesario ingresar una contraseña',
            'min_length' => 'La contraeña es demasiado corta, se requieren mínimo 6 caracteres',
          ),
        ),
        array('field' => 'password_verify', 'label' => 'Verificar Contraseña', 'rules' => 'trim|required|matches[password]',
          'errors' => array(
            'required' => 'Es necesario ingresar una contraseña',
            'matches' => 'No coinciden las contraseñas',
          ),
        ),
		  );
      $this->form_validation->set_rules($validation);

      if($this->form_validation->run() == true){     
        ///agregamos los datos del usuario
        $result = $this->UserModel->mod_add($permissions);
        
        switch ($result['success']) {
          case 0:
            $this->viewData['validate'] = $this->init_controller->alert("danger",$result['msg']);            
            $this->viewData['notification'] = ['msg' => $result['msg'], 'type' => 'danger'];
            setViewError($result['msg']);
            break;
          case 1:                        
            $this->viewData['validate'] = $this->init_controller->alert("success",$result['msg']);
            $this->init_controller->insert_log($result['msg'],$this->session->email,"Registro agregado");                                    
            $this->viewData['notification'] = ['msg' => $result['msg'], 'type' => 'success'];
            setViewSuccess($result['msg']);            
            break;
          default:
            $this->viewData['validate'] = $this->init_controller->alert("danger","Se ha producido un error.");
            $this->viewData['notification'] = ['msg' => "Se ha producido un error desconocido al guardar el usuario: " . $result['msg'], 'type' => 'danger'];
            setViewError("Se ha producido un error desconocido al guardar el usuario.".$result['msg']);
            break;
        }        
      }
    }

    ///renderizamos la vista
    setViewSuccess('Alta de usuarios');
    $this->viewData['pageTitle'] = 'Alta de usuarios';
    $this->viewData['headTitle'] = 'Alta de usuarios';
    $this->viewData['breadcrumb'] = 'Inicio > Gestion de usuarios > Alta de usuarios';
    $this->viewData['response'] = [
      'permissions' => $permissions, //permisos desde archivo de configuracion permissions
      'roles' => $this->RolesModel->get_all_roles()
    ];//no hay datos por el momento      
    $this->viewData['pageView'] = 'usuarios/alta';
    

    //render views
	  $this->load->view('layouts/general_template', $this->viewData);
  }

  /**
   * Muestra la vista con los datos a editar del administrador
   */
  public function editar($id=false){
    $permissions = $this->config->item('permissions');
    setViewSuccess('Alta de usuarios cargado correctamente');
    
    if($this->input->post('save')!=NULL){ //almacenamos los datos
      $this->actualizar($id,$permissions);
    }

    $response = $this->UserModel->getUserData($id);
    $user_permissions = [];

    foreach ($response['user_permissions']->result() as $row) {
      //creamos un array con los permisos del usuario
      $user_permissions[$row->permiso] = $row->valor;      
    }
    
    $this->viewData['pageTitle'] = 'Editar usuario';
    $this->viewData['headTitle'] = 'Editar usuario';
    $this->viewData['breadcrumb'] = 'Inicio > Gestion de usuarios > Editar usuario';
    $this->viewData['response'] = [      
      'userData' => $response['user_data'],
      'userPermissions' => $user_permissions,
      'permissions' => $this->config->item('permissions'),
      'roles' => $this->RolesModel->get_all_roles(),
      'id' => $id
    ];    
    $this->viewData['pageView'] = 'usuarios/editar'; // visa d
    
    //render views
	  $this->load->view('layouts/general_template', $this->viewData);
  }

  /**
   * Actualiza un usuario en la base de datos y sus permisos correspondientes
   */
  private function actualizar($id, $permissions = null) : void
  {    
    //en caso de no contar con el array de permisos, lo obtenemos
    if($permissions == null){
      $permissions = $this->config->item('permissions');
    }

    $validate = [];

    // Array de validaciones usando sintaxis moderna
    $validation = [
      [
        'field' => 'nombre', 
        'label' => 'Nombre', 
        'rules' => 'trim|required',
        'errors' => ['required' => 'Es necesario ingresar Nombre']
      ],
      [
        'field' => 'apellidos', 
        'label' => 'Apellidos', 
        'rules' => 'trim|required',
        'errors' => ['required' => 'Es necesario ingresar Apellido']
      ],
      [
        'field' => 'username', 
        'label' => 'Email', 
        'rules' => 'trim|required|valid_email|callback_unique_email_on_update['.$id.']',
        'errors' => [
          'required' => 'Es necesario ingresar un email',
          'valid_email' => 'Es necesario ingresar un email válido',
          'unique_email_on_update' => 'Este email ya está registrado por otro usuario'
        ]
      ]
    ];
    
    // Solo validar contraseña si se ingresó una nueva
    if (!empty($this->input->post('password'))) {
      $validation[] = [
        'field' => 'password', 
        'label' => 'Contraseña', 
        'rules' => 'trim|required|min_length[6]',
        'errors' => [
          'required' => 'Es necesario ingresar una contraseña',
          'min_length' => 'La contraseña es demasiado corta, se requieren mínimo 6 caracteres'
        ]
      ];
      
      $validation[] = [
        'field' => 'password_verify', 
        'label' => 'Verificar Contraseña', 
        'rules' => 'trim|required|matches[password]',
        'errors' => [
          'required' => 'Es necesario ingresar una contraseña',
          'matches' => 'No coinciden las contraseñas'
        ]
      ];
    }
    
    $this->form_validation->set_rules($validation);
    
    //verificamos que se cumplan las validaciones
    if($this->form_validation->run() == true){   
      $result = $this->UserModel->mod_update($id,$permissions);

      switch ($result['success']) {
        case 0:
          $this->viewData['validate'] = $this->init_controller->alert("danger",$result['msg']);            
          $this->viewData['notification'] = ['msg' => $result['msg'], 'type' => 'danger'];
          setViewError('No se pudo actualizar el usuario');
          break;
        case 1:        
          $this->viewData['validate'] = $this->init_controller->alert("success",$result['msg']);        
          $this->init_controller->insert_log($result['msg'],$this->session->email,"Registro actualizado");        
          $this->viewData['notification'] = ['msg' => $result['msg'], 'type' => 'success'];
          setViewSuccess('Usuario actualizado correctamente');                      
          break;
        default:
          $this->viewData['validate'] = $this->init_controller->alert("danger","Se ha producido un error.");
          $this->viewData['notification'] = ['msg' => "Se ha producido un error desconocido al guardar el usuario: " . $result['msg'], 'type' => 'danger'];
          setViewError('No se pudo actualizar el usuario');
          break;
      }        
    }else{ //no se cumplieron las validaciones
      $this->viewData['validate'] = $this->init_controller->alert("danger","Se ha producido un error.");
      $this->viewData['notification'] = ['msg' => "Hay errores en el formulario, por favor revíselo.", 'type' => 'danger'];
      setViewError('No se pudo actualizar el usuario. Error del formulario');
    }

    //return $result;
  }

  public function unique_email_on_update($email, $id){
    $this->db->where('username', $email);
    $this->db->where('id !=', $id);
    $query = $this->db->get('administradores');

    if($query->num_rows() > 0){
      return false;
    }else{
      return true;
    }
  } 


  /**
   * Realiza la eliminacion del usuario (soft delete)
   * @param int $id ID del usuario por post
   * @param string $peticion Tipo de peticion (ajax o api) por post
   */
  public function eliminar()
  {
    $id = $this->input->post('id');
    $peticion = $this->input->post('peticion');

    //solicitud al modelo para eliminar al usuario
    $this->outputData['validate'] = $this->UserModel->mod_delete($id);    
  
    if($this->outputData['validate']['success'] == 1){
      $this->init_controller->insert_log($this->outputData['validate']['msg'],$this->session->email,"Registro eliminado");
      setOutputSuccess('Usuario eliminado correctamente');
    }else{
      setOutputError($this->outputData['validate']['msg']);
    }

    //retornamos la respuesta
    //$this->outputData['validate'] = $this->UserModel->mod_delete($id);
    $this->outputData['response'] = [      
      'id' => $id,
      'success' => $this->outputData['validate']['success'],
      'msg' => $this->outputData['validate']['msg'],
    ];    
    echo json_encode($this->outputData);        
  }

  /**
   * Vista de bitácora de usuarios
   * Muestra todos los administradores con opción de ver su historial
   */
  public function bitacora() {
    $this->viewData['pageTitle'] = 'Bitácora de Usuarios';
    $this->viewData['headTitle'] = 'Bitácora de Usuarios';
    $this->viewData['breadcrumb'] = 'Inicio > Gestión de usuarios > Bitácora';
    
    // Obtener todos los usuarios activos y suspendidos
    $this->db->select('id, nombre, apellidos, username, estatus, fecha_alta');
    $this->db->from('administradores');
    $this->db->order_by('nombre', 'ASC');
    $usuarios = $this->db->get()->result();
    
    $this->viewData['response'] = [
      'usuarios' => $usuarios
    ];
    
    $this->viewData['pageView'] = 'usuarios/bitacora';
    
    // Render view
    $this->load->view('layouts/general_template', $this->viewData);
  }
  
  /**
   * Obtiene el historial de actividades de un usuario (AJAX)
   */
  public function get_user_logs_ajax() {
    $user_id = $this->input->post('user_id');
    $limit = $this->input->post('limit') ?: 50;
    
    if(!$user_id) {
      echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
      return;
    }
    
    // Obtener username del usuario
    $this->db->select('username');
    $this->db->where('id', $user_id);
    $user = $this->db->get('administradores')->row();
    
    if(!$user) {
      echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
      return;
    }
    
    // Obtener logs del usuario
    $logs = $this->UserModel->last_logs($user->username, $limit);
    
    echo json_encode([
      'success' => true,
      'logs' => $logs->result()
    ]);
  }

  /**
   * Vista de importación masiva desde Excel
   */
  public function importar() {
    setViewSuccess('Importación masiva de usuarios');
    $this->viewData['pageTitle'] = 'Carga Masiva de Usuarios';
    $this->viewData['headTitle'] = 'Carga Masiva (Excel)';
    $this->viewData['breadcrumb'] = 'Inicio > Gestión de usuarios > Importar';
    $this->viewData['pageView'] = 'usuarios/importar';
    
    $this->load->view('layouts/general_template', $this->viewData);
  }

  /**
   * Descarga la plantilla de Excel para importación
   */
  public function descargar_plantilla() {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Cabeceras
    $sheet->setCellValue('A1', 'Nombre');
    $sheet->setCellValue('B1', 'Apellidos');
    $sheet->setCellValue('C1', 'Email (Username)');
    $sheet->setCellValue('D1', 'Password');
    $sheet->setCellValue('E1', 'Departamento');
    
    // Formato
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    foreach(range('A','E') as $columnID) {
      $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // Ejemplo
    $sheet->setCellValue('A2', 'Juan');
    $sheet->setCellValue('B2', 'Pérez');
    $sheet->setCellValue('C2', 'juan@ejemplo.com');
    $sheet->setCellValue('D2', 'password123');
    $sheet->setCellValue('E2', 'Ventas');
    
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="plantilla_usuarios_erp.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
  }

  /**
   * Procesa el archivo Excel subido
   */
  public function procesar_importacion() {
    if(empty($_FILES['archivo_excel']['name'])) {
      setViewError('Por favor seleccione un archivo');
      redirect('usuarios/GestionUsuarios/importar');
    }

    $config['upload_path'] = './uploads/temp/';
    $config['allowed_types'] = 'xlsx|xls';
    $config['max_size'] = 2048;
    $config['encrypt_name'] = true;

    if(!is_dir($config['upload_path'])) {
      mkdir($config['upload_path'], 0755, true);
    }

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('archivo_excel')) {
      setViewError($this->upload->display_errors());
      redirect('usuarios/GestionUsuarios/importar');
    } else {
      $file_data = $this->upload->data();
      $file_path = $file_data['full_path'];

      try {
        $spreadsheet = IOFactory::load($file_path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        $rows = [];
        // Saltamos la cabecera (índice 1)
        for($i = 2; $i <= count($sheetData); $i++) {
          if(!empty($sheetData[$i]['A'])) { // Solo si tiene nombre
            $rows[] = [
              'nombre' => $sheetData[$i]['A'],
              'apellidos' => $sheetData[$i]['B'],
              'username' => $sheetData[$i]['C'],
              'password' => $sheetData[$i]['D'],
              'departamento' => $sheetData[$i]['E']
            ];
          }
        }

        if(empty($rows)) {
          setViewError('El archivo está vacío o no tiene el formato correcto');
          unlink($file_path);
          redirect('usuarios/GestionUsuarios/importar');
        }

        $permissions = $this->config->item('permissions');
        $result = $this->UserModel->mod_bulk_insert_excel($rows, $permissions);

        unlink($file_path); // Borrar archivo temp

        $msg = "Carga finalizada. Insertados: {$result['inserted']}, Errores: {$result['errors']}, Omitidos: {$result['skipped']}";
        if(!empty($result['messages'])) {
          $msg .= "<br>Detalles:<br>" . implode("<br>", $result['messages']);
        }

        if($result['inserted'] > 0) {
          setViewSuccess($msg);
        } else {
          setViewError($msg);
        }
        
        redirect('usuarios/GestionUsuarios/importar');

      } catch (Exception $e) {
        setViewError('Error al procesar el archivo: ' . $e->getMessage());
        unlink($file_path);
        redirect('usuarios/GestionUsuarios/importar');
      }
    }
  }

}