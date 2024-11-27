let searchGlobalBlacklistAjax = null;
let searchEmailBlacklistAjax = null;
let filterGlobalBlacklistAjax = null;
let filterEmailBlacklistAjax = null;

$(document).ready(function () {

    /* Reusable function to handle type selection and disabling/enabling comparison types */
    function handleTypeSelection(typeClass, comparisonTypeClass) {
        $(document).on('click', typeClass, function () {
            $(typeClass).siblings('input').prop('checked', false);
            let comparisonType = $(comparisonTypeClass).parent().parent();

            if ($(this).siblings('input').val() == 'profile_url' || $(this).siblings('input').val() == 'lead_email') {
                comparisonType.each(function (index, element) {
                    let $element = $(element);
                    if ($element.find('input').val() !== 'exact') {
                        $element.find('input').prop('checked', false).end().addClass('disabled');
                    } else {
                        $element.find('input').click();
                    }
                });
            } else {
                comparisonType.removeClass('disabled');
            }
            $(this).siblings('input').prop('checked', true);
        });
    }

    /* Initialize type handlers for both global and email blacklists */
    handleTypeSelection('.global_blacklist_type', '.global_comparison_type');
    handleTypeSelection('.email_blacklist_type', '.email_comparison_type');

    /* Reusable function to handle comparison type selection */
    function handleComparisonSelection(comparisonTypeClass) {
        $(document).on('click', comparisonTypeClass, function () {
            $(comparisonTypeClass).siblings('input').prop('checked', false);
            $(this).siblings('input').prop('checked', true);
        });
    }

    /* Initialize comparison type handlers */
    handleComparisonSelection('.global_comparison_type');
    handleComparisonSelection('.email_comparison_type');

    /* Toggle the checked state of filter types */
    $(document).on('click', '.filter_global_blacklist_type, .filter_global_comparison_type, .filter_email_blacklist_type, .filter_email_comparison_type', function () {
        const input = $(this).siblings('input');
        input.prop('checked', !input.prop('checked'));
    });

    /* Remove blacklist items */
    $(document).on('click', '.remove_global_blacklist_item, .remove_email_blacklist_item', function () {
        $(this).parent().remove();
    });

    /* Handle input events for tag inputs */
    $(document).on('input', '.tag_input_wrapper_input', inputWrapper);

    /* Handle search input events */
    $(document).on('input', '#search-global-blacklist', searchGlobalBlacklist);
    $(document).on('input', '#search-email-blacklist', searchEmailBlacklist);

    /* Handle delete actions */
    $(document).on('click', '.delete-global-blacklist', deleteGlobalBlacklist);
    $(document).on('click', '.delete-email-blacklist', deleteEmailBlacklist);

    /* Handle filter form submissions */
    $(document).on('submit', '#filter-global-blacklist', filterGlobalBlacklist);
    $(document).on('submit', '#filter-email-blacklist', filterEmailBlacklist);
});


function searchGlobalBlacklist() {
    const search = $(this).val().trim() || "null";

    /* Abort previous request if it's ongoing */
    if (searchGlobalBlacklistAjax) {
        searchGlobalBlacklistAjax.abort();
    }

    /* Send a new AJAX request */
    searchGlobalBlacklistAjax = $.ajax({
        url: searchGlobalBlacklistRoute.replace(':search', search),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            const html = response.success && response.global_blacklist.length > 0
                ? response.global_blacklist.map(element => generateGlobalBlacklistRow(element)).join('')
                : getEmptyBlacklistHTML();

            $('#global_blacklist_row').html(html);
        },
        error: function () {
            $('#global_blacklist_row').html(getEmptyBlacklistHTML());
        },
        complete: function () {
            searchGlobalBlacklistAjax = null;
        }
    });
}

function generateGlobalBlacklistRow(element) {
    return `
        <tr id="global_blacklist_${element.id}">
            <td class="text-center">
                <div class="d-flex align-items-center">
                    <strong>${element.keyword}</strong>
                </div>
            </td>
            <td class="text-center">${element.blacklist_type}</td>
            <td class="text-center">${element.comparison_type}</td>
            <td class="text-center">
                <a href="javascript:;" class="delete-global-blacklist" data-id="${element.id}">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>
    `;
}

function searchEmailBlacklist() {
    const search = $(this).val().trim() || "null";

    /* Abort previous request if it's ongoing */
    if (searchEmailBlacklistAjax) {
        searchEmailBlacklistAjax.abort();
    }

    /* Send a new AJAX request */
    searchEmailBlacklistAjax = $.ajax({
        url: searchEmailBlacklistRoute.replace(':search', search),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            const html = response.success && response.email_blacklist.length > 0
                ? response.email_blacklist.map(element => generateBlacklistRow(element)).join('')
                : getEmptyBlacklistHTML();

            $('#email_blacklist_row').html(html);
        },
        error: function () {
            $('#email_blacklist_row').html(getEmptyBlacklistHTML());
        },
        complete: function () {
            searchEmailBlacklistAjax = null;
        }
    });
}

