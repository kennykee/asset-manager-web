<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Authmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Check match for login username and password
     * 
     * $parameters["username"]
     * $parameters["users_password"]
     * */
    function checkLogin($parameters){
        
        $query = $this->db->get_where("users", array("username" => $parameters["username"], "users_password" => hash("md5", $parameters["users_password"])));
        if($query->num_rows() > 0){
            return $query->row_array();
        }
        
        return FALSE;
    }
    
    /* 
     * Update user login information
     * 
     * $parameters["users_id"]
     * */
    function updateLoginInfo($parameters){

        $this->db->update("users", array("web_login_datetime" => date("Y-m-d H:i:s"), "web_login_ip" => $this->input->ip_address()), array("id" => $parameters["users_id"]));
        
    }
    
    /* 
     * Get user access matrix 
     * 
     * $parameters["users_id"]
     * */
    function getMyAccess($parameters){
        
        $data = array();
        
        $this->db->select("*");
        $this->db->from("users_roles");
        $this->db->join("roles", "users_roles.roles_id = roles.id", "inner");
        $this->db->join("roles_functions", "roles.id = roles_functions.roles_id", "inner");
        $this->db->join("roles_functions_parameter", "roles_functions.id = roles_functions_parameter.roles_functions_id", "inner");
        $this->db->where("users_roles.users_id", $parameters["users_id"]);
        $access_query = $this->db->get();
        
        if($access_query->num_rows() > 0){
            $function_ids = array();
            $functions_parameters = array();
            
            foreach($access_query->result() as $row){
                $function_ids[] = $row->functions_id;
                $functions_parameters[$row->functions_id][] = $row->parameter;
            }
                
            /* Get Function List */
            $this->db->select("*, functions.id AS id");
            $this->db->from("functions");
            $this->db->where_in("functions.id", $function_ids);
            $this->db->order_by("menu_order", "desc");
            $this->db->join("functions_controller", "functions.id = functions_controller.functions_id", "inner");
            $functions_query = $this->db->get();
            
            foreach($functions_query->result() as $row){
                $inner = array();
                $inner["functions_id"] = $row->id;
                $inner["is_menu"] = $row->is_menu;
                $inner["display_name"] = $row->display_name;
                $inner["lang_key"] = $row->lang_key;
                $inner["icon_class"] = $row->icon_class;
                $inner["canonical_name"] = $row->canonical_name;
                $inner["module_name"] = $row->module_name;
                $inner["function_group_name"] = $row->function_group_name;
                $inner["access_dependencies"] = $row->access_dependencies;
                $inner["operation"] = $row->operation;
                $inner["menu_order"] = $row->menu_order;
                $inner["parameters"] = isset($functions_parameters[$row->id])? $functions_parameters[$row->id] : array();
                
                $uri = trim($row->uri, '/');
                $data[$uri] = $inner;
            }    
        }

        return $data;
    }
    
    /* 
     * Construct menu bar
     * 
     * $parameters - Access matrix array object
     * */
    function getMenu($parameters){

        $data = array();
        foreach($parameters as $key=>$param){
                
            if($param["is_menu"] == "1"){    
                $function = array();
                $function["uri"] = $key;
                $function["display_name"] = $param["display_name"];
                $function["lang_key"] = $param["lang_key"];
                $function["icon_class"] = $param["icon_class"];
                $function["function_group_name"] = $param["function_group_name"];
                $data[$param["module_name"]]["functions"][] = $function;
            }
        }
        
        /* Assign module name and link using first entry of each module*/
        $routes_uri = $this->config->item("routes_uri");
        
        foreach($data as $key=>&$param){
            $functions = $param["functions"];
            $param["uri"] = isset($functions[0]["uri"])? $functions[0]["uri"] : $routes_uri["dashboard"];
        }
        
        return $data; 
    }
    
    /* 
     * Reconstruct CU access matrix
     * 
     * $users_id
     * */
    function reconstructCUAccessMatrix($users_id, $new_cache_file = FALSE){
        
        $users_id = trim($users_id);
        
        $cache_user = $this->cache->file->get('cu-' . $users_id);
        
        if($cache_user || $new_cache_file){
            
            $parameters = array();
            $parameters["users_id"] = $users_id;
            $access_matrix = $this->getMyAccess($parameters);
            
            $this->load->model('user/usermodel');
            
            $users_record = $this->usermodel->getUserByUserID($users_id);
            
            $new_cache_user = array();
            $new_cache_user["access_matrix"] = $access_matrix;
            $new_cache_user["menu"] = $this->getMenu($access_matrix);
            $new_cache_user["api_access"] = hash("md5", $users_id . "::" . $users_record["api_key"]); 
            $new_cache_user["status"] = $users_record["status"];    
        
            $this->cache->file->save('cu-' . $users_id, $new_cache_user, 100000000);
        }
    }
    
}
