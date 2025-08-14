<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notificationmodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->library('email');
        $this->email->set_mailtype("html");
	}
	
	function sendEmail($email_info = array()){
		/*
		 * Only html email is supported as of current version
		 * 
		 * This is a non-checking function. All input must be pre-verified
		 * 
		 * ************************************
		 * Standard $notify input
		 * ====================================
		 * $email_info["receiver_email"]
		 * $email_info["email_subject"]
		 * $email_info["email_content"]
         * $email_info["attachment_path"] = array() //absolute path to file. Optional.
		 * 
		 */
		
		$config = array(); 
        $config['useragent']        = 'AssetsTrackingSystem';        
        $config['protocol']         = 'smtp';        
        $config['smtp_host']        = '';
        $config['smtp_user']        = '';
        $config['smtp_pass']        = '';
        $config['smtp_port']        = '';
        $config['smtp_timeout']     = 30;
        $config['smtp_crypto']      = 'tls';
        $config['mailpath']         = '/usr/sbin/sendmail';
        $config['wordwrap']         = TRUE;
        $config['wrapchars']        = 76;
        $config['mailtype']         = 'html';
        $config['charset']          = 'utf-8';
        $config['validate']         = FALSE;
        $config['priority']         = 3;
        $config['crlf']             = "\r\n";
        $config['newline']          = "\r\n";
        $config['bcc_batch_mode']   = FALSE;
        $config['bcc_batch_size']   = 200;

		$query = $this->db->get("config");
        
        $sender_email = "";
        
        foreach($query->result() as $row){
            switch($row->config_key){
                case "smtp_host":
                        $config['smtp_host'] = $row->config_value; 
                        break;
                case "smtp_user": 
                        $config['smtp_user'] = $row->config_value;
                        break;
                case "smtp_pass": 
                        $config['smtp_pass'] = $row->config_value;
                        break;
                case "smtp_port":
                        $config['smtp_port'] = $row->config_value; 
                        break;
                case "sender_email":
                        $sender_email = $row->config_value; 
                        break;
            }
        }        
         
         $this->email->initialize($config);
         
		 $this->email->from($sender_email, "Asset Management System");
		 $this->email->to($email_info["receiver_email"]); 
		 $this->email->subject($email_info["email_subject"]);
		 $this->email->message($email_info["email_content"]);	
		
         foreach($email_info["attachment_path"] as $attachment){
             $this->email->attach($attachment, 'inline');
         }  
            
		 $this->email->send();
	}
}
