# Propuesta PASO 3 — presentaciones, precios y `rendimiento_m2_por_kg`

> **Estado: PROPUESTA de negocio — no se ha escrito nada en la BD.**
> Generada el 2026-09-13 a partir de `productos_match.json` (lista de precios 2025) con la misma
> fórmula del importador (`_calc_m2_por_kg_json`) y los datos que el dry-run de Fase 2 ya reportó.
> Continúa a: `doc/entrenamiento_3/manifiestos/dry_run_fase2.txt` §8 y `decisiones_pendientes.md` (A8/A9).
>
> **Seguimiento (13-sep-2026):** los datos de §3 fueron solicitados a negocio/planta y **no se tienen
> todavía**. No bloquean las pruebas de la iteración (ninguna versión activa fue modificada); este
> documento queda como la lista de pendientes para cerrar el PASO 3.

---

## 1. Reglas aplicadas (A8/A9 + R8)

1. `productos.precio_venta` = **precio de la lista 2025 tal cual (sin IVA)** (A8.2).
2. `productos.rendimiento` (texto) = rango literal de la lista (A8.3).
3. `formulaciones.rendimiento_m2_por_kg` = **punto medio del rango m² ÷ contenido neto del envase** (A9).
4. Tasa de consumo (`250gr./1m2`) se **invierte**: 0.25 kg/m² → **4 m²/kg** (R8; la convención
   "0.25 m²/kg" es incorrecta).
5. Si no se conoce el contenido neto de la presentación **no se inventa**: queda `FALTANTE` (D2 del código).

## 2. Resultado del cálculo sobre las 45 filas de la lista

- **Calculables hoy: 2**
  - `VITROGLASS ECOLOGICO` CUBETA 19 kg → **9.74 m²/kg** (180-190 m²).
  - `POLY-COLOR(ESCAMA)` → **4 m²/kg** (invertida de 250 g/m²).
- **FALTANTE: 43 filas** — todas por la misma causa: la lista 2025 no trae el contenido neto del envase
  (galón o "(sin pres.)"), y en los productos existentes `productos.contenido_neto` guarda otra cosa
  (`#223 PINTU FLEX = 135` y `#222 ARENA SILICA = 945` son **lote**, no envase; `#404 = 5`).

