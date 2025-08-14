<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Asset extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('department/departmentmodel');
        $this->load->model('category/categorymodel');
        $this->load->model('asset/assetmodel');
        $this->load->model('asset/maintenancemodel');
    } 
    
    public function index()
    {
        $this->viewAsset();
    }
    
    public function viewAsset($departments_id = ""){
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "desc";
        $parameters["sort_field"] = "invoice_date";
        
        $fields = array("assets.id"=>1, "barcode"=>1, "assets_name"=>1, "brand"=>1, "supplier_name"=>1, "location"=>1, 
                        "invoice_number"=>1, "category_name"=>1, "status"=>1, "assets_value"=>1);
        
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
        
        $categories = $this->categorymodel->getAllCategories();
        $data["categories"] = $categories;
        
        if(count($departments) > 0){
            
            if(isset($departments[$departments_id])){
                $data["current_department_id"] = $departments_id;
            }else{
                reset($departments);
                $data["current_department_id"] = key($departments);
            }
            
            $data["current_department_name"] = $departments[$data["current_department_id"]];
            
            $parameters["departments"] = array($data["current_department_id"]);
            
            $current_data = $this->assetmodel->getAssetsByDepartments($parameters);
            
            if(isset($current_data[$data["current_department_id"]])){
                    
                $data["current_data"] = $current_data[$data["current_department_id"]];
                
            }
        }
           
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/asset/asset', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    public function viewIndividualAsset($assets_id = 0){ /* Access control at content level. */
        
        $data = array();
        $data["current_data"] = array();
        $data["current_asset_name"] = "No Asset Name";
        $data["current_asset_id"] = FALSE;
        $data["barcode"] = "0";
        
        $parameters_assets = array("assets_id" => array($assets_id));
        $departments_row = $this->assetmodel->getDepartmentsByAssets($parameters_assets);
        $departments_row = isset($departments_row[$assets_id])? $departments_row[$assets_id] : array();
        $departments = array();
        
        foreach($departments_row as $department){
            $departments[] = $department["departments_id"];
        }
        
        $data["departments"] = $departments;
        
        $tabs = array("asset_view"=>"Assets Details", "maintenance"=>"Maintenance",  "loan_view"=>"Loan", 
                      "transfer_view"=>"Transfer", "writeoff_view"=>"Write Off");
        $current_tab = "asset_view";
        
        //"discrepancy_view"=>"Discrepancy", Add back this key
        
        foreach($tabs as $key=>$tab){
            if(!$this->matrix->checkMultiAccess($this->config->item($key, "routes_uri"), $departments) && $key!="maintenance"){
                unset($tabs[$key]);
            }
        }
        
        if(isset($tabs[strtolower($this->input->get("tab", TRUE))])){
            $current_tab = strtolower($this->input->get("tab", TRUE));    
        }
        
        $data["tabs"] = $tabs;
        $data["current_tab"] = $current_tab;
        
        $parameters_assets = array();
        $parameters_assets["assets_id"] = $assets_id;
        $parameters_assets["page_no"] = 1;
        $parameters_assets["count_per_page"] = 100;
        $parameters_assets["sort"] = "desc";
        $parameters_assets["sort_field"] = "id";
        $parameters_assets["current_tab"] = $current_tab;
        
        if(is_natural_number($this->input->get("page_no", TRUE))){
            $parameters_assets["page_no"] = $this->input->get("page_no", TRUE);
        }
        if(is_natural_number($this->input->get("count_per_page", TRUE))){
            $parameters_assets["count_per_page"] = $this->input->get("count_per_page", TRUE);
        }
        if(is_sort_order($this->input->get("sort", TRUE))){
            $parameters_assets["sort"] = strtolower($this->input->get("sort", TRUE));
        }
        
        $current_data = $this->assetmodel->getAsset($parameters_assets);
        
        $this->load->view('admin/common/header', $data);
        
        if(isset($tabs[$current_tab]) && $current_data){
            
            $data["current_asset_name"] = $current_data["assets_name"];
            $data["current_asset_id"] = $current_data["assets_id"];
            $data["barcode"] = $current_data["barcode"];
            
            switch($current_tab){
                
                case "asset_view":
                        $data["next_maintenance"] = $current_data["maintenance_interval"]? $this->maintenancemodel->getNextMaintenanceByOneAsset(array("assets_id" => $assets_id)) : array();
                        $data["current_data"] = $current_data; 
                        $this->load->view('admin/asset/assetview', $data);
                        break;
                case "maintenance":
                        $data["current_data"] = $this->_getMaintenance($parameters_assets);
                        $data["assets_data"] = $current_data;
                        $this->load->view('admin/asset/assetmaintenanceview', $data);
                        break;
                case "discrepancy_view": 
                        $data["current_data"] = $this->_getDiscrepancyTab($parameters_assets);
                        $data["assets_data"] = $current_data; 
                        break;
                case "loan_view": 
                        $data["current_data"] = $this->_getLoanTab($parameters_assets);
                        $data["assets_data"] = $current_data; 
                        break;
                case "transfer_view": 
                        $data["current_data"] = $this->_getTransferTab($parameters_assets);
                        $data["assets_data"] = $current_data;
                        break;
                case "writeoff_view":
                        $data["current_data"] = $this->_getWriteoffTab($parameters_assets);
                        $data["assets_data"] = $current_data;  
                        break;
                default:
                        $this->load->view('admin/asset/assetview', $data);
            }
        }else{
            $data["current_asset_id"] = FALSE;
            $this->load->view('admin/asset/assetview', $data);
        }
        
        $this->load->view('admin/common/footer', $data);
    }
    
    function _getMaintenance($parameters){
        /*
         * $parameters_assets["assets_id"]
         * $parameters_assets["page_no"] 
         * $parameters_assets["count_per_page"]
         * $parameters_assets["sort"] 
         * $parameters_assets["sort_field"]
         * $parameters_assets["current_tab"]
         * */       
         
         return $this->maintenancemodel->getMaintenance($parameters);
    }
    
    function _getDiscrepancyTab($parameters){
        //Add
    }
    
    function _getLoanTab($parameters){
        redirect(site_url($this->config->item("loan_add", "routes_uri") . "/" . $parameters["assets_id"]));
    }
    
    function _getTransferTab($parameters){
        redirect(site_url($this->config->item("transfer_add", "routes_uri") . "/" . $parameters["assets_id"]));
    }
    
    function _getWriteoffTab($parameters){
        redirect(site_url($this->config->item("writeoff_request", "routes_uri") . "/" . $parameters["assets_id"]));
    }
    
    function searchAsset(){
                
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $this->form_validation->set_rules('page_no', 'Page Number', 'trim|xss_clean|required|integer|is_natural_no_zero');
            $this->form_validation->set_rules('count_per_page', 'Count Per Page', 'trim|xss_clean|required|integer|is_natural_no_zero');
            $this->form_validation->set_rules('sort', 'Sorting', 'trim|xss_clean|required|is_sort_order');
            $this->form_validation->set_rules('term', 'Term', 'trim|xss_clean|required'); 
            $this->form_validation->set_rules('sort_field', 'Sort Field', array('trim', 'xss_clean', 'required', array("valid_field", 
                            function($sort_field){
                                $fields = array("assets_name"=>1, "barcode"=>1, "invoice_number"=>1);
                                if(isset($fields[$sort_field])){
                                    return TRUE;
                                }
                                return FALSE;
                            })), array("valid_field"=>"Invalid sort field selected."));
             
            if($this->form_validation->run()){
                
                $parameters = array();
                $parameters["page_no"] = $this->input->post("page_no", TRUE);
                $parameters["count_per_page"] = $this->input->post("count_per_page", TRUE);
                $parameters["sort"] = $this->input->post("sort", TRUE);
                $parameters["term"] = $this->input->post("term", TRUE);
                $parameters["sort_field"] = $this->input->post("sort_field", TRUE);
                $parameters["access_array"] = $this->access_array;
                
                $data = $this->assetmodel->searchAsset($parameters);
                
                $this->session->set_userdata('search_term', $parameters["term"]);
                
                $this->json_output->setData($data);
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();        
    }
    
    public function updateAsset($assets_id = 0){ /* Access control at content level. */
        
        $_POST["assets_id"] = $assets_id;
        
        $this->form_validation->set_rules('assets_id', 'Asset', 'trim|xss_clean|required|is_natural_no_zero|callback__check_is_asset_accessible');
        $this->form_validation->set_rules('attachments_id', 'Photo', array('trim','xss_clean','is_natural'));
        $this->form_validation->set_rules('assets_name', 'Asset Name', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('enable_tracking', 'Enable Tracking', array('trim','xss_clean','in_list[0,1]'));
        $this->form_validation->set_rules('assets_value', 'Asset Value', array('trim','xss_clean','is_money'));
        $this->form_validation->set_rules('assets_lifespan', 'Asset Lifespan', array('trim','xss_clean','is_natural'));
        $this->form_validation->set_rules('maintenance_interval', 'Maintenance Interval', array('trim','xss_clean', 'is_natural'));
        $this->form_validation->set_rules('serial_number', 'Serial Number', array('trim','xss_clean', 'strip_tags'));
        $this->form_validation->set_rules('categories[]', 'Categories', array('trim','xss_clean','is_natural_no_zero'));
        $this->form_validation->set_rules('supplier_name', 'Supplier Name', array('trim','xss_clean','strip_tags'));
        $this->form_validation->set_rules('brand', 'Brand', array('trim','xss_clean','strip_tags'));
        $this->form_validation->set_rules('salvage_value', 'Salvage Value', array('trim','xss_clean','is_money'));
        $this->form_validation->set_rules('warranty_expiry', 'Waranty Expiry', array('trim','xss_clean','is_ymmmmdd_date'));
        $this->form_validation->set_rules('invoice_number', 'Invoice Number', array('trim','xss_clean'));
        $this->form_validation->set_rules('invoice_date', 'Invoice Date', array('trim','xss_clean','is_ymmmmdd_date'));
        $this->form_validation->set_rules('status', 'Status', array('trim','xss_clean','required','in_list[available,write_off,loan_out,out_of_stock,maintenance,unavailable]'));
        $this->form_validation->set_rules('remarks', 'Remarks', array('trim','xss_clean','strip_tags'));
        
        $this->form_validation->set_message('is_money', 'Please enter valid money format.');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
            
            $parameters = array();
            $parameters["assets_id"] = $this->input->post("assets_id", TRUE);
            $parameters["attachments_id"] = $this->input->post("attachments_id", TRUE)? $this->input->post("attachments_id", TRUE) : 0;
            $parameters["assets_name"] = $this->input->post("assets_name", TRUE);
            
            $parameters_assets = array("assets_id" => array($parameters["assets_id"]));
            $departments_row = $this->assetmodel->getDepartmentsByAssets($parameters_assets);
            $departments_row = isset($departments_row[$parameters["assets_id"]])? $departments_row[$parameters["assets_id"]] : array();
            $departments = array();
            foreach($departments_row as $department){
                $departments[] = $department["departments_id"];
            }
            
            if($this->matrix->checkMultiAccess($this->config->item("asset_tracking_option_access", "routes_uri"), $departments)){
                
                $parameters["enable_tracking"] = $this->input->post("enable_tracking", TRUE);
                
                if(isset($parameters["enable_tracking"])){
                    $parameters["enable_tracking"] = $this->input->post("enable_tracking", TRUE);   
                }else{
                    $parameters["enable_tracking"] = 0;
                }
            }
            
            $parameters["assets_value"] = $this->input->post("assets_value", TRUE)? $this->input->post("assets_value", TRUE) : 0;
            $parameters["assets_lifespan"] = $this->input->post("assets_lifespan", TRUE)? $this->input->post("assets_lifespan", TRUE) : 0;
            $parameters["maintenance_interval"] = $this->input->post("maintenance_interval", TRUE)? $this->input->post("maintenance_interval", TRUE) : NULL;
            $parameters["serial_number"] = $this->input->post("serial_number", TRUE)? $this->input->post("serial_number", TRUE) : "";
            $parameters["categories"] = is_array($this->input->post("categories", TRUE))? $this->input->post("categories", TRUE) : array();
            $parameters["supplier_name"] = $this->input->post("supplier_name", TRUE)? $this->input->post("supplier_name", TRUE) : "";
            $parameters["brand"] = $this->input->post("brand", TRUE)? $this->input->post("brand", TRUE) : "";
            $parameters["salvage_value"] = $this->input->post("salvage_value", TRUE)? $this->input->post("salvage_value", TRUE) : 0;
            $parameters["warranty_expiry"] = $this->input->post("warranty_expiry", TRUE)? mysql_date($this->input->post("warranty_expiry", TRUE)) : NULL;
            $parameters["invoice_number"] = $this->input->post("invoice_number", TRUE)? $this->input->post("invoice_number", TRUE) : "";
            $parameters["invoice_date"] = $this->input->post("invoice_date", TRUE)? mysql_date($this->input->post("invoice_date", TRUE)) : NULL;
            $parameters["status"] = $this->input->post("status", TRUE);
            $parameters["remarks"] = $this->input->post("remarks", TRUE)? $this->input->post("remarks", TRUE) : "";
            
            $this->assetmodel->updateAsset($parameters);
            
            $this->session->set_flashdata('success', "Asset updated.");
            redirect(site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $assets_id));
            return TRUE;
        }
        
        
        $data = array();
        $data["current_data"] = array();
        $data["current_asset_name"] = "No Asset Name";
        $data["current_asset_id"] = FALSE;
        $data["barcode"] = "0";
        
        $parameters_assets = array("assets_id" => array($assets_id));
        $departments_row = $this->assetmodel->getDepartmentsByAssets($parameters_assets);
        $departments_row = isset($departments_row[$assets_id])? $departments_row[$assets_id] : array();
        $departments = array();
        
        foreach($departments_row as $department){
            $departments[] = $department["departments_id"];
        }
        
        $data["departments"] = $departments;
        $categories = $this->categorymodel->getAllCategories();
        
        $data["categories"] = array();
        
        foreach($categories as $cat){
            $data["categories"][$cat["id"]] = $cat["categories_name"];
        }
        
        $tabs = array("asset_view"=>"Assets Details", "maintenance"=>"Maintenance", "loan_view"=>"Loan", 
                      "transfer_view"=>"Transfer", "writeoff_view"=>"Write Off");
        $current_tab = "asset_view";
        
        //"discrepancy_view"=>"Discrepancy", add back this key 
        
        foreach($tabs as $key=>$tab){
            if(!$this->matrix->checkMultiAccess($this->config->item($key, "routes_uri"), $departments) && $key!="maintenance"){
                unset($tabs[$key]);
            }
        }
        
        if(isset($tabs[strtolower($this->input->get("tab", TRUE))])){
            $current_tab = strtolower($this->input->get("tab", TRUE));    
        }
        
        $data["tabs"] = $tabs;
        $data["current_tab"] = $current_tab;
        
        $parameters_assets = array();
        $parameters_assets["assets_id"] = $assets_id;
        $parameters_assets["page_no"] = 1;
        $parameters_assets["count_per_page"] = 100;
        $parameters_assets["sort"] = "desc";
        $parameters_assets["sort_field"] = "id";
        
        if(is_natural_number($this->input->get("page_no", TRUE))){
            $parameters_assets["page_no"] = $this->input->get("page_no", TRUE);
        }
        if(is_natural_number($this->input->get("count_per_page", TRUE))){
            $parameters_assets["count_per_page"] = $this->input->get("count_per_page", TRUE);
        }
        if(is_sort_order($this->input->get("sort", TRUE))){
            $parameters_assets["sort"] = strtolower($this->input->get("sort", TRUE));
        }
        
        $current_data = $this->assetmodel->getAsset($parameters_assets);
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            $current_data["categories"] = array();
            if(is_array($this->input->post("categories", TRUE))){
                foreach($this->input->post("categories", TRUE) as $cat){
                    $current_data["categories"][] = array("categories_id"=>$cat);    
                }
            }
        }
        
        $this->load->view('admin/common/header', $data);
        
        if(isset($tabs[$current_tab]) && $current_data){
            
            $data["current_asset_name"] = $current_data["assets_name"];
            $data["current_asset_id"] = $current_data["assets_id"];
            $data["barcode"] = $current_data["barcode"];
            
            switch($current_tab){
                
                case "asset_view":
                        $data["next_maintenance"] = $current_data["maintenance_interval"]? $this->maintenancemodel->getNextMaintenanceByOneAsset(array("assets_id" => $assets_id)) : array();
                        $data["current_data"] = $current_data; 
                        $this->load->view('admin/asset/assetupdate', $data);
                        $this->load->view('admin/common/uploader', $data);
                        break;
                default:
                        $this->load->view('admin/asset/assetupdate', $data);
            }
        }else{
            $data["current_asset_id"] = FALSE;
            $this->load->view('admin/asset/assetview', $data);
        }
        
        $this->load->view('admin/common/footer', $data);
    }
    
    public function updateAssetTrackingOption($assets_id){ /* Access control at content level. */
        
    }
    
    public function addAsset(){ /* Access control at content level. */
        
        $this->form_validation->set_rules('attachments_id', 'Photo', array('trim','xss_clean','is_natural'));
        $this->form_validation->set_rules('assets_name', 'Asset Name', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('departments[]', 'Department', array('trim','xss_clean','is_natural_no_zero','required',array("access_permission", array($this->matrix, "inAccessArray"))), array("access_permission" => "You do not have access to selected department"));
        $this->form_validation->set_rules('locations[]', 'Location', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('quantity[]', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
        $this->form_validation->set_rules('enable_tracking', 'Enable Tracking', array('trim','xss_clean','required','in_list[0,1]'));
        $this->form_validation->set_rules('assets_value', 'Asset Value', array('trim','xss_clean','is_money'));
        $this->form_validation->set_rules('assets_lifespan', 'Asset Lifespan', array('trim','xss_clean','is_natural'));
        $this->form_validation->set_rules('maintenance_interval', 'Maintenance Interval', array('trim','xss_clean', 'is_natural'));
        $this->form_validation->set_rules('serial_number', 'Serial Number', array('trim','xss_clean', 'strip_tags'));
        $this->form_validation->set_rules('categories[]', 'Categories', array('trim','xss_clean','is_natural_no_zero'));
        $this->form_validation->set_rules('supplier_name', 'Supplier Name', array('trim','xss_clean','strip_tags'));
        $this->form_validation->set_rules('brand', 'Brand', array('trim','xss_clean','strip_tags'));
        $this->form_validation->set_rules('salvage_value', 'Salvage Value', array('trim','xss_clean','is_money'));
        $this->form_validation->set_rules('warranty_expiry', 'Waranty Expiry', array('trim','xss_clean','is_ymmmmdd_date'));
        $this->form_validation->set_rules('invoice_number', 'Invoice Number', array('trim','xss_clean'));
        $this->form_validation->set_rules('invoice_date', 'Invoice Date', array('trim','xss_clean','is_ymmmmdd_date'));
        $this->form_validation->set_rules('status', 'Status', array('trim','xss_clean','required','in_list[available,write_off,loan_out,out_of_stock,maintenance,unavailable]'));
        $this->form_validation->set_rules('remarks', 'Remarks', array('trim','xss_clean','strip_tags'));
        
        $this->form_validation->set_message('is_money', 'Please enter valid money format.');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
            
            $parameters = array();
            $parameters["attachments_id"] = $this->input->post("attachments_id", TRUE)? $this->input->post("attachments_id", TRUE) : 0;
            $parameters["assets_name"] = $this->input->post("assets_name", TRUE);
            
            $assets_departments = array();
            
            $departments_post = $this->input->post("departments", TRUE);
            $locations_post = $this->input->post("locations", TRUE);
            $quantity_post = $this->input->post("quantity", TRUE);
            
            if(is_array($departments_post) && is_array($locations_post) && is_array($quantity_post)){
                $departments_post = array_values($departments_post);
                $locations_post = array_values($locations_post);
                $quantity_post = array_values($quantity_post);
                
                for($i = 0; $i < min(count($departments_post), count($locations_post), count($quantity_post)); $i++){
                    $assets_departments[] = array("departments_id"=>$departments_post[$i], "location"=>$locations_post[$i], "quantity"=>$quantity_post[$i]);
                }
            }
            
            $parameters["departments"] = $assets_departments;
            $parameters["enable_tracking"] = $this->input->post("enable_tracking", TRUE);   
            $parameters["assets_value"] = $this->input->post("assets_value", TRUE)? $this->input->post("assets_value", TRUE) : 0;
            $parameters["assets_lifespan"] = $this->input->post("assets_lifespan", TRUE)? $this->input->post("assets_lifespan", TRUE) : 0;
            $parameters["maintenance_interval"] = $this->input->post("maintenance_interval", TRUE)? $this->input->post("maintenance_interval", TRUE) : NULL;
            $parameters["serial_number"] = $this->input->post("serial_number", TRUE)? $this->input->post("serial_number", TRUE) : "";
            $parameters["categories"] = is_array($this->input->post("categories", TRUE))? $this->input->post("categories", TRUE) : array();
            $parameters["supplier_name"] = $this->input->post("supplier_name", TRUE)? $this->input->post("supplier_name", TRUE) : "";
            $parameters["brand"] = $this->input->post("brand", TRUE)? $this->input->post("brand", TRUE) : "";
            $parameters["salvage_value"] = $this->input->post("salvage_value", TRUE)? $this->input->post("salvage_value", TRUE) : 0;
            $parameters["warranty_expiry"] = $this->input->post("warranty_expiry", TRUE)? mysql_date($this->input->post("warranty_expiry", TRUE)) : NULL;
            $parameters["invoice_number"] = $this->input->post("invoice_number", TRUE)? $this->input->post("invoice_number", TRUE) : "";
            $parameters["invoice_date"] = $this->input->post("invoice_date", TRUE)? mysql_date($this->input->post("invoice_date", TRUE)) : NULL;
            $parameters["status"] = $this->input->post("status", TRUE);
            $parameters["remarks"] = $this->input->post("remarks", TRUE)? $this->input->post("remarks", TRUE) : "";
            
            $assets_id = $this->assetmodel->addAsset($parameters);
            
            $this->session->set_flashdata('success', "Asset Added.");
            redirect(site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $assets_id));
            return TRUE;
        }
        
        
        $data = array();
        
        $parameters_access = array();
        $parameters_access["access_array"] = $this->access_array; 

        $departments = $this->departmentmodel->getDepartmentsByAccessArray($parameters_access);
        $data["departments"] = $departments;
        
        $categories = $this->categorymodel->getAllCategories();
        $data["categories"] = array();
        $data["categories_info"] = array();
        
        foreach($categories as $cat){
            $data["categories"][$cat["id"]] = $cat["categories_name"] . ' - ' . ($cat["tracking_default"]? "[Tracking Enabled]":"[No Tracking]") . ' - [' . ($cat["lifespan_default"]? ($cat["lifespan_default"] . " months lifespan") : "No Lifespan") . ']';
            $data["categories_info"][$cat["id"]] = array("tracking_default"=>$cat["tracking_default"], "lifespan_default"=>$cat["lifespan_default"]);
        }
        
        $data["current_data"]["attachments_id"] = 0;
        $data["current_data"]["assets_name"] = "";
        $data["current_data"]["barcode"] = "New Asset";
        $data["current_data"]["enable_tracking"] = "0";
        $data["current_data"]["assets_value"] = "";
        $data["current_data"]["assets_lifespan"] = "";
        $data["current_data"]["maintenance_interval"] = "0";
        $data["current_data"]["serial_number"] = "";
        $data["current_data"]["categories"] = array();
        $data["current_data"]["departments"] = array();
        $data["current_data"]["locations"] = array();
        $data["current_data"]["quantity"] = array();
        $data["current_data"]["supplier_name"] = "";
        $data["current_data"]["brand"] = "";
        $data["current_data"]["salvage_value"] = "0";
        $data["current_data"]["warranty_expiry"] = "";
        $data["current_data"]["invoice_number"] = "";
        $data["current_data"]["invoice_date"] = "";
        $data["current_data"]["status"] = "available";
        $data["current_data"]["remarks"] = "";
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/asset/assetadd', $data);
        $this->load->view('admin/common/uploader', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    function deleteAsset($assets_id){ /* Access control at content level. */
        
    }
    
    function downloadReport(){ /* Download a list of asset. Access control at function level. Use query string to paginate content, filter category and department. */
        
        $this->load->model('report/reportmodel');
        $this->load->model('report/assetdetailreportmodel');
        $this->load->model('report/assetlistreportmodel');
        
        $fields = array("asset_list"=>1, "asset_detail"=>1, "department_value"=>1);
        
        $parameters = array();
        $parameters["report_type"] = $this->input->get("report_type", TRUE);
        $parameters["start_date"] = $this->input->get("start_date", TRUE);
        $parameters["end_date"] = $this->input->get("end_date", TRUE);
        $parameters["departments"] = array_filter($this->input->get("departments", TRUE), "is_scalar");
        $parameters["assets"] = $this->input->get("assets", TRUE);
        $parameters["include_department"] = $this->input->get("include_department", TRUE);
        $parameters["categories"] = array_filter($this->input->get("categories", TRUE), "is_scalar");
        
        if(!isset($fields[strtolower($parameters["report_type"])])){
            echo "You have selected invalid report type";
            return TRUE;           
        } 
        
        if(!is_ymmmmdd_date($parameters["start_date"])){
            $parameters["start_date"] = "";
        }
        
        if(!is_ymmmmdd_date($parameters["end_date"])){
            $parameters["end_date"] = "";
        }
        
        switch($parameters["report_type"]){
            case "asset_list": 
                    
                    if(!$parameters["departments"] || !is_array($parameters["departments"])){
                        $parameters["departments"] = FALSE;
                        echo "Please select at least one department";
                        return TRUE;
                    }else if(!$this->matrix->inAccessArray($parameters["departments"])){
                        echo "You have no access to one of the selected department.";
                        return TRUE;
                    }else if(!is_array($parameters["categories"]) && !$parameters["categories"]){
                        echo "Please select at least one category.";
                        return TRUE;
                    }
                    
                    $assets_list = array();
                    
                    $assets_data = $this->assetmodel->getAllAssetsByDepartments(array("departments"=>$parameters["departments"]));
                    foreach($assets_data as $asset){
                        $assets_list[] = $asset["assets_id"];
                    }
                    
                    if(count($assets_list) == 0){
                        echo "Please select at least one asset.";
                        return TRUE;
                    }
                    
                    $parameters["assets"] = $assets_list;
                    
                    $this->assetlistreportmodel->downloadAssetListReport($parameters);
                    break;
                    
            case "asset_detail":
                    $assets_list = array();
                    $valid_departments = array();
                
                    if($parameters["include_department"]){
                        if($parameters["departments"] && is_array($parameters["departments"])){
                            foreach($parameters["departments"] as $department){
                                if($this->matrix->inAccessArray($department)){
                                    $valid_departments[] = $department;
                                }
                            }
                        }
                        /* Add to assets list */
                        if(count($valid_departments) > 0){
                            $assets_data = $this->assetmodel->getAllAssetsByDepartments(array("departments"=>$valid_departments));
                            foreach($assets_data as $asset){
                                $assets_list[] = $asset["assets_id"];
                            }   
                        }
                    }
                    
                    if(!is_array($parameters["assets"])){
                        $parameters["assets"] = array();
                    }
                    
                    if((!$parameters["assets"]) && count($assets_list) == 0){
                        echo "Please select at least one asset.";
                        return TRUE;
                    }else{
                        foreach($parameters["assets"] as $asset){
                            if(!$this->assetmodel->isAssetAccessibleByAccessArray(array("assets_id"=>$asset, "access_array"=>$this->access_array))){
                                echo "You have no access to one of the selected assets.";
                                return TRUE;                                
                            }
                        }
                    }
                    
                    $parameters["assets"] = array_merge($parameters["assets"], $assets_list);
                    
                    if(!is_array($parameters["categories"])){
                        $parameters["categories"] = array();
                    }
                    
                    $this->assetdetailreportmodel->downloadAssetDetailReport($parameters);
                    break;
                
            case "department_value": 
                    
                    if(!$parameters["departments"] || !is_array($parameters["departments"])){
                        $parameters["departments"] = FALSE;
                    }else if(!$this->matrix->inAccessArray($parameters["departments"])){
                        echo "You have no access to one of the selected department.";
                        return TRUE;
                    }
                    $this->reportmodel->downloadDepartmentReport($parameters);
                    break;
        }
    }
    
    function downloadDetailReport(){ /* Access control at function level. Use query string to select a list of asset ID. One sheet for one asset. */
        
    }
    
    function importAssetPreview($attachments_id = 0){ /* Preview a list only. No insert. */
        
        $data = array();
        
        $excel_row_data = FALSE;
        
        $parameters = array();
        $parameters["row_no"] = 3;
        $parameters["attachments_id"] = $attachments_id;
        
        $data["prev_id"] = $this->input->get("prev_id", TRUE);
        $data["file_id"] = $attachments_id;
        
        if(is_natural_number($this->input->get("row_no", TRUE))){
            $parameters["row_no"] = $this->input->get("row_no", TRUE);
        }
        
        if($attachments_id){
            $this->load->model('asset/importmodel');
            $excel_row_data = $this->importmodel->getExcelRow($parameters);
        }
        
        $data["excel_row_data"] = $excel_row_data;
        $data["row_no"] = $parameters["row_no"];
               
        $parameters_access = array();
        $parameters_access["access_array"] = $this->access_array; 

        $departments = $this->departmentmodel->getDepartmentsByAccessArray($parameters_access);
        $data["departments"] = $departments;
        
        $categories = $this->categorymodel->getAllCategories();
        $data["categories"] = array();
        $data["categories_info"] = array();
        
        foreach($categories as $cat){
            $data["categories"][$cat["id"]] = $cat["categories_name"] . ' - ' . ($cat["tracking_default"]? "[Tracking Enabled]":"[No Tracking]") . ' - [' . ($cat["lifespan_default"]? ($cat["lifespan_default"] . " months lifespan") : "No Lifespan") . ']';
            $data["categories_info"][$cat["id"]] = array("tracking_default"=>$cat["tracking_default"], "lifespan_default"=>$cat["lifespan_default"]);
        }
        
        $data["current_data"]["attachments_id"] = 0;
        $data["current_data"]["assets_name"] = "";
        $data["current_data"]["barcode"] = "New Asset";
        $data["current_data"]["enable_tracking"] = "0";
        $data["current_data"]["assets_value"] = "";
        $data["current_data"]["assets_lifespan"] = "";
        $data["current_data"]["maintenance_interval"] = "0";
        $data["current_data"]["serial_number"] = "";
        $data["current_data"]["categories"] = array();
        $data["current_data"]["departments"] = array();
        $data["current_data"]["locations"] = array();
        $data["current_data"]["quantity"] = array();
        $data["current_data"]["supplier_name"] = "";
        $data["current_data"]["brand"] = "";
        $data["current_data"]["salvage_value"] = "0";
        $data["current_data"]["warranty_expiry"] = "";
        $data["current_data"]["invoice_number"] = "";
        $data["current_data"]["invoice_date"] = "";
        $data["current_data"]["status"] = "available";
        $data["current_data"]["remarks"] = "";
        $data["current_data"]["next_maintenance"] = "";
        
        if($excel_row_data){
            $data["current_data"] = $excel_row_data;
        }
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/asset/assetimport', $data);
        $this->load->view('admin/common/uploader', $data);
        $this->load->view('admin/common/footer', $data);
    }
    
    function importAsset($file_id = 0){ /* Access control at function level. Show a list of imported asset before insert. */
        
        $this->form_validation->set_rules('attachments_id', 'Photo', array('trim','xss_clean','is_natural'));
        $this->form_validation->set_rules('assets_name', 'Asset Name', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('departments[]', 'Department', array('trim','xss_clean','is_natural_no_zero','required',array("access_permission", array($this->matrix, "inAccessArray"))), array("access_permission" => "You do not have access to selected department"));
        $this->form_validation->set_rules('locations[]', 'Location', array('trim','xss_clean','required'));
        $this->form_validation->set_rules('quantity[]', 'Quantity', array('trim','xss_clean','is_natural_no_zero', 'required'));
        $this->form_validation->set_rules('enable_tracking', 'Enable Tracking', array('trim','xss_clean','required','in_list[0,1]'));
        $this->form_validation->set_rules('assets_value', 'Asset Value', array('trim','xss_clean','is_money'));
        $this->form_validation->set_rules('assets_lifespan', 'Asset Lifespan', array('trim','xss_clean','is_natural'));
        $this->form_validation->set_rules('maintenance_interval', 'Maintenance Interval', array('trim','xss_clean', 'is_natural'));
        $this->form_validation->set_rules('serial_number', 'Serial Number', array('trim','xss_clean', 'strip_tags'));
        $this->form_validation->set_rules('categories[]', 'Categories', array('trim','xss_clean','is_natural_no_zero'));
        $this->form_validation->set_rules('supplier_name', 'Supplier Name', array('trim','xss_clean','strip_tags'));
        $this->form_validation->set_rules('brand', 'Brand', array('trim','xss_clean','strip_tags'));
        $this->form_validation->set_rules('salvage_value', 'Salvage Value', array('trim','xss_clean','is_money'));
        $this->form_validation->set_rules('warranty_expiry', 'Waranty Expiry', array('trim','xss_clean','is_ymmmmdd_date'));
        $this->form_validation->set_rules('invoice_number', 'Invoice Number', array('trim','xss_clean'));
        $this->form_validation->set_rules('invoice_date', 'Invoice Date', array('trim','xss_clean','is_ymmmmdd_date'));
        $this->form_validation->set_rules('status', 'Status', array('trim','xss_clean','required','in_list[available,write_off,loan_out,out_of_stock,maintenance,unavailable]'));
        $this->form_validation->set_rules('remarks', 'Remarks', array('trim','xss_clean','strip_tags'));
        
        $this->form_validation->set_message('is_money', 'Please enter valid money format.');
        
        if($this->input->server('REQUEST_METHOD') == 'POST' && $this->form_validation->run()){
            
            $parameters = array();
            $parameters["attachments_id"] = $this->input->post("attachments_id", TRUE)? $this->input->post("attachments_id", TRUE) : 0;
            $parameters["assets_name"] = $this->input->post("assets_name", TRUE);
            
            $assets_departments = array();
            
            $departments_post = $this->input->post("departments", TRUE);
            $locations_post = $this->input->post("locations", TRUE);
            $quantity_post = $this->input->post("quantity", TRUE);
            
            if(is_array($departments_post) && is_array($locations_post) && is_array($quantity_post)){
                $departments_post = array_values($departments_post);
                $locations_post = array_values($locations_post);
                $quantity_post = array_values($quantity_post);
                
                for($i = 0; $i < min(count($departments_post), count($locations_post), count($quantity_post)); $i++){
                    $assets_departments[] = array("departments_id"=>$departments_post[$i], "location"=>$locations_post[$i], "quantity"=>$quantity_post[$i]);
                }
            }
            
            $parameters["departments"] = $assets_departments;
            $parameters["enable_tracking"] = $this->input->post("enable_tracking", TRUE);   
            $parameters["assets_value"] = $this->input->post("assets_value", TRUE)? $this->input->post("assets_value", TRUE) : 0;
            $parameters["assets_lifespan"] = $this->input->post("assets_lifespan", TRUE)? $this->input->post("assets_lifespan", TRUE) : 0;
            $parameters["maintenance_interval"] = $this->input->post("maintenance_interval", TRUE)? $this->input->post("maintenance_interval", TRUE) : NULL;
            $parameters["serial_number"] = $this->input->post("serial_number", TRUE)? $this->input->post("serial_number", TRUE) : "";
            $parameters["categories"] = is_array($this->input->post("categories", TRUE))? $this->input->post("categories", TRUE) : array();
            $parameters["supplier_name"] = $this->input->post("supplier_name", TRUE)? $this->input->post("supplier_name", TRUE) : "";
            $parameters["brand"] = $this->input->post("brand", TRUE)? $this->input->post("brand", TRUE) : "";
            $parameters["salvage_value"] = $this->input->post("salvage_value", TRUE)? $this->input->post("salvage_value", TRUE) : 0;
            $parameters["warranty_expiry"] = $this->input->post("warranty_expiry", TRUE)? mysql_date($this->input->post("warranty_expiry", TRUE)) : NULL;
            $parameters["invoice_number"] = $this->input->post("invoice_number", TRUE)? $this->input->post("invoice_number", TRUE) : "";
            $parameters["invoice_date"] = $this->input->post("invoice_date", TRUE)? mysql_date($this->input->post("invoice_date", TRUE)) : NULL;
            $parameters["status"] = $this->input->post("status", TRUE);
            $parameters["remarks"] = $this->input->post("remarks", TRUE)? $this->input->post("remarks", TRUE) : "";
            
            $assets_id = $this->assetmodel->addAsset($parameters);
            
            $this->session->set_flashdata('success', "Asset Added.");
            
            $row_no = 3;
            
            if(is_natural_number($this->input->get("row_no", TRUE))){
                $row_no = $this->input->get("row_no", TRUE);
            }
            
            redirect(site_url($this->config->item("asset_import_preview", "routes_uri") . "/" . $file_id . "?row_no=" . (intval($row_no) + 1) . "&prev_id=" . $assets_id));
            return TRUE;
        }

        $this->importAssetPreview($file_id);        
    }
    
    function printLabel(){ /* Single Label Print */
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $this->form_validation->set_rules('assets_id', 'Asset ID', array('trim','xss_clean','required','is_natural_no_zero'));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["assets_id"] = $this->input->post("assets_id", TRUE);
                
                $data = $this->assetmodel->printLabel($parameters);
                
                if($data["success"]){
                    $this->json_output->setFlagSuccess();   
                }else{
                    $this->json_output->setFlagFail();
                    $this->json_output->setCode($data["code"]);
                    foreach($data["message"] as $message){
                        $this->json_output->addMessage($message);
                    }
                }
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    function _check_is_asset_accessible($assets_id){
        
        $this->form_validation->set_message('_check_is_asset_accessible', 'You have no permission to access this asset.');
        
        return $this->assetmodel->isAssetAccessibleByAccessArray(array("assets_id"=>$assets_id, "access_array"=>$this->access_array));
    }
}