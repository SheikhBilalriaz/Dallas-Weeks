let searchGlobalBlacklistAjax = null;
let searchEmailBlacklistAjax = null;
let filterGlobalBlacklistAjax = null;
let filterEmailBlacklistAjax = null;

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
    $('.tag_input_wrapper_input').on('input', inputWrapper);
    $('.global_blacklist_type').on('click', function () {
        $('.global_blacklist_type').siblings('input').prop('checked', false);
        let comparisonType = $('.global_comparison_type').parent().parent();
        if ($(this).siblings('input').val() == 'profile_url') {
            comparisonType.each(function (index, element) {
                let $element = $(element);
                if ($element.find('input').val() !== 'exact') {
                    $element.find('input').prop('checked', false);
                    $element.addClass('disabled');
                } else {
                    $element.find('input').click();
                }
            });
        } else {
            comparisonType.each(function (index, element) {
                let $element = $(element);
                $element.removeClass('disabled');
            });
        }
        $(this).siblings('input').prop('checked', true);
    });
    $('.global_comparison_type').on('click', function () {
        $('.global_comparison_type').siblings('input').prop('checked', false);
        $(this).siblings('input').prop('checked', true);
    });
    $('.email_blacklist_type').on('click', function () {
        $('.email_blacklist_type').siblings('input').prop('checked', false);
        $(this).siblings('input').prop('checked', true);
    });
    $('.email_comparison_type').on('click', function () {
        $('.email_comparison_type').siblings('input').prop('checked', false);
        $(this).siblings('input').prop('checked', true);
    });
    $('.filter_global_blacklist_type').on('click', function () {
        const input = $(this).siblings('input');
        input.prop('checked', !input.prop('checked'));
    });
    $('.filter_global_comparison_type').on('click', function () {
        const input = $(this).siblings('input');
        input.prop('checked', !input.prop('checked'));
    });
    $('.filter_email_blacklist_type').on('click', function () {
        const input = $(this).siblings('input');
        input.prop('checked', !input.prop('checked'));
    });
    $('.filter_email_comparison_type').on('click', function () {
        const input = $(this).siblings('input');
        input.prop('checked', !input.prop('checked'));
    });
    $(document).on('click', '.remove_global_blacklist_item', function () {
        $(this).parent().remove();
    });
    $(document).on('click', '.remove_email_blacklist_item', function () {
        $(this).parent().remove();
    });
    $(document).on('click', '.delete-global-blacklist', deleteGlobalBlacklist);
    $(document).on('click', '.delete-email-blacklist', deleteEmailBlacklist);
    $(document).on('input', "#search-global-blacklist", searchGlobalBlacklist);
    $(document).on('input', "#search-email-blacklist", searchEmailBlacklist);
    $(document).on('submit', '#filter-global-blacklist', filterGlobalBlacklist);
    $(document).on('submit', '#filter-email-blacklist', filterEmailBlacklist);
});

