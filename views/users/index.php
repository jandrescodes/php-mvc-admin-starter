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
                <div class="card tbl-card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-wrap align-items-center gap-2 tbl-toolbar" id="usersTop">
                            <h2 class="card-title me-auto">User Directory</h2>
                            <div class="input-group input-group-sm tbl-search">
                                <label for="usersSearch" class="visually-hidden">Search users</label>
                                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                                <input type="search" class="form-control" id="usersSearch" placeholder="Search users">
                            </div>
                            <div class="tbl-filter">
                                <label for="usersRoleFilter" class="visually-hidden">Filter by role</label>
                                <select class="form-select form-select-sm tom-select" id="usersRoleFilter">
                                    <option value="">All roles</option>
                                    <?php foreach ($roleNames as $roleName): ?>
                                        <option value="<?= htmlspecialchars($roleName); ?>"><?= htmlspecialchars($roleName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="tbl-filter">
                                <label for="usersStatusFilter" class="visually-hidden">Filter by status</label>
                                <select class="form-select form-select-sm tom-select" id="usersStatusFilter">
                                    <option value="">All statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                    <option value="2">Pending</option>
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
                                    <div class="dropdown-menu dropdown-menu-end" id="usersColumnMenu"></div>
                                </div>
                            </div>
                            <a href="<?= URL ?>users/create" class="btn btn-primary btn-sm">
                                <i class="bi bi-person-plus" aria-hidden="true"></i> New user
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="tableUsers" class="tbl-flat tbl-striped tbl-hover" data-rows="<?= htmlspecialchars($usersJson, ENT_QUOTES); ?>"></div>
                    </div>
                    <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2 tbl-toolbar" id="usersBottom">
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-3 flex-grow-1 flex-sm-grow-0">
                            <div id="usersInfo" role="status"></div>
                            <label class="d-inline-flex align-items-center gap-2 mb-0">
                                Rows
                                <select class="form-select form-select-sm w-auto" id="usersPageSize" aria-label="Rows per page"></select>
                            </label>
                        </div>
                        <nav class="mx-auto mx-sm-0 ms-sm-auto" aria-label="Users pagination">
                            <ul class="pagination pagination-sm mb-0 flex-wrap" id="usersPager"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>