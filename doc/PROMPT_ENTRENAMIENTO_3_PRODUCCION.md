# PROMPT — Entrenamiento 3: carga de formulaciones reales del cliente (Producción)

**Objetivo:** digitalizar y cargar en el ERP las formulaciones que el cliente usa hoy, a partir de 25 capturas de pantalla de su sistema anterior, dejando trazabilidad y sin romper el histórico existente.

**Modo de trabajo:** un agente *arquitecto/orquestador* (este chat) audita y valida; el trabajo de código y ejecución lo hace **Composer 2.5** en un chat nuevo, fase por fase. **No se avanza a la fase siguiente sin el visto bueno del orquestador.**

---

## 1. Insumos de datos ya preparados (NO volver a generarlos)

| Ruta | Contenido |
|------|-----------|
| `doc/entrenamiento_3/imagenes/` | 25 capturas originales (`entrenamiento1..22.jpeg`, `entrenamiento20-grupo.jpeg`, `lista-precios-1.jpeg`, `lista-precios2.jpeg`, `rendimientos.jpeg`) |
| `doc/entrenamiento_3/ocr/` | OCR crudo local (RapidOCR): 25 `.txt` (líneas reconstruidas, `y=<coord> :: [x]texto \| [x]texto`) + 25 `.tsv` (`y \t x \t texto`) |
| `doc/entrenamiento_3/ocr/parsed/` | **Estructura final en JSON** — 6 archivos: `batch_1-5.json`, `batch_6-10.json`, `batch_11-15.json`, `batch_16-20.json`, `batch_21-22-rendimientos.json`, `lista_precios.json` |
| `doc/entrenamiento_3/tools/consulta_db.php` | Utilidad CLI de consulta a BD: `php doc/entrenamiento_3/tools/consulta_db.php find\|insumos\|form\|activas\|resumen "texto"` |
| `doc/entrenamiento_3/tools/ocr_rapid_v2.py` | Script de OCR (solo si hace falta re-procesar una imagen) |

**Entorno OCR (si se requiere):** `PYTHONPATH=/home/admin/domains/erp.chisarecubrimientos.com.mx/.ocr-tmp python3 doc/entrenamiento_3/tools/ocr_rapid_v2.py <carpeta_salida> <imagen> [...]`

### 1.1 Esquema del JSON de formulación

```json
{
  "imagen": "entrenamiento1.jpeg",
  "tipo_documento": "formulacion | formulacion_grupo",
  "producto": { "nombre": "VITROGLASS ECOLOGICO", "codigo_o_referencia": null, "hoja_o_clave": null, "cliente": null },
  "lote": { "cantidad": 19.0, "unidad": "Kg", "presentacion": "Cubeta" },
  "componentes": [
    { "nombre": "RESINA QE2383(W-595)", "porcentaje": 25.0, "cantidad": 4.75, "unidad": "Kg", "precio_unitario": null, "subtipo": null }
  ],
  "variantes": [ { "color": "BLANCO", "porcentaje_color": 85.0, "cantidad_color": 16.448 } ],
  "totales": { "porcentaje": 100.0, "cantidad": 19.0, "costo_total": null, "precio_venta": null },
  "dudas": ["..."],
  "confianza": 0.95
}
```

- `cantidad` es **kg del lote** (no por cubeta); `porcentaje` viene tal cual del documento.
- Solo `entrenamiento20-grupo.jpeg` trae `variantes` (4 sub-formulaciones de color que suman el lote) — **requiere decisión del negocio**, ver §6.
- `rendimientos.jpeg` y `lista-precios*.jpeg` traen `filas` con esquemas propios (producto/presentación/`m2` o `precio_sin_iva`).

### 1.2 Esquema de BD relevante

- `productos(id, codigo, nombre, alias, tipo_producto ENUM('Fabricado','Reventa'), categoria_id, unidad_venta, presentacion_principal, contenido_neto, codigo_barras, precio_venta, rendimiento VARCHAR(100), estatus)`
- `formulaciones(id, producto_id, version, nombre_version, cantidad_producida, unidad_produccion, costo_total_insumos, costo_total, es_activa, cliente_id, referencia_cliente, comentarios, rendimiento_m2_por_kg, cantidad_cubetas_ref, fecha_creacion)`
- `detalle_formulacion(id, formulacion_id, tipo_componente ENUM('Insumo','Producto'), insumo_id, producto_id, cantidad, unidad, porcentaje, costo_unitario, grupo_color, porcentaje_fase_acuosa, kg_fase_acuosa, orden)`
- `insumos(id, codigo, nombre_tecnico, tipo ENUM('comprado','fabricado','semielaborado'), producto_id, unidad_medida, precio_promedio, estatus)`
- `log_importaciones` — bitácora de importaciones

