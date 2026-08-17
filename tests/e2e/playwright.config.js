// @ts-check
const { defineConfig, devices } = require('@playwright/test');

// Carga variables de entorno desde .env si existe (BASE_URL, ERP_USER, ERP_PASS)
require('dotenv').config({ path: require('path').join(__dirname, '.env') });

const BASE_URL = process.env.BASE_URL || 'https://erp.chisarecubrimientos.com.mx';

module.exports = defineConfig({
  testDir: './tests',
  timeout: 60_000,
  expect: { timeout: 10_000 },
  fullyParallel: false, // el flujo de nómina crea/modifica datos reales, evitar carreras
  workers: 1,
  retries: 0,
  reporter: [
    ['list'],
    ['html', { outputFolder: 'results/html-report', open: 'never' }],
  ],
  outputDir: 'results/artifacts',

  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 15_000,
    navigationTimeout: 30_000,
    ignoreHTTPSErrors: true,
    locale: 'es-MX',
    timezoneId: 'America/Chihuahua',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } },
    },
  ],
});
