<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Assets &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", array($current_asset_id => $current_asset_name . " - " . $barcode), $current_asset_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control"');
                ?>
                </div>
            </div>
            
            <h1 class="department-header"><i class="fa fa-eraser"></i> <?php echo $current_asset_name; ?> <?php echo ($barcode? (" - " . $barcode) : ""); ?></h1>
            
            <?php if(!$current_asset_id){ ?>
                
            <div class="bs-callout">
                <h4>Select an asset</h4>
                <p>Choose an asset to write off from drop down above to begin.</p>
            </div>
            
            <?php }else if(!$barcode){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div> 
                
            <?php }else{ ?>
            
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                
                <?php foreach($tabs as $key=>$tab){ ?>
                    
                    <li class="<?php echo (($key==$current_tab)? "active": ""); ?>">
                        <a href="<?php echo site_url($this->config->item("writeoff_request", "routes_uri") . "/" . $current_asset_id . "?tab=" . $key); ?>">
                            <?php echo (($key==$current_tab)? ("<b>" . $tab . "</b>"): $tab); ?>
                        </a>
                    </li>
                            
                <?php } ?>
            </ul>
            
            <div>
                <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $current_asset["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                    <img class="img-thumbnail" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $current_asset["attachments_id"] . "/showall/500/150"); ?>"/>
                </a>
            </div>
            
            <h3><i class="fa fa-cubes"></i> Asset Location</h3>
            
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center">No</th>
                            <th class="col-md-2 align-center">Department</th>
                            <th class="col-md-2 align-center">Location</th>
                            <th class="col-md-1 align-center">Quantity</th>
                            <th class="col-md-2 align-center">Status</th>
                            <th class="col-md-1 align-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($current_data as $key=>$asset){ ?>
                            
                            <tr>    
                                <td class="align-center"><b><?php echo $key+1; ?></b></td>
                                <td><?php echo $asset["departments_name"]; ?></td>
                                <td class="align-center"><?php echo $asset["location"]; ?></td>
                                <td class="align-center"><?php echo $asset["quantity"]; ?></td>
                                <td class="align-center">
                                    
                                    <?php 
                                    
                                    $available = TRUE;
                                    $quantity = 0;
                                    
                                    if(!empty($asset["loan"])){
                                        
                                        $loans = $asset["loan"];
                                        
                                        $info_string = "";
                                        
                                        foreach($loans as $loan){
                                            $quantity += intval($loan["quantity"]);
                                            $info_string .= "<div class='small-margin-top'><b>Loan to:</b> " . $loan["borrower_name"] . "/" . $loan["borrower_entity"] . "<br /> " . $loan["quantity"] . " unit(s) / " . format_period_from_hour($loan["loan_period"]) . "</div>";
                                        }
                                        
                                        if($quantity > 0){
                                            
                                            if($quantity >= $asset["quantity"]){
                                                echo "<div class='red-text'><b><i class='fa fa-ban'></i> Unavailable</b></div>";
                                                $available = FALSE;    
                                            }else{
                                                echo "<span class='green-text'><b><i class='fa fa-check'></i> " . ($asset["quantity"] - $quantity) . " Available for write off</b></span>";
                                            }
                                            
                                            echo "<div>" . $quantity . " loaned out</div>";
                                                
                                        }else{
                                            echo "<span class='green-text'><b><i class='fa fa-check'></i> Available for write off</b></span>";   
                                        }
                                        
                                        echo "<div>" . $info_string . "</div>";
                                    }else{
                                        echo "<span class='green-text'><b><i class='fa fa-check'></i> Available for write off</b></span>";
                                    }
                                    
                                    ?>
                                    
                                </td>
                                <td class="align-center">
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("writeoff_request", "routes_uri"), $asset["departments_id"]) && $available){?>
                                    <button type="button" class="btn btn-primary btn-sm update-button" 
                                            data-record-id="<?php echo $asset["assets_departments_id"]; ?>" 
                                            data-department="<?php echo htmlspecialchars($asset["departments_name"], ENT_QUOTES); ?>"
                                            data-department-id="<?php echo htmlspecialchars($asset["departments_id"], ENT_QUOTES); ?>"  
                                            data-location="<?php echo htmlspecialchars($asset["location"], ENT_QUOTES); ?>" 
                                            data-avail-quantity="<?php echo ($asset["quantity"] - $quantity); ?>" 
                                            data-uri="<?php echo site_url($this->config->item("writeoff_request_post", "routes_uri") . "/" . $current_asset_id); ?>"><i class="fa fa-eraser"></i> Request Write Off</button>
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
                <h4 class="modal-title">Request Write Off Asset</h4>
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
                    <p>4) Select at least 2 approvers.</p>
                    <p>5) <span class='update-message'></span></p>
                    <p><span class="asterisk-red">*</span> represents required fields</p>
                </div>
                
                <div class="form-group">
                    <label for="writeoff_type">Write Off Type <span class="asterisk-red">*</span></label>
                    <?php echo form_dropdown("type", array("complete_writeoff"=>"Completely remove this asset (Ensure enter all available quantity)", "reduce_quantity"=>"Remove certain quantity from this asset"), "", 'id="writeoff_type" class="form-data form-control"'); ?>
                    <input type="hidden" class="form-data form-control" name="assets_departments_id" value=""/>
                    <input type="hidden" class="routes_uri" name="routes_uri" value=""/>
                    <input type="hidden" class="form-control" name="departments_id" value=""/>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Write Off Quantity <span class="asterisk-red">*</span></label>
                    <input type="number" min="1" class="form-data form-control" id="quantity" name="quantity" value="" placeholder="Quantity" required/>
                </div>
                
                <div class="form-group">
                    <label for="approval_workflow">Approval Workflow (Add department's head and higher management executive) <span class="asterisk-red">*</span></label>
                    <div class="approval_workflow"></div>
                    <div>
                        <button type="button" class="btn btn-primary approval-workflow-add"><i class="fa fa-user"></i> Add Approver</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="update_remark">Remarks <span class="asterisk-red">*</span></label>
                    <textarea class="form-data form-control" name="remark" id="update_remark" placeholder="Remark"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <span class="post-message"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success post-button" data-container-id="update-popup">Send Request</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_asset_view");
    operation_queue.push("init_writeoff_request");
    operation_data = <?php echo json_encode(array("uri"=>$this->config->item("writeoff_request", "routes_uri"), "tab"=>$current_tab, "accessible_departments"=>$accessible_departments, "valid_users"=>(isset($user_permission)?$user_permission:array()))); ?>;
</script>