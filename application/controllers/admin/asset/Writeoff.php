<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Writeoff extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
        $this->load->model('asset/assetmodel');
        $this->load->model('asset/writeoffmodel');
        $this->load->model('asset/transfermodel');
    } 
    
    public function index()
    {
        $this->viewWriteoff();
    }
    
    public function viewWriteoff($departments_id = ""){
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "desc";
        $parameters["sort_field"] = "datetime_created";
        
        $fields = array("quantity"=>1, "assets_name"=>1, "approver_name"=>1, "datetime_created"=>1);
        
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
            
            $current_data = $this->writeoffmodel->getWriteOffByDepartments($parameters);
            
            if(isset($current_data[$data["current_department_id"]])){
                    
                $data["current_data"] = $current_data[$data["current_department_id"]];
                
            }
        }
           
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/writeoff/writeoff', $data);
        $this->load->view('admin/common/footer', $data);
    }
       
    public function updateWriteoff($writeoff_id){ /* Access control at content level */
        
    }    
    
    public function addWriteoff(){ /* Access control at content level */
        //change status of asset
        
    }
    
    public function requestWriteoff($assets_id = 0){ /* Blanket access only. Minimum viewWriteoff. */
        
        $data = array();
        $data["current_data"] = array();
        $data["current_asset_name"] = "Write Off Asset";
        $data["current_asset_id"] = $assets_id;
        $data["barcode"] = 0;
        $data["current_tab"] = "writeoff_add";
        
        $parameters_assets = array("assets_id" => array($assets_id));
        $departments_row = $this->assetmodel->getDepartmentsByAssets($parameters_assets);
        $departments_row = isset($departments_row[$assets_id])? $departments_row[$assets_id] : array();
        $departments = array();
        
        foreach($departments_row as $department){
            $departments[] = $department["departments_id"];
        }
        
        $data["departments"] = $departments;
        
        $parameters_access = array();
        $parameters_access["access_array"] = $this->access_array; 
        
        $departments_access = $this->departmentmodel->getDepartmentsByAccessArray($parameters_access);
        $data["accessible_departments"] = $departments_access;
        
        $tabs = array("writeoff_add"=>"Request Write Off", "writeoff_history"=>"Write Off History");
        
        $current_tab = "writeoff_add";
        
        if($this->matrix->checkMultiAccess($this->config->item("writeoff_request", "routes_uri"), $departments)){
            
            if(isset($tabs[strtolower($this->input->get("tab", TRUE))])){
                $current_tab = strtolower($this->input->get("tab", TRUE));    
            }
            
            $data["tabs"] = $tabs;
            $data["current_tab"] = $current_tab;
            
            $parameters_assets = array("assets_id" => $assets_id);
            
            $current_asset = $this->assetmodel->getAsset($parameters_assets);
            
            $this->load->view('admin/common/header', $data);
            
            if(isset($tabs[$current_tab]) && $current_asset){
                
                $data["current_asset_name"] = $current_asset["assets_name"];
                $data["current_asset_id"] = $current_asset["assets_id"];
                $data["barcode"] = $current_asset["barcode"];
                
                switch($current_tab){
                    
                    case "writeoff_add":
                            $data["current_data"] = $this->_getAssetLocation($assets_id);
                            $data["current_asset"] = $current_asset;
                            
                            $this->load->model('user/usermodel');
                            
                            $user_parameters = array();
                            $user_parameters["access_array"] = $departments;
                            $user_parameters["uri"] = $this->config->item("writeoff_update_access", "routes_uri");
                             
                            $data["user_permission"] = $this->usermodel->getAccessibleUsersByRoute($user_parameters);
                            
                            $this->load->view('admin/writeoff/writeoffrequest', $data);
                            break;
                    case "writeoff_history":
                            $data["current_data"] = $this->_getWriteOffHistory($assets_id);
                            $data["current_asset"] = $current_asset;
                            $this->load->view('admin/writeoff/writeoffhistory', $data);
                            break;
                    default:
                            $this->load->view('admin/writeoff/writeoffrequest', $data);
                }
            }else{
                $this->load->view('admin/writeoff/writeoffrequest', $data);
            }    
        }else{
            $this->load->view('admin/writeoff/writeoffrequest', $data);
        }
        $this->load->view('admin/common/footer', $data);
    }

    function requestWriteoffPost($assets_id = 0){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $this->form_validation->set_rules('assets_departments_id', 'Asset', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_asset_location_writable');
            $this->form_validation->set_rules('type', 'Write Off Type', array('trim','xss_clean','strip_tags', 'required', 'in_list[reduce_quantity,complete_writeoff]'));
            $this->form_validation->set_rules('quantity', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
            $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags','required'));
            $this->form_validation->set_rules('approvers[]', 'Approver', 'trim|xss_clean|is_scalar|required|is_natural_no_zero|callback__check_is_valid_approver');
            
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["assets_departments_id"] = $this->input->post("assets_departments_id", TRUE);
                $parameters["type"] = $this->input->post("type", TRUE);
                $parameters["quantity"] = $this->input->post("quantity", TRUE);
                $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
                $parameters["approvers"] = $this->input->post("approvers", TRUE);
                
                $this->writeoffmodel->requestWriteoff($parameters);
                
                $this->session->set_flashdata('success', "Request submitted.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }    
            
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
        
    }
    
    public function approveWriteoff($writeoff_id){ /* Access control at content level */
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["writeoff_id"] = $writeoff_id;
            
            $this->form_validation->set_rules('process_request', 'Approval Type', array('trim','xss_clean','integer','required','in_list[-1,1]'));
            $this->form_validation->set_rules('writeoff_id', 'Asset', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_writeoff_writable');
            $this->form_validation->set_rules('type', 'Write Off Type', array('trim','xss_clean','strip_tags', 'required', 'in_list[reduce_quantity,complete_writeoff]'));
            $this->form_validation->set_rules('quantity', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
            $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags', 'required'));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["process_request"] = $this->input->post("process_request", TRUE);
                $parameters["writeoff_id"] = $this->input->post("writeoff_id", TRUE);
                $parameters["type"] = $this->input->post("type", TRUE);
                $parameters["quantity"] = $this->input->post("quantity", TRUE);
                $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
                $parameters["assets_departments_id"] = $this->input->post("assets_departments_id", TRUE); /* Set by validation callback function */
                
                $this->writeoffmodel->approveWriteoff($parameters);
                
                $this->session->set_flashdata('success', "Write Off request has been processed.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }    
            
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    function _getAssetLocation($assets_id){
        return $this->transfermodel->getAssetLocation(array("assets_id" => $assets_id));
    }
    
    function _getWriteOffHistory($assets_id){
        
        $parameters_assets = array();
        $parameters_assets["assets_id"] = $assets_id;
        $parameters_assets["page_no"] = 1;
        $parameters_assets["count_per_page"] = 100;
        $parameters_assets["sort"] = "desc";
        $parameters_assets["sort_field"] = "datetime_created";
        $parameters_assets["current_tab"] = "writeoff_history";
        
        $fields = array("quantity"=>1, "assets_name"=>1, "approver_name"=>1, "datetime_created"=>1);
        
        if(is_natural_number($this->input->get("page_no", TRUE))){
            $parameters_assets["page_no"] = $this->input->get("page_no", TRUE);
        }
        if(is_natural_number($this->input->get("count_per_page", TRUE))){
            $parameters_assets["count_per_page"] = $this->input->get("count_per_page", TRUE);
        }
        if(is_sort_order($this->input->get("sort", TRUE))){
            $parameters_assets["sort"] = strtolower($this->input->get("sort", TRUE));
        }
        if(isset($fields[strtolower($this->input->get("sort_field", TRUE))])){
            $parameters_assets["sort_field"] = strtolower($this->input->get("sort_field", TRUE));    
        }
        
        return $this->writeoffmodel->getWriteOffBySingleAsset($parameters_assets);
    }
    
    function downloadReport(){ /* Access control at uri level. Use query string to paginate content. */
    
        $this->load->model('report/reportmodel');
        
        $parameters = array();
        $parameters["start_date"] = $this->input->get("start_date", TRUE);
        $parameters["end_date"] = $this->input->get("end_date", TRUE);
        $parameters["departments"] = $this->input->get("departments", TRUE);
        
        if(strlen($parameters["start_date"]) == 0 || !is_ymmmmdd_date($parameters["start_date"])){
            echo "Invalid start date.";
            return TRUE;
        }
        
        if(strlen($parameters["end_date"]) == 0 || !is_ymmmmdd_date($parameters["end_date"])){
            echo "Invalid end date.";
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
        $this->reportmodel->downloadWriteOffReport($parameters);
    }

    function _check_is_valid_approver($users_id){
        /* Minimum 2 approvers */
        $this->form_validation->set_message('_check_is_valid_approver', 'A minimum of 2 approvers is required');
        if(!is_array($this->input->post("approvers", TRUE)) || count($this->input->post("approvers", TRUE)) < 2){
            return FALSE;
        }
        
        $assets_departments_id = $this->input->post("assets_departments_id", TRUE);
        $assets_departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id"=>$assets_departments_id));
        
        if(!$assets_departments_row){
            $this->form_validation->set_message('_check_is_valid_approver', 'Selected asset doesn\'t exist. Please refresh page.');
            return FALSE;
        }
        
        $departments_id = $assets_departments_row["departments_id"];
        
        /* Check all accessible users */
        $this->load->model('user/usermodel');
                            
        $user_parameters = array();
        $user_parameters["access_array"] = array($departments_id);
        $user_parameters["uri"] = $this->config->item("writeoff_update_access", "routes_uri");
         
        $user_permission = $this->usermodel->getAccessibleUsersByRoute($user_parameters);
        
        $this->form_validation->set_message('_check_is_valid_approver', 'Selected approver has no access to this asset.');
        
        foreach($this->input->post("approvers", TRUE) as $users_id){
            if(!array_key_exists($users_id, $user_permission)){
                return FALSE;
            }    
        }
        
        return TRUE;
    }
    
    function _check_is_writeoff_writable($writeoff_id){
        
        $this->form_validation->set_message('_check_is_writeoff_writable', 'Please enter correct input.');
        
        $parameters = array();
        $parameters["writeoff_id"] = $writeoff_id;
        $parameters["quantity"] = intval($this->input->post("quantity", TRUE));
        
        if(!$parameters["quantity"]){
            return FALSE;
        }
        
        $writeoff_row = $this->writeoffmodel->getWriteOffByID($writeoff_id);
        
        if($writeoff_row){
            
            /* Check if already processed */
            if($writeoff_row["status"] != "0"){
                $this->form_validation->set_message('_check_is_writeoff_writable', 'This record has already been processed. Please refresh page.');
                return FALSE;
            }
            
            /* Check if having access to this record */
            if(!$this->matrix->inAccessArray($writeoff_row["origin_departments_id"])){
                $this->form_validation->set_message('_check_is_writeoff_writable', 'You have no permission to access this asset department.');
                return FALSE;
            }
            
            /* Check if having access to the asset */
            if(!$this->assetmodel->isAssetAccessibleByAccessArray(array("assets_id"=>$writeoff_row["assets_id"], "access_array"=>$this->access_array))){
                $this->form_validation->set_message('_check_is_writeoff_writable', 'You have no permission to access this asset.');
                return FALSE;    
            }

            /* Check whether enough quantity at specific department and location minus loan */
            $filter_parameters = array();
            $filter_parameters["departments_id"] = $writeoff_row["origin_departments_id"];
            $filter_parameters["location"] = $writeoff_row["origin_location"];
            $filter_parameters["assets_id"] = $writeoff_row["assets_id"];
             
            $assets_departments_row = $this->assetmodel->getAssetLocationByFilter($filter_parameters);
            
            if(!$assets_departments_row){
                $this->form_validation->set_message('_check_is_writeoff_writable', 'Asset is no longer is requested location.');
                return FALSE;
            }
            
            $quantity = $assets_departments_row["quantity"];
            
            $_POST["assets_departments_id"] = $assets_departments_row["id"];
            
            $this->load->model('asset/loanmodel');
            $loan_result = $this->loanmodel->getLoansByAssetsDepartmentsID(array("assets_departments_id"=>$assets_departments_row["id"]));
            
            $loan_quantity = 0;
            
            foreach($loan_result as $loan_row){
                $loan_quantity += intval($loan_row["quantity"]);
            }
            
            if((intval($quantity) - $loan_quantity - $parameters["quantity"]) < 0){
                $this->form_validation->set_message('_check_is_writeoff_writable', 'Write off quantity is more than available quantity. Please check asset quantity.');
                return FALSE;
            }

            /* Check valid approval sequence */
            $next_approver = $this->writeoffmodel->getNextApprover(array("writeoff_id" => $writeoff_id));
            
            if($next_approver != $this->session->userdata("users_id")){
                $this->form_validation->set_message('_check_is_writeoff_writable', 'Please wait for this request to be escalated to you. Preceeding user in the chain of approver has not response to this request yet.');
                return FALSE;
            }
            
            return TRUE;
        }
        
        $this->form_validation->set_message('_check_is_writeoff_writable', 'You have no permission to access this record or record not found.');
        return FALSE;
    }
    
    function _check_is_asset_location_writable(){
        //Allow all request
        return TRUE;
    }
}