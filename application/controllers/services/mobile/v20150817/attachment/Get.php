<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Get extends Mobile_Attachment_Controller {
     
    public function __construct(){
        parent::__construct();
        $this->load->helper('file');
        $this->load->library('image_lib');
        $this->load->model('attachment/attachmentmodel');
    } 
    
    public function index(){
        //Silence is golden
    }
    
    public function local_image(){
        
        $file_name = pathinfo($this->attachment->full_path, PATHINFO_BASENAME);
        $this->output->set_header('Content-Disposition: inline; filename="'.$file_name.'"');
        
		$id = $this->attachments_id;
		$mode = $this->mode;
		$max_width = $this->max_width;
		$max_height = $this->max_height;
		
        $max_height = intval($max_height);
        $max_width = intval($max_width);
        
        if(!is_numeric($id) || ($max_height <= 0) || ($max_width <= 0) || ($max_width > 1600) || ($max_height > 1600)){
            $this->_showBlankImage();
        }
        
        if($mode == "crop"){
            $this->_crop($id, intval($max_width), intval($max_height));
        }else{
            $this->_showall($id, intval($max_width), intval($max_height));
        }
    }
    
    function _showall($id, $max_width, $max_height){
        
        $ext = "." . pathinfo($this->attachment->full_path, PATHINFO_EXTENSION);
        
        $full_path = $this->config->item("data_folder") . "cache/attachment/" . pathinfo($this->attachment->full_path, PATHINFO_FILENAME) . "-" . $max_width . "x" . $max_height . "-showall" . $ext;
        $source_image = $this->config->item("data_folder") . $this->attachment->full_path;
        
        if(file_exists($full_path)){
            $this->output->set_content_type($this->attachment->mime_type)->set_output(read_file($full_path));    
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
                    $this->_showBlankImage();
            }else{
                 $this->output->set_content_type($this->attachment->mime_type)->set_output(read_file($full_path));    
            }
        }
    } 
    
    function _crop($id, $max_width, $max_height){
        
        $ext = "." . pathinfo($this->attachment->full_path, PATHINFO_EXTENSION);
        
        $full_path = $this->config->item("data_folder") . "cache/attachment/" . pathinfo($this->attachment->full_path, PATHINFO_FILENAME) . "-" . $max_width . "x" . $max_height . "-crop" . $ext;
        $source_image = $this->config->item("data_folder") . $this->attachment->full_path;
        
        if(file_exists($full_path)){
            $this->output->set_content_type($this->attachment->mime_type)->set_output(read_file($full_path));    
        }else{
            
            $size = getimagesize($source_image);
            $ori_width = $size[0];
            $ori_height = $size[1];
            
            //Resize
            $image_config["image_library"] = "gd2";
            $image_config["source_image"] = $source_image;
            $image_config['create_thumb'] = FALSE;
            $image_config['maintain_ratio'] = TRUE;
            $image_config['new_image'] = $this->config->item("data_folder") . "cache/attachment/". $id . "_" . random_string("alnum", 16) . $ext;
            $image_config['quality'] = "100%";
            $image_config['width'] = $max_width;
            $image_config['height'] = $max_height;
            $dim = (intval($ori_width) / intval($ori_height)) - ($image_config['width'] / $image_config['height']);
            $image_config['master_dim'] = ($dim > 0)? "height" : "width";
            
            $resized_source = $image_config['new_image'];
            
            $this->image_lib->initialize($image_config);
            
            if(!$this->image_lib->resize()){
                $this->_showBlankImage();
            }else{
                //Crop
                $size = getimagesize($resized_source);
                $meta_width = $size[0];
                $meta_height = $size[1];
                
                $this->image_lib->clear();
                $image_config['image_library'] = 'gd2';
                $image_config['source_image'] = $resized_source;
                $image_config['new_image'] = $full_path;
                $image_config['quality'] = "100%";
                $image_config['maintain_ratio'] = FALSE;
                $image_config['width'] = $max_width;
                $image_config['height'] = $max_height;
                $image_config['x_axis'] = ($meta_width - $max_width)/2; 
                $image_config['y_axis'] = ($meta_height - $max_height)/2;
                
                $this->image_lib->initialize($image_config); 
                
                if (!$this->image_lib->crop()){
                    $this->_showBlankImage();
                }else{
                     $this->output->set_content_type($this->attachment->mime_type)->set_output(read_file($full_path));    
                }
                unlink($resized_source);   
            }
        } 
    }
    
}