function searchGlobalBlacklist() {
    var search = $(this).val();
    if (search === "") {
        search = "null";
    }
    if (searchGlobalBlacklistAjax) {
        searchGlobalBlacklistAjax.abort();
        searchGlobalBlacklistAjax = null;
    }
    searchGlobalBlacklistAjax = $.ajax({
        url: searchGlobalBlacklistRoute.replace(':search', search),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.success && response.global_blacklist.length > 0) {
                let html = ``;
                response.global_blacklist.forEach(function (element) {
                    html += `
                        <tr id="global_blacklist_${element.id}">
                            <td class="text-center">
                                <div class="d-flex align-items-center">
                                    <strong>${element.keyword}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                ${element.blacklist_type}
                            </td>
                            <td class="text-center">
                                ${element.comparison_type}
                            </td>
                            <td class="text-center">
                                <a href="javascript:;" class="delete-global-blacklist" data-id="${element.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                $('#global_blacklist_row').html(html);
            }
        },
        error: function (xhr, status, error) {
            let html = ``;
            html += `
                <tr>
                    <td colspan="4">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img src="${emptyImage}" alt="">
                            <p>
                                Sorry, no results for that query
                            </p>
                        </div>
                    </td>
                </tr>
            `;
            $('#global_blacklist_row').html(html);
        },
        complete: function () {
            searchGlobalBlacklistAjax = null;
        }
    });
}

function searchEmailBlacklist() {
    var search = $(this).val();
    if (search === "") {
        search = "null";
    }
    if (searchEmailBlacklistAjax) {
        searchEmailBlacklistAjax.abort();
        searchEmailBlacklistAjax = null;
    }
    searchEmailBlacklistAjax = $.ajax({
        url: searchEmailBlacklistRoute.replace(':search', search),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.success && response.email_blacklist.length > 0) {
                let html = ``;
                response.email_blacklist.forEach(function (element) {
                    html += `
                        <tr id="email_blacklist_${element.id}">
                            <td class="text-center">
                                <div class="d-flex align-items-center">
                                    <strong>${element.keyword}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                ${element.blacklist_type}
                            </td>
                            <td class="text-center">
                                ${element.comparison_type}
                            </td>
                            <td class="text-center">
                                <a href="javascript:;" class="delete-email-blacklist"
                                    data-id="${element.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                $('#email_blacklist_row').html(html);
            }
        },
        error: function (xhr, status, error) {
            let html = ``;
            html += `
                <tr>
                    <td colspan="4">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img src="${emptyImage}" alt="">
                            <p>
                                Sorry, no results for that query
                            </p>
                        </div>
                    </td>
                </tr>
            `;
            $('#email_blacklist_row').html(html);
        },
        complete: function () {
            searchEmailBlacklistAjax = null;
        }
    });
}

function deleteGlobalBlacklist() {
    const id = $(this).data('id');
    const $element = $('#global_blacklist_' + id);

    if (confirm('Are you sure you want to delete this item?')) {
        $.ajax({
            url: deleteGlobalBlacklistRoute.replace(':blacklist-id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success('Deleted succesfully');
                $element.remove();
                if ($('.delete-global-blacklist').length == 0) {
                    let html = ``;
                    html += `
                        <tr>
                            <td colspan="4">
                                <div style="width: 50%; margin: 0 auto; ${!emailVerified ? ' opacity: 0.7;' : ''}"
                                    class="empty_blacklist text-center"
                                    title="${!emailVerified ? 'To add new global blacklist, you need to verify your email address first.' : ''}">
                                    <img src="${emptyImage}" alt="">
                                    <p>
                                        ${!emailVerified
                            ? "You can't add global blacklist until you verify your email address."
                            : "You don't have any global blacklist yet. Start by adding your first global blacklist."}
                                    </p>
                                    <div class="add_btn">
                                        <a href="javascript:;" type="button"
                                            data-bs-toggle="${emailVerified ? 'modal' : ''}"
                                            data-bs-target="${emailVerified ? '#addGlobalBlacklist' : ''}"
                                            style="${!emailVerified ? 'cursor: default;' : ''}">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                    $('#global_blacklist_row').html(html);
                }
            },
            error: function (xhr, status, error) {
                toastr.error('Something went wrong');
            }
        });
    }
}

function deleteEmailBlacklist() {
    const id = $(this).data('id');
    const $element = $('#email_blacklist_' + id);

    if (confirm('Are you sure you want to delete this item?')) {
        $.ajax({
            url: deleteEmailBlacklistRoute.replace(':blacklist-id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success('Deleted succesfully');
                $element.remove();
                if ($('.delete-email-blacklist').length == 0) {
                    let html = ``;
                    html += `
                        <tr>
                            <td colspan="4">
                                <div style="width: 50%; margin: 0 auto; ${!emailVerified ? ' opacity: 0.7;' : ''}"
                                    class="empty_blacklist text-center"
                                    title="${!emailVerified ? 'To add new email blacklist, you need to verify your email address first.' : ''}">
                                    <img src="${emptyImage}" alt="">
                                    <p>
                                    ${!emailVerified
                            ? "You can't add email blacklist until you verify your email address."
                            : "You don't have any email blacklist yet. Start by adding your first email blacklist."}
                                    </p>
                                    <div class="add_btn">
                                        <a href="javascript:;" type="button"
                                            style="${!emailVerified ? 'cursor: default;' : ''}"
                                            data-bs-toggle="${emailVerified ? 'modal' : ''}"
                                            data-bs-target="${emailVerified ? '#addEmailBlacklist' : ''}">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                    $('#email_blacklist_row').html(html);
                }
            },
            error: function (xhr, status, error) {
                toastr.error('Something went wrong');
            }
        });
    }
}

