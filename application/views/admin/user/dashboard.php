<div class="container-fluid">
	<div class="row">
		<div class="col-md-12 main">
			<h1 class="page-header">Dashboard</h1>
			<div class="bs-callout">
                <h4>Welcome, <?php echo $this->session->userdata("person_name"); ?></h4>
                <p><i class='fa fa-key fa-fw'></i> <a href="#" data-toggle="modal" data-target="#password-popup">Change Password</a></p>
                <p><i class='fa fa-lock fa-fw'></i> API Key: <?php echo $api_key; ?> (<a href="#" data-toggle="modal" data-target="#api-popup">Reset</a>)</p>
            </div>
		</div>
	</div>
</div>

<div class="modal fade" id="password-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Change Password</h4>
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
                    <label for="existing_password">Existing Password</label>
                    <input type="password" class="form-data form-control" name="existing_password" id="existing_password" placeholder="Existing Password" />
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("dashboard_change_password", "routes_uri")); ?>"/>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-data form-control" name="new_password" id="new_password" placeholder="New Password" />
                </div>
                <div class="form-group">
                    <label for="repeat_password">Repeat New Password</label>
                    <input type="password" class="form-data form-control" name="repeat_password" id="repeat_password" placeholder="Repeat New Password" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary post-button" data-container-id="password-popup">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="api-popup">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title">Reset API</h4>
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
                    <label>Are you sure to reset API key? You will need to relogin mobile application.</label>
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("dashboard_regenerate_api", "routes_uri")); ?>"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary post-button" data-container-id="api-popup">Reset</button>
            </div>
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_dashboard");
</script>