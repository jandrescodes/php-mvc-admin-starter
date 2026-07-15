/**
 * index-roles.js - Role list page management
 *
 * Handles DataTables initialization, opening the modal in create mode,
 * and status toggling. Modal logic (edit, submit, validate, reset) is
 * centralized in modal-role.js.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Roles
 * @author jandrescodes
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('Roles', [0, 1, 2, 3], {});
    const table = $('#tableRoles').DataTable(config);
    table.buttons().container().appendTo('#tableRoles_wrapper .col-md-6:eq(0)');

    // Open modal in create mode
    $('#btnNewRole').on('click', function () {
        $('#formRole')[0].reset();
        $('#roleAction').val('create');
        $('#roleId').val('');

        $('#modalRoleHeader').removeClass('bg-warning').addClass('bg-primary');
        $('#modalRoleLabel').text('Create New Role');
        $('#btnSaveRole').removeClass('btn-warning').addClass('btn-primary')
            .html('<i class="fas fa-save"></i> Save');

        $('#modalRole').modal('show');
    });

    // Toggle role status
    $(document).on('click', '.btn-toggle-status', function () {
        const id            = $(this).data('id');
        const currentStatus = $(this).data('current-status');
        const users         = $(this).data('users');

        if (currentStatus == 1 && users > 0) {
            ToastUtils.error('Error', 'Cannot deactivate this role because it has assigned users.');
            return;
        }

        const action            = currentStatus == 1 ? 'deactivate' : 'activate';
        const actionCapitalized = action.charAt(0).toUpperCase() + action.slice(1);

        AlertUtils.confirm(
            `${actionCapitalized} this role?`,
            `The role will be ${action}d.`,
            () => {
                ToastUtils.loadingWithMinTime(`${actionCapitalized}ing role...`, () => {
                    $.ajax({
                        url: `${baseUrl}roles/toggle-status`,
                        type: 'POST',
                        dataType: 'json',
                        data: { id, current_status: currentStatus, csrf_token: csrfToken },
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
            },
            {
                confirmText: `Yes, ${action}`,
                confirmColor: currentStatus == 1 ? '#d33' : '#28a745',
                cancelColor: '#6c757d',
                cancelText: 'Cancel'
            }
        );
    });
});
