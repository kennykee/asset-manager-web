<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("role_update", "routes_uri"), "*")){?>    
                <div class="pull-left"><a href="<?php echo site_url($this->config->item("role_add", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-plus"></i> Add New Role</a></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th class="col-md-4"><i class="fa fa-unlock-alt"></i> Role</th>
                            <th class="col-md-2">User</th>
                            <th class="col-md-2"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($roles as $key=>$role){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td>
                                    <a href="<?php echo site_url($this->config->item("role_view", "routes_uri") . "/" . $role["id"]); ?>">
                                        <b><?php echo $role["roles_name"]; ?></b>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                        if(isset($users[$role["id"]])){
                                            foreach($users[$role["id"]] as $user){
                                                echo "<div><a href='" . site_url($this->config->item("user_view", "routes_uri")) . "/" . $user["users_id"] .  "'><i class='fa fa-user fa-fw'></i> " . $user["person_name"] . "</a></div>";   
                                            }
                                        }
                                    ?>
                                </td>
                                <td>
                                    
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("role_view", "routes_uri"), "*")){?>
                                    <a href="<?php echo site_url($this->config->item("role_view", "routes_uri") . "/" . $role["id"]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-user"></i> View Role</a>
                                    <?php } ?>
                                    
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("role_update", "routes_uri"), "*")){?>
                                    <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("role_delete", "routes_uri") . "/" . $role["id"]); ?>" data-role="<?php echo htmlspecialchars($role["roles_name"], ENT_QUOTES); ?>"><i class="fa fa-times"></i> Delete</button>
                                    <?php } ?>
                                    
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Delete Role</h4>
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
    operation_queue.push("init_role_list");
</script>