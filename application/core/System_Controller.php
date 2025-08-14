<?php defined('BASEPATH') OR exit('No direct script access allowed');

class System_Controller extends MY_Controller {
    
	function __construct()
    {
        parent::__construct();
        $this->_verifyAuthorization();
    }
	
    function _verifyAuthorization(){
        $key = $this->uri->segment(5);
        $system_key = $this->config->item("system_key");
        
        if($key != $system_key){
            $this->output->set_status_header('401');    
            $this->output->set_output("Authorization Failed");
            $this->output->_display();
            exit();  
        } 
    }
}