<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AlertasSimuladasModel extends CI_Model {

    protected $table = 'alertas_simuladas';

    /**
     * Catálogo de los 8 tipos de alerta actuales en Notifications.php
     */
    public function get_tipos_catalogo() {
        return [
            'stock_bajo' => [
                'modulo'    => 'Almacén',
                'titulo'    => 'Stock bajo',
                'mensaje'   => 'Pintura Vinílica Blanca tiene solo 3 litros',
                'url'       => 'almacen/Inventario',
                'severidad' => 'warning',
                'icono'     => 'exclamation-triangle',
                'tiempo'    => 'Ahora',
            ],
            'orden_retrasada' => [
                'modulo'    => 'Ventas',
                'titulo'    => 'Orden retrasada',
                'mensaje'   => 'Orden OV-2026-DEMO con 5 días de retraso',
                'url'       => 'ventas/Ordenes',
                'severidad' => 'danger',
                'icono'     => 'exclamation-circle',
                'tiempo'    => '5d',
            ],
            'obra_retrasada' => [
                'modulo'    => 'Obras',
                'titulo'    => 'Obra retrasada',
                'mensaje'   => 'Obra Residencial Demo está retrasada',
                'url'       => 'obras/Obras',
                'severidad' => 'warning',
                'icono'     => 'clock',
                'tiempo'    => 'Hoy',
            ],
            'datos_incompletos' => [
                'modulo'    => 'RH',
                'titulo'    => 'Datos incompletos',
                'mensaje'   => 'Juan Pérez García - Falta: NSS, RFC',
                'url'       => 'rh/RecursosHumanos',
                'severidad' => 'info',
                'icono'     => 'user-circle',
                'tiempo'    => 'Hoy',
            ],
            'oc_pendientes' => [
                'modulo'    => 'Compras',
                'titulo'    => 'Órdenes de compra pendientes',
                'mensaje'   => '3 órdenes pendientes de recibir',
                'url'       => 'compras/OrdenesCompra',
                'severidad' => 'info',
                'icono'     => 'shopping-cart',
                'tiempo'    => 'Hoy',
            ],
            'productos_sin_formulacion' => [
                'modulo'    => 'Producción',
                'titulo'    => 'Productos sin formulación',
                'mensaje'   => '4 productos necesitan formulación',
                'url'       => 'produccion/Productos',
                'severidad' => 'warning',
                'icono'     => 'box',
                'tiempo'    => 'Hoy',
            ],
            'preordenes_pendientes' => [
                'modulo'    => 'Compras',
                'titulo'    => 'Pre-órdenes pendientes de autorización',
                'mensaje'   => '2 pre-orden(es) generada(s) desde Producción esperan autorización',
                'url'       => 'compras/OrdenesCompra',
                'severidad' => 'danger',
                'icono'     => 'clipboard-check',
                'tiempo'    => 'Ahora',
            ],
            'solicitudes_produccion' => [
                'modulo'    => 'Producción',
                'titulo'    => 'Solicitudes de producción pendientes',
                'mensaje'   => '3 solicitud(es) requieren atención (última: SP-2026-DEMO)',
                'url'       => 'produccion/Dashboard',
                'severidad' => 'danger',
                'icono'     => 'industry',
                'tiempo'    => 'Ahora',
            ],
        ];
    }

    public function get_tipo($tipo) {
        $catalogo = $this->get_tipos_catalogo();
        return isset($catalogo[$tipo]) ? $catalogo[$tipo] : null;
    }

    public function listar() {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        $this->db->from($this->table);
        $this->db->order_by('creado_en', 'DESC');
        return $this->db->get()->result();
    }

    public function crear($tipo, $user_id) {
        $def = $this->get_tipo($tipo);
        if (!$def) {
            return false;
        }

        $data = [
            'tipo'      => $tipo,
            'modulo'    => $def['modulo'],
            'titulo'    => $def['titulo'],
            'mensaje'   => $def['mensaje'],
            'url'       => $def['url'],
            'severidad' => $def['severidad'],
            'icono'     => $def['icono'],
            'tiempo'    => $def['tiempo'],
            'creado_por'=> (int) $user_id,
            'creado_en' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $data);
        if ($this->db->affected_rows() <= 0) {
            return false;
        }

        $data['id'] = $this->db->insert_id();
        return $data;
    }

    public function eliminar_todas() {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        $count = $this->db->count_all($this->table);
        $this->db->truncate($this->table);
        return $count;
    }

    /**
     * Convierte filas de BD al formato JSON de Notifications.php
     */
    public function get_para_notificaciones() {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        $rows = $this->listar();
        $out = [];

        foreach ($rows as $row) {
            $out[] = $this->row_to_notification($row);
        }

        return $out;
    }

    public function row_to_notification($row) {
        return [
            'type'    => $row->severidad,
            'icon'    => $row->icono,
            'module'  => $row->modulo,
            'title'   => $row->titulo,
            'message' => $row->mensaje,
            'link'    => base_url($row->url),
            'time'    => $row->tiempo,
            'simulada'=> true,
        ];
    }

    public function registro_to_notification($registro) {
        $row = (object) $registro;
        return $this->row_to_notification($row);
    }
}
