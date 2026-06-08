<?php
// src/views/auth/login.php
$pageTitle = 'Secure Login - PostYourJobHere.com';
include __DIR__ . '/../layouts/header.php'; // Ensure you have a header layout
?>

<div class="card" style="max-width: 460px; margin: 0 auto; padding: 2rem;">
    <h1 style="text-align: center;">🔐 Recruiter Login</h1>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error" style="color: red; margin-bottom: 1rem;">
            ⚠️ <?php echo e($errorMessage); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/login/authenticate">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div class="form-group" style="margin-bottom: 1rem;">
            <label>Email Address</label>
            <input type="email" name="email" class="form-input" required autofocus style="width: 100%;">
        </div>
        
        <div class="form-group" style="margin-bottom: 1rem;">
            <label>Password</label>
            <input type="password" name="password" class="form-input" required style="width: 100%;">
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>