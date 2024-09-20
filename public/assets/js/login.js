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
                $('.login_btn').html(`<a href="` + dashboardPageRoute + `" class="theme_btn">Login</a>`);
            } else {
                $('#passwordError').html(response.error);
                $('#successMessage').html('');
                $('.login_btn').html();
            }
        },
        error: function (xhr) {
            let errorMessage = 'Something went wrong';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    errorMessage = response.error;
                }
            } catch (e) {
                errorMessage = xhr.responseText;
            }
            $('#passwordError').html(errorMessage);
            $('#successMessage').html('');
            $('.login_btn').html('');
        },
    });
}