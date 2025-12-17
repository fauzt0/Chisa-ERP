<!DOCTYPE html>
<html lang="es" data-bs-theme="default" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left" data-sidebar-behavior="sticky">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Sistema ERP - Dashboard">
	<meta name="author" content="Especialistas Web">

	<title>ERP/CHISA - <?php if(isset($pageTitle)){ echo $pageTitle;  } ?></title>  

	<link rel="canonical" href="https://appstack.bootlab.io/dashboard-default.html" />
	<link rel="shortcut icon" href="img/favicon.ico">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

	<link href="<?php echo base_url();?>assets/dist/css/app.css" rel="stylesheet">	
	<link href="<?php echo base_url();?>assets/dist/css/estilos.css?v=<?php echo time(); ?>" rel="stylesheet">	
  
</head>

<body>
  <div class="wrapper">
    
    <?php $this->load->view('layouts/sidebar');?>
    
    <div class="main">
      <?php $this->load->view('layouts/topNavbar');?>

      <main class="content">        
        <?php $this->load->view($pageView);?>
      </main>
      
      <?php $this->load->view('layouts/footer') ?>
    </div>

  </div>  
  <script src="<?php echo base_url();?>assets/dist/js/app.js"></script>  
  <?php if(isset($pageScript) && $pageScript != ''){ $this->load->view($pageScript); } ?>


  <script src="<?php echo base_url();?>assets/dist/js/tools.js"></script>
  
</body>
</html>



