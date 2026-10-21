# Pruebas de presentación — ERP CHISA (Grokbot)

Este archivo es **todo lo que necesitas**. No tienes acceso al código, a la base de datos ni a la terminal. Trabajas **solo en el navegador**, como un administrador: menús, búsquedas, formularios y botones Guardar.

Cuando termines, entrega un reporte con: qué pantallas abriste, qué se veía mal, la bitácora de cambios y la confirmación de que **revertiste** cada cambio.

---

## Accesos

| Dato | Valor |
|------|--------|
| Sistema | ERP CHISA Recubrimientos |
| Dirección | https://erp.chisarecubrimientos.com.mx/ |
| Usuario | soporte2@especialistasweb.com.mx |
| Contraseña | Prueba123@ |

Si esa dirección no abre, usa la que te dé Fausto (a veces el entorno de prueba es local). El usuario y la contraseña son los mismos.

Entra, espera el escritorio y **no crees usuarios, no cambies la contraseña, no cierres la sesión de otros**.

---

## Cómo trabajar

1. Recorre cada módulo como si mañana lo presentaras a un cliente.
2. Si algo se ve roto (página en blanco, error, botón que no responde, cifra incorrecta), anótalo con la ruta de la pantalla y lo que hiciste.
3. **Casi todo es solo lectura.** Los únicos cambios permitidos están en las secciones 5 y 6, y hay que **deshacerlos antes de salir**.
4. No envíes correos, WhatsApp ni facturas. No cobres. No confirmes ventas. No autorices compras.

Copia esta bitácora a tu reporte y llénala cada vez que pulses Guardar:

| ID | Hora | Pantalla | Qué cambiaste | Cómo se veía antes | Cómo quedó | ¿Ya lo revertiste? |
|----|------|----------|---------------|-------------------|------------|--------------------|
| 1 |  |  |  |  |  |  |

---

## 1. Inicio y menú

Tras el login debes ver el menú izquierdo. Comprueba que existan, entre otras:

- Administradores
- Recursos Humanos
- Reloj Checador
- Proveedores (dentro: Categorías, Insumos, **Entrenamiento de materiales**, Proveedores, Órdenes de Compra, Cotizaciones)
- Producción (dentro: Fabricación Dashboard, Productos y Fórmulas, **Historial de formulaciones**, Lotes)
- CRM Ventas (dentro: Punto de Venta, **Consulta**, Órdenes de Venta, Obras, Cotizaciones de obra, Sucursales, Clientes, Descuentos)

Campana de notificaciones (arriba a la derecha): debe mostrar avisos. Si sale una obra en ejecución **sin cuadrilla**, **no asignes personal** para “arreglarlo”: es una alerta real.

---

## 2. Administradores (solo mirar)

Menú **Administradores**.

- Lista de usuarios: debe haber gente; prueba el buscador si existe.
- Roles / permisos: abre y cierra **sin guardar**.
- Datos de la empresa: logo y datos visibles.

**No** des de alta usuarios. **No** desactives a nadie.

---

## 3. Recursos humanos (solo mirar)

Menú **Recursos Humanos**.

- Empleados: lista, abre uno, cierra **sin guardar**.
- Departamentos y nómina: deben abrir.
- Reloj checador: puedes entrar a dispositivos o reportes. **No** sincronices ni envíes comandos al reloj.

---

## 4. Proveedores (solo mirar, salvo la sección 5)

Menú **Proveedores**.

- Categorías: lista de categorías de materiales.
- Insumos: busca `RES-001`, abre la ficha, cierra **sin guardar**.
- Proveedores: abre uno y cierra.
- Órdenes de compra y cotizaciones de compra: solo lista. **No autorices** nada.

---

## 5. Entrenamiento de materiales (único lugar para clasificar insumos)

Menú **Proveedores → Entrenamiento de materiales**.

