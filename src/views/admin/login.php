<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    
    <div class="card p-4 shadow-sm" style="max-width: 450px; width: 100%; border-radius: 12px; border: 1px solid #e3e6f0; background: #fff;">
        
        <div class="text-center mb-4">
            <h2 style="font-weight: 600; color: #1e293b;">🔒 Admin Login</h2>
            <p class="text-muted small">Central Platform Security Management</p>
        </div>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #f5c6cb;">
                <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="/admin/login" method="POST">
            <div class="form-group mb-3">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; color: #475569;">Admin Email Address</label>
                <input type="email" name="email" required class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; box-sizing: border-box;">
            </div>

            <div class="form-group mb-4">
                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; color: #475569;">Secure Password</label>
                <input type="password" name="password" required class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background-color: #3b82f6; color: white; padding: 12px; border: none; border-radius: 6px; font-weight: 600; width: 100%; cursor: pointer; transition: background 0.2s;">
                Sign In to Control Panel
            </button>
        </form>
        
        <div class="text-center mt-3">
            <a href="/login" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">← Go to Recruiter Portal</a>
        </div>
        
    </div> </div>