# TODO — Sistema ERP CHISA

**Última actualización:** 2026-11-19  
**Rama activa:** `iteracion-6` (I5 mergeada a `main`; `iteracion-5` retirada).  
**Entorno:** `ENVIRONMENT=development` · `https://erp.chisarecubrimientos.com.mx`

**Handoff:** el sistema **ya cubre el núcleo operativo** de la cotización (usuarios, RH, compras, CRM, POS, obras, producción, almacén, facturación sandbox, contabilidad de lectura). El tramo vivo es **afinar datos de entrenamiento y go-live de negocio**, no reimplementar P1–P9 ni Fase 5.

No timbrar/cobrar real. No autorizar `PRE-2026-0001`. No tocar `OV-2026-0009`. No SMTP/WhatsApp reales.

---

## 1. Veredicto de auditoría (2026-11-10)

| Capa | Conclusión |
|------|------------|
| Código vs seguimiento `doc/*.md` | Alineado: ítems marcados hechos en TODO/checklist coinciden con controladores/modelos actuales |
| Cotización v1.1 + `cotizacion.md` | **~78 % operativo interno.** Huecos contractuales: Bixpe/KONECT, pasarela, autofactura, QC viscosidad, QR/lector almacén, API Tres Guerras, go-live Facture, hub único de reportes, SMTP |
| Diagrama general (`diagrama_general.png` / PDF diagramas) | Módulos del mapa **existen** en menú. Nodos naranja/morado del diagrama = licencias/hardware/APIs de terceros (no código ERP) |
| Documentación técnica | Canónica: `public_html/DOCUMENTACION_TECNICA.md`. `doc/DOCUMENTACION_TECNICA.md` = puntero |
| Archivos `.doc/.docx` en `doc/` | **No son seguimiento de desarrollo.** Son papelería de entrenamiento (cotizaciones Word, etiqueta sobre, depósito). Se conservan |

**No está “listo para go-live comercial”** hasta SMTP, Facture producción, 2FA en `production`, precios de catálogo y decisión de los ítems ⏸. **Sí está listo para seguir el entrenamiento I6** (materiales, productos, CCO, raya, sucursales CHISA/HAPA).

---

## 2. Iteraciones (no borrar dossiers)

| Iteración | Código | Material en `doc/` | ¿Sigue haciendo falta? |
|-----------|--------|--------------------|------------------------|
| I1–I3 | Mergeado a `main` / ancestro de I5 | Dossiers retirados 2026-10-06; tags git `entrenamiento-3-hist`, `facturacion-contabilidad-i4` | Histórico. No recrear |
| I4 | Cerrada 2026-10-06; contenido en `main` | — | Cerrada |
| I5 | Mergeada a `main` el 2026-11-18 | Handoff `HANDOFF_ENTRENAMIENTO_SANDBOX_2026-11-10.md` | Cerrada. Rama `iteracion-5` eliminada tras el merge |
| Entrenamiento 4 | Fase 5 APU/PRES | `entrenamiento_4/` (Excel/PDF de generadores, ZOCLOS, químicos) | **Sí.** Fuente de APU y proveedores químicos. No borrar |
| Entrenamiento 5 | CCO, POS, papelería | `entrenamiento_5/` (cotizaciones 2026 Word/PDF) | **Sí.** Semilla y contraste de cartas. No borrar |
| Entrenamiento 6 | Raya, CCO PDF, HAPA, semáforos | `entrenamiento_6/` + `entrenamiento.md` | **Sí, activo.** Lista de raya, PDFs 2026, formatos contables. No borrar |
| Brief I6 | Implementado en código | `entrenamiento_6/entrenamiento.md` | Conservar como especificación |

Checklist Grokbot vigente: `PRUEBAS_GROKBOT_I6.md`. Los reportes de **21-oct-2026** (`REPORTE_QA_ERP_CHISA.md`, `PRUEBAS_GROKBOT_I5.md`, `REPORTE_PRESENTACION_ERP_CHISA2.md`) son **históricos** (varios fallos ya corregidos: lápiz de materiales, CCO sembradas, POS variantes, DataTables).

---

