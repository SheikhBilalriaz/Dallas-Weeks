var customRoleAjax = null;
var inviteMemberAjax = null;
var searchMemberAjax = null;
$(document).ready(function () {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
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
    // $('.permission').on('click', function () {
    //     if ($(this).prop('checked')) {
    //         $(this).parent().siblings('div').css('display', 'flex');
    //     } else {
    //         $(this).parent().siblings('div').css('display', 'none');
    //     }
    // });
    // $(document).on('click', '.seats', function (e) {
    //     e.preventDefault();
    //     const checkboxId = $(this).attr('for');
    //     const checkbox = $(`#${checkboxId}`);
    //     checkbox.prop('checked', !checkbox.prop('checked'));
    // });
    // $(document).on('input', '#invite_email', inviteEmail);
    // $(document).on('click', '.roles', getRole);
    // $('.step_form').on('submit', custom_role);
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
            console.log(response);
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
                    html += `<tr title="${emailVerified ? '' : 'Verify your email first to view seat'}"
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
                        html += ``;
                    });
                }
                $('#team_row').html(html);
            }
            // if (response.success && response.team_member.length > 0) {
            // let html = ``;
            // response.team_member.forEach(function (element) {
            //     html += `
            //     <tr title="{{ session('email_verified') ? '' : 'Verify your email first to view seat' }}"
            //                                     style="opacity: {{ !session('email_verified') ? 0.7 : 1 }};">
            //                                     <td>
            //                                         <div class="d-flex align-items-center">
            //                                             <img style="background: #000; border-radius: 50%;"
            //                                                 src="{{ asset('assets/img/acc.png') }}" alt="">
            //                                             <strong>{{ $member_detail->name }}</strong>
            //                                         </div>
            //                                     </td>
            //                                     <td>{{ $member_detail->email }}</td>
            //                                     @php
            //                                         $member_role = \App\Models\Role::find($assigned_seat->role_id);
            //                                     @endphp
            //                                     <td>{{ $member_role->name }}</td>
            //                                     @php
            //                                         $member_seat = \App\Models\Seat::find($assigned_seat->seat_id);
            //                                         $member_seat = \App\Models\Company_Info::find(
            //                                             $member_seat->company_info_id,
            //                                         );
            //                                     @endphp
            //                                     <td>{{ $member_seat->name }}</td>
            //                                     @if (!empty($member_detail->verified_at))
            //                                         <td>
            //                                             <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
            //                                                 href="javascript:;" class="black_list_activate active">
            //                                                 Active
            //                                             </a>
            //                                         </td>
            //                                     @else
            //                                         <td>
            //                                             <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
            //                                                 href="javascript:;" class="black_list_activate non_active">
            //                                                 InActive
            //                                             </a>
            //                                         </td>
            //                                     @endif
            //                                     @if (session('is_creator'))
            //                                         <td>
            //                                             <a style="cursor: {{ !session('email_verified') ? 'auto' : 'pointer' }};"
            //                                                 href="javascript:;" type="button"
            //                                                 class="setting setting_btn" id="">
            //                                                 <i class="fa-solid fa-gear"></i>
            //                                             </a>
            //                                             @if (session('email_verified'))
            //                                                 <ul class="setting_list">
            //                                                     <li><a href="javascript:;">Edit</a></li>
            //                                                     <li><a href="javascript:;">Delete</a></li>
            //                                                 </ul>
            //                                             @endif
            //                                         </td>
            //                                     @endif
            //                                 </tr>
            //     `;
            // });
            // $('#team_row').html(html);
            // }
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
    $('.roles').siblings('input').prop('checked', false);
    $(this).siblings('input').prop('checked', true);
    $('.seat-management').remove();
    let html = `
        <div class="seat-management" style="padding: 17px;">
            <p>Select seats to manage</p>
    `;
    if (seats.length > 0) {
        const seatCheckboxes = seats.map(seat => `
            <div style="margin-bottom: 17px;">
                <input value="${seat.id}" name="seats[]" type="checkbox" id="seat-${seat.id}"> 
                <label class="seats" for="seat-${seat.id}">${seat.company_info.name}</label>
            </div>
        `).join('');
        html += seatCheckboxes;
    } else {
        html += `<p>You don't have any seats to manage. To continue add new seats.</p>`;
        $('.manage_member').addClass('disabled');
    }
    html += `</div>`;
    $(this).parent().append(html);
}

function custom_role(e) {
    e.preventDefault();
    if (!customRoleAjax) {
        let formData = new FormData(e.target);
        customRoleAjax = $.ajax({
            url: customRoleRoute,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                }
            },
            error: function (xhr, error, status) {
                console.error(error);
            },
            complete: function () {
                customRoleAjax = null;
            }
        });
    }
}