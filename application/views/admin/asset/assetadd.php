<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>

            <h1 class="department-header"><i class="fa fa-cubes"></i> Add New Asset</h1>
            
            <?php echo form_open(site_url($this->config->item("asset_add", "routes_uri"))); ?>
            
            <div class="clearfix button-height">
                <div class="pull-left"><button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save New Asset</a></div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-blue table-2nd-right">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center">No</th>
                            <th class="col-md-3 align-center">Field</th>
                            <th class="align-center">Description</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <tr>
                            <td>1</td>
                            <td>Asset Photo</td>
                            <td>
                                <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . set_value("attachments_id", $current_data["attachments_id"]) . "/showall/1000/600"); ?>/photo.png">
                                    <img class="img-thumbnail" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . set_value("attachments_id", $current_data["attachments_id"]) . "/showall/500/150"); ?>"/>
                                </a>
                                <div style="margin-top:5px;"><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#upload-modal"><i class="fa fa-upload"></i> Upload Asset Photo</a></div>
                                <input class="attachments-id-input" type="hidden" name="attachments_id" value="<?php echo set_value("attachments_id", $current_data["attachments_id"]); ?>" />    
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Asset Name</td>
                            <td>
                                <input type="text" class="form-control" name="assets_name" value="<?php echo set_value("assets_name", $current_data["assets_name"]); ?>" placeholder="Asset Name" />
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Asset ID (Barcode)</td>
                            <td><?php echo $current_data["barcode"]; ?></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Location</td>
                            <td>
                                <?php
                                    $department_post = set_value("departments", $current_data["departments"]);
                                ?>
                                <?php foreach($department_post as $dep){ ?>
                                    <div class="dropdown-margin-padding">
                                        <div class='col-md-4'>
                                            <?php echo form_dropdown("departments[]", $departments, $dep, 'class="form-control"'); ?>
                                        </div> 
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="locations[]" value="<?php echo set_value("locations[]"); ?>" placeholder="Location" />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="quantity[]" value="<?php echo set_value("quantity[]"); ?>" placeholder="Quantity" />
                                        </div>   
                                        <i class='fa fa-times fa-2x record-delete'></i>
                                    </div>
                                <?php } ?>
                                
                                <button type="button" class="btn btn-primary add-location-dropdown">Add Location</button>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>
                                Enable Asset Tracking <br />
                                <span class="red-text">*(Selected category may override this option)</span>
                            </td>
                            <td style="vertical-align: middle">
                                <?php echo form_dropdown("enable_tracking", array(0=>"Disabled", 1=>"Yes"), set_value("enable_tracking", $current_data["enable_tracking"]), 'class="form-control"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Unit Price (S$)</td>
                            <td>
                                <input type="text" class="form-control" name="assets_value" value="<?php echo set_value("assets_value", $current_data["assets_value"]); ?>" placeholder="Purchase Price. Leave blank if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Lifespan (month)</td>
                            <td>
                                <input type="text" class="form-control" name="assets_lifespan" value="<?php echo set_value("assets_lifespan", $current_data["assets_lifespan"]); ?>" placeholder="Asset lifespan in month." />
                            </td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Maintenance Interval (month)</td>
                            <td>
                                <input type="text" class="form-control" name="maintenance_interval" value="<?php echo set_value("maintenance_interval", $current_data["maintenance_interval"]); ?>" placeholder="Maintenance interval in month." />
                            </td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Serial Number</td>
                            <td>
                                <input type="text" class="form-control" name="serial_number" value="<?php echo set_value("serial_number", $current_data["serial_number"]); ?>" placeholder="Serial Number" />
                            </td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Category</td>
                            <td>
                                <?php
                                    $category_post = set_value("categories", $current_data["categories"]);
                                ?>
                                <?php foreach($category_post as $category){ ?>
                                    <div class="dropdown-margin-padding">
                                        <div class='col-md-10'>
                                            <?php echo form_dropdown("categories[]", $categories, $category, 'class="form-control"'); ?>
                                        </div> 
                                        <i class='fa fa-times fa-2x record-delete'></i>
                                    </div>
                                <?php } ?>
                                <button type="button" class="btn btn-primary add-category-dropdown">Add Category</button>
                            </td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>Supplier</td>
                            <td>
                                <input type="text" class="form-control" name="supplier_name" value="<?php echo set_value("supplier_name", $current_data["supplier_name"]); ?>" placeholder="Supplier Name" />
                            </td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>Brand</td>
                            <td>
                                <input type="text" class="form-control" name="brand" value="<?php echo set_value("brand", $current_data["brand"]); ?>" placeholder="Brand" />
                            </td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>Salvage Value</td>
                            <td>
                                <input type="text" class="form-control" name="salvage_value" value="<?php echo set_value("salvage_value", $current_data["salvage_value"]); ?>" placeholder="Salvage value. Enter 0 if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>Warranty Expiry</td>
                            <td>
                                <input type="text" class="form-control datepicker" name="warranty_expiry" value="<?php echo set_value("warranty_expiry", $current_data["warranty_expiry"]); ?>" placeholder="Warranty expiry. Leave blank if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>Invoice Number</td>
                            <td>
                                <input type="text" class="form-control" name="invoice_number" value="<?php echo set_value("invoice_number", $current_data["invoice_number"]); ?>" placeholder="Invoice number. Leave blank if unavailable." />
                             </td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>Invoice Date</td>
                            <td>
                                <input type="text" class="form-control datepicker" name="invoice_date" value="<?php echo set_value("invoice_date", $current_data["invoice_date"]); ?>" placeholder="Invoice date. Leave blank if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>
                                Status
                            </td>
                            <td>
                                <?php 
                                    $statuses = array("available"=>"available", "write_off"=>"Written Off", "loan_out"=>"On Loan", 
                                                      "out_of_stock"=>"Out Of Stock", "maintenance"=>"Maintenance", "unavailable"=>"Not Available");
                                    echo form_dropdown("status", $statuses, set_value("status", $current_data["status"]), 'class="form-control"');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>Remarks</td>
                            <td>
                                <textarea class="form-control" name="remarks" placeholder="Remarks."><?php echo set_value("remarks", $current_data["remarks"]); ?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_uploader");
    operation_queue.push("init_asset_view");
    operation_queue.push("init_asset_update");
    operation_queue.push("init_asset_add");
    operation_data = <?php echo json_encode(array("categories"=>$categories, "departments"=>$departments, "categories_info"=>$categories_info)); ?>;
</script>