# Reporte QA — ERP CHISA Recubrimientos
Fecha: 21/10/2026 (hora CDMX). Entorno: https://erp.chisarecubrimientos.com.mx/ — usuario soporte2@especialistasweb.com.mx.

## Veredicto
NO listo para presentar. Bloqueantes: (1) no existen datos de demo (CCO-2026-0003, CCO-2026-0417, cliente ROMACO); (2) lápiz de Entrenamiento de materiales no abre; (3) "Ver" de Solicitudes de producción en Inicio lanza error PHP con rutas del servidor; (4) importes/estatus incoherentes en OV, obras y facturas.

## Bitácora de cambios (todo revertido)
| ID | Hora | Pantalla | Cambio | Antes | Después | ¿Revertido? |
|---|---|---|---|---|---|---|
| 1 | — | Proveedores > Entrenamiento de materiales, RES-002 | Ninguno (el lápiz no abre) | Activo, clase «—», alias «—» | Igual | N/A, sin cambios |
| 2 | 02:07:50 | Producción > Historial de formulaciones, BASE-ORGANICA-NARANJA v2 (id 957, inactiva) | Asoció cliente CLIENTE MOSTRADOR | Sin cliente | CLIENTE MOSTRADOR, Activa=No, componentes iguales | Sí |
| 3 | 02:07:55 | Mismo | Quitar asociación | CLIENTE MOSTRADOR | Sin cliente | Sí |
En la segunda ronda (pruebas generales) no se alteró ningún dato. Solo hubo envíos de login y búsquedas GET.

## Ronda 1 — plan PRUEBAS_GROKBOT_I5
Pantallas OK: Inicio, Administradores (usuarios, roles, empresa), RH (empleados, departamentos, nómina), Reloj Checador, Proveedores (todas), Historial de formulaciones, Lotes, Clientes, POS, Órdenes de Venta, Obras, Sucursales, Descuentos, Facturación, Contabilidad, Almacén.
Con falla: Cotizaciones de obra y Consulta (error 1054 'c.es_consulta', luego sin datos), lápiz de Entrenamiento, raíz del sitio.
Hallazgos del plan:
- ROMACO, CCO-2026-0003 y CCO-2026-0417 no existen; Consulta ROMACO+aplicaciones sin resultados; PDF/total $319,789.28 no verificable.
- OV-2026-0008 = Cotización y OV-2026-0009 = Completada (esperado: Cancelada).
- POS: tiene Obra (opcional) y Pasar a cotización de la obra; GLASS muestra MICRO ($500) y MICRO COLOR 2 Beige ($520); Variantes no abre modal.
- Historial de formulaciones sin columna Cliente; Lotes vacío.
- OB-00002 en ejecución sin cuadrilla (alerta real, no tocada). Ninguna obra en Planificación.
- Contabilidad: dashboard 10 pólizas pendientes, tabla Pólizas vacía. Almacén: stock negativo CHISA GLASS MICRO.

