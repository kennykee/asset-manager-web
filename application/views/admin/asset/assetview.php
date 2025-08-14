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
            
            
            <div class="clearfix button-height">
                <?php if($this->matrix->checkMultiAccess($this->config->item("asset_update_access", "routes_uri"), $departments)){?>    
                <div class="pull-left"><a href="<?php echo site_url($this->config->item("asset_update", "routes_uri") . "/" . $current_asset_id); ?>" class="btn btn-primary"><i class="fa fa-pencil"></i> Update Asset</a></div>
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
                                <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $current_data["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                                    <img class="img-thumbnail" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $current_data["attachments_id"] . "/showall/500/150"); ?>"/>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Asset Name</td>
                            <td><?php echo $current_data["assets_name"]; ?></td>
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
                            <td><?php echo $current_data["enable_tracking"]? "<span class='green-text'><i class='fa fa-check'></i> Yes</span>" : "<span class='red-text'><i class='fa fa-times'></i> Disabled</span>"; ?></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Unit Price</td>
                            <td>S$<?php echo $current_data["assets_value"]; ?></td>
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
                            <td>Lifespan</td>
                            <td><?php echo $current_data["assets_lifespan"]; ?> Months</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Maintenance Interval</td>
                            <td><?php echo $current_data["maintenance_interval"]? $current_data["maintenance_interval"] . " Months" : "No Maintenance"; ?> </td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Next Maintenance Date</td>
                            <td>
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
                            <td><?php echo $current_data["serial_number"]; ?></td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>Category</td>
                            <td>
                                <?php foreach($current_data["categories"] as $category){
                                    echo "<div><i class='fa fa-chevron-circle-right'></i> " . $category["categories_name"] . "</div>";
                                }?>
                            </td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>Supplier</td>
                            <td><?php echo $current_data["supplier_name"]? ("<i class='fa fa-industry'></i> " . $current_data["supplier_name"]) : ""; ?></td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>Brand</td>
                            <td><?php echo $current_data["brand"]; ?></td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>Salvage Value</td>
                            <td><?php echo floatval($current_data["salvage_value"])? "$" . $current_data["salvage_value"] : "No Salvage Value"; ?></td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>Warranty Expiry</td>
                            <td>
                                <?php if(intval(date("Y", strtotime($current_data["warranty_expiry"]))) > 1990){ ?>
                                    <i class='fa fa-calendar'></i> <?php echo date("j F Y", strtotime($current_data["warranty_expiry"])); ?>
                                <?php }else{ ?>
                                    No Warranty
                                <?php } ?>    
                            </td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>Invoice Number</td>
                            <td><?php echo $current_data["invoice_number"]; ?></td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>Invoice Date</td>
                            <td>
                                <?php if(intval(date("Y", strtotime($current_data["invoice_date"]))) > 1990){ ?>
                                    <i class='fa fa-calendar'></i> <?php echo date("j F Y", strtotime($current_data["invoice_date"])); ?>
                                <?php }else{ ?>
                                    No Invoice Date
                                <?php } ?>    
                            </td>
                        </tr>
                        <tr>
                            <td>19</td>
                            <td>Status</td>
                            <td>
                                <b>
                                    <?php 
                                        switch ($current_data["status"]) {
                                            case 'available'    : echo "<span class='green-text'><i class='fa fa-check'></i> Available</span>"; break;
                                            case 'write_off'    : echo "<span class='gray-text'><i class='fa fa-times'></i> Written Off</span>"; break;
                                            case 'loan_out'     : echo "<span class='red-text'><i class='fa fa-ban'></i> On Loan</span>"; break;
                                            case 'out_of_stock': echo "<span class='red-text'><i class='fa fa-ban'></i> Out Of Stock</span>"; break;
                                            case 'maintenance': echo "<span class='red-text'><i class='fa fa-ban'></i> Maintenance</span>"; break;
                                            case 'unavailable': echo "<span class='red-text'><i class='fa fa-ban'></i> Not Available</span>"; break;
                                        }
                                    ?>
                                 </b>
                            </td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>Remarks</td>
                            <td><?php echo $current_data["remarks"]; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php } ?>
            
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_asset_view");
    operation_data = <?php echo json_encode(array("term"=>$this->session->userdata('search_term'), "uri"=>$this->config->item("asset_individual_view", "routes_uri"), "tab"=>$current_tab)); ?>;
</script>