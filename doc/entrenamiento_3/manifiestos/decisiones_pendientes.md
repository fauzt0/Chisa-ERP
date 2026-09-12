# Decisiones pendientes — Entrenamiento 3 (Fase 1)

> Generado junto con `productos_match.json`, `insumos_match.json`, `comparativa.json`, `comparativa.md` e
> `incidencias_datos.json` por `doc/entrenamiento_3/tools/fase1_matching.py` (solo lectura: **no se escribió
> nada en la base de datos**).
>
> **¿Retomas esto en otro equipo?** Lee **§F (seguimiento de la sesión del 11-sep-2026)** y copia el bloque
> de **§G (prompt listo para pegar en el agente nuevo)** al final del documento.
>
> Números de la corrida: **64 productos** (17 `usar_existente` / 3 `crear` / 44 `requiere_revision`),
> **58 componentes** (34 `vincular` / 7 `crear` / 17 `requiere_revision`), **22 fichas**
> (8 `coincide_exacto` / 11 `difiere` / 3 `requiere_revision`).

---

## 0. Criterio transversal: variaciones y árbol de formulaciones (aplica a A1–A11)

> El modelo vigente ya distingue **versión** (histórico del producto: `formulaciones.version`, con una
> sola `es_activa` por producto), **variante de cliente** (`cliente_id`, `referencia_cliente` = etiqueta
> del pedido, ej. `1 CUBETA VENTA DAVID`, y `variante_descripcion`, ej. `REF. 128 MICRO`) y **variante
> simultánea** por renglón (`grupo_color`). El historial es filtrable por cliente
> (`ProductosModel::get_historial_formulaciones`) y el BOM multinivel explota semielaborados por el
> enlace insumo → producto.

**Principio**: toda variación de una ficha se registra como **versión nueva (`INSERT`)**; ninguna versión
existente se modifica, desactiva ni borra desde esta carga, y toda variación pedida por un cliente debe
quedar en el árbol para poder consultarla y replicarla después.

| Tipo de variación | Dónde vive | Regla |
|---|---|---|
| Cambio de receta de planta (en el tiempo) | versión nueva `V(n+1)` del producto | `INSERT` con comentario de origen; la activa no se toca (política de activación del prompt, Fase 2 punto 4) |
| Colores/variantes estándar **simultáneas** | misma versión, `grupo_color` por renglón | no crear una versión por color |
| Requerimiento de un **cliente/obra** (aunque sea un cambio leve) | versión nueva + `cliente_id` + `referencia_cliente` (+ `variante_descripcion`) | queda en el árbol, localizable por cliente; no reemplaza la receta estándar activa |

Consecuencias para esta corrida:

- "Desactivar la anterior" solo como acto explícito del negocio; **nunca** cuando la versión nueva es una
  variante de cliente.
- Los nombres de clientes u obras **no** van en `grupo_color`: ese uso ya existe en la BD y es parte de
  las incidencias de §B; no repetirlo en esta carga.
- Los OCR de esta corrida **no traen `cliente`** (todos `null`): hay que decidir qué se captura en
  `referencia_cliente`/`variante_descripcion` (p. ej. `T-034`, `HOJA N°…`) y si alguna ficha es de
  cliente/obra.
- La futura CLI JSON de Fase 2 debe aceptar `cliente`, `referencia_cliente`, `variante_descripcion` y
  grupos (el importador de Excel ya los soporta; el JSON todavía no existe) para no perder la dimensión
  del árbol.

---

## A. Bloqueantes de negocio (hay que decidirlos antes de la Fase 3)

### A1. `entrenamiento20-grupo.jpeg` — CHISA GLASS REF T-034 (producto #225): ¿cómo se modelan los colores?

> **Estado actual verificado en BD** (11-sep-2026, consulta de solo lectura): #225 tiene **una sola
> formulación** (form #323, v1, **activa**, lote 380.35 kg) con **3 grupos** (`COLOR CAFÉ`, `COLOR AZUL`,
> `COLR CREMA`) y sus `porcentaje_fase_acuosa` guardan los % de color (5.2 / 85 / 4.6) en lugar del 45%.
> La referencia T034/T-034 está repartida en **7 productos** del catálogo: #147 (6 versiones; v2 y v4 con
> 4 grupos, incluido `MICRO IS 130`), #225, #311–#314 (`E.S.CH`) y #399.

