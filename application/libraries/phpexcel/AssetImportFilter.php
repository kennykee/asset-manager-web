<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AssetImportFilter implements PHPExcel_Reader_IReadFilter {
    
    private $current_row = 0;
    
    public function readCell($column, $row, $worksheetName = '') {
        if (($row == $this->current_row) && (strlen(trim($column)) == 1)) {
            return true;
        }
        return false;
    }
    
    public function setRow($current_row){
        $this->current_row = $current_row;
    }
}

?>