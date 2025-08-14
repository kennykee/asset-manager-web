<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("user_update", "routes_uri"), "*")){?>    
                <div class="pull-left"><a href="<?php echo site_url($this->config->item("user_add", "routes_uri")); ?>" class="btn btn-success"><i class="fa fa-plus"></i> Add New User</a></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th class="col-md-3"><i class="fa fa-user"></i> User</th>
                            <th class="col-md-3">Role</th>
                            <th class="col-md-1">Status</th>
                            <th class="col-md-2">Last Login</th>
                            <th class="col-md-1"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($users as $key=>$user){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td>
                                    <a href="<?php echo site_url($this->config->item("user_view", "routes_uri") . "/" . $user["id"]); ?>">
                                        <b><?php echo $user["person_name"]; ?></b><i class="fa fa-pencil fa-fw"></i>
                                    </a>
                                    <div><b>Username:</b> <?php echo $user["username"]; ?></div>
                                    <div><b>Email:</b> <?php echo $user["email"]; ?></div>
                                </td>
                                <td>
                                    <?php 
                                        if(isset($roles[$user["id"]])){
                                            foreach($roles[$user["id"]] as $role){
                                                echo "<div><a href='" . site_url($this->config->item("role_view", "routes_uri")) . "/" . $role["roles_id"] .  "'>" . $role["roles_name"] . "</a></div>";   
                                            }
                                        }
                                    ?>
                                </td>
                                <td><b><?php echo $user["status"]? "<span class='green-text'><i class='fa fa-check'></i> Active</span>" : "<span class='red-text'><i class='fa fa-user-times'></i> Disabled</span>"; ?></b></td>
                                <td>
                                    <div><i class="fa fa-calendar"></i> <?php echo date("j-M-Y, g:i a", strtotime($user["web_login_datetime"])); ?></div>
                                    <div><b>IP</b> <?php echo $user["web_login_ip"]; ?></div>
                                </td>
                                <td>
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("user_view", "routes_uri"), "*")){?>
                                    <a href="<?php echo site_url($this->config->item("user_view", "routes_uri") . "/" . $user["id"]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-user"></i> View User</a>
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