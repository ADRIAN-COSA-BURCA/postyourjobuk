<header class="header">
    <div class="container flex-between"> <a href="<?= (isset($_SESSION['tenant_id']) ? '/dashboard' : '/') ?>" 
           class="logo" style="text-decoration: none; font-size: 1.25rem; font-weight: bold; color: var(--text);">
           📋 PostYourJobHere.com
        </a>

        <nav class="nav" style="display: flex; gap: 10px;">
    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        <a href="/admin" class="btn btn-secondary">Admin Dashboard</a>
        <a href="/admin/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
    <?php elseif (isset($_SESSION['tenant_id'])): ?>
        <a href="/dashboard" class="btn btn-secondary">Dashboard</a>
        <a href="/jobs/create" class="btn btn-primary">Post a Job</a>
        <a href="/admin/logout" class="btn" style="background:var(--danger); color:white;">Logout</a>
    <?php endif; ?>
</nav>
    </div>
</header>