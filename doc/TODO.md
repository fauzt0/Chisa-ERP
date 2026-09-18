# TODO - Sistema ERP CHISA

**Última actualización:** 2026-09-18
**Desarrollador:** Fausto Solano - CHISA Recubrimientos
**Rama activa:** `iteracion-3` (**15 commits locales sin push** a `origin/iteracion-3` tras este cierre; pendiente PR a `main`)

---

## 📝 Notas Técnicas

- [ ] Validar límite de `max_input_vars` en PHP para formularios con muchos checkboxes. (IMPORTANTE: Esto se debe realizar en cada deployment antes de desplegar a producción). Verificado 2026-09-17: valor efectivo **1000** (default), sin override en `php.conf.d`; subir si algún formulario con muchos checkboxes falla.

## 🟡 Estatus del proyecto

- [X] Desarrollo
- [ ] Iteraciones
- [ ] Pruebas — ejecutado el QA de UI de la iteración 3 (`doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md`: 2 pasadas + fixes, 13–14 sep) y el QA de la demo (`doc/entrenamiento_3/REPORTE_QA_DEMO_2026-09-14.md`). La hoja de `doc/CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` sigue vacía; los módulos no cubiertos siguen pendientes (ver "Pruebas pendientes").
- [] Despliegue — en producción: `https://erp.chisarecubrimientos.com.mx`

## Pruebas pendientes

- [ ] verificar envio de correos a los clientes de ordenes de ventas
- [ ] verificar envío de correos a los proveedores de las ordenes de compra en módulo de proveedores
- [ ] verificar envío de correos de facturas a los clientes en módulo de facturación
- [ ] verificación de módulo de proveedores
- [ ] verificación de módulo de producción
- [X] verificación de módulo de obras — Iteración 3 cerrada: checklist TEST-QA ejecutado el 2026-09-07 (ver `doc/CHECKLIST_PRUEBAS_OBRAS_ITERACION3.md`)
- [ ] verificación de módulo de facturación
- [ ] verificación de módulo de usuarios
- [ ] verificación de módulo de permisos
- [ ] verificación de módulo de bitácora
- [ ] verificación de módulo de dashboard
- [ ] verificación de módulo de recursos humanos
- [ ] verificación de módulo de reloj checador
- [ ] verificación de módulo de citas
- [ ] verificación de módulo de calendario

## ✅ Iteraciones completadas

