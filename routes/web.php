<?php

/**
 * Application Routes
 *
 * Format: ['method', 'path', 'controller', 'middleware']
 * Middleware: 'auth', 'guest', 'perm:NAME'
 */

return [
    // Auth routes (guest-only)
    ['method' => 'GET',  'path' => '/login',           'controller' => 'Auth@showLoginForm',         'middleware' => ['guest']],
    ['method' => 'POST', 'path' => '/login',           'controller' => 'Auth@login',                 'middleware' => ['guest']],
    ['method' => 'GET',  'path' => '/forgot-password', 'controller' => 'Auth@showForgotPasswordForm', 'middleware' => ['guest']],
    ['method' => 'POST', 'path' => '/forgot-password', 'controller' => 'Auth@requestPasswordReset',  'middleware' => ['guest']],
    ['method' => 'GET',  'path' => '/reset-password',      'controller' => 'PasswordReset@showResetPasswordForm', 'middleware' => []],
    ['method' => 'POST', 'path' => '/reset-password',      'controller' => 'PasswordReset@resetPassword',         'middleware' => []],
    ['method' => 'GET',  'path' => '/accept-invitation',   'controller' => 'Invitation@showAcceptForm',           'middleware' => []],
    ['method' => 'POST', 'path' => '/accept-invitation',   'controller' => 'Invitation@acceptInvitation',         'middleware' => []],
    ['method' => 'POST', 'path' => '/logout',          'controller' => 'Auth@logout',                'middleware' => []],

    // Dashboard
    ['method' => 'GET', 'path' => '/',          'controller' => 'Dashboard@index', 'middleware' => ['auth']],
    ['method' => 'GET', 'path' => '/dashboard', 'controller' => 'Dashboard@index', 'middleware' => ['auth']],

    // Users
    ['method' => 'GET',  'path' => '/users',              'controller' => 'User@index',             'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/users/create',       'controller' => 'User@create',            'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users',              'controller' => 'User@store',             'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/users/(\d+)',        'controller' => 'User@show',              'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/users/(\d+)/edit',  'controller' => 'User@edit',              'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users/update',      'controller' => 'User@updateAction',      'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/profile',           'controller' => 'User@profile',           'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/profile',           'controller' => 'User@processUpdateProfile', 'middleware' => ['auth']],

    // Users AJAX
    ['method' => 'POST', 'path' => '/users/check-email',             'controller' => 'User@checkEmail',        'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/users/check-document',          'controller' => 'User@checkDocument',     'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/users/toggle-status',           'controller' => 'User@toggleStatusAjax',  'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users/change-password',         'controller' => 'User@ajaxChangePassword', 'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/users/(\d+)/unlock-login',      'controller' => 'User@unlockLoginAjax',      'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users/(\d+)/resend-invitation', 'controller' => 'User@resendInvitationAjax', 'middleware' => ['auth', 'perm:users']],

    // Permissions
    ['method' => 'GET', 'path' => '/permissions',       'controller' => 'Permission@pageIndex', 'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'GET', 'path' => '/permissions/(\d+)', 'controller' => 'Permission@detail',    'middleware' => ['auth', 'perm:permissions']],

    // Permissions AJAX
    ['method' => 'POST', 'path' => '/permissions/create',           'controller' => 'Permission@create',          'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/update',           'controller' => 'Permission@update',          'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/toggle-status',    'controller' => 'Permission@toggleStatus',    'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/assign-user',      'controller' => 'Permission@assignUser',      'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/revoke-user',      'controller' => 'Permission@revokeUser',      'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'GET',  'path' => '/permissions/get-users-without', 'controller' => 'Permission@getUsersWithout', 'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/check-name',        'controller' => 'Permission@checkName',       'middleware' => ['auth']],

    // Roles
    ['method' => 'GET',  'path' => '/roles',               'controller' => 'Role@pageIndex',      'middleware' => ['auth', 'perm:roles']],
    ['method' => 'GET',  'path' => '/roles/(\d+)',         'controller' => 'Role@detail',         'middleware' => ['auth', 'perm:roles']],

    // Audit Log (read-only)
    ['method' => 'GET', 'path' => '/audit-log/export', 'controller' => 'AuditLog@export', 'middleware' => ['auth', 'perm:audit_log_export']],
    ['method' => 'GET', 'path' => '/audit-log', 'controller' => 'AuditLog@index', 'middleware' => ['auth', 'perm:audit_log']],

    // Roles AJAX
    ['method' => 'POST', 'path' => '/roles/create',            'controller' => 'Role@create',         'middleware' => ['auth', 'perm:roles']],
    ['method' => 'POST', 'path' => '/roles/update',            'controller' => 'Role@update',         'middleware' => ['auth', 'perm:roles']],
    ['method' => 'POST', 'path' => '/roles/toggle-status',     'controller' => 'Role@toggleStatus',   'middleware' => ['auth', 'perm:roles']],
    ['method' => 'POST', 'path' => '/roles/sync-permissions',  'controller' => 'Role@syncPermissions', 'middleware' => ['auth', 'perm:roles']],
    ['method' => 'POST', 'path' => '/roles/check-name',        'controller' => 'Role@checkName',      'middleware' => ['auth']],

];