Debes ver una tabla con código, nombre, alias, estatus, categoría, tipo (comprado/fabricado, **solo lectura**), clase de uso, proveedores con precio de compra y, si aplica, datos de producto (presentación, rendimiento, precio de **venta**). El precio de venta **no** es el costo de compra.

### Cambio permitido (y obligatorio revertir)

1. Busca **`RES-002`** (Resina Alquídica Larga Aceite).
2. Abre el lápiz. Anota en la bitácora: estatus, clase de uso, alias interno.
3. Cambia **Clase de uso** a `Insumo` (si ya decía Insumo, pon `Materia prima`).
4. Si el alias interno está vacío, escribe `GROKBOT-TMP`. Si ya tiene un nombre, **no lo borres**.
5. Pulsa **Guardar datos generales**. La página puede recargarse. Confirma que la tabla muestra el cambio.
6. **Revertir ahora:** abre de nuevo el lápiz, deja clase de uso en **Sin clasificar** (o el valor que anotaste) y el alias interno como al principio. Guardar otra vez. Marca la bitácora como revertido.

### Prohibido en esta pantalla

- Pulsar **Guardar producto** (unidad, cubeta, rendimiento, precio de venta). Eso altera el catálogo de venta.
- Cambiar el precio de un proveedor que ya existe.
- Agregar un proveedor nuevo a ese insumo.
- Si quieres probar un alias extra: tipo **Genérico**, texto `GROKBOT-ALIAS`, Agregar. Luego ve a **Proveedores → Insumos**, abre `RES-002` y **borra ese alias** desde ahí. Si no encuentras cómo borrarlo, anótalo como pendiente y no agregues más.

Si `RES-002` no aparece, **no experimentes con otro insumo**. Quédate en solo lectura y reporta que no estaba.

---

## 6. Historial de formulaciones (casi solo mirar)

Menú **Producción → Historial de formulaciones**.

1. Al entrar, **Solo activas** debe estar marcada. La tabla debe tener producto, versión, **cliente**, si está activa, rendimiento, comentarios y origen (archivo).
2. En “Texto en comentarios” escribe `xlsx` o `Archivo:` y filtra. Debe listar orígenes de Excel.
3. Abre **Ver** en una fila cuya columna Activa sea **No**. Debes ver el listado de componentes (BOM) **sin campos para editar kilos**.
4. Vuelve atrás. **No entres a Productos y Fórmulas a “corregir” recetas.**

### Cambio permitido (y obligatorio revertir)

Solo en una versión **inactiva** (columna Activa = No):

1. Anota producto, versión y si decía “Sin cliente”.
2. En el detalle, elige un cliente de la lista (anota el nombre), pulsa **Guardar cliente**.
3. Confirma que se ve el cliente y que **sigue inactiva**. Los componentes no deben cambiar.
4. **Revertir ahora:** elige **Quitar asociación** y guarda. Debe volver a “Sin cliente”.

**No** asocies cliente en una fórmula **Activa = Sí**. Si el sistema rechaza, no insistas.

Lotes: entra a consultar. No registres consumo ni cierres lotes.

---

## 7. CRM — clientes, punto de venta y órdenes

**Clientes:** busca `ROMACO`. Abre ficha, cierra sin guardar.

**Punto de Venta (POS):**

- Debe verse sucursal, cliente, ticket, **Obra (opcional)** y el botón **Pasar a cotización de la obra**.
- En el buscador de productos escribe `GLASS`. Deben salir *CHISA GLASS MICRO* y *CHISA GLASS MICRO COLOR 2*.
- En la tarjeta, el enlace de texto **Variantes** (no el icono de matraz) debe mostrar el producto padre y la variante beige a $520.
- **No agregues productos al ticket. No cobres. No guardes cotización. No pases a obra.**

**Órdenes de Venta:** solo lista y abre detalle. **No** confirmes, **no** canceles, **no** pulses Nueva versión. Si ves `OV-2026-0008` u `OV-2026-0009`, deben estar **Cancelada**. Si no, anótalo y no las toques.

