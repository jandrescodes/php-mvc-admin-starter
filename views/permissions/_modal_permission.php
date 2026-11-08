<!-- Modal de permiso (compartido entre el listado y el detalle; crear y editar) -->
<div class="modal fade" id="modalPermission" tabindex="-1" aria-labelledby="modalPermissionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-bg-primary" id="modalPermissionHeader">
                <h5 class="modal-title" id="modalPermissionLabel">Create New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPermission" method="post" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                    <input type="hidden" id="permissionAction" name="action" value="create">
                    <input type="hidden" id="permissionId" name="id" value="">

                    <div class="mb-3">
                        <label for="permissionName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="permissionName" name="name" maxlength="60" required autocomplete="off">
                        <div class="form-text">Unique name for the permission (max 60 characters).</div>
                    </div>
                    <div class="mb-0">
                        <label for="permissionDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="permissionDescription" name="description" rows="3" maxlength="255" placeholder="What is this permission for?"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg" aria-hidden="true"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSavePermission">
                        <i class="bi bi-floppy" aria-hidden="true"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>