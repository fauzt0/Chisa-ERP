# TODO - Sistema ERP CHISA

**Última actualización:** 2026-09-10
**Desarrollador:** Fausto Solano - CHISA Recubrimientos
**Rama activa:** `iteracion-3` (cambios del agente cloud integrados; pendiente PR a `main` tras validación manual de UI)

---

## 📝 Notas Técnicas

- [ ] Validar límite de `max_input_vars` en PHP para formularios con muchos checkboxes. (IMPORTANTE: Esto se debe realizar en cada deployment antes de desplegar a producción)

## 🟡 Estatus del proyecto

- [X] Desarrollo
- [ ] Iteraciones
- [ ] Pruebas — checklist manual pendiente en `doc/CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` (hoja de resultados vacía)
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

## 🟡 Iteración módulo de "Producción" (pendiente)

- [ ] Mejorar y cuadrar los procesos de producción a los procesos actuales. Los productos tienen una formulación y se fabrican en lotes de cubetas, por lo que se debe tener un control de inventario de materias primas y productos terminados (por kilo, litro, etc). El flujo de trabajo se especifica en el archivo `doc/produccion.md`

## 🟡 Pendientes de Obras / Iteración 4

- [ ] Validación manual de UI tras el merge del agente cloud (dashboard por permisos, toggle de tema, login/2FA). **Ojo:** el usuario demo `presentacion@chisa.mx` no tiene permisos del módulo Obras; usar una cuenta con permisos (ids 1, 6 o 7).
- [ ] Integrar `iteracion-3` → `main` (PR) una vez validado
- [ ] API de paquetería "Tres Guerras" (diseño listo en `doc/PLAN_ENVIOS_TRES_GUERRAS.md`)
- [ ] Tab "Entregas" también en la vista CRM Ventas (`ventas/obras/detalle.php`)
- [ ] Mover el SQL directo de `Obras::actualizar_ajax` al modelo (auditoría B5, diferido)
- [ ] Limpiar residuos de pruebas antiguas: OVs `OV-TEST-001`, `OV-2026-0004` y cliente ficticio "Empresa de Prueba S.A."
- [ ] Negocio: poblar `rendimiento_m2_por_kg` en formulaciones activas (Producción > Productos)
- [ ] Validar `direccion` server-side en `Obras::guardar_ajax()` (hoy el formulario la marca `required`; sin ella la BD responde error 1048) — hallazgo H del checklist de Obras
- [X] Cosméticos de Obras: link real en "Aún no hay entregas" (detalle de obra) y columnas del modal de entrega (`COALESCE` + `fmtCantidad`) — corregidos 2026-09-10

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
| `CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md` | Checklist manual activo — pendiente de ejecutar (hoja de resultados vacía) |
| `AUDITORIA_MODULO_OBRAS_2026-08-28.md` | Auditoría de Obras (iteración 3): brechas, riesgos, plan y pendientes |
| `CHECKLIST_PRUEBAS_OBRAS_ITERACION3.md` | Resultados QA de Obras (TEST-QA) y cierre de BUG-1 a BUG-8 |
| `PLAN_ENVIOS_TRES_GUERRAS.md` | Diseño de la integración con paquetería (iteración 4) |

> Nota: los planes, handoffs y verificaciones de iteraciones ya completadas se eliminaron de `doc/` el 2026-08-27; los prompts de la Iteración 3 de Obras se eliminaron el 2026-09-10 (los ya versionados se conservan en el historial de git por si se necesitan).
