<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Productos del POS pendientes de clasificar en la cotización de obra,
 * versiones de cotización y de orden, y la consulta cruzada de ventas.
 */
class CapturaPosModel extends CI_Model {

    public function asegurar() {
        if (!$this->db->table_exists('obra_captura_pos')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS obra_captura_pos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                obra_id INT(11) NOT NULL,
                producto_id INT(11) NOT NULL,
                nombre VARCHAR(200) NOT NULL,
                cantidad DECIMAL(14,2) NOT NULL DEFAULT 1,
                precio DECIMAL(14,2) NOT NULL DEFAULT 0,
                aplicado TINYINT(1) NOT NULL DEFAULT 0,
                cotizacion_id INT(11) NULL,
                creado_en DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_captura_obra (obra_id, aplicado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('documento_versiones')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS documento_versiones (
                id INT(11) NOT NULL AUTO_INCREMENT,
                tipo ENUM('cotizacion','orden') NOT NULL,
                documento_id INT(11) NOT NULL,
                raiz_id INT(11) NOT NULL,
                padre_id INT(11) NULL,
                numero INT(11) NOT NULL DEFAULT 1,
                nota VARCHAR(300) NULL,
                folio VARCHAR(40) NULL,
                fecha DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_ver_doc (tipo, documento_id),
                KEY idx_ver_raiz (tipo, raiz_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if ($this->db->table_exists('ordenes_venta') && !$this->db->field_exists('obra_id', 'ordenes_venta')) {
            $this->db->query("ALTER TABLE ordenes_venta ADD COLUMN obra_id INT(11) NULL");
        }
    }

    public function pendientes($obra_id) {
        $this->asegurar();
        $this->db->where('obra_id', (int) $obra_id);
        $this->db->where('aplicado', 0);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('obra_captura_pos')->result();
    }

    public function registrar_captura($obra_id, array $lineas) {
        $this->asegurar();
        $obra_id = (int) $obra_id;
        $n = 0;
        foreach ($lineas as $linea) {
            $producto_id = (int) ($linea['producto_id'] ?? 0);
            if ($producto_id <= 0) {
                continue;
            }
            $prod = $this->db->select('nombre, precio_venta')->where('id', $producto_id)->get('productos')->row();
            if (!$prod) {
                continue;
            }
            $this->db->insert('obra_captura_pos', [
                'obra_id' => $obra_id,
                'producto_id' => $producto_id,
                'nombre' => $prod->nombre,
                'cantidad' => (float) ($linea['cantidad'] ?? 1),
                'precio' => (float) ($prod->precio_venta ?: ($linea['precio_unitario'] ?? 0)),
                'aplicado' => 0,
                'creado_en' => date('Y-m-d H:i:s'),
            ]);
            $n++;
        }
        return $n;
    }

    public function aplicar(array $elecciones, $cotizacion_id) {
        $this->asegurar();
        $lineas = [];
        foreach ($elecciones as $el) {
            $row = $this->db->where('id', (int) ($el['id'] ?? 0))->where('aplicado', 0)->get('obra_captura_pos')->row();
            if (!$row) {
                continue;
            }
            $modo = ($el['modo'] ?? '') === 'concepto' ? 'concepto' : 'producto';
            if ($modo === 'concepto') {
                $lineas[] = [
                    'tipo' => 'Aplicacion',
                    'producto_id' => 0,
                    'concepto' => 'SUMINISTRO Y APLICACIÓN DE ' . mb_strtoupper($row->nombre) . ', INCLUYE MANO DE OBRA Y MATERIAL.',
                    'unidad' => 'M2',
                    'cantidad' => (float) $row->cantidad,
                    'precio_unitario' => (float) $row->precio,
                    'grupo' => '',
                ];
            } else {
                $lineas[] = [
                    'tipo' => 'Producto',
                    'producto_id' => (int) $row->producto_id,
                    'concepto' => $row->nombre,
                    'unidad' => 'PZA',
                    'cantidad' => (float) $row->cantidad,
                    'precio_unitario' => (float) $row->precio,
                    'grupo' => '',
                ];
            }
            $this->db->where('id', $row->id)->update('obra_captura_pos', [
                'aplicado' => 1,
                'cotizacion_id' => (int) $cotizacion_id ?: null,
            ]);
        }
        return $lineas;
    }

    public function variantes($producto_id) {
        $p = $this->db->where('id', (int) $producto_id)->get('productos')->row();
        if (!$p) {
            return [];
        }
        $raiz = (int) ($p->producto_padre_id ?: $p->id);
        $this->db->select('productos.*, categorias_productos.nombre as categoria_nombre');
        $this->db->from('productos');
        $this->db->join('categorias_productos', 'categorias_productos.id = productos.categoria_id', 'left');
        $this->db->group_start();
        $this->db->where('productos.id', $raiz);
        $this->db->or_where('productos.producto_padre_id', $raiz);
        $this->db->group_end();
        $this->db->where('productos.estatus', 'Activo');
        $this->db->order_by('productos.es_variante', 'ASC');
        $this->db->order_by('productos.nombre', 'ASC');
        return $this->db->get()->result();
    }

    public function asegurar_raiz($tipo, $documento_id, $folio) {
        $this->asegurar();
        $ya = $this->db->where('tipo', $tipo)->where('documento_id', (int) $documento_id)->get('documento_versiones')->row();
        if ($ya) {
            return $ya;
        }
        $this->db->insert('documento_versiones', [
            'tipo' => $tipo,
            'documento_id' => (int) $documento_id,
            'raiz_id' => (int) $documento_id,
            'padre_id' => null,
            'numero' => 1,
            'nota' => 'Versión inicial',
            'folio' => $folio,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->where('id', (int) $this->db->insert_id())->get('documento_versiones')->row();
    }

    public function registrar_version($tipo, $nuevo_id, $padre_id, $folio, $nota) {
        $this->asegurar();
        $padre = $this->db->where('tipo', $tipo)->where('documento_id', (int) $padre_id)->get('documento_versiones')->row();
        $raiz = $padre ? (int) $padre->raiz_id : (int) $padre_id;
        $num = 1;
        if ($padre) {
            $max = $this->db->select_max('numero')->where('tipo', $tipo)->where('raiz_id', $raiz)->get('documento_versiones')->row();
            $num = (int) ($max->numero ?? 1) + 1;
        }
        $this->db->insert('documento_versiones', [
            'tipo' => $tipo,
            'documento_id' => (int) $nuevo_id,
            'raiz_id' => $raiz,
            'padre_id' => (int) $padre_id,
            'numero' => $num,
            'nota' => mb_substr(trim((string) $nota), 0, 300) ?: null,
            'folio' => $folio,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        return $num;
    }

    public function arbol($tipo, $documento_id) {
        $this->asegurar();
        $row = $this->db->where('tipo', $tipo)->where('documento_id', (int) $documento_id)->get('documento_versiones')->row();
        $raiz = $row ? (int) $row->raiz_id : (int) $documento_id;
        $this->db->where('tipo', $tipo);
        $this->db->group_start();
        $this->db->where('raiz_id', $raiz);
        $this->db->or_where('documento_id', $raiz);
        $this->db->group_end();
        $this->db->order_by('numero', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('documento_versiones')->result();
    }

    public function buscar(array $f) {
        $q = trim((string) ($f['q'] ?? ''));
        $cliente = trim((string) ($f['cliente'] ?? ''));
        $desde = trim((string) ($f['desde'] ?? ''));
        $hasta = trim((string) ($f['hasta'] ?? ''));
        $lugar = trim((string) ($f['lugar'] ?? ''));
        $tipo = $f['tipo'] ?? 'todos';
        $out = [];
        if ($tipo === 'todos' || $tipo === 'orden') {
            $out = array_merge($out, $this->buscar_ordenes($q, $cliente, $desde, $hasta, $lugar));
        }
        if ($tipo === 'todos' || $tipo === 'cotizacion') {
            $out = array_merge($out, $this->buscar_cotizaciones($q, $cliente, $desde, $hasta, $lugar, !empty($f['solo_aplicacion'])));
        }
        if ($tipo === 'todos' || $tipo === 'obra') {
            $out = array_merge($out, $this->buscar_obras($q, $cliente, $lugar));
        }
        return array_slice($out, 0, 80);
    }

    private function buscar_ordenes($q, $cliente, $desde, $hasta, $lugar) {
        if (!$this->db->table_exists('ordenes_venta')) {
            return [];
        }
        $this->db->select('ordenes_venta.id, ordenes_venta.folio, ordenes_venta.fecha_orden as fecha, ordenes_venta.estatus, ordenes_venta.total, ordenes_venta.direccion_envio, clientes.razon_social');
        $this->db->from('ordenes_venta');
        $this->db->join('clientes', 'clientes.id = ordenes_venta.cliente_id', 'left');
        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('ordenes_venta.folio', $q);
            $this->db->or_like('clientes.razon_social', $q);
            $this->db->or_where("ordenes_venta.id IN (SELECT orden_venta_id FROM detalle_orden_venta d JOIN productos p ON p.id = d.producto_id WHERE p.nombre LIKE " . $this->db->escape('%' . $q . '%') . ")", null, false);
            $this->db->group_end();
        }
        if ($cliente !== '') {
            $this->db->like('clientes.razon_social', $cliente);
        }
        if ($desde !== '') {
            $this->db->where('ordenes_venta.fecha_orden >=', $desde);
        }
        if ($hasta !== '') {
            $this->db->where('ordenes_venta.fecha_orden <=', $hasta);
        }
        if ($lugar !== '') {
            $this->db->like('ordenes_venta.direccion_envio', $lugar);
        }
        $this->db->order_by('ordenes_venta.id', 'DESC');
        $this->db->limit(30);
        $rows = [];
        foreach ($this->db->get()->result() as $r) {
            $rows[] = [
                'tipo' => 'Orden de venta',
                'folio' => $r->folio,
                'fecha' => $r->fecha,
                'quien' => $r->razon_social,
                'detalle' => $r->estatus . ($r->direccion_envio ? ' · ' . $r->direccion_envio : ''),
                'monto' => $r->total,
                'url' => base_url('ventas/Ordenes?abrir=' . $r->id),
            ];
        }
        return $rows;
    }

    private function buscar_cotizaciones($q, $cliente, $desde, $hasta, $lugar, $solo_aplicacion) {
        if (!$this->db->table_exists('cotizaciones_comerciales')) {
            return [];
        }
        $this->db->select('c.id, c.folio, c.fecha, c.estatus, c.constructora, c.obra_nombre, c.ubicacion, c.es_consulta, clientes.razon_social');
        $this->db->from('cotizaciones_comerciales c');
        $this->db->join('clientes', 'clientes.id = c.cliente_id', 'left');
        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('c.folio', $q);
            $this->db->or_like('c.constructora', $q);
            $this->db->or_like('c.obra_nombre', $q);
            $this->db->or_like('clientes.razon_social', $q);
            $this->db->or_where("c.id IN (SELECT cotizacion_id FROM cotizaciones_comerciales_detalle d WHERE d.concepto LIKE " . $this->db->escape('%' . $q . '%') . ")", null, false);
            $this->db->group_end();
        }
        if ($cliente !== '') {
            $this->db->group_start();
            $this->db->like('clientes.razon_social', $cliente);
            $this->db->or_like('c.constructora', $cliente);
            $this->db->group_end();
        }
        if ($desde !== '') {
            $this->db->where('c.fecha >=', $desde);
        }
        if ($hasta !== '') {
            $this->db->where('c.fecha <=', $hasta);
        }
        if ($lugar !== '') {
            $this->db->group_start();
            $this->db->like('c.ubicacion', $lugar);
            $this->db->or_like('c.obra_nombre', $lugar);
            $this->db->group_end();
        }
        if ($solo_aplicacion) {
            $this->db->where("c.id IN (SELECT cotizacion_id FROM cotizaciones_comerciales_detalle d WHERE d.tipo = 'Aplicacion')", null, false);
        }
        $this->db->order_by('c.es_consulta', 'ASC');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit(30);
        $rows = [];
        foreach ($this->db->get()->result() as $r) {
            $rows[] = [
                'tipo' => !empty($r->es_consulta) ? 'Archivo' : 'Cotización de obra',
                'folio' => $r->folio,
                'fecha' => $r->fecha,
                'quien' => $r->razon_social ?: $r->constructora,
                'detalle' => trim($r->obra_nombre . ' ' . $r->ubicacion . ' · ' . $r->estatus),
                'monto' => null,
                'url' => base_url('ventas/CotizacionesObra/editar/' . $r->id),
            ];
        }
        return $rows;
    }

    private function buscar_obras($q, $cliente, $lugar) {
        if (!$this->db->table_exists('obras')) {
            return [];
        }
        $this->db->select('o.id, o.folio, o.nombre, o.estatus, o.direccion, o.ciudad, o.fecha_creacion, clientes.razon_social');
        $this->db->from('obras o');
        $this->db->join('clientes', 'clientes.id = o.cliente_id', 'left');
        $this->db->where('o.activo', 1);
        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('o.folio', $q);
            $this->db->or_like('o.nombre', $q);
            $this->db->or_like('clientes.razon_social', $q);
            $this->db->group_end();
        }
        if ($cliente !== '') {
            $this->db->like('clientes.razon_social', $cliente);
        }
        if ($lugar !== '') {
            $this->db->group_start();
            $this->db->like('o.direccion', $lugar);
            $this->db->or_like('o.ciudad', $lugar);
            $this->db->or_like('o.nombre', $lugar);
            $this->db->group_end();
        }
        $this->db->order_by('o.id', 'DESC');
        $this->db->limit(20);
        $rows = [];
        foreach ($this->db->get()->result() as $r) {
            $rows[] = [
                'tipo' => 'Obra',
                'folio' => $r->folio,
                'fecha' => substr((string) $r->fecha_creacion, 0, 10),
                'quien' => $r->razon_social,
                'detalle' => trim($r->nombre . ' · ' . $r->direccion . ' ' . $r->ciudad . ' · ' . $r->estatus),
                'monto' => null,
                'url' => base_url('ventas/ObrasVentas/detalle/' . $r->id),
            ];
        }
        return $rows;
    }
}
