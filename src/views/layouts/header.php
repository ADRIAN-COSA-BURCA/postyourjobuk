<header class="header">
    <!-- Removed inline flex styles to allow main.css .flex-between mobile overrides to work -->
    <div class="container flex-between">
        
        <!-- Added flex-wrap: wrap to allow the logo and text links to stack if needed -->
        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
            <!-- KEEP YOUR ORIGINAL LOGIC HERE -->
            <a href="<?= (isset($_SESSION['tenant_id']) ? '/dashboard' : '/') ?>" class="logo" style="text-decoration: none; font-size: 1.25rem; font-weight: bold; color: var(--text);">
                📋 PostYourJobHere.com
            </a>

            <!-- Removed fixed margin-left and added flex-wrap -->
            <nav style="display: flex; gap: 1.25rem; flex-wrap: wrap;">
                <?php 
                $ctx = $GLOBALS['viewContext'] ?? 'public';
                
                // Show Public Links ONLY if NOT in admin or tenant dashboard
                if ($ctx === 'public'): ?>
                    <a href="/" style="text-decoration: none; color: var(--text); font-size: 0.95rem;">Explore Jobs</a>
                    <a href="/about" style="text-decoration: none; color: var(--text-muted); font-size: 0.95rem;">About Us</a>
                    <a href="/contact" style="text-decoration: none; color: var(--text-muted); font-size: 0.95rem;">Contact</a>
                <?php elseif ($ctx === 'tenant'): ?>
                    <a href="/" style="text-decoration: none; color: var(--text); font-size: 0.95rem;">Explore Jobs</a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Added flex-wrap: wrap to allow buttons to stack gracefully -->
        <nav style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 0.5rem;">
            <?php if ($ctx === 'admin'): ?>
                <a href="/admin" class="btn btn-secondary">Admin Dashboard</a>
                
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="/admin/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
                <?php endif; ?>

            <?php elseif ($ctx === 'tenant'): ?>
                <a href="/dashboard" class="btn btn-secondary">Dashboard</a>
                <a href="/jobs/create" class="btn btn-primary">Post a Job</a>
                
                <?php if (isset($_SESSION['tenant_id'])): ?>
                    <a href="/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
                <?php endif; ?>
                        
            <?php elseif ($ctx === 'login'): ?>
                <a href="/" style="color: var(--text-muted);">Explore Jobs</a>
                <a href="/login" class="btn btn-ghost btn-sm">🔒 Recruiter Portal</a>
                <a href="/admin/login" class="btn btn-primary btn-sm">⚡ Admin Entry</a>
                        
            <?php else: // Public ?>
                <a href="/login" class="btn btn-ghost btn-sm">🔒 Recruiter Portal</a>
                <a href="/admin/login" class="btn btn-primary btn-sm">⚡ Admin Entry</a>
            <?php endif; ?>
        </nav>
    </div>
</header>