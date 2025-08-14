<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AlertRecipient extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
        $this->load->model('notification/alertrecipientmodel');
    } 
    
    public function index(){
        $this->viewAlertRecipient();
    }
    
    public function viewAlertRecipient($departments_id = ""){ /* URI access control */
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "asc";
        $parameters["sort_field"] = "id";
        
        $fields = array("email"=>1);
            
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
        
        $data = array();
        $data["current_data"] = array();
        $data["current_department_name"] = "No Department Name";
        $data["current_department_id"] = FALSE;
        
        $parameters_access = array();
        $parameters_access["access_array"] = $this->access_array; 
        
        $departments = $this->departmentmodel->getDepartmentsByAccessArray($parameters_access);
        $data["departments"] = $departments;
        
        if(count($departments) > 0){
            
            if(isset($departments[$departments_id])){
                $data["current_department_id"] = $departments_id;
            }else{
                reset($departments);
                $data["current_department_id"] = key($departments);
            }
            
            $data["current_department_name"] = $departments[$data["current_department_id"]];
            
            $parameters["departments"] = array($data["current_department_id"]);
            
            $current_data = $this->alertrecipientmodel->getRecipientsByDepartments($parameters);
            
            if(isset($current_data[$data["current_department_id"]])){
                    
                $data["current_data"] = $current_data[$data["current_department_id"]];
                
            }
        }
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/notification/alertrecipient', $data);
        $this->load->view('admin/common/footer', $data);
    }  
    
    public function addAlertRecipient(){ /* Function access control */
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
             //not missing
            $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|strip_tags|required|valid_email');
            $this->form_validation->set_rules('departments_id', 'Department ID', array('trim','xss_clean','required','is_natural_no_zero', array("department_exist", array($this->departmentmodel, "isDepartmentIDExist")), array("access_permission", array($this->matrix, "inAccessArray"))), array("department_exist" => "Department selected doesn't exist", "access_permission" => "You do not have access to this department"));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["email"] = $this->input->post("email", TRUE);
                $parameters["departments_id"] = $this->input->post("departments_id", TRUE);
                
                $this->alertrecipientmodel->addAlertRecipient($parameters);
                
                $this->session->set_flashdata('success', "New email added");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
        
    }
    
    public function deleteAlertRecipient($recipients_id){ 
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["recipients_id"] = $recipients_id;
             
            $this->form_validation->set_rules('recipients_id', 'Recipient ID', array('trim','xss_clean','required','is_natural_no_zero', array("access_permission", array($this->alertrecipientmodel, "isRecipientAccessible"))), array("access_permission" => "You do not have access to this recipient or record is not found"));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["recipients_id"] = $this->input->post("recipients_id", TRUE);
                
                $this->alertrecipientmodel->deleteAlertRecipient($parameters);
                
                $this->session->set_flashdata('success', "Recipient deleted");
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