## 3. Cumplimiento por módulo (cotización + diagrama)

Leyenda: **OK** flujo diario interno · **Parc.** falta smoke/SMTP/datos · **Contrato** requiere tercero o decisión · **No** no hay código.

| § Cotización | Módulo | Estado | Notas |
|--------------|--------|--------|-------|
| 1 | Administración usuarios | OK | 2FA en código; inactivo en `development` |
| 2 | RH | OK | Finiquito oficial = no aplica (nota cotización). Viáticos obra: campo, no flujo RH dedicado |
| 3 | Reloj Bixpe / KONECT | Contrato | Hay ZKTeco/`ApiReloj`/iclock. GPS obras Bixpe **no** |
| 4 | Proveedores / compras | OK | Cotizaciones + comparar validados 2026-10-21. Enlace OC↔factura UI diferido. PDF OC = HTML imprimible |
| 5 | CRM clientes | OK | Calendario CRM no. Autofactura/micontador pleno = contrato |
| 6 | Ventas / POS | OK | Canal mostrador vs obra. **Sin** pasarela de pagos |
| 7 | Obras / cálculo | OK | Fase 5 PRES- + CCO- comercial. Crear obra alerta INE/RH |
| 8 | Producción | OK | Falta QC **viscosidad**. Touch/etiquetas lote sí. Notificación al completar = cambio de estatus/campana, no push |
| 9 | Almacén | OK | Falta salida por **lector QR**. Tres Guerras = costo interno en CCO, no API |
| 10 | Facturación | Parc. | Sandbox OK 2026-11-18 (`cli_probe` ok, token 2026-02-04). Tras pago de OV: botón Facturar (sandbox) abre `Facturas/crear?orden_id=` (prefill). Timbrado real y SMTP siguen bloqueados (`emitir` sigue en debug). |
| 11 | Contabilidad | OK | Pólizas desde orígenes. DIOT/conciliación auto/Aspel pleno = fuera |
| 12 | Reportes | Parc. | Por módulo (Excel/PDF). No hay hub único “Reportes” del diagrama |

Detalle de filas: `MODULOS_ESTADO_CHECKLIST.md`.

---

## 4. Entrenamiento 6 — corroborado en código

| Pedido del brief | Dónde | Cumple |
|------------------|-------|--------|
| Expediente lista de raya (comidas, bancos, asistencia) | `contabilidad/ExpedienteRaya` | Sí (no sustituye nómina) |
| Semáforo materiales, no revisado, unidad, no borrar insumo | `compras/EntrenamientoMateriales` | Sí. 2026-11-18: 150 insumos, 0 revisados. Galería 23 fotos MP sin ligar a BD. Modal viejo eliminado |
| Marcar producto fabricado → enlaza SKU, insumo **sigue** | `InsumosModel::enlazar_producto_entrenamiento` | Sí (decisión: no borrar, BOM lo necesita) |
| Entrenamiento productos + historial bloqueado hasta principal | `produccion/EntrenamientoProductos` | Sí. El candado de historial queda **solo** en entrenamiento. La pantalla operativa `HistorialFormulaciones` lista activa + versiones. Huecos: sin fórmula / sin activa; métrica fórmulas con `cliente_id` |
| Completar lote sigue exigiendo pesaje | Producción (regla P1–P9) | Sí |
| CCO, sucursales CHISA/HAPA, PDF/Excel, pie, 3 Guerras interno | `ventas/CotizacionesObra`, `ventas/Sucursales` | Sí |
| Sembrar CCO desde PDF entrenamiento 6 | `entrenamiento_sembrar` + `indexar_pdfs_entrenamiento` | Sí |
| Alerta RH al crear obra sin INE | Obras / Ventas | Sí |
| POS mostrador vs obra, matraz, variantes MICRO/COLOR 2 | `ventas/Pos` | Sí (corregido post-QA 21-oct) |
| Permisos I6 en Roles | `permissions.php` | Sí |

---

## 4.1 I6 código (2026-11-19) — formulaciones y ciclo

Hecho en código (sin nuevas tablas, sin importar el xls de fichas):

