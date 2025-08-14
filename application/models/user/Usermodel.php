<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Usermodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get user list without filter
     *
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getUsers($parameters){
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $data = $this->db->get("users");
        
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
     * Update user
     *
     * $parameters["users_id"] 
     * $parameters["person_name"]
     * $parameters["username"] 
     * $parameters["users_password"] = optional. Omit if FALSE.
     * $parameters["email"]
     * $parameters["api_key"] = optional. Omit if FALSE.
     * $parameters["status"]
     * 
     * */
    function updateUser($parameters){
        
        $update_parameters = array();
        $update_parameters["person_name"] = $parameters["person_name"];
        $update_parameters["username"] = $parameters["username"];
        $update_parameters["email"] = $parameters["email"];
        $update_parameters["status"] = $parameters["status"];
        
        if(isset($parameters["users_password"]) && $parameters["users_password"]){
            $update_parameters["users_password"] = hash("md5", $parameters["users_password"]);        
        }
        
        if(isset($parameters["api_key"])){
            $update_parameters["api_key"] = hash("md5", $parameters["api_key"]);        
        }
        
        $this->db->where("id", $parameters["users_id"]);
        $this->db->update("users", $update_parameters);
    }
    
    /* 
     * Add user
     *
     * $parameters["person_name"]
     * $parameters["username"] 
     * $parameters["users_password"]
     * $parameters["email"]
     * $parameters["api_key"]
     * $parameters["status"]
     * 
     * */
    function addUser($parameters){
        
        $insert_parameters = array();
        $insert_parameters["person_name"] = $parameters["person_name"];
        $insert_parameters["username"] = $parameters["username"];
        $insert_parameters["email"] = $parameters["email"];
        $insert_parameters["status"] = $parameters["status"];
        $insert_parameters["users_password"] = hash("md5", $parameters["users_password"]);        
        $insert_parameters["api_key"] = hash("md5", $parameters["api_key"]);
        $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");        
        
        $this->db->insert("users", $insert_parameters);
        
        return $this->db->insert_id();
    }
    
    /* 
     * Get all users, optionally add-in attached roles
     *
     * $parameters["roles"] = array(); //Optional-
     * 
     * */
    function getAllUsers($parameters = array()){
            
        $data = array();    
        
        $roles_users_data = array();    
        
        if(isset($parameters["roles"]) && $parameters["roles"]){
            $this->db->where_in("roles_id", $parameters["roles"]);
            $roles_users_query = $this->db->get("users_roles");
            foreach($roles_users_query->result_array() as $roles_users){
                $roles_users_data[$roles_users["users_id"]][] = $roles_users["roles_id"];
            }
        }
        
        $this->db->order_by("person_name", "asc");
        $query = $this->db->get("users");
        
        foreach($query->result_array() as $user){
            $user["roles_id"] = isset($roles_users_data[$user["id"]])? $roles_users_data[$user["id"]] : array();
            $data[$user["id"]] = $user;   
        }
        
        return $data;
    }
    
    function isUserIDExist($users_id){
        $query = $this->db->get_where("users", array("id" => $users_id));
        
        return !!$query->num_rows();
    }
    
    function getUserByUsername($username){
        $query = $this->db->get_where("users", array("username" => $username));
        
        if($query->num_rows() > 0){
            return $query->row();
        }        
        return FALSE;
    }
    
    function getUserByUserID($users_id){
        $query = $this->db->get_where("users", array("id" => $users_id));
        
        if($query->num_rows() > 0){
            return $query->row_array();
        }        
        return FALSE;
    }
    
    /* 
     * Get user by roles ID list
     *
     * $parameters["roles"] = array(); //- refers to list roles ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * 
     * */
    function getUsersByRoles($parameters){
        
        $data = array();
                
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->select("SQL_CALC_FOUND_ROWS *, users_roles.roles_id AS roles_id, users_roles.id AS users_roles_id", FALSE);
        $this->db->where_in("roles.id", $parameters["roles"]);
        $this->db->from("roles");
        $this->db->join("users_roles", "users_roles.roles_id = roles.id", "inner");
        $this->db->join("users", "users_roles.users_id = users.id", "inner");
        
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
            $data[$row["roles_id"]][] = $row;
        }
        
        return $data;
    }
    
    /* 
     * Get all users accessible to a function and optionally filtered by access array (departments_id)
     * - Group by department id / access array
     * 
     * $parameters["uri"] 
     * $parameters["access_array"] = array(); //Optional
     * 
     * */
    function getAccessibleUsersByRoute($parameters){
        
        $data = array();    
            
        $uri_query = $this->db->query("SELECT B.functions_id as functions_id FROM functions_controller A INNER JOIN functions_controller B ON 
                          A.access_dependencies = B.uri WHERE A.uri = " . $this->db->escape($parameters["uri"]));
        if($uri_query->num_rows() > 0){
            $uri_row = $uri_query->row();
            $functions_id = $uri_row->functions_id;
            
            $this->db->select("*");
            $this->db->from("users_roles");
            $this->db->join("roles", "users_roles.roles_id = roles.id", "inner");
            $this->db->join("roles_functions", "roles.id = roles_functions.roles_id", "inner");
            $this->db->join("roles_functions_parameter", "roles_functions.id = roles_functions_parameter.roles_functions_id", "inner");
            $this->db->where("functions_id", $functions_id);
            
            if($parameters["access_array"]){
                $this->db->group_start()
                         ->where_in("parameter", $parameters["access_array"])
                         ->or_where("parameter", "*")
                         ->group_end();
            }
            
            $roles_query = $this->db->get();
            
            $users_id = array();
            $users_access_data = array();
            
            foreach($roles_query->result_array() as $role){
                $users_id[] = $role["users_id"];
                $users_access_data[$role["users_id"]][] = $role;
            } 
            
            if(count($users_id) > 0){
                $this->db->select("id,person_name,email");
                $this->db->where("status","1");
                $this->db->where_in("id", $users_id);
                $users_query = $this->db->get("users");
                
                foreach($users_query->result_array() as $users_row){
                    $user_data = array();
                    $user_data["users_id"] = $users_row["id"];
                    $user_data["person_name"] = $users_row["person_name"];
                    $user_data["email"] = $users_row["email"];    
                    $user_data["access_id"] = array();
                    
                    if(isset($users_access_data[$user_data["users_id"]])){
                        $access_data = $users_access_data[$user_data["users_id"]];
                        foreach($access_data as $access){
                            $user_data["access_id"][] = $access["parameter"];   
                        }
                    }
                    $user_data["access_id"] = array_unique($user_data["access_id"]);
                    $data[$users_row["id"]] = $user_data;
                }   
            }
        }
        
        return $data;
    }
    
    /* 
     * Change user password
     *
     * $parameters["users_id"] 
     * $parameters["new_password"]
     * 
     * */
    function changePassword($parameters){
        $this->db->where("id", $parameters["users_id"]);
        $this->db->update("users", array("users_password" => hash("md5", $parameters["new_password"])));                
    }
    
    /* 
     * Regenerate API
     *
     * $parameters["users_id"] 
     * 
     * */
    function regenerateAPI($parameters){
        $this->db->where("id", $parameters["users_id"]);
        $this->db->update("users", array("api_key" => random_string("alnum", 32)));                
    }
    
    function isPasswordMatchCurrentUser($password){
                
        $user = $this->getUserByUserID($this->session->userdata("users_id"));
            
        if($user){
            if(hash("md5", $password) == $user["users_password"]){
                return TRUE;
            }
        }
        return FALSE;
    }
    
}
