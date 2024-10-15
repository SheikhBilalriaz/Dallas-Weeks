@extends('back/partials/header')
@section('content')
    <script src="{{ asset('assets/js/webhook.js') }}"></script>
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
                                <h3>Integrations</h3>
                                <div class="filt_opt d-flex">
                                    <div class="filt_opt">
                                    </div>
                                    <div style="cursor: pointer;" class="add_btn " data-bs-toggle="modal"
                                        data-bs-target="#webhook_modal">
                                        <a href="javascript:;" class="" type="button">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>Create new webhook
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="integration_sec">
                                <div class="border_box">
                                    <div class="scroll_div">
                                        <table class="data_table w-100">
                                            <thead>
                                                <tr>
                                                    <th width="45%">Name</th>
                                                    <th width="45%">Webhook url</th>
                                                    <th width="10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="webhook_row">
                                                @if ($webhooks->isNotEmpty())
                                                    @foreach ($webhooks as $webhook)
                                                        <tr id="{{ 'webhook_' . $webhook->id }}">
                                                            <td class="title_cont">{{ $webhook->name }}</td>
                                                            <td>
                                                                <a
                                                                    href="{{ $webhook->webhook['request_url'] }}">{{ $webhook->webhook['request_url'] }}</a>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:;" class="delete-webhook"
                                                                    data-id="{{ $webhook->id }}">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="3">
                                                            <div style="width: 50%; margin: 0 auto;"
                                                                class="empty_blacklist text-center">
                                                                <p>Sorry, no results for that query</p>
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
        </div>
    </section>
    <div class="modal fade create_sequence_modal filter_modal webhook_modal" id="webhook_modal" tabindex="-1"
        aria-labelledby="filter_modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sequance_modal">Setup Webhook</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="row">
                            <div class="col-12">
                                <div class="">
                                    <p>Callback URL</p>
                                    <input type="text" placeholder="Enter callback URL here">
                                    <h6 class="text-center">What type of updates to send</h6>
                                    <ul class="webhook_check d-flex flex-wrap list-unstyled">
                                        <li><span>
                                                <input type="checkbox" id="status_Discovered" name="lead_status"
                                                    value="Discovered">
                                                <label for="status_Discovered">
                                                    When a contact is invited to connect
                                                </label>
                                        </li>
                                        <li><span>
                                                <input type="checkbox" id="status_Connection_pending" name="lead_status"
                                                    value="Connection_pending">
                                                <label for="status_Connection_pending">When a contact accepts connection
                                                </label>
                                        </li>
                                        <li><span>
                                                <input type="checkbox" id="status_Connected_not_replied" name="lead_status"
                                                    value="Connected, not replied">
                                                <label for="status_Connected_not_replied">When a contact replies
                                                </label>
                                        </li>
                                        <li><span>
                                                <input type="checkbox" id="status_Replied" name="lead_status"
                                                    value="Replied">
                                                <label for="status_Replied"> When a message is received from contact
                                                </label>
                                        </li>
                                        <li><span>
                                                <input type="checkbox" id="status_Replied_not_connected" name="lead_status"
                                                    value="Replied, not connected">
                                                <label for="status_Replied_not_connected">
                                                    Send all connection requests
                                                </label>
                                        </li>
                                        <li><span>
                                                <input type="checkbox" id="status_Replied_not_connected" name="lead_status"
                                                    value="Replied, not connected">
                                                <label for="status_Replied_not_connected">
                                                    When a lead completed the campaign
                                                </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="btn_row d-flex justify-content-center">
                                <a href="javascript:;" class="crt_btn submit">Submit<i
                                        class="fa-solid fa-arrow-right"></i></a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        var deleteWebhookRoute =
            "{{ route('deleteWebhook', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'id' => ':id']) }}";
    </script>
@endsection
