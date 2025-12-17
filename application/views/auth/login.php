<!DOCTYPE html>
<!--
  HOW TO USE: 
  data-layout: fluid (default), boxed
  data-sidebar-theme: dark (default), colored, light
  data-sidebar-position: left (default), right
  data-sidebar-behavior: sticky (default), fixed, compact
-->
<html lang="es" data-bs-theme="default" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left" data-sidebar-behavior="sticky">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="ERP CHISA RECUBRIMIENTOS">
	<meta name="author" content="Especialistas Web">

	<title><?php echo $pageTitle; ?></title>

	<link rel="canonical" href="https://appstack.bootlab.io/auth-sign-in-cover.html" />
	<link rel="shortcut icon" href="img/favicon.ico">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

	<link href="<?php echo base_url();?>assets/dist/css/app.css" rel="stylesheet">

	<!-- BEGIN SETTINGS -->
	<!-- END SETTINGS -->

<style>
    .fondo-claro-translucido {
        /* Blanco con 45% de opacidad para que se note sobre el fondo */
        background-color: rgba(255, 255, 255, 0.28); 
        
        /* Efecto de desenfoque (indispensable para el look traslúcido) */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        
        /* Texto oscuro para contraste */
        color: #1a1a1a; 
        
        /* Estilo del bloque */
        padding: 25px;
        border-radius: 12px;        
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); /* Sombra muy ligera */
        max-width: 500px;
    }

    .fondo-claro-translucido p {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 600;
		text-align: center;
    }
</style>

</head>

<body>  
	<div class="container-fluid p-0">
		<div class="row g-0">
			<div class="col-xl-6 d-none d-xl-flex">
				<div class="auth-full-page position-relative">
					<img src="<?php echo base_url();?>assets/dist/img/photos/chisa recubrimientos sistema ERP.jpg" class="auth-bg" alt="Unsplash">
					<div class="auth-quote">
						
						<figure>
							<blockquote class="fondo-claro-translucido">
								<p>Sistema ERP para la Administración y Gestión de Procesos.</p>
							</blockquote>
						</figure>
					</div>
				</div>
			</div>
			<div class="col-xl-6">
				<div class="auth-full-page d-flex p-1 p-xl-3">
					<div class="d-flex flex-column w-100 h-100">
						<div class="auth-form">											
							<div class="text-center">
								<img src="<?php echo base_url();?>assets/dist/img/photos/logo-chisa-portada.jpg"  alt="ERP" width="220px" >			
								<h1 class="h2 mt-2">Bienvenid@!</h1>
								<p class="lead">
									Ingresa a tu cuenta para continuar.
								</p>
							</div>

							<div>
								
								<div class="row">
									<div class="col">
										<hr>
									</div>
									<div class="col-auto text-uppercase d-flex align-items-center">Acceso</div>
									<div class="col">
										<hr>
									</div>
								</div>
								<?php echo form_open(base_url().'Auth/authenticate'); ?>
									<div class="mb-3">
										<label class="form-label">Email</label>
										<input class="form-control form-control-lg" type="email" name="username" placeholder="Ingresa tu email">
									</div>
									<div class="mb-3">
										<label class="form-label">Contraseña</label>
										<input class="form-control form-control-lg" type="password" name="password" placeholder="Ingrese tu contraseña" />
										<small>
                      <a href="auth-reset-password-cover.html">Olvidé la contraseña</a>
                    </small>
									</div>
									<div>
										<div class="form-check align-items-center">
											<input id="customControlInline" type="checkbox" class="form-check-input" value="remember-me" name="remember-me" checked>
											<label class="form-check-label text-small" for="customControlInline">Recordar cuenta en el dispositivo</label>
										</div>
									</div>
									<div class="d-grid gap-2 mt-3">
										<?php if(isset($validate)){ echo $validate; }?>
										<button type="submit" class="btn btn-primary btn-lg">Iniciar sesión</button>
									</div>
								</form>
							</div>

							
						</div>
						<div class="text-center">
							<p class="mb-1">
								&copy; <?php echo date('Y'); ?> -  Todos los derechos reservados. <br>Desarrollado por <a href="https://especialistasweb.com.mx" target= "_blank">Especialistas Web</a>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="<?php echo base_url();?>assets/dist/js/app.js"></script>

</body>

</html>