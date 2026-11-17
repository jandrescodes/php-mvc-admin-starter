<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Role Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon text-bg-info"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Roles</span>
                        <span class="info-box-number"><?= $statistics['total']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon text-bg-success"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active</span>
                        <span class="info-box-number"><?= $statistics['active']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon text-bg-warning"><i class="bi bi-x-circle" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Inactive</span>
                        <span class="info-box-number"><?= $statistics['inactive']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card tbl-card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-wrap align-items-center gap-2 tbl-toolbar" id="rolesTop">
                            <h2 class="card-title me-auto">System Roles</h2>
                            <div class="input-group input-group-sm tbl-search">
                                <label for="rolesSearch" class="visually-hidden">Search roles</label>
                                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                                <input type="search" class="form-control" id="rolesSearch" placeholder="Search roles">
                            </div>
                            <div class="tbl-filter">
                                <label for="rolesStatusFilter" class="visually-hidden">Filter by status</label>
                                <select class="form-select form-select-sm tom-select" id="rolesStatusFilter">
                                    <option value="">All statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Reports" title="Reports">
                                        <i class="bi bi-download" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item" data-export="copy">Copy</button></li>
                                        <li><button type="button" class="dropdown-item" data-export="pdf">PDF</button></li>
                                        <li><button type="button" class="dropdown-item" data-export="xlsx">Excel</button></li>
                                        <li><button type="button" class="dropdown-item" data-export="csv">CSV</button></li>
                                        <li><button type="button" class="dropdown-item" data-export="print">Print</button></li>
                                    </ul>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Columns" title="Columns">
                                        <i class="bi bi-layout-three-columns" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" id="rolesColumnMenu"></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="btnNewRole">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i> New role
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="tableRoles" class="tbl-flat tbl-striped tbl-hover" data-rows="<?= htmlspecialchars($rolesJson, ENT_QUOTES); ?>"></div>
                    </div>
                    <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2 tbl-toolbar" id="rolesBottom">
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-3 flex-grow-1 flex-sm-grow-0">
                            <div id="rolesInfo" role="status"></div>
                            <label class="d-inline-flex align-items-center gap-2 mb-0">
                                Rows
                                <select class="form-select form-select-sm w-auto" id="rolesPageSize" aria-label="Rows per page"></select>
                            </label>
                        </div>
                        <nav class="mx-auto mx-sm-0 ms-sm-auto" aria-label="Roles pagination">
                            <ul class="pagination pagination-sm mb-0 flex-wrap" id="rolesPager"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/_modal_role.php'; ?>