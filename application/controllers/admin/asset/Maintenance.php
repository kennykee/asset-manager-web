<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('asset/assetmodel');
        $this->load->model('asset/maintenancemodel');
    } 
    
    public function index()
    {
        /* Silence is golden */
    }    
    
    function addMaintenance(){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
             
            $this->form_validation->set_rules('assets_id', 'Asset', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_asset_accessible'); 
            $this->form_validation->set_rules('maintenance_date', 'Maintenance Date', array('trim','xss_clean','required','is_ymmmmdd_date'));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["assets_id"] = $this->input->post("assets_id", TRUE);
                $parameters["maintenance_date"] = mysql_date($this->input->post("maintenance_date", TRUE));
                
                $this->maintenancemodel->addMaintenance($parameters);
                
                $this->session->set_flashdata('success', "Maintenance date added.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    function updateMaintenance($assets_maintenance_id){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["assets_maintenance_id"] = $assets_maintenance_id;
             
            $this->form_validation->set_rules('assets_maintenance_id', 'Record ID', array('trim','xss_clean','required','is_natural_no_zero', array("maintenance_accessible", array($this->maintenancemodel, "isMaintenanceAccessibleByCurrentAccessArray"))), array("maintenance_accessible" => "Maintenance record selected doesn't exist or you have no permission to access it.")); 
            $this->form_validation->set_rules('maintenance_date', 'Maintenance Date', array('trim','xss_clean','required','is_ymmmmdd_date'));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["assets_maintenance_id"] = $this->input->post("assets_maintenance_id", TRUE);
                $parameters["maintenance_date"] = mysql_date($this->input->post("maintenance_date", TRUE));
                
                $this->maintenancemodel->updateMaintenance($parameters);
                
                $this->session->set_flashdata('success', "Maintenance date updated.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    function deleteMaintenance($assets_maintenance_id){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["assets_maintenance_id"] = $assets_maintenance_id;
             
            $this->form_validation->set_rules('assets_maintenance_id', 'Record ID', array('trim','xss_clean','required','is_natural_no_zero', array("maintenance_accessible", array($this->maintenancemodel, "isMaintenanceAccessibleByCurrentAccessArray"))), array("maintenance_accessible" => "Maintenance record selected doesn't exist or you have no permission to access it."));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["assets_maintenance_id"] = $this->input->post("assets_maintenance_id", TRUE);
                
                $this->maintenancemodel->deleteMaintenance($parameters);
                
                $this->session->set_flashdata('success', "Maintenance date deleted");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json(); 
    }
    
    function _check_is_asset_accessible($assets_id){
        
        $this->form_validation->set_message('_check_is_asset_accessible', 'You have no permission to access this asset.');
        
        return $this->assetmodel->isAssetAccessibleByAccessArray(array("assets_id"=>$assets_id, "access_array"=>$this->access_array));
    }
}