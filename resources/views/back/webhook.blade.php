@extends('back/partials/header')
@section('content')
    <style>
        .disabled {
            opacity: 0.7;
            pointer-events: none;
            cursor: default;
        }

        .webhook_check input[name="radio"] {
            display: none;
        }

        input.error {
            border: 1px solid red;
            margin-bottom: 0 !important;
        }
    </style>
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
                                    @if (session('manage_webhooks') === true)
                                        <div style="cursor: pointer;" class="add_btn " data-bs-toggle="modal"
                                            data-bs-target="#webhook_modal">
                                            <a href="javascript:;" class="" type="button">
                                                <i class="fa-solid fa-plus"></i>
                                            </a>Create new webhook
                                        </div>
                                    @endif
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
                                                    <th width="20%">Name</th>
                                                    <th width="20%">Webhook url</th>
                                                    <th width="50%">Description</th>
                                                    @if (session('manage_webhooks') === true)
                                                        <th width="10%">Action</th>
                                                    @endif
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
                                                                {{ $webhook->reason }}
                                                            </td>
                                                            @if (session('manage_webhooks') === true)
                                                                <td>
                                                                    <a href="javascript:;" class="delete-webhook"
                                                                        data-id="{{ $webhook->id }}">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </a>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="4">
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
    @if (session('manage_webhooks') === true)
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
                        <form method="POST"
                            action="{{ route('createWebhook', ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="">
                                        <p>Callback URL</p>
                                        <input class="{{ $errors->has('call_back') ? 'error' : '' }}" type="text"
                                            placeholder="Enter callback URL here" name="call_back"
                                            value="{{ old('call_back') }}">
                                        @error('call_back')
                                            <span class="text-danger text-left">{{ $message }}</span>
                                        @enderror
                                        <p>Name</p>
                                        <input class="{{ $errors->has('name') ? 'error' : '' }}" type="text"
                                            placeholder="Enter name here" name="name" value="{{ old('name') }}">
                                        @error('name')
                                            <span class="text-danger text-left">{{ $message }}</span>
                                        @enderror
                                        <p>Description</p>
                                        <input class="{{ $errors->has('desc') ? 'error' : '' }}" type="text"
                                            placeholder="Enter name here" name="desc" value="{{ old('desc') }}">
                                        @error('desc')
                                            <span class="text-danger text-left">{{ $message }}</span>
                                        @enderror
                                        <h6 class="text-center">What type of updates to send</h6>
                                        <ul class="webhook_check d-flex flex-wrap list-unstyled">
                                            <li class="account_options"><span>
                                                    <input type="radio" id="messaging" name="webhook_selection"
                                                        value="messaging"
                                                        {{ old('webhook_selection') == 'messaging' ? 'checked' : '' }}>
                                                    <label for="messaging">Messaging</label>
                                            </li>
                                            <li class="email_options"><span>
                                                    <input type="radio" id="mailing" name="webhook_selection"
                                                        value="mailing"
                                                        {{ old('webhook_selection') == 'mailing' ? 'checked' : '' }}>
                                                    <label for="mailing">Mailing</label>
                                            </li>
                                            <li class="email_options"><span>
                                                    <input type="radio" id="mail_tracking" name="webhook_selection"
                                                        value="mail_tracking"
                                                        {{ old('webhook_selection') == 'mail_tracking' ? 'checked' : '' }}>
                                                    <label for="mail_tracking">Mail Tracking</label>
                                            </li>
                                        </ul>
                                        @error('webhook_selection')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <ul class="webhook_check" id="emails_div"></ul>
                                        @error('accounts')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="btn_row d-flex justify-content-center">
                                    <a href="javascript:;" class="crt_btn submit" id="submit_webhook"
                                        type="submit">Submit<i class="fa-solid fa-arrow-right"></i></a>
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
            if ("{{ session()->has('webhook_model') }}") {
                $('#webhook_modal').modal('show');
            }
        });
        var emails = @json($emails);
        var deleteWebhookRoute =
            "{{ route('deleteWebhook', ['slug' => $team->slug, 'seat_slug' => $seat->slug, 'id' => ':id']) }}";
    </script>
@endsection
