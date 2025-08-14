<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("category_update", "routes_uri"), "*")){?>    
                <div class="pull-left"><button type="button" data-toggle="modal" data-target="#add-popup" class="btn btn-success"><i class="fa fa-plus"></i> Add Category</button></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th>Category Name</th>
                            <th>Asset Lifespan</th>
                            <th>Tracking Option</th>
                            <th class="col-md-3"></th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($categories as $key=>$category){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td>
                                    <?php echo $category["categories_name"]; ?>
                                </td>
                                <td><?php echo $category["lifespan_default"]? ($category["lifespan_default"] . " months ") : "No Lifespan" ; ?></td>
                                <td><?php echo $category["tracking_default"]? "Enabled" : "No Tracking"; ?></td>
                                <td>
                                    <?php if($this->matrix->checkSingleAccess($this->config->item("category_update", "routes_uri"), "*")){?>
                                    <button type="button" class="btn btn-primary btn-sm update-button" data-uri="<?php echo site_url($this->config->item("category_update", "routes_uri") . "/" . $category["id"]); ?>" data-category-name="<?php echo htmlspecialchars($category["categories_name"], ENT_QUOTES); ?>" data-lifespan="<?php echo $category["lifespan_default"]; ?>" data-tracking="<?php echo $category["tracking_default"]; ?>"><i class="fa fa-pencil"></i> Update</button>
                                    <button type="button" class="btn btn-danger btn-sm delete-button" data-uri="<?php echo site_url($this->config->item("category_delete", "routes_uri") . "/" . $category["id"]); ?>" data-category-name="<?php echo htmlspecialchars($category["categories_name"], ENT_QUOTES); ?>"><i class="fa fa-times"></i> Delete</button>
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
                <h4 class="modal-title">Add Category</h4>
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
                    <label for="new_category_name">Category Name</label>
                    <input type="text" class="form-data form-control" name="categories_name" id="new_category_name" placeholder="New category name">
                    <input type="hidden" class="routes_uri" name="routes_uri" value="<?php echo site_url($this->config->item("category_add", "routes_uri")); ?>"/>
                </div>
                <div class="form-group">
                    <label for="new_lifespan_default">Default Lifespan (months)</label>
                    <input type="text" class="form-data form-control" name="lifespan_default" id="new_lifespan_default" placeholder="Default lifespan in months. Put 0 for no lifespan.">
                </div>
                <div class="form-group">
                    <label for="new_tracking_default">Default Asset Tracking Status</label>
                    <select name="tracking_default" id="new_tracking_default" class="form-data form-control">
                        <option value="0">No Tracking</option>
                        <option value="1">Yes</option>
                    </select>
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
                <h4 class="modal-title">Update Category</h4>
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
                    <label for="update_category_name">Category Name</label>
                    <input type="text" class="form-data form-control" name="categories_name" id="update_category_name" placeholder="Update category name">
                    <input type="hidden" class="routes_uri" name="routes_uri" value=""/>
                </div>
                <div class="form-group">
                    <label for="update_lifespan_default">Default Lifespan (months)</label>
                    <input type="text" class="form-data form-control" name="lifespan_default" id="update_lifespan_default" placeholder="Default lifespan in months. Put 0 for no lifespan.">
                </div>
                <div class="form-group">
                    <label for="update_tracking_default">Default Asset Tracking Status</label>
                    <select name="tracking_default" id="update_tracking_default" class="form-data form-control">
                        <option value="0">No Tracking</option>
                        <option value="1">Yes</option>
                    </select>
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
                <h4 class="modal-title">Delete Category</h4>
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
    operation_queue.push("init_category");
</script>