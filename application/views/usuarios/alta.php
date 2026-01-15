<?php
defined('BASEPATH') OR exit('No direct script access allowed');
////// input classes
$form['nombre_class'] = 'form-control ';
$form['apellidos_class'] = 'form-control ';
$form['username_class'] = 'form-control ';
$form['password_class'] = 'form-control ';
$form['password_verify_class'] = 'form-control ';

if(form_error('nombre')){ $form['nombre_class'] .= 'is-invalid'; }
if(form_error('apellidos')){ $form['apellidos_class'] .= 'is-invalid'; }
if(form_error('username')){ $form['username_class'] .= 'is-invalid'; }
if(form_error('password')){ $form['password_class'] .= 'is-invalid'; }
if(form_error('password_verify')){ $form['password_verify_class'] .= 'is-invalid'; }

///create form array pre-processed
$form['nombre'] = array('type' => 'text', 'name' => 'nombre', 'id' => 'nombre', 'value' => set_value('nombre'), 'class' => $form['nombre_class']);
$form['apellidos'] = array('type' => 'text', 'name' => 'apellidos', 'id' => 'apellidos', 'value' => set_value('apellidos'), 'class' => $form['apellidos_class']);
$form['username'] = array('type' => 'email', 'name' => 'username', 'id' => 'username', 'value' => set_value('username'), 'class' => $form['username_class']);
$form['password'] = array('type' => 'password', 'name' => 'password', 'id' => 'password', 'value' => set_value('password'), 'class' => $form['password_class']);
$form['password_verify'] = array('type' => 'password', 'name' => 'password_verify', 'id' => 'password_verify', 'value' => set_value('password_verify'), 'class' => $form['password_verify_class']);
?>
<style>
    /**AGREGAMOS UN cursor-pointer a todos los label de la seccion de permisos */
    .asignar-permisos label {
        cursor: pointer;
        font-size: 12px;
    }

  .is-invalid {
    border-color: red !important;
  }
</style>