## Ronda 2 — defectos por módulo (pasos clic a clic)
### Acceso
- Raíz "/" muestra "Welcome to CodeIgniter" en vez de login. Pasos: abrir la URL raíz.
- "Olvidé la contraseña" apunta a auth-reset-password-cover.html (plantilla). Logo enlaza a aapcoy.org.mx. Pie en inglés "Support Help Center Privacy Terms of Service".
### Administradores y roles
- Mojibake: Administradores > Datos de la Empresa (Ciudad "MÃ©xico"); Buscar Administrador usuario 9 "Demo PresentaciÃ³n".
- Super Administrador ("Acceso total") tiene 67/83 permisos. Roles y Permisos > editar rol 1.
- KPIs: "Bajas (30d)=0" con 4 suspendidos; "Actividad (7d)=13" incoherente.
- Editar administrador (/usuarios/GestionUsuarios/editar/6) sin botón Cancelar.
- Buscador solo filtra con Enter/lupa. Textos sin acento ("Gestion", "Ingreso erroneo"). "Showing 0 to 0 of 0 entries" en inglés.
- Datos de empresa: RFC, domicilio y teléfono vacíos.
### Proveedores
- CRÍTICO Entrenamiento de materiales: buscar RES-002 (o SOL-004, SOL-001, ADI-007) > clic lápiz > no abre modal ni da error. Tabla sin paginación ni filtros (150 filas). Ayuda genérica de "Compras". Etiqueta técnica "Alias interno (insumos.alias)".
- Insumos: marcar "Solo stock bajo" > sin resultados, KPI dice 106.
- Insumos > lápiz (p.ej. IMP-9239F06D) > Unidad de medida vacía aunque la tabla dice Kg (riesgo de borrarla al guardar).
- Proveedores: datos sucios (PROV-039 nombre comercial "CON 70 mts DE CABLE 7 DÍAS"); insumos duplicados IMP-xxxx ("AMA CANARIO" x3).
- OC: filtro "Con adeudo" excluye OC-2026-DEMO1 (adeudo $660); OC-2026-0001/0002 total $0.00.
### RH y Reloj Checador
- KPI 18 empleados = 14 activos + 4 reingreso (confuso, no error). Departamentos suman 14.
- Recibo NOM000038: "MÃ©xico"; fecha de pago 18/08/2026 anterior al periodo 05–11/10/2026.
- NOM000038 Parcial con 16/17 pagados; percepción "Comidas $136.66" no sumada. Nóminas canceladas en 2027; NOM000120 fuera de secuencia.
- Detalle empleado: fecha "24 Dec 2025" en inglés; contrato posterior al ingreso; RFC que no corresponde (Ana Karina Roman).
- Reloj: dashboard "Empleados 0", 0 presentes / 2 ausentes; dispositivos "Desconectado" en dashboard y "Activo" en lista; última sync 12/10; reporte mensual cuenta días naturales como laborales.
### Obras
- /obras/Obras: contadores no cuadran con filas; sin tarjeta "Aprobada".
- OB-00002: avance 20% en /obras/Obras y 0.0% en /ventas/ObrasVentas/detalle/2; subtotal $36,000 vs producto 2×$500; anticipo $2,000 vs esperado $4,176; parcialidad "vence hoy" marcada Vencida; "Tiempo de entrega: 15" sin unidad.
- OB-00001 ("EHWEB"): costo $50,000 vs venta $5,800; anticipo 10% vs condiciones 50%; sin presupuesto.
- Pestaña Documentos en detalle CRM se queda "Cargando…" >4 s. Panel de seguimiento arriba del encabezado.
### Producción
- ALTO: Inicio > Solicitudes de producción > "Ver" → /produccion/Dashboard/detalle/30 → error PHP ArgumentCountError con rutas del servidor expuestas.
- Inicio dice 6 pedidos / 5 solicitudes; Fabricación Dashboard dice 7, todos "Bloqueada".
- Productos y Fórmulas: PROD-0001 stock -57; 496/497 con stock bajo; nota de desarrollador visible (GUIA_PRODUCCION_POST_IMPORTACION.md); buscador por cualquier palabra (57 resultados con "CHISA GLASS MICRO").
- Historial de formulaciones: sin paginación (956 filas); productos basura en desplegable ("22-DE-OCTUBRE-2008"); texto técnico "Sí (es_activa=1)"; versiones con componentes duplicados; dos "V1.0". Kilos no editables y sin activar (OK).
- Consultar Lote con código inexistente: alerta vacía.
### CRM Ventas
- ALTO: fechas del modal de OV y panel de cliente un día antes que lista/PDF (OV-2026-0008: 13/9 vs 14/09). Probable zona horaria.
- ALTO: OV-2026-0004 subtotal $6,000 + IVA $960 + envío $1,000 = total $6,960 (debería $7,960). OV-2026-0006 cancelada con saldo $53,244. OV canceladas con saldo/pagadas; OV pendientes con saldo $0.
- Saldo de Empresa de Prueba solo incluye OV-2026-0004; OV-TEST-001 no entra en Cobros.
- POS: el icono de matraz también dispara agregar al ticket (toast "No se puede agregar…"); con producto con precio podría agregarlo. "Variantes" en productos sin variantes. Sin filtro de categorías. CLIENTE MOSTRADOR duplicado.
- Facturación: facturas Emitidas de OV canceladas (A-F-OV-2026-0011, 0007); factura cancelada de OV confirmada (2025-0015); RFC distinto al de la ficha; CP vacío.
- Pestaña Ventas del cliente muestra 10 de 14. Estatus "Completada" no filtrable. OV-2026-0006 referencia OB-00003 inexistente.
- Formatos: "$1000.00" sin separador; fechas en 3 formatos; "Jalisto"; KPIs 100% con valor 0.
- Cotizaciones de obra y Consulta ya no dan error 1054, pero Archivo (0).

## Notificaciones (campana)
34 según contador (lista ~20): cobros y parcialidades vencidas OB-00001/OB-00002, obras retrasadas, OB-00002 sin cuadrilla, OV-2025-0015 y OV-2026-0004 vencidas, 5 solicitudes de producción, 5 productos sin formulación, 1 pre-orden, nóminas NOM000111 y NOM000120 vencidas.

## Capturas
Carpeta /workspace/erp/ (empresa_mojibake.png, admin_lista_mojibake.png, entrenamiento_lapiz_sin_modal.webp, insumos.png, proveedor_ficha.png, formula957_revertida.webp, RES-002_estado_final_sin_cambios.webp, recibo_mojibake_NOM000038.webp, produccion_dashboard_detalle30_error.webp, productos_stock_negativo.webp, rh_empleados_busqueda_english.png, pos_matraz_toast_burbujeo.png, pos_variantes_sin_variantes.png, ov0008_modal_fecha13_vs_lista14.png, ordenes_lista.png, factura_modal_cp_vacio.png).
