@extends('front/partials/master')
@section('content')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{ asset('assets/js/login.js') }}"></script>
    <style>
        .alert.alert-danger.alert-dismissible {
            background: #870000;
            border: none;
            border-radius: 30px;
            padding: 20px;
            text-align: center;
            color: #fff;
        }

        .alert.alert-danger.alert-dismissible .close {
            height: 50px;
            width: 50px;
            opacity: 1;
            font-weight: 400;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            border-radius: 100%;
            position: absolute;
            top: 5px;
            right: 10px;
        }

        #payment-form input.form-control {
            color: white !important;
        }

        .alert.alert-success.text-center {
            background: #e3c935;
            color: #000;
            border: none;
            border-radius: 30px;
            padding: 20px;
            width: 100%;
            margin: 20px auto;
            margin-bottom: 50px;
        }

        .alert.alert-success.text-center p {
            margin: 0;
            color: #000;
            font-weight: 600;
            text-transform: uppercase;
        }

        .alert.alert-success.text-center a.close {
            width: 50px;
            height: 50px;
            position: absolute;
            top: 7px;
            right: 1%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 100%;
            background: #0b3b6a;
            opacity: 1;
            color: #fff;
            font-weight: 400;
        }

        #update_seat .accordion .accordion-item .accordion-header button {
            background: #1C1E22 !important;
            width: 100%;
            border-radius: 30px !important;
            color: #fff;
            padding: 20px 15px;
            font-size: 18px;
        }


        #update_seat div#accordionExample {
            padding: 20px;
            padding-bottom: 50px;
        }

        #update_seat .accordion .accordion-item .accordion-header .accordion-button::after {
            color: #e3c935 !important;
            filter: invert(1);
        }

        #update_seat .accordion .accordion-item .accordion-header .accordion-button i {
            color: #e3c935 !important;
            font-size: 20px;
        }

        #update_seat .accordion .accordion-item .accordion-header {
            border-radius: 30px !important;
            overflow: hidden;
            border: 1px solid #fff;
        }

        #update_seat .collapse.show {
            padding-top: 40px;
            padding-bottom: 40px;
        }

        #update_seat button#delete_seat_11 {
            margin-top: 30px;
        }

        #forgot_password input.error {
            border: 1px solid red;
            margin-bottom: 0 !important;
        }

        .emailError {
            width: 100%;
            text-align: left;
            display: inline-block;
        }
    </style>

    <body>
        <section class="login">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-5 col-sm-12 form_col d-flex flex-column justify-content-between">
                        <div class="cont">
                            <h2>Welcome back to Networked</h2>
                            <h6>Log In to your account</h6>
                        </div>
                        @if ($errors->first('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    &times;
                                </button>
                                {{ $errors->first('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible text-center fade show">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    &times;
                                </button>
                                {{ session('success') }}
                            </div>
                        @endif
                        <form class="login_form" method="POST">
                            <div>
                                <label for="email">Email address</label>
                                <input value="{{ session('email') }}" type="email" id="email" name="email"
                                    placeholder="Enter your email" required>
                            </div>
                            <div class="pass">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" placeholder="Enter your password"
                                    required>
                                <span id="passwordError" style="color: red;"></span>
                                <span id="successMessage" style="color: green;"></span>
                                <span class="forg_pass">
                                    <a style="color: #16adcb;" data-bs-toggle="modal"
                                        data-bs-target="#forgetPassword">Forgot password?</a>
                                </span>
                            </div>
                            <div class="login_btn"></div>
                        </form>
                        <div class="regist">
                            Don't have an account? <a href="{{ route('registerPage') }}">Register</a>
                        </div>
                    </div>
                    <div class="col-lg-7 col-sm-12">
                        <div class="login_img">
                            <img src="{{ asset('assets/img/login-picture.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="modal fade fotget_password_popup" id="forgetPassword" tabindex="-1" role="dialog"
            aria-labelledby="basicModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <h3>Forgot password</h3>
                        <p>Enter the email address you signed up with to receive a secure link.</p>
                        <form id="forgot_password" class="forget_pass">
                            @csrf
                            <input type="email" class="email" name="email" placeholder="Enter your email">
                            <span class="text-danger emailError" id="inputEmailError"></span>
                            <button class="theme_btn">Send link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if ($is_password)
            <div class="modal fade fotget_password_popup" id="updatePassword" tabindex="-1" role="dialog"
                aria-labelledby="basicModal" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <h3>Update password</h3>
                            <form id="update_password" class="forget_pass">
                                @csrf
                                <input type="hidden" name="forgetEmail" value="{{ $forget_email }}">
                                <input type="password" class="password" name="password" placeholder="Enter new password">
                                <input type="password" class="password" name="confirm_password"
                                    placeholder="Confirm password">
                                <span class="text-danger emailError" id="inputPasswordError"></span>
                                <button class="theme_btn">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </body>
    @if ($forget_email !== null)
        <script>
            $(document).ready(function() {
                $('#updatePassword').modal({
                    backdrop: 'static',
                    keyboard: false,
                });
                $('#updatePassword').modal('show');
            });
        </script>
    @endif
    <script>
        var checkCredientialRoute = "{{ route('checkCredentials') }}";
        var dashboardRoute = "{{ route('dashboard') }}";
        var forgotPasswordRoute = "{{ route('forgotPassword') }}";
    </script>
@endsection
