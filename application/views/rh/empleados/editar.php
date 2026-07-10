<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$emp = $response['empleado'];

$form['nombre_class'] = 'form-control ';
$form['apellido_paterno_class'] = 'form-control ';
$form['apellido_materno_class'] = 'form-control ';
$form['rfc_class'] = 'form-control ';
$form['curp_class'] = 'form-control ';

if (form_error('nombre')) { $form['nombre_class'] .= 'is-invalid'; }
if (form_error('apellido_paterno')) { $form['apellido_paterno_class'] .= 'is-invalid'; }
if (form_error('rfc')) { $form['rfc_class'] .= 'is-invalid'; }
if (form_error('curp')) { $form['curp_class'] .= 'is-invalid'; }
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
      <div class="card border-0 shadow-sm">
        <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);">
          <h2 class="h4 mb-0 text-white"><i data-lucide="user-pen" class="me-2 text-white" style="width:24px;height:24px;"></i> Editar Empleado: <?php echo htmlspecialchars($emp->numero_empleado); ?></h2>
          <small class="text-white-50"><?php echo htmlspecialchars(trim($emp->nombre . ' ' . $emp->apellido_paterno)); ?></small>
        </div>
        <div class="card-body">

          <?php if (!empty($response['vinculo_habilitado'])): ?>
          <div class="alert alert-light border mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div>
                <h6 class="mb-1"><i class="fas fa-user-lock text-primary"></i> Usuario ERP vinculado</h6>
                <?php if (!empty($response['usuario_vinculado'])): ?>
                  <span class="small">#<?= (int)$response['usuario_vinculado']->id ?> — <?= htmlspecialchars($response['usuario_vinculado']->nombre . ' ' . $response['usuario_vinculado']->apellidos) ?></span><br>
                  <span class="text-muted small"><?= htmlspecialchars($response['usuario_vinculado']->username) ?></span>
                <?php else: ?>
                  <span class="text-muted small">Este empleado no tiene usuario del sistema vinculado.</span>
                <?php endif; ?>
              </div>
              <a href="<?= base_url('rh/RecursosHumanos') ?>" class="btn btn-sm btn-outline-primary" onclick="sessionStorage.setItem('abrirVinculoEmpleado', '<?= (int)$emp->id ?>');">
                <i class="fas fa-link"></i> Gestionar vinculación
              </a>
            </div>
          </div>
          <?php endif; ?>

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
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button">
                <i data-lucide="folder"></i> Documentos
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
                  <label class="form-label">Nacionalidad</label>
                  <input type="text" class="form-control" name="nacionalidad" value="<?php echo set_value('nacionalidad', $emp->nacionalidad ?? 'Mexicana'); ?>">
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
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label">Beneficiarios (Art. 501 LFT)</label>
                  <textarea class="form-control" name="beneficiarios" rows="3" placeholder="Ej: Maria Perez - Esposa - 50%, Juan Perez - Hijo - 50%"><?php echo set_value('beneficiarios', $emp->beneficiarios); ?></textarea>
                  <small class="text-muted">Nombre completo - Parentesco - Porcentaje. Obligatorio por ley.</small>
                </div>
              </div>
            </div>

            <!-- TAB 2: Datos Fiscales -->
            <div class="tab-pane fade" id="fiscales" role="tabpanel">
              <div class="alert alert-info">
                <i data-lucide="info"></i> Los datos fiscales (RFC, CURP, NSS) no se pueden modificar por seguridad.
              </div>
              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">RFC</label>
                  <input type="text" class="form-control" value="<?php echo $emp->rfc; ?>" disabled>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Régimen Fiscal</label>
                  <input type="text" class="form-control" value="<?php echo $emp->regimen_fiscal ?? 'N/A'; ?>" disabled>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">CURP</label>
                  <input type="text" class="form-control" value="<?php echo $emp->curp; ?>" disabled>
                </div>
                <div class="col-md-3 mb-3">
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

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">C.P. fiscal</label>
                  <input type="text" class="form-control" name="codigo_postal_fiscal" value="<?php echo set_value('codigo_postal_fiscal', $emp->codigo_postal_fiscal ?? ''); ?>" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" placeholder="Ej. 08220">
                  <small class="text-muted">Código postal del domicilio fiscal (SAT)</small>
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
                    <option value="Asimilados" <?php echo set_select('tipo_trabajador', 'Asimilados', $emp->tipo_trabajador == 'Asimilados'); ?>>Asimilados a Salarios</option>
                    <option value="Eventual" <?php echo set_select('tipo_trabajador', 'Eventual', $emp->tipo_trabajador == 'Eventual'); ?>>Eventual</option>
                    <option value="Confianza" <?php echo set_select('tipo_trabajador', 'Confianza', $emp->tipo_trabajador == 'Confianza'); ?>>Confianza</option>
                    <option value="Sindicalizado" <?php echo set_select('tipo_trabajador', 'Sindicalizado', $emp->tipo_trabajador == 'Sindicalizado'); ?>>Sindicalizado</option>
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
                    <option value="1" <?php echo set_select('estatus', '1', (int)$emp->estatus === 1); ?>>Activo</option>
                    <option value="2" <?php echo set_select('estatus', '2', (int)$emp->estatus === 2); ?>>Reingreso</option>
                    <option value="0" <?php echo set_select('estatus', '0', (int)$emp->estatus === 0); ?>>Inactivo</option>
                  </select>
                  <small class="text-muted">Reingreso = trabajador que regresó tras una baja previa (sigue activo en nómina y reloj).</small>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Correo institucional</label>
                  <input type="email" class="form-control" name="email_corporativo" value="<?php echo set_value('email_corporativo', $emp->email_corporativo); ?>" placeholder="nombre@chisarecubrimientos.com">
                  <small class="text-muted">Opcional. Correo de la empresa. No es el usuario de acceso al ERP.</small>
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
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pensión Alimenticia (%)</label>
                  <div class="input-group">
                    <input type="number" class="form-control" name="pension_alimenticia_porcentaje" value="<?php echo set_value('pension_alimenticia_porcentaje', $emp->pension_alimenticia_porcentaje ?? 0); ?>" step="0.01" min="0" max="100">
                    <span class="input-group-text">%</span>
                  </div>
                  <small class="text-muted">Porcentaje del sueldo</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pensión Alimenticia (Monto Fijo)</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="pension_alimenticia_monto" value="<?php echo set_value('pension_alimenticia_monto', $emp->pension_alimenticia_monto ?? 0); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Cuota fija por periodo</small>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">ISR %</label>
                  <div class="input-group">
                    <input type="number" class="form-control" name="isr_porcentaje" value="<?php echo set_value('isr_porcentaje', $emp->isr_porcentaje ?? 0); ?>" step="0.01" min="0" max="100">
                    <span class="input-group-text">%</span>
                  </div>
                  <small class="text-muted">Retención Impuesto Sobre Renta (ISR)</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Cuota IMSS</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="imss_cuota" value="<?php echo set_value('imss_cuota', $emp->imss_cuota ?? 0); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Cuota Seguridad Social</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Aportación INFONAVIT</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="infonavit_aportacion" value="<?php echo set_value('infonavit_aportacion', $emp->infonavit_aportacion ?? 0); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Aportación Vivienda</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Aportación AFORE</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="afore_aportacion" value="<?php echo set_value('afore_aportacion', $emp->afore_aportacion ?? 0); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Aportación Retiro</small>
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
              <p class="text-muted small mb-3">Datos para comunicarse con el trabajador. El acceso al ERP se configura por separado al vincular un usuario administrador.</p>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Teléfono de contacto</label>
                  <input type="tel" class="form-control" name="telefono" value="<?php echo set_value('telefono', $emp->telefono); ?>" maxlength="15" inputmode="tel" placeholder="Ej. 5512345678">
                  <small class="text-muted">Celular o fijo del trabajador</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Correo de contacto</label>
                  <input type="email" class="form-control" name="email_personal" value="<?php echo set_value('email_personal', $emp->email_personal); ?>" placeholder="ejemplo@gmail.com">
                  <small class="text-muted">Correo personal o alternativo. No es el acceso al sistema.</small>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Teléfono de emergencia</label>
                  <input type="tel" class="form-control" name="telefono_emergencia" value="<?php echo set_value('telefono_emergencia', $emp->telefono_emergencia); ?>" maxlength="15" inputmode="tel">
                  <small class="text-muted">Familiar o contacto en caso de emergencia</small>
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
                  <label class="form-label">C.P. (domicilio)</label>
                  <input type="text" class="form-control" name="codigo_postal" value="<?php echo set_value('codigo_postal', $emp->codigo_postal); ?>" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" placeholder="5 dígitos">
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

            <!-- TAB 7: Documentos del Expediente -->
            <div class="tab-pane fade" id="documentos" role="tabpanel">
              <?php $chk = $response['checklist']; ?>
              <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #f8fafc, #eef2f7);">
                <div class="card-body py-3" id="checklist-expediente-editar">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i data-lucide="clipboard-check"></i> Checklist de Expediente</h6>
                    <span class="badge bg-<?php echo $chk['completo'] ? 'success' : 'warning'; ?>"><?php echo $chk['porcentaje']; ?>% completo</span>
                  </div>
                  <div class="progress mb-3" style="height:10px;">
                    <div class="progress-bar bg-<?php echo $chk['completo'] ? 'success' : 'primary'; ?>" style="width:<?php echo $chk['porcentaje']; ?>%"></div>
                  </div>
                  <div class="row g-2">
                    <?php foreach ($chk['items'] as $item): ?>
                      <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-2 small <?php echo $item['tiene'] ? 'text-success' : 'text-danger'; ?>">
                          <i data-lucide="<?php echo $item['tiene'] ? 'check-circle' : 'circle'; ?>" style="width:16px;height:16px;"></i>
                          <?php echo htmlspecialchars($item['label']); ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-5">
                  <div class="card border-0 bg-light">
                    <div class="card-body">
                      <h6 class="mb-3"><i data-lucide="upload"></i> Subir Documento</h6>
                      <div id="formDocEditar">
                        <input type="hidden" name="empleado_id" value="<?php echo $emp->id; ?>">
                        <div class="mb-3">
                          <label class="form-label">Tipo de Documento</label>
                          <select class="form-select" name="tipo_documento" id="doc_tipo_documento">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($response['tipos_documento'] as $key => $label): ?>
                              <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Archivo (PDF o imagen)</label>
                          <input type="file" class="form-control" name="archivo" id="doc_archivo" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                          <small class="text-muted">Máximo 10 MB · NSS, acta de nacimiento, CURP, etc.</small>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Observaciones</label>
                          <textarea class="form-control" name="observaciones" id="doc_observaciones" rows="2"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="subirDocEditar()">
                          <i data-lucide="upload"></i> Subir Documento
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-7">
                  <h6 class="mb-3"><i data-lucide="folder"></i> Expediente Digital</h6>
                  <div id="lista-docs-editar">
                    <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                  </div>
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

  var formEditar = document.querySelector('.card-body form');
  if (formEditar) {
    formEditar.addEventListener('submit', function(e) {
      formEditar.querySelectorAll('.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
      });

      var requeridos = [
        { sel: '[name="nombre"]', label: 'Nombre', tab: 'personales-tab' },
        { sel: '[name="apellido_paterno"]', label: 'Apellido paterno', tab: 'personales-tab' },
        { sel: '[name="rfc"]', label: 'RFC', tab: 'fiscales-tab' },
        { sel: '[name="curp"]', label: 'CURP', tab: 'fiscales-tab' },
        { sel: '#tipo_trabajador', label: 'Tipo de trabajador', tab: 'laborales-tab' },
        { sel: '#puesto', label: 'Puesto', tab: 'laborales-tab' },
        { sel: '#fecha_ingreso', label: 'Fecha de ingreso', tab: 'laborales-tab' },
        {
          sel: '#salario_base_mensual',
          label: 'Salario base mensual',
          tab: 'nomina-tab',
          valid: function(el) { return el.value !== '' && parseFloat(el.value) > 0; }
        }
      ];

      var faltantes = [];
      var primeraTab = null;
      var primerCampo = null;

      requeridos.forEach(function(campo) {
        var el = document.querySelector(campo.sel);
        if (!el) return;
        var valor = (el.tagName === 'SELECT' || el.type === 'number') ? el.value : el.value.trim();
        var ok = campo.valid ? campo.valid(el) : valor !== '';
        if (!ok) {
          faltantes.push(campo.label);
          el.classList.add('is-invalid');
          if (!primeraTab) {
            primeraTab = campo.tab;
            primerCampo = el;
          }
        }
      });

      if (faltantes.length > 0) {
        e.preventDefault();
        if (primeraTab) {
          var tabBtn = document.getElementById(primeraTab);
          if (tabBtn && typeof bootstrap !== 'undefined') {
            bootstrap.Tab.getOrCreateInstance(tabBtn).show();
          }
        }
        if (primerCampo) primerCampo.focus();
        if (typeof notifyShow === 'function') {
          notifyShow('Completa los campos obligatorios: ' + faltantes.join(', '), 'warning');
        }
      }
    });
  }

  // Notificaciones del servidor
  <?php if (isset($notification)): ?>
    notifyShow("<?php echo $notification['msg']; ?>", "<?php echo $notification['type']; ?>");
  <?php endif; ?>

  // Notificación de errores de validación de CodeIgniter
  <?php if (validation_errors()): ?>
    notifyShow("Hay errores en el formulario. Por favor revisa los campos marcados.", "danger");
  <?php endif; ?>

  cargarDocsEditar(<?php echo (int)$emp->id; ?>);

  if (window.location.hash === '#documentos') {
    var tab = document.querySelector('#documentos-tab');
    if (tab) new bootstrap.Tab(tab).show();
  }
});