La ficha trae **4 sub-formulaciones de color** que suman el lote de 19.35 kg
(NEGRO 4.60% = 0.890 kg · BLANCO 85.00% = 16.448 kg · AZUL 5.20% = 1.006 kg · AMARILLO 5.20% = 1.006 kg)
más un bloque base con **FASE ACUOSA 45.00%** y los colorantes (BLANCO 9.384 kg, VERDE 0.475, AMARILLO 0.359,
ROJO 0.236, NEGRO 0.108, AZUL 0.082 kg).

| Opción | Pros | Contras |
|---|---|---|
| (a) 4 formulaciones separadas (una por color, vía `referencia_cliente`) | Cada color tiene su costo y su explosión propios | `referencia_cliente` es la etiqueta del pedido del cliente, no un color; 4 formulaciones a mantener; el color BLANCO es 85% del lote (no es una variante menor); duplica el mantenimiento de la base |
| (b) 1 formulación con `grupo_color` por color | **Es lo que el ERP ya hace**: hay **230 versiones** en el sistema con más de un grupo (p. ej. #147 CHISA GLASS REF T034 v2/v4 con 4 grupos — incluido `MICRO IS 130` —, REF 314 F con 20, REF. 110 con 17); la propia #225 ya trae 3 grupos en su V1, aunque mal armados; conserva un solo documento y un solo costo | La explosión de insumos debe respetar el grupo activo; el % de cada color hay que registrarlo por grupo |
| (c) 1 formulación con 4 versiones | — | El ERP sólo admite **una versión activa** por producto: se perdería la simultaneidad de colores |

**Recomendación de la IA: (b)**, por consistencia con los datos ya cargados (el caso T-034 existe así).
Lo que hay que arreglar sí o sí: la **V1 actual tiene los grupos mezclados** (`COLOR AZUL`, `COLOR CAFÉ`,
`COLR CREMA`) y guardó en `porcentaje_fase_acuosa` los **porcentajes de color** (5.2 / 85 / 4.6) en lugar del 45%.

**Criterio §0 aplicado a este caso**: los 4 colores son variantes **simultáneas** del mismo producto ⇒
grupos dentro de una misma versión (opción b), no versiones separadas. Si un cliente pidiera **su propio
color o su proporción**, eso sí sería una versión nueva con `cliente_id` (no otro `grupo_color`). Las
correcciones (grupos ordenados + `porcentaje_fase_acuosa = 45`) viajan en una **versión nueva**: no se
toca la versión existente (form #323). Dependencia a resolver en la iteración: que el simulador/explosión
BOM respete el `grupo_color` activo.

> **✅ Decisión (11-sep-2026): opción (b).** Una sola formulación con `grupo_color` por color.
> Al ejecutar, se crea una **versión nueva** de #225 con los **4 grupos de la ficha** (NEGRO, BLANCO,
> AZUL, AMARILLO), cada uno con sus renglones completos y su fase acuosa calculada por color (ver A2).
> **No se modifica la V1 existente** (form #323) y la nueva queda inactiva según la política de
> activación del prompt. Pendiente en la iteración: filtro por `grupo_color` en el simulador/explosión BOM.

### A2. `porcentaje_fase_acuosa` / `kg_fase_acuosa`: ¿qué documentos lo traen y cómo se calcula?

- **Sólo `entrenamiento20-grupo.jpeg`** lo trae explícito: `FASE ACUOSA 45.00%`.
- Del OCR se leen los kg por variante: **0.401 / 7.401 / 0.453 / 0.453**, que son exactamente
  **45% del lote de cada variante** (45% × 0.890 = 0.4005; 45% × 16.448 = 7.4016; 45% × 1.006 = 0.4527).
- Convención propuesta: `porcentaje_fase_acuosa = 45.00` y `kg_fase_acuosa = 45% × lote de la variante`
  (no 45% del lote total de 19.35 kg).
- En BD, la V1 de #225 tiene `porcentaje_fase_acuosa` = 5.2 / 85 / 4.6 → **está mal**: son los % de color.
  La corrección se aplica al crear la versión nueva (ver A1); la V1 existente no se modifica.

> **✅ Decisión (11-sep-2026): convención por color.** `porcentaje_fase_acuosa = 45.00` y
> `kg_fase_acuosa = 45% de la porción de cada color` (NEGRO 0.401 · BLANCO 7.401 · AZUL 0.453 ·
> AMARILLO 0.453 kg); no se usa el % del lote total.

### A3. Semielaborados que existen **dos veces**: como insumo y como producto

| Nombre | Producto (Fabricado, con formulación) | Insumo gemelo | Código insumo |
|---|---|---|---|
| SOLUCION DE AEROSIL 200 | **#204** (V1 activa, 213 kg) | #91 | IMP-D0012F55 |
| TINTA NEGRA | **#205** (V1 activa, 50 kg) | #105 | IMP-AF1B06D8 |
| TINTA AMARILLO OXIDO | **#206** (V1 activa, 63 kg) | #96 | IMP-7A375D05 |
| SOLUCION DE RESINA | #214 SOLUCION DE RESINA **EC-1** | #83 | IMP-7FB3924F |
| SOLUCION DE PLIOWAY E-CH | existe **"SOLUCION RESINA PLIOWAY E-CH"** (nombre distinto) | #93 | IMP-C6D4F262 |

- **Las formulaciones ya cargadas usan el insumo**, no el producto: el detector marcó **5 casos de
  auto-referencia** (formulaciones 288/302/314 de SOLUCION DE AEROSIL 200 y 290/304 de TINTA AMARILLO OXIDO).
- El prompt de la Fase 1 (§ reglas de insumos, punto b) pide proponer **`tipo = "Producto"`** cuando el nombre
  corresponde a algo que la planta fabrica; **los 140 insumos del catálogo son `tipo = 'comprado'`** y
  **ningún insumo tiene `producto_id`**, así que la vista `v_insumos_fabricados` está **vacía**.
- **Decisión: ¿se cargan como `Producto` (semielaborado con explosión) o como `Insumo` (como la V1)?**
  El manifiesto propone `Producto` para SOLUCION DE AEROSIL 200, TINTA NEGRA, TINTA AMARILLO OXIDO y
  FASE ACUOSA, y `Insumo` para SOLUCION DE RESINA / PLIOWAY (no hay producto equivalente), anotando la dualidad.
  Si se elige `Insumo`, las versiones nuevas quedarán idénticas a la V1 en `tipo_componente`.

> **✅ Decisión (11-sep-2026): opción (A).** Las versiones nuevas usan `tipo_componente='Insumo'` (igual
> que la V1) y se **enlazan** los insumos gemelos a sus productos fabricados (UPDATE puntual:
> `tipo='fabricado'` + `producto_id`, idealmente con `sp_enlazar_insumo_fabricado`): 91→204, 96→206,
> 105→205 y 83→214 (validar que "SOLUCION DE RESINA" ≡ "EC-1"); PLIOWAY #93 pendiente de validar
> equivalencia. FASE ACUOSA: crear insumo `FASE ACUOSA` y enlazarlo a #215. Así el árbol BOM explota los
> semielaborados. Las 5 auto-referencias existentes quedan como incidencia aparte (el anti-ciclo del
> árbol ya las protege).

### A4. La línea **"AGUA"** de la ficha está ligada en BD a **"Resina Base Agua SBR/Látex" (#7)**

En **8 formulaciones ya cargadas** el renglón que la ficha imprime como `AGUA` apunta al insumo #7:

| Producto | Form. | % de la línea | Ficha dice | BD tiene |
|---|---|---|---|---|
| #215 SOLUCION FASE ACUOSA | 299 (V1 activa) | 98.34 | AGUA | Resina Base Agua SBR/Látex |
| #202 BASE ORGANICA BLANCA | 286 / 300 | 0.18 | AGUA | Resina Base Agua SBR/Látex |
| #210 / #211 / #212 / #213 | 294/308, 295/309, 296/311, 297 | 0.14–0.19 | AGUA | Resina Base Agua SBR/Látex |
| #216, #217 | 310, 312 | 0.14–0.18 | AGUA | Resina Base Agua SBR/Látex |
| #204 SOLUCION DE AEROSIL 200 | 316 (V4) | 98.34 | AGUA | Resina Base Agua SBR/Látex |

Los fichas dicen literalmente `AGUA` (el OCR lo confirma: `98.34%AGUA`, `0.18%AGUA`).
**No se fusionó**: el manifiesto lo marca `nuevo` en la ficha / `falta_en_ocr` en BD con la nota
*"confirmar si es el mismo material"*. Hay que decidir si la V nueva usa `AGUA` (#126, código `INS-AGUA-001`)
o el insumo #7; cualquiera de las dos opciones **cambia la receta respecto de la V1**.

> **✅ Decisión (11-sep-2026): usar `AGUA` #126 (`INS-AGUA-001`).** En las versiones nuevas, la línea
> "AGUA" se carga contra #126 (ya usado en PASTA SERGIO, ARENA SILICA, PINTU FLEX y PINTURA VINILICA);
> la V1 conserva su liga histórica a #7. La diferencia respecto de la V1 es intencional y queda
> registrada como versión nueva (aplica a los casos 13/16/18 de A10).

### A5. Componentes que la ficha trae y el catálogo no tiene (`crear`, 7)

| Componente (OCR) | Nombre canónico propuesto | Aparece en |
|---|---|---|
| `BERMOCOLL481FQ` / `BERMOCOL481FQ` | BERMOCOLL 481 FQ | entrenamiento2, entrenamiento21 |
| `EDENOL DOA` | EDENOL DOA | entrenamiento22 (CHISA PLUS) |
| `RESINA QE-220S` y `RESINA QE 220 S(W-394)` | RESINA QE-220S | entrenamiento1 (VITROGLASS ECO), 21, 22 |
| `RESINA QE-2383` y `RESINA QE2383(W-595)` | RESINA QE-2383 W-595 | entrenamiento1, 5, 21, 22 |

El catálogo tiene **RESINA D-06 (#129), D-25 (#130), D-50 (#131)** y resinas genéricas (#2 acrílica, #3 alquídica,
#4 epóxi, #5 PU, #7 base agua SBR), pero ninguna `QE-…`; el fuzzy más cercano queda en 0.64–0.67,
así que **no son equivalentes**: hay que crear el insumo o confirmar el mapeo con el cliente.
`RESINA` a secas (entrenamiento2, 23.22%) quedó `requiere_revision` por ambigüedad con D-06/D-25/D-50.

> **✅ Decisión (11-sep-2026):** crear los **4 insumos nuevos** con los nombres canónicos propuestos
> (`BERMOCOLL 481 FQ`, `EDENOL DOA`, `RESINA QE-220S`, `RESINA QE-2383 W-595`). El componente
> `RESINA` (23.22%) de entrenamiento2 se mapea a **#128 `RESINA W4535`** (`INS-RES-W4535`), porque la
> ficha trae el código `W-4535V` junto a ese renglón.

### A6. Componentes con match parcial (`requiere_revision`: 8 nombres + 9 colores)

| Componente (OCR) | Candidato propuesto | Por qué dudoso |
|---|---|---|
| `BIOXIDO DE TITANEO`, `BIOXIDODETITANEO R-902` | #15 Dióxido de Titanio R-902 (TiO2) | el catálogo tiene además una entrada con el nombre viejo; confirmar que son el mismo material |
| `CAOLIN` | #136 CAOLIN M-325 | coincidencia por contención |
| `PARAFINA CLORADA S-25` | #95 "PARA FINA CLORADA" | el nombre del catálogo está corrupto (falta "PARAFINA") → **error de captura en BD** |
| `SOLUCION DE AEROSIL` | #91 SOLUCION DE AEROSIL 200 | falta el "200"; ver A3 |
| `FASE ACUOSA` | #215 SOLUCION FASE ACUOSA (Producto) | contención; ¿es el mismo semielaborado? |
| `BLANCOFIJO MICRO` | #61 BLANCO | el insumo elegido es autogenerado (`IMP-69A34712`); falta BLANCO FIJO MICRO |
| `BLANCO`, `NEGRO`, `NEGRO OXIDO`, `ROJO`, `ROJO OXIDO`, `VERDE`, `VERDE CROMO`, `AZUL`, `AMARILLO` | — | **nombres de color**: hay que decidir a qué pigmento/tinta corresponde cada uno (el manifiesto lista hasta 5 candidatos por color) |

> **⚠️ Respuestas del formulario perdidas — A6 pendiente de re-capturar (11-sep-2026).** La decisión se
> preguntó en dos formularios (colorantes T-034 / parafina / blanco fijo; después bioxi / blanco fijo /
> otros 4 / colores) y en ambos la interfaz reportó *"Questionnaire was accepted but no result was
> available"*: se contestaron, pero **las respuestas nunca llegaron al agente** (verificado en el transcript
> de la sesión, en la BD del proxy y en los logs de Cursor). No hay nada que anotar de A6: hay que
> re-capturarlo. Para retomarlo, basta responder estos 5 puntos:
>
> 1. `BIOXIDO DE TITANEO` / `BIOXIDODETITANEO R-902`: usar **#15 `PIG-001`** Dióxido de Titanio R-902
>    (canónico, con precio y stock) *(recomendado)* o **#86 `BIOXIDO DE TITANEO`** (como las 5 versiones actuales).
> 2. `BLANCOFIJO MICRO` (ficha CHISA PLUS): **crear insumo `BLANCO FIJO MICRO`** *(recomendado)* o mapear a
>    **#61 `BLANCO`** (genérico).
> 3. `PARAFINA CLORADA S-25` (CHISA PLUS): confirmar con planta/proveedor si es S-25 o S-52; en el catálogo
>    el único candidato es **#95** `PARA FINA CLORADA` (nombre corrupto → corregir a `PARAFINA CLORADA …`).
> 4. Confirmar los otros mapeos: `CAOLIN`→**#136**, `SOLUCION DE AEROSIL`→**#91** (consistente con A3) y
>    `FASE ACUOSA` (según A3).
> 5. Confirmar los 9 colores: T-034 → `BLANCO` **#61** · `NEGRO` **#18** · `ROJO` **#16** · `AMARILLO` **#17** ·
>    `AZUL` **#19** · `VERDE` **#20** (igual que la V1); tintas → `ROJO OXIDO` **#77** · `NEGRO OXIDO` **#94** ·
>    `VERDE CROMO` **#101**.

### A7. Productos sin match

- **De ficha (3) → `crear`**: `VITROGLASS ECOLOGICO` (19 kg), `SELLADOR INICIAL` (19 kg), `CHISA PLUS` (27 kg).
  No existe nada parecido en el catálogo de productos ni de insumos.
- **`PRAIMER GLASS ~CHISA VINIL PINTURA VINILICA` (entrenamiento2, 27 kg)**: hoy queda ligado por contención a
  **#224 PINTURA VINILICA** (25 kg). El nombre del bloque derecho trae **tres** textos, y la lista de precios
  vende PRAIMER GLASS y CHISA VINIL por separado. **¿La ficha 2 es PRAIMER GLASS, CHISA VINIL o una hoja combinada?**
  Si son dos productos, hay que partir la ficha en dos formulaciones.
- **Renglones que no parecen productos del catálogo** (vienen de `rendimientos.jpeg` y de la lista de precios;
  quedaron `requiere_revision` para que decidas si son producto, insumo o simple renglón informativo):
  `RODILLO`, `BROCHA`, `LIJAS MEDIANAS`, `IMASKING`, `PAPEL` (consumibles), `MORTERO PLASTICO`,
  `MARMOPLAST`, `MARMOFLEX`, `CASCARA DENARANJA`, `PASTA PARA TEXTURIZAR INT./EXT.`,
  `PRIMERP/IMPERMEABILIZAR`, `PINTURA ANTIBACTERIAL`, `SELLADORI.(PRAIMER)`, `SELLADORF.(VITRO GLASSECO)`,
  `CHISA GLASS` (genérico).

### A8. Lista de precios 2025: 45 filas → 29 productos únicos, **28 sin match**

Preparada ya por producto (con sus presentaciones y precios) en el campo `presentaciones` de
`productos_match.json` (se quitó la presentación pegada al nombre: `PINTUFLEXCUBETA` → PINTU FLEX + CUBETA).
Los 28 nombres sin match en catálogo son, agrupados:

- **Selladores/pastas**: SELLADOR ACRILICO 4648 (galón/cubeta), DECOPASTA, CHISA PLAST, CHISA PLAST PLUS, MARMOPLAST.
- **Pinturas**: PRAIMER GLASS, CHISA VINIL, CHISA MAR.
- **Preparadores y morteros**: REDY PLAST F/M/G, MORTERO PLASTICO MALLA 350-400, ARENA SILICA MALLA 250-300
  (esta sí matchea #222 por contención).
- **Chisa Glass**: CHISA GLASS TEXTURADO (#404 por contención), TRICOLOR PLUS, CORTPLAST.
- **Polycolor / mármol**: POLY-COLOR (ESCAMA), POLY-PLAST, POLYTANO 9000 (RESINA EPOXICA), MARMOFLEX REF. BLANCO, MARMOFLEX REF. COLOR.
- **Impermeabilizantes**: IMPERGLASS 3 / 5 / 7 AÑOS, SHELL HARD (CÁSCARA DE NARANJA), SHELL HARD LISO.
- **Otros**: HEALER GLASS KIT 1 LT C/ESPÁTULA, PROTECT PLUS.

Preguntas a resolver: (1) ¿se crean como productos nuevos o se mapean a los existentes?
(2) ¿el `precio_sin_iva` de la lista se guarda tal cual en `productos.precio_venta` (la lista dice "sin IVA")?
(3) el `rendimiento_teorico` viene como rango de texto (`18-20m2`, `250gr./1m2`) y `productos.rendimiento`
es un campo corto numérico (`12m` en #3) → ¿se guarda el punto medio, el mínimo o no se toca?

### A9. `rendimiento_m2_por_kg` (§ decisión 3 del handoff)

Sólo se puede derivar: `m2 del envase ÷ contenido_neto`. Ejemplo: CHISA GLASS MICRO (#3) = 65-70 m² por cubeta
de 19 L ⇒ ~3.5 m²/L. ¿Se calcula así en la Fase 3 o se captura a mano?

### A10. Formulaciones que ya existen y **difieren** (11 fichas)

`difiere` significa: la ficha no es idéntica a ninguna versión existente. Con el criterio de §0 la carga
siempre es **versión nueva (`INSERT`)**, nunca `UPDATE`; la pregunta ya no es "¿activa o inactiva?" sino
**de qué tipo es la diferencia**, porque eso define dónde queda en el árbol:

1. **Receta de planta actualizada** → versión nueva del producto; activación según la política del prompt
   (Fase 2 punto 4: si ya había una activa no se toca; la nueva queda inactiva y se reporta).
2. **Variante de un cliente/obra** (aun si el cambio es leve) → versión nueva con `cliente_id` +
   `referencia_cliente` (+ `variante_descripcion`), inactiva, localizable por el historial filtrado por cliente.
3. **Variante estándar simultánea** (color) → no es versión nueva: va como `grupo_color` en la misma versión (ver A1).

En los tres casos **se conservan todas las versiones previas**; "desactivar la anterior" solo como acto
explícito del negocio y nunca cuando la nueva es una variante de cliente.

Casos con diferencias **reales** (no de lote): entrenamiento20-grupo (16 de 16 líneas), entrenamiento21 PINTU FLEX (7 de 15),
entrenamiento2 PRAIMER (4 de 13), entrenamiento13/16/18 (la línea AGUA de A4), entrenamiento15 (SKEN M-8 0.35→0.42 y
ADECIDE 0.40→0.42), entrenamiento12.

Casos donde **sólo cambia el tamaño de lote** (misma receta, `coincide_receta_difiere_lote`): ninguno en esta corrida
(antes salían 4 por comparar contra la versión equivocada; ahora se comparan **todas** las versiones).

### A11. Capturas duplicadas

| Ficha | Situación |
|---|---|
| `entrenamiento3.jpeg` = `entrenamiento17.jpeg` | **idénticas** (SOLUCION DE AEROSIL 200, 213 kg): cargar una sola vez |
| `entrenamiento16.jpeg` = `entrenamiento18.jpeg` | **idénticas** (BASE ORGANICA BLANCA, 1000 kg) |
| `entrenamiento11.jpeg` vs `entrenamiento15.jpeg` | mismo producto y mismo lote (SOLUCION FASE ACUOSA, 600 kg) pero **porcentajes distintos** (SKEN M-8 0.35 vs 0.42; ADECIDE 0.40 vs 0.42) → **decidir cuál es la más reciente** (no cuál sobrevive) |

**Criterio §0 aplicado a este caso**: una captura con porcentajes distintos **no se descarta**. Si al
re-verificar las imágenes los % realmente difieren, se cargan como dos versiones consecutivas (con la
imagen de origen en `nombre_version`/`comentarios`), y la decisión es cuál es la más reciente/candidata a
activar. Para las idénticas (`3=17`, `16=18`) basta una sola carga, dejando ambas imágenes registradas en
`log_importaciones`/`comentarios`.

---

## B. Incidencias de calidad de datos **ya presentes en la BD** (detalle en `incidencias_datos.json`)

- **413 versiones** cuya suma de `porcentaje` se desvía más de 2 puntos de 100% (sin contar grupos de color).
- **230 versiones** con más de un `grupo_color` en la misma versión (varios con hasta 13 grupos, y grupos que en
  realidad son **clientes/obras**: "HOSPITAL BELISARIO DOMINGUEZ", "CUAUTLA MORELOS", "ATLACOMULCO").
- **12 claves de insumo duplicadas** (mismos nombres con y sin espacios/guiones). Ejemplos:
  `PARAFINA CLORADA (S-52)` #88 vs `PARAFINA CLORADA S-52` #97; `PLIOWAY E-CH` #92/#117; `PLIOWAY EC-1` #99/#107;
  `AEROSIL 200` #118 vs `SOLUCION DE AEROSIL 200` #91; `TILOSE` #138 vs `NATROSOL TILOSE` #110.
  Los manifiestos **siempre eligen el código canónico** (nunca `IMP-%`) y anotan el gemelo en `notas`.
- **5 casos** en los que un insumo tiene el mismo nombre que el producto que lo contiene (auto-referencia, ver A3).
- Insumo con nombre corrupto: #95 `PARA FINA CLORADA` (debería ser PARAFINA CLORADA ...).

---

## C. Dudas de OCR que afectan números (por si se quiere re-verificar en la imagen)

| Ficha | Confianza | Duda |
|---|---|---|
| entrenamiento20-grupo | **0.55** | 4 sub-recetas de color y colorantes repartidos; la más frágil de todas |
| entrenamiento12 BASE ORGANICA NARANJA | **0.60** | lote **1.000 kg** (¿real o la hoja es "por kilo"?); la fila de cantidades repite `0.164` en las columnas de AGUA y TINTA AZUL ⇒ esas dos cantidades quedaron en `null`; en la cabecera aparece `AZULYROJO CARMIN` |
| entrenamiento5 SELLADOR INICIAL | 0.70 | el precio de AGUA se reconstruyó por aritmética ($0.06) |
| entrenamiento4 y entrenamiento6 | 0.75 | importes degradados, resueltos por aritmética; en 4 hay una línea `Y AMARILLO CANARIO` suelta |
| entrenamiento2 | 0.80 | `W-4535V` y `M-325` parecen códigos de RESINA y CAOLIN; el nombre del producto mezcla 3 textos (A7) |
| entrenamiento21 PINTU FLEX | 0.85 | los % de TEXANOL y MONOETILENGLICOL venían recortados ⇒ la suma de % da **96.5** (las cantidades en kg sí cuadran con el lote de 27 kg) |
| entrenamiento3 / 17 | 0.80 / 0.88 | las cifras monetarias del bloque izquierdo están en miles |
| entrenamiento6 / 7 | 0.75 | hay un `precio_venta` 394.97 sin unidad indicada |

Nada de esto cambia los productos/insumos del manifiesto; sí puede cambiar algún **kg** de las formulaciones.

---

## D. Lo que ya está resuelto y **no** hay que decidir

- **8 fichas ya cargadas idénticas** (`coincide_exacto`, V1 activa) ⇒ no crear nada:
  TINTA AMARILLO OXIDO (63 kg), TINTA ROJO CARMIN (200), TINTA ROJA (60), TINTA NEGRA (50), TINTA VERDE CROMO (52),
  SOLUCION DE AEROSIL 200 (213, fichas 3 y 17), SOLUCION DE RESINA EC-1 (393.87).
- **15 productos de ficha** resueltos por nombre/código exacto contra productos **Activos/Fabricados**.
- `ARENA SILICA` (#222) y `CHISA GLASS MICRO` (#3) existen y están activos.
- Los 34 componentes con match exacto (`vincular`) no necesitan intervención, salvo el criterio de A3.

---

## E. Siguiente paso

Con estas respuestas se puede pasar a la **Fase 2** (`importar_formulaciones_json_cli` + `--dry-run`) usando
exclusivamente lo aprobado en los manifiestos: sólo se cargan las entradas `usar_existente` / `vincular`,
y nada de lo marcado `crear` o `requiere_revision` hasta que se resuelva aquí.

---

## F. Seguimiento de la sesión del 11-sep-2026

**Hecho (todo fue solo lectura contra la BD de producción):**

- Fase 1 completa: `productos_match.json` (64 productos: 17 `usar_existente` / 3 `crear` / 44 `requiere_revision`),
  `insumos_match.json` (58 componentes: 34 `vincular` / 7 `crear` / 17 `requiere_revision`),
  `comparativa.json` + `comparativa.md` (22 fichas: 8 `coincide_exacto` / 11 `difiere` / 3 `requiere_revision`)
  e `incidencias_datos.json`.
- Herramientas versionadas: `fase1_matching.py`, `volcar_datos_f1.php` y `consulta_db.php`
  (a esta última se le corrigieron la ruta al `config` y las columnas de `insumos`).
- **Decisiones A1–A5 tomadas y anotadas** en este documento (✅ 11-sep-2026): A1 → una sola formulación con
  `grupo_color` por color; A2 → fase acuosa por color; A3 → enlazar insumos gemelos a sus productos;
  A4 → usar `AGUA` #126; A5 → crear los 4 insumos nuevos y `RESINA` → #128.
- Verificaciones puntuales contra producción (solo `SELECT`) para sustentar cada decisión.

**Pendiente / bloqueado:**

- **A6:** las respuestas del formulario se perdieron (ver nota ⚠️ en §A6) → hay que re-capturarlas.
- **A7–A11:** requieren decisión de negocio; se revisan una por una igual que A1–A5.
- **Hallazgo técnico a corregir antes de la Fase 2:** `fase1_matching.py` evalúa la bandera `es_activa`
  (que el volcado entrega como string `'0'`/`'1'`) con `if f['es_activa']` a secas; como `'0'` es *truthy*
  en Python, `version_activa` y las etiquetas "(activa)" quedan mal siempre que la versión activa no sea la
  primera de la lista. Se corrige comparando contra `'1'` (y usando la versión que realmente coincidió en el
  texto) y se **re-ejecuta** el matching para que `productos_match.json` y `comparativa.*` queden confiables.
- **Fase 2 y Fase 3 sin ejecutar:** los prompts están en `doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md` §4–5.

**Estado del repo:** todo quedó commiteado y pusheado en la rama `iteracion-3` el 11-sep-2026 (ver `git log`).
Desde el otro equipo: `git pull origin iteracion-3`. **Nada se escribió en la base de datos.**

---

## G. Prompt para retomar (pegar tal cual en el agente del otro equipo)

```text
Trabajas en el ERP de CHISA Recubrimientos (CodeIgniter 3 + MySQL/MariaDB, PHP 7.4).
Rama de trabajo: `iteracion-3` (NUNCA `main`). Antes de empezar: `git pull origin iteracion-3`.

MISIÓN
Continuar la digitalización "Entrenamiento 3" del módulo de Producción (formulaciones).
La Fase 1 ya está hecha (solo lectura) y sus decisiones se registran en:
  doc/entrenamiento_3/manifiestos/decisiones_pendientes.md   <- LEE ESTE DOCUMENTO COMPLETO PRIMERO.
Documentos de apoyo:
  doc/HANDOFF_ENTRENAMIENTO_3.md               (estado general y reglas no negociables, §6)
  doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md     (§4 y §5: prompts de Fase 2 y Fase 3)

TRABAJO INMEDIATO (en este orden)
1) Re-capturar la decisión A6 conmigo (el usuario): las respuestas del formulario anterior se perdieron.
   Hazme las preguntas de la nota "⚠️ Respuestas del formulario perdidas" en §A6 y anótalas en el documento
   (con ✅ y fecha). Después seguimos con A7–A11 en orden, una por una: me explicas la decisión, yo respondo
   y tú la anotas en `decisiones_pendientes.md`.
2) Corregir el hallazgo técnico de `doc/entrenamiento_3/tools/fase1_matching.py` (contraste de `es_activa`
   contra '1' y uso de la versión realmente coincidente en el texto) y re-ejecutar el matching.
3) Con A1–A11 resueltas: implementar el método CLI `importar_formulaciones_json_cli` en
   `application/controllers/produccion/Productos.php` y correr `--dry-run` (Fase 2). Muéstrame el dry-run
   literal ANTES de escribir nada en la BD.
4) Solo con mi OK explícito: Fase 3 (carga real como versiones nuevas + precios 2025 + rendimientos) y commit.

REGLAS NO NEGOCIABLES (resumen de doc/HANDOFF_ENTRENAMIENTO_3.md §6)
- No modificar, desactivar ni borrar formulaciones/versiones/componentes existentes: todo cambio es
  VERSIÓN NUEVA (INSERT). "Desactivar la anterior" solo si el negocio lo pide explícitamente.
- Prohibido DROP / TRUNCATE / DELETE y UPDATE masivos; solo INSERT y UPDATE puntual con WHERE id = ...
- Nada marcado `crear` o `requiere_revision` se carga hasta que se resuelva en `decisiones_pendientes.md`.
- Respetar doc/REGLAS_TECNICAS.md (CI3, MVC estricto, Active Record, sin SQL crudo en controlador/vista,
  sin var_dump ni debug).
- Entregar siempre: tabla archivo -> cambio, comando ejecutado y salida literal relevante.
  No commitear sin validación del usuario.
- Ir anotando cada decisión en `decisiones_pendientes.md` (sección correspondiente, con ✅ y fecha).

CONSULTA RÁPIDA A LA BD (solo lectura)
  php doc/entrenamiento_3/tools/consulta_db.php resumen
  php doc/entrenamiento_3/tools/consulta_db.php find|insumos|activas|form "texto"

IMPORTANTE
- La BD es la de producción: cualquier escritura es con extremo cuidado y solo en Fase 3 y con mi OK.
- Los insumos se eligen siempre con código canónico (nunca códigos `IMP-`), salvo decisión expresa.
- El objetivo del árbol es conservar histórico: las fichas de cliente/obra van como versión nueva con
  `cliente_id` + `referencia_cliente` (+ `variante_descripcion`).
```

*ERP Chisa Recubrimientos — sesión de revisión de decisiones Fase 1 (11-sep-2026).*
