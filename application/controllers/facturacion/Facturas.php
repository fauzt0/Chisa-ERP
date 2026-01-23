<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facturas extends MY_Controller {
    
    protected $modulo = 'Contabilidad';
    
    public function __construct() {
        parent::__construct();
        //$this->load->model('Facturacion/FacturasModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }

    public function index(){
      //preparamos la conexión al api del sistema de facturación "app.micontador.mx "
      
      
    }


}