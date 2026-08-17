// @ts-check
/**
 * E2E — Módulo rh/Nomina (ERP Chisa Recubrimientos)
 * ---------------------------------------------------------------
 * Flujo principal automatizado:
 *   1. Login con credenciales de administrador/demo.
 *   2. Navegar a /rh/Nomina.
 *   3. Crear una nómina nueva (periodo futuro único, para no chocar con datos demo).
 *   4. Localizarla en la tabla (filtro por periodo) y calcularla.
 *   5. Abrir el detalle y verificar que los montos se calcularon.
 *   6. Abrir "Procesar Pago" y verificar el modal.
 *   7a. Por defecto: cancelar la nómina de prueba (limpieza automática, motivo >= 10 chars).
 *   7b. Si E2E_PAGAR_COMPLETO=1: pagarla en su totalidad y verificar estatus "Pagada"
 *       + exportar el Excel (deja el registro permanente en la base de datos).
 *
 * Notas importantes:
 * - Este módulo usa `window.confirm()` nativo del navegador en varios puntos
 *   (crear, calcular). Se maneja con `page.once('dialog', ...)`.
 * - No hay protección CSRF activa en este entorno (csrf_protection = FALSE),
 *   por lo que no se requiere extraer tokens.
 * - Contra un entorno productivo, este test SÍ inserta registros reales
 *   (tabla `nominas`, `nominas_detalle`, etc.). Por eso el comportamiento
 *   por defecto es "crear -> calcular -> revisar -> cancelar" (no deja
 *   basura visible en Pendientes/Pagadas). Ver README.md para más detalle.
 */

const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const ERP_USER = process.env.ERP_USER || 'presentacion@chisa.mx';
const ERP_PASS = process.env.ERP_PASS || 'Demo2026!';
const PAGAR_COMPLETO = process.env.E2E_PAGAR_COMPLETO === '1';

const SCREENSHOT_DIR = path.join(__dirname, '..', 'results', 'screenshots');

