<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <h1 class="department-header"><i class="fa fa-user"></i> Add New User</h1>
            
            <?php echo form_open(site_url($this->config->item("user_add", "routes_uri"))); ?>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("user_update", "routes_uri"), "*")){?>      
                <div class="pull-left button-height"><button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save New User</button></div>
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
                            <td>Name</td>
                            <td><input type="text" class="form-control" name="person_name" value="<?php echo set_value("person_name"); ?>" placeholder="Person Name" /></td>
                        </tr>
                        <tr>
                            <td>Login Username</td>
                            <td><input type="text" class="form-control" placeholder="Unique Username" name="username" value="<?php echo set_value("username"); ?>" /></td>
                        </tr>
                        <tr>
                            <td>Password</td>
                            <td><input type="password" class="form-control" placeholder="Password" name="users_password" value="<?php echo set_value("users_password"); ?>" /></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><input type="email" class="form-control" placeholder="Email" name="email" value="<?php echo set_value("email"); ?>" /></td>
                        </tr>
                        <tr>
                            <td>API Key</td>
                            <td><input type="text" class="form-control" placeholder="API Key. Type in any words to generate API Key." name="api_key" value="<?php echo set_value("api_key"); ?>" /></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <?php echo form_dropdown('status', array("1" => "Active", "0" => "Disabled"), set_value("status"), 'class="form-control"'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
        </div>
    </div>
</div>