<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('user/usermodel');
    } 
    
    public function index()
    {
        $data = array();
        $data["api_key"] = "User doesn't exist. Please contact administrator.";
        
        $user = $this->usermodel->getUserByUserID($this->session->userdata("users_id"));
        
        if($user){
            $data["api_key"] = $user["api_key"];
        }
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/dashboard', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    function changePassword(){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
             
            $this->form_validation->set_rules('existing_password', 'Existing password', array('trim','xss_clean','required', array("check_existing_password", array($this->usermodel, "isPasswordMatchCurrentUser"))), array("check_existing_password" => "Existing password not match.")); 
            $this->form_validation->set_rules('new_password', 'New Password', 'trim|xss_clean|required|min_length[6]|matches[repeat_password]');
            $this->form_validation->set_rules('repeat_password',"Repeat Password", "trim|xss_clean|required");
            $this->form_validation->set_message('matches', 'The two passwords do not match.'); 
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["users_id"] = $this->session->userdata("users_id");
                $parameters["new_password"] = $this->input->post("new_password", TRUE);
                
                $this->usermodel->changePassword($parameters);
                
                $this->session->set_flashdata('success', "Password updated.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    function regenerateAPI(){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $parameters = array();
            $parameters["users_id"] = $this->session->userdata("users_id");
            
            $this->usermodel->regenerateAPI($parameters);
            
            $this->session->set_flashdata('success', "API regenerated.");
            $this->json_output->setFlagSuccess();
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
}