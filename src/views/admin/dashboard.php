<div class="container admin-wrapper-view">
    <div class="card" style="margin-bottom: 2rem;"> 
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>System Overview</h2>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="/admin/logs" class="btn btn-secondary">View System Logs</a>
            
            <!-- NEW: Analytics Button placed exactly between Logs and Create Recruiter -->
            <a href="/admin/analytics" class="btn btn-secondary">📊 Platform Analytics</a>
            
            <button onclick="document.getElementById('createTenantModal').style.display='flex'" class="btn btn-primary">
                + Create New Recruiter
            </button>
        </div>
    </div>
</div>

    <div id="createTenantModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 500px; padding: 2rem; max-height: 90vh; overflow-y: auto;">
            <h3>Add New Recruiter</h3>
            <form action="/admin/tenant/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                
                <div style="margin-bottom: 1rem;"><label>Company Name</label><input type="text" name="company_name" required style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Contact Person</label><input type="text" name="contact_person" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Admin Email</label><input type="email" name="email" required style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Phone Number</label><input type="text" name="phone_number" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Website URL</label><input type="url" name="website_url" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Industry</label><input type="text" name="industry" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Company Address</label><textarea name="company_address" style="width: 100%; padding: 0.5rem;"></textarea></div>
                <div style="margin-bottom: 1rem;"><label>Initial Password</label><input type="password" name="password" required style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Recovery Code (Unique Identifier)</label><input type="text" name="recovery_code" required style="width: 100%; padding: 0.5rem;" placeholder="e.g., PJH-12345"></div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('createTenantModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-counters-grid">
        <div class="admin-metric-card">
            <h3>Total Tenants</h3>
            <p class="count-total"><?= htmlspecialchars($stats['total_tenants'] ?? 0); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Active Tenants</h3>
            <p class="count-active"><?= htmlspecialchars($stats['active_tenants'] ?? 0); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Total Job Postings</h3>
            <p class="count-listings"><?= htmlspecialchars($stats['total_jobs'] ?? 0); ?></p>
        </div>
    </div>

    <div class="card admin-data-card">
        <div class="card-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);">
            <h2>Manage Tenants</h2>
        </div>
		<div style="margin-bottom: 1rem;">
    <strong>Filter by Status:</strong>
    <a href="?sort=<?= $currentSort ?>&order=<?= $currentOrder ?>&filter=all" class="btn <?= $currentFilter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
    <a href="?sort=<?= $currentSort ?>&order=<?= $currentOrder ?>&filter=active" class="btn <?= $currentFilter === 'active' ? 'btn-primary' : 'btn-secondary' ?>">Active</a>
    <a href="?sort=<?= $currentSort ?>&order=<?= $currentOrder ?>&filter=suspended" class="btn <?= $currentFilter === 'suspended' ? 'btn-primary' : 'btn-secondary' ?>">Suspended</a>
</div>


        <table class="admin-table-view">
            <thead>
                <tr>
                    <?php 
                        $nextOrder = ($currentSort === 'company_name' && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
                    ?>
                    <th>
    <a href="?sort=company_name&order=<?= $nextOrder ?>&filter=<?= htmlspecialchars($currentFilter) ?>" style="text-decoration:none; color:inherit;">
        Company Profile Name <?= $currentSort === 'company_name' ? ($currentOrder === 'ASC' ? '▲' : '▼') : '' ?>
    </a>
</th>
<th>
    <?php $statusOrder = ($currentSort === 'status' && $currentOrder === 'ASC') ? 'DESC' : 'ASC'; ?>
    <a href="?sort=status&order=<?= $statusOrder ?>&filter=<?= htmlspecialchars($currentFilter) ?>" style="text-decoration:none; color:inherit;">
        Routing Status <?= $currentSort === 'status' ? ($currentOrder === 'ASC' ? '▲' : '▼') : '' ?>
    </a>
</th>
                    <th style="text-align: right;">Workspace Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tenants)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem; color: var(--muted);">
                            No tenant nodes configured in workspace infrastructure.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tenants as $tenant): 
                        $status = strtolower($tenant['status'] ?? 'active'); ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($tenant['company_name']); ?></td>
                        <td>
                            <span class="status-badge-flag <?= $status; ?>">
                                <?= htmlspecialchars(ucfirst($status)); ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="/admin/view-tenant?id=<?= urlencode($tenant['tenant_id']); ?>" class="btn btn-primary">
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