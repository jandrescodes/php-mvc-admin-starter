<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Permission Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>permissions"><i class="fas fa-key" aria-hidden="true"></i> Permissions</a></li>
                    <li class="breadcrumb-item active">Permission Detail</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Left column - Permission info -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <span class="fa-stack fa-2x" aria-hidden="true">
                                <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                <i class="fas fa-key fa-stack-1x text-white"></i>
                            </span>
                            <h4 class="mt-2 mb-0"><?= htmlspecialchars($permission['name']); ?></h4>
                            <p class="text-muted">#<?= $permission['id']; ?></p>
                        </div>

                        <?php if (!empty($permission['description'])): ?>
                            <p class="text-muted text-center mb-3"><?= htmlspecialchars($permission['description']); ?></p>
                        <?php endif; ?>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1" aria-hidden="true"></i> Status</b>
                                <span class="float-right">
                                    <?php if ($permission['status'] == 1): ?>
                                        <span class="badge badge-success badge-pill p-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill p-2">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-users mr-1" aria-hidden="true"></i> Assigned users</b>
                                <span class="float-right">
                                    <span class="badge badge-info badge-pill p-2" id="userCount"><?= count($users); ?></span>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-warning btn-edit"
                                data-id="<?= $permission['id']; ?>"
                                data-name="<?= htmlspecialchars($permission['name']); ?>"
                                data-description="<?= htmlspecialchars($permission['description'] ?? ''); ?>">
                                <i class="fas fa-edit" aria-hidden="true"></i> Edit
                            </button>
                            <a href="<?= URL ?>permissions" class="btn btn-default">
                                <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column - Users with this permission -->
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-1" aria-hidden="true"></i> Users with this Permission</h3>
                        <div class="card-tools">
                            <?php if (!$isInactive): ?>
                                <button type="button" class="btn btn-success btn-sm mr-2" id="btnAssignUser">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i> Assign User
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($isInactive): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i>
                                This permission is <strong>inactive</strong>. No new users can be assigned until it is reactivated.
                            </div>
                        <?php endif; ?>
                        <table id="tablePermissionDetail" class="table table-bordered table-hover table-striped table-sm" data-permission-id="<?= $permissionId; ?>" style="visibility: hidden;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id']; ?></td>
                                        <td><?= htmlspecialchars($user['name'] . ' ' . $user['first_surname'] . ' ' . $user['second_surname']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td><?= htmlspecialchars($user['role_name'] ?? ''); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?= URL ?>users/<?= $user['id']; ?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="View user">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm btn-revoke"
                                                    data-user-id="<?= $user['id']; ?>"
                                                    data-name="<?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']); ?>"
                                                    data-toggle="tooltip" title="Revoke permission">
                                                    <i class="fas fa-user-minus" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/_modal_permission.php'; ?>

<!-- Assign User Modal -->
<div class="modal fade" id="modalAssignUser" tabindex="-1" role="dialog" aria-labelledby="modalAssignUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="modalAssignUserLabel"><i class="fas fa-user-plus mr-1" aria-hidden="true"></i> Assign User to Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="selectUser">Select User <span class="text-danger">*</span></label>
                    <select class="form-control select2" id="selectUser">
                        <option value="">Select a user</option>
                        <?php foreach ($usersWithoutPerm as $u): ?>
                            <option value="<?= $u['id']; ?>">
                                <?= htmlspecialchars(trim($u['name'] . ' ' . $u['first_surname'] . ' ' . ($u['second_surname'] ?? '')) . ($u['position'] ? ' — ' . $u['position'] : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times" aria-hidden="true"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmAssign">
                    <i class="fas fa-user-plus" aria-hidden="true"></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>