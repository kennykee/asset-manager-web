<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('user/usermodel');
        $this->load->model('user/rolemodel');
    } 
    
    public function index()
    {
        $data = array();
        $data["roles"] = array();
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "asc";
        $parameters["sort_field"] = "id";
        
        $fields = array("person_name"=>1, "status"=>1, "id"=>1, "email"=>1, "web_login_datetime" => 1);
            
        if(is_natural_number($this->input->get("page_no", TRUE))){
            $parameters["page_no"] = $this->input->get("page_no", TRUE);
        }
        if(is_natural_number($this->input->get("count_per_page", TRUE))){
            $parameters["count_per_page"] = $this->input->get("count_per_page", TRUE);
        }
        if(is_sort_order($this->input->get("sort", TRUE))){
            $parameters["sort"] = strtolower($this->input->get("sort", TRUE));
        }
        if(isset($fields[strtolower($this->input->get("sort_field", TRUE))])){
            $parameters["sort_field"] = strtolower($this->input->get("sort_field", TRUE));    
        }    
        
        $data["users"] = $this->usermodel->getUsers($parameters);
        
        $users_id_list = array();
        
        foreach($data["users"] as $user){
            $users_id_list[] = $user["id"];
        }
        
        if(count($users_id_list) > 0){
                
            $roles_parameters = array();
            $roles_parameters["page_no"] = 1;
            $roles_parameters["count_per_page"] = 200;
            $roles_parameters["sort"] = "asc";
            $roles_parameters["sort_field"] = "roles_name";   
            $roles_parameters["users"] = $users_id_list;
            
            $data["roles"] = $this->rolemodel->getRolesByUsers($roles_parameters);    
        }
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/user', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function viewUser($users_id){ 
        
        $data = array();
        
        $data["users"] = $this->usermodel->getAllUsers();
        $data["users_id"] = $users_id;
        $data["current_roles"] = array();
        
        $users_dropdown = array();
        
        foreach($data["users"] as $key=>$user){
            $users_dropdown[$user["id"]] = $user["person_name"];
        }
        
        $data["users_dropdown"] = $users_dropdown;
        
        if(strlen($users_id) > 0){
            
            $roles_parameters = array();
            $roles_parameters["page_no"] = 1;
            $roles_parameters["count_per_page"] = 100;
            $roles_parameters["sort"] = "asc";
            $roles_parameters["sort_field"] = "roles_name";
            
            $fields = array("roles_name"=>1, "id"=>1);
                
            if(is_natural_number($this->input->get("page_no", TRUE))){
                $roles_parameters["page_no"] = $this->input->get("page_no", TRUE);
            }
            if(is_natural_number($this->input->get("count_per_page", TRUE))){
                $roles_parameters["count_per_page"] = $this->input->get("count_per_page", TRUE);
            }
            if(is_sort_order($this->input->get("sort", TRUE))){
                $roles_parameters["sort"] = strtolower($this->input->get("sort", TRUE));
            }
            if(isset($fields[strtolower($this->input->get("sort_field", TRUE))])){
                $roles_parameters["sort_field"] = strtolower($this->input->get("sort_field", TRUE));    
            }    
            
            $roles_parameters["users"] = array($users_id);
            
            $current_roles = $this->rolemodel->getRolesByUsers($roles_parameters);
            
            if(isset($current_roles[$users_id])){
                    
                $data["current_roles"] = $current_roles[$users_id];
                
            }
        }

        $data["roles"] = $this->rolemodel->getAllRoles(array("users" => array($users_id)));
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/userview', $data);
        $this->load->view('admin/common/footer', $data);
    }
       
    public function updateUser($users_id){ /* Check for enable and disable access matrix*/
        
        $_POST["users_id"] = $users_id;
             
        $this->form_validation->set_rules('users_id', 'User', array('trim','xss_clean','required','is_natural_no_zero', array("user_exist", array($this->usermodel, "isUserIDExist"))), array("user_exist" => "User selected does not exist!"));
        $this->form_validation->set_rules('person_name', 'Person Name', 'trim|xss_clean|strip_tags|required');
        $this->form_validation->set_rules('username', 'Username', 'trim|xss_clean|strip_tags|required|alpha_dash|callback__check_username');
        $this->form_validation->set_rules('users_password', 'Password', 'trim|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email');
        $this->form_validation->set_rules('api_key', 'API Key', 'trim|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|xss_clean|required|in_list[0,1]', array("in_list" => "Invalid status selected"));
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
                    
            $parameters = array();    
            
            $parameters["users_id"] = $this->input->post("users_id", TRUE);
            $parameters["person_name"] = $this->input->post("person_name", TRUE);
            $parameters["username"] = $this->input->post("username", TRUE);
            $parameters["users_password"] = $this->input->post("users_password", TRUE)? $this->input->post("users_password", TRUE) : NULL;
            $parameters["email"] = $this->input->post("email", TRUE);
            $parameters["api_key"] = $this->input->post("api_key", TRUE)? $this->input->post("api_key", TRUE) : NULL;
            $parameters["status"] = $this->input->post("status", TRUE);
            
            $this->usermodel->updateUser($parameters);
            
            /* reconstruct CU file */
            if($this->input->post("users_id", TRUE)){
                $this->load->model('auth/authmodel');
                $this->authmodel->reconstructCUAccessMatrix($this->input->post("users_id", TRUE));
            }
            
            $this->session->set_flashdata('success', "User updated.");
            redirect(site_url($this->config->item("user_view", "routes_uri") . "/" . $users_id));
            return TRUE;
        }
        
        $data = array();
        
        $data["users"] = $this->usermodel->getAllUsers();
        $data["users_id"] = $users_id;
        $data["current_roles"] = array();
        
        $users_dropdown = array();
        
        foreach($data["users"] as $key=>$user){
            $users_dropdown[$user["id"]] = $user["person_name"];
        }
        
        $data["users_dropdown"] = $users_dropdown;
        
        if(strlen($users_id) > 0){
            
            $roles_parameters = array();
            $roles_parameters["page_no"] = 1;
            $roles_parameters["count_per_page"] = 100;
            $roles_parameters["sort"] = "asc";
            $roles_parameters["sort_field"] = "roles_name";
            
            $fields = array("roles_name"=>1, "id"=>1);
                
            if(is_natural_number($this->input->get("page_no", TRUE))){
                $roles_parameters["page_no"] = $this->input->get("page_no", TRUE);
            }
            if(is_natural_number($this->input->get("count_per_page", TRUE))){
                $roles_parameters["count_per_page"] = $this->input->get("count_per_page", TRUE);
            }
            if(is_sort_order($this->input->get("sort", TRUE))){
                $roles_parameters["sort"] = strtolower($this->input->get("sort", TRUE));
            }
            if(isset($fields[strtolower($this->input->get("sort_field", TRUE))])){
                $roles_parameters["sort_field"] = strtolower($this->input->get("sort_field", TRUE));    
            }    
            
            $roles_parameters["users"] = array($users_id);
            
            $current_roles = $this->rolemodel->getRolesByUsers($roles_parameters);
            
            if(isset($current_roles[$users_id])){
                    
                $data["current_roles"] = $current_roles[$users_id];
                
            }
        }

        $data["roles"] = $this->rolemodel->getAllRoles(array("users" => array($users_id)));
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/userupdate', $data);
        $this->load->view('admin/common/footer', $data);
    }    
    
    public function addUser(){
             
        $this->form_validation->set_rules('person_name', 'Person Name', 'trim|xss_clean|strip_tags|required');
        $this->form_validation->set_rules('username', 'Username', 'trim|xss_clean|strip_tags|required|alpha_dash|callback__check_new_username');
        $this->form_validation->set_rules('users_password', 'Password', 'trim|xss_clean|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email');
        $this->form_validation->set_rules('api_key', 'API Key', 'trim|xss_clean|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|xss_clean|required|in_list[0,1]', array("in_list" => "Invalid status selected"));
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
                    
            $parameters = array();    
            
            $parameters["person_name"] = $this->input->post("person_name", TRUE);
            $parameters["username"] = $this->input->post("username", TRUE);
            $parameters["users_password"] = $this->input->post("users_password", TRUE);
            $parameters["email"] = $this->input->post("email", TRUE);
            $parameters["api_key"] = hash("md5", $this->input->post("api_key", TRUE));
            $parameters["status"] = $this->input->post("status", TRUE);
            
            $users_id = $this->usermodel->addUser($parameters);
            
            /* Construct CU file */
            $this->load->model('auth/authmodel');
            $this->authmodel->reconstructCUAccessMatrix($users_id, TRUE);
            
            $this->session->set_flashdata('success', "New user added.");
            redirect(site_url($this->config->item("user_view", "routes_uri") . "/" . $users_id));
            return TRUE;
        }
        
        $data = array();
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/useradd', $data);
        $this->load->view('admin/common/footer', $data);
        
    }
    
    public function assignRoleToUser($users_id){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["users_id"] = $users_id;
             
            $this->form_validation->set_rules('users_id', 'User', array('trim','xss_clean','required','is_natural_no_zero', array("user_exist", array($this->usermodel, "isUserIDExist"))), array("user_exist" => "User selected does not exist!"));
            $this->form_validation->set_rules('roles_id[]', 'Roles', 'trim|xss_clean|is_natural_no_zero|is_scalar');
             
            if($this->form_validation->run()){
                
                $parameters = array();
                $parameters["users_id"] = $this->input->post("users_id", TRUE);
                $parameters["roles_id"] = $this->input->post("roles_id", TRUE);
                
                $this->rolemodel->replaceRolesOfUser($parameters);
                
                /* reconstruct CU file */
                if($users_id){
                    $this->load->model('auth/authmodel');
                    $this->authmodel->reconstructCUAccessMatrix($users_id);
                }
                
                $this->session->set_flashdata('success', "Role has been assigned.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json(); 
    }
    
    public function removeRoleFromUser($users_roles_id){ 
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["users_roles_id"] = $users_roles_id;
             
            $this->form_validation->set_rules('users_roles_id', 'Role', array('trim','xss_clean','required','is_natural_no_zero'));
             
            if($this->form_validation->run()){
                
                $parameters = array();
                $parameters["users_roles_id"] = $this->input->post("users_roles_id", TRUE);
                
                $users_id = $this->rolemodel->getUserFromRoleLink($parameters);
                
                $this->rolemodel->removeRoleFromUser($parameters);
                
                /* reconstruct CU file */
                if($users_id){
                    $this->load->model('auth/authmodel');
                    $this->authmodel->reconstructCUAccessMatrix($users_id);
                }
                
                $this->session->set_flashdata('success', "Role has been unassigned.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();        
    }

    function _check_username($username){
        
        $this->form_validation->set_message('_check_username', 'Username entered already existed. Choose another username.');
        
        $current_user_id = $this->input->post("users_id", TRUE);
        
        $user = $this->usermodel->getUserByUsername($username);
        
        if($user && ($user->id != $current_user_id)){
            return FALSE;
        }
        
        return TRUE;
    }

    function _check_new_username($username){
        
        $this->form_validation->set_message('_check_new_username', 'Username entered already existed. Choose another username.');
        
        $user = $this->usermodel->getUserByUsername($username);
        
        if($user){
            return FALSE;
        }
        return TRUE;
    }
}