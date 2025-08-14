<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Department extends Mobile_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
    } 
    
    public function index()
    {
        $this->downloadDepartment();
    }
    
    function downloadDepartment(){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $data = array();
            
            $departments = $this->departmentmodel->getAllDepartment();
            
            foreach($departments as $department){
                $departments_row = array();
                $departments_row["departments_id"] = $department["id"];
                $departments_row["departments_name"] = $department["departments_name"];
                $data[] = $departments_row;
            }
            
            $this->json_output->setData($data);
            $this->json_output->setFlagSuccess();
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
}