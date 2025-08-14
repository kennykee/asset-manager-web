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
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?></h1>
            
            <div>
                <?php if($this->matrix->checkMinimumAccess($this->config->item("loan_update_access", "routes_uri"))){?>
                <div class="pull-right">
                    <a href="<?php echo site_url($this->config->item("loan_add", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-plus"></i> Make a New Loan</a>
               </div>
                <?php } ?>
                <div class="pull-right small-margin-right"><button type="button" data-toggle="modal" data-target="#download-popup" class="btn btn-primary"><i class="fa fa-download fa-fw"></i> Download Loan History Report</button></div>
                <div class="pull-right small-margin-right"><button type="button" data-toggle="modal" data-target="#generate-form-popup" class="btn btn-primary"><i class="fa fa-file-excel-o fa-fw"></i> Generate Loan Form</button></div>
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
                            <th class="col-md-2 align-center vertical-middle">Loan Date</th>
                            <th class="col-md-2 align-center vertical-middle">Asset</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity Loaned</th>
                            <th class="col-md-1 align-center vertical-middle">Expected Return</th>
                            <th class="col-md-1 align-center vertical-middle">Loan To</th>
                            <th class="col-md-1 align-center vertical-middle">Approver</th>
                            <th class="col-md-1 align-center vertical-middle">Remarks</th>
                            <th class="col-md-1 align-center vertical-middle"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($current_data as $loan){ ?>
                            <?php 
                                /* Form URL */
                                $loan_parameters = array();
                                $loan_parameters["form_date"] = date("d-M-Y");
                                $loan_parameters["form_department"] = $current_department_id;
                                $loan_parameters["form_request_by"] = $loan["borrower_entity"]; 
                                $loan_parameters["form_purpose"] = $loan["remark"];
                                $loan_parameters["loaned_assets"][] = $loan["assets_departments_loan_id"];
                                $loan_parameters["form_date_loan"] = date("d-M-Y",strtotime($loan["datetime_created"]));
                                $loan_parameters["form_issued_by"] = $loan["users"]["person_name"];
                                $loan_parameters["form_borrower_name"] = $loan["borrower_name"];
                                $loan_parameters["form_approver_name"] = $loan["approver_name"];
                                $loan_parameters["form_date_return"] = "";
                                $loan_parameters["form_return_by"] = "";
                                $loan_parameters["form_receiving_officer"] = ""; 
                                $loan_form_url = site_url($this->config->item("loan_form", "routes_uri")) . "/?" . http_build_query($loan_parameters);
                                
                            ?>
                            <tr>
                                <td class="align-center">
                                    <div><?php echo date("j F Y", strtotime($loan["datetime_created"])); ?></div>
                                    <div><?php echo date("g:i a", strtotime($loan["datetime_created"])); ?></div>
                                </td>
                                <td>
                                        <div style="margin-bottom: 3px;">
                                            <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $loan["assets_id"]); ?>">     
                                                <b class="vertical-top"><?php echo $loan["assets"]["assets_name"]; ?></b>
                                            </a>
                                        </div>
                                        <div style="margin-bottom: 3px;">
                                            <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $loan["assets"]["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                                <img class="img-thumbnail" style="vertical-align: top" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $loan["assets"]["attachments_id"] . "/crop/40/40"); ?>"/></a>
                                                
                                            <div style="display: inline-block">
                                                <div>
                                                    <b>ID:</b> <?php echo $loan["assets"]["barcode"]; ?>
                                                </div>
                                                <div>
                                                    <b>
                                                    <?php 
                                                        switch ($loan["assets"]["status"]) {
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
                                        <div><b>From: </b><?php echo $loan["location"]; ?></div>
                                    </td>
                                    <td class="align-center">
                                        <?php echo $loan["loaned_quantity"]; ?>
                                    </td>
                                    <td class="align-center">
                                        <?php 
                                            $return_date = (intval($loan["loan_period"]) * 3600) + strtotime($loan["datetime_created"]);
                                        ?>
                                        <div><?php echo date("j F Y", $return_date); ?></div>
                                        <div><?php echo date("g:i a", $return_date); ?></div>
                                    </td>
                                    <td>
                                        <b>Borrower:</b>
                                        <div>
                                            <?php echo $loan["borrower_name"]; ?>
                                        </div>
                                        <div class="small-margin-top"><b>Company:</b></div>
                                        <div>
                                            <?php echo $loan["borrower_entity"]; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <b>Approver:</b>
                                        <div>
                                            <?php echo $loan["approver_name"]; ?>
                                        </div>
                                        <div class="small-margin-top"><b>Recorded By:</b></div>
                                        <div>
                                            <?php echo $loan["users"]["person_name"]; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $loan["remark"]; ?></td>
                                    <td class="col-md-1 align-center">
                                        <?php if($this->matrix->checkSingleAccess($this->config->item("loan_update_access", "routes_uri"), $current_department_id)){?>
                                        <button type="button" class="btn btn-primary btn-sm update-button small-margin-bottom" 
                                                data-quantity="<?php echo $loan["loaned_quantity"]; ?>" 
                                                data-asset-name="<?php echo $loan["assets"]["assets_name"]; ?>"
                                                data-remark="<?php echo htmlspecialchars($loan["remark"], ENT_QUOTES); ?>" 
                                                data-uri="<?php echo site_url($this->config->item("loan_return", "routes_uri") . "/" . $loan["assets_departments_loan_id"]); ?>"><i class="fa fa-home fa-fw"></i> Return Asset</button>
                                        <?php } ?>
                                        <a target="_blank" href="<?php echo $loan_form_url; ?>" class="btn btn-primary btn-sm"><i class="fa fa-download fa-fw"></i> Download Loan Form</a>
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
                <h4 class="modal-title">Return Asset</h4>
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
                    <p>1) Once asset is returned, this record will be permanent. Check carefully before proceeding.</p>
                    <p>2) Return quantity cannot be more than loaned quantity. You can choose to return partial quantity instead of full quantity.</p>
                    <p class="update-message"></p>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Return Quantity</label>
                    <input type="number" min="1" class="form-data form-control" id="quantity" name="quantity" value="" placeholder="Quantity" required/>
                    <input type="hidden" class="routes_uri" name="routes_uri" value=""/>
                </div>
                
                <div class="form-group">
                    <label for="update_remark">Remarks</label>
                    <textarea class="form-data form-control" name="remark" id="update_remark" placeholder="Remark"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success post-button" data-container-id="update-popup"><i class="fa fa-check"></i> Return</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="download-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Download Loan Report</h4>
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
                    <p>1) Select start date and end date for loan transaction.</p>
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
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("loan_report", "routes_uri")); ?>"/>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary get-button" data-container-id="download-popup"><i class="fa fa-download"></i> Download</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="generate-form-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Generate Loan Form</h4>
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
                    <h4>Fill in the fields to generate loan form</h4>
                    <p>Add loaned asset to automatically fill fields.</p>
                </div>
                
                <div class="form-group">
                    <label for="form_department">Loaned Assets</label>
                    <div class="loaned_list"></div>
                    <div>
                        <button type="button" class="btn btn-primary add-loaned-asset-dropdown">Add Loaned Asset</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="form_date">Form Date</label>
                    <input type="text" name="form_date" id="form_date" class="form-control form-data datepicker" placeholder="Date" />
                </div>
                
                <div class="form-group">
                    <label for="form_department">Department</label>
                    <?php echo form_dropdown("form_department", $departments, "", 'class="form-data form-control"'); ?>
                </div>
                
                <div class="form-group">
                    <label for="form_request_by">Requested By</label>
                    <input type="text" name="form_request_by" id="form_request_by" class="form-control form-data" placeholder="Requested By" />
                </div>
                
                <div class="form-group">
                    <label for="form_purpose">Purpose</label>
                    <input type="text" name="form_purpose" id="form_purpose" class="form-control form-data" placeholder="Purpose" />
                </div>
                
                <div class="form-group">
                    <label for="form_date_loan">Date Loan</label>
                    <input type="text" name="form_date_loan" id="form_date_loan" class="form-control form-data datepicker" placeholder="Date Loan" />
                </div>
                
                <div class="form-group">
                    <label for="form_issued_by">Issued By</label>
                    <input type="text" name="form_issued_by" value="" id="form_issued_by" class="form-control form-data" placeholder="Issued By" />
                </div>
                
                <div class="form-group">
                    <label for="form_approver_name">Approver Name</label>
                    <input type="text" name="form_approver_name" value="" id="form_approver_name" class="form-control form-data" placeholder="Approver Name" />
                </div>
                
                <div class="form-group">
                    <label for="form_borrower_name">Borrower Name</label>
                    <input type="text" name="form_borrower_name" id="form_borrower_name" class="form-control form-data" placeholder="Borrower Name" />
                </div>
                
                <div class="form-group">
                    <label for="form_date_return">Date Return</label>
                    <input type="text" name="form_date_return" id="form_date_return" class="form-control form-data datepicker" placeholder="Date Return" />
                </div>
                
                <div class="form-group">
                    <label for="form_return_by">Returned By</label>
                    <input type="text" name="form_return_by" value="" id="form_return_by" class="form-control form-data" placeholder="Returned By" />
                </div>
                
                <div class="form-group">
                    <label for="form_receiving_officer">Receiving Officer</label>
                    <input type="text" name="form_receiving_officer" id="form_receiving_officer" class="form-control form-data" placeholder="Receiving Officer" />
                </div>
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("loan_form", "routes_uri")); ?>"/>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary get-button" data-container-id="generate-form-popup"><i class="fa fa-download"></i> Generate</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_data = <?php echo json_encode(array("departments"=>$departments, "loan_data"=>$loan_data)); ?>;
    operation_queue.push("init_loan_view");
</script>