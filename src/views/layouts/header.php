<header class="header">
    <div class="container flex-between">
        
        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
            <!-- 1. Smart Logo: Takes Admins to /admin, Recruiters to /dashboard, Guests to / -->
            <a href="<?= (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ? '/admin' : (isset($_SESSION['tenant_id']) ? '/dashboard' : '/') ?>" class="logo" style="text-decoration: none; font-size: 1.25rem; font-weight: bold; color: var(--text);">
                📋 PostYourJobUK
            </a>

            <!-- 2. Left Text Links -->
            <nav style="display: flex; gap: 1.25rem; flex-wrap: wrap;">
                <a href="/" style="text-decoration: none; color: var(--text); font-size: 0.95rem;">Explore Jobs</a>
                
                <?php 
                // Only show About/Contact to guests so the navbar isn't cluttered for logged-in users
                if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['tenant_id'])): ?>
                    <a href="/about" style="text-decoration: none; color: var(--text-muted); font-size: 0.95rem;">About Us</a>
                    <a href="/contact" style="text-decoration: none; color: var(--text-muted); font-size: 0.95rem;">Contact</a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- 3. Right Action Buttons (Session-Aware) -->
        <nav style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 0.5rem;">
            
            <?php 
            // HIGHEST PRIORITY: Check if Admin is logged in
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="/admin" class="btn btn-secondary">Admin Dashboard</a>
                <a href="/admin/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>

            <?php 
            // SECOND PRIORITY: Check if Recruiter is logged in
            elseif (isset($_SESSION['tenant_id'])): ?>
                <a href="/dashboard" class="btn btn-secondary">Dashboard</a>
                <a href="/jobs/create" class="btn btn-primary">Post a Job</a>
                <a href="/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
                        
            <?php 
            // DEFAULT: Guest Public View
            else: ?>
                <a href="/login" class="btn btn-ghost btn-sm">🔒 Recruiter Portal</a>
                <a href="/admin/login" class="btn btn-primary btn-sm">⚡ Admin Entry</a>
            <?php endif; ?>
            
        </nav>
    </div>
</header>