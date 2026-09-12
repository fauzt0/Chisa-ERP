#!/usr/bin/env python3
"""
FASE 1 — Matching del entrenamiento 3 (solo lectura).

Cruza los JSON del OCR contra el volcado de la BD (/tmp/f1_dump) y genera los manifiestos
en doc/entrenamiento_3/manifiestos/.

Hallazgos que motivan las reglas de este motor (ver doc/entrenamiento_3/manifiestos/):
- El OCR pega/parte palabras ("EXXOLD-40" == "EXXOL D-40", "SOLUCION DEAEROSIL200").
  → se compara con claves sin caracteres no alfanuméricos + contención de subcadenas.
- El catálogo de insumos ya tiene duplicados autogenerados (códigos IMP-%).
  → se prefiere siempre el insumo canónico y se anotan los duplicados.
- Varias fichas del entrenamiento 3 YA se importaron antes, pero no necesariamente en la
  versión activa (producto #204 tiene la receta en V1/V2 y una V3 activa distinta).
  → la comparativa se hace contra TODAS las versiones, no solo la activa.
- Hay formulaciones legacy contaminadas (una sola versión con componentes de varias recetas
  mezcladas vía grupo_color, y auto-referencias insumo↔producto).
  → se detectan y se reportan como incidencias de calidad de datos.

Uso:  python3 doc/entrenamiento_3/tools/fase1_matching.py [carpeta_dump]
"""
import json
import os
import re
import sys
import unicodedata
from difflib import SequenceMatcher
from collections import defaultdict

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))      # doc/entrenamiento_3
MANIFIESTOS = os.path.join(RAIZ, 'manifiestos')
PARSED = os.path.join(RAIZ, 'ocr', 'parsed')
DUMP = sys.argv[1] if len(sys.argv) > 1 else '/tmp/f1_dump'

UMBRAL_FUZZY = 0.88
UMBRAL_FUZZY_FUERTE = 0.95
MIN_SUBCADENA = 5          # longitud mínima para aceptar contención de claves
TOL_PCT = 0.01

# ── Sinónimos: nombre OCR -> nombre canónico en BD (revisados a mano tras ver el primer reporte)
SINONIMOS = {
    'DIPERSANTE': 'DISPERSANTE',
    'MONOETILIENGLICOL': 'MONOETILENGLICOL',
    'TRIPOLIFOSFATO DEPOTASIC': 'TRIPOLIFOSFATO DE POTASIO',
    'TRIPOLIFOSFATO DEPOTASIO': 'TRIPOLIFOSFATO DE POTASIO',
    'BIOXIDODETITANEO R-902': 'DIOXIDO DE TITANIO R-902',
    'BIOXIDO DETITANEO': 'DIOXIDO DE TITANIO R-902',
    'BIOXIDODETITANEO': 'DIOXIDO DE TITANIO R-902',
    'BIOXIDO DE TITANEO': 'DIOXIDO DE TITANIO R-902',
    'ANTITERRA204': 'ANTITERRA 204',
    'ANTI TERRA 204': 'ANTITERRA 204',
    'ANTI TERRA  204': 'ANTITERRA 204',
    'BERMOCOL481FQ': 'BERMOCOLL 481 FQ',
    'BERMOCOLL481FQ': 'BERMOCOLL 481 FQ',
    'BERMOCOLL481-FQ': 'BERMOCOLL 481 FQ',
    'EXXOLD-40': 'EXXOL D-40',
    'GOMADEXHATAN': 'GOMA DE XHATAN',
    'LECITINADESOYA': 'LECITINA DE SOYA',
    'LECITINA DESOYA': 'LECITINA DE SOYA',
    'NATROSOLTILOSE': 'NATROSOL TILOSE',
    'PARAFINACLORADAS-52': 'PARAFINA CLORADA S-52',
    'PARAFINA CLORADAS-52': 'PARAFINA CLORADA S-52',
    'PIGMENTOROJO OXIDO': 'PIGMENTO ROJO OXIDO',
    'PIGMENTO AMARILLLOOXIDO': 'PIGMENTO AMARILLLO OXIDO',
    'PLIOWAYE-CH': 'PLIOWAY E-CH',
    'PLIOWAYEC-1': 'PLIOWAY EC-1',
    'SOLUCIONDEPLIOWAY E-CH': 'SOLUCION DE PLIOWAY E-CH',
    'SOLUCION DE PLIOWAY E-CH': 'SOLUCION DE PLIOWAY E-CH',
    'SOLUCIONDERESINA': 'SOLUCION DE RESINA',
    'SKEN M-8': 'SKEN M-8',
    'TIXOGELEZ-100': 'TIXO GEL EZ-100',
    'TIXO GELEZ-100': 'TIXO GEL EZ-100',
    'AEROSIL200': 'AEROSIL 200',
    'SOLUCION DEAEROSIL200': 'SOLUCION DE AEROSIL 200',
    'AZULDEFTALOZANINA': 'AZUL DE FTALOZANINA',
    'VERDECROMO': 'VERDE CROMO',
    'TINTANEGRA': 'TINTA NEGRA',
    'TINTAAZUL': 'TINTA AZUL',
    'BLANCOFIJO MICRO': 'BLANCO FIJO MICRO',
    # variantes de resinas del entrenamiento 3 (doc 22 CHISA PLUS): se unifican a dos canónicos
    'RESINA QE-220S': 'RESINA QE-220S',
    'RESINA QE 220 S(W-394)': 'RESINA QE-220S',
    'RESINA QE220-S': 'RESINA QE-220S',
    'RESINA QE-2383': 'RESINA QE-2383 W-595',
    'RESINA QE2383': 'RESINA QE-2383 W-595',
    'RESINA QE2383(W-595)': 'RESINA QE-2383 W-595',
    'RESINA QE 2383(W-595)': 'RESINA QE-2383 W-595',
}

