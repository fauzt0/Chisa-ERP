    /**
     * Ajusta el stock de un producto (AJAX)
     */
    public function ajustar_stock_ajax() {
        $producto_id = $this->input->post('producto_id');
        $tipo_movimiento = $this->input->post('tipo_movimiento');
        $cantidad = $this->input->post('cantidad');
        $motivo = $this->input->post('motivo');
        
        if(!$producto_id || !$tipo_movimiento || !$cantidad) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $data = [
            'producto_id' => $producto_id,
            'tipo_movimiento' => $tipo_movimiento,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $this->session->userdata('user_id')
        ];
        
        $result = $this->ProductosModel->registrar_movimiento($data);
        echo json_encode($result);
    }
