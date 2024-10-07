var searchAjax = null;
var deleteAjax = null;

$(document).ready(function () {
    $(document).on('input', '.error', function () {
        const $requiredFields = $(this).parent();
        $requiredFields.find('.text-danger').html('');
        $requiredFields.find('.error').removeClass('error');
    });
    $(document).on('click', '.btn-prev', btn_prev);
    $(document).on('click', '.btn-next', btn_next);
    $('#payment-form').bind('submit', paymentForm);
    $(document).on('input', '#search_seat', filter_search);
    $(document).on('click', '.setting_btn', setting_list);
    $(document).on('click', '.update_seat_name', update_seat_name);
    // $(document).on('click', '.seat_table_data', toSeat);
    // $(document).on('click', '.delete_seat', deleteSeat);
});

function btn_next(e) {
    changeStep(true);
}

function btn_prev(e) {
    changeStep(false);
}

function changeStep(isNext) {
    const $progressStep = $('.progress-step.active');
    const $formStep = $('.form-step.active');
    const $requiredFields = $formStep.find('.required');
    let isValid = true;

    if (isNext && $requiredFields.length > 0) {
        $requiredFields.each(function (index, item) {
            const $inputField = $(item).find('input');
            if ($inputField.val().trim() === "") {
                isValid = false;
                $inputField.addClass('error');
                let name = $inputField.attr('name');
                const $errorSpan = $(item).find('.text-danger');
                $errorSpan.html(toTitleCase(name) + ' is required');
            } else {
                $inputField.removeClass('error');
                const $errorSpan = $(item).find('.text-danger');
                $errorSpan.html('');
            }
        });
    }

    if (isValid) {
        const $targetProgressStep = isNext ? $progressStep.next('.progress-step') : $progressStep.prev('.progress-step');
        const $targetFormStep = isNext ? $formStep.next('.form-step') : $formStep.prev('.form-step');
        if ($targetProgressStep.length && $targetFormStep.length) {
            $progressStep.removeClass('active');
            $formStep.removeClass('active');
            $targetProgressStep.addClass('active');
            $targetFormStep.addClass('active');
            const activeIndex = $('.progress-step').index($targetProgressStep) + 1;
            $('#progress').css('width', 25 * activeIndex + '%');
        }
    }
}

function paymentForm(e) {
    e.preventDefault();
    const $form = $(this);
    const $form_steps = $form.find('.form-step');
    let isValid = true;
    $form_steps.each(function (index, form_item) {
        const $requiredFields = $(form_item).find('.required');
        if ($requiredFields.length > 0) {
            $requiredFields.each(function (index, item) {
                const $inputField = $(item).find('input');
                if ($inputField.val().trim() === "") {
                    $form_steps.removeClass('active');
                    $(form_item).addClass('active');
                    $('.progress-step').removeClass('active');
                    $progressStep = $('.progress-step');
                    $progressStep.eq(index).addClass('active');
                    $('#progress').css('width', 25 * index + '%');
                    isValid = false;
                    $inputField.addClass('error');
                    let name = $inputField.attr('name');
                    const $errorSpan = $(item).find('.text-danger');
                    $errorSpan.html(toTitleCase(name) + ' is required');
                } else {
                    $inputField.removeClass('error');
                    const $errorSpan = $(item).find('.text-danger');
                    $errorSpan.html('');
                }
            });
        }
    });
    if (isValid) {
        Stripe.setPublishableKey($(this).data('stripe-publishable-key'));
        Stripe.createToken({
            number: $('#card_number').val(),
            cvc: $('#card_cvc').val(),
            exp_month: $('#card_expiry_month').val(),
            exp_year: $('#card_expiry_year').val()
        }, stripeResponseHandler);
    }
}

function stripeResponseHandler(status, response) {
    if (response.error) {
        $('.card_number_error').parent().find('input').addClass('error');
        $('.card_number_error').html(response.error.message);
    } else {
        var token = response['id'];
        $('#stripe_token').val(token);
        $('#payment-form').get(0).submit();
    }
}

