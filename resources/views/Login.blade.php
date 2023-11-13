@extends('partials/master')
@section('content')

<body>
	<section class="login">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-5 col-sm-12 form_col d-flex flex-column justify-content-between">
				<div class="cont">
				<h2>Welcome back to Networked</h2>
			<h6>Log In to your account</h6>
			</div>
			<form action="" class="login_form" method="POST">
        <div>
            <label for="username_email">Email address</label>
            <input type="email" id="username_email" name="username_email" placeholder="Enter your email" required>
        </div>
        <div class="pass">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <span class="forg_pass">
            	 <a href="#" class="" data-toggle="modal" data-target="#basicModal">Forgot password?</a>
            	<!-- <a href="#">Forgot password?</a> -->
            
            </span>
        </div>
        <div>
        	<button class="theme_btn">
        		Login
        	</button>
        </div>
    </form>
    <div class="regist">
    	Don't have an account? <a href="#">Register</a>
    </div>
		</div>
		<div class="col-lg-7 col-sm-12">

			<div class="login_img">
				<img src="assets/img/blank_img.png" alt="">
			</div>
		</div>
		</div>
	</div>
</section>
<!-- basic modal -->
<div class="modal fade fotget_password_popup" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
        </button>
      </div>
      <div class="modal-body text-center">
        <h3>Forgot password</h3>
        <p>Enter the email address you sighed up with to receive a secure link.</p>
        <form action="" class="forget_pass">
        	<input type="email" class="email" placeholder="Enter your email">
        	<button class="theme_btn">Send link</button>
        </form>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
    </div>
  </div>
</div>

</body>

@endsection