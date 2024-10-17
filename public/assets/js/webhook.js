$(document).ready(function () {
    $(document).on('click', '.delete-webhook', deleteWebhook);
    $(document).on('click', '.email_options', email_options);
    $(document).on('click', '.account_options', account_options);
    $(document).on('click', '#submit_webhook', function(e) {
        e.preventDefault();
        $(this).closest('form').submit();
    })
});

function deleteWebhook() {
    const id = $(this).data('id');
    const $element = $('#webhook_' + id);
    if (confirm('Are you sure you want to delete this item?')) {
        $.ajax({
            url: deleteWebhookRoute.replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success('Deleted succesfully');
                $element.remove();
                if ($('.delete-webhook').length == 0) {
                    $('#webhook_row').html(`
                        <tr>
                            <td colspan="3">
                                <div style="width: 50%; margin: 0 auto;" class="empty_blacklist text-center">
                                    <p>Sorry, no results for that query</p>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function (xhr, status, error) {
                const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                toastr.error(errorMessage);
            }
        });
    }
}

function account_options() {
    $('#emails_div').html(``);
    $('.email_options').prop('checked', false);
}

function email_options() {
    var html = ``;
    if (emails.length > 0) {
        const seatCheckboxes = emails.map(email => `
            <li style="margin-bottom: 17px;">
                <input value="${email.id}" name="accounts[]" data-role="${$(this).attr('for')}" 
                    type="checkbox" id="email-${email.id}"> 
                <label for="email-${email.id}">${email.profile?.email}</label>
            </li>
        `).join('');
        html += seatCheckboxes;
    } else {
        html += `<p>You don't have any email to manage. To continue add new seats.</p>`;
        $('#submit_webhook').addClass('disabled');
    }
    $('#emails_div').html(html);
    $('.account_options').prop('checked', false);
}