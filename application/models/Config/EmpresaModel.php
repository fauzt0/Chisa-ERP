<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpresaModel extends CI_Model {

    protected $table = 'configuracion_empresa';
    protected $id = 1;

    public function get_config() {
        $row = $this->db->where('id', $this->id)->get($this->table)->row();
        if (!$row) {
            return (object) [
                'id' => 1,
                'razon_social' => 'Chisa Recubrimientos',
                'nombre_comercial' => 'CHISA',
                'rfc' => '',
                'regimen_fiscal' => '',
                'calle' => '',
                'numero_exterior' => '',
                'numero_interior' => '',
                'colonia' => '',
                'ciudad' => '',
                'estado' => '',
                'codigo_postal' => '',
                'telefono' => '',
                'email' => '',
                'sitio_web' => '',
                'logo' => 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg',
            ];
        }
        $cambios = [];
        foreach (['razon_social', 'nombre_comercial', 'calle', 'colonia', 'ciudad', 'estado'] as $campo) {
            if (!isset($row->$campo)) {
                continue;
            }
            $limpio = texto_sin_mojibake($row->$campo);
            if ($limpio !== $row->$campo) {
                $row->$campo = $limpio;
                $cambios[$campo] = $limpio;
            }
        }
        if ($cambios) {
            $this->db->where('id', $this->id)->update($this->table, $cambios);
        }
        return $row;
    }

    public function guardar_config($data, $user_id = null) {
        $data['fecha_actualizacion'] = date('Y-m-d H:i:s');
        $data['actualizado_por'] = $user_id;

        $exists = $this->db->where('id', $this->id)->count_all_results($this->table);
        if ($exists) {
            $this->db->where('id', $this->id);
            return $this->db->update($this->table, $data);
        }
        $data['id'] = $this->id;
        return $this->db->insert($this->table, $data);
    }
}
