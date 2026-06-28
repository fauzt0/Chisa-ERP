<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RecursosHumanos extends MY_Controller {

  protected $modulo = 'Recursos Humanos';
  
  public function __construct() 
  {
    parent::__construct();
    // Cargar modelos específicos
    $this->load->model("RH/EmpleadoModel");
    $this->load->model("RH/DepartamentoModel");
    $this->load->model("RH/DocumentoEmpleadoModel");
    $this->load->model("RH/EmpleadoUsuarioModel");
    
    // El controlador base ya maneja la sesión y los permisos del módulo
  }

  // ========================================================================
  // VISTA PRINCIPAL
  // ========================================================================

  public function index(){
    setViewSuccess('Recursos Humanos cargado correctamente');
    $this->viewData['pageTitle'] = 'Recursos Humanos';
    $this->viewData['headTitle'] = 'Gestión de Empleados';
    $this->viewData['breadcrumb'] = 'Inicio > Recursos Humanos';
    
    // Obtener estadísticas
    $stats = $this->EmpleadoModel->get_estadisticas_rh();
    $por_departamento = $this->EmpleadoModel->get_empleados_por_departamento();
    
    $this->viewData['response'] = [
      'stats' => $stats,
      'por_departamento' => $por_departamento,
      'departamentos' => $this->DepartamentoModel->get_lista_departamentos(),
      'datos_faltantes' => [] // Inicializar vacío
    ];

    // Verificar permiso para ver alertas de datos faltantes
    if($this->init_controller->has_permission($this->session->userdata('id'), 'Consultar empleados')){
      $this->viewData['response']['datos_faltantes'] = $this->EmpleadoModel->get_empleados_datos_faltantes();
      $this->viewData['response']['expedientes_incompletos'] = $this->DocumentoEmpleadoModel->get_empleados_expediente_incompleto(10);
      $this->viewData['response']['total_expedientes_incompletos'] = $this->DocumentoEmpleadoModel->contar_expedientes_incompletos();
    }
    
    // Obtener solicitudes de vacaciones pendientes (Alerta)
    $this->load->model('RH/VacacionesModel');
    $this->viewData['response']['vacaciones_pendientes'] = count($this->VacacionesModel->get_todas_solicitudes('Pendiente'));
    $this->viewData['response']['puede_ver_reloj'] = $this->init_controller->has_permission(
        $this->session->userdata('id'),
        'reloj_ver_reportes'
    );
    $this->viewData['response']['vinculo_usuarios_habilitado'] = $this->EmpleadoUsuarioModel->tiene_vinculo_habilitado();
    $this->viewData['response']['usuarios_sin_empleado'] = $this->EmpleadoUsuarioModel->tiene_vinculo_habilitado()
        ? count($this->EmpleadoUsuarioModel->usuarios_sin_empleado(500))
        : 0;
    $this->viewData['validate'] = '';
    $this->viewData['pageView'] = 'rh/empleados/main_empleados';
    

    // Render views
	  $this->load->view('layouts/general_template', $this->viewData);
  }

  // ========================================================================
  // BÚSQUEDA DATATABLES
  // ========================================================================

  public function search_empleados(){
    $list = $this->EmpleadoModel->get_datatables();
    $data = array();
    $no = $_POST['start'];

    // Obtener IDs de empleados con datos faltantes para badge
    $this->load->model('RH/EmpleadoModel');
    $datos_faltantes = $this->EmpleadoModel->get_empleados_datos_faltantes();
    $faltantes_ids = [];
    $faltantes_info = [];
    foreach ($datos_faltantes as $df) {
      $faltantes_ids[$df['id']] = true;
      $faltantes_info[$df['id']] = implode(', ', $df['faltantes']);
    }

    $expedientes_incompletos = $this->DocumentoEmpleadoModel->get_empleados_expediente_incompleto(500);
    $expediente_map = [];
    foreach ($expedientes_incompletos as $exp) {
      $expediente_map[$exp['id']] = $exp;
    }

    foreach ($list as $empleado) {
      $no++;
      $row = array();
      
      // Número de empleado
      $row[] = $empleado->numero_empleado;
      
      // Nombre completo (con badge si hay datos faltantes)
      $nombre_html = $empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno;
      if (isset($faltantes_ids[$empleado->id])) {
        $faltantes_str = htmlspecialchars($faltantes_info[$empleado->id], ENT_QUOTES, 'UTF-8');
        $nombre_html .= ' <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;cursor:pointer;" title="Faltan datos: ' . $faltantes_str . '" onclick="notificarFaltantes(' . $empleado->id . ')">⚠️</span>';
      }
      if (isset($expediente_map[$empleado->id])) {
        $exp_falt = htmlspecialchars(implode(', ', $expediente_map[$empleado->id]['faltantes']), ENT_QUOTES, 'UTF-8');
        $nombre_html .= ' <span class="badge bg-danger ms-1" style="font-size:0.65rem;cursor:pointer;" title="Expediente incompleto: ' . $exp_falt . '" onclick="empleado_detail(' . $empleado->id . ')">📁</span>';
      }
      if ($this->EmpleadoUsuarioModel->tiene_vinculo_habilitado()) {
        $usr = $this->EmpleadoUsuarioModel->get_usuario_por_empleado($empleado->id);
        if ($usr) {
          $nombre_html .= ' <span class="badge bg-primary ms-1" style="font-size:0.65rem;" title="Usuario ERP: ' . htmlspecialchars($usr->username, ENT_QUOTES) . '"><i class="fas fa-user-lock"></i></span>';
        }
      }
      $row[] = $nombre_html;
      
      // Puesto
      $row[] = $empleado->puesto;
      
      // Departamento
      $row[] = $empleado->departamento_nombre ?? '<span class="text-muted">Sin departamento</span>';
      
      // Estatus
      if($empleado->estatus == 1){
        $row[] = '<span class="badge bg-success">Activo</span>';
      } else {
        $row[] = '<span class="badge bg-danger">Inactivo</span>';
      }
      
      // Acciones
      $acciones = '
        <button type="button" class="btn btn-sm btn-primary" onclick="empleado_detail('.$empleado->id.')">
          <i class="fas fa-eye"></i>
        </button>
        <a href="'.base_url('rh/RecursosHumanos/editar/'.$empleado->id).'" class="btn btn-sm btn-warning">
          <i class="fas fa-edit"></i>
        </a>';
      
      if($empleado->estatus == 1){
        $acciones .= '
          <button type="button" class="btn btn-sm btn-danger" onclick="delete_empleado('.$empleado->id.')">
            <i class="fas fa-trash"></i>
          </button>';
      }
      
      $row[] = $acciones;
      
      $data[] = $row;
    }

    $output = array(
      "draw" => $_POST['draw'],
      "recordsTotal" => $this->EmpleadoModel->count_all(),
      "recordsFiltered" => $this->EmpleadoModel->count_filtered(),
      "data" => $data,
    );

    echo json_encode($output);
  }

  // ========================================================================
  // ALTA DE EMPLEADO
  // ========================================================================

  public function alta(){
    // Validaciones
    $validation = [
      ['field' => 'nombre', 'label' => 'Nombre', 'rules' => 'required|trim|max_length[100]'],
      ['field' => 'apellido_paterno', 'label' => 'Apellido Paterno', 'rules' => 'required|trim|max_length[100]'],
      ['field' => 'rfc', 'label' => 'RFC', 'rules' => 'required|exact_length[13]|is_unique[empleados.rfc]|callback_validar_rfc'],
      ['field' => 'curp', 'label' => 'CURP', 'rules' => 'required|exact_length[18]|is_unique[empleados.curp]|callback_validar_curp'],
      ['field' => 'puesto', 'label' => 'Puesto', 'rules' => 'required|max_length[100]'],
      ['field' => 'tipo_trabajador', 'label' => 'Tipo de Trabajador', 'rules' => 'required'],
      ['field' => 'fecha_ingreso', 'label' => 'Fecha de Ingreso', 'rules' => 'required'],
      ['field' => 'salario_base_mensual', 'label' => 'Salario Base', 'rules' => 'required|decimal|greater_than[0]'],
    ];

    $this->form_validation->set_rules($validation);

    if ($this->form_validation->run() == FALSE) {
      // Mostrar formulario
      $this->viewData['pageTitle'] = 'Alta de Empleado';
      $this->viewData['headTitle'] = 'Nuevo Empleado';
      $this->viewData['breadcrumb'] = 'Inicio > Recursos Humanos > Alta de empleado';
      
      // Obtener flashdata si existe
      $this->viewData['validate'] = $this->session->flashdata('validate') ?? '';
      $this->viewData['notification'] = $this->session->flashdata('notification') ?? null;
      
      // Obtener departamentos para select
      $departamentos = $this->DepartamentoModel->get_lista_departamentos();
      $empleados = $this->EmpleadoModel->get_lista_empleados_activos();
      
      $this->viewData['response'] = [
        'departamentos' => $departamentos,
        'empleados' => $empleados
      ];
      $this->viewData['pageView'] = 'rh/empleados/alta';
      
      $this->load->view('layouts/general_template', $this->viewData);
    } else {
      // Procesar alta
      $data = [
        'nombre' => $this->input->post('nombre'),
        'apellido_paterno' => $this->input->post('apellido_paterno'),
        'apellido_materno' => $this->input->post('apellido_materno'),
        'fecha_nacimiento' => $this->input->post('fecha_nacimiento'),
        'nacionalidad' => $this->input->post('nacionalidad'),
        'genero' => $this->input->post('genero'),
        'estado_civil' => $this->input->post('estado_civil'),
        'beneficiarios' => $this->input->post('beneficiarios'),
        'telefono' => $this->input->post('telefono'),
        'telefono_emergencia' => $this->input->post('telefono_emergencia'),
        'email_personal' => $this->input->post('email_personal'),
        'email_corporativo' => $this->input->post('email_corporativo'),
        'calle' => $this->input->post('calle'),
        'numero_exterior' => $this->input->post('numero_exterior'),
        'numero_interior' => $this->input->post('numero_interior'),
        'colonia' => $this->input->post('colonia'),
        'codigo_postal' => $this->input->post('codigo_postal'),
        'ciudad' => $this->input->post('ciudad'),
        'estado' => $this->input->post('estado'),
        'pais' => $this->input->post('pais') ?: 'México',
        'direccion' => trim($this->input->post('calle') . ' ' . $this->input->post('numero_exterior') . ' ' . ($this->input->post('numero_interior') ? 'Int. '.$this->input->post('numero_interior') : '') . ', ' . $this->input->post('colonia') . ', C.P. ' . $this->input->post('codigo_postal') . ', ' . $this->input->post('ciudad') . ', ' . $this->input->post('estado')),
        'rfc' => strtoupper($this->input->post('rfc')),
        'regimen_fiscal' => $this->input->post('regimen_fiscal'),
        'curp' => strtoupper($this->input->post('curp')),
        'nss' => $this->input->post('nss'),
        'afore' => $this->input->post('afore'),
        'afore_numero_cuenta' => $this->input->post('afore_numero_cuenta'),
        'tiene_fonacot' => $this->input->post('tiene_fonacot') ? 1 : 0,
        'tiene_infonavit' => $this->input->post('tiene_infonavit') ? 1 : 0,
        'descuento_infonavit' => $this->input->post('descuento_infonavit'),
        'tipo_trabajador' => $this->input->post('tipo_trabajador'),
        'departamento_id' => $this->input->post('departamento_id'),
        'puesto' => $this->input->post('puesto'),
        'jefe_directo_id' => $this->input->post('jefe_directo_id'),
        'fecha_ingreso' => $this->input->post('fecha_ingreso'),
        'salario_base_mensual' => $this->input->post('salario_base_mensual'),
        'pension_alimenticia_porcentaje' => $this->input->post('pension_alimenticia_porcentaje') ?: 0,
        'pension_alimenticia_monto' => $this->input->post('pension_alimenticia_monto') ?: 0,
        'isr_porcentaje' => $this->input->post('isr_porcentaje') ?: 0,
        'imss_cuota' => $this->input->post('imss_cuota') ?: 0,
        'infonavit_aportacion' => $this->input->post('infonavit_aportacion') ?: 0,
        'afore_aportacion' => $this->input->post('afore_aportacion') ?: 0,
        'tipo_nomina' => $this->input->post('tipo_nomina'),
        'forma_pago' => $this->input->post('forma_pago'),
        'banco' => $this->input->post('banco'),
        'cuenta_bancaria' => $this->input->post('cuenta_bancaria'),
      ];

      $result = $this->EmpleadoModel->mod_add($data);

      if($result['success'] == 1){
        $this->session->set_flashdata('validate', $this->init_controller->alert("success", $result['msg']));
        $this->session->set_flashdata('notification', ['msg' => $result['msg'], 'type' => 'success']);
        redirect('rh/RecursosHumanos');
      } else {
        $this->session->set_flashdata('validate', $this->init_controller->alert("danger", $result['msg']));
        $this->session->set_flashdata('notification', ['msg' => $result['msg'], 'type' => 'danger']);
        redirect('rh/RecursosHumanos/alta');
      }
    }
  }

  // ========================================================================
  // EDITAR EMPLEADO
  // ========================================================================

  public function editar($id = null){
    if(!$id){
      setViewError('ID de empleado no válido');
      redirect('rh/RecursosHumanos');
      return;
    }

    // Validaciones
    $validation = [
      ['field' => 'nombre', 'label' => 'Nombre', 'rules' => 'required|trim|max_length[100]'],
      ['field' => 'apellido_paterno', 'label' => 'Apellido Paterno', 'rules' => 'required|trim|max_length[100]'],
      ['field' => 'puesto', 'label' => 'Puesto', 'rules' => 'required|max_length[100]'],
      ['field' => 'tipo_trabajador', 'label' => 'Tipo de Trabajador', 'rules' => 'required'],
      ['field' => 'salario_base_mensual', 'label' => 'Salario Base', 'rules' => 'required|decimal|greater_than[0]'],
    ];

    $this->form_validation->set_rules($validation);

    if ($this->form_validation->run() == FALSE) {
      // Mostrar formulario
      $empleado = $this->EmpleadoModel->get_empleado_completo($id);
      
      if(!$empleado){
        setViewError('Empleado no encontrado');
        redirect('rh/RecursosHumanos');
        return;
      }

      $this->viewData['pageTitle'] = 'Editar Empleado';
      $this->viewData['headTitle'] = 'Editar Empleado: ' . $empleado->nombre;
      $this->viewData['breadcrumb'] = 'Inicio > Recursos Humanos > Editar empleado';
      
      // Obtener flashdata si existe
      $this->viewData['validate'] = $this->session->flashdata('validate') ?? '';
      $this->viewData['notification'] = $this->session->flashdata('notification') ?? null;
      
      $departamentos = $this->DepartamentoModel->get_lista_departamentos();
      $empleados = $this->EmpleadoModel->get_lista_empleados_activos();
      
      $this->viewData['response'] = [
        'empleado' => $empleado,
        'departamentos' => $departamentos,
        'empleados' => $empleados,
        'tipos_documento' => DocumentoEmpleadoModel::TIPOS_DOCUMENTO,
        'checklist' => $this->DocumentoEmpleadoModel->get_checklist_empleado($id),
        'usuario_vinculado' => $this->EmpleadoUsuarioModel->get_usuario_por_empleado($id),
        'vinculo_habilitado' => $this->EmpleadoUsuarioModel->tiene_vinculo_habilitado(),
      ];
      $this->viewData['pageView'] = 'rh/empleados/editar';
      
      $this->load->view('layouts/general_template', $this->viewData);
    } else {
      // Procesar actualización
      $data = [
        'nombre' => $this->input->post('nombre'),
        'apellido_paterno' => $this->input->post('apellido_paterno'),
        'apellido_materno' => $this->input->post('apellido_materno'),
        'fecha_nacimiento' => $this->input->post('fecha_nacimiento'),
        'nacionalidad' => $this->input->post('nacionalidad'),
        'genero' => $this->input->post('genero'),
        'estado_civil' => $this->input->post('estado_civil'),
        'beneficiarios' => $this->input->post('beneficiarios'),
        'telefono' => $this->input->post('telefono'),
        'telefono_emergencia' => $this->input->post('telefono_emergencia'),
        'email_personal' => $this->input->post('email_personal'),
        'email_corporativo' => $this->input->post('email_corporativo'),
        'calle' => $this->input->post('calle'),
        'numero_exterior' => $this->input->post('numero_exterior'),
        'numero_interior' => $this->input->post('numero_interior'),
        'colonia' => $this->input->post('colonia'),
        'codigo_postal' => $this->input->post('codigo_postal'),
        'ciudad' => $this->input->post('ciudad'),
        'estado' => $this->input->post('estado'),
        'pais' => $this->input->post('pais'),
        'direccion' => trim($this->input->post('calle') . ' ' . $this->input->post('numero_exterior') . ' ' . ($this->input->post('numero_interior') ? 'Int. '.$this->input->post('numero_interior') : '') . ', ' . $this->input->post('colonia') . ', C.P. ' . $this->input->post('codigo_postal') . ', ' . $this->input->post('ciudad') . ', ' . $this->input->post('estado')),
        'afore' => $this->input->post('afore'),
        'afore_numero_cuenta' => $this->input->post('afore_numero_cuenta'),
        'regimen_fiscal' => $this->input->post('regimen_fiscal'),
        'tiene_fonacot' => $this->input->post('tiene_fonacot') ? 1 : 0,
        'tiene_infonavit' => $this->input->post('tiene_infonavit') ? 1 : 0,
        'descuento_infonavit' => $this->input->post('descuento_infonavit'),
        'tipo_trabajador' => $this->input->post('tipo_trabajador'),
        'departamento_id' => $this->input->post('departamento_id'),
        'puesto' => $this->input->post('puesto'),
        'jefe_directo_id' => $this->input->post('jefe_directo_id'),
        'salario_base_mensual' => $this->input->post('salario_base_mensual'),
        'pension_alimenticia_porcentaje' => $this->input->post('pension_alimenticia_porcentaje') ?: 0,
        'pension_alimenticia_monto' => $this->input->post('pension_alimenticia_monto') ?: 0,
        'isr_porcentaje' => $this->input->post('isr_porcentaje') ?: 0,
        'imss_cuota' => $this->input->post('imss_cuota') ?: 0,
        'infonavit_aportacion' => $this->input->post('infonavit_aportacion') ?: 0,
        'afore_aportacion' => $this->input->post('afore_aportacion') ?: 0,
        'tipo_nomina' => $this->input->post('tipo_nomina'),
        'forma_pago' => $this->input->post('forma_pago'),
        'banco' => $this->input->post('banco'),
        'cuenta_bancaria' => $this->input->post('cuenta_bancaria'),
        'estatus' => $this->input->post('estatus'),
      ];

      $result = $this->EmpleadoModel->mod_update($id, $data);

      if($result['success'] == 1){
        $this->session->set_flashdata('validate', $this->init_controller->alert("success", $result['msg']));
        $this->session->set_flashdata('notification', ['msg' => $result['msg'], 'type' => 'success']);
      } else {
        $this->session->set_flashdata('validate', $this->init_controller->alert("danger", $result['msg']));
        $this->session->set_flashdata('notification', ['msg' => $result['msg'], 'type' => 'danger']);
      }
      
      redirect('rh/RecursosHumanos/editar/'.$id);
    }
  }

  // ========================================================================
  // ELIMINAR EMPLEADO (AJAX)
  // ========================================================================

  public function eliminar(){
    $id = $this->input->post('id');
    $motivo = $this->input->post('motivo_baja');

    $result = $this->EmpleadoModel->mod_delete($id, $motivo);

    echo json_encode([
      'success' => $result['success'] == 1,
      'message' => $result['msg']
    ]);
  }

  // ========================================================================
  // DETALLE EMPLEADO (AJAX)
  // ========================================================================

  public function detail(){
    $id = $this->input->post('id');
    $empleado = $this->EmpleadoModel->get_empleado_completo($id);

    if(!$empleado){
      echo json_encode(['response' => null]);
      return;
    }

    // Construir tabs con datos estructurados
    $tabs = [];

    // Tab 1: Información Personal
    $usuario_vinculado = $this->EmpleadoUsuarioModel->get_usuario_por_empleado($id);
    $usuario_html = '<span class="text-muted">Sin vincular</span>';
    if ($usuario_vinculado) {
        $usuario_html = '<strong>#' . (int)$usuario_vinculado->id . '</strong> — '
            . htmlspecialchars($usuario_vinculado->nombre . ' ' . $usuario_vinculado->apellidos)
            . '<br><small class="text-muted">' . htmlspecialchars($usuario_vinculado->username) . '</small>';
    }

    $tabs['personal'] = [
      'icon' => 'user',
      'label' => 'Personal',
      'fields' => [
        ['label' => 'Nombre completo', 'value' => $empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno, 'icon' => 'user'],
        ['label' => 'Usuario ERP', 'value' => $usuario_html, 'icon' => 'user-check'],
        ['label' => 'Género', 'value' => $empleado->genero, 'icon' => 'users'],
        ['label' => 'Estado Civil', 'value' => $empleado->estado_civil ?? 'No especificado', 'icon' => 'heart'],
        ['label' => 'Fecha Nacimiento', 'value' => $empleado->fecha_nacimiento ?? 'N/A', 'icon' => 'calendar'],
        ['label' => 'Nacionalidad', 'value' => $empleado->nacionalidad ?? 'N/A', 'icon' => 'flag'],
        ['label' => 'Teléfono', 'value' => $empleado->telefono ?? 'N/A', 'icon' => 'phone'],
        ['label' => 'Tel. Emergencia', 'value' => $empleado->telefono_emergencia ?? 'N/A', 'icon' => 'phone-call'],
        ['label' => 'Email Personal', 'value' => $empleado->email_personal ?? 'N/A', 'icon' => 'mail'],
        ['label' => 'Email Corporativo', 'value' => $empleado->email_corporativo ?? 'N/A', 'icon' => 'briefcase'],
      ]
    ];

    // Tab 2: Información Fiscal
    $rfc_color = empty($empleado->rfc) ? 'text-danger' : '';
    $curp_color = empty($empleado->curp) ? 'text-danger' : '';
    $nss_color = empty($empleado->nss) ? 'text-danger' : '';

    $tabs['fiscal'] = [
      'icon' => 'file-text',
      'label' => 'Fiscal',
      'fields' => [
        ['label' => 'N° Empleado', 'value' => $empleado->numero_empleado, 'icon' => 'hash', 'css_class' => ''],
        ['label' => 'RFC', 'value' => $empleado->rfc ?: '<span class="text-danger fw-bold">FALTANTE</span>', 'icon' => 'credit-card', 'css_class' => $rfc_color],
        ['label' => 'CURP', 'value' => $empleado->curp ?: '<span class="text-danger fw-bold">FALTANTE</span>', 'icon' => 'shield', 'css_class' => $curp_color],
        ['label' => 'NSS', 'value' => $empleado->nss ?: '<span class="text-danger fw-bold">FALTANTE</span>', 'icon' => 'activity', 'css_class' => $nss_color],
        ['label' => 'Afore', 'value' => $empleado->afore ?? 'N/A', 'icon' => 'database'],
        ['label' => 'Cuenta Bancaria', 'value' => $empleado->banco ? ($empleado->banco . ' - Cuenta: ' . ($empleado->cuenta_bancaria ?? 'N/A')) : 'N/A', 'icon' => 'dollar-sign'],
        ['label' => 'Régimen Fiscal', 'value' => $empleado->regimen_fiscal ?? 'N/A', 'icon' => 'bookmark'],
      ]
    ];

    // Tab 3: Información Laboral
    $tabs['laboral'] = [
      'icon' => 'briefcase',
      'label' => 'Laboral',
      'fields' => [
        ['label' => 'Puesto', 'value' => $empleado->puesto, 'icon' => 'award'],
        ['label' => 'Departamento', 'value' => $empleado->departamento_nombre ?? '<span class="text-muted">Sin departamento</span>', 'icon' => 'grid'],
        ['label' => 'Tipo Trabajador', 'value' => $empleado->tipo_trabajador, 'icon' => 'clipboard'],
        ['label' => 'Tipo Nómina', 'value' => $empleado->tipo_nomina ?? 'N/A', 'icon' => 'dollar-sign'],
        ['label' => 'Salario Mensual', 'value' => '$' . number_format($empleado->salario_base_mensual, 2), 'icon' => 'trending-up'],
        ['label' => 'Fecha Ingreso', 'value' => date('d M Y', strtotime($empleado->fecha_ingreso)), 'icon' => 'calendar'],
        ['label' => 'Estatus', 'value' => $empleado->estatus == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>', 'icon' => 'check-circle'],
        ['label' => 'Jefe Directo', 'value' => $empleado->jefe_nombre ?? 'N/A', 'icon' => 'user-plus'],
      ]
    ];

    // Tab 4: Documentos (resumen + checklist)
    $total_docs = $this->DocumentoEmpleadoModel->contar_por_empleado($id);
    $checklist = $this->DocumentoEmpleadoModel->get_checklist_empleado($id);
    $checklist_html = '<div class="progress mb-2" style="height:8px;"><div class="progress-bar bg-' . ($checklist['completo'] ? 'success' : 'warning') . '" style="width:' . $checklist['porcentaje'] . '%"></div></div>';
    $checklist_html .= '<small class="text-muted">' . $checklist['completados'] . '/' . $checklist['total_req'] . ' documentos requeridos</small>';
    if (!$checklist['completo']) {
        $checklist_html .= '<div class="mt-2">';
        foreach ($checklist['items'] as $item) {
            $icon = $item['tiene'] ? '✅' : '❌';
            $cls = $item['tiene'] ? 'text-success' : 'text-danger';
            $checklist_html .= '<div class="small ' . $cls . '">' . $icon . ' ' . htmlspecialchars($item['label']) . '</div>';
        }
        $checklist_html .= '</div>';
    }

    $tabs['documentos'] = [
      'icon' => 'folder',
      'label' => 'Documentos',
      'fields' => [
        ['label' => 'Expediente', 'value' => $checklist_html, 'icon' => 'clipboard-check'],
        ['label' => 'Archivos adjuntos', 'value' => $total_docs . ' documento(s)', 'icon' => 'paperclip'],
        ['label' => 'Estado', 'value' => $checklist['completo']
          ? '<span class="badge bg-success">Expediente completo</span>'
          : '<span class="badge bg-warning text-dark">Faltan ' . count($checklist['faltantes']) . ' documento(s)</span>', 'icon' => 'folder-open'],
      ],
    ];

    // Acciones
    $actions = [
      'editar' => base_url('rh/RecursosHumanos/editar/' . $id),
      'nuevo_contrato' => base_url('rh/RecursosHumanos/nuevo_contrato/' . $id),
      'mostrar_finiquito' => ($empleado->estatus == 1),
      'mostrar_baja' => ($empleado->estatus == 1),
      'empleado_id' => $id,
      'total_documentos' => $total_docs,
      'checklist' => $checklist,
      'vinculo_habilitado' => $this->EmpleadoUsuarioModel->tiene_vinculo_habilitado(),
      'usuario_vinculado' => $usuario_vinculado ? [
        'id' => (int)$usuario_vinculado->id,
        'nombre' => $usuario_vinculado->nombre . ' ' . $usuario_vinculado->apellidos,
        'username' => $usuario_vinculado->username,
      ] : null,
    ];

    echo json_encode([
      'success' => true,
      'response' => $empleado,
      'nombre_completo' => $empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno,
      'numero_empleado' => $empleado->numero_empleado,
      'tabs' => $tabs,
      'actions' => $actions
    ]);
  }

  // ========================================================================
  // VINCULACIÓN USUARIO ERP ↔ EMPLEADO
  // ========================================================================

  public function usuarios_buscar_ajax() {
    $termino = trim((string)$this->input->post('q'));
    $empleado_id = (int)$this->input->post('empleado_id');
    $usuarios = $this->EmpleadoUsuarioModel->buscar_usuarios($termino, $empleado_id ?: null);
    $lista = [];
    foreach ($usuarios as $u) {
      $lista[] = [
        'id' => (int)$u->id,
        'nombre' => trim($u->nombre . ' ' . $u->apellidos),
        'username' => $u->username,
        'departamento' => $u->departamento,
        'vinculado_a_este' => $empleado_id && (int)$u->empleado_id === $empleado_id,
        'ocupado' => !empty($u->empleado_id) && (!$empleado_id || (int)$u->empleado_id !== $empleado_id),
      ];
    }
    echo json_encode(['success' => true, 'usuarios' => $lista]);
  }

  public function usuarios_sin_empleado_ajax() {
    $usuarios = $this->EmpleadoUsuarioModel->usuarios_sin_empleado(100);
    $lista = [];
    foreach ($usuarios as $u) {
      $lista[] = [
        'id' => (int)$u->id,
        'nombre' => trim($u->nombre . ' ' . $u->apellidos),
        'username' => $u->username,
        'departamento' => $u->departamento,
      ];
    }
    echo json_encode(['success' => true, 'usuarios' => $lista, 'total' => count($lista)]);
  }

  public function vincular_usuario_ajax() {
    $empleado_id = (int)$this->input->post('empleado_id');
    $usuario_id = (int)$this->input->post('usuario_id');
    if (!$empleado_id || !$usuario_id) {
      echo json_encode(['success' => false, 'message' => 'Empleado y usuario son requeridos']);
      return;
    }
    echo json_encode($this->EmpleadoUsuarioModel->vincular($empleado_id, $usuario_id));
  }

  public function desvincular_usuario_ajax() {
    $empleado_id = (int)$this->input->post('empleado_id');
    if (!$empleado_id) {
      echo json_encode(['success' => false, 'message' => 'Empleado requerido']);
      return;
    }
    echo json_encode($this->EmpleadoUsuarioModel->desvincular($empleado_id));
  }

  public function crear_empleado_desde_usuario_ajax() {
    $usuario_id = (int)$this->input->post('usuario_id');
    if (!$usuario_id) {
      echo json_encode(['success' => false, 'message' => 'Usuario requerido']);
      return;
    }
    echo json_encode($this->EmpleadoUsuarioModel->crear_empleado_desde_usuario($usuario_id));
  }

  // ========================================================================
  // DOCUMENTOS DEL EMPLEADO
  // ========================================================================

  public function documentos_listar() {
    $empleado_id = (int)$this->input->post('empleado_id');
    if (!$empleado_id) {
      echo json_encode(['success' => false, 'message' => 'Empleado requerido']);
      return;
    }

    $docs = $this->DocumentoEmpleadoModel->get_por_empleado($empleado_id);
    $lista = [];
    foreach ($docs as $doc) {
      $lista[] = [
        'id'              => $doc->id,
        'tipo_documento'  => $doc->tipo_documento,
        'tipo_label'      => $this->DocumentoEmpleadoModel->label_tipo($doc->tipo_documento),
        'nombre_archivo'  => $doc->nombre_archivo,
        'ruta_archivo'    => $doc->ruta_archivo,
        'url'             => base_url($doc->ruta_archivo),
        'tamano'          => $this->DocumentoEmpleadoModel->formatear_tamano($doc->tamano_bytes),
        'fecha_subida'    => date('d/m/Y H:i', strtotime($doc->fecha_subida)),
        'observaciones'   => $doc->observaciones,
      ];
    }

    echo json_encode([
      'success' => true,
      'documentos' => $lista,
      'tipos' => DocumentoEmpleadoModel::TIPOS_DOCUMENTO,
      'checklist' => $this->DocumentoEmpleadoModel->get_checklist_empleado($empleado_id),
    ]);
  }

  public function expediente_checklist() {
    $empleado_id = (int)$this->input->post('empleado_id');
    if (!$empleado_id) {
      echo json_encode(['success' => false, 'message' => 'Empleado requerido']);
      return;
    }
    echo json_encode([
      'success' => true,
      'checklist' => $this->DocumentoEmpleadoModel->get_checklist_empleado($empleado_id),
    ]);
  }

  public function documento_subir() {
    $empleado_id = (int)$this->input->post('empleado_id');
    $tipo = $this->input->post('tipo_documento');

    if (!$empleado_id || !$tipo) {
      echo json_encode(['success' => false, 'message' => 'Empleado y tipo de documento son requeridos']);
      return;
    }

    if (empty($_FILES['archivo']['name'])) {
      echo json_encode(['success' => false, 'message' => 'Seleccione un archivo']);
      return;
    }

    $upload_path = './uploads/empleados/' . $empleado_id . '/';
    if (!is_dir($upload_path)) {
      mkdir($upload_path, 0755, true);
    }

    $config = [
      'upload_path'   => $upload_path,
      'allowed_types' => 'pdf|jpg|jpeg|png|gif|webp',
      'max_size'      => 10240,
      'encrypt_name'  => true,
    ];

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('archivo')) {
      echo json_encode(['success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))]);
      return;
    }

    $upload_data = $this->upload->data();
    $ruta = 'uploads/empleados/' . $empleado_id . '/' . $upload_data['file_name'];

    $id = $this->DocumentoEmpleadoModel->insertar([
      'empleado_id'       => $empleado_id,
      'tipo_documento'    => $tipo,
      'nombre_archivo'    => $_FILES['archivo']['name'],
      'ruta_archivo'      => $ruta,
      'tamano_bytes'      => $upload_data['file_size'] * 1024,
      'mime_type'         => $upload_data['file_type'],
      'observaciones'     => $this->input->post('observaciones'),
      'usuario_subida_id' => $this->session->userdata('id'),
    ]);

    echo json_encode([
      'success' => true,
      'message' => 'Documento subido correctamente',
      'id'      => $id,
    ]);
  }

  public function documento_eliminar() {
    $id = (int)$this->input->post('id');
    $empleado_id = (int)$this->input->post('empleado_id');

    if (!$id) {
      echo json_encode(['success' => false, 'message' => 'Documento requerido']);
      return;
    }

    if ($this->DocumentoEmpleadoModel->eliminar($id, $empleado_id ?: null)) {
      echo json_encode(['success' => true, 'message' => 'Documento eliminado']);
    } else {
      echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el documento']);
    }
  }

  /**
   * Obtiene datos para la calculadora de baja (AJAX)
   */
  public function get_datos_calculadora(){
    $id = $this->input->post('id');
    $empleado = $this->EmpleadoModel->get_empleado_completo($id);

    if(!$empleado){
      echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
      return;
    }

    // Calcular antigüedad
    $fecha_ingreso = new DateTime($empleado->fecha_ingreso);
    $hoy = new DateTime();
    $diferencia = $fecha_ingreso->diff($hoy);
    $antiguedad_anios = $diferencia->y + ($diferencia->m / 12) + ($diferencia->d / 365);

    echo json_encode([
      'success' => true,
      'nombre' => $empleado->nombre . ' ' . $empleado->apellido_paterno,
      'fecha_ingreso' => $empleado->fecha_ingreso,
      'salario_mensual' => $empleado->salario_base_mensual,
      'salario_diario' => $empleado->salario_base_mensual / 30, // Aproximado, ajustar si hay diario real en BD
      'antiguedad_anios' => number_format($antiguedad_anios, 2),
      'antiguedad_dias' => $diferencia->days
    ]);
  }

  // ========================================================================
  // VALIDACIONES PERSONALIZADAS
  // ========================================================================

  public function validar_rfc($rfc){
    if(!$this->EmpleadoModel->validar_rfc($rfc)){
      $this->form_validation->set_message('validar_rfc', 'El RFC no tiene un formato válido');
      return FALSE;
    }
    return TRUE;
  }

  public function validar_curp($curp){
    if(!$this->EmpleadoModel->validar_curp($curp)){
      $this->form_validation->set_message('validar_curp', 'La CURP no tiene un formato válido');
      return FALSE;
    }
    return TRUE;
  }

  public function validar_nss($nss){
    if($nss && !$this->EmpleadoModel->validar_nss($nss)){
      $this->form_validation->set_message('validar_nss', 'El NSS debe tener 11 dígitos');
      return FALSE;
    }
    return TRUE;
  }

  // ========================================================================
  // HISTORIAL DE CONTRATOS
  // ========================================================================

  /**
   * Obtiene el historial de contratos de un empleado (AJAX)
   */
  public function historial_contratos(){
    $empleado_id = $this->input->post('empleado_id');
    $fecha_inicio = $this->input->post('fecha_inicio');
    $fecha_fin = $this->input->post('fecha_fin');
    
    if(!$empleado_id){
      echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
      return;
    }

    $this->load->model('RH/ContratoModel');
    $contratos = $this->ContratoModel->get_historial($empleado_id, $fecha_inicio, $fecha_fin);

    // Generar HTML del timeline
    $timeline_html = '';
    
    if(empty($contratos)){
      $timeline_html = '<li class="timeline-item"><p class="text-muted">No hay contratos registrados</p></li>';
    } else {
      foreach($contratos as $contrato){
        $badge = $contrato->vigente == 1 ? '<span class="badge bg-success">Vigente</span>' : '';
        $fecha = date('d M Y', strtotime($contrato->fecha_creacion));
        
        $timeline_html .= '
          <li class="timeline-item">
            <strong>' . $contrato->tipo_contrato . '</strong> ' . $badge . '
            <span class="float-end text-muted text-sm">' . $fecha . '</span>
            <p>' . $contrato->motivo_cambio . '</p>
            <button class="btn btn-sm btn-primary" onclick="verContrato(' . $contrato->id . ')">
              <i class="fas fa-eye"></i> Ver
            </button>
            <button class="btn btn-sm btn-danger" onclick="descargarPDFDirecto(' . $contrato->id . ')">
              <i class="fas fa-file-pdf"></i> PDF
            </button>
          </li>';
      }
    }

    echo json_encode([
      'success' => true,
      'timeline' => $timeline_html
    ]);
  }

  /**
   * Ver contrato específico (AJAX)
   */
  public function ver_contrato(){
    $contrato_id = $this->input->post('contrato_id');
    
    if(!$contrato_id){
      echo json_encode(['success' => false, 'message' => 'ID de contrato requerido']);
      return;
    }

    $this->load->model('RH/ContratoModel');
    $contrato = $this->ContratoModel->get_contrato_by_id($contrato_id);

    if(!$contrato){
      echo json_encode(['success' => false, 'message' => 'Contrato no encontrado']);
      return;
    }

    echo json_encode([
      'success' => true,
      'contrato' => $contrato
    ]);
  }

  // ========================================================================
  // GESTIÓN DE VACACIONES
  // ========================================================================

  /**
   * Obtiene el balance de vacaciones de un empleado (AJAX)
   */
  public function vacaciones_balance(){
    $empleado_id = $this->input->post('empleado_id');
    
    if(!$empleado_id){
      echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
      return;
    }

    $this->load->model('RH/VacacionesModel');
    
    // Obtener período actual
    $periodo = $this->VacacionesModel->get_balance_actual($empleado_id);
    
    // Obtener solicitudes
    $solicitudes = $this->VacacionesModel->get_solicitudes_empleado($empleado_id);
    
    // Obtener historial de períodos
    $historial = $this->VacacionesModel->get_historial_periodos($empleado_id);

    echo json_encode([
      'success' => true,
      'periodo' => $periodo,
      'solicitudes' => $solicitudes,
      'historial' => $historial
    ]);
  }

  /**
   * Solicita vacaciones (AJAX)
   */
  public function solicitar_vacaciones_ajax(){
    $this->load->model('RH/VacacionesModel');
    
    $empleado_id = $this->input->post('empleado_id');
    $fecha_inicio = $this->input->post('fecha_inicio');
    $fecha_fin = $this->input->post('fecha_fin');
    $observaciones = $this->input->post('observaciones');
    
    // Obtener período actual
    $periodo = $this->VacacionesModel->get_balance_actual($empleado_id);
    
    if(!$periodo){
      echo json_encode(['success' => false, 'message' => 'No hay período de vacaciones activo']);
      return;
    }
    
    // Calcular días solicitados
    $dias = $this->VacacionesModel->calcular_dias_habiles($fecha_inicio, $fecha_fin);
    
    // Crear solicitud
    $data = [
      'empleado_id' => $empleado_id,
      'periodo_vacaciones_id' => $periodo->id,
      'fecha_inicio' => $fecha_inicio,
      'fecha_fin' => $fecha_fin,
      'dias_solicitados' => $dias,
      'observaciones' => $observaciones
    ];
    
    $result = $this->VacacionesModel->solicitar_vacaciones($data);
    
    echo json_encode($result);
  }

  /**
   * Genera período de vacaciones manualmente
   */
  public function generar_periodo_vacaciones(){
    $empleado_id = $this->input->post('empleado_id');
    $dias_adicionales = $this->input->post('dias_adicionales') ?? 0;
    
    if(!$empleado_id){
      echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
      return;
    }

    $this->load->model('RH/VacacionesModel');
    $result = $this->VacacionesModel->generar_periodo_anual($empleado_id, $dias_adicionales);
    
    // Si el resultado es booleano (compatibilidad hacia atras o error inesperado) lo manejamos,
    // pero idealmente ahora es un array ['success', 'message']
    if(is_array($result)){
        echo json_encode($result);
    } else {
        // Fallback por si acaso
        if($result){
            echo json_encode(['success' => true, 'message' => 'Período generado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo generar el período (Error desconocido)']);
        }
    }
  }

  /**
   * Obtiene todas las solicitudes del sistema (AJAX)
   */
  public function solicitudes_vacaciones_lista(){
    $this->load->model('RH/VacacionesModel');
    $estatus = $this->input->post('estatus') ?: null;
    $solicitudes = $this->VacacionesModel->get_todas_solicitudes($estatus);
    
    echo json_encode(['success' => true, 'solicitudes' => $solicitudes]);
  }

  /**
   * Aprueba una solicitud de vacaciones (AJAX)
   */
  public function aprobar_vacaciones_ajax(){
    $this->load->model('RH/VacacionesModel');
    $solicitud_id = $this->input->post('id');
    $admin_id = $this->session->userdata('id') ?: null;
    
    if(!$solicitud_id){
      echo json_encode(['success' => false, 'message' => 'ID de solicitud requerido']);
      return;
    }

    $result = $this->VacacionesModel->aprobar_solicitud($solicitud_id, $admin_id);
    
    if($result){
      echo json_encode(['success' => true, 'message' => 'Solicitud aprobada correctamente']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Error al aprobar la solicitud']);
    }
  }

  /**
   * Rechaza una solicitud de vacaciones (AJAX)
   */
  public function rechazar_vacaciones_ajax(){
    $this->load->model('RH/VacacionesModel');
    $solicitud_id = $this->input->post('id');
    $motivo = $this->input->post('motivo');
    $admin_id = $this->session->userdata('id') ?: null;
    
    if(!$solicitud_id){
      echo json_encode(['success' => false, 'message' => 'ID de solicitud requerido']);
      return;
    }

    $result = $this->VacacionesModel->rechazar_solicitud($solicitud_id, $motivo, $admin_id);
    
    if($result){
      echo json_encode(['success' => true, 'message' => 'Solicitud rechazada correctamente y días devueltos']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Error al rechazar la solicitud']);
    }
  }

  public function incidencias_lista(){
    $this->load->model('RH/IncidenciasModel');
    $empleado_id = $this->input->post('empleado_id');
    if(!$empleado_id){ echo json_encode(['success' => false]); return; }
    $filtros = [];
    if($this->input->post('tipo_incidencia')) $filtros['tipo_incidencia'] = $this->input->post('tipo_incidencia');
    if($this->input->post('fecha_desde')) $filtros['fecha_desde'] = $this->input->post('fecha_desde');
    if($this->input->post('fecha_hasta')) $filtros['fecha_hasta'] = $this->input->post('fecha_hasta');
    $incidencias = $this->IncidenciasModel->get_incidencias_empleado($empleado_id, $filtros);
    $estadisticas = $this->IncidenciasModel->get_estadisticas_empleado($empleado_id);
    echo json_encode(['success' => true, 'incidencias' => $incidencias, 'estadisticas' => $estadisticas]);
  }

  public function registrar_incidencia_ajax(){
    $this->load->model('RH/IncidenciasModel');
    
    // Configuración para subir archivo
    $config['upload_path']   = './uploads/incidencias/';
    $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
    $config['max_size']      = 5120; // 5MB
    $config['encrypt_name']  = TRUE; // Renombrar para evitar conflictos

    $this->load->library('upload', $config);

    // Verificar si el directorio existe
    if (!is_dir($config['upload_path'])) {
        mkdir($config['upload_path'], 0777, TRUE);
    }

    $archivo_evidencia = null;

    if (!empty($_FILES['archivo_evidencia']['name'])) {
        if ($this->upload->do_upload('archivo_evidencia')) {
            $upload_data = $this->upload->data();
            $archivo_evidencia = 'uploads/incidencias/' . $upload_data['file_name'];
        } else {
            echo json_encode(['success' => false, 'message' => $this->upload->display_errors('', '')]);
            return;
        }
    }

    $data = [
      'empleado_id' => $this->input->post('empleado_id'),
      'tipo_incidencia' => $this->input->post('tipo_incidencia'),
      'fecha_incidencia' => $this->input->post('fecha_incidencia'),
      'hora_incidencia' => $this->input->post('hora_incidencia'),
      'descripcion' => $this->input->post('descripcion'),
      'observaciones' => $this->input->post('observaciones'),
      'tiene_descuento' => $this->input->post('tiene_descuento') ? 1 : 0,
      'monto_descuento' => $this->input->post('monto_descuento'),
      'archivo_adjunto' => $archivo_evidencia,
      'registrado_por' => $this->session->userdata('id') ?: null
    ];
    
    if(!$data['empleado_id'] || !$data['tipo_incidencia'] || !$data['fecha_incidencia']){
      echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
      if($archivo_evidencia && file_exists($archivo_evidencia)) unlink($archivo_evidencia); // Borrar si falla
      return;
    }
    
    $result = $this->IncidenciasModel->registrar_incidencia($data);
    echo json_encode($result ? ['success' => true, 'message' => 'Incidencia registrada correctamente'] : ['success' => false, 'message' => 'Error al registrar']);
  }

  public function cancelar_incidencia_ajax(){
    $this->load->model('RH/IncidenciasModel');
    $id = $this->input->post('id');
    if(!$id){ echo json_encode(['success' => false]); return; }
    $result = $this->IncidenciasModel->cancelar_incidencia($id);
    echo json_encode($result ? ['success' => true] : ['success' => false]);
  }

// ========================================================================
  // GESTIÓN DE HORARIOS
  // ========================================================================
  /**
   * Obtiene el horario de un empleado (AJAX)
   */
  public function horario_empleado(){
    $this->load->model('RH/HorariosModel');
    $empleado_id = $this->input->post('empleado_id');
    
    if(!$empleado_id){
      echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
      return;
    }
    
    $horarios = $this->HorariosModel->get_horario_empleado($empleado_id);
    $resumen = $this->HorariosModel->get_resumen_horario($empleado_id);
    $tiene_horario = $this->HorariosModel->tiene_horario($empleado_id);
    
    echo json_encode([
      'success' => true,
      'horarios' => $horarios,
      'resumen' => $resumen,
      'tiene_horario' => $tiene_horario
    ]);
  }
  /**
   * Guarda el horario de un empleado (AJAX)
   */
  public function guardar_horario_ajax(){
    $this->load->model('RH/HorariosModel');
    $empleado_id = $this->input->post('empleado_id');
    $fecha_inicio = $this->input->post('fecha_inicio') ?: date('Y-m-d');
    
    if(!$empleado_id){
      echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
      return;
    }
    
    // Recopilar horarios de todos los días
    $horarios = [];
    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    
    foreach($dias as $dia){
      $horarios[$dia] = [
        'hora_entrada' => $this->input->post($dia.'_entrada'),
        'hora_salida' => $this->input->post($dia.'_salida'),
        'hora_entrada_comida' => $this->input->post($dia.'_comida_entrada'),
        'hora_salida_comida' => $this->input->post($dia.'_comida_salida'),
        'es_dia_laboral' => $this->input->post($dia.'_laboral') ? 1 : 0,
        'turno' => $this->input->post($dia.'_turno'),
        'observaciones' => $this->input->post($dia.'_observaciones')
      ];
    }
    
    $creado_por = $this->session->userdata('id') ?: null;
    $result = $this->HorariosModel->guardar_horario($empleado_id, $horarios, $fecha_inicio, $creado_por);
    
    if($result){
      echo json_encode(['success' => true, 'message' => 'Horario guardado correctamente']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Error al guardar el horario']);
    }
  }
  /**
   * Crea un horario estándar para un empleado (AJAX)
   */
  public function crear_horario_estandar_ajax(){
    $this->load->model('RH/HorariosModel');
    $empleado_id = $this->input->post('empleado_id');
    
    if(!$empleado_id){
      echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
      return;
    }
    
    $creado_por = $this->session->userdata('id') ?: null;
    $result = $this->HorariosModel->crear_horario_estandar($empleado_id, $creado_por);
    
    if($result){
      echo json_encode(['success' => true, 'message' => 'Horario estándar creado (Lun-Vie 9-18hrs)']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Error al crear horario']);
    }
  }



  // ========================================================================
  // GESTIÓN DE PLANTILLAS DE CONTRATO
  // ========================================================================

  public function plantillas() {
    setViewSuccess('Plantillas de Contrato cargadas');
    $this->viewData['pageTitle'] = 'Plantillas de Contrato';
    $this->viewData['headTitle'] = 'Gestión de Contratos';
    $this->viewData['breadcrumb'] = 'Inicio > RH > Plantillas';
    
    $this->load->model('RH/PlantillaModel');
    $this->viewData['plantillas'] = $this->PlantillaModel->get_todas_activas();
    
    $this->viewData['pageView'] = 'rh/empleados/plantillas';
    $this->load->view('layouts/general_template', $this->viewData);
  }

  public function crear_plantilla() {
    $this->viewData['pageTitle'] = 'Nueva Plantilla';
    $this->viewData['headTitle'] = 'Crear Plantilla';
    $this->viewData['breadcrumb'] = 'Inicio > RH > Plantillas > Nueva';
    $this->viewData['plantilla'] = null;
    $this->viewData['pageView'] = 'rh/empleados/form_plantilla';
    $this->load->view('layouts/general_template', $this->viewData);
  }

  public function editar_plantilla($id) {
    if(!$id) redirect('rh/RecursosHumanos/plantillas');
    
    $this->load->model('RH/PlantillaModel');
    $plantilla = $this->db->where('id', $id)->get('contrato_plantillas')->row();
    
    if(!$plantilla) {
      $this->session->set_flashdata('error', 'Plantilla no encontrada');
      redirect('rh/RecursosHumanos/plantillas');
    }
    
    $this->viewData['pageTitle'] = 'Editar Plantilla';
    $this->viewData['headTitle'] = 'Editar: ' . $plantilla->nombre;
    $this->viewData['breadcrumb'] = 'Inicio > RH > Plantillas > Editar';
    $this->viewData['plantilla'] = $plantilla;
    $this->viewData['pageView'] = 'rh/empleados/form_plantilla';
    $this->load->view('layouts/general_template', $this->viewData);
  }

  public function guardar_plantilla() {
    $this->load->model('RH/PlantillaModel');
    
    $id = $this->input->post('id');
    $data = [
      'nombre' => $this->input->post('nombre'),
      'descripcion' => $this->input->post('descripcion'),
      'contenido' => $this->input->post('contenido'), // TinyMCE content
      'color_corporativo' => $this->input->post('color_corporativo'),
      'domicilio_empresa' => $this->input->post('domicilio_empresa')
    ];
    
    // Procesar Logo
    if(!empty($_FILES['logo']['name'])){
        $config['upload_path']   = './assets/uploads/logos/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['encrypt_name']  = TRUE;
        
        // Crear directorio si no existe (silenciosamente)
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('logo')){
            $uploadData = $this->upload->data();
            $data['logo'] = 'assets/uploads/logos/' . $uploadData['file_name'];
        } else {
            // Si falla la subida, notificar pero quizás guardar lo demás?
            // Por ahora, solo loguear o mostrar error
             $this->session->set_flashdata('warning', 'Error al subir logo: ' . $this->upload->display_errors());
        }
    }
    
    if($id){
      $result = $this->PlantillaModel->actualizar($id, $data);
      $msg = 'Plantilla actualizada correctamente';
    } else {
      $result = $this->PlantillaModel->guardar($data);
      $msg = 'Plantilla creada correctamente';
    }
    
    if($result){
      $this->session->set_flashdata('success', $msg);
      redirect('rh/RecursosHumanos/plantillas');
    } else {
      $this->session->set_flashdata('error', 'Error al guardar la plantilla');
      redirect($id ? 'rh/RecursosHumanos/editar_plantilla/'.$id : 'rh/RecursosHumanos/crear_plantilla');
    }
  }

  public function eliminar_plantilla($id) {
    $this->load->model('RH/PlantillaModel');
    if($this->PlantillaModel->desactivar($id)){
      echo json_encode(['success' => true, 'message' => 'Plantilla eliminada']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
    }
  }

  // ========================================================================
  // NUEVO CONTRATO (Renovación / Manual)
  // ========================================================================

  public function nuevo_contrato($empleado_id) {
    if(!$empleado_id) redirect('rh/RecursosHumanos');
    
    $this->load->model('RH/PlantillaModel');
    $this->load->model('RH/EmpleadoModel');
    
    $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
    if(!$empleado) redirect('rh/RecursosHumanos');
    
    $this->viewData['pageTitle'] = 'Nuevo Contrato';
    $this->viewData['headTitle'] = 'Contrato para: ' . $empleado->nombre;
    $this->viewData['breadcrumb'] = 'Inicio > RH > Empleados > Nuevo Contrato';
    $this->viewData['empleado'] = $empleado;
    $this->viewData['plantillas'] = $this->PlantillaModel->get_todas_activas();
    
    // Contratos anteriores para referencia
    $this->load->model('RH/ContratoModel');
    $this->viewData['historial'] = $this->ContratoModel->get_historial($empleado_id);
    
    $this->viewData['pageView'] = 'rh/empleados/nuevo_contrato';
    $this->load->view('layouts/general_template', $this->viewData);
  }
  
  /**
   * AJAX: Retorna el contenido de la plantilla con placeholders reemplazados
   */
  public function ajax_previsualizar_contrato() {
    $empleado_id = $this->input->post('empleado_id');
    $plantilla_id = $this->input->post('plantilla_id');
    
    if(!$empleado_id || !$plantilla_id) {
      echo json_encode(['success' => false, 'message' => 'Faltan datos']);
      return;
    }
    
    $this->load->model('RH/PlantillaModel');
    $plantilla = $this->db->where('id', $plantilla_id)->get('contrato_plantillas')->row();
    
    if(!$plantilla){
       echo json_encode(['success' => false, 'message' => 'Plantilla no encontrada']);
       return;
    }
    
    $this->load->model('RH/ContratoModel');
    $this->load->model('RH/EmpleadoModel');
    $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
    
    // Pasar datos de la plantilla al procesador para placeholders como color, logo, domicilio
    $contrato_data = [
        'color_corporativo' => $plantilla->color_corporativo ?? '#1a3a5c',
        'domicilio_empresa'  => $plantilla->domicilio_empresa ?? null,
        'logo'               => $plantilla->logo ?? null,
    ];
    $contenido_procesado = $this->ContratoModel->procesar_plantilla($plantilla->contenido, $empleado, $contrato_data);
    
    // Si tiene logo, agregarlo al inicio
    if(!empty($plantilla->logo) && file_exists('./' . $plantilla->logo)) {
        $logoHtml = '<div style="text-align: center; margin-bottom: 20px;"><img src="'.base_url($plantilla->logo).'" style="max-height: 100px; max-width: 200px;"></div>';
        $contenido_procesado = $logoHtml . $contenido_procesado;
    }
    
    echo json_encode(['success' => true, 'contenido' => $contenido_procesado]);
  }
  
  public function guardar_nuevo_contrato() {
    $empleado_id = $this->input->post('empleado_id');
    $tipo_contrato = $this->input->post('tipo_contrato');
    $motivo = $this->input->post('motivo');
    $plantilla_id = $this->input->post('plantilla_id');
    $plantilla_id = empty($plantilla_id) ? NULL : $plantilla_id;
    $contenido_personalizado = $this->input->post('contenido');

    $tipos_legales = [
        'Tiempo Indeterminado', 'Tiempo Determinado', 'Prueba (3 Meses)',
        'Capacitación Inicial', 'Por Obra Determinada', 'Sustitución',
    ];
    if (in_array($tipo_contrato, $tipos_legales, true)) {
        $tipo_legal = $tipo_contrato;
        $previos = (int)$this->db->where('empleado_id', (int)$empleado_id)->count_all_results('contratos_empleados');
        $tipo_contrato = $previos > 0 ? 'Renovación' : 'Inicial';
        $motivo = trim($tipo_legal . ($motivo ? ' — ' . $motivo : ''));
    }
    
    $guardar_como_plantilla = $this->input->post('guardar_como_plantilla');
    $nombre_nueva_plantilla = $this->input->post('nombre_nueva_plantilla');
    
    $this->load->model('RH/ContratoModel');
    
    // 1. Guardar el contrato para el empleado
    $result = $this->ContratoModel->crear_nuevo_contrato($empleado_id, $tipo_contrato, $motivo, $plantilla_id, $contenido_personalizado);
    
    if($result) {
      // 2. Si se solicitó, guardar como nueva plantilla
      if($guardar_como_plantilla == 1 && !empty($nombre_nueva_plantilla)){
        $this->load->model('RH/PlantillaModel');
        // Aquí hay un detalle: el contenido_personalizado YA TIENE los datos reemplazados del empleado.
        // Si lo guardamos como plantilla, tendrá "Juan Perez" hardcoded.
        // Lo ideal sería guardar el contenido ORIGINAL de la plantilla o pedir al usuario que restaure placeholders.
        // Pero el usuario pidió: "integrará la opción de añadir dichos ajustes como nuevas plantillas generales".
        // Asumiremos que el usuario sabe lo que hace, o mejor aún, si pudiéramos revertir cambios... es complejo.
        // Por ahora, guardamos lo que hay en el editor, pero advertimos al usuario en la vista.
        // Opcional: Podríamos intentar revertir los valores conocidos del empleado a placeholders, pero es arriesgado.
        
        $this->PlantillaModel->guardar([
          'nombre' => $nombre_nueva_plantilla, 
          'descripcion' => 'Generada desde contrato de empleado #' . $empleado_id, 
          'contenido' => $contenido_personalizado
        ]);
      }
      
      $this->session->set_flashdata('success', 'Contrato generado correctamente');
      redirect('rh/RecursosHumanos'); // O volver al detalle del empleado
    } else {
      $this->session->set_flashdata('error', 'Error al generar contrato');
      redirect('rh/RecursosHumanos/nuevo_contrato/' . $empleado_id);
    }
  }

  public function ajax_get_contrato($id) {
    if(!$id) return;
    $this->load->model('RH/ContratoModel');
    $contrato = $this->ContratoModel->get_contrato_by_id($id);
    if($contrato){
        echo json_encode(['success' => true, 'contenido' => $contrato->contrato_texto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contrato no encontrado']);
    }
  }

  // ========================================================================
  // ASISTENCIAS RELOJ CHECADOR (desde offcanvas de empleado)
  // ========================================================================

  /**
   * Resumen rápido para badge del offcanvas (últimos 30 días)
   */
  public function asistencias_reloj_resumen()
  {
    $this->output->set_content_type('application/json', 'utf-8');

    if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
      $this->output->set_output(json_encode(['success' => false, 'message' => 'Sin permiso']));
      return;
    }

    $empleado_id = (int)$this->input->post('empleado_id');
    if (!$empleado_id) {
      $this->output->set_output(json_encode(['success' => false, 'message' => 'Empleado requerido']));
      return;
    }

    $this->load->model('Reloj/RelojModel');

    $hoy = date('Y-m-d');
    $inicio_mes = date('Y-m-01');
    $inicio_30 = date('Y-m-d', strtotime('-30 days'));

    $checadas_mes = $this->RelojModel->get_asistencias_rango($inicio_mes, $hoy, $empleado_id);
    $dias_con_checada = [];
    $ultima = null;

    foreach ($checadas_mes as $c) {
      $dias_con_checada[date('Y-m-d', strtotime($c->fecha_hora))] = true;
      if (!$ultima || strtotime($c->fecha_hora) > strtotime($ultima)) {
        $ultima = $c->fecha_hora;
      }
    }

    $this->output->set_output(json_encode([
      'success'           => true,
      'total_checadas_30' => $this->RelojModel->contar_checadas_empleado($empleado_id, $inicio_30, $hoy),
      'dias_trabajados_mes' => count($dias_con_checada),
      'ultima_checada'    => $ultima,
      'ultima_checada_fmt' => $ultima ? date('d/m/Y H:i', strtotime($ultima)) : null,
    ]));
  }

  /**
   * Detalle de asistencias por periodo: dia | semana | mes (AJAX modal)
   */
  public function asistencias_reloj_periodo()
  {
    $this->output->set_content_type('application/json', 'utf-8');

    if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
      $this->output->set_output(json_encode(['success' => false, 'message' => 'Sin permiso']));
      return;
    }

    $empleado_id = (int)$this->input->post('empleado_id');
    $modo = $this->input->post('modo') ?: 'semana';
    $fecha_ref = $this->input->post('fecha_ref') ?: date('Y-m-d');

    if (!$empleado_id) {
      $this->output->set_output(json_encode(['success' => false, 'message' => 'Empleado requerido']));
      return;
    }

    $this->load->model('Reloj/RelojModel');
    $this->load->model('RH/HorariosModel');

    $empleado = $this->EmpleadoModel->get_empleado_completo($empleado_id);
    if (!$empleado) {
      $this->output->set_output(json_encode(['success' => false, 'message' => 'Empleado no encontrado']));
      return;
    }

    switch ($modo) {
      case 'dia':
        $fecha_inicio = $fecha_ref;
        $fecha_fin = $fecha_ref;
        break;
      case 'mes':
        $fecha_inicio = date('Y-m-01', strtotime($fecha_ref));
        $fecha_fin = date('Y-m-t', strtotime($fecha_ref));
        break;
      case 'semana':
      default:
        $modo = 'semana';
        $ts = strtotime($fecha_ref);
        $dia_num = (int)date('N', $ts);
        $fecha_inicio = date('Y-m-d', strtotime('-' . ($dia_num - 1) . ' days', $ts));
        $fecha_fin = date('Y-m-d', strtotime('+' . (7 - $dia_num) . ' days', $ts));
        break;
    }

    $horarios = $this->HorariosModel->get_horario_empleado($empleado_id);
    $horarios_por_dia = [];
    $mapa_dia = [
      'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
      'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
    ];

    $cursor = strtotime($fecha_inicio);
    $fin = strtotime($fecha_fin);
    while ($cursor <= $fin) {
      $fecha = date('Y-m-d', $cursor);
      $dia_es = $mapa_dia[date('l', $cursor)] ?? '';
      foreach ($horarios as $h) {
        if ($h->dia_semana === $dia_es && (int)$h->es_dia_laboral === 1) {
          $horarios_por_dia[$fecha] = $h;
          break;
        }
      }
      $cursor = strtotime('+1 day', $cursor);
    }

    $dias = $this->RelojModel->get_resumen_asistencias_periodo(
      $empleado_id,
      $fecha_inicio,
      $fecha_fin,
      $horarios_por_dia
    );

    $total_checadas = 0;
    $dias_con_registro = 0;
    foreach ($dias as $dia) {
      $total_checadas += count($dia['checadas']);
      if (!empty($dia['checadas'])) {
        $dias_con_registro++;
      }
    }

    $this->output->set_output(json_encode([
      'success' => true,
      'modo' => $modo,
      'fecha_inicio' => $fecha_inicio,
      'fecha_fin' => $fecha_fin,
      'empleado' => [
        'id' => $empleado->id,
        'nombre' => trim($empleado->nombre . ' ' . $empleado->apellido_paterno),
        'numero_empleado' => $empleado->numero_empleado,
      ],
      'resumen' => [
        'total_checadas' => $total_checadas,
        'dias_con_registro' => $dias_con_registro,
        'dias_periodo' => count($dias),
      ],
      'dias' => $dias,
    ]));
  }

}