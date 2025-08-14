<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Json_output {

	/*
	 * {"success":1,"message":[],"data":"","code":"200"}
	 */

	private $rs = array();

	public function __construct(){
        $this->rs["success"] = 1;
		$this->rs["message"] = array();
		$this->rs["data"] = "";
		$this->rs["code"] = "200";
        
		/* 200 = success
         * 250 = mobile number already verified  
		 * 400 = bad request - parameters not correct. common client error. login error.
		 * 401 = not logged-on or invalid api-key. redirect user to login.
		 * 403 = forbidden. successfully logged-on but no access to certain area
		 * 405 = Incorrect method. POST, GET, PUT, DELETE
		 * 500 = internal server error. common server error.
		 */
    }

    public function print_json(){
    	$CI =& get_instance();
    	$CI->output->set_content_type('application/json')->set_output(json_encode($this->rs));
    }
	
	public function setFlagFail(){
		$this->rs["success"] = 0;
	}
	
	public function setFlagSuccess(){
		$this->rs["success"] = 1;
        $this->setCode("200");
	}
	
	public function setCode($code = "200"){
		$this->rs["code"] = $code;
	}
	
	public function addMessage($message = ""){
		array_push($this->rs["message"], $message);		
	}
	
	public function setData($data = ""){
		$this->rs["data"] = $data;
	}
	
    public function setResult($data = ""){ //Same as setData with different parameter field
        $this->rs["result"] = $data;
    }
    
	public function setPostError(){
		$this->setFlagFail();
		$this->setCode("405");
		$this->addMessage("Only POST method allowed");
	}
	
	public function setFormValidationError(){
		$CI =& get_instance();
		$this->setFlagFail();
		$this->setCode("400");
		foreach($CI->form_validation->error_array() as $msg){
			$this->addMessage($msg);	
		}
	}
	
	public function setInvalidCredential(){
		$CI =& get_instance();
		$this->setFlagFail();
		$this->setCode("401");
		$this->addMessage("Invalid login credential. Please login again.");
	}
	
}