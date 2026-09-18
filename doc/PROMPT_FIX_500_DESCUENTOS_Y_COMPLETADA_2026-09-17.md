# Prompt para agente ejecutor — Fix 500 (A5 Descuentos + B4 Completada) y reintento E2E

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-09-17 |
| **Base** | rama `iteracion-3` · commit `d78843b` (base checklist `32782c8`) |
| **Origen** | `doc/CHECKLIST_PRUEBAS_MANUAL_2026-09-17.md` (fallos A5 y B4/B5) |
| **Alcance** | Corregir 2 errores 500 y reintentar "Completada" sobre OV-2026-0009 (id 28) |
| **Fuera de alcance** | F1/F2 (precios $0, decisión de negocio), correos, facturación, RH, demo |

---

## Instrucción para el agente

Copia y pega el bloque siguiente como prompt de ejecución. Está redactado para ejecutarse tal cual, sin re-diagnóstico.

---

# Contexto
Repo ERP Chisa: CodeIgniter 3, PHP 8, MariaDB 10.6 EN PRODUCCIÓN. Código en `public_html/` (`private_html` es symlink: no dupliques). Rama `iteracion-3` (commit actual `d78843b`; base `32782c8`). Lee y respeta `.cursorrules`. PROHIBIDO: DROP/TRUNCATE, commits/push (salvo que el usuario lo pida), reescribir módulos enteros, SQL nuevo en controladores.

# Objetivo
Corregir los dos 500 del checklist `public_html/doc/CHECKLIST_PRUEBAS_MANUAL_2026-09-17.md` (A5 descuentos y B4 Completada) y reintentar B4 sobre la MISMA orden OV-2026-0009 (id 28), dejando lote + entrada de producto terminado.

# Diagnóstico ya verificado contra la BD de producción (2026-09-17). NO re-diagnostiques; ejecuta y verifica.
- **A5**: `MY_Model::insert()/update()` agregan `fecha_alta`/`fecha_edicion` (default `$dateFields` de `MY_Model`, líneas 40-45), pero `descuentos` usa `fecha_creacion`/`fecha_modificacion`. El INSERT falla con `Unknown column 'fecha_alta'`.
- **B4 causa 1**: `application/controllers/produccion/Dashboard.php` **línea 194** selecciona `dov.unidad` de `detalle_orden_venta`, columna que NO existe → `Unknown column 'dov.unidad' in 'field list'` → 500 DESPUÉS de actualizar estatus y ANTES de crear el lote (por eso no hay lote).
- **B4 causa 2**: `ordenes_venta.estatus` = `enum('Cotización','Confirmada','En Preparación','Entregada','Cancelada')`, **SIN 'Completada'**. La conexión de CI3 (`stricton=false` en `config/database.php`) elimina `STRICT_*` del `sql_mode` → el UPDATE no falla, trunca el ENUM a `''` (corrupción silenciosa).
- **Estado actual verificado de OV-2026-0009 (id 28)**: `estatus=''`, `fecha_completado_produccion='2026-09-17 19:12:36'`, **0 lotes**, pesaje ya aplicado (`PESAJE-venta-28`: 3 movimientos; los insumos NO deben volver a moverse). Detalle: línea `id=27`, `producto_id=22`, `cantidad=1.00`, `formulacion_id=NULL`.
- **Verificado OK**: SP `sp_generar_codigo_barras` existe; `lotes_produccion` y `movimientos_productos` tienen todas las columnas que escribe el flujo; `productos.unidad_venta` de #22 = `'Kg'`; `obras_productos.unidad` existe (camino obras no afectado).

# Tareas

## T1 — Fix A5 (descuentos)
En `public_html/application/models/Ventas/DescuentosModel.php`, después de `protected $tableName = 'descuentos';` (línea 9), agregar:

```php
protected $dateFields = [
    'created' => 'fecha_creacion',
    'updated' => 'fecha_modificacion',
];
```

