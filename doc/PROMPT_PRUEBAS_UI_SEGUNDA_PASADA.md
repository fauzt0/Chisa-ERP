# PROMPT — Pruebas UI, segunda pasada (Iteración 3)

> Listo para pegar en un chat nuevo de un agente con navegador (Composer 2.5).
> Autorizaciones vigentes: todo el ciclo TEST-QA (obra, ventas/POS, pesaje, OC, nómina), **excepto enviar
> correos/WhatsApp**. Credenciales con permisos amplios: las que ya compartió el humano.
> Punto de partida: `doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md` (1ª pasada: 18 ✅ / 6 ⚠️ / 0 ❌ / 28 SKIP).

```text
Eres un QA manual con navegador sobre el ERP de CHISA Recubrimientos (CodeIgniter 3 + MySQL,
PRODUCCIÓN EN VIVO). Misión: SEGUNDA PASADA de validación de UI de la iteración 3 antes del PR
`iteracion-3 → main`. No escribas código, no toques git y no uses la BD directamente: solo UI,
devtools y documentación.

ENTORNO Y ACCESO
- URL: https://erp.chisarecubrimientos.com.mx
- Usa las credenciales con permisos amplios que ya te compartió el humano (no la cuenta demo).
- Commit en prueba: 1bdec68 (rama iteracion-3; es la copia que sirve el sitio).
- El servidor corre ENVIRONMENT=development: los avisos "A PHP Error was encountered /
  Severity: 8192 / Creation of dynamic property …" son RUIDO preexistente de PHP 8.3, NO bugs.
  Error real = "Fatal error", "Uncaught", HTTP 500, pantalla en blanco o acción que no responde.
- No hay 2FA en este entorno.

PUNTO DE PARTIDA
- Primera pasada ya documentada en doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md:
  18 ✅ / 6 ⚠️ / 0 ❌ / 28 SKIP, sin datos TEST-QA creados.
- BUG-UI-01 (`hospital ROCA` → 0 resultados) y BUG-UI-03 (buscar `475` → 0) NO son bugs: la
  búsqueda de Productos es literal (substring) sobre código/nombre/alias/categoría; no es por
  tokens ni por ID interno. Verifícalo con tokens sueltos y con nombre/código.
- BUG-UI-02 (tab Entregas en "Cargando..."): el endpoint ya fue sondeado del lado servidor
  (doc/entrenamiento_3/manifiestos/bug_ui_02_probe_obra2.txt) y responde success:true con 1
  producto para OB-00002. Confírmalo o descártalo con evidencia de Network.

AUTORIZACIÓN (vigente, leída por el humano)
- TODO el ciclo TEST-QA está autorizado: obra completa (crear → aprobar → preórdenes → entregas),
  cotización → confirmación → cobro/entrega TEST, pesaje/completada E2E, OC/preorden/recepción,
  nómina de periodo futuro TEST, altas y ediciones TEST.
- Lo ÚNICO prohibido: enviar correos/WhatsApp (E8 solo preview; todo botón "Enviar" → SKIP
  "requiere autorización humana"), CFDI real, pagos/cobros de montos reales, importaciones
  masivas, modificar catálogos/precios existentes, activar/desactivar formulaciones, y tocar
  datos reales (OB-00001/OB-00002, OVs reales, nómina real, clientes/proveedores reales).

REGLAS DE TRABAJO
1. Prefijo `TEST-QA-` en todo lo que crees; cantidades mínimas (1 línea / 1 cubeta / 1 registro).
2. Prohibido DROP/TRUNCATE/DELETE. La limpieza es por UI (cancelar / dar de baja). Si algo no se
   puede limpiar por UI, NO lo crees: márcalo SKIP con la razón.
3. No cotices los productos #475–#503 (28 de 29 con precio NULL); solo valida que sean
   visibles/buscables en catálogo.
4. Devtools abiertas (Network + Console) toda la sesión. Todo hallazgo se documenta con URL,
   status, Content-Type, tiempo y primeras líneas del body + captura.
5. Capturas en disco: doc/entrenamiento_3/evidencias_ui/ (permisos ya verificados), nombre
   `<ID>-<fecha>-<descriptor>.png`. Al final: `ls -la doc/entrenamiento_3/evidencias_ui/` y pega
   la salida en el informe.
6. Un bloque a la vez; si un 500/pantalla en blanco bloquea, detén ese bloque, documenta y sigue.

ALCANCE — EJECUTA EN ESTE ORDEN

0) Preparación
   - Re-smoke de login, dashboard por permisos y toggle de tema.
   - Anota usuario, hora y commit en la cabecera del informe.

1) BUG-UI-02 — confirmar/descartar (prioridad)
   - /obras/Obras/detalle/2 → pestaña Entregas; espera 15 s.
   - Mide en Network la petición obras/Obras/get_entregas_obra_ajax?obra_id=2 (status, tiempo,
     body) y repite con recarga dura (Ctrl+Shift+R).
   - Esperado: tabla "Estado por producto" con CHISA GLASS MICRO (Solicitado 2.00, Entregado 0.00,
     Pendiente 2.00, Cubeta) + mensaje "Aún no hay entregas registradas…".
   - Si carga en <2 s: marca BUG-UI-02 NO REPRODUCIBLE con la captura. Si vuelve a colgarse,
     captura Network (timing/status/body) + Console.

2) A3 y A4 (⚠️ de la primera pasada)
   - A3: /ventas/Clientes → dispara Excel y la vista Imprimir; verifica descarga/vista sin error.
   - A4: crea `TEST-QA-CLIENTE-01` (datos mínimos válidos) y edítalo; al final cancélalo o
     desactívalo por UI y anota el ID. Si no se puede desactivar, repórtalo y déjalo identificado.

3) Obras C2–C5 (ciclo completo, autorizado)
   - Obra `TEST-QA-UI-OBRA-01`: producto SIN rendimiento → debe BLOQUEAR (no calcular con 1.0, no
     generar preorden); luego producto CON m² → kg/cubetas coherentes.
   - Aprobar → preorden origen=obra; re-aprobar → NO debe duplicar.
   - Pestaña Entregas: registrar 1 entrega desde Almacén (cantidad mínima) → verifica estado/%.
   - PDF resumen: previsualiza las 5 páginas.
   - Limpieza: cancela entrega/OV/preórdenes TEST y da de baja la obra. Lista folios.

4) Producción
   - D1: busca `hospital` y `ROCA` por separado. D1b: busca `VITROGLASS` y `CHISA MAR` (deben salir
     #475 y #495) y confirma que la búsqueda es por nombre/código/alias, no por ID.
   - D2: formulación de #204 SOLUCION DE AEROSIL 200 → debe verse V5 (form#968) activa con la línea
     AEROSIL 200 (#118) al 12.14%.
   - D7: /produccion/Lotes/consultar + etiqueta de un lote existente (solo lectura).
   - D4–D6 (autorizado): 1 cubeta TEST → Completada BLOQUEADA sin pesaje; confirmar pesaje
     (PESAJE-*), insumos bajan una vez; segundo pesaje debe fallar; completar → lote + entrada PT
     (insumos no bajan otra vez). Antes de confirmar, verifica que exista forma de cancelar/revertir
     por UI; si no existe, marca SKIP y repórtalo. Documenta folios y limpieza.

5) Ventas / POS
   - B4: cotización TEST con producto fabricado → 0 preorden. B5: confirmar → preorden Pendiente
     origen=venta solo si faltantes. B6: cobrar/entregar solo el TEST (stock producto baja, insumos
     NO). B7: /ventas/Descuentos CRUD mínimo. B8: /ventas/ObrasVentas listado + detalle.
   - Limpieza: cancela cotización/OV/entrega TEST. Lista folios.

6) Compras
   - E3: OC TEST-QA 1 línea → borrador + PDF (valida subtotal/IVA/total). E5: preorden TEST →
     autorizar → OC generada (sin duplicar). Recepción parcial del TEST si aplica.
   - E8: SOLO preview de correo/WhatsApp; NO enviar (SKIP "requiere autorización").
   - Limpieza: cancela OC/preorden/recepción TEST. Lista folios.

7) Nómina (periodo FUTURO TEST-QA; no pagar)
   - G2–G7: nueva semanal futura → Borrador ($0) → Calcular (valida sueldo = diario×días en 1
     empleado) → recalcular (no duplica) → Comidas/HE inline → restaurar → Cancelar (motivo ≥10).
   - G9: verifica la campana de nómina por vencer.

8) Móvil 390×844
   - Pasada rápida: Clientes, Órdenes, POS, Obras detalle (incl. Entregas), catálogo Producción,
     RH listado.

ENTREGABLE
1. Actualiza doc/CHECKLIST_PRUEBAS_UI_ITERACION3.md agregando "§ Segunda pasada" con:
   - Casos re-ejecutados (ID | Acción | Esperado | Resultado ✅/⚠️/❌/SKIP | Evidencia | Observación).
   - Veredicto final de BUG-UI-01/02/03 (01 y 03 = no bug por diseño de búsqueda).
   - Bugs nuevos: ID, módulo, severidad, pasos, esperado vs real, evidencia.
   - Capturas en disco (salida del `ls`) y datos TEST-QA creados con su limpieza (folio/ID + estatus).
2. En el chat: resumen de máximo 10 líneas + los ❌/⚠️ con su evidencia.
3. NO hagas commits. No cuentes como bugs: deprecations PHP 8.3, #475–#503 sin precio,
   formulaciones nuevas inactivas, residuos OV-TEST-001 / OV-2026-0004 / cliente "Empresa de
   Prueba S.A.".
```