# Componentes que son colores y exigen criterio humano (no_map directo)
COLORES = {'BLANCO', 'NEGRO', 'AZUL', 'ROJO', 'VERDE', 'AMARILLO', 'NARANJA', 'NEGRO OXIDO',
           'ROJO OXIDO', 'AMARILLO OXIDO', 'VERDE CROMO', 'ROJO CARMIN'}

# Presentaciones que la lista de precios pega al final del nombre ("PINTUFLEXCUBETA").
PRESENTACIONES = ['CUBETAS', 'CUBETA', 'GALONES', 'GALON', 'LITROS', 'LITRO', 'LTS', 'LT',
                  'TAMBOR', 'TINETA', 'PIEZAS', 'PIEZA', 'BULTO', 'SACO', 'ROLLO', 'BOTELLA',
                  'KIT', 'ENVASE']

# Pares que se ven iguales en la ficha pero apuntan a insumos distintos en BD.
# No se fusionan: solo se anotan para que el orquestador decida.
EQUIVALENCIAS_SUGERIDAS = [('AGUA', 'RESINABASEAGUASBRLATEX')]


def separar_presentacion(nombre):
    """Separa la presentación pegada al nombre de una fila de la lista de precios.

    'PINTUFLEXCUBETA' -> ('PINTUFLEX', 'CUBETA') · 'SELLADOR ACRILICO GALON' -> (..., 'GALON')
    """
    s = (nombre or '').strip()
    for p in sorted(PRESENTACIONES, key=len, reverse=True):
        if len(s) > len(p) + 2 and clave(s).endswith(clave(p)):
            return s[:-len(p)].strip(' .,-'), p
    return s, None


def sin_acentos(txt):
    return ''.join(c for c in unicodedata.normalize('NFD', str(txt)) if unicodedata.category(c) != 'Mn')


def clave(txt):
    return re.sub(r'[^A-Z0-9]', '', sin_acentos(txt).upper())


def canonico(nombre):
    """Aplica la tabla de sinónimos (por clave) si existe."""
    k = clave(nombre)
    for orig, dest in SINONIMOS.items():
        if clave(orig) == k:
            return dest
    return nombre


def similitud(a, b):
    return SequenceMatcher(None, a, b).ratio()


def es_canonico(codigo):
    return not str(codigo or '').upper().startswith('IMP-')


def cargar(ruta):
    with open(ruta, encoding='utf-8') as fh:
        return json.load(fh)


# ─────────────────────────────────────────────────────────────────── carga de datos

productos = cargar(os.path.join(DUMP, 'productos.json'))
insumos = cargar(os.path.join(DUMP, 'insumos.json'))
formulaciones = cargar(os.path.join(DUMP, 'formulaciones.json'))
detalle = cargar(os.path.join(DUMP, 'detalle_formulacion.json'))

for p in productos:
    p['clave'] = clave(p['nombre'])
for i in insumos:
    i['clave'] = clave(i['nombre_tecnico'])
insumo_por_id = {i['id']: i for i in insumos}
producto_por_id = {p['id']: p for p in productos}

detalle_por_form = defaultdict(list)
for d in detalle:
    detalle_por_form[d['formulacion_id']].append(d)
form_por_producto = defaultdict(list)
for f in formulaciones:
    form_por_producto[f['producto_id']].append(f)
for pid in form_por_producto:
    form_por_producto[pid].sort(key=lambda f: int(float(f['version'] or 0)))

dupes_insumos = defaultdict(list)
for i in insumos:
    dupes_insumos[i['clave']].append(i)
dupes_insumos = {k: v for k, v in dupes_insumos.items() if len(v) > 1}

con_formulacion = {pid for pid, fs in form_por_producto.items() if fs}
semielaborados = [p for p in productos if p['id'] in con_formulacion]

print(f'BD: {len(productos)} productos · {len(semielaborados)} con formulación · '
      f'{len(insumos)} insumos · {len(formulaciones)} formulaciones · {len(detalle)} componentes')
print(f'Duplicados de insumos por clave normalizada: {len(dupes_insumos)}')

documentos = []
for archivo in sorted(os.listdir(PARSED)):
    if archivo.endswith('.json'):          # batch_*.json + lista_precios.json
        cargados = cargar(os.path.join(PARSED, archivo))
        documentos.extend(cargados if isinstance(cargados, list) else [cargados])
formularios = [d for d in documentos if str(d.get('tipo_documento', '')).startswith('formulacion')]
lista_precios = [d for d in documentos if d.get('tipo_documento') == 'lista_precios']
print(f'OCR: {len(documentos)} documentos ({len(formularios)} fichas de formulación · '
      f'{len(lista_precios)} listas de precios)')


