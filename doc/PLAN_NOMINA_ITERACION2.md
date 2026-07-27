# PLAN DE EJECUCIÓN — MÓDULO NÓMINAS ITERACIÓN 2

> **Objetivo:** Desarrollar la segunda iteración del módulo de Nóminas (Controlador/Vista `rh/Nomina`) conforme a los requerimientos del cliente CHISa.
>
> **Ejecutor:** Agente de IA (Composer 2.5) en CodeIgniter 3 + Bootstrap 5 + jQuery + DataTables + SweetAlert2.
>
> **Documentación base de referencia:**
> - `doc/DOCUMENTACION_TECNICA.md` — Arquitectura, estándares MY_Controller/MY_Model, permisos, DataTables.
> - `doc/imagenes_nomima_iteracion1/entrenamiento1.jpg`, `entrenamiento2.jpg`, `entrenamiento3.jpg` — Formato visual de tablas de nómina.

---

## Índice de Fases

| Fase | Descripción |
|:-----|:------------|
| [Fase 1](#fase-1-cambios-en-base-de-datos) | Cambios en Base de Datos (SQL) |
| [Fase 2](#fase-2-actualización-de-modelos) | Actualización de Modelos |
| [Fase 3](#fase-3-lógica-del-controlador) | Lógica del Controlador `rh/Nomina` |
| [Fase 4](#fase-4-vistas-y-ui) | Vistas y UI |
| [Fase 5](#fase-5-documentación-de-alertas) | Documentación de Alertas |

---

## Estado de Ejecución (Seguimiento)

> **Última actualización:** 24 de julio de 2026, 18:49 (UTC-6)
>
> **Responsables:** Composer 2.5 (implementación principal), Grok (seeders y fixes), Claude (verificación, permisos, plan de alertas)

### Resumen global

| Fase | Descripción | Estado |
|:-----|:------------|:-------|
| Fase 1 | Cambios en Base de Datos (4 scripts SQL) | ✅ Completada |
| Fase 2 | Actualización de Modelos (12+ métodos nuevos, 2 modificados) | ✅ Completada |
| Fase 3 | Lógica del Controlador (10 endpoints AJAX nuevos + exportación) | ✅ Completada |
| Fase 4 | Vistas y UI (3 modales nuevos, selector fechas, JS completo) | ✅ Completada |
| Fase 5 | Documentación de Alertas (`PLAN_ALERTAS_NOMINA.md`) | ✅ Completada |
| **Extra** | Permisos granulares (4 nuevos permisos + 22 endpoints protegidos) | ✅ Completada |
| **Extra** | Seeders de prueba (27 nóminas con múltiples estatus) | ✅ Completada |
| **Extra** | Fix cron: `is_cli()` en `verificar_auto_nomina_ajax` | ✅ Completada |
| **Pendiente** | Sistema de alertas en tiempo real (Fases A-D de `PLAN_ALERTAS_NOMINA.md`) | 🔜 Siguiente |

### Checklist del plan original

1. **[x]** Fase 1: Ejecutar los 4 scripts SQL en `database/` en orden.
2. **[x]** Fase 2: Modificar `NominaRhModel.php` (agregar 12+ métodos nuevos, modificar 2 existentes).
3. **[x]** Fase 2: Verificar `NominaModel.php` (el SELECT `nd.*` ya cubre nuevas columnas).
4. **[x]** Fase 2: Verificar `EmpleadoModel.php` (campo `costo_hora_extra`).
5. **[x]** Fase 3: Agregar `$this->load->helper('permissions')` y `$this->config->load('permissions')` al constructor de `Nomina.php`.
6. **[x]** Fase 3: Agregar los 10 nuevos endpoints AJAX listados en la tabla de la Fase 3.
7. **[x]** Fase 3: Agregar endpoint `get_catalogo_bancos_ajax`.
8. **[x]** Fase 4: Agregar el HTML de los 3 nuevos modales al final de `main.php`.
9. **[x]** Fase 4: Agregar el selector de fechas inteligente (reemplazar date-pickers).
10. **[x]** Fase 4: Agregar todo el JavaScript de la sección 4.3.
11. **[x]** Fase 4: Modificar el botón `verNomina` en `lista_ajax()` para apuntar al nuevo endpoint.
12. **[x]** Fase 4: Agregar método `exportar_detalle_excel($id)`.
13. **[x]** Fase 5: Crear archivo `doc/PLAN_ALERTAS_NOMINA.md`.

### Trabajo adicional (fuera del plan original)

#### Permisos granulares

Se agregaron **4 nuevos permisos** en `config/permissions.php` y se protegieron **22 métodos** con `requiere_permiso()`:

| Permiso | Métodos protegidos |
|:--------|:-------------------|
| `rh_nomina` | `index`, `lista_ajax`, `crear_ajax`, `calcular_ajax`, `pagar_ajax`, `detalle_pago_ajax`, `get_nomina_ajax`, `eliminar_ajax`, `imprimir_recibos`, `get_recibos_ajax`, `get_nomina_detalle_completo_ajax`, `verificar_auto_nomina_ajax`, `get_catalogo_bancos_ajax` (13) |
| `rh_nomina_configurar` | `get_configuracion_ajax`, `guardar_configuracion_ajax` (2) |
| `rh_nomina_editar_detalle` | `actualizar_detalle_ajax` (1) |
| `rh_nomina_cuentas` | `get_nomina_cuentas_ajax`, `guardar_cuenta_empleado_ajax`, `eliminar_cuenta_empleado_ajax`, `set_cuenta_default_ajax` (4) |
| `rh_nomina_exportar` | `exportar_excel`, `exportar_detalle_excel` (2) |

Los nuevos permisos aparecen automáticamente en `usuarios/GestionUsuarios/alta`, `usuarios/GestionUsuarios/editar/{id}` y `usuarios/Roles/editar/{id}`.

#### Seeders de prueba

| Archivo | Contenido |
|:--------|:----------|
| `database/seed_nominas_demo.sql` | 4 nóminas demo (NOM000002-000005): Pagada, Pagada, Calculada, Borrador |
| `database/run_seed_nominas_demo.php` | Ejecutor CLI/web con `apply`/`revert` |
| `database/run_seed_nominas_meses_pasados.php` | 21 nóminas históricas feb-jun 2026 con estatus variados (NOM000100-000120) |

**Nóminas en base de datos (27 total):**

| Folio | Periodo | Estatus | Empleados |
|:------|:--------|:--------|:----------|
| NOM000001 | 01-05 jul 2026 | Calculada | (original) |
| NOM000002 | 06-12 jul 2026 | Pagada | 11 |
| NOM000003 | 29 jun-05 jul 2026 | Pagada | 11 |
| NOM000004 | 13-19 jul 2026 | Calculada | 11 |
| NOM000005 | 20-26 jul 2026 | Borrador | 11 |
| NOM000006 | 27 jul-02 ago 2026 | Borrador | 11 (automática) |
| NOM000100-120 | feb-jun 2026 | Mixtos | 11 c/u |

**Distribución:** 19 Pagadas · 5 Calculadas · 1 Parcial (NOM000112) · 2 Borrador

#### Fix para ejecución desde Cron

Se modificó `verificar_auto_nomina_ajax()` para omitir `requiere_permiso()` cuando se ejecuta vía CLI:

```php
public function verificar_auto_nomina_ajax() {
    if (!is_cli()) {
        $this->requiere_permiso('rh_nomina');
    }
    // ...
}
```

**Cron pendiente de instalar en el servidor:**

```bash
0 7 * * * /usr/bin/php /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html/index.php rh/Nomina verificar_auto_nomina_ajax
```

> **Nota:** La lógica de `verificar_creacion_automatica()` solo crea nóminas Semanal los lunes. La próxima será el lunes 27 de julio de 2026.

#### Configuración de automatización activa

| Campo | Valor |
|:------|:------|
| Frecuencia | Semanal |
| Auto-crear | Activado |
| Días de anticipación | 1 |
| Última ejecución | 2026-07-24 16:27:58 |

---

## Fase 1: Cambios en Base de Datos

### 1.1 Nueva tabla: `empleados_cuentas_bancarias`

Relación uno a muchos: múltiples cuentas bancarias por empleado. La tabla `cuentas_bancarias` existente sirve como catálogo de bancos.

```sql
-- Archivo a crear: database/empleados_cuentas_bancarias.sql

CREATE TABLE IF NOT EXISTS `empleados_cuentas_bancarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `cuenta_bancaria_id` int(11) DEFAULT NULL COMMENT 'FK a cuentas_bancarias (catálogo de bancos)',
  `numero_cuenta` varchar(50) NOT NULL COMMENT 'Número de cuenta del empleado',
  `clabe` varchar(18) DEFAULT NULL COMMENT 'CLABE interbancaria del empleado',
  `es_default` tinyint(1) DEFAULT 0 COMMENT '1 = cuenta principal para depósito de nómina',
  `estatus` tinyint(1) DEFAULT 1 COMMENT '0=Inactiva, 1=Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_cuenta_bancaria` (`cuenta_bancaria_id`),
  CONSTRAINT `fk_empcta_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_empcta_cuenta` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuentas bancarias múltiples por empleado';
```

### 1.2 Migrar datos existentes de `empleados.banco`/`empleados.cuenta_bancaria`

Una sola vez después de crear la tabla, migrar los datos actuales:

```sql
-- Insertar cuenta existente de cada empleado como cuenta default
INSERT INTO empleados_cuentas_bancarias (empleado_id, numero_cuenta, clabe, es_default, estatus)
SELECT 
    e.id,
    COALESCE(e.cuenta_bancaria, 'PENDIENTE'),
    COALESCE(e.cuenta_bancaria, NULL),
    1,
    1
FROM empleados e
WHERE e.cuenta_bancaria IS NOT NULL AND e.cuenta_bancaria != ''
  AND e.estatus IN (1, 2);

-- Asociar con cuentas_bancarias por nombre de banco (best effort)
UPDATE empleados_cuentas_bancarias ecb
JOIN empleados e ON ecb.empleado_id = e.id
JOIN cuentas_bancarias cb ON cb.banco = e.banco
SET ecb.cuenta_bancaria_id = cb.id
WHERE e.banco IS NOT NULL AND e.banco != '';
```

### 1.3 Nuevas columnas en `nominas_detalle`

Campos requeridos por el nuevo formato de tabla de nómina (entrenamiento1-3.jpg):

```sql
-- Archivo a crear: database/nomina_detalle_iteracion2.sql

ALTER TABLE `nominas_detalle`
  ADD COLUMN `lugar_origen` varchar(100) DEFAULT NULL COMMENT 'Lugar u origen (Oficina, Lagunas Oaxaca, Tuxtla, etc.)' AFTER `empleado_id`,
  ADD COLUMN `sueldo_diario` decimal(10,2) DEFAULT 0.00 COMMENT 'Sueldo diario del empleado al momento del cálculo' AFTER `sueldo_base`,
  ADD COLUMN `horas_extras` decimal(6,2) DEFAULT 0.00 COMMENT 'Cantidad de horas extras' AFTER `sueldo_diario`,
  ADD COLUMN `costo_hora_extra` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo por hora extra (preestablecido por empleado)' AFTER `horas_extras`,
  ADD COLUMN `monto_horas_extras` decimal(10,2) DEFAULT 0.00 COMMENT 'Total horas extras = horas_extras * costo_hora_extra' AFTER `costo_hora_extra`,
  ADD COLUMN `comidas` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto por comidas' AFTER `monto_horas_extras`,
  ADD COLUMN `viaticos_pasajes` decimal(10,2) DEFAULT 0.00 COMMENT 'Viáticos y pasajes' AFTER `comidas`,
  ADD COLUMN `prima` decimal(10,2) DEFAULT 0.00 COMMENT 'Prima vacacional/dominical' AFTER `viaticos_pasajes`,
  ADD COLUMN `otros_bonos` decimal(10,2) DEFAULT 0.00 COMMENT 'Otros bonos' AFTER `prima`,
  ADD COLUMN `otros_ingresos` decimal(10,2) DEFAULT 0.00 COMMENT 'Otros ingresos/percepciones' AFTER `otros_bonos`,
  ADD COLUMN `prestamo_personal` decimal(10,2) DEFAULT 0.00 COMMENT 'Descuento por préstamo personal' AFTER `deducciones`,
  ADD COLUMN `otros_descuentos` decimal(10,2) DEFAULT 0.00 COMMENT 'Otros descuentos no contemplados' AFTER `prestamo_personal`,
  ADD COLUMN `infonavit_descuento` decimal(10,2) DEFAULT 0.00 COMMENT 'Descuento INFONAVIT del periodo (desglose visible)' AFTER `otros_descuentos`;
```

### 1.4 Asegurar campo `infonavit_aportacion` en `empleados`

Verificar que el campo existe en producción. Si no está, añadirlo:

```sql
-- Verificación (no destructiva)
-- El campo infonavit_aportacion ya existe en el schema de empleados (visto en backup).
-- Si hiciera falta:
-- ALTER TABLE `empleados` ADD COLUMN `infonavit_aportacion` decimal(10,2) DEFAULT 0.00 COMMENT 'Aportación patronal INFONAVIT' AFTER `descuento_infonavit`;
```

### 1.5 Nueva tabla: `nomina_configuracion`

Para almacenar la configuración de automatización de nóminas:

```sql
-- Archivo a crear: database/nomina_configuracion.sql

CREATE TABLE IF NOT EXISTS `nomina_configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frecuencia` enum('Semanal','Quincenal','Mensual') NOT NULL DEFAULT 'Quincenal',
  `auto_crear` tinyint(1) DEFAULT 0 COMMENT '1 = Crear nómina automáticamente según frecuencia',
  `crear_dias_antes` int(11) DEFAULT 0 COMMENT 'Días de anticipación para crear la nómina automática',
  `ultima_ejecucion` datetime DEFAULT NULL COMMENT 'Fecha/hora de la última ejecución automática',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración de automatización de nóminas';

-- Insertar configuración por defecto
INSERT INTO `nomina_configuracion` (frecuencia, auto_crear, crear_dias_antes, activo) 
VALUES ('Quincenal', 0, 1, 1);
```

### 1.6 Nuevo campo: `costo_hora_extra` en `empleados`

Para el valor preestablecido por empleado que se usará en el cálculo de horas extras:

```sql
ALTER TABLE `empleados`
  ADD COLUMN `costo_hora_extra` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo por hora extra (preestablecido para cálculo de nómina)' AFTER `descuento_infonavit`;
```

### 1.7 Resumen de archivos SQL a crear en `database/`

| # | Archivo | Contenido |
|:--|:--------|:----------|
| 1 | `empleados_cuentas_bancarias.sql` | CREATE TABLE + migración de datos existentes |
| 2 | `nomina_detalle_iteracion2.sql` | ALTER TABLE `nominas_detalle` — 14 nuevas columnas |
| 3 | `nomina_configuracion.sql` | CREATE TABLE `nomina_configuracion` + INSERT default |
| 4 | `empleados_costo_hora_extra.sql` | ALTER TABLE `empleados` — campo `costo_hora_extra` |

---

## Fase 2: Actualización de Modelos

### 2.1 `application/models/RH/NominaRhModel.php`

Este es el modelo principal que requiere los cambios más profundos.

#### 2.1.1 Modificar `agregar_empleados_nomina()`

**Ruta:** `application/models/RH/NominaRhModel.php`, método `agregar_empleados_nomina($nomina_id, $tipo_nomina)` (~línea 29)

**Cambio:** Al insertar cada empleado en `nominas_detalle`, incluir el campo `lugar_origen` desde `empleados` (si existe columna `lugar_pago` en empleados, usar ese valor; si no, campo por defecto vacío).

```php
// En el foreach que inserta nominas_detalle, agregar:
$this->db->insert('nominas_detalle', [
    'nomina_id'   => $nomina_id,
    'empleado_id' => $emp->id,
    'lugar_origen'=> $emp->lugar_pago ?? '',  // NUEVO
]);
```

**Ajuste:** Modificar la query `SELECT` para incluir `lugar_pago` desde empleados.

#### 2.1.2 Modificar `calcular_nomina()`

**Ruta:** `application/models/RH/NominaRhModel.php`, método `calcular_nomina($nomina_id)` (~línea 48)

**Cambios en la query de selección** (línea ~54): Agregar nuevos campos de empleados:

```php
$this->db->select('
    nd.id, nd.empleado_id,
    e.salario_base_mensual, e.salario_base_diario,
    e.isr_porcentaje, e.imss_cuota,
    e.pension_alimenticia_porcentaje, e.pension_alimenticia_monto,
    e.descuento_infonavit, e.tiene_infonavit, e.infonavit_aportacion,
    e.costo_hora_extra,   -- NUEVO
    e.lugar_pago          -- NUEVO
');
```

**Cambios en el UPDATE `nominas_detalle`** (línea ~86): Agregar los nuevos campos:

```php
$this->db->where('id', $det->id)->update('nominas_detalle', [
    'dias_trabajados'     => $dias,
    'sueldo_base'         => $sueldo,
    'sueldo_diario'       => round((float)$det->salario_base_diario, 2),  // NUEVO
    'lugar_origen'        => $det->lugar_pago ?? '',                        // NUEVO
    'percepciones'        => $percepciones,
    'deducciones'         => $deducciones,
    'infonavit_descuento' => $infonavit_calculado,                         // NUEVO
    'neto'                => $neto,
]);
```

**Cambios en `calcular_conceptos_empleado()`** (línea ~208): Extraer la variable `$infonavit_calculado` para luego pasarla al detalle:

El método privado `calcular_conceptos_empleado` actualmente retorna solo el array `$conceptos`. Debe retornar también el monto de INFONAVIT calculado. Opción: modificar este método para que devuelva `['conceptos' => [...], 'infonavit' => float]`, o simplemente recalcular el infonavit en `calcular_nomina` después de llamar a `calcular_conceptos_empleado`.

**Enfoque recomendado (mínima invasión):** Dentro del bucle `foreach ($detalles as $det)` de `calcular_nomina`, después de llamar a `$this->calcular_conceptos_empleado(...)`, recalcular infonavit localmente:

```php
$infonavit_calculado = 0;
if (!empty($det->tiene_infonavit) && (float)$det->descuento_infonavit > 0) {
    $infonavit_calculado = round((float)$det->descuento_infonavit, 2);
}
```

Y pasar `$infonavit_calculado` al UPDATE.

#### 2.1.3 Agregar método `get_nomina_detalle_completo()`

Nuevo método en `NominaRhModel` para retornar los datos en el nuevo formato de tabla (imágenes entrenamiento):

```php
/**
 * Obtiene detalle completo de nómina con las nuevas columnas para el modal informativo.
 * @param int $nomina_id
 * @return object|null
 */
public function get_nomina_detalle_completo($nomina_id) {
    $this->db->select('
        n.*,
        nd.id as detalle_id, nd.empleado_id, nd.lugar_origen,
        nd.dias_trabajados, nd.sueldo_base, nd.sueldo_diario,
        nd.horas_extras, nd.costo_hora_extra, nd.monto_horas_extras,
        nd.comidas, nd.viaticos_pasajes, nd.prima, nd.otros_bonos, nd.otros_ingresos,
        nd.percepciones, nd.deducciones,
        nd.infonavit_descuento, nd.prestamo_personal, nd.otros_descuentos,
        nd.neto, nd.monto_pagado, nd.estatus,
        e.numero_empleado, e.nombre, e.apellido_paterno, e.apellido_materno,
        e.puesto, e.rfc, e.curp, e.nss,
        e.banco, e.cuenta_bancaria
    ');
    $this->db->from('nominas_detalle nd');
    $this->db->join('nominas n', 'n.id = nd.nomina_id');
    $this->db->join('empleados e', 'e.id = nd.empleado_id');
    $this->db->where('nd.nomina_id', (int)$nomina_id);
    $this->db->order_by('e.nombre', 'ASC');
    $result = $this->db->get()->result();

    // Adjuntar cuentas bancarias múltiples a cada empleado
    foreach ($result as &$row) {
        $row->cuentas_bancarias = $this->get_cuentas_empleado($row->empleado_id);
    }
    unset($row);

    return $result;
}
```

#### 2.1.4 Agregar métodos de cuentas bancarias

```php
/**
 * Obtiene cuentas bancarias de un empleado.
 */
public function get_cuentas_empleado($empleado_id) {
    return $this->db
        ->select('ecb.*, cb.banco')
        ->from('empleados_cuentas_bancarias ecb')
        ->join('cuentas_bancarias cb', 'cb.id = ecb.cuenta_bancaria_id', 'left')
        ->where('ecb.empleado_id', (int)$empleado_id)
        ->where('ecb.estatus', 1)
        ->get()
        ->result();
}

/**
 * Guarda o actualiza cuenta bancaria de empleado.
 */
public function guardar_cuenta_empleado($data) {
    if (!empty($data['id'])) {
        $this->db->where('id', (int)$data['id'])
                 ->update('empleados_cuentas_bancarias', $data);
        return (int)$data['id'];
    }
    $this->db->insert('empleados_cuentas_bancarias', $data);
    return $this->db->insert_id();
}

/**
 * Elimina cuenta bancaria de empleado.
 */
public function eliminar_cuenta_empleado($id) {
    return $this->db->where('id', (int)$id)
                    ->update('empleados_cuentas_bancarias', ['estatus' => 0]);
}

/**
 * Establece cuenta default para depósito.
 */
public function set_cuenta_default($empleado_id, $cuenta_id) {
    $this->db->where('empleado_id', (int)$empleado_id)
             ->update('empleados_cuentas_bancarias', ['es_default' => 0]);
    $this->db->where('id', (int)$cuenta_id)
             ->update('empleados_cuentas_bancarias', ['es_default' => 1]);
    return true;
}
```

#### 2.1.5 Agregar método de actualización de campos editables

Para la edición inline en el modal (nombre, banco, cuenta):

```php
/**
 * Actualiza campos editables de un detalle de nómina.
 * Se llama vía AJAX desde el modal.
 */
public function actualizar_detalle_nomina($detalle_id, $data) {
    $allowed = [
        'lugar_origen', 'horas_extras', 'costo_hora_extra', 'monto_horas_extras',
        'comidas', 'viaticos_pasajes', 'prima', 'otros_bonos', 'otros_ingresos',
        'prestamo_personal', 'otros_descuentos',
    ];
    $update = [];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $update[$field] = $data[$field];
        }
    }
    if (empty($update)) {
        return ['success' => false, 'message' => 'Sin cambios'];
    }

    // Recalcular totales
    $det = $this->db->get_where('nominas_detalle', ['id' => (int)$detalle_id])->row();
    if (!$det) {
        return ['success' => false, 'message' => 'Detalle no encontrado'];
    }

    $percepciones = round(
        (float)($update['monto_horas_extras'] ?? $det->monto_horas_extras) +
        (float)($update['comidas'] ?? $det->comidas) +
        (float)($update['viaticos_pasajes'] ?? $det->viaticos_pasajes) +
        (float)($update['prima'] ?? $det->prima) +
        (float)($update['otros_bonos'] ?? $det->otros_bonos) +
        (float)($update['otros_ingresos'] ?? $det->otros_ingresos) +
        (float)$det->sueldo_base,
    2);

    $deducciones = round(
        (float)($update['prestamo_personal'] ?? $det->prestamo_personal) +
        (float)($update['otros_descuentos'] ?? $det->otros_descuentos) +
        (float)$det->infonavit_descuento +
        (float)($det->deducciones - $det->infonavit_descuento - $det->prestamo_personal - $det->otros_descuentos),  // ISR+IMSS+pensión existentes
    2);

    $update['percepciones'] = $percepciones;
    $update['deducciones'] = $deducciones;
    $update['neto'] = round($percepciones - $deducciones, 2);

    // Recalcular monto_horas_extras si se editaron horas o costo
    if (array_key_exists('horas_extras', $update) || array_key_exists('costo_hora_extra', $update)) {
        $h = (float)($update['horas_extras'] ?? $det->horas_extras);
        $c = (float)($update['costo_hora_extra'] ?? $det->costo_hora_extra);
        $update['monto_horas_extras'] = round($h * $c, 2);
    }

    $this->db->where('id', (int)$detalle_id)->update('nominas_detalle', $update);

    // Actualizar totales de la cabecera de nómina
    $this->actualizar_totales_nomina($det->nomina_id);

    return ['success' => true, 'message' => 'Actualizado'];
}

/**
 * Recalcula totales de la cabecera nominas.
 */
public function actualizar_totales_nomina($nomina_id) {
    $totales = $this->db
        ->select('SUM(percepciones) as p, SUM(deducciones) as d, SUM(neto) as n')
        ->from('nominas_detalle')
        ->where('nomina_id', (int)$nomina_id)
        ->get()->row();

    $this->db->where('id', (int)$nomina_id)->update('nominas', [
        'total_percepciones' => round((float)$totales->p, 2),
        'total_deducciones'  => round((float)$totales->d, 2),
        'total_neto'         => round((float)$totales->n, 2),
    ]);
}
```

#### 2.1.6 Agregar métodos de configuración y automatización

```php
/**
 * Obtiene configuración de automatización de nóminas.
 */
public function get_configuracion_automatizacion() {
    return $this->db->order_by('id', 'DESC')->limit(1)
                    ->get('nomina_configuracion')->row();
}

/**
 * Guarda configuración de automatización.
 */
public function guardar_configuracion_automatizacion($data) {
    $existe = $this->db->count_all_results('nomina_configuracion');
    if ($existe > 0) {
        $this->db->update('nomina_configuracion', $data);
    } else {
        $this->db->insert('nomina_configuracion', $data);
    }
    return true;
}

/**
 * Verifica si corresponde crear una nómina automáticamente.
 * Retorna array con los datos para crear la nómina o null si no corresponde.
 */
public function verificar_creacion_automatica() {
    $config = $this->get_configuracion_automatizacion();
    if (!$config || !$config->auto_crear) {
        return null;
    }

    $hoy = date('Y-m-d');
    $ultima = $config->ultima_ejecucion ? date('Y-m-d', strtotime($config->ultima_ejecucion)) : null;

    // Determinar si ya se generó para este periodo
    switch ($config->frecuencia) {
        case 'Semanal':
            // Cada lunes
            if (date('N') != 1) return null;
            $inicio = date('Y-m-d', strtotime('monday this week'));
            $fin = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'Quincenal':
            // Días 1 y 16
            $dia = (int)date('d');
            if ($dia == 1) { $inicio = date('Y-m-01'); $fin = date('Y-m-15'); }
            elseif ($dia == 16) { $inicio = date('Y-m-16'); $fin = date('Y-m-t'); }
            else return null;
            break;
        case 'Mensual':
            // Día 1 del mes
            if ((int)date('d') != 1) return null;
            $inicio = date('Y-m-01');
            $fin = date('Y-m-t');
            break;
        default:
            return null;
    }

    // Verificar que no exista ya una nómina en ese periodo
    $existe = $this->db
        ->where('periodo_inicio', $inicio)
        ->where('periodo_fin', $fin)
        ->where('tipo_nomina', $config->frecuencia)
        ->count_all_results('nominas');

    if ($existe > 0) return null;

    return [
        'periodo_inicio' => $inicio,
        'periodo_fin'    => $fin,
        'tipo_nomina'    => $config->frecuencia,
        'fecha_pago'     => $fin,
    ];
}

/**
 * Crea nómina automática.
 */
public function crear_nomina_automatica() {
    $datos = $this->verificar_creacion_automatica();
    if (!$datos) return null;

    $datos['folio'] = $this->generar_folio();
    $datos['usuario_creacion'] = 1; // sistema

    $this->db->insert('nominas', $datos);
    $nomina_id = $this->db->insert_id();

    if ($nomina_id) {
        $this->agregar_empleados_nomina($nomina_id, $datos['tipo_nomina']);

        // Registrar ejecución
        $this->db->update('nomina_configuracion', [
            'ultima_ejecucion' => date('Y-m-d H:i:s')
        ]);

        // Crear alerta interna
        $this->_crear_alerta_nomina_automatica($nomina_id, $datos);
    }

    return $nomina_id;
}

/**
 * Crea alerta interna al crear nómina automática.
 */
private function _crear_alerta_nomina_automatica($nomina_id, $datos) {
    if (!$this->db->table_exists('alertas_internas')) return;

    $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
    if (!$nomina) return;

    $this->db->insert('alertas_internas', [
        'tipo'        => 'nomina_creada',
        'titulo'      => 'Nómina generada automáticamente',
        'mensaje'     => "Se generó la nómina {$nomina->folio} ({$datos['tipo_nomina']}) para el periodo {$datos['periodo_inicio']} al {$datos['periodo_fin']}. Estatus: Pendiente de cálculo.",
        'modulo'      => 'Recursos Humanos',
        'url'         => 'rh/Nomina',
        'icono'       => 'fa-money-bill-wave',
        'fecha'       => date('Y-m-d H:i:s'),
        'leida'       => 0,
    ]);
}
```

### 2.2 `application/models/Contabilidad/NominaModel.php`

#### 2.2.1 Modificar `get_nomina_completa()`

**Ruta:** `application/models/Contabilidad/NominaModel.php`, método `get_nomina_completa($id)` (~línea 42)

**Cambio:** La query que obtiene el detalle debe incluir los nuevos campos:

```php
$this->db->select('
    nd.*,
    e.numero_empleado, e.nombre, e.apellido_paterno, e.apellido_materno,
    e.puesto, e.rfc, e.curp, e.nss, e.tipo_nomina as emp_tipo_nomina,
    e.banco, e.cuenta_bancaria
');
```

*(Ya incluye `nd.*` que cubrirá las nuevas columnas automáticamente. Solo verificar que no haya conflicto con campos del mismo nombre.)*

### 2.3 `application/models/RH/EmpleadoModel.php`

#### 2.3.1 Agregar campo `costo_hora_extra` en `mod_add()` y `mod_update()`

**Ruta:** `application/models/RH/EmpleadoModel.php`

Verificar que los métodos `mod_add()` y `mod_update()` procesen el nuevo campo `costo_hora_extra` dentro del array de datos que reciben. Si usan asignación dinámica (`$this->db->insert('empleados', $data)`), solo asegurarse de que el controlador envíe el campo.

### 2.4 Resumen de métodos nuevos/actualizados

| Modelo | Método | Acción |
|:-------|:-------|:-------|
| `NominaRhModel` | `agregar_empleados_nomina()` | MODIFICAR: incluir `lugar_origen` |
| `NominaRhModel` | `calcular_nomina()` | MODIFICAR: guardar `sueldo_diario`, `lugar_origen`, `infonavit_descuento` |
| `NominaRhModel` | `get_nomina_detalle_completo()` | NUEVO |
| `NominaRhModel` | `get_cuentas_empleado()` | NUEVO |
| `NominaRhModel` | `guardar_cuenta_empleado()` | NUEVO |
| `NominaRhModel` | `eliminar_cuenta_empleado()` | NUEVO |
| `NominaRhModel` | `set_cuenta_default()` | NUEVO |
| `NominaRhModel` | `actualizar_detalle_nomina()` | NUEVO |
| `NominaRhModel` | `actualizar_totales_nomina()` | NUEVO |
| `NominaRhModel` | `get_configuracion_automatizacion()` | NUEVO |
| `NominaRhModel` | `guardar_configuracion_automatizacion()` | NUEVO |
| `NominaRhModel` | `verificar_creacion_automatica()` | NUEVO |
| `NominaRhModel` | `crear_nomina_automatica()` | NUEVO |
| `NominaModel` | `get_nomina_completa()` | MODIFICAR: select expandido |

---

## Fase 3: Lógica del Controlador

### 3.1 `application/controllers/rh/Nomina.php`

#### 3.1.1 Constructor: agregar permisos

```php
public function __construct() {
    parent::__construct();
    $this->load->model('RH/NominaRhModel');
    $this->load->model('Contabilidad/NominaModel');
    $this->load->helper('permissions');      // NUEVO
    $this->config->load('permissions');       // NUEVO
}
```

#### 3.1.2 Nuevos endpoints AJAX a crear

| Método | Ruta | Descripción |
|:-------|:-----|:------------|
| `get_nomina_detalle_completo_ajax` | `rh/Nomina/get_nomina_detalle_completo_ajax` | Retorna detalle en formato nuevo para el modal |
| `get_nomina_cuentas_ajax` | `rh/Nomina/get_nomina_cuentas_ajax` | Cuentas bancarias de un empleado |
| `guardar_cuenta_empleado_ajax` | `rh/Nomina/guardar_cuenta_empleado_ajax` | Guarda nueva cuenta bancaria |
| `eliminar_cuenta_empleado_ajax` | `rh/Nomina/eliminar_cuenta_empleado_ajax` | Soft-delete de cuenta |
| `set_cuenta_default_ajax` | `rh/Nomina/set_cuenta_default_ajax` | Establece cuenta principal |
| `actualizar_detalle_ajax` | `rh/Nomina/actualizar_detalle_ajax` | Edición inline del detalle |
| `get_configuracion_ajax` | `rh/Nomina/get_configuracion_ajax` | Obtiene config de automatización |
| `guardar_configuracion_ajax` | `rh/Nomina/guardar_configuracion_ajax` | Guarda config de automatización |
| `verificar_auto_nomina_ajax` | `rh/Nomina/verificar_auto_nomina_ajax` | Verifica si toca crear nómina automática |
| `exportar_detalle_excel` | `rh/Nomina/exportar_detalle_excel/{id}` | Exporta Excel con formato de tabla idéntico a imágenes |

#### 3.1.3 Estructura de cada nuevo endpoint

##### `get_nomina_detalle_completo_ajax()`

```php
public function get_nomina_detalle_completo_ajax() {
    $id = (int)$this->input->post('id');
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    $detalle = $this->NominaRhModel->get_nomina_detalle_completo($id);
    $nomina = $this->db->get_where('nominas', ['id' => $id])->row();
    echo json_encode([
        'success' => true,
        'nomina'  => $nomina,
        'detalle' => $detalle,
    ]);
}
```

##### `get_nomina_cuentas_ajax()`

```php
public function get_nomina_cuentas_ajax() {
    $empleado_id = (int)$this->input->post('empleado_id');
    $cuentas = $this->NominaRhModel->get_cuentas_empleado($empleado_id);
    echo json_encode(['success' => true, 'cuentas' => $cuentas]);
}
```

##### `guardar_cuenta_empleado_ajax()`

```php
public function guardar_cuenta_empleado_ajax() {
    $data = [
        'id'                => $this->input->post('id') ? (int)$this->input->post('id') : null,
        'empleado_id'       => (int)$this->input->post('empleado_id'),
        'cuenta_bancaria_id'=> $this->input->post('cuenta_bancaria_id') ? (int)$this->input->post('cuenta_bancaria_id') : null,
        'numero_cuenta'     => $this->input->post('numero_cuenta'),
        'clabe'             => $this->input->post('clabe'),
        'es_default'        => (int)$this->input->post('es_default'),
    ];
    if (empty($data['empleado_id']) || empty($data['numero_cuenta'])) {
        echo json_encode(['success' => false, 'message' => 'Complete los campos requeridos']);
        return;
    }
    $id = $this->NominaRhModel->guardar_cuenta_empleado($data);
    echo json_encode(['success' => true, 'id' => $id, 'message' => 'Cuenta guardada']);
}
```

##### `eliminar_cuenta_empleado_ajax()`

```php
public function eliminar_cuenta_empleado_ajax() {
    $id = (int)$this->input->post('id');
    $this->NominaRhModel->eliminar_cuenta_empleado($id);
    echo json_encode(['success' => true, 'message' => 'Cuenta eliminada']);
}
```

##### `set_cuenta_default_ajax()`

```php
public function set_cuenta_default_ajax() {
    $empleado_id = (int)$this->input->post('empleado_id');
    $cuenta_id   = (int)$this->input->post('cuenta_id');
    $this->NominaRhModel->set_cuenta_default($empleado_id, $cuenta_id);
    echo json_encode(['success' => true, 'message' => 'Cuenta principal actualizada']);
}
```

##### `actualizar_detalle_ajax()`

```php
public function actualizar_detalle_ajax() {
    $detalle_id = (int)$this->input->post('detalle_id');
    $data = $this->input->post();
    unset($data['detalle_id']);
    $result = $this->NominaRhModel->actualizar_detalle_nomina($detalle_id, $data);
    echo json_encode($result);
}
```

##### `get_configuracion_ajax()`

```php
public function get_configuracion_ajax() {
    $config = $this->NominaRhModel->get_configuracion_automatizacion();
    echo json_encode(['success' => true, 'config' => $config]);
}
```

##### `guardar_configuracion_ajax()`

```php
public function guardar_configuracion_ajax() {
    $data = [
        'frecuencia'     => $this->input->post('frecuencia'),
        'auto_crear'     => (int)$this->input->post('auto_crear'),
        'crear_dias_antes'=> (int)$this->input->post('crear_dias_antes'),
    ];
    $this->NominaRhModel->guardar_configuracion_automatizacion($data);
    echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
}
```

##### `verificar_auto_nomina_ajax()`

```php
public function verificar_auto_nomina_ajax() {
    $id = $this->NominaRhModel->crear_nomina_automatica();
    if ($id) {
        echo json_encode(['success' => true, 'nomina_id' => $id, 'message' => 'Nómina automática creada']);
    } else {
        echo json_encode(['success' => true, 'creada' => false]);
    }
}
```

##### `exportar_detalle_excel($id)` — Ver Fase 4, sección 4.5.

---

## Fase 4: Vistas y UI

### 4.1 Vista principal `application/views/rh/nomina/main.php`

#### 4.1.1 Botón "Configurar Automatización"

Agregar en la sección de botones de acción (toolbar superior o junto al botón "Nueva Nómina"):

```html
<button class="btn btn-outline-secondary" onclick="abrirModalConfiguracion()">
    <i class="fas fa-cog"></i> Configurar Automatización
</button>
```

#### 4.1.2 Modal de Configuración de Automatización

Nuevo modal al final del archivo, antes de los scripts:

```html
<!-- Modal: Configuración de Automatización -->
<div class="modal fade" id="modalConfiguracion" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title"><i class="fas fa-robot me-2"></i>Automatización de Nóminas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-bold">Frecuencia de generación</label>
          <select id="configFrecuencia" class="form-select">
            <option value="Semanal">Semanal (cada lunes)</option>
            <option value="Quincenal">Quincenal (días 1 y 16)</option>
            <option value="Mensual">Mensual (día 1 del mes)</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Días de anticipación</label>
          <input type="number" id="configDiasAntes" class="form-control" min="0" max="30" value="1" placeholder="0 = mismo día">
          <small class="text-muted">Días antes del inicio del periodo para crear la nómina</small>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="configAutoCrear">
          <label class="form-check-label fw-bold" for="configAutoCrear">Crear nómina automáticamente</label>
        </div>
        <div class="alert alert-info mt-3" id="configAlerta" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarConfiguracion()"><i class="fas fa-save me-1"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>
```

#### 4.1.3 Modal Informativo de Detalle (Formato Tabla Nuevo)

**IMPORTANTE:** Este modal reemplaza o complementa al modal "Detalle Nómina" actual. Debe mostrar los datos en el formato de las imágenes `entrenamiento1-3.jpg`.

```html
<!-- Modal: Detalle Completo de Nómina (formato tabla nuevo) -->
<div class="modal fade" id="modalDetalleNomina" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-xl-down">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-file-invoice-dollar me-2"></i>
          Nómina <span id="detalleFolio">—</span>
        </h5>
        <div>
          <button class="btn btn-sm btn-light me-1" onclick="exportarDetalleExcel()" title="Exportar Excel">
            <i class="fas fa-file-excel text-success"></i> Exportar
          </button>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-0">
        <!-- Info de cabecera: Periodo, Tipo, Fecha Pago -->
        <div class="p-3 bg-light border-bottom">
          <div class="row g-2 small">
            <div class="col-md-3"><strong>Periodo:</strong> <span id="detallePeriodo">—</span></div>
            <div class="col-md-3"><strong>Tipo:</strong> <span id="detalleTipo">—</span></div>
            <div class="col-md-3"><strong>Fecha Pago:</strong> <span id="detalleFechaPago">—</span></div>
            <div class="col-md-3"><strong>Estatus:</strong> <span id="detalleEstatus">—</span></div>
          </div>
        </div>

        <!-- Tabla scrolling horizontal con el formato exacto de las imágenes -->
        <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
          <table class="table table-sm table-bordered table-hover mb-0" id="tablaDetalleNomina"
                 style="font-size: 0.78rem; white-space: nowrap; min-width: 2200px;">
            <thead class="table-dark text-center align-middle" style="position: sticky; top: 0; z-index: 2;">
              <tr>
                <th rowspan="2" style="min-width:100px;">Lugar u origen</th>
                <th rowspan="2" style="min-width:180px;">Nombre del trabajador</th>
                <th rowspan="2" style="min-width:80px;">Sueldo diario</th>
                <th rowspan="2" style="min-width:80px;">Sueldo neto</th>
                <th colspan="3" class="bg-success">Horas extras</th>
                <th rowspan="2" style="min-width:70px;">Comidas</th>
                <th rowspan="2" style="min-width:80px;">Viáticos / Pasajes</th>
                <th rowspan="2" style="min-width:70px;">Prima</th>
                <th rowspan="2" style="min-width:70px;">Otros bonos</th>
                <th rowspan="2" style="min-width:70px;">Otros</th>
                <th rowspan="2" class="bg-success text-white" style="min-width:90px;">Total de Percepciones</th>
                <th rowspan="2" class="bg-danger text-white" style="min-width:80px;">Desglose INFONAVIT</th>
                <th rowspan="2" style="min-width:80px;">Préstamo personal</th>
                <th rowspan="2" style="min-width:80px;">Otros descuentos</th>
                <th rowspan="2" class="bg-danger text-white" style="min-width:90px;">Total Deducciones</th>
                <th rowspan="2" class="bg-primary text-white" style="min-width:90px;">Total Sueldo Neto</th>
                <th rowspan="2" style="min-width:120px;">Banco / Cuenta</th>
              </tr>
              <tr>
                <th class="bg-success-subtle" style="min-width:60px;">Cantidad</th>
                <th class="bg-success-subtle" style="min-width:70px;">Costo x hora</th>
                <th class="bg-success-subtle" style="min-width:70px;">Monto</th>
              </tr>
            </thead>
            <tbody id="detalleNominaBody">
              <!-- Se llena vía AJAX -->
            </tbody>
            <tfoot class="table-secondary fw-bold text-end" style="position: sticky; bottom: 0; z-index: 2;">
              <tr id="detalleNominaFooter">
                <!-- Se llena vía JS con totales -->
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted me-auto">Los campos resaltados son editables. Haga clic para modificar.</small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
```

#### 4.1.4 Modal de Cuentas Bancarias del Empleado

```html
<!-- Modal: Cuentas Bancarias del Empleado -->
<div class="modal fade" id="modalCuentasEmpleado" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-university me-2"></i>Cuentas Bancarias — <span id="cuentasEmpleadoNombre">—</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover" id="tablaCuentasEmpleado">
            <thead class="table-light">
              <tr>
                <th>Banco</th>
                <th>Número de Cuenta</th>
                <th>CLABE</th>
                <th>Default</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody id="cuentasEmpleadoBody"></tbody>
          </table>
        </div>
        <hr>
        <h6 class="fw-bold">Agregar cuenta</h6>
        <div class="row g-2">
          <div class="col-md-4">
            <select id="nuevoBancoId" class="form-select form-select-sm">
              <option value="">Seleccionar banco...</option>
              <!-- Llenado vía AJAX desde cuentas_bancarias -->
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" id="nuevoNumeroCuenta" class="form-control form-control-sm" placeholder="No. Cuenta">
          </div>
          <div class="col-md-3">
            <input type="text" id="nuevoClabe" class="form-control form-control-sm" placeholder="CLABE (18 dígitos)">
          </div>
          <div class="col-md-2">
            <button class="btn btn-sm btn-success w-100" onclick="agregarCuentaEmpleado()">
              <i class="fas fa-plus"></i> Agregar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

### 4.2 Selector de Fechas Inteligente

Reemplazar los date-pickers manuales en el modal "Nueva Nómina" por opciones rápidas que autocompletan los rangos.

```html
<!-- En el modal Nueva Nómina, reemplazar los inputs de fecha por: -->
<div class="mb-3">
  <label class="form-label fw-bold">Selección rápida de periodo</label>
  <div class="d-flex flex-wrap gap-1" id="botonesPeriodoRapido">
    <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="semana">Esta semana</button>
    <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="semana_anterior">Semana anterior</button>
    <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="quincena1">1ra Quincena</button>
    <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="quincena2">2da Quincena</button>
    <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="mes_actual">Mes actual</button>
    <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="mes_anterior">Mes anterior</button>
  </div>
  <small class="text-muted">O seleccione manualmente:</small>
</div>
<div class="row g-2">
  <div class="col-md-6">
    <label class="form-label">Periodo inicio</label>
    <input type="date" id="periodoInicio" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Periodo fin</label>
    <input type="date" id="periodoFin" class="form-control" required>
  </div>
</div>
```

### 4.3 JavaScript en `main.php`

#### 4.3.1 Funciones globales nuevas

```javascript
// --- Funciones de Configuración ---

function abrirModalConfiguracion() {
    $.post('rh/Nomina/get_configuracion_ajax', function(r) {
        if (r.success && r.config) {
            $('#configFrecuencia').val(r.config.frecuencia);
            $('#configDiasAntes').val(r.config.crear_dias_antes || 1);
            $('#configAutoCrear').prop('checked', r.config.auto_crear == 1);
        }
        new bootstrap.Modal(document.getElementById('modalConfiguracion')).show();
    }, 'json');
}

function guardarConfiguracion() {
    $.post('rh/Nomina/guardar_configuracion_ajax', {
        frecuencia: $('#configFrecuencia').val(),
        crear_dias_antes: $('#configDiasAntes').val(),
        auto_crear: $('#configAutoCrear').is(':checked') ? 1 : 0
    }, function(r) {
        if (r.success) {
            showErpToast('Configuración guardada', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalConfiguracion')).hide();
        }
    }, 'json');
}

// --- Funciones del Modal de Detalle (Formato Tabla Nuevo) ---

function verNomina(id) {
    $.post('rh/Nomina/get_nomina_detalle_completo_ajax', {id: id}, function(r) {
        if (!r.success) { showErpToast(r.message || 'Error', 'error'); return; }
        
        $('#detalleFolio').text(r.nomina.folio);
        $('#detallePeriodo').text(r.nomina.periodo_inicio + ' — ' + r.nomina.periodo_fin);
        $('#detalleTipo').text(r.nomina.tipo_nomina);
        $('#detalleFechaPago').text(r.nomina.fecha_pago);
        $('#detalleEstatus').html(renderBadgeEstatus(r.nomina.estatus));
        
        // Guardar ID para exportación
        $('#modalDetalleNomina').data('nomina-id', id);
        
        // Renderizar tabla
        renderTablaDetalle(r.detalle);
        
        new bootstrap.Modal(document.getElementById('modalDetalleNomina')).show();
    }, 'json');
}

function renderTablaDetalle(detalle) {
    let html = '';
    let totales = {
        sueldo_diario: 0, sueldo_neto: 0, horas_extras: 0, monto_horas_extras: 0,
        comidas: 0, viaticos: 0, prima: 0, bonos: 0, otros: 0,
        percepciones: 0, infonavit: 0, prestamo: 0, otros_desc: 0,
        deducciones: 0, neto: 0
    };
    
    detalle.forEach(function(d, i) {
        html += '<tr data-detalle-id="' + d.detalle_id + '">';
        html += '<td class="editable text-center" data-field="lugar_origen">' + esc(d.lugar_origen) + '</td>';
        html += '<td>' + esc(d.nombre + ' ' + d.apellido_paterno + ' ' + (d.apellido_materno||'')) + '</td>';
        html += '<td class="text-end">' + fmt(d.sueldo_diario) + '</td>';
        html += '<td class="text-end">' + fmt(d.sueldo_base) + '</td>';
        // Horas extras (editables)
        html += '<td class="editable text-center" data-field="horas_extras" data-type="number">' + fmtNum(d.horas_extras) + '</td>';
        html += '<td class="editable text-end" data-field="costo_hora_extra" data-type="money">' + fmt(d.costo_hora_extra) + '</td>';
        html += '<td class="text-end fw-bold">' + fmt(d.monto_horas_extras) + '</td>';
        // Otros campos editables
        html += '<td class="editable text-end" data-field="comidas" data-type="money">' + fmt(d.comidas) + '</td>';
        html += '<td class="editable text-end" data-field="viaticos_pasajes" data-type="money">' + fmt(d.viaticos_pasajes) + '</td>';
        html += '<td class="editable text-end" data-field="prima" data-type="money">' + fmt(d.prima) + '</td>';
        html += '<td class="editable text-end" data-field="otros_bonos" data-type="money">' + fmt(d.otros_bonos) + '</td>';
        html += '<td class="editable text-end" data-field="otros_ingresos" data-type="money">' + fmt(d.otros_ingresos) + '</td>';
        // Totales
        html += '<td class="text-end bg-success text-white fw-bold">' + fmt(d.percepciones) + '</td>';
        html += '<td class="text-end bg-danger text-white fw-bold">' + fmt(d.infonavit_descuento) + '</td>';
        html += '<td class="editable text-end" data-field="prestamo_personal" data-type="money">' + fmt(d.prestamo_personal) + '</td>';
        html += '<td class="editable text-end" data-field="otros_descuentos" data-type="money">' + fmt(d.otros_descuentos) + '</td>';
        html += '<td class="text-end bg-danger text-white fw-bold">' + fmt(d.deducciones) + '</td>';
        html += '<td class="text-end bg-primary text-white fw-bold">' + fmt(d.neto) + '</td>';
        // Banco/Cuenta (clickeable para abrir modal de cuentas)
        html += '<td class="text-center">';
        html += '<button class="btn btn-sm btn-outline-secondary" onclick="verCuentasEmpleado(' + d.empleado_id + ',\'' + escJS(d.nombre) + '\')" title="Gestionar cuentas">';
        html += '<i class="fas fa-university"></i> ' + esc(d.banco || 'Sin banco') + ' / ' + esc(d.cuenta_bancaria || '—');
        html += '</button></td>';
        html += '</tr>';
        
        // Acumular totales
        totales.sueldo_diario += parseFloat(d.sueldo_diario)||0;
        totales.sueldo_neto += parseFloat(d.sueldo_base)||0;
        totales.horas_extras += parseFloat(d.horas_extras)||0;
        totales.monto_horas_extras += parseFloat(d.monto_horas_extras)||0;
        totales.comidas += parseFloat(d.comidas)||0;
        totales.viaticos += parseFloat(d.viaticos_pasajes)||0;
        totales.prima += parseFloat(d.prima)||0;
        totales.bonos += parseFloat(d.otros_bonos)||0;
        totales.otros += parseFloat(d.otros_ingresos)||0;
        totales.percepciones += parseFloat(d.percepciones)||0;
        totales.infonavit += parseFloat(d.infonavit_descuento)||0;
        totales.prestamo += parseFloat(d.prestamo_personal)||0;
        totales.otros_desc += parseFloat(d.otros_descuentos)||0;
        totales.deducciones += parseFloat(d.deducciones)||0;
        totales.neto += parseFloat(d.neto)||0;
    });
    
    $('#detalleNominaBody').html(html);
    
    // Renderizar footer de totales
    let footer = '<td class="text-center fw-bold">TOTALES</td>';
    footer += '<td></td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.sueldo_diario) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.sueldo_neto) + '</td>';
    footer += '<td class="text-center">' + fmtNum(totales.horas_extras) + '</td>';
    footer += '<td></td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.monto_horas_extras) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.comidas) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.viaticos) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.prima) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.bonos) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.otros) + '</td>';
    footer += '<td class="text-end bg-success text-white fw-bold">' + fmt(totales.percepciones) + '</td>';
    footer += '<td class="text-end bg-danger text-white fw-bold">' + fmt(totales.infonavit) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.prestamo) + '</td>';
    footer += '<td class="text-end fw-bold">' + fmt(totales.otros_desc) + '</td>';
    footer += '<td class="text-end bg-danger text-white fw-bold">' + fmt(totales.deducciones) + '</td>';
    footer += '<td class="text-end bg-primary text-white fw-bold">' + fmt(totales.neto) + '</td>';
    footer += '<td></td>';
    $('#detalleNominaFooter').html(footer);
    
    // Activar edición inline en celdas marcadas con class="editable"
    activarEdicionInline();
}

function activarEdicionInline() {
    $('.editable').off('dblclick').on('dblclick', function() {
        let td = $(this);
        if (td.find('input').length > 0) return;
        
        let valActual = td.text().replace(/[$,]/g, '').trim();
        let field = td.data('field');
        let type = td.data('type') || 'text';
        let detalleId = td.closest('tr').data('detalle-id');
        
        let input = $('<input type="' + (type === 'number' || type === 'money' ? 'number' : 'text') + '" class="form-control form-control-sm" style="width:100%;min-width:80px;">')
            .val(type === 'money' ? valActual : valActual)
            .on('blur', function() {
                let nuevoVal = $(this).val();
                td.text(type === 'money' ? fmt(parseFloat(nuevoVal)||0) : nuevoVal);
                $(this).remove();
                
                // Guardar vía AJAX
                let data = {detalle_id: detalleId};
                data[field] = parseFloat(nuevoVal) || nuevoVal;
                $.post('rh/Nomina/actualizar_detalle_ajax', data, function(r) {
                    if (r.success) {
                        // Refrescar la fila afectada — recargar modal completo
                        let nominaId = $('#modalDetalleNomina').data('nomina-id');
                        verNomina(nominaId);
                    } else {
                        showErpToast(r.message || 'Error al guardar', 'error');
                    }
                }, 'json');
            })
            .on('keydown', function(e) {
                if (e.key === 'Enter') $(this).blur();
                if (e.key === 'Escape') { $(this).blur(); td.text(valActual); }
            });
        
        td.empty().append(input);
        input.focus().select();
    });
}

// --- Funciones de Cuentas Bancarias ---

function verCuentasEmpleado(empleadoId, nombre) {
    $('#cuentasEmpleadoNombre').text(nombre);
    $('#modalCuentasEmpleado').data('empleado-id', empleadoId);
    cargarCuentasEmpleado(empleadoId);
    cargarCatalogoBancos();
    new bootstrap.Modal(document.getElementById('modalCuentasEmpleado')).show();
}

function cargarCuentasEmpleado(empleadoId) {
    $.post('rh/Nomina/get_nomina_cuentas_ajax', {empleado_id: empleadoId}, function(r) {
        let html = '';
        if (r.cuentas && r.cuentas.length > 0) {
            r.cuentas.forEach(function(c) {
                html += '<tr>';
                html += '<td>' + esc(c.banco || '—') + '</td>';
                html += '<td>' + esc(c.numero_cuenta) + '</td>';
                html += '<td>' + esc(c.clabe || '—') + '</td>';
                html += '<td>' + (c.es_default == 1 ? '<span class="badge bg-success">Principal</span>' :
                    '<button class="btn btn-sm btn-outline-success" onclick="setCuentaDefault(' + empleadoId + ',' + c.id + ')">Establecer</button>') + '</td>';
                html += '<td class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="eliminarCuentaEmpleado(' + c.id + ',' + empleadoId + ')"><i class="fas fa-trash"></i></button></td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="5" class="text-center text-muted">Sin cuentas registradas</td></tr>';
        }
        $('#cuentasEmpleadoBody').html(html);
    }, 'json');
}

function cargarCatalogoBancos() {
    $.get('rh/Nomina/get_catalogo_bancos_ajax', function(r) {
        let opts = '<option value="">Seleccionar banco...</option>';
        if (r.bancos) {
            r.bancos.forEach(function(b) {
                opts += '<option value="' + b.id + '">' + esc(b.banco) + '</option>';
            });
        }
        $('#nuevoBancoId').html(opts);
    }, 'json');
}

function agregarCuentaEmpleado() {
    let empleadoId = $('#modalCuentasEmpleado').data('empleado-id');
    $.post('rh/Nomina/guardar_cuenta_empleado_ajax', {
        empleado_id: empleadoId,
        cuenta_bancaria_id: $('#nuevoBancoId').val(),
        numero_cuenta: $('#nuevoNumeroCuenta').val(),
        clabe: $('#nuevoClabe').val(),
        es_default: 0
    }, function(r) {
        if (r.success) {
            $('#nuevoNumeroCuenta, #nuevoClabe').val('');
            cargarCuentasEmpleado(empleadoId);
            showErpToast('Cuenta agregada', 'success');
        }
    }, 'json');
}

function setCuentaDefault(empleadoId, cuentaId) {
    $.post('rh/Nomina/set_cuenta_default_ajax', {empleado_id: empleadoId, cuenta_id: cuentaId}, function(r) {
        if (r.success) { cargarCuentasEmpleado(empleadoId); showErpToast('Cuenta principal actualizada', 'success'); }
    }, 'json');
}

function eliminarCuentaEmpleado(cuentaId, empleadoId) {
    if (!confirm('¿Eliminar esta cuenta?')) return;
    $.post('rh/Nomina/eliminar_cuenta_empleado_ajax', {id: cuentaId}, function(r) {
        if (r.success) { cargarCuentasEmpleado(empleadoId); showErpToast('Cuenta eliminada', 'info'); }
    }, 'json');
}

// --- Selector inteligente de fechas ---

$(document).on('click', '.periodo-btn', function() {
    let rango = $(this).data('rango');
    let inicio, fin;
    let hoy = new Date();
    
    switch(rango) {
        case 'semana':
            let diaSemana = hoy.getDay(); // 0=Dom
            let lunes = new Date(hoy);
            lunes.setDate(hoy.getDate() - (diaSemana === 0 ? 6 : diaSemana - 1));
            inicio = lunes;
            fin = new Date(lunes);
            fin.setDate(lunes.getDate() + 6);
            break;
        case 'semana_anterior':
            let diaSemana2 = hoy.getDay();
            let lunesAnt = new Date(hoy);
            lunesAnt.setDate(hoy.getDate() - (diaSemana2 === 0 ? 6 : diaSemana2 - 1) - 7);
            inicio = lunesAnt;
            fin = new Date(lunesAnt);
            fin.setDate(lunesAnt.getDate() + 6);
            break;
        case 'quincena1':
            inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            fin = new Date(hoy.getFullYear(), hoy.getMonth(), 15);
            break;
        case 'quincena2':
            inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 16);
            fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
            break;
        case 'mes_actual':
            inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
            break;
        case 'mes_anterior':
            inicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
            fin = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
            break;
        default: return;
    }
    
    $('#periodoInicio').val(formatDate(inicio));
    $('#periodoFin').val(formatDate(fin));
    
    // Highlight selected button
    $('.periodo-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');
});

// --- Helpers ---

function formatDate(d) {
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}

function fmt(val) { return '$' + parseFloat(val||0).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtNum(val) { return parseFloat(val||0).toFixed(2); }
function esc(str) { return $('<span>').text(str||'').html(); }
function escJS(str) { return (str||'').replace(/'/g, "\\'").replace(/"/g, '&quot;'); }

function renderBadgeEstatus(estatus) {
    let map = {Borrador:'secondary',Calculada:'warning',Parcial:'info',Pagada:'success',Cancelada:'danger'};
    return '<span class="badge bg-' + (map[estatus]||'secondary') + '">' + estatus + '</span>';
}
```

### 4.4 Endpoint adicional: Catálogo de bancos

Necesario para el dropdown en el modal de cuentas:

```php
// En Nomina.php:
public function get_catalogo_bancos_ajax() {
    $bancos = $this->db
        ->select('id, banco')
        ->from('cuentas_bancarias')
        ->where('estatus', 'Activa')
        ->group_by('banco')
        ->order_by('banco', 'ASC')
        ->get()->result();
    echo json_encode(['success' => true, 'bancos' => $bancos]);
}
```

### 4.5 Exportación Excel del Detalle

**Ruta:** `application/controllers/rh/Nomina.php`, método `exportar_detalle_excel($id)`

Exporta el formato de tabla idéntico a las imágenes `entrenamiento1-3.jpg`, una hoja por nómina.

```php
public function exportar_detalle_excel($id = null) {
    $id = (int)$id;
    $detalle = $this->NominaRhModel->get_nomina_detalle_completo($id);
    $nomina = $this->db->get_where('nominas', ['id' => $id])->row();

    if (!$detalle || !$nomina) {
        setViewError('Nómina no encontrada');
        redirect('rh/Nomina');
        return;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Nómina ' . $nomina->folio);

    // Título
    $sheet->mergeCells('A1:R1');
    $sheet->setCellValue('A1', 'NÓMINA ' . $nomina->folio . ' — ' . $nomina->tipo_nomina);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $sheet->setCellValue('A2', 'Periodo: ' . $nomina->periodo_inicio . ' al ' . $nomina->periodo_fin);
    $sheet->setCellValue('A3', 'Fecha de pago: ' . $nomina->fecha_pago);
    $sheet->setCellValue('A4', 'Estatus: ' . $nomina->estatus);

    // Headers (fila 6)
    $headers = [
        'Lugar u origen', 'Nombre del trabajador', 'Sueldo diario', 'Sueldo neto',
        'Horas extras (cant.)', 'Costo x hora', 'Monto horas extras',
        'Comidas', 'Viáticos / Pasajes', 'Prima', 'Otros bonos', 'Otros',
        'Total Percepciones', 'INFONAVIT', 'Préstamo personal', 'Otros descuentos',
        'Total Deducciones', 'Total Sueldo Neto'
    ];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . '6', $h);
        $col++;
    }

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
    ];
    $sheet->getStyle('A6:R6')->applyFromArray($headerStyle);

    // Datos
    $row = 7;
    $totales = array_fill_keys(['sueldo_diario','sueldo_neto','h_extras','monto_he','comidas','viaticos','prima','bonos','otros','percepciones','infonavit','prestamo','otros_desc','deducciones','neto'], 0);

    foreach ($detalle as $d) {
        $sheet->setCellValue('A'.$row, $d->lugar_origen);
        $sheet->setCellValue('B'.$row, trim($d->nombre . ' ' . $d->apellido_paterno . ' ' . ($d->apellido_materno ?? '')));
        $sheet->setCellValue('C'.$row, (float)$d->sueldo_diario);
        $sheet->setCellValue('D'.$row, (float)$d->sueldo_base);
        $sheet->setCellValue('E'.$row, (float)$d->horas_extras);
        $sheet->setCellValue('F'.$row, (float)$d->costo_hora_extra);
        $sheet->setCellValue('G'.$row, (float)$d->monto_horas_extras);
        $sheet->setCellValue('H'.$row, (float)$d->comidas);
        $sheet->setCellValue('I'.$row, (float)$d->viaticos_pasajes);
        $sheet->setCellValue('J'.$row, (float)$d->prima);
        $sheet->setCellValue('K'.$row, (float)$d->otros_bonos);
        $sheet->setCellValue('L'.$row, (float)$d->otros_ingresos);
        $sheet->setCellValue('M'.$row, (float)$d->percepciones);
        $sheet->setCellValue('N'.$row, (float)$d->infonavit_descuento);
        $sheet->setCellValue('O'.$row, (float)$d->prestamo_personal);
        $sheet->setCellValue('P'.$row, (float)$d->otros_descuentos);
        $sheet->setCellValue('Q'.$row, (float)$d->deducciones);
        $sheet->setCellValue('R'.$row, (float)$d->neto);

        // Acumular
        $totales['sueldo_diario'] += (float)$d->sueldo_diario;
        $totales['sueldo_neto'] += (float)$d->sueldo_base;
        $totales['h_extras'] += (float)$d->horas_extras;
        $totales['monto_he'] += (float)$d->monto_horas_extras;
        $totales['comidas'] += (float)$d->comidas;
        $totales['viaticos'] += (float)$d->viaticos_pasajes;
        $totales['prima'] += (float)$d->prima;
        $totales['bonos'] += (float)$d->otros_bonos;
        $totales['otros'] += (float)$d->otros_ingresos;
        $totales['percepciones'] += (float)$d->percepciones;
        $totales['infonavit'] += (float)$d->infonavit_descuento;
        $totales['prestamo'] += (float)$d->prestamo_personal;
        $totales['otros_desc'] += (float)$d->otros_descuentos;
        $totales['deducciones'] += (float)$d->deducciones;
        $totales['neto'] += (float)$d->neto;

        // Formato pesos para columnas numéricas
        foreach (range('C','R') as $c) {
            $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $row++;
    }

    // Fila de totales
    $sheet->setCellValue('A'.$row, 'TOTALES');
    $sheet->setCellValue('C'.$row, $totales['sueldo_diario']);
    $sheet->setCellValue('D'.$row, $totales['sueldo_neto']);
    $sheet->setCellValue('E'.$row, $totales['h_extras']);
    $sheet->setCellValue('G'.$row, $totales['monto_he']);
    $sheet->setCellValue('H'.$row, $totales['comidas']);
    $sheet->setCellValue('I'.$row, $totales['viaticos']);
    $sheet->setCellValue('J'.$row, $totales['prima']);
    $sheet->setCellValue('K'.$row, $totales['bonos']);
    $sheet->setCellValue('L'.$row, $totales['otros']);
    $sheet->setCellValue('M'.$row, $totales['percepciones']);
    $sheet->setCellValue('N'.$row, $totales['infonavit']);
    $sheet->setCellValue('O'.$row, $totales['prestamo']);
    $sheet->setCellValue('P'.$row, $totales['otros_desc']);
    $sheet->setCellValue('Q'.$row, $totales['deducciones']);
    $sheet->setCellValue('R'.$row, $totales['neto']);

    $totalStyle = ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4E6F1']]];
    $sheet->getStyle('A'.$row.':R'.$row)->applyFromArray($totalStyle);
    foreach (range('C','R') as $c) {
        $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
    }

    // Auto-size
    foreach (range('A','R') as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }

    // Download
    $filename = 'Nomina_' . $nomina->folio . '_' . date('Ymd') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
```

### 4.6 Modificar `lista_ajax()` en el controlador

Agregar en la columna de acciones el nuevo botón del modal informativo (que ya existe como `verNomina(id)` pero ahora usará el nuevo formato).

El botón `verNomina` ya existe en `lista_ajax()`. Solo se debe actualizar la función JavaScript `verNomina` para que use el nuevo endpoint `get_nomina_detalle_completo_ajax` y renderice el modal con el nuevo formato (como se describe en 4.3.1).

---

## Fase 5: Documentación de Alertas

### 5.1 Archivo: `doc/PLAN_ALERTAS_NOMINA.md`

Crear este archivo con el siguiente contenido:

```markdown
# Plan de Alertas y Notificaciones — Módulo Nóminas

> **Estado:** Pendiente de integración con sistema de correo electrónico.
>
> **Dependencia:** Fase 1-4 de `PLAN_NOMINA_ITERACION2.md` y sistema de alertas general (`SISTEMA_ALERTAS_NOTIFICACIONES.md`).

---

## Alertas implementadas (alertas_internas)

| ID | Tipo | Gatillo | Destinatario | Mensaje |
|:---|:-----|:--------|:-------------|:--------|
| NOM-01 | `nomina_creada` | Al crear nómina automática (Fase 3) | Admin(s) con permiso `rh_nomina` | "Se generó la nómina `{folio}` ({tipo}) para el periodo {inicio} al {fin}. Estatus: Pendiente de cálculo." |
| NOM-02 | `nomina_calculada` | Al ejecutar cálculo de nómina | Admin(s) con permiso `rh_nomina` | "La nómina `{folio}` ha sido calculada. Total percepciones: ${monto}. Revise y procese el pago." |
| NOM-03 | `nomina_pagada` | Al completar pago total de nómina | Admin(s) con permiso `contabilidad_nomina` | "La nómina `{folio}` ha sido pagada en su totalidad. Póliza generada: `{poliza}`." |
| NOM-04 | `nomina_vencida` | 3 días después de fecha_pago si estatus != Pagada | Admin(s) con permiso `rh_nomina` | "La nómina `{folio}` con vencimiento {fecha_pago} aún no ha sido pagada completamente. Estatus actual: {estatus}." |

## Integración futura con correo electrónico

### 1. Configuración SMTP

Agregar en `application/config/email.php` o en el helper de notificaciones:

```php
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.chisarecubrimientos.com.mx';
$config['smtp_port'] = 587;
$config['smtp_user'] = 'erp@chisarecubrimientos.com.mx';
$config['smtp_pass'] = '***';
```

### 2. Función helper de envío

Crear `application/helpers/notificaciones_helper.php`:

```php
function enviar_alerta_email($tipo, $datos) {
    $CI =& get_instance();
    $CI->load->library('email');

    // Obtener destinatarios según tipo de alerta
    $admins = obtener_admins_por_permiso($datos['permiso_requerido']);

    foreach ($admins as $admin) {
        $CI->email->clear();
        $CI->email->to($admin->email);
        $CI->email->subject($datos['asunto']);
        $CI->email->message($datos['mensaje_html']);
        $CI->email->send();
    }
}
```

### 3. Puntos de integración

| Punto | Archivo | Método | Acción |
|:------|:--------|:-------|:-------|
| Creación automática | `NominaRhModel.php` | `_crear_alerta_nomina_automatica()` | Llamar a `enviar_alerta_email('nomina_creada', ...)` |
| Cálculo completado | `NominaRhModel.php` | `calcular_nomina()` | Al final, después de `return`, disparar alerta email |
| Pago completado | `NominaRhModel.php` | `procesar_pagos_nomina()` | Si estatus === 'Pagada', enviar email con resumen |
| Nómina vencida | CRON job | `verificar_nominas_vencidas()` | Job diario que revise y envíe alertas |

### 4. CRON Job sugerido

```bash
# Ejecutar diariamente a las 07:00 AM
0 7 * * * /usr/bin/php /home/admin/domains/erp.chisarecubrimientos.com.mx/public_html/index.php rh/Nomina verificar_auto_nomina_ajax
```

---

## Resumen de permisos requeridos para alertas

| Permiso | Código | Usado en |
|:--------|:-------|:---------|
| `rh_nomina` | Gestionar nómina | NOM-01, NOM-02, NOM-04 |
| `contabilidad_nomina` | Gestionar nómina (contabilidad) | NOM-03 |

*Fin del plan de alertas.*
```

---

## Checklist de Ejecución (para el agente Composer)

Sigue este orden estricto:

1. **[ ] Fase 1:** Ejecutar los 4 scripts SQL en `database/` en orden.
2. **[ ] Fase 2:** Modificar `NominaRhModel.php` (agregar 12+ métodos nuevos, modificar 2 existentes).
3. **[ ] Fase 2:** Verificar `NominaModel.php` (el SELECT `nd.*` ya cubre nuevas columnas).
4. **[ ] Fase 2:** Verificar `EmpleadoModel.php` (campo `costo_hora_extra`).
5. **[ ] Fase 3:** Agregar `$this->load->helper('permissions')` y `$this->config->load('permissions')` al constructor de `Nomina.php`.
6. **[ ] Fase 3:** Agregar los 10 nuevos endpoints AJAX listados en la tabla de la Fase 3.
7. **[ ] Fase 3:** Agregar endpoint `get_catalogo_bancos_ajax`.
8. **[ ] Fase 4:** Agregar el HTML de los 3 nuevos modales al final de `main.php`.
9. **[ ] Fase 4:** Agregar el selector de fechas inteligente (reemplazar date-pickers).
10. **[ ] Fase 4:** Agregar todo el JavaScript de la sección 4.3.
11. **[ ] Fase 4:** Modificar el botón `verNomina` en `lista_ajax()` para apuntar al nuevo endpoint.
12. **[ ] Fase 4:** Agregar método `exportar_detalle_excel($id)`.
13. **[ ] Fase 5:** Crear archivo `doc/PLAN_ALERTAS_NOMINA.md`.

---

## Notas técnicas importantes

- **`showErpToast()`**: Usar esta función del sistema para notificaciones toast (NO toastr ni SweetAlert2 para notificaciones).
- **`bootstrap.Modal.getOrCreateInstance(el).show()`**: Patrón Bootstrap 5 para abrir modales.
- **Soft Delete**: Las cuentas bancarias usan `estatus = 0` en lugar de DELETE físico.
- **Permisos**: El helper `tiene_permiso('rh_nomina')` ya existe en `config/permissions.php`. Usarlo para restringir endpoints sensibles.
- **No incluir credenciales en código**: El token SMTP para correos futuros va en archivo de entorno/configuración externa.
- **DataTables**: La tabla principal `#tablaNominas` ya usa server-side. No modificar ese flujo. El nuevo modal de detalle carga vía AJAX simple (no DataTables).

---

*ERP Chisa Recubrimientos — Departamento de Ingeniería de Software — Iteración Nóminas 2*
