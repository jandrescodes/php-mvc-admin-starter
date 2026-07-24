<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>My Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item active">My Profile</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- Left column — summary -->
            <div class="col-md-4">
                <div class="card card-outline card-primary sticky-top">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img id="sidebar-avatar"
                                class="profile-user-img img-fluid img-circle user-avatar-circle-sm"
                                src="<?= $imageSrc; ?>"
                                alt="Profile photo">
                        </div>
                        <h3 class="profile-username text-center mt-2">
                            <?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']); ?>
                        </h3>
                        <p class="text-muted text-center"><?= htmlspecialchars($user['role_name'] ?? 'No role assigned'); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope mr-1"></i> Email</b>
                                <span class="float-right text-muted small"><?= htmlspecialchars($user['email']); ?></span>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-phone mr-1"></i> Phone</b>
                                <span class="float-right text-muted">
                                    <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not registered'; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1"></i> Status</b>
                                <span class="float-right">
                                    <?php if ($user['status'] == 1): ?>
                                        <span class="badge badge-success badge-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Left column -->

            <!-- Right column — forms -->
            <div class="col-md-8">
                <div class="card card-outline card-outline-tabs card-primary">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="profile-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#tab-data" data-toggle="tab" role="tab">
                                    <i class="fas fa-user-edit mr-1"></i> My Data
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#tab-password" data-toggle="tab" role="tab">
                                    <i class="fas fa-lock mr-1"></i> Password
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- Tab: My Data -->
                            <div class="tab-pane fade show active" id="tab-data" role="tabpanel">
                                <form action="<?= URL ?>profile" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

                                    <!-- Profile photo -->
                                    <div class="card card-outline card-success mb-3">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="fas fa-camera mr-2"></i>Profile photo</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-5 text-center">
                                                    <img id="preview-image"
                                                        src="<?= $imageSrc; ?>"
                                                        class="img-circle img-thumbnail user-avatar-circle-md"
                                                        alt="Preview">
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                                        <label class="custom-file-label" for="image">Choose file</label>
                                                    </div>
                                                    <small class="form-text text-muted">JPG, PNG, WEBP — max 5 MB</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact -->
                                    <div class="card card-outline card-info mb-3">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="fas fa-address-book mr-2"></i>Contact</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    </div>
                                                    <input type="tel" class="form-control" id="phone" name="phone"
                                                        value="<?= htmlspecialchars($user['phone'] ?? ''); ?>"
                                                        placeholder="Enter your phone number" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label for="address">Address</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                    </div>
                                                    <textarea class="form-control" id="address" name="address" rows="2"
                                                        placeholder="Enter your address"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save mr-1"></i> Save changes
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <!-- /Tab: My Data -->

                            <!-- Tab: Password -->
                            <div class="tab-pane fade" id="tab-password" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i> Changing the password will end the current session.
                                </div>
                                <form id="formChangePassword" action="javascript:void(0)">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <div class="form-group">
                                        <label for="current_password">Current password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            </div>
                                            <input type="password" class="form-control" id="current_password" name="current_password"
                                                placeholder="Current password" autocomplete="off" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-default" type="button" data-password-toggle="#current_password" aria-pressed="false" aria-label="Show password">
                                                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password">New password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="password" class="form-control" id="new_password" name="new_password"
                                                placeholder="Minimum 6 characters" autocomplete="off" required minlength="6">
                                            <div class="input-group-append">
                                                <button class="btn btn-default" type="button" data-password-toggle="#new_password" aria-pressed="false" aria-label="Show password">
                                                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm new password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                                placeholder="Repeat the new password" autocomplete="off" required minlength="6">
                                            <div class="input-group-append">
                                                <button class="btn btn-default" type="button" data-password-toggle="#confirm_password" aria-pressed="false" aria-label="Show password">
                                                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-danger" id="btnChangePassword">
                                        <i class="fas fa-key mr-1"></i> Change password
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