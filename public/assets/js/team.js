var searchMemberAjax = null;
var editMemberAjax = null;

$(document).ready(function () {
    $(document).on('click', '.permission', function (e) {
        if ($(this).prop('checked')) {
            $(this).parent().siblings('div').css('display', 'flex');
        } else {
            $(this).parent().siblings('div').css('display', 'none');
        }
    });
    $(document).on('click', '.seats', function (e) {
        e.preventDefault();
        const checkboxId = $(this).attr('for');
        const checkbox = $(this).parent().find(`#${checkboxId}`);
        checkbox.prop('checked', !checkbox.prop('checked'));
        $('.invite_modal_row .edit_able_btn').addClass('disabled');
        if ($('.seat-management').length > 0) {
            let notChecked = false;
            $('.seat-management').each(function (index, element) {
                if ($(this).find('input[name^="seats"]:checked').length <= 0) {
                    notChecked = true;
                    return;
                }
            });
            if (!notChecked) {
                $('.invite_modal_row .edit_able_btn').removeClass('disabled');
            }
        }
    });
    $(document).on('input', '#invite_email', inviteEmail);
    $(document).on('input', '#edit_invite_email', inviteEmail);
    $(document).on('click', '.roles', getRole);
    $(document).on('submit', '.invite_form', inviteMember);
    $(document).on('click', '.delete-team-member', deleteMember);
    $(document).on('click', '.edit-team-member', editMember);
    $(document).on('input', '#search-team-member', searchMember);
    $(document).on('click', '.setting_btn', function () {
        var $currentList = $(this).siblings(".setting_list");
        $(".setting_list").not($currentList).hide();
        $currentList.toggle();
    });
    $(document).on("click", function (e) {
        if (!$(e.target).closest(".setting").length) {
            $(".setting_list").hide();
        }
    });
});

function editMember(e) {
    e.preventDefault();
    var id = $(this).closest('tr').prop('id').replace('table_row_', '');

    if (!editMemberAjax) {
        editMemberAjax = $.ajax({
            url: getTeamMemberRoute.replace(':id', id),
            method: 'GET',
            success: function (response) {
                const $modal = $('#edit_team_modal');
                $modal.find('#edit_member_name').val(response.user.name).prop('readonly', true);
                $modal.find('#edit_invite_email').val(response.user.email).prop('readonly', true).trigger('input');
                $modal.find('.invite_form').prop('id', 'edit_member_' + response.member.id);
                response.assigned_seats.forEach(function (assigned_seat) {
                    const $roleInput = $modal.find(`input[name="roles[]"][value="role_${assigned_seat.role_id}"]`);
                    if (!$roleInput.prop('checked')) {
                        $roleInput.siblings('.roles').trigger('click');
                    }
                    const $seatInput = $modal.find(`input[name="seats[role_${assigned_seat.role_id}][]"][value="${assigned_seat.seat_id}"]`);
                    if (!$seatInput.prop('checked')) {
                        $seatInput.siblings('.seats').trigger('click');
                    }
                });
                response.global_permissions.forEach(function (permission) {
                    $(`input[name="edit_${permission.slug}"]`).trigger('click');
                });
                $modal.modal('show');
                var id = $modal.find('form.invite_form').attr('id').replace('edit_member_', '');
                var action = $modal.find('form.invite_form').prop('action');
                $modal.find('form.invite_form').prop('action', action.replace(':id', id));
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            },
            complete: function () {
                editMemberAjax = null;
            }
        });
    }
}

function inviteMember(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    $('.input_errors').empty().hide();
    let errorFound = false;

    if ($('input[name^="roles"]:checked').length <= 0) {
        $('.input_errors').html(`Please select at least one role.`).show();
        $('.invite_modal_row .edit_able_btn').addClass('disabled');
        errorFound = true;
    }

    if (!errorFound && $('.seat-management').length > 0) {
        $('.seat-management').each(function () {
            if ($(this).find('input[name^="seats"]:checked').length <= 0) {
                var role_name = $(this).siblings('label').html();
                $('.input_errors').html(`Please select at least one seat for role "${role_name}".`).show();
                $('.invite_modal_row .edit_able_btn').addClass('disabled');
                errorFound = true;
                return false;
            }
        });
    }

    if (!errorFound) {
        form.removeClass('invite_form');
        form.submit();
    }
}

