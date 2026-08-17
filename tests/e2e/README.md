# Pruebas E2E — ERP Chisa Recubrimientos (Playwright)

Suite de pruebas automatizadas de extremo a extremo (navegador real) para el ERP.
Primera prueba incluida: **flujo principal de generación de nómina** (`rh/Nomina`).

## Requisitos

- Node.js 18+
- Acceso de red al ERP a probar (por defecto `https://erp.chisarecubrimientos.com.mx`)

## Instalación (una sola vez)

```bash
cd public_html/tests/e2e
npm install
npm run install:browsers   # descarga el navegador Chromium que usa Playwright
```

## Configuración

```bash
cp .env.example .env
# Edita .env si necesitas otra URL, otro usuario o forzar el pago completo
```

Variables disponibles (ver `.env.example`):

| Variable | Default | Descripción |
|---|---|---|
| `BASE_URL` | `https://erp.chisarecubrimientos.com.mx` | URL del ERP a probar |
| `ERP_USER` | `presentacion@chisa.mx` | Usuario con permisos de nómina |
| `ERP_PASS` | `Demo2026!` | Contraseña del usuario anterior |
| `E2E_PAGAR_COMPLETO` | `0` | Si es `1`, paga la nómina de prueba al 100% y exporta el Excel (deja el registro permanente como "Pagada"). Si es `0` (default), el test **cancela** la nómina de prueba al final (limpieza automática). |

## Ejecutar

```bash
npm test                # modo headless (sin ventana visible)
npm run test:headed     # con ventana de navegador visible (recomendado para ver el flujo)
npm run test:debug      # modo paso a paso del Inspector de Playwright
npm run report          # abre el último reporte HTML generado
```

Capturas de pantalla de cada paso quedan en `results/screenshots/`.
Trazas, video y reporte HTML (solo si algo falla) quedan en `results/`.

## Qué hace la prueba `tests/nomina-flujo.spec.js`

1. Inicia sesión en `/admin` con las credenciales configuradas.
2. Navega a `/rh/Nomina`.
3. Abre "Nueva Nómina", la define como **Semanal** con un periodo **52 semanas en el futuro**
   (para no chocar con nóminas demo/reales existentes) y la crea.
4. Localiza la nómina recién creada en la tabla (filtrando por el periodo) y la **calcula**.
5. Abre el detalle y valida que los montos por empleado se calcularon.
6. Abre "Procesar Pago" y valida el listado de empleados pendientes.
7. Por defecto: **cancela** la nómina de prueba (motivo de auditoría) para no dejar
   datos de prueba mezclados con nóminas reales. Con `E2E_PAGAR_COMPLETO=1` en su
   lugar la paga al 100 % y descarga el Excel de 3 hojas.

## Notas importantes

- Este ERP (CodeIgniter 3) usa `window.confirm()` nativo del navegador en varios
  puntos del flujo de nómina (crear, calcular, pagar). El script controla estos
  diálogos con `page.once('dialog', ...)`.
- No hay protección CSRF activa en este entorno (`csrf_protection = FALSE` en
  `application/config/config.php`), por lo que no se requiere extraer tokens.
- El script **inserta datos reales** en las tablas `nominas` / `nominas_detalle`
  del entorno indicado en `BASE_URL`. No lo ejecutes repetidamente contra producción
  sin revisar el resultado de la limpieza (paso 7). Si tu equipo cuenta con un
  entorno de *staging*/demo, es preferible apuntar `BASE_URL` ahí.
- El usuario `presentacion@chisa.mx` ya tiene otorgados los 5 permisos de nómina
  necesarios (`rh_nomina`, `rh_nomina_configurar`, `rh_nomina_editar_detalle`,
  `rh_nomina_cuentas`, `rh_nomina_exportar`) — ver `doc/VALIDACION_NOMINA_2026-08-04.md`.

## Siguiente paso recomendado

Después de correr esta prueba automática, ejecuta el checklist manual descrito en
la respuesta del agente (o en `doc/PRUEBAS_MANUALES_RH_2026-08-10.md`) para cubrir
validaciones visuales, PDFs, exportaciones y casos límite que un script no puede
verificar de forma confiable (contraste de colores, contenido exacto de PDFs, etc.).
