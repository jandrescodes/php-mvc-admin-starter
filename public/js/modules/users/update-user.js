/**
 * update-user.js - User update page script
 *
 * Includes form validation, image preview, and usability
 * improvements for the user update form.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Users
 * @author jandrescodes
 * @version 1.0
 */

$(document).ready(function () {
    // ============= JQUERY VALIDATE =============

    const userId = $('input[name="user_id"]').val() || '';

    $('#formUser').validate({
        rules: {
            name: { required: true },
            first_surname: { required: true },
            document_type: { required: true },
            document_number: {
                required: true,
                remote: {
                    url: baseUrl + 'users/check-document',
                    type: 'post',
                    data: {
                        document_type: function () { return $('#document_type').val(); },
                        document_number: function () { return $('#document_number').val(); },
                        user_id: function () { return userId; }
                    }
                }
            },
            email: {
                required: true,
                email: true,
                remote: {
                    url: baseUrl + 'users/check-email',
                    type: 'post',
                    data: {
                        email: function () { return $('#email').val(); },
                        user_id: function () { return userId; }
                    }
                }
            },
            role_id: { required: true },
            password: { minlength: 8 },
            confirm_password: {
                minlength: 8,
                equalTo: {
                    param: '#password',
                    depends: function () { return $('#password').val().length > 0; }
                }
            },
            image: { extension: 'jpg|jpeg|png|gif|webp' }
        },
        messages: {
            name: { required: 'Name is required.' },
            first_surname: { required: 'First surname is required.' },
            document_type: { required: 'Select a document type.' },
            document_number: {
                required: 'Document number is required.',
                remote: 'This document is already registered.'
            },
            email: {
                required: 'Email is required.',
                email: 'Enter a valid email address.',
                remote: 'This email is already in use.'
            },
            role_id: { required: 'Select a role.' },
            password: {
                minlength: 'Password must be at least 8 characters.'
            },
            confirm_password: {
                minlength: 'Password must be at least 8 characters.',
                equalTo: 'Passwords do not match.'
            },
            image: {
                extension: 'Only images are allowed (jpg, png, gif, webp).'
            }
        },
        submitHandler: function (form) {
            const $btn = $(form).find('[type="submit"]');
            $btn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
            ToastUtils.loadingWithMinTime('Updating user...', () => {
                form.submit();
            }, 800);
        }
    });

    // Revalidate Select2 selects on change
    $('#document_type, #role_id').on('change', function () {
        $(this).valid();
    });

    $('#document_type').on('change', function () {
        if ($('#document_number').val()) $('#document_number').valid();
    });

    // ============= FIELD VALIDATION =============

    $('.custom-file-input').on('change', function () {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);

        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-image').attr('src', e.target.result);
                $('#preview-container').show();
                $('#profile-preview-img').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Document number validation based on type
    $('#document_type').on('change', function () {
        let type = $(this).val();
        let docInput = $('#document_number');

        switch (type) {
            case 'DNI':
                docInput.attr('maxlength', '8').attr('pattern', '[0-9]{8}').attr('placeholder', 'Enter 8 digits');
                break;
            case 'RUC':
                docInput.attr('maxlength', '11').attr('pattern', '[0-9]{11}').attr('placeholder', 'Enter 11 digits');
                break;
            case 'Passport':
                docInput.attr('maxlength', '12').removeAttr('pattern').attr('placeholder', 'Enter passport number');
                break;
            default:
                docInput.removeAttr('maxlength').removeAttr('pattern').attr('placeholder', 'Enter document number');
        }
    });

    // ============= USABILITY IMPROVEMENTS =============

    $('#togglePassword').click(function () {
        const field = $('#password');
        const icon = $(this).find('i');
        if (field.attr('type') === 'password') {
            field.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            field.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggleConfirmPassword').click(function () {
        const field = $('#confirm_password');
        const icon = $(this).find('i');
        if (field.attr('type') === 'password') {
            field.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            field.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Update profile preview when fields change
    $('#name, #first_surname, #second_surname').on('input', function () {
        const name = $('#name').val() || '';
        const firstSurname = $('#first_surname').val() || '';
        const secondSurname = $('#second_surname').val() || '';

        let fullName = name;
        if (firstSurname) fullName += ' ' + firstSurname;
        if (secondSurname) fullName += ' ' + secondSurname;

        $('#profile-preview-name').text(fullName || 'User');
    });

    $('#position').change(function () {
        const position = $(this).val() || 'User position';
        $('#profile-preview-role').text(position);
    });

    $('#status').change(function () {
        const status = $(this).val();
        if (status === '1') {
            $('#profile-preview-badge').removeClass('badge-danger').addClass('badge-success').text('Active');
        } else {
            $('#profile-preview-badge').removeClass('badge-success').addClass('badge-danger').text('Inactive');
        }
    });

    // Select/deselect all permissions buttons
    $('#select-all').click(function () {
        $('input[name="permissions[]"]').prop('checked', true);
        ToastUtils.success('All permissions selected');
    });

    $('#deselect-all').click(function () {
        $('input[name="permissions[]"]').prop('checked', false);
        ToastUtils.info('All permissions deselected');
    });

});
