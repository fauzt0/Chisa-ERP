# HANDOFF — Entrenamiento 3 (Producción): estado recuperado + prompt de arranque

> **Para:** el agente/desarrollador (o chat nuevo) que continúa este task en otro equipo.
> **Rama:** `iteracion-3` · **Último commit del baseline:** `148683d`
> **Documento compañero:** `doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md` (contiene el detalle técnico completo y las **Fases 2 y 3** de los prompts).

---

## 1. Cómo usar este documento

1. Lee las secciones 2–6 para entender el estado y las reglas.
2. Copia **tal cual** el bloque de la **§8 (PROMPT COMPOSER 2.5 — FASE 1)** en un chat nuevo (Composer 2.5).
3. Cuando Composer entregue los manifiestos, valídalos con la checklist de la §9.
4. Solo entonces pasa a la Fase 2 y luego a la Fase 3 (en el documento compañero, §4 y §5).

**Nada del trabajo ya hecho (OCR + JSON) debe rehacerse.** Todo está versionado en el repo.

---

## 2. Estado actual: qué pasó y qué se recuperó

El agente anterior (chat local del 2026-09-10) **se abortó a media tarea** (dos `User aborted request`). No hubo daño: **no se escribió nada en la base de datos y no quedó nada a medias en git**.

Alcance de lo que sí quedó hecho y ahora está consolidado:

| Etapa | Estado |
|-------|--------|
| OCR local (RapidOCR) de las 25 capturas | ✅ 25 pares `.txt` + `.tsv` |
| Parseo a JSON estructurado (6 subagentes) | ✅ 22 formulaciones / **156 componentes** + 2 listas de precios (45 filas) + 32 rendimientos |
| Consolidación de los JSON en disco | ⚠️ Se perdió en el abort → **recuperada desde los transcripts de los subagentes** |
| Comparación contra la BD del ERP | ❌ No se hizo (es el trabajo de la Fase 1) |
| Carga de productos/formulaciones/precios | ❌ No se hizo (Fases 2 y 3, con gates) |

**Riesgo que hay que mitigar en la ejecución:** el importador actual (`importar_archivo_cli` → `_guardar_formulacion_importada`) **auto-crea** productos e insumos cuando no encuentra match. Con nombres que vienen de OCR eso genera duplicados basura (`IMP-xxxxxxxx`, productos repetidos con variantes del nombre). Por eso la Fase 1 construye un **manifiesto curado** y la carga solo usa lo aprobado.

---

## 3. Inventario de datos (ya en el repo)

Carpeta raíz: `doc/entrenamiento_3/` — **84 archivos**.

| Ruta | Contenido |
|------|-----------|
| `doc/entrenamiento_3/imagenes/` | **25** capturas originales: `entrenamiento1..22.jpeg`, `entrenamiento20-grupo.jpeg`, `lista-precios-1.jpeg`, `lista-precios2.jpeg`, `rendimientos.jpeg` |
| `doc/entrenamiento_3/ocr/` | OCR crudo (**50** archivos): 25 `.txt` (líneas reconstruidas `y=<coord> :: [x]texto \| [x]texto`) + 25 `.tsv` (`y \t x \t texto`) |
| `doc/entrenamiento_3/ocr/parsed/` | **6 JSON** con la estructura final: `batch_1-5.json`, `batch_6-10.json`, `batch_11-15.json`, `batch_16-20.json`, `batch_21-22-rendimientos.json`, `lista_precios.json` |
| `doc/entrenamiento_3/tools/` | `consulta_db.php` (consulta rápida a BD), `ocr_rapid_v1.py`, `ocr_rapid_v2.py` (OCR, solo si hay que reprocesar una imagen) |
| `doc/entrenamiento_3/manifiestos/` | **Vacía**: aquí escribe la Fase 1 sus 4 entregables |

### Esquema resumido del JSON de formulación

