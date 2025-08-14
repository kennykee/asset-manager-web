<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assetdetailreportmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->helper('file');
        $this->load->helper('text');
        $this->load->model("attachment/attachmentmodel");
        require_once 'application/libraries/phpexcel/PHPExcel.php';
        require_once 'application/libraries/phpexcel/AssetImportFilter.php';
    }
    
    /* 
     * Download excel report for one or more asset. One category one sheet 
     *
     * $parameters["start_date"] = optional
     * $parameters["end_date"] = optional
     * $parameters["assets"] = array()
     * $parameters["categories"] = array(); //Optional filter. If present, exclude assets not within selected categories.
     * 
     * */
    function downloadAssetDetailReport($parameters){
        
        $parameters["assets"] = array_unique($parameters["assets"]);
        $categories = array();
        $assets_categories = array();
        $assets_id = array();
        $assets_data = array();
        $categories_id = array();
        $categories_names = array();
        $departments_id = array();
        $departments_names = array();
        
        if($parameters["categories"]){
            $this->db->select("assets_id, categories_id");
            $this->db->distinct();
            $this->db->order_by("assets_id", "ASC");
            $this->db->where_in("assets_id", $parameters["assets"]);
            $this->db->where_in("categories_id", $parameters["categories"]);
            $query = $this->db->get("assets_categories");
            
            foreach($query->result_array() as $row){
                $categories[$row["categories_id"]][] = $row["assets_id"];
                $assets_id[] = $row["assets_id"];
                $categories_id[] = $row["categories_id"];
                $assets_categories[$row["assets_id"]][] = $row["categories_id"];
            }
        }
        
        if(count($categories_id) > 0){
            $this->db->where_in("id", $categories_id);
            $categories_query = $this->db->get("categories");
            foreach($categories_query->result_array() as $categories_row){
                $categories_names[$categories_row["id"]] = $categories_row["categories_name"];
            }
        }
        
        if(count($assets_id) == 0){
            echo "No record found";
            return FALSE;
        }
        
        /* Retrieving data for assets */
        $this->db->where_in("id", $assets_id);
        $assets_query = $this->db->get("assets");
        
        foreach($assets_query->result_array() as $assets_row){
            $assets_data[$assets_row["id"]] = $assets_row;
        }
        
        /* Retrieve upcoming maintenance */
        $maintenance = array();
        $this->db->where_in("assets_id", $assets_id);
        $this->db->where("maintenance_date >= CURDATE()");
        $maintenance_query = $this->db->get("assets_maintenance");
        
        foreach($maintenance_query->result_array() as $maintenance_row){
            $maintenance[$maintenance_row["assets_id"]][] = date("d-F-Y", strtotime($maintenance_row["maintenance_date"]));
        }
        
        /* Location and quantity */
        $assets_department_quantity = array();
        $assets_location_quantity = array();
        $assets_quantity = array();
        $this->db->where_in("assets_id", $assets_id);
        $quantity_query = $this->db->get("assets_departments");
        
        foreach($quantity_query->result_array() as $quantity_row){
            if(isset($assets_department_quantity[$quantity_row["assets_id"]][$quantity_row["departments_id"]])){
                $assets_department_quantity[$quantity_row["assets_id"]][$quantity_row["departments_id"]] += intval($quantity_row["quantity"]);
            }else{
                $assets_department_quantity[$quantity_row["assets_id"]][$quantity_row["departments_id"]] = intval($quantity_row["quantity"]);   
            }
            if(isset($assets_location_quantity[$quantity_row["assets_id"]][$quantity_row["location"]])){
                $assets_location_quantity[$quantity_row["assets_id"]][$quantity_row["location"]] += intval($quantity_row["quantity"]);
            }else{
                $assets_location_quantity[$quantity_row["assets_id"]][$quantity_row["location"]] = intval($quantity_row["quantity"]);   
            }
            if(isset($assets_quantity[$quantity_row["assets_id"]])){
                $assets_quantity[$quantity_row["assets_id"]] += intval($quantity_row["quantity"]);
            }else{
                $assets_quantity[$quantity_row["assets_id"]] = intval($quantity_row["quantity"]);   
            }
            $departments_id[] = $quantity_row["departments_id"];
        }

        if(count($departments_id) > 0){
            $this->db->where_in("id", $departments_id);
            $departments_query = $this->db->get("departments");
            
            foreach($departments_query->result_array() as $departments_row){
                $departments_names[$departments_row["id"]] = $departments_row["departments_name"];
            }
        }
        
        /* Header */
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Detailed Report For Each Asset")
                             ->setSubject("Detailed Report For Each Asset")
                             ->setDescription("This document describes information for selected assets")
                             ->setKeywords("asset report")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A3"=>9, "B3"=>35, "C3"=>30, "D3"=>14, "E3"=>14, "F3"=>14, "G3"=>17, "H3"=>16, "I3"=>16, "J3"=>15, 
                        "K3"=>15, "L3"=>15, "M3"=>14, "N3"=>15, "O3"=>15, "P3"=>12, "Q3"=>17, "R3"=>12, "S3"=>12, "T3"=>20, "U3"=>20,
                        "V3"=>9, "W3"=>20);
                        
        $sheet_index = 0;
        
        foreach($categories as $categories_id => $assets){
            
            $current_row_count = 0;
                
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_index);
            
            $objWorkSheet->setTitle(isset($categories_names[$categories_id])? ellipsize(remove_invalid_sheet_title_character($categories_names[$categories_id]),31,1,"") : "No Category Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(50);
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(50);
            
            $objWorkSheet->mergeCells("B1:F1");
            $objWorkSheet->getStyle('B1:F1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));
            
            $objWorkSheet->mergeCells("T2:V2");
            $objWorkSheet->getStyle('T2:X2')->applyFromArray(array('font' => array('bold' => true,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            }
            
            $current_row_count++;
            
            $objWorkSheet
                ->setCellValue('B1', 'Asset Detail Report')
                ->setCellValue('T2', "Asset\nLocation")
                ->setCellValue('W2', "Upcoming\nMaintenance")
                ->setCellValue('A' . $current_row_count, 'No')
                ->setCellValue('B' . $current_row_count, 'Photo')
                ->setCellValue('C' . $current_row_count, 'Asset Name')
                ->setCellValue('D' . $current_row_count, 'Asset ID (Barcode)')
                ->setCellValue('E' . $current_row_count, 'Enable Asset Tracking')
                ->setCellValue('F' . $current_row_count, 'Assets Unit Price')
                ->setCellValue('G' . $current_row_count, 'Total Depreciation')
                ->setCellValue('H' . $current_row_count, 'Lifespan (Months)')
                ->setCellValue('I' . $current_row_count, 'Maintenance Interval (Months)')
                ->setCellValue('J' . $current_row_count, 'Serial Number')
                ->setCellValue('K' . $current_row_count, 'Category')
                ->setCellValue('L' . $current_row_count, 'Supplier')
                ->setCellValue('M' . $current_row_count, 'Brand')
                ->setCellValue('N' . $current_row_count, 'Salvage Value')
                ->setCellValue('O' . $current_row_count, 'Warranty Expiry')
                ->setCellValue('P' . $current_row_count, 'Invoice Number')
                ->setCellValue('Q' . $current_row_count, 'Invoice Date')
                ->setCellValue('R' . $current_row_count, 'Status')
                ->setCellValue('S' . $current_row_count, 'Remarks')
                ->setCellValue('T' . $current_row_count, 'Department')
                ->setCellValue('U' . $current_row_count, 'Location')
                ->setCellValue('V' . $current_row_count, 'Quantity')
                ->setCellValue('W' . $current_row_count, 'Maintenance Date')
                ;
            
            foreach($assets as $no_count=>$asset){
                
                $current_row_count++;
                
                $objWorkSheet
                ->setCellValue('A' . $current_row_count, $no_count + 1)
                ->setCellValue('B' . $current_row_count, '')
                ->setCellValue('C' . $current_row_count, 'No Asset Name')
                ->setCellValue('D' . $current_row_count, 'No Barcode')
                ->setCellValue('E' . $current_row_count, 'No Tracking Data')
                ->setCellValue('F' . $current_row_count, 'No Unit Price')
                ->setCellValue('G' . $current_row_count, '-')
                ->setCellValue('H' . $current_row_count, 'No Lifespan')
                ->setCellValue('I' . $current_row_count, 'No Maintenance')
                ->setCellValue('J' . $current_row_count, 'No Serial Number')
                ->setCellValue('K' . $current_row_count, isset($categories_names[$categories_id])? $categories_names[$categories_id] : "No Category Name")
                ->setCellValue('L' . $current_row_count, 'No Supplier')
                ->setCellValue('M' . $current_row_count, 'No Brand')
                ->setCellValue('N' . $current_row_count, 'No Salvage Value')
                ->setCellValue('O' . $current_row_count, 'No Warranty Expiry')
                ->setCellValue('P' . $current_row_count, 'No Invoice Number')
                ->setCellValue('Q' . $current_row_count, 'No Invoice Date')
                ->setCellValue('R' . $current_row_count, 'No Status')
                ->setCellValue('S' . $current_row_count, 'No Remarks')
                ->setCellValue('T' . $current_row_count, 'No Department')
                ->setCellValue('U' . $current_row_count, 'No Location')
                ->setCellValue('V' . $current_row_count, 'No Quantity')
                ->setCellValue('W' . $current_row_count, 'No Maintenance Date')
                ;
                
                if(isset($assets_data[$asset])){
                    
                    $assets_row = $assets_data[$asset];
                    
                    /* Preprocess */
                    $assets_row["warranty_expiry"] = date("d-F-Y", strtotime($assets_row["warranty_expiry"]));
                    if(intval(date("Y", strtotime($assets_row["warranty_expiry"]))) <= 1990){
                        $assets_row["warranty_expiry"] = "";           
                    }
                    
                    $assets_row["invoice_date"] = date("d-F-Y", strtotime($assets_row["invoice_date"]));
                    if(intval(date("Y", strtotime($assets_row["invoice_date"]))) <= 1990){
                        $assets_row["invoice_date"] = "";           
                    }
                    
                    $end_date = date("d-F-Y", strtotime($parameters["end_date"]));
                    if(intval(date("Y", strtotime($end_date))) <= 1990){
                        $end_date = "";           
                    }
                    
                    switch ($assets_row["status"]) {
                        case 'available'    : $assets_row["status"] = "Available"; break;
                        case 'write_off'    : $assets_row["status"] = "Written Off"; break;
                        case 'loan_out'     : $assets_row["status"] = "On Loan"; break;
                        case 'out_of_stock': $assets_row["status"] = "Out Of Stock"; break;
                        case 'maintenance': $assets_row["status"] = "Maintenance"; break;
                        case 'unavailable': $assets_row["status"] = "Not Available"; break;
                    }
                    
                    $assets_row["category"] = "";
                    
                    if(isset($assets_categories[$assets_row["id"]])){
                        $assets_category_row = $assets_categories[$assets_row["id"]];
                        foreach($assets_category_row as $acr){
                            if(isset($categories_names[$acr])){
                                if(strlen($assets_row["category"]) > 0){
                                    $assets_row["category"] .= ",\n";
                                }
                                $assets_row["category"] .= $categories_names[$acr];   
                            }
                        }
                    }
                    
                    $assets_row["maintenance_date"] = "";
                    
                    if(isset($maintenance[$assets_row["id"]])){
                        $maintenance_row = $maintenance[$assets_row["id"]];
                        foreach($maintenance_row as $mr){
                            if(strlen($assets_row["maintenance_date"]) > 0){
                                $assets_row["maintenance_date"] .= ",\n";
                            }
                            $assets_row["maintenance_date"] .= $mr;
                        }
                    }
                    
                    $image_path = $this->attachmentmodel->get_full_path($assets_row["attachments_id"], "showall", 350, 350);
                    
                    /* Depreciation */
                    $assets_row["depreciation"] = "";
                    $assets_value = floatval($assets_row["assets_value"]);
                    $salvage_value = floatval($assets_row["salvage_value"]);
                    $lifespan = intval($assets_row["assets_lifespan"]);
                    $days_per_month = 30.4375;
                    
                    if($assets_row["invoice_date"] && $end_date && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                        
                        $invoice_timestamp = strtotime($assets_row["invoice_date"]);
                        $end_date = strtotime($end_date);
                        
                        $total_time = $lifespan * $days_per_month * 24 * 3600;
                        $time_diff = $end_date - $invoice_timestamp + (24 * 3600); /* Inclusive start day */
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        if($time_diff < 0){
                            $time_diff = 0;
                        }
                        $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                        
                        $assets_row["depreciation"] = $depreciation;
                        
                        /* Set header column name */
                        $objWorkSheet->setCellValue('G3', "Total Depreciation Till\n" . $parameters["end_date"]);
                    }

                    /* Quantity */
                    $assets_row["quantity"] = 0;
                    if(isset($assets_quantity[$assets_row["id"]])){
                        $assets_row["quantity"] = $assets_quantity[$assets_row["id"]];
                    }
                    
                    /* Quantity - Departments */
                    $assets_row["quantity_department"] = "";
                    if(isset($assets_department_quantity[$assets_row["id"]])){
                        foreach($assets_department_quantity[$assets_row["id"]] as $dep_id => $quantity){
                            if(strlen($assets_row["quantity_department"]) > 0){
                                $assets_row["quantity_department"] .= ",\n";
                            }    
                            $assets_row["quantity_department"] .= (isset($departments_names[$dep_id])? $departments_names[$dep_id] : "No Department") . " - " . $quantity; 
                        }
                    }
                    
                    /* Quantity - Location */
                    $assets_row["quantity_location"] = "";
                    if(isset($assets_location_quantity[$assets_row["id"]])){
                        foreach($assets_location_quantity[$assets_row["id"]] as $location => $quantity){
                            if(strlen($assets_row["quantity_location"]) > 0){
                                $assets_row["quantity_location"] .= ",\n";
                            }    
                            $assets_row["quantity_location"] .= $location . " - " . $quantity; 
                        }
                    }
            
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Asset Image');
                    $objDrawing->setDescription('Asset Image');
                    $objDrawing->setPath($image_path);            
                    $objDrawing->setCoordinates('B' . $current_row_count);
                    $objDrawing->setWidth(150);
                    $objDrawing->setHeight(132);
                    $objDrawing->setResizeProportional(true);
                    $objDrawing->setWorksheet($objWorkSheet);    
                    
                    $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(100);
                    
                    $objWorkSheet
                    ->setCellValue('A' . $current_row_count, $no_count + 1)
                    ->setCellValue('C' . $current_row_count, $assets_row["assets_name"])
                    ->setCellValue('D' . $current_row_count, $assets_row["barcode"])
                    ->setCellValue('E' . $current_row_count, $assets_row["enable_tracking"]? "Yes" : "No Tracking")
                    ->setCellValue('F' . $current_row_count, $assets_row["assets_value"]? $assets_row["assets_value"] : "No Asset Value")
                    ->setCellValue('G' . $current_row_count, $assets_row["depreciation"])
                    ->setCellValue('H' . $current_row_count, $assets_row["assets_lifespan"]? $assets_row["assets_lifespan"] . " months" : "No Lifespan")
                    ->setCellValue('I' . $current_row_count, $assets_row["maintenance_interval"]? $assets_row["maintenance_interval"] . " months" : "No Maintenance")
                    ->setCellValue('J' . $current_row_count, $assets_row["serial_number"])
                    ->setCellValue('K' . $current_row_count, $assets_row["category"])
                    ->setCellValue('L' . $current_row_count, $assets_row["supplier_name"])
                    ->setCellValue('M' . $current_row_count, $assets_row["brand"])
                    ->setCellValue('N' . $current_row_count, $assets_row["salvage_value"]? $assets_row["salvage_value"] : "No Salvage Value")
                    ->setCellValue('O' . $current_row_count, $assets_row["warranty_expiry"])
                    ->setCellValue('P' . $current_row_count, $assets_row["invoice_number"])
                    ->setCellValue('Q' . $current_row_count, $assets_row["invoice_date"])
                    ->setCellValue('R' . $current_row_count, $assets_row["status"])
                    ->setCellValue('S' . $current_row_count, $assets_row["remarks"])
                    ->setCellValue('T' . $current_row_count, $assets_row["quantity_department"])
                    ->setCellValue('U' . $current_row_count, $assets_row["quantity_location"])
                    ->setCellValue('V' . $current_row_count, $assets_row["quantity"])
                    ->setCellValue('W' . $current_row_count, $assets_row["maintenance_date"])
                    ;                            
                }
            }
            
            /* Align Center */  
            $objWorkSheet->getStyle('A2:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);         
        }
        
        /* Footer */                            
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', TRUE);
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - HFC Assets Detailed Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');                               
    }
}
