/**
 * modal-role.js - Shared role modal logic
 *
 * Handles opening in edit mode, jQuery Validate with remote name check,
 * form submission via AJAX, isSubmitting guard, and reset on close.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Roles
 * @author jandrescodes
 * @version 1.0
 */

/** @type {boolean} Prevents double submission while a request is in flight. */
let isSubmitting = false;

$(document).ready(function () {

    // -------------------------------------------------------------------------
    // jQuery Validate
    // -------------------------------------------------------------------------
    $('#formRole').validate({
        rules: {
            name: {
                required: true,
                maxlength: 60,
                remote: {
                    url: `${baseUrl}roles/check-name`,
                    type: 'POST',
                    data: {
                        name: () => $('#roleName').val(),
                        role_id: () => $('#roleId').val() || null,
                        csrf_token: () => csrfToken
                    }
                }
            },
            description: {
                maxlength: 255
            }
        },
        messages: {
            name: {
                required: 'Role name is required.',
                maxlength: 'Role name cannot exceed 60 characters.'
            },
            description: {
                maxlength: 'Description cannot exceed 255 characters.'
            }
        },
        submitHandler: function () {
            submitRole();
        }
    });

    // -------------------------------------------------------------------------
    // Open modal in edit mode
    // -------------------------------------------------------------------------
    $(document).on('click', '.btn-edit', function () {
        const id          = $(this).data('id');
        const name        = $(this).data('name');
        const description = $(this).data('description') || '';

        $('#roleAction').val('edit');
        $('#roleId').val(id);
        $('#roleName').val(name);
        $('#roleDescription').val(description);

        $('#modalRoleHeader').removeClass('bg-primary').addClass('bg-warning');
        $('#modalRoleLabel').text('Edit Role');
        $('#btnSaveRole').removeClass('btn-primary').addClass('btn-warning')
            .html('<i class="fas fa-save"></i> Update');

        $('#modalRole').modal('show');
    });

    // -------------------------------------------------------------------------
    // AJAX submission (triggered by validate submitHandler)
    // -------------------------------------------------------------------------
    /**
     * @returns {void}
     */
    function submitRole() {
        if (isSubmitting) return;

        const action   = $('#roleAction').val();
        const formData = $('#formRole').serialize();
        const url      = action === 'create' ? `${baseUrl}roles/create` : `${baseUrl}roles/update`;
        const toastMsg = action === 'create' ? 'Creating role...' : 'Updating role...';
        const $btn     = $('#btnSaveRole');
        const origHtml = $btn.html();

        isSubmitting = true;
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $('#modalRole').modal('hide');

        ToastUtils.loadingWithMinTime(toastMsg, () => {
            $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.close();
                        isSubmitting = false;
                        $btn.prop('disabled', false).html(origHtml);
                        ToastUtils.error('Error', response.message);
                    }
                },
                error: function () {
                    Swal.close();
                    isSubmitting = false;
                    $btn.prop('disabled', false).html(origHtml);
                    ToastUtils.error('Error', 'A communication error occurred with the server.');
                }
            });
        }, 800);
    }

    // -------------------------------------------------------------------------
    // Reset on modal close
    // -------------------------------------------------------------------------
    $('#modalRole').on('hidden.bs.modal', function () {
        isSubmitting = false;
        $('#formRole')[0].reset();
        $('#formRole').validate().resetForm();
        $('#formRole').find('.is-invalid').removeClass('is-invalid');
        $('#btnSaveRole').prop('disabled', false);
    });
});
