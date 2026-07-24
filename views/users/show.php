<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>User Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>users"><i class="fas fa-users"></i> Users</a></li>
                    <li class="breadcrumb-item active">User Detail</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Left column - Profile and actions -->
            <div class="col-md-4">
                <!-- Profile card -->
                <div class="card card-info card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <?php if (isset($user['image']) && !empty($user['image'])): ?>
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?= URL ?>uploads/users/<?= htmlspecialchars($user['image']); ?>"
                                    alt="Profile image">
                            <?php else: ?>
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?= URL ?>uploads/users/user_default.jpg"
                                    alt="Profile image">
                            <?php endif; ?>
                        </div>

                        <h3 class="profile-username text-center">
                            <?= htmlspecialchars($user['name'] . ' ' . $user['first_surname'] . ' ' . $user['second_surname']); ?>
                        </h3>

                        <p class="text-muted text-center"><?= htmlspecialchars($user['role_name'] ?? 'No role assigned'); ?></p>

                        <?php
                        $isLoginLocked = !empty($user['locked_until'])
                            && strtotime($user['locked_until']) > time();
                        $lockedUntilFmt = $isLoginLocked
                            ? date('H:i', strtotime($user['locked_until']))
                            : '';
                        ?>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-id-card mr-1"></i> <?= htmlspecialchars($user['document_type']); ?></b>
                                <a class="float-right"><?= htmlspecialchars($user['document_number']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope mr-1"></i> Email</b>
                                <a href="mailto:<?= htmlspecialchars($user['email']); ?>" class="float-right"><?= htmlspecialchars($user['email']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-phone mr-1"></i> Phone</b>
                                <?php if (!empty($user['phone'])): ?>
                                    <a href="https://wa.me/<?= htmlspecialchars($user['phone']); ?>" class="float-right" target="_blank">
                                        <?= htmlspecialchars($user['phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="float-right text-muted">Not registered</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1"></i> Status</b>
                                <span class="float-right">
                                    <?php if ((int) $user['status'] === 2): ?>
                                        <span class="badge badge-warning badge-pill p-2">Pending</span>
                                    <?php elseif ((int) $user['status'] === 1): ?>
                                        <span class="badge badge-success badge-pill p-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill p-2">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <?php if ($isLoginLocked): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-lock mr-1"></i> Login</b>
                                    <span class="float-right">
                                        <span class="badge badge-warning badge-pill p-2" title="Locked until <?= htmlspecialchars($lockedUntilFmt) ?>">
                                            <i class="fas fa-lock mr-1"></i> Locked until <?= htmlspecialchars($lockedUntilFmt) ?>
                                        </span>
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="<?= URL ?>users/<?= $user['id'] ?>/edit" class="btn btn-warning mb-3">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ((int) $user['status'] === 2): ?>
                                <button type="button" id="btn-resend-invitation"
                                    class="btn btn-secondary mb-3"
                                    data-user-id="<?= (int) $user['id'] ?>"
                                    data-name="<?= htmlspecialchars($user['name']) ?>">
                                    <i class="fas fa-paper-plane mr-1"></i> Resend Invitation
                                </button>
                            <?php endif; ?>
                            <?php if ($isLoginLocked): ?>
                                <button type="button" id="btn-unlock-login"
                                    class="btn btn-danger mb-3"
                                    data-user-id="<?= (int) $user['id'] ?>"
                                    data-url="<?= URL ?>users/<?= (int) $user['id'] ?>/unlock-login"
                                    data-csrf="<?= generateCSRFToken() ?>">
                                    <i class="fas fa-lock-open mr-1"></i> Unlock Login
                                </button>
                            <?php endif; ?>
                            <a href="<?= URL ?>users" class="btn btn-default mb-3">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Left column -->

            <!-- Right column - Detailed information -->
            <div class="col-md-8">
                <div class="card card-info card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="user-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-personal-tab" data-toggle="pill" href="#tab-personal" role="tab" aria-controls="tab-personal" aria-selected="true">
                                    <i class="fas fa-user mr-1"></i> Personal Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-address-tab" data-toggle="pill" href="#tab-address" role="tab" aria-controls="tab-address" aria-selected="false">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Address
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-permissions-tab" data-toggle="pill" href="#tab-permissions" role="tab" aria-controls="tab-permissions" aria-selected="false">
                                    <i class="fas fa-key mr-1"></i> Permissions
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="user-tab-content">
                            <!-- Personal Information Tab -->
                            <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="tab-personal-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['name']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Surname:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user-alt"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['first_surname']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Second Surname:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user-alt"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($user['second_surname']) ? htmlspecialchars($user['second_surname']) : 'Not registered'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Role:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($user['role_name']) ? htmlspecialchars($user['role_name']) : 'Not assigned'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Document Type:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['document_type']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Document Number:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['document_number']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email Address:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['email']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phone:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not registered'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Personal Information Tab -->

                            <!-- Address Tab -->
                            <div class="tab-pane fade" id="tab-address" role="tabpanel" aria-labelledby="tab-address-tab">
                                <?php if (!empty($user['address'])): ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label><i class="fas fa-map-marker-alt mr-1"></i> Full Address:</label>
                                                <p class="form-control user-address-display"><?= htmlspecialchars($user['address']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($user['address']); ?>" target="_blank" class="btn btn-info">
                                                <i class="fas fa-map-marked-alt mr-1"></i> View on Google Maps
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info"></i> No address information</h5>
                                        <p>This user has no address information registered.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- /Address Tab -->

                            <!-- Permissions Tab -->
                            <div class="tab-pane fade" id="tab-permissions" role="tabpanel" aria-labelledby="tab-permissions-tab">
                                <?php if ($isAdminUser): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="icon fas fa-check"></i> Administrator User</h5>
                                        <p>This user has an Administrator role and therefore has access to all system permissions.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <?php if (count($userPermissions) > 0): ?>
                                        <?php foreach ($userPermissions as $perm): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="info-box bg-light">
                                                    <span class="info-box-icon bg-info"><i class="fas fa-check-circle"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text"><?= htmlspecialchars($perm['name']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <h5><i class="icon fas fa-exclamation-triangle"></i> No specific permissions</h5>
                                                <p>This user has no specific permissions assigned.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- /Permissions Tab -->
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /Right column -->
        </div>
    </div>
</section>