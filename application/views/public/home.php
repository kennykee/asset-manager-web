<div class="container">

  <?php echo form_open(current_url(), array('class' => 'form-signin')); ?>
      
    <h2 class="form-signin-heading">Please sign in</h2>
    
    <label for="inputEmail" class="sr-only">Username</label>
    
    <input name="username" type="text" id="inputUsername" value="<?php echo set_value('username', ""); ?>" class="form-control" placeholder="Username" required autofocus>
    
    <label for="inputPassword" class="sr-only">Password</label>
    
    <input name="pass-auth" type="password" id="inputPassword" class="form-control" placeholder="Password" required>
    
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    
  <?php echo form_close(); ?>

</div>
