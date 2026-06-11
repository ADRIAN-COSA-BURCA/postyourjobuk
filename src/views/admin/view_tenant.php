

<div class="container admin-wrapper-view">
    <div style="margin-bottom: 1.5rem;">
        <a href="/admin" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 2rem;">
        <h1>Tenant: <?= htmlspecialchars($tenant['company_name']); ?></h1>
        <p class="text-muted">Review active routing states, client registration logs, and isolated job database metrics.</p>
        
        <div class="admin-counters-grid" style="margin-top: 1.5rem;">
            <div>
                <span class="form-label">Central Contact Email</span>
                <p style="font-weight: 600;"><?= htmlspecialchars($tenant['email']); ?></p>
            </div>
            <div>
                <span class="form-label">Current Gateway Status</span>
                <span class="status-badge-flag <?= strtolower($tenant['status']); ?>">
                    <?= ucfirst(htmlspecialchars($tenant['status'])); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card" style="border: 1px solid var(--danger);">
        <h2 style="color: var(--danger);">Infrastructure Gateway Governance</h2>
        <p>This action will <?= $tenant['status'] === 'active' ? 'suspend' : 'restore' ?> routing authorization.</p>
        
        <form action="/admin/<?= $tenant['status'] === 'active' ? 'suspend' : 'activate' ?>-tenant?id=<?= $tenant['tenant_id']; ?>" method="POST" class="flex-between" style="max-width: 500px;">
            <input type="password" name="password" placeholder="Confirm Admin Password" required class="form-input">
            <button type="submit" class="btn" style="background: var(--danger); color: white; margin-left: 1rem;">
                <?= $tenant['status'] === 'active' ? 'Suspend Node' : 'Restore Access' ?>
            </button>
        </form>
    </div>
</div>