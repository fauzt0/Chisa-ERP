# Guía de operación — Producción después de importar Excel

Esta guía explica qué hacer después de importar `FICHAS_CHISA_GLASS_2014.xls` y `BASES ORGANICAS Y TINTAS.xls`: buscar productos, activar formulaciones y enlazar **insumos** con **semielaborados fabricados en planta** para que el árbol BOM funcione.

---

## 1. Resumen de lo importado

| Archivo | Qué contiene | Formulaciones aprox. |
|---------|--------------|----------------------|
| `FICHAS_CHISA_GLASS_2014.xls` | Productos terminados CHISA GLASS (por referencia/cliente) | ~275 |
| `BASES ORGANICAS Y TINTAS.xls` | Semielaborados: bases, tintas, soluciones, fase acuosa | ~32 |

**Importante:** las formulaciones importadas se crean como **inactivas**. Debes activar la versión correcta de cada producto que uses en producción.

---

## 2. Buscar productos en el ERP

### Ruta en el sistema
**Producción → Productos → pestaña «Catálogo de Productos»**

### Cómo buscar
1. Usa el campo **«Buscar producto»** (arriba de la tabla).
2. Escribe parte del nombre o código, por ejemplo:
   - `BASE ORGANICA BLANCA`
   - `TINTA NEGRA`
   - `SOLUCION FASE ACUOSA`
   - `CHISA GLASS REF`
3. Opcional: filtra por **Tipo = Fabricado** y **Estatus = Activo**.
4. Pulsa **Limpiar** para quitar filtros.

### Semielaborados prioritarios (bases y tintas)

Actívalos y enlázalos **antes** que los CHISA GLASS, porque las fórmulas de recubrimiento los usan como componentes.

| Código en sistema | Nombre | Prioridad |
|-------------------|--------|-----------|
| `BASE-ORGANICA-BLANCA` | BASE ORGANICA BLANCA | Alta |
| `SOLUCION-RESINA-PLIOWAY-E-CH` | SOLUCION RESINA PLIOWAY E-CH | Alta |
| `SOLUCION-DE-AEROSIL-200` | SOLUCION DE AEROSIL 200 | Alta |
| `SOLUCION-FASE-ACUOSA` | SOLUCION FASE ACUOSA | Alta |
| `TINTA-NEGRA` | TINTA NEGRA | Alta |
| `TINTA-AMARILLO-OXIDO` | TINTA AMARILLO OXIDO | Media |
| `TINTA-ROJA` | TINTA ROJA | Media |
| `TINTA-ROJO-CARMIN` | TINTA ROJO CARMIN | Media |
| `TINTA-VERDE-CROMO` | TINTA VERDE CROMO | Media |
| `BASE-ORGANICA-*` (resto) | Otras bases orgánicas | Según uso |

---

## 3. Activar una formulación

1. En el catálogo, localiza el producto (ej. **BASE ORGANICA BLANCA**).
2. Clic en el botón verde **Formulación** (icono matraz) o en **Historial** (reloj).
3. En el historial verás las versiones importadas (V1, V2…).
4. Abre la versión que corresponda al Excel más reciente o al lote que usan en planta.
5. Clic en **«Establecer como activa»** / **Default**.

Repite para cada semielaborado que fabriquen. Sin formulación activa, el simulador y el árbol BOM no calculan insumos de ese producto.

### Simulador de producción
**Producción → Productos → pestaña «Simulador de Producción»**

1. Selecciona el producto.
2. Elige la versión de formulación (debe estar activa o visible en el listado).
3. Indica cubetas o m² y pulsa **Calcular**.
4. Verás la tabla tipo Excel con % BOM, kg y fase acuosa.

---

## 4. Enlazar insumos con productos fabricados (BOM multinivel)

### ¿Por qué?
En las fórmulas CHISA GLASS aparecen nombres cortos (`BLANCO`, `TINTA NEGRA`, `SOLUCION DE AEROSIL 200`). En el catálogo existen **productos** con formulación propia (semielaborados). Si un **insumo** del catálogo de proveedores representa algo que **ustedes fabrican**, debe marcarse como `fabricado` y enlazarse al **producto** correspondiente. Así el árbol BOM «explota» el semielaborado y muestra las materias primas reales.

### Paso A — Ver candidatos automáticos (MySQL)

```sql
CALL sp_detectar_candidatos_fabricados();
```

Revisa la lista: `insumo_id`, `insumo_nombre`, `producto_id_candidato`, `producto_nombre_candidato`.

### Paso B — Enlaces exactos recomendados (ya detectados en tu BD)

