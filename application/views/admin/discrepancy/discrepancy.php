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
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?></h1>
            
            <div>
                <div class="pull-right">
                    <!-- <a href="<?php echo site_url($this->config->item("discrepancy_individual_view", "routes_uri")); ?>" class="btn btn-primary"><i class="fa fa-binoculars"></i> View Per Asset Discrepancy</a> -->
               </div>
            </div>
            
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
                <div class="pull-left button-height">
                    <form class="form-inline">
                        <div class="form-group">
                             <label for="discrepancy_compare_from">&nbsp;&nbsp;Compare From Date:</label>
                             <input class="form-control" type="text" id="discrepancy_compare_from" value="<?php echo $from_date; ?>" placeholder="Compare Asset From Date"/>
                        </div>
                        <button class="btn btn-primary show-datepicker" type="button"><i class="fa fa-binoculars fa-fw"></i> Compare</button>
                    </form>
                </div>
                <div class="pull-right small-margin-right"><button type="button" data-toggle="modal" data-target="#download-popup" class="btn btn-primary"><i class="fa fa-download fa-fw"></i> Download Report</button></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center vertical-middle">No</th>
                            <th class="col-md-2 align-center vertical-middle">Last Check Date</th>
                            <th class="col-md-3 align-center vertical-middle">Asset</th>
                            <th class="col-md-2 align-center vertical-middle">Actual Quantity</th>
                            <th class="col-md-2 align-center vertical-middle">Tracked Quantity</th>
                            <th class="col-md-2 align-center vertical-middle">Status</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php $count = ($page_no-1) * $count_per_page; 
                        foreach($current_data as $assets_id=>$discrepancy){ ?>
                        <tr>
                            <td class="col-md-1 align-center"><b><?php echo ++$count; ?></b></td>
                            <td class="col-md-2 align-center">
                                <?php if(isset($discrepancy["scanned_time"])){ ?>
                                    <div><?php echo date("j F Y", strtotime($discrepancy["scanned_time"])); ?></div>
                                    <div><?php echo date("g:i a", strtotime($discrepancy["scanned_time"])); ?></div>
                                <?php }else{ ?>
                                    <div>No Scan Record</div>
                                <?php } ?>
                            </td>
                            <td class="col-md-3">
                                <div style="margin-bottom: 3px;">
                                    <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $assets_id); ?>">     
                                        <b class="vertical-top"><?php echo $discrepancy["assets_name"]; ?></b>
                                    </a>
                                </div>
                                <div>
                                    <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $discrepancy["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                        <img class="img-thumbnail" style="vertical-align: top; width:50px;" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $discrepancy["attachments_id"] . "/crop/40/40"); ?>"/></a>
                                        
                                    <div style="display: inline-block">
                                        <div>
                                            <b>ID:</b> <?php echo $discrepancy["barcode"]; ?>
                                        </div>
                                        <div>
                                            <b>
                                            <?php 
                                                switch ($discrepancy["status"]) {
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
                            <td class="col-md-2 align-center">
                                <?php echo $discrepancy["total_quantity"]; ?>
                            </td>
                            <td class="col-md-2 align-center"><?php echo isset($discrepancy["quantity_track"])? $discrepancy["quantity_track"] : "None"; ?></td>
                            <td class="col-md-2 align-center">
                                <?php 
                                    
                                    $check = $discrepancy["balance"] - $discrepancy["quantity_track"];
                                    
                                    if($check < 0){
                                        echo '<div class="red-text"><i class="fa fa-exclamation-triangle"></i> <b>' . abs($check) . ' Unit(s) Wrongly Placed Into This Department</b></div>';
                                    }else if($check > 0){
                                        echo '<div class="red-text"><i class="fa fa-exclamation-triangle"></i> <b>' . abs($check) . ' Unit(s) Missing</b></div>';    
                                    }else{
                                        echo '<div class="green-text"><i class="fa fa-check"></i> <b>Assets Loaned Out </b></div>';
                                    }
                                
                                ?>
                                <div>
                                     <?php echo $discrepancy["loan_quantity"];?> Unit(s) Loaned Out
                                </div>
                            </td>
                        </tr>
                        <?php } ?>        
                    </tbody>
                </table>
            </div>
            
            <?php } ?>
            
        </div>
    </div>
</div>

<div class="modal fade" id="download-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Download Discrepancy Report</h4>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-success hide">
                    <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
                    <i class="fa fa-thumbs-o-up"></i>
                    <strong>Success!</strong> <span class="success-message"></span>
                </div>
                
                <div class="alert alert-danger hide">
                    <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
                    <i class="fa fa-warning"></i>
                    <strong>Error!</strong> <span class="error-message"></span>
                </div>
                
                 <div class="bs-callout">
                    <h4>Select Options</h4>
                    <p>1) Select compare start date to indicate start of tracking date.</p>
                    <p>2) Select department(s). </p>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="text" name="start_date" id="start_date" class="form-control form-data datepicker" placeholder="Start Date" />
                </div>
                
                <div class="form-group departments_dropdown">
                    <label for="departments_report_dropdown">Departments</label>
                    <div class="departments_list_dropdown"></div>
                    <div><button type="button" class="btn btn-primary add-location-dropdown">Add Department</button></div>
                </div>
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("discrepancy_report", "routes_uri")); ?>"/>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary get-button" data-container-id="download-popup"><i class="fa fa-download"></i> Download</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_data = <?php echo json_encode(array("uri"=>$this->config->item("discrepancy_view", "routes_uri"), "tab"=>$current_tab, "target_id"=>$current_department_id, "departments"=>$departments)); ?>;
    operation_queue.push("init_discrepancy_view");
</script>