function generateBlacklistRow(element) {
    return `
        <tr id="email_blacklist_${element.id}">
            <td class="text-center">
                <div class="d-flex align-items-center">
                    <strong>${element.keyword}</strong>
                </div>
            </td>
            <td class="text-center">${element.blacklist_type}</td>
            <td class="text-center">${element.comparison_type}</td>
            <td class="text-center">
                <a href="javascript:;" class="delete-email-blacklist" data-id="${element.id}">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>
    `;
}

function deleteGlobalBlacklist() {
    const id = $(this).data('id');
    const $element = $(`#global_blacklist_${id}`);
    const search = $('#search-global-blacklist').val().trim();

    if (!confirm('Are you sure you want to delete this item?')) return;

    $.ajax({
        url: deleteGlobalBlacklistRoute.replace(':blacklist-id', id),
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function () {
            toastr.success('Deleted successfully');
            $element.remove();

            /* If no more global blacklists, show empty state */
            if ($('.delete-global-blacklist').length === 0) {
                const html = search === "" ? getEmptyGlobalBlacklistHTML() : getEmptyBlacklistHTML();
                $('#global_blacklist_row').html(html);
            }
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            toastr.error(errorMessage);
        }
    });
}

function getEmptyGlobalBlacklistHTML() {
    const buttonDisabledStyle = !emailVerified ? 'cursor: default;' : '';
    const modalAttributes = emailVerified
        ? `data-bs-toggle="modal" data-bs-target="#addGlobalBlacklist"`
        : '';
    const title = !emailVerified
        ? 'To add new global blacklist, you need to verify your email address first.'
        : '';
    const message = !emailVerified
        ? "You can't add global blacklist until you verify your email address."
        : "You don't have any global blacklist yet. Start by adding your first global blacklist.";

    return `
        <tr>
            <td colspan="4">
                <div style="width: 50%; margin: 0 auto; ${!emailVerified ? 'opacity: 0.7;' : ''}" 
                     class="empty_blacklist text-center" 
                     title="${title}">
                    <img src="${emptyImage}" alt="">
                    <p>${message}</p>
                    <div class="add_btn">
                        <a href="javascript:;" style="${buttonDisabledStyle}" ${modalAttributes}>
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    `;
}

function deleteEmailBlacklist() {
    const id = $(this).data('id');
    const $element = $(`#email_blacklist_${id}`);
    const search = $('#search-email-blacklist').val().trim();

    if (!confirm('Are you sure you want to delete this item?')) return;

    $.ajax({
        url: deleteEmailBlacklistRoute.replace(':blacklist-id', id),
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            toastr.success('Deleted successfully');
            $element.remove();

            /* Check if no more blacklist entries exist */
            if ($('.delete-email-blacklist').length === 0) {
                const $emailBlacklistRow = $('#email_blacklist_row');
                let html = search === "" ? getEmptyEmailBlacklistHTML() : getEmptyBlacklistHTML();
                $emailBlacklistRow.html(html);
            }
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            toastr.error(errorMessage);
        }
    });
}

function getEmptyEmailBlacklistHTML() {
    const buttonDisabledStyle = !emailVerified ? 'cursor: default;' : '';
    const modalAttributes = emailVerified
        ? `data-bs-toggle="modal" data-bs-target="#addEmailBlacklist"`
        : '';
    const title = !emailVerified
        ? 'To add new email blacklist, you need to verify your email address first.'
        : '';
    const message = !emailVerified
        ? "You can't add email blacklist until you verify your email address."
        : "You don't have any email blacklist yet. Start by adding your first email blacklist.";

    return `
        <tr>
            <td colspan="4">
                <div style="width: 50%; margin: 0 auto; ${!emailVerified ? 'opacity: 0.7;' : ''}" 
                     class="empty_blacklist text-center" 
                     title="${title}">
                    <img src="${emptyImage}" alt="">
                    <p>${message}</p>
                    <div class="add_btn">
                        <a href="javascript:;" style="${buttonDisabledStyle}" ${modalAttributes}>
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    `;
}

