<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Create User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>users"><i class="fas fa-users"></i> Users</a></li>
                    <li class="breadcrumb-item active">Create User</li>
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
                <form action="<?= URL ?>users" method="POST" enctype="multipart/form-data" id="formUser">
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
                                                placeholder="Enter name" required>
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
                                                placeholder="Enter first surname" required>
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
                                                placeholder="Enter second surname">
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
                                            <option value="DNI">DNI</option>
                                            <option value="Passport">Passport</option>
                                            <option value="ID Card">ID Card</option>
                                            <option value="RUC">RUC</option>
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
                                                placeholder="Enter document number" required>
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
                                                placeholder="Enter address"></textarea>
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
                                                placeholder="Enter phone number">
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
                                                placeholder="Enter email address" required>
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
                            <!-- Invitation toggle -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="invite_toggle" name="invite_toggle">
                                        <input type="hidden" id="invite" name="invite" value="0">
                                        <label class="custom-control-label" for="invite_toggle">
                                            <i class="fas fa-envelope mr-1"></i>
                                            Send invitation email — user will set their own password
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Role -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role_id">Role <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="role_id" name="role_id" required>
                                            <option value="">Select a role</option>
                                            <?php foreach ($activeRoles as $role): ?>
                                                <option value="<?= $role['id'] ?>">
                                                    <?= htmlspecialchars($role['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Status (hidden when invite active) -->
                                <div class="col-md-6" id="status-field">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control select2" id="status" name="status">
                                            <option value="1" selected>Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Password fields (hidden when invite active) -->
                            <div id="password-fields">
                                <div class="row">
                                    <!-- Password -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password <span class="text-danger password-required">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password" name="password"
                                                    placeholder="Enter password" autocomplete="off" minlength="8" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button" data-password-toggle="#password" aria-pressed="false" aria-label="Show password">
                                                        <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Minimum 8 characters</small>
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm Password <span class="text-danger password-required">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                                    placeholder="Confirm password" autocomplete="off" required>
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
                    </div>
                    <!-- /Account Information Card -->

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
                                        <label for="image">Select Image</label>
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
                                    <div id="preview-container" class="d-none">
                                        <label>Preview:</label><br>
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
                                            ?>
                                                <div class="col-md-4">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="permission_<?= $permission['id'] ?>"
                                                            name="permissions[]"
                                                            value="<?= $permission['id'] ?>">
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
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i> Save User
                                    </button>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <a href="<?= URL ?>users" class="btn btn-default btn-block">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Permissions Card -->
                </form>
            </div>
            <!-- /Main form -->

            <!-- Help guide (4 columns) -->
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle mr-1"></i> Guide for creating users</h3>
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
                                            <i class="fas fa-envelope mr-1"></i> Contact Information
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-info">
                                            <ul class="mb-0">
                                                <li>The <strong>email address</strong> must be unique in the system</li>
                                                <li>Include country code for international <strong>phone</strong> numbers</li>
                                                <li>The <strong>address</strong> can include references for easier location</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingThree">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            <i class="fas fa-lock mr-1"></i> Security
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-warning">
                                            <ul class="mb-0">
                                                <li>The <strong>password</strong> must have at least 8 characters</li>
                                                <li>It is recommended to use letters, numbers and symbols for stronger security</li>
                                                <li>The <strong>role</strong> defines the set of inherited permissions</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingFour">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            <i class="fas fa-key mr-1"></i> Permissions
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionGuide">
                                    <div class="card-body">
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
                        </div>
                    </div>
                </div>

                <!-- Profile preview card -->
                <div class="card card-outline card-primary sticky-top">
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
                            <img id="profile-preview-img" src="<?= URL ?>uploads/users/user_default.jpg" class="img-circle img-thumbnail user-avatar-circle-lg">
                            <h5 id="profile-preview-name" class="mt-3">New User</h5>
                            <p id="profile-preview-role" class="text-muted">User position</p>
                            <div id="profile-preview-badge" class="badge badge-success">Active</div>
                        </div>
                        <div class="alert alert-light mt-3">
                            <small><i class="fas fa-info-circle"></i> This is a preview of how the user profile will look.</small>
                        </div>
                    </div>
                </div>
                <!-- /Profile preview card -->
            </div>
            <!-- /Help guide -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->