<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RelojSyncRhModel — Sincronización forzada de empleados RH hacia reloj ZKTeco
 *
 * Encola un DATA USER por empleado activo (formato proxy de planta):
 * PIN corto secuencial (2…201); Name = nombre en reloj; TAB real entre campos.
 * Editar = mismo comando con el mismo PIN (sobrescribe en el dispositivo).
 *
 * @package ChisaERP
 * @subpackage Reloj
 */
class RelojSyncRhModel extends CI_Model {

    /** Primer PIN para empleados (PIN 1 = admin físico del reloj) */
    const PIN_INICIO_EMPLEADOS = 2;

    /** Máximo de empleados activos a sincronizar por corrida (PIN 2 … 201) */
    const MAX_EMPLEADOS_SYNC = 200;

    /** @var bool|null */
    private $campos_reloj_empleados = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Reloj/RelojModel');
        $this->load->model('RH/EmpleadoModel');
    }

    /**
     * Invalida caché tras aplicar migración
     */
    public function reset_cache_campos_reloj()
    {
        $this->campos_reloj_empleados = null;
    }

    /**
     * Columnas reloj_pin / reloj_nombre_meta / reloj_sync_at en empleados
     */
    public function empleados_tiene_campos_reloj()
    {
        if ($this->campos_reloj_empleados === null) {
            $this->campos_reloj_empleados = $this->db->table_exists('empleados')
                && $this->db->field_exists('reloj_pin', 'empleados')
                && $this->db->field_exists('reloj_nombre_meta', 'empleados')
                && $this->db->field_exists('reloj_sync_at', 'empleados');
        }
        return $this->campos_reloj_empleados;
    }

    /**
     * Crea columnas e índice en empleados (equivalente a database/reloj_sync_empleados_rh.sql)
     */
    public function aplicar_migracion_empleados_reloj()
    {
        if (!$this->db->table_exists('empleados')) {
            return ['success' => false, 'message' => 'La tabla empleados no existe en esta base de datos'];
        }

        if ($this->empleados_tiene_campos_reloj()) {
            return [
                'success'         => true,
                'message'         => 'La estructura ya estaba aplicada',
                'campos_reloj_ok' => true,
                'aplicado'        => [],
            ];
        }

        $aplicado = [];

        if (!$this->db->field_exists('reloj_pin', 'empleados')) {
            $ok = $this->db->query(
                'ALTER TABLE `empleados` ADD COLUMN `reloj_pin` INT(11) UNSIGNED DEFAULT NULL
                 COMMENT \'PIN en reloj (= numero_empleado)\' AFTER `numero_empleado`'
            );
            if (!$ok) {
                return ['success' => false, 'message' => 'Error al crear reloj_pin: ' . $this->db->error()['message']];
            }
            $aplicado[] = 'reloj_pin';
        }

        $this->reset_cache_campos_reloj();

        if (!$this->db->field_exists('reloj_nombre_meta', 'empleados')) {
            $ok = $this->db->query(
                'ALTER TABLE `empleados` ADD COLUMN `reloj_nombre_meta` VARCHAR(24) DEFAULT NULL
                 COMMENT \'Name en comando DATA USER\' AFTER `reloj_pin`'
            );
            if (!$ok) {
                return ['success' => false, 'message' => 'Error al crear reloj_nombre_meta: ' . $this->db->error()['message']];
            }
            $aplicado[] = 'reloj_nombre_meta';
        }

        $this->reset_cache_campos_reloj();

        if (!$this->db->field_exists('reloj_sync_at', 'empleados')) {
            $ok = $this->db->query(
                'ALTER TABLE `empleados` ADD COLUMN `reloj_sync_at` DATETIME DEFAULT NULL
                 COMMENT \'Última sync forzada al reloj\' AFTER `reloj_nombre_meta`'
            );
            if (!$ok) {
                return ['success' => false, 'message' => 'Error al crear reloj_sync_at: ' . $this->db->error()['message']];
            }
            $aplicado[] = 'reloj_sync_at';
        }

        $idx = $this->db->query("SHOW INDEX FROM `empleados` WHERE Key_name = 'idx_empleados_reloj_pin'");
        if ($idx && $idx->num_rows() === 0) {
            $ok = $this->db->query('ALTER TABLE `empleados` ADD KEY `idx_empleados_reloj_pin` (`reloj_pin`)');
            if (!$ok) {
                return ['success' => false, 'message' => 'Error al crear índice: ' . $this->db->error()['message']];
            }
            $aplicado[] = 'idx_empleados_reloj_pin';
        }

        $this->reset_cache_campos_reloj();

        return [
            'success'         => true,
            'message'         => empty($aplicado)
                ? 'Estructura lista'
                : 'Migración aplicada: ' . implode(', ', $aplicado),
            'campos_reloj_ok' => $this->empleados_tiene_campos_reloj(),
            'aplicado'        => $aplicado,
        ];
    }

    /**
     * PIN más alto asignable a empleados (2 + MAX − 1 = 201)
     */
    public function pin_maximo_empleados_sync()
    {
        return self::PIN_INICIO_EMPLEADOS + self::MAX_EMPLEADOS_SYNC - 1;
    }

    /**
     * Name= en el reloj: nombre completo legible (sin tabs ni saltos)
     */
    public function generar_nombre_reloj_display($nombre, $apellido_paterno, $apellido_materno = '')
    {
        $partes = array_filter([
            trim((string)$nombre),
            trim((string)$apellido_paterno),
            trim((string)$apellido_materno),
        ], function ($p) {
            return $p !== '';
        });

        return $this->RelojModel->sanitizar_nombre_reloj(implode(' ', $partes));
    }

    /**
     * Empleados activos ordenados para sincronización
     */
    public function get_empleados_activos_para_sync()
    {
        $select = 'id, numero_empleado, nombre, apellido_paterno, apellido_materno';
        if ($this->empleados_tiene_campos_reloj()) {
            $select .= ', reloj_pin, reloj_nombre_meta';
        }

        return $this->db
            ->select($select)
            ->from('empleados')
            ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
            ->order_by('id', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Vista previa sin encolar (para confirmación en UI)
     */
    public function generar_preview()
    {
        $filas = $this->armar_filas_sync_empleados();
        $omitidos = 0;
        $ejemplo_comando = null;

        foreach ($filas as $f) {
            if (!empty($f['omitido'])) {
                $omitidos++;
                continue;
            }
            if ($ejemplo_comando === null && !empty($f['ejemplo_comando'])) {
                $ejemplo_comando = $f['ejemplo_comando'];
            }
        }

        return [
            'total_empleados'     => count($filas),
            'total_encolables'    => count($filas) - $omitidos,
            'total_omitidos'      => $omitidos,
            'pin_inicio'          => self::PIN_INICIO_EMPLEADOS,
            'pin_maximo'          => $this->pin_maximo_empleados_sync(),
            'pin_admin_protegido' => RelojModel::PIN_ADMIN_RELOJ,
            'ejemplo_comando'     => $ejemplo_comando,
            'empleados'           => $filas,
        ];
    }

    /**
     * Asigna PIN corto secuencial (2, 3, 4 …) por orden de id RH — máx. 200 empleados.
     *
     * @return array<int, array>
     */
    private function armar_filas_sync_empleados()
    {
        $empleados = $this->get_empleados_activos_para_sync();
        $filas = [];
        $pin_actual = self::PIN_INICIO_EMPLEADOS;
        $pin_max = $this->pin_maximo_empleados_sync();

        foreach ($empleados as $emp) {
            $nombre_reloj = $this->generar_nombre_reloj_display(
                $emp->nombre,
                $emp->apellido_paterno,
                $emp->apellido_materno
            );

            $motivo_omitido = null;
            $pin = null;
            $ejemplo = null;

            if ($nombre_reloj === '') {
                $motivo_omitido = 'Nombre vacío';
            } elseif ($pin_actual > $pin_max) {
                $motivo_omitido = 'Límite de ' . self::MAX_EMPLEADOS_SYNC . ' empleados en reloj (PIN máx. ' . $pin_max . ')';
            } else {
                $pin = $pin_actual;
                $pin_actual++;
                $cmd = $this->RelojModel->build_comando_data_user($pin, $nombre_reloj, '');
                if ($cmd !== false) {
                    $ejemplo = str_replace($this->RelojModel->adms_tab(), '⇥', $cmd);
                }
            }

            $filas[] = [
                'empleado_id'       => (int)$emp->id,
                'numero_empleado'   => $emp->numero_empleado,
                'nombre_completo'   => trim($emp->nombre . ' ' . $emp->apellido_paterno . ' ' . $emp->apellido_materno),
                'pin_asignado'      => $pin,
                'reloj_nombre_meta' => $nombre_reloj,
                'pin_anterior'      => isset($emp->reloj_pin) ? $emp->reloj_pin : null,
                'omitido'           => $motivo_omitido !== null,
                'motivo_omitido'    => $motivo_omitido,
                'ejemplo_comando'   => $ejemplo,
            ];
        }

        return $filas;
    }

    /**
     * Encola DATA USER por empleado activo (mismo formato que proxy de planta)
     */
    public function ejecutar_sincronizacion_forzada($dispositivo_sn, $creado_por = null)
    {
        $dispositivo_sn = trim((string)$dispositivo_sn);
        if ($dispositivo_sn === '') {
            return ['success' => false, 'message' => 'Debe seleccionar un dispositivo'];
        }

        if (!$this->empleados_tiene_campos_reloj()) {
            return [
                'success' => false,
                'message' => 'Ejecute la migración database/reloj_sync_empleados_rh.sql en empleados antes de sincronizar',
            ];
        }

        $dispositivo = $this->db
            ->where('sn', $dispositivo_sn)
            ->where('activo', 1)
            ->get('reloj_dispositivos')
            ->row();

        if (!$dispositivo) {
            return ['success' => false, 'message' => 'Dispositivo no encontrado o inactivo'];
        }

        $empleados = $this->get_empleados_activos_para_sync();
        if (count($empleados) === 0) {
            return [
                'success' => false,
                'message' => 'No hay empleados activos o en reingreso para sincronizar al reloj',
            ];
        }

        $stats = [
            'success'              => true,
            'message'              => 'Sincronización encolada correctamente',
            'dispositivo_sn'       => $dispositivo_sn,
            'cola_vaciada'         => 0,
            'comandos_alta'        => 0,
            'empleados_sync'       => 0,
            'empleados_omitidos'   => 0,
            'ultimo_error_encolar' => null,
            'ejemplo_user'         => null,
            'nota_proxy'           => 'DATA USER: PIN corto 2…' . $this->pin_maximo_empleados_sync() . ', Name=nombre completo, TAB real.',
        ];

        $stats['cola_vaciada'] = $this->RelojModel->vaciar_cola_comandos_dispositivo($dispositivo_sn);

        $this->db->trans_start();

        $ahora = date('Y-m-d H:i:s');
        $filas = $this->armar_filas_sync_empleados();

        foreach ($filas as $f) {
            if (!empty($f['omitido'])) {
                $stats['empleados_omitidos']++;
                continue;
            }

            $pin = (int)$f['pin_asignado'];
            $nombre_reloj = $f['reloj_nombre_meta'];

            $this->db->where('id', $f['empleado_id'])->update('empleados', [
                'reloj_pin'         => $pin,
                'reloj_nombre_meta' => $nombre_reloj,
                'reloj_sync_at'     => $ahora,
            ]);

            $comando = $this->RelojModel->build_comando_data_user($pin, $nombre_reloj, '');
            if ($comando === false) {
                $stats['empleados_omitidos']++;
                continue;
            }

            if ($this->RelojModel->encolar_comando($dispositivo_sn, $comando, $creado_por, true, true)) {
                $stats['comandos_alta']++;
                if ($stats['ejemplo_user'] === null) {
                    $stats['ejemplo_user'] = str_replace(
                        $this->RelojModel->adms_tab(),
                        '⇥',
                        $comando
                    );
                }
            } elseif (empty($stats['ultimo_error_encolar'])) {
                $stats['ultimo_error_encolar'] = $this->RelojModel->get_ultimo_error_encolar();
            }

            $stats['empleados_sync']++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return [
                'success' => false,
                'message' => 'Error en base de datos al encolar la sincronización',
            ];
        }

        if ($stats['comandos_alta'] === 0) {
            $msg = 'No se encoló ningún comando DATA USER. Revise nombres y el límite de ' . self::MAX_EMPLEADOS_SYNC . ' empleados.';
            if (!empty($stats['ultimo_error_encolar'])) {
                $msg .= ' Detalle: ' . $stats['ultimo_error_encolar'];
            }
            if ($stats['empleados_omitidos'] > 0) {
                $msg .= ' Omitidos: ' . $stats['empleados_omitidos'] . '.';
            }
            return [
                'success'            => false,
                'message'            => $msg,
                'empleados_omitidos' => $stats['empleados_omitidos'],
            ];
        }

        $stats['pendientes_sn'] = $this->RelojModel->contar_comandos_pendientes($dispositivo_sn);

        $this->RelojModel->registrar_sync_log(
            $dispositivo_sn,
            'comandos',
            sprintf(
                'Sync RH: %d DATA USER, %d empleados (%d omitidos)',
                $stats['comandos_alta'],
                $stats['empleados_sync'],
                $stats['empleados_omitidos']
            ),
            $stats['comandos_alta']
        );

        return $stats;
    }
}
