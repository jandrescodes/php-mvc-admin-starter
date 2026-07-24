<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Stats widgets -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $userStats['total'] ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon"><i class="fas fa-users" aria-hidden="true"></i></div>
                    <?php if ($canManageUsers): ?>
                        <a href="<?= URL ?>users" class="small-box-footer">
                            Manage <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer">&nbsp;</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $permStats['total'] ?></h3>
                        <p>Total Permissions</p>
                    </div>
                    <div class="icon"><i class="fas fa-key" aria-hidden="true"></i></div>
                    <?php if ($canManagePermissions): ?>
                        <a href="<?= URL ?>permissions" class="small-box-footer">
                            Manage <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer">&nbsp;</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $roleStats['total'] ?></h3>
                        <p>Total Roles</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-tag" aria-hidden="true"></i></div>
                    <?php if ($canManageRoles): ?>
                        <a href="<?= URL ?>roles" class="small-box-footer">
                            Manage <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer"><?= $roleStats['active'] ?> active</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($canViewAuditLog): ?>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $auditToday ?></h3>
                            <p>Events Today</p>
                        </div>
                        <div class="icon"><i class="fas fa-history" aria-hidden="true"></i></div>
                        <a href="<?= URL ?>audit-log" class="small-box-footer">
                            View Log <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
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
                    <button type="button" id="btnToggleAccessMetrics" class="btn btn-link text-secondary small p-0"
                        aria-expanded="false" aria-controls="rowAccessMetrics">
                        <i class="fas fa-sliders-h" aria-hidden="true" id="iconToggleAccessMetrics"></i>
                        <span id="labelToggleAccessMetrics">Show access metrics</span>
                        <i class="fas fa-chevron-down" aria-hidden="true" id="arrowToggleAccessMetrics"></i>
                    </button>
                </div>
            </div>

            <!-- Access metrics row (hidden by default, toggled via localStorage) -->
            <div id="rowAccessMetrics" class="row">
                <!-- Pending Invitations -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= (int) $pendingInvitations ?></h3>
                            <p>Pending Invitations</p>
                        </div>
                        <div class="icon"><i class="fas fa-envelope-open-text" aria-hidden="true"></i></div>
                        <a href="<?= URL ?>users" class="small-box-footer">
                            View Users <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Resets This Week -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= (int) $resetsThisWeek ?></h3>
                            <p>Password Resets This Week</p>
                        </div>
                        <div class="icon"><i class="fas fa-lock-open" aria-hidden="true"></i></div>
                        <span class="small-box-footer">&nbsp;</span>
                    </div>
                </div>
            </div>
            <!-- /.row access metrics -->
        <?php endif; ?>

        <!-- Charts -->
        <div class="row dashboard-charts">
            <!-- Donut: active vs inactive users -->
            <div class="col-xl-4 col-lg-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-circle-notch mr-1" aria-hidden="true"></i> Users by Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartUserStatus"
                                data-active="<?= (int) $chartData['usersByStatus']['active'] ?>"
                                data-inactive="<?= (int) $chartData['usersByStatus']['inactive'] ?>"
                                data-pending="<?= (int) $chartData['usersByStatus']['pending'] ?>">
                            </canvas>
                            <p id="chartUserStatusFallback" class="text-muted text-center pt-5" style="display:none;">
                                No data to display.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar: top 5 permissions by assigned users -->
            <div class="col-xl-4 col-lg-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key mr-1" aria-hidden="true"></i> Top Permissions</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTopPermissions"
                                data-chart="<?= htmlspecialchars(json_encode($chartData['topPerms'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
                            </canvas>
                            <p id="chartTopPermissionsFallback" class="text-muted text-center pt-5" style="display:none;">
                                No permissions assigned yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line: user registrations per month -->
            <div class="col-xl-4 col-lg-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-1" aria-hidden="true"></i> Registrations (6 months)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartUsersByMonth"
                                data-chart="<?= htmlspecialchars(json_encode($chartData['usersByMonth'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
                            </canvas>
                            <p id="chartUsersByMonthFallback" class="text-muted text-center pt-5" style="display:none;">
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
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1" aria-hidden="true"></i> Recently Registered Users
                        </h3>
                        <?php if ($canManageUsers): ?>
                            <div class="card-tools">
                                <a href="<?= URL ?>users" class="btn btn-sm btn-primary">
                                    <i class="fas fa-users mr-1" aria-hidden="true"></i> View All
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentUsers)): ?>
                            <p class="text-muted text-center py-4">No users registered yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
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
                                                        <span class="badge badge-success badge-pill p-2">Active</span>
                                                    <?php elseif ((int) $user['status'] === 2): ?>
                                                        <span class="badge badge-warning badge-pill p-2">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger badge-pill p-2">Inactive</span>
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