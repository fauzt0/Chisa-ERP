<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Clases de validación
$form['nombre_class'] = 'form-control ';
$form['apellido_paterno_class'] = 'form-control ';
$form['apellido_materno_class'] = 'form-control ';
$form['rfc_class'] = 'form-control ';
$form['curp_class'] = 'form-control ';

if(form_error('nombre')){ $form['nombre_class'] .= 'is-invalid'; }
if(form_error('apellido_paterno')){ $form['apellido_paterno_class'] .= 'is-invalid'; }
if(form_error('rfc')){ $form['rfc_class'] .= 'is-invalid'; }
if(form_error('curp')){ $form['curp_class'] .= 'is-invalid'; }

// Arrays de formulario
$form['nombre'] = ['type' => 'text', 'name' => 'nombre', 'id' => 'nombre', 'value' => set_value('nombre'), 'class' => $form['nombre_class']];
$form['apellido_paterno'] = ['type' => 'text', 'name' => 'apellido_paterno', 'id' => 'apellido_paterno', 'value' => set_value('apellido_paterno'), 'class' => $form['apellido_paterno_class']];
$form['apellido_materno'] = ['type' => 'text', 'name' => 'apellido_materno', 'id' => 'apellido_materno', 'value' => set_value('apellido_materno'), 'class' => $form['apellido_materno_class']];
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
          <h2><i class="fa-solid fa-user-tie"></i> Nuevo Empleado</h2>
        </div>
        <div class="card-body">

          <?php echo form_open_multipart('rh/RecursosHumanos/alta'); ?>

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
                  <label class="form-label">Nombre *</label>
                  <?php echo form_input($form['nombre']); ?>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Apellido Paterno *</label>
                  <?php echo form_input($form['apellido_paterno']); ?>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Apellido Materno</label>
                  <?php echo form_input($form['apellido_materno']); ?>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">Nacionalidad</label>
                  <input type="text" class="form-control" name="nacionalidad" value="<?php echo set_value('nacionalidad', 'Mexicana'); ?>">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Fecha de Nacimiento</label>
                  <input type="date" class="form-control" name="fecha_nacimiento" value="<?php echo set_value('fecha_nacimiento'); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Género</label>
                  <select class="form-select" name="genero">
                    <option value="">Seleccionar...</option>
                    <option value="M" <?php echo set_select('genero', 'M'); ?>>Masculino</option>
                    <option value="F" <?php echo set_select('genero', 'F'); ?>>Femenino</option>
                    <option value="Otro" <?php echo set_select('genero', 'Otro'); ?>>Otro</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Estado Civil</label>
                  <select class="form-select" name="estado_civil">
                    <option value="">Seleccionar...</option>
                    <option value="Soltero" <?php echo set_select('estado_civil', 'Soltero'); ?>>Soltero</option>
                    <option value="Casado" <?php echo set_select('estado_civil', 'Casado'); ?>>Casado</option>
                    <option value="Divorciado" <?php echo set_select('estado_civil', 'Divorciado'); ?>>Divorciado</option>
                    <option value="Viudo" <?php echo set_select('estado_civil', 'Viudo'); ?>>Viudo</option>
                    <option value="Union Libre" <?php echo set_select('estado_civil', 'Union Libre'); ?>>Unión Libre</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label">Beneficiarios (Art. 501 LFT)</label>
                  <textarea class="form-control" name="beneficiarios" rows="3" placeholder="Ej: Maria Perez - Esposa - 50%, Juan Perez - Hijo - 50%"><?php echo set_value('beneficiarios'); ?></textarea>
                  <small class="text-muted">Nombre completo - Parentesco - Porcentaje. Obligatorio por ley.</small>
                </div>
              </div>
            </div>

            <!-- TAB 2: Datos Fiscales -->
            <div class="tab-pane fade" id="fiscales" role="tabpanel">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">RFC * <small class="text-muted">(13 caracteres)</small></label>
                  <input type="text" class="form-control <?php echo $form['rfc_class']; ?>" name="rfc" value="<?php echo set_value('rfc'); ?>" maxlength="13" style="text-transform: uppercase;">
                  <small class="text-muted">Ejemplo: AAAA######XXX</small>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Régimen Fiscal (SAT)</label>
                  <select class="form-select" name="regimen_fiscal">
                    <option value="">Seleccionar...</option>
                    <option value="605" <?php echo set_select('regimen_fiscal', '605'); ?>>605 - Sueldos y Salarios e Ingresos Asimilados</option>
                    <option value="606" <?php echo set_select('regimen_fiscal', '606'); ?>>606 - Arrendamiento</option>
                    <option value="608" <?php echo set_select('regimen_fiscal', '608'); ?>>608 - Demás ingresos</option>
                    <option value="612" <?php echo set_select('regimen_fiscal', '612'); ?>>612 - Personas Físicas con Actividades Empresariales</option>
                    <option value="621" <?php echo set_select('regimen_fiscal', '621'); ?>>621 - Incorporación Fiscal</option>
                    <option value="625" <?php echo set_select('regimen_fiscal', '625'); ?>>625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas</option>
                    <option value="626" <?php echo set_select('regimen_fiscal', '626'); ?>>626 - Régimen Simplificado de Confianza</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">CURP * <small class="text-muted">(18 caracteres)</small></label>
                  <input type="text" class="form-control <?php echo $form['curp_class']; ?>" name="curp" value="<?php echo set_value('curp'); ?>" maxlength="18" style="text-transform: uppercase;">
                  <small class="text-muted">Ejemplo: AAAA######HXXXXX##</small>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">NSS (IMSS) <small class="text-muted">(11 dígitos)</small></label>
                  <input type="text" class="form-control" name="nss" value="<?php echo set_value('nss'); ?>" maxlength="11">
                  <small class="text-muted">Número de Seguro Social</small>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">AFORE</label>
                  <input type="text" class="form-control" name="afore" value="<?php echo set_value('afore'); ?>" placeholder="Ej: Afore XXI Banorte">
                  <small class="text-muted">Nombre de la AFORE</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Número de Cuenta AFORE</label>
                  <input type="text" class="form-control" name="afore_numero_cuenta" value="<?php echo set_value('afore_numero_cuenta'); ?>" maxlength="20">
                  <small class="text-muted">Cuenta individual</small>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">C.P. fiscal</label>
                  <input type="text" class="form-control" name="codigo_postal_fiscal" value="<?php echo set_value('codigo_postal_fiscal'); ?>" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" placeholder="Ej. 08220">
                  <small class="text-muted">Código postal del domicilio fiscal (SAT)</small>
                </div>
              </div>
            </div>

            <!-- TAB 3: Datos Laborales -->
            <div class="tab-pane fade" id="laborales" role="tabpanel">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Tipo de Trabajador *</label>
                  <select class="form-select" name="tipo_trabajador" id="tipo_trabajador">
                    <option value="">Seleccionar...</option>
                    <option value="Planta" <?php echo set_select('tipo_trabajador', 'Planta'); ?>>Planta</option>
                    <option value="Temporal" <?php echo set_select('tipo_trabajador', 'Temporal'); ?>>Temporal</option>
                    <option value="Por Proyecto" <?php echo set_select('tipo_trabajador', 'Por Proyecto'); ?>>Por Proyecto</option>
                    <option value="Honorarios" <?php echo set_select('tipo_trabajador', 'Honorarios'); ?>>Honorarios</option>
                    <option value="Asimilados" <?php echo set_select('tipo_trabajador', 'Asimilados'); ?>>Asimilados a Salarios</option>
                    <option value="Eventual" <?php echo set_select('tipo_trabajador', 'Eventual'); ?>>Eventual</option>
                    <option value="Confianza" <?php echo set_select('tipo_trabajador', 'Confianza'); ?>>Confianza</option>
                    <option value="Sindicalizado" <?php echo set_select('tipo_trabajador', 'Sindicalizado'); ?>>Sindicalizado</option>
                    <option value="Practicante" <?php echo set_select('tipo_trabajador', 'Practicante'); ?>>Practicante</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Departamento</label>
                  <select class="form-select" name="departamento_id">
                    <option value="">Seleccionar...</option>
                    <?php foreach($response['departamentos'] as $dept): ?>
                      <option value="<?php echo $dept->id; ?>" <?php echo set_select('departamento_id', $dept->id); ?>>
                        <?php echo $dept->nombre; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Puesto *</label>
                  <input type="text" class="form-control" name="puesto" id="puesto" value="<?php echo set_value('puesto'); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Jefe Directo</label>
                  <select class="form-select" name="jefe_directo_id">
                    <option value="">Sin jefe directo</option>
                    <?php foreach($response['empleados'] as $emp): ?>
                      <option value="<?php echo $emp->id; ?>" <?php echo set_select('jefe_directo_id', $emp->id); ?>>
                        <?php echo $emp->nombre_completo; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de Ingreso *</label>
                  <input type="date" class="form-control" name="fecha_ingreso" id="fecha_ingreso" value="<?php echo set_value('fecha_ingreso', date('Y-m-d')); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Correo institucional</label>
                  <input type="email" class="form-control" name="email_corporativo" value="<?php echo set_value('email_corporativo'); ?>" placeholder="nombre@chisarecubrimientos.com">
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
                    <input type="number" class="form-control" name="salario_base_mensual" id="salario_base_mensual" value="<?php echo set_value('salario_base_mensual'); ?>" step="0.01">
                  </div>
                  <small class="text-muted">El salario diario se calculará automáticamente</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tipo de Nómina</label>
                  <select class="form-select" name="tipo_nomina">
                    <option value="Quincenal" <?php echo set_select('tipo_nomina', 'Quincenal', TRUE); ?>>Quincenal</option>
                    <option value="Semanal" <?php echo set_select('tipo_nomina', 'Semanal'); ?>>Semanal</option>
                    <option value="Mensual" <?php echo set_select('tipo_nomina', 'Mensual'); ?>>Mensual</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pensión Alimenticia (%)</label>
                  <div class="input-group">
                    <input type="number" class="form-control" name="pension_alimenticia_porcentaje" value="<?php echo set_value('pension_alimenticia_porcentaje'); ?>" step="0.01" min="0" max="100">
                    <span class="input-group-text">%</span>
                  </div>
                  <small class="text-muted">Porcentaje del sueldo</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pensión Alimenticia (Monto Fijo)</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="pension_alimenticia_monto" value="<?php echo set_value('pension_alimenticia_monto'); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Cuota fija por periodo</small>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">ISR %</label>
                  <div class="input-group">
                    <input type="number" class="form-control" name="isr_porcentaje" value="<?php echo set_value('isr_porcentaje'); ?>" step="0.01" min="0" max="100">
                    <span class="input-group-text">%</span>
                  </div>
                  <small class="text-muted">Retención Impuesto Sobre Renta</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Cuota IMSS</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="imss_cuota" value="<?php echo set_value('imss_cuota'); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Cuota Seguridad Social</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Aportación INFONAVIT</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="infonavit_aportacion" value="<?php echo set_value('infonavit_aportacion'); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Aportación Vivienda</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Aportación AFORE</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="afore_aportacion" value="<?php echo set_value('afore_aportacion'); ?>" step="0.01" min="0">
                  </div>
                  <small class="text-muted">Aportación Retiro</small>
                </div>
              </div>


              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Forma de Pago</label>
                  <select class="form-select" name="forma_pago">
                    <option value="Transferencia" <?php echo set_select('forma_pago', 'Transferencia', TRUE); ?>>Transferencia</option>
                    <option value="Efectivo" <?php echo set_select('forma_pago', 'Efectivo'); ?>>Efectivo</option>
                    <option value="Cheque" <?php echo set_select('forma_pago', 'Cheque'); ?>>Cheque</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Banco</label>
                  <input type="text" class="form-control" name="banco" value="<?php echo set_value('banco'); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">CLABE Interbancaria</label>
                  <input type="text" class="form-control" name="cuenta_bancaria" value="<?php echo set_value('cuenta_bancaria'); ?>" maxlength="18">
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
                  <input type="tel" class="form-control" name="telefono" value="<?php echo set_value('telefono'); ?>" maxlength="15" inputmode="tel" placeholder="Ej. 5512345678">
                  <small class="text-muted">Celular o fijo del trabajador</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Correo de contacto</label>
                  <input type="email" class="form-control" name="email_personal" value="<?php echo set_value('email_personal'); ?>" placeholder="ejemplo@gmail.com">
                  <small class="text-muted">Correo personal o alternativo. No es el acceso al sistema.</small>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Teléfono de emergencia</label>
                  <input type="tel" class="form-control" name="telefono_emergencia" value="<?php echo set_value('telefono_emergencia'); ?>" maxlength="15" inputmode="tel">
                  <small class="text-muted">Familiar o contacto en caso de emergencia</small>
                </div>
              </div>

              <hr class="my-4">
              <h5 class="mb-3"><i data-lucide="map-pin"></i> Dirección</h5>
              <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label">Calle</label>
                  <input type="text" class="form-control" name="calle" value="<?php echo set_value('calle'); ?>">
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">No. Ext</label>
                  <input type="text" class="form-control" name="numero_exterior" value="<?php echo set_value('numero_exterior'); ?>">
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">No. Int</label>
                  <input type="text" class="form-control" name="numero_interior" value="<?php echo set_value('numero_interior'); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Colonia</label>
                  <input type="text" class="form-control" name="colonia" value="<?php echo set_value('colonia'); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">C.P. (domicilio)</label>
                  <input type="text" class="form-control" name="codigo_postal" value="<?php echo set_value('codigo_postal'); ?>" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" placeholder="5 dígitos">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Ciudad</label>
                  <input type="text" class="form-control" name="ciudad" value="<?php echo set_value('ciudad'); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Estado</label>
                  <input type="text" class="form-control" name="estado" value="<?php echo set_value('estado'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">País</label>
                  <input type="text" class="form-control" name="pais" value="<?php echo set_value('pais', 'México'); ?>">
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
                    <option value="0" <?php echo set_select('tiene_infonavit', '0', TRUE); ?>>No</option>
                    <option value="1" <?php echo set_select('tiene_infonavit', '1'); ?>>Sí</option>
                  </select>
                </div>
                <div class="col-md-8 mb-3" id="descuento_infonavit_container" style="display:none;">
                  <label class="form-label">Monto de Descuento INFONAVIT</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="descuento_infonavit" id="descuento_infonavit" value="<?php echo set_value('descuento_infonavit'); ?>" step="0.01">
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
                    <option value="0" <?php echo set_select('tiene_fonacot', '0', TRUE); ?>>No</option>
                    <option value="1" <?php echo set_select('tiene_fonacot', '1'); ?>>Sí</option>
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
            <a href="<?php echo base_url('rh/RecursosHumanos'); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" value="1" name="save" class="btn btn-success" id="btnGuardar">Guardar Empleado</button>
          </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Validación personalizada antes de enviar el formulario
