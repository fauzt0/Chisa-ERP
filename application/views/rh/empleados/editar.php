<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$emp = $response['empleado'];
?>

<style>
  .is-invalid {
    border-color: red !important;
  }
</style>

<div class="container-fluid p-0">
  <!-- Breadcrumb (Migas de pan) -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h2><i class="fa-solid fa-user-edit"></i> Editar Empleado: <?php echo $emp->numero_empleado; ?></h2>
        </div>
        <div class="card-body">

          <?php echo form_open_multipart('rh/RecursosHumanos/editar/'.$emp->id); ?>

          <!-- Tabs de navegación -->
          <ul class="nav nav-tabs" id="empleadoTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="personales-tab" data-bs-toggle="tab" data-bs-target="#personales" type="button">
                <i data-lucide="user"></i> Datos Personales
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="fiscales-tab" data-bs-toggle="tab" data-bs-target="#fiscales" type="button">
                <i data-lucide="file-text"></i> Datos Fiscales
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="laborales-tab" data-bs-toggle="tab" data-bs-target="#laborales" type="button">
                <i data-lucide="briefcase"></i> Datos Laborales
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="nomina-tab" data-bs-toggle="tab" data-bs-target="#nomina" type="button">
                <i data-lucide="dollar-sign"></i> Nómina
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="contacto-tab" data-bs-toggle="tab" data-bs-target="#contacto" type="button">
                <i data-lucide="phone"></i> Contacto y Dirección
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="prestaciones-tab" data-bs-toggle="tab" data-bs-target="#prestaciones" type="button">
                <i data-lucide="shield"></i> Prestaciones
              </button>
            </li>
          </ul>

          <div class="tab-content mt-3" id="empleadoTabsContent">
            
            <!-- TAB 1: Datos Personales -->
            <div class="tab-pane fade show active" id="personales" role="tabpanel">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Número de Empleado</label>
                  <input type="text" class="form-control" value="<?php echo $emp->numero_empleado; ?>" disabled>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Nombre *</label>
                  <input type="text" class="form-control" name="nombre" value="<?php echo set_value('nombre', $emp->nombre); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Apellido Paterno *</label>
                  <input type="text" class="form-control" name="apellido_paterno" value="<?php echo set_value('apellido_paterno', $emp->apellido_paterno); ?>" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Apellido Materno</label>
                  <input type="text" class="form-control" name="apellido_materno" value="<?php echo set_value('apellido_materno', $emp->apellido_materno); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Fecha de Nacimiento</label>
                  <input type="date" class="form-control" name="fecha_nacimiento" value="<?php echo set_value('fecha_nacimiento', $emp->fecha_nacimiento); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Género</label>
                  <select class="form-select" name="genero">
                    <option value="">Seleccionar...</option>
                    <option value="M" <?php echo set_select('genero', 'M', $emp->genero == 'M'); ?>>Masculino</option>
                    <option value="F" <?php echo set_select('genero', 'F', $emp->genero == 'F'); ?>>Femenino</option>
                    <option value="Otro" <?php echo set_select('genero', 'Otro', $emp->genero == 'Otro'); ?>>Otro</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Estado Civil</label>
                  <select class="form-select" name="estado_civil">
                    <option value="">Seleccionar...</option>
                    <option value="Soltero" <?php echo set_select('estado_civil', 'Soltero', $emp->estado_civil == 'Soltero'); ?>>Soltero</option>
                    <option value="Casado" <?php echo set_select('estado_civil', 'Casado', $emp->estado_civil == 'Casado'); ?>>Casado</option>
                    <option value="Divorciado" <?php echo set_select('estado_civil', 'Divorciado', $emp->estado_civil == 'Divorciado'); ?>>Divorciado</option>
                    <option value="Viudo" <?php echo set_select('estado_civil', 'Viudo', $emp->estado_civil == 'Viudo'); ?>>Viudo</option>
                    <option value="Union Libre" <?php echo set_select('estado_civil', 'Union Libre', $emp->estado_civil == 'Union Libre'); ?>>Unión Libre</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Teléfono</label>
                  <input type="tel" class="form-control" name="telefono" value="<?php echo set_value('telefono', $emp->telefono); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Email Personal</label>
                  <input type="email" class="form-control" name="email_personal" value="<?php echo set_value('email_personal', $emp->email_personal); ?>">
                </div>
              </div>
            </div>

            <!-- TAB 2: Datos Fiscales -->
            <div class="tab-pane fade" id="fiscales" role="tabpanel">
              <div class="alert alert-info">
                <i data-lucide="info"></i> Los datos fiscales (RFC, CURP, NSS) no se pueden modificar por seguridad.
              </div>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">RFC</label>
                  <input type="text" class="form-control" value="<?php echo $emp->rfc; ?>" disabled>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">CURP</label>
                  <input type="text" class="form-control" value="<?php echo $emp->curp; ?>" disabled>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">NSS (IMSS)</label>
                  <input type="text" class="form-control" value="<?php echo $emp->nss ?? 'N/A'; ?>" disabled>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">AFORE</label>
                  <input type="text" class="form-control" value="<?php echo $emp->afore ?? 'N/A'; ?>" disabled>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Número de Cuenta AFORE</label>
                  <input type="text" class="form-control" value="<?php echo $emp->afore_numero_cuenta ?? 'N/A'; ?>" disabled>
                </div>
              </div>
            </div>

            <!-- TAB 3: Datos Laborales -->
            <div class="tab-pane fade" id="laborales" role="tabpanel">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Tipo de Trabajador *</label>
                  <select class="form-select" name="tipo_trabajador" required>
                    <option value="">Seleccionar...</option>
                    <option value="Planta" <?php echo set_select('tipo_trabajador', 'Planta', $emp->tipo_trabajador == 'Planta'); ?>>Planta</option>
                    <option value="Temporal" <?php echo set_select('tipo_trabajador', 'Temporal', $emp->tipo_trabajador == 'Temporal'); ?>>Temporal</option>
                    <option value="Por Proyecto" <?php echo set_select('tipo_trabajador', 'Por Proyecto', $emp->tipo_trabajador == 'Por Proyecto'); ?>>Por Proyecto</option>
                    <option value="Honorarios" <?php echo set_select('tipo_trabajador', 'Honorarios', $emp->tipo_trabajador == 'Honorarios'); ?>>Honorarios</option>
                    <option value="Practicante" <?php echo set_select('tipo_trabajador', 'Practicante', $emp->tipo_trabajador == 'Practicante'); ?>>Practicante</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Departamento</label>
                  <select class="form-select" name="departamento_id">
                    <option value="">Seleccionar...</option>
                    <?php foreach($response['departamentos'] as $dept): ?>
                      <option value="<?php echo $dept->id; ?>" <?php echo set_select('departamento_id', $dept->id, $emp->departamento_id == $dept->id); ?>>
                        <?php echo $dept->nombre; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Puesto *</label>
                  <input type="text" class="form-control" name="puesto" value="<?php echo set_value('puesto', $emp->puesto); ?>" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Jefe Directo</label>
                  <select class="form-select" name="jefe_directo_id">
                    <option value="">Sin jefe directo</option>
                    <?php foreach($response['empleados'] as $empl): ?>
                      <option value="<?php echo $empl->id; ?>" <?php echo set_select('jefe_directo_id', $empl->id, $emp->jefe_directo_id == $empl->id); ?>>
                        <?php echo $empl->nombre_completo; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Fecha de Ingreso</label>
                  <input type="date" class="form-control" value="<?php echo $emp->fecha_ingreso; ?>" disabled>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Estatus *</label>
                  <select class="form-select" name="estatus" required>
                    <option value="1" <?php echo set_select('estatus', '1', $emp->estatus == 1); ?>>Activo</option>
                    <option value="0" <?php echo set_select('estatus', '0', $emp->estatus == 0); ?>>Inactivo</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- TAB 4: Nómina -->
            <div class="tab-pane fade" id="nomina" role="tabpanel">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Salario Base Mensual *</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="salario_base_mensual" value="<?php echo set_value('salario_base_mensual', $emp->salario_base_mensual); ?>" step="0.01" required>
                  </div>
                  <small class="text-muted">Salario diario actual: $<?php echo number_format($emp->salario_base_diario, 2); ?></small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tipo de Nómina</label>
                  <select class="form-select" name="tipo_nomina">
                    <option value="Quincenal" <?php echo set_select('tipo_nomina', 'Quincenal', $emp->tipo_nomina == 'Quincenal'); ?>>Quincenal</option>
                    <option value="Semanal" <?php echo set_select('tipo_nomina', 'Semanal', $emp->tipo_nomina == 'Semanal'); ?>>Semanal</option>
                    <option value="Mensual" <?php echo set_select('tipo_nomina', 'Mensual', $emp->tipo_nomina == 'Mensual'); ?>>Mensual</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Forma de Pago</label>
                  <select class="form-select" name="forma_pago">
                    <option value="Transferencia" <?php echo set_select('forma_pago', 'Transferencia', $emp->forma_pago == 'Transferencia'); ?>>Transferencia</option>
                    <option value="Efectivo" <?php echo set_select('forma_pago', 'Efectivo', $emp->forma_pago == 'Efectivo'); ?>>Efectivo</option>
                    <option value="Cheque" <?php echo set_select('forma_pago', 'Cheque', $emp->forma_pago == 'Cheque'); ?>>Cheque</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Banco</label>
                  <input type="text" class="form-control" name="banco" value="<?php echo set_value('banco', $emp->banco); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">CLABE Interbancaria</label>
                  <input type="text" class="form-control" name="cuenta_bancaria" value="<?php echo set_value('cuenta_bancaria', $emp->cuenta_bancaria); ?>" maxlength="18">
                  <small class="text-muted">18 dígitos</small>
                </div>
              </div>
            </div>

            <!-- TAB 5: Contacto y Dirección -->
            <div class="tab-pane fade" id="contacto" role="tabpanel">
              <h5 class="mb-3"><i data-lucide="phone"></i> Información de Contacto</h5>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Teléfono de Emergencia</label>
                  <input type="tel" class="form-control" name="telefono_emergencia" value="<?php echo set_value('telefono_emergencia', $emp->telefono_emergencia); ?>">
                  <small class="text-muted">Contacto en caso de emergencia</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email Corporativo</label>
                  <input type="email" class="form-control" name="email_corporativo" value="<?php echo set_value('email_corporativo', $emp->email_corporativo); ?>" placeholder="nombre@chisarecubrimientos.com">
                </div>
              </div>

              <hr class="my-4">
              <h5 class="mb-3"><i data-lucide="map-pin"></i> Dirección</h5>
              <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label">Calle</label>
                  <input type="text" class="form-control" name="calle" value="<?php echo set_value('calle', $emp->calle); ?>">
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">No. Ext</label>
                  <input type="text" class="form-control" name="numero_exterior" value="<?php echo set_value('numero_exterior', $emp->numero_exterior); ?>">
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">No. Int</label>
                  <input type="text" class="form-control" name="numero_interior" value="<?php echo set_value('numero_interior', $emp->numero_interior); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Colonia</label>
                  <input type="text" class="form-control" name="colonia" value="<?php echo set_value('colonia', $emp->colonia); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Código Postal</label>
                  <input type="text" class="form-control" name="codigo_postal" value="<?php echo set_value('codigo_postal', $emp->codigo_postal); ?>" maxlength="5">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Ciudad</label>
                  <input type="text" class="form-control" name="ciudad" value="<?php echo set_value('ciudad', $emp->ciudad); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Estado</label>
                  <input type="text" class="form-control" name="estado" value="<?php echo set_value('estado', $emp->estado); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">País</label>
                  <input type="text" class="form-control" name="pais" value="<?php echo set_value('pais', $emp->pais); ?>">
                </div>
              </div>
            </div>

            <!-- TAB 6: Prestaciones (Infonavit, Fonacot) -->
            <div class="tab-pane fade" id="prestaciones" role="tabpanel">
              <h5 class="mb-3"><i data-lucide="home"></i> INFONAVIT</h5>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">¿Tiene crédito INFONAVIT?</label>
                  <select class="form-select" name="tiene_infonavit" id="tiene_infonavit">
                    <option value="0" <?php echo set_select('tiene_infonavit', '0', $emp->tiene_infonavit == 0); ?>>No</option>
                    <option value="1" <?php echo set_select('tiene_infonavit', '1', $emp->tiene_infonavit == 1); ?>>Sí</option>
                  </select>
                </div>
                <div class="col-md-8 mb-3" id="descuento_infonavit_container" style="display:<?php echo ($emp->tiene_infonavit == 1) ? 'block' : 'none'; ?>;">
                  <label class="form-label">Monto de Descuento INFONAVIT</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="descuento_infonavit" id="descuento_infonavit" value="<?php echo set_value('descuento_infonavit', $emp->descuento_infonavit); ?>" step="0.01">
                  </div>
                  <small class="text-muted">Descuento quincenal o mensual según tipo de nómina</small>
                </div>
              </div>

              <hr class="my-4">
              <h5 class="mb-3"><i data-lucide="credit-card"></i> FONACOT</h5>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">¿Tiene crédito FONACOT?</label>
                  <select class="form-select" name="tiene_fonacot">
                    <option value="0" <?php echo set_select('tiene_fonacot', '0', $emp->tiene_fonacot == 0); ?>>No</option>
                    <option value="1" <?php echo set_select('tiene_fonacot', '1', $emp->tiene_fonacot == 1); ?>>Sí</option>
                  </select>
                </div>
              </div>
            </div>

          </div>

          <!-- Mensajes de validación -->
          <?php
          echo validation_errors('<div class="alert alert-danger alert-dismissible" role="alert"><div class="alert-message">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          ','</div></div>');

          if(isset($validate)){
            echo $validate;
          }
          ?>

          <hr>
          <p class="text-end">
            <a href="<?php echo base_url('rh/RecursosHumanos'); ?>" class="btn btn-secondary">Volver</a>
            <button type="submit" value="1" name="save" class="btn btn-success">Actualizar Empleado</button>
          </p>
          </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  // Toggle descuento INFONAVIT
  const tieneInfonavit = document.getElementById('tiene_infonavit');
  const descuentoContainer = document.getElementById('descuento_infonavit_container');
  
  if(tieneInfonavit) {
    tieneInfonavit.addEventListener('change', function() {
      if(this.value == '1') {
        descuentoContainer.style.display = 'block';
      } else {
        descuentoContainer.style.display = 'none';
        document.getElementById('descuento_infonavit').value = '';
      }
    });
  }
});
</script>
