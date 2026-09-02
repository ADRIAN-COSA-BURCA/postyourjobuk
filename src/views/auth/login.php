<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="card" style="width: 100%; max-width: 440px; padding: 2.25rem;">
        
        <h1 style="text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem;">🔐 Recruiter Login</h1>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success" style="background: #dcfce3; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                ✅ <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login/authenticate">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus style="width: 100%; padding: 0.75rem; border: 1px solid var(--border, #d1d5db); border-radius: 6px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border, #d1d5db); border-radius: 6px;">
            </div>
            <div class="form-group mt-2">
                <a href="/reset-password" class="text-muted" style="font-size: 0.9em; text-decoration: none; color: #6b7280;">Forgot your password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; cursor: pointer; margin-top: 1rem;">
                Sign In
            </button>
        </form>

        <div style="display: flex; align-items: center; margin: 1.5rem 0;">
            <div style="flex-grow: 1; height: 1px; background: var(--border, #e5e7eb);"></div>
            <span style="padding: 0 1rem; color: #6b7280; font-size: 0.85rem;">OR</span>
            <div style="flex-grow: 1; height: 1px; background: var(--border, #e5e7eb);"></div>
        </div>

        <a href="/auth/google" style="background-color: #ffffff; color: #374151; display: flex; justify-content: center; align-items: center; gap: 0.75rem; text-decoration: none; border: 1px solid #d1d5db; border-radius: 6px; padding: 0.75rem; width: 100%; font-weight: 500; transition: background-color 0.2s; box-sizing: border-box;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='#ffffff'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px">
                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
            </svg>
            Continue with Google
        </a>
    </div>
</div>