$(document).ready(function () {
    // ============= UI INTERACTION =============

    $('.login-box').addClass('login-animation');

    // ============= JQUERY VALIDATE =============

    $('#login-form').validate({
        rules: {
            identifier: {
                required: true,
                minlength: 3
            },
            password: {
                required: true,
                minlength: 6
            }
        },
        messages: {
            identifier: {
                required: "Please enter your email or document number",
                minlength: "Identifier must be at least 3 characters"
            },
            password: {
                required: "Please enter your password",
                minlength: "Password must be at least 6 characters"
            }
        },
        submitHandler: function (form) {
            // Disable button and show spinner
            $('#btn-login').prop('disabled', true);
            $('#btn-icon').removeClass('fa-sign-in-alt').addClass('fa-spinner fa-spin');

            ToastUtils.loadingWithMinTime('Signing in...', () => {
                form.submit();
            }, 1000);
        }
    });
});
