<!-- Role Modal (shared between create and edit) -->
<div class="modal fade" id="modalRole" tabindex="-1" role="dialog" aria-labelledby="modalRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary" id="modalRoleHeader">
                <h5 class="modal-title" id="modalRoleLabel">Create New Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formRole" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                    <input type="hidden" id="roleAction" name="action" value="create">
                    <input type="hidden" id="roleId" name="id" value="">

                    <div class="form-group">
                        <label for="roleName">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" maxlength="60" autocomplete="off">
                        <small class="form-text text-muted">Unique name for the role (max 60 characters).</small>
                    </div>
                    <div class="form-group">
                        <label for="roleDescription">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3" maxlength="255" placeholder="What is this role for?"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times" aria-hidden="true"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSaveRole">
                        <i class="fas fa-save" aria-hidden="true"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>