### 1.3 Importador ya existente (reutilizar, no reinventar)

En `application/controllers/produccion/Productos.php`:

- `importar_archivo_cli` / `importar_entrenamiento_cli` → importación desde Excel.
- `_leer_excel_formulaciones()` → parser de Excel (5 perfiles de hoja distintos).
- `_buscar_producto($ref)` → match por `codigo`/`alias`/`nombre` (LIKE) entre productos `Fabricado`.
- `_buscar_insumo($nombre)` + `_normalizar_nombre_insumo()` (tabla de sinónimos) → match por `nombre_tecnico`.
- `_formulacion_ya_existe()` + `_fingerprint_formulacion()` → dedup por `md5(nombre:porcentaje)` + `cantidad_producida ±0.5`.
- `_guardar_formulacion_importada($pdata)` → crea formulación (`es_activa = FALSE`, `cantidad_producida = total_kg`, `unidad_produccion = 'Kg'`), resuelve o **auto-crea** producto e insumos, e inserta el detalle.

> ⚠️ **Riesgo principal:** el importador **auto-crea** productos e insumos cuando no encuentra match. Con nombres provenientes de OCR eso genera duplicados basura (`IMP-xxxxxxxx`, productos repetidos con variantes de nombre). Por eso Fase 1 construye un **manifiesto curado** y la carga usa únicamente lo aprobado.

---

## 2. Reglas no negociables (aplican a las 3 fases)

1. Respetar `doc/REGLAS_TECNICAS.md` (CI3, MVC estricto, Active Record, soft delete, PRG, sin SQL crudo en controladores/vistas, sin `var_dump`/debug).
2. Trabajar en la rama `iteracion-3`. **Nunca** hacer merge ni push a `main`.
3. **No modificar ni borrar** formulaciones, versiones o componentes existentes. Los cambios se expresan como **versión nueva** (auditoría histórica intacta).
4. **Prohibido** `DROP`, `TRUNCATE`, `DELETE` y `UPDATE` masivo sobre datos de producción. Solo `INSERT` y `UPDATE` puntual con `WHERE id = ...`.
5. Cualquier producto, insumo o precio que no tenga match **confiable** se marca `requiere_revision = true` y **NO se carga** en esa corrida.
6. Todo cambio de código se entrega con `git diff` y commit descriptivo (`feat(produccion): ...` / `fix(produccion): ...`). Sin commitear hasta que el orquestador valide.
7. Al final de cada fase, el agente reporta en formato tabla: **archivo → acción** + evidencia del comando ejecutado + salida literal relevante.

---

## 3. FASE 1 — Análisis, matching y manifiestos (SOLO LECTURA)

> **Prohibido escribir en BD en esta fase.** Solo lectura + archivos nuevos en `doc/entrenamiento_3/manifiestos/`.

~~~text
Trabajas en el ERP de CHISA Recubrimientos (CodeIgniter 3 + MySQL, rama `iteracion-3`).
NO vas a escribir nada en la base de datos en esta tarea: es 100% análisis y generación de archivos.

CONTEXTO
Se digitalizaron 25 capturas del sistema anterior del cliente. El OCR ya está hecho y estructurado
en JSON en `doc/entrenamiento_3/ocr/parsed/` (6 archivos, 22 formulaciones + 2 listas de precios +
1 tabla de rendimientos). Léelos TODOS. El esquema está documentado en
`doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md` (§1.1 y §1.2) y el importador existente en
`application/controllers/produccion/Productos.php` (§1.3). Herramienta de consulta rápida:
`php doc/entrenamiento_3/tools/consulta_db.php find|insumos|form|activas|resumen "texto"`.

ENTREGABLES (crear los 4 archivos)

