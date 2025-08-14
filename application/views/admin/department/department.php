<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("department_update", "routes_uri"), "*")){?>    
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#add-popup" class="btn btn-success"><i class="fa fa-plus"></i> Add Department</button></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th>Department Name</th>
                            <th class="col-md-3"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($departments as $key=>$department){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td>
                                    <a href="<?php echo site_url($this->config->item("asset_view", "routes_uri") . "/" . $department["id"]); ?>">
                                        <?php echo $department["departments_name"]; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("department_update", "routes_uri"), "*")){?>
                                    <button type="button" class="btn btn-primary btn-sm update-button" data-uri="<?php echo site_url($this->config->item("department_update", "routes_uri") . "/" . $department["id"]); ?>" data-department-name="<?php echo htmlspecialchars($department["departments_name"], ENT_QUOTES); ?>"><i class="fa fa-pencil"></i> Update</button>
                                    <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("department_delete", "routes_uri") . "/" . $department["id"]); ?>" data-department-name="<?php echo htmlspecialchars($department["departments_name"], ENT_QUOTES); ?>"><i class="fa fa-times"></i> Delete</button>
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


<div class="modal fade" id="add-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Add Department</h4>
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
                    <label for="new_department_name">Department Name</label>
                    <input type="text" class="form-data form-control" name="departments_name" id="new_department_name" placeholder="New department name">
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("department_add", "routes_uri")); ?>"/>
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
                <h4 class="modal-title">Update Department Name</h4>
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
                    <label for="update_department_name">Department Name</label>
                    <input type="text" class="form-data form-control" name="departments_name" id="update_department_name" placeholder="Update department name">
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
                <h4 class="modal-title">Delete Department</h4>
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
    operation_queue.push("init_department");
</script>