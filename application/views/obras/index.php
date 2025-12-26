<?php
$stats = $response['stats'] ?? [];
?>

<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item active">Obras</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Título y Botón Nueva Obra -->
<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="fas fa-hard-hat"></i> Gestión de Obras</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevaObra">
            <i class="fas fa-plus"></i> Nueva Obra
        </button>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?=$stats['total'] ?? 0?></h3>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 class="mb-0 text-warning"><?=$stats['planificación'] ?? 0?></h3>
                <small class="text-muted">Planificación</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-info">
            <div class="card-body text-center">
                <h3 class="mb-0 text-info"><?=$stats['en_cotización'] ?? 0?></h3>
                <small class="text-muted">En Cotización</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="mb-0 text-primary"><?=$stats['en_ejecución'] ?? 0?></h3>
                <small class="text-muted">En Ejecución</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="mb-0 text-success"><?=$stats['completada'] ?? 0?></h3>
                <small class="text-muted">Completadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <h3 class="mb-0 text-secondary"><?=$stats['pausada'] ?? 0?></h3>
                <small class="text-muted">Pausadas</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Filtrar por Estatus</label>
                        <select id="filtroEstatus" class="form-select">
                            <option value="todos">Todos los estatus</option>
                            <option value="Planificación">Planificación</option>
                            <option value="En Cotización">En Cotización</option>
                            <option value="Aprobada">Aprobada</option>
                            <option value="En Ejecución">En Ejecución</option>
                            <option value="Pausada">Pausada</option>
                            <option value="Completada">Completada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Obras -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Obras</h5>
            </div>
            <div class="card-body">
                <table id="tablaObras" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Nombre</th>
                            <th>Cliente</th>
                            <th>Ciudad</th>
                            <th>Estado</th>
                            <th>Estatus</th>
                            <th>Avance</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Obra -->
