@extends('back/partials/header')
@section('content')
    <script src="{{ asset('assets/js/leads.js') }}"></script>
    <section class="main_dashboard blacklist  campaign_sec lead_sec">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                <div class="col-lg-11 col-sm-12">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h3>Leads</h3>
                                <div class="filt_opt d-flex">
                                    <div class="filt_opt">
                                        @if (!empty($campaigns))
                                            <select name="campaign" id="campaign">
                                                <option value="all">All Campaigns</option>
                                                @foreach ($campaigns as $campaign)
                                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div class="add_btn ">
                                        <a href="javascript:;" class="" type="button" data-bs-toggle="modal"
                                            data-bs-target="#export_modal"><img class="img-fluid"
                                                src="{{ asset('assets/img/importexport.svg') }}" alt=""></a>Export
                                        from campaigns
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="filter_head_row d-flex">
                            </div>
                            <div class="filtr_desc" bis_skin_checked="1">
                                <div class="d-flex" bis_skin_checked="1">
                                    <strong>Leads</strong>
                                    <div class="filter" bis_skin_checked="1">
                                        <form action="/search" method="get" class="search-form">
                                            @csrf
                                            <input type="text" name="q" placeholder="Search Leads here..."
                                                id="search_lead">
                                            <button type="submit">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="comp_tabs">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link lead_tab active" data-toggle="tab" href="javascript:;"
                                            role="tab" data-bs-target="Leads">Leads</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link lead_tab" data-toggle="tab" href="javascript:;" role="tab"
                                            data-bs-target="Steps">Steps</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link lead_tab" data-toggle="tab" href="javascript:;" role="tab"
                                            data-bs-target="Stats">Stats</a>
                                    </li>
                                </ul><!-- Tab panes -->
                                <div class="tab-content">
                                    <!-- Leads Content -->
                                    <div class="tab-pane lead_pane active" id="Leads" role="tabpanel">
                                        <div class="border_box">
                                            <div class="scroll_div leads_list">
                                                <table class="data_table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th width="20%">Contact</th>
                                                            <th width="25%">Title/Company</th>
                                                            <th width="15%">Send Connections</th>
                                                            <th width="15%">Current step</th>
                                                            <th width="15%">Next step</th>
                                                            <th width="15%">Executed time</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if ($leads->isNotEmpty())
                                                            @foreach ($leads as $lead)
                                                                <tr>
                                                                    <td class="title_cont">{{ $lead['contact'] }}</td>
                                                                    <td class="title_comp">
                                                                        {{ $lead['title_company'] }}
                                                                    </td>
                                                                    <td class="">
                                                                        @if ($lead['send_connections'] == 'connected_not_replied')
                                                                            <div class="per connected_not_replied">
                                                                                Connected, not replied</div>
                                                                        @elseif ($lead['send_connections'] == 'profile_viewed')
                                                                            <div class="per discovered">
                                                                                Profile Viewed</div>
                                                                        @elseif ($lead['send_connections'] == 'followed')
                                                                            <div class="per discovered">
                                                                                Followed</div>
                                                                        @elseif ($lead['send_connections'] == 'messaged')
                                                                            <div class="per discovered">
                                                                                Messaged</div>
                                                                        @elseif ($lead['send_connections'] == 'replied_not_connected')
                                                                            <div class="per replied_not_connected">Replied,
                                                                                not connected</div>
                                                                        @elseif ($lead['send_connections'] == 'connection_pending')
                                                                            <div class="per connection_pending">Connection
                                                                                pending</div>
                                                                        @elseif ($lead['send_connections'] == 'connected')
                                                                            <div class="per connected_not_replied">Connected
                                                                            </div>
                                                                        @elseif ($lead['send_connections'] == 'replied')
                                                                            <div class="per replied">Replied</div>
                                                                        @elseif ($lead['send_connections'] == 'not_connected')
                                                                            <div class="per replied">Not Connected</div>
                                                                        @else
                                                                            <div class="per discovered">Discovered</div>
                                                                        @endif
                                                                    </td>
                                                                    <td
                                                                        style="color: {{ $lead['current_step'] ? '' : 'red' }}; font-weight: {{ $lead['current_step'] ? '' : 'bold' }};">
                                                                        {{ $lead['current_step'] ?? 'Step 1' }}
                                                                    </td>
                                                                    <td
                                                                        style="color: {{ $lead['next_step'] ? '' : 'green' }}; font-weight: {{ $lead['next_step'] ? '' : 'bold' }};">
                                                                        {{ $lead['next_step'] ?? 'Completed' }}
                                                                    </td>
                                                                    <td>
                                                                        <div class="">
                                                                            {{ $lead['created_at']->diffInDays(now()) }}
                                                                            days ago
                                                                        </div>
                                                                    </td>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="8" style="z-index: 99">
                                                                    <div class="text-center text-danger"
                                                                        style="font-size: 25px; font-weight: bold; font-style: italic;">
                                                                        Not Found!</div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Step Content -->
                                    <div class="tab-pane lead_pane" id="Steps" role="tabpanel">
                                        <div class="lead_step_cont">
                                            <div class="border_box">
                                                <form id="" class="lead_step_form">
                                                    <div class="row">
                                                        <div class="comp_name">
                                                            <label for="campaign-name">Campaign Name:</label>
                                                            <input type="text" id="campaign-name" name="campaign-name"
                                                                placeholder="Campaign name ex. Los angeles lead"
                                                                required="" readonly>
                                                        </div>
                                                        <div class="comp_url">
                                                            <label for="linkedin-url">Campaign URL:</label>
                                                            <input type="url" id="linkedin-url" name="linkedin-url"
                                                                placeholder="LinkedIn search URL" required="" readonly>
                                                        </div>
                                                    </div>
                                                </form>
                                                <div class="date" id="created_at">
                                                    <i class="fa-solid fa-calendar-days"></i>Created at:
                                                    {{ now() }}
                                                </div>
                                            </div>
                                            <div class="email_setting">
                                                <div class="border_box">
                                                    <h3>Email settings</h3>
                                                    <div class="accordion" id="accordion">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingOne">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseOne" aria-expanded="true"
                                                                    aria-controls="collapseOne">
                                                                    Email accounts to use for this campaign
                                                                </button>
                                                            </h2>
                                                            <div id="collapseOne" class="accordion-collapse collapse"
                                                                aria-labelledby="headingOne" data-bs-parent="#accordion">
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
                                                                                        <div
                                                                                            class="col-lg-1 schedule_item">
                                                                                            <input type="radio"
                                                                                                name="email_settings_email_id"
                                                                                                class="email_id email_settings_email_id"
                                                                                                value="{{ $email['id'] }}"
                                                                                                readonly>
                                                                                        </div>
                                                                                        <div
                                                                                            class="col-lg-3 schedule_name">
                                                                                            @php
                                                                                                $name =
                                                                                                    isset(
                                                                                                        $email[
                                                                                                            'profile'
                                                                                                        ]['aliases'][0][
                                                                                                            'display_name'
                                                                                                        ],
                                                                                                    ) &&
                                                                                                    $email['profile'][
                                                                                                        'aliases'
                                                                                                    ][0][
                                                                                                        'display_name'
                                                                                                    ] !== ''
                                                                                                        ? $email[
                                                                                                            'profile'
                                                                                                        ]['aliases'][0][
                                                                                                            'display_name'
                                                                                                        ]
                                                                                                        : (isset(
                                                                                                            $email[
                                                                                                                'profile'
                                                                                                            ][
                                                                                                                'display_name'
                                                                                                            ],
                                                                                                        ) &&
                                                                                                        $email[
                                                                                                            'profile'
                                                                                                        ][
                                                                                                            'display_name'
                                                                                                        ] !== ''
                                                                                                            ? $email[
                                                                                                                'profile'
                                                                                                            ][
                                                                                                                'display_name'
                                                                                                            ]
                                                                                                            : $email[
                                                                                                                    'profile'
                                                                                                                ][
                                                                                                                    'email'
                                                                                                                ] ??
                                                                                                                $email[
                                                                                                                    'account'
                                                                                                                ][
                                                                                                                    'name'
                                                                                                                ]);
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
                                                                                                    $email['account'][
                                                                                                        'name'
                                                                                                    ];
                                                                                            @endphp
                                                                                            {{ $user_email }}
                                                                                        </div>
                                                                                        @php
                                                                                            $status =
                                                                                                $email['account'][
                                                                                                    'sources'
                                                                                                ][0]['status'] ??
                                                                                                'Disconnected';
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
                                                                                    <p>No email account. Start by connecting
                                                                                        your
                                                                                        first
                                                                                        email
                                                                                        account.</p>
                                                                                    @if (session('manage_email_settings') === true)
                                                                                        <div class="add">
                                                                                            <a href="{{ route('seatSettingPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                                                                                                type="button">
                                                                                                <i
                                                                                                    class="fa-solid fa-plus"></i>
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
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwo" aria-expanded="false"
                                                                    aria-controls="collapseTwo">
                                                                    Schedule email
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwo" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwo" data-bs-parent="#accordion">
                                                                <div class="accordion-body">
                                                                    <div class="schedule-tab">
                                                                        <button class="schedule-btn active"
                                                                            id="my_email_schedule_btn"
                                                                            data-tab="my_email_schedule">My
                                                                            Schedules</button>
                                                                        <button class="schedule-btn "
                                                                            id="team_email_schedule_btn"
                                                                            data-tab="team_email_schedule">Team
                                                                            schedules</button>
                                                                    </div>
                                                                    <div class="active schedule-content"
                                                                        id="my_email_schedule">
                                                                        @if ($schedules->isNotEmpty())
                                                                            <ul class="schedule_list schedule_list_1"
                                                                                id="schedule_list_1">
                                                                                @foreach ($schedules as $schedule)
                                                                                    <li>
                                                                                        <div
                                                                                            class="row schedule_list_item">
                                                                                            <div
                                                                                                class="col-lg-1 schedule_item">
                                                                                                <input type="radio"
                                                                                                    name="email_settings_schedule_id"
                                                                                                    class="schedule_id email_settings_schedule_id"
                                                                                                    value="{{ $schedule['id'] }}"
                                                                                                    readonly>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-lg-3 schedule_name">
                                                                                                <span>{{ $schedule['name'] }}</span>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-lg-6 schedule_days">
                                                                                                @php
                                                                                                    $schedule_days = App\Models\Schedule_Day::where(
                                                                                                        'schedule_id',
                                                                                                        $schedule['id'],
                                                                                                    )
                                                                                                        ->orderBy('id')
                                                                                                        ->get();
                                                                                                @endphp
                                                                                                <ul
                                                                                                    class="schedule_day_list">
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
                                                                        @else
                                                                            <ul class="schedule_list schedule_list_1"
                                                                                id="schedule_list_1">
                                                                                <li class="text-center">
                                                                                    No Schedule Listed
                                                                                </li>
                                                                            </ul>
                                                                        @endif
                                                                    </div>
                                                                    <div class=" schedule-content"
                                                                        id="team_email_schedule">
                                                                        @if ($team_schedules->isNotEmpty())
                                                                            <ul class="schedule_list schedule_list_1"
                                                                                id="schedule_list_1">
                                                                                @foreach ($team_schedules as $schedule)
                                                                                    <li>
                                                                                        <div
                                                                                            class="row schedule_list_item">
                                                                                            <div
                                                                                                class="col-lg-1 schedule_item">
                                                                                                <input type="radio"
                                                                                                    name="email_settings_schedule_id"
                                                                                                    class="schedule_id email_settings_schedule_id"
                                                                                                    value="{{ $schedule['id'] }}"
                                                                                                    readonly>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-lg-3 schedule_name">
                                                                                                <span>{{ $schedule['name'] }}</span>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-lg-6 schedule_days">
                                                                                                @php
                                                                                                    $schedule_days = App\Models\Schedule_Day::where(
                                                                                                        'schedule_id',
                                                                                                        $schedule['id'],
                                                                                                    )
                                                                                                        ->orderBy('id')
                                                                                                        ->get();
                                                                                                @endphp
                                                                                                <ul
                                                                                                    class="schedule_day_list">
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
                                                                        @else
                                                                            <ul class="schedule_list schedule_list_1"
                                                                                id="schedule_list_1">
                                                                                <li class="text-center">
                                                                                    No Schedule Listed
                                                                                </li>
                                                                            </ul>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingThree">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseThree" aria-expanded="false"
                                                                    aria-controls="collapseThree">
                                                                    Email tracking preference
                                                                </button>
                                                            </h2>
                                                            <div id="collapseThree" class="accordion-collapse collapse"
                                                                aria-labelledby="headingThree"
                                                                data-bs-parent="#accordion">
                                                                <div class="accordion-body">
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> Track the number of email link clicks </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                class="switch setting_switch"
                                                                                id="email_settings_track_the_number_of_email_link_clicks"
                                                                                disabled><label
                                                                                for="email_settings_track_the_number_of_email_link_clicks">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> Track the number of opened emails </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                class="switch setting_switch"
                                                                                id="email_settings_track_the_number_of_opened_emails"
                                                                                disabled><label
                                                                                for="email_settings_track_the_number_of_opened_emails">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> Text only email (no HTML) <span
                                                                                title="Send email messages that only include text without images, graphics or formatting. If you enable this option, you won't be able to track open and link click rates.">!</span>
                                                                        </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                class="switch setting_switch"
                                                                                id="email_settings_text_only_email_no_html"
                                                                                disabled><label
                                                                                for="email_settings_text_only_email_no_html">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h3>LinkedIn settings</h3>
                                                    <div class="email_settings">
                                                        <ul class="list-unstyled p-0">
                                                            <li class="d-flex justify-content-between align-items-center ">
                                                                <span>Discover premium LinkedIn account only</span>
                                                                <div class="switch_box"><input type="checkbox"
                                                                        class="switch setting_switch"
                                                                        id="linkedin_settings_discover_premium_linked_accounts_only"
                                                                        disabled><label
                                                                        for="linkedin_settings_discover_premium_linked_accounts_only">Toggle</label>
                                                                </div>
                                                            </li>
                                                            <li class="d-flex justify-content-between align-items-center">
                                                                <span>Discover leads with open profile status only</span>
                                                                <div class="switch_box"><input type="checkbox"
                                                                        class="switch setting_switch"
                                                                        id="linkedin_settings_discover_leads_with_open_profile_status_only"
                                                                        disabled><label
                                                                        for="linkedin_settings_discover_leads_with_open_profile_status_only">Toggle</label>
                                                                </div>
                                                            </li>
                                                            <li class="d-flex justify-content-between align-items-center">
                                                                <span> Collect contact information</span>
                                                                <div class="switch_box"><input type="checkbox"
                                                                        class="switch setting_switch"
                                                                        id="linkedin_settings_collect_contact_information"
                                                                        disabled><label
                                                                        for="linkedin_settings_collect_contact_information">Toggle</label>
                                                                </div>
                                                            </li>
                                                            <li class="d-flex justify-content-between align-items-center">
                                                                <span>Remove leads with pending connection requests</span>
                                                                <div class="switch_box"><input type="checkbox"
                                                                        class="switch setting_switch"
                                                                        id="linkedin_settings_remove_leads_with_pending_connections"
                                                                        disabled><label
                                                                        for="linkedin_settings_remove_leads_with_pending_connections">Toggle</label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <hr>
                                                    <h3>Global settings</h3>
                                                    <div class="accordion" id="accordion">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingOne">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapse1"
                                                                    aria-expanded="true" aria-controls="collapse1">
                                                                    Targeting options
                                                                </button>
                                                            </h2>
                                                            <div id="collapse1" class="accordion-collapse collapse"
                                                                aria-labelledby="headingOne" data-bs-parent="#accordion">
                                                                <div class="accordion-body">
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> Include leads that replied to your messages
                                                                            <span
                                                                                title="Include all leads you previously had a conversation with via Linkedin messages, inMails, or email">!</span>
                                                                        </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                class="switch setting_switch"
                                                                                id="global_settings_include_leads_that_replied_to_your_messages"
                                                                                disabled><label
                                                                                for="global_settings_include_leads_that_replied_to_your_messages">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> Include leads also found in campaigns across
                                                                            your team
                                                                            seats
                                                                            <span>!</span>
                                                                        </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                class="switch setting_switch"
                                                                                id="global_settings_include_leads_also_found_in_campaigns_across_your_team_seats"
                                                                                disabled><label
                                                                                for="global_settings_include_leads_also_found_in_campaigns_across_your_team_seats">Toggle</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="linked_set d-flex justify-content-between">
                                                                        <p> Discover new leads only <span
                                                                                title="Leads that exist in other campaigns in your seat will not be discovered">!</span>
                                                                        </p>
                                                                        <div class="switch_box"><input type="checkbox"
                                                                                class="switch setting_switch"
                                                                                id="global_settings_discover_new_leads_only"
                                                                                disabled><label
                                                                                for="global_settings_discover_new_leads_only">Toggle</label>
                                                                        </div>
                                                                    </div>
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
                                                                aria-labelledby="headingTwo" data-bs-parent="#accordion">
                                                                <div class="accordion-body">
                                                                    <div class="schedule-tab">
                                                                        <button class="schedule-btn active"
                                                                            id="my_campaign_schedule_btn"
                                                                            data-tab="my_campaign_schedule">My
                                                                            Schedules</button>
                                                                        <button class="schedule-btn "
                                                                            id="team_campaign_schedule_btn"
                                                                            data-tab="team_campaign_schedule">Team
                                                                            schedules</button>
                                                                    </div>
                                                                    <div class="active schedule-content"
                                                                        id="my_campaign_schedule">
                                                                        @if ($schedules->isNotEmpty())
                                                                            <ul class="schedule_list schedule_list_2"
                                                                                id="schedule_list_2">
                                                                                @foreach ($schedules as $schedule)
                                                                                    <li>
                                                                                        <div
                                                                                            class="row schedule_list_item">
                                                                                            <div
                                                                                                class="col-lg-1 schedule_item">
                                                                                                <input type="radio"
                                                                                                    name="global_settings_schedule_id"
                                                                                                    class="schedule_id global_settings_schedule_id"
                                                                                                    value="{{ $schedule['id'] }}"
                                                                                                    readonly>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-lg-3 schedule_name">
                                                                                                <span>{{ $schedule['name'] }}</span>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-lg-6 schedule_days">
                                                                                                @php
                                                                                                    $schedule_days = App\Models\Schedule_Day::where(
                                                                                                        'schedule_id',
                                                                                                        $schedule['id'],
                                                                                                    )
                                                                                                        ->orderBy('id')
                                                                                                        ->get();
                                                                                                @endphp
                                                                                                <ul
                                                                                                    class="schedule_day_list">
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
                                                                        @else
                                                                            <ul class="schedule_list schedule_list_2"
                                                                                id="schedule_list_2">
                                                                                <li class="text-center">
                                                                                    No Schedule Listed
                                                                                </li>
                                                                            </ul>
                                                                        @endif
                                                                    </div>
                                                                    <div class=" schedule-content"
                                                                        id="team_campaign_schedule">
                                                                        Hello</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane lead_pane" id="Stats" role="tabpanel">
                                        <div class="chart_box">
                                            <div class="border_box">
                                                <div class="chart_filter d-flex justify-content-between">
                                                    <div class="select d-flex">
                                                    </div>
                                                </div>
                                                <div class="chart_canvas_report">
                                                    <div id="chartContainer"
                                                        style="height: 388px; width: 100%; !important"></div>
                                                </div>
                                                <ul
                                                    class="chart_status d-flex justify-content-between list-unstyled p-0 stats_list">
                                                    <li data-span="viewsDataPoints"><span></span>Views</li>
                                                    <li data-span="inviteDataPoints"><span></span>Connections sent</li>
                                                    <li data-span="messageDataPoints"><span></span>Messages sent</li>
                                                    <li data-span="inMailDataPoints"><span></span>InMails sent</li>
                                                    <li data-span="followDataPoints"><span></span>Follows</li>
                                                    <li data-span="emailDataPoints"><span></span>Emails sent</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="chart_data_list">
                                            <div class="border_box">
                                                <div class="scroll_div">
                                                    <table class="data_table w-100" id="chat_table">
                                                        <thead>
                                                            <tr>
                                                                <th width="15%">Date</th>
                                                                <th width="20%">Views</th>
                                                                <th width="30%">Email sent</th>
                                                                <th width="20%" class="">Follows</th>
                                                                <th width="15%">Connections sent</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="report_data">
                                                            @php
                                                                $total_view = 0;
                                                                $total_email = 0;
                                                                $total_follow = 0;
                                                                $total_invite = 0;
                                                            @endphp
                                                            @if (!empty($reports))
                                                                @foreach ($reports as $date => $counts)
                                                                    @php
                                                                        $total_view += $counts['view_count'];
                                                                        $total_email += $counts['email_count'];
                                                                        $total_follow += $counts['follow_count'];
                                                                        $total_invite += $counts['invite_count'];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $date }}</td>
                                                                        <td>{{ $counts['view_count'] ?? 0 }}</td>
                                                                        <td>{{ $counts['email_count'] ?? 0 }}</td>
                                                                        <td>{{ $counts['follow_count'] ?? 0 }}</td>
                                                                        <td>{{ $counts['invite_count'] ?? 0 }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td>{{ now()->format('Y-m-d') }}</td>
                                                                    <td>0</td>
                                                                    <td>0</td>
                                                                    <td>0</td>
                                                                    <td>0</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                        <tfoot id="report_totals">
                                                            <tr>
                                                                <td>Total</td>
                                                                <td>{{ $total_view }}</td>
                                                                <td>{{ $total_email }}</td>
                                                                <td>{{ $total_follow }}</td>
                                                                <td>{{ $total_invite }}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade create_sequence_modal export_modal" id="export_modal" tabindex="-1"
        aria-labelledby="export_modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sequance_modal">Export data from all campaigns</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="export_form">
                        <div class="row">
                            <div class="col-12">
                                <div class="">
                                    <p class="w-75">Once the export is complete, we will send you the exported data is a
                                        CSV file. Please insert the email you would like us to use.</p>
                                    <input name="export_email" id="export_email" type="email"
                                        placeholder="example@gmail.com">
                                    <span style="color: red; display: none;" id="email_error"></span>
                                </div>
                            </div>
                            <a href="javascript:;" id="export_leads" class="crt_btn ">Submit<i
                                    class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        var pastMonthReports = @json($past_month_data);
        var viewsDataPoints = [];
        var inviteDataPoints = [];
        var messageDataPoints = [];
        var inMailDataPoints = [];
        var followDataPoints = [];
        var emailDataPoints = [];

        Object.keys(pastMonthReports).forEach(function(date) {
            var dateParts = date.split('-');
            var fullDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
            viewsDataPoints.push({
                x: fullDate,
                y: pastMonthReports[date]['view_count']
            });
            inviteDataPoints.push({
                x: fullDate,
                y: pastMonthReports[date]['invite_count']
            });
            messageDataPoints.push({
                x: fullDate,
                y: pastMonthReports[date]['message_count']
            });
            inMailDataPoints.push({
                x: fullDate,
                y: pastMonthReports[date]['in_mail_count']
            });
            followDataPoints.push({
                x: fullDate,
                y: pastMonthReports[date]['follow_count']
            });
            emailDataPoints.push({
                x: fullDate,
                y: pastMonthReports[date]['email_count']
            });
        });
    </script>
    <script>
        var leadsCampaignFilterPath =
            "{{ route('getLeadsByCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':id', ':search']) }}";
        var sendLeadsToEmail = "{{ route('sendLeadsToEmail', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}";
        $(document).ready(function() {
            $('.stats_list li').first().trigger('click');
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const day = String(currentDate.getDate()).padStart(2, '0');
            const hours = String(currentDate.getHours()).padStart(2, '0');
            const minutes = String(currentDate.getMinutes()).padStart(2, '0');
            const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;
            $("#created_at").html(
                '<i class="fa-solid fa-calendar-days"></i>Created at: ' +
                formattedDate
            );
            $(".setting_list").hide();
            $(".setting_btn").on("click", function(e) {
                $(".setting_list").not($(this).siblings(".setting_list")).hide();
                $(this).siblings(".setting_list").toggle();
            });
            $(document).on("click", function(e) {
                if (!$(event.target).closest(".setting").length) {
                    $(".setting_list").hide();
                }
            });
        });
    </script>
@endsection