1) `doc/entrenamiento_3/manifiestos/productos_match.json`
   Una entrada por cada producto detectado en las 22 formulaciones + los de `rendimientos.json`
   y `lista_precios.json`. Formato:
   {
     "ocr_nombre": "VITROGLASS ECOLOGICO",
     "producto_id": 123,            // null si no hay match
     "match_criterio": "codigo|alias|nombre_exacto|nombre_fuzzy|sin_match",
     "confianza": 0.95,             // 0..1
     "accion": "usar_existente|crear|requiere_revision",
     "notas": "por qué"
   }
   Reglas de matching (en este orden):
     a. normaliza el nombre OCR: mayúsculas, colapsar espacios múltiples, quitar acentos y signos,
        corregir las ligaduras típicas del OCR (p.ej. "SOLUCION DEAEROSIL200" -> "SOLUCION DE AEROSIL 200",
        "BASEORGANICA" -> "BASE ORGANICA", "CHISA GLASSREF" -> "CHISA GLASS REF").
     b. busca coincidencia exacta por `codigo`, luego `alias`, luego `nombre`.
     c. si no hay exacta, busca por tokens (todas las palabras significativas presentes en el nombre).
     d. `confianza >= 0.85` -> `usar_existente`; `0.6-0.85` -> `requiere_revision`; `< 0.6` -> `crear`.
   IMPORTANTE: no propongas crear un producto si ya existe uno equivalente con nombre normalizado.
   Documenta cada decisión dudosa en `notas`.

2) `doc/entrenamiento_3/manifiestos/insumos_match.json`
   Una entrada por cada componente (nombre) distinto que aparece en las 22 formulaciones. Formato:
   {
     "ocr_nombre": "RESINA QE2383(W-595)",
     "nombre_canonico": "RESINA QE-2383 W-595",
     "tipo": "Insumo|Producto",
     "insumo_id": 45,               // si tipo=Insumo
     "producto_id": null,           // si tipo=Producto (semielaborado fabricado)
     "accion": "vincular|crear|requiere_revision",
     "confianza": 0.9,
     "notas": "..."
   }
   Reglas:
     a. reutiliza la lógica/tabla de sinónimos de `_normalizar_nombre_insumo()`.
     b. si el nombre corresponde a algo que la planta fabrica (bases, tintas, soluciones, fase acuosa,
        sellador, mortero...), revísalo contra `productos` con `tipo_producto='Fabricado'` y contra
        `insumos.tipo IN ('fabricado','semielaborado')` (vista `v_insumos_fabricados`): en ese caso
        propón `tipo = "Producto"` y `producto_id` correspondiente.
     c. si el match es dudoso, `requiere_revision` y explica. NUNCA propongas crear un insumo si el
        nombre normalizado coincide con uno existente.

3) `doc/entrenamiento_3/manifiestos/comparativa.json` + `doc/entrenamiento_3/manifiestos/comparativa.md`
   Para cada una de las 22 formulaciones, comparar contra lo que hay en BD para ese producto:
   {
     "imagen": "entrenamiento1.jpeg",
     "producto_id": 123,
     "estado": "producto_nuevo|sin_formulacion|coincide_exacto|difiere|requiere_revision",
     "formulacion_activa_id": 900,
     "total_kg_ocr": 19.0,
     "total_kg_bd": 19.0,
     "diff_componentes": [
       {"nombre": "...", "pct_ocr": 25.0, "pct_bd": 24.0, "kg_ocr": 4.75, "kg_bd": 4.56, "estado": "cambio|nuevo|falta_en_ocr|igual"}
     ],
     "recomendacion": "..."
   }
   Reglas: compara por nombre normalizado + porcentaje (tolerancia 0.01). Considera "coincide_exacto"
   solo si el fingerprint (nombre:porcentaje ordenado) es idéntico y el total kg difiere < 0.5.
   En `comparativa.md` escribe una tabla legible por imagen + un resumen ejecutivo al final
   (cuántas coinciden, cuántas difieren, cuántas son nuevas).

4) `doc/entrenamiento_3/manifiestos/decisiones_pendientes.md`
   Lista los casos que requieren criterio humano, como mínimo:
   - `entrenamiento20-grupo.jpeg`: 4 sub-formulaciones de color (NEGRO/BLANCO/AZUL/AMARILLO) que suman
     el lote de 19.35 kg. ¿Se modela como 4 formulaciones separadas (una por color con
     `referencia_cliente = color`) o como una sola formulación con `grupo_color`? Argumenta pros/contras.
   - Componentes sin match (`requiere_revision`).
   - Productos sin match claro.
   - `kg_fase_acuosa` / `porcentaje_fase_acuosa`: ¿qué documentos lo traen y cómo se calcularía?

