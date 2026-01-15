<!DOCTYPE html>
<html lang="es" data-bs-theme="default" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left" data-sidebar-behavior="sticky">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="ERP CHISA RECUBRIMIENTOS">
	<meta name="author" content="Especialistas Web">

	<title><?php echo $pageTitle; ?></title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

	<link href="<?php echo base_url();?>assets/dist/css/app.css" rel="stylesheet">
</head>

<body>  
	<div class="container-fluid p-0">
		<div class="row g-0">
			<div class="col-xl-6 d-none d-xl-flex">
				<div class="auth-full-page position-relative">
					<img src="<?php echo base_url();?>assets/dist/img/photos/chisa recubrimientos sistema ERP.jpg" class="auth-bg" alt="ERP Background">
				</div>
			</div>
			<div class="col-xl-6">
				<div class="auth-full-page d-flex p-1 p-xl-3">
					<div class="d-flex flex-column w-100 h-100">
						<div class="auth-form">											
							<div class="text-center">
								<img src="<?php echo base_url();?>assets/dist/img/photos/logo-chisa-portada.jpg" alt="Logo CHISA" width="220px">			
								<h1 class="h2 mt-2">Verificación de Seguridad</h1>
								<p class="lead">
									Se ha detectado un inicio de sesión desde un nuevo dispositivo o ubicación.
								</p>
							</div>

							<div class="mt-4">
								<div class="alert alert-info" role="alert">
									<div class="alert-message">
										Hemos enviado un código de 6 dígitos a su correo: <strong><?php echo $email_oculto; ?></strong>
									</div>
								</div>

								<?php if ($this->session->flashdata('error')): ?>
									<div class="alert alert-danger" role="alert">
										<div class="alert-message"><?php echo $this->session->flashdata('error'); ?></div>
									</div>
								<?php endif; ?>

								<?php echo form_open(base_url().'Auth/check_2fa'); ?>
									<div class="mb-3">
										<label class="form-label">Código de Verificación</label>
										<input class="form-control form-control-lg text-center" type="text" name="code" maxlength="6" placeholder="000000" autofocus required style="font-size: 2rem; letter-spacing: 0.5rem;">
										<div class="form-text text-center mt-2">
											El código expira en 10 minutos. Revise su bandeja de entrada (y carpeta de SPAM).
										</div>
									</div>
									<div class="d-grid gap-2 mt-4">
										<button type="submit" class="btn btn-primary btn-lg">Verificar Código</button>
										<a href="<?php echo base_url('admin'); ?>" class="btn btn-link">Volver al Login</a>
									</div>
								</form>
							</div>
						</div>
						<div class="text-center">
							<p class="mb-1">
								&copy; <?php echo date('Y'); ?> - Todos los derechos reservados. <br>Desarrollado por <a href="https://especialistasweb.com.mx" target="_blank">Especialistas Web</a>
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
