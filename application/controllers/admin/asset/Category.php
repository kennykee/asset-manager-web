<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends Admin_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('category/categorymodel');
    } 
    
    public function index()
    {
        $data = array();
        
        $parameters = array();
        $parameters["page_no"] = 1;
        $parameters["count_per_page"] = 100;
        $parameters["sort"] = "asc";
        $parameters["sort_field"] = "categories_name";
        
        $fields = array("categories_name"=>1);
            
        if(is_natural_number($this->input->get("page_no", TRUE))){
            $parameters["page_no"] = $this->input->get("page_no", TRUE);
        }
        if(is_natural_number($this->input->get("count_per_page", TRUE))){
            $parameters["count_per_page"] = $this->input->get("count_per_page", TRUE);
        }
        if(is_sort_order($this->input->get("sort", TRUE))){
            $parameters["sort"] = strtolower($this->input->get("sort", TRUE));
        }
        if(isset($fields[strtolower($this->input->get("sort_field", TRUE))])){
            $parameters["sort_field"] = strtolower($this->input->get("sort_field", TRUE));    
        }    
        
        $data["categories"] = $this->categorymodel->getCategories($parameters);
        
        $this->load->view('admin/common/header', $data);
        $this->load->view('admin/category/category', $data);
        $this->load->view('admin/common/footer', $data);
    }
       
    public function updateCategory($categories_id){
         
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["categories_id"] = $categories_id;
             
            $this->form_validation->set_rules('categories_id', 'Categories ID', array('trim','xss_clean','required','is_natural_no_zero', array("categories_exist", array($this->categorymodel, "isCategoryIDExist"))), array("categories_exist" => "Category selected doesn't exist")); 
            $this->form_validation->set_rules('categories_name', 'Category Name', 'trim|xss_clean|strip_tags|required|min_length[1]');
            $this->form_validation->set_rules('lifespan_default', 'Default Lifespan', 'trim|xss_clean|required|is_natural', array("is_natural_no_zero"=>"Please enter number only. If there is no lifespan, enter 0"));
            $this->form_validation->set_rules('tracking_default', 'Tracking Status', 'trim|xss_clean|required|in_list[0,1]', array("in_list" => "Invalid data received. Please contact administrator."));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["categories_id"] = $this->input->post("categories_id", TRUE);
                $parameters["categories_name"] = $this->input->post("categories_name", TRUE);
                $parameters["lifespan_default"] = $this->input->post("lifespan_default", TRUE);
                $parameters["tracking_default"] = $this->input->post("tracking_default", TRUE);
                
                $this->categorymodel->updateCategory($parameters);
                
                $this->session->set_flashdata('success', "Category updated.");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }    
    
    public function addCategory(){
         
        if($this->input->server('REQUEST_METHOD') == 'POST'){
             
            $this->form_validation->set_rules('categories_name', 'Category Name', 'trim|xss_clean|strip_tags|required|min_length[1]');
            $this->form_validation->set_rules('lifespan_default', 'Default Lifespan', 'trim|xss_clean|required|is_natural', array("is_natural_no_zero"=>"Please enter number only. If there is no lifespan, enter 0"));
            $this->form_validation->set_rules('tracking_default', 'Tracking Status', 'trim|xss_clean|required|in_list[0,1]', array("in_list" => "Invalid data received. Please contact administrator."));
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["categories_name"] = $this->input->post("categories_name", TRUE);
                $parameters["lifespan_default"] = $this->input->post("lifespan_default", TRUE);
                $parameters["tracking_default"] = $this->input->post("tracking_default", TRUE);
                
                $this->categorymodel->addCategory($parameters);
                
                $this->session->set_flashdata('success', "New category added");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
    public function deleteCategory($categories_id){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            $_POST["categories_id"] = $categories_id;
             
            $this->form_validation->set_rules('categories_id', 'Category ID', 'trim|xss_clean|required|is_natural_no_zero');
             
            if($this->form_validation->run()){
                $parameters = array();
                $parameters["categories_id"] = $this->input->post("categories_id", TRUE);
                
                $this->categorymodel->deleteCategory($parameters);
                
                $this->session->set_flashdata('success', "Category deleted");
                $this->json_output->setFlagSuccess();
            }else{
                $this->json_output->setFormValidationError();
            }                     
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json(); 
    }
}