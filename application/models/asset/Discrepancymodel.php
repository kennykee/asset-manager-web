<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Discrepancymodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get discrepancies by department ID 
     *
     * $parameters["departments_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * $parameters["from_date"]
     * 
     * */
    function getDiscrepancyByDepartment($parameters){
        
        $data = array();
        
        $this->db->select("*, assets_departments.id AS assets_departments_id");
        $this->db->where("departments_id", $parameters["departments_id"]);
        $this->db->where("assets.enable_tracking", "1");
        $this->db->join("assets_departments", "assets_departments.assets_id = assets.id", "inner");
        $assets_query = $this->db->get("assets");
        
        $assets_id = array();
        $assets_data = array();
        $assets_departments_id = array();
        $assets_departments_loan = array();
        
        foreach($assets_query->result_array() as $assets_row){
            $assets_data[$assets_row["assets_id"]][] = $assets_row;
            $assets_departments_id[] = $assets_row["assets_departments_id"];
            $assets_id[] = $assets_row["assets_id"];
        }
        
        if(count($assets_departments_id) > 0){
            $this->db->where_in("assets_departments_id", $assets_departments_id);
            $loan_query = $this->db->get("assets_departments_loan");
            foreach($loan_query->result_array() as $loan_row){
                $assets_departments_loan[$loan_row["assets_departments_id"]][] = $loan_row;
            }
        }
        
        foreach($assets_data as &$assets_line){
            foreach($assets_line as &$line){
                if(isset($assets_departments_loan[$line["assets_departments_id"]])){
                    $line["loan"] = $assets_departments_loan[$line["assets_departments_id"]];
                }    
            }
        }
        
        foreach($assets_data as $key=>$assets_liner){
            
            $total_quantity = 0;
            $loan_quantity = 0;
            
            foreach($assets_liner as $liner){
                    
                $total_quantity += intval($liner["quantity"]);
                
                if(isset($liner["loan"])){
                    foreach($liner["loan"] as $loan_row){
                        $loan_quantity += intval($loan_row["quantity"]);
                    }
                }
            }
            
            $assets_data[$key]["quantity_track"] = 0;
            $assets_data[$key]["scanned_time"] = NULL;
            $assets_data[$key]["track_remark"] = "";
            $assets_data[$key]["total_quantity"] = $total_quantity;
            $assets_data[$key]["loan_quantity"] = $loan_quantity;
            $assets_data[$key]["balance"] = $total_quantity - $loan_quantity;
        }
        
        if(count($assets_id) > 0){
            
            $this->db->select("assets_id, quantity, datetime_scanned,remark");
            $this->db->where("departments_id", $parameters["departments_id"]);
            $this->db->where_in("assets_id", $assets_id);
            $this->db->where("datetime_scanned >=", mysql_date($parameters["from_date"]));
            $this->db->order_by("datetime_scanned", "asc");
            $track_query = $this->db->get("assets_tracking");
            
            foreach($track_query->result_array() as $track_row){
                $assets_data[$track_row["assets_id"]]["quantity_track"] = $track_row["quantity"];
                $assets_data[$track_row["assets_id"]]["scanned_time"] = $track_row["datetime_scanned"];
                $assets_data[$track_row["assets_id"]]["track_remark"] = $track_row["remark"];
            }
        }
        
        foreach($assets_data as $key=>&$row){
            
            $row["assets_name"] = reset($row)["assets_name"];
            $row["attachments_id"] = reset($row)["attachments_id"];
            $row["barcode"] = reset($row)["barcode"];
            $row["status"] = reset($row)["status"];
            
            if($row["total_quantity"] == $row["quantity_track"]){
                unset($assets_data[$key]);
            }
        }
        
        //check from 2 direction
                
        return $assets_data;
    }
    
    /* 
     * Get tracking history by single asset
     *
     * $parameters["assets_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getTrackingHistoryByAsset($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where("assets_id", $parameters["assets_id"]);
        $query = $this->db->get("assets_tracking");
        
        $departments_id = array();
        $departments = array();
        
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
        
        foreach($query->result() as $row){
            $departments_id[] = $row->departments_id;
        }
        
        if(count($departments_id) > 0){
            $this->db->where_in("id", $departments_id);
            $department_query = $this->db->get("departments");
            
            foreach($department_query->result() as $row){
                $departments[$row->id] = $row->departments_name;
            }
        }
        
        foreach($query->result_array() as $row){
            $row["departments_name"] = "No department name";
            if(isset($departments[$row["departments_id"]])){
                $row["departments_name"] = $departments[$row["departments_id"]]; 
            }
            $data[] = $row;    
        }
        
        return $data;
    }
    
    /* 
     * Get discrepancies by department ID 
     *
     * $parameters["departments_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * 
     * */
    function getTrackingHistoryByDepartments($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $query = $this->db->get_where("assets_tracking", array("departments_id"=> $parameters["departments_id"]));
        
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
            $users_info["person_name"] = "User record not found";
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
}