# ─────────────────────────────────────────────────────────── resolución de nombres

def etiqueta(c):
    """Nombre legible de un candidato: insumos usan nombre_tecnico, productos usan nombre."""
    return c.get('nombre_tecnico') or c.get('nombre') or f'#{c.get("id")}'


def resolver(nombre, candidatos):
    """Devuelve (candidato, criterio, confianza, avisos) para un nombre OCR."""
    nombre_c = canonico(nombre)
    k = clave(nombre_c)
    avisos = []
    if k != clave(nombre):
        avisos.append(f'sinónimo aplicado: "{nombre}" → "{nombre_c}"')
    if not k:
        return None, 'vacio', 0.0, avisos

    exactos = [c for c in candidatos if c['clave'] == k]
    if exactos:
        canonicos = [c for c in exactos if es_canonico(c.get('codigo'))]
        elegido = (canonicos or exactos)[0]
        otros = [c for c in exactos if c['id'] != elegido['id']]
        if otros:
            avisos.append('duplicados en catálogo: ' +
                          ', '.join(f'#{c["id"]} [{c["codigo"]}]' for c in otros))
        return elegido, 'nombre_exacto', 1.0, avisos

    # el nombre OCR coincide con el código del catálogo (p.ej. referencia de la ficha)
    por_codigo = [c for c in candidatos if clave(c.get('codigo')) == k]
    if por_codigo:
        return por_codigo[0], 'codigo', 0.98, avisos
    por_alias = [c for c in candidatos
                 if (c.get('alias') or '').strip() and clave(c['alias']) == k]
    if por_alias:
        return por_alias[0], 'alias', 0.95, avisos

    # contención de subcadenas (cubre ligaduras y nombres compuestos)
    cont = []
    for c in candidatos:
        ck = c['clave']
        if len(k) >= MIN_SUBCADENA and k in ck:
            cont.append((len(ck) - len(k), c))
        elif len(ck) >= MIN_SUBCADENA and ck in k:
            cont.append((len(k) - len(ck), c))
    if cont:
        cont.sort(key=lambda t: t[0])
        mejor_len, mejor = cont[0]
        empatados = [c for d, c in cont if d == mejor_len]
        claves_empatadas = {c['clave'] for c in empatados}
        if len(claves_empatadas) == 1:
            elegido = empatados[0]
            avisos.append('coincidencia parcial (contención de nombre): revisar')
            return elegido, 'contencion', 0.9, avisos
        avisos.append('ambigüedad: coincide con ' +
                      ' / '.join(sorted({f'#{c["id"]} {etiqueta(c)[:28]}' for c in empatados})))
        return None, 'ambiguo', 0.5, avisos

    mejor, mejor_r = None, 0.0
    for c in candidatos:
        r = similitud(k, c['clave'])
        if r > mejor_r:
            mejor, mejor_r = c, r
    if mejor and mejor_r >= UMBRAL_FUZZY:
        avisos.append('coincidencia difusa: revisar el nombre contra el catálogo')
        return mejor, 'nombre_fuzzy', round(mejor_r, 3), avisos
    if mejor:
        avisos.append(f'candidato más cercano: #{mejor["id"]} "{etiqueta(mejor)}" ({mejor_r:.2f})')
    return None, 'sin_match', round(mejor_r, 3), avisos


# ──────────────────────────────────────────────────────── 1. manifiesto de productos

nombres = []
for d in formularios:
    nombres.append({'ocr_nombre': d['producto']['nombre'], 'origen': d['imagen'],
                    'ref_ocr': d['producto'].get('codigo_o_referencia'), 'fuente': 'ficha',
                    'presentacion': None, 'extra': None})
for doc in documentos:
    tipo_doc = doc.get('tipo_documento')
    if tipo_doc not in ('lista_precios', 'rendimientos'):
        continue
    for fila in doc.get('filas', []):
        if not fila.get('producto'):
            continue
        if tipo_doc == 'lista_precios':
            limpio, pres = separar_presentacion(fila['producto'])
            nombres.append({'ocr_nombre': limpio, 'origen': doc['imagen'], 'ref_ocr': None,
                            'fuente': 'lista_precios', 'presentacion': pres,
                            'extra': {'nombre_original': fila['producto'],
                                      'precio_sin_iva': fila.get('precio_sin_iva'),
                                      'rendimiento_teorico': fila.get('rendimiento_teorico'),
                                      'categoria': fila.get('categoria')}})
        else:
            nombres.append({'ocr_nombre': fila['producto'], 'origen': doc['imagen'],
                            'ref_ocr': fila.get('presentacion'), 'fuente': 'rendimientos',
                            'presentacion': None, 'extra': None})

# agrupa las presentaciones de un mismo producto (GALON / CUBETA S...) en una sola entrada
PRIORIDAD_FUENTE = {'ficha': 0, 'lista_precios': 1, 'rendimientos': 2}
agrupados, orden = {}, []
for n in nombres:
    k = clave(n['ocr_nombre'])
    if not k:
        continue
    if k not in agrupados:
        agrupados[k] = dict(n, presentaciones=[], origenes=[n['origen']])
        orden.append(k)
    else:
        a = agrupados[k]
        a['origenes'].append(n['origen'])
        if PRIORIDAD_FUENTE[n['fuente']] < PRIORIDAD_FUENTE[a['fuente']]:
            a['fuente'], a['ref_ocr'] = n['fuente'], n['ref_ocr']
    if n['presentacion'] or n['extra']:
        agrupados[k]['presentaciones'].append({'presentacion': n['presentacion'],
                                              'origen': n['origen'], **(n['extra'] or {})})

