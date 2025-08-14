<?php
class MY_Form_validation extends CI_Form_validation
{
     function __construct($config = array())
     {
          parent::__construct($config);
		  $this->fixEmptyPostRequest();
     }
 
    /**
     * Error Array
     *
     * Returns the error messages as an array
     *
     * @return  array
     */
    function error_array(){
        if (count($this->_error_array) === 0){
                return FALSE;
        }else{
            return $this->_error_array;
		}
    }
	
	function fixEmptyPostRequest(){
		if($_SERVER['REQUEST_METHOD'] == "POST" && count($_POST) == 0){
			$_POST["CI_Padding"] = "CI_Padding";
		}
	}
    
    function is_money($input) {
         return (bool) preg_match('/^(\+|\-)?[0-9]+(\.[0-9]{0,2})?$/', $input);
     }
    
}