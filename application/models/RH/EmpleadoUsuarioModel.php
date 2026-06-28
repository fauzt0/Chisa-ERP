<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vinculación bidireccional empleado ↔ usuario ERP (administradores.empleado_id).
 */
class EmpleadoUsuarioModel extends CI_Model {

    public function tiene_vinculo_habilitado() {
        static $ok = null;
        if ($ok === null) {
            $ok = $this->db->field_exists('empleado_id', 'administradores');
        }
        return $ok;
    }

    public function get_usuario_por_empleado($empleado_id) {
        if (!$this->tiene_vinculo_habilitado()) {
            return null;
        }
        return $this->db
            ->select('id, nombre, apellidos, username, departamento, estatus')
            ->from('administradores')
            ->where('empleado_id', (int)$empleado_id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function get_empleado_por_usuario($usuario_id) {
        if (!$this->tiene_vinculo_habilitado()) {
            return null;
        }
        $admin = $this->db
            ->select('empleado_id')
            ->from('administradores')
            ->where('id', (int)$usuario_id)
            ->get()
            ->row();
        if (!$admin || empty($admin->empleado_id)) {
            return null;
        }
        return $this->db->get_where('empleados', ['id' => (int)$admin->empleado_id])->row();
    }

    /**
     * Usuarios activos disponibles para vincular (sin empleado o ya vinculados al empleado actual).
     */
    public function buscar_usuarios($termino = '', $empleado_id = null, $limite = 25) {
        if (!$this->tiene_vinculo_habilitado()) {
            return [];
        }

        $this->db->select('id, nombre, apellidos, username, departamento, empleado_id, estatus');
        $this->db->from('administradores');
        $this->db->where('estatus', 1);
        $this->db->group_start();
        $this->db->where('empleado_id IS NULL', null, false);
        if ($empleado_id) {
            $this->db->or_where('empleado_id', (int)$empleado_id);
        }
        $this->db->group_end();

        if ($termino !== '') {
            $this->db->group_start();
            $this->db->like('nombre', $termino);
            $this->db->or_like('apellidos', $termino);
            $this->db->or_like('username', $termino);
            $this->db->or_like('id', $termino);
            $this->db->group_end();
        }

        $this->db->order_by('nombre', 'ASC');
        $this->db->limit($limite);
        return $this->db->get()->result();
    }

    public function usuarios_sin_empleado($limite = 50) {
        if (!$this->tiene_vinculo_habilitado()) {
            return [];
        }
        return $this->db
            ->select('id, nombre, apellidos, username, departamento')
            ->from('administradores')
            ->where('estatus', 1)
            ->where('empleado_id IS NULL', null, false)
            ->order_by('nombre', 'ASC')
            ->limit($limite)
            ->get()
            ->result();
    }

    public function vincular($empleado_id, $usuario_id) {
        if (!$this->tiene_vinculo_habilitado()) {
            return ['success' => false, 'message' => 'Ejecute la migración database/empleado_usuario_vinculo.sql'];
        }

        $empleado_id = (int)$empleado_id;
        $usuario_id = (int)$usuario_id;

        if (!$this->db->get_where('empleados', ['id' => $empleado_id])->row()) {
            return ['success' => false, 'message' => 'Empleado no encontrado'];
        }

        $usuario = $this->db->get_where('administradores', ['id' => $usuario_id, 'estatus' => 1])->row();
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado o inactivo'];
        }

        if (!empty($usuario->empleado_id) && (int)$usuario->empleado_id !== $empleado_id) {
            return ['success' => false, 'message' => 'Ese usuario ya está vinculado a otro empleado'];
        }

        $this->db->trans_start();

        // Un empleado solo puede tener un usuario ERP
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('id !=', $usuario_id);
        $this->db->update('administradores', ['empleado_id' => null]);

        $this->db->where('id', $usuario_id);
        $this->db->update('administradores', ['empleado_id' => $empleado_id]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Error al vincular usuario'];
        }

        return [
            'success' => true,
            'message' => 'Usuario vinculado correctamente',
            'usuario' => $this->get_usuario_por_empleado($empleado_id),
        ];
    }

    public function desvincular($empleado_id) {
        if (!$this->tiene_vinculo_habilitado()) {
            return ['success' => false, 'message' => 'Vinculación no disponible'];
        }

        $this->db->where('empleado_id', (int)$empleado_id);
        $this->db->update('administradores', ['empleado_id' => null]);

        return ['success' => true, 'message' => 'Usuario desvinculado'];
    }

    /**
     * Crea expediente de empleado a partir de un usuario ERP y los vincula.
     */
    public function crear_empleado_desde_usuario($usuario_id) {
        if (!$this->tiene_vinculo_habilitado()) {
            return ['success' => false, 'message' => 'Ejecute la migración database/empleado_usuario_vinculo.sql'];
        }

        $usuario_id = (int)$usuario_id;
        $usuario = $this->db->get_where('administradores', ['id' => $usuario_id, 'estatus' => 1])->row();
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        if (!empty($usuario->empleado_id)) {
            return [
                'success' => false,
                'message' => 'El usuario ya tiene empleado vinculado',
                'empleado_id' => (int)$usuario->empleado_id,
            ];
        }

        $partes = preg_split('/\s+/', trim($usuario->apellidos ?? ''), 2);
        $paterno = $partes[0] ?: 'SinApellido';
        $materno = $partes[1] ?? null;

        $placeholder = str_pad((string)$usuario_id, 6, '0', STR_PAD_LEFT);
        $data = [
            'nombre'               => $usuario->nombre ?: 'SinNombre',
            'apellido_paterno'     => $paterno,
            'apellido_materno'     => $materno,
            'fecha_nacimiento'     => '1990-01-01',
            'genero'               => 'Otro',
            'email_corporativo'    => $usuario->username,
            'rfc'                  => 'PEND' . $placeholder,
            'curp'                 => 'PEND' . $placeholder . 'HDFXXX00',
            'tipo_trabajador'      => 'Confianza',
            'puesto'               => $usuario->departamento ?: 'Por definir',
            'departamento_id'      => null,
            'fecha_ingreso'        => date('Y-m-d'),
            'salario_base_mensual' => 0.01,
            'tipo_nomina'          => 'Quincenal',
            'forma_pago'           => 'Transferencia',
        ];

        $this->load->model('RH/EmpleadoModel');
        $result = $this->EmpleadoModel->mod_add($data);
        if (empty($result['success'])) {
            return ['success' => false, 'message' => $result['msg'] ?? 'Error al crear empleado'];
        }

        $empleado_id = (int)($result['data']['id'] ?? 0);
        if ($empleado_id <= 0) {
            return ['success' => false, 'message' => 'No se obtuvo ID del empleado creado'];
        }

        $link = $this->vincular($empleado_id, $usuario_id);
        if (empty($link['success'])) {
            return $link;
        }

        return [
            'success'         => true,
            'message'         => 'Empleado creado y vinculado. Complete RFC, CURP y datos laborales en edición.',
            'empleado_id'     => $empleado_id,
            'numero_empleado' => $result['data']['numero_empleado'] ?? null,
        ];
    }
}
