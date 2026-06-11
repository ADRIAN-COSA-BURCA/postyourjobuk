<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="card" style="width: 100%; max-width: 440px; padding: 2.25rem;">
        
        <h1 style="text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem;">🔐 Recruiter Login</h1>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login/authenticate">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; cursor: pointer;">
                Sign In
            </button>
        </form>
    </div>
</div>