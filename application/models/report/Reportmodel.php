<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reportmodel extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->model("attachment/attachmentmodel");
        $this->load->model("asset/assetmodel");
        $this->load->model("asset/transfermodel");
        $this->load->model("asset/maintenancemodel");
        $this->load->model("asset/discrepancymodel");
        $this->load->helper('file');
        $this->load->helper('text');
        require_once 'application/libraries/phpexcel/PHPExcel.php';
        require_once 'application/libraries/phpexcel/AssetImportFilter.php';
    }
    
    /* 
     * Download excel report for one or more asset. One asset one sheet 
     *
     * $parameters["start_date"] = optional
     * $parameters["end_date"] = optional
     * $parameters["assets"] = array()
     * 
     * */
    function downloadAssetDetailReport($parameters){
        
        $parameters["assets"] = array_unique($parameters["assets"]);
        
        $assets = array();
        $assets_id = array();
        
        foreach($parameters["assets"] as $asset){
                        
            /* Query Parameters */                    
            $assets_parameters = array();
            $assets_parameters["assets_id"] = $asset;
            $assets_parameters["page_no"] = 1;
            $assets_parameters["count_per_page"] = 20;
            $assets_parameters["sort"] = "desc";
            $assets_parameters["sort_field"] = "id";
            $assets_parameters["current_tab"] = "";   
            
            /* Asset Info */    
            $asset_row = $this->assetmodel->getAsset($assets_parameters);
            
            if($asset_row){
                
                /* Preprocess */
                
                $category_name = "";
                $category = $asset_row["categories"];
                
                foreach($category as $cat){
                    if(strlen($category_name) > 0){
                        $category_name .= "\n";
                    }
                    $category_name .= $cat["categories_name"];
                }
                
                $asset_row["categories"] = $category_name;
                
                switch ($asset_row["status"]) {
                    case 'available'    : $asset_row["status"] = "Available"; break;
                    case 'write_off'    : $asset_row["status"] = "Written Off"; break;
                    case 'loan_out'     : $asset_row["status"] = "On Loan"; break;
                    case 'out_of_stock': $asset_row["status"] = "Out Of Stock"; break;
                    case 'maintenance': $asset_row["status"] = "Maintenance"; break;
                    case 'unavailable': $asset_row["status"] = "Not Available"; break;
                }
                
                $asset_row["categories"] = $category_name;
                
                $asset_row["warranty_expiry"] = date("d-F-Y", strtotime($asset_row["warranty_expiry"]));
                if(intval(date("Y", strtotime($asset_row["warranty_expiry"]))) <= 1990){
                    $asset_row["warranty_expiry"] = "";           
                }
                
                $asset_row["invoice_date"] = date("d-F-Y", strtotime($asset_row["invoice_date"]));
                if(intval(date("Y", strtotime($asset_row["invoice_date"]))) <= 1990){
                    $asset_row["invoice_date"] = "";           
                }
                
                $asset_row["depreciation"] = "";
                
                $depreciation = "-";
                $depreciation_percent = 0;
                
                $depreciation_between = FALSE;
                
                $invoice_timestamp = strtotime($asset_row["invoice_date"]);
                $assets_value = floatval($asset_row["assets_value"]);
                $lifespan = intval($asset_row["assets_lifespan"]);
                $salvage_value = floatval($asset_row["salvage_value"]);
                $days_per_month = 30.4375;
                
                if(intval(date("Y", $invoice_timestamp)) > 1990 && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                        
                    $total_time = $lifespan * $days_per_month * 24 * 3600;
                    $time_diff = time() - $invoice_timestamp;
                    if($time_diff > $total_time){
                        $time_diff = $total_time;
                    }
                    $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                    $depreciation_percent = ceil(($time_diff/$total_time * 100));
                    
                    $asset_row["depreciation"] = $depreciation;
                    
                    $start_timestamp = strtotime($parameters["start_date"]);
                    $end_timestamp = strtotime($parameters["end_date"]);
                    
                    if($parameters["start_date"] && $parameters["end_date"] && ($end_timestamp > $start_timestamp) 
                        && ($end_timestamp > $invoice_timestamp) && ($start_timestamp < ($invoice_timestamp + $total_time))){
                        
                        if($start_timestamp < $invoice_timestamp){
                            $start_timestamp = $invoice_timestamp;
                        }
                        
                        if($end_timestamp > ($invoice_timestamp + $total_time)){
                            $end_timestamp = $invoice_timestamp + $total_time;
                        }
                        
                        $time_diff = $end_timestamp - $start_timestamp;
                        
                        $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                        $depreciation_percent = ceil(($time_diff/$total_time * 100));
                        $depreciation_between = $depreciation;
                    }
                }
                
                /* Preprocess Ends */
                
                $assets_id[] = $asset;
                $assets[$asset] = $asset_row;
                
                /* Asset Loan and Location */
                $location_row = $this->transfermodel->getAssetLocation($assets_parameters);
                $assets[$asset]["location"] = array();
                
                if($location_row){
                    $assets[$asset]["location"] = $location_row;
                }
                
                /* Maintenance */
                $assets[$asset]["maintenance"] = array();
                $maintenance_row = $this->maintenancemodel->getMaintenance($assets_parameters);
                
                if($maintenance_row){
                    $assets[$asset]["maintenance"] = $maintenance_row;
                }
                
                /* Tracking */
                $assets[$asset]["tracking"] = array();
                $tracking_row = $this->discrepancymodel->getTrackingHistoryByAsset($assets_parameters);
                
                if($tracking_row){
                    $assets[$asset]["tracking"] = $tracking_row;
                }    
            }
        }
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Detailed Report For Each Asset")
                             ->setSubject("Detailed Report For Each Asset")
                             ->setDescription("This document describes information for selected assets")
                             ->setKeywords("asset report")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A3"=>15, "B3"=>14, "C3"=>14, "D3"=>14, "E3"=>14, "F3"=>17, "G3"=>17, "H3"=>15, "I3"=>15, "J3"=>14, 
                        "K3"=>14, "L3"=>15, "M3"=>16, "N3"=>12, "O3"=>12, "P3"=>12, "Q3"=>12);
                                     
        foreach($assets as $key=>$asset){
            
            $objWorkSheet = $objPHPExcel->createSheet($key);
            
            $objWorkSheet->getRowDimension(2)->setRowHeight(133);
            
            $objWorkSheet->mergeCells("A2:Q2");
            
            $objWorkSheet->getRowDimension(3)->setRowHeight(35);
            
            $objWorkSheet->getRowDimension(4)->setRowHeight(35);
            
            $objWorkSheet->setTitle(ellipsize($asset["barcode"] . " - " . remove_invalid_sheet_title_character($asset["assets_name"]),31,1,""));
            
            $image_path = $this->attachmentmodel->get_full_path($asset["attachments_id"], "showall", 1000, 177);
            
            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Asset Image');
            $objDrawing->setDescription('Asset Image');
            $objDrawing->setPath($image_path);
    
            $objDrawing->setCoordinates('A2');
            $objDrawing->setWidth(133);
            $objDrawing->setHeight(177);
            $objDrawing->setResizeProportional(true);
            $objDrawing->setWorksheet($objWorkSheet);    
            
            foreach($header as $key=>$head){
                        
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                
                $objWorkSheet->getStyle($key)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FFFF00')
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ),
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 11,
                            'name'  => 'Calibri'
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'wrap'=> TRUE
                        )
                    )
                );
            }
            
            $objWorkSheet->getRowDimension(1)->setRowHeight(48);
            
            $objWorkSheet->mergeCells("A1:D1");
            $objWorkSheet->getStyle('A1:D1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));
            
            $objWorkSheet
                ->setCellValue('A1', 'Asset Detail Report')
                ->setCellValue('A3', 'Asset Name')
                ->setCellValue('B3', 'Asset ID (Barcode)')
                ->setCellValue('C3', 'Enable Asset Tracking')
                ->setCellValue('D3', 'Assets Unit Price')
                ->setCellValue('E3', 'Depreciation')
                ->setCellValue('F3', 'Lifespan (Months)')
                ->setCellValue('G3', 'Maintenance Interval (Months)')
                ->setCellValue('H3', 'Serial Number')
                ->setCellValue('I3', 'Category')
                ->setCellValue('J3', 'Supplier')
                ->setCellValue('K3', 'Brand')
                ->setCellValue('L3', 'Salvage Value')
                ->setCellValue('M3', 'Warranty Expiry')
                ->setCellValue('N3', 'Invoice Number')
                ->setCellValue('O3', 'Invoice Date')
                ->setCellValue('P3', 'Status')
                ->setCellValue('Q3', 'Remarks')
                ;   
                
            $objWorkSheet
                ->setCellValue('A4', $asset["assets_name"])
                ->setCellValue('B4', $asset["barcode"])
                ->setCellValue('C4', ($asset["enable_tracking"]? "Yes" : "No Tracking"))
                ->setCellValue('D4', ($asset["assets_value"]? $asset["assets_value"] : "No Asset Value"))
                ->setCellValue('E4', $asset["depreciation"])
                ->setCellValue('F4', ($asset["assets_lifespan"]? $asset["assets_lifespan"] . " months" : "No Lifespan"))
                ->setCellValue('G4', ($asset["maintenance_interval"]? $asset["maintenance_interval"] . " months" : "No Maintenance"))
                ->setCellValue('H4', $asset["serial_number"])
                ->setCellValue('I4', $asset["categories"])
                ->setCellValue('J4', $asset["supplier_name"])
                ->setCellValue('K4', $asset["brand"])
                ->setCellValue('L4', ($asset["salvage_value"]? $asset["salvage_value"] : "No Salvage Value"))
                ->setCellValue('M4', $asset["warranty_expiry"])
                ->setCellValue('N4', $asset["invoice_number"])
                ->setCellValue('O4', $asset["invoice_date"])
                ->setCellValue('P4', $asset["status"])
                ->setCellValue('Q4', $asset["remarks"])
                ;   
            
            $objWorkSheet->getStyle('D4')->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('E4')->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('L4')->getNumberFormat()->setFormatCode('0.00');
            
            if($depreciation_between){
                
                $str = "Depreciation between " . $parameters["start_date"] . " and " . $parameters["end_date"] . " is " . $depreciation_between;
                
                $objWorkSheet->mergeCells("A6:E6");
                $objWorkSheet->setCellValue("A6", $str);
            }
            
            $objWorkSheet->getStyle('A8:E6')
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);
            $objWorkSheet->setCellValue('A8', "Asset Location");
            $objWorkSheet->getStyle('A8')->getFont()->setUnderline(TRUE)->setBold(TRUE);
            
            $current_row_count = 10;
            
            if(count($asset["location"]) > 0){
                
                /* Showing location */            
                $objWorkSheet
                ->setCellValue('A' . $current_row_count, 'Department')
                ->setCellValue('B' . $current_row_count, 'Location')
                ->setCellValue('C' . $current_row_count, 'Quantity')
                ->setCellValue('D' . $current_row_count, 'Status')
                ;  
                
                $objWorkSheet->mergeCells("D" . $current_row_count . ":F" . $current_row_count);
                $objWorkSheet->getStyle("A" . $current_row_count . ":F" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE))); 
                $current_row_count++;
                
                foreach($asset["location"] as $loc){
                    
                    $objWorkSheet->mergeCells("D" . $current_row_count . ":F" . $current_row_count);
                    
                    $location_status = "All Available";
                    
                    if(isset($loc["loan"])){
                        
                        $message ="";
                        $total_loaned = 0;
                        
                        $loan_list = $loc["loan"];
                        foreach($loan_list as $loan_key=>$lo){
                            $total_loaned += intval($lo["quantity"]);
                            if(strlen($message) > 0){
                                $message .= "\n";
                            }
                            $message .= "Loaned to " . $lo["borrower_name"] . "/" . $lo["borrower_entity"] . "\n" . $lo["quantity"] . "unit(s)/" . format_period_from_hour($lo["loan_period"]);
                            $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(($loan_key + 1) * 40);
                        }
                        
                        if($total_loaned){
                            $location_status = $total_loaned . " loaned out\n" . $message;   
                        }
                    }
                    
                    $objWorkSheet
                    ->setCellValue('A' . $current_row_count, $loc["departments_name"])
                    ->setCellValue('B' . $current_row_count, $loc["location"])
                    ->setCellValue('C' . $current_row_count, $loc["quantity"])
                    ->setCellValue('D' . $current_row_count, $location_status)
                    ;  
                    $current_row_count++;   
                }

                /* Maintenance */
                $current_row_count++;   
                if(count($asset["maintenance"]) > 0){
                            
                    $objWorkSheet->mergeCells("A" . $current_row_count . ":B" . $current_row_count);    
                    $objWorkSheet->setCellValue('A' . $current_row_count, "Upcoming Maintenance");
                    $objWorkSheet->getStyle('A' . $current_row_count)->getFont()->setUnderline(TRUE)->setBold(TRUE);
                    
                    $current_row_count++;   
                    $current_row_count++;   
                    
                    $objWorkSheet
                    ->setCellValue('A' . $current_row_count, 'Maintenance Date')
                    ;  
                    
                    $objWorkSheet->mergeCells("A" . $current_row_count . ":B" . $current_row_count);
                    $objWorkSheet->getStyle("A" . $current_row_count . ":B" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
                    
                    $current_row_count++;
                    
                    $upcoming = $asset["maintenance"]["upcoming"];
                    
                    foreach($upcoming as $up){
                        $objWorkSheet
                        ->setCellValue('A' . $current_row_count, date("d-F-Y", strtotime($up["maintenance_date"])))
                        ;
                        $current_row_count++; 
                    }
                }
                
                /* Tracking */
                $current_row_count++;  
                if(count($asset["tracking"]) > 0){
                    
                    $objWorkSheet->mergeCells("A" . $current_row_count . ":B" . $current_row_count);    
                    $objWorkSheet->setCellValue('A' . $current_row_count, "Last 10 Tracking Records");
                    $objWorkSheet->getStyle('A' . $current_row_count)->getFont()->setUnderline(TRUE)->setBold(TRUE);
                    
                    $current_row_count++;   
                    $current_row_count++;   
                    
                    $objWorkSheet
                    ->setCellValue('A' . $current_row_count, 'DateTime Scanned')
                    ->setCellValue('B' . $current_row_count, 'Quantity')
                    ->setCellValue('C' . $current_row_count, 'Remarks')
                    ;  
                    
                    $objWorkSheet->getStyle("A" . $current_row_count . ":C" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
                    
                    $current_row_count++;
                    
                    foreach($asset["tracking"] as $track_row){
                        $objWorkSheet
                        ->setCellValue('A' . $current_row_count, date("d-F-Y g:i a", strtotime($track_row["datetime_scanned"])))
                        ->setCellValue('B' . $current_row_count, $track_row["quantity"])
                        ->setCellValue('C' . $current_row_count, $track_row["remark"])
                        ;
                        $current_row_count++; 
                    }
                }
            }    
            
            /* Align Center */  
            $objWorkSheet->getStyle('A4:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);            
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', TRUE);
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Assets Detailed Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    
    /* 
     * Download excel report for one or more department. One department one sheet 
     *
     * $parameters["start_date"] = optional
     * $parameters["end_date"] = optional
     * $parameters["departments"] = array()
     * 
     * */
    function downloadAssetListReport($parameters){
        
        $parameters["departments"] = array_unique($parameters["departments"]);
        
        $departments_parameters["departments"] = $parameters["departments"];
        $departments_parameters["page_no"] = 1;
        $departments_parameters["count_per_page"] = 10000;
        $departments_parameters["sort"] = "ASC";
        $departments_parameters["sort_field"] = "assets.id";
        
        $departments = $this->assetmodel->getAssetsByDepartments($departments_parameters);
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Report For Each Department")
                             ->setSubject("Report For Each Department")
                             ->setDescription("This document describes asset information for selected department")
                             ->setKeywords("asset report department")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A2"=>15, "B2"=>14, "C2"=>14, "D2"=>14, "E2"=>14, "F2"=>17, "G2"=>17, "H2"=>17, "I2"=>21, "J2"=>17, 
                        "K2"=>14, "L2"=>15, "M2"=>16, "N2"=>17, "O2"=>12, "P2"=>14, "Q2"=>20, "R2"=>12, "S2"=>12, "T2"=>12,
                        "U2"=>12, "V2"=>20, "W2"=>20, "X2"=>20, "Y2"=>20);
        
        $departments_id_list = array();
        $departments_names = array();
        
        foreach($departments as $departments_id=>$assets){
            $departments_id_list[] = $departments_id;
        }
        
        if(count($departments) == 0){
            echo "No record found";
            return FALSE;
        }
        
        $this->db->where_in("id", $departments_id_list);
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $department){
            $departments_names[$department["id"]] = $department["departments_name"];
        }
        
        /* Last Scan DateTime*/
        $track_list = array();
        $assets_list = array();
        foreach($departments as $departments_id=>$assets){
            foreach($assets as $assets_item){
                $assets_list[] = $assets_item["assets_id"];
            }
        }
        
        if(count($assets_list) > 0){
            
            $assets_sql_string = implode(",", $assets_list);
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
                $track_list[$track_row["departments_id"]][$track_row["assets_id"]] = $track_row["datetime_scanned"];
            }
        }
        
        $sheet_count = 0;
        
        foreach($departments as $departments_id=>$assets){
            
            $current_row_count = 1;
            
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_count);
            
            $objWorkSheet->setTitle(isset($departments_names[$departments_id])? ellipsize(remove_invalid_sheet_title_character($departments_names[$departments_id]),31,1,"") : "No Department Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(35);
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            }
            
            $objWorkSheet->getRowDimension(1)->setRowHeight(48);
            
            $objWorkSheet->mergeCells("A1:D1");
            $objWorkSheet->getStyle('A1:D1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));
            
            $objWorkSheet
                ->setCellValue('A1', 'Asset List Report')
                ->setCellValue('A2', 'Asset ID (Barcode)')
                ->setCellValue('B2', 'Asset Name')
                ->setCellValue('C2', 'Location')
                ->setCellValue('D2', 'Quantity')
                ->setCellValue('E2', 'Enable Asset Tracking')
                ->setCellValue('F2', 'Assets Unit Price')
                ->setCellValue('G2', "Total Asset Value\n(Value x Quantity)")
                ->setCellValue('H2', 'Accumulated Depreciation')
                ->setCellValue('I2', 'Depreciation Between Range')
                ->setCellValue('J2', 'Total Depreciation Amount')
                ->setCellValue('K2', 'Netbook Value')
                ->setCellValue('L2', 'Current Depreciation')
                ->setCellValue('M2', 'Lifespan (Months)')
                ->setCellValue('N2', 'Maintenance Interval (Months)')
                ->setCellValue('O2', 'Serial Number')
                ->setCellValue('P2', 'Category')
                ->setCellValue('Q2', 'Supplier')
                ->setCellValue('R2', 'Brand')
                ->setCellValue('S2', 'Salvage Value')
                ->setCellValue('T2', 'Warranty Expiry')
                ->setCellValue('U2', 'Invoice Number')
                ->setCellValue('V2', 'Invoice Date')
                ->setCellValue('W2', 'Status')
                ->setCellValue('X2', 'Remarks')
                ->setCellValue('Y2', 'Last Scan DateTime')
                ;      
            
            $total_assets_price = 0;
            $total_depreciation = 0;
            $total_salvage = 0;
            $total_depreciation_range = 0;
            $total_accumulated_depreciation = 0;
            $total_depreciation_end_date = 0;
            $total_assets_quantity_value = 0;
            
            foreach($assets as $asset_row){
                
                $category_name = "";
                $category = $asset_row["category"];
                
                foreach($category as $cat){
                    if(strlen($category_name) > 0){
                        $category_name .= "\n";
                    }
                    $category_name .= $cat["categories_name"];
                }
                
                $asset_row["categories"] = $category_name;
                
                switch ($asset_row["status"]) {
                    case 'available'    : $asset_row["status"] = "Available"; break;
                    case 'write_off'    : $asset_row["status"] = "Written Off"; break;
                    case 'loan_out'     : $asset_row["status"] = "On Loan"; break;
                    case 'out_of_stock': $asset_row["status"] = "Out Of Stock"; break;
                    case 'maintenance': $asset_row["status"] = "Maintenance"; break;
                    case 'unavailable': $asset_row["status"] = "Not Available"; break;
                }
                
                $asset_row["warranty_expiry"] = date("d-F-Y", strtotime($asset_row["warranty_expiry"]));
                if(intval(date("Y", strtotime($asset_row["warranty_expiry"]))) <= 1990){
                    $asset_row["warranty_expiry"] = "";           
                }
                
                $asset_row["invoice_date"] = date("d-F-Y", strtotime($asset_row["invoice_date"]));
                if(intval(date("Y", strtotime($asset_row["invoice_date"]))) <= 1990){
                    $asset_row["invoice_date"] = "";           
                }
                
                $depreciation = FALSE;
                $depreciation_percent = 0;
                
                $accumulated_depreciation = FALSE;
                $depreciation_end_date = FALSE;
                
                $depreciation_between = FALSE;
                
                $invoice_timestamp = strtotime($asset_row["invoice_date"]);
                $assets_value = floatval($asset_row["assets_value"]);
                $lifespan = intval($asset_row["assets_lifespan"]);
                $salvage_value = floatval($asset_row["salvage_value"]);
                $days_per_month = 30.4375;
                
                $total_assets_price += $assets_value;
                
                if(intval(date("Y", $invoice_timestamp)) > 1990 && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                        
                    $total_time = $lifespan * $days_per_month * 24 * 3600;
                    $time_diff = time() - $invoice_timestamp + (24 * 3600); /* Inclusive start day */
                    if($time_diff > $total_time){
                        $time_diff = $total_time;
                    }
                    $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                    $depreciation_percent = ceil(($time_diff/$total_time * 100));
                    
                    $asset_row["depreciation"] = $depreciation;
                    
                    $start_timestamp = strtotime($parameters["start_date"]);
                    $end_timestamp = strtotime($parameters["end_date"]);
                    
                    if($parameters["start_date"] && $parameters["end_date"] && ($end_timestamp > $start_timestamp) 
                        && ($end_timestamp > $invoice_timestamp) && ($start_timestamp < ($invoice_timestamp + $total_time))){
                        
                        if($start_timestamp < $invoice_timestamp){
                            $start_timestamp = $invoice_timestamp;
                        }
                        
                        if($end_timestamp > ($invoice_timestamp + $total_time)){
                            $end_timestamp = $invoice_timestamp + $total_time;
                        }
                        
                        $time_diff = $end_timestamp - $start_timestamp;
                        
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        
                        $depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                        $depreciation_percent = ceil(($time_diff/$total_time * 100));
                        $depreciation_between = $depreciation;
                        
                        $objWorkSheet->setCellValue("I2", "Depreciation (" . date("j-M-Y",strtotime($parameters["start_date"])) . " and " . date("j-M-Y",strtotime($parameters["end_date"])) . ")");
                    }

                    /* Accumulated Depreciation - Depreciation from invoice date to start date selected */
                    if($parameters["start_date"]){
                        
                        $time_diff = $start_timestamp - $invoice_timestamp;
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        if($time_diff < 0){
                            $time_diff = 0;
                        }
                        
                        $accumulated_depreciation = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                    }
                    
                    /* Depreciation till end date - Depreciation from invoice date to end date selected */
                    if($parameters["end_date"]){
                        
                        $time_diff = $end_timestamp - $invoice_timestamp + (24 * 3600); /* Inclusive start day */
                        if($time_diff > $total_time){
                            $time_diff = $total_time;
                        }
                        if($time_diff < 0){
                            $time_diff = 0;
                        }
                        
                        $depreciation_end_date = number_format(($time_diff) * ($assets_value - $salvage_value) / ($total_time), 2, ".", "");
                    }
                }
    
                $asset = $asset_row;
                
                $total_assets_quantity_value += $assets_value * intval($asset["quantity"]);
                $total_accumulated_depreciation += $accumulated_depreciation * intval($asset["quantity"]);
                $total_depreciation_range += $depreciation_between * intval($asset["quantity"]);
                $total_depreciation_end_date += $depreciation_end_date * intval($asset["quantity"]);
                $total_depreciation += $asset["depreciation"] * intval($asset["quantity"]);
                $total_salvage += $salvage_value * intval($asset["quantity"]);
                
                $current_row_count++;
                
                $objWorkSheet
                    ->setCellValue('A' . $current_row_count, $asset["barcode"])
                    ->setCellValue('B' . $current_row_count, $asset["assets_name"])
                    ->setCellValue('C' . $current_row_count, $asset["location"])
                    ->setCellValue('D' . $current_row_count, $asset["quantity"])
                    ->setCellValue('E' . $current_row_count, ($asset["enable_tracking"]? "Yes" : "No Tracking"))
                    ->setCellValue('F' . $current_row_count, ($asset["assets_value"]? $asset["assets_value"] : "No Asset Value"))
                    ->setCellValue('G' . $current_row_count, $assets_value * intval($asset["quantity"]))
                    ->setCellValue('H' . $current_row_count, $accumulated_depreciation? ($accumulated_depreciation * intval($asset["quantity"])) : "")
                    ->setCellValue('I' . $current_row_count, $depreciation_between? ($depreciation_between * intval($asset["quantity"])) : "")
                    ->setCellValue('J' . $current_row_count, $depreciation_end_date? ($depreciation_end_date * intval($asset["quantity"])) : "")
                    ->setCellValue('K' . $current_row_count, $asset["depreciation"]? (($assets_value - $depreciation_end_date) * intval($asset["quantity"])) : "")
                    ->setCellValue('L' . $current_row_count, $asset["depreciation"]? ($asset["depreciation"] * intval($asset["quantity"])) : "")
                    ->setCellValue('M' . $current_row_count, ($asset["assets_lifespan"]? $asset["assets_lifespan"] . " months" : "No Lifespan"))
                    ->setCellValue('N' . $current_row_count, ($asset["maintenance_interval"]? $asset["maintenance_interval"] . " months" : "No Maintenance"))
                    ->setCellValue('O' . $current_row_count, $asset["serial_number"])
                    ->setCellValue('P' . $current_row_count, $asset["categories"])
                    ->setCellValue('Q' . $current_row_count, $asset["supplier_name"])
                    ->setCellValue('R' . $current_row_count, $asset["brand"])
                    ->setCellValue('S' . $current_row_count, ($asset["salvage_value"]? ($asset["salvage_value"] * intval($asset["quantity"])) : "No Salvage Value"))
                    ->setCellValue('T' . $current_row_count, $asset["warranty_expiry"])
                    ->setCellValue('U' . $current_row_count, $asset["invoice_number"])
                    ->setCellValue('V' . $current_row_count, $asset["invoice_date"])
                    ->setCellValue('W' . $current_row_count, $asset["status"])
                    ->setCellValue('X' . $current_row_count, $asset["remarks"])
                    ->setCellValue('Y' . $current_row_count, isset($track_list[$departments_id][$asset["assets_id"]])? date("d-F-Y", strtotime($track_list[$departments_id][$asset["assets_id"]])) : "") 
                    ;   
                $objWorkSheet->getStyle('F' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                $objWorkSheet->getStyle('G' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                $objWorkSheet->getStyle('H' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                $objWorkSheet->getStyle('I' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                $objWorkSheet->getStyle('J' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                $objWorkSheet->getStyle('K' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');                    
                $objWorkSheet->getStyle('L' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
                $objWorkSheet->getStyle('S' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            }
            
            /* SUM */
            $current_row_count++; $current_row_count++;
            
            $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(30);
            $objWorkSheet->getStyle("F" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("G" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("H" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("I" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("J" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("K" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("L" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            $objWorkSheet->getStyle("S" . $current_row_count)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            
            
            $objWorkSheet->getStyle('F' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('G' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('H' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('I' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('J' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('K' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');                    
            $objWorkSheet->getStyle('L' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            $objWorkSheet->getStyle('S' . $current_row_count)->getNumberFormat()->setFormatCode('0.00');
            
            
            $objWorkSheet->setCellValue("F" . $current_row_count, number_format($total_assets_price,2,".",""));
            $objWorkSheet->setCellValue("G" . $current_row_count, number_format($total_assets_quantity_value,2,".",""));
            $objWorkSheet->setCellValue("H" . $current_row_count, number_format($total_accumulated_depreciation,2,".",""));
            $objWorkSheet->setCellValue("I" . $current_row_count, number_format($total_depreciation_range,2,".",""));
            $objWorkSheet->setCellValue("J" . $current_row_count, number_format($total_depreciation_end_date,2,".",""));
            $objWorkSheet->setCellValue("K" . $current_row_count, number_format($total_assets_quantity_value - $total_depreciation_end_date,2,".",""));
            $objWorkSheet->setCellValue("L" . $current_row_count, number_format($total_depreciation,2,".",""));
            $objWorkSheet->setCellValue("S" . $current_row_count, number_format($total_salvage,2,".",""));
            
        
            /* Align Center */  
            $objWorkSheet->getStyle('A2:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);          
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Assets List Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    
    function downloadDepartmentReport($parameters){
        
    }
    
    /* 
     * Download excel report for transfer transaction 
     *
     * $parameters["start_date"]
     * $parameters["end_date"]
     * $parameters["departments"] = array()
     * 
     * */
    function downloadTransferReport($parameters){
        
        $parameters["departments"] = array_unique($parameters["departments"]);
        
        $departments_names = array();
        
        $this->db->where_in("id", $parameters["departments"]);
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $department){
            $departments_names[$department["id"]] = $department["departments_name"];
        }
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Transfer history report For Each Department")
                             ->setSubject("Transfer history report For Each Department")
                             ->setDescription("This document describes asset transfer information for selected department")
                             ->setKeywords("asset transfer report department")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A2"=>20, "B2"=>15, "C2"=>15, "D2"=>18, "E2"=>15, "F2"=>15, "G2"=>15, "H2"=>15, "I2"=>15, "J2"=>15, "K2"=>15);
        
        $transfer_parameters["departments"] = $parameters["departments"];
        $transfer_parameters["page_no"] = 1;
        $transfer_parameters["count_per_page"] = 5000;
        $transfer_parameters["sort"] = "desc";
        $transfer_parameters["sort_field"] = "datetime_created";   
        $transfer_parameters["start_date"] = mysql_datetime($parameters["start_date"] . " 00:00:00");
        $transfer_parameters["end_date"] = mysql_datetime($parameters["end_date"] . " 23:59:59");
        
        $transfers = $this->transfermodel->getTransfersByDepartments($transfer_parameters);
        
        $sheet_count = 0;
        
        if(count($transfers) == 0){
            echo "No record found";
            return FALSE;
        }
        
        foreach($transfers as $departments_id=>$assets){
                
            $current_row_count = 1;
            
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_count);
            
            $objWorkSheet->setTitle(isset($departments_names[$departments_id])? ellipsize(remove_invalid_sheet_title_character($departments_names[$departments_id]),31,1,"") : "No Department Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(35);
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            }
            
            $objWorkSheet->getRowDimension(1)->setRowHeight(48);
            
            $objWorkSheet->mergeCells("A1:D1");
            $objWorkSheet->getStyle('A1:D1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));

            $objWorkSheet
                ->setCellValue('A1', 'Transfer Report')
                ->setCellValue('A2', 'Transfer Date')
                ->setCellValue('B2', 'Asset')
                ->setCellValue('C2', 'Asset ID (Barcode)')
                ->setCellValue('D2', 'Type')
                ->setCellValue('E2', 'Quantity')
                ->setCellValue('F2', 'Origin Department')
                ->setCellValue('G2', 'Origin Location')
                ->setCellValue('H2', 'Destination Department')
                ->setCellValue('I2', 'Destination Location')
                ->setCellValue('J2', 'Transferred By')
                ->setCellValue('K2', 'Remarks')
                ;       
            
            foreach($assets as $asset){
                $current_row_count++;
                
                $asset["datetime_created"] = date("d-F-Y g:ia", strtotime($asset["datetime_created"]));
                if(intval(date("Y", strtotime($asset["datetime_created"]))) <= 1990){
                    $asset["datetime_created"] = "Date not found";           
                }
                
                $objWorkSheet
                ->setCellValue('A' . $current_row_count, $asset["datetime_created"])
                ->setCellValue('B' . $current_row_count, $asset["assets"]["assets_name"])
                ->setCellValue('C' . $current_row_count, $asset["assets"]["barcode"])
                ->setCellValue('D' . $current_row_count, ($asset["transaction_type"] == "transfer_department")? "Department \n< >\n Department" : "Location \n< >\n Location")
                ->setCellValue('E' . $current_row_count, $asset["quantity"])
                ->setCellValue('F' . $current_row_count, $asset["origin_departments_name"])
                ->setCellValue('G' . $current_row_count, $asset["origin_location"])
                ->setCellValue('H' . $current_row_count, $asset["destination_departments_name"])
                ->setCellValue('I' . $current_row_count, $asset["destination_location"])
                ->setCellValue('J' . $current_row_count, $asset["users"]["person_name"])
                ->setCellValue('K' . $current_row_count, $asset["remark"])
                ;       
            }
            
            /* Align Center */  
            $objWorkSheet->getStyle('A2:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);     
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Transfer Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');   
    }
    
    /* 
     * Download excel report for loan transaction 
     *
     * $parameters["start_date"]
     * $parameters["end_date"]
     * $parameters["departments"] = array()
     * 
     * */
    function downloadLoanReport($parameters){
        
        $parameters["departments"] = array_unique($parameters["departments"]);
        
        $departments_names = array();
        
        $this->db->where_in("id", $parameters["departments"]);
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $department){
            $departments_names[$department["id"]] = $department["departments_name"];
        }
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Loan history report For Each Department")
                             ->setSubject("Loan history report For Each Department")
                             ->setDescription("This document describes asset loan information for selected department")
                             ->setKeywords("asset loan report department")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A2"=>15, "B2"=>15, "C2"=>15, "D2"=>15, "E2"=>15, "F2"=>15, "G2"=>15, "H2"=>15, "I2"=>15, "J2"=>15, "K2"=>15, "L2"=>15, "M2"=>15);
      
        $loan_parameters["departments"] =  $parameters["departments"];
        $loan_parameters["page_no"] = 1;
        $loan_parameters["count_per_page"] = 5000;
        $loan_parameters["sort"] = "desc";
        $loan_parameters["sort_field"] = "datetime_created";   
        $loan_parameters["current_tab"] = "";
        $loan_parameters["start_date"] = mysql_datetime($parameters["start_date"] . " 00:00:00");
        $loan_parameters["end_date"] = mysql_datetime($parameters["end_date"] . " 23:59:59"); 
        
        $loans = $this->loanmodel->getLoanHistoryByDepartments($loan_parameters);
        
        $sheet_count = 0;
        
        if(count($loans) == 0){
            echo "No record found";
            return FALSE;
        }
        
        foreach($loans as $departments_id=>$assets){
                
            $current_row_count = 1;
            
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_count);
            
            $objWorkSheet->setTitle(isset($departments_names[$departments_id])? ellipsize(remove_invalid_sheet_title_character($departments_names[$departments_id]),31,1,"") : "No Department Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(35);
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            }
            
            $objWorkSheet->getRowDimension(1)->setRowHeight(48);
            
            $objWorkSheet->mergeCells("A1:D1");
            $objWorkSheet->getStyle('A1:D1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));

            $objWorkSheet
                ->setCellValue('A1', 'Loan Report')
                ->setCellValue('A2', 'Transaction Date')
                ->setCellValue('B2', 'Asset')
                ->setCellValue('C2', 'Asset ID (Barcode)')
                ->setCellValue('D2', 'Location')
                ->setCellValue('E2', 'Type')
                ->setCellValue('F2', 'Quantity')
                ->setCellValue('G2', 'Borrower Name')
                ->setCellValue('H2', 'Company')
                ->setCellValue('I2', 'Approver')
                ->setCellValue('J2', 'Recorded By')
                ->setCellValue('K2', 'Status')
                ->setCellValue('L2', 'Expected Return')
                ->setCellValue('M2', 'Remarks')
                ;       
            
            foreach($assets as $asset){
                $current_row_count++;
                
                $asset["datetime_created"] = date("d-F-Y g:ia", strtotime($asset["datetime_created"]));
                if(intval(date("Y", strtotime($asset["datetime_created"]))) <= 1990){
                    $asset["datetime_created"] = "Date not found";           
                }
                
                if($asset["remaining"] <= 0){
                    $asset["remaining"] = "Returned";    
                }else{
                    $asset["remaining"] = $asset["remaining"] . " Pending Return";
                }
                
                $expected_return = (intval($asset["loan_period"]) * 3600) + strtotime($asset["loan_datetime"]);
                $expected_return = date("j-F-Y", $expected_return) . "\n" . date("g:i a", $expected_return);
                
                $objWorkSheet
                ->setCellValue('A' . $current_row_count, $asset["datetime_created"])
                ->setCellValue('B' . $current_row_count, $asset["assets"]["assets_name"])
                ->setCellValue('C' . $current_row_count, $asset["assets"]["barcode"])
                ->setCellValue('D' . $current_row_count, $asset["origin_location"])
                ->setCellValue('E' . $current_row_count, ($asset["transaction_type"] == "loan")? "Loan Out" : "Return Asset")
                ->setCellValue('F' . $current_row_count, $asset["quantity"])
                ->setCellValue('G' . $current_row_count, $asset["borrower_name"])
                ->setCellValue('H' . $current_row_count, $asset["borrower_entity"])
                ->setCellValue('I' . $current_row_count, $asset["approver_name"])
                ->setCellValue('J' . $current_row_count, $asset["users"]["person_name"])
                ->setCellValue('K' . $current_row_count, $asset["remaining"])
                ->setCellValue('L' . $current_row_count, $expected_return)
                ->setCellValue('M' . $current_row_count, $asset["remark"])
                ;       
            }
                
            /* Align Center */  
            $objWorkSheet->getStyle('A2:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);          
        }
        
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Loan Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    
    /* 
     * Download excel report for discrepancies 
     *
     * $parameters["start_date"]
     * $parameters["departments"] = array()
     * 
     * */
    function downloadDiscrepancyReport($parameters){
        
        $parameters["departments"] = array_unique($parameters["departments"]);
        
        $departments_names = array();
        
        $this->db->where_in("id", $parameters["departments"]);
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $department){
            $departments_names[$department["id"]] = $department["departments_name"];
        }
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Discrepancies report For Each Department")
                             ->setSubject("Discrepancies report For Each Department")
                             ->setDescription("This document describes asset discrepancies information for selected department")
                             ->setKeywords("asset discrepancies report department")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A2"=>30, "B2"=>30, "C2"=>20, "D2"=>20, "E2"=>20, "F2"=>20, "G2"=>35);
        
        $sheet_count = 0;
        
        foreach($parameters["departments"] as $depart){
            
            $discrepancies_parameters["departments_id"] = $depart;
            $discrepancies_parameters["page_no"] = 1;
            $discrepancies_parameters["count_per_page"] = 5000;
            $discrepancies_parameters["sort"] = "id";
            $discrepancies_parameters["sort_field"] = "ASC";
            $discrepancies_parameters["current_tab"] = "";
            $discrepancies_parameters["from_date"] = mysql_date($parameters["start_date"]);
            
            $assets = $this->discrepancymodel->getDiscrepancyByDepartment($discrepancies_parameters);
            
            $current_row_count = 1;
            
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_count);
            
            $objWorkSheet->setTitle(isset($departments_names[$depart])? ellipsize(remove_invalid_sheet_title_character($departments_names[$depart]),31,1,"") : "No Department Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(35);
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            }
            
            $objWorkSheet->getRowDimension(1)->setRowHeight(48);
            
            $objWorkSheet->mergeCells("A1:G1");
            $objWorkSheet->getStyle('A1:G1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));

            $objWorkSheet
                ->setCellValue('A1', 'Discrepancy Report (Compare From ' . date("d-M-Y", strtotime($parameters["start_date"])) . ")")
                ->setCellValue('A2', 'Last Checked Date')
                ->setCellValue('B2', 'Asset')
                ->setCellValue('C2', 'Asset ID (Barcode)')
                ->setCellValue('D2', 'Tracking Remarks')
                ->setCellValue('E2', 'Actual Quantity')
                ->setCellValue('F2', 'Tracked Quantity')
                ->setCellValue('G2', 'Status')
                ;  
               
            foreach($assets as $asset){
                $current_row_count++;
                
                $diff = intval($asset["total_quantity"]) - intval($asset["loan_quantity"]) - intval($asset["quantity_track"]);
                
                $status = "";
                
                if($diff > 0){
                    $status .= abs($diff) . " Unit(s) missing.\n";
                }else if($diff < 0){
                    $status .= abs($diff) . " Unit(s) Wrongly Placed Into This Department.\n";
                }
                
                $status .= $asset["loan_quantity"] . " unit(s) loaned out";
                
                $asset["scanned_time"] = date("d-F-Y g:ia", strtotime($asset["scanned_time"]));
                if(intval(date("Y", strtotime($asset["scanned_time"]))) <= 1990){
                    $asset["scanned_time"] = "No Scan Record";           
                }
                
                $objWorkSheet
                    ->setCellValue('A' . $current_row_count, $asset["scanned_time"])
                    ->setCellValue('B' . $current_row_count, $asset["assets_name"])
                    ->setCellValue('C' . $current_row_count, $asset["barcode"])
                    ->setCellValue('D' . $current_row_count, $asset["track_remark"])
                    ->setCellValue('E' . $current_row_count, $asset["total_quantity"])
                    ->setCellValue('F' . $current_row_count, $asset["quantity_track"])
                    ->setCellValue('G' . $current_row_count, $status)
                ;
            }
            
            /* Align Center */  
            $objWorkSheet->getStyle('A2:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);          
        }
        
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Discrepancies Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    
    /* 
     * Download excel report for writeoff transaction 
     *
     * $parameters["start_date"]
     * $parameters["end_date"]
     * $parameters["departments"] = array()
     * 
     * */
    function downloadWriteOffReport($parameters){
        
        $parameters["departments"] = array_unique($parameters["departments"]);
        
        $departments_names = array();
        
        $this->db->where_in("id", $parameters["departments"]);
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $department){
            $departments_names[$department["id"]] = $department["departments_name"];
        }
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Write Off report For Each Department")
                             ->setSubject("Write Off report For Each Department")
                             ->setDescription("This document describes asset write off information for selected department")
                             ->setKeywords("asset writeoff report department")
                             ->setCategory("Report");
        
        $objPHPExcel->removeSheetByIndex(0);
        
        $header = array("A2"=>20, "B2"=>20, "C2"=>20, "D2"=>20, "E2"=>15, "F2"=>20, "G2"=>15, "H2"=>15, "I2"=>15, "J2"=>15, "K2"=>15, "L2"=>25, "M2"=>15, "N2"=>17, "O2"=>18, "P2"=>17, "Q2"=>17);
      
        $writeoff_parameters["departments"] = $parameters["departments"];
        $writeoff_parameters["page_no"] = 1;
        $writeoff_parameters["count_per_page"] = 5000;
        $writeoff_parameters["sort"] = "desc";
        $writeoff_parameters["sort_field"] = "datetime_created";   
        $writeoff_parameters["start_date"] = mysql_datetime($parameters["start_date"] . " 00:00:00");
        $writeoff_parameters["end_date"] = mysql_datetime($parameters["end_date"] . " 23:59:59");
        
        $writeoff = $this->writeoffmodel->getWriteOffByDepartments($writeoff_parameters);
        
        $sheet_count = 0;
        
        if(count($writeoff) == 0){
            echo "No record found";
            return FALSE;
        }
        
        foreach($writeoff as $departments_id=>$assets){
            
            $current_row_count = 1;
            
            $objWorkSheet = $objPHPExcel->createSheet(++$sheet_count);
            
            $objWorkSheet->setTitle(isset($departments_names[$departments_id])? ellipsize(remove_invalid_sheet_title_character($departments_names[$departments_id]),31,1,"") : "No Department Name");
            
            $objWorkSheet->getRowDimension(++$current_row_count)->setRowHeight(35);
            
            foreach($header as $key=>$head){
                $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
                $objWorkSheet->getStyle($key)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FFFF00')),'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),'font'  => array('bold'  => true,'size'  => 11,'name'  => 'Calibri'),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=> TRUE)));
            }
            
            $objWorkSheet->getRowDimension(1)->setRowHeight(48);
            
            $objWorkSheet->mergeCells("A1:D1");
            $objWorkSheet->getStyle('A1:D1')->applyFromArray(array('font' => array('bold' => true,'size' => 36,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'wrap'=> FALSE)));
            
            $objWorkSheet
                ->setCellValue('A1', 'Write Off Report')
                ->setCellValue('A2', 'Request Date')
                ->setCellValue('B2', 'Request Time')
                ->setCellValue('C2', 'Approved / Rejected Date')
                ->setCellValue('D2', 'Approved / Rejected Time')
                ->setCellValue('E2', 'Status')
                ->setCellValue('F2', 'Asset')
                ->setCellValue('G2', 'Asset ID (Barcode)')
                ->setCellValue('H2', 'Type')
                ->setCellValue('I2', 'Location')
                ->setCellValue('J2', 'Quantity')
                ->setCellValue('K2', 'Requester')
                ->setCellValue('L2', 'Approver / Rejecter')
                ->setCellValue('M2', 'Remarks')
                ->setCellValue('N2', 'Assets Unit Price')
                ->setCellValue('O2', "Total Asset Value\n(Value x Quantity)")
                ->setCellValue('P2', 'Current Depreciation')
                ->setCellValue('Q2', 'Netbook Value')
                ;       
            
            foreach($assets as $asset){
                $current_row_count++;
                
                $asset["date_created"] = date("d-F-Y", strtotime($asset["datetime_created"]));
                $asset["time_created"] = date("g:ia", strtotime($asset["datetime_created"]));
                $asset["datetime_created"] = date("d-F-Y g:ia", strtotime($asset["datetime_created"]));
                if(intval(date("Y", strtotime($asset["datetime_created"]))) <= 1990){
                    $asset["datetime_created"] = "-";   
                    $asset["date_created"] = "-";
                    $asset["time_created"] = "-";                               
                }
                
                $asset["date_approved"] = date("d-F-Y", strtotime($asset["datetime_approved"]));
                $asset["time_approved"] = date("g:ia", strtotime($asset["datetime_approved"]));
                $asset["datetime_approved"] = date("d-F-Y g:ia", strtotime($asset["datetime_approved"]));
                $end_date = date("d-F-Y", strtotime($asset["datetime_approved"]));
                if(intval(date("Y", strtotime($asset["datetime_approved"]))) <= 1990){
                    $asset["datetime_approved"] = "-";       
                    $end_date = "";    
                    $asset["date_approved"] = "-";
                    $asset["time_approved"] = "-";
                }
                
                $users_approver_str = "";
                
                foreach($asset["users_approver"] as $key=>$approver){
                    $approval_status = "";
                    
                    switch ($approver["status"]){
                        case "0": 
                                $approval_status = "Pending";
                                break;
                        case "1":
                                $approval_status = "Approved"; 
                                break;
                        case "-1":
                                $approval_status = "Rejected"; 
                                break;
                    }
                    
                    $users_approver_str .= $approver["person_name"] . " - (" . $approval_status . ")";
                    
                    if((count($asset["users_approver"]) > 1) && ($key < (count($asset["users_approver"]) - 1))){
                        $users_approver_str .= "\n▼ Escalate To\n";
                    }
                }
                
                switch ($asset["status"]) {
                    case "0"    : $asset["status"] = "Pending Approval"; break;
                    case "1"    : $asset["status"] = "Approved"; break;
                    case "-1"   : $asset["status"] = "Rejected"; break;
                }
                
                $assets_quantity_value = floatval($asset["assets"]["assets_value"]) * intval($asset["quantity"]);
                
                $invoice_date = date("d-F-Y", strtotime($asset["assets"]["invoice_date"]));
                if(intval(date("Y", strtotime($invoice_date))) <= 1990){
                    $invoice_date = "";           
                }
                
                $invoice_timestamp = strtotime($invoice_date);
                $lifespan = intval($asset["assets"]["assets_lifespan"]);
                $salvage_value = floatval($asset["assets"]["salvage_value"]);
                $days_per_month = 30.4375;
                $assets_value = floatval($asset["assets"]["assets_value"]);
                
                $current_depreciation = "-";
                $netbook_value = "-";
                
                /* Valid invoice date */
                if($invoice_date && $end_date && ($assets_value > 0) && ($lifespan > 0) && ($salvage_value >= 0)){
                    
                    $total_time = $lifespan * $days_per_month * 24 * 3600;
                    $time_diff = strtotime($end_date) - $invoice_timestamp + (24 * 3600); /* Inclusive start day */;
                    if($time_diff > $total_time){
                        $time_diff = $total_time;
                    }
                    $current_depreciation = intval($asset["quantity"]) * ($time_diff) * ($assets_value - $salvage_value) / ($total_time);
                    
                    $netbook_value = $assets_quantity_value - $current_depreciation;    
                    
                    $current_depreciation = number_format($current_depreciation, 2, ".", "");
                    $netbook_value = number_format($netbook_value, 2, ".", "");
                }
                
                
                $objWorkSheet
                ->setCellValue('A' . $current_row_count, $asset["date_created"])
                ->setCellValue('B' . $current_row_count, $asset["time_created"])
                ->setCellValue('C' . $current_row_count, $asset["date_approved"])
                ->setCellValue('D' . $current_row_count, $asset["time_approved"])
                ->setCellValue('E' . $current_row_count, $asset["status"])
                ->setCellValue('F' . $current_row_count, $asset["assets"]["assets_name"])
                ->setCellValue('G' . $current_row_count, $asset["assets"]["barcode"])
                ->setCellValue('H' . $current_row_count, ($asset["writeoff_type"] == "complete_writeoff")? "Complete Removal" : "Reduce quantity")
                ->setCellValue('I' . $current_row_count, $asset["origin_location"])
                ->setCellValue('J' . $current_row_count, $asset["quantity"])
                ->setCellValue('K' . $current_row_count, $asset["users_requester"]["person_name"])
                ->setCellValue('L' . $current_row_count, $users_approver_str)
                ->setCellValue('M' . $current_row_count, $asset["remark"])
                ->setCellValue('N' . $current_row_count, $asset["assets"]["assets_value"]? $asset["assets"]["assets_value"] : "No Asset Value")
                ->setCellValue('O' . $current_row_count, $assets_quantity_value)
                ->setCellValue('P' . $current_row_count, $current_depreciation)
                ->setCellValue('Q' . $current_row_count, $netbook_value)                                
                ;       
            }
                
            /* Align Center */  
            $objWorkSheet->getStyle('A2:' . strval($objWorkSheet->getHighestColumn()) . strval($objWorkSheet->getHighestRow()))
                         ->getAlignment()
                         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                         ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                         ->setWrapText(TRUE);          
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Write Off Report.xlsx"');
        $this->output->set_header('Cache-Control: max-age=0');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        $this->output->set_header('Cache-Control: cache, must-revalidate');
        $this->output->set_header ('Pragma: public');
        $this->output->_display();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /* 
     * Download excel form for loan 
     *
     * $parameters["form_date"] 
     * $parameters["form_department"]
     * $parameters["form_request_by"]
     * $parameters["form_purpose"] 
     * $parameters["loaned_assets"] = array()
     * $parameters["form_date_loan"]
     * $parameters["form_issued_by"]
     * $parameters["form_approver_name"]
     * $parameters["form_borrower_name"]
     * $parameters["form_date_return"] 
     * $parameters["form_return_by"] 
     * $parameters["form_receiving_officer"]
     * 
     * */
    function loanForm($parameters){
        
        $parameters["loaned_assets"] = array_unique($parameters["loaned_assets"]);
        
        $departments_names = "";
        
        $this->db->where("id", $parameters["form_department"]);
        $departments_query = $this->db->get("departments");
        
        foreach($departments_query->result_array() as $department){
            $departments_names = $department["departments_name"];
        }
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($this->session->userdata("person_name"))
                             ->setLastModifiedBy($this->session->userdata("person_name"))
                             ->setTitle("Asset Loan Form")
                             ->setSubject("Asset Loan Form")
                             ->setDescription("This document describes asset loan form")
                             ->setKeywords("loan form")
                             ->setCategory("Report");
        
        $header = array("A1"=>13, "B1"=>9, "C1"=>9, "D1"=>9, "E1"=>5, "F1"=>5, "G1"=>13, "H1"=>10, "I1"=>10);
        
        $current_row_count = 1;
        
        $objWorkSheet = $objPHPExcel->getSheet(0);
        
        foreach($header as $key=>$head){
            $objWorkSheet->getColumnDimension(substr($key, 0, 1))->setWidth($head);    
        }
        
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(75);
            
        $objWorkSheet->mergeCells("A" . $current_row_count . ":I" . $current_row_count);
        
		/*
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $objDrawing->setPath("./assets/images/Hotel_Fort_Canning.jpg");

        $objDrawing->setCoordinates('D' . $current_row_count);
        $objDrawing->setHeight(100);
        $objDrawing->setResizeProportional(true);
        $objDrawing->setWorksheet($objWorkSheet);
        */
        $current_row_count++;
        
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(21);
        $objWorkSheet->mergeCells("A" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array('font' => array('bold' => true,'size' => 14,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        $objWorkSheet->setCellValue('A' . $current_row_count, "OPERATING EQUIPMENT AND ASSET LOAN FORM");
        
        /* Row 4: Date */
        $current_row_count++; $current_row_count++;
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Date");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('C' . $current_row_count, $parameters["form_date"]);
        
        /* Row 5: Department, Request By */
        $current_row_count++; 
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Department");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->setCellValue('G' . $current_row_count, "Request By:");
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('C' . $current_row_count, $departments_names);
        
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('H' . $current_row_count, $parameters["form_request_by"]);
        
        /* Row 6: Purpose */
        $current_row_count++;
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Purpose");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        
        /* Row 1 Purpose */
        $objWorkSheet->mergeCells("C" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $rowOnePurpose = character_limiter($parameters["form_purpose"],60,"");
        $objWorkSheet->setCellValue('C' . $current_row_count, $rowOnePurpose);
        
        $current_row_count++;
         
        /* Row 2 Purpose */
        $objWorkSheet->mergeCells("C" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        
        if(strlen($parameters["form_purpose"]) > strlen($rowOnePurpose)){
            $objWorkSheet->setCellValue('C' . $current_row_count, trim(substr($parameters["form_purpose"], strlen($rowOnePurpose))));
        }
        
        $current_row_count++; 
        
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THICK))));
        
        $current_row_count++; $current_row_count++;
        
        /* Row 10: Loaned asset */
        $objWorkSheet->mergeCells("B" . $current_row_count . ":G" . $current_row_count);
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->setCellValue('A' . $current_row_count, "No");
        $objWorkSheet->setCellValue('B' . $current_row_count, "Item Description");
        $objWorkSheet->setCellValue('H' . $current_row_count, "Quantity");
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array('font' => array('bold' => true,'size' => 11,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER), "borders"=>array('allborders'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        
        if(is_array($parameters["loaned_assets"]) && count($parameters["loaned_assets"]) > 0){
            
            $assets_list = array();
            $assets = array();
            
            $this->db->select("*, assets_departments_loan.quantity AS loaned_quantity, assets_departments_loan.id AS assets_departments_loan_id, assets_departments_loan.datetime_created AS datetime_created");
            $this->db->join("assets_departments", "assets_departments.id = assets_departments_loan.assets_departments_id", "inner");
            $this->db->where_in("assets_departments_loan.id", $parameters["loaned_assets"]);
            $loan_query = $this->db->get("assets_departments_loan");
            
            foreach($loan_query->result() as $loan_row){
                $assets_list[] = $loan_row->assets_id;
            }
            
            if(count($assets_list) > 0){
                $this->db->where_in("id", $assets_list);
                $assets_query = $this->db->get("assets");
                
                foreach($assets_query->result_array() as $assets_row){
                    $assets[$assets_row["id"]] = $assets_row;
                }
            }
            
            foreach($loan_query->result() as $key=>$loan_row){
                        
                $current_row_count++;    
                                        
                $objWorkSheet->mergeCells("B" . $current_row_count . ":G" . $current_row_count);
                $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
                $objWorkSheet->setCellValue('A' . $current_row_count, $key + 1);
                $objWorkSheet->setCellValue('H' . $current_row_count, $loan_row->loaned_quantity);
                $objWorkSheet->setCellValue('B' . $current_row_count, "Asset data not found");
                
                if(isset($assets[$loan_row->assets_id])){
                    $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(40);
                    
                    $asset_unit = $assets[$loan_row->assets_id];
                    $asset_string = "[" . $asset_unit["barcode"] . "] " . $asset_unit["assets_name"] . "\n" . "Asset From: " . $loan_row->location;
                    
                    if(strlen($loan_row->remark) > 0){
                        $asset_string .= "\nRemark: " . $loan_row->remark;
                        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(50); 
                    }
                    
                    $objWorkSheet->setCellValue('B' . $current_row_count, $asset_string);
                }
                
                $objWorkSheet->getStyle("A" . $current_row_count)->applyFromArray(array('font' => array('size' => 11,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER), "borders"=>array('allborders'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
                $objWorkSheet->getStyle("B" . $current_row_count . ":G" . $current_row_count)->applyFromArray(array('font' => array('size' => 11,'name' => 'Calibri'),'alignment' => array('wrap'=> TRUE, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT), "borders"=>array('allborders'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
                $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array('font' => array('size' => 11,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER), "borders"=>array('allborders'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));                    
            }
        }
        
        $current_row_count++; $current_row_count++; 
        
        /* Row 15: Date Loan */
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Date Loan");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->mergeCells("F" . $current_row_count . ":G" . $current_row_count);
        $objWorkSheet->setCellValue('F' . $current_row_count, "Date Return:");
        
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('C' . $current_row_count, $parameters["form_date_loan"]);
        
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('H' . $current_row_count, $parameters["form_date_return"]);
        
        $current_row_count++; 
        
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        
        $current_row_count++;
         
        /* Row 17: Issued By */
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Issued By");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->mergeCells("F" . $current_row_count . ":G" . $current_row_count);
        $objWorkSheet->setCellValue('F' . $current_row_count, "Returned By:");
        
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('C' . $current_row_count, $parameters["form_issued_by"]);
        
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('H' . $current_row_count, $parameters["form_return_by"]);
        
        $current_row_count++; 
        
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        
        $current_row_count++;
        
        /* Row 19: Received By */
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Received By");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->mergeCells("F" . $current_row_count . ":G" . $current_row_count);
        $objWorkSheet->setCellValue('F' . $current_row_count, "Received By:");
        
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('C' . $current_row_count, $parameters["form_borrower_name"]);

        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('H' . $current_row_count, $parameters["form_receiving_officer"]);
        
        
        
        $current_row_count++; 
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $current_row_count++;
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THICK))));
        $current_row_count++;
        
        $objWorkSheet->mergeCells("A" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Note: Approval to be obtained prior to lending out the equipment to another department.");
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array('font' => array('bold' => true,'size' => 11,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        
        $current_row_count++; $current_row_count++;
        
        /* Row 24: Approved By */
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Approved By");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->mergeCells("F" . $current_row_count . ":G" . $current_row_count);
        $objWorkSheet->setCellValue('F' . $current_row_count, "Approved By:");
        
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->setCellValue('C' . $current_row_count, $parameters["form_approver_name"]);
        
        /* Row 25: After Approved By */
        $current_row_count++;
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->setCellValue('C' . $current_row_count, "(Department Head)");
        $objWorkSheet->setCellValue('H' . $current_row_count, "(GM/FC)");        
        
        /* Row 26: Date Bottom */
        $current_row_count++;
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Date");
        $objWorkSheet->setCellValue('B' . $current_row_count, ":");
        $objWorkSheet->mergeCells("F" . $current_row_count . ":G" . $current_row_count);
        $objWorkSheet->setCellValue('F' . $current_row_count, "Date:");
        $objWorkSheet->mergeCells("C" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->getStyle("C" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        $objWorkSheet->mergeCells("H" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->getStyle("H" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array("borders"=>array('bottom'=>array("style"=>PHPExcel_Style_Border::BORDER_THIN))));
        
        $current_row_count++;
        $objWorkSheet->getRowDimension($current_row_count)->setRowHeight(20);
        
        /* Row 28: Footer */
        $current_row_count++;
        $objWorkSheet->mergeCells("A" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->setCellValue('A' . $current_row_count, "Owned and Managed By My Company Pte Ltd (200000000A)");
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array('font' => array('size' => 10,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        
        $current_row_count++;
        $objWorkSheet->mergeCells("A" . $current_row_count . ":I" . $current_row_count);
        $objWorkSheet->setCellValue('A' . $current_row_count, "My Address Here, Singapore 800000");
        $objWorkSheet->getStyle("A" . $current_row_count . ":I" . $current_row_count)->applyFromArray(array('font' => array('size' => 10,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));

        $current_row_count++;
        $objWorkSheet->mergeCells("B" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->setCellValue('B' . $current_row_count, "Tel: (65) 6543 1234");
        $objWorkSheet->getStyle("B" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array('font' => array('size' => 10,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        $objWorkSheet->mergeCells("F" . $current_row_count . ":H" . $current_row_count);
        $objWorkSheet->setCellValue('F' . $current_row_count, "Fax: (65) 6543 1234");
        $objWorkSheet->getStyle("F" . $current_row_count . ":H" . $current_row_count)->applyFromArray(array('font' => array('size' => 10,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));
        
        $current_row_count++;
        $objWorkSheet->mergeCells("B" . $current_row_count . ":D" . $current_row_count);
        $objWorkSheet->setCellValue('B' . $current_row_count, "Website: www.example.com");
        $objWorkSheet->getStyle("B" . $current_row_count . ":D" . $current_row_count)->applyFromArray(array('font' => array('size' => 10,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        $objWorkSheet->mergeCells("E" . $current_row_count . ":H" . $current_row_count);
        $objWorkSheet->setCellValue('E' . $current_row_count, "Email: contact@example.com");
        $objWorkSheet->getStyle("E" . $current_row_count . ":H" . $current_row_count)->applyFromArray(array('font' => array('size' => 10,'name' => 'Calibri'),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        
        
        $this->output->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->output->set_header('Content-Disposition: attachment;filename="' . date("Ymd") . ' - Loan Form.xlsx"');
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
