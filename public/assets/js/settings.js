var integrateLinkedinAjax = null;
var integrateEmailAjax = null;
var disconnectLinkedinAccountAjax = null;
var disconnectEmailAccountAjax = null;
var searchEmailAccountAjax = null;

$(document).ready(function () {
    $(document).on('click', '#submit-btn', integrateLinkedin);
    $(document).on('click', '#disconnect_account', disconnectLinkedinAccount);
    $(document).on('click', '.add_an_email', integrateEmail);
    $(document).on('click', '.delete_an_email', disconnectEmailAccount);
    $(document).on('input', '#search_emails', searchEmailAccount);
    $(document).on('click', '.email_menu_btn', function (e) {
        e.stopPropagation();
        $(".setting_list").not($(this).siblings('.setting_list')).hide();
        $(this).siblings('.setting_list').toggle();
    });
});

function searchEmailAccount() {
    if (searchEmailAccountAjax) {
        searchEmailAccountAjax.abort();
        searchEmailAccountAjax = null;
    }
    $search = $(this).val();
    if ($search == '') {
        $search = 'null';
    }
    searchEmailAccountAjax = $.ajax({
        url: searchEmailAccountRoute.replace(':search', $search),
        type: 'GET',
        success: function (response) {
            if (response.success && response.email_accounts.length > 0) {
                var html = '';
                response.email_accounts.forEach(function (email) {
                    var displayName = email?.profile?.aliases?.[0]?.display_name ||
                        email?.profile?.display_name ||
                        email?.profile?.email ||
                        email?.account?.name;
                    var userEmail = email?.profile?.email || email?.account?.name;
                    var status = email?.account?.sources?.[0]?.status === 'OK' ? 'Connected' : 'Disconnected';
                    html += `
                        <tr class="table_rows" id="table_row_${email.id}">
                            <td width="33%" style="text-align: center">${displayName}</td>
                            <td width="33%" style="text-align: center">${userEmail}</td>
                            <td class="email_status" width="33%" style="text-align: center; position: relative;">
                                <span style="margin-right: 20px;" class="${status === 'Connected' ? 'connected' : 'disconnected'}">
                                    ${status}
                                </span>
                                ${manage_email_allowed ? `
                                    <span class="email_menu_btn" style="width: 20px; display: inline-block; text-align: center;">
                                        <i class="fa-solid fa-ellipsis-vertical" style="color: #ffffff;"></i>
                                    </span>
                                    <ul class="setting_list" style="display: none; z-index: 2147483647; right: -5%; width: max-content;">
                                        <li>
                                            <a class="delete_an_email" id="${email.id}">Delete an account</a>
                                        </li>
                                    </ul>` : ''}
                            </td>
                        </tr>`;
                });
                $('#emailSetting tbody').html(html);
            } else {
                $('#emailSetting tbody').html(`
                    <td colspan="5">
                        <div class="grey_box d-flex align-items-center linked">
                            <div style="width: 50%; margin: 0 auto;"
                                class="empty_blacklist text-center">
                                <img style="margin-right: 0px; width: 50%; height: 50%; border-radius: 0;"
                                    src="${emptyImage}"
                                    alt="">
                                <p style="margin-top: 25px; font-size: 18px;">
                                    Sorry, no results for that query
                                </p>
                            </div>
                        </div>
                    </td>
                `);
            }
        },
        error: function (xhr, status, error) {
            $('#emailSetting tbody').html(`
                <td colspan="5">
                    <div class="grey_box d-flex align-items-center linked">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img style="margin-right: 0px; width: 50%; height: 50%; border-radius: 0;"
                                src="${emptyImage}"
                                alt="">
                            <p style="margin-top: 25px; font-size: 18px;">
                                Sorry, no results for that query
                            </p>
                        </div>
                    </div>
                </td>
            `);
        },
        complete: function () {
            integrateEmailAjax = null;
        },
    });
}

function integrateEmail() {
    if (!integrateEmailAjax) {
        integrateEmailAjax = $.ajax({
            url: integrateEmailroute,
            type: 'POST',
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                'provider': $(this).attr('data-provider')
            },
            success: function (response) {
                if (response.success && response.data && response.data.url) {
                    window.location = response.data.url;
                } else {
                    const errorMessage = 'Something went wrong.';
                    toastr.error(errorMessage);
                }
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            },
            complete: function () {
                integrateEmailAjax = null;
            },
        });
    }
}

function integrateLinkedin() {
    if (!integrateLinkedinAjax) {
        integrateLinkedinAjax = $.ajax({
            url: integrateLinkedinRoute,
            type: 'POST',
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success && response.data && response.data.url) {
                    window.location = response.data.url;
                } else {
                    const errorMessage = 'Something went wrong.';
                    toastr.error(errorMessage);
                }
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            },
            complete: function () {
                integrateLinkedinAjax = null;
            },
        });
    }
}

function disconnectLinkedinAccount() {
    if (!disconnectLinkedinAccountAjax) {
        if (confirm('Are you sure to disconnect linkedin account')) {
            disconnectLinkedinAccountAjax = $.ajax({
                url: disconnectLinkedinAccountRoute,
                type: 'POST',
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.success) {
                        $('#integrations').html(`
                            <div
                                class="grey_box d-flex align-items-center justify-content-between">
                                <h4 style="margin-bottom: 0;">Connect your LinkedIn account
                                </h4>
                                <button style="margin-right: 0; margin-left: auto;"
                                    id="submit-btn" type="button" class="theme_btn">
                                    Connect Linked in
                                </button>
                            </div>    
                        `);
                        toastr.success(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                    toastr.error(errorMessage);
                },
                complete: function () {
                    disconnectLinkedinAccountAjax = null;
                },
            });
        }
    }
}

function disconnectEmailAccount() {
    if (!disconnectEmailAccountAjax) {
        if (confirm('Are you sure to disconnect email account')) {
            var id = $(this).attr('id');
            disconnectEmailAccountAjax = $.ajax({
                url: disconnectEmailAccountRoute.replace(':email_id', id),
                type: 'POST',
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.success) {
                        $('#table_row_' + id).remove();
                        if ($('.table_rows').length <= 0) {
                            $('#emailSetting').append(`
                                <div class="grey_box">
                                    <div class="add_cont">
                                        <p>No email account. Start by connecting your first email
                                            account.</p>
                                        <div class="add">
                                            <a href="javascript:;" type="button" data-bs-toggle="modal"
                                                data-bs-target="#add_email"><i
                                                    class="fa-solid fa-plus"></i></a>Add email account
                                        </div>
                                    </div>
                                </div>
                            `);
                        }
                        toastr.success(response.message);
                    }
                },
                error: function (status, xhr, error) {
                    const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                    toastr.error(errorMessage);
                },
                complete: function () {
                    disconnectEmailAccountAjax = null;
                }
            });
        }
    }
}