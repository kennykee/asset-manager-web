<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assets Management Login</title>
    <meta name="description" content="Assets Management System">
    <meta name="author" content="KennyKee Technologies">
    <meta name="version" content="Version 1.0.0 RC Updated On 15-Aug-2015">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/common-public.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/bootstrap-3.3.5-dist/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/lib/font-awesome-4.3.0/css/font-awesome.min.css'); ?>">
    
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
<div class="container-fluid">
    <div class="row-fluid">
    <?php if($this->session->flashdata('success')){ ?>
        <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-thumbs-o-up"></i>
          <strong>Success!</strong> <?php echo $this->session->flashdata('success') ?>
        </div>
    <?php } ?>
    
    <?php if($this->session->flashdata('error')){ ?>
        <div class="alert alert-danger">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-warning"></i>
          <strong>Error!</strong> <?php echo $this->session->flashdata('error') ?>
        </div>
    <?php } ?>
    
    <?php if($this->session->flashdata('message')){ ?>
        <div class="alert alert-info">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-comment"></i>
          <strong>Information:</strong> <?php echo $this->session->flashdata('message') ?>
        </div>
    <?php } ?>
    
    <?php if(validation_errors()){ ?>
        <div class="alert alert-danger">
          <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
          <i class="fa fa-warning"></i>
          <strong>Warning!</strong> <?php echo validation_errors(); ?>
        </div>
    <?php } ?>
    </div>
</div>