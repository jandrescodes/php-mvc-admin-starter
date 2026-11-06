<!-- Content Header -->
<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Audit Log</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">

        <!-- Filters -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-secondary collapsed-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-funnel me-1" aria-hidden="true"></i> Filters</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= URL ?>audit-log" id="formFilters">
                            <!-- Fila 1: a qué pertenece el evento. Fila 2: cuándo, y las acciones al final, alineadas con los campos. -->
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label for="filterModule" class="form-label">Module</label>
                                    <select id="filterModule" name="module" class="form-select tom-select">
                                        <option value="">All</option>
                                        <?php foreach ($modules as $mod): ?>
                                            <option value="<?= htmlspecialchars($mod) ?>" <?= ($filters['module'] === $mod) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucfirst($mod)) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="filterAction" class="form-label">Action</label>
                                    <select id="filterAction" name="action" class="form-select tom-select">
                                        <option value="">All</option>
                                        <?php foreach ($actions as $act): ?>
                                            <option value="<?= htmlspecialchars($act) ?>" <?= ($filters['action'] === $act) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($act) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="filterActor" class="form-label">User</label>
                                    <select id="filterActor" name="actor_id" class="form-select tom-select">
                                        <option value="">All</option>
                                        <?php foreach ($actors as $actor): ?>
                                            <option value="<?= (int) $actor['actor_id'] ?>"
                                                <?= ((string) ($filters['actor_id']) === (string) $actor['actor_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($actor['actor_label'] ?? "User #{$actor['actor_id']}") ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="filterDateFrom" class="form-label">From</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary btn-date-picker"
                                            data-picker-target="filterDateFrom" aria-label="Open calendar for From date">
                                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        </button>
                                        <input type="date" id="filterDateFrom" name="date_from" class="form-control"
                                            max="<?= date('Y-m-d') ?>"
                                            value="<?= htmlspecialchars($filters['date_from']) ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="filterDateTo" class="form-label">To</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary btn-date-picker"
                                            data-picker-target="filterDateTo" aria-label="Open calendar for To date">
                                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        </button>
                                        <input type="date" id="filterDateTo" name="date_to" class="form-control"
                                            max="<?= date('Y-m-d') ?>"
                                            value="<?= htmlspecialchars($filters['date_to']) ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-end justify-content-md-end">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-1" aria-hidden="true"></i>Filter
                                        </button>
                                        <a href="<?= URL ?>audit-log" class="btn btn-secondary">
                                            <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Clear
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
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h3 class="card-title">
                                <i class="bi bi-clock-history me-1" aria-hidden="true"></i> Activity Log
                                <span class="badge rounded-pill text-bg-secondary ms-2"><?= (int) $logCount ?> records</span>
                            </h3>
                            <div class="card-tools">
                                <?php if (\App\Core\Auth::hasPermission('audit_log_export')): ?>
                                    <a href="<?= URL ?>audit-log/export?<?= htmlspecialchars(http_build_query($activeFilters)) ?>"
                                        class="btn btn-success btn-sm"
                                        title="Export full report (PDF)">
                                        <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Export PDF
                                    </a>
                                <?php endif ?>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-2 align-items-center tbl-toolbar" id="auditTop">
                            <div class="col-12 col-md-6 text-center text-md-start">
                                <label class="d-inline-flex align-items-center gap-2 mb-0">
                                    Show
                                    <select class="form-select form-select-sm w-auto" id="auditPageSize" aria-label="Entries per page"></select>
                                    entries
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="d-flex align-items-center justify-content-md-end gap-2 mb-0">
                                    Search:
                                    <input type="search" class="form-control form-control-sm w-auto flex-grow-1 flex-md-grow-0" id="auditSearch">
                                </label>
                            </div>
                            <div class="col-12">
                                <div class="btn-group btn-group-sm">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Reports</button>
                                        <ul class="dropdown-menu">
                                            <li><button type="button" class="dropdown-item" data-export="copy">Copy</button></li>
                                            <li><button type="button" class="dropdown-item" data-export="pdf">PDF</button></li>
                                            <li><button type="button" class="dropdown-item" data-export="xlsx">Excel</button></li>
                                            <li><button type="button" class="dropdown-item" data-export="csv">CSV</button></li>
                                            <li><button type="button" class="dropdown-item" data-export="print">Print</button></li>
                                        </ul>
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Columns</button>
                                        <div class="dropdown-menu" id="auditColumnMenu"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tableAuditLog" class="tbl-sm tbl-hover tbl-striped tbl-bordered" data-rows="<?= htmlspecialchars($logsJson, ENT_QUOTES); ?>"></div>
                        <div class="row g-2 mt-2 align-items-center tbl-toolbar" id="auditBottom">
                            <div class="col-12 col-md-5 text-center text-md-start" id="auditInfo" role="status"></div>
                            <div class="col-12 col-md-7">
                                <nav aria-label="Audit log pagination">
                                    <ul class="pagination pagination-sm mb-0 justify-content-center justify-content-md-end flex-wrap" id="auditPager"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/_modal-detail.php' ?>