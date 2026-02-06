<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FacturaApiTokenModel extends CI_Model {

    protected $table = 'api_tokens';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener el token activo (sistema o usuario)
     */
    public function get_token($provider = 'facture_app', $user_id = null) {
        $this->db->where('provider', $provider);
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        } else {
            // Si no hay user_id, asumimos que es el token global del sistema o el último modificado
            $this->db->order_by('updated_at', 'DESC');
        }
        $query = $this->db->get($this->table);
        return $query->row(); // Retorna objeto con access_token, refresh_token, etc.
    }

    /**
     * Guardar o Actualizar token
     */
    public function save_token($data) {
        // Verificar si ya existe un token para este proveedor/usuario
        $existing = $this->get_token($data['provider'], isset($data['user_id']) ? $data['user_id'] : null);

        if ($existing) {
            $this->db->where('id', $existing->id);
            return $this->db->update($this->table, $data);
        } else {
            return $this->db->insert($this->table, $data);
        }
    }

    /**
     * Eliminar token (desconectar)
     */
    public function delete_token($provider = 'facture_app', $user_id = null) {
        $this->db->where('provider', $provider);
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        return $this->db->delete($this->table);
    }
}
