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
            
            <?php if(!$current_asset_id){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div>
            
            <?php }else{ ?>
            
            <h1 class="department-header"><i class="fa fa-cubes"></i> <?php echo $current_asset_name; ?> - <?php echo $barcode; ?></h1>
            
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                
                <?php foreach($tabs as $key=>$tab){ ?>
                    
                    <li class="<?php echo (($key==$current_tab)? "active": ""); ?>">
                        <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $current_asset_id . "?tab=" . $key); ?>">
                            <?php echo (($key==$current_tab)? ("<b>" . $tab . "</b>"): $tab); ?>
                        </a>
                    </li>
                            
                <?php } ?>
            </ul>
            
            <?php echo form_open(site_url($this->config->item("asset_update", "routes_uri") . "/" . $current_asset_id)); ?>
            
            <div class="clearfix button-height">
                <?php if($this->matrix->checkMultiAccess($this->config->item("asset_update_access", "routes_uri"), $departments)){?>    
                <div class="pull-left"><button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save Changes</a></div>
                <?php } ?>
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
                                <div class="pull-right">
                                    <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $current_asset_id . "?tab=transfer_view"); ?>">
                                        <i class="fa fa-pencil"></i> Edit In Transfer Tab
                                    </a>
                                </div>
                                <?php foreach($current_data["departments"] as $department){ ?>
                                    <div class="clearfix department-line-space">
                                        <div class="label label-primary font-14px">Department: <?php echo $department["departments_name"]; ?></div>
                                        &nbsp;<i class="fa fa-chevron-right"></i>&nbsp;
                                        <div class="label label-primary font-14px">Location: <?php echo $department["location"]; ?></div>
                                        &nbsp;<i class="fa fa-chevron-right"></i>
                                        <?php echo $department["quantity"]; ?> Unit(s)
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Enable Asset Tracking</td>
                            <td>
                                <?php if($this->matrix->checkMultiAccess($this->config->item("asset_tracking_option_access", "routes_uri"), $departments)){?>
                                        <?php echo form_dropdown("enable_tracking", array("0"=>"Disabled", "1"=>"Yes"), set_value("enable_tracking", $current_data["enable_tracking"]), 'class="form-control"'); ?>
                                <?php }else{ ?>
                                        <?php echo $current_data["enable_tracking"]? "<span class='green-text'><i class='fa fa-check'></i> Yes</span>" : "<span class='red-text'><i class='fa fa-times'></i> Disabled</span>"; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Unit Price</td>
                            <td>
                                <input type="text" class="form-control" name="assets_value" value="<?php echo set_value("assets_value", $current_data["assets_value"]); ?>" placeholder="Purchase Price. Leave blank if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Depreciation</td>
                            <td>
                                <?php if($current_data["depreciation"] !== FALSE){ ?>
                                        
                                    <div class="<?php echo $current_data["depreciation_colour"]; ?>-text">
                                        <i class="fa fa-play fa-rotate-90"></i> 
                                        <b>S$<?php echo $current_data["depreciation"]; ?> (<?php echo $current_data["depreciation_percent"]; ?>%)</b>
                                    </div>
                                
                                <?php }else{
                                    echo "-";
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Lifespan (month)</td>
                            <td>
                                <input type="text" class="form-control" name="assets_lifespan" value="<?php echo set_value("assets_lifespan", $current_data["assets_lifespan"]); ?>" placeholder="Asset lifespan in month." />
                            </td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Maintenance Interval (month)</td>
                            <td>
                                <input type="text" class="form-control" name="maintenance_interval" value="<?php echo set_value("maintenance_interval", $current_data["maintenance_interval"]); ?>" placeholder="Maintenance interval in month." />
                            </td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Next Maintenance Date</td>
                            <td>
                                <div class="pull-right">
                                    <a href="<?php echo site_url($this->config->item("asset_individual_view", "routes_uri") . "/" . $current_asset_id . "?tab=maintenance"); ?>">
                                        <i class="fa fa-pencil"></i> Edit In Maintenance Tab
                                    </a>
                                </div>
                                <?php 
                                    if(count($next_maintenance) > 0){
                                        foreach($next_maintenance as $next){ 
                                            echo "<div>" . date("j F Y", strtotime($next["maintenance_date"])) . "</div>";
                                        }        
                                    }else{
                                        echo "-";    
                                    } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>Serial Number</td>
                            <td>
                                <input type="text" class="form-control" name="serial_number" value="<?php echo set_value("serial_number", $current_data["serial_number"]); ?>" placeholder="Serial Number" />
                            </td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>Category</td>
                            <td>
                                <?php foreach($current_data["categories"] as $category){ ?>
                                    <div class="dropdown-margin-padding">
                                        <div class='col-md-10'>
                                            <?php echo form_dropdown("categories[]", $categories, $category["categories_id"], 'class="form-control"'); ?>
                                        </div> 
                                        <i class='fa fa-times fa-2x record-delete'></i>
                                    </div>
                                <?php } ?>
                                <button type="button" class="btn btn-primary add-category-dropdown">Add Category</button>
                            </td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>Supplier</td>
                            <td>
                                <input type="text" class="form-control" name="supplier_name" value="<?php echo set_value("supplier_name", $current_data["supplier_name"]); ?>" placeholder="Supplier Name" />
                            </td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>Brand</td>
                            <td>
                                <input type="text" class="form-control" name="brand" value="<?php echo set_value("brand", $current_data["brand"]); ?>" placeholder="Brand" />
                            </td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>Salvage Value</td>
                            <td>
                                <input type="text" class="form-control" name="salvage_value" value="<?php echo set_value("salvage_value", $current_data["salvage_value"]); ?>" placeholder="Salvage value. Enter 0 if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>Warranty Expiry</td>
                            <td>
                                <?php 
                                    $expiry_string = set_value("warranty_expiry", date("d-F-Y", strtotime($current_data["warranty_expiry"])));
                                    if(intval(date("Y", strtotime($expiry_string))) <= 1990){
                                        $expiry_string = "";           
                                    }
                                ?>
                                <input type="text" class="form-control datepicker" name="warranty_expiry" value="<?php echo $expiry_string; ?>" placeholder="Warranty expiry. Leave blank if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>Invoice Number</td>
                            <td>
                                <input type="text" class="form-control" name="invoice_number" value="<?php echo set_value("invoice_number", $current_data["invoice_number"]); ?>" placeholder="Invoice number. Leave blank if unavailable." />
                             </td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>Invoice Date</td>
                            <td>
                                <?php 
                                    $invoice_date = set_value("invoice_date", date("d-F-Y", strtotime($current_data["invoice_date"])));
                                    if(intval(date("Y", strtotime($invoice_date))) <= 1990){
                                        $invoice_date = "";           
                                    }
                                ?>
                                <input type="text" class="form-control datepicker" name="invoice_date" value="<?php echo $invoice_date; ?>" placeholder="Invoice date. Leave blank if unavailable." />
                            </td>
                        </tr>
                        <tr>
                            <td>19</td>
                            <td>
                                Status<br />
                                <span class="red-text">*(Use respective function to change asset status)</span>
                            </td>
                            <td style="vertical-align: middle">
                                <?php 
                                    $statuses = array("available"=>"available", "write_off"=>"Written Off", "loan_out"=>"On Loan", 
                                                      "out_of_stock"=>"Out Of Stock", "maintenance"=>"Maintenance", "unavailable"=>"Not Available");
                                    echo form_dropdown("status", $statuses, set_value("status", $current_data["status"]), 'class="form-control"');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>Remarks</td>
                            <td>
                                <textarea class="form-control" name="remarks" placeholder="Remarks."><?php echo set_value("remarks", $current_data["remarks"]); ?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
            <?php } ?>
            
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_uploader");
    operation_queue.push("init_asset_view");
    operation_queue.push("init_asset_update");
    operation_data = <?php echo json_encode(array("term"=>$this->session->userdata('search_term'), "uri"=>$this->config->item("asset_update", "routes_uri"), "tab"=>$current_tab, "categories"=>$categories)); ?>;
</script>