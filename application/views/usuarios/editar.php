<?php

defined('BASEPATH') OR exit('No direct script access allowed');
$user = $response['userData'];
$userPermissions = $response['userPermissions'];
$permissions = $response['permissions'];
/*
print_r($response['userData']);
echo "<hr>";
print_r($response['userPermissions']);
echo "<hr>";
print_r($response['permissions']);
echo "<hr>";
echo "userPermissions convertido:";
print_r($userPermissions);*/
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
$form['nombre'] = array('type' => 'text', 'name' => 'nombre', 'id' => 'nombre', 'value' => $user->nombre, 'class' => $form['nombre_class']);
$form['apellidos'] = array('type' => 'text', 'name' => 'apellidos', 'id' => 'apellidos', 'value' => $user->apellidos, 'class' => $form['apellidos_class']);
$form['username'] = array('type' => 'email', 'name' => 'username', 'id' => 'username', 'value' => $user->username, 'class' => $form['username_class']);
$form['password'] = array('type' => 'password', 'name' => 'password', 'id' => 'password', 'value' => '', 'class' => $form['password_class']);
$form['password_verify'] = array('type' => 'password', 'name' => 'password_verify', 'id' => 'password_verify', 'value' => '', 'class' => $form['password_verify_class']);


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
<input type="hidden" name="menu_locs" id="menu_locs" value="editar-administrador">
<div class="container-fluid p-0">
  <!-- Breadcrumb (Migas de pan) -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

    <div class="row">
      <!-- //////////////// -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2><i class="fas fa-edit"></i> Editar administrador</h2>
          </div>
          <div class="card-body">

            <!--carga de nuevos usuarios-->
            <?php echo form_open_multipart('usuarios/GestionUsuarios/editar/'.$response['id']); //inicializamos el formulario?>
              <input type="hidden" name="id" id="id" value="<?php echo $response['id'];?>">
              <div class="row">                

                <!-- Información del usuario (solo lectura) -->
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-3">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Información del Usuario</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-6 col-md-3 mb-2">
                          <strong>Estatus:</strong><br>
                          <?php if($user->estatus == 1): ?>
                            <span class="badge bg-success">Activo</span>
                          <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                          <?php endif; ?>
                        </div>
                        <div class="col-sm-6 col-md-3 mb-2">
                          <strong>Fecha de Alta:</strong><br>
                          <span class="text-muted"><?php echo !empty($user->fecha_alta) ? $this->init_controller->date_to_string($user->fecha_alta) : 'N/A'; ?></span>
                        </div>
                        <div class="col-sm-6 col-md-3 mb-2">
                          <strong>Última Edición:</strong><br>
                          <span class="text-muted"><?php echo !empty($user->fecha_edicion) ? $this->init_controller->date_to_string($user->fecha_edicion) : 'N/A'; ?></span>
                        </div>
                        <div class="col-sm-6 col-md-3 mb-2">
                          <strong>Fecha de Baja:</strong><br>
                          <span class="text-muted"><?php echo !empty($user->fecha_baja) ? $this->init_controller->date_to_string($user->fecha_baja) : 'N/A'; ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- datos del usuario -->
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-5 mb-3">
                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-user"></i> Nombre</label>
                    <div class="col-sm-9">
                      <?php echo form_input($form['nombre']); ?>
                    </div>
                  </div>

                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-users"></i>Apellidos</label>
                    <div class="col-sm-9">
                      <?php echo form_input($form['apellidos']); ?>
                    </div>
                  </div>


                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-envelope"></i> E-mail</label>
                    <div class="col-sm-9">
                      <?php echo form_input($form['username']); ?>
                    </div>
                  </div>

                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-lock"></i>Contraseña</label>
                    <div class="col-sm-9">
                      <?php echo form_input($form['password']); ?>
                    </div>
                  </div>

                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-lock"></i> Verificar Contraseña</label>
                    <div class="col-sm-9">
                      <?php echo form_input($form['password_verify']); ?>
                    </div>
                  </div>

                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-building"></i> Depto.</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control " id="departamento" name="departamento" placeholder="" value="<?php echo $user->departamento; ?>">
                    </div>
                  </div>
                  
                  <div class="mb-3 row">
                    <label class="col-form-label col-sm-3 text-sm-right"><i class="fas fa-toggle-on"></i> Estatus</label>
                    <div class="col-sm-9">
                      <select class="form-select" id="estatus" name="estatus">
                        <option value="1" <?php if($user->estatus==1){ echo 'selected'; } ?>>Activo</option>
                        <option value="0" <?php if($user->estatus==0){ echo 'selected'; } ?>>Inactivo</option>
                      </select>
                    </div>
                  </div>
                </div>

                
                
                

                

                <!-- permisos -->
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
                    
                  
                  <?php
                  //recorremos todos los permisos existentes "permissions" y corroboramos que esten activados para el usuario "userPermissions"
                   foreach($permissions as $key => $value) {?>
                  <div class="col-4 col-md-12 col-lg-4 col-xl-4 mb-3">
                      <div class="row">
                          <label class="col-md-12"><strong><?php echo $key; ?></strong></label>
                          <?php foreach($value as $key2 => $value2) { ?>
                          <div class="col-md-12">
                              <label><input name="<?php echo $key2; ?>" type="checkbox" value="1" <?php 
                              //corroboramos si el usuario tiene el permiso habilitado (valor "1")
                              if(isset($userPermissions[$key2]) && $userPermissions[$key2] == "1"){
                                echo set_checkbox( $key2, '1', TRUE);
                              }else{
                                echo set_checkbox( $key2, '1', FALSE);
                              }                              
                              ?>> <?php echo $value2; ?></label>
                          </div>
                          <?php } ?>
                      </div>
                  </div>
                  <?php } ?>

                  </div>
                </div>

              <hr>
              

              <!-- Mensajes de sistema -->
              <?php
              ///validacion de errores del formulario

              echo validation_errors('<div class="alert alert-danger alert-dismissible" role="alert"><div class="alert-message">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              ','</div></div>');//validacion de errores

              if(isset($validate)){
                echo $validate;
              }

              ?>
              <p class="text-end">
                <button type="submit" value="1" name="save" class="btn btn-success" onclick="">Guardar</button>
              </p>
            </form>
          </div>
        </div>
      </div>
      
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
                    
                    // Uncheck all permissions first (optional, maybe user wants to MERGE? Standard behavior for "Template" is usually replace)
                    // But if I uncheck, I lose the user's manual checking outside the role. 
                    // However, purpose is "Check permissions defined in role". 
                    // A "Role" usually *defines* the set.
                    // Let's Uncheck all first to ensure clean state matching the role.
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
    const estatusSelector = document.getElementById('estatus');
    let allChecked = false; // Empezamos con todos desmarcados por defecto

    // Detectar cambio en el selector de estatus
    estatusSelector.addEventListener('change', function() {
        if (this.value === '0') {
        alert('⚠️ Advertencia: Al cambiar el estatus a "Inactivo", se eliminarán todos los permisos del usuario.');
        }
    });

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