# PLAN — Integración API Paquetería Tres Guerras (Iteración 4)

> **Fecha de diseño:** 2026-09-07  
> **Estado:** Diseño aprobado — Implementación pendiente (Iteración 4)  
> **Autor:** Agente IA Iteración 3

---

## 1. Contexto

Tres Guerras es la empresa de paquetería y envíos con la que Chisa Recubrimientos opera para llevar productos a clientes u obras fuera de la ciudad. La integración permitirá generar guías de envío, rastrear paquetes y actualizar el estatus de entrega directamente desde el ERP.

El módulo de Almacén > Entregas (ya implementado en Iteración 3) es el punto natural de integración.

---

## 2. Campos requeridos en el sistema

### 2.1 Tabla `entregas_almacen` — columnas a agregar

```sql
ALTER TABLE entregas_almacen
  ADD COLUMN requiere_envio     TINYINT(1)   DEFAULT 0         AFTER observaciones,
  ADD COLUMN tipo_envio         ENUM('Local','Paquetería')     DEFAULT 'Local' AFTER requiere_envio,
  ADD COLUMN guia_tres_guerras  VARCHAR(100) NULL              AFTER tipo_envio,
  ADD COLUMN estatus_paqueteria VARCHAR(50)  NULL              AFTER guia_tres_guerras,
  ADD COLUMN fecha_envio        DATETIME     NULL              AFTER estatus_paqueteria,
  ADD COLUMN fecha_entrega_real DATETIME     NULL              AFTER fecha_envio,
  ADD COLUMN tracking_url       VARCHAR(500) NULL              AFTER fecha_entrega_real,
  ADD COLUMN costo_envio        DECIMAL(10,2) DEFAULT 0.00     AFTER tracking_url,
  ADD COLUMN direccion_destino  TEXT          NULL             AFTER costo_envio,
  ADD COLUMN contacto_destino   VARCHAR(255)  NULL             AFTER direccion_destino,
  ADD COLUMN telefono_destino   VARCHAR(30)   NULL             AFTER contacto_destino;
```

### 2.2 Modelo de estatus de paquetería

| Valor | Descripción |
|-------|-------------|
| `Pendiente` | Guía aún no generada |
| `Guia Generada` | Guía creada en Tres Guerras |
| `En Tránsito` | Paquete en camino |
| `En Reparto` | Última milla |
| `Entregado` | Confirmado entregado |
| `Intento Fallido` | No se pudo entregar |
| `Devuelto` | Paquete devuelto |

---

## 3. Endpoints de la API Tres Guerras a consumir

> Los nombres de endpoints son tentativos y deben confirmarse con el equipo de Tres Guerras al inicio de la Iteración 4.

### 3.1 Crear guía de envío
```
POST /api/v1/envios/crear
Headers: Authorization: Bearer {API_TOKEN}
Body (JSON):
{
  "referencia_interna": "ENT-2026-0001",
  "destinatario": {
    "nombre": "...",
    "telefono": "...",
    "direccion": "...",
    "ciudad": "...",
    "estado": "...",
    "codigo_postal": "..."
  },
  "paquetes": [
    { "peso_kg": 19.0, "largo_cm": 30, "ancho_cm": 30, "alto_cm": 30, "descripcion": "Cubeta pintura" }
  ],
  "servicio": "express|estandar",
  "instrucciones": "..."
}
Response:
{
  "exito": true,
  "numero_guia": "3G-2026-XXXXX",
  "costo": 250.00,
  "url_tracking": "https://tresquerras.mx/track/3G-2026-XXXXX",
  "pdf_guia_url": "https://tresquerras.mx/guia/3G-2026-XXXXX.pdf"
}
```

### 3.2 Consultar estatus / tracking
```
GET /api/v1/envios/{numero_guia}/estatus
Headers: Authorization: Bearer {API_TOKEN}
Response:
{
  "numero_guia": "3G-2026-XXXXX",
  "estatus": "En Tránsito",
  "ultima_actualizacion": "2026-09-07 10:30:00",
  "eventos": [
    { "fecha": "...", "descripcion": "Paquete recibido en almacén origen" },
    ...
  ]
}
```

### 3.3 Cancelar guía
```
DELETE /api/v1/envios/{numero_guia}
```

---

## 4. Punto de integración en el flujo

```
Obra/OV → Producción → Almacén > Entregas
                                    │
                          ¿Tipo envío = Paquetería?
                                    │
                            Sí → Generar Guía Tres Guerras (API)
                                    │
                          Guardar guía, costo, URL tracking
                                    │
                          Mostrar estatus en:
                            - detalle.php Obras (tab Entregas)
                            - Almacén > Entregas (tabla historial)
                            - Notificación al vendedor/cliente
```

---

## 5. Nuevos componentes a crear en Iteración 4

| Componente | Tipo | Descripción |
|------------|------|-------------|
| `application/libraries/TresGuerrasLib.php` | Librería | Cliente HTTP de la API |
| `application/models/Almacen/AlmacenModel::generar_guia_tres_guerras()` | Método | Llama API y guarda guía |
| `application/models/Almacen/AlmacenModel::consultar_tracking()` | Método | Polling de estatus |
| `application/controllers/almacen/Entregas::generar_guia_ajax()` | Endpoint | Gatillo desde UI |
| `application/controllers/almacen/Entregas::tracking_ajax()` | Endpoint | Refresco de estatus |
| Config key `tres_guerras_api_token` | Config | En `application/config/config.php` o tabla `configuracion_empresa` |

---

## 6. Requisitos previos para Iteración 4

- [ ] Convenio o plan tarifario activo con Tres Guerras
- [ ] Credenciales API (token) proporcionadas por Tres Guerras
- [ ] Confirmación de URL base y versión de la API
- [ ] Definición de qué tipos de entregas requieren paquetería (local vs. foráneo)

---

## 7. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| API no documentada / inestable | Solicitar sandbox/mock antes de implementar |
| Costo variable por envío | El campo `costo_envio` en BD permite registrarlo; no calcular automáticamente |
| Cancelaciones post-guía | Implementar endpoint DELETE y actualizar estatus local |

---

> **Nota:** Este documento es el entregable de diseño de la Iteración 3. La implementación de código se realizará en la **Iteración 4**.