unicos = [agrupados[k] for k in orden]

match_productos = []
for n in unicos:
    cand, criterio, conf, avisos = resolver(n['ocr_nombre'], productos)
    if not cand and n['ref_ocr']:
        ref = clave(n['ref_ocr'])
        for pid_ref in sorted(form_por_producto, key=lambda x: int(float(x))):
            p = producto_por_id[pid_ref]
            if ref and len(ref) >= 3 and (ref in p['clave'] or ref == p['clave']):
                cand, criterio, conf = p, 'referencia_ocr', 0.9
                avisos.append(f'resuelto por la referencia de la ficha: {n["ref_ocr"]}')
                break
    avisos_previos: list = []
    if not cand:
        if n['fuente'] == 'ficha':
            accion = 'crear'
            avisos.append('no existe en el catálogo: es un producto de ficha, se crea junto con su '
                          'formulación (validar el nombre con el negocio)')
        else:
            accion = 'requiere_revision'
            avisos.append(f'no existe en el catálogo y solo aparece en {n["fuente"]}: decidir si es '
                          f'producto, insumo o si no debe existir en el ERP')
    elif criterio == 'contencion' or criterio == 'nombre_fuzzy' or conf < 0.85:
        accion = 'requiere_revision'
        avisos_previos.append('el match no es exacto: confirmar antes de usarlo')
    elif cand['estatus'] != 'Activo':
        accion = 'requiere_revision'
        avisos_previos.append(f'el producto está en estatus {cand["estatus"]}')
    else:
        accion = 'usar_existente'
    if cand:
        avisos.insert(0, f'#{cand["id"]} [{cand["codigo"]}] {cand["nombre"]} · {cand["estatus"]}')
        avisos = avisos_previos + avisos
    match_productos.append({
        'ocr_nombre': n['ocr_nombre'],
        'producto_id': cand['id'] if cand else None,
        'producto_nombre_bd': cand['nombre'] if cand else None,
        'match_criterio': criterio,
        'confianza': round(conf, 3),
        'accion': accion,
        'fuente': n['fuente'],
        'origen': n['origen'],
        'origenes': sorted(set(n['origenes']), key=n['origenes'].index),
        'presentaciones': n['presentaciones'],
        'notas': ' · '.join(avisos),
    })

mapa_producto = {clave(m['ocr_nombre']): m for m in match_productos}

# ──────────────────────────────────────────────── 2. manifiesto de componentes

componentes = defaultdict(list)
for d in formularios:
    for c in d.get('componentes', []):
        if (c.get('nombre') or '').strip():
            componentes[clave(c['nombre'])].append({'nombre': c['nombre'], 'imagen': d['imagen']})
    for v in d.get('variantes', []) or []:
        if v.get('color'):
            componentes[clave(v['color'])].append({'nombre': v['color'], 'imagen': d['imagen']})

