<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="card" style="width: 100%; max-width: 440px; padding: 2.25rem;">
        
        <div class="text-center" style="margin-bottom: 1.5rem;">
            <h2 style="font-weight: 600; margin-bottom: 0.5rem;">🔒 Admin Login</h2>
            <p class="text-muted" style="font-size: 0.9rem;">Central Platform Security Management</p>
        </div>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; color: #dc2626; border-radius: 8px;">
                <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="/admin/login" method="POST">
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Admin Email Address</label>
                <input type="email" name="email" required class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Secure Password</label>
                <input type="password" name="password" required class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; cursor: pointer;">
                Sign In to Control Panel
            </button>
        </form>
        
        <div class="text-center" style="margin-top: 1.5rem;">
            <a href="/login" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">← Go to Recruiter Portal</a>
        </div>
    </div>
</div>