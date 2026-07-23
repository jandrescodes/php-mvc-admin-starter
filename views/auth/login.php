<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">Enter your credentials to sign in</p>

        <form action="<?= URL ?>login" method="post" id="login-form">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="form-group">
                <label for="identifier" class="sr-only">Email or document number</label>
                <div class="input-group">
                    <input type="text" name="identifier" id="identifier" class="form-control"
                        placeholder="Email or document number" autocomplete="username">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password-field" class="sr-only">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password-field" class="form-control"
                        placeholder="Password" autocomplete="current-password">
                    <div class="input-group-append">
                        <button class="btn btn-default" type="button" data-password-toggle="#password-field"
                            aria-pressed="false" aria-label="Show password" aria-controls="password-field">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember" value="1">
                        <label for="remember">Remember me</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block" id="btn-login">
                        <i class="fas fa-sign-in-alt mr-2" id="btn-icon"></i> Sign In
                    </button>
                </div>
            </div>
        </form>

        <p class="mb-1 mt-3 text-center">
            <a href="<?= URL ?>forgot-password">I forgot my password</a>
        </p>
    </div>
</div>

<div class="login-footer text-center mt-3">
    <p class="text-muted">&copy; <?= date('Y') ?> Base System. All rights reserved.</p>
</div>