| # | Producto | Categoría | Presentación | Precio s/IVA | Rendimiento (lista) | m²/envase (punto medio) | m²/kg |
|---|----------|-----------|--------------|--------------|---------------------|-------------------------|-------|
| 1 | VITROGLASS ECOLOGICO | SELLADORES | GALON | $1,244.19 | 30-32m2 | 31 m² | FALTANTE |
| 2 | VITROGLASS ECOLOGICO | SELLADORES | CUBETA | $5,023.57 | 180-190m2 | 185 m² | 9.74 m²/kg |
| 3 | PINTU FLEX | PINTURAS ARQUITECTONICAS | GALON | $487.74 | 18-19.6m2 | 18.8 m² | FALTANTE |
| 4 | PINTU FLEX | PINTURAS ARQUITECTONICAS | CUBETA | $2,410.87 | 90-95m2 | 92.5 m² | FALTANTE |
| 5 | CHISA PLUS | PINTURAS ARQUITECTONICAS | GALON | $658.89 | 18-19.6m2 | 18.8 m² | FALTANTE |
| 6 | CHISA PLUS | PINTURAS ARQUITECTONICAS | CUBETA | $2,840.13 | 90-95m2 | 92.5 m² | FALTANTE |
| 7 | MARMOPLAST | PASTAS ARQUITECTONICAS | GALON | $292.22 | 1.4-2m2 | 1.7 m² | FALTANTE |
| 8 | MARMOPLAST | PASTAS ARQUITECTONICAS | CUBETA | $1,391.16 | 7-10m2 | 8.5 m² | FALTANTE |
| 9 | SELLADORACRILICO4648 | SELLADORES | GALON | $277.07 | 18-20m2 | 19 m² | FALTANTE |
| 10 | SELLADORACRILICO4648 | SELLADORES | CUBETA | $907.37 | 90-100m2 | 95 m² | FALTANTE |
| 11 | HEALERGLASSKIT1LT.C/ESPATULA | SELLADORES | (sin pres.) | $262.05 | 1.7-2.1m2 | 1.9 m² | FALTANTE |
| 12 | REDYPLASTF | PREPARADORES DE SUPERFICIE | (sin pres.) | $987.11 | 25-30m2 | 27.5 m² | FALTANTE |
| 13 | REDYPLASTM | PREPARADORES DE SUPERFICIE | (sin pres.) | $1,042.79 | 18-22m2 | 20 m² | FALTANTE |
| 14 | REDYPLASTG | PREPARADORES DE SUPERFICIE | (sin pres.) | $1,103.44 | 12-14m2 | 13 m² | FALTANTE |
| 15 | MORTEROPLASTICOMALLA350-400 | PREPARADORES DE SUPERFICIE | (sin pres.) | $1,205.56 | 16-18m2 | 17 m² | FALTANTE |
| 16 | ARENASILICAMALLA250-300 | PREPARADORES DE SUPERFICIE | (sin pres.) | $1,188.64 | 22-25m2 | 23.5 m² | FALTANTE |
| 17 | DECOPASTA | PASTAS ARQUITECTONICAS | GALON | $184.75 | 3.2-3.6m2 | 3.4 m² | FALTANTE |
| 18 | DECOPASTA | PASTAS ARQUITECTONICAS | CUBETA | $804.86 | 16-18m2 | 17 m² | FALTANTE |
| 19 | CHISAPLAST | PASTAS ARQUITECTONICAS | GALON | $258.27 | 3-4m2 | 3.5 m² | FALTANTE |
| 20 | CHISAPLAST | PASTAS ARQUITECTONICAS | CUBETA | $1,016.34 | 16-20m2 | 18 m² | FALTANTE |
| 21 | CHISAPLASTPLUS | PASTAS ARQUITECTONICAS | GALON | $1,106.56 | 4-5m2 | 4.5 m² | FALTANTE |
| 22 | CHISAPLASTPLUS | PASTAS ARQUITECTONICAS | CUBETA | $3,400.29 | 24-26m2 | 25 m² | FALTANTE |
| 23 | PRAIMERGLASS | PINTURAS ARQUITECTONICAS | CUBETA | $1,028.18 | 85-90m2 | 87.5 m² | FALTANTE |
| 24 | CHISAVINIL | PINTURAS ARQUITECTONICAS | GALON | $418.55 | 18-19.6m2 | 18.8 m² | FALTANTE |
| 25 | CHISAVINIL | PINTURAS ARQUITECTONICAS | CUBETA | $1,334.31 | 90-95m2 | 92.5 m² | FALTANTE |
| 26 | PROTECTPLUS | PINTURAS ARQUITECTONICAS | GALON | $823.61 | 18-19.6m2 | 18.8 m² | FALTANTE |
| 27 | PROTECTPLUS | PINTURAS ARQUITECTONICAS | CUBETA | $4,118.19 | 90-95m2 | 92.5 m² | FALTANTE |
| 28 | SHELL HARD(CASCARA DENARANJA) | SHELL HARD (CASCARA DE NARANJA) | GALON | $423.02 | 4-5m2 | 4.5 m² | FALTANTE |
| 29 | SHELL HARD(CASCARA DENARANJA) | SHELL HARD (CASCARA DE NARANJA) | CUBETA | $1,704.15 | 20-25m2 | 22.5 m² | FALTANTE |
| 30 | SHELLHARD LISO(CASCARALISO) | SHELL HARD (CASCARA DE NARANJA) | GALON | $495.83 | 5-6m2 | 5.5 m² | FALTANTE |
| 31 | SHELLHARD LISO(CASCARALISO) | SHELL HARD (CASCARA DE NARANJA) | CUBETA | $2,183.04 | 25-30m2 | 27.5 m² | FALTANTE |
| 32 | CHISA GLASSMICRO | CHISA GLASS | (sin pres.) | $4,797.13 | 65-70m2 | 67.5 m² | FALTANTE |
| 33 | CHISA GLASSTEXTURADO | CHISA GLASS | (sin pres.) | $4,797.13 | 55-60m2 | 57.5 m² | FALTANTE |
| 34 | TRICOLORPLUS | CHISA GLASS | (sin pres.) | $7,805.79 | 150-190m2 | 170 m² | FALTANTE |
| 35 | CORTPLAST | CHISA GLASS | (sin pres.) | $4,393.11 | 20-25m2 | 22.5 m² | FALTANTE |
| 36 | CHISA MAR | CHISA GLASS | GALON | $875.93 | 24-26m2 | 25 m² | FALTANTE |
| 37 | CHISA MAR | CHISA GLASS | CUBETA | $3,597.23 | 120-130m2 | 125 m² | FALTANTE |
| 38 | POLY-COLOR(ESCAMA) | POLYCOLOR | (sin pres.) | $251.24 | 250gr./1m2 | 1 m² | 4.0 m²/kg |
| 39 | POLY-PLAST | POLYCOLOR | (sin pres.) | $2,373.64 | 22-25m2 | 23.5 m² | FALTANTE |
| 40 | POLYTANO9000(RESINA EPOXICA) | POLYCOLOR | (sin pres.) | $3,827.67 | 10-12m2 | 11 m² | FALTANTE |
| 41 | MARMOFLEXREF.BLANCO | GRANOS DE MARMOL | (sin pres.) | $4,433.20 | 5m2 | 5 m² | FALTANTE |
| 42 | MARMOFLEXREF.COLOR | GRANOS DE MARMOL | (sin pres.) | $4,759.76 | 5m2 | 5 m² | FALTANTE |
| 43 | IMPERGLASS3ANOS | IMPERMEABILIZANTE | (sin pres.) | $2,089.25 | 45-50m2 | 47.5 m² | FALTANTE |
| 44 | IMPERGLASS5ANOS | IMPERMEABILIZANTE | (sin pres.) | $2,994.38 | 45-50m2 | 47.5 m² | FALTANTE |
| 45 | IMPERGLASS7ANOS | IMPERMEABILIZANTE | (sin pres.) | $3,693.85 | 45-50m2 | 47.5 m² | FALTANTE |