match_componentes = []
for k, usos in sorted(componentes.items()):
    nombre = usos[0]['nombre']
    imagenes = sorted({u['imagen'] for u in usos})
    avisos = []

    # color solo (BLANCO/NEGRO/...): exige criterio humano
    if clave(nombre) in {clave(c) for c in COLORES} and len(clave(nombre)) <= 14:
        candidatos_color = [i for i in insumos if k in i['clave'] or i['clave'] in k]
        avisos.append('nombre de color: definir con el negocio a qué insumo/producto corresponde')
        if candidatos_color:
            avisos.append('candidatos: ' + ' / '.join(
                f'#{i["id"]} {i["nombre_tecnico"]}' for i in candidatos_color[:5]))
        match_componentes.append({
            'ocr_nombre': nombre, 'nombre_canonico': canonico(nombre), 'tipo': 'sin_definir',
            'insumo_id': None, 'producto_id': None, 'match_criterio': 'color',
            'confianza': 0.3, 'accion': 'requiere_revision', 'apariciones': len(imagenes),
            'imagenes': imagenes, 'notas': ' · '.join(avisos),
        })
        continue

    cand_p, crit_p, conf_p, av_p = resolver(nombre, semielaborados)
    cand_i, crit_i, conf_i, av_i = resolver(nombre, insumos)

    insumo = producto_cand = None
    if cand_p and conf_p >= UMBRAL_FUZZY_FUERTE and (not cand_i or conf_p >= conf_i):
        tipo, criterio, conf = 'Producto', crit_p, conf_p
        producto_cand = cand_p
        gemelo_insumo = next((i for i in insumos if i['clave'] == cand_p['clave']), None)
        if gemelo_insumo:
            avisos.append(f'también existe como insumo #{gemelo_insumo["id"]} '
                          f'[{gemelo_insumo["codigo"]}] (tipo {gemelo_insumo["tipo"]}): el ERP '
                          f'modela este semielaborado como producto con formulación')
        avisos += [a for a in av_p if 'candidato' not in a]
    elif cand_i:
        tipo, criterio, conf = 'Insumo', crit_i, conf_i
        insumo = cand_i
        avisos += av_i
        gemelo_producto = next((p for p in semielaborados
                                if p['clave'] == cand_i['clave']
                                or (len(cand_i['clave']) >= 8 and len(p['clave']) >= 8 and
                                    (p['clave'] in cand_i['clave'] or cand_i['clave'] in p['clave']))), None)
        if gemelo_producto:
            avisos.append(f'también existe como producto #{gemelo_producto["id"]} '
                          f'[{gemelo_producto["codigo"]}] {gemelo_producto["nombre"]}: revisar si debe ir '
                          f'como semielaborado')
    elif cand_p:
        tipo, criterio, conf = 'Producto', crit_p, conf_p
        producto_cand = cand_p
        avisos.append('semielaborado probable, coincidencia no exacta: revisar')
        avisos += [a for a in av_p if 'candidato' not in a]
    else:
        if 'ambiguo' in (crit_i, crit_p):
            tipo, criterio, conf = 'Insumo', 'ambiguo', max(conf_i, conf_p)
        else:
            tipo, criterio, conf = 'Insumo', 'sin_match', 0.0
        avisos += av_i or ['no existe en insumos ni como semielaborado']

    if criterio in ('nombre_fuzzy', 'contencion', 'ambiguo'):
        accion = 'requiere_revision'
    else:
        accion = 'vincular' if (insumo or producto_cand) else 'crear'

    if insumo and not es_canonico(insumo['codigo']):
        avisos.append(f'el insumo elegido es autogenerado ({insumo["codigo"]})')
    if insumo:
        gemelos = [x for x in dupes_insumos.get(insumo['clave'], []) if x['id'] != insumo['id']]
        if gemelos:
            avisos.append('duplicado en catálogo: ' + ', '.join(f'#{g["id"]} [{g["codigo"]}]' for g in gemelos))

    match_componentes.append({
        'ocr_nombre': nombre,
        'nombre_canonico': (insumo or producto_cand or {}).get('nombre_tecnico')
                           or (producto_cand or {}).get('nombre') or canonico(nombre),
        'tipo': tipo,
        'insumo_id': insumo['id'] if insumo else None,
        'producto_id': producto_cand['id'] if producto_cand else None,
        'match_criterio': criterio,
        'confianza': round(conf, 3),
        'accion': accion,
        'apariciones': len(imagenes),
        'imagenes': imagenes,
        'notas': ' · '.join(avisos),
    })

mapa_componente = {c['clave']: c for c in []}


def identidad_componente(nombre_ocr):
    """Devuelve la clave de identidad con la que se compara un componente del OCR."""
    m = next((c for c in match_componentes if clave(c['ocr_nombre']) == clave(nombre_ocr)), None)
    if m and m['producto_id']:
        return producto_por_id[m['producto_id']]['clave'], 'Producto', m['producto_id']
    if m and m['insumo_id']:
        return insumo_por_id[m['insumo_id']]['clave'], 'Insumo', m['insumo_id']
    return clave(canonico(nombre_ocr)), None, None


def identidad_componente_bd(c):
    """Clave de identidad de un componente de BD (normalizando sinónimos del catálogo)."""
    if c['tipo_componente'] == 'Producto' and c.get('producto_id') and c['producto_id'] in producto_por_id:
        return producto_por_id[c['producto_id']]['clave']
    if c['insumo_id'] and c['insumo_id'] in insumo_por_id:
        nombre = insumo_por_id[c['insumo_id']]['nombre_tecnico']
        return clave(canonico(nombre))
    return None


def equivalentes(k1, k2):
    """¿Dos claves de componente representan lo mismo? (exacto o contención clara)"""
    if k1 == k2:
        return True
    if len(k1) >= MIN_SUBCADENA and len(k2) >= MIN_SUBCADENA and (k1 in k2 or k2 in k1):
        return True
    return False


# ──────────────────────────────────────────── 3. comparativa contra TODAS las versiones