function filter_search(e) {
    e.preventDefault();
    var search = $("#search_seat").val().trim() || "null";
    if (searchAjax) {
        searchAjax.abort();
        searchAjax = null;
    }
    searchAjax = $.ajax({
        url: filterSeatRoute.replace(":search", search),
        type: "GET",
        beforeSend: function () {
            let html = ``;
            for (let index = 0; index < 5; index++) {
                html += `
                    <tr id="table_row_${index}" class="seat_table_row skel_table_row">
                        <td width="10%" class="seat_table_data">
                            <img class="seat_img" src="/assets/img/acc.png" alt="">
                        </td>
                        <td width="50%" class="text-left seat_table_data">
                            <div style="width: 250px; height: 35px; border-radius: 15px;" bis_skin_checked="1"></div>
                        </td>
                        <td width="15%" class="connection_status">
                            <div class="connected"><span></span>Connected</div>
                        </td>
                        <td width="15%" class="activeness_status">
                            <div class="active"><span></span>Active</div>
                        </td>
                        <td width="10%">
                            <a href="javascript:;" type="button" class="setting setting_btn"><i class="fa-solid fa-gear"></i></a>
                        </td>
                    </tr>
                `;
            }
            $("#campaign_table_body").html(html);
        },
        success: function (response) {
            if (response.success) {
                var seats = response.seats;
                const html = seats.map(seat => `
                    <tr title="${emailVerified ? 'Verify your email first to view seat' : ''}"
                        style="opacity:${!emailVerified ? 0.7 : 1};"
                        id="${'table_row_' + seat.id}" class="seat_table_row">
                        <td width="10%" class="seat_table_data"
                            style="cursor: ${!emailVerified ? 'auto' : 'pointer'};">
                            <img class="seat_img" 
                                src="${seat['account_profile'] && seat['account_profile']['profile_picture_url'] != ''
                        ? seat['account_profile']['profile_picture_url'] : accImage}" alt="">
                        </td>
                        <td width="50%" class="text-left seat_table_data"
                            style="cursor: ${!emailVerified ? 'auto' : 'pointer'};">
                            ${seat.company_info.name}
                        </td>
                        <td width="15%" class="connection_status">
                            ${seat.is_connected
                        ? '<div class="connected"><span></span>Connected</div>'
                        : '<div class="disconnected"><span></span>Disconnected</div>'}
                        </td>
                        <td width="15%" class="activeness_status">
                            ${seat.is_active
                        ? '<div class="active"><span></span>Active</div>'
                        : '<div class="not_active"><span></span>In Active</div>'}
                        </td>
                        <td width="10%">
                            <a href="javascript:;" type="button"
                                class="setting setting_btn"
                                style="cursor: ${!emailVerified ? 'auto' : 'pointer'};">
                                <i class="fa-solid fa-gear"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
                $("#campaign_table_body").html(html);
            }
        },
        error: function (xhr, status, error) {
            const html = getEmptyBlacklistHTML();
            $("#campaign_table_body").html(html);
        },
        complete: function () {
            searchAjax = null;
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

function setting_list(e) {
    var id = $(this).parent().parent().attr("id").replace("table_row_", "");
    $.ajax({
        url: getSeatRoute.replace(':seat_id', id),
        type: "GET",
        success: function (response) {
            if (response.success) {
                var seat = response.seat;
                var manageSeatSettings = response.manage_seat_settings;
                var cancelSubscription = response.cancel_subscription;
                var deleteSeat = response.delete_seat;
                var html = `<div class="modal-header">
                                <h4 class="text-center">
                                    Seat subscription
                                </h4>
                                <button type="button" class="close mt-1" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                                </button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="accordion" id="accordionExample">`;
                if (manageSeatSettings) {
                    html += accordionItem('One', 'Change seat name', `
                                        <div class="form-group text-left">
                                            <label for="seat_name">Seat Name:</label>
                                            <input type="text" id="seat_input_name" name="seat_name">
                                            <span class="text-danger" id="seat_input_name_error"></span>
                                        </div>
                                        <button type="button" class="update_seat_name theme_btn mb-3">Save Changes</button>`);
                } else {
                    html += accordionItem('One', 'Change seat name', `
                                        <div class="form-group text-left">
                                            <label for="seat_name">Seat Name:</label>
                                            <input type="text" id="seat_input_name" name="seat_name" readonly>
                                            <span class="text-danger fw-bold">You cannot update seat name</span>
                                        </div>`);
                }
                if (cancelSubscription) {
                    html += accordionItem('Three', 'Cancel Subscription', `
                        Are you sure you want to cancel subscription <span class="seat_name" style="color: #16adcb; font-weight: 600;"></span> seat?
                        <button type="button" class="theme_btn mb-3 delete_seat">Cancel Subscription</button>`);
                } else {
                    html += accordionItem('Three', 'Delete seat', `
                        You cannot delete this '<span class="seat_name" style="color: #16adcb; font-weight: 600;"></span>' seat.`);
                }
                if (deleteSeat) {
                    html += accordionItem('Four', 'Delete seat', `
                        Are you sure you want to delete <span class="seat_name" style="color: #16adcb; font-weight: 600;"></span> seat?
                        <button type="button" class="theme_btn mb-3 delete_seat">Delete seat</button>`);
                } else {
                    html += accordionItem('Four', 'Delete seat', `
                        You cannot delete this '<span class="seat_name" style="color: #16adcb; font-weight: 600;"></span>' seat.`);
                }
                html += `</div></div>`;
                $('#update_seat .modal-content').html(html);
                var username = seat.company_info.name;
                $('#seat_input_name').val(username);
                $('.seat_name').html(username);
                $('.delete_seat').attr('id', 'delete_seat_' + seat.id);
                $('.update_seat_name').attr('id', 'update_seat_name_' + seat.id);
                $('#update_seat').modal('show');
            }
        },
        error: function (xhr, status, error) {
            $('#update_seat').modal('hide');
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            toastr.error(errorMessage);
        }
    });
}

function accordionItem(id, title, content, expanded = false, icon = 'fa-address-card', extraClasses = '') {
    return `
        <div class="accordion-item ${extraClasses}">
            <h2 class="accordion-header" id="heading${id}">
                <button class="accordion-button ${expanded ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${id}" aria-expanded="${expanded}" aria-controls="collapse${id}">
                    <i class="fa-solid ${icon} fa-sm mr-2" style="color: #b0b0b0;"></i>${title}
                </button>
            </h2>
            <div id="collapse${id}" class="accordion-collapse collapse ${expanded ? 'show' : ''}" aria-labelledby="heading${id}" data-bs-parent="#accordionExample">
                ${content}
            </div>
        </div>`;
}

function update_seat_name(e) {
    e.preventDefault();
    var id = $(this).attr('id').replace('update_seat_name_', '');
    var name = $('#seat_input_name').val();
    if (!name.trim()) {
        $('#seat_input_name_error').html('Seat name cannot be empty.');
        $('#seat_input_name').addClass('error');
        return;
    }
    $.ajax({
        url: updateNameRoute.replace(':seat_id', id).replace(':seat_name', name),
        type: "GET",
        success: function (response) {
            if (response.success) {
                $('#update_seat').modal('hide');
                $('#table_row_' + id).find('.text-left').html(response.seat.username);
                toastr.options = toastrOptions;
                toastr.success('Seat name updated successfully.');
            }
        },
        error: function (xhr, status, error) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            $('#seat_input_name_error').html(errorMessage);
        }
    });
}

function toSeat(e) {
    var id = $(this).parent().attr("id").replace("table_row_", "");
    var form = $("<form>", {
        method: "POST",
        action: dashboardRoute
    });
    form.append(
        $("<input>", {
            type: "hidden",
            name: "_token",
            value: $('meta[name="csrf-token"]').attr("content")
        }),
        $("<input>", {
            type: "hidden",
            name: "seat_id",
            value: id
        })
    );
    form.appendTo("body").submit();
}

function deleteSeat(e) {
    e.preventDefault();
    var id = $(this).attr('id').replace('delete_seat_', '');

    var toastrOptions = {
        closeButton: true,
        debug: false,
        newestOnTop: false,
        progressBar: true,
        positionClass: "toast-bottom-right",
        preventDuplicates: false,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
    };

    if (!deleteAjax) {
        deleteAjax = $.ajax({
            url: deleteSeatRoute.replace(':seat_id', id),
            type: "GET",
            success: function (response) {
                if (response.success) {
                    $('#update_seat').modal('hide');
                    $('#table_row_' + response.seat).remove();
                    if ($('.seat_table_row').length == 0) {
                        $('#campaign_table_body').html(
                            `<tr>
                                <td colspan="8">
                                    <div class="text-center text-danger" style="font-size: 25px; font-weight: bold; font-style: italic;">
                                        Not Found!
                                    </div>
                                </td>
                            </tr>`
                        );
                    }
                    toastr.options = toastrOptions;
                    toastr.success('Seat deleted successfully.');
                }
            },
            error: function (xhr, status, error) {
                toastr.options = toastrOptions;
                toastr.error(xhr.responseJSON.errors);
            },
            complete: function () {
                deleteAjax = null;
            }
        });
        deleteAjax = null;
    }
}

function toTitleCase(str) {
    return str
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}
