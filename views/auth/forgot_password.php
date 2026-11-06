<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>

        <form action="<?= URL ?>forgot-password" method="post" id="forgot-password-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="mb-3">
                <label for="email" class="visually-hidden">Email</label>
                <div class="input-group">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required autocomplete="email">
                    <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="btn-request">
                <i class="bi bi-envelope me-2" aria-hidden="true"></i> Request new password
            </button>
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="<?= URL ?>login">Back to Login</a>
        </p>
    </div>
</div>
