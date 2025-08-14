<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Asset extends Mobile_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('asset/assetmodel');
    } 
    
    public function index()
    {
        $this->downloadAsset();
    }
    
    function downloadAsset(){
        
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            $this->json_output->setData($this->assetmodel->getAssetsForMobile());
            $this->json_output->setFlagSuccess();
        }else{
            $this->json_output->setPostError();
        }
        $this->json_output->print_json();
    }
    
}