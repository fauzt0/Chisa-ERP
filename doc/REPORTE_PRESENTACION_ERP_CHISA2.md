# Reporte de pruebas de presentación (ERP CHISA)
Fecha: 21/10/2026 (hora CDMX). Las pruebas se hicieron solo en el navegador, como administrador (soporte2@especialistasweb.com.mx). Se siguió PRUEBAS_GROKBOT_PRESENTACION.md.

## Bitácora (todo revertido)
| ID | Pantalla | Qué cambiaste | Cómo estaba | Cómo quedó | ¿Revertido? |
|----|----------|---------------|-------------|------------|-------------|
| 1 | Entrenamiento de materiales, RES-002 (~08:14:40) | Se cambió la clase de uso y se escribió el alias | Activo, Sin clasificar, sin alias | Activo, Insumo, GROKBOT-TMP | sí |
| 2 | Entrenamiento de materiales, RES-002 (~08:14:52) | Se revirtió el cambio de la fila 1 | Insumo, GROKBOT-TMP | Sin clasificar, sin alias (se volvió a abrir y se confirmó) | sí |

No se pulsó ningún otro Guardar. No hubo cobros, envíos, archivos subidos ni personal asignado, y OV-2026-0008 y OV-2026-0009 no se tocaron.

## Resultados por módulo
| Módulo | Punto | Resultado | Evidencia |
|---|---|---|---|
| Administración | Usuarios | OK | Hay 9 usuarios |
| Administración | Roles | OK | Se abrió y se cerró sin guardar |
| Administración | Datos de la empresa | FALLA | Se ve "MÃ©xico" en vez de "México", y "PresentaciÃ³n" en Usuarios |
| RH | Empleados, departamentos, nómina, reloj checador | OK | 18 empleados, 12 departamentos, 45 nóminas y 2 dispositivos |
| Proveedores | Listas | OK | Cotizaciones de proveedores: 0 registros (dato ausente) |
| Proveedores | Insumos RES-001 | OK | Se abrió y se cerró sin guardar |
| Proveedores | Solo stock bajo | OK | La tarjeta dice 106 y la tabla muestra 106 filas |
| Proveedores | Lápiz de RES-002 en Entrenamiento | OK | Abre la ficha; el cambio y la reversión funcionaron |
| Obras | OB-00001 y OB-00002 | OK | El detalle muestra 0.0% de avance; el listado no tiene columna de avance para comparar |
| Obras | Alerta de OB-00002 sin cuadrilla | OK (alerta real) | No se asignó personal |
| CRM | Cliente ROMACO | DATO AUSENTE | Solo hay 3 clientes; se abrió CLI-00002 |
| CRM | POS: Obra (opcional) y "Pasar a cotización de la obra" | OK | Ambos se ven |
| CRM | POS: Variantes | FALLA | También aparece en CHISA GLASS BLANCO OSTION REF.764-14A |
| CRM | POS: matraz | OK | Abre la fórmula y el ticket sigue en $0.00 |
| CRM | Órdenes de venta | FALLA | OV-2026-0004: la lista dice 19/05/2026 y el modal 18/5/2026. Además, OV-2026-0006 tiene total $1,160 pero saldo $53,244 |
| CRM | Consulta con ROMACO | DATO AUSENTE | Responde "Sin resultados"; sí llega a buscar |
| CRM | CCO-2026-0003 | DATO AUSENTE | No hay cotizaciones de obra en el servidor |
| Producción | Inicio, Ver de una orden | OK, con un detalle | No hay error PHP, pero muestra "Fecha Estimada de Entrega 30/11/-0001" |
| Producción | Fabricación, Productos y fórmulas, Lotes | OK | Lotes está vacío |
| Producción | Historial de formulaciones | OK | La columna Cliente existe; la 957 sigue inactiva y sin cliente. Detalle menor: CHISA-GLASS-REF-AZ-03-1 trae el cliente en los comentarios pero la columna dice "—" |
| Campana | Carga | OK | 34 notificaciones; no se vació |

## Antes de salir
- RES-002 quedó como estaba al empezar.
- La fórmula 957 sigue inactiva y sin cliente.
- El ticket del POS está en $0.
- No se subió ningún archivo.

## Qué bloquearía la presentación
Lo que bloquearía la presentación es que en el servidor no existen ROMACO ni la cotización CCO-2026-0003. Además, conviene corregir antes las Variantes de más en el POS, la fecha de un día menos en las órdenes de venta y los acentos rotos.
