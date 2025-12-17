<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class PrivilegeModel extends CI_model{
  private $tableName = "privilege";

  function __construct(){
    parent::__construct();
  }

  
  public function mod_get_priviledges($id){
    $data = $this->db->get_where($tableName, array('admin' =>$id ));
    return $data->row_array();
  }



}