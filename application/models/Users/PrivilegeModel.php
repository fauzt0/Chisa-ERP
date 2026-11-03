<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class PrivilegeModel extends CI_model{
  private $tableName = "privilege";

  function __construct(){
    parent::__construct();
  }

  
  public function mod_get_priviledges($id){
    $data = $this->db->get_where($this->tableName, array('admin' =>$id ));
    return $data->row_array();
  }

    /**
     * Inserta claves nuevas en privilege (valor 0 o 1) para poder activarlas
     * o desactivarlas desde Roles / usuarios. No pisa valores ya guardados.
     */
    public function asegurar_claves_i6() {
        if (!$this->db->table_exists('privilege')) {
            return;
        }
        $admins = $this->db->select('id')->get('administradores')->result();
        $defs = [
            'ventas_cotizaciones_obra' => 'ventas_cotizaciones',
            'ventas_pie_cotizacion' => 'ventas_cotizaciones',
            'entrenamiento_materiales' => 'super',
            'entrenamiento_productos' => 'super',
            'entrenamiento_sembrar' => 'super',
            'contabilidad_expediente_raya' => 'contabilidad_nomina',
        ];
        foreach ($admins as $admin) {
            $id = (int) $admin->id;
            $super = $this->es_super($id);
            foreach ($defs as $clave => $origen) {
                $ya = $this->db->where('admin', $id)->where('permiso', $clave)->count_all_results('privilege');
                if ($ya) {
                    continue;
                }
                $valor = 0;
                if ($origen === 'super') {
                    $valor = $super ? 1 : 0;
                } else {
                    $src = $this->db->where('admin', $id)->where('permiso', $origen)->where('valor', 1)->count_all_results('privilege');
                    $valor = $src ? 1 : ($super ? 1 : 0);
                }
                $this->db->insert('privilege', [
                    'admin' => $id,
                    'permiso' => $clave,
                    'valor' => $valor,
                ]);
            }
        }
        $this->fusionar_rol_super($defs);
    }

    private function es_super($admin_id) {
        $n = $this->db->where('admin', (int) $admin_id)
            ->where_in('permiso', ['user_add', 'user_delete', 'user_bitacora'])
            ->where('valor', 1)
            ->count_all_results('privilege');
        return $n >= 3;
    }

    private function fusionar_rol_super(array $defs) {
        if (!$this->db->table_exists('roles')) {
            return;
        }
        $rol = $this->db->where('nombre', 'Super Administrador')->get('roles')->row();
        if (!$rol) {
            return;
        }
        $perms = json_decode($rol->permisos, true);
        if (!is_array($perms)) {
            return;
        }
        $changed = false;
        foreach (array_keys($defs) as $clave) {
            if (empty($perms[$clave])) {
                $perms[$clave] = 1;
                $changed = true;
            }
        }
        if ($changed) {
            $this->db->where('id', (int) $rol->id)->update('roles', ['permisos' => json_encode($perms)]);
        }
    }
}