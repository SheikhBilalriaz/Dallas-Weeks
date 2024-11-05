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
    $(document).on('click', '.global_blacklist_type', function () {
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
    $(document).on('click', '.email_blacklist_type', function () {
        $('.email_blacklist_type').siblings('input').prop('checked', false);
        let comparisonType = $('.email_blacklist_type').parent().parent();
        if ($(this).siblings('input').val() == 'lead_email') {
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
    $(document).on('click', '.global_comparison_type', function () {
        $('.global_comparison_type').siblings('input').prop('checked', false);
        $(this).siblings('input').prop('checked', true);
    });
    $(document).on('click', '.filter_global_blacklist_type, .filter_global_comparison_type, .filter_email_blacklist_type, .filter_email_comparison_type', function () {
        const input = $(this).siblings('input');
        input.prop('checked', !input.prop('checked'));
    });
    $(document).on('click', '.remove_global_blacklist_item, .remove_email_blacklist_item', function () {
        $(this).parent().remove();
    });
    $(document).on('input', '.tag_input_wrapper_input', inputWrapper);
    $(document).on('input', '#search-global-blacklist', searchGlobalBlacklist);
    $(document).on('input', '#search-email-blacklist', searchEmailBlacklist);
    $(document).on('click', '.delete-global-blacklist', deleteGlobalBlacklist);
    $(document).on('click', '.delete-email-blacklist', deleteEmailBlacklist);
    $(document).on('submit', '#filter-global-blacklist', filterGlobalBlacklist);
    $(document).on('submit', '#filter-email-blacklist', filterEmailBlacklist);
});

function searchGlobalBlacklist() {
    var search = $(this).val().trim() || "null";
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
            let html = ``;
            if (response.success && response.global_blacklist.length > 0) {
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
            } else {
                html += getEmptyBlacklistHTML();
            }
            $('#global_blacklist_row').html(html);
        },
        error: function (xhr, status, error) {
            let html = getEmptyBlacklistHTML();
            $('#global_blacklist_row').html(html);
        },
        complete: function () {
            searchGlobalBlacklistAjax = null;
        }
    });
}

function searchEmailBlacklist() {
    var search = $(this).val().trim() || "null";
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
            let html = ``;
            if (response.success && response.email_blacklist.length > 0) {
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
            } else {
                html += getEmptyBlacklistHTML();
            }
            $('#email_blacklist_row').html(html);
        },
        error: function (xhr, status, error) {
            let html = getEmptyBlacklistHTML();
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
    const search = $('#search-global-blacklist').val().trim();

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
                    if (search == "") {
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
                    } else {
                        html += getEmptyBlacklistHTML();
                    }
                    $('#global_blacklist_row').html(html);
                }
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            }
        });
    }
}

function deleteEmailBlacklist() {
    const id = $(this).data('id');
    const $element = $('#email_blacklist_' + id);
    const search = $('#search-email-blacklist').val().trim();

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
                    if (search == "") {
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
                    } else {
                        html += getEmptyBlacklistHTML();
                    }
                    $('#email_blacklist_row').html(html);
                }
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            }
        });
    }
}

