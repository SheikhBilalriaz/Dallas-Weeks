@extends('back/partials/header')
@section('content')
    <script src="{{ asset('assets/js/campaign.js') }}"></script>
    <style>
        #payment-form input.form-control {
            color: white !important;
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
            /* border: 1px solid #fff; */
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
    @php
        session()->forget('campaign_details');
        session()->forget('edit_campaign_details');
    @endphp
    <section class="main_dashboard blacklist campaign_sec">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                <div class="col-lg-11 col-sm-12">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h3>Campaigns</h3>
                                @if (session('manage_campaigns') === true)
                                    <div class="filt_opt d-flex">
                                        <div class="add_btn ">
                                            <a href="{{ route('createCampaignPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                                                class=""><i class="fa-solid fa-plus"></i></a>Add Campaign
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="border_box dashboard_box">
                                <div class="count_div">
                                    <strong>{{ $profile['connections_count'] ?? 0 }}</strong>
                                    <div class="cont">
                                        <span>Total connections</span>
                                        <div class="gray_back d-flex">
                                            <i class="fa-solid fa-arrow-up"></i>2%<span>Today</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="filter_head_row d-flex">
                            </div>
                            <div class="filtr_desc">
                                <div class="d-flex">
                                    <strong>Campaigns</strong>
                                    <div class="filter">
                                        <a id="filterToggle"><i class="fa-solid fa-filter"></i></a>
                                        <select id="filterSelect" style="display: none">
                                            <option value="active">Active Campaigns</option>
                                            <option value="inactive">InActive Campaigns</option>
                                            <option value="archive">Archive Campaigns</option>
                                        </select>
                                        <form method="get" class="search-form">
                                            <input id="search_campaign" type="text" name="q"
                                                placeholder="Search Campaign here...">
                                            <button type="submit">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p>Easily track your campaigns in one place.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="border_box ">
                                <div class="campaign_list">
                                    <table class="data_table w-100">
                                        <thead>
                                            <tr>
                                                <th width="5%">Status</th>
                                                <th width="40%">Campaign name</th>
                                                <th width="10%">Total leads</th>
                                                <th width="10%">Sent messages</th>
                                                <th width="30%" class="stat">States</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="campaign_table_body">
                                            @if ($campaigns->isNotEmpty())
                                                @foreach ($campaigns as $campaign)
                                                    <tr id="{{ 'table_row_' . $campaign->id }}" class="campaign_table_row">
                                                        <td>
                                                            <div class="switch_box">
                                                                @if ($campaign->is_active == 1)
                                                                    <input type="checkbox" class="switch"
                                                                        id="switch{{ $campaign->id }}" checked>
                                                                @else
                                                                    <input type="checkbox" class="switch"
                                                                        id="switch{{ $campaign->id }}">
                                                                @endif
                                                                <label for="switch{{ $campaign->id }}">Toggle</label>
                                                            </div>
                                                        </td>
                                                        <td>{{ $campaign->name }}</td>
                                                        <td id="{{ 'lead_count_' . $campaign['id'] }}">
                                                            {{ $campaign['lead_count'] }}
                                                        </td>
                                                        <td id="{{ 'sent_message_count_' . $campaign['id'] }}">
                                                            {{ $campaign['message_count'] }}
                                                        </td>
                                                        <td class="stats">
                                                            <ul
                                                                class="status_list d-flex align-items-center list-unstyled p-0 m-0">
                                                                <li><span><img src="{{ asset('assets/img/eye.svg') }}"
                                                                            alt=""><span
                                                                            id="{{ 'view_profile_count_' . $campaign['id'] }}">{{ $campaign['view_action_count'] }}</span></span>
                                                                </li>
                                                                <li><span><img src="{{ asset('assets/img/request.svg') }}"
                                                                            alt=""><span
                                                                            id="{{ 'invite_to_connect_count_' . $campaign['id'] }}">{{ $campaign['invite_action_count'] }}</span></span>
                                                                </li>
                                                                <li><span><img src="{{ asset('assets/img/mailmsg.svg') }}"
                                                                            alt=""><span
                                                                            id="{{ 'email_message_count_' . $campaign['id'] }}">{{ $campaign['email_action_count'] }}</span></span>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                        @if (session('manage_campaigns') === true)
                                                            <td>
                                                                <a type="button" class="setting setting_btn"
                                                                    id=""><i class="fa-solid fa-gear"></i></a>
                                                                <ul class="setting_list" style="display: none">
                                                                    <li><a
                                                                            href="{{ route('campaignDetailsPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'campaign_id' => $campaign->id]) }}">Check
                                                                            campaign details</a></li>
                                                                    <li><a
                                                                            href="{{ route('editCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'campaign_id' => $campaign->id]) }}">Edit
                                                                            campaign</a></li>
                                                                    </li>
                                                                    <li><a class="archive_campaign"
                                                                            id="{{ 'archive' . $campaign->id }}">Archive
                                                                            campaign</a>
                                                                    </li>
                                                                    <li><a class="delete_campaign"
                                                                            id="{{ 'delete' . $campaign->id }}">Delete
                                                                            campaign</a>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="8">
                                                        <div class="text-center text-danger"
                                                            style="font-size: 25px; font-weight: bold; font-style: italic;">
                                                            Not Found!
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (session('manage_campaigns') === true)
        <script>
            var deleteCampaignRoute =
                "{{ route('deleteCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':id']) }}";
            var activateCampaignRoute =
                "{{ route('changeCampaignStatus', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':campaign_id']) }}";
            var archiveCampaignRoute =
                "{{ route('archiveCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':id']) }}";
        </script>
    @endif
    <script>
        var is_manage_allowed = {{ session('manage_campaigns') === true }};
        var filterCampaignRoute =
            "{{ route('filterCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':filter', ':search']) }}";
    </script>
@endsection
