<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RelojModel - Modelo para el módulo de Reloj Checador Biométrico
 * 
 * Gestiona:
 * - Dispositivos (reloj_dispositivos)
 * - Asistencias/Checadas (asistencias)
 * - Cola de comandos (reloj_comandos)
 * - Bitácora de sincronización (reloj_sync_log)
 * 
 * @package ChisaERP
 * @subpackage Reloj
 * @author Chisa Recubrimientos
 * @version 1.0
 */
class RelojModel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('RH/EmpleadoModel');
    }

    private function filtro_empleados_laboral_activo($campo = 'estatus')
    {
        $this->db->where_in($campo, EmpleadoModel::estatus_laborales_activos());
    }

    // ========================================================================
    // AUTENTICACIÓN DE DISPOSITIVOS
    // ========================================================================

    /**
     * Valida un token API contra la tabla reloj_dispositivos
     * 
     * @param string $token Token a validar
     * @return object|false Objeto del dispositivo o false si es inválido
     */
    public function validar_token($token)
    {
        return $this->db
            ->where('api_token', $token)
            ->where('activo', 1)
            ->get('reloj_dispositivos')
            ->row();
    }

    /**
     * Actualiza la última conexión de un dispositivo
     * 
     * @param int $dispositivo_id ID del dispositivo
     * @return bool
     */
    public function actualizar_conexion($dispositivo_id)
    {
        return $this->db
            ->where('id', $dispositivo_id)
            ->update('reloj_dispositivos', [
                'ultima_conexion' => date('Y-m-d H:i:s')
            ]);
    }

    // ========================================================================
    // ASISTENCIAS (CHECADAS)
    // ========================================================================

    /**
     * Busca un empleado del ERP por su PIN en el reloj
     * 
     * @param string $pin PIN del empleado en el reloj
     * @return object|null Empleado encontrado o null
     */
    public function buscar_empleado_por_pin($pin)
    {
        if (is_numeric($pin) && $this->db->field_exists('reloj_pin', 'empleados')) {
            $empleado = $this->db
                ->where('reloj_pin', (int)$pin)
                ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
                ->get('empleados')
                ->row();

            if ($empleado) {
                return $empleado;
            }
        }

        // Buscar por numero_empleado (código interno del ERP)
        $empleado = $this->db
            ->where('numero_empleado', $pin)
            ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
            ->get('empleados')
            ->row();

        if ($empleado) {
            return $empleado;
        }

        // Fallback: buscar por ID directo
        if (is_numeric($pin)) {
            return $this->db
                ->where('id', (int)$pin)
                ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
                ->get('empleados')
                ->row();
        }

        return null;
    }

    /**
     * Inserta una asistencia evitando duplicados
     * 
     * @param array $data Datos de la asistencia
     * @return string 'insertado', 'duplicado' o 'error'
     */
    public function insertar_asistencia($data)
    {
        // Verificar duplicado por usuario_id + fecha_hora
        $existe = $this->db
            ->where('usuario_id', $data['usuario_id'])
            ->where('fecha_hora', $data['fecha_hora'])
            ->get('asistencias')
            ->row();

        if ($existe) {
            return 'duplicado';
        }

        $insert = $this->db->insert('asistencias', [
            'usuario_id'     => $data['usuario_id'],
            'empleado_id'    => isset($data['empleado_id']) ? $data['empleado_id'] : null,
            'fecha_hora'     => $data['fecha_hora'],
            'metodo'         => $data['metodo'],
            'dispositivo_sn' => $data['dispositivo_sn'],
            'creado_el'      => date('Y-m-d H:i:s')
        ]);

        return $insert ? 'insertado' : 'error';
    }

    // ========================================================================
    // PARSEO DE RAW DATA ZKTeco (Proxy Local = Cartero Ciego)
    // ========================================================================

    /**
     * Procesa raw_data de la tabla ATTLOG del reloj ZKTeco.
     *
     * El Proxy Local envía el texto plano EXACTO que escupe el reloj.
     * El ERP es el responsable de parsear este texto.
     *
     * Formato esperado por línea (separado por tabs/espacios):
     *   Col0: usuario_id (PIN en el reloj)
     *   Col1: Fecha (YYYY-MM-DD)
     *   Col2: Hora (HH:MM:SS)
     *   Col3: Estado (se ignora)
     *   Col4: Método (15=Rostro, 1=Huella, 0=Password)
     *
     * @param string $raw_data      Texto plano con todas las líneas del reloj
     * @param string $dispositivo_sn SN del dispositivo que reporta
     * @return array Estadísticas de procesamiento
     */
    public function procesar_raw_data_attlog($raw_data, $dispositivo_sn)
    {
        $raw_data = trim($raw_data);
        if (empty($raw_data)) {
            return [
                'insertadas' => 0,
                'duplicados' => 0,
                'errores'    => 1,
                'detalles'   => ['raw_data vacío']
            ];
        }

        $lineas = explode("\n", $raw_data);
        $resultados = [
            'insertadas' => 0,
            'duplicados' => 0,
            'errores'    => 0,
            'detalles'   => []
        ];

        foreach ($lineas as $num_linea => $linea) {
            $linea = trim($linea);
            if (empty($linea)) {
                continue; // Saltar líneas vacías
            }

            // Dividir por tabs o espacios (ZKTeco usa tabuladores)
            $columnas = preg_split('/\s+/', $linea);

            // Validar que tenga al menos 5 columnas
            if (count($columnas) < 5) {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea " . ($num_linea + 1) . ": columnas insuficientes (" . count($columnas) . "/5)";
                continue;
            }

            $usuario_id = trim($columnas[0]);
            $fecha      = trim($columnas[1]);
            $hora       = trim($columnas[2]);
            // Col3 = Estado (se ignora intencionalmente)
            $metodo_raw = trim($columnas[4]);

            // Validar formato de fecha
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea " . ($num_linea + 1) . ": formato de fecha inválido '{$fecha}'";
                continue;
            }

            // Validar formato de hora
            if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea " . ($num_linea + 1) . ": formato de hora inválido '{$hora}'";
                continue;
            }

            // Convertir método a entero (15=Rostro, 1=Huella, 0=Password, default=1)
            $metodo = is_numeric($metodo_raw) ? (int)$metodo_raw : 1;

            $fecha_hora = $fecha . ' ' . $hora;

            $data = [
                'usuario_id'     => $usuario_id,
                'fecha_hora'     => $fecha_hora,
                'metodo'         => $metodo,
                'dispositivo_sn' => $dispositivo_sn,
            ];

            // Intentar vincular con empleado del ERP por numero_empleado
            $empleado = $this->buscar_empleado_por_pin($usuario_id);
            if ($empleado) {
                $data['empleado_id'] = $empleado->id;
            }

            $resultado = $this->insertar_asistencia($data);

            if ($resultado === 'insertado') {
                $resultados['insertadas']++;
            } elseif ($resultado === 'duplicado') {
                $resultados['duplicados']++;
            } else {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea " . ($num_linea + 1) . ": error al insertar asistencia";
            }
        }

        return $resultados;
    }

    /**
     * Obtiene asistencias por rango de fechas
     * 
     * @param string $fecha_inicio Fecha inicio (Y-m-d)
     * @param string $fecha_fin    Fecha fin (Y-m-d)
     * @param int|null $empleado_id Filtrar por empleado (opcional)
     * @return array
     */
    public function get_asistencias_rango($fecha_inicio, $fecha_fin, $empleado_id = null)
    {
        $this->db->select('asistencias.*, CONCAT(empleados.nombre, " ", empleados.apellido_paterno) as empleado_nombre');
        $this->db->from('asistencias');
        $this->db->join('empleados', 'empleados.id = asistencias.empleado_id', 'left');
        $this->db->where('asistencias.fecha_hora >=', $fecha_inicio . ' 00:00:00');
        $this->db->where('asistencias.fecha_hora <=', $fecha_fin . ' 23:59:59');

        if ($empleado_id) {
            $this->db->where('asistencias.empleado_id', $empleado_id);
        }

        $this->db->order_by('asistencias.fecha_hora', 'ASC');
        return $this->db->get()->result();
    }

    // ========================================================================
    // COLA DE COMANDOS
    // ========================================================================

    /** PIN del administrador físico del reloj — nunca borrar ni sobrescribir */
    const PIN_ADMIN_RELOJ = 1;

    /** Comandos por consulta del proxy (1 = serial; evita marcar 150 como enviado y ejecutar solo uno) */
    const COMANDOS_POR_LOTE_PROXY = 1;

    /** @var string|null Último error al encolar (validación ADMS) */
    private $ultimo_error_encolar = null;

    /**
     * Administrador del reloj (PIN 1): prohibido borrar o encolar altas que lo reemplacen
     */
    public function pin_protegido_reloj($pin)
    {
        return (int)$pin === self::PIN_ADMIN_RELOJ;
    }

    /**
     * @return int|null PIN si el comando es DATA DELETE USER
     */
    public function extraer_pin_delete_comando($comando)
    {
        if (preg_match('/^DATA DELETE USER(?:INFO)? PIN=(\d+)/i', trim((string)$comando), $m)) {
            return (int)$m[1];
        }
        return null;
    }

    public function comando_intenta_borrar_pin_protegido($comando)
    {
        $pin = $this->extraer_pin_delete_comando($comando);
        return $pin !== null && $this->pin_protegido_reloj($pin);
    }

    /**
     * Tabulador ADMS (ASCII 0x09). No usar '\t' entre comillas simples en PHP.
     */
    public function adms_tab()
    {
        return chr(9);
    }

    /**
     * Nombre visible en reloj: sin tabs ni saltos de línea
     */
    public function sanitizar_nombre_reloj($nombre)
    {
        $nombre = trim((string)$nombre);
        $nombre = str_replace([$this->adms_tab(), "\r", "\n"], '', $nombre);
        return mb_substr($nombre, 0, 24, 'UTF-8');
    }

    /**
     * DATA USER con TAB real (ASCII 9) — mismo formato que el proxy de planta.
     * Ej: DATA USER PIN=1001⇥Name=Juan Perez⇥Pri=0⇥Passwd=1234⇥Card=⇥Grp=1⇥TZ=...⇥Verify=0
     */
    public function build_comando_data_user($pin, $nombre, $passwd = '', $card = '', $grp = 1, $tz = '0000000100000000', $verify = 0)
    {
        $pin = (int)$pin;
        if ($this->pin_protegido_reloj($pin)) {
            return false;
        }

        $nombre = $this->sanitizar_nombre_reloj($nombre);
        $passwd = preg_replace('/\D/', '', (string)$passwd);

        $comando = "DATA USER PIN={$pin}\tName={$nombre}\tPri=0\tPasswd={$passwd}\tCard={$card}\tGrp=" . (int)$grp
            . "\tTZ={$tz}\tVerify=" . (int)$verify;

        if (!$this->comando_data_user_tiene_tabs_reales($comando)) {
            log_message('error', 'Reloj ADMS: build_comando_data_user sin tab real PIN=' . $pin);
        }

        return $comando;
    }

    /**
     * True si el comando DATA USER contiene tabulador ASCII 0x09 (no \t literal ni /t)
     */
    public function comando_data_user_tiene_tabs_reales($comando)
    {
        if (!preg_match('/^DATA USER PIN=/i', (string)$comando)) {
            return true;
        }

        $tab = $this->adms_tab();
        if (strpos($comando, '\\t') !== false || stripos($comando, '/tName=') !== false) {
            return false;
        }

        return strpos($comando, $tab) !== false
            && strpos($comando, $tab . 'Name=') !== false;
    }

    /**
     * DATA DELETE USER (sin tabuladores)
     */
    /**
     * @return string|false false si PIN=1 (admin reloj protegido)
     */
    public function build_comando_delete_user($pin)
    {
        $pin = (int)$pin;
        if ($this->pin_protegido_reloj($pin)) {
            return false;
        }
        return 'DATA DELETE USER PIN=' . $pin;
    }

    /**
     * Limpia y convierte a formato ADMS válido antes de validar/encolar
     */
    public function preparar_comando_adms($comando)
    {
        $comando = trim((string)$comando);
        if ($comando === '') {
            return '';
        }

        $comando = preg_replace('/^\xEF\xBB\xBF/', '', $comando);
        $comando = preg_replace('/^C:\d+:/i', '', $comando);
        $comando = trim($comando);

        // Formato legacy del placeholder (SET_USER 1=12345)
        if (preg_match('/^SET_USER\s+(\d+)\s*=\s*(.+)$/i', $comando, $m)) {
            $built = $this->build_comando_data_user((int)$m[1], trim($m[2]));
            return $built !== false ? $built : '';
        }

        if (preg_match('/^DATA\s+DELETE\s+/i', $comando) || preg_match('/^DELETE\s+USER\s+/i', $comando)) {
            return '';
        }

        $comando = preg_replace('/^DATA\s+USER\s+PIN\s*=\s*/i', 'DATA USER PIN=', $comando);

        $tab = $this->adms_tab();
        if (preg_match('/^DATA USER PIN=\d+/i', $comando) && strpos($comando, $tab) === false && preg_match('/Name\s*=/i', $comando)) {
            if (preg_match('/^DATA USER PIN=(\d+)\s+Name=([^\s]+)/i', $comando, $m)) {
                $built = $this->build_comando_data_user((int)$m[1], $m[2]);
                return $built !== false ? $built : '';
            }
        }

        return $this->normalizar_comando_adms($comando);
    }

    /**
     * Convierte separadores literales erróneos (\t, /t) a tab real en comandos ADMS
     */
    public function normalizar_comando_adms($comando)
    {
        $comando = trim((string)$comando);
        if ($comando === '') {
            return '';
        }

        if (!preg_match('/^DATA USER PIN=/i', $comando)) {
            return $comando;
        }

        $t = $this->adms_tab();

        // Secuencia literal backslash + t (error típico comillas simples PHP: '\t')
        if (strpos($comando, '\\t') !== false) {
            $comando = str_replace('\\t', $t, $comando);
        }

        // Variante /tName= (reloj muestra "2/tName yn" cuando falló el tab)
        $comando = preg_replace('#/t(?=Name=)#i', $t, $comando);
        $comando = preg_replace('#/t(?=Pri=|Passwd=|Card=|Grp=|TZ=|Verify=)#i', $t, $comando);

        // PIN=2\Name o PIN=2\tName (barra suelta)
        $comando = preg_replace('#(DATA USER PIN=\d+)\\+t?(?=Name=)#i', '$1' . $t, $comando);

        // Sin separador: PIN=2Name=
        $comando = preg_replace('#(DATA USER PIN=\d+)(?=Name=)#i', '$1' . $t, $comando);

        if (strpos($comando, $t) === false) {
            $comando = preg_replace('#/(?=Name=|Pri=|Passwd=|Card=|Grp=|TZ=|Verify=)#', $t, $comando);
        }

        if (!$this->comando_data_user_tiene_tabs_reales($comando)) {
            $rebuilt = $this->reconstruir_data_user_desde_texto_roto($comando);
            if ($rebuilt !== false) {
                $comando = $rebuilt;
            }
        }

        return $comando;
    }

    /**
     * Rearma DATA USER con tabs reales a partir de un string corrupto en BD
     *
     * @return string|false
     */
    public function reconstruir_data_user_desde_texto_roto($comando)
    {
        if (!preg_match('/^DATA USER PIN=(\d+)/i', $comando, $pinMatch)) {
            return false;
        }

        $pin = (int)$pinMatch[1];
        $nombre = null;

        if (preg_match('/Name=([^\r\n\t]+?)(?:\t|Pri=|Passwd=|Card=|Grp=|TZ=|Verify=|$)/i', $comando, $m)) {
            $nombre = trim($m[1]);
        } elseif (preg_match('/PIN=\d+[\\\\\/]?t?Name=([^\r\n]+)$/i', $comando, $m)) {
            $resto = $m[1];
            if (preg_match('/^(.+?)(?:Pri=|Passwd=|Card=|Grp=|TZ=|Verify=)/i', $resto, $m2)) {
                $nombre = trim($m2[1]);
            } else {
                $nombre = trim($resto);
            }
        }

        if ($nombre === null || $nombre === '') {
            return false;
        }

        return $this->build_comando_data_user($pin, $nombre);
    }

    /**
     * Valida formato ADMS antes de guardar en reloj_comandos
     *
     * @return true|string true si válido, mensaje de error si no
     */
    public function validar_comando_adms($comando)
    {
        $comando = trim((string)$comando);
        $tab = $this->adms_tab();

        if (preg_match('/^DATA DELETE /i', $comando)) {
            return 'No se encolan borrados. Use DATA USER con el mismo PIN para actualizar o dar de alta.';
        }

        if (preg_match('/^DATA USER PIN=(\d+)/i', $comando, $m)) {
            if ($this->pin_protegido_reloj($m[1])) {
                return 'PIN 1 está reservado; no se puede modificar el administrador del reloj desde el ERP.';
            }
            if (strpos($comando, '\\t') !== false || stripos($comando, '/tName=') !== false) {
                return 'Separadores inválidos: use tabulador real entre campos, no \\t ni /t.';
            }
            if (strpos($comando, $tab) === false) {
                return 'Falta tabulador entre campos (entre PIN= y Name=, etc.).';
            }
            if (strpos($comando, $tab . 'Name=') === false) {
                return 'Alta de usuario: debe incluir tabulador seguido de Name=';
            }
            return true;
        }

        return 'Comando no reconocido. Use DATA USER PIN=... o DATA DELETE USER PIN=...';
    }

    /**
     * @return string|null
     */
    public function get_ultimo_error_encolar()
    {
        return $this->ultimo_error_encolar;
    }

    /**
     * Obtiene comandos pendientes para un dispositivo y los marca como "enviado"
     * 
     * @param string $sn Número de serie del dispositivo
     * @return array|false Array de comandos o false en error
     */
    /**
     * Corrige en BD comandos pendientes guardados con \t literal (bug comillas simples PHP)
     */
    public function reparar_comandos_pendientes_sn($sn)
    {
        $rows = $this->db
            ->where('dispositivo_sn', $sn)
            ->where('estado', 'pendiente')
            ->get('reloj_comandos')
            ->result();

        foreach ($rows as $row) {
            $corregido = $this->preparar_comando_adms($row->comando);

            if (preg_match('/^DATA USER PIN=/i', $corregido) && !$this->comando_data_user_tiene_tabs_reales($corregido)) {
                $rebuilt = $this->reconstruir_data_user_desde_texto_roto($corregido);
                if ($rebuilt !== false) {
                    $corregido = $rebuilt;
                }
            }

            if ($this->comando_intenta_borrar_pin_protegido($corregido)) {
                $this->db->where('id', $row->id)->update('reloj_comandos', [
                    'estado'          => 'fallido',
                    'respuesta'       => 'Bloqueado: PIN 1 es el administrador del reloj',
                    'fecha_ejecucion' => date('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($corregido !== $row->comando && $corregido !== '') {
                $this->db->where('id', $row->id)->update('reloj_comandos', ['comando' => $corregido]);
            }
        }
    }

    public function obtener_y_marcar_comandos($sn)
    {
        $this->reparar_comandos_pendientes_sn($sn);

        $this->db->trans_start();

        // Obtener comandos pendientes
        $comandos = $this->db
            ->where('dispositivo_sn', $sn)
            ->where('estado', 'pendiente')
            ->limit(self::COMANDOS_POR_LOTE_PROXY)
            ->order_by('fecha_creacion', 'ASC')
            ->get('reloj_comandos')
            ->result();

        if (empty($comandos)) {
            $this->db->trans_complete();
            return [];
        }

        $ids_enviar = [];
        $ids_bloqueados = [];
        $resultado = [];

        foreach ($comandos as $cmd) {
            if (preg_match('/^DATA USER PIN=/i', $cmd->comando)) {
                $comando_norm = $this->asegurar_comando_user_con_tabs($cmd->comando);
            } else {
                $comando_norm = trim((string)$cmd->comando);
            }

            if (preg_match('/^DATA USER PIN=/i', $comando_norm) && !$this->comando_data_user_tiene_tabs_reales($comando_norm)) {
                $ids_bloqueados[] = $cmd->id;
                continue;
            }

            if ($this->comando_intenta_borrar_pin_protegido($comando_norm)) {
                $ids_bloqueados[] = $cmd->id;
                continue;
            }
            if ($comando_norm !== $cmd->comando) {
                $this->db->where('id', $cmd->id)->update('reloj_comandos', ['comando' => $comando_norm]);
            }

            $ids_enviar[] = $cmd->id;
            $resultado[] = [
                'id'      => (int)$cmd->id,
                'comando' => $comando_norm,
            ];
        }

        $ahora = date('Y-m-d H:i:s');

        if (!empty($ids_bloqueados)) {
            foreach ($ids_bloqueados as $bid) {
                $cmdRow = null;
                foreach ($comandos as $c) {
                    if ((int)$c->id === (int)$bid) {
                        $cmdRow = $c;
                        break;
                    }
                }
                $motivo = 'Bloqueado en ERP';
                if ($cmdRow && $this->comando_intenta_borrar_pin_protegido($this->normalizar_comando_adms($cmdRow->comando))) {
                    $motivo = 'Bloqueado: PIN 1 es el administrador del reloj';
                } elseif ($cmdRow && preg_match('/^DATA USER PIN=/i', $cmdRow->comando)) {
                    $motivo = 'Comando DATA USER sin tabuladores reales (formato ADMS inválido)';
                }
                $this->db->where('id', $bid)->where('estado', 'pendiente')->update('reloj_comandos', [
                    'estado'          => 'fallido',
                    'respuesta'       => $motivo,
                    'fecha_ejecucion' => $ahora,
                ]);
            }
        }

        if (!empty($ids_enviar)) {
            $this->db
                ->where_in('id', $ids_enviar)
                ->where('estado', 'pendiente')
                ->update('reloj_comandos', [
                    'estado'      => 'enviado',
                    'fecha_envio' => $ahora,
                    'intentos'    => 1,
                ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $resultado;
    }

    /**
     * Actualiza el resultado de un comando
     * 
     * @param int    $comando_id  ID del comando
     * @param string $estado      Nuevo estado (ejecutado/fallido)
     * @param string|null $respuesta Mensaje de respuesta del reloj
     * @return bool True si se actualizó
     */
    public function actualizar_comando_resultado($comando_id, $estado, $respuesta = null)
    {
        // Verificar que el comando existe y está en estado "enviado"
        $comando = $this->db
            ->where('id', $comando_id)
            ->where_in('estado', ['enviado', 'pendiente'])
            ->get('reloj_comandos')
            ->row();

        if (!$comando) {
            return false;
        }

        $data = [
            'estado'           => $estado,
            'respuesta'        => $respuesta,
            'fecha_ejecucion'  => date('Y-m-d H:i:s')
        ];

        // Incrementar intentos si vuelve a reportar resultado
        if ($comando->estado === 'enviado') {
            $data['intentos'] = $comando->intentos + 1;
        }

        return $this->db
            ->where('id', $comando_id)
            ->update('reloj_comandos', $data);
    }

    /**
     * Encola un nuevo comando para un dispositivo
     * 
     * @param string     $sn       Número de serie del dispositivo
     * @param string     $comando  Texto del comando
     * @param int|null   $creado_por ID del usuario que encola
     * @return int|false ID del comando insertado o false
     */
    /**
     * Comandos pendientes por SN (para diagnóstico tras sync RH)
     */
    public function contar_comandos_pendientes($sn)
    {
        return (int)$this->db
            ->where('dispositivo_sn', $sn)
            ->where('estado', 'pendiente')
            ->count_all_results('reloj_comandos');
    }

    /**
     * Elimina comandos pendientes/enviados del dispositivo (cola atascada o corrupta)
     */
    public function vaciar_cola_comandos_dispositivo($sn)
    {
        $this->db
            ->where('dispositivo_sn', $sn)
            ->where_in('estado', ['pendiente', 'enviado']);

        $this->db->delete('reloj_comandos');
        return (int)$this->db->affected_rows();
    }

    /**
     * Elimina todos los registros de la cola (todos los dispositivos y estados)
     */
    public function vaciar_todos_comandos()
    {
        $total = (int)$this->db->count_all('reloj_comandos');
        if ($total === 0) {
            return 0;
        }

        $this->db->truncate('reloj_comandos');
        return $total;
    }

    /**
     * Devuelve a pendiente los comandos enviados que el proxy no terminó de reportar
     */
    public function reencolar_comandos_enviados($sn)
    {
        $this->db
            ->where('dispositivo_sn', $sn)
            ->where('estado', 'enviado')
            ->update('reloj_comandos', [
                'estado'      => 'pendiente',
                'fecha_envio' => null,
            ]);

        return (int)$this->db->affected_rows();
    }

    /**
     * Fallido con texto "Ejecutado correctamente" (return_code del proxy mal interpretado)
     */
    public function corregir_fallidos_con_respuesta_exitosa($sn)
    {
        $this->db
            ->where('dispositivo_sn', $sn)
            ->where('estado', 'fallido')
            ->like('respuesta', 'Ejecutado correctamente')
            ->update('reloj_comandos', ['estado' => 'ejecutado']);

        return (int)$this->db->affected_rows();
    }

    /**
     * Interpreta éxito/fallo del proxy (return_code a veces != 0 con texto "Ejecutado correctamente")
     */
    public function interpretar_estado_resultado_comando($return_code, $respuesta = null, $estado_proxy = null)
    {
        if ($estado_proxy === 'ejecutado' || $estado_proxy === 'fallido') {
            return $estado_proxy;
        }

        if ($return_code === 0 || $return_code === '0') {
            return 'ejecutado';
        }

        if ($return_code !== null && $return_code !== '' && is_numeric($return_code) && (int)$return_code === 0) {
            return 'ejecutado';
        }

        if ($respuesta && preg_match('/ejecutado\s+correctamente|^\s*ok\s*$/i', $respuesta)) {
            return 'ejecutado';
        }

        return 'fallido';
    }

    /**
     * Reconstruye DATA USER con tabs reales a partir de pin + Name (anti 2/tName)
     */
    public function asegurar_comando_user_con_tabs($comando)
    {
        if (!preg_match('/^DATA USER PIN=(\d+)/i', (string)$comando, $pm)) {
            return $comando;
        }

        $pin = (int)$pm[1];
        $nombre = null;
        $tab = $this->adms_tab();

        if (preg_match('/Name=([^\r\n]+?)(?:' . preg_quote($tab, '/') . '|\s+Pri=|Pri=)/i', $comando, $nm)) {
            $nombre = trim($nm[1]);
        } elseif (preg_match('/Name=([^\s]+)/i', $comando, $nm)) {
            $nombre = trim($nm[1]);
        }

        if ($nombre === null || $nombre === '') {
            return $this->preparar_comando_adms($comando);
        }

        $built = $this->build_comando_data_user($pin, $nombre);
        return $built !== false ? $built : $comando;
    }

    public function encolar_comando($sn, $comando, $creado_por = null, $validar = true, $comando_ya_armado = false)
    {
        $this->ultimo_error_encolar = null;

        if ($comando_ya_armado) {
            $comando = trim((string)$comando);
            if (preg_match('/^DATA USER PIN=/i', $comando)) {
                $comando = $this->asegurar_comando_user_con_tabs($comando);
            }
        } else {
            $comando = $this->preparar_comando_adms($comando);
        }

        if ($comando === '') {
            $this->ultimo_error_encolar = 'Comando vacío';
            return false;
        }

        if ($this->comando_intenta_borrar_pin_protegido($comando)) {
            $this->ultimo_error_encolar = 'PIN 1 está reservado para el administrador del reloj; no se puede borrar.';
            return false;
        }

        // DATA USER: siempre validar tabs reales (evita reenviar PIN=2\tName corrupto)
        if (preg_match('/^DATA USER PIN=/i', $comando)) {
            if (!$this->comando_data_user_tiene_tabs_reales($comando)) {
                $rebuilt = $this->reconstruir_data_user_desde_texto_roto($comando);
                if ($rebuilt !== false) {
                    $comando = $rebuilt;
                }
            }
            $validacion_user = $this->validar_comando_adms($comando);
            if ($validacion_user !== true) {
                $this->ultimo_error_encolar = $validacion_user;
                return false;
            }
        } elseif ($validar) {
            $validacion = $this->validar_comando_adms($comando);
            if ($validacion !== true) {
                $this->ultimo_error_encolar = $validacion;
                return false;
            }
        }

        $data = [
            'dispositivo_sn' => $sn,
            'comando'        => $comando,
            'estado'         => 'pendiente',
            'creado_por'     => $creado_por,
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ];

        $insert = $this->db->insert('reloj_comandos', $data);
        return $insert ? $this->db->insert_id() : false;
    }

    /**
     * Obtiene estadísticas de comandos por dispositivo
     * 
     * @param string|null $sn Filtrar por dispositivo (opcional)
     * @return array
     */
    public function get_estadisticas_comandos($sn = null)
    {
        $this->db->select('estado, COUNT(*) as total');
        $this->db->from('reloj_comandos');
        
        if ($sn) {
            $this->db->where('dispositivo_sn', $sn);
        }

        $this->db->group_by('estado');
        $resultados = $this->db->get()->result();

        $stats = [
            'pendiente'  => 0,
            'enviado'    => 0,
            'ejecutado'  => 0,
            'fallido'    => 0
        ];

        foreach ($resultados as $r) {
            $stats[$r->estado] = (int)$r->total;
        }

        return $stats;
    }

    // ========================================================================
    // BITÁCORA DE SINCRONIZACIÓN
    // ========================================================================

    /**
     * Registra un evento en la bitácora de sincronización
     * 
     * @param string|null $sn       Número de serie del dispositivo
     * @param string      $tipo     Tipo de evento
     * @param string|null $resumen  Resumen del payload
     * @param int|null    $registros Cantidad de registros afectados
     * @return bool
     */
    public function registrar_sync_log($sn, $tipo, $resumen = null, $registros = null)
    {
        return $this->db->insert('reloj_sync_log', [
            'dispositivo_sn'     => $sn,
            'tipo'               => $tipo,
            'payload_resumen'    => $resumen,
            'registros_afectados' => $registros,
            'ip_origen'          => $this->input->ip_address(),
            'fecha'              => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtiene el historial de sincronización
     * 
     * @param int $limite Cantidad de registros a obtener
     * @return array
     */
    public function get_sync_log($limite = 100)
    {
        return $this->db
            ->order_by('fecha', 'DESC')
            ->limit($limite)
            ->get('reloj_sync_log')
            ->result();
    }

    // ========================================================================
    // DISPOSITIVOS
    // ========================================================================

    /**
     * Obtiene todos los dispositivos registrados
     * 
     * @param bool $solo_activos Si es true, solo devuelve activos
     * @return array
     */
    public function get_dispositivos($solo_activos = true)
    {
        if ($solo_activos) {
            $this->db->where('activo', 1);
        }
        
        return $this->db
            ->order_by('fecha_alta', 'DESC')
            ->get('reloj_dispositivos')
            ->result();
    }

    /**
     * Obtiene un dispositivo por su SN
     * 
     * @param string $sn Número de serie
     * @return object|null
     */
    public function get_dispositivo_by_sn($sn)
    {
        return $this->db
            ->where('sn', $sn)
            ->get('reloj_dispositivos')
            ->row();
    }

    /**
     * Obtiene estadísticas generales del módulo
     * 
     * @return array
     */
    public function get_estadisticas_dashboard()
    {
        $hoy = date('Y-m-d');

        $stats = [];

        // Total de asistencias hoy
        $stats['asistencias_hoy'] = $this->db
            ->where('DATE(fecha_hora)', $hoy)
            ->count_all_results('asistencias');

        // Total de asistencias este mes (el "=" va en la clave: CI3 no lo agrega si hay espacios en la expresión)
        $stats['asistencias_mes'] = $this->db
            ->where('DATE_FORMAT(fecha_hora, "%Y-%m") =', date('Y-m'))
            ->count_all_results('asistencias');

        // Empleados que checaron hoy
        $stats['empleados_checaron_hoy'] = $this->db
            ->distinct()
            ->select('empleado_id')
            ->where('DATE(fecha_hora)', $hoy)
            ->where('empleado_id IS NOT NULL')
            ->count_all_results('asistencias');

        // Comandos pendientes
        $stats['comandos_pendientes'] = $this->db
            ->where('estado', 'pendiente')
            ->count_all_results('reloj_comandos');

        // Dispositivos activos
        $stats['dispositivos_activos'] = $this->db
            ->where('activo', 1)
            ->count_all_results('reloj_dispositivos');

        // Última sincronización
        $ultima_sync = $this->db
            ->order_by('fecha', 'DESC')
            ->limit(1)
            ->get('reloj_sync_log')
            ->row();
        $stats['ultima_sincronizacion'] = $ultima_sync ? $ultima_sync->fecha : null;

        $alertas = $this->get_alertas_asistencia_hoy($hoy);
        $stats['retardos_hoy'] = $alertas['retardos'];
        $stats['sin_salida_hoy'] = $alertas['sin_salida'];
        $stats['empleados_sin_pin'] = $this->count_empleados_sin_pin_reloj();
        $stats['checadas_7_dias'] = $this->get_checadas_ultimos_dias(7);

        $resumen_hoy = $this->get_resumen_diario_empleados($hoy);
        $presentes = 0;
        $ausentes = 0;
        foreach ($resumen_hoy as $fila) {
            $estado = mb_strtolower((string)($fila->estado ?? ''), 'UTF-8');
            if ($estado === 'ausente' || (int)($fila->total_checadas ?? 0) === 0) {
                $ausentes++;
            } else {
                $presentes++;
            }
        }
        $stats['presentes_hoy'] = $presentes;
        $stats['ausentes_hoy'] = $ausentes;
        if ($presentes === 0 && $ausentes === 0 && (int)$stats['empleados_checaron_hoy'] > 0) {
            $stats['presentes_hoy'] = (int)$stats['empleados_checaron_hoy'];
        }
        $stats['total_esperados_hoy'] = count($resumen_hoy) > 0
            ? count($resumen_hoy)
            : max((int)$stats['presentes_hoy'] + (int)$stats['ausentes_hoy'], (int)$stats['empleados_checaron_hoy']);

        return $stats;
    }

    /**
     * Indica si un dispositivo está en línea (última conexión < 30 min)
     */
    public function dispositivo_esta_online($ultima_conexion)
    {
        if (empty($ultima_conexion)) {
            return false;
        }
        return (time() - strtotime($ultima_conexion)) <= 1800;
    }

    /**
     * Últimas checadas registradas (dashboard)
     */
    public function get_ultimas_checadas($limit = 10)
    {
        $this->db->select([
            'asistencias.*',
            'empleados.numero_empleado',
            'CONCAT(empleados.nombre, " ", empleados.apellido_paterno) AS empleado_nombre',
        ], false);
        $this->db->from('asistencias');
        $this->db->join('empleados', 'empleados.id = asistencias.empleado_id', 'left');
        $this->db->order_by('asistencias.fecha_hora', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result();
    }

    /**
     * Conteo de checadas por día (últimos N días, incluye hoy)
     */
    public function get_checadas_ultimos_dias($dias = 7)
    {
        $dias = max(1, (int)$dias);
        $inicio = date('Y-m-d', strtotime('-' . ($dias - 1) . ' days'));
        $fin = date('Y-m-d');

        $this->db->select('DATE(fecha_hora) AS fecha, COUNT(*) AS total', false);
        $this->db->from('asistencias');
        $this->db->where('DATE(fecha_hora) >=', $inicio);
        $this->db->where('DATE(fecha_hora) <=', $fin);
        $this->db->group_by('DATE(fecha_hora)');
        $this->db->order_by('fecha', 'ASC');
        $rows = $this->db->get()->result();

        $mapa = [];
        foreach ($rows as $row) {
            $mapa[$row->fecha] = (int)$row->total;
        }

        $resultado = [];
        $cursor = strtotime($inicio);
        $fin_ts = strtotime($fin);
        while ($cursor <= $fin_ts) {
            $fecha = date('Y-m-d', $cursor);
            $resultado[] = [
                'fecha'      => $fecha,
                'fecha_corta'=> date('d/m', $cursor),
                'dia_semana' => $this->dia_semana_es($fecha),
                'total'      => isset($mapa[$fecha]) ? $mapa[$fecha] : 0,
            ];
            $cursor = strtotime('+1 day', $cursor);
        }

        return $resultado;
    }

    /**
     * Empleados activos sin PIN configurado en reloj
     */
    public function count_empleados_sin_pin_reloj()
    {
        if (!$this->db->field_exists('reloj_pin', 'empleados')) {
            return null;
        }

        $this->db->from('empleados');
        $this->filtro_empleados_laboral_activo('estatus');
        $this->db->group_start();
        $this->db->where('reloj_pin IS NULL', null, false);
        $this->db->or_where('reloj_pin', '');
        $this->db->or_where('reloj_pin', 0);
        $this->db->group_end();

        return (int)$this->db->count_all_results();
    }

    /**
     * Alertas del día: retardos y empleados sin checada de salida completa
     */
    public function get_alertas_asistencia_hoy($fecha = null)
    {
        $fecha = $fecha ?: date('Y-m-d');
        $dia_semana = $this->dia_semana_es($fecha);

        $this->db->distinct();
        $this->db->select('empleado_id');
        $this->db->from('asistencias');
        $this->db->where('DATE(fecha_hora)', $fecha);
        $this->db->where('empleado_id IS NOT NULL', null, false);
        $ids_rows = $this->db->get()->result();

        $empleado_ids = array_map(function ($r) {
            return (int)$r->empleado_id;
        }, $ids_rows);

        $retardos = 0;
        $sin_salida = 0;

        if (empty($empleado_ids)) {
            return ['retardos' => 0, 'sin_salida' => 0];
        }

        $horarios_map = $this->_get_horarios_dia_batch($empleado_ids, $fecha, $dia_semana);

        foreach ($empleado_ids as $emp_id) {
            $horario = isset($horarios_map[$emp_id]) ? $horarios_map[$emp_id] : null;
            $calculo = $this->calcular_asistencia_diaria($emp_id, $fecha, $horario);

            if (!empty($calculo['retardo'])) {
                $retardos++;
            }
            if (!empty($calculo['tiene_checadas']) && empty($calculo['salida_completa'])) {
                $sin_salida++;
            }
        }

        return ['retardos' => $retardos, 'sin_salida' => $sin_salida];
    }

    /** @var array|null Cache de resumen diario por request */
    private $_resumen_diario_cache = null;

    /**
     * Resumen diario por empleado (entrada/salida/comida interpretadas)
     */
    public function get_resumen_diario_empleados($fecha, $departamento_id = null, $empleado_id = null, $estado_filtro = null)
    {
        $cache_key = md5(json_encode([$fecha, $departamento_id, $empleado_id, $estado_filtro]));
        if ($this->_resumen_diario_cache !== null && isset($this->_resumen_diario_cache[$cache_key])) {
            return $this->_resumen_diario_cache[$cache_key];
        }

        $empleados = $this->_get_empleados_para_resumen_diario($fecha, $departamento_id, $empleado_id);
        if (empty($empleados)) {
            $this->_resumen_diario_cache[$cache_key] = [];
            return [];
        }

        $empleado_ids = array_map(function ($e) {
            return (int)$e->id;
        }, $empleados);
        $dia_semana = $this->dia_semana_es($fecha);
        $horarios_map = $this->_get_horarios_dia_batch($empleado_ids, $fecha, $dia_semana);

        $resultado = [];
        foreach ($empleados as $emp) {
            $horario = isset($horarios_map[$emp->id]) ? $horarios_map[$emp->id] : null;
            $calculo = $this->calcular_asistencia_diaria($emp->id, $fecha, $horario);

            if (!$this->_estado_coincide_filtro($calculo['estado'], $estado_filtro)) {
                continue;
            }

            $resultado[] = (object)[
                'empleado_id'        => (int)$emp->id,
                'numero_empleado'    => $emp->numero_empleado,
                'empleado_nombre'    => $emp->empleado_nombre,
                'puesto'             => $emp->puesto,
                'departamento_nombre'=> $emp->departamento_nombre,
                'entrada'            => $calculo['entrada'],
                'salida_comida'      => $calculo['salida_comida'],
                'entrada_comida'     => $calculo['entrada_comida'],
                'salida'             => $calculo['salida'],
                'estado'             => $calculo['estado'],
                'retardo'            => !empty($calculo['retardo']),
                'minutos_retardo'    => (int)($calculo['minutos_retardo'] ?? 0),
                'horas_trabajadas'   => $calculo['horas_trabajadas'] ?? '00:00',
                'total_checadas'     => (int)($calculo['total_checadas'] ?? 0),
                'tiene_horario'      => !empty($calculo['tiene_horario']),
            ];
        }

        $this->_resumen_diario_cache[$cache_key] = $resultado;
        return $resultado;
    }

    /**
     * DataTables SSR — Resumen diario por empleado
     */
    public function get_resumen_diario_datatables()
    {
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $departamento_id = !empty($_POST['departamento_id']) ? $_POST['departamento_id'] : null;
        $empleado_id = !empty($_POST['empleado_id']) ? $_POST['empleado_id'] : null;
        $estado_filtro = !empty($_POST['estado']) ? $_POST['estado'] : null;

        $rows = $this->get_resumen_diario_empleados($fecha, $departamento_id, $empleado_id, $estado_filtro);
        $search = $this->_dt_search_value();

        if ($search !== '') {
            $search_lower = mb_strtolower($search, 'UTF-8');
            $rows = array_values(array_filter($rows, function ($r) use ($search_lower) {
                $campos = [
                    $r->numero_empleado ?? '',
                    $r->empleado_nombre ?? '',
                    $r->puesto ?? '',
                    $r->departamento_nombre ?? '',
                    $r->estado ?? '',
                ];
                foreach ($campos as $campo) {
                    if (mb_strpos(mb_strtolower((string)$campo, 'UTF-8'), $search_lower) !== false) {
                        return true;
                    }
                }
                return false;
            }));
        }

        $sort_cols = [
            0 => 'numero_empleado',
            1 => 'empleado_nombre',
            2 => 'departamento_nombre',
            3 => 'entrada',
            4 => 'salida_comida',
            5 => 'entrada_comida',
            6 => 'salida',
            7 => 'estado',
            8 => 'minutos_retardo',
            9 => 'horas_trabajadas',
        ];

        if (isset($_POST['order'][0]['column'], $_POST['order'][0]['dir'])) {
            $col_idx = (int)$_POST['order'][0]['column'];
            $dir = strtolower($_POST['order'][0]['dir']) === 'desc' ? -1 : 1;
            if (isset($sort_cols[$col_idx])) {
                $key = $sort_cols[$col_idx];
                usort($rows, function ($a, $b) use ($key, $dir) {
                    $va = isset($a->$key) ? $a->$key : '';
                    $vb = isset($b->$key) ? $b->$key : '';
                    if ($va == $vb) {
                        return 0;
                    }
                    return ($va < $vb ? -1 : 1) * $dir;
                });
            }
        } else {
            usort($rows, function ($a, $b) {
                return strcmp($a->empleado_nombre ?? '', $b->empleado_nombre ?? '');
            });
        }

        $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        $length = isset($_POST['length']) && (int)$_POST['length'] !== -1 ? (int)$_POST['length'] : count($rows);

        return array_slice($rows, $start, $length);
    }

    public function count_resumen_diario_all($fecha = null)
    {
        $fecha = $fecha ?: (isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d'));
        return count($this->get_resumen_diario_empleados($fecha, null, null, null));
    }

    public function count_resumen_diario_filtered()
    {
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $departamento_id = !empty($_POST['departamento_id']) ? $_POST['departamento_id'] : null;
        $empleado_id = !empty($_POST['empleado_id']) ? $_POST['empleado_id'] : null;
        $estado_filtro = !empty($_POST['estado']) ? $_POST['estado'] : null;

        $rows = $this->get_resumen_diario_empleados($fecha, $departamento_id, $empleado_id, $estado_filtro);
        $search = $this->_dt_search_value();

        if ($search === '') {
            return count($rows);
        }

        $search_lower = mb_strtolower($search, 'UTF-8');
        return count(array_filter($rows, function ($r) use ($search_lower) {
            $campos = [
                $r->numero_empleado ?? '',
                $r->empleado_nombre ?? '',
                $r->puesto ?? '',
                $r->departamento_nombre ?? '',
                $r->estado ?? '',
            ];
            foreach ($campos as $campo) {
                if (mb_strpos(mb_strtolower((string)$campo, 'UTF-8'), $search_lower) !== false) {
                    return true;
                }
            }
            return false;
        }));
    }

    /**
     * Badge HTML para estado de asistencia
     */
    public function badge_estado_asistencia_html($estado)
    {
        $clases = [
            'Asistencia completa'  => 'bg-success',
            'Con retardo'          => 'bg-warning text-dark',
            'Retardo mayor'        => 'bg-danger',
            'Salida temprana'      => 'bg-warning text-dark',
            'Checadas parciales'   => 'bg-secondary',
            'Sin checadas'         => 'bg-light text-muted border',
            'Sin horario asignado' => 'bg-info',
        ];
        $clase = isset($clases[$estado]) ? $clases[$estado] : 'bg-secondary';
        return '<span class="badge ' . $clase . '">' . htmlspecialchars($estado) . '</span>';
    }

    /**
     * Badge HTML para método de checada ZKTeco
     */
    public function badge_metodo_checada_html($metodo)
    {
        switch ((int)$metodo) {
            case 15:
                return '<span class="badge bg-info">Rostro</span>';
            case 1:
                return '<span class="badge bg-secondary">Huella</span>';
            case 0:
            case 3:
                return '<span class="badge bg-dark">Contraseña</span>';
            default:
                return '<span class="badge bg-light text-dark border">#' . (int)$metodo . '</span>';
        }
    }

    private function _get_empleados_para_resumen_diario($fecha, $departamento_id = null, $empleado_id = null)
    {
        $dia_semana = $this->dia_semana_es($fecha);
        $fecha_esc = $this->db->escape($fecha);
        $dia_esc = $this->db->escape($dia_semana);

        $this->db->select([
            'empleados.id',
            'empleados.numero_empleado',
            'CONCAT(empleados.nombre, " ", empleados.apellido_paterno) AS empleado_nombre',
            'empleados.puesto',
            'departamentos.nombre AS departamento_nombre',
        ], false);
        $this->db->from('empleados');
        $this->db->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left');
        $this->filtro_empleados_laboral_activo('empleados.estatus');

        if ($departamento_id) {
            $this->db->where('empleados.departamento_id', (int)$departamento_id);
        }
        if ($empleado_id) {
            $this->db->where('empleados.id', (int)$empleado_id);
        }

        $this->db->where(
            '(empleados.id IN (
                SELECT DISTINCT a.empleado_id FROM asistencias a
                WHERE DATE(a.fecha_hora) = ' . $fecha_esc . ' AND a.empleado_id IS NOT NULL
            ) OR empleados.id IN (
                SELECT DISTINCT h.empleado_id FROM horarios_empleados h
                WHERE h.dia_semana = ' . $dia_esc . '
                AND h.es_dia_laboral = 1
                AND h.estatus = ' . $this->db->escape('Activo') . '
                AND h.fecha_inicio <= ' . $fecha_esc . '
                AND (h.fecha_fin >= ' . $fecha_esc . ' OR h.fecha_fin IS NULL)
            ))',
            null,
            false
        );

        $this->db->order_by('empleado_nombre', 'ASC');
        return $this->db->get()->result();
    }

    private function _get_horarios_dia_batch(array $empleado_ids, $fecha, $dia_semana)
    {
        if (empty($empleado_ids)) {
            return [];
        }

        $this->db->from('horarios_empleados');
        $this->db->where_in('empleado_id', $empleado_ids);
        $this->db->where('dia_semana', $dia_semana);
        $this->db->where('es_dia_laboral', 1);
        $this->db->where('estatus', 'Activo');
        $this->db->where('fecha_inicio <=', $fecha);
        $this->db->group_start();
        $this->db->where('fecha_fin >=', $fecha);
        $this->db->or_where('fecha_fin IS NULL');
        $this->db->group_end();

        $rows = $this->db->get()->result();
        $mapa = [];
        foreach ($rows as $row) {
            $mapa[(int)$row->empleado_id] = $row;
        }
        return $mapa;
    }

    private function _estado_coincide_filtro($estado, $filtro)
    {
        if (empty($filtro)) {
            return true;
        }

        $mapa = [
            'completo'    => ['Asistencia completa'],
            'retardo'     => ['Con retardo', 'Retardo mayor'],
            'falta'       => ['Sin checadas'],
            'incompleto'  => ['Salida temprana', 'Checadas parciales'],
            'sin_horario' => ['Sin horario asignado'],
        ];

        if (!isset($mapa[$filtro])) {
            return true;
        }

        return in_array($estado, $mapa[$filtro], true);
    }

    // ========================================================================
    // DATATABLES SSR — HELPERS
    // ========================================================================

    private function _dt_search_value()
    {
        return isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    }

    private function _dt_apply_limit()
    {
        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
            $this->db->limit((int)$_POST['length'], $start);
        }
    }

    private function _dt_order_by(array $column_order, $default_column, $default_dir = 'DESC')
    {
        if (isset($_POST['order'][0]['column'], $_POST['order'][0]['dir'])) {
            $idx = (int)$_POST['order'][0]['column'];
            if (isset($column_order[$idx])) {
                $this->db->order_by($column_order[$idx], $_POST['order'][0]['dir']);
                return;
            }
        }
        $this->db->order_by($default_column, $default_dir);
    }

    // ========================================================================
    // DATATABLES SSR — DISPOSITIVOS
    // ========================================================================

    /**
     * DataTables SSR — Obtiene listado paginado de dispositivos
     *
     * @return array Lista de dispositivos
     */
    public function get_dispositivos_datatables()
    {
        $column_order = ['sn', 'alias', 'ubicacion', 'ultima_conexion', 'activo'];
        $column_search = ['sn', 'alias', 'ubicacion'];

        $this->db->from('reloj_dispositivos');

        $i = 0;
        $search_value = $_POST['search']['value'];
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('fecha_alta', 'DESC');
        }

        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        return $this->db->get()->result();
    }

    /**
     * DataTables SSR — Total de dispositivos sin filtro
     *
     * @return int
     */
    public function count_dispositivos_all()
    {
        return $this->db->count_all('reloj_dispositivos');
    }

    /**
     * DataTables SSR — Total de dispositivos filtrados
     *
     * @return int
     */
    public function count_dispositivos_filtered()
    {
        $column_search = ['sn', 'alias', 'ubicacion'];

        $this->db->from('reloj_dispositivos');

        $i = 0;
        $search_value = $_POST['search']['value'];
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        return $this->db->get()->num_rows();
    }

    // ========================================================================
    // DATATABLES SSR — COMANDOS
    // ========================================================================

    /**
     * DataTables SSR — Obtiene listado paginado de comandos
     *
     * @return array Lista de comandos
     */
    public function get_comandos_datatables()
    {
        $column_order = [
            'reloj_comandos.dispositivo_sn',
            'reloj_comandos.comando',
            'reloj_comandos.estado',
            'reloj_comandos.intentos',
            'reloj_comandos.respuesta',
            'reloj_comandos.fecha_creacion',
            'reloj_comandos.fecha_ejecucion',
            'administradores.username',
        ];
        $column_search = ['dispositivo_sn', 'comando', 'estado'];

        $this->db->select('reloj_comandos.*, administradores.username as creado_por_usuario');
        $this->db->from('reloj_comandos');
        $this->db->join('administradores', 'administradores.id = reloj_comandos.creado_por', 'left');

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        $this->_dt_order_by($column_order, 'reloj_comandos.fecha_creacion', 'DESC');
        $this->_dt_apply_limit();

        return $this->db->get()->result();
    }

    /**
     * DataTables SSR — Total de comandos sin filtro
     *
     * @return int
     */
    public function count_comandos_all()
    {
        return $this->db->count_all('reloj_comandos');
    }

    /**
     * DataTables SSR — Total de comandos filtrados
     *
     * @return int
     */
    public function count_comandos_filtered()
    {
        $column_search = ['dispositivo_sn', 'comando', 'estado'];

        $this->db->select('reloj_comandos.*');
        $this->db->from('reloj_comandos');

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        return $this->db->get()->num_rows();
    }

    // ========================================================================
    // DATATABLES SSR — SYNC LOG
    // ========================================================================

    /**
     * DataTables SSR — Obtiene listado paginado del historial de sincronización
     *
     * @return array Lista de sync log
     */
    public function get_sync_log_datatables()
    {
        $column_order = ['dispositivo_sn', 'tipo', 'payload_resumen', 'registros_afectados', 'ip_origen', 'fecha'];
        $column_search = ['dispositivo_sn', 'tipo', 'payload_resumen', 'ip_origen'];

        $this->db->from('reloj_sync_log');

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        $this->_dt_order_by($column_order, 'fecha', 'DESC');
        $this->_dt_apply_limit();

        return $this->db->get()->result();
    }

    /**
     * DataTables SSR — Total de registros de sync_log sin filtro
     *
     * @return int
     */
    public function count_sync_log_all()
    {
        return $this->db->count_all('reloj_sync_log');
    }

    /**
     * DataTables SSR — Total de registros de sync_log filtrados
     *
     * @return int
     */
    public function count_sync_log_filtered()
    {
        $column_search = ['dispositivo_sn', 'tipo', 'payload_resumen', 'ip_origen'];

        $this->db->from('reloj_sync_log');

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        return $this->db->get()->num_rows();
    }

    // ========================================================================
    // DATATABLES SSR — ASISTENCIAS DIARIAS
    // ========================================================================

    /**
     * DataTables SSR — Obtiene listado paginado de asistencias del día
     *
     * @return array Lista de asistencias diarias
     */
    public function get_asistencias_diario_datatables()
    {
        $column_order = [
            'empleados.numero_empleado',
            'empleado_nombre',
            'empleados.puesto',
            'departamentos.nombre',
            'asistencias.fecha_hora',
            'asistencias.metodo'
        ];
        $column_search = [
            'empleados.numero_empleado',
            'CONCAT(empleados.nombre, " ", empleados.apellido_paterno)',
            'empleados.puesto',
            'departamentos.nombre'
        ];

        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $empleado_id = isset($_POST['empleado_id']) ? $_POST['empleado_id'] : null;
        $departamento_id = isset($_POST['departamento_id']) ? $_POST['departamento_id'] : null;

        $this->db->select([
            'asistencias.*',
            'CONCAT(empleados.nombre, " ", empleados.apellido_paterno) AS empleado_nombre',
            'empleados.numero_empleado',
            'empleados.puesto',
            'departamentos.nombre AS departamento_nombre',
        ], FALSE);
        $this->db->from('asistencias');
        $this->db->join('empleados', 'empleados.id = asistencias.empleado_id', 'left');
        $this->db->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left');
        $this->db->where('DATE(asistencias.fecha_hora)', $fecha);

        if ($empleado_id) {
            $this->db->where('asistencias.empleado_id', $empleado_id);
        }

        if ($departamento_id) {
            $this->db->where('empleados.departamento_id', $departamento_id);
        }

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        $this->_dt_order_by($column_order, 'asistencias.fecha_hora', 'DESC');
        $this->_dt_apply_limit();

        return $this->db->get()->result();
    }

    /**
     * DataTables SSR — Total de asistencias diarias sin filtro
     *
     * @return int
     */
    public function count_asistencias_diario_all()
    {
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        return $this->db
            ->where('DATE(fecha_hora)', $fecha)
            ->count_all_results('asistencias');
    }

    /**
     * DataTables SSR — Total de asistencias diarias filtradas
     *
     * @return int
     */
    public function count_asistencias_diario_filtered()
    {
        $column_search = [
            'empleados.numero_empleado',
            'CONCAT(empleados.nombre, " ", empleados.apellido_paterno)',
            'empleados.puesto',
            'departamentos.nombre'
        ];

        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $empleado_id = isset($_POST['empleado_id']) ? $_POST['empleado_id'] : null;
        $departamento_id = isset($_POST['departamento_id']) ? $_POST['departamento_id'] : null;

        $this->db->from('asistencias');
        $this->db->join('empleados', 'empleados.id = asistencias.empleado_id', 'left');
        $this->db->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left');
        $this->db->where('DATE(asistencias.fecha_hora)', $fecha);

        if ($empleado_id) {
            $this->db->where('asistencias.empleado_id', $empleado_id);
        }

        if ($departamento_id) {
            $this->db->where('empleados.departamento_id', $departamento_id);
        }

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
            }
            $i++;
        }

        return $this->db->get()->num_rows();
    }

    // ========================================================================
    // DATATABLES SSR — ASISTENCIAS MENSUALES (RESUMEN POR EMPLEADO)
    // ========================================================================

    /**
     * DataTables SSR — Obtiene resumen mensual de asistencias por empleado
     *
     * @param bool $aplicar_limite Si es false, omite paginación (para conteo filtrado)
     * @return array Lista de empleados con resumen mensual
     */
    public function get_asistencias_mensual_datatables($aplicar_limite = true)
    {
        $column_order = [
            'empleados.numero_empleado',
            'empleado_nombre',
            'empleados.puesto',
            'departamento_nombre',
            'dias_trabajados',
            'dias_laborales',
            'dias_trabajados',
            'primera_checada',
            'ultima_checada'
        ];
        $column_search = [
            'empleados.numero_empleado',
            'empleado_nombre',
            'empleados.puesto',
            'departamento_nombre'
        ];

        $mes = isset($_POST['mes']) ? str_pad($_POST['mes'], 2, '0', STR_PAD_LEFT) : date('m');
        $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : (int)date('Y');
        $empleado_id = !empty($_POST['empleado_id']) ? $_POST['empleado_id'] : null;
        $departamento_id = !empty($_POST['departamento_id']) ? $_POST['departamento_id'] : null;

        $fecha_inicio = $anio . '-' . $mes . '-01';
        $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
        $fecha_inicio_sql = $this->db->escape($fecha_inicio . ' 00:00:00');
        $fecha_fin_sql = $this->db->escape($fecha_fin . ' 23:59:59');

        // Array + escape FALSE: CI3 no debe hacer explode por comas dentro de CONCAT/DATE_FORMAT
        $this->db->select([
            'empleados.id AS empleado_id',
            'empleados.numero_empleado',
            'CONCAT(empleados.nombre, " ", empleados.apellido_paterno) AS empleado_nombre',
            'empleados.puesto',
            'departamentos.nombre AS departamento_nombre',
            'COUNT(DISTINCT DATE(asistencias.fecha_hora)) AS dias_trabajados',
            'DAY(LAST_DAY(' . $this->db->escape($fecha_inicio) . ')) AS dias_laborales',
            "MIN(DATE_FORMAT(asistencias.fecha_hora, '%d/%m/%Y %H:%i')) AS primera_checada",
            "MAX(DATE_FORMAT(asistencias.fecha_hora, '%d/%m/%Y %H:%i')) AS ultima_checada",
        ], FALSE);
        $this->db->from('empleados');
        $this->db->join(
            'asistencias',
            'asistencias.empleado_id = empleados.id AND asistencias.fecha_hora >= ' . $fecha_inicio_sql . ' AND asistencias.fecha_hora <= ' . $fecha_fin_sql,
            'left',
            FALSE
        );
        $this->db->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left');
        $this->filtro_empleados_laboral_activo('empleados.estatus');

        if ($empleado_id) {
            $this->db->where('empleados.id', $empleado_id);
        }

        if ($departamento_id) {
            $this->db->where('empleados.departamento_id', $departamento_id);
        }

        $this->db->group_by('empleados.id');

        $i = 0;
        $search_value = $this->_dt_search_value();
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->having($item . ' LIKE "%' . $this->db->escape_like_str($search_value) . '%"');
                } else {
                    $this->db->or_having($item . ' LIKE "%' . $this->db->escape_like_str($search_value) . '%"');
                }
            }
            $i++;
        }

        $this->_dt_order_by($column_order, 'empleado_nombre', 'ASC');
        if ($aplicar_limite) {
            $this->_dt_apply_limit();
        }

        return $this->db->get()->result();
    }

    /**
     * DataTables SSR — Total de empleados activos (sin filtro mensual)
     *
     * @return int
     */
    public function count_asistencias_mensual_all()
    {
        return $this->db
            ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
            ->count_all_results('empleados');
    }

    /**
     * DataTables SSR — Total de empleados activos filtrados
     *
     * @return int
     */
    public function count_asistencias_mensual_filtered()
    {
        $search_value = $this->_dt_search_value();
        if ($search_value) {
            return count($this->get_asistencias_mensual_datatables(false));
        }

        $empleado_id = !empty($_POST['empleado_id']) ? $_POST['empleado_id'] : null;
        $departamento_id = !empty($_POST['departamento_id']) ? $_POST['departamento_id'] : null;

        $this->db->from('empleados');
        $this->filtro_empleados_laboral_activo('empleados.estatus');

        if ($empleado_id) {
            $this->db->where('empleados.id', $empleado_id);
        }

        if ($departamento_id) {
            $this->db->where('empleados.departamento_id', $departamento_id);
        }

        return $this->db->count_all_results();
    }

    // ========================================================================
    // CÁLCULO DE ASISTENCIA DIARIA
    // ========================================================================

    /**
     * Etiqueta legible del método de verificación ZKTeco (VerifyType)
     */
    public function metodo_checada_etiqueta($metodo)
    {
        $mapa = [
            0  => 'Contraseña',
            1  => 'Huella',
            3  => 'Contraseña',
            15 => 'Rostro',
        ];

        $metodo = (int)$metodo;
        return isset($mapa[$metodo]) ? $mapa[$metodo] : 'Otro (' . $metodo . ')';
    }

    /**
     * Asigna tipo de checada por secuencia cronológica (reloj no envía tipo).
     * 1=entrada | 2=entrada+salida | 4=entrada+salida_comida+entrada_comida+salida
     */
    public function etiquetar_checadas_secuencia(array $checadas)
    {
        if (empty($checadas)) {
            return [];
        }

        usort($checadas, function ($a, $b) {
            return strtotime($a->fecha_hora) - strtotime($b->fecha_hora);
        });

        $tipos_por_cantidad = [
            1 => ['entrada'],
            2 => ['entrada', 'salida'],
            3 => ['entrada', 'checada_intermedia', 'salida'],
            4 => ['entrada', 'salida_comida', 'entrada_comida', 'salida'],
        ];

        $total = count($checadas);
        $tipos = isset($tipos_por_cantidad[$total])
            ? $tipos_por_cantidad[$total]
            : array_merge(
                ['entrada'],
                array_fill(0, max(0, $total - 2), 'checada_extra'),
                $total > 1 ? ['salida'] : []
            );

        $resultado = [];
        foreach ($checadas as $i => $checada) {
            $tipo = isset($tipos[$i]) ? $tipos[$i] : 'checada_extra';
            $resultado[] = [
                'id'           => $checada->id ?? null,
                'fecha_hora'   => $checada->fecha_hora,
                'hora'         => date('H:i:s', strtotime($checada->fecha_hora)),
                'metodo'       => (int)$checada->metodo,
                'metodo_label' => $this->metodo_checada_etiqueta($checada->metodo),
                'tipo'         => $tipo,
                'tipo_label'   => $this->tipo_checada_etiqueta($tipo),
                'dispositivo_sn' => $checada->dispositivo_sn ?? null,
            ];
        }

        return $resultado;
    }

    public function tipo_checada_etiqueta($tipo)
    {
        $mapa = [
            'entrada'            => 'Entrada',
            'salida'             => 'Salida',
            'salida_comida'      => 'Salida a comida',
            'entrada_comida'     => 'Regreso de comida',
            'checada_intermedia' => 'Checada intermedia',
            'checada_extra'      => 'Checada adicional',
        ];

        return isset($mapa[$tipo]) ? $mapa[$tipo] : ucfirst(str_replace('_', ' ', $tipo));
    }

    /**
     * Resumen diario de asistencias en un rango (para modal RH / reportes)
     */
    public function get_resumen_asistencias_periodo($empleado_id, $fecha_inicio, $fecha_fin, $horarios_por_dia = [])
    {
        $checadas = $this->get_asistencias_rango($fecha_inicio, $fecha_fin, $empleado_id);
        $por_fecha = [];

        foreach ($checadas as $checada) {
            $fecha = date('Y-m-d', strtotime($checada->fecha_hora));
            if (!isset($por_fecha[$fecha])) {
                $por_fecha[$fecha] = [];
            }
            $por_fecha[$fecha][] = $checada;
        }

        $dias = [];
        $cursor = strtotime($fecha_inicio);
        $fin = strtotime($fecha_fin);

        while ($cursor <= $fin) {
            $fecha = date('Y-m-d', $cursor);
            $checadas_dia = isset($por_fecha[$fecha]) ? $por_fecha[$fecha] : [];
            $horario_hoy = isset($horarios_por_dia[$fecha]) ? $horarios_por_dia[$fecha] : null;

            $dias[] = [
                'fecha'      => $fecha,
                'dia_semana' => $this->dia_semana_es($fecha),
                'checadas'   => $this->etiquetar_checadas_secuencia($checadas_dia),
                'calculo'    => $this->calcular_asistencia_diaria($empleado_id, $fecha, $horario_hoy),
            ];

            $cursor = strtotime('+1 day', $cursor);
        }

        return array_reverse($dias);
    }

    public function dia_semana_es($fecha)
    {
        $mapa = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ];

        return $mapa[date('l', strtotime($fecha))] ?? date('l', strtotime($fecha));
    }

    public function contar_checadas_empleado($empleado_id, $fecha_inicio, $fecha_fin)
    {
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('fecha_hora >=', $fecha_inicio . ' 00:00:00');
        $this->db->where('fecha_hora <=', $fecha_fin . ' 23:59:59');
        return (int)$this->db->count_all_results('asistencias');
    }

    /**
     * Calcula la asistencia diaria de un empleado, cruzando sus checadas
     * contra el horario asignado para detectar retardos, entradas/salidas faltantes, etc.
     *
     * @param int         $empleado_id ID del empleado
     * @param string      $fecha       Fecha en formato Y-m-d
     * @param object|null $horario_hoy Objeto del horario del día (desde HorariosModel)
     * @return array Resumen del cálculo
     */
    public function calcular_asistencia_diaria($empleado_id, $fecha, $horario_hoy = null)
    {
        // Obtener todas las checadas del día para este empleado
        $checadas = $this->get_asistencias_rango($fecha, $fecha, $empleado_id);

        $resultado = [
            'tiene_checadas'    => !empty($checadas),
            'total_checadas'    => count($checadas),
            'primera_checada'   => null,
            'ultima_checada'    => null,
            'tiene_horario'     => ($horario_hoy !== null),
            'entrada'           => null,
            'salida'            => null,
            'salida_comida'     => null,
            'entrada_comida'    => null,
            'duracion_comida_minutos' => 0,
            'retardo'           => false,
            'minutos_retardo'   => 0,
            'entrada_completa'  => false,
            'salida_completa'   => false,
            'horas_trabajadas'  => '00:00',
            'estado'            => 'Sin checadas',
        ];

        if (empty($checadas)) {
            return $resultado;
        }

        // Primera y última checada del día
        // Ordenar checadas cronológicamente para el cálculo
        usort($checadas, function($a, $b) {
            return strtotime($a->fecha_hora) - strtotime($b->fecha_hora);
        });

        $primera = $checadas[0];
        $ultima = $checadas[count($checadas) - 1];

        $resultado['primera_checada'] = $primera->fecha_hora;
        $resultado['ultima_checada'] = $ultima->fecha_hora;

        // Si no hay horario asignado, solo reportar checadas
        if (!$horario_hoy) {
            $resultado['estado'] = 'Sin horario asignado';
            $resultado['entrada'] = date('H:i', strtotime($primera->fecha_hora));
            $resultado['salida'] = date('H:i', strtotime($ultima->fecha_hora));
            
            // Si hay 4 checadas, asumir comida
            if (count($checadas) === 4) {
                $resultado['salida_comida'] = date('H:i', strtotime($checadas[1]->fecha_hora));
                $resultado['entrada_comida'] = date('H:i', strtotime($checadas[2]->fecha_hora));
            }
            return $resultado;
        }

        // Parsear horario del empleado
        $hora_entrada = substr($horario_hoy->hora_entrada, 0, 5);
        $hora_salida = substr($horario_hoy->hora_salida, 0, 5);
        $hora_salida_comida = isset($horario_hoy->hora_salida_comida) ? substr($horario_hoy->hora_salida_comida, 0, 5) : null;
        $hora_entrada_comida = isset($horario_hoy->hora_entrada_comida) ? substr($horario_hoy->hora_entrada_comida, 0, 5) : null;
        $tolerancia = isset($horario_hoy->tolerancia) ? (int)$horario_hoy->tolerancia : 0;

        $resultado['entrada_horario'] = $hora_entrada;
        $resultado['salida_horario'] = $hora_salida;
        $resultado['salida_comida_horario'] = $hora_salida_comida;
        $resultado['entrada_comida_horario'] = $hora_entrada_comida;

        // === Lógica de asignación de checadas ===
        $checada_entrada = null;
        $checada_salida = null;
        $checada_salida_comida = null;
        $checada_entrada_comida = null;

        // Asignar primera y última checada
        $checada_entrada = $checadas[0];
        $checada_salida = $checadas[count($checadas) - 1];

        // Tiempos reales de primera y última checada
        $resultado['entrada'] = date('H:i', strtotime($checada_entrada->fecha_hora));
        $resultado['salida'] = date('H:i', strtotime($checada_salida->fecha_hora));

        // Si hay 4 checadas, identificar comida
        if (count($checadas) === 4) {
            $resultado['salida_comida'] = date('H:i', strtotime($checadas[1]->fecha_hora));
            $resultado['entrada_comida'] = date('H:i', strtotime($checadas[2]->fecha_hora));
            $salida_comida_ts = strtotime($checadas[1]->fecha_hora);
            $entrada_comida_ts = strtotime($checadas[2]->fecha_hora);
            $resultado['duracion_comida_minutos'] = max(0, (int)round(($entrada_comida_ts - $salida_comida_ts) / 60));
        } elseif (count($checadas) >= 4 && $hora_salida_comida && $hora_entrada_comida) {
            // Buscar las checadas más cercanas a la hora de salida y entrada de comida
            $target_salida_comida_ts = strtotime($fecha . ' ' . $hora_salida_comida);
            $target_entrada_comida_ts = strtotime($fecha . ' ' . $hora_entrada_comida);

            $min_diff_salida_comida = PHP_INT_MAX;
            $min_diff_entrada_comida = PHP_INT_MAX;

            foreach ($checadas as $i => $checada) {
                if ($i === 0 || $i === count($checadas) - 1) continue; // Ignorar primera y última

                $checada_ts = strtotime($checada->fecha_hora);

                // Buscar salida de comida
                $diff_salida = abs($checada_ts - $target_salida_comida_ts);
                if ($diff_salida < $min_diff_salida_comida && $checada_ts > strtotime($checada_entrada->fecha_hora)) {
                    $min_diff_salida_comida = $diff_salida;
                    $checada_salida_comida = $checada;
                }

                // Buscar entrada de comida (debe ser después de salida comida)
                if ($checada_salida_comida && $checada_ts > strtotime($checada_salida_comida->fecha_hora)) {
                    $diff_entrada = abs($checada_ts - $target_entrada_comida_ts);
                    if ($diff_entrada < $min_diff_entrada_comida) {
                        $min_diff_entrada_comida = $diff_entrada;
                        $checada_entrada_comida = $checada;
                    }
                }
            }
            
            // Validar que las checadas de comida están en orden
            if ($checada_salida_comida && $checada_entrada_comida && strtotime($checada_salida_comida->fecha_hora) < strtotime($checada_entrada_comida->fecha_hora)) {
                $resultado['salida_comida'] = date('H:i', strtotime($checada_salida_comida->fecha_hora));
                $resultado['entrada_comida'] = date('H:i', strtotime($checada_entrada_comida->fecha_hora));
                
                $salida_comida_ts = strtotime($checada_salida_comida->fecha_hora);
                $entrada_comida_ts = strtotime($checada_entrada_comida->fecha_hora);
                $resultado['duracion_comida_minutos'] = round(($entrada_comida_ts - $salida_comida_ts) / 60);
            } else {
                // Si no se pueden identificar bien las checadas de comida, dejar como nulas
                $resultado['salida_comida'] = null;
                $resultado['entrada_comida'] = null;
                $resultado['duracion_comida_minutos'] = 0;
            }
        }
        
        // Calcular retardo en la primera checada vs hora de entrada
        $hora_primera = date('H:i', strtotime($checada_entrada->fecha_hora));
        $entrada_ts = strtotime($fecha . ' ' . $hora_entrada);
        $primera_ts = strtotime($fecha . ' ' . $hora_primera);

        $minutos_diferencia = ($primera_ts - $entrada_ts) / 60;

        if ($minutos_diferencia > $tolerancia) {
            $resultado['retardo'] = true;
            $resultado['minutos_retardo'] = (int)$minutos_diferencia;
        }

        // Determinar si la entrada fue completa (checó antes de 1 hora después de la entrada)
        if ($minutos_diferencia <= 60) {
            $resultado['entrada_completa'] = true;
        }

        // Determinar si la salida fue completa (última checada después o cerca de la hora de salida)
        $salida_ts = strtotime($fecha . ' ' . $hora_salida);
        $ultima_ts = strtotime($fecha . ' ' . date('H:i', strtotime($checada_salida->fecha_hora)));

        // La salida se considera completa si la última checada es >= hora_salida - 30min
        if ($ultima_ts >= ($salida_ts - 1800)) {
            $resultado['salida_completa'] = true;
        }

        // Calcular horas trabajadas (diferencia entre primera checada y última, restando tiempo de comida)
        $diff_segundos = 0;
        if ($checada_entrada && $checada_salida) {
            $diff_segundos = strtotime($checada_salida->fecha_hora) - strtotime($checada_entrada->fecha_hora);
            
            // Restar duración de comida si se identificó
            if ($resultado['duracion_comida_minutos'] > 0) {
                $diff_segundos -= ($resultado['duracion_comida_minutos'] * 60);
            }
        }
        
        if ($diff_segundos > 0) {
            $horas = floor($diff_segundos / 3600);
            $minutos = floor(($diff_segundos % 3600) / 60);
            $resultado['horas_trabajadas'] = str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);
        }

        // Determinar estado general
        if ($resultado['retardo'] && $resultado['entrada_completa']) {
            $resultado['estado'] = 'Con retardo';
        } elseif ($resultado['retardo']) {
            $resultado['estado'] = 'Retardo mayor';
        } elseif ($resultado['entrada_completa'] && $resultado['salida_completa']) {
            $resultado['estado'] = 'Asistencia completa';
        } elseif ($resultado['entrada_completa']) {
            $resultado['estado'] = 'Salida temprana';
        } else {
            $resultado['estado'] = 'Checadas parciales';
        }

        return $resultado;
    }
}
