var deleteRoleAjax = null;
var editRoleAjax = null;

$(document).ready(function () {
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

    $(document).on('click', '.permission', change_permit);
    $(document).on('click', '.edit_role', edit_role);
    $(document).on('click', '.delete_role', delete_role);
});

function change_permit(e) {
    let displayStyle = $(this).prop('checked') ? 'flex' : 'none';
    $(this).parent().siblings('.switch_box').css('display', displayStyle);
}

function edit_role(e) {
    e.preventDefault();
    var id = $(this).parent().attr('id').replace('table_row_', '');

    if (!editRoleAjax) {
        editRoleAjax = $.ajax({
            url: getRoleRoute.replace(':id', id),
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let accessMap = {};
                    let viewOnlyMap = {};
                    response.permissions_to_roles.forEach(permit => {
                        accessMap[permit.permission_id] = permit.access;
                        viewOnlyMap[permit.permission_id] = permit.view_only;
                    });
                    $('#edit_role').find('#role_name_input').val(response.role.name);
                    $('#edit_role').find('.edit_form').prop('id', 'edit_role_' + response.role.id);
                    response.permissions.forEach(permission => {
                        let permissionSlug = permission.slug;
                        let target = $('#edit_role').find('#edit_permission_' + permissionSlug);
                        let access = accessMap[permission.id] || 0;
                        let viewOnly = viewOnlyMap[permission.id] || 0;
                        if (access == 1) {
                            target.click();
                            let viewTarget = $('#edit_role').find('#edit_view_only_' + permissionSlug);
                            if (viewOnly == 1) {
                                viewTarget.click();
                            }
                        }
                    });
                    $('#edit_role').modal('show');
                    var id = $('#edit_role').find('form.edit_form').attr('id').replace('edit_role_', '');
                    var action = $('#edit_role').find('form.edit_form').prop('action');
                    $('#edit_role').find('form.edit_form').prop('action', action.replace(':id', id));
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseJSON);
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    toastr.error(xhr.responseJSON.error);
                } else {
                    toastr.error('An unexpected error occurred.');
                }
            },
            complete: function () {
                editRoleAjax = null;
            }
        });
    }
}

function delete_role(e) {
    e.preventDefault();
    var id = $(this).parent().attr('id').replace('table_row_', '');
    if (!deleteRoleAjax) {
        if (confirm('Are you sure you want to delete this role?')) {
            deleteRoleAjax = $.ajax({
                url: deleteRoleRoute.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        toastr.success('Role deleted successfully.');
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseJSON);
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        toastr.error(xhr.responseJSON.error);
                    } else {
                        toastr.error('An unexpected error occurred.');
                    }
                },
                complete: function () {
                    deleteRoleAjax = null;
                }
            });
        }
    }
}