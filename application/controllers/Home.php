<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->library('session');
        $this->load->model('auth/authmodel');
        $this->config->load('config-webapp');
    } 
    
	public function index()
	{
	    redirect(site_url('admin/user/dashboard'));
	}
    
    public function login(){
        
        $this->form_validation->set_rules('username', 'Username', 'trim|xss_clean|required');
        $this->form_validation->set_rules('pass-auth', 'Password', 'trim|xss_clean|required|callback__check_auth');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
            redirect(site_url($this->config->item("dashboard", "routes_uri")));
        }
            
        $this->_unsetSession();
        $this->load->view('public/common/header');
        $this->load->view('public/home');
        $this->load->view('public/common/footer');
    }
    
    public function logout(){
        $this->_unsetSession();
        redirect(site_url($this->config->item("login", "routes_uri"))); 
    }
    
    function _setSession($userData){
        
        $this->session->set_userdata('users_id', $userData["id"]);
        $this->session->set_userdata('person_name', $userData["person_name"]);
        
        $parameters = array();
        $parameters["users_id"] = $userData["id"];
        $this->authmodel->updateLoginInfo($parameters);
        
        $access_matrix = $this->authmodel->getMyAccess($parameters);
        
        $cache_user = array();
        $cache_user["access_matrix"] = $access_matrix;
        $cache_user["menu"] = $this->authmodel->getMenu($access_matrix);
        $cache_user["api_access"] = hash("md5", $userData["id"] . "::" . $userData["api_key"]);
        $cache_user["status"] = $userData["status"];    
        
        $this->cache->file->save('cu-' . $userData["id"], $cache_user, 100000000);
    }
    
    function _unsetSession(){
        $this->session->unset_userdata('users_id');
        $this->session->unset_userdata('person_name');
        $this->session->sess_destroy();  
    }

    function _check_auth($password){
        
        $username = $this->input->post("username", TRUE);
        
        $parameters = array();
        $parameters["username"] = $username;
        $parameters["users_password"] = $password;
        
        $result = $this->authmodel->checkLogin($parameters);
        
        if($result){
            if($result["status"] == "1"){
                $this->_setSession($result);
                return TRUE;
            }
            $this->form_validation->set_message('_check_auth', "Your account has been locked.");    
            return FALSE;
        }
        
        $this->form_validation->set_message('_check_auth', "Incorrect username or password.");
        return FALSE;
    }
}
