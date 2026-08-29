# PROMPT — Iteración 3: Módulo de Obras (auditoría y mejora)

> **Fecha:** 2026-08-28
> **Rama de trabajo:** `iteracion-3` (creada desde `main` @ `e4300f1`)
> **Modelo recomendado:** Claude Opus (alternativas: Claude Sonnet 4.6, Gemini 2.5 Pro / GPT-4.5, DeepSeek Pro)
> **Uso:** pegar este contenido en una nueva conversación de agente cloud de Cursor (o agente externo) apuntando a la rama `iteracion-3`.
> **Imágenes de referencia (resumen de obra):** `doc/resumen1.jpeg` … `doc/resumen5.jpeg` — el agente las lee por ruta desde el repo.

---

# Iteración 3 — Módulo de Obras: auditoría, mejora y cierre de flujo completo

## Contexto del proyecto
ERP en producción de Chisa Recubrimientos (recubrimientos y pinturas), CodeIgniter 3 + PHP 8.x + MySQL/MariaDB + Bootstrap 5.3/AdminLTE + jQuery + DataTables.
Workspace: `/home/admin/domains/erp.chisarecubrimientos.com.mx/public_html`
URL de producción: `https://erp.chisarecubrimientos.com.mx` (NO hacer DROP/TRUNCATE; datos reales).

ANTES DE TOCAR CÓDIGO, lee obligatoriamente:
- `doc/REGLAS_TECNICAS.md` (reglas técnicas y estándares del proyecto)
- `doc/DOCUMENTACION_TECNICA.md` (arquitectura, patrones DataTables AJAX, permisos, MY_Controller/MY_Model)
- `doc/cotizacion.md` (sección del módulo de Obras: requerimientos de negocio originales)
- `doc/produccion.md` (workflow de producción: venta/obra → pedido → producción → entrega)
- `doc/TODO.md` (estado del proyecto)

## Objetivo
Analizar a fondo el módulo de Obras, corroborar que el flujo de negocio completo funcione de extremo a extremo, corregir brechas y mejorar la experiencia. Los cambios deben respetar el estilo existente del proyecto (controladores MY_Controller, modelos, vistas con DataTables AJAX, toasts con `showErpToast()`, partial `modal_styles.php`).

## Flujo de negocio que DEBE corroborarse (y arreglarse si falla algo)
1. **Nueva obra (alta y edición)**: creación/edición con datos de cliente, ubicación, fechas estimadas, anticipo, IVA, estatus.
2. **Cálculo de materiales**: al agregar productos a la obra (con área m², secciones), el sistema calcula kilos/insumos usando formulación del producto y rendimiento m²/kg (ver `ObrasModel::calcular_materiales_linea_obra` y `::calcular_materiales_obra`).
3. **Alta de orden de obra y productos requeridos**: consultable y generable desde DOS lugares:
   - Módulo Obras: `application/controllers/obras/Obras.php` y `application/views/obras/` (`index.php`, `detalle.php` con tabs Productos/Materiales/Archivos/Comentarios/Datos/Pagos, `partials/vinculo_venta.php`)
   - CRM Ventas: `application/controllers/ventas/ObrasVentas.php` y `application/views/ventas/obras/` (`main.php`, `detalle.php`, `factura.php`, `recibo.php`)
4. **Materiales → Compras o Producción**: verificar que exista y funcione el camino para pasar los insumos calculados de la obra a pre-órdenes de compra o solicitudes de producción (`ObrasModel::verificar_insumos_y_preordenes_obra`, `::crear_solicitudes_produccion_desde_obra`, `::consultar_insumos_obra`). Si falta la UI para gatillar esto, implementarla.
5. **Resumen de obra**: comparar el PDF actual (`application/views/obras/pdf_resumen.php`, `exportar_pdf` en `Obras.php`) contra las imágenes de referencia `doc/resumen1.jpeg`, `doc/resumen2.jpeg`, `doc/resumen3.jpeg`, `doc/resumen4.jpeg`, `doc/resumen5.jpeg`. Adecuar el resumen (contenido, secciones, estilo tipo documento Excel histórico) para que se asemeje a las referencias, sin perder los datos que ya calcula (avance m², partidas por sección, montos, anticipo, pagos).
6. **Seguimiento de la obra**: con los productos vendidos (órdenes de venta vinculadas), estatus de entrega por producto (revisar tablas `entregas_almacen` y `detalle_entregas_almacen` en `database/almacen.sql` y su conexión con obras/OV) y el avance de la obra. Si la conexión obra↔entregas no existe, diseñarla e implementarla (vincular entrega de almacén a la obra/OV, mostrar estatus de entrega en el detalle de la obra).

## Alcance excluido (diseño sí, código NO)
- **Integración con paquetería "Tres Guerras"** (envíos con guías mediante API propia): NO implementar, es de la iteración 4. PERO dejar contemplado en el diseño: campos necesarios (guía de envío, estatus de paquetería, fecha de envío), tabla o columnas sugeridas, endpoints de la API a consumir (crear guía, tracking), y punto de integración en el flujo (desde el seguimiento de la obra / entrega). Documentarlo en un archivo `doc/PLAN_ENVIOS_TRES_GUERRAS.md` para la iteración 4.

## Entregables
1. **Auditoría**: `doc/AUDITORIA_MODULO_OBRAS_2026-08-28.md` con: mapa del flujo actual vs el flujo requerido (cada paso ✅/⚠️/❌ con archivos y líneas), brechas encontradas, riesgos y plan de corrección priorizado.
2. **Implementación** de las correcciones y mejoras de la auditoría (siguiendo las reglas de `doc/REGLAS_TECNICAS.md`).
3. **Pruebas**: al estar en producción, probar con datos de prueba con prefijo `TEST-QA-` y cantidades mínimas; documentar resultados (`doc/CHECKLIST_PRUEBAS_OBRAS_ITERACION3.md`). No pagar/cobrar nada real. No DROP/TRUNCATE. Cancelar lo de prueba al terminar.
4. **Resumen final**: archivos modificados, SQL ejecutado (o script SQL propuesto), desviaciones del plan.

## Reglas de trabajo
- Trabaja en la rama `iteracion-3`; haz commits pequeños con mensajes descriptivos en español (estilo conventional commits del repo, ej. `feat(obras): ...`).
- No hagas push a `main`. No toques nada fuera del módulo de obras y sus dependencias directas (ventas/almacen/producción solo si el flujo lo exige y documentándolo).
- Al terminar, entrega el reporte de resumen con la lista de pendientes para la iteración 4.
