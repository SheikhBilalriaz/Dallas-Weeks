@extends('dashboard/partials/master')
@section('content')
    <style>
        .password_form .error {
            border: 1px solid red;
        }
    </style>
    <section class="blacklist setting_sec">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="filter_head_row d-flex">
                        <div class="cont">
                            <h3>Settings</h3>
                            @if (session('email_verified'))
                                <p>Your email was confirmed: {{ auth()->user()->email }}</p>
                            @else
                                <p>Please verify an email: {{ auth()->user()->email }}</p>
                            @endif
                        </div>
                    </div>
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
