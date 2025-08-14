<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Discrepancy extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
        $this->load->model('asset/assetmodel');
        $this->load->model('asset/loanmodel');
        $this->load->model('asset/transfermodel');
        $this->load->model('asset/discrepancymodel');
    } 
    
    public function index()
    {
        $this->viewDiscrepancy();
    }
    
    public function viewDiscrepancy($departments_id = "0"){
            
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "desc";
        $parameters["sort_field"] = "assets_name";
        
        $fields = array("assets_name"=>1, "datetime_scanned"=>1);
        
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
        
        $data["page_no"] = $parameters["page_no"];
        $data["count_per_page"] = $parameters["count_per_page"];
        
        $parameters_access = array();
        $parameters_access["access_array"] = $this->access_array; 
        
        $departments = $this->departmentmodel->getDepartmentsByAccessArray($parameters_access);
        $data["departments"] = $departments;
        
        $tabs = array("detected_discrepancy"=>"Detected Discrepancy", "tracking_history"=>"Asset Tracking History");
        
        $current_tab = "detected_discrepancy";
        
        if(isset($tabs[strtolower($this->input->get("tab", TRUE))])){
            $current_tab = strtolower($this->input->get("tab", TRUE));    
        }
        
        $from_date = date('d-F-Y', strtotime('-1 month'));
        
        if($this->input->get("from_date", TRUE) && is_ymmmmdd_date($this->input->get("from_date", TRUE))){
            $from_date = $this->input->get("from_date", TRUE);    
        }
        
        $data["tabs"] = $tabs;
        $data["current_tab"] = $current_tab;
        $data["from_date"] = $from_date;
        
        $parameters["current_tab"] = $current_tab;
        $parameters["from_date"] = $from_date;
        
        $this->load->view('admin/common/header', $data);
        
        if(count($departments) > 0){
            
            if(isset($departments[$departments_id])){
                $data["current_department_id"] = $departments_id;
            }else{
                reset($departments);
                $data["current_department_id"] = key($departments);
            }
            
            $data["current_department_name"] = $departments[$data["current_department_id"]];
            
            $parameters["departments_id"] = $data["current_department_id"];
                
            switch($current_tab){
                
                case "tracking_history": 
                        if($parameters["sort_field"] == "assets_name"){
                            $parameters["sort_field"] = "datetime_scanned";    
                        }
                        $data["current_data"] = $this->discrepancymodel->getTrackingHistoryByDepartments($parameters);
                        $this->load->view('admin/discrepancy/trackhistory', $data);
                        break;
                default: 
                        $data["current_data"] = $this->discrepancymodel->getDiscrepancyByDepartment($parameters);
                        $this->load->view('admin/discrepancy/discrepancy', $data);
                        break;
            }
        }
        
        $this->load->view('admin/common/footer', $data);
    }
    
    function discrepancy_individual_view($assets_id){
        
    }
    
    function downloadReport(){ /* Access control at uri level. Use query string to paginate content. */
        
        $this->load->model('report/reportmodel');
        
        $parameters = array();
        $parameters["start_date"] = $this->input->get("start_date", TRUE);
        $parameters["departments"] = $this->input->get("departments", TRUE);
        
        if(strlen($parameters["start_date"]) == 0 || !is_ymmmmdd_date($parameters["start_date"])){
            echo "Invalid start date.";
            return TRUE;
        }
        
        if(!$parameters["departments"] || !is_array($parameters["departments"])){
            $parameters["departments"] = FALSE;
            echo "Please select at least one department";
            return TRUE;
        }else if(!$this->matrix->inAccessArray($parameters["departments"])){
            echo "You have no access to one of the selected department.";
            return TRUE;
        }
        $this->reportmodel->downloadDiscrepancyReport($parameters);
    }
}