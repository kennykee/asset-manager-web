<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rolemodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get role list without filter
     *
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getRoles($parameters){
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $data = $this->db->get("roles");
        
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
     * Get role by users ID list
     *
     * $parameters["users"] = array(); //- refers to list users ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getRolesByUsers($parameters){
        
        $data = array();
                
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->select("SQL_CALC_FOUND_ROWS *, users_roles.users_id AS users_id, users_roles.id AS users_roles_id", FALSE);
        $this->db->where_in("users_roles.users_id", $parameters["users"]);
        $this->db->from("roles");
        $this->db->join("users_roles", "users_roles.roles_id = roles.id", "inner");
        
        $query = $this->db->get();
        
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
            $data[$row["users_id"]][] = $row;
        }
        
        return $data;
    }
    
    /* 
     * Get users by roles
     *
     * $parameters["roles"] = array();
     * 
     * */
    function getAllUsersByRoles($parameters){
            
        $this->db->where_in("roles_id", $parameters["roles"]);                
        $query = $this->db->get("users_roles");    
        
        return $query->result_array();
    }
    
    /* 
     * Get all roles, optionally add-in attached users roles
     *
     * $parameters["users"] = array(); //Optional-
     * 
     * */
    function getAllRoles($parameters = array()){
        
        $data = array();
        $users_roles_data = array();    
        
        if(isset($parameters["users"]) && $parameters["users"]){
            $this->db->where_in("users_id", $parameters["users"]);
            $users_roles_query = $this->db->get("users_roles");
            foreach($users_roles_query->result_array() as $users_roles){
                $users_roles_data[$users_roles["roles_id"]][] = $users_roles["users_id"];
            }
        }
        
        $this->db->order_by("roles_name", "ASC");
        $query = $this->db->get("roles");
        
        foreach($query->result_array() as $role){
            $role["users_id"] = isset($users_roles_data[$role["id"]])? $users_roles_data[$role["id"]] : array();
            $data[$role["id"]] = $role;   
        }
        
        return $data;
    }
    
    /* 
     * Get all functions, optionally add-in attached roles
     *
     * $parameters["roles"] = array(); //Optional-
     * 
     * */
    function getAllFunctions($parameters = array()){
        
        $data = array();
        $roles_data = array();    
        
        if(isset($parameters["roles"]) && $parameters["roles"]){
            $this->db->where_in("roles.id", $parameters["roles"]);
            $this->db->join("roles_functions", "roles_functions.roles_id = roles.id");
            $this->db->join("roles_functions_parameter", "roles_functions_parameter.roles_functions_id = roles_functions.id");
            $roles_query = $this->db->get("roles");
            foreach($roles_query->result_array() as $role){
                $roles_data[$role["functions_id"]][$role["roles_id"]][] = $role;
            }
        }
        
        $query = $this->db->get("functions");
        
        foreach($query->result_array() as $function){
            $function["roles_id"] = isset($roles_data[$function["id"]])? $roles_data[$function["id"]] : array();
            $data[$function["id"]] = $function;   
        }
        
        return $data;
    }
    
    
    /* 
     * Add role
     *
     * $parameters["roles_name"]
     * 
     * */
    function addRole($parameters){
        
        $insert_parameters = array();
        $insert_parameters["roles_name"] = $parameters["roles_name"];
        $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");        
        
        $this->db->insert("roles", $insert_parameters);
        
        return $this->db->insert_id();
    }
    
    /* 
     * Update role permission
     *
     * $parameters["roles_id"]
     * $parameters["parameters"] = array(array())
     * 
     * */
    function updatePermission($parameters){
        
        /* Remove existing */
        $this->db->join("roles_functions_parameter", "roles_functions.id = roles_functions_parameter.roles_functions_id", "inner");
        $this->db->where("roles_functions.roles_id", $parameters["roles_id"]);
        $this->db->delete("roles_functions");
        
        /* Reinsert */
        foreach($parameters["parameters"] as $functions_id => $parameter_list){
                
            if(count($parameter_list) > 0){
                
                $this->db->insert("roles_functions", array("roles_id" => $parameters["roles_id"], "functions_id" => $functions_id, "datetime_created" => date("Y-m-d H:i:s")));
                
                $roles_functions_id = $this->db->insert_id();
                
                foreach($parameter_list as $param){
                    
                    $this->db->insert("roles_functions_parameter", array("roles_functions_id" => $roles_functions_id, "parameter" => $param, "datetime_created" => date("Y-m-d H:i:s")));
                }   
            }
        }
    }
    
    /* 
     * Update role
     *
     * $parameters["roles_id"]
     * $parameters["roles_name"]
     * 
     * */
    function updateRole($parameters){
        $this->db->where("id", $parameters["roles_id"]);
        $this->db->update("roles", array("roles_name" => $parameters["roles_name"]));
    }
    
    /* 
     * Delete role
     *
     * $parameters["roles_id"]
     * 
     * */
    function deleteRole($parameters){
        $this->db->join("users_roles", "users_roles.roles_id = roles.id", "inner");
        $this->db->join("roles_functions", "roles.id = roles_functions.roles_id", "inner");
        $this->db->join("roles_functions_parameter", "roles_functions.id = roles_functions_parameter.roles_functions_id", "inner");
        $this->db->where("roles.id", $parameters["roles_id"]);
        $this->db->delete("roles");
    }
    
    /* 
     * Get user ID from users_roles_id
     *
     * $parameters["users_roles_id"]
     * 
     * */
    function getUserFromRoleLink($parameters){
        
        $query = $this->db->get_where("users_roles", array("id" => $parameters["users_roles_id"]));
        
        if($query->num_rows() > 0){
            $row = $query->row();
            return $row->users_id;
        }
        
        return FALSE;
    }
    
    /* 
     * Remove role link
     *
     * $parameters["users_roles_id"]
     * 
     * */
    function removeRoleFromUser($parameters){
        $this->db->delete("users_roles", array("id" => $parameters["users_roles_id"]));
    }
    
    /* 
     * Replace role link
     *
     * $parameters["users_id"]
     * $parameters["roles_id"] = array();
     * 
     * */
    function replaceRolesOfUser($parameters){
        /* Delete Existing */
        $this->db->delete("users_roles", array("users_id" => $parameters["users_id"]));
        
        /* Reinsert */
        if(is_array($parameters["roles_id"])){
            $roles_id = array_filter($parameters["roles_id"], 'is_scalar');
            
            foreach($roles_id as $roles){
                $this->db->insert("users_roles", array("users_id" => $parameters["users_id"], "roles_id" => $roles, "datetime_created" => date("Y-m-d H:i:s")));
            } 
        }
    }
    
    /* 
     * Replace role link
     *
     * $parameters["roles_id"]
     * $parameters["users_id"] = array();
     * 
     * */
    function replaceUsersOfRole($parameters){
        /* Delete Existing */
        $this->db->delete("users_roles", array("roles_id" => $parameters["roles_id"]));
        
        /* Reinsert */
        if(is_array($parameters["users_id"])){
            $users_id = array_filter($parameters["users_id"], 'is_scalar');
            
            foreach($users_id as $users){
                $this->db->insert("users_roles", array("users_id" => $users, "roles_id" => $parameters["roles_id"], "datetime_created" => date("Y-m-d H:i:s")));
            } 
        }
    }
}
