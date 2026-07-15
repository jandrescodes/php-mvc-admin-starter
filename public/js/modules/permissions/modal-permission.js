/**
 * modal-permission.js - Shared permission modal logic
 *
 * Handles opening in edit mode, jQuery Validate with remote name check,
 * form submission via AJAX, isSubmitting guard, and reset on close.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permissions
 * @author jandrescodes
 * @version 1.1
 */

/** @type {boolean} Prevents double submission while a request is in flight. */
let isSubmitting = false;

$(document).ready(function () {

    // -------------------------------------------------------------------------
    // jQuery Validate
    // -------------------------------------------------------------------------
    $('#formPermission').validate({
        rules: {
            name: {
                required: true,
                maxlength: 60,
                remote: {
                    url: `${baseUrl}permissions/check-name`,
                    type: 'POST',
                    data: {
                        name:          () => $('#name').val(),
                        permission_id: () => $('#permissionId').val() || null,
                        csrf_token:    () => csrfToken
                    }
                }
            },
            description: {
                maxlength: 255
            }
        },
        messages: {
            name: {
                required:  'Permission name is required.',
                maxlength: 'Permission name cannot exceed 60 characters.',
                remote:    'This permission name already exists.'
            },
            description: {
                maxlength: 'Description cannot exceed 255 characters.'
            }
        },
        submitHandler: function () {
            submitPermission();
        }
    });

    // -------------------------------------------------------------------------
    // Open modal in edit mode
    // -------------------------------------------------------------------------
    $(document).on('click', '.btn-edit', function () {
        const id          = $(this).data('id');
        const name        = $(this).data('name');
        const description = $(this).data('description') || '';

        $('#permissionAction').val('edit');
        $('#permissionId').val(id);
        $('#name').val(name);
        $('#description').val(description);

        $('#modalPermissionHeader').removeClass('bg-warning').addClass('bg-warning');
        $('#modalPermissionLabel').text('Edit Permission');
        $('#btnSavePermission').removeClass('btn-primary').addClass('btn-warning')
            .html('<i class="fas fa-save"></i> Update');

        $('#modalPermission').modal('show');
    });

    // -------------------------------------------------------------------------
    // AJAX submission (triggered by validate submitHandler)
    // -------------------------------------------------------------------------
    /**
     * @returns {void}
     */
    function submitPermission() {
        if (isSubmitting) return;

        const action   = $('#permissionAction').val();
        const formData = $('#formPermission').serialize();
        const url      = action === 'create'
            ? `${baseUrl}permissions/create`
            : `${baseUrl}permissions/update`;
        const toastMsg = action === 'create' ? 'Creating permission...' : 'Updating permission...';
        const $btn     = $('#btnSavePermission');
        const origHtml = $btn.html();

        isSubmitting = true;
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $('#modalPermission').modal('hide');

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
    $('#modalPermission').on('hidden.bs.modal', function () {
        isSubmitting = false;
        $('#formPermission')[0].reset();
        $('#formPermission').validate().resetForm();
        $('#formPermission').find('.is-invalid').removeClass('is-invalid');
        $('#permissionAction').val('create');
        $('#modalPermissionHeader').removeClass('bg-warning').addClass('bg-primary');
        $('#modalPermissionLabel').text('New Permission');
        $('#btnSavePermission').removeClass('btn-warning').addClass('btn-primary')
            .html('<i class="fas fa-save"></i> Save');
    });
});
