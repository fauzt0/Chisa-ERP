<style>
.recibos-nomina-wrap { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #222; }
.recibos-nomina-wrap .recibo {
  background: #fff; max-width: 720px; margin: 0 auto 24px; padding: 28px 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,.08); border-top: 4px solid #1e3a5f;
  page-break-after: always;
}
.recibos-nomina-wrap .recibo:last-child { page-break-after: auto; margin-bottom: 0; }
.recibos-nomina-wrap .header { text-align: center; border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 16px; }
.recibos-nomina-wrap .header h1 { margin: 0; font-size: 15pt; color: #1e3a5f; letter-spacing: .5px; }
.recibos-nomina-wrap .header h2 { margin: 6px 0 0; font-size: 12pt; font-weight: 600; color: #333; }
.recibos-nomina-wrap .header p { margin: 4px 0 0; font-size: 9pt; color: #666; }
.recibos-nomina-wrap .folio-recibo { text-align: right; font-size: 9pt; color: #555; margin-bottom: 12px; }
.recibos-nomina-wrap .meta { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 14px; font-size: 10pt; flex-wrap: wrap; }
.recibos-nomina-wrap .meta table td { padding: 2px 8px 2px 0; vertical-align: top; }
.recibos-nomina-wrap .empleado-nombre { font-size: 13pt; font-weight: bold; margin-bottom: 10px; color: #1e3a5f; }
.recibos-nomina-wrap table.conceptos { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
.recibos-nomina-wrap table.conceptos th,
.recibos-nomina-wrap table.conceptos td { border: 1px solid #ddd; padding: 6px 10px; }
.recibos-nomina-wrap table.conceptos th { background: #f5f7fa; font-size: 9pt; text-transform: uppercase; text-align: left; }
.recibos-nomina-wrap table.conceptos td.monto { text-align: right; white-space: nowrap; }
.recibos-nomina-wrap table.conceptos tr.percepcion td:first-child { color: #155724; }
.recibos-nomina-wrap table.conceptos tr.deduccion td:first-child { color: #842029; }
.recibos-nomina-wrap .totales { border: 1px solid #1e3a5f; padding: 12px 14px; background: #f8fafc; margin-bottom: 14px; }
.recibos-nomina-wrap .totales table { width: 100%; font-size: 10.5pt; }
.recibos-nomina-wrap .totales td { padding: 3px 0; }
.recibos-nomina-wrap .totales td:last-child { text-align: right; font-weight: 600; }
.recibos-nomina-wrap .totales tr.pago td { font-size: 13pt; color: #1e3a5f; border-top: 2px solid #1e3a5f; padding-top: 8px; }
.recibos-nomina-wrap .totales tr.pendiente td { color: #856404; }
.recibos-nomina-wrap .leyenda { font-size: 8.5pt; color: #666; margin-top: 8px; line-height: 1.4; }
.recibos-nomina-wrap .firma { margin-top: 36px; display: flex; justify-content: space-between; gap: 20px; }
.recibos-nomina-wrap .firma-linea { flex: 1; border-top: 1px solid #999; text-align: center; padding-top: 6px; font-size: 9pt; color: #666; }
.recibos-nomina-wrap .recibos-empty { text-align: center; padding: 40px; color: #666; background: #fff; border-radius: 8px; }
</style>
