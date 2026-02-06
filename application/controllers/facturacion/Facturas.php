<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facturas extends MY_Controller {
    
    protected $modulo = 'Facturacion';
    
    public function __construct() {
        parent::__construct();
        $this->load->library('FactureApp');
        $this->load->model('Facturacion/FacturaApiTokenModel', 'token_model');
    }

    public function index() {
        $this->viewData['pageTitle'] = 'Dashboard Facturación';
        $this->viewData['headTitle'] = 'Dashboard Facturación';
        $this->viewData['breadcrumb'] = 'Inicio > Facturación';

        // Verificar token
        $token = $this->token_model->get_token();
        $this->viewData['conectado'] = ($token && $token->access_token);
        
        // Filtros
        $fecha_inicio = $this->input->get('fecha_inicio');
        $fecha_fin = $this->input->get('fecha_fin');
        
        $this->db->order_by('fecha_emision', 'DESC');
        if($fecha_inicio) $this->db->where('DATE(fecha_emision) >=', $fecha_inicio);
        if($fecha_fin) $this->db->where('DATE(fecha_emision) <=', $fecha_fin);
        
        $this->viewData['facturas'] = $this->db->get('facturas')->result_array();
        $this->viewData['pageView'] = 'facturacion/dashboard';
        
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function conectar() {
        $url = $this->factureapp->get_login_url();
        redirect($url);
    }

    public function callback() {
        $code = $this->input->get('code');
        
        if ($code) {
            $response = $this->factureapp->authorize($code);
            
            if (isset($response['access_token'])) {
                $this->factureapp->save_token_from_response($response);
                $this->session->set_flashdata('success', 'Conexión exitosa con Facture App');
            } else {
                $this->session->set_flashdata('error', 'Error al obtener token: ' . json_encode($response));
            }
        } else {
            $this->session->set_flashdata('error', 'No se recibió código de autorización');
        }
        
        redirect('facturacion/Facturas');
    }

    /**
     * Desconectar (eliminar token)
     */
    public function desconectar() {
        $this->token_model->delete_token();
        $this->session->set_flashdata('success', 'Desconectado de Facture App exitosamente');
        redirect('facturacion/Facturas');
    }

    /**
     * Mostrar formulario de nueva factura
     */
    public function crear() {
        $token = $this->token_model->get_token();
        if (!$token) redirect('facturacion/Facturas');

        // Cargar helper de facturación
        $this->load->helper('facturacion');

        $sucursales = $this->factureapp->get_sucursales($token->access_token);
        
        
        $this->viewData['modulo'] = $this->modulo;
        $this->viewData['pageTitle'] = 'Nueva Factura';
        $this->viewData['headTitle'] = 'Nueva Factura';
        $this->viewData['breadcrumb'] = 'Inicio > Facturación > Nueva';
        $this->viewData['pageView'] = 'facturacion/crear';
        $this->viewData['sucursales'] = $sucursales;
        
        // Pasar catálogos a la vista
        $this->viewData['usos_cfdi'] = get_catalogo_uso_cfdi();
        $this->viewData['regimenes'] = get_catalogo_regimen_fiscal();
        $this->viewData['metodos_pago'] = get_catalogo_metodo_pago();
        $this->viewData['formas_pago'] = get_catalogo_forma_pago();
        $this->viewData['objetos_imp'] = get_catalogo_objeto_imp();
        $this->viewData['exportaciones'] = get_catalogo_exportacion();
        
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * Buscar clientes para autocompletado
     */
    public function buscar_cliente() {
        $term = $this->input->get('term');
        if (!$term) {
            echo json_encode([]);
            return;
        }

        $this->db->like('razon_social', $term);
        $this->db->or_like('rfc', $term);
        $this->db->limit(10);
        $query = $this->db->get('clientes');
        
        $results = [];
        foreach ($query->result() as $row) {
            $results[] = [
                'id' => $row->id,
                'label' => $row->razon_social . ' (' . $row->rfc . ')',
                'value' => $row->razon_social,
                'rfc' => $row->rfc,
                'cp' => $row->codigo_postal,     // Asumiendo nombre de columna
                'regimen' => $row->regimen_fiscal, // Asumiendo nombre de columna
                'email' => $row->email
            ];
        }
        echo json_encode($results);
    }

    /**
     * Buscar productos para autocompletado
     */
    public function buscar_producto() {
        $term = $this->input->get('term');
        if (!$term) {
            echo json_encode([]);
            return;
        }

        $this->db->like('nombre', $term);
        $this->db->limit(10);
        $query = $this->db->get('productos');
        
        $results = [];
        foreach ($query->result() as $row) {
            $results[] = [
                'id' => $row->id,
                'label' => $row->nombre,
                'value' => $row->nombre,
                'precio' => $row->precio_venta,
                'descripcion' => $row->descripcion
                // Agrega clave SAT si existe en BD, si no, usuario debe ingresarla manuarlmente
            ];
        }
        echo json_encode($results);
    }

    public function emitir() {
        $token = $this->token_model->get_token();
        if (!$token) redirect('facturacion/Facturas');

        // Recopilar datos del formulario
        $sucursal_id = $this->input->post('sucursal_id');
        $rfc_receptor = $this->input->post('rfc_receptor');
        $nombre_receptor = $this->input->post('nombre_receptor');
        $conceptos_form = $this->input->post('conceptos');

        // Construir estructura JSON básica
        $conceptos = [];
        if ($conceptos_form) {
            foreach ($conceptos_form as $c) {
                // Calcular base e importe de impuesto
                $importe = $c['cantidad'] * $c['valor_unitario'];
                $base = $importe; 
                $tasa = 0.160000;
                $importe_impuesto = $base * $tasa;

                $conceptos[] = [
                    'ClaveProdServ' => $c['clave'],
                    'Cantidad' => $c['cantidad'],
                    'ClaveUnidad' => $c['unidad'],
                    'Descripcion' => $c['descripcion'],
                    'ValorUnitario' => $c['valor_unitario'],
                    'Importe' => $importe,
                    'ObjetoImp' => isset($c['objeto_imp']) ? $c['objeto_imp'] : '02', // CFDI 4.0
                    'Impuestos' => [
                        'Traslados' => [
                            'Traslado' => [
                                [
                                    'Base' => number_format($c['valor_unitario'] * $c['cantidad'], 2, '.', ''), // Agregar Base
                                    'Impuesto' => '002',
                                    'TipoFactor' => 'Tasa',
                                    'TasaOCuota' => '0.160000', // REGLA SAT: 6 decimales para tasa fija
                                    'Importe' => number_format($importe_impuesto, 2, '.', '')
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        // Calcular totales para la cabecera
        $subtotal = 0;
        $total_impuestos = 0;
        $total_base_impuestos = 0; // Nueva variable para CFDI 4.0
        
        foreach ($conceptos as $c) {
            $subtotal += $c['Importe'];
            if (isset($c['Impuestos']['Traslados']['Traslado'])) {
                foreach ($c['Impuestos']['Traslados']['Traslado'] as $t) {
                    $total_impuestos += $t['Importe'];
                    $total_base_impuestos += $t['Base']; // Sumar base
                }
            }
        }
        $total = $subtotal + $total_impuestos;

        // Obtener datos de la sucursal seleccionada para obtener el RFC Emisor real
        $sucursales = $this->factureapp->get_sucursales($token->access_token);
        
        // Intentar obtener perfil para datos fiscales globales (Razon Social real)
        $perfil = $this->factureapp->get_perfil($token->access_token);
        
        $sucursal_obj = null;
        if (isset($sucursales['pagination']['items'])) {
            foreach ($sucursales['pagination']['items'] as $s) {
                if ($s['id'] == $sucursal_id) {
                    $sucursal_obj = $s;
                    break;
                }
            }
        }

        // Datos del Emisor (Priorizar perfil, luego fallback)
        $rfc_emisor = isset($perfil['usuario']['rfc']) ? $perfil['usuario']['rfc'] : 'IVD920810GU2';
        $nombre_emisor = isset($perfil['usuario']['razonSocial']) ? $perfil['usuario']['razonSocial'] : (isset($perfil['usuario']['nombre']) ? $perfil['usuario']['nombre'] : 'INNOVACION VALOR Y DESARROLLO');
        $regimen_emisor = isset($perfil['usuario']['regimenFiscal']) ? $perfil['usuario']['regimenFiscal'] : '601';

        if ($sucursal_obj) {
             // Si la sucursal tiene un RFC específico, lo usamos (casos multirfc, aunque raro en FactureApp)
             if(isset($sucursal_obj['rfc'])) $rfc_emisor = $sucursal_obj['rfc'];
             elseif(isset($sucursal_obj['RFC'])) $rfc_emisor = $sucursal_obj['RFC'];
             elseif(isset($sucursal_obj['Rfc'])) $rfc_emisor = $sucursal_obj['Rfc'];
             
             // Si el nombre de la sucursal NO es comercial sino fiscal, podría servir, 
             // pero el del perfil suele ser el más confiable para CFDI 4.0
             if(isset($sucursal_obj['regimenFiscal'])) $regimen_emisor = $sucursal_obj['regimenFiscal'];
        }

        // CORRECCIÓN CFDI 4.0: El nombre debe coincidir exactamente con la constancia.
        // Si seguimos usando el RFC de pruebas conocido y el perfil no lo trae bien:
        if ($rfc_emisor == 'IVD920810GU2' && $nombre_emisor == 'Chisa DEMO') {
            $nombre_emisor = 'INNOVACION VALOR Y DESARROLLO';
        }

        // Lugar de expedición (ZIP del emisor)
        $lugar_expedicion = isset($sucursal_obj['direccion']['codigopostal']) ? $sucursal_obj['direccion']['codigopostal'] : '44130';

        // RECEPTOR: Validaciones especiales para RFC Genérico XAXX010101000
        $uso_cfdi = $this->input->post('uso_cfdi');
        $cp_receptor = $this->input->post('cp_receptor');
        $regimen_receptor = $this->input->post('regimen_fiscal');

        if ($rfc_receptor == 'XAXX010101000') {
            $nombre_receptor = 'PUBLICO EN GENERAL'; // REGLA SAT: Debe ser exactamente este nombre
            $uso_cfdi = 'S01'; // Sin efectos fiscales (típico para genérico)
            $cp_receptor = $lugar_expedicion; // REGLA SAT: DomicilioFiscalReceptor debe ser igual a LugarExpedicion
            $regimen_receptor = '616'; // Sin obligaciones fiscales
        }

        // Estructura tentativa para "timbrado/json" - Mapeo directo a XML CFDI 4.0
        // NOTA: Agregamos campos de cabecera que la API reclama
        $factura_data = [
            'Version' => '4.0', // CAMBIO IMPORTANTE: CFDI 4.0
            'Serie' => $this->input->post('serie') ?: 'A',
            'Folio' => $this->input->post('folio') ?: time(), // Folio temporal si no hay
            'Fecha' => date('Y-m-d\TH:i:s'),
            'Sello' => '', // La API deberia poner esto, pero el nodo debe existir? Lo omitimos por ahora
            'FormaPago' => $this->input->post('forma_pago'),
            'NoCertificado' => '', // API debe ponerlo
            'Certificado' => '',   // API debe ponerlo
            'SubTotal' => number_format($subtotal, 2, '.', ''),
            'Moneda' => 'MXN',
            'Total' => number_format($total, 2, '.', ''),
            'TipoDeComprobante' => 'I',
            'Exportacion' => $this->input->post('exportacion') ?: '01', // Requerido en 4.0
            'MetodoPago' => $this->input->post('metodo_pago'),
            'LugarExpedicion' => $lugar_expedicion,
            
            // Emisor es requerido. Usamos datos reales de la sucursal.
            'Emisor' => [
                'Rfc' => $rfc_emisor,
                'Nombre' => $nombre_emisor,
                'RegimenFiscal' => $regimen_emisor
            ],

            'Sucursal' => ['id' => $sucursal_id], 

            'Receptor' => [
                'Rfc' => $rfc_receptor,
                'Nombre' => $nombre_receptor,
                'UsoCFDI' => $uso_cfdi,
                'DomicilioFiscalReceptor' => $cp_receptor, 
                'RegimenFiscalReceptor' => $regimen_receptor
            ],
            // Envolver Conceptos -> Concepto
            'Conceptos' => [
                'Concepto' => $conceptos
            ],
            'Impuestos' => [
                'TotalImpuestosTrasladados' => number_format($total_impuestos, 2, '.', ''),
                'Traslados' => [
                    'Traslado' => [
                        [
                            'Base' => number_format($total_base_impuestos, 2, '.', ''), // Requerido en 4.0
                            'Impuesto' => '002',
                            'TipoFactor' => 'Tasa',
                            'TasaOCuota' => '0.160000',
                            'Importe' => number_format($total_impuestos, 2, '.', '')
                        ]
                    ]
                ]
            ]
        ];

        // CORRECCIÓN PÚBLICO EN GENERAL (CFDI 4.0)
        // El nodo InformacionGlobal DEBE ir si el RFC es XAXX010101000 y Nombre PUBLICO EN GENERAL
        // Lo agregamos despues de definir el array para asegurar que no se sobrescriba
        if ($rfc_receptor == 'XAXX010101000' && $nombre_receptor == 'PUBLICO EN GENERAL') {
             $factura_data['InformacionGlobal'] = [
                'Periodicidad' => '01', // 01-Diaria, 02-Semanal, 03-Quincenal, 04-Mensual
                'Meses' => date('m'),   // 01-12
                'Año' => date('Y')
            ];
        }

        // Validar si hay serie/folio (opcional)
        if ($this->input->post('serie')) $factura_data['serie'] = $this->input->post('serie');
        if ($this->input->post('folio')) $factura_data['folio'] = $this->input->post('folio');

        /* NOTA: Si la API requiere XML estrictamente, aquí deberíamos usar una librería
           para generar el XML, codificarlo en base64 y enviarlo como:
           $payload = [
               'requestUuid' => uuid(),
               'encode' => base64_encode($xml_string),
               'sucursal' => ['id' => $sucursal_id]
           ];
           Por ahora probamos envío directo de datos JSON.
        */

        $resultado = $this->factureapp->timbrar_json($factura_data, $token->access_token);

        // DEBUG TEMPORAL
        echo '<pre>';
        echo "PAYLOAD JSON (sin codificar):\n";
        print_r($factura_data);
        echo "\n\nRESPUESTA API:\n";
        print_r($resultado);
        echo '</pre>';
        die();

        if (isset($resultado['succeed']) && $resultado['succeed'] == true) {
            
            // 1. Guardar en Base de Datos (Tabla 'facturas')
            $datos_db = [
                'folio_fiscal' => $resultado['uuid'], // UUID
                'serie' => isset($factura_data['Serie']) ? $factura_data['Serie'] : 'A',
                'folio' => isset($factura_data['Folio']) ? $factura_data['Folio'] : '',
                'fecha_emision' => date('Y-m-d H:i:s'),
                'rfc' => $rfc_receptor, // Corregido: rfc en lugar de cliente_rfc
                'razon_social' => $nombre_receptor, // Corregido: razon_social en lugar de cliente_nombre
                'subtotal' => $subtotal,
                'iva' => $total_impuestos,
                'total' => $total,
                'estatus' => 'Emitida',
                'codigo_postal' => $lugar_expedicion,
                'regimen_fiscal' => $regimen_receptor, // Agregado
                'uso_cfdi' => $uso_cfdi, // Agregado
                'cliente_id' => $this->input->post('cliente_id') ?: null, // Opcional
                'orden_venta_id' => null // Por ahora null
            ];
            
            // Insertar en facturas
            $this->db->insert('facturas', $datos_db);
            $factura_id = $this->db->insert_id();

            // 2. Descargar XML y PDF automáticamente
            $archivos = $this->factureapp->descargar_xml_pdf($resultado['uuid'], $token->access_token);
            
            $pdf_path_db = null;
            $xml_path_db = null;

            if (isset($archivos['xml']) && isset($archivos['pdf'])) {
                // Crear estructura de carpetas: assets/facturas/YYYY/MM/
                $anio = date('Y');
                $mes = date('m');
                $path_base = FCPATH . 'assets/facturas/' . $anio . '/' . $mes . '/';
                
                if (!is_dir($path_base)) {
                    mkdir($path_base, 0755, true);
                }
                
                $file_name = $resultado['uuid'];
                
                // Guardar XML
                $xml_decoded = base64_decode($archivos['xml']);
                $xml_path_full = $path_base . $file_name . '.xml';
                if (file_put_contents($xml_path_full, $xml_decoded)) {
                    $xml_path_db = 'assets/facturas/' . $anio . '/' . $mes . '/' . $file_name . '.xml';
                }

                // Guardar PDF
                $pdf_decoded = base64_decode($archivos['pdf']);
                $pdf_path_full = $path_base . $file_name . '.pdf';
                if (file_put_contents($pdf_path_full, $pdf_decoded)) {
                    $pdf_path_db = 'assets/facturas/' . $anio . '/' . $mes . '/' . $file_name . '.pdf';
                }
                
                // Actualizar DB con paths
                $this->db->where('id', $factura_id);
                $this->db->update('facturas', [
                    'pdf_path' => $pdf_path_db,
                    'xml_path' => $xml_path_db
                ]);
            }

            echo json_encode([
                'status' => 'success', 
                'message' => 'Factura creada existosamente. UUID: ' . $resultado['uuid'],
                'uuid' => $resultado['uuid'],
                'pdf_url' => $pdf_path_db ? base_url($pdf_path_db) : null,
                'xml_url' => $xml_path_db ? base_url($xml_path_db) : null
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => isset($resultado['message']) ? $resultado['message'] : 'Error desconocido', 'error' => isset($resultado['error']) ? $resultado['error'] : '']);
        }
    }
    public function get_details($id) {
        $factura = $this->db->get_where('facturas', ['id' => $id])->row_array();
        
        if(!$factura) {
             echo json_encode(['error' => true, 'message' => 'Factura no encontrada']);
             return;
        }

        // Devolvemos lo necesario para mostrar en el modal
        echo json_encode([
            'success' => true,
            'factura' => [
                'id' => $factura['id'],
                'folio_fiscal' => $factura['folio_fiscal'],
                'serie' => $factura['serie'],
                'folio' => $factura['folio'],
                'fecha_emision' => date('d/m/Y H:i', strtotime($factura['fecha_emision'])),
                'rfc' => $factura['rfc'],
                'razon_social' => $factura['razon_social'],
                'regimen_fiscal' => $factura['regimen_fiscal'],
                'uso_cfdi' => $factura['uso_cfdi'],
                'codigo_postal' => $factura['codigo_postal'],
                'subtotal' => number_format($factura['subtotal'], 2),
                'iva' => number_format($factura['iva'], 2),
                'total' => number_format($factura['total'], 2),
                'estatus' => $factura['estatus'],
                'pdf_url' => base_url('facturacion/Facturas/descargar/' . $factura['id'] . '/pdf'),
                'xml_url' => base_url('facturacion/Facturas/descargar/' . $factura['id'] . '/xml')
            ]
        ]);
    }

    /**
     * Descargar XML o PDF (Smart Download)
     */
    public function descargar($id, $tipo) {
        $factura = $this->db->get_where('facturas', ['id' => $id])->row_array();
        if (!$factura) show_404();

        // 1. Determinar path esperado segun DB
        //     Si ya tiene path guardado, intentamos usar ese. 
        //     Si no tiene, construiremos uno nuevo.
        $path_db = ($tipo == 'xml') ? $factura['xml_path'] : $factura['pdf_path'];
        $full_path = $path_db ? FCPATH . $path_db : null;

        // 2. Verificar existencia local
        if ($full_path && file_exists($full_path)) {
            $this->load->helper('download');
            $data = file_get_contents($full_path);
            $name = basename($full_path);
            force_download($name, $data);
        } else {
            // 3. NO EXISTE LOCALMENTE -> RECUPERAR DE API
            $token = $this->token_model->get_token();
            if (!$token || !$token->access_token) {
                show_error('No hay token de sesión con Facture App para recuperar el archivo. Conéctate nuevamente.');
            }

            // Llamada a la API para recuperar
            $archivos = $this->factureapp->descargar_xml_pdf($factura['folio_fiscal'], $token->access_token);

            if (isset($archivos['xml']) && isset($archivos['pdf'])) {
                // ...
            } else {
                // Manejo de errores más específico
                $error_msg = 'No se pudo recuperar el archivo.';
                if(isset($archivos['message'])) $error_msg .= ' ' . $archivos['message'];
                if(isset($archivos['error'])) $error_msg .= ' ' . $archivos['error'];
                
                show_error($error_msg);
            }
        }
    }

    /**
     * Sincronizar Facturas (Validar Estatus)
     */
    public function sincronizar() {
        if (!$this->input->is_ajax_request()) show_404();

        $token = $this->token_model->get_token();
        if (!$token || !$token->access_token) {
            echo json_encode(['status' => 'error', 'message' => 'No hay conexión con Facture App']);
            return;
        }

        // Obtener todas las facturas 'Emitida'
        $facturas = $this->db->select('id, folio_fiscal')
                             ->where('estatus', 'Emitida')
                             ->where('folio_fiscal !=', '')
                             ->get('facturas')
                             ->result_array();

        if (empty($facturas)) {
            echo json_encode(['status' => 'success', 'message' => 'No hay facturas emitidas para verificar.']);
            return;
        }

        $cambios = 0;
        $detalles = [];

        // Chunkear de 10 en 10 para no saturar
        $chunks = array_chunk($facturas, 10);

        foreach ($chunks as $chunk) {
            $uuids = array_column($chunk, 'folio_fiscal');
            
            // Llamar a batch api check
            $response = $this->factureapp->verificar_estado($uuids, $token->access_token);
            
            // La respuesta PUEDE ser un item unico (si enviamos 1) o un array de items
            // O un error. Ajustamos parsing.
            
            // Normalizar a lista de items
            $items = [];
            if(isset($response['result']['items'])) {
                $items = $response['result']['items'];
            } elseif (isset($response['entity']['data']['comprobantes'])) { // Estructura alternativa posible
                 $items = $response['entity']['data']['comprobantes'];
            } elseif(isset($response['message']) && strpos($response['message'], 'No existe') !== false) {
                 // Si el root es un error de "No existe", asumimos que todos fallaron?
                 // Ojo: si enviamos batch, la API deberia devolver lista.
                 // Si devuelve error global, es raro.
            }

            // Mapear resultados por UUID
            $map_resultados = [];
            foreach($items as $item) {
                 if(isset($item['requestUuid'])) {
                     $map_resultados[$item['requestUuid']] = $item; // { requestUuid, succeed, message, error ... }
                 }
            }

            // Verificar cada factura del chunk
            foreach ($chunk as $f) {
                $uuid = $f['folio_fiscal'];
                
                // Si el UUID NO esta en la respuesta o tiene error "no existe"
                if (isset($map_resultados[$uuid])) {
                    $res = $map_resultados[$uuid];
                    
                    // Si dice "Comprobante no existe"
                    if ( (isset($res['message']) && stripos($res['message'], 'no existe') !== false) ||
                         (isset($res['error']) && stripos($res['error'], 'no existe') !== false) ) {
                             
                        // Actualizar a Cancelada (o 'No Encontrada')
                        $this->db->where('id', $f['id'])->update('facturas', ['estatus' => 'Cancelada']);
                        $cambios++;
                        $detalles[] = "Factura {$uuid} marcada como Cancelada (No existe en SAT/API).";
                    }
                    // Si trae estatus explicito (si la API lo devuelve), lo usariamos.
                    // Por ahora solo detectamos "No existe".
                } else {
                    // Si no vino en la respuesta, quizas fallo el request completo o es un caso raro.
                    // No hacemos nada por seguridad.
                }
            }
        }

        echo json_encode([
            'status' => 'success', 
            'message' => "Sincronización completada. $cambios facturas actualizadas.",
            'detalles' => $detalles
        ]);
    }

    /**
     * Importar Facturas desde Facture App
     */
    public function importar_facturas() {
        if (!$this->input->is_ajax_request()) show_404();

        $token = $this->token_model->get_token();
        if (!$token || !$token->access_token) {
            echo json_encode(['status' => 'error', 'message' => 'No hay conexión con Facture App']);
            return;
        }

        // Obtener facturas de la API (últimos 100, sin filtro para traer todas)
        $response = $this->factureapp->listar_facturas($token->access_token, 0, 100, null);
        
        if (!isset($response['succeed']) || !$response['succeed']) {
            echo json_encode(['status' => 'error', 'message' => 'Error al obtener facturas de la API']);
            return;
        }

        $facturas_api = $response['pagination']['items'] ?? [];
        
        if (empty($facturas_api)) {
            echo json_encode(['status' => 'success', 'message' => 'No hay facturas en Facture App para importar.']);
            return;
        }

        // Obtener UUIDs locales existentes
        $uuids_locales = $this->db->select('folio_fiscal')
                                   ->where('folio_fiscal !=', '')
                                   ->get('facturas')
                                   ->result_array();
        $uuids_locales = array_column($uuids_locales, 'folio_fiscal');

        $importadas = 0;
        $detalles = [];

        foreach ($facturas_api as $factura_api) {
            $uuid = $factura_api['uuid'];
            
            // Si ya existe localmente, skip
            if (in_array($uuid, $uuids_locales)) {
                continue;
            }

            // Descargar XML y PDF
            $archivos = $this->factureapp->descargar_xml_pdf($uuid, $token->access_token);
            
            if (!isset($archivos['xml']) || !isset($archivos['pdf'])) {
                $detalles[] = "No se pudo descargar archivos para UUID: $uuid";
                continue;
            }

            // Preparar datos para insertar
            $fecha_timestamp = $factura_api['fecha'] / 1000; // Convertir de milisegundos
            $fecha_emision = date('Y-m-d H:i:s', $fecha_timestamp);
            $anio = date('Y', $fecha_timestamp);
            $mes = date('m', $fecha_timestamp);

            // Crear directorio si no existe
            $path_base = FCPATH . 'assets/facturas/' . $anio . '/' . $mes . '/';
            if (!is_dir($path_base)) {
                mkdir($path_base, 0755, true);
            }

            // Guardar archivos
            $xml_decoded = base64_decode($archivos['xml']);
            $pdf_decoded = base64_decode($archivos['pdf']);
            
            $xml_path_full = $path_base . $uuid . '.xml';
            $pdf_path_full = $path_base . $uuid . '.pdf';
            
            file_put_contents($xml_path_full, $xml_decoded);
            file_put_contents($pdf_path_full, $pdf_decoded);

            $xml_path_db = 'assets/facturas/' . $anio . '/' . $mes . '/' . $uuid . '.xml';
            $pdf_path_db = 'assets/facturas/' . $anio . '/' . $mes . '/' . $uuid . '.pdf';

            // Insertar en BD
            // Insertar en BD
            // Mapeo corregido segun JSON real:
            // serie -> miserie
            // folio -> mifolio
            // rfc -> tercero.rfc
            // nombre -> tercero.nombre
            
            $rfc_receptor = isset($factura_api['tercero']['rfc']) ? $factura_api['tercero']['rfc'] : '';
            $nombre_receptor = isset($factura_api['tercero']['nombre']) ? $factura_api['tercero']['nombre'] : '';
            
            $estatus = 'Emitida';
            if (!empty($factura_api['cancelada']) && $factura_api['cancelada'] === true) {
                $estatus = 'Cancelada';
            }

            $data_insert = [
                'folio_fiscal' => $uuid,
                'serie' => $factura_api['miserie'] ?? '',
                'folio' => $factura_api['mifolio'] ?? '',
                'fecha_emision' => $fecha_emision,
                'rfc' => $rfc_receptor,
                'razon_social' => $nombre_receptor,
                'subtotal' => $factura_api['subtotal'] ?? 0,
                'iva' => $factura_api['impuestosTrasladados'] ?? 0,
                'total' => $factura_api['total'] ?? 0,
                'estatus' => $estatus,
                'xml_path' => $xml_path_db,
                'pdf_path' => $pdf_path_db,
                'regimen_fiscal' => $factura_api['tercero']['regimen'] ?? '',
                'uso_cfdi' => $factura_api['tercero']['usoCFDI'] ?? '',
                'codigo_postal' => $factura_api['tercero']['direccion']['cp'] ?? '' // Asumido, si no existe quedara vacio
            ];

            $this->db->insert('facturas', $data_insert);
            $importadas++;
            $detalles[] = "Importada: {$factura_api['serie']}-{$factura_api['folio']} (UUID: $uuid)";
        }

        echo json_encode([
            'status' => 'success',
            'message' => "Importación completada. $importadas facturas importadas.",
            'detalles' => $detalles
        ]);
    }





}