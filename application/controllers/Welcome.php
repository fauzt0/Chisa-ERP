<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->view('welcome_message');
	}
  
  public function test() 
  {
    
    echo "La hora del servidor es: ". date('Y-m-d H:i:s');
  }
}
