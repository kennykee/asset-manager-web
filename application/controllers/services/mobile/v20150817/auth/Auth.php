<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('auth/authmodel');
        $this->config->load('config-mobileapp');
    } 
    
    private $users_data = FALSE;
    
	public function index()
	{
	    //silence is golden
	}
    
    public function login(){
        
        $this->form_validation->set_rules('username', 'Username', 'trim|xss_clean|required');
        $this->form_validation->set_rules('pass-auth', 'Password', 'trim|xss_clean|required|callback__check_auth');
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            if($this->form_validation->run()){
                
                $data = array();
                $data["id"] = $this->users_data["id"];
                $data["person_name"] = $this->users_data["person_name"];
                $data["api_key"] = $this->users_data["api_key"];
                
                $this->json_output->setFlagSuccess();
                $this->json_output->setData($data);
                
            }else{
                $this->json_output->setFormValidationError();
            }
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();           
    }
    
    function _check_auth($password){
        
        $username = $this->input->post("username", TRUE);
        
        $parameters = array();
        $parameters["username"] = $username;
        $parameters["users_password"] = $password;
        
        $result = $this->authmodel->checkLogin($parameters);
        
        if($result){
            if($result["status"] == "1"){
                $this->users_data = $result;
                return TRUE;
            }
            $this->form_validation->set_message('_check_auth', "Your account has been locked.");    
            return FALSE;
        }
        
        $this->form_validation->set_message('_check_auth', "Incorrect username or password.");
        return FALSE;
    }
}
