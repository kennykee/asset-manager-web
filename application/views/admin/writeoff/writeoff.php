<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Departments &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", $departments, $current_department_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("writeoff_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <?php if(!$current_department_id){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div>
            
            <?php }else{ ?>
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?> - Write Off Request List</h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkMinimumAccess($this->config->item("writeoff_request", "routes_uri"))){?>    
                <div class="pull-left small-margin-right"><a href="<?php echo site_url($this->config->item("writeoff_request", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-plus"></i> Request for Asset Write Off</a></div>
                <?php } ?>
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#download-popup" class="btn btn-primary"><i class="fa fa-download fa-fw"></i> Download Report</button></div>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center vertical-middle">Status</th>
                            <th class="col-md-2 align-center vertical-middle">Write Off Date</th>
                            <th class="col-md-2 align-center vertical-middle">Asset</th>
                            <th class="col-md-1 align-center vertical-middle">Type</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity</th>
                            <th class="col-md-1 align-center vertical-middle">Location</th>
                            <th class="col-md-2 align-center vertical-middle">User</th>
                            <th class="col-md-1 align-center vertical-middle">Remarks</th>
                            <th class="col-md-1 align-center vertical-middle"></th>
                        </tr>
                    </thead>
                    <tbody> 
                            <?php foreach($current_data as $writeoff){ ?>
                                <tr>
                                    <td class="align-center">
                                        <div>
                                            <b>
                                            <?php 
                                                switch ($writeoff["status"]) {
                                                    case "0"    : echo "<span class='red-text'><i class='fa fa-exclamation-triangle'></i> Pending Approval</span>"; break;
                                                    case "1"    : echo "<span class='green-text'><i class='fa fa-check'></i> Approved</span>"; break;
                                                    case "-1"   : echo "<span class='gray-text'><i class='fa fa-ban'></i> Rejected</span>"; break;
                                                }
                                            ?>
                                            </b>
                                        </div>
                                    </td>
                                    <td class="align-center">
                                        <div>
                                            <b>Request Date: </b>
                                            <div><?php echo date("j F Y", strtotime($writeoff["datetime_created"])); ?></div>
                                            <div><?php echo date("g:i a", strtotime($writeoff["datetime_created"])); ?></div>
                                        </div>
                                        
                                        <div class="small-margin-top">
                                            <b><?php echo (($writeoff["status"]=="-1")? "Rejected":"Approved"); ?> Date: </b>
                                            <?php if($writeoff["datetime_approved"]){ ?>
                                                <div><?php echo date("j F Y", strtotime($writeoff["datetime_approved"])); ?></div>
                                                <div><?php echo date("g:i a", strtotime($writeoff["datetime_approved"])); ?></div>
                                            <?php }else{ ?>
                                                <div>-</div>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="margin-bottom: 3px;">
                                            <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $writeoff["assets_id"]); ?>">     
                                                <b class="vertical-top"><?php echo $writeoff["assets"]["assets_name"]; ?></b>
                                            </a>
                                        </div>
                                        <div>
                                            <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $writeoff["assets"]["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                                <img class="img-thumbnail" style="vertical-align: top" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $writeoff["assets"]["attachments_id"] . "/crop/40/40"); ?>"/></a>
                                                
                                            <div style="display: inline-block">
                                                <div>
                                                    <b>ID:</b> <?php echo $writeoff["assets"]["barcode"]; ?>
                                                </div>
                                                <div>
                                                    <b>
                                                    <?php 
                                                        switch ($writeoff["assets"]["status"]) {
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
                                    <td class="align-center">
                                        <?php
                                            switch($writeoff["writeoff_type"]){
                                                case "reduce_quantity":
                                                    echo "Reduce quantity"; 
                                                    break;
                                                case "complete_writeoff":
                                                    echo "Complete removal"; 
                                                    break;  
                                            } 
                                        ?>
                                    </td>
                                    <td class="align-center">
                                        <?php echo $writeoff["quantity"]; ?>
                                    </td>
                                    <td class="align-center">
                                        <div><i class="fa fa-institution"></i> <?php echo $writeoff["origin_departments_name"]; ?></div>
                                        <div><?php echo $writeoff["origin_location"];?></div>
                                    </td>
                                    <td class="">
                                        <div>
                                            <h4 class="no-margin-top">Requester:</h4>
                                            <div>
                                                <a href="mailto:<?php echo $writeoff["users_requester"]["email"]; ?>">
                                                    <i class="fa fa-user"></i> <?php echo $writeoff["users_requester"]["person_name"]; ?>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="small-margin-top"><h4>Approvers: </h4></div>
                                        
                                        <div style="">
                                        
                                        <?php foreach($writeoff["users_approver"] as $key=>$approver){ ?>
                                            
                                            <div class="small-margin-top small-margin-left small-margin-bottom" style="padding: 5px;background-color: #FFFF33">    
                                                <b>
                                                    <?php 
                                                        switch ($approver["status"]){
                                                            case "0": 
                                                                    echo "<i class='fa fa-exclamation-triangle'></i> Pending: ";
                                                                    break;
                                                            case "1":
                                                                    echo "<i class='fa fa-check'></i> Approved: "; 
                                                                    break;
                                                            case "-1":
                                                                    echo "<i class='fa fa-times'></i> Rejected: "; 
                                                                    break;
                                                        }
                                                    ?>
                                                </b>
                                                <div class="small-margin-left">
                                                    <a href="mailto:<?php echo $approver["email"]; ?>">
                                                        <i class="fa fa-user"></i> <?php echo $approver["person_name"]; ?>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                                <?php 
                                                    if((count($writeoff["users_approver"]) > 1) && ($key < (count($writeoff["users_approver"]) - 1))){
                                                        echo "<b class='red-text'><i class='fa fa-chevron-down'></i> Escalate To</b>";
                                                    }
                                                ?>
                                            
                                        <?php } ?>
                                        
                                        </div>
                                        
                                    </td>
                                    <td class=""><?php echo $writeoff["remark"] ?></td>
                                    <td class="">
                                        <?php if(($writeoff["status"] == "0") && ($writeoff["next_approver"] == $this->session->userdata("users_id")) && $this->matrix->checkSingleAccess($this->config->item("writeoff_update_access", "routes_uri"), $current_department_id)){?>
                                        <button type="button" class="btn btn-block btn-success btn-sm update-button" data-action="1" data-quantity="<?php echo $writeoff["quantity"] ?>" data-write-type="<?php echo $writeoff["writeoff_type"] ?>" data-uri="<?php echo site_url($this->config->item("writeoff_approve", "routes_uri") . "/" . $writeoff["id"]); ?>" data-remark="<?php echo htmlspecialchars($writeoff["remark"], ENT_QUOTES); ?>"><i class="fa fa-check"></i> Approve Request</button>
                                        <button type="button" class="btn btn-block btn-danger btn-sm update-button small-margin-top" data-action="-1" data-quantity="<?php echo $writeoff["quantity"] ?>" data-write-type="<?php echo $writeoff["writeoff_type"] ?>" data-uri="<?php echo site_url($this->config->item("writeoff_approve", "routes_uri") . "/" . $writeoff["id"]); ?>" data-remark="<?php echo htmlspecialchars($writeoff["remark"], ENT_QUOTES); ?>"><i class="fa fa-times"></i> Reject Request</button>
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
                <h4 class="modal-title">Approve / Reject Write Off</h4>
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
                    <h4>Things to note:</h4>
                    <p>1) Once write off request is approved, asset will be written off immediately.</p>
                    <p>2) Write off is not reversible. Check carefully before proceeding.</p>
                    <p>3) Write off quantity cannot be more than available quantity. Return loaned asset before writting off.</p>
                </div>
                
                <div class="form-group">
                    <label for="process_request" class="red-text">Approve / Reject</label>
                    <?php echo form_dropdown("process_request", array("1"=>"Approve", "-1"=>"Reject"), "", 'id="process_request" class="form-data form-control"'); ?>
                </div>
                
                <div class="form-group">
                    <label for="writeoff_type">Write Off Type</label>
                    <?php echo form_dropdown("type", array("complete_writeoff"=>"Completely remove this asset (Ensure enter all available quantity)", "reduce_quantity"=>"Remove certain quantity from this asset"), "", 'id="writeoff_type" class="form-data form-control"'); ?>
                    <input type="hidden" class="routes_uri" name="routes_uri" value=""/>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Write Off Quantity</label>
                    <input type="number" min="1" class="form-data form-control" id="quantity" name="quantity" value="" placeholder="Quantity" required/>
                </div>
                
                <div class="form-group">
                    <label for="update_remark">Remarks</label>
                    <textarea class="form-data form-control" name="remark" id="update_remark" placeholder="Remark"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <span class="post-message"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success post-button" data-container-id="update-popup"><i class="fa fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="download-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Download Write Off Report</h4>
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
                    <p>1) Select start date and end date for write off transaction date.</p>
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
                    <div><button type="button" class="btn btn-primary add-location-dropdown">Add Department</button></div>
                </div>
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("writeoff_report", "routes_uri")); ?>"/>
                
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
    operation_queue.push("init_writeoff_view");
</script>