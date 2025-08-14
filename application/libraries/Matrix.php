<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Matrix {
    
    public function __construct(){
        //Silence is golden
    }
    
    /* Check Access Matrix
     * $parameter is an integer. Checking on single parameter only.
     * */
    function checkSingleAccess($uri, $parameter){
        
        $CI =& get_instance();
        
        $access_matrix = $CI->cache_user["access_matrix"];
        
        if(isset($access_matrix[$uri])){
            $access = $access_matrix[$uri];
            $access_parameters = $access["parameters"];
            
            if (in_array($parameter, $access_parameters) || in_array("*", $access_parameters)) { // * represents full access
                return TRUE; 
            }
        }
        return FALSE;
    }
    
    /* Check Access Matrix. Return true if at least one parameter in the array match
     * $parameter = array(); // Parameter consists of an array of integer
     * */
    function checkMultiAccess($uri, $parameter){
        
        $CI =& get_instance();
        
        $access_matrix = $CI->cache_user["access_matrix"];
        
        if(isset($access_matrix[$uri])){
            $access = $access_matrix[$uri];
            $access_parameters = $access["parameters"];
            
            if(in_array("*", $access_parameters)){ // * represents full access
                return TRUE;
            }
            
            foreach($parameter as $param){
                if(in_array($param, $access_parameters)){
                    return TRUE;
                }
            }
        }
        
        return FALSE;
    }
    
    /* Add checkMinimumAccess - at least one parameter regardless of what parameter */
    function checkMinimumAccess($uri){
        $CI =& get_instance();
        
        $access_matrix = $CI->cache_user["access_matrix"];
        
        if(isset($access_matrix[$uri])){
            return TRUE; 
        }
        return FALSE;
    }
    
    /*
     * Check if id is in the dependency access array.
     * */
    function inAccessArray($access_id){
        $CI =& get_instance();
        
        $access_array = $CI->access_array;
        
        if (in_array($access_id, $access_array) || in_array("*", $access_array)) { // * represents full access
            return TRUE; 
        }
        
        if (is_array($access_id) && (count(array_diff($access_id, $access_array)) == 0)){
            return TRUE;
        }
        
        return FALSE;
    }
}
