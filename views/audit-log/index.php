<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Audit Log</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active">Audit Log</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Filters -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-secondary collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter mr-1" aria-hidden="true"></i> Filters</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= URL ?>audit-log" id="formFilters">

                            <!-- Row 1 — filtros a ancho completo -->
                            <div class="row">
                                <!-- Module -->
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="form-group mb-2">
                                        <label for="filterModule">Module</label>
                                        <select id="filterModule" name="module" class="form-control select2" style="width:100%">
                                            <option value="">All</option>
                                            <?php foreach ($modules as $mod): ?>
                                                <option value="<?= htmlspecialchars($mod) ?>"
                                                    <?= ($filters['module'] === $mod) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars(ucfirst($mod)) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Action -->
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="form-group mb-2">
                                        <label for="filterAction">Action</label>
                                        <select id="filterAction" name="action" class="form-control select2" style="width:100%">
                                            <option value="">All</option>
                                            <?php foreach ($actions as $act): ?>
                                                <option value="<?= htmlspecialchars($act) ?>"
                                                    <?= ($filters['action'] === $act) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($act) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Actor -->
                                <div class="col-12 col-md-4 col-lg-3">
                                    <div class="form-group mb-2">
                                        <label for="filterActor">User</label>
                                        <select id="filterActor" name="actor_id" class="form-control select2" style="width:100%">
                                            <option value="">All</option>
                                            <?php foreach ($actors as $actor): ?>
                                                <option value="<?= (int) $actor['actor_id'] ?>"
                                                    <?= ((string)($filters['actor_id']) === (string)$actor['actor_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($actor['actor_label'] ?? "User #{$actor['actor_id']}") ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Date from -->
                                <div class="col-12 col-md-4 col-lg-2">
                                    <div class="form-group mb-2">
                                        <label for="filterDateFrom">From</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <button type="button" class="input-group-text btn-date-picker"
                                                    data-picker-target="filterDateFrom"
                                                    aria-label="Open calendar for From date">
                                                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <input type="date" id="filterDateFrom" name="date_from"
                                                class="form-control"
                                                max="<?= date('Y-m-d') ?>"
                                                value="<?= htmlspecialchars($filters['date_from']) ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Date to -->
                                <div class="col-12 col-md-4 col-lg-2">
                                    <div class="form-group mb-2">
                                        <label for="filterDateTo">To</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <button type="button" class="input-group-text btn-date-picker"
                                                    data-picker-target="filterDateTo"
                                                    aria-label="Open calendar for To date">
                                                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <input type="date" id="filterDateTo" name="date_to"
                                                class="form-control"
                                                max="<?= date('Y-m-d') ?>"
                                                value="<?= htmlspecialchars($filters['date_to']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2 — botones -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search mr-1" aria-hidden="true"></i>Filter
                                        </button>
                                        <a href="<?= URL ?>audit-log" class="btn btn-default">
                                            <i class="fas fa-times mr-1" aria-hidden="true"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-1" aria-hidden="true"></i> Activity Log
                            <span class="badge badge-secondary ml-2"><?= count($logs) ?> records</span>
                        </h3>
                        <div class="card-tools">
                            <?php if (\App\Core\Auth::hasPermission('audit_log_export')): ?>
                                <a href="<?= URL ?>audit-log/export?<?= htmlspecialchars(http_build_query($activeFilters)) ?>"
                                   class="btn btn-success btn-sm mr-2"
                                   title="Export full report (PDF)">
                                    <i class="fas fa-file-pdf mr-1" aria-hidden="true"></i>Export PDF
                                </a>
                            <?php endif ?>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tableAuditLog"
                            class="table table-bordered table-hover table-striped table-sm"
                            style="visibility: hidden;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:4%">ID</th>
                                    <th class="text-center" style="width:15%">Date / Time</th>
                                    <th class="text-center" style="width:20%">User</th>
                                    <th class="text-center" style="width:10%">Module</th>
                                    <th class="text-center" style="width:12%">Action</th>
                                    <th class="text-center" style="width:12%">IP</th>
                                    <th class="text-center no-export" style="width:6%">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log):
                                    $logJson = htmlspecialchars(json_encode([
                                        'id'          => (int) $log['id'],
                                        'created_at'  => $log['created_at'],
                                        'actor_id'    => $log['actor_id'] !== null ? (int) $log['actor_id'] : null,
                                        'actor_label' => $log['actor_label'] ?? '',
                                        'module'      => $log['module'],
                                        'action'      => $log['action'],
                                        'description' => $log['description'] ?? '',
                                        'ip'          => $log['ip_address'] ?? '',
                                        'user_agent'  => $log['user_agent'] ?? '',
                                        'details'     => $log['details'] ? json_decode($log['details'], true) : null,
                                    ], JSON_UNESCAPED_UNICODE));
                                ?>
                                    <tr data-log="<?= $logJson ?>">
                                        <td class="text-center"><?= (int) $log['id'] ?></td>
                                        <td class="text-center text-nowrap">
                                            <?= htmlspecialchars(date('Y-m-d H:i', strtotime($log['created_at']))) ?>
                                        </td>
                                        <td>
                                            <?php if ($log['actor_label']): ?>
                                                <?= htmlspecialchars($log['actor_label']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $moduleBadge = match ($log['module']) {
                                                'auth'        => 'badge-info',
                                                'users'       => 'badge-primary',
                                                'roles'       => 'badge-warning',
                                                'permissions' => 'badge-secondary',
                                                'audit_log'   => 'badge-success',
                                                default       => 'badge-dark',
                                            };
                                            ?>
                                            <span class="badge <?= $moduleBadge ?> badge-pill px-2">
                                                <?= htmlspecialchars(ucfirst($log['module'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <code class="small"><?= htmlspecialchars($log['action']) ?></code>
                                        </td>
                                        <td class="text-center text-monospace small">
                                            <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-info btn-sm btn-detail"
                                                data-toggle="tooltip"
                                                title="View detail"
                                                aria-label="View detail">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/_modal-detail.php' ?>