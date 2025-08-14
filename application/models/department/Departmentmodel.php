<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Departmentmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get department list without filter
     *
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getDepartments($parameters){
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $data = $this->db->get("departments");
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->build();
        
        return $data->result_array();
    }
    
    /* 
     * Get all department
     *
     * 
     * */
    function getAllDepartment(){
        
        $data = array();    
        $query = $this->db->get("departments");
        
        foreach($query->result_array() as $row){
            $data[$row["id"]] = $row;
        }
        
        return $data;
    }
    
    
    /* 
     * Add department
     *
     * $parameters["departments_name"] 
     * 
     * */
    function addDepartment($parameters){
        $this->db->insert("departments", array("departments_name" => $parameters["departments_name"], "datetime_created" => date("Y-m-d H:i:s")));
    }
    
    /* 
     * Update department name
     *
     * $parameters["departments_id"] 
     * $parameters["departments_name"] 
     * 
     * */
    function updateDepartment($parameters){
        $this->db->where("id", $parameters["departments_id"]);
        $this->db->update("departments", array("departments_name" => $parameters["departments_name"]));
    }
    
    /* 
     * Delete department and set related assets to no department
     *
     * $parameters["departments_id"] 
     * 
     * */
    function deleteDepartment($parameters){
        $this->db->delete("departments", array("id" => $parameters["departments_id"]));
        
        $this->db->where("departments_id", $parameters["departments_id"]);
        $this->db->update("assets_departments", array("departments_id" => 0));
    }
    
    /* 
     * Get a list of departments by department ID list accessible
     * Do not use for normal department retrieval
     *
     * $parameters["access_array"] = array() 
     * 
     * */
    function getDepartmentsByAccessArray($parameters){
        $data = array();
        
        if(count($parameters["access_array"]) > 0){
            
            if(!in_array("*", $parameters["access_array"])){
                $this->db->where_in("id", $parameters["access_array"]);    
            }
            
            $this->db->order_by("departments_name", "asc");
            $query = $this->db->get("departments");    
            
            foreach($query->result() as $row){
                $data[$row->id] = $row->departments_name;
            }
        }
        
        return $data;
    }
    
    function isDepartmentIDExist($departments_id){
        $query = $this->db->get_where("departments", array("id" => $departments_id));
        
        return !!$query->num_rows();
    }
}