```json
{
  "imagen": "entrenamiento1.jpeg",
  "tipo_documento": "formulacion | formulacion_grupo",
  "producto": { "nombre": "VITROGLASS ECOLOGICO", "codigo_o_referencia": null, "cliente": null },
  "lote": { "cantidad": 19.0, "unidad": "Kg", "presentacion": "Cubeta" },
  "componentes": [
    { "nombre": "RESINA QE2383(W-595)", "porcentaje": 25.0, "cantidad": 4.75, "unidad": "Kg", "precio_unitario": null }
  ],
  "variantes": [ { "color": "BLANCO", "porcentaje_color": 85.0, "cantidad_color": 16.448 } ],
  "totales": { "porcentaje": 100.0, "cantidad": 19.0 },
  "dudas": ["..."], "confianza": 0.95
}
```

- `cantidad` = **kg del lote** (no por cubeta).
- Solo `entrenamiento20-grupo.jpeg` trae `variantes` (4 sub-formulaciones de color) → decisión de negocio pendiente (§10).

---

## 4. Baseline en git

- Rama: `iteracion-3`
- Commit del baseline: **`148683d`** — *"docs(produccion): entrena 3 — 25 capturas OCR, JSON estructurado y prompts de carga por fases"*
- Ese commit incluye: `doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md` + las 84 piezas de `doc/entrenamiento_3/`.
- **Antes de empezar en el equipo nuevo:** `git pull origin iteracion-3` (verifica que el commit `148683d` exista; si no, el equipo anterior debe hacer `push`).

---

## 5. Requisitos del nuevo equipo

- PHP CLI con acceso al proyecto en `public_html/` (el importador y `tools/consulta_db.php` se ejecutan por CLI).
- Acceso a la base de datos de producción (el proyecto **no** tiene BD separada de staging para este módulo).
- **No se necesita** el entorno OCR (`/home/admin/domains/erp.chisarecubrimientos.com.mx/.ocr-tmp`) salvo que haya que reprocesar una imagen; el OCR ya está hecho.
- Herramienta de consulta rápida disponible:
  ```bash
  php doc/entrenamiento_3/tools/consulta_db.php resumen
  php doc/entrenamiento_3/tools/consulta_db.php find "BASE ORGANICA"
  php doc/entrenamiento_3/tools/consulta_db.php insumos "RESINA"
  php doc/entrenamiento_3/tools/consulta_db.php activas 202
  ```

---

## 6. Reglas no negociables (aplican a todas las fases)

1. Respetar `doc/REGLAS_TECNICAS.md` (CI3, MVC estricto, Active Record, soft delete, PRG, sin SQL crudo en controladores/vistas, sin `var_dump`/debug).
2. Trabajar en `iteracion-3`. **Nunca** merge ni push a `main`.
3. **No modificar ni borrar** formulaciones, versiones o componentes existentes: los cambios se expresan como **versión nueva** (auditoría intacta).
4. **Prohibido** `DROP`, `TRUNCATE`, `DELETE` y `UPDATE` masivo. Solo `INSERT` y `UPDATE` puntual con `WHERE id = ...`.
5. Todo producto/insumo/precio sin match **confiable** se marca `requiere_revision` y **no se carga**.
6. Entregar siempre: tabla *archivo → cambio*, evidencia del comando ejecutado y salida literal relevante.
7. No commitear hasta que el orquestador valide la fase.

---

## 7. Plan de ejecución (3 fases con gate)

| Fase | Qué hace | Escribe en BD? | Gate antes de continuar |
|------|----------|----------------|-------------------------|
| **1** | Matching de productos/insumos + comparativa contra BD → 4 manifiestos en `doc/entrenamiento_3/manifiestos/` | ❌ No | Revisar manifiestos y resolver dudosos |
| **2** | Método CLI `importar_formulaciones_json_cli` en `produccion/Productos.php` + corrida `--dry-run` | ❌ No | Revisar dry-run (sobre todo insumos/productos que crearía) |
| **3** | Carga real de formulaciones + rendimientos + lista de precios 2025 + commit | ✅ Sí | Verificación final y commit |

