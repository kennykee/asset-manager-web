<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Configmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get config list
     *
     * */
    function getConfigs(){
        $this->db->order_by('group_name', 'asc');
        $this->db->order_by('config_label', 'id');
        $query = $this->db->get("config");
        return $query->result_array();
    }
    
    /* 
     * Update configuration
     *
     * $parameters["configs"] = array(); 
     * 
     * */
    function updateConfig($parameters){
        
        foreach($parameters["configs"] as $key=>$val){
            $this->db->where("config_key", $key);
            $this->db->update("config", array("config_value" => $val));       
        }
    }
}
