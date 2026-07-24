<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Role Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>roles"><i class="fas fa-user-tag" aria-hidden="true"></i> Roles</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($role['name']) ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- Left column — Role info -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <span class="fa-stack fa-2x" aria-hidden="true">
                                <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                <i class="fas fa-user-tag fa-stack-1x text-white"></i>
                            </span>
                            <h4 class="mt-2 mb-0"><?= htmlspecialchars($role['name']) ?></h4>
                            <p class="text-muted">#<?= $role['id'] ?></p>
                        </div>

                        <?php if (!empty($role['description'])): ?>
                            <p class="text-muted text-center mb-3"><?= htmlspecialchars($role['description']) ?></p>
                        <?php endif; ?>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1" aria-hidden="true"></i> Status</b>
                                <span class="float-right">
                                    <?php if ($role['status'] == 1): ?>
                                        <span class="badge badge-success badge-pill p-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill p-2">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <?php if (!empty($role['is_system'])): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-shield-alt mr-1" aria-hidden="true"></i> System role</b>
                                    <span class="float-right">
                                        <span class="badge badge-warning badge-pill p-2">Protected</span>
                                    </span>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item">
                                <b><i class="fas fa-key mr-1" aria-hidden="true"></i> Permissions</b>
                                <span class="float-right">
                                    <span class="badge badge-info badge-pill p-2" id="permCount"><?= count($assignedIds) ?></span>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-center">
                            <a href="<?= URL ?>roles" class="btn btn-default">
                                <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column — Permission assignment -->
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key mr-1" aria-hidden="true"></i> Permission Assignment</h3>
                    </div>

                    <?php if (!empty($role['is_system'])): ?>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-shield-alt mr-1" aria-hidden="true"></i>
                                This is a <strong>system role</strong>. It has full access by default and does not require individual permission assignment.
                            </div>
                        </div>
                    <?php else: ?>

                        <form id="formSyncPermissions">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="role_id" value="<?= $role['id'] ?>">

                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="select-all">
                                                <i class="fas fa-check-square mr-1" aria-hidden="true"></i> Select all
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                                                <i class="fas fa-square mr-1" aria-hidden="true"></i> Deselect all
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <?php foreach ($allPermissions as $perm): ?>
                                        <div class="col-md-4">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" class="custom-control-input perm-checkbox"
                                                    id="perm_<?= $perm['id'] ?>"
                                                    name="permissions[]"
                                                    value="<?= $perm['id'] ?>"
                                                    <?= in_array((int) $perm['id'], $assignedIds) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="perm_<?= $perm['id'] ?>">
                                                    <?= htmlspecialchars($perm['name']) ?>
                                                    <?php if (!empty($perm['description'])): ?>
                                                        <small class="text-muted d-block"><?= htmlspecialchars($perm['description']) ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-12 col-sm-auto">
                                        <button type="submit" class="btn btn-primary w-100" id="btnSavePermissions">
                                            <i class="fas fa-save mr-1" aria-hidden="true"></i> Save Permissions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>