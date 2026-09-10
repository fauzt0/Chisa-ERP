#!/usr/bin/env python3
"""
TEMPORAL — OCR por lotes + reconstrucción de líneas (RapidOCR local).
Uso:
  PYTHONPATH=/home/admin/domains/erp.chisarecubrimientos.com.mx/.ocr-tmp \
  python3 doc/_tmp_ocr2.py <carpeta_salida> <imagen> [imagen2 ...]

Salida:
  <carpeta_salida>/<nombre>.txt  -> líneas reconstruidas:  y=<y> :: [x]texto | [x]texto ...
  <carpeta_salida>/<nombre>.tsv  -> fragmentos crudos: y \t x \t texto
"""
import os
import sys
from rapidocr_onnxruntime import RapidOCR

engine = RapidOCR()
outdir = sys.argv[1]
os.makedirs(outdir, exist_ok=True)


def reconstruir(frags):
    """Agrupa fragmentos en líneas por cercanía vertical y los junta por x."""
    frags = sorted(frags, key=lambda f: f[0])
    lineas = []
    for y, x, text in frags:
        if lineas and abs(y - lineas[-1]['y']) <= 0.6 * max(lineas[-1]['h'], 12):
            lineas[-1]['items'].append((x, text))
            lineas[-1]['y'] = (lineas[-1]['y'] * (len(lineas[-1]['items']) - 1) + y) / len(lineas[-1]['items'])
        else:
            lineas.append({'y': y, 'h': max(12, min(30, 24)), 'items': [(x, text)]})
    salida = []
    for ln in lineas:
        items = sorted(ln['items'], key=lambda i: i[0])
        partes = []
        x_prev = None
        for x, t in items:
            if x_prev is not None and (x - x_prev) > 55:
                partes.append('|')
            partes.append(t)
            x_prev = x
        salida.append((ln['y'], ' '.join(partes)))
    return salida


for path in sys.argv[2:]:
    nombre = os.path.splitext(os.path.basename(path))[0]
    print(f"--- {nombre}", flush=True)
    result, _ = engine(path)
    frags = []
    if result:
        for box, text, score in result:
            text = (text or '').strip()
            if not text:
                continue
            xs = [p[0] for p in box]
            ys = [p[1] for p in box]
            frags.append((min(ys), min(xs), text))
    with open(os.path.join(outdir, nombre + '.tsv'), 'w', encoding='utf-8') as fh:
        for y, x, t in sorted(frags, key=lambda f: (f[0], f[1])):
            fh.write(f"{y:.0f}\t{x:.0f}\t{t}\n")
    with open(os.path.join(outdir, nombre + '.txt'), 'w', encoding='utf-8') as fh:
        for y, linea in reconstruir(frags):
            fh.write(f"y={y:5.0f} :: {linea}\n")
    print(f"    fragmentos: {len(frags)}")
