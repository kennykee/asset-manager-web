<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <h1 class="department-header"><i class="fa fa-unlock-alt"></i> New Role</h1>
            
            <?php echo form_open(site_url($this->config->item("role_add", "routes_uri"))); ?>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("role_update", "routes_uri"), "*")){?>      
                <div class="pull-left button-height">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save New Role</button>
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
                            <td><input type="text" class="form-control" name="roles_name" value="<?php echo set_value("roles_name"); ?>" placeholder="New Role Name" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
        </div>
    </div>
</div>