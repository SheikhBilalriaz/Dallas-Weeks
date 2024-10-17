@extends('back/partials/header')
@section('content')
    <script src="{{ asset('assets/js/editCampaignInfo.js') }}"></script>
    <section class="main_dashboard blacklist  campaign_sec">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                @php
                    $campaign_details_json = json_encode($campaign_details);
                @endphp
                <div class="col-lg-11 col-sm-12">
                    <div class="row crt_cmp_r">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="cont">
                                    <h3>Campaigns</h3>
                                    <p>Choose between options and get your campaign running</p>
                                </div>
                                <div class="cmp_opt_link d-flex">
                                    <ul class="d-flex list-unstyled justify-content-end align-items-center">
                                        <li class="active prev full"><span>1</span><a href="javascript:;">Campaign info</a>
                                        </li>
                                        <li class="active "><span>2</span><a href="javascript:;">Campaign settings</a></li>
                                        <li><span>3</span><a href="javascript:;">Campaign steps</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row insrt_cmp_r">
                        <div class="border_box">
                            <div class="comp_tabs">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <button class="nav-link active" id="nav-email-tab" data-bs-toggle="tab"
                                            data-bs-target="#nav-email" type="button" role="tab"
                                            aria-controls="nav-email" aria-selected="true">Email settings</button>
                                        <button class="nav-link" id="nav-linkedin-tab" data-bs-toggle="tab"
                                            data-bs-target="#nav-linkedin" type="button" role="tab"
                                            aria-controls="nav-linkedin" aria-selected="false">LinkedIn settings</button>
                                        <button class="nav-link" id="nav-global-tab" data-bs-toggle="tab"
                                            data-bs-target="#nav-global" type="button" role="tab"
                                            aria-controls="nav-global" aria-selected="false">Global settings</button>
                                    </div>
                                </nav>
                                <form id="settings" method="POST"
                                    action="{{ route('updateCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'campaign_id' => $campaign_id]) }}">
                                    @csrf
                                    <div class="tab-content" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="nav-email" role="tabpanel"
                                            aria-labelledby="nav-email-tab">
                                            <div class="accordion" id="accordionExample">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingOne">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            Email accounts to use for this campaign
                                                        </button>
                                                    </h2>
                                                    <div id="collapseOne" class="accordion-collapse collapse"
                                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            @if ($emails->isNotEmpty())
                                                                @php
                                                                    $logos = [
                                                                        'OUTLOOK' => '/assets/img/outlook.png',
                                                                        'GMAIL' => '/assets/img/gmail.png',
                                                                    ];
                                                                @endphp
                                                                <ul class="email_list">
                                                                    @foreach ($emails as $email)
                                                                        <li>
                                                                            <div class="row email_list">
                                                                                <div class="col-lg-1 schedule_item">
                                                                                    @foreach ($email_settings as $setting)
                                                                                        @if ($setting['setting_slug'] == 'email_settings_email_id')
                                                                                            <input type="radio"
                                                                                                name="{{ 'email_settings_' . $setting['id'] }}"
                                                                                                class="email_id"
                                                                                                value="{{ $email['id'] }}"
                                                                                                {{ $email['id'] == $setting['value'] ? 'checked' : '' }}>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </div>
                                                                                <div class="col-lg-3 schedule_name">
                                                                                    @php
                                                                                        $name =
                                                                                            isset(
                                                                                                $email['profile'][
                                                                                                    'aliases'
                                                                                                ][0]['display_name'],
                                                                                            ) &&
                                                                                            $email['profile'][
                                                                                                'aliases'
                                                                                            ][0]['display_name'] !== ''
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
                                                                                                        $email[
                                                                                                            'account'
                                                                                                        ]['name']);
                                                                                    @endphp
                                                                                    <span>{{ $name }}</span>
                                                                                </div>
                                                                                <div class="col-lg-5">
                                                                                    <img src="{{ asset($logos[$email['profile']['provider']]) }}"
                                                                                        style="width: 25px; height: 25px; margin-right: 7px;"
                                                                                        alt="">
                                                                                    @php
                                                                                        $user_email =
                                                                                            $email['profile'][
                                                                                                'email'
                                                                                            ] ??
                                                                                            $email['account']['name'];
                                                                                    @endphp
                                                                                    {{ $user_email }}
                                                                                </div>
                                                                                @php
                                                                                    $status =
                                                                                        $email['account']['sources'][0][
                                                                                            'status'
                                                                                        ] ?? 'Disconnected';
                                                                                @endphp
                                                                                <div class="col-lg-3 email_status">
                                                                                    <div
                                                                                        class="{{ $status == 'OK' ? 'connected' : 'disconnected' }}">
                                                                                        {{ $status == 'OK' ? 'Connected' : 'Disconnected' }}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <div class="email_setting">
                                                                    <div class="grey_box">
                                                                        <div class="add_cont">
                                                                            <p>No email account. Start by connecting your
                                                                                first
                                                                                email
                                                                                account.</p>
                                                                            @if (session('manage_email_settings') === true)
                                                                                <div class="add">
                                                                                    <a href="{{ route('seatSettingPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                                                                                        type="button">
                                                                                        <i class="fa-solid fa-plus"></i>
                                                                                    </a> Add email account
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingTwo">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                            aria-expanded="false" aria-controls="collapseTwo">
                                                            Schedule email
                                                        </button>
                                                    </h2>
                                                    <div id="collapseTwo" class="accordion-collapse collapse"
                                                        aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            <div class="schedule-tab">
                                                                <button class="schedule-btn active"
                                                                    id="my_email_schedule_btn"
                                                                    data-tab="my_email_schedule">My Schedules</button>
                                                            </div>
                                                            <div class="active schedule-content" id="my_email_schedule">
                                                                <div class="schedule_content_row1">
                                                                    <p>Manage your schedules.</p>
                                                                    <button href="javascript:;" type="button"
                                                                        class="btn" data-bs-toggle="modal"
                                                                        data-bs-target="#schedule_modal">Create
                                                                        Schedule</button>
                                                                </div>
                                                                <div class="schedule_content_row2">
                                                                    <input type="text"
                                                                        placeholder="Search schedules here..."
                                                                        class="search_schedule">
                                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                                </div>
                                                                @if ($campaign_schedule->isNotEmpty())
                                                                    <ul class="schedule_list" id="schedule_list_1">
                                                                        @foreach ($campaign_schedule as $schedule)
                                                                            <li>
                                                                                <div class="row schedule_list_item">
                                                                                    <div class="col-lg-1 schedule_item">
                                                                                        @foreach ($email_settings as $setting)
                                                                                            @if ($setting['setting_slug'] == 'email_settings_schedule_id')
                                                                                                <input type="radio"
                                                                                                    name="{{ 'email_settings_' . $setting['id'] }}"
                                                                                                    class="schedule_id"
                                                                                                    value="{{ $schedule['id'] }}"
                                                                                                    {{ $schedule['id'] == $setting['value'] ? 'checked' : '' }}>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </div>
                                                                                    <div class="col-lg-3 schedule_name">
                                                                                        <span>{{ $schedule['name'] }}</span>
                                                                                    </div>
                                                                                    <div class="col-lg-6 schedule_days">
                                                                                        @php
                                                                                            $schedule_days = App\Models\Schedule_Day::where(
                                                                                                'schedule_id',
                                                                                                $schedule['id'],
                                                                                            )
                                                                                                ->orderBy('id')
                                                                                                ->get();
                                                                                        @endphp
                                                                                        <ul class="schedule_day_list">
                                                                                            @foreach ($schedule_days as $day)
                                                                                                <li
                                                                                                    class="schedule_day {{ $day['is_active'] == '1' ? 'selected_day' : '' }}">
                                                                                                    {{ ucfirst($day['day']) }}
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingThree">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseThree"
                                                            aria-expanded="false" aria-controls="collapseThree">
                                                            Email tracking preference
                                                        </button>
                                                    </h2>
                                                    <div id="collapseThree" class="accordion-collapse collapse"
                                                        aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            @foreach ($email_settings as $setting)
                                                                @if ($setting['setting_slug'] != 'email_settings_schedule_id' && $setting['setting_slug'] != 'email_settings_email_id')
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> {{ str_replace('Email Settings ', '', $setting['setting_name']) }}
                                                                        </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                name="{{ 'email_settings_' . $setting['id'] }}"
                                                                                class="linkedin_setting_switch"
                                                                                id="{{ str_replace('email_settings_', '', $setting['setting_slug']) }}"
                                                                                {{ $setting['value'] == 'yes' ? 'checked' : '' }}><label
                                                                                for="{{ str_replace('email_settings_', '', $setting['setting_slug']) }}">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cmp_btns d-flex justify-content-center align-items-center">
                                                <a href="{{ route('editCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'campaign_id' => $campaign_id]) }}"
                                                    class="btn"><i class="fa-solid fa-arrow-left"></i>Back</a>
                                                <a href="javascript:;" class="btn next_tab nxt_btn">Next<i
                                                        class="fa-solid fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="nav-linkedin" role="tabpanel"
                                            aria-labelledby="nav-linkedin-tab">
                                            @foreach ($linkedin_settings as $setting)
                                                @if ($setting['setting_slug'] != 'linkedin_settings_schedule_id')
                                                    <div class="linked_set d-flex justify-content-between">
                                                        <p> {{ str_replace('Linkedin Settings ', '', $setting['setting_name']) }}
                                                        </p>
                                                        <div class="switch_box"><input type="checkbox"
                                                                name="{{ 'linkedin_settings_' . $setting['id'] }}"
                                                                class="linkedin_setting_switch"
                                                                id="{{ str_replace('linkedin_settings_', '', $setting['setting_slug']) }}"
                                                                {{ $setting['value'] == 'yes' ? 'checked' : '' }}><label
                                                                for="{{ str_replace('linkedin_settings_', '', $setting['setting_slug']) }}">Toggle</label>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            <div class="cmp_btns d-flex justify-content-center align-items-center">
                                                <a href="javascript:;" class="btn prev_tab"><i
                                                        class="fa-solid fa-arrow-left"></i>Back</a>
                                                <a href="javascript:;" class="btn next_tab nxt_btn">Next<i
                                                        class="fa-solid fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="nav-global" role="tabpanel"
                                            aria-labelledby="nav-global-tab">
                                            <div class="accordion" id="accordionExample">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingOne">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapse1"
                                                            aria-expanded="true" aria-controls="collapse1">
                                                            Targeting options
                                                        </button>
                                                    </h2>
                                                    <div id="collapse1" class="accordion-collapse collapse"
                                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            @foreach ($global_settings as $setting)
                                                                @if ($setting['setting_slug'] != 'global_settings_schedule_id')
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> {{ str_replace('Linkedin Settings ', '', $setting['setting_name']) }}
                                                                        </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                name="{{ 'global_settings_' . $setting['id'] }}"
                                                                                class="linkedin_setting_switch"
                                                                                id="{{ str_replace('global_settings_', '', $setting['setting_slug']) }}"
                                                                                {{ $setting['value'] == 'yes' ? 'checked' : '' }}><label
                                                                                for="{{ str_replace('global_settings_', '', $setting['setting_slug']) }}">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingTwo">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapse2"
                                                            aria-expanded="false" aria-controls="collapse2">
                                                            Schedule campaign
                                                        </button>
                                                    </h2>
                                                    <div id="collapse2" class="accordion-collapse collapse"
                                                        aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            <div class="schedule-tab">
                                                                <button class="schedule-btn active"
                                                                    id="my_campaign_schedule_btn"
                                                                    data-tab="my_campaign_schedule">My Schedules</button>
                                                            </div>
                                                            <div class="active schedule-content"
                                                                id="my_campaign_schedule">
                                                                <div class="schedule_content_row1">
                                                                    <p>Manage your schedules.</p>
                                                                    <button href="javascript:;" type="button"
                                                                        class="btn" data-bs-toggle="modal"
                                                                        data-bs-target="#schedule_modal">Create
                                                                        Schedule</button>
                                                                </div>
                                                                <div class="schedule_content_row2">
                                                                    <input type="text"
                                                                        placeholder="Search schedules here..."
                                                                        class="search_schedule">
                                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                                </div>
                                                                @if (!empty($campaign_schedule))
                                                                    <ul class="schedule_list" id="schedule_list_2">
                                                                        @foreach ($campaign_schedule as $schedule)
                                                                            <li>
                                                                                <div class="row schedule_list_item">
                                                                                    <div class="col-lg-1 schedule_item">
                                                                                        @foreach ($global_settings as $setting)
                                                                                            @if ($setting['setting_slug'] == 'global_settings_schedule_id')
                                                                                                <input type="radio"
                                                                                                    name="{{ 'global_settings_' . $setting['id'] }}"
                                                                                                    class="schedule_id"
                                                                                                    value="{{ $schedule['id'] }}"
                                                                                                    {{ $schedule['id'] == $setting['value'] ? 'checked' : '' }}>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </div>
                                                                                    <div class="col-lg-3 schedule_name">
                                                                                        <span>{{ $schedule['name'] }}</span>
                                                                                    </div>
                                                                                    <div class="col-lg-6 schedule_days">
                                                                                        @php
                                                                                            $schedule_days = App\Models\Schedule_Day::where(
                                                                                                'schedule_id',
                                                                                                $schedule['id'],
                                                                                            )
                                                                                                ->orderBy('id')
                                                                                                ->get();
                                                                                        @endphp
                                                                                        <ul class="schedule_day_list">
                                                                                            @foreach ($schedule_days as $day)
                                                                                                <li
                                                                                                    class="schedule_day {{ $day['is_active'] == '1' ? 'selected_day' : '' }}">
                                                                                                    {{ ucfirst($day['day']) }}
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cmp_btns d-flex justify-content-center align-items-center">
                                                <a href="javascript:;" class="btn prev_tab"><i
                                                        class="fa-solid fa-arrow-left"></i>Back</a>
                                                <a id="create_sequence" type="button" class="btn nxt_btn">Save Changes<i class="fa-solid fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade create_schedule_modal" id="schedule_modal" tabindex="-1" aria-labelledby="schedule_modal"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schedule_modal">Create a Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form class="modal-body schedule_form">
                    <div class="row schedule_name">
                        <label class="col-lg-4 schedule_name_label" for="schedule_name">Schedule Name</label>
                        <input class="col-lg-8 schedule_name_input" type="text" name="schedule_name" id="">
                    </div>
                    <ul class="schedule_days">
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input checked="" type="checkbox"
                                        class="schedule_days" name="mon_selected_day" class="" value="mon">
                                </div>
                                <div class="col-lg-4 day_name">Monday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" value="09:00:00"
                                        name="mon_start_time" id="mon_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" value="17:00:00"
                                        name="mon_end_time" id="mon_end_time"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input checked="" type="checkbox"
                                        class="schedule_days" name="tue_selected_day" class="" value="tue">
                                </div>
                                <div class="col-lg-4 day_name">Tuesday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" value="09:00:00"
                                        name="tue_start_time" id="tue_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" value="17:00:00"
                                        name="tue_end_time" id="tue_end_time"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input checked="" type="checkbox"
                                        class="schedule_days" name="wed_selected_day" class="" value="wed">
                                </div>
                                <div class="col-lg-4 day_name">Wednesday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" value="09:00:00"
                                        name="wed_start_time" id="wed_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" value="17:00:00"
                                        name="wed_end_time" id="wed_end_time"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input checked="" type="checkbox"
                                        class="schedule_days" name="thurs_selected_day" class="" value="thurs">
                                </div>
                                <div class="col-lg-4 day_name">Thursday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" value="09:00:00"
                                        name="thurs_start_time" id="thurs_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" value="17:00:00"
                                        name="thurs_end_time" id="thurs_end_time"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input checked="" type="checkbox"
                                        class="schedule_days" name="fri_selected_day" class="" value="fri">
                                </div>
                                <div class="col-lg-4 day_name">Friday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" value="09:00:00"
                                        name="fri_start_time" id="fri_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" value="17:00:00"
                                        name="fri_end_time" id="fri_end_time"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input type="checkbox" class="schedule_days"
                                        name="sat_selected_day" class="" value="sat"></div>
                                <div class="col-lg-4 day_name">Saturday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" name="sat_start_time"
                                        id="sat_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" name="sat_end_time"
                                        id="sat_end_time"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-lg-2 day_input"><input type="checkbox" class="schedule_days"
                                        name="sun_selected_day" class="" value="sun"></div>
                                <div class="col-lg-4 day_name">Sunday</div>
                                <div class="col-lg-3 day_start_time"><input type="time" name="sun_start_time"
                                        id="sun_start_time"></div>
                                <div class="col-lg-3 day_end_time"><input type="time" name="sun_end_time"
                                        id="sun_end_time"></div>
                            </div>
                        </li>
                    </ul>
                    <button type="button" class="btn add_schedule">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        var campaign_details = {!! $campaign_details_json !!};
        var createSchedulePath = "{{ route('createSchedule', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}";
        var filterSchedulePath =
            "{{ route('filterSchedule', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':search']) }}";
        var csrfToken = "{{ csrf_token() }}";
    </script>
@endsection