Los prompts completos de las **Fases 2 y 3** están en `doc/PROMPT_ENTRENAMIENTO_3_PRODUCCION.md` (§4 y §5). La Fase 1 va aquí abajo, lista para pegar.

---

## 8. PROMPT COMPOSER 2.5 — FASE 1 (pegar tal cual en un chat nuevo)

```text
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
```

---

## 9. Qué validar cuando regrese la Fase 1 (checklist del orquestador)

- [ ] `git status` no muestra archivos del código modificados (solo nuevos en `doc/entrenamiento_3/manifiestos/`)
- [ ] Los 22 productos de formulación + los de precios/rendimientos aparecen en `productos_match.json`
- [ ] Ningún `"accion": "crear"` que en realidad ya exista en el catálogo (revisar a mano los dudosos)
- [ ] `comparativa.md` identifica bien la formulación activa de cada producto
- [ ] Los `requiere_revision` son respondibles (no son "no sé, decide tú")
- [ ] Los totales de porcentaje por formulación ≈ 100 %

**Pistas conocidas del primer cotejo (para no perder tiempo):** de los 22 productos, ya existen y están *Activos/Fabricados* los de TINTAS (rojo carmín, roja, negra, verde cromo, amarillo óxido), BASES ORGÁNICAS (blanca, negra, rojo óxido, amarillo óxido), SOLUCIONES (Aerosil 200, fase acuosa, resina EC-1) y **PINTU FLEX**. Sin coincidencia exacta en el primer barrido: **VITROGLASS ECOLOGICO**, **PRAIMER GLASS / CHISA VINIL**, **SELLADOR INICIAL**, **CHISA PLUS** y **CHISA GLASS REF T034** — ojo, varios "sin coincidencia" son solo variantes de captura (`SOLUCION DEAEROSIL200` → ya existe como `SOLUCION DE AEROSIL 200`).

---

## 10. Decisiones pendientes del negocio (no las resuelve la IA)

| # | Tema | Opciones |
|---|------|----------|
| 1 | `entrenamiento20-grupo.jpeg` (CHISA GLASS REF T034) | (a) 4 formulaciones separadas por color; (b) 1 formulación con `grupo_color`; (c) 1 formulación con 4 versiones |
| 2 | Componentes sin match (`requiere_revision`) | cargar como insumo nuevo / enlazar a semielaborado existente / excluir |
| 3 | `rendimiento_m2_por_kg` | aprobar el cálculo `m2 ÷ contenido_neto` o capturarlo manualmente |
| 4 | Precios 2025 | ¿`precio_venta` con o sin IVA? (la lista dice "sin IVA") y ¿aplica a todas las presentaciones? |
| 5 | Formulaciones que ya existen y difieren | ¿la variante del OCR reemplaza a la activa o queda como versión alternativa inactiva? |

---

## 11. Comandos útiles

```bash
# Estado del repo
git log --oneline -3 && git status --short

# Verificar que el baseline llegó completo (deben ser 84 archivos)
find doc/entrenamiento_3 -type f | wc -l

# Resumen de datos recuperados
python3 - <<'EOF'
import json, glob
f = 'doc/entrenamiento_3/ocr/parsed/'
comps = sum(len(d.get('componentes') or []) for p in glob.glob(f+'batch_*.json') for d in json.load(open(p, encoding='utf-8')))
prec  = sum(len(x.get('filas', [])) for x in json.load(open(f+'lista_precios.json', encoding='utf-8')))
print(f'componentes={comps}  filas_precios={prec}')
EOF

# Consultas rápidas a la BD
php doc/entrenamiento_3/tools/consulta_db.php resumen
php doc/entrenamiento_3/tools/consulta_db.php activas <producto_id>
```

---

*ERP Chisa Recubrimientos — Ingeniería de Software · Handoff generado el 2026-09-10 para continuar la iteración 3 (Entrenamiento 3 de Producción) en otro equipo.*