function filterGlobalBlacklist(e) {
    e.preventDefault();

    /* Abort any ongoing AJAX request */
    if (filterGlobalBlacklistAjax) {
        filterGlobalBlacklistAjax.abort();
        filterGlobalBlacklistAjax = null;
    }

    const $form = $(this);
    const actionUrl = $form.attr('action');
    const formData = $form.serialize();

    filterGlobalBlacklistAjax = $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        success: function (response) {
            const $globalBlacklistRow = $('#global_blacklist_row');
            let html = '';

            if (response.success && response.global_blacklist.length > 0) {
                html = response.global_blacklist.map(element => `
                    <tr id="global_blacklist_${element.id}">
                        <td class="text-center">
                            <div class="d-flex align-items-center">
                                <strong>${element.keyword}</strong>
                            </div>
                        </td>
                        <td class="text-center">${element.blacklist_type}</td>
                        <td class="text-center">${element.comparison_type}</td>
                        <td class="text-center">
                            <a href="javascript:;" class="delete-global-blacklist" data-id="${element.id}">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            } else {
                html = getEmptyBlacklistHTML();
            }

            $globalBlacklistRow.html(html);
        },
        error: function () {
            $('#global_blacklist_row').html(getEmptyBlacklistHTML());
        },
        complete: function () {
            filterGlobalBlacklistAjax = null;
            $('#filterGlobalBlacklist').modal('hide');

            const $searchInput = $('#search-global-blacklist');
            const $filterButtonSpan = $('#filterGlobalBlacklistButton span');

            let filterCount = 0;
            let isChecked = false;

            /* Check filters and count selected inputs */
            $('.filter_global_blacklist_type, .filter_global_comparison_type')
                .siblings('input')
                .each(function () {
                    if ($(this).prop('checked')) {
                        filterCount++;
                        isChecked = true;
                    }
                });

            /* Update UI elements based on filter state */
            if (isChecked) {
                $searchInput.val('').prop('disabled', true)
                    .attr('title', 'Remove filter to search...')
                    .css('opacity', '0.7');
                $filterButtonSpan.text(filterCount).addClass('span');
            } else {
                $searchInput.prop('disabled', false)
                    .attr('title', '')
                    .css('opacity', '1');
                $filterButtonSpan.text('').removeClass('span');
            }
        }
    });
}

function filterEmailBlacklist(e) {
    e.preventDefault();

    /* Abort any ongoing AJAX request */
    if (filterEmailBlacklistAjax) {
        filterEmailBlacklistAjax.abort();
        filterEmailBlacklistAjax = null;
    }

    const $form = $(this);
    const actionUrl = $form.attr('action');
    const formData = $form.serialize();

    filterEmailBlacklistAjax = $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        success: function (response) {
            const $emailBlacklistRow = $('#email_blacklist_row');
            let html = '';

            if (response.success && response.email_blacklist.length > 0) {
                html = response.email_blacklist.map(element => `
                    <tr id="email_blacklist_${element.id}">
                        <td class="text-center">
                            <div class="d-flex align-items-center">
                                <strong>${element.keyword}</strong>
                            </div>
                        </td>
                        <td class="text-center">${element.blacklist_type}</td>
                        <td class="text-center">${element.comparison_type}</td>
                        <td class="text-center">
                            <a href="javascript:;" class="delete-email-blacklist" data-id="${element.id}">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            } else {
                html = getEmptyBlacklistHTML();
            }

            $emailBlacklistRow.html(html);
        },
        error: function () {
            $('#email_blacklist_row').html(getEmptyBlacklistHTML());
        },
        complete: function () {
            filterEmailBlacklistAjax = null;
            $('#filterEmailBlacklist').modal('hide');

            const $searchInput = $('#search-email-blacklist');
            const $filterButtonSpan = $('#filterEmailBlacklistButton span');

            let filterCount = 0;
            let isChecked = false;

            /* Update the filter state */
            $('.filter_email_blacklist_type, .filter_email_comparison_type').siblings('input').each(function () {
                if ($(this).prop('checked')) {
                    filterCount++;
                    isChecked = true;
                }
            });

            /* Update UI elements based on filters */
            if (isChecked) {
                $searchInput.val('').prop('disabled', true)
                    .attr('title', 'Remove filter to search...')
                    .css('opacity', '0.7');
                $filterButtonSpan.text(filterCount).addClass('span');
            } else {
                $searchInput.prop('disabled', false)
                    .attr('title', '')
                    .css('opacity', '1');
                $filterButtonSpan.text('').removeClass('span');
            }
        }
    });
}

function getEmptyBlacklistHTML() {
    const message = "Sorry, no results for that query";
    const style = "width: 50%; margin: 0 auto;";

    return `
        <tr>
            <td colspan="4">
                <div class="empty_blacklist text-center" style="${style}">
                    <img src="${emptyImage}" alt="Empty blacklist">
                    <p>${message}</p>
                </div>
            </td>
        </tr>
    `;
}

function inputWrapper(e) {
    const $this = $(this);
    const blacklistValue = $this.val().trim();
    const blacklistItems = blacklistValue.split(';');
    const blacklistDivId = $this.data('div-id');
    const removeItemClass = blacklistDivId === 'global_blacklist_div' ? 'remove_global_blacklist_item' : 'remove_email_blacklist_item';
    const inputItemName = blacklistDivId === 'global_blacklist_div' ? 'global_blacklist_item' : 'email_blacklist_item';
    const $blacklistDiv = $('#' + blacklistDivId);

    /* Iterate over the blacklist items */
    blacklistItems.forEach((item, index) => {
        const trimmedItem = item.trim();

        /* Add only valid, non-empty items and ensure the last item remains in the input */
        if (trimmedItem && index < blacklistItems.length - 1) {
            $blacklistDiv.append(`
                <div class="item">
                    <span>${trimmedItem}</span>
                    <span class="${removeItemClass}">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                    <input type="hidden" name="${inputItemName}[]" value="${trimmedItem}">
                </div>
            `);
        } else {
            /* Retain the last item in the input */
            $this.val(item);
        }
    });
}