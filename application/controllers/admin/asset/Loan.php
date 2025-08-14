<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Loan extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
        $this->load->model('asset/assetmodel');
        $this->load->model('asset/loanmodel');
        $this->load->model('asset/transfermodel');
    } 
    
    public function index()
    {
        $this->viewLoan();
    }
    
    public function viewLoan($departments_id = "0"){
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "desc";
        $parameters["sort_field"] = "assets_departments_loan.datetime_created";
        
        $fields = array("quantity"=>1, "assets_name"=>1, "approver_name"=>1, "assets_departments_loan.datetime_created"=>1, "borrower_name"=>1, "borrower_entity"=>1);
        
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
        
        $tabs = array("loan_current"=>"Loaned Out Assets", "loan_history"=>"Loan History");
        
        $current_tab = "loan_current";
        
        if(isset($tabs[strtolower($this->input->get("tab", TRUE))])){
            $current_tab = strtolower($this->input->get("tab", TRUE));    
        }
        
        $data["tabs"] = $tabs;
        $data["current_tab"] = $current_tab;
        
        $parameters["current_tab"] = $current_tab;
        
        $this->load->view('admin/common/header', $data);
        
        if(count($departments) > 0){
            
            if(isset($departments[$departments_id])){
                $data["current_department_id"] = $departments_id;
            }else{
                reset($departments);
                $data["current_department_id"] = key($departments);
            }
            
            $data["current_department_name"] = $departments[$data["current_department_id"]];
            
            $parameters["departments"] = array($data["current_department_id"]);
                
            switch($current_tab){
                
                case "loan_history": 
                        if($parameters["sort_field"] == "assets_departments_loan.datetime_created"){
                            $parameters["sort_field"] = "datetime_created";    
                        }
                        $current_data = $this->loanmodel->getLoanHistoryByDepartments($parameters);
                        if(isset($current_data[$data["current_department_id"]])){
                            $data["current_data"] = $current_data[$data["current_department_id"]];
                        }
                        $this->load->view('admin/loan/loanhistory', $data);
                        break;
                default: /* loan_current */
                
                        /* Get all loaned asset info */
                        $loan_parameters = $parameters;
                        $loan_parameters["count_per_page"] = 1000;
                        $loan_parameters["departments"] = array_keys($departments);
                        $loan_data = $this->loanmodel->getLoanByDepartments($loan_parameters);
                        
                        $loan_data_array = array();
                        
                        foreach($loan_data as $loan_departments_id => $loan_department){
                            
                            $loan_department_name = isset($departments[$loan_departments_id])? $departments[$loan_departments_id] : "No Department";
                            
                            foreach($loan_department as $loan_asset){
                                $loan_asset["formatted_date"] = date("d-M-Y g:ia",strtotime($loan_asset["datetime_created"]));
                                $loan_asset["departments_name"] = $loan_department_name;
                                $loan_data_array[$loan_asset["assets_departments_loan_id"]] = $loan_asset;                                  
                            }   
                        }
                        
                        $data["loan_data"] = $loan_data_array;
                        
                        $this->pagination_output->resetPagination();
                            
                        $current_data = $this->loanmodel->getLoanByDepartments($parameters);
                        if(isset($current_data[$data["current_department_id"]])){
                            $data["current_data"] = $current_data[$data["current_department_id"]];
                        }

                        $this->load->view('admin/loan/loan', $data);
                        break;
            }
        }
        
        $this->load->view('admin/common/footer', $data);
    }
    
    public function viewIndividualLoan($loan_id){/* Access control at content level. Check whether loaned asset belonged to department. */ 
        
    }
    
    public function updateLoan($loan_id){ /* Access control at content level */
        
    }    
    
    function addLoanPost($assets_id = 0){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $this->form_validation->set_rules('assets_departments_id', 'Record', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_asset_loanable');
            $this->form_validation->set_rules('quantity', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
            $this->form_validation->set_rules('borrower_name', 'Borrower Name', array('trim','xss_clean','strip_tags','required'));
            $this->form_validation->set_rules('borrower_entity', 'Borrower Company', array('trim','xss_clean','strip_tags','required'));
            $this->form_validation->set_rules('return_date', 'Return Date', array('trim','xss_clean','required','is_ymmmmdd_date'), array("is_ymmmmdd_date"=>"Invalid Time Entered"));
            $this->form_validation->set_rules('return_time', 'Return Time', array('trim','xss_clean','required','is_valid_time'), array("is_valid_time"=>"Invalid Time Entered"));
            $this->form_validation->set_rules('approver_name', 'Approver Name', array('trim','xss_clean','strip_tags','required'));
            $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags', 'required'));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["assets_departments_id"] = $this->input->post("assets_departments_id", TRUE);
                $parameters["quantity"] = $this->input->post("quantity", TRUE);
                $parameters["borrower_name"] = $this->input->post("borrower_name", TRUE);
                $parameters["borrower_entity"] = $this->input->post("borrower_entity", TRUE);
                $parameters["return_date"] = $this->input->post("return_date", TRUE);
                $parameters["return_time"] = $this->input->post("return_time", TRUE);
                $parameters["approver_name"] = $this->input->post("approver_name", TRUE);
                $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
                $parameters["return_loan_form"] = TRUE;
                
                $return_data = $this->loanmodel->addLoan($parameters);
                
                /* Add to success notification and operation queue */
                $this->session->set_flashdata("operation_queue", array("init_loan_form_download"));
                $this->session->set_flashdata("operation_data", array("loan_form_url"=>$return_data["loan_form_url"]));
                
                $this->session->set_flashdata('success', "Asset loaned. <b><a target='_blank' href='" . $return_data["loan_form_url"] ."'><i class='fa fa-file-excel-o'></i> Click to download loan form.</a></b>");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }    
            
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    public function addLoan($assets_id = 0){ /* Access control at content level */
        
        $data = array();
        $data["current_data"] = array();
        $data["current_asset_name"] = "Loan Asset";
        $data["current_asset_id"] = $assets_id;
        $data["barcode"] = 0;
        $data["current_tab"] = "loan_add";
        
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
        
        $tabs = array("loan_add"=>"Loan Asset", "loan_history"=>"Loan History");
        
        $current_tab = "loan_add";
        
        if($this->matrix->checkMultiAccess($this->config->item("loan_view", "routes_uri"), $departments)){
            
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
                    
                    case "loan_add":
                            $data["current_data"] = $this->_getAssetLocation($assets_id);
                            $data["current_asset"] = $current_asset; 
                            $this->load->view('admin/loan/loanadd', $data);
                            break;
                    case "loan_history":
                            $data["current_data"] = $this->_getLoanHistory($assets_id);
                            $data["current_asset"] = $current_asset;
                            $this->load->view('admin/loan/loanaddhistory', $data);
                            break;
                    default:
                            $this->load->view('admin/loan/loanadd', $data);
                }
            }else{
                $this->load->view('admin/loan/loanadd', $data);
            }    
        }else{
            $this->load->view('admin/loan/loanadd', $data);
        }
        
        $this->load->view('admin/common/footer', $data);
    }
    
    public function returnLoan($assets_departments_loan_id){ /* Access control at content level */
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
        
            $_POST["assets_departments_loan_id"] = $assets_departments_loan_id;
            
            $this->form_validation->set_rules('assets_departments_loan_id', 'Record ID', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_loan_returnable');
            $this->form_validation->set_rules('quantity', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
            $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags'));
        
            if($this->form_validation->run()){
                
                $parameters = array();
                $parameters["assets_departments_loan_id"] = $this->input->post("assets_departments_loan_id", TRUE);
                $parameters["quantity"] = $this->input->post("quantity", TRUE);
                $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
                
                $this->loanmodel->returnLoan($parameters);
                
                $this->session->set_flashdata('success', "Asset has been returned.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }    
            
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
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
        $this->reportmodel->downloadLoanReport($parameters);
    }
    
    function loanForm(){
        
        $this->load->model('report/reportmodel');
        
        $parameters = array();
        $parameters["form_date"] = $this->input->get("form_date", TRUE);
        $parameters["form_department"] = $this->input->get("form_department", TRUE);
        $parameters["form_request_by"] = $this->input->get("form_request_by", TRUE);
        $parameters["form_purpose"] = $this->input->get("form_purpose", TRUE);
        $parameters["loaned_assets"] = $this->input->get("loaned_assets", TRUE);
        $parameters["form_date_loan"] = $this->input->get("form_date_loan", TRUE);
        $parameters["form_issued_by"] = $this->input->get("form_issued_by", TRUE);
        $parameters["form_approver_name"] = $this->input->get("form_approver_name", TRUE);
        $parameters["form_borrower_name"] = $this->input->get("form_borrower_name", TRUE);
        $parameters["form_date_return"] = $this->input->get("form_date_return", TRUE);
        $parameters["form_return_by"] = $this->input->get("form_return_by", TRUE);
        $parameters["form_receiving_officer"] = $this->input->get("form_receiving_officer", TRUE);
        
        if(!$parameters["loaned_assets"] || !is_array($parameters["loaned_assets"])){
            $parameters["loaned_assets"] = array();
        }
        
        $this->reportmodel->loanForm($parameters);
    }
    
    function _getAssetLocation($assets_id){
        return $this->transfermodel->getAssetLocation(array("assets_id" => $assets_id));
    }
    
    function _getLoanHistory($assets_id){
        
        $parameters_assets = array();
        $parameters_assets["assets_id"] = $assets_id;
        $parameters_assets["page_no"] = 1;
        $parameters_assets["count_per_page"] = 100;
        $parameters_assets["sort"] = "desc";
        $parameters_assets["sort_field"] = "datetime_created";
        $parameters_assets["current_tab"] = "loan_history";
        
        $fields = array("datetime_created"=>1, "quantity"=>1, "origin_departments_name"=>1, "origin_location"=>1);
        
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
        
        return $this->loanmodel->getLoanBySingleAsset($parameters_assets);
    }
    
    function _check_is_loan_returnable($assets_departments_loan_id){
        
        $this->form_validation->set_message('_check_is_loan_returnable', 'You have no permission to access this loan record or record does not exist.');
        
        /* Check loan exist and permission */
        $loan = $this->loanmodel->getLoanByID(array("assets_departments_loan_id" => $assets_departments_loan_id));
        
        if(!$loan){
            return FALSE;   
        }
        
        $loaned_quantity = $loan["quantity"];
        $assets_departments_id = $loan["assets_departments_id"];
        
        $departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id" => $assets_departments_id));
        
        if(!$departments_row){
            return FALSE;
        }
        
        if(!$this->matrix->inAccessArray($departments_row["departments_id"])){
            return FALSE;
        }
        
        /* Check Quantity */
        $returning_quantity = $this->input->post("quantity", TRUE);
        if(intval($returning_quantity) > intval($loaned_quantity)){
            $this->form_validation->set_message('_check_is_loan_returnable', 'You cannot return more than loaned quantity.');
            return FALSE;
        }
        
        return TRUE;
    }
    
    function _check_is_asset_loanable($assets_departments_id){
        
        //exist, have access, enough quantity
        
        $this->form_validation->set_message('_check_is_asset_loanable', 'You have no permission to access this asset.');
        
        $quantity = $this->input->post("quantity", TRUE);
        
        $departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id" => $assets_departments_id));
        
        /* check if from same department and location, prompt error */
        if($departments_row){
            
            if(!$this->matrix->inAccessArray($departments_row["departments_id"])){
                return FALSE;
            }
            
            /* Verify for loan, not exceeding available quantity. Transfer quantity. */
            $this->load->model('asset/loanmodel');
            $loan_result = $this->loanmodel->getLoansByAssetsDepartmentsID(array("assets_departments_id"=>$assets_departments_id));
            
            $loan_quantity = 0;
            
            foreach($loan_result as $loan_row){
                $loan_quantity += intval($loan_row["quantity"]);
            }
            
            if((intval($departments_row["quantity"]) - $loan_quantity - intval($quantity)) < 0){
                $this->form_validation->set_message('_check_is_asset_loanable', 'You can loan available quantity only.');
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }
}