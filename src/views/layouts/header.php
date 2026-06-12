<header class="header">
    <div class="container flex-between" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <a href="<?= (isset($_SESSION['tenant_id']) ? '/dashboard' : '/') ?>" 
               class="logo" style="text-decoration: none; font-size: 1.25rem; font-weight: bold; color: var(--text);">
                📋 PostYourJobHere.com
            </a>
            
            <nav class="public-nav" style="display: flex; gap: 1.25rem; margin-left: 1rem;">
                <a href="/" style="text-decoration: none; color: var(--text); font-size: 0.95rem; font-weight: 500;">Explore Jobs</a>
                <a href="/about" style="text-decoration: none; color: var(--text-muted); font-size: 0.95rem; font-weight: 500;">About Us</a>
                <a href="/contact" style="text-decoration: none; color: var(--text-muted); font-size: 0.95rem; font-weight: 500;">Contact</a>
            </nav>
        </div>

        <nav class="nav" style="display: flex; gap: 12px; align-items: center;">
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="/admin" class="btn btn-secondary">Admin Dashboard</a>
                <a href="/admin/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
                
            <?php elseif (isset($_SESSION['tenant_id'])): ?>
                <a href="/dashboard" class="btn btn-secondary">Dashboard</a>
                <a href="/jobs/create" class="btn btn-primary">Post a Job</a>
                <a href="/admin/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
                
            <?php else: ?>
                <a href="/login" class="btn btn-ghost btn-sm" style="color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
                    🔒 Recruiter Portal
                </a>
                
                <a href="/admin/login" class="btn btn-primary btn-sm" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    ⚡ Admin Entry
                </a>
                
            <?php endif; ?>
        </nav>
        
    </div>
</header>