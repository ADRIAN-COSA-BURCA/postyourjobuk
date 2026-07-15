<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h3>Reset Password</h3>
            
            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>/reset-password/submit" method="POST">
                <!-- Added CSRF Token to prevent cross-site request forgery -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <div class="form-group mb-3">
                    <label>Email Address</label>
                    <input type="email" name="email" required class="form-control">
                </div>
                
                <div class="form-group mb-3">
                    <label>Recovery Code</label>
                    <input type="text" name="recovery_code" required class="form-control" placeholder="PJH-XXXXXX">
                </div>
                
                <div class="form-group mb-3">
                    <label>New Password</label>
                    <input type="password" name="new_password" required class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        </div>
    </div>
</div>