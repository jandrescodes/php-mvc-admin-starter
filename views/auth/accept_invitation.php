<div class="card card-outline card-success">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">
            <i class="fas fa-envelope-open-text mr-1"></i>
            Welcome! Set your password to activate your account.
        </p>

        <form action="<?= URL ?>accept-invitation" method="post" id="accept-invitation-form">
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
                    <button type="submit" class="btn btn-success btn-block" id="btn-accept">
                        <i class="fas fa-check mr-2" id="btn-icon"></i> Activate Account
                    </button>
                </div>
            </div>
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="<?= URL ?>login">Back to Login</a>
        </p>
    </div>
</div>
