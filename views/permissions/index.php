<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Permission Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
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
                    <span class="info-box-icon text-bg-info"><i class="bi bi-key" aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Permissions</span>
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
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h2 class="card-title">System Permissions</h2>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" id="btnNewPermission">
                                    <i class="bi bi-plus-lg" aria-hidden="true"></i> New Permission
                                </button>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-2 align-items-center tbl-toolbar" id="permissionsTop">
                            <div class="col-12 col-md-6 text-center text-md-start">
                                <label class="d-inline-flex align-items-center gap-2 mb-0">
                                    Show
                                    <select class="form-select form-select-sm w-auto" id="permissionsPageSize" aria-label="Entries per page"></select>
                                    entries
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="d-flex align-items-center justify-content-md-end gap-2 mb-0">
                                    Search:
                                    <input type="search" class="form-control form-control-sm w-auto flex-grow-1 flex-md-grow-0" id="permissionsSearch">
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
                                        <div class="dropdown-menu" id="permissionsColumnMenu"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tablePermissions" class="tbl-sm tbl-hover tbl-striped tbl-bordered" data-rows="<?= htmlspecialchars($permissionsJson, ENT_QUOTES); ?>"></div>
                        <div class="row g-2 mt-2 align-items-center tbl-toolbar" id="permissionsBottom">
                            <div class="col-12 col-md-5 text-center text-md-start" id="permissionsInfo" role="status"></div>
                            <div class="col-12 col-md-7">
                                <nav aria-label="Permissions pagination">
                                    <ul class="pagination pagination-sm mb-0 justify-content-center justify-content-md-end flex-wrap" id="permissionsPager"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/_modal_permission.php'; ?>