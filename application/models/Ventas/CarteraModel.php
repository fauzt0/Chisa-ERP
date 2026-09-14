<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cartera de cobro: OV + obras con saldo, cliente visible y antigüedad.
 */
class CarteraModel extends MY_Model {

    public function get_resumen() {
        $pendientes = $this->get_pendientes(200);
        $docs = count($pendientes);
        $saldo = 0;
        $criticos = 0;
        foreach ($pendientes as $p) {
            $saldo += (float) $p->saldo;
            if ((int) $p->dias >= 7) {
                $criticos++;
            }
        }
        return [
            'documentos' => $docs,
            'saldo' => $saldo,
            'criticos' => $criticos,
        ];
    }

    /**
     * @return array<object>
     */
    public function get_pendientes($limite = 12) {
        $items = [];

        if ($this->db->table_exists('ordenes_venta')) {
            $sel_ov = 'ov.id, ov.folio, ov.total, ov.estatus, ov.fecha_orden,
                c.razon_social as cliente, c.nombre_comercial, c.telefono, c.email';
            if ($this->db->field_exists('saldo_pendiente', 'ordenes_venta')) {
                $sel_ov .= ', ov.saldo_pendiente, ov.estatus_pago';
            }
            $this->db->select($sel_ov, false);
            $this->db->from('ordenes_venta ov');
            $this->db->join('clientes c', 'c.id = ov.cliente_id', 'left');
            $this->db->where_not_in('ov.estatus', ['Cotización', 'Cancelada']);
            if ($this->db->field_exists('saldo_pendiente', 'ordenes_venta')) {
                $this->db->where('ov.saldo_pendiente >', 0);
            } else {
                $this->db->where('ov.estatus_pago !=', 'Pagado');
            }
            $this->db->order_by('ov.fecha_orden', 'ASC');
            $this->db->limit((int) $limite);
            foreach ($this->db->get()->result() as $ov) {
                $saldo = isset($ov->saldo_pendiente) ? (float) $ov->saldo_pendiente : (float) $ov->total;
                $items[] = $this->_fila(
                    'venta',
                    $ov->id,
                    $ov->folio,
                    $ov->cliente ?: 'Sin cliente',
                    $ov->nombre_comercial,
                    $ov->telefono,
                    (float) $ov->total,
                    $saldo,
                    $ov->estatus_pago ?? 'Pendiente',
                    $ov->estatus,
                    $ov->fecha_orden,
                    base_url('ventas/Ordenes')
                );
            }
        }

        if ($this->db->table_exists('obras')) {
            $sel_ob = 'o.id, o.folio, o.nombre, o.total, o.estatus, o.fecha_creacion,
                c.razon_social as cliente, c.nombre_comercial, c.telefono';
            if ($this->db->field_exists('saldo_pendiente', 'obras')) {
                $sel_ob .= ', o.saldo_pendiente, o.estatus_pago';
            }
            $this->db->select($sel_ob, false);
            $this->db->from('obras o');
            $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
            $this->db->where('o.activo', 1);
            $this->db->where_not_in('o.estatus', ['Cancelada', 'Planificación']);
            $this->db->where('o.total >', 0);
            if ($this->db->field_exists('saldo_pendiente', 'obras')) {
                $this->db->where('o.saldo_pendiente >', 0);
            }
            $this->db->order_by('o.fecha_creacion', 'ASC');
            $this->db->limit((int) $limite);
            foreach ($this->db->get()->result() as $ob) {
                $saldo = isset($ob->saldo_pendiente) ? (float) $ob->saldo_pendiente : (float) $ob->total;
                $items[] = $this->_fila(
                    'obra',
                    $ob->id,
                    $ob->folio,
                    $ob->cliente ?: 'Sin cliente',
                    $ob->nombre_comercial,
                    $ob->telefono,
                    (float) $ob->total,
                    $saldo,
                    $ob->estatus_pago ?? 'Pendiente',
                    $ob->estatus,
                    $ob->fecha_creacion,
                    base_url('obras/Obras/detalle/' . $ob->id)
                );
            }
        }

        usort($items, function ($a, $b) {
            return ((int) $b->dias) <=> ((int) $a->dias);
        });

        return array_slice($items, 0, (int) $limite);
    }

    private function _fila($tipo, $id, $folio, $cliente, $comercial, $tel, $total, $saldo, $pago, $estatus, $fecha, $link) {
        $dias = 0;
        if (!empty($fecha)) {
            $dias = (int) floor((time() - strtotime($fecha)) / 86400);
            if ($dias < 0) {
                $dias = 0;
            }
        }
        $row = new stdClass();
        $row->tipo = $tipo;
        $row->id = $id;
        $row->folio = $folio;
        $row->cliente = $cliente;
        $row->nombre_comercial = $comercial;
        $row->telefono = $tel;
        $row->total = $total;
        $row->saldo = $saldo;
        $row->estatus_pago = $pago;
        $row->estatus = $estatus;
        $row->fecha = $fecha;
        $row->dias = $dias;
        $row->link = $link;
        $row->severidad = $dias >= 7 ? 'danger' : ($dias >= 3 ? 'warning' : 'info');
        return $row;
    }
}
