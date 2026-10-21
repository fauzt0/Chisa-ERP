# Pruebas de presentación — ERP CHISA

Trabajas solo en el navegador, como un administrador. No tienes código ni base de datos. No envíes correo, WhatsApp ni facturas. No cobres. No confirmes ni canceles ventas. No edites recetas ni kilos. No pulses Guardar producto. No asignes personal a una obra solo para quitar una alerta.

Copia esta bitácora y anota cada vez que pulses Guardar. Antes de terminar, cada fila debe decir revertido = sí.

| ID | Pantalla | Qué cambiaste | Cómo estaba | Cómo quedó | ¿Revertido? |
|----|----------|---------------|-------------|------------|-------------|

## Accesos

| Dato | Valor |
|------|--------|
| Dirección | https://erp.chisarecubrimientos.com.mx/ |
| Usuario | soporte2@especialistasweb.com.mx |
| Contraseña | Prueba123@ |

Entra por el menú Inicio si la raíz muestra “Welcome to CodeIgniter”. Eso ya se sabe y no es falla de esta ronda.

---

## 1. Administración

Menú Administradores.

- Usuarios: la lista carga. El buscador puede pedir Enter. No des de alta ni desactives a nadie.
- Roles: abre y cierra sin guardar.
- Datos de la empresa: anota si ves letras rotas (por ejemplo “MÃ©xico”). No guardes.

## 2. Recursos humanos

- Empleados: abre una ficha y ciérrala sin guardar.
- Departamentos y nómina: deben abrir.
- Reloj checador: puedes ver dispositivos o un reporte. No sincronices ni mandes comandos.

## 3. Proveedores

- Categorías, proveedores, órdenes de compra y cotizaciones: solo listas. No autorices compras.
- Insumos: busca `RES-001`, abre y cierra sin guardar. Marca “Solo stock bajo”: si el número de la tarjeta es mayor a cero, la tabla no debe quedar vacía.
- Entrenamiento de materiales: busca `RES-002`. El lápiz debe abrir una ficha (puede ser otra página) con estatus, clase de uso y alias interno. El tipo comprado/fabricado es solo lectura.
  - Anota cómo está. Cambia clase de uso a Insumo (o a Materia prima si ya era Insumo). Si el alias está vacío, escribe `GROKBOT-TMP`. Si ya tiene alias, no lo borres.
  - Guarda. Vuelve a abrir y confirma el cambio.
  - Revierte de inmediato a como estaba (clase “Sin clasificar” si antes estaba vacía, y el alias original). Guarda otra vez.

## 4. Obras

- En CRM Ventas → Obras abre `OB-00001` y `OB-00002`.
- Si el avance no coincide entre el listado de obras y el detalle, anótalo. No lo “corrijas”.
- `OB-00002` en ejecución sin cuadrilla es una alerta real. No asignes personal.
- No subas archivos.

## 5. CRM

- Clientes: busca un cliente que exista (si ROMACO no está, anótalo y abre cualquier cliente). Cierra sin guardar.
- Punto de venta:
  - Deben verse Obra (opcional) y “Pasar a cotización de la obra”.
  - Busca `GLASS`. Variantes solo en CHISA GLASS MICRO y COLOR 2. El matraz abre la fórmula y no agrega nada al ticket (sigue en $0).
  - No cobres, no guardes cotización, no pases a obra.
- Órdenes de venta: abre una y cierra. No confirmes, no canceles, no hagas nueva versión. Anota el total y la fecha si se ven incoherentes. No modifiques `OV-2026-0008` ni `OV-2026-0009`.
- Cotizaciones de obra y Consulta:
  - Consulta: folio vacío, cliente `ROMACO`, documento cotizaciones de obra, solo aplicaciones. Si pide “escribe un cliente” sin buscar, es falla. Si dice sin resultados, es dato que no está en este servidor.
  - Si existe `CCO-2026-0003`, ábrela: ROMACO, hospital C.H. 57, total $319,789.28. No guardes ni crees versión.

## 6. Producción

- Inicio → Ver de una orden reciente: debe abrir el detalle sin error PHP ni rutas del servidor.
- Fabricación, Productos y fórmulas: abre el listado y cierra. No edites una receta.
- Historial de formulaciones: debe existir la columna Cliente. Filtra texto `xlsx` si hay filas. Abre BASE-ORGANICA-NARANJA v2 (id 957): Activa = No y Sin cliente. Los kilos no se editan. Si tuviera cliente, quita la asociación y anótalo.
- Lotes: entra a consultar. No registres consumo.

## 7. Campana

Debe cargar. No la vacíes.

## Qué anotar como falla

- Pantalla en blanco, error PHP o botón que no hace nada.
- El lápiz de `RES-002` no abre ficha.
- El matraz del POS agrega un producto al ticket.
- Variantes aparece en productos que no son MICRO / COLOR 2.
- El detalle de producción muestra rutas del servidor.
- “Solo stock bajo” vacío cuando el conteo es mayor a cero.
- Consulta con cliente no llega a buscar.

## Antes de salir

1. `RES-002` quedó como al empezar.
2. La fórmula 957 sigue sin cliente e inactiva.
3. El ticket del POS está en $0.
4. No hay archivos subidos por ti.

## Reporte

Lista por módulo: OK, falla o dato ausente. Bitácora con todo revertido. Una frase: qué bloquearía la presentación de mañana.