RESTRICCIONES
- No escribas en la base de datos (ni INSERT, ni UPDATE, ni CREATE).
- No crees productos ni insumos.
- No modifiques formulaciones existentes.
- Puedes leer la BD con SELECT/Active Record sin problema.

SALIDA EN EL CHAT (formato obligatorio)
1. Tabla "archivo creado -> contenido en una línea".
2. Resumen numérico: N productos (X existentes / Y a crear / Z dudosos), N componentes
   (X insumos / Y semielaborados / Z dudosos), N formulaciones (X coinciden / Y difieren / Z nuevas).
3. Lista literal de los casos `requiere_revision` (para que el orquestador los revise).
4. Los 5 hallazgos más importantes que descubriste en los datos.
~~~

**Gate de aprobación F1:** el orquestador revisa los manifiestos, resuelve los `requiere_revision` y las decisiones pendientes, y solo entonces se lanza la Fase 2.

---

## 4. FASE 2 — Importador JSON desde CLI + dry-run (código, sin escrituras)

~~~text
Trabajas en el ERP de CHISA Recubrimientos (CodeIgniter 3 + MySQL, rama `iteracion-3`).
Esta fase SÍ toca código, pero NO debe escribir en la base de datos: solo `--dry-run`.

CONTEXTO
Ya existe un importador de formulaciones desde Excel en `application/controllers/produccion/Productos.php`
(`importar_archivo_cli`, `_leer_excel_formulaciones`, `_formulacion_ya_existe`,
`_guardar_formulacion_importada`, `_buscar_producto`, `_buscar_insumo`, `_normalizar_nombre_insumo`).
Ahora la fuente NO es Excel sino los manifiestos JSON curados en `doc/entrenamiento_3/manifiestos/`
(`productos_match.json`, `insumos_match.json`, `comparativa.json`), aprobados por el orquestador.
Lee la documentación completa en `doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md` (§1 y §2).

TAREA
1) Agrega al controlador `produccion/Productos.php` un método CLI nuevo:
   `importar_formulaciones_json_cli()`
   - Solo ejecutable por CLI: `php index.php produccion/Productos importar_formulaciones_json_cli`.
   - Lee los manifiestos desde `doc/entrenamiento_3/manifiestos/` (ruta relativa a public_html,
     configurable por variable de entorno `IMPORT_MANIFEST_DIR`).
   - Reutiliza las funciones privadas existentes (`_buscar_producto`, `_buscar_insumo`,
     `_formulacion_ya_existe`, `_fingerprint_formulacion`, `_guardar_formulacion_importada`).
     NO dupliques lógica de negocio ni escribas SQL crudo: usa Active Record.
   - Parámetros soportados:
       --dry-run                (obligatorio por defecto en esta fase: no escribe nada)
       --solo=<imagen|ref>      (procesa un único documento, para pruebas)
       --excluir=<lista,coma>   (excluye documentos por imagen)
       --activar-nuevas         (por defecto OFF; ver política de activación abajo)
   - En modo dry-run imprime, sin escribir:
       * productos que usaría (id + nombre) y productos que crearía
       * insumos que vincularía (id + nombre_tecnico) y los que CREARÍA (marca de alerta)
       * semielaborados que enlazaría como `tipo_componente = 'Producto'` (producto_id)
       * por cada formulación: ref, producto, versión que le tocaría (V(n+1)), total kg,
         nº de componentes, y el resultado de `_formulacion_ya_existe()`
       * un bloque final de totales + "NADA ESCRITO (dry-run)"
   - Modo real (solo cuando el orquestador lo autorice, en Fase 3): inserta usando
     `_guardar_formulacion_importada()` y registra en `log_importaciones` con
     `archivo_origen = 'ENTRENAMIENTO_3'` y la lista de imágenes fuente.
2) Soporta `tipo_componente = 'Producto'` en el guardado:
   - Si el manifiesto indica que un componente es un semielaborado fabricado, inserta
     `tipo_componente='Producto'` + `producto_id` (+ `insumo_id = NULL`), respetando el CHECK
     de la tabla. Si el insumo ya está enlazado (`insumos.tipo IN ('fabricado','semielaborado')`
     con `producto_id`), sigue usando `tipo_componente='Insumo'` con su `insumo_id` — el árbol BOM
     ya se explota por esa vía. Deja la decisión documentada en un comentario del código.
   - No rompas la importación desde Excel: los cambios deben ser retrocompatibles.
