<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Acciones de seguimiento para cotización, obra y venta.
 * La sesión ya la exige MY_Controller; cada pantalla que lo abre tiene su módulo.
 */
class Seguimiento extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/SeguimientoModel');
    }

    public function fase_ajax() {
        $post = $this->input->post();
        $post['usuario_id'] = $this->session->userdata('id');
        echo json_encode($this->SeguimientoModel->guardar_fase($post));
    }

    public function eliminar_fase_ajax() {
        echo json_encode($this->SeguimientoModel->eliminar_fase(
            (int) $this->input->post('id'),
            (int) $this->input->post('origen_id')
        ));
    }

    public function nota_ajax() {
        echo json_encode($this->SeguimientoModel->nota(
            $this->input->post('origen_tipo'),
            (int) $this->input->post('origen_id'),
            $this->input->post('mensaje'),
            $this->session->userdata('id')
        ));
    }

    public function carpeta_ajax() {
        echo json_encode($this->SeguimientoModel->crear_carpeta(
            $this->input->post('origen_tipo'),
            (int) $this->input->post('origen_id'),
            $this->input->post('nombre'),
            $this->input->post('parent_id')
        ));
    }

    public function subir_ajax() {
        $tipo = (string) $this->input->post('origen_tipo');
        $id = (int) $this->input->post('origen_id');
        $carpeta = (int) $this->input->post('carpeta_id');
        if ($id <= 0 || empty($_FILES['archivos']['name'][0]) && empty($_FILES['archivos']['name'])) {
            echo json_encode(['success' => false, 'message' => 'Selecciona al menos un archivo']);
            return;
        }
        $nombres = $_FILES['archivos']['name'];
        $es_multi = is_array($nombres);
        $total = $es_multi ? count($nombres) : 1;
        $ok = 0;
        $ultimo = '';
        for ($i = 0; $i < $total; $i++) {
            $archivo = [
                'name' => $es_multi ? $_FILES['archivos']['name'][$i] : $_FILES['archivos']['name'],
                'type' => $es_multi ? $_FILES['archivos']['type'][$i] : $_FILES['archivos']['type'],
                'tmp_name' => $es_multi ? $_FILES['archivos']['tmp_name'][$i] : $_FILES['archivos']['tmp_name'],
                'error' => $es_multi ? $_FILES['archivos']['error'][$i] : $_FILES['archivos']['error'],
                'size' => $es_multi ? $_FILES['archivos']['size'][$i] : $_FILES['archivos']['size'],
            ];
            $_FILES['archivo'] = $archivo;
            $res = $this->subir_uno($tipo, $id, $carpeta);
            if (!empty($res['success'])) {
                $ok++;
                $ultimo = $res['message'];
            } else {
                $ultimo = $res['message'];
            }
        }
        echo json_encode([
            'success' => $ok > 0,
            'message' => $ok > 0 ? ($ok . ' archivo(s) en el expediente') : ($ultimo ?: 'No se pudo subir'),
        ]);
    }

    public function eliminar_archivo_ajax() {
        echo json_encode($this->SeguimientoModel->eliminar_archivo((int) $this->input->post('id')));
    }

    public function descargar($id = 0) {
        $row = $this->SeguimientoModel->get_archivo($id);
        if (!$row) {
            show_404();
            return;
        }
        $abs = $this->ruta_segura($row);
        if ($abs === '') {
            show_error('El archivo no está disponible', 404);
            return;
        }
        $this->load->helper('download');
        force_download($row->nombre, file_get_contents($abs));
    }

    public function asignar_ajax() {
        echo json_encode($this->SeguimientoModel->asignar(
            (int) $this->input->post('obra_id'),
            (int) $this->input->post('empleado_id'),
            $this->input->post('rol'),
            $this->session->userdata('id')
        ));
    }

    public function quitar_asignacion_ajax() {
        echo json_encode($this->SeguimientoModel->quitar_asignacion(
            (int) $this->input->post('id'),
            (int) $this->input->post('obra_id')
        ));
    }

    public function estatus_obra_ajax() {
        echo json_encode($this->SeguimientoModel->cambiar_estatus_obra(
            (int) $this->input->post('obra_id'),
            (string) $this->input->post('estatus'),
            $this->session->userdata('id')
        ));
    }

    public function adicion_ajax() {
        $res = $this->SeguimientoModel->crear_adicion(
            (int) $this->input->post('cotizacion_id'),
            $this->session->userdata('id')
        );
        echo json_encode($res);
    }

    public function registrar_consulta_ajax() {
        $dir = FCPATH . 'doc/entrenamiento_5/COTIZACIONES 2026';
        echo json_encode($this->SeguimientoModel->registrar_consulta($dir, $this->session->userdata('id') ?: 1));
    }

    private function subir_uno($tipo, $id, $carpeta) {
        $dir = FCPATH . 'uploads/expediente/' . $tipo . '/' . $id . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'xml', 'zip'];
        if (!in_array($ext, $permitidas, true)) {
            return ['success' => false, 'message' => 'Tipo no permitido: ' . $ext];
        }
        if ((int) $_FILES['archivo']['size'] > 12 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo supera 12 MB'];
        }
        $this->load->library('upload');
        $this->upload->initialize([
            'upload_path' => $dir,
            'allowed_types' => implode('|', $permitidas),
            'max_size' => 12288,
            'encrypt_name' => true,
        ]);
        if (!$this->upload->do_upload('archivo')) {
            return ['success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))];
        }
        $up = $this->upload->data();
        return $this->SeguimientoModel->guardar_archivo($tipo, $id, $carpeta, [
            'nombre' => $up['orig_name'],
            'ruta' => 'uploads/expediente/' . $tipo . '/' . $id . '/' . $up['file_name'],
            'extension' => ltrim($up['file_ext'], '.'),
            'tamano' => (int) $up['file_size'] * 1024,
        ], $this->session->userdata('id'), 0);
    }

    private function ruta_segura($row) {
        $abs = realpath(FCPATH . ltrim($row->ruta, '/'));
        if ($abs === false || !is_file($abs)) {
            return '';
        }
        $permitidas = [];
        if ((int) $row->es_enlace === 1) {
            $base = realpath(FCPATH . 'doc/entrenamiento_5');
            if ($base) {
                $permitidas[] = $base;
            }
        } else {
            $base = realpath(FCPATH . 'uploads/expediente');
            if ($base) {
                $permitidas[] = $base;
            }
        }
        foreach ($permitidas as $base) {
            if (strpos($abs, $base . DIRECTORY_SEPARATOR) === 0 || $abs === $base) {
                return $abs;
            }
        }
        return '';
    }
}
