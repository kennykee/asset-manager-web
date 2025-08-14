<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assets Management System</title>
    <meta name="description" content="Assets Management System">
    <meta name="author" content="KennyKee Technologies">
    <meta name="version" content="Version 1.0.0 RC Updated On 15-Aug-2015">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/fancybox/jquery.fancybox.css?v=2.1.5'); ?>" type="text/css" media="screen">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/bootstrap-3.3.5-dist/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/font-awesome-4.4.0/css/font-awesome.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/bootstrap-datepicker-eternicode/css/bootstrap-datepicker3.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/dropzone.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/select2-4.0.0/css/select2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/jquery-timepicker-master/jquery.timepicker.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/common-admin-b4d2457e0b888dd512bcd538b004963c.css'); ?>">
    
	<script>
	    var base_url = "<?php echo base_url();?>";
		var site_url = "<?php echo site_url();?>";
		var token_name = "<?php echo $this->security->get_csrf_token_name();?>";
		var token_value = "<?php echo $this->security->get_csrf_hash();?>";
		var operation_queue = new Array();
		var operation_data = {}; //Store data in object literal using operation queue as key
	</script>
</head>
<body>	
<nav class="navbar navbar-inverse navbar-fixed-top navbar-top-width">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand <?php echo ($this->active_module=="dashboard")? 'white-text' : '';?>" href="<?php echo site_url($this->config->item("dashboard", "routes_uri")); ?>">Assets Management Panel</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-left">
            <?php 
            $menu = $this->cache_user["menu"];
            
            foreach($menu as $key=>$module){ ?>
                
                <li <?php echo ($this->active_module==$key)? 'class="active"' : '';?>>
                    <a href="<?php echo site_url($module["uri"]); ?>"><?php echo ucwords($key); ?></a>
                </li>
                
            <?php } ?>
        </ul>
        
        <div class="header-search-asset-container col-md-4">
            <?php 
                echo form_dropdown("top_search_dropdown", array(), NULL, 'id="top-search-dropdown" class="form-control"');
            ?>
        </div>
        
        <p class="menu-profile navbar-text pull-right">
            <span style="margin-right: 40px;">
                <a class="logout-link" href="<?php echo site_url($this->config->item("logout", "routes_uri"));?>">Logout</a>
            </span>
          Logged in as <span class="label label-success label-green"><?php echo $this->session->userdata("person_name"); ?></span>
        </p>
    </div>
  </div>
</nav>
<div class="container-fluid">
    <div class="row-fluid col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 alert-message-container">
    <?php if($this->session->flashdata('success')){ ?>
        <div class="alert alert-success alert-message-box">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-thumbs-o-up"></i>
          <strong>Success!</strong> <?php echo $this->session->flashdata('success') ?>
        </div>
    <?php } ?>
    
    <?php if($this->session->flashdata('error')){ ?>
        <div class="alert alert-danger alert-message-box">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-warning"></i>
          <strong>Error!</strong> <?php echo $this->session->flashdata('error') ?>
        </div>
    <?php } ?>
    
    <?php if($this->session->flashdata('message')){ ?>
        <div class="alert alert-info alert-message-box">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-comment"></i>
          <strong>Information:</strong> <?php echo $this->session->flashdata('message') ?>
        </div>
    <?php } ?>
    
    <?php if(validation_errors()){ ?>
        <div class="alert alert-danger alert-message-box">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-warning"></i>
          <strong>Warning!</strong> <?php echo validation_errors(); ?>
        </div>
    <?php } ?>
    </div>
</div>