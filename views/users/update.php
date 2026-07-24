<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Edit User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>users"><i class="fas fa-users"></i> Users</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Main form (8 columns) -->
            <div class="col-md-8">
                <form action="<?= URL ?>users/update" method="POST" enctype="multipart/form-data" id="formUser">
                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

                    <!-- Personal Information Card -->
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-address-card mr-2"></i>Personal Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter name" value="<?= htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- First Surname -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="first_surname">First Surname <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user-alt"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="first_surname" name="first_surname"
                                                placeholder="Enter first surname" value="<?= htmlspecialchars($user['first_surname']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Second Surname -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="second_surname">Second Surname</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user-alt"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="second_surname" name="second_surname"
                                                placeholder="Enter second surname" value="<?= htmlspecialchars($user['second_surname'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Document Type -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="document_type">Document Type <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="document_type" name="document_type" required>
                                            <option value="">Select a document type</option>
                                            <option value="DNI" <?= $user['document_type'] == 'DNI' ? 'selected' : ''; ?>>DNI</option>
                                            <option value="Passport" <?= $user['document_type'] == 'Passport' ? 'selected' : ''; ?>>Passport</option>
                                            <option value="ID Card" <?= $user['document_type'] == 'ID Card' ? 'selected' : ''; ?>>ID Card</option>
                                            <option value="RUC" <?= $user['document_type'] == 'RUC' ? 'selected' : ''; ?>>RUC</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Document Number -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="document_number">Document Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="document_number" name="document_number"
                                                placeholder="Enter document number" value="<?= htmlspecialchars($user['document_number']); ?>"
                                                maxlength="25" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Personal Information Card -->

                    <!-- Contact Information Card -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Contact Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Address -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            </div>
                                            <textarea class="form-control" id="address" name="address" rows="2"
                                                placeholder="Enter address"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Phone -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            </div>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                placeholder="Enter phone number" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>"
                                                maxlength="20">
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            </div>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Enter email address" autocomplete="off" value="<?= htmlspecialchars($user['email']); ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Contact Information Card -->

                    <!-- Account Information Card -->
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-lock mr-2"></i>Account Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Role -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role_id">Role <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="role_id" name="role_id" required>
                                            <option value="">Select a role</option>
                                            <?php foreach ($activeRoles as $role): ?>
                                                <option value="<?= $role['id'] ?>"
                                                    <?= $currentRoleId === (int) $role['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($role['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Account Information Card -->

                    <!-- Change Password Card (optional) -->
                    <div class="card card-outline card-danger collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-key mr-2"></i>Change Password (optional)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> Leave these fields blank if you do not want to change the password.
                            </div>
                            <div class="row">
                                <!-- New Password -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Leave blank to keep current" autocomplete="new-password" minlength="8">
                                            <div class="input-group-append">
                                                <button class="btn btn-default" type="button" data-password-toggle="#password" aria-pressed="false" aria-label="Show password">
                                                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Minimum 8 characters if changing</small>
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                                placeholder="Confirm new password" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <button class="btn btn-default" type="button" data-password-toggle="#confirm_password" aria-pressed="false" aria-label="Show password">
                                                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Change Password Card -->

                    <!-- Profile Image Card -->
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-image mr-2"></i>Profile Image</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image">Select New Image</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="image" name="image"
                                                    accept="image/*">
                                                <label class="custom-file-label" for="image">Choose file</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Allowed formats: JPG, PNG, WEBP. Max 5 MB</small>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <label>Current Image:</label><br>
                                    <?php if (isset($user['image']) && !empty($user['image'])): ?>
                                        <img src="<?= URL ?>uploads/users/<?= htmlspecialchars($user['image']); ?>"
                                            alt="Current image" class="img-thumbnail user-avatar-preview">
                                    <?php else: ?>
                                        <img src="<?= URL ?>uploads/users/user_default.jpg"
                                            alt="Default image" class="img-thumbnail user-avatar-preview">
                                    <?php endif; ?>

                                    <div id="preview-container" class="d-none mt-2">
                                        <label>New Image Preview:</label><br>
                                        <img id="preview-image" src="#" alt="Preview" class="img-thumbnail user-avatar-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Profile Image Card -->

                    <!-- Permissions Card -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-key mr-2"></i>Permission Assignment</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="select-all">
                                            <i class="fas fa-check-square mr-1"></i> Select all
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                                            <i class="fas fa-square mr-1"></i> Deselect all
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Available permissions:</label>
                                        <div class="row">
                                            <?php
                                            foreach ($allPermissions as $permission) :
                                                $checked = in_array($permission['id'], $assignedPermissions) ? 'checked' : '';
                                            ?>
                                                <div class="col-md-4">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="permission_<?= $permission['id'] ?>"
                                                            name="permissions[]"
                                                            value="<?= $permission['id'] ?>"
                                                            <?= $checked ?>>
                                                        <label class="custom-control-label" for="permission_<?= $permission['id'] ?>">
                                                            <?= htmlspecialchars($permission['name']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Permissions Card -->

                    <!-- System Info Card -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history mr-2"></i>System Information</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="far fa-calendar-plus mr-1"></i> Created At:</label>
                                        <p class="form-control bg-light">
                                            <?= isset($user['created_at']) ? date('m/d/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-sync-alt mr-1"></i> Last Updated:</label>
                                        <p class="form-control bg-light">
                                            <?= isset($user['updated_at']) ? date('m/d/Y H:i', strtotime($user['updated_at'])) : 'N/A'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12 col-sm-auto mb-2 mb-sm-0 mr-sm-2">
                                    <button type="submit" class="btn btn-warning btn-block">
                                        <i class="fas fa-save mr-1"></i> Update User
                                    </button>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <a href="<?= URL ?>users" class="btn btn-default btn-block">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /System Info Card -->
                </form>
            </div>
            <!-- /Main form -->

            <!-- Help guide (4 columns) -->
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle mr-1"></i> Guide for updating users</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion" id="accordionGuide">
                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            <i class="fas fa-address-card mr-1"></i> Personal Information
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionGuide">
                                    <div class="card-body">
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

                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingTwo">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            <i class="fas fa-key mr-1"></i> Password Change
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-warning">
                                            <ul class="mb-0">
                                                <li>Leave both fields blank if you <strong>do not want to change</strong> the password</li>
                                                <li>If changing, the <strong>password</strong> must have at least 6 characters</li>
                                                <li>It is recommended to use letters, numbers and symbols</li>
                                                <li>Both passwords must match</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingThree">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            <i class="fas fa-shield-alt mr-1"></i> Permissions
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-info">
                                            <ul class="mb-0">
                                                <li>Assign <strong>permissions</strong> according to the user's functions</li>
                                                <li>Users with position <strong>Administrator</strong> can have all permissions</li>
                                                <li>Use the quick selection buttons to speed up assignment</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingFour">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            <i class="fas fa-image mr-1"></i> Profile Image
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionGuide">
                                    <div class="card-body">
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
                <div class="card card-outline card-warning sticky-top">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Profile preview</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-preview">
                            <?php if (isset($user['image']) && !empty($user['image'])): ?>
                                <img id="profile-preview-img" src="<?= URL ?>uploads/users/<?= htmlspecialchars($user['image']); ?>" class="img-circle img-thumbnail user-avatar-circle-lg">
                            <?php else: ?>
                                <img id="profile-preview-img" src="<?= URL ?>uploads/users/user_default.jpg" class="img-circle img-thumbnail user-avatar-circle-lg">
                            <?php endif; ?>
                            <h5 id="profile-preview-name" class="mt-3"><?= htmlspecialchars($user['name'] . ' ' . $user['first_surname'] . ' ' . $user['second_surname']); ?></h5>
                            <p id="profile-preview-role" class="text-muted"><?= htmlspecialchars($user['role_name'] ?? ''); ?></p>
                            <?php if ($user['status'] == 1): ?>
                                <div id="profile-preview-badge" class="badge badge-success">Active</div>
                            <?php else: ?>
                                <div id="profile-preview-badge" class="badge badge-danger">Inactive</div>
                            <?php endif; ?>
                        </div>
                        <div class="alert alert-light mt-3">
                            <small><i class="fas fa-info-circle"></i> This is a preview of how the user profile will look.</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Help guide -->

        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->