function inputWrapper(e) {
    let blacklistValue = $(this).val();
    let blacklistItems = blacklistValue.split(';');
    let blacklistDivId = $(this).data('div-id');
    let removeItem;
    let inputItem;

    blacklistItems.forEach((item, index) => {
        let trimmedItem = item.trim();
        if (blacklistDivId == 'global_blacklist_div') {
            removeItem = 'remove_global_blacklist_item';
        } else {
            removeItem = 'remove_email_blacklist_item';
        }
        if (blacklistDivId == 'global_blacklist_div') {
            inputItem = 'global_blacklist_item';
        } else {
            inputItem = 'email_blacklist_item';
        }
        if (trimmedItem !== '' && index < blacklistItems.length - 1) {
            $('#' + blacklistDivId).append(
                `<div class="item"><span>`
                + trimmedItem +
                `</span><span class="` + removeItem + `"><i class="fa-solid fa-xmark"></i></span>
                <input type="hidden" name="` + inputItem + `[]" value="`
                + trimmedItem +
                `"></div>`);
        } else {
            $(this).val(item);
        }
    });
}

function filterGlobalBlacklist(e) {
    if (filterGlobalBlacklistAjax) {
        filterGlobalBlacklistAjax.abort();
        filterGlobalBlacklistAjax = null;
    }
    e.preventDefault();
    var form = $(this);
    var actionUrl = form.attr('action');
    var formData = form.serialize();
    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        success: function (response) {
            if (response.success && response.global_blacklist.length > 0) {
                let html = ``;
                response.global_blacklist.forEach(function (element) {
                    html += `
                        <tr id="global_blacklist_${element.id}">
                            <td class="text-center">
                                <div class="d-flex align-items-center">
                                    <strong>${element.keyword}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                ${element.blacklist_type}
                            </td>
                            <td class="text-center">
                                ${element.comparison_type}
                            </td>
                            <td class="text-center">
                                <a href="javascript:;" class="delete-global-blacklist" data-id="${element.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                $('#global_blacklist_row').html(html);
            }
        },
        error: function (xhr) {
            let html = ``;
            html += `
                <tr>
                    <td colspan="4">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img src="${emptyImage}" alt="">
                            <p>
                                Sorry, no results for that query
                            </p>
                        </div>
                    </td>
                </tr>
            `;
            $('#global_blacklist_row').html(html);
        },
        complete: function () {
            filterGlobalBlacklistAjax = null;
            $('#filterGlobalBlacklist').modal('hide');
        }
    });
}

function filterEmailBlacklist(e) {
    if (filterEmailBlacklistAjax) {
        filterEmailBlacklistAjax.abort();
        filterEmailBlacklistAjax = null;
    }
    e.preventDefault();
    var form = $(this);
    var actionUrl = form.attr('action');
    var formData = form.serialize();
    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        success: function (response) {
            if (response.success && response.email_blacklist.length > 0) {
                let html = ``;
                response.email_blacklist.forEach(function (element) {
                    html += `
                        <tr id="email_blacklist_${element.id}">
                            <td class="text-center">
                                <div class="d-flex align-items-center">
                                    <strong>${element.keyword}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                ${element.blacklist_type}
                            </td>
                            <td class="text-center">
                                ${element.comparison_type}
                            </td>
                            <td class="text-center">
                                <a href="javascript:;" class="delete-email-blacklist"
                                    data-id="${element.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                $('#email_blacklist_row').html(html);
            }
        },
        error: function (xhr) {
            let html = ``;
            html += `
                <tr>
                    <td colspan="4">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img src="${emptyImage}" alt="">
                            <p>
                                Sorry, no results for that query
                            </p>
                        </div>
                    </td>
                </tr>
            `;
            $('#email_blacklist_row').html(html);
        },
        complete: function () {
            filterEmailBlacklistAjax = null;
            $('#filterEmailBlacklist').modal('hide');
        }
    });
}