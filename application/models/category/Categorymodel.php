<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Categorymodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get category list
     *
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getCategories($parameters){
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $data = $this->db->get("categories");
        
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
     * Get all categories
     *
     * 
     * */
    function getAllCategories(){
        
        $data = array();
        $query = $this->db->get("categories");
        
        foreach($query->result_array() as $row){
            $data[$row["id"]] = $row;
        }
        return $data;
    }
    
    /* 
     * Add category
     *
     * $parameters["categories_name"]
     * $parameters["lifespan_default"]
     * $parameters["tracking_default"]
     * 
     * */
    function addCategory($parameters){
                
        $insert = array();
        $insert["categories_name"] = $parameters["categories_name"];
        $insert["lifespan_default"] = $parameters["lifespan_default"];
        $insert["tracking_default"] = $parameters["tracking_default"];
        $insert["datetime_created"] = date("Y-m-d H:i:s");   
        
        $this->db->insert("categories", $insert);
    }
    
    /* 
     * Update category
     *
     * $parameters["categories_id"] 
     * $parameters["categories_name"]
     * $parameters["lifespan_default"]
     * $parameters["tracking_default"] 
     * 
     * */
    function updateCategory($parameters){
            
        $update = array();
        $update["categories_name"] = $parameters["categories_name"];
        $update["lifespan_default"] = $parameters["lifespan_default"];
        $update["tracking_default"] = $parameters["tracking_default"];    
        
        $this->db->where("id", $parameters["categories_id"]);
        $this->db->update("categories", $update);
    }
    
    /* 
     * Delete category and set related assets to no category
     *
     * $parameters["categories_id"] 
     * 
     * */
    function deleteCategory($parameters){
        $this->db->delete("categories", array("id" => $parameters["categories_id"]));
        $this->db->delete("assets_categories", array("categories_id" => $parameters["categories_id"]));
    }
    
    function isCategoryIDExist($categories_id){
        $query = $this->db->get_where("categories", array("id" => $categories_id));
        
        return !!$query->num_rows();
    }
}
