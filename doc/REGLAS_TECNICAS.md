# REGLAS TÉCNICAS — Agente IA para ERP Chisa Recubrimientos

> **Propósito:** Guía de reglas estrictas y estándares técnicos que todo agente de IA debe seguir al desarrollar, modificar o extender este sistema ERP.
>
> **Versión:** 1.0 | **Última actualización:** 2026-06-19

---

## Índice

- [1. Arquitectura General](#1-arquitectura-general)
- [2. Reglas de Seguridad](#2-reglas-de-seguridad)
- [3. Reglas de Controladores](#3-reglas-de-controladores)
- [4. Reglas de Modelos](#4-reglas-de-modelos)
- [5. Reglas de Vistas](#5-reglas-de-vistas)
- [6. Reglas de API](#6-reglas-de-api)
- [7. Reglas de Nomenclatura](#7-reglas-de-nomenclatura)
- [8. Reglas de Base de Datos](#8-reglas-de-base-de-datos)
- [9. Reglas Específicas por Módulo](#9-reglas-específicas-por-módulo)
- [10. Reglas de Deployment](#10-reglas-de-deployment)
- [11. Herramientas y Dependencias](#11-herramientas-y-dependencias)
- [12. Lista de Verificación PRE-COMMIT](#12-lista-de-verificación-pre-commit)

---

## 1. Arquitectura General

### 1.1 Stack Tecnológico

| Componente | Tecnología | Versión |
|-----------|-----------|---------|
| Framework | CodeIgniter 3 | 3.x |
| PHP | PHP-FPM | 7.4+ |
| Base de datos | MySQL / MariaDB | 5.7+ |
| Frontend CSS | Bootstrap 4 + AdminLTE 3 | 4.x / 3.x |
| Frontend JS | jQuery + DataTables + SweetAlert2 | 1.10+ |
| Iconos | Lucide Icons (`data-lucide`) | Última |
| PDF | MPDF (incluido en third_party) | — |
| Excel | PhpSpreadsheet (third_party) | — |

### 1.2 Estructura de Directorios

```
project_root/
├── public_html/                    # Producción (despliegue principal)
│   └── application/
│       ├── config/                 # routes.php, database.php, permissions.php, roles.php
│       ├── controllers/            # Controladores por módulo (subcarpetas)
│       ├── core/                   # MY_Controller.php, MY_Model.php
│       ├── helpers/                # permissions_helper.php, etc.
│       ├── libraries/              # Init_controller.php
│       ├── models/                 # Modelos por módulo (subcarpetas)
│       ├── views/                  # Vistas por módulo (subcarpetas)
│       └── third_party/            # MPDF, PhpSpreadsheet
├── private_html/                   # Staging (pruebas)
│   └── application/                # (misma estructura que public_html)
├── iclock/                         # Proxy local ZKTeco (PHP independiente)
├── logs/                           # Logs del servidor
├── stats/                          # Estadísticas webalizer
└── *.md                            # Documentación
```

### 1.3 Regla de Oro: Separación de Responsabilidades

> **NUNCA** mezcles lógica de negocio en controladores ni SQL en vistas.
>
> - **Controlador**: maneja HTTP, valida entrada, llama al modelo, responde (HTML/JSON).
> - **Modelo**: toda la lógica de negocio, queries Active Record, cálculos.
> - **Vista**: solo presentación, HTML + JS mínimo, sin queries.

---

## 2. Reglas de Seguridad

### 2.1 Sesión y Autenticación

- **TODO controlador web** debe heredar de `MY_Controller`, que ejecuta `Init_controller->check_session()` automáticamente.
- Si el controlador define `protected $modulo = 'NombreMódulo';`, `MY_Controller` valida acceso al módulo automáticamente.
- Para peticiones AJAX que requieren permiso extra, usar:
  ```php
  if (!$this->init_controller->has_permission($user_id, 'nombre_permiso')) {
      echo json_encode(['success' => false, 'message' => 'Sin permiso']);
      return;
  }
  ```

### 2.2 API Token

- Los endpoints API (`api/ApiReloj.php`) **NO extienden MY_Controller** (no requieren sesión web).
- Se autentican con header `X-API-Key` validado contra la tabla `reloj_dispositivos`.
- Los tokens se generan con `bin2hex(random_bytes(32))` al crear un dispositivo.

### 2.3 Protección de Costos y Precios

- Existe el permiso `produccion_ver_costos` que restringe la visualización de costos/precios.
- Usar las funciones del helper `permissions_helper.php`:
  - `puede_ver_costos()` → bool
  - `ocultar_costo($costo)` → valor formateado o `<span class="text-muted">🔒 Restringido</span>`
  - `ocultar_precio($precio)` → ídem

### 2.4 CSRF

- La protección CSRF de CodeIgniter está **desactivada para endpoints API** en `config.php`.
- Los formularios web deben usar el token CSRF si está habilitado.

---

## 3. Reglas de Controladores

### 3.1 Herencia Obligatoria

```php
// Controlador web estándar (requiere sesión + permisos)
class MiModulo extends MY_Controller {
    protected $modulo = 'Mi Módulo';  // ← Nombre exacto como en BD (tabla módulos)
    // ...
}

// Controlador API (sin sesión)
class ApiEjemplo extends CI_Controller {
    // ...
}
```

### 3.2 Método POST-Redirect-GET (PRG)

- **Todo formulario que modifica datos** debe usar PRG para evitar reenvíos con F5:
  ```php
  if ($this->form_validation->run() == FALSE) {
      // Mostrar formulario
  } else {
      // Procesar datos
      $this->session->set_flashdata('validate', $this->init_controller->alert("success", $msg));
      $this->session->set_flashdata('notification', ['msg' => $msg, 'type' => 'success']);
      redirect('ruta/destino');
  }
  ```

### 3.3 Patrón DataTables Server-Side

```php
public function search_mi_tabla() {
    $list = $this->MiModel->get_datatables();
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $item) {
        $no++;
        $row = array();
        $row[] = $item->campo1;
        $row[] = $item->campo2;
        // ... acciones con botones HTML al final
        $data[] = $row;
    }
    echo json_encode([
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->MiModel->count_all(),
        "recordsFiltered" => $this->MiModel->count_filtered(),
        "data" => $data,
    ]);
}
```

### 3.4 Validación de Entrada

- Usar `$this->form_validation->set_rules()` con reglas de CodeIgniter.
- **Nunca confiar en `$_POST` directo**; usar `$this->input->post('campo')`.
- Para valores numéricos: castear con `(int)` o `(float)`.
- Para strings: `trim()`, `strip_tags()` según contexto.

### 3.5 Respuestas JSON Estandarizadas

```php
// Éxito
echo json_encode(['success' => true, 'message' => 'OK', 'data' => $datos]);

// Error
echo json_encode(['success' => false, 'message' => 'Error: ...']);
```

### 3.6 ViewData Estándar

```php
$this->viewData = [
    'pageTitle'   => 'Título de Página',
    'headTitle'   => 'Título del Encabezado',
    'breadcrumb'  => 'Inicio > Módulo > Sección',
    'pageView'    => 'modulo/vista/main',       // Ruta de la vista
    'pageScript'  => 'modulo/vista/scripts',   // Scripts JS adicionales (opcional)
    'validate'    => $this->session->flashdata('validate') ?? '',
    'notification'=> $this->session->flashdata('notification') ?? null,
    'response'    => [ /* datos para la vista */ ],
];
$this->load->view('layouts/general_template', $this->viewData);
```

---

## 4. Reglas de Modelos

### 4.1 Herencia Obligatoria

```php
class MiModel extends MY_Model {
    protected $tableName = 'mi_tabla';  // ← REQUERIDO
    protected $primaryKey = 'id';
    protected $statusField = 'estatus';
    protected $datatableConfig = [
        'column_order' => ['id', 'nombre', 'fecha_alta'],
        'column_search' => ['nombre', 'descripcion'],
        'order' => ['id' => 'asc']
    ];
}
```

### 4.2 Soft Delete

- El sistema **NO elimina físicamente** registros clave.
- Se usa `estatus = 0` (inactivo) o `estatus = 'Inactivo'`.
- Métodos disponibles:
  - `$this->soft_delete($id)` — marca como inactivo
  - `$this->restore($id)` — reactiva
  - `$this->hard_delete($id)` — elimina físicamente (**solo con autorización explícita**)

### 4.3 Queries: Active Record, NUNCA SQL Crudo

```php
// ✅ CORRECTO
$this->db->select('e.*, d.nombre as departamento_nombre')
         ->from('empleados e')
         ->join('departamentos d', 'e.departamento_id = d.id', 'left')
         ->where('e.estatus', 1)
         ->order_by('e.nombre', 'asc');
$query = $this->db->get();

// ❌ INCORRECTO
$query = $this->db->query("SELECT * FROM empleados WHERE nombre = '$nombre'");  // SQL Injection
```

### 4.4 Respuestas Estandarizadas del Modelo

- `$this->success_response($mensaje, $datos)` → `['success' => 1, 'msg' => '...', 'data' => ...]`
- `$this->error_response($mensaje)` → `['success' => 0, 'msg' => '...']`
- `$this->not_found_response($mensaje)` → `['success' => -1, 'msg' => '...']`
- `$this->has_db_error()` → `bool`
- `$this->get_db_error()` → `['code' => ..., 'message' => ...]`

### 4.5 Fechas Automáticas

- `$dateFields['created'] = 'fecha_alta'` → se asigna automáticamente en `insert()`
- `$dateFields['updated'] = 'fecha_edicion'` → se asigna automáticamente en `update()`
- `$dateFields['deleted'] = 'fecha_baja'` → se asigna automáticamente en `soft_delete()`

---

## 5. Reglas de Vistas

### 5.1 Layout General

- **Toda vista de página** debe renderizarse a través de `layouts/general_template`.
- Este layout incluye: header, sidebar, breadcrumb, contenido (`$pageView`), footer, scripts.

### 5.2 Componentes UI Estándar

| Componente | Clase/Framework | Uso |
|-----------|----------------|-----|
| Tablas | DataTables (`table dt-responsive`) | Listados con búsqueda, paginación |
| Modales | Bootstrap 4 Modal | Confirmaciones, formularios rápidos |
| Offcanvas | Bootstrap 4 Offcanvas | Detalles laterales (ej. detalle empleado) |
| Alertas | SweetAlert2 (`Swal.fire()`) | Confirmaciones de borrado, notificaciones |
| Badges | `.badge.bg-success` / `.bg-danger` / `.bg-warning` | Estados |
| Botones | `.btn.btn-sm.btn-primary` / `.btn-warning` / `.btn-danger` | Acciones en tablas |
| Iconos | `data-lucide="icon-name"` + `lucide.createIcons()` | Iconografía consistente |
| Cards | `.card` + `.card-header` + `.card-body` | Secciones con título |

### 5.3 Estructura de Offcanvas (Detalles Laterales)

```html
<!-- Botón disparador -->
<button onclick="openOffcanvas('Título', contenidoHtml, accionesHtml)">Ver</button>

<!-- El sistema usa funciones JS globales para cargar el offcanvas -->
```

### 5.4 Indentación y Estilo

- **2 espacios** para indentación en HTML y PHP.
- Usar `<?php echo` en lugar de `<?=` para compatibilidad.
- Comillas dobles en HTML, simples en PHP cuando sea posible.

---

## 6. Reglas de API

### 6.1 Estructura de Endpoint API

- Los endpoints API residen en `controllers/api/`.
- Extienden `CI_Controller` (no `MY_Controller`).
- **No usan sesiones ni CSRF.**
- Autenticación por `X-API-Key` header.

### 6.2 Formato de Respuesta API

```json
{
  "status": "success",
  "message": "Descripción legible",
  "data": {
    "campo": "valor"
  },
  "timestamp": "2026-06-19 13:00:00"
}
```

### 6.3 Códigos HTTP API

| Código | Significado |
|--------|-------------|
| 200 | OK |
| 400 | Bad Request (payload inválido) |
| 401 | No autorizado (token inválido) |
| 403 | Forbidden (SN no coincide) |
| 404 | No encontrado |
| 405 | Método no permitido |
| 500 | Error interno |
| 501 | No implementado |

### 6.4 API Reloj Checador (Específico)

- **Proxy local**: `iclock/index.php` recibe datos del ZKTeco (protocolo ADMS).
- **Endpoints ERP**:
  - `POST api/reloj/sync_asistencias` → recibe `{sn, table, raw_data}` y guarda checadas.
  - `GET api/reloj/comandos_pendientes/{sn}` → devuelve comandos para el reloj.
  - `POST api/reloj/comando_resultado` → registra resultado de ejecución.
  - `GET api/reloj/status` → health check.
- El `raw_data` viene en formato **tabulador-separado** (`\t`). El ERP lo parsea.
- Los comandos ADMS deben usar **tabulador real** (`chr(9)`), no `\t` literal ni espacios.
- `VerifyType`: 0=contraseña, 1=huella, 3=password numérico, **15=facial**.

---

## 7. Reglas de Nomenclatura

### 7.1 Controladores

```
CamelCase, sin prefijo, en subcarpeta por módulo:
  rh/RecursosHumanos.php        → class RecursosHumanos
  produccion/Productos.php      → class Productos
  api/ApiReloj.php              → class ApiReloj
```

### 7.2 Modelos

```
CamelCase con sufijo "Model", en subcarpeta por módulo:
  RH/EmpleadoModel.php          → class EmpleadoModel
  Reloj/RelojModel.php          → class RelojModel
  Produccion/ProductosModel.php → class ProductosModel
```

### 7.3 Vistas

```
snake_case, en subcarpeta por módulo:
  rh/empleados/main_empleados.php
  produccion/productos/main.php
  layouts/general_template.php
```

### 7.4 Rutas

- **Siempre** registrar rutas nuevas en `application/config/routes.php`.
- Formato: `$route['url/amigable'] = 'controlador/metodo';`
- Las rutas API deben mapearse explícitamente.

### 7.5 Base de Datos

```
Tablas:        snake_case_plural (empleados, formulaciones)
Claves primarias: id (INT, AUTO_INCREMENT)
Claves foráneas: tabla_singular_id (empleado_id, departamento_id)
Campos de estado: estatus (TINYINT: 1=activo, 0=inactivo)
Fechas:           fecha_alta, fecha_edicion, fecha_baja (DATE o DATETIME)
```

---

## 8. Reglas de Base de Datos

### 8.1 Soft Delete es la Regla

- **NUNCA** borres físicamente registros de tablas principales sin autorización explícita.
- Usar `estatus = 0` para "eliminar".
- Para eliminación física: método `hard_delete()` con comentario justificando por qué.

### 8.2 Migraciones No Destructivas

- Agregar columnas con `ALTER TABLE ... ADD COLUMN` (nunca `DROP COLUMN` sin backup).
- Nuevas columnas deben ser `NULL` por defecto o tener un valor `DEFAULT`.
- Mantener compatibilidad hacia atrás con código existente.

### 8.3 Convenciones de Campos

| Propósito | Nombre | Tipo |
|-----------|--------|------|
| Clave primaria | `id` | INT AUTO_INCREMENT |
| Estatus | `estatus` | TINYINT(1) DEFAULT 1 |
| Fecha creación | `fecha_alta` | DATE |
| Fecha edición | `fecha_edicion` | DATE |
| Fecha baja | `fecha_baja` | DATE (nullable) |
| Usuario creador | `creado_por` | INT (FK → users.id) |
| Soft delete flag | `eliminado` | TINYINT(1) DEFAULT 0 (alternativo) |

### 8.4 Índices

- Índice en `estatus` para filtros frecuentes.
- Índice compuesto en `(usuario_id, fecha_hora)` para asistencias (detección de duplicados).
- Índice único en campos como `rfc`, `curp`, `email` según aplique.

---

## 9. Reglas Específicas por Módulo

### 9.1 Recursos Humanos (RH)

**Campos Fiscales MX obligatorios:**
- `rfc` (13 caracteres, validado con regex de SAT)
- `curp` (18 caracteres, validado con regex)
- `nss` (11 dígitos, opcional pero validado)
- `regimen_fiscal` (catálogo SAT)
- `tipo_trabajador`: Planta, Honorarios, Sindicalizado, Confianza, Temporal, etc.

**Tipos de contratación México:**
- Planta (indeterminado), Temporal (por obra/proyecto), Honorarios (servicios profesionales), Capacitación inicial, Periodo de prueba, Temporada.

**Campos de nómina:**
- `salario_base_mensual`, `tipo_nomina`, `forma_pago`, `banco`, `cuenta_bancaria`
- `isr_porcentaje`, `imss_cuota`, `infonavit_aportacion`, `afore_aportacion`
- `pension_alimenticia_porcentaje`, `pension_alimenticia_monto`
- `tiene_fonacot`, `tiene_infonavit`, `descuento_infonavit`
- `afore`, `afore_numero_cuenta`

**Calculadora finiquito / liquidación:**
- Debe incluir leyenda: "Este es un valor de cálculo aproximado y deberá verificarse."
- Parámetros configurables (días de aguinaldo, prima vacacional %, etc.) conforme a LFT.

**Vinculación empleado ↔ reloj:**
- `numero_empleado` debe coincidir con el PIN del reloj ZKTeco.
- El sync `RelojSyncRhModel` vincula empleados RH con usuarios del reloj.

### 9.2 Reloj Checador

**Reglas de parseo ATTLOG:**
- Formato: `PIN\tFecha\tHora\t255\tVerifyType\t0\t0\t0\t0\t0`
- El campo `255` se ignora (siempre fijo en MB10-VL).
- VerifyType: 0=password, 1=huella, 3=password numérico, 15=facial.
- Interpretación de checadas por secuencia: 1 checada = entrada, 2 = entrada+salida, 4 = entrada+salida comida+entrada comida+salida.

**Reglas de comandos ADMS:**
- Usar `chr(9)` (tabulador real), NUNCA `\t` literal.
- Comandos soportados:
  - `DATA USER PIN=N\tName=X\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0`
  - `DATA DELETE FACE PIN=N`
  - `DATA DELETE USER PIN=N`
  - `DATA QUERY ATTLOG StartTime=Y-m-d H:i:s\tEndTime=Y-m-d H:i:s`
- PIN 1 es administrador — **nunca borrable**.
- Secuencia de borrado MB10-VL: primero `DATA DELETE FACE PIN=N`, luego `DATA DELETE USER PIN=N`.

**Reglas del proxy local (iclock/):**
- `MODO_PRUEBA_LOCAL = false` en producción (envía a API ERP).
- `MODO_SYNC_DEBUG = false` en producción (usa `sync_asistencias`, no `sync_asistencias_debug`).
- El proxy responde `OK` a todas las peticiones del reloj para evitar reintentos.
- Return code `-1004` en `log_comandos.txt` = error de comando en reloj (usuarios corruptos, formato incorrecto).

### 9.3 Producción

**Entidades:**
- `productos`: tipo `Fabricado` o `Reventa`.
- `formulaciones`: cabecera (versión, costo total, rendimiento, estatus, cliente_id).
- `detalle_formulacion`: ingredientes con cantidades, porcentajes, grupo_color, fase_acuosa.

**Reglas de formulaciones:**
- Agrupar visualmente por `grupo_color`.
- Calcular `kg_fase_acuosa` = `porcentaje_fase_acuosa` × cantidad total.
- Escalamiento: si 1 cubeta = X kg, para N cubetas: total = N × X.
- Rendimiento: `rendimiento_m2_por_kg` permite calcular material para m² de proyecto.
- Historial: cada cambio genera nueva versión de formulación (no se sobrescribe).

**Flujo de producción:**
1. Venta/Obra genera pedido → se verifica stock.
2. Si hay stock → preparar y surtir (leer código de barras al entregar).
3. Si no hay stock → dashboard producción muestra pedido pendiente.
4. Sistema calcula insumos necesarios para los productos a fabricar.
5. Usuarios pueden ajustar cantidades → generar pre-orden de compra si faltan insumos.
6. Al recibir insumos → actualizar stock → fabricar.
7. Productos fabricados → generar código de barras → imprimir y pegar.
8. Salida de almacén: escanear código de barras al surtir/entregar.

### 9.4 Facturación

- Conexión con API Facture App mediante OAuth 2.0.
- Emisión de facturas desde el ERP.
- Smart Download: recuperar PDF/XML desde API si no existen localmente.
- Sincronización bidireccional: validar estatus local vs API, importar facturas faltantes.
- Envío de factura (PDF + XML) por correo electrónico.

---

## 10. Reglas de Deployment

### 10.1 Entornos

| Directorio | Entorno | Propósito |
|-----------|---------|-----------|
| `public_html/` | **PRODUCCIÓN** | Despliegue principal. Cambios solo probados y validados. |
| `private_html/` | **STAGING** | Pruebas. Desarrollo activo. |

### 10.2 Procedimiento de Cambios

1. **Desarrollar en `private_html/`**.
2. Probar exhaustivamente.
3. Solo cuando esté validado: replicar cambios en `public_html/`.
4. **NUNCA** hacer cambios directamente en `public_html/` sin probar en `private_html/` primero.

### 10.3 Antes de Desplegar a Producción

- [ ] Validar `max_input_vars` en PHP para formularios con muchos checkboxes.
- [ ] Verificar que no haya `var_dump()`, `print_r()`, `echo` de debug en el código.
- [ ] Confirmar que `MODO_SYNC_DEBUG = false` en `iclock/config.php`.
- [ ] Confirmar que `MODO_PRUEBA_LOCAL = false` en `iclock/config.php`.
- [ ] Verificar que los archivos de log tengan permisos de escritura para Apache.
- [ ] Respaldar base de datos antes de migraciones.

---

## 11. Herramientas y Dependencias

### 11.1 Librerías Cargadas Automáticamente

- `Init_controller` (sesión, permisos, helpers de alerta)
- `permissions_helper` (funciones `tiene_permiso()`, `puede_ver_costos()`)

### 11.2 Modelos Disponibles (por módulo)

| Módulo | Modelos |
|--------|---------|
| RH | `EmpleadoModel`, `DepartamentoModel`, `ContratoModel`, `VacacionesModel`, `HorariosModel`, `IncidenciasModel`, `PlantillaModel` |
| Reloj | `RelojModel`, `RelojSyncRhModel` |
| Producción | `ProductosModel` |
| Compras | `ProveedoresModel`, `InsumosModel`, `OrdenesCompraModel`, `CategoriasModel` |
| Ventas | `ClientesModel`, `OrdenesModel`, `DescuentosModel` |
| Obras | `ObrasModel` |
| Facturación | `FacturasModel` |
| Contabilidad | `NominaModel`, `BancosModel`, `PolizasModel`, `CuentasContablesModel` |
| Usuarios | `UserModel` |
| Almacén | `InventarioModel`, `EntregasModel` |

---

## 12. Lista de Verificación PRE-COMMIT

Antes de dar por terminado cualquier cambio, el agente IA debe verificar:

- [ ] El código sigue la separación modelo/controlador/vista.
- [ ] No hay SQL crudo en controladores ni vistas.
- [ ] Se usan `$this->input->post()` y `$this->form_validation` para entradas de usuario.
- [ ] Las rutas nuevas están registradas en `config/routes.php`.
- [ ] Los permisos están validados con `tiene_permiso()` o `$this->init_controller->has_permission()`.
- [ ] Los métodos AJAX retornan JSON válido con `success` y `message`.
- [ ] Los formularios usan el patrón POST-Redirect-GET.
- [ ] No hay `echo`, `var_dump()`, `print_r()` de debug en producción.
- [ ] Las queries usan Active Record (`$this->db->...`), no SQL crudo.
- [ ] Los cambios en BD son migraciones no destructivas (ALTER TABLE ADD COLUMN).
- [ ] Se respeta el soft-delete (cambiar `estatus`, no borrar físicamente).
- [ ] Los comandos ADMS para el reloj usan `chr(9)` (tabulador real).

---

> **ERP Chisa Recubrimientos** — Departamento de Ingeniería de Software
>
> *Este documento es vinculante para cualquier agente de IA que trabaje en este proyecto.*