comparativa = []
for d in formularios:
    nombre = d['producto']['nombre']
    m = mapa_producto.get(clave(nombre))
    ocr_comps = []
    for c in d.get('componentes', []):
        if (c.get('nombre') or '').strip():
            ident, tipo_id, ref_id = identidad_componente(c['nombre'])
            ocr_comps.append({'nombre': c['nombre'], 'ident': ident, 'ref_id': ref_id,
                              'pct': float(c['porcentaje']) if c.get('porcentaje') is not None else None,
                              'kg': float(c['cantidad']) if c.get('cantidad') is not None else None})
    kg_ocr = float(d['lote']['cantidad'] or 0)
    suma_pct = sum(c['pct'] for c in ocr_comps if c['pct'] is not None)

    entrada = {
        'imagen': d['imagen'],
        'producto_ocr': nombre,
        'producto_id': m['producto_id'] if m else None,
        'confianza_match_producto': m['confianza'] if m else 0.0,
        'total_kg_ocr': kg_ocr,
        'componentes_ocr': len(ocr_comps),
        'suma_pct_ocr': round(suma_pct, 2),
        'variantes_color': len(d.get('variantes') or []),
        'confianza_ocr': d.get('confianza'),
        'dudas_ocr': d.get('dudas') or [],
        'duplicado_de': None,
        'version_que_coincide': None,
        'version_activa': None,
        'diff_componentes': [],
        'incidencias': [],
    }

    if not m or not m['producto_id']:
        entrada.update({'estado': 'producto_nuevo' if not m else 'requiere_revision',
                        'recomendacion': 'Crear el producto y su formulación (validar primero el nombre).'})
        comparativa.append(entrada)
        continue

    pid = m['producto_id']
    versiones = form_por_producto.get(pid, [])
    activa = next((f for f in versiones if f['es_activa']), None)
    entrada['version_activa'] = int(float(activa['version'])) if activa else None

    mejor = None  # (score, version, diff)
    for f in versiones:
        bd_comps = []
        for c in detalle_por_form.get(f['id'], []):
            k = identidad_componente_bd(c)
            if k:
                bd_comps.append({'nombre': (insumo_por_id.get(c['insumo_id']) or producto_por_id.get(c['producto_id']) or {}).get(
                    'nombre_tecnico') or (producto_por_id.get(c['producto_id']) or {}).get('nombre') or k,
                    'ident': k,
                    'pct': float(c['porcentaje']) if c.get('porcentaje') is not None else None,
                    'kg': float(c['cantidad']) if c.get('cantidad') is not None else None})
        usados, diff, iguales = set(), [], 0
        for oc in ocr_comps:
            par = None
            for idx, bc in enumerate(bd_comps):
                if idx in usados:
                    continue
                if equivalentes(oc['ident'], bc['ident']):
                    par = (idx, bc)
                    break
            if par:
                idx, bc = par
                usados.add(idx)
                if oc['pct'] is not None and bc['pct'] is not None and abs(oc['pct'] - bc['pct']) <= TOL_PCT:
                    iguales += 1
                    estado = 'igual'
                else:
                    estado = 'cambio'
                diff.append({'nombre': oc['nombre'], 'nombre_bd': bc['nombre'],
                             'pct_ocr': oc['pct'], 'pct_bd': bc['pct'],
                             'kg_ocr': oc['kg'], 'kg_bd': bc['kg'], 'estado': estado})
            else:
                diff.append({'nombre': oc['nombre'], 'nombre_bd': None,
                             'pct_ocr': oc['pct'], 'pct_bd': None,
                             'kg_ocr': oc['kg'], 'kg_bd': None, 'estado': 'nuevo'})
        for idx, bc in enumerate(bd_comps):
            if idx not in usados:
                diff.append({'nombre': bc['nombre'], 'nombre_bd': bc['nombre'],
                             'pct_ocr': None, 'pct_bd': bc['pct'],
                             'kg_ocr': None, 'kg_bd': bc['kg'], 'estado': 'falta_en_ocr'})
        score = iguales - sum(1 for x in diff if x['estado'] in ('nuevo', 'falta_en_ocr'))
        candidato = {'score': score, 'iguales': iguales, 'version': f, 'diff': diff,
                     'kg_bd': float(f['cantidad_producida'] or 0)}
        if mejor is None or candidato['score'] > mejor['score']:
            mejor = candidato

    if not mejor:
        entrada.update({'estado': 'sin_formulacion',
                        'recomendacion': 'El producto no tiene ninguna versión: crear V1.'})
        comparativa.append(entrada)
        continue

    f = mejor['version']
    entrada['version_que_coincide'] = int(float(f['version']))
    entrada['total_kg_bd'] = mejor['kg_bd']
    entrada['diff_componentes'] = mejor['diff']
    solo_pct = [x for x in mejor['diff'] if x['pct_ocr'] is not None and x['pct_bd'] is not None]
    identicas = solo_pct and all(abs(x['pct_ocr'] - x['pct_bd']) <= TOL_PCT for x in solo_pct)
    total_componentes = len(mejor['diff'])
    exacto = (identicas and total_componentes == len(ocr_comps)
              and not any(x['estado'] in ('nuevo', 'falta_en_ocr') for x in mejor['diff']))

    if exacto and abs(mejor['kg_bd'] - kg_ocr) < 0.5:
        entrada['estado'] = 'coincide_exacto'
        entrada['recomendacion'] = (f'Ya cargada como V{entrada["version_que_coincide"]}'
                                    f'{" (activa)" if f["es_activa"] else " (inactiva)"}: no crear nada.')
    elif exacto:
        entrada['estado'] = 'coincide_receta_difiere_lote'
        entrada['recomendacion'] = (f'Misma receta que V{entrada["version_que_coincide"]}, pero el lote de '
                                    f'referencia cambia ({mejor["kg_bd"]} kg en BD vs {kg_ocr} kg en la ficha). '
                                    f'Decidir si se actualiza el lote o se crea versión.')
    else:
        entrada['estado'] = 'difiere'
        n_cambios = sum(1 for x in mejor['diff'] if x['estado'] != 'igual')
        entrada['recomendacion'] = (f'Crear versión nueva (V{int(float(f["version"])) + 1}) con la variante de la '
                                    f'ficha: {n_cambios} línea(s) con diferencia frente a V{int(float(f["version"]))}.')
    comparativa.append(entrada)

comparativa.sort(key=lambda e: (e['estado'], e['imagen']))

