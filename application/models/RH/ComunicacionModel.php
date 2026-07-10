<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mensajería y tareas internas entre empleados (usuarios vinculados).
 */
class ComunicacionModel extends CI_Model {

    const ESTATUS_TAREA = ['Pendiente', 'En proceso', 'Hecha', 'Cancelada'];

    public function tablas_listas() {
        return $this->db->table_exists('rh_mensajes') && $this->db->table_exists('rh_tareas');
    }

    public function get_resumen($empleado_id) {
        $empleado_id = (int)$empleado_id;
        if (!$empleado_id || !$this->tablas_listas()) {
            return [
                'mensajes_no_leidos' => 0,
                'tareas_pendientes' => 0,
                'tareas_en_proceso' => 0,
            ];
        }

        $this->db->where('para_empleado_id', $empleado_id);
        $this->db->where('leido', 0);
        $no_leidos = (int)$this->db->count_all_results('rh_mensajes');

        $this->db->where('para_empleado_id', $empleado_id);
        $this->db->where('estatus', 'Pendiente');
        $pendientes = (int)$this->db->count_all_results('rh_tareas');

        $this->db->where('para_empleado_id', $empleado_id);
        $this->db->where('estatus', 'En proceso');
        $en_proceso = (int)$this->db->count_all_results('rh_tareas');

        return [
            'mensajes_no_leidos' => $no_leidos,
            'tareas_pendientes' => $pendientes,
            'tareas_en_proceso' => $en_proceso,
        ];
    }

    /**
     * Datos compactos para el icono de la barra superior.
     */
    public function get_navbar_data($empleado_id) {
        $empleado_id = (int)$empleado_id;
        $resumen = $this->get_resumen($empleado_id);
        $total_pendiente = (int)$resumen['mensajes_no_leidos']
            + (int)$resumen['tareas_pendientes']
            + (int)$resumen['tareas_en_proceso'];

        return [
            'resumen' => $resumen,
            'total_pendiente' => $total_pendiente,
            'mensajes' => $this->get_mensajes_no_leidos_recientes($empleado_id, 5),
            'tareas' => $this->get_tareas_abiertas_recientes($empleado_id, 5),
        ];
    }

    public function get_mensajes_no_leidos_recientes($empleado_id, $limite = 5) {
        if (!$this->tablas_listas()) {
            return [];
        }
        $empleado_id = (int)$empleado_id;
        $nombre_de = $this->_nombre_empleado_sql('de');

        $this->db->select("m.id, m.mensaje, m.fecha_envio, TRIM({$nombre_de}) AS de_nombre", false);
        $this->db->from('rh_mensajes m');
        $this->db->join('empleados de', 'de.id = m.de_empleado_id', 'left');
        $this->db->where('m.para_empleado_id', $empleado_id);
        $this->db->where('m.leido', 0);
        $this->db->order_by('m.fecha_envio', 'DESC');
        $this->db->limit((int)$limite);
        return $this->db->get()->result();
    }

    public function get_tareas_abiertas_recientes($empleado_id, $limite = 5) {
        if (!$this->tablas_listas()) {
            return [];
        }
        $empleado_id = (int)$empleado_id;
        $nombre_de = $this->_nombre_empleado_sql('de');

        $this->db->select("t.id, t.titulo, t.estatus, t.fecha_limite, TRIM({$nombre_de}) AS de_nombre", false);
        $this->db->from('rh_tareas t');
        $this->db->join('empleados de', 'de.id = t.de_empleado_id', 'left');
        $this->db->where('t.para_empleado_id', $empleado_id);
        $this->db->where_in('t.estatus', ['Pendiente', 'En proceso']);
        $this->db->order_by("FIELD(t.estatus, 'Pendiente', 'En proceso')", '', false);
        $this->db->order_by('t.fecha_creacion', 'DESC');
        $this->db->limit((int)$limite);
        return $this->db->get()->result();
    }

