<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">Roles &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("roles_dropdown", $roles_dropdown, $roles_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("role_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <?php if(isset($roles[$roles_id])){ 
                    $current_role = $roles[$roles_id];
                                                        ?> 
            
            <h1 class="department-header"><i class="fa fa-unlock-alt"></i> <?php echo $current_role["roles_name"]; ?></h1>
            
            <?php echo form_open(site_url($this->config->item("role_update", "routes_uri") . "/" . $roles_id)); ?>
            
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
                            <th class="col-md-3 align-center">Field</th>
                            <th class="align-center">Description</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <tr>
                            <td>Role Name</td>
                            <td><input type="text" class="form-control" name="roles_name" value="<?php echo set_value("roles_name", $current_role["roles_name"]); ?>" placeholder="Role Name" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
            <h1 class="department-header"><i class="fa fa-user"></i> <?php echo $current_role["roles_name"]; ?> <i class="fa fa-chevron-right"></i> Users </h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("role_update", "routes_uri"), "*")){?>    
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#add-popup" class="btn btn-success"><i class="fa fa-plus"></i> Assign User</button></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th class="col-md-3"><i class="fa fa-user"></i> User</th>
                            <th class="col-md-2">Status</th>
                            <th class="col-md-2">Last Login</th>
                            <th class="col-md-1"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($current_users as $key=>$current_user){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td>
                                    <a href="<?php echo site_url($this->config->item("user_view", "routes_uri") . "/" . $current_user["users_id"]); ?>">
                                        <b><?php echo $current_user["person_name"]; ?></b><i class="fa fa-pencil fa-fw"></i>
                                    </a>
                                    <div><b>Username:</b> <?php echo $current_user["username"]; ?></div>
                                    <div><b>Email:</b> <?php echo $current_user["email"]; ?></div>
                                </td>
                                <td><b><?php echo $current_user["status"]? "<span class='green-text'><i class='fa fa-check'></i> Active</span>" : "<span class='red-text'><i class='fa fa-user-times'></i> Disabled</span>"; ?></b></td>
                                <td>
                                    <div><i class="fa fa-calendar"></i> <?php echo date("j-M-Y, g:i a", strtotime($current_user["web_login_datetime"])); ?></div>
                                    <div><b>IP</b> <?php echo $current_user["web_login_ip"]; ?></div>
                                </td>
                                <td>
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("role_update", "routes_uri"), "*")){?>    
                                    <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("role_user_remove", "routes_uri") . "/" . $current_user["users_roles_id"]); ?>" data-user="<?php echo htmlspecialchars($current_user["person_name"], ENT_QUOTES); ?>"><i class="fa fa-times"></i> Unassign User</button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
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
                <h4 class="modal-title">Assign User</h4>
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
                    <h2 class="no-margin-top">User List</h2>
                    
                    <?php foreach($users as $key=>$user){ ?>
                        
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="form-data" name="users_id[]" <?php echo (in_array($roles_id, $user["roles_id"])? 'checked="checked"':''); ?> value="<?php echo $user["id"]; ?>">
                            <?php echo $user["person_name"]; ?>
                        </label>
                    </div>
                    
                    <?php } ?>
                    
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("role_user_assign", "routes_uri") . "/" . $roles_id); ?>"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success post-button" data-container-id="add-popup">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Unassign User</h4>
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
    operation_queue.push("init_role_view");
</script>