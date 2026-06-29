<?php
/**
 * UserModel - Modelo de usuarios del ERP
 * 
 * Gestiona usuarios administradores del sistema incluyendo:
 * - Autenticación y gestión de sesiones
 * - CRUD de usuarios
 * - Gestión de permisos
 * - Bitácora de actividades
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends MY_Model {
    
    protected $tableName = "administradores";
    
    /**
     * Configuración para DataTables
     */
    protected $datatableConfig = [
        'column_order' => ['id', 'nombre', 'apellidos', 'username', 'estatus', null],
        'column_search' => ['id', 'nombre', 'apellidos', 'username', 'estatus'],
        'order' => ['id' => 'asc']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    // ========================================================================
    // MÉTODOS DE AUTENTICACIÓN
    // ========================================================================
    
    /**
     * Verifica las credenciales de login
     * 
     * @param string $username Email del usuario
     * @param string $password Contraseña en texto plano
     * @return bool True si las credenciales son válidas
     */
    public function mod_resolve_login($username, $password) {
        $this->db->where('username', $username);
        $this->db->where('estatus', 1);
        $hash = $this->db->get($this->tableName)->row('password');
        return password_verify($password, $hash);
    }
    
    /**
     * Obtiene datos del usuario por username
     * 
     * @param string $username Email del usuario
     * @return object|null Datos del usuario
     */
    public function get_user_data_from_username($username) {
        $this->db->select("*");
        $this->db->from($this->tableName);
        $this->db->where('username', $username);
        return $this->db->get()->row();
    }
    
    /**
     * Obtiene datos del usuario por ID
     * Alias para compatibilidad con código existente
     * 
     * @param int $id ID del usuario
     * @return object|null Datos del usuario
     */
    public function mod_get_user_from_id($id) {
        return $this->get_by_id($id); // Usa método heredado de MY_Model
    }
    
    // ========================================================================
    // MÉTODOS DE GESTIÓN DE USUARIOS
    // ========================================================================
    
    /**
     * Muestra todos los usuarios
     * 
     * @param string $select Campos a seleccionar
     * @return CI_DB_result Query result
     */
    public function mod_show_all_users($select) {
        $this->db->select($select);
        $this->db->order_by('id', 'asc');
        $query = $this->db->get_where($this->tableName);
        return $query;
    }
    
    /**
     * Agrega un nuevo usuario con sus permisos
     * 
     * @param array $permissions Array de permisos del sistema
     * @return array Respuesta estandarizada
     */
    public function mod_add($permissions) {
        // Iniciar transacción
        $this->db->trans_start();
        
        // Preparar datos del usuario
        $data = [
            'nombre' => $this->input->post('nombre'),
            'apellidos' => $this->input->post('apellidos'),
            'username' => $this->input->post('username'),
            'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
            'departamento' => $this->input->post('departamento'),
            'empleado_id' => $this->input->post('empleado_id') ? $this->input->post('empleado_id') : NULL
        ];
        
        // Insertar usuario (automáticamente agrega estatus=1 y fecha_alta)
        $inserted_id = $this->insert($data);
        
        if (!$inserted_id) {
            $this->db->trans_rollback();
            return $this->error_response("Error al insertar el usuario");
        }
        
        // Preparar permisos en batch
        $privs_batch = [];
        foreach ($permissions as $section => $perms) {
            foreach ($perms as $perm_key => $perm_label) {
                $privs_batch[] = [
                    'admin' => $inserted_id,
                    'permiso' => $perm_key,
                    'valor' => ($this->input->post($perm_key) !== NULL ? 1 : 0)
                ];
            }
        }
        
        // Insertar permisos
        $success = $this->db->insert_batch("privilege", $privs_batch);
        
        // Completar transacción
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE || $success === FALSE) {
            return $this->error_response("Error al insertar permisos del administrador");
        }
        
        return $this->success_response("Se ha agregado al Administrador con el ID: " . $inserted_id, ['id' => $inserted_id]);
    }

    /**
     * Procesa la carga masiva de usuarios desde un array de datos (Excel)
     * 
     * @param array $rows Datos leídos del Excel
     * @param array $permissions Permisos por defecto
     * @return array Resumen de la operación
     */
    public function mod_bulk_insert_excel($rows, $permissions) {
        $stats = [
            'total' => count($rows),
            'inserted' => 0,
            'errors' => 0,
            'skipped' => 0,
            'messages' => []
        ];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // +2 porque Excel es 1-indexed y tiene cabecera

            // Validar datos mínimos
            if (empty($row['nombre']) || empty($row['username']) || empty($row['password'])) {
                $stats['errors']++;
                $stats['messages'][] = "Línea $line: Datos incompletos (Nombre, Email y Password requeridos)";
                continue;
            }

            // Verificar si el usuario ya existe
            $this->db->where('username', $row['username']);
            if ($this->db->count_all_results($this->tableName) > 0) {
                $stats['skipped']++;
                $stats['messages'][] = "Línea $line: El email '" . $row['username'] . "' ya está registrado";
                continue;
            }

            // Iniciar transacción por cada usuario para evitar que uno falle y detenga todo
            $this->db->trans_start();

            $data = [
                'nombre' => $row['nombre'],
                'apellidos' => $row['apellidos'] ?? '',
                'username' => $row['username'],
                'password' => password_hash($row['password'], PASSWORD_BCRYPT),
                'departamento' => $row['departamento'] ?? 'Sin Departamento',
                'estatus' => 1
            ];

            $inserted_id = $this->insert($data);

            if ($inserted_id) {
                // Asignar permisos por defecto o heredados
                $privs_batch = [];
                foreach ($permissions as $section => $perms) {
                    foreach ($perms as $perm_key => $perm_label) {
                        $privs_batch[] = [
                            'admin' => $inserted_id,
                            'permiso' => $perm_key,
                            'valor' => 0 // Por defecto sin permisos en carga masiva por seguridad
                        ];
                    }
                }
                $this->db->insert_batch("privilege", $privs_batch);
                $stats['inserted']++;
            } else {
                $stats['errors']++;
                $stats['messages'][] = "Línea $line: Error de base de datos al insertar";
            }

            $this->db->trans_complete();
        }

        return $stats;
    }
    
    /**
     * Actualiza un usuario y sus permisos
     * 
     * @param int $id ID del usuario
     * @param array $permissions Array de permisos del sistema
     * @return array Respuesta estandarizada
     */
    public function mod_update($id, $permissions) {
        // Verificar que el usuario existe
        if (!$this->exists($id)) {
            return $this->not_found_response("El usuario no existe");
        }
        
        // Iniciar transacción
        $this->db->trans_start();
        
        // Preparar datos de actualización
        $data = [
            'nombre' => $this->input->post('nombre'),
            'apellidos' => $this->input->post('apellidos'),
            'username' => $this->input->post('username'),
            'departamento' => $this->input->post('departamento'),
            'empleado_id' => $this->input->post('empleado_id') ? $this->input->post('empleado_id') : NULL,
            'estatus' => $this->input->post('estatus')
        ];
        
        // Agregar password si se proporcionó
        if ($this->input->post('password') != NULL) {
            $data['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT);
        }
        
        // Actualizar usuario (automáticamente agrega fecha_edicion)
        $this->update($id, $data);
        
        // Verificar errores de BD
        if ($this->has_db_error()) {
            $error = $this->get_db_error();
            $this->db->trans_rollback();
            return $this->error_response("Error al actualizar: " . $error['message']);
        }
        
        // Eliminar permisos anteriores
        $this->db->where('admin', $id);
        $this->db->delete('privilege');
        
        // Insertar nuevos permisos
        $privs_batch = [];
        foreach ($permissions as $section => $perms) {
            foreach ($perms as $perm_key => $perm_label) {
                $privs_batch[] = [
                    'admin' => $id,
                    'permiso' => $perm_key,
                    'valor' => ($this->input->post($perm_key) !== NULL ? 1 : 0)
                ];
            }
        }
        
        $success = $this->db->insert_batch("privilege", $privs_batch);
        
        // Completar transacción
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE || $success === FALSE) {
            return $this->error_response("Error al actualizar permisos");
        }
        
        return $this->success_response("Se ha actualizado al Administrador con el ID: " . $id);
    }

    /**
     * Actualiza datos básicos del perfil (Mi Perfil) sin tocar permisos
     */
    public function update_perfil($id, $data) {
        if (!$this->exists($id)) {
            return $this->not_found_response('Usuario no encontrado');
        }

        $update = [];
        if (isset($data['nombre'])) {
            $update['nombre'] = $data['nombre'];
        }
        if (isset($data['apellidos'])) {
            $update['apellidos'] = $data['apellidos'];
        }
        if (isset($data['username'])) {
            $this->db->where('username', $data['username']);
            $this->db->where('id !=', $id);
            if ($this->db->count_all_results($this->tableName) > 0) {
                return $this->error_response('El correo/usuario ya está registrado');
            }
            $update['username'] = $data['username'];
        }
        if (!empty($data['password'])) {
            $update['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($update)) {
            return $this->error_response('No hay datos para actualizar');
        }

        $this->update($id, $update);
        if ($this->has_db_error()) {
            return $this->error_response('Error al actualizar el perfil');
        }

        return $this->success_response('Perfil actualizado correctamente');
    }
    
    /**
     * Elimina un usuario mediante soft delete
     * 
     * @param int $id ID del usuario
     * @return array Respuesta estandarizada
     */
    public function mod_delete($id) {
        // Verificar que el usuario existe y está activo
        if (!$this->exists_active($id)) {
            return $this->not_found_response("No existe el usuario o ya está inactivo");
        }
        
        // Iniciar transacción
        $this->db->trans_start();
        
        // Soft delete del usuario (automáticamente pone estatus=0 y fecha_baja)
        $this->soft_delete($id);
        
        if ($this->db->affected_rows() == 0) {
            $this->db->trans_rollback();
            return $this->error_response("Error, no se dio de baja al usuario correctamente");
        }
        
        // Eliminar todos los permisos del usuario
        $this->db->where('admin', $id);
        $this->db->delete('privilege');
        
        if ($this->db->affected_rows() == 0) {
            $this->db->trans_rollback();
            return $this->error_response("Error, no se eliminaron los permisos del usuario correctamente");
        }
        
        // Completar transacción
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return $this->error_response("Error en la transacción de baja");
        }
        
        return $this->success_response("Usuario dado de baja correctamente");
    }
    
    /**
     * Restaura un usuario eliminado
     * 
     * @param int $id ID del usuario
     * @return array Respuesta estandarizada
     */
    public function mod_restore($id) {
        // Verificar que el usuario existe
        $usuario = $this->get_by_id($id);
        
        if (empty($usuario)) {
            return $this->not_found_response("No existe el usuario");
        }
        
        if ($usuario->estatus == 1) {
            return $this->error_response("El usuario ya está activo");
        }
        
        // Restaurar usuario (automáticamente pone estatus=1 y limpia fecha_baja)
        $success = $this->restore($id);
        
        if ($success && $this->db->affected_rows() > 0) {
            $this->init_controller->insert_log(
                "Se restauró administrador con ID: " . $id,
                $this->session->email,
                "Registro Editado"
            );
            return $this->success_response("Usuario restaurado correctamente");
        }
        
        return $this->error_response("Error, no se restauró al administrador correctamente");
    }
    
    // ========================================================================
    // MÉTODOS DE PERMISOS Y DATOS COMPLETOS
    // ========================================================================
    
    /**
     * Obtiene los datos del usuario y sus permisos
     * 
     * @param int $id ID del usuario
     * @return array Datos del usuario y sus permisos
     */
    public function getUserData($id) {
        $user = $this->db->get_where($this->tableName, ['id' => $id]);
        $privilege = $this->db->get_where('privilege', ['admin' => $id]);
        
        $data['user_data'] = $user->row();
        $data['user_permissions'] = $privilege;
        return $data;
    }
    
    // ========================================================================
    // MÉTODOS DE BITÁCORA
    // ========================================================================
    
    /**
     * Obtiene los últimos logs de un usuario
     * 
     * @param string $email Email del usuario
     * @param int $quant Cantidad de logs a obtener
     * @return CI_DB_result Query result
     */
    public function last_logs($email, $quant = 5) {
        $this->db->select('mensaje, tipo, fecha');
        $this->db->from('bitacora');
        $this->db->where('usuario', $email);
        $this->db->limit($quant);
        $this->db->order_by('id', 'DESC');
        $result = $this->db->get();
        return $result;
    }
    
    // ========================================================================
    // MÉTODOS PARA DATATABLES
    // ========================================================================
    
    /**
     * Busca usuarios para DataTables
     * Usa el método heredado de MY_Model
     * 
     * @return array Array de resultados
     */
    public function _mod_search_users() {
        return $this->get_datatables(); // Usa método heredado
    }
    
    /**
     * Cuenta registros filtrados para DataTables
     * Usa el método heredado de MY_Model
     * 
     * @return int Número de registros filtrados
     */
    public function count_filtered_users() {
        return $this->count_filtered(); // Usa método heredado
    }
    
    /**
     * Cuenta todos los registros de una tabla
     * Mantiene compatibilidad con código legacy
     * 
     * @param string $table Nombre de la tabla
     * @return int Número de registros
     */
    public function count_all($table = null) {
        if ($table === null) {
            $table = $this->tableName;
        }
        return $this->db->count_all($table);
    }
    
    // ========================================================================
    // MÉTODOS DE ESTADÍSTICAS CRÍTICAS
    // ========================================================================
    
    /**
     * Obtiene estadísticas críticas del sistema de usuarios
     * 
     * @return array Array con estadísticas del sistema
     */
    public function get_critical_stats() {
        $stats = [];
        
        // Total de usuarios
        $stats['total_users'] = $this->db->count_all_results($this->tableName);
        
        // Usuarios activos
        $stats['active_users'] = $this->db->where('estatus', 1)
                                          ->count_all_results($this->tableName);
        
        // Usuarios inactivos/suspendidos
        $stats['inactive_users'] = $this->db->where('estatus', 0)
                                            ->count_all_results($this->tableName);
        
        // Usuarios creados en los últimos 30 días
        $fecha_limite = date('Y-m-d', strtotime('-30 days'));
        $stats['new_users_30days'] = $this->db->where('fecha_alta >=', $fecha_limite)
                                               ->count_all_results($this->tableName);
        
        // Usuarios dados de baja en los últimos 30 días
        $stats['deleted_users_30days'] = $this->db->where('fecha_baja >=', $fecha_limite)
                                                   ->where('estatus', 0)
                                                   ->count_all_results($this->tableName);
        
        // Actividad reciente (registros en bitácora últimos 7 días)
        $fecha_actividad = date('Y-m-d', strtotime('-7 days'));
        $stats['recent_activity'] = $this->db->where('fecha >=', $fecha_actividad)
                                             ->count_all_results('bitacora');
        
        // Porcentaje de usuarios activos
        $stats['active_percentage'] = $stats['total_users'] > 0 
            ? round(($stats['active_users'] / $stats['total_users']) * 100, 1) 
            : 0;
        
        // Porcentaje de crecimiento (últimos 30 días)
        $stats['growth_percentage'] = $stats['total_users'] > 0 
            ? round(($stats['new_users_30days'] / $stats['total_users']) * 100, 1) 
            : 0;
        
        return $stats;
    }
    
    /**
     * Obtiene el último usuario registrado
     * 
     * @return object|null Datos del último usuario
     */
    public function get_last_registered_user() {
        return $this->db->order_by('fecha_alta', 'DESC')
                        ->limit(1)
                        ->get($this->tableName)
                        ->row();
    }
    
    /**
     * Obtiene usuarios por departamento
     * 
     * @return array Array con conteo por departamento
     */
    public function get_users_by_department() {
        return $this->db->select('departamento, COUNT(*) as total')
                        ->where('estatus', 1)
                        ->group_by('departamento')
                        ->order_by('total', 'DESC')
                        ->get($this->tableName)
                        ->result();
    }
    /**
     * Actualiza el código 2FA para un usuario
     * 
     * @param string $username Email del usuario
     * @param string $code Código de 6 dígitos
     * @param string $ip Dirección IP actual
     * @return bool True si se actualizó correctamente
     */
    public function mod_update_2fa($username, $code, $ip) {
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $data = [
            'two_factor_code' => $code,
            'two_factor_expires' => $expires,
            'last_ip' => $ip
        ];
        $this->db->where('username', $username);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Verifica un código 2FA
     * 
     * @param string $username Email del usuario
     * @param string $code Código ingresado
     * @return bool True si es válido y no ha expirado
     */
    public function mod_verify_2fa($username, $code) {
        $this->db->where('username', $username);
        $this->db->where('two_factor_code', $code);
        $this->db->where('two_factor_expires >=', date('Y-m-d H:i:s'));
        $query = $this->db->get($this->tableName);
        
        if ($query->num_rows() > 0) {
            // Limpiar el código después de usarlo por seguridad
            $this->db->where('username', $username);
            $this->db->update($this->tableName, ['two_factor_code' => NULL]);
            return true;
        }
        return false;
    }
}
