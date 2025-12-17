<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ControlModel extends CI_model{


  function __construct(){
        parent::__construct();
  }

  ///insert log data
  public function mod_insert_log($mensaje,$usuario,$tipo){
    $this->load->helper('date');

    $fields =  array(
      'mensaje' => $mensaje,
      'usuario' => $usuario,
      'tipo'    => $tipo,
      'ip'      => $this->input->ip_address(),
      'fecha'   => date("Y-m-d"),
      'hora'    => date("H:i:s")
    );

    return $this->db->insert('bitacora', $fields ); // Customer successfully inserted if results true
  }



}