function searchMember(e) {
    var search = $(this).val();
    if (search === "") {
        search = "null";
    }
    if (searchMemberAjax) {
        searchMemberAjax.abort();
        searchMemberAjax = null;
    }
    searchMemberAjax = $.ajax({
        url: searchTeamMemberRoute.replace(':search', search),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.success) {
                let html = ``;
                if (response.creator) {
                    var td = ``;
                    if (response.creator.verified_at) {
                        td = `
                        <td>
                            <a style="cursor: ${!emailVerified ? 'auto' : 'pointer'};"
                                href="javascript:;" class="black_list_activate active">
                                Active
                            </a>
                        </td>
                        `;
                    } else {
                        td = `
                        <td>
                            <a style="cursor: ${!emailVerified ? 'auto' : 'pointer'};"
                                href="javascript:;" class="black_list_activate non_active">
                                InActive
                            </a>
                        </td>
                        `;
                    }
                    html += `<tr title="${emailVerified ? '' : 'Verify your email first to view team'}"
                                style="opacity: ${!emailVerified ? 0.7 : 1};">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img style="background: #000; border-radius: 50%;"
                                            src="${accImage}" alt="">
                                        <strong>${response.creator.name}</strong>
                                    </div>
                                </td>
                                <td>${response.creator.email}</td>
                                <td>Creator</td>
                                <td>All Seats Access</td>
                                ${td}
                                ${isCreator ? `<td></td>` : ``}
                            </tr>`;
                }
                if (response.team_member.length > 0) {
                    response.team_member.forEach(function (element) {
                        var td = ``;
                        if (element.member_deatil.verified_at) {
                            td = `<td>
                                    <a style="cursor: ${!emailVerified ? 'auto' : 'pointer'};"
                                        href="javascript:;" class="black_list_activate active">
                                        Active
                                    </a>
                                </td>`;
                        } else {
                            td = `<td>
                                    <a style="cursor: ${!emailVerified ? 'auto' : 'pointer'};"
                                        href="javascript:;" class="black_list_activate non_active">
                                        InActive
                                    </a>
                                </td>`;
                        }
                        var settingList = ``;
                        if (emailVerified) {
                            settingList += `<ul class="setting_list">
                                                <li class="edit-team-member"><a href="javascript:;">Edit</a></li>
                                                <li class="delete-team-member"><a href="javascript:;">Delete</a></li>
                                            </ul>`;
                        }
                        var creatorTd = ``;
                        if (isCreator) {
                            creatorTd += `
                            <td>
                                <a style="cursor: ${!emailVerified ? 'auto' : 'pointer'};"
                                    href="javascript:;" type="button" class="setting setting_btn"
                                    id="">
                                    <i class="fa-solid fa-gear"></i>
                                </a>
                                ${settingList}
                            </td>`;
                        }
                        html += `<tr title="${emailVerified ? '' : 'Verify your email first to view team'}"
                                    style="opacity: ${!emailVerified ? 0.7 : 1};"
                                    id="${'table_row_' + element.id}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img style="background: #000; border-radius: 50%;"
                                                src="${accImage}" alt="">
                                            <strong>${element.member_deatil.name}</strong>
                                        </div>
                                    </td>
                                    <td>${element.member_deatil.email}</td>
                                    <td>
                                        ${element.member_role}
                                    </td>
                                    <td>
                                        ${element.member_seat}
                                    </td>
                                    ${td}
                                    ${creatorTd}
                                </tr>`;
                    });
                }
                $('#team_row').html(html);
            }
        },
        error: function (xhr, status, error) {
            let html = ``;
            html += `
                <tr>
                    <td colspan="${isCreator ? '6' : '5'}">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img style="margin-right: 0px;" src="${emptyImage}" alt="">
                            <p>
                                Sorry, no results for that query
                            </p>
                        </div>
                    </td>
                </tr>
            `;
            $('#team_row').html(html);
        },
        complete: function () {
            searchMemberAjax = null;
        }
    });
}

function deleteMember() {
    const $table_row = $(this).parent().parent().parent();
    const id = $table_row.prop('id').replace('table_row_', '');
    const search = $('#search-team-member').val();
    if (confirm('Are you sure you want to delete this item?')) {
        $.ajax({
            url: deleteTeamMemberRoute.replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success('Deleted succesfully');
                $table_row.remove();
                if ($('#team_row').find('tr').length == 0) {
                    let html = ``;
                    html += `
                        <tr>
                            <td colspan="${isCreator ? '6' : '5'}">
                                <div style="width: 50%; margin: 0 auto;"
                                    class="empty_blacklist text-center">
                                    <img style="margin-right: 0px;" src="${emptyImage}" alt="">
                                    <p>
                                        Sorry, no results for that query
                                    </p>
                                </div>
                            </td>
                        </tr>
                    `;
                    $('#team_row').html(html);
                }
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            }
        });
    }
}

function inviteEmail(e) {
    var email = $(this).val();
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (emailPattern.test(email)) {
        $('.invite_modal_row .edit_able').removeClass('disabled');
    } else {
        $('.invite_modal_row .edit_able').addClass('disabled');
    }
}

function getRole(e) {
    if ($(this).siblings('input').prop('checked') != true) {
        $(this).siblings('input').prop('checked', true);
        let html = `
            <div class="seat-management" style="padding: 17px;">
                <p>Select seats to manage</p>
        `;
        if (seats.length > 0) {
            const seatCheckboxes = seats.map(seat => `
                <div style="margin-bottom: 17px;">
                    <input value="${seat.id}" name="seats[${$(this).attr('for')}][]" data-role="${$(this).attr('for')}" 
                        type="checkbox" id="seat-${seat.id}"> 
                    <label class="seats" for="seat-${seat.id}">${seat.company_info.name}</label>
                </div>
            `).join('');
            html += seatCheckboxes;
        } else {
            html += `
                    <p>
                        <i class="fa-solid fa-triangle-exclamation" style="color: #ff0000;"></i>
                        You don't have any seats to manage. To continue add new seats.
                        <a href="${createSeatRoute}">Create seat -></a>
                    </p>`;
            $('.manage_member').addClass('disabled');
        }
        html += `</div>`;
        $(this).parent().append(html);
    } else {
        $(this).siblings('input').prop('checked', false);
        $(this).siblings('.seat-management').remove()
    }
    $('.invite_modal_row .edit_able_btn').addClass('disabled');
}