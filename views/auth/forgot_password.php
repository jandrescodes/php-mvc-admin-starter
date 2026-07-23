<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <h1 class="h3">Base System</h1>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>

        <form action="<?= URL ?>forgot-password" method="post" id="forgot-password-form">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="form-group">
                <label for="email" class="sr-only">Email</label>
                <div class="input-group">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block" id="btn-request">
                        <i class="fas fa-envelope mr-2" id="btn-icon"></i> Request new password
                    </button>
                </div>
            </div>
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="<?= URL ?>login">Back to Login</a>
        </p>
    </div>
</div>
