<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends MY_Controller {

    protected $modulo = 'Administradores';

    public function __construct() {
        parent::__construct();
        $this->load->model('Users/RolesModel');
        $this->config->load('permissions');
    }

    public function index() {
        $data['pageTitle'] = 'Roles y Permisos';
        $data['headTitle'] = 'Gestión de Roles';
        $data['breadcrumb'] = 'Inicio > Gestión de usuarios > Roles';
        
        $data['roles'] = $this->RolesModel->get_all_roles();
        $data['pageView'] = 'usuarios/roles/main';
        
        $this->load->view('layouts/general_template', $data);
    }

    public function crear() {
        $data['pageTitle'] = 'Nuevo Rol';
        $data['headTitle'] = 'Nuevo Rol';
        $data['breadcrumb'] = 'Inicio > Gestión de usuarios > Roles > Nuevo';
        
        $data['permissions'] = $this->config->item('permissions');
        $data['is_edit'] = false;
        $data['custom_scripts'] = ['usuarios/roles.js'];  // Assuming I might need a JS file, or inline
        
        $data['pageView'] = 'usuarios/roles/form';
        $this->load->view('layouts/general_template', $data);
    }

    public function editar($id) {
        $role = $this->RolesModel->get_role($id);
        if (!$role) {
            show_404();
        }

        $data['pageTitle'] = 'Editar Rol';
        $data['headTitle'] = 'Editar Rol';
        $data['breadcrumb'] = 'Inicio > Gestión de usuarios > Roles > Editar';
        
        $data['role'] = $role;
        $data['selected_permissions'] = json_decode($role->permisos, true) ?? [];
        $data['permissions'] = $this->config->item('permissions');
        $data['is_edit'] = true;
        
        $data['pageView'] = 'usuarios/roles/form';
        $this->load->view('layouts/general_template', $data);
    }

    public function guardar() {
        $id = $this->input->post('id');
        $nombre = $this->input->post('nombre');
        $descripcion = $this->input->post('descripcion');
        
        // Collect permissions
        $all_permissions = $this->config->item('permissions');
        $selected_permissions = [];
        
        foreach ($all_permissions as $module => $perms) {
            foreach ($perms as $key => $label) {
                if ($this->input->post($key) == 1) {
                    $selected_permissions[$key] = 1;
                }
            }
        }

        $data = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'permisos' => json_encode($selected_permissions),
            'estatus' => 1
        ];

        if ($id) {
            $this->RolesModel->update_role($id, $data);
            $msg = 'Rol actualizado correctamente';
        } else {
            $this->RolesModel->create_role($data);
            $msg = 'Rol creado correctamente';
        }

        $this->session->set_flashdata('success', $msg); // Assuming flashdata usage or redirect
        redirect('usuarios/Roles');
    }

    public function eliminar($id) {
        $this->RolesModel->delete_role($id);
        echo json_encode(['success' => true, 'message' => 'Rol eliminado']);
    }

    // AJAX for User Form
    public function get_role_permissions_ajax() {
        $id = $this->input->post('id');
        $role = $this->RolesModel->get_role($id);
        
        if ($role) {
            echo json_encode([
                'success' => true, 
                'permissions' => json_decode($role->permisos, true)
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
