<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">Enter your credentials to sign in</p>

        <form action="<?= URL ?>login" method="post" id="login-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="mb-3">
                <label for="identifier" class="visually-hidden">Email or document number</label>
                <div class="input-group">
                    <input type="text" name="identifier" id="identifier" class="form-control"
                        placeholder="Email or document number" autocomplete="username" required minlength="3">
                    <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                </div>
            </div>

            <div class="mb-3">
                <label for="password-field" class="visually-hidden">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password-field" class="form-control"
                        placeholder="Password" autocomplete="current-password" required minlength="6">
                    <button class="btn btn-outline-secondary" type="button" data-password-toggle="#password-field"
                        aria-pressed="false" aria-label="Show password" aria-controls="password-field">
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="btn-login">
                <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i> Sign In
            </button>
        </form>

        <p class="mb-1 mt-3 text-center">
            <a href="<?= URL ?>forgot-password">I forgot my password</a>
        </p>
    </div>
</div>

<div class="login-footer text-center mt-3">
    <p class="text-body-secondary">&copy; <?= date('Y') ?> Base System. All rights reserved.</p>
</div>
