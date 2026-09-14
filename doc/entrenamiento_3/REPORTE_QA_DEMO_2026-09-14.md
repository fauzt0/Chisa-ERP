# QA demo por sección — 14 sep 2026

Recorrido en navegador (usuario EHWEB) de las pantallas del guion. Bugs de UI/flujo se corrigieron en código; datos sucios se documentan para no “arreglarlos” en producción a ciegas.

Guion: `doc/entrenamiento_3/GUION_DEMO_CLIENTE.md`

---

## 1. Campana + Inicio

**OK**

- Badge 9+; lista prioriza cobros de obra/venta (OB-00001, parcialidad vencida OB-00002 $10,000, saldo OB-00002, OV Bajío).
- Cartera: 4 docs · **$55,130**. Parcialidades: vencida 15/08, $15,000 el 14/09, OB-00001 $2,900 el 17/09.
- Botón **?** de pantalla y de las dos tarjetas de cobro.

**Corregido**

- Texto “notificaciónes” → “notificaciones”.
- Widget “Órdenes en Producción = 0” (contaba solo OV *En Proceso*). Ahora **Pedidos en fabricación** (OV Confirmada/En Preparación/En Proceso + obras Aprobada/En Ejecución). En vivo quedó en **6** (Fabricación lista 8 por incluir más filtros).
- “Cobrar” de una OV iba al listado genérico; ahora `ventas/Ordenes?abrir={id}` y la pantalla abre el detalle.

**No tocar en demo**

- Ventas mes/hoy $0 (no hay POS en septiembre).
- Stock bajo 101: es real; no abrir el acordeón si no vas a hablar de almacén.

---

## 2. Clientes

**OK**

- Bajío (id 2): RFC STB150820GH1, régimen 601, CFDI G03, CP 44130, saldo **$48,170**.
- Cobros: OV-2025-0015, OB-00001, OB-00002.
- Seguimiento: 2 notas de cobranza (demo).

**Corregido**

- Asunto/tipo de seguimiento se escapan en HTML (antes concatenaba “WhatsAppEnvío” en el árbol y era frágil).

**Dato sucio (no código)**

- Estado “Jalisto” en la dirección. No cambiar en demo.

---

## 3. POS

**OK**

- Al elegir Bajío: banner RFC / 601 / G03 / CP / **Saldo por cobrar $48,170**.
- Búsqueda MICRO: ~22 tarjetas, precio $500 visible.

**Corregido**

- Stock **−57** en MICRO se veía roto; ahora muestra “0 (ajuste)” en rojo. El inventario no se alteró.

**No hacer:** Cobrar ni guardar cotización.

---

## 4. Productos y recetas

**OK**

- Chip MICRO filtra a **57 / 494**.
- Buscar recetas MICRO: versiones con cliente/año/comentario (p. ej. CHISA GLASS REF T 423 MICRO, 2026).
- **?** en el título.

**Menor**

- Paginación DataTables a veces en inglés (Next/Last). No bloquea.

---

## 5. Fabricación

**OK**

- Pestaña PEDIDOS **8**, clientes Bajío y Empresa de Prueba, **?** presente.
- No entrar a Lotes.

---

## 6. Obra OB-00002

**OK**

- Hospital Luz, Bajío, total **$41,760**, pagado **$2,000**, saldo **$39,760**.
- Pagos: 3 parcialidades con Quitar (no las quites).

**Corregido**

- Rentabilidad mostraba utilidad = total y **margen 100 %** (costo real $0). Si no hay costo real, ya no se pinta el 100 %.

**Evitar**

- Pestaña Cálculo Materiales si no la vas a explicar (spinner hasta Recalcular).
- Generar OV / registrar pago.

---

## 7. Órdenes, Proveedores, RH, Perfil

| Pantalla | Estado |
| --- | --- |
| Órdenes | 200, cartera en la misma vista, **?**. `?abrir=` abre modal de detalle. |
| Proveedores | 9 activos, 4 OC, **?**. Algunos KPIs en $— si no hay monto; no insistir. |
| RH Empleados | 18, nómina mensual ~$378,075, **?**. No abrir Reloj. |
| Mi Perfil | 200 (sesión EHWEB). |

---

## Lista corta de fixes de este pase

1. Gramática de la campana.  
2. Conteo de pedidos en Inicio.  
3. Link Cobrar OV → detalle.  
4. Stock negativo en POS (solo presentación).  
5. Margen 100 % en obra sin costo real.  
6. Escape HTML en seguimientos CRM.

---

## Orden recomendado (sin cambios)

Campana → Inicio (cartera + parcialidades) → Clientes Bajío (Cobros + Seguimiento) → POS (elige cliente, busca MICRO, no cobres) → Productos chip MICRO + recetas → Fabricación 8 pedidos → OB-00002 pestaña Pagos.
