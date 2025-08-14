<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assetmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get assets by departments ID list
     *
     * $parameters["departments"] = array(); //- refers to list department ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getAssetsByDepartments($parameters){
        
        $data = array();
        
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->select("SQL_CALC_FOUND_ROWS *, assets.id AS assets_id", FALSE);
        $this->db->where_in("departments_id", $parameters["departments"]);
        $this->db->join("assets_departments", "assets_departments.assets_id = assets.id", "left");
        
        $query = $this->db->get("assets");
        
        $assets_list = array();
        foreach($query->result_array() as $asset){
            $assets_list[] = $asset["assets_id"]; 
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
        
        /* Category */
        $category_list = array();
        if(count($assets_list) > 0){
            $this->db->distinct();
            $this->db->select("categories_name, assets_id, categories_id");
            $this->db->where_in("assets_id", $assets_list);
            $this->db->join("categories", "categories.id = assets_categories.categories_id", "inner");
            $category_query = $this->db->get("assets_categories");
                
            foreach($category_query->result_array() as $category){
                $category_list[$category["assets_id"]][] = $category;
            }    
        }
        
        foreach($query->result_array() as $row){
            
            $depreciation = FALSE;
            $depreciation_percent = 0;
            $colour = "blue";
            
            $invoice_timestamp = strtotime($row["invoice_date"]);
            $assets_value = floatval($row["assets_value"]);
            $lifespan = intval($row["assets_lifespan"]);
            $salvage_value = floatval($row["salvage_value"]);
            $days_per_month = 30.4375;
            
            if(intval(date("Y", $invoice_timestamp)) > 1990 && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                    
                $total_time = $lifespan * $days_per_month * 24 * 3600;
                $time_diff = time() - $invoice_timestamp;
                if($time_diff > $total_time){
                    $time_diff = $total_time;
                }
                $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2);
                $depreciation_percent = ceil(($time_diff/$total_time * 100));
                
                if($depreciation_percent > 50){
                    $colour = "orange";
                }else if($depreciation_percent > 80){
                    $colour = "red";
                }
            }
            
            $row["category"] = isset($category_list[$row["assets_id"]])? $category_list[$row["assets_id"]] : array();
            $row["depreciation"] = $depreciation;
            $row["depreciation_percent"] = $depreciation_percent;
            $row["depreciation_colour"] = $colour;
            $data[$row["departments_id"]][] = $row;
        }
        
        return $data;
    }
        
    /* 
     * Get an asset details by one assets_id
     *
     * $parameters["assets_id"] 
     * Return one record only. 
     * 
     * */
    function getAsset($parameters){ /* Single asset */
        
        $data = array();
        
        $this->db->select("*, assets.id AS assets_id");
        $query = $this->db->get_where("assets", array("id" => $parameters["assets_id"]));
        
        if($query->num_rows() > 0){
            $data = $query->row_array();
            
            /* Departments and Locations */
            $this->db->where("assets_id", $parameters["assets_id"]);
            $this->db->join("assets_departments", "assets_departments.departments_id = departments.id", "inner");
            $department_query = $this->db->get("departments");
            $data["departments"] = $department_query->result_array();
            
            /* Category */
            $this->db->where("assets_id", $parameters["assets_id"]);
            $this->db->join("assets_categories", "assets_categories.categories_id = categories.id", "inner");
            $category_query = $this->db->get("categories");
            $data["categories"] = $category_query->result_array();
            
            $depreciation = FALSE;
            $depreciation_percent = 0;
            $colour = "blue";
            
            $invoice_timestamp = strtotime($data["invoice_date"]);
            $assets_value = floatval($data["assets_value"]);
            $lifespan = intval($data["assets_lifespan"]);
            $salvage_value = floatval($data["salvage_value"]);
            $days_per_month = 30.4375;
            
            if(intval(date("Y", $invoice_timestamp)) > 1990 && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                    
                $total_time = $lifespan * $days_per_month * 24 * 3600;
                $time_diff = time() - $invoice_timestamp;
                if($time_diff > $total_time){
                    $time_diff = $total_time;
                }
                $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2);
                $depreciation_percent = ceil(($time_diff/$total_time * 100));
                
                if($depreciation_percent > 50){
                    $colour = "orange";
                }else if($depreciation_percent > 80){
                    $colour = "red";
                }
            }
            
            $data["depreciation"] = $depreciation;
            $data["depreciation_percent"] = $depreciation_percent;
            $data["depreciation_colour"] = $colour;
        }
        
        return $data;
    }
    
    /* 
     * Search asset
     *
     * $parameters["page_no"]
     * $parameters["count_per_page"] 
     * $parameters["sort"] 
     * $parameters["term"] 
     * $parameters["sort_field"]
     * $parameters["access_array"] = restrict based on accessible   
     * 
     * - Return asset name, barcode, location, department, status, photo, quantity
     * - Search into fields asset name > barcode > invoice number,
     * */
    function searchAsset($parameters){
        
        $data = array();
        
        $this->db->select("*, assets.id AS assets_id");
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->group_start()
                    ->or_like('assets_name', $parameters["term"], 'both')
                    ->or_where('barcode', $parameters["term"])
                    ->or_where('invoice_number', $parameters["term"])
                 ->group_end();
        $this->db->join("assets_departments", "assets_departments.assets_id = assets.id","left");
        
        if(!in_array("*", $parameters["access_array"])){
            $this->db->where_in("assets_departments.departments_id", $parameters["access_array"]);
        }
        
        $query = $this->db->get("assets");
        
        $departments_id = array();
        $departments = array();
        
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
        
        foreach($query->result() as $row){
            $asset_info = array();
            $asset_info["assets_id"] = $row->assets_id;
            $asset_info["assets_name"] = $row->assets_name;
            $asset_info["barcode"] = $row->barcode;
            $asset_info["status"] = $row->status;
            $asset_info["attachments_id"] = $row->attachments_id;
            $asset_info["location"] = $row->location? $row->location : "-";
            $asset_info["quantity"] = $row->quantity? $row->quantity : "0";
            $asset_info["departments_name"] = isset($departments[$row->departments_id])? $departments[$row->departments_id] : "No Department";
            $data[] = $asset_info;   
        }
        
        return $data;
    }
    
    /* 
     * Update asset
     *
     * $parameters["assets_id"]
     * $parameters["attachments_id"]
     * $parameters["assets_name"] 
     * $parameters["enable_tracking"] = optional
     * $parameters["assets_value"]
     * $parameters["assets_lifespan"] 
     * $parameters["maintenance_interval"] 
     * $parameters["serial_number"] 
     * $parameters["categories"] = array()
     * $parameters["supplier_name"] 
     * $parameters["brand"] 
     * $parameters["salvage_value"] 
     * $parameters["warranty_expiry"] 
     * $parameters["invoice_number"] 
     * $parameters["invoice_date"] 
     * $parameters["status"] 
     * $parameters["remarks"]
     * 
     * */
    function updateAsset($parameters){
        
        $update_parameters = array();
        $update_parameters["assets_name"] = $parameters["assets_name"];
        $update_parameters["assets_value"] = $parameters["assets_value"];
        $update_parameters["assets_lifespan"] = $parameters["assets_lifespan"];
        $update_parameters["attachments_id"] = $parameters["attachments_id"];
        $update_parameters["serial_number"] = $parameters["serial_number"];
        $update_parameters["brand"] = $parameters["brand"];
        $update_parameters["status"] = $parameters["status"];
        $update_parameters["salvage_value"] = $parameters["salvage_value"];
        $update_parameters["supplier_name"] = $parameters["supplier_name"];
        $update_parameters["invoice_number"] = $parameters["invoice_number"];
        $update_parameters["invoice_date"] = $parameters["invoice_date"];
        $update_parameters["maintenance_interval"] = $parameters["maintenance_interval"];
        $update_parameters["warranty_expiry"] = $parameters["warranty_expiry"];
        $update_parameters["remarks"] = $parameters["remarks"];
        
        if(isset($parameters["enable_tracking"])){
            $update_parameters["enable_tracking"] = $parameters["enable_tracking"];
        }
        
        $this->db->where("id", $parameters["assets_id"]);
        $this->db->update("assets", $update_parameters);
        
        /* Category Update */
        $this->db->delete("assets_categories", array("assets_id"=>$parameters["assets_id"]));
        
        foreach($parameters["categories"] as $category){
            $category_insert_parameters = array();
            $category_insert_parameters["assets_id"] = $parameters["assets_id"];
            $category_insert_parameters["categories_id"] = $category;
            $category_insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
            
            $this->db->insert("assets_categories", $category_insert_parameters);
        }
    }
    
    /* 
     * Add asset
     *
     * $parameters["attachments_id"]
     * $parameters["assets_name"] 
     * $parameters["enable_tracking"]
     * $parameters["assets_value"]
     * $parameters["assets_lifespan"] 
     * $parameters["maintenance_interval"] 
     * $parameters["serial_number"] 
     * $parameters["categories"] = array()
     * $parameters["supplier_name"] 
     * $parameters["brand"] 
     * $parameters["salvage_value"] 
     * $parameters["warranty_expiry"] 
     * $parameters["invoice_number"] 
     * $parameters["invoice_date"] 
     * $parameters["status"] 
     * $parameters["remarks"]
     * $parameters["departments"] = array("departments_id", "location", "quantity")
     * 
     * */
    function addAsset($parameters){
                    
        $insert_parameters = array();
        $insert_parameters["assets_name"] = $parameters["assets_name"];
        $insert_parameters["assets_value"] = $parameters["assets_value"];
        $insert_parameters["assets_lifespan"] = $parameters["assets_lifespan"];
        $insert_parameters["attachments_id"] = $parameters["attachments_id"];
        $insert_parameters["serial_number"] = $parameters["serial_number"];
        $insert_parameters["brand"] = $parameters["brand"];
        $insert_parameters["enable_tracking"] = $parameters["enable_tracking"];
        $insert_parameters["status"] = $parameters["status"];
        $insert_parameters["salvage_value"] = $parameters["salvage_value"];
        $insert_parameters["supplier_name"] = $parameters["supplier_name"];
        $insert_parameters["invoice_number"] = $parameters["invoice_number"];
        $insert_parameters["invoice_date"] = $parameters["invoice_date"];
        $insert_parameters["maintenance_interval"] = $parameters["maintenance_interval"];
        $insert_parameters["warranty_expiry"] = $parameters["warranty_expiry"];
        $insert_parameters["remarks"] = $parameters["remarks"];   
        $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
        
        $this->db->insert("assets", $insert_parameters);
            
        $assets_id = $this->db->insert_id();
        
        //update barcode
        $this->db->where("id", $assets_id);
        $this->db->update("assets", array("barcode" => str_pad($assets_id, 6, "0", STR_PAD_LEFT)));
        
        foreach($parameters["categories"] as $category){
            $category_insert_parameters = array();
            $category_insert_parameters["assets_id"] = $assets_id;
            $category_insert_parameters["categories_id"] = $category;
            $category_insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
            
            $this->db->insert("assets_categories", $category_insert_parameters);
        }
        
        foreach($parameters["departments"] as $department){
            $department_insert_parameters = array();
            $department_insert_parameters["assets_id"] = $assets_id;
            $department_insert_parameters["departments_id"] = $department["departments_id"];
            $department_insert_parameters["quantity"] = $department["quantity"];
            $department_insert_parameters["location"] = $department["location"];
            $department_insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
            $this->db->insert("assets_departments", $department_insert_parameters);
        }
        
        return $assets_id;
    }
    
    /* 
     * Get a list of departments by a list of assets ID
     *
     * $parameters["assets_id"] = array() 
     * 
     * */
    function getDepartmentsByAssets($parameters){
                
        $data = array();
        
        if(count($parameters["assets_id"]) > 0){
            $this->db->where_in("assets_id", $parameters["assets_id"]);
            $query = $this->db->get("assets_departments");
            
            foreach($query->result_array() as $row){
                $data[$row["assets_id"]][] = $row;
            }
        }        
        
        return $data;
    }
    
    /* 
     * Check if user has permission to view asset 
     *
     * $parameters["access_array"] = array(); //- refers to list department ID
     * $parameters["assets_id"] 
     * 
     * */
    function isAssetAccessibleByAccessArray($parameters){
        
        if(count($parameters["access_array"]) > 0){
            
            if(in_array("*", $parameters["access_array"])){
                return TRUE;    
            }
            
            $this->db->where_in("departments_id", $parameters["access_array"]);
            $this->db->where("assets_id", $parameters["assets_id"]);
            $query = $this->db->get("assets_departments");    
            
            if($query->num_rows() > 0){
                return TRUE;
            }
        }
        return FALSE;        
    }
    
    /* 
     * Check if user has permission to access assets_departments_id 
     *
     * $parameters["access_array"] = array(); //- refers to list department ID
     * $parameters["assets_departments_id"] 
     * 
     * */
    function isAssetLocationAccessibleByAccessArray($parameters){
        
        if(count($parameters["access_array"]) > 0){
            
            if(in_array("*", $parameters["access_array"])){
                return TRUE;    
            }
            
            $this->db->where_in("departments_id", $parameters["access_array"]);
            $this->db->where("id", $parameters["assets_departments_id"]);
            $query = $this->db->get("assets_departments");    
            
            if($query->num_rows() > 0){
                return TRUE;
            }
        }
        return FALSE;        
    }
    
    /*
     * $parameters["assets_id"]
     * */
    function printLabel($parameters){
        
        $data = array();
        $data["success"] = 0;
        $data["code"] = 400;
        $data["message"] = array();
        
        $assets_id = intval($parameters["assets_id"]);
        
        $this->db->where("id", $assets_id);
        $query = $this->db->get("assets");
        
        if($query->num_rows() > 0){
            
            $row = $query->row();
            $barcode = trim($row->barcode);
            $invoice_date = $row->invoice_date;
            $assets_name = trim($row->assets_name);
            $warranty_expiry = $row->warranty_expiry;
            $line_two = "";
            $line_three = "";
            $line_four = "";
            $departments_name = "";
            $categories_name = "";
            $printer_name = "GX420t";
            
            $invoice_year = date("Y", strtotime($invoice_date));
            if($invoice_year <= 1990){
                $invoice_date = "-";
            }else{
                $invoice_date = date("my", strtotime($invoice_date));
            }
            
            $warranty_year = date("Y", strtotime($warranty_expiry));
            if($warranty_year <= 1990){
                $warranty_expiry = "-";
            }else{
                $warranty_expiry = date("d M Y", strtotime($warranty_expiry));   
            }
            
            if(strlen($assets_name) > 33){
                $reverse = strrev($assets_name);
                $blank_pos = strpos($reverse, " ", strlen($reverse) - 34);
                if($blank_pos !== FALSE){
                    $length = strlen($reverse) - $blank_pos - 1;
                    $line_two = trim(substr($assets_name, $length));
                    $assets_name = trim(substr($assets_name, 0, $length));
                }
            }
            
            $this->db->distinct();
            $this->db->select("departments_name");
            $this->db->join("departments", "departments.id = assets_departments.departments_id", "inner");
            $departments_query = $this->db->get_where("assets_departments", array("assets_id"=> $row->id));
            
            foreach($departments_query->result() as $departments_row){
                if(strlen($departments_name) > 0){
                    $departments_name .= ",";
                }
                $departments_name .= $departments_row->departments_name;
            }
            
            $this->db->distinct();
            $this->db->select("categories_name");
            $this->db->join("categories", "categories.id = assets_categories.categories_id", "inner");
            $categories_query = $this->db->get_where("assets_categories", array("assets_id" => $row->id));
            
            foreach($categories_query->result() as $categories_row){
                if(strlen($categories_name) > 0){
                    $categories_name .= ",";
                }
                $categories_name .= $categories_row->categories_name;
            }
            
            if(strlen($line_two) == 0){
                $line_two = $departments_name . " / " . $categories_name;
            }else{
                $line_three = $departments_name . " / " . $categories_name;
            }
            
            if(strlen($line_three) == 0){
                $line_three = "Warranty Date: " . $warranty_expiry;
            }else{
                $line_four = "Warranty Date: " . $warranty_expiry;
            }
            
            if(strlen($line_two) == 0){
                $line_two = " ";
            }
            
            if(strlen($line_three) == 0){
                $line_three = " ";
            }
            
            if(strlen($line_four) == 0){
                $line_four = " ";
            }
            
            $zpl = '^XA^FO235,20^A0N,30,20^FDHFC/' . $invoice_date . '^FS' .
                   '^FO245,52^A0N,30,20^FD' . $barcode . '^FS' .
                   '^FO18,75^A0N,20,20^FD' . $assets_name . '^FS' .
                   '^FO18,95^A0N,20,20^FD' . $line_two . '^FS' .
                   '^BY2,3^FS' .
                   '^FO20,20^BCN,50,N,N,N^FD' . $barcode . '^FS' .
                   '^FO18,115^A0N,20,20^FD' . $line_three . '^FS' .
                   '^FO18,135^A0N,20,20^FD' . $line_four . '^FS' .
                   '^XZ';
            
            $printer_query = $this->db->get_where("config", array("config_key"=>"printer_name"));
            
            if($printer_query->num_rows() > 0){
                $printer_row = $printer_query->row();
                $printer_name = $printer_row->config_value;
            }
            
            $print_parameters = array();
            $print_parameters["printer_name"] = $printer_name;
            $print_parameters["zpl_code"] = $zpl;
            
            $json = json_encode($print_parameters);
            
            $base64 = base64_encode($json);
            
            $return = array();
            
            $exec_string = 'java -jar E:\proxy\ZebraProxyV2.jar ' . $base64;
            exec($exec_string, $return);
            
            $return = implode("", $return);
            
            $return_array = json_decode($return, TRUE);
            $data["success"] = $return_array["success"];
            $data["code"] = $return_array["code"];
            $data["message"] = $return_array["message"];
        }else{
            $data["message"][] = "Asset with ID " . $parameters["assets_id"] . " could not be found. Continuing printing next asset.";
        }
        
        return $data;   
    }
    
    /* 
     * Return all assets within departments list. A simpler query for report function.
     *
     * $parameters["departments"] 
     * 
     * */
    function getAllAssetsByDepartments($parameters){
        
        $this->db->select("*, assets.id AS assets_id");
        $this->db->where_in("departments_id", $parameters["departments"]);
        $this->db->join("assets_departments", "assets_departments.assets_id = assets.id", "inner");
        
        $query = $this->db->get("assets");
        
        return $query->result_array();
    }
    
    /* 
     * Return a record from assets_departments table by ID 
     *
     * $parameters["assets_departments_id"] 
     * 
     * */
    function getAssetLocationByAssetsDepartmentsID($parameters){
        
        $query = $this->db->get_where("assets_departments", array("id" => $parameters["assets_departments_id"]));
        
        if($query->num_rows() > 0){
            return $query->row_array();
        }
        
        return FALSE;
    }
    
    /* 
     * Return a record from assets_departments table by assets_id, department ID and location name 
     *
     * $parameters["departments_id"]
     * $parameters["location"]
     * $parameters["assets_id"]  
     * 
     * */
    function getAssetLocationByFilter($parameters){
        $this->db->where("departments_id", $parameters["departments_id"]);
        $this->db->where("assets_id", $parameters["assets_id"]);
        $this->db->where("LOWER(location)", strtolower($parameters["location"]));
        
        $query = $this->db->get("assets_departments");
        
        if($query->num_rows() > 0){
            return $query->row_array();
        }
        
        return FALSE;
    }
    
    /* 
     * Return all assets and linked information in the database including written off.
     * For mobile only. This function loads everything and takes time.
     * 
     * - Returns each table information related in relational format.
     * 
     * - No parameter  
     * 
     * */
    function getAssetsForMobile(){
            
        $data = array();
        
        $data["assets"] = array();
        $data["assets_categories"] = array();
        $data["assets_departments"] = array();
        $data["assets_departments_loan"] = array();
        $data["assets_maintenance"] = array();
        
        $data["categories"] = array();
        $data["departments"] = array();
        
        /* Categories */
        
        $categories_query = $this->db->get("categories");
        
        foreach($categories_query->result_array() as $categories_row){
            $category = array();
            $category["categories_id"] = $categories_row["id"];
            $category["categories_name"] = $categories_row["categories_name"];
            $category["lifespan_default"] = $categories_row["lifespan_default"];
            $category["tracking_default"] = $categories_row["tracking_default"];
            $data["categories"][] = $category;
        }        
        
        /* Departments */
        
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $departments_row){
            $department = array();
            $department["departments_id"] = $departments_row["id"];
            $department["departments_name"] = $departments_row["departments_name"];
            $data["departments"][] = $department;
        }
        
        /* Assets */
        
        $assets_id = array();
        $assets_departments_id = array();
        
        $assets_query = $this->db->get("assets");
        
        foreach($assets_query->result_array() as $assets_row){
            $asset = array();
            $asset["assets_id"] = $assets_row["id"];
            $asset["assets_name"] = $assets_row["assets_name"];
            $asset["assets_lifespan"] = $assets_row["assets_lifespan"]; 
            $asset["attachments_id"] = $assets_row["attachments_id"]; 
            $asset["barcode"] = $assets_row["barcode"]; 
            $asset["serial_number"] = $assets_row["serial_number"]; 
            $asset["brand"] = $assets_row["brand"];
            $asset["enable_tracking"] = $assets_row["enable_tracking"];
            $asset["status"] = $assets_row["status"];
            $asset["purchase_date"] = $assets_row["invoice_date"];
            $asset["maintenance_interval"] = $assets_row["maintenance_interval"];
            $asset["warranty_expiry"] = $assets_row["warranty_expiry"];
            $asset["remarks"] = $assets_row["remarks"];
            $data["assets"][] = $asset;
            
            $assets_id[] = $assets_row["id"];
        }
        
        /* Assets Category */
        if(count($assets_id) > 0){
            $this->db->where_in("assets_id", $assets_id);
            $assets_categories_query = $this->db->get("assets_categories");
            
            foreach($assets_categories_query->result_array() as $assets_categories_row){
                $assets_category = array();
                $assets_category["assets_categories_id"] = $assets_categories_row["id"];
                $assets_category["assets_id"] = $assets_categories_row["assets_id"];
                $assets_category["categories_id"] = $assets_categories_row["categories_id"];
                $data["assets_categories"][] = $assets_category;
            }
            
            /* Assets Department */
            $this->db->where_in("assets_id", $assets_id);
            $assets_departments_query = $this->db->get("assets_departments");
            
            foreach($assets_departments_query->result_array() as $assets_departments_row){
                $assets_department = array();
                $assets_department["assets_departments_id"] = $assets_departments_row["id"];
                $assets_department["assets_id"] = $assets_departments_row["assets_id"];
                $assets_department["departments_id"] = $assets_departments_row["departments_id"];
                $assets_department["quantity"] = $assets_departments_row["quantity"];
                $assets_department["location"] = $assets_departments_row["location"];
                $data["assets_departments"][] = $assets_department;
                
                $assets_departments_id[] = $assets_departments_row["id"];
            }
            
            /* Assets Department Loan */
            if(count($assets_departments_id) > 0){
                $this->db->where_in("assets_departments_id", $assets_departments_id);
                $assets_departments_loan = $this->db->get("assets_departments_loan");
                
                foreach($assets_departments_loan->result_array() as $assets_departments_loan_row){
                    
                    $assets_departments_loan_record = array();
                    $assets_departments_loan_record["assets_departments_loan_id"] = $assets_departments_loan_row["id"];
                    $assets_departments_loan_record["assets_departments_id"] = $assets_departments_loan_row["assets_departments_id"];
                    $assets_departments_loan_record["quantity"] = $assets_departments_loan_row["quantity"];
                    $assets_departments_loan_record["datetime_created"] = $assets_departments_loan_row["datetime_created"];
                    $data["assets_departments_loan"][] = $assets_departments_loan_record;
                }
            }
            
            /* Assets Maintenance */
            $this->db->where_in("assets_id", $assets_id);
            $assets_maintenance_query = $this->db->get("assets_maintenance");
            
            foreach($assets_maintenance_query->result_array() as $assets_maintenance_row){
                $assets_maintenance = array();
                $assets_maintenance["assets_maintenance_id"] = $assets_maintenance_row["id"];
                $assets_maintenance["assets_id"] = $assets_maintenance_row["assets_id"];
                $assets_maintenance["maintenance_date"] = $assets_maintenance_row["maintenance_date"];
                $data["assets_maintenance"][] = $assets_maintenance;
            }
        }
        
        return $data;
    }
}
