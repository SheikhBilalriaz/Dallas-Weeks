@php
    use Illuminate\Support\Str;
@endphp
<div class="sidebar_menu col-12  p-0 flex-shrink-1">
    <ul class="list-unstyle p-0 m-0">
        @php
            $navItems = [
                [
                    'permission' => true,
                    'route' => 'seatDashboardPage',
                    'label' => asset('assets/img/home.svg'),
                ],
                [
                    'permission' => session('manage_campaigns') === true || session('manage_campaigns') === 'view_only',
                    'route' => 'campaignPage',
                    'label' => asset('assets/img/speaker.svg'),
                ],
                [
                    'permission' => session('manage_campaigns') === true || session('manage_campaigns') === 'view_only',
                    'route' => 'globalInvoicePage',
                    'label' => asset('assets/img/leads.svg'),
                ],
                [
                    'permission' =>
                        session('manage_campaign_details_and_reports') === true ||
                        session('manage_campaign_details_and_reports') === 'view_only',
                    'route' => 'globalSetting',
                    'label' => asset('assets/img/stat.svg'),
                ],
                [
                    'permission' => session('manage_chat') === true || session('manage_chat') === 'view_only',
                    'route' => 'globalSetting',
                    'label' => asset('assets/img/message.svg'),
                ],
                [
                    'permission' => session('manage_webhooks') === true || session('manage_webhooks') === 'view_only',
                    'route' => 'webhookPage',
                    'label' => asset('assets/img/clip.svg'),
                ],
                [
                    'permission' =>
                        session('manage_global_limits') === true ||
                        session('manage_global_limits') === 'view_only' ||
                        session('manage_linkedin_integrations') === true ||
                        session('manage_linkedin_integrations') === 'view_only' ||
                        session('manage_account_health') === true ||
                        session('manage_account_health') === 'view_only' ||
                        session('manage_email_settings') === true ||
                        session('manage_email_settings') === 'view_only',
                    'route' => 'seatSettingPage',
                    'label' => asset('assets/img/settings.svg'),
                ],
            ];
        @endphp
        @foreach ($navItems as $item)
            @if ($item['permission'])
                <li>
                    <a href="{{ route($item['route'], ['slug' => $team->slug, 'seat_slug' => $seat->slug]) }}"
                        class="nav_link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <img src="{{ $item['label'] }}" alt="">
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
