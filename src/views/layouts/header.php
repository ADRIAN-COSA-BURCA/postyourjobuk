<header class="header">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
        <a href="<?= (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) ? '/admin' : (isset($_SESSION['tenant_id']) ? '/dashboard' : '/') ?>" 
           class="logo" style="text-decoration: none; font-size: 1.25rem; font-weight: bold; color: inherit;">
           📋 PostYourJobHere.com
        </a>

        <nav class="nav" style="display: flex; gap: 15px;">
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="/admin" class="btn btn-ghost">Admin Dashboard</a>
                <a href="/admin/logout" class="btn btn-ghost">Logout</a>

            <?php elseif (isset($_SESSION['tenant_id'])): ?>
                <a href="/dashboard" class="btn btn-ghost">Dashboard</a>
                <a href="/jobs/create" class="btn btn-ghost">Post a Job</a>
                <a href="/admin/logout" class="btn btn-ghost">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>