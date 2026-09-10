#!/usr/bin/env python3
"""
TEMPORAL — OCR de las imágenes de entrenamiento (RapidOCR local).
Uso: PYTHONPATH=/home/admin/domains/erp.chisarecubrimientos.com.mx/.ocr-tmp \
     python3 doc/_tmp_ocr.py <imagen> [imagen2 ...]
Salida TSV: y \t x \t texto  (ordenado arriba→abajo, izquierda→derecha)
"""
import sys
from rapidocr_onnxruntime import RapidOCR

engine = RapidOCR()

for path in sys.argv[1:]:
    print(f"===== FILE: {path} =====")
    result, _ = engine(path)
    if not result:
        print("(sin texto detectado)")
        continue
    rows = []
    for box, text, score in result:
        xs = [p[0] for p in box]
        ys = [p[1] for p in box]
        rows.append((min(ys), min(xs), text.strip(), score))
    rows.sort(key=lambda r: (round(r[0] / 8), r[1]))
    for y, x, text, score in rows:
        if text:
            print(f"{y:6.0f}\t{x:6.0f}\t{text}")
    print()