function cargarDocsEditar(empleadoId) {
  $.post('<?= base_url('rh/RecursosHumanos/documentos_listar') ?>', {
    empleado_id: empleadoId,
    peticion: 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if (result.checklist) {
      actualizarChecklistEditar(result.checklist);
    }
    if (!result.success || !result.documentos.length) {
      $('#lista-docs-editar').html('<div class="alert alert-light border text-center">Sin documentos en el expediente</div>');
      return;
    }
    var html = '<div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Tipo</th><th>Archivo</th><th>Fecha</th><th></th></tr></thead><tbody>';
    result.documentos.forEach(function(doc) {
      html += '<tr><td><span class="badge bg-primary">' + doc.tipo_label + '</span></td>' +
        '<td class="small text-truncate" style="max-width:180px;">' + doc.nombre_archivo + '</td>' +
        '<td class="small text-muted">' + doc.fecha_subida + '</td>' +
        '<td class="text-end text-nowrap">' +
          '<a href="' + doc.url + '" target="_blank" class="btn btn-sm btn-outline-primary py-0"><i data-lucide="eye" style="width:14px;height:14px;"></i></a> ' +
          '<button class="btn btn-sm btn-outline-danger py-0" onclick="eliminarDocEditar(' + doc.id + ',' + empleadoId + ')"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>' +
        '</td></tr>';
    });
    html += '</tbody></table></div>';
    $('#lista-docs-editar').html(html);
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
}

function subirDocEditar() {
  var tipo = document.getElementById('doc_tipo_documento');
  var archivo = document.getElementById('doc_archivo');
  var observaciones = document.getElementById('doc_observaciones');
  if (!tipo.value) {
    notifyShow('Selecciona el tipo de documento', 'danger');
    return;
  }
  if (!archivo.files || !archivo.files.length) {
    notifyShow('Selecciona un archivo para subir', 'danger');
    return;
  }
  var formData = new FormData();
  formData.append('empleado_id', '<?php echo (int)$emp->id; ?>');
  formData.append('tipo_documento', tipo.value);
  formData.append('archivo', archivo.files[0]);
  formData.append('observaciones', observaciones.value || '');
  formData.append('peticion', 'ajax');
  formData.append('<?php echo $this->security->get_csrf_token_name();?>', '<?php echo $this->security->get_csrf_hash();?>');
  $.ajax({
    url: '<?= base_url('rh/RecursosHumanos/documento_subir') ?>',
    type: 'POST', data: formData, processData: false, contentType: false,
    success: function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if (result.success) {
        tipo.value = '';
        archivo.value = '';
        observaciones.value = '';
        cargarDocsEditar(<?php echo (int)$emp->id; ?>);
      }
    }
  });
}

function eliminarDocEditar(docId, empleadoId) {
  if (!confirm('¿Eliminar este documento?')) return;
  $.post('<?= base_url('rh/RecursosHumanos/documento_eliminar') ?>', {
    id: docId, empleado_id: empleadoId, peticion: 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) cargarDocsEditar(empleadoId);
  });
}

function actualizarChecklistEditar(chk) {
  var html = '<div class="d-flex justify-content-between align-items-center mb-2">' +
    '<h6 class="mb-0"><i data-lucide="clipboard-check"></i> Checklist de Expediente</h6>' +
    '<span class="badge bg-' + (chk.completo ? 'success' : 'warning') + '">' + chk.porcentaje + '% completo</span></div>' +
    '<div class="progress mb-3" style="height:10px;"><div class="progress-bar bg-' + (chk.completo ? 'success' : 'primary') + '" style="width:' + chk.porcentaje + '%"></div></div><div class="row g-2">';
  chk.items.forEach(function(item) {
    html += '<div class="col-md-4 col-6"><div class="d-flex align-items-center gap-2 small ' + (item.tiene ? 'text-success' : 'text-danger') + '">' +
      '<i data-lucide="' + (item.tiene ? 'check-circle' : 'circle') + '" style="width:16px;height:16px;"></i>' + item.label + '</div></div>';
  });
  html += '</div>';
  $('#checklist-expediente-editar').html(html);
  if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>
