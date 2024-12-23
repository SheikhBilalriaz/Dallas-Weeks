$(document).ready(function () {
    $(document).on('change', 'input.error', function (e) {
        e.preventDefault();
        $(this).removeClass('error');
        $(this).next('.text-danger').remove();
        if ($('input.error').length <= 0) {
            $('#submit_webhook').removeClass('disabled');
        }
    });
    $(document).on('click', '.delete-webhook', deleteWebhook);
    $(document).on('click', '.email_options', email_options);
    $(document).on('click', '.account_options', account_options);
    $(document).on('click', '#submit_webhook', function (e) {
        e.preventDefault();
        if (!$(this).hasClass('disabled')) {
            $(this).closest('form').submit();
        }
    });
});

function deleteWebhook() {
    const id = $(this).data('id');
    const $element = $('#webhook_' + id);
    if (!confirm('Are you sure you want to delete this item?')) return;
    const url = deleteWebhookRoute.replace(':id', id);
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: url,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function (response) {
            toastr.success('Deleted successfully');
            $element.remove();
            if ($('.delete-webhook').length === 0) {
                $('#webhook_row').html(`
                    <tr>
                        <td colspan="3">
                            <div class="empty_blacklist text-center" style="width: 50%; margin: 0 auto;">
                                <p>Sorry, no results for that query</p>
                            </div>
                        </td>
                    </tr>
                `);
            }
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            toastr.error(errorMessage);
        }
    });
}

function account_options() {
    $('#emails_div').empty();
    $('.email_options').prop('checked', false);
    const hasErrors = $('input.error').length > 0;
    const noEmailsChecked = $('.email_options:checked').length === 0;
    $('#submit_webhook').toggleClass('disabled', hasErrors || noEmailsChecked);
}

function email_options() {
    let html;

    if (emails.length > 0) {
        html = emails.map(email => `
            <li style="margin-bottom: 17px;">
                <input 
                    value="${email.id}" 
                    name="accounts[]" 
                    data-role="${$(this).attr('for')}" 
                    type="checkbox" 
                    id="email-${email.id}">
                <label for="email-${email.id}">${email.profile?.email || 'No Email'}</label>
            </li>
        `).join('');
    } else {
        html = `
            <p>
                <i class="fa-solid fa-triangle-exclamation" style="color: #ff0000;"></i>
                You don't have any email to manage. To continue, add new emails.
                <a href="${seatSettingsRoute}">Create seat →</a>
            </p>`;
    }
    $('#emails_div').html(html);
    $('#submit_webhook').addClass('disabled');
    $('.account_options').prop('checked', false);
}