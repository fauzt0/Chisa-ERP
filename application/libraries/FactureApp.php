<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FactureApp {

    protected $CI;
    protected $redirect_uri;
    /* Datos para produccion*/
    /*
    protected $client_id = 'mv6uSwgKrt4h7M4c7l0B';
    protected $client_secret = 'u6pHF5ftuOVIQzCh309fdVD6Vn2xpNv4';    
    protected $auth_url = 'https://app.micontador.mx/ws/login.jsp';
    protected $token_url = 'https://app.micontador.mx/api/authorize';
    protected $timbrado_url = 'https://app.micontador.mx/api/timbrado/json';
    */
    /* Datos para pruebas */
    protected $client_id = 'UrBzgu6LQzOEsX0ddS1r';
    protected $client_secret = 'VBW726kPiPx4TsEGeYJ4SCFsSVQfwtlK';    
    protected $auth_url = 'https://app.facture.com.mx/ws/login.jsp';
    protected $token_url = 'https://app.facture.com.mx/api/authorize';
    protected $timbrado_url = 'https://app.facture.com.mx/api/timbrado/json';
    

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Facturacion/FacturaApiTokenModel', 'token_model');
        // Ajustar redirect_uri a la ruta del controlador callback
        $this->redirect_uri = base_url('facturacion/Facturas/callback');
    }

    /**
     * Generar URL para login de usuario
     */
    public function get_login_url() {
        $params = [
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'scope' => 'sucursal,facturacion,concepto,timbrado,cancelacion' // Scope completo necesario
        ];
        return $this->auth_url . '?' . http_build_query($params);
    }

    /**
     * Intercambiar Authorization Code por Access Token
     */
    public function authorize($code) {
        $params = [
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_uri,
            'code' => $code,
            'scope' => 'sucursal,facturacion,concepto,timbrado,cancelacion',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];

        return $this->_request('POST', $this->token_url, $params, true);
    }

    /**
     * Obtener sucursales
     */
    public function get_sucursales($token, $offset = 0, $size = 10) {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ];
        
        // URL con parámetros de paginación según documentación
        //$url = 'https://app.micontador.mx/api/sucursal/find?offset=' . $offset . '&size=' . $size; // Produccion
        $url = 'https://app.facture.com.mx/api/sucursal/find?offset=' . $offset . '&size=' . $size; // Pruebas
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        // Log para debug
        if ($http_code !== 200) {
            log_message('error', 'Sucursales API Error (' . $http_code . '): ' . $response);
        }
        
        return $result;
    }

    /**
     * Obtener perfil del usuario (para datos fiscales de emisor)
     */
    public function get_perfil($token) {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ];
        
        // Probamos endpoint estándar de perfil
        $url = 'https://app.facture.com.mx/api/usuario/perfil'; // Pruebas
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result;
    }

    /**
     * Timbrar Factura (JSON/XML)
     */
    public function timbrar_json($data, $token) {
        // Extraer sucursal_id de los datos si existe (buscamos en miniscula o mayuscula por si acaso)
        $sucursal_id = null;
        if(isset($data['sucursal']['id'])) $sucursal_id = $data['sucursal']['id'];
        elseif(isset($data['Sucursal']['id'])) $sucursal_id = $data['Sucursal']['id'];
        
        // El endpoint api/timbrado/json espera una estructura especifica:
        // { "entity": { "data": { "comprobantes": [ { "requestUuid": "...", "encode": "JSON_BASE64" } ], "sucursal": { "id": ... } } } }
        
        // 1. Generar un UUID para el request
        $requestUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        // 2. Codificar la factura envíada en Base64 (JSON de la factura)
        // Limpiamos 'sucursal' del payload de factura, ya que solo debe ir en el wrapper y no en el XML 3.3
        if(isset($data['sucursal'])) unset($data['sucursal']);
        if(isset($data['Sucursal'])) unset($data['Sucursal']);
        
        $invoiceBase64 = base64_encode(json_encode($data));

        // 3. Construir el payload final envuelto
        $payload = [
            'entity' => [
                'data' => [
                    'comprobantes' => [
                        [
                            'requestUuid' => $requestUuid,
                            'encode' => $invoiceBase64
                        ]
                    ],
                    'sucursal' => [
                        'id' => (int)$sucursal_id
                    ]
                ]
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // Usamos el endpoint definido en la propiedad (que ya incluye /json)
        $response = $this->_request('POST', $this->timbrado_url, $payload, false, $headers);
        
        // La API devuelve una lista en result/items. Vamos a "aplanar" la respuesta para el controlador.
        if (isset($response['succeed']) && $response['succeed'] == true && isset($response['result']['items'][0])) {
            $item = $response['result']['items'][0];
            return $item; // Contiene succeed, uuid, message, etc.
        }

        return $response;
    }
    /**
     * Descargar XML y PDF de una factura
     */
    public function descargar_xml_pdf($uuid, $token) {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $payload = [
            'entity' => [
                'data' => [
                    'comprobantes' => [
                        [
                            'requestUuid' => $uuid // En recuperar, requestUuid es el folio fiscal/uuid a buscar
                        ]
                    ]
                ]
            ]
        ];
        
        // Endpoint: api/facturacion/recuperar
        // URL de producción: https://app.micontador.mx/api/facturacion/recuperar
        // URL de pruebas: https://app.facture.com.mx/api/facturacion/recuperar
        $url = 'https://app.facture.com.mx/api/facturacion/recuperar'; 
        
        $response = $this->_request('POST', $url, $payload, false, $headers);

        // La respuesta suele venir en result->data->comprobantes[0]
        // O entity->data->comprobantes[0]
        // Vamos a buscar la estructura devuelta
        
        if (isset($response['entity']['data']['comprobantes'][0])) {
            return $response['entity']['data']['comprobantes'][0];
        } elseif (isset($response['result']['data']['comprobantes'][0])) {
             return $response['result']['data']['comprobantes'][0];
        } elseif (isset($response['data']['comprobantes'][0])) {
             return $response['data']['comprobantes'][0];
        } elseif (isset($response['result']['items'][0])) {
             // Caso de error o estructura diferente (como el que pegó el usuaurio)
             return $response['result']['items'][0];
        }
        
        // Si no encontramos la estructura esperada, retornamos el response crudo para depuración
        log_message('error', 'FactureApp Recuperar: Estructura no reconocida. ' . json_encode($response));
        return $this->_request('POST', $url, $payload, false, $headers);
    }

    /**
     * Verificar estado de múltiples UUIDs
     */
    public function verificar_estado($uuids, $token) {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $comprobantes = [];
        foreach($uuids as $uuid) {
            $comprobantes[] = ['requestUuid' => $uuid];
        }

        $payload = [
            'entity' => [
                'data' => [
                    'comprobantes' => $comprobantes
                ]
            ]
        ];
        
        $url = 'https://app.facture.com.mx/api/facturacion/recuperar'; 
        
        return $this->_request('POST', $url, $payload, false, $headers);
    }

    /**
     * Listar facturas emitidas
     */
    public function listar_facturas($token, $offset = 0, $size = 100, $filter = null) {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ];
        
        // URL base
        $url = 'https://app.facture.com.mx/api/facturacion/find?offset=' . $offset . '&size=' . $size;
        
        // Agregar filtro si existe (ej: "cancelada:eq!true" para solo activas)
        if ($filter) {
            $url .= '&filter=' . urlencode($filter);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($http_code !== 200) {
            log_message('error', 'FactureApp List Error (' . $http_code . '): ' . $response);
        }
        
        return $result;
    }

    private function _request($method, $url, $data = [], $is_form = false, $custom_headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = $custom_headers;

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($is_form) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                if (empty($custom_headers)) {
                     $headers[] = 'Content-Type: application/json';
                     $headers[] = 'Accept: application/json';
                }
            }
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($http_code !== 200) {
            log_message('error', 'FactureApp API Error (' . $http_code . '): ' . $response);
            if ($result) return $result;
            return ['error' => true, 'message' => 'Error en API FactureApp: ' . $http_code, 'raw' => $response];
        }

        return $result;
    }

    public function save_token_from_response($response, $user_id = null) {
        if (isset($response['access_token'])) {
            $data = [
                'user_id' => $user_id,
                'provider' => 'facture_app',
                'access_token' => $response['access_token'],
                'refresh_token' => isset($response['refresh_token']) ? $response['refresh_token'] : '',
                'expires_in' => isset($response['expires_in']) ? $response['expires_in'] : 3600,
                'scope' => isset($response['scope']) ? $response['scope'] : '',
                'token_type' => isset($response['token_type']) ? $response['token_type'] : 'Bearer'
            ];
            return $this->CI->token_model->save_token($data);
        }
        return false;
    }
}
