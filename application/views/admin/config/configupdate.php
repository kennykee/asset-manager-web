<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="bs-callout">
                <h4>Using Configuration</h4>
                <p>Update system configuration for sending emails.</p>
            </div>
            
            <?php echo form_open(site_url($this->config->item("config_update", "routes_uri"))); ?>
            
            <div class="clearfix">
                <?php if($this->matrix->checkSingleAccess($this->config->item("config_update", "routes_uri"), "*")){?>    
                <div class="pull-left button-height"><button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save Changes</button></div>
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
                                <td>
                                    
                                    <div class="form-group no-margin-bottom">
                                        
                                    <?php if(stripos($config["config_label"], "password") === FALSE){ ?>
                                        
                                        <input type="text" class="form-control" value="<?php echo set_value('configs[' . $config["config_key"] . ']', $config["config_value"]); ?>" name="configs[<?php echo $config["config_key"]; ?>]" placeholder="<?php echo $config["config_label"]; ?>">
                                        
                                    <?php }else{ ?>
                                        
                                        <input type="password" class="form-control" value="<?php echo set_value('configs[' . $config["config_key"] . ']', $config["config_value"]); ?>" name="configs[<?php echo $config["config_key"]; ?>]" placeholder="<?php echo $config["config_label"]; ?>">
                                        
                                    <?php } ?>
                                    
                                    </div>
                                    
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <?php echo form_close(); ?>
            
        </div>
    </div>
</div>