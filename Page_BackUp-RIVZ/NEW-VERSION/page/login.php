<?php 
	include "../config/config.php";
?>

<div class="d-flex justify-content-center align-items-center" style="height: 100vh; background-image: linear-gradient(#035b03, white);">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="card-header text-center" style="border: transparent;">
        <div class="text-center my-2">
          <img src="image/tau.jpg" alt="logo" width="100">
        </div>
        <h1><b><!--Welcome Back!--></b></h1>
		<h3><b>Library Attendance Monitoring</b></h3>
      </div>
      <div class="card-body" style="border: transparent;">
        <p class="login-box-msg">Log in to your account</p>
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email or Username" id="lgtxtEmail" value="">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" id="lgtxtpassword" value="">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-key"></span>
            </div>
          </div>
        </div>
        <div class="row">
		  <div class="col-8">
			<div class="icheck-primary">
			  <input type="checkbox" id="showPasswordCheckbox">
			  <label for="showPasswordCheckbox">
				Show Password
			  </label>
			</div>
		  </div>
		  <!-- /.col -->
		  <div class="col-4">
			<button type="submit" class="btn btn-primary btn-block" id='btnLogin'/>Log In</button>
		  </div>
		  <!-- /.col -->
		</div>
		<!-- 
        <div class="social-auth-links text-center mt-2 mb-3">
          <a href="#" class="btn btn-block btn-primary" readonly>
            <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
          </a>
          <a href="#" class="btn btn-block btn-danger" readonly>
            <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
          </a>
        </div>
		-->
        <!-- /.social-auth-links -->

		<!-- 
        <p class="mb-1">
          <a href="#" id="clckForgotPassword">Forgot password?</a>
        </p>
        <p class="mb-0">
          <a href="register.html" class="text-center">Register a new membership</a>
        </p>
		-->
      </div>
      <!-- /.card-body -->

    </div>
    <div class="text-center mt-5 text-muted">
      <?php echo $cpy; ?>
    </div>
	<div class="text-center text-muted">
      PDO &mdash; MIS - v<?php echo $vrs; ?>
	  </div>
    <!-- /.card -->
  </div>
</div>


