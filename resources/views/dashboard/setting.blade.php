@extends('dashboard/partials/master')
@section('content')
    <style>
        .alert.alert-danger.alert-dismissible {
            background: #870000;
            border: none;
            border-radius: 30px;
            padding: 20px;
            text-align: center;
            color: #fff;
            width: 60%;
            margin: 20px auto;
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

        .alert.alert-success.text-center {
            background: #e3c935;
            color: #000;
            border: none;
            border-radius: 30px;
            padding: 20px;
            width: 50%;
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

        .password_form .error {
            border: 1px solid red;
        }
    </style>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible text-center fade show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                &times;
            </button>
            {{ session('success') }}
        </div>
    @endif
    <section class="blacklist setting_sec">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    @if (session('email_verified'))
                        <div class="filter_head_row d-flex">
                            <div class="cont">
                                <h3>Settings</h3>
                                <p>Your email was confirmed: {{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="setting_pass">
                        <strong>Change password</strong>
                        <form action="{{ route('changePassword', ['slug' => $team->slug]) }}" method="POST"
                            class="password_form d-flex">
                            @csrf
                            @method('PUT')
                            <div class="pass_inp">
                                <label for="old_password">Old Password:</label>
                                <input type="password" id="old_password" name="old_password" required
                                    placeholder="Enter old password"
                                    class="{{ $errors->has('old_password') ? 'error' : '' }}">
                                @if ($errors->has('old_password'))
                                    <span class="text-danger">{{ $errors->first('old_password') }}</span>
                                @endif
                            </div>
                            <div class="pass_inp">
                                <label for="new_password">New Password:</label>
                                <input type="password" id="new_password" name="new_password" required
                                    placeholder="Enter New password"
                                    class="{{ $errors->has('new_password') ? 'error' : '' }}">
                                @if ($errors->has('new_password'))
                                    <span class="text-danger">{{ $errors->first('new_password') }}</span>
                                @endif
                            </div>
                            <div class="pass_inp">
                                <label for="new_password_confirmation">Confirm Password:</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                    required placeholder="Enter Confirm password">
                            </div>
                            <input type="submit" value="Change Password" class="pass_btn">
                        </form>
                    </div>
                    {{-- TODO: API Working --}}
                    <div class="setting_pass API_Key">
                        <div class="d-flex justify-content-between"><strong>API Key</strong>
                            <p>Use this API key to access your Skylead account when using Skylead API.</p>
                        </div>
                        <form action="change_password.php" method="post" class="password_form api_key_form">
                            <label for="old_password">Your Api key</label>
                            <input type="text" id="api_key" name="api_key" required
                                placeholder="000000-11223344-556677-00000">
                        </form>
                    </div>
                    {{-- TODO: Delete Account --}}
                    <div class="setting_pass API_Key del_pass">
                        <div class="d-flex"><strong>Delete account</strong>
                            <p>Are you sure you want to delete this account?</p>
                        </div>
                        <button class="del_btn">Delete Account</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
