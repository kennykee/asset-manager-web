<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Alertrecipientmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get recipients by departments ID list
     *
     * $parameters["departments"] = array(); //- refers to list department ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getRecipientsByDepartments($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where_in("departments_id", $parameters["departments"]);
        
        $query = $this->db->get("recipients");
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->build();
        
        foreach($query->result_array() as $row){
            $data[$row["departments_id"]][] = $row;
        }
        
        return $data;
    }

    /* 
     * Add recipient
     *
     * $parameters["email"]
     * $parameters["departments_id"] 
     * 
     * */
    function addAlertRecipient($parameters){
        $this->db->insert("recipients", array("email" => $parameters["email"], "departments_id" => $parameters["departments_id"], "datetime_created" => date("Y-m-d H:i:s")));
    }    
    
    /* 
     * Delete recipient
     *
     * $parameters["recipients_id"]
     * 
     * */
    function deleteAlertRecipient($parameters){
        $this->db->delete("recipients", array("id" => $parameters["recipients_id"]));
    }
    
    function isRecipientAccessible($recipients_id){ /* Access matrix accessible */
        
        $query = $this->db->get_where("recipients", array("id" => $recipients_id));
        
        if($query->num_rows() > 0){
            
            $row = $query->row();
            
            if($this->matrix->inAccessArray($row->departments_id)){
                return TRUE;
            }
        }
        return FALSE;
    }
    
}
