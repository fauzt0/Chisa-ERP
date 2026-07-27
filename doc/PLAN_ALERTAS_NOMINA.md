# Plan de Alertas y Notificaciones — Módulo Nóminas

> **Estado:** Pendiente de implementación.
>
> **Versión:** 1.0 — Julio 2026
>
> **Dependencia:** Fase 1-4 de `PLAN_NOMINA_ITERACION2.md` y sistema de alertas general (`SISTEMA_ALERTAS_NOTIFICACIONES.md`).

---

## 1. Objetivo

Definir un sistema completo de alertas y notificaciones para el módulo de Nóminas que mantenga informados a los usuarios autorizados sobre eventos críticos del flujo de nómina: creación automática, cálculo completado, pagos realizados y nóminas vencidas.

---

## 2. Tipos de Alertas

### 2.1 Alertas Internas (Base de Datos)

Se almacenan en la tabla `alertas_internas` y se muestran mediante el sistema de notificaciones existente (campana en navbar superior).

#### Estructura de la tabla `alertas_internas`

```sql
CREATE TABLE IF NOT EXISTS `alertas_internas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL COMMENT 'Código del tipo de alerta',
  `titulo` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `modulo` varchar(100) DEFAULT NULL COMMENT 'Módulo de origen',
  `url` varchar(255) DEFAULT NULL COMMENT 'Link directo a la sección',
  `icono` varchar(100) DEFAULT NULL COMMENT 'Clase de icono (FontAwesome)',
  `destinatario_permiso` varchar(100) DEFAULT NULL COMMENT 'Permiso requerido para ver esta alerta',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) DEFAULT 0,
  `fecha_lectura` datetime DEFAULT NULL,
  `usuario_lectura` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_leida` (`leida`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_destinatario` (`destinatario_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alertas internas del sistema ERP';
```

### 2.2 Catálogo de Alertas de Nómina

| ID | Tipo | Gatillo | Prioridad | Destinatarios | Mensaje |
|:---|:-----|:--------|:----------|:--------------|:--------|
| NOM-01 | `nomina_creada` | Al crear nómina automáticamente | `info` | Usuarios con permiso `rh_nomina` | "Se generó la nómina **{folio}** ({tipo}) para el periodo {inicio} al {fin}. Estatus: Pendiente de cálculo." |
| NOM-02 | `nomina_calculada` | Al ejecutar cálculo de nómina | `success` | Usuarios con permiso `rh_nomina` | "La nómina **{folio}** ha sido calculada exitosamente. Total percepciones: ${monto}. Revise y procese el pago." |
| NOM-03 | `nomina_pagada` | Al completar pago total | `success` | Usuarios con permiso `contabilidad_nomina` | "La nómina **{folio}** ha sido pagada en su totalidad. Póliza generada: {poliza}. Total pagado: ${monto}." |
| NOM-04 | `nomina_vencida` | 3 días después de `fecha_pago` si estatus != 'Pagada' | `warning` | Usuarios con permiso `rh_nomina` | "⚠️ La nómina **{folio}** con vencimiento {fecha_pago} aún no ha sido pagada. Estatus: {estatus}." |
| NOM-05 | `nomina_pago_parcial` | Al procesar pago parcial | `warning` | Usuarios con permiso `rh_nomina` y `contabilidad_nomina` | "La nómina **{folio}** ha recibido un pago parcial de ${monto}. Restante: ${restante}." |
| NOM-06 | `empleado_sin_cuenta` | Si un empleado en nómina no tiene cuenta bancaria registrada | `danger` | Usuarios con permiso `rh_nomina` | "El empleado **{nombre}** (No. {numero}) no tiene cuenta bancaria registrada para depósito de nómina." |
| NOM-07 | `configuracion_cambiada` | Al modificar la configuración de automatización | `info` | Usuarios con permiso `rh_nomina` | "La configuración de automatización de nóminas fue modificada por {usuario}. Frecuencia: {frecuencia}, Auto-crear: {auto}." |

---

## 3. Puntos de Integración en el Código

### 3.1 Modelo `NominaRhModel.php`

#### 3.1.1 `_crear_alerta_interna($tipo, $datos)` — Método privado helper

```php
/**
 * Crea una alerta interna para los usuarios con el permiso indicado.
 *
 * @param string $tipo    Código del tipo de alerta (NOM-01, NOM-02, etc.)
 * @param array  $datos   Datos de la alerta: titulo, mensaje, modulo, url, icono, destinatario_permiso
 * @return int|false      ID de la alerta creada o false
 */
private function _crear_alerta_interna($tipo, $datos = []) {
    if (!$this->db->table_exists('alertas_internas')) {
        return false;
    }

    $insert = array_merge([
        'tipo'                  => $tipo,
        'titulo'                => '',
        'mensaje'               => '',
        'modulo'                => 'Recursos Humanos',
        'url'                   => 'rh/Nomina',
        'icono'                 => 'fa-money-bill-wave',
        'destinatario_permiso'  => 'rh_nomina',
        'fecha'                 => date('Y-m-d H:i:s'),
        'leida'                 => 0,
    ], $datos);

    $this->db->insert('alertas_internas', $insert);
    return $this->db->insert_id();
}
```

#### 3.1.2 Puntos de disparo en métodos existentes

##### A. En `crear_nomina_automatica()` — Alerta NOM-01

```php
// Al final del método, después de agregar empleados:
$this->_crear_alerta_interna('nomina_creada', [
    'titulo'   => 'Nómina generada automáticamente',
    'mensaje'  => "Se generó la nómina {$nomina->folio} ({$datos['tipo_nomina']}) para el periodo {$datos['periodo_inicio']} al {$datos['periodo_fin']}. Estatus: Pendiente de cálculo.",
    'url'      => 'rh/Nomina',
]);
```

##### B. En `calcular_nomina()` — Alerta NOM-02

```php
// Al final del método, después del return exitoso:
$this->_crear_alerta_interna('nomina_calculada', [
    'titulo'   => 'Nómina calculada exitosamente',
    'mensaje'  => "La nómina {$nomina->folio} ha sido calculada. Total percepciones: $" . number_format($total_percepciones, 2) . ". Revise y procese el pago.",
    'url'      => 'rh/Nomina',
]);
```

##### C. En `procesar_pagos_nomina()` — Alertas NOM-03 y NOM-05

```php
// Si pago es total (estatus pasa a 'Pagada'):
$this->_crear_alerta_interna('nomina_pagada', [
    'titulo'   => 'Nómina pagada en su totalidad',
    'mensaje'  => "La nómina {$nomina->folio} ha sido pagada en su totalidad. Total pagado: $" . number_format($monto_total, 2) . ".",
    'url'      => 'rh/Nomina',
    'destinatario_permiso' => 'contabilidad_nomina',
]);

// Si pago es parcial:
$this->_crear_alerta_interna('nomina_pago_parcial', [
    'titulo'   => 'Pago parcial de nómina',
    'mensaje'  => "La nómina {$nomina->folio} ha recibido un pago parcial de $" . number_format($monto, 2) . ". Restante: $" . number_format($restante, 2) . ".",
    'url'      => 'rh/Nomina',
    'icono'    => 'fa-exclamation-triangle',
]);
```

##### D. En `agregar_empleados_nomina()` — Alerta NOM-06

```php
// Por cada empleado sin cuenta bancaria:
if (empty($emp->cuenta_bancaria) || $emp->cuenta_bancaria === 'PENDIENTE') {
    $this->_crear_alerta_interna('empleado_sin_cuenta', [
        'titulo'   => 'Empleado sin cuenta bancaria',
        'mensaje'  => "El empleado {$emp->nombre} {$emp->apellido_paterno} (No. {$emp->numero_empleado}) no tiene cuenta bancaria registrada para depósito de nómina.",
        'url'      => 'rh/Nomina',
        'icono'    => 'fa-exclamation-circle',
    ]);
}
```

##### E. En `guardar_configuracion_automatizacion()` — Alerta NOM-07

```php
$this->_crear_alerta_interna('configuracion_cambiada', [
    'titulo'   => 'Configuración de automatización modificada',
    'mensaje'  => "La configuración de automatización de nóminas fue modificada. Frecuencia: {$data['frecuencia']}, Auto-crear: " . ($data['auto_crear'] ? 'Activado' : 'Desactivado') . ".",
    'url'      => 'rh/Nomina',
    'icono'    => 'fa-cog',
]);
```

---

## 4. Integración con el Sistema de Notificaciones Existente

### 4.1 Modificar `Notifications.php`

Agregar un nuevo bloque en el método `get_notifications` para incluir las alertas de nómina:

```php
// ===== ALERTAS DE NÓMINA =====
$alertas_nomina = $this->db
    ->select('a.*')
    ->from('alertas_internas a')
    ->where('a.modulo', 'Recursos Humanos')
    ->where('a.destinatario_permiso IS NOT NULL')
    ->where('a.leida', 0)
    ->where("(a.destinatario_permiso = '' OR a.destinatario_permiso IS NULL OR EXISTS (
        SELECT 1 FROM privilege p 
        WHERE p.admin = " . (int)$user_id . " 
        AND p.permiso = a.destinatario_permiso 
        AND p.valor = 1
    ))")
    ->order_by('a.fecha', 'DESC')
    ->limit(10)
    ->get()
    ->result();

foreach ($alertas_nomina as $alerta) {
    $notifications[] = [
        'type'    => strpos($alerta->tipo, 'pago_parcial') !== false || strpos($alerta->tipo, 'vencida') !== false ? 'warning' :
                     (strpos($alerta->tipo, 'sin_cuenta') !== false ? 'danger' : 'info'),
        'icon'    => $alerta->icono ?: 'fa-bell',
        'module'  => $alerta->modulo,
        'title'   => $alerta->titulo,
        'message' => $alerta->mensaje,
        'link'    => base_url($alerta->url),
        'time'    => $alerta->fecha,
        'id'      => $alerta->id,
        'tipo'    => $alerta->tipo,
    ];
}
```

### 4.2 Marcar alertas como leídas

Agregar endpoint en `Notifications.php` o en un nuevo método de `rh/Nomina.php`:

```php
public function marcar_alerta_leida($id = null) {
    $id = (int)$id;
    $user_id = $this->session->userdata('user_id');

    $this->db->where('id', $id)
             ->update('alertas_internas', [
                 'leida'           => 1,
                 'fecha_lectura'   => date('Y-m-d H:i:s'),
                 'usuario_lectura' => $user_id,
             ]);

    echo json_encode(['success' => true]);
}
```

---

## 5. CRON Job para Nóminas Vencidas (NOM-04)

### 5.1 Método en `NominaRhModel.php`

```php
/**
 * Verifica nóminas vencidas (3+ días después de fecha_pago sin estar pagadas)
 * y genera alertas NOM-04.
 *
 * @return int Número de alertas generadas
 */
public function verificar_nominas_vencidas() {
    $fecha_limite = date('Y-m-d', strtotime('-3 days'));

    $nominas_vencidas = $this->db
        ->select('id, folio, tipo_nomina, periodo_inicio, periodo_fin, fecha_pago, estatus')
        ->from('nominas')
        ->where('fecha_pago <=', $fecha_limite)
        ->where('estatus !=', 'Pagada')
        ->where('estatus !=', 'Cancelada')
        ->get()
        ->result();

    $count = 0;
    foreach ($nominas_vencidas as $n) {
        // Verificar si ya se generó alerta para esta nómina hoy
        $ya_alerta = $this->db
            ->where('tipo', 'nomina_vencida')
            ->where('mensaje LIKE', "%Nómina {$n->folio}%")
            ->where('DATE(fecha)', date('Y-m-d'))
            ->count_all_results('alertas_internas');

        if ($ya_alerta > 0) continue;

        $this->_crear_alerta_interna('nomina_vencida', [
            'titulo'   => 'Nómina vencida sin pagar',
            'mensaje'  => "⚠️ La nómina {$n->folio} ({$n->tipo_nomina}) con fecha de pago {$n->fecha_pago} aún no ha sido pagada completamente. Estatus actual: {$n->estatus}.",
            'url'      => 'rh/Nomina',
            'icono'    => 'fa-clock',
        ]);
        $count++;
    }

    return $count;
}
```

### 5.2 Endpoint en `rh/Nomina.php`

```php
/**
 * Endpoint para CRON job que verifica nóminas vencidas.
 * Llamada: php index.php rh/Nomina verificar_vencidas
 */
public function verificar_vencidas() {
    // Solo permitir ejecución por CLI o con token de seguridad
    if (!is_cli() && $this->input->get('token') !== 'CHISA_CRON_INTERNO_2024') {
        show_404();
        return;
    }

    $count = $this->NominaRhModel->verificar_nominas_vencidas();
    echo "Verificación completada. {$count} alerta(s) generada(s).\n";
}
```

### 5.3 Entrada CRON

```bash
# Ejecutar diariamente a las 07:00 AM para verificar nóminas vencidas
0 7 * * * /usr/bin/php /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html/index.php rh/Nomina verificar_vencidas

# Ejecutar diariamente a las 06:00 AM para verificar creación automática
0 6 * * * /usr/bin/php /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html/index.php rh/Nomina verificar_auto_nomina_ajax
```

---

## 6. Integración Futura con Correo Electrónico

### 6.1 Configuración SMTP

Agregar en `application/config/email.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol']   = 'smtp';
$config['smtp_host']  = 'smtp.chisarecubrimientos.com.mx';
$config['smtp_port']  = 587;
$config['smtp_user']  = 'erp@chisarecubrimientos.com.mx';
$config['smtp_pass']  = getenv('ERP_SMTP_PASSWORD') ?: '***';
$config['smtp_crypto'] = 'tls';
$config['mailtype']   = 'html';
$config['charset']    = 'utf-8';
$config['newline']    = "\r\n";
```

### 6.2 Helper de envío de correos

Crear `application/helpers/notificaciones_helper.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Envía una alerta por correo electrónico a los administradores con un permiso específico.
 *
 * @param string $tipo_alerta   Tipo de alerta (nomina_creada, nomina_calculada, etc.)
 * @param array  $datos         Datos de la alerta: asunto, mensaje_html, permiso_requerido
 * @return array                Resultados de envío por destinatario
 */
function enviar_alerta_email($tipo_alerta, $datos = []) {
    $CI =& get_instance();
    $CI->load->library('email');

    $permiso = $datos['permiso_requerido'] ?? 'rh_nomina';

    // Obtener usuarios con el permiso requerido
    $CI->db->select('u.id, u.email, u.nombre, u.apellido_paterno');
    $CI->db->from('usuarios u');
    $CI->db->join('privilege p', 'p.admin = u.id');
    $CI->db->where('p.permiso', $permiso);
    $CI->db->where('p.valor', 1);
    $CI->db->where('u.activo', 1);
    $CI->db->where('u.email IS NOT NULL');
    $CI->db->where("u.email != ''");
    $destinatarios = $CI->db->get()->result();

    $resultados = [];
    foreach ($destinatarios as $dest) {
        $CI->email->clear();
        $CI->email->to($dest->email);
        $CI->email->from('erp@chisarecubrimientos.com.mx', 'ERP CHISa Recubrimientos');
        $CI->email->subject($datos['asunto'] ?? 'Notificación de Nómina - ERP CHISa');

        // Plantilla HTML del correo
        $mensaje = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: #1E3A5F; padding: 20px; text-align: center;">
                <h2 style="color: #fff; margin: 0;">ERP CHISa Recubrimientos</h2>
            </div>
            <div style="padding: 20px; border: 1px solid #ddd;">
                <h3>' . ($datos['titulo'] ?? 'Notificación de Nómina') . '</h3>
                <p>' . ($datos['mensaje_html'] ?? '') . '</p>
                <hr>
                <p style="font-size: 12px; color: #888;">
                    Este es un correo automático del sistema ERP CHISa. 
                    <br>Para acceder al sistema: <a href="' . base_url() . '">' . base_url() . '</a>
                </p>
            </div>
        </div>';

        $CI->email->message($mensaje);
        $enviado = $CI->email->send();
        $resultados[$dest->email] = $enviado;
    }

    return $resultados;
}
```

### 6.3 Puntos de integración para correos

| Punto de disparo | Archivo | Método | Alerta email |
|:-----------------|:--------|:-------|:-------------|
| Creación automática | `NominaRhModel.php` | `crear_nomina_automatica()` | `nomina_creada` |
| Cálculo completado | `NominaRhModel.php` | `calcular_nomina()` | `nomina_calculada` |
| Pago completado | `NominaRhModel.php` | `procesar_pagos_nomina()` | `nomina_pagada` |
| Pago parcial | `NominaRhModel.php` | `procesar_pagos_nomina()` | `nomina_pago_parcial` |
| Nómina vencida | CRON job | `verificar_nominas_vencidas()` | `nomina_vencida` |
| Empleado sin cuenta | `NominaRhModel.php` | `agregar_empleados_nomina()` | `empleado_sin_cuenta` |

---

## 7. Permisos Requeridos

### 7.1 Permisos existentes (ya en `config/permissions.php`)

| Código | Descripción | Módulo | Usado en alertas |
|:-------|:------------|:-------|:-----------------|
| `rh_nomina` | Gestionar Nómina | Recursos Humanos | NOM-01, NOM-02, NOM-04, NOM-06, NOM-07 |
| `contabilidad_nomina` | Gestionar Nómina (Contabilidad) | Contabilidad | NOM-03, NOM-05 |

### 7.2 Nuevos permisos requeridos para funcionalidades de Iteración 2

| Código | Descripción | Módulo | Necesario para |
|:-------|:------------|:-------|:---------------|
| `rh_nomina_configurar` | Configurar Automatización de Nóminas | Recursos Humanos | Acceso al modal de configuración de automatización y endpoint `guardar_configuracion_ajax` |
| `rh_nomina_editar_detalle` | Editar Detalle de Nómina | Recursos Humanos | Edición inline de campos en el modal de detalle (`actualizar_detalle_ajax`) |
| `rh_nomina_cuentas` | Gestionar Cuentas Bancarias de Empleados | Recursos Humanos | CRUD de cuentas bancarias desde el modal de nómina |
| `rh_nomina_exportar` | Exportar Nómina a Excel | Recursos Humanos | Endpoint `exportar_detalle_excel` |

---

## 8. Plan de Implementación

### Fase A: Base de Datos
1. Crear/verificar tabla `alertas_internas` con la estructura definida en la sección 2.1
2. Agregar los nuevos permisos en la tabla `permisos_config` (o equivalente) y en `config/permissions.php`

### Fase B: Modelo
1. Agregar método privado `_crear_alerta_interna()` en `NominaRhModel.php`
2. Integrar disparos de alertas en cada método según sección 3.1.2
3. Agregar método `verificar_nominas_vencidas()`

### Fase C: Controlador
1. Agregar endpoint `verificar_vencidas()` en `rh/Nomina.php`
2. Agregar endpoint `marcar_alerta_leida()` 
3. Agregar verificaciones de permisos en endpoints sensibles

### Fase D: Notificaciones
1. Modificar `Notifications.php` para incluir alertas de nómina
2. Registrar entrada CRON en el servidor

### Fase E: Correo Electrónico (Futuro)
1. Configurar `application/config/email.php`
2. Crear `application/helpers/notificaciones_helper.php`
3. Integrar envíos de correo en cada punto de disparo

---

## 9. Resumen de Archivos a Modificar/Crear

| Archivo | Acción |
|:--------|:-------|
| `doc/PLAN_ALERTAS_NOMINA.md` | **CREADO** — Este documento |
| `database/alertas_internas.sql` | **CREAR** — Estructura de tabla |
| `application/config/permissions.php` | **MODIFICAR** — Agregar 4 nuevos permisos |
| `application/models/RH/NominaRhModel.php` | **MODIFICAR** — Agregar `_crear_alerta_interna()`, `verificar_nominas_vencidas()`, integrar disparos |
| `application/controllers/rh/Nomina.php` | **MODIFICAR** — Agregar `verificar_vencidas()`, `marcar_alerta_leida()`, verificaciones de permisos |
| `application/controllers/Notifications.php` | **MODIFICAR** — Incluir alertas de nómina en `get_notifications` |
| `application/config/email.php` | **CREAR** (futuro) — Configuración SMTP |
| `application/helpers/notificaciones_helper.php` | **CREAR** (futuro) — Helper de envío de correos |

---

*ERP Chisa Recubrimientos — Documento de Arquitectura de Alertas — Nóminas v1.0*