- Historial: búsqueda producto + cliente + texto; por defecto activa e historial; asociar cliente (CEO) sin cambios.
- Cotización POS: consulta de MP (`solo_consulta`). Confirmar OV: preorden + alerta. CCO: botón Consultar materia prima (no crea `PRE-`).
- Dashboard producción: % entrega OV/obra y enlace a ficha de fórmula.
- Tras pago OV: enlace a factura sandbox. No SMTP. No timbrar RFC real.

Pendiente de **dato** (no de pantalla): asociar `cliente_id` a versiones (0/956 al inicio de I6).

---

## 5. Pendiente para dar por “listo” (negocio / datos)

Orden sugerido. No son reescrituras de módulo.

1. **Datos:** precios `precio_venta` / rendimientos / contenido neto cubeta (no inventar). Entrenamiento de materiales y productos es la vía.
2. **SMTP** (`application/config/email.php` no existe): correos de cotización, OC, factura.
3. **Facture:** ambiente `produccion` + re-OAuth + smoke RFC de prueba.
4. **`ENVIRONMENT=production`** y 2FA en dispositivo nuevo.
5. Decisión: Bixpe/KONECT (licencia), API Tres Guerras, autofactura, pasarela, viscosidad, lector QR.
6. Higiene (no bloquea demo): SQL aún en varios controladores (`Notifications`, `Productos`, `Reportes`, …); no ampliar. `max_input_vars=1000`.
7. Re-smoke **UI** pesaje D5–D6 cuando haya una OV TEST con insumos convertibles y stock (evidencia CLI 28-sep ya existe).

Fuera de alcance hasta que negocio lo pida: DIOT, conciliación bancaria automática, export Aspel pleno, calendario CRM.

---

## 6. Documentos en `doc/` (2026-11-10)

### Vigentes (operación / contrato)

| Archivo | Uso |
|---------|-----|
| `../DOCUMENTACION_TECNICA.md` | Arquitectura (canónico) |
| `DOCUMENTACION_TECNICA.md` | Puntero |
| `REGLAS_TECNICAS.md` | Reglas agente |
| `TODO.md` | Este seguimiento |
| `MODULOS_ESTADO_CHECKLIST.md` | Estado por función |
| `cotizacion.md` + PDF cotización v1.1 | Contrato |
| `diagrama_general.png` + `SISTEMA ERP DIAGRAMAS CHISA.pdf` | Mapa funcional |
| `produccion.md` | Workflow venta→producción→entrega |
| `GUIA_PRODUCCION_POST_IMPORTACION.md` | Activar fórmulas importadas |
| `SISTEMA_ALERTAS_NOTIFICACIONES.md` | Campana |
| `API_RELOJ_CHECADOR.md` + `iclock/` | ZKTeco |
| `PLAN_ENVIOS_TRES_GUERRAS.md` | Diseño API (no código) |
| `PRUEBAS_GROKBOT_I6.md` | Checklist navegador vigente |
| `HANDOFF_ENTRENAMIENTO_SANDBOX_2026-11-10.md` | Cierre I5 (galería, menú, sandbox). Histórico de esa pasada |
| `entrenamiento_4/` `entrenamiento_5/` `entrenamiento_6/` | Dossiers |

### Históricos (no ejecutar, no borrar evidencia)

`CHECKLIST_MANUAL_MODULOS_ITERACION_2026-08-25.md`, `SMOKE_P0_AGENTE_EJECUTOR.md`, `PRUEBAS_GROKBOT_I5.md`, `PRUEBAS_GROKBOT_PRESENTACION.md`, `REPORTE_QA_ERP_CHISA.md`, `REPORTE_PRESENTACION_ERP_CHISA2.md`.

### Eliminado en esta depuración

`LISTA_CORRECCIONES_OK_FALLA.md` — fragmento duplicado del QA del 21-oct.

---

## 7. Archivo histórico de código (no reabrir)

Overhaul P1–P9 2026-08-20. Compras T1–T7 2026-09-28. POS B3–B6. Fase 5 APU 2026-09-29. Handoff cotizaciones CRM+proveedor 2026-10-21. Detalle de smokes: checklist manual y `SMOKE_P0_AGENTE_EJECUTOR.md`.
