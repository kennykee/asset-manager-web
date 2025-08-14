<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenancemodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->model('notification/notificationmodel');
    }
    
    /* 
     * Get maintenance date list by one asset
     *
     * $parameters["assets_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * 
     * */
    function getMaintenance($parameters){
        
        $data = array();
        
        $this->db->where("assets_id", $parameters["assets_id"]);
        $this->db->where("maintenance_date >= CURDATE()");
        $query = $this->db->get("assets_maintenance");
        
        $data["upcoming"] = $query->result_array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where("assets_id", $parameters["assets_id"]);
        $this->db->where("maintenance_date < CURDATE()");
        $query = $this->db->get("assets_maintenance");
        
        $data["history"] = $query->result_array();
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->setQueryString("tab=" . $parameters["current_tab"]);
        $this->pagination_output->build();
        
        return $data;   
    }
    
    /* 
     * Get last maintenance date or future dates whichever are earlier
     *
     * $parameters["assets_id"] = array()
     * 
     * */
    function getNextMaintenanceByOneAsset($parameters){
        
        $data = array();
        
        $this->db->order_by("maintenance_date", "desc");
        $this->db->where("assets_id", $parameters["assets_id"]);
        $this->db->where("maintenance_date >=", date('Y-m-d'));
        $query = $this->db->get("assets_maintenance");
        
        if($query->num_rows() > 0){
            return $query->result_array();
        }
        
        $this->db->order_by("maintenance_date", "desc");
        $query = $this->db->get_where("assets_maintenance", array("assets_id" => $parameters["assets_id"]), 1);
        
        if($query->num_rows() > 0){
            return $query->result_array();
        }
        
        return $data;
    }
    
    /* 
     * Add maintenance
     *
     * $parameters["assets_id"]
     * $parameters["maintenance_date"]
     * 
     * */
    function addMaintenance($parameters){
            
        $insert_parameters = array();
        $insert_parameters["assets_id"] = $parameters["assets_id"];
        $insert_parameters["maintenance_date"] = $parameters["maintenance_date"];
        $insert_parameters["notification_status"] = 0;
        $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");        
        
        $this->db->insert("assets_maintenance", $insert_parameters);
    }
    
    /* 
     * Update maintenance
     *
     * $parameters["assets_maintenance_id"]
     * $parameters["maintenance_date"]
     * 
     * */
    function updateMaintenance($parameters){
        
        $this->db->where("id", $parameters["assets_maintenance_id"]);
        $this->db->update("assets_maintenance", array("maintenance_date"=>$parameters["maintenance_date"]));
    }
    
    /* 
     * Delete maintenance date
     *
     * $parameters["assets_maintenance_id"]
     * 
     * */
    function deleteMaintenance($parameters){
        $this->db->delete("assets_maintenance", array("id"=>$parameters["assets_maintenance_id"]));
    }
    
    function checkAndSendMaintenance(){
            
        /* Second Notification */
        $this->db->where("notification_status", 1);
        $this->db->where("maintenance_date < ",  date("Y-m-d H:i:s", strtotime("+14 day")));
        $query = $this->db->get("assets_maintenance");
        
        $assets_id = array();
        $assets = array();
        
        $departments_id = array();
        $departments = array();

        $emails = array();
        $email_sending = array();
        
        $maintenance_id = array();
                
        foreach($query->result() as $row){
            $assets_id[] = $row->assets_id;
            $maintenance_id[] = $row->id;
        }
        
        if(count($assets_id) > 0){
            $this->db->where_in("id", $assets_id);
            $assets_query = $this->db->get("assets");
            
            foreach($assets_query->result_array() as $ast){
                $assets[$ast["id"]] = $ast;
            }
            
            $this->db->distinct();
            $this->db->select("assets_id, departments_id");
            $this->db->where_in("assets_id", $assets_id);
            $departments_query = $this->db->get("assets_departments");
            
            foreach($departments_query->result_array() as $depart){
                $departments_id[] = $depart["departments_id"];
                $departments[$depart["departments_id"]][] = $depart["assets_id"];
            }
            
            if(count($departments_id) > 0){
                $this->db->where_in("departments_id", $departments_id);
                $email_query = $this->db->get("recipients");
                
                foreach($email_query->result_array() as $email){
                    $emails[$email["email"]][] = $email["departments_id"];
                }
            }
            
            foreach($emails as $key=>$email){
                $current_email = $key;
                foreach($email as $email_departments_id){
                    if(isset($departments[$email_departments_id])){
                        $assets_list = $departments[$email_departments_id];
                        foreach($assets_list as $departments_assets_id){
                            $email_sending[$current_email][] = $departments_assets_id;    
                        }
                    }
                }
            }
            
            foreach($email_sending as $key=>$sending){
                $email_sending[$key] = array_unique($sending);
            }
            
            /* Send Email */
            foreach($email_sending as $key=>$sending){
                $email_to_send = $key;
                
                $message = array();
                $message[] = "<!DOCTYPE html><head><meta charset='utf-8'><title>Asset Due For Maintenance</title></head>";
                $message[] =    "<body>";
                $message[] =        "Dear Recipient,";
                $message[] =        "<br /><br />";
                
                $message[] =        "The following assets are due for maintenance in <b>14 days time</b>: <br/><br/>";
                
                foreach($sending as $send_asset){
                    if(isset($assets[$send_asset])){
                        $assets_info = $assets[$send_asset];
                        $message[] = "<div>- " . $assets_info["assets_name"] . " (ID: " . $assets_info["barcode"] . ")</div>";   
                    }
                }
                
                $message[] =        "<br />";        
                $message[] =        "Thank you and have a nice day ahead!";
                $message[] =        "<br /><br />";
                $message[] =        "Warmest Regards, <br />";
                $message[] =        "Asset Management System";
                $message[] =        "<br /><br />";
                $message[] =        "<span style='font-size: 11px'>Note: Please do not reply to this email as it is unattended</span>";
                $message[] =    "</body>";
                $message[] = "</html>";
                
                $email_parameters["receiver_email"] = $email_to_send;
                $email_parameters["email_subject"] = "Asset Management System Notification";
                $email_parameters["email_content"] = implode("", $message);
                $email_parameters["attachment_path"] = array();
                
                $this->notificationmodel->sendEmail($email_parameters);
            }
        }
        
        /* Update notification status */
        if(count($maintenance_id) > 0){
            $this->db->where_in("id", $maintenance_id);
            $this->db->update("assets_maintenance", array("notification_status"=>2));
        }
        
        
        /* First Notification */
        $this->db->where("notification_status", 0);
        $this->db->where("maintenance_date < ",  date("Y-m-d H:i:s", strtotime("+30 day")));
        $query = $this->db->get("assets_maintenance");
        
        $assets_id = array();
        $assets = array();
        
        $departments_id = array();
        $departments = array();

        $emails = array();
        $email_sending = array();
        
        $maintenance_id = array();
                
        foreach($query->result() as $row){
            $assets_id[] = $row->assets_id;
            $maintenance_id[] = $row->id;
        }
        
        if(count($assets_id) > 0){
            $this->db->where_in("id", $assets_id);
            $assets_query = $this->db->get("assets");
            
            foreach($assets_query->result_array() as $ast){
                $assets[$ast["id"]] = $ast;
            }
            
            $this->db->distinct();
            $this->db->select("assets_id, departments_id");
            $this->db->where_in("assets_id", $assets_id);
            $departments_query = $this->db->get("assets_departments");
            
            foreach($departments_query->result_array() as $depart){
                $departments_id[] = $depart["departments_id"];
                $departments[$depart["departments_id"]][] = $depart["assets_id"];
            }
            
            if(count($departments_id) > 0){
                $this->db->where_in("departments_id", $departments_id);
                $email_query = $this->db->get("recipients");
                
                foreach($email_query->result_array() as $email){
                    $emails[$email["email"]][] = $email["departments_id"];
                }
            }
            
            foreach($emails as $key=>$email){
                $current_email = $key;
                foreach($email as $email_departments_id){
                    if(isset($departments[$email_departments_id])){
                        $assets_list = $departments[$email_departments_id];
                        foreach($assets_list as $departments_assets_id){
                            $email_sending[$current_email][] = $departments_assets_id;    
                        }
                    }
                }
            }
            
            foreach($email_sending as $key=>$sending){
                $email_sending[$key] = array_unique($sending);
            }
            
            /* Send Email */
            foreach($email_sending as $key=>$sending){
                $email_to_send = $key;
                
                $message = array();
                $message[] = "<!DOCTYPE html><head><meta charset='utf-8'><title>Asset Due For Maintenance</title></head>";
                $message[] =    "<body>";
                $message[] =        "Dear Recipient,";
                $message[] =        "<br /><br />";
                
                $message[] =        "The following assets are due for maintenance in <b>30 days time</b>: <br/><br/>";
                
                foreach($sending as $send_asset){
                    if(isset($assets[$send_asset])){
                        $assets_info = $assets[$send_asset];
                        $message[] = "<div>- " . $assets_info["assets_name"] . " (ID: " . $assets_info["barcode"] . ")</div>";   
                    }
                }
                
                $message[] =        "<br />";        
                $message[] =        "Thank you and have a nice day ahead!";
                $message[] =        "<br /><br />";
                $message[] =        "Warmest Regards, <br />";
                $message[] =        "Asset Management System";
                $message[] =        "<br /><br />";
                $message[] =        "<span style='font-size: 11px'>Note: Please do not reply to this email as it is unattended</span>";
                $message[] =    "</body>";
                $message[] = "</html>";
                
                $email_parameters["receiver_email"] = $email_to_send;
                $email_parameters["email_subject"] = "Asset Management System Notification";
                $email_parameters["email_content"] = implode("", $message);
                $email_parameters["attachment_path"] = array();
                
                $this->notificationmodel->sendEmail($email_parameters);
            }
        }
        
        /* Update notification status */
        if(count($maintenance_id) > 0){
            $this->db->where_in("id", $maintenance_id);
            $this->db->update("assets_maintenance", array("notification_status"=>1));
        }
        
        echo "COMPLETED";
    }
    
    /* 
     * Check whether assets_maintenance_id is accessible or writable based on current URI access array 
     *
     * $assets_maintenance_id
     * 
     * */
    function isMaintenanceAccessibleByCurrentAccessArray($assets_maintenance_id){ 
        
        /* Blanket Access */
        if(in_array("*", $this->access_array)){
            return TRUE;
        }
        
        $query = $this->db->get_where("assets_maintenance", array("id" => $assets_maintenance_id));
        
        if($query->num_rows() > 0){
            
            $row = $query->row();
            $assets_id = $row->assets_id;
            
            if(count($this->access_array)){
                $this->db->where_in("departments_id", $this->access_array);
                $this->db->where("assets_id", $assets_id);
                $query = $this->db->get("assets_departments");
                
                if($query->num_rows() > 0){
                    return TRUE;
                }        
            }
        }
        return FALSE;
    }
}
