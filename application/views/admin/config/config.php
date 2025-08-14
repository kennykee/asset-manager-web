<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="bs-callout">
                <h4>Using Configuration</h4>
                <p>Update system configuration for sending emails.</p>
            </div>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("config_update", "routes_uri"), "*")){?>    
                <div class="pull-left button-height"><a href="<?php echo site_url($this->config->item("config_update", "routes_uri")); ?>" class="btn btn-primary"><i class="fa fa-pencil"></i> Update Configuration</a></div>
                <?php } ?>
                <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1">No</th>
                            <th class="col-md-3 align-center">Group</th>
                            <th class="col-md-3 align-center">Configuration Name</th>
                            <th class="col-md-3 align-center">Configuration Value</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($configs as $key=>$config){ ?>
                            <tr>
                                <td><b><?php echo $key + 1; ?></b></td>
                                <td><?php echo $config["group_name"]; ?></td>
                                <td><?php echo $config["config_label"]; ?></td>
                                <td><?php echo (stripos($config["config_label"], "password") === FALSE)? $config["config_value"] : "****************"; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>