<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiReloj - Controlador API para comunicación con el Proxy Local ZKTeco
 * 
 * Este controlador expone endpoints para que el Proxy Local (script en la planta)
 * pueda sincronizar asistencias y gestionar comandos del reloj checador.
 * 
 * SEGURIDAD:
 * - Autenticación mediante header X-API-Key validado contra reloj_dispositivos
 * - No extiende MY_Controller (no requiere sesión web)
 * - Los métodos CRON se protegerán con is_cli() en FASE 3
 * 
 * @package ChisaERP
 * @subpackage ApiReloj
 * @author Chisa Recubrimientos
 * @version 1.0
 */
class ApiReloj extends CI_Controller {

    /**
     * Token de API extraído del header X-API-Key
     * @var string|null
     */
    private $api_token = null;

    /**
     * Datos del dispositivo autenticado
     * @var object|null
     */
    private $dispositivo = null;

    public function __construct()
    {
        parent::__construct();

        $method = $this->router->fetch_method();

        // Monitor HTML y endpoint de prueba: sin BD ni autenticación API
        if (in_array($method, ['monitor', 'ver_log_reloj', 'sync_asistencias_debug'], true)) {
            if ($method !== 'monitor' && $method !== 'ver_log_reloj') {
                header('Content-Type: application/json; charset=utf-8');
            }
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        $this->load->model('Reloj/RelojModel');
        $this->_autenticar();
    }

    // ========================================================================
    // ENDPOINTS PÚBLICOS (Proxy Local = Cartero Ciego)
    // ========================================================================

    /**
     * POST /api/ApiReloj/sync_asistencias
     *
     * Recibe RAW DATA del reloj ZKTeco a través del Proxy Local.
     * El Proxy Local NO parsea nada, solo reenvía el texto plano.
     *
     * Payload esperado (JSON):
     * {
     *   "sn":       "UDP3252700203",
     *   "table":    "ATTLOG",
     *   "raw_data": "1\t2026-05-29 15:13:37\t255\t15\n2\t2026-05-29 14:58:09\t255\t1"
     * }
     *
     * El ERP parsea el raw_data usando explode() y preg_split():
     *   Col0: usuario_id | Col1: Fecha | Col2: Hora | Col3: Ignorar | Col4: Método
     */
    public function sync_asistencias()
    {
        // Solo aceptar POST
        if ($this->input->method() !== 'post') {
            $this->_responder(405, 'error', 'Método no permitido. Use POST.');
            return;
        }

        // Obtener payload JSON
        $payload = $this->_obtener_payload_json();
        $this->_append_debug_log(is_array($payload) ? $payload : []);

        if (!$payload) {
            $this->_responder(400, 'error', 'Payload inválido. No se pudo decodificar el JSON.');
            return;
        }

        // Validar campos requeridos del nuevo formato
        if (!isset($payload['raw_data']) || !isset($payload['table'])) {
            $this->_responder(400, 'error', 'Payload inválido. Se espera: {"sn":"...","table":"...","raw_data":"..."}');
            return;
        }

        $sn       = isset($payload['sn']) ? trim($payload['sn']) : $this->dispositivo->sn;
        $table    = trim($payload['table']);
        $raw_data = $payload['raw_data'];

        // Validar que el SN del payload coincida con el dispositivo autenticado
        if ($sn !== $this->dispositivo->sn) {
            $this->_responder(403, 'error', 'El SN del payload no coincide con el dispositivo autenticado.');
            return;
        }

        // Enrutar según el tipo de tabla ZKTeco
        switch ($table) {
            case 'ATTLOG':
                $resultado = $this->RelojModel->procesar_raw_data_attlog($raw_data, $sn);
                break;

            default:
                $this->_responder(501, 'error', "Tipo de tabla '{$table}' no soportado todavía.");
                return;
        }

        // Registrar en bitácora de sincronización
        $this->RelojModel->registrar_sync_log(
            $sn,
            'asistencias',
            "Table:{$table}, {$resultado['insertadas']} insertadas, {$resultado['duplicados']} duplicadas, {$resultado['errores']} errores",
            $resultado['insertadas'] + $resultado['duplicados'] + $resultado['errores']
        );

        $this->_responder(200, 'success', 'Datos del reloj procesados correctamente', [
            'tabla'      => $table,
            'insertadas' => $resultado['insertadas'],
            'duplicados' => $resultado['duplicados'],
            'errores'    => $resultado['errores']
        ]);
    }

    /**
     * GET /api/ApiReloj/comandos_pendientes/(:sn)
     * 
     * Obtiene los comandos pendientes para un dispositivo específico
     * Los marca como "enviado" automáticamente al ser consultados
     * 
     * @param string $sn Número de serie del dispositivo
     */
    public function comandos_pendientes($sn = null)
    {
        // Solo aceptar GET
        if ($this->input->method() !== 'get') {
            $this->_responder(405, 'error', 'Método no permitido. Use GET.');
            return;
        }

        // Validar SN
        if (empty($sn)) {
            $this->_responder(400, 'error', 'Se requiere el número de serie (SN) del dispositivo.');
            return;
        }

        // Obtener comandos pendientes y marcarlos como enviados
        $comandos = $this->RelojModel->obtener_y_marcar_comandos($sn);

        if ($comandos === false) {
            $this->_responder(500, 'error', 'Error al consultar comandos pendientes.');
            return;
        }

        // Registrar en bitácora
        $this->RelojModel->registrar_sync_log(
            $sn,
            'comandos',
            count($comandos) . ' comandos pendientes enviados',
            count($comandos)
        );

        $this->_responder(200, 'success', 'Comandos pendientes recuperados', [
            'comandos' => $comandos,
            'total'    => count($comandos)
        ]);
    }

    /**
     * POST /api/ApiReloj/comando_resultado
     * 
     * Recibe el resultado de la ejecución de un comando en el reloj
     * 
     * Payload esperado (JSON):
     * {
     *   "comando_id": 1,
     *   "return_code": 0,
     *   "respuesta": "OK"
     * }
     */
    public function comando_resultado()
    {
        // Solo aceptar POST
        if ($this->input->method() !== 'post') {
            $this->_responder(405, 'error', 'Método no permitido. Use POST.');
            return;
        }

        $payload = $this->_obtener_payload_json();
        if (!$payload || !isset($payload['comando_id'])) {
            $this->_responder(400, 'error', 'Payload inválido. Se requiere "comando_id".');
            return;
        }

        $comando_id  = (int)$payload['comando_id'];
        $return_code = array_key_exists('return_code', $payload) ? $payload['return_code'] : null;
        $respuesta   = isset($payload['respuesta']) ? trim($payload['respuesta']) : null;
        $estado_proxy = isset($payload['estado']) ? trim($payload['estado']) : null;

        $nuevo_estado = $this->RelojModel->interpretar_estado_resultado_comando(
            $return_code,
            $respuesta,
            $estado_proxy
        );

        $actualizado = $this->RelojModel->actualizar_comando_resultado(
            $comando_id,
            $nuevo_estado,
            $respuesta
        );

        if (!$actualizado) {
            $this->_responder(404, 'error', 'Comando no encontrado o ya procesado.');
            return;
        }

        // Registrar en bitácora
        $this->RelojModel->registrar_sync_log(
            $this->dispositivo->sn,
            'resultado',
            "Comando #{$comando_id}: {$nuevo_estado}",
            1
        );

        $this->_responder(200, 'success', 'Resultado de comando registrado', [
            'comando_id'  => $comando_id,
            'estado'      => $nuevo_estado
        ]);
    }

    /**
     * POST /api/reloj/sync_asistencias_debug
     *
     * Monitor temporal: solo escribe el payload en archivo de texto (sin BD).
     * Apunta el proxy aquí mientras pruebas; producción sigue en sync_asistencias.
     */
    public function sync_asistencias_debug()
    {
        if ($this->input->method() !== 'post') {
            $this->_responder(405, 'error', 'Método no permitido. Use POST.');
            return;
        }

        $raw_body = file_get_contents('php://input');
        $payload = null;

        if ($raw_body !== false && $raw_body !== '') {
            $payload = json_decode($raw_body, true);
        }

        if (!is_array($payload)) {
            $post_data = $this->input->post();
            $payload = is_array($post_data) ? $post_data : [];
        }

        $this->_append_debug_log($payload, $raw_body !== false ? $raw_body : '');

        $this->_responder(200, 'success', 'Payload registrado en debug_reloj.txt', [
            'logged' => true,
            'sn'     => isset($payload['sn']) ? $payload['sn'] : null,
            'table'  => isset($payload['table']) ? $payload['table'] : null,
        ]);
    }

    /**
     * GET /api/reloj/monitor
     *
     * Monitor temporal: muestra debug_reloj.txt y permite limpiarlo.
     */
    public function monitor()
    {
        $this->ver_log_reloj();
    }

    /**
     * GET /api/reloj/monitor (alias ver_log_reloj)
     */
    public function ver_log_reloj()
    {
        $log_file = $this->_debug_log_path();

        if ($this->input->method() === 'post' && $this->input->post('clear_log')) {
            if (is_file($log_file)) {
                unlink($log_file);
            }
            redirect('api/reloj/monitor');
            return;
        }

        $contenido = is_file($log_file) ? file_get_contents($log_file) : '';

        header('Content-Type: text/html; charset=utf-8');

        echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">';
        echo '<title>Monitor debug Reloj ZKTeco</title>';
        echo '<style>
            body { background:#0d1117; color:#3fb950; font-family:Consolas,Monaco,monospace; margin:20px; }
            h1 { color:#58a6ff; font-size:1.2rem; }
            pre { background:#161b22; border:1px solid #30363d; padding:16px; white-space:pre-wrap; word-break:break-all; tab-size:4; }
            .meta { color:#8b949e; font-size:0.9rem; margin-bottom:16px; }
            form { margin:16px 0; }
            button { background:#238636; color:#fff; border:none; padding:10px 16px; cursor:pointer; border-radius:6px; }
            button:hover { background:#2ea043; }
            a { color:#58a6ff; }
        </style></head><body>';
        echo '<h1>Monitor debug — Proxy ZKTeco → ERP</h1>';
        echo '<p class="meta">Archivo: <code>' . htmlspecialchars($log_file) . '</code></p>';
        echo '<p class="meta">POST de prueba (sin BD): <code>' . htmlspecialchars(base_url('api/reloj/sync_asistencias_debug')) . '</code></p>';
        echo '<form method="post" action="' . htmlspecialchars(base_url('api/reloj/monitor')) . '">';
        echo '<input type="hidden" name="clear_log" value="1">';
        echo '<button type="submit">Limpiar log</button></form>';
        echo '<pre>';
        echo $contenido !== '' ? htmlspecialchars($contenido) : '(vacío — aún no hay peticiones registradas)';
        echo '</pre>';
        echo '<p class="meta"><a href="' . htmlspecialchars(base_url('api/reloj/monitor')) . '">Recargar</a></p>';
        echo '</body></html>';
        exit;
    }

    /**
     * GET /api/ApiReloj/status
     * 
     * Endpoint de verificación de conectividad
     * El Proxy Local puede hacer ping a este endpoint para validar comunicación
     */
    public function status()
    {
        $this->_responder(200, 'success', 'API del Reloj Checador operativa', [
            'version'     => '1.0.0',
            'dispositivo' => $this->dispositivo->alias ?? $this->dispositivo->sn,
            'server_time' => date('Y-m-d H:i:s'),
            'timezone'    => date_default_timezone_get()
        ]);
    }

    // ========================================================================
    // MÉTODOS PRIVADOS
    // ========================================================================

    /**
     * Ruta del archivo de diagnóstico (CI3: APPPATH/logs; compatible con WRITEPATH si existe)
     *
     * @return string
     */
    private function _debug_log_path()
    {
        $base = defined('WRITEPATH') ? WRITEPATH : APPPATH;
        return $base . 'logs/debug_reloj.txt';
    }

    /**
     * Append de payload recibido del proxy (sin usar base de datos)
     *
     * @param array       $payload  JSON decodificado
     * @param string|null $raw_body Body crudo opcional
     */
    private function _append_debug_log(array $payload, $raw_body = null)
    {
        $log_file = $this->_debug_log_path();
        $log_dir = dirname($log_file);

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $sn = isset($payload['sn']) ? (string)$payload['sn'] : '(sin sn)';
        $table = isset($payload['table']) ? (string)$payload['table'] : '(sin table)';
        $raw_data = isset($payload['raw_data']) ? (string)$payload['raw_data'] : '(sin raw_data)';

        $block = "\n" . str_repeat('=', 72) . "\n";
        $block .= date('Y-m-d H:i:s') . ' | IP: ' . $this->input->ip_address() . "\n";
        $block .= 'SN: ' . $sn . "\n";
        $block .= 'TABLE: ' . $table . "\n";
        $block .= "RAW_DATA:\n" . $raw_data . "\n";

        if ($raw_body !== null && $raw_body !== '') {
            $block .= "BODY_JSON:\n" . $raw_body . "\n";
        }

        $block .= str_repeat('-', 72) . "\n";

        file_put_contents($log_file, $block, FILE_APPEND | LOCK_EX);
    }

    /**
     * Autentica la petición mediante header X-API-Key
     * Valida el token contra la tabla reloj_dispositivos
     * 
     * @return void Responde con 401 si no está autenticado
     */
    private function _autenticar()
    {
        // Obtener token del header
        $this->api_token = $this->input->get_request_header('X-API-Key', true);
        
        // Fallback: permitir también por GET ?token= (solo para status)
        if (empty($this->api_token)) {
            $this->api_token = $this->input->get('token');
        }

        if (empty($this->api_token)) {
            $this->_responder(401, 'error', 'No autorizado. Se requiere header X-API-Key.');
            exit;
        }

        // Validar token contra base de datos
        $this->dispositivo = $this->RelojModel->validar_token($this->api_token);

        if (!$this->dispositivo) {
            $this->_responder(401, 'error', 'Token inválido o dispositivo inactivo.');
            exit;
        }

        // Actualizar última conexión del dispositivo
        $this->RelojModel->actualizar_conexion($this->dispositivo->id);
    }

    /**
     * Obtiene y decodifica el payload JSON de la petición
     * Soporta: raw body JSON, form-data con campo json_data, y POST tradicional
     * 
     * @return array|null Array con datos o null si no se pudo decodificar
     */
    private function _obtener_payload_json()
    {
        $raw_body = file_get_contents('php://input');
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

        // Modo 1: JSON en raw body
        if (!empty($raw_body)) {
            $json_data = json_decode($raw_body, true);
            if ($json_data !== null) {
                return $json_data;
            }
        }

        // Modo 2: Campo json_data en form-data
        $post_data = $this->input->post();
        if (!empty($post_data)) {
            if (isset($post_data['json_data'])) {
                $json_data = json_decode($post_data['json_data'], true);
                if ($json_data !== null) {
                    return $json_data;
                }
            }
            
            // Modo 3: POST tradicional con campos sueltos
            // Si viene como array plano de asistencias
            if (isset($post_data['usuario_id'])) {
                return [
                    'asistencias' => [$post_data]
                ];
            }
            
            return $post_data;
        }

        return null;
    }

    /**
     * Genera una respuesta JSON estandarizada y la envía al cliente
     *
     * @param int    $http_code Código HTTP
     * @param string $status    Estado: success, error
     * @param string $message   Mensaje descriptivo
     * @param array  $data      Datos adicionales opcionales
     */
    private function _responder($http_code, $status, $message, $data = [])
    {
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_status_header($http_code)
            ->set_output(json_encode([
                'status'  => $status,
                'message' => $message,
                'data'    => $data,
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        
        // Terminar ejecución después de enviar respuesta
        exit;
    }
}
