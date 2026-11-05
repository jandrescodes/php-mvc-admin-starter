<!-- Modal de rol (compartido entre crear y editar) -->
<div class="modal fade" id="modalRole" tabindex="-1" aria-labelledby="modalRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-bg-primary" id="modalRoleHeader">
                <h5 class="modal-title" id="modalRoleLabel">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRole" method="post" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                    <input type="hidden" id="roleAction" name="action" value="create">
                    <input type="hidden" id="roleId" name="id" value="">

                    <div class="mb-3">
                        <label for="roleName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" maxlength="60" required autocomplete="off">
                        <div class="form-text">Unique name for the role (max 60 characters).</div>
                    </div>
                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3" maxlength="255" placeholder="What is this role for?"></textarea>
                    </div>
                    <div class="mb-0">
                        <label for="rolePermissions" class="form-label">Permissions</label>
                        <select class="tom-select" id="rolePermissions" multiple placeholder="Select permissions…">
                            <?php foreach ($permissions as $perm): ?>
                                <option value="<?= (int) $perm['id']; ?>"><?= htmlspecialchars($perm['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="rolePermissionsHint">You can only grant permissions you hold yourself.</div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg" aria-hidden="true"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSaveRole">
                        <i class="bi bi-floppy" aria-hidden="true"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>