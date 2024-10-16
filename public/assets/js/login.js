var credentialAjax = null;
var forgotPasswordAjax = null;
var updatePasswordAjax = null;

$(document).ready(function () {
    $(document).on('input', '#password, #email', credential_check);
    $(document).on('submit', '#forgot_password', forgot_password);
    $(document).on('submit', '#update_password', update_password);
});

function update_password(e) {
    e.preventDefault();
    if (updatePasswordAjax) {
        return;
    }
    var formData = $(this).serialize();
    updatePasswordAjax = $.ajax({
        url: updatePasswordRoute,
        method: 'POST',
        data: formData,
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);
                $('#updatePassword').modal('hide');
                setTimeout(() => {
                    window.location = loginRoute;
                }, 3000);
            } else {
                $('#inputPasswordError').html(response.error);
                $('#update_password').find('.password').addClass('error');
            }
        },
        error: function (xhr, status, error) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            toastr.error(errorMessage);
        },
        complete: function () {
            updatePasswordAjax = null;
        },
    });
}

function forgot_password(e) {
    e.preventDefault();
    if (forgotPasswordAjax) {
        return;
    }
    var formData = $(this).serialize();
    forgotPasswordAjax = $.ajax({
        url: forgotPasswordRoute,
        method: 'POST',
        data: formData,
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);
                $('#forgetPassword').modal('hide');
                $('#forgot_password').find('.email').removeClass('error');
                $('#inputEmailError').html('');
            } else {
                $('#inputEmailError').html(response.error);
                $('#forgot_password').find('.email').addClass('error');
            }
        },
        error: function (xhr, status, error) {
            const errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
            toastr.error(errorMessage);
            $('#forgetPassword').modal('hide');
            $('#forgot_password').find('.email').removeClass('error');
            $('#inputEmailError').html('');
        },
        complete: function () {
            forgotPasswordAjax = null;
        },
    });
}

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