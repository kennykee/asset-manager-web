<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">Roles &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("roles_dropdown", $roles_dropdown, $roles_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("role_permission_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <?php if(isset($roles[$roles_id])){ 
                    $current_role = $roles[$roles_id];
                                                        ?> 
            
            <h1 class="department-header"><i class="fa fa-unlock-alt"></i> <?php echo $current_role["roles_name"]; ?> <i class="fa fa-chevron-right"></i> Permissions</h1>
            
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                <li><a href="<?php echo site_url($this->config->item("role_view", "routes_uri") . "/" . $current_role["id"]); ?>">Role Settings</a></li>
                <li class="active"><a href="<?php echo site_url($this->config->item("role_permission_view", "routes_uri") . "/" . $current_role["id"]); ?>"><b>Permission List</b></a></li>
            </ul>
            
            <?php echo form_open(site_url($this->config->item("role_permission_update", "routes_uri") . "/" . $current_role["id"])); ?>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("role_update", "routes_uri"), "*")){?>      
                <div class="pull-left button-height">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save Changes</button>
                </div>
                <?php } ?>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="align-center col-md-1">No</th>
                            <th class="align-center col-md-1">Module</th>
                            <th class="align-center col-md-7">Function</th>
                            <th class="align-center col-md-2">Apply To</th>
                            <th class="align-center col-md-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 0; 
                        foreach($functions as $function){ ?> 
                            <tr>
                                <td><b><?php echo ++$counter; ?></b></td>
                                <td class="align-center">
                                    <?php echo ucwords($function["module_name"]); ?>
                                </td>
                                <td>
                                    <b>Function: </b><?php echo $function["functions_name"]; ?>
                                    <div style="color: #08c; margin-bottom: 5px;"><?php echo $function["functions_description"]; ?></div>
                                    
                                    <?php 
                                        $roles_data = $function["roles_id"];
                                        $parameter_array = array();
                                        if(isset($roles_data[$roles_id])){
                                            $parameters_rows = $roles_data[$roles_id];
                                            foreach($parameters_rows as $parameter_row){
                                                $parameter_array[] = $parameter_row["parameter"];
                                            }
                                        }
                                    ?>
                                    <input id="function-input-<?php echo $function["id"];?>" name="parameters[<?php echo $function["id"];?>]" type="hidden" value='<?php echo json_encode($parameter_array); ?>'/>
                                </td>
                                <td>
                                    <div id="function-apply-<?php echo $function["id"];?>">
                                    <?php if(count($parameter_array) > 0){ 
                                        
                                        foreach($parameter_array as $param){
                                            
                                            if($param == "*"){
                                                echo "<div class='green-text'><i class='fa fa-check-square-o'></i> <b>All Access</b></div>";
                                            }else if(isset($departments[$param])){
                                                echo "<div class='green-text'><i class='fa fa-check-square-o'></i> <b>" . $departments[$param]["departments_name"] . "</b></div>";
                                            }else{
                                                echo "<div class='green-text'><i class='fa fa-check-square-o'></i> <b>No Department Name</b></div>";
                                            }
                                     
                                        }}else{ ?>
                                    
                                        <span class="red-text"><i class="fa fa-ban"></i> No Access</span>
                                    
                                    <?php } ?>
                                    </div>
                                </td>
                                <td><button type="button" class="btn btn-primary btn-sm change-permission-button" data-function-id="<?php echo $function["id"]; ?>" data-function-description="<?php echo form_prep($function["functions_description"]); ?>" data-function-name="<?php echo htmlspecialchars($function["functions_name"], ENT_QUOTES); ?>" data-dependencies="<?php echo $function["dependencies_type"]; ?>"><i class="fa fa-pencil"></i> Edit Permission</button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
            <?php }else{ ?>
                <div class="bs-callout bs-callout-red">
                    <h4>Record not found!</h4>
                    <p>Requested information cannot be found. Please try again.</p>
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
                <h4 class="modal-title">Edit Permission</h4>
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
                
                <div class="form-group">
                    <h2 class="no-margin-top permission-title"></h2>
                    <div class="permission-description" style="margin-bottom: 10px;"></div>
                    
                    <label class="all-access-label">
                        <input type="checkbox" class="form-data access-id all-access" name="department" value="*">
                        All Access
                    </label>
                    
                    <div class="department-access-container">
                        
                        <?php foreach($departments as $key=>$department){ ?>
                            
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="form-data access-id department-access" name="department" data-department-name="<?php echo htmlspecialchars($department["departments_name"], ENT_QUOTES); ?>" value="<?php echo $department["id"]; ?>">
                                <?php echo $department["departments_name"]; ?>
                            </label>
                        </div>
                        
                        <?php } ?>
                    
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success done-permission" data-function-id="">Done</button>
            </div>
        </div>
    </div>
</div>


<script>
    operation_queue.push("init_role_permission_view");
</script>