# Handoff — entrenamiento + flujo + Facture sandbox

Fecha: 2026-11-10. Rama git: `iteracion-5` (repo en `public_html/`). `private_html` es symlink a `public_html`; no duplicar archivos.

Pedido de Fausto: verificar a detalle los 2 módulos de entrenamiento, galería de fotos MP del proveedor, menú lateral (submenús se confundían), que Facture arranque en **sandbox**, y validar el flujo venta/cotización → MP/preorden → producción/lotes → entrega → facturación. SMTP lo dará él después. **Avisar antes de tocar BD.** Entrenamiento en este mismo entorno. No DROP/TRUNCATE. Overhaul Obras/Producción P1–P9 cerrado (no reimplementar).

Este handoff cubre la pasada del 2026-11-10. **Cierre 2026-11-18:** cambios commiteados, I5 mergeada a `main`, rama viva `iteracion-6`. Conteos de BD de ese cierre están en `TODO.md` y `MODULOS_ESTADO_CHECKLIST.md`.

---

## No romper

- No `git commit`/`push` salvo que lo pidan.
- No DROP/TRUNCATE. No inventar precios. No cobro ni timbrado real.
- No tocar `OV-2026-0009` ni `PRE-2026-0001`.
- Preórdenes solo en documentos de compromiso (no cotización / no agregar producto a obra en borrador).
- Completar lote exige pesaje. Parsear forzar: `in_array($post, ['1',1,'true',true], true)`.
- Pesaje descuenta insumos (`PESAJE-*`); completar lote solo entra PT.
- Escalado BOM: Cubeta/Pza = cantidad×lote; Kg = kg (19 kg de lote 19 kg = 19, no 361).
- Entrenamiento materiales: marcar fabricado **enlaza producto, no borra el insumo**.
- Costos: permiso `produccion_ver_costos`. CSRF en POST. `tiene_permiso()`.
- CI3: SQL en modelos, no en controladores. Reusar `explotar_bom_plano`, `calcular_insumos_para_proyecto` (Obras: **sin** fallback rendimiento 1.0), `crear_preordenes_desde_faltantes`, `convertir_unidad_insumo`.

Docs canónicos (leer solo si hace falta, no pegar enteros): `public_html/DOCUMENTACION_TECNICA.md`, `doc/DOCUMENTACION_TECNICA.md` (puntero), `doc/cotizacion.md`, `doc/AUDITORIA_OVERHAUL_PRODUCCION_2026-08-20.md`. Checklist Grokbot: `doc/PRUEBAS_GROKBOT_I6.md`. Brief entrenamiento: `doc/entrenamiento_6/entrenamiento.md`.

---

## Estado de git (sin commit)

Modificados:

| Archivo | Qué |
|---------|-----|
| `application/controllers/compras/EntrenamientoMateriales.php` | `fotos_mp` en index; `foto_mp()` GET `?f=`; `_listar_fotos_mp()` |
| `application/views/compras/entrenamiento_materiales/main.php` | Galería 23 fotos; lápiz → ficha; **modal de edición eliminado**; DataTable simple |
| `application/views/layouts/sidebar.php` | Clase `sidebar-entrenamiento` en los 2 enlaces |
| `assets/dist/css/estilos.css` | Submenú `::before` con `– `; highlight amarillo `.sidebar-entrenamiento` |
| `application/views/produccion/entrenamiento_productos/main.php` | Texto de flujo (revisar fórmula → verificada → historial) |
| `application/controllers/facturacion/Facturas.php` | `facture_ambiente` al dashboard |
| `application/views/facturacion/dashboard.php` | Badge sandbox + aviso no timbrar RFC real |

Untracked: `doc/entrenamiento_6/entrenamiento6-imagenes-materia-prima/` (JPEGs WhatsApp; nombres con espacios). `private_html` las ve igual por el symlink.

---

## Entrenamiento — verificado en navegador (EHWEB, sin Guardar)

URL: https://erp.chisarecubrimientos.com.mx/  
Sesión usada: usuario **EHWEB**. Login Grokbot: ver `doc/PRUEBAS_GROKBOT_I6.md` (soporte2; no repetir contraseña aquí).

### Materiales (`compras/EntrenamientoMateriales`)

- Listado ~150 filas, filtro búsqueda/semáforo, lápiz abre ficha.
- Ficha probada: `/compras/EntrenamientoMateriales/editar/12` → **SOL-004** Acetato de Butilo, tipo comprado, unidad L, No revisado. Botón “Marcar como producto fabricado (enlaza, no borra el insumo)”.
- Galería: **23 fotos** visibles (Bermocoll, W-4535 V, xanthan, Arbenol, etc.). Servidas por `foto_mp?f=` (basename + realpath, MIME jpeg). **No ligadas a insumos en BD.**
- Los filenames de WhatsApp no matchean SKU; no auto-asociar.

### Productos (`produccion/EntrenamientoProductos`)

