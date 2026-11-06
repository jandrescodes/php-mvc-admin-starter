<!-- Content Header -->
<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">My Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">
        <div class="row">

            <!-- Left column — summary -->
            <div class="col-md-4">
                <div class="card card-outline card-primary mb-4 sticky-top">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img id="sidebar-avatar"
                                class="rounded-circle img-thumbnail user-avatar-circle-sm"
                                src="<?= $imageSrc; ?>"
                                alt="Profile photo">
                        </div>
                        <h3 class="fs-4 text-center mt-2">
                            <?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']); ?>
                        </h3>
                        <p class="text-body-secondary text-center"><?= htmlspecialchars($user['role_name'] ?? 'No role assigned'); ?></p>

                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-envelope me-1" aria-hidden="true"></i> Email</b>
                                <span class="text-body-secondary small text-break text-end"><?= htmlspecialchars($user['email']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-telephone me-1" aria-hidden="true"></i> Phone</b>
                                <span class="text-body-secondary">
                                    <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not registered'; ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-toggle-on me-1" aria-hidden="true"></i> Status</b>
                                <?php if ($user['status'] == 1): ?>
                                    <span class="badge rounded-pill text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill text-bg-danger">Inactive</span>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Left column -->

            <!-- Right column — forms -->
            <div class="col-md-8">
                <div class="card card-outline card-outline-tabs card-primary mb-4">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="profile-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link active" id="tab-data-tab" data-bs-toggle="tab" data-bs-target="#tab-data" role="tab" aria-controls="tab-data" aria-selected="true">
                                    <i class="bi bi-person-gear me-1" aria-hidden="true"></i> My Data
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link" id="tab-password-tab" data-bs-toggle="tab" data-bs-target="#tab-password" role="tab" aria-controls="tab-password" aria-selected="false">
                                    <i class="bi bi-lock me-1" aria-hidden="true"></i> Password
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- Tab: My Data -->
                            <div class="tab-pane fade show active" id="tab-data" role="tabpanel" aria-labelledby="tab-data-tab" tabindex="0">
                                <form id="formProfile" action="<?= URL ?>profile" method="POST" enctype="multipart/form-data" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

                                    <!-- Profile photo -->
                                    <div class="card card-outline card-success mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="bi bi-camera me-2" aria-hidden="true"></i>Profile photo</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center g-3">
                                                <div class="col-md-5 text-center">
                                                    <img id="preview-image"
                                                        src="<?= $imageSrc; ?>"
                                                        class="rounded-circle img-thumbnail user-avatar-circle-md"
                                                        alt="Preview of the profile photo">
                                                </div>
                                                <div class="col-md-7">
                                                    <label for="image" class="form-label">Select a new photo</label>
                                                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                                    <div class="form-text">JPG, PNG, GIF, WEBP — max 5 MB</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact -->
                                    <div class="card card-outline card-info mb-0">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="bi bi-person-lines-fill me-2" aria-hidden="true"></i>Contact</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                                    <input type="tel" class="form-control" id="phone" name="phone"
                                                        value="<?= htmlspecialchars($user['phone'] ?? ''); ?>"
                                                        placeholder="Enter your phone number" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="mb-0">
                                                <label for="address" class="form-label">Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                                    <textarea class="form-control" id="address" name="address" rows="2"
                                                        placeholder="Enter your address"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-floppy" aria-hidden="true"></i> Save changes
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <!-- /Tab: My Data -->

                            <!-- Tab: Password -->
                            <div class="tab-pane fade" id="tab-password" role="tabpanel" aria-labelledby="tab-password-tab" tabindex="0">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Changing the password keeps this session open and signs you out on any other device.
                                </div>
                                <form id="formChangePassword" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                                            <input type="password" class="form-control" id="current_password" name="current_password"
                                                placeholder="Current password" autocomplete="current-password" required>
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#current_password" aria-pressed="false" aria-label="Show password">
                                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key" aria-hidden="true"></i></span>
                                            <input type="password" class="form-control" id="new_password" name="new_password"
                                                placeholder="Minimum 8 characters" autocomplete="new-password" required minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#new_password" aria-pressed="false" aria-label="Show password">
                                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm new password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key" aria-hidden="true"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                                placeholder="Repeat the new password" autocomplete="new-password" required minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#confirm_password" aria-pressed="false" aria-label="Show password">
                                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-danger" id="btnChangePassword">
                                        <i class="bi bi-key" aria-hidden="true"></i> Change password
                                    </button>
                                </form>
                            </div>
                            <!-- /Tab: Password -->

                        </div>
                    </div>
                </div>
            </div>
            <!-- /Right column -->

        </div>
    </div>
</section>