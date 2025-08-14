<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Trackmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /*
     *  $parameters["users_id"]
     *  $parameters["data"]
     */ 
    function uploadTrack($parameters){
        
        /* Return upload record id*/
        $record_id = array();
            
        foreach($parameters["data"] as $insert){
                
            $insert_parameters = array();
            $insert_parameters["assets_id"] = $insert["assets_id"];
            $insert_parameters["departments_id"] = $insert["departments_id"];
            $insert_parameters["datetime_scanned"] = $insert["datetime_scanned"];
            $insert_parameters["quantity"] = $insert["quantity"];
            $insert_parameters["remark"] = $insert["remark"];
            $insert_parameters["terminal_record_id"] = $insert["terminal_record_id"];
            $insert_parameters["users_id"] = $parameters["users_id"];
            $insert_parameters["terminal_id"] = $insert["terminal_id"];
            $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
        
            $this->db->insert("assets_tracking", $insert_parameters);
            
            $record_id[] = $insert["terminal_record_id"];
        }
        
        return $record_id;   
    }
}
