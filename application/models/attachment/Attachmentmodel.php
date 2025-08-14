<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Attachmentmodel extends CI_Model {

	function __construct() {
		parent::__construct();
        $this->load->helper('string');
	}
	
    function getAttachmentSimpleInfo($attachment_id){
        $query = $this->db->get_where("attachments", array("id"=>$attachment_id));
        
        if($query->num_rows() > 0){
            $row = $query->row();
            return $row;  
        }
        return FALSE;
    }
    
    public function get_full_path($id = 0, $mode = "showall", $max_width = 300, $max_height = 300){
        
        $this->load->helper('file');
        $this->load->library('image_lib');
        
        $attachment = $this->getAttachmentSimpleInfo($id);
       
        if(!$attachment || !$attachment->status){ 
            return "assets/images/no_photo.jpg";
        }
        
        $ext = "." . pathinfo($attachment->full_path, PATHINFO_EXTENSION);
        $full_path = $this->config->item("data_folder") . "cache/attachment/" . pathinfo($attachment->full_path, PATHINFO_FILENAME) . "-" . $max_width . "x" . $max_height . "-showall" . $ext;
        $source_image = $this->config->item("data_folder") . $attachment->full_path;
        
        if(file_exists($full_path)){
            return $full_path;    
        }else{
            
            $size = getimagesize($source_image);
            $ori_width = $size[0];
            $ori_height = $size[1];
            
            //Resize
            $image_config["image_library"] = "gd2";
            $image_config["source_image"] = $source_image;
            $image_config['create_thumb'] = FALSE;
            $image_config['maintain_ratio'] = TRUE;
            $image_config['new_image'] = $full_path;
            $image_config['quality'] = "100%";
            $image_config['width'] = $max_width;
            $image_config['height'] = $max_height;
            $dim = (intval($ori_width) / intval($ori_height)) - ($image_config['width'] / $image_config['height']);
            $image_config['master_dim'] = ($dim < 0)? "height" : "width";
            
            $this->image_lib->initialize($image_config);
            
            if (!$this->image_lib->resize()){
                 return "assets/images/no_photo.jpg";
            }else{
                 return $full_path;      
            }
        }
    }
    
    function uploadMedia(){
        
        $data = array();
        $data["success"] = FALSE;
        $data["attachment_id"] = 0;
        $data["error_message"] = "";
        $data["file_type"] = "";
        
        /* Uploading */ 
        $config['upload_path'] = $this->config->item("data_folder") . 'raw/attachment/';
        $config['allowed_types'] = 'avi|mpeg|mp4|ogg|webm|jpeg|jpg|png|bmp|jpe|gif|xls|xlsx';
        $config['file_name'] = time() . "_" . random_string("alnum", 16); 
        $config['overwrite'] = TRUE;
        $config['max_size'] = '102400';
        $config['max_width']  = '0';
        $config['max_height']  = '0';   
        
        $this->load->library('upload', $config);
        
        if(!is_dir($config['upload_path'])){
            mkdir($config['upload_path'], 0755, TRUE);
        } 
        
        if(!$this->upload->do_upload("attachment")){
            /* Failed to upload */ 
            $data["error_message"] = $this->upload->display_errors('', '');
            return $data;
        }else{
            
            $upload_data = $this->upload->data();
            $ext = strtolower($upload_data["file_ext"]);
            
            $file_type = explode("/", $upload_data["file_type"], 2);
            
            $data["file_type"] = $file_type[0];
            
            if($ext == ".xls" || $ext == ".xlsx"){
                $data["file_type"] = "document";
            }
            
            $attachment_info = array();
            $attachment_info["users_id"] = $this->session->userdata('users_id');
            $attachment_info["full_path"] = "raw/attachment/" . $config['file_name'];
            $attachment_info["file_name"] = $upload_data["orig_name"];
            $attachment_info["mime_type"] = $upload_data["file_type"];
            $attachment_info["attachment_type"] = $data["file_type"];
            $attachment_info["link_type"] = 1;
            $attachment_info["datetime_created"] = date("Y-m-d H:i:s");
            
            if(isset($_FILES["attachment"]["name"])){
                $attachment_info["file_name"] = $_FILES["attachment"]["name"];
            }
            
            $this->db->insert("attachments", $attachment_info);
            
            $attachment_id = $this->db->insert_id();
            
            rename($upload_data["full_path"], $upload_data["file_path"] . $attachment_id . $ext);
            
            $this->db->where("id", $attachment_id);
            $this->db->update("attachments", array("full_path" => "raw/attachment/" . $attachment_id . $ext));
            
            $data["success"] = TRUE;
            $data["attachment_id"] = $attachment_id;
            
            return $data;
        }
    }
}
