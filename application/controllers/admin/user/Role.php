<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('user/usermodel');
        $this->load->model('user/rolemodel');
        $this->load->model('department/departmentmodel');
    } 
    
    public function index()
    {
        $data = array();
        $data["users"] = array();
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "asc";
        $parameters["sort_field"] = "roles_name";
        
        $fields = array("roles_name"=>1, "id"=>1);
            
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
        
        $data["roles"] = $this->rolemodel->getRoles($parameters);
        
        $roles_id_list = array();
        
        foreach($data["roles"] as $role){
            $roles_id_list[] = $role["id"];
        }
        
        if(count($roles_id_list) > 0){
                
            $users_parameters = array();
            $users_parameters["page_no"] = 1;
            $users_parameters["count_per_page"] = 200;
            $users_parameters["sort"] = "asc";
            $users_parameters["sort_field"] = "person_name";   
            $users_parameters["roles"] = $roles_id_list;
            
            $data["users"] = $this->usermodel->getUsersByRoles($users_parameters);    
        }
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/role', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function viewRole($roles_id){
         
        $data = array();
        
        $data["roles"] = $this->rolemodel->getAllRoles();
        $data["roles_id"] = $roles_id;
        $data["current_users"] = array();
        
        $roles_dropdown = array();
        
        foreach($data["roles"] as $key=>$role){
            $roles_dropdown[$role["id"]] = $role["roles_name"];
        }
        
        $data["roles_dropdown"] = $roles_dropdown;
        
        if(strlen($roles_id) > 0){
            
            $roles_parameters = array();
            $roles_parameters["page_no"] = 1;
            $roles_parameters["count_per_page"] = 100;
            $roles_parameters["sort"] = "asc";
            $roles_parameters["sort_field"] = "person_name";
            
            $fields = array("person_name"=>1, "status"=>1, "id"=>1, "email"=>1, "web_login_datetime" => 1);
                
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
            
            $roles_parameters["roles"] = array($roles_id);
            
            $current_users = $this->usermodel->getUsersByRoles($roles_parameters);
            
            if(isset($current_users[$roles_id])){
                    
                $data["current_users"] = $current_users[$roles_id];
                
            }
        }

        $data["users"] = $this->usermodel->getAllUsers(array("roles" => array($roles_id)));
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/roleview', $data);
        $this->load->view('admin/common/footer', $data);
    }
       
    public function updateRole($roles_id){
        
        $_POST["roles_id"] = $roles_id;
        
        $this->form_validation->set_rules('roles_id', 'Role ID', array('trim','xss_clean','required','is_natural_no_zero'));
        $this->form_validation->set_rules('roles_name', 'Role Name', 'trim|xss_clean|strip_tags|required');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
                    
            $parameters = array();    
            
            $parameters["roles_id"] = $this->input->post("roles_id", TRUE);
            $parameters["roles_name"] = $this->input->post("roles_name", TRUE);
            
            $this->rolemodel->updateRole($parameters);
            
            $this->session->set_flashdata('success', "Role updated.");
            redirect(site_url($this->config->item("role_view", "routes_uri") . "/" . $roles_id));
            return TRUE;
        }
         
        $data = array();
        
        $data["roles"] = $this->rolemodel->getAllRoles();
        $data["roles_id"] = $roles_id;
        $data["current_users"] = array();
        
        $roles_dropdown = array();
        
        foreach($data["roles"] as $key=>$role){
            $roles_dropdown[$role["id"]] = $role["roles_name"];
        }
        
        $data["roles_dropdown"] = $roles_dropdown;
        
        if(strlen($roles_id) > 0){
            
            $roles_parameters = array();
            $roles_parameters["page_no"] = 1;
            $roles_parameters["count_per_page"] = 100;
            $roles_parameters["sort"] = "asc";
            $roles_parameters["sort_field"] = "person_name";
            
            $fields = array("person_name"=>1, "status"=>1, "id"=>1, "email"=>1, "web_login_datetime" => 1);
                
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
            
            $roles_parameters["roles"] = array($roles_id);
            
            $current_users = $this->usermodel->getUsersByRoles($roles_parameters);
            
            if(isset($current_users[$roles_id])){
                    
                $data["current_users"] = $current_users[$roles_id];
                
            }
        }

        $data["users"] = $this->usermodel->getAllUsers(array("roles" => array($roles_id)));
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/roleupdate', $data);
        $this->load->view('admin/common/footer', $data);
    }    
    
    public function deleteRole($roles_id){ /* Delete all from roles_function, roles_functions_parameter and users_roles */
         
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["roles_id"] = $roles_id;
             
            $this->form_validation->set_rules('roles_id', 'Roles ID', 'trim|xss_clean|required|is_natural_no_zero');
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["roles_id"] = $this->input->post("roles_id", TRUE);
                
                $this->rolemodel->deleteRole($parameters);
                
                $this->session->set_flashdata('success', "Role deleted");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }    
    
    public function addRole(){ 
        
        $this->form_validation->set_rules('roles_name', 'Role Name', 'trim|xss_clean|strip_tags|required');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
                    
            $parameters = array();    
            
            $parameters["roles_name"] = $this->input->post("roles_name", TRUE);
            
            $roles_id = $this->rolemodel->addRole($parameters);
            
            $this->session->set_flashdata('success', "New role added.");
            redirect(site_url($this->config->item("role_view", "routes_uri") . "/" . $roles_id));
            return TRUE;
        }
        
        $data = array();
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/roleadd', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function viewPermission($roles_id){
        
        $data = array();
        
        $data["roles"] = $this->rolemodel->getAllRoles();
        $data["roles_id"] = $roles_id;
        
        $roles_dropdown = array();
        
        foreach($data["roles"] as $key=>$role){
            $roles_dropdown[$role["id"]] = $role["roles_name"];
        }
        
        $data["roles_dropdown"] = $roles_dropdown;
        
        $data["functions"] = $this->rolemodel->getAllFunctions(array("roles" => array($roles_id)));
        
        $data["departments"] = $this->departmentmodel->getAllDepartment();
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/user/rolepermissionview', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function updatePermission($roles_id){
        
        $_POST["roles_id"] = $roles_id;
        
        $this->form_validation->set_rules('roles_id', 'Roles', 'trim|xss_clean|is_natural_no_zero|required');
        $this->form_validation->set_rules('parameters[]', 'Parameters', 'trim|xss_clean|strip_tags|required|callback__clean_parameter');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
                    
            $parameters = array();    
            $parameters["roles_id"] = $this->input->post("roles_id", TRUE);
            $parameters["parameters"] = $this->input->post("parameters", TRUE);
            
            $this->rolemodel->updatePermission($parameters);
            
            $users_roles = $this->rolemodel->getAllUsersByRoles(array("roles" => array($parameters["roles_id"])));
            
            /* Reconstruct CU*/
            foreach($users_roles as $user){
                $this->load->model('auth/authmodel');
                $this->authmodel->reconstructCUAccessMatrix($user["users_id"]);   
            }
            
            $this->session->set_flashdata('success', "Role updated.");
            redirect(site_url($this->config->item("role_permission_view", "routes_uri") . "/" . $roles_id));
            return TRUE;
        }
        
        $this->viewPermission($roles_id);
    }
    
    public function assignRoleToUser($roles_id){ 
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["roles_id"] = $roles_id;
             
            $this->form_validation->set_rules('users_id[]', 'User', array('trim','xss_clean','required', 'is_scalar','is_natural_no_zero', array("user_exist", array($this->usermodel, "isUserIDExist"))), array("user_exist" => "User selected does not exist!"));
            $this->form_validation->set_rules('roles_id', 'Roles', 'trim|xss_clean|is_natural_no_zero|required');
             
            if($this->form_validation->run()){
                
                $parameters = array();
                $parameters["users_id"] = $this->input->post("users_id", TRUE);
                $parameters["roles_id"] = $this->input->post("roles_id", TRUE);
                
                $this->rolemodel->replaceUsersOfRole($parameters);
                
                /* reconstruct CU file */
                if(is_array($parameters["users_id"])){
                    foreach($parameters["users_id"] as $users_id){
                        $this->load->model('auth/authmodel');
                        $this->authmodel->reconstructCUAccessMatrix($users_id);   
                    }
                }
                
                $this->session->set_flashdata('success', "User has been assigned.");
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
                
                $this->session->set_flashdata('success', "User has been unassigned.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json(); 
    }

    function _clean_parameter($permission){
        
        $array_data = json_decode($permission, TRUE); 
        
        if(!$array_data){
            $empty_array = array();
            return $empty_array;
        }
        
        $array_data = array_filter($array_data, "is_scalar");    
        
        return $array_data;
    }
}