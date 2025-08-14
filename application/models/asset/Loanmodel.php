<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Loanmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Return a record from assets_departments_loan table by assets_departments_id 
     *
     * $parameters["assets_departments_id"] 
     * 
     * */
    function getLoansByAssetsDepartmentsID($parameters){
        return $this->db->get_where("assets_departments_loan", array("assets_departments_id" => $parameters["assets_departments_id"]))->result_array();
    }
    
    /* 
     * Return a record from assets_departments_loan table by id 
     *
     * $parameters["assets_departments_loan_id"] 
     * 
     * */
    function getLoanByID($parameters){
        $query = $this->db->get_where("assets_departments_loan", array("id" => $parameters["assets_departments_loan_id"]));
        
        if($query->num_rows() > 0){
            return $query->row_array();
        }
        
        return FALSE;
    }
    
    /* 
     * Return currently loaned out record from assets_departments_loan table by department array 
     *
     * $parameters["departments"] = array(); //- refers to list department ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * 
     * */
    function getLoanByDepartments($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *, assets_departments_loan.quantity AS loaned_quantity, assets_departments_loan.id AS assets_departments_loan_id, assets_departments_loan.datetime_created AS datetime_created", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->join("assets_departments", "assets_departments.id = assets_departments_loan.assets_departments_id", "inner");
        $this->db->where_in("assets_departments.departments_id", $parameters["departments"]);
        $query = $this->db->get("assets_departments_loan");
           
        $assets_id = array();
        $assets = array();
        
        $users_id = array();
        $users = array();
        
        foreach($query->result() as $row){
            $users_id[] = $row->users_id;
            $assets_id[] = $row->assets_id;
        }
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->setQueryString("tab=" . $parameters["current_tab"]);
        $this->pagination_output->build();
        
        if(count($assets_id) > 0){
            $this->db->where_in("id", $assets_id);
            $assets_query = $this->db->get("assets");
            
            foreach($assets_query->result_array() as $assets_row){
                $assets[$assets_row["id"]] = $assets_row;
            }    
        }
        
        if(count($users_id) > 0){
            $this->db->where_in("id", $users_id);
            $this->db->select("id, person_name, email");
            $users_query = $this->db->get("users");
            
            foreach($users_query->result_array() as $users_row){
                $users[$users_row["id"]] = $users_row;
            }
        }
        
        foreach($query->result_array() as $row){
            
            $assets_info = array();
            $assets_info["attachments_id"] = 0;
            $assets_info["assets_name"] = "Asset not found";
            $assets_info["barcode"] = "Asset not found";
            $assets_info["status"] = "unavailable";
            
            if(isset($assets[$row["assets_id"]])){
                $asset_selected = $assets[$row["assets_id"]];
                $assets_info["attachments_id"] = $asset_selected["attachments_id"];
                $assets_info["assets_name"] = $asset_selected["assets_name"];
                $assets_info["barcode"] = $asset_selected["barcode"];
                $assets_info["status"] = $asset_selected["status"];
            }
            $row["assets"] = $assets_info;
            
            /* Requester */
            $users_info = array();
            $users_info["person_name"] = "Requester user not found";
            $users_info["email"] = "User record not found";
            
            if(isset($users[$row["users_id"]])){
                $user_selected = $users[$row["users_id"]];
                $users_info["person_name"] = $user_selected["person_name"];
                $users_info["email"] = $user_selected["email"];
            }
            $row["users"] = $users_info;
            
            $data[$row["departments_id"]][] = $row;
        }
     
        return $data;   
    }

    /* 
     * Return loan history from transaction table by department
     *
     * $parameters["departments"] = array(); //- refers to list department ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * $parameters["start_date"] = optional
     * $parameters["end_date"] = optional 
     * 
     * */
    function getLoanHistoryByDepartments($parameters){
        
        $data = array();
        
        if(isset($parameters["start_date"])){
            $this->db->where("datetime_created >=", $parameters["start_date"]);
        }
        
        if(isset($parameters["end_date"])){
            $this->db->where("datetime_created <=", $parameters["end_date"]);
        }
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where_in("origin_departments_id", $parameters["departments"]);
        $this->db->where_in("transaction_type", array("loan", "return"));
        $query = $this->db->get("transaction");
           
        $assets_id = array();
        $users_id = array();
        $assets = array();
        $users = array();
        $assets_departments_loan_id = array();
        $assets_departments_loan = array();
        
        foreach($query->result() as $row){
            $assets_id[] = $row->assets_id;
            $users_id[] = $row->users_id;
            $assets_departments_loan_id[] = $row->assets_departments_loan_id;
        }
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->setQueryString("tab=" . $parameters["current_tab"]);
        $this->pagination_output->build();
        
        if(count($assets_id) > 0){
            $this->db->where_in("id", $assets_id);
            $assets_query = $this->db->get("assets");
            
            foreach($assets_query->result_array() as $assets_row){
                $assets[$assets_row["id"]] = $assets_row;
            }    
        }
        
        if(count($users_id) > 0){
            $this->db->where_in("id", $users_id);
            $this->db->select("id, person_name, email");
            $users_query = $this->db->get("users");
            
            foreach($users_query->result_array() as $users_row){
                $users[$users_row["id"]] = $users_row;
            }
        }
        
        if(count($assets_departments_loan_id) > 0){
            $this->db->where_in("assets_departments_loan_id", $assets_departments_loan_id);
            $loan_query = $this->db->get("transaction");
            
            foreach($loan_query->result_array() as $loan_row){
                $assets_departments_loan[$loan_row["assets_departments_loan_id"]]["value"][] = $loan_row;
                $assets_departments_loan[$loan_row["assets_departments_loan_id"]]["total"] = 0;
            }
        }
        
        foreach($assets_departments_loan as $key=>$loan_sum){
            $values = $loan_sum["value"];
            foreach($values as $value){
                if($value["transaction_type"] == "loan"){
                    $assets_departments_loan[$key]["total"] += intval($value["quantity"]);
                }else if($value["transaction_type"] == "return"){
                    $assets_departments_loan[$key]["total"] -= intval($value["quantity"]);
                }
            }
        }
        
        foreach($query->result_array() as $row){
            
            $assets_info = array();
            $assets_info["attachments_id"] = 0;
            $assets_info["assets_name"] = "Asset not found";
            $assets_info["barcode"] = "Asset not found";
            $assets_info["status"] = "unavailable";
            
            if(isset($assets[$row["assets_id"]])){
                $asset_selected = $assets[$row["assets_id"]];
                $assets_info["attachments_id"] = $asset_selected["attachments_id"];
                $assets_info["assets_name"] = $asset_selected["assets_name"];
                $assets_info["barcode"] = $asset_selected["barcode"];
                $assets_info["status"] = $asset_selected["status"];
            }
            $row["assets"] = $assets_info;
            
            $users_info = array();
            $users_info["person_name"] = $row["approver_name"];
            $users_info["email"] = "User record not found";
            
            if(isset($users[$row["users_id"]])){
                $user_selected = $users[$row["users_id"]];
                $users_info["person_name"] = $user_selected["person_name"];
                $users_info["email"] = $user_selected["email"];
            }
            $row["users"] = $users_info;
            
            $row["remaining"] = isset($assets_departments_loan[$row["assets_departments_loan_id"]])? $assets_departments_loan[$row["assets_departments_loan_id"]]["total"] : 0;
            
            $data[$row["origin_departments_id"]][] = $row;
        }
     
        return $data;   
    }
    
    /* 
     * Get loan histories by asset ID
     *
     * $parameters["assets_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * 
     * */
    function getLoanBySingleAsset($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where_in("transaction_type", array("loan", "return"));
        $this->db->where("assets_id",  $parameters["assets_id"]);
        $query = $this->db->get("transaction");
           
        $users_id = array();
        $users = array();
        
        $departments_id = array();
        $departments = array();
        
        $assets_departments_loan_id = array();
        $assets_departments_loan = array();
        
        foreach($query->result() as $row){
            $users_id[] = $row->users_id;
            $assets_departments_loan_id[] = $row->assets_departments_loan_id;
        }
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->setQueryString("tab=" . $parameters["current_tab"]);
        $this->pagination_output->build();
        
        if(count($users_id) > 0){
            $this->db->where_in("id", $users_id);
            $this->db->select("id, person_name, email");
            $users_query = $this->db->get("users");
            
            foreach($users_query->result_array() as $users_row){
                $users[$users_row["id"]] = $users_row;
            }
        }
        
        if(count($assets_departments_loan_id) > 0){
            $this->db->where_in("assets_departments_loan_id", $assets_departments_loan_id);
            $loan_query = $this->db->get("transaction");
            
            foreach($loan_query->result_array() as $loan_row){
                $assets_departments_loan[$loan_row["assets_departments_loan_id"]]["value"][] = $loan_row;
                $assets_departments_loan[$loan_row["assets_departments_loan_id"]]["total"] = 0;
            }
        }
        
        foreach($assets_departments_loan as $key=>$loan_sum){
            $values = $loan_sum["value"];
            foreach($values as $value){
                if($value["transaction_type"] == "loan"){
                    $assets_departments_loan[$key]["total"] += intval($value["quantity"]);
                }else if($value["transaction_type"] == "return"){
                    $assets_departments_loan[$key]["total"] -= intval($value["quantity"]);
                }
            }
        }
        
        foreach($query->result_array() as $row){
            
            $users_info = array();
            $users_info["person_name"] = $row["approver_name"];
            $users_info["email"] = "User record not found";
            
            if(isset($users[$row["users_id"]])){
                $user_selected = $users[$row["users_id"]];
                $users_info["person_name"] = $user_selected["person_name"];
                $users_info["email"] = $user_selected["email"];
            }
            $row["users"] = $users_info;
            
            $row["remaining"] = isset($assets_departments_loan[$row["assets_departments_loan_id"]])? $assets_departments_loan[$row["assets_departments_loan_id"]]["total"] : 0;
            
            $data[] = $row;
        }
        return $data;   
    }
    
    
    /* 
     * Return loan 
     *
     * $parameters["assets_departments_loan_id"] 
     * $parameters["quantity"] 
     * $parameters["remark"]
     * 
     * */
    function returnLoan($parameters){
        
        $loan = $this->getLoanByID(array("assets_departments_loan_id" => $parameters["assets_departments_loan_id"]));
        if($loan){
            
            $assets_departments_id = $loan["assets_departments_id"];
            
            $assets_id = 0;
            $departments_id = 0;
            $location = "";
            $departments_name = "No department name";
            $loaned_quantity = $loan["quantity"];
            
            $location_query = $this->db->get_where("assets_departments", array("id" => $assets_departments_id));
                
            if($location_query->num_rows() > 0){
                $location_row = $location_query->row();
                $departments_id = $location_row->departments_id;
                $assets_id = $location_row->assets_id;
                $location = $location_row->location;
                
                $department_query = $this->db->get_where("departments", array("id" => $departments_id));
                
                if($department_query->num_rows() > 0){
                    $department_row = $department_query->row();
                    $departments_name = $department_row->departments_name;
                }
                
                $transaction_insert_parameter = array();
                $transaction_insert_parameter["assets_id"] = $assets_id;
                $transaction_insert_parameter["transaction_type"] = "return";
                $transaction_insert_parameter["quantity"] = $parameters["quantity"];
                $transaction_insert_parameter["origin_departments_id"] = $departments_id;
                $transaction_insert_parameter["origin_departments_name"] = $departments_name;
                $transaction_insert_parameter["origin_location"] = $location;
                $transaction_insert_parameter["loan_period"] = $loan["loan_period"];
                $transaction_insert_parameter["borrower_name"] = $loan["borrower_name"];
                $transaction_insert_parameter["borrower_entity"] = $loan["borrower_entity"];
                $transaction_insert_parameter["loan_datetime"] = $loan["datetime_created"];
                $transaction_insert_parameter["return_datetime"] = date("Y-m-d H:i:s");
                $transaction_insert_parameter["assets_departments_loan_id"] = $parameters["assets_departments_loan_id"];
                $transaction_insert_parameter["users_id"] = $this->session->userdata("users_id");
                $transaction_insert_parameter["remark"] = $parameters["remark"];
                $transaction_insert_parameter["approver_name"] = $loan["approver_name"];
                $transaction_insert_parameter["datetime_created"] = date("Y-m-d H:i:s");
                
                $this->db->insert("transaction", $transaction_insert_parameter);
                
                if(intval($parameters["quantity"]) >= intval($loaned_quantity)){
                    /* Delete loan record */
                    $this->db->delete("assets_departments_loan", array("id"=>$parameters["assets_departments_loan_id"]));                        
                }else{
                    /* Reduce Quantity */    
                    $new_quantity = intval($loaned_quantity) - intval($parameters["quantity"]);
                    $this->db->where("id", $parameters["assets_departments_loan_id"]);
                    $this->db->update("assets_departments_loan", array("quantity" => $new_quantity, "remark" => $parameters["remark"]));
                }
                
                /* Update asset status */
                $this->db->where("assets_id", $assets_id);
                $this->db->join("assets_departments_loan", "assets_departments_loan.assets_departments_id = assets_departments.id", "inner");
                $query = $this->db->get("assets_departments");
                
                if($query->num_rows() == 0){
                    $this->db->where("id", $assets_id);
                    $this->db->update("assets", array("status"=>"available"));    
                }
            }
        }
    }
    
    /* 
     * Make asset loan 
     *
     * $parameters["assets_departments_id"] 
     * $parameters["quantity"] 
     * $parameters["borrower_name"] 
     * $parameters["borrower_entity"] 
     * $parameters["return_date"] 
     * $parameters["return_time"] 
     * $parameters["approver_name"] 
     * $parameters["remark"] 
     * $parameters["return_loan_form"] = optional; //Return path of loan form link
     * 
     * */
    function addLoan($parameters){
        
        $selected_datetime = strtotime($parameters["return_date"] . " " . $parameters["return_time"]);
        $diff = $selected_datetime - time();
        
        if($diff < 0){
            $diff = 0;
        }
        
        $insert_parameters = array();
        $insert_parameters["assets_departments_id"] = $parameters["assets_departments_id"];
        $insert_parameters["users_id"] = $this->session->userdata("users_id");
        $insert_parameters["quantity"] = $parameters["quantity"];
        $insert_parameters["borrower_name"] = $parameters["borrower_name"];
        $insert_parameters["borrower_entity"] = $parameters["borrower_entity"];
        $insert_parameters["loan_period"] = round($diff / 3600);
        $insert_parameters["approver_name"] = $parameters["approver_name"];
        $insert_parameters["remark"] = $parameters["remark"];
        $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
        
        $this->db->insert("assets_departments_loan", $insert_parameters);
           
        $insert_id = $this->db->insert_id();
        
        $departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id" => $parameters["assets_departments_id"]));
        
        $assets_id = 0;
        $origin_departments_id = 0;
        $origin_departments_name = "No Department Name";
        $origin_location = "No location";
        
        if($departments_row){
            $assets_id = $departments_row["assets_id"];
            $origin_departments_id = $departments_row["departments_id"];
            $origin_location = $departments_row["location"];
            
            $department_query = $this->db->get_where("departments", array("id" => $origin_departments_id));
            if($department_query->num_rows() > 0){
                $depart_row = $department_query->row();
                $origin_departments_name = $depart_row->departments_name;
            }
        }
        
        $insert_transaction = array();
        $insert_transaction["assets_id"] = $assets_id;
        $insert_transaction["transaction_type"] = "loan";
        $insert_transaction["quantity"] = $parameters["quantity"];
        $insert_transaction["origin_departments_id"] = $origin_departments_id;
        $insert_transaction["origin_departments_name"] = $origin_departments_name;
        $insert_transaction["origin_location"] = $origin_location;
        $insert_transaction["loan_period"] = round($diff / 3600);
        $insert_transaction["borrower_name"] = $parameters["borrower_name"];
        $insert_transaction["borrower_entity"] = $parameters["borrower_entity"];
        $insert_transaction["loan_datetime"] = $insert_parameters["datetime_created"];
        $insert_transaction["assets_departments_loan_id"] = $insert_id;
        $insert_transaction["users_id"] = $this->session->userdata("users_id");
        $insert_transaction["remark"] = $parameters["remark"];
        $insert_transaction["approver_name"] = $parameters["approver_name"];
        $insert_transaction["datetime_created"] = date("Y-m-d H:i:s");  
        
        $this->db->insert("transaction", $insert_transaction);
        
        /* Update asset status */
        $this->db->select("SUM(assets_departments_loan.quantity) AS loan_quantity");
        $this->db->where("assets_id", $assets_id);
        $this->db->join("assets_departments_loan", "assets_departments_loan.assets_departments_id = assets_departments.id", "inner");
        $query = $this->db->get("assets_departments");
        
        $loan_quantity = $query->row()->loan_quantity;
        
        $this->db->select("SUM(quantity) AS total_quantity");
        $this->db->where("assets_id", $assets_id);
        $query = $this->db->get("assets_departments");
        
        $total_quantity = $query->row()->total_quantity;
        
        if($loan_quantity >= $total_quantity){
            $this->db->where("id", $assets_id);
            $this->db->update("assets", array("status"=>"loan_out"));    
        }
        
        $data = array();
            
        $loan_parameters = array();
        $loan_parameters["form_date"] = date("d-M-Y",strtotime($insert_transaction["datetime_created"]));
        $loan_parameters["form_department"] = $insert_transaction["origin_departments_id"];
        $loan_parameters["form_request_by"] = $insert_transaction["borrower_entity"]; 
        $loan_parameters["form_purpose"] = $insert_transaction["remark"];
        $loan_parameters["loaned_assets"][] = $insert_transaction["assets_departments_loan_id"];
        $loan_parameters["form_date_loan"] = date("d-M-Y",strtotime($insert_transaction["datetime_created"]));
        $loan_parameters["form_issued_by"] = $this->session->userdata("person_name");
        $loan_parameters["form_borrower_name"] = $insert_transaction["borrower_name"];
        $loan_parameters["form_approver_name"] = $insert_transaction["approver_name"];
        $loan_parameters["form_date_return"] = "";
        $loan_parameters["form_return_by"] = "";
        $loan_parameters["form_receiving_officer"] = ""; 
        $data["loan_form_url"] = site_url($this->config->item("loan_form", "routes_uri")) . "/?" . http_build_query($loan_parameters);
        
        return $data;
    }
}
