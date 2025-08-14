<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Departments &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", $departments, $current_department_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("transfer_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <?php if(!$current_department_id){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div>
            
            <?php }else{ ?>
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?> - Transfer History</h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkMinimumAccess($this->config->item("transfer_update_access", "routes_uri"))){?>    
                <div class="pull-left small-margin-right"><a href="<?php echo site_url($this->config->item("transfer_add", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-exchange"></i> Transfer Asset Location or Department</a></div>
                <?php } ?>
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#download-popup" class="btn btn-primary"><i class="fa fa-download fa-fw"></i> Download Report</button></div>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-2 align-center vertical-middle">Transfer Date</th>
                            <th class="col-md-2 align-center vertical-middle">Asset</th>
                            <th class="col-md-1 align-center vertical-middle">Transfer Type</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity</th>
                            <th class="col-md-1 align-center vertical-middle">Origin</th>
                            <th class="col-md-1 align-center vertical-middle">Destination</th>
                            <th class="col-md-1 align-center vertical-middle">Transferred <br/> By User</th>
                            <th class="col-md-1 align-center vertical-middle">Remarks</th>
                            <th class="col-md-1 align-center vertical-middle"></th>
                        </tr>
                    </thead>
                    <tbody> 
                            <?php foreach($current_data as $transfer){ ?>
                                <tr>
                                    <td class="col-md-2 align-center">
                                        <div><?php echo date("j F Y", strtotime($transfer["datetime_created"])); ?></div>
                                        <div><?php echo date("g:i a", strtotime($transfer["datetime_created"])); ?></div>
                                    </td>
                                    <td class="col-md-2">
                                        <div style="margin-bottom: 3px;">
                                            <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $transfer["assets_id"]); ?>">     
                                                <b class="vertical-top"><?php echo $transfer["assets"]["assets_name"]; ?></b>
                                            </a>
                                        </div>
                                        <div>
                                            <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $transfer["assets"]["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                                <img class="img-thumbnail" style="vertical-align: top" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $transfer["assets"]["attachments_id"] . "/crop/40/40"); ?>"/></a>
                                                
                                            <div style="display: inline-block">
                                                <div>
                                                    <b>ID:</b> <?php echo $transfer["assets"]["barcode"]; ?>
                                                </div>
                                                <div>
                                                    <b>
                                                    <?php 
                                                        switch ($transfer["assets"]["status"]) {
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
                                    <td class="col-md-1 align-center">
                                        <?php
                                            switch($transfer["transaction_type"]){
                                                case "transfer_department":
                                                    echo "Department <br /><i class='fa fa-exchange'></i><br /> Department"; 
                                                    break;
                                                case "transfer_location":
                                                    echo "Location <br /><i class='fa fa-exchange'></i><br /> Location"; 
                                                    break;  
                                            } 
                                        ?>
                                    </td>
                                    <td class="col-md-1 align-center">
                                        <?php echo $transfer["quantity"]; ?>
                                    </td>
                                    <td class="col-md-1 align-center">
                                        <div><i class="fa fa-institution"></i> <?php echo $transfer["origin_departments_name"]; ?></div>
                                        <div><?php echo $transfer["origin_location"];?></div>
                                    </td>
                                    <td class="col-md-1 align-center">
                                        <div><i class="fa fa-institution"></i> <?php echo $transfer["destination_departments_name"]; ?></div>
                                        <div><?php echo $transfer["destination_location"]; ?></div>
                                    </td>
                                    <td class="col-md-1 align-center">
                                        <a href="mailto:<?php echo $transfer["users"]["email"]; ?>">
                                            <i class="fa fa-user"></i>
                                            <?php echo $transfer["users"]["person_name"]; ?>
                                        </a>
                                    </td>
                                    <td class="col-md-1"><?php echo $transfer["remark"] ?></td>
                                    <td class="col-md-1">
                                        <?php if($this->matrix->checkSingleAccess($this->config->item("transfer_update_access", "routes_uri"), $current_department_id)){?>
                                        <button type="button" class="btn btn-primary btn-sm update-button" data-uri="<?php echo site_url($this->config->item("transfer_update", "routes_uri") . "/" . $transfer["id"]); ?>" data-remark="<?php echo htmlspecialchars($transfer["remark"], ENT_QUOTES); ?>"><i class="fa fa-pencil"></i> Edit Remarks</button>
                                        <?php } ?>
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

<div class="modal fade" id="update-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Update Transfer Remarks</h4>
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
                    <h4>Transfer Editing Rule</h4>
                    <p>Note that transaction history cannot be edited. You may update remarks only.</p>
                </div>
                
                <div class="form-group">
                    <label for="update_remark">Remarks</label>
                    <textarea class="form-data form-control" name="remark" id="update_remark" placeholder="Update remark"></textarea>
                    <input type="hidden" class="routes_uri" name="routes_uri" value=""/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary post-button" data-container-id="update-popup">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="download-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Download Transfer Report</h4>
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
                    <p>1) Select start date and end date for transfer transaction.</p>
                    <p>2) Select department(s). </p>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="text" name="start_date" id="start_date" class="form-control form-data datepicker" placeholder="Start Date" />
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="text" name="end_date" id="end_date" class="form-control form-data datepicker" placeholder="End Date" />
                </div>
                
                <div class="form-group departments_dropdown">
                    <label for="departments_report_dropdown">Departments</label>
                    <div class="departments_list_dropdown"></div>
                    <div>
                        <button type="button" class="btn btn-primary add-location-dropdown">Add Department</button>
                        <button type="button" class="btn btn-success add-all-departments">Select All Departments</button>
                    </div>
                </div>
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("transfer_report", "routes_uri")); ?>"/>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary get-button" data-container-id="download-popup"><i class="fa fa-download"></i> Download</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_data = <?php echo json_encode(array("departments"=>$departments)); ?>;
    operation_queue.push("init_transfer_view");
</script>