<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Permission Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>permissions"><i class="bi bi-key" aria-hidden="true"></i> Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($permission['name']); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Left column - Permission info -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-bg-primary fs-2" style="width: 4rem; height: 4rem;" aria-hidden="true">
                                <i class="bi bi-key"></i>
                            </span>
                            <h2 class="h4 mt-2 mb-0"><?= htmlspecialchars($permission['name']); ?></h2>
                            <p class="text-body-secondary">#<?= $permission['id']; ?></p>
                        </div>

                        <?php if (!empty($permission['description'])): ?>
                            <p class="text-body-secondary text-center mb-3"><?= htmlspecialchars($permission['description']); ?></p>
                        <?php endif; ?>

                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <b><i class="bi bi-toggle-on me-1" aria-hidden="true"></i> Status</b>
                                <?php if ($permission['status'] == 1): ?>
                                    <span class="badge rounded-pill text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill text-bg-danger">Inactive</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <b><i class="bi bi-people me-1" aria-hidden="true"></i> Assigned users</b>
                                <span class="badge rounded-pill text-bg-info" id="userCount"><?= count($users); ?></span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-warning btn-edit"
                                data-id="<?= $permission['id']; ?>"
                                data-name="<?= htmlspecialchars($permission['name']); ?>"
                                data-description="<?= htmlspecialchars($permission['description'] ?? ''); ?>">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
                            </button>
                            <a href="<?= URL ?>permissions" class="btn btn-secondary">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column - Users with this permission -->
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h2 class="card-title"><i class="bi bi-people me-1" aria-hidden="true"></i> Users with this Permission</h2>
                            <div class="card-tools">
                                <?php if (!$isInactive): ?>
                                    <button type="button" class="btn btn-success btn-sm" id="btnAssignUser">
                                        <i class="bi bi-person-plus" aria-hidden="true"></i> Assign User
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($isInactive): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                                This permission is <strong>inactive</strong>. No new users can be assigned until it is reactivated.
                            </div>
                        <?php endif; ?>
                        <div class="row g-2 mb-2 align-items-center tbl-toolbar" id="detailTop">
                            <div class="col-12 col-md-6 text-center text-md-start">
                                <label class="d-inline-flex align-items-center gap-2 mb-0">
                                    Show
                                    <select class="form-select form-select-sm w-auto" id="detailPageSize" aria-label="Entries per page"></select>
                                    entries
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="d-flex align-items-center justify-content-md-end gap-2 mb-0">
                                    Search:
                                    <input type="search" class="form-control form-control-sm w-auto flex-grow-1 flex-md-grow-0" id="detailSearch">
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
                                        <div class="dropdown-menu" id="detailColumnMenu"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tablePermissionDetail" class="tbl-sm tbl-hover tbl-striped tbl-bordered"
                            data-permission-id="<?= $permissionId; ?>"
                            data-rows="<?= htmlspecialchars($usersJson, ENT_QUOTES); ?>"></div>
                        <div class="row g-2 mt-2 align-items-center tbl-toolbar" id="detailBottom">
                            <div class="col-12 col-md-5 text-center text-md-start" id="detailInfo" role="status"></div>
                            <div class="col-12 col-md-7">
                                <nav aria-label="Users pagination">
                                    <ul class="pagination pagination-sm mb-0 justify-content-center justify-content-md-end flex-wrap" id="detailPager"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_modal_permission.php'; ?>

<!-- Assign User Modal -->
<div class="modal fade" id="modalAssignUser" tabindex="-1" aria-labelledby="modalAssignUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-bg-success">
                <h5 class="modal-title" id="modalAssignUserLabel"><i class="bi bi-person-plus me-1" aria-hidden="true"></i> Assign User to Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-0">
                    <label for="selectUser" class="form-label">Select User <span class="text-danger">*</span></label>
                    <select class="tom-select" id="selectUser" placeholder="Select a user…">
                        <option value=""></option>
                        <?php foreach ($usersWithoutPerm as $u): ?>
                            <option value="<?= $u['id']; ?>">
                                <?= htmlspecialchars(trim($u['name'] . ' ' . $u['first_surname'] . ' ' . ($u['second_surname'] ?? '')) . ($u['position'] ? ' — ' . $u['position'] : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg" aria-hidden="true"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmAssign">
                    <i class="bi bi-person-plus" aria-hidden="true"></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>