Opcional (misma tarea): en `controllers/ventas/Descuentos.php::crear_ajax`, cambiar la línea del estatus por `'estatus' => $this->input->post('estatus') ?: 'Activo'` para no dejar que el default numérico del modelo ensucie el ENUM.

## T2 — Fix del 500 al Completar (B4)
En `public_html/application/controllers/produccion/Dashboard.php`:

1. **Línea 194**: cambiar `dov.unidad` por `p.unidad_venta AS unidad` (dejar el resto del SELECT igual; el JOIN con `productos` ya existe).
2. **Línea 179** (rama venta de `ya_tiene_lotes`): usar `->where('orden_venta_id', $orden_id)` en lugar de `orden_produccion_id`.
3. Recomendado (evita volver a corromper el ENUM): antes del UPDATE de estatus, validar contra whitelist por tipo:

```php
$permitidos = ($tipo === 'obra')
    ? ['Planificación','En Cotización','Aprobada','En Ejecución','Pausada','Completada']
    : ['Cotización','Confirmada','En Preparación','Completada','Entregada'];
if (!in_array($nuevo_estatus, $permitidos, true)) {
    echo json_encode(['success' => false, 'message' => 'Estatus no válido para este tipo de orden.']);
    return;
}
```

## T3 — Migración SQL + reparación de datos
Crear `public_html/database/fix_estatus_completada_ov.sql` (idempotente) y aplicarlo a producción (credenciales en `application/config/database.php`; usa mysql client o un script PHP temporal y bórralo al terminar):

```sql
SET @dbname = DATABASE();
SET @tablename = 'ordenes_venta';
SET @columnname = 'estatus';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA=@dbname AND TABLE_NAME=@tablename AND COLUMN_NAME=@columnname
      AND COLUMN_TYPE LIKE '%Completada%') > 0,
  'SELECT 1',
  "ALTER TABLE ordenes_venta MODIFY COLUMN estatus ENUM('Cotización','Confirmada','En Preparación','Entregada','Cancelada','Completada') NULL DEFAULT 'Cotización'"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Reparar OV-2026-0009 (id 28): quedó estatus='' por el truncado del ENUM
UPDATE ordenes_venta SET estatus='En Preparación' WHERE id=28 AND estatus='';
```

NO tocar `obras` (su ENUM ya incluye 'Completada').

## T4 — Recomendado: opciones de estatus por tipo en la UI
`views/produccion/dashboard/detalle.php` (líneas ~141-146) hoy ofrece Confirmada / En Proceso / Completada / Entregada a ventas Y obras; 'En Proceso' no existe en el ENUM de ventas y 'Confirmada'/'En Proceso'/'Entregada' no existen en el de obras (misma corrupción silenciosa). Usar la variable `$es_obra` ya definida en la vista:

- Venta: Cotización, Confirmada, En Preparación, Completada, Entregada.
- Obra: Planificación, En Cotización, Aprobada, En Ejecución, Pausada, Completada.

No agregar "Cancelada" a este select (la cancelación tiene su propio flujo con motivo).

## T5 — Reintentar B4 sobre OV-2026-0009 (id 28)
1. Login `soporte2@especialistasweb.com.mx` → `/produccion/Dashboard/detalle/orden_venta/28`.
2. Confirmar que el panel de insumos indica pesaje ya confirmado (no debe pedir pesar de nuevo).
3. Seleccionar **Completada** → Guardar Cambios. Esperado: sin 500, toast de éxito y 1 lote en la sección de lotes.
4. Verificar (solo lectura):

```sql
SELECT id, codigo_barras, producto_id, cantidad, unidad, estatus, formulacion_id FROM lotes_produccion WHERE orden_venta_id=28;  -- 1 fila, producto 22, cantidad 1.00, unidad 'Kg', 'Producido'
SELECT tipo_movimiento, cantidad, stock_anterior, stock_nuevo, venta_id FROM movimientos_productos WHERE venta_id=28;           -- 1 fila 'Produccion', stock_nuevo=1
SELECT stock_actual FROM productos WHERE id=22;                                                                                  -- 1.00
SELECT COUNT(*) FROM movimientos_inventario WHERE referencia='PESAJE-venta-28';                                                  -- 3 (NO duplicar)
SELECT estatus, fecha_completado_produccion FROM ordenes_venta WHERE id=28;                                                      -- 'Completada' + fecha nueva
```

