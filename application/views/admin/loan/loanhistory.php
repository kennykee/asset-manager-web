<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Departments &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", $departments, $current_department_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("loan_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <?php if(!$current_department_id){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div>
            
            <?php }else{ ?>
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?> - Loan History</h1>
            
            <div>
                <?php if($this->matrix->checkMinimumAccess($this->config->item("loan_update_access", "routes_uri"))){?>
                <div class="pull-right">
                    <a href="<?php echo site_url($this->config->item("loan_add", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-plus"></i> Make a New Loan</a>
               </div>
                <?php } ?>
            </div>
            
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                
                <?php foreach($tabs as $key=>$tab){ ?>
                    
                    <li class="<?php echo (($key==$current_tab)? "active": ""); ?>">
                        <a href="<?php echo site_url($this->config->item("loan_view", "routes_uri") . "/" . $current_department_id . "?tab=" . $key); ?>">
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
                            <th class="col-md-1 align-center vertical-middle">Transaction Date</th>
                            <th class="col-md-2 align-center vertical-middle">Asset</th>
                            <th class="col-md-1 align-center vertical-middle">Type</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity</th>
                            <th class="col-md-1 align-center vertical-middle">Loan To</th>
                            <th class="col-md-1 align-center vertical-middle">Approver</th>
                            <th class="col-md-1 align-center vertical-middle">Loan Status</th>
                            <th class="col-md-1 align-center vertical-middle">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($current_data as $key=>$loan){ ?>
                            <tr>
                                <td class="col-md-1 align-center"><b><?php echo $key + 1; ?></b></td>
                                <td class="align-center">
                                    <?php 
                                        switch($loan["transaction_type"]){
                                            case "loan": 
                                                    echo "<div>" . date("j F Y", strtotime($loan["loan_datetime"])) . "</div>";
                                                    echo "<div>" . date("g:i a", strtotime($loan["loan_datetime"])) . "</div>";
                                                break;
                                            case "return": 
                                                    echo "<div>" . date("j F Y", strtotime($loan["return_datetime"])) . "</div>";
                                                    echo "<div>" . date("g:i a", strtotime($loan["return_datetime"])) . "</div>";
                                                break;    
                                        }
                                    ?>
                                </td>
                                <td class="col-md-2">
                                    <div style="margin-bottom: 3px;">
                                        <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $loan["assets_id"]); ?>">     
                                            <b class="vertical-top"><?php echo $loan["assets"]["assets_name"]; ?></b>
                                        </a>
                                    </div>
                                    <div style="margin-bottom: 3px;">
                                        <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $loan["assets"]["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                            <img class="img-thumbnail" style="vertical-align: top" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $loan["assets"]["attachments_id"] . "/crop/40/40"); ?>"/></a>
                                            <b>ID:</b> <?php echo $loan["assets"]["barcode"]; ?>
                                        <div style="display: inline-block">
                                            <div><b>From: </b><?php echo $loan["origin_location"]; ?></div>
                                         </div>
                                    </div>
                                </td>
                                <td class="col-md-1 align-center">
                                    <?php 
                                        switch($loan["transaction_type"]){
                                            case "loan": 
                                                    echo "<b>Loan Out</b>";
                                                break;
                                            case "return": 
                                                    echo "<b>Return Asset</b>";
                                                break;    
                                        }
                                    ?>
                                </td>
                                <td class="col-md-1 align-center">
                                    <?php echo $loan["quantity"]; ?>
                                </td>
                                <td class="col-md-1">
                                    <b>Borrower:</b>
                                    <div>
                                        <?php echo $loan["borrower_name"]; ?>
                                    </div>
                                    <div class="small-margin-top"><b>Company:</b></div>
                                    <div>
                                        <?php echo $loan["borrower_entity"]; ?>
                                    </div>
                                </td>
                                <td class="col-md-1">
                                    <b>Approver:</b>
                                    <div>
                                        <?php echo $loan["approver_name"]; ?>
                                    </div>
                                    <div class="small-margin-top"><b>Recorded By:</b></div>
                                    <div>
                                        <?php echo $loan["users"]["person_name"]; ?>
                                    </div>
                                </td>
                                <td class="col-md-1 align-center">
                                    <b>
                                    <?php 
                                        if($loan["remaining"] <= 0){
                                            echo "<span class='green-text'><i class='fa fa-check'></i> Returned</span>";    
                                        }else{
                                            echo "<span class='red-text'>" . $loan["remaining"] . " Pending Return</span>";
                                        }
                                    ?>
                                    </b>
                                    
                                    <div class="small-margin-top"><b>Expected Return: </b></div>
                                    <?php 
                                        $return_date = (intval($loan["loan_period"]) * 3600) + strtotime($loan["loan_datetime"]);
                                    ?>
                                    <div><?php echo date("j F Y", $return_date); ?></div>
                                    <div><?php echo date("g:i a", $return_date); ?></div>
                                </td>
                                <td class="col-md-1 align-center"><?php echo $loan["remark"]; ?></td>
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
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success post-button" data-container-id="update-popup"><i class="fa fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_loan_view");
    operation_data = <?php echo json_encode(array("uri"=>$this->config->item("loan_view", "routes_uri"), "tab"=>$current_tab, "department_tab"=>$current_tab)); ?>;
</script>