# ── capturas repetidas: mismo producto y mismo lote (la carpeta trae fichas duplicadas)
huellas = defaultdict(list)
for e in comparativa:
    if e['producto_id'] is not None:
        huellas[(str(e['producto_id']), round(e['total_kg_ocr'], 3), e['componentes_ocr'])].append(e)
for grupo in huellas.values():
    if len(grupo) < 2:
        continue
    principal = grupo[0]
    firma = sorted((clave(x['nombre']), x['pct_ocr']) for x in principal['diff_componentes']
                   if x['estado'] != 'falta_en_ocr')
    for otro in grupo[1:]:
        firma_otro = sorted((clave(x['nombre']), x['pct_ocr']) for x in otro['diff_componentes']
                            if x['estado'] != 'falta_en_ocr')
        otro['duplicado_de'] = principal['imagen']
        otro['nota_duplicado'] = ('captura idéntica a la anterior: cargar una sola vez'
                                 if firma == firma_otro else
                                 'mismo producto y lote pero porcentajes distintos: revisar cuál es la vigente')

# ── pares que se ven iguales en la ficha pero apuntan a insumos distintos en la BD
for e in comparativa:
    for nombre_ocr, clave_bd in EQUIVALENCIAS_SUGERIDAS:
        nuevos = [x for x in e['diff_componentes']
                  if x['estado'] == 'nuevo' and clave(x['nombre']) == clave(nombre_ocr)]
        faltan = [x for x in e['diff_componentes']
                  if x['estado'] == 'falta_en_ocr' and clave_bd in clave(x['nombre_bd'] or '')]
        for n_, f_ in zip(nuevos, faltan):
            nota = (f'la ficha dice "{n_["nombre"]}" ({n_["pct_ocr"]}%) y la BD tiene '
                    f'"{f_["nombre_bd"]}" ({f_["pct_bd"]}%): confirmar si es el mismo material')
            n_['nota'] = f_['nota'] = nota
            e['incidencias'].append(nota)

# ─────────────────────────────────────────────────── incidencias de calidad de datos

incidencias = []
for f in formulaciones:
    comps = detalle_por_form.get(f['id'], [])
    nombre_prod = (producto_por_id.get(f['producto_id']) or {}).get('nombre', '?')
    # auto-referencia: un componente apunta al mismo producto
    for c in comps:
        if c['tipo_componente'] == 'Producto' and c.get('producto_id') == f['producto_id']:
            incidencias.append({'tipo': 'auto_referencia', 'formulacion_id': f['id'],
                                'producto': nombre_prod,
                                'detalle': f'V{f["version"]}: componente = el propio producto'})
        if c['tipo_componente'] == 'Insumo' and c.get('insumo_id'):
            ins = insumo_por_id.get(c['insumo_id'])
            if ins and 'producto_id' in f and ins['clave'] == (producto_por_id.get(f['producto_id']) or {}).get('clave'):
                incidencias.append({'tipo': 'auto_referencia_insumo', 'formulacion_id': f['id'],
                                    'producto': nombre_prod,
                                    'detalle': f'V{f["version"]}: insumo "{ins["nombre_tecnico"]}" es el producto mismo'})
    # grupos de color mezclados (más de un grupo distinto en una versión)
    grupos = {c['grupo_color'] for c in comps if c.get('grupo_color')}
    if len(grupos) > 1:
        incidencias.append({'tipo': 'grupos_mezclados', 'formulacion_id': f['id'], 'producto': nombre_prod,
                            'detalle': f'V{f["version"]}: {len(grupos)} grupos_color en una sola versión: ' +
                                       ', '.join(sorted(grupos))[:120]})
    # porcentajes que no cierran
    suma = sum(float(c['porcentaje']) for c in comps if c.get('porcentaje') is not None and not c.get('grupo_color'))
    if comps and abs(suma - 100) > 2 and not grupos:
        incidencias.append({'tipo': 'porcentaje_no_cierra', 'formulacion_id': f['id'], 'producto': nombre_prod,
                            'detalle': f'V{f["version"]}: suma de porcentajes = {suma:.2f}%'})

incidencias.sort(key=lambda x: (x['tipo'], x['formulacion_id']))


# ──────────────────────────────────────────────────────────────────── escritura

os.makedirs(MANIFIESTOS, exist_ok=True)


def escribir(nombre, data):
    with open(os.path.join(MANIFIESTOS, nombre), 'w', encoding='utf-8') as fh:
        json.dump(data, fh, ensure_ascii=False, indent=2)


escribir('productos_match.json', match_productos)
escribir('insumos_match.json', match_componentes)
escribir('comparativa.json', comparativa)
escribir('incidencias_datos.json', incidencias)

estados = defaultdict(list)
for e in comparativa:
    estados[e['estado']].append(e)
lineas = ['# Comparativa — fichas del entrenamiento 3 vs BD', '',
          'Generado por `doc/entrenamiento_3/tools/fase1_matching.py`. '
          f'{len(comparativa)} fichas analizadas contra **todas** las versiones de cada producto.', '',
          '## Resumen por estado', '', '| Estado | Fichas |', '|---|---|']
for estado, items in sorted(estados.items()):
    lineas.append(f'| `{estado}` | {len(items)} |')
