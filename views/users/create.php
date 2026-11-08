<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Create User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>users"><i class="bi bi-people" aria-hidden="true"></i> Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create User</li>
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
                <form action="<?= URL ?>users" method="POST" enctype="multipart/form-data" id="formUser" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-person-vcard me-2" aria-hidden="true"></i>Personal Information</h2>
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
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="first_surname" class="form-label">First Surname <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="first_surname" name="first_surname" placeholder="Enter first surname" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="second_surname" class="form-label">Second Surname</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="second_surname" name="second_surname" placeholder="Enter second surname">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select class="form-select tom-select" id="document_type" name="document_type" required>
                                        <option value="">Select a document type</option>
                                        <option value="DNI">DNI</option>
                                        <option value="Passport">Passport</option>
                                        <option value="ID Card">ID Card</option>
                                        <option value="RUC">RUC</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="document_number" class="form-label">Document Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                                        <input type="text" class="form-control" id="document_number" name="document_number" placeholder="Enter document number" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info mb-4">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-envelope me-2" aria-hidden="true"></i>Contact Information</h2>
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
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter address"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-at" aria-hidden="true"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-warning mb-4">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-person-lock me-2" aria-hidden="true"></i>Account Information</h2>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input type="checkbox" class="form-check-input" role="switch" id="invite_toggle" name="invite_toggle">
                                <input type="hidden" id="invite" name="invite" value="0">
                                <label class="form-check-label" for="invite_toggle">
                                    <i class="bi bi-envelope me-1" aria-hidden="true"></i>
                                    Send invitation email — user will set their own password
                                </label>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select tom-select" id="role_id" name="role_id" required>
                                        <option value="">Select a role</option>
                                        <?php foreach ($activeRoles as $role): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6" id="status-field">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select tom-select" id="status" name="status">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div id="password-fields" class="mt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password <span class="text-danger password-required">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" autocomplete="off" minlength="8" required>
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#password" aria-pressed="false" aria-label="Show password">
                                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Minimum 8 characters</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger password-required">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" autocomplete="off" required>
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#confirm_password" aria-pressed="false" aria-label="Show password">
                                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-success mb-4">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-image me-2" aria-hidden="true"></i>Profile Image</h2>
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
                                    <label for="image" class="form-label">Select Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="form-text">Allowed formats: JPG, PNG, GIF, WEBP. Max 5 MB</div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <div id="preview-container" class="d-none">
                                        <span class="form-label d-block">Preview:</span>
                                        <img id="preview-image" src="#" alt="Preview of the selected image" class="img-thumbnail user-avatar-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary mb-4">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-key me-2" aria-hidden="true"></i>Permission Assignment</h2>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                    <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="btn-group mb-4">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="select-all">
                                    <i class="bi bi-check-square me-1" aria-hidden="true"></i> Select all
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                                    <i class="bi bi-square me-1" aria-hidden="true"></i> Deselect all
                                </button>
                            </div>
                            <fieldset>
                                <legend class="form-label fs-6">Available permissions:</legend>
                                <div class="row">
                                    <?php foreach ($allPermissions as $permission) : ?>
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input"
                                                    id="permission_<?= $permission['id'] ?>"
                                                    name="permissions[]"
                                                    value="<?= $permission['id'] ?>">
                                                <label class="form-check-label" for="permission_<?= $permission['id'] ?>">
                                                    <?= htmlspecialchars($permission['name']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </div>
                        <div class="card-footer">
                            <div class="row g-2">
                                <div class="col-12 col-sm-auto">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-floppy" aria-hidden="true"></i> Save User
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
                        <h2 class="card-title"><i class="bi bi-question-circle me-1" aria-hidden="true"></i> Guide for creating users</h2>
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
                                <h3 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <i class="bi bi-person-vcard me-2" aria-hidden="true"></i> Personal Information
                                    </button>
                                </h3>
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
                                <h3 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="bi bi-envelope me-2" aria-hidden="true"></i> Contact Information
                                    </button>
                                </h3>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionGuide">
                                    <div class="accordion-body">
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
                            <div class="accordion-item border-0">
                                <h3 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        <i class="bi bi-lock me-2" aria-hidden="true"></i> Security
                                    </button>
                                </h3>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionGuide">
                                    <div class="accordion-body">
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
                            <div class="accordion-item border-0">
                                <h3 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        <i class="bi bi-key me-2" aria-hidden="true"></i> Permissions
                                    </button>
                                </h3>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionGuide">
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
                        </div>
                    </div>
                </div>

                <!-- Profile preview card -->
                <div class="card card-outline card-primary mb-4 sticky-top">
                    <div class="card-header">
                        <h2 class="card-title"><i class="bi bi-person-badge me-1" aria-hidden="true"></i> Profile preview</h2>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card">
                                <i data-lte-icon="collapse" class="bi bi-dash-lg" aria-hidden="true"></i>
                                <i data-lte-icon="expand" class="bi bi-plus-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-preview">
                            <img id="profile-preview-img" src="<?= URL ?>uploads/users/user_default.jpg" alt="" class="rounded-circle img-thumbnail user-avatar-circle-lg">
                            <h3 id="profile-preview-name" class="h5 mt-3">New User</h3>
                            <span id="profile-preview-badge" class="badge rounded-pill text-bg-success">Active</span>
                        </div>
                        <div class="alert alert-light mt-3 mb-0">
                            <small><i class="bi bi-info-circle" aria-hidden="true"></i> This is a preview of how the user profile will look.</small>
                        </div>
                    </div>
                </div>
                <!-- /Profile preview card -->
            </div>
            <!-- /Help guide -->
        </div>
    </div>
</section>