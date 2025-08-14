<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transfermodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get transfer histories by departments ID list
     *
     * $parameters["departments"] = array(); //- refers to list department ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["start_date"] = optional
     * $parameters["end_date"] = optional 
     * 
     * */
    function getTransfersByDepartments($parameters){
        
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
        $this->db->group_start()
                    ->where_in("origin_departments_id", $parameters["departments"])
                    ->or_where_in("destination_departments_id", $parameters["departments"])
                  ->group_end();
        $this->db->where_in("transaction_type", array("transfer_department", "transfer_location"));
        $query = $this->db->get("transaction");
           
        $assets_id = array();
        $users_id = array();
        $assets = array();
        $users = array();
        
        $departments_id = array();
        $departments = array();
        
        foreach($query->result() as $row){
            $assets_id[] = $row->assets_id;
            $users_id[] = $row->users_id;
        }
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
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
            
            $users_info = array();
            $users_info["person_name"] = $row["approver_name"];
            $users_info["email"] = "User record not found";
            
            if(isset($users[$row["users_id"]])){
                $user_selected = $users[$row["users_id"]];
                $users_info["person_name"] = $user_selected["person_name"];
                $users_info["email"] = $user_selected["email"];
            }
            $row["users"] = $users_info;
            
            $data[$row["origin_departments_id"]][] = $row;
            
            if($row["origin_departments_id"] != $row["destination_departments_id"]){
                $data[$row["destination_departments_id"]][] = $row;
            }    
        }
     
        return $data;   
    }
    
    /* 
     * Get transfer histories by asset ID
     *
     * $parameters["assets_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * 
     * */
    function getTransferBySingleAsset($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where_in("transaction_type", array("transfer_department", "transfer_location"));
        $this->db->where("assets_id",  $parameters["assets_id"]);
        $query = $this->db->get("transaction");
           
        $users_id = array();
        $users = array();
        
        $departments_id = array();
        $departments = array();
        
        foreach($query->result() as $row){
            $users_id[] = $row->users_id;
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
            
            $data[] = $row;
        }
        return $data;   
    }
    
    /* 
     * Add quantity to asset 
     *
     * $parameters["assets_id"] 
     * $parameters["departments_id"]
     * $parameters["quantity"]
     * $parameters["location"]
     * $parameters["remark"] 
     * 
     * */
    function addQuantity($parameters){
        
        /* Check if exist first */
        $this->db->where("assets_id", $parameters["assets_id"]);
        $this->db->where("departments_id", $parameters["departments_id"]);
        $this->db->where("LOWER(location)", strtolower($parameters["location"]));
        $query = $this->db->get("assets_departments");
        
        if($query->num_rows() > 0){
            
            $row = $query->row();
            $id = $row->id;
            $quantity = $row->quantity;
            
            $this->db->where("id", $id);
            $this->db->update("assets_departments", array("quantity" => (intval($quantity) + intval($parameters["quantity"]))));
                
        }else{
            
            $insert_parameters = array();
            $insert_parameters["assets_id"] = $parameters["assets_id"];
            $insert_parameters["departments_id"] = $parameters["departments_id"];
            $insert_parameters["quantity"] = $parameters["quantity"];
            $insert_parameters["location"] = ucwords($parameters["location"]);
            $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
            
            $this->db->insert("assets_departments", $insert_parameters);
        }
        
        $departments_name = "No department name";
        $department_query = $this->db->get_where("departments", array("id"=>$parameters["departments_id"]));
        if($department_query->num_rows() > 0){
            $department_row = $department_query->row();
            $departments_name = $department_row->departments_name;
        }
        
        
        /* Insert into transaction */
        $transaction_parameters = array();
        $transaction_parameters["assets_id"] = $parameters["assets_id"];
        $transaction_parameters["transaction_type"] = "transfer_department";
        $transaction_parameters["quantity"] = $parameters["quantity"];
        $transaction_parameters["origin_departments_id"] = 0;
        $transaction_parameters["origin_departments_name"] = "[Additional quantity added]";
        $transaction_parameters["origin_location"] = "[Additional quantity added]";
        $transaction_parameters["destination_departments_id"] = $parameters["departments_id"];
        $transaction_parameters["destination_departments_name"] = $departments_name;
        $transaction_parameters["destination_location"] = ucwords($parameters["location"]);
        $transaction_parameters["users_id"] = $this->session->userdata('users_id');
        $transaction_parameters["remark"] = $parameters["remark"];
        $transaction_parameters["approver_name"] = $this->session->userdata("person_name");
        $transaction_parameters["datetime_created"] = date("Y-m-d H:i:s");
        
        $this->db->insert("transaction", $transaction_parameters);
    }
    
    /* 
     * Transfer asset
     *
     * $parameters["assets_departments_id"] = origin 
     * $parameters["departments_id"] = destination
     * $parameters["location"] = destination
     * $parameters["quantity"] 
     * $parameters["remark"]
     * 
     * */
    function transferQuantity($parameters){
        
        /* Get origin asset */
        $origin_query = $this->db->get_where("assets_departments", array("id"=>$parameters["assets_departments_id"]));
        
        if($origin_query->num_rows() > 0){
            $origin_row = $origin_query->row();
            
            $origin_quantity = $origin_row->quantity;
            $assets_id = $origin_row->assets_id;
            $origin_departments_id = $origin_row->departments_id;
            $origin_location = $origin_row->location;
            
            /* Check if exist first */
            $this->db->where("assets_id", $assets_id);
            $this->db->where("departments_id", $parameters["departments_id"]);
            $this->db->where("LOWER(location)", strtolower($parameters["location"]));
            $query = $this->db->get("assets_departments");
            
            if($query->num_rows() > 0){
                
                $row = $query->row();
                $id = $row->id;
                $quantity = $row->quantity;
                
                $this->db->where("id", $id);
                $this->db->update("assets_departments", array("quantity" => (intval($quantity) + intval($parameters["quantity"]))));
                    
            }else{
                
                $insert_parameters = array();
                $insert_parameters["assets_id"] = $assets_id;
                $insert_parameters["departments_id"] = $parameters["departments_id"];
                $insert_parameters["quantity"] = $parameters["quantity"];
                $insert_parameters["location"] = ucwords($parameters["location"]);
                $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
                
                $this->db->insert("assets_departments", $insert_parameters);
            }
            
            /* Deduct existing or remove */
            if((intval($origin_quantity) - intval($parameters["quantity"])) <= 0){
                /* Remove */
                $this->db->delete("assets_departments", array("id"=>$parameters["assets_departments_id"]));
            }else{
                /* Reduce */
                $this->db->where("id", $parameters["assets_departments_id"]);
                $this->db->update("assets_departments", array("quantity" => (intval($origin_quantity) - intval($parameters["quantity"]))));
            }
            
            
            $departments_list = array();
            
            $this->db->where_in("id", array($parameters["departments_id"], $origin_departments_id));
            $department_query = $this->db->get("departments");
            foreach($department_query->result() as $department_row){
                $departments_list[$department_row->id] = $department_row->departments_name;
            }
            
            
            /* Insert into transaction */
            $transaction_parameters = array();
            $transaction_parameters["assets_id"] = $assets_id;
            $transaction_parameters["transaction_type"] = ($parameters["departments_id"] != $origin_departments_id)? "transfer_department" : "transfer_location";
            $transaction_parameters["quantity"] = $parameters["quantity"];
            $transaction_parameters["origin_departments_id"] = $origin_departments_id;
            $transaction_parameters["origin_departments_name"] = isset($departments_list[$origin_departments_id])? $departments_list[$origin_departments_id] : "No department name";
            $transaction_parameters["origin_location"] = $origin_location;
            $transaction_parameters["destination_departments_id"] = $parameters["departments_id"];
            $transaction_parameters["destination_departments_name"] = isset($departments_list[$parameters["departments_id"]])? $departments_list[$parameters["departments_id"]] : "No department name";
            $transaction_parameters["destination_location"] = ucwords($parameters["location"]);
            $transaction_parameters["users_id"] = $this->session->userdata('users_id');
            $transaction_parameters["remark"] = $parameters["remark"];
            $transaction_parameters["approver_name"] = $this->session->userdata("person_name");
            $transaction_parameters["datetime_created"] = date("Y-m-d H:i:s");
            
            $this->db->insert("transaction", $transaction_parameters);
            
            return TRUE;
        }
        return FALSE;
    }
    
    /* 
     * Add quantity to asset
     *
     * $parameters["transaction_id"] 
     * $parameters["remark"] 
     * 
     * */
    function updateTransfer($parameters){
        $this->db->where("id", $parameters["transaction_id"]);
        $this->db->update("transaction", array("remark"=>$parameters["remark"]));
    }
    
    /* 
     * Get asset department and location for one asset 
     *
     * $parameters["assets_id"] 
     * 
     * */
    function getAssetLocation($parameters){
        
        $data = array();
        
        $this->db->order_by("departments_name", "ASC");
        $this->db->order_by("location", "ASC");
        $this->db->select("*, assets_departments.id AS assets_departments_id");
        $this->db->where("assets_id", $parameters["assets_id"]);
        $this->db->join("departments", "departments.id = assets_departments.departments_id", "left");
        
        $query = $this->db->get("assets_departments");
        
        $assets_departments_id = array();
        $assets_loan = array();
        
        foreach($query->result() as $row){
            $assets_departments_id[] = $row->assets_departments_id;
        }
        
        if(count($assets_departments_id) > 0){
            $this->db->where_in("assets_departments_id", $assets_departments_id);
            $loan_query = $this->db->get("assets_departments_loan");
            
            foreach($loan_query->result_array() as $row){
                $assets_loan[$row["assets_departments_id"]][] = $row;
            }
        }
        
        foreach($query->result_array() as $row){
            $row["loan"] = isset($assets_loan[$row["assets_departments_id"]])? $assets_loan[$row["assets_departments_id"]] : array();
            $row["departments_name"] = isset($row["departments_name"])? $row["departments_name"] : "No Department";
            $data[] = $row;
        }
        
        return $data;
    }

    /* 
     * Check whether transaction_id is accessible or writable based on current URI access array 
     *
     * $transaction_id
     * 
     * */
    function isTransferAccessibleByCurrentAccessArray($transaction_id){ 
        
        /* Blanket Access */
        if(in_array("*", $this->access_array)){
            return TRUE;
        }
        
        $this->db->group_start()
                    ->where_in("origin_departments_id", $this->access_array)
                    ->or_where_in("destination_departments_id", $this->access_array)
                  ->group_end();
        $this->db->where_in("transaction_type", array("transfer_department", "transfer_location"));
        
        $query = $this->db->get_where("transaction", array("id" => $transaction_id));
        
        if($query->num_rows() > 0){
            return TRUE;
        }
        return FALSE;
    }
}