<input type="hidden" name="menu_locs" id="menu_locs" value="alta-administrador">
<div class="container-fluid p-0">
  <!-- Breadcrumb (Migas de pan) -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h2><i class="fa-solid fa-user-shield"></i> Nuevo administrador</h2>
        </div>
        <div class="card-body">

          <!--carga de nuevos usuarios-->
          <?php echo form_open_multipart('usuarios/GestionUsuarios/alta'); //inicializamos el formulario?>

          <div class="row">

            <!-- Formulario de datos del usuario -->
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-5 mb-3">
              <div class="mb-3 row">
                <label class="col-form-label col-sm-3 text-sm-right">Nombre</label>
                <div class="col-sm-9">
                  <?php echo form_input($form['nombre']); ?>
                </div>
              </div>

              <div class="mb-3 row">
                <label class="col-form-label col-sm-3 text-sm-right">Apellidos</label>
                <div class="col-sm-9">
                  <?php echo form_input($form['apellidos']); ?>
                </div>
              </div>


              <div class="mb-3 row">
                <label class="col-form-label col-sm-3 text-sm-right">Email</label>
                <div class="col-sm-9">
                  <?php echo form_input($form['username']); ?>
                </div>
              </div>

              <div class="mb-3 row">
                <label class="col-form-label col-sm-3 text-sm-right">Contraseña</label>
                <div class="col-sm-9">
                  <?php echo form_input($form['password']); ?>
                </div>
              </div>

              <div class="mb-3 row">
                <label class="col-form-label col-sm-3 text-sm-right">Verificar Contraseña</label>
                <div class="col-sm-9">
                  <?php echo form_input($form['password_verify']); ?>
                </div>
              </div>

              <div class="mb-3 row">
                <label class="col-form-label col-sm-3 text-sm-right">Departamento</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control " id="departamento" name="departamento" placeholder="" value="">
                </div>
              </div>
            </div>

            <!-- Formulario de asignacion de permisos -->   
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-7 asignar-permisos" >
              <div class="row">
                <!-- Selector de Rol -->
                <div class="col-12 mb-4">
                  <div class="card bg-light border-0">
                    <div class="card-body p-3">
                      <label class="form-label fw-bold"><i class="fas fa-user-tag"></i> Cargar Permisos desde Rol (Plantilla)</label>
                      <select id="role_select" class="form-select">
                        <option value="">-- Seleccionar Rol para precargar permisos --</option>
                        <?php if(isset($response['roles'])): foreach($response['roles'] as $role): ?>
                          <option value="<?=$role->id?>" data-permissions='<?=htmlspecialchars($role->permisos, ENT_QUOTES, 'UTF-8')?>'>
                            <?=$role->nombre?>
                          </option>
                        <?php endforeach; endif; ?>
                      </select>
                      <small class="text-muted">Seleccionar un rol marcará automáticamente los permisos correspondientes. No guarda el rol, solo asigna los permisos.</small>
                    </div>
                  </div>
                </div>

                <div class="col-12 mb-3">
                  <h5 class="d-inline-block">Asignar Permisos</h5>
                  <button type="button" id="toggleAllPermissions" class="btn btn-sm btn-outline-primary ms-3">
                    <i data-lucide="check-square"></i> Seleccionar todos
                  </button>
                </div>           

                <?php foreach($response['permissions'] as $key => $value) {?>
                <div class="col-4 col-md-12 col-lg-4 col-xl-4 mb-3">
                    <div class="row">
                        <label class="col-md-12"><strong><?php echo $key; ?></strong></label>
                        <?php foreach($value as $key2 => $value2) {?>
                        <div class="col-md-12">
                            <label><input name="<?php echo $key2; ?>" type="checkbox" value="1" <?php echo set_checkbox( $key2, '1', FALSE) ?>> <?php echo $value2; ?></label>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>                            
              </div>
            </div>                      

            <!-- Mensajes de sistema -->
            <?php
            ///validacion de errores del formulario
            echo validation_errors('<div class="alert alert-danger alert-dismissible" role="alert"><div class="alert-message">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            ','</div></div>');//validacion de errores

            if(isset($validate)){ // mensajes de alerta del controlador
              echo $validate;
            }
            ?>

            <hr>
            <p class="text-end">
              <button type="submit" value="1" name="save" class="btn btn-success" onclick="">Agregar +</button>
            </p>
          </form>
        </div>
      </div>
    </div>

    <!-- /////////////// -->
  </div>
</div>


<!-- Script para seleccionar/deseleccionar todos los permisos -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    
    // Logic for Role Selector
    const roleSelect = document.getElementById('role_select');
    if(roleSelect) {
        roleSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const permissionsJson = selectedOption.getAttribute('data-permissions');
            
            if(permissionsJson) {
                try {
                    const permissions = JSON.parse(permissionsJson);
                    
                    // Uncheck all first
                    document.querySelectorAll('.asignar-permisos input[type="checkbox"]').forEach(cb => {
                        cb.checked = false;
                    });
                    
                    // Check according to role
                    Object.keys(permissions).forEach(key => {
                        const cb = document.querySelector(`input[name="${key}"]`);
                        if(cb) cb.checked = true;
                    });
                    
                } catch(e) {
                    console.error("Error parsing permissions JSON", e);
                }
            }
        });
    }

    const toggleBtn = document.getElementById('toggleAllPermissions');
    const permissionsContainer = document.querySelector('.asignar-permisos');
    let allChecked = false; // Empezamos con todos desmarcados por defecto

    toggleBtn.addEventListener('click', function() {
        const checkboxes = permissionsContainer.querySelectorAll('input[type="checkbox"]');
        
        // Alternar el estado
        allChecked = !allChecked;
        
        // Aplicar a todos los checkboxes
        checkboxes.forEach(checkbox => {
        checkbox.checked = allChecked;
        });

        // Cambiar el texto del botón
        if (allChecked) {
        toggleBtn.innerHTML = '<i data-lucide="check-square"></i> Seleccionar todos';
        } else {
        toggleBtn.innerHTML = '<i data-lucide="square"></i> Deseleccionar todos';
        }

        // Reinicializar los iconos de Lucide
        if (typeof lucide !== 'undefined') {
        lucide.createIcons();
        }
    });
    });
</script>

<?php if(isset($notification)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof notifyShow === 'function') {
            notifyShow('<?= addslashes($notification['msg']) ?>', '<?= $notification['type'] ?>');
        }
    });
</script>
<?php endif; ?>

<?php if(validation_errors()): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof notifyShow === 'function') {
            notifyShow('Hay errores en el formulario, por favor revíselos', 'danger');
        }
    });
</script>
<?php endif; ?>