Ejecuta en MySQL/phpMyAdmin:

```sql
-- TINTA NEGRA (insumo) → producto TINTA NEGRA
CALL sp_enlazar_insumo_fabricado(105, 205);

-- SOLUCION DE AEROSIL 200
CALL sp_enlazar_insumo_fabricado(91, 204);

-- TINTA AMARILLO OXIDO
CALL sp_enlazar_insumo_fabricado(96, 206);
```

Script completo opcional: `public_html/database/enlazar_semielaborados.sql`

### Paso C — Enlaces manuales (nombres abreviados en Excel)

En fórmulas CHISA GLASS el insumo **BLANCO** (id 61) suele corresponder a **BASE ORGANICA BLANCA** (producto id 202), aunque el nombre no coincida exactamente:

```sql
CALL sp_enlazar_insumo_fabricado(61, 202);
```

Otros casos frecuentes (revisar con producción antes de enlazar):

| Insumo en fórmula CHISA | Posible producto semielaborado |
|-------------------------|--------------------------------|
| BLANCO | BASE ORGANICA BLANCA |
| ROJO OXIDO | BASE ORGANICA ROJO OXIDO |
| AMARILLO OXIDO / AMA. OXIDO | TINTA AMARILLO OXIDO o BASE ORGANICA AMARILLO OXIDO |
| ROJO CARMIN | TINTA ROJO CARMIN |
| VERDE CROMO | TINTA VERDE CROMO |
| SOLUCION DE RESINA | SOLUCION DE RESINA EC-1 o E-CH (elegir la correcta) |
| PLIOWAY E-CH | SOLUCION RESINA PLIOWAY E-CH |

### Paso D — Verificar enlaces

```sql
SELECT * FROM v_insumos_fabricados;
```

Cada fila debe mostrar insumo ↔ producto ↔ formulación activa del producto.

---

## 5. Probar el árbol BOM

**Producción → Dashboard (pantalla táctil) → pestaña FORMULACIONES**

1. Busca un producto CHISA GLASS con formulación activa.
2. Abre la formulación y el **Árbol BOM**.
3. Indica los kg a producir.
4. Comprueba que los semielaborados enlazados se desglosan en sus materias primas.

Si un componente aparece como hoja (sin expandir) y debería ser fabricado, falta el enlace del paso 4.

---

## 6. Completar datos del catálogo (cuando puedas)

Los productos creados automáticamente tienen código y nombre básicos. Conviene completar:

- Categoría correcta (Recubrimientos, Preparadores, etc.)
- Presentación y contenido neto (kg por lote de referencia del Excel)
- Stock mínimo / máximo
- Precio de venta (si aplica)
- Foto o ficha técnica

Ruta: **Producción → Productos → Editar** (icono lápiz).

---

## 7. Clientes en formulaciones (advertencias de importación)

Mensajes como `Cliente "HOSP. 20 NOVIEMBRE" no encontrado` son normales: la formulación se guardó sin cliente.

- Si es un cliente real: créalo en **Ventas → Clientes** y, si hace falta, edita la formulación para asociarlo.
- Si es una **fecha** mal leída (`21/10/22`, `16/05/2022`): ignórala; no afecta la fórmula.

---

## 8. Checklist rápido

- [ ] Importados ambos Excel (`FICHAS_CHISA_GLASS` + `BASES ORGANICAS`)
- [ ] Activadas formulaciones de semielaborados prioritarios (bases, tintas, soluciones)
- [ ] Ejecutado `sp_detectar_candidatos_fabricados()` y enlaces con `sp_enlazar_insumo_fabricado`
- [ ] Enlace manual BLANCO → BASE ORGANICA BLANCA (si aplica)
- [ ] Probado simulador con un semielaborado y un CHISA GLASS
- [ ] Probado árbol BOM en Dashboard de Producción
- [ ] Activadas formulaciones CHISA GLASS que usen el equipo en planta (gradual)

---

## 9. Soporte técnico / errores frecuentes

| Problema | Solución |
|----------|----------|
| No aparece el producto al buscar | Ctrl+Shift+R; usar campo «Buscar producto»; quitar filtros |
| Tabla Ajax error | Recargar página; revisar que `lista_ajax` responda JSON |
| No puedo escribir en filtros | Cerrar modales; recargar; si persiste: consola F12 → quitar `.modal-backdrop` |
| BOM no expande un componente | Enlazar insumo como `fabricado` al producto correcto |
| Formulación vacía en simulador | Activar una versión en el historial del producto |

---

*Última actualización: junio 2026 — ERP Chisa Recubrimientos*
