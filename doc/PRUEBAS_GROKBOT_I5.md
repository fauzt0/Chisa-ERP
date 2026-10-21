# Pruebas Grokbot — presentación iteración 5

Eres un revisor humano del ERP CHISA. Recorres pantallas, anotas lo que ves (textos rotos, botones que no responden, datos que no coinciden) y **solo haces cambios reversibles**. No “mejoras” fórmulas, no tocas costos de producción, no confirmas ventas, no envías correo ni WhatsApp.

- **Rama:** `iteracion-5` (no `main`)
- **App:** `https://erp.chisarecubrimientos.com.mx/` (o el entorno que te indiquen)
- **Usuario:** `soporte2@especialistasweb.com.mx` / `Prueba123@`
- **Prohibido:** `DROP`, `TRUNCATE`, force-push, SMOKE_P0, Compras T1–T7, autorizar PRE-2026-0001, cobrar en POS, confirmar o cancelar órdenes reales, editar líneas de `CCO-2026-0003`.
- **Cotización demo:** `CCO-2026-0003` (ROMACO, hospital C.H. 57, total **$319,789.28**). No la presentes como Hospital Infantil.

Al terminar entrega: lista de pantallas OK/falla, capturas si puedes, y la **bitácora de cambios** de la sección 11 (para revertir).

---

## 0. Bitácora (llénala desde el primer clic que escriba)

Copia esta tabla a tu reporte. Cada fila es un cambio. Si no cambiaste nada, déjala vacía.

| ID | Fecha/hora | Pantalla | Folio o ID | Campo | Valor anterior | Valor nuevo | Cómo revertir | ¿Revertido? |
|----|------------|----------|------------|-------|----------------|-------------|---------------|-------------|
| G1 |  |  |  |  |  |  |  |  |

Regla: si no puedes revertirlo en menos de un minuto, **no lo hagas**.

---

## 1. Arranque (solo lectura)

1. Entra con el usuario de prueba. Anota si pide 2FA (en development no debería).
2. Revisa el menú: Administradores, RH, Reloj Checador, Proveedores (con Entrenamiento de materiales), Producción (con Historial de formulaciones), CRM Ventas (POS, Consulta, Órdenes, Obras, Cotizaciones de obra), Facturación, Contabilidad, Almacén.
3. Campana de notificaciones: debe cargar; no la “arregles” asignando personal a `OB-00002` (obra en ejecución sin cuadrilla: es alerta real).

---

## 2. Administradores / usuarios (solo lectura)

Rutas: `usuarios/GestionUsuarios`, `usuarios/Roles`, `usuarios/GestionUsuarios/empresa`.

- Lista de usuarios visible, búsqueda funciona.
- Roles: hay permisos; no crees ni borres roles.
- Datos de empresa: logo y razón social se ven.
- **No** des de alta usuarios, **no** cambies contraseñas, **no** desactives a nadie.

---

## 3. RH (solo lectura)

Rutas: `rh/RecursosHumanos`, `rh/Departamentos`, `rh/Nomina`.

- Empleados listan; abre uno y cierra sin guardar.
- Departamentos y nómina abren sin error de base.
- Reloj checador: entra a dispositivos o reporte diario si el menú existe; no sincronices ni mandes comandos al reloj.
- **No** crees empleados ni plantillas.

---

## 4. Proveedores e insumos (lectura + un cambio mínimo)

Rutas: `compras/Categorias`, `compras/Insumos`, `compras/Proveedores`, `compras/OrdenesCompra`, `compras/Cotizaciones`.

- Categorías: 16 categorías; id 1 = Materias Primas Químicas, id 4 = Resinas.
- Insumos: busca `RES-001`. Abre ficha, cierra sin guardar.
- Proveedores: lista y abre uno.
- Órdenes de compra y cotizaciones de compra: lista. **No** T1–T7, **no** autorizar.

### Cambio permitido (entrenamiento, no el CRUD pesado)

Usa **Proveedores → Entrenamiento de materiales** (`compras/EntrenamientoMateriales`).

Insumo de prueba preferido: **`RES-002`** (Resina Alquídica Larga Aceite). En el VPS ya se usó y se revirtió; no está en `detalle_formulacion` en el entorno local. **No uses CHISA GLASS ni resinas de fórmulas activas con costo.**

