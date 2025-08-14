<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_hash'))
{
	function get_hash($data)
	{
	    preg_match_all('/#(\w+)/',$data, $tags);
        $data = array_map(function($str){ return "$str#"; }, $tags[0]);
        return strtolower(implode(",", $data));		
	}
}

if ( ! function_exists('is_natural_number'))
{
    function is_natural_number($data) //Non-zero
    {
        if($data){
            return ((string)(int)$data === (string)$data) && ((int)$data > 0);    
        }
        return FALSE;     
    }
}

if ( ! function_exists('is_sort_order'))
{
    function is_sort_order($data) 
    {
        $data = strtolower($data);   
        return (($data == "asc") || ($data == "desc"))? TRUE : FALSE;   
    }
}

if ( ! function_exists('decode_bracket'))
{
    function decode_bracket($data) 
    {
        $data = str_replace('&#40;', '(', $data);
        $data = str_replace('&#41;', ')', $data);   
        return $data;   
    }
}

if ( ! function_exists('is_dmy_date'))
{
    function is_dmy_date($data) 
    {
        if(date('d-m-Y', strtotime($data)) == $data){
            return TRUE;
        }
        return FALSE;
    }
}

if ( ! function_exists('is_ymd_date'))
{
    function is_ymd_date($data) 
    {
        if(date('Y-m-d', strtotime($data)) == $data){
            return TRUE;
        }
        return FALSE;
    }
}

if ( ! function_exists('is_mysql_datetime'))
{
    function is_mysql_datetime($data) 
    {
        if(date('Y-m-d H:i:s', strtotime($data)) == $data){
            return TRUE;
        }
        return FALSE;
    }
}

if ( ! function_exists('is_ymmmmdd_date'))
{
    function is_ymmmmdd_date($data) 
    {
        if(date('d-F-Y', strtotime($data)) == $data){
            return TRUE;
        }
        if(strlen($data) == 0){
            return TRUE;
        }
        return FALSE;
    }
}


if ( ! function_exists('is_valid_phone'))
{
    function is_valid_phone($data) 
    {
        if(preg_match('/^\+\d{10,20}$/', $data)){
            return TRUE;
        }
        return FALSE;
    }
}

if ( ! function_exists('mysql_date'))
{
    function mysql_date($date) 
    {
        return date("Y-m-d", strtotime($date));
    }
}

if ( ! function_exists('mysql_datetime'))
{
    function mysql_datetime($date) 
    {
        return date("Y-m-d H:i:s", strtotime($date));
    }
}

if ( ! function_exists('format_period_from_hour'))
{
    function format_period_from_hour($hour_input) 
    {
        $hour_input = intval($hour_input);
        
        $month = 0;
        $day = 0;
        $hour = 0;
        
        $month = floor($hour_input / 30.4375 / 24);
        $hour_input = $hour_input - ($month * 30.4375 * 24); 
        $day = floor($hour_input / 24);
        $hour = round($hour_input - ($day * 24));
         
        $str = (!empty($month)? $month . " month(s) " : "") . (!empty($day)? $day . " day(s) " : "") . (!empty($hour)? $hour . " hour(s)" : "");
        
        return $str;
    }
}

if ( ! function_exists('is_valid_time'))
{
    function is_valid_time($time_input)
    {
        $time_input = strtolower(str_replace(" ", "", $time_input));
        
        if((date('h:ia', strtotime($time_input)) == $time_input) || (date('g:ia', strtotime($time_input)) == $time_input)){
            return TRUE;
        }
        if(strlen($time_input) == 0){
            return TRUE;
        }
        return FALSE;
    }
}

if ( ! function_exists('remove_invalid_sheet_title_character'))
{
    function remove_invalid_sheet_title_character($input)
    {
        $invalid = array("?", "*", "/", "\\", "[", "]", ":");
        
        return str_replace($invalid,"",$input);
    }
}

if ( ! function_exists('extract_date_return_timestamp'))
{
    function extract_date_return_timestamp($date_input)
    {
        $timestamp = "";
        
        $date = DateTime::createFromFormat("Y-m-d", $date_input);
        
        if($date){
            $timestamp = $date->getTimestamp();    
        }else{
            $date = DateTime::createFromFormat("d-m-Y", $date_input);
            if($date){
                $timestamp = $date->getTimestamp();
            }else{
                $date = DateTime::createFromFormat("d/m/Y", $date_input);
                if($date){
                    $timestamp = $date->getTimestamp();
                }else{
                    $date = DateTime::createFromFormat("j/m/Y", $date_input);
                    if($date){
                        $timestamp = $date->getTimestamp();    
                    }else{
                        $date = strtotime($date_input);
                        if($date){
                            $timestamp = $date;
                        }else{
                            if(is_numeric($date_input)){
                                $timestamp = PHPExcel_Shared_Date::ExcelToPHP($date_input);    
                            }
                        }
                    }    
                }
            }
        }
        
        return $timestamp;
    }
}

/* End of file MY_string_helper.php */
/* Location: ./application/helpers/MY_string_helper.php */