<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Libreria general para administrar el control de sesiones y privilegios asi como herramientas generales
 */

class Init_controller{
  ///definimos las variables globales
  protected $ci;

  public function __construct(){
    $this->ci =& get_instance(); ///Instanciamos nuestro objeto
  }


  /**
   * Verificamos si la sesion existe y redireccionamos a la ruta general
   */
  public function check_session(){
    $id = $this->ci->session->id;
    $role = $this->ci->session->role;
    if(!$id){
        redirect('admin');
    }
    if($role!=1){
      redirect('admin');
    }
  }

  public function session_exist(){
    $id = $this->ci->session->id;
    $role = $this->ci->session->role;
    if($id AND($role==1)){
      redirect('dashboard');
    }
  }

  public function get_priviledges($module){
    $this->ci->load->model('User_model');
    $data = $this->ci->User_model->mod_get_priviledges($this->ci->session->id);

    if($data[$module] != 1){
			redirect(base_url().'deny');
      //echo "sin permiso";
		}
  }

  public function get_priviledges_view($module){
    $result=1;
    $this->ci->load->model('User_model');
    $data = $this->ci->User_model->mod_get_priviledges($this->ci->session->id);

    if($data[$module] != 1){
			$result=0;
		}
    return
    $result;
  }

  ///verificamos si tenemmos los privilegios de una cuenta de super administrador
  public function supersu_priviledges(){
    
  }

  /**Genera una fecha en formato date a un texto legible
   * Date: fecha en especifico, de lo contratio utiliza la fecha actual   
   */
  public function date_to_string($date=false){
    if($date==false){
      $date= date("Y-m-d");//fecha actual
    }
    
    $fecha = substr($date, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    //days arrays
    $dias_ES =  array('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo');
    $dias_EN = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);

    //months arrays
    $meses_ES = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
    $meses_EN = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    
    //return string
    return $nombredia." ".$numeroDia." de ".$nombreMes." de ".$anio;
  }

  /**Genera una fecha en formato date a un texto legible
   * Date: fecha en especifico, de lo contratio utiliza la fecha actual   
   */
  public function date_to_short_string($date=false){
    if($date==false){
      $date= date("Y-m-d");//fecha actual
    }
    
    $fecha = substr($date, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));


    //months arrays
    $meses_ES = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
    $meses_EN = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    
    //return string
    return $numeroDia." de ".$nombreMes." de ".$anio;
  }

  public function mes_to_string($date=false){
    if($date==false){
      $date= date("Y-m-d");//fecha actual
    }
    
    $fecha = substr($date, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    //months arrays
    $meses_ES = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
    $meses_EN = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    
    //return string
    return $nombreMes;

  }

  //////general
  public function alert($class=false,$mensaje=false){
    if($class==false){
      $class="danger";
    }
    if($mensaje==false){
      $mensaje = "<strong>Error</strong>";
    }

    $string = '<div class="alert alert-'.$class.' alert-dismissible" role="alert">
  							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    							<div class="alert-message">'.$mensaje.'</div>
							 </div>';
               
    return $string;
  }

  public function insert_log($mensaje,$usuario,$tipo){
    $this->ci->load->model("Control/ControlModel");
    $result = $this->ci->ControlModel->mod_insert_log($mensaje,$usuario,$tipo);
    return $result;
  }


  ///////////////////////secundarias
  public function fecha_contrato($con_numero_contrato){
    //obtenemos la fecha inicial del contrato
    $this->ci->load->model("General_model");
    $sql = "SELECT con_fecha_contrato FROM contrato WHERE con_numero_contrato=".$con_numero_contrato;
    $result = $this->ci->General_model->inquery($sql);
    $row = $result->row();
    $fecha_inicial = $row->con_fecha_contrato;
    return  $fecha_inicial;
  }
  

  //funcion get_anios crea un array con los años desde un año inicial hasta el año actual mas uno
  public function get_anios($anio_inicial="1984"){
    $anio_actual = date("Y");
    $anios = array();
    for($i=$anio_inicial;$i<=$anio_actual;$i++){
      $anios[] = $i;
    }
    $anios[] = $i++;
    return $anios;
  }

  
  //funcion get_meses crea un array con los meses de todo el año
  public function get_meses($mes_inicial="1"){    
    $meses = array();
    for($i=$mes_inicial;$i<=12;$i++){
      
    
      $meses[] = $i;
           
    } 
    
    return $meses;
  }

  //metodo que cambia una fecha de formato YYY-MM-DD a DD-MM-YYYY
  public function cambiar_formato_fecha($fecha=false){
    if($fecha!==false){
       //cambia $fecha a formato DD-MM-YYYY
      $fecha = substr($fecha, 8, 2)."-".substr($fecha, 5, 2)."-".substr($fecha, 0, 4);
      return $fecha;
    }else{
      return false;
    }       
  }  
  


}//end class