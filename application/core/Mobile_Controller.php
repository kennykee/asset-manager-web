<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile_Controller extends MY_Controller {
    
    public $users_id = FALSE;
    public $access_array = array();
    public $cache_user = array();
    public $api_version = "";
    
	function __construct()
    {
        parent::__construct();
        $this->load->driver('cache');
        $this->config->load('config-mobileapp');
        $this->_verifyAuthorization();
        $this->_verifyStatus();
        $this->_verifyAccessMatrix();
        $this->load->library('matrix');
        /* Include version check */
    }
    
    function _verifyAuthorization(){
        
        $header = $this->input->get_request_header('X-Authorization', TRUE);
        $api_version = $this->input->get_request_header('X-API-Version', TRUE);
        
        if($header){
            
            $header_data = explode("::", $header);
            
            if($header_data && (count($header_data) == 2)){
                
                $users_id = $header_data[0];
                $api_key = $header_data[1];
                
                $cache_file = $this->cache->file->get('cu-' . $users_id);
                
                if($cache_file && ($cache_file["api_access"] == hash("md5", $header))){
                    $this->cache_user = $cache_file;
                    $this->users_id = $users_id;
                    $this->api_version = $api_version;
                    return TRUE;
                }        
            }
        }
        $this->_showAcessDenied();
    }
    
    function _verifyStatus(){
        $status = $this->cache_user["status"];
        
        if($status == "0"){
            $this->_showAcessDenied();
        }
    }
    
    
   /* Exception to Dashboard and Logout 
    * - Required for implementation specified function
    * */
    function _constructAccessArray($dependable_uri){
        
        $access_matrix = $this->cache_user["access_matrix"];
        if(isset($access_matrix[$dependable_uri])){
            $access = $access_matrix[$dependable_uri];
            $this->access_array = $access["parameters"];
        }
    }
    
    function _verifyAccessMatrix(){
        
        $uri_array = $this->uri->segment_array();
        
        /* Check last parameter */
        $parameter = "*";
        
        if(is_numeric(end($uri_array))){
            $parameter = array_pop($uri_array);
        }
        
        $uri = implode('/', $uri_array);
        
        /* Check Access Matrix */
        
        $access_matrix = $this->cache_user["access_matrix"];
        
        if(isset($access_matrix[$uri])){
            $access = $access_matrix[$uri];
            $access_parameters = $access["parameters"];
            
            if (in_array($parameter, $access_parameters) || in_array("*", $access_parameters)) { // * represents full access
                $this->_constructAccessArray($access["access_dependencies"]);
                return TRUE; 
            }
        }
        
        $this->_showAcessDenied();
    }
    
    function _showAcessDenied(){
        $this->json_output->setInvalidCredential();
        $this->json_output->print_json();
        $this->output->_display();
        exit();
    }
}