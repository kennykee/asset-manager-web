<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Set extends Admin_Controller {
     
    public function __construct(){
        parent::__construct();
        $this->load->helper('file');
        $this->load->library('image_lib');
        $this->load->model('attachment/attachmentmodel');
    } 
    
    public function index(){
        $this->uploadMedia();
    }
    
    function uploadMedia(){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){  
            
            if(!empty($_FILES['attachment']['name']) && file_exists($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])){           
                
                $files_data = $this->attachmentmodel->uploadMedia();
                
                if($files_data["success"]){
                    
                    $data = array();
                    $data["attachment_id"] = $files_data["attachment_id"];
                    $data["file_type"] = $files_data["file_type"];
                    
                    $this->json_output->setData($data);
                    $this->json_output->setFlagSuccess();  
                }else{
                    $this->json_output->setFlagFail();
                    $this->json_output->setCode("500");
                    $this->json_output->addMessage($files_data["error_message"]);
                }
            }else{  
                $this->json_output->setFlagFail();
                $this->json_output->setCode("500");
                $this->json_output->addMessage("Error. No video uploaded.");
            }        
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
}