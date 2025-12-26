<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permissions Helper
 * Funciones auxiliares para verificar permisos de usuario
 */

if (!function_exists('tiene_permiso')) {
    /**
     * Verifica si el usuario actual tiene un permiso específico
     * 
     * @param string $permiso Nombre del permiso a verificar
     * @return bool True si tiene el permiso, False si no
     */
    function tiene_permiso($permiso) {
        $CI =& get_instance();
        
        // Si no hay sesión, no tiene permisos
        if(!$CI->session->userdata('id')) {
            return false;
        }
        
        $user_id = $CI->session->userdata('id');
        
        // Cargar modelo de usuarios si no está cargado
        if(!isset($CI->UserModel)) {
            $CI->load->model('Users/UserModel');
        }
        
        // Obtener permisos del usuario desde la tabla privilege
        // Estructura: admin (user_id), permiso (nombre del permiso), valor (1/0)
        $CI->db->where('admin', $user_id);
        $CI->db->where('permiso', $permiso);
        $CI->db->where('valor', 1);
        $query = $CI->db->get('privilege');
        
        return $query->num_rows() > 0;
    }
}

if (!function_exists('puede_ver_costos')) {
    /**
     * Verifica si el usuario puede ver costos y precios en producción
     * 
     * @return bool True si puede ver costos, False si no
     */
    function puede_ver_costos() {
        return tiene_permiso('produccion_ver_costos');
    }
}

if (!function_exists('ocultar_costo')) {
    /**
     * Retorna el costo formateado si tiene permiso, o texto oculto si no
     * 
     * @param float $costo El costo a mostrar
     * @param int $decimales Número de decimales (default 2)
     * @return string Costo formateado o texto oculto
     */
    function ocultar_costo($costo, $decimales = 2) {
        if(puede_ver_costos()) {
            return '$' . number_format($costo, $decimales);
        }
        return '<span class="text-muted"><i class="fas fa-lock"></i> Restringido</span>';
    }
}

if (!function_exists('ocultar_precio')) {
    /**
     * Retorna el precio formateado si tiene permiso, o texto oculto si no
     * 
     * @param float $precio El precio a mostrar
     * @param int $decimales Número de decimales (default 2)
     * @return string Precio formateado o texto oculto
     */
    function ocultar_precio($precio, $decimales = 2) {
        if(puede_ver_costos()) {
            return '$' . number_format($precio, $decimales);
        }
        return '<span class="text-muted"><i class="fas fa-lock"></i> Restringido</span>';
    }
}
