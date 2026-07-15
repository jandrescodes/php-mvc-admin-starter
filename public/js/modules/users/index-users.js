/**
 * index-users.js - User list page management
 *
 * Handles DataTables initialization and user status toggle functions.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Users
 * @author jandrescodes
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('Users', [0, 1, 2, 3, 4, 6, 7], {
        pageLength: 5
    });

    const table = $("#tableUsers").DataTable(config);
    table.buttons().container().appendTo('#tableUsers_wrapper .col-md-6:eq(0)');
});

document.addEventListener('DOMContentLoaded', function () {
    function handleStatusToggle(button) {
        const userId = button.dataset.id;
        const currentStatus = button.dataset.status;
        const userName = button.dataset.name;

        const alertTitle = currentStatus == 1 ? `Deactivate ${userName}?` : `Activate ${userName}?`;
        const alertText = currentStatus == 1 ? 'The user will not be able to access the system.' : 'The user will be able to access the system again.';
        const confirmText = currentStatus == 1 ? 'Yes, deactivate' : 'Yes, activate';

        AlertUtils.confirm(
            alertTitle,
            alertText,
            () => {
                ToastUtils.loadingWithMinTime(
                    currentStatus == 1 ? 'Deactivating user...' : 'Activating user...',
                    () => {
                        $.ajax({
                            url: `${baseUrl}users/toggle-status`,
                            type: 'POST',
                            dataType: 'json',
                            data: { id: userId, current_status: currentStatus, csrf_token: csrfToken },
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
                    },
                    800
                );
            },
            {
                confirmText,
                confirmColor: currentStatus == 1 ? '#d33' : '#28a745',
                cancelColor: '#6c757d',
                cancelText: 'Cancel'
            }
        );
    }

    function handleResendInvitation(button) {
        const userId   = button.dataset.id;
        const userName = button.dataset.name;

        AlertUtils.confirm(
            `Resend invitation to ${userName}?`,
            'The previous invitation link will be invalidated.',
            () => {
                ToastUtils.loadingWithMinTime('Sending invitation...', () => {
                    $.ajax({
                        url: `${baseUrl}users/${userId}/resend-invitation`,
                        type: 'POST',
                        dataType: 'json',
                        data: { csrf_token: csrfToken },
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
            { confirmText: 'Yes, resend', confirmColor: '#6c757d', cancelText: 'Cancel', cancelColor: '#adb5bd' }
        );
    }

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-resend-invitation') || e.target.closest('.btn-resend-invitation')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.classList.contains('btn-resend-invitation') ? e.target : e.target.closest('.btn-resend-invitation');
            handleResendInvitation(button);
        }

        if (e.target.classList.contains('btn-toggle-status') || e.target.closest('.btn-toggle-status')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.classList.contains('btn-toggle-status') ? e.target : e.target.closest('.btn-toggle-status');
            handleStatusToggle(button);
        }
    }, { capture: true, passive: false });

    document.body.addEventListener('touchend', function (e) {
        if (e.target.classList.contains('btn-toggle-status') || e.target.closest('.btn-toggle-status')) {
            e.preventDefault();
            const button = e.target.classList.contains('btn-toggle-status') ? e.target : e.target.closest('.btn-toggle-status');
            setTimeout(() => { handleStatusToggle(button); }, 100);
        }
    }, { passive: false });

    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        document.body.classList.add('touch-device');
    }
});