Pasos:

1. Busca `RES-002`. Abre el lápiz.
2. Anota en la bitácora: estatus, clase de uso, alias interno, proveedores y precio.
3. Pon **clase de uso = Insumo** y un alias interno `GROKBOT-TMP` si el alias interno está vacío (si ya tiene alias, **no lo borres**: concatena nada; deja el original y solo cambia clase_uso).
4. Guarda datos generales. Espera recarga. Confirma que la tabla muestra el cambio.
5. **Revertir de inmediato:** clase de uso → “Sin clasificar”; alias interno → valor anterior (o vacío). Guardar. Anota G1/G2 revertidos.
6. **No** pulses Guardar producto (precio de venta / cubeta). Eso toca catálogo de venta.
7. **No** cambies precio de un proveedor existente. Si pruebas agregar un alias extra, usa tipo Genérico con texto `GROKBOT-ALIAS` y luego bórralo en `compras/Insumos` (botón/ajax de alias) o pide SQL:  
   `DELETE FROM insumos_alias WHERE alias = 'GROKBOT-ALIAS';`

Si `RES-002` no existe, **no inventes otro insumo de fórmula**. Quédate en solo lectura.

---

## 5. Historial de formulaciones (casi solo lectura)

Ruta: `produccion/HistorialFormulaciones`.

1. Primera carga: casilla **Solo activas** marcada. Lista con columnas Producto, Versión, **Cliente**, Activa, Rendimiento, Comentarios recortados, Origen.
2. Filtra texto `xlsx` o `Archivo:`. Debe salir origen Excel.
3. Abre **Ver** de una versión **inactiva** (`es_activa` No). BOM solo lectura. **No cambies cantidades.**
4. **No abras el editor de Productos y Fórmulas para “corregir” una receta.**
5. Asociación de cliente (opcional, reversible):

   - Solo en formulación **inactiva**. En local/VPS sirve `formulaciones.id = 713` (TIO 1125, v2, inactiva) si existe.
   - Anota `cliente_id` actual (suele ser vacío).
   - Asocia un cliente de prueba (el primero de la lista, anota id y nombre). Guardar cliente.
   - Confirma que el detalle muestra el cliente y que **es_activa sigue No** y el BOM no cambió.
   - **Revertir:** elige “Quitar asociación” y guarda. Confirma cliente vacío.
   - **Prohibido** asociar cliente en una fórmula **activa** (el sistema puede rechazar si el costo no cuadra; no insistas).

6. Productos y Fórmulas (`produccion/Productos`): abre listado, busca `GLASS`, cierra. **No edites BOM.**
7. Lotes: entra a consultar; no registres consumo.

---

## 6. CRM — clientes, POS, órdenes (lectura + POS sin cobrar)

- `ventas/Clientes`: busca ROMACO o BE GRAND. Abre ficha, no guardes.
- `ventas/Pos`:
  - Sucursal visible.
  - Campo **Obra (opcional)** y botón **Pasar a cotización de la obra**.
  - Busca `GLASS`. Deben salir CHISA GLASS MICRO y COLOR 2.
  - Enlace **Variantes** (texto, no el matraz): debe listar padre + beige $520.
  - **No agregues al ticket**, **no cobres**, **no guardes cotización POS**, **no pases a obra**.
- `ventas/Ordenes`: lista. **No** confirmes, **no** canceles, **no** “Nueva versión”.
  - Órdenes locales de prueba `OV-2026-0008` y `OV-2026-0009` ya deben aparecer **Cancelada** si existen. Si están Cotización, no las toques tú; anótalo.

---

## 7. CRM — cotizaciones de obra y obras

- `ventas/CotizacionesObra`: lista de trabajo (no el archivo de 398).
- Abre `editar/3` = **CCO-2026-0003**.
  - Constructora ROMACO, obra HOSPITAL C.H. 57.
  - Dos aplicaciones M2, total **$319,789.28**.
  - Tarjeta Versiones: v1 `CCO-2026-0003`, v2 `CCO-2026-0417` (Ajuste de altura) si existe en ese entorno.
  - Vista/PDF y Excel: abre, no reimprime a clientes.
  - **No** Nueva versión, **no** Guardar, **no** cambiar estatus.