3) Persiste `grupo_color`, `porcentaje_fase_acuosa` y `orden` en el detalle cuando el manifiesto
   los traiga (el importador de Excel ya los usa: respeta el mismo formato).
4) Política de activación (implementar, pero apagada por defecto):
   - Si el producto NO tenía ninguna formulación -> la nueva se activa solo con `--activar-nuevas`.
   - Si el producto YA tenía una formulación activa -> NUNCA se toca la activa; la nueva queda
     inactiva y se reporta.
5) Ejecuta y pega la salida completa de:
   php index.php produccion/Productos importar_formulaciones_json_cli --dry-run
   y además un dry-run por documento para 3 casos representativos
   (--solo=entrenamiento1.jpeg, --solo=entrenamiento12.jpeg, --solo=entrenamiento20-grupo.jpeg).

RESTRICCIONES
- Nada de escrituras a BD en esta fase (ni productos, ni insumos, ni formulaciones).
- No toques el flujo web (AJAX) de importación ni las vistas.
- No commitees todavía: entrega `git diff` y la salida de la corrida.
- Si encuentras un caso que el manifiesto no cubre, NO improvises: repórtalo.

SALIDA EN EL CHAT (formato obligatorio)
1. Tabla "archivo -> cambio" (con líneas aproximadas).
2. Salida literal del dry-run general + los 3 dry-runs individuales (resumidos si son muy largos,
   pero SIN ocultar: productos a crear, insumos a crear, formulaciones a crear, conflictos).
3. Bloque "Riesgos detectados" con lo que podría salir mal al aplicar de verdad.
~~~

**Gate de aprobación F2:** el orquestador revisa el dry-run (sobre todo la lista de **insumos/productos a crear**) y autoriza —o ajusta el manifiesto— antes de la Fase 3.

---

## 5. FASE 3 — Aplicación real + activación + precios y rendimientos

~~~text
Trabajas en el ERP de CHISA Recubrimientos (CodeIgniter 3 + MySQL, rama `iteracion-3`).
El orquestador APROBÓ el dry-run de la Fase 2. Ahora sí se escribe en la base de datos.

ORDEN DE EJECUCIÓN (no cambiar el orden)

PASO 1 — Carga de formulaciones (modo real)
   php index.php produccion/Productos importar_formulaciones_json_cli --activar-nuevas
   - Si algún producto/insumo a crear no estaba aprobado en el dry-run, DETENTE y repórtalo.
   - Todas las formulaciones nuevas deben quedar con `es_activa = FALSE`, salvo los productos que
     no tenían ninguna formulación previa (esos sí se activan con --activar-nuevas).
   - `comentarios` de cada formulación: "Importado del entrenamiento 3 — imagen <archivo>.jpeg | <notas del manifiesto>".
   - Registra cada corrida en `log_importaciones`.
   - Pega la salida completa.

PASO 2 — Rendimientos (`doc/entrenamiento_3/ocr/parsed/batch_21-22-rendimientos.json`, entrada `rendimientos.jpeg`)
   - Por cada fila, resuelve el producto con `productos_match.json` (solo `confianza >= 0.85`).
   - Escribe el literal en `productos.rendimiento` (VARCHAR), tal cual viene
     (`m2_texto_literal`: "16M2", "14-16M2", …). No inventes equivalencias numéricas.
   - `formulaciones.rendimiento_m2_por_kg`: NO lo escribas todavía. Genera una propuesta en
     `doc/entrenamiento_3/manifiestos/propuesta_rendimiento_m2_por_kg.md` calculada como
     `m2 ÷ contenido_neto del producto` (kg de la presentación Cubeta), indicando la fuente de cada
     número y marcando los casos donde falte `contenido_neto`. Ese archivo lo aprueba el negocio.
   - Reporta cuántos productos quedaron con `rendimiento` actualizado y cuáles no se tocaron.

