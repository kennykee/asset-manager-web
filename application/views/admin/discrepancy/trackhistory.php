<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Departments &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", $departments, $current_department_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("discrepancy_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <?php if(!$current_department_id){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div>
            
            <?php }else{ ?>
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?> - Tracking History</h1>
            
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                
                <?php foreach($tabs as $key=>$tab){ ?>
                    
                    <li class="<?php echo (($key==$current_tab)? "active": ""); ?>">
                        <a href="<?php echo site_url($this->config->item("discrepancy_view", "routes_uri") . "/" . $current_department_id . "?tab=" . $key); ?>">
                            <?php echo (($key==$current_tab)? ("<b>" . $tab . "</b>"): $tab); ?>
                        </a>
                    </li>
                            
                <?php } ?>
            </ul>
            
            <div class="clearfix">
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center vertical-middle"><b>No</b></th>
                            <th class="col-md-2 align-center vertical-middle">DateTime Scanned</th>
                            <th class="col-md-2 align-center vertical-middle">Asset</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity</th>
                            <th class="col-md-2 align-center vertical-middle">Uploaded By User</th>
                            <th class="col-md-2 align-center vertical-middle">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $count = ($page_no-1) * $count_per_page; 
                            foreach($current_data as $key=>$track){ ?>
                            <tr>
                                <td class="col-md-1 align-center"><b><?php echo ++$count; ?></b></td>
                                <td class="col-md-1 align-center">
                                    <div><?php echo date("j F Y", strtotime($track["datetime_scanned"])); ?></div>
                                    <div><?php echo date("g:i a", strtotime($track["datetime_scanned"])); ?></div>
                                </td>
                                <td class="col-md-2">
                                    <div style="margin-bottom: 3px;">
                                        <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $track["assets_id"]); ?>">     
                                            <b class="vertical-top"><?php echo $track["assets"]["assets_name"]; ?></b>
                                        </a>
                                    </div>
                                    <div>
                                        <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $track["assets"]["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                            <img class="img-thumbnail" style="vertical-align: top; width:50px;" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $track["assets"]["attachments_id"] . "/crop/40/40"); ?>"/></a>
                                            
                                        <div style="display: inline-block">
                                            <div>
                                                <b>ID:</b> <?php echo $track["assets"]["barcode"]; ?>
                                            </div>
                                            <div>
                                                <b>
                                                <?php 
                                                    switch ($track["assets"]["status"]) {
                                                        case 'available'    : echo "<span class='green-text'><i class='fa fa-check'></i> Available</span>"; break;
                                                        case 'write_off'    : echo "<span class='gray-text'><i class='fa fa-times'></i> Written Off</span>"; break;
                                                        case 'loan_out'     : echo "<span class='red-text'><i class='fa fa-ban'></i> On Loan</span>"; break;
                                                        case 'out_of_stock': echo "<span class='red-text'><i class='fa fa-ban'></i> Out Of Stock</span>"; break;
                                                        case 'maintenance': echo "<span class='red-text'><i class='fa fa-ban'></i> Maintenance</span>"; break;
                                                        case 'unavailable': echo "<span class='red-text'><i class='fa fa-ban'></i> Not Available</span>"; break;
                                                    }
                                                ?>
                                                </b>
                                            </div>
                                         </div>
                                    </div>
                                </td>
                                <td class="col-md-1 align-center"><?php echo $track["quantity"]; ?></td>
                                <td class="col-md-1 align-center">
                                    <a href="mailto:<?php echo $track["users"]["email"]; ?>">
                                        <i class="fa fa-user"></i>
                                        <?php echo $track["users"]["person_name"]; ?>
                                    </a>
                                </td>
                                <td class="col-md-1 align-center"><?php echo $track["remark"]; ?></td>
                            </tr>
                        <?php } ?>        
                    </tbody>
                </table>
            </div>
            
            <?php } ?>
            
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_discrepancy_view");
    operation_data = <?php echo json_encode(array("uri"=>$this->config->item("loan_view", "routes_uri"), "tab"=>$current_tab, "department_tab"=>$current_tab)); ?>;
</script>