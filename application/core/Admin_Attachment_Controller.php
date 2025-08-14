<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_Attachment_Controller extends CI_Controller {

	function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model("attachment/attachmentmodel");
        $this->load->helper('file');
        $this->_verifyAuthorization();
        $this->_retrieveImage();
    }
	
    public $attachment = FALSE;
    
    function _verifyAuthorization(){
        if(!$this->session->userdata('users_id')){
            $this->showBlankImage();
        }
    }
    
    function _retrieveImage(){
        
        $attachment_id = $this->uri->segment(5);
        
        if(($attachment_id && is_numeric($attachment_id) && (intval($attachment_id) >= 1))){
            
            $this->attachment = $this->attachmentmodel->getAttachmentSimpleInfo($attachment_id);
           
            if(!$this->attachment || !$this->attachment->status){ 
                $this->showBlankImage();    
            }
        }else{
            $this->showBlankImage();
        }   
    }
    
    function showBlankImage(){
        $image_path = "assets/images/no_photo.jpg";
        $this->output->set_content_type("image/jpg")->set_output(read_file($image_path));
        $this->output->_display();
        exit();
    }
}

