var credentialAjax = null;

$(document).ready(function () {
    $('#password').on('input', credential_check);
    $('#email').on('input', credential_check);
});

function credential_check() {
    if (credentialAjax) {
        credentialAjax.abort();
        credentialAjax = null;
    }
    var email = $('#email').val();
    var password = $('#password').val();
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    credentialAjax = $.ajax({
        url: checkCredientialRoute,
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
        type: 'POST',
        data: {
            'email': email,
            'password': password
        },
        beforeSend: function () {
            $('#passwordError').html('');
            $('#successMessage').html('');
        },
        success: function (response) {
            if (response.success) {
                $('#passwordError').html('');
                $('#successMessage').html(response.message);
                $('.login_btn').html(`<a href="` + dashboardRoute + `" class="theme_btn">Login</a>`);
            } else {
                $('#passwordError').html(response.error);
                $('#successMessage').html('');
                $('.login_btn').html('');
            }
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            $('#passwordError').html(errorMessage);
            $('#successMessage').html('');
            $('.login_btn').html('');
        },
    });
}