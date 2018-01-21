<?php include_once 'core/init.php'; ?>
<?php include_once 'includes/header.php'; ?>

<div class="container">

    <!-- Login form -->
    <div class="loginForm">
        <form  action="classes/login.php" name="loginform" class="form-login" method="post">
            <h3 class="cnt">Welcome back!</h3>
            <hr class="colorgraph">
            
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" placeholder="Username" class="input form-control" autocomplete="off" required autofocus>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="Password" class="input form-control" autocomplete="off" required>
            <a href="forgot.php">Forgot your password?</a><br><br>
            
            <!-- If there is an error it will be shown. --> 
            <?php if(!empty($_SESSION['message'])): ?>
                <div class="alert alert-danger alert-container" id="alert">
                    <strong><center><?php echo htmlentities($_SESSION['message']) ?></center></strong>
                    <?php unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            
            <input type="submit"  name="login" value="Sign In" class="btn btn-lg btn-block submit" /> 
            
        </form>

    </div>  <!-- End Login Form-->
 
<!-- URL to registration form -->
<div class="cnt"><a href="register.php">Dont have an account? Create one</a></div>

<!-- Remove this to remove the GitHub URL link -->


<!-- Back to main page -->  
<div class="cnt gray"><a href="index.html">Back to main page</a></div>  
  
</div>
<!-- End div -->