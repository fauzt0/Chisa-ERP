<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador para pruebas de recepción de datos del reloj checador ZKTeco MB10-VL
 * 
 * Este controlador recibe los datos que el reloj envía vía HTTP POST (push)
 * cuando está configurado en modo standalone con envío a URL.
 * 
 * Endpoints disponibles:
 *   GET  /Testzk          - Muestra el panel de prueba
 *   GET  /Testzk/push     - Misma función que el index
 *   POST /Testzk/push     - Recibe datos del reloj ZKTeco
 *   GET  /Testzk/log      - Muestra el historial de peticiones recibidas
 *   GET  /Testzk/clear_log - Limpia el archivo de log
 */
class Testzk extends CI_Controller {

    private $log_file;
    
    public function __construct()
    {
        parent::__construct();
        
        // Ruta donde se guardará el log de peticiones del reloj
        $this->log_file = APPPATH . 'logs/zkteco_push.log';
        
        // Asegurar que el directorio logs existe
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }

    /**
     * Página principal de prueba
     */
    public function index()
    {
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba ZKTeco MB10-VL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        code { background: #e8e8e8; padding: 2px 6px; border-radius: 3px; font-size: 14px; }
        pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .url { background: #dff0d8; padding: 10px; border-radius: 5px; font-size: 16px; font-weight: bold; }
        .info { background: #d9edf7; padding: 10px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .btn { display: inline-block; padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; }
        .btn-primary { background: #337ab7; color: #fff; }
        .btn-danger { background: #d9534f; color: #fff; }
        .btn-success { background: #5cb85c; color: #fff; }
    </style>
</head>
<body>
    <h1>🧪 Prueba de Recepción ZKTeco MB10-VL</h1>
    
    <div class="card">
        <h2>🌐 URL para configurar en el reloj</h2>
        <div class="url">' . site_url('Testzk/push') . '</div>
        <p class="info">⚠️ Asegúrate de que el reloj tenga acceso a Internet y pueda alcanzar esta URL.</p>
    </div>

    <div class="card">
        <h2>📡 Configuración por IP (si el reloj no acepta dominio)</h2>
        <table>
            <tr><th>Parámetro</th><th>Valor</th></tr>
            <tr><td><strong>IP del Servidor</strong></td><td><code>67.217.58.138</code></td></tr>
            <tr><td><strong>Puerto</strong></td><td><code>80</code> (HTTP) o <code>443</code> (HTTPS)</td></tr>
            <tr><td><strong>URL / Ruta</strong></td><td><code>/index.php/Testzk/push</code></td></tr>
            <tr><td><strong>Protocolo</strong></td><td><code>HTTP</code> (recomendado) o <code>HTTPS</code></td></tr>
        </table>
        <p class="info">💡 Si el reloj tiene firmware antiguo, <strong>usa HTTP puerto 80</strong> — muchos modelos no soportan HTTPS correctamente.</p>
    </div>

    <div class="card">
        <h2>Opciones</h2>
        <a class="btn btn-primary" href="' . site_url('Testzk/log') . '">📋 Ver Log de peticiones</a>
        <a class="btn btn-success" href="' . site_url('Testzk/simulate') . '">🧪 Simular envío del reloj</a>
        <a class="btn btn-danger" href="' . site_url('Testzk/clear_log') . '" onclick="return confirm(\'¿Borrar todo el historial?\')">🗑️ Limpiar Log</a>
    </div>

    <div class="card">
        <h2>Formulario de prueba manual</h2>
        <p>Usa este formulario para simular lo que enviaría el reloj:</p>
        <form method="post" action="' . site_url('Testzk/push') . '">
            <table>
                <tr><td><label>Datos JSON:</label></td></tr>
                <tr><td><textarea name="json_data" rows="10" cols="80" style="width:100%;font-family:monospace;">{
    "device_sn": "MB10VLXX0000",
    "device_alias": "Reloj Entrada",
    "push_mode": "attendance",
    "attendance": [
        {
            "employee_id": "001",
            "timestamp": "' . date('Y-m-d H:i:s') . '",
            "status": 0,
            "verify_type": 1
        }
    ]
}</textarea></td></tr>
                <tr><td><input type="submit" class="btn btn-primary" value="Enviar prueba"></td></tr>
            </table>
        </form>
    </div>

    <div class="card">
        <h2>Formato esperado del reloj (JSON)</h2>
        <p>El ZKTeco MB10-VL envía los datos en este formato vía POST:</p>
        <pre>{
    "device_sn": "NÚMERO_SERIE",
    "device_alias": "Nombre del Reloj",
    "push_mode": "attendance",
    "attendance": [
        {
            "employee_id": "001",
            "timestamp": "2026-05-29 08:30:00",
            "status": 0,
            "verify_type": 1
        }
    ]
}</pre>
    </div>

    <div class="card">
        <h2>También acepta parámetros individuales vía POST</h2>
        <p>Puedes enviar datos como formulario tradicional:</p>
        <form method="post" action="' . site_url('Testzk/push') . '">
            <table>
                <tr><td>employee_id:</td><td><input type="text" name="employee_id" value="001"></td></tr>
                <tr><td>timestamp:</td><td><input type="text" name="timestamp" value="' . date('Y-m-d H:i:s') . '"></td></tr>
                <tr><td>status:</td><td><input type="text" name="status" value="0"></td></tr>
                <tr><td>verify_type:</td><td><input type="text" name="verify_type" value="1"></td></tr>
                <tr><td>device_sn:</td><td><input type="text" name="device_sn" value="MB10VLXX0000"></td></tr>
                <tr><td colspan="2"><input type="submit" class="btn btn-primary" value="Enviar prueba"></td></tr>
            </table>
        </form>
    </div>
</body>
</html>';
    }

    /**
     * Endpoint principal para recibir datos del reloj ZKTeco
     * 
     * Acepta:
     *   - POST con body JSON (application/json)
     *   - POST con form-data tradicional
     *   - GET para mostrar panel informativo
     */
    public function push()
    {
        $method = $this->input->method();
        
        // Si es GET, mostrar la página de prueba
        if ($method === 'get') {
            return $this->index();
        }

        // ============================================================
        // RECEPCIÓN DE DATOS - SOLO POST
        // ============================================================
        
        // Intentar leer el body crudo primero (para JSON)
        $raw_body = file_get_contents('php://input');
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        $data = [];
        $source = '';

        // --- Modo 1: JSON en el body (formato nativo del ZKTeco) ---
        if (!empty($raw_body) && (strpos($content_type, 'application/json') !== false || $raw_body[0] === '{' || $raw_body[0] === '[')) {
            $json_data = json_decode($raw_body, true);
            if ($json_data !== null) {
                $data = $json_data;
                $source = 'JSON body (application/json)';
            }
        }
        
        // --- Modo 2: Parámetros POST tradicionales ---
        if (empty($data)) {
            $post_data = $this->input->post();
            if (!empty($post_data)) {
                // Si viene como json_data en un campo de formulario, intentar decodificarlo
                if (isset($post_data['json_data'])) {
                    $json_data = json_decode($post_data['json_data'], true);
                    if ($json_data !== null) {
                        $data = $json_data;
                        $source = 'JSON embedido en formulario (json_data)';
                    }
                } else {
                    $data = $post_data;
                    $source = 'POST form-data tradicional';
                }
            }
        }
        
        // --- Modo 3: Body crudo como XML o texto plano ---
        if (empty($data) && !empty($raw_body)) {
            $data = ['raw_body' => $raw_body];
            $source = 'Raw body (texto plano)';
        }

        // --- Registrar la petición recibida ---
        $log_entry = [
            'fecha_hora'   => date('Y-m-d H:i:s'),
            'ip_origen'    => $this->input->ip_address(),
            'method'       => $method,
            'content_type' => $content_type,
            'source'       => $source,
            'datos'        => $data
        ];
        
        $this->_write_log($log_entry);

        // ============================================================
        // PROCESAR LOS DATOS DE ASISTENCIA
        // ============================================================
        
        $respuesta = [
            'code'    => 200,
            'message' => 'success',
            'received' => date('Y-m-d H:i:s')
        ];

        // Si es una petición del reloj con formato JSON de asistencia
        if (isset($data['push_mode']) && $data['push_mode'] === 'attendance') {
            $attendance = isset($data['attendance']) ? $data['attendance'] : [];
            
            $respuesta['attendance_count'] = count($attendance);
            $respuesta['device_sn'] = isset($data['device_sn']) ? $data['device_sn'] : '';
            
            // Aquí puedes agregar lógica para guardar en BD
            // Por ahora solo registramos en el log
            foreach ($attendance as $i => $check) {
                $empleado = isset($check['employee_id']) ? $check['employee_id'] : 'N/A';
                $hora     = isset($check['timestamp']) ? $check['timestamp'] : 'N/A';
                log_message('info', "ZKTeco Push - Empleado: {$empleado}, Hora: {$hora}");
            }

        } elseif (isset($data['employee_id'])) {
            // Si vienen datos individuales (formulario tradicional)
            $respuesta['individual'] = [
                'employee_id' => $data['employee_id'] ?? 'N/A',
                'timestamp'   => $data['timestamp'] ?? 'N/A',
                'status'      => $data['status'] ?? 'N/A',
                'verify_type' => $data['verify_type'] ?? 'N/A'
            ];
        }

        // ============================================================
        // RESPUESTA AL RELOJ
        // ============================================================
        
        // El ZKTeco espera un código 200 y típicamente un JSON de respuesta
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Simula una petición del reloj ZKTeco (para pruebas internas)
     */
    public function simulate()
    {
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Simulación de envío del reloj</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; }
        .btn-primary { background: #337ab7; color: #fff; cursor: pointer; border: none; font-size: 14px; }
        .success { background: #dff0d8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f2dede; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🧪 Simulación de envío del reloj ZKTeco</h1>
    <div class="card">
        <p>Esta página simula lo que el reloj ZKTeco enviaría a la URL configurada.</p>
        <form id="simulateForm">
            <p><label>Número de registros a enviar:</label>
            <select name="count">
                <option value="1">1</option>
                <option value="3" selected>3</option>
                <option value="5">5</option>
                <option value="10">10</option>
            </select></p>
            <button type="button" class="btn btn-primary" onclick="simulate()">🚀 Enviar simulación al reloj</button>
        </form>
        <div id="result"></div>
    </div>

    <script>
    async function simulate() {
        const count = document.querySelector(\'[name="count"]\').value;
        const url = \'' . site_url('Testzk/push') . '\';
        const resultDiv = document.getElementById("result");
        
        const employees = ["001", "002", "003", "004", "005", "006", "007", "008", "009", "010"];
        const attendance = [];
        
        for (let i = 0; i < parseInt(count); i++) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - i * 5);
            const ts = now.toISOString().slice(0, 19).replace("T", " ");
            
            attendance.push({
                employee_id: employees[i % employees.length],
                timestamp: ts,
                status: 0,
                verify_type: i % 2 === 0 ? 1 : 2
            });
        }
        
        const payload = {
            device_sn: "MB10VL-TEST-001",
            device_alias: "Reloj Prueba",
            push_mode: "attendance",
            attendance: attendance
        };
        
        resultDiv.innerHTML = \'<p>⏳ Enviando...\</p>\';
        
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            
            const responseData = await response.json();
            
            resultDiv.innerHTML = \'<div class="success">✅ Petición enviada con éxito</div>\' +
                \'<h3>Datos enviados:</h3><pre>\' + JSON.stringify(payload, null, 2) + \'</pre>\' +
                \'<h3>Respuesta del servidor:</h3><pre>\' + JSON.stringify(responseData, null, 2) + \'</pre>\';
        } catch (err) {
            resultDiv.innerHTML = \'<div class="error">❌ Error: \' + err.message + \'</div>\';
        }
    }
    </script>
</body>
</html>';
    }

    /**
     * Muestra el historial de peticiones recibidas
     */
    public function log()
    {
        $lines = $this->_read_log();
        
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Log de peticiones ZKTeco</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .entry { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .entry:last-child { border-bottom: none; }
        .entry-header { font-weight: bold; color: #337ab7; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 12px; color: #fff; }
        .badge-json { background: #5cb85c; }
        .badge-form { background: #f0ad4e; }
        .badge-raw { background: #d9534f; }
        .btn { display: inline-block; padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; }
        .btn-primary { background: #337ab7; color: #fff; }
        .btn-danger { background: #d9534f; color: #fff; }
        .vacio { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>📋 Historial de peticiones del reloj ZKTeco</h1>
    <p>
        <a class="btn btn-primary" href="' . site_url('Testzk') . '">⬅ Volver</a>
        <a class="btn btn-danger" href="' . site_url('Testzk/clear_log') . '" onclick="return confirm(\'¿Borrar todo el historial?\')">🗑️ Limpiar Log</a>
    </p>
    <div class="card">';
        
        if (empty($lines)) {
            echo '<p class="vacio">No hay peticiones registradas aún. Configura tu reloj o usa el formulario de prueba.</p>';
        } else {
            // Mostrar en orden inverso (más recientes primero)
            $lines = array_reverse($lines);
            echo '<p>Total de peticiones recibidas: <strong>' . count($lines) . '</strong></p>';
            
            foreach ($lines as $idx => $entry) {
                $source = isset($entry['source']) ? $entry['source'] : 'Desconocido';
                $badge_class = 'badge-json';
                if (strpos($source, 'form-data') !== false) $badge_class = 'badge-form';
                if (strpos($source, 'Raw') !== false) $badge_class = 'badge-raw';
                
                echo '<div class="entry">';
                echo '<div class="entry-header">#' . ($idx + 1) . ' - ' . ($entry['fecha_hora'] ?? '?') . ' <span class="badge ' . $badge_class . '">' . htmlspecialchars($source) . '</span></div>';
                echo '<div><strong>IP:</strong> ' . ($entry['ip_origen'] ?? '?') . '</div>';
                echo '<pre>' . htmlspecialchars(json_encode($entry['datos'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
                echo '</div>';
            }
        }
        
    echo '</div>
</body>
</html>';
    }

    /**
     * Limpia el archivo de log
     */
    public function clear_log()
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        redirect('Testzk/log');
    }

    // ============================================================
    // Métodos privados
    // ============================================================

    /**
     * Escribe una entrada en el archivo de log
     */
    private function _write_log($entry)
    {
        $existing = $this->_read_log();
        $existing[] = $entry;
        
        // Mantener solo las últimas 500 entradas para no saturar
        if (count($existing) > 500) {
            $existing = array_slice($existing, -500);
        }
        
        $content = json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->log_file, $content, LOCK_EX);
    }

    /**
     * Lee el archivo de log y devuelve el array de entradas
     */
    private function _read_log()
    {
        if (!file_exists($this->log_file)) {
            return [];
        }
        
        $content = file_get_contents($this->log_file);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }
}
