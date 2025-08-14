<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transfer extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
        $this->load->model('asset/assetmodel');
        $this->load->model('asset/transfermodel');
    } 
    
    public function index()
    {
        $this->viewTransfer();
    }
    
    public function viewTransfer($departments_id = ""){ /* involved in origin and destination */
    
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "desc";
        $parameters["sort_field"] = "datetime_created";
        
        $fields = array("assets_id"=>1, "quantity"=>1, "assets_name"=>1, "origin_departments_name"=>1, "origin_location"=>1, "destination_departments_name"=>1, 
                        "destination_location"=>1, "borrower_name"=>1, "borrower_entity"=>1, "loan_datetime"=>1, "return_datetime"=>1, "approver_name"=>1,
                        "datetime_created"=>1);
        
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
            
            $current_data = $this->transfermodel->getTransfersByDepartments($parameters);
            
            if(isset($current_data[$data["current_department_id"]])){
                    
                $data["current_data"] = $current_data[$data["current_department_id"]];
                
            }
        }
           
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/transfer/transfer', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function updateTransfer($transaction_id){ /* Access control at content level */
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["transaction_id"] = $transaction_id;
             
            $this->form_validation->set_rules('transaction_id', 'Record ID', array('trim','xss_clean','required','is_natural_no_zero', array("transfer_accessible", array($this->transfermodel, "isTransferAccessibleByCurrentAccessArray"))), array("transfer_accessible" => "Transfer record selected doesn't exist or you have no permission to access it.")); 
            $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags'));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["transaction_id"] = $this->input->post("transaction_id", TRUE);
                $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
                
                $this->transfermodel->updateTransfer($parameters);
                
                $this->session->set_flashdata('success', "Transfer remark updated.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }    
    
    public function addTransfer($assets_id = 0){ /* Access control at content level */
        
        $data = array();
        $data["current_data"] = array();
        $data["current_asset_name"] = "Transfer Asset";
        $data["current_asset_id"] = $assets_id;
        $data["barcode"] = 0;
        $data["current_tab"] = "transfer_add";
        
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
        
        $tabs = array("transfer_add"=>"Transfer Asset", "transfer_history"=>"Transfer History");
        
        $current_tab = "transfer_add";
        
        if($this->matrix->checkMultiAccess($this->config->item("transfer_view", "routes_uri"), $departments)){
            
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
                    
                    case "transfer_add":
                            $data["current_data"] = $this->_getAssetLocation($assets_id);
                            $data["current_asset"] = $current_asset; 
                            $this->load->view('admin/transfer/transferadd', $data);
                            break;
                    case "transfer_history":
                            $data["current_data"] = $this->_getTransferHistory($assets_id);
                            $data["current_asset"] = $current_asset;
                            $this->load->view('admin/transfer/transferhistory', $data);
                            break;
                    default:
                            $this->load->view('admin/transfer/transferadd', $data);
                }
            }else{
                $this->load->view('admin/transfer/transferadd', $data);
            }    
        }else{
            $data["tabs"] = $tabs;
            $data["current_tab"] = $current_tab;
            $this->load->view('admin/common/header', $data);
            $this->load->view('admin/transfer/transferadd', $data);
        }
        
        $this->load->view('admin/common/footer', $data);
    }
    
    function addTransferPost($assets_id = 0){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $type = $this->input->post("transfer_type", TRUE);
            
            if($type == "add_quantity"){
                $this->_addQuantity($assets_id);
            }else{
                $this->_transferQuantity($assets_id);
            }
            
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }

    function _addQuantity($assets_id){
        
        $_POST["assets_id"] = $assets_id;
        
        $this->form_validation->set_rules('assets_id', 'Asset', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_asset_accessible');
        $this->form_validation->set_rules('departments_id', 'Department', array('trim','xss_clean','is_natural_no_zero','required',array("access_permission", array($this->matrix, "inAccessArray"))), array("access_permission" => "You do not have access to selected department"));
        $this->form_validation->set_rules('quantity', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
        $this->form_validation->set_rules('location', 'Location', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags', 'required'));
         
        if($this->form_validation->run()){
            $parameters = array();
            $parameters["assets_id"] = $this->input->post("assets_id", TRUE);
            $parameters["departments_id"] = $this->input->post("departments_id", TRUE);
            $parameters["quantity"] = $this->input->post("quantity", TRUE);
            $parameters["location"] = $this->input->post("location", TRUE);
            $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
            
            $this->transfermodel->addQuantity($parameters);
            
            $this->session->set_flashdata('success', "Quantity added.");
            $this->json_output->setFlagSuccess();
        }else{
            $this->json_output->setFormValidationError();
        }       
    }
    
    function _transferQuantity($assets_id){
        
        $this->form_validation->set_rules('assets_departments_id', 'Asset', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_asset_location_transferrable');
        $this->form_validation->set_rules('departments_id', 'Department', array('trim','xss_clean','is_natural_no_zero','required',array("access_permission", array($this->matrix, "inAccessArray"))), array("access_permission" => "You do not have access to selected department"));
        $this->form_validation->set_rules('quantity', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
        $this->form_validation->set_rules('location', 'Location', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('remark', 'Remark', array('trim','xss_clean','strip_tags', 'required'));
         
        if($this->form_validation->run()){
            $parameters = array();
            $parameters["assets_departments_id"] = $this->input->post("assets_departments_id", TRUE);
            $parameters["departments_id"] = $this->input->post("departments_id", TRUE);
            $parameters["quantity"] = $this->input->post("quantity", TRUE);
            $parameters["location"] = $this->input->post("location", TRUE);
            $parameters["remark"] = $this->input->post("remark", TRUE)? $this->input->post("remark", TRUE) : "";
            
            $this->transfermodel->transferQuantity($parameters);
            
            $this->session->set_flashdata('success', "Asset transferred.");
            $this->json_output->setFlagSuccess();
        }else{
            $this->json_output->setFormValidationError();
        }    
    }
    
    function _getAssetLocation($assets_id){
        return $this->transfermodel->getAssetLocation(array("assets_id" => $assets_id));
    }
    
    function _getTransferHistory($assets_id){
        
        $parameters_assets = array();
        $parameters_assets["assets_id"] = $assets_id;
        $parameters_assets["page_no"] = 1;
        $parameters_assets["count_per_page"] = 100;
        $parameters_assets["sort"] = "desc";
        $parameters_assets["sort_field"] = "datetime_created";
        $parameters_assets["current_tab"] = "transfer_history";
        
        $fields = array("quantity"=>1, "origin_departments_name"=>1, "origin_location"=>1, "destination_departments_name"=>1, 
                        "destination_location"=>1, "datetime_created"=>1);
        
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
        
        return $this->transfermodel->getTransferBySingleAsset($parameters_assets);
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
        $this->reportmodel->downloadTransferReport($parameters);
    }
    
    function _check_is_asset_accessible($assets_id){
        
        $this->form_validation->set_message('_check_is_asset_accessible', 'You have no permission to access this asset.');
        
        return $this->assetmodel->isAssetAccessibleByAccessArray(array("assets_id"=>$assets_id, "access_array"=>$this->access_array));
    }
    
    function _check_is_asset_location_transferrable($assets_departments_id){
        
        $this->form_validation->set_message('_check_is_asset_location_transferrable', 'You have no permission to access this asset.');
        
        $destination_departments_id = $this->input->post("departments_id", TRUE);
        $quantity = $this->input->post("quantity", TRUE);
        $location = strtolower(trim($this->input->post("location", TRUE)));
        
        $departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id" => $assets_departments_id));
        
        /* check if from same department and location, prompt error */
        if($departments_row){
            
            if(!$this->matrix->inAccessArray($departments_row["departments_id"])){
                return FALSE;
            }
            
            if(($departments_row["departments_id"] == $destination_departments_id) && (strtolower(trim($departments_row["location"])) == $location)){
                $this->form_validation->set_message('_check_is_asset_location_transferrable', 'Both origin and destination department/location cannot be the same.');
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
                $this->form_validation->set_message('_check_is_asset_location_transferrable', 'You can transfer available quantity only.');
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }
    
}