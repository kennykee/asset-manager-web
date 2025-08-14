<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Departments &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", $departments, $current_department_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control" onchange="javascript:openDepartment(\'' . $this->config->item("alert_recipient_view", "routes_uri") . '\');"');
                ?>
                </div>
            </div>
            
            <div class="bs-callout">
                <h4>Using Notification</h4>
                <p>Add email recipient to receive notification for write off request and maintenance alert for each department.</p>
            </div>
            
            <h1 class="department-header"><i class="fa fa-institution"></i> <?php echo $current_department_name; ?></h1>
            
            <div class="clearfix">
                <?php if($this->matrix->checkMinimumAccess($this->config->item("alert_recipient_update_access", "routes_uri"))){?>    
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#add-popup" class="btn btn-success"><i class="fa fa-plus"></i> Add Notification Recipient</button></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th>Email</th>
                            <th class="col-md-2"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($current_data as $key=>$email_data){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td>
                                    <?php echo $email_data["email"]; ?>
                                </td>
                                <td>
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("alert_recipient_update_access", "routes_uri"), $current_department_id)){?>
                                    <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("alert_recipient_delete", "routes_uri") . "/" . $email_data["id"]); ?>" data-email="<?php echo htmlspecialchars($email_data["email"], ENT_QUOTES); ?>"><i class="fa fa-times"></i> Delete</button>
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
                <h4 class="modal-title">Add Email Recipient</h4>
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
                    <label for="new_departments_id">Department</label>
                    <?php 
                        echo form_dropdown("departments_id", $departments, $current_department_id, 'id="new_departments_id" class="departments-top-dropdown form-data form-control"');
                    ?>
                </div>
                
                <div class="form-group">
                    <label for="new_email">Email</label>
                    <input type="email" class="form-data form-control" name="email" id="new_email" placeholder="New email">
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("alert_recipient_add", "routes_uri")); ?>"/>
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
                <h4 class="modal-title">Delete Email</h4>
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
    operation_queue.push("init_alert_recipient");
</script>