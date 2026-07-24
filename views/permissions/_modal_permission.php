<!-- Permission Modal (partial shared between index and detail) -->
<div class="modal fade" id="modalPermission" tabindex="-1" role="dialog" aria-labelledby="modalPermissionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning" id="modalPermissionHeader">
                <h5 class="modal-title" id="modalPermissionLabel">Edit Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPermission" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                    <input type="hidden" id="permissionAction" name="action" value="edit">
                    <input type="hidden" id="permissionId" name="id" value="">

                    <div class="form-group">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <small class="form-text text-muted">Unique name for the permission.</small>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="What is this permission for?"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times" aria-hidden="true"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnSavePermission">
                        <i class="fas fa-save" aria-hidden="true"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>