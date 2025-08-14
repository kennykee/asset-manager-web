<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance extends System_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('asset/maintenancemodel');
    } 
    
    public function index()
    {
        $this->checkMaintenance();
    }
       
   function checkMaintenance(){
       $this->maintenancemodel->checkAndSendMaintenance();
   }
}