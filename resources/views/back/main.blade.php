@extends('back/partials/header')
@section('content')
    <section class="main_dashboard">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                <div class="col-lg-4 col-sm-12">
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
                        <div class="darkgrey_div">
                            <div class="d-flex justify-content-between">
                                <div class="connection_imgs">
                                    @if (!empty($relations))
                                        @foreach ($relations as $relation)
                                            @if (!empty($relation['profile_picture_url']))
                                                <img src="{{ $relation['profile_picture_url'] }}" alt="">
                                            @else
                                                <img class="no_img" src="{{ asset('assets/img/acc.png') }}" alt="">
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="cont">Manage Connections<i class="fa-solid fa-arrow-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="border_box">
                        <div class="chart_box">
                            <div class="d-flex justify-content-between">
                                <span>Campaign stats</span>
                            </div>
                            <div class="chart_canvas">
                                <div id="chartContainer" style="height: 350px; width: 100%;"></div>
                            </div>
                        </div>
                        <div class="invite_date_box">
                            @for ($i = 0; $i <= 3; $i++)
                                <ul class="date d-flex list-unstyle">
                                    <li>
                                        <span>Date</span>
                                        2023-01-16
                                    </li>
                                    <li>
                                        <span>Views</span>
                                        25
                                    </li>
                                    <li class="invites">
                                        <span>Invites</span>
                                        2
                                    </li>
                                    <li>
                                        <span>Follows</span>
                                        15
                                    </li>
                                </ul>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-sm-12">
                    <div class="border_box">
                        <div class="campaign_box">
                            <div class="d-flex justify-content-between">
                                <span>Campaign stats</span>
                            </div>
                            <div class="campaign_data">
                                @if (session('manage_campaigns') == true || session('manage_campaigns') == 'view_only')
                                    @if ($campaigns->isNotEmpty())
                                        @foreach ($campaigns as $campaign)
                                            <ul class="campaign_list" id="{{ 'campaign_list_' . $campaign['id'] }}">
                                                <li>{{ $campaign['campaign_name'] }}</li>
                                                <li>{{ $campaign['lead_count'] }}</li>
                                                <li><a href="javascript:;" class="campaign_stat">48%</a></li>
                                                <li><a href="javascript:;" class="campaign_stat down">23%</a></li>
                                                <li>
                                                    <div class="switch_box">
                                                        @if ($campaign['is_active'] == 1)
                                                            <input type="checkbox" class="switch"
                                                                id="switch{{ $campaign['id'] }}" checked />
                                                        @else
                                                            <input type="checkbox" class="switch"
                                                                id="switch{{ $campaign['id'] }}" />
                                                        @endif
                                                        <label for="switch{{ $campaign['id'] }}">Toggle</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        @endforeach
                                        <a class="get_more_label"
                                            href="{{ route('campaignPage', ['slug' => $team->slug, 'seat_slug', $seat->slug]) }}"
                                            bis_skin_checked="1">
                                            More Campaigns<i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    @else
                                        <div class="campaign_list" style="display: block; cursor: auto;">
                                            <div class="text-center">
                                                <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                                <p>Campaign Not Found!</p>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="campaign_list" style="display: block; cursor: auto;">
                                        <div class="text-center">
                                            <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                            <p>You can not access Campaigns</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="border_box">
                        <div class="campaign_box">
                            <div class="d-flex justify-content-between">
                                <span>Messages</span>
                            </div>
                            <div class="campaign_data">
                                @if (session('manage_chat') == true || session('manage_chat') == 'view_only')
                                    @if (!empty($chats))
                                        @foreach ($chats as $chat)
                                            @php
                                                $disable_chat = 'false';
                                                if (
                                                    $chat['read_only'] ||
                                                    in_array('reply', $chat['disabledFeatures'])
                                                ) {
                                                    $disable_chat = 'true';
                                                }
                                            @endphp
                                            <ul class="message_list chat-tab" id="{{ $chat['id'] }}"
                                                data-profile="{{ $chat['attendee_provider_id'] }}"
                                                data-disable="{{ $disable_chat }}">
                                                <li class="skel_profile">
                                                    <img src="{{ asset('assets/img/acc.png') }}" alt=""
                                                        class="skel_profile_img">
                                                    <a href="" class="skel_profile_name"></a>
                                                </li>
                                                <li class="col-lg-6">
                                                    <p class="skel_message"></p>
                                                </li>
                                                <li>
                                                    <a href="javascript:;"><i class="fa-brands fa-linkedin"></i></a>
                                                </li>
                                            </ul>
                                        @endforeach
                                        <a class="get_more_label"
                                            href="{{ route('seatMessageController', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                                            bis_skin_checked="1">
                                            More Messages
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    @else
                                        <div class="campaign_list" style="display: block; cursor: auto;">
                                            <div class="text-center">
                                                <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                                <p>Messages Not Found!</p>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="campaign_list" style="display: block; cursor: auto;">
                                        <div class="text-center">
                                            <img src="{{ asset('assets/img/empty.png') }}" alt="">
                                            <p>You can not access Campaigns</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('assets/js/main-dashboard.js') }}"></script>
    <script>
        var getProfileAndLatestMessageRoute =
            "{{ route('getProfileAndLatestMessage', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'profile_id' => ':profile_id', 'chat_id' => ':chat_id']) }}";
    </script>
@endsection
