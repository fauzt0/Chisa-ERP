<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seguimiento compartido de cotización comercial, obra y venta:
 * fases, notas, expediente con carpetas y asignación de personal.
 */
class SeguimientoModel extends CI_Model {

    public function asegurar() {
        if (!$this->db->table_exists('seguimiento_fases')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS seguimiento_fases (
                id INT(11) NOT NULL AUTO_INCREMENT,
                origen_tipo ENUM('cotizacion','obra','venta') NOT NULL,
                origen_id INT(11) NOT NULL,
                orden INT(11) NOT NULL DEFAULT 0,
                nombre VARCHAR(160) NOT NULL,
                porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
                estatus ENUM('Pendiente','En proceso','Terminada') NOT NULL DEFAULT 'Pendiente',
                fecha_compromiso DATE NULL,
                notas VARCHAR(500) NULL,
                PRIMARY KEY (id),
                KEY idx_seg_fase (origen_tipo, origen_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('seguimiento_eventos')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS seguimiento_eventos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                origen_tipo ENUM('cotizacion','obra','venta') NOT NULL,
                origen_id INT(11) NOT NULL,
                tipo VARCHAR(40) NOT NULL,
                mensaje VARCHAR(500) NOT NULL,
                usuario_id INT(11) NULL,
                fecha DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_seg_evt (origen_tipo, origen_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('expediente_carpetas')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS expediente_carpetas (
                id INT(11) NOT NULL AUTO_INCREMENT,
                origen_tipo ENUM('cotizacion','obra','venta') NOT NULL,
                origen_id INT(11) NOT NULL,
                parent_id INT(11) NULL,
                nombre VARCHAR(120) NOT NULL,
                PRIMARY KEY (id),
                KEY idx_exp_carp (origen_tipo, origen_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('expediente_archivos')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS expediente_archivos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                origen_tipo ENUM('cotizacion','obra','venta') NOT NULL,
                origen_id INT(11) NOT NULL,
                carpeta_id INT(11) NULL,
                nombre VARCHAR(255) NOT NULL,
                ruta VARCHAR(500) NOT NULL,
                extension VARCHAR(12) NULL,
                tamano INT(11) NOT NULL DEFAULT 0,
                es_enlace TINYINT(1) NOT NULL DEFAULT 0,
                subido_por INT(11) NULL,
                fecha DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_exp_arch (origen_tipo, origen_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('obra_asignaciones')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS obra_asignaciones (
                id INT(11) NOT NULL AUTO_INCREMENT,
                obra_id INT(11) NOT NULL,
                empleado_id INT(11) NOT NULL,
                rol VARCHAR(80) NULL,
                fecha DATE NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                KEY idx_obra_asig (obra_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    public function paquete($origen_tipo, $origen_id) {
        $this->asegurar();
        $origen_tipo = $this->tipo($origen_tipo);
        $origen_id = (int) $origen_id;
        return [
            'origen_tipo' => $origen_tipo,
            'origen_id' => $origen_id,
            'fases' => $this->fases($origen_tipo, $origen_id),
            'eventos' => $this->eventos($origen_tipo, $origen_id),
            'carpetas' => $this->carpetas($origen_tipo, $origen_id),
            'archivos' => $this->archivos($origen_tipo, $origen_id),
            'adiciones' => $origen_tipo === 'cotizacion' ? $this->adiciones($origen_id) : [],
            'asignaciones' => $origen_tipo === 'obra' ? $this->asignaciones($origen_id) : [],
            'empleados' => $origen_tipo === 'obra' ? $this->empleados() : [],
            'avance' => $this->avance($origen_tipo, $origen_id),
        ];
    }

    public function fases($tipo, $id) {
        $this->asegurar();
        $this->db->where('origen_tipo', $this->tipo($tipo));
        $this->db->where('origen_id', (int) $id);
        $this->db->order_by('orden', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('seguimiento_fases')->result();
    }

    public function guardar_fase($data) {
        $this->asegurar();
        $tipo = $this->tipo($data['origen_tipo'] ?? '');
        $id = (int) ($data['origen_id'] ?? 0);
        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($id <= 0 || $nombre === '') {
            return ['success' => false, 'message' => 'Indica el nombre de la fase'];
        }
        $fila = [
            'origen_tipo' => $tipo,
            'origen_id' => $id,
            'nombre' => mb_substr($nombre, 0, 160),
            'porcentaje' => max(0, min(100, (float) ($data['porcentaje'] ?? 0))),
            'estatus' => $this->estatus_fase($data['estatus'] ?? ''),
            'fecha_compromiso' => $this->fecha($data['fecha_compromiso'] ?? ''),
            'notas' => $this->texto($data['notas'] ?? '', 500),
            'orden' => (int) ($data['orden'] ?? 0),
        ];
        $fase_id = (int) ($data['id'] ?? 0);
        if ($fase_id > 0) {
            $this->db->where('id', $fase_id)->where('origen_id', $id)->update('seguimiento_fases', $fila);
        } else {
            $this->db->insert('seguimiento_fases', $fila);
            $fase_id = (int) $this->db->insert_id();
        }
        $this->evento($tipo, $id, 'fase', 'Fase «' . $fila['nombre'] . '» al ' . $fila['porcentaje'] . '%', $data['usuario_id'] ?? null);
        return ['success' => true, 'message' => 'Fase guardada', 'id' => $fase_id];
    }

    public function eliminar_fase($id, $origen_id) {
        $this->asegurar();
        $this->db->where('id', (int) $id)->where('origen_id', (int) $origen_id)->delete('seguimiento_fases');
        return ['success' => true, 'message' => 'Fase eliminada'];
    }

    public function sincronizar_fases_desde_grupos($cotizacion_id, array $grupos) {
        $this->asegurar();
        $cotizacion_id = (int) $cotizacion_id;
        $orden = 1;
        foreach ($grupos as $nombre) {
            $nombre = trim((string) $nombre);
            if ($nombre === '') {
                continue;
            }
            $ya = $this->db->where('origen_tipo', 'cotizacion')->where('origen_id', $cotizacion_id)
                ->where('nombre', mb_substr($nombre, 0, 160))->count_all_results('seguimiento_fases');
            if ($ya > 0) {
                continue;
            }
            $this->db->insert('seguimiento_fases', [
                'origen_tipo' => 'cotizacion',
                'origen_id' => $cotizacion_id,
                'orden' => $orden++,
                'nombre' => mb_substr($nombre, 0, 160),
                'porcentaje' => 0,
                'estatus' => 'Pendiente',
            ]);
        }
    }

    public function nota($tipo, $id, $mensaje, $usuario_id) {
        $mensaje = trim((string) $mensaje);
        if ($mensaje === '') {
            return ['success' => false, 'message' => 'Escribe la nota'];
        }
        $this->evento($this->tipo($tipo), (int) $id, 'nota', $mensaje, $usuario_id);
        return ['success' => true, 'message' => 'Nota agregada al seguimiento'];
    }

    public function eventos($tipo, $id) {
        $this->asegurar();
        $this->db->where('origen_tipo', $this->tipo($tipo));
        $this->db->where('origen_id', (int) $id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(30);
        return $this->db->get('seguimiento_eventos')->result();
    }

    public function evento($tipo, $id, $clase, $mensaje, $usuario_id) {
        $this->asegurar();
        $this->db->insert('seguimiento_eventos', [
            'origen_tipo' => $this->tipo($tipo),
            'origen_id' => (int) $id,
            'tipo' => mb_substr((string) $clase, 0, 40),
            'mensaje' => mb_substr(trim((string) $mensaje), 0, 500),
            'usuario_id' => (int) $usuario_id ?: null,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
    }

    public function carpetas($tipo, $id) {
        $this->asegurar();
        $this->db->where('origen_tipo', $this->tipo($tipo));
        $this->db->where('origen_id', (int) $id);
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get('expediente_carpetas')->result();
    }

    public function crear_carpeta($tipo, $id, $nombre, $parent_id = null) {
        $this->asegurar();
        $nombre = $this->nombre_carpeta($nombre);
        if ($nombre === '') {
            return ['success' => false, 'message' => 'Escribe el nombre de la carpeta'];
        }
        $parent_id = (int) $parent_id ?: null;
        if ($parent_id && !$this->carpeta_valida($parent_id, $tipo, $id)) {
            return ['success' => false, 'message' => 'La carpeta padre no pertenece a este expediente'];
        }
        $this->db->insert('expediente_carpetas', [
            'origen_tipo' => $this->tipo($tipo),
            'origen_id' => (int) $id,
            'parent_id' => $parent_id,
            'nombre' => $nombre,
        ]);
        return ['success' => true, 'message' => 'Carpeta creada', 'id' => (int) $this->db->insert_id()];
    }

    public function archivos($tipo, $id) {
        $this->asegurar();
        $this->db->where('origen_tipo', $this->tipo($tipo));
        $this->db->where('origen_id', (int) $id);
        $this->db->order_by('fecha', 'DESC');
        return $this->db->get('expediente_archivos')->result();
    }

    public function guardar_archivo($tipo, $id, $carpeta_id, array $upload, $usuario_id, $es_enlace = 0) {
        $this->asegurar();
        $carpeta_id = (int) $carpeta_id ?: null;
        if ($carpeta_id && !$this->carpeta_valida($carpeta_id, $tipo, $id)) {
            return ['success' => false, 'message' => 'Carpeta inválida'];
        }
        $this->db->insert('expediente_archivos', [
            'origen_tipo' => $this->tipo($tipo),
            'origen_id' => (int) $id,
            'carpeta_id' => $carpeta_id,
            'nombre' => mb_substr($upload['nombre'], 0, 255),
            'ruta' => $upload['ruta'],
            'extension' => mb_substr($upload['extension'] ?? '', 0, 12),
            'tamano' => (int) ($upload['tamano'] ?? 0),
            'es_enlace' => $es_enlace ? 1 : 0,
            'subido_por' => (int) $usuario_id ?: null,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        $this->evento($tipo, $id, 'documento', 'Documento «' . $upload['nombre'] . '»', $usuario_id);
        return ['success' => true, 'message' => 'Documento agregado al expediente', 'id' => (int) $this->db->insert_id()];
    }

    public function get_archivo($id) {
        $this->asegurar();
        return $this->db->where('id', (int) $id)->get('expediente_archivos')->row();
    }

    public function eliminar_archivo($id) {
        $row = $this->get_archivo($id);
        if (!$row) {
            return ['success' => false, 'message' => 'Documento no encontrado'];
        }
        if (!(int) $row->es_enlace) {
            $abs = FCPATH . ltrim($row->ruta, '/');
            if (is_file($abs)) {
                unlink($abs);
            }
        }
        $this->db->where('id', (int) $id)->delete('expediente_archivos');
        return ['success' => true, 'message' => 'Documento retirado del expediente'];
    }

    public function asignaciones($obra_id) {
        $this->asegurar();
        $this->db->select('a.*, empleados.nombre, empleados.apellido_paterno, empleados.numero_empleado');
        $this->db->from('obra_asignaciones a');
        $this->db->join('empleados', 'empleados.id = a.empleado_id', 'left');
        $this->db->where('a.obra_id', (int) $obra_id);
        $this->db->where('a.activo', 1);
        return $this->db->get()->result();
    }

    public function asignar($obra_id, $empleado_id, $rol, $usuario_id) {
        $this->asegurar();
        $obra_id = (int) $obra_id;
        $empleado_id = (int) $empleado_id;
        if ($obra_id <= 0 || $empleado_id <= 0) {
            return ['success' => false, 'message' => 'Selecciona a la persona'];
        }
        $ya = $this->db->where('obra_id', $obra_id)->where('empleado_id', $empleado_id)->where('activo', 1)
            ->count_all_results('obra_asignaciones');
        if ($ya > 0) {
            return ['success' => false, 'message' => 'Esa persona ya está asignada'];
        }
        $this->db->insert('obra_asignaciones', [
            'obra_id' => $obra_id,
            'empleado_id' => $empleado_id,
            'rol' => $this->texto($rol, 80) ?: 'Cuadrilla',
            'fecha' => date('Y-m-d'),
            'activo' => 1,
        ]);
        $emp = $this->db->select('nombre, apellido_paterno')->where('id', $empleado_id)->get('empleados')->row();
        $nombre = $emp ? trim($emp->nombre . ' ' . $emp->apellido_paterno) : 'Empleado';
        $this->evento('obra', $obra_id, 'asignacion', 'Se asignó a ' . $nombre . ' (' . ($rol ?: 'Cuadrilla') . ')', $usuario_id);
        return ['success' => true, 'message' => $nombre . ' quedó asignado a la obra'];
    }

    public function quitar_asignacion($id, $obra_id) {
        $this->asegurar();
        $this->db->where('id', (int) $id)->where('obra_id', (int) $obra_id)->update('obra_asignaciones', ['activo' => 0]);
        return ['success' => true, 'message' => 'Asignación retirada'];
    }

    public function cambiar_estatus_obra($obra_id, $estatus, $usuario_id) {
        $validos = ['Planificación', 'En Cotización', 'Aprobada', 'En Ejecución', 'Pausada', 'Completada', 'Cancelada'];
        if (!in_array($estatus, $validos, true)) {
            return ['success' => false, 'message' => 'Estatus no válido'];
        }
        $obra = $this->db->where('id', (int) $obra_id)->get('obras')->row();
        if (!$obra) {
            return ['success' => false, 'message' => 'Obra no encontrada'];
        }
        $this->db->where('id', (int) $obra_id)->update('obras', [
            'estatus' => $estatus,
            'fecha_modificacion' => date('Y-m-d H:i:s'),
        ]);
        $this->evento('obra', $obra_id, 'estatus', 'Estatus de obra: ' . $estatus, $usuario_id);
        return ['success' => true, 'message' => 'La obra pasó a ' . $estatus];
    }

    public function adiciones($cotizacion_id) {
        $this->load->model('Ventas/CotizacionesObraModel');
        $this->CotizacionesObraModel->asegurar_infraestructura();
        $this->db->where('parent_id', (int) $cotizacion_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('cotizaciones_comerciales')->result();
    }

    public function crear_adicion($cotizacion_id, $usuario_id) {
        $this->load->model('Ventas/CotizacionesObraModel');
        $padre = $this->CotizacionesObraModel->get($cotizacion_id);
        if (!$padre) {
            return ['success' => false, 'message' => 'No está la cotización origen'];
        }
        $guardado = $this->CotizacionesObraModel->guardar([
            'cliente_id' => $padre->cliente_id,
            'obra_id' => $padre->obra_id,
            'sucursal_id' => $padre->sucursal_id,
            'constructora' => $padre->constructora,
            'atencion' => $padre->atencion,
            'obra_nombre' => $padre->obra_nombre,
            'ubicacion' => $padre->ubicacion,
            'introduccion' => 'Adición por requerimientos nuevos de ' . $padre->folio . '.',
            'observaciones' => 'Subcotización de ' . $padre->folio,
        ], [], $usuario_id);
        if (empty($guardado['success'])) {
            return $guardado;
        }
        $this->db->where('id', (int) $guardado['id'])->update('cotizaciones_comerciales', [
            'parent_id' => (int) $padre->id,
        ]);
        $this->evento('cotizacion', $padre->id, 'adicion', 'Se abrió la adición ' . $guardado['folio'], $usuario_id);
        return $guardado;
    }

    /**
     * Registra cada Excel de entrenamiento como cotización consultable, sin abrirlo.
     */
    public function registrar_consulta($directorio, $usuario_id) {
        $this->load->model('Ventas/CotizacionesObraModel');
        $this->CotizacionesObraModel->asegurar_infraestructura();
        $this->asegurar();
        if (!is_dir($directorio)) {
            return ['success' => false, 'message' => 'No está la carpeta de cotizaciones'];
        }
        $nuevas = 0;
        $ya = 0;
        foreach (scandir($directorio) as $nombre) {
            if (!preg_match('/\.xlsx$/i', $nombre)) {
                continue;
            }
            $existe = $this->db->where('pres_ref', $nombre)->count_all_results('cotizaciones_comerciales');
            if ($existe > 0) {
                $ya++;
                continue;
            }
            $titulo = trim(preg_replace('/^\d+(?:-[A-Z])?\s+/', '', preg_replace('/\.xlsx$/i', '', $nombre)));
            $titulo = trim(str_replace(['-', '_'], ' ', $titulo));
            if ($titulo === '') {
                $titulo = $nombre;
            }
            $folio = $this->CotizacionesObraModel->generar_folio();
            $this->db->insert('cotizaciones_comerciales', [
                'folio' => $folio,
                'constructora' => mb_substr($titulo, 0, 200),
                'obra_nombre' => mb_substr($titulo, 0, 200),
                'pres_ref' => mb_substr($nombre, 0, 180),
                'fecha' => date('Y-m-d'),
                'introduccion' => 'Archivo de entrenamiento disponible para consulta. Aún no se desglosaron sus partidas.',
                'estatus' => 'Borrador',
                'es_consulta' => 1,
                'observaciones' => 'Consulta de archivo',
                'creado_por' => (int) $usuario_id ?: null,
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
            $cot_id = (int) $this->db->insert_id();
            $this->guardar_archivo('cotizacion', $cot_id, null, [
                'nombre' => $nombre,
                'ruta' => 'doc/entrenamiento_5/COTIZACIONES 2026/' . $nombre,
                'extension' => 'xlsx',
                'tamano' => (int) @filesize($directorio . DIRECTORY_SEPARATOR . $nombre),
            ], $usuario_id, 1);
            $nuevas++;
        }
        return [
            'success' => true,
            'nuevas' => $nuevas,
            'ya' => $ya,
            'message' => 'Archivos listos para consulta: ' . $nuevas . ' nuevos, ' . $ya . ' ya estaban',
        ];
    }

    public function alertas($limite = 8) {
        $this->asegurar();
        $out = [];
        if ($this->db->table_exists('cotizaciones_comerciales') && $this->db->field_exists('fecha_estatus', 'cotizaciones_comerciales')) {
            $this->db->where('estatus', 'Enviada');
            $this->db->where('es_consulta', 0);
            $this->db->where('fecha_estatus <', date('Y-m-d H:i:s', strtotime('-7 days')));
            $this->db->order_by('fecha_estatus', 'ASC');
            $this->db->limit(4);
            foreach ($this->db->get('cotizaciones_comerciales')->result() as $c) {
                $out[] = [
                    'type' => 'warning',
                    'icon' => 'file-signature',
                    'module' => 'Ventas',
                    'title' => 'Cotización sin respuesta',
                    'message' => $c->folio . ' sigue Enviada desde ' . date('d/m/Y', strtotime($c->fecha_estatus)),
                    'link' => base_url('ventas/CotizacionesObra/editar/' . $c->id),
                    'time' => '7d+',
                ];
            }
        }
        if ($this->db->table_exists('seguimiento_fases')) {
            $this->db->where('fecha_compromiso <', date('Y-m-d'));
            $this->db->where('estatus !=', 'Terminada');
            $this->db->where('fecha_compromiso IS NOT NULL', null, false);
            $this->db->order_by('fecha_compromiso', 'ASC');
            $this->db->limit(4);
            foreach ($this->db->get('seguimiento_fases')->result() as $f) {
                $link = $f->origen_tipo === 'obra'
                    ? base_url('ventas/ObrasVentas/detalle/' . $f->origen_id)
                    : base_url('ventas/CotizacionesObra/editar/' . $f->origen_id);
                $out[] = [
                    'type' => 'danger',
                    'icon' => 'tasks',
                    'module' => 'Obras',
                    'title' => 'Fase vencida',
                    'message' => $f->nombre . ' debía cerrar el ' . date('d/m/Y', strtotime($f->fecha_compromiso)) . ' (' . $f->porcentaje . '%)',
                    'link' => $link,
                    'time' => 'Vencida',
                ];
            }
        }
        if ($this->db->table_exists('obras') && $this->db->table_exists('obra_asignaciones')) {
            $sql = "SELECT o.id, o.folio, o.nombre FROM obras o
                    WHERE o.estatus = 'En Ejecución' AND o.activo = 1
                    AND NOT EXISTS (SELECT 1 FROM obra_asignaciones a WHERE a.obra_id = o.id AND a.activo = 1)
                    LIMIT 4";
            foreach ($this->db->query($sql)->result() as $o) {
                $out[] = [
                    'type' => 'warning',
                    'icon' => 'hard-hat',
                    'module' => 'Obras',
                    'title' => 'Obra sin personal',
                    'message' => $o->folio . ' está en ejecución y no tiene cuadrilla asignada',
                    'link' => base_url('ventas/ObrasVentas/detalle/' . $o->id),
                    'time' => 'Hoy',
                ];
            }
        }
        return array_slice($out, 0, (int) $limite);
    }

    private function avance($tipo, $id) {
        $fases = $this->fases($tipo, $id);
        if (!$fases) {
            return 0;
        }
        $suma = 0;
        foreach ($fases as $f) {
            $suma += (float) $f->porcentaje;
        }
        return round($suma / count($fases), 1);
    }

    private function empleados() {
        if (!$this->db->table_exists('empleados')) {
            return [];
        }
        $this->db->select('id, numero_empleado, nombre, apellido_paterno');
        if ($this->db->field_exists('estatus', 'empleados')) {
            $this->db->where('estatus', 1);
        }
        $this->db->order_by('nombre', 'ASC');
        $this->db->limit(80);
        return $this->db->get('empleados')->result();
    }

    private function carpeta_valida($carpeta_id, $tipo, $origen_id) {
        $row = $this->db->where('id', (int) $carpeta_id)->get('expediente_carpetas')->row();
        return $row && $row->origen_tipo === $this->tipo($tipo) && (int) $row->origen_id === (int) $origen_id;
    }

    private function nombre_carpeta($nombre) {
        $nombre = trim(preg_replace('/[\/\\\\]+/', ' ', (string) $nombre));
        $nombre = preg_replace('/\s+/', ' ', $nombre);
        return mb_substr($nombre, 0, 120);
    }

    private function tipo($tipo) {
        return in_array($tipo, ['cotizacion', 'obra', 'venta'], true) ? $tipo : 'cotizacion';
    }

    private function estatus_fase($estatus) {
        return in_array($estatus, ['Pendiente', 'En proceso', 'Terminada'], true) ? $estatus : 'Pendiente';
    }

    private function fecha($valor) {
        $valor = trim((string) $valor);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
    }

    private function texto($valor, $max) {
        $valor = trim((string) $valor);
        return $valor === '' ? null : mb_substr($valor, 0, $max);
    }
}
