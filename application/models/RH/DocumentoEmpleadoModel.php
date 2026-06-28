<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DocumentoEmpleadoModel extends CI_Model {

    const TIPOS_DOCUMENTO = [
        'acta_nacimiento'      => 'Acta de Nacimiento',
        'curp'                 => 'CURP',
        'rfc'                  => 'Constancia RFC',
        'nss'                  => 'IMSS / Seguro Social',
        'ine'                  => 'INE / Identificación',
        'comprobante_domicilio'=> 'Comprobante de Domicilio',
        'comprobante_estudios' => 'Comprobante de Estudios',
        'carta_recomendacion'  => 'Carta de Recomendación',
        'constancia_fiscal'    => 'Constancia de Situación Fiscal',
        'cuenta_bancaria'      => 'Estado de Cuenta / CLABE',
        'contrato_firmado'     => 'Contrato Firmado',
        'renuncia'             => 'Carta de Renuncia',
        'carta_baja'           => 'Carta de Baja',
        'finiquito'            => 'Finiquito (documento)',
        'liquidacion'          => 'Liquidación (documento)',
        'otro'                 => 'Otro',
    ];

    public function get_por_empleado($empleado_id) {
        return $this->db
            ->where('empleado_id', (int)$empleado_id)
            ->order_by('fecha_subida', 'DESC')
            ->get('empleados_documentos')
            ->result();
    }

    public function get_por_id($id, $empleado_id = null) {
        $this->db->where('id', (int)$id);
        if ($empleado_id !== null) {
            $this->db->where('empleado_id', (int)$empleado_id);
        }
        return $this->db->get('empleados_documentos')->row();
    }

    public function insertar($data) {
        $this->db->insert('empleados_documentos', $data);
        return $this->db->insert_id();
    }

    public function eliminar($id, $empleado_id = null) {
        $doc = $this->get_por_id($id, $empleado_id);
        if (!$doc) {
            return false;
        }

        $this->db->where('id', (int)$id)->delete('empleados_documentos');

        $ruta = FCPATH . ltrim($doc->ruta_archivo, '/');
        if (file_exists($ruta)) {
            @unlink($ruta);
        }

        return true;
    }

    public function contar_por_empleado($empleado_id) {
        return (int)$this->db
            ->where('empleado_id', (int)$empleado_id)
            ->count_all_results('empleados_documentos');
    }

    public function label_tipo($tipo) {
        return self::TIPOS_DOCUMENTO[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
    }

    const DOCUMENTOS_REQUERIDOS = [
        'acta_nacimiento',
        'curp',
        'rfc',
        'nss',
        'ine',
        'comprobante_domicilio',
    ];

    public function get_tipos_presentes($empleado_id) {
        $rows = $this->db
            ->select('tipo_documento')
            ->where('empleado_id', (int)$empleado_id)
            ->group_by('tipo_documento')
            ->get('empleados_documentos')
            ->result();

        $tipos = [];
        foreach ($rows as $row) {
            $tipos[$row->tipo_documento] = true;
        }
        return $tipos;
    }

    public function get_checklist_empleado($empleado_id) {
        $presentes = $this->get_tipos_presentes($empleado_id);
        $items = [];
        $faltantes = [];

        foreach (self::DOCUMENTOS_REQUERIDOS as $tipo) {
            $ok = isset($presentes[$tipo]);
            $items[] = [
                'tipo'   => $tipo,
                'label'  => self::TIPOS_DOCUMENTO[$tipo] ?? $tipo,
                'tiene'  => $ok,
            ];
            if (!$ok) {
                $faltantes[] = self::TIPOS_DOCUMENTO[$tipo] ?? $tipo;
            }
        }

        $total_req = count(self::DOCUMENTOS_REQUERIDOS);
        $completados = $total_req - count($faltantes);
        $porcentaje = $total_req > 0 ? round(($completados / $total_req) * 100) : 0;

        return [
            'items'        => $items,
            'faltantes'    => $faltantes,
            'completados'  => $completados,
            'total_req'    => $total_req,
            'porcentaje'   => $porcentaje,
            'completo'     => empty($faltantes),
        ];
    }

    public function get_empleados_expediente_incompleto($limite = 20) {
        $empleados = $this->db
            ->select('id, numero_empleado, nombre, apellido_paterno, apellido_materno')
            ->where('estatus', 1)
            ->order_by('nombre', 'ASC')
            ->get('empleados')
            ->result();

        $incompletos = [];
        foreach ($empleados as $emp) {
            $check = $this->get_checklist_empleado($emp->id);
            if (!$check['completo']) {
                $incompletos[] = [
                    'id'              => $emp->id,
                    'numero_empleado' => $emp->numero_empleado,
                    'nombre'          => trim($emp->nombre . ' ' . $emp->apellido_paterno . ' ' . ($emp->apellido_materno ?? '')),
                    'faltantes'       => $check['faltantes'],
                    'total_faltantes' => count($check['faltantes']),
                    'porcentaje'      => $check['porcentaje'],
                ];
            }
            if (count($incompletos) >= $limite) {
                break;
            }
        }
        return $incompletos;
    }

    public function contar_expedientes_incompletos() {
        $empleados = $this->db->select('id')->where('estatus', 1)->get('empleados')->result();
        $total = 0;
        foreach ($empleados as $emp) {
            $check = $this->get_checklist_empleado($emp->id);
            if (!$check['completo']) {
                $total++;
            }
        }
        return $total;
    }

    public function formatear_tamano($bytes) {
        $bytes = (int)$bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
