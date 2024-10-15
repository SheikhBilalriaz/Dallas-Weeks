$(document).ready(function () {
    $(document).on('click', '.delete-webhook', deleteWebhook);
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