<div class="modal fade" id="modalNuevaObra" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nueva Obra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaObra">
                    <!-- Datos Generales -->
                    <h6 class="mb-3"><i class="fas fa-info-circle"></i> Datos Generales</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Obra <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <input type="hidden" name="cliente_id" id="clienteIdSeleccionado" required>
                            <div class="input-group">
                                <input type="text" class="form-control" id="clienteNombre" placeholder="Seleccionar cliente..." readonly required>
                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalBuscarCliente">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <!-- Ubicación -->
                    <h6 class="mb-3"><i class="fas fa-map-marker-alt"></i> Ubicación</h6>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Dirección <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="direccion" rows="2" required></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="ciudad">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" name="estado">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Código Postal</label>
                            <input type="text" class="form-control" name="codigo_postal">
                        </div>
                    </div>
                    
                    <!-- Datos Técnicos -->
                    <h6 class="mb-3"><i class="fas fa-tools"></i> Datos Técnicos</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Área Total (m²)</label>
                            <input type="number" step="0.01" class="form-control" name="area_total">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Superficie</label>
                            <input type="text" class="form-control" name="tipo_superficie">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" name="estatus">
                                <option value="Planificación" selected>Planificación</option>
                                <option value="En Cotización">En Cotización</option>
                                <option value="Aprobada">Aprobada</option>
                                <option value="En Ejecución">En Ejecución</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Condiciones Ambientales</label>
                            <textarea class="form-control" name="condiciones_ambientales" rows="2" placeholder="Temperatura, humedad, exposición, etc."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Especificaciones Técnicas</label>
                            <textarea class="form-control" name="especificaciones_tecnicas" rows="2" placeholder="Requisitos técnicos específicos"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha Inicio Estimada</label>
                            <input type="date" class="form-control" name="fecha_inicio_estimada">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Fin Estimada</label>
                            <input type="date" class="form-control" name="fecha_fin_estimada">
                        </div>
                    </div>
                    
                    <!-- Datos Financieros -->
                    <h6 class="mb-3"><i class="fas fa-dollar-sign"></i> Datos Financieros</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Costo Estimado</label>
                            <input type="number" step="0.01" class="form-control" name="costo_estimado" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Descuento (%)</label>
                            <input type="number" step="0.01" class="form-control" name="descuento_porcentaje" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">IVA (%)</label>
                            <input type="number" step="0.01" class="form-control" name="iva_porcentaje" value="16">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Anticipo (%)</label>
                            <input type="number" step="0.01" class="form-control" name="anticipo_porcentaje" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tiempo de Entrega</label>
                            <input type="text" class="form-control" name="tiempo_entrega" placeholder="ej: 15 días hábiles">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Condiciones de Pago</label>
                            <input type="text" class="form-control" name="condiciones_pago" placeholder="ej: 50% anticipo">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarObra()">
                    <i class="fas fa-save"></i> Guardar Obra
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscar Cliente -->
<div class="modal fade" id="modalBuscarCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-search"></i> Buscar Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" id="inputBuscarCliente" placeholder="Buscar por nombre, razón social o RFC...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Razón Social</th>
                                <th>Nombre Comercial</th>
                                <th>RFC</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaClientesBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">Escribe para buscar clientes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let tablaObras;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    tablaObras = $('#tablaObras').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?=base_url()?>obras/Obras/get_obras_ajax',
            type: 'GET',
            data: function(d) {
                d.estatus_filter = $('#filtroEstatus').val();
            }
        },
        columns: [
            { data: 'folio' },
            { data: 'nombre' },
            { data: 'cliente' },
            { data: 'ciudad' },
            { data: 'estado' },
            { 
                data: 'estatus',
                render: function(data) {
                    const badges = {
                        'Planificación': 'warning',
                        'En Cotización': 'info',
                        'Aprobada': 'success',
                        'En Ejecución': 'primary',
                        'Pausada': 'secondary',
                        'Completada': 'success',
                        'Cancelada': 'danger'
                    };
                    return `<span class="badge bg-${badges[data] || 'secondary'}">${data}</span>`;
                }
            },
            { 
                data: 'porcentaje_avance',
                render: function(data) {
                    return `
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: ${data}%">
                                ${data}%
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'fecha_creacion',
                render: function(data) {
                    return new Date(data).toLocaleDateString('es-MX');
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <a href="<?=base_url()?>obras/Obras/detalle/${row.id}" class="btn btn-sm btn-info" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="eliminarObra(${row.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
        },
        order: [[7, 'desc']]
    });
    
    // Recargar tabla al cambiar filtro
    $('#filtroEstatus').on('change', function() {
        tablaObras.ajax.reload();
    });
    
    // Cargar clientes cuando se abre el modal
    document.getElementById('modalBuscarCliente').addEventListener('shown.bs.modal', function() {
        document.getElementById('inputBuscarCliente').focus();
        cargarTodosClientes();
    });
    
    // Buscar clientes mientras se escribe
    let timeoutBusqueda;
    document.getElementById('inputBuscarCliente').addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            buscarClientes(this.value);
        }, 300);
    });
});

let clientesData = [];

function cargarTodosClientes() {
    fetch('<?=base_url()?>obras/Obras/get_clientes_ajax')
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            clientesData = data.clientes;
            mostrarClientes(clientesData);
        }
    })
    .catch(error => console.error('Error al cargar clientes:', error));
}

function buscarClientes(termino) {
    if(!termino || termino.length < 2) {
        mostrarClientes(clientesData);
        return;
    }
    
    const terminoLower = termino.toLowerCase();
    const clientesFiltrados = clientesData.filter(cliente => {
        return (cliente.razon_social && cliente.razon_social.toLowerCase().includes(terminoLower)) ||
               (cliente.nombre_comercial && cliente.nombre_comercial.toLowerCase().includes(terminoLower)) ||
               (cliente.rfc && cliente.rfc.toLowerCase().includes(terminoLower));
    });
    
    mostrarClientes(clientesFiltrados);
}

function mostrarClientes(clientes) {
    const tbody = document.getElementById('tablaClientesBody');
    
    if(clientes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No se encontraron clientes</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    clientes.forEach(cliente => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${cliente.razon_social || '-'}</td>
            <td>${cliente.nombre_comercial || '-'}</td>
            <td>${cliente.rfc || '-'}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="seleccionarCliente(${cliente.id}, '${cliente.razon_social}')">
                    <i class="fas fa-check"></i> Seleccionar
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function seleccionarCliente(clienteId, clienteNombre) {
    document.getElementById('clienteIdSeleccionado').value = clienteId;
    document.getElementById('clienteNombre').value = clienteNombre;
    
    // Cerrar modal de búsqueda
    const modalBuscar = bootstrap.Modal.getInstance(document.getElementById('modalBuscarCliente'));
    modalBuscar.hide();
    
    // Esperar a que se cierre completamente el modal de búsqueda y reabrir el modal de Nueva Obra
    document.getElementById('modalBuscarCliente').addEventListener('hidden.bs.modal', function() {
        const modalNuevaObra = new bootstrap.Modal(document.getElementById('modalNuevaObra'));
        modalNuevaObra.show();
    }, { once: true }); // El evento solo se ejecuta una vez
    
    // Limpiar búsqueda
    document.getElementById('inputBuscarCliente').value = '';
}

function guardarObra() {
    const formData = new FormData(document.getElementById('formNuevaObra'));
    
    fetch('<?=base_url()?>obras/Obras/guardar_ajax', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            $('#modalNuevaObra').modal('hide');
            document.getElementById('formNuevaObra').reset();
            tablaObras.ajax.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la obra');
    });
}

function eliminarObra(obraId) {
    if(!confirm('¿Está seguro de eliminar esta obra?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('obra_id', obraId);
    
    fetch('<?=base_url()?>obras/Obras/eliminar_ajax', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            tablaObras.ajax.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la obra');
    });
}
</script>