- [X] **Proveedores (Iteraciones 5 y 6)** — comprobantes, email real y WhatsApp, cotizaciones de compra y comparación side-by-side (ago 2026)
- [X] **Nómina RH (Iteración 2 + Planeador Mensual)** — automatización, planeador mensual, detalle y recibos, corrección H1 enum `'Horas Extras'` (ago 2026)
- [X] **Producción / Obras (Overhaul)** — formulaciones, preórdenes, pesaje, etiquetas de lote y materiales m²→kg (ago 2026)
- [X] **Import de formulaciones** — referencia del cliente y `referencia_cliente` en formulaciones (ago 2026)
- [X] **CRM Ventas** — contactos adicionales por cliente, carga masiva y exportación Excel
- [X] **Contraste global y responsive** — tema oscuro, badges, tablas responsive (`theme-toggle.js`, `rh-tables-responsive.js`)
- [X] **Facturación** — conexión API Facture App (emisión, sincronización y smart download)
- [X] **Reloj checador** — API de conexión (`api/ApiReloj`), módulo RH (`rh/RelojChecador`), proxy local en `doc/iclock/`
- [X] **PDF Orden de Compra** — estilo Excel histórico, importe con letra, UTF-8 y dirección real del cliente
- [X] **Obras (Iteración 3)** — flujo completo auditado y cerrado: tab Entregas en detalle de obra, trigger `tr_actualizar_entrega_almacen` corregido, pre-órdenes de compra y solicitudes de producción desde obra, PDF resumen alineado a las referencias, y BUG-1 a BUG-8 corregidos (recibo, rutas de archivos, validación de alta, folios con `MAX(CAST)`, footer del detalle) (sep 2026)
- [X] **Entrenamiento 3 (Producción)** — OCR de 25 capturas, fases 1–3 (29 productos #475–#503, 4 insumos, 12 formulaciones inactivas, 4 enlaces insumo→semielaborado), corrección BOM-1 (V5 de #204, form#968 activa) y pruebas de iteración (sep 2026)
- [X] **QA UI iteración 3 + fixes de presentación** — BUG-UI-04/05/06/08/09 corregidos, migración T1 (`fecha_completado_produccion`, `lotes_produccion.orden_venta_id/obra_id`), precios lista 2025 (30 UPDATE; 33 productos con precio) y fixes de la demo (campana/cartera/PDFs/ayuda en vivo) (sep 2026)
- [X] **QA cierre I3 (Completada E2E + descuentos + ENUM)** — Re-verificación E2E: OV-2026-0009 Completada con 1 lote (PROD-20260918-22-2268), descuentos TEST-QA-DESC-01 CRUD sin 500, ENUM `ordenes_venta.estatus` con `'Completada'`, fix `dov.unidad` en query de lotes, fechas `fecha_creacion`/`fecha_modificacion` en descuentos, SQL `database/fix_estatus_completada_ov.sql`. Checklist manual A5/B4/B5 ✅ (2026-09-18)

## 🟡 Iteración módulo de "Producción" (pendiente)

- [ ] Mejorar y cuadrar los procesos de producción a los procesos actuales. Los productos tienen una formulación y se fabrican en lotes de cubetas, por lo que se debe tener un control de inventario de materias primas y productos terminados (por kilo, litro, etc). El flujo de trabajo se especifica en el archivo `doc/produccion.md`

## 🟡 Pendientes de Obras / Iteración 4

- [X] Validación manual de UI tras el merge del agente cloud (dashboard por permisos, toggle de tema, login/2FA) — hecha en `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md` (GL0–GL3 ✅; cuenta EHWEB; para Obras usar ids 1, 6 o 7). Nota: el entorno sigue en `development` (sin 2FA).
- [ ] Integrar `iteracion-3` → `main` (PR) una vez validado. **⚠️ 2026-09-17: 9 commits locales sin push a `origin/iteracion-3`** (el PR los necesita).
- [ ] API de paquetería "Tres Guerras" (diseño listo en `doc/PLAN_ENVIOS_TRES_GUERRAS.md`)
- [ ] Tab "Entregas" también en la vista CRM Ventas (`ventas/obras/detalle.php`)
- [ ] Mover el SQL directo de `Obras::actualizar_ajax` al modelo (auditoría B5, diferido)
- [ ] Residuos TEST: OVs `OV-TEST-001` / `OV-2026-0004` y cliente "Empresa de Prueba S.A." **se conservan** (siguen en el guion de demo: cartera y campana). Limpieza 2026-09-17: OB-00006 → `estatus='Cancelada'` (ya estaba `activo=0`); `PESAJE-venta-26` revertido con movimientos de Entrada (movs 22/23; stock de #18/#20 restaurado a 150.00/80.00).
- [ ] Negocio: poblar `rendimiento_m2_por_kg` en formulaciones activas — hoy **1 de 314** (solo form#966/VITROGLASS = 9.74 m²/kg). PASO 3 bloqueado hasta que negocio confirme el contenido neto de envases (ver `doc/entrenamiento_3/manifiestos/propuesta_rendimientos_fase3.md`); `#476 SELLADOR INICIAL` sin precio y equivalencia `#83 ↔ #214` sin confirmar.
- [X] Validar `direccion` server-side en `Obras::guardar_ajax()` — 2026-09-18: `trim` + JSON `{success:false}` si vacía (mismo patrón que `nombre`/`cliente_id`); evita 1048.
- [X] Cosméticos de Obras: link real en "Aún no hay entregas" (detalle de obra) y columnas del modal de entrega (`COALESCE` + `fmtCantidad`) — corregidos 2026-09-10

## 🟡 Pendientes de presentación / catálogo (QA 14-sep)

- [ ] BUG-DATA-01 / T7: catálogo incompleto — 460 productos en $0 + 1 en NULL, 462 con descripción placeholder, 492 sin foto/rendimiento y 180 fabricados sin formulación activa. **No bloqueante para I4; decisión de negocio.**
- [X] D6: re-QA de "Completada" E2E (pesaje → lote + entrada PT) — **ejecutado 2026-09-18**, OV-2026-0009 Completada, lote `PROD-20260918-22-2268` generado, entrada PT stock #22 0→1, PESAJE-venta-28 = 3. Fix: `dov.unidad` + ENUM `ordenes_venta.estatus` con `'Completada'`. Ver `doc/CHECKLIST_PRUEBAS_MANUAL_2026-09-17.md` (B4/B5 ✅).
- [X] T3: confirmar por UI el alta/edición de descuentos — **ejecutado 2026-09-18**, TEST-QA-DESC-01 crear 5% Activo / editar 7% Inactivo / eliminar sin 500. Fix: `DescuentosModel` `$dateFields = ['created'=>'fecha_creacion','updated'=>'fecha_modificacion']`. Ver `doc/CHECKLIST_PRUEBAS_MANUAL_2026-09-17.md` (A5 ✅).
- [X] `PLAN_FIX_DATATABLES_LENGTH_SELECT.md` aplicado 2026-09-17 (regla global en `theme.css` + eliminado el bloque muerto de `compras/proveedores`); queda la verificación visual en proveedores/OC.

## 🟡 Pendientes Facturación

- [ ] Implementar Automatización de Importación (Cron Job / Lazy Load)
- [ ] Vincular Facturas a Obras/Ordenes de Compra
- [/] Implementar envío de factura (PDF y XML) por correo electrónico directamente desde el ERP

## 💡 Mejoras Futuras

- [ ] Agregar botón de "Exportar a Excel" en todas las tablas
- [ ] Agregar logs de cambios y acciones realizadas en bitácora, de todas las secciones del sistema
- [ ] Revisar los logs de la bitácora en cada una de las secciones del sistema
- [ ] Optimizar consultas de permisos usando caché de CodeIgniter
- [ ] Agregar validación de permisos en todas las secciones del sistema
- [ ] Agregar un módulo para recordatorios de cumpleaños. Cuando sea el cumpleaños del usuario o de algún trabajador, agregar la notificación en el sistema y al trabajador o usuario, mostrarle una pantalla de felicitación
- [ ] Agregar un nuevo permiso en la sección "administrador", que sea "super administrador", el cual servirá para validaciones especiales como por ejemplo, editar datos fiscales de trabajadores o permisos bloqueados en general, por ejemplo, si un administrador requiere editar estos datos, saldrá una alerta para que el "super administrador" pueda validar la acción o bien ingresar directamente la contraseña del "super administrador".
- [ ] Agregar un módulo para recordatorios de citas
- [ ] Agregar un calendario en el CRM
- [ ] Convertir el array ViewData en un objeto (DTO)
- [ ] Mejorar botones en las tablas de resultados y mejorar color de textos en alertas con fondos de colores y modals (se pierden las letras con color negro y fondos de color)
- [ ] Agregar módulo para cargar logo del sistema, el cual se usará en todos los pdf, tickets, recibos, etc.
- [ ] Mejorar contrato de usuario, homogeneizarlo con los pdf que genera el sistema.
- [ ] Sweet alerts y notify shows funcionan, pero algunas alertas como warning, presentan contrastes de colores extraños o de poco contraste que dificultan la visión. Es necesario verificar y corregir

## 📚 Documentos de referencia vigentes (doc/)

| Documento | Uso |
|-----------|-----|
| `DOCUMENTACION_TECNICA.md` | Guía arquitectónica, estándares de desarrollo y estado de avance |
| `REGLAS_TECNICAS.md` | Reglas técnicas que todo agente IA debe seguir al tocar el código |
| `cotizacion.md` | Requerimientos de negocio originales del cliente (fuente de verdad de módulos) |
| `produccion.md` | Workflow crítico de producción (venta → producción → entrega) |
| `API_RELOJ_CHECADOR.md` | Documentación de la API del reloj checador y proxy ZKTeco |
| `SISTEMA_ALERTAS_NOTIFICACIONES.md` | Arquitectura del sistema global de alertas/notificaciones |
| `GUIA_PRODUCCION_POST_IMPORTACION.md` | Operación de producción después de importar Excel |
| `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` | Checklist manual base — hoja de resultados vacía; lo ejecutado de la iteración 3 vive en `CHECKLIST_PRUEBAS_UI_ITERACION3.md` |
| `CHECKLIST_PRUEBAS_UI_ITERACION3.md` | QA UI de la iteración 3 (2 pasadas + fixes): estado de BUG-UI-01 a BUG-UI-09 |
| `AUDITORIA_MODULO_OBRAS_2026-08-28.md` | Auditoría de Obras (iteración 3): brechas, riesgos, plan y pendientes |
| `CHECKLIST_PRUEBAS_OBRAS_ITERACION3.md` | Resultados QA de Obras (TEST-QA) y cierre de BUG-1 a BUG-8 |
| `PLAN_ENVIOS_TRES_GUERRAS.md` | Diseño de la integración con paquetería (iteración 4) |
| `CHECKLIST_PRUEBAS_MANUAL_2026-09-17.md` | Checklist manual para el cierre de la iteración 3 (regresión, Completada E2E, correos, módulos y PASO 3) |

> Nota: los planes, handoffs y verificaciones de iteraciones ya completadas se eliminaron de `doc/` el 2026-08-27; los prompts de la Iteración 3 de Obras se eliminaron el 2026-09-10 (los ya versionados se conservan en el historial de git por si se necesitan).
