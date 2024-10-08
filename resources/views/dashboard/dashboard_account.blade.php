@extends('dashboard/partials/master')
@section('content')
    <style>
        .step_form {
            padding: 40px;
        }

        .step_form .form_row>div {
            text-align: start;
        }

        .step_form_popup .modal-dialog {
            border-radius: 45px;
            width: 45%
        }

        .step_form input.error {
            border: 1px solid red;
        }

        #update_seat input.error {
            border: 1px solid red;
            margin-bottom: 5px !important;
        }

        #payment-form input.form-control {
            color: white !important;
        }

        #payment-form input {
            margin-bottom: 15px !important;
        }

        #payment-form span.text-danger {
            margin-bottom: 25px !important;
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

        body textarea {
            height: 174px;
            width: 100%;
            border-radius: 30px;
            background-color: #0000 !important;
            border: 1px solid oklch(0.73 0.02 250.52 / 0.6);
            margin-bottom: 40px !important;
            padding: 24px;
            font-size: 18px;
            color: #8e99a8;
        }
    </style>
    <script src="{{ asset('assets/js/dashboard-account.js') }}"></script>
    <section class="dashboard">
        <div class="container-fluid">
            <div class="row">
                @include('dashboard/partials/dashboard_sidebar')
                <div class="col-lg-8">
                    <div class="dashboard_cont">
                        <div class="row_filter d-flex align-items-center justify-content-between">
                            <div class="account d-flex align-items-center">
                                <img src="{{ asset('assets/img/acc.png') }}" style="background-color: #000;" alt="">
                                <span>{{ $team->name }}</span>
                            </div>
                            <div class="form_add d-flex">
                                <div class="search-form">
                                    <input type="text" name="q" placeholder="Search..." id="search_seat">
                                    <button type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                                @if (session('is_creator'))
                                    <div class="add_btn"
                                        style="opacity: {{ !session('email_verified') ? 0.7 : 1 }}; cursor: {{ !session('email_verified') ? 'default' : 'pointer' }};"
                                        {{ session('email_verified') ? 'data-bs-toggle=modal data-bs-target=#addaccount' : '' }}>
                                        <span
                                            title="{{ !session('email_verified') ? 'To add new seats, you need to verify your email address first.' : '' }}">
                                            <a style="cursor: {{ !session('email_verified') ? 'default' : 'pointer' }};"
                                                href="javascript:;" type="button">
                                                <i class="fa-solid fa-plus"></i>
                                            </a>Add account
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row_table">
                            @if ($seats->isNotEmpty())
                                <div class="add_account_div" style="width: 100%">
                                    <div class="campaign_list">
                                        <table class="data_table w-100">
                                            <tbody id="campaign_table_body">
                                                @foreach ($seats as $seat)
                                                    @php
                                                        $company_info = \App\Models\Company_info::find(
                                                            $seat->company_info_id,
                                                        );
                                                    @endphp
                                                    <tr title="{{ !session('email_verified') ? 'Verify your email first to view seat' : '' }}"
                                                        style="opacity:{{ !session('email_verified') ? 0.7 : 1 }};"
                                                        id="{{ 'table_row_' . $seat->id }}" class="seat_table_row">
                                                        <td width="10%" class="seat_table_data"
                                                            style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};">
                                                            @if (isset($seat['account_profile']) && $seat['account_profile']['profile_picture_url'] != '')
                                                                <img class="seat_img"
                                                                    src="{{ $seat['account_profile']['profile_picture_url'] }}"
                                                                    alt="">
                                                            @else
                                                                <img class="seat_img"
                                                                    src="{{ asset('assets/img/acc.png') }}" alt="">
                                                            @endif
                                                        </td>
                                                        <td width="50%" class="text-left seat_table_data"
                                                            style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};">
                                                            {{ $company_info->name }}
                                                        </td>
                                                        <td width="15%" class="connection_status">
                                                            @if ($seat->is_connected)
                                                                <div class="connected"><span></span>Connected</div>
                                                            @else
                                                                <div class="disconnected"><span></span>Disconnected</div>
                                                            @endif
                                                        </td>
                                                        <td width="15%" class="activeness_status">
                                                            @if ($seat->is_active)
                                                                <div class="active"><span></span>Active</div>
                                                            @else
                                                                <div class="not_active"><span></span>In Active</div>
                                                            @endif
                                                        </td>
                                                        <td width="10%">
                                                            <a href="javascript:;" type="button"
                                                                class="setting setting_btn"
                                                                style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};">
                                                                <i class="fa-solid fa-gear"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @elseif (session('is_creator'))
                                <div class="add_account_div"
                                    style="opacity: {{ !session('email_verified') ? '0.7' : '1' }};"
                                    title="{{ !session('email_verified') ? 'To add new seats, you need to verify your email address first.' : '' }}">
                                    <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                    <p class="text-center">
                                        {{ !session('email_verified')
                                            ? "You can't add account until you verify your email address."
                                            : "You don't have any account yet. Start by adding your first account." }}
                                    </p>
                                    <div class="add_btn">
                                        <a style="cursor: {{ !session('email_verified') ? 'default' : 'pointer' }};"
                                            href="javascript:;" type="button"
                                            {{ session('email_verified') ? 'data-bs-toggle=modal data-bs-target=#addaccount' : '' }}>
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="add_account_div" style="width: 100%">
                                    <div class="campaign_list">
                                        <table class="data_table w-100">
                                            <tbody id="campaign_table_body">
                                                <tr>
                                                <tr>
                                                    <td colspan="4">
                                                        <div style="width: 50%; margin: 0 auto;"
                                                            class="empty_blacklist text-center">
                                                            <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                                            <p>Sorry, no results for that query</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (session('is_creator') && session('email_verified'))
        <div class="modal fade step_form_popup" id="addaccount" tabindex="-1" role="dialog" aria-labelledby="addaccount"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="text-center">Add Account</h4>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <form role="form" action="{{ route('stripePayment', ['slug' => $team->slug]) }}"
                            method="post" data-cc-on-file="false" id="payment-form"
                            data-stripe-publishable-key="{{ config('services.stripe.key') }}" class="form step_form">
                            @csrf
                            <input type="hidden" name="stripe_token" id="stripe_token">
                            <div class="progress-bar" id="progress-bar">
                                <div class="progress" id="progress"></div>
                                <div class="progress-step active" data-title="Company Info"></div>
                                <div class="progress-step" data-title="Seat Info"></div>
                                <div class="progress-step" data-title="Payment"></div>
                            </div>
                            <div class="form-step active">
                                <div class="form_row row">
                                    <div class="col-lg-12 required">
                                        <label for="street_address">Street Address</label>
                                        <input type="text" name="street_address" id="street_address"
                                            placeholder="Enter your street address" required
                                            class="{{ $errors->has('street_address') ? 'error' : '' }}"
                                            value="{{ old('street_address') }}">
                                        <span class="text-danger">{{ $errors->first('street_address') }}</span>
                                    </div>
                                    <div class="col-lg-12 required">
                                        <label for="city">City</label>
                                        <input type="text" name="city" id="city"
                                            placeholder="Enter your city" required
                                            class="{{ $errors->has('city') ? 'error' : '' }}"
                                            value="{{ old('city') }}">
                                        <span class="text-danger">{{ $errors->first('city') }}</span>
                                    </div>
                                    <div class="col-md-12 col-lg-6 required">
                                        <label for="state">State</label>
                                        <input type="text" name="state" id="state"
                                            placeholder="Enter your state" required
                                            class="{{ $errors->has('state') ? 'error' : '' }}"
                                            value="{{ old('state') }}">
                                        <span class="text-danger">{{ $errors->first('state') }}</span>
                                    </div>
                                    <div class="col-md-12 col-lg-6 required">
                                        <label for="postal_code">Postal Code</label>
                                        <input type="text" name="postal_code" id="postal_code"
                                            placeholder="Enter your postal code" required
                                            class="{{ $errors->has('postal_code') ? 'error' : '' }}"
                                            value="{{ old('postal_code') }}">
                                        <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                                    </div>
                                    <div class="col-lg-12 required">
                                        <label for="country">Country of incorporation</label>
                                        <input type="text" name="country" id="country"
                                            placeholder="Enter your country" required
                                            class="{{ $errors->has('country') ? 'error' : '' }}"
                                            value="{{ old('country') }}">
                                        <span class="text-danger">{{ $errors->first('country') }}</span>
                                    </div>
                                    <div class="col-lg-12 required">
                                        <label for="company">Company name</label>
                                        <input type="text" name="company" id="company"
                                            placeholder="Enter your company name" required
                                            class="{{ $errors->has('company') ? 'error' : '' }}"
                                            value="{{ old('company') }}">
                                        <span class="text-danger">{{ $errors->first('company') }}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <label for="tax_id">Tax ID number</label>
                                        <input type="text" name="tax_id" id="tax_id"
                                            placeholder="Enter your tax id" value="{{ old('tax_id') }}">
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-prev">Previous</a>
                                    <a class="btn btn-next">Next</a>
                                </div>
                            </div>
                            <div class="form-step ">
                                <div class="form_row row">
                                    <div class="col-lg-12 required">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email"
                                            placeholder="Enter your email" required
                                            class="{{ $errors->has('email') ? 'error' : '' }}"
                                            value="{{ old('email') }}">
                                        <span class="text-danger">{{ $errors->first('email') }}</span>
                                    </div>
                                    <div class="col-lg-12 required">
                                        <label for="phone_number">Phone Number</label>
                                        <input type="phone_number" name="phone_number" id="phone_number"
                                            placeholder="Enter your phone number" required
                                            class="{{ $errors->has('phone_number') ? 'error' : '' }}"
                                            value="{{ old('phone_number') }}">
                                        <span class="text-danger">{{ $errors->first('phone_number') }}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <label for="summary">Profile Summary</label>
                                        <textarea name="summary" id="summary" cols="42" rows="6" placeholder="Enter profile summary"
                                            value="{{ old('summary') }}"></textarea>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-prev">Previous</a>
                                    <a class="btn btn-next">Next</a>
                                </div>
                            </div>
                            <div class="form-step ">
                                <div class="experiences-group">
                                    <div class='form_row row'>
                                        <div class='col-lg-12 form-group required'>
                                            <label for="card_name" class='control-label'>Name on Card</label>
                                            <input class='form-control' name="card_name" id="card_name" type='text'
                                                required class="{{ $errors->has('card_name') ? 'error' : '' }}"
                                                value="{{ old('card_name') }}">
                                            <span class="text-danger">{{ $errors->first('card_name') }}</span>
                                        </div>
                                        <div class='col-lg-12 form-group required'>
                                            <label class='control-label' for="card_number">Card Number</label>
                                            <input autocomplete='off' class='form-control card-number' name="card_number"
                                                id="card_number" type='text'
                                                class="{{ $errors->has('card_number') ? 'error' : '' }}"
                                                value="{{ old('card_number') }}" required>
                                            <span
                                                class="text-danger card_number_error">{{ $errors->first('card_number') }}</span>
                                        </div>
                                        <div class='col-lg-4 col-md-6 col-xs-12 form-group cvc required'>
                                            <label class='control-label' for="card_cvc">CVC</label>
                                            <input autocomplete='off'
                                                class='form-control card-cvc {{ $errors->has('card_cvc') ? 'error' : '' }}'
                                                name="card_cvc" id="card_cvc" placeholder='311' size='4'
                                                type='text' value="{{ old('card_cvc') }}" required>
                                            <span class="text-danger">{{ $errors->first('card_cvc') }}</span>
                                        </div>
                                        <div class='col-lg-4 col-md-6 col-xs-12 form-group expiration required'>
                                            <label class='control-label' for="card_expiry_month">Expiration Month</label>
                                            <input
                                                class='form-control card-expiry-month {{ $errors->has('card_expiry_month') ? 'error' : '' }}'
                                                placeholder='MM' name="card_expiry_month" id="card_expiry_month"
                                                size='2' type='text' value="{{ old('card_expiry_month') }}"
                                                required>
                                            <span class="text-danger">{{ $errors->first('card_expiry_month') }}</span>
                                        </div>
                                        <div class='col-lg-4 col-md-6 col-xs-12 form-group expiration required'>
                                            <label class='control-label' for="card_expiry_year">Expiration Year</label>
                                            <input
                                                class='form-control card-expiry-year {{ $errors->has('card_expiry_year') ? 'error' : '' }}'
                                                name="card_expiry_year" id="card_expiry_year" placeholder='YYYY'
                                                size='4' type='text' value="{{ old('card_expiry_year') }}"
                                                required>
                                            <span class="text-danger">{{ $errors->first('card_expiry_year') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-prev">Previous</a>
                                    <button class="btn btn-primary btn-lg btn-block" type="submit">Pay Now</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <script>
        $(document).ready(function() {
            if ("{{ session()->has('payment_error') }}") {
                $('#addaccount').modal('show');
            }
        });
    </script>
    @if (session('email_verified'))
        <div class="modal fade step_form_popup" id="update_seat" tabindex="-1" role="dialog"
            aria-labelledby="update_seat" aria-hidden="true">
            <div class="modal-dialog" style="border-radius: 45px;width: 30%;">
                <div class="modal-content"></div>
            </div>
        </div>
    @endif
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    @if (session('email_verified'))
        <script>
            var getSeatRoute = "{{ route('getSeatById', ['slug' => $team->slug, ':seat_id']) }}";
            var getSeatAccessRoute = "{{ route('getSeatAccess', ['slug' => $team->slug, ':seat_id']) }}";
            var seatDashboardPageRoute = "{{ route('seatDashboardPage', ['slug' => $team->slug]) }}";
            var updateNameRoute = "{{ route('updateName', ['slug' => $team->slug, ':seat_id', ':seat_name']) }}";
        </script>
    @endif
    <script>
        var accImage = "{{ asset('assets/img/acc.png') }}";
        var emailVerified = "{{ session('email_verified') }}";
        var emptyImage = "{{ asset('assets/img/empty.png') }}";
        var filterSeatRoute = "{{ route('filterSeat', ['slug' => $team->slug, ':search']) }}";
    </script>
@endsection
