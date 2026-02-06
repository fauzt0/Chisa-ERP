<div class="container-fluid p-0">
    <!-- Breadcrumb (Migas de pan) -->
    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
    <!-- Titulo de la pagina -->
    <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

    <!-- Mensajes de alerta -->
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

    <form action="<?= base_url('facturacion/Facturas/emitir') ?>" method="post" id="form-factura" class="needs-validation" novalidate>
        
        <!-- SECCIÓN 1: DATOS DE EMISIÓN -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Datos de Emisión</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Sucursal Emisora *</label>
                        <select name="sucursal_id" class="form-control mb-3" required>
                            <option value="">Seleccione una sucursal...</option>
                            <?php if(isset($sucursales['pagination']['items'])): ?>
                                <?php foreach($sucursales['pagination']['items'] as $sucursal): ?>
                                    <option value="<?= $sucursal['id'] ?>">
                                        <?= $sucursal['nombre'] ?> (<?= isset($sucursal['direccion']['codigopostal']) ? $sucursal['direccion']['codigopostal'] : 'S/CP' ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Serie (Opcional)</label>
                        <input type="text" name="serie" class="form-control mb-3" placeholder="Ej: A">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Folio (Opcional)</label>
                        <input type="text" name="folio" class="form-control mb-3" placeholder="Ej: 1001">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: CLIENTE (RECEPTOR) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cliente (Receptor)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Buscar Cliente</label>
                        <div class="input-group">
                            <input type="text" id="buscar_cliente" class="form-control" placeholder="Escriba nombre o RFC del cliente...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <small class="text-muted">Busque y seleccione para autocompletar.</small>
                        <div id="lista_clientes" class="list-group position-absolute w-100" style="z-index: 1000; display:none;"></div>
                    </div>
                    
                    <div class="col-md-6"></div> <!-- Espaciador -->

                    <div class="col-md-4 mb-3">
                        <label class="form-label">RFC *</label>
                        <input type="text" name="rfc_receptor" id="rfc_receptor" class="form-control" required readonly>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nombre / Razón Social *</label>
                        <input type="text" name="nombre_receptor" id="nombre_receptor" class="form-control" required readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">C.P. Fiscal *</label>
                        <input type="text" name="cp_receptor" id="cp_receptor" class="form-control" required>
                    </div>
                    <div class="col-md-9 mb-3">
                        <label class="form-label">Régimen Fiscal *</label>
                        <select name="regimen_fiscal" id="regimen_fiscal" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($regimenes as $key => $val): ?>
                                <option value="<?= $key ?>"><?= $key ?> - <?= $val ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: DETALLES DE FACTURA -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalles del Comprobante</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Uso CFDI *</label>
                        <select name="uso_cfdi" class="form-control" required>
                            <?php foreach($usos_cfdi as $key => $val): ?>
                                <option value="<?= $key ?>" <?= $key == 'G03' ? 'selected' : '' ?>>
                                    <?= $key ?> - <?= $val ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione Uso CFDI.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Método de Pago *</label>
                        <select name="metodo_pago" class="form-control" required>
                            <?php foreach($metodos_pago as $key => $val): ?>
                                <option value="<?= $key ?>" <?= $key == 'PUE' ? 'selected' : '' ?>>
                                    <?= $key ?> - <?= $val ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione Método de Pago.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Forma de Pago *</label>
                        <select name="forma_pago" class="form-control" required>
                             <?php foreach($formas_pago as $key => $val): ?>
                                <option value="<?= $key ?>" <?= $key == '01' ? 'selected' : '' ?>>
                                    <?= $key ?> - <?= $val ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione Forma de Pago.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                         <label class="form-label">Exportación *</label>
                        <select name="exportacion" class="form-control" required>
                            <?php foreach($exportaciones as $key => $val): ?>
                                <option value="<?= $key ?>"><?= $key ?> - <?= $val ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione si es exportación.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Moneda</label>
                        <input type="text" name="moneda" class="form-control" value="MXN" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: CONCEPTOS -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Conceptos</h5>
                <button type="button" class="btn btn-primary btn-sm" id="btn-add-concepto">
                    <i class="fas fa-plus"></i> Agregar Concepto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabla-conceptos">
                        <thead class="table-dark">
                            <tr>
                                <th width="10%">Cant.</th>
                                <th width="10%">Unidad</th>
                                <th width="10%">Clave SAT</th>
                                <th width="30%">Descripción</th>
                                <th width="15%">P. Unitario</th>
                                <th width="15%">Importe</th>
                                <th width="15%">Obj. Imp</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se agregarán dinámicamente -->
                        </tbody>
                        <tfoot class="table-light">
                             <tr>
                                <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end" id="lbl-subtotal">$0.00</td>
                                <td colspan="2"></td>
                            </tr>
                             <tr>
                                <td colspan="5" class="text-end"><strong>IVA (16%):</strong></td>
                                <td class="text-end" id="lbl-iva">$0.00</td>
                                <td colspan="2"></td>
                            </tr>
                             <tr>
                                <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end" id="lbl-total"><strong>$0.00</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mb-3 text-end">
             <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-paper-plane"></i> Emitir Factura
            </button>
        </div>

    </form>
</div>

<!-- Plantilla para fila de conceptos (Oculta) -->
<template id="template-concepto">
    <tr>
        <td>
            <input type="number" name="conceptos[{index}][cantidad]" class="form-control input-cantidad" value="1" min="0.01" step="0.01" required>
        </td>
        <td>
            <input type="text" name="conceptos[{index}][unidad]" class="form-control" value="H87" placeholder="H87" required>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="text" name="conceptos[{index}][clave]" class="form-control input-clave-sat" value="01010101" placeholder="01010101" required>
                <button class="btn btn-outline-secondary btn-buscar-sat-row" type="button" title="Buscar Clave SAT"><i class="fas fa-search"></i></button>
            </div>
        </td>
        <td>
            <div class="position-relative">
                <input type="text" name="conceptos[{index}][descripcion]" class="form-control input-descripcion" placeholder="Buscar producto..." required autocomplete="off">
                 <div class="list-group position-absolute w-100 lista-productos" style="z-index: 1000; display:none;"></div>
            </div>
        </td>
        <td>
            <input type="number" name="conceptos[{index}][valor_unitario]" class="form-control input-precio" min="0" step="0.01" required>
        </td>
        <td>
            <input type="text" class="form-control input-importe" readonly>
        </td>
        <td>
             <select name="conceptos[{index}][objeto_imp]" class="form-control form-select-sm">
                <?php foreach($objetos_imp as $key => $val): ?>
                    <option value="<?= $key ?>" <?= $key == '02' ? 'selected' : '' ?>><?= $key ?> - <?= $val ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</template>

<!-- Modal Buscador Claves SAT -->
<div class="modal fade" id="modal-claves-sat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscador de Claves SAT (Productos y Servicios)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="input-busqueda-sat" class="form-control" placeholder="Escriba palabra clave (ej. Pintura, Servicio)...">
                    <button class="btn btn-primary" type="button" id="btn-buscar-sat">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                <div class="text-end mb-2">
                    <small class="text-muted">Mostrando sugerencias comunes. <a href="http://pys.sat.gob.mx/PyS/catPyS.aspx" target="_blank">Ir al catálogo oficial del SAT <i class="fas fa-external-link-alt"></i></a></small>
                </div>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Descripción</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resultados-sat">
                            <tr>
                                <td colspan="3" class="text-center text-muted">Escriba para buscar o ver sugerencias...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Éxito Emisión -->
<div class="modal fade" id="modal-exito-factura" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> ¡Factura Emitida Correctamente!</h5>
            </div>
            <div class="modal-body text-center">
                <p class="lead mb-4">La factura ha sido certificada por el SAT.</p>
                
                <h6 class="mb-3">¿Qué deseas hacer ahora?</h6>
                
                <div class="d-grid gap-2 col-8 mx-auto">
                    <a href="#" id="btn-exito-pdf" target="_blank" class="btn btn-outline-danger btn-lg"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                    <a href="#" id="btn-exito-xml" target="_blank" class="btn btn-outline-success btn-lg"><i class="fas fa-file-code"></i> Descargar XML</a>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="<?= base_url('facturacion/Facturas/index') ?>" class="btn btn-primary">Terminar y Salir</a>
                <a href="<?= base_url('facturacion/Facturas/crear') ?>" class="btn btn-secondary">Nueva Factura</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- VALIDACIÓN BOOTSTRAP ---
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
        })

    // FORMULARIO CON AJAX
    const formFactura = document.getElementById('form-factura');
    formFactura.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!formFactura.checkValidity()) {
            formFactura.classList.add('was-validated');
            return;
        }

        // Mostrar loading...
        const btnSubmit = formFactura.querySelector('button[type="submit"]');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Emitiendo...';

        const formData = new FormData(formFactura);

        fetch(formFactura.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Configurar modal
                document.getElementById('btn-exito-pdf').href = data.pdf_url;
                document.getElementById('btn-exito-xml').href = data.xml_url;
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modal-exito-factura'));
                modal.show();
            } else {
                alert('Error al emitir factura: ' + data.message + (data.error ? '\n' + data.error : ''));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Ocurrió un error inesperado al procesar la solicitud.');
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
        });

    });
        })

    let conceptoIndex = 0;
    let currentInputClave = null; // Almacena qué input abrió el modal
    let commonKeys = [];

    // Cargar claves comunes al inicio
    fetch('<?= base_url("assets/data/common_sat_keys.json") ?>')
        .then(response => response.json())
        .then(data => {
            commonKeys = data;
        })
        .catch(err => console.error('Error cargando claves SAT:', err));

    // --- FUNCIONES DE CÁLCULO ---
    function calcularTotales() {
        let subtotal = 0;
        let iva = 0;

        document.querySelectorAll('#tabla-conceptos tbody tr').forEach(row => {
            const cantidad = parseFloat(row.querySelector('.input-cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.input-precio').value) || 0;
            const importe = cantidad * precio;
            
            row.querySelector('.input-importe').value = importe.toFixed(2);
            
            subtotal += importe;
            
            const objImp = row.querySelector('select[name*="[objeto_imp]"]').value;
            if (objImp === '02') {
                iva += importe * 0.16;
            }
        });

        const total = subtotal + iva;

        document.getElementById('lbl-subtotal').textContent = '$' + subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2});
        document.getElementById('lbl-iva').textContent = '$' + iva.toLocaleString('es-MX', {minimumFractionDigits: 2});
        document.getElementById('lbl-total').textContent = '$' + total.toLocaleString('es-MX', {minimumFractionDigits: 2});
    }

    // --- AGREGAR CONCEPTO ---
    function agregarConcepto(producto = null) {
        const template = document.getElementById('template-concepto').innerHTML;
        const html = template.replace(/{index}/g, conceptoIndex++);
        const tbody = document.querySelector('#tabla-conceptos tbody');
        
        const tempDiv = document.createElement('tbody');
        tempDiv.innerHTML = html;
        const newRow = tempDiv.firstElementChild;
        
        if (producto) {
            newRow.querySelector('.input-descripcion').value = producto.nombre;
            newRow.querySelector('.input-precio').value = producto.precio;
        }

        tbody.appendChild(newRow);
        
        newRow.querySelectorAll('.input-cantidad, .input-precio, select[name*="[objeto_imp]"]').forEach(input => {
            input.addEventListener('input', calcularTotales);
            input.addEventListener('change', calcularTotales);
        });

        newRow.querySelector('.btn-remove').addEventListener('click', function() {
            newRow.remove();
            calcularTotales();
        });

        // Evento para abrir modal SAT
        newRow.querySelector('.btn-buscar-sat-row').addEventListener('click', function() {
            currentInputClave = newRow.querySelector('.input-clave-sat');
            const modal = new bootstrap.Modal(document.getElementById('modal-claves-sat'));
            modal.show();
            mostrarResultadosSAT(''); // Mostrar todas las comunes al abrir
        });

        setupProductoAutocomplete(newRow.querySelector('.input-descripcion'));

        calcularTotales();
    }
    
    // Función Buscador SAT
    const inputBusquedaSat = document.getElementById('input-busqueda-sat');
    const btnBuscarSat = document.getElementById('btn-buscar-sat');
    const tablaResultadosSat = document.getElementById('tabla-resultados-sat');

    function mostrarResultadosSAT(term) {
        tablaResultadosSat.innerHTML = '';
        const search = term.toLowerCase();
        
        const filtered = commonKeys.filter(k => 
            k.descripcion.toLowerCase().includes(search) || 
            k.clave.includes(search)
        );

        if (filtered.length === 0) {
            tablaResultadosSat.innerHTML = '<tr><td colspan="3" class="text-center">No se encontraron resultados en sugerencias. <a href="http://pys.sat.gob.mx/PyS/catPyS.aspx" target="_blank">Consulte el catálogo oficial</a></td></tr>';
            return;
        }

        filtered.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${item.clave}</strong></td>
                <td>${item.descripcion}</td>
                <td><button class="btn btn-sm btn-success btn-select-sat" data-clave="${item.clave}">Seleccionar</button></td>
            `;
            tr.querySelector('.btn-select-sat').addEventListener('click', function() {
                if(currentInputClave) {
                    currentInputClave.value = this.dataset.clave;
                    // También podríamos intentar adivinar la unidad, pero por ahora solo clave
                }
                const modalEl = document.getElementById('modal-claves-sat');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();
            });
            tablaResultadosSat.appendChild(tr);
        });
    }

    btnBuscarSat.addEventListener('click', () => mostrarResultadosSAT(inputBusquedaSat.value));
    inputBusquedaSat.addEventListener('keyup', (e) => {
        if(e.key === 'Enter') mostrarResultadosSAT(inputBusquedaSat.value);
        else mostrarResultadosSAT(inputBusquedaSat.value);
    });

    agregarConcepto();
    document.getElementById('btn-add-concepto').addEventListener('click', () => agregarConcepto());

    // ... (Mantener código de autocompletado de clientes existente) ...
    // --- AUTOCOMPLETADO CLIENTES ---
    const inputCliente = document.getElementById('buscar_cliente');
    const listaClientes = document.getElementById('lista_clientes');

    inputCliente.addEventListener('input', function() {
        const term = this.value;
        if (term.length < 2) {
            listaClientes.style.display = 'none';
            return;
        }

        fetch('<?= base_url("facturacion/Facturas/buscar_cliente") ?>?term=' + encodeURIComponent(term))
            .then(response => response.json())
            .then(data => {
                listaClientes.innerHTML = '';
                if (data.length > 0) {
                    listaClientes.style.display = 'block';
                    data.forEach(item => {
                        const a = document.createElement('a');
                        a.className = 'list-group-item list-group-item-action';
                        a.href = '#';
                        a.innerHTML = `<strong>${item.value}</strong> <small class="text-muted">${item.rfc}</small>`;
                        a.addEventListener('click', (e) => {
                            e.preventDefault();
                            document.getElementById('rfc_receptor').value = item.rfc;
                            document.getElementById('nombre_receptor').value = item.value;
                            document.getElementById('cp_receptor').value = item.cp || '';
                            if(item.regimen) {
                                let regimenCode = item.regimen.split(' ')[0];
                                const selectRegimen = document.getElementById('regimen_fiscal');
                                selectRegimen.value = regimenCode;
                            }
                            inputCliente.value = '';
                            listaClientes.style.display = 'none';
                        });
                        listaClientes.appendChild(a);
                    });
                } else {
                    listaClientes.style.display = 'none';
                }
            });
    });

    function setupProductoAutocomplete(input) {
        const lista = input.nextElementSibling; 
        input.addEventListener('input', function() {
            const term = this.value;
            if (term.length < 2) {
                lista.style.display = 'none';
                return;
            }
            fetch('<?= base_url("facturacion/Facturas/buscar_producto") ?>?term=' + encodeURIComponent(term))
                .then(response => response.json())
                .then(data => {
                    lista.innerHTML = '';
                    if (data.length > 0) {
                        lista.style.display = 'block';
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.className = 'list-group-item list-group-item-action';
                            a.href = '#';
                            a.innerHTML = `<strong>${item.value}</strong> - $${item.precio || '0.00'}`;
                            a.addEventListener('click', (e) => {
                                e.preventDefault();
                                input.value = item.value;
                                const row = input.closest('tr');
                                if(item.precio) row.querySelector('.input-precio').value = item.precio;
                                lista.style.display = 'none';
                                calcularTotales();
                            });
                            lista.appendChild(a);
                        });
                    } else {
                        lista.style.display = 'none';
                    }
                });
        });
        document.addEventListener('click', function(e) {
            if (e.target !== input) lista.style.display = 'none';
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target !== inputCliente) listaClientes.style.display = 'none';
    });
});
</script>
