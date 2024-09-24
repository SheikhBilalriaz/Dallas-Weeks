@extends('dashboard/partials/master')
@section('content')
    <style>
        #payment-form input.form-control {
            color: white !important;
        }

        #payment-form input {
            margin-bottom: 15px !important;
        }

        #payment-form span.text-danger {
            margin-bottom: 25px !important;
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
    </style>
    @if (session('email_verified'))
        <script src="{{ asset('assets/js/dashboard-account.js') }}"></script>
    @else
        <script src="{{ asset('assets/js/dashboard-account-filter-search.js') }}"></script>
    @endif
    <section class="dashboard">
        <div class="container-fluid">
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
                                    <div class="add_btn" style="opacity: {{ !session('email_verified') ? 0.7 : 1 }}">
                                        @if (!session('email_verified'))
                                            <span style="cursor: default;"
                                                title="To add new seats, you need to verify your email address first.">
                                                <a style="cursor: default;" href="javascript:;" type="button">
                                                    <i class="fa-solid fa-plus"></i>
                                                </a>Add account
                                            </span>
                                        @else
                                            <span style="cursor: pointer;" data-bs-toggle="modal"
                                                data-bs-target="#addaccount">
                                                <a href="javascript:;" type="button">
                                                    <i class="fa-solid fa-plus"></i>
                                                </a>Add account
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row_table">
                            {{-- @if ($seats->isNotEmpty())
                                <div class="add_account_div" style="width: 100%">
                                    <div class="campaign_list">
                                        <table class="data_table w-100">
                                            <tbody id="campaign_table_body">
                                                @foreach ($seats as $seat)
                                                    <tr title="{{ empty(auth()->user()->email_verified_at) ? 'Verify your email first to view seat' : '' }}"
                                                        style="opacity:{{ empty(auth()->user()->email_verified_at) ? 0.7 : 1 }};"
                                                        id="{{ 'table_row_' . $seat['id'] }}" class="seat_table_row">
                                                        @if (isset($seat['account_profile']) && $seat['account_profile']['profile_picture_url'] != '')
                                                            <td width="10%" class="seat_table_data"
                                                                style="cursor: {{ empty(auth()->user()->email_verified_at) ? 'auto' : 'pointer' }};">
                                                                <img class="seat_img"
                                                                    src="{{ $seat['account_profile']['profile_picture_url'] }}"
                                                                    alt="">
                                                            </td>
                                                        @else
                                                            <td width="10%" class="seat_table_data"
                                                                style="cursor: {{ empty(auth()->user()->email_verified_at) ? 'auto' : 'pointer' }};">
                                                                <img class="seat_img"
                                                                    src="{{ asset('assets/img/acc.png') }}" alt="">
                                                            </td>
                                                        @endif
                                                        <td width="50%" class="text-left seat_table_data"
                                                            style="cursor: {{ empty(auth()->user()->email_verified_at) ? 'auto' : 'pointer' }};">
                                                            {{ $seat['username'] }}
                                                        </td>
                                                        <td width="15%" class="connection_status">
                                                            @if ($seat['connected'])
                                                                <div class="connected"><span></span>Connected</div>
                                                            @else
                                                                <div class="disconnected"><span></span>Disconnected</div>
                                                            @endif
                                                        </td>
                                                        <td width="15%" class="activeness_status">
                                                            @if ($seat['active'])
                                                                <div class="active"><span></span>Active</div>
                                                            @else
                                                                <div class="not_active"><span></span>In Active</div>
                                                            @endif
                                                        </td>
                                                        <td width="10%">
                                                            <a href="javascript:;" type="button"
                                                                class="setting setting_btn"
                                                                style="cursor: {{ empty(auth()->user()->email_verified_at) ? 'auto' : 'pointer' }};"><i
                                                                    class="fa-solid fa-gear"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif --}}
                            @if (session('is_creator'))
                                @if (!session('email_verified'))
                                    <div class="add_account_div" style="opacity: 0.7;"
                                        title="To add new seats, you need to verify your email address first.">
                                        <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                        <p class="text-center">
                                            You can't add account until you verify your email address.
                                        </p>
                                        <div class="add_btn">
                                            <a style="cursor: default;" href="javascript:;" type="button">
                                                <i class="fa-solid fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="add_account_div">
                                        <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                        <p class="text-center">
                                            You don't have any account yet. Start by adding your first account.
                                        </p>
                                        <div class="add_btn">
                                            <a href="javascript:;" type="button" data-bs-toggle="modal"
                                                data-bs-target="#addaccount">
                                                <i class="fa-solid fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="add_account_div" style="width: 100%">
                                    <div class="campaign_list">
                                        <table class="data_table w-100">
                                            <tbody id="campaign_table_body">
                                                <tr>
                                                    <td colspan="8">
                                                        <div class="text-center text-danger"
                                                            style="font-size: 25px; font-weight: bold; font-style: italic;">
                                                            Not Found!
                                                        </div>
                                                    </td>
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
            <div class="modal-dialog" style="border-radius: 45px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="text-center">Add Account</h4>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <form role="form" action="{{ route('stripePayment', ['slug' => $team->slug]) }}" method="post"
                            data-cc-on-file="false" data-stripe-publishable-key="{{ env('STRIPE_PK') }}" id="payment-form"
                            class="form step_form">
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
                                    <div class="col-lg-12">
                                        <label for="street_address">Street Address</label>
                                        <input type="text" name="street_address" id="street_address"
                                            placeholder="Enter your street address" required
                                            class="{{ $errors->has('street_address') ? 'error' : '' }}"
                                            value="{{ old('street_address') }}">
                                        @if ($errors->has('street_address'))
                                            <span class="text-danger">{{ $errors->first('street_address') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-lg-12">
                                        <label for="city">City</label>
                                        <input type="text" name="city" id="city"
                                            placeholder="Enter your city" required
                                            class="{{ $errors->has('city') ? 'error' : '' }}"
                                            value="{{ old('city') }}">
                                        @if ($errors->has('city'))
                                            <span class="text-danger">{{ $errors->first('city') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="state">State</label>
                                        <input type="text" name="state" id="state"
                                            placeholder="Enter your state" required
                                            class="{{ $errors->has('state') ? 'error' : '' }}"
                                            value="{{ old('state') }}">
                                        @if ($errors->has('state'))
                                            <span class="text-danger">{{ $errors->first('state') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="postal_code">Postal Code</label>
                                        <input type="text" name="postal_code" id="postal_code"
                                            placeholder="Enter your postal code" required
                                            class="{{ $errors->has('postal_code') ? 'error' : '' }}"
                                            value="{{ old('postal_code') }}">
                                        @if ($errors->has('postal_code'))
                                            <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-lg-12">
                                        <label for="country">Country of incorporation</label>
                                        <input type="text" name="country" id="country"
                                            placeholder="Enter your country" required
                                            class="{{ $errors->has('country') ? 'error' : '' }}"
                                            value="{{ old('country') }}">
                                        @if ($errors->has('country'))
                                            <span class="text-danger">{{ $errors->first('country') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-lg-12">
                                        <label for="company">Company name</label>
                                        <input type="text" name="company" id="company"
                                            placeholder="Enter your company name" required
                                            class="{{ $errors->has('company') ? 'error' : '' }}"
                                            value="{{ old('company') }}">
                                        @if ($errors->has('company'))
                                            <span class="text-danger">{{ $errors->first('company') }}</span>
                                        @endif
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
                                    <div class="col-lg-12">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email">
                                    </div>
                                    <div class="col-lg-12">
                                        <label for="phone_number">Phone Number</label>
                                        <input type="phone_number" name="phone_number" id="phone_number">
                                    </div>
                                    <div class="">
                                        <label for="summary">Profile Summary</label>
                                        <textarea name="summary" id="summary" cols="42" rows="6"></textarea>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-prev">Previous</a>
                                    <a class="btn btn-next">Next</a>
                                </div>
                            </div>
                            <div class="form-step ">
                                <h3>Payment</h3>
                                <div class="experiences-group">
                                    <div class='form-row row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label for="card_name" class='control-label'>Name on Card</label>
                                            <input class='form-control' name="card_name" id="card_name" type='text'>
                                        </div>
                                    </div>
                                    <div class='form-row row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label' for="card_number">Card Number</label>
                                            <input autocomplete='off' class='form-control card-number' name="card_number"
                                                id="card_number" type='text'
                                                class="{{ $errors->has('card_number') ? 'error' : '' }}"
                                                value="{{ old('card_number') }}">
                                            <span
                                                class="text-danger card_number_error">{{ $errors->first('card_number') }}</span>
                                        </div>
                                    </div>
                                    <div class='form-row row'>
                                        <div class='col-xs-12 col-md-4 form-group cvc required'>
                                            <label class='control-label' for="card_cvc">CVC</label>
                                            <input autocomplete='off' class='form-control card-cvc' name="card_cvc"
                                                id="card_cvc" placeholder='ex. 311' size='4' type='text'>
                                        </div>
                                        <div class='col-xs-12 col-md-4 form-group expiration required'>
                                            <label class='control-label' for="card_expiry_month">Expiration Month</label>
                                            <input class='form-control card-expiry-month' placeholder='MM'
                                                name="card_expiry_month" id="card_expiry_month" size='2'
                                                type='text'>
                                        </div>
                                        <div class='col-xs-12 col-md-4 form-group expiration required'>
                                            <label class='control-label' for="card_expiry_year">Expiration Year</label>
                                            <input class='form-control card-expiry-year' name="card_expiry_year"
                                                id="card_expiry_year" placeholder='YYYY' size='4' type='text'>
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
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        $('#payment-form').bind('submit', function(e) {
            e.preventDefault();
            var stripe = Stripe.setPublishableKey($(this).data('stripe-publishable-key'));
            Stripe.createToken({
                number: $('#card_number').val(),
                cvc: $('#card_cvc').val(),
                exp_month: $('#card_expiry_month').val(),
                exp_year: $('#card_expiry_year').val()
            }, stripeResponseHandler);
        });

        function stripeResponseHandler(status, response) {
            if (response.error) {
                switch (response.error.type) {
                    case 'card_error':
                        $('.card_number_error').html(response.error.message);
                        break;
                    default:
                        console.log(response);
                        break;
                }
            } else {
                var token = response['id'];
                $('#stripe_token').val(token);
                $('#payment-form').submit();
            }
        }
    </script>
    @if (session('email_verified'))
        <div class="modal fade step_form_popup" id="update_seat" tabindex="-1" role="dialog"
            aria-labelledby="update_seat" aria-hidden="true">
            <div class="modal-dialog" style="border-radius: 45px;width: 35%;">
                <div class="modal-content"></div>
            </div>
        </div>
    @endif
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    {{-- @if (session('email_verified'))
        <script>
            var getSeatRoute = "{{ route('getSeatById', [':seat_id']) }}";
            var deleteSeatRoute = "{{ route('deleteSeat', [':seat_id']) }}";
            var updateNameRoute = "{{ route('updateName', [':seat_id', ':seat_name']) }}";
            var dashboardRoute = "{{ route('acc_dash') }}";
        </script>
    @endif --}}
    {{-- <script>
        var filterSeatRoute = "{{ route('filterSeat', [':search']) }}";
    </script> --}}
@endsection
