@extends('dashboard/partials/master')
@section('content')
    @php
        foreach ($seats as $seat) {
            $seat->company_info = \App\Models\Company_Info::find($seat->company_info_id);
            $seat->seat_info = \App\Models\Seat_Info::find($seat->seat_info_id);
        }
    @endphp
    <script src="{{ asset('assets/js/team.js') }}"></script>
    <style>
        .filter_head_row .d-flex {
            justify-content: space-between;
        }

        .create_sequence_modal .modal-dialog .modal-body {
            padding: 10% !important;
        }

        .create_sequence_modal .modal-dialog .modal-body #role_name {
            width: 100%;
            text-align: start;
        }

        .btn-theme {
            padding: 20px 30px !important;
            border-radius: 30px !important;
            font-size: 18px !important;
            line-height: 1 !important;
            position: relative;
            background: #e3c935 !important;
            border: 1px solid #e3c935 !important;
            transition: 0.5s !important;
            margin: 0 10px;
            color: #000 !important;
        }

        #name_error {
            text-align: start;
            margin-bottom: 40px !important;
        }

        #role_name_input {
            margin-bottom: 0px !important;
        }

        #role_name_input.error {
            border: 1px solid red;
        }

        .step_form_popup input.error {
            border: 1px solid red;
        }

        .disabled {
            opacity: 0.7;
            pointer-events: none;
            cursor: default;
        }
        
        .un-disabled {
            opacity: 1;
            pointer-events: auto;
            cursor: pointer;
        }

        .no-pointer {
            cursor: default;
        }

        .pointer {
            cursor: pointer;
        }

        .team_management .filt_opt.d-flex {
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .filter_head_row .d-flex {
            justify-content: space-between;
        }

        .filt_opt .add_btn {
            margin-right: 0px;
        }

        .global-blacklist__back-arrow * {
            color: #16adcb;
        }

        .global-blacklist__back-arrow:hover * {
            color: #fff !important;
        }

        .empty_blacklist img {
            width: 30% !important;
            height: 100% !important;
            margin-bottom: 25px;
        }
    </style>
    <section class="blacklist team_management">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="filter_head_row">
                        <div class="cont d-flex">
                            <a class="d-block global-blacklist__back-arrow"
                                href="{{ route('dashboardPage', ['slug' => $team->slug]) }}">
                                <span><i class="fa-solid fa-chevron-left"></i></span>
                                <span>Back</span>
                            </a>
                            <h3>Team Management</h3>
                        </div>
                        <div class="filt_opt d-flex">
                            @if (session('is_creator'))
                                <p>Invite team members and manage team permissions.</p>
                            @else
                                <p>You can not invite team members and manage team permissions.</p>
                            @endif
                            @if (session('is_creator'))
                                <div style="cursor: pointer; opacity: {{ !session('email_verified') ? '0.7' : '1' }};"
                                    class="add_btn "
                                    title="{{ !session('email_verified') ? 'To add new team members, you need to verify your email address first.' : '' }}"
                                    {{ !session('email_verified') ? '' : 'data-bs-toggle=modal data-bs-target=#invite_team_modal' }}>
                                    <a href="javascript:;" class="">
                                        <i class="fa-solid fa-plus"></i>
                                    </a>
                                    Add team member
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="filtr_desc">
                        <div class="d-flex">
                            <strong>Team members</strong>
                            <div class="filter">
                                <div class="search-form">
                                    <input type="text" name="q" placeholder="Search..." id="search-team-member">
                                    <button type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                                <a href="{{ route('rolesPermissionPage', ['slug' => $team->slug]) }}" class="roles_btn">
                                    Roles & permissions
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="data_row">
                        <div class="data_head">
                            <table class="data_table w-100">
                                <thead>
                                    <tr>
                                        <th width="{{ session('is_creator') ? '30%' : '40%' }}">Name</th>
                                        <th width="15%">Email</th>
                                        <th width="15%">Roles</th>
                                        <th width="15%">Seats</th>
                                        <th width="15%">Status</th>
                                        @if (session('is_creator'))
                                            <th width="10%">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="team_row">
                                    <tr class=""
                                        title="{{ session('email_verified') ? '' : 'Verify your email first to view team' }}"
                                        style="opacity: {{ !session('email_verified') ? 0.7 : 1 }};">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img style="background: #000; border-radius: 50%;"
                                                    src="{{ asset('assets/img/acc.png') }}" alt="">
                                                <strong>{{ $creator->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $creator->email }}</td>
                                        <td>Creator</td>
                                        <td>All Seats Access</td>
                                        @if (!empty($creator->verified_at))
                                            <td>
                                                <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
                                                    href="javascript:;" class="black_list_activate active">
                                                    Active
                                                </a>
                                            </td>
                                        @else
                                            <td>
                                                <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
                                                    href="javascript:;" class="black_list_activate non_active">
                                                    InActive
                                                </a>
                                            </td>
                                        @endif
                                        @if (session('is_creator'))
                                            <td></td>
                                        @endif
                                    </tr>
                                    @if ($members->isNotEmpty())
                                        @foreach ($members as $member)
                                            @php
                                                $member_detail = \App\Models\User::find($member->user_id);
                                            @endphp
                                            <tr title="{{ session('email_verified') ? '' : 'Verify your email first to view team' }}"
                                                style="opacity: {{ !session('email_verified') ? 0.7 : 1 }};"
                                                id="{{ 'table_row_' . $member->id }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img style="background: #000; border-radius: 50%;"
                                                            src="{{ asset('assets/img/acc.png') }}" alt="">
                                                        <strong>{{ $member_detail->name }}</strong>
                                                    </div>
                                                </td>
                                                <td>{{ $member_detail->email }}</td>
                                                @php
                                                    $assigned_seats = \App\Models\Assigned_Seat::where(
                                                        'member_id',
                                                        $member->id,
                                                    )->get();
                                                @endphp
                                                <td>
                                                    @php
                                                        $member_role = \App\Models\Role::whereIn(
                                                            'id',
                                                            $assigned_seats->pluck('role_id')->toArray(),
                                                        )->get();
                                                        $member_roles = $member_role->pluck('name')->toArray();
                                                    @endphp
                                                    {{ implode(', ', $member_roles) ?: 'No Role Assigned' }}
                                                </td>
                                                <td>
                                                    @php
                                                        $member_seat = \App\Models\Seat::whereIn(
                                                            'id',
                                                            $assigned_seats->pluck('seat_id')->toArray(),
                                                        )->get();
                                                        $member_seat = \App\Models\Company_Info::whereIn(
                                                            'id',
                                                            $member_seat->pluck('company_info_id')->toArray(),
                                                        )->get();
                                                        $member_seats = $member_seat->pluck('name')->toArray();
                                                    @endphp
                                                    {{ implode(', ', $member_seats) ?: 'No Seat Assigned' }}
                                                </td>
                                                @if (!empty($member_detail->verified_at))
                                                    <td>
                                                        <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
                                                            href="javascript:;" class="black_list_activate active">
                                                            Active
                                                        </a>
                                                    </td>
                                                @else
                                                    <td>
                                                        <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
                                                            href="javascript:;" class="black_list_activate non_active">
                                                            InActive
                                                        </a>
                                                    </td>
                                                @endif
                                                @if (session('is_creator'))
                                                    <td>
                                                        <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
                                                            href="javascript:;" type="button" class="setting setting_btn"
                                                            id="">
                                                            <i class="fa-solid fa-gear"></i>
                                                        </a>
                                                        @if (session('email_verified'))
                                                            <ul class="setting_list">
                                                                <li class="edit-team-member">
                                                                    <a href="javascript:;">Edit</a>
                                                                </li>
                                                                <li class="delete-team-member">
                                                                    <a href="javascript:;">Delete</a>
                                                                </li>
                                                            </ul>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (!$members->isNotEmpty() && empty($creator))
                                        <tr>
                                            <td colspan="{{ session('is_creator') ? '6' : '5' }}">
                                                <div style="width: 50%; margin: 0 auto;"
                                                    class="empty_blacklist text-center">
                                                    <img style="margin-right: 0px;"
                                                        src="{{ asset('assets/img/empty.png') }}" alt="">
                                                    <p>
                                                        Sorry, no results for that query
                                                    </p>
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
    </section>
    <script>
        var seats = @json($seats);
        var emailVerified = "{{ session('email_verified') }}";
        var emptyImage = "{{ asset('assets/img/empty.png') }}";
        var accImage = "{{ asset('assets/img/acc.png') }}";
        var isCreator = "{{ session('is_creator') }}";
    </script>
    @if (session('is_creator'))
        <div class="modal fade create_sequence_modal invite_team_modal" id="invite_team_modal" tabindex="-1"
            aria-labelledby="invite_team_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sequance_modal">Invite a team member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="model_alert alert alert-danger alert-dismissible fade show input_errors"
                            style="display: none;" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @if ($errors->first())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <a class="close" data-dismiss="alert" aria-label="Close">&times;</a>
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <form class="invite_form" method="POST"
                            action="{{ route('inviteTeamMember', ['slug' => $team->slug]) }}">
                            @csrf
                            <div class="row invite_modal_row">
                                <div class="col-lg-6">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" placeholder="Enter team member's name"
                                        value="{{ old('name') }}" required>
                                </div>
                                <div class="col-lg-6">
                                    <label for="email">Email</label>
                                    <input type="email" id="invite_email" name="invite_email"
                                        placeholder="Enter team member's email" value="{{ old('invite_email') }}"
                                        required>
                                </div>
                                <span>Select one role for your team member</span>
                                <div class="col-lg-12 edit_able {{ !session()->has('invite_error') ? 'disabled' : '' }}">
                                    @if ($roles->isNotEmpty())
                                        @foreach ($roles as $role)
                                            <div class="checkboxes">
                                                <div class="check">
                                                    <input value="{{ 'role_' . $role->id }}" name="roles[]"
                                                        type="checkbox">
                                                    <label class="roles"
                                                        for="{{ 'role_' . $role->id }}">{{ $role->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="un-disabled mb-5">
                                            <i class="fa-solid fa-triangle-exclamation" style="color: #ff0000;"></i>
                                            You don't have any listed role
                                            <a href="{{ route('rolesPermissionPage', ['slug' => $team->slug]) }}">Create role -></a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-lg-6 edit_able {{ !session()->has('invite_error') ? 'disabled' : '' }}">
                                    <div class="border_box">
                                        <h6>Manage payment system</h6>
                                        <p>This is a global option that enables access to invoices and adding seats.</p>
                                        <div class="switch_box"><input type="checkbox" name="manage_payment_system"
                                                class="switch" id="switch0"><label for="switch0">Toggle</label></div>
                                    </div>
                                </div>
                                <div class="col-lg-6 edit_able {{ !session()->has('invite_error') ? 'disabled' : '' }}">
                                    <div class="border_box">
                                        <h6>Manage global blacklist</h6>
                                        <p>This is a global option that enables managing the global blacklist on the team
                                            level.
                                        </p>
                                        <div class="switch_box"><input type="checkbox" name="manage_global_blacklist"
                                                class="switch" id="switch1"><label for="switch1">Toggle</label></div>
                                    </div>
                                </div>
                                <button type="submit"
                                    class="crt_btn edit_able_btn {{ !session()->has('invite_error') ? 'disabled' : '' }} theme_btn manage_member mt-5">
                                    Invite member <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (session('is_creator'))
        <div class="modal fade create_sequence_modal invite_team_modal" id="edit_team_modal" tabindex="-1"
            aria-labelledby="edit_team_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sequance_modal">Edit a team member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="model_alert alert alert-danger alert-dismissible fade show input_errors"
                            style="display: none;" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @if ($errors->first())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <a class="close" data-dismiss="alert" aria-label="Close">&times;</a>
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <form class="invite_form" method="POST"
                            action="{{ route('editTeamMember', ['slug' => $team->slug, 'id' => ':id']) }}">
                            @csrf
                            @method('PUT')
                            <div class="row invite_modal_row">
                                <div class="col-lg-6">
                                    <label for="name">Name</label>
                                    <input type="text" name="edit_member_name" id="edit_member_name"
                                        placeholder="Enter team member's name" value="{{ old('edit_member_name') }}"
                                        required>
                                </div>
                                <div class="col-lg-6">
                                    <label for="email">Email</label>
                                    <input type="email" id="edit_invite_email" name="edit_invite_email"
                                        placeholder="Enter team member's email" value="{{ old('edit_invite_email') }}"
                                        required>
                                </div>
                                <span>Select one role for your team member</span>
                                <div
                                    class="col-lg-12 edit_able {{ !session()->has('edit_invite_error') ? 'disabled' : '' }}">
                                    @if ($roles->isNotEmpty())
                                        @foreach ($roles as $role)
                                            <div class="checkboxes">
                                                <div class="check">
                                                    <input value="{{ 'role_' . $role->id }}" name="roles[]"
                                                        type="checkbox">
                                                    <label class="roles"
                                                        for="{{ 'role_' . $role->id }}">{{ $role->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div
                                    class="col-lg-6 edit_able {{ !session()->has('edit_invite_error') ? 'disabled' : '' }}">
                                    <div class="border_box">
                                        <h6>Manage payment system</h6>
                                        <p>This is a global option that enables access to invoices and adding seats.</p>
                                        <div class="switch_box"><input type="checkbox" name="edit_manage_payment_system"
                                                class="switch" id="switch2"><label for="switch2">Toggle</label></div>
                                    </div>
                                </div>
                                <div
                                    class="col-lg-6 edit_able {{ !session()->has('edit_invite_error') ? 'disabled' : '' }}">
                                    <div class="border_box">
                                        <h6>Manage global blacklist</h6>
                                        <p>This is a global option that enables managing the global blacklist on the team
                                            level.
                                        </p>
                                        <div class="switch_box"><input type="checkbox"
                                                name="edit_manage_global_blacklist" class="switch" id="switch3"><label
                                                for="switch3">Toggle</label></div>
                                    </div>
                                </div>
                                <button type="submit"
                                    class="crt_btn edit_able_btn {{ !session()->has('edit_invite_error') ? 'disabled' : '' }} theme_btn manage_member mt-5">
                                    Edit Team member <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <script>
        $(document).ready(function() {
            if ("{{ session()->has('invite_error') }}") {
                $('#invite_team_modal').modal('show');
            }
            if ("{{ session()->has('edit_invite_error') }}") {
                $('#edit_team_modal').modal('show');
            }
        });
    </script>
    <script>
        var searchTeamMemberRoute = "{{ route('searchTeamMember', ['slug' => $team->slug, 'search' => ':search']) }}";
    </script>
    @if (session('is_creator'))
        <script>
            var deleteTeamMemberRoute = "{{ route('deleteTeamMember', ['slug' => $team->slug, 'id' => ':id']) }}";
            var getTeamMemberRoute = "{{ route('getTeamMember', ['slug' => $team->slug, 'id' => ':id']) }}";
            var createSeatRoute = "{{ route('dashboardPage', ['slug' => $team->slug]) }}";
        </script>
    @endif
@endsection
