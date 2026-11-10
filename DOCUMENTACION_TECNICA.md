# DOCUMENTACIÓN TÉCNICA — ERP CHISA Recubrimientos

Guía arquitectónica y estándar de desarrollo para retomar el proyecto sin reabrir módulos cerrados.

**Fuente canónica:** este archivo (`public_html/DOCUMENTACION_TECNICA.md`).  
`doc/DOCUMENTACION_TECNICA.md` es solo un **puntero** (no duplicar contenido aquí).

**Estado (2026-11-10, rama `iteracion-5`):** overhaul Producción/Obras P1–P9 **cerrado** (2026-08-20). Iteraciones 3 y 4 cerradas. El trabajo vivo es **afinar operación y datos de entrenamiento** (I5/I6), no reimplementar formulación, pesaje ni presupuestos. Pendientes y contrato: `doc/TODO.md`.

---

## Índice

- [1. Arquitectura General](#1-arquitectura-general-del-sistema)
- [2. Clases Base y Estándares](#2-clases-base-y-estándares-del-proyecto)
- [3. Pautas de Desarrollo](#3-pautas-de-desarrollo-específicas)
- [4. Producción y formulaciones (cerrado)](#4-módulo-de-producción-y-formulaciones-cerrado)
- [5. Qué no reabrir](#5-qué-no-reabrir)

Reglas estrictas para agentes: `doc/REGLAS_TECNICAS.md`.

---

## 1. Arquitectura General del Sistema

El ERP está en **CodeIgniter 3**, **PHP 8.3**, MySQL/MariaDB, UI **Bootstrap / AdminLTE**, **DataTables**, **jQuery**, **SweetAlert2**. PDF: mPDF 8.x. Excel: PhpSpreadsheet.

Código en `public_html/` (`private_html` es symlink; no duplicar).

### 1.1 Estructura de directorios

- `application/core/`: `MY_Controller.php`, `MY_Model.php`
- `application/controllers/`: HTTP por módulo (`produccion/`, `compras/`, `obras/`, `ventas/`, …)
- `application/models/`: SQL y negocio
- `application/views/`: presentación
- `application/helpers/`: `permissions_helper.php`, papelería, etc.
- `application/libraries/`: `Init_controller.php`, `FactureApp.php`
- `doc/`: seguimiento, cotización, diagramas, **dossiers de entrenamiento** (no borrar)

---

## 2. Clases Base y Estándares del Proyecto

### 2.1 Controladores (`MY_Controller.php`)

Todos los controladores web heredan de `MY_Controller` (sesión + permiso de `$modulo`). Excepción: `Auth` (login).

Si define `protected $modulo = 'Producción';`, el constructor valida acceso; si no, `deny` o JSON de error en AJAX.

**`$this->viewData`** (layout `layouts/general_template`):

```php
$this->viewData = [
    'pageTitle'  => '',
    'headTitle'  => '',
    'breadcrumb' => '',
    'pageView'   => '',  // obligatorio
    'pageScript' => '',
    'validate'   => '',
    'response'   => []
];
```

Patrón:

```php
$this->viewData['pageTitle']  = 'Título';
$this->viewData['headTitle']  = 'Encabezado';
$this->viewData['breadcrumb'] = 'Inicio > Módulo > Página';
$this->viewData['pageView']   = 'modulo/subcarpeta/main';
$this->load->view('layouts/general_template', $this->viewData);
```

`MY_Controller` ya inicializa las claves. Sesión: usar `$this->session->userdata('id')` (nunca `user_id`).

POST: CSRF. Permisos extra: `tiene_permiso()` / `requiere_permiso()`. Costos: `produccion_ver_costos`.

### 2.2 Modelos (`MY_Model.php`)

CRUD genérico + DataTables (`get_datatables()`, `count_filtered()` vía `$datatableConfig`). Soft delete por `estatus`. **SQL y negocio solo en modelos.**

Reutilizar (no reescribir): `explotar_bom_plano`, `calcular_insumos_para_proyecto` (Obras: **sin** fallback rendimiento 1.0), `crear_preordenes_desde_faltantes`, `convertir_unidad_insumo`.

---

## 3. Pautas de Desarrollo Específicas

### 3.1 DataTables (AJAX)

Frontend → endpoint del controlador → modelo `get_datatables()` → JSON `{ draw, recordsTotal, recordsFiltered, data }`.

### 3.2 Permisos y costos

Tabla `privilege` (`admin`, `permiso`, `valor=1`). Helper: `tiene_permiso('clave')`. Costos: `puede_ver_costos()`, `ocultar_costo()`, `ocultar_precio()`.

### 3.3 Controlador vs modelo

Controlador: HTTP, validación, JSON. Modelo: Active Record y cálculos. No SQL nuevo en controladores (hay deuda histórica; no ampliarla).

### 3.4 Negocio crítico

- Preórdenes solo en documentos de **compromiso** (no cotización; no agregar producto a obra en borrador).
- Completada de producción exige pesaje. Forzar: `in_array($post, ['1', 1, 'true', true], true)` — nunca `(bool)$post`.
- Pesaje descuenta insumos (`PESAJE-*`); completar lote solo entra producto terminado.
- Escalado BOM: Cubeta/Pza = cantidad×lote; Kg = kg (19 kg de lote 19 kg = 19, no 361).
- No DROP/TRUNCATE en producción. No re-migrar columnas de formulaciones (ya existen).

---

## 4. Módulo de Producción y formulaciones (CERRADO)

El editor tipo Excel, grupos de color, fase acuosa, cliente en fórmula, `rendimiento_m2_por_kg`, historial, importador `importar_formulacion_excel_ajax` (PhpSpreadsheet) y el motor de escalado **ya están en código**. No volver a aplicar `ALTER` de esas columnas.

Entidades: `productos` (Fabricado / reventa) → `formulaciones` → `detalle_formulacion` (`grupo_color`, fase acuosa).

Pantallas de entrenamiento (temporales, permiso propio):

- Compras → Entrenamiento de materiales (`entrenamiento_materiales`)
- Producción → Entrenamiento de productos (`entrenamiento_productos`)

Marcar insumo como producto fabricado **enlaza** un SKU (`insumos.producto_id`) y **no borra** el insumo (sigue en BOM). Mensaje explícito en modelo.

Guía post-import Excel: `doc/GUIA_PRODUCCION_POST_IMPORTACION.md`. Workflow: `doc/produccion.md`.

---

## 5. Qué no reabrir

| Tema | Estado |
|------|--------|
| Overhaul P1–P9 producción/obras | Cerrado 2026-08-20 |
| Fase 5 presupuestos/APU/generadores (PRES-) | Cerrado en `iteracion-5` |
| Cotización comercial de obra (CCO-) | Implementada (I5/I6); distinta de PRES- y de cotización a proveedor |
| Compras preorden→OC→recepción | Cerrado 2026-09-28 |
| Smoke P0 POS/Compras/Facture sandbox | Cerrado; no re-ejecutar `SMOKE_P0_AGENTE_EJECUTOR.md` |
| Reloj Bixpe / KONECT | Contrato aparte; no iniciado |
| API Tres Guerras | Diseño en `PLAN_ENVIOS_TRES_GUERRAS.md`; en CCO solo costo interno + carpeta de expediente |
| Control de viscosidad / QC formal | No implementado (cotización §8) |
| Pasarela de pagos / autofactura / go-live Facture | Negocio / proveedor |

Ruta por defecto: `$route['default_controller'] = 'auth'`. No usar `Welcome` como entrada.

*ERP Chisa Recubrimientos — Ingeniería de Software — 2026-11-10*