lineas += ['', '## Detalle por ficha', '',
           '| Imagen | Producto OCR | Producto BD | kg ficha | kg BD | Versión | Suma % ficha | Estado | Líneas con diferencia | Recomendación |',
           '|---|---|---|---|---|---|---|---|---|---|']
for e in comparativa:
    difs = sum(1 for x in e['diff_componentes'] if x['estado'] != 'igual')
    lineas.append('| {} | {} | {} | {} | {} | {} | {} | `{}` | {} | {} |'.format(
        e['imagen'], e['producto_ocr'], e.get('producto_id') or '—', e['total_kg_ocr'],
        e.get('total_kg_bd') or '—', e.get('version_que_coincide') or '—', e.get('suma_pct_ocr', '—'),
        e['estado'],
        f'{difs} de {len(e["diff_componentes"])}' if e['diff_componentes'] else '—',
        e['recomendacion'] + (f' · duplicado de {e["duplicado_de"]}: {e["nota_duplicado"]}'
                              if e.get('duplicado_de') else '')))
lineas += ['', '## Diferencias línea a línea', '']
for e in comparativa:
    pend = [x for x in e['diff_componentes'] if x['estado'] != 'igual']
    if not pend:
        continue
    lineas += [f'### {e["imagen"]} — {e["producto_ocr"]} (`{e["estado"]}` vs V{e.get("version_que_coincide")})', '',
               '| Componente ficha | Componente BD | % ficha | % BD | kg ficha | kg BD | Estado | Nota |',
               '|---|---|---|---|---|---|---|---|']
    for x in pend:
        lineas.append('| {} | {} | {} | {} | {} | {} | `{}` | {} |'.format(
            x['nombre'], x.get('nombre_bd') or '—',
            x['pct_ocr'] if x['pct_ocr'] is not None else '—',
            x['pct_bd'] if x['pct_bd'] is not None else '—',
            x['kg_ocr'] if x['kg_ocr'] is not None else '—',
            x['kg_bd'] if x['kg_bd'] is not None else '—', x['estado'],
            x.get('nota') or ''))
    lineas.append('')
with open(os.path.join(MANIFIESTOS, 'comparativa.md'), 'w', encoding='utf-8') as fh:
    fh.write('\n'.join(lineas) + '\n')

# ─────────────────────────────────────────────────────────────────────── reporte

print('\n' + '=' * 100)
print('PRODUCTOS:', len(match_productos), '→',
      f"usar_existente={sum(1 for m in match_productos if m['accion'] == 'usar_existente')}",
      f"crear={sum(1 for m in match_productos if m['accion'] == 'crear')}",
      f"requiere_revision={sum(1 for m in match_productos if m['accion'] == 'requiere_revision')}")
print('COMPONENTES:', len(match_componentes), '→',
      f"vincular={sum(1 for c in match_componentes if c['accion'] == 'vincular')}",
      f"crear={sum(1 for c in match_componentes if c['accion'] == 'crear')}",
      f"requiere_revision={sum(1 for c in match_componentes if c['accion'] == 'requiere_revision')}",
      f"| como Producto={sum(1 for c in match_componentes if c['tipo'] == 'Producto')}")
print('FICHAS:', dict((k, len(v)) for k, v in sorted(estados.items())))
print('INCIDENCIAS DE DATOS:', dict((t, sum(1 for i in incidencias if i['tipo'] == t))
                                    for t in sorted({i['tipo'] for i in incidencias})))
print('=' * 100)

print('\n--- PRODUCTOS a crear / dudosos ---')
for m in match_productos:
    if m['accion'] != 'usar_existente':
        print(f"  [{m['accion']:<17}] {m['fuente']:<13} {m['ocr_nombre'][:40]:<40} {m['notas'][:78]}")

print('\n--- FICHAS con suma de porcentajes != 100 ---')
for e in comparativa:
    if e['estado'] != 'requiere_revision' and abs(e.get('suma_pct_ocr', 100) - 100) > 0.5:
        print(f"  {e['imagen']:<28} {e['producto_ocr'][:32]:<32} suma={e['suma_pct_ocr']}% "
              f"(variantes de color={e['variantes_color']}, dudas OCR={len(e['dudas_ocr'])})")

print('\n--- FICHAS duplicadas ---')
for e in comparativa:
    if e.get('duplicado_de'):
        print(f"  {e['imagen']:<28} duplica a {e['duplicado_de']:<28} {e['nota_duplicado']}")

print('\n--- COMPONENTES a crear ---')
for c in match_componentes:
    if c['accion'] == 'crear':
        print(f"  {c['ocr_nombre'][:44]:<44} {c['notas'][:90]}")

print('\n--- COMPONENTES dudosos ---')
for c in match_componentes:
    if c['accion'] == 'requiere_revision':
        print(f"  {c['ocr_nombre'][:38]:<38} -> {(c['nombre_canonico'] or '—')[:30]:<30} ({c['tipo']:<8}) {c['notas'][:95]}")

print('\n--- SEMIELABORADOS (componentes resueltos a producto) ---')
for c in match_componentes:
    if c['tipo'] == 'Producto':
        print(f"  {c['ocr_nombre'][:38]:<38} -> #{c['producto_id']} {c['nombre_canonico']} ({c['accion']}) {c['notas'][:60]}")