function filterGlobalBlacklist(e) {
    e.preventDefault();

    if (filterGlobalBlacklistAjax) {
        filterGlobalBlacklistAjax.abort();
        filterGlobalBlacklistAjax = null;
    }

    var form = $(this);
    var actionUrl = form.attr('action');
    var formData = form.serialize();

    filterGlobalBlacklistAjax = $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        success: function (response) {
            let html = ``;
            if (response.success && response.global_blacklist.length > 0) {
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
            } else {
                html += getEmptyBlacklistHTML();
            }
            $('#global_blacklist_row').html(html);
        },
        error: function (xhr) {
            let html = getEmptyBlacklistHTML();
            $('#global_blacklist_row').html(html);
        },
        complete: function () {
            filterGlobalBlacklistAjax = null;
            $('#filterGlobalBlacklist').modal('hide');
            let count = 0;
            let isChecked = false;
            $('.filter_global_blacklist_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    count++;
                }
            })
            $('.filter_global_comparison_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    count++;
                }
            });
            $('.filter_global_blacklist_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    isChecked = true;
                    return false;
                }
            });
            $('.filter_global_comparison_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    isChecked = true;
                    return false;
                }
            });
            if (isChecked) {
                $('#search-global-blacklist').val('');
                $('#search-global-blacklist').prop('disabled', true);
                $('#search-global-blacklist').attr('title', 'Remove filter to search...');
                $('#search-global-blacklist').css('opacity', '0.7');
                $('#filterGlobalBlacklistButton span').html(`${count}`);
                $('#filterGlobalBlacklistButton span').addClass('span');
            } else {
                $('#search-global-blacklist').prop('disabled', false);
                $('#search-global-blacklist').attr('title', '');
                $('#search-global-blacklist').css('opacity', '1');
                $('#filterGlobalBlacklistButton span').html(``);
                $('#filterGlobalBlacklistButton span').removeClass('span');
            }
        }
    });
}

function filterEmailBlacklist(e) {
    e.preventDefault();

    if (filterEmailBlacklistAjax) {
        filterEmailBlacklistAjax.abort();
        filterEmailBlacklistAjax = null;
    }

    var form = $(this);
    var actionUrl = form.attr('action');
    var formData = form.serialize();

    filterEmailBlacklistAjax = $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        success: function (response) {
            let html = ``;
            if (response.success && response.email_blacklist.length > 0) {
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
            } else {
                html += getEmptyBlacklistHTML();
            }
            $('#email_blacklist_row').html(html);
        },
        error: function (xhr) {
            let html = getEmptyBlacklistHTML();
            $('#email_blacklist_row').html(html);
        },
        complete: function () {
            filterEmailBlacklistAjax = null;
            $('#filterEmailBlacklist').modal('hide');
            let count = 0;
            let isChecked = false;
            $('.filter_email_blacklist_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    count++;
                }
            })
            $('.filter_email_comparison_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    count++;
                }
            });
            $('.filter_email_blacklist_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    isChecked = true;
                    return false;
                }
            });
            $('.filter_email_comparison_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    isChecked = true;
                    return false;
                }
            });
            if (isChecked) {
                $('#search-email-blacklist').val('');
                $('#search-email-blacklist').prop('disabled', true);
                $('#search-email-blacklist').attr('title', 'Remove filter to search...');
                $('#search-email-blacklist').css('opacity', '0.7');
                $('#filterEmailBlacklistButton span').html(`${count}`);
                $('#filterEmailBlacklistButton span').addClass('span');
            } else {
                $('#search-email-blacklist').prop('disabled', false);
                $('#search-email-blacklist').attr('title', '');
                $('#search-email-blacklist').css('opacity', '1');
                $('#filterEmailBlacklistButton span').html(``);
                $('#filterEmailBlacklistButton span').removeClass('span');
            }
        }
    });
}

function getEmptyBlacklistHTML() {
    return `
        <tr>
            <td colspan="4">
                <div style="width: 50%; margin: 0 auto;" class="empty_blacklist text-center">
                    <img src="${emptyImage}" alt="">
                    <p>Sorry, no results for that query</p>
                </div>
            </td>
        </tr>
    `;
}

function inputWrapper(e) {
    let blacklistValue = $(this).val();
    let blacklistItems = blacklistValue.split(';');
    let blacklistDivId = $(this).data('div-id');
    let removeItem = blacklistDivId == 'global_blacklist_div' ? 'remove_global_blacklist_item' : 'remove_email_blacklist_item';
    let inputItem = blacklistDivId == 'global_blacklist_div' ? 'global_blacklist_item' : 'email_blacklist_item';

    blacklistItems.forEach((item, index) => {
        let trimmedItem = item.trim();
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