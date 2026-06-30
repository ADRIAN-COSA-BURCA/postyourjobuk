<div class="container admin-wrapper-view">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between;">
        <a href="/admin" class="btn btn-secondary">← Back to Dashboard</a>
        <a href="/admin/tenant/edit?id=<?= urlencode($tenant['tenant_id']); ?>" class="btn btn-primary">Edit Company Details</a>
    </div>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 2rem;">
        <h2> <?= htmlspecialchars($tenant['company_name']); ?></h2>
                
        <div class="admin-counters-grid" style="margin-top: 1.5rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div>
                <span class="form-label">Central Contact Email</span>
                <p style="font-weight: 600;"><?= htmlspecialchars($tenant['email']); ?></p>
            </div>
            <div>
                <span class="form-label">Contact Person</span>
                <p><?= htmlspecialchars($tenant['contact_person'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <span class="form-label">Phone Number</span>
                <p><?= htmlspecialchars($tenant['phone_number'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <span class="form-label">Industry</span>
                <p><?= htmlspecialchars($tenant['industry'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <span class="form-label">Website</span>
                <p><a href="<?= htmlspecialchars($tenant['website_url'] ?? '#'); ?>" target="_blank"><?= htmlspecialchars($tenant['website_url'] ?? 'N/A'); ?></a></p>
            </div>
            <div>
                <span class="form-label">Current Gateway Status</span>
                <span class="status-badge-flag <?= strtolower($tenant['status']); ?>">
                    <?= ucfirst(htmlspecialchars($tenant['status'])); ?>
                </span>
            </div>
        </div>
        <div style="margin-top: 1rem;">
            <span class="form-label">Address</span>
            <p><?= nl2br(htmlspecialchars($tenant['company_address'] ?? 'No address provided.')); ?></p>
        </div>
    </div>

    <div class="card" style="border: 1px solid var(--danger); margin-top: 2rem;">
        <h2 style="color: var(--danger);">Suspend Tenant</h2>
        <p>This action will <?= $tenant['status'] === 'active' ? 'suspend' : 'restore' ?> authorization for this tenant.</p>
        
        <form action="/admin/<?= $tenant['status'] === 'active' ? 'suspend' : 'activate' ?>-tenant?id=<?= $tenant['tenant_id']; ?>" method="POST" class="flex-between" style="max-width: 500px; margin-top: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <button type="submit" class="btn" style="background: var(--danger); color: white;">
                <?= $tenant['status'] === 'active' ? 'Suspend' : 'Restore Access' ?>
            </button>
        </form>
    </div>
</div>