# Checklist de pruebas manual — ERP CHISA (cierre iteración 3)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-09-17 |
| **URL** | https://erp.chisarecubrimientos.com.mx |
| **Rama / commit base** | `iteracion-3` · `32782c8` (12 commits locales sin push) |
| **Usuario principal** | `soporte2@especialistasweb.com.mx` (EHWEB, permisos amplios; para Obras también sirven ids 1, 6 y 7) |
| **Usuario demo** | `presentacion@chisa.mx` — **no** tiene permisos de Obras |
| **Leyenda** | ✅ OK · ⚠️ con observación · ❌ falla · SKIP (no ejecutado, anotar por qué) |

## Reglas de la sesión

1. Prefijo **`TEST-QA-`** en folios/comentarios; cantidades mínimas (1 unidad / 1 línea).
2. **No** cobrar/pagar real, **no** timbrar CFDI, **no** enviar correo/WhatsApp a clientes/proveedores reales (usa tu propio correo), **no** autorizar `PRE-2026-0001`.
3. Completada de producción **sin** pesaje debe fallar; un segundo pesaje debe fallar.
4. Cancelar los TEST al terminar (motivo ≥ 10 caracteres) y anotar folios en la hoja final.
5. Desktop ~1440×900; smoke móvil 390×844 en las pantallas clave. Evidencia: captura + folio + Network si hay 500.

## Estado de partida (17-sep-2026, para comparar)

