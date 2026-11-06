<!-- Content Header -->
<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">

        <!-- Stats widgets -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= $userStats['total'] ?></h3>
                        <p>Total Users</p>
                    </div>
                    <i class="bi bi-people small-box-icon" aria-hidden="true"></i>
                    <?php if ($canManageUsers): ?>
                        <a href="<?= URL ?>users" class="small-box-footer link-light link-underline-opacity-0">
                            Manage <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer link-light link-underline-opacity-0">&nbsp;</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?= $permStats['total'] ?></h3>
                        <p>Total Permissions</p>
                    </div>
                    <i class="bi bi-key small-box-icon" aria-hidden="true"></i>
                    <?php if ($canManagePermissions): ?>
                        <a href="<?= URL ?>permissions" class="small-box-footer link-dark link-underline-opacity-0">
                            Manage <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer link-dark link-underline-opacity-0">&nbsp;</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3><?= $roleStats['total'] ?></h3>
                        <p>Total Roles</p>
                    </div>
                    <i class="bi bi-person-badge small-box-icon" aria-hidden="true"></i>
                    <?php if ($canManageRoles): ?>
                        <a href="<?= URL ?>roles" class="small-box-footer link-dark link-underline-opacity-0">
                            Manage <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer link-dark link-underline-opacity-0"><?= $roleStats['active'] ?> active</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($canViewAuditLog): ?>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3><?= $auditToday ?></h3>
                            <p>Events Today</p>
                        </div>
                        <i class="bi bi-clock-history small-box-icon" aria-hidden="true"></i>
                        <a href="<?= URL ?>audit-log" class="small-box-footer link-light link-underline-opacity-0">
                            View Log <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- /.row stats -->

        <!-- Access metrics toggle -->
        <?php if ($canManageUsers): ?>
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" id="btnToggleAccessMetrics" class="btn btn-link text-body-secondary small p-0"
                        aria-expanded="false" aria-controls="rowAccessMetrics">
                        <i class="bi bi-sliders" aria-hidden="true" id="iconToggleAccessMetrics"></i>
                        <span id="labelToggleAccessMetrics">Show access metrics</span>
                        <i class="bi bi-chevron-down" aria-hidden="true" id="arrowToggleAccessMetrics"></i>
                    </button>
                </div>
            </div>

            <!-- Access metrics row (hidden by default, toggled via localStorage) -->
            <div id="rowAccessMetrics" class="row">
                <!-- Pending Invitations -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3><?= (int) $pendingInvitations ?></h3>
                            <p>Pending Invitations</p>
                        </div>
                        <i class="bi bi-envelope-open small-box-icon" aria-hidden="true"></i>
                        <a href="<?= URL ?>users" class="small-box-footer link-dark link-underline-opacity-0">
                            View Users <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Resets This Week -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="small-box text-bg-secondary">
                        <div class="inner">
                            <h3><?= (int) $resetsThisWeek ?></h3>
                            <p>Password Resets This Week</p>
                        </div>
                        <i class="bi bi-unlock small-box-icon" aria-hidden="true"></i>
                        <span class="small-box-footer link-light link-underline-opacity-0">&nbsp;</span>
                    </div>
                </div>
            </div>
            <!-- /.row access metrics -->
        <?php endif; ?>

        <!-- Charts -->
        <div class="row dashboard-charts">
            <!-- Donut: active vs inactive users -->
            <div class="col-xl-4 col-lg-6">
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-pie-chart me-1" aria-hidden="true"></i> Users by Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartUserStatus"
                                data-active="<?= (int) $chartData['usersByStatus']['active'] ?>"
                                data-inactive="<?= (int) $chartData['usersByStatus']['inactive'] ?>"
                                data-pending="<?= (int) $chartData['usersByStatus']['pending'] ?>">
                            </canvas>
                            <p id="chartUserStatusFallback" class="text-body-secondary text-center pt-5" style="display:none;">
                                No data to display.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar: top 5 permissions by assigned users -->
            <div class="col-xl-4 col-lg-6">
                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-key me-1" aria-hidden="true"></i> Top Permissions</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTopPermissions"
                                data-chart="<?= htmlspecialchars(json_encode($chartData['topPerms'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
                            </canvas>
                            <p id="chartTopPermissionsFallback" class="text-body-secondary text-center pt-5" style="display:none;">
                                No permissions assigned yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line: user registrations per month -->
            <div class="col-xl-4 col-lg-12">
                <div class="card card-outline card-success mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-graph-up me-1" aria-hidden="true"></i> Registrations (6 months)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartUsersByMonth"
                                data-chart="<?= htmlspecialchars(json_encode($chartData['usersByMonth'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
                            </canvas>
                            <p id="chartUsersByMonthFallback" class="text-body-secondary text-center pt-5" style="display:none;">
                                No registrations in the last 6 months.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row charts -->

        <!-- Recent Users -->
        <div class="row dashboard-recent">
            <div class="col-12">
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-clock me-1" aria-hidden="true"></i> Recently Registered Users
                        </h3>
                        <?php if ($canManageUsers): ?>
                            <div class="card-tools">
                                <a href="<?= URL ?>users" class="btn btn-sm btn-primary">
                                    <i class="bi bi-people me-1" aria-hidden="true"></i> View All
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentUsers)): ?>
                            <p class="text-body-secondary text-center py-4">No users registered yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th class="d-none d-sm-table-cell">Email</th>
                                            <th>Status</th>
                                            <th class="d-none d-md-table-cell">Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']) ?></td>
                                                <td class="d-none d-sm-table-cell"><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <?php if ((int) $user['status'] === 1): ?>
                                                        <span class="badge rounded-pill text-bg-success">Active</span>
                                                    <?php elseif ((int) $user['status'] === 2): ?>
                                                        <span class="badge rounded-pill text-bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill text-bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="d-none d-md-table-cell">
                                                    <?= $user['created_at'] ? date('m/d/Y', strtotime($user['created_at'])) : '—' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row recent users -->

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->