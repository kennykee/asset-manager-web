<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Department extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
    } 
    
    public function index()
    {
        $data = array();
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "asc";
        $parameters["sort_field"] = "departments_name";
        
        $fields = array("departments_name"=>1);
            
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
        
        $data["departments"] = $this->departmentmodel->getDepartments($parameters);
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/department/department', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function addDepartment(){ 
    
        if($this->input->server('REQUEST_METHOD') == 'POST'){
             
            $this->form_validation->set_rules('departments_name', 'Department Name', 'trim|xss_clean|strip_tags|required|min_length[1]');
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["departments_name"] = $this->input->post("departments_name", TRUE);
                
                $this->departmentmodel->addDepartment($parameters);
                
                $this->session->set_flashdata('success', "New department added");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    public function updateDepartment($departments_id){
             
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["departments_id"] = $departments_id;
             
            $this->form_validation->set_rules('departments_id', 'Department ID', array('trim','xss_clean','required','is_natural_no_zero', array("department_exist", array($this->departmentmodel, "isDepartmentIDExist"))), array("department_exist" => "Department selected doesn't exist")); 
            $this->form_validation->set_rules('departments_name', 'Department Name', 'trim|xss_clean|strip_tags|required|min_length[1]');
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["departments_id"] = $this->input->post("departments_id", TRUE);
                $parameters["departments_name"] = $this->input->post("departments_name", TRUE);
                
                $this->departmentmodel->updateDepartment($parameters);
                
                $this->session->set_flashdata('success', "Department updated.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
        
    }
    
    public function deleteDepartment($departments_id){ /* Set all asset to no department. */
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["departments_id"] = $departments_id;
             
            $this->form_validation->set_rules('departments_id', 'Department ID', 'trim|xss_clean|required|is_natural_no_zero');
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["departments_id"] = $this->input->post("departments_id", TRUE);
                
                $this->departmentmodel->deleteDepartment($parameters);
                
                $this->session->set_flashdata('success', "Department deleted");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
}