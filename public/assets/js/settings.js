var integrateLinkedinAjax = null;
var disconnectLinkedinAccountAjax = null;

$(document).ready(function () {
    $(document).on('click', '#submit-btn', integrateLinkedin);
    $(document).on('click', '#disconnect_account', disconnectLinkedinAccount);
});

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
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
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