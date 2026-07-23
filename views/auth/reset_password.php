<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">You are only one step away from your new password, recover your password now.</p>

        <form action="<?= URL ?>reset-password" method="post" id="reset-password-form">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <label for="password" class="sr-only">New Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="New Password" required minlength="8" autocomplete="new-password">
                    <div class="input-group-append">
                        <button class="btn btn-default" type="button" data-password-toggle="#password" tabindex="-1"
                            aria-pressed="false" aria-label="Show password" aria-controls="password">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="sr-only">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                        placeholder="Confirm Password" required minlength="8" autocomplete="new-password">
                    <div class="input-group-append">
                        <button class="btn btn-default" type="button" data-password-toggle="#confirm_password" tabindex="-1"
                            aria-pressed="false" aria-label="Show password" aria-controls="confirm_password">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block" id="btn-reset">
                        <i class="fas fa-lock mr-2" id="btn-icon"></i> Change password
                    </button>
                </div>
            </div>
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="<?= URL ?>login">Back to Login</a>
        </p>
    </div>
</div>
