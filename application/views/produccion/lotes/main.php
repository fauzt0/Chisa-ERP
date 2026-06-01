<div class="container-fluid p-0">
    <div class="row mb-2 mb-xl-3">
        <div class="col-auto d-none d-sm-block">
            <h3><strong>Control de Lotes</strong> Historial de Fabricación</h3>
        </div>

        <div class="col-auto ms-auto text-end mt-n1">
            <a href="<?=base_url()?>produccion/Dashboard" class="btn btn-primary">
                <i class="fas fa-industry"></i> Ir a Fabricación
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Listado Global de Lotes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla_lotes" class="table table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código de Barras</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Fecha Fabricación</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver Etiqueta -->
<div class="modal fade" id="modalEtiqueta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-barcode"></i> Etiqueta de Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="etiqueta_body">
                <div class="p-4">
                    <h4 id="etiqueta_producto"></h4>
                    <svg id="barcode_full"></svg>
                    <h2 id="etiqueta_codigo" class="mt-2"></h2>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" id="btn_imprimir_modal">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    let tabla;
    let currentLoteId = null;

    $(document).ready(function() {
        tabla = $('#tabla_lotes').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [[5, "desc"]], // Ajustado por nueva columna Origen
            "ajax": {
                "url": "<?=base_url()?>produccion/Lotes/lista_ajax",
                "type": "POST"
            },
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "columns": [
                { "width": "5%" },
                { "width": "15%" },
                { "width": "25%" },
                { "width": "10%" },
                { "width": "15%" },
                { "width": "10%" },
                { "width": "10%" },
                { "width": "10%", "orderable": false }
            ],
            "responsive": true
        });
    });

    function verEtiqueta(id, codigo) {
        currentLoteId = id;
        $('#etiqueta_codigo').text(codigo);
        $('#modalEtiqueta').modal('show');
        
        // Cargar nombre del producto desde la tabla para ahorrar una llamada extra
        const row = $(`#tabla_lotes tr:contains("${codigo}")`);
        const nombreProd = row.find('td:eq(2)').html().split('<br>')[0];
        $('#etiqueta_producto').text(nombreProd);

        setTimeout(() => {
            JsBarcode("#barcode_full", codigo, {
                format: "CODE128",
                width: 2,
                height: 100,
                displayValue: false
            });
        }, 300);
    }

    $('#btn_imprimir_modal').click(function() {
        if (currentLoteId) {
            window.open('<?=base_url()?>produccion/Dashboard/etiqueta_lote/' + currentLoteId, '_blank');
        }
    });

    function reloadTable() {
        tabla.ajax.reload(null, false);
    }
</script>

<style>
    .stock-badge { font-size: 0.9rem; padding: 5px 10px; }
    #barcode_full { max-width: 100%; height: auto; }
</style>
