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
    // Asumiendo que el ID del módulo de RH es 8 o que validamos por nombre/slug
    // Pero usaremos la verificación estándar del sistema si existe
    if($this->init_controller->has_permission($this->session->userdata('id'), 'Consultar empleados')){
      $this->viewData['response']['datos_faltantes'] = $this->EmpleadoModel->get_empleados_datos_faltantes();
    }
    
    // Obtener solicitudes de vacaciones pendientes (Alerta)
    $this->load->model('RH/VacacionesModel');
    $this->viewData['response']['vacaciones_pendientes'] = count($this->VacacionesModel->get_todas_solicitudes('Pendiente'));
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

    foreach ($list as $empleado) {
      $no++;
      $row = array();
      
      // Número de empleado
      $row[] = $empleado->numero_empleado;
      
      // Nombre completo
      $row[] = $empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno;
      
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
        'empleados' => $empleados
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

    // Generar HTML de detalles
    $detail = '
      <tr class="table-light"><td colspan="2"><strong>Información Personal</strong></td></tr>
      <tr><td><strong>Nombre:</strong></td><td>'.$empleado->nombre.' '.$empleado->apellido_paterno.' '.$empleado->apellido_materno.'</td></tr>
      <tr><td><strong>Género:</strong></td><td>'.$empleado->genero.'</td></tr>
      <tr><td><strong>Estado Civil:</strong></td><td>'.($empleado->estado_civil ?? 'No especificado').'</td></tr>
      <tr><td><strong>Teléfono:</strong></td><td>'.($empleado->telefono ?? 'N/A').'</td></tr>
      <tr><td><strong>Email Personal:</strong></td><td>'.($empleado->email_personal ?? 'N/A').'</td></tr>
      
      <tr class="table-light"><td colspan="2"><strong>Información Fiscal</strong></td></tr>
      <tr><td><strong>Número Empleado:</strong></td><td>'.$empleado->numero_empleado.'</td></tr>
      <tr><td><strong>RFC:</strong></td><td>'.$empleado->rfc.'</td></tr>
      <tr><td><strong>CURP:</strong></td><td>'.$empleado->curp.'</td></tr>
      <tr><td><strong>NSS:</strong></td><td>'.($empleado->nss ?? 'N/A').'</td></tr>
      
      <tr class="table-light"><td colspan="2"><strong>Información Laboral</strong></td></tr>
      <tr><td><strong>Puesto:</strong></td><td>'.$empleado->puesto.'</td></tr>
      <tr><td><strong>Departamento:</strong></td><td>'.($empleado->departamento_nombre ?? 'N/A').'</td></tr>
      <tr><td><strong>Tipo:</strong></td><td>'.$empleado->tipo_trabajador.'</td></tr>
      <tr><td><strong>Email Corp:</strong></td><td>'.($empleado->email_corporativo ?? 'N/A').'</td></tr>
      <tr><td><strong>Salario Mensual:</strong></td><td>$'.number_format($empleado->salario_base_mensual, 2).'</td></tr>
      <tr><td><strong>Fecha Ingreso:</strong></td><td>'.$empleado->fecha_ingreso.'</td></tr>
      <tr><td><strong>Estatus:</strong></td><td>'.($empleado->estatus == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>').'</td></tr>
    ';

    // Acciones
    $actions = '
      <a href="'.base_url('rh/RecursosHumanos/editar/'.$id).'" class="btn btn-warning btn-sm w-100 mb-2">
        <i data-lucide="edit"></i> Editar
      </a>
      <a href="'.base_url('rh/RecursosHumanos/nuevo_contrato/'.$id).'" class="btn btn-success btn-sm w-100 mb-2">
        <i data-lucide="file-text"></i> Nuevo Contrato
      </a>';
    
    if($empleado->estatus == 1){
      $actions .= '
        <button class="btn btn-info btn-sm w-100 mb-2" onclick="abrirCalculadoraBaja('.$id.')">
          <i class="fas fa-calculator"></i> Calcular Finiquito
        </button>
        <button class="btn btn-danger btn-sm w-100" onclick="delete_empleado('.$id.')">
          <i data-lucide="trash-2"></i> Dar de Baja
        </button>';
    }

    echo json_encode([
      'response' => $empleado,
      'detail' => $detail,
      'actions' => $actions
    ]);
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
      'archivo_evidencia' => $archivo_evidencia,
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
      'contenido' => $this->input->post('contenido') // TinyMCE content
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
    
    // Preparar un array básico para los placeholders
    $contenido_procesado = $this->ContratoModel->procesar_plantilla($plantilla->contenido, $empleado, []);
    
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
    $contenido_personalizado = $this->input->post('contenido'); // Del editor
    
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

}