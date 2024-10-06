<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap-grid.min.css"
        integrity="sha512-ZuRTqfQ3jNAKvJskDAU/hxbX1w25g41bANOVd1Co6GahIe2XjM6uVZ9dh0Nt3KFCOA061amfF2VeL60aJXdwwQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta.2/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/brands.min.css"
        integrity="sha512-W/zrbCncQnky/EzL+/AYwTtosvrM+YG/V6piQLSe2HuKS6cmbw89kjYkp3tWFn1dkWV7L1ruvJyKbLz73Vlgfg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('assets/js/custom_dashboard.js') }}"></script>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    @if (request()->is('accdashboard', 'report', 'leads'))
        <script src="{{ asset('assets/js/chart_query.js') }}"></script>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<style>
    .custom_link {
        color: #3b475d;
        font-size: 14px;
        width: 100% !important;
        display: block;
    }

    .custom_link:hover {
        color: #3b475ddd !important;
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

    .alert.alert-danger.alert-dismissible {
        background: #870000;
        border: none;
        border-radius: 30px;
        padding: 20px;
        text-align: center;
        color: #fff;
        width: 60%;
        margin: 20px auto;
    }

    .alert.alert-danger.alert-dismissible .close {
        height: 50px;
        width: 50px;
        opacity: 1;
        font-weight: 400;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 100%;
        position: absolute;
        top: 5px;
        right: 10px;
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

    .alert.alert-danger.alert-dismissible {
        background: #870000;
        border: none;
        border-radius: 30px;
        padding: 20px;
        text-align: center;
        color: #fff;
        width: 60%;
        margin: 20px auto;
    }

    .alert.alert-danger.alert-dismissible .close {
        height: 50px;
        width: 50px;
        opacity: 1;
        font-weight: 400;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 100%;
        position: absolute;
        top: 5px;
        right: 10px;
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
</style>

<body>
    @if (!session('email_verified'))
        <div style="margin: 0;border-radius: 0%;padding: 7px;"
            class="alert-warning alert-dismissible fade show text-center" role="alert">
            <a href="{{ route('resendAnEmail') }}" class="custom_link">
                <strong><i style="color: #ecb25d" class="fa-solid fa-triangle-exclamation"></i></strong>
                Please confirm your email. Check your inbox and your spam folder. No email? Click here to Resend
                confirmation email
            </a>
        </div>
    @endif
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-dark justify-content-between">
            <a class="navbar-brand" href="{{ route('dashboardPage', ['slug' => $team->slug]) }}">
                <img style="width: 65px; background-color: #fff; margin-right: 7px; border-radius: 10px; padding: 6px;"
                    src="{{ asset('assets/images/logo.png') }}">Networked
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                @php
                    $navItems = [
                        [
                            'permission' => session('is_manage_global_blacklist'),
                            'route' => 'globalBlacklistPage',
                            'label' => 'Blacklist',
                        ],
                        [
                            'permission' => true,
                            'route' => 'teamPage',
                            'label' => 'Team',
                        ],
                        [
                            'permission' => session('is_manage_payment_system'),
                            'route' => 'globalInvoicePage',
                            'label' => 'Invoice',
                        ],
                        [
                            'permission' => true,
                            'route' => 'globalSetting',
                            'label' => 'Settings',
                        ],
                    ];
                @endphp
                <ul class="navbar-nav">
                    @foreach ($navItems as $item)
                        @if ($item['permission'])
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                                    href="{{ route($item['route'], ['slug' => $team->slug]) }}">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="right_nav">
                <ul class="d-flex list-unstyled">
                    <li>
                        <a>
                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                        </a>
                    </li>
                    @php
                        $user = auth()->user();
                    @endphp
                    @if ($user)
                        <li class="acc d-flex align-item-center">
                            <img src="{{ asset('/assets/img/acc.png') }}" alt="">
                            <span>{{ $user->name }}</span>
                            <a type="button" class="user_toggle" id="">
                                <i class="fa-solid fa-chevron-down"></i>
                            </a>
                            <ul class="user_toggle_list" style="display: none">
                                <li>
                                    <a href="{{ route('logoutUser') }}">
                                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <li class="darkmode">
                        <a href="javascript:;" id="darkModeToggle">
                            <i class="fa-solid fa-sun"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    </script>
    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @endif
    @if ($errors->first('error'))
        <script>
            toastr.error("{{ $errors->first('error') }}");
        </script>
    @endif
    @yield('content')
    <script>
        $(".user_toggle").on("click", function(e) {
            $(".user_toggle_list").toggle();
        });
    </script>
</body>

</html>
