<!-- Content Header -->
<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">User Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="card-title">User List</h3>
                            <div class="card-tools">
                                <a href="<?= URL ?>users/create" class="btn btn-primary btn-sm">
                                    <i class="bi bi-person-plus" aria-hidden="true"></i> New User
                                </a>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-2 align-items-center tbl-toolbar" id="usersTop">
                            <div class="col-12 col-md-6 text-center text-md-start">
                                <label class="d-inline-flex align-items-center gap-2 mb-0">
                                    Show
                                    <select class="form-select form-select-sm w-auto" id="usersPageSize" aria-label="Entries per page"></select>
                                    entries
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="d-flex align-items-center justify-content-md-end gap-2 mb-0">
                                    Search:
                                    <input type="search" class="form-control form-control-sm w-auto flex-grow-1 flex-md-grow-0" id="usersSearch">
                                </label>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    Role:
                                    <select class="form-select form-select-sm tom-select" id="usersRoleFilter">
                                        <option value="">All</option>
                                        <?php foreach ($roleNames as $roleName): ?>
                                            <option value="<?= htmlspecialchars($roleName); ?>"><?= htmlspecialchars($roleName); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    Status:
                                    <select class="form-select form-select-sm tom-select" id="usersStatusFilter">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="2">Pending</option>
                                    </select>
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
                                        <div class="dropdown-menu" id="usersColumnMenu"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tableUsers" class="tbl-sm tbl-hover tbl-striped tbl-bordered" data-rows="<?= htmlspecialchars($usersJson, ENT_QUOTES); ?>"></div>
                        <div class="row g-2 mt-2 align-items-center tbl-toolbar" id="usersBottom">
                            <div class="col-12 col-md-5 text-center text-md-start" id="usersInfo" role="status"></div>
                            <div class="col-12 col-md-7">
                                <nav aria-label="Users pagination">
                                    <ul class="pagination pagination-sm mb-0 justify-content-center justify-content-md-end flex-wrap" id="usersPager"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>