    /**
     * Contactos: empleados activos excepto el actual (prioriza jefe y equipo).
     */
    public function get_contactos($empleado_id, $limite = 100) {
        $empleado_id = (int)$empleado_id;
        $yo = $this->db->get_where('empleados', ['id' => $empleado_id])->row();
        if (!$yo) {
            return [];
        }

        $jefe_id = (int)($yo->jefe_directo_id ?? 0);
        $depto_id = (int)($yo->departamento_id ?? 0);

        $this->db->select(
            'e.id, e.numero_empleado, e.nombre, e.apellido_paterno, e.apellido_materno, e.puesto, ' .
            'd.nombre AS departamento_nombre, ' .
            'CASE WHEN e.id = ' . $jefe_id . ' THEN 1 ELSE 0 END AS es_mi_jefe, ' .
            'CASE WHEN e.jefe_directo_id = ' . $empleado_id . ' THEN 1 ELSE 0 END AS es_mi_equipo, ' .
            'CASE WHEN e.departamento_id = ' . $depto_id . ' AND ' . $depto_id . ' > 0 THEN 1 ELSE 0 END AS mismo_depto',
            false
        );
        $this->db->from('empleados e');
        $this->db->join('departamentos d', 'd.id = e.departamento_id', 'left');
        $this->db->where_in('e.estatus', [1, 2]);
        $this->db->where('e.id !=', $empleado_id);
        $this->db->order_by('es_mi_jefe', 'DESC');
        $this->db->order_by('es_mi_equipo', 'DESC');
        $this->db->order_by('mismo_depto', 'DESC');
        $this->db->order_by('e.nombre', 'ASC');
        $this->db->limit($limite);

        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->nombre_completo = trim($r->nombre . ' ' . $r->apellido_paterno . ' ' . ($r->apellido_materno ?? ''));
        }
        return $rows;
    }

    public function puede_interactuar($de_empleado_id, $para_empleado_id) {
        $de_empleado_id = (int)$de_empleado_id;
        $para_empleado_id = (int)$para_empleado_id;
        if (!$de_empleado_id || !$para_empleado_id || $de_empleado_id === $para_empleado_id) {
            return false;
        }
        $contactos = $this->get_contactos($de_empleado_id, 500);
        foreach ($contactos as $c) {
            if ((int)$c->id === $para_empleado_id) {
                return true;
            }
        }
        return false;
    }

    private function _nombre_empleado_sql($alias = 'e') {
        return "CONCAT({$alias}.nombre, ' ', {$alias}.apellido_paterno, ' ', IFNULL({$alias}.apellido_materno, ''))";
    }

    public function get_mensajes($empleado_id, $bandeja = 'recibidos', $limite = 50) {
        if (!$this->tablas_listas()) {
            return [];
        }
        $empleado_id = (int)$empleado_id;
        $nombre_de = $this->_nombre_empleado_sql('de');
        $nombre_para = $this->_nombre_empleado_sql('para');

        $this->db->select("m.*, TRIM({$nombre_de}) AS de_nombre, TRIM({$nombre_para}) AS para_nombre, de.puesto AS de_puesto, para.puesto AS para_puesto", false);
        $this->db->from('rh_mensajes m');
        $this->db->join('empleados de', 'de.id = m.de_empleado_id', 'left');
        $this->db->join('empleados para', 'para.id = m.para_empleado_id', 'left');

        if ($bandeja === 'enviados') {
            $this->db->where('m.de_empleado_id', $empleado_id);
        } else {
            $this->db->where('m.para_empleado_id', $empleado_id);
        }

        $this->db->order_by('m.fecha_envio', 'DESC');
        $this->db->limit($limite);
        return $this->db->get()->result();
    }

    public function enviar_mensaje($de_empleado_id, $para_empleado_id, $mensaje, $de_usuario_id = null) {
        if (!$this->tablas_listas()) {
            return ['success' => false, 'message' => 'El módulo de comunicación no está instalado. Ejecuta database/rh_comunicacion_interna.sql'];
        }
        $mensaje = trim($mensaje);
        if ($mensaje === '') {
            return ['success' => false, 'message' => 'El mensaje no puede estar vacío.'];
        }
        if (mb_strlen($mensaje) > 2000) {
            return ['success' => false, 'message' => 'El mensaje no puede exceder 2000 caracteres.'];
        }
        if (!(int)$para_empleado_id) {
            return ['success' => false, 'message' => 'Selecciona un destinatario.'];
        }
        if (!$this->puede_interactuar($de_empleado_id, $para_empleado_id)) {
            return ['success' => false, 'message' => 'No puedes enviar mensajes a ese empleado.'];
        }

        $ok = $this->db->insert('rh_mensajes', [
            'de_empleado_id' => (int)$de_empleado_id,
            'para_empleado_id' => (int)$para_empleado_id,
            'de_usuario_id' => $de_usuario_id ? (int)$de_usuario_id : null,
            'mensaje' => $mensaje,
            'leido' => 0,
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? ['success' => true, 'message' => 'Mensaje enviado.', 'id' => $this->db->insert_id()]
            : ['success' => false, 'message' => 'No se pudo enviar el mensaje.'];
    }

    public function marcar_mensaje_leido($mensaje_id, $empleado_id) {
        if (!$this->tablas_listas()) {
            return false;
        }
        $this->db->where('id', (int)$mensaje_id);
        $this->db->where('para_empleado_id', (int)$empleado_id);
        return $this->db->update('rh_mensajes', ['leido' => 1]);
    }

    public function get_tareas($empleado_id, $vista = 'asignadas', $limite = 50) {
        if (!$this->tablas_listas()) {
            return [];
        }
        $empleado_id = (int)$empleado_id;
        $nombre_de = $this->_nombre_empleado_sql('de');
        $nombre_para = $this->_nombre_empleado_sql('para');

        $this->db->select("t.*, TRIM({$nombre_de}) AS de_nombre, TRIM({$nombre_para}) AS para_nombre", false);
        $this->db->from('rh_tareas t');
        $this->db->join('empleados de', 'de.id = t.de_empleado_id', 'left');
        $this->db->join('empleados para', 'para.id = t.para_empleado_id', 'left');

        if ($vista === 'enviadas') {
            $this->db->where('t.de_empleado_id', $empleado_id);
        } else {
            $this->db->where('t.para_empleado_id', $empleado_id);
        }

        $this->db->order_by("FIELD(t.estatus, 'Pendiente', 'En proceso', 'Hecha', 'Cancelada')", '', false);
        $this->db->order_by('t.fecha_creacion', 'DESC');
        $this->db->limit($limite);
        return $this->db->get()->result();
    }

    public function crear_tarea($de_empleado_id, $para_empleado_id, $titulo, $descripcion = '', $fecha_limite = null, $de_usuario_id = null) {
        if (!$this->tablas_listas()) {
            return ['success' => false, 'message' => 'El módulo de comunicación no está instalado.'];
        }
        $titulo = trim($titulo);
        if ($titulo === '') {
            return ['success' => false, 'message' => 'El título de la tarea es obligatorio.'];
        }
        if (mb_strlen($titulo) > 200) {
            return ['success' => false, 'message' => 'El título no puede exceder 200 caracteres.'];
        }
        if (!(int)$para_empleado_id) {
            return ['success' => false, 'message' => 'Selecciona a quién asignar la tarea.'];
        }
        if ($fecha_limite === '' || $fecha_limite === '0000-00-00') {
            $fecha_limite = null;
        }
        if (!$this->puede_interactuar($de_empleado_id, $para_empleado_id)) {
            return ['success' => false, 'message' => 'No puedes asignar tareas a ese empleado.'];
        }

        $data = [
            'de_empleado_id' => (int)$de_empleado_id,
            'para_empleado_id' => (int)$para_empleado_id,
            'de_usuario_id' => $de_usuario_id ? (int)$de_usuario_id : null,
            'titulo' => $titulo,
            'descripcion' => trim($descripcion) ?: null,
            'estatus' => 'Pendiente',
            'fecha_limite' => $fecha_limite ?: null,
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ];

        $ok = $this->db->insert('rh_tareas', $data);
        return $ok
            ? ['success' => true, 'message' => 'Tarea asignada.', 'id' => $this->db->insert_id()]
            : ['success' => false, 'message' => 'No se pudo crear la tarea.'];
    }

    public function actualizar_estatus_tarea($tarea_id, $empleado_id, $estatus) {
        if (!$this->tablas_listas()) {
            return ['success' => false, 'message' => 'Módulo no instalado.'];
        }
        if (!in_array($estatus, self::ESTATUS_TAREA, true)) {
            return ['success' => false, 'message' => 'Estatus inválido.'];
        }

        $tarea = $this->db->get_where('rh_tareas', ['id' => (int)$tarea_id])->row();
        if (!$tarea) {
            return ['success' => false, 'message' => 'Tarea no encontrada.'];
        }

        $empleado_id = (int)$empleado_id;
        $puede = ((int)$tarea->para_empleado_id === $empleado_id) || ((int)$tarea->de_empleado_id === $empleado_id);
        if (!$puede) {
            return ['success' => false, 'message' => 'Sin permiso para actualizar esta tarea.'];
        }

        $this->db->where('id', (int)$tarea_id);
        $ok = $this->db->update('rh_tareas', [
            'estatus' => $estatus,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? ['success' => true, 'message' => 'Estatus actualizado.']
            : ['success' => false, 'message' => 'No se pudo actualizar.'];
    }
}