- Archivo de consulta: enlace Archivo (~398 fichas). Abre una ficha de consulta; no la conviertas en cotización viva.
- `ventas/ObrasVentas`: lista. Detalle de `OB-00001` (Planificación) y `OB-00002` (posible alerta sin cuadrilla). No asignes personal “para quitar la alerta”.
- Seguimiento: fases, expediente drag-and-drop. **No subas archivos reales de cliente.** Si pruebas drag-and-drop, usa un PDF mínimo llamado `GROKBOT-TMP.pdf` y bórralo del expediente al final (anota carpeta/id).

---

## 8. Consulta cruzada

Ruta: `ventas/Consulta`.

1. Cliente `ROMACO`, documento Cotizaciones de obra, **Solo con aplicaciones**. Deben salir `CCO-2026-0003` y, si existe, `CCO-2026-0417`.
2. Folio `CCO-2026-0003` en el buscador de texto. Abrir.
3. Tipo Obras, lugar `Iguala` o el nombre de una obra conocida.

---

## 9. Recorrido corto de lo demás (solo abrir)

Si el menú existe y carga en < 10 s, marca OK. Si 500/Severity, copia el texto.

- Dashboard inicio
- Facturación (lista, no timbrar)
- Contabilidad dashboard / pólizas (no polizar)
- Almacén inventario (no ajustes de stock)

---

## 10. Criterios de falla (aunque el HTTP sea 200)

Anota como defecto:

- Botón que no hace nada, modal vacío, DataTable en inglés o “error ajax”.
- Precio de venta mostrado como si fuera costo de compra.
- Clase de uso que cambia `insumos.tipo` (comprado/fabricado) — no debe.
- Historial de fórmulas que permita editar kg o “activar esta versión”.
- PDF de `CCO-2026-0003` con total distinto de 319,789.28.
- POS sin selector de obra o sin Variantes en la tarjeta (no el icono matraz).
- Consulta que no encuentra `CCO-2026-0003` con ROMACO + aplicaciones.

---

## 11. Reversión (ejecuta antes de cerrar)

En este orden:

1. Quitar cliente de cualquier formulación que hayas asociado (`Quitar asociación`).
2. En entrenamiento, devolver `RES-002` a estatus/clase/alias originales.
3. Borrar alias `GROKBOT-ALIAS` / `GROKBOT-TMP` en insumos_alias.
4. Borrar `GROKBOT-TMP.pdf` del expediente si lo subiste.
5. **No** restaures el respaldo completo de BD salvo desastre (formulaciones tocadas, costos cambiados). Si eso pasó, detente y reporta IDs; el usuario tiene respaldo.

SQL de emergencia (solo lo que hayas creado tú):

```sql
-- Alias de prueba
DELETE FROM insumos_alias WHERE alias LIKE 'GROKBOT%';

-- Insumo RES-002 (id local típico = 3; confirma codigo='RES-002')
UPDATE insumos
SET alias = NULL, clase_uso = NULL, estatus = 'Activo', actualizado_por = NULL
WHERE codigo = 'RES-002';

-- Cliente en formulación de prueba (confirma id)
UPDATE formulaciones SET cliente_id = NULL WHERE id = 713 AND es_activa = 0;
```

Comprueba después:

```sql
SELECT codigo, alias, clase_uso, estatus FROM insumos WHERE codigo = 'RES-002';
SELECT id, es_activa, cliente_id FROM formulaciones WHERE id IN (713, 318);
SELECT folio, estatus, (SELECT ROUND(SUM(importe),2) FROM cotizaciones_comerciales_detalle d WHERE d.cotizacion_id = c.id) total
FROM cotizaciones_comerciales c WHERE folio = 'CCO-2026-0003';
```

`CCO-2026-0003` debe seguir Borrador y **319789.28**. Fórmula 318 (si existe en VPS) activa, `cliente_id` NULL, componentes intactos.

---

## 12. Entregable

1. Tabla de módulos: URL, HTTP/resultado visual, notas humanas.
2. Bitácora sección 0 con revertido = sí.
3. Defectos con pasos para reproducir (sin parches de código a menos que te lo pidan).