- Productos: 494 · con precio: **33** · sin precio: **461** (sin dato comercial).
- Formulaciones activas: 314 · con `rendimiento_m2_por_kg`: **1** (#966 VITROGLASS = 9.74).
- Lotes de producción: **0** · movimientos `Produccion`: **0** · obras activas: **2** (OB-00001, OB-00002).
- `OB-00006` quedó `activo=0` / `Cancelada`; `PESAJE-venta-26` revertido (insumos #18 y #20 en 150.00 / 80.00).
- Ruido tolerado: avisos PHP 8 `Severity: 8192` (deprecations preexistentes). Error real = `Fatal error`, 500, blanco o acción muerta.

---

## A. Regresión de los fixes del 17-sep (rápido, ~10 min)

| ID | Acción | Esperado | Resultado | Evidencia / notas |
|----|--------|----------|-----------|-------------------|
| A1 | `/compras/Proveedores` y `/compras/OrdenesCompra`: mirar "Mostrar X registros" en tema claro y oscuro | Número y flecha **no** se enciman | | |
| A2 | POS: buscar `MICRO` (teclado real) | Se oculta "Más Vendidos"; resultados debajo | | |
| A3 | `/produccion/Lotes` | Carga con tabla vacía, sin 500 (BUG-UI-04) | | |
| A4 | `/almacen/Inventario` | 200, KPIs y listados pintan (BUG-UI-05) | | |
| A5 | `/ventas/Descuentos`: crear `TEST-QA-DESC-01` → editar → desactivar, todo por UI | Persiste sin 500 (BUG-UI-08 / T3) | | |
| A6 | `/ventas/ObrasVentas` | No aparece OB-00006; KPIs cuadran con 2 obras (BUG-UI-09) | | |
| A7 | `/compras/Insumos`: #18 y #20 | Stock 150.00 / 80.00 (reverso aplicado) | | |

## B. D6 — "Completada" E2E de producción (pendiente principal)

Usar un producto producible: **#22 CHISA GLASS REF 308** (form 684); alternos #84, #36, #35, #10.

| ID | Acción | Esperado | Resultado | Evidencia / notas |
|----|--------|----------|-----------|-------------------|
| B1 | Verificar insumos con stock del producto elegido (Simulador de Producción, 1 lote) | Sin faltantes | | |
| B2 | Crear cotización/OV TEST (1 unidad) y confirmarla | **0 preórdenes** en cotización; al confirmar, preórdenes solo si hay faltantes | | |
| B3 | Confirmar pesaje (cantidades sugeridas) | Insumos bajan **una sola vez**; 2º intento rechazado | | |
| B4 | Marcar **Completada** | Crea **lote** + entrada de producto terminado; **sin** 500 | | |
| B5 | `/produccion/Lotes` y detalle del lote / etiqueta | El lote nuevo aparece y se consulta | | |
| B6 | Revisar insumos de B3 | **No** bajaron otra vez | | |
| B7 | Limpieza | Documentar folios (una OV completada no se cancela; usar TEST y anotar) | | |

## C. Casos QA que quedaron pendientes (T9)

| ID | Acción | Esperado | Resultado | Evidencia / notas |
|----|--------|----------|-----------|-------------------|
| C1 | B6: cobrar/entregar una OV TEST con precio ≠ 0 | Baja stock de PT; insumos no se mueven; recibo OK | | |
| C2 | E3: OC TEST de 1 línea → borrador + PDF → cancelar | Subtotal/IVA 16 %/total correctos | | |
| C3 | E5: preorden TEST → autorizar | OC generada sin duplicar. **NO tocar `PRE-2026-0001`** | | |
| C4 | E8: preview de correo/WhatsApp de la OC | Preview OK; **no enviar** | | |
| C5 | G2–G7: nómina de periodo **futuro** TEST: borrador ($0) → calcular → recalcular → comidas/HE inline → cancelar (motivo ≥ 10) | Calcula sin duplicar conceptos; cancelación OK | | |

## D. Correos (pendientes del TODO)

| ID | Acción | Esperado | Resultado | Evidencia / notas |
|----|--------|----------|-----------|-------------------|
| D1 | Enviar por correo una OV TEST a **tu propio correo** | Llega con formato/adjunto correcto | | |
| D2 | Enviar por correo una OC TEST a un proveedor TEST o a tu correo | Llega correctamente | | |
| D3 | Facturación: envío de factura (PDF/XML) por correo | Si no está implementado, marcar SKIP y anotarlo | | |

## E. Smoke por módulo (módulos que siguen sin verificar en `TODO.md`)

Para cada módulo: listado + detalle + una acción clave, **sin 500**.

| ID | Módulo | Acción | Resultado | Notas |
|----|--------|--------|-----------|-------|
| E1 | Proveedores | Listado, filtros, detalle, insumos vinculados | | |
| E2 | Producción | Productos (chips/búsqueda/recetas), Dashboard, Lotes, Simulador 1–2 kg | | |
| E3 | Facturación | Listado, emisión/sincronización (sin timbrar real), descarga | | |
| E4 | Usuarios | Listado, alta/edición de un usuario TEST | | |
| E5 | Permisos | Revisar asignación por rol sin bloquearse | | |
| E6 | Bitácora | Logs recientes de acciones | | |
| E7 | Dashboard | Tarjetas por rol; "Personalizar" | | |
| E8 | RH | Empleados, expediente, incidencias, vacaciones, contrato PDF (sin `{{ }}`) | | |
| E9 | Reloj checador | Dashboard de dispositivos / sync | | |
| E10 | Citas y Calendario | Carga de vistas y alta mínima | | |

## F. Catálogo / presentación (BUG-DATA-01)

| ID | Acción | Esperado | Resultado | Notas |
|----|--------|----------|-----------|-------|
| F1 | POS: buscar `VITROGLASS`, `CHISA MAR`, `HEALER`, `PINTU FLEX` | Precio ≠ $0.00 | | |
| F2 | Cotización TEST | `iva = subtotal × 0.16` correcto | | |
| F3 | Revisar descripciones placeholder (462) | Decisión: limpiar/asignar texto | | |
| F4 | Lista de 180 fabricados sin formulación activa | Decisión: versión nueva / Reventa / desactivar | | |
| F5 | Fotos faltantes (492) | Decisión de alcance | | |

## G. Datos de negocio para cerrar el PASO 3 (recolección, no prueba técnica)

| ID | Dato que falta | ¿Resuelto? | Notas |
|----|----------------|------------|-------|
| G1 | Contenido neto (kg) de la **CUBETA** de los 28 productos de la lista 2025 (¿19 kg?) | | |
| G2 | Contenido neto del **GALÓN** (o 3.785 L + densidad) | | |
| G3 | Presentación real de las 16 filas **"(sin pres.)"** | | |
| G4 | Aprobar POLY-COLOR: 250 g/m² → **4 m²/kg** | | |
| G5 | Precio de venta de **#476 SELLADOR INICIAL** | | |
| G6 | Planta: ¿`SOLUCION DE RESINA` **#83** ≡ **#214 EC-1**? | | |
| G7 | Planta: parafina de **CHISA PLUS**, ¿S-25 o S-52? (ficha excluida) | | |

## H. Demo al cliente (opcional — solo si vas a presentar)

Recorrido del guion `doc/entrenamiento_3/GUION_DEMO_CLIENTE.md`: Campana → Inicio (cartera + parcialidades) → Clientes Bajío (Cobros + Seguimiento) → POS (elige cliente y busca `MICRO`, no cobres) → Productos chip MICRO + recetas → Fabricación (pedidos) → OB-00002 pestaña Pagos.

**No hacer en vivo:** cobrar, Completada/pesaje, autorizar preórdenes, timbrar, enviar correo, entrar a Reloj checador ni ajustar stock.

---

## Hoja de resultados

| Bloque | Fecha | Quién | ✅/⚠️/❌/SKIP | Folios TEST creados | Notas |
|--------|-------|-------|---------------|---------------------|-------|
| A Regresión fixes | | | | | |
| B Completada E2E | | | | | |
| C Casos T9 | | | | | |
| D Correos | | | | | |
| E Módulos | | | | | |
| F Catálogo | | | | | |
| G Datos de negocio | | | | | |
| H Demo | | | | | |

## Atajos y trampas

- POS: el buscador solo dispara con **eventos de teclado reales**.
- Detalle de producción: `/produccion/Dashboard/detalle/orden_venta/{id}` (la ruta `…/venta/{id}` da 404).
- `productos` **no** tiene columna `activo`; su estatus es `productos.estatus`.
- Un lote se puede ver en Fabricación → detalle o en `/produccion/Lotes/consultar`.
- Al terminar: documenta folios, limpieza y cualquier 500 con pasos + esperado/real + captura.

*ERP Chisa Recubrimientos — checklist manual generado el 2026-09-17 (base `iteracion-3` / `32782c8`).*
