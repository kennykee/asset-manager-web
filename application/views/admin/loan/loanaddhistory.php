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
            
            <h1 class="department-header"><i class="fa fa-exchange"></i> <?php echo $current_asset_name; ?> <?php echo ($barcode? (" - " . $barcode) : ""); ?></h1>
            
            <?php if(!$current_asset_id){ ?>
                
            <div class="bs-callout">
                <h4>Select an asset</h4>
                <p>Choose an asset to loan from drop down above to begin.</p>
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
                        <a href="<?php echo site_url($this->config->item("loan_add", "routes_uri") . "/" . $current_asset_id . "?tab=" . $key); ?>">
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
            
            <h3><i class="fa fa-cubes"></i> Loan History</h3>
            
            <div class="table-responsive">
                <table class="table table-bordered table-blue">
                    <thead>
                        <tr>
                            <th class="col-md-1 align-center vertical-middle"><b>No</b></th>
                            <th class="col-md-1 align-center vertical-middle">Transaction Date</th>
                            <th class="col-md-1 align-center vertical-middle">Type</th>
                            <th class="col-md-1 align-center vertical-middle">Quantity</th>
                            <th class="col-md-1 align-center vertical-middle">Loan From</th>
                            <th class="col-md-1 align-center vertical-middle">Loan To</th>
                            <th class="col-md-1 align-center vertical-middle">Approver</th>
                            <th class="col-md-1 align-center vertical-middle">Loan Status</th>
                            <th class="col-md-1 align-center vertical-middle">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($current_data as $key=>$loan){ ?>
                            
                            <tr>
                                <td class="col-md-1 align-center"><b><?php echo $key + 1; ?></b></td>
                                <td class="align-center">
                                    <?php 
                                        switch($loan["transaction_type"]){
                                            case "loan": 
                                                    echo "<div>" . date("j F Y", strtotime($loan["loan_datetime"])) . "</div>";
                                                    echo "<div>" . date("g:i a", strtotime($loan["loan_datetime"])) . "</div>";
                                                break;
                                            case "return": 
                                                    echo "<div>" . date("j F Y", strtotime($loan["return_datetime"])) . "</div>";
                                                    echo "<div>" . date("g:i a", strtotime($loan["return_datetime"])) . "</div>";
                                                break;    
                                        }
                                    ?>
                                </td>
                                <td class="col-md-1 align-center">
                                    <?php 
                                        switch($loan["transaction_type"]){
                                            case "loan": 
                                                    echo "<b>Loan Out</b>";
                                                break;
                                            case "return": 
                                                    echo "<b>Return Asset</b>";
                                                break;    
                                        }
                                    ?>
                                </td>
                                <td class="col-md-1 align-center">
                                    <?php echo $loan["quantity"]; ?>
                                </td>
                                <td>
                                    <div><i class="fa fa-institution"></i> <?php echo $loan["origin_departments_name"]; ?></div>
                                    <div><?php echo $loan["origin_location"]; ?></div>   
                                </td>
                                <td class="col-md-1">
                                    <b>Borrower:</b>
                                    <div>
                                        <?php echo $loan["borrower_name"]; ?>
                                    </div>
                                    <div class="small-margin-top"><b>Company:</b></div>
                                    <div>
                                        <?php echo $loan["borrower_entity"]; ?>
                                    </div>
                                </td>
                                <td class="col-md-1">
                                    <b>Approver:</b>
                                    <div>
                                        <?php echo $loan["approver_name"]; ?>
                                    </div>
                                    <div class="small-margin-top"><b>Recorded By:</b></div>
                                    <div>
                                        <?php echo $loan["users"]["person_name"]; ?>
                                    </div>
                                </td>
                                <td class="col-md-1 align-center">
                                    <b>
                                    <?php 
                                        if($loan["remaining"] <= 0){
                                            echo "<span class='green-text'><i class='fa fa-check'></i> Returned</span>";    
                                        }else{
                                            echo "<span class='red-text'>" . $loan["remaining"] . " Pending Return</span>";
                                        }
                                    ?>
                                    </b>
                                    
                                    <div class="small-margin-top"><b>Expected Return: </b></div>
                                    <?php 
                                        $return_date = (intval($loan["loan_period"]) * 3600) + strtotime($loan["loan_datetime"]);
                                    ?>
                                    <div><?php echo date("j F Y", $return_date); ?></div>
                                    <div><?php echo date("g:i a", $return_date); ?></div>
                                </td>
                                <td class="col-md-1 align-center"><?php echo $loan["remark"]; ?></td>
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
    operation_data = <?php echo json_encode(array("uri"=>$this->config->item("loan_add", "routes_uri"), "tab"=>$current_tab, "accessible_departments"=>$accessible_departments)); ?>;
</script>