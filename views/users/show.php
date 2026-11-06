<!-- Content Header -->
<section class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">User Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL ?>users"><i class="bi bi-people" aria-hidden="true"></i> Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Detail</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Left column - Profile and actions -->
            <div class="col-md-4">
                <div class="card card-info card-outline mb-4">
                    <div class="card-body box-profile">
                        <?php
                        $isLoginLocked = !empty($user['locked_until'])
                            && strtotime($user['locked_until']) > time();
                        $lockedUntilFmt = $isLoginLocked
                            ? date('H:i', strtotime($user['locked_until']))
                            : '';
                        $statusCode = (int) $user['status'];
                        ?>
                        <div class="text-center">
                            <button type="button" class="btn p-0 border-0 rounded-circle" id="btn-profile-image" aria-label="Enlarge profile image"
                                data-src="<?= URL ?>uploads/users/<?= htmlspecialchars(!empty($user['image']) ? $user['image'] : 'user_default.jpg'); ?>">
                                <img class="rounded-circle img-thumbnail user-avatar-circle-md"
                                    src="<?= URL ?>uploads/users/<?= htmlspecialchars(!empty($user['image']) ? $user['image'] : 'user_default.jpg'); ?>"
                                    alt="Profile image">
                            </button>
                        </div>

                        <h3 class="fs-4 text-center mt-3">
                            <?= htmlspecialchars(trim($user['name'] . ' ' . $user['first_surname'] . ' ' . ($user['second_surname'] ?? ''))); ?>
                        </h3>

                        <p class="text-body-secondary text-center"><?= htmlspecialchars($user['role_name'] ?? 'No role assigned'); ?></p>

                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-person-vcard me-1" aria-hidden="true"></i> <?= htmlspecialchars($user['document_type']); ?></b>
                                <span><?= htmlspecialchars($user['document_number']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-envelope me-1" aria-hidden="true"></i> Email</b>
                                <a href="mailto:<?= htmlspecialchars($user['email']); ?>" class="text-break text-end"><?= htmlspecialchars($user['email']); ?></a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-telephone me-1" aria-hidden="true"></i> Phone</b>
                                <?php if (!empty($user['phone'])): ?>
                                    <a href="https://wa.me/<?= htmlspecialchars($user['phone']); ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($user['phone']); ?></a>
                                <?php else: ?>
                                    <span class="text-body-secondary">Not registered</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <b class="text-nowrap"><i class="bi bi-toggle-on me-1" aria-hidden="true"></i> Status</b>
                                <?php if ($statusCode === 2): ?>
                                    <span class="badge rounded-pill text-bg-warning">Pending</span>
                                <?php elseif ($statusCode === 1): ?>
                                    <span class="badge rounded-pill text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill text-bg-danger">Inactive</span>
                                <?php endif; ?>
                            </li>
                            <?php if ($isLoginLocked): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                    <b class="text-nowrap"><i class="bi bi-lock me-1" aria-hidden="true"></i> Login</b>
                                    <span class="badge rounded-pill text-bg-warning" title="Locked until <?= htmlspecialchars($lockedUntilFmt) ?>"><i class="bi bi-lock me-1" aria-hidden="true"></i> Locked until <?= htmlspecialchars($lockedUntilFmt) ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <?php if ($canManage): ?>
                                <a href="<?= URL ?>users/<?= $user['id'] ?>/edit" class="btn btn-warning">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
                                </a>
                                <?php if ($statusCode === 2): ?>
                                    <button type="button" id="btn-resend-invitation" class="btn btn-secondary"
                                        data-user-id="<?= (int) $user['id'] ?>"
                                        data-name="<?= htmlspecialchars($user['name']) ?>">
                                        <i class="bi bi-send me-1" aria-hidden="true"></i> Resend Invitation
                                    </button>
                                <?php endif; ?>
                                <?php if ($isLoginLocked): ?>
                                    <button type="button" id="btn-unlock-login" class="btn btn-danger"
                                        data-user-id="<?= (int) $user['id'] ?>">
                                        <i class="bi bi-unlock me-1" aria-hidden="true"></i> Unlock Login
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="<?= URL ?>users" class="btn btn-secondary">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Left column -->

            <!-- Right column - Detailed information -->
            <div class="col-md-8">
                <div class="card card-info card-outline card-outline-tabs mb-4">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="user-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link active" id="tab-personal-tab" data-bs-toggle="tab" data-bs-target="#tab-personal" role="tab" aria-controls="tab-personal" aria-selected="true">
                                    <i class="bi bi-person me-1" aria-hidden="true"></i> Personal Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link" id="tab-address-tab" data-bs-toggle="tab" data-bs-target="#tab-address" role="tab" aria-controls="tab-address" aria-selected="false">
                                    <i class="bi bi-geo-alt me-1" aria-hidden="true"></i> Address
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link" id="tab-permissions-tab" data-bs-toggle="tab" data-bs-target="#tab-permissions" role="tab" aria-controls="tab-permissions" aria-selected="false">
                                    <i class="bi bi-key me-1" aria-hidden="true"></i> Permissions
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="user-tab-content">
                            <!-- Personal Information Tab -->
                            <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="tab-personal-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Name:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= htmlspecialchars($user['name']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">First Surname:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= htmlspecialchars($user['first_surname']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Second Surname:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= !empty($user['second_surname']) ? htmlspecialchars($user['second_surname']) : 'Not registered'; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Role:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= !empty($user['role_name']) ? htmlspecialchars($user['role_name']) : 'Not assigned'; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Document Type:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= htmlspecialchars($user['document_type']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Document Number:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= htmlspecialchars($user['document_number']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Email Address:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label d-block">Phone:</span>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                            <div class="form-control bg-body-secondary"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not registered'; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Personal Information Tab -->

                            <!-- Address Tab -->
                            <div class="tab-pane fade" id="tab-address" role="tabpanel" aria-labelledby="tab-address-tab" tabindex="0">
                                <?php if (!empty($user['address'])): ?>
                                    <span class="form-label d-block"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i> Full Address:</span>
                                    <div class="form-control bg-body-secondary user-address-display mb-3"><?= htmlspecialchars($user['address']); ?></div>
                                    <div class="text-center">
                                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($user['address']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info">
                                            <i class="bi bi-map me-1" aria-hidden="true"></i> View on Google Maps
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">
                                        <h5><i class="bi bi-info-circle me-1" aria-hidden="true"></i> No address information</h5>
                                        <p class="mb-0">This user has no address information registered.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- /Address Tab -->

                            <!-- Permissions Tab -->
                            <div class="tab-pane fade" id="tab-permissions" role="tabpanel" aria-labelledby="tab-permissions-tab" tabindex="0">
                                <?php if ($isAdminUser): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="bi bi-check-circle me-1" aria-hidden="true"></i> Administrator User</h5>
                                        <p class="mb-0">This user has an Administrator role and therefore has access to all system permissions.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <?php if (count($userPermissions) > 0): ?>
                                        <?php foreach ($userPermissions as $perm): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="info-box">
                                                    <span class="info-box-icon text-bg-info"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text"><?= htmlspecialchars($perm['name']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif (!$isAdminUser): ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning mb-0">
                                                <h5><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i> No specific permissions</h5>
                                                <p class="mb-0">This user has no specific permissions assigned.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- /Permissions Tab -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Right column -->
        </div>
    </div>
</section>