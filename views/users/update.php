<!-- Content Header -->
<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Edit User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>users"><i class="bi bi-people" aria-hidden="true"></i> Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Main form (8 columns) -->
            <div class="col-md-8">
                <form action="<?= URL ?>users/update" method="POST" enctype="multipart/form-data" id="formUser" novalidate>
                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-person-vcard me-2" aria-hidden="true"></i>Personal Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" value="<?= htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="first_surname" class="form-label">First Surname <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="first_surname" name="first_surname" placeholder="Enter first surname" value="<?= htmlspecialchars($user['first_surname']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="second_surname" class="form-label">Second Surname</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="second_surname" name="second_surname" placeholder="Enter second surname" value="<?= htmlspecialchars($user['second_surname'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select class="form-select tom-select" id="document_type" name="document_type" required>
                                        <option value="">Select a document type</option>
                                        <?php foreach (['DNI', 'Passport', 'ID Card', 'RUC'] as $type): ?>
                                            <option value="<?= $type ?>" <?= $user['document_type'] === $type ? 'selected' : ''; ?>><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="document_number" class="form-label">Document Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="document_number" name="document_number" placeholder="Enter document number" value="<?= htmlspecialchars($user['document_number']); ?>" maxlength="25" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-envelope me-2" aria-hidden="true"></i>Contact Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter address"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-at" aria-hidden="true"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" value="<?= htmlspecialchars($user['email']); ?>" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-warning mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-person-lock me-2" aria-hidden="true"></i>Account Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select tom-select" id="role_id" name="role_id" required>
                                        <option value="">Select a role</option>
                                        <?php foreach ($activeRoles as $role): ?>
                                            <option value="<?= $role['id'] ?>" <?= $currentRoleId === (int) $role['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($role['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-danger mb-4 collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-key me-2" aria-hidden="true"></i>Change Password (optional)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Leave these fields blank if you do not want to change the password.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current" autocomplete="new-password" minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" data-password-toggle="#password" aria-pressed="false" aria-label="Show password">
                                            <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 8 characters if changing</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" data-password-toggle="#confirm_password" aria-pressed="false" aria-label="Show password">
                                            <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-success mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-image me-2" aria-hidden="true"></i>Profile Image</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Select New Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="form-text">Allowed formats: JPG, PNG, GIF, WEBP. Max 5 MB</div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <span class="form-label d-block">Current Image:</span>
                                    <img src="<?= URL ?>uploads/users/<?= htmlspecialchars(!empty($user['image']) ? $user['image'] : 'user_default.jpg'); ?>"
                                        alt="Current profile image" class="img-thumbnail user-avatar-preview">
                                    <div id="preview-container" class="d-none mt-2">
                                        <span class="form-label d-block">New Image Preview:</span>
                                        <img id="preview-image" src="#" alt="Preview of the selected image" class="img-thumbnail user-avatar-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Permission Assignment</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="btn-group mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="select-all">
                                    <i class="bi bi-check-square me-1" aria-hidden="true"></i> Select all
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                                    <i class="bi bi-square me-1" aria-hidden="true"></i> Deselect all
                                </button>
                            </div>
                            <fieldset>
                                <legend class="form-label fs-6">Available permissions:</legend>
                                <input type="hidden" name="permissions_submitted" value="1">
                                <div class="row">
                                    <?php foreach ($allPermissions as $permission) : ?>
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input"
                                                    id="permission_<?= $permission['id'] ?>"
                                                    name="permissions[]"
                                                    value="<?= $permission['id'] ?>"
                                                    <?= in_array($permission['id'], $assignedPermissions) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="permission_<?= $permission['id'] ?>">
                                                    <?= htmlspecialchars($permission['name']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-clock-history me-2" aria-hidden="true"></i>System Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <span class="form-label d-block"><i class="bi bi-calendar-plus me-1" aria-hidden="true"></i> Created At:</span>
                                    <p class="form-control bg-body-secondary mb-0">
                                        <?= isset($user['created_at']) ? date('m/d/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <span class="form-label d-block"><i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> Last Updated:</span>
                                    <p class="form-control bg-body-secondary mb-0">
                                        <?= isset($user['updated_at']) ? date('m/d/Y H:i', strtotime($user['updated_at'])) : 'N/A'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row g-2">
                                <div class="col-12 col-sm-auto">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-floppy" aria-hidden="true"></i> Update User
                                    </button>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <a href="<?= URL ?>users" class="btn btn-secondary w-100">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /Main form -->

            <!-- Help guide (4 columns) -->
            <div class="col-md-4">
                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-question-circle me-1" aria-hidden="true"></i> Guide for updating users</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="accordionGuide">
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <i class="bi bi-person-vcard me-2" aria-hidden="true"></i> Personal Information
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionGuide">
                                    <div class="accordion-body">
                                        <div class="callout callout-info">
                                            <ul class="mb-0">
                                                <li>Fill in all fields marked with <span class="text-danger">*</span></li>
                                                <li>The <strong>second surname</strong> is optional</li>
                                                <li>Verify that the <strong>document number</strong> is correct</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="bi bi-key me-2" aria-hidden="true"></i> Password Change
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionGuide">
                                    <div class="accordion-body">
                                        <div class="callout callout-warning">
                                            <ul class="mb-0">
                                                <li>Leave both fields blank if you <strong>do not want to change</strong> the password</li>
                                                <li>If changing, the <strong>password</strong> must have at least 8 characters</li>
                                                <li>It is recommended to use letters, numbers and symbols</li>
                                                <li>Both passwords must match</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        <i class="bi bi-shield-check me-2" aria-hidden="true"></i> Permissions
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionGuide">
                                    <div class="accordion-body">
                                        <div class="callout callout-info">
                                            <ul class="mb-0">
                                                <li>Assign <strong>permissions</strong> according to the user's functions</li>
                                                <li>Users with the <strong>Administrator</strong> role have full access automatically</li>
                                                <li>Use the quick selection buttons to speed up assignment</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        <i class="bi bi-image me-2" aria-hidden="true"></i> Profile Image
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionGuide">
                                    <div class="accordion-body">
                                        <div class="callout callout-success">
                                            <ul class="mb-0">
                                                <li>Select a new image <strong>only if you want to change it</strong></li>
                                                <li>Recommended formats: JPG, PNG</li>
                                                <li>Recommended size: square 500×500 pixels</li>
                                                <li>If no new image is uploaded, the current one is kept</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile preview card -->
                <div class="card card-outline card-warning mb-4 sticky-top">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-person-badge me-1" aria-hidden="true"></i> Profile preview</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-preview">
                            <img id="profile-preview-img" src="<?= URL ?>uploads/users/<?= htmlspecialchars(!empty($user['image']) ? $user['image'] : 'user_default.jpg'); ?>" alt="" class="rounded-circle img-thumbnail user-avatar-circle-lg">
                            <h5 id="profile-preview-name" class="mt-3"><?= htmlspecialchars(trim($user['name'] . ' ' . $user['first_surname'] . ' ' . ($user['second_surname'] ?? ''))); ?></h5>
                            <p id="profile-preview-role" class="text-body-secondary"><?= htmlspecialchars($user['role_name'] ?? ''); ?></p>
                            <?php if ($user['status'] == 1): ?>
                                <span class="badge rounded-pill text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge rounded-pill text-bg-danger">Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div class="alert alert-light mt-3 mb-0">
                            <small><i class="bi bi-info-circle" aria-hidden="true"></i> This is a preview of how the user profile will look.</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Help guide -->
        </div>
    </div>
</section>
