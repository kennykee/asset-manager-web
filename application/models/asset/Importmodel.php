<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Importmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->model("attachment/attachmentmodel");
        $this->load->helper('file');
        require_once 'application/libraries/phpexcel/PHPExcel.php';
        require_once 'application/libraries/phpexcel/AssetImportFilter.php';
    }
    
    /* 
     * Retrieve row from an excel 
     *
     * $parameters["attachments_id"]
     * $parameters["row_no"] 
     * 
     * */
    function getExcelRow($parameters){
        
        $data = array();
        
        $attachment_id = $this->uri->segment(5);
        
        if(($attachment_id && is_numeric($attachment_id) && (intval($attachment_id) >= 1))){
            
            $this->attachment = $this->attachmentmodel->getAttachmentSimpleInfo($attachment_id);
           
            if(!$this->attachment || !$this->attachment->status){ 
                return $data;
            }
            
            $source_file = $this->config->item("data_folder") . $this->attachment->full_path;
            
            $objReader = new PHPExcel_Reader_Excel2007();
            //$objReader->setReadDataOnly(true);
            
            if(!$objReader->canRead($source_file)){
                return $data;
            }
            
            $reading_row = intval($parameters["row_no"]);
            
            $assetImportFilter = new AssetImportFilter();
            $assetImportFilter->setRow($reading_row);
            
            $objReader->setReadFilter($assetImportFilter);
            $objPHPExcel = $objReader->load($source_file);
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            
            if(isset($sheetData[$reading_row])){
                
                $row = $sheetData[$reading_row];
                
                if(strlen($row["C"]) == 0){
                    /* No Data */
                    return $data;
                }
                
                $data["attachments_id"] = $row["R"]; //process
                $data["assets_name"] = $row["C"];
                $data["barcode"] = "New Asset";
                $data["enable_tracking"] = $row["F"];
                $data["assets_value"] = $row["G"];
                $data["assets_lifespan"] = $row["H"];
                $data["maintenance_interval"] = $row["O"];
                $data["serial_number"] = $row["I"];
                $data["categories"] = $row["B"]; //array()
                $data["departments"] = $row["A"]; //array()
                $data["locations"] = array($row["D"]);
                $data["quantity"] = array($row["E"]); 
                $data["supplier_name"] = $row["L"];
                $data["brand"] = $row["J"];
                $data["salvage_value"] = $row["K"];
                $data["warranty_expiry"] = $row["Q"];//process
                $data["invoice_number"] = $row["M"];
                $data["invoice_date"] = $row["N"]; //process
                $data["status"] = "available";
                $data["remarks"] = $row["S"];
                $data["next_maintenance"] = $row["P"]; //process
                
                /* Preprocess data */
                
                /* Asset Value */
                
                $data["assets_value"] = preg_replace("/[^0-9.]/", "", $data["assets_value"]);
                $data["assets_value"] = number_format($data["assets_value"], 2, ".", "");
                
                /* Salvage Value */
                
                $data["salvage_value"] = preg_replace("/[^0-9.]/", "", $data["salvage_value"]);
                $data["salvage_value"] = number_format($data["salvage_value"], 2, ".", "");
                
                /* Attachment */
                $this->db->like("file_name", $data["attachments_id"], 'both');
                $this->db->where("attachment_type", "image");
                $this->db->where("status", "1");
                $this->db->order_by("id", "desc");
                $this->db->limit(1);
                $image_query = $this->db->get("attachments");
                
                if($image_query->num_rows() > 0){
                    $image_row = $image_query->row();
                    $data["attachments_id"] = $image_row->id;
                }else{
                    $data["attachments_id"] = 0;
                }
                
                /* Categories */
                $this->db->like("categories_name", $data["categories"], 'both');
                $this->db->order_by("id", "desc");
                $this->db->limit(1);
                $categories_query = $this->db->get("categories");
                
                if($categories_query->num_rows() > 0){
                    $categories_row = $categories_query->row();
                    $data["categories"] = array($categories_row->id);
                }else{
                    $data["categories"] = array();
                }
                
                /* Departments */
                $this->db->like("departments_name", $data["departments"], 'both');
                $this->db->order_by("id", "desc");
                $this->db->limit(1);
                $departments_query = $this->db->get("departments");
                
                if($departments_query->num_rows() > 0){
                    $departments_row = $departments_query->row();
                    $data["departments"] = array($departments_row->id);
                }else{
                    $data["departments"] = array();
                }
                
                $date = extract_date_return_timestamp($data["warranty_expiry"]);
                
                if($date){
                    $data["warranty_expiry"] = date("d-F-Y", $date);
                }else{
                    $data["warranty_expiry"] = "";
                }
                
                $date = extract_date_return_timestamp($data["invoice_date"]);
                
                if($date){
                    $data["invoice_date"] = date("d-F-Y", $date);
                }else{
                    $data["invoice_date"] = "";
                }
                
                $date = extract_date_return_timestamp($data["next_maintenance"]);
                
                if($date){
                    $data["next_maintenance"] = date("d-F-Y", $date);
                }else{
                    $data["next_maintenance"] = "";
                }
            }
        }   
       
        return $data;
    }
}
