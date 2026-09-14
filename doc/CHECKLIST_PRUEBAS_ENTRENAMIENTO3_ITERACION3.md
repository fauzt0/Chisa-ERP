# CHECKLIST DE PRUEBAS — Entrenamiento 3 (Iteración 3)

**Fecha:** 2026-09-13 · **Rama:** `iteracion-3` · **Alcance:** regresión de rutas, explosión BOM de las
formulaciones activas que usan los insumos enlazados (A3) y catálogo con los 29 productos nuevos.
**Modo:** SOLO LECTURA — ninguna escritura en la BD (endpoints y métodos verificados por inspección:
`explotar_bom_*` y `calcular_materiales_*` no tienen `insert`/`update`/`delete`).
**Herramienta:** `doc/entrenamiento_3/tools/pruebas_entrenamiento3.php` (temporal, se elimina al cerrar la iteración).

---

## 1. Regresión de rutas (render por CLI)

| Ruta | bytes | fatales | deprecations PHP 8 | título |
|------|-------|---------|--------------------|--------|
| `obras/Obras` | 110,329 | 0 | 42 | Gestión de Obras |
| `obras/Obras/detalle/2` | 184,691 | 0 | 42 | Detalle de Obra - OB-00002 |
| `ventas/ObrasVentas` | 96,367 | 0 | 44 | Obras |
| `almacen/Entregas` | 108,779 | 0 | 42 | Entregas de Almacén |
| `produccion/Productos` | 154,004 | 0 | 44 | Productos |
| `produccion/Dashboard` | 143,771 | 0 | 42 | Dashboard de Producción |

✅ **0 errores fatales** en las 6 rutas. Las 42–44 "deprecations" por render son ruido preexistente de
PHP 8 (`Creation of dynamic property …`), ajeno a esta iteración.

## 2. Explosión BOM de las formulaciones activas con insumos enlazados (A3)

| Form | Producto (versión activa) | Insumos enlazados que usa | Nodos | Fabricados | Cortes anti-ciclo | Hojas (plano) | kg plano (base 1 kg) |
|------|---------------------------|---------------------------|-------|-----------|-------------------|---------------|----------------------|
| 314 | SOLUCION DE AEROSIL 200 V3 | #91 (auto-referencia) | 3 | 1 | 1 → form#314 | 2 | 0.8786 |
| 304 | TINTA AMARILLO OXIDO V2 | #96 (auto-referencia) | 5 | 1 | 1 → form#304 | 4 | 0.6800 |
| 311 | BASE ORGANICA NEGRA V2 | #91, #105 | 16 | 3 | 1 → AEROSIL form#314 | 13 | 0.9711 |
| 300 | BASE ORGANICA BLANCA V2 | #91 | 13 | 2 | 1 → AEROSIL form#314 | 11 | 0.9741 |

✅ El anti-ciclo funciona: **no hay recursión infinita ni errores**; los tres cortes son las
auto-referencias conocidas (A3). La expansión de la TINTA NEGRA (#105→#205) se completó sin corte.

## 3. Cálculo de materiales de Obras

| Caso | Llamada | Resultado |
|------|---------|-----------|
| 2.1 | producto 3, 50 m², factor 1.1, override 2.5 | ✅ `kg_necesarios=22`, `m2_efectivo=55`, form 66 (V5), `cubetas=1` (lote 570.35 kg), `rendimiento_origen="linea"` |
| 2.3 | producto 3, 50 m², sin rendimiento | ✅ `requiere_rendimiento:true`, form 66, mensaje descriptivo |
| 2.2 | producto 161, 50 m² | ✅ `requiere_formulacion:true` + mensaje |
| extra | `materiales_obra_ajax obra_id=2` | ✅ 1 línea, 2.75 kg, 1 cubeta; consolidado: Pintura Vinílica Blanca 0.5 Cubeta |

> Nota vs. el checklist original: el producto 3 cambió de formulación activa (hoy form 66 V5, lote
> 570.35 kg) y su receta ya no trae rendimiento ⇒ los casos siguen pasando, pero con esos valores.

## 4. Catálogo con los 29 productos nuevos

| Prueba | Resultado |
|--------|-----------|
| `obras/Obras/get_productos_ajax` (selector de productos de obra) | 494 activos; **los 29 nuevos aparecen**; **28/29 con `precio_venta = NULL`** (solo #475 VITROGLASS = $5,023.57) |
| `produccion/Dashboard/get_catalogo_ajax termino=IMPERGLASS` | 3 resultados: #501, #502, #503 |

---

## 5. Hallazgos

1. **BOM-1 (dato preexistente, ahora visible por A3).** La receta activa de #204 (form#314) incluye el
   insumo **#91 al 12.14 %** (auto-referencia). Con el enlace `#91→204`, el plano BOM descarta ese
   12.14 % de la masa (antes el insumo aparecía como hoja con su kg completo). Los consolidados que usan
   `explotar_bom_plano` (`ProductosModel` líneas 1389 y 1529 → cálculo de insumos de obra/proyecto)
   subestiman ese porcentaje en las fórmulas que usan la SOLUCION DE AEROSIL 200.
   **Recomendación:** versión nueva de #204 sustituyendo la línea `#91` por `#118 AEROSIL 200`
   (probable enlace erróneo del importador viejo). Decisión de negocio; mientras, el sesgo es de 12.14 %.
2. **Catálogo-1.** Los 28 productos nuevos sin precio son seleccionables al armar una obra (saldrían en
   $0). No cotizarlos hasta el PASO 3; se pueden dejar listados/buscables sin problema.
3. **Ruido PHP 8**: 42–44 deprecations por render (preexistentes, no bloquean).
4. Los casos 2.1–2.3 del checklist de Obras siguen OK con los datos vigentes.

## 6. Cómo re-ejecutar

```bash
php doc/entrenamiento_3/tools/pruebas_entrenamiento3.php render obras/Obras
php doc/entrenamiento_3/tools/pruebas_entrenamiento3.php bom 311 1
php doc/entrenamiento_3/tools/pruebas_entrenamiento3.php route obras/Obras/calcular_materiales_ajax producto_id=3 area_aplicacion=50 rendimiento_teorico=2.5
php doc/entrenamiento_3/tools/pruebas_entrenamiento3.php sql "SELECT ..."   # solo SELECT
```

---

*ERP Chisa Recubrimientos — Entrenamiento 3, pruebas de iteración (solo lectura).*
