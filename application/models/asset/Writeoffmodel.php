<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Writeoffmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    /* 
     * Get writeoff histories by departments ID list
     *
     * $parameters["departments"] = array(); //- refers to list department ID
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["start_date"] = optional
     * $parameters["end_date"] = optional 
     * 
     * */
    function getWriteOffByDepartments($parameters){
        
        $data = array();
        
        if(isset($parameters["start_date"])){
            $this->db->where("datetime_created >=", $parameters["start_date"]);
        }
        
        if(isset($parameters["end_date"])){
            $this->db->where("datetime_created <=", $parameters["end_date"]);
        }
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where_in("origin_departments_id", $parameters["departments"]);
        $query = $this->db->get("writeoff");
           
        $assets_id = array();
        $assets = array();
        
        $users_id = array();
        $users = array();
        
        $departments_id = array();
        $departments = array();
        
        $writeoff_id = array();
        $approvers = array();
        
        $next_approver = array();
        
        foreach($query->result() as $row){
            $users_id[] = $row->requester_users_id;
            $users_id[] = $row->approver_users_id;
            $assets_id[] = $row->assets_id;
            $writeoff_id[] = $row->id;
        }
        
        /* Pagination */
        $page_query = $this->db->query('SELECT FOUND_ROWS() AS `count`');
        $this->pagination_output->setCurrentURL(current_url());
        $this->pagination_output->setTotalRows($page_query->row()->count);
        $this->pagination_output->setPageNo($parameters["page_no"]);
        $this->pagination_output->setCountPerPage($parameters["count_per_page"]);
        $this->pagination_output->setSort($parameters["sort"]);
        $this->pagination_output->setSortField($parameters["sort_field"]);
        $this->pagination_output->build();
        
        if(count($assets_id) > 0){
            $this->db->where_in("id", $assets_id);
            $assets_query = $this->db->get("assets");
            
            foreach($assets_query->result_array() as $assets_row){
                $assets[$assets_row["id"]] = $assets_row;
            }    
        }
        
        /* Approver List and Next Approver */
        if(count($writeoff_id) > 0){
            $this->db->where_in("writeoff_id", $writeoff_id);
            $this->db->order_by("priority", "DESC");
            $approvers_query = $this->db->get("writeoff_approvers");
            
            foreach($approvers_query->result_array() as $approvers_row){
                $approvers[$approvers_row["writeoff_id"]][] = $approvers_row;
                $users_id[] = $approvers_row["users_id"];
            }
        }
        
        foreach($approvers as $approvers_writeoff_id => $approvers_row){
            foreach($approvers_row as $approv){
                if($approv["status"] == "0"){
                    $next_approver[$approvers_writeoff_id] = $approv["users_id"];
                    break;
                }
            }
        }
        
        if(count($users_id) > 0){
            $this->db->where_in("id", $users_id);
            $this->db->select("id, person_name, email");
            $users_query = $this->db->get("users");
            
            foreach($users_query->result_array() as $users_row){
                $users[$users_row["id"]] = $users_row;
            }
        }
        
        foreach($query->result_array() as $row){
            
            $assets_info = array();
            $assets_info["attachments_id"] = 0;
            $assets_info["assets_name"] = "Asset not found";
            $assets_info["barcode"] = "Asset not found";
            $assets_info["status"] = "unavailable";
            $assets_info["assets_value"] = "";
            $assets_info["invoice_date"] = "";
            $assets_info["assets_lifespan"] = "";
            $assets_info["salvage_value"] = "";
            
            if(isset($assets[$row["assets_id"]])){
                $asset_selected = $assets[$row["assets_id"]];
                $assets_info["attachments_id"] = $asset_selected["attachments_id"];
                $assets_info["assets_name"] = $asset_selected["assets_name"];
                $assets_info["barcode"] = $asset_selected["barcode"];
                $assets_info["status"] = $asset_selected["status"];
                $assets_info["assets_value"] = $asset_selected["assets_value"];
                $assets_info["invoice_date"] = $asset_selected["invoice_date"];
                $assets_info["assets_lifespan"] = $asset_selected["assets_lifespan"];
                $assets_info["salvage_value"] = $asset_selected["salvage_value"];
            }
            $row["assets"] = $assets_info;
            
            /* Requester */
            $users_info = array();
            $users_info["person_name"] = "Requester user not found";
            $users_info["email"] = "User record not found";
            
            if(isset($users[$row["requester_users_id"]])){
                $user_selected = $users[$row["requester_users_id"]];
                $users_info["person_name"] = $user_selected["person_name"];
                $users_info["email"] = $user_selected["email"];
            }
            $row["users_requester"] = $users_info;
            
            /* Approver */
            $row["users_approver"] = array();
            
            if(isset($approvers[$row["id"]])){
                $selected_approver = $approvers[$row["id"]];
                foreach($selected_approver as $appro){
                    $users_info = array();
                    $users_info["person_name"] = "Approver user not found";
                    $users_info["email"] = "User record not found";
                    $users_info["status"] = $appro["status"];
                    $users_info["datetime_approved"] = $appro["datetime_approved"];
                                                
                    if(isset($users[$appro["users_id"]])){
                        $user_selected = $users[$appro["users_id"]];
                        $users_info["person_name"] = $user_selected["person_name"];
                        $users_info["email"] = $user_selected["email"];
                    }
                    $row["users_approver"][] = $users_info;   
                }
            }
            
            $row["next_approver"] = 0;
            /* Next Approver */
            if(isset($next_approver[$row["id"]])){
                $row["next_approver"] = $next_approver[$row["id"]];                    
            }
            
            $data[$row["origin_departments_id"]][] = $row;
        }
     
        return $data;   
    }

    /* 
     * Get write off histories by asset ID
     *
     * $parameters["assets_id"]
     * $parameters["page_no"] 
     * $parameters["count_per_page"]
     * $parameters["sort"] 
     * $parameters["sort_field"]
     * $parameters["current_tab"]
     * 
     * */
    function getWriteOffBySingleAsset($parameters){
        
        $data = array();
        
        $this->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->db->order_by($parameters["sort_field"], $parameters["sort"]);
        $this->db->limit($parameters["count_per_page"], ($parameters["page_no"] - 1) * $parameters["count_per_page"]);
        $this->db->where("assets_id",  $parameters["assets_id"]);
        $query = $this->db->get("writeoff");
           
        $users_id = array();
        $writeoff_id = array();
        $users = array();
        $approvers = array();
        
        $departments_id = array();
        $departments = array();
        
        foreach($query->result() as $row){
            $users_id[] = $row->requester_users_id;
            $users_id[] = $row->approver_users_id;
            $writeoff_id[] = $row->id;
        }
        
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
        
        if(count($writeoff_id) > 0){
            $this->db->where_in("writeoff_id", $writeoff_id);
            $this->db->order_by("priority", "DESC");
            $approvers_query = $this->db->get("writeoff_approvers");
            
            foreach($approvers_query->result_array() as $approvers_row){
                $approvers[$approvers_row["writeoff_id"]][] = $approvers_row;
                $users_id[] = $approvers_row["users_id"];
            }
        }
        
        if(count($users_id) > 0){
            $this->db->where_in("id", $users_id);
            $this->db->select("id, person_name, email");
            $users_query = $this->db->get("users");
            
            foreach($users_query->result_array() as $users_row){
                $users[$users_row["id"]] = $users_row;
            }
        }
        
        foreach($query->result_array() as $row){
            
            /* Requester */
            $users_info = array();
            $users_info["person_name"] = "Requester user not found";
            $users_info["email"] = "User record not found";
            
            if(isset($users[$row["requester_users_id"]])){
                $user_selected = $users[$row["requester_users_id"]];
                $users_info["person_name"] = $user_selected["person_name"];
                $users_info["email"] = $user_selected["email"];
            }
            $row["users_requester"] = $users_info;
            
            /* Approver */
            $row["users_approver"] = array();
            
            if(isset($approvers[$row["id"]])){
                $selected_approver = $approvers[$row["id"]];
                foreach($selected_approver as $appro){
                    $users_info = array();
                    $users_info["person_name"] = "Approver user not found";
                    $users_info["email"] = "User record not found";
                    $users_info["status"] = $appro["status"];
                    $users_info["datetime_approved"] = $appro["datetime_approved"];
                                                
                    if(isset($users[$appro["users_id"]])){
                        $user_selected = $users[$appro["users_id"]];
                        $users_info["person_name"] = $user_selected["person_name"];
                        $users_info["email"] = $user_selected["email"];
                    }
                    $row["users_approver"][] = $users_info;   
                }
            }
            
            $data[] = $row;
        }
        return $data;   
    }

    /* 
     * Request write off
     *
     * $parameters["assets_departments_id"] 
     * $parameters["type"] 
     * $parameters["quantity"] 
     * $parameters["remark"]
     * $parameters["approvers"]
     * 
     * */
    function requestWriteoff($parameters){
            
        $query = $this->db->get_where("assets_departments", array("id"=>$parameters["assets_departments_id"]));
        
        if($query->num_rows() > 0){
            
            $row = $query->row();
            
            $department_name = "No Department Name";
            
            $department_query = $this->db->get_where("departments", array("id" => $row->departments_id));
            
            if($department_query->num_rows() > 0){
                $department_row = $department_query->row();
                $department_name = $department_row->departments_name;
            }
            
            $insert_parameters = array();        
            $insert_parameters["requester_users_id"] = $this->session->userdata("users_id");
            $insert_parameters["origin_departments_id"] = $row->departments_id;
            $insert_parameters["origin_departments_name"] = $department_name;
            $insert_parameters["origin_location"] = $row->location;
            $insert_parameters["assets_id"] = $row->assets_id;
            $insert_parameters["writeoff_type"] = $parameters["type"];
            $insert_parameters["quantity"] = $parameters["quantity"];
            $insert_parameters["remark"] = $parameters["remark"];
            $insert_parameters["status"] = "0";
            $insert_parameters["datetime_created"] = date("Y-m-d H:i:s");
            
            $this->db->insert("writeoff", $insert_parameters);
            
            $writeoff_id = $this->db->insert_id();
            
            $approver = $parameters["approvers"];
            
            ksort($approver, SORT_NUMERIC);
            $approver = array_reverse($approver, TRUE);
            
            $key = 100;
            
            foreach($approver as $approv){
                $approver_parameters = array();
                $approver_parameters["writeoff_id"] = $writeoff_id;
                $approver_parameters["users_id"] = $approv;
                $approver_parameters["priority"] = $key;
                $approver_parameters["status"] = 0;
                $approver_parameters["datetime_created"] = date("Y-m-d H:i:s");
                $this->db->insert("writeoff_approvers", $approver_parameters);
                $key += 100;
            }
            
            /* Send Email */
            $this->load->model('notification/notificationmodel');
            
            $recipients_email = array();
            $users = array();
            $first_approver_users_id = end($approver);
            
            $recipients_id = $approver;
            $recipients_id[] = $insert_parameters["requester_users_id"];
            
            $this->db->where_in("id", $recipients_id);
            $users_query = $this->db->get("users");
            
            foreach($users_query->result() as $users_row){
                $recipients_email[] = $users_row->email;
                $users[$users_row->id] = $users_row;    
            }
            
            $recipients_query = $this->db->get_where("recipients", array("departments_id" => $insert_parameters["origin_departments_id"]));
            
            foreach($recipients_query->result() as $recipient_row){
                $recipients_email[] = $recipient_row->email;   
            }
            
            $assets_info = "";
        
            /* Assets Info */
            if($insert_parameters["assets_id"]){
                $assets_query = $this->db->get_where("assets", array("id" => $insert_parameters["assets_id"]));
                if($assets_query->num_rows() > 0){
                    $assets_row = $assets_query->row();
                    
                    $assets_info_table = array();
                    $assets_info_table[] = "<table>";
                    $assets_info_table[] =      "<tr>";
                    $assets_info_table[] =          "<th style='text-align:left'>Field</th>";
                    $assets_info_table[] =          "<th style='text-align:left'>Description</th>";
                    $assets_info_table[] =      "</tr>";
                    $assets_info_table[] =      "<tr><td>Requester</td><td>" . $this->session->userdata("person_name") . "</td></tr>";
                    
                    $approver = array_values(array_reverse($approver, TRUE));
                    
                    $first_approver = "No Name";
                    
                    foreach($approver as $key=>$appr){
                            
                        if($key == 0){
                            $first_approver = $users[$appr]->person_name;
                        }    
                        
                        $person_name = "No Name";
                        if(isset($users[$appr])){
                            $person_name = $users[$appr]->person_name;
                        }
                        $assets_info_table[] = "<tr><td>Approver " . ($key + 1). "</td><td>" . $person_name . "</td></tr>"; 
                    }
                                       
                    $assets_info_table[] =      "<tr><td>Asset</td><td>" . $assets_row->assets_name . "</td></tr>";
                    $assets_info_table[] =      "<tr><td>Barcode</td><td>" . $assets_row->barcode . "</td></tr>";
                    $assets_info_table[] =      "<tr><td>Department</td><td>" . $insert_parameters["origin_departments_name"] . "</td></tr>";
                    $assets_info_table[] =      "<tr><td>Location</td><td>" . $insert_parameters["origin_location"] . "</td></tr>";
                    $assets_info_table[] =      "<tr><td>Write Off Type</td><td>" . (($insert_parameters["writeoff_type"] == "reduce_quantity")? "Reduce Quantity" : "Complete Write Off") . "</td></tr>";
                    $assets_info_table[] =      "<tr><td>Quantity</td><td>" . $insert_parameters["quantity"] . "</td></tr>";
                    $assets_info_table[] =      "<tr><td>Remarks</td><td>" . $insert_parameters["remark"] . "</td></tr>";
                    
                    $assets_info_table[] = "</table><br />";
                    $assets_info_table[] = "<b>Note: </b>" . $first_approver . ", please login to asset management system to approve/reject this request.";
                    
                    $assets_info = implode("", $assets_info_table);
                }
            }
            
            foreach($recipients_email as $email){
                
                $message = array();
                $message[] = "<!DOCTYPE html><head><meta charset='utf-8'><title>Request For Asset Write Off</title></head>";
                $message[] =    "<body>";
                $message[] =        "Dear Recipient,";
                $message[] =        "<br /><br />";
                $message[] =        $this->session->userdata("person_name") . " has requested an asset to be written off. Please login to view write off list.";
                $message[] =        "<br /><br />";
                $message[] =        $assets_info;
                $message[] =        "<br /><br />";        
                $message[] =        "Thank you and have a nice day ahead!";
                $message[] =        "<br /><br />";
                $message[] =        "Warmest Regards, <br />";
                $message[] =        "Asset Management System";
                $message[] =        "<br /><br />";
                $message[] =        "<span style='font-size: 11px'>Note: Please do not reply to this email as it is unattended</span>";
                $message[] =    "</body>";
                $message[] = "</html>";
                
                $email_parameters["receiver_email"] = $email;
                $email_parameters["email_subject"] = "Asset Management System Notification";
                $email_parameters["email_content"] = implode("", $message);
                $email_parameters["attachment_path"] = array();
                
                $this->notificationmodel->sendEmail($email_parameters);
            }
        }   
    }
    
    /* 
     * Approve write off
     *
     * $parameters["process_request"]
     * $parameters["writeoff_id"]
     * $parameters["type"] 
     * $parameters["quantity"] 
     * $parameters["remark"] 
     * $parameters["assets_departments_id"]
     * 
     * */
    function approveWriteoff($parameters){
      
      /* Update Write Off Approver Table */
      $this->db->where("writeoff_id", $parameters["writeoff_id"]);
      $this->db->where("status", "0");
      $this->db->where("users_id", $this->session->userdata("users_id"));
      $this->db->order_by("priority", "DESC");
      $approvers_query = $this->db->get("writeoff_approvers");
    
      if($approvers_query->num_rows() > 0){
            $approvers_row = $approvers_query->row();
            $writeoff_approvers_id = $approvers_row->id;
        
            $this->db->where("id", $writeoff_approvers_id);
            $this->db->update("writeoff_approvers", array("status"=>$parameters["process_request"], "datetime_approved"=>date("Y-m-d H:i:s")));
      }
      
      $status_cache = 0;
      $datetime_approved_cache = NULL;
      
      /* Check whether to update primary status */
      if($parameters["process_request"] == "-1"){
            $status_cache = $parameters["process_request"];
            $datetime_approved_cache = date("Y-m-d H:i:s");
      }else{
            $this->db->where("writeoff_id", $parameters["writeoff_id"]);
            $this->db->where("status", "0");
            $this->db->order_by("priority", "DESC");
            $approvers_query = $this->db->get("writeoff_approvers");
          
            if($approvers_query->num_rows() == 0){
                $status_cache = $parameters["process_request"];
                $datetime_approved_cache = date("Y-m-d H:i:s");           
            }
      }
      
      $parameters["status_cache"] = $status_cache;
            
      if($parameters["process_request"] == "-1"){
          /* Reject */
          $update_parameters = array();
          $update_parameters["status"] = $status_cache;
          $update_parameters["remark"] = $parameters["remark"];
          $update_parameters["approver_users_id"] = $this->session->userdata("users_id");
          $update_parameters["datetime_approved"] = $datetime_approved_cache;
          
          $this->db->where("id", $parameters["writeoff_id"]);
          $this->db->update("writeoff", $update_parameters);
          
      }else if($parameters["process_request"] == "1"){
              
          if($parameters["type"] == "reduce_quantity"){
              
              $update_parameters = array();
              $update_parameters["status"] = $status_cache;
              $update_parameters["remark"] = $parameters["remark"];
              $update_parameters["quantity"] = $parameters["quantity"];
              $update_parameters["writeoff_type"] = $parameters["type"];
              $update_parameters["approver_users_id"] = $this->session->userdata("users_id");
              $update_parameters["datetime_approved"] = $datetime_approved_cache;
              
              $this->db->where("id", $parameters["writeoff_id"]);
              $this->db->update("writeoff", $update_parameters);
              
              $departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id" => $parameters["assets_departments_id"]));
              
              /* Deduction */
              if($departments_row && ($status_cache == $parameters["process_request"])){
                    $this->db->where("id", $parameters["assets_departments_id"]);
                    $this->db->update("assets_departments", array("quantity" => (intval($departments_row["quantity"]) - intval($parameters["quantity"]))));
              }
              
          }else if($parameters["type"] == "complete_writeoff"){
              
              $update_parameters = array();
              $update_parameters["status"] = $status_cache;
              $update_parameters["remark"] = $parameters["remark"];
              $update_parameters["quantity"] = $parameters["quantity"];
              $update_parameters["writeoff_type"] = $parameters["type"];
              $update_parameters["approver_users_id"] = $this->session->userdata("users_id");
              $update_parameters["datetime_approved"] = $datetime_approved_cache;
              
              $this->db->where("id", $parameters["writeoff_id"]);
              $this->db->update("writeoff", $update_parameters);
              
              $departments_row = $this->assetmodel->getAssetLocationByAssetsDepartmentsID(array("assets_departments_id" => $parameters["assets_departments_id"]));
              
              if($departments_row && ($status_cache == $parameters["process_request"])){
                    $this->db->where("id", $parameters["assets_departments_id"]);
                    $this->db->update("assets_departments", array("quantity" => (intval($departments_row["quantity"]) - intval($parameters["quantity"]))));
                    
                    /* Change status to written off */
                    $assets_id = $departments_row["assets_id"];
                    
                    $this->db->where("assets.id", $assets_id);
                    $this->db->join("assets_departments", "assets_departments.assets_id = assets.id", "INNER");
                    $asset_query = $this->db->get("assets");
                    
                    $total_quantity = 0;
                    
                    foreach($asset_query->result() as $asset_row){
                        $total_quantity += intval($asset_row->quantity);
                    }
                    
                    if($total_quantity == 0){
                        $this->db->delete("assets_departments", array("assets_id" => $assets_id));
                        
                        $this->db->where("id", $assets_id);
                        $this->db->update("assets", array("status"=>"write_off"));
                    }else{
                        /* Remove assets_departments with 0 quantity */
                        $this->db->delete("assets_departments", array("assets_id" => $assets_id, "quantity"=>0));
                    }
              }
          }
      }   
      
      /* Send Notification */
      $this->sendApprovalNotification($parameters);
    }
    
    /* 
     * Get next approver for a write off request. Return FALSE if no next approver
     *
     * $parameters["writeoff_id"]
     * 
     * */
    function getNextApprover($parameters){
        $this->db->where("writeoff_id", $parameters["writeoff_id"]);
        $this->db->where("status", "0");
        $this->db->order_by("priority", "DESC");
        $approvers_query = $this->db->get("writeoff_approvers");
        
        if($approvers_query->num_rows() > 0){
            $approvers_row = $approvers_query->row();
            return $approvers_row->users_id;
        }
        
        return FALSE;
    }
    
    /* 
     * Write Off Notification
     *
     * $parameters["process_request"]
     * $parameters["writeoff_id"]
     * $parameters["type"] 
     * $parameters["quantity"] 
     * $parameters["remark"] 
     * $parameters["assets_departments_id"]
     * $parameters["status_cache"] //Current status
     * 
     * */
    function sendApprovalNotification($parameters){
        
        /* Get all recipients */
        $recipients_id      = array();
        $recipients_email   = array();
        $approver_list      = array();
        $department_id      = 0;
        $assets_id          = 0;
        $users              = array();
        $next_approver_id   = 0;
        
        $requester_name     = "";
        $departments_name   = "";
        $location_name      = "";
        $writeoff_type      = "";
        $quantity           = "";
        $remark             = "";
        
        $writeoff_query = $this->db->get_where("writeoff", array("id"=>$parameters["writeoff_id"]));
        
        if($writeoff_query->num_rows() > 0){
            $writeoff_row = $writeoff_query->row();
            $recipients_id[] = $writeoff_row->requester_users_id;
            $recipients_id[] = $writeoff_row->approver_users_id;
            $assets_id = $writeoff_row->assets_id;
            $department_id = $writeoff_row->origin_departments_id;
            $departments_name = $writeoff_row->origin_departments_name;
            $location_name = $writeoff_row->origin_location;
            $writeoff_type = ($writeoff_row->writeoff_type == "reduce_quantity")? "Reduce Quantity" : "Complete Write Off";
            $quantity = $writeoff_row->quantity;
            $remark = $writeoff_row->remark;
            
            /* Get approver list and next approver */
            $this->db->where("writeoff_id", $parameters["writeoff_id"]);
            $this->db->order_by("priority", "DESC");
            $approvers_query = $this->db->get("writeoff_approvers");
            
            foreach($approvers_query->result() as $approver){
                $recipients_id[] = $approver->users_id;
                $approver_list[] = $approver;
                
                if(!$next_approver_id && ($approver->status == 0)){
                    $next_approver_id = $approver->users_id;
                }
            }
            
            if(count($recipients_id) > 0){
                $this->db->select("id, person_name, email");
                $this->db->where_in("id", $recipients_id);
                $users_query = $this->db->get("users");
                foreach($users_query->result() as $users_row){
                    $recipients_email[] = $users_row->email;
                    
                    if($writeoff_row->requester_users_id == $users_row->id){
                        $requester_name = $users_row->person_name;
                    }
                    $users[$users_row->id] = $users_row;
                }
            }
        }
        
        $this->load->model('notification/notificationmodel');
            
        if($department_id){    
            $recipients_query = $this->db->get_where("recipients", array("departments_id" => $department_id));
            foreach($recipients_query->result() as $recipients_row){
                $recipients_email[] = $recipients_row->email;
            }
        }
        
        $recipients_email = array_unique($recipients_email);
        
        $title = "REJECTED: Request For Asset Write Off Has Been Rejected";
        $email_subject = "Asset Management System Notification: Asset Writeoff Request Rejected";
        $approval_status = " has <b style='color:red'>rejected</b> a write off request.";
        $approver_label = "Rejecter";
        
        if($parameters["process_request"] == "1"){
            $title = "Approved: Request For Asset Write Off Has Been Approved";
            $email_subject = "Asset Management System Notification: Asset Writeoff Request Approved";
            $approval_status = " has <b style='color:green'>approved</b> a write off request.";
            $approver_label = "Approver";    
        }
        
        $assets_info = "";
        
        /* Assets Info */
        if($assets_id){
            $assets_query = $this->db->get_where("assets", array("id" => $assets_id));
            if($assets_query->num_rows() > 0){
                $assets_row = $assets_query->row();
                
                $assets_info_table = array();
                $assets_info_table[] = "<table>";
                $assets_info_table[] =      "<tr>";
                $assets_info_table[] =          "<th style='text-align:left'>Field</th>";
                $assets_info_table[] =          "<th style='text-align:left'>Description</th>";
                $assets_info_table[] =      "</tr>";
                $assets_info_table[] =      "<tr><td>Requester</td><td>" . $requester_name . "</td></tr>";
                
                foreach($approver_list as $key=>$approver){
                    switch($approver->status){
                        case "0": 
                                $approver_name = isset($users[$approver->users_id])? $users[$approver->users_id]->person_name : "No Name";
                                $assets_info_table[] = "<tr><td>Approver " . ($key+1) . "</td><td>" . $approver_name . " - <span style='color:red'>Pending</span></td></tr>";
                                break;
                        case "1": 
                                $approver_name = isset($users[$approver->users_id])? $users[$approver->users_id]->person_name : "No Name";
                                $assets_info_table[] = "<tr><td>Approver " . ($key+1) . "</td><td>" . $approver_name . " - <span style='color:green'>Approved</span></td></tr>";
                                break;
                        case "-1": 
                                $approver_name = isset($users[$approver->users_id])? $users[$approver->users_id]->person_name : "No Name";
                                $assets_info_table[] = "<tr><td>Approver " . ($key+1) . "</td><td>" . $approver_name . " - <span style='color:gray'>Rejected</span></td></tr>";
                                break;
                    }
                }
                
                $assets_info_table[] =      "<tr><td>Asset</td><td>" . $assets_row->assets_name . "</td></tr>";
                $assets_info_table[] =      "<tr><td>Barcode</td><td>" . $assets_row->barcode . "</td></tr>";
                $assets_info_table[] =      "<tr><td>Department</td><td>" . $departments_name . "</td></tr>";
                $assets_info_table[] =      "<tr><td>Location</td><td>" . $location_name . "</td></tr>";
                $assets_info_table[] =      "<tr><td>Write Off Type</td><td>" . $writeoff_type . "</td></tr>";
                $assets_info_table[] =      "<tr><td>Quantity</td><td>" . $quantity . "</td></tr>";
                $assets_info_table[] =      "<tr><td>Remarks</td><td>" . $remark . "</td></tr>";
                
                $assets_info_table[] = "</table><br/>";
                
                $next_approver_name = "No Name";
                
                if(isset($users[$next_approver_id])){
                    $next_approver_name = $users[$next_approver_id]->person_name;
                }
                
                switch($parameters["status_cache"]){
                    case "0":
                            $assets_info_table[] = "<b>Note: </b>" . $next_approver_name . ", please login to asset management system to approve/reject this request."; 
                            break;
                    case "1": 
                            $assets_info_table[] = "<b>Note: </b> This asset has been successfully written off and the request has been closed.";
                            break;
                    case "-1": 
                            $assets_info_table[] = "<b>Note: </b> This request has been rejected and closed.";
                            break;
                }
                
                $assets_info = implode("", $assets_info_table);
            }
        }
        
         
        foreach($recipients_email as $email){
            
            $message = array();
            $message[] = "<!DOCTYPE html><head><meta charset='utf-8'><title>" . $title . "</title></head>";
            $message[] =    "<body>";
            $message[] =        "Dear Recipient,";
            $message[] =        "<br /><br />";
            $message[] =        $this->session->userdata("person_name") . $approval_status;
            $message[] =        "<br /><br />";     
            $message[] =        $assets_info;   
            $message[] =        "<br /><br />Thank you and have a nice day ahead!";
            $message[] =        "<br /><br />";
            $message[] =        "Warmest Regards, <br />";
            $message[] =        "Asset Management System";
            $message[] =        "<br /><br />";
            $message[] =        "<span style='font-size: 11px'>Note: Please do not reply to this email as it is unattended</span>";
            $message[] =    "</body>";
            $message[] = "</html>";
            
            $email_parameters["receiver_email"] = $email;
            $email_parameters["email_subject"] = $email_subject;
            $email_parameters["email_content"] = implode("", $message);
            $email_parameters["attachment_path"] = array();
            
            $this->notificationmodel->sendEmail($email_parameters);   
        }
    }
    
    function getWriteOffByID($writeoff_id){
        $query = $this->db->get_where("writeoff", array("id" => $writeoff_id));
        
        if($query->num_rows() > 0){
            return $query->row_array();
        }
        
        return FALSE;
    }
    
}
