@extends('back/partials/header')
@section('content')
    <script src="{{ asset('assets/js/editCampaignSequence.js') }}"></script>
    @php
        $settings = json_encode($settings);
    @endphp
    <section class="main_dashboard blacklist  campaign_sec">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                <div class="col-lg-11 col-sm-12">
                    <div class="row crt_cmp_r sequence-steps">
                        <div class="col-lg-9 drop-pad" id="capture">
                            <h5>Sequence Steps</h5>
                            <div class="custom-center">
                                <div class="cmp_opt_link d-flex">
                                    <ul class="d-flex list-unstyled justify-content-end align-items-center">
                                        <li class="active prev full"><span>1</span><a href="javascript:;">Campaign info</a>
                                        </li>
                                        <li class="active prev full"><span>2</span><a href="javascript:;">Campaign
                                                settings</a></li>
                                        <li class="active"><span>3</span><a href="javascript:;">Campaign steps</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-list"></div>
                        </div>
                        <div class="col-lg-3 add-elements">
                            <div class="element-tab">
                                <button class="element-btn active" id="element-list-btn" data-tab="element-list">Add
                                    Elements</button>
                                <button class="element-btn" id="properties-btn" data-tab="properties">Properties</button>
                            </div>
                            <div class="element-list element-content active" id="element-list">
                                <div class="element_div">
                                    @if (!empty($campaigns))
                                        <div class="action_elements">
                                            <p>Actions</p>
                                            <ul class='drop-list'>
                                                @foreach ($campaigns as $campaign)
                                                    <li>
                                                        <div class="element element_item"
                                                            id="{{ $campaign['slug'] }}" data-filter-item
                                                            data-filter-name="{{ $campaign['slug'] }}">
                                                            <div
                                                                class="element_change_input attach-elements attach-elements-in">
                                                            </div>
                                                            <div class="cancel-icon">
                                                                <i class="fa-solid fa-x"></i>
                                                            </div>
                                                            <div class="list-icon">
                                                                {!! $campaign['icon'] !!}
                                                            </div>
                                                            <div class="item_details">
                                                                <p class="item_name">{{ $campaign['name'] }}</p>
                                                                <p class="item_desc"><i class="fa-solid fa-clock"></i>Wait
                                                                    for: <span class="item_days">0</span> days <span
                                                                        class="item_hours">0</span> hours</p>
                                                            </div>
                                                            <div class="menu-icon">
                                                                <i class="fa-solid fa-bars"></i>
                                                            </div>
                                                            <div
                                                                class="element_change_output attach-elements attach-elements-out condition_true">
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <div class="action_elements">
                                            <p>Actions</p>
                                            <ul class='drop-list'>
                                                <li>No Elemenets Found</li>
                                            </ul>
                                        </div>
                                    @endif
                                    @if (!empty($conditional_campaigns))
                                        <div class="conditional_elements">
                                            <p>Conditions</p>
                                            <ul class='drop-list'>
                                                @foreach ($conditional_campaigns as $campaign)
                                                    <li>
                                                        <div class="element element_item"
                                                            id="{{ $campaign['slug'] }}" data-filter-item
                                                            data-filter-name="{{ $campaign['slug'] }}">
                                                            <div
                                                                class="element_change_input conditional-elements conditional-elements-in">
                                                            </div>
                                                            <div class="cancel-icon">
                                                                <i class="fa-solid fa-x"></i>
                                                            </div>
                                                            <div class="list-icon">
                                                                {!! $campaign['icon'] !!}
                                                            </div>
                                                            <div class="item_details">
                                                                <p class="item_name">{{ $campaign['name'] }}</p>
                                                                <p class="item_desc"><i class="fa-solid fa-clock"></i>Check
                                                                    after: <span class="item_days">0</span> days <span
                                                                        class="item_hours">0</span> hours</p>
                                                            </div>
                                                            <div class="menu-icon">
                                                                <i class="fa-solid fa-bars"></i>
                                                            </div>
                                                            <div class="conditional-elements conditional-elements-out">
                                                                <div class="element_change_output condition_true">
                                                                    <i class="fa-solid fa-check"></i>
                                                                </div>
                                                                <div class="element_change_output condition_false">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <div class="action_elements">
                                            <p>Conditions</p>
                                            <ul class='drop-list'>
                                                <li>No Elemenets Found</li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                <div class="save-btns">
                                    <button id="save-changes">Save Changes</button>
                                </div>
                            </div>
                            <div class="properties element-content" id="properties"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        var campaign_id = {!! $campaign_id !!};
        var settings = {!! $settings !!};
        var updateCampaignRoute = "{{ route('updateCampaign', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':campaign_id']) }}";
        var getElementsRoute = "{{ route('getElements', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':campaign_id']) }}";
        var getElementByIdRoute = "{{ route('getcampaignelementbyid', ['slug' => $team->slug, 'seat_slug' => $seat->slug, ':element_id']) }}";
        var campaignRoute = "{{ route('campaignPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}";
    </script>
@endsection