- Listado ~497 filas, DataTable, semáforo.
- Ficha: `/produccion/EntrenamientoProductos/ver/365` → **22-DE-OCTUBRE-2008**, formulación activa con cantidades, Revisado=No, Principal=Aún no. Historial se habilita al marcar `entrenamiento_principal_ok`.
- Constructor llama `ProductosModel::asegurar_entrenamiento_producto()`: solo `ALTER` si faltan columnas `entrenamiento_revisado` / `entrenamiento_principal_ok` (ya existen; no re-migrar).
- Completar lote **no** está en este módulo; sigue en `produccion/Lotes` + pesaje.

Permisos: `entrenamiento_materiales`, `entrenamiento_productos` (también existen `entrenamiento_sembrar`, `ventas_cotizaciones_obra`, `ventas_pie_cotizacion`).

---

## Flujo operativo — pantallas OK, circuito de alta **no ejecutado**

No se creó venta, preorden, lote ni factura (Fausto pidió avisar antes de BD).

| Etapa | Dónde | Código / nota |
|-------|--------|----------------|
| Venta mostrador / cotización | `ventas/Pos` carga (sucursales SUC-MATRIZ / SUC-HAPA). No cobrar. | Canal mostrador vs obra. |
| OV | `ventas/Ordenes` lista con Cobrar (no pulsar). | No tocar OV-2026-0009. |
| Obras | `obras/Obras` lista. | Compromiso (no borrador) dispara MP. |
| Faltante MP → preorden + aviso | `VentasModel::verificar_insumos_y_preordenes_venta`, `ObrasModel::verificar_insumos_y_preordenes_obra` → `PreordenesModel::crear_preordenes_desde_faltantes` | Orígenes: `produccion` \| `venta` \| `obra` \| `interno`. UI preórdenes: AJAX en `compras/OrdenesCompra` (`lista_preordenes_ajax`). No autorizar PRE-2026-0001. Notif: `Notifications::_get_preordenes_pendientes`. |
| Producción / BOM / lotes | `produccion/Lotes` lista. | `explotar_bom_plano`, `calcular_insumos_para_proyecto`. Pesaje descuenta; completar entra PT. |
| Entrega | `almacen/Entregas` | Tabs: **Órdenes de Venta 4**, **Obras 3**. No entregar. |
| Facturación | `facturacion/Facturas` | Ver siguiente sección. |

---

## Facture sandbox

- Config: `application/config/factureapp.php` — `$config['factureapp_ambiente'] = 'sandbox'` → `https://app.facture.com.mx`. Producción sería `app.micontador.mx`. Credenciales en ese archivo; no re-OAuth salvo token muerto.
- Token en tabla `api_tokens` (`updated_at` visto 2026-02-04). Sigue sirviendo.
- Probe **solo CLI** (HTTP 403):  
  `cd public_html && php index.php facturacion/Facturas/cli_probe`  
  Resultado 2026-11-10: `ok: true`, `ambiente: sandbox`, `api_message: Petición satisfactoria.`
- Dashboard: badge **sandbox**, listado de facturas (cuenta **conectada**; no sale “Conectar Ahora”). Botones Sincronizar / Importar / Nueva / Cancelar / Enviar correo: **no usar** en prueba a ciegas.
- “Enviar por Correo (**Pendiente**)”: espera SMTP que dará Fausto. No configurar mail hasta entonces.
- **No timbrar RFC real.** RFC fallback en código: `IVD920810GU2` (sandbox).

---

## Pendiente (siguiente agente)

1. **SMTP**: esperar correo de Fausto; no improvisar.
2. **BD — pedir OK** antes de:
   - Ligar fotos MP a insumos (hace falta criterio de match; filenames no son SKU; posible columna o tabla).
   - Guardar ficha entrenamiento (revisado, unidad, aliases, precios proveedor).
   - Marcar formulación principal verificada / pasar a MP / editar componentes.
   - Circuito de prueba: cotización/venta → faltante → preorden → lote/pesaje → entrega → factura sandbox.
3. Entorno de prueba: Fausto dijo que si hay que tocar BD avisar para generar entorno; el entrenamiento sigue en **este** ERP.
4. Checklist Grokbot `doc/PRUEBAS_GROKBOT_I6.md` (C2, C3, P1, P2, etc.) **no está marcado** OK/falla por el agente; C2 ficha sí se abrió (RES-002/SOL-004) pero **no se guardó**.
5. Commit de los 7 archivos + fotos: **solo si lo piden**.
6. Galería: 23 thumbs en la misma página del listado; si duele rendimiento, paginar o lightbox. `alt=""` vacío (a11y).
7. No reabrir modal de materiales. No reimplementar P1–P9.

---

## Cómo retomar UI

Navegador ya logueado a veces como EHWEB. Orden sugerido (solo lectura hasta OK de BD):

1. Proveedores → Entrenamiento de materiales (galería + ficha).
2. Producción → Entrenamiento de productos (Abrir, no Guardar).
3. Facturación dashboard (badge sandbox).
4. POS / OV / Obras / Lotes / Entregas.

Submenús: guión amarillo `–` antes del texto. Enlaces de entrenamiento con fondo amarillo suave.
