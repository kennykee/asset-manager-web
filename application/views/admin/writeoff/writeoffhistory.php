<div class="container-fluid">
    <div class="row">
        <?php echo $this->sub_menu_string; ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header"><?php echo $this->active_function_label; ?></h1>
            
            <div class="manage-departments clearfix">
                <div class="departments-select-label col-md-2">My Assets &raquo;</div>
                <div class="departments-top-dropdown-container">
                <?php 
                    echo form_dropdown("departments_dropdown", array($current_asset_id => $current_asset_name . " - " . $barcode), $current_asset_id, 'id="departments-top-dropdown" class="departments-top-dropdown form-control"');
                ?>
                </div>
            </div>
            
            <h1 class="department-header"><i class="fa fa-eraser"></i> <?php echo $current_asset_name; ?> <?php echo ($barcode? (" - " . $barcode) : ""); ?></h1>
            
            <?php if(!$current_asset_id){ ?>
                
            <div class="bs-callout">
                <h4>Select an asset</h4>
                <p>Choose an asset to write off from drop down above to begin.</p>
            </div>
            
            <?php }else if(!$barcode){ ?>
                
            <div class="bs-callout bs-callout-red">
                <h4>Record not found!</h4>
                <p>Requested information cannot be found or you have no permission to access. Please try again.</p>
            </div> 
                
            <?php }else{ ?>
            
            <ul class="nav nav-tabs" style="margin-bottom:15px;">
                
                <?php foreach($tabs as $key=>$tab){ ?>
                    
                    <li class="<?php echo (($key==$current_tab)? "active": ""); ?>">
                        <a href="<?php echo site_url($this->config->item("writeoff_request", "routes_uri") . "/" . $current_asset_id . "?tab=" . $key); ?>">
                            <?php echo (($key==$current_tab)? ("<b>" . $tab . "</b>"): $tab); ?>
                        </a>
                    </li>
                            
                <?php } ?>
            </ul>
            
            <div>
                <a class="photo" href="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $current_asset["attachments_id"] . "/showall/1000/600"); ?>/photo.png">
                    <img class="img-thumbnail" src="<?php echo site_url($this->config->item("photo", "routes_uri") . "/" . $current_asset["attachments_id"] . "/showall/500/150"); ?>"/>
                </a>
            </div>
            
            <div class="pull-right"><?php echo $this->pagination_output->getPageHTML(); ?></div>
            
            <h3><i class="fa fa-cubes"></i> Write Off History</h3>
            
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center vertical-middle">Status</th>
                            <th class="col-md-2 align-center vertical-middle">Write Off Date</th>
                            <th class="col-md-1 align-center vertical-middle">Type</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity</th>
                            <th class="col-md-1 align-center vertical-middle">Location</th>
                            <th class="col-md-2 align-center vertical-middle">User</th>
                            <th class="col-md-1 align-center vertical-middle">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($current_data as $writeoff){ ?>
                                <tr>
                                    <td class="align-center">
                                        <div>
                                            <b>
                                            <?php 
                                                switch ($writeoff["status"]) {
                                                    case "0"    : echo "<span class='red-text'><i class='fa fa-exclamation-triangle'></i> Pending Approval</span>"; break;
                                                    case "1"    : echo "<span class='green-text'><i class='fa fa-check'></i> Approved</span>"; break;
                                                    case "-1"   : echo "<span class='gray-text'><i class='fa fa-ban'></i> Rejected</span>"; break;
                                                }
                                            ?>
                                            </b>
                                        </div>
                                    </td>
                                    <td class="align-center">
                                        <div>
                                            <b>Request Date: </b>
                                            <div><?php echo date("j F Y", strtotime($writeoff["datetime_created"])); ?></div>
                                            <div><?php echo date("g:i a", strtotime($writeoff["datetime_created"])); ?></div>
                                        </div>
                                        
                                        <div class="small-margin-top">
                                            <b><?php echo (($writeoff["status"]=="-1")? "Rejected":"Approved"); ?> Date: </b>
                                            <?php if($writeoff["datetime_approved"]){ ?>
                                                <div><?php echo date("j F Y", strtotime($writeoff["datetime_approved"])); ?></div>
                                                <div><?php echo date("g:i a", strtotime($writeoff["datetime_approved"])); ?></div>
                                            <?php }else{ ?>
                                                <div>-</div>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td class="align-center">
                                        <?php
                                            switch($writeoff["writeoff_type"]){
                                                case "reduce_quantity":
                                                    echo "Reduce quantity"; 
                                                    break;
                                                case "complete_writeoff":
                                                    echo "Complete removal"; 
                                                    break;  
                                            } 
                                        ?>
                                    </td>
                                    <td class="align-center">
                                        <?php echo $writeoff["quantity"]; ?>
                                    </td>
                                    <td class="align-center">
                                        <div><i class="fa fa-institution"></i> <?php echo $writeoff["origin_departments_name"]; ?></div>
                                        <div><?php echo $writeoff["origin_location"];?></div>
                                    </td>
                                    <td class="">
                                        <div>
                                            <h4 class="no-margin-top">Requester:</h4>
                                            <div>
                                                <a href="mailto:<?php echo $writeoff["users_requester"]["email"]; ?>">
                                                    <i class="fa fa-user"></i> <?php echo $writeoff["users_requester"]["person_name"]; ?>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="small-margin-top"><h4>Approvers: </h4></div>
                                        
                                        <div style="">
                                        
                                        <?php foreach($writeoff["users_approver"] as $key=>$approver){ ?>
                                            
                                            <div class="small-margin-top small-margin-left small-margin-bottom" style="padding: 5px;background-color: #FFFF33">    
                                                <b>
                                                    <?php 
                                                        switch ($approver["status"]){
                                                            case "0": 
                                                                    echo "<i class='fa fa-exclamation-triangle'></i> Pending: ";
                                                                    break;
                                                            case "1":
                                                                    echo "<i class='fa fa-check'></i> Approved: "; 
                                                                    break;
                                                            case "-1":
                                                                    echo "<i class='fa fa-times'></i> Rejected: "; 
                                                                    break;
                                                        }
                                                    ?>
                                                </b>
                                                <div class="small-margin-left">
                                                    <a href="mailto:<?php echo $approver["email"]; ?>">
                                                        <i class="fa fa-user"></i> <?php echo $approver["person_name"]; ?>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                                <?php 
                                                    if((count($approver) > 1) && ($key < (count($writeoff["users_approver"]) - 1))){
                                                        echo "<b class='red-text'><i class='fa fa-chevron-down'></i> Escalate To</b>";
                                                    }
                                                ?>
                                            
                                        <?php } ?>
                                        
                                        </div>
                                        
                                    </td>
                                    <td class=""><?php echo $writeoff["remark"] ?></td>
                                </tr>
                            <?php } ?>       
                    </tbody>
                </table>
            </div>
            
            <?php } ?>
            
        </div>
    </div>
</div>

<script>
    operation_queue.push("init_asset_view");
    operation_data = <?php echo json_encode(array("uri"=>$this->config->item("writeoff_request", "routes_uri"), "tab"=>$current_tab, "accessible_departments"=>$accessible_departments)); ?>;
</script>