---

## 8. Cotizaciones de obra y obras (pieza de la presentación)

**CRM Ventas → Cotizaciones de obra**

Abre **CCO-2026-0003**. Debe ser:

- Constructora ROMACO (nombre largo de construcciones y proyectos).
- Obra **HOSPITAL C.H. 57** (Iguala, Guerrero).
- Dos conceptos de **aplicación** en M2.
- Total **$319,789.28**.

Si hay tarjeta **Versiones**, puede aparecer `CCO-2026-0417` como versión 2 (“Ajuste de altura”). **No** pulses Nueva versión ni Guardar.

Abre Vista / PDF y Excel solo para ver. El PDF no debe mostrar otro total.

**No presentes CCO-2026-0001 como la cotización del Hospital Infantil.**

Hay un archivo de cotizaciones de consulta (cientos de fichas). Puedes abrir una ficha marcada como consulta; no la conviertas en cotización viva.

**Obras:** abre una en Planificación. Si `OB-00002` está en ejecución sin personal, **no asignes cuadrilla**. Expediente: no subas documentos de clientes. Si pruebas arrastrar un archivo, usa un PDF mínimo llamado `GROKBOT-TMP.pdf` y **elimínalo del expediente** antes de salir.

---

## 9. Consulta rápida

**CRM Ventas → Consulta**

1. Cliente o constructora: `ROMACO`. Documento: Cotizaciones de obra. Marca **Solo con aplicaciones**. Buscar. Deben salir `CCO-2026-0003` y, si existe, `CCO-2026-0417`.
2. En folio/concepto escribe `CCO-2026-0003` y busca. Abrir.
3. Prueba tipo Obras con un lugar conocido (por ejemplo Iguala).

---

## 10. Un vistazo al resto

Abre y cierra, sin guardar:

- Inicio / dashboard
- Facturación (lista; **no timbrar**)
- Contabilidad (dashboard o pólizas; **no polizar**)
- Almacén / inventario (**no ajustes de stock**)

---

## 11. Qué reportar como defecto

Aunque la pantalla “cargue”, es defecto si:

- Un botón no hace nada, un modal sale vacío o la tabla queda en inglés / “error ajax”.
- El precio de venta se entiende como costo de compra.
- En entrenamiento puedes cambiar el tipo comprado/fabricado (no debería).
- En historial de fórmulas puedes editar kilos o activar una versión.
- El PDF o el editor de `CCO-2026-0003` no suma **$319,789.28**.
- El POS no tiene obra, no tiene “Pasar a cotización de la obra”, o Variantes no muestra COLOR 2.
- La Consulta con ROMACO + aplicaciones no encuentra `CCO-2026-0003`.

---

## 12. Antes de irte (obligatorio)

1. Quita el cliente de la fórmula inactiva si lo asociaste.
2. Devuelve `RES-002` a clase de uso y alias originales.
3. Borra el alias `GROKBOT-ALIAS` si lo creaste (desde Insumos).
4. Borra `GROKBOT-TMP.pdf` del expediente si lo subiste.
5. **No restaures ningún respaldo** salvo que hayas cambiado una receta o un costo: en ese caso detente y descríbelo; hay un respaldo de base, pero no lo ejecutas tú.

Comprueba otra vez **CCO-2026-0003**: sigue en borrador y el total es **$319,789.28**.

---

## 13. Formato de tu reporte

1. Lista de pantallas: nombre del menú, ¿abrió?, notas (textos raros, lentitud, botones rotos).
2. Bitácora de la tabla de arriba, todas las filas con “¿Ya lo revertiste? = sí”.
3. Defectos con pasos para repetirlos (clic a clic), como si se los explicaras a Fausto.
4. Una frase: si el sistema se siente listo para presentarse mañana o qué bloquearía la demo.
