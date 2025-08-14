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
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#add-popup" class="btn btn-success"><i class="fa fa-plus"></i> Add Maintenance Date</button></div>
                <?php } ?>
            </div>
            
            <h3>Upcoming Maintenance</h3>
            
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center">No</th>
                            <th class="align-center col-md-3">Maintenance Date</th>
                            <th class="align-center col-md-3">Maintenance Interval</th>
                            <th class="align-center col-md-3">Notification Status</th>
                            <th class="col-md-2 align-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($current_data["upcoming"] as $key=>$upcoming){ ?>
                            <tr> 
                            <td><b><?php echo $key + 1; ?></b></td>
                            <td class="align-center"><?php echo date("j F Y", strtotime($upcoming["maintenance_date"])); ?></td>
                            <td class="align-center"><?php echo $assets_data["maintenance_interval"]; ?> months</td>
                            <td><?php 
                                 switch($upcoming["notification_status"]){
                                    case "0": 
                                            echo "Pending due date to notify.";
                                            break;
                                    case "1": 
                                            echo "First notification sent. Due date is less than 1 month.";
                                            break;
                                    case "2":
                                            echo "Second notification sent. Due date is less than 2 weeks."; 
                                            break;
                                 } 
                                ?></td>
                            <td class="align-center">
                                <?php if($this->matrix->checkMultiAccess($this->config->item("asset_update_access", "routes_uri"), $departments)){?>
                                <button type="button" class="btn btn-primary btn-sm update-button" data-uri="<?php echo site_url($this->config->item("asset_maintenance_update", "routes_uri") . "/" . $upcoming["id"]); ?>" data-date="<?php echo htmlspecialchars(date("d-F-Y", strtotime($upcoming["maintenance_date"])), ENT_QUOTES); ?>"><i class="fa fa-pencil"></i> Update</button>
                                <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("asset_maintenance_delete", "routes_uri") . "/" . $upcoming["id"]); ?>" data-date="<?php echo htmlspecialchars(date("d-F-Y", strtotime($upcoming["maintenance_date"])), ENT_QUOTES); ?>"><i class="fa fa-times"></i> Delete</button>
                                <?php } ?>
                            </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            
            <h3>Historical Maintenance</h3>
            
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center">No</th>
                            <th class="col-md-3 align-center">Maintenance Date</th>
                            <th class="col-md-3 align-center">Maintenance Interval</th>
                            <th class="col-md-3 align-center">Notification Status</th>
                            <th class="col-md-2 align-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($current_data["history"] as $key=>$history){ ?>
                            <tr>
                            <td><b><?php echo $key + 1; ?></b></td>
                            <td class="align-center"><?php echo date("j F Y", strtotime($history["maintenance_date"])); ?></td>
                            <td class="align-center"><?php echo $assets_data["maintenance_interval"]; ?> months</td>
                            <td><?php 
                                 switch($history["notification_status"]){
                                    case "0": 
                                            echo "No notification sent";
                                            break;
                                    case "1": 
                                            echo "First notification sent. Due date is less than 1 month.";
                                            break;
                                    case "2":
                                            echo "Second notification sent. Due date is less than 2 weeks."; 
                                            break;
                                 } 
                                ?></td>
                            <td class="align-center">
                                <?php if($this->matrix->checkMultiAccess($this->config->item("asset_update_access", "routes_uri"), $departments)){?>
                                <button type="button" class="btn btn-primary btn-sm update-button" data-uri="<?php echo site_url($this->config->item("asset_maintenance_update", "routes_uri") . "/" . $history["id"]); ?>" data-date="<?php echo htmlspecialchars(date("d-F-Y", strtotime($history["maintenance_date"])), ENT_QUOTES); ?>"><i class="fa fa-pencil"></i> Update</button>
                                <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("asset_maintenance_delete", "routes_uri") . "/" . $history["id"]); ?>" data-date="<?php echo htmlspecialchars(date("d-F-Y", strtotime($history["maintenance_date"])), ENT_QUOTES); ?>"><i class="fa fa-times"></i> Delete</button>
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

<div class="modal fade" id="add-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Add Maintenance Date</h4>
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
                    <strong>Error!</strong> <br /><span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="new_maintenance">Maintenance Date</label>
                    <input type="text" class="form-data form-control datepicker" name="maintenance_date" id="new_maintenance" placeholder="Maintenance date">
                    <input type="hidden" class="form-data" name="assets_id" value="<?php echo $current_asset_id; ?>">
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("asset_maintenance_add", "routes_uri")); ?>"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success post-button" data-container-id="add-popup">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="update-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Update Maintenance</h4>
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
                    <strong>Error!</strong> <br /><span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="update_maintenance">Maintenance Date</label>
                    <input type="text" class="form-data form-control datepicker" name="maintenance_date" id="update_maintenance" placeholder="Update maintenance date">
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

<div class="modal fade" id="delete-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Delete Maintenance Date</h4>
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
                    <strong>Error!</strong> <br /><span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <input type="hidden" class="routes_uri" name="routes_uri" value=""/>
                    <span class="delete-message"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger post-button" data-container-id="delete-popup">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_asset_view");
    operation_queue.push("init_maintenance_view");
    operation_data = <?php echo json_encode(array("term"=>$this->session->userdata('search_term'), "uri"=>$this->config->item("asset_individual_view", "routes_uri"), "tab"=>$current_tab)); ?>;
</script>