# Pruebas Grokbot — entrenamiento 6

Solo navegador. No cobres, no timbres, no canceles `OV-2026-0009`, no autorices `PRE-2026-0001`.

| Dato | Valor |
|------|--------|
| URL | https://erp.chisarecubrimientos.com.mx/ |
| Usuario | soporte2@especialistasweb.com.mx |
| Contraseña | la vigente en el VPS (confirmar con Fausto) |

Bitácora: anota cada Guardar y revierte.

## Checklist

| ID | Qué | OK / falla |
|----|-----|------------|
| C1 | Contabilidad → Expediente lista de raya: hay comidas, bancos, asistencia. Cuadrilla Monterrey: Gerardo En RH; el resto Pendiente. | |
| C2 | Entrenamiento de materiales: filtro No revisado (rojo). RES-002 lápiz abre ficha. Unidad y Revisado se guardan. No borra el insumo. | |
| C3 | Marcar producto fabricado en un insumo de prueba: aparece producto enlazado; el insumo sigue en el listado. Revertir revisado. | |
| P1 | Producción → Entrenamiento de productos: listado con semáforo. Abrir un fabricado. Historial bloqueado hasta marcar principal verificada. | |
| P2 | Tras verificar principal, historial se ve. Completar lote sigue pidiendo pesaje (Lotes). | |
| O1 | CRM Ventas → Cotizaciones de obra: hay sucursal CHISA (`SUC-MATRIZ`, Iztacalco, logo CHISA) y sucursal HAPA (`SUC-HAPA`, Del Valle, sin logo CHISA). CCO-2026-0003 total $319,789.28. PDF y Excel descargan. | |
| O2 | Nueva cotización Borrador con un concepto, costo envío 3 Guerras, pie. Guardar. No pasar a obra activa. | |
| O3 | Obras: crear borrador con cliente existente. Si el responsable no tiene INE, debe avisar (no bloquear). Si el INE ya está en RH, debe aparecer en documentos de la obra. | |
| O4 | Cotizaciones de obra: hay CCO Borrador con PDF de `COTIZACIONES 2026 PDF` en el expediente y carpeta «3 Guerras / envío». CCO-2026-0003 no cambia de total. | |
| O5 | Abrir CCO-2026-0418 → Vista/PDF: papelería HAPA (Moras 506, hapasoluciones.com, sin logo CHISA). Fecha a la derecha, CONSTRUCTORA / P R E S E N T E / AT'N / OBRA / PRES. REF., tabla CONCEPTO-UNIDAD-CANTIDAD-PRECIO-IMPORTE, SUBTOTAL y TOTAL. Segunda hoja FORMA DE PAGO y NOTAS. El costo 3 Guerras no sale en la hoja del cliente. Excel trae hoja «Forma de pago». | |
| P3 | Administración → Roles: existen checkboxes `entrenamiento_materiales`, `entrenamiento_productos`, `entrenamiento_sembrar`, `ventas_cotizaciones_obra`, `ventas_pie_cotizacion`, `contabilidad_expediente_raya`. Desmarcar uno oculta el menú y niega la pantalla. | |
| V1 | POS: canal mostrador vs obra. Matraz no agrega al ticket. Variantes solo MICRO/COLOR 2. | |

Al salir: POS en $0, RES-002 como al inicio, fórmula 957 inactiva sin cliente.
