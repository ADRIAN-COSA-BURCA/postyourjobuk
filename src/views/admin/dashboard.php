

<div class="admin-wrapper-view">
    
    <div class="admin-brand-header">
        <h1>System Overview</h1>
        <p>Manage multi-tenant isolation configurations, system stats, and corporate customer status mappings.</p>
    </div>

    <div class="admin-counters-grid">
        <div class="admin-metric-card">
            <h3>Total Configured Workspaces</h3>
            <p class="count-total"><?= htmlspecialchars($stats['total_tenants'] ?? 0); ?></p>
        </div>

        <div class="admin-metric-card">
            <h3>Active Tenants</h3>
            <p class="count-active"><?= htmlspecialchars($stats['active_tenants'] ?? 0); ?></p>
        </div>

        <div class="admin-metric-card">
            <h3>Global Active Listings</h3>
            <p class="count-listings"><?= htmlspecialchars($stats['total_jobs'] ?? 0); ?></p>
        </div>
    </div>

    <div class="admin-data-card">
        <div class="card-title-bar">
            <h2>Manage Operational Client Environments</h2>
        </div>

        <table class="admin-table-view">
            <thead>
                <tr>
                    <th>Company Profile Name</th>
                    <th>Routing Status</th>
                    <th style="text-align: right;">Workspace Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tenants)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic; padding: 32px;">
                            No tenant nodes configured in workspace infrastructure.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tenants as $tenant): 
                        $status = strtolower($tenant['status'] ?? 'active'); 
                    ?>
                    <tr>
                        <td style="font-weight: 600;">
                            <?= htmlspecialchars($tenant['company_name']); ?>
                        </td>
                        <td>
                            <span class="status-badge-flag <?= $status; ?>">
                                <?= htmlspecialchars(ucfirst($status)); ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="/admin/view-tenant?id=<?= urlencode($tenant['tenant_id']); ?>" class="admin-action-btn">
                                Control Console
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

