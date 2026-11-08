<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Role Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>roles"><i class="bi bi-person-badge" aria-hidden="true"></i> Roles</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($role['name']) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">

            <!-- Left column — Role info -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-bg-primary fs-2" style="width: 4rem; height: 4rem;" aria-hidden="true">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <h2 class="h4 mt-2 mb-0"><?= htmlspecialchars($role['name']) ?></h2>
                            <p class="text-body-secondary">#<?= $role['id'] ?></p>
                        </div>

                        <?php if (!empty($role['description'])): ?>
                            <p class="text-body-secondary text-center mb-3"><?= htmlspecialchars($role['description']) ?></p>
                        <?php endif; ?>

                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <b><i class="bi bi-toggle-on me-1" aria-hidden="true"></i> Status</b>
                                <span>
                                    <?php if ($role['status'] == 1): ?>
                                        <span class="badge rounded-pill text-bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <?php if (!empty($role['is_system'])): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <b><i class="bi bi-shield-check me-1" aria-hidden="true"></i> System role</b>
                                    <span>
                                        <span class="badge rounded-pill text-bg-warning">Protected</span>
                                    </span>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <b><i class="bi bi-key me-1" aria-hidden="true"></i> Permissions</b>
                                <span>
                                    <span class="badge rounded-pill text-bg-info" id="permCount"><?= count($assignedIds) ?></span>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-center">
                            <a href="<?= URL ?>roles" class="btn btn-secondary">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column — Permission assignment -->
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h2 class="card-title"><i class="bi bi-key me-1" aria-hidden="true"></i> Permission Assignment</h2>
                    </div>

                    <?php if (!empty($role['is_system'])): ?>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
                                This is a <strong>system role</strong>. It has full access by default and does not require individual permission assignment.
                            </div>
                        </div>
                    <?php else: ?>

                        <form id="formSyncPermissions" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="role_id" value="<?= $role['id'] ?>">

                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="select-all">
                                                <i class="bi bi-check-square me-1" aria-hidden="true"></i> Select all
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                                                <i class="bi bi-square me-1" aria-hidden="true"></i> Deselect all
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <?php foreach ($allPermissions as $perm): ?>
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input perm-checkbox"
                                                    id="perm_<?= $perm['id'] ?>"
                                                    name="permissions[]"
                                                    value="<?= $perm['id'] ?>"
                                                    <?= in_array((int) $perm['id'], $assignedIds) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= $perm['id'] ?>">
                                                    <?= htmlspecialchars($perm['name']) ?>
                                                    <?php if (!empty($perm['description'])): ?>
                                                        <small class="text-body-secondary d-block"><?= htmlspecialchars($perm['description']) ?></small>
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
                                            <i class="bi bi-floppy me-1" aria-hidden="true"></i> Save Permissions
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
</div>