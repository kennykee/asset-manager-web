<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Pagination_output {
    
    private $page = ""; 
    public  $current_url = "";
    public  $total_rows = 0;
    public  $page_no = 0;
    public  $sort = "asc";
    public  $sort_field = "id";
    public  $count_per_page = 0;
    public  $query_string = "";
    
    public function __construct(){
        //Silence is golden
    }
    
    public function setCurrentURL($current_url){
        $this->current_url = $current_url;
    }
    
    public function setTotalRows($total_rows){
        $this->total_rows = intval($total_rows);
    }
    
    public function setPageNo($page_no){
        $this->page_no = intval($page_no);
    }
    
    public function setSort($sort){
        $this->sort = $sort;
    }
    
    public function setSortField($sort_field){
        $this->sort_field = $sort_field;
    }
    
    public function setCountPerPage($count_per_page){
        $this->count_per_page = intval($count_per_page);
    }
    
    public function setQueryString($query_string){
        $this->query_string = $query_string;
    }
    
    public function build(){
    
        $query_string = !empty($this->query_string)? ("&" . $this->query_string) : "";
        
        $from =  (($this->page_no - 1) * $this->count_per_page) + 1;
        $to = $from + $this->count_per_page - 1;
        
        $total_page = intval(ceil($this->total_rows/$this->count_per_page));
        
        $from_url_array = array("page_no" => max(($this->page_no - 1),1), 
                                "count_per_page" => $this->count_per_page, 
                                "sort" => $this->sort, 
                                "sort_field" => $this->sort_field);
        $to_url_array =   array("page_no" => min(($this->page_no + 1), $total_page),
                                "count_per_page" => $this->count_per_page, 
                                "sort" => $this->sort, 
                                "sort_field" => $this->sort_field);
                                
        $active_from = ($this->page_no <= 1)? TRUE : FALSE;
        $active_to = ($this->page_no >= $total_page)? TRUE : FALSE;
        
        if($from <= $this->total_rows && $to > $this->total_rows){
            $to = $this->total_rows;
        }
        if($this->total_rows == 0){
            $from = 0;
            $to = 0;
            $from_url_array["page_no"] = 1;
            $to_url_array["page_no"] = 1;
            $active_from = FALSE;
            $active_to = FALSE;
        } 
        
        $previous_str = '<li class="' . ($active_from? "disabled" : "") . '">' . ((!$active_from)? ('<a href="' . $this->current_url . '?' . http_build_query($from_url_array) . $query_string . '"><i class="fa fa-chevron-left"></i></a>') : ("<span><i class='fa fa-chevron-left'></i></span>")) . '</li>';
        $next_str = '<li class="' . ($active_to? "disabled" : "") . '">' . ((!$active_to)? ('<a href="' . $this->current_url . '?' . http_build_query($to_url_array) . $query_string . '"><i class="fa fa-chevron-right"></i></a>') : ("<span><i class='fa fa-chevron-right'></i></span>")) . '</li>';
        
        $str = '<ul class="pagination no-margin-top no-margin-bottom">';
        $str .= '<li><span class="showing-box">Showing ' . $from . ' &raquo; ' . $to . ' OF ' . $this->total_rows . '</span></li>';
        $str .= $previous_str;
        
        for($i = 1; $i <= $total_page; $i++){
            $current_selected = ($i==$this->page_no);
            $get_param = http_build_query(array("page_no" => $i, "count_per_page" => $this->count_per_page, "sort" => $this->sort, "sort_field" => $this->sort_field));
            $str .= '<li class="' . ($current_selected? 'active':'') . '">' . ((!$current_selected)?('<a href="' . $this->current_url . '?' . $get_param . $query_string .  '">' . $i . '</a>'):('<span>' . $i . '</span>')) . '</li>';
        }
        
        $str .= $next_str;
        $str .= '</ul>';
        
        $this->page = $str;
    }
    
    public function getPageHTML(){
        return $this->page;
    }
    
    public function resetPagination(){
        $this->page = "";
        $this->current_url = "";
        $this->total_rows = 0;
        $this->page_no = 0;
        $this->sort = "asc";
        $this->sort_field = "id";
        $this->count_per_page = 0;
        $this->query_string = "";
    }
}