5. Abrir `/produccion/Lotes` (o `/produccion/Lotes/consultar`), confirmar que el lote aparece y que la etiqueta abre (B5).
6. Opcional (trazabilidad): si se desea que el lote quede ligado a la formulación, verificar que la activa de #22 sea la `684` V4 y hacer `UPDATE detalle_orden_venta SET formulacion_id=684, formulacion_version='V4' WHERE id=27 AND formulacion_id IS NULL;` ANTES del paso 3. No es bloqueante.

## T6 — Verificación A5 (descuentos)
UI `/ventas/Descuentos`: crear `TEST-QA-DESC-01` → editar (p. ej. cambiar valor) → desactivar. Esperado: sin 500; fila persistida con estatus `'Inactivo'` y `fecha_creacion`/`fecha_modificacion` pobladas. Al final limpiar el registro TEST (botón Eliminar; si se prefiere SQL: `DELETE FROM descuentos WHERE nombre='TEST-QA-DESC-01'`).

# Reglas
- Solo se permite escritura en BD: el `ALTER` + el `UPDATE` de T3 (y el `DELETE` opcional de T6 sobre esa única fila). Nada de DROP/TRUNCATE ni otros UPDATE masivos.
- NO cancelar OV-2026-0009; no tocar `PRE-2026-0001`, OV-2026-0007 ni OV-2026-0008; no cobrar/timbrar/enviar correo.
- Si un SQL o el flujo falla, DETENTE y reporta el error exacto de la BD (no improvises parches).
- Fuera de alcance (NO hacer): F1/F2 del checklist. `productos.precio_venta` de #22 = `0.00` es dato de negocio (461 productos sin precio) y el IVA $0 es correcto con subtotal $0 (lo calcula el trigger `trg_ordenes_venta_calcular_totales`). No bloquea B4.

# Entregable
Reporte final con: diff de los archivos tocados, SQL aplicado (y salida de las verificaciones), capturas/folios de la UI (A5 y B4), y actualización de las filas correspondientes en `doc/CHECKLIST_PRUEBAS_MANUAL_2026-09-17.md` (A5 ✅, B4 ✅, B5 ✅) sin commitear salvo que el usuario lo pida.

---

## Anexo — Evidencia usada para el diagnóstico (solo lectura, 2026-09-17)

| Verificación | Resultado |
|---|---|
| `descuentos` columnas | `id, cliente_id, nombre, descripcion, tipo_descuento, valor, estatus, fecha_creacion, fecha_modificacion` — **no** `fecha_alta` |
| `detalle_orden_venta` columnas | **no** tiene `unidad` (sí `formulacion_id`, `formulacion_version`) |
| `ordenes_venta.estatus` | `enum('Cotización','Confirmada','En Preparación','Entregada','Cancelada')` — sin `'Completada'` |
| `obras.estatus` | `enum('Planificación','En Cotización','Aprobada','En Ejecución','Pausada','Completada','Cancelada')` — OK |
| OV id 28 | `estatus=''`, `fecha_completado_produccion=2026-09-17 19:12:36`, 0 lotes, 3 movs `PESAJE-venta-28` |
| SP `sp_generar_codigo_barras` | existe |
| `productos` #22 | `unidad_venta='Kg'`, `precio_venta=0.00`, `stock_actual=0.00`, estatus `Activo` |
| `sql_mode` servidor | `STRICT_TRANS_TABLES,...`; CI3 lo relaja por conexión (`stricton=false`) |

*Generado el 2026-09-17. Base `iteracion-3` / `d78843b`.*
