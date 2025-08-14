<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assetlistreportmodel extends CI_Model {
    
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
     * $parameters["departments"]
     * $parameters["categories"] = array(); //Optional filter. If present, exclude assets not within selected categories.
     * 
     * */
    function downloadAssetListReport($parameters){
        
        $parameters["assets"] = array_unique($parameters["assets"]);
        $categories = array();
        $assets_categories = array();
        $assets_id = array();
        $assets_data = array();
        $categories_id = array();
        $categories_names = array();
        $departments_id = array();
        $departments_names = array();
        $track_list = array();
        
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
        
        /* Last Scan DateTime*/
        if(count($assets_id) > 0){
            
            $assets_sql_string = implode(",", $assets_id);
            $departments_sql_string = implode(",", $parameters["departments"]);
            
            $query_string = "SELECT tracking.* FROM assets_tracking tracking 
                         INNER JOIN
                         (
                         SELECT MAX(datetime_scanned) as datetime_scanned, assets_id, departments_id FROM 
                         assets_tracking 
                         WHERE assets_id IN (" . $assets_sql_string . ") AND departments_id IN (" . $departments_sql_string . ")
                         group by assets_id, departments_id
                         ) track_latest
                           ON (tracking.assets_id = track_latest.assets_id AND 
                               tracking.departments_id = track_latest.departments_id AND
                               tracking.datetime_scanned = track_latest.datetime_scanned)";
            
            $track_query = $this->db->query($query_string);
            
            foreach($track_query->result_array() as $track_row){
                $track_list[$track_row["assets_id"]][$track_row["departments_id"]] = $track_row["datetime_scanned"];
            }
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
        
        $start_date = date("d-F-Y", strtotime($parameters["start_date"]));
        if(intval(date("Y", strtotime($start_date))) <= 1990){
            $start_date = "";           
        }
        
        $end_date = date("d-F-Y", strtotime($parameters["end_date"]));
        if(intval(date("Y", strtotime($end_date))) <= 1990){
            $end_date = "";           
        }
        
        /* Header */
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Report For Asset List")
                             ->setSubject("Report For Asset List")
                             ->setDescription("This document describes information for selected categories")
                             ->setKeywords("asset report")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A2"=>9, "B2"=>15, "C2"=>20, "D2"=>20, "E2"=>20, "F2"=>12, "G2"=>18, "H2"=>17, "I2"=>18, "J2"=>18, 
                        "K2"=>24, "L2"=>18, "M2"=>18, "N2"=>18, "O2"=>18, "P2"=>20, "Q2"=>18, "R2"=>18, "S2"=>18, "T2"=>18, "U2"=>18,
                        "V2"=>18, "W2"=>18, "X2"=>18, "Y2"=>15, "Z2"=>18);
                        
        $sheet_index = 0;
        
        foreach($categories as $categories_id => $assets){
            
            $current_row_count = 0;
                
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_index);
            
            $objWorkSheet->setTitle(isset($categories_names[$categories_id])? ellipsize(remove_invalid_sheet_title_character($categories_names[$categories_id]),31,1,"") : "No Category Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(50);
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(35);
            
            $objWorkSheet->mergeCells("A1:F1");
            $objWorkSheet->getStyle('A1:F1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));                
            }
            
            $objWorkSheet->getColumnDimension("AA")->setWidth(27);    
            $objWorkSheet->getStyleByColumnAndRow(26,2)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            
            $objWorkSheet
                ->setCellValue('A1', 'Asset List Report')
                ->setCellValue('A' . $current_row_count, 'No')
                ->setCellValue('B' . $current_row_count, 'Asset ID (Barcode)')
                ->setCellValue('C' . $current_row_count, 'Asset Name')
                ->setCellValue('D' . $current_row_count, 'Department')
                ->setCellValue('E' . $current_row_count, 'Location')
                ->setCellValue('F' . $current_row_count, 'Quantity')
                ->setCellValue('G' . $current_row_count, 'Enable Asset Tracking')
                ->setCellValue('H' . $current_row_count, 'Assets Unit Price')
                ->setCellValue('I' . $current_row_count, "Total Asset Value\n(Value x Quantity)")
                ->setCellValue('J' . $current_row_count, 'Accumulated Depreciation')
                ->setCellValue('K' . $current_row_count, 'Depreciation Between Range')
                ->setCellValue('L' . $current_row_count, 'Total Depreciation Amount')
                ->setCellValue('M' . $current_row_count, 'Netbook Value')
                ->setCellValue('N' . $current_row_count, 'Current Depreciation')
                ->setCellValue('O' . $current_row_count, 'Lifespan (Months)')
                ->setCellValue('P' . $current_row_count, 'Maintenance Interval (Months)')
                ->setCellValue('Q' . $current_row_count, 'Serial Number')
                ->setCellValue('R' . $current_row_count, 'Category')
                ->setCellValue('S' . $current_row_count, 'Supplier')
                ->setCellValue('T' . $current_row_count, 'Brand')
                ->setCellValue('U' . $current_row_count, 'Salvage Value')
                ->setCellValue('V' . $current_row_count, 'Warranty Expiry')
                ->setCellValue('W' . $current_row_count, 'Invoice Number')
                ->setCellValue('X' . $current_row_count, 'Invoice Date')
                ->setCellValue('Y' . $current_row_count, 'Status')
                ->setCellValue('Z' . $current_row_count, 'Remarks')
                ->setCellValue("AA" . $current_row_count, "Last Scan \nDateTime")
                ;
            
            $category_asset_unit_price = 0;
            $category_total_asset_value = 0;
            $category_total_accumulated_depreciation = 0;
            $category_total_depreciation_between = 0;
            $category_total_depreciation_amount = 0;
            $category_total_netbook_value = 0;
            $category_total_current_depreciation = 0;
            $category_total_salvage_value = 0;
            
            $no_count = 0;
            
            foreach($assets as $asset){
                
                if(isset($assets_data[$asset])){
                
                    $current_row_count++;
                    
                    $assets_row = $assets_data[$asset];
                    
                    switch ($assets_row["status"]) {
                        case 'available'    : $assets_row["status"] = "Available"; break;
                        case 'write_off'    : $assets_row["status"] = "Written Off"; break;
                        case 'loan_out'     : $assets_row["status"] = "On Loan"; break;
                        case 'out_of_stock' : $assets_row["status"] = "Out Of Stock"; break;
                        case 'maintenance'  : $assets_row["status"] = "Maintenance"; break;
                        case 'unavailable'  : $assets_row["status"] = "Not Available"; break;
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
                    
                    $assets_row["last_scan"] = "";
                    /* Last scan date time */
                    if(isset($track_list[$assets_row["id"]])){
                        $last_scan = $track_list[$assets_row["id"]];
                        foreach($last_scan as $scan_key=>$scan){
                            if(strlen($assets_row["last_scan"]) > 0){
                                $assets_row["last_scan"] .= ",\n";
                            }  
                            $assets_row["last_scan"] .= (isset($departments_names[$scan_key])? $departments_names[$scan_key] : "No Department") . " - " . date("d-F-Y", strtotime($scan));
                        }
                    }
                    
                    
                    $assets_row["warranty_expiry"] = date("d-F-Y", strtotime($assets_row["warranty_expiry"]));
                    if(intval(date("Y", strtotime($assets_row["warranty_expiry"]))) <= 1990){
                        $assets_row["warranty_expiry"] = "";           
                    }
                    
                    $assets_row["invoice_date"] = date("d-F-Y", strtotime($assets_row["invoice_date"]));
                    if(intval(date("Y", strtotime($assets_row["invoice_date"]))) <= 1990){
                        $assets_row["invoice_date"] = "";           
                    }
                    
                    $row_asset_unit_price = $assets_row["assets_value"]? floatval($assets_row["assets_value"]) : 0;
                    $row_total_asset_value = $assets_row["assets_value"]? (floatval($assets_row["assets_value"]) * intval($assets_row["quantity"])) : 0;
                    $row_total_accumulated_depreciation = 0;
                    $row_total_depreciation_between = 0;
                    $row_total_depreciation_amount = 0;
                    $row_total_netbook_value = 0;
                    $row_total_current_depreciation = 0;
                    $row_total_salvage_value = $assets_row["salvage_value"]? (floatval($assets_row["salvage_value"]) * intval($assets_row["quantity"])) : 0;
                    
                    $assets_value = floatval($assets_row["assets_value"]);
                    $salvage_value = floatval($assets_row["salvage_value"]);
                    $lifespan = intval($assets_row["assets_lifespan"]);
                    $days_per_month = 30.4375;
                    
                    $start_timestamp = strtotime($start_date);
                    $end_timestamp = strtotime($end_date);
                    $invoice_timestamp = strtotime($assets_row["invoice_date"]);
                    $total_time = $lifespan * $days_per_month * 24 * 3600;
                    
                    /* Calculation - Accumulated Depreciation */
                    if($assets_row["invoice_date"] && ($assets_value > 0) && ($lifespan > 0) && $start_date && ($salvage_value >= 0)){
                        
                        $time_diff = $start_timestamp - $invoice_timestamp;
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        if($time_diff < 0){
                            $time_diff = 0;
                        }                            
                        $row_total_accumulated_depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time) * intval($assets_row["quantity"]), 2, ".", "");
                    }
                    
                    /* Calculation - Depreciation Between */
                    if($assets_row["invoice_date"] && ($assets_value > 0) && ($lifespan > 0) && $start_date && $end_date && ($salvage_value >= 0) && 
                        ($end_timestamp > $start_timestamp) && ($end_timestamp > $invoice_timestamp) && ($start_timestamp < ($invoice_timestamp + $total_time))){
                        
                        if($start_timestamp < $invoice_timestamp){
                            $start_timestamp = $invoice_timestamp;
                        }
                        
                        if($end_timestamp > ($invoice_timestamp + $total_time)){
                            $end_timestamp = $invoice_timestamp + $total_time;
                        }
                        
                        $time_diff = $end_timestamp - $start_timestamp + (24 * 3600); /* Inclusive start day */
                        
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        
                        $row_total_depreciation_between = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time) * intval($assets_row["quantity"]), 2, ".", "");
                        
                        $objWorkSheet->setCellValue("K2", "Depreciation (" . date("j-M-Y", strtotime($parameters["start_date"])) . " and " . date("j-M-Y", strtotime($parameters["end_date"])) . ")");
                    }
                    
                    /* Calculation - Depreciation Till End Date */
                    if($assets_row["invoice_date"] && ($assets_value > 0) && ($lifespan > 0) && $end_date && ($salvage_value >= 0)){
                        
                        $time_diff = $end_timestamp - $invoice_timestamp + (24 * 3600); /* Inclusive start day */
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        if($time_diff < 0){
                            $time_diff = 0;
                        }
                        
                        $row_total_depreciation_amount = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time) * intval($assets_row["quantity"]), 2, ".", "");
                    }
                    
                    /* Calculation - Netbook Value */
                    $row_total_netbook_value = $row_total_asset_value - $row_total_depreciation_amount;
                    
                    /* Calculation - Current Depreciation */
                    if($assets_row["invoice_date"] && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                        
                        $time_diff = time() - $invoice_timestamp + (24 * 3600); /* Inclusive start day */
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        $row_total_current_depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time) * intval($assets_row["quantity"]), 2, ".", "");
                    }
                    
                    $category_asset_unit_price += $row_asset_unit_price;
                    $category_total_asset_value += $row_total_asset_value;
                    $category_total_accumulated_depreciation += $row_total_accumulated_depreciation;
                    $category_total_depreciation_between += $row_total_depreciation_between;
                    $category_total_depreciation_amount += $row_total_depreciation_amount;
                    $category_total_netbook_value += $row_total_netbook_value;
                    $category_total_current_depreciation += $row_total_current_depreciation;
                    $category_total_salvage_value += $row_total_salvage_value;
                    
                    $objWorkSheet
                        ->setCellValue('A' . $current_row_count, ++$no_count)
                        ->setCellValue('B' . $current_row_count, $assets_row["barcode"])
                        ->setCellValue('C' . $current_row_count, $assets_row["assets_name"])
                        ->setCellValue('D' . $current_row_count, $assets_row["quantity_department"])
                        ->setCellValue('E' . $current_row_count, $assets_row["quantity_location"])
                        ->setCellValue('F' . $current_row_count, $assets_row["quantity"])
                        ->setCellValue('G' . $current_row_count, $assets_row["enable_tracking"]? "Yes" : "No Tracking")
                        ->setCellValue('H' . $current_row_count, $assets_row["assets_value"]? $assets_row["assets_value"] : "No Asset Value")
                        ->setCellValue('I' . $current_row_count, $row_total_asset_value)
                        ->setCellValue('J' . $current_row_count, $row_total_accumulated_depreciation? $row_total_accumulated_depreciation : "")
                        ->setCellValue('K' . $current_row_count, $row_total_depreciation_between? $row_total_depreciation_between : "")
                        ->setCellValue('L' . $current_row_count, $row_total_depreciation_amount? $row_total_depreciation_amount : "")
                        ->setCellValue('M' . $current_row_count, $row_total_netbook_value? $row_total_netbook_value : "")
                        ->setCellValue('N' . $current_row_count, $row_total_current_depreciation? $row_total_current_depreciation : "")
                        ->setCellValue('O' . $current_row_count, $assets_row["assets_lifespan"]? $assets_row["assets_lifespan"] . " months" : "No Lifespan")
                        ->setCellValue('P' . $current_row_count, $assets_row["maintenance_interval"]? $assets_row["maintenance_interval"] . " months" : "No Maintenance")
                        ->setCellValue('Q' . $current_row_count, $assets_row["serial_number"])
                        ->setCellValue('R' . $current_row_count, $assets_row["category"])
                        ->setCellValue('S' . $current_row_count, $assets_row["supplier_name"])
                        ->setCellValue('T' . $current_row_count, $assets_row["brand"])
                        ->setCellValue('U' . $current_row_count, $row_total_salvage_value? $row_total_salvage_value : "No Salvage Value")
                        ->setCellValue('V' . $current_row_count, $assets_row["warranty_expiry"])
                        ->setCellValue('W' . $current_row_count, $assets_row["invoice_number"])
                        ->setCellValue('X' . $current_row_count, $assets_row["invoice_date"])
                        ->setCellValue('Y' . $current_row_count, $assets_row["status"])
                        ->setCellValue('Z' . $current_row_count, $assets_row["remarks"])
                        ->setCellValue('AA' . $current_row_count, $assets_row["last_scan"] )
                    ;
                    
                    $objWorkSheet->getStyle('H' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                    $objWorkSheet->getStyle('I' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                    $objWorkSheet->getStyle('J' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                    $objWorkSheet->getStyle('K' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                    $objWorkSheet->getStyle('L' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                    $objWorkSheet->getStyle('M' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');                    
                    $objWorkSheet->getStyle('N' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                    $objWorkSheet->getStyle('U' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                }
            }
            
            /* SUM */
            $current_row_count++; $current_row_count++;
            
            $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(30);
            $objWorkSheet->getStyle("H" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("I" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("J" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("K" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("L" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("M" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("N" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("U" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            
            
            $objWorkSheet->getStyle('H' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('I' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('J' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('K' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('L' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('M' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');                    
            $objWorkSheet->getStyle('N' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('U' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            
            $objWorkSheet->setCellValue("H" . $current_row_count, number_format($category_asset_unit_price,2,".",""));
            $objWorkSheet->setCellValue("I" . $current_row_count, number_format($category_total_asset_value,2,".",""));
            $objWorkSheet->setCellValue("J" . $current_row_count, number_format($category_total_accumulated_depreciation,2,".",""));
            $objWorkSheet->setCellValue("K" . $current_row_count, number_format($category_total_depreciation_between,2,".",""));
            $objWorkSheet->setCellValue("L" . $current_row_count, number_format($category_total_depreciation_amount,2,".",""));
            $objWorkSheet->setCellValue("M" . $current_row_count, number_format($category_total_netbook_value,2,".",""));
            $objWorkSheet->setCellValue("N" . $current_row_count, number_format($category_total_current_depreciation,2,".",""));
            $objWorkSheet->setCellValue("U" . $current_row_count, number_format($category_total_salvage_value,2,".",""));
            
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
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - HFC Assets List Report.xlsx"');
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
