<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Departments &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", $departments, $current_department_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("asset_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?></h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkMinimumAccess($this->config->item("asset_update_access", "routes_uri"))){?>    
                <div class="pull-left small-margin-right small-margin-bottom"><a href="<?php echo site_url($this->config->item("asset_add", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-plus"></i> Add New Asset</a></div>
                <div class="pull-left small-margin-right small-margin-bottom"><a href="<?php echo site_url($this->config->item("asset_import_preview", "routes_uri")); ?>" class="btn btn-primary"><i class="fa fa-upload fa-fw"></i> Import New Assets From Excel</a></div>                
                <?php } ?>
                <div class="pull-left small-margin-right small-margin-bottom"><button type="button" data-toggle="modal" data-target="#download-popup" class="btn btn-primary"><i class="fa fa-download fa-fw"></i> Download Report</button></div>
                <div class="pull-left small-margin-bottom"><button type="button" data-toggle="modal" data-target="#print-popup" class="btn btn-primary"><i class="fa fa-print fa-fw"></i> Print Label</button></div>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">Asset ID</th>
                            <th class="col-md-3">Name</th>
                            <th class="col-md-1 align-center">Location</th>
                            <th class="col-md-1 align-center">Unit Price</th>
                            <th class="col-md-1 align-center">Category</th>
                            <th class="col-md-1 align-center">Supplier</th>
                            <th class="col-md-1 align-center">Invoice</th>
                            <th class="col-md-1 align-center">Status</th>
                        </tr>
                    </thead>
                    <tbody> 
                            <?php foreach($current_data as $asset){ ?>
                                <tr>
                                <td>
                                    <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $asset["assets_id"]); ?>">
                                        <b><?php echo $asset["barcode"]; ?></b>
                                    </a>
                                </td>
                                <td>
                                    <a style="display:block; float: left; margin-right:4px;" href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $asset["assets_id"]); ?>"><img class="img-thumbnail" style="height: 60px;" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $asset["attachments_id"] . "/crop/50/50"); ?>"/></a>
                                    <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $asset["assets_id"]); ?>">     
                                        <b class="vertical-top"><?php echo $asset["assets_name"]; ?></b>
                                    </a>
                                    
                                </td>
                                <td>
                                    <div class="badge blue-background"><?php echo $asset["location"]; ?></div>
                                    <div>(Qty: <?php echo $asset["quantity"]; ?>)</div>
                                </td>
                                <td>
                                    <div>S$<?php echo $asset["assets_value"]; ?></div>
                                    
                                    <?php if($asset["depreciation"] !== FALSE){ ?>
                                        
                                        <div class="<?php echo $asset["depreciation_colour"]; ?>-text">
                                            <i class="fa fa-play fa-rotate-90"></i> 
                                            <b>S$<?php echo $asset["depreciation"]; ?> (<?php echo $asset["depreciation_percent"]; ?>%)</b>
                                        </div>
                                    
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php 
                                        $category = $asset["category"];
                                        foreach($category as $cat){
                                            echo "<div><i class='fa fa-chevron-circle-right'></i> " . $cat["categories_name"] . "</div>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <div><?php echo "<i class='fa fa-industry'></i> <b>" . $asset["supplier_name"] . "</b>"; ?></div>
                                    <div><?php echo $asset["brand"]; ?></div>
                                </td>
                                <td>
                                    <div><i class="fa fa-file-text-o"></i> <?php echo $asset["invoice_number"]; ?></div>
                                    <div><i class="fa fa-calendar-check-o"></i> <?php echo date("j-M-Y", strtotime($asset["invoice_date"])); ?></div>
                                </td>
                                <td class="align-center"><b>
                                    <?php 
                                        switch ($asset["status"]) {
                                            case 'available'    : echo "<span class='green-text'><i class='fa fa-check'></i> Available</span>"; break;
                                            case 'write_off'    : echo "<span class='gray-text'><i class='fa fa-times'></i> Written Off</span>"; break;
                                            case 'loan_out'     : echo "<span class='red-text'><i class='fa fa-ban'></i> On Loan</span>"; break;
                                            case 'out_of_stock': echo "<span class='red-text'><i class='fa fa-ban'></i> Out Of Stock</span>"; break;
                                            case 'maintenance': echo "<span class='red-text'><i class='fa fa-ban'></i> Maintenance</span>"; break;
                                            case 'unavailable': echo "<span class='red-text'><i class='fa fa-ban'></i> Not Available</span>"; break;
                                        }
                                    ?></b>
                                </td>
                                </tr>
                            <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="download-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Download Asset Report</h4>
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
                    <p>1) Choose start and end date to determine asset depreciation amount within specified period. Leave blank for total depreciated value till date.</p>
                    <p>2) Asset List Summary For Department Report - List of asset for selected department(s) </p>
                    <p>3) Detailed Asset Report - Detailed asset information for selected asset(s) </p>
                    <!-- <p>4) Summary of Department Value - Summary of total value for selected department(s) </p> -->
                </div>
                
                <div class="form-group">
                    <label for="report_type">Report Type</label>
                    <?php 
                        $report_type = array();
                        $report_type["asset_list"] = "Asset List Summary For Department Report";
                        $report_type["asset_detail"] = "Detailed Asset Report";
                        //$report_type["department_value"] = "Summary of Department Value";
                        echo form_dropdown("report_type", $report_type, "", 'id="report_type" class="form-data form-control"');
                    ?>
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
                    <div class="small-margin-bottom include-department">
                        <input name="include_department" id="include_department" type="checkbox" class="form-data" value="1" /> 
                        <label for="include_department" style="font-weight:normal">Include all assets in these departments</label>
                    </div>
                    <div class="departments_list_dropdown"></div>
                    <div>
                        <button type="button" class="btn btn-primary add-location-dropdown">Add Department</button>
                        <button type="button" class="btn btn-success add-all-departments">Select All Departments</button>
                    </div>
                </div>
                
                <div class="form-group categories_dropdown">
                    <label for="categories_report_dropdown">Categories</label>
                    <div class="categories_list_dropdown"></div>
                    <div>
                        <button type="button" class="btn btn-primary add-category-dropdown">Add Category</button>
                        <button type="button" class="btn btn-success add-all-categories">Select All Categories</button>
                    </div>
                </div>
                
                <div class="form-group assets_dropdown">
                    <label for="assets_id">Assets</label>
                    <div class="assets_list_container"></div>
                    <div>
                        <?php 
                            echo form_dropdown("assets_dropdown", array("0"=>"Please Select"), "", 'id="assets-dropdown" style="width:100%" class="form-control"');
                        ?>
                    </div>
                </div>
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("asset_report", "routes_uri")); ?>"/>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary get-button" data-container-id="download-popup"><i class="fa fa-download"></i> Download</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="print-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Print Barcode Label</h4>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-success hide">
                    <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
                    <i class="fa fa-thumbs-o-up"></i>
                    <strong>Success!</strong> <span class="success-message"></span>
                </div>
                
                <div class="bs-callout">
                    <h4>Select a range of barcode to print</h4>
                    <p>1) For example, from 1 to 100 will print barcodes for assets with ID from 000001 to 000100. </p>
                    <p>2) Ensure the printer is connected to server using USB cable. </p>
                    <p>3) <i class="fa fa-exclamation-triangle"></i> Make sure the printer is named GX420t . Exact name is required for the server to identify the printer.</p>
                </div>
                
                <div class="bs-callout">
                    <h4 style="display:inline">Print Status</h4> - <span class="print-status">Ready For Printing</span>
                    <button class="btn btn-danger pull-right stop-printing-button hide" data-continue='1'><i class="fa fa-times"></i> Stop Printing</button>
                </div>
                
                <div class="alert alert-danger hide">
                    <i class="fa fa-warning"></i>
                    <strong>Error!</strong> <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="start_number">Start Number</label>
                    <input type="number" name="start_number" min="1" id="start_number" step="1" class="form-control form-data" placeholder="Start Number" />
                </div>
                
                <div class="form-group">
                    <label for="end_number">End Number</label>
                    <input type="number" name="end_number" min="1" id="end_number" step="1" class="form-control form-data" placeholder="End Number" />
                </div>
                
                <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("asset_print_label", "routes_uri")); ?>"/>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary print-button" data-container-id="print-popup"><i class="fa fa-print"></i> Print</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_asset_list");
    operation_data = <?php echo json_encode(array("departments"=>$departments, "categories"=>$categories)); ?>;
</script>