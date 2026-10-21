# Verificación de correcciones — ERP CHISA (21/10/2026, ~07:10 CDMX)

| # | Prueba | Resultado | Evidencia |
|---|---|---|---|
| 1 | Raíz / muestra login | OMITIDO | Indicado por Fausto: seguirá mostrando la página de CodeIgniter. |
| 2 | Entrenamiento de materiales, lápiz de RES-002 abre modal | FALLA | El clic no abre el modal ni manda petición. No hay error en consola. `get_fila_ajax?id=3` sí responde un JSON correcto, así que la falla está en el JS del cliente. No se pudo verificar que Tipo sea solo lectura. |
| 3 | Inicio, "Ver" de orden reciente sin error PHP | OK | OV-2026-0011 abre /produccion/Dashboard/detalle/orden_venta/30 sin error ni rutas del servidor. |
| 4 | POS: GLASS, Variantes y matraz | OK | "Variantes" solo aparece en CHISA GLASS MICRO (PROD-0001) y MICRO COLOR 2 Beige (PROD-0002), y no en CA-1018. El matraz abre Formulación sin agregar nada al ticket, que sigue en $0.00. |
| 5 | Consulta: ROMACO con solo aplicaciones, CCO-2026-0003 por $319,789.28 | NO VERIFICABLE (dato del servidor) | Sale "Sin resultados" y Cotizaciones de obra está vacía. CCO-2026-0003, CCO-2026-0417 y ROMACO no existen en este servidor. |
| 6 | Historial de formulaciones: columna Cliente y v2 inactiva sin cliente | OK | La columna Cliente existe. BASE-ORGANICA-NARANJA v2 (id 957) aparece con Activa=No y "Sin cliente"; no hubo que quitar nada. |

Defecto menor: en Consulta, si solo se llena el campo cliente sin texto libre, aparece "Escribe un cliente, un folio…".
Datos alterados: ninguno.
