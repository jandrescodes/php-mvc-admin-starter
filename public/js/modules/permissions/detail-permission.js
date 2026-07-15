/**
 * detail-permission.js - Permission detail page
 *
 * Initializes the DataTable of users with this permission and manages
 * user assignment/revocation from this view.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permissions
 * @author jandrescodes
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('Users', [0, 1, 2, 3], {
        "pageLength": 10,
        "language": {
            "sEmptyTable": "No users have been assigned this permission."
        }
    });

    const table = $("#tablePermissionDetail").DataTable(config);
    table.buttons().container().appendTo('#tablePermissionDetail_wrapper .col-md-6:eq(0)');

    // Open assign modal
    $('#btnAssignUser').on('click', function () {
        $('#selectUser').val(null).trigger('change');
        $('#modalAssignUser').modal('show');
    });

    // Confirm assignment
    $('#btnConfirmAssign').on('click', function () {
        const userId = $('#selectUser').val();
        if (!userId) {
            ToastUtils.warning('Select a user', '', 2500);
            return;
        }

        $('#modalAssignUser').modal('hide');

        ToastUtils.loadingWithMinTime('Assigning user...', () => {
            $.ajax({
                url: `${baseUrl}permissions/assign-user`,
                type: 'POST',
                dataType: 'json',
                data: { user_id: userId, permission_id: permissionId, csrf_token: csrfToken },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.close();
                        ToastUtils.error('Error', response.message);
                    }
                },
                error: function () {
                    Swal.close();
                    ToastUtils.error('Error', 'A communication error occurred with the server.');
                }
            });
        }, 800);
    });

    // Reset on modal close
    $('#modalAssignUser').on('hidden.bs.modal', function () {
        $('#btnConfirmAssign').prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Assign');
    });

    // Revoke user
    $(document).on('click', '.btn-revoke', function () {
        const userId = $(this).data('user-id');
        const name = $(this).data('name');
        const $btn = $(this);

        AlertUtils.confirm(
            'Revoke permission?',
            null,
            () => {
                $btn.prop('disabled', true);
                ToastUtils.loadingWithMinTime('Revoking permission...', () => {
                    $.ajax({
                        url: `${baseUrl}permissions/revoke-user`,
                        type: 'POST',
                        dataType: 'json',
                        data: { user_id: userId, permission_id: permissionId, csrf_token: csrfToken },
                        success: function (response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                Swal.close();
                                ToastUtils.error('Error', response.message);
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function () {
                            Swal.close();
                            ToastUtils.error('Error', 'A communication error occurred with the server.');
                            $btn.prop('disabled', false);
                        }
                    });
                }, 800);
            },
            {
                html: `This permission will be removed from <b>${name}</b>.`,
                confirmText: 'Yes, revoke',
                cancelColor: '#6c757d',
                cancelText: 'Cancel'
            }
        );
    });
});
