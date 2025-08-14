<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends MY_Controller {
    
    /* Use config function label */
    /* Use sub-controller to overwrite active */
    /* All user menu, active module and active function are lower case*/
    
    public $sub_menu_string = "";
    public $active_module = "dashboard"; 
    public $active_function = "";
    public $active_function_label = "";
    public $access_array = array();
    public $cache_user = array();
    
	function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->driver('cache');
        $this->config->load('config-webapp');
        $this->_verifyAuthorization();
        $this->_loadCache();
        $this->_verifyStatus();
        $this->_verifyAccessMatrix();
        $this->_constructSubMenu();
        $this->_setResponseCode();
        $this->load->library('pagination_output');
        $this->load->library('matrix');
    }
	
    function _verifyAuthorization(){
        if(!$this->session->userdata('users_id')){
            redirect(site_url($this->config->item("login", "routes_uri")), 'refresh');     
        }
    }
    
    function _verifyStatus(){
        $status = $this->cache_user["status"];
        
        if($status == "0"){
            $this->session->unset_userdata('users_id');
            $this->session->unset_userdata('person_name');
            $this->session->sess_destroy();
            redirect(site_url($this->config->item("login", "routes_uri")), 'refresh');
        }
    }
    
    function _loadCache(){
        $this->cache_user = $this->cache->file->get('cu-' . $this->session->userdata('users_id'));
    }
    
    function _constructSubMenu(){
            
        $menu = $this->cache_user["menu"];
        
        if(isset($menu[$this->active_module])){
            $sub_menu = $menu[$this->active_module]["functions"];
            
            $this->sub_menu_string = '<div class="col-sm-3 col-md-2 sidebar">
                                        <ul class="nav nav-sidebar">';
                                        
            foreach($sub_menu as $sub){
                $this->sub_menu_string .= '<li ' . (($this->active_function==$sub["function_group_name"])? 'class="active"' : '') . '>';
                $this->sub_menu_string .=   '<a href="' . site_url($sub["uri"]) . '">' . ucwords($sub["display_name"]) . '</a>';
                $this->sub_menu_string .= '</li>';   
            }                            
                                      
            $this->sub_menu_string .= '</ul></div>';
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
        
        /*  Exception List - Ability to logged-on will have the following 2 accesses
         *  Includes: Dashboard, Logout
         */
         if($uri == $this->config->item("dashboard", "routes_uri") || 
            $uri == $this->config->item("dashboard_change_password", "routes_uri") ||
            $uri == $this->config->item("dashboard_regenerate_api", "routes_uri")){
                $this->active_module = "dashboard";
                $this->active_function_label = "Dashboard";
                return TRUE;
         }else if($uri == $this->config->item("logout", "routes_uri")){
                return TRUE;
         }
        
        /* Check Access Matrix */
        
        $access_matrix = $this->cache_user["access_matrix"];
        
        if(isset($access_matrix[$uri])){
            $access = $access_matrix[$uri];
            $access_parameters = $access["parameters"];
            
            if (in_array($parameter, $access_parameters) || in_array("*", $access_parameters)) { // * represents full access
                $this->active_module = $access["module_name"];
                $this->active_function = $access["function_group_name"];
                $this->active_function_label = $access["display_name"];;
                $this->_constructAccessArray($access["access_dependencies"]);
                return TRUE; 
            }
        }
        
        $this->_showAcessDenied();
    }
    
    function _showAcessDenied(){
        $this->session->set_flashdata('error', 'Access Denied. You have no permission to access this function.');
        redirect($this->config->item("dashboard", "routes_uri"), "auto");
        
        /* Check if ajax, return ajax response.
         * 
         *  if($this->input->is_ajax_request()){
         *      //Execute Your Code
         *  }
         * 
         * */       
         exit();
    }
    
    function _setResponseCode(){
        if($this->session->flashdata('response_code')){
            $this->output->set_status_header($this->session->flashdata('response_code'), $this->session->flashdata('error'));
        }
    }
    
}