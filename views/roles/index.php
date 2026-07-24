<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Role Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-tag" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Roles</span>
                        <span class="info-box-number"><?= $statistics['total']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active</span>
                        <span class="info-box-number"><?= $statistics['active']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-times-circle" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Inactive</span>
                        <span class="info-box-number"><?= $statistics['inactive']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                            <h3 class="card-title mb-2 mb-sm-0">System Roles</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm me-2" id="btnNewRole">
                                    <i class="fas fa-plus" aria-hidden="true"></i> New Role
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tableRoles" class="table table-bordered table-hover table-striped table-sm" style="visibility: hidden;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%">ID</th>
                                    <th class="text-center" style="width: 20%">Name</th>
                                    <th class="text-center" style="width: 40%">Description</th>
                                    <th class="text-center" style="width: 12%">Users</th>
                                    <th class="text-center" style="width: 10%">Status</th>
                                    <th class="text-center" style="width: 10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role):
                                    $isActive   = ((int) $role['status']) === 1;
                                    $totalUsers = (int) ($role['total_users'] ?? 0);
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $role['id']; ?></td>
                                        <td><?= htmlspecialchars($role['name']); ?></td>
                                        <td><?= htmlspecialchars(!empty($role['description']) ? $role['description'] : 'N/A'); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $totalUsers > 0 ? 'badge-primary' : 'badge-secondary'; ?> badge-pill p-2">
                                                <?= $totalUsers; ?> <?= $totalUsers === 1 ? 'user' : 'users'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $isActive ? 'badge-success' : 'badge-danger'; ?> badge-pill p-2">
                                                <?= $isActive ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?= URL ?>roles/<?= $role['id']; ?>" class="btn btn-info btn-sm"
                                                    data-toggle="tooltip" title="View permissions">
                                                    <i class="fas fa-key" aria-hidden="true"></i>
                                                </a>
                                                <button type="button" class="btn btn-warning btn-sm btn-edit"
                                                    data-id="<?= $role['id']; ?>"
                                                    data-name="<?= htmlspecialchars($role['name']); ?>"
                                                    data-description="<?= htmlspecialchars($role['description'] ?? ''); ?>"
                                                    data-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" class="btn <?= $isActive ? 'btn-danger' : 'btn-success'; ?> btn-sm btn-toggle-status"
                                                    data-id="<?= $role['id']; ?>"
                                                    data-current-status="<?= $role['status']; ?>"
                                                    data-users="<?= $totalUsers; ?>"
                                                    data-toggle="tooltip" title="<?= $isActive ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas <?= $isActive ? 'fa-times' : 'fa-check'; ?>" aria-hidden="true"></i>
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

<?php include __DIR__ . '/_modal_role.php'; ?>