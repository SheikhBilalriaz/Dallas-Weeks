@extends('dashboard/partials/master')
@php
    use App\Models\Role_Permission;
@endphp
<style>
    span.edit_role {
        cursor: pointer;
    }

    span.edit_role:hover {
        color: #0f0;
    }

    span.delete_role {
        cursor: pointer;
    }

    span.delete_role:hover {
        color: #f00;
    }

    .global-blacklist__back-arrow * {
        color: #16adcb;
        margin-bottom: 15px !important;
    }

    .global-blacklist__back-arrow:hover * {
        color: #fff !important;
    }

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
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@if (session('is_creator'))
    <script src="{{ asset('assets/js/roles&permission.js') }}"></script>
@endif
@section('content')
    <section class="blacklist team_management role_per_sec">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 filtr_desc">
                    <div class="filter_head_row filt_opt d-flex">
                        <div class="cont">
                            <a class="d-block global-blacklist__back-arrow"
                                href="{{ route('teamPage', ['slug' => $team->slug]) }}">
                                <span><i class="fa-solid fa-chevron-left"></i></span>
                                <span>Back</span>
                            </a>
                            <h3>Roles & permissions</h3>
                        </div>
                        @if (session('is_creator'))
                            <div>
                                <div class="add_btn " bis_skin_checked="1">
                                    <span style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#create_new_role">
                                        <a href="javascript:;" class="">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                        Create custom role
                                    </span>
                                </div>
                                <div class="text-center">{{ $count_role }}/10 customized roles</div>
                            </div>
                        @endif
                    </div>
                    <div class="data_row">
                        <div class="data_head">
                            <table class="data_table w-100">
                                <thead>
                                    <tr>
                                        <th width="70%">Permission</th>
                                        @if ($roles->isNotEmpty())
                                            @foreach ($roles as $role)
                                                <th class="text-center" id="{{ 'table_row_' . $role['id'] }}">
                                                    {{ $role['name'] }}
                                                    @if (session('is_creator'))
                                                        {!! $role['team_id'] == 0
                                                            ? ''
                                                            : '<span class="edit_role"><i class="fa-solid fa-pencil"></i></span> <span class="delete_role"><i class="fa-solid fa-trash"></i></span>' !!}
                                                    @endif
                                                </th>
                                            @endforeach
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($permissions->isNotEmpty())
                                        @foreach ($permissions as $permission)
                                            <tr>
                                                <td class="per">{{ $permission['name'] }}</td>
                                                @if ($roles->isNotEmpty())
                                                    @foreach ($roles as $role)
                                                        @php
                                                            $role_permission = Role_Permission::where(
                                                                'role_id',
                                                                $role['id'],
                                                            )
                                                                ->where('permission_id', $permission['id'])
                                                                ->first();
                                                        @endphp
                                                        @if (!empty($role_permission))
                                                            @if ($role_permission['view_only'] == 1)
                                                                <td><span class="">View Only</span></td>
                                                            @elseif ($role_permission['access'] == 1)
                                                                <td><span class="check checked"></span></td>
                                                            @else
                                                                <td><span class="check unchecked"></span></td>
                                                            @endif
                                                        @else
                                                            <td><span class="check unchecked"></span></td>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (session('is_creator'))
        <div class="modal fade create_sequence_modal step_form_popup invite_team_modal " id="create_new_role" tabindex="-1"
            aria-labelledby="create_new_role" aria-hidden="true">
            <div class="modal-dialog" style="width: 40%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="create_new_role">Create a custom role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body" bis_skin_checked="1">
                        <form class="invite_form" id="">
                            <label for="role_name" id="role_name">Role name</label>
                            <input type="text" name="role_name" id="role_name_input" required
                                placeholder="Enter role name">
                            <div class="text-danger" id="name_error">{{ $errors->first('role_name') }}</div>
                            <div>
                                @if ($permissions->isNotEmpty())
                                    @foreach ($permissions as $permission)
                                        <div class="row invite_modal_row ">
                                            <div class="checkboxes" style="display: flex; width: 60%;">
                                                <input class="permission"
                                                    style="width: 25px; height: 25px; margin-right: 25px;" type="checkbox"
                                                    id="permission_{{ $permission['slug'] }}"
                                                    name="{{ $permission['slug'] }}">
                                                <label
                                                    for="permission_{{ $permission['slug'] }}">{{ $permission['name'] }}</label>
                                            </div>
                                            <div class="switch_box" style="display: none; width: 30%;">
                                                @if ($permission->allow_view_only == 1)
                                                    <input type="checkbox"
                                                        style="width: 25px; height: 25px; margin-right: 25px;"
                                                        id="view_only_{{ $permission['slug'] }}" class="view_only switch"
                                                        name="view_only_{{ $permission['slug'] }}">
                                                    <label for="view_only_{{ $permission['slug'] }}"></label>
                                                    View Only
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="submit" class="btn btn-next btn-theme">Create Role</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (session('is_creator'))
        <div class="modal fade create_sequence_modal step_form_popup invite_team_modal " id="edit_role" tabindex="-1"
            aria-labelledby="edit_role" aria-hidden="true">
            <div class="modal-dialog" style="width: 40%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="edit_role">Edit role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body" bis_skin_checked="1">
                        <form class="edit_form" method="POST">
                            @method('PUT')
                            <label for="role_name">Role name</label>
                            <input type="text" name="role_name" id="role_name_input" required
                                placeholder="Enter role name">
                            <div class="text-danger" id="name_error">{{ $errors->first('role_name') }}</div>
                            <div>
                                @if ($permissions->isNotEmpty())
                                    @foreach ($permissions as $permission)
                                        <div class="row invite_modal_row ">
                                            <div class="col-lg-6 checkboxes" style="display: flex; width: 60%;">
                                                <input class="permission"
                                                    style="width: 25px; height: 25px; margin-right: 25px;" type="checkbox"
                                                    id="edit_permission_{{ $permission['slug'] }}"
                                                    name="{{ $permission['slug'] }}">
                                                <label
                                                    for="edit_permission_{{ $permission['slug'] }}">{{ $permission['name'] }}</label>
                                            </div>
                                            <div class="col-lg-6 switch_box" style="display: none; width: 30%;">
                                                @if ($permission->allow_view_only == 1)
                                                    <input type="checkbox"
                                                        style="width: 25px; height: 25px; margin-right: 25px;"
                                                        id="edit_view_only_{{ $permission['slug'] }}"
                                                        class="view_only switch"
                                                        name="view_only_{{ $permission['slug'] }}">
                                                    <label for="edit_view_only_{{ $permission['slug'] }}"></label>
                                                    View Only
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="submit" class="btn btn-next btn-theme">Edit Role</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (session('is_creator'))
        <script>
            var customRoleRoute = "{{ route('customRole', ['slug' => $team->slug]) }}";
            var getRoleRoute = "{{ route('getRole', ['slug' => $team->slug, 'id' => ':id']) }}";
            var editRoleRoute = "{{ route('editRole', ['slug' => $team->slug, 'id' => ':id']) }}";
            var deleteRoleRoute = "{{ route('deleteRole', ['slug' => $team->slug, 'id' => ':id']) }}";
        </script>
    @endif
    <script>
        $(document).ready(function() {
            if ("{{ session()->has('custom_role_error') }}") {
                $('#create_new_role').modal('show');
            }
            if ("{{ session()->has('custom_role_edit_error') }}") {
                $('#edit_role').modal('show');
            }
        });
    </script>
@endsection
