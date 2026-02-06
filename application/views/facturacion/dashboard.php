<div class="container-fluid p-0">

    <!-- Breadcrumb (Migas de pan) -->
    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
    <!-- Titulo de la pagina -->
    <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

    <?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-message">
            <?= $this->session->flashdata('success') ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-message">
            <?= $this->session->flashdata('error') ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            
            <?php if(!$conectado): ?>
                <div class="alert alert-warning" role="alert">
                    <div class="alert-message">
                         <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> No conectado a Facture App</h4>
                         <p>Para poder emitir facturas, necesitas conectar tu cuenta primero.</p>
                         <hr>
                         <div class="mb-0">
                             <a href="<?php echo base_url('facturacion/Facturas/conectar'); ?>" class="btn btn-warning text-dark">Conectar Ahora</a>
                         </div>
                    </div>
                </div>
            <?php else: ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Listado de Facturas</h5>
                        <div class="btn-group">
                            <button id="btn-sincronizar" class="btn btn-outline-primary"><i class="fas fa-sync"></i> Sincronizar Estatus</button>
                            <button id="btn-importar" class="btn btn-outline-info"><i class="fas fa-download"></i> Importar desde API</button>
                            <a href="<?php echo base_url('facturacion/Facturas/crear'); ?>" class="btn btn-success"><i class="fas fa-plus"></i> Nueva Factura</a>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <!-- Filtros -->
                        <form method="get" action="<?php echo base_url('facturacion/Facturas/index'); ?>" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $this->input->get('fecha_inicio'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control" value="<?php echo $this->input->get('fecha_fin'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Folio Fiscal (UUID)</th>
                                        <th>Serie/Folio</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>RFC</th>
                                        <th>Total</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($facturas)): ?>
                                        <?php foreach($facturas as $f): ?>
                                            <tr>
                                                <td><small class="text-muted"><?php echo substr($f['folio_fiscal'], 0, 8) . '...'; ?></small></td>
                                                <td><?php echo $f['serie'] . '-' . $f['folio']; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($f['fecha_emision'])); ?></td>
                                                <td><?php echo $f['cliente_nombre'] ?? $f['razon_social']; ?></td>
                                                <td><?php echo $f['cliente_rfc'] ?? $f['rfc']; ?></td>
                                                <td><strong>$<?php echo number_format($f['total'], 2); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($f['estatus'] == 'Emitida') ? 'success' : 'danger'; ?>">
                                                        <?php echo $f['estatus']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-info btn-detalle" data-id="<?php echo $f['id']; ?>" title="Ver Detalles"><i class="fas fa-eye"></i></button>

                                                        <?php if($f['pdf_path']): ?>
                                                            <a href="<?php echo base_url('facturacion/Facturas/descargar/' . $f['id'] . '/pdf'); ?>" class="btn btn-sm btn-outline-danger" title="Descargar PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($f['xml_path']): ?>
                                                            <a href="<?php echo base_url('facturacion/Facturas/descargar/' . $f['id'] . '/xml'); ?>" class="btn btn-sm btn-outline-success" title="Descargar XML" target="_blank"><i class="fas fa-file-code"></i></a>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Enviar por Correo (Pendiente)"><i class="fas fa-envelope"></i></button>
                                                        
                                                        <?php if($f['estatus'] == 'Emitida'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Cancelar"><i class="fas fa-ban"></i></button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No hay facturas registradas en este periodo.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
            
        </div>
      </div>
</div>

<!-- Modal Detalle Factura -->
<div class="modal fade" id="modal-detalle-factura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Emisor y Receptor</strong></h6>
                        <p class="mb-1"><strong>RFC Cliente:</strong> <span id="modal-rfc"></span></p>
                        <p class="mb-1"><strong>Razón Social:</strong> <span id="modal-razon-social"></span></p>
                        <p class="mb-1"><strong>Régimen Fiscal:</strong> <span id="modal-regimen"></span></p>
                        <p><strong>CP:</strong> <span id="modal-cp"></span></p>
                    </div>
                    <div class="col-md-6 border-start">
                        <h6><strong>Datos de Factura</strong></h6>
                        <p class="mb-1"><strong>Folio:</strong> <span id="modal-serie-folio"></span></p>
                        <p class="mb-1"><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
                        <p class="mb-1"><strong>Folio Fiscal:</strong> <br><small class="text-muted" id="modal-uuid"></small></p>
                        <p><strong>Estatus:</strong> <span id="modal-estatus"></span></p>
                    </div>
                </div>
                <hr>
                <div class="row text-end">
                    <div class="col-12">
                        <h5>Total: <strong class="text-primary" id="modal-total"></strong></h5>
                        <small class="text-muted">Subtotal: <span id="modal-subtotal"></span> | IVA: <span id="modal-iva"></span></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="btn-modal-pdf" target="_blank" class="btn btn-outline-danger"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a href="#" id="btn-modal-xml" target="_blank" class="btn btn-outline-success"><i class="fas fa-file-code"></i> Descargar XML</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handler para botones de detalle
    document.querySelectorAll('.btn-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            
            // Mostrar loading o limpiar
            // ...

            fetch('<?= base_url("facturacion/Facturas/get_details/") ?>' + id)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        const f = data.factura;
                        
                        document.getElementById('modal-rfc').textContent = f.rfc;
                        document.getElementById('modal-razon-social').textContent = f.razon_social;
                        document.getElementById('modal-regimen').textContent = f.regimen_fiscal;
                        document.getElementById('modal-cp').textContent = f.codigo_postal;
                        
                        document.getElementById('modal-serie-folio').textContent = f.serie + '-' + f.folio;
                        document.getElementById('modal-fecha').textContent = f.fecha_emision;
                        document.getElementById('modal-uuid').textContent = f.folio_fiscal;
                        document.getElementById('modal-estatus').textContent = f.estatus;
                        
                        document.getElementById('modal-total').textContent = '$' + f.total;
                        document.getElementById('modal-subtotal').textContent = '$' + f.subtotal;
                        document.getElementById('modal-iva').textContent = '$' + f.iva;
                        
                        const btnPdf = document.getElementById('btn-modal-pdf');
                        const btnXml = document.getElementById('btn-modal-xml');
                        
                        btnPdf.href = f.pdf_url;
                        btnXml.href = f.xml_url;

                        const modal = new bootstrap.Modal(document.getElementById('modal-detalle-factura'));
                        modal.show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => console.error(err));
        });
    });
    // Handler para sincronizar
    document.getElementById('btn-sincronizar').addEventListener('click', function() {
        if(!confirm('Esto verificará el estatus de todas las facturas locales contra el SAT/Facture App. ¿Continuar?')) return;
        
        const btn = this;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

        fetch('<?= base_url("facturacion/Facturas/sincronizar") ?>', { headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(response => response.json())
            .then(data => {
                let msg = data.message;
                if(data.detalles && data.detalles.length > 0) {
                    msg += '\n\nDetalles:\n' + data.detalles.join('\n');
                }
                alert(msg);
                if(data.status === 'success') location.reload();
            })
            .catch(err => {
                console.error(err);
                alert('Error al sincronizar.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = original;
            });
    });

    // Handler para importar
    document.getElementById('btn-importar').addEventListener('click', function() {
        if(!confirm('Esto descargará las facturas de Facture App que no existan localmente. ¿Continuar?')) return;
        
        const btn = this;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importando...';

        fetch('<?= base_url("facturacion/Facturas/importar_facturas") ?>', { headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(response => response.json())
            .then(data => {
                let msg = data.message;
                if(data.detalles && data.detalles.length > 0) {
                    msg += '\n\nDetalles:\n' + data.detalles.join('\n');
                }
                alert(msg);
                if(data.status === 'success') location.reload();
            })
            .catch(err => {
                console.error(err);
                alert('Error al importar.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = original;
            });
    });
});
</script>
