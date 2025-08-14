<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Track extends Mobile_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('asset/trackmodel');
    } 
    
    public $track_data = array();
    
    public function index()
    {
        $this->uploadTrack();
    }
    
    function uploadTrack(){
        
        $this->form_validation->set_rules('data', 'Data', 'trim|xss_clean|required|callback__check_valid_data');
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            
            if($this->form_validation->run()){
                
                $parameters = array();
                $parameters["users_id"] = $this->users_id;
                $parameters["data"] = $this->track_data;
                
                $data = $this->trackmodel->uploadTrack($parameters);
                
                $this->json_output->setData($data);
                $this->json_output->setFlagSuccess();
                
            }else{
                $this->json_output->setFormValidationError();
            }
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();           
    } 
    
    function _check_valid_data($data){
        
        $this->form_validation->set_message('_check_valid_data', 'Data uploaded is not valid or empty.');
        
        $json_array = json_decode($data, TRUE);
        
        if(!empty($json_array) && is_array($json_array)){
            
            /* Verify Correct Data */
            $filtered_data = array();
            
            $field_count = 0;
            
            foreach($json_array as $json_row){
                
                if(is_array($json_row)){
                
                    foreach($json_row as $key=>$value){
                    
                        switch($key){
                            case "assets_id":
                                    if(!((string)(int)$value == $value)){
                                        break 2;
                                    }
                                    $field_count++; 
                                    break;
                            case "departments_id": 
                                    if(!((string)(int)$value == $value)){
                                        break 2;
                                    }
                                    $field_count++;
                                    break;
                            case "datetime_scanned": 
                                    if(!is_mysql_datetime($value)){
                                        break 2;
                                    }
                                    $field_count++;
                                    break;
                            case "quantity": 
                                    if(!((string)(int)$value == $value)){
                                        break 2;
                                    }
                                    $field_count++;
                                    break;
                            case "remark": 
                                    /* No Checking */
                                    $field_count++;
                                    break;
                            case "terminal_record_id": 
                                    if(!((string)(int)$value == $value)){
                                        break 2;
                                    }
                                    $field_count++;
                                    break;
                            case "terminal_id": 
                                    if(strlen($value) == 0){
                                        break 2;
                                    }
                                    $field_count++;
                                    break;
                        }
    
                    }
                }
                
                if($field_count == 7){
                    $filtered_data[] = $json_row;    
                }
                $field_count = 0;
            }

            if(count($filtered_data) == 0){
                $this->form_validation->set_message('_check_valid_data', 'There is no valid data in the upload. Ensure you have setup terminal ID in settings.');
                return FALSE;
            }
            
            $this->track_data = $filtered_data;
            return TRUE;    
        }
        
        return FALSE;
    }
}