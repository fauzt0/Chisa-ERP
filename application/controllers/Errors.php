<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Errors Controller
 * 
 * Handles custom error pages and access denial.
 */
class Errors extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // $modulo is remains null so permission check is skipped in MY_Controller
    }

    /**
     * Custom Access Denied page integrated with the ERP layout
     */
    public function deny() {
        $this->viewData['pageTitle'] = 'Acceso Denegado';
        $this->viewData['headTitle'] = 'Falta de Privilegios';
        $this->viewData['breadcrumb'] = 'ERP > Acceso Denegado';
        $this->viewData['pageView'] = 'errors/deny';
        
        // Render views using the general template
        $this->load->view('layouts/general_template', $this->viewData);
    }
}
