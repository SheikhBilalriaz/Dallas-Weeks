@extends('back/partials/header')
@section('content')
    <style>
        .theme_btn {
            margin-left: 0 !important;
        }

        input.error {
            border: 1px solid red;
            margin-bottom: 0 !important;
        }
    </style>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <section class="main_dashboard blacklist  campaign_sec lead_sec setting_sec">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                <div class="col-lg-11 col-sm-12">
                    @if (session()->has('add_account'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Add Account! </strong> You should integrate linkedin account first.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between w-100 setting_head">
                                <h3>Settings</h3>
                                <div class="filt_opt d-flex">
                                    <p>
                                        Customize settings for your account. Highest limits are imposed by
                                        <br>
                                        LinkedIn system itself and vary by type of premium account.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="comp_tabs setting_tabs">
                                @php
                                    $is_linkedin_settings =
                                        session('manage_global_limits') === true ||
                                        session('manage_global_limits') === 'view_only' ||
                                        session('manage_linkedin_integrations') === true ||
                                        session('manage_linkedin_integrations') === 'view_only' ||
                                        session('manage_account_health') === true ||
                                        session('manage_account_health') === 'view_only';
                                    $is_email_settings =
                                        session('manage_email_settings') === true ||
                                        session('manage_email_settings') === 'view_only';
                                @endphp
                                <ul class="nav nav-tabs" role="tablist">
                                    @if ($is_linkedin_settings)
                                        <li class="nav-item">
                                            <a class="nav-link setting_tab active" data-bs-toggle="tab" href="#LinkedIn"
                                                role="tab">
                                                LinkedIn settings
                                            </a>
                                        </li>
                                    @endif
                                    @if ($is_email_settings)
                                        <li class="nav-item">
                                            <a class="nav-link setting_tab {{ $is_linkedin_settings ? '' : 'active' }}"
                                                data-bs-toggle="tab" href="#emailSetting" role="tab">
                                                Email settings
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                <div class="tab-content border_box">
                                    @php
                                        $manage_global_limits =
                                            session('manage_global_limits') == true ||
                                            session('manage_global_limits') == 'view_only';
                                        $manage_global_health =
                                            session('manage_account_health') == true ||
                                            session('manage_account_health') == 'view_only';
                                        $manage_linkedin_integrations =
                                            session('manage_linkedin_integrations') == true ||
                                            session('manage_linkedin_integrations') == 'view_only';
                                    @endphp
                                    @if ($is_linkedin_settings)
                                        <div class="tab-pane setting_pane active" id="LinkedIn" role="tabpanel">
                                            <ul class="nav nav-tabs" role="tablist">
                                                @if ($manage_global_limits)
                                                    <li class="nav-item">
                                                        <a class="nav-link linkedin_setting active" data-bs-toggle="tab"
                                                            href="#global" role="tab">
                                                            Global limits for campaigns
                                                        </a>
                                                    </li>
                                                @endif
                                                @if ($manage_global_health)
                                                    <li class="nav-item">
                                                        <a class="nav-link linkedin_setting {{ $manage_global_limits ? '' : 'active' }}"
                                                            data-bs-toggle="tab" href="#health" role="tab">
                                                            Account health
                                                        </a>
                                                    </li>
                                                @endif
                                                @if ($manage_linkedin_integrations)
                                                    <li class="nav-item">
                                                        <a class="nav-link linkedin_setting {{ $manage_global_limits || $manage_global_health ? '' : 'active' }}"
                                                            data-bs-toggle="tab" href="#integrations" role="tab">
                                                            LinkedIn integration
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                            <div class="tab-content">
                                                @if (session('manage_global_limits') === 'view_only')
                                                    <div class="tab-pane linkedin_pane global_tab active" id="Global"
                                                        role="tabpanel">
                                                        <h6>Time zone</h6>
                                                        <div class="time_zone_form">
                                                            <div class="input_fields">
                                                                <label for="timezone">Your Time Zone:</label>
                                                                <select name="timezone" id="timezone" disabled>
                                                                    @foreach ($time_zones as $zone)
                                                                        <option value="{{ $zone['timezone'] }}"
                                                                            {{ $seat_zone->timezone == $zone['timezone'] ? 'selected' : '' }}>
                                                                            {{ $zone['offset'] . ' - ' . $zone['timezone'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="input_fields">
                                                                <label for="start_time">Start Time:</label>
                                                                <input type="time" name="start_time" id="start_time"
                                                                    value="{{ isset($start_time->time) ? \Carbon\Carbon::createFromFormat('H:i:s', $start_time->time)->format('H:i') : '09:00' }}"
                                                                    required readonly disabled>
                                                            </div>
                                                            <div class="input_fields">
                                                                <label for="end_time">End Time:</label>
                                                                <input type="time" name="end_time" id="end_time"
                                                                    value="{{ isset($end_time->time) ? \Carbon\Carbon::createFromFormat('H:i:s', $end_time->time)->format('H:i') : '17:00' }}"
                                                                    required readonly disabled>
                                                            </div>
                                                        </div>
                                                        <div class="globle_list">
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Profile views</h4>
                                                                    Change daily limit for viewing profiles. If your account
                                                                    is new or haven't been active for some time we advice to
                                                                    start with lower limits than maximum, otherwise set
                                                                    limits at range 20 to 30.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="profile_views">Profile Views:
                                                                        <span
                                                                            id="profile_views_val">{{ $profile_views->value }}</span></label>
                                                                    <input type="range" name="profile_views"
                                                                        id="profile_views" min="0"
                                                                        max="180" step="10"
                                                                        value="{{ $profile_views->value }}"
                                                                        data-span="profile_views_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Follows</h4>
                                                                    Change daily limit for following profiles. If your
                                                                    account is new or haven't been active for some time we
                                                                    advice to start with lower limits than maximum,
                                                                    otherwise set limits at range 20 to 30.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="follows">Follows:
                                                                        <span
                                                                            id="follows_val">{{ $follows->value }}</span></label>
                                                                    <input type="range" name="follows"
                                                                        id="follows" min="0" max="160"
                                                                        step="10" value="{{ $follows->value }}"
                                                                        data-span="follows_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Connection invites</h4>
                                                                    The daily limit for sending connection invites. We
                                                                    recommend keeping your connect limits in the range
                                                                    between 10 and 25 per day.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="invite">Invites:
                                                                        <span
                                                                            id="invite_val">{{ $invite->value }}</span></label>
                                                                    <input type="range" name="invite"
                                                                        id="invite" min="0" max="15"
                                                                        step="1" value="{{ $invite->value }}"
                                                                        data-span="invite_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Messages</h4>
                                                                    Change daily limit for sending messages. If your account
                                                                    is new or haven't been active for some time we advice to
                                                                    start with lower limits than maximum, otherwise set
                                                                    limits at range 30 to 40.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="message">Messages:
                                                                        <span
                                                                            id="message_val">{{ $message->value }}</span></label>
                                                                    <input type="range" name="message"
                                                                        id="message" min="0" max="120"
                                                                        step="1" value="{{ $message->value }}"
                                                                        data-span="message_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>InMail</h4>
                                                                    Change daily limit for sending InMail messages. If your
                                                                    account is new or haven't been active for some time we
                                                                    advice to start with lower limits than maximum,
                                                                    otherwise set limits at range 15 to 25.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="inmail">Inmail Messages:
                                                                        <span
                                                                            id="inmail_val">{{ $inmail->value }}</span></label>
                                                                    <input type="range" name="inmail"
                                                                        id="inmail" min="0" max="45"
                                                                        step="1" value="{{ $inmail->value }}"
                                                                        data-span="inmail_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Discover</h4>
                                                                    Change the number of pages per day that the tool will
                                                                    discover from the search results. Recommend range is
                                                                    between 40 and 60
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="discover">Discover:
                                                                        <span
                                                                            id="discover_val">{{ $discover->value }}</span></label>
                                                                    <input type="range" name="discover"
                                                                        id="discover" min="0" max="100"
                                                                        step="1" value="{{ $discover->value }}"
                                                                        data-span="discover_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Email</h4>
                                                                    Change daily limit for sending email messages. If your
                                                                    account is new, or haven't been active for some time we
                                                                    advise to start with lower limits than maximum,
                                                                    otherwise set limits of 20 and 40.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="email_message">Email:
                                                                        <span
                                                                            id="email_message_val">{{ $email_message->value }}</span></label>
                                                                    <input type="range" name="email_message"
                                                                        id="email_message" min="0"
                                                                        max="100" step="1"
                                                                        value="{{ $email_message->value }}"
                                                                        data-span="email_message_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="row grey_box d-flex align-items-center">
                                                                <div class="eye_img col-lg-1">
                                                                    <img src="{{ asset('assets/img/eye.png') }}"
                                                                        alt="">
                                                                </div>
                                                                <div class="cont col-lg-7">
                                                                    <h4>Email delay</h4>
                                                                    The minimum number of minutes between the emails are
                                                                    being sent from the tool. For examle, if set to 5, the
                                                                    email is going to be sent each 5 minutes from the tool.
                                                                    The recommended value is 5 or more.
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label for="email_delay">Email Delay:
                                                                        <span
                                                                            id="email_delay_val">{{ $email_delay->value }}</span></label>
                                                                    <input type="range" name="email_delay"
                                                                        id="email_delay" min="0"
                                                                        max="60" step="1"
                                                                        value="{{ $email_delay->value }}"
                                                                        data-span="email_delay_val"
                                                                        class="global_limit_ranges"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif (session('manage_global_limits') === true)
                                                    <div class="tab-pane linkedin_pane global_tab active" id="Global"
                                                        role="tabpanel">
                                                        <h6>Time zone</h6>
                                                        <form
                                                            action="{{ route('updateSeatLimit', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="time_zone_form">
                                                                <div class="input_fields">
                                                                    <label for="timezone">Your Time Zone:</label>
                                                                    <select name="timezone" id="timezone">
                                                                        @foreach ($time_zones as $zone)
                                                                            <option value="{{ $zone['timezone'] }}"
                                                                                {{ $seat_zone->timezone == $zone['timezone'] ? 'selected' : '' }}>
                                                                                {{ $zone['offset'] . ' - ' . $zone['timezone'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="input_fields">
                                                                    <label for="start_time">Start Time:</label>
                                                                    <input type="time" name="start_time"
                                                                        id="start_time"
                                                                        value="{{ old(
                                                                            'start_time',
                                                                            isset($start_time->time) ? \Carbon\Carbon::createFromFormat('H:i:s', $start_time->time)->format('H:i') : '09:00',
                                                                        ) }}"
                                                                        required
                                                                        class="{{ $errors->has('start_time') ? 'error' : '' }}">
                                                                    @if ($errors->has('start_time'))
                                                                        <span
                                                                            class="text-danger">{{ $errors->first('start_time') }}</span>
                                                                    @endif
                                                                </div>
                                                                <div class="input_fields">
                                                                    <label for="end_time">End Time:</label>
                                                                    <input type="time" name="end_time" id="end_time"
                                                                        value="{{ old(
                                                                            'end_time',
                                                                            isset($end_time->time) ? \Carbon\Carbon::createFromFormat('H:i:s', $end_time->time)->format('H:i') : '17:00',
                                                                        ) }}"
                                                                        required
                                                                        class="{{ $errors->has('end_time') ? 'error' : '' }}">
                                                                    @if ($errors->has('end_time'))
                                                                        <span
                                                                            class="text-danger">{{ $errors->first('end_time') }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="globle_list">
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Profile views</h4>
                                                                        Change daily limit for viewing profiles. If your
                                                                        account
                                                                        is new or haven't been active for some time we
                                                                        advice to
                                                                        start with lower limits than maximum, otherwise set
                                                                        limits at range 20 to 30.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="profile_views">Profile Views:
                                                                            <span
                                                                                id="profile_views_val">{{ $profile_views->value }}</span></label>
                                                                        <input type="range" name="profile_views"
                                                                            id="profile_views" min="0"
                                                                            max="180" step="10"
                                                                            value="{{ $profile_views->value }}"
                                                                            data-span="profile_views_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Follows</h4>
                                                                        Change daily limit for following profiles. If your
                                                                        account is new or haven't been active for some time
                                                                        we
                                                                        advice to start with lower limits than maximum,
                                                                        otherwise set limits at range 20 to 30.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="follows">Follows:
                                                                            <span
                                                                                id="follows_val">{{ $follows->value }}</span></label>
                                                                        <input type="range" name="follows"
                                                                            id="follows" min="0" max="160"
                                                                            step="10" value="{{ $follows->value }}"
                                                                            data-span="follows_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Connection invites</h4>
                                                                        The daily limit for sending connection invites. We
                                                                        recommend keeping your connect limits in the range
                                                                        between 10 and 25 per day.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="invite">Invites:
                                                                            <span
                                                                                id="invite_val">{{ $invite->value }}</span></label>
                                                                        <input type="range" name="invite"
                                                                            id="invite" min="0" max="15"
                                                                            step="1" value="{{ $invite->value }}"
                                                                            data-span="invite_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Messages</h4>
                                                                        Change daily limit for sending messages. If your
                                                                        account
                                                                        is new or haven't been active for some time we
                                                                        advice to
                                                                        start with lower limits than maximum, otherwise set
                                                                        limits at range 30 to 40.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="message">Messages:
                                                                            <span
                                                                                id="message_val">{{ $message->value }}</span></label>
                                                                        <input type="range" name="message"
                                                                            id="message" min="0" max="120"
                                                                            step="1" value="{{ $message->value }}"
                                                                            data-span="message_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>InMail</h4>
                                                                        Change daily limit for sending InMail messages. If
                                                                        your
                                                                        account is new or haven't been active for some time
                                                                        we
                                                                        advice to start with lower limits than maximum,
                                                                        otherwise set limits at range 15 to 25.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="inmail">Inmail Messages:
                                                                            <span
                                                                                id="inmail_val">{{ $inmail->value }}</span></label>
                                                                        <input type="range" name="inmail"
                                                                            id="inmail" min="0" max="45"
                                                                            step="1" value="{{ $inmail->value }}"
                                                                            data-span="inmail_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Discover</h4>
                                                                        Change the number of pages per day that the tool
                                                                        will
                                                                        discover from the search results. Recommend range is
                                                                        between 40 and 60
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="discover">Discover:
                                                                            <span
                                                                                id="discover_val">{{ $discover->value }}</span></label>
                                                                        <input type="range" name="discover"
                                                                            id="discover" min="0" max="100"
                                                                            step="1" value="{{ $discover->value }}"
                                                                            data-span="discover_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Email</h4>
                                                                        Change daily limit for sending email messages. If
                                                                        your
                                                                        account is new, or haven't been active for some time
                                                                        we
                                                                        advise to start with lower limits than maximum,
                                                                        otherwise set limits of 20 and 40.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="email_message">Email:
                                                                            <span
                                                                                id="email_message_val">{{ $email_message->value }}</span></label>
                                                                        <input type="range" name="email_message"
                                                                            id="email_message" min="0"
                                                                            max="100" step="1"
                                                                            value="{{ $email_message->value }}"
                                                                            data-span="email_message_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                                <div class="row grey_box d-flex align-items-center">
                                                                    <div class="eye_img col-lg-1">
                                                                        <img src="{{ asset('assets/img/eye.png') }}"
                                                                            alt="">
                                                                    </div>
                                                                    <div class="cont col-lg-7">
                                                                        <h4>Email delay</h4>
                                                                        The minimum number of minutes between the emails are
                                                                        being sent from the tool. For examle, if set to 5,
                                                                        the
                                                                        email is going to be sent each 5 minutes from the
                                                                        tool.
                                                                        The recommended value is 5 or more.
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label for="email_delay">Email Delay:
                                                                            <span
                                                                                id="email_delay_val">{{ $email_delay->value }}</span></label>
                                                                        <input type="range" name="email_delay"
                                                                            id="email_delay" min="0"
                                                                            max="60" step="1"
                                                                            value="{{ $email_delay->value }}"
                                                                            data-span="email_delay_val"
                                                                            class="global_limit_ranges">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="submit"
                                                                class="text-left crt_btn edit_able_btn theme_btn manage_member mt-5">
                                                                Save Changes
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                                @if (session('manage_account_health') === 'view_only')
                                                    <div class="tab-pane linkedin_pane health_tab {{ $manage_global_limits ? '' : 'active' }}"
                                                        id="health" role="tabpanel">
                                                        <div class="account_health">
                                                            <div class="grey_box d-flex align-items-center">
                                                                <div class="cont">
                                                                    <h4>Choose how many pending connections you want to have
                                                                    </h4>
                                                                    If you have too many pending invitations, you may not be
                                                                    able to
                                                                    invite more people to connect.
                                                                </div>
                                                                <div>
                                                                    <label for="pending_connections">Pending
                                                                        Connections:
                                                                        <span
                                                                            id="pending_connection_val">{{ $pending_connections->value }}</span></label>
                                                                    <input type="range" name="pending_connections"
                                                                        id="pending_connections" min="0"
                                                                        max="1100" step="10"
                                                                        value="{{ $pending_connections->value }}"
                                                                        @disabled(true)>
                                                                </div>
                                                            </div>
                                                            <div class="grey_box d-flex align-items-center">
                                                                <div class="cont">
                                                                    <h4>
                                                                        Automatically delete oldest pending invitations to
                                                                        keep count less than 1100
                                                                    </h4>
                                                                    If you have too many pending invitations, you may not be
                                                                    able to invite more people to connect.
                                                                </div>
                                                                <div class="switch_box">
                                                                    <input type="checkbox" class="switch" id="switch0"
                                                                        {{ $oldest_pending_invitations->value == 0 ? '' : 'checked' }}
                                                                        readonly @disabled(true)>
                                                                    <label for="switch0">Toggle</label>
                                                                </div>
                                                            </div>
                                                            <div class="grey_box d-flex align-items-center">
                                                                <div class="cont">
                                                                    <h4>Run on weekends</h4>
                                                                    Choose if you want actions to be taken over the weekend
                                                                </div>
                                                                <div class="switch_box">
                                                                    <input type="checkbox" class="switch" id="switch1"
                                                                        {{ $run_on_weekends->value == 0 ? '' : 'checked' }}
                                                                        readonly @disabled(true)>
                                                                    <label for="switch1">Toggle</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif (session('manage_account_health') === true)
                                                    <div class="tab-pane linkedin_pane health_tab {{ $manage_global_limits ? '' : 'active' }}"
                                                        id="health" role="tabpanel">
                                                        <div class="account_health">
                                                            <form
                                                                action="{{ route('updateAccountHealth', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="grey_box d-flex align-items-center">
                                                                    <div class="cont">
                                                                        <h4>Choose how many pending connections you want to
                                                                            have
                                                                        </h4>
                                                                        If you have too many pending invitations, you may
                                                                        not be
                                                                        able to
                                                                        invite more people to connect.
                                                                    </div>
                                                                    <div>
                                                                        <label for="pending_connections">Pending
                                                                            Connections:
                                                                            <span
                                                                                id="pending_connection_val">{{ $pending_connections->value }}</span></label>
                                                                        <input type="range" name="pending_connections"
                                                                            id="pending_connections" min="0"
                                                                            max="1100" step="10"
                                                                            value="{{ $pending_connections->value }}">
                                                                    </div>
                                                                </div>
                                                                <div class="grey_box d-flex align-items-center">
                                                                    <div class="cont">
                                                                        <h4>
                                                                            Automatically delete oldest pending invitations
                                                                            to
                                                                            keep count less than 1100
                                                                        </h4>
                                                                        If you have too many pending invitations, you may
                                                                        not be
                                                                        able to invite more people to connect.
                                                                    </div>
                                                                    <div class="switch_box">
                                                                        <input type="checkbox"
                                                                            name="oldest_pending_invitations"
                                                                            class="switch" id="switch0"
                                                                            {{ $oldest_pending_invitations->value == 1 ? 'checked' : '' }}>
                                                                        <label for="switch0">Toggle</label>
                                                                    </div>
                                                                </div>
                                                                <div class="grey_box d-flex align-items-center">
                                                                    <div class="cont">
                                                                        <h4>Run on weekends</h4>
                                                                        Choose if you want actions to be taken over the
                                                                        weekend
                                                                    </div>
                                                                    <div class="switch_box">
                                                                        <input type="checkbox" name="run_on_weekends"
                                                                            class="switch" id="switch1"
                                                                            {{ $run_on_weekends->value == 1 ? 'checked' : '' }}>
                                                                        <label for="switch1">Toggle</label>
                                                                    </div>
                                                                </div>
                                                                <button type="submit"
                                                                    class="text-left crt_btn edit_able_btn theme_btn manage_member mt-5">
                                                                    Save Changes
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (session('manage_linkedin_integrations') === 'view_only')
                                                    <div class="tab-pane linkedin_pane integrations_tab {{ $manage_global_limits || $manage_global_health ? '' : 'active' }}"
                                                        id="integrations" role="tabpanel">
                                                        @if (session()->has('seat_linkedin') && session()->has('linkedin_profile'))
                                                            @php
                                                                $account = session('seat_linkedin');
                                                                $account_profile = session('linkedin_profile');
                                                            @endphp
                                                            <h4>Connected LinkedIn account</h4>
                                                            <div class="grey_box d-flex align-items-center">
                                                                <div class="linked">
                                                                    <div class="cont">
                                                                        <i class="fa-brands fa-linkedin"></i>
                                                                        <div class="head_cont">
                                                                            <span class="head">LinkedIn</span>
                                                                            <span>Connected account:
                                                                                {{ $account['connection_params']['im']['username'] }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if ($account['sources'][0]['status'] == 'OK')
                                                                    <div class="con">Status: Connected</div>
                                                                @else
                                                                    <div class="con">Status: Not Connected</div>
                                                                @endif
                                                            </div>
                                                            <div class="grey_box d-flex align-items-center">
                                                                <h6>LinkedIn subscription</h6>
                                                                <div class="radio-buttons">
                                                                    <label for="premium">
                                                                        <input type="radio"
                                                                            name="linkedinSubscription[]"
                                                                            id="premium"
                                                                            value="premium"
                                                                            {{
                                                                                ($account_profile['premium']
                                                                                &&
                                                                                !isset($account_profile['sales_navigator'])
                                                                                &&
                                                                                !isset($account_profile['recruiter']))
                                                                                ?
                                                                                'checked'
                                                                                :
                                                                                ''
                                                                            }}
                                                                        >
                                                                        <span></span>
                                                                        LinkedIn Premium
                                                                    </label>
                                                                    <label for="salesNavigator">
                                                                        <input type="radio"
                                                                            name="linkedinSubscription[]"
                                                                            id="salesNavigator"
                                                                            value="salesNavigator"
                                                                            {{ 
                                                                                (isset($account_profile['sales_navigator'])
                                                                                &&
                                                                                !isset($account_profile['recruiter']))
                                                                                ?
                                                                                'checked'
                                                                                :
                                                                                ''
                                                                            }}
                                                                        >
                                                                        <span></span>
                                                                        Sales Navigator
                                                                    </label>
                                                                    <label for="recruiter">
                                                                        <input type="radio"
                                                                            name="linkedinSubscription[]"
                                                                            id="recruiter"
                                                                            value="recruiter"
                                                                            {{ isset($account_profile['recruiter']) ? 'checked' : '' }}>
                                                                        <span></span>
                                                                        LinkedIn Recruiter
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="grey_box d-flex align-items-center linked">
                                                                <div style="width: 50%; margin: 0 auto;"
                                                                    class="empty_blacklist text-center">
                                                                    <img style="margin-right: 0px; width: 50%; height: 50%; border-radius: 0;"
                                                                        src="{{ asset('assets/img/empty.png') }}"
                                                                        alt="">
                                                                    <p style="margin-top: 25px; font-size: 18px;">
                                                                        You can not integrate Linkedin account
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @elseif (session('manage_linkedin_integrations') === true)
                                                    <div class="tab-pane linkedin_pane integrations_tab {{ $manage_global_limits || $manage_global_health ? '' : 'active' }}"
                                                        id="integrations" role="tabpanel">
                                                        @if (session()->has('seat_linkedin') && session()->has('linkedin_profile'))
                                                            @php
                                                                $account = session('seat_linkedin');
                                                                $account_profile = session('linkedin_profile');
                                                            @endphp
                                                            <h4>Connected LinkedIn account</h4>
                                                            <div class="grey_box d-flex align-items-center">
                                                                <div class="linked">
                                                                    <div class="cont">
                                                                        <i class="fa-brands fa-linkedin"></i>
                                                                        <div class="head_cont">
                                                                            <span class="head">LinkedIn</span>
                                                                            <span>Connected account:
                                                                                {{ $account['connection_params']['im']['username'] }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if ($account['sources'][0]['status'] == 'OK')
                                                                    <div class="con">Status: Connected</div>
                                                                    <div class="add_btn">
                                                                        <a href="javascript:;" class="disconnect_account"
                                                                            type="button" id="disconnect_account">
                                                                            <img class="img-fluid"
                                                                                src="{{ asset('assets/img/disconnect.png') }}"
                                                                                alt="">
                                                                        </a>
                                                                        Disconnect
                                                                    </div>
                                                                @else
                                                                    <div class="con">Status: Not Connected</div>
                                                                @endif
                                                            </div>
                                                            <div class="grey_box d-flex align-items-center">
                                                                <h6>LinkedIn subscription</h6>
                                                                <div class="radio-buttons">
                                                                    <label for="premium">
                                                                        <input type="radio"
                                                                            name="linkedinSubscription[]"
                                                                            id="premium"
                                                                            value="premium"
                                                                            {{
                                                                                ($account_profile['premium']
                                                                                &&
                                                                                !isset($account_profile['sales_navigator'])
                                                                                &&
                                                                                !isset($account_profile['recruiter']))
                                                                                ?
                                                                                'checked'
                                                                                :
                                                                                ''
                                                                            }}
                                                                        >
                                                                        <span></span>
                                                                        LinkedIn Premium
                                                                    </label>
                                                                    <label for="salesNavigator">
                                                                        <input type="radio"
                                                                            name="linkedinSubscription[]"
                                                                            id="salesNavigator"
                                                                            value="salesNavigator"
                                                                            {{ 
                                                                                (isset($account_profile['sales_navigator'])
                                                                                &&
                                                                                !isset($account_profile['recruiter']))
                                                                                ?
                                                                                'checked'
                                                                                :
                                                                                ''
                                                                            }}
                                                                        >
                                                                        <span></span>
                                                                        Sales Navigator
                                                                    </label>
                                                                    <label for="recruiter">
                                                                        <input type="radio"
                                                                            name="linkedinSubscription[]"
                                                                            id="recruiter"
                                                                            value="recruiter"
                                                                            {{ isset($account_profile['recruiter']) ? 'checked' : '' }}>
                                                                        <span></span>
                                                                        LinkedIn Recruiter
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="grey_box d-flex align-items-center justify-content-between">
                                                                <h4 style="margin-bottom: 0;">Connect your LinkedIn account
                                                                </h4>
                                                                <button style="margin-right: 0; margin-left: auto;"
                                                                    id="submit-btn" type="button" class="theme_btn">
                                                                    Connect Linked in
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if ($is_email_settings)
                                        @if (session('manage_email_settings') === 'view_only')
                                            <div class="tab-pane setting_pane email_setting {{ $is_linkedin_settings ? '' : 'active' }}"
                                                id="emailSetting" role="tabpanel">
                                                <div class="filtr_desc">
                                                    <div class="d-flex justify-content-end">
                                                        <strong></strong>
                                                        <div class="filter">
                                                            <div class="search-form">
                                                                <input type="text" name="q"
                                                                    placeholder="Search Emails here..."
                                                                    id="search_emails">
                                                                <button type="submit">
                                                                    <i class="fa fa-search"></i>
                                                                </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <table class="data_table w-100">
                                                        <thead>
                                                            <tr>
                                                                <th width="33%" style="text-align: center">Name</th>
                                                                <th width="33%" style="text-align: center">Email</th>
                                                                <th width="33%" style="text-align: center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if ($emails->isNotEmpty())
                                                                @php
                                                                    $logos = [
                                                                        'OUTLOOK' => '/assets/img/outlook.png',
                                                                        'GMAIL' => '/assets/img/gmail.png',
                                                                    ];
                                                                @endphp
                                                                @foreach ($emails as $email)
                                                                    <tr class="table_rows"
                                                                        id="{{ 'table_row_' . $email['id'] }}">
                                                                        <td width="33%" style="text-align: center">
                                                                            @php
                                                                                $name =
                                                                                    isset(
                                                                                        $email['profile']['aliases'][0][
                                                                                            'display_name'
                                                                                        ],
                                                                                    ) &&
                                                                                    $email['profile']['aliases'][0][
                                                                                        'display_name'
                                                                                    ] !== ''
                                                                                        ? $email['profile'][
                                                                                            'aliases'
                                                                                        ][0]['display_name']
                                                                                        : (isset(
                                                                                            $email['profile'][
                                                                                                'display_name'
                                                                                            ],
                                                                                        ) &&
                                                                                        $email['profile'][
                                                                                            'display_name'
                                                                                        ] !== ''
                                                                                            ? $email['profile'][
                                                                                                'display_name'
                                                                                            ]
                                                                                            : $email['profile'][
                                                                                                    'email'
                                                                                                ] ??
                                                                                                $email['account'][
                                                                                                    'name'
                                                                                                ]);
                                                                            @endphp
                                                                            {{ $name }}
                                                                        </td>
                                                                        <td width="33%" style="text-align: center">
                                                                            <img src="{{ asset($logos[$email['profile']['provider']]) }}"
                                                                                style="width: 25px; height: 25px; margin-right: 7px;"
                                                                                alt="">
                                                                            @php
                                                                                $user_email =
                                                                                    $email['profile']['email'] ??
                                                                                    $email['account']['name'];
                                                                            @endphp
                                                                            {{ $user_email }}
                                                                        </td>
                                                                        <td class="email_status" width="33%"
                                                                            style="text-align: center; position: relative; z-index: 1;">
                                                                            @php
                                                                                $status =
                                                                                    $email['account']['sources'][0][
                                                                                        'status'
                                                                                    ] ?? 'Disconnected';
                                                                            @endphp
                                                                            <span style="margin-right: 20px;"
                                                                                class="{{ $status == 'OK' ? 'connected' : 'disconnected' }}">
                                                                                {{ $status == 'OK' ? 'Connected' : 'Disconnected' }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <td colspan="5">
                                                                    <div class="grey_box d-flex align-items-center linked">
                                                                        <div style="width: 50%; margin: 0 auto;"
                                                                            class="empty_blacklist text-center">
                                                                            <img style="margin-right: 0px; width: 50%; height: 50%; border-radius: 0;"
                                                                                src="{{ asset('assets/img/empty.png') }}"
                                                                                alt="">
                                                                            <p style="margin-top: 25px; font-size: 18px;">
                                                                                You can not integrate Email account
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @elseif (session('manage_email_settings') === true)
                                            <div class="tab-pane setting_pane email_setting {{ $is_linkedin_settings ? '' : 'active' }}"
                                                id="emailSetting" role="tabpanel">
                                                <div class="filtr_desc">
                                                    <div class="d-flex justify-content-end">
                                                        <strong></strong>
                                                        <div class="filter">
                                                            <div class="search-form">
                                                                <input type="text" name="q"
                                                                    placeholder="Search Emails here..."
                                                                    id="search_emails">
                                                                <button type="submit">
                                                                    <i class="fa fa-search"></i>
                                                                </button>
                                                                </form>
                                                            </div>
                                                            <div style="cursor: pointer;" class="add_btn "
                                                                data-bs-toggle="modal" data-bs-target="#add_email">
                                                                <a href="javascript:;" class="" type="button"><i
                                                                        class="fa-solid fa-plus"></i></a>Add email account
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table class="data_table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th width="33%" style="text-align: center">Name</th>
                                                            <th width="33%" style="text-align: center">Email</th>
                                                            <th width="33%" style="text-align: center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if ($emails->isNotEmpty())
                                                            @php
                                                                $logos = [
                                                                    'OUTLOOK' => '/assets/img/outlook.png',
                                                                    'GMAIL' => '/assets/img/gmail.png',
                                                                ];
                                                            @endphp
                                                            @foreach ($emails as $email)
                                                                <tr class="table_rows"
                                                                    id="{{ 'table_row_' . $email['id'] }}">
                                                                    <td width="33%" style="text-align: center">
                                                                        @php
                                                                            $name =
                                                                                isset(
                                                                                    $email['profile']['aliases'][0][
                                                                                        'display_name'
                                                                                    ],
                                                                                ) &&
                                                                                $email['profile']['aliases'][0][
                                                                                    'display_name'
                                                                                ] !== ''
                                                                                    ? $email['profile']['aliases'][0][
                                                                                        'display_name'
                                                                                    ]
                                                                                    : (isset(
                                                                                        $email['profile'][
                                                                                            'display_name'
                                                                                        ],
                                                                                    ) &&
                                                                                    $email['profile'][
                                                                                        'display_name'
                                                                                    ] !== ''
                                                                                        ? $email['profile'][
                                                                                            'display_name'
                                                                                        ]
                                                                                        : $email['profile']['email'] ??
                                                                                            $email['account']['name']);
                                                                        @endphp
                                                                        {{ $name }}
                                                                    </td>
                                                                    <td width="33%" style="text-align: center">
                                                                        <img src="{{ asset($logos[$email['profile']['provider']]) }}"
                                                                            style="width: 25px; height: 25px; margin-right: 7px;"
                                                                            alt="">
                                                                        @php
                                                                            $user_email =
                                                                                $email['profile']['email'] ??
                                                                                $email['account']['name'];
                                                                        @endphp
                                                                        {{ $user_email }}
                                                                    </td>
                                                                    <td class="email_status" width="33%"
                                                                        style="text-align: center; position: relative; z-index: 1;">
                                                                        @php
                                                                            $status =
                                                                                $email['account']['sources'][0][
                                                                                    'status'
                                                                                ] ?? 'Disconnected';
                                                                        @endphp
                                                                        <span style="margin-right: 20px;"
                                                                            class="{{ $status == 'OK' ? 'connected' : 'disconnected' }}">
                                                                            {{ $status == 'OK' ? 'Connected' : 'Disconnected' }}
                                                                        </span>
                                                                        <span class="email_menu_btn"
                                                                            style="width: 20px; display: inline-block; text-align: center;">
                                                                            <i class="fa-solid fa-ellipsis-vertical"
                                                                                style="color: #ffffff;"></i>
                                                                        </span>
                                                                        <ul class="setting_list"
                                                                            style="display: none; z-index: 2147483647; right: -5%; width: max-content;">
                                                                            <li><a class="delete_an_email"
                                                                                    id="{{ $email['id'] }}">Delete an
                                                                                    account</a>
                                                                            </li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <td colspan="5">
                                                                <div class="grey_box">
                                                                    <div class="add_cont">
                                                                        <p>No email account. Start by connecting your first
                                                                            email
                                                                            account.</p>
                                                                        <div class="add">
                                                                            <a href="javascript:;" type="button"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#add_email"><i
                                                                                    class="fa-solid fa-plus"></i></a>Add
                                                                            email account
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (session('manage_email_settings') === true)
        <div class="modal fade create_sequence_modal add_email" id="add_email" tabindex="-1"
            aria-labelledby="add_email" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sequance_modal">Add email account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="modal-body">
                        <form action="">
                            <div class="row">
                                <div class="col-12">
                                    <p>Select your email provider</p>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="border_box">
                                                <div class="email_box_img add_an_email" data-provider="GOOGLE">
                                                    <img src="{{ asset('assets/img/gmail.png') }}" alt="">
                                                    <span>Gmail</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="border_box">
                                                <div class="email_box_img add_an_email" data-provider="OUTLOOK">
                                                    <img src="{{ asset('assets/img/outlook.png') }}" alt="">
                                                    <span>Outlook</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{ session()->forget('add_account') }}
    <script>
        var integrateLinkedinRoute =
            "{{ route('createLinkedinAccount', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}";
        var integrateEmailroute =
            "{{ route('createEmailAccount', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}";
        var disconnectLinkedinAccountRoute =
            "{{ route('disconnectLinkedinAccount', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}";
        var disconnectEmailAccountRoute =
            "{{ route('disconnectEmailAccount', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'email_id' => ':email_id']) }}";
        var searchEmailAccountRoute =
            "{{ route('searchEmailAccount', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'search' => ':search']) }}"
        var emptyImage = "{{ asset('assets/img/empty.png') }}";
        var manage_email_allowed = "{{ session('manage_email_settings') === true }}";
    </script>
    <script>
        var addAccountAjax = null;
        $(document).ready(function() {
            $(document).on("click", function(e) {
                if (!$(e.target).closest(".setting").length) {
                    $(".setting_list").hide();
                }
            });
        });
    </script>
@endsection
