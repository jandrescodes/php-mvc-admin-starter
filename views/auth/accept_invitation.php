<div class="card card-outline card-success">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">
            <i class="bi bi-envelope-open me-1" aria-hidden="true"></i>
            Welcome! Set your password to activate your account.
        </p>

        <form action="<?= URL ?>accept-invitation" method="post" id="accept-invitation-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="mb-3">
                <label for="password" class="visually-hidden">New Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="New Password" required minlength="8" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" data-password-toggle="#password" tabindex="-1"
                        aria-pressed="false" aria-label="Show password" aria-controls="password">
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="visually-hidden">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                        placeholder="Confirm Password" required minlength="8" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" data-password-toggle="#confirm_password" tabindex="-1"
                        aria-pressed="false" aria-label="Show password" aria-controls="confirm_password">
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100" id="btn-accept">
                <i class="bi bi-check-lg me-2" aria-hidden="true"></i> Activate Account
            </button>
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="<?= URL ?>login">Back to Login</a>
        </p>
    </div>
</div>
