# Guion de demo — ERP Chisa Recubrimientos

Fecha: 14 de septiembre de 2026  
Usuario sugerido: **EHWEB**  
URL: https://erp.chisarecubrimientos.com.mx/dashboard

En cada pantalla hay un botón **?** (barra superior y junto al título). Úsalo para explicar funciones sin improvisar.

---

## Qué no hacer en vivo

- No registrar un cobro real ni cancelar documentos.
- No pasar producción a **Completada** ni hacer pesaje.
- No autorizar preórdenes de compra.
- No timbrar CFDI ni enviar correo.
- No entrar a **Control de Lotes** (inventario de lotes vacío).
- No mostrar **Reloj checador**.

---

## 1. Campana de alertas (30 s)

Arriba a la derecha, icono de campana.

Debe verse, entre otras:

| Tipo | Ejemplo para decir |
| --- | --- |
| Parcialidad vencida | OB-00002 · Bajío · $10,000 el 15/08/2026 |
| Cobro pendiente | OV-2025-0015 · Bajío · $2,610 |
| Cobro pendiente | OV-2026-0004 · Empresa de Prueba · $6,960 |
| Producción | Solicitudes pendientes (p. ej. SP-2026-0003) |
| Compras | Preórdenes por autorizar |

Mensaje: *el sistema avisa solo; no hay que entrar a cada módulo a cazar vencimientos*.

---

## 2. Inicio ERP

Tarjetas clave (baja hasta ellas si hace falta):

**Clientes con falta de pago**

| Cliente | Documento | Saldo aprox. |
| --- | --- | --- |
| Soluciones Tecnológicas del Bajío | OB-00002 | $39,760 |
| Soluciones Tecnológicas del Bajío | OB-00001 | $5,800 |
| Soluciones Tecnológicas del Bajío | OV-2025-0015 | $2,610 |
| Empresa de Prueba | OV-2026-0004 | $6,960 |

Saldo agrupado Bajío ≈ **$48,170**. Prueba ≈ **$6,960**.

**Parcialidades de obra**

| Obra | Fecha | Monto | Estado |
| --- | --- | --- | --- |
| OB-00002 | 15/08/2026 | $10,000 | Vencida |
| OB-00002 | 14/09/2026 | $15,000 | Hoy / próxima |
| OB-00002 | octubre | $14,760 | Programada |
| OB-00001 | 50% + 50% | según calendario | Programada |

Nota: “Ventas del mes / hoy” pueden mostrar **$0** si no hay órdenes de venta en septiembre. No es un error del tablero; el dinero de la demo está en **obras y cartera**.

---

## 3. CRM · Clientes

Ruta: CRM Ventas → Clientes.  
Abre **Soluciones Tecnológicas del Bajío** (RFC Bajío STB150820GH1).

Mostrar:

- Datos fiscales y saldo.
- Pestaña **Cobros**: los 3 documentos del Bajío.
- Pestaña **Seguimiento**: bitácora comercial (hay registros de demo).

---

## 4. POS

Ruta: CRM Ventas → Punto de Venta.  
Selecciona el mismo cliente Bajío.

Debe aparecer un recuadro con RFC, régimen, uso de CFDI, CP y saldo.  
Busca un producto (p. ej. MICRO) para mostrar el buscador. **No confirmes la venta** si no quieres un documento nuevo.

---

## 5. Productos y recetas

Ruta: Producción → Productos y Fórmulas.

- Chip **MICRO** → catálogo filtrado (decenas de SKU, no los 490).
- Panel **recetas**: búsqueda por cliente/año; MICRO debe devolver formulaciones (≈ 40).
- Explica: receta en **Kg** se escala por kilos del lote, no “cantidad × 19”.

---

## 6. Fabricación

Ruta: Producción → Fabricación Dashboard.

- Pedidos / solicitudes en atención (varias, p. ej. 8 en el tablero interno).
- Relación obra → producción.
- Cierra aquí; no entres a Lotes.

---

## 7. Obra con cobro en parcialidades

Ruta: Obras → **OB-00002 Hospital Luz**.

- Cliente Bajío, total **$41,760**, saldo **$39,760** (ya hay un abono REC-00001 de $2,000).
- Pestaña **Pagos**: calendario de 3 fechas + opción de quitar/agregar (no borres en demo).
- Relato: *se cobra en exhibiciones con fecha; lo vencido pinta en rojo y sale en la campana*.

Opcional 20 s: **OB-00001** (EHWEB / Bajío, $5,800, estatus Aprobada) para mostrar otra obra con precio.

---

## 8. Cierre rápido (si hay tiempo)

| Módulo | Qué decir | Qué no hacer |
| --- | --- | --- |
| Proveedores | 9 activos, catálogo e historial de compra | No editar |
| RH Empleados | 18 empleados, nómina con flujo borrador→cálculo→pago | No pagar nómina |
| Mi Perfil | Usuario, tema claro/oscuro, tamaño de letra | — |
| Almacén | Stock bajo en Inicio (101 insumos) | No ajustar existencias |

---

## Frases útiles

- *Todo lo que el cliente debe está con nombre, RFC y folio; no es un número suelto.*
- *La obra se cobra a plazos y el sistema avisa antes y cuando se vence.*
- *Producción usa la receta real; no inventa rendimientos.*
- *La campana junta cobro, fábrica y compras para no perseguir cada lista.*

---

## Permisos / personalización

El tablero solo muestra módulos del rol. Si falta una tarjeta: **Personalizar** (esquina superior) y actívala en este equipo.
