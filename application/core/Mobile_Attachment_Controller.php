<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mobile_Attachment_Controller extends CI_Controller {
    
    public $users_id = FALSE;
    public $cache_user = array();
    public $api_version = "";
	public $attachments_id = 0;
	public $mode = "showall";
	public $max_width = 300;
	public $max_height = 300;
    
	function __construct()
    {
        parent::__construct();
        $this->load->model("attachment/attachmentmodel");
        $this->load->helper('file');
        $this->load->driver('cache');
        $this->_verifyAuthorization();
        $this->_verifyStatus();
        $this->_retrieveImage();
    }
	
    public $attachment = FALSE;
    
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
        $this->_showBlankImage();
    }
    
    function _verifyStatus(){
        $status = $this->cache_user["status"];
        
        if($status == "0"){
            $this->_showBlankImage();
        }
    }
    
    function _retrieveImage(){
        
        $attachment_id = $this->uri->segment(6,0);
        
        if(($attachment_id && is_numeric($attachment_id) && (intval($attachment_id) >= 1))){
            
            $this->attachment = $this->attachmentmodel->getAttachmentSimpleInfo($attachment_id);
			$this->attachments_id = $attachment_id;
			$this->mode = $this->uri->segment(7,"showall");
			$this->max_width = $this->uri->segment(8,300);
			$this->max_height = $this->uri->segment(9,300);
			
            if(!$this->attachment || !$this->attachment->status){ 
                $this->_showBlankImage();    
            }
        }else{
            $this->_showBlankImage();
        }
    }
    
    function _showBlankImage(){
        $image_path = "assets/images/no_photo.jpg";
        $this->output->set_content_type("image/jpg")->set_output(read_file($image_path));
        $this->output->_display();
        exit();
    }
}