function ensureDir(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

async function shot(page, name) {
  ensureDir(SCREENSHOT_DIR);
  const file = path.join(SCREENSHOT_DIR, `${name}.png`);
  await page.screenshot({ path: file, fullPage: true });
  await test.info().attach(name, { path: file, contentType: 'image/png' });
}

/** Devuelve el lunes de la semana N a partir de hoy (para elegir un periodo sin colisión). */
function lunesDentroDeNSemanas(semanas) {
  const d = new Date();
  d.setHours(0, 0, 0, 0);
  d.setDate(d.getDate() + semanas * 7);
  const dia = d.getDay(); // 0=domingo ... 6=sábado
  const offsetALunes = dia === 0 ? 1 : (8 - dia) % 7;
  d.setDate(d.getDate() + offsetALunes);
  return d;
}

function iso(d) {
  return d.toISOString().slice(0, 10);
}

function fechaBonita(d) {
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

test.describe.configure({ mode: 'serial' });

test('Flujo principal: crear, calcular y procesar el pago de una nómina', async ({ page }) => {
  test.setTimeout(120_000);

  // Periodo semanal muy alejado en el futuro (52 semanas) para minimizar colisión
  // con nóminas demo/reales ya existentes en la base de datos.
  const lunes = lunesDentroDeNSemanas(52);
  const domingo = new Date(lunes);
  domingo.setDate(domingo.getDate() + 6);

  const periodoInicio = iso(lunes);
  const periodoFin = iso(domingo);
  const fechaPago = periodoFin;

  let nominaId = null;

  await test.step('1. Login con credenciales de administrador', async () => {
    await page.goto('/admin');
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await page.fill('input[name="username"]', ERP_USER);
    await page.fill('input[name="password"]', ERP_PASS);

    await Promise.all([
      page.waitForURL('**/dashboard**', { timeout: 20_000 }),
      page.click('button[type="submit"]'),
    ]);

    await expect(page).toHaveURL(/dashboard/);
    await shot(page, '01-login-dashboard');
  });

  await test.step('2. Navegar al módulo rh/Nomina', async () => {
    await page.goto('/rh/Nomina');
    await expect(page.locator('#tablaNominas')).toBeVisible();
    // Esperamos a que DataTables termine de pintar al menos una fila (datos demo existentes)
    await page.waitForSelector('#tablaNominas tbody tr', { timeout: 15_000 });
    await shot(page, '02-vista-principal-nomina');
  });

  await test.step('3. Abrir modal "Nueva Nómina" y completar el formulario', async () => {
    await page.click('button:has-text("Nueva Nómina")');
    await expect(page.locator('#modalNomina')).toBeVisible();

    await page.selectOption('#nomina_tipo', 'Semanal');
    await page.fill('#periodoInicio', periodoInicio);
    await page.fill('#periodoFin', periodoFin);
    await page.fill('#nomina_fecha_pago', fechaPago);

    await shot(page, '03-modal-nueva-nomina');
  });

  await test.step('4. Crear la nómina (confirmar creación, declinar auto-cálculo)', async () => {
    // Al crear con éxito, la app muestra un window.confirm() preguntando si se
    // desea calcular de inmediato. Lo declinamos para controlar cada paso.
    page.once('dialog', (dialog) => dialog.dismiss());

    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('rh/Nomina/crear_ajax') && r.status() === 200),
      page.click('#modalNomina button:has-text("Crear Nómina")'),
    ]);

    const json = await resp.json();
    expect(json.success, `crear_ajax debe responder success=true. Mensaje: ${json.message}`).toBeTruthy();
    expect(json.total_empleados, 'Debe haber al menos un empleado activo tipo Semanal').toBeGreaterThan(0);

    nominaId = json.nomina_id;
    console.log(`Nómina de prueba creada: id=${nominaId}, empleados=${json.total_empleados}, periodo=${periodoInicio} a ${periodoFin}`);

    await expect(page.locator('#modalNomina')).toBeHidden();
  });

  await test.step('5. Filtrar la tabla por el periodo creado y verificar estatus Borrador', async () => {
    await page.fill('#filtro_periodo_desde', periodoInicio);
    await page.dispatchEvent('#filtro_periodo_desde', 'change');
    await page.fill('#filtro_periodo_hasta', periodoFin);
    await page.dispatchEvent('#filtro_periodo_hasta', 'change');

    const fila = page.locator('#tablaNominas tbody tr', {
      hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
    });
    await expect(fila).toHaveCount(1, { timeout: 10_000 });
    await expect(fila).toContainText('Borrador');

    await shot(page, '04-nomina-creada-borrador');
  });

  await test.step('6. Calcular la nómina', async () => {
    const fila = page.locator('#tablaNominas tbody tr', {
      hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
    });

    // calcularNomina() dispara confirm() antes de calcular, y otro confirm()
    // después preguntando si se desea abrir "Procesar Pago" (lo declinamos).
    page.once('dialog', (dialog) => dialog.accept());

    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('rh/Nomina/calcular_ajax') && r.status() === 200),
      fila.locator('button[title="Calcular"]').click(),
    ]);
    const json = await resp.json();
    expect(json.success, `calcular_ajax debe responder success=true. Mensaje: ${json.message}`).toBeTruthy();

    // Segundo confirm ("¿Desea abrir Procesar Pago ahora?") -> lo declinamos
    page.once('dialog', (dialog) => dialog.dismiss());
    await page.waitForTimeout(300); // pequeño margen para que el confirm() aparezca y se resuelva

    await expect(fila).toContainText('Calculada', { timeout: 10_000 });
    await shot(page, '05-nomina-calculada');
  });

  await test.step('7. Abrir el detalle y verificar montos calculados', async () => {
    const fila = page.locator('#tablaNominas tbody tr', {
      hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
    });

    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('get_nomina_detalle_completo_ajax') && r.status() === 200),
      fila.locator('button[title="Ver / editar detalle"]').click(),
    ]);
    const detalle = await resp.json();
    expect(detalle.success).toBeTruthy();
    expect(Array.isArray(detalle.detalle) ? detalle.detalle.length : 0).toBeGreaterThan(0);

    await expect(page.locator('#modalDetalleNomina')).toBeVisible();
    await expect(page.locator('#detalleEstatus')).toContainText('Calculada');
    console.log(`Folio de la nómina de prueba: ${detalle.nomina.folio}`);

    await shot(page, '06-detalle-nomina-calculada');

    // Cerramos el modal de detalle para continuar con el flujo de pago
    await page.locator('#modalDetalleNomina .btn-close, #modalDetalleNomina [data-bs-dismiss="modal"]').first().click();
    await expect(page.locator('#modalDetalleNomina')).toBeHidden();
  });

  await test.step('8. Abrir "Procesar Pago" y verificar el listado de empleados pendientes', async () => {
    const fila = page.locator('#tablaNominas tbody tr', {
      hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
    });

    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('detalle_pago_ajax') && r.status() === 200),
      fila.locator('button[title="Procesar Pago"]').click(),
    ]);
    const json = await resp.json();
    expect(json.success).toBeTruthy();

    await expect(page.locator('#modalProcesarPago')).toBeVisible();
    await expect(page.locator('#pago-empleados-body tr')).not.toHaveCount(0);
    await shot(page, '07-modal-procesar-pago');
  });

  if (PAGAR_COMPLETO) {
    await test.step('9a. [E2E_PAGAR_COMPLETO=1] Pagar el 100% y verificar estatus Pagada', async () => {
      page.once('dialog', (dialog) => dialog.accept());
      const [resp] = await Promise.all([
        page.waitForResponse((r) => r.url().includes('pagar_ajax') && r.status() === 200),
        page.click('#modalProcesarPago button:has-text("Confirmar Pago")'),
      ]);
      const json = await resp.json();
      expect(json.success, `pagar_ajax debe responder success=true. Mensaje: ${json.message}`).toBeTruthy();

      const fila = page.locator('#tablaNominas tbody tr', {
        hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
      });
      await expect(fila).toContainText('Pagada', { timeout: 10_000 });
      await shot(page, '08-nomina-pagada');
    });

    await test.step('9b. Exportar el Excel de la nómina pagada', async () => {
      const fila = page.locator('#tablaNominas tbody tr', {
        hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
      });
      const [download] = await Promise.all([
        page.waitForEvent('download'),
        fila.locator('button[title="Exportar Excel"]').click(),
      ]);
      const savedPath = path.join(SCREENSHOT_DIR, '..', 'downloads', download.suggestedFilename());
      ensureDir(path.dirname(savedPath));
      await download.saveAs(savedPath);
      expect(fs.existsSync(savedPath)).toBeTruthy();
      console.log(`Excel exportado en: ${savedPath}`);
    });
  } else {
    await test.step('9. Limpieza: cancelar la nómina de prueba (motivo QA)', async () => {
      await page.locator('#modalProcesarPago .btn-close, #modalProcesarPago [data-bs-dismiss="modal"]').first().click();
      await expect(page.locator('#modalProcesarPago')).toBeHidden();

      const fila = page.locator('#tablaNominas tbody tr', {
        hasText: `${fechaBonita(lunes)} — ${fechaBonita(domingo)}`,
      });

      // pedirCancelarNomina() abre el modal #modalCancelarNomina (no un prompt nativo).
      await fila.locator('button[title="Cancelar nómina"]').click();
      await expect(page.locator('#modalCancelarNomina')).toBeVisible();
      await page.fill('#cancelar-motivo', 'Prueba automatizada E2E - limpieza de datos de test');

      const [resp] = await Promise.all([
        page.waitForResponse((r) => r.url().includes('eliminar_ajax') && r.status() === 200),
        page.click('#modalCancelarNomina button:has-text("Confirmar Cancelación")'),
      ]);
      const json = await resp.json();
      expect(json.success, `eliminar_ajax (cancelación) debe responder success=true. Mensaje: ${json.message}`).toBeTruthy();

      await expect(fila).toContainText('Cancelada', { timeout: 10_000 });
      await shot(page, '08-nomina-cancelada-limpieza');
    });
  }
});