PASO 3 — Lista de precios 2025 (`lista_precios.json`, ambos archivos)
   - Resuelve cada fila (`precio_sin_iva`) contra el producto y su presentación (Cubeta / Galón / Litro).
   - Escribe `precio_venta` SOLO cuando el match de producto sea de confianza >= 0.90.
   - Si el precio corresponde a una presentación específica (Galón vs Cubeta), guárdalo en
     `presentaciones_producto` de esa presentación; si el producto maneja una sola presentación,
     va en `productos.precio_venta`.
   - Antes de escribir, muestra la tabla completa "producto -> precio anterior -> precio nuevo".
   - Cualquier caso dudoso: NO escribir, dejar en
     `doc/entrenamiento_3/manifiestos/precios_requiere_revision.md`.

PASO 4 — Verificación y cierre
   - Ejecuta en PHP CLI (script temporal en `doc/entrenamiento_3/tools/`, eliminado al terminar) las
     verificaciones: formulaciones creadas por producto, componentes por formulación, suma de
     porcentajes, totales kg, formulaciones activas vs inactivas, insumos nuevos creados.
   - Actualiza `doc/TODO.md` con un resumen breve del avance y deja pendiente lo que requiera
     validación de negocio (rendimiento_m2_por_kg, precios dudosos, decisión del grupo T034).
   - Commit único y descriptivo, por ejemplo:
     `feat(produccion): carga formulaciones entrenamiento 3 (22 fichas, N componentes) + rendimientos y lista de precios 2025`
     Incluye en el commit los manifiestos y excluye los archivos temporales.
   - Pega `git log -1 --stat` y `git status --short`.

LÍMITES DUROS
- No modifiques formulaciones ni componentes existentes (solo INSERT de versiones nuevas).
- No borres nada (`DELETE`/`TRUNCATE`/`DROP` prohibidos).
- No hagas merge ni push a `main`.
- Si un paso falla a la mitad, detente y reporta; no continúes con el siguiente.
~~~

---

## 6. Decisiones que requieren aprobación humana (no las resuelve la IA)

| # | Tema | Opciones |
|---|------|----------|
| 1 | `entrenamiento20-grupo.jpeg` (CHISA GLASS REF T034) | (a) 4 formulaciones separadas, una por color, con `referencia_cliente = NEGRO/BLANCO/AZUL/AMARILLO`; (b) 1 formulación con `grupo_color` en el detalle; (c) 1 formulación con las 4 variantes como versiones |
| 2 | Componentes sin match (`requiere_revision`) | cargar como insumo nuevo / enlazar a un semielaborado existente / excluir |
| 3 | `rendimiento_m2_por_kg` | aprobar el cálculo `m2 ÷ contenido_neto` o capturarlo manualmente |
| 4 | Precios 2025 | ¿`precio_venta` con o sin IVA? (el OCR dice "precio sin IVA") y ¿aplica a todas las presentaciones? |
| 5 | Formulaciones que ya existen y difieren | ¿la variante del OCR reemplaza a la activa (activarla) o queda como versión alternativa inactiva? |

---

## 7. Checklist de verificación del orquestador (post-fase)

**Tras Fase 1**
- [ ] Ningún archivo del repo modificado (`git status` solo muestra `doc/entrenamiento_3/`)
- [ ] Todos los productos del OCR aparecen en `productos_match.json` (22 formulaciones + precios + rendimientos)
- [ ] Ninguna propuesta de "crear" que en realidad ya existe (revisar a mano los dudosos)
- [ ] La comparativa detecta correctamente la formulación activa de cada producto
- [ ] Las decisiones pendientes están explícitas y son respondibles

**Tras Fase 2**
- [ ] El dry-run no escribió nada (verificar conteos en BD antes/después)
- [ ] La lista de insumos a crear es razonable y sin duplicados evidentes
- [ ] Los semielaborados se enlazan como `Producto` o vía insumo fabricado (no como insumo nuevo)
- [ ] El importador de Excel sigue funcionando (no hay regresión)
- [ ] Los porcentajes y kg del dry-run cuadran con el JSON

**Tras Fase 3**
- [ ] N formulaciones creadas = N aprobadas; ninguna activa pisada
- [ ] Ninguna formulación existente modificada
- [ ] `log_importaciones` con las filas de la corrida
- [ ] Totales de porcentaje ≈ 100 % por formulación (salvo casos documentados)
- [ ] Sin insumos `IMP-%` no aprobados
- [ ] `doc/TODO.md` actualizado y commit hecho con mensaje describiendo el avance

---

*ERP Chisa Recubrimientos — Ingeniería de Software · Prompts generados para la iteración 3 (Entrenamiento 3 de Producción).*
