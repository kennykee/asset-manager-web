<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('config/configmodel');
    } 
    
    public function index()
    {
        $data = array();
        
        $data["configs"] = $this->configmodel->getConfigs();
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/config/config', $data);
        $this->load->view('admin/common/footer', $data);
    }
       
    public function updateConfig(){ 
        
        $this->form_validation->set_rules('configs[]', 'Configuration Parameters', 'trim|xss_clean|strip_tags|required', array("required" => "Ensure all field are entered."));
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
            
            $parameters = array();
            
            $parameters["configs"] = $this->input->post("configs", TRUE);
            
            $this->configmodel->updateConfig($parameters);
            
            $this->session->set_flashdata('success', "Configuration updated.");
            redirect(site_url($this->config->item("config", "routes_uri")));
            return TRUE;
        }
        $this->_getform();
    }   
    
    function _getform(){
        
        $data = array();
        
        $data["configs"] = $this->configmodel->getConfigs();
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/config/configupdate', $data);
        $this->load->view('admin/common/footer', $data);
    } 
}