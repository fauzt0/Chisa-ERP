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
            $sel_ov = 'ov.id, ov.folio, ov.cliente_id, ov.total, ov.estatus, ov.fecha_orden,
                c.razon_social as cliente, c.nombre_comercial, c.telefono, c.email, c.rfc';
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
                    base_url('ventas/Ordenes?abrir=' . (int) $ov->id),
                    (int) $ov->cliente_id,
                    $ov->rfc ?? ''
                );
            }
        }

        if ($this->db->table_exists('obras')) {
            $sel_ob = 'o.id, o.folio, o.nombre, o.cliente_id, o.total, o.estatus, o.fecha_creacion,
                c.razon_social as cliente, c.nombre_comercial, c.telefono, c.rfc';
            if ($this->db->field_exists('saldo_pendiente', 'obras')) {
                $sel_ob .= ', o.saldo_pendiente, o.estatus_pago';
            }
            $this->db->select($sel_ob, false);
            $this->db->from('obras o');
            $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
            $this->db->where('o.activo', 1);
            $this->db->where_not_in('o.estatus', ['Cancelada']);
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
                    base_url('obras/Obras/detalle/' . $ob->id),
                    (int) $ob->cliente_id,
                    $ob->rfc ?? ''
                );
            }
        }

        usort($items, function ($a, $b) {
            return ((int) $b->dias) <=> ((int) $a->dias);
        });

        return array_slice($items, 0, (int) $limite);
    }

    public function get_por_cliente($cliente_id, $limite = 20) {
        $cliente_id = (int) $cliente_id;
        $todos = $this->get_pendientes(200);
        $out = [];
        foreach ($todos as $p) {
            if ((int) $p->cliente_id === $cliente_id) {
                $out[] = $p;
            }
            if (count($out) >= $limite) {
                break;
            }
        }
        return $out;
    }

    /**
     * Parcialidades de obra vencidas o con vencimiento en 7 días.
     */
    public function get_parcialidades_alerta($limite = 12) {
        if (!$this->db->table_exists('obras_parcialidades')) {
            return [];
        }
        $this->load->model('Obras/ObrasModel');
        $this->ObrasModel->marcar_parcialidades_vencidas();

        $this->db->select('p.*, o.folio, o.nombre as obra_nombre, c.razon_social as cliente, c.rfc');
        $this->db->from('obras_parcialidades p');
        $this->db->join('obras o', 'o.id = p.obra_id');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('p.activo', 1);
        $this->db->where_not_in('p.estatus', ['Pagada']);
        $this->db->where('o.activo', 1);
        $this->db->where('p.fecha_programada <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->order_by('p.fecha_programada', 'ASC');
        $this->db->limit((int) $limite);
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->dias_vencer = (int) floor((strtotime($r->fecha_programada) - time()) / 86400);
            $r->link = base_url('obras/Obras/detalle/' . $r->obra_id);
        }
        return $rows;
    }

    public function recalcular_saldo_cliente($cliente_id) {
        $cliente_id = (int) $cliente_id;
        if ($cliente_id <= 0 || !$this->db->table_exists('clientes')) {
            return 0;
        }
        $ov = 0;
        if ($this->db->field_exists('saldo_pendiente', 'ordenes_venta')) {
            $row = $this->db->query(
                "SELECT COALESCE(SUM(saldo_pendiente),0) AS s FROM ordenes_venta
                 WHERE cliente_id = ? AND estatus NOT IN ('Cotización','Cancelada')",
                [$cliente_id]
            )->row();
            $ov = (float) ($row->s ?? 0);
        }
        $ob = 0;
        if ($this->db->table_exists('obras') && $this->db->field_exists('saldo_pendiente', 'obras')) {
            $row = $this->db->query(
                "SELECT COALESCE(SUM(saldo_pendiente),0) AS s FROM obras
                 WHERE cliente_id = ? AND activo = 1 AND estatus <> 'Cancelada'",
                [$cliente_id]
            )->row();
            $ob = (float) ($row->s ?? 0);
        }
        $saldo = round($ov + $ob, 2);
        if ($this->db->field_exists('saldo_pendiente', 'clientes')) {
            $this->db->where('id', $cliente_id);
            $this->db->update('clientes', ['saldo_pendiente' => $saldo]);
        }
        return $saldo;
    }

    private function _fila($tipo, $id, $folio, $cliente, $comercial, $tel, $total, $saldo, $pago, $estatus, $fecha, $link, $cliente_id = 0, $rfc = '') {
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
        $row->cliente_id = (int) $cliente_id;
        $row->rfc = $rfc;
        $row->severidad = $dias >= 7 ? 'danger' : ($dias >= 3 ? 'warning' : 'info');
        return $row;
    }
}