document.addEventListener("DOMContentLoaded", function() {
  const form = document.querySelector('form');
  const btnGuardar = document.getElementById('btnGuardar');
  
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
  
  form.addEventListener('submit', function(e) {
    let errores = [];
    
    // Validar campos obligatorios
    const nombre = document.querySelector('[name="nombre"]').value.trim();
    const apellido_paterno = document.querySelector('[name="apellido_paterno"]').value.trim();
    const rfc = document.querySelector('[name="rfc"]').value.trim();
    const curp = document.querySelector('[name="curp"]').value.trim();
    const tipo_trabajador = document.getElementById('tipo_trabajador').value;
    const puesto = document.getElementById('puesto').value.trim();
    const fecha_ingreso = document.getElementById('fecha_ingreso').value;
    const salario = document.getElementById('salario_base_mensual').value;
    
    if(!nombre) errores.push('El nombre es obligatorio');
    if(!apellido_paterno) errores.push('El apellido paterno es obligatorio');
    if(!rfc) errores.push('El RFC es obligatorio');
    if(!curp) errores.push('El CURP es obligatorio');
    if(!tipo_trabajador) errores.push('El tipo de trabajador es obligatorio');
    if(!puesto) errores.push('El puesto es obligatorio');
    if(!fecha_ingreso) errores.push('La fecha de ingreso es obligatoria');
    if(!salario || salario <= 0) errores.push('El salario base mensual es obligatorio');
    
    if(errores.length > 0) {
      e.preventDefault();
      notifyShow('Por favor completa los campos obligatorios.', 'warning');
      return false;
    }
  });

  // Notificaciones del servidor
  <?php if (isset($notification)): ?>
    notifyShow("<?php echo $notification['msg']; ?>", "<?php echo $notification['type']; ?>");
  <?php endif; ?>

  // Notificación de errores de validación de CodeIgniter
  <?php if (validation_errors()): ?>
    notifyShow("Hay errores en el formulario. Por favor revisa los campos marcados.", "danger");
  <?php endif; ?>
});
</script>