> Nota: los productos que se venden por Kg (`#222 ARENA SILICA`, `#223 PINTU FLEX`, `#404 CHISA GLASS
> TEXTURADO`) guardan en `contenido_neto` el **lote de fabricación**, por eso su m²/kg queda FALTANTE
> aunque la lista traiga m²; se requiere el **peso real del envase**.

## 3. Datos que necesito de negocio para cerrar el PASO 3

1. **Contenido neto (kg) del envase CUBETA** de los 28 productos de la lista 2025 (¿19 kg para todos,
   como VITROGLASS?). Con eso se calculan 21 filas CUBETA.
2. **Contenido neto (kg) del envase GALÓN** (o confirmar 3.785 L + densidad) para las 11 filas GALÓN.
3. **Presentación real de las 16 filas "(sin pres.)"**: HEALER GLASS KIT 1 LT (¿es Litro/Pieza, no cubeta?),
   REDY PLAST F/M/G, MORTERO PLASTICO MALLA 350-400, ARENA SILICA MALLA 250-300, CHISA GLASS MICRO/TEXTURADO,
   TRICOLOR PLUS, CORTPLAST, POLY-COLOR (¿venta por kg?), POLY-PLAST, POLYTANO 9000, MARMOFLEX ×2,
   IMPERGLASS ×3.
4. **POLY-COLOR (ESCAMA)**: confirmar la convención de consumo (250 g/m² → 4 m²/kg).
5. **`unidad_venta` de los 27 productos creados**: hoy todos quedaron `Cubeta` por defecto; corregir los
   que no lo sean (p. ej. `#480 HEALER GLASS KIT 1 LT`, los que se venden por Kg).
6. **R1 — `#476 SELLADOR INICIAL`**: precio de venta (no está ni en la lista ni en `rendimientos.jpeg`).
7. **`#83` vs `#214` (EC-1)**: ver §5; el 4º enlace insumo→semielaborado sigue sin aplicar.

## 4. Qué escribiría el PASO 3 (con la aprobación de estos datos)

- `UPDATE` puntuales por producto (`WHERE id = …`) sobre `#476–#503`: `precio_venta`,
  `presentacion_principal`, `contenido_neto`, `unidad_contenido`, `unidad_venta` y `rendimiento` (texto).
- `UPDATE` puntual de `formulaciones.rendimiento_m2_por_kg` en las 12 versiones nuevas:
  hoy sólo `#966` (VITROGLASS) tiene valor derivable = **9.74**; las demás quedan sin dato (A9: si el
  producto no trae m², la formulación queda sin rendimiento).
- **Presentaciones múltiples (GALÓN/CUBETA)**: `productos` sólo guarda una presentación
  (`presentacion_principal` + `contenido_neto`); el soporte de variantes existe
  (`producto_padre_id`, `es_variante`, `variante_tipo`, `variante_valor`). Decisión pendiente:
  (a) filas variante para el GALÓN, o (b) cargar sólo la presentación base.

## 5. Evidencia sobre el enlace `#83` → `#214` (seguimiento de R3)

Consulta de solo lectura del 2026-09-13:

- Insumo **#83 "SOLUCION DE RESINA"** (`IMP-7FB3924F`) se usa en **las 7 bases** (202, 210, 211, 212,
  213, 216, 217) entre 23.59 % y 31.25 %.
- En esas **mismas versiones** la fórmula lleva `EXXOL D-40` (**#108**) como línea aparte (17.26–23.51 %).
- El producto **#214 "SOLUCION DE RESINA EC-1"** (form #315 v2 activa) = **PLIOWAY EC-1 49.68 % +
  EXXOL D-40 49.68 % + ANTI TERRA 204 0.64 %**.
- Si `#83` fuese `#214`, la explosión de las bases **duplicaría el EXXOL** (línea propia + el que trae
  la solución) y alteraría su costo ⇒ **no se aplica el enlace hasta que planta confirme la equivalencia**.

Enlaces ya aplicados (4): `#91→204`, `#96→206`, `#105→205`, `#157 FASE ACUOSA→215`.

---

*ERP Chisa Recubrimientos — entrenamiento 3, cierre de Fase 3 (PASO 3 pendiente de datos de negocio).*
