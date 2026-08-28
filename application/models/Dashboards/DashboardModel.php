<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DashboardModel
 * -----------------------------------------------------------------------------
 * Support model for the main dashboard. Its only responsibility is to resolve,
 * in a single query, the full set of permissions granted to a user so the
 * controller can decide which widgets to build/fetch WITHOUT running one query
 * per permission check (as tiene_permiso() would).
 *
 * Metric data itself is still queried through the existing per-module models
 * (VentasModel, ProduccionModel, etc.) — this model does not duplicate them.
 */
class DashboardModel extends CI_Model {

    /**
     * Returns the granted permissions for a user as an associative set
     * ['permiso' => true, ...] for O(1) membership checks.
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_permissions($user_id) {
        $perms = array();
        if (!$user_id) {
            return $perms;
        }

        $this->db->select('permiso');
        $this->db->where('admin', $user_id);
        $this->db->where('valor', 1);
        $rows = $this->db->get('privilege')->result();

        foreach ($rows as $row) {
            $perms[$row->permiso] = true;
        }
        return $perms;
    }
}
